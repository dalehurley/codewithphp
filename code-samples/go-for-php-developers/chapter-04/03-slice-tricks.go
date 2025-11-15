package main

import (
	"fmt"
	"sort"
)

/*
Slice Tricks and Patterns for PHP Developers
=============================================

This file demonstrates advanced slice techniques that PHP developers
might accomplish with array functions like array_splice, array_merge,
array_filter, etc.

Go doesn't have as many built-in array functions, but provides
powerful primitives to build your own patterns.
*/

func main() {
	fmt.Println("=== Slice Tricks for PHP Developers ===\n")

	demonstrateAppendPatterns()
	demonstrateInsertDelete()
	demonstrateFilterMap()
	demonstrateReverseShuffle()
	demonstrateUnique()
	demonstrateStackQueue()
	demonstratePartitioning()
	demonstrateRotate()
	demonstrateBatchProcessing()
}

// demonstrateAppendPatterns shows append variations
func demonstrateAppendPatterns() {
	fmt.Println("--- Append Patterns ---")

	// PHP: array_push, array_unshift, array_merge

	s := []int{1, 2, 3}
	fmt.Printf("Original: %v\n", s)

	// Append (like array_push)
	s = append(s, 4, 5, 6)
	fmt.Printf("Append 4,5,6: %v\n", s)

	// Append slice (like array_merge)
	s = append(s, []int{7, 8, 9}...)
	fmt.Printf("Append slice: %v\n", s)

	// Prepend (like array_unshift)
	s = append([]int{0}, s...)
	fmt.Printf("Prepend 0: %v\n", s)

	// Prepend multiple
	s = append([]int{-2, -1}, s...)
	fmt.Printf("Prepend -2,-1: %v\n", s)

	// Insert in middle
	s = []int{1, 2, 5, 6}
	index := 2
	s = append(s[:index], append([]int{3, 4}, s[index:]...)...)
	fmt.Printf("Insert 3,4 at index 2: %v\n", s)

	fmt.Println()
}

// demonstrateInsertDelete shows insert/delete operations
func demonstrateInsertDelete() {
	fmt.Println("--- Insert & Delete ---")

	// PHP: array_splice, unset, array_values

	// Delete element at index
	s := []int{10, 20, 30, 40, 50}
	fmt.Printf("Original: %v\n", s)

	index := 2
	s = append(s[:index], s[index+1:]...)
	fmt.Printf("Delete index 2: %v\n", s)

	// Delete multiple elements
	s = []int{1, 2, 3, 4, 5, 6, 7, 8, 9}
	start, end := 3, 6
	s = append(s[:start], s[end:]...)
	fmt.Printf("Delete range [3:6]: %v\n", s)

	// Delete without preserving order (faster)
	s = []int{10, 20, 30, 40, 50}
	index = 2
	s[index] = s[len(s)-1]
	s = s[:len(s)-1]
	fmt.Printf("Fast delete index 2: %v\n", s)

	// Remove by value
	s = []int{10, 20, 30, 40, 30, 50}
	target := 30
	result := []int{}
	for _, v := range s {
		if v != target {
			result = append(result, v)
		}
	}
	fmt.Printf("Remove all 30s: %v\n", result)

	// Remove first occurrence
	s = []int{10, 20, 30, 40, 30, 50}
	for i, v := range s {
		if v == target {
			s = append(s[:i], s[i+1:]...)
			break
		}
	}
	fmt.Printf("Remove first 30: %v\n", s)

	// Clear slice (keep capacity)
	s = []int{1, 2, 3, 4, 5}
	s = s[:0]
	fmt.Printf("Cleared: %v (len=%d, cap=%d)\n", s, len(s), cap(s))

	fmt.Println()
}

// demonstrateFilterMap shows functional patterns
func demonstrateFilterMap() {
	fmt.Println("--- Filter & Map ---")

	// PHP: array_filter, array_map, array_reduce

	numbers := []int{1, 2, 3, 4, 5, 6, 7, 8, 9, 10}

	// Filter (like array_filter)
	evens := []int{}
	for _, n := range numbers {
		if n%2 == 0 {
			evens = append(evens, n)
		}
	}
	fmt.Printf("Evens: %v\n", evens)

	// Map (like array_map)
	squared := make([]int, len(numbers))
	for i, n := range numbers {
		squared[i] = n * n
	}
	fmt.Printf("Squared: %v\n", squared)

	// Reduce (like array_reduce)
	sum := 0
	for _, n := range numbers {
		sum += n
	}
	fmt.Printf("Sum: %d\n", sum)

	// Filter and map combined
	evenSquares := []int{}
	for _, n := range numbers {
		if n%2 == 0 {
			evenSquares = append(evenSquares, n*n)
		}
	}
	fmt.Printf("Even squares: %v\n", evenSquares)

	// Filter strings
	words := []string{"apple", "apricot", "banana", "avocado", "cherry"}
	aWords := []string{}
	for _, word := range words {
		if word[0] == 'a' {
			aWords = append(aWords, word)
		}
	}
	fmt.Printf("Words starting with 'a': %v\n", aWords)

	fmt.Println()
}

// demonstrateReverseShuffle shows reverse and shuffle
func demonstrateReverseShuffle() {
	fmt.Println("--- Reverse & Shuffle ---")

	// PHP: array_reverse, shuffle

	// Reverse in-place
	s := []int{1, 2, 3, 4, 5}
	fmt.Printf("Original: %v\n", s)

	for i, j := 0, len(s)-1; i < j; i, j = i+1, j-1 {
		s[i], s[j] = s[j], s[i]
	}
	fmt.Printf("Reversed: %v\n", s)

	// Reverse creating new slice
	original := []int{1, 2, 3, 4, 5}
	reversed := make([]int, len(original))
	for i, v := range original {
		reversed[len(original)-1-i] = v
	}
	fmt.Printf("Original: %v, Reversed copy: %v\n", original, reversed)

	// Rotate left
	s = []int{1, 2, 3, 4, 5}
	n := 2
	s = append(s[n:], s[:n]...)
	fmt.Printf("Rotate left by %d: %v\n", n, s)

	// Rotate right
	s = []int{1, 2, 3, 4, 5}
	n = 2
	s = append(s[len(s)-n:], s[:len(s)-n]...)
	fmt.Printf("Rotate right by %d: %v\n", n, s)

	fmt.Println()
}

// demonstrateUnique shows removing duplicates
func demonstrateUnique() {
	fmt.Println("--- Remove Duplicates ---")

	// PHP: array_unique

	// Preserve order
	nums := []int{1, 2, 2, 3, 1, 4, 3, 5, 4, 5}
	fmt.Printf("Original: %v\n", nums)

	seen := make(map[int]bool)
	unique := []int{}
	for _, num := range nums {
		if !seen[num] {
			seen[num] = true
			unique = append(unique, num)
		}
	}
	fmt.Printf("Unique (order preserved): %v\n", unique)

	// Don't care about order (sort first)
	nums = []int{5, 2, 3, 2, 1, 4, 3, 5, 1, 4}
	sort.Ints(nums)

	unique = []int{}
	for i, num := range nums {
		if i == 0 || num != nums[i-1] {
			unique = append(unique, num)
		}
	}
	fmt.Printf("Unique (sorted): %v\n", unique)

	// Strings
	words := []string{"apple", "banana", "apple", "cherry", "banana"}
	seenWords := make(map[string]bool)
	uniqueWords := []string{}
	for _, word := range words {
		if !seenWords[word] {
			seenWords[word] = true
			uniqueWords = append(uniqueWords, word)
		}
	}
	fmt.Printf("Unique words: %v\n", uniqueWords)

	fmt.Println()
}

// demonstrateStackQueue shows stack and queue operations
func demonstrateStackQueue() {
	fmt.Println("--- Stack & Queue ---")

	// PHP: array_push/array_pop for stack, array_shift for queue

	// Stack (LIFO)
	var stack []int
	fmt.Println("Stack operations:")

	// Push
	stack = append(stack, 1)
	stack = append(stack, 2)
	stack = append(stack, 3)
	fmt.Printf("After pushes: %v\n", stack)

	// Pop
	if len(stack) > 0 {
		top := stack[len(stack)-1]
		stack = stack[:len(stack)-1]
		fmt.Printf("Popped: %d, Stack: %v\n", top, stack)
	}

	// Peek (top without removing)
	if len(stack) > 0 {
		top := stack[len(stack)-1]
		fmt.Printf("Top: %d (stack unchanged)\n", top)
	}

	// Queue (FIFO)
	var queue []int
	fmt.Println("\nQueue operations:")

	// Enqueue
	queue = append(queue, 10)
	queue = append(queue, 20)
	queue = append(queue, 30)
	fmt.Printf("After enqueues: %v\n", queue)

	// Dequeue
	if len(queue) > 0 {
		front := queue[0]
		queue = queue[1:]
		fmt.Printf("Dequeued: %d, Queue: %v\n", front, queue)
	}

	// Peek front
	if len(queue) > 0 {
		front := queue[0]
		fmt.Printf("Front: %d (queue unchanged)\n", front)
	}

	fmt.Println()
}

// demonstratePartitioning shows partitioning patterns
func demonstratePartitioning() {
	fmt.Println("--- Partitioning ---")

	// PHP: array_chunk, custom logic

	numbers := []int{1, 2, 3, 4, 5, 6, 7, 8, 9, 10}

	// Chunk into groups
	chunkSize := 3
	fmt.Printf("Chunk into groups of %d:\n", chunkSize)
	for i := 0; i < len(numbers); i += chunkSize {
		end := i + chunkSize
		if end > len(numbers) {
			end = len(numbers)
		}
		fmt.Printf("  %v\n", numbers[i:end])
	}

	// Partition by condition (even/odd)
	evens := []int{}
	odds := []int{}
	for _, n := range numbers {
		if n%2 == 0 {
			evens = append(evens, n)
		} else {
			odds = append(odds, n)
		}
	}
	fmt.Printf("\nPartitioned by even/odd:\n")
	fmt.Printf("  Evens: %v\n", evens)
	fmt.Printf("  Odds: %v\n", odds)

	// Group by function
	words := []string{"cat", "dog", "elephant", "ant", "bear", "cow"}
	short := []string{}
	long := []string{}
	for _, word := range words {
		if len(word) <= 3 {
			short = append(short, word)
		} else {
			long = append(long, word)
		}
	}
	fmt.Printf("\nPartitioned by length:\n")
	fmt.Printf("  Short (≤3): %v\n", short)
	fmt.Printf("  Long (>3): %v\n", long)

	// Sliding window
	fmt.Println("\nSliding window (size 3):")
	for i := 0; i <= len(numbers)-3; i++ {
		window := numbers[i : i+3]
		sum := 0
		for _, n := range window {
			sum += n
		}
		fmt.Printf("  %v -> sum = %d\n", window, sum)
	}

	fmt.Println()
}

// demonstrateRotate shows rotation patterns
func demonstrateRotate() {
	fmt.Println("--- Rotation Patterns ---")

	s := []int{1, 2, 3, 4, 5, 6, 7, 8}
	fmt.Printf("Original: %v\n", s)

	// Left rotate by n
	rotateLeft := func(s []int, n int) []int {
		n = n % len(s)  // Handle n > len(s)
		return append(s[n:], s[:n]...)
	}

	fmt.Printf("Rotate left by 2: %v\n", rotateLeft(s, 2))
	fmt.Printf("Rotate left by 5: %v\n", rotateLeft(s, 5))

	// Right rotate by n
	rotateRight := func(s []int, n int) []int {
		n = n % len(s)
		return append(s[len(s)-n:], s[:len(s)-n]...)
	}

	fmt.Printf("Rotate right by 2: %v\n", rotateRight(s, 2))
	fmt.Printf("Rotate right by 5: %v\n", rotateRight(s, 5))

	// Circular shift
	fmt.Println("\nCircular iteration:")
	for i := 0; i < len(s)+3; i++ {
		idx := i % len(s)
		fmt.Printf("  i=%d -> s[%d] = %d\n", i, idx, s[idx])
	}

	fmt.Println()
}

// demonstrateBatchProcessing shows batching patterns
func demonstrateBatchProcessing() {
	fmt.Println("--- Batch Processing ---")

	data := []int{1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15}
	fmt.Printf("Data: %v\n", data)

	// Process in batches
	batchSize := 4
	fmt.Printf("\nProcessing in batches of %d:\n", batchSize)

	for i := 0; i < len(data); i += batchSize {
		end := i + batchSize
		if end > len(data) {
			end = len(data)
		}

		batch := data[i:end]
		sum := 0
		for _, v := range batch {
			sum += v
		}

		fmt.Printf("  Batch %d: %v -> sum = %d\n", i/batchSize+1, batch, sum)
	}

	// Windowed processing
	windowSize := 3
	fmt.Printf("\nWindowed processing (window size %d):\n", windowSize)

	for i := 0; i <= len(data)-windowSize; i++ {
		window := data[i : i+windowSize]
		avg := float64(0)
		for _, v := range window {
			avg += float64(v)
		}
		avg /= float64(len(window))

		fmt.Printf("  Window [%d:%d]: %v -> avg = %.2f\n", i, i+windowSize, window, avg)
	}

	// Paginate
	pageSize := 5
	totalPages := (len(data) + pageSize - 1) / pageSize
	fmt.Printf("\nPagination (page size %d, total pages %d):\n", pageSize, totalPages)

	for page := 0; page < totalPages; page++ {
		start := page * pageSize
		end := start + pageSize
		if end > len(data) {
			end = len(data)
		}

		pageData := data[start:end]
		fmt.Printf("  Page %d: %v\n", page+1, pageData)
	}

	fmt.Println()
}

/*
Expected Output:
=== Slice Tricks for PHP Developers ===

--- Append Patterns ---
Original: [1 2 3]
Append 4,5,6: [1 2 3 4 5 6]
Append slice: [1 2 3 4 5 6 7 8 9]
Prepend 0: [0 1 2 3 4 5 6 7 8 9]
Prepend -2,-1: [-2 -1 0 1 2 3 4 5 6 7 8 9]
Insert 3,4 at index 2: [1 2 3 4 5 6]

--- Insert & Delete ---
Original: [10 20 30 40 50]
Delete index 2: [10 20 40 50]
Delete range [3:6]: [1 2 3 7 8 9]
Fast delete index 2: [10 20 50 40]
Remove all 30s: [10 20 40 50]
Remove first 30: [10 20 40 30 50]
Cleared: [] (len=0, cap=5)

--- Filter & Map ---
Evens: [2 4 6 8 10]
Squared: [1 4 9 16 25 36 49 64 81 100]
Sum: 55
Even squares: [4 16 36 64 100]
Words starting with 'a': [apple apricot avocado]

--- Reverse & Shuffle ---
Original: [1 2 3 4 5]
Reversed: [5 4 3 2 1]
Original: [1 2 3 4 5], Reversed copy: [5 4 3 2 1]
Rotate left by 2: [3 4 5 1 2]
Rotate right by 2: [4 5 1 2 3]

--- Remove Duplicates ---
Original: [1 2 2 3 1 4 3 5 4 5]
Unique (order preserved): [1 2 3 4 5]
Unique (sorted): [1 2 3 4 5]
Unique words: [apple banana cherry]

--- Stack & Queue ---
Stack operations:
After pushes: [1 2 3]
Popped: 3, Stack: [1 2]
Top: 2 (stack unchanged)

Queue operations:
After enqueues: [10 20 30]
Dequeued: 10, Queue: [20 30]
Front: 20 (queue unchanged)

--- Partitioning ---
Chunk into groups of 3:
  [1 2 3]
  [4 5 6]
  [7 8 9]
  [10]

Partitioned by even/odd:
  Evens: [2 4 6 8 10]
  Odds: [1 3 5 7 9]

Partitioned by length:
  Short (≤3): [cat dog ant cow]
  Long (>3): [elephant bear]

Sliding window (size 3):
  [1 2 3] -> sum = 6
  [2 3 4] -> sum = 9
  [3 4 5] -> sum = 12
  [4 5 6] -> sum = 15
  [5 6 7] -> sum = 18
  [6 7 8] -> sum = 21
  [7 8 9] -> sum = 24
  [8 9 10] -> sum = 27

--- Rotation Patterns ---
Original: [1 2 3 4 5 6 7 8]
Rotate left by 2: [3 4 5 6 7 8 1 2]
Rotate left by 5: [6 7 8 1 2 3 4 5]
Rotate right by 2: [7 8 1 2 3 4 5 6]
Rotate right by 5: [4 5 6 7 8 1 2 3]

Circular iteration:
  i=0 -> s[0] = 1
  i=1 -> s[1] = 2
  i=2 -> s[2] = 3
  i=3 -> s[3] = 4
  i=4 -> s[4] = 5
  i=5 -> s[5] = 6
  i=6 -> s[6] = 7
  i=7 -> s[7] = 8
  i=8 -> s[0] = 1
  i=9 -> s[1] = 2
  i=10 -> s[2] = 3

--- Batch Processing ---
Data: [1 2 3 4 5 6 7 8 9 10 11 12 13 14 15]

Processing in batches of 4:
  Batch 1: [1 2 3 4] -> sum = 10
  Batch 2: [5 6 7 8] -> sum = 26
  Batch 3: [9 10 11 12] -> sum = 42
  Batch 4: [13 14 15] -> sum = 42

Windowed processing (window size 3):
  Window [0:3]: [1 2 3] -> avg = 2.00
  Window [1:4]: [2 3 4] -> avg = 3.00
  Window [2:5]: [3 4 5] -> avg = 4.00
  Window [3:6]: [4 5 6] -> avg = 5.00
  Window [4:7]: [5 6 7] -> avg = 6.00
  Window [5:8]: [6 7 8] -> avg = 7.00
  Window [6:9]: [7 8 9] -> avg = 8.00
  Window [7:10]: [8 9 10] -> avg = 9.00
  Window [8:11]: [9 10 11] -> avg = 10.00
  Window [9:12]: [10 11 12] -> avg = 11.00
  Window [10:13]: [11 12 13] -> avg = 12.00
  Window [11:14]: [12 13 14] -> avg = 13.00
  Window [12:15]: [13 14 15] -> avg = 14.00

Pagination (page size 5, total pages 3):
  Page 1: [1 2 3 4 5]
  Page 2: [6 7 8 9 10]
  Page 3: [11 12 13 14 15]
*/
