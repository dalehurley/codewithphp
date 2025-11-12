<?php

declare(strict_types=1);

/**
 * File Operations - Reading and Writing
 * 
 * Demonstrates common file operations in PHP.
 */

echo "=== File Operations ===" . PHP_EOL . PHP_EOL;

// Example 1: Writing to a file
echo "1. Writing to Files:" . PHP_EOL;

$filename = 'test.txt';
$content = "Hello, World!\nThis is line 2.\nThis is line 3.";

file_put_contents($filename, $content);
echo "✓ Written to $filename" . PHP_EOL;
echo PHP_EOL;

// Example 2: Reading entire file
echo "2. Reading Entire File:" . PHP_EOL;

$contents = file_get_contents($filename);
echo "File contents:" . PHP_EOL;
echo $contents . PHP_EOL;
echo PHP_EOL;

// Example 3: Reading file into array
echo "3. Reading File into Array:" . PHP_EOL;

$lines = file($filename);
echo "Line by line:" . PHP_EOL;
foreach ($lines as $lineNum => $line) {
    echo "Line " . ($lineNum + 1) . ": " . trim($line) . PHP_EOL;
}
echo PHP_EOL;

// Example 4: Appending to file
echo "4. Appending to File:" . PHP_EOL;

file_put_contents($filename, "\nAppended line 4.", FILE_APPEND);
echo "✓ Appended to $filename" . PHP_EOL;
echo file_get_contents($filename) . PHP_EOL;
echo PHP_EOL;

// Example 5: File operations with fopen
echo "5. File Handle Operations:" . PHP_EOL;

$handle = fopen('log.txt', 'w');
if ($handle) {
    fwrite($handle, date('Y-m-d H:i:s') . " - Application started\n");
    fwrite($handle, date('Y-m-d H:i:s') . " - User logged in\n");
    fwrite($handle, date('Y-m-d H:i:s') . " - Process completed\n");
    fclose($handle);
    echo "✓ Log written" . PHP_EOL;
}
echo PHP_EOL;

// Example 6: Reading file line by line (memory efficient)
echo "6. Reading Line by Line:" . PHP_EOL;

$handle = fopen('log.txt', 'r');
if ($handle) {
    $lineNum = 1;
    while (($line = fgets($handle)) !== false) {
        echo "[$lineNum] " . trim($line) . PHP_EOL;
        $lineNum++;
    }
    fclose($handle);
}
echo PHP_EOL;

// Example 7: File existence and information
echo "7. File Information:" . PHP_EOL;

if (file_exists($filename)) {
    echo "File: $filename" . PHP_EOL;
    echo "Size: " . filesize($filename) . " bytes" . PHP_EOL;
    echo "Last modified: " . date('Y-m-d H:i:s', filemtime($filename)) . PHP_EOL;
    echo "Is readable: " . (is_readable($filename) ? 'Yes' : 'No') . PHP_EOL;
    echo "Is writable: " . (is_writable($filename) ? 'Yes' : 'No') . PHP_EOL;
}
echo PHP_EOL;

// Example 8: CSV file operations
echo "8. CSV File Operations:" . PHP_EOL;

$csvFile = 'users.csv';
$users = [
    ['Name', 'Email', 'Age'],
    ['Alice', 'alice@example.com', 28],
    ['Bob', 'bob@example.com', 35],
    ['Charlie', 'charlie@example.com', 42]
];

// Write CSV
$handle = fopen($csvFile, 'w');
foreach ($users as $user) {
    fputcsv($handle, $user);
}
fclose($handle);
echo "✓ CSV written" . PHP_EOL;

// Read CSV
$handle = fopen($csvFile, 'r');
echo "CSV contents:" . PHP_EOL;
while (($data = fgetcsv($handle)) !== false) {
    echo "  " . implode(' | ', $data) . PHP_EOL;
}
fclose($handle);
echo PHP_EOL;

// Example 9: JSON file operations
echo "9. JSON File Operations:" . PHP_EOL;

$data = [
    'app_name' => 'My App',
    'version' => '1.0.0',
    'settings' => [
        'debug' => true,
        'timezone' => 'UTC'
    ]
];

// Write JSON
file_put_contents('config.json', json_encode($data, JSON_PRETTY_PRINT));
echo "✓ JSON written" . PHP_EOL;

// Read JSON
$loaded = json_decode(file_get_contents('config.json'), true);
echo "Loaded config:" . PHP_EOL;
echo "  App: {$loaded['app_name']}" . PHP_EOL;
echo "  Version: {$loaded['version']}" . PHP_EOL;
echo "  Debug: " . ($loaded['settings']['debug'] ? 'On' : 'Off') . PHP_EOL;
echo PHP_EOL;

// Example 10: Safe file operations with error handling
echo "10. Safe File Operations:" . PHP_EOL;

function safeReadFile(string $path): ?string
{
    if (!file_exists($path)) {
        return null;
    }

    $contents = @file_get_contents($path);
    if ($contents === false) {
        return null;
    }

    return $contents;
}

function safeWriteFile(string $path, string $data): bool
{
    $result = @file_put_contents($path, $data);
    return $result !== false;
}

if (safeWriteFile('safe.txt', 'Safe content')) {
    echo "✓ Write successful" . PHP_EOL;
    $content = safeReadFile('safe.txt');
    echo "✓ Read successful: $content" . PHP_EOL;
}
echo PHP_EOL;

// Cleanup
$files = ['test.txt', 'log.txt', 'users.csv', 'config.json', 'safe.txt'];
foreach ($files as $file) {
    if (file_exists($file)) {
        unlink($file);
    }
}
echo "✓ Cleanup complete" . PHP_EOL;
