<?php

declare(strict_types=1);

/**
 * Laravel Route Rendering Blade Template
 * 
 * This demonstrates how Laravel routes render Blade templates with data.
 * Place this in routes/web.php in a Laravel application.
 * 
 * Run with: php artisan serve (after setting up Laravel)
 */

use App\Models\Post;
use Illuminate\Support\Facades\Route;

Route::get('/posts', function (): \Illuminate\View\View {
    $posts = Post::all();
    return view('blog.post-list', ['posts' => $posts]);
});

