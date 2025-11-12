# Chapter 04: PHP Syntax & Language Differences - Code Examples

This directory contains code examples demonstrating PHP syntax differences from Python, with side-by-side comparisons where applicable.

## Files Overview

### `quickstart.php`
Quick Start example demonstrating key PHP syntax differences:
- Function definitions with type declarations
- Nullable types (`?int`)
- String interpolation (`{$variable}`)
- Constructor property promotion (PHP 8.0+)

**Run it:**
```bash
php quickstart.php
```

### `variables-comparison.php`
Demonstrates PHP's variable syntax differences:
- Variable prefix requirement (`$`)
- Variable assignment and reassignment
- Constants vs variables
- Variable scope
- Variable variables (PHP-specific)
- Null coalescing operator

**Run it:**
```bash
php variables-comparison.php
```

### `types-comparison.php`
Shows PHP type declaration syntax compared to Python type hints:
- Basic type declarations
- Nullable types (`?type`)
- Union types (`type1|type2`)
- Return type declarations
- Strict typing (`declare(strict_types=1)`)
- Property type declarations
- Mixed type

**Run it:**
```bash
php types-comparison.php
```

### `strings-comparison.php`
Demonstrates PHP string syntax differences:
- Single vs double quotes
- String interpolation (`{$variable}`)
- String concatenation (`.` operator)
- Heredoc and Nowdoc
- String functions vs Python methods
- String formatting

**Run it:**
```bash
php strings-comparison.php
```

### `arrays-comparison.php`
Shows PHP array syntax vs Python lists and dictionaries:
- Indexed arrays (like Python lists)
- Associative arrays (like Python dicts)
- Array operations and functions
- Array destructuring
- Array functions vs Python methods
- Array merging and slicing
- Array sorting
- Spread operator

**Run it:**
```bash
php arrays-comparison.php
```

### `functions-comparison.php`
Demonstrates PHP function definition syntax:
- Basic function definitions
- Default parameters
- Variadic functions (`...$args`)
- Arrow functions (`fn()`)
- Anonymous functions (closures)
- Named arguments (PHP 8.0+)
- Return type declarations
- Callable type hints

**Run it:**
```bash
php functions-comparison.php
```

### `operators-comparison.php`
Shows PHP operators compared to Python:
- Ternary operator (`condition ? true : false`)
- Null coalescing operator (`??`)
- Null coalescing assignment (`??=`)
- Chaining null coalescing operators
- Comparison with Python's `or` operator

**Run it:**
```bash
php operators-comparison.php
```

### `match-expressions-comparison.php`
Demonstrates PHP match expressions (PHP 8.0+) vs Python match statements:
- Basic match expressions
- Multiple conditions
- Match with guards/conditions
- Match as expression (returns value)
- Type checking and exhaustiveness

**Run it:**
```bash
php match-expressions-comparison.php
```

### `oop-comparison.php`
Shows PHP OOP syntax differences:
- Basic class definitions
- Properties and visibility modifiers
- Methods and `$this`
- Static methods and properties
- Inheritance
- Abstract classes
- Interfaces
- Constructor property promotion (PHP 8.0+)
- Traits (code reuse mechanism)
- Trait conflict resolution
- Enums (PHP 8.1+)

**Run it:**
```bash
php oop-comparison.php
```

### `namespaces-comparison.php`
Demonstrates PHP namespaces vs Python modules:
- Namespace declaration
- Use statements
- Fully qualified names
- Namespace aliasing
- Grouped use statements
- Global namespace access
- Namespace functions and constants
- PSR-4 autoloading example
- File structure comparison

**Note:** This file contains examples and comments. Some code is commented out because it requires a proper project structure with autoloading.

**Run it:**
```bash
php namespaces-comparison.php
```

### `exceptions-comparison.php`
Shows PHP exception handling syntax:
- Basic try/catch/finally
- Throwing exceptions
- Custom exceptions
- Exception with custom properties
- Error vs Exception (PHP 7+)
- Exception chaining
- Multiple exception types
- Exception information
- Error suppression (not recommended)

**Run it:**
```bash
php exceptions-comparison.php
```

## Running All Examples

To run all examples at once:

```bash
# On Unix/macOS
for file in *.php; do
    echo "=== Running $file ==="
    php "$file"
    echo ""
done

# Or individually
php quickstart.php
php variables-comparison.php
php types-comparison.php
php strings-comparison.php
php arrays-comparison.php
php functions-comparison.php
php operators-comparison.php
php match-expressions-comparison.php
php oop-comparison.php
php namespaces-comparison.php
php exceptions-comparison.php
```

## Key Syntax Differences Summary

| Feature | Python | PHP |
|---------|--------|-----|
| Variables | `name = "value"` | `$name = "value";` |
| Type hints | `def func(param: type) -> type:` | `function func(type $param): type` |
| Nullable | `Optional[type]` or `type \| None` | `?type` or `type\|null` |
| String interpolation | `f"{var}"` | `"{$var}"` |
| String concatenation | `str1 + str2` | `$str1 . $str2` |
| Arrays | `[1, 2, 3]` or `{"key": "value"}` | `[1, 2, 3]` or `["key" => "value"]` |
| Ternary | `true if condition else false` | `condition ? true : false` |
| Null coalescing | `value or 'default'` (falsy) | `$value ?? 'default'` (null only) |
| Match | `match value: case x: return y` | `match ($value) { x => y }` |
| Object access | `obj.property` | `$obj->property` |
| Static access | `Class.method()` | `Class::method()` |
| Traits/Mixins | Mixin classes | `trait Name { }` and `use Name;` |
| Namespaces | File-based modules | `namespace App\Models;` |
| Imports | `from app.models import User` | `use App\Models\User;` |
| Exceptions | `except Exception as e:` | `catch (Exception $e)` |
| Throw | `raise Exception()` | `throw new Exception();` |

## Requirements

- PHP 8.4+ (some examples use PHP 8.0+ features)
- All files use `declare(strict_types=1);` for strict type checking

## Notes

- All examples are designed to be runnable standalone
- Examples include comments comparing Python and PHP syntax
- Some examples may produce warnings/errors intentionally to demonstrate concepts
- The `namespaces-comparison.php` file contains mostly examples and comments, as it requires a proper project structure

## Exercise Solutions

Complete solutions for all exercises from Chapter 04 are available in the `solutions/` directory:

### `solutions/exercise-1-product.php`
Solution for Exercise 1: Converting a Python Product class to PHP 8.4.
- Demonstrates constructor property promotion
- Shows proper type declarations
- Includes exception handling
- Matches Python's string formatting

**Run it:**
```bash
php solutions/exercise-1-product.php
```

### `solutions/exercise-2-process-tags.php`
Solution for Exercise 2: String processing function.
- Converts Python tag processing function to PHP
- Demonstrates array functions and sorting
- Shows proper type declarations

**Run it:**
```bash
php solutions/exercise-2-process-tags.php
```

### `solutions/exercise-3-namespaces/`
Solution for Exercise 3: Namespace and autoloading project.
- Complete project structure with PSR-4 autoloading
- Demonstrates namespace usage across multiple files
- Includes `composer.json` configuration
- Shows `use` statements and cross-namespace usage

**Setup and run:**
```bash
cd solutions/exercise-3-namespaces
composer install  # or composer dump-autoload
php index.php
```

See the [solutions/exercise-3-namespaces/README.md](solutions/exercise-3-namespaces/README.md) for detailed setup instructions.

## Related Chapter

See [Chapter 04: The PHP Syntax & Language Differences for Python Devs](../../chapters/04-php-syntax-language-differences-for-python-devs.md) for the full tutorial.

