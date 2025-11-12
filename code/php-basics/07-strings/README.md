# Chapter 07: Mastering String Manipulation - Code Examples

This directory contains comprehensive examples for working with strings in PHP, one of the most common data types in web development.

## Files Overview

### 1. `basic-strings.php` - String Fundamentals

**What it demonstrates:**

- String creation (single quotes, double quotes, heredoc)
- String concatenation and interpolation
- String length (`strlen`, `mb_strlen`)
- Character access
- Case conversion functions
- Trimming and padding
- String reversal and repetition
- Practical name formatting

**Run:** `php basic-strings.php`

**Key Takeaways:**

- Use single quotes for literal strings, double quotes for interpolation
- `mb_strlen()` for proper UTF-8 character counting
- `ucwords()` for title case, `strtolower()`/`strtoupper()` for case conversion

### 2. `search-replace.php` - Finding and Replacing

**What it demonstrates:**

- PHP 8.0+ `str_contains()`, `str_starts_with()`, `str_ends_with()`
- `strpos()`, `stripos()` for finding substrings
- `substr()` for extracting substrings
- `str_replace()`, `str_ireplace()` for replacements
- Counting replacements
- Practical examples: censoring, highlighting, slug creation

**Run:** `php search-replace.php`

**Key Takeaways:**

- Use PHP 8.0+ functions for cleaner, more readable code
- `stripos()` and `str_ireplace()` for case-insensitive operations
- Always validate `strpos()` with `=== false` (0 is a valid position)

### 3. `split-join.php` - Breaking Apart and Combining

**What it demonstrates:**

- `explode()` to split strings into arrays
- `implode()`/`join()` to combine arrays into strings
- `str_split()` for character arrays
- `chunk_split()` for formatting
- `preg_split()` for complex splitting
- Practical examples: CSV parsing, URL paths, breadcrumbs, query strings

**Run:** `php split-join.php`

**Key Takeaways:**

- `explode()` with limit parameter for controlled splitting
- `array_map('trim', $array)` pattern for cleaning split data
- `implode()` for joining with separators

## Exercise Solutions

### Exercise 1: Email Domain Extractor

**File:** `solutions/exercise-1-email-validator.php`

Functions to extract and validate email domains.

**Features:**

- Extract domain from email address
- Validate email format
- Check if email is from specific domain
- Extract username from email

**Run:** `php solutions/exercise-1-email-validator.php`

### Exercise 2: Text Analyzer

**File:** `solutions/exercise-2-text-analyzer.php`

Comprehensive text analysis tool.

**Features:**

- Character count (excluding spaces)
- Word count
- Sentence count
- Most common word detection
- Average word length calculation

**Run:** `php solutions/exercise-2-text-analyzer.php`

### Exercise 3: Password Generator

**File:** `solutions/exercise-3-password-generator.php`

Secure random password generator with strength checker.

**Features:**

- Configurable length
- Optional uppercase, lowercase, numbers, special characters
- Password strength analysis
- Security score calculation

**Run:** `php solutions/exercise-3-password-generator.php`

## Quick Reference

### Basic Operations

```php
// Concatenation
$full = $first . " " . $last;

// Interpolation
$greeting = "Hello, $name!";

// Length
$len = strlen($text);
$len = mb_strlen($text);  // UTF-8 safe
```

### Search & Check (PHP 8.0+)

```php
str_contains($text, 'PHP');      // true/false
str_starts_with($text, 'Hello'); // true/false
str_ends_with($text, 'world');   // true/false
```

### Finding & Extracting

```php
$pos = strpos($text, 'needle');  // Position or false
$part = substr($text, 0, 5);     // First 5 characters
$part = substr($text, -5);       // Last 5 characters
```

### Replace

```php
str_replace('old', 'new', $text);      // Case-sensitive
str_ireplace('OLD', 'new', $text);     // Case-insensitive
```

### Split & Join

```php
$parts = explode(',', $csv);     // Split by comma
$text = implode(' ', $words);    // Join with space
```

### Case Conversion

```php
strtoupper($text);   // ALL UPPERCASE
strtolower($text);   // all lowercase
ucfirst($text);      // First char uppercase
ucwords($text);      // Each Word Capitalized
```

### Trim & Pad

```php
trim($text);                            // Remove whitespace
str_pad($text, 10, '0', STR_PAD_LEFT); // Left pad with zeros
```

## Best Practices

**1. Always Use Multibyte Functions for UTF-8**

```php
// ✓ GOOD
$length = mb_strlen($text);

// ✗ BAD (incorrect for emoji/unicode)
$length = strlen($text);
```

**2. Validate strpos() Correctly**

```php
// ✓ GOOD
if (strpos($text, 'PHP') !== false) { }

// ✗ BAD (fails when found at position 0)
if (strpos($text, 'PHP')) { }
```

**3. Use Modern PHP 8.0+ Functions**

```php
// ✓ GOOD (PHP 8.0+)
if (str_contains($text, 'PHP')) { }

// ✗ AVOID (older approach)
if (strpos($text, 'PHP') !== false) { }
```

**4. Clean User Input**

```php
// ✓ GOOD
$clean = trim($_POST['name']);
$safe = htmlspecialchars($clean);

// ✗ BAD
echo $_POST['name'];
```

## Common Patterns

### URL Slug Creation

```php
function createSlug(string $text): string {
    $slug = strtolower(trim($text));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    return trim($slug, '-');
}
```

### CSV Parsing

```php
$data = explode(',', $csvLine);
list($name, $email, $age) = $data;
```

### Truncate with Ellipsis

```php
function truncate(string $text, int $max): string {
    if (strlen($text) <= $max) return $text;
    return substr($text, 0, $max - 3) . '...';
}
```

## Related Chapter

[Chapter 07: Mastering String Manipulation](../../chapters/07-mastering-string-manipulation.md)

## Further Reading

- [PHP Manual: String Functions](https://www.php.net/manual/en/ref.strings.php)
- [PHP Manual: Multibyte String](https://www.php.net/manual/en/book.mbstring.php)
- [PHP 8.0: str_contains, str_starts_with, str_ends_with](https://www.php.net/releases/8.0/en.php#str-contains-str-starts-with-str-ends-with)
