<?php

declare(strict_types=1);

/**
 * Exercise 1: Email Domain Extractor and Validator
 * 
 * Create functions to:
 * - Extract the domain from an email address
 * - Validate email format
 * - Check if email is from a specific domain
 */

function extractDomain(string $email): ?string
{
    // Find @ symbol position
    $atPos = strpos($email, '@');

    if ($atPos === false) {
        return null;
    }

    // Extract everything after @
    return substr($email, $atPos + 1);
}

function isValidEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function isFromDomain(string $email, string $domain): bool
{
    $emailDomain = extractDomain($email);
    return $emailDomain !== null && strcasecmp($emailDomain, $domain) === 0;
}

// Test the functions
echo "=== Email Domain Extractor ===" . PHP_EOL . PHP_EOL;

$testEmails = [
    'user@example.com',
    'john.doe@company.co.uk',
    'admin@EXAMPLE.COM',
    'invalid-email',
    'test@test@example.com'
];

foreach ($testEmails as $email) {
    echo "Email: $email" . PHP_EOL;
    echo "  Valid: " . (isValidEmail($email) ? 'Yes' : 'No') . PHP_EOL;
    $domain = extractDomain($email);
    echo "  Domain: " . ($domain ?? 'N/A') . PHP_EOL;
    echo "  From example.com: " . (isFromDomain($email, 'example.com') ? 'Yes' : 'No') . PHP_EOL;
    echo PHP_EOL;
}

// Additional functionality: Get username
function extractUsername(string $email): ?string
{
    if (!isValidEmail($email)) {
        return null;
    }

    return strstr($email, '@', true);
}

echo "=== Username Extraction ===" . PHP_EOL . PHP_EOL;
$email = 'john.doe@example.com';
echo "Email: $email" . PHP_EOL;
echo "Username: " . extractUsername($email) . PHP_EOL;
echo "Domain: " . extractDomain($email) . PHP_EOL;
