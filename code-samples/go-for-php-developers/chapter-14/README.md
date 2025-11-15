# Chapter 14: Sync Package & Mutexes

Learn when to use traditional synchronization instead of channels. Master mutexes, wait groups, atomic operations, and other sync primitives for protecting shared state.

## Overview

While channels are great for communication, sometimes you need to protect shared memory directly. Go's sync package provides traditional synchronization primitives: mutexes for mutual exclusion, wait groups for coordination, and atomic operations for lock-free programming.

## Files in This Chapter

### 1. `01-mutex-basics.go`
**Topics**: Mutex, Lock, Unlock, protecting shared state

### 2. `02-rwmutex.go`
**Topics**: RWMutex, read locks, write locks, performance

### 3. `03-waitgroup.go`
**Topics**: WaitGroup, Add, Done, Wait, coordinating goroutines

### 4. `04-atomic.go`
**Topics**: atomic package, lock-free operations, counters

### 5. `05-once.go`
**Topics**: sync.Once, lazy initialization, singleton pattern

### 6. `06-cond.go`
**Topics**: Cond, Wait, Signal, Broadcast, condition variables

## Quick Reference

### Protecting Shared State

**PHP**:
```php
// PHP: Use file locks or external systems
$fp = fopen("counter.txt", "c+");
flock($fp, LOCK_EX);  // Exclusive lock

$count = (int)fread($fp, 100);
$count++;

ftruncate($fp, 0);
rewind($fp);
fwrite($fp, $count);

flock($fp, LOCK_UN);  // Unlock
fclose($fp);

// Or use Redis, Memcached for distributed locks
```

**Go**:
```go
// Built-in mutex
var (
    counter int
    mu      sync.Mutex
)

mu.Lock()
counter++
mu.Unlock()

// That's it! No external dependencies
```

## Key Concepts

### 1. Mutex (Mutual Exclusion)

```go
var (
    counter int
    mu      sync.Mutex
)

func increment() {
    mu.Lock()
    defer mu.Unlock()  // Always unlock!

    counter++
}

func getCounter() int {
    mu.Lock()
    defer mu.Unlock()

    return counter
}

// Example: Safe concurrent counter
func main() {
    var wg sync.WaitGroup

    for i := 0; i < 1000; i++ {
        wg.Add(1)
        go func() {
            defer wg.Done()
            increment()
        }()
    }

    wg.Wait()
    fmt.Println("Counter:", getCounter())  // Always 1000
}
```

### 2. RWMutex (Read/Write Mutex)

```go
type SafeMap struct {
    mu    sync.RWMutex
    data  map[string]int
}

func (sm *SafeMap) Get(key string) (int, bool) {
    sm.mu.RLock()         // Read lock
    defer sm.mu.RUnlock()

    val, ok := sm.data[key]
    return val, ok
}

func (sm *SafeMap) Set(key string, value int) {
    sm.mu.Lock()          // Write lock
    defer sm.mu.Unlock()

    sm.data[key] = value
}

// Multiple readers can read simultaneously
// Only one writer at a time
// Readers block writers, writers block everything
```

### 3. WaitGroup

```go
var wg sync.WaitGroup

// Add before starting goroutines
wg.Add(3)

go func() {
    defer wg.Done()
    task1()
}()

go func() {
    defer wg.Done()
    task2()
}()

go func() {
    defer wg.Done()
    task3()
}()

// Wait for all to complete
wg.Wait()
fmt.Println("All done")
```

### 4. Atomic Operations

```go
import "sync/atomic"

var counter int64

// Atomic increment
atomic.AddInt64(&counter, 1)

// Atomic read
value := atomic.LoadInt64(&counter)

// Atomic write
atomic.StoreInt64(&counter, 100)

// Compare and swap
atomic.CompareAndSwapInt64(&counter, 100, 200)

// Swap
old := atomic.SwapInt64(&counter, 0)

// Example: Fast concurrent counter
func incrementAtomic() {
    for i := 0; i < 1000; i++ {
        go func() {
            atomic.AddInt64(&counter, 1)
        }()
    }
}
```

### 5. sync.Once

```go
var (
    instance *Singleton
    once     sync.Once
)

func GetInstance() *Singleton {
    once.Do(func() {
        instance = &Singleton{}
        instance.init()
    })
    return instance
}

// Only runs once, even with concurrent calls
// Thread-safe lazy initialization
```

### 6. sync.Cond

```go
var (
    ready bool
    mu    sync.Mutex
    cond  = sync.NewCond(&mu)
)

// Wait for condition
func waiter() {
    mu.Lock()
    defer mu.Unlock()

    for !ready {
        cond.Wait()  // Atomically unlocks, waits, relocks
    }

    fmt.Println("Ready!")
}

// Signal condition
func signaler() {
    mu.Lock()
    ready = true
    mu.Unlock()

    cond.Signal()    // Wake one waiter
    // cond.Broadcast()  // Wake all waiters
}
```

## Common Patterns

### 1. Safe Counter

```go
type SafeCounter struct {
    mu    sync.Mutex
    count int
}

func (c *SafeCounter) Inc() {
    c.mu.Lock()
    defer c.mu.Unlock()
    c.count++
}

func (c *SafeCounter) Value() int {
    c.mu.Lock()
    defer c.mu.Unlock()
    return c.count
}
```

### 2. Safe Map

```go
type SafeMap struct {
    mu   sync.RWMutex
    data map[string]interface{}
}

func NewSafeMap() *SafeMap {
    return &SafeMap{
        data: make(map[string]interface{}),
    }
}

func (sm *SafeMap) Set(key string, value interface{}) {
    sm.mu.Lock()
    defer sm.mu.Unlock()
    sm.data[key] = value
}

func (sm *SafeMap) Get(key string) (interface{}, bool) {
    sm.mu.RLock()
    defer sm.mu.RUnlock()
    val, ok := sm.data[key]
    return val, ok
}

func (sm *SafeMap) Delete(key string) {
    sm.mu.Lock()
    defer sm.mu.Unlock()
    delete(sm.data, key)
}
```

### 3. Connection Pool

```go
type Pool struct {
    mu    sync.Mutex
    conns []*Connection
    max   int
}

func NewPool(max int) *Pool {
    return &Pool{
        conns: make([]*Connection, 0, max),
        max:   max,
    }
}

func (p *Pool) Get() (*Connection, error) {
    p.mu.Lock()
    defer p.mu.Unlock()

    if len(p.conns) > 0 {
        conn := p.conns[0]
        p.conns = p.conns[1:]
        return conn, nil
    }

    if len(p.conns) < p.max {
        return newConnection()
    }

    return nil, errors.New("pool exhausted")
}

func (p *Pool) Put(conn *Connection) {
    p.mu.Lock()
    defer p.mu.Unlock()

    if len(p.conns) < p.max {
        p.conns = append(p.conns, conn)
    } else {
        conn.Close()
    }
}
```

### 4. Singleton Pattern

```go
type Database struct {
    conn *sql.DB
}

var (
    instance *Database
    once     sync.Once
)

func GetDB() *Database {
    once.Do(func() {
        db, err := sql.Open("mysql", dsn)
        if err != nil {
            panic(err)
        }
        instance = &Database{conn: db}
    })
    return instance
}
```

### 5. Cache with Expiration

```go
type CacheItem struct {
    value  interface{}
    expiry time.Time
}

type Cache struct {
    mu    sync.RWMutex
    items map[string]CacheItem
}

func NewCache() *Cache {
    c := &Cache{
        items: make(map[string]CacheItem),
    }

    // Cleanup goroutine
    go c.cleanup()

    return c
}

func (c *Cache) Set(key string, value interface{}, ttl time.Duration) {
    c.mu.Lock()
    defer c.mu.Unlock()

    c.items[key] = CacheItem{
        value:  value,
        expiry: time.Now().Add(ttl),
    }
}

func (c *Cache) Get(key string) (interface{}, bool) {
    c.mu.RLock()
    defer c.mu.RUnlock()

    item, ok := c.items[key]
    if !ok {
        return nil, false
    }

    if time.Now().After(item.expiry) {
        return nil, false
    }

    return item.value, true
}

func (c *Cache) cleanup() {
    ticker := time.NewTicker(time.Minute)
    defer ticker.Stop()

    for range ticker.C {
        c.mu.Lock()
        for key, item := range c.items {
            if time.Now().After(item.expiry) {
                delete(c.items, key)
            }
        }
        c.mu.Unlock()
    }
}
```

## Best Practices

### 1. Always defer Unlock

```go
// ✅ Good
func update() {
    mu.Lock()
    defer mu.Unlock()

    // Update logic
    // Even if panic, unlock is called
}

// ❌ Bad
func update() {
    mu.Lock()

    // Update logic

    mu.Unlock()  // Might not be called if panic
}
```

### 2. Keep Critical Sections Small

```go
// ❌ Bad: Large critical section
func processWithLock() {
    mu.Lock()
    defer mu.Unlock()

    data := fetchData()       // Slow!
    processed := process(data) // Slow!
    save(processed)            // Slow!
}

// ✅ Good: Minimal critical section
func processWithoutLock() {
    data := fetchData()       // Outside lock
    processed := process(data) // Outside lock

    mu.Lock()
    save(processed)            // Only this needs lock
    mu.Unlock()
}
```

### 3. Use RWMutex for Read-Heavy Workloads

```go
// ✅ Good: Many readers, few writers
type Config struct {
    mu   sync.RWMutex
    data map[string]string
}

func (c *Config) Get(key string) string {
    c.mu.RLock()
    defer c.mu.RUnlock()
    return c.data[key]
}

func (c *Config) Set(key, value string) {
    c.mu.Lock()
    defer c.mu.Unlock()
    c.data[key] = value
}
```

### 4. Use Atomic for Simple Counters

```go
// ✅ Good: Atomic for simple operations
var counter int64

atomic.AddInt64(&counter, 1)

// ❌ Overkill: Mutex for simple counter
var (
    counter int64
    mu      sync.Mutex
)

mu.Lock()
counter++
mu.Unlock()
```

## Common Mistakes

### 1. Forgetting to Unlock

```go
// ❌ Deadlock!
func bad() {
    mu.Lock()

    if err != nil {
        return  // Lock not released!
    }

    mu.Unlock()
}

// ✅ Always defer
func good() {
    mu.Lock()
    defer mu.Unlock()

    if err != nil {
        return  // Lock released
    }
}
```

### 2. Copying Mutex

```go
// ❌ Copying mutex breaks it
type Counter struct {
    mu    sync.Mutex
    count int
}

func (c Counter) Inc() {  // Value receiver copies mutex!
    c.mu.Lock()
    defer c.mu.Unlock()
    c.count++
}

// ✅ Use pointer receiver
func (c *Counter) Inc() {
    c.mu.Lock()
    defer c.mu.Unlock()
    c.count++
}
```

### 3. Recursive Locking

```go
// ❌ Deadlock!
func outer() {
    mu.Lock()
    defer mu.Unlock()

    inner()  // Tries to lock again!
}

func inner() {
    mu.Lock()  // Deadlock!
    defer mu.Unlock()
}

// ✅ Restructure to avoid recursion
func outer() {
    mu.Lock()
    defer mu.Unlock()

    innerUnlocked()  // Doesn't need lock
}
```

### 4. Wrong WaitGroup Count

```go
// ❌ Wrong count
for i := 0; i < 10; i++ {
    go func() {
        wg.Add(1)  // Race condition!
        defer wg.Done()
    }()
}

// ✅ Add before starting goroutine
for i := 0; i < 10; i++ {
    wg.Add(1)
    go func() {
        defer wg.Done()
    }()
}
```

## When to Use What

### Use Channels When:
- Transferring ownership of data
- Distributing work
- Communicating async results

### Use Mutexes When:
- Protecting shared state
- Caching
- Counter/metrics
- Configuration

### Use Atomic When:
- Simple counters
- Flags
- No complex state

```go
// Channel: Transfer ownership
ch := make(chan Task)
go func() {
    task := <-ch
    process(task)
}()
ch <- myTask

// Mutex: Protect shared state
mu.Lock()
sharedMap[key] = value
mu.Unlock()

// Atomic: Simple counter
atomic.AddInt64(&requests, 1)
```

## Comparison with PHP

| Feature | PHP | Go |
|---------|-----|-----|
| Mutex | File locks, Semaphores | sync.Mutex (built-in) |
| Read/Write Lock | No built-in | sync.RWMutex |
| Atomic ops | No built-in | atomic package |
| Wait group | No equivalent | sync.WaitGroup |
| Once | Static variables | sync.Once |
| Scope | Process-level | Thread-level |

## Next Steps

- **Chapter 15**: Concurrent Patterns - Combining all concurrent features
- **Chapter 26**: Unit Testing - Testing concurrent code
- **Chapter 29**: Benchmarking - Comparing mutex vs channel performance

---

**Key Takeaway**: Use channels for communication, mutexes for protecting state. Channels are great for passing data between goroutines, but when you need to guard shared memory (maps, counters, caches), mutexes are simpler and often faster. Don't overuse channels - sometimes a mutex is the right tool.
