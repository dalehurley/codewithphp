<?php

declare(strict_types=1);

/**
 * Type System Syntax - PHP vs Python
 * 
 * This file demonstrates PHP's type declaration syntax compared to Python type hints.
 * Key differences:
 * - PHP: type $variable (type before variable)
 * - Python: variable: type (variable before type)
 * - PHP: ?type for nullable (shorthand for type|null)
 * - Python: Optional[type] or type | None
 * - PHP: declare(strict_types=1) for strict type checking
 */

// ============================================================================
// 1. Basic Type Declarations
// ============================================================================

// Python: def calculate_total(price: float, tax: float) -> float:
// PHP:    function calculateTotal(float $price, float $tax): float

function calculateTotal(float $price, float $tax): float
{
    return $price * (1 + $tax);
}

$total = calculateTotal(100.0, 0.1);
echo "Total: $total\n";

// ============================================================================
// 2. Nullable Types
// ============================================================================

// Python: def get_user_name(user_id: Optional[int] = None) -> str:
// PHP:    function getUserName(?int $user_id = null): string

function getUserName(?int $user_id = null): string
{
    if ($user_id === null) {
        return "Guest";
    }
    return "User $user_id";
}

echo getUserName() . "\n";        // Output: Guest
echo getUserName(42) . "\n";     // Output: User 42

// Alternative syntax (explicit union type)
function getUserNameAlt(int|null $user_id = null): string
{
    return getUserName($user_id);
}

// ============================================================================
// 3. Union Types (PHP 8.0+)
// ============================================================================

// Python: def process_value(value: int | str) -> str:
// PHP:    function processValue(int|string $value): string

function processValue(int|string $value): string
{
    return (string) $value;
}

echo processValue(42) . "\n";      // Output: 42
echo processValue("hello") . "\n"; // Output: hello

// ============================================================================
// 4. Return Types
// ============================================================================

// Python: def get_data() -> dict[str, str]:
// PHP:    function getData(): array

function getData(): array
{
    return ["key" => "value"];
}

// More specific return type (PHP 8.0+ with generics-like syntax in docblocks)
/**
 * @return array<string, string>
 */
function getTypedData(): array
{
    return ["name" => "Alice", "email" => "alice@example.com"];
}

// ============================================================================
// 5. Type Coercion (without strict_types)
// ============================================================================

// Without declare(strict_types=1), PHP coerces types
function addNumbersLenient(int $a, int $b): int
{
    return $a + $b;
}

// This would work without strict_types (but we have it enabled, so it won't)
// echo addNumbersLenient("5", 10);  // Would output: 15

// ============================================================================
// 6. Strict Types
// ============================================================================

// With declare(strict_types=1) at top of file, PHP enforces types strictly
function addNumbersStrict(int $a, int $b): int
{
    return $a + $b;
}

// This will cause a TypeError with strict_types enabled
// echo addNumbersStrict("5", 10);  // Fatal error: must be of type int

echo addNumbersStrict(5, 10) . "\n";  // Works: 15

// ============================================================================
// 7. Property Type Declarations (PHP 7.4+)
// ============================================================================

class User
{
    public string $name;
    public ?string $email;  // Nullable property
    
    public function __construct(string $name, ?string $email = null)
    {
        $this->name = $name;
        $this->email = $email;
    }
}

$user = new User("Alice", "alice@example.com");
echo "User: {$user->name}, Email: {$user->email}\n";

// ============================================================================
// 8. Mixed Type (PHP 8.0+)
// ============================================================================

// Python: def process(value: Any) -> Any:
// PHP:    function process(mixed $value): mixed

function process(mixed $value): mixed
{
    return $value;
}

echo process(42) . "\n";
echo process("hello") . "\n";
echo process([1, 2, 3]) ? "array\n" : "";

