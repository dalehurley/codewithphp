<?php

declare(strict_types=1);

/**
 * Eloquent Query Examples
 * 
 * This demonstrates Eloquent queries: filtering, ordering, aggregations.
 * Compare to Django ORM (django-queries.py) and SQLAlchemy (sqlalchemy-queries.php).
 */

namespace App\Queries;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Basic queries
$posts = Post::all(); // Get all posts
$post = Post::find(1); // Get by primary key (returns null if not found)
$post = Post::where('id', 1)->first(); // Get one (returns null if not found)
$post = Post::where('id', 1)->firstOrFail(); // Get one (throws ModelNotFoundException if not found)

// Filtering
$recentPosts = Post::where('published_at', '>=', now()->subDays(7))->get();
$draftPosts = Post::whereNull('published_at')->get();
$userPosts = Post::where('author_id', 1)->get();

// Complex filtering
$posts = Post::where(function ($query): void {
    $query->where('title', 'like', '%django%')
          ->orWhere('content', 'like', '%django%');
})->whereNotNull('published_at')->get();

// Field operations
$posts = Post::where('title', 'like', '%django%')  // Contains
    ->where('published_at', '>=', now()->subDays(7))  // Greater than or equal
    ->where('published_at', '<=', now())  // Less than or equal
    ->whereIn('author_id', [1, 2, 3])  // In list
    ->get();

// Exclude (opposite of where)
$publishedPosts = Post::whereNotNull('published_at')->get();

// Ordering and limiting
$posts = Post::orderBy('published_at', 'desc')->limit(10)->get(); // Latest 10
$posts = Post::orderBy('title', 'asc')->get(); // Ascending
$posts = Post::orderBy('published_at', 'desc')
    ->orderBy('title', 'asc')
    ->get(); // Multiple ordering

// Aggregations
$postCount = Post::count();
$avgPosts = User::withCount('posts')->get()->avg('posts_count');

// Annotations (add computed fields)
$users = User::withCount('posts')
    ->withMax('posts', 'published_at')
    ->having('posts_count', '>', 0)
    ->get();

// Chaining
$posts = Post::where('author_id', 1)
    ->orderBy('published_at', 'desc')
    ->where('title', '!=', '')
    ->distinct()
    ->get();

// Subqueries
$recentAuthors = User::whereIn('id', function ($query): void {
    $query->select('author_id')
          ->from('posts')
          ->where('published_at', '>=', now()->subDays(7));
})->get();

// Group by
$postsByAuthor = Post::select('author_id', DB::raw('count(*) as count'))
    ->groupBy('author_id')
    ->orderBy('count', 'desc')
    ->get();

// Exists check
$hasPosts = Post::where('author_id', 1)->exists();

// Update
Post::where('author_id', 1)->update(['published_at' => now()]);

// Delete
Post::where('published_at', '<', now()->subDays(365))->delete();

// Advanced: Select specific columns
$posts = Post::select('id', 'title', 'author_id')
    ->with('author:id,name')  // Only load id and name from author
    ->get();

// Advanced: Raw expressions
$posts = Post::selectRaw('*, YEAR(published_at) as year')
    ->whereRaw('published_at >= ?', [now()->subDays(7)])
    ->get();

// Advanced: Joins
$posts = Post::join('users', 'posts.author_id', '=', 'users.id')
    ->select('posts.*', 'users.name as author_name')
    ->get();

// Advanced: Unions
$allPosts = Post::where('author_id', 1)
    ->union(Post::where('author_id', 2))
    ->get();

