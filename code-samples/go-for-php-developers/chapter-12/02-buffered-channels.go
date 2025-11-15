package main

import (
	"fmt"
	"time"
)

/*
Buffered Channels for PHP Developers
=====================================

PHP equivalents:
- Queue with max size (SplQueue with checks)
- Bounded message queue (RabbitMQ with limits)
- Buffered streams

Unbuffered channel:
	ch := make(chan int)  // No buffer

Buffered channel:
	ch := make(chan int, 3)  // Buffer size 3

Key differences:
- Unbuffered: Send blocks until receive (synchronous)
- Buffered: Send blocks only when buffer full (asynchronous)
- Buffer acts as queue (FIFO)
*/

func main() {
	fmt.Println("=== Buffered Channels for PHP Developers ===\n")

	demonstrateUnbufferedVsBuffered()
	demonstrateBufferBehavior()
	demonstrateBufferCapacity()
	demonstrateBufferedPipeline()
	demonstrateRateLimiting()
	demonstratePracticalPatterns()
}

// demonstrateUnbufferedVsBuffered shows the difference
func demonstrateUnbufferedVsBuffered() {
	fmt.Println("--- Unbuffered vs Buffered ---")

	// Unbuffered channel (size 0)
	fmt.Println("Unbuffered channel:")
	unbuffered := make(chan int)

	// This would deadlock (send blocks forever):
	// unbuffered <- 1  // DEADLOCK!

	// Must use goroutine
	go func() {
		fmt.Println("  Sending to unbuffered...")
		unbuffered <- 1
		fmt.Println("  Sent! (only after receive)")
	}()

	time.Sleep(50 * time.Millisecond)
	fmt.Println("  Receiving from unbuffered...")
	val := <-unbuffered
	fmt.Printf("  Received: %d\n", val)

	// Buffered channel
	fmt.Println("\nBuffered channel (size 3):")
	buffered := make(chan int, 3)

	// Can send without receiver (up to buffer size)
	fmt.Println("  Sending to buffered...")
	buffered <- 1
	fmt.Println("  Sent 1 (non-blocking)")
	buffered <- 2
	fmt.Println("  Sent 2 (non-blocking)")
	buffered <- 3
	fmt.Println("  Sent 3 (non-blocking)")

	// Fourth send would block
	// buffered <- 4  // Would block until receive

	// Receive values
	fmt.Printf("  Received: %d\n", <-buffered)
	fmt.Printf("  Received: %d\n", <-buffered)
	fmt.Printf("  Received: %d\n", <-buffered)

	fmt.Println()
}

// demonstrateBufferBehavior shows how buffer works
func demonstrateBufferBehavior() {
	fmt.Println("--- Buffer Behavior ---")

	ch := make(chan string, 2)

	fmt.Printf("Empty buffer: len=%d, cap=%d\n", len(ch), cap(ch))

	// Send 1
	ch <- "first"
	fmt.Printf("After send 1: len=%d, cap=%d\n", len(ch), cap(ch))

	// Send 2
	ch <- "second"
	fmt.Printf("After send 2: len=%d, cap=%d (buffer full)\n", len(ch), cap(ch))

	// Receive 1
	val := <-ch
	fmt.Printf("Received %q: len=%d, cap=%d\n", val, len(ch), cap(ch))

	// Send 3 (now there's space)
	ch <- "third"
	fmt.Printf("After send 3: len=%d, cap=%d\n", len(ch), cap(ch))

	// Receive all
	fmt.Printf("Received %q: len=%d\n", <-ch, len(ch))
	fmt.Printf("Received %q: len=%d\n", <-ch, len(ch))

	fmt.Println()
}

// demonstrateBufferCapacity shows capacity selection
func demonstrateBufferCapacity() {
	fmt.Println("--- Choosing Buffer Size ---")

	// Buffer size 1: Minimal buffering
	ch1 := make(chan int, 1)
	fmt.Println("Buffer size 1:")
	ch1 <- 1
	fmt.Println("  Sent 1 (OK)")
	// ch1 <- 2  // Would block
	<-ch1
	fmt.Println("  Received 1")

	// Larger buffer: More decoupling
	ch10 := make(chan int, 10)
	fmt.Println("\nBuffer size 10:")
	for i := 1; i <= 10; i++ {
		ch10 <- i
		fmt.Printf("  Sent %d (non-blocking)\n", i)
	}
	// 11th would block
	fmt.Println("  Buffer full!")

	// Drain buffer
	for i := 0; i < 10; i++ {
		<-ch10
	}
	fmt.Println("  Buffer drained")

	// Guidelines:
	fmt.Println("\nBuffer size guidelines:")
	fmt.Println("  0 (unbuffered): Synchronization, guaranteed receive")
	fmt.Println("  1: Simplest async, prevents one blocking send")
	fmt.Println("  n: Match expected burst size")
	fmt.Println("  Large: Decouple producers/consumers (use carefully!)")

	fmt.Println()
}

// demonstrateBufferedPipeline shows pipeline with buffering
func demonstrateBufferedPipeline() {
	fmt.Println("--- Buffered Pipeline ---")

	// Create pipeline with buffered channels
	numbers := make(chan int, 3)
	squares := make(chan int, 3)
	results := make(chan string, 3)

	// Stage 1: Generate numbers
	go func() {
		for i := 1; i <= 5; i++ {
			fmt.Printf("Generator: Sending %d\n", i)
			numbers <- i
			time.Sleep(50 * time.Millisecond)
		}
		close(numbers)
	}()

	// Stage 2: Square numbers
	go func() {
		for n := range numbers {
			squared := n * n
			fmt.Printf("Squarer: %d² = %d\n", n, squared)
			squares <- squared
		}
		close(squares)
	}()

	// Stage 3: Format results
	go func() {
		for s := range squares {
			result := fmt.Sprintf("Result: %d", s)
			fmt.Printf("Formatter: %s\n", result)
			results <- result
		}
		close(results)
	}()

	// Collect final results
	for r := range results {
		fmt.Printf("Final: %s\n", r)
	}

	fmt.Println()
}

// demonstrateRateLimiting shows rate limiting with buffered channels
func demonstrateRateLimiting() {
	fmt.Println("--- Rate Limiting ---")

	// Rate limiter: Allow 3 concurrent operations
	limiter := make(chan struct{}, 3)

	tasks := []int{1, 2, 3, 4, 5, 6, 7, 8, 9, 10}

	for _, task := range tasks {
		// Acquire slot (blocks if full)
		limiter <- struct{}{}

		go func(t int) {
			// Release slot when done
			defer func() { <-limiter }()

			fmt.Printf("Processing task %d\n", t)
			time.Sleep(100 * time.Millisecond)
			fmt.Printf("Task %d complete\n", t)
		}(task)
	}

	// Wait for all tasks
	for i := 0; i < cap(limiter); i++ {
		limiter <- struct{}{}
	}

	fmt.Println()
}

// demonstratePracticalPatterns shows real-world usage
func demonstratePracticalPatterns() {
	fmt.Println("--- Practical Patterns ---")

	// Pattern 1: Job queue
	fmt.Println("Pattern 1: Job Queue")

	type Job struct {
		ID   int
		Data string
	}

	jobs := make(chan Job, 5)  // Buffer allows burst of jobs

	// Producer
	go func() {
		for i := 1; i <= 10; i++ {
			job := Job{ID: i, Data: fmt.Sprintf("data-%d", i)}
			jobs <- job
			fmt.Printf("  Queued job %d\n", i)
			time.Sleep(20 * time.Millisecond)
		}
		close(jobs)
	}()

	// Consumer (slower than producer)
	for job := range jobs {
		fmt.Printf("  Processing job %d\n", job.ID)
		time.Sleep(50 * time.Millisecond)
	}
	fmt.Println("  All jobs processed")

	// Pattern 2: Request coalescing
	fmt.Println("\nPattern 2: Request Batching")

	requests := make(chan int, 10)

	// Batch processor
	go func() {
		batch := []int{}
		ticker := time.NewTicker(100 * time.Millisecond)
		defer ticker.Stop()

		for {
			select {
			case req, ok := <-requests:
				if !ok {
					if len(batch) > 0 {
						fmt.Printf("  Final batch: %v\n", batch)
					}
					return
				}
				batch = append(batch, req)
				if len(batch) >= 5 {
					fmt.Printf("  Batch (size): %v\n", batch)
					batch = []int{}
				}

			case <-ticker.C:
				if len(batch) > 0 {
					fmt.Printf("  Batch (time): %v\n", batch)
					batch = []int{}
				}
			}
		}
	}()

	// Send requests
	for i := 1; i <= 12; i++ {
		requests <- i
		time.Sleep(30 * time.Millisecond)
	}
	close(requests)

	time.Sleep(200 * time.Millisecond)

	// Pattern 3: Semaphore for resource limiting
	fmt.Println("\nPattern 3: Semaphore")

	// Limit concurrent database connections
	dbConnections := make(chan struct{}, 3)

	for i := 1; i <= 10; i++ {
		// Acquire connection
		dbConnections <- struct{}{}

		go func(id int) {
			defer func() { <-dbConnections }()  // Release

			fmt.Printf("  Query %d using connection\n", id)
			time.Sleep(50 * time.Millisecond)
			fmt.Printf("  Query %d done\n", id)
		}(i)
	}

	// Wait for all queries
	for i := 0; i < cap(dbConnections); i++ {
		dbConnections <- struct{}{}
	}

	// Pattern 4: Work stealing
	fmt.Println("\nPattern 4: Work Distribution")

	work := make(chan int, 20)

	// Fill work queue
	for i := 1; i <= 20; i++ {
		work <- i
	}
	close(work)

	// Multiple workers steal from same queue
	done := make(chan bool, 3)

	for w := 1; w <= 3; w++ {
		go func(worker int) {
			count := 0
			for task := range work {
				count++
				fmt.Printf("  Worker %d: task %d\n", worker, task)
				time.Sleep(20 * time.Millisecond)
			}
			fmt.Printf("  Worker %d processed %d tasks\n", worker, count)
			done <- true
		}(w)
	}

	// Wait for all workers
	for i := 0; i < 3; i++ {
		<-done
	}

	// Pattern 5: Drop-oldest queue
	fmt.Println("\nPattern 5: Drop-Oldest Queue")

	// When buffer full, drop oldest to make space
	dropOldest := make(chan int, 3)

	// Helper to send with drop-oldest
	send := func(val int) {
		select {
		case dropOldest <- val:
			fmt.Printf("  Queued: %d\n", val)
		default:
			// Buffer full, drop oldest
			<-dropOldest
			dropOldest <- val
			fmt.Printf("  Queued (dropped oldest): %d\n", val)
		}
	}

	// Fill buffer
	for i := 1; i <= 5; i++ {
		send(i)
	}

	// Receive all
	close(dropOldest)
	fmt.Print("  Received: ")
	for val := range dropOldest {
		fmt.Printf("%d ", val)
	}
	fmt.Println()

	fmt.Println()
}

/*
Expected Output:
=== Buffered Channels for PHP Developers ===

--- Unbuffered vs Buffered ---
Unbuffered channel:
  Sending to unbuffered...
  Receiving from unbuffered...
  Sent! (only after receive)
  Received: 1

Buffered channel (size 3):
  Sending to buffered...
  Sent 1 (non-blocking)
  Sent 2 (non-blocking)
  Sent 3 (non-blocking)
  Received: 1
  Received: 2
  Received: 3

--- Buffer Behavior ---
Empty buffer: len=0, cap=2
After send 1: len=1, cap=2
After send 2: len=2, cap=2 (buffer full)
Received "first": len=1, cap=2
After send 3: len=2, cap=2
Received "second": len=1
Received "third": len=0

--- Choosing Buffer Size ---
Buffer size 1:
  Sent 1 (OK)
  Received 1

Buffer size 10:
  Sent 1 (non-blocking)
  Sent 2 (non-blocking)
  Sent 3 (non-blocking)
  Sent 4 (non-blocking)
  Sent 5 (non-blocking)
  Sent 6 (non-blocking)
  Sent 7 (non-blocking)
  Sent 8 (non-blocking)
  Sent 9 (non-blocking)
  Sent 10 (non-blocking)
  Buffer full!
  Buffer drained

Buffer size guidelines:
  0 (unbuffered): Synchronization, guaranteed receive
  1: Simplest async, prevents one blocking send
  n: Match expected burst size
  Large: Decouple producers/consumers (use carefully!)

--- Buffered Pipeline ---
Generator: Sending 1
Squarer: 1² = 1
Formatter: Result: 1
Final: Result: 1
Generator: Sending 2
Squarer: 2² = 4
Formatter: Result: 4
Final: Result: 4
Generator: Sending 3
Squarer: 3² = 9
Formatter: Result: 9
Final: Result: 9
Generator: Sending 4
Squarer: 4² = 16
Formatter: Result: 16
Final: Result: 16
Generator: Sending 5
Squarer: 5² = 25
Formatter: Result: 25
Final: Result: 25

--- Rate Limiting ---
Processing task 1
Processing task 2
Processing task 3
Task 1 complete
Processing task 4
Task 2 complete
Processing task 5
Task 3 complete
Processing task 6
Task 4 complete
Processing task 7
Task 5 complete
Processing task 8
Task 6 complete
Processing task 9
Task 7 complete
Processing task 10
Task 8 complete
Task 9 complete
Task 10 complete

--- Practical Patterns ---
Pattern 1: Job Queue
  Queued job 1
  Processing job 1
  Queued job 2
  Queued job 3
  Processing job 2
  Queued job 4
  Queued job 5
  Processing job 3
  Queued job 6
  Queued job 7
  Processing job 4
  Queued job 8
  Queued job 9
  Processing job 5
  Queued job 10
  Processing job 6
  Processing job 7
  Processing job 8
  Processing job 9
  Processing job 10
  All jobs processed

Pattern 2: Request Batching
  Batch (size): [1 2 3 4 5]
  Batch (time): [6 7 8]
  Batch (size): [9 10 11 12]

Pattern 3: Semaphore
  Query 1 using connection
  Query 2 using connection
  Query 3 using connection
  Query 1 done
  Query 4 using connection
  Query 2 done
  Query 5 using connection
  Query 3 done
  Query 6 using connection
  Query 4 done
  Query 7 using connection
  Query 5 done
  Query 8 using connection
  Query 6 done
  Query 9 using connection
  Query 7 done
  Query 10 using connection
  Query 8 done
  Query 9 done
  Query 10 done

Pattern 4: Work Distribution
  Worker 1: task 1
  Worker 2: task 2
  Worker 3: task 3
  Worker 1: task 4
  Worker 2: task 5
  ...
  Worker 1 processed 7 tasks
  Worker 2 processed 7 tasks
  Worker 3 processed 6 tasks

Pattern 5: Drop-Oldest Queue
  Queued: 1
  Queued: 2
  Queued: 3
  Queued (dropped oldest): 4
  Queued (dropped oldest): 5
  Received: 3 4 5

Note: Order of concurrent output may vary between runs!
*/
