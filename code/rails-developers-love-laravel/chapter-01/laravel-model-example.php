<?php
// Laravel Model Example
// app/Models/Post.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Post extends Model
{
    use HasFactory;

    // Fillable attributes (whitelist for mass assignment)
    protected $fillable = [
        'user_id',
        'title',
        'body',
        'slug',
        'published',
        'published_at',
        'status',
    ];

    // Type casting
    protected $casts = [
        'published' => 'boolean',
        'published_at' => 'datetime',
        'status' => 'string',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Model events (observers are also available)
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->generateSlug();
        });

        static::created(function ($model) {
            $model->notifyUser();
        });
    }

    // Methods
    public function publish(): void
    {
        $this->update([
            'published' => true,
            'published_at' => now(),
            'status' => 'published',
        ]);
    }

    public function isDraft(): bool
    {
        return !$this->published;
    }

    public function excerpt(int $length = 200): string
    {
        return str($this->body)->limit($length);
    }

    private function generateSlug(): void
    {
        if (!$this->slug) {
            $this->slug = str($this->title)->slug();
        }
    }

    private function notifyUser(): void
    {
        // UserMailer::postCreated($this)->dispatch();
        // Or using Notification:
        // $this->user->notify(new PostCreated($this));
    }
}

// app/Models/User.php
class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Automatically hashes on set
    ];

    // Relationships
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // Methods
    public function postsCount(): int
    {
        return $this->posts()->count();
    }
}







