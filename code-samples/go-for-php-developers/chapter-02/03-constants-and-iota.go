package main

import (
	"fmt"
	"math"
	"time"
)

/*
Constants and Iota in Go for PHP Developers
============================================

PHP constants:
	define('PI', 3.14159);
	const MAX_SIZE = 100;

Go constants:
	const Pi = 3.14159
	const MaxSize = 100

Go has powerful compile-time constants with:
- Type inference
- Untyped constants
- iota for enumerated values
- Constant expressions evaluated at compile time
*/

func main() {
	fmt.Println("=== Constants and Iota for PHP Developers ===\n")

	demonstrateBasicConstants()
	demonstrateUntypedConstants()
	demonstrateConstantExpressions()
	demonstrateIotaBasics()
	demonstrateIotaExpressions()
	demonstrateIotaSkipping()
	demonstrateBitFlags()
	demonstrateTimeUnits()
	demonstrateEnumPatterns()
}

// Basic constant declarations
const (
	// Public constant (starts with uppercase)
	Pi = 3.14159

	// Private constant (starts with lowercase)
	maxRetries = 3

	// Typed constant
	ServerPort int = 8080

	// String constant
	AppName = "MyApp"

	// Boolean constant
	DebugMode = true
)

// demonstrateBasicConstants shows basic constant usage
func demonstrateBasicConstants() {
	fmt.Println("--- Basic Constants ---")

	// PHP: define('MAX_USERS', 100);
	// Go: const MaxUsers = 100

	const MaxUsers = 100
	const MinAge = 18
	const CompanyName = "Acme Corp"

	fmt.Printf("MaxUsers: %d\n", MaxUsers)
	fmt.Printf("MinAge: %d\n", MinAge)
	fmt.Printf("CompanyName: %s\n", CompanyName)

	// Constants must be assigned at compile time
	// const Now = time.Now()  // ERROR! Function call not allowed

	// But these are OK (compile-time values)
	const (
		Zero     = 0
		One      = 1
		Two      = 1 + 1
		Four     = Two * 2
		Username = "admin"
		Size     = len(Username)  // len() of string constant is OK
	)

	fmt.Printf("\nCompile-time calculations:\n")
	fmt.Printf("Four = %d\n", Four)
	fmt.Printf("Size = %d\n", Size)

	// Constants cannot be modified
	// MaxUsers = 200  // COMPILE ERROR!

	// Constants can shadow outer constants
	const MaxUsers = 50  // Different MaxUsers in this scope
	fmt.Printf("Shadowed MaxUsers: %d\n", MaxUsers)

	fmt.Println()
}

// demonstrateUntypedConstants shows untyped constant flexibility
func demonstrateUntypedConstants() {
	fmt.Println("--- Untyped Constants ---")

	// Untyped constants are more flexible than typed ones
	const UntypedNumber = 42
	const TypedNumber int = 42

	// Untyped constant can be used with any numeric type
	var i int = UntypedNumber
	var i8 int8 = UntypedNumber
	var i64 int64 = UntypedNumber
	var f32 float32 = UntypedNumber
	var f64 float64 = UntypedNumber
	var c64 complex64 = UntypedNumber

	fmt.Printf("Untyped constant works with all types:\n")
	fmt.Printf("  int: %d\n", i)
	fmt.Printf("  int8: %d\n", i8)
	fmt.Printf("  int64: %d\n", i64)
	fmt.Printf("  float32: %f\n", f32)
	fmt.Printf("  float64: %f\n", f64)
	fmt.Printf("  complex64: %v\n", c64)

	// Typed constant is restricted
	var ti int = TypedNumber
	// var tf float32 = TypedNumber  // ERROR! Cannot use int as float32
	var tf float32 = float32(TypedNumber)  // Must convert
	fmt.Printf("\nTyped constant requires conversion: %f\n", tf)

	// High precision untyped constants
	const (
		// These have arbitrary precision until assigned
		BigNumber = 1e100
		Precise   = 1.23456789012345678901234567890
	)

	fmt.Printf("\nHigh precision constants:\n")
	fmt.Printf("BigNumber: %e\n", BigNumber)
	fmt.Printf("Precise: %.30f\n", Precise)

	// Precision is maintained until assignment
	const Calc = Precise * 2 / 3
	fmt.Printf("Calc: %.30f\n", Calc)

	fmt.Println()
}

// demonstrateConstantExpressions shows compile-time expressions
func demonstrateConstantExpressions() {
	fmt.Println("--- Constant Expressions ---")

	// All arithmetic happens at compile time
	const (
		KB = 1024
		MB = 1024 * KB
		GB = 1024 * MB
		TB = 1024 * GB
	)

	fmt.Printf("Storage units:\n")
	fmt.Printf("1 KB = %d bytes\n", KB)
	fmt.Printf("1 MB = %d bytes\n", MB)
	fmt.Printf("1 GB = %d bytes\n", GB)
	fmt.Printf("1 TB = %d bytes\n", TB)

	// Mathematical constants
	const (
		Pi      = 3.14159265358979323846
		E       = 2.71828182845904523536
		Sqrt2   = 1.41421356237309504880
		Phi     = 1.61803398874989484820  // Golden ratio
		RadToDeg = 180 / Pi
		DegToRad = Pi / 180
	)

	fmt.Printf("\nMath constants:\n")
	fmt.Printf("Pi = %.20f\n", Pi)
	fmt.Printf("e = %.20f\n", E)
	fmt.Printf("√2 = %.20f\n", Sqrt2)
	fmt.Printf("φ = %.20f\n", Phi)

	// Using in calculations
	angle := 45.0
	radians := angle * DegToRad
	fmt.Printf("\n45° = %.4f radians\n", radians)

	fmt.Println()
}

// iota is a powerful feature for creating enumerated constants
const (
	Sunday = iota  // 0
	Monday         // 1
	Tuesday        // 2
	Wednesday      // 3
	Thursday       // 4
	Friday         // 5
	Saturday       // 6
)

// demonstrateIotaBasics shows basic iota usage
func demonstrateIotaBasics() {
	fmt.Println("--- Iota Basics ---")

	// PHP: No built-in enum (before PHP 8.1)
	// class DaysOfWeek {
	//     const SUNDAY = 0;
	//     const MONDAY = 1;
	//     const TUESDAY = 2;
	//     ...
	// }

	// Go: iota automatically increments
	fmt.Println("Days of week (using iota):")
	fmt.Printf("Sunday: %d\n", Sunday)
	fmt.Printf("Monday: %d\n", Monday)
	fmt.Printf("Tuesday: %d\n", Tuesday)
	fmt.Printf("Friday: %d\n", Friday)
	fmt.Printf("Saturday: %d\n", Saturday)

	// iota starts at 0 in each const block
	const (
		First  = iota  // 0
		Second         // 1
		Third          // 2
	)

	const (
		// New const block, iota resets to 0
		Red   = iota  // 0
		Green         // 1
		Blue          // 2
	)

	fmt.Printf("\nFirst const block: %d, %d, %d\n", First, Second, Third)
	fmt.Printf("Second const block: %d, %d, %d\n", Red, Green, Blue)

	// Can start from 1
	const (
		One = iota + 1  // 1
		Two             // 2
		Three           // 3
	)

	fmt.Printf("\nStarting from 1: %d, %d, %d\n", One, Two, Three)

	fmt.Println()
}

// demonstrateIotaExpressions shows iota in expressions
func demonstrateIotaExpressions() {
	fmt.Println("--- Iota Expressions ---")

	// Powers of 2 (bit flags)
	const (
		ReadPermission  = 1 << iota  // 1 << 0 = 1
		WritePermission              // 1 << 1 = 2
		ExecPermission               // 1 << 2 = 4
	)

	fmt.Println("File permissions (powers of 2):")
	fmt.Printf("Read: %d (binary: %08b)\n", ReadPermission, ReadPermission)
	fmt.Printf("Write: %d (binary: %08b)\n", WritePermission, WritePermission)
	fmt.Printf("Exec: %d (binary: %08b)\n", ExecPermission, ExecPermission)

	// Storage sizes
	const (
		_  = iota  // Skip 0
		KiB = 1 << (10 * iota)  // 1 << 10 = 1024
		MiB                      // 1 << 20 = 1048576
		GiB                      // 1 << 30 = 1073741824
		TiB                      // 1 << 40
	)

	fmt.Println("\nStorage units (using iota):")
	fmt.Printf("1 KiB = %d bytes\n", KiB)
	fmt.Printf("1 MiB = %d bytes\n", MiB)
	fmt.Printf("1 GiB = %d bytes\n", GiB)
	fmt.Printf("1 TiB = %d bytes\n", TiB)

	// Multiple values per line
	const (
		a, b = iota, iota + 10  // 0, 10
		c, d                    // 1, 11
		e, f                    // 2, 12
	)

	fmt.Printf("\nMultiple values: a=%d, b=%d, c=%d, d=%d, e=%d, f=%d\n", a, b, c, d, e, f)

	fmt.Println()
}

// demonstrateIotaSkipping shows skipping values
func demonstrateIotaSkipping() {
	fmt.Println("--- Skipping Values with Iota ---")

	// HTTP status codes
	const (
		StatusOK       = 200
		StatusCreated  = 201
		StatusAccepted = 202

		StatusBadRequest   = 400
		StatusUnauthorized = 401
		StatusForbidden    = 403
		StatusNotFound     = 404

		StatusServerError = 500
	)

	// Using iota with skip
	const (
		_ = iota  // Skip 0
		_ = iota  // Skip 1
		StatusProcessing = iota + 100  // 102
	)

	// Day type enumeration
	const (
		_         = iota  // Skip 0
		Monday2            // 1
		Tuesday2           // 2
		_                  // Skip 3 (no Wednesday)
		Thursday2          // 4
		Friday2            // 5
	)

	fmt.Printf("Skipped values:\n")
	fmt.Printf("Monday2: %d, Tuesday2: %d, Thursday2: %d, Friday2: %d\n",
		Monday2, Tuesday2, Thursday2, Friday2)

	fmt.Println()
}

// Bit flags for permissions
type Permission uint

const (
	PermNone Permission = 0
	PermRead Permission = 1 << iota
	PermWrite
	PermExecute
	PermDelete
	PermAll = PermRead | PermWrite | PermExecute | PermDelete
)

// demonstrateBitFlags shows using iota for bit flags
func demonstrateBitFlags() {
	fmt.Println("--- Bit Flags with Iota ---")

	// PHP: Use constants and bitwise operators
	// const PERM_READ = 1;
	// const PERM_WRITE = 2;
	// const PERM_EXEC = 4;
	// $perms = PERM_READ | PERM_WRITE;

	// Go: Similar but with type safety
	var userPerms Permission = PermRead | PermWrite
	var adminPerms Permission = PermAll

	fmt.Printf("User permissions: %d (binary: %04b)\n", userPerms, userPerms)
	fmt.Printf("Admin permissions: %d (binary: %04b)\n", adminPerms, adminPerms)

	// Check permissions
	if userPerms&PermRead != 0 {
		fmt.Println("User has read permission")
	}

	if userPerms&PermExecute != 0 {
		fmt.Println("User has execute permission")
	} else {
		fmt.Println("User does NOT have execute permission")
	}

	// Add permission
	userPerms |= PermExecute
	fmt.Printf("After adding execute: %d (binary: %04b)\n", userPerms, userPerms)

	// Remove permission
	userPerms &^= PermWrite  // AND NOT
	fmt.Printf("After removing write: %d (binary: %04b)\n", userPerms, userPerms)

	// Toggle permission
	userPerms ^= PermRead
	fmt.Printf("After toggling read: %d (binary: %04b)\n", userPerms, userPerms)

	fmt.Println()
}

// Time duration units (similar to time.Duration in stdlib)
type Duration int64

const (
	Nanosecond  Duration = 1
	Microsecond          = 1000 * Nanosecond
	Millisecond          = 1000 * Microsecond
	Second               = 1000 * Millisecond
	Minute               = 60 * Second
	Hour                 = 60 * Minute
	Day                  = 24 * Hour
)

// demonstrateTimeUnits shows practical use of constants
func demonstrateTimeUnits() {
	fmt.Println("--- Time Units (Constant Expressions) ---")

	// PHP: Would use separate variables or class constants
	// Go: Constants with type safety and clear relationships

	fmt.Printf("Time units in nanoseconds:\n")
	fmt.Printf("1 Microsecond = %d nanoseconds\n", Microsecond)
	fmt.Printf("1 Millisecond = %d nanoseconds\n", Millisecond)
	fmt.Printf("1 Second = %d nanoseconds\n", Second)
	fmt.Printf("1 Minute = %d nanoseconds\n", Minute)
	fmt.Printf("1 Hour = %d nanoseconds\n", Hour)
	fmt.Printf("1 Day = %d nanoseconds\n", Day)

	// Using actual time.Duration from standard library
	timeout := 30 * time.Second
	fmt.Printf("\nTimeout: %v\n", timeout)

	interval := 500 * time.Millisecond
	fmt.Printf("Interval: %v\n", interval)

	// Calculations
	totalTime := 2*time.Hour + 30*time.Minute + 15*time.Second
	fmt.Printf("Total time: %v\n", totalTime)
	fmt.Printf("Total in minutes: %.2f\n", totalTime.Minutes())
	fmt.Printf("Total in hours: %.2f\n", totalTime.Hours())

	fmt.Println()
}

// State represents application state
type State int

const (
	StateUnknown State = iota
	StateInitializing
	StateRunning
	StatePaused
	StateStopped
	StateError
)

// String method for State (makes it printable)
func (s State) String() string {
	names := []string{
		"Unknown",
		"Initializing",
		"Running",
		"Paused",
		"Stopped",
		"Error",
	}

	if s < 0 || int(s) >= len(names) {
		return fmt.Sprintf("State(%d)", s)
	}
	return names[s]
}

// IsValid checks if state is valid
func (s State) IsValid() bool {
	return s >= StateUnknown && s <= StateError
}

// CanTransitionTo checks if state transition is allowed
func (s State) CanTransitionTo(target State) bool {
	switch s {
	case StateInitializing:
		return target == StateRunning || target == StateError
	case StateRunning:
		return target == StatePaused || target == StateStopped || target == StateError
	case StatePaused:
		return target == StateRunning || target == StateStopped
	case StateStopped:
		return target == StateInitializing
	default:
		return false
	}
}

// demonstrateEnumPatterns shows enum-like patterns with iota
func demonstrateEnumPatterns() {
	fmt.Println("--- Enum Patterns ---")

	// PHP 8.1+: Has enum support
	// enum State {
	//     case Unknown;
	//     case Running;
	//     case Stopped;
	// }

	// Go: Use iota with custom type and methods

	var appState State = StateInitializing
	fmt.Printf("Initial state: %v (value: %d)\n", appState, appState)

	// Using String() method
	fmt.Printf("State name: %s\n", appState)

	// Check validity
	fmt.Printf("Is valid: %v\n", appState.IsValid())

	// State transitions
	states := []State{StateRunning, StatePaused, StateStopped, StateInitializing}

	for _, newState := range states {
		if appState.CanTransitionTo(newState) {
			fmt.Printf("✓ Can transition from %v to %v\n", appState, newState)
			appState = newState
		} else {
			fmt.Printf("✗ Cannot transition from %v to %v\n", appState, newState)
		}
	}

	// Type safety prevents invalid values
	// var invalid State = 999
	// But we can assign it:
	invalid := State(999)
	fmt.Printf("\nInvalid state: %v\n", invalid)
	fmt.Printf("Is valid: %v\n", invalid.IsValid())

	fmt.Println()
}

/*
Expected Output:
=== Constants and Iota for PHP Developers ===

--- Basic Constants ---
MaxUsers: 100
MinAge: 18
CompanyName: Acme Corp

Compile-time calculations:
Four = 4
Size = 5
Shadowed MaxUsers: 50

--- Untyped Constants ---
Untyped constant works with all types:
  int: 42
  int8: 42
  int64: 42
  float32: 42.000000
  float64: 42.000000
  complex64: (42+0i)

Typed constant requires conversion: 42.000000

High precision constants:
BigNumber: 1.000000e+100
Precise: 1.234567890123456789012345679
Calc: 0.822926260082304596155699063

--- Constant Expressions ---
Storage units:
1 KB = 1024 bytes
1 MB = 1048576 bytes
1 GB = 1073741824 bytes
1 TB = 1099511627776 bytes

Math constants:
Pi = 3.14159265358979311600
e = 2.71828182845904509080
√2 = 1.41421356237309514547
φ = 1.61803398874989490253

45° = 0.7854 radians

--- Iota Basics ---
Days of week (using iota):
Sunday: 0
Monday: 1
Tuesday: 2
Friday: 5
Saturday: 6

First const block: 0, 1, 2
Second const block: 0, 1, 2

Starting from 1: 1, 2, 3

--- Iota Expressions ---
File permissions (powers of 2):
Read: 1 (binary: 00000001)
Write: 2 (binary: 00000010)
Exec: 4 (binary: 00000100)

Storage units (using iota):
1 KiB = 1024 bytes
1 MiB = 1048576 bytes
1 GiB = 1073741824 bytes
1 TiB = 1099511627776 bytes

Multiple values: a=0, b=10, c=1, d=11, e=2, f=12

--- Skipping Values with Iota ---
Skipped values:
Monday2: 1, Tuesday2: 2, Thursday2: 4, Friday2: 5

--- Bit Flags with Iota ---
User permissions: 3 (binary: 0011)
Admin permissions: 15 (binary: 1111)
User has read permission
User does NOT have execute permission
After adding execute: 7 (binary: 0111)
After removing write: 5 (binary: 0101)
After toggling read: 4 (binary: 0100)

--- Time Units (Constant Expressions) ---
Time units in nanoseconds:
1 Microsecond = 1000 nanoseconds
1 Millisecond = 1000000 nanoseconds
1 Second = 1000000000 nanoseconds
1 Minute = 60000000000 nanoseconds
1 Hour = 3600000000000 nanoseconds
1 Day = 86400000000000 nanoseconds

Timeout: 30s
Interval: 500ms
Total time: 2h30m15s
Total in minutes: 150.25
Total in hours: 2.50

--- Enum Patterns ---
Initial state: Initializing (value: 1)
State name: Initializing
Is valid: true
✓ Can transition from Initializing to Running
✗ Cannot transition from Running to Paused
✗ Cannot transition from Running to Stopped
✗ Cannot transition from Running to Initializing

Invalid state: State(999)
Is valid: false
*/
