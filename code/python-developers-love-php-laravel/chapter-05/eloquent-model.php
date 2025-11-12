<?php

declare(strict_types=1);

/**
 * Eloquent Model Definition
 * 
 * This demonstrates Eloquent ORM model definition with relationships.
 * Compare to Django ORM (django-orm-model.py) and SQLAlchemy (sqlalchemy-model.py).
 * 
 * Place this in app/Models/Post.php in a Laravel application.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Model
{
    /**
     * The attributes that are mass assignable.
     * Similar to Django's form fields or SQLAlchemy's bulk operations.
     */
    protected $fillable = ['name', 'email'];
    
    /**
     * The attributes that should be cast.
     * Similar to Django's field types or SQLAlchemy's column types.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

class Post extends Model
{
    /**
     * The table associated with the model.
     * Eloquent automatically pluralizes: Post -> posts
     * Override if needed: protected $table = 'posts';
     */
    protected $table = 'posts';
    
    /**
     * The attributes that are mass assignable.
     * Similar to Django's form fields or SQLAlchemy's bulk operations.
     * Only these fields can be set via create() or fill().
     */
    protected $fillable = ['title', 'content', 'author_id', 'published_at'];
    
    /**
     * The attributes that should be cast.
     * Similar to Django's field types or SQLAlchemy's column types.
     */
    protected $casts = [
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * Get the author that owns the post.
     * Equivalent to Django's ForeignKey or SQLAlchemy's relationship.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
    
    /**
     * The "booted" method adds global scopes.
     * Similar to Django's Meta.ordering.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('ordered', function ($query): void {
            $query->orderBy('published_at', 'desc');
        });
    }
    
    /**
     * Check if post is published.
     * Equivalent to Django's model method or SQLAlchemy's instance method.
     */
    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }
}

