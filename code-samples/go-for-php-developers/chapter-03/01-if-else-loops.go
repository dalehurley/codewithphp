package main

import (
	"bufio"
	"fmt"
	"math/rand"
	"os"
	"strings"
	"time"
)

/*
Control Structures: If-Else and Loops for PHP Developers
=========================================================

PHP has familiar control structures:
	if ($x > 0) { ... } elseif ($x < 0) { ... } else { ... }
	while ($i < 10) { $i++; }
	for ($i = 0; $i < 10; $i++) { ... }
	foreach ($array as $value) { ... }

Go simplifies these:
	if x > 0 { ... } else if x < 0 { ... } else { ... }
	for i < 10 { i++ }  // No while keyword!
	for i := 0; i < 10; i++ { ... }
	for _, value := range array { ... }

Key differences:
- No parentheses around conditions
- Opening brace must be on same line
- Only 'for' loop (replaces while, do-while, foreach)
*/

func main() {
	fmt.Println("=== Control Structures for PHP Developers ===\n")

	demonstrateIfStatements()
	demonstrateIfWithInit()
	demonstrateForLoops()
	demonstrateWhileStyle()
	demonstrateRangeLoop()
	demonstrateLoopControl()
	demonstrateNestedLoops()
	demonstrateInfiniteLoops()
	demonstratePracticalExamples()
}

// demonstrateIfStatements shows if-else patterns
func demonstrateIfStatements() {
	fmt.Println("--- If-Else Statements ---")

	// PHP: if ($age >= 18) { ... }
	// Go: if age >= 18 { ... }  // No parentheses!

	age := 25

	if age >= 18 {
		fmt.Printf("Age %d: Adult\n", age)
	}

	// Multiple conditions
	score := 85

	if score >= 90 {
		fmt.Println("Grade: A")
	} else if score >= 80 {
		fmt.Println("Grade: B")
	} else if score >= 70 {
		fmt.Println("Grade: C")
	} else if score >= 60 {
		fmt.Println("Grade: D")
	} else {
		fmt.Println("Grade: F")
	}

	// Logical operators - same as PHP
	username := "admin"
	password := "secret123"

	if username == "admin" && password == "secret123" {
		fmt.Println("Login successful")
	} else {
		fmt.Println("Login failed")
	}

	// OR operator
	role := "admin"

	if role == "admin" || role == "moderator" {
		fmt.Println("Has elevated privileges")
	}

	// NOT operator
	isActive := true

	if !isActive {
		fmt.Println("Account is inactive")
	} else {
		fmt.Println("Account is active")
	}

	// Comparison operators
	x, y := 10, 20

	if x == y {
		fmt.Println("Equal")
	} else if x != y {
		fmt.Println("Not equal")
	}

	if x < y {
		fmt.Printf("%d is less than %d\n", x, y)
	}

	if x <= y {
		fmt.Printf("%d is less than or equal to %d\n", x, y)
	}

	// No ternary operator in Go!
	// PHP: $max = ($a > $b) ? $a : $b;
	// Go: Must use if-else
	a, b := 15, 25
	var max int
	if a > b {
		max = a
	} else {
		max = b
	}
	fmt.Printf("Max of %d and %d is %d\n", a, b, max)

	fmt.Println()
}

// demonstrateIfWithInit shows if with initialization
func demonstrateIfWithInit() {
	fmt.Println("--- If with Initialization Statement ---")

	// Go unique feature: initialize variable in if statement
	// Scope is limited to if-else block

	// PHP equivalent:
	// $result = getUserRole();
	// if ($result['ok']) {
	//     $role = $result['value'];
	//     // use $role
	// }

	// Go: Cleaner with init statement
	if role := getUserRole(); role == "admin" {
		fmt.Printf("Welcome admin! (role: %s)\n", role)
	} else if role == "user" {
		fmt.Printf("Welcome user! (role: %s)\n", role)
	} else {
		fmt.Printf("Unknown role: %s\n", role)
	}
	// role is not accessible here (out of scope)

	// Multiple init statements using multiple assignment
	if x, y := 10, 20; x < y {
		fmt.Printf("x=%d is less than y=%d\n", x, y)
	}

	// Common pattern: Check error right after function call
	if err := riskyOperation(); err != nil {
		fmt.Printf("Error occurred: %v\n", err)
	} else {
		fmt.Println("Operation successful")
	}

	// With map access
	users := map[string]int{
		"alice": 25,
		"bob":   30,
	}

	// Check if key exists
	if age, exists := users["alice"]; exists {
		fmt.Printf("Alice is %d years old\n", age)
	} else {
		fmt.Println("Alice not found")
	}

	if age, exists := users["charlie"]; exists {
		fmt.Printf("Charlie is %d years old\n", age)
	} else {
		fmt.Println("Charlie not found")
	}

	fmt.Println()
}

// Helper function for demonstration
func getUserRole() string {
	return "admin"
}

// Helper function that returns an error
func riskyOperation() error {
	// Simulating no error
	return nil
}

// demonstrateForLoops shows various for loop styles
func demonstrateForLoops() {
	fmt.Println("--- For Loops ---")

	// PHP: for ($i = 0; $i < 5; $i++) { ... }
	// Go: Same syntax, no parentheses

	fmt.Println("Classic for loop:")
	for i := 0; i < 5; i++ {
		fmt.Printf("%d ", i)
	}
	fmt.Println()

	// Counting down
	fmt.Println("\nCounting down:")
	for i := 5; i > 0; i-- {
		fmt.Printf("%d ", i)
	}
	fmt.Println()

	// Custom increment
	fmt.Println("\nEven numbers:")
	for i := 0; i <= 10; i += 2 {
		fmt.Printf("%d ", i)
	}
	fmt.Println()

	// Multiple variables (must use var before loop)
	fmt.Println("\nFibonacci sequence:")
	a, b := 0, 1
	for i := 0; i < 10; i++ {
		fmt.Printf("%d ", a)
		a, b = b, a+b
	}
	fmt.Println()

	// Omit parts of for loop
	fmt.Println("\nOmitting init:")
	j := 0
	for ; j < 5; j++ {  // No init statement
		fmt.Printf("%d ", j)
	}
	fmt.Println()

	// No post statement
	fmt.Println("\nManual increment:")
	k := 0
	for k < 5 {  // Only condition (like while)
		fmt.Printf("%d ", k)
		k++
	}
	fmt.Println()

	fmt.Println()
}

// demonstrateWhileStyle shows while-style loops
func demonstrateWhileStyle() {
	fmt.Println("--- While-Style Loops ---")

	// PHP: while ($i < 5) { ... }
	// Go: for i < 5 { ... }  // No 'while' keyword!

	fmt.Println("While-style loop:")
	i := 0
	for i < 5 {
		fmt.Printf("%d ", i)
		i++
	}
	fmt.Println()

	// PHP: do { ... } while ($condition);
	// Go: No do-while, but can simulate it

	fmt.Println("\nDo-while style (simulated):")
	n := 0
	for {
		fmt.Printf("%d ", n)
		n++
		if n >= 5 {
			break
		}
	}
	fmt.Println()

	// Reading input until condition
	fmt.Println("\nReading lines (simulated):")
	lines := []string{"line1", "line2", "end", "line4"}
	idx := 0
	for idx < len(lines) {
		line := lines[idx]
		fmt.Printf("Read: %s\n", line)
		idx++
		if line == "end" {
			break
		}
	}

	fmt.Println()
}

// demonstrateRangeLoop shows range-based loops
func demonstrateRangeLoop() {
	fmt.Println("--- Range Loops ---")

	// PHP: foreach ($array as $value) { ... }
	// Go: for _, value := range array { ... }

	// Array/slice iteration
	numbers := []int{10, 20, 30, 40, 50}

	fmt.Println("Range with index and value:")
	for i, num := range numbers {
		fmt.Printf("  [%d] = %d\n", i, num)
	}

	// Just values (ignore index with _)
	fmt.Println("\nJust values:")
	for _, num := range numbers {
		fmt.Printf("%d ", num)
	}
	fmt.Println()

	// Just indices
	fmt.Println("\nJust indices:")
	for i := range numbers {
		fmt.Printf("%d ", i)
	}
	fmt.Println()

	// Map iteration
	// PHP: foreach ($map as $key => $value) { ... }
	scores := map[string]int{
		"Alice": 95,
		"Bob":   87,
		"Carol": 92,
	}

	fmt.Println("\nIterating map:")
	for name, score := range scores {
		fmt.Printf("%s: %d\n", name, score)
	}

	// Just keys
	fmt.Println("\nJust keys:")
	for name := range scores {
		fmt.Printf("%s ", name)
	}
	fmt.Println()

	// String iteration (iterates runes, not bytes!)
	text := "Go语言"

	fmt.Println("\nString iteration (runes):")
	for i, char := range text {
		fmt.Printf("  Position %d: %c (U+%04X)\n", i, char, char)
	}

	// Channel iteration (will be covered in concurrency chapter)
	ch := make(chan int, 3)
	ch <- 1
	ch <- 2
	ch <- 3
	close(ch)

	fmt.Println("\nChannel iteration:")
	for val := range ch {
		fmt.Printf("%d ", val)
	}
	fmt.Println()

	fmt.Println()
}

// demonstrateLoopControl shows break, continue, and labels
func demonstrateLoopControl() {
	fmt.Println("--- Loop Control ---")

	// break - same as PHP
	fmt.Println("Break example:")
	for i := 0; i < 10; i++ {
		if i == 5 {
			break
		}
		fmt.Printf("%d ", i)
	}
	fmt.Println()

	// continue - same as PHP
	fmt.Println("\nContinue example (skip even numbers):")
	for i := 0; i < 10; i++ {
		if i%2 == 0 {
			continue
		}
		fmt.Printf("%d ", i)
	}
	fmt.Println()

	// Labels - Go has labeled breaks (PHP doesn't)
	fmt.Println("\nLabeled break (break outer loop):")
outer:
	for i := 0; i < 3; i++ {
		for j := 0; j < 3; j++ {
			fmt.Printf("(%d,%d) ", i, j)
			if i == 1 && j == 1 {
				break outer  // Breaks outer loop!
			}
		}
		fmt.Println()
	}
	fmt.Println()

	// Labeled continue
	fmt.Println("\nLabeled continue (continue outer loop):")
outerLoop:
	for i := 0; i < 3; i++ {
		for j := 0; j < 3; j++ {
			if j == 1 {
				continue outerLoop  // Continue outer loop
			}
			fmt.Printf("(%d,%d) ", i, j)
		}
		fmt.Println()
	}
	fmt.Println()

	fmt.Println()
}

// demonstrateNestedLoops shows nested loop patterns
func demonstrateNestedLoops() {
	fmt.Println("--- Nested Loops ---")

	// Multiplication table
	fmt.Println("Multiplication table (3x3):")
	for i := 1; i <= 3; i++ {
		for j := 1; j <= 3; j++ {
			fmt.Printf("%2d ", i*j)
		}
		fmt.Println()
	}

	// Pattern printing
	fmt.Println("\nPyramid pattern:")
	for i := 1; i <= 5; i++ {
		for j := 0; j < i; j++ {
			fmt.Print("* ")
		}
		fmt.Println()
	}

	// Iterate 2D slice
	matrix := [][]int{
		{1, 2, 3},
		{4, 5, 6},
		{7, 8, 9},
	}

	fmt.Println("\n2D slice iteration:")
	for i, row := range matrix {
		for j, val := range row {
			fmt.Printf("[%d,%d]=%d ", i, j, val)
		}
		fmt.Println()
	}

	fmt.Println()
}

// demonstrateInfiniteLoops shows infinite loop patterns
func demonstrateInfiniteLoops() {
	fmt.Println("--- Infinite Loops ---")

	// PHP: while (true) { ... }
	// Go: for { ... }

	fmt.Println("Infinite loop with break:")
	count := 0
	for {
		fmt.Printf("%d ", count)
		count++
		if count >= 5 {
			break
		}
	}
	fmt.Println()

	// Simulating a game loop
	fmt.Println("\nSimulated game loop:")
	health := 100
	round := 0

	for {
		round++
		damage := rand.Intn(20) + 10
		health -= damage

		fmt.Printf("Round %d: Took %d damage, health: %d\n", round, damage, health)

		if health <= 0 {
			fmt.Println("Game Over!")
			break
		}

		if round >= 3 {  // Limit for demo
			fmt.Println("Demo ended")
			break
		}
	}

	fmt.Println()
}

// demonstratePracticalExamples shows real-world patterns
func demonstratePracticalExamples() {
	fmt.Println("--- Practical Examples ---")

	// Example 1: Find first occurrence
	numbers := []int{1, 3, 5, 8, 9, 12, 15}
	target := 8

	found := false
	for i, num := range numbers {
		if num == target {
			fmt.Printf("Found %d at index %d\n", target, i)
			found = true
			break
		}
	}
	if !found {
		fmt.Printf("%d not found\n", target)
	}

	// Example 2: Filter slice
	fmt.Println("\nFilter even numbers:")
	var evens []int
	for _, num := range numbers {
		if num%2 == 0 {
			evens = append(evens, num)
		}
	}
	fmt.Printf("Even numbers: %v\n", evens)

	// Example 3: Sum and average
	sum := 0
	for _, num := range numbers {
		sum += num
	}
	avg := float64(sum) / float64(len(numbers))
	fmt.Printf("\nSum: %d, Average: %.2f\n", sum, avg)

	// Example 4: Find min and max
	if len(numbers) > 0 {
		min, max := numbers[0], numbers[0]
		for _, num := range numbers {
			if num < min {
				min = num
			}
			if num > max {
				max = num
			}
		}
		fmt.Printf("Min: %d, Max: %d\n", min, max)
	}

	// Example 5: Reverse a slice
	reversed := make([]int, len(numbers))
	for i, num := range numbers {
		reversed[len(numbers)-1-i] = num
	}
	fmt.Printf("\nOriginal: %v\n", numbers)
	fmt.Printf("Reversed: %v\n", reversed)

	// Example 6: Count occurrences
	words := []string{"apple", "banana", "apple", "orange", "banana", "apple"}
	counts := make(map[string]int)

	for _, word := range words {
		counts[word]++
	}

	fmt.Println("\nWord counts:")
	for word, count := range counts {
		fmt.Printf("%s: %d\n", word, count)
	}

	// Example 7: Process until sentinel value
	fmt.Println("\nProcessing until 'quit':")
	commands := []string{"start", "run", "process", "quit", "ignored"}
	for _, cmd := range commands {
		if cmd == "quit" {
			fmt.Println("Received quit command")
			break
		}
		fmt.Printf("Executing: %s\n", cmd)
	}

	// Example 8: Retry pattern
	fmt.Println("\nRetry pattern:")
	maxRetries := 3
	for attempt := 1; attempt <= maxRetries; attempt++ {
		fmt.Printf("Attempt %d/%d...\n", attempt, maxRetries)

		// Simulate operation (fails first 2 times)
		if attempt < 3 {
			fmt.Println("  Failed, retrying...")
			time.Sleep(100 * time.Millisecond)
			continue
		}

		fmt.Println("  Success!")
		break
	}

	fmt.Println()
}

/*
Example of reading input in a loop (commented out for automated testing):

func readUserInput() {
	fmt.Println("Enter text (type 'quit' to exit):")
	scanner := bufio.NewScanner(os.Stdin)

	for scanner.Scan() {
		text := scanner.Text()

		if strings.ToLower(text) == "quit" {
			fmt.Println("Goodbye!")
			break
		}

		fmt.Printf("You entered: %s\n", text)
		fmt.Println("Enter more text:")
	}

	if err := scanner.Err(); err != nil {
		fmt.Printf("Error reading input: %v\n", err)
	}
}
*/

/*
Expected Output:
=== Control Structures for PHP Developers ===

--- If-Else Statements ---
Age 25: Adult
Grade: B
Login successful
Has elevated privileges
Account is active
Not equal
10 is less than 20
10 is less than or equal to 20
Max of 15 and 25 is 25

--- If with Initialization Statement ---
Welcome admin! (role: admin)
x=10 is less than y=20
Operation successful
Alice is 25 years old
Charlie not found

--- For Loops ---
Classic for loop:
0 1 2 3 4

Counting down:
5 4 3 2 1

Even numbers:
0 2 4 6 8 10

Fibonacci sequence:
0 1 1 2 3 5 8 13 21 34

Omitting init:
0 1 2 3 4

Manual increment:
0 1 2 3 4

--- While-Style Loops ---
While-style loop:
0 1 2 3 4

Do-while style (simulated):
0 1 2 3 4

Reading lines (simulated):
Read: line1
Read: line2
Read: end

--- Range Loops ---
Range with index and value:
  [0] = 10
  [1] = 20
  [2] = 30
  [3] = 40
  [4] = 50

Just values:
10 20 30 40 50

Just indices:
0 1 2 3 4

Iterating map:
Alice: 95
Bob: 87
Carol: 92

Just keys:
Alice Bob Carol

String iteration (runes):
  Position 0: G (U+0047)
  Position 1: o (U+006F)
  Position 2: 语 (U+8BED)
  Position 5: 言 (U+8A00)

Channel iteration:
1 2 3

--- Loop Control ---
Break example:
0 1 2 3 4

Continue example (skip even numbers):
1 3 5 7 9

Labeled break (break outer loop):
(0,0) (0,1) (0,2)
(1,0) (1,1)

Labeled continue (continue outer loop):
(0,0) (1,0) (2,0)

--- Nested Loops ---
Multiplication table (3x3):
 1  2  3
 2  4  6
 3  6  9

Pyramid pattern:
*
* *
* * *
* * * *
* * * * *

2D slice iteration:
[0,0]=1 [0,1]=2 [0,2]=3
[1,0]=4 [1,1]=5 [1,2]=6
[2,0]=7 [2,1]=8 [2,2]=9

--- Infinite Loops ---
Infinite loop with break:
0 1 2 3 4

Simulated game loop:
Round 1: Took 15 damage, health: 85
Round 2: Took 18 damage, health: 67
Round 3: Took 12 damage, health: 55
Demo ended

--- Practical Examples ---
Found 8 at index 3

Filter even numbers:
Even numbers: [8 12]

Sum: 53, Average: 7.57
Min: 1, Max: 15

Original: [1 3 5 8 9 12 15]
Reversed: [15 12 9 8 5 3 1]

Word counts:
apple: 3
banana: 2
orange: 1

Processing until 'quit':
Executing: start
Executing: run
Executing: process
Received quit command

Retry pattern:
Attempt 1/3...
  Failed, retrying...
Attempt 2/3...
  Failed, retrying...
Attempt 3/3...
  Success!
*/
