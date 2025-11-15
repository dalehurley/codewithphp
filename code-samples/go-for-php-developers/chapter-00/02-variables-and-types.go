package main

import "fmt"

/*
PHP VERSION:
<?php
$name = "Alice";           // Dynamic typing
$age = 30;                 // Int inferred
$price = 19.99;            // Float inferred
$active = true;            // Boolean
$count = null;             // Null allowed

// Type juggling happens automatically
$result = $age + "10";     // "40" - string coerced to int
$weird = $age . " years";  // "30 years" - int coerced to string
*/

func main() {
	fmt.Println("=== Variables in Go vs PHP ===\n")

	// Method 1: var with explicit type
	var name string = "Alice"
	var age int = 30
	var price float64 = 19.99
	var active bool = true

	fmt.Printf("Name: %s (type: string)\n", name)
	fmt.Printf("Age: %d (type: int)\n", age)
	fmt.Printf("Price: %.2f (type: float64)\n", price)
	fmt.Printf("Active: %t (type: bool)\n", active)

	// Method 2: var with type inference
	var city = "New York"    // Type inferred as string
	var population = 8000000 // Type inferred as int

	fmt.Printf("\nCity: %s\n", city)
	fmt.Printf("Population: %d\n", population)

	// Method 3: Short declaration (most common in Go)
	country := "USA"         // := declares and assigns
	founded := 1776

	fmt.Printf("\nCountry: %s\n", country)
	fmt.Printf("Founded: %d\n", founded)

	// Constants
	const Pi = 3.14159
	const AppName = "MyApp"

	fmt.Printf("\nPi: %f\n", Pi)
	fmt.Printf("App: %s\n", AppName)

	// Zero values (no "null" in Go for basic types)
	var emptyString string   // "" (empty string, NOT null)
	var zeroInt int         // 0
	var zeroFloat float64   // 0.0
	var zeroBool bool       // false

	fmt.Println("\n=== Zero Values (not null!) ===")
	fmt.Printf("Empty string: '%s' (length: %d)\n", emptyString, len(emptyString))
	fmt.Printf("Zero int: %d\n", zeroInt)
	fmt.Printf("Zero float: %f\n", zeroFloat)
	fmt.Printf("Zero bool: %t\n", zeroBool)

	// Type conversion (NO automatic type juggling like PHP!)
	var intValue int = 42
	var floatValue float64 = float64(intValue) // Explicit conversion required
	var stringValue string = fmt.Sprintf("%d", intValue) // Convert to string

	fmt.Println("\n=== Type Conversion (Explicit Only!) ===")
	fmt.Printf("Int: %d, Float: %f, String: '%s'\n", intValue, floatValue, stringValue)

	// This would cause compile error (no automatic type coercion):
	// var result = intValue + floatValue  // ❌ Error: mismatched types

	// Correct way:
	var result = float64(intValue) + floatValue
	fmt.Printf("Result: %f\n", result)

	// Multiple variable declaration
	var (
		username = "alice"
		password = "secret"
		attempts = 3
	)

	fmt.Println("\n=== Multiple Declarations ===")
	fmt.Printf("Username: %s, Password: %s, Attempts: %d\n", username, password, attempts)

	// Multiple assignment
	x, y := 10, 20
	fmt.Printf("\nMultiple assignment: x=%d, y=%d\n", x, y)

	// Swap values (no temp variable needed)
	x, y = y, x
	fmt.Printf("After swap: x=%d, y=%d\n", x, y)
}

/*
OUTPUT:
=== Variables in Go vs PHP ===

Name: Alice (type: string)
Age: 30 (type: int)
Price: 19.99 (type: float64)
Active: true (type: bool)

City: New York
Population: 8000000

Country: USA
Founded: 1776

Pi: 3.141590
App: MyApp

=== Zero Values (not null!) ===
Empty string: '' (length: 0)
Zero int: 0
Zero float: 0.000000
Zero bool: false

=== Type Conversion (Explicit Only!) ===
Int: 42, Float: 42.000000, String: '42'
Result: 84.000000

=== Multiple Declarations ===
Username: alice, Password: secret, Attempts: 3

Multiple assignment: x=10, y=20
After swap: x=20, y=10
*/

/*
KEY DIFFERENCES FROM PHP:

1. STATIC TYPING:
   PHP: $x = 42; $x = "hello";  // ✅ Works
   Go:  x := 42; x = "hello"     // ❌ Compile error

2. NO NULL FOR BASIC TYPES:
   PHP: $x = null;                    // ✅ OK
   Go:  var x string  // x is "", not null
        var x *string // pointer can be nil

3. NO AUTOMATIC TYPE CONVERSION:
   PHP: $result = 5 + "10";          // "15" (automatic)
   Go:  result := 5 + int("10")      // Must convert explicitly

4. ZERO VALUES:
   PHP: Uninitialized variables trigger warnings
   Go:  All variables have zero values (safe defaults)

5. DECLARATION SYNTAX:
   PHP: $name = "value";
   Go:  name := "value"  // or var name = "value"

6. TYPE COMES AFTER NAME:
   PHP: string $name
   Go:  name string

COMMON TYPES IN GO:

Basic Types:
- bool
- string
- int, int8, int16, int32, int64
- uint, uint8, uint16, uint32, uint64
- byte (alias for uint8)
- rune (alias for int32, represents a Unicode code point)
- float32, float64
- complex64, complex128

BEST PRACTICES:

1. Use := for local variables (inside functions)
2. Use var for package-level variables
3. Use const for constants
4. Always convert types explicitly
5. Check zero values - they're not null!

NEXT STEPS:
- Run: go run 02-variables-and-types.go
- Try changing types and see compiler errors
- Experiment with type conversions
*/
