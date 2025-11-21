<?php

declare(strict_types=1);

/**
 * Chapter 35: Cryptographic Algorithms - Password Hashing
 *
 * This example demonstrates secure password hashing:
 * - Argon2id password hashing (recommended)
 * - bcrypt password hashing (good alternative)
 * - Password verification
 * - Rehashing detection
 * - Password strength validation
 *
 * @author PHP Algorithms Series
 * @version 1.0.0
 * @license MIT
 */

/**
 * Secure password management using modern hashing algorithms
 */
class PasswordManager
{
    // Use Argon2id (best) or bcrypt (good fallback)
    private const ALGORITHM = PASSWORD_ARGON2ID;

    // Argon2id parameters
    private const MEMORY_COST = 65536;  // 64 MB
    private const TIME_COST = 4;         // 4 iterations
    private const THREADS = 2;           // 2 parallel threads

    /**
     * Hash a password using Argon2id
     *
     * @param string $password Password to hash
     * @return string Hashed password
     * @throws RuntimeException If hashing fails
     */
    public static function hash(string $password): string
    {
        $hash = password_hash($password, self::ALGORITHM, [
            'memory_cost' => self::MEMORY_COST,
            'time_cost' => self::TIME_COST,
            'threads' => self::THREADS
        ]);

        if ($hash === false) {
            throw new RuntimeException("Password hashing failed");
        }

        return $hash;
    }

    /**
     * Hash a password using bcrypt (alternative)
     *
     * @param string $password Password to hash
     * @param int $cost Cost parameter (4-31, default 12)
     * @return string Hashed password
     */
    public static function hashBcrypt(string $password, int $cost = 12): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
    }

    /**
     * Verify a password against a hash (timing-safe)
     *
     * @param string $password Password to verify
     * @param string $hash Hash to verify against
     * @return bool True if password matches
     */
    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Check if a hash needs to be rehashed with updated parameters
     *
     * @param string $hash Hash to check
     * @return bool True if rehashing is needed
     */
    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, self::ALGORITHM, [
            'memory_cost' => self::MEMORY_COST,
            'time_cost' => self::TIME_COST,
            'threads' => self::THREADS
        ]);
    }

    /**
     * Get information about a password hash
     *
     * @param string $hash Hash to analyze
     * @return array Hash information
     */
    public static function getInfo(string $hash): array
    {
        return password_get_info($hash);
    }

    /**
     * Calculate password strength score
     *
     * @param string $password Password to evaluate
     * @return array Score and feedback
     */
    public static function strength(string $password): array
    {
        $score = 0;
        $feedback = [];

        // Length check
        $length = strlen($password);
        if ($length >= 16) {
            $score += 3;
        } elseif ($length >= 12) {
            $score += 2;
        } elseif ($length >= 8) {
            $score += 1;
        } else {
            $feedback[] = "Password should be at least 8 characters";
        }

        // Character variety checks
        if (preg_match('/[a-z]/', $password)) {
            $score++;
        } else {
            $feedback[] = "Add lowercase letters";
        }

        if (preg_match('/[A-Z]/', $password)) {
            $score++;
        } else {
            $feedback[] = "Add uppercase letters";
        }

        if (preg_match('/[0-9]/', $password)) {
            $score++;
        } else {
            $feedback[] = "Add numbers";
        }

        if (preg_match('/[^a-zA-Z0-9]/', $password)) {
            $score++;
        } else {
            $feedback[] = "Add special characters (!@#$%^&*)";
        }

        // Pattern detection
        if (preg_match('/(.)\1{2,}/', $password)) {
            $score--;
            $feedback[] = "Avoid repeated characters";
        }

        if (preg_match('/^[0-9]+$/', $password)) {
            $score -= 2;
            $feedback[] = "Don't use only numbers";
        }

        // Common password check (simplified)
        $commonPasswords = [
            'password', '123456', '12345678', 'qwerty', 'abc123',
            'monkey', '1234567', 'letmein', 'trustno1', 'dragon',
            'baseball', 'iloveyou', 'master', 'sunshine', 'ashley',
            'bailey', 'passw0rd', 'shadow', '123123', '654321',
            'superman', 'qazwsx', 'michael', 'football'
        ];

        if (in_array(strtolower($password), $commonPasswords)) {
            $score = 0;
            $feedback = ["This is a commonly used password - choose something unique"];
        }

        // Calculate final score (0-10)
        $score = max(0, min(10, $score));

        return [
            'score' => $score,
            'strength' => self::getStrengthLabel($score),
            'feedback' => $feedback
        ];
    }

    /**
     * Get strength label from score
     *
     * @param int $score Strength score
     * @return string Strength label
     */
    private static function getStrengthLabel(int $score): string
    {
        return match(true) {
            $score >= 8 => 'Very Strong',
            $score >= 6 => 'Strong',
            $score >= 4 => 'Medium',
            $score >= 2 => 'Weak',
            default => 'Very Weak'
        };
    }

    /**
     * Generate a random secure password
     *
     * @param int $length Password length
     * @param bool $includeSymbols Include special characters
     * @return string Generated password
     */
    public static function generate(int $length = 16, bool $includeSymbols = true): string
    {
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()_-+=[]{}|;:,.<>?';

        $chars = $lowercase . $uppercase . $numbers;
        if ($includeSymbols) {
            $chars .= $symbols;
        }

        $password = '';
        $charsLength = strlen($chars);

        // Ensure at least one of each type
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];

        if ($includeSymbols) {
            $password .= $symbols[random_int(0, strlen($symbols) - 1)];
        }

        // Fill remaining length
        for ($i = strlen($password); $i < $length; $i++) {
            $password .= $chars[random_int(0, $charsLength - 1)];
        }

        // Shuffle the password
        $passwordArray = str_split($password);
        for ($i = count($passwordArray) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$passwordArray[$i], $passwordArray[$j]] = [$passwordArray[$j], $passwordArray[$i]];
        }

        return implode('', $passwordArray);
    }
}

/**
 * User authentication system example
 */
class UserAuthSystem
{
    private array $users = [];

    /**
     * Register a new user
     *
     * @param string $username Username
     * @param string $password Password
     * @return bool True if registration successful
     */
    public function register(string $username, string $password): bool
    {
        if (isset($this->users[$username])) {
            return false; // User already exists
        }

        // Check password strength
        $strength = PasswordManager::strength($password);
        if ($strength['score'] < 4) {
            throw new RuntimeException(
                "Password too weak. " . implode(", ", $strength['feedback'])
            );
        }

        // Hash and store password
        $this->users[$username] = [
            'password_hash' => PasswordManager::hash($password),
            'created_at' => time()
        ];

        return true;
    }

    /**
     * Authenticate a user
     *
     * @param string $username Username
     * @param string $password Password
     * @return bool True if authentication successful
     */
    public function authenticate(string $username, string $password): bool
    {
        if (!isset($this->users[$username])) {
            return false;
        }

        $user = $this->users[$username];

        // Verify password
        if (!PasswordManager::verify($password, $user['password_hash'])) {
            return false;
        }

        // Check if rehashing is needed
        if (PasswordManager::needsRehash($user['password_hash'])) {
            $this->users[$username]['password_hash'] = PasswordManager::hash($password);
        }

        return true;
    }

    /**
     * Change user password
     *
     * @param string $username Username
     * @param string $oldPassword Old password
     * @param string $newPassword New password
     * @return bool True if password changed
     */
    public function changePassword(
        string $username,
        string $oldPassword,
        string $newPassword
    ): bool {
        if (!$this->authenticate($username, $oldPassword)) {
            return false;
        }

        // Check new password strength
        $strength = PasswordManager::strength($newPassword);
        if ($strength['score'] < 4) {
            throw new RuntimeException(
                "New password too weak. " . implode(", ", $strength['feedback'])
            );
        }

        $this->users[$username]['password_hash'] = PasswordManager::hash($newPassword);
        return true;
    }
}

// =============================================================================
// DEMONSTRATION
// =============================================================================

echo "=== Password Hashing Demo ===\n\n";

// Example 1: Basic password hashing
echo "1. Password Hashing:\n";
$password = "MySecureP@ssw0rd!2024";

echo "Original password: $password\n";

$hash = PasswordManager::hash($password);
echo "Argon2id hash: $hash\n";

$bcryptHash = PasswordManager::hashBcrypt($password);
echo "Bcrypt hash: $bcryptHash\n";

// Example 2: Password verification
echo "\n2. Password Verification:\n";
$testPasswords = [
    "MySecureP@ssw0rd!2024",  // Correct
    "WrongPassword123",        // Wrong
    "mysecurep@ssw0rd!2024",  // Wrong case
];

foreach ($testPasswords as $testPwd) {
    $valid = PasswordManager::verify($testPwd, $hash);
    echo "Password '$testPwd': " . ($valid ? "✓ Valid" : "✗ Invalid") . "\n";
}

// Example 3: Hash information
echo "\n3. Hash Information:\n";
$info = PasswordManager::getInfo($hash);
echo "Algorithm: " . ($info['algoName'] ?? 'unknown') . "\n";
echo "Options: " . json_encode($info['options'] ?? []) . "\n";

// Example 4: Rehashing detection
echo "\n4. Rehashing Detection:\n";
echo "Current hash needs rehash: " .
     (PasswordManager::needsRehash($hash) ? "Yes" : "No") . "\n";

// Create an old bcrypt hash with low cost
$oldHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 4]);
echo "Old bcrypt hash (cost=4) needs rehash: " .
     (PasswordManager::needsRehash($oldHash) ? "Yes" : "No") . "\n";

// Example 5: Password strength evaluation
echo "\n5. Password Strength Evaluation:\n";

$testPasswords = [
    "password123",
    "P@ssw0rd",
    "MySecureP@ssw0rd!",
    "C0mpl3x!P@ssw0rd#2024",
    "aaaaaaaa",
    "12345678"
];

foreach ($testPasswords as $pwd) {
    $strength = PasswordManager::strength($pwd);
    echo sprintf(
        "%-25s Score: %2d/10 - %-12s %s\n",
        "\"$pwd\"",
        $strength['score'],
        "[{$strength['strength']}]",
        empty($strength['feedback']) ? "✓" : "(" . $strength['feedback'][0] . ")"
    );
}

// Example 6: Password generation
echo "\n6. Secure Password Generation:\n";

for ($i = 0; $i < 5; $i++) {
    $generated = PasswordManager::generate(16, true);
    $strength = PasswordManager::strength($generated);

    echo "Generated: $generated ";
    echo "[{$strength['strength']}, Score: {$strength['score']}/10]\n";
}

// Example 7: User authentication system
echo "\n7. User Authentication System:\n";

$authSystem = new UserAuthSystem();

// Register users
try {
    $authSystem->register('alice', 'SecureAlice@2024!');
    echo "✓ User 'alice' registered successfully\n";

    $authSystem->register('bob', 'StrongBob#Pass2024');
    echo "✓ User 'bob' registered successfully\n";

    // Try weak password
    try {
        $authSystem->register('charlie', 'password123');
        echo "✓ User 'charlie' registered\n";
    } catch (RuntimeException $e) {
        echo "✗ User 'charlie' registration failed: {$e->getMessage()}\n";
    }
} catch (Exception $e) {
    echo "Registration error: {$e->getMessage()}\n";
}

// Authenticate users
echo "\nAuthentication tests:\n";
$authTests = [
    ['alice', 'SecureAlice@2024!', true],
    ['alice', 'wrongpassword', false],
    ['bob', 'StrongBob#Pass2024', true],
    ['charlie', 'password123', false], // User doesn't exist
];

foreach ($authTests as [$username, $password, $expected]) {
    $result = $authSystem->authenticate($username, $password);
    $status = $result ? "✓" : "✗";
    $expectation = $result === $expected ? "(expected)" : "(unexpected)";

    echo "   $status User '$username': " .
         ($result ? "Authenticated" : "Failed") . " $expectation\n";
}

// Change password
echo "\nPassword change:\n";
$changed = $authSystem->changePassword('alice', 'SecureAlice@2024!', 'NewSecureAlice@2024!');
echo "Alice changed password: " . ($changed ? "✓ Success" : "✗ Failed") . "\n";

// Verify new password works
$authWithNew = $authSystem->authenticate('alice', 'NewSecureAlice@2024!');
echo "Login with new password: " . ($authWithNew ? "✓ Success" : "✗ Failed") . "\n";

// Example 8: Performance comparison
echo "\n8. Hash Algorithm Performance:\n";

$testPassword = "TestPassword@123!";
$iterations = 100;

echo "Hashing '$testPassword' $iterations times:\n";

// Argon2id
$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    PasswordManager::hash($testPassword);
}
$argon2Time = microtime(true) - $start;

printf("   Argon2id: %6.2f ms/hash\n", ($argon2Time / $iterations) * 1000);

// bcrypt
$start = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
    PasswordManager::hashBcrypt($testPassword);
}
$bcryptTime = microtime(true) - $start;

printf("   Bcrypt:   %6.2f ms/hash\n", ($bcryptTime / $iterations) * 1000);

echo "\nNote: Slower is better for password hashing!\n";
echo "This prevents brute-force attacks.\n";

echo "\n=== Demo Complete ===\n";
