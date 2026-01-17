<?php

declare(strict_types=1);

/**
 * CLI Invocation Example - PHP Runs Python
 * 
 * Demonstrates executing Python scripts from PHP.
 */

function runPythonScript(array $input): array
{
    // Encode input as JSON
    $inputJson = json_encode($input);
    $escapedInput = escapeshellarg($inputJson);
    
    // Run Python script
    $command = "python3 " . __DIR__ . "/model.py {$escapedInput} 2>&1";
    exec($command, $output, $returnCode);
    
    // Check for errors
    if ($returnCode !== 0) {
        throw new RuntimeException("Python failed: " . implode("\n", $output));
    }
    
    // Parse JSON output
    $result = json_decode(implode('', $output), true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException("Invalid JSON: " . json_last_error_msg());
    }
    
    return $result;
}

// Test the integration
echo "CLI Invocation Example\n";
echo "======================\n\n";

try {
    // Example 1
    $result1 = runPythonScript(['age' => 35, 'income' => 50000]);
    echo "Input: age=35, income=50000\n";
    echo "Score: {$result1['score']}\n\n";
    
    // Example 2
    $result2 = runPythonScript(['age' => 28, 'income' => 75000]);
    echo "Input: age=28, income=75000\n";
    echo "Score: {$result2['score']}\n\n";
    
    echo "✓ CLI invocation successful\n";
} catch (RuntimeException $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    exit(1);
}


