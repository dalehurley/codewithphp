# Chapter 12: REST APIs - Express.js vs PHP Native

Code examples for building REST APIs in PHP, comparing native PHP to Slim Framework.

## Prerequisites

- PHP 8.1+
- Composer

## Installation

```bash
composer install
```

## Examples

### 1. Basic Native PHP API (`01-native-api.php`)
Simple REST API without frameworks.

```bash
php -S localhost:8000 01-native-api.php
curl http://localhost:8000/api/users
```

### 2. Router Class (`02-router-class.php`)
Custom router implementation.

```bash
php -S localhost:8000 02-router-class.php
curl http://localhost:8000/api/users
```

### 3. Request/Response Objects (`03-request-response.php`)
Clean request and response handling.

```bash
php -S localhost:8000 03-request-response.php
curl -X POST http://localhost:8000/api/users -H "Content-Type: application/json" -d '{"name":"Alice"}'
```

### 4. Middleware Pipeline (`04-middleware.php`)
Middleware pattern implementation.

```bash
php -S localhost:8000 04-middleware.php
curl http://localhost:8000/api/protected
```

### 5. Slim Framework API (`05-slim-api/`)
Full CRUD API with Slim Framework.

```bash
cd 05-slim-api
composer install
php -S localhost:8000 -t public/
curl http://localhost:8000/api/users
```

### 6. Validation Example (`06-validation.php`)
Input validation with respect/validation.

```bash
php -S localhost:8000 06-validation.php
curl -X POST http://localhost:8000/api/users -H "Content-Type: application/json" -d '{"name":"A","email":"invalid"}'
```

## Key Concepts

- **Routing**: Map HTTP methods + paths to handlers
- **Middleware**: Request/response pipeline
- **Validation**: Input data validation
- **Error Handling**: Consistent error responses
- **CORS**: Cross-origin resource sharing
- **RESTful Design**: Resource-based endpoints

## RESTful Best Practices

✅ **Use HTTP methods correctly:**
- GET: Retrieve resources
- POST: Create resources
- PUT/PATCH: Update resources
- DELETE: Delete resources

✅ **Use proper status codes:**
- 200: OK
- 201: Created
- 204: No Content
- 400: Bad Request
- 401: Unauthorized
- 404: Not Found
- 500: Server Error

✅ **Resource naming:**
- `/api/users` (collection)
- `/api/users/1` (specific resource)
- Use nouns, not verbs

## Testing APIs

```bash
# GET request
curl http://localhost:8000/api/users

# POST request
curl -X POST http://localhost:8000/api/users \
  -H "Content-Type: application/json" \
  -d '{"name":"Alice","email":"alice@example.com"}'

# PUT request
curl -X PUT http://localhost:8000/api/users/1 \
  -H "Content-Type: application/json" \
  -d '{"name":"Alice Updated"}'

# DELETE request
curl -X DELETE http://localhost:8000/api/users/1
```

## Resources

- [Slim Framework](https://www.slimframework.com/)
- [PSR-7: HTTP Messages](https://www.php-fig.org/psr/psr-7/)
- [REST API Best Practices](https://restfulapi.net/)
