---
title: "16: Sessions & Authentication"
description: "Session management, authentication strategies, security"
series: "php-for-java-developers"
chapter: 16
order: 16
difficulty: "Intermediate"
prerequisites:
  - "/series/php-for-java-developers/chapters/15-http-and-request-response"
---

# Chapter 16: Sessions & Authentication

<Badge type="warning">Intermediate</Badge>

## Overview

Session management and authentication in PHP, from native sessions to modern token-based auth.

**Topics:** PHP sessions, Session security, JWT authentication, OAuth, Password hashing

## Section 1: PHP Sessions

```php
<?php
session_start();
$_SESSION['user_id'] = 123;
$userId = $_SESSION['user_id'] ?? null;
session_destroy();
```

## Section 2: Secure Sessions

```php
<?php
session_start([
    'cookie_lifetime' => 0,
    'cookie_secure' => true,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => true,
]);
```

## Section 3: Password Hashing

```php
<?php
$hash = password_hash($password, PASSWORD_ARGON2ID);
if (password_verify($password, $hash)) {
    // Valid
}
```

## Section 4: JWT Authentication

```php
<?php
$token = JWT::encode(['userId' => 123], $secret);
$payload = JWT::decode($token, $secret);
```

## Section 5: OAuth Integration

```php
<?php
// OAuth 2.0 flow
$authUrl = $provider->getAuthorizationUrl();
$token = $provider->getAccessToken('authorization_code', ['code' => $code]);
```

---

<div style="display: flex; justify-content: space-between;">
  <div><strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/15-http-and-request-response">← Chapter 15</a></div>
  <div><strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/17-forms-and-validation">Chapter 17 →</a></div>
</div>
