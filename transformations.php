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

function transformKey($key, $transformation) {
    switch ($transformation) {
        case 'camel':
            return lcfirst(str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $key))));
        
        case 'snake':
            $key = preg_replace('/[A-Z]/', '_$0', $key);
            $key = str_replace([' ', '-'], '_', $key);
            return strtolower(ltrim($key, '_'));
        
        case 'upper':
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
                // key mapping
                if ($key === $mapping['from_key']) {
                    $newKey = $mapping['to_key'];
                }
                // value mapping
                if (!is_array($value) && 
                    !empty($mapping['from_value']) && 
                    $value == $mapping['from_value']) {
                    $newValue = $mapping['to_value'];
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