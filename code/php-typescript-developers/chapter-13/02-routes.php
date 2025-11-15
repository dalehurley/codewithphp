<?php

/**
 * Laravel Routing Examples
 *
 * This file demonstrates various Laravel routing patterns.
 * These examples would typically go in routes/web.php or routes/api.php
 */

// ===== Basic Routes =====

// GET request
Route::get('/users', function () {
    return ['users' => ['Alice', 'Bob']];
});

// POST request
Route::post('/users', function (Request $request) {
    return ['message' => 'User created', 'data' => $request->all()];
});

// PUT request
Route::put('/users/{id}', function ($id, Request $request) {
    return ['message' => "User {$id} updated", 'data' => $request->all()];
});

// DELETE request
Route::delete('/users/{id}', function ($id) {
    return ['message' => "User {$id} deleted"];
});

// ===== Route Parameters =====

// Required parameter
Route::get('/users/{id}', function ($id) {
    return ['user' => "User {$id}"];
});

// Optional parameter
Route::get('/posts/{id?}', function ($id = null) {
    return $id ? ['post' => "Post {$id}"] : ['posts' => 'All posts'];
});

// Multiple parameters
Route::get('/posts/{postId}/comments/{commentId}', function ($postId, $commentId) {
    return ['post' => $postId, 'comment' => $commentId];
});

// Parameter constraints
Route::get('/users/{id}', function ($id) {
    return ['user' => "User {$id}"];
})->where('id', '[0-9]+'); // Only numbers

Route::get('/posts/{slug}', function ($slug) {
    return ['post' => $slug];
})->where('slug', '[a-z-]+'); // Only lowercase letters and dashes

// ===== Controller Routes =====

use App\Http\Controllers\UserController;

// Single action
Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);

// Resource routes (shorthand for CRUD)
Route::resource('posts', PostController::class);
// Equivalent to:
// GET    /posts           -> index()
// GET    /posts/create    -> create()
// POST   /posts           -> store()
// GET    /posts/{id}      -> show()
// GET    /posts/{id}/edit -> edit()
// PUT    /posts/{id}      -> update()
// DELETE /posts/{id}      -> destroy()

// API Resource (no create/edit views)
Route::apiResource('tasks', TaskController::class);
// Equivalent to:
// GET    /tasks      -> index()
// POST   /tasks      -> store()
// GET    /tasks/{id} -> show()
// PUT    /tasks/{id} -> update()
// DELETE /tasks/{id} -> destroy()

// ===== Route Groups =====

// Prefix group
Route::prefix('api')->group(function () {
    Route::get('/users', [UserController::class, 'index']);      // /api/users
    Route::get('/posts', [PostController::class, 'index']);      // /api/posts
});

// Middleware group
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/profile', [ProfileController::class, 'show']);
});

// Combined prefix + middleware
Route::prefix('admin')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::get('/settings', [AdminSettingsController::class, 'index']);
    });

// Namespace group (for organizing controllers)
Route::namespace('Admin')->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index']);
});

// ===== Named Routes =====

Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
Route::post('/users', [UserController::class, 'store'])->name('users.store');

// Generate URL from named route
// route('users.show', ['id' => 1]) // /users/1
// route('users.store')              // /users

// ===== Middleware =====

// Single middleware
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth');

// Multiple middleware
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware(['auth', 'admin', 'verified']);

// Middleware with parameters
Route::get('/posts/{id}', [PostController::class, 'show'])
    ->middleware('throttle:60,1'); // 60 requests per minute

// ===== Route Model Binding =====

// Automatic model injection
Route::get('/users/{user}', function (User $user) {
    // Laravel automatically finds User by ID
    return $user;
});

Route::put('/posts/{post}', function (Post $post, Request $request) {
    // $post is automatically loaded
    $post->update($request->all());
    return $post;
});

// Custom binding key
Route::get('/users/{user:email}', function (User $user) {
    // Find user by email instead of ID
    return $user;
});

// ===== Fallback Routes =====

Route::fallback(function () {
    return response()->json(['error' => 'Not Found'], 404);
});

// ===== Rate Limiting =====

Route::middleware('throttle:10,1')->group(function () {
    // 10 requests per minute
    Route::get('/api/expensive-operation', function () {
        return ['result' => 'data'];
    });
});

// ===== CORS =====

// In routes/api.php, Laravel automatically adds 'api' prefix and CORS middleware
Route::get('/users', [UserController::class, 'index']);
// Accessible at: /api/users

// ===== Response Types =====

// JSON response
Route::get('/json', function () {
    return ['message' => 'Hello JSON'];
    // Or explicitly:
    // return response()->json(['message' => 'Hello JSON']);
});

// Custom status code
Route::post('/users', function (Request $request) {
    return response()->json(['message' => 'Created'], 201);
});

// No content response
Route::delete('/users/{id}', function ($id) {
    return response()->noContent();
});

// Redirect
Route::get('/old-url', function () {
    return redirect('/new-url');
});

// ===== Comparison with Express.js =====

/*
Express.js:
```javascript
app.get('/users/:id', (req, res) => {
  const id = req.params.id;
  res.json({ user: `User ${id}` });
});

app.post('/users', (req, res) => {
  const data = req.body;
  res.status(201).json({ message: 'Created', data });
});
```

Laravel:
```php
Route::get('/users/{id}', function ($id) {
    return ['user' => "User {$id}"];
});

Route::post('/users', function (Request $request) {
    return response()->json(['message' => 'Created', 'data' => $request->all()], 201);
});
```

Laravel is more elegant with:
- Automatic JSON responses
- Route model binding
- Named routes
- Resource routes
- Middleware integration
*/
