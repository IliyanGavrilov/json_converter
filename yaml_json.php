<?php
function valueToYaml(mixed $value, int $indent, int $indentSize): string {
    if ($value === null)  return "null\n";
    if ($value === true)  return "true\n";
    if ($value === false) return "false\n";

    if (is_int($value) || is_float($value)) return $value . "\n";

    if (is_string($value)) return formatYamlString($value, $indentSize) . "\n";

    if (is_array($value)) {
        if (empty($value)) {
            return array_is_list($value) ? "[]\n" : "{}\n";
        }

        $pad = str_repeat(' ', $indent * $indentSize);
        $out = '';

        if (array_is_list($value)) {
            foreach ($value as $item) {
                if (is_array($item)) {
                    $inner = valueToYaml($item, $indent + 1, $indentSize);
                    $lines = explode("\n", rtrim($inner));
                    $first = array_shift($lines);
                    $out .= $pad . '- ' . ltrim($first) . "\n";
                    foreach ($lines as $line) {
                        if (trim($line) !== '') {
                            $out .= $line . "\n";
                        }
                    }
                } else {
                    $out .= $pad . '- ' . ltrim(valueToYaml($item, 0, $indentSize));
                }
            }
        } else {
            foreach ($value as $key => $val) {
                $yamlKey = needsQuoting((string)$key)
                    ? '"' . addcslashes((string)$key, '"\\') . '"'
                    : (string)$key;

                if (is_array($val) && !empty($val)) {
                    $out .= $pad . $yamlKey . ":\n" . valueToYaml($val, $indent + 1, $indentSize);
                } else {
                    $out .= $pad . $yamlKey . ': ' . ltrim(valueToYaml($val, 0, $indentSize));
                }
            }
        }
        return $out;
    }

    return "null\n";
}

function formatYamlString(string $s, int $indentSize): string {
    if ($s === '') return '""';
    if (str_contains($s, "\n")) {
        $lines = explode("\n", $s);
        $block = "|\n";
        $pad = str_repeat(' ', $indentSize);
        foreach ($lines as $line) {
            $block .= $pad . $line . "\n";
        }
        return rtrim($block);
    }

    if (needsQuoting($s)) {
        return '"' . addcslashes($s, '"\\') . '"';
    }
    return $s;
}

function needsQuoting(string $s): bool {
    if ($s === '') return true;
 
    $boolNull = ['true','false','yes','no','on','off','null','~'];
    if (in_array(strtolower($s), $boolNull, true)) return true;
    if (is_numeric($s)) return true;
    if (preg_match('/^[\[\]{}&*!|>\'"%@`,]/', $s)) return true;
    if (preg_match('/[:#\[\]{},]/', $s)) return true;
    if ($s !== trim($s)) return true;
 
    return false;
}

function parseSimpleYaml(string $input): array {
    $lines = explode("\n", $input);
    $result = [];
    $i = 0;
    
    while ($i < count($lines)) {
        $line = $lines[$i];
        $indent = strlen($line) - strlen(ltrim($line));
        $trimmed = trim($line);
        
        if ($trimmed === '' || (isset($trimmed[0]) && $trimmed[0] === '#')) {
            $i++;
            continue;
        }
        
        $parsed = parseYamlBlock($lines, $i, $indent);
        if (!empty($parsed)) {
            if (isset($parsed['_is_list']) && $parsed['_is_list']) {
                unset($parsed['_is_list']);
                $result = array_merge($result, $parsed);
            } else {
                $result = array_merge($result, $parsed);
            }
        }
    }
    
    return $result;
}

function parseYamlBlock(array $lines, int &$i, int $baseIndent): array {
    $result = [];
    $isList = false;
    
    while ($i < count($lines)) {
        if (!isset($lines[$i])) break;
        
        $line = $lines[$i];
        $indent = strlen($line) - strlen(ltrim($line));
        $trimmed = trim($line);
        
        if ($indent < $baseIndent) {
            break;
        }
        
        if ($trimmed === '' || (isset($trimmed[0]) && $trimmed[0] === '#')) {
            $i++;
            continue;
        }
        if (isset($trimmed[0]) && $trimmed[0] === '-') {
            $isList = true;
            $listContent = ltrim(substr($trimmed, 1));
            
            if ($listContent === '') {
                $i++;
                $nested = parseYamlBlock($lines, $i, $indent + 2);
                $result[] = $nested;
            } elseif (strpos($listContent, ':') !== false) {
                $colonPos = strpos($listContent, ':');
                $key = trim(substr($listContent, 0, $colonPos));
                $value = trim(substr($listContent, $colonPos + 1));
                
                $i++;
                
                if ($i < count($lines)) {
                    $nextLine = $lines[$i];
                    $nextIndent = strlen($nextLine) - strlen(ltrim($nextLine));
                    if ($nextIndent > $indent) {
                        $nested = parseYamlBlock($lines, $i, $nextIndent);
                        $result[] = [$key => $nested];
                    } else {
                        $result[] = [$key => castYamlValue($value)];
                    }
                } else {
                    $result[] = [$key => castYamlValue($value)];
                }
            } else {
                $i++;
                $result[] = castYamlValue($listContent);
            }
            continue;
        }
        if (strpos($trimmed, ':') !== false) {
            $colonPos = strpos($trimmed, ':');
            $key = trim(substr($trimmed, 0, $colonPos));
            $value = trim(substr($trimmed, $colonPos + 1));
            
            $i++;
            if ($value === '|' || $value === '>') {
                $result[$key] = parseMultiLineString($lines, $i, $indent + 1, $value);
                continue;
            }
            
            if ($value === '' && $i < count($lines)) {
                $nextLine = $lines[$i];
                $nextIndent = strlen($nextLine) - strlen(ltrim($nextLine));
                if ($nextIndent > $indent) {
                    $nested = parseYamlBlock($lines, $i, $nextIndent);
                    $result[$key] = $nested;
                    continue;
                }
            }
            
            $result[$key] = castYamlValue($value);
            continue;
        }
        
        $i++;
    }
    
    if ($isList) {
        $result['_is_list'] = true;
    }
    
    return $result;
}

function parseMultiLineString(array $lines, int &$i, int $baseIndent, string $indicator): string {
    $result = [];
    $preserveNewlines = ($indicator === '|');
    
    while ($i < count($lines)) {
        $line = $lines[$i];
        $indent = strlen($line) - strlen(ltrim($line));
        
        if ($indent < $baseIndent) {
            break;
        }
        $content = $line;
        if ($indent >= $baseIndent) {
            $content = substr($line, $baseIndent);
        }
        $result[] = rtrim($content);
        $i++;
    }
    $string = implode($preserveNewlines ? "\n" : " ", $result);
    if (!$preserveNewlines) {
        $string = preg_replace('/\s+/', ' ', $string);
    }
    return trim($string);
}

function castYamlValue(string $val): mixed {
    $val = trim($val);
    if ($val === '~' || strtolower($val) === 'null') {
        return null;
    }
    $lower = strtolower($val);
    if ($lower === 'true' || $lower === 'yes' || $lower === 'on') {
        return true;
    }
    if ($lower === 'false' || $lower === 'no' || $lower === 'off') {
        return false;
    }
    if (is_numeric($val)) {
        if (strpos($val, '.') !== false || strpos($val, 'e') !== false) {
            return (float) $val;
        }
        return (int) $val;
    }
    if ((substr($val, 0, 1) === '"' && substr($val, -1) === '"') ||
        (substr($val, 0, 1) === "'" && substr($val, -1) === "'")) {
        return substr($val, 1, -1);
    }
    return $val;
}