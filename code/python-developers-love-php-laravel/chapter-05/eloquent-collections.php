<?php

declare(strict_types=1);

/**
 * Eloquent Collections Examples
 * 
 * This demonstrates Eloquent Collections and their methods.
 * Compare to Django QuerySets and Python lists.
 */

namespace App\Examples;

use App\Models\Post;
use Illuminate\Support\Collection;

// Eloquent returns Collections, not arrays
$posts = Post::all(); // Returns Illuminate\Support\Collection

// Convert to array
$postArray = $posts->toArray();

// ============================================
// EXTRACTING VALUES
// ============================================

// pluck() - Extract single column (like list comprehension)
$titles = $posts->pluck('title'); // ['Title 1', 'Title 2', ...]
$titlesById = $posts->pluck('title', 'id'); // [1 => 'Title 1', 2 => 'Title 2', ...]

// ============================================
// TRANSFORMING
// ============================================

// map() - Transform each item (like Python's map())
$uppercaseTitles = $posts->map(fn($post) => strtoupper($post->title));
$formattedPosts = $posts->map(fn($post) => [
    'id' => $post->id,
    'title' => $post->title,
    'excerpt' => substr($post->content, 0, 100),
]);

// ============================================
// FILTERING
// ============================================

// filter() - Filter items (like list comprehension)
$published = $posts->filter(fn($post) => $post->published_at !== null);
$recent = $posts->filter(fn($post) => $post->published_at >= now()->subDays(7));

// reject() - Opposite of filter
$drafts = $posts->reject(fn($post) => $post->published_at !== null);

// ============================================
// REDUCING
// ============================================

// reduce() - Reduce to single value (like Python's reduce())
$totalViews = $posts->reduce(fn($carry, $post) => $carry + ($post->views ?? 0), 0);
$longestTitle = $posts->reduce(fn($carry, $post) => 
    strlen($post->title) > strlen($carry) ? $post->title : $carry, ''
);

// sum() - Sum values
$totalViews = $posts->sum('views');
$avgViews = $posts->avg('views');
$maxViews = $posts->max('views');
$minViews = $posts->min('views');

// ============================================
// GROUPING
// ============================================

// groupBy() - Group by key (like itertools.groupby())
$byAuthor = $posts->groupBy('author_id');
$byYear = $posts->groupBy(fn($post) => $post->published_at->year);

// ============================================
// SORTING
// ============================================

// sortBy() - Sort ascending
$sorted = $posts->sortBy('published_at');
$sorted = $posts->sortBy(fn($post) => strlen($post->title));

// sortByDesc() - Sort descending
$sorted = $posts->sortByDesc('published_at');

// ============================================
// SLICING
// ============================================

// take() - Take first N items
$first5 = $posts->take(5);

// skip() - Skip first N items
$skip5 = $posts->skip(5);

// slice() - Get slice
$middle = $posts->slice(5, 10); // Skip 5, take 10

// ============================================
// PROCESSING LARGE COLLECTIONS
// ============================================

// chunk() - Process in chunks
$posts->chunk(100, function (Collection $chunk): void {
    foreach ($chunk as $post) {
        // Process 100 posts at a time
    }
});

// lazy() - Lazy collection (for memory efficiency)
$posts->lazy()->each(function ($post): void {
    // Process one at a time without loading all into memory
});

// ============================================
// SEARCHING
// ============================================

// contains() - Check if contains value
if ($posts->contains('id', 1)) {
    // Collection contains post with id = 1
}

// first() - Get first item
$first = $posts->first();
$firstPublished = $posts->first(fn($post) => $post->published_at !== null);

// last() - Get last item
$last = $posts->last();

// ============================================
// CONVERSION
// ============================================

// toArray() - Convert to array
$array = $posts->toArray();

// toJson() - Convert to JSON
$json = $posts->toJson();

// values() - Reset keys
$reindexed = $posts->values();

// ============================================
// METHOD CHAINING
// ============================================

// Chain multiple methods
$result = $posts
    ->filter(fn($post) => $post->published_at !== null)
    ->map(fn($post) => strtoupper($post->title))
    ->take(10)
    ->pluck('title')
    ->toArray();

