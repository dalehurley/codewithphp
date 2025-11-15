---
title: "12: Unit Testing with PHPUnit"
description: "PHPUnit vs JUnit, assertions, mocking, code coverage"
series: "php-for-java-developers"
chapter: 12
order: 12
difficulty: "Intermediate"
prerequisites:
  - "/series/php-for-java-developers/chapters/11-dependency-injection"
---

# Chapter 12: Unit Testing with PHPUnit

<Badge type="warning">Intermediate</Badge>

## Overview

Unit testing is essential for maintaining code quality, preventing regressions, and enabling confident refactoring. If you're coming from Java, you're likely familiar with JUnit. PHPUnit is PHP's equivalent testing framework, offering similar capabilities with PHP-specific features. This chapter covers everything from basic test structure to advanced mocking techniques, helping you write comprehensive test suites for your PHP applications.

**What You'll Learn:**
- PHPUnit vs JUnit comparison and similarities
- Test structure and organization
- Assertions and test expectations
- Test fixtures and lifecycle methods
- Data providers for parameterized tests
- Mocking and test doubles
- Code coverage analysis
- Testing best practices (FIRST, AAA pattern)
- Integration with CI/CD pipelines
- Testing strategies for different scenarios

## Prerequisites

Before starting this chapter, you should be comfortable with:
- Object-oriented programming (Chapters 3-5)
- Dependency injection (Chapter 11)
- Basic command-line usage
- Composer for dependency management

## Learning Objectives

By the end of this chapter, you will be able to:

1. **Install and configure PHPUnit** in your projects
2. **Write unit tests** following testing best practices
3. **Use assertions** to verify expected outcomes
4. **Create test fixtures** to set up test environments
5. **Implement data providers** for parameterized testing
6. **Mock dependencies** to isolate units under test
7. **Measure code coverage** and interpret results
8. **Organize test suites** effectively
9. **Apply TDD principles** to development workflow

---

## Section 1: PHPUnit vs JUnit

PHPUnit is heavily inspired by JUnit, so the concepts are familiar to Java developers.

### Installation

Install PHPUnit via Composer:

```bash
composer require --dev phpunit/phpunit ^10.0
```

### Basic Test Comparison

::: code-group

```php [PHPUnit]
<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Calculator;

class CalculatorTest extends TestCase
{
    private Calculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new Calculator();
    }

    public function testAddition(): void
    {
        $result = $this->calculator->add(2, 3);
        $this->assertEquals(5, $result);
    }

    public function testDivision(): void
    {
        $result = $this->calculator->divide(10, 2);
        $this->assertEquals(5, $result);
    }

    public function testDivisionByZero(): void
    {
        $this->expectException(\DivisionByZeroError::class);
        $this->calculator->divide(10, 0);
    }
}
```

```java [JUnit 5]
package com.example;

import org.junit.jupiter.api.*;
import static org.junit.jupiter.api.Assertions.*;

class CalculatorTest {
    private Calculator calculator;

    @BeforeEach
    void setUp() {
        calculator = new Calculator();
    }

    @Test
    void testAddition() {
        int result = calculator.add(2, 3);
        assertEquals(5, result);
    }

    @Test
    void testDivision() {
        int result = calculator.divide(10, 2);
        assertEquals(5, result);
    }

    @Test
    void testDivisionByZero() {
        assertThrows(ArithmeticException.class, () -> {
            calculator.divide(10, 0);
        });
    }
}
```

:::

### Key Similarities

| Feature | PHPUnit | JUnit 5 |
|---------|---------|---------|
| Base class | `TestCase` | None (annotations) |
| Setup | `setUp()` | `@BeforeEach` |
| Teardown | `tearDown()` | `@AfterEach` |
| Test method | `test*()` or `@test` | `@Test` |
| Assertions | `$this->assertEquals()` | `assertEquals()` |
| Exceptions | `expectException()` | `assertThrows()` |
| Data providers | `@dataProvider` | `@ParameterizedTest` |

### phpunit.xml Configuration

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         failOnWarning="true"
         failOnRisky="true"
         stopOnFailure="false">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <coverage>
        <report>
            <html outputDirectory="coverage/html"/>
            <text outputFile="php://stdout" showUncoveredFiles="true"/>
        </report>
    </coverage>
    <source>
        <include>
            <directory>src</directory>
        </include>
    </source>
</phpunit>
```

---

## Section 2: Test Structure and Organization

Organize tests to mirror your application structure.

### Directory Structure

```
project/
├── src/
│   ├── Calculator.php
│   ├── Services/
│   │   ├── UserService.php
│   │   └── EmailService.php
│   └── Repositories/
│       └── UserRepository.php
├── tests/
│   ├── Unit/
│   │   ├── CalculatorTest.php
│   │   ├── Services/
│   │   │   ├── UserServiceTest.php
│   │   │   └── EmailServiceTest.php
│   │   └── Repositories/
│   │       └── UserRepositoryTest.php
│   └── Feature/
│       └── UserRegistrationTest.php
├── phpunit.xml
└── composer.json
```

### Test Naming Conventions

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\UserService;

class UserServiceTest extends TestCase
{
    // Test naming pattern: test + MethodName + Scenario + ExpectedResult

    public function testRegisterUserWithValidDataCreatesUser(): void
    {
        // Test implementation
    }

    public function testRegisterUserWithDuplicateEmailThrowsException(): void
    {
        // Test implementation
    }

    public function testFindUserByIdWithNonExistentIdReturnsNull(): void
    {
        // Test implementation
    }

    // Alternative: descriptive method names with @test annotation
    /**
     * @test
     */
    public function it_creates_user_with_valid_data(): void
    {
        // Test implementation
    }

    /**
     * @test
     */
    public function it_throws_exception_for_duplicate_email(): void
    {
        // Test implementation
    }
}
```

---

## Section 3: Assertions

PHPUnit provides comprehensive assertions for verifying expectations.

### Common Assertions

```php
<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AssertionsExampleTest extends TestCase
{
    public function testEqualityAssertions(): void
    {
        // Loose comparison (==)
        $this->assertEquals(5, '5');
        $this->assertEquals([1, 2, 3], [1, 2, 3]);

        // Strict comparison (===)
        $this->assertSame(5, 5);
        $this->assertNotSame(5, '5'); // Would fail with assertEquals

        // Not equal
        $this->assertNotEquals(5, 10);
        $this->assertNotSame(5, '5');
    }

    public function testBooleanAssertions(): void
    {
        $this->assertTrue(true);
        $this->assertFalse(false);

        // Check if value is truthy/falsy
        $this->assertTrue((bool) 1);
        $this->assertFalse((bool) 0);
    }

    public function testNullAssertions(): void
    {
        $this->assertNull(null);
        $this->assertNotNull('value');
    }

    public function testTypeAssertions(): void
    {
        $this->assertIsInt(42);
        $this->assertIsFloat(3.14);
        $this->assertIsString('hello');
        $this->assertIsBool(true);
        $this->assertIsArray([1, 2, 3]);
        $this->assertIsObject(new \stdClass());
        $this->assertIsCallable(fn() => true);
        $this->assertIsIterable([1, 2, 3]);
    }

    public function testStringAssertions(): void
    {
        $this->assertStringContainsString('world', 'Hello world');
        $this->assertStringStartsWith('Hello', 'Hello world');
        $this->assertStringEndsWith('world', 'Hello world');
        $this->assertStringMatchesFormat('%s world', 'Hello world');
        $this->assertMatchesRegularExpression('/\d+/', '123');
    }

    public function testArrayAssertions(): void
    {
        $array = ['name' => 'John', 'age' => 30, 'email' => 'john@example.com'];

        // Check if array has key
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayNotHasKey('phone', $array);

        // Check array contains value
        $this->assertContains(30, $array);
        $this->assertNotContains(50, $array);

        // Check array count
        $this->assertCount(3, $array);
        $this->assertNotCount(2, $array);

        // Check if array is empty
        $this->assertEmpty([]);
        $this->assertNotEmpty($array);
    }

    public function testObjectAssertions(): void
    {
        $user = new \stdClass();
        $user->name = 'John';
        $user->age = 30;

        // Check object has attribute
        $this->assertObjectHasProperty('name', $user);
        $this->assertObjectNotHasProperty('email', $user);

        // Check instance type
        $this->assertInstanceOf(\stdClass::class, $user);
        $this->assertNotInstanceOf(\DateTime::class, $user);
    }

    public function testNumericAssertions(): void
    {
        // Greater than
        $this->assertGreaterThan(5, 10);
        $this->assertGreaterThanOrEqual(5, 5);

        // Less than
        $this->assertLessThan(10, 5);
        $this->assertLessThanOrEqual(5, 5);

        // Finite, infinite, NAN
        $this->assertFinite(42);
        $this->assertInfinite(INF);
        $this->assertNan(NAN);
    }

    public function testFileAssertions(): void
    {
        $file = '/tmp/test.txt';
        file_put_contents($file, 'content');

        $this->assertFileExists($file);
        $this->assertFileIsReadable($file);
        $this->assertFileIsWritable($file);

        unlink($file);
        $this->assertFileDoesNotExist($file);
    }

    public function testExceptionAssertions(): void
    {
        // Expect exception type
        $this->expectException(\InvalidArgumentException::class);

        // Expect exception message
        $this->expectExceptionMessage('Invalid argument');

        // Expect exception code
        $this->expectExceptionCode(400);

        throw new \InvalidArgumentException('Invalid argument', 400);
    }
}
```

### Custom Assertions

```php
<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\User;

class CustomAssertionsTest extends TestCase
{
    public function testUserIsActive(): void
    {
        $user = new User(['name' => 'John', 'status' => 'active']);

        $this->assertUserIsActive($user);
    }

    /**
     * Custom assertion for user active status
     */
    private function assertUserIsActive(User $user, string $message = ''): void
    {
        $this->assertSame(
            'active',
            $user->status,
            $message ?: "Failed asserting that user '{$user->name}' is active"
        );
    }

    /**
     * Custom assertion for valid email
     */
    private function assertValidEmail(string $email, string $message = ''): void
    {
        $this->assertMatchesRegularExpression(
            '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            $email,
            $message ?: "Failed asserting that '{$email}' is a valid email"
        );
    }

    public function testEmailValidation(): void
    {
        $this->assertValidEmail('user@example.com');
    }
}
```

---

## Section 4: Test Fixtures and Lifecycle

Test fixtures set up the test environment.

### Lifecycle Methods

```php
<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\UserService;
use App\Repositories\UserRepository;

class LifecycleTest extends TestCase
{
    private UserService $userService;
    private static $sharedResource;

    /**
     * Called once before any tests in this class
     */
    public static function setUpBeforeClass(): void
    {
        // Initialize shared resources
        self::$sharedResource = new \stdClass();
        echo "setUpBeforeClass called\n";
    }

    /**
     * Called before each test method
     */
    protected function setUp(): void
    {
        // Initialize fresh test objects
        $repository = $this->createMock(UserRepository::class);
        $this->userService = new UserService($repository);
        echo "setUp called\n";
    }

    /**
     * Called after each test method
     */
    protected function tearDown(): void
    {
        // Clean up after test
        echo "tearDown called\n";
    }

    /**
     * Called once after all tests in this class
     */
    public static function tearDownAfterClass(): void
    {
        // Clean up shared resources
        self::$sharedResource = null;
        echo "tearDownAfterClass called\n";
    }

    public function testFirst(): void
    {
        echo "testFirst executed\n";
        $this->assertTrue(true);
    }

    public function testSecond(): void
    {
        echo "testSecond executed\n";
        $this->assertTrue(true);
    }
}

// Output:
// setUpBeforeClass called
// setUp called
// testFirst executed
// tearDown called
// setUp called
// testSecond executed
// tearDown called
// tearDownAfterClass called
```

### Test Fixtures

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\UserService;
use App\Repositories\UserRepository;
use App\Models\User;

class UserServiceTest extends TestCase
{
    private UserService $userService;
    private UserRepository $repository;
    private array $testUsers;

    protected function setUp(): void
    {
        // Create test data
        $this->testUsers = [
            new User(['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com']),
            new User(['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com']),
        ];

        // Create mock repository
        $this->repository = $this->createMock(UserRepository::class);

        // Create service with mocked dependencies
        $this->userService = new UserService($this->repository);
    }

    public function testFindUserById(): void
    {
        // Configure mock to return test user
        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($this->testUsers[0]);

        $user = $this->userService->findById(1);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
    }

    public function testGetAllUsers(): void
    {
        // Configure mock to return all test users
        $this->repository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($this->testUsers);

        $users = $this->userService->getAllUsers();

        $this->assertCount(2, $users);
        $this->assertEquals('John Doe', $users[0]->name);
        $this->assertEquals('Jane Smith', $users[1]->name);
    }
}
```

---

## Section 5: Data Providers

Data providers enable parameterized testing.

### Basic Data Provider

```php
<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Validators\EmailValidator;

class EmailValidatorTest extends TestCase
{
    private EmailValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new EmailValidator();
    }

    /**
     * @dataProvider validEmailProvider
     */
    public function testValidEmails(string $email): void
    {
        $this->assertTrue($this->validator->isValid($email));
    }

    /**
     * @dataProvider invalidEmailProvider
     */
    public function testInvalidEmails(string $email): void
    {
        $this->assertFalse($this->validator->isValid($email));
    }

    /**
     * Provides valid email addresses
     */
    public static function validEmailProvider(): array
    {
        return [
            ['user@example.com'],
            ['john.doe@example.com'],
            ['user+tag@example.com'],
            ['user123@subdomain.example.com'],
        ];
    }

    /**
     * Provides invalid email addresses
     */
    public static function invalidEmailProvider(): array
    {
        return [
            ['invalid'],
            ['@example.com'],
            ['user@'],
            ['user @example.com'],
            ['user@.com'],
        ];
    }
}
```

### Named Data Sets

```php
<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Calculator;

class CalculatorTest extends TestCase
{
    private Calculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new Calculator();
    }

    /**
     * @dataProvider additionProvider
     */
    public function testAddition(int $a, int $b, int $expected): void
    {
        $result = $this->calculator->add($a, $b);
        $this->assertEquals($expected, $result);
    }

    /**
     * Provides test cases for addition
     */
    public static function additionProvider(): array
    {
        return [
            'positive numbers' => [2, 3, 5],
            'negative numbers' => [-2, -3, -5],
            'mixed signs' => [5, -3, 2],
            'with zero' => [0, 5, 5],
            'large numbers' => [1000, 2000, 3000],
        ];
    }
}

// Output shows named data sets:
// testAddition with data set "positive numbers"
// testAddition with data set "negative numbers"
// etc.
```

### Complex Data Providers

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\PriceCalculator;

class PriceCalculatorTest extends TestCase
{
    private PriceCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new PriceCalculator();
    }

    /**
     * @dataProvider priceCalculationProvider
     */
    public function testPriceCalculation(
        float $basePrice,
        float $taxRate,
        float $discount,
        float $expected
    ): void {
        $result = $this->calculator->calculateFinalPrice(
            $basePrice,
            $taxRate,
            $discount
        );

        $this->assertEquals($expected, $result);
    }

    public static function priceCalculationProvider(): array
    {
        return [
            'no tax, no discount' => [100.00, 0.00, 0.00, 100.00],
            'with 10% tax' => [100.00, 0.10, 0.00, 110.00],
            'with 20% discount' => [100.00, 0.00, 0.20, 80.00],
            'tax and discount' => [100.00, 0.10, 0.20, 88.00],
            'free item' => [0.00, 0.10, 0.00, 0.00],
        ];
    }

    /**
     * @dataProvider bulkOrderProvider
     */
    public function testBulkOrderDiscount(
        int $quantity,
        float $unitPrice,
        array $tiers,
        float $expected
    ): void {
        $result = $this->calculator->calculateBulkPrice(
            $quantity,
            $unitPrice,
            $tiers
        );

        $this->assertEquals($expected, $result);
    }

    public static function bulkOrderProvider(): array
    {
        $discountTiers = [
            10 => 0.05,  // 5% discount for 10+
            50 => 0.10,  // 10% discount for 50+
            100 => 0.15, // 15% discount for 100+
        ];

        return [
            'small order' => [5, 10.00, $discountTiers, 50.00],
            'tier 1 discount' => [15, 10.00, $discountTiers, 142.50],
            'tier 2 discount' => [60, 10.00, $discountTiers, 540.00],
            'tier 3 discount' => [150, 10.00, $discountTiers, 1275.00],
        ];
    }
}
```

---

## Section 6: Mocking and Test Doubles

Mocking isolates the unit under test from its dependencies.

### Types of Test Doubles

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\{UserService, EmailService};
use App\Repositories\UserRepository;
use App\Models\User;

class TestDoublesExampleTest extends TestCase
{
    /**
     * Dummy - Object passed but never used
     */
    public function testDummy(): void
    {
        $dummyLogger = $this->createMock(LoggerInterface::class);

        // Logger is required but not used in this test
        $service = new UserService(
            $this->createMock(UserRepository::class),
            $dummyLogger
        );

        $this->assertInstanceOf(UserService::class, $service);
    }

    /**
     * Stub - Provides canned answers to calls
     */
    public function testStub(): void
    {
        $user = new User(['id' => 1, 'name' => 'John']);

        // Create stub that returns predefined value
        $repositoryStub = $this->createStub(UserRepository::class);
        $repositoryStub->method('findById')->willReturn($user);

        $service = new UserService($repositoryStub);
        $result = $service->findById(1);

        $this->assertEquals('John', $result->name);
    }

    /**
     * Mock - Verifies behavior (method calls, arguments)
     */
    public function testMock(): void
    {
        $user = new User(['id' => 1, 'name' => 'John', 'email' => 'john@example.com']);

        // Create mock with expectations
        $emailMock = $this->createMock(EmailService::class);
        $emailMock
            ->expects($this->once())
            ->method('send')
            ->with(
                $this->equalTo('john@example.com'),
                $this->stringContains('Welcome')
            );

        $repositoryStub = $this->createStub(UserRepository::class);
        $repositoryStub->method('create')->willReturn($user);

        $service = new UserService($repositoryStub, $emailMock);
        $service->registerUser(['name' => 'John', 'email' => 'john@example.com']);

        // Mock automatically verifies expectations
    }

    /**
     * Spy - Records information about calls
     */
    public function testSpy(): void
    {
        $user = new User(['id' => 1, 'name' => 'John']);

        $repositorySpy = $this->createMock(UserRepository::class);
        $repositorySpy->method('save')->willReturn(true);

        $service = new UserService($repositorySpy);
        $service->updateUser(1, ['name' => 'Jane']);

        // Verify spy was called
        $this->assertEquals(1, $repositorySpy->getInvocationCount('save'));
    }

    /**
     * Fake - Working implementation (simplified)
     */
    public function testFake(): void
    {
        // Use in-memory repository instead of database
        $fakeRepository = new InMemoryUserRepository();

        $service = new UserService($fakeRepository);
        $user = $service->createUser(['name' => 'John']);

        $this->assertNotNull($user->id);
        $this->assertEquals('John', $user->name);

        // Verify it was stored
        $retrieved = $service->findById($user->id);
        $this->assertEquals($user->id, $retrieved->id);
    }
}
```

### Mock Configuration

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\UserService;
use App\Repositories\UserRepository;
use App\Contracts\{LoggerInterface, CacheInterface};
use App\Models\User;

class UserServiceTest extends TestCase
{
    private UserRepository $repository;
    private LoggerInterface $logger;
    private CacheInterface $cache;
    private UserService $userService;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(UserRepository::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);

        $this->userService = new UserService(
            $this->repository,
            $this->logger,
            $this->cache
        );
    }

    public function testFindByIdUsesCache(): void
    {
        $user = new User(['id' => 1, 'name' => 'John']);

        // Expect cache to be checked
        $this->cache
            ->expects($this->once())
            ->method('get')
            ->with('user:1')
            ->willReturn($user);

        // Repository should not be called
        $this->repository
            ->expects($this->never())
            ->method('findById');

        $result = $this->userService->findById(1);

        $this->assertSame($user, $result);
    }

    public function testFindByIdLoadsFromDatabaseWhenCacheMisses(): void
    {
        $user = new User(['id' => 1, 'name' => 'John']);

        // Cache miss
        $this->cache
            ->expects($this->once())
            ->method('get')
            ->with('user:1')
            ->willReturn(null);

        // Load from database
        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($user);

        // Store in cache
        $this->cache
            ->expects($this->once())
            ->method('set')
            ->with('user:1', $user, 3600);

        $result = $this->userService->findById(1);

        $this->assertEquals('John', $result->name);
    }

    public function testCreateUserLogsSuccess(): void
    {
        $userData = ['name' => 'John', 'email' => 'john@example.com'];
        $user = new User(array_merge($userData, ['id' => 1]));

        $this->repository
            ->method('create')
            ->willReturn($user);

        // Expect logger to be called with specific message
        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with(
                $this->stringContains('User created'),
                $this->arrayHasKey('user_id')
            );

        $this->userService->createUser($userData);
    }

    public function testCreateUserThrowsExceptionOnDuplicateEmail(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('create')
            ->willThrowException(new \RuntimeException('Duplicate email'));

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Failed to create user'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Duplicate email');

        $this->userService->createUser([
            'name' => 'John',
            'email' => 'john@example.com',
        ]);
    }
}
```

### Partial Mocks

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\PaymentService;

class PaymentServiceTest extends TestCase
{
    public function testProcessPaymentCallsCharge(): void
    {
        // Create partial mock - only mock specific methods
        $paymentService = $this->getMockBuilder(PaymentService::class)
            ->onlyMethods(['charge'])
            ->getMock();

        $paymentService
            ->expects($this->once())
            ->method('charge')
            ->with(100.00)
            ->willReturn(true);

        // Other methods work normally
        $result = $paymentService->processPayment(100.00);

        $this->assertTrue($result);
    }
}
```

---

## Section 7: Code Coverage

Code coverage measures how much code is executed by tests.

### Generating Coverage Reports

```bash
# Generate HTML coverage report
./vendor/bin/phpunit --coverage-html coverage/html

# Generate text coverage summary
./vendor/bin/phpunit --coverage-text

# Generate Clover XML (for CI/CD)
./vendor/bin/phpunit --coverage-clover coverage/clover.xml
```

### Coverage Annotations

```php
<?php

declare(strict_types=1);

namespace App\Services;

class UserService
{
    /**
     * @codeCoverageIgnore
     */
    public function debugMethod(): void
    {
        // This method won't be included in coverage
        var_dump($this->users);
    }

    public function processUser(User $user): void
    {
        // Normal method - included in coverage
    }

    // @codeCoverageIgnoreStart
    private function legacyCode(): void
    {
        // This entire section is ignored
    }

    private function moreLegeacyCode(): void
    {
        // Also ignored
    }
    // @codeCoverageIgnoreEnd

    public function normalMethod(): void
    {
        // Back to normal coverage tracking
    }
}
```

### Test Coverage Example

```php
<?php

declare(strict_types=1);

namespace App\Services;

class MathService
{
    public function calculate(string $operation, float $a, float $b): float
    {
        return match ($operation) {
            'add' => $this->add($a, $b),
            'subtract' => $this->subtract($a, $b),
            'multiply' => $this->multiply($a, $b),
            'divide' => $this->divide($a, $b),
            default => throw new \InvalidArgumentException("Unknown operation: {$operation}")
        };
    }

    private function add(float $a, float $b): float
    {
        return $a + $b;
    }

    private function subtract(float $a, float $b): float
    {
        return $a - $b;
    }

    private function multiply(float $a, float $b): float
    {
        return $a * $b;
    }

    private function divide(float $a, float $b): float
    {
        if ($b === 0.0) {
            throw new \DivisionByZeroError('Division by zero');
        }
        return $a / $b;
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\MathService;

class MathServiceTest extends TestCase
{
    private MathService $service;

    protected function setUp(): void
    {
        $this->service = new MathService();
    }

    /**
     * @dataProvider calculationProvider
     */
    public function testCalculate(
        string $operation,
        float $a,
        float $b,
        float $expected
    ): void {
        $result = $this->service->calculate($operation, $a, $b);
        $this->assertEquals($expected, $result);
    }

    public static function calculationProvider(): array
    {
        return [
            'addition' => ['add', 5, 3, 8],
            'subtraction' => ['subtract', 5, 3, 2],
            'multiplication' => ['multiply', 5, 3, 15],
            'division' => ['divide', 6, 3, 2],
        ];
    }

    public function testDivisionByZero(): void
    {
        $this->expectException(\DivisionByZeroError::class);
        $this->service->calculate('divide', 10, 0);
    }

    public function testInvalidOperation(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown operation');
        $this->service->calculate('modulo', 10, 3);
    }
}

// This test suite achieves 100% code coverage for MathService
```

---

## Section 8: Testing Best Practices

Apply proven testing principles for maintainable tests.

### FIRST Principles

```php
<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\UserService;

/**
 * FIRST Principles:
 * F - Fast
 * I - Independent
 * R - Repeatable
 * S - Self-validating
 * T - Timely
 */
class FIRSTExampleTest extends TestCase
{
    /**
     * FAST - Tests should run quickly
     * Avoid: Database calls, file I/O, network requests
     * Use: Mocks, stubs, in-memory implementations
     */
    public function testFast(): void
    {
        // Mock external dependencies
        $repository = $this->createStub(UserRepository::class);
        $repository->method('findById')->willReturn(new User(['id' => 1]));

        $service = new UserService($repository);
        $user = $service->findById(1);

        $this->assertEquals(1, $user->id);
        // Runs in milliseconds, not seconds
    }

    /**
     * INDEPENDENT - Tests should not depend on each other
     * Each test should set up its own state
     */
    public function testIndependent1(): void
    {
        // Fresh service instance
        $service = new UserService($this->createStub(UserRepository::class));
        $this->assertNotNull($service);
    }

    public function testIndependent2(): void
    {
        // Independent from testIndependent1
        $service = new UserService($this->createStub(UserRepository::class));
        $this->assertNotNull($service);
    }

    /**
     * REPEATABLE - Same result every time
     * Avoid: Time-dependent code, random values, external dependencies
     */
    public function testRepeatable(): void
    {
        $service = new UserService($this->createStub(UserRepository::class));

        // Will always produce same result
        $result = $service->calculateAge(new \DateTime('1990-01-01'));

        // Use fixed dates for predictable results
        $this->assertIsInt($result);
    }

    /**
     * SELF-VALIDATING - Test has boolean output (pass/fail)
     * Avoid: Manual verification, visual inspection
     */
    public function testSelfValidating(): void
    {
        $service = new UserService($this->createStub(UserRepository::class));

        // Clear pass/fail - no manual verification needed
        $this->assertTrue($service->isValid(['name' => 'John']));
        $this->assertFalse($service->isValid(['name' => '']));
    }

    /**
     * TIMELY - Write tests at the right time
     * Ideally: Before or during implementation (TDD)
     * At least: Before moving to next feature
     */
}
```

### AAA Pattern (Arrange, Act, Assert)

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\OrderService;
use App\Models\{Order, OrderItem};

class AAAPatternTest extends TestCase
{
    public function testCalculateOrderTotal(): void
    {
        // ARRANGE - Set up test data and dependencies
        $items = [
            new OrderItem(['name' => 'Product A', 'price' => 10.00, 'quantity' => 2]),
            new OrderItem(['name' => 'Product B', 'price' => 15.00, 'quantity' => 1]),
        ];
        $order = new Order(['items' => $items]);
        $service = new OrderService();

        // ACT - Execute the method under test
        $total = $service->calculateTotal($order);

        // ASSERT - Verify the result
        $this->assertEquals(35.00, $total);
    }

    public function testApplyDiscountCode(): void
    {
        // ARRANGE
        $order = new Order([
            'subtotal' => 100.00,
            'discount_code' => null,
        ]);
        $discountService = $this->createStub(DiscountService::class);
        $discountService->method('getDiscount')->willReturn(0.10); // 10% discount

        $service = new OrderService($discountService);

        // ACT
        $finalTotal = $service->applyDiscount($order, 'SAVE10');

        // ASSERT
        $this->assertEquals(90.00, $finalTotal);
        $this->assertEquals('SAVE10', $order->discount_code);
    }
}
```

### Test Organization

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\UserService;
use App\Exceptions\{UserNotFoundException, DuplicateEmailException};

/**
 * Well-organized test class:
 * - Group related tests together
 * - Use descriptive test names
 * - One concept per test
 * - Clear setup and teardown
 */
class UserServiceTest extends TestCase
{
    private UserService $userService;

    protected function setUp(): void
    {
        $this->userService = new UserService(
            $this->createMock(UserRepository::class),
            $this->createMock(EmailService::class),
            $this->createMock(LoggerInterface::class)
        );
    }

    // Group 1: User Creation Tests
    public function testCreateUserWithValidDataSucceeds(): void
    {
        // Test implementation
    }

    public function testCreateUserWithDuplicateEmailThrowsException(): void
    {
        // Test implementation
    }

    public function testCreateUserWithInvalidEmailThrowsException(): void
    {
        // Test implementation
    }

    // Group 2: User Retrieval Tests
    public function testFindUserByIdReturnsUser(): void
    {
        // Test implementation
    }

    public function testFindUserByIdWithNonExistentIdThrowsException(): void
    {
        // Test implementation
    }

    public function testFindUserByEmailReturnsUser(): void
    {
        // Test implementation
    }

    // Group 3: User Update Tests
    public function testUpdateUserWithValidDataSucceeds(): void
    {
        // Test implementation
    }

    public function testUpdateUserWithNonExistentIdThrowsException(): void
    {
        // Test implementation
    }

    // Group 4: User Deletion Tests
    public function testDeleteUserSucceeds(): void
    {
        // Test implementation
    }

    public function testDeleteUserWithNonExistentIdThrowsException(): void
    {
        // Test implementation
    }
}
```

---

## Section 9: Testing Strategies

Different scenarios require different testing approaches.

### Testing Private Methods

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\PasswordService;

class PasswordServiceTest extends TestCase
{
    /**
     * Approach 1: Test through public interface (preferred)
     */
    public function testPasswordHashingThroughPublicMethod(): void
    {
        $service = new PasswordService();

        $hashedPassword = $service->hashPassword('secret123');

        // Verify hash works without testing private method directly
        $this->assertTrue($service->verifyPassword('secret123', $hashedPassword));
    }

    /**
     * Approach 2: Use reflection (when necessary)
     */
    public function testPrivateMethodUsingReflection(): void
    {
        $service = new PasswordService();

        // Get private method via reflection
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('generateSalt');
        $method->setAccessible(true);

        // Invoke private method
        $salt = $method->invoke($service);

        $this->assertIsString($salt);
        $this->assertEquals(32, strlen($salt));
    }
}
```

### Testing Static Methods

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Utilities;

use PHPUnit\Framework\TestCase;
use App\Utilities\StringHelper;

class StringHelperTest extends TestCase
{
    /**
     * @dataProvider slugProvider
     */
    public function testSlugify(string $input, string $expected): void
    {
        $result = StringHelper::slugify($input);
        $this->assertEquals($expected, $result);
    }

    public static function slugProvider(): array
    {
        return [
            ['Hello World', 'hello-world'],
            ['PHP for Java Developers', 'php-for-java-developers'],
            ['Special!@#$%Characters', 'specialcharacters'],
            ['  Multiple   Spaces  ', 'multiple-spaces'],
        ];
    }
}
```

### Testing Abstract Classes

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use PHPUnit\Framework\TestCase;
use App\Repositories\AbstractRepository;

class AbstractRepositoryTest extends TestCase
{
    public function testFindByIdCallsCorrectQuery(): void
    {
        // Create anonymous class extending abstract class
        $repository = new class extends AbstractRepository {
            protected function getTable(): string
            {
                return 'test_table';
            }
        };

        // Inject mocked PDO
        $pdo = $this->createMock(\PDO::class);
        $stmt = $this->createMock(\PDOStatement::class);

        $pdo->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM test_table WHERE id = ?')
            ->willReturn($stmt);

        $reflection = new \ReflectionClass($repository);
        $property = $reflection->getProperty('pdo');
        $property->setAccessible(true);
        $property->setValue($repository, $pdo);

        $repository->findById(1);
    }
}
```

### Testing Exceptions

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Services\FileService;
use App\Exceptions\FileNotFoundException;

class ExceptionTestingTest extends TestCase
{
    /**
     * Method 1: Using expectException
     */
    public function testFileNotFoundThrowsException(): void
    {
        $service = new FileService();

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File not found');
        $this->expectExceptionCode(404);

        $service->readFile('/nonexistent/file.txt');
    }

    /**
     * Method 2: Using try-catch (when you need to test after exception)
     */
    public function testExceptionWithAdditionalAssertions(): void
    {
        $service = new FileService();

        try {
            $service->readFile('/nonexistent/file.txt');
            $this->fail('Expected exception was not thrown');
        } catch (FileNotFoundException $e) {
            $this->assertEquals('File not found', $e->getMessage());
            $this->assertEquals(404, $e->getCode());
            $this->assertInstanceOf(\RuntimeException::class, $e);
        }
    }

    /**
     * Method 3: Testing no exception is thrown
     */
    public function testValidFileDoesNotThrowException(): void
    {
        $service = new FileService();

        // Create temporary file
        $file = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($file, 'content');

        try {
            $content = $service->readFile($file);
            $this->assertEquals('content', $content);
        } finally {
            unlink($file);
        }
    }
}
```

---

## Section 10: Integration with CI/CD

Automate tests in continuous integration pipelines.

### GitHub Actions Example

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    strategy:
      matrix:
        php-version: ['8.1', '8.2', '8.3']

    steps:
      - name: Checkout code
        uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php-version }}
          extensions: mbstring, pdo, pdo_mysql
          coverage: xdebug

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress

      - name: Run tests
        run: vendor/bin/phpunit --coverage-text --coverage-clover=coverage.xml

      - name: Upload coverage to Codecov
        uses: codecov/codecov-action@v3
        with:
          files: ./coverage.xml
```

### Running Tests in Docker

```dockerfile
# Dockerfile.test
FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader

COPY . .
RUN composer dump-autoload

CMD ["vendor/bin/phpunit"]
```

```bash
# Build and run tests
docker build -f Dockerfile.test -t my-app-tests .
docker run --rm my-app-tests
```

---

## Exercises

Practice unit testing concepts:

### Exercise 1: Write Tests for Calculator

Create comprehensive tests for a calculator class:

```php
<?php

declare(strict_types=1);

namespace App;

class Calculator
{
    public function add(float $a, float $b): float
    {
        return $a + $b;
    }

    public function subtract(float $a, float $b): float
    {
        return $a - $b;
    }

    public function multiply(float $a, float $b): float
    {
        return $a * $b;
    }

    public function divide(float $a, float $b): float
    {
        if ($b === 0.0) {
            throw new \DivisionByZeroError();
        }
        return $a / $b;
    }

    public function percentage(float $value, float $percentage): float
    {
        return ($value * $percentage) / 100;
    }
}

// TODO: Write comprehensive test suite
// - Test all methods with various inputs
// - Use data providers
// - Test edge cases
// - Achieve 100% code coverage
```

### Exercise 2: Mock Dependencies

Test this service with mocked dependencies:

```php
<?php

declare(strict_types=1);

namespace App\Services;

class OrderService
{
    public function __construct(
        private OrderRepository $orders,
        private PaymentGateway $payment,
        private EmailService $email,
        private LoggerInterface $logger
    ) {}

    public function processOrder(Order $order): bool
    {
        try {
            // Charge payment
            $charged = $this->payment->charge(
                $order->getTotal(),
                $order->getPaymentMethod()
            );

            if (!$charged) {
                $this->logger->error('Payment failed', ['order_id' => $order->id]);
                return false;
            }

            // Save order
            $this->orders->save($order);

            // Send confirmation email
            $this->email->sendOrderConfirmation($order);

            $this->logger->info('Order processed', ['order_id' => $order->id]);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Order processing failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}

// TODO: Write tests that:
// - Mock all dependencies
// - Test successful order processing
// - Test payment failure
// - Test exception handling
// - Verify all method calls and arguments
```

---

## Common Pitfalls

**❌ Testing Implementation Instead of Behavior**

```php
<?php
// Bad - Testing implementation details
public function testUserRepositoryUsesCorrectQuery(): void
{
    // Don't test how it works internally
    $this->assertStringContainsString('SELECT * FROM users', $this->repository->getQuery());
}

// Good - Testing behavior
public function testFindUserByIdReturnsCorrectUser(): void
{
    // Test what it does, not how
    $user = $this->repository->findById(1);
    $this->assertEquals('John', $user->name);
}
```

**❌ Brittle Tests (Too Many Mocks)**

```php
<?php
// Bad - Overly complex mocking
public function testComplexInteraction(): void
{
    $mock1 = $this->createMock(Service1::class);
    $mock2 = $this->createMock(Service2::class);
    $mock3 = $this->createMock(Service3::class);
    // ... many more mocks
    // Test becomes brittle and hard to maintain
}

// Good - Test at appropriate level
public function testBusinessLogic(): void
{
    // Use minimal mocking, test real behavior
    $service = new MyService($this->createMock(DatabaseInterface::class));
    $result = $service->process($data);
    $this->assertEquals($expected, $result);
}
```

**❌ Not Testing Edge Cases**

```php
<?php
// Bad - Only testing happy path
public function testDivision(): void
{
    $this->assertEquals(2, $this->calculator->divide(4, 2));
}

// Good - Testing edge cases
public function testDivisionByZero(): void
{
    $this->expectException(\DivisionByZeroError::class);
    $this->calculator->divide(4, 0);
}

public function testDivisionWithNegatives(): void
{
    $this->assertEquals(-2, $this->calculator->divide(-4, 2));
    $this->assertEquals(-2, $this->calculator->divide(4, -2));
    $this->assertEquals(2, $this->calculator->divide(-4, -2));
}
```

---

## Best Practices Summary

✅ **Follow FIRST principles** - Fast, Independent, Repeatable, Self-validating, Timely
✅ **Use AAA pattern** - Arrange, Act, Assert
✅ **Test behavior, not implementation** - Focus on what, not how
✅ **One assertion per concept** - Keep tests focused
✅ **Use descriptive test names** - Test names should describe scenario and expectation
✅ **Mock external dependencies** - Isolate units under test
✅ **Use data providers** - Reduce duplication, test multiple scenarios
✅ **Aim for high coverage** - But don't worship 100%
✅ **Test edge cases** - Empty, null, zero, negative, very large
✅ **Keep tests maintainable** - Tests are code too

---

## Further Reading

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [PHPUnit Best Practices](https://phpunit.de/manual/current/en/writing-tests-for-phpunit.html)
- [Test-Driven Development](https://en.wikipedia.org/wiki/Test-driven_development)
- [FIRST Principles](https://github.com/ghsukumar/SFDC_Best_Practices/wiki/F.I.R.S.T-Principles-of-Unit-Testing)
- [PHPUnit GitHub Repository](https://github.com/sebastianbergmann/phpunit)

---

## Chapter Wrap-up Checklist

Before moving to the next chapter, ensure you can:

- [ ] Install and configure PHPUnit in a project
- [ ] Write basic unit tests with assertions
- [ ] Use test fixtures to set up test environments
- [ ] Create data providers for parameterized tests
- [ ] Mock dependencies to isolate units under test
- [ ] Configure and interpret code coverage reports
- [ ] Apply FIRST principles to test design
- [ ] Use AAA pattern for test structure
- [ ] Test exceptions and edge cases
- [ ] Integrate tests with CI/CD pipelines

---

<div style="display: flex; justify-content: space-between; margin-top: 2rem;">
  <div>
    <strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/11-dependency-injection">← Chapter 11</a>
  </div>
  <div><strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/13-integration-testing">Chapter 13 →</a></div>
</div>
