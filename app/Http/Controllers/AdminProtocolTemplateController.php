<?php

namespace App\Http\Controllers;

use App\Models\ProtocolTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\TemplateProcessor;

class AdminProtocolTemplateController extends Controller
{
    public function show()
    {
        $template = ProtocolTemplate::active();

        return view('admin.protocol-template', compact('template'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'protocol_template' => 'required|file|mimes:docx|max:10240',
        ]);

        $file = $request->file('protocol_template');
        $filePath = Str::uuid() . '/plantilla.docx';

        if (! Storage::disk('local')->putFileAs('protocol_templates', $file, $filePath)) {
            return back()->with('error', 'No se pudo guardar la plantilla. Inténtalo de nuevo.');
        }

        $src = Storage::disk('local')->path('protocol_templates/' . $filePath);

        try {
            $variables = $this->getVariablesFromFile($src);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete('protocol_templates/' . $filePath);
            report($e);

            return back()->with('error', 'El archivo no es un .docx válido o está dañado.');
        }

        if ($variables === []) {
            Storage::disk('local')->delete('protocol_templates/' . $filePath);

            return back()->with('error', 'La plantilla no contiene variables {{...}}. Agrega al menos una antes de subirla.');
        }

        $normalized = [];
        foreach ($variables as $var) {
            $key = preg_replace('/[^A-Z0-9_]/', '_', strtoupper(trim($var)));
            $key = trim($key, '_');
            $normalized[] = $key !== '' ? $key : 'CAMPO';
        }

        $uniqueVariables = array_values(array_unique($normalized));

        $schema = [];
        foreach ($uniqueVariables as $var) {
            $schema[] = [
                'key' => $var,
                'label' => $var,
                'type' => 'text',
                'required' => true,
                'maxChars' => null,
                'options' => [],
            ];
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($file, $filePath, $schema) {
            ProtocolTemplate::query()->update(['activo' => false]);

            ProtocolTemplate::create([
                'nombre' => $file->getClientOriginalName(),
                'docx_path' => $filePath,
                'schema_json' => $schema,
                'activo' => true,
                'created_by' => auth()->id(),
            ]);
        });

        return redirect()->route('admin.protocolo.plantilla')
            ->with('success', 'Plantilla subida y analizada correctamente.');
    }

    public function updateSchema(Request $request)
    {
        $template = ProtocolTemplate::active();

        if (! $template) {
            return redirect()->route('admin.protocolo.plantilla')
                ->with('error', 'No hay plantilla activa.');
        }

        $validated = $request->validate([
            'placeholders' => 'required|array',
            'placeholders.*.key' => 'required|string',
            'placeholders.*.label' => 'nullable|string|max:255',
            'placeholders.*.type' => 'required|string|in:text,textarea,email,tel,cedula,number,date,select',
            'placeholders.*.required' => 'nullable',
            'placeholders.*.maxChars' => 'nullable|integer|min:0',
            'placeholders.*.options' => 'nullable|string',
        ]);

        $schema = [];
        foreach ($validated['placeholders'] as $field) {
            $options = [];
            if (! empty($field['options'])) {
                $options = array_values(array_filter(array_map('trim', explode(',', $field['options']))));
            }

            $schema[] = [
                'key' => $field['key'],
                'label' => ! empty($field['label']) ? $field['label'] : $field['key'],
                'type' => $field['type'],
                'required' => ! empty($field['required']) && $field['required'] === '1',
                'maxChars' => isset($field['maxChars']) && $field['maxChars'] !== null && $field['maxChars'] !== ''
                    ? (int) $field['maxChars']
                    : null,
                'options' => $options,
            ];
        }

        $template->schema_json = $schema;
        $template->save();

        return redirect()->route('admin.protocolo.plantilla')
            ->with('success', 'Configuración guardada correctamente.');
    }

    /**
     * Detecta las variables {{...}} de la plantilla.
     *
     * Combina dos fuentes para no perder ninguna variable:
     *  1) TemplateProcessor::getVariables() (detecta las contiguas).
     *  2) Regex sobre el XML interno del .docx, incluyendo todas las partes
     *     (document, headers, footers, footnotes, endnotes, comments).
     *
     * Word suele partir placeholders en varios "runs" <w:t>, en cuyo caso
     * getVariables() los omite. Por eso SIEMPRE se ejecuta el regex y se
     * fusionan los resultados (no se retorna antes).
     */
    private function getVariablesFromFile(string $src): array
    {
        $variables = [];

        // 1) Intento estándar (puede devolver solo las contiguas)
        try {
            $tp = new TemplateProcessor($src);
            foreach ($tp->getVariables() as $raw) {
                $name = $this->cleanVariableName((string) $raw);
                if ($name !== '') {
                    $variables[] = $name;
                }
            }
        } catch (\Throwable $e) {
            // Ignorar y continuar con el fallback de regex
        }

        // 2) Fallback/refuerzo con regex sobre el XML interno
        $zip = new \ZipArchive();
        if ($zip->open($src) === true) {
            $parts = [
                'word/document.xml',
                'word/footnotes.xml',
                'word/endnotes.xml',
                'word/comments.xml',
            ];
            for ($i = 1; $i <= 10; $i++) {
                $parts[] = "word/header{$i}.xml";
                $parts[] = "word/footer{$i}.xml";
            }

            // Añadir cualquier otra parte word/*.xml por seguridad
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (is_string($name)
                    && preg_match('#^word/[^/]+\.xml$#', $name)
                    && ! in_array($name, $parts, true)) {
                    $parts[] = $name;
                }
            }

            $parts = array_values(array_unique($parts));

            foreach ($parts as $part) {
                $xml = $zip->getFromName($part);
                if ($xml === false || $xml === '') {
                    continue;
                }

                // Estrategia robusta: Word parte los placeholders en varios
                // <w:t> (runs). Extraer el texto de cada <w:t>, unirlos y luego
                // buscar {{...}} con un regex ESTRICTO que solo acepte:
                //   {{UPPERCASE_LETTERS_NUMBERS_UNDERSCORES}}
                // Esto evita falsos positivos si hay texto arbitrario entre partes.
                $texts = [];
                if (preg_match_all('/<w:t[^>]*>(.*?)<\/w:t>/s', $xml, $textMatches)) {
                    $texts = $textMatches[1];
                }

                if (! empty($texts)) {
                    $found = [];

                    // 1) Buscar placeholders contiguos dentro de cada <w:t>.
                    foreach ($texts as $t) {
                        if (preg_match_all('/\{\{\s*([A-Za-z0-9_]+)\s*\}\}/', $t, $m)) {
                            foreach ($m[1] as $match) {
                                $found[] = $match;
                            }
                        }
                    }

                    // 2) Por cada párrafo, unir sus <w:t> y buscar de nuevo.
                    //    Word parte los placeholders entre runs DENTRO de un
                    //    párrafo, así que agrupar por <w:p> cubre esos casos
                    //    sin mezclar texto de párrafos distintos (falsos positivos).
                    $paragraphs = [];
                    if (preg_match_all('/<w:p(?:\s[^>]*)?>(.*?)<\/w:p>/s', $xml, $pMatches)) {
                        $paragraphs = $pMatches[1];
                    }
                    foreach ($paragraphs as $paragraphXml) {
                        if (! preg_match_all('/<w:t[^>]*>(.*?)<\/w:t>/s', $paragraphXml, $pTexts)) {
                            continue;
                        }
                        $plain = implode('', $pTexts[1]);
                        if (preg_match_all('/\{\{\s*([A-Za-z0-9_]+)\s*\}\}/', $plain, $m)) {
                            foreach ($m[1] as $match) {
                                $found[] = $match;
                            }
                        }
                    }

                    // 3) Fallback global: si aún no se encontró nada, unir todos
                    //    los <w:t> del documento (cubre párrafos vacíos anidados
                    //    o estructuras raras). El regex estricto limita el riesgo.
                    if (empty($found)) {
                        $plain = implode('', $texts);
                        if (preg_match_all('/\{\{\s*([A-Za-z0-9_]+)\s*\}\}/', $plain, $m)) {
                            foreach ($m[1] as $match) {
                                $found[] = $match;
                            }
                        }
                    }

                    foreach (array_unique($found) as $name) {
                        if ($name !== '') {
                            $variables[] = $name;
                        }
                    }
                }
            }
            $zip->close();
        }

        return array_values(array_unique($variables));
    }

    /**
     * Limpia el nombre de una variable: normaliza a mayúsculas con underscores.
     * (El regex estricto ya garantiza [A-Z0-9_], esto es defensa extra para
     * los nombres que devuelve TemplateProcessor::getVariables()).
     */
    private function cleanVariableName(string $raw): string
    {
        $name = trim($raw, "{} \t\n\r\0\x0B");
        $name = preg_replace('/[^A-Z0-9_]/', '_', strtoupper($name));
        return trim($name, '_');
    }
}
