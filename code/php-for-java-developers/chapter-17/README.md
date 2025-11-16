# Chapter 17: Forms & Validation - Code Examples

This directory contains code examples for Chapter 17: Forms & Validation.

## Files

### Core Classes

- **`Validator.php`** - Reusable validation class with method chaining, similar to Java's validator pattern
- **`CsrfProtection.php`** - CSRF token generation and validation system
- **`FileUploadHandler.php`** - Secure file upload handler with type, size, and signature validation
- **`InputSanitizer.php`** - Input sanitization utilities for safe data handling

### Examples

- **`quick-form.php`** - Minimal working example demonstrating form processing and validation
- **`contact-form-example.php`** - Complete contact form with validation and CSRF protection

## Usage

### Quick Start

1. Start PHP's built-in server:
```bash
cd code/php-for-java-developers/chapter-17
php -S localhost:8000
```

2. Try the quick form example:
```
http://localhost:8000/quick-form.php
```

### Running the Contact Form Example

Open your browser and navigate to:
```
http://localhost:8000/contact-form-example.php
```

### Using the Validator Class

```php
<?php
require_once 'Validator.php';
use App\Validation\Validator;

$validator = new Validator($_POST);
$validator
    ->required('email')
    ->email('email')
    ->min('password', 8);

if ($validator->fails()) {
    $errors = $validator->errors();
}
```

### Using CSRF Protection

```php
<?php
require_once 'CsrfProtection.php';
use App\Security\CsrfProtection;

// In form
<?= CsrfProtection::field() ?>

// On submission
if (!CsrfProtection::verify()) {
    die('CSRF validation failed');
}
```

### Using File Upload Handler

```php
<?php
require_once 'FileUploadHandler.php';
use App\Upload\FileUploadHandler;

$handler = new FileUploadHandler();
$result = $handler->upload($_FILES['avatar'], __DIR__ . '/uploads');
```

## Requirements

- PHP 8.4+
- Sessions enabled (for CSRF protection)
- `fileinfo` extension (for file type validation)
- Write permissions for upload directory

## Security Notes

- Always validate server-side, never trust client-side validation alone
- Use CSRF protection for all state-changing forms
- Validate file uploads using multiple methods (extension, MIME type, file signature)
- Escape output using `htmlspecialchars()` to prevent XSS
- Use prepared statements for database queries (don't rely on sanitization alone)

## Testing

### Quick Form Testing
- Submit empty form (should show "Email is required")
- Enter invalid email like "notanemail" (should show "Invalid email format")
- Enter valid email like "user@example.com" (should show success message)

### Contact Form Testing
- Empty submission (should show validation errors)
- Invalid email format
- Message too short
- Valid submission (should show success message)

### File Upload Testing
To test file uploads, you'll need to create an upload form that uses `FileUploadHandler.php`:
- Valid image files (JPG, PNG, GIF, WEBP)
- Files exceeding size limit (5MB)
- Invalid file types
- Files with spoofed extensions (should be caught by signature validation)

## Comparison to Java

These examples demonstrate PHP equivalents to:
- Java Bean Validation (`@NotNull`, `@Email`) → PHP `Validator` class with method chaining
- Spring Security CSRF → Custom `CsrfProtection` class
- Spring `MultipartFile` → PHP `$_FILES` superglobal with `FileUploadHandler`
- Java's `StringEscapeUtils.escapeHtml()` → PHP's `htmlspecialchars()`

