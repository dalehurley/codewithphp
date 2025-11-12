<?php

declare(strict_types=1);

/**
 * Directory Operations
 * 
 * Working with directories and directory structures.
 */

echo "=== Directory Operations ===" . PHP_EOL . PHP_EOL;

// Example 1: Creating directories
echo "1. Creating Directories:" . PHP_EOL;

$dir = 'uploads';
if (!is_dir($dir)) {
    mkdir($dir);
    echo "✓ Created directory: $dir" . PHP_EOL;
}

// Create nested directories
$nested = 'data/cache/temp';
if (!is_dir($nested)) {
    mkdir($nested, 0755, true);  // recursive = true
    echo "✓ Created nested directory: $nested" . PHP_EOL;
}
echo PHP_EOL;

// Example 2: Listing directory contents
echo "2. Listing Directory Contents:" . PHP_EOL;

// Create some files
file_put_contents('uploads/file1.txt', 'content1');
file_put_contents('uploads/file2.txt', 'content2');
file_put_contents('uploads/file3.txt', 'content3');

$files = scandir('uploads');
echo "Contents of uploads/:" . PHP_EOL;
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        echo "  - $file" . PHP_EOL;
    }
}
echo PHP_EOL;

// Example 3: Recursive directory listing
echo "3. Recursive Directory Listing:" . PHP_EOL;

function listDirectory(string $dir, int $depth = 0): void
{
    $indent = str_repeat('  ', $depth);
    $files = scandir($dir);

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $path = $dir . '/' . $file;
        $type = is_dir($path) ? '[DIR]' : '[FILE]';
        echo "$indent$type $file" . PHP_EOL;

        if (is_dir($path)) {
            listDirectory($path, $depth + 1);
        }
    }
}

echo "Directory tree:" . PHP_EOL;
listDirectory('data');
echo PHP_EOL;

// Example 4: File globbing (pattern matching)
echo "4. Pattern Matching with glob():" . PHP_EOL;

// Create various files
file_put_contents('uploads/image1.jpg', '');
file_put_contents('uploads/image2.png', '');
file_put_contents('uploads/document.pdf', '');

// Find all .txt files
$txtFiles = glob('uploads/*.txt');
echo "Text files: " . implode(', ', array_map('basename', $txtFiles)) . PHP_EOL;

// Find all image files
$images = glob('uploads/*.{jpg,png}', GLOB_BRACE);
echo "Image files: " . implode(', ', array_map('basename', $images)) . PHP_EOL;
echo PHP_EOL;

// Example 5: Getting directory size
echo "5. Calculating Directory Size:" . PHP_EOL;

function getDirectorySize(string $dir): int
{
    $size = 0;

    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $file) {
        if ($file->isFile()) {
            $size += $file->getSize();
        }
    }

    return $size;
}

$dirSize = getDirectorySize('uploads');
echo "Size of uploads/: $dirSize bytes" . PHP_EOL;
echo PHP_EOL;

// Example 6: Copying and moving files
echo "6. Copying and Moving Files:" . PHP_EOL;

$source = 'uploads/file1.txt';
$dest = 'uploads/file1_copy.txt';

copy($source, $dest);
echo "✓ Copied $source to $dest" . PHP_EOL;

rename('uploads/file1_copy.txt', 'uploads/file1_renamed.txt');
echo "✓ Renamed file" . PHP_EOL;
echo PHP_EOL;

// Example 7: Checking if directory is empty
echo "7. Checking if Directory is Empty:" . PHP_EOL;

function isDirectoryEmpty(string $dir): bool
{
    if (!is_dir($dir)) {
        return false;
    }

    $files = scandir($dir);
    return count($files) === 2; // Only . and ..
}

echo "uploads/ is empty: " . (isDirectoryEmpty('uploads') ? 'Yes' : 'No') . PHP_EOL;
echo "data/cache/temp/ is empty: " . (isDirectoryEmpty('data/cache/temp') ? 'Yes' : 'No') . PHP_EOL;
echo PHP_EOL;

// Example 8: Working with paths
echo "8. Path Operations:" . PHP_EOL;

$path = 'uploads/subfolder/../file1.txt';
$realPath = realpath($path);
echo "Normalized path: $realPath" . PHP_EOL;

$info = pathinfo('uploads/image1.jpg');
echo "Filename: {$info['filename']}" . PHP_EOL;
echo "Extension: {$info['extension']}" . PHP_EOL;
echo "Directory: {$info['dirname']}" . PHP_EOL;
echo "Basename: {$info['basename']}" . PHP_EOL;
echo PHP_EOL;

// Example 9: Deleting directories (recursive)
echo "9. Deleting Directories:" . PHP_EOL;

function deleteDirectory(string $dir): bool
{
    if (!is_dir($dir)) {
        return false;
    }

    $files = array_diff(scandir($dir), ['.', '..']);

    foreach ($files as $file) {
        $path = $dir . '/' . $file;

        if (is_dir($path)) {
            deleteDirectory($path);
        } else {
            unlink($path);
        }
    }

    return rmdir($dir);
}

// Cleanup
deleteDirectory('uploads');
deleteDirectory('data');
echo "✓ Cleanup complete" . PHP_EOL;
