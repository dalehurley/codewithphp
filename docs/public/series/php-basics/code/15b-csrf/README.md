# Chapter 15b: CSRF Protection & Form Security — Code Examples

Complete examples demonstrating CSRF protection, secure token handling, and form security best practices.

## Files

### `csrf-basics.php`

Foundation of CSRF protection:

- Cryptographically secure token generation
- Timing-attack-safe validation with `hash_equals()`
- Session-based token storage
- Demonstration of security concepts

**Run:**

```bash
php csrf-basics.php
```

### `CsrfProtection.php`

Production-ready CSRF protection class:

- Token generation and validation
- HTML field embedding
- Token regeneration
- Exception-based validation
- Reusable across your entire application

**Usage:**

```php
require_once 'CsrfProtection.php';

session_start();
CsrfProtection::init();

// In your form
echo CsrfProtection::getTokenField();

// Validate submission
if (!CsrfProtection::validate()) {
    die('CSRF validation failed');
}
```

### `protected-form.php`

Complete form example with all security features:

- CSRF protection
- Input validation and sanitization
- XSS prevention
- Security headers
- Error handling
- Token regeneration

**Run:**

```bash
php -S localhost:8000 protected-form.php
```

Then visit `http://localhost:8000`

## Security Testing

### Test 1: Normal Submission (Should Work)

1. Open the form
2. Fill in name and email
3. Click submit
4. ✓ Should show success message

### Test 2: Missing Token (Should Fail)

1. Open browser DevTools (F12)
2. Find and delete the hidden `csrf_token` input
3. Submit the form
4. ❌ Should show security error

### Test 3: Invalid Token (Should Fail)

1. Open DevTools
2. Change the `csrf_token` value to anything else
3. Submit the form
4. ❌ Should show security error

### Test 4: Token Replay (Should Fail)

1. Submit form successfully
2. Use browser back button
3. Submit the same form again
4. ❌ Token has been regenerated, old token is invalid

## Key Concepts Demonstrated

### 1. Token Generation

```php
bin2hex(random_bytes(32))  // 256 bits of entropy
```

### 2. Timing-Safe Comparison

```php
// ✓ Good: Constant time
hash_equals($expected, $actual)

// ❌ Bad: Variable time (timing attack vulnerable)
$expected === $actual
```

### 3. Token Storage

```php
$_SESSION['csrf_token'] = $token;  // Server-side only
```

### 4. Validation Pattern

```php
if (!CsrfProtection::validate()) {
    http_response_code(403);
    die('CSRF validation failed');
}
```

## Common Mistakes to Avoid

### ❌ Don't Use Predictable Tokens

```php
// BAD - easily guessable
$token = md5($user_id . $timestamp);
```

### ❌ Don't Use Regular Comparison

```php
// BAD - vulnerable to timing attacks
if ($_SESSION['token'] === $_POST['token'])
```

### ❌ Don't Store in Cookies Only

```php
// BAD - can be read by JavaScript (XSS)
setcookie('csrf_token', $token);
```

### ❌ Don't Forget to Validate

```php
// BAD - token present but not validated
if (isset($_POST['csrf_token'])) {
    // Process without validation!
}
```

## Security Checklist

Before deploying to production:

- [ ] CSRF tokens on all state-changing operations (POST, PUT, DELETE)
- [ ] Tokens validated using `hash_equals()`
- [ ] Tokens regenerated after login/sensitive actions
- [ ] Session configured with HTTPOnly and Secure flags
- [ ] All user input sanitized with `htmlspecialchars()`
- [ ] Input validated server-side
- [ ] HTTPS enforced in production
- [ ] Security headers set (X-Frame-Options, CSP, etc.)

## Integration with Frameworks

This pattern works with any PHP framework:

**Laravel:**

```php
// Built-in: @csrf directive
<form method="POST">
    @csrf
    ...
</form>
```

**Symfony:**

```php
// Built-in: csrf_token() function
<input type="hidden" name="_token" value="{{ csrf_token('form_name') }}">
```

**Plain PHP:**

```php
// Use our CsrfProtection class!
<?= CsrfProtection::getTokenField() ?>
```

## Further Reading

- [OWASP CSRF Prevention Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html)
- [PHP random_bytes() Documentation](https://www.php.net/manual/en/function.random-bytes.php)
- [PHP hash_equals() Documentation](https://www.php.net/manual/en/function.hash-equals.php)
- [Session Security Best Practices](https://www.php.net/manual/en/session.security.php)

## Related Chapters

- [Chapter 05: Handling HTML Forms and User Input](/series/php-basics/chapters/05-handling-html-forms-and-user-input)
- [Chapter 15: Managing State with Sessions and Cookies](/series/php-basics/chapters/15-managing-state-with-sessions-and-cookies)
- [Chapter 16: Writing Better Code with PSR-1 and PSR-12](/series/php-basics/chapters/16-writing-better-code-with-psr-1-and-psr-12)
