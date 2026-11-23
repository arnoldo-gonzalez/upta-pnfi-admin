<?php

namespace App\Services;

use App\Models\ProtocolTemplate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;

class ProtocolDocxService
{
    /**
     * Genera un docx a partir de la plantilla + valores y devuelve la ruta
     * del archivo temporal generado.
     */
    public function generate(ProtocolTemplate $template, array $values): string
    {
        $relative = 'protocol_templates/' . $template->docx_path;

        if (! Storage::disk('local')->exists($relative)) {
            throw new \RuntimeException('No se encontró el archivo de la plantilla de protocolo.');
        }

        $src = Storage::disk('local')->path($relative);

        Settings::setOutputEscapingEnabled(true);

        $tp = new TemplateProcessor($src);

        foreach ($values as $key => $value) {
            $tp->setValue($key, (string) $value);
        }

        // Fallback: reemplazar placeholders partidos por Word en el XML interno
        $this->replaceSplitPlaceholders($tp, $values);

        $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . Str::uuid() . '.docx';
        $tp->saveAs($tmp);

        return $tmp;
    }

    /**
     * Genera el docx y lo guarda en el disco local, devolviendo la ruta relativa.
     *
     * @return string ruta relativa dentro del disco local (ej: protocols/xxx.docx)
     */
    public function generateToStorage(ProtocolTemplate $template, array $values, string $directory = 'protocols'): string
    {
        $tmp = $this->generate($template, $values);

        try {
            $relative = $directory . '/' . Str::uuid() . '.docx';
            $contents = file_get_contents($tmp);

            if ($contents === false || ! Storage::disk('local')->put($relative, $contents)) {
                throw new \RuntimeException('No se pudo guardar el protocolo generado.');
            }

            return $relative;
        } finally {
            @unlink($tmp);
        }
    }

    /**
     * Construye las reglas de validación a partir del schema de la plantilla.
     */
    public function validationRules(ProtocolTemplate $template): array
    {
        $placeholders = $template->schema_json ?? [];

        $rules = [];
        foreach ($placeholders as $campo) {
            if (empty($campo['key'])) {
                continue;
            }

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
                'select' => empty($campo['options'])
                    ? 'string'
                    : 'in:' . implode(',', $campo['options']),
                default => 'string',
            };

            if (! empty($campo['maxChars']) && in_array($campo['type'] ?? 'text', ['text', 'textarea', 'email'])) {
                $rules[$key][] = 'max:' . $campo['maxChars'];
            }
        }

        return $rules;
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
            '/\{\{\s*([A-Za-z0-9_]+)\s*\}\}/',
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
            if (preg_match('/\{\{\s*([A-Za-z0-9_]+)\s*\}\}/', $plain)) {
                $xml = preg_replace_callback(
                    '/\{\{\s*([A-Za-z0-9_]+)\s*\}\}/',
                    $replacer,
                    $xml
                );
            }
        }

        // 4) Normalizar: mover <w:br/> fuera de <w:t> (debe ser hermano
        //    dentro de <w:r>; Word no lo renderiza dentro de <w:t>).
        $xml = $this->normalizeBrInXml($xml);

        // 5) Dividir párrafos justificados que contengan <w:br/> en múltiples
        //    párrafos separados. En Word, un párrafo con <w:jc="both"> justifica
        //    TODAS las líneas excepto la última; si hay <w:br/> internos, Word
        //    trata las líneas intermedias como "no últimas" y las estira.
        //    La solución es convertir cada <w:br/> en un nuevo <w:p> clonando
        //    las propiedades del párrafo y run original.
        $xml = $this->splitJustifiedParagraphsByBr($xml);

        $prop->setValue($tp, $xml);
    }

    /**
     * Divide párrafos justificados (<w:jc w:val="both"/>) que contienen
     * saltos de línea manuales (<w:br/>) en múltiples párrafos separados.
     *
     * Cada segmento entre <w:br/> se convierte en su propio <w:p>,
     * preservando <w:pPr> (incluyendo justificación) y <w:rPr> del run original.
     * Los <w:br/> finales no generan párrafo vacío adicional.
     */
    private function splitJustifiedParagraphsByBr(string $xml): string
    {
        return preg_replace_callback(
            '/(<w:p(?:\s[^>]*)?>)(.*?)(<\/w:p>)/s',
            function ($m) {
                $openTag = $m[1];
                $inner = $m[2];
                $closeTag = $m[3];

                // Solo procesar si el párrafo es justificado Y contiene <w:br/>
                if (! preg_match('/<w:jc\s+w:val\s*=\s*"both"/', $inner)) {
                    return $m[0];
                }
                if (strpos($inner, '<w:br/>') === false) {
                    return $m[0];
                }

                // Extraer <w:pPr> del párrafo (para clonar en nuevos párrafos)
                if (! preg_match('/^(<w:pPr>.*?<\/w:pPr>)/s', $inner, $pprMatch)) {
                    return $m[0]; // sin propiedades, no procesar
                }
                $pPr = $pprMatch[1];
                $rest = substr($inner, strlen($pPr));

                // Extraer todos los runs y dividir por <w:br/>
                if (! preg_match_all('/(<w:r(?:\s[^>]*)?>.*?<\/w:r>)/s', $rest, $runMatches)) {
                    return $m[0];
                }

                $currentParaRuns = [];

                foreach ($runMatches[0] as $runXml) {
                    if (! preg_match('/^(<w:r(?:\s[^>]*)?>)(.*?)(<\/w:r>)$/s', $runXml, $runParts)) {
                        $currentParaRuns[] = $runXml;
                        continue;
                    }

                    $runOpen = $runParts[1];
                    $runInner = $runParts[2];
                    $runClose = $runParts[3];

                    // Extraer rPr
                    $rpr = '';
                    if (preg_match('/^(<w:rPr>.*?<\/w:rPr>)/s', $runInner, $rprMatch)) {
                        $rpr = $rprMatch[1];
                        $runInner = substr($runInner, strlen($rpr));
                    }

                    // Dividir contenido del run por <w:br/>
                    $segments = [];
                    $lastPos = 0;
                    while (preg_match('/(<w:t[^>]*>.*?<\/w:t>|<w:br\/>)/s', $runInner, $tok, PREG_OFFSET_CAPTURE, $lastPos)) {
                        $token = $tok[0][0];
                        $tokenPos = $tok[0][1];
                        if ($tokenPos > $lastPos) {
                            $segments[] = substr($runInner, $lastPos, $tokenPos - $lastPos);
                        }
                        $segments[] = $token;
                        $lastPos = $tokenPos + strlen($token);
                    }
                    if ($lastPos < strlen($runInner)) {
                        $segments[] = substr($runInner, $lastPos);
                    }

                    // Agrupar segmentos en párrafos separados por <w:br/>
                    $paraSegments = [[]];
                    foreach ($segments as $seg) {
                        if ($seg === '<w:br/>') {
                            if (! empty($paraSegments[count($paraSegments) - 1])) {
                                $paraSegments[] = [];
                            }
                        } else {
                            $paraSegments[count($paraSegments) - 1][] = $seg;
                        }
                    }

                    // Reconstruir runs para cada párrafo
                    foreach ($paraSegments as $pi => $segList) {
                        if (empty($segList)) {
                            continue;
                        }
                        $newRunInner = $rpr . implode('', $segList);
                        $newRun = $runOpen . $newRunInner . $runClose;
                        if (! isset($currentParaRuns[$pi])) {
                            $currentParaRuns[$pi] = '';
                        }
                        $currentParaRuns[$pi] .= $newRun;
                    }
                }

                // Construir párrafos finales (solo los que tienen contenido)
                $paragraphs = [];
                foreach ($currentParaRuns as $runsXml) {
                    if (trim($runsXml) === '') {
                        continue;
                    }
                    $paragraphs[] = $openTag . $pPr . $runsXml . $closeTag;
                }

                return implode('', $paragraphs);
            },
            $xml
        );
    }

    /**
     * Consolida placeholders partidos en runs preservando el formato.
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
        if (! preg_match_all('/\{\{\s*([A-Za-z0-9_]+)\s*\}\}/', $paraText, $pm, PREG_OFFSET_CAPTURE)) {
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
