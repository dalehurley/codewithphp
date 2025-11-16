<?php
// Laravel Routing Example
// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Api\V1\PostController as ApiPostController;
use App\Http\Controllers\Api\V1\UserController as ApiUserController;

// Root route
Route::get('/', [PageController::class, 'home'])->name('home');

// RESTful resource
Route::resource('posts', PostController::class);

// Nested resource
Route::resource('posts.comments', CommentController::class);

// Specific routes
Route::get('about', [PageController::class, 'about'])->name('about');
Route::post('contact', [PageController::class, 'contact'])->name('contact');

// Admin namespace
Route::namespace('Admin')->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', AdminUserController::class);
    Route::resource('posts', AdminPostController::class);
});

// API namespace with versioning
Route::prefix('api/v1')->name('api.v1.')->group(function () {
    Route::apiResource('posts', ApiPostController::class, ['only' => ['index', 'show']]);
    Route::apiResource('users', ApiUserController::class, ['only' => ['index', 'show']]);
});

// Redirect
Route::redirect('old-posts', 'posts');

// Catch-all (must be last)
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});





