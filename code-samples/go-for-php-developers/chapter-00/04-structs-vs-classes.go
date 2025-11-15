package main

import "fmt"

/*
PHP VERSION:
<?php
class User {
    private string $name;
    private int $age;
    private string $email;

    public function __construct(string $name, int $age, string $email) {
        $this->name = $name;
        $this->age = $age;
        $this->email = $email;
    }

    public function greet(): string {
        return "Hello, I'm {$this->name}";
    }

    public function isAdult(): bool {
        return $this->age >= 18;
    }
}

class Admin extends User {
    private string $role;

    public function __construct(string $name, int $age, string $email, string $role) {
        parent::__construct($name, $age, $email);
        $this->role = $role;
    }
}

$user = new User("Alice", 30, "alice@example.com");
echo $user->greet() . "\n";
*/

// Go uses structs instead of classes
// No classes, no inheritance, no constructors

// User struct - like a simple PHP class
type User struct {
	Name  string // Capitalized = exported (public)
	Age   int
	email string // lowercase = unexported (private)
}

// Constructor pattern (not built-in, just a function)
func NewUser(name string, age int, email string) *User {
	return &User{
		Name:  name,
		Age:   age,
		email: email,
	}
}

// Method with value receiver (gets a copy)
func (u User) Greet() string {
	return fmt.Sprintf("Hello, I'm %s", u.Name)
}

// Method with pointer receiver (can modify the struct)
func (u *User) HaveBirthday() {
	u.Age++
}

// Method accessing private field
func (u *User) GetEmail() string {
	return u.email
}

// Admin struct - uses composition instead of inheritance
type Admin struct {
	User // Embedded struct (composition)
	Role string
	Permissions []string
}

// Constructor for Admin
func NewAdmin(name string, age int, email string, role string) *Admin {
	return &Admin{
		User: User{
			Name:  name,
			Age:   age,
			email: email,
		},
		Role: role,
		Permissions: []string{"read", "write", "delete"},
	}
}

// Admin-specific method
func (a *Admin) HasPermission(perm string) bool {
	for _, p := range a.Permissions {
		if p == perm {
			return true
		}
	}
	return false
}

// Override/extend User's Greet method
func (a *Admin) Greet() string {
	return fmt.Sprintf("Hello, I'm %s (Admin: %s)", a.Name, a.Role)
}

// Interface - defines behavior (like PHP interfaces)
type Greeter interface {
	Greet() string
}

// Any type with a Greet() string method implements this interface
// No explicit "implements" keyword needed!

// Function that works with any Greeter
func SayHello(g Greeter) {
	fmt.Println(g.Greet())
}

// Another struct implementing Greeter
type Bot struct {
	ID   int
	Name string
}

func (b *Bot) Greet() string {
	return fmt.Sprintf("Beep boop! I'm bot %s", b.Name)
}

func main() {
	fmt.Println("=== Structs vs Classes in Go ===\n")

	// Creating a User (using constructor function)
	user := NewUser("Alice", 30, "alice@example.com")
	fmt.Println(user.Greet())
	fmt.Printf("Age: %d\n", user.Age)
	fmt.Printf("Email: %s\n", user.GetEmail())

	// Using a method with pointer receiver
	user.HaveBirthday()
	fmt.Printf("After birthday: Age %d\n", user.Age)

	// Creating a struct directly (without constructor)
	user2 := &User{
		Name:  "Bob",
		Age:   25,
		email: "bob@example.com",
	}
	fmt.Printf("\n%s is %d years old\n", user2.Name, user2.Age)

	// Admin using composition
	admin := NewAdmin("Charlie", 35, "charlie@example.com", "SuperAdmin")
	fmt.Printf("\n%s\n", admin.Greet())
	fmt.Printf("Can write: %t\n", admin.HasPermission("write"))
	fmt.Printf("Can execute: %t\n", admin.HasPermission("execute"))

	// Accessing embedded User fields directly
	fmt.Printf("Admin age: %d\n", admin.Age) // From embedded User

	// Interface usage - polymorphism
	fmt.Println("\n=== Interface Example ===")

	// Both User and Admin implement Greeter
	SayHello(user)
	SayHello(admin)

	// Bot also implements Greeter
	bot := &Bot{ID: 1, Name: "Helper"}
	SayHello(bot)

	// Struct comparison
	fmt.Println("\n=== Struct Features ===")

	// Structs can be compared
	p1 := User{Name: "Dave", Age: 40, email: "dave@example.com"}
	p2 := User{Name: "Dave", Age: 40, email: "dave@example.com"}
	fmt.Printf("p1 == p2: %t\n", p1 == p2)

	// Zero value of struct
	var emptyUser User
	fmt.Printf("Empty user: %+v\n", emptyUser)

	// Anonymous struct (useful for one-off data)
	config := struct {
		Host string
		Port int
	}{
		Host: "localhost",
		Port: 8080,
	}
	fmt.Printf("Config: %s:%d\n", config.Host, config.Port)
}

/*
OUTPUT:
=== Structs vs Classes in Go ===

Hello, I'm Alice
Age: 30
Email: alice@example.com
After birthday: Age 31

Bob is 25 years old

Hello, I'm Charlie (Admin: SuperAdmin)
Can write: true
Can execute: false
Admin age: 35

=== Interface Example ===
Hello, I'm Alice
Hello, I'm Charlie (Admin: SuperAdmin)
Beep boop! I'm bot Helper

=== Struct Features ===
p1 == p2: true
Empty user: {Name: Age:0 email:}
Config: localhost:8080
*/

/*
KEY DIFFERENCES FROM PHP:

1. NO CLASSES:
   PHP: class User { }
   Go:  type User struct { }

2. NO INHERITANCE:
   PHP: class Admin extends User { }
   Go:  type Admin struct { User } // Composition/embedding

3. NO CONSTRUCTORS:
   PHP: public function __construct() { }
   Go:  func NewUser() *User { } // Convention, not built-in

4. VISIBILITY:
   PHP: public/private/protected keywords
   Go:  Capitalized = exported, lowercase = unexported

5. METHODS:
   PHP: Methods inside class
   Go:  Methods defined outside struct with receiver

6. INTERFACES:
   PHP: class Foo implements BarInterface { }
   Go:  Implicit - any type with matching methods implements interface

STRUCT BEST PRACTICES:

1. Use pointer receivers for methods that modify the struct:
   func (u *User) Update() { }

2. Use value receivers for read-only methods:
   func (u User) GetName() string { }

3. Be consistent - if one method uses pointer receiver, use it for all

4. Constructor pattern (not required but common):
   func NewType(params) *Type { return &Type{...} }

5. Embed structs for composition:
   type Admin struct { User; role string }

COMPOSITION OVER INHERITANCE:

PHP Inheritance:
- Admin extends User
- Tightly coupled
- Can only extend one class

Go Composition:
- Admin contains User
- Loosely coupled
- Can embed multiple structs
- More flexible

INTERFACE BENEFITS:

1. Implicit implementation - no "implements" keyword
2. Small interfaces are better (often 1-2 methods)
3. Accept interfaces, return structs
4. Easy to test with mocks

COMMON PATTERNS:

1. Constructor:
   func NewUser(name string) *User {
       return &User{Name: name}
   }

2. Getter for private field:
   func (u *User) Email() string {
       return u.email
   }

3. Builder pattern:
   user := &User{}
   user.SetName("Alice").SetAge(30)

NEXT STEPS:
- Run: go run 04-structs-vs-classes.go
- Create your own structs
- Practice composition over inheritance
- Experiment with interfaces
*/
