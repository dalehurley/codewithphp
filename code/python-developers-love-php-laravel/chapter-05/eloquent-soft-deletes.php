<?php

declare(strict_types=1);

/**
 * Eloquent Soft Deletes Examples
 * 
 * This demonstrates soft deletes in Eloquent.
 * Compare to Django's approach (which requires packages).
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes; // Adds soft delete functionality
    
    protected $fillable = ['title', 'content', 'author_id'];
    
    // Eloquent automatically adds deleted_at column
    // Migration should include: $table->softDeletes();
}

// Usage examples:
/*
// Soft delete (sets deleted_at timestamp)
$post = Post::find(1);
$post->delete(); // Sets deleted_at, doesn't remove from database

// Force delete (actually removes from database)
$post->forceDelete(); // Permanently deletes

// Restore soft-deleted record
$post->restore(); // Clears deleted_at

// Query only non-deleted (default behavior)
$posts = Post::all(); // Excludes soft-deleted records

// Include soft-deleted records
$posts = Post::withTrashed()->get(); // Includes soft-deleted

// Only soft-deleted records
$posts = Post::onlyTrashed()->get(); // Only soft-deleted

// Check if record is soft-deleted
if ($post->trashed()) {
    echo "This post has been deleted";
}

// Restore all soft-deleted posts
Post::onlyTrashed()->restore();

// Force delete all soft-deleted posts
Post::onlyTrashed()->forceDelete();
*/

