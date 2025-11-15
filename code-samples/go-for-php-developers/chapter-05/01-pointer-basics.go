package main

import "fmt"

/*
Pointer Basics for PHP Developers
==================================

PHP has references but they work differently:
	$a = 5;
	$b = &$a;  // $b is reference to $a
	$b = 10;   // Both $a and $b are now 10

Go has explicit pointers:
	a := 5
	b := &a    // b is pointer to a
	*b = 10    // a is now 10 (dereference with *)

Key concepts:
- & operator: Get address (create pointer)
- * operator: Dereference (get value at address)
- Pointers are typed (*int, *string, etc.)
- nil is the zero value for pointers
- Go has automatic dereferencing for struct fields
*/

func main() {
	fmt.Println("=== Pointer Basics for PHP Developers ===\n")

	demonstratePointerBasics()
	demonstrateValueVsPointer()
	demonstrateNilPointers()
	demonstratePointerArithmetic()
	demonstratePassingPointers()
	demonstrateReturningPointers()
	demonstratePointerComparison()
	demonstratePracticalExamples()
}

// demonstratePointerBasics shows basic pointer syntax
func demonstratePointerBasics() {
	fmt.Println("--- Pointer Basics ---")

	// Variable with value
	x := 42
	fmt.Printf("x = %d\n", x)
	fmt.Printf("Type of x: %T\n", x)

	// Pointer to x
	ptr := &x  // & gets the memory address
	fmt.Printf("\nptr = &x\n")
	fmt.Printf("ptr value (address): %p\n", ptr)
	fmt.Printf("Type of ptr: %T\n", ptr)

	// Dereference pointer
	value := *ptr  // * gets the value at address
	fmt.Printf("\nvalue = *ptr\n")
	fmt.Printf("value = %d\n", value)
	fmt.Printf("Type of value: %T\n", value)

	// Modify through pointer
	*ptr = 100
	fmt.Printf("\nAfter *ptr = 100:\n")
	fmt.Printf("x = %d\n", x)
	fmt.Printf("*ptr = %d\n", *ptr)

	// Modify original
	x = 200
	fmt.Printf("\nAfter x = 200:\n")
	fmt.Printf("x = %d\n", x)
	fmt.Printf("*ptr = %d\n", *ptr)

	// Multiple pointers to same variable
	ptr2 := &x
	fmt.Printf("\nTwo pointers to same variable:\n")
	fmt.Printf("ptr1 address: %p\n", ptr)
	fmt.Printf("ptr2 address: %p\n", ptr2)
	fmt.Printf("Same? %v\n", ptr == ptr2)

	*ptr2 = 300
	fmt.Printf("\nAfter *ptr2 = 300:\n")
	fmt.Printf("x = %d, *ptr = %d, *ptr2 = %d\n", x, *ptr, *ptr2)

	fmt.Println()
}

// demonstrateValueVsPointer shows value vs pointer semantics
func demonstrateValueVsPointer() {
	fmt.Println("--- Value vs Pointer Semantics ---")

	// PHP: Objects are references, primitives are values
	// Go: Everything is a value by default, must use pointers explicitly

	// Pass by value (copy)
	num := 10
	fmt.Printf("Before passValue: %d\n", num)
	passValue(num)
	fmt.Printf("After passValue: %d (unchanged)\n", num)

	// Pass by pointer (reference)
	fmt.Printf("\nBefore passPointer: %d\n", num)
	passPointer(&num)
	fmt.Printf("After passPointer: %d (changed!)\n", num)

	// Arrays are values (copied)
	arr := [3]int{1, 2, 3}
	fmt.Printf("\nBefore modifyArray: %v\n", arr)
	modifyArray(arr)
	fmt.Printf("After modifyArray: %v (unchanged)\n", arr)

	// Slices are references (share underlying array)
	slice := []int{1, 2, 3}
	fmt.Printf("\nBefore modifySlice: %v\n", slice)
	modifySlice(slice)
	fmt.Printf("After modifySlice: %v (changed!)\n", slice)

	// Maps are references
	m := map[string]int{"a": 1}
	fmt.Printf("\nBefore modifyMap: %v\n", m)
	modifyMap(m)
	fmt.Printf("After modifyMap: %v (changed!)\n", m)

	fmt.Println()
}

func passValue(n int) {
	n = 999
	fmt.Printf("  Inside passValue: %d\n", n)
}

func passPointer(n *int) {
	*n = 999
	fmt.Printf("  Inside passPointer: %d\n", *n)
}

func modifyArray(arr [3]int) {
	arr[0] = 999
	fmt.Printf("  Inside modifyArray: %v\n", arr)
}

func modifySlice(s []int) {
	s[0] = 999
	fmt.Printf("  Inside modifySlice: %v\n", s)
}

func modifyMap(m map[string]int) {
	m["a"] = 999
	fmt.Printf("  Inside modifyMap: %v\n", m)
}

// demonstrateNilPointers shows nil pointer handling
func demonstrateNilPointers() {
	fmt.Println("--- Nil Pointers ---")

	// PHP: null
	// Go: nil for pointers

	var ptr *int
	fmt.Printf("Uninitialized pointer: %v\n", ptr)
	fmt.Printf("Is nil? %v\n", ptr == nil)

	// Dereferencing nil pointer panics!
	// *ptr = 10  // PANIC: nil pointer dereference

	// Always check for nil before dereferencing
	if ptr != nil {
		fmt.Printf("Value: %d\n", *ptr)
	} else {
		fmt.Println("Pointer is nil, cannot dereference")
	}

	// Initialize pointer
	value := 42
	ptr = &value
	fmt.Printf("\nAfter initialization:\n")
	fmt.Printf("Pointer: %p\n", ptr)
	fmt.Printf("Is nil? %v\n", ptr == nil)
	fmt.Printf("Value: %d\n", *ptr)

	// Set back to nil
	ptr = nil
	fmt.Printf("\nAfter setting to nil:\n")
	fmt.Printf("Is nil? %v\n", ptr == nil)

	// Using new() to create pointer
	ptr = new(int)  // Allocates memory, returns pointer
	fmt.Printf("\nUsing new(int):\n")
	fmt.Printf("Pointer: %p\n", ptr)
	fmt.Printf("Value (zero): %d\n", *ptr)

	*ptr = 100
	fmt.Printf("After assignment: %d\n", *ptr)

	fmt.Println()
}

// demonstratePointerArithmetic shows Go doesn't have it
func demonstratePointerArithmetic() {
	fmt.Println("--- Pointer Arithmetic (Not Allowed!) ---")

	// C/C++: ptr++, ptr + 5, etc.
	// Go: NO pointer arithmetic for safety

	arr := [5]int{10, 20, 30, 40, 50}
	ptr := &arr[0]

	fmt.Printf("Array: %v\n", arr)
	fmt.Printf("Pointer to first element: %p, value: %d\n", ptr, *ptr)

	// ptr++  // COMPILE ERROR!
	// ptr += 1  // COMPILE ERROR!
	// ptr = ptr + 1  // COMPILE ERROR!

	// Must use indices or slices instead
	for i := range arr {
		fmt.Printf("arr[%d] = %d (address: %p)\n", i, arr[i], &arr[i])
	}

	// Use slices for dynamic arrays
	slice := []int{10, 20, 30, 40, 50}
	for i := range slice {
		slice[i] *= 2
	}
	fmt.Printf("\nDoubled slice: %v\n", slice)

	fmt.Println()
}

// demonstratePassingPointers shows when to use pointers
func demonstratePassingPointers() {
	fmt.Println("--- Passing Pointers to Functions ---")

	// Use case 1: Modify the original value
	num := 10
	fmt.Printf("Original: %d\n", num)
	doubleValue(&num)
	fmt.Printf("After double: %d\n", num)

	// Use case 2: Avoid copying large structures
	type LargeStruct struct {
		Data [1000]int
	}

	large := LargeStruct{}
	large.Data[0] = 42

	fmt.Printf("\nBefore modify: Data[0] = %d\n", large.Data[0])
	modifyLargeStruct(&large)  // Pass pointer to avoid copy
	fmt.Printf("After modify: Data[0] = %d\n", large.Data[0])

	// Use case 3: Optional parameters (can be nil)
	printIfNotNil(nil)
	value := 123
	printIfNotNil(&value)

	// Use case 4: Swap values
	a, b := 5, 10
	fmt.Printf("\nBefore swap: a=%d, b=%d\n", a, b)
	swap(&a, &b)
	fmt.Printf("After swap: a=%d, b=%d\n", a, b)

	fmt.Println()
}

func doubleValue(n *int) {
	*n *= 2
}

func modifyLargeStruct(ls *LargeStruct) {
	ls.Data[0] = 999
}

func printIfNotNil(n *int) {
	if n != nil {
		fmt.Printf("Value: %d\n", *n)
	} else {
		fmt.Println("Value is nil")
	}
}

func swap(a, b *int) {
	*a, *b = *b, *a
}

// demonstrateReturningPointers shows returning pointers
func demonstrateReturningPointers() {
	fmt.Println("--- Returning Pointers ---")

	// Safe to return pointer to local variable in Go
	// (escape analysis moves it to heap)

	ptr := createInt(42)
	fmt.Printf("Returned pointer: %p, value: %d\n", ptr, *ptr)

	// Factory pattern
	person := newPerson("Alice", 30)
	fmt.Printf("Person: %+v\n", *person)

	// Pointer vs value return
	val := getValue()
	fmt.Printf("Value return: %d (copy)\n", val)

	ptr2 := getPointer()
	fmt.Printf("Pointer return: %d (reference)\n", *ptr2)

	fmt.Println()
}

func createInt(value int) *int {
	x := value  // Local variable
	return &x   // Safe! Moved to heap automatically
}

type Person struct {
	Name string
	Age  int
}

func newPerson(name string, age int) *Person {
	return &Person{Name: name, Age: age}
}

func getValue() int {
	return 42
}

func getPointer() *int {
	x := 42
	return &x
}

// demonstratePointerComparison shows pointer equality
func demonstratePointerComparison() {
	fmt.Println("--- Pointer Comparison ---")

	x := 10
	y := 10

	ptr1 := &x
	ptr2 := &x
	ptr3 := &y

	fmt.Printf("x = %d, y = %d\n", x, y)
	fmt.Printf("ptr1 points to x: %p\n", ptr1)
	fmt.Printf("ptr2 points to x: %p\n", ptr2)
	fmt.Printf("ptr3 points to y: %p\n", ptr3)

	// Compare pointer addresses
	fmt.Printf("\nptr1 == ptr2: %v (same address)\n", ptr1 == ptr2)
	fmt.Printf("ptr1 == ptr3: %v (different addresses)\n", ptr1 == ptr3)

	// Compare values
	fmt.Printf("*ptr1 == *ptr3: %v (same value)\n", *ptr1 == *ptr3)

	// Nil comparison
	var nilPtr *int
	fmt.Printf("\nnilPtr == nil: %v\n", nilPtr == nil)
	fmt.Printf("ptr1 == nil: %v\n", ptr1 == nil)

	fmt.Println()
}

// demonstratePracticalExamples shows real-world uses
func demonstratePracticalExamples() {
	fmt.Println("--- Practical Examples ---")

	// Example 1: Optional fields
	fmt.Println("Optional fields:")
	user1 := User{Name: "Alice", Email: stringPtr("alice@example.com")}
	user2 := User{Name: "Bob", Email: nil}

	printUser(user1)
	printUser(user2)

	// Example 2: Updating configuration
	fmt.Println("\nConfiguration update:")
	config := Config{Host: "localhost", Port: 8080}
	fmt.Printf("Before: %+v\n", config)

	updateHost := "example.com"
	updateConfig(&config, &updateHost, nil)
	fmt.Printf("After host update: %+v\n", config)

	updatePort := 9090
	updateConfig(&config, nil, &updatePort)
	fmt.Printf("After port update: %+v\n", config)

	// Example 3: Linked list node
	fmt.Println("\nLinked list:")
	head := &Node{Value: 1}
	head.Next = &Node{Value: 2}
	head.Next.Next = &Node{Value: 3}

	printList(head)

	// Example 4: Tree structure
	fmt.Println("\nBinary tree:")
	root := &TreeNode{Value: 10}
	root.Left = &TreeNode{Value: 5}
	root.Right = &TreeNode{Value: 15}
	root.Left.Left = &TreeNode{Value: 3}
	root.Left.Right = &TreeNode{Value: 7}

	fmt.Print("In-order: ")
	inOrder(root)
	fmt.Println()

	fmt.Println()
}

type User struct {
	Name  string
	Email *string  // Optional field
}

func stringPtr(s string) *string {
	return &s
}

func printUser(u User) {
	fmt.Printf("Name: %s", u.Name)
	if u.Email != nil {
		fmt.Printf(", Email: %s", *u.Email)
	} else {
		fmt.Print(", Email: not provided")
	}
	fmt.Println()
}

type Config struct {
	Host string
	Port int
}

func updateConfig(c *Config, host *string, port *int) {
	if host != nil {
		c.Host = *host
	}
	if port != nil {
		c.Port = *port
	}
}

type Node struct {
	Value int
	Next  *Node
}

func printList(head *Node) {
	current := head
	for current != nil {
		fmt.Printf("%d ", current.Value)
		current = current.Next
	}
	fmt.Println()
}

type TreeNode struct {
	Value int
	Left  *TreeNode
	Right *TreeNode
}

func inOrder(node *TreeNode) {
	if node == nil {
		return
	}
	inOrder(node.Left)
	fmt.Printf("%d ", node.Value)
	inOrder(node.Right)
}

/*
Expected Output:
=== Pointer Basics for PHP Developers ===

--- Pointer Basics ---
x = 42
Type of x: int

ptr = &x
ptr value (address): 0x... (actual address varies)
Type of ptr: *int

value = *ptr
value = 42
Type of value: int

After *ptr = 100:
x = 100
*ptr = 100

After x = 200:
x = 200
*ptr = 200

Two pointers to same variable:
ptr1 address: 0x...
ptr2 address: 0x...
Same? true

After *ptr2 = 300:
x = 300, *ptr = 300, *ptr2 = 300

--- Value vs Pointer Semantics ---
Before passValue: 10
  Inside passValue: 999
After passValue: 10 (unchanged)

Before passPointer: 10
  Inside passPointer: 999
After passPointer: 999 (changed!)

Before modifyArray: [1 2 3]
  Inside modifyArray: [999 2 3]
After modifyArray: [1 2 3] (unchanged)

Before modifySlice: [1 2 3]
  Inside modifySlice: [999 2 3]
After modifySlice: [999 2 3] (changed!)

Before modifyMap: map[a:1]
  Inside modifyMap: map[a:999]
After modifyMap: map[a:999] (changed!)

--- Nil Pointers ---
Uninitialized pointer: <nil>
Is nil? true
Pointer is nil, cannot dereference

After initialization:
Pointer: 0x...
Is nil? false
Value: 42

After setting to nil:
Is nil? true

Using new(int):
Pointer: 0x...
Value (zero): 0
After assignment: 100

--- Pointer Arithmetic (Not Allowed!) ---
Array: [10 20 30 40 50]
Pointer to first element: 0x..., value: 10
arr[0] = 10 (address: 0x...)
arr[1] = 20 (address: 0x...)
arr[2] = 30 (address: 0x...)
arr[3] = 40 (address: 0x...)
arr[4] = 50 (address: 0x...)

Doubled slice: [20 40 60 80 100]

--- Passing Pointers to Functions ---
Original: 10
After double: 20

Before modify: Data[0] = 42
After modify: Data[0] = 999
Value is nil
Value: 123

Before swap: a=5, b=10
After swap: a=10, b=5

--- Returning Pointers ---
Returned pointer: 0x..., value: 42
Person: &{Name:Alice Age:30}
Value return: 42 (copy)
Pointer return: 42 (reference)

--- Pointer Comparison ---
x = 10, y = 10
ptr1 points to x: 0x...
ptr2 points to x: 0x...
ptr3 points to y: 0x...

ptr1 == ptr2: true (same address)
ptr1 == ptr3: false (different addresses)
*ptr1 == *ptr3: true (same value)

nilPtr == nil: true
ptr1 == nil: false

--- Practical Examples ---
Optional fields:
Name: Alice, Email: alice@example.com
Name: Bob, Email: not provided

Configuration update:
Before: {Host:localhost Port:8080}
After host update: {Host:example.com Port:8080}
After port update: {Host:example.com Port:9090}

Linked list:
1 2 3

Binary tree:
In-order: 3 5 7 10 15
*/
