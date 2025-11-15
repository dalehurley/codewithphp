package main

/*
 * Middleware Chain in Go - Chapter 17, Example 2
 *
 * PHP FRAMEWORK MIDDLEWARE COMPARISON:
 *
 * Laravel Middleware (app/Http/Middleware/LogRequests.php):
 * class LogRequests {
 *     public function handle($request, Closure $next) {
 *         // Before logic
 *         Log::info('Request: ' . $request->path());
 *
 *         $response = $next($request);
 *
 *         // After logic
 *         Log::info('Response sent');
 *         return $response;
 *     }
 * }
 *
 * Symfony Event Subscribers:
 * class RequestListener implements EventSubscriberInterface {
 *     public function onKernelRequest(RequestEvent $event) {
 *         // Handle request
 *     }
 * }
 *
 * In Go, middleware is a function that:
 * 1. Takes an http.HandlerFunc
 * 2. Returns a new http.HandlerFunc
 * 3. Wraps the original handler with additional logic
 *
 * Middleware can run code:
 * - Before the handler (request processing)
 * - After the handler (response processing)
 * - Around the handler (both before and after)
 *
 * This example demonstrates:
 * - Creating reusable middleware
 * - Chaining multiple middleware
 * - Order of execution
 * - Common middleware patterns (logging, auth, CORS, etc.)
 */

import (
	"context"
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"runtime/debug"
	"strings"
	"sync/atomic"
	"time"
)

// Middleware is a function that wraps an http.HandlerFunc
// Similar to Laravel's middleware interface
type Middleware func(http.HandlerFunc) http.HandlerFunc

// Chain applies multiple middleware in order
// Laravel: $app->middleware([Middleware1::class, Middleware2::class])
func Chain(handler http.HandlerFunc, middleware ...Middleware) http.HandlerFunc {
	// Apply middleware in reverse order so they execute in the correct sequence
	// If middleware are [A, B, C], we want execution: A -> B -> C -> handler
	for i := len(middleware) - 1; i >= 0; i-- {
		handler = middleware[i](handler)
	}
	return handler
}

// LoggingMiddleware logs HTTP requests
// Laravel: App\Http\Middleware\LogRequests
// Logs: method, path, duration, status code
func LoggingMiddleware(next http.HandlerFunc) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		start := time.Now()

		// Create a custom ResponseWriter to capture status code
		// Laravel automatically logs this via events
		lrw := &loggingResponseWriter{
			ResponseWriter: w,
			statusCode:     http.StatusOK,
		}

		// Log request
		log.Printf("[REQUEST] %s %s", r.Method, r.URL.Path)

		// Call next handler
		next(lrw, r)

		// Log response
		duration := time.Since(start)
		log.Printf("[RESPONSE] %s %s - Status: %d - Duration: %v",
			r.Method,
			r.URL.Path,
			lrw.statusCode,
			duration,
		)
	}
}

// loggingResponseWriter wraps http.ResponseWriter to capture status code
type loggingResponseWriter struct {
	http.ResponseWriter
	statusCode int
	written    bool
}

func (lrw *loggingResponseWriter) WriteHeader(statusCode int) {
	if !lrw.written {
		lrw.statusCode = statusCode
		lrw.written = true
		lrw.ResponseWriter.WriteHeader(statusCode)
	}
}

func (lrw *loggingResponseWriter) Write(b []byte) (int, error) {
	if !lrw.written {
		lrw.WriteHeader(http.StatusOK)
	}
	return lrw.ResponseWriter.Write(b)
}

// RecoveryMiddleware recovers from panics
// Laravel: App\Exceptions\Handler catches all exceptions
// PHP: set_exception_handler() or try/catch blocks
func RecoveryMiddleware(next http.HandlerFunc) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		defer func() {
			if err := recover(); err != nil {
				// Log the panic
				log.Printf("[PANIC RECOVERED] %v\n%s", err, debug.Stack())

				// Return 500 error
				// Laravel: App\Exceptions\Handler::render()
				w.Header().Set("Content-Type", "application/json")
				w.WriteHeader(http.StatusInternalServerError)
				json.NewEncoder(w).Encode(map[string]interface{}{
					"error":   "Internal server error",
					"message": "The server encountered an error",
				})
			}
		}()

		next(w, r)
	}
}

// CORSMiddleware handles Cross-Origin Resource Sharing
// Laravel: \Fruitcake\Cors\HandleCors::class
// Symfony: NelmioCorsBundle
func CORSMiddleware(allowedOrigins []string) Middleware {
	return func(next http.HandlerFunc) http.HandlerFunc {
		return func(w http.ResponseWriter, r *http.Request) {
			origin := r.Header.Get("Origin")

			// Check if origin is allowed
			allowed := false
			for _, allowedOrigin := range allowedOrigins {
				if allowedOrigin == "*" || allowedOrigin == origin {
					allowed = true
					break
				}
			}

			if allowed {
				// Set CORS headers
				// Laravel: config/cors.php settings
				w.Header().Set("Access-Control-Allow-Origin", origin)
				w.Header().Set("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, OPTIONS")
				w.Header().Set("Access-Control-Allow-Headers", "Content-Type, Authorization")
				w.Header().Set("Access-Control-Allow-Credentials", "true")
				w.Header().Set("Access-Control-Max-Age", "86400") // 24 hours
			}

			// Handle preflight OPTIONS request
			// Laravel handles this automatically
			if r.Method == http.MethodOptions {
				w.WriteHeader(http.StatusOK)
				return
			}

			next(w, r)
		}
	}
}

// AuthMiddleware validates authentication
// Laravel: App\Http\Middleware\Authenticate
// Checks for Authorization header with Bearer token
func AuthMiddleware(next http.HandlerFunc) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		// Get Authorization header
		// Laravel: $request->bearerToken()
		authHeader := r.Header.Get("Authorization")

		if authHeader == "" {
			// Laravel: return redirect('/login')
			// Or: abort(401, 'Unauthenticated')
			w.Header().Set("Content-Type", "application/json")
			w.WriteHeader(http.StatusUnauthorized)
			json.NewEncoder(w).Encode(map[string]string{
				"error": "Authentication required",
			})
			return
		}

		// Check Bearer token
		// In production, validate against database or JWT
		// Laravel: Auth::check() or JWT validation
		const bearerPrefix = "Bearer "
		if !strings.HasPrefix(authHeader, bearerPrefix) {
			w.Header().Set("Content-Type", "application/json")
			w.WriteHeader(http.StatusUnauthorized)
			json.NewEncoder(w).Encode(map[string]string{
				"error": "Invalid authorization header",
			})
			return
		}

		token := strings.TrimPrefix(authHeader, bearerPrefix)

		// Simplified validation (in production, verify JWT or check database)
		// Laravel: Auth::user() or JWTAuth::parseToken()->authenticate()
		if token == "" || token == "invalid" {
			w.Header().Set("Content-Type", "application/json")
			w.WriteHeader(http.StatusUnauthorized)
			json.NewEncoder(w).Encode(map[string]string{
				"error": "Invalid token",
			})
			return
		}

		// Store user info in context for downstream handlers
		// Laravel: Auth::user() is available in controllers
		ctx := context.WithValue(r.Context(), "userID", "user-123")
		ctx = context.WithValue(ctx, "token", token)

		next(w, r.WithContext(ctx))
	}
}

// RateLimitMiddleware implements rate limiting
// Laravel: throttle:60,1 middleware
// Symfony: RateLimiterBundle
func RateLimitMiddleware(requestsPerMinute int) Middleware {
	// Simple in-memory rate limiter
	// In production, use Redis or a proper rate limiting library
	type rateLimiter struct {
		count     int32
		resetTime time.Time
	}

	limiters := make(map[string]*rateLimiter)

	return func(next http.HandlerFunc) http.HandlerFunc {
		return func(w http.ResponseWriter, r *http.Request) {
			// Use IP address as key
			// Laravel: Uses session or API key
			ip := r.RemoteAddr

			now := time.Now()

			// Get or create limiter for this IP
			limiter, exists := limiters[ip]
			if !exists || now.After(limiter.resetTime) {
				limiter = &rateLimiter{
					count:     0,
					resetTime: now.Add(time.Minute),
				}
				limiters[ip] = limiter
			}

			// Increment counter
			count := atomic.AddInt32(&limiter.count, 1)

			// Check if rate limit exceeded
			// Laravel: abort(429, 'Too Many Requests')
			if int(count) > requestsPerMinute {
				w.Header().Set("Content-Type", "application/json")
				w.Header().Set("X-RateLimit-Limit", fmt.Sprintf("%d", requestsPerMinute))
				w.Header().Set("X-RateLimit-Remaining", "0")
				w.Header().Set("Retry-After", fmt.Sprintf("%d", int(time.Until(limiter.resetTime).Seconds())))

				w.WriteHeader(http.StatusTooManyRequests)
				json.NewEncoder(w).Encode(map[string]string{
					"error": "Rate limit exceeded",
				})
				return
			}

			// Set rate limit headers
			// Laravel includes these automatically with throttle middleware
			remaining := requestsPerMinute - int(count)
			w.Header().Set("X-RateLimit-Limit", fmt.Sprintf("%d", requestsPerMinute))
			w.Header().Set("X-RateLimit-Remaining", fmt.Sprintf("%d", remaining))

			next(w, r)
		}
	}
}

// ContentTypeMiddleware enforces request content type
// Laravel: Custom middleware or FormRequest validation
func ContentTypeMiddleware(contentType string) Middleware {
	return func(next http.HandlerFunc) http.HandlerFunc {
		return func(w http.ResponseWriter, r *http.Request) {
			// Skip for GET and DELETE requests
			if r.Method == http.MethodGet || r.Method == http.MethodDelete {
				next(w, r)
				return
			}

			// Check Content-Type header
			requestContentType := r.Header.Get("Content-Type")

			if !strings.Contains(requestContentType, contentType) {
				w.Header().Set("Content-Type", "application/json")
				w.WriteHeader(http.StatusUnsupportedMediaType)
				json.NewEncoder(w).Encode(map[string]string{
					"error": fmt.Sprintf("Content-Type must be %s", contentType),
				})
				return
			}

			next(w, r)
		}
	}
}

// RequestIDMiddleware adds unique request ID
// Laravel: Custom middleware or package
// Useful for tracing requests through logs
func RequestIDMiddleware(next http.HandlerFunc) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		// Generate request ID
		requestID := fmt.Sprintf("%d-%d", time.Now().UnixNano(), time.Now().Unix())

		// Add to response headers
		w.Header().Set("X-Request-ID", requestID)

		// Store in context for use in handlers
		ctx := context.WithValue(r.Context(), "requestID", requestID)

		next(w, r.WithContext(ctx))
	}
}

// SecurityHeadersMiddleware adds security headers
// Laravel: Custom middleware or package
// Symfony: SecurityBundle headers
func SecurityHeadersMiddleware(next http.HandlerFunc) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		// Security headers
		// Laravel: These can be set in middleware or config/session.php
		w.Header().Set("X-Frame-Options", "DENY")
		w.Header().Set("X-Content-Type-Options", "nosniff")
		w.Header().Set("X-XSS-Protection", "1; mode=block")
		w.Header().Set("Strict-Transport-Security", "max-age=31536000; includeSubDomains")
		w.Header().Set("Content-Security-Policy", "default-src 'self'")
		w.Header().Set("Referrer-Policy", "strict-origin-when-cross-origin")

		next(w, r)
	}
}

// CompressionMiddleware would compress responses (simplified example)
// Laravel: Package like barryvdh/laravel-compression
// In production, use a package like github.com/nytimes/gziphandler
func CompressionMiddleware(next http.HandlerFunc) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		// Check if client accepts gzip
		if strings.Contains(r.Header.Get("Accept-Encoding"), "gzip") {
			// In production, wrap ResponseWriter with gzip writer
			w.Header().Set("Content-Encoding", "gzip")
		}

		next(w, r)
	}
}

// Example handlers

func homeHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]string{
		"message": "Welcome to middleware demo",
		"path":    r.URL.Path,
	})
}

func protectedHandler(w http.ResponseWriter, r *http.Request) {
	// Get user ID from context (set by AuthMiddleware)
	// Laravel: Auth::id() or $request->user()->id
	userID := r.Context().Value("userID")

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"message": "Protected route accessed",
		"user_id": userID,
	})
}

func panicHandler(w http.ResponseWriter, r *http.Request) {
	// Trigger a panic to test recovery middleware
	// Laravel: Exceptions are caught by exception handler
	panic("Intentional panic for testing recovery middleware")
}

func apiHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"message": "API endpoint",
		"cors":    "CORS headers should be present",
	})
}

func main() {
	// Create HTTP server multiplexer
	mux := http.NewServeMux()

	// Route 1: Home - with logging and recovery only
	// Laravel: Route::get('/', 'HomeController@index')
	mux.HandleFunc("/", Chain(
		homeHandler,
		LoggingMiddleware,
		RecoveryMiddleware,
		RequestIDMiddleware,
	))

	// Route 2: Protected - with full middleware stack
	// Laravel: Route::get('/dashboard', 'DashboardController@index')->middleware('auth')
	mux.HandleFunc("/protected", Chain(
		protectedHandler,
		LoggingMiddleware,
		RecoveryMiddleware,
		RequestIDMiddleware,
		AuthMiddleware,
		RateLimitMiddleware(10), // 10 requests per minute
	))

	// Route 3: API endpoint - with CORS
	// Laravel: Route::group(['middleware' => 'cors'], function() {
	//     Route::get('/api/data', 'ApiController@data');
	// });
	mux.HandleFunc("/api/data", Chain(
		apiHandler,
		LoggingMiddleware,
		RecoveryMiddleware,
		CORSMiddleware([]string{"*"}),
		ContentTypeMiddleware("application/json"),
		RateLimitMiddleware(30),
	))

	// Route 4: Panic test - to demonstrate recovery
	mux.HandleFunc("/panic", Chain(
		panicHandler,
		LoggingMiddleware,
		RecoveryMiddleware,
	))

	// Global middleware applied to all routes
	// Laravel: $app->middleware([...]) in app/Http/Kernel.php
	handler := Chain(
		mux.ServeHTTP,
		SecurityHeadersMiddleware,
	)

	// Create server
	server := &http.Server{
		Addr:         ":8080",
		Handler:      http.HandlerFunc(handler),
		ReadTimeout:  15 * time.Second,
		WriteTimeout: 15 * time.Second,
	}

	log.Println("Server starting on http://localhost:8080")
	log.Println("\nAvailable routes:")
	log.Println("  GET  /           - Home (logging, recovery)")
	log.Println("  GET  /protected  - Protected route (requires auth)")
	log.Println("  GET  /api/data   - API endpoint (CORS enabled)")
	log.Println("  GET  /panic      - Triggers panic (tests recovery)")
	log.Println("\nMiddleware demonstration:")
	log.Println("  1. Try: curl http://localhost:8080/")
	log.Println("  2. Try: curl http://localhost:8080/protected")
	log.Println("       (should fail - no auth)")
	log.Println("  3. Try: curl -H 'Authorization: Bearer mytoken' http://localhost:8080/protected")
	log.Println("       (should succeed)")
	log.Println("  4. Try: curl http://localhost:8080/api/data -H 'Origin: http://example.com'")
	log.Println("       (should include CORS headers)")
	log.Println("  5. Try: curl http://localhost:8080/panic")
	log.Println("       (should recover from panic)")

	if err := server.ListenAndServe(); err != nil {
		log.Fatalf("Server failed: %v", err)
	}
}

/*
 * KEY TAKEAWAYS FOR PHP DEVELOPERS:
 *
 * 1. MIDDLEWARE CONCEPT
 *    - Laravel: Classes implementing handle($request, Closure $next)
 *    - Go: Functions wrapping http.HandlerFunc
 *    - Both use onion/chain pattern
 *
 * 2. EXECUTION ORDER
 *    - Laravel: Middleware array in Kernel.php
 *    - Go: Chain() function applies middleware in order
 *    - Both execute middleware before and after handler
 *
 * 3. REQUEST/RESPONSE MODIFICATION
 *    - Laravel: Modify $request before, $response after
 *    - Go: Modify request/response through wrappers
 *
 * 4. AUTHENTICATION
 *    - Laravel: Auth::check() in middleware
 *    - Go: Validate token, store user in context
 *
 * 5. CORS
 *    - Laravel: fruitcake/laravel-cors package
 *    - Go: Custom middleware or package
 *
 * 6. RATE LIMITING
 *    - Laravel: throttle:60,1 middleware
 *    - Go: Custom implementation or package
 *
 * 7. ERROR HANDLING
 *    - Laravel: Exception handler catches all
 *    - Go: Recovery middleware with panic/recover
 *
 * 8. CONTEXT USAGE
 *    - Laravel: Request properties and session
 *    - Go: context.Context for passing data
 *
 * MIDDLEWARE PATTERNS:
 *
 * 1. Before Middleware: Runs before handler
 *    - Authentication
 *    - Validation
 *    - Rate limiting
 *
 * 2. After Middleware: Runs after handler
 *    - Logging response
 *    - Compression
 *    - Response modification
 *
 * 3. Around Middleware: Runs before and after
 *    - Logging (request + response)
 *    - Timing
 *    - Transaction management
 *
 * COMPARISON TO LARAVEL KERNEL.PHP:
 *
 * Laravel app/Http/Kernel.php:
 * protected $middleware = [
 *     \App\Http\Middleware\TrustProxies::class,
 *     \Fruitcake\Cors\HandleCors::class,
 *     \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
 * ];
 *
 * Go equivalent:
 * handler := Chain(
 *     mux.ServeHTTP,
 *     TrustProxiesMiddleware,
 *     CORSMiddleware,
 *     MaintenanceModeMiddleware,
 * )
 *
 * Both organize middleware in a clear, maintainable way!
 */
