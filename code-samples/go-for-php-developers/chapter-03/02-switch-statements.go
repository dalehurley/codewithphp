package main

import (
	"fmt"
	"runtime"
	"time"
)

/*
Switch Statements in Go for PHP Developers
===========================================

PHP switch:
	switch ($var) {
	    case 1:
	        echo "One";
	        break;  // Must break!
	    case 2:
	        echo "Two";
	        break;
	    default:
	        echo "Other";
	}

Go switch (better!):
	switch var {
	case 1:
		fmt.Println("One")  // Auto-breaks!
	case 2:
		fmt.Println("Two")
	default:
		fmt.Println("Other")
	}

Key differences:
- No break needed (automatic)
- Multiple values per case
- Switch on types
- Switch without expression
- Fallthrough keyword available
*/

func main() {
	fmt.Println("=== Switch Statements for PHP Developers ===\n")

	demonstrateBasicSwitch()
	demonstrateMultipleValues()
	demonstrateSwitchWithoutExpression()
	demonstrateFallthrough()
	demonstrateTypeSwitch()
	demonstrateInterfaceSwitch()
	demonstrateSwitchWithInit()
	demonstratePracticalExamples()
}

// demonstrateBasicSwitch shows basic switch usage
func demonstrateBasicSwitch() {
	fmt.Println("--- Basic Switch ---")

	// PHP: switch with breaks
	// Go: No breaks needed!

	day := 3

	switch day {
	case 1:
		fmt.Println("Monday")
	case 2:
		fmt.Println("Tuesday")
	case 3:
		fmt.Println("Wednesday")
	case 4:
		fmt.Println("Thursday")
	case 5:
		fmt.Println("Friday")
	case 6:
		fmt.Println("Saturday")
	case 7:
		fmt.Println("Sunday")
	default:
		fmt.Println("Invalid day")
	}

	// String switch
	status := "active"

	switch status {
	case "active":
		fmt.Println("User is active")
	case "inactive":
		fmt.Println("User is inactive")
	case "pending":
		fmt.Println("User is pending")
	case "suspended":
		fmt.Println("User is suspended")
	default:
		fmt.Println("Unknown status")
	}

	// No default needed
	role := "guest"

	switch role {
	case "admin":
		fmt.Println("Full access")
	case "moderator":
		fmt.Println("Limited admin access")
	case "user":
		fmt.Println("Standard access")
	// No default - just falls through
	}

	fmt.Println()
}

// demonstrateMultipleValues shows multiple values per case
func demonstrateMultipleValues() {
	fmt.Println("--- Multiple Values Per Case ---")

	// PHP: Would need multiple case statements
	// case 1:
	// case 2:
	// case 3:
	//     echo "Quarter 1";
	//     break;

	// Go: Cleaner with comma-separated values
	month := 4

	switch month {
	case 1, 2, 3:
		fmt.Println("Quarter 1")
	case 4, 5, 6:
		fmt.Println("Quarter 2")
	case 7, 8, 9:
		fmt.Println("Quarter 3")
	case 10, 11, 12:
		fmt.Println("Quarter 4")
	default:
		fmt.Println("Invalid month")
	}

	// Workday vs weekend
	dayName := "Saturday"

	switch dayName {
	case "Monday", "Tuesday", "Wednesday", "Thursday", "Friday":
		fmt.Println("Workday")
	case "Saturday", "Sunday":
		fmt.Println("Weekend!")
	default:
		fmt.Println("Invalid day")
	}

	// HTTP status codes
	statusCode := 404

	switch statusCode {
	case 200, 201, 202, 204:
		fmt.Println("Success")
	case 301, 302, 307, 308:
		fmt.Println("Redirect")
	case 400, 401, 403, 404:
		fmt.Println("Client error")
	case 500, 502, 503, 504:
		fmt.Println("Server error")
	default:
		fmt.Printf("Unknown status: %d\n", statusCode)
	}

	fmt.Println()
}

// demonstrateSwitchWithoutExpression shows expression-less switch
func demonstrateSwitchWithoutExpression() {
	fmt.Println("--- Switch Without Expression ---")

	// PHP equivalent would be if-elseif chain
	// Go: Can use switch without expression (cleaner than if-else chain)

	age := 25
	income := 50000

	switch {
	case age < 18:
		fmt.Println("Minor")
	case age >= 18 && age < 65:
		fmt.Println("Adult")
	case age >= 65:
		fmt.Println("Senior")
	}

	// Multiple conditions
	switch {
	case income < 25000:
		fmt.Println("Low income")
	case income >= 25000 && income < 75000:
		fmt.Println("Middle income")
	case income >= 75000:
		fmt.Println("High income")
	}

	// Complex conditions
	score := 85
	attendance := 95

	switch {
	case score >= 90 && attendance >= 90:
		fmt.Println("Grade: A+ (Excellent)")
	case score >= 80 && attendance >= 80:
		fmt.Println("Grade: A (Good)")
	case score >= 70:
		fmt.Println("Grade: B (Satisfactory)")
	case score >= 60:
		fmt.Println("Grade: C (Pass)")
	default:
		fmt.Println("Grade: F (Fail)")
	}

	// Replacing long if-else chains
	temperature := 25

	switch {
	case temperature < 0:
		fmt.Println("Freezing")
	case temperature < 10:
		fmt.Println("Cold")
	case temperature < 20:
		fmt.Println("Cool")
	case temperature < 30:
		fmt.Println("Warm")
	default:
		fmt.Println("Hot")
	}

	fmt.Println()
}

// demonstrateFallthrough shows fallthrough keyword
func demonstrateFallthrough() {
	fmt.Println("--- Fallthrough ---")

	// PHP: Cases fall through by default (need break)
	// Go: Cases don't fall through (need fallthrough keyword)

	num := 1

	fmt.Println("With fallthrough:")
	switch num {
	case 1:
		fmt.Println("Case 1")
		fallthrough  // Continue to next case
	case 2:
		fmt.Println("Case 2")
		fallthrough
	case 3:
		fmt.Println("Case 3")
	default:
		fmt.Println("Default")
	}

	// Practical example: logging severity
	severity := "warning"

	fmt.Println("\nLogging with fallthrough:")
	switch severity {
	case "critical":
		fmt.Println("→ Alert team")
		fallthrough
	case "error":
		fmt.Println("→ Send email")
		fallthrough
	case "warning":
		fmt.Println("→ Log to file")
		fallthrough
	case "info":
		fmt.Println("→ Log to console")
	}

	// Note: fallthrough must be last statement in case
	// Note: fallthrough ignores next case condition

	fmt.Println()
}

// demonstrateTypeSwitch shows switching on types
func demonstrateTypeSwitch() {
	fmt.Println("--- Type Switch ---")

	// PHP: Would use gettype() or instanceof
	// Go: Type switch for interface types

	var x interface{} = "hello"

	switch v := x.(type) {
	case int:
		fmt.Printf("Integer: %d\n", v)
	case string:
		fmt.Printf("String: %s (length: %d)\n", v, len(v))
	case bool:
		fmt.Printf("Boolean: %v\n", v)
	case float64:
		fmt.Printf("Float: %.2f\n", v)
	default:
		fmt.Printf("Unknown type: %T\n", v)
	}

	// Multiple types
	testTypeSwitch(42)
	testTypeSwitch("Go")
	testTypeSwitch(3.14)
	testTypeSwitch(true)
	testTypeSwitch([]int{1, 2, 3})
	testTypeSwitch(nil)

	fmt.Println()
}

// testTypeSwitch demonstrates type switching
func testTypeSwitch(x interface{}) {
	switch v := x.(type) {
	case nil:
		fmt.Println("nil value")
	case int, int8, int16, int32, int64:
		fmt.Printf("Integer type: %d\n", v)
	case uint, uint8, uint16, uint32, uint64:
		fmt.Printf("Unsigned integer type: %d\n", v)
	case float32, float64:
		fmt.Printf("Float type: %v\n", v)
	case string:
		fmt.Printf("String type: %q\n", v)
	case bool:
		fmt.Printf("Boolean type: %v\n", v)
	case []int:
		fmt.Printf("Slice of ints: %v\n", v)
	default:
		fmt.Printf("Unknown type: %T\n", v)
	}
}

// Shape interface for demonstration
type Shape interface {
	Area() float64
}

// Circle implements Shape
type Circle struct {
	Radius float64
}

func (c Circle) Area() float64 {
	return 3.14159 * c.Radius * c.Radius
}

// Rectangle implements Shape
type Rectangle struct {
	Width, Height float64
}

func (r Rectangle) Area() float64 {
	return r.Width * r.Height
}

// Triangle implements Shape
type Triangle struct {
	Base, Height float64
}

func (t Triangle) Area() float64 {
	return 0.5 * t.Base * t.Height
}

// demonstrateInterfaceSwitch shows switching on interface implementations
func demonstrateInterfaceSwitch() {
	fmt.Println("--- Interface Type Switch ---")

	shapes := []Shape{
		Circle{Radius: 5},
		Rectangle{Width: 4, Height: 6},
		Triangle{Base: 3, Height: 4},
	}

	for i, shape := range shapes {
		fmt.Printf("Shape %d: ", i+1)

		switch s := shape.(type) {
		case Circle:
			fmt.Printf("Circle with radius %.2f, area = %.2f\n", s.Radius, s.Area())
		case Rectangle:
			fmt.Printf("Rectangle %.2f×%.2f, area = %.2f\n", s.Width, s.Height, s.Area())
		case Triangle:
			fmt.Printf("Triangle base=%.2f height=%.2f, area = %.2f\n", s.Base, s.Height, s.Area())
		default:
			fmt.Printf("Unknown shape, area = %.2f\n", s.Area())
		}
	}

	fmt.Println()
}

// demonstrateSwitchWithInit shows switch with initialization
func demonstrateSwitchWithInit() {
	fmt.Println("--- Switch with Initialization ---")

	// Similar to if with init, can initialize in switch

	// Time-based switching
	switch hour := time.Now().Hour(); {
	case hour < 12:
		fmt.Println("Good morning!")
	case hour < 17:
		fmt.Println("Good afternoon!")
	default:
		fmt.Println("Good evening!")
	}

	// With expression
	switch os := runtime.GOOS; os {
	case "darwin":
		fmt.Println("Running on macOS")
	case "linux":
		fmt.Println("Running on Linux")
	case "windows":
		fmt.Println("Running on Windows")
	default:
		fmt.Printf("Running on %s\n", os)
	}

	// Function call result
	switch result := calculate(10, 5); {
	case result < 0:
		fmt.Println("Negative result")
	case result == 0:
		fmt.Println("Zero result")
	case result > 0:
		fmt.Println("Positive result")
	}

	fmt.Println()
}

// Helper function
func calculate(a, b int) int {
	return a - b
}

// demonstratePracticalExamples shows real-world switch usage
func demonstratePracticalExamples() {
	fmt.Println("--- Practical Examples ---")

	// Example 1: HTTP handler routing
	path := "/api/users"
	method := "GET"

	fmt.Println("HTTP Router:")
	switch {
	case path == "/api/users" && method == "GET":
		fmt.Println("→ List users")
	case path == "/api/users" && method == "POST":
		fmt.Println("→ Create user")
	case path == "/api/users" && method == "PUT":
		fmt.Println("→ Update user")
	case path == "/api/users" && method == "DELETE":
		fmt.Println("→ Delete user")
	default:
		fmt.Println("→ 404 Not Found")
	}

	// Example 2: State machine
	state := "running"

	fmt.Println("\nState machine:")
	switch state {
	case "idle":
		fmt.Println("System is idle")
		// nextState = "starting"
	case "starting":
		fmt.Println("System is starting...")
		// nextState = "running"
	case "running":
		fmt.Println("System is running")
		// nextState = "stopping" or "paused"
	case "paused":
		fmt.Println("System is paused")
		// nextState = "running" or "stopping"
	case "stopping":
		fmt.Println("System is stopping...")
		// nextState = "stopped"
	case "stopped":
		fmt.Println("System is stopped")
		// nextState = "starting"
	default:
		fmt.Println("Unknown state")
	}

	// Example 3: Error handling
	err := performOperation()

	fmt.Println("\nError handling:")
	switch {
	case err == nil:
		fmt.Println("Success")
	case err.Error() == "not found":
		fmt.Println("Resource not found")
	case err.Error() == "permission denied":
		fmt.Println("Access denied")
	default:
		fmt.Printf("Error: %v\n", err)
	}

	// Example 4: Content type detection
	filename := "document.pdf"

	fmt.Println("\nContent type detection:")
	switch {
	case len(filename) > 4 && filename[len(filename)-4:] == ".pdf":
		fmt.Println("PDF document")
	case len(filename) > 4 && filename[len(filename)-4:] == ".jpg":
		fmt.Println("JPEG image")
	case len(filename) > 4 && filename[len(filename)-4:] == ".png":
		fmt.Println("PNG image")
	case len(filename) > 5 && filename[len(filename)-5:] == ".html":
		fmt.Println("HTML document")
	default:
		fmt.Println("Unknown file type")
	}

	// Example 5: Configuration selection
	env := "production"

	fmt.Println("\nEnvironment config:")
	switch env {
	case "development", "dev":
		fmt.Println("Debug: ON, Cache: OFF, Logging: VERBOSE")
	case "staging", "stage":
		fmt.Println("Debug: ON, Cache: ON, Logging: NORMAL")
	case "production", "prod":
		fmt.Println("Debug: OFF, Cache: ON, Logging: ERROR")
	default:
		fmt.Println("Unknown environment, using defaults")
	}

	// Example 6: Rate limiting
	requestCount := 150

	fmt.Println("\nRate limiting:")
	switch {
	case requestCount < 100:
		fmt.Println("Normal rate")
	case requestCount < 1000:
		fmt.Println("High rate - warning")
	case requestCount < 10000:
		fmt.Println("Very high rate - throttling")
	default:
		fmt.Println("Extreme rate - blocking")
	}

	// Example 7: Version comparison
	version := "2.1.0"

	fmt.Println("\nVersion handling:")
	switch version {
	case "1.0.0", "1.0.1", "1.0.2":
		fmt.Println("Legacy version - upgrade recommended")
	case "2.0.0":
		fmt.Println("Current stable version")
	case "2.1.0":
		fmt.Println("Latest version")
	default:
		fmt.Println("Unknown version")
	}

	// Example 8: Day of week scheduling
	today := time.Now().Weekday()

	fmt.Println("\nSchedule:")
	switch today {
	case time.Monday:
		fmt.Println("Team meeting at 10 AM")
	case time.Wednesday:
		fmt.Println("Code review at 2 PM")
	case time.Friday:
		fmt.Println("Sprint retrospective at 4 PM")
	case time.Saturday, time.Sunday:
		fmt.Println("Weekend - no meetings!")
	default:
		fmt.Println("Regular work day")
	}

	fmt.Println()
}

// Helper function for error handling example
func performOperation() error {
	// Simulating success
	return nil
}

/*
Expected Output:
=== Switch Statements for PHP Developers ===

--- Basic Switch ---
Wednesday
User is active

--- Multiple Values Per Case ---
Quarter 2
Weekend!
Client error

--- Switch Without Expression ---
Adult
Middle income
Grade: A (Good)
Warm

--- Fallthrough ---
With fallthrough:
Case 1
Case 2
Case 3

Logging with fallthrough:
→ Log to file
→ Log to console

--- Type Switch ---
String: hello (length: 5)
Integer type: 42
String type: "Go"
Float type: 3.14
Boolean type: true
Slice of ints: [1 2 3]
nil value

--- Interface Type Switch ---
Shape 1: Circle with radius 5.00, area = 78.54
Shape 2: Rectangle 4.00×6.00, area = 24.00
Shape 3: Triangle base=3.00 height=4.00, area = 6.00

--- Switch with Initialization ---
Good morning! (or afternoon/evening depending on when run)
Running on Linux (or macOS/Windows depending on OS)
Positive result

--- Practical Examples ---
HTTP Router:
→ List users

State machine:
System is running

Error handling:
Success

Content type detection:
PDF document

Environment config:
Debug: OFF, Cache: ON, Logging: ERROR

Rate limiting:
High rate - warning

Version handling:
Latest version

Schedule:
Regular work day (varies by day of week)
*/
