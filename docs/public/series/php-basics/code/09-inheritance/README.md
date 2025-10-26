# Chapter 09: OOP Inheritance, Abstract Classes, and Interfaces - Code Examples

Master inheritance, abstraction, and interfaces - the pillars of object-oriented design in PHP.

## Files

1. **`09-inheritance.php`** - Basic inheritance with Employee/Manager example
2. **`09-method-overriding.php`** - Overriding parent methods
3. **`09-abstract-shapes.php`** - Abstract classes with Shape hierarchy
4. **`09-interfaces.php`** - Interface implementation
5. **`09-multiple-interfaces.php`** - Implementing multiple interfaces
6. **`09-oop-demo.php`** - Complete demonstration

## Quick Start

```bash
php 09-inheritance.php
php 09-method-overriding.php
php 09-abstract-shapes.php
php 09-interfaces.php
php 09-multiple-interfaces.php
```

## Inheritance Basics

### Extending a Class

```php
class Employee {
    protected string $name;

    public function getInfo(): string {
        return "$this->name is an employee.";
    }
}

class Manager extends Employee {
    public function approveExpense(): string {
        return "$this->name approved the expense.";
    }
}

$manager = new Manager();
$manager->getInfo();         // Inherited from Employee
$manager->approveExpense();  // Defined in Manager
```

**Key Concepts:**

- Child class inherits all public/protected members
- Use `extends` keyword
- Child can add new methods and properties
- Child can override parent methods

### Method Overriding

```php
class Animal {
    public function makeSound(): string {
        return "Some sound";
    }
}

class Dog extends Animal {
    public function makeSound(): string {
        return "Woof!";
    }
}

$dog = new Dog();
echo $dog->makeSound(); // "Woof!" (overridden)
```

### Calling Parent Methods

```php
class Child extends Parent {
    public function greet(): string {
        $parentGreeting = parent::greet();
        return $parentGreeting . " And hello from Child!";
    }
}
```

## Abstract Classes

**Purpose:** Define a template that child classes must follow.

```php
abstract class Shape {
    abstract public function area(): float;
    abstract public function perimeter(): float;

    // Can also have concrete methods
    public function describe(): string {
        return "This is a shape.";
    }
}

class Rectangle extends Shape {
    public function __construct(
        private float $width,
        private float $height
    ) {}

    public function area(): float {
        return $this->width * $this->height;
    }

    public function perimeter(): float {
        return 2 * ($this->width + $this->height);
    }
}

$rect = new Rectangle(5, 10);
echo $rect->area();      // 50
echo $rect->perimeter(); // 30
```

**Rules:**

- Cannot instantiate abstract class directly
- Child class MUST implement all abstract methods
- Can have both abstract and concrete methods
- Use when classes share common behavior

## Interfaces

**Purpose:** Define a contract that classes must implement.

```php
interface Drawable {
    public function draw(): void;
}

interface Resizable {
    public function resize(float $scale): void;
}

class Circle implements Drawable, Resizable {
    public function __construct(
        private float $radius
    ) {}

    public function draw(): void {
        echo "Drawing circle with radius: {$this->radius}";
    }

    public function resize(float $scale): void {
        $this->radius *= $scale;
    }
}
```

**Rules:**

- All methods are public and abstract (no implementation)
- A class can implement multiple interfaces
- Use `implements` keyword
- Must implement ALL interface methods

## Abstract Class vs Interface

| Feature         | Abstract Class                      | Interface                         |
| --------------- | ----------------------------------- | --------------------------------- |
| **Methods**     | Can have both abstract and concrete | Only abstract (no implementation) |
| **Properties**  | Yes                                 | No (only constants)               |
| **Multiple**    | Can extend ONE class                | Can implement MANY interfaces     |
| **Constructor** | Yes                                 | No                                |
| **Use When**    | Classes share common behavior       | Define a contract/capability      |

**Example:**

```php
// Abstract class: Shared behavior
abstract class Vehicle {
    abstract public function move(): void;

    // Concrete method shared by all vehicles
    public function startEngine(): void {
        echo "Engine started!";
    }
}

// Interface: Contract/capability
interface Flyable {
    public function fly(): void;
    public function land(): void;
}

class Airplane extends Vehicle implements Flyable {
    public function move(): void {
        echo "Flying through the air";
    }

    public function fly(): void {
        echo "Taking off!";
    }

    public function land(): void {
        echo "Landing!";
    }
}
```

## Type Hinting with Inheritance

```php
interface Payable {
    public function pay(float $amount): void;
}

class CreditCard implements Payable {
    public function pay(float $amount): void {
        echo "Paid $$amount with credit card";
    }
}

class PayPal implements Payable {
    public function pay(float $amount): void {
        echo "Paid $$amount with PayPal";
    }
}

function processPayment(Payable $method, float $amount): void {
    $method->pay($amount);
}

processPayment(new CreditCard(), 100);  // Works!
processPayment(new PayPal(), 50);      // Works!
```

## The `final` Keyword

**Prevent Overriding:**

```php
class Parent {
    final public function cannotOverride(): void {
        echo "This method is final";
    }
}

class Child extends Parent {
    // This would cause a fatal error:
    // public function cannotOverride(): void {}
}
```

**Prevent Inheritance:**

```php
final class CannotExtend {
    // No class can extend this
}
```

## Common Patterns

### Repository Pattern

```php
interface RepositoryInterface {
    public function find(int $id): ?array;
    public function all(): array;
    public function save(array $data): bool;
}

class UserRepository implements RepositoryInterface {
    public function find(int $id): ?array {
        // Database query logic
    }

    public function all(): array {
        // Return all users
    }

    public function save(array $data): bool {
        // Save user
    }
}
```

### Strategy Pattern

```php
interface SortStrategy {
    public function sort(array $data): array;
}

class BubbleSort implements SortStrategy {
    public function sort(array $data): array {
        // Bubble sort implementation
    }
}

class QuickSort implements SortStrategy {
    public function sort(array $data): array {
        // Quick sort implementation
    }
}

class Sorter {
    public function __construct(
        private SortStrategy $strategy
    ) {}

    public function sortData(array $data): array {
        return $this->strategy->sort($data);
    }
}
```

## Polymorphism

**Same interface, different implementations:**

```php
interface Logger {
    public function log(string $message): void;
}

class FileLogger implements Logger {
    public function log(string $message): void {
        file_put_contents('log.txt', $message, FILE_APPEND);
    }
}

class DatabaseLogger implements Logger {
    public function log(string $message): void {
        // Insert into database
    }
}

class EmailLogger implements Logger {
    public function log(string $message): void {
        mail('admin@example.com', 'Log', $message);
    }
}

// Use any logger interchangeably
function handleRequest(Logger $logger): void {
    $logger->log("Request received");
}
```

## Best Practices

✓ **Favor composition over inheritance**
✓ **Use interfaces for contracts**
✓ **Use abstract classes for shared behavior**
✓ **Keep inheritance hierarchies shallow** (2-3 levels max)
✓ **Follow SOLID principles**
✓ **Use type hints** for better code clarity
✓ **Document what abstract methods should do**

## Common Mistakes

❌ **Too deep inheritance** (4+ levels)

```php
Animal → Mammal → Carnivore → Feline → DomesticCat // Too deep!
```

❌ **Not implementing all interface methods**

```php
class MyClass implements MyInterface {
    // Error: Must implement all methods!
}
```

❌ **Using inheritance for code reuse only**

```php
// Bad: No "is-a" relationship
class Button extends Rectangle {} // Button is-a Rectangle?
```

## SOLID Principles Quick Reference

- **S**ingle Responsibility: One class, one job
- **O**pen/Closed: Open for extension, closed for modification
- **L**iskov Substitution: Child classes should be substitutable for parent
- **I**nterface Segregation: Many specific interfaces > one general
- **D**ependency Inversion: Depend on abstractions, not concretions

## Related Chapter

[Chapter 09: OOP - Inheritance, Abstract Classes, and Interfaces](../../chapters/09-oop-inheritance-abstract-classes-and-interfaces.md)

## Further Reading

- [PHP OOP Documentation](https://www.php.net/manual/en/language.oop5.php)
- [Design Patterns in PHP](https://designpatternsphp.readthedocs.io/)
- [SOLID Principles](https://en.wikipedia.org/wiki/SOLID)
