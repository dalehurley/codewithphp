# Chapter 10: Standard Library Tour

Explore Go's rich standard library. Discover the most important packages you'll use daily - from strings and time to JSON and HTTP. Learn what's built-in so you don't reinvent the wheel.

## Overview

Go's standard library is comprehensive and well-designed. Many things that require external packages in PHP (HTTP servers, JSON handling, testing) are built into Go. This chapter tours the essential packages every Go developer should know.

## Files in This Chapter

### 1. `01-strings-package.go`
**Topics**: strings.Contains, Split, Join, Replace, trimming, case conversion

### 2. `02-fmt-package.go`
**Topics**: Printf, Sprintf, Fprintf, format verbs, Scan functions

### 3. `03-time-package.go`
**Topics**: Time, Duration, parsing, formatting, timers, tickers

### 4. `04-json-package.go`
**Topics**: Marshal, Unmarshal, Encoder, Decoder, struct tags

### 5. `05-io-package.go`
**Topics**: Reader, Writer, Copy, ReadAll, file operations

### 6. `06-http-package.go`
**Topics**: HTTP server, client, handlers, middleware basics

## Quick Reference

### String Manipulation

**PHP**:
```php
$str = "Hello, World!";

// Contains
str_contains($str, "World");  // PHP 8+
strpos($str, "World") !== false;  // PHP 7

// Split/Join
$parts = explode(",", $str);
$joined = implode(",", $parts);

// Replace
str_replace("World", "PHP", $str);

// Case
strtoupper($str);
strtolower($str);

// Trim
trim($str);
ltrim($str);
rtrim($str);
```

**Go**:
```go
import "strings"

str := "Hello, World!"

// Contains
strings.Contains(str, "World")  // true

// Split/Join
parts := strings.Split(str, ",")
joined := strings.Join(parts, ",")

// Replace
strings.Replace(str, "World", "Go", 1)  // Replace once
strings.ReplaceAll(str, "World", "Go")  // Replace all

// Case
strings.ToUpper(str)
strings.ToLower(str)

// Trim
strings.TrimSpace(str)
strings.TrimLeft(str, " ")
strings.TrimRight(str, " ")
```

### JSON Handling

**PHP**:
```php
// Encode
$data = ['name' => 'Alice', 'age' => 30];
$json = json_encode($data);

// Decode
$obj = json_decode($json);
$array = json_decode($json, true);

// Error handling
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_last_error_msg();
}
```

**Go**:
```go
import "encoding/json"

// Encode (Marshal)
type Person struct {
    Name string `json:"name"`
    Age  int    `json:"age"`
}

person := Person{Name: "Alice", Age: 30}
jsonData, err := json.Marshal(person)
// {"name":"Alice","age":30}

// Decode (Unmarshal)
var p Person
err = json.Unmarshal(jsonData, &p)
if err != nil {
    log.Fatal(err)
}
```

## Key Standard Library Packages

### 1. strings Package

```go
import "strings"

// Searching
strings.Contains("hello", "ll")     // true
strings.HasPrefix("hello", "he")    // true
strings.HasSuffix("hello", "lo")    // true
strings.Index("hello", "ll")        // 2
strings.LastIndex("hello", "l")     // 3
strings.Count("cheese", "e")        // 3

// Splitting/Joining
parts := strings.Split("a,b,c", ",")        // ["a", "b", "c"]
joined := strings.Join([]string{"a", "b"}, ",")  // "a,b"
fields := strings.Fields("  a  b  c  ")     // ["a", "b", "c"]

// Replacing
strings.Replace("oink oink", "oink", "moo", 1)  // "moo oink"
strings.ReplaceAll("oink oink", "oink", "moo")  // "moo moo"

// Case conversion
strings.ToUpper("hello")      // "HELLO"
strings.ToLower("HELLO")      // "hello"
strings.Title("hello world")  // "Hello World"

// Trimming
strings.TrimSpace("  hello  ")           // "hello"
strings.Trim("!!!hello!!!", "!")         // "hello"
strings.TrimPrefix("hello", "he")        // "llo"
strings.TrimSuffix("hello", "lo")        // "hel"

// Building strings
var builder strings.Builder
builder.WriteString("Hello")
builder.WriteString(" ")
builder.WriteString("World")
result := builder.String()  // "Hello World"
```

### 2. fmt Package

```go
import "fmt"

// Printing
fmt.Print("Hello")           // No newline
fmt.Println("Hello")         // With newline
fmt.Printf("Hello %s\n", "World")  // Formatted

// String formatting
s := fmt.Sprintf("Hello %s", "World")

// Common format verbs
fmt.Printf("%v", value)    // Default format
fmt.Printf("%+v", value)   // With field names (structs)
fmt.Printf("%#v", value)   // Go syntax representation
fmt.Printf("%T", value)    // Type
fmt.Printf("%t", bool)     // Boolean
fmt.Printf("%d", int)      // Integer
fmt.Printf("%f", float)    // Float
fmt.Printf("%s", string)   // String
fmt.Printf("%p", pointer)  // Pointer address
fmt.Printf("%q", string)   // Quoted string

// Examples
type Person struct {
    Name string
    Age  int
}
p := Person{Name: "Alice", Age: 30}

fmt.Printf("%v\n", p)   // {Alice 30}
fmt.Printf("%+v\n", p)  // {Name:Alice Age:30}
fmt.Printf("%#v\n", p)  // main.Person{Name:"Alice", Age:30}
```

### 3. time Package

```go
import "time"

// Current time
now := time.Now()

// Creating time
date := time.Date(2024, time.January, 15, 14, 30, 0, 0, time.UTC)

// Parsing
layout := "2006-01-02"  // Reference time!
t, err := time.Parse(layout, "2024-01-15")

// Common layouts
time.RFC3339      // "2006-01-02T15:04:05Z07:00"
time.RFC822       // "02 Jan 06 15:04 MST"
time.Kitchen      // "3:04PM"

// Formatting
formatted := now.Format("2006-01-02 15:04:05")

// Duration
duration := 5 * time.Second
duration := time.Hour + 30*time.Minute

// Time arithmetic
tomorrow := now.Add(24 * time.Hour)
yesterday := now.Add(-24 * time.Hour)

// Time comparison
if tomorrow.After(now) {
    fmt.Println("Tomorrow is after now")
}

// Sleep
time.Sleep(time.Second)

// Timer
timer := time.NewTimer(5 * time.Second)
<-timer.C  // Wait for timer

// Ticker
ticker := time.NewTicker(time.Second)
defer ticker.Stop()

for range ticker.C {
    fmt.Println("Tick")
}
```

### 4. encoding/json Package

```go
import "encoding/json"

// Marshal (Go → JSON)
type User struct {
    ID    int    `json:"id"`
    Name  string `json:"name"`
    Email string `json:"email,omitempty"`  // Omit if empty
    Pass  string `json:"-"`                 // Never serialize
}

user := User{ID: 1, Name: "Alice"}
data, err := json.Marshal(user)
// {"id":1,"name":"Alice"}

// Pretty print
data, err := json.MarshalIndent(user, "", "  ")

// Unmarshal (JSON → Go)
jsonStr := `{"id":1,"name":"Alice","email":"alice@example.com"}`
var u User
err := json.Unmarshal([]byte(jsonStr), &u)

// Encoding to writer
encoder := json.NewEncoder(os.Stdout)
encoder.Encode(user)

// Decoding from reader
decoder := json.NewDecoder(resp.Body)
var result map[string]interface{}
err := decoder.Decode(&result)

// Working with raw JSON
var raw map[string]interface{}
json.Unmarshal(data, &raw)
```

### 5. io Package

```go
import "io"

// Reader interface
type Reader interface {
    Read(p []byte) (n int, err error)
}

// Writer interface
type Writer interface {
    Write(p []byte) (n int, err error)
}

// Copy data
io.Copy(dst, src)

// Read all data
data, err := io.ReadAll(reader)

// Limit reader
limited := io.LimitReader(reader, 1024)  // Max 1KB

// Multi-reader
combined := io.MultiReader(reader1, reader2)

// Tee reader (read and copy)
tee := io.TeeReader(reader, writer)

// Pipe
pr, pw := io.Pipe()
go func() {
    pw.Write([]byte("data"))
    pw.Close()
}()
io.Copy(os.Stdout, pr)
```

### 6. net/http Package

```go
import "net/http"

// HTTP Server
http.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
    fmt.Fprintf(w, "Hello, World!")
})
http.ListenAndServe(":8080", nil)

// HTTP Client
resp, err := http.Get("https://api.example.com/users")
if err != nil {
    log.Fatal(err)
}
defer resp.Body.Close()

body, err := io.ReadAll(resp.Body)

// POST request
jsonData := []byte(`{"name":"Alice"}`)
resp, err := http.Post(
    "https://api.example.com/users",
    "application/json",
    bytes.NewBuffer(jsonData),
)

// Custom request
req, err := http.NewRequest("PUT", url, body)
req.Header.Set("Content-Type", "application/json")
req.Header.Set("Authorization", "Bearer "+token)

client := &http.Client{Timeout: 10 * time.Second}
resp, err := client.Do(req)
```

## Common Patterns

### 1. Working with Files

```go
import (
    "io"
    "os"
)

// Read entire file
data, err := os.ReadFile("file.txt")

// Write file
err := os.WriteFile("file.txt", data, 0644)

// Open file
file, err := os.Open("file.txt")
defer file.Close()

// Read with buffer
buf := make([]byte, 1024)
n, err := file.Read(buf)

// Copy files
src, err := os.Open("source.txt")
defer src.Close()

dst, err := os.Create("dest.txt")
defer dst.Close()

io.Copy(dst, src)

// File info
info, err := os.Stat("file.txt")
fmt.Println(info.Size())
fmt.Println(info.ModTime())
fmt.Println(info.IsDir())
```

### 2. Path Manipulation

```go
import "path/filepath"

// Join paths
path := filepath.Join("dir", "subdir", "file.txt")
// "dir/subdir/file.txt"

// Split
dir, file := filepath.Split(path)

// Extension
ext := filepath.Ext("file.txt")  // ".txt"

// Base name
base := filepath.Base("/path/to/file.txt")  // "file.txt"

// Directory
dir := filepath.Dir("/path/to/file.txt")  // "/path/to"

// Absolute path
abs, err := filepath.Abs("relative/path")

// Walk directory tree
filepath.Walk("dir", func(path string, info os.FileInfo, err error) error {
    if err != nil {
        return err
    }
    fmt.Println(path)
    return nil
})
```

### 3. Regular Expressions

```go
import "regexp"

// Compile pattern
re := regexp.MustCompile(`\d+`)

// Match
matched := re.MatchString("abc123")  // true

// Find
result := re.FindString("abc123def456")  // "123"

// Find all
results := re.FindAllString("abc123def456", -1)  // ["123", "456"]

// Replace
replaced := re.ReplaceAllString("abc123", "X")  // "abcX"

// Named groups
re := regexp.MustCompile(`(?P<name>\w+)@(?P<domain>\w+\.\w+)`)
matches := re.FindStringSubmatch("user@example.com")
// matches[0] = "user@example.com"
// matches[1] = "user"
// matches[2] = "example.com"
```

### 4. Command-Line Flags

```go
import "flag"

// Define flags
var (
    host = flag.String("host", "localhost", "Server host")
    port = flag.Int("port", 8080, "Server port")
    debug = flag.Bool("debug", false, "Enable debug mode")
)

func main() {
    flag.Parse()

    fmt.Printf("Host: %s\n", *host)
    fmt.Printf("Port: %d\n", *port)
    fmt.Printf("Debug: %t\n", *debug)

    // Remaining args
    args := flag.Args()
}

// Usage:
// go run main.go -host=0.0.0.0 -port=3000 -debug
```

### 5. Context Package

```go
import "context"

// Background context
ctx := context.Background()

// With timeout
ctx, cancel := context.WithTimeout(context.Background(), 5*time.Second)
defer cancel()

// With deadline
deadline := time.Now().Add(10 * time.Second)
ctx, cancel := context.WithDeadline(context.Background(), deadline)
defer cancel()

// With cancellation
ctx, cancel := context.WithCancel(context.Background())
defer cancel()

// With values
ctx := context.WithValue(context.Background(), "key", "value")
value := ctx.Value("key")

// Use in HTTP request
req, err := http.NewRequestWithContext(ctx, "GET", url, nil)
```

### 6. Sorting

```go
import "sort"

// Sort slice of ints
nums := []int{3, 1, 4, 1, 5, 9}
sort.Ints(nums)  // [1, 1, 3, 4, 5, 9]

// Sort slice of strings
strs := []string{"c", "a", "b"}
sort.Strings(strs)  // ["a", "b", "c"]

// Custom sort
type Person struct {
    Name string
    Age  int
}

people := []Person{
    {"Alice", 30},
    {"Bob", 25},
    {"Charlie", 35},
}

// Sort by age
sort.Slice(people, func(i, j int) bool {
    return people[i].Age < people[j].Age
})

// Check if sorted
isSorted := sort.IntsAreSorted(nums)

// Binary search
index := sort.SearchInts(nums, 4)
```

## Best Practices

### 1. Use io.Reader and io.Writer

```go
// ✅ Accept interfaces
func processData(r io.Reader) error {
    data, err := io.ReadAll(r)
    // Can work with files, network, strings, etc.
}

// ❌ Too specific
func processData(filename string) error {
    // Only works with files
}
```

### 2. Close Resources with defer

```go
// ✅ Always defer Close()
file, err := os.Open("file.txt")
if err != nil {
    return err
}
defer file.Close()

resp, err := http.Get(url)
if err != nil {
    return err
}
defer resp.Body.Close()
```

### 3. Use context for Cancellation

```go
// ✅ Accept context
func fetchData(ctx context.Context, url string) ([]byte, error) {
    req, err := http.NewRequestWithContext(ctx, "GET", url, nil)
    if err != nil {
        return nil, err
    }
    // Request can be cancelled via context
}
```

### 4. Read Files Efficiently

```go
// ✅ For small files
data, err := os.ReadFile("small.txt")

// ✅ For large files (streaming)
file, err := os.Open("large.txt")
defer file.Close()

scanner := bufio.NewScanner(file)
for scanner.Scan() {
    line := scanner.Text()
    // Process line by line
}
```

## Common Mistakes

### 1. Not Closing Response Body

```go
// ❌ Body not closed (leak!)
resp, _ := http.Get(url)

// ✅ Always close
resp, err := http.Get(url)
if err != nil {
    return err
}
defer resp.Body.Close()
```

### 2. Ignoring Errors from Close()

```go
// ❌ Might lose data
file.Write(data)
file.Close()

// ✅ Check error
if _, err := file.Write(data); err != nil {
    return err
}
if err := file.Close(); err != nil {
    return err
}
```

### 3. Wrong Time Format

```go
// ❌ Wrong reference time
t.Format("YYYY-MM-DD")  // Wrong!

// ✅ Use Go's reference time: Mon Jan 2 15:04:05 MST 2006
t.Format("2006-01-02")
```

### 4. Modifying Slice During Range

```go
// ❌ Modifying during iteration
for _, v := range slice {
    slice = append(slice, v*2)  // Dangerous!
}

// ✅ Use index or copy
for i := 0; i < len(slice); i++ {
    slice = append(slice, slice[i]*2)
}
```

## Package Comparison

### PHP vs Go Standard Library

| Task | PHP | Go |
|------|-----|-----|
| Strings | String functions | `strings` package |
| Arrays | Array functions | `slices` package (Go 1.21+) |
| JSON | `json_encode/decode` | `encoding/json` |
| HTTP Server | External (Apache/Nginx) | `net/http` |
| HTTP Client | `file_get_contents`, cURL | `net/http` |
| Date/Time | DateTime class | `time` package |
| Files | `file_get_contents`, etc | `os`, `io` |
| Regex | PCRE functions | `regexp` |
| Testing | PHPUnit (external) | `testing` package |
| Database | PDO | `database/sql` |
| Logging | error_log, Monolog | `log`, `slog` |
| Templates | Twig (external) | `html/template` |
| Crypto | OpenSSL extension | `crypto/*` packages |
| Hashing | `hash()` | `crypto/sha256`, etc |

## Useful Packages Not Covered

### Standard Library

```go
// Compression
import "compress/gzip"
import "compress/zip"

// Crypto
import "crypto/md5"
import "crypto/sha256"
import "crypto/aes"

// Templates
import "html/template"
import "text/template"

// Database
import "database/sql"

// Testing
import "testing"

// Reflection
import "reflect"

// Math
import "math"
import "math/rand"

// Binary encoding
import "encoding/base64"
import "encoding/hex"

// CSV
import "encoding/csv"

// XML
import "encoding/xml"

// URL parsing
import "net/url"
```

## Next Steps

- **Chapter 11**: Goroutines Fundamentals - Concurrent programming basics
- **Chapter 12**: Channels & Communication - Goroutine communication
- **Chapter 16**: HTTP Server Basics - Deep dive into net/http
- **Chapter 21**: Database/SQL Package - Working with databases

---

**Key Takeaway**: Go's standard library is comprehensive and production-ready. Most things that require external packages in PHP (HTTP servers, JSON, testing) are built into Go. Learn the standard library well - it's your foundation for Go development.
