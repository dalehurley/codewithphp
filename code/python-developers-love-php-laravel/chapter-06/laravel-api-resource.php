<?php

declare(strict_types=1);

/**
 * Laravel API Resource Example
 * 
 * This example demonstrates how to format API responses using Laravel API Resources.
 * Compare this to Django REST Framework serializers and Flask response formatting.
 * 
 * Location: app/Http/Resources/UserResource.php
 */

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     * 
     * Similar to Django REST's Serializer.to_representation() or Flask's format_user() function
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at->toIso8601String(),
            // Computed field (similar to Django REST's SerializerMethodField)
            'profile_url' => "/users/{$this->id}",
        ];
    }
}

/**
 * Usage in Controller:
 * 
 * namespace App\Http\Controllers\Api;
 * 
 * use App\Http\Resources\UserResource;
 * use App\Models\User;
 * 
 * class UserController extends Controller
 * {
 *     public function show(int $user_id): JsonResponse
 *     {
 *         $user = User::findOrFail($user_id);
 *         return new UserResource($user);
 *     }
 *     
 *     public function index(): JsonResponse
 *     {
 *         $users = User::all();
 *         // Use collection() for multiple resources
 *         return UserResource::collection($users);
 *     }
 * }
 */

