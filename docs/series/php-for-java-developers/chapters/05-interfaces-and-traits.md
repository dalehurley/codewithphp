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

### Interface Segregation Principle (ISP)

The Interface Segregation Principle states: **"No client should be forced to depend on methods it does not use."**

In other words: **Make interfaces small and focused**.

```php
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

### Trait Constants (PHP 8.2+)

PHP 8.2 introduced constants in traits:

```php
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

// Hydrat able - Fill object from array
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
