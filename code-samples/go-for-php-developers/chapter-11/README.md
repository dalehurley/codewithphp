# Chapter 11: Goroutines Fundamentals

Discover Go's most powerful feature: goroutines. Learn how to run thousands of concurrent tasks with minimal overhead - something nearly impossible in traditional PHP.

## Overview

Goroutines are lightweight threads managed by the Go runtime. While PHP handles concurrency through processes or extensions like ReactPHP, Go makes concurrent programming a first-class language feature. A single Go program can run thousands or millions of goroutines efficiently.

## Files in This Chapter

### 1. `01-goroutine-basics.go`
**Topics**: Creating goroutines, go keyword, basic concurrency

### 2. `02-goroutine-vs-threads.go`
**Topics**: Goroutines vs OS threads, memory footprint, scheduling

### 3. `03-anonymous-goroutines.go`
**Topics**: Inline goroutines, closures, variable capture

### 4. `04-waiting-goroutines.go`
**Topics**: WaitGroups, coordinating goroutines, synchronization

### 5. `05-goroutine-leaks.go`
**Topics**: Common mistakes, memory leaks, proper cleanup

### 6. `06-practical-examples.go`
**Topics**: Real-world goroutine patterns, fan-out, fan-in

## Quick Reference

### Creating Concurrent Tasks

**PHP**:
```php
// PHP is synchronous by default
function fetchData($url) {
    return file_get_contents($url);
}

// Sequential execution (slow!)
$data1 = fetchData('http://api1.example.com');
$data2 = fetchData('http://api2.example.com');
$data3 = fetchData('http://api3.example.com');

// With ReactPHP (requires library)
$loop = React\EventLoop\Factory::create();
$browser = new React\Http\Browser($loop);

$promises = [
    $browser->get('http://api1.example.com'),
    $browser->get('http://api2.example.com'),
    $browser->get('http://api3.example.com'),
];

$loop->run();
```

**Go**:
```go
// Concurrent execution (built-in!)
go fetchData("http://api1.example.com")
go fetchData("http://api2.example.com")
go fetchData("http://api3.example.com")

// That's it! No external libraries needed

func fetchData(url string) {
    resp, err := http.Get(url)
    // Process response
}
```

## Key Concepts

### 1. What is a Goroutine?

```go
// Regular function call (synchronous)
doSomething()  // Wait for it to finish

// Goroutine (asynchronous)
go doSomething()  // Don't wait, continue immediately

func doSomething() {
    fmt.Println("Doing something...")
    time.Sleep(time.Second)
    fmt.Println("Done!")
}

// Example
func main() {
    go doSomething()
    fmt.Println("main: not waiting")
    time.Sleep(2 * time.Second)  // Wait for goroutine to finish
}

// Output:
// main: not waiting
// Doing something...
// Done!
```

### 2. Creating Goroutines

```go
// From a named function
func worker() {
    fmt.Println("Working...")
}

go worker()

// From an anonymous function
go func() {
    fmt.Println("Anonymous worker")
}()

// From a method
type Worker struct{}

func (w *Worker) DoWork() {
    fmt.Println("Method worker")
}

w := &Worker{}
go w.DoWork()

// With parameters
go func(msg string) {
    fmt.Println(msg)
}("Hello from goroutine")
```

### 3. Goroutines vs Threads

```
                Goroutines          OS Threads
Stack Size:     2-8 KB initial      1-2 MB
Creation:       ~2 µs              ~1000 µs
Scalability:    Millions           Thousands
Scheduling:     Go runtime         OS kernel
Context switch: ~0.2 µs           ~1-2 µs
```

```go
// Can easily create thousands
for i := 0; i < 10000; i++ {
    go func(id int) {
        // Each goroutine uses ~2KB
        time.Sleep(time.Second)
    }(i)
}

// This is normal in Go!
// Would be impossible with OS threads
```

### 4. WaitGroup for Synchronization

```go
import "sync"

var wg sync.WaitGroup

func main() {
    for i := 0; i < 5; i++ {
        wg.Add(1)  // Increment counter

        go func(id int) {
            defer wg.Done()  // Decrement when done
            fmt.Printf("Worker %d starting\n", id)
            time.Sleep(time.Second)
            fmt.Printf("Worker %d done\n", id)
        }(i)
    }

    wg.Wait()  // Wait for all to finish
    fmt.Println("All workers completed")
}
```

### 5. Common Goroutine Patterns

```go
// Pattern 1: Fire and forget
go sendEmail(user.Email, "Welcome!")

// Pattern 2: Wait for completion
var wg sync.WaitGroup
wg.Add(1)
go func() {
    defer wg.Done()
    processData()
}()
wg.Wait()

// Pattern 3: Multiple workers
for i := 0; i < 10; i++ {
    go worker(i)
}

// Pattern 4: Fan-out (distribute work)
for _, task := range tasks {
    go processTask(task)
}
```

## Common Patterns

### 1. Parallel Processing

```go
func processURLs(urls []string) {
    var wg sync.WaitGroup

    for _, url := range urls {
        wg.Add(1)

        go func(u string) {
            defer wg.Done()
            resp, err := http.Get(u)
            if err != nil {
                log.Println("Error:", err)
                return
            }
            defer resp.Body.Close()

            // Process response
            fmt.Printf("Got %s: %d bytes\n", u, resp.ContentLength)
        }(url)
    }

    wg.Wait()
    fmt.Println("All URLs processed")
}
```

### 2. Worker Pool

```go
func workerPool(jobs int, workers int) {
    var wg sync.WaitGroup

    // Start workers
    for w := 0; w < workers; w++ {
        wg.Add(1)

        go func(workerID int) {
            defer wg.Done()

            for j := 0; j < jobs/workers; j++ {
                fmt.Printf("Worker %d processing job %d\n", workerID, j)
                time.Sleep(100 * time.Millisecond)
            }
        }(w)
    }

    wg.Wait()
}
```

### 3. Timeout Pattern

```go
func doWorkWithTimeout() {
    done := make(chan bool)

    go func() {
        // Do work
        time.Sleep(2 * time.Second)
        done <- true
    }()

    select {
    case <-done:
        fmt.Println("Work completed")
    case <-time.After(1 * time.Second):
        fmt.Println("Timeout!")
    }
}
```

### 4. Error Handling in Goroutines

```go
type Result struct {
    Value interface{}
    Error error
}

func processWithErrors(items []string) []error {
    var wg sync.WaitGroup
    errChan := make(chan error, len(items))

    for _, item := range items {
        wg.Add(1)

        go func(i string) {
            defer wg.Done()

            if err := process(i); err != nil {
                errChan <- err
            }
        }(item)
    }

    wg.Wait()
    close(errChan)

    // Collect errors
    var errs []error
    for err := range errChan {
        errs = append(errs, err)
    }

    return errs
}
```

### 5. Rate Limiting

```go
func rateLimitedWorker(urls []string, rateLimit int) {
    var wg sync.WaitGroup
    limiter := time.Tick(time.Second / time.Duration(rateLimit))

    for _, url := range urls {
        wg.Add(1)

        go func(u string) {
            defer wg.Done()

            <-limiter  // Wait for rate limit
            resp, err := http.Get(u)
            if err != nil {
                log.Println(err)
                return
            }
            defer resp.Body.Close()
        }(url)
    }

    wg.Wait()
}
```

### 6. Graceful Shutdown

```go
func gracefulShutdown() {
    var wg sync.WaitGroup
    quit := make(chan os.Signal, 1)
    signal.Notify(quit, os.Interrupt, syscall.SIGTERM)

    // Start workers
    for i := 0; i < 5; i++ {
        wg.Add(1)

        go func(id int) {
            defer wg.Done()

            ticker := time.NewTicker(time.Second)
            defer ticker.Stop()

            for {
                select {
                case <-ticker.C:
                    fmt.Printf("Worker %d: tick\n", id)
                case <-quit:
                    fmt.Printf("Worker %d: shutting down\n", id)
                    return
                }
            }
        }(i)
    }

    <-quit
    fmt.Println("Received shutdown signal")
    wg.Wait()
    fmt.Println("All workers stopped")
}
```

## Best Practices

### 1. Always Handle Goroutine Completion

```go
// ❌ Fire and forget (might not complete)
go doSomething()
// Program might exit before goroutine finishes

// ✅ Use WaitGroup
var wg sync.WaitGroup
wg.Add(1)
go func() {
    defer wg.Done()
    doSomething()
}()
wg.Wait()

// ✅ Or use channels
done := make(chan bool)
go func() {
    doSomething()
    done <- true
}()
<-done
```

### 2. Pass Parameters to Goroutines

```go
// ❌ Closure over loop variable
for i := 0; i < 5; i++ {
    go func() {
        fmt.Println(i)  // Wrong! All print 5
    }()
}

// ✅ Pass as parameter
for i := 0; i < 5; i++ {
    go func(id int) {
        fmt.Println(id)  // Correct: 0, 1, 2, 3, 4
    }(i)
}

// ✅ Or create new variable
for i := 0; i < 5; i++ {
    i := i  // Shadow variable
    go func() {
        fmt.Println(i)  // Correct
    }()
}
```

### 3. Use defer with WaitGroup.Done()

```go
// ✅ Always defer Done()
wg.Add(1)
go func() {
    defer wg.Done()  // Ensures Done() is called even if panic

    // Work here
    if err != nil {
        return  // Done() still called
    }
}()
```

### 4. Don't Create Unbounded Goroutines

```go
// ❌ Bad: Creates millions of goroutines
for i := 0; i < 1000000; i++ {
    go process(i)
}

// ✅ Good: Use worker pool
workers := 100
jobs := make(chan int, 1000000)

var wg sync.WaitGroup
for w := 0; w < workers; w++ {
    wg.Add(1)
    go func() {
        defer wg.Done()
        for job := range jobs {
            process(job)
        }
    }()
}

for i := 0; i < 1000000; i++ {
    jobs <- i
}
close(jobs)
wg.Wait()
```

### 5. Proper Error Handling

```go
// ✅ Collect errors from goroutines
func processAll(items []Item) error {
    var wg sync.WaitGroup
    errChan := make(chan error, len(items))

    for _, item := range items {
        wg.Add(1)

        go func(i Item) {
            defer wg.Done()

            if err := process(i); err != nil {
                errChan <- err
            }
        }(item)
    }

    wg.Wait()
    close(errChan)

    // Return first error
    select {
    case err := <-errChan:
        return err
    default:
        return nil
    }
}
```

## Common Mistakes

### 1. Goroutine Leaks

```go
// ❌ Goroutine never exits (leak!)
func leak() {
    ch := make(chan int)

    go func() {
        val := <-ch  // Blocks forever if nothing sends
        fmt.Println(val)
    }()
    // Channel never receives, goroutine blocked forever
}

// ✅ Use timeout or context
func noLeak() {
    ch := make(chan int)
    ctx, cancel := context.WithTimeout(context.Background(), time.Second)
    defer cancel()

    go func() {
        select {
        case val := <-ch:
            fmt.Println(val)
        case <-ctx.Done():
            return  // Exit on timeout
        }
    }()
}
```

### 2. Not Waiting for Goroutines

```go
// ❌ Main exits before goroutines finish
func main() {
    go doWork()
    go doWork()
    go doWork()
    // Program exits immediately!
}

// ✅ Wait for completion
func main() {
    var wg sync.WaitGroup
    wg.Add(3)

    go func() { defer wg.Done(); doWork() }()
    go func() { defer wg.Done(); doWork() }()
    go func() { defer wg.Done(); doWork() }()

    wg.Wait()
}
```

### 3. Shared State Without Synchronization

```go
// ❌ Race condition!
var counter int

for i := 0; i < 1000; i++ {
    go func() {
        counter++  // Unsafe!
    }()
}

// ✅ Use mutex or atomic
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

// ✅ Or atomic operations
var counter int64

for i := 0; i < 1000; i++ {
    go func() {
        atomic.AddInt64(&counter, 1)
    }()
}
```

### 4. Blocking on Unbuffered Channel

```go
// ❌ Deadlock!
func deadlock() {
    ch := make(chan int)
    ch <- 1  // Blocks forever (no receiver)
}

// ✅ Use goroutine
func noDeadlock() {
    ch := make(chan int)

    go func() {
        ch <- 1
    }()

    val := <-ch
}

// ✅ Or use buffered channel
func buffered() {
    ch := make(chan int, 1)
    ch <- 1  // Doesn't block (buffer size 1)
    val := <-ch
}
```

## Comparison with PHP

| Feature | PHP | Go |
|---------|-----|-----|
| Concurrency model | Multi-process (FPM) | Goroutines |
| Built-in support | No (ReactPHP, Swoole) | Yes (language feature) |
| Creation overhead | High (fork process) | Very low (~2µs) |
| Memory per unit | ~8-32 MB (process) | ~2-8 KB (goroutine) |
| Max concurrent | Hundreds | Millions |
| Communication | Shared memory, Redis | Channels |
| Learning curve | Extension required | Built-in, simple |

### PHP Concurrency Options

```php
// 1. Multi-process (FPM)
// - Separate processes
// - No shared memory
// - High overhead

// 2. ReactPHP (event loop)
$loop = React\EventLoop\Factory::create();
$loop->addTimer(1, function() {
    echo "Async!";
});
$loop->run();

// 3. Swoole (extension)
go(function() {
    echo "Coroutine";
});

// 4. Parallel (extension)
$parallel = new \parallel\Runtime();
$future = $parallel->run(function() {
    return "result";
});
```

### Go is Much Simpler

```go
// Just use go keyword
go func() {
    fmt.Println("Async!")
}()

// That's it!
```

## Advanced Patterns

### 1. Generator Pattern

```go
func generateNumbers(n int) <-chan int {
    ch := make(chan int)

    go func() {
        defer close(ch)
        for i := 0; i < n; i++ {
            ch <- i
        }
    }()

    return ch
}

// Usage
for num := range generateNumbers(10) {
    fmt.Println(num)
}
```

### 2. Pipeline Pattern

```go
func generator(nums ...int) <-chan int {
    out := make(chan int)
    go func() {
        defer close(out)
        for _, n := range nums {
            out <- n
        }
    }()
    return out
}

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

// Usage
nums := generator(1, 2, 3, 4, 5)
squared := square(nums)

for result := range squared {
    fmt.Println(result)  // 1, 4, 9, 16, 25
}
```

### 3. Fan-Out, Fan-In

```go
func fanOut(input <-chan int, workers int) []<-chan int {
    outputs := make([]<-chan int, workers)

    for i := 0; i < workers; i++ {
        outputs[i] = worker(input)
    }

    return outputs
}

func fanIn(channels ...<-chan int) <-chan int {
    var wg sync.WaitGroup
    out := make(chan int)

    output := func(c <-chan int) {
        defer wg.Done()
        for n := range c {
            out <- n
        }
    }

    wg.Add(len(channels))
    for _, c := range channels {
        go output(c)
    }

    go func() {
        wg.Wait()
        close(out)
    }()

    return out
}
```

## Next Steps

- **Chapter 12**: Channels & Communication - Goroutine communication patterns
- **Chapter 13**: Select & Timeouts - Advanced channel operations
- **Chapter 14**: Sync Package & Mutexes - Synchronization primitives
- **Chapter 15**: Concurrent Patterns - Real-world concurrency patterns

---

**Key Takeaway**: Goroutines are Go's superpower. They're lightweight, easy to create, and enable true concurrent programming. While PHP requires extensions or external tools, Go makes concurrency a core language feature. Start with simple goroutines and WaitGroups - you'll be running thousands of concurrent tasks in no time.
