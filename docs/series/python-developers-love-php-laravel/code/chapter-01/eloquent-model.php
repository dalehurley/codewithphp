<?php

declare(strict_types=1);

/**
 * Eloquent Model Definition
 * 
 * This demonstrates Eloquent ORM model definition with relationships.
 * Place this in app/Models/Post.php in a Laravel application.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['title', 'content', 'author_id'];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published_at' => 'datetime',
    ];
    
    /**
     * Get the author that owns the post.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * The "booted" method of the model.
     * Adds a global scope to order posts by published_at descending.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('ordered', function ($query): void {
            $query->orderBy('published_at', 'desc');
        });
    }
}

