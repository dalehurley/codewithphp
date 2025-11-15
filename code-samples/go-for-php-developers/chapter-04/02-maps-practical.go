package main

import (
	"fmt"
	"sort"
	"strings"
)

/*
Maps Practical Guide for PHP Developers
========================================

PHP associative arrays:
	$user = [
	    'name' => 'Alice',
	    'age' => 30,
	    'city' => 'NYC'
	];

Go maps (similar concept):
	user := map[string]interface{}{
	    "name": "Alice",
	    "age":  30,
	    "city": "NYC",
	}

Key differences:
- Maps are reference types (like PHP arrays)
- Keys must be comparable type
- No ordering (unlike PHP 7+)
- Comma-ok idiom for checking existence
- Must initialize before use (or use make)
*/

func main() {
	fmt.Println("=== Maps Practical Guide for PHP Developers ===\n")

	demonstrateBasicMaps()
	demonstrateMapOperations()
	demonstrateCheckingKeys()
	demonstrateIterating()
	demonstrateMapAsReference()
	demonstrateComplexMaps()
	demonstratePracticalPatterns()
	demonstrateMapVsStruct()
}

// demonstrateBasicMaps shows basic map usage
func demonstrateBasicMaps() {
	fmt.Println("--- Basic Maps ---")

	// PHP: $ages = ['Alice' => 30, 'Bob' => 25];
	// Go: ages := map[string]int{"Alice": 30, "Bob": 25}

	// Map literal
	ages := map[string]int{
		"Alice": 30,
		"Bob":   25,
		"Carol": 35,
	}
	fmt.Printf("Ages: %v\n", ages)

	// Access elements
	fmt.Printf("Alice's age: %d\n", ages["Alice"])
	fmt.Printf("Bob's age: %d\n", ages["Bob"])

	// Add/Update
	ages["David"] = 28  // Add
	ages["Alice"] = 31  // Update

	fmt.Printf("After changes: %v\n", ages)

	// Delete
	delete(ages, "Bob")
	fmt.Printf("After delete: %v\n", ages)

	// Create with make
	scores := make(map[string]int)
	scores["math"] = 95
	scores["english"] = 87
	scores["science"] = 92

	fmt.Printf("Scores: %v\n", scores)

	// Zero value is nil
	var nilMap map[string]int
	fmt.Printf("Nil map: %v (is nil: %v)\n", nilMap, nilMap == nil)
	// nilMap["key"] = 1  // PANIC! Can't write to nil map

	// Empty map (initialized)
	emptyMap := make(map[string]int)
	fmt.Printf("Empty map: %v (is nil: %v)\n", emptyMap, emptyMap == nil)
	emptyMap["key"] = 1  // OK

	fmt.Println()
}

// demonstrateMapOperations shows common operations
func demonstrateMapOperations() {
	fmt.Println("--- Map Operations ---")

	// Different key types
	intMap := map[int]string{
		1: "one",
		2: "two",
		3: "three",
	}
	fmt.Printf("Int keys: %v\n", intMap)

	// Struct as key (must be comparable)
	type Point struct {
		X, Y int
	}

	distances := map[Point]float64{
		{0, 0}: 0.0,
		{1, 0}: 1.0,
		{0, 1}: 1.0,
		{1, 1}: 1.414,
	}
	fmt.Printf("Point map: %v\n", distances)

	// Different value types
	mixed := map[string]interface{}{
		"name":   "Alice",
		"age":    30,
		"active": true,
		"score":  95.5,
	}
	fmt.Printf("Mixed types: %v\n", mixed)

	// Map of slices
	groups := map[string][]string{
		"admins": {"alice", "bob"},
		"users":  {"carol", "david", "eve"},
		"guests": {"frank"},
	}
	fmt.Printf("Map of slices: %v\n", groups)

	// Map of maps
	config := map[string]map[string]string{
		"database": {
			"host": "localhost",
			"port": "5432",
		},
		"cache": {
			"host": "localhost",
			"port": "6379",
		},
	}
	fmt.Printf("Nested maps: %v\n", config)

	fmt.Println()
}

// demonstrateCheckingKeys shows comma-ok idiom
func demonstrateCheckingKeys() {
	fmt.Println("--- Checking Key Existence ---")

	// PHP: isset($arr['key']) or array_key_exists('key', $arr)
	// Go: value, ok := map[key]

	ages := map[string]int{
		"Alice": 30,
		"Bob":   25,
		"Zero":  0,  // Edge case: zero value
	}

	// WRONG: Just accessing (zero value if missing)
	age := ages["Alice"]
	fmt.Printf("Alice: %d (exists)\n", age)

	age = ages["Charlie"]  // Returns zero value!
	fmt.Printf("Charlie: %d (doesn't exist, but got zero value)\n", age)

	// CORRECT: Comma-ok idiom
	if age, ok := ages["Alice"]; ok {
		fmt.Printf("Alice exists: %d\n", age)
	} else {
		fmt.Println("Alice not found")
	}

	if age, ok := ages["Charlie"]; ok {
		fmt.Printf("Charlie exists: %d\n", age)
	} else {
		fmt.Println("Charlie not found")
	}

	// Important for zero values
	if age, ok := ages["Zero"]; ok {
		fmt.Printf("Zero exists with value: %d\n", age)
	} else {
		fmt.Println("Zero not found")
	}

	// Just check existence
	if _, ok := ages["Bob"]; ok {
		fmt.Println("Bob exists")
	}

	// Get with default value
	defaultAge := 18
	charlieAge := ages["Charlie"]
	if _, ok := ages["Charlie"]; !ok {
		charlieAge = defaultAge
	}
	fmt.Printf("Charlie's age (with default): %d\n", charlieAge)

	// Helper function pattern
	fmt.Printf("GetWithDefault: %d\n", getWithDefault(ages, "David", 21))

	fmt.Println()
}

// getWithDefault returns value or default if not found
func getWithDefault(m map[string]int, key string, defaultVal int) int {
	if val, ok := m[key]; ok {
		return val
	}
	return defaultVal
}

// demonstrateIterating shows map iteration
func demonstrateIterating() {
	fmt.Println("--- Iterating Maps ---")

	// PHP: foreach ($map as $key => $value) { ... }
	// Go: for key, value := range map { ... }

	scores := map[string]int{
		"Alice": 95,
		"Bob":   87,
		"Carol": 92,
		"David": 88,
	}

	// Iterate key-value pairs
	fmt.Println("Key-value pairs:")
	for name, score := range scores {
		fmt.Printf("  %s: %d\n", name, score)
	}

	// Just keys
	fmt.Println("\nJust keys:")
	for name := range scores {
		fmt.Printf("  %s\n", name)
	}

	// Just values (must use _)
	fmt.Println("\nJust values:")
	for _, score := range scores {
		fmt.Printf("  %d\n", score)
	}

	// IMPORTANT: Order is random!
	fmt.Println("\nIteration order is random:")
	for i := 0; i < 3; i++ {
		keys := []string{}
		for name := range scores {
			keys = append(keys, name)
		}
		fmt.Printf("  Run %d: %v\n", i+1, keys)
	}

	// Sorted iteration
	fmt.Println("\nSorted by key:")
	keys := make([]string, 0, len(scores))
	for name := range scores {
		keys = append(keys, name)
	}
	sort.Strings(keys)

	for _, name := range keys {
		fmt.Printf("  %s: %d\n", name, scores[name])
	}

	// Sorted by value
	type kv struct {
		key   string
		value int
	}

	var sorted []kv
	for k, v := range scores {
		sorted = append(sorted, kv{k, v})
	}

	sort.Slice(sorted, func(i, j int) bool {
		return sorted[i].value > sorted[j].value  // Descending
	})

	fmt.Println("\nSorted by score (descending):")
	for _, pair := range sorted {
		fmt.Printf("  %s: %d\n", pair.key, pair.value)
	}

	fmt.Println()
}

// demonstrateMapAsReference shows reference behavior
func demonstrateMapAsReference() {
	fmt.Println("--- Maps as References ---")

	// Maps are reference types (like PHP arrays)
	original := map[string]int{
		"a": 1,
		"b": 2,
		"c": 3,
	}
	fmt.Printf("Original: %v\n", original)

	// Assignment shares reference
	copy := original
	copy["a"] = 999

	fmt.Printf("After copy[a]=999:\n")
	fmt.Printf("  Original: %v (modified!)\n", original)
	fmt.Printf("  Copy: %v\n", copy)

	// Pass to function
	modifyMap(original)
	fmt.Printf("After modifyMap: %v\n", original)

	// To make a real copy, must iterate
	original = map[string]int{"a": 1, "b": 2, "c": 3}
	realCopy := make(map[string]int)
	for k, v := range original {
		realCopy[k] = v
	}

	realCopy["a"] = 999
	fmt.Printf("\nAfter real copy modification:\n")
	fmt.Printf("  Original: %v (unchanged)\n", original)
	fmt.Printf("  Real copy: %v\n", realCopy)

	fmt.Println()
}

func modifyMap(m map[string]int) {
	m["b"] = 888
}

// demonstrateComplexMaps shows advanced patterns
func demonstrateComplexMaps() {
	fmt.Println("--- Complex Map Patterns ---")

	// Set implementation (map[T]bool)
	// PHP: $set = ['a' => true, 'b' => true];
	set := make(map[string]bool)
	set["apple"] = true
	set["banana"] = true
	set["cherry"] = true

	fmt.Printf("Set: %v\n", set)

	// Check membership
	if set["apple"] {
		fmt.Println("Set contains apple")
	}

	if !set["orange"] {
		fmt.Println("Set doesn't contain orange")
	}

	// Add to set
	set["orange"] = true
	fmt.Printf("After add: %v\n", set)

	// Remove from set
	delete(set, "banana")
	fmt.Printf("After remove: %v\n", set)

	// Set with empty struct (saves memory)
	compactSet := make(map[string]struct{})
	compactSet["a"] = struct{}{}
	compactSet["b"] = struct{}{}

	if _, exists := compactSet["a"]; exists {
		fmt.Println("Compact set contains 'a'")
	}

	// Counting occurrences
	words := []string{"apple", "banana", "apple", "cherry", "banana", "apple"}
	counts := make(map[string]int)

	for _, word := range words {
		counts[word]++  // Zero value is 0, so this works!
	}

	fmt.Printf("\nWord counts: %v\n", counts)

	// Grouping
	type Person struct {
		Name string
		City string
	}

	people := []Person{
		{"Alice", "NYC"},
		{"Bob", "LA"},
		{"Carol", "NYC"},
		{"David", "Chicago"},
		{"Eve", "LA"},
	}

	byCity := make(map[string][]Person)
	for _, person := range people {
		byCity[person.City] = append(byCity[person.City], person)
	}

	fmt.Println("\nPeople grouped by city:")
	for city, residents := range byCity {
		fmt.Printf("  %s: %v\n", city, residents)
	}

	// Cache/memoization
	cache := make(map[int]int)
	result := fibonacci(10, cache)
	fmt.Printf("\nFibonacci(10) = %d (cache: %v)\n", result, cache)

	fmt.Println()
}

// fibonacci with memoization
func fibonacci(n int, cache map[int]int) int {
	if n <= 1 {
		return n
	}

	if val, ok := cache[n]; ok {
		return val
	}

	result := fibonacci(n-1, cache) + fibonacci(n-2, cache)
	cache[n] = result
	return result
}

// demonstratePracticalPatterns shows real-world patterns
func demonstratePracticalPatterns() {
	fmt.Println("--- Practical Patterns ---")

	// 1. Count unique elements
	numbers := []int{1, 2, 2, 3, 3, 3, 4, 4, 4, 4}
	unique := make(map[int]bool)
	for _, num := range numbers {
		unique[num] = true
	}
	fmt.Printf("Unique count: %d\n", len(unique))

	// 2. Invert map (swap keys and values)
	original := map[string]int{
		"alice": 1,
		"bob":   2,
		"carol": 3,
	}

	inverted := make(map[int]string)
	for k, v := range original {
		inverted[v] = k
	}
	fmt.Printf("Inverted: %v\n", inverted)

	// 3. Merge maps
	map1 := map[string]int{"a": 1, "b": 2}
	map2 := map[string]int{"b": 3, "c": 4}

	merged := make(map[string]int)
	for k, v := range map1 {
		merged[k] = v
	}
	for k, v := range map2 {
		merged[k] = v  // map2 overwrites map1
	}
	fmt.Printf("Merged: %v\n", merged)

	// 4. Filter map
	scores := map[string]int{
		"Alice": 95,
		"Bob":   75,
		"Carol": 92,
		"David": 68,
	}

	passing := make(map[string]int)
	for name, score := range scores {
		if score >= 70 {
			passing[name] = score
		}
	}
	fmt.Printf("Passing scores: %v\n", passing)

	// 5. Default map values
	config := map[string]string{
		"host": "localhost",
		"port": "8080",
	}

	// Get with default
	host := config["host"]
	if host == "" {
		host = "0.0.0.0"
	}

	timeout := config["timeout"]
	if timeout == "" {
		timeout = "30s"
	}

	fmt.Printf("Host: %s, Timeout: %s\n", host, timeout)

	// 6. Two-level lookup
	permissions := map[string]map[string]bool{
		"admin": {
			"read":   true,
			"write":  true,
			"delete": true,
		},
		"user": {
			"read":  true,
			"write": true,
		},
		"guest": {
			"read": true,
		},
	}

	role := "user"
	action := "write"

	if perms, ok := permissions[role]; ok {
		if allowed := perms[action]; allowed {
			fmt.Printf("%s can %s\n", role, action)
		} else {
			fmt.Printf("%s cannot %s\n", role, action)
		}
	}

	// 7. Safe concurrent access requires sync.Map or mutex
	// (Will be covered in concurrency chapter)

	fmt.Println()
}

// demonstrateMapVsStruct compares maps and structs
func demonstrateMapVsStruct() {
	fmt.Println("--- Map vs Struct ---")

	// Map: Dynamic keys, runtime flexibility
	userMap := map[string]interface{}{
		"name":  "Alice",
		"age":   30,
		"email": "alice@example.com",
	}
	fmt.Printf("Map: %v\n", userMap)

	// Can add fields dynamically
	userMap["phone"] = "555-1234"
	fmt.Printf("After add: %v\n", userMap)

	// But: No type safety, runtime errors
	// age := userMap["age"] + 1  // ERROR! interface{} doesn't support +

	// Type assertion needed
	if age, ok := userMap["age"].(int); ok {
		fmt.Printf("Age next year: %d\n", age+1)
	}

	// Struct: Fixed fields, compile-time safety
	type User struct {
		Name  string
		Age   int
		Email string
	}

	userStruct := User{
		Name:  "Alice",
		Age:   30,
		Email: "alice@example.com",
	}
	fmt.Printf("\nStruct: %+v\n", userStruct)

	// Type safe operations
	fmt.Printf("Age next year: %d\n", userStruct.Age+1)

	// Can't add fields dynamically
	// userStruct.Phone = "555-1234"  // COMPILE ERROR!

	// When to use what:
	fmt.Println("\nUse Map when:")
	fmt.Println("  - Keys are not known at compile time")
	fmt.Println("  - Need dynamic structure")
	fmt.Println("  - Implementing sets or counters")
	fmt.Println("  - Working with JSON with unknown structure")

	fmt.Println("\nUse Struct when:")
	fmt.Println("  - Fields are known at compile time")
	fmt.Println("  - Need type safety")
	fmt.Println("  - Want methods on the type")
	fmt.Println("  - Better performance")

	fmt.Println()
}

/*
Expected Output:
=== Maps Practical Guide for PHP Developers ===

--- Basic Maps ---
Ages: map[Alice:30 Bob:25 Carol:35]
Alice's age: 30
Bob's age: 25
After changes: map[Alice:31 Bob:25 Carol:35 David:28]
After delete: map[Alice:31 Carol:35 David:28]
Scores: map[english:87 math:95 science:92]
Nil map: map[] (is nil: true)
Empty map: map[] (is nil: false)

--- Map Operations ---
Int keys: map[1:one 2:two 3:three]
Point map: map[{0 0}:0 {0 1}:1 {1 0}:1 {1 1}:1.414]
Mixed types: map[active:true age:30 name:Alice score:95.5]
Map of slices: map[admins:[alice bob] guests:[frank] users:[carol david eve]]
Nested maps: map[cache:map[host:localhost port:6379] database:map[host:localhost port:5432]]

--- Checking Key Existence ---
Alice: 30 (exists)
Charlie: 0 (doesn't exist, but got zero value)
Alice exists: 30
Charlie not found
Zero exists with value: 0
Bob exists
Charlie's age (with default): 18
GetWithDefault: 21

--- Iterating Maps ---
Key-value pairs:
  Alice: 95
  Bob: 87
  Carol: 92
  David: 88

Just keys:
  Alice
  Bob
  Carol
  David

Just values:
  95
  87
  92
  88

Iteration order is random:
  Run 1: [...]
  Run 2: [...]
  Run 3: [...]

Sorted by key:
  Alice: 95
  Bob: 87
  Carol: 92
  David: 88

Sorted by score (descending):
  Alice: 95
  Carol: 92
  David: 88
  Bob: 87

--- Maps as References ---
Original: map[a:1 b:2 c:3]
After copy[a]=999:
  Original: map[a:999 b:2 c:3] (modified!)
  Copy: map[a:999 b:2 c:3]
After modifyMap: map[a:999 b:888 c:3]

After real copy modification:
  Original: map[a:1 b:2 c:3] (unchanged)
  Real copy: map[a:999 b:2 c:3]

--- Complex Map Patterns ---
Set: map[apple:true banana:true cherry:true]
Set contains apple
Set doesn't contain orange
After add: map[apple:true banana:true cherry:true orange:true]
After remove: map[apple:true cherry:true orange:true]
Compact set contains 'a'

Word counts: map[apple:3 banana:2 cherry:1]

People grouped by city:
  NYC: [{Alice NYC} {Carol NYC}]
  LA: [{Bob LA} {Eve LA}]
  Chicago: [{David Chicago}]

Fibonacci(10) = 55 (cache: map[2:1 3:2 4:3 5:5 6:8 7:13 8:21 9:34 10:55])

--- Practical Patterns ---
Unique count: 4
Inverted: map[1:alice 2:bob 3:carol]
Merged: map[a:1 b:3 c:4]
Passing scores: map[Alice:95 Bob:75 Carol:92]
Host: localhost, Timeout: 30s
user can write

--- Map vs Struct ---
Map: map[age:30 email:alice@example.com name:Alice]
After add: map[age:30 email:alice@example.com name:Alice phone:555-1234]
Age next year: 31

Struct: {Name:Alice Age:30 Email:alice@example.com}
Age next year: 31

Use Map when:
  - Keys are not known at compile time
  - Need dynamic structure
  - Implementing sets or counters
  - Working with JSON with unknown structure

Use Struct when:
  - Fields are known at compile time
  - Need type safety
  - Want methods on the type
  - Better performance
*/
