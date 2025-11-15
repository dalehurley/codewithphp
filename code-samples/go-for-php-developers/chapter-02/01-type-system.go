package main

import (
	"fmt"
	"math"
	"reflect"
	"strconv"
	"unsafe"
)

/*
Go Type System for PHP Developers
==================================

PHP is dynamically typed - variables can change types at runtime:
	$x = 42;        // int
	$x = "hello";   // now string
	$x = [1, 2, 3]; // now array

Go is statically typed - types are checked at compile time:
	var x int = 42
	x = "hello"  // COMPILE ERROR!

This chapter demonstrates Go's type system and how it differs from PHP.
*/

func main() {
	fmt.Println("=== Go Type System for PHP Developers ===\n")

	demonstrateBasicTypes()
	demonstrateTypeInference()
	demonstrateZeroValues()
	demonstrateTypeConversions()
	demonstrateTypeAssertions()
	demonstrateTypeSafety()
	demonstrateCustomTypes()
	demonstrateTypeAliases()
}

// demonstrateBasicTypes shows Go's basic types vs PHP
func demonstrateBasicTypes() {
	fmt.Println("--- Basic Types ---")

	// PHP: $bool = true; (mixed type)
	// Go: Must specify type explicitly or let compiler infer
	var isActive bool = true
	fmt.Printf("bool: %v (type: %T)\n", isActive, isActive)

	// Integer types - PHP only has one "int" type
	// Go has multiple integer types with different sizes
	var age int = 42              // Platform dependent (32 or 64 bit)
	var count int8 = 127          // -128 to 127
	var population int16 = 32000  // -32768 to 32767
	var bigNumber int32 = 2147483647
	var hugeNumber int64 = 9223372036854775807

	fmt.Printf("int: %d (type: %T, size: %d bytes)\n", age, age, unsafe.Sizeof(age))
	fmt.Printf("int8: %d (type: %T, size: %d bytes)\n", count, count, unsafe.Sizeof(count))
	fmt.Printf("int16: %d (type: %T, size: %d bytes)\n", population, population, unsafe.Sizeof(population))
	fmt.Printf("int32: %d (type: %T, size: %d bytes)\n", bigNumber, bigNumber, unsafe.Sizeof(bigNumber))
	fmt.Printf("int64: %d (type: %T, size: %d bytes)\n", hugeNumber, hugeNumber, unsafe.Sizeof(hugeNumber))

	// Unsigned integers - PHP doesn't distinguish signed/unsigned
	var positiveOnly uint = 42
	var smallPositive uint8 = 255  // 0 to 255
	var byte1 byte = 255           // byte is alias for uint8

	fmt.Printf("uint: %d (type: %T)\n", positiveOnly, positiveOnly)
	fmt.Printf("uint8: %d (type: %T)\n", smallPositive, smallPositive)
	fmt.Printf("byte: %d (type: %T)\n", byte1, byte1)

	// Floating point - PHP has only "float"
	// Go distinguishes float32 and float64
	var price32 float32 = 19.99
	var price64 float64 = 19.99

	fmt.Printf("float32: %.2f (type: %T, size: %d bytes)\n", price32, price32, unsafe.Sizeof(price32))
	fmt.Printf("float64: %.2f (type: %T, size: %d bytes)\n", price64, price64, unsafe.Sizeof(price64))

	// Complex numbers - PHP doesn't have native complex numbers
	var complex1 complex64 = 1 + 2i
	var complex2 complex128 = 3 + 4i

	fmt.Printf("complex64: %v (type: %T)\n", complex1, complex1)
	fmt.Printf("complex128: %v (type: %T)\n", complex2, complex2)

	// String - similar to PHP but immutable in Go
	var name string = "John Doe"
	fmt.Printf("string: %s (type: %T, length: %d)\n", name, name, len(name))

	// Rune - represents a Unicode code point (int32 alias)
	// PHP doesn't have a separate rune type
	var char rune = 'A'
	var emoji rune = '😀'

	fmt.Printf("rune: %c (type: %T, value: %d)\n", char, char, char)
	fmt.Printf("rune (emoji): %c (type: %T, value: %d)\n", emoji, emoji, emoji)

	fmt.Println()
}

// demonstrateTypeInference shows Go's type inference vs PHP
func demonstrateTypeInference() {
	fmt.Println("--- Type Inference ---")

	// PHP: Type is inferred from value and can change
	// $x = 42;        // int
	// $x = "hello";   // now string (allowed!)

	// Go: Type is inferred once and cannot change
	x := 42          // Type inferred as int
	// x = "hello"   // COMPILE ERROR! x is int, can't assign string

	y := 3.14        // Type inferred as float64
	z := "hello"     // Type inferred as string
	a := true        // Type inferred as bool
	b := 'A'         // Type inferred as rune (int32)

	fmt.Printf("x := 42 -> type: %T\n", x)
	fmt.Printf("y := 3.14 -> type: %T\n", y)
	fmt.Printf("z := \"hello\" -> type: %T\n", z)
	fmt.Printf("a := true -> type: %T\n", a)
	fmt.Printf("b := 'A' -> type: %T\n", b)

	// Short declaration only works inside functions
	// Outside functions, use var

	fmt.Println()
}

// demonstrateZeroValues shows Go's zero values vs PHP's null
func demonstrateZeroValues() {
	fmt.Println("--- Zero Values ---")

	// PHP: Uninitialized variables are null (with notice)
	// Go: Every type has a "zero value" - no null/nil for basic types

	var i int           // 0
	var f float64       // 0.0
	var b bool          // false
	var s string        // "" (empty string)

	fmt.Printf("Zero value of int: %d\n", i)
	fmt.Printf("Zero value of float64: %f\n", f)
	fmt.Printf("Zero value of bool: %v\n", b)
	fmt.Printf("Zero value of string: %q\n", s)

	// This is safer than PHP's null because:
	// 1. No "undefined variable" errors
	// 2. Predictable behavior
	// 3. No need for isset() or ?? operators for basic types

	fmt.Println()
}

// demonstrateTypeConversions shows explicit type conversion
func demonstrateTypeConversions() {
	fmt.Println("--- Type Conversions ---")

	// PHP: Automatic type coercion (often surprising)
	// echo 1 + "2";      // 3 (string "2" coerced to int)
	// echo "5" + "3";    // 8 (both strings coerced to int)
	// echo "10" + 2.5;   // 12.5 (string coerced to float)

	// Go: NO automatic type conversion - must be explicit
	var i int = 42
	var f float64

	// f = i  // COMPILE ERROR! Cannot assign int to float64
	f = float64(i)  // Explicit conversion required

	fmt.Printf("int to float64: %d -> %f\n", i, f)

	// Converting float to int truncates (doesn't round)
	var pi float64 = 3.14159
	var rounded int = int(pi)  // Truncates to 3

	fmt.Printf("float64 to int: %f -> %d (truncated, not rounded)\n", pi, rounded)

	// Proper rounding requires math package
	var properRound int = int(math.Round(pi))
	fmt.Printf("float64 to int (rounded): %f -> %d\n", pi, properRound)

	// String conversions - very different from PHP!
	var num int = 65

	// In PHP: $str = (string)65;  // "65"
	// In Go: string(65) gives "A" (ASCII character!)
	var char string = string(num)  // "A" (ASCII 65)
	fmt.Printf("string(65) = %q (NOT \"65\"!)\n", char)

	// To convert number to string, use strconv package
	var numStr string = strconv.Itoa(num)  // "65"
	fmt.Printf("strconv.Itoa(65) = %q\n", numStr)

	// String to int
	str := "123"
	converted, err := strconv.Atoi(str)
	if err != nil {
		fmt.Printf("Error converting: %v\n", err)
	} else {
		fmt.Printf("strconv.Atoi(\"123\") = %d\n", converted)
	}

	// ParseInt for more control
	parsed, err := strconv.ParseInt("FF", 16, 64)  // Base 16 (hex)
	if err == nil {
		fmt.Printf("strconv.ParseInt(\"FF\", 16, 64) = %d\n", parsed)
	}

	// ParseFloat
	floatVal, err := strconv.ParseFloat("3.14", 64)
	if err == nil {
		fmt.Printf("strconv.ParseFloat(\"3.14\", 64) = %f\n", floatVal)
	}

	// Bool conversions
	boolVal, err := strconv.ParseBool("true")
	if err == nil {
		fmt.Printf("strconv.ParseBool(\"true\") = %v\n", boolVal)
	}

	fmt.Println()
}

// demonstrateTypeAssertions shows working with interface{} (any)
func demonstrateTypeAssertions() {
	fmt.Println("--- Type Assertions (interface{} / any) ---")

	// PHP: Variables can hold any type
	// $var = 42;
	// $var = "hello";  // OK

	// Go: Use interface{} or 'any' (Go 1.18+) for dynamic typing
	var dynamic any  // any is alias for interface{}

	dynamic = 42
	fmt.Printf("dynamic = 42 -> type: %T, value: %v\n", dynamic, dynamic)

	dynamic = "hello"
	fmt.Printf("dynamic = \"hello\" -> type: %T, value: %v\n", dynamic, dynamic)

	dynamic = true
	fmt.Printf("dynamic = true -> type: %T, value: %v\n", dynamic, dynamic)

	// To use the concrete value, need type assertion
	dynamic = 100

	// Safe type assertion with comma-ok idiom
	if num, ok := dynamic.(int); ok {
		fmt.Printf("Type assertion succeeded: %d\n", num)
		fmt.Printf("Can now do math: %d + 10 = %d\n", num, num+10)
	} else {
		fmt.Println("Type assertion failed")
	}

	// Unsafe type assertion (panics if wrong type)
	// num := dynamic.(int)  // OK if dynamic is int

	// Type switch - like PHP's gettype() but more powerful
	dynamic = 3.14
	describeType(dynamic)

	dynamic = "Go language"
	describeType(dynamic)

	dynamic = []int{1, 2, 3}
	describeType(dynamic)

	fmt.Println()
}

// describeType uses type switch to identify type
func describeType(x interface{}) {
	// PHP equivalent:
	// switch (gettype($x)) {
	//     case 'integer': ...
	//     case 'string': ...
	// }

	switch v := x.(type) {
	case int:
		fmt.Printf("Integer: %d\n", v)
	case float64:
		fmt.Printf("Float: %.2f\n", v)
	case string:
		fmt.Printf("String: %q (length: %d)\n", v, len(v))
	case bool:
		fmt.Printf("Boolean: %v\n", v)
	case []int:
		fmt.Printf("Slice of ints: %v\n", v)
	default:
		fmt.Printf("Unknown type: %T\n", v)
	}
}

// demonstrateTypeSafety shows advantages of static typing
func demonstrateTypeSafety() {
	fmt.Println("--- Type Safety Benefits ---")

	// PHP: These errors only appear at runtime
	// $result = "10" + "20";  // Works (gives 30) but might not be intended
	// $result = "abc" + 5;    // Warning at runtime
	// $result = someFunction("wrong", "param", "types");  // Runtime error

	// Go: Type errors caught at compile time
	var a int = 10
	var b int = 20
	var result int = a + b

	fmt.Printf("Type-safe addition: %d + %d = %d\n", a, b, result)

	// The following would NOT compile:
	// var str string = "10"
	// result = str + a  // COMPILE ERROR!

	// Benefits:
	// 1. Errors caught before runtime
	// 2. Better IDE autocomplete
	// 3. Easier refactoring
	// 4. Self-documenting code
	// 5. Better performance (no runtime type checking)

	fmt.Println("✓ All type checks passed at compile time!")
	fmt.Println()
}

// demonstrateCustomTypes shows creating custom types
func demonstrateCustomTypes() {
	fmt.Println("--- Custom Types ---")

	// PHP: No separate type definition needed
	// $userId = 123;
	// $productId = 456;
	// processUser($productId);  // Oops! Wrong ID type, but allowed

	// Go: Define custom types for type safety
	var user UserID = 123
	var product ProductID = 456

	fmt.Printf("UserID: %d (type: %T)\n", user, user)
	fmt.Printf("ProductID: %d (type: %T)\n", product, product)

	// Cannot mix custom types even if underlying type is same!
	// processUser(product)  // COMPILE ERROR! ProductID is not UserID
	processUser(user)  // OK

	// Must explicitly convert
	processUser(UserID(product))  // OK - explicit conversion

	// Custom types can have methods
	var temp Temperature = 98.6
	fmt.Printf("Temperature: %.1f°F = %.1f°C\n", temp, temp.ToCelsius())

	fmt.Println()
}

// Custom type definitions
type UserID int
type ProductID int
type Temperature float64

// processUser demonstrates type safety with custom types
func processUser(id UserID) {
	fmt.Printf("Processing user with ID: %d\n", id)
}

// ToCelsius converts Fahrenheit to Celsius
func (t Temperature) ToCelsius() float64 {
	return (float64(t) - 32) * 5 / 9
}

// demonstrateTypeAliases shows type aliases vs custom types
func demonstrateTypeAliases() {
	fmt.Println("--- Type Aliases ---")

	// Type alias - just another name for existing type
	type MyInt = int  // Alias (note the =)

	// Custom type - creates new type
	type CustomInt int  // New type (no =)

	var regular int = 42
	var aliased MyInt = 42
	var custom CustomInt = 42

	fmt.Printf("regular int: %d (type: %T)\n", regular, regular)
	fmt.Printf("MyInt (alias): %d (type: %T)\n", aliased, aliased)
	fmt.Printf("CustomInt (new type): %d (type: %T)\n", custom, custom)

	// Alias can be used interchangeably
	regular = aliased  // OK - same type
	aliased = regular  // OK - same type

	// Custom type requires conversion
	// regular = custom  // COMPILE ERROR!
	regular = int(custom)  // OK - explicit conversion

	// Common aliases in Go standard library:
	// type byte = uint8
	// type rune = int32
	// type any = interface{}

	// Use reflection to see type identity
	fmt.Printf("\nType identity check:\n")
	fmt.Printf("regular == aliased? %v\n", reflect.TypeOf(regular) == reflect.TypeOf(aliased))
	fmt.Printf("regular == custom? %v\n", reflect.TypeOf(regular) == reflect.TypeOf(custom))

	fmt.Println()
}

/*
Expected Output:
=== Go Type System for PHP Developers ===

--- Basic Types ---
bool: true (type: bool)
int: 42 (type: int, size: 8 bytes)
int8: 127 (type: int8, size: 1 bytes)
int16: 32000 (type: int16, size: 2 bytes)
int32: 2147483647 (type: int32, size: 4 bytes)
int64: 9223372036854775807 (type: int64, size: 8 bytes)
uint: 42 (type: uint)
uint8: 255 (type: uint8)
byte: 255 (type: uint8)
float32: 19.99 (type: float32, size: 4 bytes)
float64: 19.99 (type: float64, size: 8 bytes)
complex64: (1+2i) (type: complex64)
complex128: (3+4i) (type: complex128)
string: John Doe (type: string, length: 8)
rune: A (type: int32, value: 65)
rune (emoji): 😀 (type: int32, value: 128512)

--- Type Inference ---
x := 42 -> type: int
y := 3.14 -> type: float64
z := "hello" -> type: string
a := true -> type: bool
b := 'A' -> type: int32

--- Zero Values ---
Zero value of int: 0
Zero value of float64: 0.000000
Zero value of bool: false
Zero value of string: ""

--- Type Conversions ---
int to float64: 42 -> 42.000000
float64 to int: 3.141590 -> 3 (truncated, not rounded)
float64 to int (rounded): 3.141590 -> 3
string(65) = "A" (NOT "65"!)
strconv.Itoa(65) = "65"
strconv.Atoi("123") = 123
strconv.ParseInt("FF", 16, 64) = 255
strconv.ParseFloat("3.14", 64) = 3.140000
strconv.ParseBool("true") = true

--- Type Assertions (interface{} / any) ---
dynamic = 42 -> type: int, value: 42
dynamic = "hello" -> type: string, value: hello
dynamic = true -> type: bool, value: true
Type assertion succeeded: 100
Can now do math: 100 + 10 = 110
Float: 3.14
String: "Go language" (length: 11)
Slice of ints: [1 2 3]

--- Type Safety Benefits ---
Type-safe addition: 10 + 20 = 30
✓ All type checks passed at compile time!

--- Custom Types ---
UserID: 123 (type: main.UserID)
ProductID: 456 (type: main.ProductID)
Processing user with ID: 123
Processing user with ID: 456
Temperature: 98.6°F = 37.0°C

--- Type Aliases ---
regular int: 42 (type: int)
MyInt (alias): 42 (type: int)
CustomInt (new type): 42 (type: main.CustomInt)

Type identity check:
regular == aliased? true
regular == custom? false
*/
