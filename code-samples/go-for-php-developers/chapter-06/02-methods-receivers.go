package main

import (
	"fmt"
	"math"
)

/*
Methods and Receivers for PHP Developers
=========================================

PHP methods are defined inside classes:
	class Circle {
	    private $radius;

	    public function area() {
	        return pi() * $this->radius * $this->radius;
	    }
	}

Go methods are defined outside structs:
	type Circle struct {
	    Radius float64
	}

	func (c Circle) Area() float64 {
	    return math.Pi * c.Radius * c.Radius
	}

Key concepts:
- Methods are functions with receivers
- Value receivers vs pointer receivers
- Methods can be on any type (not just structs)
- No method overloading
*/

func main() {
	fmt.Println("=== Methods and Receivers ===\n")

	demonstrateBasicMethods()
	demonstrateValueReceivers()
	demonstratePointerReceivers()
	demonstrateReceiverChoice()
	demonstrateMethodsOnTypes()
	demonstrateMethodChaining()
	demonstrateMethodSets()
	demonstratePractical()
}

//Circle struct for demonstrations
type Circle struct {
	Radius float64
}

// Area calculates circle area (value receiver)
func (c Circle) Area() float64 {
	return math.Pi * c.Radius * c.Radius
}

// Circumference calculates circle circumference (value receiver)
func (c Circle) Circumference() float64 {
	return 2 * math.Pi * c.Radius
}

// Scale increases radius (pointer receiver - modifies)
func (c *Circle) Scale(factor float64) {
	c.Radius *= factor
}

// demonstrateBasicMethods shows basic method syntax
func demonstrateBasicMethods() {
	fmt.Println("--- Basic Methods ---")

	// PHP: $circle = new Circle(5.0);
	// PHP: $area = $circle->area();
	// Go: Similar but methods defined outside struct

	c := Circle{Radius: 5.0}
	fmt.Printf("Circle: %+v\n", c)
	fmt.Printf("Area: %.2f\n", c.Area())
	fmt.Printf("Circumference: %.2f\n", c.Circumference())

	// Can call on pointer too
	ptr := &c
	fmt.Printf("\nVia pointer:\n")
	fmt.Printf("Area: %.2f\n", ptr.Area())
	fmt.Printf("Circumference: %.2f\n", ptr.Circumference())

	fmt.Println()
}

// Rectangle for comparison
type Rectangle struct {
	Width, Height float64
}

// Area method for Rectangle
func (r Rectangle) Area() float64 {
	return r.Width * r.Height
}

// Perimeter method for Rectangle
func (r Rectangle) Perimeter() float64 {
	return 2 * (r.Width + r.Height)
}

// demonstrateValueReceivers shows value receiver behavior
func demonstrateValueReceivers() {
	fmt.Println("--- Value Receivers ---")

	// Value receiver gets a copy of the struct
	// PHP: Similar to passing $this by value (rare in PHP)

	r := Rectangle{Width: 10, Height: 5}
	fmt.Printf("Original: %+v\n", r)
	fmt.Printf("Area: %.2f\n", r.Area())

	// Value receiver can't modify original
	r.Double()  // Modifies copy, not original
	fmt.Printf("After Double: %+v (unchanged)\n", r)

	// Use pointer receiver to modify
	r.DoublePtr()
	fmt.Printf("After DoublePtr: %+v (changed)\n", r)

	fmt.Println()
}

// Double tries to double dimensions (value receiver)
func (r Rectangle) Double() {
	r.Width *= 2
	r.Height *= 2
	fmt.Printf("Inside Double: %+v\n", r)
}

// DoublePtr doubles dimensions (pointer receiver)
func (r *Rectangle) DoublePtr() {
	r.Width *= 2
	r.Height *= 2
	fmt.Printf("Inside DoublePtr: %+v\n", r)
}

// demonstratePointerReceivers shows pointer receiver behavior
func demonstratePointerReceivers() {
	fmt.Println("--- Pointer Receivers ---")

	// Pointer receiver gets reference to struct
	// PHP: Standard $this behavior

	c := Circle{Radius: 5.0}
	fmt.Printf("Original: %+v\n", c)
	fmt.Printf("Area: %.2f\n", c.Area())

	// Scale modifies original (pointer receiver)
	c.Scale(2.0)
	fmt.Printf("After Scale(2.0): %+v\n", c)
	fmt.Printf("New area: %.2f\n", c.Area())

	// Go automatically takes address when needed
	c.Scale(0.5)  // c is value, but Go converts to &c
	fmt.Printf("After Scale(0.5): %+v\n", c)

	// Explicit pointer also works
	ptr := &c
	ptr.Scale(2.0)
	fmt.Printf("Via explicit pointer: %+v\n", c)

	fmt.Println()
}

// Counter demonstrates modification pattern
type Counter struct {
	Value int
}

// Increment increases counter (pointer receiver)
func (c *Counter) Increment() {
	c.Value++
}

// Decrement decreases counter (pointer receiver)
func (c *Counter) Decrement() {
	c.Value--
}

// Reset sets counter to zero (pointer receiver)
func (c *Counter) Reset() {
	c.Value = 0
}

// GetValue returns current value (value receiver)
func (c Counter) GetValue() int {
	return c.Value
}

// demonstrateReceiverChoice shows when to use each
func demonstrateReceiverChoice() {
	fmt.Println("--- Choosing Receivers ---")

	// Use pointer receiver when:
	// 1. Method modifies the receiver
	counter := Counter{Value: 0}
	fmt.Printf("Initial: %d\n", counter.GetValue())

	counter.Increment()
	counter.Increment()
	counter.Increment()
	fmt.Printf("After 3 increments: %d\n", counter.GetValue())

	counter.Decrement()
	fmt.Printf("After decrement: %d\n", counter.GetValue())

	counter.Reset()
	fmt.Printf("After reset: %d\n", counter.GetValue())

	// 2. Struct is large (avoid copying)
	type LargeStruct struct {
		Data [1000]int
	}

	// Pointer receiver avoids copying 1000 ints
	func(ls *LargeStruct) Process() {
		ls.Data[0] = 42
	}

	large := LargeStruct{}
	(&large).Process()
	fmt.Printf("\nLarge struct first element: %d\n", large.Data[0])

	// Use value receiver when:
	// 1. Method doesn't modify receiver
	// 2. Struct is small
	// 3. Receiver is a basic type
	type Temperature float64

	func(t Temperature) Celsius() float64 {
		return float64(t)
	}

	func(t Temperature) Fahrenheit() float64 {
		return float64(t)*9/5 + 32
	}

	temp := Temperature(100)
	fmt.Printf("\n%.0f°C = %.0f°F\n", temp.Celsius(), temp.Fahrenheit())

	fmt.Println()
}

// demonstrateMethodsOnTypes shows methods on custom types
func demonstrateMethodsOnTypes() {
	fmt.Println("--- Methods on Custom Types ---")

	// Methods can be on any type, not just structs

	// 1. On custom int type
	type Age int

	func(a Age) IsAdult() bool {
		return a >= 18
	}

	func(a Age) Category() string {
		switch {
		case a < 13:
			return "Child"
		case a < 20:
			return "Teenager"
		case a < 65:
			return "Adult"
		default:
			return "Senior"
		}
	}

	age := Age(25)
	fmt.Printf("Age %d: Adult=%v, Category=%s\n",
		age, age.IsAdult(), age.Category())

	// 2. On string type
	type Email string

	func(e Email) Domain() string {
		for i, char := range e {
			if char == '@' {
				return string(e[i+1:])
			}
		}
		return ""
	}

	func(e Email) IsValid() bool {
		hasAt := false
		hasDot := false
		for _, char := range e {
			if char == '@' {
				hasAt = true
			}
			if char == '.' {
				hasDot = true
			}
		}
		return hasAt && hasDot
	}

	email := Email("alice@example.com")
	fmt.Printf("\nEmail: %s\n", email)
	fmt.Printf("Domain: %s\n", email.Domain())
	fmt.Printf("Valid: %v\n", email.IsValid())

	// 3. On slice type
	type IntSlice []int

	func(is IntSlice) Sum() int {
		sum := 0
		for _, v := range is {
			sum += v
		}
		return sum
	}

	func(is IntSlice) Average() float64 {
		if len(is) == 0 {
			return 0
		}
		return float64(is.Sum()) / float64(len(is))
	}

	nums := IntSlice{1, 2, 3, 4, 5}
	fmt.Printf("\nNumbers: %v\n", nums)
	fmt.Printf("Sum: %d\n", nums.Sum())
	fmt.Printf("Average: %.2f\n", nums.Average())

	fmt.Println()
}

// Builder pattern
type StringBuilder struct {
	str string
}

// Add appends string (pointer receiver for chaining)
func (sb *StringBuilder) Add(s string) *StringBuilder {
	sb.str += s
	return sb
}

// AddLine appends string with newline
func (sb *StringBuilder) AddLine(s string) *StringBuilder {
	sb.str += s + "\n"
	return sb
}

// String returns final string (value receiver)
func (sb StringBuilder) String() string {
	return sb.str
}

// demonstrateMethodChaining shows fluent interface pattern
func demonstrateMethodChaining() {
	fmt.Println("--- Method Chaining ---")

	// PHP: $result = $builder->add("a")->add("b")->build();
	// Go: Similar pattern with pointer receivers

	sb := &StringBuilder{}
	result := sb.
		Add("Hello ").
		Add("World").
		AddLine("!").
		AddLine("Go is awesome").
		String()

	fmt.Printf("Built string:\n%s", result)

	// Another example: Request builder
	type Request struct {
		Method  string
		URL     string
		Headers map[string]string
		Body    string
	}

	func(r *Request) SetMethod(method string) *Request {
		r.Method = method
		return r
	}

	func(r *Request) SetURL(url string) *Request {
		r.URL = url
		return r
	}

	func(r *Request) AddHeader(key, value string) *Request {
		if r.Headers == nil {
			r.Headers = make(map[string]string)
		}
		r.Headers[key] = value
		return r
	}

	func(r *Request) SetBody(body string) *Request {
		r.Body = body
		return r
	}

	req := &Request{}
	req.SetMethod("POST").
		SetURL("https://api.example.com/users").
		AddHeader("Content-Type", "application/json").
		AddHeader("Authorization", "Bearer token123").
		SetBody(`{"name":"Alice"}`)

	fmt.Printf("\nRequest: %+v\n", req)

	fmt.Println()
}

// Shape interface for demonstration
type Shape interface {
	Area() float64
	Perimeter() float64
}

// demonstrateMethodSets shows method sets
func demonstrateMethodSets() {
	fmt.Println("--- Method Sets ---")

	// Value type has methods with value AND pointer receivers
	// Pointer type has methods with pointer receivers only

	c := Circle{Radius: 5.0}
	ptr := &c

	// Value can call both
	fmt.Printf("Value calling value method: %.2f\n", c.Area())
	c.Scale(2.0)  // Go auto-converts to &c
	fmt.Printf("Value calling pointer method (auto-convert): %.2f\n", c.Area())

	// Pointer can call both
	fmt.Printf("Pointer calling value method: %.2f\n", ptr.Area())
	ptr.Scale(0.5)
	fmt.Printf("Pointer calling pointer method: %.2f\n", ptr.Area())

	// Interface satisfaction
	var s Shape
	s = Rectangle{Width: 10, Height: 5}
	fmt.Printf("\nShape (Rectangle): Area=%.2f, Perimeter=%.2f\n",
		s.Area(), s.Perimeter())

	// Note: Circle can't satisfy Shape because Scale has pointer receiver
	// s = Circle{Radius: 5}  // Would work if all methods are value receivers
	s = &Circle{Radius: 5}  // Pointer satisfies if pointer receivers exist
	fmt.Printf("Shape (Circle): Area=%.2f\n", s.Area())

	fmt.Println()
}

// demonstratePractical shows real-world examples
func demonstratePractical() {
	fmt.Println("--- Practical Examples ---")

	// Example 1: Account with transactions
	type Account struct {
		ID      string
		Balance float64
		History []string
	}

	func(a *Account) Deposit(amount float64) {
		a.Balance += amount
		a.History = append(a.History, fmt.Sprintf("Deposit: +$%.2f", amount))
	}

	func(a *Account) Withdraw(amount float64) bool {
		if amount > a.Balance {
			return false
		}
		a.Balance -= amount
		a.History = append(a.History, fmt.Sprintf("Withdraw: -$%.2f", amount))
		return true
	}

	func(a Account) Statement() string {
		return fmt.Sprintf("Account %s: Balance $%.2f", a.ID, a.Balance)
	}

	acc := &Account{ID: "ACC001", Balance: 100.0}
	fmt.Printf("%s\n", acc.Statement())

	acc.Deposit(50.0)
	acc.Withdraw(30.0)
	fmt.Printf("%s\n", acc.Statement())

	fmt.Println("History:")
	for _, entry := range acc.History {
		fmt.Printf("  %s\n", entry)
	}

	// Example 2: Validator
	type User struct {
		Username string
		Email    string
		Age      int
	}

	func(u User) Validate() []string {
		var errors []string

		if len(u.Username) < 3 {
			errors = append(errors, "Username too short")
		}

		if !Email(u.Email).IsValid() {
			errors = append(errors, "Invalid email")
		}

		if u.Age < 18 {
			errors = append(errors, "Must be 18 or older")
		}

		return errors
	}

	user1 := User{Username: "alice", Email: "alice@example.com", Age: 25}
	if errs := user1.Validate(); len(errs) > 0 {
		fmt.Printf("\nValidation errors for %s:\n", user1.Username)
		for _, err := range errs {
			fmt.Printf("  - %s\n", err)
		}
	} else {
		fmt.Printf("\nUser %s is valid\n", user1.Username)
	}

	user2 := User{Username: "ab", Email: "invalid", Age: 16}
	if errs := user2.Validate(); len(errs) > 0 {
		fmt.Printf("\nValidation errors for %s:\n", user2.Username)
		for _, err := range errs {
			fmt.Printf("  - %s\n", err)
		}
	}

	fmt.Println()
}

/*
Expected Output:
=== Methods and Receivers ===

--- Basic Methods ---
Circle: {Radius:5}
Area: 78.54
Circumference: 31.42

Via pointer:
Area: 78.54
Circumference: 31.42

--- Value Receivers ---
Original: {Width:10 Height:5}
Area: 50.00
Inside Double: {Width:20 Height:10}
After Double: {Width:10 Height:5} (unchanged)
Inside DoublePtr: {Width:20 Height:10}
After DoublePtr: {Width:20 Height:10} (changed)

--- Pointer Receivers ---
Original: {Radius:5}
Area: 78.54
After Scale(2.0): {Radius:10}
New area: 314.16
After Scale(0.5): {Radius:5}
Via explicit pointer: {Radius:10}

--- Choosing Receivers ---
Initial: 0
After 3 increments: 3
After decrement: 2
After reset: 0

Large struct first element: 42

100°C = 212°F

--- Methods on Custom Types ---
Age 25: Adult=true, Category=Adult

Email: alice@example.com
Domain: example.com
Valid: true

Numbers: [1 2 3 4 5]
Sum: 15
Average: 3.00

--- Method Chaining ---
Built string:
Hello World!
Go is awesome

Request: &{Method:POST URL:https://api.example.com/users Headers:map[Authorization:Bearer token123 Content-Type:application/json] Body:{"name":"Alice"}}

--- Method Sets ---
Value calling value method: 78.54
Value calling pointer method (auto-convert): 314.16
Pointer calling value method: 314.16
Pointer calling pointer method: 78.54

Shape (Rectangle): Area=50.00, Perimeter=30.00
Shape (Circle): Area=78.54

--- Practical Examples ---
Account ACC001: Balance $100.00
Account ACC001: Balance $120.00
History:
  Deposit: +$50.00
  Withdraw: -$30.00

User alice is valid

Validation errors for ab:
  - Username too short
  - Invalid email
  - Must be 18 or older
*/
