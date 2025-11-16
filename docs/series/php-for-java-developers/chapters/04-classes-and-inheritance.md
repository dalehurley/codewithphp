---
title: "04: Classes & Inheritance"
description: "Master inheritance, abstract classes, method overriding, and polymorphism in PHP with Java comparisons"
series: "php-for-java-developers"
chapter: 4
order: 4
difficulty: "Intermediate"
prerequisites:
  - "/series/php-for-java-developers/chapters/03-oop-basics"
---

![Inheritance Hero](/images/php-for-java-developers/chapter-04-inheritance-hero-full.webp)

# Chapter 4: Classes & Inheritance

<Badge type="warning">Intermediate</Badge> <Badge type="info">90-120 min</Badge>

## Overview

Inheritance in PHP works similarly to Java—you use the `extends` keyword, can have abstract classes, and follow the same polymorphism principles. However, PHP has some unique features like late static binding and the `parent` keyword that differ from Java's `super`. In this chapter, we'll explore PHP's inheritance model in depth, always comparing it to Java.

By the end of this chapter, you'll understand how to build class hierarchies in PHP and leverage inheritance effectively.

## Prerequisites

::: info Time Estimate
⏱️ **90-120 minutes** to complete this chapter
:::

**What you need:**
- Completed [Chapter 3: OOP Basics](/series/php-for-java-developers/chapters/03-oop-basics)
- Solid understanding of Java inheritance
- Familiarity with Java's abstract classes and polymorphism

## What You'll Build

In this chapter, you'll create:
- A Shape hierarchy with Circle and Rectangle using abstract classes
- An employee management system with inheritance
- An abstract repository pattern implementation
- A payment processing system demonstrating polymorphism

## Learning Objectives

By the end of this chapter, you'll be able to:

- **Use inheritance** with the `extends` keyword
- **Create abstract classes** and methods
- **Override methods** with proper visibility rules
- **Use `final`** to prevent inheritance or overriding
- **Understand late static binding** (static:: vs self::)
- **Call parent methods** with `parent::`
- **Apply polymorphism** effectively in PHP

---

## Section 1: Basic Inheritance

### Goal

Learn how to extend classes in PHP and understand the similarities with Java.

### Extending Classes

::: code-group

```php [PHP Inheritance]
<?php

declare(strict_types=1);

class Animal
{
    protected string $name;
    protected int $age;

    public function __construct(string $name, int $age)
    {
        $this->name = $name;
        $this->age = $age;
    }

    public function makeSound(): string
    {
        return "Some generic sound";
    }

    public function getInfo(): string
    {
        return "{$this->name} is {$this->age} years old";
    }
}

class Dog extends Animal
{
    private string $breed;

    public function __construct(string $name, int $age, string $breed)
    {
        // Call parent constructor
        parent::__construct($name, $age);
        $this->breed = $breed;
    }

    // Override method
    public function makeSound(): string
    {
        return "Woof! Woof!";
    }

    // Add new method
    public function getBreed(): string
    {
        return $this->breed;
    }

    // Override and extend
    public function getInfo(): string
    {
        return parent::getInfo() . " and is a {$this->breed}";
    }
}

// Usage
$dog = new Dog("Buddy", 3, "Golden Retriever");
echo $dog->makeSound();  // "Woof! Woof!"
echo $dog->getInfo();    // "Buddy is 3 years old and is a Golden Retriever"
```

```java [Java Inheritance]
class Animal {
    protected String name;
    protected int age;

    public Animal(String name, int age) {
        this.name = name;
        this.age = age;
    }

    public String makeSound() {
        return "Some generic sound";
    }

    public String getInfo() {
        return name + " is " + age + " years old";
    }
}

class Dog extends Animal {
    private String breed;

    public Dog(String name, int age, String breed) {
        // Call parent constructor
        super(name, age);
        this.breed = breed;
    }

    // Override method
    @Override
    public String makeSound() {
        return "Woof! Woof!";
    }

    // Add new method
    public String getBreed() {
        return breed;
    }

    // Override and extend
    @Override
    public String getInfo() {
        return super.getInfo() + " and is a " + breed;
    }
}

// Usage
Dog dog = new Dog("Buddy", 3, "Golden Retriever");
System.out.println(dog.makeSound());
System.out.println(dog.getInfo());
```

:::

### Key Similarities and Differences

| Feature | PHP | Java |
|---------|-----|------|
| **Extend keyword** | `extends` | `extends` |
| **Call parent constructor** | `parent::__construct()` | `super()` |
| **Call parent method** | `parent::methodName()` | `super.methodName()` |
| **Access parent members** | `$this->parentProperty` | `this.parentField` |
| **Override annotation** | No annotation (optional @Override in docs) | `@Override` annotation |
| **Multiple inheritance** | No (use interfaces/traits) | No (use interfaces) |

::: tip Parent vs Super
- **PHP**: Use `parent::` to call parent methods (static syntax)
- **Java**: Use `super.` to call parent methods (object syntax)

Both serve the same purpose—accessing parent class members.
:::

### Constructor Chaining

PHP requires explicit parent constructor calls:

```php
<?php

declare(strict_types=1);

class Vehicle
{
    public function __construct(
        protected string $make,
        protected string $model,
        protected int $year
    ) {
        echo "Vehicle constructor called\n";
    }
}

class Car extends Vehicle
{
    public function __construct(
        string $make,
        string $model,
        int $year,
        private int $doors
    ) {
        // MUST explicitly call parent constructor
        parent::__construct($make, $model, $year);
        echo "Car constructor called\n";
    }

    public function getDetails(): string
    {
        return "{$this->year} {$this->make} {$this->model} ({$this->doors} doors)";
    }
}

$car = new Car("Toyota", "Camry", 2024, 4);
// Output:
// Vehicle constructor called
// Car constructor called

echo $car->getDetails();
```

::: warning Constructor Inheritance
Unlike Java, PHP does NOT automatically call the parent constructor. You must explicitly call `parent::__construct()` if you want to run the parent's initialization code.

**Java**: Parent constructor called automatically
**PHP**: Must call `parent::__construct()` explicitly
:::

### Constructor Property Promotion with Inheritance

PHP 8's constructor property promotion works with inheritance:

```php
<?php

declare(strict_types=1);

// Parent with promoted properties
class Person
{
    public function __construct(
        protected string $name,
        protected int $age
    ) {}

    public function introduce(): string
    {
        return "I'm {$this->name}, {$this->age} years old";
    }
}

// Child adds more promoted properties
class Employee extends Person
{
    public function __construct(
        string $name,
        int $age,
        protected string $employeeId,
        protected float $salary
    ) {
        parent::__construct($name, $age);
    }

    public function introduce(): string
    {
        return parent::introduce() . " and work here as #{$this->employeeId}";
    }

    public function getSalary(): float
    {
        return $this->salary;
    }
}

// Usage
$employee = new Employee("Alice", 30, "EMP001", 75000);
echo $employee->introduce();  // "I'm Alice, 30 years old and work here as #EMP001"
echo $employee->getSalary();  // 75000
```

::: tip Best Practice
When using constructor promotion with inheritance:
1. **Parent properties** - Use promoted properties in the parent for clean code
2. **Child-specific** - Add child-specific promoted properties in child constructor
3. **Call parent first** - Always call `parent::__construct()` at the beginning
4. **Keep it simple** - Don't mix promoted and non-promoted properties unnecessarily
:::

---

## Section 2: Abstract Classes

### Goal

Master abstract classes and methods in PHP.

### Abstract Classes and Methods

::: code-group

```php [PHP Abstract Classes]
<?php

declare(strict_types=1);

abstract class Shape
{
    public function __construct(
        protected string $color
    ) {}

    // Abstract methods (must be implemented by subclasses)
    abstract public function calculateArea(): float;
    abstract public function calculatePerimeter(): float;

    // Concrete method (inherited by all subclasses)
    public function getColor(): string
    {
        return $this->color;
    }

    public function describe(): string
    {
        return "A {$this->color} " . static::class . " with area " .
               number_format($this->calculateArea(), 2);
    }
}

class Circle extends Shape
{
    public function __construct(
        string $color,
        private float $radius
    ) {
        parent::__construct($color);
    }

    public function calculateArea(): float
    {
        return M_PI * $this->radius ** 2;
    }

    public function calculatePerimeter(): float
    {
        return 2 * M_PI * $this->radius;
    }

    public function getRadius(): float
    {
        return $this->radius;
    }
}

class Rectangle extends Shape
{
    public function __construct(
        string $color,
        private float $width,
        private float $height
    ) {
        parent::__construct($color);
    }

    public function calculateArea(): float
    {
        return $this->width * $this->height;
    }

    public function calculatePerimeter(): float
    {
        return 2 * ($this->width + $this->height);
    }
}

// Cannot instantiate abstract class
// $shape = new Shape("red");  // Error!

// Create concrete instances
$circle = new Circle("red", 5);
echo $circle->describe() . "\n";
// "A red Circle with area 78.54"

$rectangle = new Rectangle("blue", 4, 6);
echo $rectangle->describe() . "\n";
// "A blue Rectangle with area 24.00"

// Polymorphism
function printShapeInfo(Shape $shape): void
{
    echo "Color: {$shape->getColor()}\n";
    echo "Area: {$shape->calculateArea()}\n";
    echo "Perimeter: {$shape->calculatePerimeter()}\n";
}

printShapeInfo($circle);
printShapeInfo($rectangle);
```

```java [Java Abstract Classes]
abstract class Shape {
    protected String color;

    public Shape(String color) {
        this.color = color;
    }

    // Abstract methods
    public abstract double calculateArea();
    public abstract double calculatePerimeter();

    // Concrete method
    public String getColor() {
        return color;
    }

    public String describe() {
        return "A " + color + " " + this.getClass().getSimpleName() +
               " with area " + String.format("%.2f", calculateArea());
    }
}

class Circle extends Shape {
    private double radius;

    public Circle(String color, double radius) {
        super(color);
        this.radius = radius;
    }

    @Override
    public double calculateArea() {
        return Math.PI * radius * radius;
    }

    @Override
    public double calculatePerimeter() {
        return 2 * Math.PI * radius;
    }
}

class Rectangle extends Shape {
    private double width;
    private double height;

    public Rectangle(String color, double width, double height) {
        super(color);
        this.width = width;
        this.height = height;
    }

    @Override
    public double calculateArea() {
        return width * height;
    }

    @Override
    public double calculatePerimeter() {
        return 2 * (width + height);
    }
}

// Usage
Circle circle = new Circle("red", 5);
Rectangle rectangle = new Rectangle("blue", 4, 6);

// Polymorphism
void printShapeInfo(Shape shape) {
    System.out.println("Color: " + shape.getColor());
    System.out.println("Area: " + shape.calculateArea());
    System.out.println("Perimeter: " + shape.calculatePerimeter());
}
```

:::

### Abstract Class Rules

| Rule | PHP | Java |
|------|-----|------|
| **Cannot instantiate** | ✅ Same | ✅ Same |
| **Can have concrete methods** | ✅ Yes | ✅ Yes |
| **Can have abstract methods** | ✅ Yes | ✅ Yes |
| **Abstract methods in subclass** | Must implement | Must implement |
| **Can have constructors** | ✅ Yes | ✅ Yes |
| **Can have properties** | ✅ Yes | ✅ Yes |
| **Multiple inheritance** | ❌ No | ❌ No |

::: tip When to Use Abstract Classes
Use abstract classes when:
- You want to provide common implementation for subclasses
- You need to enforce a contract (abstract methods)
- Subclasses share state (properties)
- You want to use protected members
- You're modeling an "is-a" relationship

**Abstract class vs Interface** (covered in Chapter 5):
- Abstract class: Provides implementation + contract
- Interface: Only defines contract (no implementation in PHP < 8.0)
:::

---

## Section 3: Method Overriding

### Goal

Understand method overriding rules and visibility in PHP.

### Override Rules

```php
<?php

declare(strict_types=1);

class ParentClass
{
    public function publicMethod(): string
    {
        return "Parent public method";
    }

    protected function protectedMethod(): string
    {
        return "Parent protected method";
    }

    private function privateMethod(): string
    {
        return "Parent private method";
    }
}

class ChildClass extends ParentClass
{
    // ✅ Can override public as public
    public function publicMethod(): string
    {
        return "Child public method";
    }

    // ✅ Can override protected as protected or public
    public function protectedMethod(): string
    {
        return "Child protected method (now public)";
    }

    // ✅ Private methods are NOT inherited, so this is a new method
    private function privateMethod(): string
    {
        return "Child private method (not an override)";
    }

    // ❌ Cannot reduce visibility
    // protected function publicMethod(): string { }  // Error!
}
```

### Visibility Override Rules

| Parent Visibility | Child Can Use |
|-------------------|---------------|
| **public** | public only |
| **protected** | protected or public |
| **private** | N/A (not inherited) |

::: warning Visibility Rules
You can make methods MORE visible (protected → public) but NOT less visible (public → protected/private).

**This is the same in Java!**
:::

### Return Type Compatibility

PHP 7.4+ (including PHP 8.4) enforces return type compatibility (covariance):

```php
<?php

declare(strict_types=1);

class Animal {}
class Dog extends Animal {}

class AnimalFactory
{
    public function create(): Animal
    {
        return new Animal();
    }
}

class DogFactory extends AnimalFactory
{
    // ✅ Covariant return type (PHP 7.4+, PHP 8.4 compatible)
    public function create(): Dog
    {
        return new Dog();
    }
}

// This works because Dog is a subtype of Animal
$factory = new DogFactory();
$animal = $factory->create();  // Returns Dog, which is an Animal
```

---

## Section 4: Final Classes and Methods

### Goal

Learn how to prevent inheritance and method overriding.

### Final Keyword

::: code-group

```php [PHP Final]
<?php

declare(strict_types=1);

// Final class - cannot be extended
final class ImmutableValue
{
    public function __construct(
        private readonly mixed $value
    ) {}

    public function getValue(): mixed
    {
        return $this->value;
    }
}

// Error: Cannot extend final class
// class ExtendedValue extends ImmutableValue {}

class BaseService
{
    // Final method - cannot be overridden
    final public function authenticate(string $token): bool
    {
        // Critical authentication logic
        return hash('sha256', $token) === $this->getExpectedHash();
    }

    protected function getExpectedHash(): string
    {
        return 'expected-hash';
    }

    // Regular method - can be overridden
    public function process(): void
    {
        echo "Base processing\n";
    }
}

class UserService extends BaseService
{
    // Error: Cannot override final method
    // public function authenticate(string $token): bool {}

    // ✅ Can override non-final method
    public function process(): void
    {
        echo "User processing\n";
    }
}
```

```java [Java Final]
// Final class - cannot be extended
final class ImmutableValue {
    private final Object value;

    public ImmutableValue(Object value) {
        this.value = value;
    }

    public Object getValue() {
        return value;
    }
}

// Error: Cannot extend final class
// class ExtendedValue extends ImmutableValue {}

class BaseService {
    // Final method - cannot be overridden
    final public boolean authenticate(String token) {
        // Critical authentication logic
        return token.hashCode() == getExpectedHash();
    }

    protected int getExpectedHash() {
        return 12345;
    }

    // Regular method - can be overridden
    public void process() {
        System.out.println("Base processing");
    }
}

class UserService extends BaseService {
    // Error: Cannot override final method
    // public boolean authenticate(String token) {}

    // Can override non-final method
    @Override
    public void process() {
        System.out.println("User processing");
    }
}
```

:::

::: tip When to Use Final
Use `final` to:
- **Prevent inheritance**: When a class shouldn't be subclassed (e.g., utility classes)
- **Prevent overriding**: When a method is critical and shouldn't be modified (e.g., security, core logic)
- **Optimization**: Final classes/methods can be optimized by the runtime

**Don't overuse**: Only use when there's a clear reason. Excessive use makes code less flexible.
:::

---

## Section 5: Late Static Binding

### Goal

Understand the difference between `self::`, `parent::`, and `static::`.

### self:: vs static::

This is a PHP-specific feature with no direct Java equivalent:

```php
<?php

declare(strict_types=1);

class BaseModel
{
    protected static string $tableName = 'base_table';

    // Using self:: (early binding)
    public static function getTableWithSelf(): string
    {
        return self::$tableName;  // Always refers to BaseModel::$tableName
    }

    // Using static:: (late binding)
    public static function getTableWithStatic(): string
    {
        return static::$tableName;  // Refers to the called class's $tableName
    }

    public static function createSelf(): static
    {
        return new self();  // Always creates BaseModel
    }

    public static function createStatic(): static
    {
        return new static();  // Creates instance of called class
    }
}

class UserModel extends BaseModel
{
    protected static string $tableName = 'users';
}

class ProductModel extends BaseModel
{
    protected static string $tableName = 'products';
}

// Early binding (self::)
echo BaseModel::getTableWithSelf();    // "base_table"
echo UserModel::getTableWithSelf();    // "base_table" (refers to parent!)
echo ProductModel::getTableWithSelf(); // "base_table" (refers to parent!)

// Late static binding (static::)
echo BaseModel::getTableWithStatic();    // "base_table"
echo UserModel::getTableWithStatic();    // "users" (correct!)
echo ProductModel::getTableWithStatic(); // "products" (correct!)

// Object creation
$base = BaseModel::createSelf();       // BaseModel instance
$user = UserModel::createSelf();       // BaseModel instance (wrong!)
$userCorrect = UserModel::createStatic();  // UserModel instance (correct!)
```

### When to Use Each

| Keyword | Binding | Use Case |
|---------|---------|----------|
| `self::` | Early (compile-time) | When you specifically want the defining class |
| `static::` | Late (runtime) | When you want the called class (polymorphic behavior) |
| `parent::` | Parent class | When you want to call parent's implementation |

::: tip Late Static Binding Use Cases
Use `static::` for:
- **Factory methods**: `return new static()` creates instance of called class
- **Polymorphic static methods**: Different behavior per subclass
- **Active Record pattern**: `User::find()`, `Product::find()`, etc.

**Most of the time, use `static::` for static methods in inheritance hierarchies.**
:::

### Practical Example: Repository Pattern

```php
<?php

declare(strict_types=1);

abstract class Repository
{
    protected static string $table;
    protected static string $primaryKey = 'id';

    public static function find(int $id): ?static
    {
        // Late binding: uses child class's $table
        $table = static::$table;
        $pk = static::$primaryKey;

        // Simulate database query
        echo "SELECT * FROM {$table} WHERE {$pk} = {$id}\n";

        // Return instance of called class
        return new static();
    }

    public static function all(): array
    {
        $table = static::$table;
        echo "SELECT * FROM {$table}\n";
        return [];
    }
}

class UserRepository extends Repository
{
    protected static string $table = 'users';
}

class ProductRepository extends Repository
{
    protected static string $table = 'products';
}

// Each class uses its own table name
$user = UserRepository::find(1);     // SELECT * FROM users WHERE id = 1
$product = ProductRepository::find(5); // SELECT * FROM products WHERE id = 5

UserRepository::all();    // SELECT * FROM users
ProductRepository::all(); // SELECT * FROM products
```

---

## Section 6: Polymorphism

### Goal

Apply polymorphism effectively in PHP.

### Type Hinting with Parent Classes

```php
<?php

declare(strict_types=1);

abstract class PaymentMethod
{
    public function __construct(
        protected float $amount
    ) {}

    abstract public function process(): bool;
    abstract public function getTransactionFee(): float;

    public function getTotalAmount(): float
    {
        return $this->amount + $this->getTransactionFee();
    }
}

class CreditCardPayment extends PaymentMethod
{
    public function __construct(
        float $amount,
        private string $cardNumber,
        private string $cvv
    ) {
        parent::__construct($amount);
    }

    public function process(): bool
    {
        echo "Processing credit card payment: \${$this->amount}\n";
        echo "Card: ****" . substr($this->cardNumber, -4) . "\n";
        return true;
    }

    public function getTransactionFee(): float
    {
        return $this->amount * 0.029 + 0.30;  // 2.9% + $0.30
    }
}

class PayPalPayment extends PaymentMethod
{
    public function __construct(
        float $amount,
        private string $email
    ) {
        parent::__construct($amount);
    }

    public function process(): bool
    {
        echo "Processing PayPal payment: \${$this->amount}\n";
        echo "PayPal account: {$this->email}\n";
        return true;
    }

    public function getTransactionFee(): float
    {
        return $this->amount * 0.034 + 0.30;  // 3.4% + $0.30
    }
}

class BitcoinPayment extends PaymentMethod
{
    public function __construct(
        float $amount,
        private string $walletAddress
    ) {
        parent::__construct($amount);
    }

    public function process(): bool
    {
        echo "Processing Bitcoin payment: \${$this->amount}\n";
        echo "Wallet: {$this->walletAddress}\n";
        return true;
    }

    public function getTransactionFee(): float
    {
        return 1.00;  // Fixed fee
    }
}

// Polymorphic function - accepts any PaymentMethod
function processPayment(PaymentMethod $payment): void
{
    echo "\n=== Processing Payment ===\n";
    echo "Amount: \${$payment->getTotalAmount()}\n";
    echo "Fee: \${$payment->getTransactionFee()}\n";

    if ($payment->process()) {
        echo "Payment successful!\n";
    } else {
        echo "Payment failed!\n";
    }
}

// All payment types can be processed polymorphically
$payments = [
    new CreditCardPayment(100, "4532123456789012", "123"),
    new PayPalPayment(100, "user@example.com"),
    new BitcoinPayment(100, "1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa")
];

foreach ($payments as $payment) {
    processPayment($payment);
}
```

### instanceof Operator

```php
<?php

declare(strict_types=1);

function handlePayment(PaymentMethod $payment): void
{
    // Type checking
    if ($payment instanceof CreditCardPayment) {
        echo "Processing credit card...\n";
        // Can access CreditCardPayment-specific methods
    } elseif ($payment instanceof PayPalPayment) {
        echo "Processing PayPal...\n";
    } elseif ($payment instanceof BitcoinPayment) {
        echo "Processing Bitcoin...\n";
    }

    // Process regardless of type
    $payment->process();
}

// Check against parent class
$cc = new CreditCardPayment(50, "4532123456789012", "123");
var_dump($cc instanceof CreditCardPayment);  // true
var_dump($cc instanceof PaymentMethod);      // true (inheritance)
var_dump($cc instanceof PayPalPayment);      // false
```

---

## Section 7: Liskov Substitution Principle (LSP)

### Goal

Understand the Liskov Substitution Principle and how to apply it in PHP.

### What is LSP?

The Liskov Substitution Principle states: **"Objects of a superclass should be replaceable with objects of a subclass without breaking the application."**

In simpler terms: If class `B` extends class `A`, you should be able to use `B` anywhere you use `A` without unexpected behavior.

### LSP Violations

```php
<?php

declare(strict_types=1);

// ❌ BAD: Violates LSP
class Rectangle
{
    protected float $width;
    protected float $height;

    public function setWidth(float $width): void
    {
        $this->width = $width;
    }

    public function setHeight(float $height): void
    {
        $this->height = $height;
    }

    public function getArea(): float
    {
        return $this->width * $this->height;
    }
}

class Square extends Rectangle
{
    // Problem: Square changes behavior of Rectangle
    public function setWidth(float $width): void
    {
        $this->width = $width;
        $this->height = $width;  // Square must have equal sides
    }

    public function setHeight(float $height): void
    {
        $this->width = $height;
        $this->height = $height;  // Square must have equal sides
    }
}

// This function works with Rectangle
function resizeRectangle(Rectangle $rect): void
{
    $rect->setWidth(5);
    $rect->setHeight(10);
    // Expects area = 50
}

$rectangle = new Rectangle();
resizeRectangle($rectangle);
echo $rectangle->getArea();  // 50 ✅ correct

$square = new Square();
resizeRectangle($square);
echo $square->getArea();  // 100 ❌ unexpected! (should be 50)
// LSP violated: Square can't be used where Rectangle is expected
```

### LSP Compliant Design

```php
<?php

declare(strict_types=1);

// ✅ GOOD: LSP compliant
interface Shape
{
    public function getArea(): float;
    public function getPerimeter(): float;
}

class Rectangle implements Shape
{
    public function __construct(
        private float $width,
        private float $height
    ) {}

    public function setWidth(float $width): void
    {
        $this->width = $width;
    }

    public function setHeight(float $height): void
    {
        $this->height = $height;
    }

    public function getArea(): float
    {
        return $this->width * $this->height;
    }

    public function getPerimeter(): float
    {
        return 2 * ($this->width + $this->height);
    }
}

class Square implements Shape
{
    public function __construct(
        private float $side
    ) {}

    public function setSide(float $side): void
    {
        $this->side = $side;
    }

    public function getArea(): float
    {
        return $this->side ** 2;
    }

    public function getPerimeter(): float
    {
        return 4 * $this->side;
    }
}

// Now both work independently without inheritance issues
function printArea(Shape $shape): void
{
    echo "Area: {$shape->getArea()}\n";
}

printArea(new Rectangle(5, 10));  // 50
printArea(new Square(7));         // 49
```

### LSP Guidelines

::: tip Follow LSP
**Do:**
- Subclass should fulfill parent's contract
- Preserve expected behavior
- Don't strengthen preconditions (input requirements)
- Don't weaken postconditions (output guarantees)
- Subclass should be truly "is-a" relationship

**Don't:**
- Change expected behavior
- Throw new exceptions not in parent
- Require more than parent requires
- Promise less than parent promises
:::

---

## Section 8: Template Method Pattern

### Goal

Learn the Template Method pattern using abstract classes.

### Template Method Pattern

The Template Method pattern defines the skeleton of an algorithm in a base class, letting subclasses override specific steps:

```php
<?php

declare(strict_types=1);

abstract class DataImporter
{
    // Template method - defines the algorithm structure
    final public function import(string $source): void
    {
        echo "Starting import from: $source\n";

        $this->validateSource($source);
        $data = $this->readData($source);
        $processed = $this->processData($data);
        $this->saveData($processed);
        $this->sendNotification();

        echo "Import complete!\n";
    }

    // Hook method - can be overridden but has default
    protected function validateSource(string $source): void
    {
        if (empty($source)) {
            throw new InvalidArgumentException("Source cannot be empty");
        }
    }

    // Abstract steps - must be implemented
    abstract protected function readData(string $source): array;
    abstract protected function processData(array $data): array;
    abstract protected function saveData(array $data): void;

    // Hook method with default implementation
    protected function sendNotification(): void
    {
        echo "Sending default notification\n";
    }
}

class CSVImporter extends DataImporter
{
    protected function readData(string $source): array
    {
        echo "Reading CSV from $source\n";
        // Simulate CSV reading
        return [
            ['name' => 'Alice', 'email' => 'alice@example.com'],
            ['name' => 'Bob', 'email' => 'bob@example.com']
        ];
    }

    protected function processData(array $data): array
    {
        echo "Processing CSV data\n";
        // Transform data
        return array_map(function($row) {
            return [
                'name' => strtoupper($row['name']),
                'email' => strtolower($row['email'])
            ];
        }, $data);
    }

    protected function saveData(array $data): void
    {
        echo "Saving " . count($data) . " records to database\n";
        // Simulate save
    }
}

class JSONImporter extends DataImporter
{
    protected function readData(string $source): array
    {
        echo "Reading JSON from $source\n";
        // Simulate JSON reading
        return json_decode('[{"name":"Charlie","email":"charlie@example.com"}]', true);
    }

    protected function processData(array $data): array
    {
        echo "Processing JSON data\n";
        return $data;  // No transformation needed
    }

    protected function saveData(array $data): void
    {
        echo "Saving " . count($data) . " JSON records\n";
    }

    // Override hook to customize behavior
    protected function sendNotification(): void
    {
        echo "Sending custom JSON import notification via Slack\n";
    }
}

class XMLImporter extends DataImporter
{
    // More strict validation
    protected function validateSource(string $source): void
    {
        parent::validateSource($source);

        if (!str_ends_with($source, '.xml')) {
            throw new InvalidArgumentException("Source must be an XML file");
        }
    }

    protected function readData(string $source): array
    {
        echo "Reading XML from $source\n";
        return [['name' => 'Diana', 'email' => 'diana@example.com']];
    }

    protected function processData(array $data): array
    {
        echo "Processing XML data\n";
        return $data;
    }

    protected function saveData(array $data): void
    {
        echo "Saving XML data\n";
    }
}

// Usage
$csvImporter = new CSVImporter();
$csvImporter->import("users.csv");

echo "\n";

$jsonImporter = new JSONImporter();
$jsonImporter->import("users.json");

echo "\n";

$xmlImporter = new XMLImporter();
$xmlImporter->import("users.xml");
```

::: tip Template Method Benefits
**Advantages:**
- Code reuse: Common algorithm in base class
- Flexibility: Subclasses customize specific steps
- Control: Base class controls the algorithm flow
- Extension points: Clear places for customization

**When to use:**
- Multiple classes with similar algorithms
- Want to avoid code duplication
- Need controlled extension points
- Algorithm steps are well-defined
:::

---

## Section 9: Composition vs Inheritance

### Goal

Understand when to use composition instead of inheritance.

### The Problem with Deep Inheritance

```php
<?php

declare(strict_types=1);

// ❌ BAD: Deep inheritance hierarchy
class Animal {
    public function eat(): void {
        echo "Eating...\n";
    }
}

class Mammal extends Animal {
    public function breathe(): void {
        echo "Breathing...\n";
    }
}

class Dog extends Mammal {
    public function bark(): void {
        echo "Woof!\n";
    }
}

class Bird extends Animal {
    public function fly(): void {
        echo "Flying...\n";
    }
}

// Problem: What about a Penguin? It's a bird that can't fly!
// Problem: What about a Bat? It's a mammal that can fly!
// Inheritance becomes problematic
```

### Composition Over Inheritance

```php
<?php

declare(strict_types=1);

// ✅ GOOD: Use composition
interface Eatable {
    public function eat(): void;
}

interface Swimmable {
    public function swim(): void;
}

interface Flyable {
    public function fly(): void;
}

class Dog implements Eatable, Swimmable
{
    public function eat(): void {
        echo "Dog is eating\n";
    }

    public function swim(): void {
        echo "Dog is swimming\n";
    }

    public function bark(): void {
        echo "Woof!\n";
    }
}

class Penguin implements Eatable, Swimmable
{
    public function eat(): void {
        echo "Penguin is eating fish\n";
    }

    public function swim(): void {
        echo "Penguin is swimming\n";
    }

    // Note: No fly method - penguins can't fly!
}

class Eagle implements Eatable, Flyable
{
    public function eat(): void {
        echo "Eagle is eating\n";
    }

    public function fly(): void {
        echo "Eagle is flying high\n";
    }
}

class Bat implements Eatable, Flyable
{
    public function eat(): void {
        echo "Bat is eating insects\n";
    }

    public function fly(): void {
        echo "Bat is flying at night\n";
    }
}
```

### Real-World Example: Logger with Composition

```php
<?php

declare(strict_types=1);

// Instead of inheritance hierarchy, use composition

interface LogWriter
{
    public function write(string $message): void;
}

class FileLogWriter implements LogWriter
{
    public function __construct(
        private string $filename
    ) {}

    public function write(string $message): void
    {
        echo "Writing to file {$this->filename}: $message\n";
    }
}

class DatabaseLogWriter implements LogWriter
{
    public function write(string $message): void
    {
        echo "Writing to database: $message\n";
    }
}

class CloudLogWriter implements LogWriter
{
    public function write(string $message): void
    {
        echo "Sending to cloud: $message\n";
    }
}

// Logger uses composition, not inheritance
class Logger
{
    /** @var LogWriter[] */
    private array $writers = [];

    public function addWriter(LogWriter $writer): void
    {
        $this->writers[] = $writer;
    }

    public function log(string $level, string $message): void
    {
        $formatted = "[" . date('Y-m-d H:i:s') . "] [$level] $message";

        foreach ($this->writers as $writer) {
            $writer->write($formatted);
        }
    }

    public function info(string $message): void
    {
        $this->log('INFO', $message);
    }

    public function error(string $message): void
    {
        $this->log('ERROR', $message);
    }
}

// Usage: Flexible composition
$logger = new Logger();
$logger->addWriter(new FileLogWriter('/var/log/app.log'));
$logger->addWriter(new DatabaseLogWriter());
$logger->addWriter(new CloudLogWriter());

$logger->info("Application started");
$logger->error("Something went wrong");

// Can easily change behavior at runtime
$logger2 = new Logger();
$logger2->addWriter(new FileLogWriter('/var/log/debug.log'));
$logger2->info("Debug mode");
```

### When to Use Each

| Use Inheritance When | Use Composition When |
|---------------------|---------------------|
| True "is-a" relationship | "has-a" or "uses-a" relationship |
| Subclass is a specialized version | Need flexible behavior changes |
| Shared implementation needed | Want to combine multiple behaviors |
| Polymorphism is essential | Prefer runtime flexibility |
| Hierarchy is stable | Requirements may change |

::: tip Favor Composition Over Inheritance
**"Favor composition over inheritance"** is a famous design principle because:

1. **Flexibility**: Easy to change behavior at runtime
2. **Less coupling**: Components are independent
3. **Easier testing**: Mock individual components
4. **Avoid fragile base class**: Changes to parent don't break children
5. **Multiple behaviors**: Combine features from multiple sources

**Use inheritance when:**
- True "is-a" relationship exists
- Polymorphism is needed
- Shared behavior is core to the abstraction
:::

---

## Section 10: Practical Example - Employee System

### Goal

Build a complete employee management system using inheritance.

```php
<?php

declare(strict_types=1);

abstract class Employee
{
    private static int $nextId = 1;
    protected int $id;

    public function __construct(
        protected string $name,
        protected string $email,
        protected float $baseSalary
    ) {
        $this->id = self::$nextId++;
    }

    abstract public function calculateSalary(): float;
    abstract public function getRole(): string;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getBaseSalary(): float
    {
        return $this->baseSalary;
    }

    public function getDetails(): string
    {
        return sprintf(
            "ID: %d | Name: %s | Role: %s | Salary: $%.2f",
            $this->id,
            $this->name,
            $this->getRole(),
            $this->calculateSalary()
        );
    }
}

class FullTimeEmployee extends Employee
{
    public function __construct(
        string $name,
        string $email,
        float $baseSalary,
        private float $bonus = 0
    ) {
        parent::__construct($name, $email, $baseSalary);
    }

    public function calculateSalary(): float
    {
        return $this->baseSalary + $this->bonus;
    }

    public function getRole(): string
    {
        return "Full-Time Employee";
    }

    public function setBonus(float $bonus): void
    {
        $this->bonus = $bonus;
    }
}

class ContractEmployee extends Employee
{
    public function __construct(
        string $name,
        string $email,
        private float $hourlyRate,
        private int $hoursWorked
    ) {
        parent::__construct($name, $email, 0);
    }

    public function calculateSalary(): float
    {
        return $this->hourlyRate * $this->hoursWorked;
    }

    public function getRole(): string
    {
        return "Contract Employee";
    }

    public function addHours(int $hours): void
    {
        $this->hoursWorked += $hours;
    }
}

class Manager extends FullTimeEmployee
{
    public function __construct(
        string $name,
        string $email,
        float $baseSalary,
        float $bonus,
        private int $teamSize
    ) {
        parent::__construct($name, $email, $baseSalary, $bonus);
    }

    public function getRole(): string
    {
        return "Manager (Team of {$this->teamSize})";
    }

    // Manager gets additional bonus based on team size
    public function calculateSalary(): float
    {
        $teamBonus = $this->teamSize * 500;
        return parent::calculateSalary() + $teamBonus;
    }
}

// Company class to manage employees
class Company
{
    /** @var Employee[] */
    private array $employees = [];

    public function hire(Employee $employee): void
    {
        $this->employees[] = $employee;
        echo "Hired: {$employee->getName()} as {$employee->getRole()}\n";
    }

    public function calculateTotalPayroll(): float
    {
        $total = 0;
        foreach ($this->employees as $employee) {
            $total += $employee->calculateSalary();
        }
        return $total;
    }

    public function listEmployees(): void
    {
        echo "\n=== Employee List ===\n";
        foreach ($this->employees as $employee) {
            echo $employee->getDetails() . "\n";
        }
        echo "\nTotal Payroll: $" . number_format($this->calculateTotalPayroll(), 2) . "\n";
    }

    public function getEmployeesByType(string $className): array
    {
        return array_filter(
            $this->employees,
            fn($e) => $e instanceof $className
        );
    }
}

// Usage
$company = new Company();

$company->hire(new FullTimeEmployee("Alice Johnson", "alice@company.com", 75000, 5000));
$company->hire(new FullTimeEmployee("Bob Smith", "bob@company.com", 65000, 3000));
$company->hire(new ContractEmployee("Charlie Brown", "charlie@contractor.com", 50, 160));
$company->hire(new Manager("Diana Prince", "diana@company.com", 95000, 10000, 5));

$company->listEmployees();

// Get specific employee types
$managers = $company->getEmployeesByType(Manager::class);
echo "\nManagers: " . count($managers) . "\n";
```

---

## Section 11: Advanced Inheritance Topics

### Goal
Explore advanced inheritance concepts and runtime introspection in PHP.

### Runtime Inheritance Inspection

PHP provides powerful functions to inspect inheritance relationships at runtime:

```php
<?php

declare(strict_types=1);

class Vehicle {
    protected string $model;
    
    public function __construct(string $model) {
        $this->model = $model;
    }
}

class Car extends Vehicle {
    private int $doors;
    
    public function __construct(string $model, int $doors) {
        parent::__construct($model);
        $this->doors = $doors;
    }
}

class SportsCar extends Car {
    private int $horsepower;
    
    public function __construct(string $model, int $doors, int $horsepower) {
        parent::__construct($model, $doors);
        $this->horsepower = $horsepower;
    }
}

// Runtime inspection
$sportsCar = new SportsCar("Ferrari", 2, 650);

echo get_class($sportsCar);                    // "SportsCar"
echo get_parent_class($sportsCar);             // "Car"
echo get_parent_class(\Car::class);            // "Vehicle"
echo get_parent_class(\Vehicle::class);        // false (no parent)

// Check if a class is a subclass
if (is_subclass_of($sportsCar, \Vehicle::class)) {
    echo "SportsCar is a subclass of Vehicle";
}

// Get all interfaces implemented
$interfaces = class_implements($sportsCar);
print_r($interfaces);

// Reflection for advanced inspection
$reflection = new \ReflectionClass($sportsCar);
echo "Parent: " . $reflection->getParentClass()->getName() . "\n";
echo "All methods: " . implode(", ", array_map(fn($m) => $m->getName(), $reflection->getMethods()));
```

### Object Cloning in Inheritance

When cloning objects in inheritance hierarchies, you need to handle nested objects properly:

```php
<?php

declare(strict_types=1);

class Engine {
    public int $horsepower;
    public string $type;
    
    public function __construct(int $horsepower, string $type) {
        $this->horsepower = $horsepower;
        $this->type = $type;
    }
}

class Vehicle {
    protected string $model;
    protected Engine $engine;
    
    public function __construct(string $model, Engine $engine) {
        $this->model = $model;
        $this->engine = $engine;
    }
    
    public function __clone(): void {
        // Deep clone the engine to prevent shared references
        $this->engine = clone $this->engine;
    }
}

class Car extends Vehicle {
    private int $doors;
    
    public function __construct(string $model, Engine $engine, int $doors) {
        parent::__construct($model, $engine);
        $this->doors = $doors;
    }
    
    public function __clone(): void {
        parent::__clone(); // Don't forget to call parent
        // Add custom cloning if needed
    }
}

$original = new Car("BMW", new Engine(250, "V6"), 4);
$clone = clone $original;

// Modify cloned object
$clone->engine->horsepower = 300;

// Original remains untouched thanks to __clone()
echo $original->engine->horsepower;  // 250
```

### Design Decision: Abstract Classes vs Interfaces

Here's a systematic approach for choosing between abstract classes and interfaces:

```php
<?php

declare(strict_types=1);

// Use Abstract Class when:
// 1. You have shared implementation
// 2. Classes have an "is-a" relationship
// 3. You need protected members
// 4. You want to share state
abstract class AbstractVehicle {
    protected string $licensePlate;
    
    public function __construct(string $licensePlate) {
        $this->licensePlate = $licensePlate;
    }
    
    // Shared implementation
    protected function getFormattedLicense(): string {
        return "License: " . $this->licensePlate;
    }
    
    abstract public function startEngine(): void;
}

// Use Interface when:
// 1. You only define contracts
// 2. Classes can do something (capabilities)
// 3. Multiple inheritance needed
interface Drivable {
    public function drive(float $distance): float;
}

interface Parkingable {
    public function park(): void;
}

class ElectricCar extends AbstractVehicle implements Drivable, Parkingable {
    private float $batteryLevel;
    
    public function __construct(string $licensePlate, float $batteryLevel = 100.0) {
        parent::__construct($licensePlate);
        $this->batteryLevel = $batteryLevel;
    }
    
    public function startEngine(): void {
        echo "Electric engine starting silently...";
    }
    
    public function drive(float $distance): float {
        $used = $distance * 0.5; // kWh per km
        $this->batteryLevel -= $used;
        return $this->batteryLevel;
    }
    
    public function park(): void {
        echo "Electric car parking and charging...";
    }
}
```

### Inheritance and Serialization

Here's how inheritance affects object serialization:

```php
<?php

declare(strict_types=1);

class Person {
    public string $name;
    public int $age;
    
    public function __construct(string $name, int $age) {
        $this->name = $name;
        $this->age = $age;
    }
    
    public function __serialize(): array {
        return ['name' => $this->name, 'age' => $this->age];
    }
    
    public function __unserialize(array $data): void {
        $this->name = $data['name'];
        $this->age = $data['age'];
    }
}

class Employee extends Person {
    private string $employeeId;
    protected float $salary;
    
    public function __construct(string $name, int $age, string $employeeId, float $salary) {
        parent::__construct($name, $age);
        $this->employeeId = $employeeId;
        $this->salary = $salary;
    }
    
    public function __serialize(): array {
        return array_merge(parent::__serialize(), [
            'employeeId' => $this->employeeId,
            'salary' => $this->salary
        ]);
    }
    
    public function __unserialize(array $data): void {
        parent::__unserialize($data);
        $this->employeeId = $data['employeeId'];
        $this->salary = $data['salary'];
    }
}

$employee = new Employee("Alice", 30, "EMP123", 75000);
$serialized = serialize($employee);
$unserialized = unserialize($serialized);

echo "Unserialized: " . $unserialized->name . " - " . $unserialized->employeeId;
```

::: tip Inheritance Design Guide
**Use Abstract Class when:**
- You have shared implementation code
- Classes have a true "is-a" relationship
- You need protected members or state sharing
- You want to use final methods for critical logic

**Use Interface when:**
- You need multiple inheritance
- Classes only need contracts (no implementation)
- You want to define capabilities ("can-do")
- You need maximum flexibility

**Use Traits** (Chapter 5) **when:**
- You need code reuse without inheritance
- You want horizontal composition
- Single inheritance limitation is a problem
:::

---

## Exercises

### Exercise 1: Vehicle Hierarchy

Create a vehicle hierarchy with proper inheritance.

**Requirements:**
- Abstract `Vehicle` base class
- `Car`, `Motorcycle`, and `Truck` subclasses
- Abstract `getFuelEfficiency()` method
- Calculate trip cost based on distance and fuel price

<details>
<summary>Solution</summary>

```php
<?php

declare(strict_types=1);

abstract class Vehicle
{
    public function __construct(
        protected string $make,
        protected string $model,
        protected int $year
    ) {}

    abstract public function getFuelEfficiency(): float;  // mpg

    public function calculateTripCost(float $distance, float $fuelPrice): float
    {
        $gallonsNeeded = $distance / $this->getFuelEfficiency();
        return $gallonsNeeded * $fuelPrice;
    }

    public function getInfo(): string
    {
        return "{$this->year} {$this->make} {$this->model}";
    }
}

class Car extends Vehicle
{
    public function __construct(
        string $make,
        string $model,
        int $year,
        private int $doors
    ) {
        parent::__construct($make, $model, $year);
    }

    public function getFuelEfficiency(): float
    {
        return 30.0;  // 30 mpg
    }
}

class Motorcycle extends Vehicle
{
    public function getFuelEfficiency(): float
    {
        return 50.0;  // 50 mpg
    }
}

class Truck extends Vehicle
{
    public function __construct(
        string $make,
        string $model,
        int $year,
        private float $cargoCapacity
    ) {
        parent::__construct($make, $model, $year);
    }

    public function getFuelEfficiency(): float
    {
        return 18.0;  // 18 mpg
    }
}

// Test
$vehicles = [
    new Car("Toyota", "Camry", 2024, 4),
    new Motorcycle("Harley Davidson", "Street 750", 2024),
    new Truck("Ford", "F-150", 2024, 2000)
];

$distance = 300;  // miles
$fuelPrice = 3.50;  // per gallon

foreach ($vehicles as $vehicle) {
    echo $vehicle->getInfo() . "\n";
    echo "Fuel efficiency: {$vehicle->getFuelEfficiency()} mpg\n";
    echo "Trip cost: $" . number_format($vehicle->calculateTripCost($distance, $fuelPrice), 2) . "\n\n";
}
```

</details>

### Exercise 2: Notification System

Build a notification system with multiple delivery methods.

**Requirements:**
- Abstract `Notification` class
- `EmailNotification`, `SMSNotification`, `PushNotification` subclasses
- `send()` method
- Track notification delivery status

<details>
<summary>Solution</summary>

```php
<?php

declare(strict_types=1);

abstract class Notification
{
    protected bool $sent = false;
    protected ?string $sentAt = null;

    public function __construct(
        protected string $recipient,
        protected string $message
    ) {}

    abstract protected function deliver(): bool;

    final public function send(): bool
    {
        if ($this->sent) {
            throw new RuntimeException("Notification already sent");
        }

        if ($this->deliver()) {
            $this->sent = true;
            $this->sentAt = date('Y-m-d H:i:s');
            return true;
        }

        return false;
    }

    public function isSent(): bool
    {
        return $this->sent;
    }

    public function getSentAt(): ?string
    {
        return $this->sentAt;
    }
}

class EmailNotification extends Notification
{
    public function __construct(
        string $recipient,
        string $message,
        private string $subject
    ) {
        parent::__construct($recipient, $message);
    }

    protected function deliver(): bool
    {
        echo "Sending email to {$this->recipient}\n";
        echo "Subject: {$this->subject}\n";
        echo "Message: {$this->message}\n";
        return true;
    }
}

class SMSNotification extends Notification
{
    protected function deliver(): bool
    {
        echo "Sending SMS to {$this->recipient}\n";
        echo "Message: {$this->message}\n";
        return true;
    }
}

class PushNotification extends Notification
{
    public function __construct(
        string $recipient,
        string $message,
        private string $deviceToken
    ) {
        parent::__construct($recipient, $message);
    }

    protected function deliver(): bool
    {
        echo "Sending push notification to device {$this->deviceToken}\n";
        echo "Message: {$this->message}\n";
        return true;
    }
}

// Test
$notifications = [
    new EmailNotification("user@example.com", "Welcome!", "Welcome to our service"),
    new SMSNotification("+1234567890", "Your code is: 123456"),
    new PushNotification("user123", "New message received", "device-token-xyz")
];

foreach ($notifications as $notification) {
    $notification->send();
    echo "Sent at: {$notification->getSentAt()}\n\n";
}
```

</details>

### Exercise 3: Inheritance Inspector

Build a tool to inspect inheritance relationships at runtime.

**Requirements:**
- Use `get_parent_class()`, `is_subclass_of()`, and reflection
- Display class hierarchy tree (parent → child → grandchild)
- Show implemented interfaces
- Compare regular `clone` vs deep clone behavior

<details>
<summary>Solution</summary>

```php
<?php

declare(strict_types=1);

class InheritanceInspector {
    public static function inspectClass(string $className): array {
        $reflection = new \ReflectionClass($className);
        
        return [
            'class' => $className,
            'parent' => $reflection->getParentClass()?->getName(),
            'interfaces' => class_implements($className) ?: [],
            'methods' => array_map(fn($m) => $m->getName(), $reflection->getMethods()),
            'properties' => array_keys($reflection->getDefaultProperties())
        ];
    }

    public static function showHierarchy(string $className, int $indent = 0): void {
        $spaces = str_repeat("  ", $indent);
        echo $spaces . "├── " . $className . "\n";
        
        if ($parent = \get_parent_class($className)) {
            self::showHierarchy($parent, $indent + 1);
        }
    }
}

// Test classes
class Vehicle {
    public string $type;
    public function __construct(string $type) { $this->type = $type; }
}

class Engine {
    public int $horsepower;
    public function __construct(int $horsepower) { $this->horsepower = $horsepower; }
    public function __clone(): void {
        echo "Engine cloned\n";
    }
}

class Car extends Vehicle {
    private Engine $engine;
    public function __construct(string $type, Engine $engine) {
        parent::__construct($type);
        $this->engine = $engine;
    }
    public function __clone(): void {
        echo "Deep cloning Car...\n";
        $this->engine = clone $this->engine;
    }
}

// Test the inspector
$info = InheritanceInspector::inspectClass(Car::class);
print_r($info);

InheritanceInspector::showHierarchy(Car::class);

// Test cloning
$original = new Car("Sedan", new Engine(200));
$clone = clone $original;
```

</details>

---

## Wrap-up Checklist

Before moving to the next chapter, ensure you can:

- [ ] Use the `extends` keyword to create class hierarchies
- [ ] Call parent constructors with `parent::__construct()`
- [ ] Create abstract classes and implement abstract methods
- [ ] Override methods with proper visibility rules
- [ ] Use `final` to prevent inheritance/overriding
- [ ] Understand `self::` vs `static::` vs `parent::`
- [ ] Apply polymorphism with type hinting
- [ ] Use `instanceof` for type checking
- [ ] Build class hierarchies that model real-world relationships

::: tip Ready for More?
In [Chapter 5: Interfaces & Traits](/series/php-for-java-developers/chapters/05-interfaces-and-traits), we'll explore interfaces (similar to Java) and traits (a PHP-specific feature for code reuse).
:::

<ChapterCheckbox 
  seriesId="php-for-java-developers"
  chapterId="04"
  label="Mastered PHP inheritance, abstract classes, and polymorphism!"
/>

---

## Further Reading

**PHP Documentation:**
- [Object Inheritance](https://www.php.net/manual/en/language.oop5.inheritance.php)
- [Abstract Classes](https://www.php.net/manual/en/language.oop5.abstract.php)
- [Late Static Binding](https://www.php.net/manual/en/language.oop5.late-static-bindings.php)
- [Final Keyword](https://www.php.net/manual/en/language.oop5.final.php)

---

<div style="display: flex; justify-content: space-between; margin-top: 2rem;">
  <div>
    <strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/03-oop-basics">← Chapter 3: OOP Basics</a>
  </div>
  <div>
    <strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/05-interfaces-and-traits">Chapter 5: Interfaces & Traits →</a>
  </div>
</div>
