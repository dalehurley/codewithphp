package main

/*
 * Context Usage in Go Web Applications - Chapter 17, Example 3
 *
 * PHP COMPARISON - REQUEST CONTEXT:
 *
 * In PHP frameworks, request-scoped data is typically stored in:
 * - Request object: $request->attributes->set('user', $user)
 * - Session: Session::put('key', 'value')
 * - Service container bindings
 * - Global variables (not recommended)
 *
 * Laravel examples:
 * $request->merge(['user_id' => $userId]);
 * $request->route('id');
 * Auth::user();
 * Session::get('cart');
 *
 * Symfony examples:
 * $request->attributes->get('_route_params');
 * $this->getUser();
 * $session->get('key');
 *
 * In Go, context.Context is the standard way to:
 * 1. Pass request-scoped values down the call chain
 * 2. Handle cancellation and timeouts
 * 3. Control goroutine lifecycle
 *
 * Context is particularly important in Go because:
 * - Multiple goroutines may handle parts of a request
 * - Need to cancel operations when request is cancelled
 * - Need to enforce timeouts
 * - Share request-scoped data without globals
 *
 * This example demonstrates:
 * - Passing values through context
 * - Request timeouts and cancellation
 * - Best practices for context usage
 * - Integration with middleware and handlers
 */

import (
	"context"
	"database/sql"
	"encoding/json"
	"errors"
	"fmt"
	"log"
	"net/http"
	"time"
)

// Context keys should be unexported to avoid collisions
// This is similar to Laravel's request attributes being namespaced
type contextKey string

const (
	userContextKey      contextKey = "user"
	requestIDContextKey contextKey = "requestID"
	authTokenContextKey contextKey = "authToken"
	timingContextKey    contextKey = "timing"
)

// User represents an authenticated user
// Laravel: Auth::user() returns User model
type User struct {
	ID       int       `json:"id"`
	Username string    `json:"username"`
	Email    string    `json:"email"`
	Role     string    `json:"role"`
	LoginAt  time.Time `json:"login_at"`
}

// RequestTiming tracks request timing information
type RequestTiming struct {
	StartTime      time.Time
	DatabaseTime   time.Duration
	ExternalAPITime time.Duration
}

// SetUser stores user in context
// Laravel: $request->setUserResolver(function() { return $user; })
// Or: Auth::setUser($user)
func SetUser(ctx context.Context, user *User) context.Context {
	return context.WithValue(ctx, userContextKey, user)
}

// GetUser retrieves user from context
// Laravel: Auth::user() or $request->user()
// Returns nil if user not authenticated
func GetUser(ctx context.Context) *User {
	if user, ok := ctx.Value(userContextKey).(*User); ok {
		return user
	}
	return nil
}

// SetRequestID stores request ID in context
// Laravel: $request->attributes->set('request_id', $id)
func SetRequestID(ctx context.Context, requestID string) context.Context {
	return context.WithValue(ctx, requestIDContextKey, requestID)
}

// GetRequestID retrieves request ID from context
// Laravel: $request->attributes->get('request_id')
func GetRequestID(ctx context.Context) string {
	if id, ok := ctx.Value(requestIDContextKey).(string); ok {
		return id
	}
	return ""
}

// SetTiming initializes request timing
func SetTiming(ctx context.Context, timing *RequestTiming) context.Context {
	return context.WithValue(ctx, timingContextKey, timing)
}

// GetTiming retrieves request timing from context
func GetTiming(ctx context.Context) *RequestTiming {
	if timing, ok := ctx.Value(timingContextKey).(*RequestTiming); ok {
		return timing
	}
	return nil
}

// ContextMiddleware demonstrates context initialization
// Laravel: This would be in middleware or service provider
func ContextMiddleware(next http.HandlerFunc) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		// Generate request ID
		// Laravel: Middleware or custom package
		requestID := fmt.Sprintf("%d", time.Now().UnixNano())

		// Initialize timing
		timing := &RequestTiming{
			StartTime: time.Now(),
		}

		// Add to context
		ctx := SetRequestID(r.Context(), requestID)
		ctx = SetTiming(ctx, timing)

		// Add request ID to response header
		w.Header().Set("X-Request-ID", requestID)

		log.Printf("[%s] Request started: %s %s", requestID, r.Method, r.URL.Path)

		// Continue with modified context
		next(w, r.WithContext(ctx))

		// Log completion
		duration := time.Since(timing.StartTime)
		log.Printf("[%s] Request completed in %v", requestID, duration)
	}
}

// TimeoutMiddleware enforces request timeout
// Laravel: set_time_limit() or timeout configuration
// This is critical in Go because long-running requests can accumulate
func TimeoutMiddleware(timeout time.Duration) func(http.HandlerFunc) http.HandlerFunc {
	return func(next http.HandlerFunc) http.HandlerFunc {
		return func(w http.ResponseWriter, r *http.Request) {
			// Create context with timeout
			// If request takes longer than timeout, ctx.Done() channel is closed
			ctx, cancel := context.WithTimeout(r.Context(), timeout)
			defer cancel() // Always call cancel to release resources

			// Channel to signal completion
			done := make(chan struct{})

			// Run handler in goroutine
			go func() {
				next(w, r.WithContext(ctx))
				close(done)
			}()

			// Wait for completion or timeout
			select {
			case <-done:
				// Request completed successfully
				return

			case <-ctx.Done():
				// Request timed out
				// Laravel: Throws TimeoutException or returns 504
				log.Printf("[TIMEOUT] Request exceeded %v timeout", timeout)

				w.Header().Set("Content-Type", "application/json")
				w.WriteHeader(http.StatusGatewayTimeout)
				json.NewEncoder(w).Encode(map[string]string{
					"error":   "Request timeout",
					"message": fmt.Sprintf("Request exceeded %v timeout", timeout),
				})
			}
		}
	}
}

// AuthMiddleware authenticates user and adds to context
// Laravel: App\Http\Middleware\Authenticate
func AuthMiddleware(next http.HandlerFunc) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		// In production, validate token against database or JWT
		// Laravel: Auth::attempt() or JWT validation
		token := r.Header.Get("Authorization")

		if token == "" {
			http.Error(w, "Unauthorized", http.StatusUnauthorized)
			return
		}

		// Simulate user lookup
		// Laravel: Auth::user() does this automatically
		user := &User{
			ID:       123,
			Username: "john_doe",
			Email:    "john@example.com",
			Role:     "user",
			LoginAt:  time.Now(),
		}

		// Store user in context
		// Laravel: Auth::setUser($user) or handled automatically
		ctx := SetUser(r.Context(), user)

		next(w, r.WithContext(ctx))
	}
}

// simulateDatabaseQuery simulates a database operation with context
// This demonstrates how to respect context cancellation
// Laravel: DB::table('users')->get() doesn't need cancellation
// because PHP is synchronous, but Go needs it for long operations
func simulateDatabaseQuery(ctx context.Context, query string) ([]string, error) {
	// Get timing from context
	timing := GetTiming(ctx)
	dbStart := time.Now()
	defer func() {
		if timing != nil {
			timing.DatabaseTime += time.Since(dbStart)
		}
	}()

	// In production, use database/sql with context:
	// db.QueryContext(ctx, "SELECT ...")
	// This allows cancellation of database queries

	// Simulate slow query
	select {
	case <-time.After(100 * time.Millisecond):
		// Query completed
		return []string{"result1", "result2", "result3"}, nil

	case <-ctx.Done():
		// Context cancelled (timeout or request cancelled)
		// Laravel: Query would continue running in background
		// Go: Query is cancelled, resources released
		return nil, ctx.Err()
	}
}

// simulateExternalAPI simulates calling an external API with context
// Context is crucial for controlling external API timeouts
func simulateExternalAPI(ctx context.Context, url string) (map[string]interface{}, error) {
	// Get timing from context
	timing := GetTiming(ctx)
	apiStart := time.Now()
	defer func() {
		if timing != nil {
			timing.ExternalAPITime += time.Since(apiStart)
		}
	}()

	// In production, use http.NewRequestWithContext:
	// req, _ := http.NewRequestWithContext(ctx, "GET", url, nil)
	// resp, err := client.Do(req)

	// Simulate API call
	select {
	case <-time.After(200 * time.Millisecond):
		return map[string]interface{}{
			"status": "success",
			"data":   "API response",
		}, nil

	case <-ctx.Done():
		// API call cancelled
		return nil, ctx.Err()
	}
}

// Handlers demonstrating context usage

func homeHandler(w http.ResponseWriter, r *http.Request) {
	// Get request ID from context
	// Laravel: $request->attributes->get('request_id')
	requestID := GetRequestID(r.Context())

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"message":    "Context usage demo",
		"request_id": requestID,
	})
}

func userProfileHandler(w http.ResponseWriter, r *http.Request) {
	// Get authenticated user from context
	// Laravel: Auth::user()
	user := GetUser(r.Context())

	if user == nil {
		http.Error(w, "Unauthorized", http.StatusUnauthorized)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"message": "User profile",
		"user":    user,
	})
}

func databaseHandler(w http.ResponseWriter, r *http.Request) {
	ctx := r.Context()

	// Perform database query with context
	// Laravel: $users = DB::table('users')->get();
	results, err := simulateDatabaseQuery(ctx, "SELECT * FROM users")

	if err != nil {
		if errors.Is(err, context.DeadlineExceeded) {
			// Query timed out
			// Laravel: Throws QueryException or database timeout
			http.Error(w, "Database query timeout", http.StatusGatewayTimeout)
			return
		}

		http.Error(w, "Database error", http.StatusInternalServerError)
		return
	}

	// Get timing info
	timing := GetTiming(ctx)

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"message": "Database query result",
		"results": results,
		"timing": map[string]string{
			"database": timing.DatabaseTime.String(),
		},
	})
}

func externalAPIHandler(w http.ResponseWriter, r *http.Request) {
	ctx := r.Context()

	// Call external API with context
	// Laravel: Http::timeout(5)->get('https://api.example.com')
	apiResponse, err := simulateExternalAPI(ctx, "https://api.example.com/data")

	if err != nil {
		if errors.Is(err, context.DeadlineExceeded) {
			http.Error(w, "API request timeout", http.StatusGatewayTimeout)
			return
		}

		http.Error(w, "API error", http.StatusInternalServerError)
		return
	}

	timing := GetTiming(ctx)

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"message": "External API response",
		"data":    apiResponse,
		"timing": map[string]string{
			"api": timing.ExternalAPITime.String(),
		},
	})
}

func slowHandler(w http.ResponseWriter, r *http.Request) {
	ctx := r.Context()

	// Simulate slow operation
	// This will trigger timeout if TimeoutMiddleware is used
	select {
	case <-time.After(10 * time.Second):
		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(map[string]string{
			"message": "Slow operation completed",
		})

	case <-ctx.Done():
		// Context was cancelled (timeout or client disconnected)
		// In Laravel, you'd need to manually check this
		log.Printf("Request cancelled: %v", ctx.Err())
		return
	}
}

func complexHandler(w http.ResponseWriter, r *http.Request) {
	ctx := r.Context()

	// Get user from context
	// Laravel: Auth::user()
	user := GetUser(ctx)

	// Perform multiple operations with context
	// All respect the context timeout and cancellation

	// 1. Database query
	dbResults, err := simulateDatabaseQuery(ctx, "SELECT * FROM orders WHERE user_id = ?")
	if err != nil {
		http.Error(w, "Database error", http.StatusInternalServerError)
		return
	}

	// 2. External API call
	apiData, err := simulateExternalAPI(ctx, "https://api.example.com/user-data")
	if err != nil {
		http.Error(w, "API error", http.StatusInternalServerError)
		return
	}

	// Get timing
	timing := GetTiming(ctx)
	totalTime := time.Since(timing.StartTime)

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"user":       user,
		"db_results": dbResults,
		"api_data":   apiData,
		"timing": map[string]string{
			"total":    totalTime.String(),
			"database": timing.DatabaseTime.String(),
			"api":      timing.ExternalAPITime.String(),
		},
	})
}

// Example of context with transactions
// Laravel: DB::transaction(function() { ... })
func transactionExample(ctx context.Context, db *sql.DB) error {
	// Start transaction with context
	// Context cancellation will rollback the transaction
	tx, err := db.BeginTx(ctx, nil)
	if err != nil {
		return err
	}

	// Ensure rollback on error or panic
	defer tx.Rollback()

	// Execute queries with context
	// If ctx is cancelled, queries will be aborted
	_, err = tx.ExecContext(ctx, "INSERT INTO users (name) VALUES (?)", "John")
	if err != nil {
		return err
	}

	_, err = tx.ExecContext(ctx, "INSERT INTO profiles (user_id) VALUES (?)", 1)
	if err != nil {
		return err
	}

	// Commit transaction
	// Laravel: Transaction is automatically committed if no exception
	return tx.Commit()
}

func main() {
	mux := http.NewServeMux()

	// Basic routes with context
	mux.HandleFunc("/", ContextMiddleware(homeHandler))

	// Protected route requiring authentication
	// Laravel: Route::middleware('auth')->get('/profile', ...)
	mux.HandleFunc("/profile", ContextMiddleware(
		AuthMiddleware(userProfileHandler),
	))

	// Route with database operations
	mux.HandleFunc("/database", ContextMiddleware(databaseHandler))

	// Route with external API calls
	mux.HandleFunc("/api", ContextMiddleware(externalAPIHandler))

	// Slow route with timeout
	// This route has a 2-second timeout
	// Laravel: set_time_limit(2) or timeout configuration
	mux.HandleFunc("/slow", ContextMiddleware(
		TimeoutMiddleware(2*time.Second)(slowHandler),
	))

	// Complex route demonstrating multiple operations
	mux.HandleFunc("/complex", ContextMiddleware(
		AuthMiddleware(
			TimeoutMiddleware(5*time.Second)(complexHandler),
		),
	))

	server := &http.Server{
		Addr:    ":8080",
		Handler: mux,
	}

	log.Println("Server starting on http://localhost:8080")
	log.Println("\nAvailable routes:")
	log.Println("  GET  /           - Basic context demo")
	log.Println("  GET  /profile    - User profile (requires auth)")
	log.Println("  GET  /database   - Database query with context")
	log.Println("  GET  /api        - External API call with context")
	log.Println("  GET  /slow       - Slow operation (will timeout)")
	log.Println("  GET  /complex    - Complex operation with multiple steps")
	log.Println("\nContext features demonstrated:")
	log.Println("  - Request-scoped values (user, request ID, timing)")
	log.Println("  - Timeout handling")
	log.Println("  - Cancellation propagation")
	log.Println("  - Resource cleanup")
	log.Println("\nTry these commands:")
	log.Println("  curl http://localhost:8080/")
	log.Println("  curl -H 'Authorization: Bearer token' http://localhost:8080/profile")
	log.Println("  curl http://localhost:8080/database")
	log.Println("  curl http://localhost:8080/slow  (will timeout after 2s)")

	if err := server.ListenAndServe(); err != nil {
		log.Fatalf("Server failed: %v", err)
	}
}

/*
 * KEY TAKEAWAYS FOR PHP DEVELOPERS:
 *
 * 1. CONTEXT VS REQUEST SCOPE
 *    - PHP: Request object holds all request-scoped data
 *    - Go: context.Context passes values and controls cancellation
 *    - Both achieve request-scoped data, different mechanisms
 *
 * 2. TIMEOUTS
 *    - PHP: set_time_limit() applies to entire script
 *    - Go: context.WithTimeout() applies to specific operations
 *    - Go gives fine-grained control over timeouts
 *
 * 3. CANCELLATION
 *    - PHP: No built-in cancellation mechanism
 *    - Go: Context cancellation propagates through call stack
 *    - Critical for cleaning up goroutines and resources
 *
 * 4. DATABASE OPERATIONS
 *    - PHP: db->query() - no timeout control
 *    - Go: db.QueryContext(ctx, ...) - respects timeout
 *    - Go can cancel long-running queries
 *
 * 5. HTTP REQUESTS
 *    - PHP: file_get_contents() or Guzzle with timeout
 *    - Go: http.NewRequestWithContext(ctx, ...) - cancellable
 *    - Both support timeouts, Go is more integrated
 *
 * 6. USER AUTHENTICATION
 *    - Laravel: Auth::user() available everywhere
 *    - Go: Store user in context, retrieve with GetUser(ctx)
 *    - Similar pattern, different implementation
 *
 * 7. REQUEST TRACKING
 *    - Laravel: Request ID in middleware or attributes
 *    - Go: Store in context, passes through call chain
 *    - Both enable request tracing
 *
 * 8. TRANSACTIONS
 *    - Laravel: DB::transaction() with automatic rollback
 *    - Go: BeginTx(ctx) with context-aware operations
 *    - Go transactions respect context cancellation
 *
 * BEST PRACTICES:
 *
 * 1. Always accept context as first parameter: func foo(ctx context.Context, ...)
 * 2. Never store context in struct fields
 * 3. Always call cancel() when using WithTimeout/WithCancel
 * 4. Use context for request-scoped values, not for optional parameters
 * 5. Pass context through entire call chain
 * 6. Check ctx.Done() in long-running operations
 *
 * LARAVEL COMPARISON:
 *
 * Laravel Request Attributes:
 * $request->attributes->set('key', 'value')
 * $request->attributes->get('key')
 *
 * Go Context:
 * ctx = context.WithValue(ctx, key, value)
 * value := ctx.Value(key)
 *
 * Both provide request-scoped storage, but Go's context
 * also handles timeouts and cancellation - critical for
 * concurrent operations.
 */
