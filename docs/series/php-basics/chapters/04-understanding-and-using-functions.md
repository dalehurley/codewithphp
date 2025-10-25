---
title: "04: Understanding and Using Functions"
description: "Learn how to write clean, reusable, and organized code by bundling logic into functions."
series: "php-basics"
chapter: 4
order: 4
difficulty: "Beginner"
prerequisites:
  - "/series/php-basics/chapters/03-control-structures"
---

# Chapter 04: Understanding and Using Functions

## Overview

As your programs grow, you'll find yourself writing the same pieces of code over and over again. This is not only tedious but also makes your code harder to maintain. If you need to change that logic, you have to find and update every place you copied it.

Functions solve this problem. A function is a named, reusable block of code that performs a specific task. You can "call" the function whenever you need to perform that task, which makes your code more organized, readable, and efficient—a principle known as **DRY** (Don't Repeat Yourself).

By the end of this chapter, you'll be able to write clean, reusable functions with parameters, return values, and type declarations—the foundation of professional PHP development.

## Prerequisites

Before starting this chapter, you should:

- Have PHP 8.4 or higher installed and accessible from your terminal.
- Understand basic PHP syntax: variables, data types, and control structures.
- Have completed [Chapter 03: Control Structures](/series/php-basics/chapters/03-control-structures).
- **Estimated Time**: 30–35 minutes

## Objectives

- Define and call your own custom functions.
- Pass information to functions using parameters (arguments).
- Get information back from functions using `return` values.
- Understand variable scope and how it affects functions.
- Use modern PHP features like type declarations and strict mode for more robust code.
- Leverage named arguments for clearer, more maintainable function calls.
- Write concise arrow functions for simple operations.

## Step 1: Defining and Calling a Function (~3 min)

**Goal**: Create and execute your first custom function.

Let's start with the basics: creating a function and then running it.

1.  **Create a File**:
    Create a new file named `functions.php`.

2.  **Define and Call a Simple Function**:
    The syntax for defining a function starts with the `function` keyword, followed by the function's name and a set of parentheses. The code that belongs to the function goes inside curly braces `{}`.

```php
# filename: functions.php
<?php

// Define the function
function sayHello()
{
    echo "Hello, world!" . PHP_EOL;
}

// Call the function
sayHello();
sayHello();
```

3.  **Run the Script**:

```bash
# Execute the script
php functions.php
```

**Expected Result**:

```
Hello, world!
Hello, world!
```

**Why it works**: We defined the logic once inside `sayHello()` and then called it twice. Each call executes the code inside the function's body. This is the essence of code reuse—write once, use many times.

## Step 2: Passing Information with Parameters (~4 min)

**Goal**: Make functions flexible by passing data into them.

Functions become much more powerful when you can pass data into them. We do this using **parameters** (also called **arguments**), which are variables declared inside the function's parentheses.

1.  **Update your file with a parameterized function**:

```php
# filename: functions.php
<?php

// The $name parameter acts as a placeholder for the data we'll pass in.
function greetUser($name)
{
    echo "Hello, $name!" . PHP_EOL;
}

greetUser('Dale');   // 'Dale' is the argument passed to the $name parameter.
greetUser('Alice');  // 'Alice' is the argument.
```

**Expected Result**:

```
Hello, Dale!
Hello, Alice!
```

2.  **Add Default Values**:
    You can provide **default values** for parameters, which are used if an argument isn't provided when the function is called.

```php
# filename: functions.php
<?php

function greetUser($name = 'Guest')
{
    echo "Hello, $name!" . PHP_EOL;
}

greetUser('Dale'); // Prints "Hello, Dale!"
greetUser();       // Prints "Hello, Guest!" because no argument was provided.
```

**Expected Result**:

```
Hello, Dale!
Hello, Guest!
```

**Why it works**: Parameters act as placeholders. When you call the function with an argument, that value replaces the parameter throughout the function's body. Default values provide fallback behavior, making functions more flexible.

## Step 3: Getting Information Back with `return` (~4 min)

**Goal**: Learn how to send values back from functions for reuse.

So far, our functions have only printed text directly. This is often not what you want. A function is most useful when it performs a calculation or a task and then **returns** a result that you can store in a variable or use elsewhere.

The `return` keyword immediately stops the function's execution and sends a value back.

1.  **Create a function that returns a value**:

```php
# filename: functions.php
<?php

function add($num1, $num2)
{
    $result = $num1 + $num2;
    return $result;

    // Any code after the return statement will NOT be executed.
    echo "This will not be printed.";
}

$sum = add(5, 10); // The returned value (15) is stored in the $sum variable.
echo "The sum is: " . $sum . PHP_EOL;

$anotherSum = add(100, 200);
echo "Another sum is: " . $anotherSum . PHP_EOL;
```

**Expected Result**:

```
The sum is: 15
Another sum is: 300
```

**Why it works**: The `return` statement sends the calculated value back to the place where the function was called. This value can then be stored in a variable, passed to another function, or used in any expression. Once `return` executes, the function stops immediately—any code after it is ignored.

## Step 4: Type Declarations (Strict Typing) (~5 min)

**Goal**: Add type safety to your functions to catch bugs early and make code more predictable.

Modern PHP encourages you to be explicit about the types of data your functions expect and return. This makes your code more predictable and helps catch bugs early.

You can add **type declarations** (or "type hints") before parameter names and a **return type declaration** after the parentheses.

1.  **Add type declarations to your function**:

```php
# filename: functions.php
<?php

// This function now expects two integers and is guaranteed to return an integer.
function add(int $num1, int $num2): int
{
    return $num1 + $num2;
}

$sum = add(5, 10);
echo "The sum is: " . $sum . PHP_EOL;

// What happens if we pass the wrong type?
// $wrongSum = add(5, 'ten'); // This will cause a TypeError.
```

**Expected Result**:

```
The sum is: 15
```

2.  **Enable Strict Mode** (Best Practice):
    To enforce these types strictly, add a `declare` statement at the very top of your file. This is considered a best practice in modern PHP.

```php
# filename: functions.php
<?php

declare(strict_types=1); // Must be the very first statement after <?php

function add(int $num1, int $num2): int
{
    return $num1 + $num2;
}

$sum = add(5, 10);
echo "The sum is: " . $sum . PHP_EOL;

// Now, even if PHP could normally convert the types (e.g., add(5, "5")),
// it will throw an error because strict mode is enabled.
// This prevents unexpected behavior.
```

**Expected Result**:

```
The sum is: 15
```

**Why it works**: Type declarations tell PHP exactly what type of data a function expects and returns. Without `declare(strict_types=1)`, PHP will try to convert types automatically (e.g., `"5"` becomes `5`). With strict mode enabled, PHP requires exact type matches, catching potential bugs at runtime. This is especially valuable in larger codebases.

## Step 5: Named Arguments for Clarity (~4 min)

**Goal**: Use named arguments to make function calls more readable and flexible.

One of PHP 8.0's best features is **named arguments**. Instead of passing arguments by position, you can specify them by name. This makes your code self-documenting and allows you to skip optional parameters.

1.  **Create a function with multiple parameters**:

```php
# filename: named_args.php
<?php

declare(strict_types=1);

function createUser(string $name, string $email, bool $isAdmin = false, bool $isActive = true): string
{
    $status = $isAdmin ? 'Admin' : 'User';
    $activity = $isActive ? 'Active' : 'Inactive';

    return "$name ($email) - $status - $activity";
}

// Traditional positional arguments (harder to read)
echo createUser('Dale', 'dale@example.com', false, true) . PHP_EOL;

// Named arguments (self-documenting and clear)
echo createUser(
    name: 'Alice',
    email: 'alice@example.com',
    isAdmin: true,
    isActive: true
) . PHP_EOL;

// Skip optional parameters - only set what you need
echo createUser(
    name: 'Bob',
    email: 'bob@example.com',
    isAdmin: true  // isActive uses default value
) . PHP_EOL;
```

**Expected Result**:

```
Dale (dale@example.com) - User - Active
Alice (alice@example.com) - Admin - Active
Bob (bob@example.com) - Admin - Active
```

2.  **Mix positional and named arguments**:
    You can mix both styles, but positional arguments must come first:

```php
# filename: named_args.php
<?php

declare(strict_types=1);

function greetUser(string $greeting, string $name, string $punctuation = '!'): string
{
    return "$greeting, $name$punctuation";
}

// Mix positional and named
echo greetUser('Hello', name: 'Charlie', punctuation: '.') . PHP_EOL;
```

**Expected Result**:

```
Hello, Charlie.
```

**Why it works**: Named arguments bind values to parameters by name rather than position. This eliminates the need to remember parameter order, makes function calls self-explanatory, and lets you skip optional parameters in the middle. This is especially useful for functions with many parameters or several optional ones.

::: tip
Use named arguments when calling functions with multiple boolean parameters or when skipping optional parameters. They make your code dramatically more readable and maintainable.
:::

## Step 6: Understanding Variable Scope (~3 min)

**Goal**: Learn how functions isolate variables to prevent unintended side effects.

An important concept to understand is **variable scope**. Variables defined _inside_ a function are local to that function; they cannot be accessed from outside of it, and variables from outside cannot be accessed from within it. This prevents functions from accidentally modifying variables they shouldn't.

```php
# filename: scope.php
<?php

$globalMessage = "This is a global variable.";

function myFunction()
{
    $localMessage = "This is a local variable.";
    echo $localMessage . PHP_EOL; // Works fine.
    // echo $globalMessage; // This will cause an error because it's out of scope.
}

myFunction();
// echo $localMessage; // This will also cause an error.
```

**Expected Result**:

```
This is a local variable.
```

**Why it works**: Each function creates its own "scope"—a private space for variables. Variables inside the function can't leak out, and variables outside can't leak in (unless explicitly passed as parameters). This isolation is a feature, not a limitation—it prevents functions from accidentally breaking each other's data.

## Step 7: Arrow Functions for Quick Operations (~3 min)

**Goal**: Learn a concise syntax for simple, single-expression functions.

PHP 7.4 introduced **arrow functions** (also called "short closures"). They're a shorter syntax for writing small, simple functions—especially useful when working with array functions like `array_map()`, `array_filter()`, and `usort()`.

The syntax uses `fn` instead of `function` and automatically returns the result of the expression:

```php
# filename: arrow_functions.php
<?php

declare(strict_types=1);

// Traditional function
function double(int $n): int
{
    return $n * 2;
}

// Arrow function (much shorter!)
$double = fn(int $n): int => $n * 2;

echo double(5) . PHP_EOL;        // 10
echo $double(5) . PHP_EOL;       // 10
```

**Expected Result**:

```
10
10
```

**Arrow Functions with Array Operations**:

Arrow functions really shine when working with arrays (we'll cover these array functions more in Chapter 06):

```php
# filename: arrow_functions.php
<?php

declare(strict_types=1);

$numbers = [1, 2, 3, 4, 5];

// Double each number using array_map
$doubled = array_map(fn($n) => $n * 2, $numbers);
print_r($doubled);

// Filter to only even numbers using array_filter
$evens = array_filter($numbers, fn($n) => $n % 2 === 0);
print_r($evens);

// Calculate squares
$squares = array_map(fn($n) => $n ** 2, $numbers);
print_r($squares);
```

**Expected Result**:

```
Array
(
    [0] => 2
    [1] => 4
    [2] => 6
    [3] => 8
    [4] => 10
)
Array
(
    [1] => 2
    [3] => 4
)
Array
(
    [0] => 1
    [1] => 4
    [2] => 9
    [3] => 16
    [4] => 25
)
```

**Arrow Functions Automatically Capture Variables**:

Unlike regular anonymous functions, arrow functions automatically have access to variables from the parent scope:

```php
# filename: arrow_functions.php
<?php

declare(strict_types=1);

$multiplier = 10;

$numbers = [1, 2, 3];

// Arrow function automatically accesses $multiplier
$result = array_map(fn($n) => $n * $multiplier, $numbers);
print_r($result);
```

**Expected Result**:

```
Array
(
    [0] => 10
    [1] => 20
    [2] => 30
)
```

**Why it works**: Arrow functions are syntactic sugar for simple operations. The `fn` keyword defines the function, `=>` separates parameters from the expression, and the result is automatically returned. They can only contain a single expression (not multiple statements), which makes them perfect for quick transformations.

::: tip
Use arrow functions for simple, one-line operations—especially with array functions. For anything more complex (multiple statements, multiple lines), use regular functions.
:::

## Troubleshooting

### Error: "Call to undefined function functionName()"

**Cause**: You're trying to call a function before it's been defined, or you have a typo in the function name.

**Fix**: Ensure the function is defined before you call it, or check the spelling. PHP function names are case-insensitive, but it's best practice to match the case exactly.

### Error: "Too few arguments to function functionName()"

**Cause**: You're calling a function without providing all required parameters.

**Fix**: Check the function definition and provide all required arguments. Remember, parameters with default values are optional.

### Error: "TypeError: Argument 1 passed to functionName() must be of the type int, string given"

**Cause**: You've passed the wrong data type to a typed parameter, and strict types are enabled.

**Fix**: Pass the correct type, or remove type declarations if flexibility is more important than type safety.

## Exercises

### 1. Area Calculator

Write a function named `calculateRectangleArea` that accepts two arguments, `$width` and `$height`. It should `return` the area of the rectangle. Use strict typing to ensure the parameters and return value are `float`s.

**Expected behavior**:

```php
<?php

declare(strict_types=1);

// Your function here

$area = calculateRectangleArea(10.5, 20.0);
echo "Area: " . $area . PHP_EOL;
```

**Expected output**: `Area: 210`

### 2. String Reverser

Create a function named `reverseString` that takes a `string` as an argument and returns the string in reverse. You can use the built-in PHP function `strrev()` to help you. Add strict typing.

**Expected behavior**:

```php
<?php

declare(strict_types=1);

// Your function here

$reversed = reverseString("Hello, PHP!");
echo $reversed . PHP_EOL;
```

**Expected output**: `!PHP ,olleH`

### 3. Temperature Converter (Challenge)

Create a function `celsiusToFahrenheit` that converts Celsius to Fahrenheit using the formula: `F = (C × 9/5) + 32`. Use `float` types for both parameter and return value.

**Expected behavior**:

```php
<?php

declare(strict_types=1);

// Your function here

$fahrenheit = celsiusToFahrenheit(0.0);
echo "0°C is " . $fahrenheit . "°F" . PHP_EOL;

$fahrenheit = celsiusToFahrenheit(100.0);
echo "100°C is " . $fahrenheit . "°F" . PHP_EOL;
```

**Expected output**:

```
0°C is 32°F
100°C is 212°F
```

### 4. Named Arguments Practice

Create a function `formatPrice` that accepts: `$amount` (float), `$currency` (string, default `'USD'`), `$showSymbol` (bool, default `true`), and `$decimals` (int, default `2`). The function should return a formatted price string.

**Expected behavior**:

```php
<?php

declare(strict_types=1);

// Your function here

// Using named arguments for clarity
echo formatPrice(amount: 99.5) . PHP_EOL;
echo formatPrice(amount: 1234.567, decimals: 3) . PHP_EOL;
echo formatPrice(amount: 50.0, currency: 'EUR', showSymbol: false) . PHP_EOL;
```

**Expected output**:

```
$99.50 USD
$1234.567 USD
50.00 EUR
```

### 5. Arrow Functions with Arrays

Using arrow functions, create a variable `$prices` with values `[10.5, 25.0, 99.99, 5.25]`. Then:

1. Use `array_map()` with an arrow function to add 10% tax to each price
2. Use `array_filter()` with an arrow function to find all prices over 20

**Expected behavior**:

```php
<?php

declare(strict_types=1);

$prices = [10.5, 25.0, 99.99, 5.25];

// Your arrow functions here

print_r($withTax);
print_r($expensive);
```

**Expected output**:

```
Array
(
    [0] => 11.55
    [1] => 27.5
    [2] => 109.989
    [3] => 5.775
)
Array
(
    [1] => 25
    [2] => 99.99
)
```

## Wrap-up

Congratulations! You've mastered one of the most important concepts in programming: functions. Here's what you achieved:

- ✅ Created reusable functions to eliminate code duplication
- ✅ Used parameters to make functions flexible and configurable
- ✅ Leveraged `return` values to get data back from functions
- ✅ Applied type declarations and strict mode for safer, more maintainable code
- ✅ Used named arguments to make function calls self-documenting
- ✅ Wrote concise arrow functions for simple operations
- ✅ Understood variable scope to write cleaner, more isolated logic

Functions are the foundation of the **DRY principle** (Don't Repeat Yourself) and are essential for building scalable applications. As your projects grow, you'll find yourself organizing more and more logic into well-named, single-purpose functions.

The modern PHP features you learned—strict typing, named arguments, and arrow functions—will make your code more readable, maintainable, and professional. These aren't just "nice to have" features; they're the standard in modern PHP development.

**Next Steps**:
In the next chapter, we'll make our applications interactive by learning how to process data submitted from HTML forms.

## Knowledge Check

Test your understanding of PHP functions:

<Quiz
title="Chapter 04 Quiz: Functions"
:questions="[
{
question: 'What does the return statement do in a function?',
options: [
{ text: 'Sends a value back to the caller and exits the function immediately', correct: true, explanation: 'Return both provides a value to the caller and stops execution of the function—any code after return is not executed.' },
{ text: 'Only exits the function without returning a value', correct: false, explanation: 'Return does exit, but it also sends back a value (or null if no value specified).' },
{ text: 'Prints the value to the screen', correct: false, explanation: 'That\'s what echo does. Return gives the value back to the calling code.' },
{ text: 'Assigns a value to a variable', correct: false, explanation: 'Return sends a value back; assignment uses the = operator.' }
]
},
{
question: 'What is the purpose of declare(strict_types=1)?',
options: [
{ text: 'Enforces type checking for function arguments and return values', correct: true, explanation: 'Strict types mode prevents automatic type coercion in function calls, catching type errors early.' },
{ text: 'Makes all variables require type declarations', correct: false, explanation: 'Strict types only affects function parameters and return types, not regular variables.' },
{ text: 'Improves code performance', correct: false, explanation: 'Strict types is about type safety, not performance.' },
{ text: 'Prevents all type juggling in PHP', correct: false, explanation: 'It only affects function signatures, not general operations.' }
]
},
{
question: 'What is the benefit of using named arguments when calling a function?',
options: [
{ text: 'Makes code self-documenting and allows skipping default parameters', correct: true, explanation: 'Named arguments let you specify which parameter you\'re setting, improving readability and allowing you to skip optional parameters in any order.' },
{ text: 'Makes the function run faster', correct: false, explanation: 'Named arguments don\'t affect performance; they improve code clarity.' },
{ text: 'Required for all functions in PHP 8.4', correct: false, explanation: 'Named arguments are optional; positional arguments still work.' },
{ text: 'Prevents type errors', correct: false, explanation: 'Named arguments are about clarity, not type safety.' }
]
},
{
question: 'When should you use an arrow function (fn) instead of a regular function?',
options: [
{ text: 'For simple, single-expression operations', correct: true, explanation: 'Arrow functions are perfect for short, one-line operations and automatically return the expression result.' },
{ text: 'For complex functions with multiple statements', correct: false, explanation: 'Arrow functions can only contain a single expression; use regular functions for multi-statement logic.' },
{ text: 'When you need to modify global variables', correct: false, explanation: 'Arrow functions automatically capture variables but aren\'t specifically for modification.' },
{ text: 'Always, as they\'re faster than regular functions', correct: false, explanation: 'Arrow functions are syntactic sugar; performance is equivalent to regular functions.' }
]
},
{
question: 'What happens when you try to access a variable defined inside a function from outside that function?',
options: [
{ text: 'You get an undefined variable error', correct: true, explanation: 'Variables defined inside functions have local scope and aren\'t accessible outside the function.' },
{ text: 'The variable value is returned automatically', correct: false, explanation: 'You must explicitly use return to get values from a function.' },
{ text: 'The variable becomes global', correct: false, explanation: 'Variables are local by default; you\'d need the global keyword to access global variables.' },
{ text: 'PHP creates a new variable with the same name', correct: false, explanation: 'PHP throws an error because the variable doesn\'t exist in that scope.' }
]
}
]"
/>

## Further Reading

- [PHP Manual: Functions](https://www.php.net/manual/en/language.functions.php) — Official documentation on functions
- [PHP Manual: Type Declarations](https://www.php.net/manual/en/language.types.declarations.php) — Detailed guide on type hints and return types
- [PHP: The Right Way - Functions](https://phptherightway.com/#functions) — Best practices for writing functions
- [PSR-12: Extended Coding Style Guide](https://www.php-fig.org/psr/psr-12/) — Coding standards for function formatting
