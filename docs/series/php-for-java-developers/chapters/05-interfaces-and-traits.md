---
title: "05: Interfaces & Traits"
description: "Master PHP interfaces and traits for flexible code reuse with Java comparisons"
series: "php-for-java-developers"
chapter: 5
order: 5
difficulty: "Intermediate"
prerequisites:
  - "/series/php-for-java-developers/chapters/04-classes-and-inheritance"
---

![Interfaces and Traits Hero](/images/php-for-java-developers/chapter-05-interfaces-traits-hero-full.webp)

# Chapter 5: Interfaces & Traits

<Badge type="warning">Intermediate</Badge> <Badge type="info">90-120 min</Badge>

## Overview

PHP interfaces work almost identically to Java interfaces—they define contracts that classes must implement. However, PHP has a unique feature called **traits** that provides horizontal code reuse without inheritance. Think of traits as a way to copy-paste methods into classes, solving the "diamond problem" and allowing for flexible composition.

In this chapter, we'll master both interfaces (familiar to you) and traits (PHP's powerful addition).

## Prerequisites

::: info Time Estimate
⏱️ **90-120 minutes** to complete this chapter
:::

**What you need:**
- Completed [Chapter 4: Classes & Inheritance](/series/php-for-java-developers/chapters/04-classes-and-inheritance)
- Understanding of Java interfaces
- Familiarity with composition vs inheritance

## What You'll Build

In this chapter, you'll create:
- A payment gateway system using interfaces
- A logging system using traits
- A cache implementation with multiple traits
- A comprehensive example combining interfaces and traits

## Learning Objectives

By the end of this chapter, you'll be able to:

- **Define and implement interfaces** in PHP
- **Use multiple interfaces** in a single class
- **Understand traits** and their use cases
- **Compose classes** using multiple traits
- **Resolve trait conflicts** when methods collide
- **Combine interfaces and traits** effectively
- **Choose between** abstract classes, interfaces, and traits

---

## Section 1: Interfaces in PHP

### Goal

Master PHP interfaces and understand their similarities with Java.

### Basic Interface Definition

::: code-group

```php [PHP Interface]
<?php

declare(strict_types=1);

interface Drawable
{
    public function draw(): void;
    public function getColor(): string;
}

interface Resizable
{
    public function resize(float $scale): void;
    public function getWidth(): float;
    public function getHeight(): float;
}

// Implement one interface
class Circle implements Drawable
{
    public function __construct(
        private float $radius,
        private string $color
    ) {}

    public function draw(): void
    {
        echo "Drawing a {$this->color} circle with radius {$this->radius}\n";
    }

    public function getColor(): string
    {
        return $this->color;
    }
}

// Implement multiple interfaces
class Rectangle implements Drawable, Resizable
{
    public function __construct(
        private float $width,
        private float $height,
        private string $color
    ) {}

    public function draw(): void
    {
        echo "Drawing a {$this->color} rectangle {$this->width}x{$this->height}\n";
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function resize(float $scale): void
    {
        $this->width *= $scale;
        $this->height *= $scale;
    }

    public function getWidth(): float
    {
        return $this->width;
    }

    public function getHeight(): float
    {
        return $this->height;
    }
}

// Polymorphism with interfaces
function renderShape(Drawable $shape): void
{
    echo "Color: {$shape->getColor()}\n";
    $shape->draw();
}

$circle = new Circle(5, "red");
$rectangle = new Rectangle(10, 20, "blue");

renderShape($circle);
renderShape($rectangle);

// Type checking
if ($rectangle instanceof Resizable) {
    $rectangle->resize(1.5);
}
```

```java [Java Interface]
interface Drawable {
    void draw();
    String getColor();
}

interface Resizable {
    void resize(double scale);
    double getWidth();
    double getHeight();
}

// Implement one interface
class Circle implements Drawable {
    private double radius;
    private String color;

    public Circle(double radius, String color) {
        this.radius = radius;
        this.color = color;
    }

    @Override
    public void draw() {
        System.out.println("Drawing a " + color + " circle with radius " + radius);
    }

    @Override
    public String getColor() {
        return color;
    }
}

// Implement multiple interfaces
class Rectangle implements Drawable, Resizable {
    private double width;
    private double height;
    private String color;

    public Rectangle(double width, double height, String color) {
        this.width = width;
        this.height = height;
        this.color = color;
    }

    @Override
    public void draw() {
        System.out.println("Drawing a " + color + " rectangle " + width + "x" + height);
    }

    @Override
    public String getColor() {
        return color;
    }

    @Override
    public void resize(double scale) {
        this.width *= scale;
        this.height *= scale;
    }

    @Override
    public double getWidth() {
        return width;
    }

    @Override
    public double getHeight() {
        return height;
    }
}
```

:::

### Interface Rules

| Rule | PHP | Java |
|------|-----|------|
| **Method implementation** | No implementation (before PHP 8) | No implementation (before Java 8) |
| **Multiple interfaces** | ✅ Yes | ✅ Yes |
| **Properties/fields** | ❌ No | ❌ No (only constants) |
| **Constants** | ✅ Yes | ✅ Yes |
| **Visibility** | All public | All public |
| **Extend interfaces** | ✅ Yes | ✅ Yes |
| **Default methods** | ❌ No (use traits) | ✅ Yes (Java 8+) |

### Interface Constants

```php
<?php

declare(strict_types=1);

interface HttpStatus
{
    public const OK = 200;
    public const CREATED = 201;
    public const BAD_REQUEST = 400;
    public const UNAUTHORIZED = 401;
    public const NOT_FOUND = 404;
    public const SERVER_ERROR = 500;
}

class Response implements HttpStatus
{
    public function __construct(
        private int $statusCode,
        private string $body
    ) {}

    public function isSuccess(): bool
    {
        return $this->statusCode >= self::OK && $this->statusCode < 300;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}

// Access constants
echo HttpStatus::OK;  // 200
$response = new Response(HttpStatus::CREATED, "Resource created");
```

### Extending Interfaces

```php
<?php

declare(strict_types=1);

interface Readable
{
    public function read(string $key): mixed;
}

interface Writable
{
    public function write(string $key, mixed $value): void;
}

// Interface extending multiple interfaces
interface Storage extends Readable, Writable
{
    public function delete(string $key): void;
    public function exists(string $key): bool;
}

class FileStorage implements Storage
{
    private array $data = [];

    public function read(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    public function write(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function delete(string $key): void
    {
        unset($this->data[$key]);
    }

    public function exists(string $key): bool
    {
        return isset($this->data[$key]);
    }
}
```

---

## Section 2: Introduction to Traits

### Goal

Understand PHP traits and how they differ from Java concepts.

### What Are Traits?

Traits are PHP's mechanism for horizontal code reuse. They're like "copy-paste on steroids"—methods defined in a trait are copied into classes that use them.

::: code-group

```php [PHP Traits]
<?php

declare(strict_types=1);

// Define a trait
trait Timestampable
{
    protected ?string $createdAt = null;
    protected ?string $updatedAt = null;

    public function setCreatedAt(): void
    {
        $this->createdAt = date('Y-m-d H:i:s');
    }

    public function setUpdatedAt(): void
    {
        $this->updatedAt = date('Y-m-d H:i:s');
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }
}

// Use trait in a class
class Article
{
    use Timestampable;  // "Copy-paste" trait methods here

    public function __construct(
        private string $title,
        private string $content
    ) {
        $this->setCreatedAt();
    }

    public function update(string $content): void
    {
        $this->content = $content;
        $this->setUpdatedAt();
    }
}

// Another class using the same trait
class Comment
{
    use Timestampable;

    public function __construct(
        private string $text,
        private string $authorId
    ) {
        $this->setCreatedAt();
    }
}

// Usage
$article = new Article("My Title", "Content");
echo "Created: " . $article->getCreatedAt() . "\n";

sleep(1);
$article->update("Updated content");
echo "Updated: " . $article->getUpdatedAt() . "\n";
```

```java [Java - No Direct Equivalent]
// Java doesn't have traits, but you can achieve similar results with:

// 1. Interfaces with default methods (Java 8+)
interface Timestampable {
    default void setCreatedAt() {
        // Can't store state in interface
        // Need to use abstract methods
    }

    String getCreatedAt();
    String getUpdatedAt();
}

// 2. Composition (preferred in Java)
class Timestamps {
    private String createdAt;
    private String updatedAt;

    public void setCreatedAt() {
        this.createdAt = LocalDateTime.now().toString();
    }

    public void setUpdatedAt() {
        this.updatedAt = LocalDateTime.now().toString();
    }

    public String getCreatedAt() {
        return createdAt;
    }

    public String getUpdatedAt() {
        return updatedAt;
    }
}

class Article {
    private Timestamps timestamps = new Timestamps();  // Composition
    private String title;
    private String content;

    public Article(String title, String content) {
        this.title = title;
        this.content = content;
        timestamps.setCreatedAt();
    }

    public String getCreatedAt() {
        return timestamps.getCreatedAt();
    }
}
```

:::

### Traits vs Java Concepts

| Feature | PHP Trait | Java Interface (8+) | Java Composition |
|---------|-----------|---------------------|------------------|
| **Code reuse** | Horizontal (copy-paste) | Vertical (inheritance) | Delegation |
| **State (properties)** | ✅ Yes | ❌ No | ✅ Yes |
| **Multiple use** | ✅ Yes | ✅ Yes | ✅ Yes |
| **Method conflicts** | Manual resolution | No conflicts | Manual delegation |
| **Instance of** | No | Yes | No |

::: tip When to Use Traits
Use traits when:
- **Multiple unrelated classes** need the same functionality
- **Inheritance isn't appropriate** (no "is-a" relationship)
- **You need to share state** (properties) across classes
- **Avoiding code duplication** without creating inheritance hierarchy

**Don't use traits for:**
- Core functionality (use inheritance)
- Defining contracts (use interfaces)
- When composition would be clearer
:::

---

## Section 3: Using Multiple Traits

### Goal

Learn to compose classes using multiple traits.

### Multiple Trait Usage

```php
<?php

declare(strict_types=1);

trait Loggable
{
    protected array $logs = [];

    public function log(string $message): void
    {
        $this->logs[] = [
            'message' => $message,
            'time' => date('Y-m-d H:i:s')
        ];
    }

    public function getLogs(): array
    {
        return $this->logs;
    }
}

trait Cacheable
{
    private array $cache = [];

    protected function getCached(string $key): mixed
    {
        return $this->cache[$key] ?? null;
    }

    protected function setCached(string $key, mixed $value): void
    {
        $this->cache[$key] = $value;
    }

    protected function clearCache(): void
    {
        $this->cache = [];
    }
}

trait Validatable
{
    protected array $errors = [];

    abstract protected function rules(): array;

    public function validate(): bool
    {
        $this->errors = [];
        $rules = $this->rules();

        foreach ($rules as $field => $rule) {
            if (!isset($this->$field) || empty($this->$field)) {
                $this->errors[$field] = "$field is required";
            }
        }

        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

// Use multiple traits
class User
{
    use Loggable, Cacheable, Validatable;

    public function __construct(
        public string $name = '',
        public string $email = ''
    ) {}

    protected function rules(): array
    {
        return ['name' => 'required', 'email' => 'required'];
    }

    public function save(): bool
    {
        $this->log("Attempting to save user");

        if (!$this->validate()) {
            $this->log("Validation failed");
            return false;
        }

        // Check cache
        $cached = $this->getCached("user_{$this->email}");
        if ($cached) {
            $this->log("User found in cache");
            return true;
        }

        // Save logic here
        $this->setCached("user_{$this->email}", $this);
        $this->log("User saved successfully");

        return true;
    }
}

// Usage
$user = new User("Alice", "alice@example.com");
$user->save();

print_r($user->getLogs());
```

---

## Section 4: Trait Conflicts and Resolution

### Goal

Handle method name conflicts when using multiple traits.

### Conflict Resolution

```php
<?php

declare(strict_types=1);

trait Logger
{
    public function log(string $message): void
    {
        echo "[Logger] $message\n";
    }
}

trait Auditor
{
    public function log(string $message): void
    {
        echo "[Auditor] $message\n";
    }
}

// ERROR: Both traits have log() method!
// class Service
// {
//     use Logger, Auditor;  // Fatal error: Trait method collision
// }

// Solution 1: Choose one method with 'insteadof'
class ServiceA
{
    use Logger, Auditor {
        Logger::log insteadof Auditor;  // Use Logger's log, not Auditor's
    }
}

// Solution 2: Rename conflicting methods with 'as'
class ServiceB
{
    use Logger, Auditor {
        Logger::log as logToLogger;
        Auditor::log as logToAuditor;
    }

    public function process(): void
    {
        $this->logToLogger("Processing with Logger");
        $this->logToAuditor("Auditing with Auditor");
    }
}

// Solution 3: Combine both
class ServiceC
{
    use Logger, Auditor {
        Logger::log insteadof Auditor;  // Default to Logger
        Auditor::log as auditLog;        // But keep Auditor as alias
    }

    public function process(): void
    {
        $this->log("Default log");  // Uses Logger::log
        $this->auditLog("Audit log");  // Uses Auditor::log
    }
}

// Test
$serviceA = new ServiceA();
$serviceA->log("Test A");  // [Logger] Test A

$serviceB = new ServiceB();
$serviceB->process();

$serviceC = new ServiceC();
$serviceC->process();
```

### Changing Method Visibility

```php
<?php

declare(strict_types=1);

trait Helper
{
    private function internalMethod(): string
    {
        return "Internal helper";
    }

    protected function protectedMethod(): string
    {
        return "Protected helper";
    }
}

class MyClass
{
    use Helper {
        internalMethod as public;  // Make private method public
        protectedMethod as public publicProtected;  // Rename and make public
    }
}

$obj = new MyClass();
echo $obj->internalMethod();  // Now accessible!
echo $obj->publicProtected();  // Renamed and public
```

---

## Section 5: Traits Using Traits

### Goal

Understand trait composition—traits using other traits.

### Nested Traits

```php
<?php

declare(strict_types=1);

trait TimestampTrait
{
    protected ?string $timestamp = null;

    protected function setTimestamp(): void
    {
        $this->timestamp = date('Y-m-d H:i:s');
    }

    public function getTimestamp(): ?string
    {
        return $this->timestamp;
    }
}

trait AuditTrait
{
    use TimestampTrait;  // Trait using another trait

    protected array $auditLog = [];

    protected function audit(string $action): void
    {
        $this->setTimestamp();
        $this->auditLog[] = [
            'action' => $action,
            'timestamp' => $this->getTimestamp()
        ];
    }

    public function getAuditLog(): array
    {
        return $this->auditLog;
    }
}

trait SoftDeleteTrait
{
    use TimestampTrait;

    protected ?string $deletedAt = null;

    public function softDelete(): void
    {
        $this->setTimestamp();
        $this->deletedAt = $this->getTimestamp();
    }

    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }

    public function getDeletedAt(): ?string
    {
        return $this->deletedAt;
    }
}

class Document
{
    use AuditTrait, SoftDeleteTrait;

    public function __construct(
        private string $title
    ) {
        $this->audit("Document created");
    }

    public function update(string $title): void
    {
        $this->title = $title;
        $this->audit("Document updated");
    }

    public function delete(): void
    {
        $this->softDelete();
        $this->audit("Document deleted");
    }
}

// Usage
$doc = new Document("My Document");
$doc->update("Updated Title");
$doc->delete();

print_r($doc->getAuditLog());
echo "Deleted: " . ($doc->isDeleted() ? 'Yes' : 'No') . "\n";
```

---

## Section 6: Interfaces + Traits

### Goal

Combine interfaces and traits for maximum flexibility.

### Powerful Combination

```php
<?php

declare(strict_types=1);

// Interface defines the contract
interface CacheInterface
{
    public function get(string $key): mixed;
    public function set(string $key, mixed $value, int $ttl = 3600): void;
    public function delete(string $key): void;
    public function clear(): void;
}

// Trait provides common implementation
trait CacheTrait
{
    protected array $cache = [];
    protected array $expiry = [];

    public function get(string $key): mixed
    {
        if ($this->isExpired($key)) {
            unset($this->cache[$key], $this->expiry[$key]);
            return null;
        }

        return $this->cache[$key] ?? null;
    }

    public function set(string $key, mixed $value, int $ttl = 3600): void
    {
        $this->cache[$key] = $value;
        $this->expiry[$key] = time() + $ttl;
    }

    public function delete(string $key): void
    {
        unset($this->cache[$key], $this->expiry[$key]);
    }

    public function clear(): void
    {
        $this->cache = [];
        $this->expiry = [];
    }

    protected function isExpired(string $key): bool
    {
        if (!isset($this->expiry[$key])) {
            return false;
        }

        return time() > $this->expiry[$key];
    }
}

// Array-based cache
class ArrayCache implements CacheInterface
{
    use CacheTrait;  // Reuse implementation
}

// File-based cache (override some methods)
class FileCache implements CacheInterface
{
    use CacheTrait {
        set as protected setInMemory;
    }

    public function __construct(
        private string $cacheDir
    ) {}

    // Override to persist to file
    public function set(string $key, mixed $value, int $ttl = 3600): void
    {
        $this->setInMemory($key, $value, $ttl);

        $filePath = $this->cacheDir . '/' . md5($key);
        file_put_contents($filePath, serialize([
            'value' => $value,
            'expiry' => time() + $ttl
        ]));
    }
}

// Redis-based cache (different implementation)
class RedisCache implements CacheInterface
{
    // Doesn't use CacheTrait - has its own implementation
    public function __construct(
        private object $redis
    ) {}

    public function get(string $key): mixed
    {
        return $this->redis->get($key);
    }

    public function set(string $key, mixed $value, int $ttl = 3600): void
    {
        $this->redis->setex($key, $ttl, $value);
    }

    public function delete(string $key): void
    {
        $this->redis->del($key);
    }

    public function clear(): void
    {
        $this->redis->flushAll();
    }
}

// Function accepts interface
function cacheUser(CacheInterface $cache, int $userId, array $userData): void
{
    $cache->set("user:$userId", $userData, 3600);
}

// Works with any cache implementation
$arrayCache = new ArrayCache();
$fileCache = new FileCache('/tmp/cache');

cacheUser($arrayCache, 1, ['name' => 'Alice']);
cacheUser($fileCache, 2, ['name' => 'Bob']);
```

::: tip Best Practice Pattern
**Interface + Trait pattern:**
1. **Interface** defines the contract (what)
2. **Trait** provides default implementation (how)
3. **Classes** can use trait for quick implementation or override for custom behavior
4. **Polymorphism** works through interface type hints

This is extremely powerful for library and framework development!
:::

---

## Section 7: Practical Example - Event System

### Goal

Build a comprehensive event system using interfaces and traits.

```php
<?php

declare(strict_types=1);

// Interfaces
interface EventInterface
{
    public function getName(): string;
    public function getData(): array;
    public function isPropagationStopped(): bool;
    public function stopPropagation(): void;
}

interface EventListenerInterface
{
    public function handle(EventInterface $event): void;
}

interface EventDispatcherInterface
{
    public function addEventListener(string $eventName, EventListenerInterface $listener): void;
    public function dispatch(EventInterface $event): void;
}

// Traits
trait EventTrait
{
    private bool $propagationStopped = false;

    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }

    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }
}

// Events
class UserRegisteredEvent implements EventInterface
{
    use EventTrait;

    public function __construct(
        private array $userData
    ) {}

    public function getName(): string
    {
        return 'user.registered';
    }

    public function getData(): array
    {
        return $this->userData;
    }

    public function getUser(): array
    {
        return $this->userData;
    }
}

class OrderPlacedEvent implements EventInterface
{
    use EventTrait;

    public function __construct(
        private array $orderData
    ) {}

    public function getName(): string
    {
        return 'order.placed';
    }

    public function getData(): array
    {
        return $this->orderData;
    }

    public function getOrder(): array
    {
        return $this->orderData;
    }
}

// Listeners
class SendWelcomeEmailListener implements EventListenerInterface
{
    public function handle(EventInterface $event): void
    {
        if ($event instanceof UserRegisteredEvent) {
            $user = $event->getUser();
            echo "Sending welcome email to {$user['email']}\n";
        }
    }
}

class CreateUserProfileListener implements EventListenerInterface
{
    public function handle(EventInterface $event): void
    {
        if ($event instanceof UserRegisteredEvent) {
            $user = $event->getUser();
            echo "Creating profile for {$user['name']}\n";
        }
    }
}

class SendOrderConfirmationListener implements EventListenerInterface
{
    public function handle(EventInterface $event): void
    {
        if ($event instanceof OrderPlacedEvent) {
            $order = $event->getOrder();
            echo "Sending order confirmation for order #{$order['id']}\n";
        }
    }
}

class UpdateInventoryListener implements EventListenerInterface
{
    public function handle(EventInterface $event): void
    {
        if ($event instanceof OrderPlacedEvent) {
            $order = $event->getOrder();
            echo "Updating inventory for order #{$order['id']}\n";
            // Could stop propagation if inventory update fails
            // $event->stopPropagation();
        }
    }
}

// Event Dispatcher
class EventDispatcher implements EventDispatcherInterface
{
    /** @var array<string, EventListenerInterface[]> */
    private array $listeners = [];

    public function addEventListener(string $eventName, EventListenerInterface $listener): void
    {
        $this->listeners[$eventName][] = $listener;
    }

    public function dispatch(EventInterface $event): void
    {
        $eventName = $event->getName();

        if (!isset($this->listeners[$eventName])) {
            return;
        }

        foreach ($this->listeners[$eventName] as $listener) {
            if ($event->isPropagationStopped()) {
                break;
            }

            $listener->handle($event);
        }
    }
}

// Usage
$dispatcher = new EventDispatcher();

// Register listeners for user registration
$dispatcher->addEventListener('user.registered', new SendWelcomeEmailListener());
$dispatcher->addEventListener('user.registered', new CreateUserProfileListener());

// Register listeners for order placement
$dispatcher->addEventListener('order.placed', new SendOrderConfirmationListener());
$dispatcher->addEventListener('order.placed', new UpdateInventoryListener());

// Dispatch events
echo "=== User Registration ===\n";
$userEvent = new UserRegisteredEvent([
    'name' => 'Alice Johnson',
    'email' => 'alice@example.com'
]);
$dispatcher->dispatch($userEvent);

echo "\n=== Order Placement ===\n";
$orderEvent = new OrderPlacedEvent([
    'id' => 12345,
    'total' => 99.99,
    'items' => ['Product A', 'Product B']
]);
$dispatcher->dispatch($orderEvent);
```

---

## Exercises

### Exercise 1: Payment Gateway

Create a payment gateway system using interfaces and traits.

**Requirements:**
- `PaymentGatewayInterface` with `charge()` and `refund()` methods
- `LoggableTrait` for logging transactions
- Multiple gateway implementations (Stripe, PayPal, Square)

<details>
<summary>Solution in Chapter 5 Code Examples</summary>

Check `/code/php-for-java-developers/chapter-05/PaymentGateway.php` for the complete solution.

</details>

### Exercise 2: Serializable Objects

Create a system for serializing objects to different formats.

**Requirements:**
- `SerializableInterface` with `toArray()` method
- `JsonSerializableTrait` and `XmlSerializableTrait`
- Classes using both traits

<details>
<summary>Solution in Chapter 5 Code Examples</summary>

Check `/code/php-for-java-developers/chapter-05/Serializable.php` for the complete solution.

</details>

---

## Wrap-up Checklist

Before moving to the next chapter, ensure you can:

- [ ] Define and implement interfaces in PHP
- [ ] Use multiple interfaces in a single class
- [ ] Create traits for horizontal code reuse
- [ ] Use multiple traits in a class
- [ ] Resolve trait method conflicts with `insteadof` and `as`
- [ ] Compose traits (traits using traits)
- [ ] Combine interfaces and traits effectively
- [ ] Choose between abstract classes, interfaces, and traits

::: tip Ready for More?
In [Chapter 6: Namespaces & Autoloading](/series/php-for-java-developers/chapters/06-namespaces-and-autoloading), we'll explore PHP's namespace system (similar to Java packages) and autoloading (similar to Java's classpath).
:::

---

## Further Reading

**PHP Documentation:**
- [Interfaces](https://www.php.net/manual/en/language.oop5.interfaces.php)
- [Traits](https://www.php.net/manual/en/language.oop5.traits.php)
- [Object Interfaces](https://www.php.net/manual/en/language.oop5.interfaces.php)

---

<div style="display: flex; justify-content: space-between; margin-top: 2rem;">
  <div>
    <strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/04-classes-and-inheritance">← Chapter 4: Classes & Inheritance</a>
  </div>
  <div>
    <strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/06-namespaces-and-autoloading">Chapter 6: Namespaces & Autoloading →</a>
  </div>
</div>
