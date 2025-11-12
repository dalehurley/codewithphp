<?php

declare(strict_types=1);

/**
 * Cookie Basics
 * 
 * Cookies store data on the client's browser.
 * Use for preferences, tracking, "remember me" functionality.
 */

echo "=== Cookie Basics ===" . PHP_EOL . PHP_EOL;

// Example 1: Setting a basic cookie
echo "1. Setting Cookies:" . PHP_EOL;

// Note: In CLI, cookies won't actually be set, but code shows proper syntax
$cookieSet = setcookie('username', 'john_doe', time() + 3600, '/');
echo "Cookie 'username' set (expires in 1 hour)" . PHP_EOL;
echo "Result: " . ($cookieSet ? 'Success' : 'Failed') . PHP_EOL;
echo PHP_EOL;

// Example 2: Cookie parameters explained
echo "2. Cookie Parameters:" . PHP_EOL;
echo <<<'PARAMS'
setcookie(
    name: 'cookie_name',      // Cookie name
    value: 'cookie_value',    // Cookie value
    expires_or_options: [
        'expires' => time() + 3600,      // Expiration time (1 hour)
        'path' => '/',                   // Available on entire site
        'domain' => '',                  // Domain (empty = current domain)
        'secure' => true,                // Only over HTTPS
        'httponly' => true,              // Not accessible via JavaScript
        'samesite' => 'Strict'           // CSRF protection
    ]
);
PARAMS;
echo PHP_EOL . PHP_EOL;

// Example 3: Secure cookie settings (PHP 7.3+)
echo "3. Secure Cookie Settings:" . PHP_EOL;

$secureOptions = [
    'expires' => time() + (86400 * 30), // 30 days
    'path' => '/',
    'domain' => '',
    'secure' => true,      // HTTPS only
    'httponly' => true,    // Not accessible via JS (XSS protection)
    'samesite' => 'Strict' // CSRF protection
];

setcookie('secure_token', 'abc123def456', $secureOptions);
echo "✓ Secure cookie set with all security options" . PHP_EOL;
echo PHP_EOL;

// Example 4: Reading cookies
echo "4. Reading Cookies:" . PHP_EOL;

// Simulate having cookies (in real app, these come from browser)
$_COOKIE['username'] = 'john_doe';
$_COOKIE['theme'] = 'dark';

if (isset($_COOKIE['username'])) {
    echo "Username cookie: {$_COOKIE['username']}" . PHP_EOL;
}

$theme = $_COOKIE['theme'] ?? 'light'; // Default to light if not set
echo "Theme: $theme" . PHP_EOL;
echo PHP_EOL;

// Example 5: Deleting cookies
echo "5. Deleting Cookies:" . PHP_EOL;

// To delete a cookie, set it with expiration in the past
setcookie('username', '', time() - 3600, '/');
echo "✓ Cookie 'username' deleted (expired)" . PHP_EOL;
echo PHP_EOL;

// Example 6: Cookie manager class
echo "6. Cookie Manager Class:" . PHP_EOL;

class CookieManager
{
    public static function set(
        string $name,
        string $value,
        int $expires = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = true,
        bool $httponly = true,
        string $samesite = 'Strict'
    ): bool {
        return setcookie($name, $value, [
            'expires' => $expires,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => $samesite
        ]);
    }

    public static function get(string $name, ?string $default = null): ?string
    {
        return $_COOKIE[$name] ?? $default;
    }

    public static function has(string $name): bool
    {
        return isset($_COOKIE[$name]);
    }

    public static function delete(string $name, string $path = '/'): bool
    {
        unset($_COOKIE[$name]);
        return setcookie($name, '', [
            'expires' => time() - 3600,
            'path' => $path
        ]);
    }

    public static function setForever(string $name, string $value): bool
    {
        // "Forever" = 10 years
        return self::set($name, $value, time() + (86400 * 365 * 10));
    }
}

echo "Using CookieManager:" . PHP_EOL;
CookieManager::set('preference', 'dark_mode', time() + 86400);
echo "✓ Preference cookie set" . PHP_EOL;
echo PHP_EOL;

// Example 7: Remember me functionality
echo "7. 'Remember Me' Pattern:" . PHP_EOL;

function createRememberToken(int $userId): string
{
    // In production, use a cryptographically secure token
    return bin2hex(random_bytes(32));
}

function setRememberMe(int $userId): void
{
    $token = createRememberToken($userId);

    // Store token in database associated with user
    // ... (database code would go here)

    // Set cookie with token
    CookieManager::set(
        'remember_token',
        $token,
        time() + (86400 * 30) // 30 days
    );

    echo "✓ Remember me token created and stored" . PHP_EOL;
}

function checkRememberMe(): ?int
{
    $token = CookieManager::get('remember_token');

    if ($token === null) {
        return null;
    }

    // Verify token from database
    // ... (database lookup would go here)

    // Return user ID if valid
    return 123; // Simulated user ID
}

setRememberMe(123);
$userId = checkRememberMe();
echo "Remembered user ID: " . ($userId ?? 'None') . PHP_EOL;
echo PHP_EOL;

// Example 8: Cookie consent tracking
echo "8. Cookie Consent Tracking:" . PHP_EOL;

class CookieConsent
{
    public static function hasConsent(): bool
    {
        return CookieManager::get('cookie_consent') === 'accepted';
    }

    public static function grantConsent(): void
    {
        CookieManager::setForever('cookie_consent', 'accepted');
    }

    public static function revokeConsent(): void
    {
        CookieManager::delete('cookie_consent');
    }

    public static function canUseAnalytics(): bool
    {
        return self::hasConsent() &&
            CookieManager::get('analytics_consent') === 'yes';
    }
}

CookieConsent::grantConsent();
echo "✓ Cookie consent granted" . PHP_EOL;
echo "Has consent: " . (CookieConsent::hasConsent() ? 'Yes' : 'No') . PHP_EOL;
echo PHP_EOL;

// Example 9: Security best practices
echo "9. Security Best Practices:" . PHP_EOL;
echo "✓ Always use 'secure' flag (HTTPS only)" . PHP_EOL;
echo "✓ Always use 'httponly' flag (prevent XSS)" . PHP_EOL;
echo "✓ Use 'samesite=Strict' or 'Lax' (prevent CSRF)" . PHP_EOL;
echo "✓ Don't store sensitive data in cookies" . PHP_EOL;
echo "✓ Encrypt cookie values if needed" . PHP_EOL;
echo "✓ Set appropriate expiration times" . PHP_EOL;
echo "✓ Use sessions for sensitive data" . PHP_EOL;
echo PHP_EOL;

// Example 10: Cookie vs Session comparison
echo "10. When to Use Cookies vs Sessions:" . PHP_EOL;
echo "Use COOKIES for:" . PHP_EOL;
echo "  - User preferences (theme, language)" . PHP_EOL;
echo "  - 'Remember me' functionality" . PHP_EOL;
echo "  - Tracking/analytics" . PHP_EOL;
echo "  - Shopping cart (non-sensitive)" . PHP_EOL;
echo PHP_EOL;
echo "Use SESSIONS for:" . PHP_EOL;
echo "  - Authentication state" . PHP_EOL;
echo "  - Sensitive user data" . PHP_EOL;
echo "  - Shopping cart (with prices)" . PHP_EOL;
echo "  - Form data between steps" . PHP_EOL;
echo "  - Flash messages" . PHP_EOL;
