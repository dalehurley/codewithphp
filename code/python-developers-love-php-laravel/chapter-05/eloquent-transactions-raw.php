<?php

declare(strict_types=1);

/**
 * Eloquent Transactions and Raw Queries Examples
 * 
 * This demonstrates database transactions and raw SQL queries in Eloquent.
 * Compare to Django transactions and SQLAlchemy transactions.
 */

namespace App\Examples;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Support\Facades\DB;

// ============================================
// TRANSACTIONS
// ============================================

// Closure-based transaction (recommended)
DB::transaction(function (): void {
    $post = Post::create(['title' => 'Post', 'content' => 'Content']);
    Comment::create(['post_id' => $post->id, 'content' => 'Comment']);
    // If any operation fails, all are rolled back automatically
});

// Manual transaction
DB::beginTransaction();
try {
    $post = Post::create(['title' => 'Post', 'content' => 'Content']);
    Comment::create(['post_id' => $post->id, 'content' => 'Comment']);
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    throw $e;
}

// Transaction with return value
$post = DB::transaction(function (): Post {
    $post = Post::create(['title' => 'Post']);
    Comment::create(['post_id' => $post->id, 'content' => 'Comment']);
    return $post;
});

// Nested transactions (savepoints)
DB::transaction(function (): void {
    $post = Post::create(['title' => 'Post']);
    
    DB::transaction(function () use ($post): void {
        // Nested transaction (savepoint)
        Comment::create(['post_id' => $post->id, 'content' => 'Comment']);
    });
});

// ============================================
// RAW QUERIES
// ============================================

// Raw SELECT queries
$posts = DB::select("SELECT * FROM posts WHERE published_at IS NOT NULL");
$post = DB::selectOne("SELECT * FROM posts WHERE id = ?", [1]);

// Raw INSERT
DB::insert("INSERT INTO posts (title, content) VALUES (?, ?)", ['Title', 'Content']);

// Raw UPDATE
DB::update("UPDATE posts SET title = ? WHERE id = ?", ['New Title', 1]);

// Raw DELETE
DB::delete("DELETE FROM posts WHERE id = ?", [1]);

// Raw in query builder
$posts = Post::whereRaw("published_at IS NOT NULL")->get();
$posts = Post::selectRaw("*, YEAR(published_at) as year")->get();
$posts = Post::whereRaw("author_id IN (SELECT id FROM users WHERE active = ?)", [1])->get();

// Raw expressions
$posts = Post::where('id', DB::raw('(SELECT MAX(id) FROM posts)'))->get();
$posts = Post::orderByRaw('RAND()')->get();

// ============================================
// CONVERTING RAW QUERIES TO MODELS
// ============================================

// Convert raw query results to Eloquent models
$posts = Post::fromQuery("SELECT * FROM posts WHERE published_at >= ?", [now()->subDays(7)]);

// ============================================
// TRANSACTION BEST PRACTICES
// ============================================

// ✅ Good: Use closure-based transactions
function createPostWithComment(array $postData, string $commentContent): Post
{
    return DB::transaction(function () use ($postData, $commentContent): Post {
        $post = Post::create($postData);
        Comment::create([
            'post_id' => $post->id,
            'content' => $commentContent,
        ]);
        return $post;
    });
}

// ✅ Good: Handle exceptions properly
try {
    DB::transaction(function (): void {
        // Operations
    });
} catch (\Exception $e) {
    // Handle error (transaction already rolled back)
    \Log::error("Transaction failed: " . $e->getMessage());
    throw $e;
}

// ❌ Bad: Don't forget to commit/rollback manually
// DB::beginTransaction();
// ... operations ...
// Missing DB::commit() or DB::rollBack()

// ============================================
// RAW QUERY BEST PRACTICES
// ============================================

// ✅ Good: Use parameter binding (prevents SQL injection)
$id = 1;
$posts = DB::select("SELECT * FROM posts WHERE id = ?", [$id]);

// ✅ Good: Use query builder when possible
$posts = Post::where('published_at', '>=', now())->get();

// ❌ Bad: Don't concatenate user input into SQL
// $id = $_GET['id'];
// DB::select("SELECT * FROM posts WHERE id = " . $id); // SQL injection risk!

// ============================================
// ADVANCED: DATABASE-SPECIFIC QUERIES
// ============================================

// MySQL specific
$posts = DB::select("SELECT * FROM posts WHERE MATCH(title, content) AGAINST(? IN BOOLEAN MODE)", ['search term']);

// PostgreSQL specific
$posts = DB::select("SELECT * FROM posts WHERE title ILIKE ?", ['%search%']);

// SQLite specific
$posts = DB::select("SELECT * FROM posts WHERE title LIKE ? ESCAPE '\\'", ['%search%']);

