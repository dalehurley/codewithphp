---
title: "07: Testing - Jest Patterns in PHPUnit"
description: "Apply your Jest testing knowledge to PHPUnit. Learn test structure, assertions, mocking, coverage, and testing patterns that feel familiar."
series: "php-typescript-developers"
chapter: 7
order: 7
difficulty: "Intermediate"
prerequisites:
  - "/series/php-typescript-developers/chapters/06-package-management"
---

# Testing: Jest Patterns in PHPUnit

## Overview

If you're familiar with Jest, you'll find PHPUnit surprisingly similar. Both frameworks use the xUnit pattern with describe/test structure, assertions, mocks, and coverage reporting. This chapter maps Jest concepts directly to PHPUnit.

## Learning Objectives

By the end of this chapter, you'll be able to:

- ✅ Write PHPUnit tests using familiar Jest patterns
- ✅ Use assertions effectively
- ✅ Mock dependencies and spy on methods
- ✅ Set up and tear down test fixtures
- ✅ Generate code coverage reports
- ✅ Run tests with filters and options
- ✅ Apply testing best practices

## Installation

### Jest

```bash
npm install --save-dev jest @types/jest
npm install --save-dev ts-jest typescript
```

**package.json:**
```json
{
  "scripts": {
    "test": "jest",
    "test:watch": "jest --watch",
    "test:coverage": "jest --coverage"
  }
}
```

### PHPUnit

```bash
composer require --dev phpunit/phpunit
```

**composer.json:**
```json
{
  "scripts": {
    "test": "phpunit",
    "test:coverage": "phpunit --coverage-html coverage/"
  }
}
```

**phpunit.xml:**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="vendor/autoload.php"
         colors="true"
         testdox="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>src</directory>
        </include>
    </source>
</phpunit>
```

## Test Structure

### Jest Test Structure

```typescript
// src/Calculator.ts
export class Calculator {
  add(a: number, b: number): number {
    return a + b;
  }

  divide(a: number, b: number): number {
    if (b === 0) {
      throw new Error('Division by zero');
    }
    return a / b;
  }
}

// tests/Calculator.test.ts
import { Calculator } from '../src/Calculator';

describe('Calculator', () => {
  let calculator: Calculator;

  beforeEach(() => {
    calculator = new Calculator();
  });

  test('should add two numbers', () => {
    expect(calculator.add(2, 3)).toBe(5);
  });

  test('should divide two numbers', () => {
    expect(calculator.divide(10, 2)).toBe(5);
  });

  test('should throw error when dividing by zero', () => {
    expect(() => calculator.divide(10, 0)).toThrow('Division by zero');
  });
});
```

### PHPUnit Test Structure

```php
<?php
// src/Calculator.php
namespace App;

class Calculator {
    public function add(int $a, int $b): int {
        return $a + $b;
    }

    public function divide(float $a, float $b): float {
        if ($b === 0) {
            throw new \InvalidArgumentException('Division by zero');
        }
        return $a / $b;
    }
}

// tests/Unit/CalculatorTest.php
namespace Tests\Unit;

use App\Calculator;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase {
    private Calculator $calculator;

    protected function setUp(): void {
        $this->calculator = new Calculator();
    }

    public function testShouldAddTwoNumbers(): void {
        $this->assertSame(5, $this->calculator->add(2, 3));
    }

    public function testShouldDivideTwoNumbers(): void {
        $this->assertSame(5.0, $this->calculator->divide(10, 2));
    }

    public function testShouldThrowErrorWhenDividingByZero(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Division by zero');
        $this->calculator->divide(10, 0);
    }
}
```

**Comparison:**

| Jest | PHPUnit | Purpose |
|------|---------|---------|
| `describe()` | Class name | Group tests |
| `test()` / `it()` | `test*()` method | Individual test |
| `beforeEach()` | `setUp()` | Setup before each test |
| `afterEach()` | `tearDown()` | Cleanup after each test |
| `beforeAll()` | `setUpBeforeClass()` | Setup once |
| `afterAll()` | `tearDownAfterClass()` | Cleanup once |

## Assertions

### Jest Assertions

```typescript
// Equality
expect(value).toBe(5);
expect(value).toEqual({ name: 'Alice' });
expect(value).not.toBe(3);

// Truthiness
expect(value).toBeTruthy();
expect(value).toBeFalsy();
expect(value).toBeNull();
expect(value).toBeUndefined();
expect(value).toBeDefined();

// Numbers
expect(value).toBeGreaterThan(3);
expect(value).toBeGreaterThanOrEqual(3);
expect(value).toBeLessThan(10);
expect(value).toBeCloseTo(0.3, 2); // Floating point

// Strings
expect(string).toMatch(/pattern/);
expect(string).toContain('substring');

// Arrays
expect(array).toContain('item');
expect(array).toHaveLength(3);

// Objects
expect(obj).toHaveProperty('key');
expect(obj).toMatchObject({ name: 'Alice' });

// Exceptions
expect(() => fn()).toThrow();
expect(() => fn()).toThrow(Error);
expect(() => fn()).toThrow('Error message');
```

### PHPUnit Assertions

```php
<?php
// Equality
$this->assertSame(5, $value);
$this->assertEquals(['name' => 'Alice'], $value);
$this->assertNotSame(3, $value);

// Truthiness
$this->assertTrue($value);
$this->assertFalse($value);
$this->assertNull($value);
$this->assertNotNull($value);

// Numbers
$this->assertGreaterThan(3, $value);
$this->assertGreaterThanOrEqual(3, $value);
$this->assertLessThan(10, $value);
$this->assertEqualsWithDelta(0.3, $value, 0.01); // Floating point

// Strings
$this->assertMatchesRegularExpression('/pattern/', $string);
$this->assertStringContainsString('substring', $string);

// Arrays
$this->assertContains('item', $array);
$this->assertCount(3, $array);

// Objects/Arrays
$this->assertObjectHasProperty('key', $obj);
$this->assertArrayHasKey('key', $array);

// Exceptions
$this->expectException(\Exception::class);
$this->expectExceptionMessage('Error message');
fn(); // Call function that throws
```

**Key Differences:**
- PHPUnit: Method calls (`$this->assert*()`)
- Jest: Expect chains (`expect().toBe()`)
- PHPUnit: Expected value first, actual value second
- Jest: Actual value first in `expect()`

## Mocking and Spies

### Jest Mocking

```typescript
// Mock function
const mockFn = jest.fn();
mockFn.mockReturnValue(42);
mockFn.mockReturnValueOnce(1).mockReturnValueOnce(2);

expect(mockFn()).toBe(42);
expect(mockFn).toHaveBeenCalled();
expect(mockFn).toHaveBeenCalledWith('arg1', 'arg2');
expect(mockFn).toHaveBeenCalledTimes(1);

// Mock class
class UserService {
  getUser(id: number) {
    return { id, name: 'Alice' };
  }
}

const mockService = new UserService();
jest.spyOn(mockService, 'getUser').mockReturnValue({ id: 1, name: 'Bob' });

// Test
expect(mockService.getUser(1)).toEqual({ id: 1, name: 'Bob' });
expect(mockService.getUser).toHaveBeenCalledWith(1);
```

### PHPUnit Mocking

```php
<?php
// Create mock
$mockRepository = $this->createMock(UserRepository::class);

// Configure mock
$mockRepository->method('findById')
    ->willReturn(new User(1, 'Alice'));

// Or with specific arguments
$mockRepository->expects($this->once())
    ->method('findById')
    ->with($this->equalTo(1))
    ->willReturn(new User(1, 'Alice'));

// Test
$user = $mockRepository->findById(1);
$this->assertSame('Alice', $user->getName());
```

**Mock Expectations:**

| Jest | PHPUnit |
|------|---------|
| `toHaveBeenCalled()` | `$this->once()` |
| `toHaveBeenCalledTimes(n)` | `$this->exactly(n)` |
| `toHaveBeenCalledWith(args)` | `->with($this->equalTo(args))` |
| `mockReturnValue()` | `->willReturn()` |
| `mockReturnValueOnce()` | `->willReturnOnConsecutiveCalls()` |

### Practical Mocking Example

**Jest:**
```typescript
class EmailService {
  send(to: string, subject: string): boolean {
    // Actually sends email
    return true;
  }
}

class UserController {
  constructor(private emailService: EmailService) {}

  registerUser(email: string): void {
    // ... registration logic
    this.emailService.send(email, 'Welcome!');
  }
}

// Test
describe('UserController', () => {
  test('should send welcome email on registration', () => {
    const mockEmailService = {
      send: jest.fn().mockReturnValue(true)
    } as unknown as EmailService;

    const controller = new UserController(mockEmailService);
    controller.registerUser('test@example.com');

    expect(mockEmailService.send).toHaveBeenCalledWith(
      'test@example.com',
      'Welcome!'
    );
  });
});
```

**PHPUnit:**
```php
<?php
class EmailService {
    public function send(string $to, string $subject): bool {
        // Actually sends email
        return true;
    }
}

class UserController {
    public function __construct(
        private EmailService $emailService
    ) {}

    public function registerUser(string $email): void {
        // ... registration logic
        $this->emailService->send($email, 'Welcome!');
    }
}

// Test
class UserControllerTest extends TestCase {
    public function testShouldSendWelcomeEmailOnRegistration(): void {
        $mockEmailService = $this->createMock(EmailService::class);
        $mockEmailService->expects($this->once())
            ->method('send')
            ->with(
                $this->equalTo('test@example.com'),
                $this->equalTo('Welcome!')
            )
            ->willReturn(true);

        $controller = new UserController($mockEmailService);
        $controller->registerUser('test@example.com');
    }
}
```

## Test Lifecycle

### Jest Hooks

```typescript
describe('Database', () => {
  beforeAll(() => {
    // Runs once before all tests
    console.log('Setting up database');
  });

  afterAll(() => {
    // Runs once after all tests
    console.log('Tearing down database');
  });

  beforeEach(() => {
    // Runs before each test
    console.log('Starting transaction');
  });

  afterEach(() => {
    // Runs after each test
    console.log('Rolling back transaction');
  });

  test('test 1', () => {
    // Test code
  });

  test('test 2', () => {
    // Test code
  });
});
```

### PHPUnit Hooks

```php
<?php
class DatabaseTest extends TestCase {
    public static function setUpBeforeClass(): void {
        // Runs once before all tests
        echo "Setting up database\n";
    }

    public static function tearDownAfterClass(): void {
        // Runs once after all tests
        echo "Tearing down database\n";
    }

    protected function setUp(): void {
        // Runs before each test
        echo "Starting transaction\n";
    }

    protected function tearDown(): void {
        // Runs after each test
        echo "Rolling back transaction\n";
    }

    public function testOne(): void {
        // Test code
    }

    public function testTwo(): void {
        // Test code
    }
}
```

## Data Providers (Parameterized Tests)

### Jest Parameterized Tests

```typescript
describe.each([
  [1, 1, 2],
  [2, 2, 4],
  [3, 3, 6],
])('Calculator.add(%i, %i)', (a, b, expected) => {
  test(`should return ${expected}`, () => {
    expect(new Calculator().add(a, b)).toBe(expected);
  });
});

// Or using test.each
test.each([
  [1, 1, 2],
  [2, 2, 4],
  [3, 3, 6],
])('add(%i, %i) should return %i', (a, b, expected) => {
  expect(new Calculator().add(a, b)).toBe(expected);
});
```

### PHPUnit Data Providers

```php
<?php
class CalculatorTest extends TestCase {
    /**
     * @dataProvider additionProvider
     */
    public function testAdd(int $a, int $b, int $expected): void {
        $calculator = new Calculator();
        $this->assertSame($expected, $calculator->add($a, $b));
    }

    public static function additionProvider(): array {
        return [
            'one plus one' => [1, 1, 2],
            'two plus two' => [2, 2, 4],
            'three plus three' => [3, 3, 6],
        ];
    }
}
```

**PHPUnit also supports attributes (PHP 8+):**

```php
<?php
use PHPUnit\Framework\Attributes\DataProvider;

class CalculatorTest extends TestCase {
    #[DataProvider('additionProvider')]
    public function testAdd(int $a, int $b, int $expected): void {
        $calculator = new Calculator();
        $this->assertSame($expected, $calculator->add($a, $b));
    }

    public static function additionProvider(): array {
        return [
            [1, 1, 2],
            [2, 2, 4],
            [3, 3, 6],
        ];
    }
}
```

## Code Coverage

### Jest Coverage

```bash
# Run with coverage
npm test -- --coverage

# Generate HTML report
npm test -- --coverage --coverageReporters=html

# Coverage thresholds in jest.config.js
module.exports = {
  coverageThreshold: {
    global: {
      branches: 80,
      functions: 80,
      lines: 80,
      statements: 80
    }
  }
};
```

### PHPUnit Coverage

```bash
# Run with coverage (requires Xdebug or PCOV)
composer test -- --coverage-html coverage/

# Or in phpunit.xml
```

**phpunit.xml:**
```xml
<coverage>
    <report>
        <html outputDirectory="coverage/"/>
        <text outputFile="php://stdout"/>
    </report>
</coverage>
```

**Coverage thresholds:**
```xml
<coverage>
    <include>
        <directory>src</directory>
    </include>
    <report>
        <html outputDirectory="coverage/"/>
    </report>
</coverage>
```

## Running Tests

### Jest Commands

```bash
# Run all tests
npm test

# Watch mode
npm test -- --watch

# Run specific file
npm test -- Calculator.test.ts

# Run tests matching pattern
npm test -- --testNamePattern="should add"

# Run with coverage
npm test -- --coverage

# Update snapshots
npm test -- -u

# Verbose output
npm test -- --verbose
```

### PHPUnit Commands

```bash
# Run all tests
composer test

# Run specific file
vendor/bin/phpunit tests/Unit/CalculatorTest.php

# Run specific test
vendor/bin/phpunit --filter testShouldAdd

# Run tests matching pattern
vendor/bin/phpunit --filter Calculator

# Run with coverage
vendor/bin/phpunit --coverage-html coverage/

# Testdox output (pretty)
vendor/bin/phpunit --testdox

# Stop on failure
vendor/bin/phpunit --stop-on-failure
```

## Snapshot Testing

### Jest Snapshots

```typescript
test('renders correctly', () => {
  const output = {
    name: 'Alice',
    age: 30,
    email: 'alice@example.com'
  };

  expect(output).toMatchSnapshot();
});
```

### PHPUnit Snapshots (with package)

```bash
composer require --dev spatie/phpunit-snapshot-assertions
```

```php
<?php
use Spatie\Snapshots\MatchesSnapshots;

class UserTest extends TestCase {
    use MatchesSnapshots;

    public function testUserOutput(): void {
        $output = [
            'name' => 'Alice',
            'age' => 30,
            'email' => 'alice@example.com'
        ];

        $this->assertMatchesJsonSnapshot(json_encode($output));
    }
}
```

## Integration Testing

### Jest Integration Test

```typescript
import request from 'supertest';
import { app } from '../src/app';

describe('API Integration Tests', () => {
  test('GET /users should return users', async () => {
    const response = await request(app)
      .get('/users')
      .expect(200);

    expect(response.body).toHaveLength(2);
    expect(response.body[0]).toHaveProperty('name');
  });

  test('POST /users should create user', async () => {
    const response = await request(app)
      .post('/users')
      .send({ name: 'Alice', email: 'alice@example.com' })
      .expect(201);

    expect(response.body).toHaveProperty('id');
    expect(response.body.name).toBe('Alice');
  });
});
```

### PHPUnit Integration Test

```php
<?php
class ApiIntegrationTest extends TestCase {
    private $client;

    protected function setUp(): void {
        $this->client = new GuzzleHttp\Client([
            'base_uri' => 'http://localhost:8000'
        ]);
    }

    public function testGetUsersShouldReturnUsers(): void {
        $response = $this->client->get('/users');

        $this->assertSame(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        $this->assertCount(2, $data);
        $this->assertArrayHasKey('name', $data[0]);
    }

    public function testPostUsersShouldCreateUser(): void {
        $response = $this->client->post('/users', [
            'json' => [
                'name' => 'Alice',
                'email' => 'alice@example.com'
            ]
        ]);

        $this->assertSame(201, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame('Alice', $data['name']);
    }
}
```

## Best Practices

### 1. Test Naming

**Jest:**
```typescript
describe('UserService', () => {
  describe('createUser', () => {
    it('should create user with valid data', () => {});
    it('should throw error with invalid email', () => {});
  });
});
```

**PHPUnit:**
```php
<?php
class UserServiceTest extends TestCase {
    public function testShouldCreateUserWithValidData(): void {}
    public function testShouldThrowErrorWithInvalidEmail(): void {}
}
```

### 2. Arrange-Act-Assert (AAA)

**Both frameworks:**
```typescript
test('should calculate total', () => {
  // Arrange
  const cart = new ShoppingCart();
  cart.addItem({ price: 10, quantity: 2 });

  // Act
  const total = cart.getTotal();

  // Assert
  expect(total).toBe(20);
});
```

### 3. One Assertion Per Test (Guideline)

```typescript
// ❌ Bad: Multiple unrelated assertions
test('user creation', () => {
  const user = createUser('Alice');
  expect(user.name).toBe('Alice');
  expect(user.isActive).toBe(true);
  expect(user.role).toBe('user');
});

// ✅ Good: Focused tests
test('should create user with correct name', () => {
  const user = createUser('Alice');
  expect(user.name).toBe('Alice');
});

test('should create active user by default', () => {
  const user = createUser('Alice');
  expect(user.isActive).toBe(true);
});
```

## Key Takeaways

1. **PHPUnit ≈ Jest** - Very similar testing experience with minor syntax differences
2. **Test methods** start with `test` prefix or use `@test` annotation
3. **Assertions** are method calls in PHPUnit (`$this->assert*()`) not chained methods
4. **Mocking** uses `createMock()` instead of `jest.fn()` with explicit method configuration
5. **Data providers** replace `test.each()` for parameterized tests - more powerful
6. **Coverage** requires Xdebug or PCOV extension (not built-in like Jest)
7. **Setup/Teardown** use `setUp()`/`tearDown()` methods (camelCase, not snake_case)
8. **Expected before actual** in PHPUnit assertions: `assertEquals($expected, $actual)`
9. **Test classes extend `TestCase`** - all test classes must inherit from PHPUnit base
10. **Use `@covers` annotation** to specify which class/method is being tested
11. **Assertions are strict** - `assertSame()` for type-safe equality (like `toBe` in Jest)
12. **No watch mode** by default - use PHPUnit Watcher package for auto-rerun

## Comparison Table

| Feature | Jest | PHPUnit |
|---------|------|---------|
| **Test grouping** | `describe()` | Class |
| **Test case** | `test()` / `it()` | `test*()` method |
| **Setup** | `beforeEach()` | `setUp()` |
| **Teardown** | `afterEach()` | `tearDown()` |
| **Assertion** | `expect().toBe()` | `$this->assertSame()` |
| **Mock** | `jest.fn()` | `$this->createMock()` |
| **Parameterized** | `test.each()` | `@dataProvider` |
| **Coverage** | Built-in | Requires Xdebug/PCOV |
| **Watch mode** | `--watch` | Not built-in |

## Next Steps

Now that you understand testing, let's explore code quality tools and linting.

**Next Chapter:** [08: Code Quality: ESLint meets PHP_CodeSniffer](/series/php-typescript-developers/chapters/08-code-quality)

## Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [PHPUnit Assertions](https://phpunit.de/manual/current/en/appendixes.assertions.html)
- [Mockery (Alternative Mocking)](https://github.com/mockery/mockery)
- [Pest PHP (Laravel-flavored Testing)](https://pestphp.com/)

---

**Questions or feedback?** Open an issue on [GitHub](https://github.com/dalehurley/codewithphp/issues)
