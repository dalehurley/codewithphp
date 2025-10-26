<?php

declare(strict_types=1);

/**
 * CSRF Protection Basics
 * 
 * Demonstrates token generation, validation, and timing-safe comparison.
 */

session_start();

/**
 * Generate a cryptographically secure CSRF token
 * 
 * @return string 64-character hexadecimal token
 */
function generateCsrfToken(): string
{
    // Generate 32 random bytes (256 bits of entropy)
    // Convert to hexadecimal (64 characters)
    return bin2hex(random_bytes(32));
}

/**
 * Get or create CSRF token for current session
 * 
 * @return string The session's CSRF token
 */
function getCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateCsrfToken();
    }

    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token (timing-attack safe)
 * 
 * @param string $token Token from form submission
 * @return bool True if valid
 */
function validateCsrfToken(string $token): bool
{
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }

    // Use hash_equals to prevent timing attacks
    // This function takes constant time regardless of where strings differ
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Demonstrate timing attack vulnerability (DON'T USE THIS!)
 */
function unsafeValidation(string $token): bool
{
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }

    // ❌ VULNERABLE: Comparison stops at first differing character
    // Attacker can measure response time to guess token character by character
    return $_SESSION['csrf_token'] === $token;
}

echo "=== CSRF Token Generation Demo ===" . PHP_EOL . PHP_EOL;

// Generate and display token
$token = getCsrfToken();
echo "Generated CSRF Token: " . $token . PHP_EOL;
echo "Token Length: " . strlen($token) . " characters" . PHP_EOL;
echo "Entropy: 256 bits (32 bytes)" . PHP_EOL;
echo "Format: Hexadecimal" . PHP_EOL . PHP_EOL;

// Show token is consistent within session
$sameToken = getCsrfToken();
echo "Getting token again (same session): " . ($token === $sameToken ? '✓ Same token' : '❌ Different token') . PHP_EOL . PHP_EOL;

echo "=== Token Validation Demo ===" . PHP_EOL . PHP_EOL;

// Demo: Validation with correct token
$validToken = $token;
$isValid = validateCsrfToken($validToken);
echo "Validating correct token: " . ($isValid ? '✓ Valid' : '❌ Invalid') . PHP_EOL;

// Demo: Validation with incorrect token
$invalidToken = 'fake-token-12345';
$isValid = validateCsrfToken($invalidToken);
echo "Validating incorrect token: " . ($isValid ? '✓ Valid' : '❌ Invalid') . PHP_EOL;

// Demo: Validation with empty token
$emptyToken = '';
$isValid = validateCsrfToken($emptyToken);
echo "Validating empty token: " . ($isValid ? '✓ Valid' : '❌ Invalid') . PHP_EOL . PHP_EOL;

echo "=== Security Demonstration ===" . PHP_EOL . PHP_EOL;

// Show why hash_equals is important
echo "Why use hash_equals()?" . PHP_EOL;
echo "- Regular comparison (==, ===) can leak timing information" . PHP_EOL;
echo "- Attacker measures how long comparison takes" . PHP_EOL;
echo "- Faster response = differing character found earlier" . PHP_EOL;
echo "- hash_equals() always takes same time" . PHP_EOL . PHP_EOL;

// Generate multiple tokens to show uniqueness
echo "Token Uniqueness:" . PHP_EOL;
for ($i = 1; $i <= 3; $i++) {
    $newToken = generateCsrfToken();
    echo "$i. " . $newToken . PHP_EOL;
}

echo PHP_EOL . "✓ CSRF basics demonstration complete!" . PHP_EOL;
