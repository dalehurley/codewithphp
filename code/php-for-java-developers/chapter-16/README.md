# Chapter 16: Sessions & Authentication - Code Examples

This directory contains the code examples from Chapter 16, demonstrating session management, authentication, and security best practices in PHP.

## Files

- `SecureSession.php` - Secure session management class with proper configuration
- `PasswordHasher.php` - Password hashing and verification utility
- `Authenticator.php` - Complete authentication system with login/logout
- `JWT.php` - JWT (JSON Web Token) implementation for stateless authentication
- `JWTAuthMiddleware.php` - Middleware for JWT authentication in APIs
- `CSRFProtection.php` - CSRF token generation and validation
- `OAuth2Client.php` - OAuth 2.0 client for third-party authentication
- `DatabaseSessionHandler.php` - Database-backed session storage for scalability
- `FlashMessages.php` - One-time session messages for post-redirect notifications
- `SessionTimeout.php` - Idle session timeout handling
- `JWTRefreshToken.php` - Refresh token implementation for JWT
- `example-usage.php` - Demonstration of how to use the classes

## Running the Examples

```bash
# Run the example usage file
php example-usage.php

# Run individual classes (they require autoloading or manual includes)
php -r "require 'SecureSession.php'; require 'PasswordHasher.php'; ..."
```

## Prerequisites

- PHP 8.4 or higher
- Understanding of HTTP and cookies
- Basic knowledge of security concepts (hashing, encryption)

## Key Concepts Demonstrated

### 1. Secure Session Management

The `SecureSession` class demonstrates:
- Proper session configuration (httponly, secure, samesite)
- Session ID regeneration to prevent fixation attacks
- Type-safe session value access

### 2. Password Security

The `PasswordHasher` class shows:
- Modern password hashing with Argon2ID
- Password strength validation
- Secure password verification

### 3. JWT Authentication

The `JWT` class implements:
- Token encoding and decoding
- Signature verification
- Expiration handling

### 4. CSRF Protection

The `CSRFProtection` class provides:
- Token generation and validation
- HTML form field generation
- Constant-time comparison to prevent timing attacks

### 5. OAuth 2.0 Integration

The `OAuth2Client` class handles:
- Authorization URL generation
- Token exchange
- User information retrieval

### 6. Database Session Storage

The `DatabaseSessionHandler` class provides:
- Database-backed session storage
- Scalable session management for load-balanced environments
- Automatic garbage collection
- Better monitoring and security options

### 7. Flash Messages

The `FlashMessages` class provides:
- One-time messages stored in session
- Automatic removal after display
- HTML rendering support
- Perfect for post-redirect-get patterns

### 8. Session Timeout

The `SessionTimeout` class provides:
- Idle session detection
- Automatic logout on timeout
- Configurable timeout duration
- Activity tracking

### 9. JWT Refresh Tokens

The `JWTRefreshToken` class provides:
- Long-lived refresh tokens
- Access token renewal
- Token type validation
- Secure token refresh flow

## Security Best Practices

All examples follow security best practices:

- ✅ Secure session configuration
- ✅ Password hashing with modern algorithms
- ✅ CSRF protection for forms
- ✅ JWT token expiration
- ✅ Constant-time string comparison
- ✅ Input validation
- ✅ SQL injection prevention (prepared statements)

## Notes

- The `Authenticator` class requires a database with a `users` table
- The `DatabaseSessionHandler` requires a `sessions` table (see schema in chapter)
- OAuth examples require actual OAuth provider credentials
- Session examples work best in a web server context
- JWT examples can be tested via command line or API calls

## Comparison to Java

These examples demonstrate PHP equivalents to:
- Java's `HttpSession` → PHP's `$_SESSION` superglobal
- Spring Security's `BCryptPasswordEncoder` → PHP's `password_hash()`
- Java JWT libraries (jjwt) → Custom PHP JWT implementation
- Spring Security CSRF → Custom PHP CSRF protection

