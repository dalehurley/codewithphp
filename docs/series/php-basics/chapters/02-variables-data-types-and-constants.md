---
title: "02: Variables, Data Types, and Constants"
description: "Learn how to store, manage, and work with different kinds of data using PHP's variables, data types, and constants."
series: "php-basics"
chapter: 2
order: 2
difficulty: "Beginner"
prerequisites:
  - "/series/php-basics/chapters/01-your-first-php-script"
---

# Chapter 02: Variables, Data Types, and Constants

## Overview

In the last chapter, we worked with static text like 'Hello, World!'. But to build dynamic applications, we need a way to store and manipulate information that can change. This is where variables come in. Variables are like containers that hold data, such as a username, the price of a product, or the result of a calculation.

In this chapter, you'll master the fundamentals of variables, explore PHP's different data types, and learn about constants—a special type of variable whose value cannot be changed. By the end, you'll be able to store, manipulate, and debug data effectively.

## Prerequisites

- **PHP installed**: PHP 8.4 (check with `php --version`)
- **Text editor**: Any code editor (VS Code, Sublime Text, etc.)
- **Terminal access**: Command line/terminal to run PHP scripts
- **Previous chapter completed**: [Chapter 01: Your First PHP Script](/series/php-basics/chapters/01-your-first-php-script)
- **Estimated time**: 25–30 minutes

## What You'll Build

By the end of this chapter, you'll have created several working PHP scripts that demonstrate:

- Variable declaration and usage
- String concatenation and interpolation
- Heredoc and Nowdoc syntax for multiline strings
- Complex string interpolation with curly braces
- Type inspection with `var_dump()`
- Explicit type casting and conversion
- Constant definition and usage in calculations
- A practical tax calculation script

## Objectives

- Declare, assign, and use variables with proper naming conventions
- Understand PHP's primary data types: string, integer, float, and boolean
- Use `var_dump()` to inspect the type and value of a variable
- Combine and embed variables within strings using concatenation and interpolation
- Work with multiline strings using Heredoc and Nowdoc syntax
- Master complex string interpolation with curly braces
- Understand the difference between automatic type juggling and manual type casting
- Explicitly cast between types (`int`, `float`, `string`, `bool`)
- Define and use constants for values that should remain fixed
- Debug common variable-related errors

## Quick Start

If you want to jump straight in, create a file called `variables.php` and run it:

```bash
# Create and run your first variable script
echo '<?php
$name = "Dale";
echo "Hello, $name!";
' > variables.php

php variables.php
# Expected output: Hello, Dale!
```

## Step 1: Understanding Variables (~3 min)

**Goal**: Create variables, assign values, and display them.

Think of a variable as a labeled box where you can store a piece of information. You give the box a name (the variable name) and put something inside it (the value).

### Variable Naming Rules

In PHP, a variable name must:

- Start with a dollar sign (`$`)
- Be followed by a letter or an underscore
- Contain only letters, numbers, and underscores (`A-z`, `0-9`, and `_`)

Variable names are **case-sensitive**, meaning `$name` and `$Name` are two different variables.

**Naming Conventions**: By convention, PHP developers use `camelCase` for variable names (e.g., `$firstName`, `$totalPrice`). This makes multi-word variable names easier to read.

### Actions

1.  **Create a New File**:

```bash
# Create variables.php in your project directory
touch variables.php
```

2.  **Declare and Use Variables**:
    Add the following code to the file:

```php
# filename: variables.php
<?php

$name = 'Dale';
$age = 41;

echo $name;
echo $age;
```

3.  **Run the Script**:

```bash
# Execute the script
php variables.php
```

### Expected Result

You'll see `Dale41`. The output is mashed together because `echo` simply prints the values one after the other without any spacing.

### Why It Works

PHP stores the string 'Dale' in memory and labels it `$name`. When you write `echo $name`, PHP retrieves that value and outputs it. The assignment operator (`=`) copies the right-hand value into the variable on the left.

### Troubleshooting

**Error: "Parse error: syntax error, unexpected '$name'"**

- **Cause**: You forgot the opening `<?php` tag.
- **Fix**: Always start PHP files with `<?php` on the first line.

**Error: "Undefined variable $Name"**

- **Cause**: Variable names are case-sensitive. `$name` and `$Name` are different.
- **Fix**: Use the exact variable name as you defined it.

## Step 2: Working with Strings (~4 min)

**Goal**: Combine variables and text using concatenation and interpolation.

Strings are sequences of characters, like "Hello" or "PHP is fun!". PHP provides two primary ways to combine strings and variables: concatenation and interpolation.

### Actions

1.  **Concatenation**:
    Concatenation means joining strings together using the dot (`.`) operator. Modify your `variables.php` file:

```php
# filename: variables.php
<?php

$name = 'Dale';
$age = 41;

// The . operator joins strings together
echo 'My name is ' . $name . ' and I am ' . $age . ' years old.' . PHP_EOL;
```

2.  **Run and Verify**:

```bash
php variables.php
# Expected output: My name is Dale and I am 41 years old.
```

3.  **Interpolation** (with double quotes):
    Interpolation is a convenient shortcut where variables inside a **double-quoted string** are automatically replaced with their values. Update your file:

```php
# filename: variables.php
<?php

$name = 'Dale';
$age = 41;

// Double quotes enable variable interpolation
echo "My name is $name and I am $age years old." . PHP_EOL;
```

4.  **Verify the Output**:

```bash
php variables.php
# Expected output: My name is Dale and I am 41 years old.
```

### Expected Result

Both methods produce identical output: a clean, readable sentence.

### Why It Works

**Concatenation** uses the dot operator to join separate strings and variables into one string before outputting. **Interpolation** (double quotes only) tells PHP to scan the string for `$variables` and replace them with their values before outputting. Interpolation is generally preferred for readability.

**Single vs. Double Quotes**: Single quotes (`'`) treat everything as literal text—what you see is what you get. Double quotes (`"`) enable variable interpolation and interpret escape sequences like `\n` (newline) and `\t` (tab). Single quotes are slightly faster since PHP doesn't need to scan the string, but the difference is negligible in modern PHP.

> **Note**: `PHP_EOL` is a predefined constant that outputs the correct newline character for your operating system (`\n` on Unix/Mac, `\r\n` on Windows).

### Troubleshooting

**Output shows "$name" instead of "Dale"**

- **Cause**: You used single quotes for interpolation.
- **Fix**: Variable interpolation only works with double quotes (`"`). Single quotes (`'`) treat everything as literal text.

**Error: "syntax error, unexpected '.'"**

- **Cause**: Missing quotes or mismatched quotes around strings.
- **Fix**: Check that every opening quote has a matching closing quote of the same type.

## Step 2b: Advanced String Syntax (~5 min)

**Goal**: Learn multiline strings and complex interpolation for more powerful text handling.

While single and double quotes work great for short strings, PHP offers additional syntax for working with longer, multiline text and complex variable interpolation.

### Heredoc Syntax (with Interpolation)

Heredoc is perfect for multiline strings that need variable interpolation. It starts with `<<<` followed by an identifier (commonly `EOT` for "End Of Text" or `HTML` for HTML content), and ends with that same identifier on its own line.

### Actions

1. **Create a Heredoc Example**:
   Create a new file called `heredoc.php`:

```php
# filename: heredoc.php
<?php

$name = "Dale";
$age = 41;
$role = "developer";

// Heredoc with interpolation (like double quotes)
$message = <<<EOT
Hello, my name is $name.
I am $age years old and I work as a $role.

This is perfect for:
  - Multiline text
  - Email templates
  - HTML content
EOT;

echo $message . PHP_EOL;
```

2. **Run the Script**:

```bash
php heredoc.php
```

### Expected Result

```text
Hello, my name is Dale.
I am 41 years old and I work as a developer.

This is perfect for:
  - Multiline text
  - Email templates
  - HTML content
```

### Nowdoc Syntax (Literal Text)

Nowdoc is like single quotes but for multiline strings—no variable interpolation. Use it by wrapping the identifier in single quotes.

3. **Add Nowdoc Example**:
   Update your `heredoc.php` file:

```php
# filename: heredoc.php
<?php

$name = "Dale";

// Nowdoc - no interpolation (like single quotes)
$template = <<<'EOT'
This is a literal string.
The variable $name will not be replaced.
Use this for code examples or templates.
EOT;

echo $template . PHP_EOL;
```

### Expected Result

```text
This is a literal string.
The variable $name will not be replaced.
Use this for code examples or templates.
```

### Complex Interpolation with Braces

When interpolating array values or complex expressions, you need to wrap them in curly braces `{}`:

4. **Create a Complex Interpolation Example**:
   Create a new file called `interpolation.php`:

```php
# filename: interpolation.php
<?php

$user = [
    'name' => 'Dale',
    'age' => 41
];

$price = 29.99;
$quantity = 3;

// Complex interpolation requires braces
echo "User: {$user['name']}, Age: {$user['age']}" . PHP_EOL;
echo "Total: ${$price * $quantity}" . PHP_EOL; // Note: $ inside {}
echo "Total (cleaner): " . ($price * $quantity) . PHP_EOL; // Concatenation is clearer
```

5. **Run the Script**:

```bash
php interpolation.php
```

### Expected Result

```text
User: Dale, Age: 41
Total: $89.97
Total (cleaner): 89.97
```

### Why It Works

- **Heredoc** (`<<<EOT`) works like double quotes—variables are interpolated and escape sequences are processed.
- **Nowdoc** (`<<<'EOT'`) works like single quotes—everything is literal, no interpolation.
- **Curly braces** `{}` tell PHP exactly what variable or expression to interpolate, which is required for arrays, object properties, and complex expressions.

> **Best Practice**: For complex expressions or calculations, use concatenation instead of inline interpolation—it's more readable and easier to debug.

### Troubleshooting

**Error: "syntax error, unexpected end of file"**

- **Cause**: The closing identifier must be on its own line with no whitespace before or after it (except the semicolon).
- **Fix**: Ensure the closing identifier (e.g., `EOT;`) is alone on a line:

```php
$text = <<<EOT
Some text
EOT; // Must be alone on the line, no spaces before it
```

**Array interpolation not working**

- **Cause**: You forgot the curly braces around array access.
- **Fix**: Use `{$array['key']}` not `$array['key']` in double-quoted strings.

**Heredoc treating everything as literal**

- **Cause**: You accidentally used Nowdoc syntax (single quotes around identifier).
- **Fix**: Use `<<<EOT` for interpolation, not `<<<'EOT'`.

## Step 3: Exploring Data Types (~5 min)

**Goal**: Understand PHP's primary data types and use `var_dump()` for debugging.

PHP is a "dynamically typed" language. This means you don't have to declare what type of data a variable will hold; PHP figures it out automatically at runtime. While modern PHP (8.0+) supports explicit type declarations for better code safety, understanding PHP's dynamic nature is essential.

### Common Scalar Types

The most common scalar (single value) types are:

- **`string`**: Text (e.g., `"Hello"`)
- **`int`**: Whole numbers (e.g., `10`, `-50`)
- **`float`** (or `double`): Numbers with a decimal point (e.g., `3.14`, `99.99`)
- **`bool`**: Represents `true` or `false`
- **`null`**: Represents "no value" (we'll explore this in later chapters)

PHP also performs **type juggling** (automatic type conversion) when needed. For example, `"5" + 3` becomes `8` because PHP converts the string `"5"` to an integer. While convenient, this can sometimes lead to unexpected behavior, so use `var_dump()` to verify your types.

### Actions

1.  **Create a Type Demonstration Script**:
    Create a new file called `types.php`:

```php
# filename: types.php
<?php

// Declaring variables with different types
$bookTitle = "The Lord of the Rings"; // string
$pageCount = 1216;                    // int
$price = 24.99;                       // float
$isPublished = true;                  // bool

echo "=== Variable Types ===" . PHP_EOL;
var_dump($bookTitle);
var_dump($pageCount);
var_dump($price);
var_dump($isPublished);

// Demonstrating type juggling
echo PHP_EOL . "=== Type Juggling ===" . PHP_EOL;
$stringNumber = "5";
$intNumber = 3;
$result = $stringNumber + $intNumber;
echo "\"5\" + 3 = ";
var_dump($result); // int(8) - PHP converts "5" to 5
```

2.  **Run and Inspect**:

```bash
php types.php
```

### Expected Result

You'll see detailed type information for each variable, followed by a demonstration of type juggling:

```text
=== Variable Types ===
string(23) "The Lord of the Rings"
int(1216)
float(24.99)
bool(true)

=== Type Juggling ===
"5" + 3 = int(8)
```

The number in parentheses after `string` is the character count. For `int` and `float`, it's the value itself. Notice how PHP automatically converts the string `"5"` to an integer when performing addition.

### Why It Works

`var_dump()` is PHP's built-in introspection function. It examines a variable's type and contents, then prints them in a human-readable format. This is incredibly useful for debugging because PHP's dynamic typing can sometimes produce unexpected type conversions.

**Other Debugging Functions**:

- `print_r()` — Shows values in a more readable format (but without type information)
- `var_export()` — Outputs valid PHP code representation of a value
- `gettype()` — Returns a string containing the type name

> **Tip**: Use `var_dump()` liberally when debugging. It's one of the most powerful tools for understanding what your code is doing.

### Troubleshooting

**Boolean shows as "bool(false)" with no output**

- **Cause**: PHP doesn't print anything for `false` booleans with `echo`, but `var_dump()` shows it correctly.
- **Fix**: Always use `var_dump()` to inspect boolean values.

**Number shows as string: "string(3) "123""**

- **Cause**: You wrapped the number in quotes, making it a string.
- **Fix**: Remove quotes from numbers: use `123` not `"123"`.

**Unexpected calculation results**

- **Cause**: Type juggling might be converting values in ways you don't expect.
- **Fix**: Use `var_dump()` on all values involved in the calculation to verify their types. For strict type comparisons, use `===` instead of `==` (we'll cover this in Chapter 03).

## Step 3b: Type Casting and Conversion (~4 min)

**Goal**: Learn to explicitly control type conversion instead of relying on automatic type juggling.

While PHP's automatic type juggling is convenient, sometimes you need precise control over type conversion. This is where **type casting** comes in—manually converting a value from one type to another.

### Why Type Casting Matters

Type juggling happens automatically but can be unpredictable. Type casting gives you explicit control:

```php
"5" + "3"     // 8 (automatic juggling)
(int)"5" + 3  // 8 (explicit casting)
(string)42    // "42" (explicit casting)
```

### Actions

1. **Create a Type Casting Demonstration**:
   Create a new file called `casting.php`:

```php
# filename: casting.php
<?php

echo "=== Type Casting Examples ===" . PHP_EOL . PHP_EOL;

// String to Integer
$stringNumber = "42";
$intNumber = (int)$stringNumber;
echo "String to Int:" . PHP_EOL;
var_dump($stringNumber); // string(2) "42"
var_dump($intNumber);    // int(42)
echo PHP_EOL;

// Float to Integer (truncates decimal)
$floatPrice = 19.99;
$intPrice = (int)$floatPrice;
echo "Float to Int (truncates):" . PHP_EOL;
var_dump($floatPrice); // float(19.99)
var_dump($intPrice);   // int(19)
echo PHP_EOL;

// Integer to String
$age = 41;
$ageString = (string)$age;
echo "Int to String:" . PHP_EOL;
var_dump($age);        // int(41)
var_dump($ageString);  // string(2) "41"
echo PHP_EOL;

// Boolean Conversions
echo "=== Boolean Conversions ===" . PHP_EOL;
var_dump((bool)"");      // bool(false) - empty string
var_dump((bool)"0");     // bool(false) - string "0"
var_dump((bool)"text");  // bool(true)  - non-empty string
var_dump((bool)0);       // bool(false) - zero
var_dump((bool)1);       // bool(true)  - non-zero
var_dump((bool)-1);      // bool(true)  - non-zero
echo PHP_EOL;

// Practical Example: User Input
$userInput = "123";
$productId = (int)$userInput; // Ensure it's an integer
echo "Processing product ID: ";
var_dump($productId); // int(123)
```

2. **Run the Script**:

```bash
php casting.php
```

### Expected Result

```text
=== Type Casting Examples ===

String to Int:
string(2) "42"
int(42)

Float to Int (truncates):
float(19.99)
int(19)

Int to String:
int(41)
string(2) "41"

=== Boolean Conversions ===
bool(false)
bool(false)
bool(true)
bool(false)
bool(true)
bool(true)

Processing product ID: int(123)
```

### Common Type Casts

| Cast       | Example         | Description                                 |
| :--------- | :-------------- | :------------------------------------------ |
| `(int)`    | `(int)"42"`     | Convert to integer (truncates decimals)     |
| `(float)`  | `(float)"3.14"` | Convert to floating-point number            |
| `(string)` | `(string)42`    | Convert to string                           |
| `(bool)`   | `(bool)1`       | Convert to boolean                          |
| `(array)`  | `(array)$value` | Convert to array (we'll cover arrays later) |

### Falsy Values in PHP

When casting to boolean, these values become `false`:

- `0` (integer zero)
- `0.0` (float zero)
- `""` (empty string)
- `"0"` (string containing only zero)
- `null` (the null value)
- `false` (boolean false)
- `[]` (empty array - covered in Chapter 06)

**Everything else** evaluates to `true` when cast to boolean.

### Why It Works

Type casting uses the syntax `(type)$variable` to explicitly convert a value. This is compile-time safe and makes your intentions clear. When you see `(int)$id`, you know the developer wants to ensure `$id` is an integer, preventing type-related bugs.

**Type Casting vs. Type Juggling**:

- **Juggling** (automatic): `"5" + 3` → PHP decides the conversion
- **Casting** (manual): `(int)"5" + 3` → You control the conversion

### Practical Use Cases

```php
// 1. Sanitizing user input
$userId = (int)$_GET['id']; // Ensure it's an integer

// 2. Formatting output
$price = 29.99;
$displayPrice = "$" . (string)$price; // Ensure string concatenation

// 3. Boolean checks
$hasAccess = (bool)$userPermissions; // Convert to true/false

// 4. Preventing decimal issues
$quantity = (int)$floatQuantity; // Truncate to whole number
```

> **Best Practice**: Use type casting when accepting external input (forms, URLs, APIs) to ensure data is the expected type before processing.

### Troubleshooting

**Unexpected `1` instead of `0` after casting**

- **Cause**: Non-zero numbers, even negative, cast to `true` (which displays as `1` with `echo`).
- **Fix**: Use `var_dump()` to see the actual boolean value, not `echo`.

**Float casting gives unexpected decimals**

- **Cause**: Floating-point precision issues (inherent to all programming languages).
- **Fix**: Use `number_format()` for display, and `round()` for calculations.

**String "123abc" becomes `123`**

- **Cause**: `(int)` casting reads from left to right and stops at the first non-numeric character.
- **Fix**: This is expected behavior. Use validation before casting if you need to reject invalid input.

## Step 4: Using Constants (~3 min)

**Goal**: Define and use constants for values that should never change.

Sometimes you need to use a value that should never change during the execution of your script, like a configuration setting or a mathematical value like PI. For this, you use **constants**.

### Actions

1.  **Create a Tax Calculator**:
    Create a new file called `constants.php`:

```php
# filename: constants.php
<?php

// Define a constant for the sales tax rate
const TAX_RATE = 0.08;

$productPrice = 150;
$totalPrice = $productPrice + ($productPrice * TAX_RATE);

echo "Product price: $" . $productPrice . PHP_EOL;
echo "Tax rate: " . (TAX_RATE * 100) . "%" . PHP_EOL;
echo "Total price: $" . $totalPrice . PHP_EOL;
```

2.  **Run the Calculator**:

```bash
php constants.php
```

### Expected Result

```text
Product price: $150
Tax rate: 8%
Total price: $162
```

### Why It Works

Constants are defined once and cannot be changed. Unlike variables, constants:

- Do **not** use the `$` prefix
- Cannot be redefined or undefined once set
- Are typically written in `UPPER_SNAKE_CASE` by convention
- Have global scope (accessible everywhere without passing them around)

**`const` vs `define()`**: You can define constants using the `const` keyword or the `define()` function. The `const` keyword is preferred for:

- Better performance (resolved at compile time)
- Cleaner syntax and improved readability
- Works at the top level and inside classes

Use `define()` only when you need to define constants conditionally or with dynamic names.

> **Note**: Attempting to change a constant's value will result in a fatal error, which is useful for preventing accidental modifications to critical configuration values.

### Troubleshooting

**Error: "Undefined constant TAX_RATE"**

- **Cause**: You tried to use the constant with a `$` prefix (`$TAX_RATE`) or misspelled it.
- **Fix**: Constants don't use `$`. Write `TAX_RATE`, not `$TAX_RATE`.

**Warning: "Constant TAX_RATE already defined"**

- **Cause**: You defined the same constant twice in your script.
- **Fix**: Remove the duplicate definition. Constants can only be defined once per script execution.

**Error: "Cannot redeclare constant TAX_RATE"**

- **Cause**: You tried to assign a new value to an existing constant.
- **Fix**: Constants are immutable. Use a variable if you need to change the value.

## Exercises

Practice what you've learned with these hands-on exercises:

1.  **Personal Profile** (~5 min):
    Create a script called `profile.php` that stores your first name, last name, and year of birth in variables. Then, print out a complete sentence using interpolation.

    **Hints**:

    - Use `camelCase` for variable names (e.g., `$firstName`, `$lastName`)
    - Use double quotes for string interpolation
    - Add `PHP_EOL` at the end for a clean newline

    **Expected output**:

    ```text
    My name is Dale Hurley. I was born in 1990.
    ```

2.  **Circle Calculator** (~8 min):
    Create a script called `circle.php` that calculates and prints the circumference and area of a circle.

    **Requirements**:

    - Store the radius in a variable (e.g., `$radius = 5;`)
    - Define a constant for PI: `const PI = 3.14159;`
    - Calculate the circumference: `2 * PI * radius`
    - Calculate the area: `PI * radius * radius` (or use `$radius ** 2` for squaring)
    - Print the results in a human-readable format
    - Use `var_dump()` to verify the data types of your calculated values

    **Hints**:

    - Use descriptive variable names like `$circumference` and `$area`
    - The `**` operator performs exponentiation (power), so `$radius ** 2` is the same as `$radius * $radius`
    - All calculations should result in `float` types

    **Expected output**:

    ```text
    Circle with radius: 5
    Circumference: 31.4159
    Area: 78.53975
    ```

3.  **Temperature Converter** (~8 min):
    Create a script called `temperature.php` that converts Celsius to Fahrenheit.

    **Requirements**:

    - Store a temperature in Celsius as a variable (e.g., `$celsius = 25;`)
    - Use the formula: `fahrenheit = (celsius * 9/5) + 32`
    - Print both temperatures with descriptive text
    - Use `var_dump()` to check the types of both values

    **Hints**:

    - The division `9/5` produces a `float` (1.8), making your result a `float`
    - Use parentheses to ensure correct order of operations
    - Consider using `number_format()` to control decimal places in output (e.g., `number_format($fahrenheit, 1)` for one decimal place)

    **Expected output**:

    ```text
    Temperature in Celsius: 25
    Temperature in Fahrenheit: 77
    ```

4.  **Type Casting Exercise** (~8 min):
    Create a script called `type-casting.php` that demonstrates safe type casting for user input simulation.

    **Requirements**:

    - Simulate user input with string variables: `$inputAge = '25'`, `$inputPrice = '19.99'`, `$inputQuantity = '3.7'`
    - Cast each input to the appropriate type
    - Calculate total price (price × quantity, with quantity as integer)
    - Check if age is valid for adult content (age >= 18)
    - Use `var_dump()` to show types before and after casting
    - Display a summary message

    **Hints**:

    - Cast `$inputAge` to `int` for comparison
    - Cast `$inputPrice` to `float` for calculation
    - Cast `$inputQuantity` to `int` (truncates to 3)
    - Use boolean casting to verify access

    **Expected output**:

    ```text
    === Before Casting ===
    string(2) "25"
    string(5) "19.99"
    string(3) "3.7"

    === After Casting ===
    int(25)
    float(19.99)
    int(3)

    Age verification: Adult access granted
    Order: 3 items at $19.99 each
    Total: $59.97
    ```

5.  **Bonus: Shopping Cart** (~10 min):
    Create a script called `cart.php` that calculates the total cost of items in a shopping cart with tax.

    **Requirements**:

    - Create variables for at least 3 product prices (e.g., `$item1 = 29.99;`)
    - Define a constant for the tax rate (e.g., `const TAX_RATE = 0.07;`)
    - Calculate the subtotal (sum of all items)
    - Calculate the tax amount (`subtotal * TAX_RATE`)
    - Calculate the final total (`subtotal + tax`)
    - Print a receipt showing each item, subtotal, tax, and total
    - Use `number_format($price, 2)` to format prices to 2 decimal places

    **Expected output**:

    ```text
    Item 1: $29.99
    Item 2: $15.50
    Item 3: $8.75
    ─────────────────
    Subtotal: $54.24
    Tax (7%): $3.80
    ─────────────────
    Total: $58.04
    ```

6.  **Super Bonus: Email Template with Heredoc** (~12 min):
    Create a script called `email.php` that generates a formatted email using Heredoc syntax.

    **Requirements**:

    - Use variables for: `$userName`, `$orderId`, `$orderTotal`, `$itemCount`
    - Create an email body using Heredoc that includes:
      - A personalized greeting
      - Order confirmation details
      - A thank you message
      - Footer with company info
    - Use proper formatting with line breaks

    **Expected output**:

    ```text
    Dear Dale,

    Thank you for your order!

    Order Details:
    Order ID: #12345
    Items: 3
    Total: $89.97

    Your order will be processed within 24 hours.

    Best regards,
    The PHP Store Team
    ```

## Wrap-up

Excellent work! You now have a solid understanding of how to store and manage data in PHP. Let's recap what you've learned:

### What You've Accomplished

- ✓ Declared and used variables with proper naming conventions (`camelCase`)
- ✓ Mastered string concatenation and interpolation (single vs. double quotes)
- ✓ Learned Heredoc and Nowdoc syntax for multiline strings
- ✓ Implemented complex string interpolation with curly braces
- ✓ Understood PHP's primary scalar data types and automatic type juggling
- ✓ Mastered explicit type casting for precise control over data types
- ✓ Learned PHP's falsy values and boolean conversion rules
- ✓ Used `var_dump()` and other debugging functions to inspect types
- ✓ Defined and used constants with `const` for immutable values
- ✓ Built practical scripts including a tax calculator
- ✓ Learned when to use `const` vs. `define()`

### Key Takeaways

- Variables use the `$` prefix and follow `camelCase` naming convention; constants use `UPPER_SNAKE_CASE` without `$`
- Double quotes enable interpolation and escape sequences; single quotes are literal
- Use Heredoc (`<<<EOT`) for multiline strings with interpolation; use Nowdoc (`<<<'EOT'`) for literal multiline text
- Complex interpolation (arrays, expressions) requires curly braces: `{$array['key']}`
- `var_dump()` is essential for debugging—it shows both type and value
- PHP is dynamically typed and performs automatic type juggling (implicit conversion)
- Type casting gives you explicit control: `(int)$value`, `(string)$value`, `(bool)$value`
- Know your falsy values: `0`, `0.0`, `""`, `"0"`, `null`, `false`, `[]`
- Use `const` for defining constants (preferred over `define()`)
- Constants have global scope and prevent accidental value changes
- Type casting is crucial when handling external input for security and correctness

These concepts are the absolute bedrock of programming in any language. Every application you build will rely heavily on variables, data types, and constants to manage state and perform calculations.

### Next Steps

In [Chapter 03: Control Structures](/series/php-basics/chapters/03-control-structures), you'll learn how to make decisions and repeat actions in your code using `if` statements and loops—bringing your scripts to life with logic and automation.

## Knowledge Check

Test your understanding of variables, data types, and constants:

<Quiz
title="Chapter 02 Quiz: Variables & Data Types"
:questions="[
{
question: 'Which of the following is a valid PHP variable name?',
options: [
{ text: '$my_variable', correct: true, explanation: 'Variables start with $ and can contain letters, numbers, and underscores.' },
{ text: '$2nd_variable', correct: false, explanation: 'Variable names cannot start with a number.' },
{ text: 'myVariable', correct: false, explanation: 'PHP variables must start with a $ symbol.' },
{ text: '$my-variable', correct: false, explanation: 'Hyphens are not allowed in variable names.' }
]
},
{
question: 'What does `declare(strict_types=1);` do?',
options: [
{ text: 'Enables strict type checking for function arguments and return types', correct: true, explanation: 'Strict types prevent automatic type conversion in function calls.' },
{ text: 'Forces all variables to have explicit types', correct: false, explanation: 'Variables in PHP don\'t require type declarations, only function parameters and returns.' },
{ text: 'Makes the code run faster', correct: false, explanation: 'While good practice, it doesn\'t affect performance significantly.' },
{ text: 'Prevents all type juggling', correct: false, explanation: 'It only affects function/method parameters and return types.' }
]
},
{
question: 'What is the difference between a constant and a variable?',
options: [
{ text: 'Constants cannot be changed after definition', correct: true, explanation: 'Once defined, constants remain the same throughout script execution.' },
{ text: 'Constants are faster than variables', correct: false, explanation: 'Performance difference is negligible in modern PHP.' },
{ text: 'Constants don\'t need the $ prefix', correct: true, explanation: 'Constants use define() or const and don\'t have the $ symbol.' },
{ text: 'Constants are case-sensitive', correct: false, explanation: 'By default, constants are case-sensitive, but this is a feature, not a difference.' }
]
},
{
question: 'What is type juggling in PHP?',
options: [
{ text: 'Automatic conversion between data types', correct: true, explanation: 'PHP automatically converts types when needed (e.g., \'5\' + 3 = 8).' },
{ text: 'A way to declare multiple types for one variable', correct: false, explanation: 'This describes union types, not type juggling.' },
{ text: 'An error that occurs when types don\'t match', correct: false, explanation: 'With strict_types=0 (default), PHP converts types instead of erroring.' },
{ text: 'A performance optimization technique', correct: false, explanation: 'Type juggling is about automatic conversion, not optimization.' }
]
},
{
question: 'Which data type would you use to store `true` or `false`?',
options: [
{ text: 'bool', correct: true, explanation: 'Boolean (bool) type is specifically for true/false values.' },
{ text: 'int', correct: false, explanation: 'While 1/0 can represent true/false, bool is the correct type.' },
{ text: 'string', correct: false, explanation: 'Strings like \'true\' are not the same as boolean true.' },
{ text: 'binary', correct: false, explanation: 'Binary is not a PHP data type.' }
]
}
]"
/>

## Further Reading

- [PHP Manual: Variables](https://www.php.net/manual/en/language.variables.php) — Official PHP documentation on variables
- [PHP Manual: Types](https://www.php.net/manual/en/language.types.php) — Complete guide to PHP data types including type juggling
- [PHP Manual: Constants](https://www.php.net/manual/en/language.constants.php) — Deep dive into constants and their usage
- [PHP 8.4 Release Notes](https://www.php.net/releases/8.4/) — What's new in PHP 8.4
- [Type Declarations](https://www.php.net/manual/en/language.types.declarations.php) — Learn about strict typing in modern PHP
