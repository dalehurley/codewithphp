<?php

/**
 * Complete Blog System
 * Demonstrates PDO best practices with a simple blog CRUD system
 */

class BlogDatabase
{
    private PDO $pdo;

    public function __construct(string $dbPath)
    {
        $dsn = "sqlite:$dbPath";

        try {
            $this->pdo = new PDO($dsn);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->initializeDatabase();
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    private function initializeDatabase(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS posts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                content TEXT NOT NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    public function createPost(string $title, string $content): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO posts (title, content) VALUES (:title, :content)
        ");

        $stmt->execute([
            ':title' => $title,
            ':content' => $content
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function getPost(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM posts WHERE id = ?
        ");

        $stmt->execute([$id]);
        $post = $stmt->fetch();

        return $post ?: null;
    }

    public function getAllPosts(): array
    {
        $stmt = $this->pdo->query("
            SELECT * FROM posts ORDER BY created_at DESC
        ");

        return $stmt->fetchAll();
    }

    public function updatePost(int $id, string $title, string $content): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE posts 
            SET title = :title, 
                content = :content, 
                updated_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $id,
            ':title' => $title,
            ':content' => $content
        ]);

        return $stmt->rowCount() > 0;
    }

    public function deletePost(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$id]);

        return $stmt->rowCount() > 0;
    }

    public function searchPosts(string $query): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM posts 
            WHERE title LIKE :query OR content LIKE :query
            ORDER BY created_at DESC
        ");

        $stmt->execute([':query' => "%$query%"]);

        return $stmt->fetchAll();
    }
}

// Usage example
try {
    $blog = new BlogDatabase(__DIR__ . '/data/blog.sqlite');

    // Create posts
    echo "Creating posts..." . PHP_EOL;
    $id1 = $blog->createPost("Getting Started with PDO", "PDO is awesome!");
    $id2 = $blog->createPost("PHP 8.4 Features", "Check out these new features...");
    echo "Created posts with IDs: $id1, $id2" . PHP_EOL . PHP_EOL;

    // Get all posts
    echo "All posts:" . PHP_EOL;
    $posts = $blog->getAllPosts();
    foreach ($posts as $post) {
        echo "  [{$post['id']}] {$post['title']}" . PHP_EOL;
    }
    echo PHP_EOL;

    // Update a post
    echo "Updating post $id1..." . PHP_EOL;
    $blog->updatePost($id1, "Getting Started with PDO (Updated)", "PDO is really awesome!");
    echo "Post updated successfully." . PHP_EOL . PHP_EOL;

    // Search posts
    echo "Searching for 'PDO':" . PHP_EOL;
    $results = $blog->searchPosts("PDO");
    foreach ($results as $post) {
        echo "  [{$post['id']}] {$post['title']}" . PHP_EOL;
    }
    echo PHP_EOL;

    // Delete a post
    echo "Deleting post $id2..." . PHP_EOL;
    $blog->deletePost($id2);
    echo "Post deleted successfully." . PHP_EOL . PHP_EOL;

    // Final count
    $remaining = $blog->getAllPosts();
    echo "Total posts remaining: " . count($remaining) . PHP_EOL;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
