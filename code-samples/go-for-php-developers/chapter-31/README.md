# Chapter 31: Context Package

Master context.Context for cancellation, deadlines, and request-scoped values.

## Files
1. 01-context-basics.go
2. 02-cancellation.go
3. 03-timeouts.go
4. 04-values.go

**Example**:
```go
ctx, cancel := context.WithTimeout(context.Background(), 5*time.Second)
defer cancel()

req, _ := http.NewRequestWithContext(ctx, "GET", url, nil)
resp, err := client.Do(req)
```

**Key Takeaway**: Context enables proper cancellation and timeout handling across goroutines.
