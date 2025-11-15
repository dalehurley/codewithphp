# Chapter 19: Templates & Views

Learn Go's html/template package for server-side rendering. Discover how Go prevents XSS attacks automatically and compare templating to PHP, Blade, and Twig.

## Overview

Go's html/template package provides secure, efficient server-side rendering. Unlike PHP where you mix code and HTML freely, Go templates are sandboxed and automatically escape output to prevent XSS attacks.

## Files in This Chapter

1. `01-template-basics.go` - Template syntax, actions, variables
2. `02-template-functions.go` - Built-in functions, custom functions
3. `03-layouts-partials.go` - Layouts, partials, template composition
4. `04-template-data.go` - Passing data, structs, maps, slices
5. `05-template-security.go` - Auto-escaping, safe HTML, XSS prevention
6. `06-template-caching.go` - Template parsing, caching, performance

## Quick Reference

**PHP (Blade)**:
```php
<!-- resources/views/user.blade.php -->
<h1>{{ $user->name }}</h1>
<p>Email: {{ $user->email }}</p>

@if($user->isAdmin)
    <p>Admin User</p>
@endif

@foreach($posts as $post)
    <h2>{{ $post->title }}</h2>
@endforeach
```

**Go (html/template)**:
```go
// templates/user.html
<h1>{{.Name}}</h1>
<p>Email: {{.Email}}</p>

{{if .IsAdmin}}
    <p>Admin User</p>
{{end}}

{{range .Posts}}
    <h2>{{.Title}}</h2>
{{end}}
```

```go
// Render template
t := template.Must(template.ParseFiles("templates/user.html"))
t.Execute(w, user)
```

## Key Concepts

### 1. Basic Template

```go
package main

import (
    "html/template"
    "net/http"
)

func handler(w http.ResponseWriter, r *http.Request) {
    tmpl := template.Must(template.ParseFiles("template.html"))

    data := map[string]string{
        "Title": "Welcome",
        "Name":  "Alice",
    }

    tmpl.Execute(w, data)
}
```

```html
<!-- template.html -->
<!DOCTYPE html>
<html>
<head>
    <title>{{.Title}}</title>
</head>
<body>
    <h1>Hello, {{.Name}}!</h1>
</body>
</html>
```

### 2. Template Actions

```html
<!-- Variables -->
{{.FieldName}}
{{.Method}}

<!-- Conditionals -->
{{if .Condition}}
    <p>True</p>
{{else}}
    <p>False</p>
{{end}}

<!-- Range (loops) -->
{{range .Items}}
    <li>{{.}}</li>
{{end}}

<!-- With (null checks) -->
{{with .User}}
    <p>{{.Name}}</p>
{{end}}
```

### 3. Template Functions

```go
// Custom functions
funcMap := template.FuncMap{
    "upper": strings.ToUpper,
    "formatDate": func(t time.Time) string {
        return t.Format("2006-01-02")
    },
}

tmpl := template.New("test").Funcs(funcMap)
tmpl.Parse(`<h1>{{.Name | upper}}</h1>`)
```

### 4. Layouts and Partials

```html
<!-- layouts/base.html -->
<!DOCTYPE html>
<html>
<head>
    <title>{{block "title" .}}Default Title{{end}}</title>
</head>
<body>
    {{block "content" .}}{{end}}
</body>
</html>

<!-- pages/home.html -->
{{define "title"}}Home Page{{end}}

{{define "content"}}
    <h1>Welcome Home</h1>
{{end}}
```

```go
// Render with layout
tmpl := template.Must(template.ParseFiles(
    "layouts/base.html",
    "pages/home.html",
))
tmpl.ExecuteTemplate(w, "base.html", data)
```

### 5. Auto-Escaping

```go
// Go automatically escapes HTML
data := map[string]string{
    "HTML": "<script>alert('xss')</script>",
}

// Renders as: &lt;script&gt;alert(&#39;xss&#39;)&lt;/script&gt;
tmpl.Execute(w, data)

// To render raw HTML (use with caution!)
type SafeHTML template.HTML

data := map[string]interface{}{
    "HTML": template.HTML("<strong>Bold</strong>"),
}
```

## Common Patterns

### 1. Template Cache

```go
var templates *template.Template

func init() {
    templates = template.Must(template.ParseGlob("templates/*.html"))
}

func renderTemplate(w http.ResponseWriter, name string, data interface{}) {
    err := templates.ExecuteTemplate(w, name, data)
    if err != nil {
        http.Error(w, err.Error(), http.StatusInternalServerError)
    }
}
```

### 2. Layout Pattern

```go
type TemplateData struct {
    Title   string
    User    *User
    Content interface{}
}

func render(w http.ResponseWriter, page string, data interface{}) {
    tmplData := TemplateData{
        Title:   "My App",
        Content: data,
    }

    templates.ExecuteTemplate(w, "layout.html", tmplData)
}
```

### 3. Form Rendering

```html
<form method="POST" action="/users">
    <input type="text" name="name" value="{{.Name}}">
    <input type="email" name="email" value="{{.Email}}">
    <button type="submit">Submit</button>
</form>

{{if .Errors}}
    <ul>
    {{range .Errors}}
        <li>{{.}}</li>
    {{end}}
    </ul>
{{end}}
```

## Best Practices

- Parse templates once, cache them
- Use layouts for consistent structure
- Never trust user input - let Go escape
- Use `template.HTML` only for trusted content
- Organize templates in directories
- Use custom functions for complex logic
- Implement proper error handling

## Comparison with PHP

| Feature | PHP | Go |
|---------|-----|-----|
| Mixing code/HTML | Yes (native) | No (templated) |
| Auto-escaping | Manual (htmlspecialchars) | Automatic |
| Performance | Interpreted each request | Parsed once, cached |
| Template engine | Blade, Twig (external) | html/template (built-in) |
| XSS protection | Manual | Automatic |

## Next Steps

- Chapter 20: Web Frameworks - Echo, Gin, Fiber
- Chapter 21: Database/SQL Package
- Chapter 35: Files & IO Operations

---

**Key Takeaway**: Go's template package is secure by default with automatic XSS prevention. While less flexible than PHP's inline code, templates are safer and performant when cached properly.
