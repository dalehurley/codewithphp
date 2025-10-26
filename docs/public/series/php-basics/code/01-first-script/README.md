# Chapter 01: Your First PHP Script - Code Examples

This directory contains working code examples from Chapter 01.

## Files Overview

### 1. `basic-syntax.php`

Demonstrates fundamental PHP syntax including comments, echo statements, and basic output.

**What it demonstrates:**

- PHP opening tags
- Single-line and multi-line comments
- DocBlock comments for documentation
- The `echo` statement
- String concatenation with the `.` operator

**Run it:**

```bash
php basic-syntax.php
```

**Expected output:**

```
Welcome to PHP!
Hello World!
PHP is awesome!
```

### 2. `variables-demo.php`

Shows how to declare, use, and output variables.

**What it demonstrates:**

- Variable declaration and assignment
- String interpolation (variables in double quotes)
- Variable reassignment
- Different data types (strings, numbers, booleans)
- Ternary operator for conditional output

**Run it:**

```bash
php variables-demo.php
```

### 3. `mixing-html.php`

Demonstrates how to embed PHP within HTML documents.

**What it demonstrates:**

- Embedding PHP in HTML
- Dynamically generating content
- Using PHP for conditional display
- Generating HTML elements with loops
- Time-based greetings

**Run it:**

```bash
# Start PHP's built-in server
php -S localhost:8000

# Then visit in your browser:
# http://localhost:8000/mixing-html.php
```

## Exercise Solutions

The `solutions/` directory contains complete, working solutions for chapter exercises:

### Exercise 1: Personal Information Script

**File:** `solutions/exercise-1-personal-info.php`

Create a script that displays your name, age, and favorite hobby using variables.

**Run it:**

```bash
php solutions/exercise-1-personal-info.php
```

### Exercise 2: Simple Calculations

**File:** `solutions/exercise-2-calculations.php`

Perform basic math operations (add, subtract, multiply, divide) on two numbers.

**Run it:**

```bash
php solutions/exercise-2-calculations.php
```

## Try It Yourself

Before looking at the solutions, try writing these programs yourself! Here are the exercise requirements:

**Exercise 1 Requirements:**

- Create variables for your name, age, and a favorite hobby
- Use `echo` to display them in a nicely formatted way
- Use string concatenation or interpolation

**Exercise 2 Requirements:**

- Create two variables with numbers (e.g., 20 and 5)
- Calculate and display their sum, difference, product, and quotient
- Format the output clearly

## Common Mistakes to Avoid

1. **Forgetting the `<?php` opening tag**

   ```php
   // Wrong
   echo "Hello";

   // Correct
   <?php
   echo "Hello";
   ```

2. **Missing semicolons**

   ```php
   // Wrong
   echo "Hello"

   // Correct
   echo "Hello";
   ```

3. **Using single quotes with variable interpolation**

   ```php
   $name = "Alice";

   // Wrong - this outputs literally: Hello, $name
   echo 'Hello, $name';

   // Correct - this outputs: Hello, Alice
   echo "Hello, $name";
   ```

## Quick Tips

- Use `PHP_EOL` instead of `\n` for cross-platform newlines
- Double quotes (`"`) allow variable interpolation, single quotes (`'`) don't
- Every PHP statement must end with a semicolon (`;`)
- Variable names are case-sensitive: `$name` ≠ `$Name`
- Variable names must start with `$` followed by a letter or underscore

## Next Steps

Once you're comfortable with these examples, you're ready to move on to Chapter 02: Variables, Data Types, and Constants!

## Related Chapter

[Chapter 01: Your First PHP Script](../../chapters/01-your-first-php-script.md)
