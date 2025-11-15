package main

import (
	"errors"
	"fmt"
	"strconv"
)

/*
PHP VERSION:
<?php
function divide(float $a, float $b): float {
    if ($b == 0) {
        throw new Exception("Division by zero");
    }
    return $a / $b;
}

try {
    $result = divide(10, 2);
    echo "Result: $result\n";

    $result = divide(10, 0);  // Throws exception
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
*/

func main() {
	fmt.Println("=== Functions and Error Handling in Go ===\n")

	// Simple function call
	sum := add(5, 3)
	fmt.Printf("5 + 3 = %d\n", sum)

	// Function with multiple return values (very common in Go!)
	quotient, err := divide(10, 2)
	if err != nil {
		fmt.Printf("Error: %v\n", err)
	} else {
		fmt.Printf("10 / 2 = %.2f\n", quotient)
	}

	// Error handling - no try/catch in Go
	quotient, err = divide(10, 0)
	if err != nil {
		fmt.Printf("Error dividing: %v\n", err)
	}

	// Named return values
	result := multiply(4, 5)
	fmt.Printf("4 * 5 = %d\n", result)

	// Defer statement - runs when function exits
	demonstrateDefer()

	// Multiple return values in action
	min, max := findMinMax([]int{3, 7, 2, 9, 1, 5})
	fmt.Printf("\nMin: %d, Max: %d\n", min, max)

	// Practical error handling example
	age, err := parseAge("25")
	if err != nil {
		fmt.Printf("Error: %v\n", err)
	} else {
		fmt.Printf("Parsed age: %d\n", age)
	}

	// Invalid age
	age, err = parseAge("invalid")
	if err != nil {
		fmt.Printf("Error: %v\n", err)
	}

	// Variadic functions (like PHP's ...args)
	total := sum(1, 2, 3, 4, 5)
	fmt.Printf("\nSum of 1,2,3,4,5 = %d\n", total)
}

// Simple function (like PHP)
func add(a int, b int) int {
	return a + b
}

// Can group same types
func subtract(a, b int) int {
	return a - b
}

// Multiple return values (idiomatic error handling)
// This is THE Go way - no exceptions!
func divide(a, b float64) (float64, error) {
	if b == 0 {
		return 0, errors.New("division by zero")
	}
	return a / b, nil // nil means no error
}

// Named return values
func multiply(a, b int) (result int) {
	result = a * b
	return // returns named variable
}

// Variadic function (accepts any number of arguments)
func sum(numbers ...int) int {
	total := 0
	for _, num := range numbers {
		total += num
	}
	return total
}

// Function demonstrating defer
func demonstrateDefer() {
	fmt.Println("\n=== Defer Example ===")
	fmt.Println("Start")

	// defer executes when function returns
	defer fmt.Println("This runs last (deferred)")

	fmt.Println("Middle")
	fmt.Println("End")
	// defer runs here, before function exits
}

// Practical example: finding min and max
func findMinMax(numbers []int) (min int, max int) {
	if len(numbers) == 0 {
		return 0, 0
	}

	min = numbers[0]
	max = numbers[0]

	for _, num := range numbers {
		if num < min {
			min = num
		}
		if num > max {
			max = num
		}
	}

	return // returns named values
}

// Real-world error handling example
func parseAge(s string) (int, error) {
	age, err := strconv.Atoi(s)
	if err != nil {
		return 0, fmt.Errorf("invalid age format: %w", err)
	}

	if age < 0 || age > 150 {
		return 0, errors.New("age out of valid range")
	}

	return age, nil
}

/*
OUTPUT:
=== Functions and Error Handling in Go ===

5 + 3 = 8
10 / 2 = 5.00
Error dividing: division by zero
4 * 5 = 20

=== Defer Example ===
Start
Middle
End
This runs last (deferred)

Min: 1, Max: 9
Parsed age: 25
Error: invalid age format: strconv.Atoi: parsing "invalid": invalid syntax

Sum of 1,2,3,4,5 = 15
*/

/*
KEY DIFFERENCES FROM PHP:

1. NO EXCEPTIONS - Use multiple return values:
   PHP: throw new Exception("error");
   Go:  return 0, errors.New("error")

2. ERROR CHECKING PATTERN:
   PHP: try { } catch (Exception $e) { }
   Go:  if err != nil { }

3. MULTIPLE RETURN VALUES:
   PHP: Can only return one value (or array)
   Go:  Can return multiple values directly

4. DEFER STATEMENT:
   PHP: No equivalent (use finally or register_shutdown_function)
   Go:  defer runs code when function exits

5. FUNCTION SIGNATURE:
   PHP: function name(Type $param): ReturnType
   Go:  func name(param Type) ReturnType

ERROR HANDLING BEST PRACTICES:

1. Always check errors:
   ❌ result, _ := divide(10, 2)  // Ignoring error!
   ✅ result, err := divide(10, 2)
      if err != nil { handle error }

2. Return errors, don't panic:
   ❌ panic("something went wrong")  // Only for unrecoverable errors
   ✅ return fmt.Errorf("something went wrong")

3. Wrap errors for context:
   return fmt.Errorf("failed to parse age: %w", err)

4. Use nil for no error:
   return result, nil  // Success

5. Check error before using result:
   if err != nil {
       return err  // Handle error first
   }
   // Now safe to use result

DEFER USAGE:

Common patterns:
- Closing files: defer file.Close()
- Unlocking mutexes: defer mu.Unlock()
- Recovering from panics: defer recover()

Multiple defers execute in LIFO order:
defer fmt.Println("1")
defer fmt.Println("2")
defer fmt.Println("3")
// Prints: 3, 2, 1

NEXT STEPS:
- Run: go run 03-functions-and-errors.go
- Practice writing functions with error returns
- Get comfortable with "if err != nil" pattern
*/
