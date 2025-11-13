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
- A Shape hierarchy with Circle, Rectangle, and Triangle
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

PHP 7.4+ enforces return type compatibility (covariance):

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
    // ✅ Covariant return type (PHP 7.4+)
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

## Section 7: Practical Example - Employee System

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
