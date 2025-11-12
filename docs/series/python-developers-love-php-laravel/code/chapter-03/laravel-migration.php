<?php

/**
 * Laravel Migration Example
 * 
 * This file demonstrates Laravel migrations.
 * Compare to Django migrations (django-migration.py).
 * 
 * To create a migration:
 * php artisan make:migration create_users_table
 * 
 * To run migrations:
 * php artisan migrate
 * 
 * To rollback:
 * php artisan migrate:rollback
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Example: Create users table migration
 * 
 * Django equivalent: myapp/migrations/0001_initial.py
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
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('name'); // VARCHAR(255)
            $table->string('email')->unique(); // VARCHAR(255) with unique index
            $table->timestamp('email_verified_at')->nullable(); // Nullable timestamp
            $table->string('password'); // VARCHAR(255)
            $table->rememberToken(); // VARCHAR(100) nullable, for "remember me" functionality
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
 * Example: Add column to existing table
 * 
 * To create: php artisan make:migration add_phone_to_users_table --table=users
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            // Note: 'after()' is MySQL-specific. For PostgreSQL, use separate migrations or reorder columns manually.
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }
};

/**
 * Example: Create table with foreign key
 * 
 * To create: php artisan make:migration create_posts_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // Equivalent to: $table->unsignedBigInteger('user_id');
            //              $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->boolean('published')->default(false);
            $table->timestamps();
            
            // Add indexes (foreignId already creates an index, but we can add more)
            $table->index('published');
            $table->index(['user_id', 'published']); // Composite index
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};

/**
 * Example: Modify column
 * 
 * To create: php artisan make:migration modify_users_table
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Change column type
            $table->string('name', 500)->change(); // Change VARCHAR length
            
            // Rename column
            $table->renameColumn('name', 'full_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('full_name', 255)->change();
            $table->renameColumn('full_name', 'name');
        });
    }
};

