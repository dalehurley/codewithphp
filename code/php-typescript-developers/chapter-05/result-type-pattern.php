<?php
declare(strict_types=1);

/**
 * Result Type Pattern
 * Functional error handling without exceptions
 */

echo "=== Result Type Pattern ===" . PHP_EOL . PHP_EOL;

/**
 * @template T
 * @template E of Throwable
 */
class Result {
    private function __construct(
        private bool $success,
        private mixed $value,
        private ?Throwable $error
    ) {}

    /**
     * @template V
     * @param V $value
     * @return Result<V, never>
     */
    public static function ok(mixed $value): self {
        return new self(true, $value, null);
    }

    /**
     * @template Err of Throwable
     * @param Err $error
     * @return Result<never, Err>
     */
    public static function err(Throwable $error): self {
        return new self(false, null, $error);
    }

    public function isOk(): bool {
        return $this->success;
    }

    public function isErr(): bool {
        return !$this->success;
    }

    /**
     * @return T
     * @throws RuntimeException
     */
    public function unwrap(): mixed {
        if ($this->success) {
            return $this->value;
        }
        throw new RuntimeException("Called unwrap on error result: {$this->error->getMessage()}");
    }

    /**
     * @return E
     * @throws RuntimeException
     */
    public function unwrapErr(): Throwable {
        if (!$this->success) {
            return $this->error;
        }
        throw new RuntimeException("Called unwrapErr on ok result");
    }

    /**
     * @template U
     * @param U $default
     * @return T|U
     */
    public function unwrapOr(mixed $default): mixed {
        return $this->success ? $this->value : $default;
    }

    /**
     * @template U
     * @param callable(T): U $fn
     * @return Result<U, E>
     */
    public function map(callable $fn): self {
        if ($this->success) {
            return Result::ok($fn($this->value));
        }
        return Result::err($this->error);
    }

    /**
     * @template U
     * @param callable(T): Result<U, E> $fn
     * @return Result<U, E>
     */
    public function andThen(callable $fn): self {
        if ($this->success) {
            return $fn($this->value);
        }
        return Result::err($this->error);
    }

    /**
     * @param callable(E): void $fn
     * @return $this
     */
    public function orElse(callable $fn): self {
        if (!$this->success) {
            $fn($this->error);
        }
        return $this;
    }
}

// Basic usage
echo "Basic Result Type Usage:" . PHP_EOL;

/**
 * @return Result<float, Exception>
 */
function divide(float $a, float $b): Result {
    if ($b === 0) {
        return Result::err(new Exception("Division by zero"));
    }
    return Result::ok($a / $b);
}

$result1 = divide(10, 2);
if ($result1->isOk()) {
    echo "10 / 2 = " . $result1->unwrap() . PHP_EOL;
}

$result2 = divide(10, 0);
if ($result2->isErr()) {
    echo "Error: " . $result2->unwrapErr()->getMessage() . PHP_EOL;
}

// Using unwrapOr for default values
echo PHP_EOL . "Using Default Values:" . PHP_EOL;
$result3 = divide(10, 0);
$value = $result3->unwrapOr(0);
echo "Result with default: {$value}" . PHP_EOL;

// Map operation
echo PHP_EOL . "Mapping Results:" . PHP_EOL;
$result4 = divide(10, 2)
    ->map(fn($x) => $x * 2)
    ->map(fn($x) => "Result: {$x}");

if ($result4->isOk()) {
    echo $result4->unwrap() . PHP_EOL;
}

// AndThen (flatMap) for chaining operations
echo PHP_EOL . "Chaining Operations:" . PHP_EOL;

/**
 * @return Result<float, Exception>
 */
function safeSqrt(float $n): Result {
    if ($n < 0) {
        return Result::err(new Exception("Cannot take square root of negative number"));
    }
    return Result::ok(sqrt($n));
}

$result5 = divide(16, 2)
    ->andThen(fn($x) => safeSqrt($x));

if ($result5->isOk()) {
    echo "sqrt(16/2) = " . $result5->unwrap() . PHP_EOL;
}

$result6 = divide(16, -2)
    ->andThen(fn($x) => safeSqrt($x));

if ($result6->isErr()) {
    echo "Error: " . $result6->unwrapErr()->getMessage() . PHP_EOL;
}

// OrElse for error handling
echo PHP_EOL . "Error Handling with orElse:" . PHP_EOL;

divide(10, 0)
    ->orElse(fn($e) => echo "Handled error: {$e->getMessage()}" . PHP_EOL);

// Practical example: User validation
echo PHP_EOL . "=== Practical: User Validation ===" . PHP_EOL . PHP_EOL;

class ValidationError extends Exception {}

/**
 * @return Result<string, ValidationError>
 */
function validateEmail(string $email): Result {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return Result::err(new ValidationError("Invalid email format: {$email}"));
    }
    return Result::ok($email);
}

/**
 * @return Result<int, ValidationError>
 */
function validateAge(int $age): Result {
    if ($age < 18 || $age > 120) {
        return Result::err(new ValidationError("Age must be between 18 and 120"));
    }
    return Result::ok($age);
}

/**
 * @return Result<array, ValidationError>
 */
function validateUser(string $email, int $age): Result {
    return validateEmail($email)
        ->andThen(fn($validEmail) =>
            validateAge($age)
                ->map(fn($validAge) => [
                    'email' => $validEmail,
                    'age' => $validAge
                ])
        );
}

$user1 = validateUser("alice@example.com", 25);
if ($user1->isOk()) {
    echo "Valid user:" . PHP_EOL;
    print_r($user1->unwrap());
}

$user2 = validateUser("invalid-email", 16);
if ($user2->isErr()) {
    echo "Validation failed: " . $user2->unwrapErr()->getMessage() . PHP_EOL;
}

// Practical example: Database operations
echo PHP_EOL . "=== Practical: Database Operations ===" . PHP_EOL . PHP_EOL;

class DatabaseError extends Exception {}

/**
 * @return Result<array, DatabaseError>
 */
function findUserById(int $id): Result {
    // Simulate database lookup
    $users = [
        1 => ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
        2 => ['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com'],
    ];

    if (!isset($users[$id])) {
        return Result::err(new DatabaseError("User {$id} not found"));
    }

    return Result::ok($users[$id]);
}

/**
 * @return Result<string, DatabaseError>
 */
function getUserEmail(int $userId): Result {
    return findUserById($userId)
        ->map(fn($user) => $user['email']);
}

$email = getUserEmail(1);
if ($email->isOk()) {
    echo "User email: " . $email->unwrap() . PHP_EOL;
}

$email2 = getUserEmail(999);
echo "User 999 email: " . $email2->unwrapOr("no-reply@example.com") . PHP_EOL;

// Practical example: File operations
echo PHP_EOL . "=== Practical: File Operations ===" . PHP_EOL . PHP_EOL;

class FileError extends Exception {}

/**
 * @return Result<string, FileError>
 */
function readFile(string $filename): Result {
    if (!file_exists($filename)) {
        return Result::err(new FileError("File not found: {$filename}"));
    }

    $content = file_get_contents($filename);
    if ($content === false) {
        return Result::err(new FileError("Failed to read file: {$filename}"));
    }

    return Result::ok($content);
}

/**
 * @return Result<array, FileError>
 */
function parseJsonFile(string $filename): Result {
    return readFile($filename)
        ->andThen(function($content) {
            $data = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return Result::err(new FileError("Invalid JSON: " . json_last_error_msg()));
            }
            return Result::ok($data);
        });
}

// Create a test file
$testFile = '/tmp/test.json';
file_put_contents($testFile, json_encode(['name' => 'Test', 'value' => 123]));

$config = parseJsonFile($testFile);
if ($config->isOk()) {
    echo "Config loaded:" . PHP_EOL;
    print_r($config->unwrap());
}

unlink($testFile);

// Multiple operations with Result
echo PHP_EOL . "=== Chaining Multiple Operations ===" . PHP_EOL . PHP_EOL;

/**
 * @return Result<array, Exception>
 */
function processUserOrder(int $userId, float $amount): Result {
    return findUserById($userId)
        ->andThen(fn($user) => validateAmount($amount)
            ->map(fn($validAmount) => ['user' => $user, 'amount' => $validAmount]))
        ->andThen(fn($data) => processPayment($data['user'], $data['amount']));
}

/**
 * @return Result<float, Exception>
 */
function validateAmount(float $amount): Result {
    if ($amount <= 0) {
        return Result::err(new Exception("Amount must be positive"));
    }
    if ($amount > 10000) {
        return Result::err(new Exception("Amount exceeds maximum limit"));
    }
    return Result::ok($amount);
}

/**
 * @return Result<array, Exception>
 */
function processPayment(array $user, float $amount): Result {
    // Simulate payment processing
    if ($amount > 1000 && $user['id'] === 2) {
        return Result::err(new Exception("Insufficient funds"));
    }
    return Result::ok(['status' => 'success', 'transaction_id' => uniqid()]);
}

$order1 = processUserOrder(1, 500);
if ($order1->isOk()) {
    echo "Order processed:" . PHP_EOL;
    print_r($order1->unwrap());
}

$order2 = processUserOrder(2, 2000);
$order2->orElse(fn($e) => echo "Order failed: {$e->getMessage()}" . PHP_EOL);
