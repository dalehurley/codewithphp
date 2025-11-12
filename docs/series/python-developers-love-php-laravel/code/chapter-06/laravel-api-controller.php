<?php

declare(strict_types=1);

/**
 * Laravel API Controller Example
 * 
 * This example demonstrates how to create API controllers in Laravel.
 * Compare this to Flask-RESTful's Resource classes and Django REST's ViewSets.
 * 
 * Location: app/Http/Controllers/Api/UserController.php
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     * GET /api/users
     * 
     * Equivalent to Flask-RESTful's UserListResource.get() or Django REST's UserViewSet.list()
     */
    public function index(): JsonResponse
    {
        // In a real application, fetch from database using Eloquent
        // $users = User::all();
        
        return response()->json([
            'users' => [
                ['id' => 1, 'name' => 'John Doe'],
                ['id' => 2, 'name' => 'Jane Smith']
            ]
        ]);
    }

    /**
     * Display the specified user.
     * GET /api/users/{user_id}
     * 
     * Equivalent to Flask-RESTful's UserResource.get() or Django REST's UserViewSet.retrieve()
     */
    public function show(int $user_id): JsonResponse
    {
        // In a real application, fetch from database
        // $user = User::findOrFail($user_id);
        
        return response()->json([
            'id' => $user_id,
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);
    }

    /**
     * Store a newly created user.
     * POST /api/users
     * 
     * Equivalent to Flask-RESTful's UserListResource.post() or Django REST's UserViewSet.create()
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->all();
        
        // In a real application, validate and save to database
        // $user = User::create($validated);
        
        return response()->json([
            'id' => 3,
            ...$data
        ], 201);
    }

    /**
     * Update the specified user.
     * PUT /api/users/{user_id}
     * 
     * Equivalent to Flask-RESTful's UserResource.put() or Django REST's UserViewSet.update()
     */
    public function update(Request $request, int $user_id): JsonResponse
    {
        $data = $request->all();
        
        // In a real application, validate and update database
        // $user = User::findOrFail($user_id);
        // $user->update($validated);
        
        return response()->json([
            'id' => $user_id,
            ...$data
        ]);
    }

    /**
     * Remove the specified user.
     * DELETE /api/users/{user_id}
     * 
     * Equivalent to Flask-RESTful's UserResource.delete() or Django REST's UserViewSet.destroy()
     */
    public function destroy(int $user_id): JsonResponse
    {
        // In a real application, delete from database
        // $user = User::findOrFail($user_id);
        // $user->delete();
        
        return response()->json([], 204);
    }
}

