<?php

declare(strict_types=1);

/**
 * Object-Oriented Programming Syntax - PHP vs Python
 * 
 * This file demonstrates PHP OOP syntax differences from Python.
 * Key differences:
 * - PHP requires visibility modifiers (public, private, protected)
 * - PHP uses $this->property instead of self.property
 * - PHP uses -> operator for instance access (Python uses .)
 * - PHP uses :: operator for static access (Python uses .)
 * - PHP 8.0+ constructor property promotion
 */

// ============================================================================
// 1. Basic Class Definition
// ============================================================================

// Python:
// class User:
//     def __init__(self, name: str, email: str):
//         self.name = name
//         self.email = email

// PHP:
class User
{
    public function __construct(
        public string $name,
        public string $email
    ) {}
}

$user = new User("Alice", "alice@example.com");
echo "User: {$user->name}, Email: {$user->email}\n";

// ============================================================================
// 2. Properties and Visibility
// ============================================================================

// Python: self.balance (public by default, _protected, __private)
// PHP:    $this->balance (must specify public, protected, or private)

class BankAccount
{
    public float $balance;           // Public: accessible anywhere
    protected string $internalId;   // Protected: class and children
    private string $secret;          // Private: class only
    
    public function __construct(float $balance)
    {
        $this->balance = $balance;
        $this->internalId = "ACC-123";
        $this->secret = "hidden";
    }
    
    public function getInternalId(): string
    {
        return $this->internalId;  // Can access protected from within class
    }
}

$account = new BankAccount(1000.0);
echo "Balance: {$account->balance}\n";
echo "Internal ID: {$account->getInternalId()}\n";
// echo $account->secret;  // Error: Cannot access private property

// ============================================================================
// 3. Methods and $this
// ============================================================================

// Python: self.value
// PHP:    $this->value

class Calculator
{
    public function __construct(
        private int $value = 0
    ) {}
    
    public function add(int $amount): void
    {
        $this->value += $amount;
    }
    
    public function getValue(): int
    {
        return $this->value;
    }
}

$calc = new Calculator(10);
$calc->add(5);
echo "Value: {$calc->getValue()}\n";  // Output: 15

// ============================================================================
// 4. Static Methods and Properties
// ============================================================================

// Python:
// class MathUtils:
//     PI = 3.14159
//     @staticmethod
//     def add(a: int, b: int) -> int:
//         return a + b

// PHP:
class MathUtils
{
    public static float $PI = 3.14159;
    
    public static function add(int $a, int $b): int
    {
        return $a + $b;
    }
}

// Access static members with ::
echo "PI: " . MathUtils::$PI . "\n";
echo "Sum: " . MathUtils::add(5, 3) . "\n";

// ============================================================================
// 5. Inheritance
// ============================================================================

// Python: class Dog(Animal):
// PHP:    class Dog extends Animal

class Animal
{
    public function __construct(
        protected string $name
    ) {}
    
    public function speak(): string
    {
        return "Some sound";
    }
    
    public function getName(): string
    {
        return $this->name;
    }
}

class Dog extends Animal
{
    public function speak(): string
    {
        return "Woof!";
    }
}

$dog = new Dog("Buddy");
echo "{$dog->getName()} says: {$dog->speak()}\n";

// ============================================================================
// 6. Abstract Classes
// ============================================================================

// Python: from abc import ABC, abstractmethod
// PHP:    abstract class with abstract methods

abstract class Shape
{
    abstract public function area(): float;
    
    public function describe(): string
    {
        return "Shape with area: {$this->area()}";
    }
}

class Circle extends Shape
{
    public function __construct(
        private float $radius
    ) {}
    
    public function area(): float
    {
        return MathUtils::$PI * $this->radius * $this->radius;
    }
}

$circle = new Circle(5.0);
echo $circle->describe() . "\n";

// ============================================================================
// 7. Interfaces
// ============================================================================

// Python: Protocol (typing) or ABC
// PHP:    interface

interface Drawable
{
    public function draw(): void;
}

interface Resizable
{
    public function resize(float $scale): void;
}

class Rectangle implements Drawable, Resizable
{
    public function __construct(
        private float $width,
        private float $height
    ) {}
    
    public function draw(): void
    {
        echo "Drawing rectangle: {$this->width}x{$this->height}\n";
    }
    
    public function resize(float $scale): void
    {
        $this->width *= $scale;
        $this->height *= $scale;
    }
}

$rect = new Rectangle(10, 5);
$rect->draw();
$rect->resize(2.0);
$rect->draw();

// ============================================================================
// 8. Constructor Property Promotion (PHP 8.0+)
// ============================================================================

// Shorthand for declaring and initializing properties
class Product
{
    // Old way (PHP 7.4):
    // private string $name;
    // private float $price;
    // public function __construct(string $name, float $price) {
    //     $this->name = $name;
    //     $this->price = $price;
    // }
    
    // New way (PHP 8.0+):
    public function __construct(
        private string $name,
        private float $price
    ) {}
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getPrice(): float
    {
        return $this->price;
    }
}

$product = new Product("Laptop", 999.99);
echo "Product: {$product->getName()}, Price: \${$product->getPrice()}\n";

// ============================================================================
// 9. Traits
// ============================================================================

// Python: Mixin classes
// PHP:    traits

trait Loggable
{
    public function log(string $message): void
    {
        echo "[" . static::class . "] {$message}\n";
    }
}

trait Timestampable
{
    public function getTimestamp(): string
    {
        return date('Y-m-d H:i:s');
    }
}

class UserWithTraits
{
    use Loggable, Timestampable;
    
    public function __construct(
        public string $name
    ) {
        $this->log("User {$name} created at {$this->getTimestamp()}");
    }
}

$user = new UserWithTraits("Alice");
// Output: [UserWithTraits] User Alice created at 2024-01-01 12:00:00

// Trait conflict resolution
trait TraitA
{
    public function method(): string
    {
        return "TraitA";
    }
}

trait TraitB
{
    public function method(): string
    {
        return "TraitB";
    }
}

class MyClass
{
    use TraitA, TraitB {
        TraitA::method insteadof TraitB;  // Use TraitA's method
        TraitB::method as methodB;        // Alias TraitB's method
    }
}

$obj = new MyClass();
echo $obj->method() . "\n";   // Output: TraitA
echo $obj->methodB() . "\n";  // Output: TraitB

// ============================================================================
// 10. Enums (PHP 8.1+)
// ============================================================================

// Python: from enum import Enum
// PHP:    enum

enum Status: string
{
    case PENDING = "pending";
    case ACTIVE = "active";
    case INACTIVE = "inactive";
}

class UserStatus
{
    public function __construct(
        private Status $status
    ) {}
    
    public function getStatus(): Status
    {
        return $this->status;
    }
}

$userStatus = new UserStatus(Status::ACTIVE);
echo "Status: {$userStatus->getStatus()->value}\n";

