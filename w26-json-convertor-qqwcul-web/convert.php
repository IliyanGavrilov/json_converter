<?php
require_once 'yaml_json.php';

function parseInput($input, $fromFormat) {
    switch ($fromFormat) {
        case 'json':
            $data = json_decode($input, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON: " . json_last_error_msg());
            }
            return $data;
            
        case 'yaml':
            return parseSimpleYaml($input);
            
        case 'xml':
            $xml = simplexml_load_string($input);
            if ($xml === false) {
                throw new Exception("Invalid XML");
            }
            return xmlToArray($xml);
            
        case 'csv':
            $lines = array_filter(explode("\n", trim($input)));
            $lines = array_values($lines);
            $headers = str_getcsv(array_shift($lines));
            $result = [];
            foreach ($lines as $line) {
                $row = str_getcsv($line);
                $result[] = array_combine($headers, $row);
            }
            return $result;
            
        case 'properties':
            $result = [];
            foreach (explode("\n", $input) as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#') continue;
                if (strpos($line, '=') !== false) {
                    [$key, $value] = explode('=', $line, 2);
                    $result[trim($key)] = trim($value);
                }
            }
            return $result;

        case 'ini':
            $result = [];
            $section = null;
            foreach (explode("\n", $input) as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === ';') continue;
                if (preg_match('/^\[(.+)\]$/', $line, $matches)) {
                    $section = $matches[1];
                } elseif (strpos($line, '=') !== false) {
                    [$key, $value] = explode('=', $line, 2);
                    if ($section) {
                        $result[$section][trim($key)] = trim($value);
                    } else {
                        $result[trim($key)] = trim($value);
                    }
                }
            }
            return $result;

        default:
            throw new Exception("Unsupported input format: " . $fromFormat);
    }
}

function outputFormat($data, $toFormat, $options = []) {
    $pretty = $options['pretty_print'] ?? true;

    switch ($toFormat) {
        case 'json':
            if (!$pretty) return json_encode($data, 0);
            $json = json_encode($data, JSON_PRETTY_PRINT);
            $indentation = (int)($options['indentation'] ?? 4);
            if ($indentation !== 4) {
                $indent = str_repeat(' ', $indentation);
                $json = preg_replace_callback('/^(    )+/m', function ($m) use ($indent) {
                    return str_repeat($indent, strlen($m[0]) / 4);
                }, $json);
            }
            return $json;

        case 'yaml':
            $indentSize = (int)($options['indentation'] ?? 2);
            return valueToYaml($data, 0, $indentSize);

        case 'xml':
            $xml = new SimpleXMLElement('<root/>');
            arrayToXml($data, $xml);
            if (!$pretty) {
                return $xml->asXML();
            }
            $dom = new DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($xml->asXML());
            $xmlOut = $dom->saveXML();
            $indentation = (int)($options['indentation'] ?? 2);
            if ($indentation !== 2) {
                $indent = str_repeat(' ', $indentation);
                $xmlOut = preg_replace_callback('/^(  )+/m', function ($m) use ($indent) {
                    return str_repeat($indent, strlen($m[0]) / 2);
                }, $xmlOut);
            }
            return $xmlOut;
            
        case 'csv':
            return arrayToCsv($data);
            
        case 'properties':
            return arrayToProperties($data);

        case 'ini':
            return arrayToIni($data);
            
        default:
            throw new Exception("Unsupported output format: " . $toFormat);
    }
}

function xmlToArray(SimpleXMLElement $node): array {
    $result = [];
    foreach ($node->children() as $name => $child) {
        $value = $child->count() > 0 ? xmlToArray($child) : (string)$child;
        if (isset($result[$name])) {
            if (!is_array($result[$name]) || !array_key_exists(0, $result[$name])) {
                $result[$name] = [$result[$name]];
            }
            $result[$name][] = $value;
        } else {
            $result[$name] = $value;
        }
    }
    return $result;
}

function arrayToXml($data, &$xml) {
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $subnode = $xml->addChild($key);
            arrayToXml($value, $subnode);
        } else {
            $xml->addChild($key, htmlspecialchars($value));
        }
    }
}

function arrayToCsv($data) {
    if (empty($data)) return '';
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $v) {
                if (is_array($v)) {
                    throw new Exception("CSV does not support nested data. Flatten your input first.");
                }
            }
        }
    }
    
    ob_start();
    $out = fopen('php://output', 'w');
    if (isset($data[0]) && is_array($data[0])) {
        fputcsv($out, array_keys($data[0]));
        foreach ($data as $row) {
            fputcsv($out, $row);
        }
    } else {
        fputcsv($out, array_keys($data));
        fputcsv($out, array_values($data));
    }
    fclose($out);
    return ob_get_clean();
}

function arrayToProperties($data, $prefix = '') {
    $lines = [];
    foreach ($data as $key => $value) {
        $fullKey = $prefix ? $prefix . '.' . $key : $key;
        if (is_array($value)) {
            $lines[] = arrayToProperties($value, $fullKey);
        } else {
            $lines[] = $fullKey . '=' . $value;
        }
    }
    return implode("\n", $lines);
}

function arrayToIni($data) {
    $lines = [];
    foreach ($data as $section => $values) {
        if (is_array($values)) {
            $lines[] = "[$section]";
            foreach ($values as $key => $value) {
                $lines[] = "$key=$value";
            }
            $lines[] = '';
        } else {
            $lines[] = "$section=$values";
        }
    }
    return implode("\n", $lines);
}