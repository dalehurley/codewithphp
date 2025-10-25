<?php

declare(strict_types=1);

/**
 * Exercise 3: Add Timestamps to Edit (⭐)
 * 
 * Update the schema and views to track when posts were last edited.
 * 
 * Requirements:
 * - Update posts table to add updated_at column
 * - Modify update() method to set updated_at
 * - Display "Last updated" when updated_at differs from created_at
 */

// ============================================================================
// Database Schema Update
// ============================================================================

function initializeDatabase(): PDO
{
    $pdo = new PDO('sqlite:' . __DIR__ . '/blog.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create posts table with updated_at column
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            content TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    echo "✓ Database initialized with updated_at column" . PHP_EOL;
    return $pdo;
}

/**
 * Migration to add updated_at to existing database
 */
function addUpdatedAtColumn(): void
{
    $pdo = new PDO('sqlite:' . __DIR__ . '/blog.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    try {
        // Check if column exists
        $result = $pdo->query("PRAGMA table_info(posts)")->fetchAll();
        $hasUpdatedAt = false;

        foreach ($result as $column) {
            if ($column['name'] === 'updated_at') {
                $hasUpdatedAt = true;
                break;
            }
        }

        if (!$hasUpdatedAt) {
            $pdo->exec("ALTER TABLE posts ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP");
            echo "✓ Added updated_at column to existing posts table" . PHP_EOL;
        } else {
            echo "✓ updated_at column already exists" . PHP_EOL;
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage() . PHP_EOL;
    }
}

// ============================================================================
// Updated Post Model with timestamp tracking
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
     * Update a post and set updated_at timestamp
     * 
     * @param int $id Post ID
     * @param string $title New title
     * @param string $content New content
     * @return bool Success status
     */
    public static function update(int $id, string $title, string $content): bool
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("
            UPDATE posts 
            SET title = ?, 
                content = ?, 
                updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        return $stmt->execute([$title, $content, $id]);
    }

    /**
     * Create a new post
     */
    public static function create(string $title, string $content): int
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO posts (title, content, created_at, updated_at) 
            VALUES (?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");
        $stmt->execute([$title, $content]);
        return (int) $pdo->lastInsertId();
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
     * Check if a post has been updated since creation
     */
    public static function wasUpdated(array $post): bool
    {
        if (!isset($post['created_at']) || !isset($post['updated_at'])) {
            return false;
        }

        // Compare timestamps
        $created = strtotime($post['created_at']);
        $updated = strtotime($post['updated_at']);

        // Consider updated if difference is more than 1 second
        return ($updated - $created) > 1;
    }
}

// ============================================================================
// Updated View with timestamp display
// ============================================================================

function renderPostWithTimestamps(array $post): string
{
    $wasUpdated = Post::wasUpdated($post);

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
                margin-bottom: 15px;
            }

            .post-meta {
                display: flex;
                flex-direction: column;
                gap: 5px;
                color: #7f8c8d;
                font-size: 0.9em;
                padding: 15px;
                background: #f8f9fa;
                border-radius: 4px;
            }

            .meta-item {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .meta-label {
                font-weight: bold;
                color: #555;
            }

            .updated-badge {
                background: #3498db;
                color: white;
                padding: 2px 8px;
                border-radius: 3px;
                font-size: 0.85em;
                font-weight: bold;
            }

            .post-content {
                line-height: 1.8;
                color: #333;
                margin: 30px 0;
                padding: 20px;
                background: #fff;
                border: 1px solid #e1e8ed;
                border-radius: 4px;
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
                <div class="meta-item">
                    <span class="meta-label">📅 Created:</span>
                    <span><?php echo date('F j, Y \a\t g:i A', strtotime($post['created_at'])); ?></span>
                </div>

                <?php if ($wasUpdated): ?>
                    <div class="meta-item">
                        <span class="meta-label">✏️ Last Updated:</span>
                        <span><?php echo date('F j, Y \a\t g:i A', strtotime($post['updated_at'])); ?></span>
                        <span class="updated-badge">EDITED</span>
                    </div>
                <?php endif; ?>
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
// Helper function to display relative time
// ============================================================================

function timeAgo(string $datetime): string
{
    $now = time();
    $timestamp = strtotime($datetime);
    $diff = $now - $timestamp;

    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 2592000) {
        $weeks = floor($diff / 604800);
        return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
    } else {
        return date('F j, Y', $timestamp);
    }
}

// ============================================================================
// Demo
// ============================================================================

echo "=== Timestamp Tracking Implementation ===" . PHP_EOL . PHP_EOL;

echo "✓ Database schema updated:" . PHP_EOL;
echo "  - Added updated_at DATETIME column" . PHP_EOL;
echo "  - Default value: CURRENT_TIMESTAMP" . PHP_EOL . PHP_EOL;

echo "✓ Post::update() modified to:" . PHP_EOL;
echo "  - Automatically set updated_at = CURRENT_TIMESTAMP" . PHP_EOL;
echo "  - Update happens on every post modification" . PHP_EOL . PHP_EOL;

echo "✓ Added Post::wasUpdated() helper method:" . PHP_EOL;
echo "  - Compares created_at and updated_at timestamps" . PHP_EOL;
echo "  - Returns true if post was edited after creation" . PHP_EOL . PHP_EOL;

echo "✓ Updated view displays:" . PHP_EOL;
echo "  - Created date (always shown)" . PHP_EOL;
echo "  - Last updated date (only if modified)" . PHP_EOL;
echo "  - 'EDITED' badge for modified posts" . PHP_EOL;
echo "  - Formatted timestamps with time" . PHP_EOL . PHP_EOL;

echo "✓ Bonus: timeAgo() function for relative times:" . PHP_EOL;
echo "  - Shows '5 minutes ago', '2 hours ago', etc." . PHP_EOL;
echo "  - More user-friendly than absolute timestamps" . PHP_EOL . PHP_EOL;

echo "Migration command included:" . PHP_EOL;
echo "  - addUpdatedAtColumn() adds column to existing database" . PHP_EOL;
echo "  - Safe to run multiple times (checks if column exists)" . PHP_EOL . PHP_EOL;

// Demo the timeAgo function
echo "timeAgo() examples:" . PHP_EOL;
echo "  5 minutes ago: " . timeAgo(date('Y-m-d H:i:s', strtotime('-5 minutes'))) . PHP_EOL;
echo "  2 hours ago: " . timeAgo(date('Y-m-d H:i:s', strtotime('-2 hours'))) . PHP_EOL;
echo "  3 days ago: " . timeAgo(date('Y-m-d H:i:s', strtotime('-3 days'))) . PHP_EOL;
