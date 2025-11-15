package main

import (
	"fmt"
	"sync"
	"time"
)

/*
WaitGroups for PHP Developers
==============================

PHP: No built-in equivalent for waiting on multiple async operations
- Would use promise libraries (ReactPHP, Amp)
- Or track processes manually with pcntl_wait

Go sync.WaitGroup:
	var wg sync.WaitGroup
	wg.Add(1)  // Add counter
	go func() {
	    defer wg.Done()  // Decrement when done
	    // Work here
	}()
	wg.Wait()  // Wait for counter to reach zero

WaitGroup is the idiomatic way to wait for multiple goroutines.
*/

func main() {
	fmt.Println("=== WaitGroups for PHP Developers ===\n")

	demonstrateBasicWaitGroup()
	demonstrateMultipleGoroutines()
	demonstrateWaitGroupPattern()
	demonstrateCommonMistakes()
	demonstrateAdvancedPatterns()
	demonstratePracticalExamples()
}

// demonstrateBasicWaitGroup shows basic WaitGroup usage
func demonstrateBasicWaitGroup() {
	fmt.Println("--- Basic WaitGroup ---")

	// Without WaitGroup (wrong!)
	fmt.Println("Without WaitGroup:")
	go func() {
		time.Sleep(100 * time.Millisecond)
		fmt.Println("  Goroutine finished (might not see this!)")
	}()
	// Main exits immediately, goroutine might not finish

	// With WaitGroup (correct!)
	fmt.Println("\nWith WaitGroup:")
	var wg sync.WaitGroup

	wg.Add(1)  // Increment counter
	go func() {
		defer wg.Done()  // Decrement counter when done
		time.Sleep(100 * time.Millisecond)
		fmt.Println("  Goroutine finished!")
	}()

	wg.Wait()  // Wait for counter to reach 0
	fmt.Println("All goroutines complete")

	fmt.Println()
}

// demonstrateMultipleGoroutines shows waiting for multiple goroutines
func demonstrateMultipleGoroutines() {
	fmt.Println("--- Multiple Goroutines ---")

	var wg sync.WaitGroup

	// Launch 5 goroutines
	for i := 1; i <= 5; i++ {
		wg.Add(1)  // Increment for each goroutine

		go func(id int) {
			defer wg.Done()  // Decrement when done

			duration := time.Duration(id*50) * time.Millisecond
			time.Sleep(duration)
			fmt.Printf("Goroutine %d completed\n", id)
		}(i)
	}

	fmt.Println("Waiting for all goroutines...")
	wg.Wait()
	fmt.Println("All goroutines complete!")

	fmt.Println()
}

// demonstrateWaitGroupPattern shows idiomatic pattern
func demonstrateWaitGroupPattern() {
	fmt.Println("--- Idiomatic Pattern ---")

	// Pattern 1: Add before launching
	var wg sync.WaitGroup

	tasks := []string{"Task A", "Task B", "Task C"}

	for _, task := range tasks {
		wg.Add(1)  // Add before go
		go processTask(&wg, task)
	}

	wg.Wait()
	fmt.Println("All tasks processed")

	// Pattern 2: Add in bulk
	fmt.Println("\nBulk add:")
	wg.Add(3)  // Add 3 at once

	go worker(&wg, 1)
	go worker(&wg, 2)
	go worker(&wg, 3)

	wg.Wait()
	fmt.Println("All workers done")

	fmt.Println()
}

func processTask(wg *sync.WaitGroup, name string) {
	defer wg.Done()
	fmt.Printf("%s: Processing...\n", name)
	time.Sleep(100 * time.Millisecond)
	fmt.Printf("%s: Done\n", name)
}

func worker(wg *sync.WaitGroup, id int) {
	defer wg.Done()
	fmt.Printf("Worker %d working\n", id)
	time.Sleep(50 * time.Millisecond)
	fmt.Printf("Worker %d done\n", id)
}

// demonstrateCommonMistakes shows what NOT to do
func demonstrateCommonMistakes() {
	fmt.Println("--- Common Mistakes ---")

	// Mistake 1: Add inside goroutine (race condition!)
	fmt.Println("Don't add inside goroutine:")
	var wg sync.WaitGroup

	// WRONG:
	// for i := 0; i < 3; i++ {
	//     go func() {
	//         wg.Add(1)  // RACE! Might happen after Wait()
	//         defer wg.Done()
	//         // work
	//     }()
	// }

	// CORRECT:
	for i := 0; i < 3; i++ {
		wg.Add(1)  // Add before launching goroutine
		go func(n int) {
			defer wg.Done()
			fmt.Printf("  Correct goroutine %d\n", n)
		}(i)
	}

	wg.Wait()

	// Mistake 2: Forgetting Done() (deadlock!)
	fmt.Println("\nAlways use defer wg.Done():")
	wg.Add(1)
	go func() {
		defer wg.Done()  // Ensures Done() is called even on panic
		fmt.Println("  Work with deferred Done()")
		// Even if panic occurs, Done() will be called
	}()

	wg.Wait()

	// Mistake 3: Calling Wait() multiple times
	fmt.Println("\nWait() can be called multiple times:")
	wg.Add(1)
	go func() {
		defer wg.Done()
		time.Sleep(50 * time.Millisecond)
		fmt.Println("  Goroutine done")
	}()

	wg.Wait()  // First wait
	fmt.Println("  First wait complete")
	wg.Wait()  // Second wait (returns immediately)
	fmt.Println("  Second wait complete (immediate)")

	fmt.Println()
}

// demonstrateAdvancedPatterns shows advanced usage
func demonstrateAdvancedPatterns() {
	fmt.Println("--- Advanced Patterns ---")

	// Pattern 1: WaitGroup in struct
	type JobProcessor struct {
		wg sync.WaitGroup
	}

	func(jp *JobProcessor) Process(id int) {
		jp.wg.Add(1)
		go func() {
			defer jp.wg.Done()
			fmt.Printf("Processing job %d\n", id)
			time.Sleep(50 * time.Millisecond)
		}()
	}

	func(jp *JobProcessor) Wait() {
		jp.wg.Wait()
	}

	processor := &JobProcessor{}
	for i := 1; i <= 3; i++ {
		processor.Process(i)
	}
	processor.Wait()
	fmt.Println("All jobs processed")

	// Pattern 2: Nested WaitGroups
	fmt.Println("\nNested WaitGroups:")
	var outerWg sync.WaitGroup

	for i := 1; i <= 2; i++ {
		outerWg.Add(1)
		go func(group int) {
			defer outerWg.Done()

			var innerWg sync.WaitGroup
			for j := 1; j <= 3; j++ {
				innerWg.Add(1)
				go func(task int) {
					defer innerWg.Done()
					fmt.Printf("Group %d, Task %d\n", group, task)
					time.Sleep(20 * time.Millisecond)
				}(j)
			}
			innerWg.Wait()
			fmt.Printf("Group %d complete\n", group)
		}(i)
	}

	outerWg.Wait()
	fmt.Println("All groups complete")

	// Pattern 3: Limited concurrency
	fmt.Println("\nLimited concurrency (semaphore pattern):")
	var wg sync.WaitGroup
	semaphore := make(chan struct{}, 2)  // Max 2 concurrent

	for i := 1; i <= 6; i++ {
		wg.Add(1)
		go func(id int) {
			defer wg.Done()

			semaphore <- struct{}{}  // Acquire
			defer func() { <-semaphore }()  // Release

			fmt.Printf("Worker %d started\n", id)
			time.Sleep(100 * time.Millisecond)
			fmt.Printf("Worker %d done\n", id)
		}(i)
	}

	wg.Wait()
	fmt.Println("All work complete")

	fmt.Println()
}

// demonstratePracticalExamples shows real-world usage
func demonstratePracticalExamples() {
	fmt.Println("--- Practical Examples ---")

	// Example 1: Parallel file processing
	fmt.Println("Example 1: Process files concurrently")

	files := []string{"file1.txt", "file2.txt", "file3.txt", "file4.txt"}
	var wg sync.WaitGroup

	for _, file := range files {
		wg.Add(1)
		go func(filename string) {
			defer wg.Done()
			processFile(filename)
		}(file)
	}

	wg.Wait()
	fmt.Println("All files processed")

	// Example 2: Parallel API calls
	fmt.Println("\nExample 2: Fetch multiple APIs")

	type Result struct {
		URL  string
		Data string
		Err  error
	}

	urls := []string{
		"https://api.example.com/users",
		"https://api.example.com/posts",
		"https://api.example.com/comments",
	}

	results := make([]Result, len(urls))

	for i, url := range urls {
		wg.Add(1)
		go func(index int, u string) {
			defer wg.Done()
			data, err := fetchURL(u)
			results[index] = Result{URL: u, Data: data, Err: err}
		}(i, url)
	}

	wg.Wait()

	fmt.Println("Results:")
	for _, res := range results {
		if res.Err != nil {
			fmt.Printf("  %s: ERROR - %v\n", res.URL, res.Err)
		} else {
			fmt.Printf("  %s: %s\n", res.URL, res.Data)
		}
	}

	// Example 3: Worker pool
	fmt.Println("\nExample 3: Worker pool")

	jobs := make(chan int, 10)
	results2 := make(chan int, 10)

	// Start workers
	numWorkers := 3
	for w := 1; w <= numWorkers; w++ {
		wg.Add(1)
		go poolWorker(&wg, w, jobs, results2)
	}

	// Send jobs
	for j := 1; j <= 9; j++ {
		jobs <- j
	}
	close(jobs)

	// Wait for workers to finish
	wg.Wait()
	close(results2)

	// Collect results
	fmt.Println("Results:")
	for r := range results2 {
		fmt.Printf("  %d\n", r)
	}

	// Example 4: Error group pattern
	fmt.Println("\nExample 4: Stop on first error")

	errChan := make(chan error, 3)
	var errorWg sync.WaitGroup

	tasks2 := []struct {
		name    string
		willErr bool
	}{
		{"Task 1", false},
		{"Task 2", true},  // This will error
		{"Task 3", false},
	}

	for _, task := range tasks2 {
		errorWg.Add(1)
		go func(t struct {
			name    string
			willErr bool
		}) {
			defer errorWg.Done()

			time.Sleep(50 * time.Millisecond)
			if t.willErr {
				errChan <- fmt.Errorf("%s failed", t.name)
				return
			}
			fmt.Printf("  %s succeeded\n", t.name)
		}(task)
	}

	// Wait in goroutine
	go func() {
		errorWg.Wait()
		close(errChan)
	}()

	// Check for errors
	for err := range errChan {
		if err != nil {
			fmt.Printf("  Error: %v\n", err)
			// In real code, might cancel other tasks here
		}
	}

	fmt.Println()
}

func processFile(filename string) {
	fmt.Printf("Processing %s...\n", filename)
	time.Sleep(50 * time.Millisecond)
	fmt.Printf("Processed %s\n", filename)
}

func fetchURL(url string) (string, error) {
	time.Sleep(100 * time.Millisecond)
	return fmt.Sprintf("Data from %s", url), nil
}

func poolWorker(wg *sync.WaitGroup, id int, jobs <-chan int, results chan<- int) {
	defer wg.Done()

	for j := range jobs {
		fmt.Printf("Worker %d processing job %d\n", id, j)
		time.Sleep(50 * time.Millisecond)
		results <- j * 2
	}
}

/*
Expected Output:
=== WaitGroups for PHP Developers ===

--- Basic WaitGroup ---
Without WaitGroup:

With WaitGroup:
  Goroutine finished!
All goroutines complete

--- Multiple Goroutines ---
Waiting for all goroutines...
Goroutine 1 completed
Goroutine 2 completed
Goroutine 3 completed
Goroutine 4 completed
Goroutine 5 completed
All goroutines complete!

--- Idiomatic Pattern ---
Task A: Processing...
Task B: Processing...
Task C: Processing...
Task A: Done
Task B: Done
Task C: Done
All tasks processed

Bulk add:
Worker 1 working
Worker 2 working
Worker 3 working
Worker 1 done
Worker 2 done
Worker 3 done
All workers done

--- Common Mistakes ---
Don't add inside goroutine:
  Correct goroutine 0
  Correct goroutine 1
  Correct goroutine 2

Always use defer wg.Done():
  Work with deferred Done()

Wait() can be called multiple times:
  Goroutine done
  First wait complete
  Second wait complete (immediate)

--- Advanced Patterns ---
Processing job 1
Processing job 2
Processing job 3
All jobs processed

Nested WaitGroups:
Group 1, Task 1
Group 1, Task 2
Group 1, Task 3
Group 2, Task 1
Group 2, Task 2
Group 2, Task 3
Group 1 complete
Group 2 complete
All groups complete

Limited concurrency (semaphore pattern):
Worker 1 started
Worker 2 started
Worker 1 done
Worker 3 started
Worker 2 done
Worker 4 started
Worker 3 done
Worker 5 started
Worker 4 done
Worker 6 started
Worker 5 done
Worker 6 done
All work complete

--- Practical Examples ---
Example 1: Process files concurrently
Processing file1.txt...
Processing file2.txt...
Processing file3.txt...
Processing file4.txt...
Processed file1.txt
Processed file2.txt
Processed file3.txt
Processed file4.txt
All files processed

Example 2: Fetch multiple APIs
Results:
  https://api.example.com/users: Data from https://api.example.com/users
  https://api.example.com/posts: Data from https://api.example.com/posts
  https://api.example.com/comments: Data from https://api.example.com/comments

Example 3: Worker pool
Worker 1 processing job 1
Worker 2 processing job 2
Worker 3 processing job 3
Worker 1 processing job 4
Worker 2 processing job 5
Worker 3 processing job 6
Worker 1 processing job 7
Worker 2 processing job 8
Worker 3 processing job 9
Results:
  2
  4
  6
  8
  10
  12
  14
  16
  18

Example 4: Stop on first error
  Task 1 succeeded
  Task 3 succeeded
  Error: Task 2 failed

Note: Order of concurrent output may vary between runs!
*/
