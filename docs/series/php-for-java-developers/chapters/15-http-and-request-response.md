---
title: "15: HTTP & Request/Response"  
description: "Superglobals, PSR-7, headers, middleware pattern"
series: "php-for-java-developers"
chapter: 15
order: 15
difficulty: "Intermediate"
prerequisites:
  - "/series/php-for-java-developers/chapters/14-code-quality-tools"
---

# Chapter 15: HTTP & Request/Response

<Badge type="warning">Intermediate</Badge>

## Overview

Understanding HTTP in PHP is essential for web development. This chapter bridges Java's Servlet API concepts to PHP's HTTP handling, from superglobals to modern PSR-7 standards.

**Topics:** Superglobals, PSR-7 interfaces, Headers, Cookies, File uploads, Middleware, Streams

## Section 1: Superglobals

```php
<?php
// $_GET, $_POST, $_SERVER, $_COOKIE, $_FILES
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$method = $_SERVER['REQUEST_METHOD'];
```

## Section 2: PSR-7 Interfaces

```php
<?php
use Psr\Http\Message\{RequestInterface, ResponseInterface};

class Request implements RequestInterface {
    // Immutable HTTP request
}
```

## Section 3: Headers & Cookies

```php
<?php
header('Content-Type: application/json');
setcookie('session', $id, time() + 3600, '/');
```

## Section 4: File Uploads

```php
<?php
if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
    move_uploaded_file($_FILES['file']['tmp_name'], $destination);
}
```

## Section 5: Middleware Pattern

```php
<?php
class AuthMiddleware {
    public function process($request, $handler) {
        // Authenticate, then pass to next handler
        return $handler->handle($request);
    }
}
```

---

<div style="display: flex; justify-content: space-between;">
  <div><strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/14-code-quality-tools">← Chapter 14</a></div>
  <div><strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/16-sessions-and-authentication">Chapter 16 →</a></div>
</div>
