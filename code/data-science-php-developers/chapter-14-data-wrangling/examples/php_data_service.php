<?php
/**
 * PHP-pandas Integration: Data Wrangling Service
 * Demonstrates calling pandas for heavy data processing from PHP
 */
declare(strict_types=1);

class DataWranglingService
{
    private string $pythonPath = 'python3';
    private string $scriptDir;

    public function __construct(string $scriptDir = __DIR__)
    {
        $this->scriptDir = $scriptDir;
    }

    /**
     * Clean a messy CSV file using pandas
     */
    public function cleanDataset(string $inputFile, string $outputFile): array
    {
        $payload = json_encode([
            'task' => 'clean',
            'input_file' => $inputFile,
            'output_file' => $outputFile
        ]);

        $result = $this->callPython('data_cleaner.py', $payload);
        
        return $result;
    }

    /**
     * Perform complex aggregations
     */
    public function analyzeData(string $csvFile, array $groupBy, array $aggregations): array
    {
        $payload = json_encode([
            'task' => 'aggregate',
            'file' => $csvFile,
            'group_by' => $groupBy,
            'aggregations' => $aggregations
        ]);

        return $this->callPython('data_analyzer.py', $payload);
    }

    /**
     * Merge multiple datasets
     */
    public function mergeDatasets(array $files, string $joinColumn, string $outputFile): array
    {
        $payload = json_encode([
            'task' => 'merge',
            'files' => $files,
            'join_column' => $joinColumn,
            'output_file' => $outputFile
        ]);

        return $this->callPython('data_merger.py', $payload);
    }

    /**
     * Get dataset statistics
     */
    public function getStatistics(string $csvFile): array
    {
        $payload = json_encode([
            'task' => 'stats',
            'file' => $csvFile
        ]);

        return $this->callPython('data_stats.py', $payload);
    }

    /**
     * Call Python script with payload
     */
    private function callPython(string $script, string $payload): array
    {
        $scriptPath = $this->scriptDir . '/' . $script;
        
        $descriptors = [
            0 => ['pipe', 'r'], // stdin
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w']  // stderr
        ];

        $process = proc_open(
            "{$this->pythonPath} {$scriptPath}",
            $descriptors,
            $pipes
        );

        if (!is_resource($process)) {
            throw new \RuntimeException("Failed to start Python process");
        }

        // Send payload
        fwrite($pipes[0], $payload);
        fclose($pipes[0]);

        // Get output
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $returnCode = proc_close($process);

        if ($returnCode !== 0) {
            throw new \RuntimeException("Python error: " . $stderr);
        }

        $result = json_decode($stdout, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Invalid JSON response: " . json_last_error_msg());
        }

        return $result;
    }
}

// Example Usage
try {
    $service = new DataWranglingService();

    echo "Data Wrangling Service Demo\n";
    echo "=" . str_repeat("=", 59) . "\n\n";

    // Example 1: Get statistics
    echo "Example 1: Getting dataset statistics\n";
    echo "-" . str_repeat("-", 59) . "\n";
    
    // Create sample data
    $sampleData = "name,age,city,salary\n";
    $sampleData .= "Alice,30,NYC,75000\n";
    $sampleData .= "Bob,25,LA,65000\n";
    $sampleData .= "Charlie,35,NYC,85000\n";
    file_put_contents('sample_data.csv', $sampleData);

    $stats = $service->getStatistics('sample_data.csv');
    echo "Statistics:\n";
    print_r($stats);

    // Example 2: Clean dataset
    echo "\nExample 2: Cleaning messy data\n";
    echo "-" . str_repeat("-", 59) . "\n";
    
    $cleanResult = $service->cleanDataset(
        'sample_data.csv',
        'cleaned_data.csv'
    );
    echo "Cleaned {$cleanResult['rows_cleaned']} rows\n";
    echo "Output: {$cleanResult['output_file']}\n";

    // Clean up
    unlink('sample_data.csv');
    if (file_exists('cleaned_data.csv')) {
        unlink('cleaned_data.csv');
    }

    echo "\n✓ All examples completed successfully!\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
