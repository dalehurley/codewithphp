package main

import (
	"fmt"
	"sort"
)

/*
Slices Deep Dive for PHP Developers
====================================

PHP arrays are dynamic and versatile:
	$arr = [1, 2, 3];
	$arr[] = 4;  // Append
	count($arr);  // Length
	array_slice($arr, 1, 2);  // Slicing

Go has two concepts:
1. Arrays: Fixed size, rarely used directly
2. Slices: Dynamic, built on arrays, used everywhere

Key differences:
- Slices are reference types (like PHP arrays)
- Must understand capacity vs length
- Slicing creates views, not copies
- append() may reallocate
*/

func main() {
	fmt.Println("=== Slices Deep Dive for PHP Developers ===\n")

	demonstrateArraysVsSlices()
	demonstrateSliceCreation()
	demonstrateLengthAndCapacity()
	demonstrateSlicing()
	demonstrateAppending()
	demonstrateCopying()
	demonstrateSliceAsReference()
	demonstrateNilVsEmptySlice()
	demonstrateMultidimensional()
	demonstratePracticalPatterns()
}

// demonstrateArraysVsSlices shows the difference
func demonstrateArraysVsSlices() {
	fmt.Println("--- Arrays vs Slices ---")

	// Array: Fixed size, value type
	// PHP equivalent: None (all arrays are dynamic)
	var arr [3]int  // Array of exactly 3 ints
	arr[0] = 1
	arr[1] = 2
	arr[2] = 3

	fmt.Printf("Array: %v (type: %T, length: %d)\n", arr, arr, len(arr))
	// arr[3] = 4  // COMPILE ERROR! Arrays have fixed size

	// Slice: Dynamic size, reference type
	// PHP equivalent: Standard array behavior
	var slice []int  // Slice of ints (no size specified)
	slice = append(slice, 1)
	slice = append(slice, 2)
	slice = append(slice, 3)
	slice = append(slice, 4)  // OK! Slices can grow

	fmt.Printf("Slice: %v (type: %T, length: %d)\n", slice, slice, len(slice))

	// Array literal
	arr2 := [3]string{"a", "b", "c"}
	fmt.Printf("Array literal: %v\n", arr2)

	// Slice literal
	slice2 := []string{"x", "y", "z"}
	fmt.Printf("Slice literal: %v\n", slice2)

	// Arrays are value types
	arr3 := arr  // Copies the array
	arr3[0] = 999
	fmt.Printf("Original array: %v\n", arr)
	fmt.Printf("Modified copy: %v\n", arr3)

	// Slices are reference types
	slice3 := slice  // Shares underlying array
	slice3[0] = 999
	fmt.Printf("Original slice: %v\n", slice)
	fmt.Printf("'Copy' (same reference): %v\n", slice3)

	fmt.Println()
}

// demonstrateSliceCreation shows different ways to create slices
func demonstrateSliceCreation() {
	fmt.Println("--- Slice Creation ---")

	// 1. Literal
	s1 := []int{1, 2, 3, 4, 5}
	fmt.Printf("Literal: %v\n", s1)

	// 2. make() with length
	s2 := make([]int, 5)  // Length 5, capacity 5, all zeros
	fmt.Printf("make([]int, 5): %v (len=%d, cap=%d)\n", s2, len(s2), cap(s2))

	// 3. make() with length and capacity
	s3 := make([]int, 3, 10)  // Length 3, capacity 10
	fmt.Printf("make([]int, 3, 10): %v (len=%d, cap=%d)\n", s3, len(s3), cap(s3))

	// 4. From array
	arr := [5]int{10, 20, 30, 40, 50}
	s4 := arr[:]  // Slice from entire array
	fmt.Printf("From array: %v\n", s4)

	// 5. nil slice
	var s5 []int  // nil slice
	fmt.Printf("nil slice: %v (len=%d, cap=%d, nil=%v)\n", s5, len(s5), cap(s5), s5 == nil)

	// 6. Empty slice
	s6 := []int{}  // Empty but not nil
	fmt.Printf("Empty slice: %v (len=%d, cap=%d, nil=%v)\n", s6, len(s6), cap(s6), s6 == nil)

	// 7. Slice of strings
	names := []string{"Alice", "Bob", "Carol"}
	fmt.Printf("String slice: %v\n", names)

	// 8. Slice of structs
	type Person struct {
		Name string
		Age  int
	}
	people := []Person{
		{"Alice", 30},
		{"Bob", 25},
		{"Carol", 35},
	}
	fmt.Printf("Struct slice: %v\n", people)

	fmt.Println()
}

// demonstrateLengthAndCapacity explains len vs cap
func demonstrateLengthAndCapacity() {
	fmt.Println("--- Length vs Capacity ---")

	// PHP: Only has count()
	// Go: Has both len() and cap()

	s := make([]int, 3, 10)
	fmt.Printf("Initial: %v\n", s)
	fmt.Printf("Length (len): %d - number of elements\n", len(s))
	fmt.Printf("Capacity (cap): %d - size of underlying array\n", cap(s))

	// Append within capacity
	s = append(s, 4)
	fmt.Printf("\nAfter append(s, 4): %v\n", s)
	fmt.Printf("Length: %d, Capacity: %d (no reallocation)\n", len(s), cap(s))

	// Keep appending
	for i := 5; i <= 10; i++ {
		s = append(s, i)
		fmt.Printf("After append(%d): len=%d, cap=%d\n", i, len(s), cap(s))
	}

	// Next append exceeds capacity - reallocation occurs
	s = append(s, 11)
	fmt.Printf("After append(11): len=%d, cap=%d (reallocated!)\n", len(s), cap(s))

	// Capacity growth pattern
	fmt.Println("\nCapacity growth pattern:")
	var growth []int
	for i := 0; i < 20; i++ {
		oldCap := cap(growth)
		growth = append(growth, i)
		newCap := cap(growth)
		if oldCap != newCap {
			fmt.Printf("Grew from %d to %d (×%.2f)\n", oldCap, newCap, float64(newCap)/float64(oldCap))
		}
	}

	fmt.Println()
}

// demonstrateSlicing shows slice operations
func demonstrateSlicing() {
	fmt.Println("--- Slicing Operations ---")

	// PHP: array_slice($arr, $start, $length)
	// Go: arr[start:end]  (end is exclusive!)

	numbers := []int{0, 1, 2, 3, 4, 5, 6, 7, 8, 9}
	fmt.Printf("Original: %v\n", numbers)

	// Basic slicing
	fmt.Printf("numbers[2:5]: %v\n", numbers[2:5])    // Elements 2, 3, 4
	fmt.Printf("numbers[:3]: %v\n", numbers[:3])      // First 3 elements
	fmt.Printf("numbers[7:]: %v\n", numbers[7:])      // From index 7 to end
	fmt.Printf("numbers[:]: %v\n", numbers[:])        // Entire slice

	// Negative indices don't work (unlike PHP)
	// numbers[-1]  // ERROR!
	// Must use: numbers[len(numbers)-1]
	fmt.Printf("Last element: %d\n", numbers[len(numbers)-1])
	fmt.Printf("Last 3: %v\n", numbers[len(numbers)-3:])

	// Three-index slice [low:high:max]
	// Specifies capacity of result
	s := numbers[2:5:7]  // Elements 2-4, capacity 5
	fmt.Printf("\nnumbers[2:5:7]: %v (len=%d, cap=%d)\n", s, len(s), cap(s))

	// Slices share underlying array
	sub := numbers[2:5]
	fmt.Printf("\nOriginal: %v\n", numbers)
	fmt.Printf("Slice [2:5]: %v\n", sub)

	sub[0] = 999  // Modifies shared array!
	fmt.Printf("After sub[0]=999:\n")
	fmt.Printf("Original: %v\n", numbers)
	fmt.Printf("Slice: %v\n", sub)

	fmt.Println()
}

// demonstrateAppending shows append operations
func demonstrateAppending() {
	fmt.Println("--- Appending Elements ---")

	// PHP: $arr[] = $value; or array_push($arr, $value);
	// Go: slice = append(slice, value)

	var nums []int
	fmt.Printf("Initial: %v\n", nums)

	// Append single element
	nums = append(nums, 1)
	fmt.Printf("After append(1): %v\n", nums)

	// Append multiple elements
	nums = append(nums, 2, 3, 4)
	fmt.Printf("After append(2,3,4): %v\n", nums)

	// Append another slice
	more := []int{5, 6, 7}
	nums = append(nums, more...)  // ... unpacks slice
	fmt.Printf("After append(more...): %v\n", nums)

	// Append to middle (insert pattern)
	index := 3
	nums = append(nums[:index], append([]int{99}, nums[index:]...)...)
	fmt.Printf("After insert 99 at index 3: %v\n", nums)

	// Prepend
	nums = append([]int{0}, nums...)
	fmt.Printf("After prepend 0: %v\n", nums)

	// Important: Must assign result!
	// append(nums, 10)  // Does nothing!
	nums = append(nums, 10)  // Correct

	fmt.Printf("Final: %v\n", nums)

	fmt.Println()
}

// demonstrateCopying shows copy operation
func demonstrateCopying() {
	fmt.Println("--- Copying Slices ---")

	// PHP: $copy = $original; (automatic copy for arrays)
	// Go: Must use copy() function

	original := []int{1, 2, 3, 4, 5}
	fmt.Printf("Original: %v\n", original)

	// WRONG: Assignment shares underlying array
	wrong := original
	wrong[0] = 999
	fmt.Printf("After wrong[0]=999:\n")
	fmt.Printf("  Original: %v (modified!)\n", original)
	fmt.Printf("  Wrong: %v\n", wrong)

	// Reset
	original = []int{1, 2, 3, 4, 5}

	// CORRECT: Use copy()
	correct := make([]int, len(original))
	copy(correct, original)
	correct[0] = 999
	fmt.Printf("\nAfter correct[0]=999:\n")
	fmt.Printf("  Original: %v (unchanged)\n", original)
	fmt.Printf("  Correct: %v\n", correct)

	// Partial copy
	dest := make([]int, 3)
	n := copy(dest, original)  // Returns number of elements copied
	fmt.Printf("\nPartial copy: %v (copied %d elements)\n", dest, n)

	// Copy to larger slice
	large := make([]int, 10)
	copy(large, original)
	fmt.Printf("Copy to larger: %v\n", large)

	// Alternative: Append to nil slice
	clone := append([]int(nil), original...)
	fmt.Printf("Clone via append: %v\n", clone)

	fmt.Println()
}

// demonstrateSliceAsReference shows reference behavior
func demonstrateSliceAsReference() {
	fmt.Println("--- Slices as References ---")

	// PHP: Arrays are values (copy on assignment unless &)
	// Go: Slices are references (always share underlying array)

	nums := []int{1, 2, 3, 4, 5}
	fmt.Printf("Original: %v\n", nums)

	// Pass to function
	modifySlice(nums)
	fmt.Printf("After modifySlice: %v (modified!)\n", nums)

	// Append in function (may or may not affect original)
	appendToSlice(nums)
	fmt.Printf("After appendToSlice: %v (unchanged)\n", nums)

	// Proper way to append
	nums = appendAndReturn(nums)
	fmt.Printf("After appendAndReturn: %v (extended)\n", nums)

	fmt.Println()
}

func modifySlice(s []int) {
	if len(s) > 0 {
		s[0] = 999  // Modifies original!
	}
}

func appendToSlice(s []int) {
	s = append(s, 100)  // Local change, doesn't affect caller
}

func appendAndReturn(s []int) []int {
	return append(s, 100)  // Correct pattern
}

// demonstrateNilVsEmptySlice shows nil vs empty slice
func demonstrateNilVsEmptySlice() {
	fmt.Println("--- Nil vs Empty Slice ---")

	// PHP: No distinction
	// Go: nil slice vs empty slice are different

	var nilSlice []int  // nil slice
	emptySlice := []int{}  // Empty slice
	madeSlice := make([]int, 0)  // Also empty

	fmt.Printf("nil slice: %v (nil=%v, len=%d, cap=%d)\n",
		nilSlice, nilSlice == nil, len(nilSlice), cap(nilSlice))
	fmt.Printf("Empty slice: %v (nil=%v, len=%d, cap=%d)\n",
		emptySlice, emptySlice == nil, len(emptySlice), cap(emptySlice))
	fmt.Printf("Made slice: %v (nil=%v, len=%d, cap=%d)\n",
		madeSlice, madeSlice == nil, len(madeSlice), cap(madeSlice))

	// Both work with append
	nilSlice = append(nilSlice, 1)
	emptySlice = append(emptySlice, 1)
	fmt.Printf("\nAfter append:\n")
	fmt.Printf("  nil slice: %v\n", nilSlice)
	fmt.Printf("  empty slice: %v\n", emptySlice)

	// Both safe to use in range
	for _, v := range nilSlice {
		fmt.Printf("Value: %d\n", v)
	}

	// Prefer nil for zero value
	// Use empty when encoding to JSON ([] vs null)

	fmt.Println()
}

// demonstrateMultidimensional shows 2D slices
func demonstrateMultidimensional() {
	fmt.Println("--- Multidimensional Slices ---")

	// PHP: $matrix = [[1,2,3], [4,5,6], [7,8,9]];
	// Go: Similar but with types

	// Create 2D slice
	matrix := [][]int{
		{1, 2, 3},
		{4, 5, 6},
		{7, 8, 9},
	}

	fmt.Println("Matrix:")
	for i, row := range matrix {
		fmt.Printf("Row %d: %v\n", i, row)
	}

	// Access elements
	fmt.Printf("\nmatrix[1][2] = %d\n", matrix[1][2])

	// Jagged array (rows can have different lengths)
	jagged := [][]int{
		{1},
		{2, 3},
		{4, 5, 6},
		{7, 8, 9, 10},
	}

	fmt.Println("\nJagged array:")
	for i, row := range jagged {
		fmt.Printf("Row %d (len=%d): %v\n", i, len(row), row)
	}

	// Create with make
	rows, cols := 3, 4
	grid := make([][]int, rows)
	for i := range grid {
		grid[i] = make([]int, cols)
	}

	// Fill grid
	val := 1
	for i := range grid {
		for j := range grid[i] {
			grid[i][j] = val
			val++
		}
	}

	fmt.Println("\nGrid:")
	for _, row := range grid {
		fmt.Printf("%v\n", row)
	}

	fmt.Println()
}

// demonstratePracticalPatterns shows common slice patterns
func demonstratePracticalPatterns() {
	fmt.Println("--- Practical Patterns ---")

	nums := []int{5, 2, 8, 1, 9, 3, 7, 4, 6}
	fmt.Printf("Original: %v\n", nums)

	// 1. Remove element at index
	index := 3
	nums = append(nums[:index], nums[index+1:]...)
	fmt.Printf("Remove index 3: %v\n", nums)

	// 2. Filter (keep even numbers)
	nums = []int{1, 2, 3, 4, 5, 6, 7, 8, 9}
	evens := []int{}
	for _, num := range nums {
		if num%2 == 0 {
			evens = append(evens, num)
		}
	}
	fmt.Printf("Evens only: %v\n", evens)

	// 3. Map (double each number)
	nums = []int{1, 2, 3, 4, 5}
	doubled := make([]int, len(nums))
	for i, num := range nums {
		doubled[i] = num * 2
	}
	fmt.Printf("Doubled: %v\n", doubled)

	// 4. Reduce (sum)
	sum := 0
	for _, num := range nums {
		sum += num
	}
	fmt.Printf("Sum: %d\n", sum)

	// 5. Reverse
	nums = []int{1, 2, 3, 4, 5}
	for i, j := 0, len(nums)-1; i < j; i, j = i+1, j-1 {
		nums[i], nums[j] = nums[j], nums[i]
	}
	fmt.Printf("Reversed: %v\n", nums)

	// 6. Sort
	nums = []int{5, 2, 8, 1, 9}
	sort.Ints(nums)
	fmt.Printf("Sorted: %v\n", nums)

	// 7. Deduplicate
	nums = []int{1, 2, 2, 3, 3, 3, 4, 4, 5}
	unique := []int{}
	seen := make(map[int]bool)
	for _, num := range nums {
		if !seen[num] {
			seen[num] = true
			unique = append(unique, num)
		}
	}
	fmt.Printf("Deduplicated: %v\n", unique)

	// 8. Find index
	nums = []int{10, 20, 30, 40, 50}
	target := 30
	foundIndex := -1
	for i, num := range nums {
		if num == target {
			foundIndex = i
			break
		}
	}
	fmt.Printf("Index of %d: %d\n", target, foundIndex)

	// 9. Chunk slice
	data := []int{1, 2, 3, 4, 5, 6, 7, 8, 9}
	chunkSize := 3
	fmt.Printf("Chunked (size %d):\n", chunkSize)
	for i := 0; i < len(data); i += chunkSize {
		end := i + chunkSize
		if end > len(data) {
			end = len(data)
		}
		fmt.Printf("  %v\n", data[i:end])
	}

	// 10. Merge slices
	a := []int{1, 2, 3}
	b := []int{4, 5, 6}
	merged := append(append([]int(nil), a...), b...)
	fmt.Printf("Merged: %v\n", merged)

	fmt.Println()
}

/*
Expected Output:
=== Slices Deep Dive for PHP Developers ===

--- Arrays vs Slices ---
Array: [1 2 3] (type: [3]int, length: 3)
Slice: [1 2 3 4] (type: []int, length: 4)
Array literal: [a b c]
Slice literal: [x y z]
Original array: [1 2 3]
Modified copy: [999 2 3]
Original slice: [999 2 3 4]
'Copy' (same reference): [999 2 3 4]

--- Slice Creation ---
Literal: [1 2 3 4 5]
make([]int, 5): [0 0 0 0 0] (len=5, cap=5)
make([]int, 3, 10): [0 0 0] (len=3, cap=10)
From array: [10 20 30 40 50]
nil slice: [] (len=0, cap=0, nil=true)
Empty slice: [] (len=0, cap=0, nil=false)
String slice: [Alice Bob Carol]
Struct slice: [{Alice 30} {Bob 25} {Carol 35}]

--- Length vs Capacity ---
Initial: [0 0 0]
Length (len): 3 - number of elements
Capacity (cap): 10 - size of underlying array

After append(s, 4): [0 0 0 4]
Length: 4, Capacity: 10 (no reallocation)
After append(5): len=5, cap=10
After append(6): len=6, cap=10
After append(7): len=7, cap=10
After append(8): len=8, cap=10
After append(9): len=9, cap=10
After append(10): len=10, cap=10
After append(11): len=11, cap=20 (reallocated!)

Capacity growth pattern:
Grew from 0 to 1 (×+Inf)
Grew from 1 to 2 (×2.00)
Grew from 2 to 4 (×2.00)
Grew from 4 to 8 (×2.00)
Grew from 8 to 16 (×2.00)
Grew from 16 to 32 (×2.00)

--- Slicing Operations ---
Original: [0 1 2 3 4 5 6 7 8 9]
numbers[2:5]: [2 3 4]
numbers[:3]: [0 1 2]
numbers[7:]: [7 8 9]
numbers[:]: [0 1 2 3 4 5 6 7 8 9]
Last element: 9
Last 3: [7 8 9]

numbers[2:5:7]: [2 3 4] (len=3, cap=5)

Original: [0 1 2 3 4 5 6 7 8 9]
Slice [2:5]: [2 3 4]
After sub[0]=999:
Original: [0 1 999 3 4 5 6 7 8 9]
Slice: [999 3 4]

--- Appending Elements ---
Initial: []
After append(1): [1]
After append(2,3,4): [1 2 3 4]
After append(more...): [1 2 3 4 5 6 7]
After insert 99 at index 3: [1 2 3 99 4 5 6 7]
After prepend 0: [0 1 2 3 99 4 5 6 7]
Final: [0 1 2 3 99 4 5 6 7 10]

--- Copying Slices ---
Original: [1 2 3 4 5]
After wrong[0]=999:
  Original: [999 2 3 4 5] (modified!)
  Wrong: [999 2 3 4 5]

After correct[0]=999:
  Original: [1 2 3 4 5] (unchanged)
  Correct: [999 2 3 4 5]

Partial copy: [1 2 3] (copied 3 elements)
Copy to larger: [1 2 3 4 5 0 0 0 0 0]
Clone via append: [1 2 3 4 5]

--- Slices as References ---
Original: [1 2 3 4 5]
After modifySlice: [999 2 3 4 5] (modified!)
After appendToSlice: [999 2 3 4 5] (unchanged)
After appendAndReturn: [999 2 3 4 5 100] (extended)

--- Nil vs Empty Slice ---
nil slice: [] (nil=true, len=0, cap=0)
Empty slice: [] (nil=false, len=0, cap=0)
Made slice: [] (nil=false, len=0, cap=0)

After append:
  nil slice: [1]
  empty slice: [1]
Value: 1

--- Multidimensional Slices ---
Matrix:
Row 0: [1 2 3]
Row 1: [4 5 6]
Row 2: [7 8 9]

matrix[1][2] = 6

Jagged array:
Row 0 (len=1): [1]
Row 1 (len=2): [2 3]
Row 2 (len=3): [4 5 6]
Row 3 (len=4): [7 8 9 10]

Grid:
[1 2 3 4]
[5 6 7 8]
[9 10 11 12]

--- Practical Patterns ---
Original: [5 2 8 1 9 3 7 4 6]
Remove index 3: [5 2 8 9 3 7 4 6]
Evens only: [2 4 6 8]
Doubled: [2 4 6 8 10]
Sum: 15
Reversed: [5 4 3 2 1]
Sorted: [1 2 5 8 9]
Deduplicated: [1 2 3 4 5]
Index of 30: 2
Chunked (size 3):
  [1 2 3]
  [4 5 6]
  [7 8 9]
Merged: [1 2 3 4 5 6]
*/
