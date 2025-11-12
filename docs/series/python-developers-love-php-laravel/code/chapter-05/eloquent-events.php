<?php

declare(strict_types=1);

/**
 * Eloquent Model Events and Observers Examples
 * 
 * This demonstrates model events and observers in Eloquent.
 * Compare to Django signals or SQLAlchemy events.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = ['title', 'content', 'published_at', 'slug'];
    
    /**
     * Model lifecycle events (hooks) in booted() method
     * Equivalent to Django signals or SQLAlchemy events
     */
    protected static function booted(): void
    {
        // ============================================
        // CREATING: Before a new model is saved
        // Equivalent to Django's pre_save signal
        // ============================================
        static::creating(function (Post $post): void {
            // Generate slug if not provided
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
            
            // Set default published_at if not set
            if (empty($post->published_at)) {
                $post->published_at = null; // Keep as draft
            }
        });
        
        // ============================================
        // CREATED: After a new model is saved
        // Equivalent to Django's post_save signal with created=True
        // ============================================
        static::created(function (Post $post): void {
            // Send notification, update cache, etc.
            \Log::info("Post created: {$post->title}");
            
            // Example: Send email notification
            // Mail::to($post->author->email)->send(new PostCreated($post));
        });
        
        // ============================================
        // UPDATING: Before an existing model is updated
        // Equivalent to Django's pre_save signal (for updates)
        // ============================================
        static::updating(function (Post $post): void {
            // Update slug if title changed
            if ($post->isDirty('title')) {
                $post->slug = Str::slug($post->title);
            }
        });
        
        // ============================================
        // UPDATED: After an existing model is updated
        // Equivalent to Django's post_save signal (for updates)
        // ============================================
        static::updated(function (Post $post): void {
            // Clear cache, update search index, etc.
            \Log::info("Post updated: {$post->title}");
            
            // Example: Clear cache
            // Cache::forget("post.{$post->id}");
        });
        
        // ============================================
        // DELETING: Before a model is deleted
        // Equivalent to Django's pre_delete signal
        // ============================================
        static::deleting(function (Post $post): void {
            // Clean up related data, archive, etc.
            \Log::info("Post being deleted: {$post->title}");
            
            // Example: Delete related comments
            // $post->comments()->delete();
        });
        
        // ============================================
        // DELETED: After a model is deleted
        // Equivalent to Django's post_delete signal
        // ============================================
        static::deleted(function (Post $post): void {
            // Final cleanup, logging, etc.
            \Log::info("Post deleted: {$post->title}");
        });
        
        // ============================================
        // SAVING: Before save (both create and update)
        // ============================================
        static::saving(function (Post $post): void {
            // Common logic for both create and update
            // Example: Normalize data
            $post->title = trim($post->title);
        });
        
        // ============================================
        // SAVED: After save (both create and update)
        // ============================================
        static::saved(function (Post $post): void {
            // Common logic for both create and update
            // Example: Update search index
        });
    }
}

// ============================================
// USING OBSERVERS (Better for Complex Logic)
// ============================================

namespace App\Observers;

use App\Models\Post;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PostObserver
{
    /**
     * Handle the Post "creating" event.
     */
    public function creating(Post $post): void
    {
        // Generate slug if not provided
        if (empty($post->slug)) {
            $post->slug = \Str::slug($post->title);
        }
    }
    
    /**
     * Handle the Post "created" event.
     */
    public function created(Post $post): void
    {
        Log::info("Post created: {$post->title}");
        // Send notification, update cache, etc.
    }
    
    /**
     * Handle the Post "updating" event.
     */
    public function updating(Post $post): void
    {
        // Update slug if title changed
        if ($post->isDirty('title')) {
            $post->slug = \Str::slug($post->title);
        }
    }
    
    /**
     * Handle the Post "updated" event.
     */
    public function updated(Post $post): void
    {
        Log::info("Post updated: {$post->title}");
        Cache::forget("post.{$post->id}");
    }
    
    /**
     * Handle the Post "deleting" event.
     */
    public function deleting(Post $post): void
    {
        Log::info("Post being deleted: {$post->title}");
        // Clean up related data
        $post->comments()->delete();
    }
    
    /**
     * Handle the Post "deleted" event.
     */
    public function deleted(Post $post): void
    {
        Log::info("Post deleted: {$post->title}");
    }
}

// ============================================
// REGISTER OBSERVER (In AppServiceProvider)
// ============================================

namespace App\Providers;

use App\Models\Post;
use App\Observers\PostObserver;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register observer
        Post::observe(PostObserver::class);
    }
}

// ============================================
// PREVENTING SAVE/DELETE IN EVENTS
// ============================================

// Return false to cancel the operation
static::creating(function (Post $post) {
    if ($post->title === 'forbidden') {
        return false; // Cancels the create operation
    }
});

static::deleting(function (Post $post) {
    if ($post->is_published) {
        return false; // Cancels the delete operation
    }
});

// ============================================
// EVENT NAMING CONVENTIONS
// ============================================

// Available events:
// - creating, created
// - updating, updated
// - saving, saved
// - deleting, deleted
// - restoring, restored (for soft deletes)
// - retrieved (after model is fetched from database)

// ============================================
// USING EVENTS IN TESTS
// ============================================

// Disable events during testing
Post::withoutEvents(function () {
    Post::create(['title' => 'Test']);
});

// Or disable for specific model instance
$post = new Post();
$post->withoutEvents(function () use ($post) {
    $post->save();
});

