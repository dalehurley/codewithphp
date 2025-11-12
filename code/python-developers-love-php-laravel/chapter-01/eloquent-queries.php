<?php

declare(strict_types=1);

/**
 * Eloquent Query Examples
 * 
 * This demonstrates common Eloquent ORM query patterns.
 * These queries correspond to Django ORM and SQLAlchemy queries.
 * 
 * Place these in controllers or other application code.
 */

use App\Models\Post;

// Get all posts
$posts = Post::all();

// Get a single post by ID
$post = Post::find(1);

// Filter posts published in the last 7 days
$recentPosts = Post::where('published_at', '>=', now()->subDays(7))->get();

// Filter posts by author
$authorPosts = Post::where('author_id', $userId)->get();

// Get posts with related author (eager loading)
$postsWithAuthor = Post::with('author')->get();

// Get posts with multiple relationships (eager loading)
$postsWithRelations = Post::with(['author', 'comments'])->get();

// Chaining query methods
$filteredPosts = Post::where('published_at', '>=', now()->subDays(7))
    ->where('author_id', $userId)
    ->orderBy('published_at', 'desc')
    ->get();

// Using query scopes (if defined in model)
// $publishedPosts = Post::published()->get();

