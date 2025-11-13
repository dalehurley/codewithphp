---
title: "00: Computational Thinking and Problem Solving"
description: "Learn to think like a computer scientist. Understand abstraction, decomposition, pattern recognition, and algorithmic thinking—the fundamental problem-solving approaches that power all of computer science."
series: "computer-science"
chapter: 0
order: 0
difficulty: "Intermediate"
prerequisites: ["Basic programming knowledge", "Understanding of functions and loops"]
---

# Chapter 00: Computational Thinking and Problem Solving

## Introduction

Before we dive into algorithms and data structures, we need to develop a fundamental skill that separates great programmers from the rest: **computational thinking**. This isn't about memorizing syntax or learning a specific technology—it's about developing a systematic approach to solving problems that transfers to any programming language or domain.

Computational thinking is the mental process that allows you to break down complex problems into manageable pieces, recognize patterns, and design step-by-step solutions. It's how computer scientists approach challenges, and it's a skill you can apply far beyond coding.

In this chapter, you'll learn:

- What computational thinking is and why it matters
- The four pillars of computational thinking
- How to apply these principles to real-world problems
- Practical examples in PHP

## What is Computational Thinking?

**Computational thinking** is a problem-solving approach that involves:

1. **Decomposition** — Breaking complex problems into smaller, manageable parts
2. **Pattern Recognition** — Identifying similarities and patterns in problems
3. **Abstraction** — Focusing on relevant information while ignoring irrelevant details
4. **Algorithm Design** — Creating step-by-step solutions

These four pillars form the foundation of how computer scientists think. Let's explore each one.

## Pillar 1: Decomposition

**Decomposition** is the art of breaking a large, complex problem into smaller sub-problems that are easier to solve.

### Example: Building a Blog

Imagine you need to build a blog application. This feels overwhelming at first, but decomposition helps:

```
Blog Application
│
├── User Authentication
│   ├── Registration
│   ├── Login
│   └── Password Reset
│
├── Post Management
│   ├── Create Post
│   ├── Edit Post
│   ├── Delete Post
│   └── View Posts
│
├── Comment System
│   ├── Add Comment
│   ├── Delete Comment
│   └── Moderate Comments
│
└── Admin Panel
    ├── User Management
    └── Content Moderation
```

By decomposing the problem, we've transformed "build a blog" into 12+ specific, actionable tasks.

### Decomposition in Code

Here's how decomposition looks in PHP:

```php
<?php

// Instead of one massive function:
function buildEntireBlog() {
    // 500 lines of tangled logic...
}

// We decompose it into smaller, focused functions:
function authenticateUser(string $username, string $password): bool {
    // Handle authentication logic
    return true;
}

function createPost(string $title, string $content, int $authorId): int {
    // Handle post creation
    return 1; // post ID
}

function addComment(int $postId, int $userId, string $content): void {
    // Handle comment addition
}

// Now the main logic becomes clear:
function handleUserRequest(array $request): void {
    if ($request['action'] === 'login') {
        authenticateUser($request['username'], $request['password']);
    } elseif ($request['action'] === 'create_post') {
        createPost($request['title'], $request['content'], $request['author_id']);
    } elseif ($request['action'] === 'add_comment') {
        addComment($request['post_id'], $request['user_id'], $request['content']);
    }
}
```

**Key Principle**: Each function should do one thing well. If a function is doing multiple things, decompose it further.

## Pillar 2: Pattern Recognition

**Pattern recognition** involves identifying similarities between problems you've solved before and the current challenge.

### Example: CRUD Operations

Once you've built "Create, Read, Update, Delete" functionality for blog posts, you'll recognize the same pattern applies to:

- User accounts
- Comments
- Categories
- Products in an e-commerce site

The pattern is the same; only the data changes.

### Pattern Recognition in Code

```php
<?php

// Pattern: Generic CRUD operations
interface CrudInterface {
    public function create(array $data): int;
    public function read(int $id): ?array;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}

// Apply the pattern to different entities
class PostRepository implements CrudInterface {
    public function create(array $data): int {
        // Insert post into database
        return 1; // post ID
    }

    public function read(int $id): ?array {
        // Fetch post from database
        return ['id' => $id, 'title' => 'Example'];
    }

    public function update(int $id, array $data): bool {
        // Update post in database
        return true;
    }

    public function delete(int $id): bool {
        // Delete post from database
        return true;
    }
}

class UserRepository implements CrudInterface {
    // Same methods, different data source
    public function create(array $data): int { /* ... */ return 1; }
    public function read(int $id): ?array { /* ... */ return []; }
    public function update(int $id, array $data): bool { /* ... */ return true; }
    public function delete(int $id): bool { /* ... */ return true; }
}
```

**Key Principle**: When you encounter a new problem, ask yourself: "Have I solved something similar before?"

## Pillar 3: Abstraction

**Abstraction** means focusing on the essential details while hiding complexity. It's about creating simple interfaces for complex operations.

### Example: Database Abstraction

You don't need to know how MySQL stores data on disk to use it. The database provides an abstract interface (SQL queries) that hides internal complexity.

### Abstraction in Code

```php
<?php

// Without abstraction: Complex database operations scattered everywhere
$conn = new PDO('mysql:host=localhost;dbname=blog', 'user', 'pass');
$stmt = $conn->prepare('SELECT * FROM posts WHERE id = :id');
$stmt->execute(['id' => 1]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

// With abstraction: Simple, reusable interface
class Database {
    private PDO $connection;

    public function __construct(string $dsn, string $user, string $pass) {
        $this->connection = new PDO($dsn, $user, $pass);
    }

    public function query(string $sql, array $params = []): array {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Now the calling code is simple and clear
$db = new Database('mysql:host=localhost;dbname=blog', 'user', 'pass');
$post = $db->query('SELECT * FROM posts WHERE id = :id', ['id' => 1]);
```

**Key Principle**: Hide complexity behind simple interfaces. Users shouldn't need to understand implementation details.

## Pillar 4: Algorithm Design

**Algorithm design** is creating step-by-step instructions to solve a problem. It's the culmination of decomposition, pattern recognition, and abstraction.

### Example: Search Algorithm

Let's design an algorithm to find a specific post by title:

**Step 1: Decompose the problem**
- Read the list of posts
- Compare each post title to the search term
- Return the matching post

**Step 2: Recognize patterns**
- This is a linear search pattern
- We've used this before when filtering arrays

**Step 3: Abstract**
- Create a generic search function
- Make it work for any data, not just posts

**Step 4: Implement**

```php
<?php

// Algorithm: Linear search
function findByTitle(array $posts, string $searchTerm): ?array {
    // Step 1: Iterate through all posts
    foreach ($posts as $post) {
        // Step 2: Compare title (case-insensitive)
        if (strcasecmp($post['title'], $searchTerm) === 0) {
            // Step 3: Return matching post
            return $post;
        }
    }

    // Step 4: No match found
    return null;
}

// Test the algorithm
$posts = [
    ['id' => 1, 'title' => 'Intro to PHP'],
    ['id' => 2, 'title' => 'Advanced OOP'],
    ['id' => 3, 'title' => 'Database Design'],
];

$result = findByTitle($posts, 'Advanced OOP');
var_dump($result); // Returns post with id 2
```

**Key Principle**: Break the solution into clear, sequential steps. Each step should be simple and unambiguous.

## Putting It All Together: A Real Example

Let's solve a practical problem using all four pillars of computational thinking.

**Problem**: Build a system to validate and process user registration forms.

### Step 1: Decomposition

Break the problem into smaller tasks:

1. Validate email format
2. Check if username is unique
3. Validate password strength
4. Hash the password
5. Store user in database
6. Send welcome email

### Step 2: Pattern Recognition

We've validated forms before. The pattern is:
- Check each field
- Collect errors
- Return success or failure

### Step 3: Abstraction

Create a `Validator` class that hides validation complexity.

### Step 4: Algorithm Design

```php
<?php

class UserValidator {
    private array $errors = [];

    public function validate(array $data): bool {
        $this->errors = [];

        // Validate email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'Invalid email format';
        }

        // Validate password strength
        if (strlen($data['password']) < 8) {
            $this->errors[] = 'Password must be at least 8 characters';
        }

        // Validate username uniqueness
        if ($this->usernameExists($data['username'])) {
            $this->errors[] = 'Username already taken';
        }

        return empty($this->errors);
    }

    public function getErrors(): array {
        return $this->errors;
    }

    private function usernameExists(string $username): bool {
        // Check database for existing username
        return false; // Placeholder
    }
}

class UserRegistration {
    private UserValidator $validator;

    public function __construct(UserValidator $validator) {
        $this->validator = $validator;
    }

    public function register(array $data): bool {
        // Step 1: Validate input
        if (!$this->validator->validate($data)) {
            echo "Validation failed:\n";
            print_r($this->validator->getErrors());
            return false;
        }

        // Step 2: Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);

        // Step 3: Store user (abstracted)
        $this->storeUser($data['username'], $data['email'], $hashedPassword);

        // Step 4: Send welcome email (abstracted)
        $this->sendWelcomeEmail($data['email']);

        return true;
    }

    private function storeUser(string $username, string $email, string $password): void {
        // Database logic hidden
    }

    private function sendWelcomeEmail(string $email): void {
        // Email sending logic hidden
    }
}

// Usage
$validator = new UserValidator();
$registration = new UserRegistration($validator);

$result = $registration->register([
    'username' => 'johndoe',
    'email' => 'john@example.com',
    'password' => 'SecurePass123'
]);
```

## Practice: Applying Computational Thinking

Let's practice with a challenge:

**Challenge**: Design a system that recommends blog posts to users based on their reading history.

Before jumping into code, apply computational thinking:

1. **Decomposition**: What are the sub-problems?
   - Track user reading history
   - Calculate similarity between posts
   - Rank recommendations
   - Return top N results

2. **Pattern Recognition**: What patterns apply?
   - This is a filtering/ranking problem
   - Similar to e-commerce recommendation systems

3. **Abstraction**: What can we hide?
   - Similarity calculation algorithm
   - Database queries
   - Scoring logic

4. **Algorithm Design**: What are the steps?
   - Fetch user's reading history
   - Find posts user hasn't read
   - Score each post based on similarity
   - Sort by score
   - Return top results

Now you can start coding with a clear plan!

## Key Takeaways

- **Computational thinking** is a problem-solving methodology, not a programming technique
- **Decomposition** breaks complex problems into manageable pieces
- **Pattern recognition** helps you leverage previous experience
- **Abstraction** hides complexity behind simple interfaces
- **Algorithm design** creates step-by-step solutions

These principles apply to every aspect of software development, from writing a single function to architecting a distributed system.

## Exercises

1. **Decomposition Practice**: Break down the problem "Build a todo list app" into 15+ specific tasks.

2. **Pattern Recognition**: Identify the pattern in these three problems:
   - Validating user registration forms
   - Validating product data in e-commerce
   - Validating API request payloads

3. **Abstraction Challenge**: Take the following code and abstract it into a reusable class:
```php
$email = $_POST['email'];
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Invalid email');
}
$name = trim($_POST['name']);
if (strlen($name) < 2) {
    die('Name too short');
}
```

4. **Algorithm Design**: Design an algorithm (in pseudocode or PHP) that finds duplicate values in an array.

## What's Next?

Now that you understand how to think computationally, we'll apply these principles to analyze algorithms in Chapter 01: Algorithm Analysis and Big O Notation. You'll learn how to measure efficiency and compare solutions objectively.

---

**Further Reading**:
- [Computational Thinking - Carnegie Mellon](https://www.cs.cmu.edu/~15110-s13/Wing06-ct.pdf)
- [Thinking Computationally - Google for Education](https://edu.google.com/resources/programs/exploring-computational-thinking/)
