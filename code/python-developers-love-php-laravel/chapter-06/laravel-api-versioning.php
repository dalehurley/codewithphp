<?php

declare(strict_types=1);

/**
 * Laravel API Versioning Example
 * 
 * This example demonstrates API versioning strategies in Laravel.
 * Compare this to Flask/Django API versioning approaches.
 * 
 * Location: routes/api.php and app/Http/Middleware/ApiVersion.php
 */

/**
 * URL-based versioning (most common):
 * /api/v1/users, /api/v2/users
 * 
 * routes/api.php:
 */

use App\Http\Controllers\Api\V1\UserController as V1UserController;
use App\Http\Controllers\Api\V2\UserController as V2UserController;

// Version 1 routes
Route::prefix('v1')->group(function () {
    Route::apiResource('users', V1UserController::class);
});

// Version 2 routes
Route::prefix('v2')->group(function () {
    Route::apiResource('users', V2UserController::class);
});

/**
 * Header-based versioning:
 * Accept: application/vnd.api+json;version=1
 * 
 * routes/api.php:
 */

use App\Http\Middleware\ApiVersion;

Route::middleware([ApiVersion::class])->group(function () {
    Route::apiResource('users', App\Http\Controllers\Api\UserController::class);
});

/**
 * Version Middleware (app/Http/Middleware/ApiVersion.php):
 * 
 * Note: This would be in a separate file: app/Http/Middleware/ApiVersion.php
 * The namespace declaration below is for demonstration purposes only.
 */

// Example middleware class (would be in separate file: app/Http/Middleware/ApiVersion.php):
/*
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiVersion
{
    public function handle(Request $request, Closure $next): mixed
    {
        $acceptHeader = $request->header('Accept');
        
        // Parse version from header: application/vnd.api+json;version=1
        if (preg_match('/version=(\d+)/', $acceptHeader, $matches)) {
            $version = $matches[1];
            
            // Set version as route parameter or request attribute
            $request->attributes->set('api_version', $version);
            
            // Or modify route to use versioned controller
            // This is a simplified example - actual implementation would route to V1/V2 controllers
        }

        return $next($request);
    }
}
*/

/**
 * Usage in Controller:
 * 
 * namespace App\Http\Controllers\Api;
 * 
 * use App\Http\Controllers\Controller;
 * use Illuminate\Http\JsonResponse;
 * 
 * class UserController extends Controller
 * {
 *     public function show(Request $request, int $id): JsonResponse
 *     {
 *         $version = $request->attributes->get('api_version', '1');
 *         
 *         // Return different response based on version
 *         if ($version === '2') {
 *             return response()->json([
 *                 'id' => $id,
 *                 'name' => 'John Doe',
 *                 'email' => 'john@example.com',
 *                 'profile_url' => "/users/{$id}" // New in v2
 *             ]);
 *         }
 *         
 *         return response()->json([
 *             'id' => $id,
 *             'name' => 'John Doe',
 *             'email' => 'john@example.com'
 *         ]);
 *     }
 * }
 */

