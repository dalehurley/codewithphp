package main

/*
 * Request and Response Handling in Go - Chapter 16, Example 3
 *
 * PHP COMPARISON - REQUEST/RESPONSE:
 *
 * In PHP, you interact with requests and responses through:
 * - Superglobals: $_GET, $_POST, $_SERVER, $_FILES, $_COOKIE
 * - Functions: header(), http_response_code(), setcookie()
 * - Frameworks: Request objects (Laravel/Symfony)
 *
 * Laravel Request:
 * $request->input('name')
 * $request->query('page')
 * $request->cookie('session')
 * $request->file('upload')
 * $request->ip()
 *
 * In Go, everything goes through the http.Request and http.ResponseWriter:
 * - http.Request: Contains all request data (method, headers, body, cookies)
 * - http.ResponseWriter: Interface for writing response (headers, status, body)
 *
 * This example demonstrates comprehensive request/response handling.
 */

import (
	"bytes"
	"encoding/json"
	"fmt"
	"io"
	"log"
	"net/http"
	"os"
	"path/filepath"
	"strconv"
	"strings"
	"time"
)

// FormData represents parsed form data
// Similar to Laravel's validated() or Symfony's form data
type FormData struct {
	Name    string
	Email   string
	Message string
	Age     int
	Subscribe bool
}

// JSONPayload represents a JSON request body
// In Laravel: $request->json()->all()
type JSONPayload struct {
	Action string                 `json:"action"`
	Data   map[string]interface{} `json:"data"`
}

// ResponseBuilder helps build HTTP responses
// Similar to Laravel's response() helper
type ResponseBuilder struct {
	writer     http.ResponseWriter
	statusCode int
	headers    map[string]string
}

// NewResponse creates a new response builder
func NewResponse(w http.ResponseWriter) *ResponseBuilder {
	return &ResponseBuilder{
		writer:     w,
		statusCode: http.StatusOK,
		headers:    make(map[string]string),
	}
}

// Status sets the HTTP status code
// Laravel: return response()->status(201);
func (rb *ResponseBuilder) Status(code int) *ResponseBuilder {
	rb.statusCode = code
	return rb
}

// Header sets a response header
// Laravel: return response()->header('X-Custom', 'value');
func (rb *ResponseBuilder) Header(key, value string) *ResponseBuilder {
	rb.headers[key] = value
	return rb
}

// JSON sends a JSON response
// Laravel: return response()->json($data);
func (rb *ResponseBuilder) JSON(data interface{}) error {
	rb.Header("Content-Type", "application/json")

	// Write headers
	for key, value := range rb.headers {
		rb.writer.Header().Set(key, value)
	}

	// Write status code
	rb.writer.WriteHeader(rb.statusCode)

	// Encode and write JSON
	return json.NewEncoder(rb.writer).Encode(data)
}

// HTML sends an HTML response
// Laravel: return response()->view('template', $data);
func (rb *ResponseBuilder) HTML(html string) error {
	rb.Header("Content-Type", "text/html; charset=utf-8")

	for key, value := range rb.headers {
		rb.writer.Header().Set(key, value)
	}

	rb.writer.WriteHeader(rb.statusCode)
	_, err := fmt.Fprint(rb.writer, html)
	return err
}

// Text sends a plain text response
// Laravel: return response($text)->header('Content-Type', 'text/plain');
func (rb *ResponseBuilder) Text(text string) error {
	rb.Header("Content-Type", "text/plain; charset=utf-8")

	for key, value := range rb.headers {
		rb.writer.Header().Set(key, value)
	}

	rb.writer.WriteHeader(rb.statusCode)
	_, err := fmt.Fprint(rb.writer, text)
	return err
}

// QueryParamsHandler demonstrates query parameter handling
// Laravel: $request->query('name')
// Symfony: $request->query->get('name')
// PHP: $_GET['name']
func QueryParamsHandler(w http.ResponseWriter, r *http.Request) {
	// Parse query parameters
	// In PHP, this is automatic: $_GET is populated
	// In Go, you explicitly access r.URL.Query()
	query := r.URL.Query()

	// Get single value
	// PHP: $_GET['name'] ?? 'Guest'
	// Laravel: $request->query('name', 'Guest')
	name := query.Get("name")
	if name == "" {
		name = "Guest"
	}

	// Get integer parameter
	// PHP: (int)($_GET['page'] ?? 1)
	// Laravel: $request->query('page', 1)
	page := 1
	if pageStr := query.Get("page"); pageStr != "" {
		if p, err := strconv.Atoi(pageStr); err == nil {
			page = p
		}
	}

	// Get multiple values for same key
	// PHP: $_GET['tags'] (if tags[]=foo&tags[]=bar)
	// Laravel: $request->query('tags', [])
	tags := query["tags"]

	// Get all parameters
	// PHP: $_GET
	// Laravel: $request->query()
	allParams := make(map[string]interface{})
	for key, values := range query {
		if len(values) == 1 {
			allParams[key] = values[0]
		} else {
			allParams[key] = values
		}
	}

	// Build response
	response := map[string]interface{}{
		"name":       name,
		"page":       page,
		"tags":       tags,
		"all_params": allParams,
	}

	NewResponse(w).JSON(response)
}

// FormHandler demonstrates form data handling
// Laravel: $request->input('field')
// Symfony: $request->request->get('field')
// PHP: $_POST['field']
func FormHandler(w http.ResponseWriter, r *http.Request) {
	if r.Method == http.MethodGet {
		// Show form
		html := `
<!DOCTYPE html>
<html>
<head>
    <title>Form Example</title>
    <style>
        body { font-family: Arial; max-width: 600px; margin: 50px auto; }
        input, textarea { width: 100%; padding: 8px; margin: 5px 0; }
        button { background: #4CAF50; color: white; padding: 10px 20px; border: none; }
    </style>
</head>
<body>
    <h1>Form Submission</h1>
    <form method="POST">
        <label>Name:</label>
        <input type="text" name="name" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Age:</label>
        <input type="number" name="age" min="1" max="120">

        <label>Message:</label>
        <textarea name="message" rows="4"></textarea>

        <label>
            <input type="checkbox" name="subscribe" value="yes">
            Subscribe to newsletter
        </label>

        <br><br>
        <button type="submit">Submit</button>
    </form>
</body>
</html>
		`
		NewResponse(w).HTML(html)
		return
	}

	if r.Method == http.MethodPost {
		// Parse form data
		// In PHP, this is automatic: $_POST is populated
		// In Go, you must explicitly parse
		if err := r.ParseForm(); err != nil {
			NewResponse(w).Status(http.StatusBadRequest).JSON(map[string]string{
				"error": "Failed to parse form",
			})
			return
		}

		// Access form values
		// PHP: $_POST['name']
		// Laravel: $request->input('name')
		formData := FormData{
			Name:    r.FormValue("name"),
			Email:   r.FormValue("email"),
			Message: r.FormValue("message"),
			Subscribe: r.FormValue("subscribe") == "yes",
		}

		// Parse integer field
		if ageStr := r.FormValue("age"); ageStr != "" {
			if age, err := strconv.Atoi(ageStr); err == nil {
				formData.Age = age
			}
		}

		// Validate
		// Laravel: $request->validate([...])
		errors := make([]string, 0)
		if formData.Name == "" {
			errors = append(errors, "Name is required")
		}
		if formData.Email == "" {
			errors = append(errors, "Email is required")
		}
		if formData.Age < 1 || formData.Age > 120 {
			errors = append(errors, "Age must be between 1 and 120")
		}

		if len(errors) > 0 {
			NewResponse(w).Status(http.StatusBadRequest).JSON(map[string]interface{}{
				"errors": errors,
			})
			return
		}

		// Success response
		NewResponse(w).Status(http.StatusCreated).JSON(map[string]interface{}{
			"message": "Form submitted successfully",
			"data":    formData,
		})
		return
	}

	http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
}

// JSONHandler demonstrates JSON request/response handling
// Laravel: $request->json() and response()->json()
// PHP: json_decode(file_get_contents('php://input'))
func JSONHandler(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		NewResponse(w).Status(http.StatusMethodNotAllowed).JSON(map[string]string{
			"error": "Only POST method allowed",
		})
		return
	}

	// Check Content-Type header
	// Laravel does this automatically
	contentType := r.Header.Get("Content-Type")
	if !strings.Contains(contentType, "application/json") {
		NewResponse(w).Status(http.StatusBadRequest).JSON(map[string]string{
			"error": "Content-Type must be application/json",
		})
		return
	}

	// Read request body
	// PHP: file_get_contents('php://input')
	// Laravel: $request->getContent()
	body, err := io.ReadAll(r.Body)
	if err != nil {
		NewResponse(w).Status(http.StatusBadRequest).JSON(map[string]string{
			"error": "Failed to read request body",
		})
		return
	}
	defer r.Body.Close()

	// Parse JSON
	// PHP: json_decode($json, true)
	// Laravel: $request->json()->all()
	var payload JSONPayload
	if err := json.Unmarshal(body, &payload); err != nil {
		NewResponse(w).Status(http.StatusBadRequest).JSON(map[string]string{
			"error": "Invalid JSON",
		})
		return
	}

	// Process the payload
	response := map[string]interface{}{
		"received_action": payload.Action,
		"received_data":   payload.Data,
		"processed_at":    time.Now().Unix(),
	}

	// Send JSON response
	// Laravel: return response()->json($response);
	NewResponse(w).Status(http.StatusOK).JSON(response)
}

// HeadersHandler demonstrates working with HTTP headers
// Laravel: $request->header('X-Custom')
// PHP: $_SERVER['HTTP_X_CUSTOM'] or getallheaders()
func HeadersHandler(w http.ResponseWriter, r *http.Request) {
	// Read request headers
	// PHP: getallheaders() or $_SERVER
	// Laravel: $request->headers->all()

	headers := make(map[string]interface{})

	// Get specific header
	// PHP: $_SERVER['HTTP_USER_AGENT']
	// Laravel: $request->header('User-Agent')
	userAgent := r.Header.Get("User-Agent")
	headers["user_agent"] = userAgent

	// Get custom header
	// PHP: $_SERVER['HTTP_X_API_KEY']
	// Laravel: $request->header('X-API-Key')
	apiKey := r.Header.Get("X-API-Key")
	headers["api_key"] = apiKey

	// Get all headers
	headers["all"] = r.Header

	// Set response headers
	// PHP: header('X-Powered-By: Go')
	// Laravel: return response()->header('X-Powered-By', 'Go')
	resp := NewResponse(w).
		Header("X-Powered-By", "Go").
		Header("X-Request-ID", generateRequestID()).
		Header("X-Response-Time", time.Now().Format(time.RFC3339))

	resp.JSON(map[string]interface{}{
		"message": "Headers example",
		"request_headers": headers,
	})
}

// CookieHandler demonstrates cookie handling
// Laravel: $request->cookie('name') and cookie('name', 'value')
// PHP: $_COOKIE['name'] and setcookie()
func CookieHandler(w http.ResponseWriter, r *http.Request) {
	if r.Method == http.MethodGet {
		// Read cookies
		// PHP: $_COOKIE['user']
		// Laravel: $request->cookie('user')
		userCookie, err := r.Cookie("user")
		userName := "Guest"
		if err == nil {
			userName = userCookie.Value
		}

		// Get visit count
		visitCount := 1
		if countCookie, err := r.Cookie("visits"); err == nil {
			if count, err := strconv.Atoi(countCookie.Value); err == nil {
				visitCount = count + 1
			}
		}

		// Set/update cookies
		// PHP: setcookie('user', 'John', time() + 3600)
		// Laravel: Cookie::make('user', 'John', 60)
		http.SetCookie(w, &http.Cookie{
			Name:     "user",
			Value:    userName,
			Path:     "/",
			MaxAge:   3600, // 1 hour
			HttpOnly: true,  // Prevent JavaScript access
			Secure:   false, // Set to true in production with HTTPS
			SameSite: http.SameSiteLaxMode,
		})

		http.SetCookie(w, &http.Cookie{
			Name:     "visits",
			Value:    strconv.Itoa(visitCount),
			Path:     "/",
			MaxAge:   86400, // 24 hours
			HttpOnly: true,
		})

		NewResponse(w).JSON(map[string]interface{}{
			"user":   userName,
			"visits": visitCount,
			"message": "Cookies have been set",
		})
		return
	}

	if r.Method == http.MethodPost {
		// Set new user cookie from request
		var data struct {
			UserName string `json:"username"`
		}

		if err := json.NewDecoder(r.Body).Decode(&data); err != nil {
			NewResponse(w).Status(http.StatusBadRequest).JSON(map[string]string{
				"error": "Invalid JSON",
			})
			return
		}

		http.SetCookie(w, &http.Cookie{
			Name:     "user",
			Value:    data.UserName,
			Path:     "/",
			MaxAge:   3600,
			HttpOnly: true,
		})

		NewResponse(w).JSON(map[string]string{
			"message": "User cookie updated",
		})
		return
	}

	if r.Method == http.MethodDelete {
		// Delete cookie by setting MaxAge to -1
		// PHP: setcookie('user', '', time() - 3600)
		// Laravel: Cookie::forget('user')
		http.SetCookie(w, &http.Cookie{
			Name:   "user",
			Value:  "",
			Path:   "/",
			MaxAge: -1,
		})

		NewResponse(w).JSON(map[string]string{
			"message": "User cookie deleted",
		})
		return
	}

	http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
}

// FileUploadHandler demonstrates file upload handling
// Laravel: $request->file('upload')
// PHP: $_FILES['upload']
func FileUploadHandler(w http.ResponseWriter, r *http.Request) {
	if r.Method == http.MethodGet {
		html := `
<!DOCTYPE html>
<html>
<head>
    <title>File Upload</title>
    <style>
        body { font-family: Arial; max-width: 600px; margin: 50px auto; }
        button { background: #4CAF50; color: white; padding: 10px 20px; border: none; }
    </style>
</head>
<body>
    <h1>File Upload</h1>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="upload" required>
        <br><br>
        <button type="submit">Upload</button>
    </form>
</body>
</html>
		`
		NewResponse(w).HTML(html)
		return
	}

	if r.Method == http.MethodPost {
		// Parse multipart form
		// PHP: This is automatic, files go to $_FILES
		// Laravel: This is handled by the framework
		// In Go, you must explicitly parse with a size limit
		maxSize := int64(10 << 20) // 10 MB
		if err := r.ParseMultipartForm(maxSize); err != nil {
			NewResponse(w).Status(http.StatusBadRequest).JSON(map[string]string{
				"error": "Failed to parse multipart form",
			})
			return
		}

		// Get uploaded file
		// PHP: $_FILES['upload']
		// Laravel: $request->file('upload')
		file, header, err := r.FormFile("upload")
		if err != nil {
			NewResponse(w).Status(http.StatusBadRequest).JSON(map[string]string{
				"error": "No file uploaded",
			})
			return
		}
		defer file.Close()

		// File information
		// PHP: $_FILES['upload']['name'], $_FILES['upload']['size']
		// Laravel: $file->getClientOriginalName(), $file->getSize()
		fileInfo := map[string]interface{}{
			"name":    header.Filename,
			"size":    header.Size,
			"type":    header.Header.Get("Content-Type"),
		}

		// Read file contents (for demonstration)
		// In production, you'd save to disk or cloud storage
		// Laravel: $file->store('uploads') or Storage::put()
		var buf bytes.Buffer
		if _, err := io.Copy(&buf, file); err != nil {
			NewResponse(w).Status(http.StatusInternalServerError).JSON(map[string]string{
				"error": "Failed to read file",
			})
			return
		}

		// Save file (example)
		// Laravel: $request->file('upload')->storeAs('uploads', $filename)
		uploadDir := "./uploads"
		os.MkdirAll(uploadDir, 0755)

		destPath := filepath.Join(uploadDir, header.Filename)
		destFile, err := os.Create(destPath)
		if err != nil {
			NewResponse(w).Status(http.StatusInternalServerError).JSON(map[string]string{
				"error": "Failed to save file",
			})
			return
		}
		defer destFile.Close()

		if _, err := destFile.Write(buf.Bytes()); err != nil {
			NewResponse(w).Status(http.StatusInternalServerError).JSON(map[string]string{
				"error": "Failed to write file",
			})
			return
		}

		NewResponse(w).Status(http.StatusCreated).JSON(map[string]interface{}{
			"message": "File uploaded successfully",
			"file":    fileInfo,
			"saved_to": destPath,
		})
		return
	}

	http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
}

// generateRequestID generates a unique request ID
func generateRequestID() string {
	return fmt.Sprintf("%d-%d", time.Now().UnixNano(), os.Getpid())
}

// MultipartFormHandler demonstrates complex multipart form handling
func MultipartFormHandler(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	// Parse multipart form with 10MB limit
	if err := r.ParseMultipartForm(10 << 20); err != nil {
		NewResponse(w).Status(http.StatusBadRequest).JSON(map[string]string{
			"error": "Failed to parse form",
		})
		return
	}

	// Get regular form fields
	// PHP: $_POST['field']
	name := r.FormValue("name")
	email := r.FormValue("email")

	// Get uploaded files
	// PHP: $_FILES['files']
	files := r.MultipartForm.File["files"]

	uploadedFiles := make([]map[string]interface{}, 0)
	for _, fileHeader := range files {
		uploadedFiles = append(uploadedFiles, map[string]interface{}{
			"name": fileHeader.Filename,
			"size": fileHeader.Size,
			"type": fileHeader.Header.Get("Content-Type"),
		})
	}

	NewResponse(w).JSON(map[string]interface{}{
		"name":  name,
		"email": email,
		"files": uploadedFiles,
		"count": len(uploadedFiles),
	})
}

func main() {
	// Register handlers
	http.HandleFunc("/query", QueryParamsHandler)
	http.HandleFunc("/form", FormHandler)
	http.HandleFunc("/json", JSONHandler)
	http.HandleFunc("/headers", HeadersHandler)
	http.HandleFunc("/cookies", CookieHandler)
	http.HandleFunc("/upload", FileUploadHandler)
	http.HandleFunc("/multipart", MultipartFormHandler)

	// Home page
	http.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
		html := `
<!DOCTYPE html>
<html>
<head>
    <title>Request/Response Examples</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 50px auto; }
        .endpoint { background: #f4f4f4; padding: 10px; margin: 10px 0; }
        code { background: #e0e0e0; padding: 2px 5px; }
    </style>
</head>
<body>
    <h1>Request/Response Handling Examples</h1>

    <div class="endpoint">
        <h3>Query Parameters</h3>
        <p><a href="/query?name=John&page=2&tags=go&tags=php">GET /query?name=John&page=2&tags=go&tags=php</a></p>
        <p>Try: <code>curl "http://localhost:8080/query?name=John&page=2&tags=go&tags=php"</code></p>
    </div>

    <div class="endpoint">
        <h3>Form Handling</h3>
        <p><a href="/form">GET /form</a></p>
    </div>

    <div class="endpoint">
        <h3>JSON API</h3>
        <p>POST /json</p>
        <p>Try: <code>curl -X POST http://localhost:8080/json -H "Content-Type: application/json" -d '{"action":"test","data":{"key":"value"}}'</code></p>
    </div>

    <div class="endpoint">
        <h3>Headers</h3>
        <p><a href="/headers">GET /headers</a></p>
        <p>Try: <code>curl http://localhost:8080/headers -H "X-API-Key: secret123"</code></p>
    </div>

    <div class="endpoint">
        <h3>Cookies</h3>
        <p><a href="/cookies">GET /cookies</a></p>
        <p>Try: <code>curl -X POST http://localhost:8080/cookies -H "Content-Type: application/json" -d '{"username":"John"}'</code></p>
    </div>

    <div class="endpoint">
        <h3>File Upload</h3>
        <p><a href="/upload">GET /upload</a></p>
        <p>Try: <code>curl -X POST http://localhost:8080/upload -F "upload=@file.txt"</code></p>
    </div>
</body>
</html>
		`
		NewResponse(w).HTML(html)
	})

	// Start server
	log.Println("Server starting on http://localhost:8080")
	log.Println("\nEndpoints:")
	log.Println("  GET  /           - Home page with all examples")
	log.Println("  GET  /query      - Query parameters demo")
	log.Println("  GET  /form       - Form handling demo")
	log.Println("  POST /json       - JSON API demo")
	log.Println("  GET  /headers    - Headers demo")
	log.Println("  GET  /cookies    - Cookie handling demo")
	log.Println("  GET  /upload     - File upload demo")

	if err := http.ListenAndServe(":8080", nil); err != nil {
		log.Fatalf("Server failed: %v", err)
	}
}

/*
 * KEY TAKEAWAYS FOR PHP DEVELOPERS:
 *
 * 1. REQUEST DATA ACCESS
 *    - PHP: Superglobals ($_GET, $_POST, $_FILES, $_COOKIE)
 *    - Go: Methods on *http.Request
 *
 * 2. AUTOMATIC VS EXPLICIT
 *    - PHP: Request data is automatically parsed
 *    - Go: You must explicitly parse forms and multipart data
 *
 * 3. RESPONSE BUILDING
 *    - PHP: echo, header(), http_response_code()
 *    - Go: Write to http.ResponseWriter interface
 *
 * 4. HEADERS
 *    - PHP: header('X-Custom: value') before output
 *    - Go: w.Header().Set() before WriteHeader()
 *
 * 5. COOKIES
 *    - PHP: setcookie() function
 *    - Go: http.SetCookie() with Cookie struct
 *
 * 6. FILE UPLOADS
 *    - PHP: $_FILES array, automatically parsed
 *    - Go: ParseMultipartForm(), FormFile() methods
 *
 * 7. JSON
 *    - PHP: json_encode(), json_decode()
 *    - Go: json.Marshal(), json.Unmarshal()
 *
 * 8. TYPE SAFETY
 *    - PHP: Loose typing, automatic conversion
 *    - Go: Strong typing, explicit conversion needed
 *
 * FRAMEWORK COMPARISON:
 *
 * Laravel Request:               Go Equivalent:
 * $request->input('key')    ->   r.FormValue("key")
 * $request->query('key')    ->   r.URL.Query().Get("key")
 * $request->cookie('key')   ->   r.Cookie("key")
 * $request->file('key')     ->   r.FormFile("key")
 * $request->header('key')   ->   r.Header.Get("key")
 * $request->ip()            ->   r.RemoteAddr
 * $request->method()        ->   r.Method
 *
 * The main difference: PHP frameworks abstract away the details,
 * while Go gives you direct control over request/response handling.
 */
