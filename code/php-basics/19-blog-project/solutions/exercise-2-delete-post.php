<?php

declare(strict_types=1);

/**
 * Exercise 2: Implement Delete (⭐⭐)
 * 
 * Add the ability to delete posts.
 * 
 * Requirements:
 * - Add delete() method to Post model
 * - Add destroy() method to PostController
 * - Add delete form to show view with confirmation
 * - Add route for deletion
 */

// ============================================================================
// Updated Post Model with delete() method
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
     * Delete a post by ID
     * 
     * @param int $id Post ID
     * @return bool Success status
     */
    public static function delete(int $id): bool
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        return $stmt->execute([$id]);
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
}

// ============================================================================
// Updated PostController with destroy() method
// ============================================================================

class PostController
{
    /**
     * Delete a post
     */
    public function destroy(string $id): void
    {
        $postId = (int) $id;
        $post = Post::find($postId);

        if ($post === null) {
            http_response_code(404);
            echo "Post not found";
            return;
        }

        // Delete the post
        $success = Post::delete($postId);

        if ($success) {
            // Redirect to posts list
            header('Location: /posts');
            exit;
        } else {
            echo "Failed to delete post";
        }
    }

    /**
     * Show a single post
     */
    public function show(string $id): void
    {
        $post = Post::find((int) $id);

        if ($post === null) {
            http_response_code(404);
            echo "Post not found";
            return;
        }

        $this->renderShowView($post);
    }

    /**
     * Render the show view with delete button
     */
    private function renderShowView(array $post): void
    {
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
                    padding: 20px;
                    background: #f8f9fa;
                    border-radius: 4px;
                }

                .actions {
                    display: flex;
                    gap: 10px;
                    padding: 20px 0;
                    border-top: 2px solid #ecf0f1;
                    align-items: center;
                }

                .btn {
                    padding: 10px 20px;
                    text-decoration: none;
                    border-radius: 4px;
                    font-weight: bold;
                    display: inline-block;
                }

                .btn-edit {
                    background: #3498db;
                    color: white;
                    border: none;
                }

                .btn-edit:hover {
                    background: #2980b9;
                }

                .btn-back {
                    background: #95a5a6;
                    color: white;
                    border: none;
                }

                .btn-back:hover {
                    background: #7f8c8d;
                }

                .btn-delete {
                    background: #e74c3c;
                    color: white;
                    border: none;
                    cursor: pointer;
                    padding: 10px 20px;
                    border-radius: 4px;
                    font-weight: bold;
                }

                .btn-delete:hover {
                    background: #c0392b;
                }

                .delete-form {
                    display: inline;
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
                <a href="/posts/<?php echo $post['id']; ?>/edit" class="btn btn-edit">✏️ Edit</a>

                <form
                    action="/posts/<?php echo $post['id']; ?>/delete"
                    method="post"
                    class="delete-form"
                    onsubmit="return confirm('Are you sure you want to delete this post? This action cannot be undone.');">
                    <button type="submit" class="btn-delete">🗑️ Delete</button>
                </form>

                <a href="/posts" class="btn btn-back">← Back to All Posts</a>
            </div>
        </body>

        </html>
<?php
    }
}

// ============================================================================
// Alternative: Soft Delete Implementation
// ============================================================================

class PostWithSoftDelete
{
    /**
     * Soft delete a post (mark as deleted instead of removing)
     * 
     * This is a better approach for production as it allows recovery
     */
    public static function softDelete(int $id): bool
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("UPDATE posts SET deleted_at = CURRENT_TIMESTAMP WHERE id = ? AND deleted_at IS NULL");
        return $stmt->execute([$id]);
    }

    /**
     * Restore a soft-deleted post
     */
    public static function restore(int $id): bool
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("UPDATE posts SET deleted_at = NULL WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get all posts excluding deleted ones
     */
    public static function all(): array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->query("SELECT * FROM posts WHERE deleted_at IS NULL ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all posts including deleted ones
     */
    public static function allWithTrashed(): array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get only deleted posts
     */
    public static function onlyTrashed(): array
    {
        $pdo = self::getConnection();
        $stmt = $pdo->query("SELECT * FROM posts WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private static function getConnection(): PDO
    {
        $pdo = new PDO('sqlite:' . __DIR__ . '/blog.db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
}

// ============================================================================
// Demo
// ============================================================================

echo "=== Delete Post Implementation ===" . PHP_EOL . PHP_EOL;

echo "✓ Added Post::delete() method:" . PHP_EOL;
echo "  - Accepts post ID" . PHP_EOL;
echo "  - Permanently removes from database" . PHP_EOL;
echo "  - Returns success boolean" . PHP_EOL . PHP_EOL;

echo "✓ Added PostController::destroy() method:" . PHP_EOL;
echo "  - Checks if post exists" . PHP_EOL;
echo "  - Calls Post::delete()" . PHP_EOL;
echo "  - Redirects to posts list on success" . PHP_EOL;
echo "  - Handles 404 for missing posts" . PHP_EOL . PHP_EOL;

echo "✓ Updated show view with delete button:" . PHP_EOL;
echo "  - Form with POST method" . PHP_EOL;
echo "  - JavaScript confirmation dialog" . PHP_EOL;
echo "  - Styled as dangerous action (red)" . PHP_EOL;
echo "  - Inline form for proper semantics" . PHP_EOL . PHP_EOL;

echo "✓ Route registration:" . PHP_EOL;
echo "  \$router->post('/posts/{id}/delete', [PostController::class, 'destroy']);" . PHP_EOL . PHP_EOL;

echo "Security considerations:" . PHP_EOL;
echo "  - Use POST method (not GET) for destructive actions" . PHP_EOL;
echo "  - Add confirmation dialog" . PHP_EOL;
echo "  - Consider adding CSRF protection" . PHP_EOL;
echo "  - Consider soft delete for production" . PHP_EOL . PHP_EOL;

echo "✓ Bonus: Soft delete implementation included:" . PHP_EOL;
echo "  - softDelete() marks post as deleted" . PHP_EOL;
echo "  - restore() undeletes a post" . PHP_EOL;
echo "  - onlyTrashed() shows deleted posts" . PHP_EOL;
echo "  - allWithTrashed() includes deleted posts" . PHP_EOL;
