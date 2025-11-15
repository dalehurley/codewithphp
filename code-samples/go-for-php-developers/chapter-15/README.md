# Chapter 15: Concurrent Patterns

Master real-world concurrency patterns. Learn worker pools, pipelines, rate limiting, and error handling patterns that combine goroutines, channels, and sync primitives effectively.

## Overview

This chapter brings together everything you've learned about Go's concurrency features. You'll see how to combine goroutines, channels, select statements, and sync primitives to solve common problems like parallel processing, rate limiting, and graceful shutdown.

## Files in This Chapter

### 1. `01-worker-pool.go`
**Topics**: Fixed worker pools, job distribution, result collection

### 2. `02-pipeline.go`
**Topics**: Multi-stage pipelines, stream processing, backpressure

### 3. `03-fan-out-fan-in.go`
**Topics**: Parallel processing, result merging, synchronization

### 4. `04-rate-limiting.go`
**Topics**: Token bucket, time.Ticker, request throttling

### 5. `05-error-handling.go`
**Topics**: Error aggregation, first-error wins, context cancellation

### 6. `06-graceful-shutdown.go`
**Topics**: Signal handling, cleanup, timeout patterns

## Quick Reference

### Concurrent Processing

**PHP**:
```php
// PHP: Sequential processing
$results = [];
foreach ($urls as $url) {
    $results[] = file_get_contents($url);
}

// With ReactPHP (complex setup)
$loop = React\EventLoop\Factory::create();
$browser = new React\Http\Browser($loop);

$promises = [];
foreach ($urls as $url) {
    $promises[] = $browser->get($url);
}

React\Promise\all($promises)->then(function ($responses) {
    // Handle responses
});

$loop->run();
```

**Go**:
```go
// Built-in worker pool
func processURLs(urls []string) []Result {
    results := make(chan Result, len(urls))
    var wg sync.WaitGroup

    for _, url := range urls {
        wg.Add(1)
        go func(u string) {
            defer wg.Done()
            results <- fetch(u)
        }(url)
    }

    go func() {
        wg.Wait()
        close(results)
    }()

    var output []Result
    for r := range results {
        output = append(output, r)
    }
    return output
}
```

## Key Patterns

### 1. Worker Pool

```go
type Job struct {
    ID   int
    Data string
}

type Result struct {
    Job   Job
    Value string
    Error error
}

func workerPool(jobs <-chan Job, results chan<- Result, numWorkers int) {
    var wg sync.WaitGroup

    // Start workers
    for i := 0; i < numWorkers; i++ {
        wg.Add(1)

        go func(workerID int) {
            defer wg.Done()

            for job := range jobs {
                // Process job
                value, err := process(job.Data)

                results <- Result{
                    Job:   job,
                    Value: value,
                    Error: err,
                }
            }
        }(i)
    }

    // Close results when all workers done
    wg.Wait()
    close(results)
}

// Usage
func main() {
    jobs := make(chan Job, 100)
    results := make(chan Result, 100)

    // Start pool
    go workerPool(jobs, results, 10)

    // Send jobs
    go func() {
        for i := 0; i < 50; i++ {
            jobs <- Job{ID: i, Data: fmt.Sprintf("job-%d", i)}
        }
        close(jobs)
    }()

    // Collect results
    for result := range results {
        if result.Error != nil {
            log.Printf("Job %d failed: %v", result.Job.ID, result.Error)
        } else {
            log.Printf("Job %d: %s", result.Job.ID, result.Value)
        }
    }
}
```

### 2. Pipeline Pattern

```go
// Stage 1: Generate data
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

// Stage 3: Filter evens
func filterEven(in <-chan int) <-chan int {
    out := make(chan int)

    go func() {
        defer close(out)
        for n := range in {
            if n%2 == 0 {
                out <- n
            }
        }
    }()

    return out
}

// Pipeline
func main() {
    numbers := generator(1, 2, 3, 4, 5, 6, 7, 8, 9, 10)
    squared := square(numbers)
    evens := filterEven(squared)

    for n := range evens {
        fmt.Println(n)  // 4, 16, 36, 64, 100
    }
}
```

### 3. Fan-Out / Fan-In

```go
func fanOut(input <-chan int, workers int) []<-chan int {
    channels := make([]<-chan int, workers)

    for i := 0; i < workers; i++ {
        ch := make(chan int)
        channels[i] = ch

        go func(out chan<- int) {
            defer close(out)

            for n := range input {
                // Expensive operation
                time.Sleep(100 * time.Millisecond)
                out <- n * n
            }
        }(ch)
    }

    return channels
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

// Usage
func main() {
    input := generator(1, 2, 3, 4, 5, 6, 7, 8, 9, 10)

    // Fan-out to 3 workers
    workers := fanOut(input, 3)

    // Fan-in results
    results := fanIn(workers...)

    for result := range results {
        fmt.Println(result)
    }
}
```

### 4. Rate Limiting

```go
// Token bucket rate limiter
type RateLimiter struct {
    tokens chan struct{}
}

func NewRateLimiter(rate int, burst int) *RateLimiter {
    rl := &RateLimiter{
        tokens: make(chan struct{}, burst),
    }

    // Fill bucket
    for i := 0; i < burst; i++ {
        rl.tokens <- struct{}{}
    }

    // Refill tokens
    go func() {
        ticker := time.NewTicker(time.Second / time.Duration(rate))
        defer ticker.Stop()

        for range ticker.C {
            select {
            case rl.tokens <- struct{}{}:
            default:
                // Bucket full
            }
        }
    }()

    return rl
}

func (rl *RateLimiter) Wait() {
    <-rl.tokens
}

// Usage
func main() {
    limiter := NewRateLimiter(10, 5)  // 10/sec, burst 5

    for i := 0; i < 20; i++ {
        limiter.Wait()
        fmt.Printf("Request %d at %v\n", i, time.Now())
    }
}
```

### 5. Error Group Pattern

```go
type ErrorGroup struct {
    wg     sync.WaitGroup
    errMu  sync.Mutex
    errors []error
}

func (eg *ErrorGroup) Go(fn func() error) {
    eg.wg.Add(1)

    go func() {
        defer eg.wg.Done()

        if err := fn(); err != nil {
            eg.errMu.Lock()
            eg.errors = append(eg.errors, err)
            eg.errMu.Unlock()
        }
    }()
}

func (eg *ErrorGroup) Wait() []error {
    eg.wg.Wait()
    return eg.errors
}

// Usage
func main() {
    var eg ErrorGroup

    urls := []string{
        "http://example.com",
        "http://google.com",
        "http://github.com",
    }

    for _, url := range urls {
        url := url
        eg.Go(func() error {
            return fetchURL(url)
        })
    }

    errs := eg.Wait()
    if len(errs) > 0 {
        log.Println("Errors:", errs)
    }
}
```

### 6. Graceful Shutdown

```go
type Server struct {
    workers []chan struct{}
    wg      sync.WaitGroup
}

func NewServer(numWorkers int) *Server {
    s := &Server{
        workers: make([]chan struct{}, numWorkers),
    }

    // Start workers
    for i := 0; i < numWorkers; i++ {
        done := make(chan struct{})
        s.workers[i] = done

        s.wg.Add(1)
        go s.worker(i, done)
    }

    return s
}

func (s *Server) worker(id int, done <-chan struct{}) {
    defer s.wg.Done()

    ticker := time.NewTicker(time.Second)
    defer ticker.Stop()

    for {
        select {
        case <-ticker.C:
            fmt.Printf("Worker %d: processing\n", id)
        case <-done:
            fmt.Printf("Worker %d: shutting down\n", id)
            return
        }
    }
}

func (s *Server) Shutdown(timeout time.Duration) error {
    fmt.Println("Shutting down...")

    // Signal all workers
    for _, done := range s.workers {
        close(done)
    }

    // Wait with timeout
    done := make(chan struct{})
    go func() {
        s.wg.Wait()
        close(done)
    }()

    select {
    case <-done:
        fmt.Println("Shutdown complete")
        return nil
    case <-time.After(timeout):
        return errors.New("shutdown timeout")
    }
}

// Usage
func main() {
    server := NewServer(5)

    // Wait for signal
    sigint := make(chan os.Signal, 1)
    signal.Notify(sigint, os.Interrupt, syscall.SIGTERM)
    <-sigint

    // Shutdown with 5s timeout
    if err := server.Shutdown(5 * time.Second); err != nil {
        log.Fatal(err)
    }
}
```

## Advanced Patterns

### 1. Bounded Concurrency

```go
func processWithBoundedConcurrency(items []Item, maxConcurrent int) []Result {
    sem := make(chan struct{}, maxConcurrent)
    results := make(chan Result, len(items))
    var wg sync.WaitGroup

    for _, item := range items {
        wg.Add(1)

        go func(i Item) {
            defer wg.Done()

            sem <- struct{}{}        // Acquire
            defer func() { <-sem }() // Release

            results <- process(i)
        }(item)
    }

    go func() {
        wg.Wait()
        close(results)
    }()

    var output []Result
    for r := range results {
        output = append(output, r)
    }

    return output
}
```

### 2. Circuit Breaker

```go
type CircuitBreaker struct {
    maxFailures int
    timeout     time.Duration

    mu           sync.Mutex
    failures     int
    lastFailTime time.Time
    state        string // "closed", "open", "half-open"
}

func NewCircuitBreaker(maxFailures int, timeout time.Duration) *CircuitBreaker {
    return &CircuitBreaker{
        maxFailures: maxFailures,
        timeout:     timeout,
        state:       "closed",
    }
}

func (cb *CircuitBreaker) Call(fn func() error) error {
    cb.mu.Lock()

    if cb.state == "open" {
        if time.Since(cb.lastFailTime) > cb.timeout {
            cb.state = "half-open"
        } else {
            cb.mu.Unlock()
            return errors.New("circuit breaker open")
        }
    }

    cb.mu.Unlock()

    err := fn()

    cb.mu.Lock()
    defer cb.mu.Unlock()

    if err != nil {
        cb.failures++
        cb.lastFailTime = time.Now()

        if cb.failures >= cb.maxFailures {
            cb.state = "open"
        }

        return err
    }

    // Success
    cb.failures = 0
    cb.state = "closed"

    return nil
}
```

### 3. Retry with Backoff

```go
func retryWithBackoff(fn func() error, maxAttempts int) error {
    backoff := time.Second

    for i := 0; i < maxAttempts; i++ {
        err := fn()
        if err == nil {
            return nil
        }

        if i < maxAttempts-1 {
            log.Printf("Attempt %d failed: %v, retrying in %v", i+1, err, backoff)
            time.Sleep(backoff)
            backoff *= 2  // Exponential backoff

            if backoff > 30*time.Second {
                backoff = 30 * time.Second  // Cap at 30s
            }
        }
    }

    return fmt.Errorf("failed after %d attempts", maxAttempts)
}
```

## Best Practices

### 1. Always Use Context for Cancellation

```go
func worker(ctx context.Context, jobs <-chan Job) {
    for {
        select {
        case <-ctx.Done():
            return
        case job := <-jobs:
            processJob(job)
        }
    }
}
```

### 2. Handle Errors Properly

```go
// ✅ Good: Collect all errors
func processAll(items []Item) []error {
    var mu sync.Mutex
    var errors []error

    var wg sync.WaitGroup
    for _, item := range items {
        wg.Add(1)

        go func(i Item) {
            defer wg.Done()

            if err := process(i); err != nil {
                mu.Lock()
                errors = append(errors, err)
                mu.Unlock()
            }
        }(item)
    }

    wg.Wait()
    return errors
}
```

### 3. Prevent Goroutine Leaks

```go
// ✅ Good: Cleanup goroutines
func safeProcess(ctx context.Context) {
    done := make(chan bool, 1)

    go func() {
        result := longRunningTask()
        done <- result
    }()

    select {
    case result := <-done:
        handleResult(result)
    case <-ctx.Done():
        return  // Goroutine will complete and write to buffered channel
    }
}
```

## Next Steps

- **Chapter 16**: HTTP Server Basics - Applying concurrency to web servers
- **Chapter 17**: Routing & Middleware - Concurrent request handling
- **Chapter 26**: Unit Testing - Testing concurrent code
- **Chapter 29**: Benchmarking - Measuring concurrent performance

---

**Key Takeaway**: Real-world Go programs combine goroutines, channels, select, and sync primitives. Worker pools handle parallel processing, pipelines stream data efficiently, and proper error handling ensures robust concurrent systems. Master these patterns and you'll write concurrent code that's both performant and maintainable - something nearly impossible in vanilla PHP.
