<?php

declare(strict_types=1);

/**
 * Eloquent Accessors and Mutators Examples
 * 
 * This demonstrates accessors (computed attributes) and mutators (value transformers).
 * Compare to Django's @property and save() override, or SQLAlchemy's @property.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = ['title', 'content', 'published_at', 'slug', 'metadata'];
    
    /**
     * Accessor: Computed attribute
     * Equivalent to Django's @property or SQLAlchemy's @property
     * 
     * Usage: $post->excerpt (automatically calls getExcerptAttribute)
     * 
     * Naming convention: get{AttributeName}Attribute()
     */
    public function getExcerptAttribute(): string
    {
        $content = $this->attributes['content'] ?? '';
        return strlen($content) > 150 
            ? substr($content, 0, 150) . '...' 
            : $content;
    }
    
    /**
     * Accessor: Boolean check
     * 
     * Usage: $post->is_published (automatically calls getIsPublishedAttribute)
     */
    public function getIsPublishedAttribute(): bool
    {
        return $this->published_at !== null;
    }
    
    /**
     * Accessor: Calculate reading time
     * 
     * Usage: $post->reading_time
     */
    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count($this->attributes['content'] ?? '');
        $wordsPerMinute = 200;
        return (int) ceil($wordCount / $wordsPerMinute);
    }
    
    /**
     * Accessor: Format date
     * 
     * Usage: $post->formatted_date
     */
    public function getFormattedDateAttribute(): ?string
    {
        return $this->published_at 
            ? $this->published_at->format('F j, Y')
            : null;
    }
    
    /**
     * Mutator: Transform value before saving
     * Equivalent to Django's save() override or SQLAlchemy's setter
     * 
     * Usage: $post->title = 'my title' (automatically calls setTitleAttribute)
     * 
     * Naming convention: set{AttributeName}Attribute()
     */
    public function setTitleAttribute(string $value): void
    {
        // Capitalize first letter and trim whitespace
        $this->attributes['title'] = ucfirst(trim($value));
    }
    
    /**
     * Mutator: Generate slug from title
     * 
     * Usage: $post->title = 'My Post' (slug is auto-generated)
     */
    public function setSlugAttribute(?string $value): void
    {
        // Only generate slug if not provided
        $this->attributes['slug'] = $value ?? Str::slug($this->attributes['title'] ?? '');
    }
    
    /**
     * Mutator: Transform JSON
     * 
     * Usage: $post->metadata = ['key' => 'value'] (automatically JSON encodes)
     */
    public function setMetadataAttribute($value): void
    {
        $this->attributes['metadata'] = is_array($value) 
            ? json_encode($value) 
            : $value;
    }
    
    /**
     * Accessor: Transform JSON back
     * 
     * Usage: $post->metadata (automatically JSON decodes)
     */
    public function getMetadataAttribute(?string $value): ?array
    {
        return $value ? json_decode($value, true) : null;
    }
    
    /**
     * Mutator: Hash password (example)
     * 
     * Usage: $user->password = 'plaintext' (automatically hashed)
     */
    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = bcrypt($value);
    }
}

// ============================================
// Usage Examples
// ============================================

// Accessors (computed attributes)
$post = Post::find(1);
echo $post->excerpt; // Returns first 150 characters + "..."
echo $post->is_published; // Returns true/false
echo $post->reading_time; // Returns estimated reading time in minutes
echo $post->formatted_date; // Returns "January 15, 2024"

// Mutators (value transformers)
$post = new Post();
$post->title = '  my post title  '; // Saved as "My post title" (capitalized, trimmed)
$post->slug = null; // Auto-generated from title: "my-post-title"
$post->metadata = ['key' => 'value']; // Saved as JSON string
$post->save();

// Access transformed data
echo $post->title; // "My post title"
echo $post->slug; // "my-post-title"
print_r($post->metadata); // ['key' => 'value'] (decoded from JSON)

// ============================================
// Accessor with Relationship
// ============================================

class Post extends Model
{
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Accessor using relationship
     * 
     * Usage: $post->author_name
     */
    public function getAuthorNameAttribute(): ?string
    {
        return $this->author?->name;
    }
}

// ============================================
// Custom Accessor/Mutator Names
// ============================================

class Post extends Model
{
    /**
     * Accessor with custom name
     * Access via: $post->summary (not $post->get_summary)
     */
    public function getSummaryAttribute(): string
    {
        return substr($this->content, 0, 100);
    }
    
    /**
     * Mutator with custom name
     * Set via: $post->summary = 'value' (not $post->set_summary)
     */
    public function setSummaryAttribute(string $value): void
    {
        // This would need a summary column in database
        $this->attributes['summary'] = $value;
    }
}

// ============================================
// Accessor/Mutator with Type Casting
// ============================================

class Post extends Model
{
    protected $casts = [
        'published_at' => 'datetime',
        'metadata' => 'array', // Automatically JSON encode/decode
    ];
    
    /**
     * If using $casts for JSON, you don't need mutator/accessor
     * But you can still add custom logic
     */
    public function setMetadataAttribute($value): void
    {
        // Custom validation or transformation
        if (is_array($value)) {
            $this->attributes['metadata'] = json_encode($value);
        }
    }
}

