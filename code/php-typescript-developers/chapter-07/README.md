# Chapter 07: Testing - Jest Patterns in PHPUnit

Practical PHPUnit examples demonstrating testing patterns familiar to Jest users.

## Setup

```bash
cd code/php-typescript-developers/chapter-07
composer install
```

## Files

- `composer.json` - Project dependencies
- `phpunit.xml` - PHPUnit configuration
- `src/Calculator.php` - Example class to test
- `src/UserService.php` - Service with dependencies (for mocking)
- `tests/Unit/CalculatorTest.php` - Basic unit tests
- `tests/Unit/UserServiceTest.php` - Mocking examples
- `tests/Integration/ApiTest.php` - Integration test example

## Running Tests

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/Unit/CalculatorTest.php

# Run with coverage (requires Xdebug)
vendor/bin/phpunit --coverage-html coverage/

# Run with testdox (pretty output)
vendor/bin/phpunit --testdox

# Run specific test method
vendor/bin/phpunit --filter testShouldAddTwoNumbers
```

## Composer Scripts

```bash
composer test              # Run all tests
composer test:coverage     # Run with coverage
```

## Requirements

- PHP 8.1+
- Composer
- Xdebug (optional, for coverage)
