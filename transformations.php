<?php

function applyTransformation($data, $transformation) {
    if ($transformation === 'none' || empty($transformation)) {
        return $data;
    }
    
    if (is_array($data)) {
        $result = [];
        foreach ($data as $key => $value) {
            $newKey = transformKey($key, $transformation);
            $result[$newKey] = is_array($value) 
                ? applyTransformation($value, $transformation) 
                : $value;
        }
        return $result;
    }
    
    return $data;
}

function splitToWords(string $key): array {
    $key = preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1_$2', $key);
    $key = preg_replace('/([a-z\d])([A-Z])/', '$1_$2', $key);
    $key = str_replace([' ', '-'], '_', $key);
    return explode('_', strtolower($key));
}

function transformKey($key, $transformation) {
    switch ($transformation) {
        case 'camelCase':
            $words = splitToWords($key);
            $first = array_shift($words);
            return $first . implode('', array_map('ucfirst', $words));

        case 'PascalCase':
            return implode('', array_map('ucfirst', splitToWords($key)));

        case 'snake_case':
            $key = preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1_$2', $key);
            $key = preg_replace('/([a-z\d])([A-Z])/', '$1_$2', $key);
            $key = str_replace([' ', '-'], '_', $key);
            return strtolower($key);

        case 'kebab-case':
            $key = preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1-$2', $key);
            $key = preg_replace('/([a-z\d])([A-Z])/', '$1-$2', $key);
            $key = str_replace(['_', ' '], '-', $key);
            return strtolower($key);

        case 'UPPER_CASE':
            $key = preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1_$2', $key);
            $key = preg_replace('/([a-z\d])([A-Z])/', '$1_$2', $key);
            return strtoupper(str_replace([' ', '-'], '_', $key));
        
        default:
            return $key;
    }
}

function applyValueMappings($data, $mappings) {
    if (empty($mappings)) return $data;
    
    if (is_array($data)) {
        $result = [];
        foreach ($data as $key => $value) {
            $newKey = $key;
            $newValue = $value;
            
            foreach ($mappings as $mapping) {
                if ($key === $mapping["from_key"]) {
                    $newKey = $mapping["to_key"];
                }
                if (!is_array($value) &&
                    !empty($mapping["from_value"]) &&
                    $value === $mapping["from_value"]) {
                    $newValue = $mapping["to_value"];
                }
            }
            
            $result[$newKey] = is_array($value) 
                ? applyValueMappings($value, $mappings) 
                : $newValue;
        }
        return $result;
    }
    
    return $data;
}