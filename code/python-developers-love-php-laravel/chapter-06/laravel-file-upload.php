<?php

declare(strict_types=1);

/**
 * Laravel File Upload Example
 * 
 * This example demonstrates how to handle file uploads in Laravel API endpoints.
 * Compare this to Flask's request.files and Django REST's file upload handling.
 * 
 * Location: app/Http/Controllers/Api/UserController.php
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadAvatarRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Upload user avatar.
     * POST /api/users/{user_id}/avatar
     * Content-Type: multipart/form-data
     * Body: avatar=<file>
     */
    public function uploadAvatar(UploadAvatarRequest $request, int $user_id): JsonResponse
    {
        $user = User::findOrFail($user_id);
        
        // Store file and get path
        // 'avatars' is the directory, 'public' is the disk
        $path = $request->file('avatar')->store('avatars', 'public');
        
        // Delete old avatar if exists
        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }
        
        // Update user record
        $user->avatar_path = $path;
        $user->save();
        
        // Return public URL
        return response()->json([
            'avatar_url' => Storage::url($path),
            'message' => 'Avatar uploaded successfully'
        ], 201);
    }
    
    /**
     * Upload to S3 (cloud storage).
     */
    public function uploadAvatarS3(UploadAvatarRequest $request, int $user_id): JsonResponse
    {
        $user = User::findOrFail($user_id);
        
        // Store to S3
        $path = $request->file('avatar')->store('avatars', 's3');
        
        // Delete old avatar from S3 if exists
        if ($user->avatar_path && Storage::disk('s3')->exists($user->avatar_path)) {
            Storage::disk('s3')->delete($user->avatar_path);
        }
        
        $user->avatar_path = $path;
        $user->save();
        
        // Get S3 URL
        $url = Storage::disk('s3')->url($path);
        
        return response()->json([
            'avatar_url' => $url,
            'message' => 'Avatar uploaded successfully'
        ], 201);
    }
    
    /**
     * Upload multiple files.
     * POST /api/posts/{post_id}/images
     */
    public function uploadImages(Request $request, int $post_id): JsonResponse
    {
        $request->validate([
            'images.*' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);
        
        $paths = [];
        
        foreach ($request->file('images') as $file) {
            $path = $file->store('post-images', 'public');
            $paths[] = Storage::url($path);
        }
        
        return response()->json([
            'image_urls' => $paths,
            'message' => 'Images uploaded successfully'
        ], 201);
    }
}

/**
 * File Upload Validation Form Request.
 * Location: app/Http/Requests/UploadAvatarRequest.php
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Add authorization logic here
        // For example: return $this->user()->id === $this->route('user_id');
        return true;
    }

    public function rules(): array
    {
        return [
            'avatar' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048', // 2MB in kilobytes
                'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required' => 'Please select an image file.',
            'avatar.file' => 'The uploaded file is not valid.',
            'avatar.image' => 'The file must be an image.',
            'avatar.mimes' => 'The image must be a jpeg, png, jpg, or gif.',
            'avatar.max' => 'The image must not be larger than 2MB.',
            'avatar.dimensions' => 'The image must be between 100x100 and 2000x2000 pixels.',
        ];
    }
}

/**
 * File Storage Configuration.
 * Location: config/filesystems.php
 * 
 * Example configuration:
 */

// 'disks' => [
//     'public' => [
//         'driver' => 'local',
//         'root' => storage_path('app/public'),
//         'url' => env('APP_URL').'/storage',
//         'visibility' => 'public',
//         'throw' => false,
//     ],
//     
//     's3' => [
//         'driver' => 's3',
//         'key' => env('AWS_ACCESS_KEY_ID'),
//         'secret' => env('AWS_SECRET_ACCESS_KEY'),
//         'region' => env('AWS_DEFAULT_REGION'),
//         'bucket' => env('AWS_BUCKET'),
//         'url' => env('AWS_URL'),
//         'endpoint' => env('AWS_ENDPOINT'),
//         'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
//         'throw' => false,
//     ],
// ],

/**
 * Usage Examples:
 * 
 * // Store file locally
 * $path = $request->file('avatar')->store('avatars', 'public');
 * $url = Storage::url($path);
 * 
 * // Store to S3
 * $path = $request->file('avatar')->store('avatars', 's3');
 * $url = Storage::disk('s3')->url($path);
 * 
 * // Delete file
 * Storage::disk('public')->delete($path);
 * 
 * // Check if file exists
 * if (Storage::disk('public')->exists($path)) {
 *     // File exists
 * }
 * 
 * // Get file size
 * $size = Storage::disk('public')->size($path);
 * 
 * // Get file MIME type
 * $mime = Storage::disk('public')->mimeType($path);
 */

