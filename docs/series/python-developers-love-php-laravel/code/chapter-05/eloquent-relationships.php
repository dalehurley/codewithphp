<?php

declare(strict_types=1);

/**
 * Eloquent Relationships
 * 
 * This demonstrates Eloquent relationships: one-to-one, one-to-many, many-to-many.
 * Compare to Django ORM (django-relationships.py) and SQLAlchemy (sqlalchemy-relationships.php).
 * 
 * Place these in app/Models/ directory in a Laravel application.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Model
{
    protected $fillable = ['name', 'email'];
    
    /**
     * One-to-many: User has many Posts
     * Equivalent to Django's ForeignKey with related_name='posts'
     * or SQLAlchemy's relationship('Post', backref='author')
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
        // Eloquent convention: foreign key is 'author_id' (snake_case of method name + '_id')
    }
    
    /**
     * One-to-one: User has one Profile
     * Equivalent to Django's OneToOneField or SQLAlchemy's uselist=False
     */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
        // Eloquent convention: foreign key is 'user_id' in profiles table
    }
}

class Post extends Model
{
    protected $fillable = ['title', 'content', 'author_id'];
    
    /**
     * Many-to-one: Post belongs to User (author)
     * Equivalent to Django's ForeignKey or SQLAlchemy's ForeignKey column
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
        // Eloquent convention: foreign key is 'author_id'
    }
    
    /**
     * Many-to-many: Post has many Tags
     * Equivalent to Django's ManyToManyField or SQLAlchemy's secondary relationship
     */
    public function tags(): BelongsToMany
    {
        // Eloquent auto-detects: post_tag table, post_id, tag_id
        return $this->belongsToMany(Tag::class);
        
        // Or explicitly specify:
        // return $this->belongsToMany(Tag::class, 'post_tag', 'post_id', 'tag_id');
    }
}

class Profile extends Model
{
    protected $fillable = ['bio', 'user_id'];
    
    /**
     * One-to-one inverse: Profile belongs to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

class Tag extends Model
{
    protected $fillable = ['name'];
    
    /**
     * Many-to-many inverse: Tag has many Posts
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }
}

// Usage examples:
/*
// One-to-many
$user = User::find(1);
$posts = $user->posts; // Get all posts by user (collection)

$post = Post::find(1);
$author = $post->author; // Get post's author

// One-to-one
$user = User::find(1);
$profile = $user->profile; // Get user's profile

$profile = Profile::find(1);
$user = $profile->user; // Get profile's user

// Many-to-many
$post = Post::find(1);
$tags = $post->tags; // Get all tags for post (collection)

$tag = Tag::find(1);
$posts = $tag->posts; // Get all posts with tag (collection)

// Add/remove tags
$post->tags()->attach($tagId);
$post->tags()->detach($tagId);
$post->tags()->sync([$tagId1, $tagId2]); // Sync all tags (removes others)
$post->tags()->syncWithoutDetaching([$tagId]); // Add without removing

// Clear all tags
$post->tags()->detach();
*/

