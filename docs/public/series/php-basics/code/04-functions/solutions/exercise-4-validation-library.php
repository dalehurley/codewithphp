<?php

declare(strict_types=1);

/**
 * Exercise 4: Create a Validation Library
 * 
 * Build a set of validation functions useful for form processing:
 * - isValidEmail(string $email): bool
 * - isValidUrl(string $url): bool
 * - isStrongPassword(string $password): bool (min 8 chars, has uppercase, lowercase, number)
 * - isValidUsername(string $username): bool (alphanumeric, 3-20 chars)
 * - sanitizeString(string $input): string (remove HTML tags)
 */

// Solution:

function isValidEmail(string $email): bool
{
    // Use filter_var with email validation filter
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function isValidUrl(string $url): bool
{
    // Use filter_var with URL validation filter
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

function isStrongPassword(string $password): bool
{
    // Check minimum length
    if (strlen($password) < 8) {
        return false;
    }

    // Check for at least one uppercase letter
    if (!preg_match('/[A-Z]/', $password)) {
        return false;
    }

    // Check for at least one lowercase letter
    if (!preg_match('/[a-z]/', $password)) {
        return false;
    }

    // Check for at least one number
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }

    return true;
}

function isValidUsername(string $username): bool
{
    // Must be alphanumeric and between 3-20 characters
    return preg_match('/^[a-zA-Z0-9]{3,20}$/', $username) === 1;
}

function sanitizeString(string $input): string
{
    // Remove HTML and PHP tags
    $clean = strip_tags($input);

    // Trim whitespace
    $clean = trim($clean);

    return $clean;
}

// Additional validation functions
function isValidAge(int $age): bool
{
    return $age >= 0 && $age <= 150;
}

function isValidZipCode(string $zip): bool
{
    // US ZIP code format (12345 or 12345-6789)
    return preg_match('/^\d{5}(-\d{4})?$/', $zip) === 1;
}

// Comprehensive validation with error messages
function validateUser(array $data): array
{
    $errors = [];

    // Validate username
    if (!isset($data['username']) || empty($data['username'])) {
        $errors['username'] = 'Username is required';
    } elseif (!isValidUsername($data['username'])) {
        $errors['username'] = 'Username must be 3-20 alphanumeric characters';
    }

    // Validate email
    if (!isset($data['email']) || empty($data['email'])) {
        $errors['email'] = 'Email is required';
    } elseif (!isValidEmail($data['email'])) {
        $errors['email'] = 'Invalid email format';
    }

    // Validate password
    if (!isset($data['password']) || empty($data['password'])) {
        $errors['password'] = 'Password is required';
    } elseif (!isStrongPassword($data['password'])) {
        $errors['password'] = 'Password must be at least 8 characters with uppercase, lowercase, and number';
    }

    // Validate age
    if (isset($data['age']) && !isValidAge($data['age'])) {
        $errors['age'] = 'Age must be between 0 and 150';
    }

    return [
        'isValid' => count($errors) === 0,
        'errors' => $errors
    ];
}

// Testing the functions
echo "=== Validation Library ===" . PHP_EOL . PHP_EOL;

// Test email validation
echo "1. Email Validation:" . PHP_EOL;
$emails = [
    'user@example.com',
    'invalid.email',
    'test@domain.co.uk',
    '@invalid.com'
];

foreach ($emails as $email) {
    $valid = isValidEmail($email) ? '✓ Valid' : '✗ Invalid';
    echo "  $email → $valid" . PHP_EOL;
}
echo PHP_EOL;

// Test URL validation
echo "2. URL Validation:" . PHP_EOL;
$urls = [
    'https://example.com',
    'http://example.com/path',
    'invalid-url',
    'ftp://files.example.com'
];

foreach ($urls as $url) {
    $valid = isValidUrl($url) ? '✓ Valid' : '✗ Invalid';
    echo "  $url → $valid" . PHP_EOL;
}
echo PHP_EOL;

// Test password strength
echo "3. Password Strength:" . PHP_EOL;
$passwords = [
    'weak',
    'Stronger1',
    'UPPERCASE1',
    'lowercase1',
    'NoNumbers'
];

foreach ($passwords as $password) {
    $strong = isStrongPassword($password) ? '✓ Strong' : '✗ Weak';
    echo "  '$password' → $strong" . PHP_EOL;
}
echo PHP_EOL;

// Test username validation
echo "4. Username Validation:" . PHP_EOL;
$usernames = [
    'john',
    'user123',
    'ab',                      // Too short
    'user_name',               // Contains underscore
    'verylongusername12345678' // Too long
];

foreach ($usernames as $username) {
    $valid = isValidUsername($username) ? '✓ Valid' : '✗ Invalid';
    echo "  '$username' → $valid" . PHP_EOL;
}
echo PHP_EOL;

// Test sanitization
echo "5. String Sanitization:" . PHP_EOL;
$dirtyInputs = [
    '<script>alert("xss")</script>Hello',
    '  Whitespace around  ',
    '<b>Bold</b> text with <i>tags</i>'
];

foreach ($dirtyInputs as $input) {
    $clean = sanitizeString($input);
    echo "  Input:  '$input'" . PHP_EOL;
    echo "  Output: '$clean'" . PHP_EOL;
}
echo PHP_EOL;

// Test comprehensive user validation
echo "6. Comprehensive User Validation:" . PHP_EOL . PHP_EOL;

$testUser1 = [
    'username' => 'alice123',
    'email' => 'alice@example.com',
    'password' => 'SecurePass1',
    'age' => 28
];

echo "Test User 1:" . PHP_EOL;
$validation1 = validateUser($testUser1);
if ($validation1['isValid']) {
    echo "  ✓ All validations passed!" . PHP_EOL;
} else {
    echo "  ✗ Validation errors:" . PHP_EOL;
    foreach ($validation1['errors'] as $field => $error) {
        echo "    - $field: $error" . PHP_EOL;
    }
}
echo PHP_EOL;

$testUser2 = [
    'username' => 'ab',                 // Too short
    'email' => 'invalid-email',         // Invalid format
    'password' => 'weak',               // Not strong
    'age' => 200                        // Invalid age
];

echo "Test User 2:" . PHP_EOL;
$validation2 = validateUser($testUser2);
if ($validation2['isValid']) {
    echo "  ✓ All validations passed!" . PHP_EOL;
} else {
    echo "  ✗ Validation errors:" . PHP_EOL;
    foreach ($validation2['errors'] as $field => $error) {
        echo "    - $field: $error" . PHP_EOL;
    }
}
