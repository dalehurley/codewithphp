---
title: "14: Design Patterns in PHP"
description: "Master software design patterns. Implement Factory, Singleton, Observer, Strategy, Decorator, and more. Understand when and why to use each pattern in real applications."
series: "computer-science"
chapter: 14
order: 14
difficulty: "Intermediate"
prerequisites: ["Object-oriented programming", "PHP classes and interfaces"]
---

# Chapter 14: Design Patterns in PHP

## Introduction

Design patterns are reusable solutions to common software design problems. They represent best practices refined over decades and provide a shared vocabulary for developers.

In this chapter, you'll learn:

- What design patterns are
- Creational, structural, and behavioral patterns
- When to use each pattern
- PHP implementations

## What Are Design Patterns?

**Design patterns**: Proven solutions to recurring design problems.

**Benefits**:
- Provide tested solutions
- Improve code readability
- Facilitate communication
- Speed up development

## Creational Patterns

### 1. Singleton

Ensure only one instance exists.

```php
<?php

class Database {
    private static ?Database $instance = null;
    private PDO $connection;

    private function __construct() {
        $this->connection = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function query(string $sql): PDOStatement {
        return $this->connection->query($sql);
    }

    // Prevent cloning
    private function __clone() {}

    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Usage
$db = Database::getInstance();
```

**When to use**: Global state, resource management, logging

### 2. Factory Method

Create objects without specifying exact class.

```php
<?php

interface Transport {
    public function deliver(): string;
}

class Truck implements Transport {
    public function deliver(): string {
        return "Deliver by land";
    }
}

class Ship implements Transport {
    public function deliver(): string {
        return "Deliver by sea";
    }
}

abstract class Logistics {
    abstract public function createTransport(): Transport;

    public function planDelivery(): string {
        $transport = $this->createTransport();
        return $transport->deliver();
    }
}

class RoadLogistics extends Logistics {
    public function createTransport(): Transport {
        return new Truck();
    }
}

class SeaLogistics extends Logistics {
    public function createTransport(): Transport {
        return new Ship();
    }
}

// Usage
$logistics = new RoadLogistics();
echo $logistics->planDelivery(); // "Deliver by land"
```

**When to use**: Object creation complexity, multiple implementations

### 3. Builder

Construct complex objects step by step.

```php
<?php

class HttpRequest {
    private string $method = 'GET';
    private string $url;
    private array $headers = [];
    private ?string $body = null;

    public function setMethod(string $method): self {
        $this->method = $method;
        return $this;
    }

    public function setUrl(string $url): self {
        $this->url = $url;
        return $this;
    }

    public function addHeader(string $key, string $value): self {
        $this->headers[$key] = $value;
        return $this;
    }

    public function setBody(string $body): self {
        $this->body = $body;
        return $this;
    }

    public function send(): string {
        // Execute request
        return "Sent {$this->method} to {$this->url}";
    }
}

// Usage
$response = (new HttpRequest())
    ->setMethod('POST')
    ->setUrl('https://api.example.com/users')
    ->addHeader('Content-Type', 'application/json')
    ->setBody('{"name": "John"}')
    ->send();
```

**When to use**: Complex object construction, many optional parameters

## Structural Patterns

### 4. Adapter

Make incompatible interfaces work together.

```php
<?php

// Existing interface
interface PaymentGateway {
    public function pay(float $amount): bool;
}

// Third-party class with different interface
class StripePayment {
    public function makePayment(int $cents): array {
        return ['success' => true, 'amount' => $cents / 100];
    }
}

// Adapter
class StripeAdapter implements PaymentGateway {
    private StripePayment $stripe;

    public function __construct(StripePayment $stripe) {
        $this->stripe = $stripe;
    }

    public function pay(float $amount): bool {
        $result = $this->stripe->makePayment((int)($amount * 100));
        return $result['success'];
    }
}

// Usage
$stripe = new StripePayment();
$adapter = new StripeAdapter($stripe);
$adapter->pay(19.99);
```

**When to use**: Integrate third-party libraries, legacy code

### 5. Decorator

Add behavior to objects dynamically.

```php
<?php

interface Coffee {
    public function cost(): float;
    public function description(): string;
}

class SimpleCoffee implements Coffee {
    public function cost(): float {
        return 2.00;
    }

    public function description(): string {
        return "Simple coffee";
    }
}

abstract class CoffeeDecorator implements Coffee {
    protected Coffee $coffee;

    public function __construct(Coffee $coffee) {
        $this->coffee = $coffee;
    }
}

class MilkDecorator extends CoffeeDecorator {
    public function cost(): float {
        return $this->coffee->cost() + 0.50;
    }

    public function description(): string {
        return $this->coffee->description() . ", milk";
    }
}

class SugarDecorator extends CoffeeDecorator {
    public function cost(): float {
        return $this->coffee->cost() + 0.25;
    }

    public function description(): string {
        return $this->coffee->description() . ", sugar";
    }
}

// Usage
$coffee = new SimpleCoffee();
$coffee = new MilkDecorator($coffee);
$coffee = new SugarDecorator($coffee);

echo $coffee->description(); // "Simple coffee, milk, sugar"
echo $coffee->cost(); // 2.75
```

**When to use**: Add responsibilities dynamically, avoid subclass explosion

## Behavioral Patterns

### 6. Strategy

Define family of algorithms, make them interchangeable.

```php
<?php

interface SortStrategy {
    public function sort(array $data): array;
}

class BubbleSortStrategy implements SortStrategy {
    public function sort(array $data): array {
        // Bubble sort implementation
        return $data;
    }
}

class QuickSortStrategy implements SortStrategy {
    public function sort(array $data): array {
        // Quick sort implementation
        return $data;
    }
}

class Sorter {
    private SortStrategy $strategy;

    public function __construct(SortStrategy $strategy) {
        $this->strategy = $strategy;
    }

    public function setStrategy(SortStrategy $strategy): void {
        $this->strategy = $strategy;
    }

    public function sort(array $data): array {
        return $this->strategy->sort($data);
    }
}

// Usage
$sorter = new Sorter(new QuickSortStrategy());
$sorted = $sorter->sort([3, 1, 4, 1, 5]);
```

**When to use**: Multiple algorithms for same task, runtime selection

### 7. Observer

Define one-to-many dependency between objects.

```php
<?php

interface Observer {
    public function update(string $event): void;
}

class Subject {
    private array $observers = [];

    public function attach(Observer $observer): void {
        $this->observers[] = $observer;
    }

    public function notify(string $event): void {
        foreach ($this->observers as $observer) {
            $observer->update($event);
        }
    }
}

class EmailNotifier implements Observer {
    public function update(string $event): void {
        echo "Email sent for: $event\n";
    }
}

class SMSNotifier implements Observer {
    public function update(string $event): void {
        echo "SMS sent for: $event\n";
    }
}

// Usage
$subject = new Subject();
$subject->attach(new EmailNotifier());
$subject->attach(new SMSNotifier());

$subject->notify("New order received");
// Email sent for: New order received
// SMS sent for: New order received
```

**When to use**: Event systems, loosely coupled notifications

### 8. Command

Encapsulate requests as objects.

```php
<?php

interface Command {
    public function execute(): void;
}

class Light {
    public function on(): void {
        echo "Light is ON\n";
    }

    public function off(): void {
        echo "Light is OFF\n";
    }
}

class LightOnCommand implements Command {
    private Light $light;

    public function __construct(Light $light) {
        $this->light = $light;
    }

    public function execute(): void {
        $this->light->on();
    }
}

class RemoteControl {
    private ?Command $command = null;

    public function setCommand(Command $command): void {
        $this->command = $command;
    }

    public function pressButton(): void {
        $this->command?->execute();
    }
}

// Usage
$light = new Light();
$lightOn = new LightOnCommand($light);

$remote = new RemoteControl();
$remote->setCommand($lightOn);
$remote->pressButton(); // "Light is ON"
```

**When to use**: Undo/redo, queuing operations, logging

## When to Use Patterns

| Pattern | Problem | Solution |
|---------|---------|----------|
| Singleton | Need exactly one instance | Global access point |
| Factory | Complex object creation | Encapsulate instantiation |
| Builder | Many constructor parameters | Step-by-step construction |
| Adapter | Incompatible interfaces | Convert interface |
| Decorator | Add behavior dynamically | Wrap objects |
| Strategy | Multiple algorithms | Encapsulate algorithms |
| Observer | One-to-many notifications | Event system |
| Command | Parameterize requests | Object-ify operations |

## Key Takeaways

- **Design patterns** solve common design problems
- **Creational**: Object creation
- **Structural**: Object composition
- **Behavioral**: Object interaction
- Don't overuse—apply when beneficial

## Exercises

1. **Implement Repository Pattern**: Abstract data access layer.

2. **Chain of Responsibility**: Build request handler chain.

3. **Template Method**: Define algorithm skeleton, let subclasses override steps.

4. **State Pattern**: Implement object with state-dependent behavior.

## What's Next?

Design patterns help us build better software. In Chapter 15, we'll explore **Computational Complexity**—understanding the limits of computation.

---

**Further Reading**:
- [Design Patterns (Gang of Four)](https://en.wikipedia.org/wiki/Design_Patterns)
- [PHP Design Patterns](https://designpatternsphp.readthedocs.io/)
