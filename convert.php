<?php
require_once 'vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;

function convertContent($input, $fromFormat, $toFormat) {
    // Parse input into a PHP array
    $data = null;
    
    switch ($fromFormat) {
        case 'json':
            $data = json_decode($input, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON: " . json_last_error_msg());
            }
            break;
            
        case 'yaml':
            $data = Yaml::parse($input);
            break;
            
        case 'xml':
            $xml = simplexml_load_string($input);
            if ($xml === false) {
                throw new Exception("Invalid XML");
            }
            $data = json_decode(json_encode($xml), true);
            break;
    }
    
    // Parse output to target format
    switch ($toFormat) {
        case 'json':
            return json_encode($data, JSON_PRETTY_PRINT);
            
        case 'yaml':
            return Yaml::dump($data, 4, 2);
            
        case 'xml':
            $xml = new SimpleXMLElement('<root/>');
            arrayToXml($data, $xml);
            return $xml->asXML();
            
        case 'csv':
            return arraytoCsv($data);
            
        case 'properties':
            return arrayToProperties($data);
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