<?php

declare(strict_types=1);

/**
 * Complete Authentication System
 * 
 * Demonstrates a production-ready authentication system
 * using sessions and secure password handling.
 */

session_start();

echo "=== Authentication System ===" . PHP_EOL . PHP_EOL;

// Simulated user database
$users = [
    'john@example.com' => [
        'id' => 1,
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => password_hash('secret123', PASSWORD_DEFAULT),
        'role' => 'admin'
    ],
    'jane@example.com' => [
        'id' => 2,
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'password' => password_hash('password456', PASSWORD_DEFAULT),
        'role' => 'user'
    ]
];

// Authentication class
class Auth
{
    public static function login(string $email, string $password, array $users): bool
    {
        if (!isset($users[$email])) {
            return false;
        }

        $user = $users[$email];

        if (!password_verify($password, $user['password'])) {
            return false;
        }

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        // Store user data in session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();

        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];

        // Delete session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'] ?? null,
            'name' => $_SESSION['user_name'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'role' => $_SESSION['user_role'] ?? null
        ];
    }

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function hasRole(string $role): bool
    {
        return self::check() && ($_SESSION['user_role'] ?? '') === $role;
    }

    public static function isAdmin(): bool
    {
        return self::hasRole('admin');
    }
}

// Example 1: Login
echo "1. User Login:" . PHP_EOL;

$loginSuccess = Auth::login('john@example.com', 'secret123', $users);
if ($loginSuccess) {
    echo "✓ Login successful!" . PHP_EOL;
    $user = Auth::user();
    echo "Welcome, {$user['name']}!" . PHP_EOL;
    echo "Role: {$user['role']}" . PHP_EOL;
} else {
    echo "✗ Login failed" . PHP_EOL;
}
echo PHP_EOL;

// Example 2: Check authentication
echo "2. Check Authentication:" . PHP_EOL;

if (Auth::check()) {
    echo "✓ User is authenticated" . PHP_EOL;
    echo "User ID: " . Auth::id() . PHP_EOL;
} else {
    echo "✗ User is not authenticated" . PHP_EOL;
}
echo PHP_EOL;

// Example 3: Role-based access control
echo "3. Role-Based Access Control:" . PHP_EOL;

if (Auth::isAdmin()) {
    echo "✓ Admin access granted" . PHP_EOL;
    echo "User can access admin panel" . PHP_EOL;
} else {
    echo "✗ Admin access denied" . PHP_EOL;
}
echo PHP_EOL;

// Example 4: Protected page pattern
echo "4. Protected Page Pattern:" . PHP_EOL;

function requireAuth(): void
{
    if (!Auth::check()) {
        echo "✗ Access denied - login required" . PHP_EOL;
        exit;
    }
}

function requireAdmin(): void
{
    requireAuth();

    if (!Auth::isAdmin()) {
        echo "✗ Access denied - admin required" . PHP_EOL;
        exit;
    }
}

requireAuth(); // Check if user is logged in
echo "✓ Access granted to protected page" . PHP_EOL;
echo PHP_EOL;

// Example 5: Session timeout
echo "5. Session Timeout:" . PHP_EOL;

function checkSessionTimeout(int $maxLifetime = 1800): bool
{
    if (!isset($_SESSION['login_time'])) {
        return false;
    }

    $elapsed = time() - $_SESSION['login_time'];

    if ($elapsed > $maxLifetime) {
        Auth::logout();
        return true;
    }

    return false;
}

$isExpired = checkSessionTimeout();
echo "Session expired: " . ($isExpired ? 'Yes' : 'No') . PHP_EOL;
echo PHP_EOL;

// Example 6: Failed login attempts tracking
echo "6. Failed Login Attempts:" . PHP_EOL;

class LoginAttempts
{
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_TIME = 900; // 15 minutes

    public static function record(string $email): void
    {
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = [];
        }

        if (!isset($_SESSION['login_attempts'][$email])) {
            $_SESSION['login_attempts'][$email] = [
                'count' => 0,
                'locked_until' => 0
            ];
        }

        $_SESSION['login_attempts'][$email]['count']++;

        if ($_SESSION['login_attempts'][$email]['count'] >= self::MAX_ATTEMPTS) {
            $_SESSION['login_attempts'][$email]['locked_until'] = time() + self::LOCKOUT_TIME;
        }
    }

    public static function isLocked(string $email): bool
    {
        if (!isset($_SESSION['login_attempts'][$email])) {
            return false;
        }

        $lockedUntil = $_SESSION['login_attempts'][$email]['locked_until'];

        if ($lockedUntil > 0 && $lockedUntil > time()) {
            return true;
        }

        // Unlock if time has passed
        if ($lockedUntil > 0 && $lockedUntil <= time()) {
            self::reset($email);
        }

        return false;
    }

    public static function reset(string $email): void
    {
        if (isset($_SESSION['login_attempts'][$email])) {
            unset($_SESSION['login_attempts'][$email]);
        }
    }

    public static function getAttempts(string $email): int
    {
        return $_SESSION['login_attempts'][$email]['count'] ?? 0;
    }
}

// Simulate failed login
LoginAttempts::record('attacker@evil.com');
LoginAttempts::record('attacker@evil.com');
LoginAttempts::record('attacker@evil.com');

echo "Failed attempts: " . LoginAttempts::getAttempts('attacker@evil.com') . PHP_EOL;
echo "Account locked: " . (LoginAttempts::isLocked('attacker@evil.com') ? 'Yes' : 'No') . PHP_EOL;
echo PHP_EOL;

// Example 7: Logout
echo "7. User Logout:" . PHP_EOL;

Auth::logout();
echo "✓ User logged out" . PHP_EOL;
echo "Still authenticated: " . (Auth::check() ? 'Yes' : 'No') . PHP_EOL;
