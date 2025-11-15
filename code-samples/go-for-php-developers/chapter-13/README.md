# Chapter 13: Select & Timeouts

Master the select statement - Go's powerful multiplexer for channels. Learn how to handle multiple channels, implement timeouts, and create non-blocking operations.

## Overview

The select statement lets you wait on multiple channel operations simultaneously. It's like a switch statement for channels, enabling timeouts, non-blocking communication, and complex channel orchestration. This is unique to Go - PHP has nothing comparable built-in.

## Files in This Chapter

### 1. `01-select-basics.go`
**Topics**: Select statement, multiple channels, random selection

### 2. `02-timeout-patterns.go`
**Topics**: time.After, context deadlines, timeout implementations

### 3. `03-non-blocking.go`
**Topics**: Default case, try-send, try-receive patterns

### 4. `04-select-advanced.go`
**Topics**: Empty select, nil channels, dynamic channel selection

### 5. `05-real-world-examples.go`
**Topics**: HTTP timeouts, graceful shutdown, rate limiting

## Quick Reference

### Multiple Operations

**PHP**:
```php
// PHP: No built-in equivalent
// Would need to poll or use stream_select

$streams = [$stream1, $stream2];
$write = $except = null;

while (true) {
    $read = $streams;
    $result = stream_select($read, $write, $except, $timeout);

    if ($result) {
        foreach ($read as $stream) {
            // Handle ready stream
        }
    } else {
        // Timeout
    }
}
```

**Go**:
```go
// Built-in select statement
select {
case msg1 := <-ch1:
    fmt.Println("Received from ch1:", msg1)
case msg2 := <-ch2:
    fmt.Println("Received from ch2:", msg2)
case <-time.After(time.Second):
    fmt.Println("Timeout")
}
```

## Key Concepts

### 1. Basic Select

```go
ch1 := make(chan string)
ch2 := make(chan string)

go func() {
    time.Sleep(1 * time.Second)
    ch1 <- "one"
}()

go func() {
    time.Sleep(2 * time.Second)
    ch2 <- "two"
}()

select {
case msg1 := <-ch1:
    fmt.Println("Received", msg1)
case msg2 := <-ch2:
    fmt.Println("Received", msg2)
}
// Waits for first channel to be ready
// Output: "Received one" (after 1 second)
```

### 2. Select with Timeout

```go
func fetchWithTimeout(url string) (string, error) {
    result := make(chan string, 1)

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
    case <-time.After(5 * time.Second):
        return "", errors.New("request timeout")
    }
}
```

### 3. Default Case (Non-Blocking)

```go
select {
case msg := <-ch:
    fmt.Println("Received:", msg)
default:
    fmt.Println("No message ready")
}
// Returns immediately if channel not ready

// Try-send pattern
select {
case ch <- value:
    fmt.Println("Sent")
default:
    fmt.Println("Channel full or blocked")
}

// Try-receive pattern
select {
case msg := <-ch:
    fmt.Println("Received:", msg)
default:
    fmt.Println("Nothing to receive")
}
```

### 4. Multiple Send and Receive

```go
select {
case <-ch1:
    // Receive from ch1
case <-ch2:
    // Receive from ch2
case ch3 <- value:
    // Send to ch3
case ch4 <- value:
    // Send to ch4
default:
    // None ready
}
```

### 5. Empty Select (Block Forever)

```go
select {}
// Blocks forever - useful for keeping main alive

func main() {
    // Start background workers
    go worker1()
    go worker2()

    // Keep main alive
    select {}
}
```

### 6. Nil Channel Behavior

```go
var ch chan int  // nil channel

select {
case <-ch:
    // Never executes (nil channel never ready)
default:
    fmt.Println("Channel is nil")
}

// Use to dynamically disable cases
if !shouldReceive {
    ch = nil  // Disable this case
}

select {
case <-ch:  // Skipped if ch is nil
    process()
default:
    // Do something else
}
```

## Common Patterns

### 1. Timeout Pattern

```go
func doWorkWithTimeout(timeout time.Duration) error {
    done := make(chan error, 1)

    go func() {
        done <- doWork()
    }()

    select {
    case err := <-done:
        return err
    case <-time.After(timeout):
        return errors.New("operation timed out")
    }
}
```

### 2. Heartbeat Pattern

```go
func heartbeat(interval time.Duration) <-chan struct{} {
    beat := make(chan struct{})

    go func() {
        ticker := time.NewTicker(interval)
        defer ticker.Stop()

        for {
            select {
            case <-ticker.C:
                beat <- struct{}{}
            }
        }
    }()

    return beat
}

// Usage
beat := heartbeat(time.Second)

for {
    select {
    case <-beat:
        fmt.Println("Still alive")
    case <-time.After(2 * time.Second):
        fmt.Println("No heartbeat!")
        return
    }
}
```

### 3. Graceful Shutdown

```go
func worker(done <-chan struct{}) {
    ticker := time.NewTicker(time.Second)
    defer ticker.Stop()

    for {
        select {
        case <-ticker.C:
            fmt.Println("Working...")
        case <-done:
            fmt.Println("Shutting down...")
            return
        }
    }
}

func main() {
    done := make(chan struct{})

    go worker(done)

    // Wait for interrupt
    sigint := make(chan os.Signal, 1)
    signal.Notify(sigint, os.Interrupt)
    <-sigint

    // Signal shutdown
    close(done)

    time.Sleep(time.Second)
    fmt.Println("Shutdown complete")
}
```

### 4. Fan-In with Select

```go
func fanIn(ch1, ch2 <-chan int) <-chan int {
    out := make(chan int)

    go func() {
        defer close(out)

        for {
            select {
            case val, ok := <-ch1:
                if !ok {
                    ch1 = nil  // Disable this case
                    continue
                }
                out <- val
            case val, ok := <-ch2:
                if !ok {
                    ch2 = nil  // Disable this case
                    continue
                }
                out <- val
            }

            // Exit when both closed
            if ch1 == nil && ch2 == nil {
                return
            }
        }
    }()

    return out
}
```

### 5. Rate Limiting

```go
func rateLimiter(rate time.Duration) <-chan time.Time {
    return time.Tick(rate)
}

func processWithRateLimit(items []string) {
    limiter := rateLimiter(time.Second / 10) // 10 per second

    for _, item := range items {
        <-limiter  // Wait for rate limit
        process(item)
    }
}

// Or with select
func processWithSelectRateLimit(items []string) {
    limiter := time.Tick(100 * time.Millisecond)

    for _, item := range items {
        select {
        case <-limiter:
            process(item)
        case <-time.After(5 * time.Second):
            fmt.Println("Overall timeout")
            return
        }
    }
}
```

### 6. Context-Based Cancellation

```go
func doWorkWithContext(ctx context.Context) error {
    result := make(chan error, 1)

    go func() {
        result <- heavyWork()
    }()

    select {
    case err := <-result:
        return err
    case <-ctx.Done():
        return ctx.Err()  // Cancelled or timeout
    }
}

// Usage
ctx, cancel := context.WithTimeout(context.Background(), 5*time.Second)
defer cancel()

err := doWorkWithContext(ctx)
```

## Best Practices

### 1. Always Have Timeout

```go
// ❌ Bad: Can block forever
select {
case result := <-ch:
    process(result)
}

// ✅ Good: With timeout
select {
case result := <-ch:
    process(result)
case <-time.After(5 * time.Second):
    return errors.New("timeout")
}
```

### 2. Use Context for Cancellation

```go
// ✅ Good: Context-aware
select {
case result := <-ch:
    return result
case <-ctx.Done():
    return ctx.Err()
}
```

### 3. Check Channel Closure

```go
// ✅ Good: Check if closed
select {
case val, ok := <-ch:
    if !ok {
        // Channel closed
        return
    }
    process(val)
case <-time.After(timeout):
    return errors.New("timeout")
}
```

### 4. Buffer Channels in Select

```go
// ✅ Good: Buffered to prevent goroutine leak
result := make(chan string, 1)

go func() {
    result <- compute()  // Won't block if select times out
}()

select {
case res := <-result:
    return res
case <-time.After(timeout):
    return "timeout"  // Goroutine can still complete
}
```

## Common Mistakes

### 1. Forgetting Default Can Block

```go
// ❌ Blocks if no channel ready
select {
case msg := <-ch:
    process(msg)
}

// ✅ Non-blocking with default
select {
case msg := <-ch:
    process(msg)
default:
    // Do something else
}
```

### 2. Nil Channel in Select

```go
var ch chan int  // nil

// ❌ This case never executes
select {
case <-ch:
    fmt.Println("Never happens")
default:
    fmt.Println("Always this")
}
```

### 3. Not Handling All Cases

```go
// ❌ Missing timeout case
select {
case result := <-ch:
    return result
// What if ch never sends?
}

// ✅ Always have fallback
select {
case result := <-ch:
    return result
case <-time.After(timeout):
    return errors.New("timeout")
}
```

### 4. Goroutine Leak with Timeout

```go
// ❌ Goroutine leak!
result := make(chan string)

go func() {
    result <- expensiveOperation()  // Blocks if select times out
}()

select {
case res := <-result:
    return res
case <-time.After(time.Second):
    return "timeout"  // Goroutine still blocked!
}

// ✅ Use buffered channel
result := make(chan string, 1)  // Won't block goroutine
```

## Advanced Patterns

### 1. Priority Select

```go
// Higher priority for ch1
for {
    select {
    case msg := <-ch1:
        handleHighPriority(msg)
    default:
        select {
        case msg := <-ch1:
            handleHighPriority(msg)
        case msg := <-ch2:
            handleNormalPriority(msg)
        }
    }
}
```

### 2. Dynamic Channel Selection

```go
func dynamicSelect(channels []<-chan int) int {
    cases := make([]reflect.SelectCase, len(channels))

    for i, ch := range channels {
        cases[i] = reflect.SelectCase{
            Dir:  reflect.SelectRecv,
            Chan: reflect.ValueOf(ch),
        }
    }

    _, value, ok := reflect.Select(cases)
    if ok {
        return value.Int()
    }
    return 0
}
```

### 3. Timeout with Retry

```go
func retryWithTimeout(attempts int, timeout time.Duration) error {
    for i := 0; i < attempts; i++ {
        result := make(chan error, 1)

        go func() {
            result <- doWork()
        }()

        select {
        case err := <-result:
            if err == nil {
                return nil
            }
            log.Printf("Attempt %d failed: %v", i+1, err)
        case <-time.After(timeout):
            log.Printf("Attempt %d timed out", i+1)
        }

        if i < attempts-1 {
            time.Sleep(time.Second * time.Duration(i+1))
        }
    }

    return errors.New("all attempts failed")
}
```

## Real-World Examples

### 1. HTTP Request with Timeout

```go
func fetchURL(url string, timeout time.Duration) ([]byte, error) {
    result := make(chan []byte, 1)
    errChan := make(chan error, 1)

    go func() {
        resp, err := http.Get(url)
        if err != nil {
            errChan <- err
            return
        }
        defer resp.Body.Close()

        body, err := io.ReadAll(resp.Body)
        if err != nil {
            errChan <- err
            return
        }

        result <- body
    }()

    select {
    case data := <-result:
        return data, nil
    case err := <-errChan:
        return nil, err
    case <-time.After(timeout):
        return nil, errors.New("request timeout")
    }
}
```

### 2. Circuit Breaker Pattern

```go
type CircuitBreaker struct {
    maxFailures int
    timeout     time.Duration
    failures    int
    lastFail    time.Time
    mu          sync.Mutex
}

func (cb *CircuitBreaker) Call(fn func() error) error {
    cb.mu.Lock()

    // Check if circuit is open
    if cb.failures >= cb.maxFailures {
        if time.Since(cb.lastFail) < cb.timeout {
            cb.mu.Unlock()
            return errors.New("circuit breaker open")
        }
        // Try to close circuit
        cb.failures = 0
    }

    cb.mu.Unlock()

    // Execute with timeout
    result := make(chan error, 1)

    go func() {
        result <- fn()
    }()

    select {
    case err := <-result:
        if err != nil {
            cb.mu.Lock()
            cb.failures++
            cb.lastFail = time.Now()
            cb.mu.Unlock()
        }
        return err
    case <-time.After(cb.timeout):
        cb.mu.Lock()
        cb.failures++
        cb.lastFail = time.Now()
        cb.mu.Unlock()
        return errors.New("call timeout")
    }
}
```

## Next Steps

- **Chapter 14**: Sync Package & Mutexes - When channels aren't the answer
- **Chapter 15**: Concurrent Patterns - Combining everything you've learned
- **Chapter 31**: Context Package - Deep dive into context for cancellation

---

**Key Takeaway**: The select statement is your Swiss Army knife for channel operations. Use it for timeouts, non-blocking operations, and coordinating multiple channels. Combined with channels and goroutines, select enables elegant concurrent programming that's nearly impossible in PHP without external tools.
