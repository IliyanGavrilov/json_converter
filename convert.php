<?php

function parseInput($input, $fromFormat) {
    switch ($fromFormat) {
        case 'json':
            $data = json_decode($input, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON: " . json_last_error_msg());
            }
            return $data;
            
        case 'yaml':
            // TODO !!!
            
        case 'xml':
            $xml = simplexml_load_string($input);
            if ($xml === false) {
                throw new Exception("Invalid XML");
            }
            return json_decode(json_encode($xml), true);
            
        default:
            throw new Exception("Unsupported input format: " . $fromFormat);
    }
}

function outputFormat($data, $toFormat) {
    switch ($toFormat) {
        case 'json':
            return json_encode($data, JSON_PRETTY_PRINT);
            
        case 'yaml':
            // TODO !!!
            
        case 'xml':
            $xml = new SimpleXMLElement('<root/>');
            arrayToXml($data, $xml);
            return $xml->asXML();
            
        case 'csv':
            return arrayToCsv($data);
            
        case 'properties':
            return arrayToProperties($data);
            
        default:
            throw new Exception("Unsupported output format: " . $toFormat);
    }
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