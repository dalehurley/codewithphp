# Chapter 18: Project Structure - Complete MVC Application

A complete, production-ready MVC application structure for PHP projects.

## 📁 Project Structure

```
18-structure/
├── app/
│   ├── Controllers/
│   │   ├── Controller.php          # Base controller
│   │   ├── HomeController.php      # Home page handler
│   │   ├── PostController.php      # Blog CRUD operations
│   │   └── UserController.php      # User management
│   ├── Models/
│   │   ├── Model.php               # Base model with PDO
│   │   ├── Post.php                # Post model
│   │   └── User.php                # User model
│   ├── views/
│   │   ├── layouts/
│   │   │   └── main.php            # Main layout template
│   │   ├── home/
│   │   │   ├── index.php           # Home page
│   │   │   └── about.php           # About page
│   │   ├── posts/
│   │   │   ├── index.php           # All posts
│   │   │   ├── show.php            # Single post
│   │   │   ├── create.php          # Create form
│   │   │   └── edit.php            # Edit form
│   │   ├── users/
│   │   │   └── show.php            # User profile
│   │   └── 404.php                 # 404 error page
│   └── Router.php                  # URL routing
├── config/
│   └── config.php                  # Application config
├── public/
│   ├── index.php                   # Front controller (entry point)
│   └── .htaccess                   # Apache rewrite rules
└── routes.php                      # Route definitions
```

## 🚀 Quick Start

### 1. Setup Database

```bash
# Create SQLite database
sqlite3 database.sqlite

# Or from PHP:
php -r "touch('database.sqlite');"
```

### 2. Create Tables

```sql
CREATE TABLE posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    content TEXT NOT NULL,
    author TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO posts (title, content, author) VALUES
    ('First Post', 'This is my first blog post!', 'Admin'),
    ('Second Post', 'Another great article.', 'Admin'),
    ('PHP Tips', 'Here are some PHP best practices...', 'Dale');

INSERT INTO users (name, email, password) VALUES
    ('John Doe', 'john@example.com', '$2y$10$...'), -- Use password_hash()
    ('Jane Smith', 'jane@example.com', '$2y$10$...');
```

### 3. Start Server

```bash
# From the public/ directory
cd public
php -S localhost:8000

# Or from project root
php -S localhost:8000 -t public
```

### 4. Visit Application

- Home: http://localhost:8000/
- Posts: http://localhost:8000/posts
- Create Post: http://localhost:8000/posts/create
- About: http://localhost:8000/about

## 🏗️ MVC Architecture

### Model (Data Layer)

**Responsibilities:**

- Database interaction
- Business logic
- Data validation
- Relationships

**Base Model** (`app/Models/Model.php`):

```php
abstract class Model {
    protected PDO $db;
    protected string $table;

    public function all(): array { }
    public function find(int $id): ?array { }
    public function create(array $data): int { }
    public function update(int $id, array $data): bool { }
    public function delete(int $id): bool { }
}
```

**Example Usage:**

```php
$postModel = new Post();
$posts = $postModel->all();
$post = $postModel->find(1);
$postModel->create(['title' => 'New Post', 'content' => '...']);
```

### View (Presentation Layer)

**Responsibilities:**

- Display data
- HTML templates
- User interface
- Form rendering

**Layout System:**

- Main layout: `app/views/layouts/main.php`
- Page views: `app/views/{controller}/{action}.php`
- Shared header/footer/nav

**Example View:**

```php
<!-- app/views/posts/show.php -->
<h1><?= htmlspecialchars($post['title']) ?></h1>
<p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
```

### Controller (Logic Layer)

**Responsibilities:**

- Handle requests
- Coordinate Model & View
- Input validation
- Redirects & responses

**Base Controller** (`app/Controllers/Controller.php`):

```php
abstract class Controller {
    protected function view(string $view, array $data = []): void { }
    protected function redirect(string $url): never { }
    protected function json(mixed $data, int $status = 200): never { }
    protected function input(string $key, mixed $default = null): mixed { }
    protected function validate(array $rules): array { }
}
```

**Example Controller:**

```php
class PostController extends Controller {
    public function show(string $id): void {
        $post = $this->postModel->find((int)$id);
        $this->view('posts/show', ['post' => $post]);
    }
}
```

## 🛣️ Routing

Routes are defined in `routes.php`:

```php
// Simple route
$router->get('/', [new HomeController(), 'index']);

// Route with parameter
$router->get('/posts/{id}', [$postController, 'show']);

// RESTful routes
$router->get('/posts', [$postController, 'index']);        // List
$router->get('/posts/create', [$postController, 'create']); // Create form
$router->post('/posts', [$postController, 'store']);       // Save
$router->get('/posts/{id}', [$postController, 'show']);    // Display
$router->get('/posts/{id}/edit', [$postController, 'edit']); // Edit form
$router->post('/posts/{id}/update', [$postController, 'update']); // Update
$router->post('/posts/{id}/delete', [$postController, 'destroy']); // Delete
```

## 🔧 Configuration

Edit `config/config.php`:

```php
return [
    'app' => [
        'name' => 'My App',
        'env' => 'development', // or 'production'
        'debug' => true,
    ],
    'database' => [
        'driver' => 'sqlite',
        'path' => BASE_PATH . '/database.sqlite',
    ],
];
```

## 💡 Key Features

### 1. Automatic Class Loading

```php
spl_autoload_register(function (string $class) {
    $file = APP_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    require $file;
});
```

### 2. Flash Messages

```php
// Set in controller
$_SESSION['success'] = 'Post created!';
$this->redirect('/posts');

// Displayed once in layout, then cleared
```

### 3. Input Helpers

```php
// Get input from POST or GET
$title = $this->input('title', 'Default');

// Validate
$errors = $this->validate([
    'title' => 'required',
    'email' => 'required'
]);
```

### 4. Template System

```php
// Render view with data
$this->view('posts/show', [
    'title' => 'Post Title',
    'post' => $postData
]);

// Data is automatically extracted as variables
// $title and $post are available in the view
```

## 🎯 RESTful Conventions

| HTTP Method | URL                | Action  | Description      |
| ----------- | ------------------ | ------- | ---------------- |
| GET         | /posts             | index   | List all posts   |
| GET         | /posts/create      | create  | Show create form |
| POST        | /posts             | store   | Save new post    |
| GET         | /posts/{id}        | show    | Display one post |
| GET         | /posts/{id}/edit   | edit    | Show edit form   |
| POST/PUT    | /posts/{id}/update | update  | Update post      |
| POST/DELETE | /posts/{id}/delete | destroy | Delete post      |

## 🔒 Security Best Practices

✓ **Output Escaping**: Always use `htmlspecialchars()`
✓ **Prepared Statements**: PDO with parameter binding
✓ **Password Hashing**: `password_hash()` / `password_verify()`
✓ **CSRF Tokens**: Add to forms (exercise for you!)
✓ **Input Validation**: Validate all user input
✓ **Session Security**: Regenerate ID on login

## 📝 Adding New Features

### Create a New Controller

```php
// app/Controllers/CommentController.php
namespace Controllers;

class CommentController extends Controller {
    public function index(): void {
        // Logic here
        $this->view('comments/index');
    }
}
```

### Create a New Model

```php
// app/Models/Comment.php
namespace Models;

class Comment extends Model {
    protected string $table = 'comments';

    public function byPost(int $postId): array {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE post_id = ?",
            [$postId]
        );
    }
}
```

### Add Routes

```php
// routes.php
$commentController = new CommentController();
$router->get('/posts/{id}/comments', [$commentController, 'index']);
```

## 🚦 Common Patterns

### Middleware (Authentication Example)

```php
// Add to routes.php before protected routes
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

// Now define protected routes
$router->get('/admin', [$adminController, 'index']);
```

### API Endpoints

```php
public function apiPosts(): void {
    $posts = $this->postModel->all();
    $this->json($posts);
}
```

### Form Validation

```php
$errors = $this->validate([
    'title' => 'required',
    'content' => 'required',
    'email' => 'required'
]);

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $this->redirect('/posts/create');
}
```

## 🧪 Testing

```bash
# Test routes
curl http://localhost:8000/
curl http://localhost:8000/posts
curl http://localhost:8000/posts/1

# Test POST
curl -X POST http://localhost:8000/posts \
  -d "title=Test&content=Content"
```

## 📚 Next Steps

1. **Add Authentication**: User login/logout
2. **CSRF Protection**: Token-based form security
3. **Middleware System**: Auth, logging, etc.
4. **Template Engine**: Use Twig or Blade
5. **Database Migrations**: Version control for schema
6. **Unit Tests**: PHPUnit for testing
7. **API Layer**: Build RESTful API
8. **Dependency Injection**: Service container

## Related Chapter

[Chapter 18: Project Structuring a Simple Application](../../chapters/18-project-structuring-a-simple-application.md)

## Further Reading

- [MVC Pattern Explained](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller)
- [PSR-4 Autoloading](https://www.php-fig.org/psr/psr-4/)
- [Repository Pattern](https://designpatternsphp.readthedocs.io/en/latest/More/Repository/README.html)
