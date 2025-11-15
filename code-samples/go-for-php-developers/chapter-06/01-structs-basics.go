package main

import (
	"encoding/json"
	"fmt"
	"time"
)

/*
Structs Basics for PHP Developers
==================================

PHP has classes:
	class Person {
	    public $name;
	    public $age;

	    public function __construct($name, $age) {
	        $this->name = $name;
	        $this->age = $age;
	    }
	}

Go has structs (similar to classes but simpler):
	type Person struct {
	    Name string
	    Age  int
	}

Key differences:
- No inheritance (use composition instead)
- No constructors (use functions)
- Methods are defined outside struct
- Exported fields start with uppercase
*/

func main() {
	fmt.Println("=== Structs Basics for PHP Developers ===\n")

	demonstrateStructBasics()
	demonstrateStructCreation()
	demonstrateFieldAccess()
	demonstrateAnonymousStructs()
	demonstrateStructComparison()
	demonstrateStructTags()
	demonstrateNestedStructs()
	demonstratePractical()
}

// demonstrateStructBasics shows basic struct definition
func demonstrateStructBasics() {
	fmt.Println("--- Struct Basics ---")

	// Define struct type
	type Person struct {
		Name string
		Age  int
	}

	// Create instance
	var p1 Person
	fmt.Printf("Zero value: %+v\n", p1)
	fmt.Printf("Name: %q, Age: %d\n", p1.Name, p1.Age)

	// Initialize with values
	p2 := Person{Name: "Alice", Age: 30}
	fmt.Printf("Initialized: %+v\n", p2)

	// Order doesn't matter with field names
	p3 := Person{
		Age:  25,
		Name: "Bob",
	}
	fmt.Printf("Different order: %+v\n", p3)

	// Positional initialization (not recommended)
	p4 := Person{"Carol", 35}
	fmt.Printf("Positional: %+v\n", p4)

	// Partial initialization (rest are zero values)
	p5 := Person{Name: "David"}
	fmt.Printf("Partial: %+v\n", p5)

	fmt.Println()
}

// demonstrateStructCreation shows creation patterns
func demonstrateStructCreation() {
	fmt.Println("--- Struct Creation Patterns ---")

	type User struct {
		ID       int
		Username string
		Email    string
		Created  time.Time
	}

	// Pattern 1: Literal
	u1 := User{
		ID:       1,
		Username: "alice",
		Email:    "alice@example.com",
		Created:  time.Now(),
	}
	fmt.Printf("Literal: %+v\n", u1)

	// Pattern 2: Constructor function
	u2 := NewUser(2, "bob", "bob@example.com")
	fmt.Printf("Constructor: %+v\n", u2)

	// Pattern 3: Pointer with new
	u3 := new(User)
	u3.ID = 3
	u3.Username = "carol"
	u3.Email = "carol@example.com"
	u3.Created = time.Now()
	fmt.Printf("Using new: %+v\n", u3)

	// Pattern 4: Taking address
	u4 := &User{
		ID:       4,
		Username: "david",
		Email:    "david@example.com",
		Created:  time.Now(),
	}
	fmt.Printf("Address operator: %+v\n", u4)

	fmt.Println()
}

type User struct {
	ID       int
	Username string
	Email    string
	Created  time.Time
}

func NewUser(id int, username, email string) *User {
	return &User{
		ID:       id,
		Username: username,
		Email:    email,
		Created:  time.Now(),
	}
}

// demonstrateFieldAccess shows field access patterns
func demonstrateFieldAccess() {
	fmt.Println("--- Field Access ---")

	type Product struct {
		ID    int
		Name  string
		Price float64
		Tags  []string
	}

	p := Product{
		ID:    1,
		Name:  "Widget",
		Price: 19.99,
		Tags:  []string{"hardware", "tools"},
	}

	// Read fields
	fmt.Printf("ID: %d\n", p.ID)
	fmt.Printf("Name: %s\n", p.Name)
	fmt.Printf("Price: $%.2f\n", p.Price)
	fmt.Printf("Tags: %v\n", p.Tags)

	// Modify fields
	p.Price = 24.99
	p.Tags = append(p.Tags, "sale")

	fmt.Printf("\nAfter modification:\n")
	fmt.Printf("Price: $%.2f\n", p.Price)
	fmt.Printf("Tags: %v\n", p.Tags)

	// With pointer
	ptr := &p
	ptr.Price = 29.99  // Auto-dereference
	fmt.Printf("\nVia pointer: $%.2f\n", ptr.Price)

	// Visibility (exported vs unexported)
	type Account struct {
		ID       int    // Exported (public)
		Username string // Exported
		password string // Unexported (private)
	}

	acc := Account{
		ID:       1,
		Username: "alice",
		password: "secret",  // Only accessible in same package
	}

	fmt.Printf("\nAccount: %+v\n", acc)
	// From another package: acc.password would be inaccessible

	fmt.Println()
}

// demonstrateAnonymousStructs shows anonymous struct usage
func demonstrateAnonymousStructs() {
	fmt.Println("--- Anonymous Structs ---")

	// Define and initialize in one go
	person := struct {
		Name string
		Age  int
	}{
		Name: "Alice",
		Age:  30,
	}

	fmt.Printf("Anonymous struct: %+v\n", person)

	// Use case: Table-driven tests
	tests := []struct {
		name     string
		input    int
		expected int
	}{
		{"zero", 0, 0},
		{"positive", 5, 25},
		{"negative", -3, 9},
	}

	fmt.Println("\nTest cases:")
	for _, tc := range tests {
		result := tc.input * tc.input
		status := "PASS"
		if result != tc.expected {
			status = "FAIL"
		}
		fmt.Printf("%s: %d² = %d (expected %d) - %s\n",
			tc.name, tc.input, result, tc.expected, status)
	}

	// Use case: Temporary data structures
	response := struct {
		Success bool
		Message string
		Data    map[string]interface{}
	}{
		Success: true,
		Message: "Operation successful",
		Data: map[string]interface{}{
			"id":   123,
			"name": "test",
		},
	}

	fmt.Printf("\nResponse: %+v\n", response)

	fmt.Println()
}

// demonstrateStructComparison shows struct comparison
func demonstrateStructComparison() {
	fmt.Println("--- Struct Comparison ---")

	type Point struct {
		X, Y int
	}

	p1 := Point{X: 1, Y: 2}
	p2 := Point{X: 1, Y: 2}
	p3 := Point{X: 3, Y: 4}

	// Structs are comparable if all fields are comparable
	fmt.Printf("p1 == p2: %v\n", p1 == p2)
	fmt.Printf("p1 == p3: %v\n", p1 == p3)

	// Structs with slices/maps are NOT comparable
	type Config struct {
		Name  string
		Tags  []string  // Slice makes struct non-comparable
		Hosts map[string]string
	}

	c1 := Config{Name: "prod", Tags: []string{"a"}}
	c2 := Config{Name: "prod", Tags: []string{"a"}}

	// c1 == c2  // COMPILE ERROR!

	// Can compare individual fields
	fmt.Printf("\nNames equal: %v\n", c1.Name == c2.Name)

	// Use reflect.DeepEqual for deep comparison
	// (covered in advanced topics)

	// Structs can be used as map keys if comparable
	distances := make(map[Point]float64)
	distances[Point{0, 0}] = 0.0
	distances[Point{1, 0}] = 1.0
	distances[Point{0, 1}] = 1.0

	fmt.Printf("\nPoint map: %v\n", distances)

	fmt.Println()
}

// demonstrateStructTags shows struct tags (metadata)
func demonstrateStructTags() {
	fmt.Println("--- Struct Tags ---")

	// Tags provide metadata for encoding, validation, etc.
	type Person struct {
		Name  string `json:"name" xml:"name"`
		Age   int    `json:"age" xml:"age"`
		Email string `json:"email,omitempty" xml:"email,omitempty"`
	}

	p := Person{
		Name:  "Alice",
		Age:   30,
		Email: "alice@example.com",
	}

	// JSON encoding
	jsonData, _ := json.Marshal(p)
	fmt.Printf("JSON: %s\n", jsonData)

	// With omitempty
	p2 := Person{
		Name: "Bob",
		Age:  25,
		// Email omitted
	}

	jsonData2, _ := json.Marshal(p2)
	fmt.Printf("JSON (email omitted): %s\n", jsonData2)

	// JSON decoding
	jsonStr := `{"name":"Carol","age":35,"email":"carol@example.com"}`
	var p3 Person
	json.Unmarshal([]byte(jsonStr), &p3)
	fmt.Printf("Decoded: %+v\n", p3)

	// Other common tags:
	type Product struct {
		ID    int     `db:"id" validate:"required"`
		Name  string  `db:"product_name" validate:"required,min=3"`
		Price float64 `db:"price" validate:"required,gt=0"`
	}

	prod := Product{ID: 1, Name: "Widget", Price: 19.99}
	fmt.Printf("\nProduct: %+v\n", prod)

	fmt.Println()
}

// demonstrateNestedStructs shows struct composition
func demonstrateNestedStructs() {
	fmt.Println("--- Nested Structs ---")

	type Address struct {
		Street string
		City   string
		State  string
		Zip    string
	}

	type Person struct {
		Name    string
		Age     int
		Address Address  // Nested struct
	}

	p := Person{
		Name: "Alice",
		Age:  30,
		Address: Address{
			Street: "123 Main St",
			City:   "Springfield",
			State:  "IL",
			Zip:    "62701",
		},
	}

	fmt.Printf("Person: %+v\n", p)
	fmt.Printf("City: %s\n", p.Address.City)

	// Anonymous embedding (composition)
	type Employee struct {
		Person          // Embedded (anonymous field)
		EmployeeID string
		Department string
	}

	emp := Employee{
		Person: Person{
			Name: "Bob",
			Age:  35,
			Address: Address{
				City:  "Boston",
				State: "MA",
			},
		},
		EmployeeID: "E001",
		Department: "Engineering",
	}

	// Can access embedded fields directly
	fmt.Printf("\nEmployee: %+v\n", emp)
	fmt.Printf("Name: %s (via embedding)\n", emp.Name)
	fmt.Printf("City: %s (via embedding)\n", emp.Address.City)
	fmt.Printf("Employee ID: %s\n", emp.EmployeeID)

	// Or explicitly
	fmt.Printf("Name (explicit): %s\n", emp.Person.Name)

	fmt.Println()
}

// demonstratePractical shows practical examples
func demonstratePractical() {
	fmt.Println("--- Practical Examples ---")

	// Example 1: Configuration
	type DBConfig struct {
		Host     string
		Port     int
		Database string
		Username string
		Password string
	}

	db := DBConfig{
		Host:     "localhost",
		Port:     5432,
		Database: "myapp",
		Username: "admin",
		Password: "secret",
	}

	fmt.Printf("DB Config: %+v\n", db)

	// Example 2: Response struct
	type APIResponse struct {
		Status  int
		Message string
		Data    interface{}
		Error   *string
	}

	resp := APIResponse{
		Status:  200,
		Message: "Success",
		Data: map[string]interface{}{
			"users": []string{"alice", "bob"},
			"count": 2,
		},
		Error: nil,
	}

	fmt.Printf("\nAPI Response: %+v\n", resp)

	// Example 3: State machine
	type OrderState int

	const (
		OrderPending OrderState = iota
		OrderProcessing
		OrderShipped
		OrderDelivered
		OrderCancelled
	)

	type Order struct {
		ID       string
		Items    []string
		Total    float64
		State    OrderState
		Created  time.Time
		Updated  time.Time
	}

	order := Order{
		ID:      "ORD-001",
		Items:   []string{"Widget", "Gadget"},
		Total:   49.99,
		State:   OrderPending,
		Created: time.Now(),
		Updated: time.Now(),
	}

	fmt.Printf("\nOrder: %+v\n", order)

	// Example 4: Builder pattern
	type QueryBuilder struct {
		table   string
		fields  []string
		where   map[string]interface{}
		orderBy string
		limit   int
	}

	qb := QueryBuilder{
		table:  "users",
		fields: []string{"id", "name", "email"},
		where: map[string]interface{}{
			"active": true,
			"age >":  18,
		},
		orderBy: "created_at DESC",
		limit:   10,
	}

	fmt.Printf("\nQuery Builder: %+v\n", qb)

	// Example 5: Option pattern
	type ServerOptions struct {
		Host         string
		Port         int
		ReadTimeout  time.Duration
		WriteTimeout time.Duration
		MaxConns     int
	}

	defaultOptions := ServerOptions{
		Host:         "0.0.0.0",
		Port:         8080,
		ReadTimeout:  30 * time.Second,
		WriteTimeout: 30 * time.Second,
		MaxConns:     100,
	}

	// Override specific options
	customOptions := defaultOptions
	customOptions.Port = 9090
	customOptions.MaxConns = 200

	fmt.Printf("\nDefault: %+v\n", defaultOptions)
	fmt.Printf("Custom: %+v\n", customOptions)

	fmt.Println()
}

/*
Expected Output:
=== Structs Basics for PHP Developers ===

--- Struct Basics ---
Zero value: {Name: Age:0}
Name: "", Age: 0
Initialized: {Name:Alice Age:30}
Different order: {Name:Bob Age:25}
Positional: {Name:Carol Age:35}
Partial: {Name:David Age:0}

--- Struct Creation Patterns ---
Literal: {ID:1 Username:alice Email:alice@example.com Created:2025-...}
Constructor: &{ID:2 Username:bob Email:bob@example.com Created:2025-...}
Using new: &{ID:3 Username:carol Email:carol@example.com Created:2025-...}
Address operator: &{ID:4 Username:david Email:david@example.com Created:2025-...}

--- Field Access ---
ID: 1
Name: Widget
Price: $19.99
Tags: [hardware tools]

After modification:
Price: $24.99
Tags: [hardware tools sale]

Via pointer: $29.99

Account: {ID:1 Username:alice password:secret}

--- Anonymous Structs ---
Anonymous struct: {Name:Alice Age:30}

Test cases:
zero: 0² = 0 (expected 0) - PASS
positive: 5² = 25 (expected 25) - PASS
negative: -3² = 9 (expected 9) - PASS

Response: {Success:true Message:Operation successful Data:map[id:123 name:test]}

--- Struct Comparison ---
p1 == p2: true
p1 == p3: false

Names equal: true

Point map: map[{0 0}:0 {0 1}:1 {1 0}:1]

--- Struct Tags ---
JSON: {"name":"Alice","age":30,"email":"alice@example.com"}
JSON (email omitted): {"name":"Bob","age":25}
Decoded: {Name:Carol Age:35 Email:carol@example.com}

Product: {ID:1 Name:Widget Price:19.99}

--- Nested Structs ---
Person: {Name:Alice Age:30 Address:{Street:123 Main St City:Springfield State:IL Zip:62701}}
City: Springfield

Employee: {Person:{Name:Bob Age:35 Address:{Street: City:Boston State:MA Zip:}} EmployeeID:E001 Department:Engineering}
Name: Bob (via embedding)
City: Boston (via embedding)
Employee ID: E001
Name (explicit): Bob

--- Practical Examples ---
DB Config: {Host:localhost Port:5432 Database:myapp Username:admin Password:secret}

API Response: {Status:200 Message:Success Data:map[count:2 users:[alice bob]] Error:<nil>}

Order: {ID:ORD-001 Items:[Widget Gadget] Total:49.99 State:0 Created:2025-... Updated:2025-...}

Query Builder: {table:users fields:[id name email] where:map[active:true age >:18] orderBy:created_at DESC limit:10}

Default: {Host:0.0.0.0 Port:8080 ReadTimeout:30s WriteTimeout:30s MaxConns:100}
Custom: {Host:0.0.0.0 Port:9090 ReadTimeout:30s WriteTimeout:30s MaxConns:200}
*/
