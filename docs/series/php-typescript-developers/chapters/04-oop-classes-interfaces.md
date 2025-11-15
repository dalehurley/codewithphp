---
title: "04: OOP - Classes, Interfaces & Generics"
description: "Master PHP's object-oriented programming by leveraging your TypeScript knowledge. Learn classes, interfaces, abstract classes, traits, and generic type annotations."
series: "php-typescript-developers"
chapter: 4
order: 4
difficulty: "Intermediate"
prerequisites:
  - "/series/php-typescript-developers/chapters/03-functions-and-closures"
---

# OOP: Classes, Interfaces & Generics

## Overview

Both TypeScript and PHP support object-oriented programming, but they take different approaches. TypeScript uses **structural typing** (duck typing), while PHP uses **nominal typing** (name-based). This chapter bridges these concepts and shows you how to write idiomatic PHP classes.

## Learning Objectives

By the end of this chapter, you'll be able to:

- ✅ Write PHP classes with proper visibility modifiers
- ✅ Use constructor property promotion (PHP 8.0+)
- ✅ Understand nominal vs structural typing
- ✅ Create and implement interfaces
- ✅ Use abstract classes and methods
- ✅ Apply traits (PHP's mixin alternative)
- ✅ Add generic type annotations with PHPStan/Psalm
- ✅ Use readonly and static properties

## Code Examples

📁 **[View Code Examples on GitHub](https://github.com/dalehurley/codewithphp/tree/main/code/php-typescript-developers/chapter-04)**

This chapter includes OOP examples:
- Classes with property promotion
- Interfaces and abstract classes
- Traits for code reuse
- Magic methods

Run the examples:
```bash
cd code/php-typescript-developers/chapter-04
php classes.php
php traits.php
```

## Class Basics

### TypeScript

```typescript
class User {
  // Properties
  private id: number;
  public name: string;
  protected email: string;

  constructor(id: number, name: string, email: string) {
    this.id = id;
    this.name = name;
    this.email = email;
  }

  // Method
  public greet(): string {
    return `Hello, ${this.name}!`;
  }

  // Getter
  get displayName(): string {
    return this.name.toUpperCase();
  }
}

const user = new User(1, "Alice", "alice@example.com");
console.log(user.greet());
```

### PHP

```php
<?php
declare(strict_types=1);

class User {
    // Properties
    private int $id;
    public string $name;
    protected string $email;

    public function __construct(int $id, string $name, string $email) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
    }

    // Method
    public function greet(): string {
        return "Hello, {$this->name}!";
    }

    // Getter (no special syntax, just a method)
    public function getDisplayName(): string {
        return strtoupper($this->name);
    }
}

$user = new User(1, "Alice", "alice@example.com");
echo $user->greet();
```

**Key Differences:**
- PHP uses `$this->` instead of `this.`
- PHP uses `->` for property/method access
- No special getter syntax in PHP (use methods)
- Visibility keywords come before type hints

## Constructor Property Promotion (PHP 8.0+)

### TypeScript

```typescript
class User {
  constructor(
    public id: number,
    public name: string,
    private email: string
  ) {}
}
```

### PHP (8.0+)

```php
<?php
declare(strict_types=1);

class User {
    public function __construct(
        public int $id,
        public string $name,
        private string $email
    ) {}
}
```

**Identical concept!** Properties are declared and assigned in one step.

## Readonly Properties

### TypeScript

```typescript
class User {
  readonly id: number;
  name: string;

  constructor(id: number, name: string) {
    this.id = id;
    this.name = name;
  }
}

const user = new User(1, "Alice");
user.name = "Bob"; // ✅ OK
user.id = 2;       // ❌ Error: Cannot assign to 'id' because it is a read-only property
```

### PHP (8.1+)

```php
<?php
declare(strict_types=1);

class User {
    public function __construct(
        public readonly int $id,
        public string $name
    ) {}
}

$user = new User(1, "Alice");
$user->name = "Bob"; // ✅ OK
$user->id = 2;       // ❌ Fatal error: Cannot modify readonly property
```

## Static Properties and Methods

### TypeScript

```typescript
class Counter {
  private static count: number = 0;

  public static increment(): void {
    Counter.count++;
  }

  public static getCount(): number {
    return Counter.count;
  }
}

Counter.increment();
console.log(Counter.getCount()); // 1
```

### PHP

```php
<?php
declare(strict_types=1);

class Counter {
    private static int $count = 0;

    public static function increment(): void {
        self::$count++;
    }

    public static function getCount(): int {
        return self::$count;
    }
}

Counter::increment();
echo Counter::getCount(); // 1
```

**Key Differences:**
- PHP uses `self::` instead of `ClassName::` inside the class
- PHP uses `::` (scope resolution operator) instead of `.`
- Static properties need `$` prefix: `self::$count`

## Interfaces

### TypeScript (Structural Typing)

```typescript
// Interface describes object shape
interface Nameable {
  name: string;
  getName(): string;
}

// Any object with matching shape works
class User implements Nameable {
  constructor(public name: string) {}

  getName(): string {
    return this.name;
  }
}

// Duck typing: no 'implements' needed if shape matches
const obj = {
  name: "Alice",
  getName() { return this.name; }
};

function greet(item: Nameable): string {
  return `Hello, ${item.getName()}!`;
}

greet(obj); // ✅ Works (structural typing)
```

### PHP (Nominal Typing)

```php
<?php
declare(strict_types=1);

// Interface defines method contracts (no properties allowed)
interface Nameable {
    public function getName(): string;
}

// Must explicitly implement interface
class User implements Nameable {
    public function __construct(
        private string $name
    ) {}

    public function getName(): string {
        return $this->name;
    }
}

// ❌ Plain object won't work without 'implements'
// PHP uses nominal typing: must explicitly implement interface

function greet(Nameable $item): string {
    return "Hello, {$item->getName()}!";
}

$user = new User("Alice");
greet($user); // ✅ Works
```

**Critical Difference:**
- **TypeScript:** Structural typing (shape-based)
- **PHP:** Nominal typing (must explicitly `implements` interface)
- **PHP interfaces:** Cannot have properties, only method signatures

## Multiple Interfaces

### TypeScript

```typescript
interface Nameable {
  getName(): string;
}

interface Timestamped {
  getCreatedAt(): Date;
}

class User implements Nameable, Timestamped {
  constructor(
    private name: string,
    private createdAt: Date
  ) {}

  getName(): string {
    return this.name;
  }

  getCreatedAt(): Date {
    return this.createdAt;
  }
}
```

### PHP

```php
<?php
declare(strict_types=1);

interface Nameable {
    public function getName(): string;
}

interface Timestamped {
    public function getCreatedAt(): DateTimeInterface;
}

class User implements Nameable, Timestamped {
    public function __construct(
        private string $name,
        private DateTimeImmutable $createdAt
    ) {}

    public function getName(): string {
        return $this->name;
    }

    public function getCreatedAt(): DateTimeInterface {
        return $this->createdAt;
    }
}
```

## Abstract Classes

### TypeScript

```typescript
abstract class Animal {
  constructor(protected name: string) {}

  abstract makeSound(): string;

  describe(): string {
    return `${this.name} says: ${this.makeSound()}`;
  }
}

class Dog extends Animal {
  makeSound(): string {
    return "Woof!";
  }
}

// const animal = new Animal("Test"); // ❌ Error: Cannot create instance of abstract class
const dog = new Dog("Buddy");
console.log(dog.describe()); // "Buddy says: Woof!"
```

### PHP

```php
<?php
declare(strict_types=1);

abstract class Animal {
    public function __construct(
        protected string $name
    ) {}

    abstract public function makeSound(): string;

    public function describe(): string {
        return "{$this->name} says: {$this->makeSound()}";
    }
}

class Dog extends Animal {
    public function makeSound(): string {
        return "Woof!";
    }
}

// $animal = new Animal("Test"); // ❌ Fatal error: Cannot instantiate abstract class
$dog = new Dog("Buddy");
echo $dog->describe(); // "Buddy says: Woof!"
```

## Traits (PHP's Mixin Alternative)

TypeScript doesn't have built-in mixins at the language level (requires helper functions), but PHP has **traits** for code reuse.

### PHP Traits

```php
<?php
declare(strict_types=1);

// Trait: reusable code blocks
trait Timestampable {
    private DateTimeImmutable $createdAt;

    public function setCreatedAt(): void {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getCreatedAt(): DateTimeImmutable {
        return $this->createdAt;
    }
}

trait Sluggable {
    private string $slug;

    public function setSlug(string $title): void {
        $this->slug = strtolower(str_replace(' ', '-', $title));
    }

    public function getSlug(): string {
        return $this->slug;
    }
}

// Use multiple traits
class BlogPost {
    use Timestampable, Sluggable;

    public function __construct(
        private string $title,
        private string $content
    ) {
        $this->setCreatedAt();
        $this->setSlug($title);
    }

    public function getTitle(): string {
        return $this->title;
    }
}

$post = new BlogPost("Hello World", "Content here");
echo $post->getSlug() . PHP_EOL;        // "hello-world"
echo $post->getCreatedAt()->format('Y-m-d') . PHP_EOL;
```

**Traits Benefits:**
- ✅ Code reuse without inheritance
- ✅ Multiple traits per class
- ✅ Resolve naming conflicts explicitly

## Inheritance

### TypeScript

```typescript
class Animal {
  constructor(protected name: string) {}

  speak(): string {
    return `${this.name} makes a sound`;
  }
}

class Dog extends Animal {
  constructor(name: string, private breed: string) {
    super(name);
  }

  speak(): string {
    return `${this.name} barks`;
  }

  getBreed(): string {
    return this.breed;
  }
}

const dog = new Dog("Buddy", "Golden Retriever");
console.log(dog.speak());     // "Buddy barks"
console.log(dog.getBreed());  // "Golden Retriever"
```

### PHP

```php
<?php
declare(strict_types=1);

class Animal {
    public function __construct(
        protected string $name
    ) {}

    public function speak(): string {
        return "{$this->name} makes a sound";
    }
}

class Dog extends Animal {
    public function __construct(
        string $name,
        private string $breed
    ) {
        parent::__construct($name);
    }

    public function speak(): string {
        return "{$this->name} barks";
    }

    public function getBreed(): string {
        return $this->breed;
    }
}

$dog = new Dog("Buddy", "Golden Retriever");
echo $dog->speak() . PHP_EOL;     // "Buddy barks"
echo $dog->getBreed() . PHP_EOL;  // "Golden Retriever"
```

**Key Differences:**
- PHP uses `parent::` instead of `super`
- PHP parent constructor must be called explicitly if child has constructor

## Type Hints for Classes

### TypeScript

```typescript
class User {
  constructor(public name: string) {}
}

function greet(user: User): string {
  return `Hello, ${user.name}!`;
}

const user = new User("Alice");
greet(user);
```

### PHP

```php
<?php
declare(strict_types=1);

class User {
    public function __construct(
        public string $name
    ) {}
}

function greet(User $user): string {
    return "Hello, {$user->name}!";
}

$user = new User("Alice");
greet($user);
```

## Generics (via PHPStan/Psalm)

PHP doesn't have native generics, but static analysis tools (PHPStan, Psalm) support generic annotations via docblocks.

### TypeScript

```typescript
class Repository<T> {
  private items: T[] = [];

  add(item: T): void {
    this.items.push(item);
  }

  getAll(): T[] {
    return this.items;
  }

  find(predicate: (item: T) => boolean): T | undefined {
    return this.items.find(predicate);
  }
}

interface User {
  id: number;
  name: string;
}

const userRepo = new Repository<User>();
userRepo.add({ id: 1, name: "Alice" });
const users = userRepo.getAll(); // Type: User[]
```

### PHP (with PHPStan/Psalm)

```php
<?php
declare(strict_types=1);

/**
 * @template T
 */
class Repository {
    /** @var array<T> */
    private array $items = [];

    /**
     * @param T $item
     */
    public function add(mixed $item): void {
        $this->items[] = $item;
    }

    /**
     * @return array<T>
     */
    public function getAll(): array {
        return $this->items;
    }

    /**
     * @param callable(T): bool $predicate
     * @return T|null
     */
    public function find(callable $predicate): mixed {
        foreach ($this->items as $item) {
            if ($predicate($item)) {
                return $item;
            }
        }
        return null;
    }
}

class User {
    public function __construct(
        public int $id,
        public string $name
    ) {}
}

/** @var Repository<User> */
$userRepo = new Repository();
$userRepo->add(new User(1, "Alice"));
$users = $userRepo->getAll(); // PHPStan infers: array<User>
```

**Generic Syntax:**
- `@template T` - Declare generic type
- `@param T $item` - Generic parameter
- `@return array<T>` - Generic return type
- `@var Repository<User>` - Instantiate with specific type

## Visibility Modifiers

| Modifier | TypeScript | PHP | Access |
|----------|------------|-----|--------|
| **public** | ✅ | ✅ | Everywhere |
| **protected** | ✅ | ✅ | Class + subclasses |
| **private** | ✅ | ✅ | Only within class |
| **readonly** | ✅ | ✅ (PHP 8.1+) | Immutable after init |
| **static** | ✅ | ✅ | Class level |

**PHP-specific:**
- `private` in PHP is truly private (not accessible in subclasses)
- TypeScript `private` is compile-time only (exists at runtime in JS)

## Magic Methods

PHP has special "magic methods" that TypeScript doesn't have equivalents for:

```php
<?php
declare(strict_types=1);

class User {
    private array $data = [];

    // Constructor
    public function __construct(string $name) {
        $this->data['name'] = $name;
    }

    // Get property dynamically
    public function __get(string $name): mixed {
        return $this->data[$name] ?? null;
    }

    // Set property dynamically
    public function __set(string $name, mixed $value): void {
        $this->data[$name] = $value;
    }

    // Convert to string
    public function __toString(): string {
        return "User: {$this->data['name']}";
    }

    // Called when object is invoked as function
    public function __invoke(string $greeting): string {
        return "{$greeting}, {$this->data['name']}!";
    }

    // Destructor
    public function __destruct() {
        // Cleanup code
    }
}

$user = new User("Alice");
echo $user->name . PHP_EOL;     // "Alice" (__get)
$user->age = 30;                 // __set
echo $user . PHP_EOL;            // "User: Alice" (__toString)
echo $user("Hello") . PHP_EOL;   // "Hello, Alice!" (__invoke)
```

## Practical Example: Repository Pattern

### TypeScript

```typescript
interface Entity {
  id: number;
}

interface Repository<T extends Entity> {
  find(id: number): T | undefined;
  save(entity: T): void;
  delete(id: number): void;
}

class InMemoryRepository<T extends Entity> implements Repository<T> {
  private items = new Map<number, T>();

  find(id: number): T | undefined {
    return this.items.get(id);
  }

  save(entity: T): void {
    this.items.set(entity.id, entity);
  }

  delete(id: number): void {
    this.items.delete(id);
  }
}

interface User extends Entity {
  name: string;
  email: string;
}

const userRepo = new InMemoryRepository<User>();
userRepo.save({ id: 1, name: "Alice", email: "alice@example.com" });
const user = userRepo.find(1);
```

### PHP

```php
<?php
declare(strict_types=1);

interface Entity {
    public function getId(): int;
}

/**
 * @template T of Entity
 */
interface Repository {
    /**
     * @return T|null
     */
    public function find(int $id): ?Entity;

    /**
     * @param T $entity
     */
    public function save(Entity $entity): void;

    public function delete(int $id): void;
}

/**
 * @template T of Entity
 * @implements Repository<T>
 */
class InMemoryRepository implements Repository {
    /** @var array<int, T> */
    private array $items = [];

    public function find(int $id): ?Entity {
        return $this->items[$id] ?? null;
    }

    public function save(Entity $entity): void {
        $this->items[$entity->getId()] = $entity;
    }

    public function delete(int $id): void {
        unset($this->items[$id]);
    }
}

class User implements Entity {
    public function __construct(
        private int $id,
        public string $name,
        public string $email
    ) {}

    public function getId(): int {
        return $this->id;
    }
}

/** @var InMemoryRepository<User> */
$userRepo = new InMemoryRepository();
$userRepo->save(new User(1, "Alice", "alice@example.com"));
$user = $userRepo->find(1);
```

## Hands-On Exercise

### Task 1: Create a Shape Hierarchy

Create an abstract `Shape` class with `Circle` and `Rectangle` implementations:

**Requirements:**
- Abstract `Shape` class with abstract `getArea()` and `getPerimeter()` methods
- `Circle` class (radius)
- `Rectangle` class (width, height)
- `Drawable` interface with `draw()` method
- Both shapes implement `Drawable`

<details>
<summary>Solution</summary>

```php
<?php
declare(strict_types=1);

interface Drawable {
    public function draw(): string;
}

abstract class Shape {
    abstract public function getArea(): float;
    abstract public function getPerimeter(): float;

    public function describe(): string {
        return sprintf(
            "Area: %.2f, Perimeter: %.2f",
            $this->getArea(),
            $this->getPerimeter()
        );
    }
}

class Circle extends Shape implements Drawable {
    public function __construct(
        private float $radius
    ) {}

    public function getArea(): float {
        return pi() * $this->radius ** 2;
    }

    public function getPerimeter(): float {
        return 2 * pi() * $this->radius;
    }

    public function draw(): string {
        return "Drawing a circle with radius {$this->radius}";
    }
}

class Rectangle extends Shape implements Drawable {
    public function __construct(
        private float $width,
        private float $height
    ) {}

    public function getArea(): float {
        return $this->width * $this->height;
    }

    public function getPerimeter(): float {
        return 2 * ($this->width + $this->height);
    }

    public function draw(): string {
        return "Drawing a rectangle {$this->width}x{$this->height}";
    }
}

// Test
$circle = new Circle(5);
echo $circle->describe() . PHP_EOL;
echo $circle->draw() . PHP_EOL;

$rectangle = new Rectangle(4, 6);
echo $rectangle->describe() . PHP_EOL;
echo $rectangle->draw() . PHP_EOL;
```
</details>

### Task 2: Generic Collection

Create a generic `Collection` class with type-safe operations:

<details>
<summary>Solution</summary>

```php
<?php
declare(strict_types=1);

/**
 * @template T
 */
class Collection {
    /** @var array<T> */
    private array $items = [];

    /**
     * @param T $item
     */
    public function add(mixed $item): void {
        $this->items[] = $item;
    }

    /**
     * @return array<T>
     */
    public function all(): array {
        return $this->items;
    }

    /**
     * @param callable(T): bool $predicate
     * @return Collection<T>
     */
    public function filter(callable $predicate): self {
        $filtered = new self();
        foreach ($this->items as $item) {
            if ($predicate($item)) {
                $filtered->add($item);
            }
        }
        return $filtered;
    }

    /**
     * @template U
     * @param callable(T): U $mapper
     * @return Collection<U>
     */
    public function map(callable $mapper): self {
        $mapped = new self();
        foreach ($this->items as $item) {
            $mapped->add($mapper($item));
        }
        return $mapped;
    }

    public function count(): int {
        return count($this->items);
    }
}

// Test
/** @var Collection<int> */
$numbers = new Collection();
$numbers->add(1);
$numbers->add(2);
$numbers->add(3);
$numbers->add(4);

$evens = $numbers->filter(fn($x) => $x % 2 === 0);
$doubled = $numbers->map(fn($x) => $x * 2);

print_r($evens->all());   // [2, 4]
print_r($doubled->all()); // [2, 4, 6, 8]
```
</details>

## Common OOP Pitfalls

### Pitfall 1: Forgetting `parent::__construct()`

```php
<?php
class Animal {
    public function __construct(
        protected string $name
    ) {}
}

// ❌ BAD: Doesn't call parent constructor
class Dog extends Animal {
    public function __construct(
        string $name,
        private string $breed
    ) {
        $this->breed = $breed; // ⚠️ $name never set!
    }
}

// ✅ GOOD: Calls parent constructor
class Dog extends Animal {
    public function __construct(
        string $name,
        private string $breed
    ) {
        parent::__construct($name);
    }
}
```

### Pitfall 2: Trait Method Conflicts

```php
<?php
trait A {
    public function test() { return "A"; }
}

trait B {
    public function test() { return "B"; }
}

// ❌ ERROR: Method conflict
class MyClass {
    use A, B; // Fatal error: Trait method conflict
}

// ✅ SOLUTION: Resolve explicitly
class MyClass {
    use A, B {
        A::test insteadof B;  // Use A's test
        B::test as testB;      // Alias B's test
    }
}
```

### Pitfall 3: Interface Properties

```typescript
// ✅ TypeScript: Interfaces can have properties
interface User {
  name: string;
  age: number;
}
```

```php
<?php
// ❌ PHP: Interfaces cannot have properties
interface User {
    public string $name; // Parse error!
}

// ✅ PHP: Use getters/setters
interface User {
    public function getName(): string;
    public function getAge(): int;
}
```

### Pitfall 4: Visibility in Interfaces

```php
<?php
// ❌ BAD: Interface methods must be public
interface MyInterface {
    private function test(): void; // Parse error!
    protected function foo(): void; // Parse error!
}

// ✅ GOOD: Only public methods allowed
interface MyInterface {
    public function test(): void;
    public function foo(): void;
}
```

### Pitfall 5: Static vs Instance Context

```php
<?php
class Counter {
    private static int $count = 0;
    private int $instanceCount = 0;

    public function increment(): void {
        self::$count++;      // ✅ Static property
        $this->instanceCount++; // ✅ Instance property

        // ❌ WRONG: Can't use $this for static
        // $this->count++; // Error!

        // ❌ WRONG: Can't use self:: for instance
        // self::$instanceCount++; // Error!
    }
}
```

## Key Takeaways

1. **Constructor promotion** (PHP 8.0+) makes classes as concise as TypeScript
2. **Nominal typing** in PHP requires explicit `implements` (unlike TS structural typing)
3. **Interfaces** can only define public methods, not properties
4. **Traits** provide mixin-like functionality without inheritance limitations
5. **Abstract classes** work identically in both languages
6. **Generics** require PHPStan/Psalm docblock annotations (`@template`)
7. **Magic methods** (`__get`, `__set`, `__toString`) provide dynamic behavior
8. **Readonly properties** (PHP 8.1+) work like TypeScript's `readonly`
9. **Always call `parent::__construct()`** when extending classes with constructors
10. **Resolve trait conflicts explicitly** using `insteadof` and `as`
11. **Static context** uses `self::` or `static::`, instance context uses `$this->`
12. **Visibility inheritance** rules: child methods can be more permissive, not less
13. **Type covariance** for return types and **contravariance** for parameters (PHP 7.4+)
14. **Final classes/methods** prevent extension/overriding

## Comparison Table

| Feature | TypeScript | PHP |
|---------|------------|-----|
| **Class Syntax** | `class User {}` | `class User {}` |
| **Constructor Promotion** | ✅ | ✅ (PHP 8.0+) |
| **Interfaces** | Structural | Nominal |
| **Abstract Classes** | ✅ | ✅ |
| **Multiple Inheritance** | ❌ | ❌ |
| **Mixins/Traits** | Helper functions | Native traits |
| **Generics** | Native | Docblocks only |
| **Readonly** | ✅ | ✅ (PHP 8.1+) |
| **Private Fields** | Compile-time | Runtime |
| **Static Members** | ✅ | ✅ |
| **Property Access** | `.` | `->` |

## Next Steps

Now that you understand OOP in PHP, let's explore error handling and exception management.

**Next Chapter:** [05: Error Handling: Try/Catch & Type Safety](/series/php-typescript-developers/chapters/05-error-handling)

## Resources

- [PHP OOP Documentation](https://www.php.net/manual/en/language.oop5.php)
- [PHP Traits](https://www.php.net/manual/en/language.oop5.traits.php)
- [PHPStan Generics](https://phpstan.org/blog/generics-in-php-using-phpdocs)
- [Psalm Templating](https://psalm.dev/docs/annotating_code/templated_annotations/)

---

**Questions or feedback?** Open an issue on [GitHub](https://github.com/dalehurley/codewithphp/issues)
