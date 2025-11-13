<?php
/**
 * Chapter 05: Eloquent ORM Examples
 * 
 * This file demonstrates Eloquent query patterns
 * and how they compare to ActiveRecord.
 * 
 * Note: These examples cannot be executed directly.
 * They demonstrate the syntax and patterns used in Laravel.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// ============ BASIC QUERIES ============

// Rails:
// User.all
// User.first
// User.last
// User.find(1)
// User.find_by(email: 'john@example.com')

// Laravel:
// $users = User::all();
// $firstUser = User::first();
// $lastUser = User::latest()->first();
// $user = User::find(1);
// $user = User::where('email', 'john@example.com')->first();

// ============ ADVANCED QUERIES ============

class User extends Model
{
    /**
     * Get all active users
     * 
     * Rails: User.where(active: true)
     */
    public static function getActiveUsers()
    {
        return self::where('active', true)->get();
    }

    /**
     * Get users by email domain
     * 
     * Rails: User.where("email LIKE ?", "%@example.com")
     */
    public static function fromDomain(string $domain)
    {
        return self::where('email', 'like', "%@{$domain}")->get();
    }

    /**
     * Get ordered users
     * 
     * Rails: User.order(created_at: :desc)
     */
    public static function getRecent()
    {
        return self::orderBy('created_at', 'desc')->get();
    }

    /**
     * Get limited users
     * 
     * Rails: User.limit(10)
     */
    public static function getTop10()
    {
        return self::limit(10)->get();
    }

    /**
     * Get paginated users
     * 
     * Rails: User.page(1).per(20)
     */
    public static function getPaginated()
    {
        return self::paginate(20);
    }

    /**
     * Get specific columns
     * 
     * Rails: User.select(:id, :name, :email)
     */
    public static function getSelectedColumns()
    {
        return self::select('id', 'name', 'email')->get();
    }
}

// ============ QUERY SCOPES ============

// Rails:
// class User < ApplicationRecord
//   scope :active, -> { where(active: true) }
//   scope :recent, -> { order(created_at: :desc) }
//   scope :admin, -> { where(role: 'admin') }
// end
//
// User.active.recent.admin

// Laravel:
class UserWithScopes extends Model
{
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeAdmin($query)
    {
        return $query->where('role', 'admin');
    }
}

// Usage:
// $users = UserWithScopes::active()->recent()->admin()->get();

// ============ RELATIONSHIPS ============

// Rails:
// class User < ApplicationRecord
//   has_many :posts
//   has_many :comments
// end
//
// class Post < ApplicationRecord
//   belongs_to :user
//   has_many :comments
// end

// Laravel:
class UserModel extends Model
{
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}

class Post extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

// Usage:
// $user = User::find(1);
// $posts = $user->posts;
// $post = Post::find(1);
// $user = $post->user;

// ============ EAGER LOADING ============

// Rails (prevent N+1 queries):
// posts = Post.includes(:user).all
// posts.each { |post| puts post.user.name }

// Laravel:
// $posts = Post::with('user')->get();
// foreach ($posts as $post) {
//     echo $post->user->name;
// }

// ============ AGGREGATE FUNCTIONS ============

// Rails:
// User.count
// User.where(active: true).count
// Post.pluck(:user_id)
// Post.group_by(:user_id).count

// Laravel:
// User::count()
// User::where('active', true)->count()
// Post::pluck('user_id')
// Post::groupBy('user_id')->selectRaw('user_id, count(*) as count')->get()

// ============ UPDATING RECORDS ============

// Rails:
// user = User.find(1)
// user.update(name: "Jane", email: "jane@example.com")
//
// User.where(active: false).update_all(status: 'inactive')

// Laravel:
// $user = User::find(1);
// $user->update(['name' => 'Jane', 'email' => 'jane@example.com']);
//
// User::where('active', false)->update(['status' => 'inactive']);

// ============ DELETING RECORDS ============

// Rails:
// user = User.find(1)
// user.destroy
//
// User.where(active: false).destroy_all

// Laravel:
// $user = User::find(1);
// $user->delete();
//
// User::where('active', false)->delete();

// ============ MASS ASSIGNMENT ============

// Rails:
// user = User.create(name: "John", email: "john@example.com")

// Laravel (using $fillable):
// $user = User::create(['name' => 'John', 'email' => 'john@example.com']);

// Laravel model with $fillable:
class SafeUser extends Model
{
    protected $fillable = ['name', 'email', 'password'];
    // Prevents mass assignment to other fields like 'admin', 'is_superuser'
}

// ============ TRANSACTIONS ============

// Rails:
// User.transaction do
//   user = User.create(name: "John")
//   user.posts.create(title: "Hello")
// end

// Laravel:
// use Illuminate\Support\Facades\DB;
//
// DB::transaction(function () {
//     $user = User::create(['name' => 'John']);
//     $user->posts()->create(['title' => 'Hello']);
// });

echo "Eloquent ORM patterns demonstrated.\n";
echo "These are examples showing syntax, not executable code.\n";

