package main

/*
 * Advanced Handlers and Routing in Go - Chapter 16, Example 2
 *
 * PHP COMPARISON - ROUTING:
 *
 * Laravel routes/web.php:
 * Route::get('/users', [UserController::class, 'index']);
 * Route::get('/users/{id}', [UserController::class, 'show']);
 * Route::post('/users', [UserController::class, 'store']);
 *
 * Symfony routing (annotations):
 * #[Route('/users', methods: ['GET'])]
 * #[Route('/users/{id}', methods: ['GET'])]
 *
 * In Go, we use the http.ServeMux (multiplexer) for routing, or popular
 * third-party routers like gorilla/mux, chi, or gin. This example shows
 * both the standard library approach and pattern matching.
 *
 * HANDLER TYPES:
 * Go provides several ways to create handlers:
 * 1. http.HandlerFunc - a function with signature (w, r)
 * 2. http.Handler interface - any type with ServeHTTP method
 * 3. Method receivers on structs (like PHP class methods)
 */

import (
	"encoding/json"
	"fmt"
	"html/template"
	"log"
	"net/http"
	"path"
	"regexp"
	"strconv"
	"strings"
	"sync"
	"time"
)

// User represents a user model
// Similar to Eloquent models in Laravel or Doctrine entities in Symfony
type User struct {
	ID        int       `json:"id"`
	Name      string    `json:"name"`
	Email     string    `json:"email"`
	CreatedAt time.Time `json:"created_at"`
}

// UserStore manages user data
// This is a simple in-memory store, similar to using an array in PHP
// In production, this would be a database layer (like Eloquent or Doctrine)
type UserStore struct {
	mu    sync.RWMutex
	users map[int]*User
	nextID int
}

// NewUserStore creates a new user store
func NewUserStore() *UserStore {
	store := &UserStore{
		users: make(map[int]*User),
		nextID: 1,
	}

	// Add some sample data
	// Like Laravel's database seeders
	store.Create("John Doe", "john@example.com")
	store.Create("Jane Smith", "jane@example.com")
	store.Create("Bob Johnson", "bob@example.com")

	return store
}

// Create adds a new user
// Similar to: User::create(['name' => $name, 'email' => $email]);
func (s *UserStore) Create(name, email string) *User {
	s.mu.Lock()
	defer s.mu.Unlock()

	user := &User{
		ID:        s.nextID,
		Name:      name,
		Email:     email,
		CreatedAt: time.Now(),
	}

	s.users[s.nextID] = user
	s.nextID++

	return user
}

// GetAll returns all users
// Similar to: User::all();
func (s *UserStore) GetAll() []*User {
	s.mu.RLock()
	defer s.mu.RUnlock()

	users := make([]*User, 0, len(s.users))
	for _, user := range s.users {
		users = append(users, user)
	}

	return users
}

// GetByID returns a user by ID
// Similar to: User::find($id);
func (s *UserStore) GetByID(id int) (*User, bool) {
	s.mu.RLock()
	defer s.mu.RUnlock()

	user, exists := s.users[id]
	return user, exists
}

// Update updates a user
// Similar to: $user->update(['name' => $name]);
func (s *UserStore) Update(id int, name, email string) (*User, bool) {
	s.mu.Lock()
	defer s.mu.Unlock()

	user, exists := s.users[id]
	if !exists {
		return nil, false
	}

	user.Name = name
	user.Email = email

	return user, true
}

// Delete removes a user
// Similar to: $user->delete();
func (s *UserStore) Delete(id int) bool {
	s.mu.Lock()
	defer s.mu.Unlock()

	_, exists := s.users[id]
	if exists {
		delete(s.users, id)
	}

	return exists
}

// UserController handles user-related HTTP requests
// This is similar to Laravel's controllers or Symfony's controllers
// In PHP frameworks:
//
// class UserController extends Controller {
//     public function index() { ... }
//     public function show($id) { ... }
//     public function store(Request $request) { ... }
// }
type UserController struct {
	store *UserStore
	templates *template.Template
}

// NewUserController creates a new user controller
func NewUserController(store *UserStore) *UserController {
	// Parse HTML templates
	// Similar to Blade templates in Laravel or Twig in Symfony
	tmpl := template.Must(template.New("").Parse(`
<!DOCTYPE html>
<html>
<head>
    <title>{{.Title}}</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        .btn { background-color: #008CBA; color: white; padding: 10px 20px; text-decoration: none; }
    </style>
</head>
<body>
    <h1>{{.Title}}</h1>
    {{.Content}}
</body>
</html>
`))

	return &UserController{
		store: store,
		templates: tmpl,
	}
}

// Index displays all users
// Laravel equivalent: public function index()
// Route: GET /users
func (c *UserController) Index(w http.ResponseWriter, r *http.Request) {
	// Only allow GET method
	if r.Method != http.MethodGet {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	users := c.store.GetAll()

	// Check Accept header to determine response format
	// Similar to Laravel's response()->json() or response()->view()
	accept := r.Header.Get("Accept")

	if strings.Contains(accept, "application/json") {
		// Return JSON response
		// Laravel: return response()->json($users);
		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(map[string]interface{}{
			"users": users,
			"count": len(users),
		})
		return
	}

	// Return HTML response
	// Laravel: return view('users.index', ['users' => $users]);
	w.Header().Set("Content-Type", "text/html; charset=utf-8")

	var html strings.Builder
	html.WriteString("<table>")
	html.WriteString("<tr><th>ID</th><th>Name</th><th>Email</th><th>Created</th><th>Actions</th></tr>")

	for _, user := range users {
		html.WriteString(fmt.Sprintf(
			"<tr><td>%d</td><td>%s</td><td>%s</td><td>%s</td><td><a href='/users/%d'>View</a></td></tr>",
			user.ID,
			user.Name,
			user.Email,
			user.CreatedAt.Format("2006-01-02 15:04"),
			user.ID,
		))
	}

	html.WriteString("</table>")
	html.WriteString("<br><a class='btn' href='/users/create'>Create New User</a>")

	data := map[string]interface{}{
		"Title": "All Users",
		"Content": template.HTML(html.String()),
	}

	c.templates.Execute(w, data)
}

// Show displays a single user
// Laravel equivalent: public function show($id)
// Route: GET /users/{id}
func (c *UserController) Show(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodGet {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	// Extract ID from URL path
	// In Laravel/Symfony, this is done automatically: Route::get('/users/{id}')
	// In Go with standard library, we need to parse it manually
	id, err := extractID(r.URL.Path, "/users/")
	if err != nil {
		http.Error(w, "Invalid user ID", http.StatusBadRequest)
		return
	}

	user, exists := c.store.GetByID(id)
	if !exists {
		// Laravel: abort(404);
		http.Error(w, "User not found", http.StatusNotFound)
		return
	}

	// Check for JSON response
	accept := r.Header.Get("Accept")
	if strings.Contains(accept, "application/json") {
		// Laravel: return response()->json($user);
		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(user)
		return
	}

	// HTML response
	w.Header().Set("Content-Type", "text/html; charset=utf-8")

	html := fmt.Sprintf(`
		<div>
			<p><strong>ID:</strong> %d</p>
			<p><strong>Name:</strong> %s</p>
			<p><strong>Email:</strong> %s</p>
			<p><strong>Created:</strong> %s</p>
			<br>
			<a class="btn" href="/users">Back to List</a>
		</div>
	`, user.ID, user.Name, user.Email, user.CreatedAt.Format("2006-01-02 15:04:05"))

	data := map[string]interface{}{
		"Title": fmt.Sprintf("User: %s", user.Name),
		"Content": template.HTML(html),
	}

	c.templates.Execute(w, data)
}

// Create shows the create form
// Laravel: public function create()
// Route: GET /users/create
func (c *UserController) Create(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodGet {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	w.Header().Set("Content-Type", "text/html; charset=utf-8")

	// Laravel: return view('users.create');
	html := `
		<form method="POST" action="/users/store">
			<p>
				<label>Name:</label><br>
				<input type="text" name="name" required style="width: 300px; padding: 5px;">
			</p>
			<p>
				<label>Email:</label><br>
				<input type="email" name="email" required style="width: 300px; padding: 5px;">
			</p>
			<p>
				<button type="submit" class="btn">Create User</button>
				<a href="/users">Cancel</a>
			</p>
		</form>
	`

	data := map[string]interface{}{
		"Title": "Create New User",
		"Content": template.HTML(html),
	}

	c.templates.Execute(w, data)
}

// Store handles creating a new user
// Laravel: public function store(Request $request)
// Route: POST /users/store
func (c *UserController) Store(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	// Parse form data
	// In PHP: $_POST['name'], $_POST['email']
	// Laravel: $request->input('name')
	if err := r.ParseForm(); err != nil {
		http.Error(w, "Failed to parse form", http.StatusBadRequest)
		return
	}

	name := r.FormValue("name")
	email := r.FormValue("email")

	// Basic validation
	// Laravel uses FormRequest validation or validator facade
	if name == "" || email == "" {
		http.Error(w, "Name and email are required", http.StatusBadRequest)
		return
	}

	// Create user
	user := c.store.Create(name, email)

	// Check if JSON response requested
	if r.Header.Get("Content-Type") == "application/json" {
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusCreated)
		json.NewEncoder(w).Encode(user)
		return
	}

	// Redirect to user list
	// Laravel: return redirect('/users');
	http.Redirect(w, r, "/users", http.StatusSeeOther)
}

// extractID extracts an integer ID from a URL path
// Helper function for manual route parameter extraction
// In Laravel/Symfony, this is done automatically by the router
func extractID(urlPath, prefix string) (int, error) {
	// Remove prefix and trailing slash
	idStr := strings.TrimPrefix(urlPath, prefix)
	idStr = strings.TrimSuffix(idStr, "/")

	// Handle /users/{id}/edit or /users/{id}/delete patterns
	parts := strings.Split(idStr, "/")
	if len(parts) > 0 {
		idStr = parts[0]
	}

	// Convert to integer
	id, err := strconv.Atoi(idStr)
	if err != nil {
		return 0, err
	}

	return id, nil
}

// Router handles routing logic
// This is a simplified version of what routers like gorilla/mux or chi provide
// In PHP frameworks, this is built into the framework
type Router struct {
	routes map[string]http.HandlerFunc
	patterns map[*regexp.Regexp]http.HandlerFunc
}

// NewRouter creates a new router
func NewRouter() *Router {
	return &Router{
		routes: make(map[string]http.HandlerFunc),
		patterns: make(map[*regexp.Regexp]http.HandlerFunc),
	}
}

// Handle registers a handler for an exact path
// Similar to: Route::get('/exact-path', handler)
func (rt *Router) Handle(pattern string, handler http.HandlerFunc) {
	rt.routes[pattern] = handler
}

// HandlePattern registers a handler for a regex pattern
// Similar to: Route::get('/users/{id}', handler)
func (rt *Router) HandlePattern(pattern string, handler http.HandlerFunc) {
	re := regexp.MustCompile(pattern)
	rt.patterns[re] = handler
}

// ServeHTTP implements http.Handler interface
// This is called for every request
func (rt *Router) ServeHTTP(w http.ResponseWriter, r *http.Request) {
	// Clean the path
	cleanPath := path.Clean(r.URL.Path)

	// Try exact match first
	if handler, exists := rt.routes[cleanPath]; exists {
		handler(w, r)
		return
	}

	// Try pattern matching
	for pattern, handler := range rt.patterns {
		if pattern.MatchString(cleanPath) {
			handler(w, r)
			return
		}
	}

	// No match found
	// Laravel: 404 error page
	http.NotFound(w, r)
}

// HomeHandler serves the home page
func HomeHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "text/html; charset=utf-8")

	html := `
<!DOCTYPE html>
<html>
<head>
    <title>Go Web Application</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; }
        .btn { background-color: #4CAF50; color: white; padding: 15px 32px;
               text-decoration: none; display: inline-block; margin: 10px; }
    </style>
</head>
<body>
    <h1>Welcome to Go Web Application</h1>
    <p>This demonstrates handlers and routing in Go.</p>
    <div>
        <a class="btn" href="/users">View Users</a>
        <a class="btn" href="/users/create">Create User</a>
        <a class="btn" href="/api/health">API Health</a>
    </div>
</body>
</html>
	`

	fmt.Fprint(w, html)
}

// HealthHandler provides API health check
// Common in modern web applications for monitoring
func HealthHandler(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	response := map[string]interface{}{
		"status": "healthy",
		"timestamp": time.Now().Unix(),
		"service": "go-web-app",
	}

	json.NewEncoder(w).Encode(response)
}

func main() {
	// Initialize user store
	// Laravel: Models are auto-discovered and use database
	store := NewUserStore()

	// Initialize controller
	// Laravel: Controllers are in app/Http/Controllers
	userController := NewUserController(store)

	// Create router
	// Laravel: routes/web.php and routes/api.php
	router := NewRouter()

	// Register routes
	// Exact path matches
	router.Handle("/", HomeHandler)
	router.Handle("/users", userController.Index)
	router.Handle("/users/create", userController.Create)
	router.Handle("/users/store", userController.Store)
	router.Handle("/api/health", HealthHandler)

	// Pattern matches for dynamic routes
	// Match: /users/1, /users/123, etc.
	router.HandlePattern("^/users/[0-9]+$", userController.Show)

	// Create server
	server := &http.Server{
		Addr:         ":8080",
		Handler:      router,
		ReadTimeout:  15 * time.Second,
		WriteTimeout: 15 * time.Second,
		IdleTimeout:  60 * time.Second,
	}

	// Start server
	log.Println("Server starting on http://localhost:8080")
	log.Println("Routes:")
	log.Println("  GET  /              - Home page")
	log.Println("  GET  /users         - List all users")
	log.Println("  GET  /users/{id}    - Show user details")
	log.Println("  GET  /users/create  - Show create form")
	log.Println("  POST /users/store   - Create new user")
	log.Println("  GET  /api/health    - Health check")
	log.Println("\nTry:")
	log.Println("  curl http://localhost:8080/users")
	log.Println("  curl -H 'Accept: application/json' http://localhost:8080/users")
	log.Println("  curl http://localhost:8080/api/health")

	if err := server.ListenAndServe(); err != nil {
		log.Fatalf("Server failed: %v", err)
	}
}

/*
 * KEY TAKEAWAYS FOR PHP DEVELOPERS:
 *
 * 1. CONTROLLERS
 *    - PHP: Classes with methods (UserController@index)
 *    - Go: Structs with methods (same pattern!)
 *
 * 2. ROUTING
 *    - PHP: Declarative (Route::get()) or file-based
 *    - Go: Programmatic registration
 *    - Third-party routers (gorilla/mux, chi) are more like Laravel's router
 *
 * 3. ROUTE PARAMETERS
 *    - PHP: Automatic extraction {id} becomes $id
 *    - Go: Manual extraction with standard library, automatic with routers
 *
 * 4. RESPONSES
 *    - PHP: return view() or return response()->json()
 *    - Go: Write to http.ResponseWriter
 *
 * 5. MODELS/DATA
 *    - PHP: Eloquent ORM, Doctrine ORM
 *    - Go: Struct types with methods (this example uses in-memory storage)
 *
 * 6. TEMPLATES
 *    - PHP: Blade, Twig
 *    - Go: html/template package (similar syntax)
 *
 * 7. THREAD SAFETY
 *    - PHP: Each request is isolated (process/thread)
 *    - Go: Shared memory requires sync.Mutex for concurrent access
 *
 * RECOMMENDED THIRD-PARTY ROUTERS:
 *
 * - gorilla/mux: Most popular, feature-rich
 * - chi: Lightweight, fast, idiomatic
 * - gin: High performance, includes more features
 * - echo: Fast, minimalist
 *
 * These provide Laravel-like routing with automatic parameter extraction.
 */
