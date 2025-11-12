<?php

/**
 * Laravel Seeder Examples
 * 
 * This file demonstrates Laravel seeders.
 * Compare to Django fixtures (django-fixture.json).
 * 
 * To create a seeder:
 * php artisan make:seeder UserSeeder
 * 
 * To run seeders:
 * php artisan db:seed
 * php artisan db:seed --class=UserSeeder
 * 
 * To migrate and seed:
 * php artisan migrate --seed
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Post;

/**
 * Example: Basic Seeder
 * 
 * Django equivalent: myapp/fixtures/users.json
 */
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds
     */
    public function run(): void
    {
        // Create specific users
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => bcrypt('password123'),
        ]);

        // Create multiple users using factory
        User::factory()->count(10)->create();
    }
}

/**
 * Example: Seeder with Relationships
 */
class PostSeeder extends Seeder
{
    public function run(): void
    {
        // Get existing users
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run UserSeeder first.');
            return;
        }

        // Create posts for each user
        foreach ($users as $user) {
            Post::factory()->count(3)->create([
                'user_id' => $user->id,
            ]);
        }

        $this->command->info('Created posts for all users.');
    }
}

/**
 * Example: Conditional Seeding
 */
class ConditionalSeeder extends Seeder
{
    public function run(): void
    {
        // Only seed if database is empty
        if (User::count() > 0) {
            $this->command->warn('Users already exist. Skipping seeding.');
            return;
        }

        User::factory()->count(5)->create();
        $this->command->info('Seeded 5 users.');
    }
}

/**
 * Example: Seeder with Progress Bar
 */
class ProgressSeeder extends Seeder
{
    public function run(): void
    {
        $count = 100;
        $bar = $this->command->getOutput()->createProgressBar($count);
        $bar->start();

        for ($i = 0; $i < $count; $i++) {
            User::factory()->create();
            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info("Created {$count} users.");
    }
}

/**
 * Example: DatabaseSeeder - Main Seeder
 * 
 * This is the main seeder that calls other seeders.
 * Django equivalent: management/commands/seed_data.py
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database
     */
    public function run(): void
    {
        // Call other seeders
        $this->call([
            UserSeeder::class,
            PostSeeder::class,
        ]);

        // Or run seeders conditionally
        if (app()->environment('local')) {
            $this->call([
                ConditionalSeeder::class,
            ]);
        }
    }
}

