package main

/*
 * Complete REST API in Go - Chapter 18, Example 2
 *
 * PHP FRAMEWORK REST API COMPARISON:
 *
 * Laravel API Resource Controllers:
 * Route::apiResource('posts', PostController::class);
 * // Generates: index, store, show, update, destroy
 *
 * class PostController extends Controller {
 *     public function index() { return Post::all(); }
 *     public function store(Request $request) { ... }
 *     public function show($id) { return Post::find($id); }
 *     public function update(Request $request, $id) { ... }
 *     public function destroy($id) { ... }
 * }
 *
 * Symfony REST API:
 * #[Route('/api/posts', methods: ['GET'])]
 * #[Route('/api/posts/{id}', methods: ['GET', 'PUT', 'DELETE'])]
 *
 * This example implements a complete REST API for a blog with:
 * - CRUD operations (Create, Read, Update, Delete)
 * - Proper HTTP methods and status codes
 * - Request validation
 * - Error handling
 * - Pagination
 * - Filtering and sorting
 * - Resource relationships
 *
 * REST conventions:
 * GET    /posts       - List all posts (index)
 * POST   /posts       - Create new post (store)
 * GET    /posts/{id}  - Get single post (show)
 * PUT    /posts/{id}  - Update post (update)
 * DELETE /posts/{id}  - Delete post (destroy)
 */

import (
	"encoding/json"
	"log"
	"net/http"
	"regexp"
	"strconv"
	"strings"
	"sync"
	"time"
)

// Post represents a blog post
// Laravel: class Post extends Model { ... }
type Post struct {
	ID        int       `json:"id"`
	Title     string    `json:"title"`
	Slug      string    `json:"slug"`
	Content   string    `json:"content"`
	Excerpt   string    `json:"excerpt,omitempty"`
	AuthorID  int       `json:"author_id"`
	Status    string    `json:"status"` // draft, published
	Tags      []string  `json:"tags,omitempty"`
	CreatedAt time.Time `json:"created_at"`
	UpdatedAt time.Time `json:"updated_at"`
}

// Author represents a post author
type Author struct {
	ID        int       `json:"id"`
	Name      string    `json:"name"`
	Email     string    `json:"email"`
	Bio       string    `json:"bio,omitempty"`
	CreatedAt time.Time `json:"created_at"`
}

// PostWithAuthor includes author details
// Laravel: Post::with('author')->get()
type PostWithAuthor struct {
	Post
	Author *Author `json:"author,omitempty"`
}

// CreatePostRequest represents post creation request
// Laravel: FormRequest validation class
type CreatePostRequest struct {
	Title    string   `json:"title"`
	Content  string   `json:"content"`
	AuthorID int      `json:"author_id"`
	Status   string   `json:"status"`
	Tags     []string `json:"tags"`
}

// UpdatePostRequest represents post update request
type UpdatePostRequest struct {
	Title   *string  `json:"title,omitempty"`
	Content *string  `json:"content,omitempty"`
	Status  *string  `json:"status,omitempty"`
	Tags    []string `json:"tags,omitempty"`
}

// APIResponse is a standard response wrapper
type APIResponse struct {
	Success bool        `json:"success"`
	Data    interface{} `json:"data,omitempty"`
	Error   *APIError   `json:"error,omitempty"`
	Meta    *Meta       `json:"meta,omitempty"`
}

// APIError represents an error response
type APIError struct {
	Code    string            `json:"code"`
	Message string            `json:"message"`
	Details map[string]string `json:"details,omitempty"`
}

// Meta contains pagination and other metadata
// Laravel: $posts->paginate(15) includes meta automatically
type Meta struct {
	Page       int `json:"page,omitempty"`
	PerPage    int `json:"per_page,omitempty"`
	Total      int `json:"total,omitempty"`
	TotalPages int `json:"total_pages,omitempty"`
}

// PostStore manages posts (in-memory for demo)
// Laravel: Database with Eloquent ORM
// In production, this would use a real database
type PostStore struct {
	mu      sync.RWMutex
	posts   map[int]*Post
	authors map[int]*Author
	nextID  int
}

// NewPostStore creates a new post store with sample data
func NewPostStore() *PostStore {
	store := &PostStore{
		posts:   make(map[int]*Post),
		authors: make(map[int]*Author),
		nextID:  1,
	}

	// Seed sample data
	// Laravel: database/seeders/PostSeeder.php
	author1 := &Author{
		ID:        1,
		Name:      "John Doe",
		Email:     "john@example.com",
		Bio:       "Tech blogger and developer",
		CreatedAt: time.Now(),
	}

	author2 := &Author{
		ID:        2,
		Name:      "Jane Smith",
		Email:     "jane@example.com",
		Bio:       "Content creator",
		CreatedAt: time.Now(),
	}

	store.authors[1] = author1
	store.authors[2] = author2

	// Create sample posts
	posts := []*Post{
		{
			Title:    "Getting Started with Go",
			Slug:     "getting-started-with-go",
			Content:  "Go is a statically typed, compiled programming language...",
			Excerpt:  "Introduction to Go programming",
			AuthorID: 1,
			Status:   "published",
			Tags:     []string{"go", "programming", "tutorial"},
		},
		{
			Title:    "Building REST APIs",
			Slug:     "building-rest-apis",
			Content:  "REST APIs are the backbone of modern web applications...",
			Excerpt:  "Learn to build REST APIs",
			AuthorID: 1,
			Status:   "published",
			Tags:     []string{"rest", "api", "web"},
		},
		{
			Title:    "Draft Post",
			Slug:     "draft-post",
			Content:  "This is a draft post...",
			AuthorID: 2,
			Status:   "draft",
			Tags:     []string{"draft"},
		},
	}

	for _, post := range posts {
		store.Create(post)
	}

	return store
}

// Create creates a new post
// Laravel: Post::create($data)
func (s *PostStore) Create(post *Post) *Post {
	s.mu.Lock()
	defer s.mu.Unlock()

	post.ID = s.nextID
	post.CreatedAt = time.Now()
	post.UpdatedAt = time.Now()

	s.posts[post.ID] = post
	s.nextID++

	return post
}

// GetAll returns all posts with filtering
// Laravel: Post::where('status', 'published')->orderBy('created_at', 'desc')->get()
func (s *PostStore) GetAll(filters map[string]string, page, perPage int) ([]*Post, int) {
	s.mu.RLock()
	defer s.mu.RUnlock()

	// Collect all posts
	allPosts := make([]*Post, 0, len(s.posts))
	for _, post := range s.posts {
		allPosts = append(allPosts, post)
	}

	// Filter posts
	filtered := make([]*Post, 0)
	for _, post := range allPosts {
		if s.matchesFilters(post, filters) {
			filtered = append(filtered, post)
		}
	}

	total := len(filtered)

	// Pagination
	// Laravel: $posts->paginate(15)
	start := (page - 1) * perPage
	end := start + perPage

	if start > total {
		return []*Post{}, total
	}

	if end > total {
		end = total
	}

	return filtered[start:end], total
}

// GetByID returns a post by ID
// Laravel: Post::find($id) or Post::findOrFail($id)
func (s *PostStore) GetByID(id int) (*Post, bool) {
	s.mu.RLock()
	defer s.mu.RUnlock()

	post, exists := s.posts[id]
	return post, exists
}

// Update updates a post
// Laravel: $post->update($data)
func (s *PostStore) Update(id int, updates map[string]interface{}) (*Post, bool) {
	s.mu.Lock()
	defer s.mu.Unlock()

	post, exists := s.posts[id]
	if !exists {
		return nil, false
	}

	// Apply updates
	if title, ok := updates["title"].(string); ok {
		post.Title = title
		post.Slug = slugify(title)
	}
	if content, ok := updates["content"].(string); ok {
		post.Content = content
	}
	if status, ok := updates["status"].(string); ok {
		post.Status = status
	}
	if tags, ok := updates["tags"].([]string); ok {
		post.Tags = tags
	}

	post.UpdatedAt = time.Now()

	return post, true
}

// Delete deletes a post
// Laravel: $post->delete() or Post::destroy($id)
func (s *PostStore) Delete(id int) bool {
	s.mu.Lock()
	defer s.mu.Unlock()

	_, exists := s.posts[id]
	if exists {
		delete(s.posts, id)
	}

	return exists
}

// GetAuthor returns an author by ID
// Laravel: Author::find($id)
func (s *PostStore) GetAuthor(id int) (*Author, bool) {
	s.mu.RLock()
	defer s.mu.RUnlock()

	author, exists := s.authors[id]
	return author, exists
}

// matchesFilters checks if post matches filters
func (s *PostStore) matchesFilters(post *Post, filters map[string]string) bool {
	if status, ok := filters["status"]; ok && post.Status != status {
		return false
	}

	if authorID, ok := filters["author_id"]; ok {
		id, _ := strconv.Atoi(authorID)
		if post.AuthorID != id {
			return false
		}
	}

	if tag, ok := filters["tag"]; ok {
		hasTag := false
		for _, t := range post.Tags {
			if t == tag {
				hasTag = true
				break
			}
		}
		if !hasTag {
			return false
		}
	}

	return true
}

// PostAPI handles HTTP requests for posts
// Laravel: PostController class
type PostAPI struct {
	postStore *PostStore
}

// NewPostAPI creates a new post API handler
func NewPostAPI(store *PostStore) *PostAPI {
	return &PostAPI{postStore: store}
}

// ServeHTTP implements http.Handler interface
// Routes requests to appropriate handler methods
func (api *PostAPI) ServeHTTP(w http.ResponseWriter, r *http.Request) {
	// Extract ID from path if present
	// /posts or /posts/123
	path := strings.TrimPrefix(r.URL.Path, "/posts")
	path = strings.Trim(path, "/")

	// Route to appropriate handler
	if path == "" {
		// /posts
		switch r.Method {
		case http.MethodGet:
			api.index(w, r)
		case http.MethodPost:
			api.store(w, r)
		default:
			api.methodNotAllowed(w)
		}
	} else {
		// /posts/{id}
		id, err := strconv.Atoi(path)
		if err != nil {
			api.errorResponse(w, http.StatusBadRequest, "invalid_id", "Invalid post ID", nil)
			return
		}

		switch r.Method {
		case http.MethodGet:
			api.show(w, r, id)
		case http.MethodPut:
			api.update(w, r, id)
		case http.MethodDelete:
			api.destroy(w, r, id)
		default:
			api.methodNotAllowed(w)
		}
	}
}

// index lists all posts
// Laravel: public function index()
// GET /posts?status=published&page=1&per_page=10
func (api *PostAPI) index(w http.ResponseWriter, r *http.Request) {
	// Parse query parameters
	// Laravel: $request->query('status')
	query := r.URL.Query()

	filters := make(map[string]string)
	if status := query.Get("status"); status != "" {
		filters["status"] = status
	}
	if authorID := query.Get("author_id"); authorID != "" {
		filters["author_id"] = authorID
	}
	if tag := query.Get("tag"); tag != "" {
		filters["tag"] = tag
	}

	// Pagination
	// Laravel: $request->query('page', 1)
	page, _ := strconv.Atoi(query.Get("page"))
	if page < 1 {
		page = 1
	}

	perPage, _ := strconv.Atoi(query.Get("per_page"))
	if perPage < 1 {
		perPage = 10
	}
	if perPage > 100 {
		perPage = 100
	}

	// Get posts
	posts, total := api.postStore.GetAll(filters, page, perPage)

	// Include author if requested
	// Laravel: Post::with('author')->get()
	includeAuthor := query.Get("include") == "author"

	var data interface{}
	if includeAuthor {
		postsWithAuthor := make([]PostWithAuthor, len(posts))
		for i, post := range posts {
			author, _ := api.postStore.GetAuthor(post.AuthorID)
			postsWithAuthor[i] = PostWithAuthor{
				Post:   *post,
				Author: author,
			}
		}
		data = postsWithAuthor
	} else {
		data = posts
	}

	// Calculate pagination
	totalPages := (total + perPage - 1) / perPage

	// Send response
	// Laravel: return response()->json($posts)
	api.successResponse(w, http.StatusOK, data, &Meta{
		Page:       page,
		PerPage:    perPage,
		Total:      total,
		TotalPages: totalPages,
	})
}

// store creates a new post
// Laravel: public function store(Request $request)
// POST /posts
func (api *PostAPI) store(w http.ResponseWriter, r *http.Request) {
	var req CreatePostRequest

	// Decode request body
	// Laravel: $request->validated()
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		api.errorResponse(w, http.StatusBadRequest, "invalid_json", "Invalid JSON", nil)
		return
	}
	defer r.Body.Close()

	// Validate request
	// Laravel: $request->validate([...])
	if errors := api.validateCreateRequest(req); len(errors) > 0 {
		api.errorResponse(w, http.StatusUnprocessableEntity, "validation_error", "Validation failed", errors)
		return
	}

	// Create post
	post := &Post{
		Title:    req.Title,
		Slug:     slugify(req.Title),
		Content:  req.Content,
		Excerpt:  excerpt(req.Content, 100),
		AuthorID: req.AuthorID,
		Status:   req.Status,
		Tags:     req.Tags,
	}

	created := api.postStore.Create(post)

	// Return created post
	// Laravel: return response()->json($post, 201)
	api.successResponse(w, http.StatusCreated, created, nil)
}

// show retrieves a single post
// Laravel: public function show($id)
// GET /posts/{id}
func (api *PostAPI) show(w http.ResponseWriter, r *http.Request, id int) {
	post, exists := api.postStore.GetByID(id)

	if !exists {
		// Laravel: abort(404)
		api.errorResponse(w, http.StatusNotFound, "not_found", "Post not found", nil)
		return
	}

	// Include author if requested
	query := r.URL.Query()
	includeAuthor := query.Get("include") == "author"

	var data interface{}
	if includeAuthor {
		author, _ := api.postStore.GetAuthor(post.AuthorID)
		data = PostWithAuthor{
			Post:   *post,
			Author: author,
		}
	} else {
		data = post
	}

	api.successResponse(w, http.StatusOK, data, nil)
}

// update updates a post
// Laravel: public function update(Request $request, $id)
// PUT /posts/{id}
func (api *PostAPI) update(w http.ResponseWriter, r *http.Request, id int) {
	// Check if post exists
	_, exists := api.postStore.GetByID(id)
	if !exists {
		api.errorResponse(w, http.StatusNotFound, "not_found", "Post not found", nil)
		return
	}

	var req UpdatePostRequest

	// Decode request
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		api.errorResponse(w, http.StatusBadRequest, "invalid_json", "Invalid JSON", nil)
		return
	}
	defer r.Body.Close()

	// Validate request
	if errors := api.validateUpdateRequest(req); len(errors) > 0 {
		api.errorResponse(w, http.StatusUnprocessableEntity, "validation_error", "Validation failed", errors)
		return
	}

	// Build updates map
	updates := make(map[string]interface{})
	if req.Title != nil {
		updates["title"] = *req.Title
	}
	if req.Content != nil {
		updates["content"] = *req.Content
	}
	if req.Status != nil {
		updates["status"] = *req.Status
	}
	if req.Tags != nil {
		updates["tags"] = req.Tags
	}

	// Update post
	updated, _ := api.postStore.Update(id, updates)

	api.successResponse(w, http.StatusOK, updated, nil)
}

// destroy deletes a post
// Laravel: public function destroy($id)
// DELETE /posts/{id}
func (api *PostAPI) destroy(w http.ResponseWriter, r *http.Request, id int) {
	deleted := api.postStore.Delete(id)

	if !deleted {
		api.errorResponse(w, http.StatusNotFound, "not_found", "Post not found", nil)
		return
	}

	// Return 204 No Content
	// Laravel: return response()->noContent()
	w.WriteHeader(http.StatusNoContent)
}

// Validation methods

func (api *PostAPI) validateCreateRequest(req CreatePostRequest) map[string]string {
	errors := make(map[string]string)

	// Laravel: 'title' => 'required|string|max:255'
	if req.Title == "" {
		errors["title"] = "Title is required"
	} else if len(req.Title) > 255 {
		errors["title"] = "Title must not exceed 255 characters"
	}

	// Laravel: 'content' => 'required|string'
	if req.Content == "" {
		errors["content"] = "Content is required"
	}

	// Laravel: 'author_id' => 'required|exists:authors,id'
	if req.AuthorID == 0 {
		errors["author_id"] = "Author ID is required"
	} else {
		if _, exists := api.postStore.GetAuthor(req.AuthorID); !exists {
			errors["author_id"] = "Author does not exist"
		}
	}

	// Laravel: 'status' => 'required|in:draft,published'
	if req.Status == "" {
		errors["status"] = "Status is required"
	} else if req.Status != "draft" && req.Status != "published" {
		errors["status"] = "Status must be 'draft' or 'published'"
	}

	return errors
}

func (api *PostAPI) validateUpdateRequest(req UpdatePostRequest) map[string]string {
	errors := make(map[string]string)

	if req.Title != nil && len(*req.Title) > 255 {
		errors["title"] = "Title must not exceed 255 characters"
	}

	if req.Status != nil && *req.Status != "draft" && *req.Status != "published" {
		errors["status"] = "Status must be 'draft' or 'published'"
	}

	return errors
}

// Response helpers

func (api *PostAPI) successResponse(w http.ResponseWriter, statusCode int, data interface{}, meta *Meta) {
	response := APIResponse{
		Success: true,
		Data:    data,
		Meta:    meta,
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(statusCode)
	json.NewEncoder(w).Encode(response)
}

func (api *PostAPI) errorResponse(w http.ResponseWriter, statusCode int, code, message string, details map[string]string) {
	response := APIResponse{
		Success: false,
		Error: &APIError{
			Code:    code,
			Message: message,
			Details: details,
		},
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(statusCode)
	json.NewEncoder(w).Encode(response)
}

func (api *PostAPI) methodNotAllowed(w http.ResponseWriter) {
	api.errorResponse(w, http.StatusMethodNotAllowed, "method_not_allowed", "Method not allowed", nil)
}

// Utility functions

func slugify(s string) string {
	// Simple slugify: lowercase and replace spaces with hyphens
	s = strings.ToLower(s)
	s = regexp.MustCompile(`[^a-z0-9]+`).ReplaceAllString(s, "-")
	s = strings.Trim(s, "-")
	return s
}

func excerpt(s string, length int) string {
	if len(s) <= length {
		return s
	}
	return s[:length] + "..."
}

func main() {
	// Initialize store
	store := NewPostStore()

	// Create API handler
	api := NewPostAPI(store)

	// Register routes
	// Laravel: Route::apiResource('posts', PostController::class)
	http.Handle("/posts", api)
	http.Handle("/posts/", api)

	// Health check endpoint
	http.HandleFunc("/health", func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Content-Type", "application/json")
		json.NewEncoder(w).Encode(map[string]string{
			"status": "healthy",
			"time":   time.Now().Format(time.RFC3339),
		})
	})

	log.Println("REST API Server starting on http://localhost:8080")
	log.Println("\nAPI Endpoints:")
	log.Println("  GET    /posts              - List all posts")
	log.Println("  POST   /posts              - Create new post")
	log.Println("  GET    /posts/{id}         - Get single post")
	log.Println("  PUT    /posts/{id}         - Update post")
	log.Println("  DELETE /posts/{id}         - Delete post")
	log.Println("\nQuery Parameters:")
	log.Println("  ?status=published          - Filter by status")
	log.Println("  ?author_id=1               - Filter by author")
	log.Println("  ?tag=go                    - Filter by tag")
	log.Println("  ?page=1&per_page=10        - Pagination")
	log.Println("  ?include=author            - Include author details")
	log.Println("\nExample Commands:")
	log.Println("  curl http://localhost:8080/posts")
	log.Println("  curl http://localhost:8080/posts/1")
	log.Println(`  curl -X POST http://localhost:8080/posts -H "Content-Type: application/json" -d '{"title":"New Post","content":"Content here","author_id":1,"status":"published","tags":["go"]}'`)
	log.Println(`  curl -X PUT http://localhost:8080/posts/1 -H "Content-Type: application/json" -d '{"title":"Updated Title"}'`)
	log.Println("  curl -X DELETE http://localhost:8080/posts/1")

	if err := http.ListenAndServe(":8080", nil); err != nil {
		log.Fatalf("Server failed: %v", err)
	}
}

/*
 * KEY TAKEAWAYS FOR PHP DEVELOPERS:
 *
 * 1. REST CONVENTIONS
 *    - Both Go and Laravel follow same REST conventions
 *    - HTTP methods: GET, POST, PUT, DELETE
 *    - Status codes: 200, 201, 204, 400, 404, 422, 500
 *
 * 2. RESOURCE CONTROLLERS
 *    - Laravel: Route::apiResource() generates 5 routes
 *    - Go: Manual routing but same pattern (index, store, show, update, destroy)
 *
 * 3. VALIDATION
 *    - Laravel: FormRequest or $request->validate()
 *    - Go: Manual validation or validation libraries
 *    - Both return 422 for validation errors
 *
 * 4. RESPONSE FORMAT
 *    - Laravel: return response()->json($data, $statusCode)
 *    - Go: json.NewEncoder(w).Encode(data) with w.WriteHeader()
 *
 * 5. ELOQUENT vs STRUCTS
 *    - Laravel: Eloquent models with database
 *    - Go: Structs with repository pattern
 *    - Same operations: Create, Read, Update, Delete
 *
 * 6. RELATIONSHIPS
 *    - Laravel: Post::with('author')->get()
 *    - Go: Manual loading or eager loading implementation
 *
 * 7. PAGINATION
 *    - Laravel: $posts->paginate(15) - automatic
 *    - Go: Manual calculation and slicing
 *
 * 8. FILTERING
 *    - Laravel: Post::where('status', 'published')->get()
 *    - Go: Manual filtering in query methods
 *
 * HTTP STATUS CODES:
 *
 * 200 OK               - Successful GET/PUT
 * 201 Created          - Successful POST
 * 204 No Content       - Successful DELETE
 * 400 Bad Request      - Invalid JSON or parameters
 * 404 Not Found        - Resource not found
 * 422 Unprocessable    - Validation errors
 * 500 Server Error     - Internal error
 *
 * LARAVEL EQUIVALENTS:
 *
 * GET /posts           -> PostController@index
 * POST /posts          -> PostController@store
 * GET /posts/{id}      -> PostController@show
 * PUT /posts/{id}      -> PostController@update
 * DELETE /posts/{id}   -> PostController@destroy
 */
