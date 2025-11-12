<?php

declare(strict_types=1);

/**
 * Eloquent Scopes Examples
 * 
 * This demonstrates scopes in Eloquent: global scopes and local scopes.
 * Compare to Django's Meta.ordering and queryset methods.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Post extends Model
{
    protected $fillable = ['title', 'content', 'published_at', 'author_id'];
    
    /**
     * Global Scope: Applied to all queries
     * Equivalent to Django's Meta.ordering
     * 
     * Usage: Post::all() automatically orders by published_at desc
     */
    protected static function booted(): void
    {
        static::addGlobalScope('ordered', function (Builder $query): void {
            $query->orderBy('published_at', 'desc');
        });
    }
    
    /**
     * Local Scope: Reusable query constraint
     * Equivalent to Django's @classmethod queryset methods
     * 
     * Usage: Post::published()->get()
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at');
    }
    
    /**
     * Local Scope with parameters
     * 
     * Usage: Post::byAuthor(1)->get()
     */
    public function scopeByAuthor(Builder $query, int $authorId): Builder
    {
        return $query->where('author_id', $authorId);
    }
    
    /**
     * Local Scope with multiple parameters
     * 
     * Usage: Post::recent(7)->get()
     */
    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('published_at', '>=', now()->subDays($days));
    }
    
    /**
     * Local Scope with relationship
     * 
     * Usage: Post::popular()->get()
     */
    public function scopePopular(Builder $query): Builder
    {
        return $query->withCount('comments')
            ->having('comments_count', '>=', 10)
            ->orderBy('comments_count', 'desc');
    }
    
    /**
     * Local Scope with complex conditions
     * 
     * Usage: Post::featured()->get()
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
    
    /**
     * Dynamic Scope that accepts a closure
     * 
     * Usage: Post::whereAuthor(function ($query) { $query->where('active', true); })->get();
     */
    public function scopeWhereAuthor(Builder $query, callable $callback): Builder
    {
        return $query->whereHas('author', $callback);
    }
}

// ============================================
// Usage Examples
// ============================================

// Single scope
$publishedPosts = Post::published()->get();

// Multiple scopes (chaining)
$posts = Post::published()
    ->byAuthor(1)
    ->recent(30)
    ->get();

// Scope with relationship
$popularPosts = Post::popular()->with('author')->get();

// Scope with additional conditions
$posts = Post::published()
    ->where('title', 'like', '%django%')
    ->get();

// ============================================
// Disabling Global Scopes
// ============================================

// Disable specific global scope
$posts = Post::withoutGlobalScope('ordered')->get();

// Disable all global scopes
$posts = Post::withoutGlobalScopes()->get();

// Disable specific global scope class (example - would need OrderedScope class)
// use App\Scopes\OrderedScope;
// $posts = Post::withoutGlobalScope(OrderedScope::class)->get();

// ============================================
// Usage Examples
// ============================================

// Dynamic scope usage
$posts = Post::whereAuthor(function ($query) {
    $query->where('active', true);
})->get();

// ============================================
// Scope Macros (Advanced)
// ============================================

// Register a macro on Builder
Builder::macro('published', function () {
    return $this->whereNotNull('published_at');
});

// Usage (works on any model)
$posts = Post::published()->get();
$users = User::published()->get(); // If User has published_at column

