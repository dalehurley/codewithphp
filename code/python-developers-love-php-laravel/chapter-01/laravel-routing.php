<?php

declare(strict_types=1);

/**
 * Laravel Routing Example
 * 
 * This demonstrates Laravel routing patterns that map from Flask/Django.
 * Place this in routes/web.php in a Laravel application.
 * 
 * Run with: php artisan serve (after setting up Laravel)
 */

use Illuminate\Support\Facades\Route;

Route::get('/', function (): string {
    return 'Hello, World!';
});

Route::get('/user/{user_id}', function (int $user_id): string {
    return "User {$user_id}";
})->where('user_id', '[0-9]+');

Route::match(['get', 'post'], '/post/{slug}', function (string $slug): string {
    if (request()->isMethod('post')) {
        // Handle POST
    }
    return "Post: {$slug}";
})->where('slug', '[a-z0-9-]+');

