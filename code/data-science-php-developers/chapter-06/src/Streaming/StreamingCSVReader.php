<?php

declare(strict_types=1);

namespace DataScience\Streaming;

use Generator;

class StreamingCSVReader
{
    /**
     * Read CSV file line by line using generator
     */
    public function readFile(
        string $filename,
        bool $hasHeader = true,
        string $delimiter = ',',
        string $enclosure = '"',
        bool $skipErrors = false,
        string $escape = '\\'
    ): Generator {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException("File not found: {$filename}");
        }
        
        $handle = fopen($filename, 'r');
        
        if ($handle === false) {
            throw new \RuntimeException("Cannot open file: {$filename}");
        }
        
        try {
            $header = null;
            $lineNumber = 0;
            
            // Read header if present
            if ($hasHeader) {
                $header = fgetcsv($handle, 0, $delimiter, $enclosure, $escape);
                $lineNumber++;
            }
            
            // Yield each row
            while (($row = fgetcsv($handle, 0, $delimiter, $enclosure, $escape)) !== false) {
                $lineNumber++;
                
                // Skip empty rows
                if ($row === [null]) {
                    if ($skipErrors) {
                        continue;
                    }
                    throw new \RuntimeException("Malformed line at {$lineNumber}");
                }
                
                // Convert to associative array if header exists
                if ($header !== null) {
                    if (count($row) !== count($header) && !$skipErrors) {
                        throw new \RuntimeException(
                            "Column count mismatch at line {$lineNumber}: " .
                            "expected " . count($header) . ", got " . count($row)
                        );
                    }
                    
                    // Pad or trim row to match header
                    $row = array_pad($row, count($header), null);
                    $row = array_slice($row, 0, count($header));
                    
                    yield $lineNumber => array_combine($header, $row);
                } else {
                    yield $lineNumber => $row;
                }
            }
        } finally {
            fclose($handle);
        }
    }
    
    /**
     * Read file in chunks
     */
    public function readChunks(
        string $filename,
        int $chunkSize = 1000,
        bool $hasHeader = true
    ): Generator {
        $chunk = [];
        $count = 0;
        
        foreach ($this->readFile($filename, $hasHeader) as $lineNumber => $row) {
            $chunk[] = $row;
            $count++;
            
            if ($count >= $chunkSize) {
                yield $chunk;
                $chunk = [];
                $count = 0;
            }
        }
        
        // Yield remaining rows
        if (!empty($chunk)) {
            yield $chunk;
        }
    }
    
    /**
     * Filter rows while streaming
     */
    public function filter(
        string $filename,
        callable $predicate,
        bool $hasHeader = true
    ): Generator {
        foreach ($this->readFile($filename, $hasHeader) as $lineNumber => $row) {
            if ($predicate($row)) {
                yield $lineNumber => $row;
            }
        }
    }
    
    /**
     * Transform rows while streaming
     */
    public function transform(
        string $filename,
        callable $transformer,
        bool $hasHeader = true
    ): Generator {
        foreach ($this->readFile($filename, $hasHeader) as $lineNumber => $row) {
            yield $lineNumber => $transformer($row);
        }
    }
    
    /**
     * Count rows without loading into memory
     */
    public function count(string $filename, bool $hasHeader = true): int
    {
        $count = 0;
        
        foreach ($this->readFile($filename, $hasHeader) as $row) {
            $count++;
        }
        
        return $count;
    }
    
    /**
     * Calculate statistics while streaming
     */
    public function calculateStats(
        string $filename,
        string $column,
        bool $hasHeader = true
    ): array {
        $count = 0;
        $sum = 0;
        $min = PHP_FLOAT_MAX;
        $max = PHP_FLOAT_MIN;
        $values = [];
        
        foreach ($this->readFile($filename, $hasHeader) as $row) {
            $value = (float)($row[$column] ?? 0);
            
            $count++;
            $sum += $value;
            $min = min($min, $value);
            $max = max($max, $value);
            $values[] = $value;
        }
        
        if ($count === 0) {
            return [];
        }
        
        $mean = $sum / $count;
        
        // Calculate variance
        $variance = 0;
        foreach ($values as $value) {
            $variance += ($value - $mean) ** 2;
        }
        $variance /= $count;
        
        return [
            'count' => $count,
            'sum' => $sum,
            'mean' => $mean,
            'min' => $min,
            'max' => $max,
            'variance' => $variance,
            'std_dev' => sqrt($variance),
        ];
    }
}
