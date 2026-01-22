---
title: "02: Modern PHP: What's Changed"
description: "Discover how PHP 8.4 has evolved into a modern, type-safe language that Ruby developers will appreciate"
sidebar:
  label: "02: Modern PHP: What's Changed"
  order: 2
  badge:
    text: Beginner
    variant: success
---


![Modern PHP: What's Changed](/images/rails-developers-love-laravel/chapter-02-modern-php-whats-changed-hero-full.webp)

# 02: Modern PHP: What's Changed <span class="difficulty-badge difficulty-beginner">Beginner</span>

## Overview

If your last encounter with PHP was years ago, prepare to be pleasantly surprised. Modern PHP (8.0+) has undergone a dramatic transformation that brings it closer to Ruby in expressiveness while adding powerful features of its own.

This chapter explores how PHP has evolved from a loosely-typed scripting language into a modern, performant language with sophisticated type systems, elegant syntax, and developer-friendly features that Ruby developers will appreciate.

## Prerequisites

Before starting this chapter, you should have:

- Completion of [Chapter 01: Mapping Concepts: Rails vs Laravel](/series/rails-developers-love-laravel/chapters/01-mapping-concepts-rails-vs-laravel) or equivalent understanding of Rails concepts
- Basic familiarity with Ruby syntax and language features
- **Estimated Time**: ~45-60 minutes

**Note:** This chapter is reference-heavy and doesn't require you to write code. You can read through it to understand PHP's evolution, then refer back to specific sections as needed.

## What You'll Build

By the end of this chapter, you will have:

- A comprehensive understanding of PHP 8.4's modern features
- Side-by-side comparisons of Ruby and PHP syntax
- Knowledge of type safety improvements in modern PHP
- Understanding of performance enhancements with JIT compilation
- Mental models for translating Ruby patterns to PHP
- Practical examples showing PHP 8.4 features in action

## What You'll Learn

- How PHP 8.x transformed the language
- Modern PHP features that rival Ruby's elegance
- Type safety improvements and strict typing
- Performance enhancements with JIT compilation
- New syntax features in PHP 8.4 (property hooks, asymmetric visibility)
- PHP 8.3 features (typed constants, JSON validation, dynamic properties deprecation)
- Important limitations: what PHP doesn't have (generics, pattern matching)
- Why modern PHP feels nothing like old PHP
- Side-by-side Ruby vs PHP comparisons

## 📦 Code Samples

All executable code examples for this chapter are available on GitHub:

- **[Type Safety Example](https://github.com/dalehurley/codewithphp/blob/main/code/rails-developers-love-laravel/chapter-02/01-type-safety-example.php)** — Type declarations, strict typing, union types
- **[Property Hooks Example](https://github.com/dalehurley/codewithphp/blob/main/code/rails-developers-love-laravel/chapter-02/02-property-hooks-example.php)** — PHP 8.4 property hooks, computed properties, lazy loading
- **[Enums Example](https://github.com/dalehurley/codewithphp/blob/main/code/rails-developers-love-laravel/chapter-02/03-enums-example.php)** — Enums with methods, backing values, exhaustive matching

You can run these examples locally:

```bash
# Download all chapter 02 examples
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/rails-developers-love-laravel/chapter-02

# Run examples
php 01-type-safety-example.php
php 02-property-hooks-example.php
php 03-enums-example.php
```

All examples require PHP 8.4+

## The PHP Renaissance

PHP has undergone a renaissance since version 7.0 (2015) and especially with the 8.x series:

**PHP 7.0-7.4** (2015-2019):

- Type declarations
- Return types
- Scalar type hints
- Anonymous classes
- Null coalescing operator
- 2x performance improvement

**PHP 8.0** (2020):

- JIT compilation
- Named arguments
- Attributes (like Ruby decorators)
- Union types
- Match expressions
- Constructor property promotion

**PHP 8.1** (2021):

- Enums
- Readonly properties
- Fibers (like Ruby Fibers)
- First-class callables

**PHP 8.2** (2022):

- Readonly classes
- DNF types
- True/false/null standalone types

**PHP 8.3** (2023):

- Typed class constants
- Dynamic properties deprecation
- JSON validation

**PHP 8.4** (2024):

- Property hooks
- Asymmetric visibility
- New array functions
- HTML5 support

Each version added features that make PHP more elegant, type-safe, and performant.

## Type System: From Loose to Strict

### The Old Way (PHP 5.x)

```php
# filename: old-php-example.php
<?php
// Old PHP - no type safety
function getUser($id) {
    $user = User::find($id);
    if ($user == null) {  // Weak comparison
        return null;
    }
    return $user->name;
}

// Called with anything
getUser("hello");  // No error!
getUser([1, 2, 3]);  // Still no error!
```

This is the PHP that gave the language a bad reputation.

### The Modern Way (PHP 8.4)

```php
# filename: modern-php-example.php
<?php
// Modern PHP - strict types
declare(strict_types=1);

function getUser(int $id): ?string
{
    return User::find($id)?->name;
}

// Type errors caught immediately
getUser("hello");    // TypeError!
getUser([1, 2, 3]);  // TypeError!
```

### Ruby Comparison

```ruby
# Ruby - duck typing with optional type checking (Sorbet/RBS)
def get_user(id)
  User.find(id)&.name
end

# With Sorbet type annotations
sig { params(id: Integer).returns(T.nilable(String)) }
def get_user(id)
  User.find(id)&.name
end
```

Modern PHP's type system is **more strict by default** than Ruby, which can prevent entire classes of bugs.

::: tip Pro Tip: Enabling Strict Types
Always start your PHP files with `declare(strict_types=1);` for maximum type safety. This catches type errors at call-time instead of silently coercing types.

```php
<?php
declare(strict_types=1); // Always add this!

// Now TypeErrors are thrown instead of silent coercion
function add(int $a, int $b): int {
    return $a + $b;
}

add(5, "10"); // TypeError (good!)
// Without strict_types: silently converts "10" to 10 (bad!)
```

:::

::: warning Common Gotcha: Strict Types Are File-Scoped
`declare(strict_types=1)` only affects the file it's declared in, not the files you call! Each file needs its own declaration.

```php
<?php
// File: UserService.php
declare(strict_types=1);

class UserService {
    public function process(int $id) { ... }
}

// File: controller.php
// NO strict_types declared here!
$service = new UserService();
$service->process("5"); // This STILL gets coerced to 5!
```

**Solution:** Add `declare(strict_types=1)` to every PHP file, or use PHPStan/Psalm to catch these at build time.
:::

## Modern Syntax: Null Safety

### Null Coalescing

**Ruby:**

```ruby
# Ruby
name = user.name || "Guest"
name = user&.name || "Guest"
```

**PHP:**

```php
<?php
// PHP 7.0+ - Null coalescing operator
$name = $user->name ?? "Guest";

// PHP 8.0+ - Null-safe operator
$name = $user?->profile?->name ?? "Guest";
```

Nearly identical to Ruby's safe navigation operator!

### Null Coalescing Assignment

**Ruby:**

```ruby
# Ruby
@cache ||= []
@cache ||= expensive_operation()
```

**PHP:**

```php
<?php
// PHP 7.4+ - Null coalescing assignment
$this->cache ??= [];
$this->cache ??= expensive_operation();
```

Same pattern, different syntax.

## Constructor Property Promotion

One of PHP 8.0's best features eliminates boilerplate:

### Before PHP 8.0

```php
<?php
class User
{
    private string $name;
    private string $email;
    private int $age;

    public function __construct(string $name, string $email, int $age)
    {
        $this->name = $name;
        $this->email = $email;
        $this->age = $age;
    }
}
```

### PHP 8.0+

```php
<?php
class User
{
    public function __construct(
        private string $name,
        private string $email,
        private int $age,
    ) {}
}
```

Much cleaner! Compare to Ruby:

```ruby
# Ruby
class User
  attr_reader :name, :email, :age

  def initialize(name, email, age)
    @name = name
    @email = email
    @age = age
  end
end
```

PHP's version is even more concise.

## Property Hooks (PHP 8.4)

PHP 8.4 introduced property hooks - a game-changing feature that rivals Ruby's elegance:

### Ruby Properties

```ruby
# Ruby - automatic getters/setters
class User
  attr_accessor :name

  # Custom setter with logic
  def name=(value)
    @name = value.strip.capitalize
  end

  def name
    @name
  end
end

user = User.new
user.name = "  john  "
puts user.name  # "John"
```

### PHP 8.4 Property Hooks

```php
<?php
class User
{
    // Property hook with automatic backing field
    public string $name {
        set(string $value) {
            $this->name = trim(ucfirst($value));
        }
    }

    // Computed property (no backing field)
    public string $fullName {
        get => "{$this->firstName} {$this->lastName}";
    }

    // Asymmetric visibility (new in 8.4!)
    public private(set) int $id;
}

$user = new User();
$user->name = "  john  ";
echo $user->name;  // "John"
echo $user->fullName;  // Computed on access

$user->id = 5;  // Error! Can only set internally
```

This is **incredibly elegant** and rivals or exceeds Ruby's property capabilities.

### Lazy Loading with Property Hooks

```php
<?php
class Post
{
    private ?Collection $comments = null;

    public Collection $comments {
        get {
            $this->comments ??= $this->loadComments();
            return $this->comments;
        }
    }

    private function loadComments(): Collection
    {
        return Comment::where('post_id', $this->id)->get();
    }
}

$post = Post::find(1);
// Comments only loaded when accessed
$comments = $post->comments;
```

Compare to Ruby:

```ruby
# Ruby
class Post
  def comments
    @comments ||= load_comments
  end

  private

  def load_comments
    Comment.where(post_id: id)
  end
end
```

Both achieve lazy loading elegantly.

::: tip Pro Tip: Property Hooks vs Traditional Getters/Setters
Property hooks are cleaner than traditional get/set methods:

**Old Way (verbose):**

```php
<?php
class User {
    private string $name;

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = trim(ucfirst($name));
    }
}

$user->setName("john"); // Ugly method call
```

**New Way (elegant):**

```php
<?php
class User {
    public string $name {
        set => trim(ucfirst($value));
    }
}

$user->name = "john"; // Natural property access
```

:::

::: warning Common Gotcha: Infinite Recursion with Property Hooks
Be careful not to create infinite loops!

```php
<?php
class User {
    public string $name {
        set(string $value) {
            // ❌ WRONG - creates infinite recursion!
            $this->name = strtoupper($value);
        }
    }
}
```

**Why:** Setting `$this->name` inside the `set` hook calls the hook again!

**Solution:** Use the backing field or a different property:

```php
<?php
class User {
    private string $_name; // Backing field

    public string $name {
        get => $this->_name;
        set(string $value) {
            $this->_name = strtoupper($value); // ✅ Correct
        }
    }
}
```

**Or use the shorthand** (PHP automatically handles the backing field):

```php
<?php
class User {
    public string $name {
        set => strtoupper($value); // ✅ Works! No recursion
    }
}
```

:::

## Enums: Type-Safe Constants

### Ruby Approach

```ruby
# Ruby - typically uses symbols or constants
class Status
  DRAFT = "draft"
  PUBLISHED = "published"
  ARCHIVED = "archived"
end

post.status = Status::DRAFT

# Or with gems
class Status < T::Enum
  enums do
    Draft = new
    Published = new
    Archived = new
  end
end
```

### PHP 8.1+ Enums

```php
<?php
enum Status: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    // Enums can have methods!
    public function color(): string
    {
        return match($this) {
            self::Draft => 'yellow',
            self::Published => 'green',
            self::Archived => 'gray',
        };
    }

    public function isPublic(): bool
    {
        return $this === self::Published;
    }
}

// Usage
$status = Status::Published;
echo $status->value;      // "published"
echo $status->color();    // "green"
echo $status->isPublic(); // true

// Type safety
function setStatus(Status $status): void {
    // Only valid Status enums accepted
}

setStatus(Status::Draft);    // ✓
setStatus("draft");          // ✗ TypeError!
```

PHP's enums are **first-class language features** with full type safety.

## Match Expressions: Better Switch

### Ruby Case

```ruby
# Ruby case statement
status = case role
         when "admin"
           "full_access"
         when "editor"
           "edit_access"
         when "viewer"
           "read_access"
         else
           "no_access"
         end
```

### PHP 8.0 Match

```php
<?php
// PHP 8.0+ match expression
$status = match($role) {
    'admin' => 'full_access',
    'editor' => 'edit_access',
    'viewer' => 'read_access',
    default => 'no_access',
};

// Match is an expression (returns value)
// Strict comparison (===)
// No fallthrough
// Exhaustive checking with enums

$color = match($status) {
    Status::Draft => 'yellow',
    Status::Published => 'green',
    Status::Archived => 'gray',
    // Error if any case is missing!
};
```

Match expressions are more concise and type-safe than traditional switch statements.

## Named Arguments

### Ruby Keyword Arguments

```ruby
# Ruby - keyword arguments
def create_user(name:, email:, age: 18, active: true)
  # ...
end

create_user(
  name: "John",
  email: "john@example.com",
  age: 25
)

# Can reorder arguments
create_user(
  email: "john@example.com",
  name: "John"
)
```

### PHP 8.0+ Named Arguments

```php
# filename: named-arguments-example.php
<?php
// PHP 8.0+ - named arguments
function createUser(
    string $name,
    string $email,
    int $age = 18,
    bool $active = true
): User {
    // ...
}

createUser(
    name: "John",
    email: "john@example.com",
    age: 25
);

// Can reorder arguments
createUser(
    email: "john@example.com",
    name: "John"
);

// Skip optional arguments
createUser(
    name: "John",
    email: "john@example.com",
    active: false  // Skip $age, use its default
);
```

PHP now has the same flexibility as Ruby's keyword arguments!

## Union Types and Type Safety

### Ruby Type Annotations (with Sorbet)

```ruby
# Ruby with Sorbet
sig { params(id: T.any(Integer, String)).returns(T.nilable(User)) }
def find_user(id)
  User.find(id)
end
```

### PHP 8.0+ Union Types

```php
<?php
// PHP 8.0+ - union types
function findUser(int|string $id): ?User
{
    return User::find($id);
}

// PHP 8.2+ - DNF types (Disjunctive Normal Form)
function process((Stringable&JsonSerializable)|string $data): void
{
    // Accepts: (Stringable AND JsonSerializable) OR string
}

// PHP 8.2+ - standalone types
function getValue(): true|false|null
{
    // Explicit true/false/null types
}
```

PHP's union types are **built into the language**, not a third-party tool.

## Attributes: Metadata That Matters

### Ruby Decorators (via gems)

```ruby
# Ruby - typically requires gems
class User < ApplicationRecord
  # Using custom decorators/concerns
  include Cacheable

  cached(:ttl => 3600)
  def expensive_operation
    # ...
  end
end
```

### PHP 8.0+ Attributes

```php
# filename: attributes-example.php
<?php
// PHP 8.0+ - built-in attributes
#[Route('/users/{id}', methods: ['GET'])]
#[Cache(ttl: 3600)]
#[Authorize(role: 'admin')]
class UserController
{
    #[Validate(['id' => 'integer|min:1'])]
    public function show(int $id): JsonResponse
    {
        // Attributes processed by framework
        return response()->json(User::find($id));
    }
}

// Laravel uses attributes extensively
#[AsController]
class PostController extends Controller
{
    #[Get('/posts')]
    public function index(): View
    {
        return view('posts.index');
    }
}
```

Attributes provide clean, declarative metadata without decorators or mixins.

## Arrow Functions and Closures

### Ruby Blocks and Lambdas

```ruby
# Ruby - multiple syntaxes
numbers = [1, 2, 3, 4, 5]

# Block syntax
doubled = numbers.map { |n| n * 2 }

# Lambda/proc
multiply = ->(x, y) { x * y }
result = multiply.call(3, 4)

# Block with do/end
numbers.each do |n|
  puts n
end
```

### PHP Arrow Functions

```php
<?php
// PHP 7.4+ - arrow functions (short closures)
$numbers = [1, 2, 3, 4, 5];

// Arrow function - auto-captures variables
$doubled = array_map(fn($n) => $n * 2, $numbers);

$multiplier = 3;
$tripled = array_map(fn($n) => $n * $multiplier, $numbers);

// PHP 8.0+ - traditional closure with shorthand
$multiply = fn($x, $y) => $x * $y;

// Multi-line closure
$process = function($n) use ($multiplier) {
    echo $n * $multiplier;
    return $n * $multiplier;
};
```

Arrow functions make PHP feel more functional, like Ruby.

## First-Class Callables

### Ruby Method References

```ruby
# Ruby - symbols as method references
numbers = ["1", "2", "3"]
integers = numbers.map(&:to_i)

# Method objects
method = User.method(:find)
user = method.call(1)
```

### PHP 8.1+ First-Class Callables

```php
# filename: first-class-callables-example.php
<?php
// PHP 8.1+ - first-class callable syntax
$numbers = ["1", "2", "3"];
$integers = array_map(intval(...), $numbers);

// Method references
$finder = User::find(...);
$user = $finder(1);

// Instance methods
$user = new User();
$getName = $user->getName(...);
echo $getName();  // Calls $user->getName()

// Static methods
$validator = Validator::make(...);
$validator(['email' => 'required']);
```

The `...` syntax creates clean callable references.

## Collections and Functional Programming

### Ruby Enumerables

```ruby
# Ruby - rich enumerable methods
users = User.all

result = users
  .select { |u| u.active? }
  .map { |u| u.name.upcase }
  .sort
  .take(10)

# Ruby's lazy enumerables
result = (1..Float::INFINITY)
  .lazy
  .select { |n| n.even? }
  .take(10)
  .to_a
```

### Laravel Collections

```php
# filename: collections-example.php
<?php
// Laravel Collections - Ruby-inspired
$users = User::all();

$result = $users
    ->filter(fn($u) => $u->active)
    ->map(fn($u) => strtoupper($u->name))
    ->sort()
    ->take(10);

// Lazy collections (PHP 8.x)
$result = LazyCollection::make(function() {
    yield from range(1, INF);
})
    ->filter(fn($n) => $n % 2 === 0)
    ->take(10)
    ->all();

// Rich collection methods (150+ methods!)
$collection = collect([1, 2, 3, 4, 5]);

$collection->sum();
$collection->avg();
$collection->chunk(2);
$collection->groupBy('status');
$collection->pluck('name', 'id');
$collection->pipe(fn($c) => $c->sum() * 2);
```

Laravel's Collections API is **directly inspired by Ruby's Enumerable**.

## Performance: JIT Compilation

One area where modern PHP pulls ahead is performance.

### PHP 8.0+ JIT Compiler

PHP 8.0 introduced Just-In-Time compilation:

```php
# filename: jit-example.php
<?php
// JIT automatically optimizes hot code paths
function fibonacci(int $n): int
{
    if ($n <= 1) return $n;
    return fibonacci($n - 1) + fibonacci($n - 2);
}

// JIT identifies this as hot code and compiles to machine code
for ($i = 0; $i < 100000; $i++) {
    fibonacci(20);
}
```

**Benchmarks (approximate):**

- PHP 7.4 (no JIT): 100ms
- PHP 8.4 (with JIT): 30-40ms
- Ruby 3.3 (YJIT): 80-100ms

PHP's JIT provides **2-3x performance improvement** in CPU-intensive tasks.

### Real-World Performance

**Web Request Handling:**

- PHP 8.4: ~15,000 req/sec (simple JSON response)
- Ruby 3.3 + Rails: ~3,000-5,000 req/sec

**Database Operations:**

- Similar performance (database is bottleneck)

**JSON Parsing:**

- PHP: Significantly faster (native C implementation)
- Ruby: Slower but acceptable for most use cases

PHP's performance advantage matters for:

- High-traffic applications
- API-heavy workloads
- Real-time processing
- Cost optimization (fewer servers needed)

::: tip Pro Tip: Enabling JIT in Production
JIT is disabled by default. Enable it in `php.ini`:

```ini
; Recommended for production
opcache.enable=1
opcache.jit_buffer_size=100M
opcache.jit=1255  ; tracing mode (best for web apps)
```

**JIT Modes:**

- `1255` (tracing) - Best for web applications (default recommendation)
- `1205` (function) - Best for CPU-intensive scripts
- `0` (disabled) - Debugging/development

**Test it:**

```bash
php -d opcache.jit=1255 -d opcache.jit_buffer_size=100M your-script.php
```

:::

::: warning Common Gotcha: JIT Doesn't Help Everything
JIT primarily helps CPU-intensive code, not I/O-bound operations:

**JIT Helps:**

- ✅ Complex calculations
- ✅ String manipulation loops
- ✅ Array operations
- ✅ Recursive algorithms

**JIT Doesn't Help Much:**

- ❌ Database queries (I/O bound)
- ❌ API calls (network bound)
- ❌ File operations (I/O bound)
- ❌ Most web requests (already fast)

**Reality:** For typical Laravel web apps, JIT provides 5-15% improvement, not the 2-3x you see in benchmarks. Still worth enabling!
:::

## Fibers: Lightweight Concurrency

### Ruby Fibers

```ruby
# Ruby Fiber
fiber = Fiber.new do
  puts "Fiber started"
  Fiber.yield "intermediate result"
  puts "Fiber resumed"
  "final result"
end

puts fiber.resume  # "intermediate result"
puts fiber.resume  # "final result"
```

### PHP 8.1+ Fibers

```php
<?php
// PHP 8.1+ Fiber
$fiber = new Fiber(function(): void {
    echo "Fiber started\n";
    Fiber::suspend("intermediate result");
    echo "Fiber resumed\n";
});

echo $fiber->start();  // "intermediate result"
echo $fiber->resume("final result");
```

Both languages support cooperative multitasking with similar APIs.

## Readonly Classes

### Ruby Frozen Objects

```ruby
# Ruby - freeze objects
class Point
  attr_reader :x, :y

  def initialize(x, y)
    @x = x
    @y = y
    freeze  # Make immutable
  end
end

point = Point.new(10, 20)
point.x = 30  # Error! Frozen object
```

### PHP 8.2+ Readonly Classes

```php
<?php
// PHP 8.2+ - readonly classes
readonly class Point
{
    public function __construct(
        public int $x,
        public int $y,
    ) {}
}

$point = new Point(10, 20);
$point->x = 30;  // Error! Readonly property

// All properties are automatically readonly
// Can only be initialized in constructor
```

PHP's readonly classes provide **compile-time immutability guarantees**.

## Array Improvements

### Ruby Arrays

```ruby
# Ruby arrays
arr = [1, 2, 3, 4, 5]

arr.first       # 1
arr.last        # 5
arr.first(3)    # [1, 2, 3]
arr.last(2)     # [4, 5]

# Functional operations
arr.any? { |n| n > 3 }
arr.all? { |n| n > 0 }
arr.find { |n| n > 3 }
```

### PHP 8.4 Array Functions

```php
# filename: array-functions-example.php
<?php
// PHP 8.4+ - new array functions
$arr = [1, 2, 3, 4, 5];

array_first($arr);              // 1
array_last($arr);               // 5
array_find($arr, fn($n) => $n > 3);     // 4
array_find_key($arr, fn($n) => $n > 3); // 3
array_any($arr, fn($n) => $n > 3);      // true
array_all($arr, fn($n) => $n > 0);      // true

// Previous versions required Collection or custom functions
```

PHP is catching up to Ruby's array convenience methods!

## PHP 8.3 Features: Typed Constants and JSON Validation

### Typed Class Constants

PHP 8.3 introduced typed class constants, providing better type safety:

**Before PHP 8.3:**

```php
<?php
class Status
{
    const DRAFT = 'draft';        // No type checking
    const PUBLISHED = 'published';
}
```

**PHP 8.3+:**

```php
# filename: typed-constants-example.php
<?php
class Status
{
    public const string DRAFT = 'draft';
    public const string PUBLISHED = 'published';
    public const int MAX_LENGTH = 255;
}

// Type safety enforced
function setStatus(string $status): void
{
    // Only string constants accepted
}
```

Compare to Ruby:

```ruby
# Ruby - constants are always strings/symbols
class Status
  DRAFT = "draft"
  PUBLISHED = "published"
end
```

### Dynamic Properties Deprecation

**Important for Ruby Developers:** PHP 8.3 deprecated dynamic properties (creating properties on the fly), which Ruby allows freely.

**Ruby (always allowed):**

```ruby
# Ruby - dynamic properties always work
user = User.new
user.custom_field = "value"  # Works!
```

**PHP 8.3+ (deprecated):**

```php
# filename: dynamic-properties-example.php
<?php
$user = new User();
$user->customField = "value";  // Deprecated warning in 8.3, error in 9.0!
```

**Solution:** Use `#[AllowDynamicProperties]` attribute or explicitly declare properties:

```php
# filename: allow-dynamic-properties.php
<?php
#[AllowDynamicProperties]
class User
{
    // Now dynamic properties work
}

// Or better: declare properties explicitly
class User
{
    public ?string $customField = null;  // Explicit property
}
```

This is a **breaking change** for Ruby developers who expect dynamic properties to work!

### JSON Validation

PHP 8.3 added `json_validate()` for validating JSON without parsing:

```php
# filename: json-validation-example.php
<?php
// PHP 8.3+ - validate JSON without parsing
$json = '{"name": "John", "age": 30}';

if (json_validate($json)) {
    // JSON is valid, safe to parse
    $data = json_decode($json);
} else {
    // Invalid JSON
    throw new InvalidArgumentException('Invalid JSON');
}

// More efficient than json_decode() + check for errors
```

## PHP 8.4: HTML5 Support

PHP 8.4 added better HTML5 support in the DOM extension:

```php
# filename: html5-example.php
<?php
// PHP 8.4+ - improved HTML5 parsing
$html = '<!DOCTYPE html><html><body><section><article>Content</article></section></body></html>';

$dom = new DOMDocument();
$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

// Better handling of HTML5 semantic elements
$articles = $dom->getElementsByTagName('article');
```

This is particularly useful for web scraping and HTML processing, though most Laravel applications use Blade templates instead.

## Important Limitations: What PHP Doesn't Have

### No Generics (Yet)

Unlike Ruby (with Sorbet/RBS) or languages like TypeScript, PHP doesn't have generics:

**Ruby with Sorbet:**

```ruby
# Ruby with Sorbet - generics
sig { params(items: T::Array[User]).returns(T::Array[String]) }
def get_names(items)
  items.map(&:name)
end
```

**PHP (no generics):**

```php
<?php
// PHP - no generics, must use docblocks or union types
/**
 * @template T
 * @param array<T> $items
 * @return array<string>
 */
function getNames(array $items): array
{
    // Type information lost at runtime
    return array_map(fn($item) => $item->name, $items);
}

// Or use union types (limited)
function getNames(array $items): array
{
    // Less type-safe than generics
    return array_map(fn($item) => $item->name, $items);
}
```

**Workaround:** Use PHPStan or Psalm for static analysis with generics support, or wait for PHP 9.0+ (generics are being discussed).

### No Pattern Matching (Yet)

Ruby has pattern matching (introduced in 2.7), but PHP doesn't:

**Ruby:**

```ruby
# Ruby - pattern matching
case user
in {role: "admin", active: true}
  "Full access"
in {role: "editor"}
  "Edit access"
else
  "No access"
end
```

**PHP (use match instead):**

```php
<?php
// PHP - use match expression (simpler but less powerful)
$access = match($user->role) {
    'admin' => $user->active ? 'Full access' : 'No access',
    'editor' => 'Edit access',
    default => 'No access',
};
```

## Comparing Language Evolution

### Ruby's Strengths

**What Ruby Still Does Better:**

- More elegant syntax (no semicolons, less punctuation)
- Better metaprogramming (method_missing, define_method)
- More mature block syntax
- Cleaner module system
- Better REPL (IRB)

**Ruby Example:**

```ruby
# Ruby's elegant syntax
class User < ApplicationRecord
  has_many :posts

  scope :active, -> { where(active: true) }

  def full_name
    "#{first_name} #{last_name}"
  end
end

user = User.find(1)
puts user.full_name if user.active?
```

### PHP's Strengths

**What PHP Does Better:**

- Stricter type system (compile-time checking)
- Better performance (JIT compilation)
- Simpler deployment (no application server)
- Ubiquitous hosting support
- Lower memory usage
- Property hooks (8.4)
- First-class enums

**PHP Example:**

```php
<?php
declare(strict_types=1);

class User extends Model
{
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }

    // Property hook (8.4)
    public string $fullName {
        get => "{$this->firstName} {$this->lastName}";
    }
}

$user = User::find(1);
if ($user->active) {
    echo $user->fullName;
}
```

Both achieve the same result with different trade-offs.

## Modern PHP Developer Experience

### Package Management

**Ruby:**

```bash
# Gemfile
gem 'rails', '~> 7.0'
gem 'pg'

bundle install
```

**PHP:**

```bash
# composer.json
"require": {
    "laravel/framework": "^12.0",
    "doctrine/dbal": "^3.0"
}

composer install
```

Composer and Bundler are nearly identical in functionality.

### Interactive Console

**Ruby:**

```bash
rails console

> user = User.first
> user.posts.count
```

**PHP:**

```bash
php artisan tinker

>>> $user = User::first()
>>> $user->posts->count()
```

Laravel's Tinker provides the same REPL experience.

### Code Generation

**Ruby:**

```bash
rails generate model User name:string email:string
rails generate controller Users index show
```

**PHP:**

```bash
php artisan make:model User -m
php artisan make:controller UserController --resource
```

Same philosophy, different commands.

## The Bottom Line

Modern PHP (8.4) is a **fundamentally different language** than PHP 5.x:

### What Changed

✅ **Type Safety**: Strict types, union types, generics-like features
✅ **Performance**: JIT compilation, 2-3x faster
✅ **Syntax**: Null safety, arrow functions, match expressions
✅ **OOP**: Constructor promotion, property hooks, readonly
✅ **Functional**: First-class callables, collections API
✅ **Tooling**: Better IDEs, static analysis, great ecosystem

### What's the Same

❌ **Syntax**: Still uses `$`, `->`, semicolons
❌ **Legacy**: Backwards compatibility can be messy
❌ **Reputation**: PHP's bad reputation persists

## Practical Example: Side-by-Side

Let's build a simple User service in both languages:

### Ruby Version

```ruby
class UserService
  attr_reader :repository, :mailer

  def initialize(repository:, mailer:)
    @repository = repository
    @mailer = mailer
  end

  def create_user(name:, email:, role: :user)
    user = repository.create(
      name: name.strip,
      email: email.downcase,
      role: role
    )

    mailer.send_welcome(user) if user.persisted?

    user
  end

  def active_users
    repository
      .where(active: true)
      .order(created_at: :desc)
  end
end
```

### PHP 8.4 Version

```php
# filename: user-service-example.php
<?php
declare(strict_types=1);

class UserService
{
    public function __construct(
        private UserRepository $repository,
        private Mailer $mailer,
    ) {}

    public function createUser(
        string $name,
        string $email,
        Role $role = Role::User,
    ): User {
        $user = $this->repository->create([
            'name' => trim($name),
            'email' => strtolower($email),
            'role' => $role,
        ]);

        if ($user->exists) {
            $this->mailer->sendWelcome($user);
        }

        return $user;
    }

    public function activeUsers(): Collection
    {
        return $this->repository
            ->where('active', true)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
```

**Key Differences:**

1. PHP has explicit type declarations
2. PHP uses enums for role (type-safe)
3. PHP has constructor promotion (less boilerplate)
4. Both achieve the same result
5. PHP catches more errors at compile-time

## Should Ruby Developers Learn Modern PHP?

**Yes, if you want to:**

- ✅ Expand your toolkit with a faster, type-safe language
- ✅ Work with Laravel (excellent framework)
- ✅ Take advantage of ubiquitous PHP hosting
- ✅ Reduce infrastructure costs with better performance
- ✅ Learn from a different approach to web development

**No, if you:**

- ❌ Strongly prefer Ruby's syntax aesthetics
- ❌ Are perfectly productive with Rails
- ❌ Don't want to learn new patterns
- ❌ Have no business reason to switch

**The Reality:**
Learning modern PHP and Laravel **makes you a better developer** by exposing you to different solutions to the same problems. Many patterns transfer back to Ruby!

## Practice Exercises

### Exercise 1: Type Safety

Convert this Ruby code to modern PHP with full type safety:

```ruby
def calculate_total(items, discount = 0, tax_rate = 0.1)
  subtotal = items.sum { |item| item[:price] * item[:quantity] }
  after_discount = subtotal * (1 - discount)
  after_discount * (1 + tax_rate)
end
```

<details>
<summary>Solution</summary>

```php
<?php
declare(strict_types=1);

function calculateTotal(
    array $items,
    float $discount = 0.0,
    float $taxRate = 0.1,
): float {
    $subtotal = array_reduce(
        $items,
        fn($sum, $item) => $sum + ($item['price'] * $item['quantity']),
        0.0
    );

    $afterDiscount = $subtotal * (1 - $discount);
    return $afterDiscount * (1 + $taxRate);
}

// Or with PHP 8.4 array functions
function calculateTotal(
    array $items,
    float $discount = 0.0,
    float $taxRate = 0.1,
): float {
    $subtotal = collect($items)
        ->sum(fn($item) => $item['price'] * $item['quantity']);

    $afterDiscount = $subtotal * (1 - $discount);
    return $afterDiscount * (1 + $taxRate);
}
```

</details>

### Exercise 2: Property Hooks

Create a User class with property hooks that:

- Capitalizes name on set
- Lowercases email on set
- Has a computed `fullName` property

<details>
<summary>Solution</summary>

```php
<?php
class User
{
    public string $firstName {
        set(string $value) {
            $this->firstName = ucfirst(strtolower(trim($value)));
        }
    }

    public string $lastName {
        set(string $value) {
            $this->lastName = ucfirst(strtolower(trim($value)));
        }
    }

    public string $email {
        set(string $value) {
            $this->email = strtolower(trim($value));
        }
    }

    public string $fullName {
        get => "{$this->firstName} {$this->lastName}";
    }
}

$user = new User();
$user->firstName = "  JOHN  ";
$user->lastName = "  DOE  ";
$user->email = "  John.Doe@EXAMPLE.com  ";

echo $user->fullName;  // "John Doe"
echo $user->email;     // "john.doe@example.com"
```

</details>

### Exercise 3: Enums with Methods

Create a Status enum with colors and a method to check if public:

<details>
<summary>Solution</summary>

```php
<?php
enum Status: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    public function color(): string
    {
        return match($this) {
            self::Draft => 'yellow',
            self::Published => 'green',
            self::Archived => 'gray',
        };
    }

    public function isPublic(): bool
    {
        return $this === self::Published;
    }

    public static function fromString(string $value): self
    {
        return match(strtolower($value)) {
            'draft' => self::Draft,
            'published' => self::Published,
            'archived' => self::Archived,
            default => throw new \ValueError("Invalid status: $value"),
        };
    }
}

// Usage
$status = Status::Published;
echo $status->color();      // "green"
echo $status->isPublic();   // true

$status2 = Status::fromString('draft');
echo $status2->value;       // "draft"
```

</details>

## Wrap-up

Congratulations! You've completed a comprehensive tour of modern PHP. Here's what you've accomplished:

✓ **Understood PHP's Evolution** - From PHP 5.x to PHP 8.4's modern features
✓ **Compared Ruby and PHP** - Side-by-side syntax comparisons for every major feature
✓ **Learned Type Safety** - How strict types, union types, and enums work in PHP
✓ **Explored Modern Syntax** - Property hooks, match expressions, named arguments, and more
✓ **Discovered Performance** - JIT compilation and real-world performance benefits
✓ **Practiced Translation** - Converted Ruby code to modern PHP in exercises
✓ **Built Mental Models** - Understand how to think in PHP when coming from Ruby

### Key Takeaways

1. **Modern PHP is Nothing Like Old PHP** - PHP 8.4 is a modern, type-safe language
2. **Type Safety Built-In** - Strict types catch errors at compile-time
3. **Performance Matters** - JIT compilation provides 2-3x speed improvements
4. **Ruby-Inspired Features** - Collections, conventions, developer happiness
5. **Unique Innovations** - Property hooks, enums, attributes go beyond Ruby
6. **Deployment Simplicity** - No application server needed
7. **Cost Effective** - Better performance = fewer servers = lower costs

### What's Next?

Now that you understand modern PHP's capabilities, we'll dive into Laravel's developer experience in the next chapter. You'll discover how Laravel takes these modern PHP features and builds an incredibly productive framework on top of them.

---

::: tip Continue Learning
Move on to [Chapter 03: Laravel Developer Experience](/series/rails-developers-love-laravel/chapters/03-laravel-developer-experience) to explore Artisan, Tinker, and Laravel's productivity tools.
:::


## Further Reading

- [PHP 8.4 Release Notes](https://www.php.net/releases/8.4/en.php) — Official PHP 8.4 release announcement and feature list
- [PHP The Right Way](https://phptherightway.com/) — Modern PHP best practices, coding standards, and community resources
- [PHP Type System Documentation](https://www.php.net/manual/en/language.types.php) — Comprehensive guide to PHP's type system
- [PHP Attributes RFC](https://wiki.php.net/rfc/attributes_v2) — Technical details on PHP 8.0 attributes
- [PHP JIT Compilation](https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.jit) — Configuration and optimization guide for JIT
- [PSR Standards](https://www.php-fig.org/psr/) — PHP Framework Interop Group standards (PSR-1, PSR-12 for coding standards)
- [Laravel Collections Documentation](https://laravel.com/docs/collections) — Laravel's collection API (Ruby Enumerable-inspired)


<style>
.comparison-table {
  width: 100%;
  border-collapse: collapse;
  margin: 1.5rem 0;
}

.comparison-table th,
.comparison-table td {
  border: 1px solid #e5e7eb;
  padding: 0.75rem;
  text-align: left;
}

.comparison-table th {
  background: #f9fafb;
  font-weight: 600;
}

.feature-box {
  background: #f0fdf4;
  border-left: 4px solid #10b981;
  padding: 1rem;
  margin: 1rem 0;
  border-radius: 4px;
}

.performance-box {
  background: #eff6ff;
  border-left: 4px solid #3b82f6;
  padding: 1rem;
  margin: 1rem 0;
  border-radius: 4px;
}
</style>
