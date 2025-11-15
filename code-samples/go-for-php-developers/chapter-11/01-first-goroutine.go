package main

import (
	"fmt"
	"time"
)

/*
Your First Goroutine for PHP Developers
========================================

PHP concurrency options:
- Multi-process (pcntl_fork)
- Multi-threading (pthreads extension, not standard)
- Async I/O (ReactPHP, Amp, Swoole)
- Process pools (workers)

Go has goroutines (lightweight threads):
	go function()  // Run concurrently!

Goroutines are:
- Lightweight (~2KB stack vs 2MB for OS threads)
- Managed by Go runtime
- Multiplexed onto OS threads
- Started with 'go' keyword
- Similar to async/await but built into language

This chapter introduces goroutines and basic concurrency.
*/

func main() {
	fmt.Println("=== Your First Goroutine ===\n")

	demonstrateBasicGoroutine()
	demonstrateMultipleGoroutines()
	demonstrateAnonymousGoroutines()
	demonstrateConcurrentExecution()
	demonstrateGoroutineTiming()
	demonstratePracticalExamples()
}

// demonstrateBasicGoroutine shows basic goroutine syntax
func demonstrateBasicGoroutine() {
	fmt.Println("--- Basic Goroutine ---")

	// PHP: No direct equivalent, would need process/thread
	// ReactPHP: $loop->futureTick(function() { ... });
	// Go: Just add 'go' keyword!

	// Regular function call (synchronous)
	fmt.Println("Main: Starting")
	sayHello("Alice")
	fmt.Println("Main: After sayHello")

	// Goroutine (asynchronous)
	fmt.Println("\nWith goroutine:")
	fmt.Println("Main: Starting")
	go sayHello("Bob")  // Runs concurrently!
	fmt.Println("Main: After go sayHello")

	// Sleep to let goroutine complete
	time.Sleep(100 * time.Millisecond)
	fmt.Println("Main: Ending")

	fmt.Println()
}

func sayHello(name string) {
	fmt.Printf("Hello, %s!\n", name)
}

// demonstrateMultipleGoroutines shows multiple concurrent goroutines
func demonstrateMultipleGoroutines() {
	fmt.Println("--- Multiple Goroutines ---")

	// PHP: Would need multiple processes or event loop
	// Go: Just use 'go' multiple times

	fmt.Println("Main: Launching goroutines")

	// Launch 5 goroutines
	for i := 1; i <= 5; i++ {
		go printNumber(i)
	}

	// Wait for goroutines to complete
	time.Sleep(200 * time.Millisecond)
	fmt.Println("Main: All done")

	fmt.Println()
}

func printNumber(n int) {
	// Simulate some work
	time.Sleep(time.Duration(n*10) * time.Millisecond)
	fmt.Printf("Goroutine %d completed\n", n)
}

// demonstrateAnonymousGoroutines shows inline goroutines
func demonstrateAnonymousGoroutines() {
	fmt.Println("--- Anonymous Goroutines ---")

	// Can use anonymous functions with goroutines
	// Similar to PHP: $loop->futureTick(function() { ... });

	// Basic anonymous goroutine
	go func() {
		fmt.Println("Anonymous goroutine 1")
	}()

	// With parameters
	message := "Hello from goroutine"
	go func(msg string) {
		fmt.Printf("Message: %s\n", msg)
	}(message)

	// Closure capturing variables
	for i := 1; i <= 3; i++ {
		// WRONG: Captures variable reference
		// go func() {
		// 	fmt.Printf("Wrong: %d\n", i)
		// }()

		// CORRECT: Pass as parameter
		go func(n int) {
			fmt.Printf("Correct: %d\n", n)
		}(i)
	}

	time.Sleep(100 * time.Millisecond)

	fmt.Println()
}

// demonstrateConcurrentExecution shows true concurrency
func demonstrateConcurrentExecution() {
	fmt.Println("--- Concurrent Execution ---")

	// Tasks that run concurrently
	tasks := []string{"Task A", "Task B", "Task C"}

	// Sequential execution
	fmt.Println("Sequential execution:")
	start := time.Now()
	for _, task := range tasks {
		doWork(task, 200*time.Millisecond)
	}
	fmt.Printf("Sequential took: %v\n", time.Since(start))

	// Concurrent execution
	fmt.Println("\nConcurrent execution:")
	start = time.Now()
	for _, task := range tasks {
		go doWork(task, 200*time.Millisecond)
	}
	time.Sleep(300 * time.Millisecond)  // Wait for all
	fmt.Printf("Concurrent took: %v\n", time.Since(start))

	fmt.Println()
}

func doWork(name string, duration time.Duration) {
	fmt.Printf("%s: Starting\n", name)
	time.Sleep(duration)
	fmt.Printf("%s: Done\n", name)
}

// demonstrateGoroutineTiming shows timing issues
func demonstrateGoroutineTiming() {
	fmt.Println("--- Goroutine Timing Issues ---")

	// Problem: Main exits before goroutine completes
	fmt.Println("Problem - goroutine doesn't finish:")
	go func() {
		time.Sleep(100 * time.Millisecond)
		fmt.Println("This might not print!")
	}()
	// Main continues immediately
	// time.Sleep(0)  // Goroutine probably won't run

	// Solution: Wait for goroutine
	fmt.Println("\nSolution - wait for goroutine:")
	done := make(chan bool)

	go func() {
		time.Sleep(100 * time.Millisecond)
		fmt.Println("Goroutine completed!")
		done <- true  // Signal completion
	}()

	<-done  // Wait for signal
	fmt.Println("Main received signal")

	// Multiple goroutines
	fmt.Println("\nMultiple goroutines with signals:")
	signals := make(chan int)

	for i := 1; i <= 3; i++ {
		go func(n int) {
			time.Sleep(time.Duration(n*50) * time.Millisecond)
			fmt.Printf("Goroutine %d done\n", n)
			signals <- n
		}(i)
	}

	// Wait for all 3
	for i := 0; i < 3; i++ {
		sig := <-signals
		fmt.Printf("Received signal from goroutine %d\n", sig)
	}

	fmt.Println()
}

// demonstratePracticalExamples shows real-world patterns
func demonstratePracticalExamples() {
	fmt.Println("--- Practical Examples ---")

	// Example 1: Parallel data processing
	fmt.Println("Example 1: Process URLs concurrently")

	urls := []string{
		"https://api.example.com/users",
		"https://api.example.com/posts",
		"https://api.example.com/comments",
	}

	// PHP would need: curl_multi or async library
	// Go: Simple goroutines

	done := make(chan bool)
	for _, url := range urls {
		go func(u string) {
			fetchURL(u)
			done <- true
		}(url)
	}

	// Wait for all
	for range urls {
		<-done
	}
	fmt.Println("All URLs fetched")

	// Example 2: Background task
	fmt.Println("\nExample 2: Background worker")

	// Start background worker
	go backgroundWorker("Logger", 100*time.Millisecond)

	// Do main work
	for i := 1; i <= 3; i++ {
		fmt.Printf("Main: Working on item %d\n", i)
		time.Sleep(150 * time.Millisecond)
	}

	// Example 3: Timeout pattern
	fmt.Println("\nExample 3: Timeout pattern")

	result := make(chan string)

	// Long-running task
	go func() {
		time.Sleep(200 * time.Millisecond)
		result <- "Task completed"
	}()

	// Wait with timeout
	select {
	case res := <-result:
		fmt.Printf("Success: %s\n", res)
	case <-time.After(100 * time.Millisecond):
		fmt.Println("Timeout!")
	}

	// Try again with longer timeout
	go func() {
		time.Sleep(50 * time.Millisecond)
		result <- "Fast task completed"
	}()

	select {
	case res := <-result:
		fmt.Printf("Success: %s\n", res)
	case <-time.After(100 * time.Millisecond):
		fmt.Println("Timeout!")
	}

	// Example 4: Fan-out pattern
	fmt.Println("\nExample 4: Fan-out pattern")

	jobs := make(chan int, 10)
	results := make(chan int, 10)

	// Start 3 workers
	for w := 1; w <= 3; w++ {
		go worker(w, jobs, results)
	}

	// Send 9 jobs
	for j := 1; j <= 9; j++ {
		jobs <- j
	}
	close(jobs)

	// Collect results
	for r := 1; r <= 9; r++ {
		<-results
	}
	fmt.Println("All jobs processed")

	fmt.Println()
}

func fetchURL(url string) {
	fmt.Printf("Fetching: %s\n", url)
	time.Sleep(100 * time.Millisecond)  // Simulate HTTP request
	fmt.Printf("Fetched: %s\n", url)
}

func backgroundWorker(name string, interval time.Duration) {
	for i := 1; i <= 3; i++ {
		time.Sleep(interval)
		fmt.Printf("%s: Tick %d\n", name, i)
	}
}

func worker(id int, jobs <-chan int, results chan<- int) {
	for j := range jobs {
		fmt.Printf("Worker %d processing job %d\n", id, j)
		time.Sleep(50 * time.Millisecond)
		results <- j * 2
	}
}

/*
Expected Output:
=== Your First Goroutine ===

--- Basic Goroutine ---
Main: Starting
Hello, Alice!
Main: After sayHello

With goroutine:
Main: Starting
Main: After go sayHello
Hello, Bob!
Main: Ending

--- Multiple Goroutines ---
Main: Launching goroutines
Goroutine 1 completed
Goroutine 2 completed
Goroutine 3 completed
Goroutine 4 completed
Goroutine 5 completed
Main: All done

--- Anonymous Goroutines ---
Anonymous goroutine 1
Message: Hello from goroutine
Correct: 1
Correct: 2
Correct: 3

--- Concurrent Execution ---
Sequential execution:
Task A: Starting
Task A: Done
Task B: Starting
Task B: Done
Task C: Starting
Task C: Done
Sequential took: 600ms (approx)

Concurrent execution:
Task A: Starting
Task B: Starting
Task C: Starting
Task A: Done
Task B: Done
Task C: Done
Concurrent took: 300ms (approx)

--- Goroutine Timing Issues ---
Problem - goroutine doesn't finish:

Solution - wait for goroutine:
Goroutine completed!
Main received signal

Multiple goroutines with signals:
Goroutine 1 done
Received signal from goroutine 1
Goroutine 2 done
Received signal from goroutine 2
Goroutine 3 done
Received signal from goroutine 3

--- Practical Examples ---
Example 1: Process URLs concurrently
Fetching: https://api.example.com/users
Fetching: https://api.example.com/posts
Fetching: https://api.example.com/comments
Fetched: https://api.example.com/users
Fetched: https://api.example.com/posts
Fetched: https://api.example.com/comments
All URLs fetched

Example 2: Background worker
Main: Working on item 1
Logger: Tick 1
Main: Working on item 2
Logger: Tick 2
Main: Working on item 3
Logger: Tick 3

Example 3: Timeout pattern
Timeout!
Success: Fast task completed

Example 4: Fan-out pattern
Worker 1 processing job 1
Worker 2 processing job 2
Worker 3 processing job 3
Worker 1 processing job 4
Worker 2 processing job 5
Worker 3 processing job 6
Worker 1 processing job 7
Worker 2 processing job 8
Worker 3 processing job 9
All jobs processed

Note: Order of concurrent output may vary between runs!
*/
