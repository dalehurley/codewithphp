---
title: "07: Mastering String Manipulation"
description: "Learn how to effectively search, replace, format, and parse text using PHP's powerful built-in string functions."
series: "php-basics"
chapter: 7
order: 7
difficulty: "Beginner"
prerequisites:
  - "/series/php-basics/chapters/06-deep-dive-into-arrays"
---

# Chapter 07: Mastering String Manipulation

## Overview

Working with text is a core part of web development. Whether you're formatting a user's name, creating a URL slug from a blog post title, or parsing data from a file, you'll constantly be manipulating strings.

PHP provides a rich library of over 100 functions dedicated to working with strings, making it a powerful tool for text processing. In this chapter, we'll cover the most common and useful functions that you'll use every day, including modern PHP 8.0+ features that make text processing more intuitive.

By the end of this chapter, you'll be able to clean, search, transform, and format strings with confidence.

## Prerequisites

**Required**:

- PHP 8.4 installed ([Chapter 00](/series/php-basics/chapters/00-setting-up-your-development-environment))
- A text editor or IDE
- Basic understanding of variables and data types ([Chapter 02](/series/php-basics/chapters/02-variables-data-types-and-constants))

**Estimated time**: ~25 minutes

## What You'll Build

By the end of this chapter, you'll create several working examples demonstrating:

- Length calculation and case conversion
- Substring extraction with `substr()`
- Title case formatting (`ucfirst`, `ucwords`, `lcfirst`)
- Creating blog excerpts and text previews
- Converting text to HTML with `nl2br()`
- Padding strings for formatted output
- Modern PHP 8.0+ string searching functions
- Find and replace operations (including batch replacements)
- String splitting and joining
- Professional string formatting with `sprintf`
- Handling international characters with multibyte functions
- A practical URL slug generator function
- A simple CSV parser

## Quick Start

If you want to see string manipulation in action immediately, create a file called `quick-strings.php`:

```php
<?php
// Quick demonstration of PHP string power
$text = "  Learn PHP 8.4 Today!  ";

echo "Original: '$text'\n";
echo "Cleaned: '" . trim($text) . "'\n";
echo "Lowercase: " . strtolower($text) . "\n";
echo "Slug: " . strtolower(str_replace(' ', '-', trim($text))) . "\n";

// Modern PHP 8.0+ features
if (str_contains($text, 'PHP')) {
    echo "This text is about PHP!\n";
}
```

Run it:

```bash
php quick-strings.php
```

Now let's dive deeper into each capability.

## Objectives

- Find the length of a string and access individual characters
- Use modern PHP 8.0+ string search functions (`str_contains`, `str_starts_with`, `str_ends_with`)
- Search for and replace substrings using multiple methods
- Change the case of a string (uppercase/lowercase)
- Split a string into an array and join an array into a string
- Format strings professionally using `sprintf`
- Handle multibyte (international) strings safely

## Step 1: Basic String Operations (~4 min)

Let's start with some of the most fundamental operations: finding a string's length, changing its case, and trimming whitespace.

**Goal**: Learn to measure, clean, and transform string casing.

**Actions**:

1.  Create a new file named `01-basic-strings.php`.

2.  Add the following code:

```php
<?php
// 01-basic-strings.php - Basic string operations

$sentence = "  The quick brown fox jumps over the lazy dog.  ";

// 1. Get the length of the string
$length = strlen($sentence);
echo "Original length: $length characters\n";

// 2. Convert to uppercase and lowercase
$upper = strtoupper($sentence);
$lower = strtolower($sentence);
echo "Uppercase: $upper\n";
echo "Lowercase: $lower\n";

// 3. Trim whitespace from the beginning and end
$trimmed = trim($sentence);
echo "Trimmed length: " . strlen($trimmed) . " characters\n";
echo "Trimmed: '$trimmed'\n";

// 4. Trim only specific sides
$leftTrimmed = ltrim($sentence);   // Remove left whitespace
$rightTrimmed = rtrim($sentence);  // Remove right whitespace
echo "Left trimmed: '$leftTrimmed'\n";
echo "Right trimmed: '$rightTrimmed'\n";
```

3.  Run the file:

```bash
php 01-basic-strings.php
```

**Expected Result**:

```text
Original length: 49 characters
Uppercase:   THE QUICK BROWN FOX JUMPS OVER THE LAZY DOG.
Lowercase:   the quick brown fox jumps over the lazy dog.
Trimmed length: 45 characters
Trimmed: 'The quick brown fox jumps over the lazy dog.'
Left trimmed: 'The quick brown fox jumps over the lazy dog.  '
Right trimmed: '  The quick brown fox jumps over the lazy dog.'
```

**Why it works**:

- `strlen()`: Returns the number of bytes in the string (which equals the number of characters for ASCII text).
- `strtoupper()` / `strtolower()`: Convert the entire string to uppercase or lowercase.
- `trim()`: Removes whitespace (or other specified characters) from both ends—essential for cleaning up user input.
- `ltrim()` / `rtrim()`: Target only the left or right side.

**Note**: For strings containing international characters (like "Ñoño" or "日本語"), use the `mb_*` functions like `mb_strlen()` and `mb_strtoupper()` to handle multibyte characters correctly. We'll cover this at the end of the chapter.

## Step 2: Extracting and Transforming Substrings (~5 min)

Often you need to extract part of a string or change its capitalization style. These functions are essential for formatting names, titles, excerpts, and display text.

**Goal**: Learn to extract substrings and apply title/sentence case transformations.

**Actions**:

1.  Create a file named `02-substrings.php`.

2.  Add this code:

```php
<?php
// 02-substrings.php - Extracting and transforming substrings

// 1. substr() - Extract part of a string
$text = "Hello, World!";
$first5 = substr($text, 0, 5);           // Start at 0, take 5 chars
$world = substr($text, 7, 5);            // Start at 7, take 5 chars
$last6 = substr($text, -6);              // Last 6 characters (negative offset)
$withoutLast = substr($text, 0, -1);     // Everything except last char

echo "Original: $text\n";
echo "First 5 chars: $first5\n";
echo "Middle portion: $world\n";
echo "Last 6 chars: $last6\n";
echo "Without last char: $withoutLast\n\n";

// 2. Title case and capitalization
$name = "john doe";
$title = "the quick brown fox";
$sentence = "hello world";

echo "ucfirst: " . ucfirst($sentence) . "\n";        // Capitalize first letter
echo "ucwords: " . ucwords($title) . "\n";           // Capitalize Each Word
echo "Name formatted: " . ucwords($name) . "\n\n";   // John Doe

// 3. lcfirst() - Lowercase first letter (useful for camelCase)
$className = "MyClass";
echo "Variable name: " . lcfirst($className) . "\n\n";  // myClass

// 4. Creating excerpts (common use case)
$article = "This is a very long article about PHP string manipulation. It contains multiple sentences and demonstrates how to create excerpts from longer text.";
$excerpt = substr($article, 0, 50) . "...";
echo "Excerpt: $excerpt\n\n";

// 5. nl2br() - Convert newlines to HTML <br> tags
$userInput = "Line 1\nLine 2\nLine 3";
echo "Plain text:\n$userInput\n\n";
echo "HTML formatted:\n" . nl2br($userInput) . "\n\n";

// 6. str_pad() - Pad strings to a fixed width
$id = "42";
$price = "9.99";
echo "Invoice:\n";
echo "ID: " . str_pad($id, 5, "0", STR_PAD_LEFT) . "\n";    // 00042
echo "Price: $" . str_pad($price, 8, " ", STR_PAD_LEFT) . "\n";  // Align right

// 7. Counting functions
$text = "The quick brown fox jumps over the lazy dog.";
$wordCount = str_word_count($text);
$theCount = substr_count(strtolower($text), 'the');
echo "\nWord count: $wordCount\n";
echo "Occurrences of 'the': $theCount\n";
```

3.  Run the file:

```bash
php 02-substrings.php
```

**Expected Result**:

```text
Original: Hello, World!
First 5 chars: Hello
Middle portion: World
Last 6 chars: World!
Without last char: Hello, World

ucfirst: Hello world
ucwords: The Quick Brown Fox
Name formatted: John Doe

Variable name: myClass

Excerpt: This is a very long article about PHP string m...

Plain text:
Line 1
Line 2
Line 3

HTML formatted:
Line 1<br />
Line 2<br />
Line 3<br />

Invoice:
ID: 00042
Price: $    9.99

Word count: 9
Occurrences of 'the': 2
```

**Why it works**:

- `substr(string $string, int $offset, ?int $length)`: Extracts a portion of a string. Negative offsets count from the end.
- `ucfirst()`: Converts the first character to uppercase—perfect for sentences.
- `ucwords()`: Capitalizes the first letter of each word—ideal for names and titles.
- `lcfirst()`: Lowercase first character—useful for variable naming conventions.
- `nl2br()`: Converts newline characters (`\n`) to HTML `<br />` tags for web display.
- `str_pad()`: Pads a string to a certain length with another string (defaults to spaces).
- `str_word_count()`: Counts the number of words in a string.
- `substr_count()`: Counts how many times a substring occurs.

**Common Use Cases**:

- **Blog excerpts**: `substr($content, 0, 150) . '...'`
- **Name formatting**: `ucwords(strtolower($userName))`
- **Displaying user comments**: `nl2br(htmlspecialchars($comment))`
- **Report alignment**: `str_pad($value, 20)`
- **Reading indicators**: `str_word_count($article) . ' words'`

**Troubleshooting**:

- **`substr()` returns false**: The offset is beyond the string length. Always validate string length first.
- **Wrong character extracted**: Remember PHP uses zero-based indexing. Position 0 is the first character.
- **`ucwords()` doesn't work for names like "O'Brien"**: Use `mb_convert_case($name, MB_CASE_TITLE, 'UTF-8')` for better handling of special characters.
- **`nl2br()` doubles line breaks in HTML**: If your text already has `<br>` tags, you might see duplicates. Use the second parameter: `nl2br($text, false)` for XHTML compatibility.

## Step 3: Modern String Searching (PHP 8.0+) (~4 min)

PHP 8.0 introduced cleaner, more readable string searching functions. These are now the recommended way to check if a string contains, starts with, or ends with specific text.

**Goal**: Learn modern, intuitive string search methods.

**Actions**:

1.  Create a file named `03-modern-search.php`.

2.  Add this code:

```php
<?php
// 03-modern-search.php - Modern PHP 8.0+ string search

$sentence = "The quick brown fox jumps over the lazy dog.";
$email = "user@example.com";
$filename = "document.pdf";

// Check if a string contains a substring
if (str_contains($sentence, 'fox')) {
    echo "✓ The sentence contains 'fox'\n";
}

// Check if a string starts with a prefix
if (str_starts_with($email, 'user')) {
    echo "✓ The email starts with 'user'\n";
}

// Check if a string ends with a suffix
if (str_ends_with($filename, '.pdf')) {
    echo "✓ The filename ends with '.pdf'\n";
}

// Case-insensitive searching
if (str_contains(strtolower($sentence), 'fox')) {
    echo "✓ Case-insensitive: found 'fox'\n";
}

// Combining checks for validation
function isValidEmail(string $email): bool {
    return str_contains($email, '@') && str_contains($email, '.');
}

echo isValidEmail('test@example.com') ? "✓ Valid email\n" : "✗ Invalid email\n";
```

3.  Run the file:

```bash
php 03-modern-search.php
```

**Expected Result**:

```text
✓ The sentence contains 'fox'
✓ The email starts with 'user'
✓ The filename ends with '.pdf'
✓ Case-insensitive: found 'fox'
✓ Valid email
```

**Why it works**: These functions return simple `true` or `false` values, making your code much more readable than the older `strpos() !== false` pattern. They're also slightly faster than `strpos()` because they don't need to calculate the position.

**Troubleshooting**:

- **Error: "Call to undefined function str_contains()"**: You're running PHP 7.x. These functions require PHP 8.0+. Check your version with `php -v`.
- **Not finding text that's clearly there**: These functions are case-sensitive. Use `strtolower()` on both the haystack and needle for case-insensitive searches.

## Step 4: Finding Positions and Replacing Text (~5 min)

Sometimes you need to know exactly _where_ a substring appears, or you need to replace text.

**Goal**: Learn to find substring positions and perform text replacements.

**Actions**:

1.  Create a file named `04-find-replace.php`.

2.  Add this code:

```php
<?php
// 04-find-replace.php - Finding positions and replacing text

$sentence = "The quick brown fox jumps over the lazy dog.";

// Find the numeric position of the first occurrence
$position = strpos($sentence, 'fox');

// IMPORTANT: Use === because strpos returns 0 for matches at the start,
// and 0 == false, but 0 !== false
if ($position !== false) {
    echo "Found 'fox' at position: $position\n";
    echo "Character at that position: " . $sentence[$position] . "\n";
} else {
    echo "The word 'fox' was not found.\n";
}

// Replace a single occurrence
$newSentence = str_replace('dog', 'cat', $sentence);
echo "Single replacement: $newSentence\n";

// Replace multiple different strings at once
$find = ['brown', 'lazy'];
$replace = ['red', 'energetic'];
$multiReplace = str_replace($find, $replace, $sentence);
echo "Multiple replacements: $multiReplace\n";

// Count how many replacements were made
$count = 0;
$text = "test test test";
$result = str_replace('test', 'example', $text, $count);
echo "Replaced $count occurrences: $result\n";

// Case-insensitive replacement
$text = "PHP is great. php is powerful. Php is popular.";
$caseInsensitive = str_ireplace('php', 'Python', $text);
echo "Case-insensitive: $caseInsensitive\n";
```

3.  Run the file:

```bash
php 04-find-replace.php
```

**Expected Result**:

```text
Found 'fox' at position: 16
Character at that position: f
Single replacement: The quick brown fox jumps over the lazy cat.
Multiple replacements: The quick red fox jumps over the energetic dog.
Replaced 3 occurrences: example example example
Case-insensitive: Python is great. Python is powerful. Python is popular.
```

**Why it works**:

- `strpos()`: Returns the zero-based index of the first match, or `false` if not found.
- `str_replace()`: Replaces all occurrences. Pass arrays to do multiple find-and-replace operations in one call.
- `str_ireplace()`: Case-insensitive version of `str_replace()`.

**Troubleshooting**:

- **Bug: `if ($position)` doesn't work when the match is at position 0**: Always use `!== false` with `strpos()` to avoid this classic PHP gotcha.
- **Unexpected replacements**: `str_replace()` replaces _all_ occurrences. If you need to replace only the first occurrence, use `preg_replace()` with a limit parameter.

## Step 5: Splitting and Joining Strings (~4 min)

You'll frequently need to break a string apart into an array, or join array elements into a single string.

**Goal**: Master string-to-array and array-to-string conversions.

**Actions**:

1.  Create a file named `05-split-join.php`.

2.  Add this code:

```php
<?php
// 05-split-join.php - Splitting and joining strings

// A comma-separated list of tags
$tagsString = "php, web development, programming, tutorial";

// Explode the string into an array using the delimiter ', '
$tagsArray = explode(', ', $tagsString);
echo "Array from string:\n";
print_r($tagsArray);

// Implode the array back into a string with a different separator
$newTagsString = implode(' | ', $tagsArray);
echo "\nJoined with pipes: $newTagsString\n";

// Practical example: building a URL-friendly parameter string
$params = [
    'search' => 'php tutorial',
    'category' => 'programming',
    'sort' => 'newest'
];

$queryString = [];
foreach ($params as $key => $value) {
    $queryString[] = "$key=" . urlencode($value);
}
$url = 'https://example.com/search?' . implode('&', $queryString);
echo "\nBuilt URL: $url\n";

// Splitting by single character (space)
$sentence = "The quick brown fox";
$words = explode(' ', $sentence);
echo "\nWord count: " . count($words) . "\n";
echo "Words: " . implode(', ', $words) . "\n";
```

3.  Run the file:

```bash
php 05-split-join.php
```

**Expected Result**:

```text
Array from string:
Array
(
    [0] => php
    [1] => web development
    [2] => programming
    [3] => tutorial
)

Joined with pipes: php | web development | programming | tutorial

Built URL: https://example.com/search?search=php+tutorial&category=programming&sort=newest

Word count: 4
Words: The, quick, brown, fox
```

**Why it works**:

- `explode(string $separator, string $string)`: Splits a string into an array using the specified delimiter. The delimiter is not included in the results.
- `implode(string $separator, array $array)`: Joins array elements with the separator string (also called `join()`—they're aliases).

**Troubleshooting**:

- **Getting unexpected array elements**: Make sure your delimiter exactly matches what's in the string. `explode(',', 'a, b')` creates `['a', ' b']` (note the space).
- **Empty string results in array with one empty element**: `explode(',', '')` returns `['']`, not `[]`. Check for empty strings before exploding if this matters.

## Step 6: Formatting Strings with `sprintf` (~4 min)

When you need to create complex strings from various pieces of data, concatenation gets messy. `sprintf` (string print formatted) lets you create strings from templates with placeholders.

**Goal**: Format strings professionally with precise control over output.

**Actions**:

1.  Create a file named `06-sprintf.php`.

2.  Add this code:

```php
<?php
// 06-sprintf.php - Professional string formatting

$product = 'Laptop';
$quantity = 3;
$price = 1200.50;

// Basic sprintf with common placeholders
$format = "You ordered %d units of the %s, for a total of $%.2f.";
$output = sprintf($format, $quantity, $product, $quantity * $price);
echo $output . "\n\n";

// Padding and alignment
$id = 42;
$name = "Alice";
$score = 87.5;

// %05d = pad to 5 digits with zeros
// %-15s = left-align in 15-character field
// %6.2f = 6 total characters, 2 decimal places
echo sprintf("ID: %05d | Name: %-15s | Score: %6.2f%%\n", $id, $name, $score);

// More examples
echo sprintf("ID: %05d | Name: %-15s | Score: %6.2f%%\n", 7, "Bob", 92.33);
echo sprintf("ID: %05d | Name: %-15s | Score: %6.2f%%\n", 123, "Charlie", 78.9);

// Argument swapping (useful for internationalization)
$format_en = "%s costs $%.2f";
$format_fr = "%.2f$ est le prix de %s";  // French puts price first
echo sprintf($format_en, "Book", 29.99) . "\n";
echo sprintf($format_fr, 29.99, "Livre") . "\n";

// Date formatting with sprintf
$year = 2024;
$month = 3;
$day = 7;
$date = sprintf("%04d-%02d-%02d", $year, $month, $day);
echo "\nFormatted date: $date\n";

// Hexadecimal and binary
$number = 255;
echo sprintf("Decimal: %d, Hex: %x, Binary: %b\n", $number, $number, $number);
```

3.  Run the file:

```bash
php 06-sprintf.php
```

**Expected Result**:

```text
You ordered 3 units of the Laptop, for a total of $3601.50.

ID: 00042 | Name: Alice           | Score:  87.50%
ID: 00007 | Name: Bob             | Score:  92.33%
ID: 00123 | Name: Charlie         | Score:  78.90%
Book costs $29.99
29.99$ est le prix de Livre

Formatted date: 2024-03-07
Decimal: 255, Hex: ff, Binary: 11111111
```

**Why it works**:

- `%s`: String
- `%d`: Integer (decimal)
- `%f`: Floating-point number
- `%.2f`: Float with 2 decimal places
- `%05d`: Pad integer to 5 digits with leading zeros
- `%-15s`: Left-align string in 15-character field
- `%x` / `%b`: Hexadecimal / binary representation

**Tip**: Use `sprintf` for building SQL queries (though use prepared statements for user data), formatting reports, creating fixed-width tables, and internationalizing number/date formats.

**Troubleshooting**:

- **Wrong number of arguments**: `sprintf` must receive exactly as many arguments as there are `%` placeholders (excluding `%%` for literal %).
- **Type mismatch warnings**: Passing a string to `%d` will trigger a warning in PHP 8+. Cast explicitly: `(int)$value`.

## Step 7: Working with Multibyte Strings (~3 min)

When working with international text (Chinese, Japanese, Arabic, emoji, etc.), standard string functions can give incorrect results because they count bytes, not characters.

**Goal**: Learn to handle international text correctly.

**Actions**:

1.  Create a file named `07-multibyte.php`.

2.  Add this code:

```php
<?php
// 07-multibyte.php - Handling international text

$ascii = "Hello";
$japanese = "こんにちは";  // "Hello" in Japanese
$emoji = "Hello 👋🌍";

// Problem: strlen() counts bytes, not characters
echo "ASCII strlen: " . strlen($ascii) . "\n";
echo "Japanese strlen: " . strlen($japanese) . "\n";  // Wrong!
echo "Emoji strlen: " . strlen($emoji) . "\n";       // Wrong!

echo "\n";

// Solution: Use mb_strlen() for character count
echo "ASCII mb_strlen: " . mb_strlen($ascii) . "\n";
echo "Japanese mb_strlen: " . mb_strlen($japanese) . "\n";  // Correct!
echo "Emoji mb_strlen: " . mb_strlen($emoji) . "\n";       // Correct!

echo "\n";

// Other multibyte functions
$text = "Ñoño";
echo "strtoupper: " . strtoupper($text) . "\n";      // May be wrong
echo "mb_strtoupper: " . mb_strtoupper($text) . "\n"; // Correct!

// Substring with multibyte support
$text = "你好世界";  // "Hello World" in Chinese
echo "First 2 chars: " . mb_substr($text, 0, 2) . "\n";
```

3.  Run the file:

```bash
php 07-multibyte.php
```

**Expected Result**:

```text
ASCII strlen: 5
Japanese strlen: 15
Emoji strlen: 13

ASCII mb_strlen: 5
Japanese mb_strlen: 5
Emoji mb_strlen: 9

strtoupper: ÑOñO
mb_strtoupper: ÑOÑO

First 2 chars: 你好
```

**Why it works**: The `mb_*` (multibyte) functions are aware of character encoding and count characters correctly, not bytes. Most modern applications should use these functions by default.

**Tip**: Set a default encoding at the start of your script: `mb_internal_encoding('UTF-8');`

**Troubleshooting**:

- **Error: "Call to undefined function mb_strlen()"**: The mbstring extension isn't enabled. Install it: `apt-get install php-mbstring` (Linux) or enable it in `php.ini`.
- **Still getting wrong results**: Check your file encoding. Save files as UTF-8 without BOM.

## Exercises

Create a file `exercises.php` to complete these challenges.

### Exercise 1: URL Slug Generator

Create a function `generateSlug($title)` that converts blog post titles into URL-friendly slugs.

**Requirements**:

- Input: "My Awesome First Post!"
- Output: "my-awesome-first-post"

**Steps**:

1. Convert the string to lowercase
2. Replace all spaces with hyphens (`-`)
3. Remove any characters that aren't letters, numbers, or hyphens
4. Remove any duplicate hyphens

**Hint**: Use `strtolower()`, `str_replace()`, and `preg_replace('/[^a-z0-9-]+/', '', $text)` to remove unwanted characters.

**Validation**: Test with these inputs:

```php
echo generateSlug("My Awesome First Post!");     // my-awesome-first-post
echo generateSlug("PHP 8.4: What's New?");      // php-84-whats-new
echo generateSlug("  Hello   World  ");         // hello-world
```

### Exercise 2: CSV Parser

Parse a multi-line CSV string into formatted output.

**Given**:

```php
$csv = "dale,dale@example.com,Admin\nalice,alice@example.com,Editor\nbob,bob@example.com,Viewer";
```

**Requirements**:

1. Split the string into individual lines
2. For each line, split by comma to get user data
3. Format and print each user nicely

**Expected Output**:

```text
User #1: dale (dale@example.com) - Admin
User #2: alice (alice@example.com) - Editor
User #3: bob (bob@example.com) - Viewer
```

**Hint**: Use nested `explode()` calls and a counter variable.

### Exercise 3: Password Strength Checker

Create a function `checkPasswordStrength($password)` that returns a string describing the password strength.

**Requirements**:

- "Weak": Less than 8 characters
- "Medium": 8+ characters
- "Strong": 8+ characters AND contains both uppercase and lowercase
- "Very Strong": All above AND contains a number

**Hints**: Use `strlen()`, `str_contains()` with character ranges, or check for uppercase/lowercase with comparison.

**Validation**:

```php
echo checkPasswordStrength("pass");           // Weak
echo checkPasswordStrength("password");       // Medium
echo checkPasswordStrength("Password");       // Strong
echo checkPasswordStrength("Password123");    // Very Strong
```

## Wrap-up

Congratulations! You now have a comprehensive toolkit for working with strings in PHP. Let's recap what you've learned:

**Core Skills Acquired**:

- Measuring, cleaning, and transforming strings with `strlen()`, `trim()`, and case functions
- Extracting substrings with `substr()` and creating text excerpts
- Formatting titles and names with `ucfirst()`, `ucwords()`, and `lcfirst()`
- Converting plain text to HTML with `nl2br()` and padding with `str_pad()`
- Counting words and substring occurrences
- Using modern PHP 8.0+ search functions (`str_contains`, `str_starts_with`, `str_ends_with`)
- Finding positions and replacing text with `strpos()` and `str_replace()`
- Converting between strings and arrays with `explode()` and `implode()`
- Professional formatting with `sprintf()`
- Handling international text with multibyte functions

**Real-World Applications**:

- Cleaning and validating user input
- Creating URL slugs and search-friendly text
- Parsing CSV, log files, and other delimited data
- Building dynamic messages and reports
- Processing multilingual content

**Next Steps**:
With our understanding of core data types like strings and arrays solidified, we're ready to move on to a major new paradigm in programming: Object-Oriented Programming (OOP). In [Chapter 08](/series/php-basics/chapters/08-introduction-to-object-oriented-programming), we'll learn how to model real-world concepts using classes and objects.

::: info Code Examples
Complete, runnable examples from this chapter are available in:

- [`basic-strings.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/php-basics/code/07-strings/basic-strings.php) - String basics and common operations
- [`search-replace.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/php-basics/code/07-strings/search-replace.php) - Searching and replacing in strings
- [`split-join.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/php-basics/code/07-strings/split-join.php) - Splitting and joining strings
- `solutions/` - Solutions to chapter exercises
  :::

## Further Reading

- [PHP String Functions](https://www.php.net/manual/en/ref.strings.php) — Complete reference
- [PHP Multibyte String Functions](https://www.php.net/manual/en/ref.mbstring.php) — For international text
- [Regular Expressions in PHP](https://www.php.net/manual/en/book.pcre.php) — Advanced pattern matching
- [PHP 8.0 Release Notes](https://www.php.net/releases/8.0/en.php) — New string functions

## Knowledge Check

Test your understanding of string manipulation:

<Quiz
title="Chapter 07 Quiz: String Manipulation"
:questions="[
{
question: 'What does the trim() function do?',
options: [
{ text: 'Removes whitespace from the beginning and end of a string', correct: true, explanation: 'trim() removes spaces, tabs, newlines, and other whitespace characters from both ends of a string.' },
{ text: 'Removes all whitespace from anywhere in a string', correct: false, explanation: 'trim() only removes from the beginning and end; use str_replace() to remove all whitespace.' },
{ text: 'Shortens a string to a specific length', correct: false, explanation: 'That\'s substr(); trim() removes whitespace.' },
{ text: 'Converts a string to lowercase', correct: false, explanation: 'That\'s strtolower(); trim() removes whitespace.' }
]
},
{
question: 'What is the difference between str_contains() and strpos()?',
options: [
{ text: 'str_contains() returns true/false, strpos() returns the position or false', correct: true, explanation: 'str_contains() (PHP 8.0+) returns a boolean, while strpos() returns the numeric position (or false if not found).' },
{ text: 'They do exactly the same thing', correct: false, explanation: 'They search the same way but return different types: boolean vs integer/false.' },
{ text: 'str_contains() is case-insensitive', correct: false, explanation: 'Both are case-sensitive; use stripos() for case-insensitive searching.' },
{ text: 'strpos() is faster than str_contains()', correct: false, explanation: 'Performance is similar; the difference is in what they return.' }
]
},
{
question: 'What does explode() do?',
options: [
{ text: 'Splits a string into an array based on a delimiter', correct: true, explanation: 'explode() breaks a string into pieces wherever the delimiter appears, returning an array.' },
{ text: 'Joins an array into a string', correct: false, explanation: 'That\'s implode() or join(); explode() does the opposite.' },
{ text: 'Removes characters from a string', correct: false, explanation: 'That\'s str_replace() or trim(); explode() creates an array.' },
{ text: 'Converts a string to uppercase', correct: false, explanation: 'That\'s strtoupper(); explode() splits strings.' }
]
},
{
question: 'Why would you use mb_strlen() instead of strlen() for some strings?',
options: [
{ text: 'To correctly count characters in multibyte/international text', correct: true, explanation: 'mb_strlen() properly handles Unicode and multibyte characters like emoji and non-Latin scripts.' },
{ text: 'It\'s faster than strlen()', correct: false, explanation: 'Actually, strlen() is faster; use mb_strlen() when you need multibyte support.' },
{ text: 'It returns the length in bytes', correct: false, explanation: 'strlen() returns bytes; mb_strlen() returns character count for multibyte strings.' },
{ text: 'It works with arrays', correct: false, explanation: 'Both work only with strings; use count() for arrays.' }
]
},
{
question: 'What does sprintf() do?',
options: [
{ text: 'Formats a string with placeholders replaced by variables', correct: true, explanation: 'sprintf() creates formatted strings using format specifiers like %s, %d, %f for professional string building.' },
{ text: 'Prints a string to the screen', correct: false, explanation: 'sprintf() returns a string; use printf() to print directly, or echo sprintf().' },
{ text: 'Splits a string by spaces', correct: false, explanation: 'That\'s explode() with a space delimiter; sprintf() formats strings.' },
{ text: 'Converts a string to an integer', correct: false, explanation: 'That\'s (int) casting or intval(); sprintf() formats strings.' }
]
}
]"
/>
