<?php

declare(strict_types=1);

/**
 * Exercise 1: File Manager Class
 * 
 * Build a file manager with common operations:
 * - Read/write files
 * - Copy/move files
 * - Delete files/directories
 * - Get file information
 * - Search files
 */

echo "=== File Manager Demo ===" . PHP_EOL . PHP_EOL;

class FileManager
{
    private string $baseDir;

    public function __construct(string $baseDir = '.')
    {
        $this->baseDir = realpath($baseDir);
    }

    /**
     * Read file contents
     */
    public function read(string $path): string
    {
        $fullPath = $this->getFullPath($path);

        if (!file_exists($fullPath)) {
            throw new RuntimeException("File not found: $path");
        }

        if (!is_readable($fullPath)) {
            throw new RuntimeException("File not readable: $path");
        }

        return file_get_contents($fullPath);
    }

    /**
     * Write content to file
     */
    public function write(string $path, string $content, bool $append = false): bool
    {
        $fullPath = $this->getFullPath($path);
        $flags = $append ? FILE_APPEND : 0;

        $result = file_put_contents($fullPath, $content, $flags);

        if ($result === false) {
            throw new RuntimeException("Failed to write to file: $path");
        }

        return true;
    }

    /**
     * Copy file
     */
    public function copy(string $source, string $destination): bool
    {
        $sourcePath = $this->getFullPath($source);
        $destPath = $this->getFullPath($destination);

        if (!file_exists($sourcePath)) {
            throw new RuntimeException("Source file not found: $source");
        }

        if (!copy($sourcePath, $destPath)) {
            throw new RuntimeException("Failed to copy file");
        }

        return true;
    }

    /**
     * Move/rename file
     */
    public function move(string $source, string $destination): bool
    {
        $sourcePath = $this->getFullPath($source);
        $destPath = $this->getFullPath($destination);

        if (!file_exists($sourcePath)) {
            throw new RuntimeException("Source file not found: $source");
        }

        if (!rename($sourcePath, $destPath)) {
            throw new RuntimeException("Failed to move file");
        }

        return true;
    }

    /**
     * Delete file
     */
    public function delete(string $path): bool
    {
        $fullPath = $this->getFullPath($path);

        if (!file_exists($fullPath)) {
            throw new RuntimeException("File not found: $path");
        }

        if (is_dir($fullPath)) {
            throw new RuntimeException("Cannot delete directory with delete() - use deleteDirectory()");
        }

        if (!unlink($fullPath)) {
            throw new RuntimeException("Failed to delete file: $path");
        }

        return true;
    }

    /**
     * Create directory
     */
    public function createDirectory(string $path, int $permissions = 0755, bool $recursive = true): bool
    {
        $fullPath = $this->getFullPath($path);

        if (is_dir($fullPath)) {
            return true;
        }

        if (!mkdir($fullPath, $permissions, $recursive)) {
            throw new RuntimeException("Failed to create directory: $path");
        }

        return true;
    }

    /**
     * Delete directory recursively
     */
    public function deleteDirectory(string $path): bool
    {
        $fullPath = $this->getFullPath($path);

        if (!is_dir($fullPath)) {
            throw new RuntimeException("Directory not found: $path");
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($fullPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $func = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $func($fileinfo->getRealPath());
        }

        return rmdir($fullPath);
    }

    /**
     * Get file information
     */
    public function getInfo(string $path): array
    {
        $fullPath = $this->getFullPath($path);

        if (!file_exists($fullPath)) {
            throw new RuntimeException("File not found: $path");
        }

        return [
            'name' => basename($fullPath),
            'path' => $fullPath,
            'size' => filesize($fullPath),
            'size_human' => $this->formatBytes(filesize($fullPath)),
            'type' => is_dir($fullPath) ? 'directory' : 'file',
            'extension' => pathinfo($fullPath, PATHINFO_EXTENSION),
            'modified' => filemtime($fullPath),
            'modified_formatted' => date('Y-m-d H:i:s', filemtime($fullPath)),
            'permissions' => substr(sprintf('%o', fileperms($fullPath)), -4),
            'readable' => is_readable($fullPath),
            'writable' => is_writable($fullPath),
        ];
    }

    /**
     * List files in directory
     */
    public function listFiles(string $path = '.', string $pattern = '*'): array
    {
        $fullPath = $this->getFullPath($path);

        if (!is_dir($fullPath)) {
            throw new RuntimeException("Not a directory: $path");
        }

        $files = glob($fullPath . '/' . $pattern);

        return array_map(function ($file) use ($fullPath) {
            return str_replace($fullPath . '/', '', $file);
        }, $files);
    }

    /**
     * Search for files by name
     */
    public function search(string $directory, string $pattern): array
    {
        $fullPath = $this->getFullPath($directory);
        $results = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($fullPath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (fnmatch($pattern, $file->getFilename())) {
                $results[] = str_replace($this->baseDir . '/', '', $file->getPathname());
            }
        }

        return $results;
    }

    /**
     * Check if file exists
     */
    public function exists(string $path): bool
    {
        return file_exists($this->getFullPath($path));
    }

    /**
     * Get file size in bytes
     */
    public function getSize(string $path): int
    {
        $fullPath = $this->getFullPath($path);

        if (!file_exists($fullPath)) {
            throw new RuntimeException("File not found: $path");
        }

        return filesize($fullPath);
    }

    /**
     * Get full path
     */
    private function getFullPath(string $path): string
    {
        if (str_starts_with($path, '/')) {
            return $path;
        }

        return $this->baseDir . '/' . $path;
    }

    /**
     * Format bytes to human-readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

// Test the File Manager
$fm = new FileManager(__DIR__);

// Create test directory
echo "1. Creating Test Directory:" . PHP_EOL;
$fm->createDirectory('test_files');
echo "✓ Directory created: test_files/" . PHP_EOL;
echo PHP_EOL;

// Write files
echo "2. Writing Files:" . PHP_EOL;
$fm->write('test_files/file1.txt', 'Hello, World!');
$fm->write('test_files/file2.txt', 'PHP File Manager');
$fm->write('test_files/data.json', '{"name": "Test", "value": 123}');
echo "✓ Created 3 files" . PHP_EOL;
echo PHP_EOL;

// Read file
echo "3. Reading File:" . PHP_EOL;
$content = $fm->read('test_files/file1.txt');
echo "  Content: $content" . PHP_EOL;
echo PHP_EOL;

// Get file info
echo "4. File Information:" . PHP_EOL;
$info = $fm->getInfo('test_files/file1.txt');
echo "  Name: {$info['name']}" . PHP_EOL;
echo "  Size: {$info['size_human']}" . PHP_EOL;
echo "  Modified: {$info['modified_formatted']}" . PHP_EOL;
echo "  Permissions: {$info['permissions']}" . PHP_EOL;
echo PHP_EOL;

// List files
echo "5. Listing Files:" . PHP_EOL;
$files = $fm->listFiles('test_files');
echo "  Found " . count($files) . " files:" . PHP_EOL;
foreach ($files as $file) {
    echo "  - $file" . PHP_EOL;
}
echo PHP_EOL;

// Copy file
echo "6. Copying File:" . PHP_EOL;
$fm->copy('test_files/file1.txt', 'test_files/file1_copy.txt');
echo "✓ File copied" . PHP_EOL;
echo PHP_EOL;

// Search for files
echo "7. Searching Files:" . PHP_EOL;
$results = $fm->search('test_files', '*.txt');
echo "  Found " . count($results) . " .txt files" . PHP_EOL;
echo PHP_EOL;

// Append to file
echo "8. Appending to File:" . PHP_EOL;
$fm->write('test_files/file1.txt', "\nAppended line!", true);
echo "✓ Content appended" . PHP_EOL;
echo "  New content: " . $fm->read('test_files/file1.txt') . PHP_EOL;
echo PHP_EOL;

// Move file
echo "9. Moving File:" . PHP_EOL;
$fm->move('test_files/file2.txt', 'test_files/renamed.txt');
echo "✓ File moved/renamed" . PHP_EOL;
echo PHP_EOL;

// Clean up
echo "10. Cleaning Up:" . PHP_EOL;
$fm->deleteDirectory('test_files');
echo "✓ Test directory removed" . PHP_EOL;
echo PHP_EOL;

echo "✓ File Manager demo complete!" . PHP_EOL;
