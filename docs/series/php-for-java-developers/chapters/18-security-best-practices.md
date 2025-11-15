---
title: "18: Security Best Practices"
description: "OWASP Top 10, XSS, SQL injection, security headers"
series: "php-for-java-developers"
chapter: 18
order: 18
difficulty: "Advanced"
prerequisites:
  - "/series/php-for-java-developers/chapters/17-forms-and-validation"
---

# Chapter 18: Security Best Practices

<Badge type="danger">Advanced</Badge>

## Overview

Essential security practices for PHP applications based on OWASP guidelines.

**Topics:** OWASP Top 10, SQL injection prevention, XSS protection, CSRF, Security headers

## Section 1: SQL Injection Prevention

```php
<?php
// ✅ Use prepared statements
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);

// ❌ Never concatenate user input
$sql = "SELECT * FROM users WHERE email = '$email'";  // DANGEROUS!
```

## Section 2: XSS Protection

```php
<?php
// Always escape output
echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');

// Content Security Policy header
header("Content-Security-Policy: default-src 'self'");
```

## Section 3: Authentication Security

```php
<?php
// Strong password hashing
$hash = password_hash($password, PASSWORD_ARGON2ID);

// Rate limiting for login attempts
if ($attempts > 5) {
    sleep(pow(2, $attempts)); // Exponential backoff
}
```

## Section 4: Security Headers

```php
<?php
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000');
```

## Section 5: File Upload Security

```php
<?php
// Validate file type by content, not extension
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $_FILES['file']['tmp_name']);

// Store outside web root
$destination = '/var/uploads/' . bin2hex(random_bytes(16));
```

---

<div style="display: flex; justify-content: space-between;">
  <div><strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/17-forms-and-validation">← Chapter 17</a></div>
  <div><strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/19-framework-comparison">Chapter 19 →</a></div>
</div>
