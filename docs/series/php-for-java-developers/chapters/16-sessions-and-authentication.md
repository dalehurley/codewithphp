---
title: "16: Sessions & Authentication"
description: "Session management, authentication strategies, and security best practices for PHP web applications"
series: "php-for-java-developers"
chapter: 16
order: 16
difficulty: "Intermediate"
prerequisites:
  - "/series/php-for-java-developers/chapters/15-http-and-request-response"
---

![Sessions & Authentication](/images/php-for-java-developers/chapter-16-sessions-authentication-hero-full.webp)

# Chapter 16: Sessions & Authentication

<Badge type="warning">Intermediate</Badge> <Badge type="info">90-120 min</Badge>

## Overview

Session management and authentication are fundamental to building secure web applications. If you're coming from Java, you're familiar with `HttpSession` for server-side session management and various authentication mechanisms. PHP provides similar capabilities with its native session system, plus modern token-based authentication options.

In this chapter, we'll explore PHP's session management system, compare it to Java's `HttpSession`, and dive into authentication strategies including password hashing, JWT tokens, and OAuth integration. You'll learn how to implement secure authentication systems that protect user data and prevent common security vulnerabilities.

**What You'll Learn:**

- PHP's native session system and how it compares to Java's `HttpSession`
- Secure session configuration and best practices
- Password hashing with PHP's `password_hash()` function
- JWT (JSON Web Token) authentication for stateless APIs
- OAuth 2.0 integration for third-party authentication
- CSRF protection and security best practices
- Session storage options (files, database, Redis)

## Prerequisites

::: info Time Estimate
⏱️ **90-120 minutes** to complete this chapter
:::

Before starting this chapter, you should be comfortable with:

- HTTP fundamentals (Chapter 15)
- Understanding of cookies and HTTP headers
- Basic security concepts (hashing, encryption)
- Familiarity with Java's `HttpSession` (helpful for comparison)

## What You'll Build

In this chapter, you'll create:

- A secure session management system with proper configuration
- A complete authentication system with login/logout functionality
- Password hashing and verification utilities
- A JWT token generator and validator
- CSRF protection middleware
- A session-based shopping cart example
- OAuth integration example

## Learning Objectives

By the end of this chapter, you'll be able to:

1. **Manage PHP sessions** securely with proper configuration
2. **Compare PHP sessions** to Java's `HttpSession` implementation
3. **Hash and verify passwords** using PHP's modern password functions
4. **Implement JWT authentication** for stateless API authentication
5. **Protect against CSRF attacks** using tokens
6. **Configure secure session settings** (httponly, secure, samesite)
7. **Integrate OAuth 2.0** for third-party authentication
8. **Store sessions in databases** for scalability

---

## Section 1: PHP Sessions vs Java HttpSession

### Goal

Understand how PHP's session system works and how it compares to Java's `HttpSession`.

### PHP Session Basics

PHP sessions provide server-side storage for user data across multiple requests. The concept is very similar to Java's `HttpSession`, but the implementation details differ.

::: code-group

```php [PHP Sessions]
<?php

declare(strict_types=1);

// Start session (must be called before any output)
session_start();

// Store data in session
$_SESSION['user_id'] = 123;
$_SESSION['username'] = 'john_doe';
$_SESSION['role'] = 'admin';

// Read session data
$userId = $_SESSION['user_id'] ?? null;
$username = $_SESSION['username'] ?? 'Guest';

// Check if session variable exists
if (isset($_SESSION['user_id'])) {
    echo "User is logged in";
}

// Remove specific session variable
unset($_SESSION['role']);

// Destroy entire session
session_destroy();
```

```java [Java HttpSession]
import javax.servlet.http.HttpSession;

// Get session (creates if doesn't exist)
HttpSession session = request.getSession();

// Store data in session
session.setAttribute("user_id", 123);
session.setAttribute("username", "john_doe");
session.setAttribute("role", "admin");

// Read session data
Integer userId = (Integer) session.getAttribute("user_id");
String username = (String) session.getAttribute("username");

// Check if session variable exists
if (session.getAttribute("user_id") != null) {
    System.out.println("User is logged in");
}

// Remove specific session variable
session.removeAttribute("role");

// Invalidate entire session
session.invalidate();
```

:::

### Key Differences

| Feature | PHP | Java |
|---------|-----|------|
| **Access Method** | `$_SESSION` superglobal array | `HttpSession` object from request |
| **Starting Sessions** | `session_start()` must be called explicitly | Automatically created via `request.getSession()` |
| **Type Safety** | No type checking (array values) | Requires casting when retrieving |
| **Session ID** | Stored in cookie `PHPSESSID` | Stored in cookie `JSESSIONID` |
| **Storage Location** | Files by default (configurable) | Server memory or configured store |
| **Configuration** | `php.ini` or `ini_set()` | `web.xml` or annotations |

### Session Lifecycle

**PHP:**
```php
<?php

declare(strict_types=1);

// 1. Start session (creates or resumes)
session_start();

// 2. Session ID is sent as cookie to browser
// Cookie name: PHPSESSID
// Value: Random 32-character hex string

// 3. Data stored server-side in $_SESSION array
$_SESSION['data'] = 'value';

// 4. On next request, browser sends cookie back
// PHP automatically loads session data into $_SESSION

// 5. Session persists until:
//    - Browser closes (if cookie lifetime = 0)
//    - Expires (based on gc_maxlifetime)
//    - Explicitly destroyed
```

**Java:**
```java
// 1. Get session (creates if doesn't exist)
HttpSession session = request.getSession(true);

// 2. Session ID sent as cookie JSESSIONID

// 3. Data stored via setAttribute()
session.setAttribute("data", "value");

// 4. On next request, session retrieved via request.getSession()
// Data automatically available

// 5. Session persists until:
//    - Timeout (configured in web.xml)
//    - Explicitly invalidated
```

### Expected Result

You understand that PHP sessions work similarly to Java's `HttpSession`, but PHP uses a superglobal array (`$_SESSION`) instead of an object-oriented approach.

### Why It Works

PHP's session system uses cookies to track session IDs. When you call `session_start()`, PHP:
1. Checks for an existing session ID cookie
2. If found, loads the corresponding session data from server storage
3. If not found, generates a new session ID and creates a new session
4. Makes data available through the `$_SESSION` superglobal array

The session data itself is stored server-side (typically in files), while only the session ID is sent to the client as a cookie. This is identical to how Java's `HttpSession` works.

### Troubleshooting

- **"Headers already sent" error**: `session_start()` must be called before any output (HTML, whitespace, or even blank lines). Ensure `<?php` is the first thing in your file.
- **Session data not persisting**: Make sure you call `session_start()` at the top of every page that needs session access.
- **Session ID not being sent**: Check that cookies are enabled in the browser and that no output occurs before `session_start()`.

---

## Section 2: Secure Session Configuration

### Goal

Configure PHP sessions with security best practices to prevent common attacks.

### Secure Session Setup

PHP sessions need proper configuration to be secure. Here's how to set up sessions with security in mind:

```php
<?php

declare(strict_types=1);

/**
 * Configure secure session settings
 * Call this before session_start()
 */
function configureSecureSession(): void
{
    // Use only cookies (prevent session ID in URL)
    ini_set('session.use_only_cookies', '1');
    
    // Prevent session fixation attacks
    ini_set('session.use_strict_mode', '1');
    
    // Make session cookie HTTP-only (prevent JavaScript access)
    ini_set('session.cookie_httponly', '1');
    
    // Only send cookie over HTTPS (set to 1 in production)
    ini_set('session.cookie_secure', '1'); // Use 0 for local development
    
    // Prevent CSRF attacks
    ini_set('session.cookie_samesite', 'Strict');
    
    // Set session cookie lifetime (0 = until browser closes)
    ini_set('session.cookie_lifetime', '0');
    
    // Set session garbage collection max lifetime (30 minutes)
    ini_set('session.gc_maxlifetime', '1800');
    
    // Use strong session ID hashing
    ini_set('session.hash_function', 'sha256');
}

// Configure before starting session
configureSecureSession();
session_start();

// Regenerate session ID after login (prevents session fixation)
session_regenerate_id(true);

// Store user data
$_SESSION['user_id'] = 123;
$_SESSION['login_time'] = time();
```

### Comparison: PHP vs Java Configuration

::: code-group

```php [PHP Configuration]
<?php
// Configure via ini_set() or php.ini
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_samesite', 'Strict');
session_start();
```

```java [Java Configuration]
// In web.xml
<session-config>
    <session-timeout>30</session-timeout>
    <cookie-config>
        <http-only>true</http-only>
        <secure>true</secure>
        <same-site>Strict</same-site>
    </cookie-config>
</session-config>

// Or via annotations
@SessionConfig(
    cookieHttpOnly = true,
    cookieSecure = true
)
```

:::

### Session Security Class

Let's create a reusable class for secure session management:

```php
<?php

declare(strict_types=1);

namespace App\Security;

class SecureSession
{
    private bool $started = false;

    public function __construct()
    {
        $this->configure();
    }

    /**
     * Configure secure session settings
     */
    private function configure(): void
    {
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', $this->isHttps() ? '1' : '0');
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.cookie_lifetime', '0');
        ini_set('session.gc_maxlifetime', '1800');
    }

    /**
     * Start secure session
     */
    public function start(): void
    {
        if (!$this->started && session_status() === PHP_SESSION_NONE) {
            session_start();
            $this->started = true;
        }
    }

    /**
     * Regenerate session ID (call after login)
     */
    public function regenerateId(): void
    {
        if ($this->started) {
            session_regenerate_id(true);
        }
    }

    /**
     * Get session value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->ensureStarted();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set session value
     */
    public function set(string $key, mixed $value): void
    {
        $this->ensureStarted();
        $_SESSION[$key] = $value;
    }

    /**
     * Check if session key exists
     */
    public function has(string $key): bool
    {
        $this->ensureStarted();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session value
     */
    public function remove(string $key): void
    {
        $this->ensureStarted();
        unset($_SESSION[$key]);
    }

    /**
     * Destroy session
     */
    public function destroy(): void
    {
        if ($this->started) {
            $_SESSION = [];
            if (isset($_COOKIE[session_name()])) {
                setcookie(
                    session_name(),
                    '',
                    time() - 3600,
                    '/',
                    '',
                    true,
                    true
                );
            }
            session_destroy();
            $this->started = false;
        }
    }

    /**
     * Get session ID
     */
    public function getId(): string
    {
        $this->ensureStarted();
        return session_id();
    }

    private function ensureStarted(): void
    {
        if (!$this->started) {
            $this->start();
        }
    }

    private function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || $_SERVER['SERVER_PORT'] == 443;
    }
}

// Usage
$session = new SecureSession();
$session->start();
$session->set('user_id', 123);
$userId = $session->get('user_id');
```

### Expected Result

You have a secure session configuration that prevents common attacks like session fixation, XSS (via httponly cookies), and CSRF (via SameSite cookies).

### Why It Works

- **`use_only_cookies`**: Prevents session IDs from appearing in URLs, which could be leaked via referrer headers or browser history
- **`use_strict_mode`**: Only accepts session IDs that were created by the server, preventing session fixation attacks
- **`cookie_httponly`**: Prevents JavaScript from accessing the session cookie, protecting against XSS attacks
- **`cookie_secure`**: Only sends cookies over HTTPS, preventing man-in-the-middle attacks
- **`cookie_samesite`**: Prevents cookies from being sent in cross-site requests, protecting against CSRF attacks
- **`session_regenerate_id(true)`**: Creates a new session ID after login, invalidating any session ID an attacker might have obtained

### Troubleshooting

- **Session not working in development**: Set `cookie_secure` to `0` if you're not using HTTPS locally
- **Session expires too quickly**: Adjust `gc_maxlifetime` to increase session lifetime
- **Session persists after logout**: Make sure you call `session_destroy()` and clear the session cookie

---

## Section 3: Password Hashing and Verification

### Goal

Learn how to securely hash and verify passwords in PHP, comparing to Java's BCrypt approach.

### PHP Password Hashing

PHP provides built-in functions for secure password hashing using modern algorithms like Argon2 and BCrypt. This is much simpler than Java's approach.

::: code-group

```php [PHP Password Hashing]
<?php

declare(strict_types=1);

// Hash a password (uses Argon2ID by default in PHP 8.4)
$password = 'mySecurePassword123';
$hash = password_hash($password, PASSWORD_ARGON2ID);

// The hash includes algorithm, cost, salt, and hash
// Example: $argon2id$v=19$m=65536,t=4,p=3$...

// Verify a password
if (password_verify($password, $hash)) {
    echo "Password is correct";
} else {
    echo "Password is incorrect";
}

// Check if hash needs rehashing (if algorithm/cost changed)
if (password_needs_rehash($hash, PASSWORD_ARGON2ID, ['memory_cost' => 65536])) {
    $newHash = password_hash($password, PASSWORD_ARGON2ID);
    // Update hash in database
}
```

```java [Java BCrypt]
import org.springframework.security.crypto.bcrypt.BCryptPasswordEncoder;

BCryptPasswordEncoder encoder = new BCryptPasswordEncoder();

// Hash a password
String password = "mySecurePassword123";
String hash = encoder.encode(password);

// Verify a password
if (encoder.matches(password, hash)) {
    System.out.println("Password is correct");
} else {
    System.out.println("Password is incorrect");
}
```

:::

### Password Hashing Utility Class

```php
<?php

declare(strict_types=1);

namespace App\Security;

class PasswordHasher
{
    /**
     * Hash a password using Argon2ID
     */
    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    /**
     * Verify a password against a hash
     */
    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Check if hash needs to be rehashed
     */
    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_ARGON2ID);
    }

    /**
     * Validate password strength
     */
    public static function validateStrength(string $password): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }

        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }

        return $errors;
    }
}

// Usage
$password = 'MySecure123!';
$errors = PasswordHasher::validateStrength($password);

if (empty($errors)) {
    $hash = PasswordHasher::hash($password);
    // Store $hash in database
} else {
    foreach ($errors as $error) {
        echo $error . "\n";
    }
}
```

### Complete Authentication Example

```php
<?php

declare(strict_types=1);

namespace App\Auth;

use App\Security\{PasswordHasher, SecureSession};
use PDO;

class Authenticator
{
    public function __construct(
        private PDO $pdo,
        private SecureSession $session
    ) {}

    /**
     * Authenticate user with email and password
     */
    public function login(string $email, string $password): bool
    {
        // Find user by email
        $stmt = $this->pdo->prepare(
            'SELECT id, email, password_hash FROM users WHERE email = ?'
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        // Verify password
        if (!PasswordHasher::verify($password, $user['password_hash'])) {
            return false;
        }

        // Check if hash needs rehashing (algorithm/cost updated)
        if (PasswordHasher::needsRehash($user['password_hash'])) {
            $newHash = PasswordHasher::hash($password);
            $updateStmt = $this->pdo->prepare(
                'UPDATE users SET password_hash = ? WHERE id = ?'
            );
            $updateStmt->execute([$newHash, $user['id']]);
        }

        // Start session and store user data
        $this->session->start();
        $this->session->regenerateId(); // Prevent session fixation
        $this->session->set('user_id', $user['id']);
        $this->session->set('email', $user['email']);
        $this->session->set('login_time', time());

        return true;
    }

    /**
     * Register a new user
     */
    public function register(string $email, string $password): bool
    {
        // Validate password strength
        $errors = PasswordHasher::validateStrength($password);
        if (!empty($errors)) {
            throw new \InvalidArgumentException(
                'Password does not meet requirements: ' . implode(', ', $errors)
            );
        }

        // Check if user already exists
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new \InvalidArgumentException('User already exists');
        }

        // Hash password and insert user
        $hash = PasswordHasher::hash($password);
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (email, password_hash) VALUES (?, ?)'
        );

        return $stmt->execute([$email, $hash]);
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        $this->session->start();
        return $this->session->has('user_id');
    }

    /**
     * Get current user ID
     */
    public function getUserId(): ?int
    {
        $this->session->start();
        return $this->session->get('user_id');
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        $this->session->destroy();
    }
}

// Usage
$pdo = new PDO('sqlite:database.db');
$session = new SecureSession();
$auth = new Authenticator($pdo, $session);

// Register
try {
    $auth->register('user@example.com', 'SecurePass123!');
    echo "User registered\n";
} catch (\InvalidArgumentException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Login
if ($auth->login('user@example.com', 'SecurePass123!')) {
    echo "Logged in successfully\n";
    echo "User ID: " . $auth->getUserId() . "\n";
} else {
    echo "Invalid credentials\n";
}

// Check authentication
if ($auth->isAuthenticated()) {
    echo "User is authenticated\n";
}

// Logout
$auth->logout();
```

### Expected Result

You can securely hash passwords, verify them, and implement a complete authentication system.

### Why It Works

- **`password_hash()`**: Uses a cryptographically secure algorithm (Argon2ID by default) that automatically generates a random salt and includes it in the hash
- **`password_verify()`**: Safely compares the password to the hash using constant-time comparison to prevent timing attacks
- **`password_needs_rehash()`**: Allows you to upgrade password hashes when you change algorithms or cost parameters without requiring users to reset their passwords
- **Argon2ID**: A modern password hashing algorithm that's resistant to GPU-based attacks and side-channel attacks

### Troubleshooting

- **"password_hash(): Unknown password hashing algorithm"**: Make sure you're using PHP 7.2+ for Argon2ID, or use `PASSWORD_BCRYPT` for older PHP versions
- **Passwords not matching**: Ensure you're using the same algorithm for hashing and verification
- **Performance issues**: Adjust Argon2ID memory_cost and time_cost parameters if hashing is too slow

---

## Section 4: JWT Authentication

### Goal

Implement stateless JWT (JSON Web Token) authentication for APIs, comparing to Java's JWT libraries.

### JWT Basics

JWT tokens are self-contained and stateless, making them ideal for API authentication. They consist of three parts: header, payload, and signature.

```php
<?php

declare(strict_types=1);

// JWT structure: header.payload.signature
// Example: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoxMjN9.signature

// Header: {"alg": "HS256", "typ": "JWT"}
// Payload: {"user_id": 123, "exp": 1234567890}
// Signature: HMAC-SHA256(base64UrlEncode(header) + "." + base64UrlEncode(payload), secret)
```

### Simple JWT Implementation

```php
<?php

declare(strict_types=1);

namespace App\Security;

class JWT
{
    /**
     * Encode data into a JWT token
     */
    public static function encode(array $payload, string $secret, int $expiration = 3600): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        // Add expiration time
        $payload['exp'] = time() + $expiration;
        $payload['iat'] = time(); // Issued at

        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac(
            'sha256',
            $headerEncoded . '.' . $payloadEncoded,
            $secret,
            true
        );
        $signatureEncoded = self::base64UrlEncode($signature);

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    /**
     * Decode and verify a JWT token
     */
    public static function decode(string $token, string $secret): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        // Verify signature
        $signature = self::base64UrlDecode($signatureEncoded);
        $expectedSignature = hash_hmac(
            'sha256',
            $headerEncoded . '.' . $payloadEncoded,
            $secret,
            true
        );

        if (!hash_equals($signature, $expectedSignature)) {
            return null; // Invalid signature
        }

        // Decode payload
        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);

        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null; // Token expired
        }

        return $payload;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}

// Usage
$secret = 'your-secret-key-change-this-in-production';

// Create token
$token = JWT::encode(['user_id' => 123, 'email' => 'user@example.com'], $secret, 3600);
echo "Token: $token\n";

// Verify token
$payload = JWT::decode($token, $secret);
if ($payload) {
    echo "User ID: " . $payload['user_id'] . "\n";
    echo "Email: " . $payload['email'] . "\n";
} else {
    echo "Invalid or expired token\n";
}
```

### JWT Authentication Middleware

```php
<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Security\JWT;

class JWTAuthMiddleware
{
    public function __construct(private string $secret) {}

    /**
     * Authenticate request using JWT token
     */
    public function authenticate(string $token): ?array
    {
        $payload = JWT::decode($token, $this->secret);
        
        if (!$payload) {
            return null;
        }

        return $payload;
    }

    /**
     * Extract token from Authorization header
     */
    public function extractToken(array $headers): ?string
    {
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        
        if (!$authHeader) {
            return null;
        }

        // Format: "Bearer <token>"
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Handle authentication for API request
     */
    public function handle(array $server, callable $next): mixed
    {
        // Extract token from header
        $authHeader = $server['HTTP_AUTHORIZATION'] ?? null;
        if (!$authHeader) {
            http_response_code(401);
            echo json_encode(['error' => 'Missing authorization header']);
            exit;
        }

        $token = $this->extractToken(['Authorization' => $authHeader]);
        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid authorization format']);
            exit;
        }

        // Verify token
        $payload = $this->authenticate($token);
        if (!$payload) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or expired token']);
            exit;
        }

        // Add user data to request context
        $_SERVER['AUTH_USER'] = $payload;

        // Continue to next handler
        return $next();
    }
}

// Usage in API endpoint
$middleware = new JWTAuthMiddleware('your-secret-key');

$middleware->handle($_SERVER, function() {
    $user = $_SERVER['AUTH_USER'];
    echo json_encode([
        'message' => 'Authenticated request',
        'user_id' => $user['user_id']
    ]);
});
```

### Comparison: PHP vs Java JWT

::: code-group

```php [PHP JWT]
<?php
use App\Security\JWT;

$token = JWT::encode(['user_id' => 123], $secret);
$payload = JWT::decode($token, $secret);
```

```java [Java JWT with jjwt]
import io.jsonwebtoken.Jwts;
import io.jsonwebtoken.SignatureAlgorithm;

// Create token
String token = Jwts.builder()
    .setSubject("123")
    .setExpiration(new Date(System.currentTimeMillis() + 3600000))
    .signWith(SignatureAlgorithm.HS256, secret)
    .compact();

// Verify token
Claims claims = Jwts.parser()
    .setSigningKey(secret)
    .parseClaimsJws(token)
    .getBody();
```

:::

### Expected Result

You can create and verify JWT tokens for stateless API authentication.

### Why It Works

- **Stateless**: JWT tokens contain all necessary information, so the server doesn't need to store session data
- **Self-contained**: The payload can include user information, eliminating the need for database lookups on each request
- **Signed**: The signature ensures the token hasn't been tampered with
- **Expiration**: Built-in expiration prevents tokens from being valid indefinitely

### JWT Refresh Tokens

For better security, implement refresh tokens that allow users to obtain new access tokens without re-authenticating:

```php
<?php

declare(strict_types=1);

namespace App\Security;

class JWTRefreshToken
{
    /**
     * Generate a refresh token (longer-lived, stored server-side)
     */
    public static function generate(string $userId, string $secret): string
    {
        // Refresh tokens are longer-lived (7 days) and stored in database
        return JWT::encode(
            ['user_id' => $userId, 'type' => 'refresh'],
            $secret,
            604800 // 7 days
        );
    }

    /**
     * Refresh an access token using a refresh token
     */
    public static function refresh(string $refreshToken, string $secret): ?array
    {
        $payload = JWT::decode($refreshToken, $secret);
        
        if (!$payload || ($payload['type'] ?? null) !== 'refresh') {
            return null;
        }

        // Generate new access token
        $newAccessToken = JWT::encode(
            ['user_id' => $payload['user_id']],
            $secret,
            3600 // 1 hour
        );

        return [
            'access_token' => $newAccessToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600
        ];
    }
}

// Usage in login endpoint
$accessToken = JWT::encode(['user_id' => $user['id']], $secret, 3600);
$refreshToken = JWTRefreshToken::generate($user['id'], $secret);

// Store refresh token in database (for revocation)
// Return both tokens to client

// Usage in refresh endpoint
$refreshToken = $_POST['refresh_token'] ?? null;
$result = JWTRefreshToken::refresh($refreshToken, $secret);

if ($result) {
    echo json_encode($result);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid refresh token']);
}
```

### Troubleshooting

- **"Invalid token" errors**: Ensure the secret key matches between encoding and decoding
- **Token expired immediately**: Check system clock synchronization
- **Signature verification fails**: Make sure you're using the same secret and algorithm
- **Refresh token not working**: Verify the token type is 'refresh' and hasn't expired

---

## Section 5: CSRF Protection

### Goal

Implement CSRF (Cross-Site Request Forgery) protection using tokens.

### CSRF Token Implementation

```php
<?php

declare(strict_types=1);

namespace App\Security;

class CSRFProtection
{
    public function __construct(private SecureSession $session) {}

    /**
     * Generate CSRF token
     */
    public function generateToken(): string
    {
        $this->session->start();
        $token = bin2hex(random_bytes(32));
        $this->session->set('csrf_token', $token);
        return $token;
    }

    /**
     * Get current CSRF token
     */
    public function getToken(): ?string
    {
        $this->session->start();
        return $this->session->get('csrf_token');
    }

    /**
     * Validate CSRF token
     */
    public function validateToken(string $submittedToken): bool
    {
        $this->session->start();
        $storedToken = $this->session->get('csrf_token');

        if (!$storedToken) {
            return false;
        }

        // Use hash_equals for constant-time comparison
        return hash_equals($storedToken, $submittedToken);
    }

    /**
     * Generate HTML hidden input field
     */
    public function field(): string
    {
        $token = $this->getToken() ?? $this->generateToken();
        return '<input type="hidden" name="csrf_token" value="' 
            . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') 
            . '">';
    }
}

// Usage in form
$csrf = new CSRFProtection(new SecureSession());
?>
<form method="POST" action="process.php">
    <?php echo $csrf->field(); ?>
    <input type="text" name="email">
    <button type="submit">Submit</button>
</form>

<?php
// In process.php
$csrf = new CSRFProtection(new SecureSession());
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!$csrf->validateToken($token)) {
        http_response_code(403);
        die('CSRF token validation failed');
    }
    // Process form...
}
```

### Expected Result

Forms are protected against CSRF attacks using secure tokens.

### Why It Works

CSRF tokens ensure that form submissions come from your own website, not from a malicious site. The token is:
1. Generated server-side and stored in the session
2. Included in the form as a hidden field
3. Validated on submission to ensure it matches the session token

---

## Section 6: OAuth 2.0 Integration

### Goal

Integrate OAuth 2.0 for third-party authentication (Google, GitHub, etc.).

### OAuth 2.0 Flow Implementation

```php
<?php

declare(strict_types=1);

namespace App\Auth;

class OAuth2Client
{
    public function __construct(
        private string $clientId,
        private string $clientSecret,
        private string $redirectUri,
        private string $authorizationUrl,
        private string $tokenUrl,
        private string $userInfoUrl
    ) {}

    /**
     * Get authorization URL
     */
    public function getAuthorizationUrl(string $state): string
    {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state
        ];

        return $this->authorizationUrl . '?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for access token
     */
    public function getAccessToken(string $code): array
    {
        $data = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret
        ];

        $ch = curl_init($this->tokenUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \RuntimeException('Failed to get access token');
        }

        return json_decode($response, true);
    }

    /**
     * Get user information using access token
     */
    public function getUserInfo(string $accessToken): array
    {
        $ch = curl_init($this->userInfoUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \RuntimeException('Failed to get user info');
        }

        return json_decode($response, true);
    }
}

// Google OAuth example
$googleClient = new OAuth2Client(
    clientId: 'your-google-client-id',
    clientSecret: 'your-google-client-secret',
    redirectUri: 'https://yourapp.com/auth/google/callback',
    authorizationUrl: 'https://accounts.google.com/o/oauth2/v2/auth',
    tokenUrl: 'https://oauth2.googleapis.com/token',
    userInfoUrl: 'https://www.googleapis.com/oauth2/v2/userinfo'
);

// In login route
session_start();
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;
$authUrl = $googleClient->getAuthorizationUrl($state);
header('Location: ' . $authUrl);
exit;

// In callback route
session_start();
$state = $_GET['state'] ?? '';
$code = $_GET['code'] ?? '';

if (!hash_equals($_SESSION['oauth_state'], $state)) {
    die('Invalid state parameter');
}

$tokenData = $googleClient->getAccessToken($code);
$userInfo = $googleClient->getUserInfo($tokenData['access_token']);

// $userInfo contains: id, email, name, picture, etc.
// Create or update user account, then log them in
```

### Expected Result

You can authenticate users via OAuth 2.0 providers like Google, GitHub, etc.

### Why It Works

OAuth 2.0 allows users to authenticate using their existing accounts on third-party services. The flow:
1. Redirects user to provider's authorization page
2. User grants permission
3. Provider redirects back with authorization code
4. Exchange code for access token
5. Use access token to get user information

### Troubleshooting

- **"Invalid state parameter" error**: Always validate the `state` parameter to prevent CSRF attacks. Store it in the session before redirecting and compare it when the callback is received.
- **"Failed to get access token"**: Check that the authorization code hasn't expired (usually valid for 10 minutes) and that the redirect URI matches exactly what was registered with the provider.
- **CURL errors**: Ensure the `curl` extension is enabled in PHP. Check network connectivity and firewall settings.
- **Token exchange fails**: Verify client ID and secret are correct, and that the redirect URI matches exactly (including trailing slashes and protocol).

---

## Section 7: Database Session Storage

### Goal

Store session data in a database instead of files for better scalability and security.

### Why Use Database Sessions?

While file-based sessions work fine for small applications, database sessions offer several advantages:

- **Scalability**: Works across multiple servers in a load-balanced environment
- **Security**: Can encrypt session data in the database
- **Monitoring**: Easy to query and monitor active sessions
- **Performance**: Can use Redis or Memcached for faster access
- **Cleanup**: Automatic garbage collection via database queries

### Database Session Handler Implementation

```php
<?php

declare(strict_types=1);

namespace App\Session;

use PDO;
use SessionHandlerInterface;

class DatabaseSessionHandler implements SessionHandlerInterface
{
    public function __construct(private PDO $pdo) {}

    /**
     * Initialize session (called when session_start() is called)
     */
    public function open(string $path, string $name): bool
    {
        return true; // Database connection already established
    }

    /**
     * Close session (called when script ends)
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Read session data
     */
    public function read(string $id): string|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT data FROM sessions WHERE id = ? AND last_activity > ?'
        );
        $stmt->execute([$id, time() - 1800]); // 30 minutes timeout
        
        $data = $stmt->fetchColumn();
        return $data !== false ? $data : '';
    }

    /**
     * Write session data
     */
    public function write(string $id, string $data): bool
    {
        $stmt = $this->pdo->prepare(
            'REPLACE INTO sessions (id, data, last_activity) VALUES (?, ?, ?)'
        );
        return $stmt->execute([$id, $data, time()]);
    }

    /**
     * Destroy session
     */
    public function destroy(string $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Garbage collection (clean up old sessions)
     */
    public function gc(int $max_lifetime): int|false
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM sessions WHERE last_activity < ?'
        );
        $stmt->execute([time() - $max_lifetime]);
        return $stmt->rowCount() ?: 0;
    }
}

// Database schema
/*
CREATE TABLE sessions (
    id VARCHAR(128) PRIMARY KEY,
    data TEXT NOT NULL,
    last_activity INT NOT NULL,
    INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
*/

// Usage
$pdo = new PDO('mysql:host=localhost;dbname=myapp', 'user', 'password');
$handler = new DatabaseSessionHandler($pdo);
session_set_save_handler($handler, true);

// Configure secure session settings before starting
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_samesite', 'Strict');

session_start();
```

### Comparison: PHP vs Java Session Storage

::: code-group

```php [PHP Database Sessions]
<?php
$handler = new DatabaseSessionHandler($pdo);
session_set_save_handler($handler, true);
session_start();
```

```java [Java Database Sessions]
// In web.xml
<session-config>
    <session-timeout>30</session-timeout>
    <persistent-store>
        <store-class>
            org.apache.catalina.session.JDBCStore
        </store-class>
        <driver-name>com.mysql.jdbc.Driver</driver-name>
        <connection-url>jdbc:mysql://localhost/mydb</connection-url>
    </persistent-store>
</session-config>
```

:::

### Expected Result

Sessions are stored in the database, allowing your application to scale across multiple servers and providing better monitoring capabilities.

### Why It Works

PHP's `session_set_save_handler()` function allows you to replace the default file-based session handler with a custom implementation. The `SessionHandlerInterface` defines the methods PHP calls when it needs to read, write, or delete session data. By implementing these methods to interact with a database, session data is stored persistently and can be shared across multiple servers.

### Troubleshooting

- **Sessions not persisting**: Ensure the database table exists and the PDO connection has proper permissions (SELECT, INSERT, UPDATE, DELETE).
- **Performance issues**: Add indexes on the `id` and `last_activity` columns. Consider using Redis or Memcached for even better performance.
- **Session data corruption**: Ensure the `data` column is large enough (TEXT or BLOB) to store serialized session data.
- **Garbage collection not running**: PHP's garbage collection runs probabilistically. You may need to run a cron job to clean up old sessions manually.

---

## Section 8: Flash Messages and Session Utilities

### Goal

Implement flash messages for one-time notifications and other common session utilities.

### Flash Messages

Flash messages are one-time messages stored in the session that are displayed once and then automatically removed. This is a common pattern for showing success/error messages after redirects.

```php
<?php

declare(strict_types=1);

namespace App\Session;

use App\Security\SecureSession;

class FlashMessages
{
    public function __construct(private SecureSession $session) {}

    /**
     * Set a flash message
     */
    public function set(string $type, string $message): void
    {
        $this->session->start();
        $messages = $this->session->get('flash_messages', []);
        $messages[] = ['type' => $type, 'message' => $message];
        $this->session->set('flash_messages', $messages);
    }

    /**
     * Get and remove all flash messages
     */
    public function get(): array
    {
        $this->session->start();
        $messages = $this->session->get('flash_messages', []);
        $this->session->remove('flash_messages');
        return $messages;
    }

    /**
     * Check if there are flash messages
     */
    public function has(): bool
    {
        $this->session->start();
        return $this->session->has('flash_messages');
    }

    /**
     * Render flash messages as HTML
     */
    public function render(): string
    {
        $messages = $this->get();
        if (empty($messages)) {
            return '';
        }

        $html = '<div class="flash-messages">';
        foreach ($messages as $msg) {
            $html .= sprintf(
                '<div class="flash-message flash-%s">%s</div>',
                htmlspecialchars($msg['type'], ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8')
            );
        }
        $html .= '</div>';

        return $html;
    }
}

// Usage
$session = new SecureSession();
$flash = new FlashMessages($session);

// Set flash message before redirect
$flash->set('success', 'User created successfully!');
header('Location: /users');
exit;

// In the target page
$flash = new FlashMessages($session);
if ($flash->has()) {
    echo $flash->render();
}
```

### Session Timeout Handling

Implement idle timeout to automatically log out inactive users:

```php
<?php

declare(strict_types=1);

namespace App\Session;

use App\Security\SecureSession;

class SessionTimeout
{
    public function __construct(
        private SecureSession $session,
        private int $timeoutSeconds = 1800 // 30 minutes
    ) {}

    /**
     * Check if session has timed out
     */
    public function isExpired(): bool
    {
        $this->session->start();
        $lastActivity = $this->session->get('last_activity');

        if ($lastActivity === null) {
            return false; // New session
        }

        return (time() - $lastActivity) > $this->timeoutSeconds;
    }

    /**
     * Update last activity timestamp
     */
    public function updateActivity(): void
    {
        $this->session->start();
        $this->session->set('last_activity', time());
    }

    /**
     * Check and handle timeout
     */
    public function check(): bool
    {
        if ($this->isExpired()) {
            $this->session->destroy();
            return false; // Session expired
        }

        $this->updateActivity();
        return true; // Session valid
    }
}

// Usage in protected pages
$session = new SecureSession();
$timeout = new SessionTimeout($session);

if (!$timeout->check()) {
    header('Location: /login?expired=1');
    exit;
}
```

### Comparison: PHP vs Java Flash Messages

::: code-group

```php [PHP Flash Messages]
<?php
$flash = new FlashMessages($session);
$flash->set('success', 'Operation completed');
// After redirect, message is displayed once and removed
```

```java [Java Flash Messages - Spring]
// In controller
redirectAttributes.addFlashAttribute("message", "Operation completed");
// In view (Thymeleaf)
<div th:if="${message}">${message}</div>
```

:::

### Expected Result

You can display one-time messages after redirects and automatically handle session timeouts for inactive users.

### Why It Works

Flash messages are stored in the session and automatically removed after being read once, making them perfect for post-redirect-get patterns. Session timeout tracking uses timestamps to detect idle sessions and automatically log users out for security.

### Troubleshooting

- **Flash messages not appearing**: Make sure you're reading them on the page after the redirect, not before.
- **Messages appearing multiple times**: Ensure you're calling `get()` which removes them, not just checking with `has()`.
- **Session timeout too aggressive**: Adjust the `timeoutSeconds` value based on your application's needs.

---

## Exercises

### Exercise 1: Session-Based Shopping Cart (~15 min)

**Goal**: Build a shopping cart using PHP sessions

Create a shopping cart system that stores items in the session:

```php
<?php
// Requirements:
// 1. Add items to cart (product_id, quantity)
// 2. Update item quantities
// 3. Remove items from cart
// 4. Display cart contents
// 5. Calculate total price
```

**Validation**: Test adding, updating, and removing items. Verify cart persists across page loads.

### Exercise 2: JWT API Authentication (~20 min)

**Goal**: Create a complete JWT authentication system for an API

Build an API with:
- POST `/api/auth/login` - Returns JWT token
- GET `/api/users/me` - Protected endpoint requiring JWT
- POST `/api/auth/refresh` - Refresh expired tokens

**Validation**: Test authentication flow with curl or Postman.

---

## Wrap-up

In this chapter, you've learned:

- ✅ How PHP sessions compare to Java's `HttpSession`
- ✅ How to configure secure sessions with proper security settings
- ✅ How to hash and verify passwords using PHP's built-in functions
- ✅ How to implement JWT authentication for stateless APIs
- ✅ How to implement JWT refresh tokens for secure token renewal
- ✅ How to protect against CSRF attacks
- ✅ How to integrate OAuth 2.0 for third-party authentication
- ✅ How to store sessions in databases for scalability
- ✅ How to implement flash messages for one-time notifications
- ✅ How to handle session timeouts and idle sessions

**Key Takeaways:**

- PHP sessions use the `$_SESSION` superglobal array, similar to Java's `HttpSession`
- Always configure sessions with security settings (httponly, secure, samesite)
- Use `password_hash()` and `password_verify()` for password security
- JWT tokens enable stateless authentication for APIs
- Refresh tokens provide secure token renewal without re-authentication
- CSRF protection is essential for form submissions
- OAuth 2.0 allows users to authenticate with third-party providers
- Database sessions enable scalability across multiple servers
- Flash messages provide clean post-redirect-get patterns
- Session timeouts protect against idle session attacks

**Next Steps:**

- Chapter 17 covers forms and validation
- Practice implementing authentication in your own projects
- Explore advanced topics like token rotation and multi-factor authentication

---

<ChapterCheckbox 
  seriesId="php-for-java-developers"
  chapterId="16"
  label="Completed sessions and authentication with secure practices!"
/>

---

## Further Reading

- [PHP Sessions Documentation](https://www.php.net/manual/en/book.session.php) — Official PHP session documentation
- [OWASP Session Management](https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html) — Security best practices
- [JWT.io](https://jwt.io/) — JWT specification and debugger
- [OAuth 2.0 RFC](https://oauth.net/2/) — OAuth 2.0 specification
- [PHP Password Hashing](https://www.php.net/manual/en/password.hashing.php) — Password hashing functions

---

<div style="display: flex; justify-content: space-between; margin-top: 2rem;">
  <div>
    <strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/15-http-and-request-response">← Chapter 15</a>
  </div>
  <div><strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/17-forms-and-validation">Chapter 17 →</a></div>
</div>
