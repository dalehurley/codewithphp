package main

/*
 * Simple HTTP Server in Go - Chapter 16, Example 1
 *
 * PHP COMPARISON:
 * In PHP, you typically rely on an external web server (Apache, Nginx) combined with
 * PHP-FPM to handle HTTP requests. You write scripts that respond to requests, but
 * the server infrastructure is separate from your code.
 *
 * Example PHP with Apache:
 * - Apache/Nginx handles HTTP protocol
 * - PHP-FPM processes PHP scripts
 * - .htaccess or nginx.conf for routing
 * - php.ini for configuration
 *
 * In Go, the HTTP server is BUILT INTO your application. You don't need Apache or Nginx
 * (though you can use them as reverse proxies). The net/http package provides a
 * production-ready HTTP server that's:
 * - Fast and concurrent
 * - Built-in TLS/HTTPS support
 * - No external dependencies
 * - Full control over server behavior
 *
 * This is similar to using frameworks like Laravel Octane, Swoole, or ReactPHP, but
 * it's the standard way to write web apps in Go, not an alternative runtime.
 */

import (
	"context"
	"fmt"
	"io"
	"log"
	"net/http"
	"os"
	"os/signal"
	"syscall"
	"time"
)

// ServerConfig holds configuration for our HTTP server
// In PHP frameworks like Laravel, this would be in config/server.php or .env
type ServerConfig struct {
	Host         string
	Port         string
	ReadTimeout  time.Duration
	WriteTimeout time.Duration
	IdleTimeout  time.Duration
}

// NewServerConfig creates a default server configuration
// Similar to Laravel's config() helper or Symfony's parameters.yml
func NewServerConfig() *ServerConfig {
	return &ServerConfig{
		Host:         "localhost",
		Port:         "8080",
		ReadTimeout:  15 * time.Second,
		WriteTimeout: 15 * time.Second,
		IdleTimeout:  60 * time.Second,
	}
}

// SimpleHandler is a basic HTTP handler function
// In PHP, this would be like a simple index.php file:
//
// <?php
// echo "Hello, World!";
//
// But in Go, handlers receive two parameters:
// - http.ResponseWriter: for writing the response (like echo or Response object)
// - *http.Request: contains all request information (like $_SERVER, $_GET, $_POST)
func SimpleHandler(w http.ResponseWriter, r *http.Request) {
	// Set content type header
	// In PHP: header('Content-Type: text/html; charset=utf-8');
	w.Header().Set("Content-Type", "text/html; charset=utf-8")

	// Write status code (200 OK is automatic if you don't call this)
	// In PHP: http_response_code(200);
	w.WriteHeader(http.StatusOK)

	// Write response body
	// In PHP: echo "Hello, World!";
	fmt.Fprintf(w, "<h1>Hello, World!</h1>")
	fmt.Fprintf(w, "<p>This is a simple Go HTTP server.</p>")
}

// InfoHandler displays request information
// Similar to phpinfo() but for request details
// In PHP, you might use $_SERVER, $_GET, $_POST, getallheaders(), etc.
func InfoHandler(w http.ResponseWriter, r *http.Request) {
	// Set content type
	w.Header().Set("Content-Type", "text/html; charset=utf-8")

	// Start HTML response
	io.WriteString(w, "<html><head><title>Request Info</title></head><body>")
	io.WriteString(w, "<h1>Request Information</h1>")

	// Method - equivalent to PHP's $_SERVER['REQUEST_METHOD']
	fmt.Fprintf(w, "<p><strong>Method:</strong> %s</p>", r.Method)

	// URL path - equivalent to PHP's $_SERVER['REQUEST_URI']
	fmt.Fprintf(w, "<p><strong>Path:</strong> %s</p>", r.URL.Path)

	// Query string - equivalent to PHP's $_SERVER['QUERY_STRING']
	fmt.Fprintf(w, "<p><strong>Query String:</strong> %s</p>", r.URL.RawQuery)

	// Protocol - equivalent to PHP's $_SERVER['SERVER_PROTOCOL']
	fmt.Fprintf(w, "<p><strong>Protocol:</strong> %s</p>", r.Proto)

	// Remote address - equivalent to PHP's $_SERVER['REMOTE_ADDR']
	fmt.Fprintf(w, "<p><strong>Remote Address:</strong> %s</p>", r.RemoteAddr)

	// Host - equivalent to PHP's $_SERVER['HTTP_HOST']
	fmt.Fprintf(w, "<p><strong>Host:</strong> %s</p>", r.Host)

	// Headers - equivalent to PHP's getallheaders()
	io.WriteString(w, "<h2>Headers</h2><ul>")
	for name, values := range r.Header {
		for _, value := range values {
			fmt.Fprintf(w, "<li><strong>%s:</strong> %s</li>", name, value)
		}
	}
	io.WriteString(w, "</ul>")

	// Query parameters - equivalent to PHP's $_GET
	if len(r.URL.Query()) > 0 {
		io.WriteString(w, "<h2>Query Parameters</h2><ul>")
		for key, values := range r.URL.Query() {
			for _, value := range values {
				fmt.Fprintf(w, "<li><strong>%s:</strong> %s</li>", key, value)
			}
		}
		io.WriteString(w, "</ul>")
	}

	io.WriteString(w, "</body></html>")
}

// MethodHandler demonstrates handling different HTTP methods
// In PHP, you might check $_SERVER['REQUEST_METHOD'] and handle differently:
//
// <?php
// switch ($_SERVER['REQUEST_METHOD']) {
//     case 'GET':
//         // Handle GET
//         break;
//     case 'POST':
//         // Handle POST
//         break;
// }
func MethodHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "text/html; charset=utf-8")

	switch r.Method {
	case http.MethodGet:
		// Handle GET request
		io.WriteString(w, "<h1>GET Request</h1>")
		io.WriteString(w, "<p>This is a GET request handler.</p>")
		io.WriteString(w, "<form method='POST' action='/method'>")
		io.WriteString(w, "<button type='submit'>Send POST Request</button>")
		io.WriteString(w, "</form>")

	case http.MethodPost:
		// Handle POST request
		// In PHP: $_POST is automatically populated
		// In Go: You must explicitly parse the form
		if err := r.ParseForm(); err != nil {
			http.Error(w, "Failed to parse form", http.StatusBadRequest)
			return
		}

		io.WriteString(w, "<h1>POST Request</h1>")
		io.WriteString(w, "<p>This is a POST request handler.</p>")
		io.WriteString(w, "<a href='/method'>Try GET Request</a>")

	case http.MethodPut:
		io.WriteString(w, "<h1>PUT Request</h1>")

	case http.MethodDelete:
		io.WriteString(w, "<h1>DELETE Request</h1>")

	default:
		// Method not allowed
		// In PHP: http_response_code(405);
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
	}
}

// RedirectHandler demonstrates HTTP redirects
// In PHP: header('Location: /destination'); exit;
// Or with Laravel: return redirect('/destination');
func RedirectHandler(w http.ResponseWriter, r *http.Request) {
	// Temporary redirect (302) - PHP: header('Location: ...', true, 302);
	// http.Redirect(w, r, "/", http.StatusFound)

	// Permanent redirect (301) - more common for SEO
	// In Laravel: return redirect('/destination', 301);
	http.Redirect(w, r, "/", http.StatusMovedPermanently)
}

// ErrorHandler demonstrates error responses
// In PHP frameworks like Laravel:
// - abort(404) or abort(500, 'Error message')
// - Or manual: http_response_code(404); echo 'Not Found';
func ErrorHandler(w http.ResponseWriter, r *http.Request) {
	// Get error type from query parameter
	errorType := r.URL.Query().Get("type")

	switch errorType {
	case "404":
		// Not Found
		// In Laravel: abort(404, 'Page not found');
		http.Error(w, "Page not found", http.StatusNotFound)

	case "500":
		// Internal Server Error
		// In Laravel: abort(500, 'Server error');
		http.Error(w, "Internal server error", http.StatusInternalServerError)

	case "403":
		// Forbidden
		// In Laravel: abort(403, 'Forbidden');
		http.Error(w, "Forbidden", http.StatusForbidden)

	default:
		// Custom error response with JSON
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusBadRequest)
		fmt.Fprintf(w, `{"error": "Bad request", "message": "Invalid error type"}`)
	}
}

// LoggingHandler wraps another handler with logging
// This is a simple example of middleware pattern
// In Laravel, this would be like a middleware:
//
// class LogRequests {
//     public function handle($request, Closure $next) {
//         Log::info('Request: ' . $request->path());
//         return $next($request);
//     }
// }
func LoggingHandler(next http.HandlerFunc) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		start := time.Now()

		// Call the next handler
		next(w, r)

		// Log after handling
		log.Printf(
			"%s %s %s %v",
			r.Method,
			r.RequestURI,
			r.RemoteAddr,
			time.Since(start),
		)
	}
}

// setupRoutes configures all routes for our application
// In PHP frameworks:
// - Laravel: routes/web.php with Route::get(), Route::post(), etc.
// - Symfony: config/routes.yaml or annotations
func setupRoutes() {
	// Register routes with handlers
	// This is similar to Laravel's Route::get('/path', 'Controller@method')

	// Simple handler
	http.HandleFunc("/", LoggingHandler(SimpleHandler))

	// Info handler to display request details
	http.HandleFunc("/info", LoggingHandler(InfoHandler))

	// Method-specific handler
	http.HandleFunc("/method", LoggingHandler(MethodHandler))

	// Redirect handler
	http.HandleFunc("/redirect", RedirectHandler)

	// Error handler
	http.HandleFunc("/error", LoggingHandler(ErrorHandler))

	// Static file serving (like public directory in Laravel)
	// In PHP: Files in public/ are served automatically by Apache/Nginx
	// In Go: You must explicitly set up static file serving
	// Uncomment if you have a public directory:
	// fs := http.FileServer(http.Dir("./public"))
	// http.Handle("/static/", http.StripPrefix("/static/", fs))
}

// gracefulShutdown handles SIGINT and SIGTERM signals
// This allows the server to finish handling requests before shutting down
// In PHP with FPM, this is handled by the process manager
// In Go, you manage the entire lifecycle
func gracefulShutdown(server *http.Server) {
	// Create channel to listen for signals
	sigChan := make(chan os.Signal, 1)

	// Register for SIGINT (Ctrl+C) and SIGTERM signals
	signal.Notify(sigChan, os.Interrupt, syscall.SIGTERM)

	// Block until signal received
	sig := <-sigChan
	log.Printf("Received signal: %v. Shutting down gracefully...", sig)

	// Give outstanding requests 30 seconds to complete
	// This is like PHP-FPM's process_control_timeout
	shutdownCtx, cancel := context.WithTimeout(context.Background(), 30*time.Second)
	defer cancel()

	// Shutdown the server
	if err := server.Shutdown(shutdownCtx); err != nil {
		log.Printf("Server shutdown error: %v", err)
	} else {
		log.Println("Server stopped gracefully")
	}
}

func main() {
	// Get server configuration
	config := NewServerConfig()

	// Setup routes
	setupRoutes()

	// Create HTTP server
	// This is the equivalent of configuring Apache/Nginx + PHP-FPM
	// But it's all in your Go code!
	server := &http.Server{
		Addr: config.Host + ":" + config.Port,

		// Timeouts prevent slow clients from holding connections
		// In PHP, these are configured in php.ini or FPM config:
		// - max_execution_time
		// - request_terminate_timeout
		ReadTimeout:  config.ReadTimeout,
		WriteTimeout: config.WriteTimeout,
		IdleTimeout:  config.IdleTimeout,

		// Maximum header size (default is 1MB)
		// In PHP: this is in php.ini as post_max_size or upload_max_filesize
		MaxHeaderBytes: 1 << 20, // 1 MB
	}

	// Start graceful shutdown handler in background
	go gracefulShutdown(server)

	// Print startup information
	log.Printf("Starting HTTP server on http://%s:%s", config.Host, config.Port)
	log.Println("Available routes:")
	log.Println("  GET  /           - Simple hello world")
	log.Println("  GET  /info       - Request information (like phpinfo)")
	log.Println("  GET  /method     - Method handling demo")
	log.Println("  POST /method     - Method handling demo")
	log.Println("  GET  /redirect   - Redirect example")
	log.Println("  GET  /error?type=404 - Error handling (404, 500, 403)")
	log.Println("\nPress Ctrl+C to stop the server")

	// Start the server
	// In PHP, you'd start Apache/Nginx and PHP-FPM separately
	// In Go, this one call starts a production-ready HTTP server
	if err := server.ListenAndServe(); err != nil && err != http.ErrServerClosed {
		log.Fatalf("Server failed to start: %v", err)
	}
}

/*
 * KEY TAKEAWAYS FOR PHP DEVELOPERS:
 *
 * 1. NO EXTERNAL WEB SERVER NEEDED
 *    - PHP: Apache/Nginx + PHP-FPM
 *    - Go: Built-in HTTP server in your application
 *
 * 2. EXPLICIT ROUTING
 *    - PHP: File-based routing (index.php, about.php) or framework routes
 *    - Go: Explicit route registration with http.HandleFunc()
 *
 * 3. REQUEST/RESPONSE HANDLING
 *    - PHP: Global variables ($_GET, $_POST, $_SERVER) and echo/print
 *    - Go: http.Request parameter and http.ResponseWriter interface
 *
 * 4. LIFECYCLE CONTROL
 *    - PHP: Process manager (FPM) controls lifecycle
 *    - Go: You control the entire server lifecycle
 *
 * 5. CONCURRENCY
 *    - PHP: One request per process/thread (blocking)
 *    - Go: Goroutines handle each request (thousands of concurrent requests)
 *
 * 6. CONFIGURATION
 *    - PHP: php.ini, .htaccess, FPM config files
 *    - Go: Programmatic configuration in your code
 *
 * 7. DEPLOYMENT
 *    - PHP: Deploy code, configure Apache/Nginx, reload
 *    - Go: Compile to binary, deploy binary, run (no dependencies)
 *
 * HOW TO RUN THIS CODE:
 *
 * 1. Save this file as simple-http-server.go
 * 2. Run: go run simple-http-server.go
 * 3. Open browser to http://localhost:8080
 * 4. Try different routes: /info, /method, /error?type=404
 * 5. Press Ctrl+C to stop gracefully
 *
 * FOR PRODUCTION:
 *
 * 1. Compile: go build -o server simple-http-server.go
 * 2. Run: ./server
 * 3. Optional: Put Nginx in front as reverse proxy (for SSL, load balancing)
 * 4. Use systemd or supervisor to manage the process
 *
 * PERFORMANCE NOTES:
 *
 * This simple Go server can handle 10,000+ requests per second on modest hardware.
 * Compare that to PHP-FPM which typically handles 100-500 requests per second.
 * The built-in HTTP server is production-ready and used by companies like
 * Google, Uber, and Netflix.
 */
