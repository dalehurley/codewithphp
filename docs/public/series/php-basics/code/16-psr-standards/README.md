# Chapter 16: Writing Better Code with PSR-1 and PSR-12 - Code Examples

Master PHP coding standards for professional, maintainable code that follows industry best practices.

## Files

### Good Examples

1. **`good-examples/PSR1Example.php`** - Correct PSR-1 basic coding standards
2. **`good-examples/PSR12Example.php`** - Correct PSR-12 extended coding style
3. **`phpdoc-example.php`** - Comprehensive PHPDoc documentation

### Bad Examples (Learn What to Avoid)

1. **`bad-examples/BadPSR1Example.php`** - Common PSR-1 violations
2. **`bad-examples/BadPSR12Example.php`** - Common PSR-12 violations

## Quick Reference

### PSR-1: Basic Coding Standard

#### Naming Conventions

```php
// ✅ Classes: PascalCase
class UserAccount {}
class OrderProcessor {}

// ✅ Methods: camelCase, start with verb
public function getUserName() {}
public function isActive() {}
public function hasPermission() {}

// ✅ Properties: camelCase
private string $userName;
protected int $loginAttempts;

// ✅ Constants: UPPER_CASE with underscores
public const MAX_LOGIN_ATTEMPTS = 5;
public const SESSION_TIMEOUT = 3600;
```

#### File Organization

```php
<?php

// ✅ Always use strict types
declare(strict_types=1);

// ✅ Namespace declaration
namespace App\Services;

// ✅ Use statements
use DateTime;
use InvalidArgumentException;

// ✅ One class per file
class UserService
{
    // Class definition
}

// ✅ Omit closing PHP tag
```

### PSR-12: Extended Coding Style

#### Indentation & Spacing

```php
// ✅ 4 spaces for indentation (not tabs)
class Example
{
    public function method(): void
    {
        if ($condition) {
            // 4 spaces
            $this->doSomething();
        }
    }
}
```

#### Control Structures

```php
// ✅ Space after keyword, brace on same line
if ($condition) {
    // Code
} elseif ($anotherCondition) {
    // Code
} else {
    // Code
}

// ✅ Foreach spacing
foreach ($items as $key => $value) {
    // Code
}

// ✅ While spacing
while ($condition) {
    // Code
}

// ✅ Switch formatting
switch ($value) {
    case 'option1':
        // Code
        break;

    case 'option2':
        // Code
        break;

    default:
        // Code
        break;
}
```

#### Method Declarations

```php
// ✅ Single line when short
public function getName(): string
{
    return $this->name;
}

// ✅ Multiple lines when long
public function processOrder(
    int $orderId,
    array $items,
    float $total,
    ?string $couponCode = null
): OrderResult {
    // Implementation
}
```

#### Arrays

```php
// ✅ Short array syntax
$array = [1, 2, 3];

// ✅ Multi-line formatting
$config = [
    'database' => [
        'host' => 'localhost',
        'port' => 3306,
    ],
    'cache' => [
        'driver' => 'redis',
        'ttl' => 3600,
    ],
];

// ✅ Trailing comma on multi-line
$items = [
    'first',
    'second',
    'third', // ← Trailing comma
];
```

#### Try-Catch-Finally

```php
try {
    $result = $this->riskyOperation();
} catch (SpecificException $e) {
    // Handle specific exception
} catch (Exception $e) {
    // Handle general exception
} finally {
    // Always executed
}
```

### PHPDoc Standards

#### Class Documentation

```php
/**
 * User Service
 *
 * Handles user management, authentication, and authorization.
 *
 * @package App\Services
 * @author  Your Name <email@example.com>
 * @license MIT
 */
class UserService
{
}
```

#### Method Documentation

```php
/**
 * Find user by ID
 *
 * Retrieves a user from the database using their unique identifier.
 * Returns null if the user is not found.
 *
 * @param int $id User's unique identifier
 *
 * @return User|null User object or null
 *
 * @throws InvalidArgumentException If ID is invalid
 * @throws DatabaseException If query fails
 */
public function findById(int $id): ?User
{
    // Implementation
}
```

#### Property Documentation

```php
/**
 * Maximum number of login attempts
 *
 * @var int
 */
private const MAX_LOGIN_ATTEMPTS = 5;

/**
 * Currently authenticated user
 *
 * @var User|null
 */
private ?User $currentUser = null;
```

#### Type Annotations

```php
/**
 * Get all users
 *
 * @param array<string, mixed> $criteria Search criteria
 * @param array<string>        $orderBy  Sort fields
 *
 * @return array<int, User> Array of users
 */
public function findAll(array $criteria, array $orderBy): array
{
}
```

## Common Violations & Fixes

### Naming Violations

```php
// ❌ Bad
class user_account {}        // snake_case
class UserAccount {}         // ✅ Good: PascalCase

// ❌ Bad
public function get_name() {} // snake_case
public function getName() {}  // ✅ Good: camelCase

// ❌ Bad
public const maxAttempts = 5; // camelCase
public const MAX_ATTEMPTS = 5; // ✅ Good: UPPER_CASE
```

### Spacing Violations

```php
// ❌ Bad
if($condition){
    $this->doSomething();}

// ✅ Good
if ($condition) {
    $this->doSomething();
}
```

### Visibility Violations

```php
// ❌ Bad
function doSomething() {} // No visibility

// ✅ Good
public function doSomething() {}
```

### Type Declaration Violations

```php
// ❌ Bad
public function getName():String {} // Wrong case
public function getName(): string {} // ✅ Good

// ❌ Bad
public function process(Array $data) {} // Wrong case
public function process(array $data) {} // ✅ Good
```

## Tools for PSR Compliance

### PHP CS Fixer

```bash
# Install
composer require --dev friendsofphp/php-cs-fixer

# Create config
# .php-cs-fixer.php
<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
    ])
    ->setFinder($finder);

# Run fixer
vendor/bin/php-cs-fixer fix
```

### PHP_CodeSniffer

```bash
# Install
composer require --dev squizlabs/php_codesniffer

# Check
vendor/bin/phpcs --standard=PSR12 src/

# Fix
vendor/bin/phpcbf --standard=PSR12 src/
```

### PHPStan (Static Analysis)

```bash
# Install
composer require --dev phpstan/phpstan

# Run
vendor/bin/phpstan analyse src tests --level=8
```

## Editor Configuration

### VS Code (.editorconfig)

```ini
root = true

[*.php]
indent_style = space
indent_size = 4
end_of_line = lf
charset = utf-8
trim_trailing_whitespace = true
insert_final_newline = true
```

### PhpStorm

- Settings → Editor → Code Style → PHP
- Set from → PSR-12
- Enable: "Ensure right margin is not exceeded"
- Enable: "Keep indents on empty lines"

## Best Practices

✅ **Always use strict types**

```php
declare(strict_types=1);
```

✅ **Use type hints everywhere**

```php
public function process(Request $request): Response
```

✅ **Document complex logic**

```php
/**
 * Calculates compound interest using the formula:
 * A = P(1 + r/n)^(nt)
 */
```

✅ **Keep methods focused**

```php
// One responsibility per method
public function validateEmail(string $email): bool
```

✅ **Use meaningful names**

```php
// ❌ Bad: $d, $tmp, $x
// ✅ Good: $deliveryDate, $tempUser, $xCoordinate
```

✅ **Organize imports**

```php
// Group by: PHP built-ins, vendor, internal
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use App\Models\User;
```

## Why Standards Matter

**Code Consistency**: Easier to read and maintain
**Team Collaboration**: Everyone follows same style
**Tool Support**: IDEs and linters work better
**Professional Quality**: Shows attention to detail
**Reduced Bugs**: Clarity reduces mistakes
**Onboarding**: New devs learn faster

## Quick Checklist

Before committing code, verify:

- [ ] `declare(strict_types=1)` at top
- [ ] All classes use PascalCase
- [ ] All methods use camelCase
- [ ] All constants use UPPER_CASE
- [ ] 4-space indentation (no tabs)
- [ ] Visibility on all methods/properties
- [ ] Type hints on parameters and returns
- [ ] PHPDoc on public methods
- [ ] No closing `?>` tag
- [ ] Trailing comma on multi-line arrays

## Related Chapter

[Chapter 16: Writing Better Code with PSR-1 and PSR-12](../../chapters/16-writing-better-code-with-psr-1-and-psr-12.md)

## Further Reading

- [PSR-1: Basic Coding Standard](https://www.php-fig.org/psr/psr-1/)
- [PSR-12: Extended Coding Style](https://www.php-fig.org/psr/psr-12/)
- [PHPDoc Reference](https://docs.phpdoc.org/)
- [PHP CS Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer)
- [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)
