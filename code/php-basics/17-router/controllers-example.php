<?php

declare(strict_types=1);

/**
 * Router with Controller Classes
 * 
 * Demonstrates organizing route handlers into controller classes.
 */

require_once __DIR__ . '/Router.php';

// Controller classes
class HomeController
{
    public function index(): void
    {
        echo "<h1>Home Page</h1>";
        echo "<p>Using controller method!</p>";
    }

    public function about(): void
    {
        echo "<h1>About Page</h1>";
        echo "<p>This is the about page.</p>";
    }
}

class PostController
{
    public function index(): void
    {
        echo "<h1>All Posts</h1>";
        $posts = $this->getAllPosts();
        echo "<ul>";
        foreach ($posts as $post) {
            echo "<li><a href='/posts/{$post['id']}'>{$post['title']}</a></li>";
        }
        echo "</ul>";
    }

    public function show(string $id): void
    {
        $post = $this->getPost((int)$id);

        if ($post === null) {
            http_response_code(404);
            echo "<h1>Post Not Found</h1>";
            return;
        }

        echo "<h1>{$post['title']}</h1>";
        echo "<p>{$post['content']}</p>";
        echo "<p><small>By {$post['author']}</small></p>";
        echo '<p><a href="/posts">Back to all posts</a></p>';
    }

    public function create(): void
    {
        echo "<h1>Create New Post</h1>";
        echo '<form method="POST" action="/posts">';
        echo '<input type="text" name="title" placeholder="Title" required><br>';
        echo '<textarea name="content" placeholder="Content" required></textarea><br>';
        echo '<button type="submit">Create Post</button>';
        echo '</form>';
    }

    public function store(): void
    {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';

        // In real app, save to database
        echo "<h1>Post Created!</h1>";
        echo "<p>Title: " . htmlspecialchars($title) . "</p>";
        echo "<p>Content: " . htmlspecialchars($content) . "</p>";
    }

    private function getAllPosts(): array
    {
        // Simulate database
        return [
            ['id' => 1, 'title' => 'First Post', 'content' => 'Content 1', 'author' => 'Alice'],
            ['id' => 2, 'title' => 'Second Post', 'content' => 'Content 2', 'author' => 'Bob'],
            ['id' => 3, 'title' => 'Third Post', 'content' => 'Content 3', 'author' => 'Charlie']
        ];
    }

    private function getPost(int $id): ?array
    {
        $posts = $this->getAllPosts();
        foreach ($posts as $post) {
            if ($post['id'] === $id) {
                return $post;
            }
        }
        return null;
    }
}

class UserController
{
    public function profile(string $username): void
    {
        echo "<h1>User Profile</h1>";
        echo "<p>Username: " . htmlspecialchars($username) . "</p>";
        echo "<p>Email: {$username}@example.com</p>";
        echo "<p>Member since: 2024</p>";
    }
}

// Setup router with controllers
$router = new Router();

// Home routes
$homeController = new HomeController();
$router->get('/', [$homeController, 'index']);
$router->get('/about', [$homeController, 'about']);

// Post routes (RESTful style)
$postController = new PostController();
$router->get('/posts', [$postController, 'index']);
$router->get('/posts/create', [$postController, 'create']);
$router->post('/posts', [$postController, 'store']);
$router->get('/posts/{id}', [$postController, 'show']);

// User routes
$userController = new UserController();
$router->get('/users/{username}', [$userController, 'profile']);

// Dispatch
$router->dispatch();
