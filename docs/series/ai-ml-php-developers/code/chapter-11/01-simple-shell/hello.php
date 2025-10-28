<?php

declare(strict_types=1);

/**
 * Basic PHP-Python integration via shell execution.
 * 
 * This demonstrates the fundamental pattern:
 * 1. PHP prepares data as JSON
 * 2. PHP calls Python script with data
 * 3. Python processes and outputs JSON
 * 4. PHP parses and uses the result
 */

function callPythonScript(string $scriptPath, array $data): ?array
{
    // Step 1: Encode data as JSON
    $json = json_encode($data);
    if ($json === false) {
        throw new RuntimeException('Failed to encode data as JSON');
    }

    // Step 2: Escape for shell (CRITICAL for security)
    $escapedJson = escapeshellarg($json);

    // Step 3: Build and execute command
    $command = "python3 {$scriptPath} {$escapedJson}";
    $output = shell_exec($command);

    if ($output === null) {
        throw new RuntimeException('Failed to execute Python script');
    }

    // Step 4: Parse Python's JSON output
    $result = json_decode($output, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException('Invalid JSON from Python: ' . json_last_error_msg());
    }

    return $result;
}

// Example usage
try {
    echo "=== Basic PHP-Python Integration ===\n\n";

    // Test 1: Simple greeting
    $result1 = callPythonScript(__DIR__ . '/hello.py', ['name' => 'PHP Developer']);
    echo "Test 1 - Simple greeting:\n";
    echo "  {$result1['greeting']}\n";
    echo "  Processed by: {$result1['processed_by']}\n\n";

    // Test 2: Complex data
    $result2 = callPythonScript(__DIR__ . '/hello.py', [
        'name' => 'Machine Learning Engineer',
        'skills' => ['PHP', 'Python', 'ML'],
        'experience' => 5
    ]);
    echo "Test 2 - Complex data:\n";
    echo "  {$result2['greeting']}\n";
    echo "  Data received by Python: " . json_encode($result2['input_received']) . "\n\n";

    echo "✅ Integration working successfully!\n";
} catch (RuntimeException $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    exit(1);
}


