<?php

namespace App\Http\Controllers;

use App\Models\ProtocolTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;

class ProtocolPreviewController extends Controller
{
    public function create()
    {
        $template = ProtocolTemplate::active();

        if (! $template) {
            return view('protocol.form', ['noTemplate' => true]);
        }

        return view('protocol.form', compact('template'));
    }

    public function preview(Request $request)
    {
        $template = ProtocolTemplate::active();

        if (! $template) {
            return redirect()->route('protocolo.llenar')
                ->with('error', 'No hay plantilla de protocolo configurada.');
        }

        $placeholders = $template->schema_json ?? [];

        $rules = [];
        foreach ($placeholders as $campo) {
            $key = $campo['key'];
            $rules[$key] = [];

            if (! empty($campo['required'])) {
                $rules[$key][] = 'required';
            } else {
                $rules[$key][] = 'nullable';
            }

            $rules[$key][] = match ($campo['type'] ?? 'text') {
                'email' => 'email',
                'number' => 'numeric',
                'date' => 'date',
                'tel' => 'regex:/^\+?[\d\s\-().]{7,20}$/',
                'cedula' => 'regex:/^[VE]-?\d{1,2}\.?\d{3}\.?\d{3}$/i',
                'select' => 'in:' . implode(',', $campo['options'] ?? []),
                default => 'string',
            };

            if (! empty($campo['maxChars']) && in_array($campo['type'] ?? 'text', ['text', 'textarea', 'email'])) {
                $rules[$key][] = 'max:' . $campo['maxChars'];
            }
        }

        $validated = $request->validate($rules);

        $src = Storage::disk('local')->path('protocol_templates/' . $template->docx_path);

        Settings::setOutputEscapingEnabled(true);

        $tp = new TemplateProcessor($src);

        foreach ($validated as $key => $value) {
            $tp->setValue($key, (string) $value);
        }

        // Fallback: reemplazar placeholders partidos por Word en el XML interno
        $this->replaceSplitPlaceholders($tp, $validated);

        $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . Str::uuid() . '.docx';
        $tp->saveAs($tmp);

        return response()->download($tmp, 'protocolo_previsualizado.docx')
            ->deleteFileAfterSend(true);
    }

    /**
     * Reemplaza placeholders que Word partió en varios runs XML.
     *
     * TemplateProcessor::setValue() falla si el placeholder está dividido
     * entre múltiples nodos <w:t>. Este método busca los placeholders en el
     * XML y los reemplaza directamente, fusionando los runs si es necesario.
     *
     * Estrategia robusta:
     *   1. Buscar en cada <w:t> individual (placeholders contiguos).
     *   2. Buscar uniendo <w:t> por párrafo <w:p> (placeholders partidos).
     *   3. Buscar uniendo todos los <w:t> (fallback global).
     * El regex es estricto: solo {{UPPERCASE_NUMBERS_UNDERSCORES}}.
     *
     * Los saltos de línea (\n) se convierten a <w:br/> para que Word los
     * muestre como saltos reales.
     */
    private function replaceSplitPlaceholders(TemplateProcessor $tp, array $values): void
    {
        $ref = new \ReflectionObject($tp);
        $prop = $ref->getProperty('tempDocumentMainPart');
        $prop->setAccessible(true);
        $xml = $prop->getValue($tp);

        if (! is_string($xml)) {
            return;
        }

        // Construir una tabla de normalización de claves para comparación
        $normKeys = [];
        foreach ($values as $key => $value) {
            $norm = preg_replace('/[^A-Z0-9_]/', '_', strtoupper($key));
            $normKeys[$norm] = (string) $value;
        }

        // Función auxiliar para reemplazar un match con el valor correcto,
        // convirtiendo \n a <w:br/> y escapando el resto de caracteres XML.
        $replacer = function ($m) use ($normKeys) {
            $norm = preg_replace('/[^A-Z0-9_]/', '_', strtoupper($m[1]));
            if (! isset($normKeys[$norm])) {
                return $m[0];
            }
            $value = trim((string) $normKeys[$norm]);
            // Escape XML de los caracteres especiales
            $value = htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
            // Convertir saltos de línea a <w:br/> (Word no renderiza \n)
            // y escapear texto para XML (evitar inyección)
            $value = preg_replace('/\r\n|\r|\n/', '<w:br/>', $value);
            return $value;
        };

        // 1) Buscar placeholders contiguos en <w:t> individuales
        $xml = preg_replace_callback(
            '/\{\{\s*([A-Z0-9_]+)\s*\}\}/',
            $replacer,
            $xml
        );

        // 2) Buscar placeholders partidos uniendo <w:t> por párrafo <w:p>
        // Usar el enfoque consolidado: preservar formato de la primera run con texto de variable
        $xml = preg_replace_callback(
            '/(<w:p(?:\s[^>]*)?>)(.*?)(<\/w:p>)/s',
            function ($m) use ($replacer, $normKeys) {
                $openTag = $m[1];  // <w:p ...>
                $inner = $m[2];    // contenido
                $closeTag = $m[3]; // </w:p>

                return $openTag . $this->consolidatePlaceholderRuns($inner, $replacer, $normKeys) . $closeTag;
            },
            $xml
        );

        // 3) Fallback global: unir todos los <w:t> y buscar
        if (preg_match_all('/<w:t[^>]*>(.*?)<\/w:t>/s', $xml, $tm)) {
            $plain = implode('', $tm[1]);
            if (preg_match('/\{\{\s*([A-Z0-9_]+)\s*\}\}/', $plain)) {
                $xml = preg_replace_callback(
                    '/\{\{\s*([A-Z0-9_]+)\s*\}\}/',
                    $replacer,
                    $xml
                );
            }
        }

        // 4) Normalizar: mover <w:br/> fuera de <w:t> (debe ser hermano
        //    dentro de <w:r>; Word no lo renderiza dentro de <w:t>).
        $xml = $this->normalizeBrInXml($xml);

        $prop->setValue($tp, $xml);
    }

    /**
     * Consolida placeholders partidos en runs preservando el formato.
     *
     * Estrategia (Approach B):
     *  - Extrae todos los <w:r> del párrafo y su texto (<w:t>).
     *  - Construye el texto plano del párrafo y mapea posiciones a runs.
     *  - Para cada {{KEY}} encontrado identifica los runs que lo componen.
     *  - El primer run que contiene texto real de la variable recibe el
     *    valor de reemplazo (conservando su <w:rPr>); los demás runs
     *    implicados se vacían.
     *  - Los runs no implicados se dejan intactos.
     */
    private function consolidatePlaceholderRuns(string $inner, \Closure $replacer, array $normKeys): string
    {
        // Detección rápida: sin llaves no hay nada que consolidar.
        if (strpos($inner, '{{') === false) {
            return $inner;
        }

        // 1) Extraer todos los runs con sus offsets dentro de $inner.
        if (! preg_match_all('/<w:r(?:\s[^>]*)?>.*?<\/w:r>/s', $inner, $rm, PREG_OFFSET_CAPTURE)) {
            return $inner;
        }

        $runs = [];
        $paraText = '';

        foreach ($rm[0] as $entry) {
            $fullXml = $entry[0];
            $offset = $entry[1];

            if (! preg_match('/^(<w:r(?:\s[^>]*)?>)(.*)(<\/w:r>)$/s', $fullXml, $parts)) {
                continue;
            }

            $openTag = $parts[1];
            $runInner = $parts[2];
            $closeTag = $parts[3];

            $rpr = '';
            if (preg_match('/<w:rPr>.*?<\/w:rPr>/s', $runInner, $rpm)) {
                $rpr = $rpm[0];
            }

            $text = '';
            if (preg_match_all('/<w:t[^>]*>(.*?)<\/w:t>/s', $runInner, $tmm)) {
                $text = implode('', $tmm[1]);
            }

            $start = strlen($paraText);
            $paraText .= $text;

            $runs[] = [
                'offset' => $offset,
                'length' => strlen($fullXml),
                'open' => $openTag,
                'close' => $closeTag,
                'rpr' => $rpr,
                'text' => $text,
                'start' => $start,
                'end' => $start + strlen($text),
            ];
        }

        if (empty($runs) || strpos($paraText, '{{') === false) {
            return $inner;
        }

        // 2) Encontrar todos los placeholders en el texto plano del párrafo.
        if (! preg_match_all('/\{\{\s*([A-Z0-9_]+)\s*\}\}/', $paraText, $pm, PREG_OFFSET_CAPTURE)) {
            return $inner;
        }

        // 3) Construir ediciones por run.
        $edits = []; // runIndex => lista de ['start','end','replacement']

        foreach ($pm[0] as $i => $match) {
            $full = $match[0];
            $ms = $match[1];
            $me = $ms + strlen($full);
            $key = $pm[1][$i][0];

            $replacement = $replacer([$full, $key]);

            // Clave desconocida: el replacer devuelve el match original.
            if ($replacement === $full) {
                continue;
            }

            // Runs que solapan con este match.
            $overlapping = [];
            foreach ($runs as $idx => $run) {
                if ($run['end'] === $run['start']) {
                    continue; // run sin texto literal
                }
                if ($run['end'] <= $ms || $run['start'] >= $me) {
                    continue;
                }
                $llS = max($ms, $run['start']) - $run['start'];
                $llE = min($me, $run['end']) - $run['start'];
                $overlapping[$idx] = [$llS, $llE];
            }

            if (empty($overlapping)) {
                continue;
            }

            // Elegir el run que recibe el reemplazo: el primero que contiene
            // caracteres reales de la variable (no solo llaves/espacios).
            $target = null;
            foreach ($overlapping as $idx => [$llS, $llE]) {
                $slice = substr($runs[$idx]['text'], $llS, $llE - $llS);
                if (preg_match('/[A-Za-z0-9_]/', $slice)) {
                    $target = $idx;
                    break;
                }
            }
            if ($target === null) {
                $target = array_key_first($overlapping);
            }

            foreach ($overlapping as $idx => [$llS, $llE]) {
                $edits[$idx][] = [
                    'start' => $llS,
                    'end' => $llE,
                    'replacement' => ($idx === $target) ? $replacement : '',
                ];
            }
        }

        if (empty($edits)) {
            return $inner;
        }

        // 4) Reconstruir el XML del párrafo preservando los runs no editados.
        $result = '';
        $cursor = 0;

        foreach ($runs as $idx => $run) {
            if ($run['offset'] > $cursor) {
                $result .= substr($inner, $cursor, $run['offset'] - $cursor);
            }

            if (! isset($edits[$idx])) {
                $result .= substr($inner, $run['offset'], $run['length']);
            } else {
                $newText = $run['text'];
                // Aplicar ediciones de derecha a izquierda para no desplazar offsets.
                $list = $edits[$idx];
                usort($list, function ($a, $b) {
                    return $b['start'] <=> $a['start'];
                });
                foreach ($list as $edit) {
                    $newText = substr($newText, 0, $edit['start'])
                        . $edit['replacement']
                        . substr($newText, $edit['end']);
                }

                $result .= $run['open'] . $run['rpr']
                    . '<w:t xml:space="preserve">' . $newText . '</w:t>'
                    . $run['close'];
            }

            $cursor = $run['offset'] + $run['length'];
        }

        if ($cursor < strlen($inner)) {
            $result .= substr($inner, $cursor);
        }

        return $result;
    }

    /**
     * Normaliza el XML resultante: asegura que <w:br/> aparezca como
     * elemento hermano dentro del mismo <w:r>, NO como hijo de <w:t>.
     * Word no renderiza <w:br/> dentro de <w:t>, por lo que Word "repara"
     * el documento introduciendo saltos de línea extra.
     */
    private function normalizeBrInXml(string $xml): string
    {
        return preg_replace_callback(
            '/<w:t([^>]*)>(.*?)<\/w:t>/s',
            function ($m) {
                $attrs = $m[1];
                $inner = $m[2];

                // Si ya no hay <w:br/>, devolver tal cual
                if (strpos($inner, '<w:br/>') === false) {
                    return $m[0];
                }

                // Quitar cualquier xml:space existente para evitar duplicados
                $attrs = preg_replace('/\s*xml:space\s*=\s*"[^"]*"/', '', $attrs);
                $attrs = trim($attrs);
                $prefix = $attrs ? $attrs . ' ' : '';

                // Dividir en segmentos por <w:br/>
                $segments = explode('<w:br/>', $inner);
                $parts = [];
                foreach ($segments as $seg) {
                    // Guardar segmentos vacíos (líneas en blanco) como <w:t></w:t>
                    $parts[] = '<w:t ' . $prefix . 'xml:space="preserve">' . $seg . '</w:t>';
                }

                // Reconstruir: los <w:br/> van entre los <w:t>, NO dentro
                return implode('<w:br/>', $parts);
            },
            $xml
        );
    }
}
