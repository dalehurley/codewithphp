---
title: "05: Interfaces & Traits"
description: "Master PHP interfaces and traits for flexible code reuse with Java comparisons"
sidebar:
  label: "05: Interfaces & Traits"
  order: 5
  badge:
    text: Intermediate
    variant: caution
---

![Interfaces and Traits Hero](/images/php-for-java-developers/chapter-05-interfaces-traits-hero-full.webp)

# Chapter 05: Interfaces & Traits

<Badge type="warning">Intermediate</Badge> <Badge type="info">90-120 min</Badge>

## Overview

PHP interfaces work almost identically to Java interfaces - they define contracts that classes must implement. However, PHP has a unique feature called **traits** that provides horizontal code reuse without inheritance. Think of traits as a way to copy-paste methods into classes, solving the "diamond problem" and allowing for flexible composition.

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

- **Payment gateway interfaces** with multiple implementations (Stripe, PayPal, Square)
- **Reusable traits** for logging, caching, validation, and timestamps
- **A cache system** combining interfaces and traits with multiple storage backends
- **An event system** demonstrating interfaces and traits working together
- **Practical trait examples** including Sluggable, Uuidable, and Hydratable

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

| Rule                      | PHP                              | Java                              |
| ------------------------- | -------------------------------- | --------------------------------- |
| **Method implementation** | No implementation (before PHP 8) | No implementation (before Java 8) |
| **Default methods**       | ✅ Yes (PHP 8.0+)                | ✅ Yes (Java 8+)                  |
| **Multiple interfaces**   | ✅ Yes                           | ✅ Yes                            |
| **Properties/fields**     | ❌ No                            | ❌ No (only constants)            |
| **Constants**             | ✅ Yes                           | ✅ Yes                            |
| **Visibility**            | All public                       | All public                        |
| **Extend interfaces**     | ✅ Yes                           | ✅ Yes                            |

### Interface Default Methods (PHP 8.0+)

PHP 8.0 introduced default method implementations in interfaces, similar to Java 8+. This allows interfaces to provide default behavior that implementing classes can use or override.

```php
# filename: interface-default-methods.php
<?php

declare(strict_types=1);

interface LoggerInterface
{
    // Abstract method - must be implemented
    public function log(string $message): void;

    // Default method - optional to override
    public function logError(string $message): void
    {
        $this->log("[ERROR] " . $message);
    }

    // Another default method
    public function logWarning(string $message): void
    {
        $this->log("[WARNING] " . $message);
    }
}

// Simple implementation - uses default methods
class SimpleLogger implements LoggerInterface
{
    private array $logs = [];

    public function log(string $message): void
    {
        $this->logs[] = $message;
    }

    // logError() and logWarning() use default implementations
}

// Advanced implementation - overrides default methods
class AdvancedLogger implements LoggerInterface
{
    private array $logs = [];

    public function log(string $message): void
    {
        $this->logs[] = date('Y-m-d H:i:s') . ' - ' . $message;
    }

    // Override default implementation
    public function logError(string $message): void
    {
        $this->log("[CRITICAL ERROR] " . $message);
        // Could also send to error tracking service
    }
}

// Usage
$simple = new SimpleLogger();
$simple->log("Info message");
$simple->logError("Something went wrong");  // Uses default implementation
$simple->logWarning("Be careful");          // Uses default implementation

$advanced = new AdvancedLogger();
$advanced->log("Info message");
$advanced->logError("Something went wrong");  // Uses overridden implementation
```

::: tip When to Use Default Methods
**Use default methods when:**

- You want to provide common behavior that most implementations will use
- You need to add new methods to existing interfaces without breaking implementations
- You want to reduce code duplication across interface implementations

**Don't use default methods when:**

- The behavior requires access to class-specific state that interfaces can't have
- You need complex logic that belongs in traits or abstract classes
- The default implementation would be too generic to be useful
  :::

### Interface Constants

```php
# filename: interface-constants.php
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
# filename: extending-interfaces.php
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

### Interface Segregation Principle (ISP)

The Interface Segregation Principle states: **"No client should be forced to depend on methods it does not use."**

In other words: **Make interfaces small and focused**.

```php
# filename: interface-segregation.php
<?php

declare(strict_types=1);

// ❌ BAD: Fat interface - forces implementation of unused methods
interface Worker
{
    public function work(): void;
    public function eat(): void;
    public function sleep(): void;
    public function getSalary(): float;
    public function takeVacation(int $days): void;
}

class Employee implements Worker
{
    public function work(): void {
        echo "Working...\n";
    }

    public function eat(): void {
        echo "Eating lunch\n";
    }

    public function sleep(): void {
        echo "Sleeping\n";
    }

    public function getSalary(): float {
        return 50000.0;
    }

    public function takeVacation(int $days): void {
        echo "Taking $days days vacation\n";
    }
}

class Robot implements Worker
{
    public function work(): void {
        echo "Robot working...\n";
    }

    // ❌ Robots don't eat, sleep, or take vacation!
    public function eat(): void {
        // Meaningless for robots
    }

    public function sleep(): void {
        // Meaningless for robots
    }

    public function getSalary(): float {
        return 0.0;  // Robots don't get paid
    }

    public function takeVacation(int $days): void {
        // Meaningless for robots
    }
}

// ✅ GOOD: Segregated interfaces
interface Workable
{
    public function work(): void;
}

interface Eatable
{
    public function eat(): void;
}

interface Sleepable
{
    public function sleep(): void;
}

interface Payable
{
    public function getSalary(): float;
}

interface Vacationable
{
    public function takeVacation(int $days): void;
}

// Human implements all biological and employment interfaces
class Human implements Workable, Eatable, Sleepable, Payable, Vacationable
{
    public function work(): void {
        echo "Human working...\n";
    }

    public function eat(): void {
        echo "Human eating...\n";
    }

    public function sleep(): void {
        echo "Human sleeping...\n";
    }

    public function getSalary(): float {
        return 50000.0;
    }

    public function takeVacation(int $days): void {
        echo "Taking $days days vacation\n";
    }
}

// Robot only implements what it needs
class AutomatedRobot implements Workable
{
    public function work(): void {
        echo "Robot working 24/7...\n";
    }
}

// Contractor might not get vacation
class Contractor implements Workable, Payable
{
    public function work(): void {
        echo "Contractor working...\n";
    }

    public function getSalary(): float {
        return 75000.0;
    }
}

// Functions can require only what they need
function performWork(Workable $worker): void
{
    $worker->work();
}

function processPayroll(Payable $employee): void
{
    echo "Processing payment: $" . $employee->getSalary() . "\n";
}

// All types can work
performWork(new Human());
performWork(new AutomatedRobot());
performWork(new Contractor());

// Only payable types get paid
processPayroll(new Human());
processPayroll(new Contractor());
// processPayroll(new AutomatedRobot());  // Won't compile - robot isn't Payable
```

::: tip ISP Benefits
**Benefits of Interface Segregation:**

1. **Flexibility**: Classes implement only what they need
2. **Easier maintenance**: Small interfaces are easier to understand
3. **Better composition**: Mix and match interfaces as needed
4. **Reduced coupling**: Clients depend on minimal interfaces
5. **Clearer intent**: Interface names describe specific capabilities

**Guidelines:**

- Keep interfaces small and focused
- Name interfaces by capability (Readable, Writable, Closeable)
- Prefer many specific interfaces over one general interface
- Clients should depend on the smallest interface possible
  :::

---

## Section 2: Introduction to Traits

### Goal

Understand PHP traits and how they differ from Java concepts.

### What Are Traits?

Traits are PHP's mechanism for horizontal code reuse. They're like "copy-paste on steroids" - methods defined in a trait are copied into classes that use them.

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

| Feature                | PHP Trait               | Java Interface (8+)    | Java Composition  |
| ---------------------- | ----------------------- | ---------------------- | ----------------- |
| **Code reuse**         | Horizontal (copy-paste) | Vertical (inheritance) | Delegation        |
| **State (properties)** | ✅ Yes                  | ❌ No                  | ✅ Yes            |
| **Multiple use**       | ✅ Yes                  | ✅ Yes                 | ✅ Yes            |
| **Method conflicts**   | Manual resolution       | No conflicts           | Manual delegation |
| **Instance of**        | No                      | Yes                    | No                |

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
# filename: multiple-traits.php
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
# filename: trait-conflicts.php
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
# filename: trait-visibility.php
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

Understand trait composition - traits using other traits.

### Nested Traits

```php
# filename: nested-traits.php
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

### Trait Constants (PHP 8.2+)

PHP 8.2 introduced constants in traits:

```php
# filename: trait-constants.php
<?php

declare(strict_types=1);

trait MathOperations
{
    // PHP 8.2+: Constants in traits
    public const PI = 3.14159;
    public const E = 2.71828;
    private const MAX_ITERATIONS = 1000;

    public function circleArea(float $radius): float
    {
        return self::PI * $radius ** 2;
    }

    public function exponential(float $x): float
    {
        // Use private constant
        $result = 1;
        for ($i = 0; $i < self::MAX_ITERATIONS && $i < 10; $i++) {
            $result += pow($x, $i) / $this->factorial($i);
        }
        return $result;
    }

    private function factorial(int $n): int
    {
        return $n <= 1 ? 1 : $n * $this->factorial($n - 1);
    }
}

class Calculator
{
    use MathOperations;
}

$calc = new Calculator();
echo Calculator::PI;  // Access constant through class
echo $calc->circleArea(5);
```

### Practical Trait Examples

```php
# filename: practical-traits.php
<?php

declare(strict_types=1);

// Sluggable - Convert titles to URL-friendly slugs
trait Sluggable
{
    protected ?string $slug = null;

    public function generateSlug(string $text): string
    {
        // Convert to lowercase
        $slug = strtolower($text);

        // Replace non-alphanumeric with hyphens
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        // Remove leading/trailing hyphens
        $slug = trim($slug, '-');

        $this->slug = $slug;
        return $slug;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }
}

// Sortable - Add sorting capabilities
trait Sortable
{
    protected int $sortOrder = 0;

    public function setSortOrder(int $order): void
    {
        $this->sortOrder = $order;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public static function sortByOrder(self $a, self $b): int
    {
        return $a->sortOrder <=> $b->sortOrder;
    }
}

// Uuidable - Add UUID support
trait Uuidable
{
    protected ?string $uuid = null;

    protected function generateUuid(): string
    {
        // Simple UUID v4 generation (use ramsey/uuid in production)
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Version 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Variant

        $this->uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        return $this->uuid;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }
}

// Hydratable - Fill object from array
trait Hydratable
{
    public function hydrate(array $data): self
    {
        foreach ($data as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));

            if (method_exists($this, $method)) {
                $this->$method($value);
            } elseif (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        return $this;
    }

    public function toArray(): array
    {
        $data = [];

        foreach (get_object_vars($this) as $key => $value) {
            // Skip private/protected traits properties if needed
            $data[$key] = $value;
        }

        return $data;
    }
}

// Using multiple practical traits
class BlogPost
{
    use Sluggable, Sortable, Uuidable, Hydratable;

    public function __construct(
        private string $title = '',
        private string $content = ''
    ) {
        $this->generateUuid();
        if ($title) {
            $this->generateSlug($title);
        }
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
        $this->generateSlug($title);
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}

// Usage
$post = new BlogPost("Hello World! This is a Test", "Content here");
echo "UUID: " . $post->getUuid() . "\n";
echo "Slug: " . $post->getSlug() . "\n";  // "hello-world-this-is-a-test"

// Hydration
$post2 = (new BlogPost())->hydrate([
    'title' => 'Another Post',
    'content' => 'More content',
    'sort_order' => 10
]);

echo "Title: " . $post2->getTitle() . "\n";
echo "Sort Order: " . $post2->getSortOrder() . "\n";

// Sorting
$posts = [$post, $post2];
$post->setSortOrder(5);
$post2->setSortOrder(1);

usort($posts, [BlogPost::class, 'sortByOrder']);
echo "First post: " . $posts[0]->getTitle() . "\n";  // Another Post (order: 1)
```

### Abstract Methods in Traits

Traits can declare abstract methods that implementing classes must define:

```php
# filename: abstract-trait-methods.php
<?php

declare(strict_types=1);

trait Repository
{
    protected array $items = [];

    // Force implementing class to provide table name
    abstract protected function getTableName(): string;

    // Force implementing class to provide primary key
    abstract protected function getPrimaryKey(): string;

    public function find(int $id): ?array
    {
        $table = $this->getTableName();
        $pk = $this->getPrimaryKey();

        echo "SELECT * FROM {$table} WHERE {$pk} = {$id}\n";

        return $this->items[$id] ?? null;
    }

    public function all(): array
    {
        $table = $this->getTableName();
        echo "SELECT * FROM {$table}\n";

        return $this->items;
    }

    public function save(array $data): void
    {
        $table = $this->getTableName();
        echo "INSERT INTO {$table} ...\n";

        $this->items[] = $data;
    }
}

class UserRepository
{
    use Repository;

    // Must implement abstract methods from trait
    protected function getTableName(): string
    {
        return 'users';
    }

    protected function getPrimaryKey(): string
    {
        return 'id';
    }
}

class ProductRepository
{
    use Repository;

    protected function getTableName(): string
    {
        return 'products';
    }

    protected function getPrimaryKey(): string
    {
        return 'product_id';  // Different primary key
    }
}

$userRepo = new UserRepository();
$userRepo->find(1);  // SELECT * FROM users WHERE id = 1

$productRepo = new ProductRepository();
$productRepo->find(5);  // SELECT * FROM products WHERE product_id = 5
```

### Trait Precedence Order

When methods are defined in multiple places, PHP follows a specific precedence order:

1. **Class methods** (highest priority)
2. **Trait methods**
3. **Parent class methods** (lowest priority)

```php
# filename: trait-precedence.php
<?php

declare(strict_types=1);

class ParentClass
{
    public function greet(): string
    {
        return "Hello from Parent";
    }
}

trait MyTrait
{
    public function greet(): string
    {
        return "Hello from Trait";
    }
}

class ChildClass extends ParentClass
{
    use MyTrait;

    // If this method exists, it takes precedence over trait and parent
    // public function greet(): string
    // {
    //     return "Hello from Child";
    // }
}

$obj = new ChildClass();
echo $obj->greet();  // "Hello from Trait" (trait overrides parent)

// If ChildClass defines greet(), it would output "Hello from Child"
```

::: tip Trait Precedence Rules
**Priority order:**

1. Class methods always win
2. Trait methods override parent class methods
3. Last trait used wins if multiple traits define the same method (unless resolved with `insteadof`)

**Best practice:** Be explicit about conflicts using `insteadof` rather than relying on trait order.
:::

::: tip Practical Traits Tips
**Common useful traits:**

- **Sluggable**: URL-friendly slugs from titles
- **Timestampable**: created_at, updated_at tracking
- **Sortable**: Ordering capabilities
- **Uuidable**: UUID generation
- **Hydratable**: Fill from arrays
- **Loggable**: Activity logging
- **Cacheable**: Simple caching layer
- **Validatable**: Validation rules

**Best practices:**

- Keep traits focused (single responsibility)
- Document required properties/methods
- Use abstract methods to enforce requirements
- Prefix trait-specific properties to avoid conflicts
- Consider composition when traits get complex
  :::

---

## Section 6: Interfaces + Traits

### Goal

Combine interfaces and traits for maximum flexibility.

### Powerful Combination

```php
# filename: interface-trait-combination.php
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

### Choosing Between Abstract Classes, Interfaces, and Traits

Knowing when to use each is crucial for good design. Here's a decision guide:

| Feature                             | Abstract Class                 | Interface                    | Trait                          |
| ----------------------------------- | ------------------------------ | ---------------------------- | ------------------------------ |
| **Can be instantiated**             | ❌ No                          | ❌ No                        | ❌ No                          |
| **Can have properties**             | ✅ Yes                         | ❌ No (constants only)       | ✅ Yes                         |
| **Can have method implementations** | ✅ Yes                         | ✅ Yes (PHP 8.0+)            | ✅ Yes                         |
| **Multiple inheritance**            | ❌ No (single parent)          | ✅ Yes (multiple interfaces) | ✅ Yes (multiple traits)       |
| **Use for "is-a" relationship**     | ✅ Yes                         | ✅ Yes                       | ❌ No                          |
| **Use for "has-a" capability**      | ❌ No                          | ✅ Yes                       | ✅ Yes                         |
| **State sharing**                   | ✅ Yes (via inheritance)       | ❌ No                        | ✅ Yes (via composition)       |
| **Constructor**                     | ✅ Yes                         | ❌ No                        | ❌ No                          |
| **Access modifiers**                | All (public/private/protected) | Public only                  | All (public/private/protected) |

**Decision Tree:**

1. **Need to share state (properties) across unrelated classes?**

   - ✅ **Use Trait** - Traits can share properties and methods

2. **Defining a contract that multiple unrelated classes must follow?**

   - ✅ **Use Interface** - Best for polymorphism and type hints

3. **Need a base class with shared implementation for related classes?**

   - ✅ **Use Abstract Class** - Perfect for class hierarchies

4. **Want to add capabilities to existing classes without inheritance?**

   - ✅ **Use Trait** - Horizontal code reuse

5. **Need to enforce a contract AND provide default implementation?**
   - ✅ **Use Interface with default methods** (PHP 8.0+) or **Interface + Trait**

**Examples:**

```php
# filename: choosing-right-tool.php
<?php

declare(strict_types=1);

// ✅ Use Abstract Class: Shape hierarchy with shared implementation
abstract class Shape
{
    protected string $color;

    public function __construct(string $color)
    {
        $this->color = $color;
    }

    public function getColor(): string
    {
        return $this->color;  // Shared implementation
    }

    abstract public function getArea(): float;  // Must be implemented
}

// ✅ Use Interface: Contract for unrelated classes
interface Loggable
{
    public function log(string $message): void;
}

class User implements Loggable { /* ... */ }
class Order implements Loggable { /* ... */ }
class Product implements Loggable { /* ... */ }

// ✅ Use Trait: Add capability to multiple unrelated classes
trait Timestampable
{
    protected ?string $createdAt = null;

    public function setCreatedAt(): void
    {
        $this->createdAt = date('Y-m-d H:i:s');
    }
}

class Article { use Timestampable; }
class Comment { use Timestampable; }
class Post { use Timestampable; }
```

::: tip Quick Reference

- **Abstract Class**: "A User IS A Person" (inheritance, shared state)
- **Interface**: "A User CAN BE Loggable" (contract, polymorphism)
- **Trait**: "A User HAS timestamp capabilities" (horizontal reuse, composition)
  :::

---

## Section 7: Practical Example - Event System

### Goal

Build a comprehensive event system using interfaces and traits.

```php
# filename: event-system.php
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

- Define `PaymentGatewayInterface` with `charge(float $amount): bool` and `refund(string $transactionId): bool` methods
- Create `LoggableTrait` that logs all transactions with timestamps
- Implement three gateway classes: `StripeGateway`, `PayPalGateway`, and `SquareGateway`
- Each gateway should use the `LoggableTrait` and implement the interface
- Each gateway should return different transaction IDs (e.g., "stripe_123", "paypal_456")

**Validation**: Test your implementation:

```php
# filename: test-payment-gateway.php
<?php

declare(strict_types=1);

$stripe = new StripeGateway();
$paypal = new PayPalGateway();

// Test charging
$stripe->charge(100.00);  // Should log transaction
$paypal->charge(50.00);   // Should log transaction

// Test refunding
$stripe->refund("stripe_123");
$paypal->refund("paypal_456");

// Verify logs exist
print_r($stripe->getLogs());
print_r($paypal->getLogs());
```

Expected output should show logged transactions with timestamps for both charge and refund operations.

<details>
<summary>Solution in Chapter 5 Code Examples</summary>

Check `/code/php-for-java-developers/chapter-05/PaymentGateway.php` for the complete solution.

</details>

### Exercise 2: Serializable Objects

Create a system for serializing objects to different formats.

**Requirements:**

- Define `SerializableInterface` with `toArray(): array` method
- Create `JsonSerializableTrait` with `toJson(): string` method that uses `json_encode($this->toArray())`
- Create `XmlSerializableTrait` with `toXml(): string` method that converts array to simple XML format
- Create a `Product` class that implements `SerializableInterface` and uses both traits
- The `Product` class should have properties: `name`, `price`, `category`

**Validation**: Test your implementation:

```php
# filename: test-serializable.php
<?php

declare(strict_types=1);

$product = new Product("Laptop", 999.99, "Electronics");

// Test toArray
$array = $product->toArray();
// Expected: ['name' => 'Laptop', 'price' => 999.99, 'category' => 'Electronics']

// Test JSON serialization
$json = $product->toJson();
// Expected: Valid JSON string

// Test XML serialization
$xml = $product->toXml();
// Expected: Valid XML string with product data

echo $json . "\n";
echo $xml . "\n";
```

Expected output should show valid JSON and XML representations of the product data.

<details>
<summary>Solution in Chapter 5 Code Examples</summary>

Check `/code/php-for-java-developers/chapter-05/Serializable.php` for the complete solution.

</details>

---

## Troubleshooting

### Error: "Fatal error: Trait method X has not been applied"

**Symptom**: `Fatal error: Trait method log has not been applied, because there are collisions with other trait methods`

**Cause**: Two or more traits define methods with the same name, and PHP doesn't know which one to use.

**Solution**: Resolve the conflict using `insteadof` or `as`:

```php
# filename: resolve-trait-conflict.php
class MyClass
{
    use TraitA, TraitB {
        TraitA::log insteadof TraitB;  // Use TraitA's log method
        TraitB::log as logToB;          // Keep TraitB's log as alias
    }
}
```

### Error: "Class X cannot implement interface Y"

**Symptom**: `Fatal error: Class MyClass contains 1 abstract method and must therefore be declared abstract or implement the remaining methods`

**Cause**: The class implements an interface but doesn't provide implementations for all required methods.

**Solution**: Implement all methods defined in the interface:

```php
# filename: implement-interface.php
interface MyInterface
{
    public function requiredMethod(): void;
}

class MyClass implements MyInterface
{
    // Must implement requiredMethod
    public function requiredMethod(): void
    {
        // Implementation here
    }
}
```

### Problem: Trait properties conflict with class properties

**Symptom**: Unexpected behavior when trait properties have the same name as class properties.

**Cause**: Trait properties are copied into the class, potentially overwriting or conflicting with existing properties.

**Solution**: Use different property names in traits, or prefix trait properties:

```php
# filename: avoid-property-conflict.php
trait MyTrait
{
    protected ?string $traitCreatedAt = null;  // Prefix to avoid conflicts
}

class MyClass
{
    use MyTrait;

    protected ?string $createdAt = null;  // Different name
}
```

### Problem: Abstract method in trait not implemented

**Symptom**: `Fatal error: Class MyClass contains 1 abstract method (MyTrait::abstractMethod)`

**Cause**: A trait defines an abstract method, but the class using the trait doesn't implement it.

**Solution**: Implement the abstract method in the class:

```php
# filename: implement-abstract-trait.php
trait MyTrait
{
    abstract protected function getTableName(): string;
}

class MyClass
{
    use MyTrait;

    // Must implement abstract method
    protected function getTableName(): string
    {
        return 'my_table';
    }
}
```

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

**Related Chapters:**

- [Chapter 4: Classes & Inheritance](/series/php-for-java-developers/chapters/04-classes-and-inheritance) - Abstract classes and inheritance
- [Chapter 6: Namespaces & Autoloading](/series/php-for-java-developers/chapters/06-namespaces-and-autoloading) - Organizing interfaces and traits

---

<div style="display: flex; justify-content: space-between; margin-top: 2rem;">
  <div>
    <strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/04-classes-and-inheritance">← Chapter 4: Classes & Inheritance</a>
  </div>
  <div>
    <strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/06-namespaces-and-autoloading">Chapter 6: Namespaces & Autoloading →</a>
  </div>
</div>
