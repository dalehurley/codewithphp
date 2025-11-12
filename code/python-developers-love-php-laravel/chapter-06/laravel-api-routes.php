<?php

declare(strict_types=1);

/**
 * Laravel API Routes Example
 * 
 * This file demonstrates how to define API routes in Laravel.
 * Compare this to Flask-RESTful's api.add_resource() and Django REST's router registration.
 * 
 * Location: routes/api.php
 */

use App\Http\Controllers\Api\UserController;

// Individual route definitions
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{user_id}', [UserController::class, 'show']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{user_id}', [UserController::class, 'update']);
Route::patch('/users/{user_id}', [UserController::class, 'update']); // Alternative to PUT
Route::delete('/users/{user_id}', [UserController::class, 'destroy']);

// Or use resource routes (generates all CRUD routes automatically)
// This is equivalent to Flask-RESTful's api.add_resource() or Django REST's router.register()
Route::apiResource('users', UserController::class);

// Note: API routes are automatically prefixed with /api
// So the above routes become:
// GET    /api/users
// GET    /api/users/{user_id}
// POST   /api/users
// PUT    /api/users/{user_id}
// DELETE /api/users/{user_id}

