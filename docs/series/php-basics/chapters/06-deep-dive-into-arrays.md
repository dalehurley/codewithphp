---
title: "06: Deep Dive into Arrays"
description: "Master PHP's most fundamental data structure for storing and managing lists of information, from simple lists to complex, structured data."
series: "php-basics"
chapter: 6
order: 6
difficulty: "Beginner"
prerequisites:
  - "/series/php-basics/chapters/05-handling-html-forms-and-user-input"
---

# Chapter 06: Deep Dive into Arrays

## Overview

We've worked with variables that hold a single piece of data, like a name or a number. But what happens when you need to work with a list of items, like a collection of products, a list of student names, or the days of the week? Storing each one in a separate variable (`$student1`, `$student2`, etc.) would be incredibly inefficient.

This is where arrays come in. An array is a special variable that can hold multiple values under a single name. They are one of the most important and frequently used data structures in PHP, and mastering them is essential.

## Prerequisites

- PHP 8.4 installed and configured ([Chapter 00](/series/php-basics/chapters/00-setting-up-your-development-environment))
- Understanding of variables and data types ([Chapter 02](/series/php-basics/chapters/02-variables-data-types-and-constants))
- Familiarity with control structures and loops ([Chapter 03](/series/php-basics/chapters/03-control-structures))
- Estimated time: ~20 minutes

## What You'll Build

By the end of this chapter, you'll be able to:

- Create and manipulate indexed arrays for simple lists
- Build associative arrays with descriptive keys for structured data
- Work with multi-dimensional arrays for complex data structures
- Use essential array functions to search, sort, and transform data
- Apply array operations to solve real-world problems

## Objectives

- Understand the difference between indexed and associative arrays.
- Create and access elements in multi-dimensional arrays.
- Add, update, and remove elements from an array.
- Use common and powerful array functions to manipulate data.

## Step 1: Indexed Arrays (~4 min)

**Goal** - Create and manipulate indexed arrays to store lists of data.

The simplest type of array is an **indexed array**, where each element is identified by a numeric index, starting from 0.

### Actions

1.  **Create a File**
    Create a new file named `arrays.php` in your working directory.

2.  **Create an Indexed Array**
    You can create an array using the `[]` short syntax, which is the modern standard.

```php
# filename: arrays.php
<?php

// An indexed array of programming languages
$languages = ['PHP', 'JavaScript', 'Python', 'Go'];

// Accessing elements by their index
echo "The second language is: " . $languages[1] . PHP_EOL; // Index 1 is the second element

// To see the whole array structure, use print_r() or var_dump()
print_r($languages);
```

3.  **Run the Script**

```bash
# Execute the script
php arrays.php
```

### Expected Result

```text
The second language is: JavaScript
Array
(
    [0] => PHP
    [1] => JavaScript
    [2] => Python
    [3] => Go
)
```

### Why It Works

Arrays in PHP are zero-indexed, meaning the first element is at position `0`, not `1`. The `[]` syntax creates an array where each element is automatically assigned an integer key starting from 0. The `print_r()` function is like `var_dump()` but gives a cleaner, more human-readable output for arrays—perfect for debugging.

### Validation

To confirm you can access array elements correctly, try adding this to your script:

```php
# Test individual element access
echo "First: " . $languages[0] . PHP_EOL;
echo "Last: " . $languages[count($languages) - 1] . PHP_EOL;
```

You should see `First: PHP` and `Last: Go`.

4.  **Modifying Indexed Arrays**
    You can change an element by referencing its index, or add a new one by using empty square brackets `[]`.

```php
# filename: arrays.php
<?php
$languages = ['PHP', 'JavaScript', 'Python', 'Go'];

// Update an element
$languages[1] = 'JS';

// Add a new element to the end of the array
$languages[] = 'Rust';

print_r($languages);
```

**Expected Result**

```text
Array
(
    [0] => PHP
    [1] => JS
    [2] => Python
    [3] => Go
    [4] => Rust
)
```

### Troubleshooting

- **Error: Undefined array key**: This happens when you try to access an index that doesn't exist. Always check if a key exists using `isset($array[index])` or use the null coalescing operator: `$value = $array[5] ?? 'default'`.
- **Warning about array to string conversion**: You tried to echo an entire array. Use `print_r()` or `var_dump()` to display array contents, not `echo`.

## Step 2: Associative Arrays (~5 min)

**Goal** - Create associative arrays with descriptive keys for structured, self-documenting data.

Indexed arrays are great for simple lists, but sometimes you need to store data with more descriptive labels. An **associative array** uses named **keys** instead of numeric indexes. This allows you to create more structured, self-documenting data.

### Actions

1.  **Create an Associative Array**
    Replace the contents of `arrays.php` with the following:

```php
# filename: arrays.php
<?php

// An associative array representing a user
$user = [
    'first_name' => 'Dale',
    'last_name'  => 'Hurley',
    'email'      => 'dale@example.com',
    'age'        => 30
];

// Accessing elements by their key
echo "User's email: " . $user['email'] . PHP_EOL;

// Adding a new key-value pair
$user['role'] = 'Admin';

print_r($user);
```

2.  **Run the Script**

```bash
php arrays.php
```

### Expected Result

```text
User's email: dale@example.com
Array
(
    [first_name] => Dale
    [last_name] => Hurley
    [email] => dale@example.com
    [age] => 30
    [role] => Admin
)
```

### Why It Works

Associative arrays use the `=>` arrow operator to pair keys with values. Keys are usually strings (though they can be integers), and they make your code self-documenting. Compare `$user['email']` to `$user[2]`—which one is more readable? The associative array makes it crystal clear what data you're accessing.

### Validation

Check if specific keys exist in your array:

```php
# Test key existence
if (array_key_exists('email', $user)) {
    echo "Email key exists!" . PHP_EOL;
}

// Or use isset()
if (isset($user['role'])) {
    echo "Role is set to: " . $user['role'] . PHP_EOL;
}
```

### Troubleshooting

- **Undefined array key warning**: You're trying to access a key that doesn't exist. Use `isset()` or `array_key_exists()` to check first, or use the null coalescing operator: `$value = $user['phone'] ?? 'N/A'`.
- **Syntax error near =>**: Make sure you're using `=>` (not `->` or `=`) to separate keys and values in array definitions.

## Step 3: Multi-dimensional Arrays (~4 min)

**Goal** - Build and navigate multi-dimensional arrays to represent complex, nested data structures.

A multi-dimensional array is simply an array that contains other arrays. This is incredibly useful for grouping related data. For example, you could have a list of users, where each user is an associative array.

### Actions

1.  **Create a Multi-dimensional Array**
    Update `arrays.php` with this code:

```php
# filename: arrays.php
<?php

$users = [
    [
        'first_name' => 'Dale',
        'last_name'  => 'Hurley',
        'email'      => 'dale@example.com',
    ],
    [
        'first_name' => 'Alice',
        'last_name'  => 'Smith',
        'email'      => 'alice@example.com',
    ],
    [
        'first_name' => 'Bob',
        'last_name'  => 'Johnson',
        'email'      => 'bob@example.com',
    ]
];

// To access Dale's email:
// First, access the first element of the $users array (index 0)
// Then, access the 'email' key within that element
echo "Dale's email is: " . $users[0]['email'] . PHP_EOL;

// You can loop through them easily with foreach
foreach ($users as $user) {
    echo $user['first_name'] . "'s email is " . $user['email'] . PHP_EOL;
}
```

2.  **Run the Script**

```bash
php arrays.php
```

### Expected Result

```text
Dale's email is: dale@example.com
Dale's email is dale@example.com
Alice's email is alice@example.com
Bob's email is bob@example.com
```

### Why It Works

Multi-dimensional arrays use multiple sets of brackets to access nested data. `$users[0]` gets the first user array, and `$users[0]['email']` digs one level deeper to get that user's email. This pattern scales to any depth—you could have arrays within arrays within arrays.

When looping with `foreach`, each iteration gives you one inner array, which you can then work with as a regular associative array.

### Validation

Try accessing different levels of the structure:

```php
# Count total users
echo "Total users: " . count($users) . PHP_EOL;

# Access the last user's last name
$lastIndex = count($users) - 1;
echo "Last user's last name: " . $users[$lastIndex]['last_name'] . PHP_EOL;
```

You should see `Total users: 3` and `Last user's last name: Johnson`.

### Troubleshooting

- **Trying to access array offset on value of type null**: You're trying to use array syntax on something that isn't an array. Check that the first level exists before accessing the second: `if (isset($users[0])) { echo $users[0]['email']; }`.
- **Confusion with nesting levels**: Draw out your array structure on paper. Each `[` increases your nesting depth. Use `print_r()` liberally to see the structure.

## Step 4: Essential Array Functions (~6 min)

**Goal** - Master the most commonly used array functions for searching, sorting, and transforming data.

PHP has a huge library of built-in functions for working with arrays. Here are the most essential ones you'll use constantly.

### Common Array Functions

#### Inspection Functions

- `count()`: Returns the number of elements in an array
- `array_key_exists()`: Checks if a given key exists in the array
- `in_array()`: Checks if a given value exists in the array
- `isset()`: Checks if a key exists and its value is not null

#### Modification Functions

- `array_push()`: Adds one or more elements to the end (or use `$array[] = $value`)
- `array_pop()`: Removes and returns the last element
- `array_shift()`: Removes and returns the first element
- `array_unshift()`: Adds elements to the beginning of an array

#### Combination Functions

- `array_merge()`: Merges one or more arrays into one
- `array_combine()`: Creates an array using one array for keys, another for values

#### Sorting Functions

- `sort()`: Sorts an indexed array in ascending order
- `rsort()`: Sorts in descending order
- `asort()`: Sorts an associative array by values, preserving keys
- `ksort()`: Sorts an associative array by keys

#### Extraction Functions

- `array_keys()`: Returns all keys from an array
- `array_values()`: Returns all values from an array (re-indexes)
- `array_slice()`: Extracts a portion of an array

### Actions

1.  **Try Basic Array Functions**
    Create a new file `array_functions.php`:

```php
# filename: array_functions.php
<?php

$numbers = [3, 1, 4, 1, 5, 9, 2, 6];

echo "There are " . count($numbers) . " numbers in the array." . PHP_EOL;

if (in_array(5, $numbers)) {
    echo "The number 5 was found!" . PHP_EOL;
}

// Sort the array in ascending order
sort($numbers);
echo "Sorted: ";
print_r($numbers);

// Add and remove elements
array_push($numbers, 10, 11);
echo "After push: " . implode(', ', $numbers) . PHP_EOL;

$last = array_pop($numbers);
echo "Popped value: $last" . PHP_EOL;
echo "After pop: " . implode(', ', $numbers) . PHP_EOL;
```

2.  **Run the Script**

```bash
php array_functions.php
```

### Expected Result

```text
There are 8 numbers in the array.
The number 5 was found!
Sorted: Array
(
    [0] => 1
    [1] => 1
    [2] => 2
    [3] => 3
    [4] => 4
    [5] => 5
    [6] => 6
    [7] => 9
)
After push: 1, 1, 2, 3, 4, 5, 6, 9, 10, 11
Popped value: 11
After pop: 1, 1, 2, 3, 4, 5, 6, 9, 10
```

### Why It Works

Array functions operate directly on the array, often modifying it in place. Functions like `sort()`, `array_push()`, and `array_pop()` change the original array, while functions like `count()` and `in_array()` just read it. The `implode()` function joins array elements into a string—perfect for displaying arrays inline.

### Advanced Example - Working with Keys and Values

```php
# filename: array_keys_values.php
<?php

$product = [
    'name' => 'Laptop',
    'price' => 999,
    'stock' => 15,
    'category' => 'Electronics'
];

// Get all keys
$keys = array_keys($product);
echo "Keys: " . implode(', ', $keys) . PHP_EOL;

// Get all values
$values = array_values($product);
echo "Values: " . implode(', ', $values) . PHP_EOL;

// Check if a specific key exists
if (array_key_exists('price', $product)) {
    echo "Price: $" . $product['price'] . PHP_EOL;
}

// Merge with another array
$extraInfo = ['brand' => 'TechCorp', 'warranty' => '2 years'];
$fullProduct = array_merge($product, $extraInfo);

print_r($fullProduct);
```

### Validation

Test that you understand which functions modify arrays:

```php
# Test array modification
$test = [1, 2, 3];
$count = count($test); // Doesn't modify
sort($test);           // DOES modify
echo "After sort: " . implode(', ', $test) . PHP_EOL;
```

### Troubleshooting

- **Warning: sort() expects parameter 1 to be array**: You're passing a non-array value to an array function. Use `is_array()` to check first.
- **Array to string conversion error**: You tried to echo an array. Use `implode()`, `print_r()`, or `var_dump()` instead.
- **Values lost after array_values()**: This function re-indexes arrays starting from 0, discarding string keys. Use it only when you want a pure indexed array.

## Step 5: Modern Array Features (~4 min)

**Goal** - Use modern PHP array syntax including the spread operator and array unpacking.

PHP has evolved to include powerful array manipulation syntax that makes your code cleaner and more expressive.

### The Spread Operator (...)

The spread operator allows you to unpack arrays inline, which is incredibly useful for merging arrays or passing array elements as function arguments.

```php
# filename: modern_arrays.php
<?php

// Merge arrays using the spread operator
$fruits = ['apple', 'banana'];
$vegetables = ['carrot', 'broccoli'];
$food = [...$fruits, ...$vegetables];

echo "Food items: " . implode(', ', $food) . PHP_EOL;

// Add elements while spreading
$moreFruits = ['orange', ...$fruits, 'grape'];
print_r($moreFruits);

// Unpack array in function calls
$numbers = [1, 2, 3, 4, 5];
echo "Max value: " . max(...$numbers) . PHP_EOL;
```

### Array Unpacking in Assignments

You can destructure arrays directly in assignments:

```php
# Destructuring arrays
<?php

// Simple unpacking
[$first, $second, $third] = ['PHP', 'JavaScript', 'Python'];
echo "First language: $first" . PHP_EOL;

// Skip elements with empty positions
[$one, , $three] = [1, 2, 3];
echo "One: $one, Three: $three" . PHP_EOL;

// Works with associative arrays too
['name' => $name, 'age' => $age] = ['name' => 'Dale', 'age' => 30];
echo "$name is $age years old" . PHP_EOL;
```

### Expected Result

```text
Food items: apple, banana, carrot, broccoli
Array
(
    [0] => orange
    [1] => apple
    [2] => banana
    [3] => grape
)
Max value: 5
First language: PHP
One: 1, Three: 3
Dale is 30 years old
```

### Why It Works

The spread operator (`...`) unpacks array elements where you use it. It's more readable than `array_merge()` and works in more contexts. Array destructuring lets you extract multiple values in one line, making your code more concise.

### Validation

Test combining different spreading techniques:

```bash
php modern_arrays.php
```

You should see all output matching the expected result above.

### Troubleshooting

- **Syntax error, unexpected '...'**: Make sure you're using PHP 7.4+ for array spread in array expressions. Check with `php -v`.
- **Cannot unpack array with string keys without explicitly specifying keys**: When using spread with associative arrays, string keys must be explicitly matched in your unpacking syntax.

## Exercises

### Exercise 1 - Student Grades Calculator

Create a script that manages and calculates student grades.

**Requirements**

- Create an associative array called `$student` with keys for `name`, `age`, and `grades`
- The `grades` key should hold an indexed array of numbers (e.g., `[85, 92, 78, 95]`)
- Calculate the average grade using `array_sum()` and `count()`
- Find the highest and lowest grades using `max()` and `min()`
- Print a summary like: "Dale is 30 years old and has an average grade of 87.5 (highest: 95, lowest: 78)."

**Expected Output**

```text
Dale is 30 years old and has an average grade of 87.5 (highest: 95, lowest: 78).
```

### Exercise 2 - Product Inventory Filter

Create a product filtering system that shows only available items.

**Requirements**

- Create an array of at least 4 products
- Each product should be an associative array with keys: `name`, `price`, and `in_stock` (boolean)
- Use a `foreach` loop to iterate through the products
- Print only the names and prices of products that are in stock, formatted as: "Laptop - $999.00"
- At the end, print the total number of in-stock products

**Expected Output** (example):

```text
Available Products:
Laptop - $999.00
Mouse - $25.50
Monitor - $349.99
Total in stock: 3
```

### Exercise 3 - Array Manipulation Challenge

Practice various array operations in a single script.

**Requirements**

- Start with this array: `$numbers = [15, 8, 23, 4, 42, 16]`
- Add the number `50` to the end
- Remove the first element
- Sort the array in descending order
- Use the spread operator to create a new array that includes these numbers plus `[100, 200]`
- Print the final array and its count

**Expected Output**

```text
Final array: 50, 42, 23, 16, 15, 8, 100, 200
Total numbers: 8
```

## Step 7: PHP 8.4 Modern Array Functions (~5 min)

**Goal** - Use modern PHP 8.4 array functions for cleaner, more expressive code.

PHP 8.4 introduces four powerful new array functions that make searching and validating arrays much simpler and more intuitive than traditional approaches.

### Actions

1.  **Create a New File**
    Create `php84-arrays.php` in your working directory.

2.  **Explore the New Functions**

```php
# filename: php84-arrays.php
<?php

declare(strict_types=1);

$users = [
    ['id' => 1, 'name' => 'Alice', 'isActive' => true],
    ['id' => 2, 'name' => 'Bob', 'isActive' => false],
    ['id' => 3, 'name' => 'Charlie', 'isActive' => true],
];

// array_find() - Find the first element that matches a condition
$firstActive = array_find($users, fn($user) => $user['isActive']);
echo "First active user: " . $firstActive['name'] . PHP_EOL;

// array_find_key() - Find the KEY of the first matching element
$key = array_find_key($users, fn($user) => $user['name'] === 'Bob');
echo "Bob is at index: " . $key . PHP_EOL;

// array_any() - Check if ANY element matches a condition
$hasInactive = array_any($users, fn($user) => !$user['isActive']);
echo "Has inactive users: " . ($hasInactive ? 'Yes' : 'No') . PHP_EOL;

// array_all() - Check if ALL elements match a condition
$allActive = array_all($users, fn($user) => $user['isActive']);
echo "All users active: " . ($allActive ? 'Yes' : 'No') . PHP_EOL;
```

3.  **Run the Script**

```bash
php php84-arrays.php
```

### Expected Output

```text
First active user: Alice
Bob is at index: 1
Has inactive users: Yes
All users active: No
```

### Why It Matters

**Before PHP 8.4**, you had to write verbose code like this:

```php
// Old way to find first active user
$filtered = array_filter($users, fn($user) => $user['isActive']);
$firstActive = reset($filtered) ?: null;

// Old way to check if any are inactive
$hasInactive = count(array_filter($users, fn($u) => !$u['isActive'])) > 0;
```

**With PHP 8.4**, the code is clearer and more expressive:

```php
// New way - much cleaner!
$firstActive = array_find($users, fn($user) => $user['isActive']);
$hasInactive = array_any($users, fn($u) => !$u['isActive']);
```

### Comparison Table

| Function           | Purpose                     | Returns           | Old Equivalent                        |
| ------------------ | --------------------------- | ----------------- | ------------------------------------- |
| `array_find()`     | Find first matching element | Element or `null` | `array_filter()` + `reset()`          |
| `array_find_key()` | Find key of first match     | Key or `null`     | `array_keys()` + `array_filter()`     |
| `array_any()`      | Check if any match          | `bool`            | `count(array_filter())` > 0           |
| `array_all()`      | Check if all match          | `bool`            | `count(array_filter())` === `count()` |

### Practical Example

Here's a real-world authentication scenario:

```php
$roles = ['user', 'editor', 'viewer'];

// Check if user has admin privileges
$isAdmin = array_any($roles, fn($role) => $role === 'admin');

if ($isAdmin) {
    echo "Access granted!" . PHP_EOL;
} else {
    echo "Admin access required." . PHP_EOL;
}

// Ensure user has at least one valid role
$validRoles = ['user', 'admin', 'editor', 'viewer'];
$allValid = array_all($roles, fn($role) => in_array($role, $validRoles));

if (!$allValid) {
    echo "Invalid roles detected!" . PHP_EOL;
}
```

### Code Files

For more comprehensive examples of PHP 8.4 array functions, see:

- [`code/06-arrays/php84-array-functions.php`](https://github.com/dalehurley/codewithphp/blob/main/docs/series/php-basics/code/06-arrays/php84-array-functions.php)

### Validation

Test the new functions with different scenarios:

```php
$numbers = [1, 2, 3, 4, 5];

// Find first even number
$firstEven = array_find($numbers, fn($n) => $n % 2 === 0);
echo "First even: " . $firstEven . PHP_EOL;  // 2

// Check if any are negative
$hasNegative = array_any($numbers, fn($n) => $n < 0);
echo "Has negative: " . ($hasNegative ? 'Yes' : 'No') . PHP_EOL;  // No

// Check if all are positive
$allPositive = array_all($numbers, fn($n) => $n > 0);
echo "All positive: " . ($allPositive ? 'Yes' : 'No') . PHP_EOL;  // Yes
```

::: tip Why Use These New Functions?

- **More readable**: Intent is immediately clear
- **More efficient**: No need to iterate entire array if early match is found
- **Type safe**: Returns proper types (`null` instead of `false`)
- **Less code**: No need to combine multiple functions
  :::

## Wrap-up

You've now taken a deep dive into PHP arrays, the workhorse of data collection in the language. You can:

- Create and manipulate indexed arrays for simple lists
- Build associative arrays with descriptive keys for structured data
- Navigate multi-dimensional arrays to represent complex data
- Use essential array functions to search, sort, and transform data
- Apply modern PHP array syntax with the spread operator and unpacking

Understanding how to structure and work with data in arrays is a massive step forward in your PHP journey. Arrays are everywhere in PHP programming—from handling form data to managing database results to building complex application state.

In the next chapter, we'll focus on another crucial data type: strings. You'll learn how to search, replace, format, and manipulate text with precision.

## Knowledge Check

Test your understanding of PHP arrays:

<Quiz
title="Chapter 06 Quiz - Arrays"
:questions="[
{
question: 'What is the difference between an indexed array and an associative array?',
options: [
{ text: 'Indexed arrays use numeric keys, associative arrays use string keys', correct: true, explanation: 'Indexed arrays automatically use 0, 1, 2... as keys, while associative arrays use custom string keys like \'name\' or \'email\'.' },
{ text: 'Indexed arrays are faster than associative arrays', correct: false, explanation: 'Performance is nearly identical; the difference is in how you access elements.' },
{ text: 'Associative arrays can only hold strings', correct: false, explanation: 'Associative arrays can hold any data type, just like indexed arrays.' },
{ text: 'They are exactly the same in PHP', correct: false, explanation: 'They differ in their key types and how you access elements.' }
]
},
{
question: 'What does the count() function return for an array?',
options: [
{ text: 'The number of elements in the array', correct: true, explanation: 'count() returns the total number of elements at the top level of an array.' },
{ text: 'The total memory size of the array', correct: false, explanation: 'count() returns the element count, not memory usage.' },
{ text: 'The highest numeric key', correct: false, explanation: 'count() returns element count, which may not match the highest key if you\'ve set custom keys.' },
{ text: 'The last element in the array', correct: false, explanation: 'That would be end() or accessing with count()-1; count() returns the number.' }
]
},
{
question: 'What is the new PHP 8.4 array_find() function used for?',
options: [
{ text: 'Returns the first element that matches a callback condition', correct: true, explanation: 'array_find() stops at the first match, making it more efficient than array_filter() when you only need one result.' },
{ text: 'Returns all elements matching a callback', correct: false, explanation: 'That\'s array_filter(); array_find() returns only the first match.' },
{ text: 'Finds the index of an element', correct: false, explanation: 'That\'s array_search() or array_find_key(); array_find() returns the value.' },
{ text: 'Sorts an array by value', correct: false, explanation: 'That\'s sort() or asort(); array_find() is for searching.' }
]
},
{
question: 'How do you access a value in a multi-dimensional array?',
options: [
{ text: 'Use multiple square brackets: $array[0][\'key\']', correct: true, explanation: 'Each set of brackets accesses one level deeper into the nested structure.' },
{ text: 'Use a dot notation: $array.0.key', correct: false, explanation: 'PHP uses brackets, not dots. Dots are for string concatenation.' },
{ text: 'Use arrow notation: $array->0->key', correct: false, explanation: 'Arrows are for object properties, not array elements.' },
{ text: 'You can only access the first level', correct: false, explanation: 'You can nest to any depth using multiple brackets.' }
]
},
{
question: 'What does the in_array() function do?',
options: [
{ text: 'Checks if a specific value exists anywhere in the array', correct: true, explanation: 'in_array() searches for a value and returns true if found, false otherwise.' },
{ text: 'Checks if a key exists in the array', correct: false, explanation: 'That\'s array_key_exists() or isset(); in_array() checks values.' },
{ text: 'Adds an element to the array', correct: false, explanation: 'That\'s array_push() or $array[] = value; in_array() only checks.' },
{ text: 'Counts elements in the array', correct: false, explanation: 'That\'s count(); in_array() checks for value existence.' }
]
}
]"
/>

## Further Reading

- [PHP Arrays Documentation](https://www.php.net/manual/en/language.types.array.php) — Complete reference for array syntax and behavior
- [PHP Array Functions](https://www.php.net/manual/en/ref.array.php) — Full list of 70+ built-in array functions
- [Array Operators](https://www.php.net/manual/en/language.operators.array.php) — Union, equality, and identity operators for arrays
- [Spread Operator in Arrays](https://www.php.net/manual/en/migration74.new-features.php#migration74.new-features.core.unpack-inside-array) — PHP 7.4+ array unpacking features
