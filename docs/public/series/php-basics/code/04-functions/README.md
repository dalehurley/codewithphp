# Chapter 04: Understanding and Using Functions - Code Examples

This directory contains comprehensive examples demonstrating PHP functions, from basic syntax to advanced features like arrow functions, closures, and variadic parameters.

## Files Overview

### 1. `basic-functions.php`

Foundation of function syntax in PHP.

**What it demonstrates:**

- Simple functions with no parameters
- Functions with parameters
- Functions with return values
- Multiple parameters with type declarations
- Default parameter values
- Union return types (PHP 8.0+)
- Nullable return types
- Early return pattern
- PHPDoc documentation

**Run it:**

```bash
php basic-functions.php
```

**Key Takeaways:**

- Always use type declarations for parameters and return values
- Use descriptive function names (verbs like `calculate`, `get`, `create`)
- Default parameters must come after required parameters
- Use early returns to avoid deep nesting

### 2. `arrow-closures.php`

Modern function syntax with arrow functions and closures.

**What it demonstrates:**

- Arrow function syntax (PHP 7.4+)
- Anonymous functions (closures)
- Variable capture with `use` keyword
- Capturing by reference
- Returning closures from functions
- First-class callable syntax (PHP 8.1+)
- Array operations with arrow functions
- Practical callback patterns

**Run it:**

```bash
php arrow-closures.php
```

**Key Takeaways:**

- Arrow functions automatically capture variables from parent scope
- Use arrow functions for simple one-line operations
- Use closures for more complex operations with multiple statements
- Capture by reference (`use (&$var)`) to modify external variables
- Arrow functions are perfect for `array_map`, `array_filter`, `usort`

### 3. `scope-variadic.php`

Variable scope and flexible function signatures.

**What it demonstrates:**

- Local vs global scope
- `global` keyword (not recommended)
- `$GLOBALS` superglobal
- Static variables (persist between calls)
- Variadic functions (`...$args`)
- Named arguments (PHP 8.0+)
- Argument unpacking
- Mixed parameters (regular + variadic)

**Run it:**

```bash
php scope-variadic.php
```

**Key Takeaways:**

- Avoid global variables; pass parameters instead
- Use `static` variables sparingly (can make testing harder)
- Variadic functions provide flexible APIs
- Named arguments improve readability with many parameters
- Type hint variadic parameters for type safety

## Exercise Solutions

### Exercise 1: Temperature Converter

**File:** `solutions/exercise-1-temperature-converter.php`

Create functions to convert between Celsius, Fahrenheit, and Kelvin.

**Functions required:**

- `celsiusToFahrenheit(float $celsius): float`
- `fahrenheitToCelsius(float $fahrenheit): float`
- `celsiusToKelvin(float $celsius): float`
- `kelvinToCelsius(float $kelvin): float`

**Bonus:** Universal converter using `match` expressions

**Run it:**

```bash
php solutions/exercise-1-temperature-converter.php
```

**What you'll learn:**

- Creating focused, single-purpose functions
- Working with mathematical formulas
- Using `match` for clean conditional logic

### Exercise 2: String Utilities

**File:** `solutions/exercise-2-string-utilities.php`

Build a collection of useful string manipulation functions.

**Functions required:**

- `isPalindrome(string $text): bool`
- `wordCount(string $text): int`
- `reverseWords(string $text): string`
- `truncate(string $text, int $length, string $suffix = '...'): string`

**Bonus:** URL slug generator

**Run it:**

```bash
php solutions/exercise-2-string-utilities.php
```

**What you'll learn:**

- String manipulation techniques
- Using default parameters
- Regular expressions for text processing
- Creating utility libraries

### Exercise 3: Array Statistics

**File:** `solutions/exercise-3-array-statistics.php`

Create functions to calculate statistical measures.

**Functions required:**

- `getAverage(array $numbers): float`
- `getMedian(array $numbers): float`
- `getMode(array $numbers): int|float|null`
- `getRange(array $numbers): float`
- `getStats(array $numbers): array` - Returns all stats

**Run it:**

```bash
php solutions/exercise-3-array-statistics.php
```

**What you'll learn:**

- Working with array operations
- Implementing mathematical algorithms
- Returning structured data (arrays)
- Union types for flexible return values

### Exercise 4: Validation Library

**File:** `solutions/exercise-4-validation-library.php`

Build a comprehensive validation library for form processing.

**Functions required:**

- `isValidEmail(string $email): bool`
- `isValidUrl(string $url): bool`
- `isStrongPassword(string $password): bool`
- `isValidUsername(string $username): bool`
- `sanitizeString(string $input): string`
- `validateUser(array $data): array` - Comprehensive validation

**Run it:**

```bash
php solutions/exercise-4-validation-library.php
```

**What you'll learn:**

- Using built-in PHP filters (`filter_var`)
- Regular expressions for pattern matching
- Building comprehensive validation systems
- Returning validation results with error messages
- Input sanitization for security

## Quick Reference

### Function Syntax

```php
// Basic function
function add(int $a, int $b): int {
    return $a + $b;
}

// Arrow function (one-liner)
$add = fn($a, $b) => $a + $b;

// Anonymous function (closure)
$add = function ($a, $b) {
    return $a + $b;
};
```

### Type Declarations

```php
// Scalar types
function example(
    string $name,
    int $age,
    float $price,
    bool $isActive
): void { }

// Array and nullable
function process(?array $data): ?string { }

// Union types (PHP 8.0+)
function handle(int|string $value): float|string { }
```

### Default Parameters

```php
function greet(string $name, string $title = 'Mr.'): string {
    return "Hello, $title $name";
}

greet('Smith');              // Uses default "Mr."
greet('Smith', 'Dr.');       // Overrides default
```

### Named Arguments (PHP 8.0+)

```php
function create(string $name, int $age, bool $active = true): array {
    return compact('name', 'age', 'active');
}

// Call with named arguments in any order
create(age: 25, name: 'Alice');
create(name: 'Bob', active: false, age: 30);
```

### Variadic Functions

```php
// Accept any number of arguments
function sum(int ...$numbers): int {
    return array_sum($numbers);
}

sum(1, 2, 3);        // 6
sum(1, 2, 3, 4, 5); // 15
```

### Variable Scope

```php
$global = 'I am global';

function test() {
    $local = 'I am local';
    global $global;  // Access global variable (not recommended)

    static $counter = 0;  // Persists between calls
    $counter++;
}
```

## Best Practices

### 1. Always Use Type Declarations

```php
// ✗ BAD - No types
function add($a, $b) {
    return $a + $b;
}

// ✓ GOOD - With types and strict_types
declare(strict_types=1);

function add(int $a, int $b): int {
    return $a + $b;
}
```

### 2. Use Descriptive Names

```php
// ✗ BAD - Unclear names
function calc($x) { }
function process($data) { }

// ✓ GOOD - Clear, descriptive names
function calculateTotalPrice(float $basePrice): float { }
function processUserRegistration(array $userData): bool { }
```

### 3. Keep Functions Focused (Single Responsibility)

```php
// ✗ BAD - Does too much
function handleUser($data) {
    // validates, sanitizes, saves, sends email, logs...
}

// ✓ GOOD - Separate concerns
function validateUser(array $data): array { }
function sanitizeUser(array $data): array { }
function saveUser(array $data): bool { }
function sendWelcomeEmail(string $email): void { }
```

### 4. Use Early Returns

```php
// ✗ BAD - Deep nesting
function process($data) {
    if ($data !== null) {
        if (count($data) > 0) {
            if ($data['valid']) {
                // do something
            }
        }
    }
}

// ✓ GOOD - Early returns
function process(?array $data): void {
    if ($data === null) {
        return;
    }

    if (count($data) === 0) {
        return;
    }

    if (!$data['valid']) {
        return;
    }

    // Main logic here
}
```

### 5. Document Complex Functions

```php
/**
 * Calculate compound interest
 *
 * @param float $principal Initial amount
 * @param float $rate Annual interest rate (as decimal, e.g., 0.05 for 5%)
 * @param int $years Number of years
 * @param int $compound Compounds per year (default: 12 for monthly)
 * @return float Final amount after interest
 */
function calculateCompoundInterest(
    float $principal,
    float $rate,
    int $years,
    int $compound = 12
): float {
    return $principal * pow(1 + $rate / $compound, $compound * $years);
}
```

## Common Patterns

### Factory Pattern

```php
function createUser(string $username, string $email): array {
    return [
        'id' => uniqid(),
        'username' => $username,
        'email' => $email,
        'created_at' => time()
    ];
}
```

### Validator Pattern

```php
function validate(array $data, array $rules): array {
    $errors = [];

    foreach ($rules as $field => $rule) {
        if (!isset($data[$field])) {
            $errors[$field] = "Field $field is required";
        }
    }

    return ['valid' => count($errors) === 0, 'errors' => $errors];
}
```

### Transform Pattern

```php
function transformUsers(array $users, callable $transformer): array {
    return array_map($transformer, $users);
}

$users = [/* ... */];
$formatted = transformUsers($users, fn($u) => [
    'name' => strtoupper($u['name']),
    'email' => strtolower($u['email'])
]);
```

## Common Mistakes to Avoid

### 1. Modifying Parameters Unexpectedly

```php
// ✗ BAD - Modifies input array
function addItem(array $cart, array $item): array {
    $cart[] = $item;  // Modifies parameter
    return $cart;
}

// ✓ GOOD - Return new array
function addItem(array $cart, array $item): array {
    return [...$cart, $item];
}
```

### 2. Using Global State

```php
// ✗ BAD - Depends on global state
$config = ['tax' => 0.08];

function calculateTotal($price) {
    global $config;  // Hidden dependency
    return $price * (1 + $config['tax']);
}

// ✓ GOOD - Explicit dependencies
function calculateTotal(float $price, float $taxRate): float {
    return $price * (1 + $taxRate);
}
```

### 3. Not Validating Input

```php
// ✗ BAD - No validation
function divide($a, $b) {
    return $a / $b;  // Will fail if $b is 0
}

// ✓ GOOD - Validate and handle errors
function divide(float $a, float $b): float|string {
    if ($b === 0.0) {
        return 'Cannot divide by zero';
    }
    return $a / $b;
}
```

## Performance Tips

1. **Avoid unnecessary function calls in loops:**

```php
// Less efficient
for ($i = 0; $i < count($array); $i++) { }

// More efficient
$length = count($array);
for ($i = 0; $i < $length; $i++) { }
```

2. **Use arrow functions for simple operations:**

```php
// Arrow functions are optimized
$doubled = array_map(fn($n) => $n * 2, $numbers);
```

3. **Cache expensive computations:**

```php
function fibonacci(int $n): int {
    static $cache = [];

    if (isset($cache[$n])) {
        return $cache[$n];
    }

    if ($n <= 1) {
        return $n;
    }

    $cache[$n] = fibonacci($n - 1) + fibonacci($n - 2);
    return $cache[$n];
}
```

## Next Steps

Once you master functions, you're ready for:

- **Chapter 05:** Handling HTML Forms and User Input
- **Chapter 06:** Deep Dive into Arrays (with advanced array functions)
- **Chapter 08:** Object-Oriented Programming (methods are functions in classes)

## Related Chapter

[Chapter 04: Understanding and Using Functions](../../chapters/04-understanding-and-using-functions.md)

## Further Reading

- [PHP Manual: Functions](https://www.php.net/manual/en/language.functions.php)
- [PHP Manual: Arrow Functions](https://www.php.net/manual/en/functions.arrow.php)
- [PHP Manual: Anonymous Functions](https://www.php.net/manual/en/functions.anonymous.php)
- [PHP Manual: First Class Callable Syntax](https://www.php.net/manual/en/functions.first_class_callable_syntax.php)
