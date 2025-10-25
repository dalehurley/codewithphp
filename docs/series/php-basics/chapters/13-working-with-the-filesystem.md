---
title: "13: Working with the Filesystem"
description: "Learn how to read, write, and manage files and directories on the server, a fundamental skill for everything from logging to storing user data."
series: "php-basics"
chapter: 13
order: 13
difficulty: "Beginner"
prerequisites:
  - "/series/php-basics/chapters/12-dependency-management-with-composer"
---

# Chapter 13: Working with the Filesystem

## Overview

Most web applications need to interact with the server's filesystem in some way. You might need to read a configuration file, write a log entry, save a user's uploaded avatar, or store cached data to improve performance.

PHP provides a simple and powerful set of functions for working with files and directories. In this chapter, you'll master the essentials of reading from and writing to files, checking if files exist, handling errors safely, and working with structured data formats like JSON. By the end, you'll have built a working file-based logging system and a JSON configuration manager.

## Prerequisites

Before starting this chapter, you should have:

- PHP 8.4 installed and working from the command line
- Completed Chapter 12 (Dependency Management with Composer)
- A text editor and terminal ready
- Basic understanding of arrays and functions
- **Estimated time**: 35–40 minutes

## What You'll Build

By the end of this chapter, you will have:

- A working file existence checker that safely validates files and directories
- A simple text file reader/writer that creates, overwrites, and appends content
- A JSON-based configuration system that stores and retrieves structured data
- A practical logging function that writes timestamped entries to a log file
- A file management system that copies, moves, renames, and deletes files
- Directory listing tools using both `scandir()` and `glob()` pattern matching
- Path parsing utilities for safe file path manipulation
- An efficient large file reader that processes logs line-by-line
- Understanding of file permissions and common error handling patterns

## Quick Start

If you want to see filesystem operations in action immediately, create a file called `quick-demo.php`:

```php
<?php
// filename: quick-demo.php
// Quick filesystem demo

// Create a data directory
@mkdir('data', 0755, true);

// Write some data
file_put_contents('data/demo.txt', "Hello, filesystem!\n");

// Read it back
$content = file_get_contents('data/demo.txt');
echo $content;

// Create JSON data
$data = ['name' => 'PHP', 'version' => '8.4'];
file_put_contents('data/demo.json', json_encode($data, JSON_PRETTY_PRINT));

// Read and decode JSON
$loaded = json_decode(file_get_contents('data/demo.json'), true);
echo "Language: {$loaded['name']} {$loaded['version']}\n";
```

Run it:

```bash
# Run the quick demo
php quick-demo.php
```

Expected output:

```
Hello, filesystem!
Language: PHP 8.4
```

Now let's explore each concept in depth.

## Objectives

- Check for the existence of files and directories before operations
- Read the entire contents of a file into a string
- Write and append content to files safely
- Understand file permissions and common permission issues
- Handle filesystem errors gracefully
- Encode PHP arrays into JSON strings and decode JSON back into PHP
- Manage directories: create, list, and navigate directory structures
- Perform file operations: copy, move, rename, and delete files
- Parse and manipulate file paths safely
- Read large files efficiently without exhausting memory
- Build practical, reusable filesystem utilities

## Step 1: Checking for Files and Directories (~4 min)

### Goal

Learn to safely check whether files and directories exist before attempting to read or write them, preventing common errors.

### Actions

1. **Set up the Project Structure**

Create a new directory for this chapter's work:

```bash
# Create project directory and navigate to it
mkdir -p filesystem-tutorial/data
cd filesystem-tutorial
```

2. **Create the Main Script**

Create a new file called `check-files.php`:

```php
<?php
// filename: check-files.php
// Demonstrates file and directory existence checks

$filePath = 'data/notes.txt';
$fakePath = 'data/fake-file.txt';
$dirPath = 'data';

echo "=== File Existence Checks ===" . PHP_EOL . PHP_EOL;

// 1. Check if a file exists
if (file_exists($filePath)) {
    echo "✓ '$filePath' exists." . PHP_EOL;
} else {
    echo "✗ '$filePath' does not exist." . PHP_EOL;
}

if (file_exists($fakePath)) {
    echo "✓ '$fakePath' exists." . PHP_EOL;
} else {
    echo "✗ '$fakePath' does not exist." . PHP_EOL;
}

echo PHP_EOL;

// 2. Check if a path is a directory
if (is_dir($dirPath)) {
    echo "✓ '$dirPath' is a directory." . PHP_EOL;
} else {
    echo "✗ '$dirPath' is not a directory." . PHP_EOL;
}

// 3. Check if a path is a file (not a directory)
if (is_file($filePath)) {
    echo "✓ '$filePath' is a file." . PHP_EOL;
} else {
    echo "✗ '$filePath' is not a file." . PHP_EOL;
}

echo PHP_EOL;

// 4. More detailed checks
if (file_exists($dirPath) && is_dir($dirPath)) {
    echo "✓ '$dirPath' exists and is a directory." . PHP_EOL;
}

if (file_exists($filePath) && is_readable($filePath)) {
    echo "✓ '$filePath' exists and is readable." . PHP_EOL;
} else {
    echo "✗ '$filePath' does not exist or is not readable." . PHP_EOL;
}
```

3. **Create the Test File**

```bash
# Create an empty notes.txt file
touch data/notes.txt
```

4. **Run the Script**

```bash
# Execute the file checker
php check-files.php
```

### Expected Result

You should see output like this:

```
=== File Existence Checks ===

✓ 'data/notes.txt' exists.
✗ 'data/fake-file.txt' does not exist.

✓ 'data' is a directory.
✓ 'data/notes.txt' is a file.

✓ 'data' exists and is a directory.
✓ 'data/notes.txt' exists and is readable.
```

### Why It Works

- **`file_exists($path)`**: Returns `true` if the file or directory exists at the given path. This is the most common check and works for both files and directories.
- **`is_dir($path)`**: Returns `true` only if the path exists and is a directory.
- **`is_file($path)`**: Returns `true` only if the path exists and is a regular file (not a directory).
- **`is_readable($path)`**: Returns `true` if the file exists and the current user has permission to read it.

These checks prevent errors like "file not found" or "failed to open stream" that occur when you try to read or write non-existent files.

### Troubleshooting

**Problem**: All checks show files don't exist, even though you created them.

**Solution**: Make sure you're running the script from the correct directory. Use `pwd` (Unix/macOS) or `cd` (Windows) to verify your current location. The paths in the script are relative to where you run it.

**Problem**: File exists but shows as not readable.

**Solution**: Check file permissions with `ls -la data/notes.txt` (Unix/macOS) or use File Explorer properties (Windows). You may need to adjust permissions with `chmod 644 data/notes.txt`.

## Step 2: Reading and Writing Files (~6 min)

### Goal

Master the essential operations of writing content to files, reading file contents, and appending data without overwriting existing content.

### Actions

1. **Create the Read/Write Script**

Create a new file called `read-write.php`:

```php
<?php
// filename: read-write.php
// Demonstrates reading and writing files

$filePath = 'data/notes.txt';

echo "=== File Read/Write Operations ===" . PHP_EOL . PHP_EOL;

// 1. Write content to a file
// This will create the file if it doesn't exist, or
// COMPLETELY OVERWRITE it if it does
$contentToWrite = "This is the first line." . PHP_EOL;
$bytesWritten = file_put_contents($filePath, $contentToWrite);

if ($bytesWritten !== false) {
    echo "✓ Wrote $bytesWritten bytes to file." . PHP_EOL;
} else {
    echo "✗ Failed to write to file." . PHP_EOL;
    exit(1);
}

echo PHP_EOL;

// 2. Read the entire file into a string
$contentRead = file_get_contents($filePath);

if ($contentRead !== false) {
    echo "✓ Read from file:" . PHP_EOL;
    echo "  Content: " . $contentRead;
} else {
    echo "✗ Failed to read file." . PHP_EOL;
    exit(1);
}

echo PHP_EOL;

// 3. Append content to a file
// Use the FILE_APPEND flag to avoid overwriting
$contentToAppend = "This is the second line." . PHP_EOL;
$bytesAppended = file_put_contents($filePath, $contentToAppend, FILE_APPEND);

if ($bytesAppended !== false) {
    echo "✓ Appended $bytesAppended bytes to file." . PHP_EOL;
} else {
    echo "✗ Failed to append to file." . PHP_EOL;
    exit(1);
}

echo PHP_EOL;

// 4. Read the updated file
$contentReadAgain = file_get_contents($filePath);
echo "✓ Final file contents:" . PHP_EOL;
echo $contentReadAgain;

echo PHP_EOL;

// 5. Demonstrate the danger of overwriting
echo "--- Overwrite Warning Demo ---" . PHP_EOL;
$dangerousContent = "OOPS! Everything was replaced." . PHP_EOL;
file_put_contents($filePath, $dangerousContent);
echo "After overwrite: " . file_get_contents($filePath);
```

2. **Run the Script**

```bash
# Execute the read/write script
php read-write.php
```

### Expected Result

```
=== File Read/Write Operations ===

✓ Wrote 24 bytes to file.

✓ Read from file:
  Content: This is the first line.

✓ Appended 25 bytes to file.

✓ Final file contents:
This is the first line.
This is the second line.

--- Overwrite Warning Demo ---
After overwrite: OOPS! Everything was replaced.
```

### Why It Works

- **`file_put_contents($path, $data)`**: Opens the file, writes the data, and closes it in one operation. If the file doesn't exist, it's created. If it does exist, it's completely overwritten. Returns the number of bytes written, or `false` on failure.

- **`file_get_contents($path)`**: Opens the file, reads the entire contents into a string, and closes it. Returns the file contents as a string, or `false` on failure.

- **`FILE_APPEND` flag**: When passed as the third argument to `file_put_contents()`, this flag tells PHP to add the new content to the end of the file instead of replacing it.

- **`PHP_EOL`**: A constant that contains the correct line ending for your operating system (`\n` on Unix/macOS, `\r\n` on Windows).

These functions are convenience wrappers around lower-level file operations (`fopen`, `fwrite`, `fread`, `fclose`), making common tasks much simpler.

### Troubleshooting

**Problem**: `file_put_contents()` returns `false` and nothing is written.

**Solutions**:

1. **Check directory permissions**: The `data/` directory must be writable. On Unix/macOS, run `chmod 755 data/` or `chmod 777 data/` for testing (never use 777 in production).
2. **Check if directory exists**: If `data/` doesn't exist, create it first with `mkdir data`.
3. **Check disk space**: Ensure your disk isn't full by running `df -h` (Unix/macOS) or checking drive properties (Windows).

**Problem**: `file_get_contents()` returns `false`.

**Solutions**:

1. **Verify the file exists**: Use `file_exists()` before reading.
2. **Check read permissions**: Make sure the file is readable with `is_readable($filePath)`.
3. **Check the path**: Ensure you're using the correct relative or absolute path.

**Problem**: Content is being overwritten instead of appended.

**Solution**: Always use the `FILE_APPEND` flag as the third argument: `file_put_contents($path, $data, FILE_APPEND)`.

> **Note on Permissions**: For PHP to write a file, the user running the script (or the web server user, typically `www-data` or `apache` for web applications) must have write permissions on both the file and its parent directory. When developing locally from the command line, this is usually your own user account and works without issues. In production web environments, you may need to explicitly set permissions.

## Step 3: Working with JSON (~7 min)

### Goal

Learn to store and retrieve structured data using JSON, enabling you to save complex arrays and objects to files and read them back reliably.

### Actions

1. **Create the JSON Script**

Create a new file called `json-demo.php`:

```php
<?php
// filename: json-demo.php
// Demonstrates JSON encoding and decoding with files

echo "=== JSON File Operations ===" . PHP_EOL . PHP_EOL;

// 1. Create structured data as a PHP array
$users = [
    [
        'id' => 1,
        'name' => 'Dale Hurley',
        'email' => 'dale@example.com',
        'active' => true,
    ],
    [
        'id' => 2,
        'name' => 'Alice Johnson',
        'email' => 'alice@example.com',
        'active' => false,
    ],
    [
        'id' => 3,
        'name' => 'Bob Smith',
        'email' => 'bob@example.com',
        'active' => true,
    ],
];

$jsonFilePath = 'data/users.json';

// 2. Encode the PHP array into a JSON string
// JSON_PRETTY_PRINT makes the output human-readable
$jsonString = json_encode($users, JSON_PRETTY_PRINT);

if ($jsonString === false) {
    echo "✗ Failed to encode JSON: " . json_last_error_msg() . PHP_EOL;
    exit(1);
}

// 3. Save the JSON string to a file
$bytesWritten = file_put_contents($jsonFilePath, $jsonString);

if ($bytesWritten !== false) {
    echo "✓ Saved $bytesWritten bytes to '$jsonFilePath'" . PHP_EOL;
} else {
    echo "✗ Failed to write JSON file." . PHP_EOL;
    exit(1);
}

echo PHP_EOL;

// 4. Read the JSON string from the file
$jsonData = file_get_contents($jsonFilePath);

if ($jsonData === false) {
    echo "✗ Failed to read JSON file." . PHP_EOL;
    exit(1);
}

// 5. Decode the JSON string back into a PHP array
// The second argument `true` returns an associative array
// Without it, it would return an array of stdClass objects
$decodedUsers = json_decode($jsonData, true);

if ($decodedUsers === null) {
    echo "✗ Failed to decode JSON: " . json_last_error_msg() . PHP_EOL;
    exit(1);
}

echo "✓ Successfully decoded " . count($decodedUsers) . " users" . PHP_EOL;
echo PHP_EOL;

// 6. Work with the decoded data
echo "--- User Details ---" . PHP_EOL;
foreach ($decodedUsers as $user) {
    $status = $user['active'] ? 'Active' : 'Inactive';
    echo "• {$user['name']} ({$user['email']}) - $status" . PHP_EOL;
}

echo PHP_EOL;

// 7. Access specific data
echo "--- Accessing Specific Data ---" . PHP_EOL;
echo "First user's email: {$decodedUsers[0]['email']}" . PHP_EOL;
echo "Last user's name: {$decodedUsers[2]['name']}" . PHP_EOL;

// 8. Count active users
$activeCount = count(array_filter($decodedUsers, fn($u) => $u['active']));
echo "Active users: $activeCount" . PHP_EOL;
```

2. **Run the Script**

```bash
# Execute the JSON demo
php json-demo.php
```

3. **Inspect the Generated File**

```bash
# View the generated JSON file
cat data/users.json
```

### Expected Result

Running the script should produce:

```
=== JSON File Operations ===

✓ Saved 312 bytes to 'data/users.json'

✓ Successfully decoded 3 users

--- User Details ---
• Dale Hurley (dale@example.com) - Active
• Alice Johnson (alice@example.com) - Inactive
• Bob Smith (bob@example.com) - Active

--- Accessing Specific Data ---
First user's email: dale@example.com
Last user's name: Bob Smith
Active users: 2
```

The `data/users.json` file should contain:

```json
[
  {
    "id": 1,
    "name": "Dale Hurley",
    "email": "dale@example.com",
    "active": true
  },
  {
    "id": 2,
    "name": "Alice Johnson",
    "email": "alice@example.com",
    "active": false
  },
  {
    "id": 3,
    "name": "Bob Smith",
    "email": "bob@example.com",
    "active": true
  }
]
```

### Why It Works

- **`json_encode($data, $flags)`**: Converts PHP arrays, objects, and primitive values into a JSON-formatted string. Returns `false` on failure. Common flags:

  - `JSON_PRETTY_PRINT`: Adds whitespace for readability
  - `JSON_UNESCAPED_SLASHES`: Prevents escaping forward slashes
  - `JSON_UNESCAPED_UNICODE`: Preserves Unicode characters

- **`json_decode($json, $associative, $depth, $flags)`**: Converts a JSON string back into PHP data structures. The second parameter (`true`) is crucial—it determines whether you get associative arrays or objects:

  - `json_decode($json, true)` → associative arrays (easier to work with)
  - `json_decode($json, false)` or `json_decode($json)` → stdClass objects

- **Error handling**: Both functions can fail. Use `json_last_error_msg()` to get human-readable error messages when they return `false` or `null`.

JSON is the standard for data interchange, used by REST APIs, configuration files, and data storage. It supports strings, numbers, booleans, null, arrays, and objects—covering most data needs.

### Troubleshooting

**Problem**: `json_encode()` returns `false`.

**Solutions**:

1. **Check for invalid UTF-8**: JSON requires valid UTF-8. If your data has encoding issues, use `utf8_encode()` or the `JSON_INVALID_UTF8_SUBSTITUTE` flag.
2. **Check recursion depth**: Deeply nested arrays may exceed limits. Use the third parameter to increase depth: `json_encode($data, 0, 512)`.
3. **Check for resources**: JSON cannot encode PHP resources (like file handles or database connections).

**Problem**: `json_decode()` returns `null` and the data looks valid.

**Solutions**:

1. **Always check errors**: Use `json_last_error_msg()` to see the actual problem.
2. **Validate JSON syntax**: Use an online JSON validator or `json_last_error()` to identify syntax errors in the JSON string.
3. **Check for BOM**: Files with a UTF-8 BOM (Byte Order Mark) can cause parsing issues. Remove the BOM or use `trim($jsonData, "\xEF\xBB\xBF")`.

**Problem**: Getting objects instead of arrays after `json_decode()`.

**Solution**: Pass `true` as the second argument: `json_decode($json, true)`.

**Problem**: Special characters are escaped (`\/` or `\u0000`).

**Solution**: Use flags to control encoding: `json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)`.

## Step 4: Practical Application - Building a Logger (~5 min)

### Goal

Create a reusable logging function that demonstrates real-world filesystem usage by appending timestamped log entries to a file.

### Actions

1. **Create the Logger Script**

Create a file called `logger.php`:

```php
<?php
// filename: logger.php
// A simple but practical file-based logger

function logMessage(string $level, string $message): void
{
    $logFile = 'data/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;

    // Ensure the data directory exists
    if (!is_dir('data')) {
        mkdir('data', 0755, true);
    }

    // Append the log entry
    $result = file_put_contents($logFile, $logEntry, FILE_APPEND);

    if ($result === false) {
        error_log("Failed to write to log file: $logFile");
    }
}

// Test the logger
echo "=== Testing Logger ===" . PHP_EOL . PHP_EOL;

logMessage('INFO', 'Application started');
logMessage('INFO', 'User authentication successful');
logMessage('WARNING', 'Deprecated function called in legacy code');
logMessage('ERROR', 'Database connection failed');
logMessage('INFO', 'Application shutdown gracefully');

echo "✓ Log entries written to data/app.log" . PHP_EOL;
echo PHP_EOL;

// Read and display the log
echo "=== Current Log Contents ===" . PHP_EOL;
if (file_exists('data/app.log')) {
    echo file_get_contents('data/app.log');
} else {
    echo "Log file not found." . PHP_EOL;
}
```

2. **Run the Logger**

```bash
# Execute the logger
php logger.php
```

3. **View the Log File Directly**

```bash
# View the generated log
cat data/app.log
```

### Expected Result

```
=== Testing Logger ===

✓ Log entries written to data/app.log

=== Current Log Contents ===
[2025-10-25 14:23:45] [INFO] Application started
[2025-10-25 14:23:45] [INFO] User authentication successful
[2025-10-25 14:23:45] [WARNING] Deprecated function called in legacy code
[2025-10-25 14:23:45] [ERROR] Database connection failed
[2025-10-25 14:23:45] [INFO] Application shutdown gracefully
```

### Why This Is Useful

File-based logging is essential for debugging production applications where you can't use `echo` or `var_dump()`. This simple logger demonstrates:

- **Atomic operations**: Each `file_put_contents()` with `FILE_APPEND` is atomic, preventing log corruption when multiple processes write simultaneously
- **Timestamping**: Using `date()` to create sortable, readable timestamps
- **Structured format**: Following a consistent format makes logs easy to parse and search
- **Error handling**: Gracefully handling failures without stopping the application

In production, you'd typically use a logging library like [Monolog](https://github.com/Seldaek/monolog), but understanding file operations helps you work with any logging system.

## Step 5: Directory Operations and File Management (~6 min)

### Goal

Learn to manage directories and files programmatically—listing contents, copying, moving, deleting, and working with file paths safely.

### Actions

1. **Create the File Management Script**

Create a new file called `file-manager.php`:

```php
<?php
// filename: file-manager.php
// Demonstrates directory operations and file management

echo "=== File and Directory Management ===" . PHP_EOL . PHP_EOL;

// 1. Create a directory structure
echo "--- Creating Directories ---" . PHP_EOL;
$uploadDir = 'data/uploads';
$backupDir = 'data/backups';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
    echo "✓ Created directory: $uploadDir" . PHP_EOL;
}

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
    echo "✓ Created directory: $backupDir" . PHP_EOL;
}

echo PHP_EOL;

// 2. Create some test files
echo "--- Creating Test Files ---" . PHP_EOL;
$files = [
    'uploads/document.txt' => 'This is a text document.',
    'uploads/report.txt' => 'Monthly sales report.',
    'uploads/notes.txt' => 'Project notes and ideas.',
];

foreach ($files as $path => $content) {
    $fullPath = 'data/' . $path;
    file_put_contents($fullPath, $content);
    echo "✓ Created: $fullPath" . PHP_EOL;
}

echo PHP_EOL;

// 3. List directory contents using scandir()
echo "--- Listing Directory Contents (scandir) ---" . PHP_EOL;
$contents = scandir($uploadDir);

foreach ($contents as $item) {
    // Skip . and ..
    if ($item === '.' || $item === '..') {
        continue;
    }

    $fullPath = $uploadDir . '/' . $item;
    $type = is_file($fullPath) ? 'File' : 'Dir';
    $size = is_file($fullPath) ? filesize($fullPath) . ' bytes' : '';

    echo "  [$type] $item $size" . PHP_EOL;
}

echo PHP_EOL;

// 4. List files using glob() - more powerful pattern matching
echo "--- Listing Files with Pattern Matching (glob) ---" . PHP_EOL;
$txtFiles = glob('data/uploads/*.txt');

echo "Found " . count($txtFiles) . " .txt files:" . PHP_EOL;
foreach ($txtFiles as $file) {
    echo "  • $file" . PHP_EOL;
}

echo PHP_EOL;

// 5. Copy a file
echo "--- Copying Files ---" . PHP_EOL;
$sourceFile = 'data/uploads/document.txt';
$destFile = 'data/backups/document-backup.txt';

if (copy($sourceFile, $destFile)) {
    echo "✓ Copied: $sourceFile → $destFile" . PHP_EOL;
} else {
    echo "✗ Failed to copy file" . PHP_EOL;
}

echo PHP_EOL;

// 6. Rename/move a file
echo "--- Moving/Renaming Files ---" . PHP_EOL;
$oldPath = 'data/uploads/report.txt';
$newPath = 'data/uploads/monthly-report.txt';

if (rename($oldPath, $newPath)) {
    echo "✓ Renamed: $oldPath → $newPath" . PHP_EOL;
} else {
    echo "✗ Failed to rename file" . PHP_EOL;
}

echo PHP_EOL;

// 7. Work with file paths
echo "--- Path Information ---" . PHP_EOL;
$filePath = 'data/uploads/monthly-report.txt';

$info = pathinfo($filePath);
echo "Full path: $filePath" . PHP_EOL;
echo "  Directory: {$info['dirname']}" . PHP_EOL;
echo "  Filename: {$info['basename']}" . PHP_EOL;
echo "  Name only: {$info['filename']}" . PHP_EOL;
echo "  Extension: {$info['extension']}" . PHP_EOL;

echo PHP_EOL;

// Alternative functions
echo "Using individual functions:" . PHP_EOL;
echo "  dirname():  " . dirname($filePath) . PHP_EOL;
echo "  basename(): " . basename($filePath) . PHP_EOL;
echo "  basename() without extension: " . basename($filePath, '.txt') . PHP_EOL;

echo PHP_EOL;

// 8. Get file metadata
echo "--- File Metadata ---" . PHP_EOL;
if (file_exists($filePath)) {
    echo "File: $filePath" . PHP_EOL;
    echo "  Size: " . filesize($filePath) . " bytes" . PHP_EOL;
    echo "  Modified: " . date('Y-m-d H:i:s', filemtime($filePath)) . PHP_EOL;
    echo "  Readable: " . (is_readable($filePath) ? 'Yes' : 'No') . PHP_EOL;
    echo "  Writable: " . (is_writable($filePath) ? 'Yes' : 'No') . PHP_EOL;
}

echo PHP_EOL;

// 9. Delete a file
echo "--- Deleting Files ---" . PHP_EOL;
$fileToDelete = 'data/uploads/notes.txt';

if (file_exists($fileToDelete)) {
    if (unlink($fileToDelete)) {
        echo "✓ Deleted: $fileToDelete" . PHP_EOL;
    } else {
        echo "✗ Failed to delete file" . PHP_EOL;
    }
}

echo PHP_EOL;

// 10. Count remaining files
$remainingFiles = glob('data/uploads/*');
echo "Remaining files in uploads: " . count($remainingFiles) . PHP_EOL;

// List them
foreach ($remainingFiles as $file) {
    if (is_file($file)) {
        echo "  • " . basename($file) . PHP_EOL;
    }
}
```

2. **Run the Script**

```bash
# Execute the file manager
php file-manager.php
```

### Expected Result

```
=== File and Directory Management ===

--- Creating Directories ---
✓ Created directory: data/uploads
✓ Created directory: data/backups

--- Creating Test Files ---
✓ Created: data/uploads/document.txt
✓ Created: data/uploads/report.txt
✓ Created: data/uploads/notes.txt

--- Listing Directory Contents (scandir) ---
  [File] document.txt 24 bytes
  [File] notes.txt 23 bytes
  [File] report.txt 21 bytes

--- Listing Files with Pattern Matching (glob) ---
Found 3 .txt files:
  • data/uploads/document.txt
  • data/uploads/notes.txt
  • data/uploads/report.txt

--- Copying Files ---
✓ Copied: data/uploads/document.txt → data/backups/document-backup.txt

--- Moving/Renaming Files ---
✓ Renamed: data/uploads/report.txt → data/uploads/monthly-report.txt

--- Path Information ---
Full path: data/uploads/monthly-report.txt
  Directory: data/uploads
  Filename: monthly-report.txt
  Name only: monthly-report
  Extension: txt

Using individual functions:
  dirname():  data/uploads
  basename(): monthly-report.txt
  basename() without extension: monthly-report

--- File Metadata ---
File: data/uploads/monthly-report.txt
  Size: 21 bytes
  Modified: 2025-10-25 14:45:12
  Readable: Yes
  Writable: Yes

--- Deleting Files ---
✓ Deleted: data/uploads/notes.txt

Remaining files in uploads: 2
  • document.txt
  • monthly-report.txt
```

### Why It Works

- **`mkdir($path, $mode, $recursive)`**: Creates a directory. The third parameter (`true`) allows creating nested directories in one call. The mode (0755) sets permissions—7 (owner can read/write/execute), 5 (group and others can read/execute).

- **`scandir($path)`**: Returns an array of all files and directories in the given path, including `.` (current directory) and `..` (parent directory). Always filter these out.

- **`glob($pattern)`**: More powerful than `scandir()`—finds files matching a pattern. Supports wildcards: `*` (any characters), `?` (single character), `[abc]` (character sets). Very useful for finding specific file types.

- **`copy($source, $dest)`**: Creates a complete copy of a file. Returns `true` on success, `false` on failure. The destination directory must exist.

- **`rename($old, $new)`**: Despite the name, this function both renames AND moves files. You can move a file to a different directory by providing a different path. Atomic operation on most systems.

- **`unlink($path)`**: Deletes a file. Cannot delete directories (use `rmdir()` for empty directories). Returns `true` on success.

- **`pathinfo($path)`**: Returns an associative array with path components: `dirname`, `basename`, `extension`, `filename`. Invaluable for path manipulation.

- **`dirname($path)` / `basename($path)`**: Extract directory or filename from a path. Useful for building new paths or validating user input.

- **`filesize($path)`**: Returns file size in bytes. For large files (>2GB), use `sprintf("%u", filesize($path))` to avoid integer overflow on 32-bit systems.

- **`filemtime($path)`**: Returns last modification timestamp as Unix timestamp. Use with `date()` to format human-readable dates.

- **`is_readable()` / `is_writable()`**: Check permissions before attempting operations. Prevents errors and improves security.

### Troubleshooting

**Problem**: `mkdir()` fails with "Permission denied" error.

**Solutions**:

1. **Check parent directory permissions**: Ensure the parent directory is writable.
2. **Try absolute paths**: Use `__DIR__ . '/data/uploads'` instead of relative paths.
3. **Check SELinux/AppArmor**: On Linux, security modules may restrict writes. Check with `getenforce` or `aa-status`.

**Problem**: `scandir()` or `glob()` returns empty array but files exist.

**Solutions**:

1. **Verify the path**: Use `realpath($path)` to see the actual resolved path.
2. **Check permissions**: The directory must be readable. Test with `is_readable($dirPath)`.
3. **Case sensitivity**: Unix/Linux filesystems are case-sensitive; Windows is not.

**Problem**: `copy()` fails without error message.

**Solutions**:

1. **Check destination directory exists**: `copy()` won't create directories—do `mkdir()` first.
2. **Check disk space**: Use `disk_free_space('.')` to verify available space.
3. **Check source file exists**: Always use `file_exists()` before copying.

**Problem**: `rename()` fails when moving between filesystems.

**Solution**: `rename()` doesn't work across different filesystems/drives. Use `copy()` followed by `unlink()` instead:

```php
if (copy($source, $dest)) {
    unlink($source);
}
```

**Problem**: Cannot delete file with `unlink()`.

**Solutions**:

1. **Check file permissions**: File must be writable, or parent directory must allow file deletion.
2. **Check if file is open**: Close any file handles before deleting.
3. **Windows file locking**: Windows locks files more aggressively than Unix. Ensure no process is using the file.

## Step 6: Reading Large Files Efficiently (~5 min)

### Goal

Learn to process large files line-by-line without loading them entirely into memory, essential for handling log files, CSVs, and large datasets.

### Actions

1. **Create a Sample Large File**

First, let's create a test file with many lines:

```php
<?php
// filename: create-large-file.php
// Creates a sample large log file for testing

echo "Creating sample log file..." . PHP_EOL;

$logFile = 'data/large-access.log';
$handle = fopen($logFile, 'w');

if (!$handle) {
    die("Failed to create file");
}

// Write 10,000 log entries
for ($i = 1; $i <= 10000; $i++) {
    $timestamp = date('Y-m-d H:i:s', strtotime("-{$i} seconds"));
    $methods = ['GET', 'POST', 'PUT', 'DELETE'];
    $method = $methods[array_rand($methods)];
    $paths = ['/api/users', '/api/posts', '/api/comments', '/home', '/about'];
    $path = $paths[array_rand($paths)];
    $codes = [200, 200, 200, 201, 204, 400, 404, 500]; // 200s are more common
    $code = $codes[array_rand($codes)];

    $line = "[$timestamp] $method $path $code\n";
    fwrite($handle, $line);
}

fclose($handle);

$size = filesize($logFile);
echo "✓ Created log file with 10,000 entries" . PHP_EOL;
echo "  File size: " . number_format($size) . " bytes (" .
     round($size / 1024, 2) . " KB)" . PHP_EOL;
```

Run it:

```bash
# Create the large file
php create-large-file.php
```

2. **Create the Line-by-Line Reader**

Now create `read-large-file.php`:

```php
<?php
// filename: read-large-file.php
// Demonstrates efficient reading of large files

$logFile = 'data/large-access.log';

if (!file_exists($logFile)) {
    die("Log file not found. Run create-large-file.php first.\n");
}

echo "=== Reading Large Files Efficiently ===" . PHP_EOL . PHP_EOL;

// Method 1: Using fopen/fgets/fclose for line-by-line reading
echo "--- Method 1: Line-by-Line with fgets() ---" . PHP_EOL;

$handle = fopen($logFile, 'r');
if (!$handle) {
    die("Failed to open file\n");
}

$lineCount = 0;
$errorCount = 0;
$successCount = 0;

// Read file line by line
while (($line = fgets($handle)) !== false) {
    $lineCount++;

    // Parse the log line (looking for status codes)
    if (preg_match('/\s(\d{3})$/', $line, $matches)) {
        $statusCode = (int)$matches[1];

        if ($statusCode >= 400) {
            $errorCount++;
        } else {
            $successCount++;
        }
    }

    // Display first 5 lines as sample
    if ($lineCount <= 5) {
        echo "  Line $lineCount: " . trim($line) . PHP_EOL;
    }
}

// Check for read errors
if (!feof($handle)) {
    echo "✗ Error: unexpected end of file\n";
}

fclose($handle);

echo PHP_EOL;
echo "✓ Processed $lineCount lines" . PHP_EOL;
echo "  Success responses (2xx-3xx): $successCount" . PHP_EOL;
echo "  Error responses (4xx-5xx): $errorCount" . PHP_EOL;

echo PHP_EOL;

// Method 2: Using file() to read into array
echo "--- Method 2: Read Entire File as Array ---" . PHP_EOL;

// WARNING: This loads the entire file into memory!
$lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

echo "✓ Loaded " . count($lines) . " lines into array" . PHP_EOL;
echo "  First line: {$lines[0]}" . PHP_EOL;
echo "  Last line: {$lines[count($lines) - 1]}" . PHP_EOL;

echo PHP_EOL;

// Method 3: Processing specific sections
echo "--- Method 3: Reading Specific Sections ---" . PHP_EOL;

$handle = fopen($logFile, 'r');
$targetLine = 5000;
$currentLine = 0;

// Skip to line 5000
while ($currentLine < $targetLine && !feof($handle)) {
    fgets($handle);
    $currentLine++;
}

// Read 5 lines from that position
echo "Lines $targetLine to " . ($targetLine + 4) . ":" . PHP_EOL;
for ($i = 0; $i < 5 && !feof($handle); $i++) {
    $line = fgets($handle);
    echo "  " . trim($line) . PHP_EOL;
}

fclose($handle);

echo PHP_EOL;

// Method 4: Count total lines efficiently
echo "--- Method 4: Counting Lines Efficiently ---" . PHP_EOL;

$handle = fopen($logFile, 'r');
$lines = 0;

while (!feof($handle)) {
    $line = fgets($handle);
    if ($line !== false) {
        $lines++;
    }
}

fclose($handle);

echo "✓ Total lines in file: $lines" . PHP_EOL;

echo PHP_EOL;

// Method 5: Find specific entries
echo "--- Method 5: Searching for Specific Entries ---" . PHP_EOL;

$handle = fopen($logFile, 'r');
$errors = [];

while (($line = fgets($handle)) !== false) {
    // Find 500 errors
    if (strpos($line, '500') !== false) {
        $errors[] = trim($line);

        // Stop after finding 5 examples
        if (count($errors) >= 5) {
            break;
        }
    }
}

fclose($handle);

echo "Found " . count($errors) . " examples of 500 errors:" . PHP_EOL;
foreach ($errors as $error) {
    echo "  • $error" . PHP_EOL;
}

echo PHP_EOL;

// Performance comparison
echo "--- Performance Note ---" . PHP_EOL;
echo "Memory usage: " . round(memory_get_usage() / 1024 / 1024, 2) . " MB" . PHP_EOL;
echo "Peak memory: " . round(memory_get_peak_usage() / 1024 / 1024, 2) . " MB" . PHP_EOL;
```

3. **Run the Reader**

```bash
# Execute the large file reader
php read-large-file.php
```

### Expected Result

```
=== Reading Large Files Efficiently ===

--- Method 1: Line-by-Line with fgets() ---
  Line 1: [2025-10-25 14:30:15] GET /api/users 200
  Line 2: [2025-10-25 14:30:14] POST /api/posts 201
  Line 3: [2025-10-25 14:30:13] GET /home 200
  Line 4: [2025-10-25 14:30:12] DELETE /api/comments 404
  Line 5: [2025-10-25 14:30:11] PUT /about 200

✓ Processed 10000 lines
  Success responses (2xx-3xx): 7523
  Error responses (4xx-5xx): 2477

--- Method 2: Read Entire File as Array ---
✓ Loaded 10000 lines into array
  First line: [2025-10-25 14:30:15] GET /api/users 200
  Last line: [2025-10-22 11:43:35] POST /api/posts 200

--- Method 3: Reading Specific Sections ---
Lines 5000 to 5004:
  [2025-10-24 01:06:55] GET /api/posts 200
  [2025-10-24 01:06:54] DELETE /home 200
  [2025-10-24 01:06:53] POST /api/users 404
  [2025-10-24 01:06:52] GET /api/comments 200
  [2025-10-24 01:06:51] PUT /about 500

--- Method 4: Counting Lines Efficiently ---
✓ Total lines in file: 10000

--- Method 5: Searching for Specific Entries ---
Found 5 examples of 500 errors:
  • [2025-10-25 14:28:42] POST /api/posts 500
  • [2025-10-25 14:27:31] DELETE /home 500
  • [2025-10-25 14:26:15] GET /api/users 500
  • [2025-10-25 14:25:08] PUT /about 500
  • [2025-10-25 14:23:44] POST /api/comments 500

--- Performance Note ---
Memory usage: 2.45 MB
Peak memory: 3.12 MB
```

### Why It Works

- **`fopen($path, $mode)`**: Opens a file and returns a file handle (resource). Common modes:

  - `'r'` - Read only, start at beginning
  - `'r+'` - Read/write, start at beginning
  - `'w'` - Write only, truncate file or create new
  - `'w+'` - Read/write, truncate file or create new
  - `'a'` - Write only, append to end
  - `'a+'` - Read/write, append to end

- **`fgets($handle)`**: Reads one line from the file (up to the next newline). Returns `false` when reaching end of file. Very memory-efficient because it only loads one line at a time.

- **`feof($handle)`**: Returns `true` when the file pointer reaches the end. Always check this after a loop to distinguish between legitimate end-of-file and read errors.

- **`fclose($handle)`**: Closes the file handle. Always close files when done to free system resources. PHP will close them automatically at script end, but explicit closure is best practice.

- **`file($path, $flags)`**: Reads entire file into an array, with each line as an element. Very convenient but uses more memory. Flags:

  - `FILE_IGNORE_NEW_LINES` - Strips newlines from each line
  - `FILE_SKIP_EMPTY_LINES` - Skips empty lines

- **`fwrite($handle, $string)`**: Writes a string to a file. Used with `fopen(..., 'w')` or `fopen(..., 'a')` for more control than `file_put_contents()`.

**Key Difference**: `file_get_contents()` loads the entire file into a string in memory. For a 1GB log file, that uses 1GB of RAM. Using `fopen()`/`fgets()`/`fclose()` only keeps one line in memory at a time—typically just a few hundred bytes.

### Troubleshooting

**Problem**: `fopen()` returns `false`.

**Solutions**:

1. **Check file exists**: Use `file_exists()` before opening for reading.
2. **Check permissions**: Verify you have permission to read/write the file.
3. **Check path**: Use absolute paths or `__DIR__` to avoid confusion.
4. **Enable error reporting**: Add `ini_set('display_errors', 1);` to see detailed errors.

**Problem**: Infinite loop—`fgets()` never returns `false`.

**Solution**: Always check `feof()` in your loop condition or immediately after:

```php
while (!feof($handle)) {
    $line = fgets($handle);
    if ($line === false) break;
    // Process line
}
```

**Problem**: Memory usage still high with `fgets()`.

**Solutions**:

1. **Check for memory leaks**: Make sure you're not storing all lines in an array.
2. **Unset variables**: After processing each line, use `unset()` on large variables.
3. **Process in chunks**: If you must store data, write intermediate results to disk.

**Problem**: File is locked and can't be opened.

**Solutions**:

1. **Close previous handles**: Make sure you called `fclose()` on previous opens.
2. **Check other processes**: On Windows especially, other programs may lock files.
3. **Use shared read mode**: On some systems, open with `'rb'` for binary read mode.

**Problem**: Reading CSV files character-by-character is slow.

**Solution**: Use `fgetcsv($handle)` instead of `fgets()` for CSV files. It automatically parses CSV format and returns an array:

```php
while (($data = fgetcsv($handle)) !== false) {
    // $data is an array of CSV columns
}
```

## Exercises

### Exercise 1: Enhanced Logger

Extend the logger to include more features:

**Requirements**:

- Add a maximum file size limit (e.g., 1MB)
- When the limit is reached, rename `app.log` to `app.log.old` and start a new file
- Add a function `clearLogs()` that deletes all log files
- Add log levels: `DEBUG`, `INFO`, `WARNING`, `ERROR`, `CRITICAL`

**Hints**:

- Use `filesize($path)` to check file size
- Use `rename($oldName, $newName)` to rotate logs
- Use `unlink($path)` to delete files
- Consider using constants for log levels

### Exercise 2: Configuration Manager

Build a simple configuration system using JSON:

**Requirements**:

1. Create a JSON file `config/app.json` with these settings:

```json
{
  "app_name": "My Awesome Blog",
  "version": "1.0.0",
  "database": {
    "host": "localhost",
    "port": 3306,
    "name": "blog_db"
  },
  "features": {
    "comments_enabled": true,
    "registration_enabled": false
  }
}
```

2. Create a `Config` class with these methods:

   - `load(string $path)`: Loads the JSON file
   - `get(string $key, mixed $default = null)`: Gets a value (supports dot notation like `database.host`)
   - `set(string $key, mixed $value)`: Sets a value
   - `save()`: Saves changes back to the file

3. Write a test script that:
   - Loads the config
   - Prints the app name and database host
   - Changes `features.comments_enabled` to `false`
   - Saves the config

**Hints**:

- For dot notation, use `explode('.', $key)` and loop through the array
- Store the file path as a class property
- Keep the decoded array as a private property

### Exercise 3: Simple File-Based Database

Create a basic "database" using JSON files:

**Requirements**:

- Create a `data/users/` directory
- Each user is stored as a separate JSON file: `{id}.json`
- Implement these functions:
  - `createUser(array $userData)`: Creates a new user file
  - `getUser(int $id)`: Reads and returns user data
  - `updateUser(int $id, array $updates)`: Updates user data
  - `deleteUser(int $id)`: Deletes user file
  - `getAllUsers()`: Returns all users as an array

**Hints**:

- Generate IDs by finding the highest existing ID and adding 1
- Use `glob('data/users/*.json')` to find all user files
- Validate that files exist before reading
- Handle JSON errors gracefully

### Exercise 4: CSV Export

Write a function that converts your user data to CSV format:

**Requirements**:

- Create a function `exportUsersToCSV(array $users, string $filename)`
- The CSV should include headers: `id,name,email,active`
- Use proper CSV escaping for fields containing commas or quotes
- Test with the user data from Step 3

**Hints**:

- Use `fopen()`, `fputcsv()`, and `fclose()` for proper CSV handling
- Or manually build CSV lines with proper escaping
- Test with data that contains commas, quotes, and newlines

## Validation

To verify your work is complete:

```bash
# Navigate to your project directory
cd filesystem-tutorial

# Check that all expected files and directories exist
ls -laR data/

# Expected structure:
# data/
#   notes.txt
#   users.json
#   app.log
#   demo.txt
#   demo.json
#   large-access.log
#   uploads/
#     document.txt
#     monthly-report.txt
#   backups/
#     document-backup.txt

# Verify JSON is valid
php -r "json_decode(file_get_contents('data/users.json')); echo json_last_error_msg() . PHP_EOL;"
# Should output: No error

# Check log file has entries
wc -l data/app.log
# Should show at least 5 lines

# Check large file exists and has correct line count
wc -l data/large-access.log
# Should show exactly 10000 lines

# Verify uploads directory has files
ls -1 data/uploads/
# Should show:
# document.txt
# monthly-report.txt

# Verify backups directory has backup file
ls -1 data/backups/
# Should show:
# document-backup.txt

# Test that all scripts run without errors
echo "Testing all scripts..."
php check-files.php && echo "✓ check-files.php works"
php read-write.php && echo "✓ read-write.php works"
php json-demo.php && echo "✓ json-demo.php works"
php logger.php && echo "✓ logger.php works"
php file-manager.php && echo "✓ file-manager.php works"
php read-large-file.php && echo "✓ read-large-file.php works"
```

## Wrap-up

You've mastered the fundamentals of filesystem operations in PHP. You now know how to:

- **Check for files and directories** before operating on them, preventing errors
- **Read and write files** using the simple `file_get_contents()` and `file_put_contents()` functions
- **Append to files** without destroying existing content using `FILE_APPEND`
- **Work with JSON** to store and retrieve structured data
- **Manage directories** with `mkdir()`, `scandir()`, and `glob()` for listing and pattern matching
- **Copy, move, rename, and delete** files using `copy()`, `rename()`, and `unlink()`
- **Parse file paths** safely with `pathinfo()`, `dirname()`, and `basename()`
- **Get file metadata** like size, modification time, and permissions
- **Read large files efficiently** with `fopen()`, `fgets()`, and `fclose()` to avoid memory issues
- **Handle errors gracefully** by checking return values and using error functions
- **Build practical utilities** like loggers, file managers, and log analyzers

These skills are fundamental for almost any PHP application. File operations are used for:

- **Configuration files** (JSON, YAML, INI)
- **Logging and debugging** (application logs, error logs)
- **Caching** (storing computed results to improve performance)
- **Data import/export** (CSV, JSON data interchange)
- **Session storage** (PHP can store sessions in files)
- **Template systems** (reading and compiling template files)

### Security Considerations

When working with files in web applications, always remember:

1. **Validate paths**: Never trust user input in file paths. Attackers can use `../` to access files outside your intended directory.
2. **Check permissions**: Ensure files aren't world-writable (avoid `chmod 777` in production).
3. **Sanitize filenames**: Remove or escape special characters from user-provided filenames.
4. **Use absolute paths**: Relative paths can be unpredictable in web contexts.
5. **Limit file sizes**: Check uploaded file sizes to prevent disk space exhaustion.

### What's Next

In the next chapter, we'll take a major step forward by learning how to connect to and interact with a database using PHP's PDO extension. While file storage is useful, databases provide better performance, querying capabilities, and data integrity for most applications.

## Knowledge Check

Test your understanding of filesystem operations:

<Quiz
title="Chapter 13 Quiz: Working with the Filesystem"
:questions="[
{
question: 'What does file_get_contents() do?',
options: [
{ text: 'Reads an entire file into a string', correct: true, explanation: 'file_get_contents() is the simplest way to read a whole file at once into a string variable.' },
{ text: 'Writes content to a file', correct: false, explanation: 'That\'s file_put_contents(); file_get_contents() reads files.' },
{ text: 'Deletes a file', correct: false, explanation: 'That\'s unlink(); file_get_contents() reads files.' },
{ text: 'Lists files in a directory', correct: false, explanation: 'That\'s scandir() or glob(); file_get_contents() reads file content.' }
]
},
{
question: 'What does the FILE_APPEND flag do with file_put_contents()?',
options: [
{ text: 'Adds content to the end without overwriting', correct: true, explanation: 'FILE_APPEND adds new content to the end of the file instead of replacing everything.' },
{ text: 'Creates a new file', correct: false, explanation: 'file_put_contents() creates files by default; FILE_APPEND specifically adds to existing content.' },
{ text: 'Deletes the file first', correct: false, explanation: 'FILE_APPEND preserves existing content; without it, the file is overwritten.' },
{ text: 'Converts content to JSON', correct: false, explanation: 'JSON conversion uses json_encode(); FILE_APPEND is about append behavior.' }
]
},
{
question: 'What function encodes a PHP array into JSON format?',
options: [
{ text: 'json_encode()', correct: true, explanation: 'json_encode() converts PHP arrays/objects into JSON strings for storage or API responses.' },
{ text: 'json_decode()', correct: false, explanation: 'json_decode() does the opposite: converts JSON strings back to PHP arrays.' },
{ text: 'serialize()', correct: false, explanation: 'serialize() creates PHP-specific format; json_encode() creates JSON.' },
{ text: 'file_put_contents()', correct: false, explanation: 'file_put_contents() writes to files; json_encode() converts to JSON format.' }
]
},
{
question: 'Why should you use fopen()/fgets()/fclose() instead of file_get_contents() for large files?',
options: [
{ text: 'To read line-by-line without loading entire file into memory', correct: true, explanation: 'fgets() reads one line at a time, preventing memory exhaustion with huge files.' },
{ text: 'It\'s faster', correct: false, explanation: 'file_get_contents() can be faster for small files; line-by-line is about memory management.' },
{ text: 'It\'s required for all files', correct: false, explanation: 'file_get_contents() works fine for small files; use line-by-line for large ones.' },
{ text: 'It creates the file if it doesn\'t exist', correct: false, explanation: 'Both methods can create files; the difference is in how they read.' }
]
},
{
question: 'What does the glob() function do?',
options: [
{ text: 'Finds files matching a pattern with wildcards', correct: true, explanation: 'glob() searches for files using patterns like *.txt or data/*.json, returning matching paths.' },
{ text: 'Creates global variables', correct: false, explanation: 'glob() finds files; it has nothing to do with global variables.' },
{ text: 'Deletes multiple files', correct: false, explanation: 'glob() finds files; use unlink() in a loop to delete them.' },
{ text: 'Combines file contents', correct: false, explanation: 'glob() finds files; combining content requires reading and concatenating.' }
]
}
]"
/>

### Further Reading

- [PHP Filesystem Functions](https://www.php.net/manual/en/ref.filesystem.php) - Complete reference
- [JSON Functions in PHP](https://www.php.net/manual/en/ref.json.php) - Detailed JSON documentation
- [File Locking with flock()](https://www.php.net/manual/en/function.flock.php) - Prevent concurrent write issues
- [SPL File Classes](https://www.php.net/manual/en/book.spl.php) - Object-oriented file handling
