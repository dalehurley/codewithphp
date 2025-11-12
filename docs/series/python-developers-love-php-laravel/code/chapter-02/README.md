# Chapter 02: Modern PHP Code Examples

This directory contains code examples demonstrating modern PHP features (PHP 7.4+ and PHP 8.4) with comparisons to Python equivalents.

## Files

### PHP 7.x Features

- **`php7-types.php`** - Type declarations for function parameters and return types (PHP 7.0+)
- **`null-coalescing.php`** - Null coalescing operator (`??`) examples (PHP 7.0+)
- **`strict-typing.php`** - Strict type checking with `declare(strict_types=1)` (PHP 7.0+)

### PHP 8.0+ Features

- **`union-types.php`** - Union types (`string|int`) examples (PHP 8.0+)
- **`named-arguments.php`** - Named arguments examples (PHP 8.0+)
- **`match-expressions.php`** - Match expressions vs switch statements (PHP 8.0+)
- **`constructor-promotion.php`** - Constructor property promotion (PHP 8.0+)
- **`attributes.php`** - Attributes (similar to Python decorators) (PHP 8.0+)
- **`enums.php`** - Enums (similar to Python Enum class) (PHP 8.1+)

### PHP 8.4 Features

- **`property-hooks.php`** - Property hooks (custom getters/setters) (PHP 8.4)
- **`asymmetric-visibility.php`** - Asymmetric visibility (public read, private write) (PHP 8.4)
- **`typed-constants.php`** - Typed class constants (PHP 8.4)

### Code Standards

- **`psr12-example.php`** - PSR-12 compliant code example demonstrating modern PHP structure

## Running the Examples

All examples require **PHP 8.4+** to run. Some features require specific PHP versions:

- PHP 7.0+: Type declarations, null coalescing operator
- PHP 8.0+: Union types, named arguments, match expressions, constructor property promotion, attributes
- PHP 8.1+: Enums
- PHP 8.4: Property hooks, asymmetric visibility, typed constants

### Running Individual Files

```bash
# Run a specific example
php php7-types.php
php union-types.php
php property-hooks.php
```

### Running All Examples

```bash
# Run all PHP files in this directory
for file in *.php; do
    echo "Running $file..."
    php "$file"
    echo ""
done
```

## Python Equivalents

Each PHP example includes comments comparing to Python equivalents:

- **Type hints**: Python's `typing` module (PEP 484)
- **Union types**: Python's `Union[str, int]` from `typing`
- **Named arguments**: Python's keyword arguments
- **Match expressions**: Python 3.10+'s `match` statement
- **Constructor promotion**: Python's `@dataclass` decorator
- **Attributes**: Python decorators
- **Enums**: Python's `Enum` class
- **Property hooks**: Python's `@property` decorator

## Notes

- All examples use `declare(strict_types=1);` for strict type checking
- Examples follow PSR-12 coding standards
- Code is production-ready and demonstrates best practices
- Comments explain PHP vs Python differences where relevant

## Further Reading

- [PHP 8.4 Release Notes](https://www.php.net/releases/8.4/en.php)
- [PHP 8.0 New Features](https://www.php.net/manual/en/migration80.new-features.php)
- [PSR-12 Coding Style Guide](https://www.php-fig.org/psr/psr-12/)

