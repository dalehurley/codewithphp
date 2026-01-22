---
title: "04: PHP Syntax & Language Differences for Rails Devs"
description: "Learn PHP syntax from a Rails perspective-variables, functions, classes, and the patterns you already know with new syntax"
sidebar:
  label: "04: PHP Syntax & Language Differences for Rails Devs"
  order: 4
  badge:
    text: Intermediate
    variant: caution
---


![PHP Syntax & Language Differences for Rails Devs](/images/rails-developers-love-laravel/chapter-04-php-syntax-for-rails-devs-hero-full.webp)

# 04: PHP Syntax & Language Differences for Rails Devs <span class="difficulty-badge difficulty-intermediate">Intermediate</span>

## Overview

When you start writing PHP code, you'll immediately notice syntax differences from Ruby. The concepts are the same, but PHP uses different symbols, keywords, and conventions. If you're comfortable with Ruby, you already understand variables, methods, classes, modules, and blocks. PHP does all of these things too—just with different syntax.

This chapter shows you Ruby code you know, then demonstrates the PHP equivalent side-by-side. By the end, you'll be able to read and write PHP code confidently, translating your Ruby knowledge into PHP syntax.

## Prerequisites

Before starting this chapter, you should have:

- Completion of [Chapter 03: Laravel's Developer Experience](/series/rails-developers-love-laravel/chapters/03-laravel-developer-experience) or equivalent understanding
- Familiarity with Ruby syntax (variables, methods, classes, blocks)
- PHP 8.4+ installed (optional for this chapter, but recommended)
- **Estimated Time**: ~45-60 minutes

**Verify your setup (optional):**

```bash
# Check PHP version if you have it installed
php --version
```

## What You'll Build

By the end of this chapter, you will have:

- A comprehensive syntax comparison reference between Ruby and PHP
- Understanding of PHP's variable system, type declarations, and modern features
- Ability to convert Ruby code patterns to PHP equivalents
- Knowledge of Laravel Collections as a Ruby-like alternative to PHP arrays
- A working PHP class implementation converted from Ruby

## Objectives

By completing this chapter, you will:

- Understand PHP's syntax differences from Ruby (variables, functions, classes)
- Learn PHP's type system and how it compares to Ruby's dynamic typing
- Master PHP arrays and Laravel Collections for Ruby-like enumerable operations
- Recognize modern PHP 8.4 features that feel familiar to Ruby developers
- Understand advanced PHP features: variadic functions, references, magic methods, and autoloading
- Convert Ruby code patterns to PHP equivalents confidently
- Identify common gotchas when transitioning from Ruby to PHP

## What You'll Learn

- PHP's basic syntax compared to Ruby
- Variable declarations and types
- Functions vs methods
- Object-oriented PHP (classes, interfaces, traits)
- PHP's type system (gradual typing)
- Arrays and collections
- String handling
- Error handling
- Modern PHP 8.4 features
- Advanced features: variadic functions, references, magic methods, autoloading

## 📦 Code Samples

Ready-to-run PHP syntax examples:

- **[PHP Syntax Comparisons](https://github.com/dalehurley/codewithphp/blob/main/code/rails-developers-love-laravel/chapter-04/SyntaxComparisons.php)** — Side-by-side comparisons of PHP vs Ruby syntax with working examples

Run locally:

```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/rails-developers-love-laravel/chapter-04

# Run the executable example
php SyntaxComparisons.php
```

Requires PHP 8.4+

## Quick Syntax Comparison

| Feature          | Ruby                              | PHP                                    |
| ---------------- | --------------------------------- | -------------------------------------- |
| **Variables**    | `name = "John"`                   | `$name = "John";`                      |
| **Constants**    | `MAX_SIZE = 100`                  | `const MAX_SIZE = 100;` or `define()` |
| **Comments**     | `# comment`                       | `// comment` or `/* block */`          |
| **String interp**| `"Hello #{name}"`                 | `"Hello {$name}"` or `"Hello $name"`   |
| **Arrays**       | `items = [1, 2, 3]`               | `$items = [1, 2, 3];`                  |
| **Hashes/Arrays**| `user = {name: "John"}`           | `$user = ['name' => 'John'];`          |
| **Functions**    | `def greet(name)`                 | `function greet($name)`                |
| **Methods**      | `def greet(name)...end`           | `public function greet($name) { }`     |
| **Conditionals** | `if condition...end`              | `if ($condition) { }`                  |
| **Loops**        | `items.each do \|item\|`          | `foreach ($items as $item)`            |
| **Classes**      | `class User`                      | `class User`                           |
| **Inheritance**  | `class Admin < User`              | `class Admin extends User`             |
| **Modules**      | `module Searchable`               | `trait Searchable` (similar)           |
| **Variadic**     | `def method(*args)`               | `function method(...$args)`            |
| **Nil/Null**     | `nil`                             | `null`                                 |

## 1. Variables

### Ruby Variables

```ruby
# Local variable
name = "John"
age = 30

# Instance variable
@name = "John"
@age = 30

# Class variable
@@count = 0

# Global variable (rare)
$global_config = {}

# Constants
MAX_SIZE = 100
API_KEY = "secret"
```

### PHP Variables

```php
# filename: variables.php
<?php

declare(strict_types=1);

// All variables start with $
$name = "John";
$age = 30;

// PHP has no @ or @@ prefixes
// Instance variables are defined in class properties
class User {
    private $name;  // Instance variable
    private static $count = 0;  // Class variable
}

// Global variable (avoid)
global $globalConfig;
$globalConfig = [];

// Constants
const MAX_SIZE = 100;
define('API_KEY', 'secret');  // Old style
```

::: tip Key Difference: $ Prefix
In PHP, ALL variables must start with `$`. There's no distinction between local, instance, or class variables via prefixes like Ruby's `@` and `@@`.
:::

## 2. Strings

### Ruby Strings

```ruby
# Single vs double quotes
name = 'John'
greeting = "Hello, #{name}!"  # Interpolation

# Concatenation
full_name = first_name + ' ' + last_name

# Multiline
text = <<~HEREDOC
  This is a
  multiline string
HEREDOC

# String methods
name.upcase          # => "JOHN"
name.downcase        # => "john"
name.length          # => 4
"  trim  ".strip     # => "trim"
"a,b,c".split(',')   # => ["a", "b", "c"]
```

### PHP Strings

```php
# filename: strings.php
<?php

declare(strict_types=1);

// Single quotes (no interpolation)
$name = 'John';

// Double quotes (with interpolation)
$greeting = "Hello, $name!";
$greeting = "Hello, {$name}!";  // Clearer with braces

// Concatenation with .
$fullName = $firstName . ' ' . $lastName;

// Multiline (heredoc)
$text = <<<HEREDOC
This is a
multiline string
HEREDOC;

// String functions (note: functions, not methods)
strtoupper($name);           // => "JOHN"
strtolower($name);           // => "john"
strlen($name);               // => 4
trim("  trim  ");            // => "trim"
explode(',', "a,b,c");       // => ["a", "b", "c"]

// Modern PHP: String helper methods
use Illuminate\Support\Str;

Str::upper($name);           // => "JOHN"
Str::lower($name);           // => "john"
Str::slug('Hello World');    // => "hello-world"
```

::: tip String Functions vs Methods
Ruby uses methods (`string.upcase`), while PHP historically uses functions (`strtoupper($string)`). Laravel provides modern `Str` helper methods that feel more like Ruby.
:::

## 3. Arrays and Hashes

### Ruby Arrays and Hashes

```ruby
# Array
fruits = ['apple', 'banana', 'cherry']
fruits[0]           # => "apple"
fruits << 'date'    # Add element
fruits.length       # => 4

# Iteration
fruits.each do |fruit|
  puts fruit
end

fruits.map { |f| f.upcase }
fruits.select { |f| f.start_with?('a') }

# Hash
user = {
  name: 'John',
  age: 30,
  email: 'john@example.com'
}

user[:name]         # => "John"
user[:role] = 'admin'

# Hash iteration
user.each do |key, value|
  puts "#{key}: #{value}"
end
```

### PHP Arrays (Unified)

```php
# filename: arrays.php
<?php

declare(strict_types=1);

// Indexed array (like Ruby array)
$fruits = ['apple', 'banana', 'cherry'];
$fruits[0];              // => "apple"
$fruits[] = 'date';      // Add element
count($fruits);          // => 4

// Iteration
foreach ($fruits as $fruit) {
    echo $fruit;
}

// Array functions
array_map(fn($f) => strtoupper($f), $fruits);
array_filter($fruits, fn($f) => str_starts_with($f, 'a'));

// Associative array (like Ruby hash)
$user = [
    'name' => 'John',
    'age' => 30,
    'email' => 'john@example.com'
];

$user['name'];           // => "John"
$user['role'] = 'admin';

// Array iteration
foreach ($user as $key => $value) {
    echo "$key: $value";
}
```

### Laravel Collections (Ruby-like)

```php
# filename: collections.php
<?php

declare(strict_types=1);

use Illuminate\Support\Collection;

// Collections feel like Ruby enumerables
$fruits = collect(['apple', 'banana', 'cherry']);

$fruits->first();                    // => "apple"
$fruits->last();                     // => "cherry"
$fruits->count();                    // => 3
$fruits->map(fn($f) => strtoupper($f));
$fruits->filter(fn($f) => strlen($f) > 5);
$fruits->contains('apple');          // => true
$fruits->pluck('name');
$fruits->sort();
$fruits->reverse();

// Chaining (like Ruby)
$result = collect($users)
    ->filter(fn($u) => $u->active)
    ->map(fn($u) => $u->name)
    ->sort()
    ->values();
```

::: tip Laravel Collections
Laravel's Collection class provides Ruby-like enumerable methods (`map`, `filter`, `pluck`, etc.), making array manipulation feel familiar.
:::

## 4. Functions and Methods

### Ruby Methods

```ruby
# Simple method
def greet(name)
  "Hello, #{name}!"
end

# Method with default argument
def greet(name = "World")
  "Hello, #{name}!"
end

# Method with keyword arguments
def create_user(name:, email:, role: 'user')
  { name: name, email: email, role: role }
end

create_user(name: 'John', email: 'john@example.com')

# Block parameter
def process_items(items, &block)
  items.each(&block)
end

process_items([1, 2, 3]) { |n| puts n * 2 }

# Return (implicit)
def add(a, b)
  a + b  # No return needed
end
```

### PHP Functions

```php
# filename: functions.php
<?php

declare(strict_types=1);

// Simple function
function greet(string $name): string
{
    return "Hello, $name!";
}

// Function with default argument
function greet(string $name = "World"): string
{
    return "Hello, $name!";
}

// Named arguments (PHP 8+)
function createUser(string $name, string $email, string $role = 'user'): array
{
    return ['name' => $name, 'email' => $email, 'role' => $role];
}

createUser(name: 'John', email: 'john@example.com');

// Closure (like Ruby blocks)
function processItems(array $items, callable $callback)
{
    array_map($callback, $items);
}

processItems([1, 2, 3], fn($n) => $n * 2);

// Return (explicit)
function add(int $a, int $b): int
{
    return $a + $b;  // Return required
}

// Arrow function (PHP 7.4+)
$add = fn(int $a, int $b): int => $a + $b;
```

::: tip Return Statements
Unlike Ruby (implicit return), PHP requires explicit `return` statements. Arrow functions (`fn() => `) have implicit returns like Ruby lambdas.
:::

## 5. Classes and OOP

### Ruby Classes

```ruby
class User
  attr_accessor :name, :email
  attr_reader :id

  def initialize(name, email)
    @name = name
    @email = email
    @id = generate_id
  end

  def greet
    "Hello, I'm #{@name}"
  end

  def self.find(id)
    # Class method
  end

  private

  def generate_id
    SecureRandom.uuid
  end
end

# Usage
user = User.new('John', 'john@example.com')
puts user.greet
```

### PHP Classes

```php
# filename: classes.php
<?php

declare(strict_types=1);

class User
{
    public string $name;
    public string $email;
    private string $id;

    public function __construct(string $name, string $email)
    {
        $this->name = $name;
        $this->email = $email;
        $this->id = $this->generateId();
    }

    public function greet(): string
    {
        return "Hello, I'm {$this->name}";
    }

    public static function find(string $id): ?self
    {
        // Static method (like Ruby class method)
    }

    private function generateId(): string
    {
        return uniqid();
    }
}

// Usage
$user = new User('John', 'john@example.com');
echo $user->greet();
```

### Modern PHP 8+ Features

```php
# filename: modern-php.php
<?php

declare(strict_types=1);

// Constructor property promotion (PHP 8+)
class User
{
    public function __construct(
        public string $name,
        public string $email,
        private string $id = ''
    ) {
        $this->id = $this->id ?: uniqid();
    }

    public function greet(): string
    {
        return "Hello, I'm {$this->name}";
    }
}

// Readonly properties (PHP 8.1+)
class User
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
    ) {}
}

// Property hooks (PHP 8.4+)
class User
{
    public string $name {
        set(string $value) {
            $this->name = ucfirst($value);
        }
    }
}
```

## 6. Inheritance and Interfaces

### Ruby

```ruby
# Inheritance
class Admin < User
  def ban_user(user)
    # Admin-specific method
  end
end

# Modules (mixins)
module Searchable
  def search(query)
    # Search implementation
  end
end

class User
  include Searchable
end

# Duck typing (no interfaces)
def process(object)
  object.save if object.respond_to?(:save)
end
```

### PHP

```php
# filename: inheritance.php
<?php

declare(strict_types=1);

// Inheritance
class Admin extends User
{
    public function banUser(User $user)
    {
        // Admin-specific method
    }
}

// Interfaces
interface Searchable
{
    public function search(string $query): array;
}

class User implements Searchable
{
    public function search(string $query): array
    {
        // Implementation required
    }
}

// Traits (like Ruby mixins)
trait Searchable
{
    public function search(string $query): array
    {
        // Shared implementation
    }
}

class User
{
    use Searchable;
}
```

::: tip Traits vs Modules
PHP traits are similar to Ruby modules/mixins. Use `use TraitName` instead of Ruby's `include ModuleName`.
:::

## 7. Type Declarations

Ruby is dynamically typed. Modern PHP supports gradual typing:

### Ruby (No Types)

```ruby
def add(a, b)
  a + b
end

class User
  attr_accessor :name

  def initialize(name)
    @name = name
  end
end
```

### PHP (With Types)

```php
# filename: types.php
<?php

declare(strict_types=1);

// Function type hints (PHP 7+)
function add(int $a, int $b): int
{
    return $a + $b;
}

// Property types (PHP 7.4+)
class User
{
    public string $name;
    private int $age;
    private ?string $email = null;  // Nullable

    public function __construct(string $name, int $age)
    {
        $this->name = $name;
        $this->age = $age;
    }

    public function getName(): string
    {
        return $this->name;
    }
}

// Union types (PHP 8+)
function process(int|string $value): bool|string
{
    // Can accept int or string, returns bool or string
}

// Intersection types (PHP 8.1+)
function save(Countable&Traversable $data): void
{
    // Must implement both interfaces
}
```

::: tip Gradual Typing
PHP's type system is optional but recommended. It catches bugs early and improves IDE autocomplete-unlike Ruby's dynamic typing.
:::

## 8. Conditionals and Loops

### Ruby

```ruby
# If/elsif/else
if age >= 18
  "Adult"
elsif age >= 13
  "Teen"
else
  "Child"
end

# Unless
unless logged_in?
  redirect_to login_path
end

# Ternary
status = active? ? 'Active' : 'Inactive'

# Case/when
case role
when 'admin'
  # Admin logic
when 'user'
  # User logic
else
  # Default
end

# Loops
[1, 2, 3].each do |n|
  puts n
end

10.times do |i|
  puts i
end

while condition
  # Loop
end
```

### PHP

```php
# filename: conditionals.php
<?php

declare(strict_types=1);

// If/elseif/else
if ($age >= 18) {
    echo "Adult";
} elseif ($age >= 13) {
    echo "Teen";
} else {
    echo "Child";
}

// PHP has no "unless" - use negation
if (!$loggedIn) {
    header('Location: /login');
}

// Ternary
$status = $active ? 'Active' : 'Inactive';

// Switch/case
switch ($role) {
    case 'admin':
        // Admin logic
        break;
    case 'user':
        // User logic
        break;
    default:
        // Default
}

// Match expression (PHP 8+) - like Ruby case
$result = match ($role) {
    'admin' => 'Administrator',
    'user' => 'Regular User',
    default => 'Guest'
};

// Loops
foreach ([1, 2, 3] as $n) {
    echo $n;
}

for ($i = 0; $i < 10; $i++) {
    echo $i;
}

while ($condition) {
    // Loop
}
```

## 9. Null Safety

### Ruby

```ruby
# Safe navigation operator
user&.profile&.name

# Nil coalescing
name = user.name || 'Guest'
```

### PHP

```php
# filename: null-safety.php
<?php

declare(strict_types=1);

// Null-safe operator (PHP 8+)
$user?->profile?->name;

// Null coalescing operator (PHP 7+)
$name = $user->name ?? 'Guest';

// Null coalescing assignment (PHP 7.4+)
$data['key'] ??= 'default';
```

## 10. Error Handling

### Ruby

```ruby
begin
  # Risky operation
  file = File.open('data.txt')
rescue FileNotFoundError => e
  puts "File not found: #{e.message}"
rescue => e
  puts "Error: #{e.message}"
ensure
  file&.close
end

# Raising errors
raise ArgumentError, "Invalid input"
```

### PHP

```php
# filename: error-handling.php
<?php

declare(strict_types=1);

try {
    // Risky operation
    $file = fopen('data.txt', 'r');
} catch (FileNotFoundException $e) {
    echo "File not found: {$e->getMessage()}";
} catch (Exception $e) {
    echo "Error: {$e->getMessage()}";
} finally {
    if ($file) {
        fclose($file);
    }
}

// Throwing exceptions
throw new InvalidArgumentException("Invalid input");
```

## 11. Namespaces

### Ruby Modules

```ruby
module Blog
  module Models
    class Post
    end
  end
end

# Usage
post = Blog::Models::Post.new
```

### PHP Namespaces

```php
# filename: namespaces.php
<?php

declare(strict_types=1);

namespace Blog\Models;

class Post
{
}

// Usage
$post = new \Blog\Models\Post();

// Or with use statement
use Blog\Models\Post;

$post = new Post();
```

## 12. Modern PHP 8.4 Features

PHP 8.4 introduces several Ruby-inspired features:

### Property Hooks

```php
# filename: property-hooks.php
<?php

declare(strict_types=1);

class User
{
    // Like Ruby's attr_accessor with custom logic
    public string $name {
        get => ucfirst($this->name);
        set(string $value) {
            if (strlen($value) < 2) {
                throw new ValueError('Name too short');
            }
            $this->name = $value;
        }
    }
}
```

### Asymmetric Visibility

```php
# filename: asymmetric-visibility.php
<?php

declare(strict_types=1);

class User
{
    // Public read, private write (like attr_reader with custom setter)
    public private(set) string $id;

    public function __construct()
    {
        $this->id = uniqid();  // Can set internally
    }
}

$user = new User();
echo $user->id;      // OK: Can read
$user->id = 'new';   // Error: Cannot write
```

## 13. Advanced PHP Features

### Variadic Functions (Splat Operator)

Ruby uses `*args` for variable arguments. PHP uses `...$args`:

#### Ruby

```ruby
# Ruby - splat operator
def greet(*names)
  names.each { |name| puts "Hello, #{name}!" }
end

greet('John', 'Jane', 'Bob')
# => Hello, John!
# => Hello, Jane!
# => Hello, Bob!

# With keyword arguments
def create_user(**attributes)
  User.new(attributes)
end

create_user(name: 'John', email: 'john@example.com')
```

#### PHP

```php
# filename: variadic.php
<?php

declare(strict_types=1);

// PHP 5.6+ - variadic arguments
function greet(string ...$names): void
{
    foreach ($names as $name) {
        echo "Hello, $name!\n";
    }
}

greet('John', 'Jane', 'Bob');
// => Hello, John!
// => Hello, Jane!
// => Hello, Bob!

// With named arguments (PHP 8+)
function createUser(string $name, string $email, string ...$roles): array
{
    return [
        'name' => $name,
        'email' => $email,
        'roles' => $roles
    ];
}

createUser('John', 'john@example.com', 'admin', 'editor');
// => ['name' => 'John', 'email' => 'john@example.com', 'roles' => ['admin', 'editor']]

// Unpacking arrays (like Ruby's *array)
$names = ['John', 'Jane', 'Bob'];
greet(...$names);  // Unpacks array as arguments
```

::: tip Variadic Functions
PHP's `...$args` works like Ruby's `*args`, but with type safety. Use `...$array` to unpack arrays as function arguments.
:::

### References

PHP supports pass-by-reference, which Ruby doesn't have. This is useful for modifying variables in place:

#### Ruby

```ruby
# Ruby - no pass-by-reference
def increment(value)
  value += 1  # Creates new value, doesn't modify original
end

x = 5
increment(x)
puts x  # => 5 (unchanged)
```

#### PHP

```php
# filename: references.php
<?php

declare(strict_types=1);

// PHP - pass-by-reference with &
function increment(int &$value): void
{
    $value += 1;  // Modifies original variable
}

$x = 5;
increment($x);
echo $x;  // => 6 (modified)

// Reference assignment
$original = 'Hello';
$reference = &$original;  // Both point to same value
$reference = 'World';
echo $original;  // => "World"

// Array references
$array = [1, 2, 3];
foreach ($array as &$value) {
    $value *= 2;  // Modifies original array
}
// => [2, 4, 6]
```

::: warning References
Use references sparingly. They can make code harder to understand. Laravel rarely uses them, but you'll see `&` in some PHP internals.
:::

### Magic Methods

PHP's magic methods are crucial for understanding Laravel, especially Eloquent models. They're similar to Ruby's `method_missing`:

#### Ruby

```ruby
# Ruby - method_missing
class User
  def method_missing(name, *args)
    if name.to_s.start_with?('find_by_')
      attribute = name.to_s.sub('find_by_', '')
      where(attribute => args.first).first
    else
      super
    end
  end

  def respond_to_missing?(name, include_private = false)
    name.to_s.start_with?('find_by_') || super
  end
end

user = User.find_by_email('john@example.com')
```

#### PHP Magic Methods

```php
# filename: magic-methods.php
<?php

declare(strict_types=1);

class User
{
    private array $attributes = [];

    // __get - called when accessing undefined property
    public function __get(string $name): mixed
    {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }
        return null;
    }

    // __set - called when setting undefined property
    public function __set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    // __call - called when calling undefined method
    public function __call(string $name, array $arguments): mixed
    {
        if (str_starts_with($name, 'findBy')) {
            $attribute = lcfirst(substr($name, 6));  // Remove "findBy"
            // Simulate database lookup
            return $this->attributes[$attribute] ?? null;
        }
        throw new BadMethodCallException("Method $name does not exist");
    }

    // __callStatic - called when calling undefined static method
    public static function __callStatic(string $name, array $arguments): mixed
    {
        if ($name === 'findByEmail') {
            // Simulate static lookup
            return new self();
        }
        throw new BadMethodCallException("Static method $name does not exist");
    }

    // __toString - called when object is used as string
    public function __toString(): string
    {
        return $this->attributes['name'] ?? 'Unknown User';
    }

    // __invoke - makes object callable
    public function __invoke(): string
    {
        return "User: {$this->attributes['name']}";
    }
}

// Usage
$user = new User();
$user->name = 'John';  // Calls __set
echo $user->name;      // Calls __get => "John"

$found = $user->findByEmail('john@example.com');  // Calls __call
echo $user;  // Calls __toString => "John"

$callable = new User();
$callable->name = 'Jane';
echo $callable();  // Calls __invoke => "User: Jane"
```

#### Laravel Eloquent Uses Magic Methods

```php
# filename: eloquent-magic.php
<?php

// Eloquent models use __get and __set for database attributes
$user = User::find(1);

// These use __get/__set internally:
$name = $user->name;           // __get('name')
$user->email = 'new@example.com';  // __set('email', '...')

// Dynamic relationships use __call:
$posts = $user->posts();       // __call('posts')
$comments = $user->comments(); // __call('comments')
```

::: tip Magic Methods in Laravel
Eloquent models rely heavily on `__get`, `__set`, and `__call` for dynamic attribute access and relationship methods. Understanding these helps debug Laravel code.
:::

### Autoloading

PHP's autoloading is how classes are automatically loaded. Laravel uses Composer's PSR-4 autoloading:

#### Ruby

```ruby
# Ruby - require files manually or use autoload
require_relative 'models/user'
require_relative 'models/post'

# Or with autoload
autoload :User, 'models/user'
autoload :Post, 'models/post'
```

#### PHP Autoloading

```php
# filename: autoloading.php
<?php

// Old way - manual require
require_once 'User.php';
require_once 'Post.php';

// Modern way - Composer autoloading (PSR-4)
// composer.json:
// {
//     "autoload": {
//         "psr-4": {
//             "App\\": "app/"
//         }
//     }
// }

// Then just use classes:
use App\Models\User;
use App\Models\Post;

$user = new User();  // Automatically loads app/Models/User.php
$post = new Post();  // Automatically loads app/Models/Post.php
```

#### How PSR-4 Autoloading Works

```php
# Laravel's namespace structure:
# App\Models\User → app/Models/User.php
# App\Http\Controllers\UserController → app/Http/Controllers/UserController.php

// No require statements needed!
namespace App\Models;

class User
{
    // Class automatically loaded when used
}
```

#### Custom Autoloader (Advanced)

```php
# filename: custom-autoloader.php
<?php

// Simple autoloader example
spl_autoload_register(function ($class) {
    // Convert namespace to file path
    $file = str_replace('\\', '/', $class) . '.php';
    
    if (file_exists($file)) {
        require_once $file;
    }
});

// Now you can use classes without require
use MyNamespace\MyClass;
$obj = new MyClass();
```

::: tip Composer Autoloading
Laravel uses Composer's PSR-4 autoloading. Run `composer dump-autoload` after adding new classes. No `require` statements needed!
:::

## Common Gotchas for Rails Developers

### 1. Semicolons Required

```ruby
# Ruby - no semicolons
name = "John"
age = 30
```

```php
# filename: gotchas.php
<?php

declare(strict_types=1);

// PHP - semicolons required
$name = "John";
$age = 30;
```

### 2. Explicit Return

```ruby
# Ruby - implicit return
def add(a, b)
  a + b  # Returns automatically
end
```

```php
# filename: gotchas.php
<?php

declare(strict_types=1);

// PHP - explicit return
function add(int $a, int $b): int
{
    return $a + $b;  // Must use return
}
```

### 3. Array/Hash Distinction

```ruby
# Ruby - separate types
array = [1, 2, 3]
hash = {key: 'value'}
```

```php
# filename: gotchas.php
<?php

declare(strict_types=1);

// PHP - both are arrays
$array = [1, 2, 3];
$hash = ['key' => 'value'];

// But behave differently
echo $array[0];      // => 1
echo $hash['key'];   // => "value"
```

### 4. Method vs Function Calls

```ruby
# Ruby - no parentheses needed
puts "Hello"
user.save
```

```php
# filename: gotchas.php
<?php

declare(strict_types=1);

// PHP - parentheses always required
echo("Hello");  // or just: echo "Hello";
$user->save();  // Must have ()
```

## Summary Cheatsheet

| Concept | Ruby | PHP |
|---------|------|-----|
| **Variable** | `name = "John"` | `$name = "John";` |
| **String interpolation** | `"Hello #{name}"` | `"Hello $name"` |
| **Array** | `[1, 2, 3]` | `[1, 2, 3]` |
| **Hash/Assoc** | `{key: 'val'}` | `['key' => 'val']` |
| **Function** | `def name...end` | `function name() {}` |
| **Class** | `class Name...end` | `class Name {}` |
| **Inheritance** | `<` | `extends` |
| **Mixin** | `include Module` | `use Trait` |
| **Instance var** | `@var` | `$this->var` |
| **Class var** | `@@var` | `self::$var` |
| **Nil/Null** | `nil` | `null` |
| **True/False** | `true`/`false` | `true`/`false` |
| **Safe nav** | `&.` | `?->` |
| **Null coalesce** | `\|\|` | `??` |

## Exercises

### Exercise 1: Convert Ruby Class to PHP

**Goal**: Practice converting Ruby class patterns to modern PHP 8.4 syntax

Convert this Ruby class to PHP:

```ruby
class BlogPost
  attr_accessor :title, :body
  attr_reader :slug

  def initialize(title, body)
    @title = title
    @body = body
    @slug = title.downcase.gsub(' ', '-')
  end

  def publish
    @published_at = Time.now
  end

  def published?
    !@published_at.nil?
  end

  def self.recent(limit = 10)
    # Fetch recent posts
  end
end
```

**Requirements:**

- Create a file called `BlogPost.php`
- Use property types for all properties
- Use constructor property promotion (PHP 8+)
- Add type hints to all methods
- Implement a static method `recent()` with a default parameter
- Use PHP 8.4 features where appropriate

**Validation**: Test your implementation:

```php
# filename: test-blog-post.php
<?php

declare(strict_types=1);

require_once 'BlogPost.php';

// Create a new post
$post = new BlogPost('My First Post', 'This is the body content');

// Test properties
echo $post->title;  // Expected: "My First Post"
echo $post->slug;   // Expected: "my-first-post"

// Test publish method
$post->publish();
echo $post->published() ? 'Published' : 'Not published';  // Expected: "Published"

// Test static method
$recent = BlogPost::recent(5);
```

Expected output:

```
My First Post
my-first-post
Published
```

## Wrap-up

You've now learned the key syntax differences between Ruby and PHP:

- ✓ PHP variables use `$` prefix (unlike Ruby's `@` and `@@`)
- ✓ PHP requires semicolons and explicit `return` statements
- ✓ PHP uses `->` for method calls instead of `.`
- ✓ PHP arrays unify Ruby's arrays and hashes
- ✓ Laravel Collections provide Ruby-like enumerable methods
- ✓ Modern PHP 8.4 features (property hooks, asymmetric visibility) feel familiar
- ✓ PHP's gradual typing system catches bugs early
- ✓ Variadic functions (`...$args`) work like Ruby's `*args`
- ✓ Magic methods (`__get`, `__set`, `__call`) power Laravel's Eloquent models
- ✓ Composer autoloading eliminates the need for `require` statements

The concepts are the same—just different syntax. You already understand the patterns; now you know how PHP expresses them.

## Troubleshooting

### Error: "Parse error: syntax error, unexpected '$name'"

**Symptom**: `Parse error: syntax error, unexpected '$name' (T_VARIABLE)`

**Cause**: Missing `$` prefix on a variable or forgetting semicolon on previous line

**Solution**: Ensure all variables start with `$` and all statements end with semicolons:

```php
// Wrong
name = "John";

// Correct
$name = "John";
```

### Error: "Call to undefined function"

**Symptom**: `Fatal error: Uncaught Error: Call to undefined function strtoupper()`

**Cause**: Using Ruby-style method calls instead of PHP functions

**Solution**: PHP uses functions, not methods. Use function syntax:

```php
// Wrong (Ruby style)
$name->upcase();

// Correct (PHP style)
strtoupper($name);

// Or use Laravel's Str helper
use Illuminate\Support\Str;
Str::upper($name);
```

### Error: "Return value must be of type int, string returned"

**Symptom**: Type error when function return type doesn't match

**Cause**: PHP's type system is stricter than Ruby's dynamic typing

**Solution**: Ensure return types match function signatures:

```php
// Wrong
function add(int $a, int $b): int
{
    return "$a + $b";  // Returns string, not int
}

// Correct
function add(int $a, int $b): int
{
    return $a + $b;  // Returns int
}
```

### Problem: Array access returns null

**Symptom**: `$array[0]` returns `null` when you expect a value

**Cause**: Using hash-style access on indexed array or vice versa

**Solution**: Use correct array access syntax:

```php
// Indexed array
$fruits = ['apple', 'banana'];
echo $fruits[0];  // => "apple"

// Associative array (hash)
$user = ['name' => 'John'];
echo $user['name'];  // => "John"
// NOT: $user->name (that's for objects)
```

## Further Reading

- [PHP 8.4 Documentation](https://www.php.net/releases/8.4/en.php) — Official PHP 8.4 release notes and features
- [Laravel Collections Documentation](https://laravel.com/docs/collections) — Ruby-like enumerable methods for PHP arrays
- [PHP Type Declarations](https://www.php.net/manual/en/language.types.declarations.php) — Understanding PHP's type system
- [PSR-12 Coding Standard](https://www.php-fig.org/psr/psr-12/) — PHP coding standards for consistency
- [PHP The Right Way](https://phptherightway.com/) — Modern PHP best practices and patterns

## Next Steps

Now that you understand PHP syntax, you're ready to dive into Laravel's ORM:

---

::: tip Continue Learning
Move on to [Chapter 05: Eloquent ORM](/series/rails-developers-love-laravel/chapters/05-eloquent-orm) to learn how Laravel's ORM compares to ActiveRecord and how to work with databases in Laravel.
:::


