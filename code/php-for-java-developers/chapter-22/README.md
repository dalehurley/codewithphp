# Chapter 22: Micro-frameworks (Slim) - Code Examples

This directory contains code examples for building REST APIs with the Slim micro-framework.

## Structure

```
chapter-22/
├── public/
│   ├── index.php          # Main application entry point
│   └── .htaccess          # Apache rewrite rules
├── src/
│   ├── Controllers/       # Route controllers
│   │   └── UserController.php
│   ├── Middleware/        # PSR-15 middleware
│   │   ├── AuthMiddleware.php
│   │   ├── CorsMiddleware.php
│   │   ├── LoggingMiddleware.php
│   │   └── ErrorHandler.php
│   ├── Services/          # Business logic services
│   │   └── UserService.php
│   └── Exceptions/        # Custom exceptions
│       └── ValidationException.php
└── config/
    └── container.php      # Dependency injection container config
```

## Setup

1. **Install dependencies:**

```bash
composer require slim/slim:"^4.12" slim/psr7
composer require php-di/php-di:"^7.0" php-di/slim-bridge:"^1.0"
```

2. **Run the development server:**

```bash
php -S localhost:8000 -t public
```

3. **Test the API:**

```bash
# Get all users
curl http://localhost:8000/users

# Get specific user
curl http://localhost:8000/users/1

# Create user
curl -X POST http://localhost:8000/users \
  -H "Content-Type: application/json" \
  -d '{"name":"Charlie"}'

# Protected route (requires auth token)
curl http://localhost:8000/protected \
  -H "Authorization: Bearer valid-token-123"
```

## Examples

### Basic Routing

The `public/index.php` file demonstrates:
- Basic route definitions
- Route parameters
- HTTP method handlers (GET, POST, PUT, DELETE)
- Route groups for API versioning

### Middleware

Middleware examples include:
- **AuthMiddleware**: JWT token validation (simplified)
- **CorsMiddleware**: CORS headers for cross-origin requests
- **LoggingMiddleware**: Request/response logging
- **ErrorHandler**: Custom error responses

### Dependency Injection

The `config/container.php` shows how to configure PHP-DI for dependency injection. Controllers receive services through constructor injection.

## Exercises

### Exercise 1: Task API

Create a complete REST API for task management:
- `TaskService` with CRUD operations
- `TaskController` with route handlers
- Validation for required fields
- Proper HTTP status codes

### Exercise 2: JWT Authentication

Implement real JWT authentication:
- Install `firebase/php-jwt`
- Create login endpoint that generates tokens
- Update `AuthMiddleware` to validate JWT tokens
- Extract user information from token payload

## Notes

- This is a simplified example for learning purposes
- In production, use proper database connections, validation libraries, and security practices
- Error handling should be more comprehensive
- Consider using environment variables for configuration
- Add proper logging and monitoring in production applications

## Resources

- [Slim Framework Documentation](https://www.slimframework.com/docs/v4/)
- [PSR-7 HTTP Message Interfaces](https://www.php-fig.org/psr/psr-7/)
- [PSR-15 HTTP Server Handlers](https://www.php-fig.org/psr/psr-15/)
- [PHP-DI Documentation](https://php-di.org/doc/)





