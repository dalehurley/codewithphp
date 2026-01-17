<?php

declare(strict_types=1);

namespace DataScience\Streaming;

use Generator;

/**
 * Stream large JSON files (NDJSON format - newline-delimited JSON)
 */
class StreamingJSONReader
{
    /**
     * Read NDJSON file line by line
     * Each line should be a valid JSON object
     */
    public function readFile(
        string $filename,
        bool $skipMalformed = false
    ): Generator {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException("File not found: {$filename}");
        }
        
        $handle = fopen($filename, 'r');
        
        if ($handle === false) {
            throw new \RuntimeException("Cannot open file: {$filename}");
        }
        
        try {
            $lineNumber = 0;
            
            while (($line = fgets($handle)) !== false) {
                $lineNumber++;
                $line = trim($line);
                
                // Skip empty lines
                if ($line === '') {
                    continue;
                }
                
                $decoded = json_decode($line, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    if ($skipMalformed) {
                        continue;
                    }
                    
                    throw new \RuntimeException(
                        "Invalid JSON at line {$lineNumber}: " . json_last_error_msg()
                    );
                }
                
                yield $lineNumber => $decoded;
            }
        } finally {
            fclose($handle);
        }
    }
    
    /**
     * Filter objects while streaming
     */
    public function filter(
        string $filename,
        callable $predicate,
        bool $skipMalformed = false
    ): Generator {
        foreach ($this->readFile($filename, $skipMalformed) as $lineNumber => $object) {
            if ($predicate($object)) {
                yield $lineNumber => $object;
            }
        }
    }
    
    /**
     * Transform objects while streaming
     */
    public function transform(
        string $filename,
        callable $transformer,
        bool $skipMalformed = false
    ): Generator {
        foreach ($this->readFile($filename, $skipMalformed) as $lineNumber => $object) {
            yield $lineNumber => $transformer($object);
        }
    }
    
    /**
     * Count objects without loading into memory
     */
    public function count(string $filename, bool $skipMalformed = false): int
    {
        $count = 0;
        
        foreach ($this->readFile($filename, $skipMalformed) as $object) {
            $count++;
        }
        
        return $count;
    }
    
    /**
     * Calculate statistics on a numeric field
     */
    public function calculateStats(
        string $filename,
        string $field,
        bool $skipMalformed = false
    ): array {
        $count = 0;
        $sum = 0;
        $min = PHP_FLOAT_MAX;
        $max = PHP_FLOAT_MIN;
        
        foreach ($this->readFile($filename, $skipMalformed) as $object) {
            if (!isset($object[$field])) {
                continue;
            }
            
            $value = (float)$object[$field];
            
            $count++;
            $sum += $value;
            $min = min($min, $value);
            $max = max($max, $value);
        }
        
        if ($count === 0) {
            return [];
        }
        
        return [
            'count' => $count,
            'sum' => $sum,
            'mean' => $sum / $count,
            'min' => $min,
            'max' => $max,
        ];
    }
}
