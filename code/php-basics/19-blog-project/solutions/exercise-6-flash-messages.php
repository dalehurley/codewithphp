<?php

declare(strict_types=1);

/**
 * Exercise 6: Implement Flash Messages (⭐⭐)
 * 
 * Add user feedback for create/update/delete actions.
 * 
 * Requirements:
 * - Create Session class to manage flash messages
 * - Set flash messages after actions (success/error)
 * - Display messages in views
 * - Auto-dismiss messages
 * - Support different message types (success, error, info, warning)
 */

// ============================================================================
// Session and Flash Message Manager
// ============================================================================

class Session
{
    /**
     * Start the session if not already started
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Set a flash message
     * 
     * @param string $key Message key
     * @param string $message Message content
     * @param string $type Message type (success, error, info, warning)
     */
    public static function flash(string $key, string $message, string $type = 'info'): void
    {
        self::start();
        $_SESSION['flash'][$key] = [
            'message' => $message,
            'type' => $type
        ];
    }

    /**
     * Get and remove a flash message
     * 
     * @param string $key Message key
     * @return array|null Message data or null if not found
     */
    public static function getFlash(string $key): ?array
    {
        self::start();

        if (!isset($_SESSION['flash'][$key])) {
            return null;
        }

        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);

        return $message;
    }

    /**
     * Check if a flash message exists
     * 
     * @param string $key Message key
     * @return bool
     */
    public static function hasFlash(string $key): bool
    {
        self::start();
        return isset($_SESSION['flash'][$key]);
    }

    /**
     * Get all flash messages and clear them
     * 
     * @return array All flash messages
     */
    public static function getAllFlashes(): array
    {
        self::start();

        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);

        return $messages;
    }

    /**
     * Set a session value
     */
    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session value
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Remove a session value
     */
    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Destroy the session
     */
    public static function destroy(): void
    {
        self::start();
        session_destroy();
        $_SESSION = [];
    }
}

// ============================================================================
// Flash Message Helper Functions
// ============================================================================

/**
 * Set a success flash message
 */
function flashSuccess(string $message): void
{
    Session::flash('message', $message, 'success');
}

/**
 * Set an error flash message
 */
function flashError(string $message): void
{
    Session::flash('message', $message, 'error');
}

/**
 * Set an info flash message
 */
function flashInfo(string $message): void
{
    Session::flash('message', $message, 'info');
}

/**
 * Set a warning flash message
 */
function flashWarning(string $message): void
{
    Session::flash('message', $message, 'warning');
}

/**
 * Render flash messages HTML
 */
function renderFlashMessages(): string
{
    $messages = Session::getAllFlashes();

    if (empty($messages)) {
        return '';
    }

    ob_start();
?>
    <div id="flash-messages">
        <?php foreach ($messages as $key => $data): ?>
            <div class="flash-message flash-<?php echo htmlspecialchars($data['type']); ?>">
                <div class="flash-content">
                    <span class="flash-icon">
                        <?php
                        echo match ($data['type']) {
                            'success' => '✓',
                            'error' => '✗',
                            'warning' => '⚠',
                            'info' => 'ℹ',
                            default => '•'
                        };
                        ?>
                    </span>
                    <span class="flash-text"><?php echo htmlspecialchars($data['message']); ?></span>
                </div>
                <button class="flash-close" onclick="this.parentElement.remove()">×</button>
            </div>
        <?php endforeach; ?>
    </div>

    <style>
        #flash-messages {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 400px;
        }

        .flash-message {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .flash-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .flash-icon {
            font-size: 20px;
            font-weight: bold;
        }

        .flash-text {
            line-height: 1.5;
        }

        .flash-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            margin-left: 15px;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .flash-close:hover {
            opacity: 1;
        }

        .flash-success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
        }

        .flash-error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }

        .flash-warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            color: #856404;
        }

        .flash-info {
            background: #d1ecf1;
            border-left: 4px solid #17a2b8;
            color: #0c5460;
        }
    </style>

    <script>
        // Auto-dismiss messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const messages = document.querySelectorAll('.flash-message');
            messages.forEach(function(message) {
                setTimeout(function() {
                    message.style.animation = 'slideOut 0.3s ease-out';
                    setTimeout(function() {
                        message.remove();
                    }, 300);
                }, 5000);
            });
        });

        // Animation for slide out
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideOut {
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
    <?php
    return ob_get_clean();
}

// ============================================================================
// Updated PostController with Flash Messages
// ============================================================================

class PostController
{
    /**
     * Create a new post
     */
    public function store(): void
    {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        // Validation
        if (empty($title) || empty($content)) {
            flashError('Title and content are required');
            header('Location: /posts/create');
            exit;
        }

        try {
            $postId = Post::create($title, $content);
            flashSuccess('Post created successfully!');
            header("Location: /posts/{$postId}");
            exit;
        } catch (Exception $e) {
            flashError('Failed to create post: ' . $e->getMessage());
            header('Location: /posts/create');
            exit;
        }
    }

    /**
     * Update a post
     */
    public function update(string $id): void
    {
        $postId = (int) $id;
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        // Validation
        if (empty($title) || empty($content)) {
            flashError('Title and content are required');
            header("Location: /posts/{$postId}/edit");
            exit;
        }

        try {
            $success = Post::update($postId, $title, $content);

            if ($success) {
                flashSuccess('Post updated successfully!');
            } else {
                flashError('Failed to update post');
            }

            header("Location: /posts/{$postId}");
            exit;
        } catch (Exception $e) {
            flashError('Error: ' . $e->getMessage());
            header("Location: /posts/{$postId}/edit");
            exit;
        }
    }

    /**
     * Delete a post
     */
    public function destroy(string $id): void
    {
        $postId = (int) $id;

        try {
            $success = Post::delete($postId);

            if ($success) {
                flashSuccess('Post deleted successfully');
            } else {
                flashError('Failed to delete post');
            }
        } catch (Exception $e) {
            flashError('Error: ' . $e->getMessage());
        }

        header('Location: /posts');
        exit;
    }

    /**
     * Show a single post with flash messages
     */
    public function show(string $id): void
    {
        $post = Post::find((int) $id);

        if ($post === null) {
            flashError('Post not found');
            header('Location: /posts');
            exit;
        }

        $this->renderShowView($post);
    }

    /**
     * Render show view with flash messages
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

                .btn-back {
                    background: #95a5a6;
                    color: white;
                }
            </style>
        </head>

        <body>
            <?php echo renderFlashMessages(); ?>

            <div class="post-header">
                <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
            </div>

            <div class="post-content">
                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
            </div>

            <div class="actions">
                <a href="/posts/<?php echo $post['id']; ?>/edit" class="btn btn-edit">Edit</a>
                <a href="/posts" class="btn btn-back">Back</a>
            </div>
        </body>

        </html>
<?php
    }
}

// ============================================================================
// Stub Post class for demo
// ============================================================================

class Post
{
    public static function create(string $title, string $content): int
    {
        return 1; // Return new post ID
    }

    public static function update(int $id, string $title, string $content): bool
    {
        return true;
    }

    public static function delete(int $id): bool
    {
        return true;
    }

    public static function find(int $id): ?array
    {
        return [
            'id' => $id,
            'title' => 'Sample Post',
            'content' => 'Sample content'
        ];
    }
}

// ============================================================================
// Demo
// ============================================================================

echo "=== Flash Messages Implementation ===" . PHP_EOL . PHP_EOL;

echo "✓ Created Session class with:" . PHP_EOL;
echo "  - flash() method to set flash messages" . PHP_EOL;
echo "  - getFlash() to retrieve and remove messages" . PHP_EOL;
echo "  - getAllFlashes() to get all messages" . PHP_EOL;
echo "  - Support for success, error, info, warning types" . PHP_EOL . PHP_EOL;

echo "✓ Helper functions:" . PHP_EOL;
echo "  - flashSuccess(\$message)" . PHP_EOL;
echo "  - flashError(\$message)" . PHP_EOL;
echo "  - flashInfo(\$message)" . PHP_EOL;
echo "  - flashWarning(\$message)" . PHP_EOL;
echo "  - renderFlashMessages() for display" . PHP_EOL . PHP_EOL;

echo "✓ Updated PostController actions to set flash messages:" . PHP_EOL;
echo "  - store(): 'Post created successfully!'" . PHP_EOL;
echo "  - update(): 'Post updated successfully!'" . PHP_EOL;
echo "  - destroy(): 'Post deleted successfully'" . PHP_EOL;
echo "  - All with appropriate error messages" . PHP_EOL . PHP_EOL;

echo "✓ Flash message features:" . PHP_EOL;
echo "  - Fixed position (top-right)" . PHP_EOL;
echo "  - Slide-in animation" . PHP_EOL;
echo "  - Auto-dismiss after 5 seconds" . PHP_EOL;
echo "  - Manual close button" . PHP_EOL;
echo "  - Color-coded by type" . PHP_EOL;
echo "  - Icons for each type" . PHP_EOL;
echo "  - Stacked for multiple messages" . PHP_EOL . PHP_EOL;

echo "Usage example:" . PHP_EOL;
echo "  flashSuccess('Operation completed!');" . PHP_EOL;
echo "  header('Location: /posts');" . PHP_EOL;
echo "  exit;" . PHP_EOL . PHP_EOL;

echo "In view template:" . PHP_EOL;
echo "  <?php echo renderFlashMessages(); ?>" . PHP_EOL;
