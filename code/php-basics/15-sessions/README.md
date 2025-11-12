# Chapter 15: Managing State with Sessions and Cookies - Code Examples

Master session management and cookies for building stateful web applications.

## Files

1. **`session-basics.php`** - Session fundamentals, flash messages, timeout
2. **`cookie-basics.php`** - Cookie operations, security, consent tracking
3. **`auth-system.php`** - Complete authentication system with login/logout

## Quick Start

```bash
php session-basics.php
php cookie-basics.php
php auth-system.php
```

## Key Concepts

### Sessions

- **Server-side** storage
- Data stored on server, ID in cookie
- Secure for sensitive data
- Destroyed when browser closes (by default)

### Cookies

- **Client-side** storage
- Data stored in browser
- Sent with every request
- Persist across browser sessions

## Session Operations

```php
// Start session (required first)
session_start();

// Set data
$_SESSION['key'] = 'value';

// Get data
$value = $_SESSION['key'] ?? 'default';

// Check existence
if (isset($_SESSION['key'])) { }

// Remove specific key
unset($_SESSION['key']);

// Destroy all session data
session_destroy();

// Regenerate session ID (security)
session_regenerate_id(true);
```

## Cookie Operations

```php
// Set cookie (30 days, secure)
setcookie('name', 'value', [
    'expires' => time() + (86400 * 30),
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Get cookie
$value = $_COOKIE['name'] ?? 'default';

// Delete cookie (set in past)
setcookie('name', '', time() - 3600);
```

## Authentication Pattern

```php
// Login
function login($email, $password) {
    // Verify credentials
    // ...

    // Regenerate session ID
    session_regenerate_id(true);

    // Store user data
    $_SESSION['user_id'] = $userId;
    $_SESSION['logged_in'] = true;
}

// Check authentication
function isAuthenticated(): bool {
    return isset($_SESSION['logged_in'])
        && $_SESSION['logged_in'] === true;
}

// Logout
function logout() {
    $_SESSION = [];
    session_destroy();
}
```

## Flash Messages

```php
// Set flash (persists for one request)
function setFlash($key, $message) {
    $_SESSION['flash'][$key] = $message;
}

// Get flash (and remove)
function getFlash($key) {
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}
```

## Security Best Practices

### Sessions

✓ Always call `session_start()` before output
✓ Regenerate ID on privilege changes
✓ Implement session timeout
✓ Use HTTPS in production
✓ Set secure session cookie flags

### Cookies

✓ Use `secure` flag (HTTPS only)
✓ Use `httponly` flag (prevent XSS)
✓ Use `samesite` flag (prevent CSRF)
✓ Don't store passwords or sensitive data
✓ Encrypt values if needed
✓ Set appropriate expiration

## Session Configuration

```php
// In php.ini or at runtime
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', '1');
ini_set('session.gc_maxlifetime', '1800'); // 30 minutes
```

## Common Patterns

### Remember Me

```php
// Create secure token
$token = bin2hex(random_bytes(32));

// Store in database + cookie
setcookie('remember', $token, time() + (86400 * 30));

// Verify on subsequent visits
if (isset($_COOKIE['remember'])) {
    // Look up token in database
    // Auto-login user if valid
}
```

### Shopping Cart

```php
// Add to cart
$_SESSION['cart'][] = [
    'id' => $productId,
    'quantity' => $quantity
];

// Get cart total
$total = array_sum(array_column($_SESSION['cart'], 'price'));
```

### User Preferences

```php
// Set preference (cookie)
setcookie('theme', 'dark', time() + (86400 * 365));

// Apply preference
$theme = $_COOKIE['theme'] ?? 'light';
```

## Related Chapter

[Chapter 15: Managing State with Sessions and Cookies](../../chapters/15-managing-state-with-sessions-and-cookies.md)

## Further Reading

- [PHP Manual: Sessions](https://www.php.net/manual/en/book.session.php)
- [PHP Manual: Cookies](https://www.php.net/manual/en/features.cookies.php)
- [OWASP: Session Management](https://owasp.org/www-community/controls/Session_Management_Cheat_Sheet)
