# Chapter 29: Benchmarking & Profiling

Measure and optimize performance with Go's built-in benchmarking and profiling tools.

## Files
1. 01-benchmarks.go
2. 02-profiling.go
3. 03-memory-profiling.go
4. 04-pprof-tool.go

**Example**:
```go
func BenchmarkFibonacci(b *testing.B) {
    for i := 0; i < b.N; i++ {
        Fibonacci(20)
    }
}
```

**Key Takeaway**: Go's benchmarking is built-in and powerful for performance optimization.
