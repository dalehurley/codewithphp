package main

/*
 * JSON Encoding and Decoding in Go - Chapter 18, Example 1
 *
 * PHP JSON COMPARISON:
 *
 * PHP has built-in JSON functions:
 * - json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
 * - json_decode($json, true)  // true for associative array
 * - json_last_error()
 * - json_last_error_msg()
 *
 * Laravel JSON:
 * - return response()->json($data);
 * - $request->json()->all()
 * - $model->toJson()
 *
 * Symfony JSON:
 * - $serializer->serialize($data, 'json');
 * - $serializer->deserialize($json, Type::class, 'json');
 *
 * In Go, the encoding/json package provides:
 * - json.Marshal() - encode to JSON (like json_encode)
 * - json.Unmarshal() - decode from JSON (like json_decode)
 * - json.Encoder - streaming encoder
 * - json.Decoder - streaming decoder
 * - Struct tags for field mapping
 *
 * Key differences:
 * 1. PHP is dynamically typed, Go requires struct definitions
 * 2. Go uses struct tags to control JSON field names
 * 3. Go's JSON is type-safe - validation at compile time
 * 4. Both support custom marshaling/unmarshaling
 *
 * This example demonstrates:
 * - Basic encoding/decoding
 * - Struct tags and field mapping
 * - Custom JSON marshaling
 * - Handling different data types
 * - Error handling
 * - Best practices
 */

import (
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"strings"
	"time"
)

// Basic struct for JSON encoding
// PHP: $user = ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'];
// Laravel: User::find(1)->toArray()
type User struct {
	// Struct tags control JSON encoding
	// `json:"field_name"` sets the JSON key
	ID       int    `json:"id"`
	Name     string `json:"name"`
	Email    string `json:"email"`
	Password string `json:"-"` // `-` means skip this field (like Laravel's $hidden)

	// Tags with options:
	// `json:"field,omitempty"` - omit if zero value
	// `json:"field,string"` - encode as string
	// `json:"-"` - skip this field
	Age      int       `json:"age,omitempty"`        // Omit if 0
	IsActive bool      `json:"is_active"`            // Snake case in JSON
	CreatedAt time.Time `json:"created_at"`          // Auto-formats time
	UpdatedAt *time.Time `json:"updated_at,omitempty"` // Omit if nil
}

// MarshalJSON implements custom JSON encoding
// Similar to Laravel's toArray() or Symfony's JsonSerializable
// This gives full control over JSON representation
func (u User) MarshalJSON() ([]byte, error) {
	// Create custom representation
	type Alias User // Avoid recursion by creating alias

	return json.Marshal(&struct {
		Alias
		PasswordHidden bool   `json:"password_hidden"`
		MemberSince    string `json:"member_since"`
	}{
		Alias:          (Alias)(u),
		PasswordHidden: u.Password != "",
		MemberSince:    u.CreatedAt.Format("2006-01-02"),
	})
}

// Product demonstrates different field types
// PHP: $product = ['id' => 1, 'name' => 'Widget', 'price' => 99.99, 'tags' => ['electronics', 'gadget']];
type Product struct {
	ID          int                    `json:"id"`
	Name        string                 `json:"name"`
	Price       float64                `json:"price"`
	InStock     bool                   `json:"in_stock"`
	Tags        []string               `json:"tags"`                  // Array
	Metadata    map[string]interface{} `json:"metadata"`              // Object with any values
	Variants    []ProductVariant       `json:"variants,omitempty"`    // Nested objects
	DiscountPct *float64               `json:"discount_percent,omitempty"` // Pointer for optional
}

// ProductVariant is a nested struct
type ProductVariant struct {
	SKU      string  `json:"sku"`
	Color    string  `json:"color"`
	Size     string  `json:"size"`
	Price    float64 `json:"price"`
	Quantity int     `json:"quantity"`
}

// APIResponse is a standard response wrapper
// Laravel: return response()->json(['success' => true, 'data' => $data]);
type APIResponse struct {
	Success   bool        `json:"success"`
	Data      interface{} `json:"data,omitempty"`
	Error     string      `json:"error,omitempty"`
	Message   string      `json:"message,omitempty"`
	Timestamp int64       `json:"timestamp"`
}

// PaginatedResponse wraps paginated data
// Laravel: $users->paginate(15)
type PaginatedResponse struct {
	Data       interface{} `json:"data"`
	Page       int         `json:"page"`
	PerPage    int         `json:"per_page"`
	Total      int         `json:"total"`
	TotalPages int         `json:"total_pages"`
	HasMore    bool        `json:"has_more"`
}

// Custom time type with specific format
// PHP: date('Y-m-d H:i:s', $timestamp)
type CustomTime struct {
	time.Time
}

// MarshalJSON for custom time format
func (ct CustomTime) MarshalJSON() ([]byte, error) {
	formatted := ct.Time.Format("2006-01-02 15:04:05")
	return json.Marshal(formatted)
}

// UnmarshalJSON for custom time format
func (ct *CustomTime) UnmarshalJSON(data []byte) error {
	var timeStr string
	if err := json.Unmarshal(data, &timeStr); err != nil {
		return err
	}

	parsed, err := time.Parse("2006-01-02 15:04:05", timeStr)
	if err != nil {
		return err
	}

	ct.Time = parsed
	return nil
}

// Handlers demonstrating JSON encoding/decoding

func basicEncodingHandler(w http.ResponseWriter, r *http.Request) {
	// Create user
	// PHP: $user = new User(['name' => 'John Doe', ...]);
	now := time.Now()
	user := User{
		ID:        1,
		Name:      "John Doe",
		Email:     "john@example.com",
		Password:  "secret123", // Won't appear in JSON due to `json:"-"`
		Age:       30,
		IsActive:  true,
		CreatedAt: now,
		UpdatedAt: &now,
	}

	// Method 1: Manual marshaling
	// PHP: $json = json_encode($user, JSON_PRETTY_PRINT);
	jsonBytes, err := json.MarshalIndent(user, "", "  ")
	if err != nil {
		http.Error(w, "Failed to encode JSON", http.StatusInternalServerError)
		return
	}

	// Set content type and write response
	// Laravel: return response()->json($user);
	w.Header().Set("Content-Type", "application/json")
	w.Write(jsonBytes)
}

func arrayEncodingHandler(w http.ResponseWriter, r *http.Request) {
	// Array of users
	// PHP: $users = User::all()->toArray();
	users := []User{
		{ID: 1, Name: "John Doe", Email: "john@example.com", Age: 30, IsActive: true, CreatedAt: time.Now()},
		{ID: 2, Name: "Jane Smith", Email: "jane@example.com", Age: 25, IsActive: true, CreatedAt: time.Now()},
		{ID: 3, Name: "Bob Johnson", Email: "bob@example.com", Age: 35, IsActive: false, CreatedAt: time.Now()},
	}

	// Method 2: Using json.Encoder (streaming, more efficient)
	// PHP: echo json_encode($users);
	// Laravel: return response()->json($users);
	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(users)
}

func nestedEncodingHandler(w http.ResponseWriter, r *http.Request) {
	// Complex nested structure
	// PHP: Nested arrays and objects
	discount := 10.0
	product := Product{
		ID:      123,
		Name:    "Super Widget",
		Price:   99.99,
		InStock: true,
		Tags:    []string{"electronics", "gadget", "popular"},
		Metadata: map[string]interface{}{
			"brand":      "ACME",
			"weight":     "1.5kg",
			"dimensions": map[string]float64{"length": 10, "width": 5, "height": 3},
			"features":   []string{"wireless", "waterproof", "durable"},
		},
		DiscountPct: &discount,
		Variants: []ProductVariant{
			{SKU: "SW-RED-S", Color: "red", Size: "S", Price: 89.99, Quantity: 10},
			{SKU: "SW-BLUE-M", Color: "blue", Size: "M", Price: 94.99, Quantity: 5},
			{SKU: "SW-GREEN-L", Color: "green", Size: "L", Price: 99.99, Quantity: 8},
		},
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(product)
}

func standardResponseHandler(w http.ResponseWriter, r *http.Request) {
	// Standard API response format
	// Laravel: return response()->json(['success' => true, 'data' => $data]);
	response := APIResponse{
		Success: true,
		Data: map[string]interface{}{
			"users_count": 42,
			"new_today":   5,
		},
		Message:   "Data retrieved successfully",
		Timestamp: time.Now().Unix(),
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(response)
}

func paginatedResponseHandler(w http.ResponseWriter, r *http.Request) {
	// Paginated response
	// Laravel: User::paginate(15)
	users := []User{
		{ID: 1, Name: "User 1", Email: "user1@example.com", Age: 25, IsActive: true, CreatedAt: time.Now()},
		{ID: 2, Name: "User 2", Email: "user2@example.com", Age: 30, IsActive: true, CreatedAt: time.Now()},
		{ID: 3, Name: "User 3", Email: "user3@example.com", Age: 35, IsActive: true, CreatedAt: time.Now()},
	}

	page := 1
	perPage := 15
	total := 42
	totalPages := (total + perPage - 1) / perPage

	response := PaginatedResponse{
		Data:       users,
		Page:       page,
		PerPage:    perPage,
		Total:      total,
		TotalPages: totalPages,
		HasMore:    page < totalPages,
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(response)
}

func decodingHandler(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	// Method 1: Decode into struct
	// PHP: $data = json_decode(file_get_contents('php://input'), true);
	// Laravel: $data = $request->json()->all();
	var user User

	// Using json.Decoder (streaming)
	if err := json.NewDecoder(r.Body).Decode(&user); err != nil {
		// Laravel: Validation error or JSON parse error
		http.Error(w, fmt.Sprintf("Invalid JSON: %v", err), http.StatusBadRequest)
		return
	}
	defer r.Body.Close()

	// Validate required fields
	// Laravel: $request->validate(['name' => 'required', ...])
	var errors []string
	if user.Name == "" {
		errors = append(errors, "name is required")
	}
	if user.Email == "" {
		errors = append(errors, "email is required")
	}

	if len(errors) > 0 {
		w.Header().Set("Content-Type", "application/json")
		w.WriteHeader(http.StatusBadRequest)
		json.NewEncoder(w).Encode(map[string]interface{}{
			"success": false,
			"errors":  errors,
		})
		return
	}

	// Success response
	response := APIResponse{
		Success:   true,
		Data:      user,
		Message:   "User created successfully",
		Timestamp: time.Now().Unix(),
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(response)
}

func dynamicJSONHandler(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	// Decode into map for dynamic JSON
	// PHP: $data = json_decode($json, true); // Associative array
	// This is useful when structure is not known in advance
	var data map[string]interface{}

	if err := json.NewDecoder(r.Body).Decode(&data); err != nil {
		http.Error(w, "Invalid JSON", http.StatusBadRequest)
		return
	}
	defer r.Body.Close()

	// Access dynamic fields
	// PHP: $data['field_name']
	response := map[string]interface{}{
		"received":   data,
		"field_count": len(data),
		"fields":     getKeys(data),
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(response)
}

func errorResponseHandler(w http.ResponseWriter, r *http.Request) {
	// Demonstrate error response
	// Laravel: abort(404, 'User not found');
	response := APIResponse{
		Success:   false,
		Error:     "not_found",
		Message:   "The requested user was not found",
		Timestamp: time.Now().Unix(),
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusNotFound)
	json.NewEncoder(w).Encode(response)
}

func prettyPrintHandler(w http.ResponseWriter, r *http.Request) {
	// Pretty print JSON (useful for debugging)
	// PHP: json_encode($data, JSON_PRETTY_PRINT);
	user := User{
		ID:        1,
		Name:      "John Doe",
		Email:     "john@example.com",
		Age:       30,
		IsActive:  true,
		CreatedAt: time.Now(),
	}

	// Check if pretty print requested
	pretty := r.URL.Query().Get("pretty") == "true"

	w.Header().Set("Content-Type", "application/json")

	if pretty {
		encoder := json.NewEncoder(w)
		encoder.SetIndent("", "  ")
		encoder.Encode(user)
	} else {
		json.NewEncoder(w).Encode(user)
	}
}

// Helper functions

func getKeys(m map[string]interface{}) []string {
	keys := make([]string, 0, len(m))
	for k := range m {
		keys = append(keys, k)
	}
	return keys
}

func main() {
	// Register handlers
	http.HandleFunc("/basic", basicEncodingHandler)
	http.HandleFunc("/array", arrayEncodingHandler)
	http.HandleFunc("/nested", nestedEncodingHandler)
	http.HandleFunc("/response", standardResponseHandler)
	http.HandleFunc("/paginated", paginatedResponseHandler)
	http.HandleFunc("/decode", decodingHandler)
	http.HandleFunc("/dynamic", dynamicJSONHandler)
	http.HandleFunc("/error", errorResponseHandler)
	http.HandleFunc("/pretty", prettyPrintHandler)

	// Home page with examples
	http.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
		html := `
<!DOCTYPE html>
<html>
<head>
    <title>JSON Encoding/Decoding Examples</title>
    <style>
        body { font-family: Arial; max-width: 1000px; margin: 50px auto; }
        .endpoint { background: #f4f4f4; padding: 15px; margin: 10px 0; border-left: 4px solid #4CAF50; }
        code { background: #e0e0e0; padding: 2px 5px; font-family: monospace; }
        h3 { margin-top: 0; color: #333; }
    </style>
</head>
<body>
    <h1>JSON Encoding and Decoding in Go</h1>

    <div class="endpoint">
        <h3>Basic Encoding</h3>
        <p><a href="/basic">GET /basic</a></p>
        <p>Simple user object with struct tags</p>
    </div>

    <div class="endpoint">
        <h3>Array Encoding</h3>
        <p><a href="/array">GET /array</a></p>
        <p>Array of users</p>
    </div>

    <div class="endpoint">
        <h3>Nested Structures</h3>
        <p><a href="/nested">GET /nested</a></p>
        <p>Complex nested objects with arrays and maps</p>
    </div>

    <div class="endpoint">
        <h3>Standard Response</h3>
        <p><a href="/response">GET /response</a></p>
        <p>Standard API response format</p>
    </div>

    <div class="endpoint">
        <h3>Paginated Response</h3>
        <p><a href="/paginated">GET /paginated</a></p>
        <p>Paginated data response</p>
    </div>

    <div class="endpoint">
        <h3>JSON Decoding</h3>
        <p>POST /decode</p>
        <p>Try: <code>curl -X POST http://localhost:8080/decode -H "Content-Type: application/json" -d '{"name":"John","email":"john@example.com","age":30}'</code></p>
    </div>

    <div class="endpoint">
        <h3>Dynamic JSON</h3>
        <p>POST /dynamic</p>
        <p>Try: <code>curl -X POST http://localhost:8080/dynamic -H "Content-Type: application/json" -d '{"any":"field","works":"here"}'</code></p>
    </div>

    <div class="endpoint">
        <h3>Error Response</h3>
        <p><a href="/error">GET /error</a></p>
        <p>Example error response (404)</p>
    </div>

    <div class="endpoint">
        <h3>Pretty Print</h3>
        <p><a href="/pretty?pretty=true">GET /pretty?pretty=true</a></p>
        <p>Formatted JSON output</p>
    </div>
</body>
</html>
		`
		w.Header().Set("Content-Type", "text/html")
		fmt.Fprint(w, strings.TrimSpace(html))
	})

	log.Println("Server starting on http://localhost:8080")
	log.Println("\nEndpoints:")
	log.Println("  GET  /basic     - Basic JSON encoding")
	log.Println("  GET  /array     - Array encoding")
	log.Println("  GET  /nested    - Nested structures")
	log.Println("  GET  /response  - Standard API response")
	log.Println("  GET  /paginated - Paginated response")
	log.Println("  POST /decode    - JSON decoding")
	log.Println("  POST /dynamic   - Dynamic JSON")
	log.Println("  GET  /error     - Error response")
	log.Println("  GET  /pretty    - Pretty print JSON")

	if err := http.ListenAndServe(":8080", nil); err != nil {
		log.Fatalf("Server failed: %v", err)
	}
}

/*
 * KEY TAKEAWAYS FOR PHP DEVELOPERS:
 *
 * 1. ENCODING
 *    - PHP: json_encode($data)
 *    - Go: json.Marshal(data) or json.NewEncoder(w).Encode(data)
 *
 * 2. DECODING
 *    - PHP: json_decode($json, true)
 *    - Go: json.Unmarshal(data, &struct) or json.Decoder.Decode(&struct)
 *
 * 3. STRUCT TAGS
 *    - PHP: No equivalent, field names match directly
 *    - Go: `json:"field_name"` controls JSON key names
 *
 * 4. HIDDEN FIELDS
 *    - Laravel: $hidden = ['password']
 *    - Go: `json:"-"` tag
 *
 * 5. OPTIONAL FIELDS
 *    - PHP: Just omit from array
 *    - Go: `json:"field,omitempty"` or use pointers
 *
 * 6. CUSTOM SERIALIZATION
 *    - Laravel: toArray() method or JsonSerializable
 *    - Go: MarshalJSON() and UnmarshalJSON() methods
 *
 * 7. TYPE SAFETY
 *    - PHP: Dynamic types, runtime checking
 *    - Go: Static types, compile-time checking
 *
 * 8. PERFORMANCE
 *    - PHP: json_encode/decode are fast but blocking
 *    - Go: Encoder/Decoder can stream, more efficient for large data
 *
 * STRUCT TAGS CHEAT SHEET:
 *
 * `json:"field_name"`          - Use this name in JSON
 * `json:"field,omitempty"`     - Omit if zero value
 * `json:"-"`                   - Never include in JSON
 * `json:"field,string"`        - Encode as string
 *
 * COMMON PATTERNS:
 *
 * 1. API Response:
 *    type Response struct {
 *        Success bool        `json:"success"`
 *        Data    interface{} `json:"data,omitempty"`
 *        Error   string      `json:"error,omitempty"`
 *    }
 *
 * 2. Paginated Response:
 *    type Paginated struct {
 *        Data    interface{} `json:"data"`
 *        Page    int         `json:"page"`
 *        Total   int         `json:"total"`
 *    }
 *
 * 3. Error Response:
 *    type Error struct {
 *        Error   string `json:"error"`
 *        Message string `json:"message"`
 *        Code    string `json:"code,omitempty"`
 *    }
 */
