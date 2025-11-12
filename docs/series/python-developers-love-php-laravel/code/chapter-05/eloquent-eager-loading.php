<?php

declare(strict_types=1);

/**
 * Eloquent Eager Loading Examples
 * 
 * This demonstrates eager loading in Eloquent to prevent N+1 query problems.
 * Compare to Django's select_related() and prefetch_related().
 */

namespace App\Examples;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// ============================================
// THE N+1 PROBLEM (Without Eager Loading)
// ============================================

// This generates 1 query for posts + N queries for authors (one per post)
// If you have 100 posts, this generates 101 queries!
DB::enableQueryLog();
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author->name; // Query executed here for each post!
}
$queries = DB::getQueryLog();
// Result: 1 query for posts + N queries for authors = N+1 queries

// ============================================
// SOLUTION: Eager Loading with with()
// ============================================

// This generates only 2 queries: 1 for posts + 1 for all authors
DB::enableQueryLog();
$posts = Post::with('author')->get();
foreach ($posts as $post) {
    echo $post->author->name; // No additional query!
}
$queries = DB::getQueryLog();
// Result: 2 queries total (posts + authors)

// ============================================
// Eager Loading Multiple Relationships
// ============================================

// Load author and tags for all posts
$posts = Post::with('author', 'tags')->get();
// Generates:
// 1. SELECT * FROM posts
// 2. SELECT * FROM users WHERE id IN (...)
// 3. SELECT * FROM post_tag WHERE post_id IN (...)
// 4. SELECT * FROM tags WHERE id IN (...)

// ============================================
// Nested Eager Loading
// ============================================

// Load Post → Author → Profile, and Post → Tags
$posts = Post::with('author.profile', 'tags')->get();
// Generates queries for: posts, authors, profiles, tags

// ============================================
// Constraining Eager Loads
// ============================================

// Only load specific columns from author
$posts = Post::with('author:id,name')->get();
// Only loads id and name from users table

// Load author with conditions
$posts = Post::with(['author' => function ($query): void {
    $query->select('id', 'name')->where('active', true);
}])->get();

// Load tags with conditions
$posts = Post::with(['tags' => function ($query): void {
    $query->where('name', '!=', 'draft');
}])->get();

// ============================================
// Lazy Eager Loading
// ============================================

// Load relationships after the initial query
$posts = Post::all();
$posts->load('author'); // Load authors for already-fetched posts

// Load with conditions
$posts->load(['tags' => function ($query): void {
    $query->where('active', true);
}]);

// ============================================
// Eager Loading with Count
// ============================================

// Count relationships without loading them
$posts = Post::withCount('tags')->get();
// Access via: $post->tags_count

// Count with conditions
$posts = Post::withCount(['tags' => function ($query): void {
    $query->where('active', true);
}])->get();
// Access via: $post->tags_count

// Multiple counts
$posts = Post::withCount(['tags', 'comments'])->get();
// Access via: $post->tags_count, $post->comments_count

// ============================================
// Eager Loading with Aggregates
// ============================================

// Get max/min/avg of related models
$posts = Post::withMax('comments', 'created_at')->get();
// Access via: $post->comments_max_created_at

$posts = Post::withMin('comments', 'created_at')->get();
// Access via: $post->comments_min_created_at

$posts = Post::withAvg('comments', 'rating')->get();
// Access via: $post->comments_avg_rating

// ============================================
// Conditional Eager Loading
// ============================================

// Only eager load if condition is met
$loadAuthor = request()->has('include_author');
$posts = Post::when($loadAuthor, function ($query) {
    $query->with('author');
})->get();

// ============================================
// Performance Tips
// ============================================

// 1. Only load what you need
$posts = Post::select('id', 'title', 'author_id')
    ->with('author:id,name')
    ->get();

// 2. Use pagination to limit results
$posts = Post::with('author')->paginate(20);

// 3. Add indexes on foreign keys
// In migration: $table->index('author_id');

// 4. Use lazy() for large datasets
$posts = Post::lazy()->each(function ($post) {
    $post->load('author');
});

