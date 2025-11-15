package main

import (
	"errors"
	"fmt"
	"math"
	"strings"
)

/*
Functions and Multiple Returns for PHP Developers
==================================================

PHP functions:
	function divide($a, $b) {
	    if ($b == 0) {
	        return null;  // or throw exception
	    }
	    return $a / $b;
	}

Go functions with multiple returns:
	func divide(a, b float64) (float64, error) {
	    if b == 0 {
	        return 0, errors.New("division by zero")
	    }
	    return a / b, nil
	}

Key features:
- Multiple return values
- Named return values
- Error as return value (no exceptions!)
- First-class functions
- Closures
- Variadic functions
- Defer statement
*/

func main() {
	fmt.Println("=== Functions and Multiple Returns ===\n")

	demonstrateBasicFunctions()
	demonstrateMultipleReturns()
	demonstrateNamedReturns()
	demonstrateErrorHandling()
	demonstrateVariadicFunctions()
	demonstrateAnonymousFunctions()
	demonstrateClosures()
	demonstrateDefer()
	demonstratePracticalExamples()
}

// demonstrateBasicFunctions shows basic function syntax
func demonstrateBasicFunctions() {
	fmt.Println("--- Basic Functions ---")

	// PHP: function add($a, $b) { return $a + $b; }
	// Go: Similar but with types

	result := add(10, 20)
	fmt.Printf("add(10, 20) = %d\n", result)

	// Multiple parameters of same type
	result = multiply(5, 6)
	fmt.Printf("multiply(5, 6) = %d\n", result)

	// No parameters
	greet()

	// No return value (void in other languages)
	printSum(15, 25)

	// Different parameter types
	info := formatUser("Alice", 30)
	fmt.Println(info)

	fmt.Println()
}

// Basic function
func add(a int, b int) int {
	return a + b
}

// Multiple parameters of same type (shorthand)
func multiply(a, b int) int {
	return a * b
}

// No parameters
func greet() {
	fmt.Println("Hello, World!")
}

// No return value
func printSum(a, b int) {
	fmt.Printf("Sum: %d\n", a+b)
}

// Different parameter types
func formatUser(name string, age int) string {
	return fmt.Sprintf("%s is %d years old", name, age)
}

// demonstrateMultipleReturns shows multiple return values
func demonstrateMultipleReturns() {
	fmt.Println("--- Multiple Return Values ---")

	// PHP: Would return array or object
	// list($quot, $rem) = divmod(17, 5);

	// Go: Return multiple values directly
	quotient, remainder := divmod(17, 5)
	fmt.Printf("17 ÷ 5 = %d remainder %d\n", quotient, remainder)

	// Ignore return value with _
	q, _ := divmod(20, 3)
	fmt.Printf("Quotient only: %d\n", q)

	_, r := divmod(25, 7)
	fmt.Printf("Remainder only: %d\n", r)

	// Return multiple different types
	name, age, active := getUserInfo()
	fmt.Printf("User: %s, Age: %d, Active: %v\n", name, age, active)

	// Return calculated values
	min, max, avg := stats([]int{5, 2, 8, 1, 9, 3})
	fmt.Printf("Stats: min=%d, max=%d, avg=%.2f\n", min, max, avg)

	fmt.Println()
}

// divmod returns quotient and remainder
func divmod(a, b int) (int, int) {
	return a / b, a % b
}

// getUserInfo returns multiple types
func getUserInfo() (string, int, bool) {
	return "John Doe", 35, true
}

// stats calculates min, max, and average
func stats(numbers []int) (int, int, float64) {
	if len(numbers) == 0 {
		return 0, 0, 0
	}

	min, max := numbers[0], numbers[0]
	sum := 0

	for _, num := range numbers {
		if num < min {
			min = num
		}
		if num > max {
			max = num
		}
		sum += num
	}

	avg := float64(sum) / float64(len(numbers))
	return min, max, avg
}

// demonstrateNamedReturns shows named return values
func demonstrateNamedReturns() {
	fmt.Println("--- Named Return Values ---")

	// Named returns act as variables in function
	// Useful for documentation and naked returns

	area, perimeter := rectangleProps(5, 10)
	fmt.Printf("Rectangle 5×10: area=%d, perimeter=%d\n", area, perimeter)

	volume, surfaceArea := boxProps(3, 4, 5)
	fmt.Printf("Box 3×4×5: volume=%d, surface area=%d\n", volume, surfaceArea)

	// Named returns with early return
	value, err := parsePositiveInt("42")
	if err != nil {
		fmt.Printf("Error: %v\n", err)
	} else {
		fmt.Printf("Parsed: %d\n", value)
	}

	value, err = parsePositiveInt("-10")
	if err != nil {
		fmt.Printf("Error: %v\n", err)
	}

	fmt.Println()
}

// Named return values
func rectangleProps(width, height int) (area int, perimeter int) {
	area = width * height
	perimeter = 2 * (width + height)
	return // Naked return uses named values
}

// More complex named returns
func boxProps(length, width, height int) (volume int, surfaceArea int) {
	volume = length * width * height
	surfaceArea = 2 * (length*width + length*height + width*height)
	return
}

// Named returns with conditional logic
func parsePositiveInt(s string) (value int, err error) {
	// Simplified parsing
	if s == "" {
		err = errors.New("empty string")
		return
	}

	if s[0] == '-' {
		err = errors.New("negative number")
		return
	}

	// Simulate successful parsing
	value = 42
	return
}

// demonstrateErrorHandling shows idiomatic error handling
func demonstrateErrorHandling() {
	fmt.Println("--- Error Handling ---")

	// Go uses errors as return values, not exceptions
	// PHP: try { $result = divide(10, 2); } catch (Exception $e) { ... }
	// Go: result, err := divide(10, 2); if err != nil { ... }

	// Successful operation
	result, err := divide(10, 2)
	if err != nil {
		fmt.Printf("Error: %v\n", err)
	} else {
		fmt.Printf("10 / 2 = %.2f\n", result)
	}

	// Error case
	result, err = divide(10, 0)
	if err != nil {
		fmt.Printf("Error: %v\n", err)
	} else {
		fmt.Printf("10 / 0 = %.2f\n", result)
	}

	// Multiple operations with error checking
	fmt.Println("\nChained operations:")
	if r1, err := sqrt(16); err != nil {
		fmt.Printf("Error: %v\n", err)
	} else {
		fmt.Printf("sqrt(16) = %.2f\n", r1)
	}

	if r2, err := sqrt(-4); err != nil {
		fmt.Printf("Error: %v\n", err)
	} else {
		fmt.Printf("sqrt(-4) = %.2f\n", r2)
	}

	// Custom error types
	user, err := findUser(123)
	if err != nil {
		fmt.Printf("Error: %v\n", err)
	} else {
		fmt.Printf("Found user: %s\n", user)
	}

	user, err = findUser(999)
	if err != nil {
		fmt.Printf("Error: %v\n", err)
	}

	fmt.Println()
}

// divide returns result or error
func divide(a, b float64) (float64, error) {
	if b == 0 {
		return 0, errors.New("division by zero")
	}
	return a / b, nil
}

// sqrt returns square root or error
func sqrt(x float64) (float64, error) {
	if x < 0 {
		return 0, fmt.Errorf("cannot compute square root of negative number: %f", x)
	}
	return math.Sqrt(x), nil
}

// Custom error
type NotFoundError struct {
	ID int
}

func (e *NotFoundError) Error() string {
	return fmt.Sprintf("user with ID %d not found", e.ID)
}

// findUser demonstrates custom error
func findUser(id int) (string, error) {
	users := map[int]string{
		123: "Alice",
		456: "Bob",
	}

	if name, ok := users[id]; ok {
		return name, nil
	}

	return "", &NotFoundError{ID: id}
}

// demonstrateVariadicFunctions shows variadic parameters
func demonstrateVariadicFunctions() {
	fmt.Println("--- Variadic Functions ---")

	// PHP: function sum(...$numbers) { ... }
	// Go: func sum(numbers ...int) int { ... }

	fmt.Printf("sum(1, 2, 3) = %d\n", sum(1, 2, 3))
	fmt.Printf("sum(1, 2, 3, 4, 5) = %d\n", sum(1, 2, 3, 4, 5))
	fmt.Printf("sum() = %d\n", sum())

	// Mix regular and variadic parameters
	fmt.Println(greetAll("Hello", "Alice", "Bob", "Carol"))

	// Spread slice into variadic function
	numbers := []int{10, 20, 30, 40}
	fmt.Printf("sum of slice: %d\n", sum(numbers...))

	// Multiple types
	printAll("Age:", 25, "Score:", 95.5, "Active:", true)

	fmt.Println()
}

// Variadic function
func sum(numbers ...int) int {
	total := 0
	for _, num := range numbers {
		total += num
	}
	return total
}

// Mix regular and variadic parameters
func greetAll(greeting string, names ...string) string {
	return fmt.Sprintf("%s %s", greeting, strings.Join(names, ", "))
}

// Variadic with interface{} (any type)
func printAll(args ...interface{}) {
	for i, arg := range args {
		fmt.Printf("[%d] %v (%T) ", i, arg, arg)
	}
	fmt.Println()
}

// demonstrateAnonymousFunctions shows anonymous functions
func demonstrateAnonymousFunctions() {
	fmt.Println("--- Anonymous Functions ---")

	// PHP: $add = function($a, $b) { return $a + $b; };
	// Go: add := func(a, b int) int { return a + b }

	// Assign to variable
	add := func(a, b int) int {
		return a + b
	}
	fmt.Printf("Anonymous add(5, 3) = %d\n", add(5, 3))

	// Immediate invocation
	result := func(x, y int) int {
		return x * y
	}(4, 5)
	fmt.Printf("Immediate invocation: %d\n", result)

	// Function as parameter
	numbers := []int{1, 2, 3, 4, 5}
	doubled := mapInts(numbers, func(n int) int {
		return n * 2
	})
	fmt.Printf("Doubled: %v\n", doubled)

	squared := mapInts(numbers, func(n int) int {
		return n * n
	})
	fmt.Printf("Squared: %v\n", squared)

	// Filter with anonymous function
	evens := filterInts(numbers, func(n int) bool {
		return n%2 == 0
	})
	fmt.Printf("Evens: %v\n", evens)

	fmt.Println()
}

// Higher-order function: map
func mapInts(numbers []int, fn func(int) int) []int {
	result := make([]int, len(numbers))
	for i, num := range numbers {
		result[i] = fn(num)
	}
	return result
}

// Higher-order function: filter
func filterInts(numbers []int, fn func(int) bool) []int {
	result := []int{}
	for _, num := range numbers {
		if fn(num) {
			result = append(result, num)
		}
	}
	return result
}

// demonstrateClosures shows closure behavior
func demonstrateClosures() {
	fmt.Println("--- Closures ---")

	// PHP: Closures need 'use' keyword
	// Go: Closures automatically capture variables

	// Counter closure
	counter := makeCounter()
	fmt.Printf("counter() = %d\n", counter())
	fmt.Printf("counter() = %d\n", counter())
	fmt.Printf("counter() = %d\n", counter())

	// New counter instance
	counter2 := makeCounter()
	fmt.Printf("counter2() = %d\n", counter2())

	// Closure with parameter
	addN := makeAdder(10)
	fmt.Printf("addN(5) = %d\n", addN(5))
	fmt.Printf("addN(3) = %d\n", addN(3))

	// Multiplier
	times3 := makeMultiplier(3)
	fmt.Printf("times3(4) = %d\n", times3(4))

	// Closure capturing loop variable (common gotcha)
	funcs := []func() int{}
	for i := 0; i < 3; i++ {
		i := i  // Capture current value
		funcs = append(funcs, func() int {
			return i
		})
	}

	fmt.Print("Closure values: ")
	for _, f := range funcs {
		fmt.Printf("%d ", f())
	}
	fmt.Println()

	fmt.Println()
}

// makeCounter returns a closure that increments
func makeCounter() func() int {
	count := 0
	return func() int {
		count++
		return count
	}
}

// makeAdder returns a closure that adds n
func makeAdder(n int) func(int) int {
	return func(x int) int {
		return x + n
	}
}

// makeMultiplier returns a closure that multiplies by n
func makeMultiplier(n int) func(int) int {
	return func(x int) int {
		return x * n
	}
}

// demonstrateDefer shows defer statement
func demonstrateDefer() {
	fmt.Println("--- Defer Statement ---")

	// PHP: No defer, must manually cleanup
	// Go: defer executes function at end of current function

	fmt.Println("Defer example:")
	deferExample()

	fmt.Println("\nMultiple defers (LIFO order):")
	multipleDefers()

	fmt.Println("\nDefer with arguments (evaluated immediately):")
	deferArgs(10)

	fmt.Println("\nPractical defer (resource cleanup):")
	processFile()

	fmt.Println()
}

// deferExample shows basic defer
func deferExample() {
	defer fmt.Println("Deferred: Last")
	fmt.Println("First")
	fmt.Println("Second")
}

// multipleDefers shows LIFO execution
func multipleDefers() {
	defer fmt.Println("Defer 1")
	defer fmt.Println("Defer 2")
	defer fmt.Println("Defer 3")
	fmt.Println("Main function")
}

// deferArgs shows argument evaluation
func deferArgs(x int) {
	defer fmt.Printf("Deferred x = %d\n", x)
	x = 20
	fmt.Printf("Modified x = %d\n", x)
}

// processFile demonstrates resource cleanup with defer
func processFile() {
	// Simulating file operations
	file := "data.txt"
	fmt.Printf("Opening %s\n", file)

	defer func() {
		fmt.Printf("Closing %s\n", file)
	}()

	fmt.Println("Processing data...")
	fmt.Println("More processing...")

	// File automatically closed via defer
}

// demonstratePracticalExamples shows real-world function patterns
func demonstratePracticalExamples() {
	fmt.Println("--- Practical Examples ---")

	// Example 1: Parse and validate
	if num, err := parseAndValidate("42", 0, 100); err != nil {
		fmt.Printf("Validation error: %v\n", err)
	} else {
		fmt.Printf("Valid number: %d\n", num)
	}

	// Example 2: Safe division with multiple checks
	if result, err := safeDivide(100, 4); err != nil {
		fmt.Printf("Division error: %v\n", err)
	} else {
		fmt.Printf("100 / 4 = %.2f\n", result)
	}

	// Example 3: Swap function
	a, b := 10, 20
	fmt.Printf("Before swap: a=%d, b=%d\n", a, b)
	a, b = swap(a, b)
	fmt.Printf("After swap: a=%d, b=%d\n", a, b)

	// Example 4: Min/Max helper
	fmt.Printf("min(5, 3) = %d\n", min(5, 3))
	fmt.Printf("max(5, 3) = %d\n", max(5, 3))

	// Example 5: Contains check
	nums := []int{1, 2, 3, 4, 5}
	fmt.Printf("Contains 3: %v\n", contains(nums, 3))
	fmt.Printf("Contains 10: %v\n", contains(nums, 10))

	// Example 6: Curry function
	add5 := curry(5)
	fmt.Printf("curry(5)(3) = %d\n", add5(3))

	fmt.Println()
}

// parseAndValidate parses and validates a number
func parseAndValidate(s string, min, max int) (int, error) {
	// Simplified parsing
	num := 42

	if num < min {
		return 0, fmt.Errorf("value %d is less than minimum %d", num, min)
	}

	if num > max {
		return 0, fmt.Errorf("value %d is greater than maximum %d", num, max)
	}

	return num, nil
}

// safeDivide performs division with validation
func safeDivide(a, b float64) (float64, error) {
	if b == 0 {
		return 0, errors.New("division by zero")
	}

	if math.IsNaN(a) || math.IsNaN(b) {
		return 0, errors.New("NaN value")
	}

	if math.IsInf(a, 0) || math.IsInf(b, 0) {
		return 0, errors.New("infinite value")
	}

	return a / b, nil
}

// swap returns swapped values
func swap(a, b int) (int, int) {
	return b, a
}

// min returns minimum of two integers
func min(a, b int) int {
	if a < b {
		return a
	}
	return b
}

// max returns maximum of two integers
func max(a, b int) int {
	if a > b {
		return a
	}
	return b
}

// contains checks if slice contains value
func contains(slice []int, value int) bool {
	for _, item := range slice {
		if item == value {
			return true
		}
	}
	return false
}

// curry demonstrates function currying
func curry(x int) func(int) int {
	return func(y int) int {
		return x + y
	}
}

/*
Expected Output:
=== Functions and Multiple Returns ===

--- Basic Functions ---
add(10, 20) = 30
multiply(5, 6) = 30
Hello, World!
Sum: 40
Alice is 30 years old

--- Multiple Return Values ---
17 ÷ 5 = 3 remainder 2
Quotient only: 6
Remainder only: 4
User: John Doe, Age: 35, Active: true
Stats: min=1, max=9, avg=4.67

--- Named Return Values ---
Rectangle 5×10: area=50, perimeter=30
Box 3×4×5: volume=60, surface area=94
Parsed: 42
Error: negative number

--- Error Handling ---
10 / 2 = 5.00
Error: division by zero

Chained operations:
sqrt(16) = 4.00
Error: cannot compute square root of negative number: -4.000000
Found user: Alice
Error: user with ID 999 not found

--- Variadic Functions ---
sum(1, 2, 3) = 6
sum(1, 2, 3, 4, 5) = 15
sum() = 0
Hello Alice, Bob, Carol
sum of slice: 100
[0] Age: (string) [1] 25 (int) [2] Score: (string) [3] 95.5 (float64) [4] Active: (string) [5] true (bool)

--- Anonymous Functions ---
Anonymous add(5, 3) = 8
Immediate invocation: 20
Doubled: [2 4 6 8 10]
Squared: [1 4 9 16 25]
Evens: [2 4]

--- Closures ---
counter() = 1
counter() = 2
counter() = 3
counter2() = 1
addN(5) = 15
addN(3) = 13
times3(4) = 12
Closure values: 0 1 2

--- Defer Statement ---
Defer example:
First
Second
Deferred: Last

Multiple defers (LIFO order):
Main function
Defer 3
Defer 2
Defer 1

Defer with arguments (evaluated immediately):
Modified x = 20
Deferred x = 10

Practical defer (resource cleanup):
Opening data.txt
Processing data...
More processing...
Closing data.txt

--- Practical Examples ---
Valid number: 42
100 / 4 = 25.00
Before swap: a=10, b=20
After swap: a=20, b=10
min(5, 3) = 3
max(5, 3) = 5
Contains 3: true
Contains 10: false
curry(5)(3) = 8
*/
