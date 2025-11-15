package main

/*
 * Custom Router Implementation in Go - Chapter 17, Example 1
 *
 * PHP FRAMEWORK ROUTING COMPARISON:
 *
 * Laravel routes/web.php:
 * Route::get('/users/{id}', [UserController::class, 'show']);
 * Route::post('/api/products/{id}/reviews', [ReviewController::class, 'store']);
 * Route::put('/posts/{post}/comments/{comment}', 'CommentController@update');
 *
 * Symfony routing.yaml:
 * user_show:
 *   path: /users/{id}
 *   controller: App\Controller\UserController::show
 *   requirements:
 *     id: '\d+'
 *
 * This example builds a custom router similar to Laravel's routing system,
 * with support for:
 * - Dynamic path parameters (like {id})
 * - HTTP method constraints
 * - Route grouping with prefixes
 * - Named routes
 * - Route parameter constraints
 * - Middleware support
 *
 * In production, you'd typically use established routers like:
 * - gorilla/mux (most popular)
 * - chi (lightweight)
 * - gin (high performance)
 * - echo (minimalist)
 *
 * But understanding how they work helps you choose and use them effectively.
 */

import (
	"context"
	"encoding/json"
	"log"
	"net/http"
	"regexp"
	"strings"
	"time"
)

// Route represents a single route definition
// Similar to Laravel's Route or Symfony's Route object
type Route struct {
	Method         string                                     // HTTP method: GET, POST, etc.
	Pattern        string                                     // URL pattern: /users/{id}
	Handler        http.HandlerFunc                           // Handler function
	Regex          *regexp.Regexp                             // Compiled regex for matching
	ParamNames     []string                                   // Parameter names extracted from pattern
	RouteName      string                                     // Named route (like Laravel's route names)
	Constraints    map[string]*regexp.Regexp                  // Parameter constraints
	RouteMiddleware []func(http.HandlerFunc) http.HandlerFunc // Route-specific middleware
}

// Router manages application routes
// Similar to Laravel's Router or Symfony's Router
type Router struct {
	routes      []*Route
	groups      []*RouteGroup
	notFound    http.HandlerFunc
	middleware  []func(http.HandlerFunc) http.HandlerFunc // Global middleware
}

// RouteGroup allows grouping routes with common attributes
// Laravel: Route::group(['prefix' => 'api', 'middleware' => ['auth']], function() {...})
type RouteGroup struct {
	Prefix     string
	Middleware []func(http.HandlerFunc) http.HandlerFunc
}

// RouteParams holds extracted route parameters
// Similar to Laravel's route parameters: Route::get('/users/{id}')
// Access in controller: $id or $request->route('id')
type RouteParams map[string]string

// NewRouter creates a new router instance
func NewRouter() *Router {
	return &Router{
		routes:  make([]*Route, 0),
		groups:  make([]*RouteGroup, 0),
		notFound: func(w http.ResponseWriter, r *http.Request) {
			http.Error(w, "Not Found", http.StatusNotFound)
		},
		middleware: make([]func(http.HandlerFunc) http.HandlerFunc, 0),
	}
}

// Get registers a GET route
// Laravel: Route::get('/path', handler)
func (router *Router) Get(pattern string, handler http.HandlerFunc) *Route {
	return router.addRoute(http.MethodGet, pattern, handler)
}

// Post registers a POST route
// Laravel: Route::post('/path', handler)
func (router *Router) Post(pattern string, handler http.HandlerFunc) *Route {
	return router.addRoute(http.MethodPost, pattern, handler)
}

// Put registers a PUT route
// Laravel: Route::put('/path', handler)
func (router *Router) Put(pattern string, handler http.HandlerFunc) *Route {
	return router.addRoute(http.MethodPut, pattern, handler)
}

// Delete registers a DELETE route
// Laravel: Route::delete('/path', handler)
func (router *Router) Delete(pattern string, handler http.HandlerFunc) *Route {
	return router.addRoute(http.MethodDelete, pattern, handler)
}

// Patch registers a PATCH route
// Laravel: Route::patch('/path', handler)
func (router *Router) Patch(pattern string, handler http.HandlerFunc) *Route {
	return router.addRoute(http.MethodPatch, pattern, handler)
}

// Any registers a route for all HTTP methods
// Laravel: Route::any('/path', handler)
func (router *Router) Any(pattern string, handler http.HandlerFunc) []*Route {
	methods := []string{
		http.MethodGet,
		http.MethodPost,
		http.MethodPut,
		http.MethodDelete,
		http.MethodPatch,
	}

	routes := make([]*Route, 0, len(methods))
	for _, method := range methods {
		route := router.addRoute(method, pattern, handler)
		routes = append(routes, route)
	}

	return routes
}

// Group creates a route group
// Laravel: Route::group(['prefix' => 'api'], function($router) {...})
func (router *Router) Group(prefix string, middleware []func(http.HandlerFunc) http.HandlerFunc, fn func(*Router)) {
	// Save current group state
	previousGroup := &RouteGroup{}
	if len(router.groups) > 0 {
		previousGroup = router.groups[len(router.groups)-1]
	}

	// Create new group
	group := &RouteGroup{
		Prefix:     prefix,
		Middleware: middleware,
	}

	router.groups = append(router.groups, group)

	// Execute group callback
	fn(router)

	// Restore previous group state
	if len(router.groups) > 1 {
		router.groups = router.groups[:len(router.groups)-1]
	} else {
		router.groups = make([]*RouteGroup, 0)
	}

	_ = previousGroup // Avoid unused variable warning
}

// Use adds global middleware
// Laravel: $app->middleware([...])
// Symfony: services.yaml with tags
func (router *Router) Use(middleware ...func(http.HandlerFunc) http.HandlerFunc) {
	router.middleware = append(router.middleware, middleware...)
}

// NotFound sets the 404 handler
// Laravel: App::missing() or fallback route
func (router *Router) NotFound(handler http.HandlerFunc) {
	router.notFound = handler
}

// addRoute adds a route to the router
func (router *Router) addRoute(method, pattern string, handler http.HandlerFunc) *Route {
	// Apply group prefix if we're in a group
	if len(router.groups) > 0 {
		group := router.groups[len(router.groups)-1]
		pattern = group.Prefix + pattern

		// Apply group middleware
		for i := len(group.Middleware) - 1; i >= 0; i-- {
			handler = group.Middleware[i](handler)
		}
	}

	// Parse route pattern and extract parameter names
	// Convert Laravel-style {id} to regex (?P<id>[^/]+)
	paramNames := make([]string, 0)
	regexPattern := "^" + pattern + "$"

	// Find all {param} patterns
	paramRegex := regexp.MustCompile(`\{([a-zA-Z0-9_]+)\}`)
	matches := paramRegex.FindAllStringSubmatch(pattern, -1)

	for _, match := range matches {
		paramName := match[1]
		paramNames = append(paramNames, paramName)

		// Replace {param} with named capture group
		regexPattern = strings.Replace(
			regexPattern,
			"{"+paramName+"}",
			"(?P<"+paramName+">[^/]+)",
			1,
		)
	}

	route := &Route{
		Method:      method,
		Pattern:     pattern,
		Handler:     handler,
		Regex:       regexp.MustCompile(regexPattern),
		ParamNames:  paramNames,
		Constraints: make(map[string]*regexp.Regexp),
	}

	router.routes = append(router.routes, route)
	return route
}

// Name sets the route name
// Laravel: Route::get('/users/{id}', ...)->name('users.show')
func (r *Route) Name(name string) *Route {
	r.RouteName = name
	return r
}

// Where adds parameter constraints
// Laravel: Route::get('/users/{id}', ...)->where('id', '[0-9]+')
func (r *Route) Where(param string, pattern string) *Route {
	r.Constraints[param] = regexp.MustCompile("^" + pattern + "$")
	return r
}

// Middleware adds route-specific middleware
// Laravel: Route::get('/dashboard', ...)->middleware('auth')
func (r *Route) Middleware(middleware ...func(http.HandlerFunc) http.HandlerFunc) *Route {
	r.RouteMiddleware = append(r.RouteMiddleware, middleware...)
	return r
}

// ServeHTTP implements the http.Handler interface
// This is called for every HTTP request
func (router *Router) ServeHTTP(w http.ResponseWriter, r *http.Request) {
	// Try to match a route
	for _, route := range router.routes {
		// Check HTTP method
		if route.Method != r.Method {
			continue
		}

		// Check if pattern matches
		if !route.Regex.MatchString(r.URL.Path) {
			continue
		}

		// Extract route parameters
		params := router.extractParams(route, r.URL.Path)

		// Validate parameter constraints
		if !router.validateConstraints(route, params) {
			continue
		}

		// Store params in request context (we'll cover context in detail later)
		// In Laravel, these are available via $request->route('param')
		ctx := r.Context()
		ctx = setParams(ctx, params)
		r = r.WithContext(ctx)

		// Apply route-specific middleware
		handler := route.Handler
		for i := len(route.RouteMiddleware) - 1; i >= 0; i-- {
			handler = route.RouteMiddleware[i](handler)
		}

		// Apply global middleware
		for i := len(router.middleware) - 1; i >= 0; i-- {
			handler = router.middleware[i](handler)
		}

		// Call the handler
		handler(w, r)
		return
	}

	// No route matched - call 404 handler
	router.notFound(w, r)
}

// extractParams extracts route parameters from the URL path
func (router *Router) extractParams(route *Route, path string) RouteParams {
	params := make(RouteParams)

	matches := route.Regex.FindStringSubmatch(path)
	if matches == nil {
		return params
	}

	// Map captured groups to parameter names
	for i, name := range route.Regex.SubexpNames() {
		if i > 0 && i <= len(matches) {
			params[name] = matches[i]
		}
	}

	return params
}

// validateConstraints validates route parameter constraints
func (router *Router) validateConstraints(route *Route, params RouteParams) bool {
	for paramName, constraint := range route.Constraints {
		value, exists := params[paramName]
		if !exists {
			return false
		}

		if !constraint.MatchString(value) {
			return false
		}
	}

	return true
}

// Context helpers for storing route parameters
// In Laravel, parameters are automatically available in the request
type contextKey string

const paramsKey contextKey = "routeParams"

func setParams(ctx context.Context, params RouteParams) context.Context {
	return context.WithValue(ctx, paramsKey, params)
}

// GetParams retrieves route parameters from request context
// Laravel: $request->route('id') or direct parameter injection
func GetParams(r *http.Request) RouteParams {
	if params, ok := r.Context().Value(paramsKey).(RouteParams); ok {
		return params
	}
	return make(RouteParams)
}

// GetParam retrieves a single route parameter
// Laravel: $request->route('id')
func GetParam(r *http.Request, name string) string {
	params := GetParams(r)
	return params[name]
}

// Example handlers demonstrating router usage

func homeHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]string{
		"message": "Welcome to the custom router demo",
		"path":    r.URL.Path,
	})
}

func userShowHandler(w http.ResponseWriter, r *http.Request) {
	// Extract route parameter
	// Laravel: public function show($id) or $request->route('id')
	userID := GetParam(r, "id")

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"action": "show user",
		"user_id": userID,
	})
}

func userUpdateHandler(w http.ResponseWriter, r *http.Request) {
	userID := GetParam(r, "id")

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"action": "update user",
		"user_id": userID,
	})
}

func postCommentHandler(w http.ResponseWriter, r *http.Request) {
	// Multiple parameters
	// Laravel: Route::get('/posts/{post}/comments/{comment}')
	// public function show($postId, $commentId)
	postID := GetParam(r, "post")
	commentID := GetParam(r, "comment")

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"action": "show comment",
		"post_id": postID,
		"comment_id": commentID,
	})
}

func apiProductsHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]interface{}{
		"message": "API products endpoint",
		"prefix": "This is in the /api group",
	})
}

func adminDashboardHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]string{
		"message": "Admin dashboard",
		"note": "Protected by auth middleware",
	})
}

func main() {
	// Create router
	router := NewRouter()

	// Custom 404 handler
	// Laravel: App::missing(function() {...})
	router.NotFound(func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusNotFound)
		json.NewEncoder(w).Encode(map[string]string{
			"error": "Route not found",
			"path":  r.URL.Path,
		})
	})

	// Basic routes
	// Laravel: Route::get('/', 'HomeController@index')
	router.Get("/", homeHandler)

	// Route with parameter
	// Laravel: Route::get('/users/{id}', 'UserController@show')
	router.Get("/users/{id}", userShowHandler).
		Where("id", "[0-9]+"). // Constraint: ID must be numeric
		Name("users.show")      // Named route

	// Route with multiple HTTP methods for same path
	router.Put("/users/{id}", userUpdateHandler).
		Where("id", "[0-9]+").
		Name("users.update")

	// Route with multiple parameters
	// Laravel: Route::get('/posts/{post}/comments/{comment}')
	router.Get("/posts/{post}/comments/{comment}", postCommentHandler).
		Where("post", "[0-9]+").
		Where("comment", "[0-9]+").
		Name("posts.comments.show")

	// Route groups - API routes
	// Laravel:
	// Route::group(['prefix' => 'api'], function() {
	//     Route::get('/products', 'ProductController@index');
	// });
	router.Group("/api", nil, func(r *Router) {
		r.Get("/products", apiProductsHandler).Name("api.products")
		r.Get("/products/{id}", func(w http.ResponseWriter, r *http.Request) {
			productID := GetParam(r, "id")
			w.Header().Set("Content-Type", "application/json")
			json.NewEncoder(w).Encode(map[string]interface{}{
				"action": "show product",
				"product_id": productID,
				"prefix": "API route",
			})
		}).Where("id", "[0-9]+")
	})

	// Route group with middleware (we'll implement auth middleware in next example)
	// Laravel:
	// Route::group(['prefix' => 'admin', 'middleware' => ['auth']], function() {
	//     Route::get('/dashboard', 'AdminController@dashboard');
	// });
	router.Group("/admin", nil, func(r *Router) {
		r.Get("/dashboard", adminDashboardHandler)
		r.Get("/users", func(w http.ResponseWriter, r *http.Request) {
			w.Header().Set("Content-Type", "application/json")
			json.NewEncoder(w).Encode(map[string]string{
				"message": "Admin users list",
			})
		})
	})

	// Catch-all route
	// Laravel: Route::fallback(function() {...})
	router.Any("/catch-all/{path}", func(w http.ResponseWriter, r *http.Request) {
		path := GetParam(r, "path")
		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(map[string]interface{}{
			"message": "Catch-all route",
			"path": path,
			"method": r.Method,
		})
	})

	// Print registered routes
	log.Println("Registered routes:")
	for _, route := range router.routes {
		name := route.RouteName
		if name == "" {
			name = "(unnamed)"
		}
		log.Printf("  %s %s -> %s", route.Method, route.Pattern, name)
	}

	// Start server
	server := &http.Server{
		Addr:         ":8080",
		Handler:      router,
		ReadTimeout:  15 * time.Second,
		WriteTimeout: 15 * time.Second,
	}

	log.Println("\nServer starting on http://localhost:8080")
	log.Println("\nTry these URLs:")
	log.Println("  GET  http://localhost:8080/")
	log.Println("  GET  http://localhost:8080/users/123")
	log.Println("  GET  http://localhost:8080/users/abc  (should fail constraint)")
	log.Println("  GET  http://localhost:8080/posts/1/comments/5")
	log.Println("  GET  http://localhost:8080/api/products")
	log.Println("  GET  http://localhost:8080/api/products/42")
	log.Println("  GET  http://localhost:8080/admin/dashboard")
	log.Println("  GET  http://localhost:8080/nonexistent  (404)")

	if err := server.ListenAndServe(); err != nil {
		log.Fatalf("Server failed: %v", err)
	}
}

/*
 * KEY TAKEAWAYS FOR PHP DEVELOPERS:
 *
 * 1. ROUTING PATTERNS
 *    - Laravel: Route::get('/users/{id}', ...)
 *    - Go: router.Get("/users/{id}", ...)
 *    - Very similar syntax!
 *
 * 2. ROUTE PARAMETERS
 *    - Laravel: Automatic injection or $request->route('id')
 *    - Go: Extract from context with GetParam(r, "id")
 *
 * 3. ROUTE CONSTRAINTS
 *    - Laravel: ->where('id', '[0-9]+')
 *    - Go: ->Where("id", "[0-9]+")
 *    - Same pattern!
 *
 * 4. NAMED ROUTES
 *    - Laravel: ->name('users.show')
 *    - Go: ->Name("users.show")
 *    - Useful for URL generation
 *
 * 5. ROUTE GROUPS
 *    - Laravel: Route::group(['prefix' => 'api'], ...)
 *    - Go: router.Group("/api", nil, ...)
 *    - Groups routes with common attributes
 *
 * 6. HTTP METHODS
 *    - Laravel: Route::get(), Route::post(), etc.
 *    - Go: router.Get(), router.Post(), etc.
 *    - One-to-one correspondence
 *
 * 7. CUSTOM ROUTERS
 *    - This example shows how routers work internally
 *    - In production, use gorilla/mux, chi, or gin
 *    - They provide more features and are battle-tested
 *
 * PRODUCTION ROUTERS:
 *
 * gorilla/mux (most popular):
 *   r := mux.NewRouter()
 *   r.HandleFunc("/users/{id:[0-9]+}", handler)
 *
 * chi (lightweight):
 *   r := chi.NewRouter()
 *   r.Get("/users/{id}", handler)
 *
 * gin (high performance):
 *   r := gin.Default()
 *   r.GET("/users/:id", handler)
 *
 * All follow similar patterns to what we've built here!
 */
