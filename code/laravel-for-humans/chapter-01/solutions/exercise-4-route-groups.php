<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Exercise 4: Route Groups
|--------------------------------------------------------------------------
|
| Create a group of routes under /admin prefix with routes for:
| - dashboard
| - users
| - settings
|
| Add this to routes/web.php:
*/

Route::prefix('admin')->group(function () {
    // Admin Dashboard
    Route::get('/dashboard', function () {
        return response()->json([
            'page' => 'Admin Dashboard',
            'stats' => [
                'total_users' => 150,
                'active_projects' => 42,
                'pending_tasks' => 87
            ]
        ]);
    })->name('admin.dashboard');

    // User Management
    Route::get('/users', function () {
        return response()->json([
            'page' => 'User Management',
            'users' => [
                ['id' => 1, 'name' => 'Alice Johnson', 'role' => 'Admin'],
                ['id' => 2, 'name' => 'Bob Smith', 'role' => 'User'],
                ['id' => 3, 'name' => 'Charlie Brown', 'role' => 'User'],
            ]
        ]);
    })->name('admin.users');

    // Settings
    Route::get('/settings', function () {
        return response()->json([
            'page' => 'Settings',
            'settings' => [
                'site_name' => 'TaskFlow',
                'maintenance_mode' => false,
                'registration_enabled' => true,
                'email_notifications' => true
            ]
        ]);
    })->name('admin.settings');
});

/*
|--------------------------------------------------------------------------
| Advanced: Nested Groups with Middleware
|--------------------------------------------------------------------------
|
| You can nest groups and apply middleware:
*/

Route::prefix('admin')->name('admin.')->group(function () {
    // Public admin routes (no auth required for demo)
    Route::get('/login', function () {
        return 'Admin Login Page';
    })->name('login');

    // Protected admin routes (would use auth middleware in real app)
    Route::middleware([])->group(function () { // Empty middleware for demo
        Route::get('/dashboard', function () {
            return 'Protected Admin Dashboard';
        })->name('dashboard.protected');

        Route::get('/reports', function () {
            return 'Admin Reports';
        })->name('reports');
    });
});

/*
|--------------------------------------------------------------------------
| Advanced: Route Group with Multiple Attributes
|--------------------------------------------------------------------------
|
| Groups can have multiple attributes:
*/

Route::group([
    'prefix' => 'api/v1',
    'as' => 'api.v1.',
    // 'middleware' => ['api', 'auth:sanctum'], // Commented for demo
], function () {
    Route::get('/tasks', function () {
        return response()->json([
            'version' => 'v1',
            'endpoint' => 'tasks',
            'data' => [
                ['id' => 1, 'title' => 'Task 1', 'completed' => false],
                ['id' => 2, 'title' => 'Task 2', 'completed' => true],
            ]
        ]);
    })->name('tasks.index');

    Route::get('/projects', function () {
        return response()->json([
            'version' => 'v1',
            'endpoint' => 'projects',
            'data' => [
                ['id' => 1, 'name' => 'Project Alpha'],
                ['id' => 2, 'name' => 'Project Beta'],
            ]
        ]);
    })->name('projects.index');
});

/*
|--------------------------------------------------------------------------
| Test the routes:
|--------------------------------------------------------------------------
|
| Visit these URLs to see the routes in action:
|
| http://localhost:8000/admin/dashboard
| http://localhost:8000/admin/users
| http://localhost:8000/admin/settings
| http://localhost:8000/admin/login
| http://localhost:8000/api/v1/tasks
| http://localhost:8000/api/v1/projects
|
| List all routes:
| php artisan route:list --path=admin
| php artisan route:list --path=api/v1
*/

/*
|--------------------------------------------------------------------------
| Benefits of Route Groups:
|--------------------------------------------------------------------------
|
| 1. DRY (Don't Repeat Yourself) - Prefix applied once
| 2. Organization - Related routes grouped together
| 3. Middleware - Apply to all routes in group
| 4. Naming - Consistent naming with prefix
| 5. Easier refactoring - Change prefix in one place
*/
