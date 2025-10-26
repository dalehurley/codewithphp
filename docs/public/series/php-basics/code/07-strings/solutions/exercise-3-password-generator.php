<?php

declare(strict_types=1);

/**
 * Exercise 3: Secure Password Generator
 * 
 * Create a function that generates a random secure password with options for:
 * - Length
 * - Include uppercase letters
 * - Include lowercase letters
 * - Include numbers
 * - Include special characters
 */

function generatePassword(
    int $length = 12,
    bool $includeUppercase = true,
    bool $includeLowercase = true,
    bool $includeNumbers = true,
    bool $includeSpecial = true
): string {
    $characters = '';
    $password = '';

    // Build character set
    if ($includeUppercase) {
        $characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    }
    if ($includeLowercase) {
        $characters .= 'abcdefghijklmnopqrstuvwxyz';
    }
    if ($includeNumbers) {
        $characters .= '0123456789';
    }
    if ($includeSpecial) {
        $characters .= '!@#$%^&*()_+-=[]{}|;:,.<>?';
    }

    // If no character sets selected, use alphanumeric
    if (empty($characters)) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    }

    $charactersLength = strlen($characters);

    // Generate password
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[random_int(0, $charactersLength - 1)];
    }

    return $password;
}

function checkPasswordStrength(string $password): array
{
    $strength = [
        'score' => 0,
        'has_uppercase' => preg_match('/[A-Z]/', $password) === 1,
        'has_lowercase' => preg_match('/[a-z]/', $password) === 1,
        'has_numbers' => preg_match('/[0-9]/', $password) === 1,
        'has_special' => preg_match('/[^a-zA-Z0-9]/', $password) === 1,
        'length' => strlen($password)
    ];

    // Calculate strength score
    if ($strength['length'] >= 8) $strength['score']++;
    if ($strength['length'] >= 12) $strength['score']++;
    if ($strength['length'] >= 16) $strength['score']++;
    if ($strength['has_uppercase']) $strength['score']++;
    if ($strength['has_lowercase']) $strength['score']++;
    if ($strength['has_numbers']) $strength['score']++;
    if ($strength['has_special']) $strength['score']++;

    // Determine level
    $strength['level'] = match (true) {
        $strength['score'] >= 6 => 'Strong',
        $strength['score'] >= 4 => 'Medium',
        default => 'Weak'
    };

    return $strength;
}

// Test the password generator
echo "=== Secure Password Generator ===" . PHP_EOL . PHP_EOL;

// Generate different types of passwords
$passwords = [
    'Default (12 chars, all types)' => generatePassword(),
    'Long (20 chars)' => generatePassword(20),
    'Only letters (16 chars)' => generatePassword(16, true, true, false, false),
    'Only numbers (8 chars)' => generatePassword(8, false, false, true, false),
    'Alphanumeric (14 chars)' => generatePassword(14, true, true, true, false),
    'Max security (16 chars)' => generatePassword(16, true, true, true, true)
];

foreach ($passwords as $type => $password) {
    echo "$type:" . PHP_EOL;
    echo "  Password: $password" . PHP_EOL;

    $strength = checkPasswordStrength($password);
    echo "  Length: {$strength['length']}" . PHP_EOL;
    echo "  Strength: {$strength['level']} (Score: {$strength['score']}/7)" . PHP_EOL;
    echo "  Has uppercase: " . ($strength['has_uppercase'] ? 'Yes' : 'No') . PHP_EOL;
    echo "  Has lowercase: " . ($strength['has_lowercase'] ? 'Yes' : 'No') . PHP_EOL;
    echo "  Has numbers: " . ($strength['has_numbers'] ? 'Yes' : 'No') . PHP_EOL;
    echo "  Has special chars: " . ($strength['has_special'] ? 'Yes' : 'No') . PHP_EOL;
    echo PHP_EOL;
}

// Generate multiple passwords
echo "=== Generate 5 Random Passwords ===" . PHP_EOL . PHP_EOL;
for ($i = 1; $i <= 5; $i++) {
    $pwd = generatePassword(16);
    $strength = checkPasswordStrength($pwd);
    echo "$i. $pwd ({$strength['level']})" . PHP_EOL;
}
