---
title: "04: The PHP Syntax & Language Differences for Python Devs"
description: "Learn PHP syntax differences from Python—variable prefixes, OOP, typing, namespaces—with side-by-side code examples."
series: "python-developers-love-php-laravel"
chapter: 4
order: 4
difficulty: "Intermediate"
prerequisites:
  - "/series/python-developers-love-php-laravel/chapters/03-laravel-developer-experience-productivity-tools"
---

![PHP Syntax & Language Differences](/images/python-developers-love-php-laravel/chapter-04-php-syntax-language-differences-for-python-devs-hero-full.webp)

# Chapter 04: The PHP Syntax & Language Differences for Python Devs

## Overview

In Chapter 03, you explored Laravel's developer experience tools—Artisan CLI, migrations, testing, and conventions. These tools feel familiar because they mirror Python's best practices. But when you start writing PHP code, you'll immediately notice syntax differences. The concepts are the same, but PHP uses different symbols, keywords, and conventions.

This chapter is about **bridging the syntax gap**. If you're comfortable with Python, you already understand variables, functions, classes, namespaces, and type hints. PHP does all of these things too—just with different syntax. We'll show you Python code you know, then demonstrate the PHP equivalent side-by-side.

By the end of this chapter, you'll understand PHP's variable prefixes (`$`), OOP syntax differences, type system variations, namespace conventions, and string handling. Most importantly, you'll recognize that PHP syntax, while different, follows logical patterns that become intuitive once you understand the "why" behind the differences.

## Prerequisites

Before starting this chapter, you should have:

- Completion of [Chapter 03](/series/python-developers-love-php-laravel/chapters/03-laravel-developer-experience-productivity-tools) or equivalent understanding of Laravel's developer tools
- Python experience (for comparisons)
- PHP 8.4+ installed and verified working
- Basic familiarity with Python type hints (PEP 484)
- Text editor or IDE ready for PHP development
- **Estimated Time**: ~95 minutes

**Verify your setup:**

```bash
# Check PHP version (should show PHP 8.4+)
php --version

# Expected output: PHP 8.4.x (or higher)

# Test PHP execution
php -r "echo 'PHP is working!\n';"

# Expected output: PHP is working!
```

## What You'll Build

By the end of this chapter, you will have:

- Side-by-side comparison examples (Python → PHP) for variables, types, strings, arrays, functions, operators, match expressions, OOP (including traits), namespaces, and error handling
- Understanding of PHP's `$` variable prefix and why it exists
- Knowledge of PHP type system syntax vs Python type hints
- Understanding of PHP OOP syntax differences (class definitions, properties, methods, visibility)
- Working PHP code examples demonstrating each syntax concept
- Ability to read and write PHP code with confidence
- Mental map connecting Python syntax patterns to PHP equivalents

## Quick Start

Want to see PHP syntax differences right away? Here's a quick comparison:

**Python:**

```python
from typing import Optional

def greet(name: str, age: Optional[int] = None) -> str:
    if age is None:
        return f"Hello, {name}!"
    return f"Hello, {name}! You are {age} years old."

class User:
    def __init__(self, name: str, email: str):
        self.name = name
        self.email = email
```

**PHP 8.4:**

```php
# filename: quickstart.php
<?php

declare(strict_types=1);

function greet(string $name, ?int $age = null): string
{
    if ($age === null) {
        return "Hello, {$name}!";
    }
    return "Hello, {$name}! You are {$age} years old.";
}

class User
{
    public function __construct(
        public string $name,
        public string $email
    ) {}
}
```

Notice the differences? PHP uses `$` for variables, `?int` for nullable types, `{$name}` for string interpolation, and constructor property promotion. Same concepts, different syntax! This chapter will show you all the syntax differences you need to know.

## Objectives

- Understand PHP's variable prefix (`$`) and assignment syntax
- Compare PHP type declarations with Python type hints
- Learn PHP string interpolation and concatenation differences
- Understand PHP array syntax vs Python lists and dictionaries
- Compare PHP function definitions with Python functions
- Master PHP operators (ternary, null coalescing) vs Python conditionals
- Understand PHP match expressions vs Python match statements
- Master PHP OOP syntax (classes, properties, methods, visibility, traits)
- Understand PHP namespaces vs Python modules
- Learn PHP error handling syntax vs Python exceptions

## Step 1: Variables and Variable Prefixes (~10 min)

### Goal

Understand PHP's most distinctive syntax feature: the `$` prefix for variables, and how variable assignment and scope compare to Python.

### Actions

1. **Variable Prefix Requirement**

   In PHP, every variable must be prefixed with `$`. This is PHP's way of distinguishing variables from keywords and function names:

   **Python:**

   ```python
   # filename: python-variables.py
   name = "Alice"
   age = 30
   is_active = True

   print(name)  # Output: Alice
   print(age)   # Output: 30
   ```

   **PHP:**

   ```php
   # filename: php-variables.php
   <?php

   $name = "Alice";
   $age = 30;
   $is_active = true;

   echo $name;  // Output: Alice
   echo $age;   // Output: 30
   ```

   Notice: PHP requires `$` before every variable name, even when referencing it. Python doesn't need any prefix.

2. **Variable Assignment and Reassignment**

   Both languages allow variable reassignment, but PHP requires the `$` prefix each time:

   **Python:**

   ```python
   # filename: python-assignment.py
   count = 10
   count = count + 5  # Reassign
   count += 5         # Shorthand
   ```

   **PHP:**

   ```php
   # filename: php-assignment.php
   <?php

   $count = 10;
   $count = $count + 5;  // Reassign
   $count += 5;           // Shorthand (same as Python)
   ```

3. **Constants**

   PHP uses `define()` or `const` for constants (similar to Python's `CONSTANT_NAME` convention):

   **Python:**

   ```python
   # filename: python-constants.py
   MAX_USERS = 100  # Convention: uppercase
   API_VERSION = "v1"
   ```

   **PHP:**

   ```php
   # filename: php-constants.php
   <?php

   define('MAX_USERS', 100);  // Function-based constant
   const API_VERSION = "v1";  // Class/file-level constant

   echo MAX_USERS;     // No $ prefix for constants
   echo API_VERSION;
   ```

   **Important**: Constants in PHP don't use `$`—only variables do.

### Expected Result

After this step, you understand:

- PHP requires `$` prefix for all variables (assignment and reference)
- Constants don't use `$` prefix
- Variable assignment syntax is otherwise similar to Python
- The `$` prefix helps PHP distinguish variables from keywords

### Why It Works

PHP's `$` prefix is a historical design choice that helps the parser distinguish variables from keywords and function names. While it might seem verbose at first, it actually makes code more readable once you're used to it—you can immediately spot variables vs constants vs functions. Modern PHP still requires it for consistency and backward compatibility.

### Troubleshooting

- **Error: "Undefined variable"** — You forgot the `$` prefix when referencing a variable. Check: `$name` not `name`.
- **Error: "Parse error: syntax error"** — You're missing `$` in variable assignment. Check: `$count = 10` not `count = 10`.
- **Constants showing as undefined** — Constants don't use `$`. Use `MAX_USERS` not `$MAX_USERS`.

## Step 2: Data Types and Type System (~12 min)

### Goal

Compare PHP's type declaration syntax with Python's type hints, understanding nullable types, union types, and type coercion differences.

### Actions

1. **Type Declarations vs Type Hints**

   PHP uses type declarations (enforced at runtime), while Python uses type hints (checked by type checkers):

   **Python (with type hints):**

   ```python
   # filename: python-types.py
   from typing import Optional, Union

   def calculate_total(price: float, tax: float) -> float:
       return price * (1 + tax)

   def get_user_name(user_id: Optional[int] = None) -> str:
       if user_id is None:
           return "Guest"
       return f"User {user_id}"
   ```

   **PHP 8.4:**

   ```php
   # filename: php-types.php
   <?php

   declare(strict_types=1);

   function calculateTotal(float $price, float $tax): float
   {
       return $price * (1 + $tax);
   }

   function getUserName(?int $user_id = null): string
   {
       if ($user_id === null) {
           return "Guest";
       }
       return "User {$user_id}";
   }
   ```

   **Key differences:**

   - PHP: `float $price` (type before variable)
   - Python: `price: float` (variable before type)
   - PHP: `?int` for nullable (shorthand for `int|null`)
   - Python: `Optional[int]` or `int | None`

2. **Nullable Types**

   PHP uses `?` prefix for nullable types (PHP 7.1+):

   **Python:**

   ```python
   # filename: python-nullable.py
   from typing import Optional

   def process_data(data: Optional[str] = None) -> str:
       if data is None:
           return "No data"
       return data.upper()
   ```

   **PHP:**

   ```php
   # filename: php-nullable.php
   <?php

   declare(strict_types=1);

   function processData(?string $data = null): string
   {
       if ($data === null) {
           return "No data";
       }
       return strtoupper($data);
   }
   ```

   PHP's `?string` is equivalent to Python's `Optional[str]` or `str | None`.

3. **Union Types**

   PHP 8.0+ supports union types (similar to Python 3.10+):

   **Python:**

   ```python
   # filename: python-union.py
   def process_value(value: int | str) -> str:
       return str(value)
   ```

   **PHP:**

   ```php
   # filename: php-union.php
   <?php

   declare(strict_types=1);

   function processValue(int|string $value): string
   {
       return (string) $value;
   }
   ```

   Both languages use `|` for union types, but PHP places it before the variable name.

4. **Type Coercion**

   PHP is more lenient with type coercion by default (unless `strict_types=1`):

   **Python:**

   ```python
   # filename: python-coercion.py
   def add_numbers(a: int, b: int) -> int:
       return a + b

   # Type checker would warn about this
   result = add_numbers("5", 10)  # Error at runtime or type check
   ```

   **PHP (without strict_types):**

   ```php
   # filename: php-coercion.php
   <?php

   // Without strict_types, PHP coerces types
   function addNumbers(int $a, int $b): int
   {
       return $a + $b;
   }

   echo addNumbers("5", 10);  // Works: "5" coerced to 5
   ```

   **PHP (with strict_types):**

   ```php
   # filename: php-strict.php
   <?php

   declare(strict_types=1);

   function addNumbers(int $a, int $b): int
   {
       return $a + $b;
   }

   // echo addNumbers("5", 10);  // Fatal error: must be int
   echo addNumbers(5, 10);  // Works: both are int
   ```

   **Best practice**: Always use `declare(strict_types=1);` at the top of PHP files for Python-like type safety.

### Expected Result

After this step, you understand:

- PHP type declarations: `type $variable` vs Python: `variable: type`
- PHP nullable types: `?string` vs Python: `Optional[str]`
- PHP union types: `int|string` (same syntax, different position)
- PHP's `strict_types=1` provides Python-like type safety
- Type coercion differences between languages

### Why It Works

PHP's type system has evolved significantly. PHP 7.0+ added scalar type declarations, PHP 7.1+ added nullable types, and PHP 8.0+ added union types. With `declare(strict_types=1);`, PHP behaves similarly to Python's type checking—rejecting invalid types at runtime. The `?` prefix for nullable types is PHP's shorthand for `Type|null`, making nullable types concise and readable.

### Troubleshooting

- **Error: "TypeError: must be of type int"** — You're passing wrong type. Enable `strict_types=1` and ensure types match.
- **Confusion about `?string`** — `?string` means `string|null`. It's shorthand for nullable types.
- **Type coercion unexpected** — Add `declare(strict_types=1);` at top of file for strict type checking.

## Step 3: Strings and String Interpolation (~10 min)

### Goal

Understand PHP string syntax differences: single vs double quotes, string interpolation, and concatenation operators.

### Actions

1. **Single vs Double Quotes**

   PHP treats single and double quotes differently (Python treats them the same):

   **Python:**

   ```python
   # filename: python-strings.py
   name = "Alice"
   message1 = 'Hello, ' + name
   message2 = f"Hello, {name}"  # f-string interpolation
   message3 = "Hello, " + name   # Same as single quotes
   ```

   **PHP:**

   ```php
   # filename: php-strings.php
   <?php

   $name = "Alice";
   $message1 = 'Hello, ' . $name;        // Single quotes: no interpolation
   $message2 = "Hello, {$name}";          // Double quotes: interpolation works
   $message3 = "Hello, " . $name;         // Concatenation with .

   echo $message1;  // Output: Hello, Alice
   echo $message2;  // Output: Hello, Alice
   ```

   **Key difference**: PHP single quotes are literal (no variable interpolation), double quotes allow interpolation.

2. **String Interpolation**

   PHP uses `{$variable}` syntax inside double-quoted strings:

   **Python:**

   ```python
   # filename: python-interpolation.py
   name = "Alice"
   age = 30
   message = f"Hello, {name}! You are {age} years old."
   ```

   **PHP:**

   ```php
   # filename: php-interpolation.php
   <?php

   $name = "Alice";
   $age = 30;
   $message = "Hello, {$name}! You are {$age} years old.";

   echo $message;  // Output: Hello, Alice! You are 30 years old.
   ```

   **Note**: PHP allows `$name` without braces for simple variables, but `{$name}` is clearer and required for complex expressions.

3. **String Concatenation**

   PHP uses `.` (dot) operator instead of `+`:

   **Python:**

   ```python
   # filename: python-concat.py
   first = "Hello"
   second = "World"
   result = first + " " + second  # Using +
   ```

   **PHP:**

   ```php
   # filename: php-concat.php
   <?php

   $first = "Hello";
   $second = "World";
   $result = $first . " " . $second;  // Using . operator

   echo $result;  // Output: Hello World
   ```

   **Important**: PHP's `+` operator does math, not string concatenation. Use `.` for strings.

4. **Heredoc and Nowdoc**

   PHP's heredoc/nowdoc are similar to Python's triple-quoted strings:

   **Python:**

   ```python
   # filename: python-triple-quotes.py
   message = """
   This is a multi-line string.
   It preserves line breaks.
   Variables: {name}
   """.format(name="Alice")
   ```

   **PHP:**

   ```php
   # filename: php-heredoc.php
   <?php

   $name = "Alice";
   $message = <<<EOT
   This is a multi-line string.
   It preserves line breaks.
   Variables: {$name}
   EOT;

   echo $message;
   ```

   **Heredoc** (double quotes): Allows variable interpolation  
   **Nowdoc** (single quotes): Literal text, no interpolation

   ```php
   # filename: php-nowdoc.php
   <?php

   $message = <<<'EOT'
   This is literal text.
   No variable interpolation: {$name}
   EOT;
   ```

### Expected Result

After this step, you understand:

- PHP single quotes: literal text, no interpolation
- PHP double quotes: allow `{$variable}` interpolation
- PHP uses `.` for string concatenation (not `+`)
- PHP heredoc/nowdoc are similar to Python triple-quoted strings
- `{$variable}` syntax is clearer than `$variable` in complex strings

### Why It Works

PHP's single/double quote distinction allows you to choose: use single quotes for performance (no parsing needed) or double quotes for convenience (variable interpolation). The `.` operator for concatenation prevents confusion with math operations—in PHP, `"5" + "3"` equals `8` (coerced to numbers), while `"5" . "3"` equals `"53"` (string concatenation).

### Troubleshooting

- **Variables not interpolating** — You're using single quotes. Switch to double quotes: `"Hello, {$name}"`.
- **Unexpected math result** — You used `+` instead of `.` for strings. Use: `$str1 . $str2`.
- **Parse error with heredoc** — Ensure `EOT;` is on its own line with no indentation or spaces before it.

## Step 4: Arrays and Collections (~12 min)

### Goal

Understand PHP array syntax differences: associative arrays vs dictionaries, array functions vs list methods, and array destructuring.

### Actions

1. **Array Syntax**

   PHP arrays are more flexible than Python lists—they can be indexed or associative:

   **Python:**

   ```python
   # filename: python-lists-dicts.py
   # Lists (indexed)
   fruits = ["apple", "banana", "cherry"]
   print(fruits[0])  # Output: apple

   # Dictionaries (associative)
   person = {"name": "Alice", "age": 30}
   print(person["name"])  # Output: Alice
   ```

   **PHP:**

   ```php
   # filename: php-arrays.php
   <?php

   // Indexed array (like Python list)
   $fruits = ["apple", "banana", "cherry"];
   echo $fruits[0];  // Output: apple

   // Associative array (like Python dict)
   $person = ["name" => "Alice", "age" => 30];
   echo $person["name"];  // Output: Alice

   // Or using array() syntax (older, still valid)
   $fruits = array("apple", "banana", "cherry");
   ```

   **Key difference**: PHP uses `=>` for key-value pairs, Python uses `:`.

2. **Array Operations**

   PHP uses functions, Python uses methods:

   **Python:**

   ```python
   # filename: python-array-ops.py
   fruits = ["apple", "banana"]
   fruits.append("cherry")      # Method
   count = len(fruits)           # Function
   has_apple = "apple" in fruits # Operator
   ```

   **PHP:**

   ```php
   # filename: php-array-ops.php
   <?php

   $fruits = ["apple", "banana"];
   $fruits[] = "cherry";              // Append (no function needed)
   $count = count($fruits);           // Function
   $has_apple = in_array("apple", $fruits);  // Function

   // Or using array_push()
   array_push($fruits, "cherry");
   ```

3. **Array Destructuring**

   PHP 7.1+ supports array destructuring (similar to Python unpacking):

   **Python:**

   ```python
   # filename: python-unpacking.py
   data = ["Alice", 30, "alice@example.com"]
   name, age, email = data

   # Dictionary unpacking
   person = {"name": "Alice", "age": 30}
   name = person["name"]
   age = person["age"]
   ```

   **PHP:**

   ```php
   # filename: php-destructuring.php
   <?php

   $data = ["Alice", 30, "alice@example.com"];
   [$name, $age, $email] = $data;

   // Associative array destructuring (PHP 7.1+)
   $person = ["name" => "Alice", "age" => 30];
   ["name" => $name, "age" => $age] = $person;
   ```

4. **Array Functions vs Methods**

   PHP uses functions, Python uses methods:

   **Python:**

   ```python
   # filename: python-methods.py
   numbers = [1, 2, 3, 4, 5]
   doubled = [x * 2 for x in numbers]  # List comprehension
   filtered = [x for x in numbers if x > 2]  # Filter
   ```

   **PHP:**

   ```php
   # filename: php-functions.php
   <?php

   $numbers = [1, 2, 3, 4, 5];
   $doubled = array_map(fn($x) => $x * 2, $numbers);  // Arrow function
   $filtered = array_filter($numbers, fn($x) => $x > 2);  // Filter

   // Or using traditional anonymous functions
   $doubled = array_map(function($x) { return $x * 2; }, $numbers);
   ```

### Expected Result

After this step, you understand:

- PHP arrays can be indexed (`[0, 1, 2]`) or associative (`["key" => "value"]`)
- PHP uses `=>` for key-value pairs (Python uses `:`)
- PHP uses functions (`array_map()`, `array_filter()`) vs Python methods
- PHP array destructuring: `[$a, $b] = $array` (similar to Python unpacking)
- PHP uses `[]` for appending (no function needed)

### Why It Works

PHP arrays are actually ordered hash maps—they can be both indexed and associative. This flexibility means PHP doesn't need separate "list" and "dict" types like Python. The `=>` operator clearly distinguishes keys from values, and PHP's function-based array operations provide a consistent API (though less object-oriented than Python's methods).

### Troubleshooting

- **Error: "Undefined array key"** — Key doesn't exist. Use `isset($array['key'])` or `$array['key'] ?? 'default'`.
- **Confusion about `=>`** — `=>` is for associative arrays (key-value pairs). Indexed arrays use `[value1, value2]`.
- **Array functions not working** — Check function name: `array_map()` not `map()`, `array_filter()` not `filter()`.

## Step 5: Functions and Methods (~10 min)

### Goal

Compare PHP function definition syntax with Python functions, including type hints, default parameters, and variadic functions.

### Actions

1. **Function Definitions**

   PHP function syntax places types before variable names:

   **Python:**

   ```python
   # filename: python-functions.py
   def greet(name: str, age: int = 0) -> str:
       return f"Hello, {name}! Age: {age}"

   result = greet("Alice", 30)
   ```

   **PHP:**

   ```php
   # filename: php-functions.php
   <?php

   declare(strict_types=1);

   function greet(string $name, int $age = 0): string
   {
       return "Hello, {$name}! Age: {$age}";
   }

   $result = greet("Alice", 30);
   ```

   **Key differences:**

   - PHP: `function name(type $param): returnType`
   - Python: `def name(param: type) -> returnType`
   - PHP requires `{}` braces (even for one line)
   - PHP uses `;` semicolon after function call

2. **Default Parameters**

   Both languages support default parameters, with similar syntax:

   **Python:**

   ```python
   # filename: python-defaults.py
   def create_user(name: str, email: str, is_active: bool = True) -> dict:
       return {"name": name, "email": email, "active": is_active}
   ```

   **PHP:**

   ```php
   # filename: php-defaults.php
   <?php

   declare(strict_types=1);

   function createUser(string $name, string $email, bool $isActive = true): array
   {
       return ["name" => $name, "email" => $email, "active" => $isActive];
   }
   ```

3. **Variadic Functions**

   PHP uses `...$args` (similar to Python's `*args`):

   **Python:**

   ```python
   # filename: python-variadic.py
   def sum_numbers(*args: int) -> int:
       return sum(args)

   result = sum_numbers(1, 2, 3, 4)  # Output: 10
   ```

   **PHP:**

   ```php
   # filename: php-variadic.php
   <?php

   declare(strict_types=1);

   function sumNumbers(int ...$args): int
   {
       return array_sum($args);
   }

   $result = sumNumbers(1, 2, 3, 4);  // Output: 10
   ```

   **Note**: PHP's `...$args` collects arguments into an array automatically.

4. **Arrow Functions**

   PHP 7.4+ supports arrow functions (similar to Python lambdas):

   **Python:**

   ```python
   # filename: python-lambda.py
   numbers = [1, 2, 3, 4, 5]
   doubled = list(map(lambda x: x * 2, numbers))
   ```

   **PHP:**

   ```php
   # filename: php-arrow.php
   <?php

   $numbers = [1, 2, 3, 4, 5];
   $doubled = array_map(fn($x) => $x * 2, $numbers);
   ```

   **Key difference**: PHP arrow functions use `fn()` keyword and `=>` syntax.

### Expected Result

After this step, you understand:

- PHP function syntax: `function name(type $param): returnType`
- PHP requires `{}` braces and `;` semicolons
- PHP variadic: `...$args` (similar to Python `*args`)
- PHP arrow functions: `fn($x) => $x * 2` (similar to Python lambdas)
- Default parameters work similarly in both languages

### Why It Works

PHP's function syntax places types before variable names (C-style), while Python places types after (PEP 484 style). Both approaches are valid—PHP's syntax is more traditional, while Python's is more readable for type hints. PHP's `...$args` syntax automatically collects arguments into an array, making variadic functions concise. Arrow functions (`fn()`) were added in PHP 7.4 to provide Python-like lambda functionality.

### Troubleshooting

- **Parse error: syntax error** — Missing `{}` braces or `;` semicolon. Check function definition and calls.
- **Type error** — Function expects different type. Check parameter types match function signature.
- **Variadic not working** — Ensure `...$args` comes last in parameter list (same as Python `*args`).

## Step 6: Operators: Ternary and Null Coalescing (~8 min)

### Goal

Understand PHP's ternary operator and null coalescing operator, which are commonly used and differ from Python's conditional expressions.

### Actions

1. **Ternary Operator**

   PHP's ternary operator is similar to Python's conditional expression, but with different syntax:

   **Python:**

   ```python
   # filename: python-ternary.py
   result = "positive" if value > 0 else "non-positive"
   message = f"Status: {status}" if status else "No status"
   ```

   **PHP:**

   ```php
   # filename: php-ternary.php
   <?php

   $value = 5;
   $result = $value > 0 ? "positive" : "non-positive";

   $status = "active";
   $message = $status ? "Status: {$status}" : "No status";

   echo $result;   // Output: positive
   echo $message;  // Output: Status: active
   ```

   **Key difference**: PHP uses `condition ? true_value : false_value`, Python uses `true_value if condition else false_value`.

2. **Null Coalescing Operator (`??`)**

   PHP's null coalescing operator provides a concise way to handle null values:

   **Python:**

   ```python
   # filename: python-null-coalesce.py
   # Python uses 'or' operator (but checks falsy, not just null)
   username = request.GET.get('username') or 'guest'
   email = user.email if user.email else 'no-email@example.com'

   # Or using None specifically
   value = data.get('key') if data.get('key') is not None else 'default'
   ```

   **PHP:**

   ```php
   # filename: php-null-coalesce.php
   <?php

   // PHP null coalescing (only checks null/undefined)
   $username = $_GET['username'] ?? 'guest';
   $email = $user->email ?? 'no-email@example.com';
   $value = $data['key'] ?? 'default';

   // Null coalescing assignment (PHP 7.4+)
   $config = [];
   $config['timeout'] ??= 30;  // Only sets if not already set
   ```

   **Important**: PHP's `??` only checks for `null` or undefined, while Python's `or` checks for any falsy value (`None`, `0`, `""`, `[]`, etc.).

3. **Chaining Null Coalescing**

   PHP allows chaining null coalescing operators:

   **Python:**

   ```python
   # filename: python-chain.py
   value = (data.get('user') or {}).get('name') or 'Unknown'
   ```

   **PHP:**

   ```php
   # filename: php-chain.php
   <?php

   $data = [];
   $value = $data['user']['name'] ?? 'Unknown';  // Safe chaining

   // Or with multiple fallbacks
   $value = $data['user']['name'] ?? $data['default_name'] ?? 'Unknown';
   ```

### Expected Result

After this step, you understand:

- PHP ternary: `condition ? true : false` vs Python: `true if condition else false`
- PHP null coalescing: `$value ?? 'default'` (checks null only)
- Python `or`: checks falsy values (not just null)
- PHP null coalescing assignment: `$var ??= value`

### Why It Works

PHP's ternary operator follows C-style syntax (condition first), while Python's reads more naturally (value first). PHP's `??` operator is more precise than Python's `or`—it only checks for null/undefined, making it safer for default value assignment. The null coalescing assignment operator (`??=`) provides a concise way to set defaults only if a variable is null.

### Troubleshooting

- **Unexpected value with `??`** — Remember `??` only checks null, not other falsy values. Use `?:` (Elvis operator) for falsy checks, or explicit `if` statements.
- **Ternary operator confusion** — PHP: `condition ? true : false` (condition first), Python: `true if condition else false` (value first).

## Step 7: Match Expressions (~8 min)

### Goal

Understand PHP's `match` expression syntax compared to Python's `match` statement (Python 3.10+).

### Actions

1. **Basic Match Expression**

   PHP 8.0+ introduced `match` expressions, similar to Python 3.10+'s `match` statement:

   **Python 3.10+:**

   ```python
   # filename: python-match.py
   def get_status_message(status: str) -> str:
       match status:
           case 'pending':
               return 'Your request is pending review.'
           case 'approved':
               return 'Your request has been approved!'
           case 'rejected':
               return 'Your request has been rejected.'
           case _:
               return 'Unknown status.'
   ```

   **PHP 8.0+:**

   ```php
   # filename: php-match.php
   <?php

   declare(strict_types=1);

   function getStatusMessage(string $status): string
   {
       return match ($status) {
           'pending' => 'Your request is pending review.',
           'approved' => 'Your request has been approved!',
           'rejected' => 'Your request has been rejected.',
           default => 'Unknown status.',
       };
   }

   echo getStatusMessage('approved');  // Output: Your request has been approved!
   ```

   **Key differences:**

   - PHP: `match (value) { case => result, default => result }`
   - Python: `match value: case 'value': return result`
   - PHP `match` returns a value (expression), Python `match` is a statement

2. **Match with Multiple Conditions**

   Both languages support multiple conditions:

   **Python:**

   ```python
   # filename: python-match-multiple.py
   match value:
       case 1 | 2 | 3:
           return "Small number"
       case 4 | 5 | 6:
           return "Medium number"
       case _:
           return "Large number"
   ```

   **PHP:**

   ```php
   # filename: php-match-multiple.php
   <?php

   $result = match ($value) {
       1, 2, 3 => "Small number",
       4, 5, 6 => "Medium number",
       default => "Large number",
   };
   ```

3. **Match with Conditions**

   PHP `match` supports conditions (guards):

   **Python:**

   ```python
   # filename: python-match-guard.py
   match value:
       case x if x > 100:
           return "Very large"
       case x if x > 50:
           return "Large"
       case _:
           return "Small"
   ```

   **PHP:**

   ```php
   # filename: php-match-condition.php
   <?php

   $result = match (true) {
       $value > 100 => "Very large",
       $value > 50 => "Large",
       default => "Small",
   };
   ```

### Expected Result

After this step, you understand:

- PHP `match` is an expression (returns a value)
- Python `match` is a statement (uses `return` or assignments)
- PHP uses `=>` for case results, Python uses `:` and `return`
- PHP `match` requires exhaustive matching (or `default`)
- Both support multiple conditions and guards

### Why It Works

PHP's `match` is an expression that returns a value directly, making it more concise than `switch` statements. Python's `match` is a statement that requires explicit `return` or assignment. PHP's `match` provides strict type checking and requires exhaustive matching (or a `default` case), preventing bugs from missing cases.

### Troubleshooting

- **Error: "Unhandled match value"** — PHP `match` requires all possible values to be handled or a `default` case. Add `default => value` to handle unmatched cases.
- **Match not returning value** — PHP `match` is an expression—assign its result: `$result = match ($value) { ... };`

## Step 8: Object-Oriented Programming (~15 min)

### Goal

Master PHP OOP syntax differences: class definitions, properties, methods, visibility modifiers, and constructor property promotion.

### Actions

1. **Class Definitions**

   PHP class syntax is similar to Python, but with different visibility keywords:

   **Python:**

   ```python
   # filename: python-class.py
   class User:
       def __init__(self, name: str, email: str):
           self.name = name
           self.email = email

       def get_info(self) -> str:
           return f"{self.name} ({self.email})"
   ```

   **PHP:**

   ```php
   # filename: php-class.php
   <?php

   declare(strict_types=1);

   class User
   {
       public function __construct(
           public string $name,
           public string $email
       ) {}

       public function getInfo(): string
       {
           return "{$this->name} ({$this->email})";
       }
   }
   ```

   **Key differences:**

   - PHP requires `public`, `private`, or `protected` visibility modifiers
   - PHP uses `$this->property` instead of `self.property`
   - PHP 8.0+ supports constructor property promotion (like dataclasses)

2. **Properties and Visibility**

   PHP requires explicit visibility modifiers for all properties and methods:

   **Python:**

   ```python
   # filename: python-visibility.py
   class BankAccount:
       def __init__(self, balance: float):
           self.balance = balance        # Public by default
           self._internal_id = "123"    # Convention: protected
           self.__secret = "hidden"     # Name mangling: private
   ```

   **PHP:**

   ```php
   # filename: php-visibility.php
   <?php

   declare(strict_types=1);

   class BankAccount
   {
       public float $balance;           // Public: accessible anywhere
       protected string $internalId;   // Protected: class and children
       private string $secret;          // Private: class only

       public function __construct(float $balance)
       {
           $this->balance = $balance;
           $this->internalId = "123";
           $this->secret = "hidden";
       }
   }
   ```

   **Important**: PHP enforces visibility at language level (not just convention like Python).

3. **Methods and $this**

   PHP uses `$this->` to reference instance properties and methods:

   **Python:**

   ```python
   # filename: python-methods.py
   class Calculator:
       def __init__(self, value: int = 0):
           self.value = value

       def add(self, amount: int) -> None:
           self.value += amount

       def get_value(self) -> int:
           return self.value
   ```

   **PHP:**

   ```php
   # filename: php-methods.php
   <?php

   declare(strict_types=1);

   class Calculator
   {
       public function __construct(
           private int $value = 0
       ) {}

       public function add(int $amount): void
       {
           $this->value += $amount;
       }

       public function getValue(): int
       {
           return $this->value;
       }
   }
   ```

   **Note**: PHP uses `->` (arrow) operator, Python uses `.` (dot) operator.

4. **Static Methods and Properties**

   Both languages support static members:

   **Python:**

   ```python
   # filename: python-static.py
   class MathUtils:
       PI = 3.14159  # Class variable

       @staticmethod
       def add(a: int, b: int) -> int:
           return a + b
   ```

   **PHP:**

   ```php
   # filename: php-static.php
   <?php

   declare(strict_types=1);

   class MathUtils
   {
       public static float $PI = 3.14159;  // Static property

       public static function add(int $a, int $b): int
       {
           return $a + $b;
       }
   }

   // Usage
   echo MathUtils::$PI;           // Access static property
   echo MathUtils::add(5, 3);     // Call static method
   ```

5. **Inheritance**

   PHP uses `extends` keyword (similar to Python):

   **Python:**

   ```python
   # filename: python-inheritance.py
   class Animal:
       def __init__(self, name: str):
           self.name = name

       def speak(self) -> str:
           return "Some sound"

   class Dog(Animal):
       def speak(self) -> str:
           return "Woof!"
   ```

   **PHP:**

   ```php
   # filename: php-inheritance.php
   <?php

   declare(strict_types=1);

   class Animal
   {
       public function __construct(
           protected string $name
       ) {}

       public function speak(): string
       {
           return "Some sound";
       }
   }

   class Dog extends Animal
   {
       public function speak(): string
       {
           return "Woof!";
       }
   }
   ```

6. **Traits**

   PHP traits provide code reuse similar to Python mixins:

   **Python:**

   ```python
   # filename: python-mixin.py
   class LoggableMixin:
       def log(self, message: str) -> None:
           print(f"[{self.__class__.__name__}] {message}")

   class User(LoggableMixin):
       def __init__(self, name: str):
           self.name = name
           self.log(f"User {name} created")
   ```

   **PHP:**

   ```php
   # filename: php-traits.php
   <?php

   declare(strict_types=1);

   trait Loggable
   {
       public function log(string $message): void
       {
           echo "[" . static::class . "] {$message}\n";
       }
   }

   class User
   {
       use Loggable;

       public function __construct(
           public string $name
       ) {
           $this->log("User {$name} created");
       }
   }

   $user = new User("Alice");
   // Output: [User] User Alice created
   ```

   **Key differences:**

   - PHP: `trait TraitName { ... }` and `use TraitName;` in class
   - Python: Mixin classes that are inherited
   - PHP traits can have methods, properties, and abstract methods
   - Multiple traits can be used: `use Trait1, Trait2;`

7. **Trait Conflict Resolution**

   PHP provides conflict resolution when multiple traits have the same method:

   **PHP:**

   ```php
   # filename: php-trait-conflict.php
   <?php

   trait TraitA
   {
       public function method(): string
       {
           return "TraitA";
       }
   }

   trait TraitB
   {
       public function method(): string
       {
           return "TraitB";
       }
   }

   class MyClass
   {
       use TraitA, TraitB {
           TraitA::method insteadof TraitB;  // Use TraitA's method
           TraitB::method as methodB;        // Alias TraitB's method
       }
   }
   ```

### Expected Result

After this step, you understand:

- PHP requires visibility modifiers (`public`, `private`, `protected`) for all members
- PHP uses `$this->property` instead of `self.property`
- PHP uses `->` operator for object access (Python uses `.`)
- PHP 8.0+ constructor property promotion simplifies class definitions
- PHP static members use `::` operator (Python uses `.`)
- PHP traits (`trait` and `use`) provide code reuse similar to Python mixins

### Why It Works

PHP's explicit visibility modifiers enforce encapsulation at the language level, while Python relies on conventions (`_` for protected, `__` for private). PHP's `$this->` syntax clearly distinguishes instance access from static access (`::`). Constructor property promotion (PHP 8.0+) allows you to declare and initialize properties in the constructor signature, similar to Python dataclasses, reducing boilerplate.

### Troubleshooting

- **Error: "Cannot access private property"** — Property is private. Change to `public` or add getter method.
- **Error: "Call to undefined method"** — Check method name and visibility. Ensure `$this->method()` not `$this->method`.
- **Confusion about `->` vs `::`** — Use `->` for instance members (`$obj->method()`), `::` for static members (`Class::method()`).
- **Trait conflict errors** — When using multiple traits with same method, use `insteadof` or `as` to resolve conflicts.

## Step 9: Namespaces and Autoloading (~12 min)

### Goal

Understand PHP namespaces vs Python modules, `use` statements vs imports, and PSR-4 autoloading.

### Actions

1. **Namespaces vs Modules**

   PHP uses `namespace` keyword, Python uses file-based modules:

   **Python:**

   ```python
   # filename: app/models/user.py
   class User:
       def __init__(self, name: str):
           self.name = name

   # filename: main.py
   from app.models.user import User
   user = User("Alice")
   ```

   **PHP:**

   ```php
   # filename: app/Models/User.php
   <?php

   namespace App\Models;

   class User
   {
       public function __construct(
           public string $name
       ) {}
   }
   ```

   ```php
   # filename: index.php
   <?php

   require_once 'app/Models/User.php';

   use App\Models\User;

   $user = new User("Alice");
   ```

2. **Use Statements**

   PHP `use` statements are similar to Python imports:

   **Python:**

   ```python
   # filename: python-imports.py
   from app.models import User, Post
   from app.utils.helpers import format_date
   import json
   ```

   **PHP:**

   ```php
   # filename: php-use.php
   <?php

   use App\Models\User;
   use App\Models\Post;
   use App\Utils\Helpers\formatDate;
   use function json_encode;

   // Or group imports
   use App\Models\{User, Post};
   ```

3. **Fully Qualified Names**

   PHP allows using fully qualified names without `use`:

   **Python:**

   ```python
   # filename: python-qualified.py
   import app.models.user
   user = app.models.user.User("Alice")
   ```

   **PHP:**

   ```php
   # filename: php-qualified.php
   <?php

   // Without use statement
   $user = new \App\Models\User("Alice");

   // With use statement (preferred)
   use App\Models\User;
   $user = new User("Alice");
   ```

   **Note**: PHP uses `\` (backslash) as namespace separator, Python uses `.` (dot).

4. **PSR-4 Autoloading**

   PHP uses Composer for autoloading (similar to Python's import system):

   **Python:**

   ```python
   # Python automatically finds modules based on file structure
   from app.models.user import User  # Looks for app/models/user.py
   ```

   **PHP (with Composer):**

   ```json
   # filename: composer.json
   {
       "autoload": {
           "psr-4": {
               "App\\": "app/"
           }
       }
   }
   ```

   ```php
   # filename: index.php
   <?php

   require_once 'vendor/autoload.php';

   use App\Models\User;  // Automatically loads app/Models/User.php

   $user = new User("Alice");
   ```

   **Key difference**: PHP requires explicit autoloader setup (Composer), Python's import system is built-in.

### Expected Result

After this step, you understand:

- PHP namespaces: `namespace App\Models;` vs Python file-based modules
- PHP `use` statements: `use App\Models\User;` vs Python `from app.models import User`
- PHP uses `\` for namespace separator (Python uses `.`)
- PHP requires autoloader (Composer) vs Python's built-in import system
- Fully qualified names: `\App\Models\User` vs `app.models.user.User`

### Why It Works

PHP namespaces provide logical organization independent of file structure (though PSR-4 links them). Python's module system is file-based—the file path determines the module name. PHP's `\` separator prevents conflicts with class names, while Python's `.` separator matches file system paths. Composer's autoloader maps namespace prefixes to directories, similar to how Python's import system resolves module paths.

### Troubleshooting

- **Error: "Class not found"** — Missing `use` statement or autoloader not configured. Check `composer.json` and run `composer dump-autoload`.
- **Error: "Undefined namespace"** — Namespace declaration missing or incorrect. Ensure `namespace App\Models;` matches directory structure.
- **Confusion about `\` vs `/`** — Use `\` for namespaces (`App\Models\User`), `/` for file paths (`app/Models/User.php`).

## Step 10: Error Handling and Exceptions (~10 min)

### Goal

Compare PHP exception handling syntax with Python exceptions, understanding `try/catch/finally` blocks and exception types.

### Actions

1. **Try/Catch/Finally**

   PHP exception syntax is very similar to Python:

   **Python:**

   ```python
   # filename: python-exceptions.py
   try:
       result = 10 / 0
   except ZeroDivisionError as e:
       print(f"Error: {e}")
   except Exception as e:
       print(f"Unexpected error: {e}")
   finally:
       print("Cleanup code")
   ```

   **PHP:**

   ```php
   # filename: php-exceptions.php
   <?php

   try {
       $result = 10 / 0;
   } catch (DivisionByZeroError $e) {
       echo "Error: " . $e->getMessage();
   } catch (Exception $e) {
       echo "Unexpected error: " . $e->getMessage();
   } finally {
       echo "Cleanup code";
   }
   ```

   **Key differences:**

   - PHP: `catch (ExceptionType $e)` vs Python: `except ExceptionType as e`
   - PHP uses `->getMessage()` method vs Python's string representation
   - Both use `finally` for cleanup code

2. **Throwing Exceptions**

   Both languages use `throw`/`raise`:

   **Python:**

   ```python
   # filename: python-throw.py
   def divide(a: float, b: float) -> float:
       if b == 0:
           raise ValueError("Cannot divide by zero")
       return a / b
   ```

   **PHP:**

   ```php
   # filename: php-throw.php
   <?php

   declare(strict_types=1);

   function divide(float $a, float $b): float
   {
       if ($b == 0) {
           throw new ValueError("Cannot divide by zero");
       }
       return $a / $b;
   }
   ```

   **Note**: PHP uses `throw new ExceptionType()` vs Python's `raise ExceptionType()`.

3. **Custom Exceptions**

   Both languages allow custom exception classes:

   **Python:**

   ```python
   # filename: python-custom-exception.py
   class ValidationError(Exception):
       pass

   def validate_email(email: str) -> None:
       if "@" not in email:
           raise ValidationError("Invalid email format")
   ```

   **PHP:**

   ```php
   # filename: php-custom-exception.php
   <?php

   declare(strict_types=1);

   class ValidationError extends Exception
   {
   }

   function validateEmail(string $email): void
   {
       if (strpos($email, "@") === false) {
           throw new ValidationError("Invalid email format");
       }
   }
   ```

4. **Error vs Exception**

   PHP distinguishes between `Error` (fatal) and `Exception` (catchable):

   **Python:**

   ```python
   # filename: python-errors.py
   # Python treats all errors as exceptions
   try:
       undefined_function()
   except NameError as e:
       print(f"Caught: {e}")
   ```

   **PHP:**

   ```php
   # filename: php-errors.php
   <?php

   // PHP 7+ treats many errors as exceptions
   try {
       undefinedFunction();
   } catch (Error $e) {
       echo "Caught: " . $e->getMessage();
   } catch (Exception $e) {
       echo "Exception: " . $e->getMessage();
   }
   ```

   **Note**: PHP 7+ introduced `Error` class for fatal errors, making them catchable like exceptions.

### Expected Result

After this step, you understand:

- PHP `try/catch/finally` syntax is very similar to Python
- PHP: `catch (ExceptionType $e)` vs Python: `except ExceptionType as e`
- PHP: `throw new Exception()` vs Python: `raise Exception()`
- PHP uses `->getMessage()` to get exception message
- PHP distinguishes `Error` (fatal) from `Exception` (catchable)

### Why It Works

PHP's exception handling closely mirrors Python's—both use try/catch/finally blocks and allow custom exception classes. PHP 7+ unified error handling by making fatal errors throwable as `Error` exceptions, similar to how Python treats all errors as exceptions. The main difference is syntax: PHP uses `catch (Type $e)` and `throw new Type()`, while Python uses `except Type as e` and `raise Type()`.

### Troubleshooting

- **Error not caught** — Check exception type matches. Use `catch (Exception $e)` to catch all exceptions.
- **"Fatal error" not catchable** — In PHP 7+, most fatal errors are catchable as `Error`. Use `catch (Error $e)`.
- **Exception message not showing** — Use `$e->getMessage()` method, not `$e` directly in string context.

## Exercises

Practice converting Python code to PHP and understanding syntax differences.

### Exercise 1: Convert Python Class to PHP

**Goal**: Practice PHP OOP syntax by converting a Python class to PHP.

Convert this Python class to PHP 8.4:

```python
# filename: python-exercise.py
class Product:
    def __init__(self, name: str, price: float, in_stock: bool = True):
        self.name = name
        self.price = price
        self.in_stock = in_stock

    def apply_discount(self, percent: float) -> None:
        if percent < 0 or percent > 100:
            raise ValueError("Discount must be between 0 and 100")
        self.price *= (1 - percent / 100)

    def get_info(self) -> str:
        status = "Available" if self.in_stock else "Out of stock"
        return f"{self.name}: ${self.price:.2f} - {status}"
```

**Requirements:**

- Use PHP 8.4 constructor property promotion
- Include proper type declarations
- Use `declare(strict_types=1);`
- Throw `ValueError` exception for invalid discount
- Match Python's string formatting in `get_info()`

**Validation**: Test your PHP class:

```php
# filename: test-exercise.php
<?php

require_once 'Product.php';

$product = new Product("Laptop", 999.99, true);
$product->applyDiscount(10);
echo $product->getInfo();  // Expected: Laptop: $899.99 - Available
```

Expected output:

```
Laptop: $899.99 - Available
```

### Exercise 2: String Processing Function

**Goal**: Practice PHP string syntax and array functions.

Convert this Python function to PHP:

```python
# filename: python-string-exercise.py
def process_tags(tags: list[str]) -> dict[str, int]:
    """Count tag occurrences and return sorted by count."""
    counts = {}
    for tag in tags:
        tag_lower = tag.lower().strip()
        if tag_lower:
            counts[tag_lower] = counts.get(tag_lower, 0) + 1

    # Sort by count (descending)
    sorted_tags = dict(sorted(counts.items(), key=lambda x: x[1], reverse=True))
    return sorted_tags
```

**Requirements:**

- Use PHP type declarations
- Use `array_map()` or array functions where appropriate
- Handle empty strings properly
- Return associative array sorted by count (descending)

**Validation**: Test your PHP function:

```php
# filename: test-string-exercise.php
<?php

require_once 'process_tags.php';

$tags = ["PHP", "python", "PHP", "  JavaScript  ", "python", ""];
$result = processTags($tags);
print_r($result);
```

Expected output:

```
Array
(
    [php] => 2
    [python] => 2
    [javascript] => 1
)
```

### Exercise 3: Namespace and Autoloading

**Goal**: Practice PHP namespaces and `use` statements.

Create a PHP project structure:

```
app/
  Models/
    User.php
  Services/
    EmailService.php
index.php
```

**Requirements:**

- Create `User` class in `App\Models` namespace
- Create `EmailService` class in `App\Services` namespace
- Use `EmailService` in `User` class
- Set up PSR-4 autoloading in `composer.json`
- Use `use` statements in `index.php`

**Validation**: Verify autoloading works:

```php
# filename: index.php
<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Services\EmailService;

$user = new User("Alice", "alice@example.com");
$user->sendWelcomeEmail();
```

## Troubleshooting

Common syntax errors and solutions when transitioning from Python to PHP.

### Error: "Parse error: syntax error, unexpected '$variable'"

**Symptom**: PHP parser complains about variable syntax.

**Cause**: Missing `$` prefix or incorrect variable reference.

**Solution**: Ensure all variables use `$` prefix:

```php
// Wrong
$name = "Alice";
echo name;  // Missing $

// Correct
$name = "Alice";
echo $name;  // Has $ prefix
```

### Error: "Fatal error: Uncaught TypeError: must be of type int"

**Symptom**: Type error when calling function.

**Cause**: Type mismatch or `strict_types=1` enabled with wrong type.

**Solution**: Ensure types match function signature:

```php
// Wrong
declare(strict_types=1);
function add(int $a, int $b): int { return $a + $b; }
echo add("5", 10);  // String passed to int parameter

// Correct
echo add(5, 10);  // Both are int
// Or disable strict_types (not recommended)
```

### Error: "Class 'App\Models\User' not found"

**Symptom**: Autoloader can't find class.

**Cause**: Missing `use` statement, incorrect namespace, or autoloader not configured.

**Solution**: Check namespace and autoloader:

```php
// Wrong
$user = new User("Alice");  // No use statement

// Correct
use App\Models\User;
$user = new User("Alice");

// Or use fully qualified name
$user = new \App\Models\User("Alice");
```

Also verify `composer.json` autoload configuration and run `composer dump-autoload`.

### Problem: Variables Not Interpolating in Strings

**Symptom**: Variables show as literal text in strings.

**Cause**: Using single quotes instead of double quotes.

**Solution**: Use double quotes for interpolation:

```php
// Wrong
$name = "Alice";
$message = 'Hello, $name';  // Single quotes: no interpolation

// Correct
$message = "Hello, {$name}";  // Double quotes: interpolation works
```

### Problem: Array Operations Not Working

**Symptom**: Array functions return errors or unexpected results.

**Cause**: Using Python-style method calls instead of PHP functions.

**Solution**: Use PHP array functions:

```php
// Wrong (Python style)
$numbers = [1, 2, 3];
$doubled = $numbers.map(fn($x) => $x * 2);  // PHP arrays don't have map() method

// Correct (PHP style)
$doubled = array_map(fn($x) => $x * 2, $numbers);  // Use array_map() function
```

## Wrap-up

Congratulations! You've completed Chapter 04 and now understand PHP syntax differences from Python. Here's what you've accomplished:

- **Variables**: Understand PHP's `$` prefix requirement and variable assignment syntax
- **Types**: Compare PHP type declarations with Python type hints, including nullable and union types
- **Strings**: Master PHP string interpolation (`{$var}`), concatenation (`.`), and heredoc/nowdoc
- **Arrays**: Understand PHP arrays (indexed and associative) vs Python lists and dictionaries
- **Functions**: Compare PHP function syntax with Python, including variadic functions and arrow functions
- **Operators**: Master PHP ternary operator and null coalescing operator (`??`) vs Python conditionals
- **Match Expressions**: Understand PHP `match` expressions vs Python `match` statements
- **OOP**: Master PHP class syntax, visibility modifiers, `$this->`, constructor property promotion, and traits
- **Namespaces**: Understand PHP namespaces vs Python modules, `use` statements, and PSR-4 autoloading
- **Exceptions**: Compare PHP exception handling with Python, including `try/catch/finally` syntax

You now have the syntax foundation needed to read and write PHP code confidently. The concepts are the same as Python—variables, functions, classes, namespaces, exceptions—just with different syntax that follows logical patterns.

**What's Next?**

In [Chapter 05: Working with Data: Eloquent ORM & Database Workflow](/series/python-developers-love-php-laravel/chapters/05-working-with-data-eloquent-orm-database-workflow), you'll apply this PHP syntax knowledge to Laravel's Eloquent ORM, comparing it to Django ORM and SQLAlchemy. You'll learn how Laravel handles database queries, relationships, and migrations—all using the PHP syntax you just mastered.

## Code Examples

All code examples from this chapter are available in the [`code/chapter-04/`](https://github.com/dalehurley/codewithphp/tree/main/docs/series/python-developers-love-php-laravel/code/chapter-04) directory:

- **Quick Start**: [`quickstart.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-04/quickstart.php) — Quick syntax comparison example
- **Variables**: [`variables-comparison.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-04/variables-comparison.php) — Variable prefixes and assignment
- **Types**: [`types-comparison.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-04/types-comparison.php) — Type declarations and nullable types
- **Strings**: [`strings-comparison.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-04/strings-comparison.php) — String interpolation and concatenation
- **Arrays**: [`arrays-comparison.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-04/arrays-comparison.php) — Array syntax and operations
- **Functions**: [`functions-comparison.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-04/functions-comparison.php) — Function definitions and arrow functions
- **Operators**: [`operators-comparison.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-04/operators-comparison.php) — Ternary operator and null coalescing operator
- **Match Expressions**: [`match-expressions-comparison.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-04/match-expressions-comparison.php) — Match expressions vs Python match statements
- **OOP**: [`oop-comparison.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-04/oop-comparison.php) — Class syntax, visibility, inheritance, and traits
- **Namespaces**: [`namespaces-comparison.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-04/namespaces-comparison.php) — Namespaces and autoloading
- **Exceptions**: [`exceptions-comparison.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-04/exceptions-comparison.php) — Exception handling syntax
- **Exercise Solutions**: [`solutions/`](https://github.com/dalehurley/codewithphp/tree/main/docs/series/python-developers-love-php-laravel/code/chapter-04/solutions) — Complete solutions for all exercises

See the [README.md](https://github.com/dalehurley/codewithphp/blob/main/docs/series/python-developers-love-php-laravel/code/chapter-04/README.md) for detailed instructions on running each example.

## Further Reading

- [PHP Manual: Language Reference](https://www.php.net/manual/en/langref.php) — Complete PHP syntax reference
- [PHP The Right Way: Type Declarations](https://phptherightway.com/#type_declarations) — Best practices for PHP types
- [PSR-4 Autoloading Standard](https://www.php-fig.org/psr/psr-4/) — PHP namespace and autoloading standard
- [PHP Manual: Exceptions](https://www.php.net/manual/en/language.exceptions.php) — PHP exception handling documentation
- [Composer Documentation](https://getcomposer.org/doc/) — PHP dependency management and autoloading

For Python developers:

- [Python Type Hints (PEP 484)](https://www.python.org/dev/peps/pep-0484/) — Compare with PHP type declarations
- [Python Modules and Packages](https://docs.python.org/3/tutorial/modules.html) — Compare with PHP namespaces
