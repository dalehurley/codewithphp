---
title: "03: OOP Basics"
description: "Master PHP's object-oriented programming with detailed comparisons to Java's OOP model"
series: "php-for-java-developers"
chapter: 3
order: 3
difficulty: "Beginner"
prerequisites:
  - "/series/php-for-java-developers/chapters/02-control-flow-and-functions"
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

| Feature | PHP | Java |
|---------|-----|------|
| **Property declaration** | `public string $name;` | `public String name;` |
| **Constructor name** | `__construct` | Class name |
| **This reference** | `$this->property` | `this.property` |
| **Member access** | `->` operator | `.` operator |
| **Type hints** | Optional (but recommended) | Required |
| **Visibility** | Same (public, private, protected) | Same |

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

## Section 3: Visibility Modifiers

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

| Modifier | Same Class | Subclass | Outside Class | PHP Package | Java Package |
|----------|-----------|----------|---------------|-------------|--------------|
| **public** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **protected** | ✅ | ✅ | ❌ | ❌ | ✅ (same package) |
| **private** | ✅ | ❌ | ❌ | ❌ | ❌ |

::: warning Key Difference: Protected
In Java, `protected` also grants access to classes in the same package. PHP doesn't have packages (it has namespaces, covered in Chapter 6), so `protected` only applies to the class hierarchy.
:::

### Best Practices

```php
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

## Section 4: Static Members

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

| Feature | PHP | Java |
|---------|-----|------|
| **Access static property** | `self::$property` | `ClassName.field` |
| **Access static method** | `self::method()` or `ClassName::method()` | `ClassName.method()` |
| **From instance** | `self::$property` (not `$this->property`) | `this.staticField` (works but discouraged) |
| **Inheritance** | Can be overridden (late static binding with `static::`) | Can be hidden, not overridden |

::: warning Common Mistake
```php
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

---

## Section 5: Class Constants and Enums

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

## Section 6: Magic Methods

### Goal

Understand PHP's special "magic methods" that provide advanced functionality.

### Common Magic Methods

PHP has special methods that are automatically called in certain situations:

```php
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
}

// Usage
$product = new Product("Laptop", 999.99);

// __get
echo $product->name;  // "Laptop" (calls __get)

// __set
$product->category = "Electronics";  // calls __set

// __isset
var_dump(isset($product->category));  // true

// __toString
echo $product;  // "Laptop: $999.99"

// __invoke
echo $product(5);  // 4999.95 (price * quantity)

// __clone
$clone = clone $product;
```

### Useful Magic Methods

| Method | Purpose | Java Equivalent |
|--------|---------|-----------------|
| `__construct()` | Initialize object | Constructor |
| `__destruct()` | Cleanup before destruction | `finalize()` (deprecated) |
| `__toString()` | String representation | `toString()` |
| `__get()` | Dynamic property access | No direct equivalent |
| `__set()` | Dynamic property setting | No direct equivalent |
| `__call()` | Dynamic method calls | No direct equivalent |
| `__invoke()` | Call object as function | Functional interface |
| `__clone()` | Customize object cloning | `clone()` |

::: warning Use Magic Methods Sparingly
Magic methods are powerful but can make code harder to understand and debug. Use them when:
- Building frameworks or ORMs
- Creating flexible APIs
- Implementing specific patterns (like Active Record)

For regular application code, prefer explicit methods and properties.
:::

---

## Section 7: Object Comparison

### Goal

Understand how PHP compares objects vs Java.

### Comparison Operators

::: code-group

```php [PHP Object Comparison]
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

| Comparison | PHP | Java |
|------------|-----|------|
| **Value equality** | `==` | `equals()` (must override) |
| **Reference equality** | `===` | `==` |
| **Type check** | `instanceof` | `instanceof` |

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
- [ ] Apply visibility modifiers correctly
- [ ] Create and use static members
- [ ] Define class constants and enums
- [ ] Understand `$this` vs `self` vs `static`
- [ ] Use magic methods appropriately
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
