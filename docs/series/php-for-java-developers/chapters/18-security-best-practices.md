---
title: "18: Security Best Practices"
description: "Comprehensive security practices for PHP applications based on OWASP Top 10"
series: "php-for-java-developers"
chapter: 18
order: 18
difficulty: "Advanced"
prerequisites:
  - "/series/php-for-java-developers/chapters/17-forms-and-validation"
  - "/series/php-for-java-developers/chapters/09-working-with-databases"
  - "/series/php-for-java-developers/chapters/16-sessions-and-authentication"
---

![Security Best Practices](/images/php-for-java-developers/chapter-18-security-best-practices-hero-full.webp)

# Chapter 18: Security Best Practices

<Badge type="danger">Advanced</Badge> <Badge type="info">120-150 min</Badge>

## Overview

Security is not optional—it's fundamental to building production-ready PHP applications. As a Java developer transitioning to PHP, you're already familiar with security concepts like input validation, SQL injection prevention, and authentication. This chapter translates those concepts into PHP-specific implementations, covering the OWASP Top 10 vulnerabilities and providing practical, battle-tested solutions.

Security vulnerabilities can compromise user data, damage your reputation, and even end careers. Unlike Java's Spring Security framework that provides many security features out of the box, PHP requires you to implement security measures explicitly. This chapter teaches you how to build secure PHP applications from the ground up, covering everything from SQL injection prevention to secure file uploads.

**What You'll Learn:**

- OWASP Top 10 vulnerabilities and PHP-specific mitigations
- SQL injection prevention with prepared statements
- Cross-Site Scripting (XSS) protection strategies
- Cross-Site Request Forgery (CSRF) token implementation
- Secure password hashing and authentication
- Security headers configuration
- File upload security best practices
- Input validation and sanitization techniques
- Secure session management
- Error handling without information disclosure

## Prerequisites

::: info Time Estimate
⏱️ **120-150 minutes** to complete this chapter
:::

Before starting this chapter, you should be comfortable with:

- Database operations with PDO (Chapter 9)
- Form handling and validation (Chapter 17)
- Session management and authentication (Chapter 16)
- HTTP fundamentals (headers, methods, status codes)
- Basic understanding of web security concepts

**Verify your setup:**

```bash
# Check PHP version (8.4+ recommended)
php --version

# Verify PDO extension is available
php -m | grep pdo
```

## Learning Objectives

By the end of this chapter, you will be able to:

1. **Prevent SQL injection** using prepared statements and parameterized queries
2. **Protect against XSS attacks** with proper output escaping and Content Security Policy
3. **Implement CSRF protection** with secure token generation and validation
4. **Secure authentication** with strong password hashing and rate limiting
5. **Implement authorization** with RBAC, permissions, and resource access control
6. **Prevent command injection** using safe command execution methods
7. **Prevent XXE attacks** by disabling external entity processing
8. **Avoid deserialization vulnerabilities** by using JSON or whitelisting classes
9. **Prevent mass assignment** by whitelisting allowed fields
10. **Prevent IDOR vulnerabilities** by validating resource access
11. **Manage configuration securely** using environment variables and secret storage
12. **Scan dependencies** for vulnerabilities using Composer audit
13. **Encrypt sensitive data** at rest using sodium encryption
14. **Log security events** and detect intrusion patterns
15. **Configure security headers** to protect against common attack vectors
16. **Handle file uploads securely** with content validation and safe storage
17. **Validate and sanitize input** using PHP's filter functions
18. **Manage sessions securely** with proper configuration and regeneration
19. **Handle errors safely** without exposing sensitive information
20. **Apply OWASP Top 10 mitigations** to your PHP applications

## What You'll Build

By the end of this chapter, you will have created:

- A secure authentication system with password hashing and rate limiting
- An authorization system with RBAC and permission-based access control
- A CSRF protection middleware class
- A security headers configuration class
- A secure file upload handler with content validation
- Input validation and sanitization utilities
- Safe command execution utilities
- Secure XML parsing with XXE prevention
- Mass assignment protection utilities
- IDOR prevention with resource access validation
- Secure configuration management with environment variables
- Dependency vulnerability scanning tools
- Encryption utilities for data at rest
- Security logging and intrusion detection system
- A comprehensive security checklist for your applications

---

## Section 1: Understanding OWASP Top 10

The OWASP Top 10 is a standard awareness document representing the most critical security risks to web applications. Understanding these vulnerabilities helps you build more secure applications.

### OWASP Top 10 Overview

**1. Broken Access Control**
- Unauthorized access to resources
- PHP mitigation: Implement proper authorization checks

**2. Cryptographic Failures**
- Weak encryption or exposed sensitive data
- PHP mitigation: Use `password_hash()` with strong algorithms

**3. Injection**
- SQL, NoSQL, Command injection
- PHP mitigation: Prepared statements, parameterized queries

**4. Insecure Design**
- Flawed security architecture
- PHP mitigation: Security-first design principles

**5. Security Misconfiguration**
- Default configurations, exposed files
- PHP mitigation: Secure defaults, proper headers

**6. Vulnerable Components**
- Outdated dependencies
- PHP mitigation: Keep Composer packages updated

**7. Authentication Failures**
- Weak authentication mechanisms
- PHP mitigation: Strong password hashing, rate limiting

**8. Software and Data Integrity Failures**
- Unsafe CI/CD, untrusted sources
- PHP mitigation: Verify dependencies, use checksums

**9. Security Logging Failures**
- Insufficient logging and monitoring
- PHP mitigation: Comprehensive error logging

**10. Server-Side Request Forgery (SSRF)**
- Forced server requests to internal resources
- PHP mitigation: Validate and sanitize URLs

---

## Section 2: SQL Injection Prevention

SQL injection occurs when attackers manipulate SQL queries by injecting malicious SQL code through user input. This is one of the most dangerous vulnerabilities and must be prevented at all costs.

### The Problem: String Concatenation

```php
<?php

declare(strict_types=1);

// ❌ DANGEROUS: Direct string concatenation
$email = $_GET['email'] ?? '';
$sql = "SELECT * FROM users WHERE email = '$email'";
$result = $pdo->query($sql);

// Attacker can inject: email=' OR '1'='1
// Results in: SELECT * FROM users WHERE email = '' OR '1'='1'
// This returns ALL users!
```

### The Solution: Prepared Statements

```php
<?php

declare(strict_types=1);

namespace App\Security;

use PDO;
use PDOException;

class SecureDatabase
{
    public function __construct(
        private PDO $pdo
    ) {}

    /**
     * ✅ SAFE: Use prepared statements with parameters
     */
    public function findUserByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, email, name FROM users WHERE email = :email'
        );
        
        $stmt->execute(['email' => $email]);
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * ✅ SAFE: Multiple parameters
     */
    public function findUsersByRoleAndStatus(
        string $role,
        bool $active
    ): array {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM users WHERE role = :role AND active = :active'
        );
        
        $stmt->execute([
            'role' => $role,
            'active' => $active ? 1 : 0
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ✅ SAFE: INSERT with prepared statements
     */
    public function createUser(
        string $email,
        string $name,
        string $passwordHash
    ): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (email, name, password_hash, created_at) 
             VALUES (:email, :name, :password_hash, NOW())'
        );
        
        $stmt->execute([
            'email' => $email,
            'name' => $name,
            'password_hash' => $passwordHash
        ]);
        
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * ✅ SAFE: LIKE queries with prepared statements
     */
    public function searchUsers(string $searchTerm): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM users WHERE name LIKE :search'
        );
        
        // Escape wildcards in search term
        $searchTerm = str_replace(['%', '_'], ['\%', '\_'], $searchTerm);
        
        $stmt->execute([
            'search' => "%{$searchTerm}%"
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
```

### Positional vs Named Parameters

```php
<?php

declare(strict_types=1);

// Positional parameters (use ?)
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND role = ?');
$stmt->execute([$email, $role]);

// Named parameters (use :name)
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email AND role = :role');
$stmt->execute(['email' => $email, 'role' => $role]);

// Named parameters are more readable and maintainable
```

### Dynamic WHERE Clauses

```php
<?php

declare(strict_types=1);

namespace App\Security;

class UserRepository
{
    public function __construct(
        private \PDO $pdo
    ) {}

    /**
     * ✅ SAFE: Build dynamic queries with prepared statements
     */
    public function findUsers(array $filters): array
    {
        $conditions = [];
        $params = [];

        if (isset($filters['email'])) {
            $conditions[] = 'email = :email';
            $params['email'] = $filters['email'];
        }

        if (isset($filters['role'])) {
            $conditions[] = 'role = :role';
            $params['role'] = $filters['role'];
        }

        if (isset($filters['active'])) {
            $conditions[] = 'active = :active';
            $params['active'] = $filters['active'] ? 1 : 0;
        }

        $where = !empty($conditions)
            ? 'WHERE ' . implode(' AND ', $conditions)
            : '';

        $sql = "SELECT * FROM users {$where}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
```

### Why Prepared Statements Work

Prepared statements separate SQL logic from data:

1. **SQL structure is parsed first** - The database understands the query structure
2. **Parameters are bound separately** - Data is treated as data, not SQL code
3. **Type safety** - Parameters are properly typed and escaped
4. **Performance** - Queries can be cached and reused

---

## Section 3: Cross-Site Scripting (XSS) Protection

XSS attacks occur when malicious scripts are injected into web pages viewed by other users. PHP applications must escape all output to prevent XSS.

### Types of XSS Attacks

**1. Stored XSS** - Malicious script stored in database
**2. Reflected XSS** - Malicious script reflected in response
**3. DOM-based XSS** - Client-side script manipulation

### Output Escaping

```php
<?php

declare(strict_types=1);

namespace App\Security;

class XSSProtection
{
    /**
     * ✅ Escape HTML output
     */
    public static function escapeHtml(string $input): string
    {
        return htmlspecialchars(
            $input,
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );
    }

    /**
     * ✅ Escape HTML attributes
     */
    public static function escapeAttribute(string $input): string
    {
        return htmlspecialchars(
            $input,
            ENT_QUOTES,
            'UTF-8',
            false
        );
    }

    /**
     * ✅ Escape JavaScript strings
     */
    public static function escapeJavaScript(string $input): string
    {
        return json_encode($input, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    /**
     * ✅ Escape URL parameters
     */
    public static function escapeUrl(string $input): string
    {
        return urlencode($input);
    }
}
```

### Context-Aware Escaping

```php
<?php

declare(strict_types=1);

// HTML context
echo '<div>' . htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8') . '</div>';

// HTML attribute context
echo '<input value="' . htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8') . '">';

// JavaScript context
echo '<script>var name = ' . json_encode($userInput, JSON_HEX_TAG) . ';</script>';

// URL context
echo '<a href="/user/' . urlencode($userId) . '">Profile</a>';

// CSS context (rare, but important)
echo '<style>color: ' . preg_replace('/[^a-zA-Z0-9#]/', '', $color) . ';</style>';
```

### Content Security Policy (CSP)

```php
<?php

declare(strict_types=1);

namespace App\Security;

class SecurityHeaders
{
    /**
     * Set Content Security Policy header
     */
    public static function setCSP(string $policy = "default-src 'self'"): void
    {
        header("Content-Security-Policy: {$policy}");
    }

    /**
     * Common CSP policies
     */
    public static function setStrictCSP(): void
    {
        $policy = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline'", // Remove 'unsafe-inline' in production
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: https:",
            "font-src 'self'",
            "connect-src 'self'",
            "frame-ancestors 'none'"
        ]);

        self::setCSP($policy);
    }
}

// Usage
SecurityHeaders::setStrictCSP();
```

### Template Helper Function

```php
<?php

declare(strict_types=1);

// Create a helper function for templates
function e(string $string): string
{
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// Usage in templates
?>
<div class="user-name"><?= e($user['name']) ?></div>
<input type="text" value="<?= e($user['email']) ?>">
<a href="/user/<?= e($userId) ?>">View Profile</a>
```

---

## Section 4: Cross-Site Request Forgery (CSRF) Protection

CSRF attacks trick authenticated users into executing unwanted actions. CSRF tokens ensure requests originate from your application.

### CSRF Token Implementation

```php
<?php

declare(strict_types=1);

namespace App\Security;

class CSRFProtection
{
    /**
     * Generate CSRF token
     */
    public static function generateToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token
     */
    public static function validateToken(string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }

        // Use hash_equals to prevent timing attacks
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Get token from request
     */
    public static function getTokenFromRequest(): ?string
    {
        return $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? null;
    }

    /**
     * Require valid CSRF token or throw exception
     */
    public static function requireToken(): void
    {
        $token = self::getTokenFromRequest();

        if ($token === null || !self::validateToken($token)) {
            http_response_code(403);
            throw new \RuntimeException('CSRF token validation failed');
        }
    }
}
```

### Using CSRF Tokens in Forms

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/Security/CSRFProtection.php';

use App\Security\CSRFProtection;

// Generate token for form
$csrfToken = CSRFProtection::generateToken();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create User</title>
</head>
<body>
    <form method="POST" action="/users/create">
        <!-- Include CSRF token -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
        
        <input type="text" name="name" placeholder="Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <button type="submit">Create User</button>
    </form>
</body>
</html>
```

### Validating CSRF Tokens on Form Submission

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/Security/CSRFProtection.php';

use App\Security\CSRFProtection;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        CSRFProtection::requireToken();

        // Process form data
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';

        // ... create user logic ...

        header('Location: /users');
        exit;
    } catch (\RuntimeException $e) {
        http_response_code(403);
        die('CSRF validation failed');
    }
}
```

### CSRF Protection for AJAX Requests

```php
<?php

declare(strict_types=1);

// API endpoint to get CSRF token
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['PATH_INFO'] === '/csrf-token') {
    header('Content-Type: application/json');
    echo json_encode([
        'token' => CSRFProtection::generateToken()
    ]);
    exit;
}

// AJAX request with CSRF token
?>
<script>
fetch('/api/users', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': '<?= CSRFProtection::generateToken() ?>'
    },
    body: JSON.stringify({ name: 'John', email: 'john@example.com' })
});
</script>
```

---

## Section 5: Secure Password Hashing

Never store passwords in plain text. PHP provides strong password hashing functions that handle salting and secure algorithms automatically.

### Password Hashing

```php
<?php

declare(strict_types=1);

namespace App\Security;

class PasswordSecurity
{
    /**
     * Hash password using Argon2ID (recommended) or bcrypt
     */
    public static function hashPassword(string $password): string
    {
        // PASSWORD_ARGON2ID is the strongest (PHP 7.2+)
        // Falls back to PASSWORD_DEFAULT (bcrypt) if not available
        $options = [
            'memory_cost' => 65536, // 64 MB
            'time_cost' => 4,      // 4 iterations
            'threads' => 3          // 3 threads
        ];

        return password_hash($password, PASSWORD_ARGON2ID, $options);
    }

    /**
     * Verify password against hash
     */
    public static function verifyPassword(
        string $password,
        string $hash
    ): bool {
        return password_verify($password, $hash);
    }

    /**
     * Check if hash needs rehashing (algorithm upgraded)
     */
    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_ARGON2ID);
    }
}
```

### Authentication with Rate Limiting

```php
<?php

declare(strict_types=1);

namespace App\Security;

class Authentication
{
    public function __construct(
        private \PDO $pdo
    ) {}

    /**
     * Login with rate limiting
     */
    public function login(string $email, string $password): bool
    {
        // Check rate limit
        if ($this->isRateLimited($email)) {
            sleep(2); // Slow down brute force attempts
            throw new \RuntimeException('Too many login attempts. Please try again later.');
        }

        // Find user
        $stmt = $this->pdo->prepare(
            'SELECT id, email, password_hash FROM users WHERE email = :email'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            $this->recordFailedAttempt($email);
            return false;
        }

        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            $this->recordFailedAttempt($email);
            return false;
        }

        // Check if hash needs updating
        if (password_needs_rehash($user['password_hash'], PASSWORD_ARGON2ID)) {
            $newHash = password_hash($password, PASSWORD_ARGON2ID);
            $updateStmt = $this->pdo->prepare(
                'UPDATE users SET password_hash = :hash WHERE id = :id'
            );
            $updateStmt->execute([
                'hash' => $newHash,
                'id' => $user['id']
            ]);
        }

        // Clear failed attempts
        $this->clearFailedAttempts($email);

        // Start session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true); // Prevent session fixation
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];

        return true;
    }

    /**
     * Check if email is rate limited
     */
    private function isRateLimited(string $email): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) as attempts, MAX(attempted_at) as last_attempt
             FROM login_attempts 
             WHERE email = :email 
             AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)'
        );
        $stmt->execute(['email' => $email]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return ($result['attempts'] ?? 0) >= 5;
    }

    /**
     * Record failed login attempt
     */
    private function recordFailedAttempt(string $email): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO login_attempts (email, attempted_at) 
             VALUES (:email, NOW())'
        );
        $stmt->execute(['email' => $email]);
    }

    /**
     * Clear failed attempts after successful login
     */
    private function clearFailedAttempts(string $email): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM login_attempts WHERE email = :email'
        );
        $stmt->execute(['email' => $email]);
    }
}
```

---

## Section 6: Security Headers

Security headers provide an additional layer of protection against various attack vectors. Set these headers for all responses.

### Comprehensive Security Headers

```php
<?php

declare(strict_types=1);

namespace App\Security;

class SecurityHeaders
{
    /**
     * Set all security headers
     */
    public static function setAll(): void
    {
        self::setFrameOptions();
        self::setContentTypeOptions();
        self::setXSSProtection();
        self::setReferrerPolicy();
        self::setPermissionsPolicy();
        self::setStrictTransportSecurity();
    }

    /**
     * X-Frame-Options: Prevent clickjacking
     */
    public static function setFrameOptions(string $value = 'DENY'): void
    {
        header("X-Frame-Options: {$value}");
    }

    /**
     * X-Content-Type-Options: Prevent MIME sniffing
     */
    public static function setContentTypeOptions(): void
    {
        header('X-Content-Type-Options: nosniff');
    }

    /**
     * X-XSS-Protection: Enable browser XSS filter
     */
    public static function setXSSProtection(): void
    {
        header('X-XSS-Protection: 1; mode=block');
    }

    /**
     * Referrer-Policy: Control referrer information
     */
    public static function setReferrerPolicy(string $policy = 'strict-origin-when-cross-origin'): void
    {
        header("Referrer-Policy: {$policy}");
    }

    /**
     * Permissions-Policy: Control browser features
     */
    public static function setPermissionsPolicy(): void
    {
        $policy = implode(', ', [
            'geolocation=()',
            'microphone=()',
            'camera=()',
            'payment=()'
        ]);
        header("Permissions-Policy: {$policy}");
    }

    /**
     * Strict-Transport-Security: Force HTTPS
     */
    public static function setStrictTransportSecurity(int $maxAge = 31536000): void
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header("Strict-Transport-Security: max-age={$maxAge}; includeSubDomains; preload");
        }
    }

    /**
     * Content-Security-Policy: Prevent XSS and injection attacks
     */
    public static function setContentSecurityPolicy(string $policy): void
    {
        header("Content-Security-Policy: {$policy}");
    }
}

// Usage: Set headers early in application
SecurityHeaders::setAll();
```

---

## Section 7: Secure File Uploads

File uploads are a common attack vector. Validate file types, scan for malware, and store files securely outside the web root.

### Secure File Upload Handler

```php
<?php

declare(strict_types=1);

namespace App\Security;

class SecureFileUpload
{
    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5 MB
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp'
    ];

    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * Validate and upload file securely
     */
    public function upload(array $file, string $uploadDir): string
    {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('File upload error: ' . $file['error']);
        }

        // Validate file size
        if ($file['size'] > self::MAX_FILE_SIZE) {
            throw new \RuntimeException('File too large');
        }

        // Validate MIME type by content (not extension)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            throw new \RuntimeException('Invalid file type');
        }

        // Validate extension matches MIME type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new \RuntimeException('Invalid file extension');
        }

        // Generate secure filename
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = $uploadDir . '/' . $filename;

        // Ensure upload directory exists and is writable
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new \RuntimeException('Failed to move uploaded file');
        }

        // Set proper permissions
        chmod($destination, 0644);

        return $filename;
    }

    /**
     * Validate image dimensions
     */
    public function validateImageDimensions(
        string $filePath,
        int $maxWidth = 2000,
        int $maxHeight = 2000
    ): bool {
        $imageInfo = getimagesize($filePath);
        
        if ($imageInfo === false) {
            return false;
        }

        [$width, $height] = $imageInfo;

        return $width <= $maxWidth && $height <= $maxHeight;
    }
}
```

### File Upload Usage

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/Security/SecureFileUpload.php';

use App\Security\SecureFileUpload;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    try {
        $uploader = new SecureFileUpload();
        
        // Store outside web root
        $uploadDir = '/var/www/uploads/avatars';
        
        $filename = $uploader->upload($_FILES['avatar'], $uploadDir);
        
        // Validate dimensions
        if (!$uploader->validateImageDimensions($uploadDir . '/' . $filename)) {
            unlink($uploadDir . '/' . $filename);
            throw new \RuntimeException('Image dimensions too large');
        }
        
        // Save filename to database
        // $user->setAvatar($filename);
        
        echo "File uploaded successfully: {$filename}";
    } catch (\RuntimeException $e) {
        echo "Upload failed: " . $e->getMessage();
    }
}
```

---

## Section 8: Input Validation and Sanitization

Always validate input on the server side, even if you validate on the client. Sanitize data before storing or displaying it.

### Input Validation

```php
<?php

declare(strict_types=1);

namespace App\Security;

class InputValidator
{
    /**
     * Validate email
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate URL
     */
    public static function validateUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validate integer range
     */
    public static function validateInt(
        mixed $value,
        int $min = PHP_INT_MIN,
        int $max = PHP_INT_MAX
    ): bool {
        $options = [
            'options' => [
                'min_range' => $min,
                'max_range' => $max
            ]
        ];
        return filter_var($value, FILTER_VALIDATE_INT, $options) !== false;
    }

    /**
     * Validate string length
     */
    public static function validateStringLength(
        string $string,
        int $min = 0,
        int $max = PHP_INT_MAX
    ): bool {
        $length = mb_strlen($string, 'UTF-8');
        return $length >= $min && $length <= $max;
    }

    /**
     * Validate against regex pattern
     */
    public static function validatePattern(string $value, string $pattern): bool
    {
        return preg_match($pattern, $value) === 1;
    }
}
```

### Input Sanitization

```php
<?php

declare(strict_types=1);

namespace App\Security;

class InputSanitizer
{
    /**
     * Sanitize string (remove tags, encode special chars)
     */
    public static function sanitizeString(string $input): string
    {
        // Remove HTML tags
        $cleaned = strip_tags($input);
        
        // Trim whitespace
        $cleaned = trim($cleaned);
        
        // Remove null bytes
        $cleaned = str_replace("\0", '', $cleaned);
        
        return $cleaned;
    }

    /**
     * Sanitize email
     */
    public static function sanitizeEmail(string $email): string
    {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitize URL
     */
    public static function sanitizeUrl(string $url): string
    {
        return filter_var($url, FILTER_SANITIZE_URL);
    }

    /**
     * Sanitize integer
     */
    public static function sanitizeInt(mixed $value): ?int
    {
        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitize for SQL (use prepared statements instead!)
     */
    public static function sanitizeForSql(string $input): string
    {
        // This is a fallback - ALWAYS use prepared statements
        return addslashes($input);
    }
}
```

---

## Section 9: Secure Session Management

Sessions must be configured securely to prevent session hijacking and fixation attacks.

### Secure Session Configuration

```php
<?php

declare(strict_types=1);

namespace App\Security;

class SecureSession
{
    /**
     * Start secure session
     */
    public static function start(): void
    {
        // Configure session before starting
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', '1'); // HTTPS only
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_lifetime', '0'); // Until browser closes
        ini_set('session.gc_maxlifetime', '1800'); // 30 minutes

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Regenerate session ID (prevent session fixation)
     */
    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    /**
     * Destroy session securely
     */
    public static function destroy(): void
    {
        $_SESSION = [];
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(
                session_name(),
                '',
                time() - 3600,
                '/',
                '',
                true,  // secure
                true    // httponly
            );
        }
        
        session_destroy();
    }

    /**
     * Check if session is valid
     */
    public static function isValid(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            return false;
        }

        // Check session timeout
        if (isset($_SESSION['last_activity']) && 
            (time() - $_SESSION['last_activity']) > 1800) {
            return false;
        }

        $_SESSION['last_activity'] = time();
        return true;
    }
}
```

---

## Section 10: Error Handling Without Information Disclosure

Never expose sensitive information in error messages. Log errors securely, but show generic messages to users.

### Secure Error Handling

```php
<?php

declare(strict_types=1);

namespace App\Security;

class SecureErrorHandler
{
    /**
     * Handle errors securely
     */
    public static function handleError(
        int $errno,
        string $errstr,
        string $errfile,
        int $errline
    ): bool {
        // Log full error details
        error_log(sprintf(
            "Error [%d]: %s in %s on line %d",
            $errno,
            $errstr,
            $errfile,
            $errline
        ));

        // Show generic message to user
        if (error_reporting() !== 0) {
            http_response_code(500);
            echo json_encode([
                'error' => 'An internal error occurred. Please try again later.'
            ]);
        }

        return true;
    }

    /**
     * Handle exceptions securely
     */
    public static function handleException(\Throwable $exception): void
    {
        // Log full exception details
        error_log(sprintf(
            "Exception: %s in %s:%d\nStack trace:\n%s",
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        ));

        // Show generic message
        http_response_code(500);
        
        if (php_sapi_name() === 'cli') {
            // Show full error in CLI
            echo $exception->getMessage() . "\n";
            echo $exception->getTraceAsString() . "\n";
        } else {
            // Generic message for web
            echo json_encode([
                'error' => 'An error occurred processing your request.'
            ]);
        }
    }
}

// Set error handlers
set_error_handler([SecureErrorHandler::class, 'handleError']);
set_exception_handler([SecureErrorHandler::class, 'handleException']);
```

---

## Section 11: Authorization and Access Control

Authorization determines what authenticated users are allowed to do. Unlike authentication (who you are), authorization answers "what can you do?" This is critical for preventing unauthorized access to resources.

### Role-Based Access Control (RBAC)

```php
<?php

declare(strict_types=1);

namespace App\Security;

class Authorization
{
    /**
     * Check if user has specific role
     */
    public static function hasRole(string $role): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userRoles = $_SESSION['roles'] ?? [];
        return in_array($role, $userRoles, true);
    }

    /**
     * Check if user has any of the specified roles
     */
    public static function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if (self::hasRole($role)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has all specified roles
     */
    public static function hasAllRoles(array $roles): bool
    {
        foreach ($roles as $role) {
            if (!self::hasRole($role)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Require role or throw exception
     */
    public static function requireRole(string $role): void
    {
        if (!self::hasRole($role)) {
            http_response_code(403);
            throw new \RuntimeException("Access denied. Required role: {$role}");
        }
    }
}
```

### Permission-Based Access Control

```php
<?php

declare(strict_types=1);

namespace App\Security;

class PermissionManager
{
    /**
     * Check if user has permission for specific action
     */
    public static function can(string $action, ?string $resource = null): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $permissions = $_SESSION['permissions'] ?? [];

        // Check specific resource permission
        if ($resource !== null) {
            $key = "{$action}:{$resource}";
            if (isset($permissions[$key])) {
                return $permissions[$key] === true;
            }
        }

        // Check general permission
        return $permissions[$action] ?? false;
    }

    /**
     * Require permission or throw exception
     */
    public static function requirePermission(string $action, ?string $resource = null): void
    {
        if (!self::can($action, $resource)) {
            http_response_code(403);
            throw new \RuntimeException("Permission denied: {$action}");
        }
    }
}

// Usage
PermissionManager::requirePermission('edit', 'post');
PermissionManager::requirePermission('delete', 'user');
```

### Resource Ownership Validation

```php
<?php

declare(strict_types=1);

namespace App\Security;

class ResourceAuthorization
{
    public function __construct(
        private \PDO $pdo
    ) {}

    /**
     * Check if user owns a resource
     */
    public function ownsPost(int $postId, int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT user_id FROM posts WHERE id = :id'
        );
        $stmt->execute(['id' => $postId]);
        $post = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $post && (int)$post['user_id'] === $userId;
    }

    /**
     * Check if user can access resource (owner or admin)
     */
    public function canAccessPost(int $postId, int $userId, array $userRoles): bool
    {
        // Admins can access any post
        if (in_array('admin', $userRoles, true)) {
            return true;
        }

        // Check ownership
        return $this->ownsPost($postId, $userId);
    }

    /**
     * Require resource access or throw exception
     */
    public function requirePostAccess(int $postId, int $userId, array $userRoles): void
    {
        if (!$this->canAccessPost($postId, $userId, $userRoles)) {
            http_response_code(403);
            throw new \RuntimeException('Access denied to this resource');
        }
    }
}
```

### Middleware for Authorization

```php
<?php

declare(strict_types=1);

namespace App\Security;

class AuthorizationMiddleware
{
    public function __construct(
        private string $requiredRole
    ) {}

    public function handle(\Closure $next): mixed
    {
        if (!Authorization::hasRole($this->requiredRole)) {
            http_response_code(403);
            return ['error' => 'Access denied'];
        }

        return $next();
    }
}

// Usage in router
$router->get('/admin/users', function() {
    return ['users' => []];
})->middleware(new AuthorizationMiddleware('admin'));
```

---

## Section 12: Command Injection Prevention

Command injection occurs when user input is passed to system commands without proper sanitization. This allows attackers to execute arbitrary commands on the server.

### The Problem: Unsafe Command Execution

```php
<?php

declare(strict_types=1);

// ❌ DANGEROUS: Direct user input in command
$filename = $_GET['file'] ?? '';
exec("cat /var/logs/{$filename}"); // Attacker can inject: file.txt; rm -rf /

// ❌ DANGEROUS: Shell command with user input
$email = $_POST['email'] ?? '';
system("mail -s 'Welcome' {$email}"); // Attacker can inject: user@example.com; rm -rf /
```

### The Solution: Safe Command Execution

```php
<?php

declare(strict_types=1);

namespace App\Security;

class SafeCommandExecution
{
    /**
     * ✅ SAFE: Use escapeshellarg() for single argument
     */
    public static function executeSafeCommand(string $command, string $argument): string
    {
        $escapedArg = escapeshellarg($argument);
        $fullCommand = "{$command} {$escapedArg}";
        
        return shell_exec($fullCommand);
    }

    /**
     * ✅ SAFE: Use escapeshellcmd() for entire command
     */
    public static function executeSafeShellCommand(string $command): string
    {
        $escapedCmd = escapeshellcmd($command);
        return shell_exec($escapedCmd);
    }

    /**
     * ✅ SAFE: Use proc_open() with array arguments
     */
    public static function executeWithArrayArgs(
        string $command,
        array $arguments
    ): string {
        $escapedArgs = array_map('escapeshellarg', $arguments);
        $fullCommand = $command . ' ' . implode(' ', $escapedArgs);
        
        return shell_exec($fullCommand);
    }

    /**
     * ✅ SAFE: Validate and whitelist allowed commands
     */
    public static function executeWhitelistedCommand(
        string $command,
        string $argument
    ): string {
        $allowedCommands = ['ls', 'cat', 'grep'];
        
        if (!in_array($command, $allowedCommands, true)) {
            throw new \InvalidArgumentException("Command not allowed: {$command}");
        }

        // Validate argument format
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $argument)) {
            throw new \InvalidArgumentException("Invalid argument format");
        }

        $escapedArg = escapeshellarg($argument);
        return shell_exec("{$command} {$escapedArg}");
    }
}
```

### Safe File Operations

```php
<?php

declare(strict_types=1);

namespace App\Security;

class SafeFileOperations
{
    /**
     * ✅ SAFE: Read file without shell commands
     */
    public static function readFile(string $filename): string
    {
        // Validate filename (prevent directory traversal)
        $filename = basename($filename);
        
        // Whitelist allowed characters
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $filename)) {
            throw new \InvalidArgumentException("Invalid filename");
        }

        $path = '/var/logs/' . $filename;
        
        // Check if file exists and is readable
        if (!file_exists($path) || !is_readable($path)) {
            throw new \RuntimeException("File not accessible");
        }

        return file_get_contents($path);
    }

    /**
     * ✅ SAFE: Use PHP functions instead of shell commands
     */
    public static function listDirectory(string $directory): array
    {
        // Validate directory path
        $directory = realpath($directory);
        
        if ($directory === false || !is_dir($directory)) {
            throw new \InvalidArgumentException("Invalid directory");
        }

        // Use PHP functions instead of exec('ls')
        return array_filter(scandir($directory), function($file) {
            return $file !== '.' && $file !== '..';
        });
    }
}
```

### Why It Works

- **`escapeshellarg()`**: Wraps argument in single quotes and escapes any single quotes
- **`escapeshellcmd()`**: Escapes shell metacharacters in entire command
- **Whitelisting**: Only allow specific, safe commands
- **Input validation**: Validate format before using in commands
- **PHP alternatives**: Use PHP functions instead of shell commands when possible

---

## Section 13: XXE (XML External Entity) Prevention

XXE attacks exploit XML parsers that process external entity references, potentially exposing files or causing denial of service.

### The Problem: Unsafe XML Parsing

```php
<?php

declare(strict_types=1);

// ❌ DANGEROUS: Allows external entities
$xml = <<<XML
<?xml version="1.0"?>
<!DOCTYPE foo [
  <!ENTITY xxe SYSTEM "file:///etc/passwd">
]>
<foo>&xxe;</foo>
XML;

$data = simplexml_load_string($xml); // Reads /etc/passwd!
```

### The Solution: Disable External Entities

```php
<?php

declare(strict_types=1);

namespace App\Security;

class SafeXMLParser
{
    /**
     * ✅ SAFE: Parse XML with external entities disabled
     */
    public static function parseXML(string $xmlString): \SimpleXMLElement
    {
        // Disable external entity loading
        $previousValue = libxml_disable_entity_loader(true);
        
        // Disable external entity processing
        libxml_set_external_entity_loader(function() {
            return null; // Block all external entities
        });

        try {
            $xml = simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOENT);
            
            if ($xml === false) {
                throw new \RuntimeException('Failed to parse XML');
            }

            return $xml;
        } finally {
            // Restore previous setting
            libxml_disable_entity_loader($previousValue);
        }
    }

    /**
     * ✅ SAFE: Use DOMDocument with secure settings
     */
    public static function parseXMLSecure(string $xmlString): \DOMDocument
    {
        $dom = new \DOMDocument();
        
        // Disable external entity loading
        $previousValue = libxml_disable_entity_loader(true);
        
        try {
            // Load XML with secure flags
            $dom->loadXML($xmlString, LIBXML_NOENT | LIBXML_DTDLOAD);
            
            return $dom;
        } finally {
            libxml_disable_entity_loader($previousValue);
        }
    }

    /**
     * ✅ SAFE: Validate XML structure before parsing
     */
    public static function validateAndParse(string $xmlString): \SimpleXMLElement
    {
        // Check for dangerous patterns
        if (preg_match('/<!ENTITY\s+\w+\s+SYSTEM/i', $xmlString)) {
            throw new \RuntimeException('External entities not allowed');
        }

        if (preg_match('/<!DOCTYPE/i', $xmlString)) {
            throw new \RuntimeException('DOCTYPE declarations not allowed');
        }

        return self::parseXML($xmlString);
    }
}
```

### Configuration: php.ini Settings

```ini
; Disable external entity loading globally
libxml_disable_entity_loader = true
```

---

## Section 14: Deserialization Security

Unsafe deserialization can lead to object injection attacks, allowing attackers to execute arbitrary code or access sensitive data.

### The Problem: Unsafe Deserialization

```php
<?php

declare(strict_types=1);

// ❌ DANGEROUS: Deserializing untrusted data
$data = $_COOKIE['user_data'] ?? '';
$user = unserialize($data); // Attacker can inject malicious objects!

// ❌ DANGEROUS: Magic methods can execute code
class User {
    public function __wakeup() {
        // This executes during unserialize!
        system($_GET['cmd']);
    }
}
```

### The Solution: Safe Serialization Alternatives

```php
<?php

declare(strict_types=1);

namespace App\Security;

class SafeSerialization
{
    /**
     * ✅ SAFE: Use JSON instead of serialize()
     */
    public static function serializeToJson(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    public static function deserializeFromJson(string $json): array
    {
        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * ✅ SAFE: Whitelist allowed classes for deserialization
     */
    public static function safeUnserialize(
        string $data,
        array $allowedClasses = []
    ): mixed {
        // Use allowed_classes option (PHP 7.0+)
        $options = ['allowed_classes' => $allowedClasses];
        
        return unserialize($data, $options);
    }

    /**
     * ✅ SAFE: Validate serialized data before deserializing
     */
    public static function validateAndDeserialize(
        string $data,
        array $allowedClasses = []
    ): mixed {
        // Check for dangerous patterns
        if (preg_match('/[OoC]:\d+:"/', $data)) {
            // Contains object or class reference
            if (empty($allowedClasses)) {
                throw new \RuntimeException('Object deserialization not allowed');
            }
        }

        return self::safeUnserialize($data, $allowedClasses);
    }
}

// Usage
$data = SafeSerialization::serializeToJson(['name' => 'John', 'email' => 'john@example.com']);
$user = SafeSerialization::deserializeFromJson($data);

// If you must use serialize(), whitelist classes
$user = SafeSerialization::safeUnserialize($serialized, [User::class]);
```

### Best Practices

```php
<?php

declare(strict_types=1);

// ✅ Prefer JSON for data serialization
$data = json_encode($userData);
$userData = json_decode($data, true);

// ✅ If using serialize(), whitelist classes
$user = unserialize($data, ['allowed_classes' => [User::class]]);

// ✅ Never deserialize untrusted input
// Always validate source and signature
```

---

## Section 15: Mass Assignment Prevention

Mass assignment vulnerabilities occur when applications allow users to update fields they shouldn't have access to by including them in form data.

### The Problem: Mass Assignment Vulnerability

```php
<?php

declare(strict_types=1);

// ❌ DANGEROUS: Updating all fields from request
$userData = $_POST;
$stmt = $pdo->prepare('UPDATE users SET name = :name, email = :email, role = :role WHERE id = :id');
$stmt->execute($userData); // Attacker can set role = 'admin'!
```

### The Solution: Whitelist Allowed Fields

```php
<?php

declare(strict_types=1);

namespace App\Security;

class MassAssignmentProtection
{
    /**
     * ✅ SAFE: Whitelist allowed fields
     */
    public static function filterFields(
        array $data,
        array $allowedFields
    ): array {
        return array_intersect_key($data, array_flip($allowedFields));
    }

    /**
     * ✅ SAFE: Update only specific fields
     */
    public static function updateUser(
        \PDO $pdo,
        int $userId,
        array $data
    ): void {
        // Whitelist allowed fields
        $allowedFields = ['name', 'email', 'bio'];
        $filteredData = self::filterFields($data, $allowedFields);

        if (empty($filteredData)) {
            return;
        }

        // Build dynamic UPDATE query
        $fields = [];
        $params = ['id' => $userId];

        foreach ($filteredData as $field => $value) {
            $fields[] = "{$field} = :{$field}";
            $params[$field] = $value;
        }

        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    /**
     * ✅ SAFE: Role-based field filtering
     */
    public static function filterFieldsByRole(
        array $data,
        string $userRole
    ): array {
        $roleFields = [
            'user' => ['name', 'email', 'bio'],
            'admin' => ['name', 'email', 'bio', 'role', 'status'],
        ];

        $allowedFields = $roleFields[$userRole] ?? $roleFields['user'];
        return self::filterFields($data, $allowedFields);
    }
}
```

### Using in Controllers

```php
<?php

declare(strict_types=1);

// In controller
$allowedFields = ['name', 'email', 'bio'];
$userData = MassAssignmentProtection::filterFields($_POST, $allowedFields);

$stmt = $pdo->prepare('UPDATE users SET name = :name, email = :email, bio = :bio WHERE id = :id');
$stmt->execute(array_merge($userData, ['id' => $userId]));
```

---

## Section 16: IDOR (Insecure Direct Object References) Prevention

IDOR vulnerabilities occur when applications expose internal implementation details (like database IDs) and fail to verify user access to those resources.

### The Problem: Direct Object Reference

```php
<?php

declare(strict_types=1);

// ❌ DANGEROUS: No access control check
$postId = $_GET['id'] ?? 0;
$stmt = $pdo->prepare('SELECT * FROM posts WHERE id = :id');
$stmt->execute(['id' => $postId]);
$post = $stmt->fetch(); // User can access any post by changing ID!
```

### The Solution: Resource Access Validation

```php
<?php

declare(strict_types=1);

namespace App\Security;

class IDORProtection
{
    public function __construct(
        private \PDO $pdo
    ) {}

    /**
     * ✅ SAFE: Verify user owns resource
     */
    public function getPostForUser(int $postId, int $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM posts WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute([
            'id' => $postId,
            'user_id' => $userId
        ]);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * ✅ SAFE: Check access before returning resource
     */
    public function requirePostAccess(int $postId, int $userId, array $userRoles): array
    {
        // Admins can access any post
        if (in_array('admin', $userRoles, true)) {
            $stmt = $this->pdo->prepare('SELECT * FROM posts WHERE id = :id');
            $stmt->execute(['id' => $postId]);
        } else {
            // Regular users can only access their own posts
            $stmt = $this->pdo->prepare(
                'SELECT * FROM posts WHERE id = :id AND user_id = :user_id'
            );
            $stmt->execute([
                'id' => $postId,
                'user_id' => $userId
            ]);
        }

        $post = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$post) {
            http_response_code(404); // Don't reveal resource exists
            throw new \RuntimeException('Post not found');
        }

        return $post;
    }

    /**
     * ✅ SAFE: Use indirect references (tokens instead of IDs)
     */
    public function getPostByToken(string $token): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.* FROM posts p 
             JOIN post_tokens pt ON p.id = pt.post_id 
             WHERE pt.token = :token AND pt.expires_at > NOW()'
        );
        $stmt->execute(['token' => $token]);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
}
```

### Using Indirect References

```php
<?php

declare(strict_types=1);

// Generate shareable token instead of exposing ID
$token = bin2hex(random_bytes(32));

$stmt = $pdo->prepare(
    'INSERT INTO post_tokens (post_id, token, expires_at) 
     VALUES (:post_id, :token, DATE_ADD(NOW(), INTERVAL 7 DAY))'
);
$stmt->execute([
    'post_id' => $postId,
    'token' => $token
]);

// Share URL: /post?token=abc123... instead of /post?id=42
```

---

## Section 17: Secure Configuration Management

Sensitive configuration data like API keys, database passwords, and secrets must be stored securely and never committed to version control.

### Environment Variables

```php
<?php

declare(strict_types=1);

namespace App\Config;

class Environment
{
    /**
     * Get environment variable with default
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $value = $_ENV[$key] ?? getenv($key);
        return $value !== false ? $value : $default;
    }

    /**
     * Require environment variable (throws if missing)
     */
    public static function require(string $key): string
    {
        $value = self::get($key);
        
        if ($value === null) {
            throw new \RuntimeException("Required environment variable not set: {$key}");
        }

        return $value;
    }

    /**
     * Get boolean environment variable
     */
    public static function getBool(string $key, bool $default = false): bool
    {
        $value = self::get($key);
        
        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get integer environment variable
     */
    public static function getInt(string $key, int $default = 0): int
    {
        $value = self::get($key);
        
        if ($value === null) {
            return $default;
        }

        return (int) $value;
    }
}
```

### Loading .env Files Securely

```php
<?php

declare(strict_types=1);

namespace App\Config;

class EnvLoader
{
    /**
     * Load .env file securely
     */
    public static function load(string $envFile = '.env'): void
    {
        if (!file_exists($envFile)) {
            throw new \RuntimeException("Environment file not found: {$envFile}");
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse KEY=VALUE
            if (strpos($line, '=') === false) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remove quotes if present
            $value = trim($value, '"\'');
            
            // Set environment variable if not already set
            if (!isset($_ENV[$key]) && !getenv($key)) {
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }
}

// Usage: Load at application startup
EnvLoader::load();

// Access securely
$dbPassword = Environment::require('DB_PASSWORD');
$apiKey = Environment::require('API_KEY');
```

### .env File Example

```bash
# .env.example (commit this)
DB_HOST=localhost
DB_NAME=myapp
DB_USER=myuser
DB_PASSWORD=

# .env (DO NOT COMMIT - add to .gitignore)
DB_HOST=localhost
DB_NAME=myapp_production
DB_USER=prod_user
DB_PASSWORD=super_secret_password_123
API_KEY=sk_live_abc123xyz789
```

### .gitignore Configuration

```gitignore
# Environment files
.env
.env.local
.env.*.local

# Secrets
secrets/
*.key
*.pem
```

### Secure Secret Storage

```php
<?php

declare(strict_types=1);

namespace App\Security;

class SecretManager
{
    /**
     * Encrypt sensitive value before storage
     */
    public static function encrypt(string $value, string $key): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $encrypted = sodium_crypto_secretbox($value, $nonce, $key);
        
        return base64_encode($nonce . $encrypted);
    }

    /**
     * Decrypt stored value
     */
    public static function decrypt(string $encrypted, string $key): string
    {
        $data = base64_decode($encrypted, true);
        
        if ($data === false) {
            throw new \RuntimeException('Invalid encrypted data');
        }

        $nonce = mb_substr($data, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        $ciphertext = mb_substr($data, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');

        $decrypted = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
        
        if ($decrypted === false) {
            throw new \RuntimeException('Decryption failed');
        }

        return $decrypted;
    }
}
```

---

## Section 18: Dependency Vulnerability Scanning

Keeping dependencies updated and scanning for known vulnerabilities is critical for application security.

### Using Composer Audit

```bash
# Check for known vulnerabilities
composer audit

# Update dependencies
composer update

# Update specific package
composer update vendor/package-name

# Check outdated packages
composer outdated
```

### Automated Security Checks

```php
<?php

declare(strict_types=1);

namespace App\Security;

class DependencyScanner
{
    /**
     * Run composer audit and parse results
     */
    public static function audit(): array
    {
        $output = [];
        $returnCode = 0;
        
        exec('composer audit --format=json 2>&1', $output, $returnCode);
        
        $json = implode("\n", $output);
        $data = json_decode($json, true);
        
        return [
            'vulnerabilities' => $data['advisories'] ?? [],
            'count' => count($data['advisories'] ?? []),
            'has_vulnerabilities' => $returnCode !== 0
        ];
    }

    /**
     * Check if specific package has vulnerabilities
     */
    public static function hasVulnerabilities(string $package): bool
    {
        $audit = self::audit();
        
        foreach ($audit['vulnerabilities'] as $vuln) {
            if ($vuln['package'] === $package) {
                return true;
            }
        }
        
        return false;
    }
}
```

### CI/CD Integration

```yaml
# .github/workflows/security.yml
name: Security Audit

on:
  schedule:
    - cron: '0 0 * * 0' # Weekly
  pull_request:
    paths:
      - 'composer.json'
      - 'composer.lock'

jobs:
  audit:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - uses: php-actions/composer@v6
      - name: Run security audit
        run: composer audit
```

### Best Practices

- ✅ Run `composer audit` regularly (weekly/monthly)
- ✅ Update dependencies promptly when vulnerabilities are found
- ✅ Pin dependency versions in `composer.lock`
- ✅ Review changelogs before updating major versions
- ✅ Use `composer update --with-dependencies` to update transitive dependencies

---

## Section 19: Encryption at Rest

Sensitive data stored in databases should be encrypted to protect against data breaches.

### Database Field Encryption

```php
<?php

declare(strict_types=1);

namespace App\Security;

class Encryption
{
    private string $key;

    public function __construct(string $encryptionKey)
    {
        if (strlen($encryptionKey) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw new \InvalidArgumentException('Invalid key length');
        }
        $this->key = $encryptionKey;
    }

    /**
     * Encrypt sensitive data before storage
     */
    public function encrypt(string $plaintext): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = sodium_crypto_secretbox($plaintext, $nonce, $this->key);
        
        // Store nonce with ciphertext
        return base64_encode($nonce . $ciphertext);
    }

    /**
     * Decrypt stored data
     */
    public function decrypt(string $encrypted): string
    {
        $data = base64_decode($encrypted, true);
        
        if ($data === false) {
            throw new \RuntimeException('Invalid encrypted data');
        }

        $nonce = mb_substr($data, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
        $ciphertext = mb_substr($data, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');

        $plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, $this->key);
        
        if ($plaintext === false) {
            throw new \RuntimeException('Decryption failed');
        }

        return $plaintext;
    }
}
```

### Using Encryption in Models

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Security\Encryption;

class User
{
    public function __construct(
        private \PDO $pdo,
        private Encryption $encryption
    ) {}

    /**
     * Store encrypted credit card number
     */
    public function updateCreditCard(int $userId, string $cardNumber): void
    {
        $encrypted = $this->encryption->encrypt($cardNumber);
        
        $stmt = $this->pdo->prepare(
            'UPDATE users SET credit_card_encrypted = :encrypted WHERE id = :id'
        );
        $stmt->execute([
            'encrypted' => $encrypted,
            'id' => $userId
        ]);
    }

    /**
     * Retrieve and decrypt credit card
     */
    public function getCreditCard(int $userId): ?string
    {
        $stmt = $this->pdo->prepare(
            'SELECT credit_card_encrypted FROM users WHERE id = :id'
        );
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user || !$user['credit_card_encrypted']) {
            return null;
        }

        return $this->encryption->decrypt($user['credit_card_encrypted']);
    }
}
```

### Key Management

```php
<?php

declare(strict_types=1);

namespace App\Security;

class KeyManager
{
    /**
     * Generate encryption key
     */
    public static function generateKey(): string
    {
        return sodium_crypto_secretbox_keygen();
    }

    /**
     * Load key from secure storage
     */
    public static function loadKey(): string
    {
        $keyPath = Environment::require('ENCRYPTION_KEY_PATH');
        
        if (!file_exists($keyPath)) {
            throw new \RuntimeException("Encryption key not found at: {$keyPath}");
        }

        $key = file_get_contents($keyPath);
        
        if (strlen($key) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            throw new \RuntimeException("Invalid key length");
        }

        return $key;
    }
}
```

---

## Section 20: Security Logging and Monitoring

Comprehensive security logging helps detect attacks and investigate security incidents.

### Security Event Logging

```php
<?php

declare(strict_types=1);

namespace App\Security;

class SecurityLogger
{
    private string $logFile;

    public function __construct(string $logFile = '/var/log/security.log')
    {
        $this->logFile = $logFile;
    }

    /**
     * Log security event
     */
    public function logEvent(
        string $eventType,
        array $context = [],
        string $severity = 'INFO'
    ): void {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'severity' => $severity,
            'event' => $eventType,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'context' => $context
        ];

        $message = json_encode($logEntry, JSON_UNESCAPED_SLASHES) . "\n";
        error_log($message, 3, $this->logFile);
    }

    /**
     * Log failed login attempt
     */
    public function logFailedLogin(string $email, string $reason = ''): void
    {
        $this->logEvent('FAILED_LOGIN', [
            'email' => $email,
            'reason' => $reason
        ], 'WARNING');
    }

    /**
     * Log successful login
     */
    public function logSuccessfulLogin(int $userId, string $email): void
    {
        $this->logEvent('SUCCESSFUL_LOGIN', [
            'user_id' => $userId,
            'email' => $email
        ], 'INFO');
    }

    /**
     * Log unauthorized access attempt
     */
    public function logUnauthorizedAccess(string $resource, string $action): void
    {
        $this->logEvent('UNAUTHORIZED_ACCESS', [
            'resource' => $resource,
            'action' => $action
        ], 'ALERT');
    }

    /**
     * Log suspicious activity
     */
    public function logSuspiciousActivity(string $description, array $details = []): void
    {
        $this->logEvent('SUSPICIOUS_ACTIVITY', [
            'description' => $description,
            'details' => $details
        ], 'CRITICAL');
    }
}
```

### Intrusion Detection Patterns

```php
<?php

declare(strict_types=1);

namespace App\Security;

class IntrusionDetection
{
    public function __construct(
        private \PDO $pdo,
        private SecurityLogger $logger
    ) {}

    /**
     * Detect brute force attempts
     */
    public function detectBruteForce(string $identifier, int $threshold = 5): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) as attempts 
             FROM security_logs 
             WHERE event = "FAILED_LOGIN" 
             AND identifier = :identifier 
             AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)'
        );
        $stmt->execute(['identifier' => $identifier]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        $attempts = (int) ($result['attempts'] ?? 0);

        if ($attempts >= $threshold) {
            $this->logger->logSuspiciousActivity('Brute force detected', [
                'identifier' => $identifier,
                'attempts' => $attempts
            ]);
            return true;
        }

        return false;
    }

    /**
     * Detect SQL injection attempts
     */
    public function detectSQLInjection(string $input): bool
    {
        $patterns = [
            '/(\bUNION\b.*\bSELECT\b)/i',
            '/(\bOR\b.*=.*)/i',
            '/(\bAND\b.*=.*)/i',
            '/(\bEXEC\b|\bEXECUTE\b)/i',
            '/(\bDROP\b.*\bTABLE\b)/i',
            '/(\bINSERT\b.*\bINTO\b)/i',
            '/(\bDELETE\b.*\bFROM\b)/i',
            '/(\bUPDATE\b.*\bSET\b)/i',
            '/(\'\s*OR\s*\'\d+\'=\'\d+)/i',
            '/(;\s*DROP\s+TABLE)/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $this->logger->logSuspiciousActivity('SQL injection attempt detected', [
                    'input' => substr($input, 0, 100) // Log first 100 chars
                ]);
                return true;
            }
        }

        return false;
    }

    /**
     * Detect XSS attempts
     */
    public function detectXSS(string $input): bool
    {
        $patterns = [
            '/<script[^>]*>/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe[^>]*>/i',
            '/<object[^>]*>/i',
            '/<embed[^>]*>/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $this->logger->logSuspiciousActivity('XSS attempt detected', [
                    'input' => substr($input, 0, 100)
                ]);
                return true;
            }
        }

        return false;
    }
}
```

### Log Analysis

```php
<?php

declare(strict_types=1);

namespace App\Security;

class LogAnalyzer
{
    public function __construct(
        private string $logFile
    ) {}

    /**
     * Analyze security logs for patterns
     */
    public function analyze(int $hours = 24): array
    {
        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $cutoff = time() - ($hours * 3600);
        
        $analysis = [
            'failed_logins' => 0,
            'unauthorized_access' => 0,
            'suspicious_activities' => 0,
            'top_ips' => [],
            'top_events' => []
        ];

        foreach ($lines as $line) {
            $entry = json_decode($line, true);
            
            if (!$entry || $entry['timestamp'] < date('Y-m-d H:i:s', $cutoff)) {
                continue;
            }

            // Count events
            switch ($entry['event']) {
                case 'FAILED_LOGIN':
                    $analysis['failed_logins']++;
                    break;
                case 'UNAUTHORIZED_ACCESS':
                    $analysis['unauthorized_access']++;
                    break;
                case 'SUSPICIOUS_ACTIVITY':
                    $analysis['suspicious_activities']++;
                    break;
            }

            // Track IPs
            $ip = $entry['ip'] ?? 'unknown';
            $analysis['top_ips'][$ip] = ($analysis['top_ips'][$ip] ?? 0) + 1;

            // Track events
            $event = $entry['event'];
            $analysis['top_events'][$event] = ($analysis['top_events'][$event] ?? 0) + 1;
        }

        // Sort top IPs and events
        arsort($analysis['top_ips']);
        arsort($analysis['top_events']);
        $analysis['top_ips'] = array_slice($analysis['top_ips'], 0, 10, true);
        $analysis['top_events'] = array_slice($analysis['top_events'], 0, 10, true);

        return $analysis;
    }
}
```

---

## Best Practices Summary

✅ **Always use prepared statements** - Never concatenate user input into SQL queries
✅ **Escape all output** - Use `htmlspecialchars()` for HTML, `json_encode()` for JavaScript
✅ **Implement CSRF protection** - Use tokens for all state-changing operations
✅ **Hash passwords securely** - Use `password_hash()` with `PASSWORD_ARGON2ID`
✅ **Set security headers** - Configure all recommended security headers
✅ **Validate file uploads** - Check MIME type by content, not extension
✅ **Validate and sanitize input** - Validate early, sanitize before storage
✅ **Configure sessions securely** - Use httponly, secure, and samesite cookies
✅ **Handle errors securely** - Log details, show generic messages to users
✅ **Implement authorization checks** - Verify user permissions for all resources
✅ **Prevent command injection** - Use `escapeshellarg()` and whitelist commands
✅ **Disable XML external entities** - Prevent XXE attacks in XML parsing
✅ **Avoid unsafe deserialization** - Prefer JSON, whitelist classes if using serialize()
✅ **Prevent mass assignment** - Whitelist allowed fields, filter by role
✅ **Validate resource access** - Check ownership and permissions (prevent IDOR)
✅ **Secure configuration management** - Use environment variables, never commit secrets
✅ **Scan dependencies regularly** - Use `composer audit` to find vulnerabilities
✅ **Encrypt sensitive data at rest** - Use sodium encryption for database fields
✅ **Log security events** - Monitor failed logins, unauthorized access, suspicious activity
✅ **Keep dependencies updated** - Regularly update Composer packages
✅ **Use HTTPS in production** - Encrypt all traffic
✅ **Implement rate limiting** - Prevent brute force attacks
✅ **Follow principle of least privilege** - Grant minimum necessary permissions
✅ **Regular security audits** - Review code for vulnerabilities

---

## Exercises

### Exercise 1: Secure Login System

**Goal**: Implement a secure login system with password hashing and rate limiting

Create a file called `secure-login.php` and implement:

- Password hashing using `PASSWORD_ARGON2ID`
- Rate limiting (max 5 attempts per 15 minutes)
- Session regeneration after login
- CSRF token validation

**Validation**: Test your implementation:

```php
// Test password hashing
$hash = password_hash('test123', PASSWORD_ARGON2ID);
echo password_verify('test123', $hash) ? "Password verified\n" : "Password failed\n";

// Test rate limiting
// Attempt 6 logins within 15 minutes - should be blocked
```

### Exercise 2: CSRF Protection Middleware

**Goal**: Create a reusable CSRF protection middleware

Create a file called `csrf-middleware.php` and implement:

- Token generation and storage
- Token validation with timing attack prevention
- Middleware that can be applied to routes
- Token refresh mechanism

**Validation**: Test CSRF protection:

```php
// Generate token
$token = CSRFProtection::generateToken();

// Validate correct token
echo CSRFProtection::validateToken($token) ? "Valid\n" : "Invalid\n";

// Validate incorrect token
echo CSRFProtection::validateToken('wrong') ? "Valid\n" : "Invalid\n";
```

### Exercise 3: Secure File Upload Handler

**Goal**: Build a secure file upload system

Create a file called `secure-upload.php` and implement:

- MIME type validation by content (not extension)
- File size limits
- Secure filename generation
- Image dimension validation
- Storage outside web root

**Validation**: Test file upload security:

```php
// Test valid image upload
// Test invalid file type
// Test oversized file
// Test malicious filename
```

---

## Troubleshooting

### Error: "SQLSTATE[HY000]: General error"

**Symptom**: Prepared statement fails with general error

**Cause**: Parameter count mismatch or invalid parameter types

**Solution**: Ensure parameter count matches placeholders:

```php
// ❌ Wrong: Missing parameter
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email AND role = :role');
$stmt->execute(['email' => $email]); // Missing 'role'

// ✅ Correct: All parameters provided
$stmt->execute(['email' => $email, 'role' => $role]);
```

### Problem: XSS Still Occurring Despite Escaping

**Symptom**: Scripts execute even after using `htmlspecialchars()`

**Cause**: Escaping in wrong context (e.g., HTML escaping in JavaScript)

**Solution**: Use context-appropriate escaping:

```php
// ❌ Wrong: HTML escaping in JavaScript
echo '<script>var name = "' . htmlspecialchars($name) . '";</script>';

// ✅ Correct: JSON encoding for JavaScript
echo '<script>var name = ' . json_encode($name) . ';</script>';
```

### Problem: CSRF Token Validation Always Fails

**Symptom**: CSRF validation fails even with correct token

**Cause**: Session not started before token generation or validation

**Solution**: Ensure session is started:

```php
// ✅ Always start session first
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$token = CSRFProtection::generateToken();
```

### Problem: Password Verification Fails

**Symptom**: `password_verify()` returns false even with correct password

**Cause**: Password hash was created with different algorithm or corrupted

**Solution**: Verify hash format and algorithm:

```php
// Check hash info
$info = password_get_info($hash);
echo $info['algoName']; // Should be 'argon2id' or 'bcrypt'

// Rehash if needed
if (password_needs_rehash($hash, PASSWORD_ARGON2ID)) {
    $newHash = password_hash($password, PASSWORD_ARGON2ID);
}
```

---

## Wrap-up

You've completed a comprehensive security chapter covering the most critical vulnerabilities and their mitigations. Here's what you've accomplished:

- ✅ **Prevented SQL injection** using prepared statements and parameterized queries
- ✅ **Protected against XSS** with proper output escaping and Content Security Policy
- ✅ **Implemented CSRF protection** with secure token generation and validation
- ✅ **Secured authentication** with strong password hashing and rate limiting
- ✅ **Configured security headers** to protect against common attack vectors
- ✅ **Handled file uploads securely** with content validation and safe storage
- ✅ **Validated and sanitized input** using PHP's filter functions
- ✅ **Managed sessions securely** with proper configuration and regeneration
- ✅ **Handled errors safely** without exposing sensitive information
- ✅ **Applied OWASP Top 10 mitigations** to your PHP applications

Security is an ongoing process, not a one-time task. Always validate input, escape output, use prepared statements, and keep your dependencies updated. When in doubt, use battle-tested framework features instead of building your own security mechanisms.

<ChapterCheckbox 
  seriesId="php-for-java-developers"
  chapterId="18"
  label="Completed security best practices and OWASP Top 10 mitigations!"
/>

---

## Further Reading

- [OWASP Top 10](https://owasp.org/www-project-top-ten/) — The most critical web application security risks
- [OWASP PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html) — PHP-specific security guidelines
- [PHP Manual: Password Hashing](https://www.php.net/manual/en/book.password.php) — Official password hashing documentation
- [PHP Manual: Prepared Statements](https://www.php.net/manual/en/pdo.prepared-statements.php) — PDO prepared statements guide
- [Content Security Policy](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP) — CSP header documentation
- [Paragon Initiative Security Guide](https://paragonie.com/blog/2017/12/2018-guide-building-secure-php-software) — Comprehensive PHP security guide

---

## Chapter Wrap-up Checklist

Before moving to the next chapter, ensure you can:

- [ ] Explain OWASP Top 10 vulnerabilities and their PHP mitigations
- [ ] Use prepared statements to prevent SQL injection
- [ ] Escape output appropriately for different contexts (HTML, JavaScript, URLs)
- [ ] Implement CSRF protection with secure tokens
- [ ] Hash passwords using `PASSWORD_ARGON2ID` and verify them securely
- [ ] Configure security headers for your application
- [ ] Validate and securely handle file uploads
- [ ] Validate and sanitize user input
- [ ] Configure sessions securely with proper cookie settings
- [ ] Handle errors without exposing sensitive information
- [ ] Apply security best practices to your PHP applications

---

<div style="display: flex; justify-content: space-between; margin-top: 2rem;">
  <div>
    <strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/17-forms-and-validation">← Chapter 17</a>
  </div>
  <div><strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/19-framework-comparison">Chapter 19 →</a></div>
</div>
