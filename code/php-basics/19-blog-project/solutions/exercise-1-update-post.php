<?php

declare(strict_types=1);

/**
 * Exercise 1: Implement Update (⭐⭐⭐)
 * 
 * Add the ability to edit existing posts.
 * 
 * Requirements:
 * - Add update() method to Post model
 * - Add edit() and updatePost() methods to PostController
 * - Create edit.php view
 * - Add routes for editing
 * - Add "Edit" link to post show page
 */

// ============================================================================
// Updated Post Model with update() method
// ============================================================================

class Post
{
    private static function getConnection(): PDO
    {
        $pdo = new PDO('sqlite:' . __DIR__ . '/blog.db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }

    /**
     * Update an existing post
     * 
     * @param int $id Post ID
     * @param string $title New title
     * @param string $content New content
     * @return bool Success status
     */
    public static function update(int $id, string $title, string $content): bool
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
        return $stmt->execute([$title, $content, $id]);
    }

    /**
     * Find a post by ID
     */
    public static function find(int $id): ?array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
        $stmt->execute([$id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        return $post ?: null;
    }

    /**
     * Get all posts
     */
    public static function all(): array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new post
     */
    public static function create(string $title, string $content): int
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("INSERT INTO posts (title, content) VALUES (?, ?)");
        $stmt->execute([$title, $content]);
        return (int) $pdo->lastInsertId();
    }
}

// ============================================================================
// Updated PostController with edit() and updatePost() methods
// ============================================================================

class PostController
{
    /**
     * Display edit form for a post
     */
    public function edit(string $id): void
    {
        $post = Post::find((int) $id);

        if ($post === null) {
            http_response_code(404);
            echo "Post not found";
            return;
        }

        $this->renderEditView($post);
    }

    /**
     * Handle post update submission
     */
    public function updatePost(string $id): void
    {
        $postId = (int) $id;
        $post = Post::find($postId);

        if ($post === null) {
            http_response_code(404);
            echo "Post not found";
            return;
        }

        // Validate input
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        $errors = [];

        if (empty($title)) {
            $errors[] = "Title is required";
        } elseif (strlen($title) < 3) {
            $errors[] = "Title must be at least 3 characters";
        }

        if (empty($content)) {
            $errors[] = "Content is required";
        } elseif (strlen($content) < 10) {
            $errors[] = "Content must be at least 10 characters";
        }

        if (!empty($errors)) {
            $this->renderEditView($post, $errors);
            return;
        }

        // Update the post
        $success = Post::update($postId, $title, $content);

        if ($success) {
            header("Location: /posts/{$postId}");
            exit;
        } else {
            $this->renderEditView($post, ["Failed to update post"]);
        }
    }

    /**
     * Render the edit view
     */
    private function renderEditView(array $post, array $errors = []): void
    {
?>
        <!DOCTYPE html>
        <html>

        <head>
            <title>Edit Post - <?php echo htmlspecialchars($post['title']); ?></title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    max-width: 800px;
                    margin: 50px auto;
                    padding: 0 20px;
                }

                h1 {
                    color: #2c3e50;
                }

                .error {
                    background: #fee;
                    border: 1px solid #fcc;
                    color: #c33;
                    padding: 15px;
                    border-radius: 4px;
                    margin-bottom: 20px;
                }

                .error ul {
                    margin: 10px 0 0 20px;
                }

                .form-group {
                    margin-bottom: 20px;
                }

                label {
                    display: block;
                    font-weight: bold;
                    margin-bottom: 5px;
                    color: #555;
                }

                input[type="text"],
                textarea {
                    width: 100%;
                    padding: 10px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    font-size: 14px;
                    font-family: Arial, sans-serif;
                }

                textarea {
                    min-height: 300px;
                    resize: vertical;
                }

                .button-group {
                    display: flex;
                    gap: 10px;
                }

                button,
                .cancel-link {
                    padding: 12px 24px;
                    font-size: 16px;
                    border-radius: 4px;
                    cursor: pointer;
                    text-decoration: none;
                    display: inline-block;
                }

                button {
                    background: #27ae60;
                    color: white;
                    border: none;
                }

                button:hover {
                    background: #229954;
                }

                .cancel-link {
                    background: #95a5a6;
                    color: white;
                }

                .cancel-link:hover {
                    background: #7f8c8d;
                }

                .info {
                    background: #e8f4f8;
                    padding: 10px 15px;
                    border-radius: 4px;
                    margin-bottom: 20px;
                    color: #31708f;
                }
            </style>
        </head>

        <body>
            <h1>Edit Post</h1>

            <div class="info">
                Editing post ID: <?php echo $post['id']; ?><br>
                Created: <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="error">
                    <strong>Please fix the following errors:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="/posts/<?php echo $post['id']; ?>/update" method="post">
                <div class="form-group">
                    <label for="title">Title:</label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="<?php echo htmlspecialchars($post['title']); ?>"
                        required />
                </div>

                <div class="form-group">
                    <label for="content">Content:</label>
                    <textarea
                        id="content"
                        name="content"
                        required><?php echo htmlspecialchars($post['content']); ?></textarea>
                </div>

                <div class="button-group">
                    <button type="submit">Update Post</button>
                    <a href="/posts/<?php echo $post['id']; ?>" class="cancel-link">Cancel</a>
                </div>
            </form>
        </body>

        </html>
    <?php
    }
}

// ============================================================================
// Updated show view with Edit link
// ============================================================================

function renderShowViewWithEdit(array $post): string
{
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>

    <head>
        <title><?php echo htmlspecialchars($post['title']); ?></title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 800px;
                margin: 50px auto;
                padding: 0 20px;
            }

            .post-header {
                margin-bottom: 30px;
            }

            .post-title {
                color: #2c3e50;
                margin-bottom: 10px;
            }

            .post-meta {
                color: #7f8c8d;
                font-size: 0.9em;
            }

            .post-content {
                line-height: 1.8;
                color: #333;
                margin-bottom: 30px;
            }

            .actions {
                display: flex;
                gap: 10px;
                padding: 20px 0;
                border-top: 2px solid #ecf0f1;
            }

            .btn {
                padding: 10px 20px;
                text-decoration: none;
                border-radius: 4px;
                font-weight: bold;
            }

            .btn-edit {
                background: #3498db;
                color: white;
            }

            .btn-edit:hover {
                background: #2980b9;
            }

            .btn-back {
                background: #95a5a6;
                color: white;
            }

            .btn-back:hover {
                background: #7f8c8d;
            }
        </style>
    </head>

    <body>
        <div class="post-header">
            <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
            <div class="post-meta">
                Posted on <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
            </div>
        </div>

        <div class="post-content">
            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
        </div>

        <div class="actions">
            <a href="/posts/<?php echo $post['id']; ?>/edit" class="btn btn-edit">✏️ Edit Post</a>
            <a href="/posts" class="btn btn-back">← Back to All Posts</a>
        </div>
    </body>

    </html>
<?php
    return ob_get_clean();
}

// ============================================================================
// Route Registration Example
// ============================================================================

echo "=== Update Post Implementation ===" . PHP_EOL . PHP_EOL;

echo "✓ Added Post::update() method:" . PHP_EOL;
echo "  - Accepts id, title, content" . PHP_EOL;
echo "  - Updates database record" . PHP_EOL;
echo "  - Returns success boolean" . PHP_EOL . PHP_EOL;

echo "✓ Added PostController::edit() method:" . PHP_EOL;
echo "  - Fetches post by ID" . PHP_EOL;
echo "  - Renders edit form" . PHP_EOL;
echo "  - Handles 404 for missing posts" . PHP_EOL . PHP_EOL;

echo "✓ Added PostController::updatePost() method:" . PHP_EOL;
echo "  - Validates input (title and content)" . PHP_EOL;
echo "  - Updates post in database" . PHP_EOL;
echo "  - Redirects to post view on success" . PHP_EOL;
echo "  - Shows errors if validation fails" . PHP_EOL . PHP_EOL;

echo "✓ Created edit form view with:" . PHP_EOL;
echo "  - Pre-filled form fields" . PHP_EOL;
echo "  - Error display" . PHP_EOL;
echo "  - Cancel button" . PHP_EOL;
echo "  - Post info display" . PHP_EOL . PHP_EOL;

echo "✓ Updated show view with:" . PHP_EOL;
echo "  - Edit button linking to /posts/{id}/edit" . PHP_EOL . PHP_EOL;

echo "Route registration:" . PHP_EOL;
echo "  \$router->get('/posts/{id}/edit', [PostController::class, 'edit']);" . PHP_EOL;
echo "  \$router->post('/posts/{id}/update', [PostController::class, 'updatePost']);" . PHP_EOL;
