# Chapter 13: Working with the Filesystem - Code Examples

Complete filesystem operations including files, directories, and file uploads.

## Files

1. **`file-operations.php`** - Reading, writing, CSV, JSON operations
2. **`directory-operations.php`** - Creating, listing, copying, deleting directories

## Quick Start

```bash
php file-operations.php
php directory-operations.php
```

## Key Operations

### File Operations

```php
// Write
file_put_contents('file.txt', 'content');

// Read
$content = file_get_contents('file.txt');

// Append
file_put_contents('file.txt', 'more', FILE_APPEND);

// Check existence
if (file_exists('file.txt')) { }

// Delete
unlink('file.txt');
```

### Directory Operations

```php
// Create
mkdir('uploads', 0755, true);  // recursive

// List
$files = scandir('uploads');

// Check if directory
is_dir('uploads');

// Delete
rmdir('uploads');  // must be empty
```

### CSV Operations

```php
// Write
$fp = fopen('data.csv', 'w');
fputcsv($fp, ['Name', 'Email']);
fclose($fp);

// Read
$fp = fopen('data.csv', 'r');
while ($row = fgetcsv($fp)) {
    print_r($row);
}
fclose($fp);
```

### JSON Operations

```php
// Write
file_put_contents('data.json', json_encode($array, JSON_PRETTY_PRINT));

// Read
$data = json_decode(file_get_contents('data.json'), true);
```

## File Information

```php
filesize($path);              // Size in bytes
filemtime($path);             // Last modified time
is_readable($path);           // Can read?
is_writable($path);           // Can write?
is_file($path);               // Is a file?
is_dir($path);                // Is a directory?
file_exists($path);           // Exists?
```

## Path Operations

```php
realpath($path);              // Absolute path
basename($path);              // Filename
dirname($path);               // Directory
pathinfo($path);              // All info
```

## Globbing (Pattern Matching)

```php
glob('*.txt');                // All .txt files
glob('data/*.{jpg,png}', GLOB_BRACE);  // Multiple extensions
```

## Best Practices

✓ Always check `file_exists()` before operations
✓ Use `@` suppression or try-catch for error handling
✓ Close file handles with `fclose()`
✓ Use `FILE_APPEND` flag when appending
✓ Set proper permissions (0644 files, 0755 directories)
✓ Validate file uploads (type, size, name)
✓ Never trust user-provided filenames
✓ Use `realpath()` to prevent directory traversal

## Security Warnings

❌ **Never** allow user input in file paths directly
❌ **Never** execute uploaded files
❌ **Always** validate file extensions
❌ **Always** set upload limits

## Related Chapter

[Chapter 13: Working with the Filesystem](../../chapters/13-working-with-the-filesystem.md)

## Further Reading

- [PHP Manual: Filesystem Functions](https://www.php.net/manual/en/ref.filesystem.php)
- [PHP Manual: Directory Functions](https://www.php.net/manual/en/ref.dir.php)
