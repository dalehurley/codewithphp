---
title: "03: Control Structures"
description: "Learn how to make decisions and repeat actions in your code using conditionals (if/else) and loops (for, while, foreach)."
series: "php-basics"
chapter: 3
order: 3
difficulty: "Beginner"
prerequisites:
  - "/series/php-basics/chapters/02-variables-data-types-and-constants"
---

# Chapter 03: Control Structures

## Overview

So far, our scripts have run in a straight line, from top to bottom. But to build interesting applications, our code needs to be able to make decisions and perform repetitive tasks. For example, we might want to show a "Welcome back!" message if a user is logged in, or display a list of products from a database.

This is where control structures come in. They allow us to control the "flow" of our code's execution. In this chapter, you'll learn about the two main types: conditionals (for making decisions) and loops (for repeating actions).

## Prerequisites

- PHP 8.4 installed and working (verify with `php --version`)
- A text editor or IDE
- Basic understanding of variables, data types, and arrays from previous chapters
- Estimated time: ~30 minutes

## What You'll Build

By the end of this chapter, you'll have:

- `control-structures.php` — A playground file showcasing conditionals and loop patterns
- `switch-vs-match.php` — Examples comparing `switch` statements and modern `match` expressions
- `loop-control.php` — Demonstrations of `break`, `continue`, and common loop pitfalls
- A set of array iteration patterns using `foreach`
- Practical exercises such as FizzBuzz, multiplication tables, and a number guessing game

## Objectives

- Use `if`, `elseif`, and `else` to execute code based on conditions
- Understand comparison operators (`==`, `===`, `!=`, `!==`, `>`, `>=`, `<`, `<=`)
- Combine conditions with logical operators (`&&`, `||`, `!`)
- Use `switch` statements and modern `match` expressions for multiple conditions
- Apply ternary operator (`? :`) and null coalescing operator (`??`) for concise conditionals
- Use `for` loops to repeat a task a specific number of times
- Use `while` loops to repeat a task as long as a condition is true
- Use `foreach` loops to easily iterate over arrays
- Control loop flow with `break` and `continue` statements
- Troubleshoot common control structure issues

## Step 1: Making Decisions with `if`, `elseif`, and `else` (~5 min)

### Goal

Use conditional statements to execute different code paths based on the current state.

### Actions

Think of an `if` statement as a decision tree: **if** a condition is true, run a block of code; otherwise, skip it. You can extend the decision tree with `elseif` and provide a fallback with `else`.

1. **Create a file** called `control-structures.php`.
2. **Add the following code**:

```php
# filename: control-structures.php
<?php

$hour = date('H'); // Gets the current hour in 24-hour format

if ($hour < 12) {
    echo "Good morning!";
} elseif ($hour < 18) {
    echo "Good afternoon!";
} else {
    echo "Good evening!";
}

echo PHP_EOL;
```

3. **Run the script**:

```bash
php control-structures.php
```

### Expected Result

The greeting changes based on the time of day. You might see:

```
Good afternoon!
```

### Why It Works

- `date('H')` returns the current hour as a zero-padded integer string (00–23).
- The condition `$hour < 12` evaluates to `true` if it’s before noon; otherwise PHP checks the next condition.
- Only the first matching branch executes, ensuring a single greeting is printed.

### Troubleshooting

- **Always prints “Good evening!”** — Confirm your system clock and timezone (`date_default_timezone_set`) are set correctly.
- **Syntax error near `elseif`** — Ensure all opening braces `{` have matching closing braces `}` and each conditional keyword is lowercase.
- **Output appears on the same line as the prompt** — Append `PHP_EOL` (as shown) to add a newline after the greeting.

### Understanding Comparison Operators

The condition inside the `if` parentheses must evaluate to either `true` or `false`. We create these conditions using comparison operators.

| Operator | Name                     | Example     | Result                                                |
| :------- | :----------------------- | :---------- | :---------------------------------------------------- |
| `==`     | Equal                    | `$a == $b`  | `true` if `$a` is equal to `$b` (type coercion)       |
| `===`    | Identical                | `$a === $b` | `true` if `$a` equals `$b` AND same type              |
| `!=`     | Not equal                | `$a != $b`  | `true` if `$a` is not equal to `$b`                   |
| `!==`    | Not identical            | `$a !== $b` | `true` if `$a` is not equal to `$b` OR different type |
| `<`      | Less than                | `$a < $b`   | `true` if `$a` is less than `$b`                      |
| `>`      | Greater than             | `$a > $b`   | `true` if `$a` is greater than `$b`                   |
| `<=`     | Less than or equal to    | `$a <= $b`  | `true` if `$a` is less or equal to `$b`               |
| `>=`     | Greater than or equal to | `$a >= $b`  | `true` if `$a` is greater or equal to `$b`            |

> **Pro Tip**: Always prefer the "identical" operator (`===`) over the "equal" operator (`==`). The identical operator checks that both the value _and_ the data type are the same, which prevents subtle bugs. For example, `0 == "0"` is true, but `0 === "0"` is false.

### Understanding Logical Operators

Often you'll need to combine multiple conditions. That's where logical operators come in.

| Operator | Name | Example               | Result                                         |
| :------- | :--- | :-------------------- | :--------------------------------------------- |
| `&&`     | And  | `$a > 5 && $b < 10`   | `true` if **both** conditions are true         |
| `\|\|`   | Or   | `$a > 5 \|\| $b < 10` | `true` if **at least one** condition is true   |
| `!`      | Not  | `!$isLoggedIn`        | Inverts the boolean value (true becomes false) |

**Example**:

```php
<?php

$age = 25;
$hasLicense = true;

if ($age >= 18 && $hasLicense) {
    echo "You can drive!" . PHP_EOL;
} else {
    echo "You cannot drive." . PHP_EOL;
}
```

## Step 2: Repeating Tasks with a `for` Loop (~3 min)

### Goal

Repeat an action a specific number of times using a `for` loop.

The `for` loop consists of three parts inside its parentheses, separated by semicolons:

1. **Initialization** — Runs once at the beginning (`$i = 1`).
2. **Condition** — Checked before each iteration. If it’s `true`, the loop continues (`$i <= 10`).
3. **Increment** — Runs at the end of each iteration (`$i++`).

### Actions

1. **Add the following code** to `control-structures.php` (or create a separate file if you prefer):

```php
<?php

// This loop will run 10 times.
for ($i = 1; $i <= 10; $i++) {
    echo "This is loop number " . $i . PHP_EOL;
}
```

2. **Run the script**:

```bash
php control-structures.php
```

### Expected Result

```
This is loop number 1
This is loop number 2
This is loop number 3
This is loop number 4
This is loop number 5
This is loop number 6
This is loop number 7
This is loop number 8
This is loop number 9
This is loop number 10
```

### Why It Works

- `$i` starts at 1 and increments by 1 after each iteration.
- The loop stops once `$i` becomes 11 because the condition `$i <= 10` fails.
- Concatenation (`.`) combines the static text with the current value of `$i`.

### Troubleshooting

- **Loop never stops** — Double-check the condition and increment. If you accidentally wrote `$i >= 10`, the loop won’t terminate.
- **No output** — Ensure the loop body contains an `echo` statement and that the script runs without syntax errors.
- **Numbers appear on one line** — Add `PHP_EOL` or `\n` to include line breaks.

## Step 3: Repeating Tasks with a `while` Loop (~3 min)

### Goal

Continue executing a block of code as long as a condition remains true.

### Actions

1. **Add the following code** to your script:

```php
<?php

$randomNumber = 0;
$count = 0;

// Keep looping until the number is 5.
while ($randomNumber !== 5) {
    $randomNumber = rand(1, 10); // Generate a random number between 1 and 10
    $count++;
    echo "Attempt #$count: The number is $randomNumber" . PHP_EOL;
}

echo "Success! It took $count attempts to get the number 5." . PHP_EOL;
```

2. **Run the script**:

```bash
php control-structures.php
```

### Expected Result

The output will vary, but it looks something like:

```
Attempt #1: The number is 3
Attempt #2: The number is 7
Attempt #3: The number is 5
Success! It took 3 attempts to get the number 5.
```

### Why It Works

- `while` evaluates the condition before each iteration. If it’s `true`, the loop executes; otherwise, it stops.
- The loop continues generating numbers until `rand()` returns 5.
- `$count` tracks how many attempts were needed, providing useful feedback at the end.

### Troubleshooting

- **Infinite loop** — Ensure the loop changes the variables in the condition (`$randomNumber`). Without updating it, the loop never exits.
- **Same number every time** — `rand()` is pseudo-random but should vary. If it doesn’t, check for mistakes or consider `mt_rand()`.
- **Off-by-one errors** — Make sure you’re comparing with the correct condition (`!== 5`). Using `!=` works too but strict comparison is safer for type checks.

## Step 4: Looping Through Arrays with `foreach` (~4 min)

### Goal

Iterate over arrays cleanly using `foreach` without managing indexes manually.

### Actions

1. **Loop through values**:

```php
<?php

$colors = ['Red', 'Green', 'Blue', 'Yellow'];

foreach ($colors as $color) {
    echo $color . PHP_EOL;
}
```

2. **Run the script**:

```bash
php control-structures.php
```

3. **Loop through keys and values**:

```php
<?php

$user = [
    'name' => 'Dale',
    'email' => 'dale@example.com',
    'role' => 'Admin'
];

foreach ($user as $key => $value) {
    echo "$key: $value" . PHP_EOL;
}
```

### Expected Result

```
Red
Green
Blue
Yellow
```

```
name: Dale
email: dale@example.com
role: Admin
```

### Why It Works

- `foreach` automatically assigns each array value to `$color` on each iteration.
- Using the `as $key => $value` syntax provides access to both the key and the value without manual indexing.
- `PHP_EOL` ensures each item prints on its own line across different operating systems.

### Troubleshooting

- **Undefined index errors** — These occur when using `for` with a numeric counter. `foreach` avoids this by iterating over actual elements.
- **Accidentally modifying the original array** — Remember that iterating by reference (e.g., `foreach ($array as &$value)`) alters the original array. Use references only when you intend to mutate the data.

## Step 5: Alternative Conditional Structures (~6 min)

### Goal

Compare `switch`, `match`, and other concise conditional operators to choose the right tool for each scenario.

### Actions

1. **Use `switch` for multiple value comparisons**:

```php
<?php

$day = 'Tuesday';

switch ($day) {
    case 'Monday':
        echo "Start of the work week!" . PHP_EOL;
        break;
    case 'Tuesday':
    case 'Wednesday':
    case 'Thursday':
        echo "Midweek grind." . PHP_EOL;
        break;
    case 'Friday':
        echo "Almost weekend!" . PHP_EOL;
        break;
    case 'Saturday':
    case 'Sunday':
        echo "Weekend!" . PHP_EOL;
        break;
    default:
        echo "Not a valid day." . PHP_EOL;
        break;
}
```

2. **Run the script** to observe the output.

3. **Experiment with `match` (PHP 8.0+)**:

```php
<?php

$statusCode = 404;

$message = match ($statusCode) {
    200 => "Success",
    201 => "Created",
    400 => "Bad Request",
    401 => "Unauthorized",
    404 => "Not Found",
    500 => "Server Error",
    default => "Unknown Status",
};

echo "HTTP $statusCode: $message" . PHP_EOL;
```

4. **Apply concise operators** like the ternary and null coalescing operators:

```php
<?php

$age = 20;
$status = ($age >= 18) ? 'Adult' : 'Minor';
echo "Status: $status" . PHP_EOL;

$ticketPrice = ($age < 12) ? 5 : (($age >= 65) ? 8 : 12);
echo "Ticket price: $$ticketPrice" . PHP_EOL;

$username = null;
$displayName = $username ?? 'Guest';
echo "Welcome, $displayName!" . PHP_EOL;
```

### Expected Result

```
Midweek grind.
HTTP 404: Not Found
Status: Adult
Ticket price: $12
Welcome, Guest!
```

### Why It Works

- `switch` is useful when comparing a single value against many discrete options. Remember to include `break` statements to prevent fall-through.
- `match` uses strict comparison (`===`), returns a value, and doesn’t require `break` statements. It’s ideal when you need an expression rather than statements.
- The ternary operator offers a concise alternative to simple `if`/`else` checks, while the null coalescing operator (`??`) provides default values when variables are `null` or undefined.

### Troubleshooting

- **`switch` executes multiple cases** — Ensure each case ends with `break` unless you intentionally want fall-through.
- **`match` throws `UnhandledMatchError`** — Provide a `default` arm to handle unexpected values.
- **Nested ternaries are unreadable** — If the condition becomes complex, switch back to standard `if`/`else` or use `match` for clarity.

## Step 6: Controlling Loop Flow with `break` and `continue` (~3 min)

### Goal

Exit loops early or skip specific iterations using `break` and `continue`.

### Actions

1. **Use `break` to exit a loop** when a condition is met:

```php
<?php

// Find the first number divisible by 7
for ($i = 1; $i <= 100; $i++) {
    if ($i % 7 === 0) {
        echo "Found it! The first number divisible by 7 is: $i" . PHP_EOL;
        break; // Exit the loop immediately
    }
}
```

2. **Use `continue` to skip specific iterations**:

```php
<?php

// Print odd numbers from 1 to 10
for ($i = 1; $i <= 10; $i++) {
    if ($i % 2 === 0) {
        continue; // Skip even numbers
    }
    echo $i . PHP_EOL;
}
```

### Expected Result

```
Found it! The first number divisible by 7 is: 7
```

```
1
3
5
7
9
```

### Why It Works

- `break` stops the loop entirely as soon as the condition is satisfied, saving unnecessary iterations.
- `continue` skips the rest of the current loop body and moves on to the next iteration.
- Combining these tools allows fine-grained control over loop execution without nesting additional conditionals.

### Troubleshooting

- **Loop still runs after `break`** — Confirm the `break` statement is inside the correct loop, especially when loops are nested.
- **`continue` appears to skip more iterations than expected** — Check the condition; ensure the code after `continue` isn’t handling required work, and verify the loop counter still increments.
- **Inconsistent results** — Add debug statements before and after `break`/`continue` to trace the execution flow.

## Troubleshooting Common Issues

### Goal

Identify and fix the most common pitfalls when working with control structures.

### Actions

Review these scenarios and apply the suggested fixes when you run into similar behavior.

#### Issue: Infinite Loop

**Symptom**: Your script runs forever and never completes.

**Cause**: The loop condition never becomes false.

**Solution**: Ensure your loop has a way to exit. Press `Ctrl+C` in the terminal to stop a runaway script and then fix the logic.

```php
// BAD: This will run forever
$i = 1;
while ($i <= 10) {
    echo $i . PHP_EOL;
    // Forgot to increment $i!
}

// GOOD: Loop will exit
$i = 1;
while ($i <= 10) {
    echo $i . PHP_EOL;
    $i++; // This eventually makes the condition false
}
```

#### Issue: Comparison Operator Confusion

**Symptom**: Your `if` statement isn't working as expected.

**Cause**: Using `=` (assignment) instead of `==` or `===` (comparison).

**Solution**: Always use `===` for comparisons so types must match.

```php
// BAD: This assigns 5 to $age, doesn't compare!
if ($age = 5) {
    echo "You are 5 years old.";
}

// GOOD: This compares $age to 5
if ($age === 5) {
    echo "You are 5 years old.";
}
```

#### Issue: Unexpected Array Loop Behavior

**Symptom**: Your `foreach` loop modifies the array but changes don't persist.

**Cause**: By default, `foreach` works with a copy of each element.

**Solution**: Use references only when you want to mutate the array, and remember to unset the reference afterward.

```php
$numbers = [1, 2, 3];

// This doesn't modify the original array
foreach ($numbers as $num) {
    $num = $num * 2;
}
// $numbers is still [1, 2, 3]

// This DOES modify the original array
foreach ($numbers as &$num) {
    $num = $num * 2;
}
unset($num); // Important! Remove the lingering reference
// $numbers is now [2, 4, 6]
```

### Issue: Switch Fall-through

**Symptom**: Your `switch` statement executes multiple cases when you only wanted one.

**Cause**: Missing `break` statement causes execution to "fall through" to the next case.

**Solution**: Always add `break` after each case (unless you intentionally want fall-through).

```php
// BAD: Missing break statements
$color = 'blue';
switch ($color) {
    case 'red':
        echo "Red";
    case 'blue':
        echo "Blue";
    case 'green':
        echo "Green";
}
// Output: BlueGreen (falls through!)

// GOOD: Proper break statements
$color = 'blue';
switch ($color) {
    case 'red':
        echo "Red";
        break;
    case 'blue':
        echo "Blue";
        break;
    case 'green':
        echo "Green";
        break;
}
// Output: Blue
```

## Exercises

1.  **FizzBuzz**: This is a classic programming challenge. Write a script that prints the numbers from 1 to 100.
    - For multiples of three, print "Fizz" instead of the number.
    - For multiples of five, print "Buzz" instead of the number.
    - For numbers which are multiples of both three and five, print "FizzBuzz".
2.  **Multiplication Table**: Create a script that prints a multiplication table for a given number (e.g., 7) up to 12.
    - Example output for the number 7:
      ```
      7 x 1 = 7
      7 x 2 = 14
      ...
      7 x 12 = 84
      ```
3.  **Number Guessing Game**: Create a script where the program picks a random number between 1 and 20, and you have to guess it. After each guess, the program should tell you if your guess is too high, too low, or correct. Hint: You'll need to use a `while` loop and comparison operators.

4.  **Grade Calculator**: Create a script that converts numerical scores to letter grades using a `match` expression.
    - 90-100: A
    - 80-89: B
    - 70-79: C
    - 60-69: D
    - Below 60: F
    - Hint: You can use `match` with conditions like `$score >= 90 => 'A'`

## Further Reading

To dive deeper into control structures, check out these official PHP documentation pages:

- [Control Structures Overview](https://www.php.net/manual/en/language.control-structures.php) - Complete reference for all control structures
- [Match Expression](https://www.php.net/manual/en/control-structures.match.php) - Modern PHP 8.0+ alternative to switch
- [Switch Statement](https://www.php.net/manual/en/control-structures.switch.php) - Traditional multi-way branching
- [Comparison Operators](https://www.php.net/manual/en/language.operators.comparison.php) - Detailed guide including ternary (`? :`) and null coalescing (`??`)
- [Logical Operators](https://www.php.net/manual/en/language.operators.logical.php) - In-depth explanation of logical operators
- [Alternative Syntax for Control Structures](https://www.php.net/manual/en/control-structures.alternative-syntax.php) - Useful when mixing PHP with HTML

## Wrap-up

You've just learned one of the most powerful concepts in programming: controlling the flow of your code. You can now:

- Make decisions using `if`, `elseif`, and `else` statements
- Combine conditions with logical operators (`&&`, `||`, `!`)
- Use `switch` for multiple conditions and modern `match` expressions (PHP 8.0+)
- Write concise conditionals with the ternary operator (`? :`)
- Handle null values elegantly with the null coalescing operator (`??`)
- Repeat tasks with `for`, `while`, and `foreach` loops
- Control loop execution with `break` and `continue`
- Troubleshoot common control structure issues

These building blocks are essential for writing any meaningful program. You'll use them in virtually every script you write from this point forward. The modern features like `match` and `??` are particularly powerful tools that make PHP 8.4 code cleaner and more expressive.

In the next chapter, we'll learn how to bundle our code into reusable blocks called functions, making our programs much more organized and powerful.

::: info Code Examples
Complete, runnable examples from this chapter are available in:

- [`if-else-examples.php`](../code/03-control/if-else-examples.php) - If/else statements and conditional logic
- [`switch-match-examples.php`](../code/03-control/switch-match-examples.php) - Switch statements and match expressions
- [`loops-examples.php`](../code/03-control/loops-examples.php) - For, while, and foreach loops
- [`solutions/`](../code/03-control/solutions/) - Solutions to chapter exercises
  :::

## Knowledge Check

Test your understanding of control structures:

<Quiz
title="Chapter 03 Quiz: Control Structures"
:questions="[
{
question: 'What is the difference between == and === in PHP?',
options: [
{ text: '=== checks both value and type, while == only checks value', correct: true, explanation: 'The === operator (identical) checks that both the value and data type are the same, preventing type coercion bugs.' },
{ text: 'They are exactly the same', correct: false, explanation: '== performs type coercion while === does not.' },
{ text: '=== is faster than ==', correct: false, explanation: 'While === can be slightly faster, the main difference is type checking behavior.' },
{ text: '== is stricter than ===', correct: false, explanation: 'It\'s the opposite: === is stricter because it checks both value and type.' }
]
},
{
question: 'What does the match expression return if no condition matches and there is no default case?',
options: [
{ text: 'It throws an UnhandledMatchError', correct: true, explanation: 'Unlike switch, match requires all cases to be handled or a default case, otherwise it throws an error.' },
{ text: 'It returns null', correct: false, explanation: 'Match throws an error rather than returning a value.' },
{ text: 'It returns false', correct: false, explanation: 'Match throws an UnhandledMatchError, not false.' },
{ text: 'It continues execution normally', correct: false, explanation: 'An unhandled match is considered an error in PHP.' }
]
},
{
question: 'Which loop should you use when you want to iterate over all elements in an array?',
options: [
{ text: 'foreach', correct: true, explanation: 'The foreach loop is specifically designed for iterating over arrays and is the cleanest choice.' },
{ text: 'for', correct: false, explanation: 'While you can use for with arrays, foreach is more idiomatic and cleaner.' },
{ text: 'while', correct: false, explanation: 'While can work but requires manual index management; foreach is better.' },
{ text: 'do-while', correct: false, explanation: 'Do-while can work but foreach is the standard choice for arrays.' }
]
},
{
question: 'What does the break statement do inside a loop?',
options: [
{ text: 'Exits the loop immediately', correct: true, explanation: 'Break terminates the loop entirely and continues execution after the loop.' },
{ text: 'Skips the current iteration and moves to the next', correct: false, explanation: 'That\'s what continue does. Break exits the entire loop.' },
{ text: 'Pauses the loop temporarily', correct: false, explanation: 'There is no pause mechanism; break exits the loop completely.' },
{ text: 'Restarts the loop from the beginning', correct: false, explanation: 'Break exits; it doesn\'t restart the loop.' }
]
},
{
question: 'What is the null coalescing operator (??) used for?',
options: [
{ text: 'To provide a default value when a variable is null or undefined', correct: true, explanation: 'The ?? operator returns the left operand if it exists and is not null, otherwise returns the right operand.' },
{ text: 'To check if two values are equal', correct: false, explanation: 'That\'s what comparison operators (== or ===) do.' },
{ text: 'To combine multiple conditions', correct: false, explanation: 'That\'s what logical operators (&&, ||) do.' },
{ text: 'To cast a value to boolean', correct: false, explanation: 'Type casting uses (bool) syntax, not ??.' }
]
}
]"
/>
