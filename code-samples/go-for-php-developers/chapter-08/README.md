# Chapter 08: Error Handling

Master Go's idiomatic error handling approach. Learn why Go doesn't use exceptions and how multiple return values create more explicit, maintainable code.

## Overview

Go doesn't have try/catch/throw. Instead, errors are values returned from functions. This explicit approach makes error paths clear and forces you to handle errors where they occur. While it may seem verbose at first, it leads to more robust code.

## Files in This Chapter

### 1. `01-error-basics.go`
**Topics**: The error interface, errors.New, fmt.Errorf, checking errors

### 2. `02-custom-errors.go`
**Topics**: Custom error types, error methods, structured errors

### 3. `03-error-wrapping.go`
**Topics**: errors.Is, errors.As, error wrapping with %w

### 4. `04-sentinel-errors.go`
**Topics**: Predefined errors, io.EOF, comparing errors

### 5. `05-panic-recover.go`
**Topics**: panic, recover, when to use (rarely!)

### 6. `06-error-patterns.go`
**Topics**: Error handling patterns, best practices, helper functions

## Quick Reference

### Exception Handling

**PHP**:
```php
function divide(float $a, float $b): float {
    if ($b == 0) {
        throw new InvalidArgumentException("division by zero");
    }
    return $a / $b;
}

try {
    $result = divide(10, 0);
    echo $result;
} catch (InvalidArgumentException $e) {
    echo "Error: " . $e->getMessage();
} finally {
    echo "Cleanup";
}
```

**Go**:
```go
func divide(a, b float64) (float64, error) {
    if b == 0 {
        return 0, errors.New("division by zero")
    }
    return a / b, nil
}

result, err := divide(10, 0)
if err != nil {
    fmt.Println("Error:", err)
    return
}
fmt.Println(result)

// No try/catch! Errors are values
// "finally" equivalent is defer
defer fmt.Println("Cleanup")
```

### Creating Errors

**PHP**:
```php
throw new Exception("something went wrong");
throw new RuntimeException("file not found");
throw new InvalidArgumentException("invalid input");
```

**Go**:
```go
// Simple error
err := errors.New("something went wrong")

// Formatted error
err := fmt.Errorf("file not found: %s", filename)

// Custom error type
err := &ValidationError{Field: "email", Message: "invalid format"}
```

## Key Concepts

### 1. The error Interface

```go
// Built-in error interface
type error interface {
    Error() string
}

// Any type with Error() method is an error
type MyError struct {
    Message string
    Code    int
}

func (e *MyError) Error() string {
    return fmt.Sprintf("%d: %s", e.Code, e.Message)
}

// Use it
err := &MyError{Message: "something failed", Code: 500}
fmt.Println(err)  // "500: something failed"
```

### 2. Returning Errors

```go
// Single error
func doSomething() error {
    if problemOccurs {
        return errors.New("problem occurred")
    }
    return nil  // No error
}

// Result and error
func getUser(id int) (*User, error) {
    user := database.Find(id)
    if user == nil {
        return nil, errors.New("user not found")
    }
    return user, nil
}

// Multiple results
func divide(a, b float64) (result float64, err error) {
    if b == 0 {
        return 0, errors.New("division by zero")
    }
    return a / b, nil
}
```

### 3. Checking Errors

```go
// Standard pattern
result, err := doSomething()
if err != nil {
    // Handle error
    return err
}
// Use result

// Inline check
if err := doSomething(); err != nil {
    return err
}

// Ignore error (not recommended!)
result, _ := doSomething()
```

### 4. Error Wrapping (Go 1.13+)

```go
// Wrap error with context
func readConfig() error {
    data, err := os.ReadFile("config.json")
    if err != nil {
        return fmt.Errorf("failed to read config: %w", err)
    }
    // Parse data...
    return nil
}

// Chain errors
func initialize() error {
    err := readConfig()
    if err != nil {
        return fmt.Errorf("initialization failed: %w", err)
    }
    return nil
}

// Error message includes full chain:
// "initialization failed: failed to read config: open config.json: no such file or directory"
```

### 5. Error Unwrapping

```go
// errors.Is - check if error matches
if errors.Is(err, os.ErrNotExist) {
    fmt.Println("File doesn't exist")
}

// errors.As - extract specific error type
var pathError *os.PathError
if errors.As(err, &pathError) {
    fmt.Println("Failed path:", pathError.Path)
}

// Example
err := fmt.Errorf("operation failed: %w", os.ErrNotExist)
errors.Is(err, os.ErrNotExist)  // true - unwraps to find it
```

### 6. Sentinel Errors

```go
// Predefined errors (package level)
var (
    ErrNotFound      = errors.New("not found")
    ErrUnauthorized  = errors.New("unauthorized")
    ErrInvalidInput  = errors.New("invalid input")
)

func getUser(id int) (*User, error) {
    if id <= 0 {
        return nil, ErrInvalidInput
    }
    user := findUser(id)
    if user == nil {
        return nil, ErrNotFound
    }
    return user, nil
}

// Check with ==
user, err := getUser(123)
if err == ErrNotFound {
    // Handle not found
}

// Or with errors.Is (safer with wrapped errors)
if errors.Is(err, ErrNotFound) {
    // Handle not found
}
```

## Common Patterns

### 1. Standard Error Checking

```go
func processFile(path string) error {
    // Check error immediately
    file, err := os.Open(path)
    if err != nil {
        return fmt.Errorf("failed to open file: %w", err)
    }
    defer file.Close()

    // Check error immediately
    data, err := io.ReadAll(file)
    if err != nil {
        return fmt.Errorf("failed to read file: %w", err)
    }

    // Process data
    return nil
}
```

### 2. Custom Error Types

```go
// Structured error
type ValidationError struct {
    Field   string
    Message string
}

func (e *ValidationError) Error() string {
    return fmt.Sprintf("validation error on %s: %s", e.Field, e.Message)
}

// Constructor
func NewValidationError(field, message string) error {
    return &ValidationError{
        Field:   field,
        Message: message,
    }
}

// Usage
func validateUser(user *User) error {
    if user.Email == "" {
        return NewValidationError("email", "required")
    }
    if !strings.Contains(user.Email, "@") {
        return NewValidationError("email", "invalid format")
    }
    return nil
}

// Check specific type
err := validateUser(user)
var valErr *ValidationError
if errors.As(err, &valErr) {
    fmt.Printf("Field: %s, Message: %s\n", valErr.Field, valErr.Message)
}
```

### 3. Error Collection

```go
type ErrorList []error

func (e ErrorList) Error() string {
    if len(e) == 0 {
        return ""
    }
    var msgs []string
    for _, err := range e {
        msgs = append(msgs, err.Error())
    }
    return strings.Join(msgs, "; ")
}

func validateForm(form *Form) error {
    var errs ErrorList

    if form.Name == "" {
        errs = append(errs, errors.New("name required"))
    }
    if form.Email == "" {
        errs = append(errs, errors.New("email required"))
    }
    if form.Age < 0 {
        errs = append(errs, errors.New("invalid age"))
    }

    if len(errs) > 0 {
        return errs
    }
    return nil
}
```

### 4. Error Helper Functions

```go
// Check and log
func checkErr(err error) {
    if err != nil {
        log.Println("Error:", err)
    }
}

// Check and exit
func must(err error) {
    if err != nil {
        log.Fatal(err)
    }
}

// Check and panic (use sparingly!)
func mustNot(err error) {
    if err != nil {
        panic(err)
    }
}

// Usage
data, err := os.ReadFile("config.json")
must(err)  // Exit if error

// Common in main() initialization
func main() {
    db, err := sql.Open("mysql", dsn)
    must(err)
    defer db.Close()
}
```

### 5. Retry Pattern

```go
func retry(attempts int, sleep time.Duration, fn func() error) error {
    for i := 0; i < attempts; i++ {
        err := fn()
        if err == nil {
            return nil
        }

        if i < attempts-1 {
            time.Sleep(sleep)
            sleep *= 2  // Exponential backoff
        }
    }
    return fmt.Errorf("after %d attempts, last error: %w", attempts, err)
}

// Usage
err := retry(3, time.Second, func() error {
    return makeNetworkRequest()
})
```

### 6. Context-Rich Errors

```go
type RequestError struct {
    StatusCode int
    Method     string
    URL        string
    Err        error
}

func (e *RequestError) Error() string {
    return fmt.Sprintf("%s %s failed with status %d: %v",
        e.Method, e.URL, e.StatusCode, e.Err)
}

func (e *RequestError) Unwrap() error {
    return e.Err
}

func makeRequest(method, url string) error {
    resp, err := http.Get(url)
    if err != nil {
        return &RequestError{
            Method: method,
            URL:    url,
            Err:    err,
        }
    }
    if resp.StatusCode != 200 {
        return &RequestError{
            StatusCode: resp.StatusCode,
            Method:     method,
            URL:        url,
        }
    }
    return nil
}
```

## panic and recover

### When to Use panic

```go
// ❌ Don't use panic for normal errors
func getUser(id int) *User {
    user := findUser(id)
    if user == nil {
        panic("user not found")  // ❌ Bad!
    }
    return user
}

// ✅ Return error instead
func getUser(id int) (*User, error) {
    user := findUser(id)
    if user == nil {
        return nil, errors.New("user not found")
    }
    return user, nil
}

// ✅ Use panic for unrecoverable errors
func init() {
    config, err := loadConfig()
    if err != nil {
        panic("failed to load config: " + err.Error())
    }
}
```

### recover from panic

```go
func safeExecute(fn func()) (err error) {
    defer func() {
        if r := recover(); r != nil {
            err = fmt.Errorf("panic: %v", r)
        }
    }()

    fn()  // May panic
    return nil
}

// Usage
err := safeExecute(func() {
    // Some code that might panic
    panic("something went wrong")
})

if err != nil {
    fmt.Println("Recovered:", err)
}
```

## Best Practices

### 1. Always Check Errors

```go
// ❌ Ignoring errors
file, _ := os.Open("data.txt")

// ✅ Check errors
file, err := os.Open("data.txt")
if err != nil {
    return err
}
defer file.Close()
```

### 2. Add Context to Errors

```go
// ❌ Losing context
func readConfig() error {
    _, err := os.ReadFile("config.json")
    return err  // Just returns the raw error
}

// ✅ Add context
func readConfig() error {
    _, err := os.ReadFile("config.json")
    if err != nil {
        return fmt.Errorf("failed to read config: %w", err)
    }
    return nil
}
```

### 3. Use %w for Error Wrapping

```go
// ❌ Loses error chain
return fmt.Errorf("failed: %v", err)

// ✅ Preserves error chain
return fmt.Errorf("failed: %w", err)

// Allows errors.Is and errors.As to work
```

### 4. Define Sentinel Errors as Package Variables

```go
// ✅ Package-level errors
var (
    ErrNotFound     = errors.New("not found")
    ErrUnauthorized = errors.New("unauthorized")
)

// Can be compared with == or errors.Is
if err == ErrNotFound {
    // Handle
}
```

### 5. Return Early on Errors

```go
// ❌ Deep nesting
func process() error {
    if err := step1(); err == nil {
        if err := step2(); err == nil {
            if err := step3(); err == nil {
                return step4()
            } else {
                return err
            }
        } else {
            return err
        }
    } else {
        return err
    }
}

// ✅ Return early
func process() error {
    if err := step1(); err != nil {
        return err
    }
    if err := step2(); err != nil {
        return err
    }
    if err := step3(); err != nil {
        return err
    }
    return step4()
}
```

### 6. Don't Panic

```go
// ❌ Panic for normal errors
func divide(a, b int) int {
    if b == 0 {
        panic("division by zero")
    }
    return a / b
}

// ✅ Return error
func divide(a, b int) (int, error) {
    if b == 0 {
        return 0, errors.New("division by zero")
    }
    return a / b, nil
}
```

## Common Mistakes

### 1. Not Checking Errors

```go
// ❌ Ignoring error
data, _ := os.ReadFile("config.json")

// ✅ Check error
data, err := os.ReadFile("config.json")
if err != nil {
    log.Fatal(err)
}
```

### 2. Checking Error After Using Result

```go
// ❌ Using result before checking error
data, err := os.ReadFile("config.json")
fmt.Println(string(data))  // May be nil!
if err != nil {
    return err
}

// ✅ Check error first
data, err := os.ReadFile("config.json")
if err != nil {
    return err
}
fmt.Println(string(data))
```

### 3. Creating New Error Instead of Wrapping

```go
// ❌ Loses original error
if err != nil {
    return errors.New("failed to read file")
}

// ✅ Wrap original error
if err != nil {
    return fmt.Errorf("failed to read file: %w", err)
}
```

### 4. Comparing Errors with == After Wrapping

```go
err := fmt.Errorf("wrapped: %w", io.EOF)

// ❌ Won't work
if err == io.EOF {  // false!
    // ...
}

// ✅ Use errors.Is
if errors.Is(err, io.EOF) {  // true
    // ...
}
```

### 5. Not Handling All Error Cases

```go
// ❌ Specific error not handled
if err != nil {
    return err  // Always returns, never checks type
}

// ✅ Handle specific errors differently
if err != nil {
    if errors.Is(err, os.ErrNotExist) {
        // Create default config
        return createDefaultConfig()
    }
    return err
}
```

## Advanced Patterns

### 1. Error Types with Methods

```go
type NotFoundError struct {
    Resource string
    ID       int
}

func (e *NotFoundError) Error() string {
    return fmt.Sprintf("%s with ID %d not found", e.Resource, e.ID)
}

func (e *NotFoundError) IsNotFound() bool {
    return true
}

// Usage
func getUser(id int) (*User, error) {
    user := findUser(id)
    if user == nil {
        return nil, &NotFoundError{Resource: "User", ID: id}
    }
    return user, nil
}

// Check with type assertion
user, err := getUser(123)
if notFound, ok := err.(*NotFoundError); ok {
    fmt.Printf("Not found: %s %d\n", notFound.Resource, notFound.ID)
}
```

### 2. Error Wrapping with Stack Traces

```go
import "github.com/pkg/errors"

func deepFunction() error {
    return errors.New("something failed")
}

func middleFunction() error {
    err := deepFunction()
    if err != nil {
        return errors.Wrap(err, "middle failed")
    }
    return nil
}

func topFunction() error {
    err := middleFunction()
    if err != nil {
        return errors.Wrap(err, "top failed")
    }
    return nil
}

// Print with stack trace
err := topFunction()
fmt.Printf("%+v\n", err)
```

### 3. Deferred Error Handling

```go
func processFile(path string) (err error) {
    file, err := os.Open(path)
    if err != nil {
        return err
    }

    // Handle close error
    defer func() {
        if cerr := file.Close(); cerr != nil && err == nil {
            err = cerr
        }
    }()

    // Process file
    return nil
}
```

### 4. Error Channels

```go
func processItems(items []Item) error {
    errCh := make(chan error, len(items))

    for _, item := range items {
        go func(item Item) {
            errCh <- processItem(item)
        }(item)
    }

    // Collect errors
    var errs []error
    for range items {
        if err := <-errCh; err != nil {
            errs = append(errs, err)
        }
    }

    if len(errs) > 0 {
        return fmt.Errorf("processing failed: %v", errs)
    }
    return nil
}
```

## Comparison with PHP

| Feature | PHP | Go |
|---------|-----|-----|
| Error mechanism | Exceptions | Return values |
| Throw error | `throw new Exception()` | `return error` |
| Try/catch | `try { } catch { }` | `if err != nil { }` |
| Finally | `finally { }` | `defer` |
| Error types | Exception classes | error interface |
| Stack traces | Built-in | Third-party (pkg/errors) |
| Checked errors | No | No (but explicit returns) |
| Multiple returns | No | Yes (result, error) |
| Error wrapping | `previous` parameter | fmt.Errorf with %w |

## Next Steps

- **Chapter 09**: Packages & Modules - Organizing error handling across packages
- **Chapter 10**: Standard Library Tour - Common error patterns in stdlib
- **Chapter 26**: Unit Testing - Testing error cases

---

**Key Takeaway**: Go treats errors as values, not exceptions. Always check errors explicitly, add context when wrapping, and use sentinel errors for common cases. While more verbose than try/catch, this approach makes error handling paths explicit and maintainable.
