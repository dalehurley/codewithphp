package main

import "fmt"

/*
Composition Over Inheritance for PHP Developers
================================================

PHP uses inheritance:
	class Animal {
	    protected $name;
	    public function speak() { }
	}

	class Dog extends Animal {
	    public function speak() {
	        return "Woof!";
	    }
	}

Go uses composition (embedding):
	type Animal struct {
	    Name string
	}

	type Dog struct {
	    Animal  // Embedded field
	}

	func (d Dog) Speak() string {
	    return "Woof!"
	}

This is "composition over inheritance" - a more flexible design pattern.
*/

func main() {
	fmt.Println("=== Composition Over Inheritance ===\n")

	demonstrateBasicEmbedding()
	demonstrateMethodPromotion()
	demonstrateOverriding()
	demonstrateMultipleEmbedding()
	demonstrateInterfaces()
	demonstratePracticalPatterns()
}

// Basic types for demonstration
type Animal struct {
	Name   string
	Age    int
	Species string
}

func (a Animal) Info() string {
	return fmt.Sprintf("%s (%s, %d years old)", a.Name, a.Species, a.Age)
}

func (a Animal) Sleep() string {
	return fmt.Sprintf("%s is sleeping", a.Name)
}

// Dog embeds Animal
type Dog struct {
	Animal  // Embedded field (anonymous)
	Breed string
}

func (d Dog) Speak() string {
	return "Woof! Woof!"
}

// Cat embeds Animal
type Cat struct {
	Animal
	Indoor bool
}

func (c Cat) Speak() string {
	return "Meow!"
}

// demonstrateBasicEmbedding shows basic embedding syntax
func demonstrateBasicEmbedding() {
	fmt.Println("--- Basic Embedding ---")

	// PHP: class Dog extends Animal { ... }
	// Go: Embed Animal in Dog struct

	dog := Dog{
		Animal: Animal{
			Name:    "Buddy",
			Age:     3,
			Species: "Dog",
		},
		Breed: "Golden Retriever",
	}

	// Access embedded fields directly
	fmt.Printf("Name: %s\n", dog.Name)
	fmt.Printf("Age: %d\n", dog.Age)
	fmt.Printf("Breed: %s\n", dog.Breed)

	// Or explicitly through embedded field
	fmt.Printf("Species: %s\n", dog.Animal.Species)

	// Call embedded methods
	fmt.Printf("Info: %s\n", dog.Info())
	fmt.Printf("Sleep: %s\n", dog.Sleep())

	// Call own methods
	fmt.Printf("Speak: %s\n", dog.Speak())

	fmt.Println()
}

// demonstrateMethodPromotion shows method promotion
func demonstrateMethodPromotion() {
	fmt.Println("--- Method Promotion ---")

	// Embedded type's methods are "promoted" to outer type

	cat := Cat{
		Animal: Animal{
			Name:    "Whiskers",
			Age:     5,
			Species: "Cat",
		},
		Indoor: true,
	}

	// These work because Animal's methods are promoted:
	fmt.Printf("%s\n", cat.Info())    // Promoted from Animal
	fmt.Printf("%s\n", cat.Sleep())   // Promoted from Animal
	fmt.Printf("%s\n", cat.Speak())   // Cat's own method

	// Can also call explicitly
	fmt.Printf("%s\n", cat.Animal.Info())

	// Check indoor status
	if cat.Indoor {
		fmt.Printf("%s is an indoor cat\n", cat.Name)
	}

	fmt.Println()
}

// Bird has its own Speak method
type Bird struct {
	Animal
	CanFly bool
}

func (b Bird) Speak() string {
	return "Tweet! Tweet!"
}

// Fly is Bird-specific
func (b Bird) Fly() string {
	if b.CanFly {
		return fmt.Sprintf("%s is flying!", b.Name)
	}
	return fmt.Sprintf("%s can't fly", b.Name)
}

// demonstrateOverriding shows method "overriding"
func demonstrateOverriding() {
	fmt.Println("--- Method Overriding ---")

	// PHP: Override parent method
	// Go: Define method with same name (shadows embedded method)

	// Bird that overrides Animal's Sleep
	type Owl struct {
		Bird
		Nocturnal bool
	}

	func(o Owl) Sleep() string {
		if o.Nocturnal {
			return fmt.Sprintf("%s sleeps during the day", o.Name)
		}
		return o.Animal.Sleep()  // Call embedded method
	}

	owl := Owl{
		Bird: Bird{
			Animal: Animal{
				Name:    "Hoot",
				Age:     2,
				Species: "Owl",
			},
			CanFly: true,
		},
		Nocturnal: true,
	}

	// Owl's Sleep overrides Animal's Sleep
	fmt.Printf("%s\n", owl.Sleep())

	// Can still call Animal's Sleep explicitly
	fmt.Printf("%s\n", owl.Animal.Sleep())

	// Other methods still promoted
	fmt.Printf("%s\n", owl.Info())
	fmt.Printf("%s\n", owl.Speak())
	fmt.Printf("%s\n", owl.Fly())

	fmt.Println()
}

// demonstrateMultipleEmbedding shows embedding multiple types
func demonstrateMultipleEmbedding() {
	fmt.Println("--- Multiple Embedding ---")

	// Can embed multiple types (not possible with single inheritance)

	type Walker struct{}

	func(w Walker) Walk() string {
		return "Walking..."
	}

	type Swimmer struct{}

	func(s Swimmer) Swim() string {
		return "Swimming..."
	}

	type Flyer struct{}

	func(f Flyer) Fly() string {
		return "Flying..."
	}

	// Duck can walk, swim, and fly!
	type Duck struct {
		Name string
		Walker
		Swimmer
		Flyer
	}

	duck := Duck{Name: "Donald"}

	fmt.Printf("%s says:\n", duck.Name)
	fmt.Printf("  %s\n", duck.Walk())
	fmt.Printf("  %s\n", duck.Swim())
	fmt.Printf("  %s\n", duck.Fly())

	// Human can only walk
	type Human struct {
		Name string
		Walker
	}

	human := Human{Name: "Alice"}
	fmt.Printf("\n%s says:\n", human.Name)
	fmt.Printf("  %s\n", human.Walk())
	// human.Swim()  // COMPILE ERROR! No Swimmer embedded

	// Fish can only swim
	type Fish struct {
		Name string
		Swimmer
	}

	fish := Fish{Name: "Nemo"}
	fmt.Printf("\n%s says:\n", fish.Name)
	fmt.Printf("  %s\n", fish.Swim())

	fmt.Println()
}

// Interfaces for demonstration
type Speaker interface {
	Speak() string
}

type Mover interface {
	Move() string
}

type LivingBeing interface {
	Speaker
	Mover
	Sleep() string
}

// demonstrateInterfaces shows composition with interfaces
func demonstrateInterfaces() {
	fmt.Println("--- Interfaces with Composition ---")

	// Add Move method to existing types
	func(d Dog) Move() string {
		return fmt.Sprintf("%s runs on four legs", d.Name)
	}

	func(c Cat) Move() string {
		return fmt.Sprintf("%s prowls silently", c.Name)
	}

	func(b Bird) Move() string {
		if b.CanFly {
			return b.Fly()
		}
		return fmt.Sprintf("%s hops around", b.Name)
	}

	// All satisfy Speaker interface
	var speakers []Speaker
	speakers = append(speakers,
		Dog{Animal: Animal{Name: "Buddy", Species: "Dog", Age: 3}},
		Cat{Animal: Animal{Name: "Whiskers", Species: "Cat", Age: 5}},
		Bird{Animal: Animal{Name: "Tweety", Species: "Bird", Age: 1}, CanFly: true},
	)

	fmt.Println("All speakers:")
	for _, s := range speakers {
		fmt.Printf("  %s\n", s.Speak())
	}

	// All satisfy LivingBeing interface
	var beings []LivingBeing
	beings = append(beings,
		Dog{Animal: Animal{Name: "Max", Species: "Dog", Age: 4}},
		Cat{Animal: Animal{Name: "Fluffy", Species: "Cat", Age: 3}},
		Bird{Animal: Animal{Name: "Chirpy", Species: "Bird", Age: 2}, CanFly: true},
	)

	fmt.Println("\nAll living beings:")
	for _, b := range beings {
		fmt.Printf("  %s\n", b.Speak())
		fmt.Printf("  %s\n", b.Move())
		fmt.Printf("  %s\n", b.Sleep())
		fmt.Println()
	}

	fmt.Println()
}

// demonstratePracticalPatterns shows real-world patterns
func demonstratePracticalPatterns() {
	fmt.Println("--- Practical Patterns ---")

	// Pattern 1: Logger mixin
	type Logger struct {
		Prefix string
	}

	func(l Logger) Log(msg string) {
		fmt.Printf("[%s] %s\n", l.Prefix, msg)
	}

	func(l Logger) Error(msg string) {
		fmt.Printf("[%s] ERROR: %s\n", l.Prefix, msg)
	}

	// Service with logging
	type UserService struct {
		Logger
	}

	func(us *UserService) CreateUser(name string) {
		us.Log(fmt.Sprintf("Creating user: %s", name))
		// Create user logic here
		us.Log("User created successfully")
	}

	userSvc := &UserService{
		Logger: Logger{Prefix: "UserService"},
	}

	userSvc.CreateUser("Alice")

	// Pattern 2: Timestamped entities
	type Timestamps struct {
		CreatedAt string
		UpdatedAt string
	}

	type Article struct {
		ID      int
		Title   string
		Content string
		Timestamps
	}

	article := Article{
		ID:      1,
		Title:   "Go Composition",
		Content: "...",
		Timestamps: Timestamps{
			CreatedAt: "2025-01-01",
			UpdatedAt: "2025-01-02",
		},
	}

	fmt.Printf("\nArticle: %s (created: %s)\n", article.Title, article.CreatedAt)

	// Pattern 3: Validation mixin
	type Validator struct{}

	func(v Validator) Required(value, field string) error {
		if value == "" {
			return fmt.Errorf("%s is required", field)
		}
		return nil
	}

	func(v Validator) MinLength(value string, min int, field string) error {
		if len(value) < min {
			return fmt.Errorf("%s must be at least %d characters", field, min)
		}
		return nil
	}

	type UserForm struct {
		Username string
		Email    string
		Password string
		Validator
	}

	func(uf UserForm) Validate() []error {
		var errors []error

		if err := uf.Required(uf.Username, "Username"); err != nil {
			errors = append(errors, err)
		}

		if err := uf.MinLength(uf.Username, 3, "Username"); err != nil {
			errors = append(errors, err)
		}

		if err := uf.Required(uf.Password, "Password"); err != nil {
			errors = append(errors, err)
		}

		if err := uf.MinLength(uf.Password, 8, "Password"); err != nil {
			errors = append(errors, err)
		}

		return errors
	}

	form := UserForm{
		Username: "ab",
		Password: "short",
	}

	fmt.Println("\nValidation:")
	if errs := form.Validate(); len(errs) > 0 {
		for _, err := range errs {
			fmt.Printf("  - %v\n", err)
		}
	}

	// Pattern 4: HTTP handler composition
	type BaseHandler struct{}

	func(bh BaseHandler) JSON(status int, data interface{}) string {
		return fmt.Sprintf("HTTP %d: %v", status, data)
	}

	func(bh BaseHandler) Error(status int, message string) string {
		return fmt.Sprintf("HTTP %d: %s", status, message)
	}

	type UserHandler struct {
		BaseHandler
	}

	func(uh UserHandler) HandleCreate() string {
		// Validation, business logic, etc.
		return uh.JSON(201, map[string]string{"id": "123", "name": "Alice"})
	}

	func(uh UserHandler) HandleNotFound() string {
		return uh.Error(404, "User not found")
	}

	handler := UserHandler{}
	fmt.Println("\nHTTP Responses:")
	fmt.Printf("  %s\n", handler.HandleCreate())
	fmt.Printf("  %s\n", handler.HandleNotFound())

	fmt.Println()
}

/*
Expected Output:
=== Composition Over Inheritance ===

--- Basic Embedding ---
Name: Buddy
Age: 3
Breed: Golden Retriever
Species: Dog
Info: Buddy (Dog, 3 years old)
Sleep: Buddy is sleeping
Speak: Woof! Woof!

--- Method Promotion ---
Whiskers (Cat, 5 years old)
Whiskers is sleeping
Meow!
Whiskers (Cat, 5 years old)
Whiskers is an indoor cat

--- Method Overriding ---
Hoot sleeps during the day
Hoot is sleeping
Hoot (Owl, 2 years old)
Tweet! Tweet!
Hoot is flying!

--- Multiple Embedding ---
Donald says:
  Walking...
  Swimming...
  Flying...

Alice says:
  Walking...

Nemo says:
  Swimming...

--- Interfaces with Composition ---
All speakers:
  Woof! Woof!
  Meow!
  Tweet! Tweet!

All living beings:
  Woof! Woof!
  Max runs on four legs
  Max is sleeping

  Meow!
  Fluffy prowls silently
  Fluffy is sleeping

  Tweet! Tweet!
  Chirpy is flying!
  Chirpy is sleeping

--- Practical Patterns ---
[UserService] Creating user: Alice
[UserService] User created successfully

Article: Go Composition (created: 2025-01-01)

Validation:
  - Username must be at least 3 characters
  - Password must be at least 8 characters

HTTP Responses:
  HTTP 201: map[id:123 name:Alice]
  HTTP 404: User not found
*/
