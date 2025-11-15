package main

import "fmt"

/*
Pointers with Structs for PHP Developers
=========================================

PHP objects are always references:
	$user = new User();
	$copy = $user;  // Same object reference
	$copy->name = "New";  // Modifies original

Go structs are values by default:
	user := User{}
	copy := user  // Copies the struct
	copy.Name = "New"  // Doesn't modify original

	// Use pointers for reference behavior:
	ptr := &user
	ptr.Name = "New"  // Modifies original

This file demonstrates pointer usage with structs.
*/

func main() {
	fmt.Println("=== Pointers with Structs ===\n")

	demonstrateStructValue()
	demonstrateStructPointer()
	demonstrateAutoDereference()
	demonstrateConstructorPattern()
	demonstrateMethodReceivers()
	demonstrateNilStructs()
	demonstrateEmbedding()
	demonstratePractical()
}

// Person struct for demonstrations
type Person struct {
	Name string
	Age  int
}

// demonstrateStructValue shows value semantics
func demonstrateStructValue() {
	fmt.Println("--- Struct as Value ---")

	// PHP: $person = new Person();
	// Go: person := Person{} (value, not reference!)

	person1 := Person{Name: "Alice", Age: 30}
	fmt.Printf("person1: %+v\n", person1)

	// Assignment copies the struct
	person2 := person1
	person2.Name = "Bob"
	person2.Age = 25

	fmt.Printf("\nAfter modifying person2:\n")
	fmt.Printf("person1: %+v (unchanged)\n", person1)
	fmt.Printf("person2: %+v (new copy)\n", person2)

	// Function call copies struct
	modifyPersonValue(person1)
	fmt.Printf("\nAfter modifyPersonValue:\n")
	fmt.Printf("person1: %+v (unchanged)\n", person1)

	fmt.Println()
}

func modifyPersonValue(p Person) {
	p.Name = "Modified"
	fmt.Printf("Inside function: %+v\n", p)
}

// demonstrateStructPointer shows pointer semantics
func demonstrateStructPointer() {
	fmt.Println("--- Struct Pointer ---")

	// Create struct pointer
	person1 := &Person{Name: "Alice", Age: 30}
	fmt.Printf("person1 (pointer): %+v\n", person1)
	fmt.Printf("Type: %T\n", person1)

	// Share reference
	person2 := person1
	person2.Name = "Bob"  // Modifies shared struct

	fmt.Printf("\nAfter modifying via person2:\n")
	fmt.Printf("person1: %+v (modified!)\n", person1)
	fmt.Printf("person2: %+v\n", person2)

	// Function call shares reference
	modifyPersonPointer(person1)
	fmt.Printf("\nAfter modifyPersonPointer:\n")
	fmt.Printf("person1: %+v (modified!)\n", person1)

	// Get pointer from value
	person3 := Person{Name: "Carol", Age: 35}
	ptr := &person3
	ptr.Age = 36

	fmt.Printf("\nPointer from value:\n")
	fmt.Printf("person3: %+v\n", person3)
	fmt.Printf("via ptr: %+v\n", ptr)

	fmt.Println()
}

func modifyPersonPointer(p *Person) {
	p.Name = "Modified"
	p.Age = 99
	fmt.Printf("Inside function: %+v\n", p)
}

// demonstrateAutoDereference shows automatic dereferencing
func demonstrateAutoDereference() {
	fmt.Println("--- Auto-Dereference ---")

	// Go automatically dereferences struct pointers
	// for field access (syntactic sugar)

	person := &Person{Name: "Alice", Age: 30}

	// These are equivalent:
	fmt.Printf("person.Name: %s\n", person.Name)
	fmt.Printf("(*person).Name: %s\n", (*person).Name)

	// Auto-dereference for assignment
	person.Age = 31            // Automatic
	(*person).Age = 32         // Explicit

	fmt.Printf("person.Age: %d\n", person.Age)

	// Works with nested structs too
	type Address struct {
		City  string
		State string
	}

	type Employee struct {
		Person  Person
		Address Address
	}

	emp := &Employee{
		Person:  Person{Name: "Bob", Age: 25},
		Address: Address{City: "NYC", State: "NY"},
	}

	// All of these work with auto-dereference:
	fmt.Printf("\nEmployee: %s, %d, %s, %s\n",
		emp.Person.Name,
		emp.Person.Age,
		emp.Address.City,
		emp.Address.State)

	emp.Person.Name = "Robert"
	emp.Address.City = "Boston"

	fmt.Printf("Updated: %s, %s\n", emp.Person.Name, emp.Address.City)

	fmt.Println()
}

// demonstrateConstructorPattern shows constructor patterns
func demonstrateConstructorPattern() {
	fmt.Println("--- Constructor Pattern ---")

	// Go doesn't have constructors, use functions
	// Convention: NewTypeName

	// Return value
	p1 := NewPerson("Alice", 30)
	fmt.Printf("NewPerson (value): %+v\n", p1)

	// Return pointer (more common for large structs)
	p2 := NewPersonPtr("Bob", 25)
	fmt.Printf("NewPersonPtr (pointer): %+v\n", p2)

	// With validation
	p3, err := NewValidatedPerson("Carol", 35)
	if err != nil {
		fmt.Printf("Error: %v\n", err)
	} else {
		fmt.Printf("NewValidatedPerson: %+v\n", p3)
	}

	p4, err := NewValidatedPerson("", -5)
	if err != nil {
		fmt.Printf("Error: %v\n", err)
	}

	// Functional options pattern
	p5 := NewPersonWithOptions(
		WithName("David"),
		WithAge(40),
		WithEmail("david@example.com"),
	)
	fmt.Printf("With options: %+v\n", p5)

	fmt.Println()
}

func NewPerson(name string, age int) Person {
	return Person{Name: name, Age: age}
}

func NewPersonPtr(name string, age int) *Person {
	return &Person{Name: name, Age: age}
}

func NewValidatedPerson(name string, age int) (*Person, error) {
	if name == "" {
		return nil, fmt.Errorf("name cannot be empty")
	}
	if age < 0 {
		return nil, fmt.Errorf("age cannot be negative")
	}
	return &Person{Name: name, Age: age}, nil
}

type PersonWithEmail struct {
	Name  string
	Age   int
	Email string
}

type PersonOption func(*PersonWithEmail)

func WithName(name string) PersonOption {
	return func(p *PersonWithEmail) {
		p.Name = name
	}
}

func WithAge(age int) PersonOption {
	return func(p *PersonWithEmail) {
		p.Age = age
	}
}

func WithEmail(email string) PersonOption {
	return func(p *PersonWithEmail) {
		p.Email = email
	}
}

func NewPersonWithOptions(opts ...PersonOption) *PersonWithEmail {
	p := &PersonWithEmail{}
	for _, opt := range opts {
		opt(p)
	}
	return p
}

// demonstrateMethodReceivers shows value vs pointer receivers
func demonstrateMethodReceivers() {
	fmt.Println("--- Method Receivers ---")

	// Value receiver (gets copy)
	p1 := Person{Name: "Alice", Age: 30}
	fmt.Printf("Before ValueMethod: %+v\n", p1)
	p1.ValueMethod()
	fmt.Printf("After ValueMethod: %+v (unchanged)\n", p1)

	// Pointer receiver (gets reference)
	p2 := Person{Name: "Bob", Age: 25}
	fmt.Printf("\nBefore PointerMethod: %+v\n", p2)
	p2.PointerMethod()  // Go automatically takes address!
	fmt.Printf("After PointerMethod: %+v (changed)\n", p2)

	// Explicit pointer
	p3 := &Person{Name: "Carol", Age: 35}
	fmt.Printf("\nWith explicit pointer:\n")
	p3.PointerMethod()
	fmt.Printf("After PointerMethod: %+v\n", p3)

	// Value method on pointer also works
	p3.ValueMethod()
	fmt.Printf("After ValueMethod: %+v (unchanged)\n", p3)

	fmt.Println()
}

// ValueMethod has value receiver
func (p Person) ValueMethod() {
	p.Age += 10  // Modifies copy
	fmt.Printf("Inside ValueMethod: %+v\n", p)
}

// PointerMethod has pointer receiver
func (p *Person) PointerMethod() {
	p.Age += 10  // Modifies original
	fmt.Printf("Inside PointerMethod: %+v\n", p)
}

// Guidelines for choosing receivers:
// Use pointer receiver when:
// - Method modifies the receiver
// - Struct is large (avoid copying)
// - Consistency (if some methods use pointer)
// Use value receiver when:
// - Method doesn't modify receiver
// - Struct is small and cheap to copy
// - Receiver is a map, slice, or function (already references)

// demonstrateNilStructs shows nil struct pointers
func demonstrateNilStructs() {
	fmt.Println("--- Nil Struct Pointers ---")

	var p *Person
	fmt.Printf("Uninitialized: %v\n", p)
	fmt.Printf("Is nil: %v\n", p == nil)

	// Accessing fields panics!
	// fmt.Println(p.Name)  // PANIC!

	// Check before accessing
	if p != nil {
		fmt.Println(p.Name)
	} else {
		fmt.Println("Pointer is nil")
	}

	// Methods on nil receivers can work!
	p.SafeMethod()

	// Initialize
	p = &Person{Name: "Alice", Age: 30}
	fmt.Printf("\nAfter init: %+v\n", p)
	p.SafeMethod()

	fmt.Println()
}

// SafeMethod handles nil receiver
func (p *Person) SafeMethod() {
	if p == nil {
		fmt.Println("Called on nil receiver")
		return
	}
	fmt.Printf("Called on: %+v\n", p)
}

// demonstrateEmbedding shows struct embedding with pointers
func demonstrateEmbedding() {
	fmt.Println("--- Struct Embedding ---")

	type Address struct {
		City  string
		State string
	}

	type Employee struct {
		Person          // Embedded struct
		*Address        // Embedded pointer
		EmployeeID string
	}

	emp := Employee{
		Person:     Person{Name: "Alice", Age: 30},
		Address:    &Address{City: "NYC", State: "NY"},
		EmployeeID: "E001",
	}

	// Access embedded fields directly
	fmt.Printf("Name: %s\n", emp.Name)  // From Person
	fmt.Printf("City: %s\n", emp.City)  // From Address
	fmt.Printf("ID: %s\n", emp.EmployeeID)

	// Can also access via field name
	fmt.Printf("\nExplicit: %s, %s\n", emp.Person.Name, emp.Address.City)

	// Modify embedded struct
	emp.Age = 31
	emp.State = "CA"
	fmt.Printf("Modified: %+v\n", emp)

	// Embedded pointer can be nil
	emp2 := Employee{
		Person:     Person{Name: "Bob", Age: 25},
		Address:    nil,
		EmployeeID: "E002",
	}

	fmt.Printf("\nEmployee 2: %+v\n", emp2)
	// emp2.City would panic if accessed!

	if emp2.Address != nil {
		fmt.Println(emp2.City)
	} else {
		fmt.Println("No address")
	}

	fmt.Println()
}

// demonstratePractical shows practical patterns
func demonstratePractical() {
	fmt.Println("--- Practical Patterns ---")

	// Pattern 1: Fluent interface (method chaining)
	b := NewBuilder()
	result := b.SetName("Alice").
		SetAge(30).
		SetEmail("alice@example.com").
		Build()

	fmt.Printf("Built: %+v\n", result)

	// Pattern 2: In-place modification
	users := []*User{
		{ID: 1, Name: "Alice", Active: false},
		{ID: 2, Name: "Bob", Active: false},
		{ID: 3, Name: "Carol", Active: false},
	}

	fmt.Println("\nBefore activation:")
	for _, u := range users {
		fmt.Printf("  %+v\n", u)
	}

	// Activate all users
	for _, u := range users {
		u.Activate()
	}

	fmt.Println("\nAfter activation:")
	for _, u := range users {
		fmt.Printf("  %+v\n", u)
	}

	// Pattern 3: Safe updates
	config := &Config{Host: "localhost", Port: 8080}
	fmt.Printf("\nOriginal config: %+v\n", config)

	config.Update(map[string]interface{}{
		"host": "example.com",
		"port": 9090,
	})

	fmt.Printf("Updated config: %+v\n", config)

	fmt.Println()
}

type Builder struct {
	person *PersonWithEmail
}

func NewBuilder() *Builder {
	return &Builder{person: &PersonWithEmail{}}
}

func (b *Builder) SetName(name string) *Builder {
	b.person.Name = name
	return b  // Return self for chaining
}

func (b *Builder) SetAge(age int) *Builder {
	b.person.Age = age
	return b
}

func (b *Builder) SetEmail(email string) *Builder {
	b.person.Email = email
	return b
}

func (b *Builder) Build() *PersonWithEmail {
	return b.person
}

type User struct {
	ID     int
	Name   string
	Active bool
}

func (u *User) Activate() {
	u.Active = true
}

type Config struct {
	Host string
	Port int
}

func (c *Config) Update(updates map[string]interface{}) {
	if host, ok := updates["host"].(string); ok {
		c.Host = host
	}
	if port, ok := updates["port"].(int); ok {
		c.Port = port
	}
}

/*
Expected Output:
=== Pointers with Structs ===

--- Struct as Value ---
person1: {Name:Alice Age:30}

After modifying person2:
person1: {Name:Alice Age:30} (unchanged)
person2: {Name:Bob Age:25} (new copy)
Inside function: {Name:Modified Age:30}

After modifyPersonValue:
person1: {Name:Alice Age:30} (unchanged)

--- Struct Pointer ---
person1 (pointer): &{Name:Alice Age:30}
Type: *main.Person

After modifying via person2:
person1: &{Name:Bob Age:30} (modified!)
person2: &{Name:Bob Age:30}
Inside function: &{Name:Modified Age:99}

After modifyPersonPointer:
person1: &{Name:Modified Age:99} (modified!)

Pointer from value:
person3: {Name:Carol Age:36}
via ptr: &{Name:Carol Age:36}

--- Auto-Dereference ---
person.Name: Alice
(*person).Name: Alice
person.Age: 32

Employee: Bob, 25, NYC, NY
Updated: Robert, Boston

--- Constructor Pattern ---
NewPerson (value): {Name:Alice Age:30}
NewPersonPtr (pointer): &{Name:Bob Age:25}
NewValidatedPerson: &{Name:Carol Age:35}
Error: name cannot be empty
With options: &{Name:David Age:40 Email:david@example.com}

--- Method Receivers ---
Before ValueMethod: {Name:Alice Age:30}
Inside ValueMethod: {Name:Alice Age:40}
After ValueMethod: {Name:Alice Age:30} (unchanged)

Before PointerMethod: {Name:Bob Age:25}
Inside PointerMethod: &{Name:Bob Age:35}
After PointerMethod: {Name:Bob Age:35} (changed)

With explicit pointer:
Inside PointerMethod: &{Name:Carol Age:45}
After PointerMethod: &{Name:Carol Age:45}
Inside ValueMethod: {Name:Carol Age:55}
After PointerMethod: &{Name:Carol Age:45} (unchanged)

--- Nil Struct Pointers ---
Uninitialized: <nil>
Is nil: true
Pointer is nil
Called on nil receiver

After init: &{Name:Alice Age:30}
Called on: &{Name:Alice Age:30}

--- Struct Embedding ---
Name: Alice
City: NYC
ID: E001

Explicit: Alice, NYC
Modified: {Person:{Name:Alice Age:31} Address:0x... EmployeeID:E001}

Employee 2: {Person:{Name:Bob Age:25} Address:<nil> EmployeeID:E002}
No address

--- Practical Patterns ---
Built: &{Name:Alice Age:30 Email:alice@example.com}

Before activation:
  &{ID:1 Name:Alice Active:false}
  &{ID:2 Name:Bob Active:false}
  &{ID:3 Name:Carol Active:false}

After activation:
  &{ID:1 Name:Alice Active:true}
  &{ID:2 Name:Bob Active:true}
  &{ID:3 Name:Carol Active:true}

Original config: &{Host:localhost Port:8080}
Updated config: &{Host:example.com Port:9090}
*/
