<?php

/*
|--------------------------------------------------------------------------
| Exercise 1: Personal Info Route
|--------------------------------------------------------------------------
|
| Create a route /about that returns JSON with:
| - Your name
| - Your favorite programming language
| - Years of coding experience
| - Whether you're excited to learn Laravel
|
| Add this to routes/web.php:
*/

use Illuminate\Support\Facades\Route;

Route::get('/about', function () {
    return response()->json([
        'name' => 'Your Name',
        'favorite_language' => 'PHP',
        'years_experience' => 5,
        'excited_for_laravel' => true,
        'learning_goals' => [
            'Build a SaaS application',
            'Master Laravel ecosystem',
            'Deploy to production'
        ]
    ]);
});

/*
|--------------------------------------------------------------------------
| Test the route:
|--------------------------------------------------------------------------
|
| Visit: http://localhost:8000/about
|
| Expected output:
| {
|   "name": "Your Name",
|   "favorite_language": "PHP",
|   "years_experience": 5,
|   "excited_for_laravel": true,
|   "learning_goals": [
|     "Build a SaaS application",
|     "Master Laravel ecosystem",
|     "Deploy to production"
|   ]
| }
*/
