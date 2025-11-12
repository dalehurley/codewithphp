<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WelcomeController;

/*
|--------------------------------------------------------------------------
| Exercise 3: Named Routes
|--------------------------------------------------------------------------
|
| Laravel lets you name routes for easy reference. This makes it easier
| to generate URLs and change routes without breaking your application.
|
| Add these to routes/web.php:
*/

// Name existing routes
Route::get('/', [WelcomeController::class, 'index'])->name('home');
Route::get('/greet/{name}', [WelcomeController::class, 'greet'])->name('greet');
Route::get('/api/status', [WelcomeController::class, 'status'])->name('api.status');

// Create a routes listing endpoint
Route::get('/routes', function () {
    return response()->json([
        'routes' => [
            'home' => route('home'),
            'greet' => route('greet', ['name' => 'John']),
            'api_status' => route('api.status'),
            'dashboard' => route('dashboard'),
            'admin_dashboard' => route('admin.dashboard'),
            'admin_users' => route('admin.users'),
        ],
        'note' => 'These URLs are generated using the route() helper'
    ]);
})->name('routes.list');

/*
|--------------------------------------------------------------------------
| Using named routes in your code:
|--------------------------------------------------------------------------
|
| // Generate URL
| $url = route('greet', ['name' => 'Alice']);
| // Returns: http://localhost:8000/greet/Alice
|
| // In Blade templates
| <a href="{{ route('home') }}">Home</a>
|
| // Redirect to named route
| return redirect()->route('dashboard');
|
| // Check current route
| if (request()->routeIs('home')) {
|     // Do something
| }
*/

/*
|--------------------------------------------------------------------------
| Benefits of named routes:
|--------------------------------------------------------------------------
|
| 1. URL generation: route('greet', ['name' => 'John'])
| 2. Easy to change URLs without breaking links
| 3. More readable than hardcoded URLs
| 4. IDE autocomplete support
| 5. Easier testing and refactoring
*/

/*
|--------------------------------------------------------------------------
| Test the route:
|--------------------------------------------------------------------------
|
| Visit: http://localhost:8000/routes
|
| Expected output:
| {
|   "routes": {
|     "home": "http://localhost:8000",
|     "greet": "http://localhost:8000/greet/John",
|     "api_status": "http://localhost:8000/api/status",
|     "dashboard": "http://localhost:8000/dashboard",
|     "admin_dashboard": "http://localhost:8000/admin/dashboard",
|     "admin_users": "http://localhost:8000/admin/users"
|   },
|   "note": "These URLs are generated using the route() helper"
| }
*/
