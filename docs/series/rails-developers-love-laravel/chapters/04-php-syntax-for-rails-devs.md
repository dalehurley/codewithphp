---
title: "PHP Syntax & Language Differences for Rails Devs"
description: Learn PHP syntax from a Rails perspective—variables, functions, classes, and the patterns you already know with new syntax.
series: rails-developers-love-laravel
chapter: 4
difficulty: Intermediate
tags: ["php", "ruby", "syntax", "comparison", "language"]
---

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/#choose-your-learning-path">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/rails-developers-love-laravel/">Rails to Laravel</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 04</span>
</div>

![PHP Syntax & Language Differences for Rails Devs](/images/rails-developers-love-laravel/chapter-04-php-syntax-for-rails-devs-hero-full.webp)

# Chapter 04: PHP Syntax & Language Differences for Rails Devs <span class="difficulty-badge difficulty-intermediate">Intermediate</span>

## Overview

When you start writing PHP code, you'll immediately notice syntax differences from Ruby. The concepts are the same, but PHP uses different symbols, keywords, and conventions. If you're comfortable with Ruby, you already understand variables, methods, classes, modules, and blocks. PHP does all of these things too—just with different syntax.

This chapter shows you Ruby code you know, then demonstrates the PHP equivalent side-by-side.

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
<?php
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
<?php
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
<?php
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
<?php
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
<?php
// Simple function
function greet($name)
{
    return "Hello, $name!";
}

// Function with default argument
function greet($name = "World")
{
    return "Hello, $name!";
}

// Named arguments (PHP 8+)
function createUser($name, $email, $role = 'user')
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
function add($a, $b)
{
    return $a + $b;  // Return required
}

// Arrow function (PHP 7.4+)
$add = fn($a, $b) => $a + $b;
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
<?php
class User
{
    public $name;
    public $email;
    private $id;

    public function __construct($name, $email)
    {
        $this->name = $name;
        $this->email = $email;
        $this->id = $this->generateId();
    }

    public function greet()
    {
        return "Hello, I'm {$this->name}";
    }

    public static function find($id)
    {
        // Static method (like Ruby class method)
    }

    private function generateId()
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
<?php
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
<?php
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
<?php
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
PHP's type system is optional but recommended. It catches bugs early and improves IDE autocomplete—unlike Ruby's dynamic typing.
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
<?php
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
<?php
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
<?php
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
<?php
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
<?php
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
<?php
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

## Common Gotchas for Rails Developers

### 1. Semicolons Required

```ruby
# Ruby - no semicolons
name = "John"
age = 30
```

```php
<?php
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
<?php
// PHP - explicit return
function add($a, $b)
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
<?php
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
<?php
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

## Practice Exercise

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

Try implementing this in PHP with:
- Property types
- Constructor property promotion
- Type-hinted methods
- A static method for `recent`

## Next Steps

Now that you understand PHP syntax, you're ready to explore Laravel's developer experience and tooling:

---

::: tip Continue Learning
Move on to [Chapter 03: Laravel's Developer Experience](/series/rails-developers-love-laravel/chapters/03-laravel-developer-experience) to learn about Artisan, migrations, and Laravel's productivity tools.
:::

<ProgressTracker seriesId="rails-developers-love-laravel" :totalChapters="11" title="Your Progress" />
