---
title: "03: OOP Basics"
description: "Master PHP's object-oriented programming with detailed comparisons to Java's OOP model"
sidebar:
  label: "03: OOP Basics"
  order: 3
  badge:
    text: Beginner
    variant: success
---

![OOP Basics Hero](/images/php-for-java-developers/chapter-03-oop-basics-hero-full.webp)

# Chapter 3: OOP Basics

<Badge type="tip">Beginner</Badge> <Badge type="info">75-90 min</Badge>

## Overview

If you're comfortable with Java's OOP model, you'll feel right at home with PHP's object-oriented features. PHP supports classes, inheritance, interfaces, and many other OOP concepts you already know. The syntax is similar, with some PHP-specific enhancements that make certain tasks easier.

In this chapter, we'll explore PHP's core OOP features, always comparing them to Java so you can quickly understand the similarities and differences.

## Prerequisites

::: info Time Estimate
⏱️ **75-90 minutes** to complete this chapter
:::

**What you need:**

- Completed [Chapter 2: Control Flow & Functions](/series/php-for-java-developers/chapters/02-control-flow-and-functions)
- Solid understanding of Java's OOP concepts
- Familiarity with Java classes, objects, and methods

## What You'll Build

In this chapter, you'll create:

- A Product class with various property types
- A ShoppingCart class demonstrating encapsulation
- A Logger class with static methods
- A comprehensive example combining OOP concepts

## Learning Objectives

By the end of this chapter, you'll be able to:

- **Define classes** with properties and methods in PHP
- **Use constructors** including property promotion (PHP 8+)
- **Apply visibility modifiers** (public, private, protected)
- **Create static members** (properties and methods)
- **Work with class constants** and enums
- **Understand `$this`** and `self` keywords
- **Use magic methods** for advanced functionality

---

## Section 1: Classes and Objects

### Goal

Understand how to define and instantiate classes in PHP vs Java.

### Basic Class Definition

::: code-group

```php [PHP Class]
<?php

declare(strict_types=1);

class Product
{
    // Properties (like Java fields)
    public string $name;
    public float $price;
    public int $quantity;

    // Constructor
    public function __construct(string $name, float $price, int $quantity)
    {
        $this->name = $name;
        $this->price = $price;
        $this->quantity = $quantity;
    }

    // Method
    public function getTotalValue(): float
    {
        return $this->price * $this->quantity;
    }

    // Method with no return value
    public function display(): void
    {
        echo "{$this->name}: \${$this->price} x {$this->quantity}\n";
    }
}

// Instantiate (identical to Java)
$product = new Product("Laptop", 999.99, 5);
echo $product->getTotalValue();  // 4999.95
$product->display();
```

```java [Java Class]
public class Product {
    // Fields
    public String name;
    public double price;
    public int quantity;

    // Constructor
    public Product(String name, double price, int quantity) {
        this.name = name;
        this.price = price;
        this.quantity = quantity;
    }

    // Method
    public double getTotalValue() {
        return this.price * this.quantity;
    }

    // Method with no return value
    public void display() {
        System.out.println(name + ": $" + price + " x " + quantity);
    }
}

// Instantiate (identical syntax!)
Product product = new Product("Laptop", 999.99, 5);
System.out.println(product.getTotalValue());
product.display();
```

:::

### Key Differences

| Feature                  | PHP                               | Java                  |
| ------------------------ | --------------------------------- | --------------------- |
| **Property declaration** | `public string $name;`            | `public String name;` |
| **Constructor name**     | `__construct`                     | Class name            |
| **This reference**       | `$this->property`                 | `this.property`       |
| **Member access**        | `->` operator                     | `.` operator          |
| **Type hints**           | Optional (but recommended)        | Required              |
| **Visibility**           | Same (public, private, protected) | Same                  |

::: tip Constructor Naming
In PHP, all constructors are named `__construct()`, regardless of the class name. This is different from Java where the constructor shares the class name. This makes refactoring easier—rename your class without changing constructor code!
:::

---

## Section 2: Constructor Property Promotion (PHP 8+)

### Goal

Learn PHP's concise constructor syntax that reduces boilerplate.

### Property Promotion

PHP 8 introduced constructor property promotion, dramatically reducing boilerplate:

::: code-group

```php [PHP 8+ Constructor Promotion]
<?php

declare(strict_types=1);

// Modern PHP 8+ way (concise!)
class Product
{
    public function __construct(
        public string $name,
        public float $price,
        public int $quantity = 0  // Default value
    ) {
        // Properties automatically created and assigned!
        // Constructor body only needed for additional logic
    }

    public function getTotalValue(): float
    {
        return $this->price * $this->quantity;
    }
}

// Usage (same as before)
$product = new Product("Laptop", 999.99, 5);
echo $product->name;  // "Laptop"
```

```php [PHP 7.4 (Old Way)]
<?php

declare(strict_types=1);

// Old way (verbose)
class Product
{
    public string $name;
    public float $price;
    public int $quantity;

    public function __construct(
        string $name,
        float $price,
        int $quantity = 0
    ) {
        $this->name = $name;
        $this->price = $price;
        $this->quantity = $quantity;
    }
}
```

```java [Java (Verbose)]
// Java requires explicit field declaration and assignment
public class Product {
    public String name;
    public double price;
    public int quantity;

    public Product(String name, double price, int quantity) {
        this.name = name;
        this.price = price;
        this.quantity = quantity;
    }
}

// Or use Java 14+ Records (immutable)
public record Product(
    String name,
    double price,
    int quantity
) {}
```

:::

::: tip Constructor Promotion Benefits

1. **Less boilerplate**: No need to declare properties separately
2. **Less repetition**: Don't write property name 3 times
3. **Cleaner code**: Intent is immediately clear
4. **Same functionality**: Works exactly like traditional constructors

**When to use:**

- Simple data classes (DTOs, value objects)
- When properties map directly to constructor parameters
- When you want concise, readable code
  :::

---

## Section 3: Named Constructors & Readonly Properties

### Goal

Learn advanced constructor patterns and immutability with readonly properties.

### Named Constructors (Static Factory Methods)

PHP allows static factory methods as alternative constructors, just like Java:

::: code-group

```php [PHP Named Constructors]
<?php

declare(strict_types=1);

class Money
{
    private function __construct(
        private float $amount,
        private string $currency
    ) {}

    // Named constructor: create from dollars
    public static function fromDollars(float $amount): self
    {
        return new self($amount, 'USD');
    }

    // Named constructor: create from euros
    public static function fromEuros(float $amount): self
    {
        return new self($amount, 'EUR');
    }

    // Named constructor: parse from string
    public static function parse(string $money): self
    {
        // Parse "$100.00" or "€50.00"
        preg_match('/^([$€])(\d+(?:\.\d{2})?)$/', $money, $matches);

        if (empty($matches)) {
            throw new InvalidArgumentException("Invalid money format: $money");
        }

        $currency = $matches[1] === '$' ? 'USD' : 'EUR';
        $amount = (float) $matches[2];

        return new self($amount, $currency);
    }

    // Named constructor: create zero amount
    public static function zero(string $currency = 'USD'): self
    {
        return new self(0.0, $currency);
    }

    public function format(): string
    {
        $symbol = $this->currency === 'USD' ? '$' : '€';
        return sprintf('%s%.2f', $symbol, $this->amount);
    }
}

// Usage - much more readable than constructor!
$price1 = Money::fromDollars(100.50);
$price2 = Money::fromEuros(85.00);
$price3 = Money::parse('$42.99');
$price4 = Money::zero('EUR');

echo $price1->format();  // $100.50
echo $price3->format();  // $42.99
```

```java [Java Named Constructors]
public class Money {
    private final double amount;
    private final String currency;

    private Money(double amount, String currency) {
        this.amount = amount;
        this.currency = currency;
    }

    // Static factory methods
    public static Money fromDollars(double amount) {
        return new Money(amount, "USD");
    }

    public static Money fromEuros(double amount) {
        return new Money(amount, "EUR");
    }

    public static Money parse(String money) {
        // Parse "$100.00" or "€50.00"
        String currency = money.startsWith("$") ? "USD" : "EUR";
        double amount = Double.parseDouble(money.substring(1));
        return new Money(amount, currency);
    }

    public static Money zero(String currency) {
        return new Money(0.0, currency);
    }

    public String format() {
        String symbol = currency.equals("USD") ? "$" : "€";
        return String.format("%s%.2f", symbol, amount);
    }
}

// Usage
Money price1 = Money.fromDollars(100.50);
Money price2 = Money.fromEuros(85.00);
Money price3 = Money.parse("$42.99");
```

:::

::: tip Named Constructor Benefits

1. **Descriptive names**: `Money::fromDollars()` is clearer than `new Money(100, 'USD')`
2. **Multiple ways to create**: Different static methods for different input types
3. **Validation**: Centralized in factory methods
4. **Flexibility**: Can return cached instances or subclasses
5. **Private constructor**: Force use of factory methods
   :::

### Readonly Properties (PHP 8.1+)

PHP 8.1 introduced readonly properties for immutability:

::: code-group

```php [PHP Readonly Properties]
<?php

declare(strict_types=1);

class Point
{
    public function __construct(
        public readonly int $x,
        public readonly int $y
    ) {}

    public function distanceFrom(Point $other): float
    {
        $dx = $this->x - $other->x;
        $dy = $this->y - $other->y;
        return sqrt($dx ** 2 + $dy ** 2);
    }
}

$p1 = new Point(0, 0);
$p2 = new Point(3, 4);

echo $p1->x;  // ✅ Can read
echo $p1->distanceFrom($p2);  // 5.0

// ❌ Error: Cannot modify readonly property
// $p1->x = 10;

// To "change" a value, create new instance
$p3 = new Point(10, $p1->y);
```

```php [PHP Readonly Class (8.2+)]
<?php

declare(strict_types=1);

// PHP 8.2+: Entire class is readonly
readonly class User
{
    public function __construct(
        public string $name,
        public string $email,
        public int $age
    ) {}

    public function withEmail(string $newEmail): self
    {
        return new self($this->name, $newEmail, $this->age);
    }
}

$user = new User("Alice", "alice@example.com", 30);

// ❌ All properties are readonly
// $user->email = "new@example.com";

// ✅ Create new instance with changed value
$updated = $user->withEmail("alice.smith@example.com");
```

```java [Java Final Fields]
public class Point {
    public final int x;
    public final int y;

    public Point(int x, int y) {
        this.x = x;
        this.y = y;
    }

    public double distanceFrom(Point other) {
        int dx = this.x - other.x;
        int dy = this.y - other.y;
        return Math.sqrt(dx * dx + dy * dy);
    }
}

// Java 14+ Record (all fields final)
public record Point(int x, int y) {
    public double distanceFrom(Point other) {
        int dx = this.x - other.x;
        int dy = this.y - other.y;
        return Math.sqrt(dx * dx + dy * dy);
    }
}
```

:::

::: tip When to Use Readonly

1. **Value objects**: Money, Date, Point, Color
2. **DTOs**: Data Transfer Objects
3. **Configuration**: Immutable settings
4. **Thread safety**: Immutable objects are thread-safe
5. **Predictability**: Values can't change unexpectedly
   :::

---

## Section 4: Visibility Modifiers

### Goal

Master public, private, and protected visibility in PHP.

### Access Modifiers

PHP and Java have the same three access modifiers:

::: code-group

```php [PHP Visibility]
<?php

declare(strict_types=1);

class BankAccount
{
    // Public: accessible everywhere
    public string $accountNumber;

    // Private: only within this class
    private float $balance;

    // Protected: this class and subclasses
    protected string $accountType;

    public function __construct(
        string $accountNumber,
        float $initialBalance,
        string $accountType
    ) {
        $this->accountNumber = $accountNumber;
        $this->balance = $initialBalance;
        $this->accountType = $accountType;
    }

    // Public method
    public function deposit(float $amount): void
    {
        if ($amount > 0) {
            $this->balance += $amount;
            $this->logTransaction("Deposit: $amount");
        }
    }

    // Public getter (accessor)
    public function getBalance(): float
    {
        return $this->balance;
    }

    // Private method (helper)
    private function logTransaction(string $message): void
    {
        echo "[LOG] {$this->accountNumber}: $message\n";
    }

    // Protected method (for subclasses)
    protected function validateAmount(float $amount): bool
    {
        return $amount > 0;
    }
}

$account = new BankAccount("123456", 1000.00, "savings");
$account->deposit(500);
echo $account->getBalance();  // 1500

// Error: Cannot access private property
// echo $account->balance;

// Error: Cannot call private method
// $account->logTransaction("test");
```

```java [Java Visibility]
public class BankAccount {
    // Public: accessible everywhere
    public String accountNumber;

    // Private: only within this class
    private double balance;

    // Protected: this class, subclasses, and same package
    protected String accountType;

    public BankAccount(
        String accountNumber,
        double initialBalance,
        String accountType
    ) {
        this.accountNumber = accountNumber;
        this.balance = initialBalance;
        this.accountType = accountType;
    }

    // Public method
    public void deposit(double amount) {
        if (amount > 0) {
            this.balance += amount;
            this.logTransaction("Deposit: " + amount);
        }
    }

    // Public getter
    public double getBalance() {
        return this.balance;
    }

    // Private method
    private void logTransaction(String message) {
        System.out.println("[LOG] " + accountNumber + ": " + message);
    }

    // Protected method
    protected boolean validateAmount(double amount) {
        return amount > 0;
    }
}
```

:::

### Visibility Rules

| Modifier      | Same Class | Subclass | Outside Class | PHP Package | Java Package      |
| ------------- | ---------- | -------- | ------------- | ----------- | ----------------- |
| **public**    | ✅         | ✅       | ✅            | ✅          | ✅                |
| **protected** | ✅         | ✅       | ❌            | ❌          | ✅ (same package) |
| **private**   | ✅         | ❌       | ❌            | ❌          | ❌                |

::: warning Key Difference: Protected
In Java, `protected` also grants access to classes in the same package. PHP doesn't have packages (it has namespaces, covered in Chapter 6), so `protected` only applies to the class hierarchy.
:::

### Best Practices

```php
# filename: user-encapsulation.php
<?php

declare(strict_types=1);

// Best practice: encapsulation
class User
{
    // Always make properties private
    private string $email;
    private string $passwordHash;

    public function __construct(string $email, string $password)
    {
        $this->email = $email;
        $this->passwordHash = $this->hashPassword($password);
    }

    // Provide public getters
    public function getEmail(): string
    {
        return $this->email;
    }

    // Setters with validation
    public function setEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email");
        }
        $this->email = $email;
    }

    // Private helper methods
    private function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    // Public method using private helper
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }
}
```

::: tip Encapsulation Guidelines

1. **Make properties private** by default
2. **Provide getters/setters** only when needed
3. **Validate in setters** to maintain object integrity
4. **Use private methods** for internal logic
5. **Keep public API minimal** - easier to maintain
   :::

---

## Section 5: Static Members

### Goal

Understand static properties and methods in PHP.

### Static Properties and Methods

::: code-group

```php [PHP Static]
<?php

declare(strict_types=1);

class Database
{
    // Static property (shared across all instances)
    private static ?Database $instance = null;
    private static int $queryCount = 0;

    // Regular instance property
    private string $connectionString;

    // Private constructor (Singleton pattern)
    private function __construct(string $connectionString)
    {
        $this->connectionString = $connectionString;
    }

    // Static method
    public static function getInstance(string $connectionString = 'default'): self
    {
        if (self::$instance === null) {
            self::$instance = new self($connectionString);
        }
        return self::$instance;
    }

    // Static method
    public static function getQueryCount(): int
    {
        return self::$queryCount;
    }

    // Instance method that modifies static property
    public function query(string $sql): void
    {
        self::$queryCount++;
        echo "Executing: $sql (Query #{self::$queryCount})\n";
    }

    // Static helper method
    public static function sanitize(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
}

// Usage
$db1 = Database::getInstance();
$db1->query("SELECT * FROM users");

$db2 = Database::getInstance();
$db2->query("SELECT * FROM products");

// Same instance (Singleton)
var_dump($db1 === $db2);  // true

// Access static method
echo Database::getQueryCount();  // 2

// Static method without instance
$safe = Database::sanitize("<script>alert('xss')</script>");
```

```java [Java Static]
public class Database {
    // Static field
    private static Database instance = null;
    private static int queryCount = 0;

    // Instance field
    private String connectionString;

    // Private constructor
    private Database(String connectionString) {
        this.connectionString = connectionString;
    }

    // Static method
    public static Database getInstance(String connectionString) {
        if (instance == null) {
            instance = new Database(connectionString);
        }
        return instance;
    }

    // Static method
    public static int getQueryCount() {
        return queryCount;
    }

    // Instance method
    public void query(String sql) {
        queryCount++;
        System.out.println("Executing: " + sql + " (Query #" + queryCount + ")");
    }

    // Static method
    public static String sanitize(String input) {
        return input.replaceAll("<", "&lt;").replaceAll(">", "&gt;");
    }
}

// Usage (identical!)
Database db1 = Database.getInstance("default");
db1.query("SELECT * FROM users");

Database db2 = Database.getInstance("default");
db2.query("SELECT * FROM products");

System.out.println(db1 == db2);  // true
System.out.println(Database.getQueryCount());  // 2
```

:::

### Key Points

| Feature                    | PHP                                                     | Java                                       |
| -------------------------- | ------------------------------------------------------- | ------------------------------------------ |
| **Access static property** | `self::$property`                                       | `ClassName.field`                          |
| **Access static method**   | `self::method()` or `ClassName::method()`               | `ClassName.method()`                       |
| **From instance**          | `self::$property` (not `$this->property`)               | `this.staticField` (works but discouraged) |
| **Inheritance**            | Can be overridden (late static binding with `static::`) | Can be hidden, not overridden              |

::: warning Common Mistake

```php
# filename: static-access-example.php
<?php

class Example
{
    private static int $count = 0;

    public function increment(): void
    {
        // ❌ Wrong: $this->count
        // ✅ Correct: self::$count
        self::$count++;
    }
}
```

You must use `self::` or `static::` to access static members, never `$this->`.
:::

### Late Static Binding (self vs static)

An important difference from Java: PHP has two ways to reference the current class in static context:

```php
# filename: late-static-binding.php
<?php

declare(strict_types=1);

class Animal
{
    protected static string $type = 'Generic Animal';

    // Using self:: - early binding (resolves to Animal)
    public static function getTypeEarly(): string
    {
        return self::$type;
    }

    // Using static:: - late binding (resolves to actual called class)
    public static function getTypeLate(): string
    {
        return static::$type;
    }

    public static function create(): static
    {
        return new static();  // Creates instance of called class
    }
}

class Dog extends Animal
{
    protected static string $type = 'Dog';
}

class Cat extends Animal
{
    protected static string $type = 'Cat';
}

// self:: resolves to the class where it's written (Animal)
echo Animal::getTypeEarly();  // "Generic Animal"
echo Dog::getTypeEarly();     // "Generic Animal" (uses Animal's $type)
echo Cat::getTypeEarly();     // "Generic Animal" (uses Animal's $type)

// static:: resolves to the class being called (late binding)
echo Animal::getTypeLate();   // "Generic Animal"
echo Dog::getTypeLate();      // "Dog" (uses Dog's $type)
echo Cat::getTypeLate();      // "Cat" (uses Cat's $type)

// Creating instances with late static binding
$dog = Dog::create();         // Creates Dog instance
$cat = Cat::create();         // Creates Cat instance
var_dump($dog instanceof Dog);  // true
var_dump($cat instanceof Cat);  // true
```

::: tip When to Use static::
Use `static::` instead of `self::` when:

1. You want subclasses to override the behavior
2. Implementing factory methods that return instances of the called class
3. Building extensible frameworks or base classes
4. You need "runtime class resolution" behavior

Use `self::` when:

1. You specifically want the current class, not subclasses
2. The behavior should NOT be overridden
3. Accessing constants that shouldn't change in subclasses
   :::

**Practical Example: Active Record Pattern**

```php
# filename: active-record-pattern.php
<?php

declare(strict_types=1);

abstract class Model
{
    protected static string $table;

    public static function find(int $id): ?static
    {
        // static::$table resolves to the subclass's table name
        $query = "SELECT * FROM " . static::$table . " WHERE id = ?";

        // Simulate database query
        echo "Query: $query with id=$id\n";

        // Return instance of the actual called class
        return new static();
    }

    public static function all(): array
    {
        $query = "SELECT * FROM " . static::$table;
        echo "Query: $query\n";

        return [new static()];
    }
}

class User extends Model
{
    protected static string $table = 'users';
}

class Product extends Model
{
    protected static string $table = 'products';
}

// Each class uses its own table name
$user = User::find(1);        // SELECT * FROM users WHERE id = 1
$product = Product::find(5);  // SELECT * FROM products WHERE id = 5

var_dump($user instanceof User);        // true
var_dump($product instanceof Product);  // true
```

---

## Section 6: Class Constants and Enums

### Goal

Learn about class constants and PHP's enum feature.

### Class Constants

::: code-group

```php [PHP Constants]
<?php

declare(strict_types=1);

class MathHelper
{
    // Class constants (like Java's static final)
    public const PI = 3.14159;
    public const E = 2.71828;

    // Private constant (PHP 7.1+)
    private const MAX_ITERATIONS = 1000;

    // Protected constant
    protected const DEFAULT_PRECISION = 6;

    public static function calculateCircleArea(float $radius): float
    {
        return self::PI * $radius ** 2;
    }

    public static function getPrecision(): int
    {
        return self::DEFAULT_PRECISION;
    }
}

// Access public constants
echo MathHelper::PI;  // 3.14159
echo MathHelper::calculateCircleArea(10);

// Error: Cannot access private constant
// echo MathHelper::MAX_ITERATIONS;
```

```java [Java Constants]
public class MathHelper {
    // Constants (static final)
    public static final double PI = 3.14159;
    public static final double E = 2.71828;

    // Private constant
    private static final int MAX_ITERATIONS = 1000;

    // Protected constant
    protected static final int DEFAULT_PRECISION = 6;

    public static double calculateCircleArea(double radius) {
        return PI * Math.pow(radius, 2);
    }

    public static int getPrecision() {
        return DEFAULT_PRECISION;
    }
}

// Access constants
System.out.println(MathHelper.PI);
System.out.println(MathHelper.calculateCircleArea(10));
```

:::

### Enums (PHP 8.1+)

PHP 8.1 introduced native enums, similar to Java enums:

::: code-group

```php [PHP Enums]
<?php

declare(strict_types=1);

// Pure enum
enum Status
{
    case PENDING;
    case APPROVED;
    case REJECTED;
}

// Backed enum (with values)
enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    // Methods in enums
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending Payment',
            self::PROCESSING => 'Processing Order',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function canCancel(): bool
    {
        return match($this) {
            self::PENDING, self::PROCESSING => true,
            default => false,
        };
    }
}

// Usage
$status = OrderStatus::PENDING;
echo $status->value;  // 'pending'
echo $status->label();  // 'Pending Payment'
var_dump($status->canCancel());  // true

// Type hint with enum
function updateOrder(OrderStatus $status): void
{
    echo "Order status: {$status->label()}\n";
}

updateOrder(OrderStatus::PROCESSING);

// Get all cases
$allStatuses = OrderStatus::cases();
foreach ($allStatuses as $status) {
    echo "{$status->name}: {$status->value}\n";
}
```

```java [Java Enums]
// Java enum
public enum OrderStatus {
    PENDING("pending"),
    PROCESSING("processing"),
    SHIPPED("shipped"),
    DELIVERED("delivered"),
    CANCELLED("cancelled");

    private final String value;

    OrderStatus(String value) {
        this.value = value;
    }

    public String getValue() {
        return value;
    }

    public String label() {
        return switch(this) {
            case PENDING -> "Pending Payment";
            case PROCESSING -> "Processing Order";
            case SHIPPED -> "Shipped";
            case DELIVERED -> "Delivered";
            case CANCELLED -> "Cancelled";
        };
    }

    public boolean canCancel() {
        return this == PENDING || this == PROCESSING;
    }
}

// Usage
OrderStatus status = OrderStatus.PENDING;
System.out.println(status.getValue());
System.out.println(status.label());
System.out.println(status.canCancel());
```

:::

::: tip Enum Benefits

1. **Type safety**: Can't use invalid values
2. **IDE autocomplete**: See all possible values
3. **Methods**: Add behavior to enum cases
4. **Pattern matching**: Works great with `match` expressions
5. **Backed enums**: Store in database as string/int
   :::

---

## Section 7: Magic Methods

### Goal

Understand PHP's special "magic methods" that provide advanced functionality.

### Common Magic Methods

PHP has special methods that are automatically called in certain situations:

```php
# filename: magic-methods.php
<?php

declare(strict_types=1);

class Product
{
    private array $data = [];

    // Called when creating object
    public function __construct(string $name, float $price)
    {
        $this->data['name'] = $name;
        $this->data['price'] = $price;
    }

    // Called when object is destroyed
    public function __destruct()
    {
        echo "Product {$this->data['name']} is being destroyed\n";
    }

    // Called when accessing undefined property
    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    // Called when setting undefined property
    public function __set(string $name, mixed $value): void
    {
        $this->data[$name] = $value;
    }

    // Called when checking if undefined property exists
    public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }

    // Called when unsetting a property
    public function __unset(string $name): void
    {
        unset($this->data[$name]);
    }

    // Called when converting object to string
    public function __toString(): string
    {
        return "{$this->data['name']}: \${$this->data['price']}";
    }

    // Called when calling object as function
    public function __invoke(int $quantity): float
    {
        return $this->data['price'] * $quantity;
    }

    // Called when object is cloned
    public function __clone(): void
    {
        // Deep copy behavior
        $this->data = array_merge([], $this->data);
    }

    // Called when calling undefined method
    public function __call(string $method, array $args): mixed
    {
        echo "Called undefined method: $method\n";
        return null;
    }

    // Called when calling undefined static method
    public static function __callStatic(string $method, array $args): mixed
    {
        echo "Called undefined static method: $method\n";
        return null;
    }

    // Called by var_dump() and print_r()
    public function __debugInfo(): array
    {
        return [
            'name' => $this->data['name'],
            'price' => $this->data['price'],
            'formatted' => $this->__toString()
        ];
    }

    // Called during serialization
    public function __serialize(): array
    {
        return $this->data;
    }

    // Called during unserialization
    public function __unserialize(array $data): void
    {
        $this->data = $data;
    }
}

// Usage
$product = new Product("Laptop", 999.99);

// __get
echo $product->name;  // "Laptop" (calls __get)

// __set
$product->category = "Electronics";  // calls __set

// __isset
var_dump(isset($product->category));  // true

// __unset
unset($product->category);

// __toString
echo $product;  // "Laptop: $999.99"

// __invoke
echo $product(5);  // 4999.95 (price * quantity)

// __call
$product->undefinedMethod();  // "Called undefined method: undefinedMethod"

// __debugInfo
var_dump($product);  // Shows customized debug output

// __serialize / __unserialize
$serialized = serialize($product);
$unserialized = unserialize($serialized);

// __clone
$clone = clone $product;
```

### Advanced Magic Method Example: Fluent API Builder

```php
# filename: fluent-query-builder.php
<?php

declare(strict_types=1);

class QueryBuilder
{
    private array $parts = [];

    // __call for fluent methods
    public function __call(string $method, array $args): self
    {
        // Support select(), where(), orderBy(), etc.
        $this->parts[$method] = $args;
        return $this;
    }

    // __toString to build final query
    public function __toString(): string
    {
        $query = "SELECT";

        if (isset($this->parts['select'])) {
            $query .= " " . implode(', ', $this->parts['select']);
        } else {
            $query .= " *";
        }

        if (isset($this->parts['from'])) {
            $query .= " FROM " . $this->parts['from'][0];
        }

        if (isset($this->parts['where'])) {
            $query .= " WHERE " . $this->parts['where'][0];
        }

        if (isset($this->parts['orderBy'])) {
            $query .= " ORDER BY " . $this->parts['orderBy'][0];
        }

        return $query;
    }
}

// Fluent API usage
$query = new QueryBuilder();
$sql = $query
    ->select('id', 'name', 'email')
    ->from('users')
    ->where('age > 18')
    ->orderBy('name ASC');

echo $sql;  // SELECT id, name, email FROM users WHERE age > 18 ORDER BY name ASC
```

### Useful Magic Methods

| Method            | Purpose                     | Java Equivalent           |
| ----------------- | --------------------------- | ------------------------- |
| `__construct()`   | Initialize object           | Constructor               |
| `__destruct()`    | Cleanup before destruction  | `finalize()` (deprecated) |
| `__toString()`    | String representation       | `toString()`              |
| `__get()`         | Dynamic property access     | No direct equivalent      |
| `__set()`         | Dynamic property setting    | No direct equivalent      |
| `__isset()`       | Check dynamic property      | No direct equivalent      |
| `__unset()`       | Unset dynamic property      | No direct equivalent      |
| `__call()`        | Dynamic method calls        | No direct equivalent      |
| `__callStatic()`  | Dynamic static method calls | No direct equivalent      |
| `__invoke()`      | Call object as function     | Functional interface      |
| `__clone()`       | Customize object cloning    | `clone()`                 |
| `__debugInfo()`   | Customize debug output      | No direct equivalent      |
| `__serialize()`   | Custom serialization        | `writeObject()`           |
| `__unserialize()` | Custom deserialization      | `readObject()`            |

::: warning Use Magic Methods Sparingly
Magic methods are powerful but can make code harder to understand and debug. Use them when:

- Building frameworks or ORMs
- Creating flexible APIs
- Implementing specific patterns (like Active Record)
- Need dynamic behavior that can't be achieved otherwise

For regular application code, prefer explicit methods and properties.
:::

---

## Section 8: Object Cloning

### Goal

Understand shallow vs deep cloning and how to customize clone behavior.

### Shallow vs Deep Copy

::: code-group

```php [PHP Object Cloning]
<?php

declare(strict_types=1);

class Address
{
    public function __construct(
        public string $street,
        public string $city
    ) {}
}

class Person
{
    public function __construct(
        public string $name,
        public Address $address
    ) {}

    // Customize cloning behavior
    public function __clone(): void
    {
        // Without this, $address would be a shallow copy (same reference)
        // With this, we create a deep copy
        $this->address = clone $this->address;
    }
}

// Shallow copy (without __clone)
$person1 = new Person("Alice", new Address("123 Main St", "NYC"));
$person2 = clone $person1;

// Without __clone, both share the same Address object
// With __clone, each has its own Address object

$person2->name = "Bob";  // Only affects person2
$person2->address->city = "LA";  // With __clone: only affects person2
                                  // Without __clone: affects both!

echo $person1->name;  // "Alice"
echo $person1->address->city;  // "NYC" (with __clone) or "LA" (without __clone)
```

```java [Java Object Cloning]
class Address implements Cloneable {
    String street;
    String city;

    Address(String street, String city) {
        this.street = street;
        this.city = city;
    }

    @Override
    protected Object clone() throws CloneNotSupportedException {
        return super.clone();
    }
}

class Person implements Cloneable {
    String name;
    Address address;

    Person(String name, Address address) {
        this.name = name;
        this.address = address;
    }

    @Override
    protected Object clone() throws CloneNotSupportedException {
        Person cloned = (Person) super.clone();
        // Deep copy the address
        cloned.address = (Address) address.clone();
        return cloned;
    }
}
```

:::

### Deep Clone Example: Complex Object Graph

```php
# filename: deep-clone-example.php
<?php

declare(strict_types=1);

class Order
{
    /** @var OrderItem[] */
    private array $items = [];

    public function __construct(
        public string $orderNumber,
        public Customer $customer
    ) {}

    public function addItem(OrderItem $item): void
    {
        $this->items[] = $item;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    // Deep clone all nested objects
    public function __clone(): void
    {
        // Clone customer
        $this->customer = clone $this->customer;

        // Clone all items in the array
        $this->items = array_map(
            fn(OrderItem $item) => clone $item,
            $this->items
        );
    }
}

class Customer
{
    public function __construct(
        public string $name,
        public string $email
    ) {}
}

class OrderItem
{
    public function __construct(
        public string $productName,
        public float $price,
        public int $quantity
    ) {}
}

// Create original order
$order1 = new Order("ORD-001", new Customer("Alice", "alice@example.com"));
$order1->addItem(new OrderItem("Laptop", 999.99, 1));
$order1->addItem(new OrderItem("Mouse", 29.99, 2));

// Clone the order
$order2 = clone $order1;

// Modify cloned order
$order2->orderNumber = "ORD-002";
$order2->customer->name = "Bob";
$order2->getItems()[0]->quantity = 3;

// Original order unchanged thanks to deep cloning
echo $order1->orderNumber;  // "ORD-001"
echo $order1->customer->name;  // "Alice"
echo $order1->getItems()[0]->quantity;  // 1
```

::: tip Cloning Best Practices

1. **Always implement \_\_clone()** for objects containing other objects
2. **Deep clone nested objects** to avoid shared references
3. **Be careful with resources** (file handles, database connections) - they can't be cloned
4. **Consider using serialization** for very complex deep clones: `unserialize(serialize($object))`
5. **Document cloning behavior** so users know what to expect
   :::

---

## Section 9: Anonymous Classes

### Goal

Learn about PHP's anonymous classes for quick, one-off object creation.

### Anonymous Class Syntax

PHP 7.0+ supports anonymous classes, useful for creating objects on the fly:

```php
# filename: anonymous-classes.php
<?php

declare(strict_types=1);

// Regular named class
class Logger
{
    public function log(string $message): void
    {
        echo "[LOG] $message\n";
    }
}

// Anonymous class - created and used immediately
$logger = new class {
    public function log(string $message): void
    {
        echo "[ANON LOG] $message\n";
    }
};

$logger->log("Hello from anonymous class");

// Anonymous class with constructor
$counter = new class(10) {
    public function __construct(
        private int $count
    ) {}

    public function increment(): void
    {
        $this->count++;
    }

    public function getCount(): int
    {
        return $this->count;
    }
};

$counter->increment();
echo $counter->getCount();  // 11

// Anonymous class implementing interface
interface Renderer
{
    public function render(string $content): string;
}

function display(Renderer $renderer): void
{
    echo $renderer->render("Hello World");
}

// Pass anonymous class implementing interface
display(new class implements Renderer {
    public function render(string $content): string
    {
        return "<div>$content</div>";
    }
});
```

### Practical Use Cases

**1. Mock Objects in Tests**

```php
# filename: anonymous-mock-objects.php
<?php

declare(strict_types=1);

interface UserRepository
{
    public function find(int $id): ?User;
}

class UserService
{
    public function __construct(
        private UserRepository $repository
    ) {}

    public function getUserName(int $id): string
    {
        $user = $this->repository->find($id);
        return $user?->name ?? 'Unknown';
    }
}

// Quick mock for testing
$mockRepo = new class implements UserRepository {
    public function find(int $id): ?User
    {
        return new User($id, "Test User");
    }
};

$service = new UserService($mockRepo);
echo $service->getUserName(1);  // "Test User"
```

**2. Event Listeners**

```php
# filename: anonymous-event-listeners.php
<?php

declare(strict_types=1);

interface EventListener
{
    public function handle(array $event): void;
}

class EventDispatcher
{
    private array $listeners = [];

    public function addEventListener(EventListener $listener): void
    {
        $this->listeners[] = $listener;
    }

    public function dispatch(array $event): void
    {
        foreach ($this->listeners as $listener) {
            $listener->handle($event);
        }
    }
}

$dispatcher = new EventDispatcher();

// Add anonymous listener inline
$dispatcher->addEventListener(new class implements EventListener {
    public function handle(array $event): void
    {
        echo "User logged in: {$event['user']}\n";
    }
});

$dispatcher->addEventListener(new class implements EventListener {
    public function handle(array $event): void
    {
        echo "Sending welcome email to: {$event['user']}\n";
    }
});

$dispatcher->dispatch(['user' => 'alice@example.com']);
```

**3. Strategy Pattern**

```php
# filename: anonymous-strategy-pattern.php
<?php

declare(strict_types=1);

interface PaymentStrategy
{
    public function pay(float $amount): void;
}

class ShoppingCart
{
    public function checkout(float $amount, PaymentStrategy $strategy): void
    {
        echo "Processing payment...\n";
        $strategy->pay($amount);
        echo "Payment complete!\n";
    }
}

$cart = new ShoppingCart();

// Credit card payment
$cart->checkout(100, new class implements PaymentStrategy {
    public function pay(float $amount): void
    {
        echo "Charging $amount to credit card\n";
    }
});

// PayPal payment
$cart->checkout(50, new class implements PaymentStrategy {
    public function pay(float $amount): void
    {
        echo "Processing $amount via PayPal\n";
    }
});
```

### Anonymous Classes vs Java

| Feature           | PHP                          | Java                         |
| ----------------- | ---------------------------- | ---------------------------- |
| **Syntax**        | `new class { }`              | `new Interface() { }`        |
| **Can extend**    | Yes                          | Yes                          |
| **Can implement** | Yes                          | Yes                          |
| **Constructor**   | Yes                          | No (uses initializer blocks) |
| **Use case**      | Testing, callbacks, strategy | Event handlers, comparators  |

::: tip When to Use Anonymous Classes
**Good for:**

- Quick mock objects in tests
- One-off implementations of interfaces
- Event listeners or callbacks
- Simple strategy pattern implementations

**Avoid for:**

- Complex logic that needs debugging
- Reusable code (use named classes)
- When you need to reference the class type
- Production code that needs maintenance
  :::

---

## Section 10: Object Comparison

### Goal

Understand how PHP compares objects vs Java.

### Comparison Operators

::: code-group

```php [PHP Object Comparison]
# filename: object-comparison.php
<?php

declare(strict_types=1);

class Point
{
    public function __construct(
        public int $x,
        public int $y
    ) {}
}

$p1 = new Point(10, 20);
$p2 = new Point(10, 20);
$p3 = $p1;

// == compares values (same properties)
var_dump($p1 == $p2);  // true (same values)

// === compares identity (same object)
var_dump($p1 === $p2);  // false (different objects)
var_dump($p1 === $p3);  // true (same reference)

// instanceof (like Java)
var_dump($p1 instanceof Point);  // true
```

```java [Java Object Comparison]
class Point {
    int x, y;

    Point(int x, int y) {
        this.x = x;
        this.y = y;
    }

    @Override
    public boolean equals(Object obj) {
        if (this == obj) return true;
        if (!(obj instanceof Point)) return false;
        Point other = (Point) obj;
        return x == other.x && y == other.y;
    }
}

Point p1 = new Point(10, 20);
Point p2 = new Point(10, 20);
Point p3 = p1;

// == compares references (identity)
System.out.println(p1 == p2);  // false (different objects)
System.out.println(p1 == p3);  // true (same reference)

// equals() compares values (if overridden)
System.out.println(p1.equals(p2));  // true (same values)

// instanceof
System.out.println(p1 instanceof Point);  // true
```

:::

### Key Differences

| Comparison             | PHP          | Java                       |
| ---------------------- | ------------ | -------------------------- |
| **Value equality**     | `==`         | `equals()` (must override) |
| **Reference equality** | `===`        | `==`                       |
| **Type check**         | `instanceof` | `instanceof`               |

---

## Section 11: Comprehensive Example - Shopping Cart System

### Goal

Build a complete shopping cart system that demonstrates multiple OOP concepts covered in this chapter.

### Complete Shopping Cart Implementation

This example combines encapsulation, static members, readonly properties, and magic methods:

```php
# filename: shopping-cart.php
<?php

declare(strict_types=1);

// Logger class with static methods
class Logger
{
    private static array $logs = [];
    private static int $logCount = 0;

    public static function log(string $message, string $level = 'INFO'): void
    {
        $timestamp = date('Y-m-d H:i:s');
        self::$logs[] = [
            'timestamp' => $timestamp,
            'level' => $level,
            'message' => $message
        ];
        self::$logCount++;
    }

    public static function getLogs(): array
    {
        return self::$logs;
    }

    public static function getLogCount(): int
    {
        return self::$logCount;
    }

    public static function clearLogs(): void
    {
        self::$logs = [];
        self::$logCount = 0;
    }
}

// Product class with readonly properties
readonly class Product
{
    public function __construct(
        public string $id,
        public string $name,
        public float $price
    ) {}

    public function __toString(): string
    {
        return "{$this->name} (\${$this->price})";
    }
}

// ShoppingCart class demonstrating encapsulation
class ShoppingCart
{
    /** @var array<string, int> */
    private array $items = [];  // productId => quantity
    private float $taxRate = 0.10;  // 10% tax

    public function __construct(
        private string $cartId
    ) {
        Logger::log("Shopping cart {$this->cartId} created");
    }

    public function addItem(Product $product, int $quantity = 1): void
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException("Quantity must be positive");
        }

        $this->items[$product->id] = ($this->items[$product->id] ?? 0) + $quantity;
        Logger::log("Added {$quantity}x {$product->name} to cart {$this->cartId}");
    }

    public function removeItem(string $productId, int $quantity = 1): void
    {
        if (!isset($this->items[$productId])) {
            throw new InvalidArgumentException("Product not in cart");
        }

        $this->items[$productId] -= $quantity;

        if ($this->items[$productId] <= 0) {
            unset($this->items[$productId]);
        }

        Logger::log("Removed {$quantity}x product {$productId} from cart");
    }

    public function getSubtotal(array $products): float
    {
        $subtotal = 0.0;

        foreach ($this->items as $productId => $quantity) {
            $product = $products[$productId] ?? null;
            if ($product) {
                $subtotal += $product->price * $quantity;
            }
        }

        return $subtotal;
    }

    public function getTax(array $products): float
    {
        return $this->getSubtotal($products) * $this->taxRate;
    }

    public function getTotal(array $products): float
    {
        return $this->getSubtotal($products) + $this->getTax($products);
    }

    public function getItemCount(): int
    {
        return array_sum($this->items);
    }

    public function getCartId(): string
    {
        return $this->cartId;
    }

    public function clear(): void
    {
        $this->items = [];
        Logger::log("Cart {$this->cartId} cleared");
    }

    public function __toString(): string
    {
        return "Cart {$this->cartId}: {$this->getItemCount()} items";
    }
}

// Usage example
$products = [
    'laptop-001' => new Product('laptop-001', 'Laptop', 999.99),
    'mouse-001' => new Product('mouse-001', 'Wireless Mouse', 29.99),
    'keyboard-001' => new Product('keyboard-001', 'Mechanical Keyboard', 79.99),
];

$cart = new ShoppingCart('CART-12345');
$cart->addItem($products['laptop-001'], 1);
$cart->addItem($products['mouse-001'], 2);
$cart->addItem($products['keyboard-001'], 1);

echo $cart . "\n";  // Cart CART-12345: 4 items
echo "Subtotal: \${$cart->getSubtotal($products)}\n";  // Subtotal: $1139.97
echo "Tax: \${$cart->getTax($products)}\n";  // Tax: $113.997
echo "Total: \${$cart->getTotal($products)}\n";  // Total: $1253.967

// View logs
echo "\nLogs:\n";
foreach (Logger::getLogs() as $log) {
    echo "[{$log['timestamp']}] {$log['level']}: {$log['message']}\n";
}

echo "\nTotal log entries: " . Logger::getLogCount() . "\n";
```

### Key Concepts Demonstrated

1. **Encapsulation**: `ShoppingCart` uses private properties and public methods
2. **Static Members**: `Logger` class uses static properties and methods
3. **Readonly Properties**: `Product` class is immutable
4. **Magic Methods**: `__toString()` for string representation
5. **Type Hints**: All methods have proper type declarations
6. **Error Handling**: Validation with exceptions

---

## Exercises

### Exercise 1: Bank Account System

Create a `BankAccount` class with proper encapsulation.

**Requirements:**

- Private balance property
- Public methods: deposit, withdraw, getBalance
- Validate amounts (positive, sufficient balance)
- Track transaction count (static)

<details>
<summary>Solution</summary>

```php
# filename: bank-account-exercise.php
<?php

declare(strict_types=1);

class BankAccount
{
    private static int $totalAccounts = 0;
    private static int $totalTransactions = 0;

    private float $balance;
    private string $accountNumber;

    public function __construct(string $accountNumber, float $initialBalance = 0)
    {
        if ($initialBalance < 0) {
            throw new InvalidArgumentException("Initial balance cannot be negative");
        }

        $this->accountNumber = $accountNumber;
        $this->balance = $initialBalance;
        self::$totalAccounts++;
    }

    public function deposit(float $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException("Deposit amount must be positive");
        }

        $this->balance += $amount;
        self::$totalTransactions++;
    }

    public function withdraw(float $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException("Withdrawal amount must be positive");
        }

        if ($amount > $this->balance) {
            throw new RuntimeException("Insufficient funds");
        }

        $this->balance -= $amount;
        self::$totalTransactions++;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public static function getTotalAccounts(): int
    {
        return self::$totalAccounts;
    }

    public static function getTotalTransactions(): int
    {
        return self::$totalTransactions;
    }
}

// Test
$account1 = new BankAccount("ACC001", 1000);
$account1->deposit(500);
$account1->withdraw(200);
echo "Balance: {$account1->getBalance()}\n";  // 1300

$account2 = new BankAccount("ACC002", 2000);
echo "Total accounts: " . BankAccount::getTotalAccounts() . "\n";  // 2
echo "Total transactions: " . BankAccount::getTotalTransactions() . "\n";  // 2
```

</details>

### Exercise 2: Shape Hierarchy

Create a `Shape` base class with `Circle` and `Rectangle` subclasses.

**Requirements:**

- Abstract `getArea()` and `getPerimeter()` methods
- Concrete implementations in subclasses
- `__toString()` for display

<details>
<summary>Solution (Preview - Full implementation in Chapter 4)</summary>

```php
# filename: shape-hierarchy-exercise.php
<?php

declare(strict_types=1);

// We'll cover abstract classes in Chapter 4, but here's a preview
abstract class Shape
{
    abstract public function getArea(): float;
    abstract public function getPerimeter(): float;

    public function __toString(): string
    {
        return static::class . " - Area: {$this->getArea()}, Perimeter: {$this->getPerimeter()}";
    }
}

class Circle extends Shape
{
    public function __construct(
        private float $radius
    ) {}

    public function getArea(): float
    {
        return M_PI * $this->radius ** 2;
    }

    public function getPerimeter(): float
    {
        return 2 * M_PI * $this->radius;
    }
}

// Test
$circle = new Circle(5);
echo $circle;  // Circle - Area: 78.54, Perimeter: 31.42
```

</details>

---

## Wrap-up Checklist

Before moving to the next chapter, ensure you can:

- [ ] Define classes with properties and methods
- [ ] Use constructor property promotion (PHP 8+)
- [ ] Create named constructors (static factory methods)
- [ ] Use readonly properties for immutability (PHP 8.1+)
- [ ] Apply visibility modifiers correctly
- [ ] Create and use static members
- [ ] Understand `self::` vs `static::` (late static binding)
- [ ] Define class constants and enums
- [ ] Understand `$this` vs `self` vs `static`
- [ ] Use magic methods appropriately
- [ ] Implement proper object cloning (shallow vs deep)
- [ ] Create anonymous classes for quick implementations
- [ ] Compare objects with `==` and `===`

::: tip Ready for More?
In [Chapter 4: Classes & Inheritance](/series/php-for-java-developers/chapters/04-classes-and-inheritance), we'll explore inheritance, abstract classes, method overriding, and more advanced OOP concepts.
:::


---

## Further Reading

**PHP Documentation:**

- [Classes and Objects](https://www.php.net/manual/en/language.oop5.php)
- [Constructor Property Promotion](https://www.php.net/manual/en/language.oop5.decon.php#language.oop5.decon.constructor.promotion)
- [Enumerations](https://www.php.net/manual/en/language.enumerations.php)
- [Magic Methods](https://www.php.net/manual/en/language.oop5.magic.php)

---

<div style="display: flex; justify-content: space-between; margin-top: 2rem;">
  <div>
    <strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/02-control-flow-and-functions">← Chapter 2: Control Flow & Functions</a>
  </div>
  <div>
    <strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/04-classes-and-inheritance">Chapter 4: Classes & Inheritance →</a>
  </div>
</div>
