<?php

declare(strict_types=1);

namespace DataScience\Sorting;

use Generator;
use SplHeap;

/**
 * External merge sort for datasets larger than memory
 * Splits data into sorted chunks, then merges using a min-heap
 */
class ExternalSorter
{
    private string $tempDir;
    private int $chunkSize;
    
    public function __construct(int $chunkSize = 10000, ?string $tempDir = null)
    {
        $this->chunkSize = $chunkSize;
        $this->tempDir = $tempDir ?? sys_get_temp_dir();
    }
    
    /**
     * Sort large file using external merge sort
     */
    public function sort(
        string $inputFile,
        string $outputFile,
        callable $comparator
    ): void {
        // Step 1: Split into sorted chunks
        $chunkFiles = $this->createSortedChunks($inputFile, $comparator);
        
        // Step 2: Merge chunks
        $this->mergeChunks($chunkFiles, $outputFile, $comparator);
        
        // Step 3: Cleanup temporary files
        foreach ($chunkFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
    
    /**
     * Create sorted chunks from input file
     */
    private function createSortedChunks(string $inputFile, callable $comparator): array
    {
        $handle = fopen($inputFile, 'r');
        if ($handle === false) {
            throw new \RuntimeException("Cannot open input file: {$inputFile}");
        }
        
        $chunkFiles = [];
        $chunkNumber = 0;
        
        try {
            while (!feof($handle)) {
                $chunk = [];
                
                // Read chunk
                for ($i = 0; $i < $this->chunkSize && !feof($handle); $i++) {
                    $line = fgets($handle);
                    if ($line !== false) {
                        $chunk[] = trim($line);
                    }
                }
                
                if (empty($chunk)) {
                    break;
                }
                
                // Sort chunk in memory
                usort($chunk, $comparator);
                
                // Write sorted chunk to temp file
                $chunkFile = $this->tempDir . '/sort_chunk_' . $chunkNumber . '.tmp';
                file_put_contents($chunkFile, implode("\n", $chunk) . "\n");
                
                $chunkFiles[] = $chunkFile;
                $chunkNumber++;
            }
        } finally {
            fclose($handle);
        }
        
        return $chunkFiles;
    }
    
    /**
     * Merge sorted chunks using min-heap
     */
    private function mergeChunks(array $chunkFiles, string $outputFile, callable $comparator): void
    {
        // Open all chunk files
        $handles = [];
        foreach ($chunkFiles as $file) {
            $handle = fopen($file, 'r');
            if ($handle !== false) {
                $handles[] = $handle;
            }
        }
        
        if (empty($handles)) {
            touch($outputFile);
            return;
        }
        
        // Create min-heap for merging
        $heap = new class($comparator) extends SplHeap {
            private $comparator;
            
            public function __construct(callable $comparator) {
                $this->comparator = $comparator;
            }
            
            protected function compare(mixed $a, mixed $b): int {
                return -($this->comparator)($a['value'], $b['value']);
            }
        };
        
        // Initialize heap with first line from each chunk
        foreach ($handles as $index => $handle) {
            $line = fgets($handle);
            if ($line !== false) {
                $heap->insert([
                    'value' => trim($line),
                    'handle' => $handle,
                    'index' => $index,
                ]);
            }
        }
        
        // Merge to output file
        $output = fopen($outputFile, 'w');
        if ($output === false) {
            throw new \RuntimeException("Cannot open output file: {$outputFile}");
        }
        
        try {
            while (!$heap->isEmpty()) {
                $item = $heap->extract();
                
                // Write to output
                fwrite($output, $item['value'] . "\n");
                
                // Read next line from same chunk
                $line = fgets($item['handle']);
                if ($line !== false) {
                    $heap->insert([
                        'value' => trim($line),
                        'handle' => $item['handle'],
                        'index' => $item['index'],
                    ]);
                }
            }
        } finally {
            fclose($output);
            foreach ($handles as $handle) {
                fclose($handle);
            }
        }
    }
}
