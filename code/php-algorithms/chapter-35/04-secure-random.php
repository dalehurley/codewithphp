<?php

declare(strict_types=1);

/**
 * Chapter 35: Cryptographic Algorithms - Secure Random Generation
 *
 * This example demonstrates cryptographically secure random number generation:
 * - Random bytes generation
 * - Random integers in range
 * - Random strings and tokens
 * - UUID generation
 * - TOTP (Time-based One-Time Password)
 *
 * @author PHP Algorithms Series
 * @version 1.0.0
 * @license MIT
 */

/**
 * Cryptographically secure random number utilities
 */
class SecureRandom
{
    /**
     * Generate random bytes
     *
     * @param int $length Number of bytes
     * @return string Random bytes
     */
    public static function bytes(int $length): string
    {
        return random_bytes($length);
    }

    /**
     * Generate random integer in range [min, max]
     *
     * @param int $min Minimum value (inclusive)
     * @param int $max Maximum value (inclusive)
     * @return int Random integer
     */
    public static function int(int $min, int $max): int
    {
        return random_int($min, $max);
    }

    /**
     * Generate random alphanumeric string
     *
     * @param int $length String length
     * @return string Random string
     */
    public static function string(int $length): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[self::int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * Generate random hex string
     *
     * @param int $length Number of bytes (output will be 2x this length)
     * @return string Random hex string
     */
    public static function hex(int $length): string
    {
        return bin2hex(self::bytes($length));
    }

    /**
     * Generate UUIDv4 (random)
     *
     * @return string UUID string
     */
    public static function uuid(): string
    {
        $data = self::bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);  // Version 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);  // Variant

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Generate URL-safe token
     *
     * @param int $length Token length in bytes
     * @return string URL-safe token
     */
    public static function token(int $length = 32): string
    {
        return rtrim(strtr(base64_encode(self::bytes($length)), '+/', '-_'), '=');
    }

    /**
     * Cryptographically secure array shuffle
     *
     * @param array $array Array to shuffle
     * @return array Shuffled array
     */
    public static function shuffle(array $array): array
    {
        $count = count($array);

        for ($i = $count - 1; $i > 0; $i--) {
            $j = self::int(0, $i);
            [$array[$i], $array[$j]] = [$array[$j], $array[$i]];
        }

        return $array;
    }

    /**
     * Select random element from array
     *
     * @param array $array Array to select from
     * @return mixed Random element
     */
    public static function choice(array $array): mixed
    {
        if (empty($array)) {
            throw new InvalidArgumentException("Array cannot be empty");
        }

        $keys = array_keys($array);
        $randomKey = $keys[self::int(0, count($keys) - 1)];

        return $array[$randomKey];
    }

    /**
     * Select multiple random elements from array
     *
     * @param array $array Array to select from
     * @param int $count Number of elements to select
     * @return array Random elements
     */
    public static function sample(array $array, int $count): array
    {
        if ($count > count($array)) {
            throw new InvalidArgumentException("Count cannot exceed array size");
        }

        $shuffled = self::shuffle($array);
        return array_slice($shuffled, 0, $count);
    }
}

/**
 * Time-based One-Time Password (TOTP) implementation
 */
class TOTP
{
    private const PERIOD = 30;  // 30 seconds
    private const DIGITS = 6;   // 6-digit codes

    /**
     * Generate a random secret for TOTP
     *
     * @return string Base64-encoded secret
     */
    public static function generateSecret(): string
    {
        return base64_encode(SecureRandom::bytes(20));
    }

    /**
     * Get current TOTP code
     *
     * @param string $secret Base64-encoded secret
     * @param int|null $timestamp Optional timestamp (default: current time)
     * @return string 6-digit code
     */
    public static function getCode(string $secret, ?int $timestamp = null): string
    {
        $timestamp = $timestamp ?? time();
        $counter = floor($timestamp / self::PERIOD);

        $secret = base64_decode($secret);

        // Convert counter to binary string
        $counterBinary = pack('N*', 0, $counter);

        // Generate HMAC
        $hash = hash_hmac('sha1', $counterBinary, $secret, true);

        // Dynamic truncation
        $offset = ord($hash[strlen($hash) - 1]) & 0x0f;
        $code = (
            ((ord($hash[$offset]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        );

        $code = $code % pow(10, self::DIGITS);

        return str_pad((string) $code, self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Verify a TOTP code
     *
     * @param string $secret Base64-encoded secret
     * @param string $code Code to verify
     * @param int $window Time window (periods to check before/after)
     * @return bool True if code is valid
     */
    public static function verify(string $secret, string $code, int $window = 1): bool
    {
        $timestamp = time();

        // Check current period and adjacent periods (to account for clock drift)
        for ($i = -$window; $i <= $window; $i++) {
            $testTime = $timestamp + ($i * self::PERIOD);
            $testCode = self::getCode($secret, $testTime);

            if (hash_equals($testCode, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get QR code URL for Google Authenticator
     *
     * @param string $secret Base64-encoded secret
     * @param string $label User identifier
     * @param string $issuer Application name
     * @return string QR code URL
     */
    public static function getQRCodeUrl(
        string $secret,
        string $label,
        string $issuer = 'MyApp'
    ): string {
        $url = sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s',
            urlencode($issuer),
            urlencode($label),
            $secret,
            urlencode($issuer)
        );

        return 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($url);
    }
}

// =============================================================================
// DEMONSTRATION
// =============================================================================

echo "=== Secure Random Generation Demo ===\n\n";

// Example 1: Basic random generation
echo "1. Basic Random Generation:\n";
echo "Random bytes (hex, 16 bytes): " . bin2hex(SecureRandom::bytes(16)) . "\n";
echo "Random int (1-100): " . SecureRandom::int(1, 100) . "\n";
echo "Random int (1-100): " . SecureRandom::int(1, 100) . "\n";
echo "Random int (1-100): " . SecureRandom::int(1, 100) . "\n";
echo "Random string (20 chars): " . SecureRandom::string(20) . "\n";
echo "Random hex (16 bytes): " . SecureRandom::hex(16) . "\n";

// Example 2: UUID generation
echo "\n2. UUID Generation:\n";
for ($i = 0; $i < 5; $i++) {
    echo "UUID " . ($i + 1) . ": " . SecureRandom::uuid() . "\n";
}

// Example 3: URL-safe tokens
echo "\n3. URL-Safe Tokens:\n";
echo "API Key: " . SecureRandom::token(32) . "\n";
echo "Session Token: " . SecureRandom::token(64) . "\n";
echo "Reset Token: " . SecureRandom::token(48) . "\n";

// Example 4: Array operations
echo "\n4. Array Operations:\n";
$items = ['apple', 'banana', 'orange', 'grape', 'mango'];
echo "Original array: " . implode(', ', $items) . "\n";

$shuffled = SecureRandom::shuffle($items);
echo "Shuffled: " . implode(', ', $shuffled) . "\n";

$choice = SecureRandom::choice($items);
echo "Random choice: $choice\n";

$sample = SecureRandom::sample($items, 3);
echo "Random sample (3): " . implode(', ', $sample) . "\n";

// Example 5: Distribution test
echo "\n5. Distribution Test (10,000 samples):\n";
$distribution = array_fill(1, 6, 0);

for ($i = 0; $i < 10000; $i++) {
    $roll = SecureRandom::int(1, 6);
    $distribution[$roll]++;
}

echo "Dice roll distribution:\n";
foreach ($distribution as $value => $count) {
    $percentage = ($count / 10000) * 100;
    $bar = str_repeat('█', (int)($percentage / 2));
    printf("   %d: %5d (%.1f%%) %s\n", $value, $count, $percentage, $bar);
}

// Example 6: TOTP (Two-Factor Authentication)
echo "\n6. TOTP (Two-Factor Authentication):\n";
$secret = TOTP::generateSecret();

echo "Secret key: $secret\n";
echo "Current code: " . TOTP::getCode($secret) . "\n";

// Verify code
$code = TOTP::getCode($secret);
$isValid = TOTP::verify($secret, $code);
echo "Code verification: " . ($isValid ? "✓ Valid" : "✗ Invalid") . "\n";

// Test with wrong code
$wrongCode = '000000';
$isWrongValid = TOTP::verify($secret, $wrongCode);
echo "Wrong code verification: " . ($isWrongValid ? "✓ Valid" : "✗ Invalid (expected)") . "\n";

// QR Code URL
$qrUrl = TOTP::getQRCodeUrl($secret, 'user@example.com', 'DemoApp');
echo "\nQR Code URL (scan with Google Authenticator):\n$qrUrl\n";

// Show code changes over time
echo "\nCode changes every 30 seconds:\n";
$currentCode = TOTP::getCode($secret);
echo "   Current code: $currentCode\n";
echo "   Previous code: " . TOTP::getCode($secret, time() - 30) . "\n";
echo "   Next code: " . TOTP::getCode($secret, time() + 30) . "\n";

// Example 7: Real-world - Password reset tokens
echo "\n7. Real-World Example - Password Reset System:\n";

class PasswordResetSystem
{
    private array $tokens = [];

    public function generateResetToken(string $email): string
    {
        $token = SecureRandom::token(48);
        $expiry = time() + 3600; // 1 hour

        $this->tokens[$token] = [
            'email' => $email,
            'expiry' => $expiry,
            'created' => time()
        ];

        return $token;
    }

    public function verifyResetToken(string $token): ?string
    {
        if (!isset($this->tokens[$token])) {
            return null; // Token not found
        }

        $data = $this->tokens[$token];

        if (time() > $data['expiry']) {
            unset($this->tokens[$token]);
            return null; // Token expired
        }

        return $data['email'];
    }

    public function useResetToken(string $token): bool
    {
        $email = $this->verifyResetToken($token);

        if ($email === null) {
            return false;
        }

        // Token is valid, remove it (one-time use)
        unset($this->tokens[$token]);
        return true;
    }
}

$resetSystem = new PasswordResetSystem();

$userEmail = 'user@example.com';
$resetToken = $resetSystem->generateResetToken($userEmail);

echo "Reset token generated for $userEmail:\n";
echo "Token: $resetToken\n";

$verifiedEmail = $resetSystem->verifyResetToken($resetToken);
echo "Token verification: " . ($verifiedEmail ? "✓ Valid for $verifiedEmail" : "✗ Invalid") . "\n";

$used = $resetSystem->useResetToken($resetToken);
echo "Token used: " . ($used ? "✓ Success" : "✗ Failed") . "\n";

// Try to use again
$usedAgain = $resetSystem->useResetToken($resetToken);
echo "Token reuse: " . ($usedAgain ? "✓ Success" : "✗ Failed (expected)") . "\n";

// Example 8: Performance test
echo "\n8. Performance Test:\n";
$iterations = 10000;

echo "Generating $iterations secure random values:\n";

$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    SecureRandom::bytes(32);
}
$bytesTime = microtime(true) - $start;

printf("   Random bytes (32): %.2f μs/op\n", ($bytesTime / $iterations) * 1000000);

$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    SecureRandom::int(1, 1000000);
}
$intTime = microtime(true) - $start;

printf("   Random int:        %.2f μs/op\n", ($intTime / $iterations) * 1000000);

$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    SecureRandom::uuid();
}
$uuidTime = microtime(true) - $start;

printf("   UUID:              %.2f μs/op\n", ($uuidTime / $iterations) * 1000000);

$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    SecureRandom::token(32);
}
$tokenTime = microtime(true) - $start;

printf("   Token (32 bytes):  %.2f μs/op\n", ($tokenTime / $iterations) * 1000000);

echo "\n=== Demo Complete ===\n";
