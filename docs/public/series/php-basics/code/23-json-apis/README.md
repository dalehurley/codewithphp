# Chapter 23: Working with JSON & APIs — Code Examples

Complete, working examples for Chapter 23 demonstrating JSON handling and API integration in PHP 8.4.

## Files

### `json-basics.php`

Comprehensive JSON encoding and decoding examples:

- Compact and pretty-printed JSON
- Unicode and slash handling
- Decoding to arrays vs objects
- Error handling with `json_last_error_msg()`
- Using `JSON_THROW_ON_ERROR` for exceptions
- Complex nested data structures

**Run:**

```bash
php json-basics.php
```

### `api-client-curl.php`

Real-world API client using cURL:

- GET requests to GitHub API
- POST requests to test APIs
- Complete error handling (cURL, HTTP, JSON)
- URL encoding and query parameters
- Repository search functionality

**Run:**

```bash
php api-client-curl.php
```

### `simple-rest-api.php`

Complete REST API server:

- Full CRUD operations (Create, Read, Update, Delete)
- Proper HTTP status codes (200, 201, 404, 400)
- Request body parsing
- Input validation
- CORS headers for development

**Run:**

```bash
# Start the server
php -S localhost:8000 simple-rest-api.php

# In another terminal, test the endpoints:

# List all products
curl http://localhost:8000/products

# Get single product
curl http://localhost:8000/products/1

# Create product
curl -X POST http://localhost:8000/products \
  -H "Content-Type: application/json" \
  -d '{"name":"Webcam HD","price":89.99,"stock":20}'

# Update product
curl -X PUT http://localhost:8000/products/1 \
  -H "Content-Type: application/json" \
  -d '{"name":"Updated Laptop","price":1399.99,"stock":10}'

# Delete product
curl -X DELETE http://localhost:8000/products/1
```

## Prerequisites

- PHP 8.4 with cURL extension enabled
- Internet connection for external API calls
- Terminal/command line access

## Common Tasks

### Check if cURL is Enabled

```bash
php -m | grep curl
```

### Test JSON Functions

```bash
php -r "echo json_encode(['test' => 'data']);"
```

### Pretty Print JSON from Command Line

```bash
php -r "echo json_encode(['a'=>1,'b'=>2], JSON_PRETTY_PRINT);"
```

## Tips

1. **Rate Limits**: GitHub API allows 60 requests/hour without authentication
2. **User Agent**: Always set a User-Agent header when calling APIs
3. **Error Handling**: Always check both cURL errors and HTTP status codes
4. **JSON Validation**: Use `JSON_THROW_ON_ERROR` for cleaner exception-based error handling
5. **Timeouts**: Set reasonable timeouts (10-30 seconds) to prevent hanging

## Further Exploration

Try modifying the examples to:

- Add caching to reduce API calls
- Implement OAuth authentication
- Add pagination support
- Build a wrapper class for your favorite API
- Add request/response logging
- Implement retry logic for failed requests

## Related Chapters

- [Chapter 11: Error and Exception Handling](/series/php-basics/chapters/11-error-and-exception-handling)
- [Chapter 14: Interacting with Databases using PDO](/series/php-basics/chapters/14-interacting-with-databases-using-pdo)
- [Chapter 20: A Gentle Introduction to Laravel](/series/php-basics/chapters/20-a-gentle-introduction-to-laravel)
