# Chapter 02: Modern PHP - What's Changed

This directory contains practical examples of PHP 8.4 features that modern developers will appreciate.

## Directory Structure

```
chapter-02/
├── 01-type-safety/           # Type declarations and strict typing
├── 02-property-hooks/        # PHP 8.4 property hooks
├── 03-enums/                 # Type-safe enums
├── 04-match-expressions/     # Better switch statements
├── 05-named-arguments/       # Keyword arguments
├── 06-union-types/           # Union and DNF types
├── 07-attributes/            # Metadata attributes
├── 08-arrow-functions/       # Short closures
├── 09-readonly/              # Immutable classes
└── 10-collections/           # Laravel collections
```

## Testing

All examples can be tested with:

```bash
# Test individual file
php chapter-02/01-type-safety/strict-types.php

# Run all examples
for file in chapter-02/*/*.php; do
    echo "Testing $file..."
    php "$file" && echo "✓ PASSED" || echo "✗ FAILED"
done
```

## Key Concepts

1. **Type Safety** - Strict typing catches errors at compile time
2. **Property Hooks** - Elegant getters/setters without boilerplate
3. **Enums** - Type-safe constants with methods
4. **Match Expressions** - Exhaustive pattern matching
5. **Named Arguments** - Self-documenting function calls
6. **Union Types** - Accept multiple types safely
7. **Attributes** - Declarative metadata
8. **Arrow Functions** - Concise, auto-capturing closures
9. **Readonly Classes** - Immutable DTOs
10. **Collections** - Ruby-like functional programming

## Running Examples

```bash
cd chapter-02

# Run a specific example
php 01-type-safety/modern-php.php

# Test property hooks
php 02-property-hooks/property-hooks.php

# Test enums
php 03-enums/status-enum.php
```

## Notes

- All examples use PHP 8.4 syntax
- Examples are self-contained and can be run independently
- Output is typically shown with echo statements
- Errors are intentionally included to show what NOT to do







