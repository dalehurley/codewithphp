---
title: "09: OOP: Inheritance, Abstract Classes, and Interfaces"
description: "Build powerful and flexible class structures using inheritance, define reusable blueprints with abstract classes, and enforce contracts with interfaces."
series: "php-basics"
chapter: 9
order: 9
difficulty: "Intermediate"
prerequisites:
  - "/series/php-basics/chapters/08-introduction-to-object-oriented-programming"
---

![OOP: Inheritance, Abstract Classes, and Interfaces](/images/php-basics/chapter-09-oop-inheritance-hero-full.webp)

# Chapter 09: OOP: Inheritance, Abstract Classes, and Interfaces

## Overview

In the last chapter, you learned the basics of classes and objects. Now, we're going to explore three powerful, related concepts that allow us to create flexible, scalable, and robust object-oriented systems: **Inheritance**, **Abstract Classes**, and **Interfaces**.

These concepts are all about defining relationships and contracts between classes. They help you reuse code, reduce duplication, and design software that is easier to maintain and extend over time. Understanding them is key to moving from writing simple objects to architecting real applications.

By the end of this chapter, you'll understand how to build class hierarchies, define reusable blueprints with abstract classes, and enforce contracts with interfaces—all essential skills for professional PHP development.

## Prerequisites

- PHP 8.4 installed and working
- Completion of [Chapter 08: Introduction to Object-Oriented Programming](/series/php-basics/chapters/08-introduction-to-object-oriented-programming)
- Understanding of classes, objects, properties, and methods
- A text editor and terminal
- Estimated time: **35-40 minutes**

## What You'll Build

In this chapter, you'll create:

- An employee management system demonstrating inheritance and the `protected` keyword
- Method overriding examples with the `parent::` keyword for calling parent implementations
- A shape calculation system using abstract classes and methods
- A content sharing system implementing interfaces for flexible, contract-based design
- Multiple interface implementation showing how classes can compose different behaviors
- Examples of polymorphism and type compatibility
- Usage of the `final` keyword to prevent inheritance and method overriding

## Objectives

- Use **inheritance** to create a specialized class based on an existing one
- Understand the `protected` visibility keyword
- **Override methods** from a parent class and use `parent::` to call parent implementations
- Use the `final` keyword to prevent inheritance or method overriding
- Create **abstract classes** and methods to define a base template for other classes
- Define and implement **interfaces** to guarantee that a class has certain methods
- Implement **multiple interfaces** in a single class
- Understand **polymorphism** and type compatibility in inheritance hierarchies

## Quick Start

Want to see all three concepts in action immediately? Create `oop_demo.php`:

```php
<?php

// Interface: A contract
interface Playable {
    public function play(): string;
}

// Abstract class: A template
abstract class Instrument {
    protected string $name;

    public function __construct(string $name) {
        $this->name = $name;
    }

    abstract public function makeSound(): string;
}

// Inheritance + Interface implementation
class Guitar extends Instrument implements Playable {
    public function makeSound(): string {
        return "Strum strum";
    }

    public function play(): string {
        return "Playing {$this->name}: {$this->makeSound()}";
    }
}

$guitar = new Guitar("Fender Stratocaster");
echo $guitar->play() . PHP_EOL;
```

Run it:

```bash
php oop_demo.php
# Output: Playing Fender Stratocaster: Strum strum
```

**Code**: [View complete example](https://github.com/dalehurley/codewithphp/blob/main/docs/series/php-basics/code/09-inheritance/09-oop-demo.php)

Now let's understand each concept in detail.

## Step 1: Inheritance (~5 min)

**Inheritance** is a mechanism where one class (the **child** or **subclass**) can acquire the properties and methods of another class (the **parent** or **superclass**). This is a classic "is-a" relationship. For example, a `Manager` _is a_ type of `Employee`.

This is incredibly useful for code reuse. You can define common functionality in a parent class, and then create specialized child classes that inherit that functionality and add their own.

1.  **Create a File**:
    Create a new file called `inheritance.php`.

2.  **Define Parent and Child Classes**:
    We use the `extends` keyword to establish the relationship.

```php
<?php

// Parent class
class Employee
{
    protected string $name;
    protected string $title;

    public function __construct(string $name, string $title)
    {
        $this->name = $name;
        $this->title = $title;
    }

    public function getInfo(): string
    {
        return "$this->name is a $this->title.";
    }
}

// Child class inherits from Employee
class Manager extends Employee
{
    // This class inherits the properties and methods of Employee
    // and adds its own.
    public function approveExpense(): string
    {
        return "$this->name approved the expense.";
    }
}

$employee = new Employee('Alice', 'Web Developer');
echo $employee->getInfo() . PHP_EOL;

$manager = new Manager('Bob', 'Engineering Manager');
echo $manager->getInfo() . PHP_EOL; // Can call parent's method
echo $manager->approveExpense() . PHP_EOL; // Can call its own method
```

3.  **Run the Code**:

```bash
php inheritance.php
```

**Expected Output**:

```
Alice is a Web Developer.
Bob is a Engineering Manager.
Bob approved the expense.
```

**Why it works**: The `Manager` class inherits all properties and methods from `Employee`. When you create a `Manager`, it has access to both the parent's `getInfo()` method and its own `approveExpense()` method. The `protected` keyword allows the child class to access the parent's properties directly.

::: tip PHP 8.4 Features
Notice how we use typed properties (`protected string $name`) and return type declarations (`: string`). These features, available since PHP 7.4 and enhanced in PHP 8.x, help catch bugs early and make your code more maintainable.
:::

**Code**: [View complete example](https://github.com/dalehurley/codewithphp/blob/main/docs/series/php-basics/code/09-inheritance/09-inheritance.php)

### Method Overriding and the `parent::` Keyword

One of the most powerful features of inheritance is the ability to **override** methods from the parent class. This means a child class can provide its own implementation of a method that exists in the parent.

Let's extend our employee example:

1.  **Create a File**:
    Create `method_overriding.php`.

2.  **Override a Parent Method**:

```php
<?php

class Employee
{
    protected string $name;
    protected string $title;
    protected float $salary;

    public function __construct(string $name, string $title, float $salary)
    {
        $this->name = $name;
        $this->title = $title;
        $this->salary = $salary;
    }

    public function getInfo(): string
    {
        return "$this->name is a $this->title.";
    }

    public function getAnnualBonus(): float
    {
        return $this->salary * 0.05; // 5% bonus
    }
}

class Manager extends Employee
{
    private int $teamSize;

    public function __construct(string $name, string $title, float $salary, int $teamSize)
    {
        // Call the parent constructor using parent::
        parent::__construct($name, $title, $salary);
        $this->teamSize = $teamSize;
    }

    // Override the parent's getInfo() method
    public function getInfo(): string
    {
        // Call the parent's version and add to it
        return parent::getInfo() . " (Managing {$this->teamSize} people)";
    }

    // Override the bonus calculation for managers
    public function getAnnualBonus(): float
    {
        // Managers get 10% plus $1000 per team member
        return ($this->salary * 0.10) + ($this->teamSize * 1000);
    }
}

$employee = new Employee('Alice', 'Developer', 80000);
echo $employee->getInfo() . PHP_EOL;
echo "Annual bonus: $" . number_format($employee->getAnnualBonus(), 2) . PHP_EOL;
echo PHP_EOL;

$manager = new Manager('Bob', 'Engineering Manager', 120000, 5);
echo $manager->getInfo() . PHP_EOL;
echo "Annual bonus: $" . number_format($manager->getAnnualBonus(), 2) . PHP_EOL;
```

3.  **Run the Code**:

```bash
php method_overriding.php
```

**Expected Output**:

```
Alice is a Developer.
Annual bonus: $4,000.00

Bob is a Engineering Manager. (Managing 5 people)
Annual bonus: $17,000.00
```

**Why it works**:

- The `Manager` class **overrides** both `getInfo()` and `getAnnualBonus()` methods
- The `parent::` keyword lets you call the parent class's version of a method
- In `getInfo()`, we call `parent::getInfo()` and then add extra information
- In `getAnnualBonus()`, we provide a completely different calculation
- This is powerful: you can reuse parent behavior where it makes sense, and customize where it doesn't

**Code**: [View complete example](https://github.com/dalehurley/codewithphp/blob/main/docs/series/php-basics/code/09-inheritance/09-method-overriding.php)

### The `protected` Keyword

Notice we used a new visibility keyword: `protected`.

- **`public`**: Accessible from anywhere
- **`protected`**: Accessible from within the class itself **and** from any class that extends it. Cannot be accessed from outside.
- **`private`**: Accessible only from within the class itself (not even child classes)

This is perfect for inheritance, as it allows the child class (`Manager`) to access the `$name` and `$title` properties of the parent class (`Employee`).

### Troubleshooting

**Error: "Cannot access protected property"**

- This happens if you try to access a `protected` property from outside the class hierarchy.
- Solution: Use a public getter method or change the visibility to `public` if appropriate.

**Error: "Class not found"**

- Make sure both parent and child classes are defined in the same file or properly included.
- Solution: Check your file structure and use `require` or `require_once` if classes are in separate files.

### The `final` Keyword

Sometimes you want to prevent a class from being extended, or prevent a method from being overridden. This is where the `final` keyword comes in.

**Use cases for `final`**:

- Prevent accidental modification of critical business logic
- Ensure internal implementation details aren't changed
- Create stable APIs that won't break when extended

```php
<?php

// This class cannot be extended
final class ImmutableConfig
{
    public function __construct(private array $config) {}

    public function get(string $key): mixed
    {
        return $this->config[$key] ?? null;
    }
}

// This would cause a fatal error:
// class ExtendedConfig extends ImmutableConfig {}
// Error: Class ExtendedConfig cannot extend final class ImmutableConfig

class Payment
{
    // This method cannot be overridden by child classes
    final public function processRefund(float $amount): bool
    {
        // Critical business logic that must not be changed
        if ($amount <= 0) {
            return false;
        }
        // Process refund...
        return true;
    }

    // This method CAN be overridden
    public function calculateFees(float $amount): float
    {
        return $amount * 0.029; // 2.9% fee
    }
}

class CreditCardPayment extends Payment
{
    // This would cause a fatal error:
    // public function processRefund(float $amount): bool { }
    // Error: Cannot override final method Payment::processRefund()

    // But this is fine:
    public function calculateFees(float $amount): float
    {
        return $amount * 0.035; // 3.5% fee for credit cards
    }
}
```

::: warning Use `final` Sparingly
While `final` can be useful, overusing it makes your code less flexible. Only use it when you have a good reason, such as protecting critical business logic or ensuring API stability.
:::

## Step 2: Abstract Classes and Methods (~5 min)

Sometimes, you want to create a "base" class that defines a template for other classes, but should never be instantiated on its own. For example, you might have a concept of a `Shape`, but you can't create a generic "shape"—you can only create a specific _type_ of shape, like a `Circle` or a `Square`.

This is what **abstract classes** are for. An abstract class cannot be instantiated. It can also contain **abstract methods**, which are methods that are declared but not implemented. Any child class that extends the abstract class **must** provide its own implementation for all abstract methods.

1.  **Create a File**:
    Create a new file called `abstract_shapes.php`.

2.  **Define Abstract Class and Concrete Implementations**:

```php
<?php

// An abstract class cannot be instantiated. It's a blueprint.
abstract class Shape
{
    protected string $color;

    public function __construct(string $color)
    {
        $this->color = $color;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    // An abstract method has no body. It defines a "contract".
    // Any class extending Shape MUST implement this method.
    abstract public function getArea(): float;
}

class Circle extends Shape
{
    private float $radius;

    public function __construct(string $color, float $radius)
    {
        parent::__construct($color); // Call the parent's constructor
        $this->radius = $radius;
    }

    // Provide the required implementation for the abstract method.
    public function getArea(): float
    {
        return pi() * $this->radius * $this->radius;
    }
}

class Square extends Shape
{
    private float $side;

    public function __construct(string $color, float $side)
    {
        parent::__construct($color);
        $this->side = $side;
    }

    public function getArea(): float
    {
        return $this->side * $this->side;
    }
}

// $shape = new Shape('red'); // FATAL ERROR! Cannot instantiate abstract class.

$circle = new Circle('red', 5);
echo "The red circle has an area of: " . $circle->getArea() . PHP_EOL;

$square = new Square('blue', 4);
echo "The blue square has an area of: " . $square->getArea() . PHP_EOL;
```

3.  **Run the Code**:

```bash
php abstract_shapes.php
```

**Expected Output**:

```
The red circle has an area of: 78.539816339745
The blue square has an area of: 16
```

**Why it works**: The `Shape` class is abstract—you cannot create a generic "shape", only specific types like `Circle` or `Square`. The abstract `getArea()` method forces every concrete shape class to provide its own calculation logic. This ensures consistency while allowing flexibility.

::: tip Abstract Classes vs Interfaces
Use abstract classes when child classes share **implementation** (like the `getColor()` method in `Shape`). Use interfaces when you only want to define **behavior contracts** without any shared implementation. You'll see interfaces in the next step!
:::

**Code**: [View complete example](https://github.com/dalehurley/codewithphp/blob/main/docs/series/php-basics/code/09-inheritance/09-abstract-shapes.php)

### Troubleshooting

**Error: "Cannot instantiate abstract class"**

- This happens when you try to use `new` on an abstract class.
- Solution: Only instantiate concrete (non-abstract) classes that extend the abstract class.

**Error: "Class contains abstract method and must therefore be declared abstract"**

- This happens when a class extends an abstract class but doesn't implement all required abstract methods.
- Solution: Implement all abstract methods from the parent, or mark your class as `abstract` as well.

## Step 3: Interfaces (~5 min)

An **interface** is similar to an abstract class, but it's even more abstract. An interface is a "contract" that defines a set of methods a class **must** implement. It contains no properties and no method bodies—only method signatures.

Interfaces are used when you want to enforce that different, unrelated classes share a common behavior. For example, you might want both a `BlogPost` and an `Image` to be "shareable" on social media. They are different things, but they both should have a `share()` method.

We use the `implements` keyword for interfaces.

1.  **Create a File**:
    Create a new file called `interfaces.php`.

2.  **Define an Interface and Implementing Classes**:

```php
<?php

// An interface defines a contract of methods.
interface Shareable
{
    public function share(): string;
}

class BlogPost implements Shareable
{
    private string $title;
    private string $content;

    public function __construct(string $title, string $content)
    {
        $this->title = $title;
        $this->content = $content;
    }

    // Must implement the share() method from Shareable
    public function share(): string
    {
        return "Sharing blog post: {$this->title}";
    }
}

class Image implements Shareable
{
    private string $url;
    private string $altText;

    public function __construct(string $url, string $altText)
    {
        $this->url = $url;
        $this->altText = $altText;
    }

    // Must implement the share() method from Shareable
    public function share(): string
    {
        return "Sharing image: {$this->url} ({$this->altText})";
    }
}

// This function accepts ANY object that implements Shareable
function processShareable(Shareable $item): void
{
    echo $item->share() . PHP_EOL;
}

$post = new BlogPost("My Awesome Trip", "I went to the mountains...");
$image = new Image("/images/trip.jpg", "Mountain landscape");

processShareable($post);
processShareable($image);

// Both BlogPost and Image are completely different classes,
// but they can both be used in processShareable() because
// they share the Shareable interface contract.
```

3.  **Run the Code**:

```bash
php interfaces.php
```

**Expected Output**:

```
Sharing blog post: My Awesome Trip
Sharing image: /images/trip.jpg (Mountain landscape)
```

**Why it works**: The `processShareable` function can accept _any_ object, as long as that object implements the `Shareable` interface. This makes the code incredibly flexible and decoupled. You can add new shareable types (like `Video` or `Podcast`) without changing the `processShareable` function.

**Code**: [View complete example](https://github.com/dalehurley/codewithphp/blob/main/docs/series/php-basics/code/09-inheritance/09-interfaces.php)

### Understanding Polymorphism

What we just demonstrated is called **polymorphism**—one of the fundamental principles of OOP. Polymorphism means "many forms." In programming, it allows us to write code that works with objects of different types, as long as they share a common interface.

In the example above:

- `processShareable()` doesn't care whether it receives a `BlogPost` or an `Image`
- It only cares that the object has a `share()` method
- This is **interface-based polymorphism**

**Type compatibility**: When you type-hint an interface (or parent class), PHP accepts any object that implements that interface (or extends that class). This is incredibly powerful for writing flexible, extensible code.

```php
<?php

interface Renderable
{
    public function render(): string;
}

class HtmlPage implements Renderable
{
    public function render(): string
    {
        return "<html><body>HTML Content</body></html>";
    }
}

class JsonResponse implements Renderable
{
    public function render(): string
    {
        return '{"status": "success"}';
    }
}

class XmlDocument implements Renderable
{
    public function render(): string
    {
        return '<?xml version="1.0"?><root>XML Content</root>';
    }
}

// Polymorphic function: works with ANY Renderable
function outputResponse(Renderable $response): void
{
    echo $response->render() . PHP_EOL;
}

// All three different classes can be used interchangeably
outputResponse(new HtmlPage());
outputResponse(new JsonResponse());
outputResponse(new XmlDocument());
```

::: tip The Power of Polymorphism
Polymorphism lets you write functions and classes that work with **types that don't exist yet**. When you write `function process(Shareable $item)`, any future class that implements `Shareable` will automatically work with your function—without changing a single line of existing code!
:::

### Multiple Interface Implementation

Unlike inheritance (where a class can only extend one parent), a class can implement **multiple interfaces**. This gives you incredible flexibility in composing behavior.

1.  **Create a File**:
    Create `multiple_interfaces.php`.

2.  **Implement Multiple Interfaces**:

```php
<?php

interface Shareable
{
    public function share(): string;
}

interface Searchable
{
    public function getSearchableContent(): string;
}

interface Cacheable
{
    public function getCacheKey(): string;
    public function getCacheDuration(): int; // in seconds
}

// BlogPost implements all three interfaces
class BlogPost implements Shareable, Searchable, Cacheable
{
    public function __construct(
        private string $title,
        private string $content,
        private int $id
    ) {}

    // From Shareable
    public function share(): string
    {
        return "Share: {$this->title}";
    }

    // From Searchable
    public function getSearchableContent(): string
    {
        return $this->title . ' ' . $this->content;
    }

    // From Cacheable
    public function getCacheKey(): string
    {
        return "blog_post_{$this->id}";
    }

    public function getCacheDuration(): int
    {
        return 3600; // Cache for 1 hour
    }
}

// Video only implements two of them
class Video implements Shareable, Cacheable
{
    public function __construct(
        private string $url,
        private int $id
    ) {}

    public function share(): string
    {
        return "Share video: {$this->url}";
    }

    public function getCacheKey(): string
    {
        return "video_{$this->id}";
    }

    public function getCacheDuration(): int
    {
        return 7200; // Cache for 2 hours
    }
}

// Functions can require specific interfaces
function shareItem(Shareable $item): void
{
    echo $item->share() . PHP_EOL;
}

function cacheItem(Cacheable $item): void
{
    echo "Caching with key: {$item->getCacheKey()} for {$item->getCacheDuration()}s" . PHP_EOL;
}

function searchContent(Searchable $item): void
{
    echo "Indexing: {$item->getSearchableContent()}" . PHP_EOL;
}

$post = new BlogPost("PHP Interfaces", "Learn about interfaces in PHP...", 1);
$video = new Video("https://example.com/video.mp4", 100);

// BlogPost can be used with all three functions
shareItem($post);
cacheItem($post);
searchContent($post);

echo PHP_EOL;

// Video can be used with two of them
shareItem($video);
cacheItem($video);
// searchContent($video); // This would cause an error - Video is not Searchable
```

3.  **Run the Code**:

```bash
php multiple_interfaces.php
```

**Expected Output**:

```
Share: PHP Interfaces
Caching with key: blog_post_1 for 3600s
Indexing: PHP Interfaces Learn about interfaces in PHP...

Share video: https://example.com/video.mp4
Caching with key: video_100 for 7200s
```

**Why it works**: Each interface defines a specific capability. Classes can "opt-in" to as many capabilities as they need by implementing multiple interfaces. This is far more flexible than trying to express all these relationships through inheritance.

**Code**: [View complete example](https://github.com/dalehurley/codewithphp/blob/main/docs/series/php-basics/code/09-inheritance/09-multiple-interfaces.php)

::: tip Interface Segregation
It's better to have many small, focused interfaces (like `Shareable`, `Cacheable`) than one large interface with many methods. This principle is called **Interface Segregation** and is part of the SOLID design principles. Classes should only implement the interfaces they actually need.
:::

### Troubleshooting

**Error: "Class must implement interface method"**

- This happens when you implement an interface but forget to define all required methods.
- Solution: Implement all methods declared in the interface with matching signatures (parameters and return types).

**Error: "Declaration must be compatible with interface"**

- This happens when your method signature doesn't match the interface definition.
- Solution: Check that parameter types, return types, and method names match exactly.

## Exercises

Test your understanding with these hands-on challenges:

### Exercise 1: Vehicle Hierarchy (~10 min)

Build an inheritance hierarchy for vehicles:

1. Create a `Vehicle` base class with:

   - `protected` properties for `make` (string) and `model` (string)
   - A constructor that sets these properties
   - A public `getDetails()` method that returns a formatted string

2. Create a `Car` class that extends `Vehicle` and adds:

   - A public `startEngine()` method that returns "Starting the car engine..."

3. Create a `Truck` class that extends `Vehicle` and adds:

   - A `protected` property `$cargoCapacity` (int)
   - A public `loadCargo()` method that returns "Loading cargo..."

4. Instantiate both classes and test all methods

**Expected behavior**: Both `Car` and `Truck` should be able to call `getDetails()` from the parent class, plus their own specialized methods.

### Exercise 2: Payment System with Abstract Classes (~15 min)

Design an abstract payment system:

1. Create an abstract `PaymentMethod` class with:

   - An abstract method `processPayment(float $amount): string`
   - A concrete method `formatAmount(float $amount): string` that formats to 2 decimal places

2. Create concrete classes:

   - `CreditCard` that implements `processPayment()` to return "Processed $X via credit card"
   - `PayPal` that implements `processPayment()` to return "Processed $X via PayPal"

3. Create a function `checkout(PaymentMethod $method, float $amount)` that calls `processPayment()`

4. Test with different payment methods

### Exercise 3: Notification System with Interfaces (~15 min)

Build a flexible notification system:

1. Create a `Notifiable` interface with:

   - A method `send(string $message): void`

2. Implement the interface in three classes:

   - `EmailNotification` - outputs "Email: [message]"
   - `SmsNotification` - outputs "SMS: [message]"
   - `PushNotification` - outputs "Push: [message]"

3. Create a function `notifyUser(Notifiable $channel, string $message)` that sends notifications

4. Create an array of different notification channels and loop through them

**Challenge**: Add a `Logger` class that also implements `Notifiable` to demonstrate how unrelated classes can share an interface.

## Wrap-up

Congratulations! You've just learned some of the most important concepts in object-oriented design:

- **Inheritance** allows you to create specialized classes based on existing ones, reducing code duplication and creating clear "is-a" relationships
- **Method overriding** lets child classes customize parent behavior while optionally calling the parent implementation with `parent::`
- **The `protected` keyword** gives child classes access to parent properties while maintaining encapsulation
- **The `final` keyword** prevents classes from being extended or methods from being overridden when you need to protect critical logic
- **Abstract classes** provide templates that cannot be instantiated directly, forcing child classes to implement specific behaviors
- **Interfaces** define contracts that completely unrelated classes can share, enabling flexible, decoupled design
- **Polymorphism** allows you to write code that works with many different types through shared interfaces or parent classes
- **Multiple interfaces** let classes compose different capabilities without the limitations of single inheritance

### What You've Accomplished

You can now:

- Build class hierarchies using `extends`
- Override parent methods and use `parent::` to call parent implementations
- Use `protected` properties effectively in inheritance
- Apply the `final` keyword to classes and methods when appropriate
- Create abstract base classes with `abstract` methods
- Define and implement interfaces using `implements`
- Implement multiple interfaces in a single class
- Write polymorphic functions that accept any class implementing an interface
- Understand type compatibility in class hierarchies
- Design flexible systems that are easy to extend and maintain

### Key Takeaways

- Use **inheritance** when you have a clear "is-a" relationship (Manager _is an_ Employee)
- Use **method overriding** to customize parent behavior; use `parent::` when you want to extend rather than replace
- Use the **`final` keyword** sparingly—only when you have a compelling reason to prevent extension
- Use **abstract classes** when you want to share code _and_ enforce implementation of specific methods
- Use **interfaces** when you want to define behavior contracts without inheritance relationships
- **Polymorphism** is the ability to use different object types interchangeably based on shared interfaces or parent classes
- A class can only extend _one_ parent class, but can implement _multiple_ interfaces
- Favor **composition** (multiple interfaces) over deep inheritance hierarchies

### When to Use Each

| Concept            | Use When                                                       | Example                         |
| ------------------ | -------------------------------------------------------------- | ------------------------------- |
| **Inheritance**    | Child class needs parent's functionality                       | `Manager extends Employee`      |
| **Abstract Class** | Related classes share code and must implement specific methods | `Circle extends Shape`          |
| **Interface**      | Unrelated classes need common behavior                         | `BlogPost implements Shareable` |

## Next Steps

In the [next chapter](/series/php-basics/chapters/10-oop-traits-and-namespaces), we'll cover two more essential OOP tools: **traits** for reusing method implementations across unrelated classes, and **namespaces** for organizing your code in larger projects.

## Further Reading

- [PHP Manual: Object Inheritance](https://www.php.net/manual/en/language.oop5.inheritance.php)
- [PHP Manual: Class Abstraction](https://www.php.net/manual/en/language.oop5.abstract.php)
- [PHP Manual: Object Interfaces](https://www.php.net/manual/en/language.oop5.interfaces.php)
- [PHP Manual: The `final` Keyword](https://www.php.net/manual/en/language.oop5.final.php)
- [SOLID Principles](https://en.wikipedia.org/wiki/SOLID) - Professional OOP design principles
- [Polymorphism in OOP](<https://en.wikipedia.org/wiki/Polymorphism_(computer_science)>) - Deep dive into polymorphism concepts

## Knowledge Check

Test your understanding of inheritance, abstract classes, and interfaces:

<Quiz
title="Chapter 09 Quiz: OOP Advanced Concepts"
:questions="[
{
question: 'What is the difference between an abstract class and an interface?',
options: [
{ text: 'Abstract classes can have implementation code, interfaces cannot', correct: true, explanation: 'Abstract classes can have both abstract and concrete methods with implementations. Interfaces only define method signatures.' },
{ text: 'Interfaces can have properties, abstract classes cannot', correct: false, explanation: 'It\'s the opposite: abstract classes can have properties, interfaces cannot (though they can define constants).' },
{ text: 'You can extend multiple abstract classes but only one interface', correct: false, explanation: 'It\'s the opposite: you can only extend one class but implement multiple interfaces.' },
{ text: 'They are exactly the same', correct: false, explanation: 'They serve different purposes: abstract classes share code, interfaces define contracts.' }
]
},
{
question: 'What does the protected keyword mean in inheritance?',
options: [
{ text: 'The property/method is accessible in the class and its child classes', correct: true, explanation: 'Protected members are visible to the class itself and any classes that extend it, but not to outside code.' },
{ text: 'The property/method is accessible everywhere', correct: false, explanation: 'That\'s public; protected limits access to the class and its descendants.' },
{ text: 'The property/method is only accessible in the class itself', correct: false, explanation: 'That\'s private; protected also allows child class access.' },
{ text: 'The property/method cannot be overridden', correct: false, explanation: 'That\'s what final does; protected is about visibility.' }
]
},
{
question: 'How many interfaces can a class implement?',
options: [
{ text: 'As many as needed using comma-separated list', correct: true, explanation: 'A class can implement multiple interfaces: class MyClass implements Interface1, Interface2, Interface3 {}' },
{ text: 'Only one interface', correct: false, explanation: 'Unlike class extension (only one parent), you can implement multiple interfaces.' },
{ text: 'Maximum of three interfaces', correct: false, explanation: 'There\'s no limit on the number of interfaces a class can implement.' },
{ text: 'None, interfaces cannot be implemented', correct: false, explanation: 'Interfaces are specifically designed to be implemented by classes.' }
]
},
{
question: 'What does the parent:: keyword do?',
options: [
{ text: 'Calls a method from the parent class', correct: true, explanation: 'parent:: allows you to call the parent class\'s version of a method from an overriding child method.' },
{ text: 'Creates a new parent class', correct: false, explanation: 'parent:: is for calling parent methods, not creating classes.' },
{ text: 'Prevents method overriding', correct: false, explanation: 'That\'s what the final keyword does; parent:: calls parent implementations.' },
{ text: 'Checks if a class has a parent', correct: false, explanation: 'parent:: calls parent methods; use get_parent_class() to check hierarchy.' }
]
},
{
question: 'What happens if you try to instantiate an abstract class directly?',
options: [
{ text: 'PHP throws a fatal error', correct: true, explanation: 'Abstract classes cannot be instantiated directly; they must be extended by concrete classes.' },
{ text: 'It works normally', correct: false, explanation: 'Abstract classes are templates and cannot be instantiated.' },
{ text: 'PHP creates an incomplete object', correct: false, explanation: 'PHP prevents instantiation entirely with a fatal error.' },
{ text: 'Only abstract methods are available', correct: false, explanation: 'You can\'t create the object at all; instantiation is forbidden.' }
]
}
]"
/>
