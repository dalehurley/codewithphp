<?php

/**
 * Eloquent ORM Basics
 *
 * Complete guide to Laravel Eloquent compared to TypeORM.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

// ===== Basic Model Definition =====

class User extends Model
{
    // Table name (optional - auto-detects as 'users')
    protected $table = 'users';

    // Primary key (optional - defaults to 'id')
    protected $primaryKey = 'id';

    // Timestamps (optional - defaults to true)
    public $timestamps = true;

    // Mass assignable attributes
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    // Hidden attributes (for JSON)
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Cast attributes to specific types
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'settings' => 'array', // Auto JSON encode/decode
    ];

    // Default attribute values
    protected $attributes = [
        'is_active' => true,
    ];
}

// ===== CRUD Operations =====

// CREATE
$user = new User();
$user->name = 'Alice';
$user->email = 'alice@example.com';
$user->save();

// Or using mass assignment
$user = User::create([
    'name' => 'Bob',
    'email' => 'bob@example.com',
    'password' => bcrypt('secret'),
]);

// READ
$user = User::find(1);                    // Find by ID
$user = User::findOrFail(1);             // Find or throw 404
$users = User::all();                     // Get all
$user = User::where('email', 'alice@example.com')->first();
$users = User::where('is_active', true)->get();

// UPDATE
$user = User::find(1);
$user->name = 'Alice Updated';
$user->save();

// Or update directly
$user->update(['name' => 'Alice Updated']);

// Or update multiple records
User::where('is_active', false)->update(['status' => 'inactive']);

// DELETE
$user = User::find(1);
$user->delete();

// Or delete by ID
User::destroy(1);
User::destroy([1, 2, 3]);

// Soft delete (if using SoftDeletes trait)
$user->delete();           // Soft delete
$user->forceDelete();      // Permanent delete
$user->restore();          // Restore soft deleted

// ===== Relationships =====

class Post extends Model
{
    protected $fillable = ['title', 'content', 'user_id'];

    // Belongs to User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Has many comments
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // Many-to-many tags
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}

class User extends Model
{
    // One-to-many: User has many posts
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    // Many-to-many: User has many roles
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}

// Using relationships
$user = User::find(1);
$posts = $user->posts;                // Get all posts
$firstPost = $user->posts()->first(); // Query posts

$post = Post::find(1);
$author = $post->user;                // Get author

// Eager loading (avoid N+1)
$users = User::with('posts')->get();
$users = User::with(['posts', 'roles'])->get();
$users = User::with('posts.comments')->get(); // Nested

// ===== Query Builder =====

// Where clauses
$users = User::where('name', 'Alice')->get();
$users = User::where('age', '>', 18)->get();
$users = User::where('name', 'like', '%alice%')->get();

// Multiple conditions
$users = User::where('is_active', true)
    ->where('age', '>', 18)
    ->get();

// Or conditions
$users = User::where('name', 'Alice')
    ->orWhere('name', 'Bob')
    ->get();

// WhereIn
$users = User::whereIn('id', [1, 2, 3])->get();

// WhereBetween
$users = User::whereBetween('age', [18, 30])->get();

// WhereNull
$users = User::whereNull('email_verified_at')->get();

// Ordering
$users = User::orderBy('created_at', 'desc')->get();
$users = User::latest()->get();  // Order by created_at desc
$users = User::oldest()->get();  // Order by created_at asc

// Limit & Offset
$users = User::take(10)->get();          // LIMIT 10
$users = User::skip(10)->take(10)->get(); // OFFSET 10 LIMIT 10

// Pagination
$users = User::paginate(15);              // 15 per page
$users = User::simplePaginate(15);        // Simple pagination

// Aggregates
$count = User::count();
$max = User::max('age');
$min = User::min('age');
$avg = User::avg('age');
$sum = User::sum('points');

// Group by
$users = User::groupBy('country')
    ->selectRaw('country, count(*) as total')
    ->get();

// ===== Scopes =====

class User extends Model
{
    // Local scope
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAdults($query)
    {
        return $query->where('age', '>=', 18);
    }

    // Dynamic scope
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
}

// Using scopes
$users = User::active()->get();
$users = User::active()->adults()->get();
$users = User::ofType('admin')->get();

// ===== Accessors & Mutators =====

class User extends Model
{
    // Accessor (get)
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // Mutator (set)
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = bcrypt($value);
    }
}

$user = User::find(1);
echo $user->full_name;  // Uses accessor

$user->password = 'secret';  // Automatically hashed
$user->save();

// ===== Comparison with TypeORM =====

/**
 * TypeORM:
 * ```typescript
 * @Entity()
 * export class User {
 *   @PrimaryGeneratedColumn()
 *   id: number;
 *
 *   @Column()
 *   name: string;
 *
 *   @OneToMany(() => Post, post => post.user)
 *   posts: Post[];
 * }
 *
 * // Usage
 * const users = await userRepository.find();
 * const user = await userRepository.findOne({ where: { id: 1 } });
 * const user = await userRepository.save(newUser);
 * await userRepository.remove(user);
 * ```
 *
 * Eloquent advantages:
 * - Much cleaner syntax
 * - Active Record pattern (model instances)
 * - No repository boilerplate
 * - Automatic timestamps
 * - Built-in pagination
 * - Accessors & mutators
 * - Query scopes
 * - Soft deletes
 */
