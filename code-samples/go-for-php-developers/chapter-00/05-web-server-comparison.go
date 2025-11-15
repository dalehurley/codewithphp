package main

import (
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"time"
)

/*
PHP VERSION (index.php):
<?php
header('Content-Type: application/json');

$uri = $_SERVER['REQUEST_URI'];

if ($uri === '/') {
    echo "Hello from PHP!";
} elseif ($uri === '/api/users') {
    echo json_encode(['users' => ['Alice', 'Bob', 'Charlie']]);
} elseif ($uri === '/time') {
    echo json_encode(['time' => date('Y-m-d H:i:s')]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
}

// Requires: Apache/Nginx + PHP-FPM
// Deployment: Upload .php files to server
// Process model: One process per request (or FPM pool)
*/

/*
GO VERSION: Everything below!

Key differences:
1. Built-in HTTP server (no Apache/Nginx/PHP-FPM needed)
2. Compiled to single binary
3. Each request runs in its own goroutine (lightweight thread)
4. Handles thousands of concurrent requests easily
5. No web server configuration needed
*/

// Handler functions (like PHP route handlers)

// Homepage handler
func homeHandler(w http.ResponseWriter, r *http.Request) {
	fmt.Fprintf(w, "Hello from Go!\n")
	fmt.Fprintf(w, "Request method: %s\n", r.Method)
	fmt.Fprintf(w, "Request URL: %s\n", r.URL.Path)
}

// JSON API handler - like PHP's json_encode()
func usersHandler(w http.ResponseWriter, r *http.Request) {
	users := []string{"Alice", "Bob", "Charlie"}

	// Set JSON content type
	w.Header().Set("Content-Type", "application/json")

	// Encode and send JSON
	json.NewEncoder(w).Encode(map[string]interface{}{
		"users": users,
		"count": len(users),
	})
}

// Time API handler
func timeHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	json.NewEncoder(w).Encode(map[string]interface{}{
		"time":      time.Now().Format("2006-01-02 15:04:05"),
		"timestamp": time.Now().Unix(),
	})
}

// User struct for JSON response
type User struct {
	ID    int    `json:"id"`
	Name  string `json:"name"`
	Email string `json:"email"`
}

// RESTful JSON API handler
func apiUserHandler(w http.ResponseWriter, r *http.Request) {
	user := User{
		ID:    1,
		Name:  "Alice",
		Email: "alice@example.com",
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(user)
}

// 404 handler
func notFoundHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusNotFound)

	json.NewEncoder(w).Encode(map[string]interface{}{
		"error":  "Not found",
		"path":   r.URL.Path,
		"method": r.Method,
	})
}

// Middleware example (like PHP middleware)
func loggingMiddleware(next http.HandlerFunc) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		start := time.Now()

		// Log request
		fmt.Printf("[%s] %s %s\n", time.Now().Format("15:04:05"), r.Method, r.URL.Path)

		// Call next handler
		next(w, r)

		// Log duration
		fmt.Printf("Request took: %v\n", time.Since(start))
	}
}

func main() {
	fmt.Println("=== Go Web Server vs PHP ===\n")

	// Register routes (like PHP routing)
	http.HandleFunc("/", loggingMiddleware(homeHandler))
	http.HandleFunc("/api/users", loggingMiddleware(usersHandler))
	http.HandleFunc("/api/time", loggingMiddleware(timeHandler))
	http.HandleFunc("/api/user", loggingMiddleware(apiUserHandler))

	// Custom 404 handler
	http.HandleFunc("/404", notFoundHandler)

	// Server configuration
	server := &http.Server{
		Addr:         ":8080",
		ReadTimeout:  10 * time.Second,
		WriteTimeout: 10 * time.Second,
		IdleTimeout:  120 * time.Second,
	}

	fmt.Println("Server starting on http://localhost:8080")
	fmt.Println("\nTry these endpoints:")
	fmt.Println("  http://localhost:8080/")
	fmt.Println("  http://localhost:8080/api/users")
	fmt.Println("  http://localhost:8080/api/time")
	fmt.Println("  http://localhost:8080/api/user")
	fmt.Println("  http://localhost:8080/404")
	fmt.Println("\nPress Ctrl+C to stop")
	fmt.Println()

	// Start server (blocking call)
	if err := server.ListenAndServe(); err != nil {
		log.Fatal(err)
	}
}

/*
RUNNING THE SERVER:

1. Compile and run:
   $ go run 05-web-server-comparison.go

2. Or build binary:
   $ go build -o webserver 05-web-server-comparison.go
   $ ./webserver

3. Test endpoints:
   $ curl http://localhost:8080/
   $ curl http://localhost:8080/api/users
   $ curl http://localhost:8080/api/time
   $ curl http://localhost:8080/api/user

EXAMPLE OUTPUT (server logs):
=== Go Web Server vs PHP ===

Server starting on http://localhost:8080

Try these endpoints:
  http://localhost:8080/
  http://localhost:8080/api/users
  http://localhost:8080/api/time
  http://localhost:8080/api/user
  http://localhost:8080/404

Press Ctrl+C to stop

[10:30:15] GET /
Request took: 45.2µs
[10:30:18] GET /api/users
Request took: 123.4µs
[10:30:20] GET /api/time
Request took: 89.1µs
*/

/*
KEY DIFFERENCES FROM PHP:

1. WEB SERVER:
   PHP: Requires Apache/Nginx + PHP-FPM
   Go:  Built-in HTTP server in standard library

2. DEPLOYMENT:
   PHP: Upload .php files + configure web server
   Go:  Single compiled binary, just run it

3. ROUTING:
   PHP: .htaccess or web server config
   Go:  http.HandleFunc() or router package

4. JSON:
   PHP: json_encode($data)
   Go:  json.NewEncoder(w).Encode(data)

5. CONCURRENCY:
   PHP: One process/thread per request
   Go:  One goroutine per request (thousands possible)

6. PERFORMANCE:
   PHP: ~10k requests/second (FPM)
   Go:  ~50k+ requests/second (built-in)

ADVANTAGES OF GO WEB SERVERS:

1. No external dependencies
   - No Apache, Nginx, PHP-FPM needed
   - Just run the binary

2. Better performance
   - Compiled code
   - Efficient concurrency
   - Lower memory usage

3. Simpler deployment
   - Single binary
   - No PHP version conflicts
   - No extension dependencies

4. Built-in features
   - HTTP/2 support
   - TLS/SSL
   - Timeouts
   - Graceful shutdown

COMMON PATTERNS:

1. Handler function:
   func handler(w http.ResponseWriter, r *http.Request) {
       fmt.Fprintf(w, "Hello!")
   }

2. JSON response:
   w.Header().Set("Content-Type", "application/json")
   json.NewEncoder(w).Encode(data)

3. Read JSON body:
   var data MyStruct
   json.NewDecoder(r.Body).Decode(&data)

4. Query parameters:
   name := r.URL.Query().Get("name")

5. URL path variables (need router):
   // Use gorilla/mux or chi for path variables
   vars := mux.Vars(r)
   id := vars["id"]

PRODUCTION CONSIDERATIONS:

1. Use a router library:
   - gorilla/mux
   - chi
   - gin
   - echo

2. Add middleware:
   - Logging
   - Authentication
   - CORS
   - Rate limiting

3. Configure timeouts:
   ReadTimeout, WriteTimeout, IdleTimeout

4. Graceful shutdown:
   server.Shutdown(ctx)

5. TLS/HTTPS:
   server.ListenAndServeTLS(cert, key)

REAL-WORLD DEPLOYMENT:

1. Build for Linux:
   GOOS=linux GOARCH=amd64 go build -o app

2. Copy binary to server:
   scp app user@server:/opt/app/

3. Run with systemd:
   systemctl start myapp

4. Or use Docker:
   FROM golang:1.21 AS builder
   COPY . .
   RUN go build -o app

   FROM alpine
   COPY --from=builder /app /app
   CMD ["/app"]

NEXT STEPS:
- Run the server and test endpoints
- Modify handlers to return different data
- Add new routes
- Experiment with JSON encoding/decoding
- Compare performance with PHP version

This is just the beginning!
Web frameworks (Gin, Echo, Fiber) make this even easier.
See Chapter 20 for framework comparisons.
*/
