---
title: "17: Forms & Validation"
description: "Secure form handling, CSRF protection, and comprehensive validation techniques in PHP"
series: "php-for-java-developers"
chapter: 17
order: 17
difficulty: "Intermediate"
prerequisites:
  - "/series/php-for-java-developers/chapters/16-sessions-and-authentication"
---

![Forms & Validation](/images/php-for-java-developers/chapter-17-forms-validation-hero-full.webp)

# Chapter 17: Forms & Validation

<Badge type="warning">Intermediate</Badge> <Badge type="info">90-120 min</Badge>

## Overview

Form handling and validation are fundamental skills for any web developer. In PHP, unlike Java's Spring Framework which provides built-in validation annotations like `@NotNull` and `@Email`, you'll work with PHP's native validation functions and superglobals. However, the principles remain the same: validate input, sanitize output, and protect against common attacks like CSRF and XSS.

If you're coming from Java, you're probably familiar with Spring's `@Valid` annotation and Bean Validation (JSR 303). PHP doesn't have a standard validation framework, but it provides powerful built-in functions like `filter_var()` and `filter_input()` that cover most validation needs. We'll also explore how to build reusable validation classes similar to Java's validator pattern.

In this chapter, you'll learn how to securely handle HTML forms, implement comprehensive validation rules, protect against CSRF attacks, validate file uploads, and sanitize user input. We'll build a complete contact form system with validation, error handling, and security features. By the end, you'll understand how to create production-ready forms that are both secure and user-friendly.

We'll cover everything from basic form processing to advanced topics like custom validation rules, file upload security, and input sanitization. Each concept is demonstrated with practical, runnable code examples that you can adapt for your own projects. The patterns you'll learn here form the foundation for working with modern PHP frameworks like Laravel and Symfony, which build upon these core concepts.

## What You'll Build

In this chapter, you'll create:

- A complete contact form with server-side validation
- A reusable validation class similar to Java's validator pattern
- CSRF protection middleware for form submissions
- Secure file upload handler with type and size validation
- Input sanitization utilities for safe output
- A form builder class for generating HTML forms with validation
- Error handling and user feedback systems
- A fully functional, production-ready form system

## Prerequisites

::: info Time Estimate
⏱️ **90-120 minutes** to complete this chapter
:::

Before starting this chapter, you should be comfortable with:

- PHP sessions and session management (Chapter 16)
- HTTP request/response handling (Chapter 15)
- Basic HTML forms and form elements
- PHP superglobals (`$_POST`, `$_GET`, `$_FILES`)
- Exception handling (Chapter 7)

## Learning Objectives

By the end of this chapter, you will be able to:

1. **Process HTML forms** securely using POST and GET methods
2. **Validate user input** using PHP's built-in validation functions
3. **Build reusable validation classes** similar to Java's validator pattern
4. **Implement CSRF protection** to prevent cross-site request forgery attacks
5. **Validate file uploads** with proper security checks
6. **Sanitize user input** for safe database storage and output
7. **Handle validation errors** gracefully with user-friendly messages
8. **Create secure forms** that protect against common web vulnerabilities
9. **Build form builders** for generating HTML forms programmatically

---

## Quick Start

If you want to see a working form with validation immediately, here's a minimal example:

```php
# filename: quick-form.php
<?php

declare(strict_types=1);

session_start();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    } else {
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Quick Form</title>
    <style>
        body { font-family: sans-serif; max-width: 400px; margin: 50px auto; }
        .error { color: #d32f2f; font-size: 14px; margin-top: 5px; }
        .success { background: #4caf50; color: white; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        input { width: 100%; padding: 8px; margin: 10px 0; }
        button { background: #2196f3; color: white; padding: 10px 20px; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Email Subscription</h1>
    
    <?php if ($success): ?>
        <div class="success">Thank you! Your email has been registered.</div>
    <?php endif; ?>
    
    <form method="POST">
        <input type="email" name="email" placeholder="Enter your email" 
               value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        <?php if (isset($errors['email'])): ?>
            <div class="error"><?= htmlspecialchars($errors['email']) ?></div>
        <?php endif; ?>
        <button type="submit">Subscribe</button>
    </form>
</body>
</html>
```

**To run this:**

```bash
cd code/php-for-java-developers/chapter-17
php -S localhost:8000
# Then visit http://localhost:8000/quick-form.php
```

This demonstrates the core concepts: form processing, validation, error handling, and XSS protection. We'll expand on each of these throughout the chapter.

---

## Section 1: Form Handling Basics

Form handling in PHP is straightforward: data submitted via HTML forms is available in superglobal arrays. Unlike Java's Spring MVC which uses `@ModelAttribute` to bind form data to objects, PHP gives you direct access to raw form data through `$_POST` and `$_GET`.

### Understanding Form Methods

**GET vs POST:**

::: code-group

```php [PHP]
<?php

declare(strict_types=1);

// GET request - data in URL
// URL: form.php?name=John&email=john@example.com
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $name = $_GET['name'] ?? '';
    $email = $_GET['email'] ?? '';
}

// POST request - data in request body
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
}
```

```java [Java Spring]
// Java Spring MVC equivalent
@GetMapping("/form")
public String showForm(@RequestParam(required = false) String name,
                      @RequestParam(required = false) String email,
                      Model model) {
    model.addAttribute("name", name);
    model.addAttribute("email", email);
    return "form";
}

@PostMapping("/form")
public String submitForm(@ModelAttribute FormData formData) {
    String name = formData.getName();
    String email = formData.getEmail();
    // Process form
    return "redirect:/success";
}
```

:::

### Basic Form Processing

Let's create a simple contact form handler:

```php
# filename: contact-form.php
<?php

declare(strict_types=1);

session_start();

$errors = [];
$success = false;
$name = '';
$email = '';
$message = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data with null coalescing operator
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Basic validation
if (empty($name)) {
    $errors['name'] = 'Name is required';
}

    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }

    if (empty($message)) {
        $errors['message'] = 'Message is required';
    } elseif (strlen($message) < 10) {
        $errors['message'] = 'Message must be at least 10 characters';
    }

    // If no errors, process the form
    if (empty($errors)) {
        // In production, save to database or send email
        $success = true;
        
        // Clear form data
        $name = '';
        $email = '';
        $message = '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form</title>
    <style>
        body {
            font-family: sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        .error {
            color: #d32f2f;
            font-size: 14px;
            margin-top: 5px;
        }
        .success {
            background: #4caf50;
            color: white;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        button {
            background: #2196f3;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: #1976d2;
        }
    </style>
</head>
<body>
    <h1>Contact Us</h1>

    <?php if ($success): ?>
        <div class="success">
            Thank you! Your message has been sent successfully.
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="name">Name *</label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
                required
            >
            <?php if (isset($errors['name'])): ?>
                <span class="error"><?= htmlspecialchars($errors['name']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email">Email *</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                required
            >
            <?php if (isset($errors['email'])): ?>
                <span class="error"><?= htmlspecialchars($errors['email']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="message">Message *</label>
            <textarea 
                id="message" 
                name="message" 
                required
            ><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></textarea>
            <?php if (isset($errors['message'])): ?>
                <span class="error"><?= htmlspecialchars($errors['message']) ?></span>
            <?php endif; ?>
        </div>

        <button type="submit">Send Message</button>
    </form>
</body>
</html>
```

### Why It Works

- **`$_SERVER['REQUEST_METHOD']`**: Checks if the request method is POST before processing form data
- **Null coalescing operator (`??`)**: Provides default empty string if form field doesn't exist, preventing undefined index errors
- **`trim()`**: Removes whitespace from the beginning and end of input strings
- **Sticky forms**: The form preserves user input on validation errors using `value="<?= htmlspecialchars(...) ?>"` to prevent XSS attacks
- **`htmlspecialchars()`**: Escapes HTML special characters to prevent XSS when displaying user input

### Troubleshooting

- **"Undefined index" errors**: Always use the null coalescing operator (`??`) when accessing `$_POST` or `$_GET` values
- **Form data not received**: Check that the form's `method` attribute matches what you're checking (`POST` vs `GET`)
- **Special characters breaking HTML**: Always use `htmlspecialchars()` when outputting user input

---

## Section 2: Comprehensive Validation

PHP provides built-in validation functions through the `filter_var()` function, similar to Java's Bean Validation but more function-based. Let's build a reusable validation class similar to Java's validator pattern.

### PHP Validation Functions

PHP's `filter_var()` function supports many validation filters:

```php
<?php

declare(strict_types=1);

// Email validation
$email = 'user@example.com';
if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Valid email";
}

// URL validation
$url = 'https://example.com';
if (filter_var($url, FILTER_VALIDATE_URL)) {
    echo "Valid URL";
}

// Integer validation
$age = '25';
if (filter_var($age, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 120]])) {
    echo "Valid age";
}

// IP address validation
$ip = '192.168.1.1';
if (filter_var($ip, FILTER_VALIDATE_IP)) {
    echo "Valid IP";
}
```

### Building a Validation Class

Let's create a reusable validation class similar to Java's validator pattern:

```php
# filename: Validator.php
<?php

declare(strict_types=1);

namespace App\Validation;

class Validator
{
    private array $errors = [];
    private array $data = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Validate required field
     */
    public function required(string $field, ?string $message = null): self
    {
        $value = $this->getValue($field);
        
        if (empty($value) && $value !== '0') {
            $this->addError($field, $message ?? "The {$field} field is required");
        }

        return $this;
    }

    /**
     * Validate email format
     */
    public function email(string $field, ?string $message = null): self
    {
        $value = $this->getValue($field);
        
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, $message ?? "The {$field} must be a valid email address");
        }

        return $this;
    }

    /**
     * Validate minimum length
     */
    public function min(string $field, int $length, ?string $message = null): self
    {
        $value = $this->getValue($field);
        
        if (!empty($value) && strlen($value) < $length) {
            $this->addError($field, $message ?? "The {$field} must be at least {$length} characters");
        }

        return $this;
    }

    /**
     * Validate maximum length
     */
    public function max(string $field, int $length, ?string $message = null): self
    {
        $value = $this->getValue($field);
        
        if (!empty($value) && strlen($value) > $length) {
            $this->addError($field, $message ?? "The {$field} must not exceed {$length} characters");
        }

        return $this;
    }

    /**
     * Validate numeric value
     */
    public function numeric(string $field, ?string $message = null): self
    {
        $value = $this->getValue($field);
        
        if (!empty($value) && !is_numeric($value)) {
            $this->addError($field, $message ?? "The {$field} must be a number");
        }

        return $this;
    }

    /**
     * Validate integer with range
     */
    public function integer(string $field, ?int $min = null, ?int $max = null, ?string $message = null): self
    {
        $value = $this->getValue($field);
        
        if (!empty($value)) {
            $options = [];
            if ($min !== null) {
                $options['min_range'] = $min;
            }
            if ($max !== null) {
                $options['max_range'] = $max;
            }

            if (!filter_var($value, FILTER_VALIDATE_INT, ['options' => $options])) {
                $range = '';
                if ($min !== null && $max !== null) {
                    $range = " between {$min} and {$max}";
                } elseif ($min !== null) {
                    $range = " at least {$min}";
                } elseif ($max !== null) {
                    $range = " at most {$max}";
                }
                $this->addError($field, $message ?? "The {$field} must be an integer{$range}");
            }
        }

        return $this;
    }

    /**
     * Validate URL format
     */
    public function url(string $field, ?string $message = null): self
    {
        $value = $this->getValue($field);
        
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, $message ?? "The {$field} must be a valid URL");
        }

        return $this;
    }

    /**
     * Validate against regex pattern
     */
    public function regex(string $field, string $pattern, ?string $message = null): self
    {
        $value = $this->getValue($field);
        
        if (!empty($value) && !preg_match($pattern, $value)) {
            $this->addError($field, $message ?? "The {$field} format is invalid");
        }

        return $this;
    }

    /**
     * Validate that two fields match
     */
    public function match(string $field, string $otherField, ?string $message = null): self
    {
        $value = $this->getValue($field);
        $otherValue = $this->getValue($otherField);
        
        if ($value !== $otherValue) {
            $this->addError($field, $message ?? "The {$field} must match {$otherField}");
        }

        return $this;
    }

    /**
     * Check if validation passed
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Check if validation passed
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }

    /**
     * Get all validation errors
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get error for specific field
     */
    public function error(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }

    /**
     * Get validated data
     */
    public function validated(): array
    {
        return $this->data;
    }

    private function getValue(string $field): mixed
    {
        return $this->data[$field] ?? null;
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }
}
```

### Using the Validator Class

Now let's use our validator in a form handler:

```php
# filename: register-form.php
<?php

declare(strict_types=1);

require_once 'Validator.php';

use App\Validation\Validator;

session_start();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $validator = new Validator($_POST);

    $validator
        ->required('username', 'Username is required')
        ->min('username', 3, 'Username must be at least 3 characters')
        ->max('username', 20, 'Username must not exceed 20 characters')
        ->regex('username', '/^[a-zA-Z0-9_]+$/', 'Username can only contain letters, numbers, and underscores')
        
        ->required('email', 'Email is required')
        ->email('email', 'Please enter a valid email address')
        
        ->required('password', 'Password is required')
        ->min('password', 8, 'Password must be at least 8 characters')
        
        ->required('password_confirm', 'Please confirm your password')
        ->match('password_confirm', 'password', 'Passwords do not match')
        
        ->required('age', 'Age is required')
        ->integer('age', 18, 120, 'Age must be between 18 and 120');

    if ($validator->fails()) {
        $errors = $validator->errors();
    } else {
        $validated = $validator->validated();
        // Process registration
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration Form</title>
    <style>
        body { font-family: sans-serif; max-width: 500px; margin: 50px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .error { color: #d32f2f; font-size: 14px; margin-top: 5px; }
        .success { background: #4caf50; color: white; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        button { background: #2196f3; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Register</h1>

    <?php if ($success): ?>
        <div class="success">Registration successful!</div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="username">Username *</label>
            <input type="text" id="username" name="username" 
                   value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            <?php if (isset($errors['username'])): ?>
                <span class="error"><?= htmlspecialchars($errors['username']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" 
                   value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            <?php if (isset($errors['email'])): ?>
                <span class="error"><?= htmlspecialchars($errors['email']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="password">Password *</label>
            <input type="password" id="password" name="password" required>
            <?php if (isset($errors['password'])): ?>
                <span class="error"><?= htmlspecialchars($errors['password']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="password_confirm">Confirm Password *</label>
            <input type="password" id="password_confirm" name="password_confirm" required>
            <?php if (isset($errors['password_confirm'])): ?>
                <span class="error"><?= htmlspecialchars($errors['password_confirm']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="age">Age *</label>
            <input type="number" id="age" name="age" min="18" max="120" 
                   value="<?= htmlspecialchars($_POST['age'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            <?php if (isset($errors['age'])): ?>
                <span class="error"><?= htmlspecialchars($errors['age']) ?></span>
            <?php endif; ?>
        </div>

        <button type="submit">Register</button>
    </form>
</body>
</html>
```

### Comparison with Java Validation

::: code-group

```php [PHP Validator]
<?php

$validator = new Validator($_POST);
$validator
    ->required('email')
    ->email('email')
    ->min('password', 8);

if ($validator->fails()) {
    $errors = $validator->errors();
}
```

```java [Java Bean Validation]
// Java with Bean Validation (JSR 303)
public class UserRegistration {
    @NotNull(message = "Email is required")
    @Email(message = "Invalid email format")
    private String email;

    @NotNull(message = "Password is required")
    @Size(min = 8, message = "Password must be at least 8 characters")
    private String password;
}

// In controller
@PostMapping("/register")
public ResponseEntity<?> register(@Valid @RequestBody UserRegistration registration,
                                  BindingResult result) {
    if (result.hasErrors()) {
        return ResponseEntity.badRequest().body(result.getAllErrors());
    }
    // Process registration
}
```

:::

### Why It Works

- **Fluent interface**: Method chaining allows readable validation rules similar to Java's builder pattern
- **Early return pattern**: Each validation method returns `$this` for chaining
- **Error collection**: Errors are stored in an array and can be retrieved by field name
- **Separation of concerns**: Validation logic is separated from form processing logic

### Troubleshooting

- **Validation not running**: Ensure `$_SERVER['REQUEST_METHOD'] === 'POST'` check is in place
- **Errors not displaying**: Check that error keys match form field names exactly
- **Empty string validation**: Use `empty()` check with `!== '0'` to allow zero values

---

## Section 3: CSRF Protection

Cross-Site Request Forgery (CSRF) attacks trick users into submitting forms on malicious websites that target your application. PHP doesn't have built-in CSRF protection like Spring Security, so we need to implement it ourselves using session tokens.

### Understanding CSRF Attacks

A CSRF attack works like this:

1. User logs into your application (e.g., `bank.com`)
2. User visits a malicious site (`evil.com`)
3. Malicious site contains a form that submits to `bank.com/transfer`
4. Browser sends cookies (including session) automatically
5. Your server processes the request as if it came from the user

### CSRF Token Implementation

Let's create a CSRF protection system:

```php
# filename: CsrfProtection.php
<?php

declare(strict_types=1);

namespace App\Security;

class CsrfProtection
{
    private const TOKEN_NAME = 'csrf_token';
    private const TOKEN_LENGTH = 32;

    /**
     * Generate a CSRF token and store it in session
     */
    public static function generateToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $_SESSION[self::TOKEN_NAME] = $token;

        return $token;
    }

    /**
     * Get current CSRF token from session
     */
    public static function getToken(): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION[self::TOKEN_NAME] ?? null;
    }

    /**
     * Validate CSRF token
     */
    public static function validateToken(string $submittedToken): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $sessionToken = $_SESSION[self::TOKEN_NAME] ?? null;

        if ($sessionToken === null) {
            return false;
        }

        // Use hash_equals to prevent timing attacks
        return hash_equals($sessionToken, $submittedToken);
    }

    /**
     * Generate HTML hidden input field with CSRF token
     */
    public static function field(): string
    {
        $token = self::getToken() ?? self::generateToken();
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            htmlspecialchars(self::TOKEN_NAME, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Verify CSRF token from POST data
     */
    public static function verify(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true; // GET requests don't need CSRF protection
        }

        $submittedToken = $_POST[self::TOKEN_NAME] ?? null;

        if ($submittedToken === null) {
            return false;
        }

        return self::validateToken($submittedToken);
    }

    /**
     * Regenerate CSRF token (useful after successful form submission)
     */
    public static function regenerateToken(): string
    {
        return self::generateToken();
    }
}
```

### Using CSRF Protection

Here's how to use CSRF protection in your forms:

```php
# filename: protected-form.php
<?php

declare(strict_types=1);

require_once 'CsrfProtection.php';

use App\Security\CsrfProtection;

session_start();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token first
    if (!CsrfProtection::verify()) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        // Token is valid, process form
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        // Validation
        if (empty($name)) {
            $errors['name'] = 'Name is required';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Valid email is required';
        }

        if (empty($errors)) {
            // Process form data
            $success = true;
            
            // Regenerate token after successful submission
            CsrfProtection::regenerateToken();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Protected Form</title>
    <style>
        body { font-family: sans-serif; max-width: 500px; margin: 50px auto; padding: 20px; }
        .error { color: #d32f2f; margin-bottom: 15px; padding: 10px; background: #ffebee; border-radius: 4px; }
        .success { background: #4caf50; color: white; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        button { background: #2196f3; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Protected Form</h1>

    <?php if ($success): ?>
        <div class="success">Form submitted successfully!</div>
    <?php endif; ?>

    <?php if (!empty($errors) && is_array($errors)): ?>
        <?php foreach ($errors as $error): ?>
            <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endforeach; ?>
    <?php endif; ?>

    <form method="POST">
        <?= CsrfProtection::field() ?>

        <div class="form-group">
            <label for="name">Name *</label>
            <input type="text" id="name" name="name" 
                   value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            <?php if (isset($errors['name'])): ?>
                <span style="color: #d32f2f; font-size: 14px;"><?= htmlspecialchars($errors['name']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" 
                   value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            <?php if (isset($errors['email'])): ?>
                <span style="color: #d32f2f; font-size: 14px;"><?= htmlspecialchars($errors['email']) ?></span>
            <?php endif; ?>
        </div>

        <button type="submit">Submit</button>
    </form>
</body>
</html>
```

### Comparison with Java Spring Security

::: code-group

```php [PHP CSRF]
<?php

// Generate token
$token = CsrfProtection::generateToken();

// In form
<?= CsrfProtection::field() ?>

// Verify on submission
if (!CsrfProtection::verify()) {
    die('CSRF validation failed');
}
```

```java [Java Spring Security]
// Spring Security automatically handles CSRF
// Just include token in form
<form method="post" action="/submit">
    <input type="hidden" 
           th:name="${_csrf.parameterName}" 
           th:value="${_csrf.token}"/>
    <!-- form fields -->
</form>

// Controller automatically validates
@PostMapping("/submit")
public String submit(@Valid FormData data) {
    // CSRF automatically validated by Spring Security
}
```

:::

### Why It Works

- **`random_bytes()`**: Generates cryptographically secure random bytes
- **`bin2hex()`**: Converts binary data to hexadecimal string for safe HTML embedding
- **`hash_equals()`**: Compares tokens in constant time to prevent timing attacks
- **Session storage**: Token is stored in server-side session, not accessible to JavaScript
- **Token regeneration**: New token generated after successful submission prevents token reuse

### Troubleshooting

- **"Invalid security token" on legitimate submission**: Ensure `session_start()` is called before generating/validating tokens
- **Token validation always fails**: Check that cookies are enabled and sessions are working
- **Token not in form**: Ensure `CsrfProtection::field()` is called inside the `<form>` tag

---

## Section 4: File Upload Validation

File uploads require special security considerations. Unlike Java's Spring which provides `MultipartFile` with built-in validation, PHP requires manual validation of file types, sizes, and content.

### Secure File Upload Handler

```php
# filename: FileUploadHandler.php
<?php

declare(strict_types=1);

namespace App\Upload;

use InvalidArgumentException;
use RuntimeException;

class FileUploadHandler
{
    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * Validate and upload file
     */
    public function upload(array $file, string $uploadDir): array
    {
        // Check for upload errors
        $this->checkUploadError($file['error']);

        // Validate file size
        $this->validateFileSize($file['size']);

        // Validate file type
        $this->validateFileType($file);

        // Generate secure filename
        $filename = $this->generateSecureFilename($file['name']);
        $destination = $uploadDir . DIRECTORY_SEPARATOR . $filename;

        // Create upload directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException('Failed to move uploaded file');
        }

        return [
            'filename' => $filename,
            'path' => $destination,
            'size' => $file['size'],
            'type' => $file['type'],
        ];
    }

    /**
     * Check for PHP upload errors
     */
    private function checkUploadError(int $error): void
    {
        $errorMessages = [
            UPLOAD_ERR_OK => 'No error',
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'PHP extension stopped the file upload',
        ];

        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException($errorMessages[$error] ?? 'Unknown upload error');
        }
    }

    /**
     * Validate file size
     */
    private function validateFileSize(int $size): void
    {
        if ($size === 0) {
            throw new InvalidArgumentException('File is empty');
        }

        if ($size > self::MAX_FILE_SIZE) {
            $maxSizeMB = self::MAX_FILE_SIZE / (1024 * 1024);
            throw new InvalidArgumentException("File size exceeds maximum of {$maxSizeMB}MB");
        }
    }

    /**
     * Validate file type using multiple methods
     */
    private function validateFileType(array $file): void
    {
        // Get file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Validate extension
        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new InvalidArgumentException('File type not allowed');
        }

        // Validate MIME type (can be spoofed, so we also check content)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            throw new InvalidArgumentException('Invalid file type detected');
        }

        // Additional validation: Check file signature (magic bytes)
        $this->validateFileSignature($file['tmp_name'], $extension);
    }

    /**
     * Validate file signature (magic bytes) to prevent spoofing
     */
    private function validateFileSignature(string $filePath, string $extension): void
    {
        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            throw new RuntimeException('Cannot read file for validation');
        }

        $signature = fread($handle, 8);
        fclose($handle);

        $validSignatures = [
            'jpg' => ["\xFF\xD8\xFF"],
            'jpeg' => ["\xFF\xD8\xFF"],
            'png' => ["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"],
            'gif' => ["\x47\x49\x46\x38"],
            'webp' => ["RIFF", "WEBP"],
        ];

        if (!isset($validSignatures[$extension])) {
            throw new InvalidArgumentException('Unknown file extension');
        }

        $isValid = false;
        foreach ($validSignatures[$extension] as $sig) {
            if (strpos($signature, $sig) === 0) {
                $isValid = true;
                break;
            }
        }

        if (!$isValid) {
            throw new InvalidArgumentException('File signature does not match file type');
        }
    }

    /**
     * Generate secure filename to prevent directory traversal
     */
    private function generateSecureFilename(string $originalName): string
    {
        // Remove path information
        $filename = basename($originalName);

        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

        // Add timestamp and random string to prevent overwrites
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $random = bin2hex(random_bytes(8));

        return sprintf('%s_%s_%s.%s', $name, time(), $random, $extension);
    }
}
```

### Using File Upload Handler

```php
# filename: upload-form.php
<?php

declare(strict_types=1);

require_once 'FileUploadHandler.php';
require_once 'CsrfProtection.php';

use App\Upload\FileUploadHandler;
use App\Security\CsrfProtection;

session_start();

$errors = [];
$success = false;
$uploadedFile = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!CsrfProtection::verify()) {
        $errors[] = 'Invalid security token';
    } elseif (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Please select a file to upload';
    } else {
        try {
            $uploadDir = __DIR__ . '/uploads';
            $handler = new FileUploadHandler();
            $uploadedFile = $handler->upload($_FILES['avatar'], $uploadDir);
            $success = true;
            CsrfProtection::regenerateToken();
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>File Upload</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .error { color: #d32f2f; margin-bottom: 15px; padding: 10px; background: #ffebee; border-radius: 4px; }
        .success { background: #4caf50; color: white; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="file"] { width: 100%; padding: 8px; }
        button { background: #2196f3; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; }
        .file-info { margin-top: 15px; padding: 10px; background: #f5f5f5; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>Upload Avatar</h1>

    <?php if ($success && $uploadedFile): ?>
        <div class="success">
            File uploaded successfully!
            <div class="file-info">
                <strong>Filename:</strong> <?= htmlspecialchars($uploadedFile['filename'], ENT_QUOTES, 'UTF-8') ?><br>
                <strong>Size:</strong> <?= number_format($uploadedFile['size'] / 1024, 2) ?> KB<br>
                <strong>Type:</strong> <?= htmlspecialchars($uploadedFile['type'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $error): ?>
            <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endforeach; ?>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <?= CsrfProtection::field() ?>

        <div class="form-group">
            <label for="avatar">Select Image (Max 5MB) *</label>
            <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp" required>
            <small style="color: #666;">Allowed: JPG, PNG, GIF, WEBP</small>
        </div>

        <button type="submit">Upload</button>
    </form>
</body>
</html>
```

### Why It Works

- **Multiple validation layers**: Extension, MIME type, and file signature validation prevent spoofing
- **Secure filename generation**: Prevents directory traversal attacks and filename collisions
- **File signature validation**: Checks magic bytes to ensure file type matches extension
- **Size limits**: Prevents DoS attacks from large file uploads
- **Error handling**: Comprehensive error messages help diagnose upload issues

### Troubleshooting

- **"File exceeds upload_max_filesize"**: Increase `upload_max_filesize` and `post_max_size` in `php.ini`
- **"Failed to move uploaded file"**: Check directory permissions (must be writable)
- **"Invalid file type"**: Verify file signature matches extension (prevents spoofed files)

---

## Section 5: Input Sanitization

Sanitization is the process of cleaning user input to make it safe for storage and display. Unlike validation which checks if data is acceptable, sanitization modifies data to make it safe.

### Sanitization vs Validation

- **Validation**: Checks if data meets requirements (rejects invalid data)
- **Sanitization**: Cleans data to make it safe (modifies data)

### Sanitization Functions

```php
# filename: InputSanitizer.php
<?php

declare(strict_types=1);

namespace App\Sanitization;

class InputSanitizer
{
    /**
     * Sanitize string input (remove tags, encode special chars)
     */
    public static function string(string $input): string
    {
        // Remove HTML tags
        $cleaned = strip_tags($input);
        
        // Trim whitespace
        $cleaned = trim($cleaned);
        
        // Remove null bytes (security risk)
        $cleaned = str_replace("\0", '', $cleaned);
        
        return $cleaned;
    }

    /**
     * Sanitize email (remove invalid characters)
     */
    public static function email(string $input): string
    {
        return filter_var($input, FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitize URL (remove invalid characters)
     */
    public static function url(string $input): string
    {
        return filter_var($input, FILTER_SANITIZE_URL);
    }

    /**
     * Sanitize integer (remove non-numeric characters)
     */
    public static function integer(string $input): int
    {
        return (int) filter_var($input, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitize float (remove non-numeric characters except decimal point)
     */
    public static function float(string $input): float
    {
        return (float) filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Escape HTML for safe output (prevent XSS)
     */
    public static function escapeHtml(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Sanitize for database storage (prevent SQL injection)
     * Note: Use prepared statements instead, this is just for demonstration
     */
    public static function forDatabase(string $input): string
    {
        // Remove SQL injection attempts
        $dangerous = ['--', ';', '/*', '*/', 'xp_', 'sp_'];
        $cleaned = str_replace($dangerous, '', $input);
        
        // Remove null bytes
        $cleaned = str_replace("\0", '', $cleaned);
        
        return $cleaned;
    }

    /**
     * Sanitize filename (remove dangerous characters)
     */
    public static function filename(string $input): string
    {
        // Remove path components
        $filename = basename($input);
        
        // Remove dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        
        // Prevent hidden files
        if (strpos($filename, '.') === 0) {
            $filename = 'file_' . $filename;
        }
        
        return $filename;
    }

    /**
     * Sanitize array of inputs recursively
     */
    public static function array(array $input, callable $sanitizer = null): array
    {
        $sanitizer = $sanitizer ?? [self::class, 'string'];
        
        return array_map(function ($value) use ($sanitizer) {
            if (is_array($value)) {
                return self::array($value, $sanitizer);
            }
            if (is_string($value)) {
                return $sanitizer($value);
            }
            return $value;
        }, $input);
    }
}
```

### Using Sanitization

```php
# filename: sanitization-example.php
<?php

declare(strict_types=1);

require_once 'InputSanitizer.php';

use App\Sanitization\InputSanitizer;

// Example: Sanitizing form input
$userInput = '<script>alert("XSS")</script>Hello World';
$sanitized = InputSanitizer::string($userInput);
echo $sanitized; // Output: Hello World

// Example: Escaping for HTML output
$userComment = '<b>Bold text</b> & "quotes"';
$escaped = InputSanitizer::escapeHtml($userComment);
echo $escaped; // Output: &lt;b&gt;Bold text&lt;/b&gt; &amp; &quot;quotes&quot;

// Example: Sanitizing email
$email = 'user@example.com<script>';
$cleanEmail = InputSanitizer::email($email);
echo $cleanEmail; // Output: user@example.com

// Example: Sanitizing array
$formData = [
    'name' => '<b>John</b>',
    'email' => 'john@example.com',
    'tags' => ['<script>', 'php', '<img>'],
];
$sanitized = InputSanitizer::array($formData, [InputSanitizer::class, 'escapeHtml']);
print_r($sanitized);
```

### Best Practices

1. **Validate first, sanitize second**: Always validate input before sanitizing
2. **Use prepared statements**: Never rely on sanitization alone for SQL injection prevention
3. **Escape on output**: Escape data when displaying, not when storing
4. **Context matters**: Use appropriate sanitization for the context (HTML, SQL, shell, etc.)

### Why It Works

- **`strip_tags()`**: Removes HTML/XML tags from strings
- **`htmlspecialchars()`**: Converts special characters to HTML entities
- **`filter_var()` with sanitize filters**: Removes invalid characters based on data type
- **Recursive sanitization**: Handles nested arrays safely

### Troubleshooting

- **Data still contains HTML**: Ensure you're using `htmlspecialchars()` when outputting, not just when storing
- **Special characters disappearing**: Use appropriate encoding (UTF-8) to preserve international characters
- **Sanitization too aggressive**: Consider the context - some HTML might be acceptable in certain fields

---

## Exercises

### Exercise 1: Enhanced Contact Form

**Goal**: Build a complete contact form with comprehensive validation

Create a contact form with the following fields:
- Name (required, 2-50 characters)
- Email (required, valid email format)
- Phone (optional, valid phone format)
- Subject (required, 1-100 characters)
- Message (required, 10-1000 characters)

Requirements:

- Use the `Validator` class for all validation
- Implement CSRF protection
- Display validation errors next to each field
- Preserve user input on validation errors
- Show success message after successful submission

**Validation**: Test your form with:
- Empty submission (should show all required field errors)
- Invalid email format
- Message too short
- Valid submission (should show success message)

### Exercise 2: File Upload with Multiple Files

**Goal**: Extend the file upload handler to support multiple files

Modify the `FileUploadHandler` to accept multiple files:

```php
<?php

declare(strict_types=1);

// TODO: Implement multiple file upload
// - Accept array of files
// - Validate each file individually
// - Return array of uploaded file info
// - Handle partial failures gracefully

public function uploadMultiple(array $files, string $uploadDir): array
{
    // Your implementation here
}
```

**Validation**: Test with:
- Single file upload
- Multiple files (some valid, some invalid)
- Files exceeding size limit
- Invalid file types

### Exercise 3: Form Builder Class

**Goal**: Create a form builder class for generating HTML forms programmatically

Build a `FormBuilder` class that generates forms with validation:

```php
<?php

declare(strict_types=1);

namespace App\Forms;

class FormBuilder
{
    // TODO: Implement form builder
    // - Method chaining for fluent interface
    // - Generate form fields with labels
    // - Include CSRF token automatically
    // - Add validation error display
    // - Support different input types
    
    public function open(string $action, string $method = 'POST'): self
    {
        // Your implementation
    }
    
    public function text(string $name, string $label, array $attributes = []): self
    {
        // Your implementation
    }
    
    public function email(string $name, string $label, array $attributes = []): self
    {
        // Your implementation
    }
    
    public function submit(string $text = 'Submit'): string
    {
        // Your implementation
    }
    
    public function close(): string
    {
        // Your implementation
    }
}
```

**Usage example:**

```php
$builder = new FormBuilder();
echo $builder
    ->open('/contact', 'POST')
    ->text('name', 'Name', ['required' => true])
    ->email('email', 'Email', ['required' => true])
    ->textarea('message', 'Message', ['required' => true, 'rows' => 5])
    ->submit('Send')
    ->close();
```

**Validation**: Test your implementation:

```php
<?php
// Test form generation
$builder = new FormBuilder();
$html = $builder
    ->open('/contact', 'POST')
    ->text('name', 'Name', ['required' => true])
    ->email('email', 'Email', ['required' => true])
    ->submit('Send')
    ->close();

// Verify HTML contains:
// - Form tag with correct action and method
// - CSRF token field
// - All form fields with labels
// - Submit button
echo $html;
```

---

## Common Mistakes and How to Avoid Them

### ❌ Trusting Client-Side Validation Only

```php
// Bad - Only HTML5 validation
<input type="email" required>

// Good - Server-side validation too
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email';
}
```

### ❌ Not Escaping Output

```php
// Bad - XSS vulnerability
echo $_POST['comment'];

// Good - Escape output
echo htmlspecialchars($_POST['comment'], ENT_QUOTES, 'UTF-8');
```

### ❌ Weak CSRF Protection

```php
// Bad - Simple comparison
if ($_POST['token'] === $_SESSION['token']) {
    // Vulnerable to timing attacks
}

// Good - Use hash_equals
if (hash_equals($_SESSION['token'], $_POST['token'])) {
    // Secure comparison
}
```

### ❌ Trusting File MIME Types

```php
// Bad - Only check MIME type
if ($_FILES['file']['type'] === 'image/jpeg') {
    // Can be spoofed!
}

// Good - Validate file signature too
$signature = file_get_contents($_FILES['file']['tmp_name'], false, null, 0, 4);
if ($signature !== "\xFF\xD8\xFF") {
    throw new Exception('Invalid JPEG file');
}
```

### ❌ Not Validating File Size

```php
// Bad - No size check
move_uploaded_file($_FILES['file']['tmp_name'], $destination);

// Good - Check size
if ($_FILES['file']['size'] > 5 * 1024 * 1024) {
    throw new Exception('File too large');
}
```

---

## Best Practices Summary

✅ **Always validate server-side** - Never trust client-side validation alone
✅ **Use CSRF protection** - Protect all state-changing forms with CSRF tokens
✅ **Validate file uploads** - Check type, size, and content (magic bytes)
✅ **Escape on output** - Use `htmlspecialchars()` when displaying user input
✅ **Use prepared statements** - Never rely on sanitization for SQL injection prevention
✅ **Validate early, sanitize appropriately** - Validate first, then sanitize if needed
✅ **Provide clear error messages** - Help users fix validation errors
✅ **Preserve user input** - Don't make users re-enter data on validation errors
✅ **Use secure random tokens** - Use `random_bytes()` for CSRF tokens
✅ **Check file signatures** - Don't trust file extensions or MIME types alone

---

<ChapterCheckbox 
  seriesId="php-for-java-developers"
  chapterId="17"
  label="Completed forms and validation with CSRF protection and secure file uploads!"
/>

---

## Further Reading

- [PHP: filter_var()](https://www.php.net/manual/en/function.filter-var.php) — PHP's built-in validation functions
- [OWASP: Cross-Site Request Forgery](https://owasp.org/www-community/attacks/crsf) — CSRF attack prevention
- [OWASP: File Upload Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/File_Upload_Cheat_Sheet.html) — Secure file upload practices
- [PHP: File Uploads](https://www.php.net/manual/en/features.file-upload.php) — Official PHP file upload documentation
- [OWASP: XSS Prevention](https://cheatsheetseries.owasp.org/cheatsheets/Cross_Site_Scripting_Prevention_Cheat_Sheet.html) — Cross-site scripting prevention

---

## Chapter Wrap-up Checklist

Before moving to the next chapter, ensure you can:

- [ ] Process HTML forms using `$_POST` and `$_GET` superglobals
- [ ] Validate user input using PHP's `filter_var()` function
- [ ] Build reusable validation classes with method chaining
- [ ] Implement CSRF protection using session tokens
- [ ] Validate file uploads with type, size, and signature checks
- [ ] Sanitize user input for safe storage and display
- [ ] Handle validation errors gracefully with user-friendly messages
- [ ] Create secure forms that protect against common vulnerabilities
- [ ] Understand the difference between validation and sanitization
- [ ] Build production-ready forms with comprehensive security

---

<div style="display: flex; justify-content: space-between; margin-top: 2rem;">
  <div>
    <strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/16-sessions-and-authentication">← Chapter 16</a>
  </div>
  <div><strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/18-security-best-practices">Chapter 18 →</a></div>
</div>
