# Chapter 05: Handling HTML Forms and User Input - Code Examples

This directory contains comprehensive examples demonstrating how to safely process HTML forms and handle user input in PHP.

## Files Overview

### 1. `basic-form.php`

Introduction to form processing fundamentals.

**What it demonstrates:**

- Creating an HTML form with POST method
- Checking request method (`$_SERVER['REQUEST_METHOD']`)
- Accessing POST data with `$_POST` superglobal
- Using `htmlspecialchars()` for output sanitization
- Self-submitting forms (form action points to same file)

**Run it:**

```bash
php -S localhost:8000
# Visit: http://localhost:8000/basic-form.php
```

**Key Takeaways:**

- Always check the request method before processing
- Use `$_POST` for form data sent via POST
- Always sanitize output with `htmlspecialchars()`
- Forms should submit to themselves for simplicity

### 2. `validation-example.php`

Production-ready form with comprehensive validation.

**What it demonstrates:**

- Server-side validation (never trust client-side only)
- Form repopulation (keeping values after submission)
- Error handling and display
- Multiple validation rules per field
- Success message display
- Form reset after successful submission

**Run it:**

```bash
php -S localhost:8000
# Visit: http://localhost:8000/validation-example.php
```

**Key Takeaways:**

- Always validate on the server (client validation can be bypassed)
- Repopulate form fields with submitted values
- Display specific error messages per field
- Use `filter_var()` for email and URL validation
- Reset form after successful submission

### 3. `get-vs-post.php`

Understanding GET and POST methods.

**What it demonstrates:**

- Difference between GET and POST
- When to use each method
- Accessing data with `$_GET` vs `$_POST`
- URL query strings with GET
- Security implications of each method
- Current request information

**Run it:**

```bash
php -S localhost:8000
# Visit: http://localhost:8000/get-vs-post.php
```

**Key Takeaways:**

- GET: Data visible in URL, for searches and filters
- POST: Data hidden, for sensitive information and state changes
- GET requests should be idempotent (safe to repeat)
- POST for creating, updating, or deleting data
- Use `$_SERVER['REQUEST_METHOD']` to check method

### 4. `sanitization-demo.php`

Critical security demonstration of input sanitization.

**What it demonstrates:**

- Why sanitization is critical (XSS prevention)
- Using `htmlspecialchars()` properly
- Common XSS attack vectors
- Before/after sanitization examples
- Different sanitization functions for different contexts
- Security best practices checklist

**Run it:**

```bash
php -S localhost:8000
# Visit: http://localhost:8000/sanitization-demo.php
```

**Key Takeaways:**

- **ALWAYS** use `htmlspecialchars()` for HTML output
- Use `ENT_QUOTES` and `UTF-8` parameters
- Use `strip_tags()` to remove HTML completely
- Use `filter_var()` for validation
- Different contexts need different sanitization

### 5. `comprehensive-form.php`

Complete example with all input types.

**What it demonstrates:**

- Text, email, number inputs
- Select dropdowns
- Radio buttons (single selection)
- Checkboxes (multiple selection)
- Single checkbox (boolean)
- Textarea for long text
- Form repopulation for all input types
- Validation for all field types

**Run it:**

```bash
php -S localhost:8000
# Visit: http://localhost:8000/comprehensive-form.php
```

**Key Takeaways:**

- Different input types require different handling
- Radio buttons: Use same `name`, different `value`
- Checkboxes: Use `name[]` for arrays
- Select dropdowns: Use `selected` attribute
- Single checkbox: Check with `isset()`
- Repopulate all fields after failed validation

## Exercise Solutions

### Exercise 1: Contact Form

**File:** `solutions/exercise-1-contact-form.php`

Build a complete contact form with validation.

**Requirements:**

- Name, email, subject, and message fields
- Validate all fields (required, format, length)
- Display error messages
- Show success message after submission
- Repopulate form on errors

**Run it:**

```bash
php -S localhost:8000
# Visit: http://localhost:8000/solutions/exercise-1-contact-form.php
```

**What you'll learn:**

- Complete form validation workflow
- Multiple validation rules per field
- Error and success message handling
- Professional form UX patterns

### Exercise 2: Calculator Form

**File:** `solutions/exercise-2-calculator.php`

Create a calculator with two numbers and operation selection.

**Requirements:**

- Two number inputs
- Dropdown for operation selection (+, -, ×, ÷)
- Calculate and display result
- Handle division by zero
- Validate numeric inputs

**Run it:**

```bash
php -S localhost:8000
# Visit: http://localhost:8000/solutions/exercise-2-calculator.php
```

**What you'll learn:**

- Working with numeric inputs
- Select dropdown handling
- Using `match` expressions for operations
- Error handling for edge cases

## Quick Reference

### Request Methods

```php
// Check request method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process POST data
}

// Access GET data
$search = $_GET['search'] ?? '';

// Access POST data
$username = $_POST['username'] ?? '';
```

### Sanitization

```php
// For HTML output - ALWAYS USE THIS
$safe = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');

// Short syntax in templates
<?= htmlspecialchars($variable) ?>

// Remove all HTML tags
$clean = strip_tags($input);

// For URLs
$safe = urlencode($input);
```

### Validation

```php
// Email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email';
}

// URL validation
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    $errors[] = 'Invalid URL';
}

// Number validation
if (!is_numeric($value)) {
    $errors[] = 'Must be a number';
}

// Required field
if (empty($name)) {
    $errors[] = 'Name is required';
}

// String length
if (strlen($name) < 2 || strlen($name) > 50) {
    $errors[] = 'Name must be 2-50 characters';
}
```

### Form Repopulation

```php
<!-- Text input -->
<input type="text" name="name" value="<?= htmlspecialchars($name) ?>">

<!-- Textarea -->
<textarea name="bio"><?= htmlspecialchars($bio) ?></textarea>

<!-- Select dropdown -->
<select name="country">
    <option value="US" <?= $country === 'US' ? 'selected' : '' ?>>USA</option>
</select>

<!-- Radio button -->
<input type="radio" name="gender" value="male" <?= $gender === 'male' ? 'checked' : '' ?>>

<!-- Checkbox -->
<input type="checkbox" name="newsletter" <?= $newsletter ? 'checked' : '' ?>>

<!-- Checkbox array -->
<input type="checkbox" name="interests[]" value="coding" <?= in_array('coding', $interests) ? 'checked' : '' ?>>
```

## Best Practices

### 1. Always Validate Server-Side

```php
// ✗ BAD - Relying only on HTML5 validation
<input type="email" required>

// ✓ GOOD - Server-side validation
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email';
}
```

### 2. Always Sanitize Output

```php
// ✗ BAD - Direct output (XSS vulnerability)
echo $_POST['name'];

// ✓ GOOD - Sanitized output
echo htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
```

### 3. Use Null Coalescing Operator

```php
// ✗ BAD - Verbose and error-prone
$name = isset($_POST['name']) ? $_POST['name'] : '';

// ✓ GOOD - Clean and safe
$name = $_POST['name'] ?? '';
```

### 4. Validate Then Sanitize

```php
// ✓ GOOD - Validate first, sanitize for output
$email = trim($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email';
}

// When displaying
echo htmlspecialchars($email);
```

### 5. Provide Specific Error Messages

```php
// ✗ BAD - Generic error
$errors[] = 'Invalid input';

// ✓ GOOD - Specific, actionable error
$errors['email'] = 'Please enter a valid email address (e.g., user@example.com)';
```

### 6. Post/Redirect/Get Pattern

```php
// After successful form processing, redirect
if (empty($errors)) {
    // Process form (save to database, etc.)

    // Redirect to prevent resubmission
    header('Location: success.php');
    exit;
}
```

## Common Patterns

### Form Validation Pattern

```php
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data
    $name = trim($_POST['name'] ?? '');

    // Validate
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    }

    // If valid, process
    if (empty($errors)) {
        // Save to database, send email, etc.
        $success = true;
    }
}
```

### Multi-Step Form Pattern

```php
session_start();

$step = $_GET['step'] ?? 1;

if ($step === 1 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['step1_data'] = $_POST;
    header('Location: form.php?step=2');
    exit;
}

if ($step === 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $allData = array_merge($_SESSION['step1_data'], $_POST);
    // Process complete form
}
```

### File Upload Pattern (Preview for Chapter 13)

```php
if (isset($_FILES['upload'])) {
    $file = $_FILES['upload'];

    // Validate
    if ($file['error'] === UPLOAD_ERR_OK) {
        // Check file size, type, etc.
        move_uploaded_file($file['tmp_name'], 'uploads/' . $file['name']);
    }
}
```

## Security Checklist

- ✓ Always validate on server-side
- ✓ Always sanitize output with `htmlspecialchars()`
- ✓ Use prepared statements for database queries (Chapter 14)
- ✓ Implement CSRF tokens (Chapter 15)
- ✓ Validate file uploads (type, size, content)
- ✓ Use HTTPS in production
- ✓ Set secure session cookies
- ✓ Implement rate limiting for forms
- ✓ Log suspicious activity
- ✓ Keep PHP and dependencies updated

## Common Mistakes to Avoid

### 1. Not Checking Request Method

```php
// ✗ BAD - Processes on every request
$name = $_POST['name'];

// ✓ GOOD - Only processes POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
}
```

### 2. Forgetting to Sanitize

```php
// ✗ BAD - XSS vulnerability
<h1>Welcome <?= $_POST['name'] ?></h1>

// ✓ GOOD - Safe output
<h1>Welcome <?= htmlspecialchars($_POST['name'] ?? '') ?></h1>
```

### 3. Not Repopulating Forms

```php
// ✗ BAD - User has to re-enter everything
<input type="text" name="email">

// ✓ GOOD - Keeps user's input
<input type="text" name="email" value="<?= htmlspecialchars($email) ?>">
```

### 4. Weak Validation

```php
// ✗ BAD - Accepts any non-empty string
if (!empty($_POST['email'])) { }

// ✓ GOOD - Validates format
if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}
```

## Next Steps

Once you master form handling, you're ready for:

- **Chapter 06:** Deep Dive into Arrays (for processing complex form data)
- **Chapter 14:** Interacting with Databases (saving form data)
- **Chapter 15:** Managing State with Sessions (multi-step forms, login)

## Related Chapter

[Chapter 05: Handling HTML Forms and User Input](../../chapters/05-handling-html-forms-and-user-input.md)

## Further Reading

- [PHP Manual: Handling Forms](https://www.php.net/manual/en/tutorial.forms.php)
- [PHP Manual: Filter Functions](https://www.php.net/manual/en/book.filter.php)
- [OWASP: Cross-Site Scripting (XSS)](https://owasp.org/www-community/attacks/xss/)
- [PHP Manual: htmlspecialchars](https://www.php.net/manual/en/function.htmlspecialchars.php)
