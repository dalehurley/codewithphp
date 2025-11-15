# Chapter 12: Channels & Communication

Master Go's channels - the primary way goroutines communicate. Learn how "Don't communicate by sharing memory; share memory by communicating" makes concurrent programming safer and simpler.

## Overview

Channels are Go's way of enabling safe communication between goroutines. Unlike PHP where you'd use shared memory, databases, or message queues for inter-process communication, Go provides channels as a built-in language feature. They're type-safe, can be buffered or unbuffered, and prevent many common concurrency bugs.

## Files in This Chapter

### 1. `01-channel-basics.go`
**Topics**: Creating channels, sending, receiving, channel types

### 2. `02-buffered-channels.go`
**Topics**: Buffered vs unbuffered, capacity, blocking behavior

### 3. `03-channel-directions.go`
**Topics**: Send-only, receive-only channels, function signatures

### 4. `04-closing-channels.go`
**Topics**: Closing channels, range over channels, detecting closure

### 5. `05-channel-patterns.go`
**Topics**: Pipeline, fan-out/fan-in, worker pools with channels

### 6. `06-common-pitfalls.go`
**Topics**: Deadlocks, channel leaks, closing pitfalls

## Quick Reference

### Inter-Process Communication

**PHP**:
```php
// PHP: Use external mechanisms
// 1. Database
$redis = new Redis();
$redis->lpush('queue', json_encode($data));
$data = $redis->brpop('queue', 0);

// 2. Message Queue
$rabbit = new AMQPConnection();
$channel = $rabbit->channel();
$channel->basic_publish($message, '', 'queue');

// 3. Shared memory (with extensions)
$shm = shmop_open($key, "c", 0644, 100);
shmop_write($shm, $data, 0);
```

**Go**:
```go
// Built-in channels!
ch := make(chan string)

// Send
go func() {
    ch <- "Hello"  // Send to channel
}()

// Receive
msg := <-ch  // Receive from channel
fmt.Println(msg)

// That's it! Type-safe, no external dependencies
```

## Key Concepts

### 1. Creating Channels

```go
// Unbuffered channel
ch := make(chan int)

// Buffered channel
ch := make(chan int, 10)  // Buffer size 10

// Channel of any type
strCh := make(chan string)
userCh := make(chan *User)
errCh := make(chan error)
```

### 2. Sending and Receiving

```go
ch := make(chan int)

// Send (blocks until received)
ch <- 42

// Receive (blocks until sent)
value := <-ch

// Receive and check if channel is closed
value, ok := <-ch
if !ok {
    // Channel is closed
}

// Example
func main() {
    ch := make(chan string)

    // Sender
    go func() {
        ch <- "Hello"
    }()

    // Receiver
    msg := <-ch
    fmt.Println(msg)  // "Hello"
}
```

### 3. Buffered Channels

```go
// Unbuffered: Send blocks until receive
ch := make(chan int)
ch <- 1  // Blocks here if no receiver!

// Buffered: Send blocks only when buffer is full
ch := make(chan int, 3)
ch <- 1  // OK
ch <- 2  // OK
ch <- 3  // OK
ch <- 4  // Blocks here (buffer full)

// Buffer properties
cap(ch)  // Capacity: 3
len(ch)  // Current items: 3
```

### 4. Channel Directions

```go
// Send-only channel
func sendOnly(ch chan<- int) {
    ch <- 42        // OK
    // val := <-ch  // Compile error!
}

// Receive-only channel
func receiveOnly(ch <-chan int) {
    val := <-ch     // OK
    // ch <- 42     // Compile error!
}

// Bidirectional channel
func bidirectional(ch chan int) {
    ch <- 42        // OK
    val := <-ch     // OK
}

// Usage
ch := make(chan int)
go sendOnly(ch)      // Converts to send-only
go receiveOnly(ch)   // Converts to receive-only
```

### 5. Closing Channels

```go
ch := make(chan int, 3)

// Send values
ch <- 1
ch <- 2
ch <- 3

// Close channel
close(ch)

// Can still receive
v1 := <-ch  // 1
v2 := <-ch  // 2
v3 := <-ch  // 3
v4 := <-ch  // 0 (zero value after close)

// Check if closed
val, ok := <-ch
if !ok {
    fmt.Println("Channel closed")
}

// ❌ Can't send to closed channel (panics!)
// ch <- 4  // Panic!

// ❌ Can't close already closed channel (panics!)
// close(ch)  // Panic!
```

### 6. Range Over Channels

```go
ch := make(chan int, 5)

// Send values
go func() {
    for i := 0; i < 5; i++ {
        ch <- i
    }
    close(ch)  // Must close!
}()

// Receive with range
for val := range ch {
    fmt.Println(val)  // 0, 1, 2, 3, 4
}
// Loop exits when channel is closed
```

## Common Patterns

### 1. Worker Pool with Channels

```go
func workerPool(jobs <-chan int, results chan<- int, workers int) {
    var wg sync.WaitGroup

    // Start workers
    for w := 0; w < workers; w++ {
        wg.Add(1)

        go func(id int) {
            defer wg.Done()

            for job := range jobs {
                fmt.Printf("Worker %d processing %d\n", id, job)
                time.Sleep(time.Second)
                results <- job * 2
            }
        }(w)
    }

    wg.Wait()
    close(results)
}

// Usage
func main() {
    jobs := make(chan int, 100)
    results := make(chan int, 100)

    // Start pool
    go workerPool(jobs, results, 5)

    // Send jobs
    for j := 1; j <= 10; j++ {
        jobs <- j
    }
    close(jobs)

    // Collect results
    for result := range results {
        fmt.Println("Result:", result)
    }
}
```

### 2. Pipeline Pattern

```go
// Stage 1: Generate numbers
func generate(nums ...int) <-chan int {
    out := make(chan int)

    go func() {
        defer close(out)
        for _, n := range nums {
            out <- n
        }
    }()

    return out
}

// Stage 2: Square numbers
func square(in <-chan int) <-chan int {
    out := make(chan int)

    go func() {
        defer close(out)
        for n := range in {
            out <- n * n
        }
    }()

    return out
}

// Stage 3: Print results
func print(in <-chan int) {
    for n := range in {
        fmt.Println(n)
    }
}

// Usage: Pipeline
func main() {
    numbers := generate(1, 2, 3, 4, 5)
    squared := square(numbers)
    print(squared)
}
```

### 3. Fan-Out (Multiple Workers)

```go
func fanOut(input <-chan int, workers int) []<-chan int {
    channels := make([]<-chan int, workers)

    for i := 0; i < workers; i++ {
        ch := make(chan int)
        channels[i] = ch

        go func(out chan<- int) {
            defer close(out)

            for n := range input {
                result := expensiveOperation(n)
                out <- result
            }
        }(ch)
    }

    return channels
}
```

### 4. Fan-In (Merge Multiple Channels)

```go
func fanIn(channels ...<-chan int) <-chan int {
    var wg sync.WaitGroup
    out := make(chan int)

    // Start goroutine for each channel
    for _, ch := range channels {
        wg.Add(1)

        go func(c <-chan int) {
            defer wg.Done()
            for n := range c {
                out <- n
            }
        }(ch)
    }

    // Close output when all inputs are done
    go func() {
        wg.Wait()
        close(out)
    }()

    return out
}

// Usage
func main() {
    input := generate(1, 2, 3, 4, 5)
    workers := fanOut(input, 3)
    results := fanIn(workers...)

    for result := range results {
        fmt.Println(result)
    }
}
```

### 5. Timeout Pattern

```go
func fetchWithTimeout(url string) (string, error) {
    result := make(chan string)

    go func() {
        resp, err := http.Get(url)
        if err != nil {
            return
        }
        defer resp.Body.Close()

        body, _ := io.ReadAll(resp.Body)
        result <- string(body)
    }()

    select {
    case data := <-result:
        return data, nil
    case <-time.After(2 * time.Second):
        return "", errors.New("timeout")
    }
}
```

### 6. Done Channel Pattern

```go
func worker(done <-chan bool) {
    for {
        select {
        case <-done:
            fmt.Println("Worker stopping")
            return
        default:
            fmt.Println("Working...")
            time.Sleep(time.Second)
        }
    }
}

func main() {
    done := make(chan bool)

    go worker(done)

    time.Sleep(3 * time.Second)
    done <- true
    time.Sleep(time.Second)
}
```

## Best Practices

### 1. Sender Should Close Channels

```go
// ✅ Good: Sender closes
func sender(ch chan<- int) {
    for i := 0; i < 5; i++ {
        ch <- i
    }
    close(ch)  // Sender closes
}

// ❌ Bad: Receiver closes (dangerous!)
func receiver(ch <-chan int) {
    for val := range ch {
        fmt.Println(val)
    }
    // Don't close here!
}
```

### 2. Use Buffered Channels for Known Capacity

```go
// ✅ Good: Buffered for known size
results := make(chan Result, numWorkers)

// ✅ Good: Prevents goroutine blocking
errors := make(chan error, 1)  // Buffer 1 for single error
```

### 3. Don't Close Receive-Only Channels

```go
// ✅ Good
func produce(ch chan<- int) {
    ch <- 42
    close(ch)  // OK
}

// ❌ Bad: Can't close receive-only
func consume(ch <-chan int) {
    // close(ch)  // Compile error!
}
```

### 4. Check for Closed Channels

```go
// ✅ Good: Check if closed
val, ok := <-ch
if !ok {
    // Channel closed
    return
}

// ✅ Good: Range handles closure
for val := range ch {
    process(val)
}
```

### 5. Use Select for Multiple Channels

```go
// ✅ Good: Select statement
select {
case msg1 := <-ch1:
    fmt.Println("Received from ch1:", msg1)
case msg2 := <-ch2:
    fmt.Println("Received from ch2:", msg2)
case <-time.After(time.Second):
    fmt.Println("Timeout")
}
```

## Common Mistakes

### 1. Deadlock - Send Without Receiver

```go
// ❌ Deadlock!
func deadlock() {
    ch := make(chan int)
    ch <- 42  // Blocks forever (no receiver)
}

// ✅ Fix: Use goroutine
func noDeadlock() {
    ch := make(chan int)

    go func() {
        ch <- 42
    }()

    val := <-ch
}

// ✅ Or use buffered channel
func buffered() {
    ch := make(chan int, 1)
    ch <- 42  // Doesn't block
    val := <-ch
}
```

### 2. Closing Channel Multiple Times

```go
// ❌ Panic!
ch := make(chan int)
close(ch)
close(ch)  // Panic: close of closed channel

// ✅ Use sync.Once
var once sync.Once
closeChannel := func() {
    once.Do(func() {
        close(ch)
    })
}
```

### 3. Sending to Closed Channel

```go
// ❌ Panic!
ch := make(chan int)
close(ch)
ch <- 42  // Panic: send on closed channel

// ✅ Check before sending
select {
case ch <- value:
    // Sent successfully
default:
    // Channel closed or full
}
```

### 4. Not Closing Channels (Goroutine Leak)

```go
// ❌ Goroutine leak!
func leak() {
    ch := make(chan int)

    go func() {
        for val := range ch {  // Waits forever
            fmt.Println(val)
        }
    }()
    // ch never closed, goroutine blocked forever
}

// ✅ Always close channels
func noLeak() {
    ch := make(chan int)

    go func() {
        for val := range ch {
            fmt.Println(val)
        }
    }()

    ch <- 1
    ch <- 2
    close(ch)  // Goroutine can exit
}
```

### 5. Wrong Buffer Size

```go
// ❌ Too small (causes blocking)
ch := make(chan int, 1)
ch <- 1
ch <- 2  // Blocks!

// ❌ Too large (wastes memory)
ch := make(chan int, 1000000)

// ✅ Right size for use case
results := make(chan Result, numWorkers)
```

## Advanced Patterns

### 1. Semaphore Pattern

```go
// Limit concurrent operations
func semaphore(maxConcurrent int) {
    sem := make(chan struct{}, maxConcurrent)

    for i := 0; i < 100; i++ {
        sem <- struct{}{}  // Acquire

        go func(id int) {
            defer func() { <-sem }()  // Release

            // Do work (max maxConcurrent concurrent)
            time.Sleep(time.Second)
            fmt.Println("Task", id, "done")
        }(i)
    }

    // Wait for all
    for i := 0; i < maxConcurrent; i++ {
        sem <- struct{}{}
    }
}
```

### 2. Request-Response Pattern

```go
type Request struct {
    Data     string
    Response chan<- string
}

func server(requests <-chan Request) {
    for req := range requests {
        // Process request
        result := process(req.Data)

        // Send response
        req.Response <- result
    }
}

// Usage
func main() {
    requests := make(chan Request)

    go server(requests)

    // Make request
    response := make(chan string)
    requests <- Request{
        Data:     "hello",
        Response: response,
    }

    // Get response
    result := <-response
    fmt.Println(result)
}
```

### 3. Or-Channel (First Result Wins)

```go
func orChannel(channels ...<-chan interface{}) <-chan interface{} {
    switch len(channels) {
    case 0:
        return nil
    case 1:
        return channels[0]
    }

    orDone := make(chan interface{})

    go func() {
        defer close(orDone)

        switch len(channels) {
        case 2:
            select {
            case <-channels[0]:
            case <-channels[1]:
            }
        default:
            select {
            case <-channels[0]:
            case <-channels[1]:
            case <-channels[2]:
            case <-orChannel(append(channels[3:], orDone)...):
            }
        }
    }()

    return orDone
}
```

### 4. Broadcast Pattern

```go
type Broadcaster struct {
    listeners []chan<- string
    mu        sync.Mutex
}

func (b *Broadcaster) Subscribe() <-chan string {
    ch := make(chan string, 10)

    b.mu.Lock()
    b.listeners = append(b.listeners, ch)
    b.mu.Unlock()

    return ch
}

func (b *Broadcaster) Broadcast(msg string) {
    b.mu.Lock()
    defer b.mu.Unlock()

    for _, ch := range b.listeners {
        ch <- msg
    }
}
```

## Comparison with PHP

| Feature | PHP | Go |
|---------|-----|-----|
| Communication | Redis, RabbitMQ, DB | Channels (built-in) |
| Type safety | Manual serialization | Type-safe channels |
| Setup | External services | No setup needed |
| Buffering | Queue-dependent | Configurable buffer |
| Direction control | No | Send/receive-only |
| Closing | N/A | Built-in close() |
| Range iteration | N/A | Built-in range |

## Next Steps

- **Chapter 13**: Select & Timeouts - Advanced channel operations
- **Chapter 14**: Sync Package & Mutexes - When not to use channels
- **Chapter 15**: Concurrent Patterns - Real-world patterns combining goroutines and channels

---

**Key Takeaway**: Channels are Go's primary communication mechanism between goroutines. They're type-safe, built into the language, and prevent many concurrency bugs. Remember: "Don't communicate by sharing memory; share memory by communicating." Use channels to pass data, not to share state.
