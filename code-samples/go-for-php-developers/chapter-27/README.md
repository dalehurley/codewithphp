# Chapter 27: Table-Driven Tests

Master table-driven testing - Go's idiomatic approach to testing multiple scenarios efficiently.

## Files
1. 01-table-driven-basics.go
2. 02-subtests.go
3. 03-parallel-tests.go
4. 04-test-helpers.go

**Example**:
```go
func TestAdd(t *testing.T) {
    tests := []struct {
        name string
        a, b int
        want int
    }{
        {"positive", 2, 3, 5},
        {"negative", -1, -1, -2},
        {"zero", 0, 0, 0},
    }

    for _, tt := range tests {
        t.Run(tt.name, func(t *testing.T) {
            got := Add(tt.a, tt.b)
            if got != tt.want {
                t.Errorf("Add(%d, %d) = %d, want %d", tt.a, tt.b, got, tt.want)
            }
        })
    }
}
```

**Key Takeaway**: Table-driven tests make testing multiple scenarios clean and maintainable.
