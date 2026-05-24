<?php
function valueToYaml(mixed $value, int $indent, int $indentSize): string {
    if ($value === null)  return "null\n";
    if ($value === true)  return "true\n";
    if ($value === false) return "false\n";

    if (is_int($value) || is_float($value)) return $value . "\n";

    if (is_string($value)) return formatYamlString($value,$indentSize) . "\n";

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
                        $out .= $line . "\n";
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
    $lines = array_values(array_filter($lines, fn($l) => trim($l) !== '' && trim($l)[0] !== '#'));
    $pos = 0;
    return parseYamlBlock($lines, $pos, 0);
}

function parseYamlBlock(array $lines, int &$pos, int $baseIndent): array {
    $result = [];
    while ($pos < count($lines)) {
        $line    = $lines[$pos];
        $indent  = strlen($line) - strlen(ltrim($line));
        $content = trim($line);
        if ($indent < $baseIndent) break;

        if (str_starts_with($content, '- ') || $content === '-') {
            $pos++;
            $itemContent = trim(substr($content, 2));
            if ($itemContent === '' || $content === '-') {
                $result[] = parseYamlBlock($lines, $pos, $indent + 1);

            } elseif (str_ends_with($itemContent, ':') && !str_contains($itemContent, ': ')) {
                $key  = rtrim($itemContent, ':');
                $item = [$key => parseYamlBlock($lines, $pos, $indent + 1)];
                $result[] = $item;
            } elseif (str_contains($itemContent, ': ')) {
                [$key, $val] = explode(': ', $itemContent, 2);
                $item = [$key => $val === ''
                    ? parseYamlBlock($lines, $pos, $indent + 2)
                    : castYamlValue($val)];
                while ($pos < count($lines)) {
                    $nextIndent  = strlen($lines[$pos]) - strlen(ltrim($lines[$pos]));
                    $nextContent = trim($lines[$pos]);
                    if ($nextIndent <= $indent) break;
                    $pos++;
                    if (str_ends_with($nextContent, ':') && !str_contains($nextContent, ': ')) {
                        $k = rtrim($nextContent, ':');
                        $item[$k] = parseYamlBlock($lines, $pos, $nextIndent + 1);
                    } else {
                        [$k, $v] = explode(': ', $nextContent, 2);
                        $item[$k] = $v === ''
                            ? parseYamlBlock($lines, $pos, $nextIndent + 1)
                            : castYamlValue($v);
                    }
                }
                $result[] = $item;

            } else {
                $result[] = castYamlValue($itemContent);
            }

        } elseif (str_ends_with($content, ':') && !str_contains($content, ': ')) {
            $key = rtrim($content, ':');
            $pos++;
            $result[$key] = parseYamlBlock($lines, $pos, $indent + 1);

        } elseif (str_contains($content, ': ')) {
            [$key, $val] = explode(': ', $content, 2);
            $pos++;
            $result[$key] = $val === ''
                ? parseYamlBlock($lines, $pos, $indent + 1)
                : castYamlValue($val);
        } 
        else {
            break;
        }
    }
    return $result;
}

function castYamlValue(string $val): mixed {
    if (str_starts_with($val, '!!int '))    return (int)   trim(substr($val, 6));
    if (str_starts_with($val, '!!float '))  return (float) trim(substr($val, 8));
    if (str_starts_with($val, '!!str '))    return (string)trim(substr($val, 6));
    if (str_starts_with($val, '!!bool '))   return filter_var(trim(substr($val, 7)), FILTER_VALIDATE_BOOLEAN);
    if (str_starts_with($val, '!!null '))   return null;
    if ($val === '!!null')                  return null;
    if ($val === '~' || strtolower($val) === 'null') return null;
    if (in_array(strtolower($val), ['true',  'yes', 'on'],  true)) return true;
    if (in_array(strtolower($val), ['false', 'no',  'off'], true)) return false;
    return $val;
}