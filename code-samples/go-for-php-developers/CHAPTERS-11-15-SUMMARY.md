# Go for PHP Developers: Chapters 11-15 Summary

## Part 3: Concurrent Programming

Master Go's most powerful feature: built-in concurrency with goroutines and channels. Learn patterns that make concurrent programming practical and safe.

## Overview

**Total Chapters**: 5 (Chapters 11-15)
**Code Files**: 20+ Go files
**Learning Time**: 2-3 weeks
**Prerequisite**: Completion of Parts 1-2 (Chapters 00-10)
**Difficulty**: Intermediate to Advanced

## Why This Matters for PHP Developers

**PHP Concurrency**:
- Process per request (blocking)
- ReactPHP (complex, event loop)
- Swoole (extension required)
- Async/await (PHP 8.1+, limited)

**Go Concurrency**:
- Built into the language
- Goroutines (lightweight threads)
- Channels (safe communication)
- Easy to use, hard to mess up
- Scales to 100,000+ concurrent operations

## What's Covered

### Chapter 11: Goroutines Fundamentals
**Goal**: Master lightweight concurrent execution

**Code Files Created**:
- `01-first-goroutine.go` - Introduction to goroutines vs PHP async
- `02-waitgroups.go` - Synchronizing goroutines

**What is a Goroutine?**

```go
// Launch a goroutine with 'go' keyword
go doSomething()

// That's it! Runs concurrently in the background
```

**vs PHP**:
```php
// PHP: No built-in concurrency
// Need ReactPHP or Swoole:
$loop = React\EventLoop\Factory::create();
$loop->futureTick(function() {
    // Runs asynchronously
});
$loop->run();

// Or spawn process:
$pid = pcntl_fork();
```

**Key Concepts**:

1. **Launching Goroutines**:
```go
// Sequential (slow)
fetchUser()
fetchPosts()
fetchComments()

// Concurrent (fast!)
go fetchUser()
go fetchPosts()
go fetchComments()
```

2. **WaitGroups** (wait for completion):
```go
var wg sync.WaitGroup

for i := 0; i < 5; i++ {
    wg.Add(1)
    go func(id int) {
        defer wg.Done()
        processJob(id)
    }(i)
}

wg.Wait() // Wait for all to complete
```

3. **Goroutine Costs**:
- Memory: ~2KB stack (vs ~2MB thread)
- Can run 100,000+ simultaneously
- Scheduled by Go runtime, not OS

**Performance Comparison**:
```
Task: Fetch 100 APIs

PHP Sequential:
- Time: 10 seconds (100 * 100ms each)
- Memory: ~30MB

PHP with Swoole:
- Time: 100ms (concurrent)
- Memory: ~50MB
- Requires extension

Go with Goroutines:
- Time: 100ms (concurrent)
- Memory: ~10MB
- Built-in, no extensions
```

---

### Chapter 12: Channels & Communication
**Goal**: Safe communication between goroutines

**Code Files Created**:
- `01-channels-basics.go` - Channel fundamentals
- `02-buffered-channels.go` - Buffered vs unbuffered

**Channels = Type-Safe Message Passing**

```go
// Create channel
messages := make(chan string)

// Send (blocks until received)
messages <- "hello"

// Receive (blocks until sent)
msg := <-messages
```

**vs PHP**:
```php
// PHP: No built-in channels
// Use message queues (Redis, RabbitMQ)
$redis->publish('channel', 'message');
$redis->subscribe(['channel'], function($message) {
    // Handle message
});
```

**Key Patterns**:

1. **Producer-Consumer**:
```go
// Producer
go func() {
    for i := 0; i < 10; i++ {
        jobs <- i
    }
    close(jobs)
}()

// Consumer
for job := range jobs {
    process(job)
}
```

2. **Unbuffered vs Buffered**:
```go
// Unbuffered (synchronous)
ch := make(chan int)
ch <- 1  // Blocks until someone receives!

// Buffered (asynchronous)
ch := make(chan int, 10)
ch <- 1  // Doesn't block (until buffer full)
```

3. **Closing Channels**:
```go
close(ch)  // Signal no more values

// Check if closed
value, ok := <-ch
if !ok {
    // Channel closed
}

// Range automatically stops on close
for value := range ch {
    // Process values
}
```

**Common Use Cases**:
- Worker pools
- Pipeline processing
- Event broadcasting
- Rate limiting
- Timeout handling

---

### Chapter 13: Select & Timeouts
**Goal**: Handle multiple channels and timeouts

**The select Statement**:

```go
select {
case msg := <-ch1:
    // Handle ch1
case msg := <-ch2:
    // Handle ch2
case <-time.After(1 * time.Second):
    // Timeout!
default:
    // Non-blocking option
}
```

**vs PHP**:
```php
// PHP: No equivalent
// Must use separate libraries
$timeout = 1.0;
$read = [$socket1, $socket2];
$write = null;
$except = null;
if (stream_select($read, $write, $except, $timeout)) {
    // Handle readable sockets
}
```

**Common Patterns**:

1. **Timeout**:
```go
select {
case result := <-ch:
    // Got result
case <-time.After(5 * time.Second):
    return errors.New("timeout")
}
```

2. **Non-blocking Send/Receive**:
```go
select {
case ch <- value:
    // Sent successfully
default:
    // Channel full, skip
}
```

3. **Multiple Channels**:
```go
for {
    select {
    case msg := <-messages:
        handleMessage(msg)
    case err := <-errors:
        handleError(err)
    case <-done:
        return
    }
}
```

---

### Chapter 14: Sync Package & Mutexes
**Goal**: Protect shared state

**When Channels Aren't Enough**:

Sometimes you need shared memory:
```go
// Shared counter (UNSAFE!)
var counter int

for i := 0; i < 1000; i++ {
    go func() {
        counter++  // Race condition!
    }()
}
```

**Solution: Mutex**:
```go
var (
    counter int
    mu      sync.Mutex
)

for i := 0; i < 1000; i++ {
    go func() {
        mu.Lock()
        counter++
        mu.Unlock()
    }()
}
```

**vs PHP**:
```php
// PHP: No race conditions (single-threaded)
$counter = 0;
$counter++;  // Always safe

// Unless using pthreads:
$mutex = new Mutex();
$mutex->lock();
$counter++;
$mutex->unlock();
```

**Sync Primitives**:

1. **Mutex** - Mutual exclusion:
```go
var mu sync.Mutex
mu.Lock()
// Critical section
mu.Unlock()
```

2. **RWMutex** - Multiple readers, one writer:
```go
var mu sync.RWMutex

// Reading
mu.RLock()
value := cache[key]
mu.RUnlock()

// Writing
mu.Lock()
cache[key] = value
mu.Unlock()
```

3. **Once** - Run exactly once:
```go
var once sync.Once

once.Do(func() {
    // Runs only once, even with multiple goroutines
    initializeDatabase()
})
```

4. **Atomic** - Lock-free operations:
```go
var counter int64

atomic.AddInt64(&counter, 1)
value := atomic.LoadInt64(&counter)
```

**Rule of Thumb**:
- Prefer channels for communication
- Use mutexes for protecting state
- "Share memory by communicating, don't communicate by sharing memory"

---

### Chapter 15: Concurrent Patterns
**Goal**: Production-ready concurrent patterns

**Essential Patterns**:

1. **Worker Pool**:
```go
// Create pool of workers
jobs := make(chan Job, 100)
results := make(chan Result, 100)

// Start workers
for w := 0; w < 10; w++ {
    go worker(jobs, results)
}

// Send jobs
for _, job := range allJobs {
    jobs <- job
}
close(jobs)

// Collect results
for r := range results {
    handleResult(r)
}
```

**vs PHP**:
```php
// Laravel Queues
dispatch(new ProcessJob($data));

// Or with Swoole
$pool = new Swoole\Process\Pool(10);
$pool->on('WorkerStart', function($pool, $workerId) {
    // Process jobs
});
```

2. **Fan-Out/Fan-In**:
```go
// Fan-out: distribute work
for i := 0; i < numWorkers; i++ {
    go processChunk(input, output)
}

// Fan-in: collect results
results := merge(output1, output2, output3)
```

3. **Pipeline**:
```go
// Stage 1: Generate
gen := func() <-chan int {
    out := make(chan int)
    go func() {
        for i := 0; i < 10; i++ {
            out <- i
        }
        close(out)
    }()
    return out
}

// Stage 2: Square
square := func(in <-chan int) <-chan int {
    out := make(chan int)
    go func() {
        for n := range in {
            out <- n * n
        }
        close(out)
    }()
    return out
}

// Chain pipeline
for n := range square(gen()) {
    fmt.Println(n)
}
```

4. **Context for Cancellation**:
```go
ctx, cancel := context.WithCancel(context.Background())
defer cancel()

go func() {
    select {
    case <-ctx.Done():
        // Cancelled, clean up
        return
    case result := <-doWork():
        // Process result
    }
}()

// Cancel all goroutines
cancel()
```

5. **Rate Limiting**:
```go
limiter := time.Tick(100 * time.Millisecond)

for request := range requests {
    <-limiter  // Wait for tick
    go handleRequest(request)
}
```

---

## Concurrency Best Practices

### Do's ✅

1. **Use channels for coordination**:
```go
done := make(chan bool)
go func() {
    doWork()
    done <- true
}()
<-done
```

2. **Always close channels (sender side)**:
```go
go func() {
    for i := 0; i < 10; i++ {
        ch <- i
    }
    close(ch)  // Signal completion
}()
```

3. **Check for closed channels**:
```go
value, ok := <-ch
if !ok {
    // Channel closed
}
```

4. **Use context for cancellation**:
```go
ctx, cancel := context.WithTimeout(context.Background(), 5*time.Second)
defer cancel()
```

5. **Limit goroutines**:
```go
// Don't spawn unlimited goroutines!
// Use worker pool pattern
```

### Don'ts ❌

1. **Don't ignore race conditions**:
```go
// Run with: go run -race yourfile.go
// Detects data races
```

2. **Don't leak goroutines**:
```go
// ❌ Bad: goroutine never stops
go func() {
    for {
        doWork()
    }
}()

// ✅ Good: can be stopped
go func() {
    for {
        select {
        case <-done:
            return
        default:
            doWork()
        }
    }
}()
```

3. **Don't send on closed channel**:
```go
close(ch)
ch <- value  // PANIC!
```

4. **Don't forget WaitGroup.Done()**:
```go
wg.Add(1)
go func() {
    defer wg.Done()  // Always defer!
    doWork()
}()
```

---

## Performance Comparison

### Scenario: Process 10,000 API requests

**PHP (Sequential)**:
```
Time: 1000 seconds (10,000 * 100ms)
Memory: 30MB
Code complexity: Simple
```

**PHP (Swoole/ReactPHP)**:
```
Time: 100ms (concurrent)
Memory: 100MB
Code complexity: Complex
Requires: Extension/library
```

**Go (Goroutines)**:
```
Time: 100ms (concurrent)
Memory: 15MB
Code complexity: Simple
Requires: Nothing (built-in)
```

**Go (Goroutines + Worker Pool)**:
```
Time: 100ms (concurrent)
Memory: 10MB (controlled)
Code complexity: Moderate
Control: Limited concurrency
```

---

## Real-World Examples

### 1. Web Scraper
```go
func scrapeWebsites(urls []string) []Result {
    results := make(chan Result, len(urls))

    for _, url := range urls {
        go func(u string) {
            results <- scrape(u)
        }(url)
    }

    var data []Result
    for range urls {
        data = append(data, <-results)
    }
    return data
}
```

### 2. Database Connection Pool
```go
type Pool struct {
    connections chan *sql.DB
}

func (p *Pool) Get() *sql.DB {
    return <-p.connections
}

func (p *Pool) Put(db *sql.DB) {
    p.connections <- db
}
```

### 3. Background Job Processor
```go
func processJobs(jobs <-chan Job) {
    for job := range jobs {
        go func(j Job) {
            if err := j.Process(); err != nil {
                log.Printf("Job failed: %v", err)
            }
        }(job)
    }
}
```

---

## Common Mistakes

1. **Starting too many goroutines**:
```go
// ❌ Could spawn millions!
for _, item := range millionItems {
    go process(item)
}

// ✅ Use worker pool
```

2. **Forgetting to wait**:
```go
// ❌ Program exits before goroutine runs
go doSomething()
// Program ends

// ✅ Wait for completion
go doSomething()
time.Sleep(1 * time.Second)  // Or use WaitGroup
```

3. **Closing channel from receiver**:
```go
// ❌ Receiver shouldn't close
go func() {
    for msg := range ch {
        process(msg)
    }
    close(ch)  // DON'T!
}()

// ✅ Sender closes
go func() {
    send(ch)
    close(ch)  // Sender closes
}()
```

---

## What's Next

After mastering Part 3:

### Part 4: Web Development (Ch 16-20)
Now you can:
- Handle thousands of concurrent HTTP requests
- Build high-performance APIs
- Process background jobs
- Create real-time applications

---

**Key Takeaway**: Concurrency is Go's superpower. Master goroutines, channels, and these patterns, and you'll build applications that scale effortlessly to handle massive concurrent loads—something very difficult in PHP.

---

*Continue to Part 4 to build concurrent web applications!*
