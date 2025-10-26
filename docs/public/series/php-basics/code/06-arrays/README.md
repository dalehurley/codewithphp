# Chapter 06: Deep Dive into Arrays - Code Examples

Master PHP arrays from basics to advanced techniques, including PHP 8.4's new array functions.

## Files

1. **`array-basics.php`** - Creating, accessing, and modifying arrays
2. **`array-functions.php`** - Common array manipulation functions
3. **`php84-array-functions.php`** - NEW! array_find(), array_any(), array_all()
4. **`array-sorting.php`** - All sorting functions and techniques
5. **`multidimensional-arrays.php`** - Working with nested arrays

## Quick Start

```bash
php array-basics.php
php array-functions.php
php php84-array-functions.php
php array-sorting.php
php multidimensional-arrays.php
```

## Array Fundamentals

### Creating Arrays

```php
// Indexed
$fruits = ["apple", "banana", "cherry"];

// Associative
$person = [
    "name" => "Alice",
    "age" => 25
];

// Mixed
$mixed = [0 => "first", "key" => "value"];
```

### Accessing Elements

```php
echo $fruits[0];           // "apple"
echo $person["name"];      // "Alice"
echo $fruits[count($fruits) - 1];  // Last element
```

## Essential Array Functions

### Transform: array_map()

```php
$numbers = [1, 2, 3, 4, 5];
$squared = array_map(fn($n) => $n * $n, $numbers);
// [1, 4, 9, 16, 25]
```

### Filter: array_filter()

```php
$even = array_filter($numbers, fn($n) => $n % 2 === 0);
// [2, 4]
```

### Reduce: array_reduce()

```php
$sum = array_reduce($numbers, fn($carry, $n) => $carry + $n, 0);
// 15
```

### Merge: array_merge()

```php
$all = array_merge([1, 2], [3, 4]);
// [1, 2, 3, 4]
```

### Extract Column: array_column()

```php
$users = [
    ["name" => "Alice", "age" => 25],
    ["name" => "Bob", "age" => 30]
];
$names = array_column($users, "name");
// ["Alice", "Bob"]
```

## PHP 8.4 New Functions ⚡

### array_find() - Find First Match

```php
$users = [
    ["name" => "Alice", "age" => 25],
    ["name" => "Bob", "age" => 30],
    ["name" => "Charlie", "age" => 35]
];

// OLD WAY (verbose)
$filtered = array_filter($users, fn($u) => $u["age"] > 30);
$first = reset($filtered) ?: null;

// NEW WAY (clean!)
$first = array_find($users, fn($u) => $u["age"] > 30);
// ["name" => "Charlie", "age" => 35]
```

### array_find_key() - Find Key of Match

```php
$index = array_find_key($users, fn($u) => $u["age"] > 30);
// 2 (index of Charlie)
```

### array_any() - Check if Any Match

```php
$hasOver40 = array_any($users, fn($u) => $u["age"] > 40);
// false
```

### array_all() - Check if All Match

```php
$allOver20 = array_all($users, fn($u) => $u["age"] > 20);
// true
```

**Why These Matter:**

- ✓ More readable and expressive
- ✓ Stop iterating once condition met (performance)
- ✓ No workarounds needed (reset(), array_values())
- ✓ Consistent return types

## Sorting Arrays

### Basic Sorts

```php
sort($array);        // Ascending, indexed
rsort($array);       // Descending, indexed
asort($array);       // Ascending, preserve keys
arsort($array);      // Descending, preserve keys
ksort($array);       // By key, ascending
krsort($array);      // By key, descending
```

### Custom Sort

```php
$people = [
    ["name" => "Alice", "age" => 25],
    ["name" => "Bob", "age" => 30]
];

// Sort by age
usort($people, fn($a, $b) => $a["age"] <=> $b["age"]);

// Sort by name (descending)
usort($people, fn($a, $b) => $b["name"] <=> $a["name"]);
```

### Spaceship Operator

```php
1 <=> 2;  // -1 (less than)
2 <=> 2;  // 0  (equal)
3 <=> 2;  // 1  (greater than)
```

## Multidimensional Arrays

### Creating

```php
$users = [
    [
        "name" => "Alice",
        "address" => [
            "city" => "NYC",
            "zip" => "10001"
        ]
    ],
    [
        "name" => "Bob",
        "address" => [
            "city" => "LA",
            "zip" => "90001"
        ]
    ]
];
```

### Accessing

```php
echo $users[0]["name"];              // "Alice"
echo $users[0]["address"]["city"];   // "NYC"
```

### Searching (PHP 8.4)

```php
$alice = array_find($users, fn($u) => $u["name"] === "Alice");
```

### Extracting Column

```php
$names = array_column($users, "name");
// ["Alice", "Bob"]
```

### Flattening

```php
$nested = [[1, 2], [3, 4], [5, 6]];
$flat = array_merge(...$nested);
// [1, 2, 3, 4, 5, 6]
```

## Common Patterns

### Check if Empty

```php
if (empty($array)) { }
if (count($array) === 0) { }
```

### Add Elements

```php
$array[] = "value";              // Append
array_push($array, "a", "b");    // Append multiple
array_unshift($array, "first");  // Prepend
```

### Remove Elements

```php
array_pop($array);      // Remove last
array_shift($array);    // Remove first
unset($array[2]);       // Remove by index
```

### Check Existence

```php
in_array("value", $array);           // Value exists?
isset($array["key"]);                // Key exists and not null?
array_key_exists("key", $array);     // Key exists (even if null)?
```

### Join/Split

```php
$string = implode(", ", $array);     // Array to string
$array = explode(", ", $string);     // String to array
```

## Performance Tips

✓ **Use array_column()** instead of array_map() for extracting keys
✓ **Use array_any()** instead of count(array_filter()) > 0
✓ **Use array_find()** instead of array_filter() + reset()
✓ **Use isset()** instead of array_key_exists() when possible
✓ **Use []** instead of array() for better readability

## Common Mistakes

❌ **Modifying array during foreach**

```php
foreach ($array as $key => $value) {
    unset($array[$key]); // Can cause issues
}
```

❌ **Forgetting to preserve keys**

```php
$filtered = array_filter($data, $callback);
// Keys may not be sequential!
$filtered = array_values(array_filter($data, $callback));
```

❌ **Using in_array() without strict mode**

```php
in_array("1", [1, 2, 3]);        // true (type juggling!)
in_array("1", [1, 2, 3], true);  // false (strict)
```

## Related Chapter

[Chapter 06: Deep Dive into Arrays](../../chapters/06-deep-dive-into-arrays.md)

## Further Reading

- [PHP Array Functions](https://www.php.net/manual/en/ref.array.php)
- [PHP 8.4 Release Notes](https://www.php.net/releases/8.4/)
- [Array Best Practices](https://phptherightway.com/#arrays)
