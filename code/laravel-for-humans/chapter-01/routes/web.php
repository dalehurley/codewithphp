<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeController;

/*
|--------------------------------------------------------------------------
| Web Routes - Chapter 01 Examples
|--------------------------------------------------------------------------
|
| Here are all the route examples from Chapter 01. These demonstrate:
| - Basic routes with closures
| - Routes with parameters
| - JSON responses
| - Controller-based routes
|
*/

// Default Laravel welcome route
Route::get('/', [WelcomeController::class, 'index']);

// Simple text response
Route::get('/hello', function () {
    return 'Hello from TaskFlow!';
});

// Route with dynamic parameter
Route::get('/greet/{name}', [WelcomeController::class, 'greet']);

// JSON API response
Route::get('/api/status', [WelcomeController::class, 'status']);

// Configuration info route
Route::get('/info', function () {
    return [
        'app' => config('app.name'),
        'environment' => config('app.env'),
        'debug' => config('app.debug'),
        'timezone' => config('app.timezone'),
    ];
});

// Named route example
Route::get('/dashboard', function () {
    return 'Dashboard';
})->name('dashboard');

// Route group example
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return 'Admin Dashboard';
    })->name('admin.dashboard');

    Route::get('/users', function () {
        return 'User Management';
    })->name('admin.users');

    Route::get('/settings', function () {
        return 'Settings';
    })->name('admin.settings');
});

// Multiple HTTP methods
Route::match(['get', 'post'], '/form', function () {
    if (request()->isMethod('post')) {
        return 'Form submitted!';
    }
    return 'Show form';
});

// Route with optional parameter
Route::get('/welcome/{name?}', function (?string $name = null) {
    if ($name) {
        return "Welcome, {$name}!";
    }
    return 'Welcome, Guest!';
});

// Route with constraints (only numbers)
Route::get('/user/{id}', function (string $id) {
    return "User ID: {$id}";
})->where('id', '[0-9]+');

// Route with multiple parameters
Route::get('/posts/{category}/{slug}', function (string $category, string $slug) {
    return "Category: {$category}, Slug: {$slug}";
});
