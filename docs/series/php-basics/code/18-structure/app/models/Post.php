<?php

declare(strict_types=1);

namespace Models;

/**
 * Post Model
 * 
 * Represents a blog post.
 */

class Post extends Model
{
    protected string $table = 'posts';

    /**
     * Get recent posts
     */
    public function recent(int $limit = 10): array
    {
        return $this->query(
            "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT ?",
            [$limit]
        );
    }

    /**
     * Search posts by title
     */
    public function search(string $query): array
    {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE title LIKE ? OR content LIKE ?",
            ["%{$query}%", "%{$query}%"]
        );
    }

    /**
     * Get posts by author
     */
    public function byAuthor(string $author): array
    {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE author = ?",
            [$author]
        );
    }
}
