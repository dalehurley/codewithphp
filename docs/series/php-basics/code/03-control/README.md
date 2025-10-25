# Chapter 03: Control Structures - Code Examples

This directory contains comprehensive examples demonstrating PHP's control structures for making decisions and creating loops.

## Files Overview

### 1. `if-else-examples.php`

Complete guide to conditional statements and decision-making.

**What it demonstrates:**

- Simple if statements
- If-else statements
- If-elseif-else chains
- Nested if statements
- Multiple conditions with logical operators (&&, ||, !)
- Comparison operators (==, !=, <, >, <=, >=)
- Strict vs loose comparison (=== vs ==)
- Ternary operator (shorthand if-else)
- Null coalescing operator (??)
- Practical authentication example

**Run it:**

```bash
php if-else-examples.php
```

**Key Takeaways:**

- Always use `===` (strict) instead of `==` (loose) for comparisons
- Ternary operator for simple conditions: `$result = $condition ? $true : $false;`
- Null coalescing for defaults: `$value = $var ?? 'default';`

### 2. `switch-match-examples.php`

Modern approach to multi-value comparisons with switch and match.

**What it demonstrates:**

- Basic switch statements
- Switch with fall-through (multiple cases)
- HTTP status code handling
- Match expressions (PHP 8.0+) - RECOMMENDED
- Match with complex conditions
- Match returning different types
- Switch vs Match comparison
- When to use each

**Run it:**

```bash
php switch-match-examples.php
```

**Key Takeaways:**

- `match` is safer than `switch` (strict comparison, exhaustive checking)
- `match` returns a value, `switch` executes statements
- Use `match` for modern PHP 8.0+ code
- `switch` allows fall-through behavior, `match` doesn't

### 3. `loops-examples.php`

Complete guide to all loop types in PHP.

**What it demonstrates:**

- For loops (basic, countdown, custom step)
- While loops
- Do-while loops (execute at least once)
- Foreach with indexed arrays
- Foreach with keys and values
- Foreach with associative arrays
- Break statement (exit loop early)
- Continue statement (skip iteration)
- Nested loops (multiplication table)
- Controlled infinite loops
- Multiple exit conditions
- Practical examples (menu systems, shopping carts)
- Performance tips

**Run it:**

```bash
php loops-examples.php
```

**Key Takeaways:**

- Use `foreach` for arrays (cleanest and most efficient)
- Use `for` when you know iteration count
- Use `while` for condition-based iteration
- `break` exits the loop, `continue` skips to next iteration
- Cache array length in `for` loops for better performance

## Exercise Solutions

### Exercise 1: Age Category Checker

**File:** `solutions/exercise-1-age-checker.php`

Categorize people by age using if-elseif-else statements.

**Requirements:**

- 0-12: Child
- 13-17: Teenager
- 18-64: Adult
- 65+: Senior

**Run it:**

```bash
php solutions/exercise-1-age-checker.php
```

### Exercise 2: Multiplication Table Generator

**File:** `solutions/exercise-2-multiplication-table.php`

Generate multiplication tables using loops.

**Requirements:**

- Print multiplication table for a given number (1-10)
- Bonus: Create full 12×12 table with formatting

**Run it:**

```bash
php solutions/exercise-2-multiplication-table.php
```

### Exercise 3: FizzBuzz Challenge

**File:** `solutions/exercise-3-fizzbuzz.php`

Classic programming interview question!

**Requirements:**

- Print numbers 1-100
- Multiples of 3: "Fizz"
- Multiples of 5: "Buzz"
- Multiples of both: "FizzBuzz"

**Run it:**

```bash
php solutions/exercise-3-fizzbuzz.php
```

**Why it matters:** FizzBuzz tests your understanding of:

- Loops
- Conditional logic
- Modulo operator (%)
- Order of conditions (check 15 before 3 or 5)

### Exercise 4: Grade Statistics Calculator

**File:** `solutions/exercise-4-grade-statistics.php`

Analyze an array of student grades.

**Requirements:**

- Calculate average, highest, lowest
- Count passed/failed students
- Convert to letter grades (A-F)
- Display grade distribution

**Run it:**

```bash
php solutions/exercise-4-grade-statistics.php
```

## Quick Reference

### Comparison Operators

```php
==   Equal (loose)
===  Identical (strict) ✓ RECOMMENDED
!=   Not equal (loose)
!==  Not identical (strict) ✓ RECOMMENDED
<    Less than
>    Greater than
<=   Less than or equal
>=   Greater than or equal
```

### Logical Operators

```php
&&  AND - both conditions must be true
||  OR - at least one condition must be true
!   NOT - inverts the condition
```

### Loop Types Quick Guide

```php
// When you know the iteration count
for ($i = 0; $i < 10; $i++) { }

// When condition-based
while ($condition) { }

// Execute at least once
do { } while ($condition);

// Best for arrays
foreach ($array as $value) { }
foreach ($array as $key => $value) { }
```

### Loop Control

```php
break;     // Exit loop immediately
continue;  // Skip to next iteration
```

## Common Patterns

### Validation Pattern

```php
if ($age < 0) {
    echo "Invalid age";
} elseif ($age < 18) {
    echo "Minor";
} else {
    echo "Adult";
}
```

### Early Return Pattern (in functions)

```php
function processUser($user) {
    if (!$user) {
        return null; // Early exit
    }

    // Main logic here
    return $result;
}
```

### Loop with Accumulator Pattern

```php
$total = 0;
foreach ($prices as $price) {
    $total += $price;
}
```

### Search Pattern with Break

```php
$found = false;
foreach ($items as $item) {
    if ($item === $target) {
        $found = true;
        break;
    }
}
```

## Common Mistakes to Avoid

### 1. Using Loose Comparison

```php
// BAD - loose comparison can cause bugs
if ($value == 0) { } // true for "0", "", null, false

// GOOD - strict comparison
if ($value === 0) { } // only true for integer 0
```

### 2. Missing Breaks in Switch

```php
// BAD - falls through to next case
switch ($day) {
    case 'Monday':
        echo "Start week";
    case 'Tuesday':  // This will execute too!
        echo "Continue";
}

// GOOD - use break
switch ($day) {
    case 'Monday':
        echo "Start week";
        break;
    case 'Tuesday':
        echo "Continue";
        break;
}
```

### 3. Infinite Loops

```php
// BAD - infinite loop, $i never changes
$i = 0;
while ($i < 10) {
    echo $i;
}

// GOOD - increment the counter
$i = 0;
while ($i < 10) {
    echo $i;
    $i++;
}
```

### 4. Off-by-One Errors

```php
// BAD - misses last element (< instead of <=)
for ($i = 1; $i < 10; $i++) { } // Only goes to 9

// GOOD - includes 10
for ($i = 1; $i <= 10; $i++) { } // Goes to 10
```

## Performance Tips

1. **Cache array length in loops:**

```php
$count = count($array); // Calculate once
for ($i = 0; $i < $count; $i++) { }
```

2. **Use foreach for arrays:**

```php
// Faster and cleaner
foreach ($array as $value) { }

// vs
for ($i = 0; $i < count($array); $i++) {
    $value = $array[$i];
}
```

3. **Break early when possible:**

```php
// Stop as soon as you find it
foreach ($items as $item) {
    if ($item === $target) {
        break; // Don't check remaining items
    }
}
```

## Next Steps

Once you master control structures, you're ready for Chapter 04: Understanding and Using Functions, where you'll learn to organize your code into reusable blocks!

## Related Chapter

[Chapter 03: Control Structures](../../chapters/03-control-structures.md)
