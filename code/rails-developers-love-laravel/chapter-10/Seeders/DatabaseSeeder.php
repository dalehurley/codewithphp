<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Task;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Create regular users
        $users = User::factory()->count(5)->create();

        // Create categories with specific data
        $categoryData = [
            ['name' => 'Work', 'color' => '#3B82F6'],
            ['name' => 'Personal', 'color' => '#10B981'],
            ['name' => 'Shopping', 'color' => '#F59E0B'],
            ['name' => 'Health', 'color' => '#EF4444'],
            ['name' => 'Learning', 'color' => '#8B5CF6'],
        ];

        $categories = collect($categoryData)->map(function ($data) {
            return Category::factory()->create($data);
        });

        // Create tags
        $tags = Tag::factory()->count(10)->create();

        // Create tasks for each user
        $users->push($admin)->each(function ($user) use ($categories, $tags) {
            Task::factory()->count(10)->create([
                'user_id' => $user->id,
            ])->each(function ($task) use ($categories, $tags) {
                // Attach random categories
                $task->categories()->attach(
                    $categories->random(rand(1, 3))->pluck('id')
                );

                // Attach random tags
                $task->tags()->attach(
                    $tags->random(rand(1, 4))->pluck('id')
                );
            });
        });
    }
}

