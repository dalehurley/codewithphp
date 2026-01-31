<?php

declare(strict_types=1);

/**
 * Built-in Tools Example
 * 
 * Demonstrates the framework's built-in tools:
 * - CalculatorTool
 * - HTTPTool (simulated)
 * - FileSystemTool
 */

// Load from the claude-php-agent project in the workspace
require_once __DIR__ . '/../../../../claude-php-agent/vendor/autoload.php';

use ClaudeAgents\Tools\BuiltIn\CalculatorTool;
use ClaudeAgents\Tools\BuiltIn\FileSystemTool;

echo "=== Built-in Tools Example ===\n\n";

// 1. Calculator Tool
echo "=== Calculator Tool ===\n";
$calculator = CalculatorTool::create([
    'allow_functions' => true,
    'max_precision' => 10,
]);

$tests = [
    '25 * 17 + 42',
    '(100 + 50) / 3',
    'sqrt(144)',
    'sin(0)',
    'pow(2, 10)',
];

foreach ($tests as $expression) {
    $result = $calculator->execute(['expression' => $expression]);
    $data = json_decode($result->getContent(), true);
    echo "Expression: {$expression}\n";
    echo "Result: " . $data['result'] . "\n";
    echo "\n";
}

// Calculator error handling
echo "Error case: Invalid expression\n";
$errorResult = $calculator->execute(['expression' => 'invalid code here']);
echo "Success: " . ($errorResult->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Error: " . $errorResult->getContent() . "\n\n";

// 2. FileSystem Tool
echo "=== FileSystem Tool ===\n";

// Create temp directory for testing
$tempDir = sys_get_temp_dir() . '/agent-test-' . uniqid();
mkdir($tempDir);

$filesystem = FileSystemTool::create([
    'allowed_paths' => [$tempDir],
    'read_only' => false,
    'max_file_size' => 10 * 1024 * 1024, // 10MB
    'allowed_extensions' => ['txt', 'json', 'log'],
]);

// Test 1: Write a file
echo "Test 1: Write file\n";
$writeResult = $filesystem->execute([
    'operation' => 'write',
    'path' => $tempDir . '/test.txt',
    'content' => 'Hello from the agent!',
]);
echo "Success: " . ($writeResult->isSuccess() ? 'Yes' : 'No') . "\n";
$writeData = json_decode($writeResult->getContent(), true);
echo "Bytes written: " . $writeData['bytes_written'] . "\n\n";

// Test 2: Read the file
echo "Test 2: Read file\n";
$readResult = $filesystem->execute([
    'operation' => 'read',
    'path' => $tempDir . '/test.txt',
]);
echo "Success: " . ($readResult->isSuccess() ? 'Yes' : 'No') . "\n";
$readData = json_decode($readResult->getContent(), true);
echo "Content: " . $readData['content'] . "\n";
echo "Size: " . $readData['size'] . " bytes\n\n";

// Test 3: Check if file exists
echo "Test 3: Check file existence\n";
$existsResult = $filesystem->execute([
    'operation' => 'exists',
    'path' => $tempDir . '/test.txt',
]);
$existsData = json_decode($existsResult->getContent(), true);
echo "Exists: " . ($existsData['exists'] ? 'Yes' : 'No') . "\n";
echo "Type: " . $existsData['type'] . "\n\n";

// Test 4: Write JSON file
echo "Test 4: Write JSON file\n";
$jsonData = json_encode([
    'agent' => 'ClaudeAgents',
    'version' => '0.5.0',
    'tools' => ['calculator', 'filesystem', 'http'],
]);
$jsonWriteResult = $filesystem->execute([
    'operation' => 'write',
    'path' => $tempDir . '/config.json',
    'content' => $jsonData,
]);
echo "Success: " . ($jsonWriteResult->isSuccess() ? 'Yes' : 'No') . "\n\n";

// Test 5: List directory
echo "Test 5: List directory\n";
$listResult = $filesystem->execute([
    'operation' => 'list',
    'path' => $tempDir,
    'recursive' => false,
]);
$listData = json_decode($listResult->getContent(), true);
echo "Files found: " . $listData['count'] . "\n";
foreach ($listData['items'] as $item) {
    echo "- {$item['name']} ({$item['type']}, {$item['size']} bytes)\n";
}
echo "\n";

// Test 6: Get file info
echo "Test 6: File info\n";
$infoResult = $filesystem->execute([
    'operation' => 'info',
    'path' => $tempDir . '/config.json',
]);
$infoData = json_decode($infoResult->getContent(), true);
echo "Path: " . $infoData['path'] . "\n";
echo "Type: " . $infoData['type'] . "\n";
echo "Size: " . $infoData['size'] . " bytes\n";
echo "Extension: " . $infoData['extension'] . "\n";
echo "Modified: " . $infoData['modified'] . "\n\n";

// Test 7: Delete file
echo "Test 7: Delete file\n";
$deleteResult = $filesystem->execute([
    'operation' => 'delete',
    'path' => $tempDir . '/test.txt',
]);
echo "Success: " . ($deleteResult->isSuccess() ? 'Yes' : 'No') . "\n";
$deleteData = json_decode($deleteResult->getContent(), true);
echo "Deleted: " . ($deleteData['deleted'] ? 'Yes' : 'No') . "\n\n";

// Test 8: Create directory
echo "Test 8: Create directory\n";
$mkdirResult = $filesystem->execute([
    'operation' => 'mkdir',
    'path' => $tempDir . '/subdirectory',
    'recursive' => false,
]);
echo "Success: " . ($mkdirResult->isSuccess() ? 'Yes' : 'No') . "\n\n";

// Test 9: Security - Access denied (path outside allowed)
echo "Test 9: Security - Access denied\n";
$deniedResult = $filesystem->execute([
    'operation' => 'read',
    'path' => '/etc/passwd', // Outside allowed paths
]);
echo "Success: " . ($deniedResult->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Error: " . $deniedResult->getContent() . "\n\n";

// Test 10: Security - Forbidden extension
echo "Test 10: Security - Forbidden extension\n";
$forbiddenResult = $filesystem->execute([
    'operation' => 'write',
    'path' => $tempDir . '/script.php', // .php not in allowed extensions
    'content' => '<?php echo "test"; ?>',
]);
echo "Success: " . ($forbiddenResult->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Error: " . $forbiddenResult->getContent() . "\n\n";

// Cleanup
echo "=== Cleanup ===\n";
$cleanupFiles = glob($tempDir . '/*');
foreach ($cleanupFiles as $file) {
    if (is_file($file)) {
        unlink($file);
    }
}
if (is_dir($tempDir . '/subdirectory')) {
    rmdir($tempDir . '/subdirectory');
}
rmdir($tempDir);
echo "Temporary directory cleaned up\n\n";

echo "✅ Built-in tools example complete!\n";
echo "\nBuilt-in Tools:\n";
echo "1. CalculatorTool - Safe math evaluation with functions\n";
echo "2. FileSystemTool - File operations with security constraints\n";
echo "3. HTTPTool - HTTP requests with domain whitelisting\n";
echo "4. DatabaseTool - Database operations with query restrictions\n";
echo "5. DateTimeTool - Date/time operations and formatting\n";
echo "6. RegexTool - Regular expression matching and replacement\n";
