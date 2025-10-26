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

## What You'll Build

By the end of this chapter, you'll have:

- `functions.php` — A playground script showcasing function definitions, parameters, and return values
- `strict-functions.php` — Examples using type declarations and strict mode
- `named-arguments.php` — Demonstrations of named arguments and default values
- `arrow-functions.php` — Concise helper functions using PHP's arrow syntax
- Practice exercises that reinforce scope, recursion, and reusable function design

## Objectives

- Define and call your own custom functions.
- Pass information to functions using parameters (arguments).
- Get information back from functions using `return` values.
- Understand variable scope and how it affects functions.
- Use modern PHP features like type declarations and strict mode for more robust code.
- Leverage named arguments for clearer, more maintainable function calls.
- Write concise arrow functions for simple operations.

## Step 1: Defining and Calling a Function (~3 min)

### Goal

Create and execute your first custom function.

### Actions

1. **Create a file** named `functions.php`.
2. **Add the code** below to define and call a simple function:

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

3. **Run the script**:

```bash
php functions.php
```

### Expected Result

```
Hello, world!
Hello, world!
```

### Why It Works

- The `function` keyword defines a reusable block of code.
- Calling `sayHello()` executes the logic inside the function body every time.
- Functions help you avoid repeating logic across your code base.

### Troubleshooting

- **`Call to undefined function`** — Ensure the function is defined before you call it and that filenames match.
- **No output** — Confirm the function contains an `echo` statement and that you’re calling it after the definition.

## Step 2: Passing Information with Parameters (~4 min)

### Goal

Make functions flexible by passing data into them via parameters.

### Actions

1. **Update your file** with a parameterized function:

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

2. **Run the script** to confirm the output.

3. **Add default values** so the function works even when no argument is provided:

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

### Expected Result

```
Hello, Dale!
Hello, Alice!
Hello, Dale!
Hello, Guest!
```

### Why It Works

- Parameters act as placeholders; when you call the function, the arguments replace those placeholders.
- Default values provide fallback behavior, making functions more resilient when arguments are omitted.

### Troubleshooting

- **`Too few arguments to function`** — Add a default value or ensure every call provides the required parameters.
- **Output still shows `Guest`** — Double-check that the function call is passing the expected value.

## Step 3: Getting Information Back with `return` (~4 min)

### Goal

Return values from functions so results can be reused elsewhere in your program.

### Actions

1. **Create a function that returns a value**:

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

2. **Run the script**:

```bash
php functions.php
```

### Expected Result

```
The sum is: 15
Another sum is: 300
```

### Why It Works

- The `return` keyword sends the computed value back to the caller and halts the function immediately.
- Returned values can be stored in variables, passed to other functions, or echoed directly.

### Troubleshooting

- **Script prints `This will not be printed.`** — Ensure the `return` statement appears before any cleanup `echo` statements.
- **`null` is returned** — Confirm every code path in the function returns a value.

## Step 4: Type Declarations (Strict Typing) (~5 min)

### Goal

Add type safety to your functions to catch bugs early and make code more predictable.

### Actions

1. **Add parameter and return type declarations**:

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

2. **Enable strict mode** so PHP enforces type declarations:

```php
# filename: strict-functions.php
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
// $wrongSum = add(5, "5"); // TypeError: Argument #2 must be of type int.
```

3. **Run each script** to see how PHP behaves with and without strict mode.

### Expected Result

```
The sum is: 15
```

In strict mode, passing a string yields a `TypeError` instead of silently converting types.

### Why It Works

- Parameter type declarations (`int $num1`) and return types (`: int`) ensure values adhere to expected types.
- `declare(strict_types=1);` forces PHP to throw errors when type declarations are violated instead of performing automatic conversions.

### Troubleshooting

- **TypeError when passing strings** — This is expected in strict mode. Cast the value (`(int)` or `(float)`) before passing it to the function.
- **Strict types ignored** — Ensure `declare(strict_types=1);` is the very first line in the file, before any `namespace` or `use` statements.

## Step 5: Named Arguments for Clarity (~4 min)

### Goal

Use named arguments to make function calls self-documenting and flexible.

### Actions

1. **Create a function with multiple parameters**:

```php
# filename: named_args.php
<?php

function createUser(string $name, string $email, bool $isAdmin = false): array
{
    return [
        'name' => $name,
        'email' => $email,
        'admin' => $isAdmin,
    ];
}
```

2. **Call the function using positional arguments**:

```php
$user = createUser('Dale Hurley', 'dale@example.com', true);
print_r($user);
```

3. **Call the same function using named arguments**:

```php
$user = createUser(
    email: 'alice@example.com',
    name: 'Alice Johnson',
    isAdmin: true,
);
print_r($user);

// You can omit optional parameters when using named arguments.
$guest = createUser(
    name: 'Guest User',
    email: 'guest@example.com'
);
print_r($guest);
```

### Expected Result

```
Array
(
    [name] => Dale Hurley
    [email] => dale@example.com
    [admin] => 1
)
Array
(
    [name] => Alice Johnson
    [email] => alice@example.com
    [admin] => 1
)
Array
(
    [name] => Guest User
    [email] => guest@example.com
    [admin] =>
)
```

### Why It Works

- Named arguments allow values to be passed by parameter name instead of position, improving readability and making code resilient to parameter reordering.
- Optional parameters with default values (`$isAdmin = false`) can be omitted when unnecessary.
- Mixing positional and named arguments is allowed, but named arguments must come last.

### Troubleshooting

- **`Unknown named parameter`** — Double-check the parameter names in the function definition and the call.
- **`Argument cannot be passed by name`** — Variadic parameters (`...$items`) or parameters that use the same name multiple times can’t be passed by name.

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

## Step 7: Writing Concise Arrow Functions (~4 min)

### Goal

Use arrow functions for short, expression-based helpers.

### Actions

1. **Create arrow function examples**:

```php
# filename: arrow-functions.php
<?php

$numbers = [1, 2, 3, 4, 5];

// Traditional anonymous function
$doubled = array_map(function (int $number): int {
    return $number * 2;
}, $numbers);

print_r($doubled);

// Arrow function version
$doubledArrow = array_map(fn (int $number): int => $number * 2, $numbers);
print_r($doubledArrow);
```

2. **Run the script**:

```bash
php arrow-functions.php
```

### Expected Result

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
    [0] => 2
    [1] => 4
    [2] => 6
    [3] => 8
    [4] => 10
)
```

### Why It Works

- Arrow functions (`fn ($x) => $x * 2`) provide a more compact syntax for simple, single-expression callbacks.
- They automatically inherit variables from the parent scope, removing the need for `use (...)` in most cases.
- For multi-line logic or complex operations, stick with traditional anonymous functions for clarity.

### Troubleshooting

- **`fn` keyword not recognized** — Arrow functions were introduced in PHP 7.4. Ensure your environment runs PHP 7.4 or later (this project uses PHP 8.4).
- **Unexpected scope issues** — Remember that arrow functions use implicit `use` semantics. If you need separate scope control, use a traditional `function` closure.

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

::: info Code Examples
Complete, runnable examples from this chapter are available in:

- [`basic-functions.php`](../code/04-functions/basic-functions.php) - Basic function syntax and usage
- [`scope-variadic.php`](../code/04-functions/scope-variadic.php) - Variable scope and variadic functions
- [`arrow-closures.php`](../code/04-functions/arrow-closures.php) - Arrow functions and closures
- [`solutions/`](../code/04-functions/solutions/) - Solutions to chapter exercises
  :::

## Further Reading

- [PHP Manual: Functions](https://www.php.net/manual/en/language.functions.php) — Official documentation on functions
- [PHP Manual: Type Declarations](https://www.php.net/manual/en/language.types.declarations.php) — Detailed guide on type hints and return types
- [PHP: The Right Way - Functions](https://phptherightway.com/#functions) — Best practices for writing functions
- [PSR-12: Extended Coding Style Guide](https://www.php-fig.org/psr/psr-12/) — Coding standards for function formatting

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
