<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

echo "Custom Tools - File Operations\n\n";

$fileTools = [
    'read_file' => [
        'name' => 'read_file',
        'description' => 'Read contents of a file',
        'handler' => function($input) {
            $file = $input['path'];
            if (!file_exists($file)) {
                return ['error' => 'File not found'];
            }
            return ['content' => file_get_contents($file)];
        },
    ],
    'write_file' => [
        'name' => 'write_file',
        'description' => 'Write content to a file',
        'handler' => function($input) {
            file_put_contents($input['path'], $input['content']);
            return ['success' => true, 'bytes' => strlen($input['content'])];
        },
    ],
    'list_files' => [
        'name' => 'list_files',
        'description' => 'List files in a directory',
        'handler' => function($input) {
            $dir = $input['path'] ?? '.';
            if (!is_dir($dir)) {
                return ['error' => 'Not a directory'];
            }
            $files = array_diff(scandir($dir), ['.', '..']);
            return ['files' => array_values($files)];
        },
    ],
];

echo "Available file tools:\n";
foreach ($fileTools as $name => $tool) {
    echo "  - {$name}: {$tool['description']}\n";
}

// Example: List current directory
echo "\nListing current directory:\n";
$result = ($fileTools['list_files']['handler'])(['path' => __DIR__]);
foreach ($result['files'] as $file) {
    echo "  - {$file}\n";
}
