---
title: "19: Framework Comparison"
description: "Compare Laravel, Symfony, and Slim frameworks to choose the right tool for your PHP project"
series: "php-for-java-developers"
chapter: 19
order: 19
difficulty: "Intermediate"
prerequisites:
  - "/series/php-for-java-developers/chapters/18-security-best-practices"
---

![Framework Comparison](/images/php-for-java-developers/chapter-19-framework-comparison-hero-full.webp)

# Chapter 19: Framework Comparison

<Badge type="warning">Intermediate</Badge> <Badge type="info">90-120 min</Badge>

## Overview

Choosing the right PHP framework is crucial for building maintainable, scalable applications. Just as Java developers choose between Spring Boot, Quarkus, and Micronaut based on project requirements, PHP developers select from Laravel, Symfony, and Slim. Each framework has distinct strengths, learning curves, and use cases.

This chapter provides a comprehensive comparison of PHP's three most popular frameworks, helping you understand their architectures, ecosystems, and when to use each one. We'll explore routing, ORM capabilities, dependency injection, templating, and more, always drawing parallels to Java frameworks you already know.

**What You'll Learn:**

- Framework architecture and design philosophies
- Routing systems and request handling
- ORM capabilities (Eloquent vs Doctrine vs custom)
- Dependency injection and service containers
- Templating engines and view rendering
- Middleware and request processing pipelines
- Validation systems and input handling
- Queue and background job processing
- CLI tools and developer experience
- Performance characteristics and scalability
- Ecosystem and community support
- Decision criteria for framework selection

## Prerequisites

::: info Time Estimate
⏱️ **90-120 minutes** to complete this chapter
:::

Before starting this chapter, you should be comfortable with:

- PHP namespaces and autoloading (Chapter 6)
- Object-oriented programming in PHP (Chapter 3)
- Dependency injection concepts (Chapter 11)
- Working with databases and PDO (Chapter 9)
- REST API development (Chapter 10)
- Security best practices (Chapter 18)

**Familiarity with Java frameworks helps:**

- Spring Boot (for Laravel comparisons)
- Spring Framework (for Symfony comparisons)
- Micronaut/Quarkus (for Slim comparisons)

## What You'll Build

In this chapter, you'll create:

- A simple user management API in each framework
- Routing examples demonstrating each framework's approach
- Database models using each framework's ORM
- Dependency injection examples
- A comparison matrix to guide framework selection

## Quick Start

Let's see a simple "Hello World" API endpoint in each framework to understand their basic structure:

::: code-group

```php [Laravel - routes/api.php]
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/hello', function () {
    return response()->json(['message' => 'Hello from Laravel!']);
});
```

```php [Symfony - src/Controller/HelloController.php]
<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class HelloController extends AbstractController
{
    #[Route('/hello', methods: ['GET'])]
    public function hello(): JsonResponse
    {
        return $this->json(['message' => 'Hello from Symfony!']);
    }
}
```

```php [Slim - public/index.php]
<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->get('/hello', function (Request $request, Response $response) {
    $response->getBody()->write(json_encode(['message' => 'Hello from Slim!']));
    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
```

:::

**Expected Result**: All three return `{"message": "Hello from [Framework]!"}` when accessing `/hello`.

**Key Differences**:
- **Laravel**: Route closure, automatic JSON response helper
- **Symfony**: Controller class with attribute routing, explicit return type
- **Slim**: Closure with PSR-7 request/response, manual JSON encoding

## Learning Objectives

By the end of this chapter, you will be able to:

1. **Compare framework architectures** and understand their design philosophies
2. **Evaluate routing systems** and choose the right approach for your project
3. **Assess ORM capabilities** and understand when to use each framework's database layer
4. **Implement dependency injection** using each framework's service container
5. **Choose the right framework** based on project requirements, team size, and constraints
6. **Understand performance trade-offs** between convenience and speed
7. **Navigate framework ecosystems** and leverage community resources

---

## Section 1: Framework Overview and Philosophy

### Goal

Understand the core philosophies and target use cases for each framework.

### Framework Comparison Matrix

| Framework | Type | Philosophy | Best For | Java Equivalent |
|-----------|------|------------|----------|------------------|
| **Laravel** | Full-stack | Convention over configuration, rapid development | Web apps, APIs, startups | Spring Boot |
| **Symfony** | Full-stack | Flexibility, enterprise-grade, reusable components | Enterprise apps, complex systems | Spring Framework |
| **Slim** | Micro | Minimal, lightweight, fast | APIs, microservices, learning | Micronaut/Quarkus |

### Laravel Philosophy

Laravel follows "convention over configuration" - similar to Spring Boot:

- **Opinionated defaults**: Sensible conventions reduce decision fatigue
- **Rapid development**: Built-in features for common tasks
- **Developer happiness**: Elegant syntax and excellent documentation
- **Batteries included**: Authentication, queues, caching, testing out of the box

```php
<?php

declare(strict_types=1);

// Laravel: Simple, expressive syntax
Route::get('/users', [UserController::class, 'index']);
User::where('active', true)->get();
```

### Symfony Philosophy

Symfony emphasizes flexibility and component reusability:

- **Modular architecture**: Use only what you need
- **Enterprise-ready**: Battle-tested components
- **Framework-agnostic**: Components work standalone
- **Configuration-driven**: Explicit configuration over magic

```php
<?php

declare(strict_types=1);

// Symfony: Explicit, flexible
#[Route('/users', methods: ['GET'])]
public function index(): Response
{
    $users = $this->userRepository->findAll();
    return $this->json($users);
}
```

### Slim Philosophy

Slim prioritizes minimalism and performance:

- **Lightweight**: Minimal dependencies (~2MB)
- **Fast**: Low overhead, high performance
- **Flexible**: Build exactly what you need
- **Learning-friendly**: Understand HTTP fundamentals

```php
<?php

declare(strict_types=1);

// Slim: Minimal, explicit
$app->get('/users', function (Request $request, Response $response) {
    $users = $userService->getAll();
    return $response->withJson($users);
});
```

### Why It Works

Each framework serves different needs:

- **Laravel** accelerates development with conventions and built-in features, perfect for rapid prototyping and standard web applications
- **Symfony** provides flexibility for complex enterprise applications where you need fine-grained control
- **Slim** offers minimal overhead for APIs and microservices where performance and simplicity matter most

---

## Section 2: Installation and Project Setup

### Goal

Set up a new project in each framework to understand their installation processes.

### Laravel Installation

Laravel uses Composer and provides a convenient installer:

**Actions:**

1. **Install Laravel globally** (optional but recommended):

```bash
# Install Laravel installer globally
composer global require laravel/installer

# Ensure Composer global bin directory is in PATH
# Add to ~/.zshrc or ~/.bashrc:
# export PATH="$HOME/.composer/vendor/bin:$PATH"
```

2. **Create new Laravel project**:

```bash
# Using Laravel installer
laravel new my-app

# OR using Composer directly
composer create-project laravel/laravel my-app

# Navigate to project
cd my-app
```

3. **Start development server**:

```bash
# Start Laravel development server
php artisan serve

# Server runs on http://localhost:8000
# Visit http://localhost:8000 to see Laravel welcome page
```

**Project Structure:**

```
my-app/
├── app/              # Application code
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   ├── Models/
│   └── Services/
├── config/           # Configuration files
├── database/        # Migrations, seeders
├── routes/          # Route definitions
│   ├── web.php
│   └── api.php
├── resources/       # Views, assets
└── public/         # Web root
```

### Symfony Installation

Symfony uses the Symfony CLI or Composer:

**Actions:**

1. **Install Symfony CLI** (recommended):

```bash
# macOS (using Homebrew)
brew install symfony-cli/tap/symfony

# Or download from https://symfony.com/download
# Or use Composer directly (no CLI needed)
```

2. **Create new Symfony project**:

```bash
# Using Symfony CLI
symfony new my-app

# OR using Composer directly
composer create-project symfony/skeleton my-app

# Navigate to project
cd my-app
```

3. **Start development server**:

```bash
# Using Symfony CLI
symfony server:start

# OR using PHP built-in server
php -S localhost:8000 -t public

# Server runs on http://localhost:8000
# Visit http://localhost:8000 to see Symfony welcome page
```

**Project Structure:**

```
my-app/
├── src/
│   └── Controller/  # Controllers
├── config/          # Configuration (YAML/XML/PHP)
├── migrations/      # Database migrations
├── public/         # Web root
├── templates/      # Twig templates
└── var/           # Cache, logs
```

### Slim Installation

Slim is installed via Composer as a dependency:

**Actions:**

1. **Create project directory and initialize Composer**:

```bash
# Create project directory
mkdir my-app && cd my-app

# Initialize Composer (interactive)
composer init

# OR create composer.json manually
```

2. **Install Slim dependencies**:

```bash
# Install Slim framework
composer require slim/slim:"^4.0"

# Install PSR-7 implementation
composer require slim/psr7

# Install error handling middleware (recommended)
composer require slim/error-handler
```

3. **Create entry point** (`public/index.php`):

```php
# filename: public/index.php
<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write('Hello from Slim!');
    return $response;
});

$app->run();
```

4. **Start PHP built-in server**:

```bash
# Start PHP development server
php -S localhost:8000 -t public

# Server runs on http://localhost:8000
# Visit http://localhost:8000 to see your Slim app
```

**Project Structure:**

```
my-app/
├── src/
│   └── Controllers/
├── public/
│   └── index.php   # Entry point
├── config/
└── vendor/         # Dependencies
```

### Expected Result

After installation, each framework provides:

- **Laravel**: Welcome page at `http://localhost:8000` with Laravel branding
- **Symfony**: Welcome page with Symfony logo and environment info
- **Slim**: Blank page (you build everything from scratch)

### Why It Works

Installation reflects each framework's philosophy:

- **Laravel** provides a complete application skeleton with many features pre-configured
- **Symfony** offers a minimal skeleton that you customize based on your needs
- **Slim** requires manual setup, giving you full control over project structure

---

## Section 3: Routing Comparison

### Goal

Compare routing systems across frameworks and understand their approaches.

### Laravel Routing

Laravel uses expressive, fluent routing similar to Spring Boot:

**Actions:**

1. **Define routes in `routes/api.php`**:

```php
# filename: routes/api.php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

// Simple route
Route::get('/users', [UserController::class, 'index']);

// Route with parameters
Route::get('/users/{id}', [UserController::class, 'show']);

// Route groups with middleware
Route::middleware('auth')->group(function () {
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});

// Route model binding (automatic!)
Route::get('/users/{user}', [UserController::class, 'show']);
// Laravel automatically resolves User model from ID
```

2. **Create controller**:

```php
# filename: app/Http/Controllers/UserController.php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::all();
        return response()->json($users);
    }

    public function show(User $user): JsonResponse
    {
        // $user is automatically resolved from route parameter
        return response()->json($user);
    }
}
```

```java [Spring Boot Equivalent]
@RestController
@RequestMapping("/users")
public class UserController {
    
    @GetMapping
    public List<User> index() { }
    
    @GetMapping("/{id}")
    public User show(@PathVariable Long id) { }
    
    @PostMapping
    @PreAuthorize("hasRole('USER')")
    public User store(@RequestBody User user) { }
}
```

:::

**Laravel Route Features:**

- Route model binding (automatic model resolution)
- Route caching for performance
- Route groups for organization
- Middleware assignment per route
- Named routes for URL generation

### Symfony Routing

Symfony uses attributes (annotations) or YAML configuration:

**Actions:**

1. **Create controller with route attributes**:

```php
# filename: src/Controller/UserController.php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/users', methods: ['GET'])]
    public function index(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();
        return $this->json($users);
    }

    #[Route('/users/{id}', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($id);
        
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }
        
        return $this->json($user);
    }

    #[Route('/users', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        // Process and save user
        return $this->json(['created' => true], 201);
    }
}
```

```yaml [Symfony YAML Routes]
# config/routes.yaml
user_index:
    path: /users
    controller: App\Controller\UserController::index
    methods: [GET]

user_show:
    path: /users/{id}
    controller: App\Controller\UserController::show
    methods: [GET]
    requirements:
        id: '\d+'
```

```java [Spring Framework Equivalent]
@RestController
@RequestMapping("/users")
public class UserController {
    
    @GetMapping
    public ResponseEntity<List<User>> index() { }
    
    @GetMapping("/{id}")
    public ResponseEntity<User> show(@PathVariable @Min(1) Long id) { }
}
```

:::

**Symfony Route Features:**

- Multiple configuration formats (attributes, YAML, XML, PHP)
- Route requirements and constraints
- Route parameters with type conversion
- Route caching for production
- Flexible route organization

### Slim Routing

Slim uses a simple, closure-based routing system:

**Actions:**

1. **Define routes in `public/index.php`**:

```php
# filename: public/index.php
<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

// Simple GET route
$app->get('/users', function (Request $request, Response $response) {
    $users = [
        ['id' => 1, 'name' => 'John Doe'],
        ['id' => 2, 'name' => 'Jane Smith']
    ];
    $response->getBody()->write(json_encode($users));
    return $response->withHeader('Content-Type', 'application/json');
});

// Route with parameters
$app->get('/users/{id}', function (Request $request, Response $response, array $args) {
    $id = (int) $args['id'];
    $user = ['id' => $id, 'name' => 'User ' . $id];
    $response->getBody()->write(json_encode($user));
    return $response->withHeader('Content-Type', 'application/json');
});

// Route groups with middleware
$app->group('/api', function ($group) {
    $group->post('/users', function (Request $request, Response $response) {
        $data = json_decode($request->getBody()->getContents(), true);
        // Create user logic here
        return $response->withStatus(201)
            ->withHeader('Content-Type', 'application/json')
            ->withJson(['created' => true]);
    });
    
    $group->put('/users/{id}', function (Request $request, Response $response, array $args) {
        $id = (int) $args['id'];
        $data = json_decode($request->getBody()->getContents(), true);
        // Update user logic here
        return $response->withHeader('Content-Type', 'application/json')
            ->withJson(['updated' => true]);
    });
})->add(new AuthMiddleware());

$app->run();
```

```java [Micronaut Equivalent]
@Controller("/users")
public class UserController {
    
    @Get
    public List<User> index() { }
    
    @Get("/{id}")
    public User show(Long id) { }
    
    @Post
    @Secured("ROLE_USER")
    public User store(@Body User user) { }
}
```

:::

**Slim Route Features:**

- Minimal, closure-based routing
- PSR-7 request/response objects
- Route groups for organization
- Middleware support
- Fast route resolution

### Comparison Table

| Feature | Laravel | Symfony | Slim |
|---------|---------|---------|------|
| **Route Definition** | Fluent API | Attributes/YAML | Closures |
| **Route Model Binding** | ✅ Automatic | ❌ Manual | ❌ Manual |
| **Route Caching** | ✅ | ✅ | ✅ |
| **Route Groups** | ✅ | ✅ | ✅ |
| **Middleware** | ✅ Per route | ✅ Per route | ✅ Per route |
| **Performance** | Medium | Fast | Fastest |

### Why It Works

Each routing approach serves different needs:

- **Laravel** prioritizes developer experience with expressive syntax and automatic model binding
- **Symfony** offers flexibility with multiple configuration formats and explicit control
- **Slim** focuses on simplicity and performance with minimal abstraction

---

## Section 4: ORM Comparison

### Goal

Compare database abstraction layers: Eloquent (Laravel), Doctrine (Symfony), and PDO (Slim).

### Laravel Eloquent ORM

Eloquent provides an ActiveRecord-style ORM similar to JPA/Hibernate:

**Actions:**

1. **Create Eloquent model**:

```php
# filename: app/Models/User.php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    // Table name (auto-detected: 'users')
    protected $table = 'users';
    
    // Mass assignment protection
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password'];
    
    // Timestamps (created_at, updated_at)
    public $timestamps = true;
    
    // Relationships
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
    
    // Accessors (getters)
    public function getNameAttribute(string $value): string
    {
        return ucwords($value);
    }
    
    // Mutators (setters)
    public function setEmailAttribute(string $value): void
    {
        $this->attributes['email'] = strtolower($value);
    }
    
    // Query scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
    
    public function scopeEmailDomain($query, string $domain)
    {
        return $query->where('email', 'like', "%@{$domain}");
    }
}

// Usage examples
$users = User::where('email', 'like', '%@example.com')
    ->active()
    ->orderBy('created_at', 'desc')
    ->get();

$user = User::find(1);
$user->name = 'John Doe';
$user->save();

// Mass assignment
$user = User::create([
    'name' => 'Jane Doe',
    'email' => 'jane@example.com',
    'password' => bcrypt('secret')
]);

// Using scopes
$activeUsers = User::active()->get();
$gmailUsers = User::emailDomain('gmail.com')->get();
```

```java [JPA/Hibernate Equivalent]
@Entity
@Table(name = "users")
public class User {
    @Id
    @GeneratedValue
    private Long id;
    
    private String name;
    private String email;
    
    @OneToMany(mappedBy = "user")
    private List<Post> posts;
    
    // Getters and setters
}

// Usage
List<User> users = entityManager
    .createQuery("SELECT u FROM User u WHERE u.email LIKE :email", User.class)
    .setParameter("email", "%@example.com")
    .getResultList();
```

:::

**Eloquent Features:**

- ActiveRecord pattern (models extend Model)
- Fluent query builder
- Automatic timestamps
- Relationship definitions
- Model events and observers
- Eager loading to prevent N+1 queries

### Symfony Doctrine ORM

Doctrine uses Data Mapper pattern (like JPA):

**Actions:**

1. **Create Doctrine entity**:

```php
# filename: src/Entity/User.php
<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;
    
    #[ORM\Column(type: 'string', length: 255)]
    private string $name;
    
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $email;
    
    #[ORM\Column(type: 'boolean')]
    private bool $active = true;
    
    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;
    
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'user')]
    private Collection $posts;
    
    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }
    
    // Getters and setters
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }
    
    public function getEmail(): string
    {
        return $this->email;
    }
    
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }
    
    public function isActive(): bool
    {
        return $this->active;
    }
    
    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }
    
    public function getPosts(): Collection
    {
        return $this->posts;
    }
}
```

2. **Create repository with custom queries**:

```php
# filename: src/Repository/UserRepository.php
<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }
    
    public function findActiveUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.active = :active')
            ->setParameter('active', true)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
    
    public function findByEmailDomain(string $domain): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.email LIKE :domain')
            ->setParameter('domain', "%@{$domain}")
            ->getQuery()
            ->getResult();
    }
}

// Usage in Controller
public function index(UserRepository $userRepository): JsonResponse
{
    $users = $userRepository->findActiveUsers();
    return $this->json($users);
}
```

```java [JPA Equivalent]
@Entity
@Table(name = "users")
public class User {
    @Id
    @GeneratedValue
    private Long id;
    
    private String name;
    private String email;
    
    @OneToMany(mappedBy = "user")
    private List<Post> posts;
}

@Repository
public interface UserRepository extends JpaRepository<User, Long> {
    List<User> findByActiveTrueOrderByCreatedAtDesc();
}
```

:::

**Doctrine Features:**

- Data Mapper pattern (entities are plain PHP classes)
- DQL (Doctrine Query Language) - like JPQL
- Repository pattern
- Migrations from entity definitions
- Event listeners and lifecycle callbacks
- Second-level cache support

### Slim with PDO

Slim doesn't include an ORM - you use PDO directly or choose your own:

::: code-group

```php [Slim with PDO]
<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class UserRepository
{
    public function __construct(private PDO $pdo) {}
    
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM users WHERE active = 1');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
    
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (name, email, password) VALUES (?, ?, ?)'
        );
        $stmt->execute([
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT)
        ]);
        return (int) $this->pdo->lastInsertId();
    }
}

// In route
$app->get('/users', function (Request $request, Response $response) use ($container) {
    $userRepo = $container->get(UserRepository::class);
    $users = $userRepo->findAll();
    $response->getBody()->write(json_encode($users));
    return $response->withHeader('Content-Type', 'application/json');
});
```

```java [JDBC Equivalent]
@Repository
public class UserRepository {
    @Autowired
    private JdbcTemplate jdbcTemplate;
    
    public List<User> findAll() {
        return jdbcTemplate.query(
            "SELECT * FROM users WHERE active = 1",
            new UserRowMapper()
        );
    }
}
```

:::

**Slim Database Options:**

- Use PDO directly (full control)
- Install Doctrine DBAL (lightweight query builder)
- Use Eloquent standalone (if you want Eloquent without Laravel)
- Choose any ORM you prefer

### Comparison Table

| Feature | Eloquent (Laravel) | Doctrine (Symfony) | PDO (Slim) |
|---------|-------------------|-------------------|------------|
| **Pattern** | ActiveRecord | Data Mapper | Raw SQL |
| **Learning Curve** | Easy | Medium | Easy |
| **Performance** | Good | Excellent | Best |
| **Relationships** | ✅ Fluent | ✅ Explicit | ❌ Manual |
| **Migrations** | ✅ Built-in | ✅ Built-in | ❌ Manual |
| **Query Builder** | ✅ Fluent | ✅ DQL | ❌ Raw SQL |

### Why It Works

Each ORM approach has trade-offs:

- **Eloquent** prioritizes developer productivity with intuitive syntax and automatic features
- **Doctrine** offers enterprise-grade features with explicit control and better performance
- **PDO** provides maximum performance and control, perfect for simple APIs or when you need raw SQL

---

## Section 5: Dependency Injection Comparison

### Goal

Compare dependency injection containers and service resolution across frameworks.

### Laravel Service Container

Laravel's container is similar to Spring's ApplicationContext:

::: code-group

```php [Laravel DI]
<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private EmailService $emailService
    ) {}
    
    public function createUser(array $data): User
    {
        $user = $this->userRepository->create($data);
        $this->emailService->sendWelcomeEmail($user);
        return $user;
    }
}

// Automatic resolution (no configuration needed!)
// Laravel resolves dependencies automatically

// Manual binding in ServiceProvider
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UserRepository::class, function ($app) {
            return new UserRepository($app->make(Database::class));
        });
        
        // Interface binding
        $this->app->bind(
            PaymentGatewayInterface::class,
            StripePaymentGateway::class
        );
    }
}
```

```java [Spring Boot Equivalent]
@Service
public class UserService {
    private final UserRepository userRepository;
    private final EmailService emailService;
    
    public UserService(UserRepository userRepository, EmailService emailService) {
        this.userRepository = userRepository;
        this.emailService = emailService;
    }
}

@Configuration
public class AppConfig {
    @Bean
    public UserRepository userRepository() {
        return new UserRepository();
    }
}
```

:::

**Laravel Container Features:**

- Automatic dependency resolution
- Service providers for registration
- Singleton and transient bindings
- Interface binding
- Contextual binding
- Service location (`app()->make()`)

### Symfony Service Container

Symfony uses explicit configuration (YAML/XML/PHP) or autowiring:

::: code-group

```php [Symfony Autowiring]
<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\UserRepository;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private EmailService $emailService
    ) {}
    
    // Symfony automatically resolves dependencies
    // No configuration needed if types are clear
}

// services.yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true
    
    App\:
        resource: '../src/*'
        exclude:
            - '../src/DependencyInjection/'
            - '../src/Entity/'
            - '../src/Kernel.php'
    
    # Explicit service definition
    App\Service\PaymentGatewayInterface:
        class: App\Service\StripePaymentGateway
```

```yaml [Symfony YAML Config]
# config/services.yaml
services:
    App\Repository\UserRepository:
        arguments:
            $entityManager: '@doctrine.orm.entity_manager'
    
    App\Service\UserService:
        arguments:
            $userRepository: '@App\Repository\UserRepository'
            $emailService: '@App\Service\EmailService'
```

```java [Spring Framework Equivalent]
@Service
public class UserService {
    @Autowired
    private UserRepository userRepository;
    
    @Autowired
    private EmailService emailService;
}

@Configuration
public class AppConfig {
    @Bean
    public UserRepository userRepository() {
        return new UserRepository();
    }
}
```

:::

**Symfony Container Features:**

- Autowiring (automatic resolution)
- Explicit configuration (YAML/XML/PHP)
- Service tags for organization
- Lazy loading services
- Service decoration
- Compiler passes for advanced configuration

### Slim Dependency Injection

Slim uses PSR-11 container (like Pimple or PHP-DI):

::: code-group

```php [Slim with PHP-DI]
<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;

// Create container
$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions([
    UserRepository::class => function ($container) {
        return new UserRepository($container->get(PDO::class));
    },
    
    PDO::class => function () {
        return new PDO(
            'mysql:host=localhost;dbname=myapp',
            'user',
            'password'
        );
    },
    
    UserService::class => \DI\autowire(UserService::class),
]);

$container = $containerBuilder->build();

// Create app with container
AppFactory::setContainer($container);
$app = AppFactory::create();

// Usage in routes
$app->get('/users', function (Request $request, Response $response) use ($container) {
    $userService = $container->get(UserService::class);
    $users = $userService->getAll();
    $response->getBody()->write(json_encode($users));
    return $response->withHeader('Content-Type', 'application/json');
});
```

```php [Slim with Pimple]
<?php

declare(strict_types=1);

use Pimple\Container;
use Slim\Factory\AppFactory;

$container = new Container();

$container['pdo'] = function ($c) {
    return new PDO('mysql:host=localhost;dbname=myapp', 'user', 'password');
};

$container['userRepository'] = function ($c) {
    return new UserRepository($c['pdo']);
};

$container['userService'] = function ($c) {
    return new UserService($c['userRepository']);
};

AppFactory::setContainer($container);
$app = AppFactory::create();
```

:::

**Slim Container Options:**

- Use any PSR-11 container
- PHP-DI (recommended, supports autowiring)
- Pimple (simple, lightweight)
- Custom container implementation
- Full control over dependency resolution

### Comparison Table

| Feature | Laravel | Symfony | Slim |
|---------|---------|---------|------|
| **Autowiring** | ✅ | ✅ | ✅ (with PHP-DI) |
| **Configuration** | Service Providers | YAML/XML/PHP | PHP array/DI config |
| **Interface Binding** | ✅ | ✅ | ✅ |
| **Lazy Loading** | ✅ | ✅ | ✅ |
| **Service Tags** | ❌ | ✅ | ❌ |
| **PSR-11** | ❌ | ✅ | ✅ |

### Why It Works

Each DI approach reflects framework philosophy:

- **Laravel** provides automatic resolution with sensible defaults, reducing configuration
- **Symfony** offers flexibility with multiple configuration formats and explicit control
- **Slim** lets you choose your container, giving maximum flexibility

---

## Section 6: Templating Engines

### Goal

Compare view rendering systems: Blade (Laravel), Twig (Symfony), and plain PHP (Slim).

### Laravel Blade

Blade is Laravel's templating engine with elegant syntax:

```php
{{-- resources/views/users/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <h1>Users</h1>
    
    @if($users->isEmpty())
        <p>No users found.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <a href="{{ route('users.show', $user->id) }}">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        {{ $users->links() }}
    @endif
@endsection
```

**Blade Features:**

- Template inheritance (`@extends`, `@section`)
- Component system (`@component`, `<x-component>`)
- Directives (`@if`, `@foreach`, `@auth`)
- Automatic XSS protection (`{{ }}` escapes, `{!! !!}` raw)
- Compiled to plain PHP (fast)

### Symfony Twig

Twig is Symfony's templating engine (similar to Jinja2):

```twig
{# templates/users/index.html.twig #}
{% extends 'base.html.twig' %}

{% block content %}
    <h1>Users</h1>
    
    {% if users is empty %}
        <p>No users found.</p>
    {% else %}
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                {% for user in users %}
                    <tr>
                        <td>{{ user.name }}</td>
                        <td>{{ user.email }}</td>
                        <td>
                            <a href="{{ path('user_show', {id: user.id}) }}">View</a>
                        </td>
                    </tr>
                {% endfor %}
            </tbody>
        </table>
    {% endif %}
{% endblock %}
```

**Twig Features:**

- Template inheritance (`{% extends %}`)
- Blocks and includes
- Filters (`|upper`, `|date`, `|escape`)
- Functions (`path()`, `url()`)
- Automatic escaping (safe by default)
- Extensible (custom filters/functions)

### Slim Templates

Slim doesn't include a templating engine - use PHP or install one:

```php
<?php

// Option 1: Plain PHP templates
// templates/users/index.php
?>
<h1>Users</h1>
<?php if (empty($users)): ?>
    <p>No users found.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php
// Option 2: Install Twig
composer require twig/twig

// In route
$app->get('/users', function (Request $request, Response $response) {
    $loader = new \Twig\Loader\FilesystemLoader('../templates');
    $twig = new \Twig\Environment($loader);
    $html = $twig->render('users/index.html.twig', ['users' => $users]);
    $response->getBody()->write($html);
    return $response;
});
```

**Slim Template Options:**

- Plain PHP (simple, no dependencies)
- Twig (install separately)
- Blade (install separately)
- Any templating engine you prefer

### Comparison Table

| Feature | Blade (Laravel) | Twig (Symfony) | Plain PHP (Slim) |
|---------|----------------|----------------|------------------|
| **Syntax** | `@directive` | `{% tag %}` | PHP tags |
| **Auto-escaping** | ✅ | ✅ | ❌ Manual |
| **Template Inheritance** | ✅ | ✅ | ❌ Manual |
| **Components** | ✅ | ✅ | ❌ |
| **Performance** | Fast (compiled) | Fast (compiled) | Fastest (native) |
| **Learning Curve** | Easy | Easy | Easy |

### Why It Works

Templating choices reflect framework philosophy:

- **Blade** integrates seamlessly with Laravel, providing elegant syntax and built-in features
- **Twig** offers a powerful, secure templating engine with extensive features
- **Slim** lets you choose your templating solution or use plain PHP for maximum control

---

## Section 7: CLI Tools and Developer Experience

### Goal

Compare command-line tools and developer productivity features.

### Laravel Artisan

Artisan is Laravel's CLI tool (like Spring Boot CLI):

```bash
# List all commands
php artisan list

# Create controller
php artisan make:controller UserController

# Create model with migration
php artisan make:model User -m

# Create migration
php artisan make:migration create_users_table

# Run migrations
php artisan migrate

# Create seeder
php artisan make:seeder UserSeeder

# Run seeders
php artisan db:seed

# Generate key
php artisan key:generate

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Tinker (REPL)
php artisan tinker
>>> User::count()
=> 42

# Queue workers
php artisan queue:work

# Schedule tasks
php artisan schedule:run
```

**Artisan Features:**

- Code generation (controllers, models, migrations)
- Database management (migrations, seeders)
- Cache management
- Queue processing
- Task scheduling
- REPL (Tinker)
- Custom commands

### Symfony Console

Symfony provides a powerful console component:

```bash
# List commands
php bin/console list

# Create controller
php bin/console make:controller UserController

# Create entity
php bin/console make:entity User

# Create migration
php bin/console make:migration

# Run migrations
php bin/console doctrine:migrations:migrate

# Create form
php bin/console make:form UserType

# Clear cache
php bin/console cache:clear

# Debug routes
php bin/console debug:router

# Debug container
php bin/console debug:container

# Debug autowiring
php bin/console debug:autowiring UserService
```

**Symfony Console Features:**

- Code generation (entities, controllers, forms)
- Database management (Doctrine migrations)
- Debug commands (routes, container, autowiring)
- Cache management
- Custom commands
- Interactive commands

### Slim CLI

Slim doesn't include CLI tools - you build your own:

```php
<?php

// Create custom CLI script: cli.php
#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

$application = new Application();

$application->add(new class extends Command {
    protected static $defaultName = 'migrate';
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Run migrations
        $output->writeln('Running migrations...');
        return Command::SUCCESS;
    }
});

$application->run();
```

**Slim CLI Options:**

- Build custom CLI scripts
- Use Symfony Console component
- Use Laravel Zero (micro-framework with Artisan)
- Use any CLI library you prefer

### Comparison Table

| Feature | Laravel Artisan | Symfony Console | Slim |
|---------|----------------|-----------------|------|
| **Code Generation** | ✅ Extensive | ✅ Extensive | ❌ Custom |
| **Database Tools** | ✅ Built-in | ✅ Built-in | ❌ Custom |
| **Debug Commands** | ✅ | ✅ Extensive | ❌ |
| **REPL** | ✅ Tinker | ❌ | ❌ |
| **Queue Workers** | ✅ Built-in | ❌ | ❌ |
| **Task Scheduling** | ✅ Built-in | ❌ | ❌ |

### Why It Works

CLI tools reflect framework completeness:

- **Laravel** provides comprehensive CLI tools for rapid development
- **Symfony** offers powerful debugging and code generation tools
- **Slim** requires you to build custom tools, giving full control

---

## Section 8: Performance and Scalability

### Goal

Understand performance characteristics and scalability considerations.

### Performance Benchmarks

| Metric | Laravel | Symfony | Slim |
|--------|---------|---------|------|
| **Request/Response Time** | ~50-100ms | ~30-60ms | ~10-30ms |
| **Memory Usage** | ~20-30MB | ~15-25MB | ~5-10MB |
| **Throughput (req/s)** | ~500-1000 | ~1000-2000 | ~2000-5000 |
| **Cold Start** | Slower | Medium | Fastest |
| **Warm Performance** | Good | Excellent | Excellent |

::: warning Benchmark Disclaimer
These are rough estimates. Actual performance depends on:
- Application complexity
- Database queries
- Caching strategy
- Server configuration
- PHP version and opcache settings
:::

### Laravel Performance

**Optimization Strategies:**

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Use queue for heavy tasks
dispatch(new ProcessLargeFile($file));

# Use Redis for caching
CACHE_DRIVER=redis
```

**Laravel Performance Tips:**

- Enable opcache
- Use route caching in production
- Use view caching
- Queue heavy operations
- Use Redis for sessions/cache
- Optimize database queries (eager loading)
- Use CDN for static assets

### Symfony Performance

**Optimization Strategies:**

```bash
# Production environment
APP_ENV=prod APP_DEBUG=false

# Clear and warm cache
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Use HTTP cache
#[Cache(expires: 3600)]
public function index(): Response { }
```

**Symfony Performance Tips:**

- Use production environment
- Enable opcache
- Warm up cache
- Use HTTP cache
- Optimize autoloader
- Use Redis for sessions
- Enable second-level Doctrine cache
- Use reverse proxy (Varnish)

### Slim Performance

**Optimization Strategies:**

```php
<?php

// Use opcache
// Enable in php.ini: opcache.enable=1

// Use fast PSR-11 container
$container = new \DI\ContainerBuilder();
$container->enableCompilation(__DIR__ . '/var/cache');

// Use connection pooling for database
$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_PERSISTENT => true
]);

// Cache responses
$app->get('/users', function ($request, $response) {
    $cache = new SimpleCache();
    $key = 'users_list';
    
    if ($data = $cache->get($key)) {
        return $response->withJson(json_decode($data));
    }
    
    $users = $userService->getAll();
    $cache->set($key, json_encode($users), 3600);
    return $response->withJson($users);
});
```

**Slim Performance Tips:**

- Minimal overhead (fastest)
- Use opcache
- Enable container compilation (PHP-DI)
- Use connection pooling
- Implement response caching
- Use fast PSR-11 container
- Optimize database queries
- Use reverse proxy

### Scalability Considerations

**Laravel Scalability:**

- ✅ Horizontal scaling (stateless)
- ✅ Queue workers for background jobs
- ✅ Redis for shared state
- ✅ Database connection pooling
- ⚠️ Session storage (use Redis/database, not file)

**Symfony Scalability:**

- ✅ Horizontal scaling (stateless)
- ✅ HTTP cache with reverse proxy
- ✅ Doctrine second-level cache
- ✅ Redis for sessions
- ✅ Message queue integration

**Slim Scalability:**

- ✅ Horizontal scaling (stateless)
- ✅ Minimal resource usage
- ✅ Fast request handling
- ✅ Easy to containerize
- ✅ Perfect for microservices

### Why It Works

Performance reflects framework complexity:

- **Laravel** trades some performance for developer productivity and built-in features
- **Symfony** balances performance with flexibility and enterprise features
- **Slim** prioritizes performance and minimal overhead, perfect for high-throughput APIs

---

## Section 9: Middleware and Request Processing

### Goal

Compare middleware systems and request/response processing pipelines.

### Laravel Middleware

Laravel middleware is similar to Spring interceptors:

```php
# filename: app/Http/Middleware/Authenticate.php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        return $next($request);
    }
}

// Register in routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [UserController::class, 'profile']);
});

// Or in controller
class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }
}
```

**Laravel Middleware Features:**
- Global middleware (runs on every request)
- Route middleware (specific routes)
- Middleware groups (web, api, etc.)
- Terminable middleware (runs after response)
- Middleware parameters

### Symfony Event Listeners

Symfony uses event listeners/subscribers (like Spring AOP):

```php
# filename: src/EventListener/AuthenticationListener.php
<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AuthenticationListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }
    
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        
        if (!$request->headers->has('Authorization')) {
            $response = new JsonResponse(['error' => 'Unauthorized'], 401);
            $event->setResponse($response);
        }
    }
}
```

**Symfony Event Features:**
- Event dispatcher (PSR-14)
- Event listeners and subscribers
- Event priorities
- Multiple events per listener
- Kernel events (request, response, exception, etc.)

### Slim Middleware

Slim uses PSR-15 middleware:

```php
# filename: src/Middleware/AuthMiddleware.php
<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if (!$request->hasHeader('Authorization')) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withStatus(401)
                ->withHeader('Content-Type', 'application/json');
        }
        
        return $handler->handle($request);
    }
}

// Usage
$app->add(new AuthMiddleware());
$app->group('/api', function ($group) {
    $group->get('/users', function ($request, $response) {
        // Protected route
    });
})->add(new AuthMiddleware());
```

**Slim Middleware Features:**
- PSR-15 compliant
- Per-route or global middleware
- Middleware stack execution
- Request/response manipulation
- Simple and explicit

### Comparison Table

| Feature | Laravel | Symfony | Slim |
|---------|---------|---------|------|
| **Pattern** | Middleware | Event Listeners | PSR-15 Middleware |
| **Global Middleware** | ✅ | ✅ | ✅ |
| **Route Middleware** | ✅ | ✅ | ✅ |
| **Priority/Order** | ✅ | ✅ | ✅ |
| **PSR Standards** | ❌ | ✅ (PSR-14) | ✅ (PSR-15) |
| **Learning Curve** | Easy | Medium | Easy |

### Why It Works

Each approach reflects framework philosophy:
- **Laravel** provides simple, intuitive middleware with built-in helpers
- **Symfony** offers powerful event-driven architecture with fine-grained control
- **Slim** uses PSR standards for interoperability and simplicity

---

## Section 10: Validation Systems

### Goal

Compare input validation approaches across frameworks.

### Laravel Validation

Laravel provides fluent validation similar to Bean Validation:

```php
# filename: app/Http/Controllers/UserController.php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'age' => 'nullable|integer|min:18|max:120',
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        // Validation passed
        $validated = $validator->validated();
    }
    
    // Or use Form Request classes
    public function update(UpdateUserRequest $request, User $user)
    {
        // $request->validated() is already available
    }
}

// Form Request class
# filename: app/Http/Requests/UpdateUserRequest.php
class UpdateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $this->user->id,
        ];
    }
}
```

**Laravel Validation Features:**
- Fluent rule chaining
- Form Request classes (reusable)
- Custom validation rules
- Conditional validation
- Database-aware rules (unique, exists)

### Symfony Validator

Symfony uses annotations/attributes (like Bean Validation):

```php
# filename: src/Entity/User.php
<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class User
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    private string $name;
    
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\UniqueEntity(fields: ['email'])]
    private string $email;
    
    #[Assert\NotBlank]
    #[Assert\Length(min: 8)]
    private string $password;
    
    #[Assert\Type('integer')]
    #[Assert\Range(min: 18, max: 120)]
    private ?int $age = null;
}

// In Controller
# filename: src/Controller/UserController.php
use Symfony\Component\Validator\Validator\ValidatorInterface;

public function store(Request $request, ValidatorInterface $validator): JsonResponse
{
    $user = new User();
    $user->setName($request->request->get('name'));
    $user->setEmail($request->request->get('email'));
    
    $errors = $validator->validate($user);
    
    if (count($errors) > 0) {
        return $this->json($errors, 422);
    }
    
    // Save user
}
```

**Symfony Validator Features:**
- Attribute-based validation (like JSR-303)
- Constraint classes
- Custom constraints
- Validation groups
- Property-level and class-level validation

### Slim Validation

Slim doesn't include validation - use libraries:

```php
# filename: src/Middleware/ValidationMiddleware.php
<?php

declare(strict_types=1);

use Respect\Validation\Validator as v;

class ValidationMiddleware
{
    public function __invoke($request, $response, $next)
    {
        $data = json_decode($request->getBody()->getContents(), true);
        
        $validator = v::key('name', v::stringType()->length(2, 255))
            ->key('email', v::email())
            ->key('password', v::stringType()->length(8, null));
        
        try {
            $validator->assert($data);
            $request = $request->withAttribute('validated', $data);
            return $next($request, $response);
        } catch (\Respect\Validation\Exceptions\ValidationException $e) {
            return $response->withJson(['errors' => $e->getMessages()], 422);
        }
    }
}
```

**Slim Validation Options:**
- Respect/Validation (popular)
- Symfony Validator (standalone)
- Custom validation
- Any PSR-compliant library

### Comparison Table

| Feature | Laravel | Symfony | Slim |
|---------|---------|---------|------|
| **Built-in** | ✅ | ✅ | ❌ |
| **Rule Syntax** | Fluent | Attributes | Library-dependent |
| **Form Requests** | ✅ | ❌ | ❌ |
| **Custom Rules** | ✅ | ✅ | ✅ |
| **Database Rules** | ✅ | ✅ | ❌ |
| **Error Messages** | Automatic | Automatic | Manual |

### Why It Works

Validation approaches reflect framework design:
- **Laravel** provides convenient fluent validation with built-in database rules
- **Symfony** offers annotation-based validation similar to Java Bean Validation
- **Slim** lets you choose your validation library for maximum flexibility

---

## Section 11: Queue and Job Systems

### Goal

Compare background job processing capabilities.

### Laravel Queues

Laravel has built-in queue system (like Spring @Async):

```php
# filename: app/Jobs/SendWelcomeEmail.php
<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWelcomeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(
        public User $user
    ) {}
    
    public function handle(): void
    {
        // Send welcome email
        Mail::to($this->user->email)->send(new WelcomeMail($this->user));
    }
}

// Dispatch job
SendWelcomeEmail::dispatch($user);

// Or with delay
SendWelcomeEmail::dispatch($user)->delay(now()->addMinutes(5));

// Queue configuration (config/queue.php)
'connections' => [
    'database' => [
        'driver' => 'database',
        'table' => 'jobs',
    ],
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
    ],
    'sqs' => [
        'driver' => 'sqs',
        // AWS SQS configuration
    ],
]
```

**Laravel Queue Features:**
- Multiple queue drivers (database, Redis, SQS, Beanstalkd)
- Job batching and chaining
- Failed job handling
- Queue priorities
- Rate limiting
- Horizon dashboard (monitoring)

### Symfony Messenger

Symfony Messenger component (like Spring Integration):

```php
# filename: src/Message/SendWelcomeEmail.php
<?php

declare(strict_types=1);

namespace App\Message;

class SendWelcomeEmail
{
    public function __construct(
        private int $userId
    ) {}
    
    public function getUserId(): int
    {
        return $this->userId;
    }
}

# filename: src/MessageHandler/SendWelcomeEmailHandler.php
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SendWelcomeEmailHandler implements MessageHandlerInterface
{
    public function __invoke(SendWelcomeEmail $message): void
    {
        $user = $this->userRepository->find($message->getUserId());
        // Send email
    }
}

// Dispatch message
$bus->dispatch(new SendWelcomeEmail($user->getId()));

// Configuration (config/packages/messenger.yaml)
framework:
    messenger:
        transports:
            async: 'doctrine://default'
            failed: 'doctrine://default?queue_name=failed'
```

**Symfony Messenger Features:**
- Multiple transports (Doctrine, Redis, AMQP, SQS)
- Message routing
- Middleware support
- Retry strategies
- Failed message handling
- Message serialization

### Slim Queues

Slim doesn't include queues - use libraries:

```php
# Install queue library
composer require enqueue/enqueue

// Use Enqueue or similar library
use Enqueue\SimpleClient\SimpleClient;

$client = new SimpleClient('redis://localhost:6379');
$client->send('user.welcome', ['userId' => $user->getId()]);
```

**Slim Queue Options:**
- Enqueue (multi-transport)
- Laravel Queue (standalone)
- Custom implementation
- External queue services (RabbitMQ, SQS)

### Comparison Table

| Feature | Laravel | Symfony | Slim |
|---------|---------|---------|------|
| **Built-in** | ✅ | ✅ (Component) | ❌ |
| **Drivers** | Many | Many | Library-dependent |
| **Monitoring** | Horizon | ❌ | ❌ |
| **Batching** | ✅ | ✅ | ❌ |
| **Retry Logic** | ✅ | ✅ | Library-dependent |
| **Learning Curve** | Easy | Medium | Medium |

### Why It Works

Queue systems reflect framework completeness:
- **Laravel** provides comprehensive queue system with monitoring tools
- **Symfony** offers flexible Messenger component with multiple transports
- **Slim** requires external libraries, giving you choice of queue solution

---

## Section 12: Testing Framework Integration

### Goal

Compare how each framework integrates with PHPUnit and testing best practices.

### Laravel Testing

Laravel provides extensive testing helpers and features:

```php
# filename: tests/Feature/UserApiTest.php
<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserApiTest extends TestCase
{
    use RefreshDatabase; // Resets database after each test
    
    public function test_can_create_user(): void
    {
        $response = $this->postJson('/api/users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);
        
        $response->assertStatus(201)
            ->assertJson([
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ]);
        
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }
    
    public function test_can_authenticate_user(): void
    {
        $user = User::factory()->create();
        
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        
        $response->assertStatus(200)
            ->assertJsonStructure(['token']);
    }
}

# filename: tests/Unit/UserServiceTest.php
use Tests\TestCase;
use App\Services\UserService;
use App\Repositories\UserRepository;
use Mockery;

class UserServiceTest extends TestCase
{
    public function test_create_user_calls_repository(): void
    {
        $mockRepo = Mockery::mock(UserRepository::class);
        $mockRepo->shouldReceive('create')
            ->once()
            ->andReturn(new User(['id' => 1]));
        
        $service = new UserService($mockRepo);
        $service->createUser(['name' => 'John']);
    }
}
```

**Laravel Testing Features:**
- `RefreshDatabase` trait (auto-migrations)
- Database factories and seeders
- HTTP testing helpers (`get()`, `postJson()`, etc.)
- Authentication helpers (`actingAs()`)
- Queue testing (`Queue::fake()`)
- Event testing (`Event::fake()`)
- Mail testing (`Mail::fake()`)

### Symfony Testing

Symfony provides testing tools via PHPUnit and WebTestCase:

```php
# filename: tests/Controller/UserControllerTest.php
<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    public function testCreateUser(): void
    {
        $client = static::createClient();
        
        $client->request('POST', '/api/users', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]));
        
        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('John Doe', $responseData['name']);
    }
    
    public function testGetUser(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/users/1');
        
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
    }
}

# filename: tests/Unit/UserServiceTest.php
use PHPUnit\Framework\TestCase;
use App\Service\UserService;
use App\Repository\UserRepository;
use PHPUnit\Framework\MockObject\MockObject;

class UserServiceTest extends TestCase
{
    private UserRepository|MockObject $repository;
    private UserService $service;
    
    protected function setUp(): void
    {
        $this->repository = $this->createMock(UserRepository::class);
        $this->service = new UserService($this->repository);
    }
    
    public function testCreateUser(): void
    {
        $this->repository->expects($this->once())
            ->method('save')
            ->willReturn(new User());
        
        $this->service->createUser(['name' => 'John']);
    }
}
```

**Symfony Testing Features:**
- `WebTestCase` for functional tests
- `KernelTestCase` for integration tests
- Database transactions (auto-rollback)
- Client for HTTP testing
- Service mocking
- Fixtures support (Alice/Faker)

### Slim Testing

Slim requires manual setup but is flexible:

```php
# filename: tests/UserApiTest.php
<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface;

class UserApiTest extends TestCase
{
    private $app;
    
    protected function setUp(): void
    {
        $this->app = AppFactory::create();
        // Register routes
        require __DIR__ . '/../routes/api.php';
    }
    
    public function testCreateUser(): void
    {
        $request = $this->createRequest('POST', '/api/users')
            ->withParsedBody([
                'name' => 'John Doe',
                'email' => 'john@example.com',
            ]);
        
        $response = $this->app->handle($request);
        
        $this->assertEquals(201, $response->getStatusCode());
    }
    
    private function createRequest(string $method, string $path): ServerRequestInterface
    {
        return (new \Slim\Psr7\Factory\ServerRequestFactory())
            ->createServerRequest($method, $path);
    }
}
```

**Slim Testing Options:**
- Manual test setup
- Use Slim's PSR-7 factories
- Any PHPUnit testing patterns
- Database fixtures manually
- More control, more setup required

### Comparison Table

| Feature | Laravel | Symfony | Slim |
|---------|---------|---------|------|
| **Testing Helpers** | ✅ Extensive | ✅ Good | ❌ Manual |
| **Database Reset** | ✅ Auto | ✅ Transaction | ❌ Manual |
| **HTTP Testing** | ✅ Built-in | ✅ WebTestCase | ❌ Manual |
| **Factories** | ✅ Built-in | ✅ Fixtures | ❌ Manual |
| **Mocking** | ✅ Mockery | ✅ PHPUnit | ✅ PHPUnit |
| **Learning Curve** | Easy | Medium | Medium |

### Why It Works

Testing integration reflects framework philosophy:
- **Laravel** provides comprehensive testing helpers for rapid test writing
- **Symfony** offers solid testing tools with more explicit setup
- **Slim** requires manual setup but gives you full control over test structure

---

## Section 13: Caching Strategies

### Goal

Compare caching implementations and strategies across frameworks.

### Laravel Caching

Laravel provides unified caching API with multiple drivers:

```php
# filename: app/Services/ProductService.php
<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class ProductService
{
    public function getProduct(int $id): array
    {
        return Cache::remember("product.{$id}", 3600, function () use ($id) {
            return $this->fetchFromDatabase($id);
        });
    }
    
    public function getPopularProducts(): array
    {
        return Cache::tags(['products', 'popular'])
            ->remember('products.popular', 1800, function () {
                return $this->fetchPopularProducts();
            });
    }
    
    public function clearProductCache(int $id): void
    {
        Cache::forget("product.{$id}");
        Cache::tags(['products'])->flush();
    }
}

// Configuration (config/cache.php)
'stores' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'cache',
    ],
    'memcached' => [
        'driver' => 'memcached',
        'servers' => [
            ['host' => '127.0.0.1', 'port' => 11211],
        ],
    ],
    'file' => [
        'driver' => 'file',
        'path' => storage_path('framework/cache'),
    ],
]
```

**Laravel Caching Features:**
- Multiple drivers (Redis, Memcached, file, database, array)
- Cache tags (for grouped invalidation)
- Atomic locks
- Cache events
- Query result caching
- View caching
- Route caching
- Config caching

### Symfony Caching

Symfony Cache component (PSR-6 compliant):

```php
# filename: src/Service/ProductService.php
<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class ProductService
{
    public function __construct(
        private CacheInterface $cache
    ) {}
    
    public function getProduct(int $id): array
    {
        return $this->cache->get("product.{$id}", function (ItemInterface $item) use ($id) {
            $item->expiresAfter(3600);
            return $this->fetchFromDatabase($id);
        });
    }
    
    public function invalidateProduct(int $id): void
    {
        $this->cache->delete("product.{$id}");
    }
}

// Configuration (config/packages/cache.yaml)
framework:
    cache:
        app: cache.adapter.redis
        pools:
            products.cache:
                adapter: cache.adapter.redis
                default_lifetime: 3600
```

**Symfony Caching Features:**
- PSR-6 compliant
- Multiple adapters (Redis, Memcached, APCu, filesystem)
- Cache pools (isolated namespaces)
- Tag-based invalidation
- Doctrine query cache integration
- HTTP cache (reverse proxy)

### Slim Caching

Slim doesn't include caching - use libraries:

```php
# Install cache library
composer require symfony/cache

# filename: src/Service/ProductService.php
use Symfony\Component\Cache\Adapter\RedisAdapter;

$redis = new \Redis();
$redis->connect('127.0.0.1', 6379);
$cache = new RedisAdapter($redis);

$product = $cache->getItem("product.{$id}");
if (!$product->isHit()) {
    $product->set($this->fetchFromDatabase($id));
    $product->expiresAfter(3600);
    $cache->save($product);
}
```

**Slim Caching Options:**
- Symfony Cache (standalone)
- PSR-6/PSR-16 libraries
- Direct Redis/Memcached
- Custom implementation

### Comparison Table

| Feature | Laravel | Symfony | Slim |
|---------|---------|---------|------|
| **Built-in** | ✅ | ✅ (Component) | ❌ |
| **PSR-6 Compliant** | ❌ | ✅ | Library-dependent |
| **Multiple Drivers** | ✅ | ✅ | Library-dependent |
| **Cache Tags** | ✅ | ✅ | Library-dependent |
| **Query Cache** | ✅ | ✅ (Doctrine) | ❌ |
| **View Cache** | ✅ | ✅ | ❌ |

### Why It Works

Caching approaches reflect framework design:
- **Laravel** provides convenient caching with built-in helpers
- **Symfony** offers PSR-6 compliant caching with flexible adapters
- **Slim** lets you choose your caching solution

---

## Section 14: Security Features

### Goal

Compare built-in security features and best practices.

### Laravel Security

Laravel includes comprehensive security features:

```php
# filename: app/Http/Controllers/AuthController.php
// CSRF Protection (automatic for web routes)
Route::post('/users', [UserController::class, 'store'])
    ->middleware('csrf');

// Password Hashing
use Illuminate\Support\Facades\Hash;

$hashed = Hash::make($password);
if (Hash::check($password, $hashed)) {
    // Valid
}

// Encryption
use Illuminate\Support\Facades\Crypt;

$encrypted = Crypt::encryptString('sensitive data');
$decrypted = Crypt::decryptString($encrypted);

// Authentication
use Illuminate\Support\Facades\Auth;

if (Auth::attempt(['email' => $email, 'password' => $password])) {
    // Login successful
}

// Authorization (Policies)
# filename: app/Policies/PostPolicy.php
class PostPolicy
{
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }
}

// Usage in controller
$this->authorize('update', $post);

// Rate Limiting
Route::middleware(['throttle:60,1'])->group(function () {
    Route::get('/api/data', [DataController::class, 'index']);
});
```

**Laravel Security Features:**
- CSRF protection (automatic)
- XSS protection (Blade auto-escaping)
- SQL injection prevention (Eloquent/Query Builder)
- Password hashing (bcrypt/argon2)
- Encryption/decryption
- Authentication scaffolding
- Authorization (Policies, Gates)
- Rate limiting
- Security headers middleware

### Symfony Security

Symfony Security component (comprehensive):

```php
# filename: config/packages/security.yaml
security:
    password_hashers:
        Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: 'auto'
    
    providers:
        app_user_provider:
            entity:
                class: App\Entity\User
                property: email
    
    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        main:
            lazy: true
            provider: app_user_provider
            form_login:
                login_path: login
                check_path: login
            logout:
                path: logout

# filename: src/Controller/PostController.php
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
public function delete(Post $post): Response
{
    // Only admins can access
}

#[IsGranted('POST_EDIT', subject: 'post')]
public function edit(Post $post): Response
{
    // Custom voter check
}
```

**Symfony Security Features:**
- Authentication (multiple providers)
- Authorization (voters, role hierarchy)
- Password hashing (auto-detection)
- CSRF protection
- Security voters
- Access control expressions
- Remember me functionality
- Two-factor authentication support

### Slim Security

Slim requires manual security implementation:

```php
# filename: src/Middleware/CsrfMiddleware.php
use Slim\Csrf\Guard;

$app->add(new Guard($responseFactory));

# filename: src/Middleware/AuthMiddleware.php
class AuthMiddleware
{
    public function __invoke($request, $response, $next)
    {
        $token = $request->getHeaderLine('Authorization');
        
        if (!$this->validateToken($token)) {
            return $response->withStatus(401);
        }
        
        return $next($request, $response);
    }
}

// Password hashing (use PHP built-in)
$hash = password_hash($password, PASSWORD_ARGON2ID);
if (password_verify($password, $hash)) {
    // Valid
}
```

**Slim Security Options:**
- Manual CSRF protection
- JWT authentication libraries
- PHP built-in password functions
- Security middleware (custom or libraries)
- More control, more responsibility

### Comparison Table

| Feature | Laravel | Symfony | Slim |
|---------|---------|---------|------|
| **CSRF Protection** | ✅ Automatic | ✅ Built-in | ❌ Manual |
| **Authentication** | ✅ Scaffolding | ✅ Component | ❌ Manual |
| **Authorization** | ✅ Policies/Gates | ✅ Voters | ❌ Manual |
| **Password Hashing** | ✅ Helper | ✅ Auto | ✅ Built-in PHP |
| **Encryption** | ✅ Built-in | ✅ Component | ❌ Manual |
| **Rate Limiting** | ✅ Built-in | ✅ Component | ❌ Manual |
| **Security Headers** | ✅ Middleware | ✅ Component | ❌ Manual |

### Why It Works

Security approaches reflect framework completeness:
- **Laravel** provides comprehensive security out-of-the-box
- **Symfony** offers powerful security component with fine-grained control
- **Slim** requires manual implementation but gives you full control

---

## Section 15: API Documentation Tools

### Goal

Compare tools and approaches for API documentation generation.

### Laravel API Resources

Laravel uses API Resources for structured responses:

```php
# filename: app/Http/Resources/UserResource.php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at->toIso8601String(),
            'links' => [
                'self' => route('users.show', $this->id),
            ],
        ];
    }
}

// Usage in controller
return new UserResource($user);
return UserResource::collection($users);

// API Documentation (Laravel API Documentation packages)
composer require darkaonline/l5-swagger
// Generates OpenAPI/Swagger docs from annotations
```

**Laravel API Documentation:**
- API Resources (response transformation)
- L5-Swagger (OpenAPI/Swagger)
- Laravel API Documentation generators
- Manual documentation

### Symfony API Platform

Symfony has API Platform for REST APIs with auto-documentation:

```php
# filename: src/Entity/User.php
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;

#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
    ]
)]
class User
{
    #[ApiProperty(description: 'User ID')]
    private int $id;
    
    #[ApiProperty(description: 'User email address')]
    private string $email;
}

// Auto-generates OpenAPI documentation at /api/docs
// Includes interactive Swagger UI
```

**Symfony API Documentation:**
- API Platform (auto-documentation)
- NelmioApiDocBundle (OpenAPI/Swagger)
- Manual documentation
- OpenAPI/Swagger generation

### Slim API Documentation

Slim requires manual documentation or libraries:

```php
# Install Swagger-PHP
composer require zircote/swagger-php

# filename: src/Controller/UserController.php
/**
 * @OA\Get(
 *     path="/api/users/{id}",
 *     summary="Get user by ID",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User found",
 *         @OA\JsonContent(ref="#/components/schemas/User")
 *     )
 * )
 */
public function getUser($request, $response, $args)
{
    // Implementation
}
```

**Slim API Documentation Options:**
- Swagger-PHP (OpenAPI annotations)
- Manual documentation
- Custom solutions
- Any PSR-compliant documentation tool

### Comparison Table

| Feature | Laravel | Symfony | Slim |
|---------|---------|---------|------|
| **API Resources** | ✅ Built-in | ✅ (API Platform) | ❌ |
| **Auto-Documentation** | ❌ (Packages) | ✅ (API Platform) | ❌ |
| **OpenAPI/Swagger** | ✅ (L5-Swagger) | ✅ (Nelmio) | ✅ (Swagger-PHP) |
| **Interactive UI** | ✅ (Packages) | ✅ (API Platform) | ✅ (Swagger-PHP) |
| **Learning Curve** | Easy | Medium | Medium |

### Why It Works

API documentation approaches vary:
- **Laravel** provides API Resources for consistent responses, documentation via packages
- **Symfony** offers API Platform with automatic OpenAPI generation
- **Slim** requires manual setup but supports standard documentation tools

---

## Section 16: Deployment and DevOps

### Goal

Compare deployment strategies and DevOps tooling.

### Laravel Deployment

Laravel has deployment optimizations:

```bash
# Production optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Environment setup
cp .env.example .env
php artisan key:generate

# Queue workers (Supervisor)
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=8

# Laravel Forge / Envoyer (managed deployment)
# Automated deployments, zero-downtime, rollbacks
```

**Laravel Deployment Features:**
- Artisan optimization commands
- Laravel Forge (managed hosting)
- Laravel Envoyer (zero-downtime deployment)
- Queue worker management
- Horizon (queue monitoring)
- Vapor (serverless)

### Symfony Deployment

Symfony provides deployment tools:

```bash
# Production optimizations
composer install --no-dev --optimize-autoloader
php bin/console cache:clear --env=prod --no-debug
php bin/console cache:warmup --env=prod

# Environment variables
# Use .env.local for production secrets

# Symfony Cloud (managed hosting)
symfony deploy

# Docker deployment
docker-compose up -d
```

**Symfony Deployment Features:**
- Console optimization commands
- Symfony Cloud (managed hosting)
- Docker support
- Flex recipes for deployment tools
- Environment-based configuration

### Slim Deployment

Slim deployment is manual but flexible:

```bash
# Production setup
composer install --no-dev --optimize-autoloader

# Environment variables
# Use .env or system environment variables

# Docker deployment
FROM php:8.4-fpm
COPY . /var/www/html
RUN composer install --no-dev --optimize-autoloader

# Process managers (PM2, Supervisor)
pm2 start php --name="slim-api" -- public/index.php
```

**Slim Deployment Options:**
- Manual optimization
- Docker containers
- Process managers (PM2, Supervisor)
- Any PHP hosting
- Maximum flexibility

### Comparison Table

| Feature | Laravel | Symfony | Slim |
|---------|---------|---------|------|
| **Optimization Commands** | ✅ Built-in | ✅ Built-in | ❌ Manual |
| **Managed Hosting** | ✅ Forge/Envoyer | ✅ Symfony Cloud | ❌ |
| **Docker Support** | ✅ | ✅ | ✅ |
| **Zero-Downtime** | ✅ (Envoyer) | ✅ (Cloud) | ❌ Manual |
| **Queue Management** | ✅ Built-in | ✅ (Messenger) | ❌ Manual |
| **Deployment Tools** | ✅ Extensive | ✅ Good | ❌ Manual |

### Why It Works

Deployment approaches reflect framework ecosystem:
- **Laravel** provides comprehensive deployment tools and managed hosting
- **Symfony** offers solid deployment options with Symfony Cloud
- **Slim** requires manual setup but works with any deployment strategy

---

## Section 17: Ecosystem and Community

### Goal

Evaluate community support, packages, and learning resources.

### Laravel Ecosystem

**Official Packages:**

- Laravel Horizon (queue monitoring)
- Laravel Nova (admin panel)
- Laravel Spark (SaaS scaffolding)
- Laravel Vapor (serverless deployment)
- Laravel Forge (server management)
- Laravel Envoyer (zero-downtime deployment)

**Community Packages:**

- Laravel Debugbar (debugging toolbar)
- Laravel Telescope (application debugging)
- Spatie packages (many utilities)
- Laravel Sanctum (API authentication)
- Laravel Passport (OAuth server)

**Learning Resources:**

- Official documentation (excellent)
- Laracasts (video tutorials)
- Laravel News (blog)
- Laravel Podcast
- Large community on GitHub/Discord

### Symfony Ecosystem

**Official Components:**

- Symfony Components (standalone)
- Symfony Flex (recipe system)
- Symfony Maker Bundle (code generation)
- Symfony Debug Toolbar
- Symfony Profiler

**Community Packages:**

- FOSUserBundle (user management)
- SonataAdminBundle (admin panel)
- Doctrine Extensions (behaviors)
- Many enterprise packages

**Learning Resources:**

- Official documentation (comprehensive)
- SymfonyCasts (video tutorials)
- Symfony Blog
- SymfonyCon (conference)
- Strong enterprise community

### Slim Ecosystem

**Official Packages:**

- Slim Framework (core)
- Slim PSR-7 (HTTP messages)
- Slim Twig View (Twig integration)
- Slim PHP View (PHP templates)

**Community Packages:**

- Slim CSRF (CSRF protection)
- Slim JWT Auth (JWT authentication)
- Many PSR-compliant packages

**Learning Resources:**

- Official documentation (good)
- Community tutorials
- GitHub examples
- Smaller but active community

### Comparison Table

| Aspect | Laravel | Symfony | Slim |
|--------|---------|---------|------|
| **Package Count** | Very High | High | Medium |
| **Documentation** | Excellent | Excellent | Good |
| **Community Size** | Largest | Large | Medium |
| **Learning Resources** | Extensive | Extensive | Moderate |
| **Enterprise Support** | Good | Excellent | Limited |
| **Job Market** | High demand | High demand | Moderate |

### Why It Works

Ecosystem reflects framework maturity and adoption:

- **Laravel** has the largest ecosystem with extensive packages and learning resources
- **Symfony** offers enterprise-grade components and strong corporate support
- **Slim** has a smaller but focused ecosystem, perfect for specific use cases

---

## Section 13: Framework Selection Guide

### Goal

Create a decision framework to choose the right tool for your project.

### Decision Matrix

**Choose Laravel if:**

- ✅ Rapid prototyping and MVP development
- ✅ Standard web application (CRUD, auth, etc.)
- ✅ Team prefers convention over configuration
- ✅ Need built-in features (queues, scheduling, etc.)
- ✅ Learning PHP or new to frameworks
- ✅ Startup or small-to-medium projects
- ✅ Similar to: Spring Boot for rapid development

**Choose Symfony if:**

- ✅ Enterprise application with complex requirements
- ✅ Need fine-grained control over architecture
- ✅ Want to use only specific components
- ✅ Building microservices or distributed systems
- ✅ Team values explicit configuration
- ✅ Long-term maintainability is critical
- ✅ Similar to: Spring Framework for flexibility

**Choose Slim if:**

- ✅ Building REST APIs or microservices
- ✅ Need maximum performance
- ✅ Want minimal dependencies
- ✅ Learning HTTP fundamentals
- ✅ Simple application requirements
- ✅ Prefer building custom solutions
- ✅ Similar to: Micronaut/Quarkus for minimal overhead

### Project Type Recommendations

| Project Type | Recommended Framework | Reason |
|--------------|----------------------|--------|
| **Startup MVP** | Laravel | Rapid development, built-in features |
| **Enterprise App** | Symfony | Flexibility, scalability, components |
| **REST API** | Slim or Laravel | Performance vs convenience |
| **Microservices** | Slim or Symfony | Lightweight vs flexibility |
| **Learning PHP** | Slim or Laravel | Simplicity vs features |
| **Legacy Migration** | Symfony | Component-based, gradual migration |

### Team Considerations

**Small Team (1-3 developers):**

- Laravel: Fastest to productivity
- Slim: Maximum control, minimal overhead
- Symfony: Steeper learning curve

**Medium Team (4-10 developers):**

- Laravel: Good balance
- Symfony: Better for complex projects
- Slim: Good for focused APIs

**Large Team (10+ developers):**

- Symfony: Best for enterprise scale
- Laravel: Good with proper structure
- Slim: Good for microservices teams

### Migration Paths

**From Java Spring Boot:**

- Laravel: Easiest transition (similar conventions)
- Symfony: Similar architecture (component-based)
- Slim: Most different (minimal framework)

**Between PHP Frameworks:**

- Laravel ↔ Symfony: Moderate effort (different patterns)
- Any → Slim: Easy (Slim is minimal)
- Slim → Laravel/Symfony: Moderate (add features)

### Why It Works

Framework selection depends on multiple factors:

- **Project requirements** determine feature needs
- **Team experience** affects learning curve
- **Performance needs** influence framework choice
- **Long-term maintenance** requires considering ecosystem and support

---

## Exercises

### Exercise 1: Framework Comparison Matrix

**Goal**: Create a detailed comparison document to guide framework selection.

Create a markdown file `framework-comparison.md` with:

- Feature comparison table (routing, ORM, DI, templating)
- Performance benchmarks (research and document)
- Use case recommendations
- Migration considerations from Java frameworks

**Validation**: Your document should help a Java developer choose the right PHP framework.

### Exercise 2: Simple API in Each Framework

**Goal**: Build the same REST API in Laravel, Symfony, and Slim to compare developer experience.

Create a simple user management API with:

- GET `/users` - List all users
- GET `/users/{id}` - Get user by ID
- POST `/users` - Create user
- PUT `/users/{id}` - Update user
- DELETE `/users/{id}` - Delete user

**Requirements:**

- Use each framework's ORM/database layer
- Implement basic validation
- Return JSON responses
- Time yourself for each implementation

**Validation**: Compare:
- Lines of code
- Development time
- Code readability
- Performance (simple benchmark)

### Exercise 3: Framework Selection Scenario

**Goal**: Practice making framework decisions based on project requirements.

For each scenario, choose a framework and justify your choice:

1. **E-commerce startup**: Need to launch MVP in 2 months, team of 3 developers
2. **Enterprise CRM**: Complex business logic, 20+ developers, long-term project
3. **Mobile app backend**: REST API only, high traffic expected, minimal features
4. **Legacy system migration**: Gradual migration from old PHP codebase
5. **Learning project**: Developer new to PHP, wants to understand fundamentals

**Validation**: Write a brief justification (2-3 sentences) for each choice.

---

## Troubleshooting

### Issue: "Class not found" in Laravel

**Symptom**: `Class 'App\Models\User' not found`

**Cause**: Autoloader not updated after creating new class

**Solution**:

```bash
# Regenerate autoloader
composer dump-autoload

# Or clear all caches
php artisan optimize:clear
```

### Issue: Symfony service not found

**Symptom**: `Service "App\Service\UserService" not found`

**Cause**: Service not registered or autowiring disabled

**Solution**:

```yaml
# config/services.yaml
services:
    App\:
        resource: '../src/*'
        autowire: true
        autoconfigure: true
```

### Issue: Slim route not working

**Symptom**: 404 error for valid route

**Cause**: Route not registered or wrong HTTP method

**Solution**:

```php
// Check route registration
$app->get('/users', function ($request, $response) {
    // Make sure this matches your request method
});

// Enable error display
$app->addErrorMiddleware(true, true, true);
```

### Issue: Performance issues in Laravel

**Symptom**: Slow response times

**Cause**: Not using production optimizations

**Solution**:

```bash
# Enable production optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Enable opcache in php.ini
opcache.enable=1
```

### Issue: Doctrine query performance

**Symptom**: Slow database queries, especially with relationships

**Cause**: N+1 query problem or missing indexes

**Solution**:

```php
# filename: src/Repository/UserRepository.php
// Use eager loading to prevent N+1 queries
public function findAllWithPosts(): array
{
    return $this->createQueryBuilder('u')
        ->leftJoin('u.posts', 'p')
        ->addSelect('p')
        ->getQuery()
        ->getResult();
}

// Add indexes to entity
#[ORM\Entity]
#[ORM\Table(name: 'users', indexes: [
    new ORM\Index(columns: ['email']),
    new ORM\Index(columns: ['active', 'created_at'])
])]
class User { }
```

### Issue: Slim container not resolving dependencies

**Symptom**: `Service not found` or `Cannot resolve dependency`

**Cause**: Service not registered in container or autowiring not enabled

**Solution**:

```php
# filename: public/index.php
// Ensure PHP-DI autowiring is enabled
$containerBuilder = new ContainerBuilder();
$containerBuilder->useAutowiring(true);
$containerBuilder->useAnnotations(false);

// Or explicitly define services
$containerBuilder->addDefinitions([
    UserRepository::class => \DI\autowire(UserRepository::class),
    UserService::class => \DI\autowire(UserService::class),
]);
```

### Issue: Route not matching in any framework

**Symptom**: 404 error even though route is defined

**Cause**: Route caching, wrong HTTP method, or path mismatch

**Solution**:

```bash
# Laravel: Clear route cache
php artisan route:clear
php artisan route:cache  # Rebuild cache

# Symfony: Clear cache
php bin/console cache:clear

# Check routes
php artisan route:list  # Laravel
php bin/console debug:router  # Symfony

# Slim: Check route registration order
# Routes are matched in order - more specific routes first
```

---

## Wrap-up

Congratulations! You've completed a comprehensive comparison of PHP's major frameworks. Here's what you've accomplished:

- ✅ **Compared framework philosophies** and understood their design goals
- ✅ **Evaluated routing systems** across Laravel, Symfony, and Slim
- ✅ **Analyzed ORM capabilities** (Eloquent, Doctrine, PDO)
- ✅ **Compared dependency injection** implementations
- ✅ **Reviewed templating engines** (Blade, Twig, plain PHP)
- ✅ **Explored middleware and request processing** pipelines
- ✅ **Compared validation systems** and input handling
- ✅ **Analyzed queue and job processing** capabilities
- ✅ **Explored CLI tools** and developer experience
- ✅ **Assessed performance characteristics** and scalability
- ✅ **Compared testing framework integration** (PHPUnit)
- ✅ **Evaluated caching strategies** and implementations
- ✅ **Reviewed security features** and best practices
- ✅ **Compared API documentation tools** and approaches
- ✅ **Analyzed deployment and DevOps** strategies
- ✅ **Evaluated ecosystems** and community support
- ✅ **Created decision framework** for choosing the right tool

### Key Takeaways

**Laravel** is like Spring Boot:
- Convention over configuration
- Rapid development
- Batteries included
- Great for standard web apps

**Symfony** is like Spring Framework:
- Flexible and modular
- Enterprise-grade
- Component-based
- Great for complex systems

**Slim** is like Micronaut/Quarkus:
- Minimal and lightweight
- High performance
- Perfect for APIs
- Maximum control

### Next Steps

In the next chapter, we'll dive deep into **Laravel Fundamentals**, exploring:
- Eloquent ORM in detail
- Blade templating
- Artisan commands
- Laravel's service container
- Building a complete Laravel application

---

<ChapterCheckbox 
  seriesId="php-for-java-developers"
  chapterId="19"
  label="Completed framework comparison and selection guide!"
/>

---

## Further Reading

- [Laravel Documentation](https://laravel.com/docs) — Comprehensive Laravel guide
- [Symfony Documentation](https://symfony.com/doc/current/index.html) — Complete Symfony reference
- [Slim Framework Documentation](https://www.slimframework.com/docs/v4/) — Slim framework guide
- [PHP The Right Way](https://phptherightway.com/) — PHP best practices
- [Framework Benchmarks](https://www.techempower.com/benchmarks/) — Performance comparisons
- [PSR Standards](https://www.php-fig.org/psr/) — PHP Framework Interop Group standards

---

## Chapter Wrap-up Checklist

Before moving to the next chapter, ensure you can:

- [ ] Explain the philosophy and use cases for Laravel, Symfony, and Slim
- [ ] Compare routing systems across frameworks
- [ ] Evaluate ORM options (Eloquent, Doctrine, PDO)
- [ ] Understand dependency injection in each framework
- [ ] Compare templating engines (Blade, Twig, plain PHP)
- [ ] Assess performance characteristics and optimization strategies
- [ ] Evaluate ecosystem and community support
- [ ] Make informed framework selection decisions based on project requirements
- [ ] Draw parallels between PHP frameworks and Java frameworks (Spring Boot, Spring Framework, Micronaut)

---

<div style="display: flex; justify-content: space-between; margin-top: 2rem;">
  <div>
    <strong>Previous:</strong> <a href="/series/php-for-java-developers/chapters/18-security-best-practices">← Chapter 18</a>
  </div>
  <div><strong>Next:</strong> <a href="/series/php-for-java-developers/chapters/20-laravel-fundamentals">Chapter 20 →</a></div>
</div>
