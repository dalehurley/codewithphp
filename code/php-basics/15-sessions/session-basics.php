<?php

declare(strict_types=1);

/**
 * Session Basics
 * 
 * Sessions allow you to store user data across multiple page requests.
 * Essential for authentication, shopping carts, user preferences, etc.
 */

// Start the session (must be called before any output)
session_start();

echo "=== Session Basics ===" . PHP_EOL . PHP_EOL;

// Example 1: Setting session data
echo "1. Setting Session Data:" . PHP_EOL;

$_SESSION['username'] = 'john_doe';
$_SESSION['user_id'] = 123;
$_SESSION['is_logged_in'] = true;

echo "✓ Session data set" . PHP_EOL;
echo "Username: {$_SESSION['username']}" . PHP_EOL;
echo "User ID: {$_SESSION['user_id']}" . PHP_EOL;
echo "Logged in: " . ($_SESSION['is_logged_in'] ? 'Yes' : 'No') . PHP_EOL;
echo PHP_EOL;

// Example 2: Checking if session variable exists
echo "2. Checking Session Variables:" . PHP_EOL;

if (isset($_SESSION['username'])) {
    echo "✓ Username is set: {$_SESSION['username']}" . PHP_EOL;
}

if (!isset($_SESSION['email'])) {
    echo "✗ Email is not set" . PHP_EOL;
}
echo PHP_EOL;

// Example 3: Session with arrays
echo "3. Storing Arrays in Session:" . PHP_EOL;

$_SESSION['cart'] = [
    ['id' => 1, 'name' => 'Laptop', 'price' => 999],
    ['id' => 2, 'name' => 'Mouse', 'price' => 25]
];

echo "Cart items:" . PHP_EOL;
foreach ($_SESSION['cart'] as $item) {
    echo "  - {$item['name']}: \${$item['price']}" . PHP_EOL;
}
echo PHP_EOL;

// Example 4: Modifying session data
echo "4. Modifying Session Data:" . PHP_EOL;

$_SESSION['cart'][] = ['id' => 3, 'name' => 'Keyboard', 'price' => 79];
echo "✓ Added item to cart" . PHP_EOL;
echo "Cart now has " . count($_SESSION['cart']) . " items" . PHP_EOL;
echo PHP_EOL;

// Example 5: Removing specific session variable
echo "5. Removing Session Variables:" . PHP_EOL;

unset($_SESSION['cart']);
echo "✓ Cart cleared" . PHP_EOL;
echo "Cart exists: " . (isset($_SESSION['cart']) ? 'Yes' : 'No') . PHP_EOL;
echo PHP_EOL;

// Example 6: Session ID
echo "6. Session Information:" . PHP_EOL;

echo "Session ID: " . session_id() . PHP_EOL;
echo "Session Name: " . session_name() . PHP_EOL;
echo "Session Status: " . match (session_status()) {
    PHP_SESSION_DISABLED => 'Disabled',
    PHP_SESSION_NONE => 'Not started',
    PHP_SESSION_ACTIVE => 'Active'
} . PHP_EOL;
echo PHP_EOL;

// Example 7: Flash messages (messages that persist for one request)
echo "7. Flash Messages Pattern:" . PHP_EOL;

function setFlashMessage(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

function getFlashMessage(string $key): ?string
{
    $message = $_SESSION['flash'][$key] ?? null;
    if ($message !== null) {
        unset($_SESSION['flash'][$key]);
    }
    return $message;
}

function hasFlashMessage(string $key): bool
{
    return isset($_SESSION['flash'][$key]);
}

setFlashMessage('success', 'Operation completed successfully!');
setFlashMessage('error', 'An error occurred!');

echo "Flash messages set" . PHP_EOL;
echo "Success: " . getFlashMessage('success') . PHP_EOL;
echo "Error: " . getFlashMessage('error') . PHP_EOL;

// Try to get again (should be null)
echo "Success again: " . (getFlashMessage('success') ?? 'Not found (already retrieved)') . PHP_EOL;
echo PHP_EOL;

// Example 8: Session timeout
echo "8. Session Timeout:" . PHP_EOL;

function isSessionExpired(int $timeout = 1800): bool // 30 minutes default
{
    if (isset($_SESSION['last_activity'])) {
        $elapsed = time() - $_SESSION['last_activity'];
        if ($elapsed > $timeout) {
            return true;
        }
    }

    $_SESSION['last_activity'] = time();
    return false;
}

$_SESSION['last_activity'] = time();
echo "Session activity tracked" . PHP_EOL;
echo "Expired: " . (isSessionExpired() ? 'Yes' : 'No') . PHP_EOL;
echo PHP_EOL;

// Example 9: Session regeneration (security best practice)
echo "9. Session Regeneration:" . PHP_EOL;

$oldId = session_id();
session_regenerate_id(true); // Delete old session file
$newId = session_id();

echo "Old Session ID: $oldId" . PHP_EOL;
echo "New Session ID: $newId" . PHP_EOL;
echo "✓ Session ID regenerated (prevents session fixation)" . PHP_EOL;
echo PHP_EOL;

// Example 10: Complete session manager class
echo "10. Session Manager Class:" . PHP_EOL;

class SessionManager
{
    private static bool $started = false;

    public static function start(): void
    {
        if (!self::$started) {
            session_start();
            self::$started = true;
        }
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function clear(): void
    {
        $_SESSION = [];
    }

    public static function destroy(): void
    {
        self::clear();

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
        self::$started = false;
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }
}

SessionManager::set('test_key', 'test_value');
echo "Test key: " . SessionManager::get('test_key') . PHP_EOL;
echo "Has test_key: " . (SessionManager::has('test_key') ? 'Yes' : 'No') . PHP_EOL;

// Clean up for demo
session_destroy();
echo PHP_EOL . "✓ Session destroyed" . PHP_EOL;
