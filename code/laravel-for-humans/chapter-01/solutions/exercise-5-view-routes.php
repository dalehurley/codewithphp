<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Exercise 5: View Routes
|--------------------------------------------------------------------------
|
| Create a new view in resources/views/hello.blade.php
| Make it display HTML with your name
| Create a route that returns this view
|
| Step 1: Create resources/views/hello.blade.php
| (See hello.blade.php file in this solutions directory)
|
| Step 2: Add this route to routes/web.php:
*/

Route::get('/hello', function () {
    return view('hello');
})->name('hello');

/*
|--------------------------------------------------------------------------
| Passing Data to Views
|--------------------------------------------------------------------------
|
| You can pass data to views in multiple ways:
*/

// Method 1: Array notation
Route::get('/welcome/{name}', function (string $name) {
    return view('welcome-user', ['name' => $name]);
})->name('welcome.user');

// Method 2: with() method
Route::get('/profile/{id}', function (int $id) {
    return view('profile')
        ->with('userId', $id)
        ->with('userName', 'John Doe');
})->name('profile');

// Method 3: compact() helper
Route::get('/dashboard', function () {
    $username = 'Alice';
    $role = 'Admin';
    $tasks = ['Task 1', 'Task 2', 'Task 3'];

    return view('dashboard', compact('username', 'role', 'tasks'));
})->name('dashboard.view');

/*
|--------------------------------------------------------------------------
| View with Controller
|--------------------------------------------------------------------------
|
| For better organization, use a controller:
|
| php artisan make:controller PageController
|
| Then in PageController.php:
|
| public function home()
| {
|     return view('home', [
|         'title' => 'Welcome to TaskFlow',
|         'description' => 'Manage your projects efficiently'
|     ]);
| }
|
| And in routes/web.php:
| Route::get('/', [PageController::class, 'home'])->name('home');
*/

/*
|--------------------------------------------------------------------------
| Checking if a View Exists
|--------------------------------------------------------------------------
*/

use Illuminate\Support\Facades\View;

Route::get('/safe-view', function () {
    if (View::exists('custom-page')) {
        return view('custom-page');
    }

    return response('View not found', 404);
});

/*
|--------------------------------------------------------------------------
| Returning View Directly (No Closure)
|--------------------------------------------------------------------------
|
| For simple views without logic, use Route::view():
*/

Route::view('/about', 'about', [
    'title' => 'About TaskFlow',
    'version' => '1.0.0'
])->name('about');

Route::view('/contact', 'contact')->name('contact');

/*
|--------------------------------------------------------------------------
| View Composers (Advanced)
|--------------------------------------------------------------------------
|
| Share data across multiple views using view composers.
| Add this to a Service Provider (e.g., AppServiceProvider.php):
|
| use Illuminate\Support\Facades\View;
|
| public function boot()
| {
|     // Share data with specific view
|     View::composer('dashboard', function ($view) {
|         $view->with('appName', config('app.name'));
|     });
|
|     // Share data with all views
|     View::share('currentYear', date('Y'));
| }
*/

/*
|--------------------------------------------------------------------------
| Test the routes:
|--------------------------------------------------------------------------
|
| After creating the view files, visit:
|
| http://localhost:8000/hello
| http://localhost:8000/welcome/Alice
| http://localhost:8000/profile/123
| http://localhost:8000/dashboard
| http://localhost:8000/about
| http://localhost:8000/contact
*/

/*
|--------------------------------------------------------------------------
| Example View File: resources/views/hello.blade.php
|--------------------------------------------------------------------------
|
| <!DOCTYPE html>
| <html lang="en">
| <head>
|     <meta charset="UTF-8">
|     <meta name="viewport" content="width=device-width, initial-scale=1.0">
|     <title>Hello</title>
|     <style>
|         body {
|             font-family: Arial, sans-serif;
|             max-width: 800px;
|             margin: 50px auto;
|             padding: 20px;
|         }
|         h1 {
|             color: #FF2D20;
|         }
|     </style>
| </head>
| <body>
|     <h1>Hello from Laravel!</h1>
|     <p>My name is <strong>Your Name</strong></p>
|     <p>I'm learning Laravel and loving it!</p>
|     <p><a href="/">Back to Home</a></p>
| </body>
| </html>
*/
