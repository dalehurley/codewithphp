<?php

declare(strict_types=1);

/**
 * Laravel URL Generation Examples
 * 
 * This demonstrates Laravel's route() helper for generating URLs.
 * Place routes in routes/web.php
 */

use Illuminate\Support\Facades\Route;

Route::get('/user/{user_id}', function (int $user_id): string {
    return "User {$user_id}";
})->name('user_profile');

Route::get('/example', function (): \Illuminate\Http\RedirectResponse {
    // Generate URL in PHP code
    $url = route('user_profile', ['user_id' => 123]);
    // Returns: '/user/123'
    
    // Redirect to a named route
    return redirect()->route('user_profile', ['user_id' => 123]);
});

// In Blade templates (resources/views/example.blade.php):
// {{ route('user_profile', ['user_id' => 123]) }}
// <a href="{{ route('user_profile', ['user_id' => 123]) }}">View Profile</a>

