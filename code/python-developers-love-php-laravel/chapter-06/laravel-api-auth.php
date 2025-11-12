<?php

declare(strict_types=1);

/**
 * Laravel Sanctum Authentication Example
 * 
 * This example demonstrates API authentication using Laravel Sanctum.
 * Compare this to Flask-JWT and Django REST Framework token authentication.
 * 
 * Setup:
 * 1. composer require laravel/sanctum
 * 2. php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
 * 3. php artisan migrate
 */

/**
 * Routes (routes/api.php):
 * 
 * use App\Http\Controllers\Api\AuthController;
 * use App\Http\Controllers\Api\UserController;
 * 
 * Route::post('/login', [AuthController::class, 'login']);
 * Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
 * 
 * Route::middleware('auth:sanctum')->group(function () {
 *     Route::get('/users', [UserController::class, 'index']);
 *     Route::get('/users/{user_id}', [UserController::class, 'show']);
 * });
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login endpoint that returns an API token.
     * POST /api/login
     * 
     * Body: {"email": "user@example.com", "password": "password"}
     * 
     * Compare to Flask-JWT's login() or Django REST's login view.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Attempt authentication
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();
        
        // Create API token (similar to Flask-JWT's create_access_token() or Django REST's Token.objects.create())
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    /**
     * Logout endpoint that deletes the current API token.
     * POST /api/logout
     * 
     * Headers: Authorization: Bearer {token}
     * 
     * Compare to Flask-JWT's logout or Django REST's logout view.
     */
    public function logout(Request $request): JsonResponse
    {
        // Delete the current access token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
}

/**
 * Protected Controller Example:
 * 
 * namespace App\Http\Controllers\Api;
 * 
 * use App\Http\Controllers\Controller;
 * use Illuminate\Http\JsonResponse;
 * use Illuminate\Http\Request;
 * 
 * class UserController extends Controller
 * {
 *     public function __construct()
 *     {
 *         // Apply auth:sanctum middleware to all methods
 *         // Similar to Flask-JWT's @jwt_required() or Django REST's @permission_classes([IsAuthenticated])
 *         $this->middleware('auth:sanctum');
 *     }
 * 
 *     public function index(Request $request): JsonResponse
 *     {
 *         // $request->user() is automatically set by auth:sanctum middleware
 *         // Similar to Flask-JWT's get_jwt_identity() or Django REST's request.user
 *         return response()->json([
 *             'user' => $request->user()->email
 *         ]);
 *     }
 * }
 */

