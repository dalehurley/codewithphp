package main

/*
 * Request Validation in Go - Chapter 18, Example 3
 *
 * PHP VALIDATION COMPARISON:
 *
 * Laravel Validation:
 * $request->validate([
 *     'name' => 'required|string|max:255',
 *     'email' => 'required|email|unique:users',
 *     'age' => 'required|integer|min:18|max:120',
 *     'password' => 'required|min:8|confirmed',
 * ]);
 *
 * Symfony Validation:
 * use Symfony\Component\Validator\Constraints as Assert;
 *
 * class User {
 *     #[Assert\NotBlank]
 *     #[Assert\Length(max: 255)]
 *     private string $name;
 *
 *     #[Assert\NotBlank]
 *     #[Assert\Email]
 *     private string $email;
 * }
 *
 * In Go, validation can be done:
 * 1. Manually (full control, verbose)
 * 2. Using struct tags with libraries like:
 *    - go-playground/validator (most popular)
 *    - ozzo-validation
 *    - govalidator
 *
 * This example demonstrates:
 * - Manual validation (no dependencies)
 * - Custom validation rules
 * - Validation error formatting
 * - Field-level and cross-field validation
 * - Similar patterns to Laravel's validation
 *
 * For production, consider using go-playground/validator for struct tag validation
 * similar to Laravel's validation rules.
 */

import (
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"net/mail"
	"regexp"
	"strings"
	"time"
	"unicode"
)

// ValidationError represents a validation error for a specific field
// Laravel: validation errors are returned as ['field' => ['error message']]
type ValidationError struct {
	Field   string `json:"field"`
	Message string `json:"message"`
	Rule    string `json:"rule"`
}

// ValidationErrors is a collection of validation errors
type ValidationErrors []ValidationError

// ToMap converts validation errors to a map
// Laravel format: ['field' => 'error message']
func (ve ValidationErrors) ToMap() map[string]string {
	result := make(map[string]string)
	for _, err := range ve {
		result[err.Field] = err.Message
	}
	return result
}

// Validator provides validation functionality
type Validator struct {
	errors ValidationErrors
}

// NewValidator creates a new validator
func NewValidator() *Validator {
	return &Validator{
		errors: make(ValidationErrors, 0),
	}
}

// AddError adds a validation error
func (v *Validator) AddError(field, rule, message string) {
	v.errors = append(v.errors, ValidationError{
		Field:   field,
		Message: message,
		Rule:    rule,
	})
}

// IsValid returns true if no errors
func (v *Validator) IsValid() bool {
	return len(v.errors) == 0
}

// Errors returns all validation errors
func (v *Validator) Errors() ValidationErrors {
	return v.errors
}

// Validation rules (similar to Laravel's validation rules)

// Required checks if value is not empty
// Laravel: 'required'
func (v *Validator) Required(field, value string) {
	if strings.TrimSpace(value) == "" {
		v.AddError(field, "required", fmt.Sprintf("The %s field is required", field))
	}
}

// Email validates email format
// Laravel: 'email'
func (v *Validator) Email(field, value string) {
	if value == "" {
		return // Skip if empty (use Required for required check)
	}

	_, err := mail.ParseAddress(value)
	if err != nil {
		v.AddError(field, "email", fmt.Sprintf("The %s must be a valid email address", field))
	}
}

// MinLength validates minimum string length
// Laravel: 'min:8'
func (v *Validator) MinLength(field, value string, min int) {
	if value == "" {
		return
	}

	if len(value) < min {
		v.AddError(field, "min", fmt.Sprintf("The %s must be at least %d characters", field, min))
	}
}

// MaxLength validates maximum string length
// Laravel: 'max:255'
func (v *Validator) MaxLength(field, value string, max int) {
	if value == "" {
		return
	}

	if len(value) > max {
		v.AddError(field, "max", fmt.Sprintf("The %s must not exceed %d characters", field, max))
	}
}

// Min validates minimum numeric value
// Laravel: 'min:18'
func (v *Validator) Min(field string, value, min int) {
	if value < min {
		v.AddError(field, "min", fmt.Sprintf("The %s must be at least %d", field, min))
	}
}

// Max validates maximum numeric value
// Laravel: 'max:120'
func (v *Validator) Max(field string, value, max int) {
	if value > max {
		v.AddError(field, "max", fmt.Sprintf("The %s must not exceed %d", field, max))
	}
}

// Between validates value is between min and max
// Laravel: 'between:18,120'
func (v *Validator) Between(field string, value, min, max int) {
	if value < min || value > max {
		v.AddError(field, "between", fmt.Sprintf("The %s must be between %d and %d", field, min, max))
	}
}

// In validates value is in allowed list
// Laravel: 'in:draft,published,archived'
func (v *Validator) In(field, value string, allowed []string) {
	if value == "" {
		return
	}

	for _, a := range allowed {
		if value == a {
			return
		}
	}

	v.AddError(field, "in", fmt.Sprintf("The %s must be one of: %s", field, strings.Join(allowed, ", ")))
}

// Alpha validates value contains only alphabetic characters
// Laravel: 'alpha'
func (v *Validator) Alpha(field, value string) {
	if value == "" {
		return
	}

	for _, r := range value {
		if !unicode.IsLetter(r) && r != ' ' {
			v.AddError(field, "alpha", fmt.Sprintf("The %s must contain only letters", field))
			return
		}
	}
}

// AlphaNumeric validates value contains only alphanumeric characters
// Laravel: 'alpha_num'
func (v *Validator) AlphaNumeric(field, value string) {
	if value == "" {
		return
	}

	for _, r := range value {
		if !unicode.IsLetter(r) && !unicode.IsDigit(r) {
			v.AddError(field, "alpha_num", fmt.Sprintf("The %s must contain only letters and numbers", field))
			return
		}
	}
}

// Regex validates value matches pattern
// Laravel: 'regex:/pattern/'
func (v *Validator) Regex(field, value, pattern, message string) {
	if value == "" {
		return
	}

	matched, err := regexp.MatchString(pattern, value)
	if err != nil || !matched {
		if message == "" {
			message = fmt.Sprintf("The %s format is invalid", field)
		}
		v.AddError(field, "regex", message)
	}
}

// URL validates URL format
// Laravel: 'url'
func (v *Validator) URL(field, value string) {
	if value == "" {
		return
	}

	pattern := `^https?://[^\s/$.?#].[^\s]*$`
	matched, _ := regexp.MatchString(pattern, value)
	if !matched {
		v.AddError(field, "url", fmt.Sprintf("The %s must be a valid URL", field))
	}
}

// Date validates date format
// Laravel: 'date' or 'date_format:Y-m-d'
func (v *Validator) Date(field, value, format string) {
	if value == "" {
		return
	}

	_, err := time.Parse(format, value)
	if err != nil {
		v.AddError(field, "date", fmt.Sprintf("The %s must be a valid date", field))
	}
}

// After validates date is after another date
// Laravel: 'after:tomorrow' or 'after:start_date'
func (v *Validator) After(field, value string, after time.Time, format string) {
	if value == "" {
		return
	}

	date, err := time.Parse(format, value)
	if err != nil {
		v.AddError(field, "after", fmt.Sprintf("The %s must be a valid date", field))
		return
	}

	if !date.After(after) {
		v.AddError(field, "after", fmt.Sprintf("The %s must be after %s", field, after.Format(format)))
	}
}

// Before validates date is before another date
// Laravel: 'before:end_date'
func (v *Validator) Before(field, value string, before time.Time, format string) {
	if value == "" {
		return
	}

	date, err := time.Parse(format, value)
	if err != nil {
		v.AddError(field, "before", fmt.Sprintf("The %s must be a valid date", field))
		return
	}

	if !date.Before(before) {
		v.AddError(field, "before", fmt.Sprintf("The %s must be before %s", field, before.Format(format)))
	}
}

// Confirmed validates field matches confirmation field
// Laravel: 'confirmed' (checks password vs password_confirmation)
func (v *Validator) Confirmed(field, value, confirmation string) {
	if value != confirmation {
		v.AddError(field, "confirmed", fmt.Sprintf("The %s confirmation does not match", field))
	}
}

// Request types for validation examples

// RegisterRequest represents user registration
// Laravel: FormRequest class with validation rules
type RegisterRequest struct {
	Username        string `json:"username"`
	Email           string `json:"email"`
	Password        string `json:"password"`
	PasswordConfirm string `json:"password_confirmation"`
	Age             int    `json:"age"`
	Website         string `json:"website,omitempty"`
	Bio             string `json:"bio,omitempty"`
}

// Validate validates the registration request
// Laravel: public function rules() { return [...]; }
func (r RegisterRequest) Validate() *Validator {
	v := NewValidator()

	// Username validation
	// Laravel: 'username' => 'required|alpha_num|min:3|max:20'
	v.Required("username", r.Username)
	v.AlphaNumeric("username", r.Username)
	v.MinLength("username", r.Username, 3)
	v.MaxLength("username", r.Username, 20)

	// Email validation
	// Laravel: 'email' => 'required|email'
	v.Required("email", r.Email)
	v.Email("email", r.Email)

	// Password validation
	// Laravel: 'password' => 'required|min:8|confirmed'
	v.Required("password", r.Password)
	v.MinLength("password", r.Password, 8)
	v.Confirmed("password", r.Password, r.PasswordConfirm)

	// Age validation
	// Laravel: 'age' => 'required|integer|between:18,120'
	if r.Age == 0 {
		v.AddError("age", "required", "The age field is required")
	} else {
		v.Between("age", r.Age, 18, 120)
	}

	// Optional website validation
	// Laravel: 'website' => 'nullable|url'
	if r.Website != "" {
		v.URL("website", r.Website)
	}

	// Optional bio validation
	// Laravel: 'bio' => 'nullable|max:500'
	if r.Bio != "" {
		v.MaxLength("bio", r.Bio, 500)
	}

	return v
}

// ArticleRequest represents article creation/update
type ArticleRequest struct {
	Title           string   `json:"title"`
	Slug            string   `json:"slug"`
	Content         string   `json:"content"`
	Excerpt         string   `json:"excerpt,omitempty"`
	Status          string   `json:"status"`
	PublishDate     string   `json:"publish_date,omitempty"`
	Tags            []string `json:"tags,omitempty"`
	AllowComments   bool     `json:"allow_comments"`
	FeaturedImage   string   `json:"featured_image,omitempty"`
}

// Validate validates the article request
func (r ArticleRequest) Validate() *Validator {
	v := NewValidator()

	// Title validation
	// Laravel: 'title' => 'required|string|max:255'
	v.Required("title", r.Title)
	v.MaxLength("title", r.Title, 255)

	// Slug validation
	// Laravel: 'slug' => 'required|alpha_dash|unique:articles'
	v.Required("slug", r.Slug)
	slugPattern := `^[a-z0-9]+(?:-[a-z0-9]+)*$`
	v.Regex("slug", r.Slug, slugPattern, "The slug must be lowercase alphanumeric with hyphens")

	// Content validation
	// Laravel: 'content' => 'required|string'
	v.Required("content", r.Content)

	// Excerpt validation
	// Laravel: 'excerpt' => 'nullable|max:500'
	if r.Excerpt != "" {
		v.MaxLength("excerpt", r.Excerpt, 500)
	}

	// Status validation
	// Laravel: 'status' => 'required|in:draft,published,archived'
	v.Required("status", r.Status)
	v.In("status", r.Status, []string{"draft", "published", "archived"})

	// Publish date validation
	// Laravel: 'publish_date' => 'nullable|date|after:today'
	if r.PublishDate != "" {
		dateFormat := "2006-01-02"
		v.Date("publish_date", r.PublishDate, dateFormat)

		// Must be in the future for draft articles
		if r.Status == "draft" {
			today := time.Now().Truncate(24 * time.Hour)
			v.After("publish_date", r.PublishDate, today, dateFormat)
		}
	}

	// Tags validation
	// Laravel: 'tags' => 'array|max:5'
	// Laravel: 'tags.*' => 'string|max:50'
	if len(r.Tags) > 5 {
		v.AddError("tags", "max", "The tags must not have more than 5 items")
	}

	for i, tag := range r.Tags {
		if len(tag) > 50 {
			field := fmt.Sprintf("tags.%d", i)
			v.AddError(field, "max", "Each tag must not exceed 50 characters")
		}
	}

	// Featured image validation
	// Laravel: 'featured_image' => 'nullable|url'
	if r.FeaturedImage != "" {
		v.URL("featured_image", r.FeaturedImage)
	}

	return v
}

// EventRequest represents event creation with cross-field validation
type EventRequest struct {
	Name        string `json:"name"`
	Description string `json:"description"`
	StartDate   string `json:"start_date"`
	EndDate     string `json:"end_date"`
	Location    string `json:"location"`
	MaxAttendees int   `json:"max_attendees"`
}

// Validate validates the event request with cross-field rules
func (r EventRequest) Validate() *Validator {
	v := NewValidator()
	dateFormat := "2006-01-02 15:04"

	// Name validation
	v.Required("name", r.Name)
	v.MaxLength("name", r.Name, 200)

	// Description validation
	v.Required("description", r.Description)
	v.MinLength("description", r.Description, 10)

	// Location validation
	v.Required("location", r.Location)

	// Date validation
	v.Required("start_date", r.StartDate)
	v.Required("end_date", r.EndDate)

	// Parse dates for cross-field validation
	// Laravel: 'end_date' => 'after:start_date'
	startDate, startErr := time.Parse(dateFormat, r.StartDate)
	endDate, endErr := time.Parse(dateFormat, r.EndDate)

	if startErr == nil && endErr == nil {
		// End date must be after start date
		if !endDate.After(startDate) {
			v.AddError("end_date", "after", "The end date must be after the start date")
		}

		// Event must be in the future
		now := time.Now()
		if !startDate.After(now) {
			v.AddError("start_date", "after", "The event must be scheduled in the future")
		}
	}

	// Max attendees validation
	// Laravel: 'max_attendees' => 'required|integer|min:1|max:10000'
	if r.MaxAttendees == 0 {
		v.AddError("max_attendees", "required", "The max attendees field is required")
	} else {
		v.Between("max_attendees", r.MaxAttendees, 1, 10000)
	}

	return v
}

// API Response types

type APIResponse struct {
	Success bool              `json:"success"`
	Data    interface{}       `json:"data,omitempty"`
	Errors  map[string]string `json:"errors,omitempty"`
	Message string            `json:"message,omitempty"`
}

// Handlers

func registerHandler(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	// Decode request
	var req RegisterRequest
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		sendError(w, http.StatusBadRequest, "Invalid JSON", nil)
		return
	}
	defer r.Body.Close()

	// Validate request
	// Laravel: $validated = $request->validated();
	validator := req.Validate()

	if !validator.IsValid() {
		// Return validation errors
		// Laravel: ValidationException returns 422 with errors
		sendError(w, http.StatusUnprocessableEntity, "Validation failed", validator.Errors().ToMap())
		return
	}

	// Process registration (simplified)
	response := APIResponse{
		Success: true,
		Data: map[string]interface{}{
			"username": req.Username,
			"email":    req.Email,
		},
		Message: "Registration successful",
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(response)
}

func createArticleHandler(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	var req ArticleRequest
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		sendError(w, http.StatusBadRequest, "Invalid JSON", nil)
		return
	}
	defer r.Body.Close()

	// Validate
	validator := req.Validate()

	if !validator.IsValid() {
		sendError(w, http.StatusUnprocessableEntity, "Validation failed", validator.Errors().ToMap())
		return
	}

	// Create article (simplified)
	response := APIResponse{
		Success: true,
		Data: map[string]interface{}{
			"title":  req.Title,
			"slug":   req.Slug,
			"status": req.Status,
		},
		Message: "Article created successfully",
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(response)
}

func createEventHandler(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		http.Error(w, "Method not allowed", http.StatusMethodNotAllowed)
		return
	}

	var req EventRequest
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		sendError(w, http.StatusBadRequest, "Invalid JSON", nil)
		return
	}
	defer r.Body.Close()

	// Validate with cross-field rules
	validator := req.Validate()

	if !validator.IsValid() {
		sendError(w, http.StatusUnprocessableEntity, "Validation failed", validator.Errors().ToMap())
		return
	}

	response := APIResponse{
		Success: true,
		Data: map[string]interface{}{
			"name":       req.Name,
			"start_date": req.StartDate,
			"end_date":   req.EndDate,
		},
		Message: "Event created successfully",
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(response)
}

// Helper functions

func sendError(w http.ResponseWriter, status int, message string, errors map[string]string) {
	response := APIResponse{
		Success: false,
		Message: message,
		Errors:  errors,
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(status)
	json.NewEncoder(w).Encode(response)
}

func main() {
	http.HandleFunc("/register", registerHandler)
	http.HandleFunc("/articles", createArticleHandler)
	http.HandleFunc("/events", createEventHandler)

	// Documentation endpoint
	http.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
		docs := map[string]interface{}{
			"title": "Validation Examples API",
			"endpoints": []map[string]interface{}{
				{
					"path":   "/register",
					"method": "POST",
					"description": "User registration with validation",
					"example": map[string]interface{}{
						"username":             "johndoe",
						"email":                "john@example.com",
						"password":             "securepass123",
						"password_confirmation": "securepass123",
						"age":                  25,
						"website":              "https://example.com",
					},
				},
				{
					"path":   "/articles",
					"method": "POST",
					"description": "Create article with validation",
					"example": map[string]interface{}{
						"title":        "My Article",
						"slug":         "my-article",
						"content":      "Article content here...",
						"status":       "draft",
						"publish_date": "2025-12-31",
						"tags":         []string{"go", "programming"},
					},
				},
				{
					"path":   "/events",
					"method": "POST",
					"description": "Create event with cross-field validation",
					"example": map[string]interface{}{
						"name":          "Tech Conference",
						"description":   "Annual technology conference",
						"start_date":    "2025-06-01 09:00",
						"end_date":      "2025-06-03 17:00",
						"location":      "Convention Center",
						"max_attendees": 500,
					},
				},
			},
		}

		w.Header().Set("Content-Type", "application/json")
		encoder := json.NewEncoder(w)
		encoder.SetIndent("", "  ")
		encoder.Encode(docs)
	})

	log.Println("Validation API Server starting on http://localhost:8080")
	log.Println("\nEndpoints:")
	log.Println("  POST /register  - User registration")
	log.Println("  POST /articles  - Create article")
	log.Println("  POST /events    - Create event")
	log.Println("\nExample commands:")
	log.Println(`  curl -X POST http://localhost:8080/register -H "Content-Type: application/json" -d '{"username":"john","email":"john@example.com","password":"pass123","password_confirmation":"pass123","age":25}'`)
	log.Println(`  curl -X POST http://localhost:8080/articles -H "Content-Type: application/json" -d '{"title":"Test","slug":"test","content":"Content","status":"draft"}'`)

	if err := http.ListenAndServe(":8080", nil); err != nil {
		log.Fatalf("Server failed: %v", err)
	}
}

/*
 * KEY TAKEAWAYS FOR PHP DEVELOPERS:
 *
 * 1. VALIDATION APPROACH
 *    - Laravel: Declarative rules in array or FormRequest
 *    - Go: Imperative validation methods (manual or library)
 *    - Both return 422 for validation errors
 *
 * 2. VALIDATION RULES
 *    - Laravel: 'required|email|min:8|max:255'
 *    - Go: validator.Required(), validator.Email(), validator.MinLength()
 *    - Same concepts, different syntax
 *
 * 3. ERROR FORMAT
 *    - Laravel: ['field' => 'error message']
 *    - Go: Map[string]string with same structure
 *    - Compatible with frontend expectations
 *
 * 4. CUSTOM VALIDATION
 *    - Laravel: Custom validation rules or closures
 *    - Go: Add methods to Validator struct
 *    - Both support complex custom rules
 *
 * 5. CROSS-FIELD VALIDATION
 *    - Laravel: 'after:start_date', 'confirmed'
 *    - Go: Manual comparison in Validate() method
 *    - Go gives more control, Laravel is more concise
 *
 * 6. FORMREQUEST PATTERN
 *    - Laravel: FormRequest classes with rules() method
 *    - Go: Request structs with Validate() method
 *    - Same pattern, different implementation
 *
 * 7. VALIDATION LIBRARIES
 *    - For production, consider: github.com/go-playground/validator
 *    - Provides struct tags similar to Laravel:
 *      type User struct {
 *          Email string `validate:"required,email"`
 *          Age   int    `validate:"gte=18,lte=120"`
 *      }
 *
 * LARAVEL VALIDATION RULES -> GO EQUIVALENTS:
 *
 * required         -> validator.Required()
 * email            -> validator.Email()
 * min:8            -> validator.MinLength(..., 8)
 * max:255          -> validator.MaxLength(..., 255)
 * between:18,120   -> validator.Between(..., 18, 120)
 * in:a,b,c         -> validator.In(..., []string{"a","b","c"})
 * alpha            -> validator.Alpha()
 * alpha_num        -> validator.AlphaNumeric()
 * url              -> validator.URL()
 * date             -> validator.Date()
 * after:date       -> validator.After()
 * before:date      -> validator.Before()
 * confirmed        -> validator.Confirmed()
 * regex:/pattern/  -> validator.Regex()
 *
 * PRODUCTION RECOMMENDATION:
 *
 * For production APIs, use go-playground/validator:
 *
 * import "github.com/go-playground/validator/v10"
 *
 * type User struct {
 *     Email    string `json:"email" validate:"required,email"`
 *     Password string `json:"password" validate:"required,min=8"`
 *     Age      int    `json:"age" validate:"required,gte=18,lte=120"`
 * }
 *
 * validate := validator.New()
 * err := validate.Struct(user)
 *
 * This provides Laravel-like validation with struct tags!
 */
