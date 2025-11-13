<?php
/**
 * Chapter 04: PHP Syntax for Rails Developers
 * 
 * This file contains side-by-side syntax comparisons
 * for common programming patterns.
 */

declare(strict_types=1);

// ============ VARIABLES ============

// Ruby:
// name = "John"
// age = 30
// items = [1, 2, 3]
// user = {name: "John", age: 30}

// PHP:
$name = "John";
$age = 30;
$items = [1, 2, 3];
$user = ['name' => 'John', 'age' => 30];

echo "Variables:\n";
echo "Name: $name\n";
echo "Age: $age\n";
echo "Items: " . implode(', ', $items) . "\n";
echo "User: " . json_encode($user) . "\n";

// ============ ARRAYS ============

// Ruby:
// arr = [1, 2, 3]
// arr.push(4)
// arr.each { |item| puts item }
// arr.map { |x| x * 2 }
// arr.select { |x| x > 2 }

// PHP:
$arr = [1, 2, 3];
$arr[] = 4;  // or array_push($arr, 4)

echo "\nArrays:\n";
foreach ($arr as $item) {
    echo "$item ";
}
echo "\n";

// Map
$doubled = array_map(fn($x) => $x * 2, $arr);
echo "Doubled: " . implode(', ', $doubled) . "\n";

// Filter
$filtered = array_filter($arr, fn($x) => $x > 2);
echo "Filtered (>2): " . implode(', ', $filtered) . "\n";

// ============ CONTROL FLOW ============

// Ruby:
// if age > 18
//   puts "Adult"
// elsif age >= 13
//   puts "Teenager"
// else
//   puts "Child"
// end

// PHP:
echo "\nControl Flow:\n";
if ($age > 18) {
    echo "Adult\n";
} elseif ($age >= 13) {
    echo "Teenager\n";
} else {
    echo "Child\n";
}

// ============ LOOPS ============

// Ruby:
// 5.times { |i| puts i }
// (1..10).each { |i| puts i }
// arr.each_with_index { |item, idx| puts "#{idx}: #{item}" }

// PHP:
echo "\nLoops:\n";

// C-style for loop
for ($i = 0; $i < 5; $i++) {
    echo "$i ";
}
echo "\n";

// Foreach with index
foreach ($items as $idx => $item) {
    echo "$idx: $item ";
}
echo "\n";

// While loop
$count = 0;
while ($count < 3) {
    echo "$count ";
    $count++;
}
echo "\n";

// ============ FUNCTIONS ============

// Ruby:
// def greet(name, age = 30)
//   "Hello #{name}, age #{age}"
// end
//
// greet("John")
// greet("John", 25)

// PHP:
function greet(string $name, int $age = 30): string
{
    return "Hello $name, age $age";
}

echo "\nFunctions:\n";
echo greet("John") . "\n";
echo greet("John", 25) . "\n";

// Named arguments (PHP 8.0+)
echo greet(name: "Jane", age: 28) . "\n";

// ============ CLASSES ============

// Ruby:
// class Person
//   attr_reader :name, :age
//   attr_writer :age
//
//   def initialize(name, age)
//     @name = name
//     @age = age
//   end
//
//   def introduce
//     "I'm #{@name}, #{@age} years old"
//   end
// end

// PHP:
class Person
{
    public function __construct(
        private string $name,
        private int $age
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function setAge(int $age): void
    {
        $this->age = $age;
    }

    public function introduce(): string
    {
        return "I'm {$this->name}, {$this->age} years old";
    }
}

echo "\nClasses:\n";
$person = new Person("Alice", 28);
echo $person->introduce() . "\n";
$person->setAge(29);
echo "New age: " . $person->getAge() . "\n";

// ============ STRING INTERPOLATION ============

// Ruby:
// message = "Hello #{name}"
// message = "Result: #{1 + 2}"

// PHP:
echo "\nString Interpolation:\n";
$message = "Hello $name";
echo "$message\n";

// Complex expressions need curly braces
$result = "Result: {$age}";
echo "$result\n";

// Or use concatenation
$message = "Hello " . $name;
echo "$message\n";

// Or use sprintf
$message = sprintf("Hello %s, age %d", $name, $age);
echo "$message\n";

// ============ HASH / ASSOCIATIVE ARRAY ============

// Ruby:
// user = {name: "John", age: 30, active: true}
// user[:name]
// user[:email] = "john@example.com"
// user.each { |key, value| puts "#{key}: #{value}" }

// PHP:
echo "\nAssociative Arrays:\n";
$user = ['name' => 'John', 'age' => 30, 'active' => true];
echo "Name: " . $user['name'] . "\n";

$user['email'] = 'john@example.com';
foreach ($user as $key => $value) {
    echo "$key: " . (is_bool($value) ? ($value ? 'true' : 'false') : $value) . "\n";
}

// ============ ERROR HANDLING ============

// Ruby:
// begin
//   risky_operation
// rescue StandardError => e
//   puts "Error: #{e.message}"
// ensure
//   cleanup
// end

// PHP:
echo "\nError Handling:\n";
try {
    $result = 10 / 0;  // This would throw an error
} catch (DivisionByZeroError $e) {
    echo "Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Unexpected error: " . $e->getMessage() . "\n";
} finally {
    echo "Cleanup code\n";
}

echo "\n✓ All syntax examples completed!\n";

