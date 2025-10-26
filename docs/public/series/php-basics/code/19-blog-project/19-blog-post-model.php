<?php

declare(strict_types=1);

/**
 * Chapter 19 Code Sample: Post Model
 * 
 * This file demonstrates a simple model for blog post operations.
 * Copy this to src/Models/Post.php in your simple-blog project.
 */

namespace App\Models;

use App\Core\Database;
use PDO;

class Post
{
    /**
     * Get all posts ordered by most recent first.
     * 
     * @return array Array of post records
     */
    public static function all(): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->query("SELECT * FROM posts ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    /**
     * Find a single post by ID.
     * 
     * @param int $id The post ID
     * @return array|false The post record or false if not found
     */
    public static function find(int $id): array|false
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create a new post.
     * 
     * @param string $title The post title
     * @param string $content The post content
     * @return bool True on success, false on failure
     */
    public static function create(string $title, string $content): bool
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("INSERT INTO posts (title, content) VALUES (?, ?)");
        return $stmt->execute([$title, $content]);
    }

    /**
     * Update an existing post.
     * 
     * @param int $id The post ID
     * @param string $title The new title
     * @param string $content The new content
     * @return bool True on success, false on failure
     */
    public static function update(int $id, string $title, string $content): bool
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
        return $stmt->execute([$title, $content, $id]);
    }

    /**
     * Delete a post by ID.
     * 
     * @param int $id The post ID
     * @return bool True on success, false on failure
     */
    public static function delete(int $id): bool
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Count total posts.
     * 
     * @return int Total number of posts
     */
    public static function count(): int
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->query("SELECT COUNT(*) FROM posts");
        return (int) $stmt->fetchColumn();
    }

    /**
     * Search posts by title or content.
     * 
     * @param string $query The search query
     * @return array Array of matching post records
     */
    public static function search(string $query): array
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT * FROM posts 
            WHERE title LIKE ? OR content LIKE ? 
            ORDER BY id DESC
        ");
        $searchTerm = "%{$query}%";
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
}
