---
title: "17: Forms & Validation"
description: "Form handling, CSRF protection, validation techniques"
series: "php-for-java-developers"
chapter: 17
order: 17
difficulty: "Intermediate"
prerequisites:
  - "/series/php-for-java-developers/chapters/16-sessions-and-authentication"
---

# Chapter 17: Forms & Validation

<Badge type="warning">Intermediate</Badge>

## Overview

Secure form handling and validation in PHP applications.

**Topics:** Form processing, Input validation, CSRF protection, File uploads, Sanitization

## Section 1: Form Handling

```php
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
}
```

## Section 2: Validation

```php
<?php
$errors = [];
if (empty($name)) {
    $errors['name'] = 'Name is required';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email';
}
```

## Section 3: CSRF Protection

```php
<?php
// Generate token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Verify token
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF validation failed');
}
```

## Section 4: File Upload Validation

```php
<?php
$allowed = ['image/jpeg', 'image/png'];
if (!in_array($_FILES['file']['type'], $allowed)) {
    throw new Exception('Invalid file type');
}
```

## Section 5: Input Sanitization

```php
<?php
$clean = filter_var($input, FILTER_SANITIZE_STRING);
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
$html = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
```

---

<div style="display: flex; justify-content: space-between;">
  <div><strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/16-sessions-and-authentication">← Chapter 16</a></div>
  <div><strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/18-security-best-practices">Chapter 18 →</a></div>
</div>
