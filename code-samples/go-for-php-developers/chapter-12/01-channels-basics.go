package main

import (
	"fmt"
	"time"
)

/*
Channels Basics for PHP Developers
===================================

PHP: No direct equivalent
- Message queues (RabbitMQ, Redis)
- Inter-process communication (pipes, sockets)
- Event emitters (ReactPHP)

Go channels: Built-in communication between goroutines
	ch := make(chan int)
	ch <- 42      // Send
	value := <-ch // Receive

Channels enable:
- Safe communication between goroutines
- Synchronization
- "Don't communicate by sharing memory; share memory by communicating"

This is one of Go's most powerful features!
*/

func main() {
	fmt.Println("=== Channels Basics for PHP Developers ===\n")

	demonstrateBasicChannel()
	demonstrateSendReceive()
	demonstrateChannelBlocking()
	demonstrateChannelDirection()
	demonstrateClosingChannels()
	demonstrateRangeOverChannels()
	demonstrateSelect()
	demonstratePracticalExamples()
}

// demonstrateBasicChannel shows channel creation and basic usage
func demonstrateBasicChannel() {
	fmt.Println("--- Basic Channel ---")

	// Create channel
	ch := make(chan string)

	// Send in goroutine (must be concurrent!)
	go func() {
		ch <- "Hello from goroutine"  // Send to channel
	}()

	// Receive in main
	message := <-ch  // Receive from channel
	fmt.Printf("Received: %s\n", message)

	// Channels are typed
	intCh := make(chan int)
	go func() {
		intCh <- 42
	}()
	num := <-intCh
	fmt.Printf("Received number: %d\n", num)

	fmt.Println()
}

// demonstrateSendReceive shows send and receive operations
func demonstrateSendReceive() {
	fmt.Println("--- Send and Receive ---")

	// Send and receive are inverse operations
	ch := make(chan int)

	// Producer goroutine
	go func() {
		fmt.Println("Producer: Sending 1")
		ch <- 1
		fmt.Println("Producer: Sending 2")
		ch <- 2
		fmt.Println("Producer: Sending 3")
		ch <- 3
	}()

	// Consumer (main)
	time.Sleep(100 * time.Millisecond)
	fmt.Println("Consumer: Receiving...")
	fmt.Printf("Received: %d\n", <-ch)

	time.Sleep(100 * time.Millisecond)
	fmt.Printf("Received: %d\n", <-ch)

	time.Sleep(100 * time.Millisecond)
	fmt.Printf("Received: %d\n", <-ch)

	fmt.Println()
}

// demonstrateChannelBlocking shows blocking behavior
func demonstrateChannelBlocking() {
	fmt.Println("--- Channel Blocking ---")

	// Channels block by default (synchronous)
	ch := make(chan string)

	// This would deadlock (send blocks forever):
	// ch <- "message"  // DEADLOCK! No receiver

	// Solution: Send in goroutine
	go func() {
		fmt.Println("Goroutine: About to send")
		ch <- "message"
		fmt.Println("Goroutine: Sent!")
	}()

	// Receive blocks until data available
	fmt.Println("Main: About to receive")
	msg := <-ch
	fmt.Printf("Main: Received: %s\n", msg)

	// Synchronization example
	done := make(chan bool)

	go func() {
		fmt.Println("Worker: Starting work...")
		time.Sleep(100 * time.Millisecond)
		fmt.Println("Worker: Work done!")
		done <- true  // Signal completion
	}()

	fmt.Println("Main: Waiting for worker...")
	<-done  // Block until signal received
	fmt.Println("Main: Worker completed!")

	fmt.Println()
}

// demonstrateChannelDirection shows send-only and receive-only channels
func demonstrateChannelDirection() {
	fmt.Println("--- Channel Direction ---")

	// Bidirectional channel
	ch := make(chan int)

	// Send-only channel (can only send)
	go sender(ch)  // Automatically converts to send-only

	// Receive-only channel (can only receive)
	receiver(ch)  // Automatically converts to receive-only

	fmt.Println()
}

// sender accepts send-only channel
func sender(ch chan<- int) {
	fmt.Println("Sender: Sending values")
	for i := 1; i <= 3; i++ {
		ch <- i
		time.Sleep(50 * time.Millisecond)
	}
	close(ch)
}

// receiver accepts receive-only channel
func receiver(ch <-chan int) {
	fmt.Println("Receiver: Receiving values")
	for val := range ch {
		fmt.Printf("  Received: %d\n", val)
	}
}

// demonstrateClosingChannels shows channel closing
func demonstrateClosingChannels() {
	fmt.Println("--- Closing Channels ---")

	ch := make(chan int)

	// Producer closes channel when done
	go func() {
		for i := 1; i <= 5; i++ {
			ch <- i
		}
		close(ch)  // Signal no more values
		fmt.Println("Producer: Channel closed")
	}()

	// Consumer checks if channel is closed
	for {
		val, ok := <-ch  // ok is false if channel closed
		if !ok {
			fmt.Println("Consumer: Channel closed, exiting")
			break
		}
		fmt.Printf("Consumer: Received %d\n", val)
	}

	// Sending to closed channel panics!
	// ch <- 10  // PANIC!

	// Receiving from closed channel returns zero value
	val := <-ch
	fmt.Printf("Receive from closed channel: %d (zero value)\n", val)

	fmt.Println()
}

// demonstrateRangeOverChannels shows range loops
func demonstrateRangeOverChannels() {
	fmt.Println("--- Range Over Channels ---")

	// range automatically receives until channel closed
	ch := make(chan string)

	go func() {
		fruits := []string{"apple", "banana", "orange", "grape"}
		for _, fruit := range fruits {
			ch <- fruit
			time.Sleep(50 * time.Millisecond)
		}
		close(ch)  // Must close for range to exit!
	}()

	// Cleaner than manual loop
	for fruit := range ch {
		fmt.Printf("Received: %s\n", fruit)
	}
	fmt.Println("All fruits received")

	fmt.Println()
}

// demonstrateSelect shows select statement
func demonstrateSelect() {
	fmt.Println("--- Select Statement ---")

	// Select lets you wait on multiple channels
	ch1 := make(chan string)
	ch2 := make(chan string)

	go func() {
		time.Sleep(100 * time.Millisecond)
		ch1 <- "from channel 1"
	}()

	go func() {
		time.Sleep(50 * time.Millisecond)
		ch2 <- "from channel 2"
	}()

	// Select waits for first available channel
	for i := 0; i < 2; i++ {
		select {
		case msg1 := <-ch1:
			fmt.Printf("Received %s\n", msg1)
		case msg2 := <-ch2:
			fmt.Printf("Received %s\n", msg2)
		}
	}

	// Select with timeout
	fmt.Println("\nSelect with timeout:")
	ch3 := make(chan string)

	select {
	case msg := <-ch3:
		fmt.Printf("Received: %s\n", msg)
	case <-time.After(100 * time.Millisecond):
		fmt.Println("Timeout! No message received")
	}

	// Select with default (non-blocking)
	fmt.Println("\nSelect with default:")
	ch4 := make(chan int)

	select {
	case val := <-ch4:
		fmt.Printf("Received: %d\n", val)
	default:
		fmt.Println("No value available (non-blocking)")
	}

	fmt.Println()
}

// demonstratePracticalExamples shows real-world patterns
func demonstratePracticalExamples() {
	fmt.Println("--- Practical Examples ---")

	// Example 1: Pipeline pattern
	fmt.Println("Example 1: Pipeline")

	// Stage 1: Generate numbers
	numbers := make(chan int)
	go func() {
		for i := 1; i <= 5; i++ {
			numbers <- i
		}
		close(numbers)
	}()

	// Stage 2: Square numbers
	squares := make(chan int)
	go func() {
		for n := range numbers {
			squares <- n * n
		}
		close(squares)
	}()

	// Stage 3: Print results
	for s := range squares {
		fmt.Printf("  %d\n", s)
	}

	// Example 2: Fan-out, fan-in
	fmt.Println("\nExample 2: Fan-out, fan-in")

	jobs := make(chan int, 10)
	results := make(chan int, 10)

	// Fan-out: Multiple workers
	for w := 1; w <= 3; w++ {
		go func(id int) {
			for j := range jobs {
				fmt.Printf("  Worker %d processing %d\n", id, j)
				time.Sleep(50 * time.Millisecond)
				results <- j * 2
			}
		}(w)
	}

	// Send jobs
	for j := 1; j <= 6; j++ {
		jobs <- j
	}
	close(jobs)

	// Fan-in: Collect results
	for r := 1; r <= 6; r++ {
		fmt.Printf("  Result: %d\n", <-results)
	}

	// Example 3: Request-response pattern
	fmt.Println("\nExample 3: Request-response")

	type Request struct {
		Data     int
		Response chan int
	}

	requests := make(chan Request)

	// Server
	go func() {
		for req := range requests {
			result := req.Data * req.Data
			req.Response <- result
		}
	}()

	// Client
	for i := 1; i <= 3; i++ {
		resp := make(chan int)
		requests <- Request{Data: i, Response: resp}
		result := <-resp
		fmt.Printf("  %d² = %d\n", i, result)
	}

	close(requests)

	// Example 4: Timeout pattern
	fmt.Println("\nExample 4: Timeouts")

	result := make(chan string)

	go func() {
		time.Sleep(200 * time.Millisecond)
		result <- "completed"
	}()

	select {
	case res := <-result:
		fmt.Printf("  Success: %s\n", res)
	case <-time.After(100 * time.Millisecond):
		fmt.Println("  Timeout: operation took too long")
	}

	// Example 5: Worker pool with graceful shutdown
	fmt.Println("\nExample 5: Graceful shutdown")

	tasks := make(chan int, 5)
	done := make(chan bool)

	// Worker
	go func() {
		for {
			select {
			case task, ok := <-tasks:
				if !ok {  // Channel closed
					fmt.Println("  Worker: Shutting down")
					done <- true
					return
				}
				fmt.Printf("  Worker: Processing task %d\n", task)
				time.Sleep(50 * time.Millisecond)
			}
		}
	}()

	// Send tasks
	for i := 1; i <= 3; i++ {
		tasks <- i
	}

	// Signal shutdown
	close(tasks)

	// Wait for shutdown
	<-done
	fmt.Println("  Main: Worker shutdown complete")

	fmt.Println()
}

/*
Expected Output:
=== Channels Basics for PHP Developers ===

--- Basic Channel ---
Received: Hello from goroutine
Received number: 42

--- Send and Receive ---
Producer: Sending 1
Producer: Sending 2
Producer: Sending 3
Consumer: Receiving...
Received: 1
Received: 2
Received: 3

--- Channel Blocking ---
Goroutine: About to send
Main: About to receive
Goroutine: Sent!
Main: Received: message
Main: Waiting for worker...
Worker: Starting work...
Worker: Work done!
Main: Worker completed!

--- Channel Direction ---
Sender: Sending values
Receiver: Receiving values
  Received: 1
  Received: 2
  Received: 3

--- Closing Channels ---
Consumer: Received 1
Consumer: Received 2
Consumer: Received 3
Consumer: Received 4
Consumer: Received 5
Producer: Channel closed
Consumer: Channel closed, exiting
Receive from closed channel: 0 (zero value)

--- Range Over Channels ---
Received: apple
Received: banana
Received: orange
Received: grape
All fruits received

--- Select Statement ---
Received from channel 2
Received from channel 1

Select with timeout:
Timeout! No message received

Select with default:
No value available (non-blocking)

--- Practical Examples ---
Example 1: Pipeline
  1
  4
  9
  16
  25

Example 2: Fan-out, fan-in
  Worker 1 processing 1
  Worker 2 processing 2
  Worker 3 processing 3
  Result: 2
  Worker 1 processing 4
  Result: 4
  Worker 2 processing 5
  Result: 6
  Worker 3 processing 6
  Result: 8
  Result: 10
  Result: 12

Example 3: Request-response
  1² = 1
  2² = 4
  3² = 9

Example 4: Timeouts
  Timeout: operation took too long

Example 5: Graceful shutdown
  Worker: Processing task 1
  Worker: Processing task 2
  Worker: Processing task 3
  Worker: Shutting down
  Main: Worker shutdown complete

Note: Order of concurrent output may vary between runs!
*/
