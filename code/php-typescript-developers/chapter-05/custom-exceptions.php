<?php
declare(strict_types=1);

/**
 * Custom Exception Classes
 * Demonstrates creating domain-specific exceptions with context
 */

echo "=== Basic Custom Exceptions ===" . PHP_EOL . PHP_EOL;

// Base exception for HTTP-related errors
class HttpException extends Exception {
    public function __construct(
        private int $statusCode,
        string $message = "",
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }

    public function toArray(): array {
        return [
            'error' => true,
            'status' => $this->statusCode,
            'message' => $this->getMessage(),
            'code' => $this->getCode()
        ];
    }
}

// Specific HTTP exceptions
class NotFoundException extends HttpException {
    public function __construct(string $resource = "Resource") {
        parent::__construct(404, "{$resource} not found");
    }
}

class UnauthorizedException extends HttpException {
    public function __construct(string $message = "Unauthorized") {
        parent::__construct(401, $message);
    }
}

class ValidationException extends HttpException {
    public function __construct(
        private array $errors,
        string $message = "Validation failed"
    ) {
        parent::__construct(422, $message);
    }

    public function getErrors(): array {
        return $this->errors;
    }

    public function toArray(): array {
        return [
            'error' => true,
            'status' => $this->getStatusCode(),
            'message' => $this->getMessage(),
            'errors' => $this->errors
        ];
    }
}

// Test HTTP exceptions
try {
    throw new NotFoundException("User");
} catch (HttpException $e) {
    echo "HTTP Exception:" . PHP_EOL;
    echo json_encode($e->toArray(), JSON_PRETTY_PRINT) . PHP_EOL;
}

echo PHP_EOL;

try {
    throw new ValidationException([
        'email' => 'Invalid email format',
        'age' => 'Must be 18 or older'
    ]);
} catch (ValidationException $e) {
    echo "Validation Exception:" . PHP_EOL;
    echo json_encode($e->toArray(), JSON_PRETTY_PRINT) . PHP_EOL;
}

// Domain-specific exceptions
echo PHP_EOL . "=== Domain-Specific Exceptions ===" . PHP_EOL . PHP_EOL;

class OrderException extends Exception {}
class InsufficientStockException extends OrderException {}
class PaymentFailedException extends OrderException {}

class Order {
    public function __construct(
        private int $quantity,
        private int $stock
    ) {}

    public function process(): void {
        if ($this->quantity > $this->stock) {
            throw new InsufficientStockException(
                "Requested {$this->quantity} items, only {$this->stock} available"
            );
        }

        // Simulate payment failure
        if (random_int(0, 1) === 0) {
            throw new PaymentFailedException("Payment gateway rejected the transaction");
        }

        echo "Order processed successfully" . PHP_EOL;
    }
}

try {
    $order = new Order(10, 5);
    $order->process();
} catch (InsufficientStockException $e) {
    echo "Stock Error: {$e->getMessage()}" . PHP_EOL;
} catch (PaymentFailedException $e) {
    echo "Payment Error: {$e->getMessage()}" . PHP_EOL;
} catch (OrderException $e) {
    echo "Order Error: {$e->getMessage()}" . PHP_EOL;
}

// Exception with detailed context
echo PHP_EOL . "=== Exception with Context ===" . PHP_EOL . PHP_EOL;

class DatabaseException extends Exception {
    public function __construct(
        string $message,
        private string $query = '',
        private array $bindings = [],
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getQuery(): string {
        return $this->query;
    }

    public function getBindings(): array {
        return $this->bindings;
    }

    public function getContext(): array {
        return [
            'message' => $this->getMessage(),
            'query' => $this->query,
            'bindings' => $this->bindings,
            'file' => $this->getFile(),
            'line' => $this->getLine()
        ];
    }
}

try {
    throw new DatabaseException(
        "Duplicate entry violation",
        "INSERT INTO users (email) VALUES (?)",
        ['user@example.com']
    );
} catch (DatabaseException $e) {
    echo "Database Error:" . PHP_EOL;
    echo json_encode($e->getContext(), JSON_PRETTY_PRINT) . PHP_EOL;
}

// Exception factory pattern
echo PHP_EOL . "=== Exception Factory ===" . PHP_EOL . PHP_EOL;

class UserException extends Exception {
    public static function notFound(int $id): self {
        return new self("User with ID {$id} not found");
    }

    public static function invalidCredentials(): self {
        return new self("Invalid username or password");
    }

    public static function emailAlreadyExists(string $email): self {
        return new self("Email '{$email}' is already registered");
    }

    public static function accountLocked(int $userId): self {
        return new self("User account {$userId} is locked");
    }
}

try {
    throw UserException::notFound(123);
} catch (UserException $e) {
    echo "User Error: {$e->getMessage()}" . PHP_EOL;
}

try {
    throw UserException::emailAlreadyExists("test@example.com");
} catch (UserException $e) {
    echo "User Error: {$e->getMessage()}" . PHP_EOL;
}

// Practical: API response exceptions
echo PHP_EOL . "=== Practical: API Response Handler ===" . PHP_EOL . PHP_EOL;

abstract class ApiException extends Exception {
    abstract public function getStatusCode(): int;

    public function toResponse(): array {
        return [
            'success' => false,
            'error' => [
                'code' => $this->getStatusCode(),
                'message' => $this->getMessage(),
                'type' => (new \ReflectionClass($this))->getShortName()
            ]
        ];
    }
}

class BadRequestException extends ApiException {
    public function getStatusCode(): int { return 400; }
}

class ResourceNotFoundException extends ApiException {
    public function getStatusCode(): int { return 404; }
}

class ServerErrorException extends ApiException {
    public function getStatusCode(): int { return 500; }
}

function handleApiRequest(string $endpoint): array {
    try {
        // Simulate API logic
        if ($endpoint === '/bad') {
            throw new BadRequestException("Invalid request parameters");
        }
        if ($endpoint === '/notfound') {
            throw new ResourceNotFoundException("Endpoint not found");
        }
        if ($endpoint === '/error') {
            throw new ServerErrorException("Internal server error");
        }

        return ['success' => true, 'data' => ['result' => 'OK']];
    } catch (ApiException $e) {
        return $e->toResponse();
    }
}

echo "Request: /bad" . PHP_EOL;
echo json_encode(handleApiRequest('/bad'), JSON_PRETTY_PRINT) . PHP_EOL;

echo PHP_EOL . "Request: /notfound" . PHP_EOL;
echo json_encode(handleApiRequest('/notfound'), JSON_PRETTY_PRINT) . PHP_EOL;

// Exception with retry metadata
echo PHP_EOL . "=== Exception with Retry Info ===" . PHP_EOL . PHP_EOL;

class RetryableException extends Exception {
    public function __construct(
        string $message,
        private bool $retryable = true,
        private int $retryAfterSeconds = 0,
        int $code = 0,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function isRetryable(): bool {
        return $this->retryable;
    }

    public function getRetryAfterSeconds(): int {
        return $this->retryAfterSeconds;
    }
}

try {
    throw new RetryableException(
        "Rate limit exceeded",
        retryable: true,
        retryAfterSeconds: 60
    );
} catch (RetryableException $e) {
    if ($e->isRetryable()) {
        echo "Retryable error: {$e->getMessage()}" . PHP_EOL;
        echo "Retry after: {$e->getRetryAfterSeconds()} seconds" . PHP_EOL;
    }
}
