# Chapter 11: Error and Exception Handling - Code Examples

Master error and exception handling to build robust, production-ready PHP applications.

## Files

1. **`11-exceptions.php`** - Basic exception handling with try/catch
2. **`11-spl-exceptions.php`** - SPL (Standard PHP Library) exception types
3. **`11-exception-chaining.php`** - Exception chaining and previous exceptions
4. **`11-finally-demo.php`** - Using the finally block
5. **`11-bank-account.php`** - Complete example with custom exceptions

## Quick Start

```bash
php 11-exceptions.php
php 11-spl-exceptions.php
php 11-exception-chaining.php
php 11-finally-demo.php
php 11-bank-account.php
```

## Exception Basics

### Try-Catch-Finally

```php
try {
    // Code that might throw an exception
    $result = riskyOperation();
} catch (Exception $e) {
    // Handle the exception
    echo "Error: " . $e->getMessage();
} finally {
    // Always executes (cleanup code)
    closeResources();
}
```

### Throwing Exceptions

```php
function divide(int $a, int $b): float {
    if ($b === 0) {
        throw new Exception("Cannot divide by zero");
    }
    return $a / $b;
}

try {
    echo divide(10, 0);
} catch (Exception $e) {
    echo "Caught: " . $e->getMessage();
}
```

## Exception Class Hierarchy

```
Throwable (interface)
├── Exception
│   ├── LogicException
│   │   ├── BadFunctionCallException
│   │   ├── BadMethodCallException
│   │   ├── DomainException
│   │   ├── InvalidArgumentException
│   │   ├── LengthException
│   │   └── OutOfRangeException
│   └── RuntimeException
│       ├── OutOfBoundsException
│       ├── OverflowException
│       ├── RangeException
│       ├── UnderflowException
│       └── UnexpectedValueException
└── Error
    ├── ArithmeticError
    ├── ParseError
    ├── TypeError
    └── ...
```

## SPL Exception Types

### LogicException

**Use:** Errors in program logic that should be fixed during development.

```php
// InvalidArgumentException
function setAge(int $age): void {
    if ($age < 0 || $age > 150) {
        throw new InvalidArgumentException("Age must be between 0 and 150");
    }
}

// DomainException
function processOrder(Order $order): void {
    if ($order->getStatus() !== 'pending') {
        throw new DomainException("Can only process pending orders");
    }
}

// LengthException
function setPassword(string $password): void {
    if (strlen($password) < 8) {
        throw new LengthException("Password must be at least 8 characters");
    }
}
```

### RuntimeException

**Use:** Errors that can only be detected at runtime.

```php
// OutOfBoundsException
$arr = [1, 2, 3];
if (!isset($arr[$index])) {
    throw new OutOfBoundsException("Index $index does not exist");
}

// UnexpectedValueException
$data = json_decode($json);
if ($data === null) {
    throw new UnexpectedValueException("Invalid JSON");
}

// OverflowException
if (count($queue) >= $maxSize) {
    throw new OverflowException("Queue is full");
}
```

## Custom Exceptions

```php
class InsufficientFundsException extends Exception {
    public function __construct(
        float $balance,
        float $amount,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $message = "Insufficient funds: balance $balance, attempted $amount";
        parent::__construct($message, $code, $previous);
    }
}

class BankAccount {
    private float $balance = 0;

    public function withdraw(float $amount): void {
        if ($amount > $this->balance) {
            throw new InsufficientFundsException($this->balance, $amount);
        }
        $this->balance -= $amount;
    }
}

try {
    $account = new BankAccount();
    $account->withdraw(100);
} catch (InsufficientFundsException $e) {
    echo $e->getMessage();
}
```

## Multiple Catch Blocks

```php
try {
    processPayment($amount);
} catch (InvalidArgumentException $e) {
    // Handle invalid arguments
    log("Invalid argument: " . $e->getMessage());
} catch (RuntimeException $e) {
    // Handle runtime errors
    log("Runtime error: " . $e->getMessage());
} catch (Exception $e) {
    // Catch all other exceptions
    log("Unknown error: " . $e->getMessage());
}
```

## Catching Multiple Types (PHP 7.1+)

```php
try {
    riskyOperation();
} catch (InvalidArgumentException | DomainException $e) {
    // Handle either type the same way
    echo "Input error: " . $e->getMessage();
}
```

## Exception Chaining

**Preserve original exception context:**

```php
class DatabaseException extends Exception {}

function fetchUser(int $id): array {
    try {
        return $db->query("SELECT * FROM users WHERE id = ?", [$id]);
    } catch (PDOException $e) {
        throw new DatabaseException(
            "Failed to fetch user $id",
            0,
            $e  // Chain the original exception
        );
    }
}

try {
    $user = fetchUser(123);
} catch (DatabaseException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Original: " . $e->getPrevious()->getMessage();
}
```

## Finally Block

**Always executes, even if exception is thrown:**

```php
$file = null;
try {
    $file = fopen('data.txt', 'r');
    $data = fread($file, filesize('data.txt'));
    processData($data);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    // Always close the file
    if ($file !== null) {
        fclose($file);
    }
}
```

## Exception Properties & Methods

```php
try {
    throw new Exception("Something went wrong", 42);
} catch (Exception $e) {
    echo $e->getMessage();     // "Something went wrong"
    echo $e->getCode();        // 42
    echo $e->getFile();        // /path/to/file.php
    echo $e->getLine();        // Line number
    echo $e->getTrace();       // Stack trace array
    echo $e->getTraceAsString(); // Stack trace as string
    echo $e->getPrevious();    // Previous exception (if chained)
    echo $e->__toString();     // Full exception info
}
```

## Error vs Exception

**Error (PHP 7+):** For fatal errors that usually shouldn't be caught.

```php
try {
    // This will throw TypeError in PHP 7+
    function add(int $a, int $b): int {
        return $a + $b;
    }
    add("string", 5);
} catch (TypeError $e) {
    echo "Type error: " . $e->getMessage();
}
```

**Exception:** For exceptional conditions that can be recovered from.

## Global Exception Handler

```php
set_exception_handler(function (Throwable $e) {
    // Log the exception
    error_log($e->getMessage());

    // Show user-friendly message
    if (ini_get('display_errors')) {
        echo "Exception: " . $e->getMessage();
    } else {
        echo "An error occurred. Please try again later.";
    }
});

// Now any uncaught exception will be handled
throw new Exception("This will be caught globally");
```

## Error Handling Best Practices

### 1. Be Specific

✅ **Good:**

```php
throw new InvalidArgumentException("Email must be valid");
```

❌ **Bad:**

```php
throw new Exception("Error");
```

### 2. Create Custom Exceptions

```php
// Good: Domain-specific exceptions
class UserNotFoundException extends Exception {}
class InvalidCredentialsException extends Exception {}
class AccountLockedException extends Exception {}
```

### 3. Don't Catch What You Can't Handle

❌ **Bad:**

```php
try {
    processOrder($order);
} catch (Exception $e) {
    // Do nothing - swallow the exception
}
```

✅ **Good:**

```php
try {
    processOrder($order);
} catch (PaymentException $e) {
    // We know how to handle payment issues
    refundCustomer($order);
    notifyAdmin($e);
}
// Other exceptions bubble up
```

### 4. Log Exceptions

```php
try {
    performCriticalOperation();
} catch (Exception $e) {
    // Always log
    error_log("Critical error: " . $e->getMessage());
    error_log($e->getTraceAsString());

    // Re-throw if needed
    throw $e;
}
```

### 5. Use Finally for Cleanup

```php
$lock = acquireLock();
try {
    processData();
} finally {
    // Always release the lock
    releaseLock($lock);
}
```

## Real-World Example: API Client

```php
class ApiException extends Exception {}
class RateLimitException extends ApiException {}
class AuthenticationException extends ApiException {}

class ApiClient {
    public function request(string $endpoint): array {
        try {
            $response = $this->httpClient->get($endpoint);
        } catch (Exception $e) {
            throw new ApiException("API request failed", 0, $e);
        }

        if ($response->getStatusCode() === 429) {
            throw new RateLimitException("Rate limit exceeded");
        }

        if ($response->getStatusCode() === 401) {
            throw new AuthenticationException("Invalid API key");
        }

        return json_decode($response->getBody(), true);
    }
}

// Usage
try {
    $data = $api->request('/users');
} catch (RateLimitException $e) {
    // Wait and retry
    sleep(60);
    $data = $api->request('/users');
} catch (AuthenticationException $e) {
    // Refresh token
    $api->refreshToken();
} catch (ApiException $e) {
    // Log and show error
    error_log($e->getMessage());
    echo "Service temporarily unavailable";
}
```

## Exception Testing Pattern

```php
function expectException(callable $callback, string $exceptionClass): void {
    try {
        $callback();
        echo "FAIL: Expected $exceptionClass but nothing was thrown\n";
    } catch (Throwable $e) {
        if ($e instanceof $exceptionClass) {
            echo "PASS: Caught expected $exceptionClass\n";
        } else {
            echo "FAIL: Expected $exceptionClass but got " . get_class($e) . "\n";
        }
    }
}

// Test that divide by zero throws
expectException(
    fn() => divide(10, 0),
    DivisionByZeroError::class
);
```

## Production Error Handling

```php
// In production, never show detailed errors to users
if ($_ENV['APP_ENV'] === 'production') {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);

    set_exception_handler(function (Throwable $e) {
        // Log to file/service
        error_log($e->getMessage() . "\n" . $e->getTraceAsString());

        // Show generic message
        http_response_code(500);
        echo "An unexpected error occurred. Please try again.";
    });
}
```

## Common Patterns

### Repository Pattern with Exceptions

```php
interface UserRepositoryInterface {
    /**
     * @throws UserNotFoundException
     */
    public function find(int $id): User;
}
```

### Service Layer

```php
class OrderService {
    public function placeOrder(array $items): Order {
        try {
            $this->validateItems($items);
            $order = $this->createOrder($items);
            $this->processPayment($order);
            return $order;
        } catch (ValidationException $e) {
            throw new OrderException("Invalid order: " . $e->getMessage(), 0, $e);
        } catch (PaymentException $e) {
            $this->cancelOrder($order);
            throw new OrderException("Payment failed: " . $e->getMessage(), 0, $e);
        }
    }
}
```

## Related Chapter

[Chapter 11: Error and Exception Handling](../../chapters/11-error-and-exception-handling.md)

## Further Reading

- [PHP Exceptions](https://www.php.net/manual/en/language.exceptions.php)
- [SPL Exceptions](https://www.php.net/manual/en/spl.exceptions.php)
- [Error Handling Best Practices](https://phptherightway.com/#error_reporting)
