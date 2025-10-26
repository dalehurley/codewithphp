<?php

declare(strict_types=1);

/**
 * Exercise 2: Create a Post Model and Controller
 * 
 * Build out the Model layer of MVC by creating a Post model and displaying posts.
 * 
 * Requirements:
 * - Post model with static all() method
 * - PostController with index() method
 * - View to display all posts
 * - Route registration for /posts
 * 
 * This demonstrates the full MVC pattern with Model, View, and Controller.
 */

// ============================================================================
// src/Models/Post.php
// ============================================================================

namespace App\Models;

class Post
{
    /**
     * Get all blog posts
     * 
     * In a real application, this would query a database.
     * For now, we return hardcoded data.
     * 
     * @return array Array of post data
     */
    public static function all(): array
    {
        return [
            [
                'id' => 1,
                'title' => 'Getting Started with PHP 8.4',
                'content' => 'PHP 8.4 introduces amazing new features like property hooks and asymmetric visibility. These features make PHP more expressive and easier to work with.',
                'date' => '2024-03-15',
                'author' => 'John Doe'
            ],
            [
                'id' => 2,
                'title' => 'Building Modern Web Applications',
                'content' => 'Modern web development requires understanding of MVC architecture, routing, and database interactions. This post explores these concepts in depth.',
                'date' => '2024-03-20',
                'author' => 'Jane Smith'
            ],
            [
                'id' => 3,
                'title' => 'Understanding Object-Oriented Programming',
                'content' => 'OOP is a fundamental programming paradigm that helps organize code into reusable, maintainable structures. Learn about classes, inheritance, and polymorphism.',
                'date' => '2024-03-25',
                'author' => 'Bob Wilson'
            ],
            [
                'id' => 4,
                'title' => 'Database Design Best Practices',
                'content' => 'Proper database design is crucial for application performance and scalability. This article covers normalization, indexing, and query optimization.',
                'date' => '2024-03-30',
                'author' => 'Alice Johnson'
            ],
            [
                'id' => 5,
                'title' => 'Security in PHP Applications',
                'content' => 'Web security is paramount. Learn about SQL injection prevention, XSS protection, CSRF tokens, and other essential security practices for PHP developers.',
                'date' => '2024-04-05',
                'author' => 'Charlie Brown'
            ]
        ];
    }

    /**
     * Get a single post by ID
     * 
     * @param int $id Post ID
     * @return array|null Post data or null if not found
     */
    public static function find(int $id): ?array
    {
        $posts = self::all();

        foreach ($posts as $post) {
            if ($post['id'] === $id) {
                return $post;
            }
        }

        return null;
    }

    /**
     * Get recent posts (limit)
     * 
     * @param int $limit Number of posts to return
     * @return array
     */
    public static function recent(int $limit = 3): array
    {
        $posts = self::all();
        return array_slice($posts, 0, $limit);
    }
}

// ============================================================================
// src/Controllers/PostController.php
// ============================================================================

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Post;

class PostController extends Controller
{
    /**
     * Display all blog posts
     */
    public function index(): void
    {
        $posts = Post::all();

        $this->view('posts/index', [
            'posts' => $posts,
            'title' => 'Blog Posts'
        ]);
    }

    /**
     * Display a single post
     */
    public function show(string $id): void
    {
        $post = Post::find((int) $id);

        if ($post === null) {
            $this->view('404', [
                'message' => 'Post not found'
            ]);
            return;
        }

        $this->view('posts/show', [
            'post' => $post,
            'title' => $post['title']
        ]);
    }
}

// ============================================================================
// Example: src/Views/posts/index.php
// ============================================================================

function renderPostsIndexView(array $posts, string $title): string
{
    ob_start();
?>
    <!DOCTYPE html>
    <html>

    <head>
        <title><?php echo htmlspecialchars($title); ?></title>
        <style>
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                max-width: 900px;
                margin: 50px auto;
                padding: 0 20px;
                background: #f5f5f5;
            }

            h1 {
                color: #2c3e50;
                border-bottom: 3px solid #3498db;
                padding-bottom: 10px;
            }

            .post-list {
                display: grid;
                gap: 20px;
            }

            .post-card {
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                transition: transform 0.2s, box-shadow 0.2s;
            }

            .post-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }

            .post-title {
                color: #2c3e50;
                margin-top: 0;
                font-size: 1.5em;
            }

            .post-meta {
                color: #7f8c8d;
                font-size: 0.9em;
                margin-bottom: 15px;
            }

            .post-content {
                color: #555;
                line-height: 1.6;
            }

            .read-more {
                display: inline-block;
                margin-top: 15px;
                color: #3498db;
                text-decoration: none;
                font-weight: bold;
            }

            .read-more:hover {
                text-decoration: underline;
            }

            .post-count {
                background: #ecf0f1;
                padding: 15px;
                border-radius: 4px;
                margin-bottom: 30px;
                text-align: center;
                color: #2c3e50;
            }
        </style>
    </head>

    <body>
        <h1><?php echo htmlspecialchars($title); ?></h1>

        <div class="post-count">
            Showing <?php echo count($posts); ?> blog post<?php echo count($posts) !== 1 ? 's' : ''; ?>
        </div>

        <div class="post-list">
            <?php foreach ($posts as $post): ?>
                <article class="post-card">
                    <h2 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h2>
                    <div class="post-meta">
                        By <?php echo htmlspecialchars($post['author']); ?>
                        on <?php echo date('F j, Y', strtotime($post['date'])); ?>
                    </div>
                    <p class="post-content"><?php echo htmlspecialchars($post['content']); ?></p>
                    <a href="/posts/<?php echo $post['id']; ?>" class="read-more">Read More →</a>
                </article>
            <?php endforeach; ?>
        </div>
    </body>

    </html>
<?php
    return ob_get_clean();
}

// ============================================================================
// Example: public/index.php (route registration)
// ============================================================================

echo "=== Post Model and Controller Demo ===" . PHP_EOL . PHP_EOL;

echo "✓ Created Post Model (src/Models/Post.php) with:" . PHP_EOL;
echo "  - all() method returning array of posts" . PHP_EOL;
echo "  - find(\$id) method for single posts" . PHP_EOL;
echo "  - recent(\$limit) method for latest posts" . PHP_EOL . PHP_EOL;

echo "✓ Created PostController (src/Controllers/PostController.php) with:" . PHP_EOL;
echo "  - index() method to display all posts" . PHP_EOL;
echo "  - show(\$id) method to display single post" . PHP_EOL . PHP_EOL;

echo "✓ Created View (src/Views/posts/index.php) that:" . PHP_EOL;
echo "  - Loops through all posts" . PHP_EOL;
echo "  - Displays post title, author, date, and content" . PHP_EOL;
echo "  - Includes styling for better UX" . PHP_EOL . PHP_EOL;

echo "✓ Route registration example:" . PHP_EOL;
echo "  \$router->get('/posts', [PostController::class, 'index']);" . PHP_EOL;
echo "  \$router->get('/posts/{id}', [PostController::class, 'show']);" . PHP_EOL . PHP_EOL;

// Demonstrate the Post model
$posts = Post::all();
echo "Sample posts from Post::all():" . PHP_EOL;
foreach ($posts as $post) {
    echo "  - {$post['title']} by {$post['author']}" . PHP_EOL;
}

echo PHP_EOL . "This demonstrates a complete MVC implementation:" . PHP_EOL;
echo "  Model: Post class manages data" . PHP_EOL;
echo "  View: posts/index.php displays data" . PHP_EOL;
echo "  Controller: PostController coordinates Model and View" . PHP_EOL;
