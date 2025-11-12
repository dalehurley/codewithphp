<?php

declare(strict_types=1);

/**
 * Laravel Migration Examples
 * 
 * This file demonstrates Laravel migrations with detailed examples.
 * Compare to Django migrations (django-migration-detailed.py).
 * 
 * To create a migration:
 * php artisan make:migration create_users_table
 * 
 * To run migrations:
 * php artisan migrate
 * 
 * To rollback:
 * php artisan migrate:rollback
 * php artisan migrate:rollback --step=1
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Example 1: Create users table migration
 * 
 * To create: php artisan make:migration create_users_table
 */
return new class extends Migration
{
    /**
     * Run the migrations (up)
     * 
     * This method is called when running: php artisan migrate
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id(); // Big integer, auto-increment, primary key
            $table->string('name'); // VARCHAR(255)
            $table->string('email')->unique(); // VARCHAR(255) with unique index
            $table->timestamps(); // created_at and updated_at timestamps
        });
    }
    
    /**
     * Reverse the migrations (down)
     * 
     * This method is called when running: php artisan migrate:rollback
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

/**
 * Example 2: Create posts table with foreign key and indexes
 * 
 * To create: php artisan make:migration create_posts_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table): void {
            $table->id();
            
            // Foreign key with cascade delete
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            // Equivalent to:
            // $table->unsignedBigInteger('author_id');
            // $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->string('title', 200);
            $table->text('content');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            
            // Add indexes
            $table->index('author_id', 'post_author_idx');
            $table->index('published_at', 'post_published_idx');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};

/**
 * Example 3: Add column to existing table
 * 
 * To create: php artisan make:migration add_phone_to_users_table --table=users
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone', 20)->nullable()->after('email');
            // Note: 'after()' is MySQL-specific. For PostgreSQL, use separate migrations or reorder columns manually.
        });
    }
    
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('phone');
        });
    }
};

/**
 * Example 4: Modify column
 * 
 * To create: php artisan make:migration modify_users_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // Change column type
            $table->string('name', 500)->change(); // Change VARCHAR length
            
            // Rename column
            $table->renameColumn('name', 'full_name');
        });
    }
    
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('full_name', 255)->change();
            $table->renameColumn('full_name', 'name');
        });
    }
};

/**
 * Example 5: Create many-to-many pivot table
 * 
 * To create: php artisan make:migration create_post_tag_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_tag', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Ensure unique combination
            $table->unique(['post_id', 'tag_id']);
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('post_tag');
    }
};

