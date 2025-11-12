<?php

declare(strict_types=1);

/**
 * Laravel Form Request Example
 * 
 * This example demonstrates how to validate API requests using Laravel Form Requests.
 * Compare this to Django forms and Flask validation schemas.
 * 
 * Location: app/Http/Requests/StoreUserRequest.php
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * 
     * Similar to Django's form validation or Flask's authorization checks.
     */
    public function authorize(): bool
    {
        // Add authorization logic here
        // For example: return $this->user()->can('create', User::class);
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     * 
     * Similar to Django's form field definitions or Flask's Marshmallow schema fields
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email'],
            'age' => ['nullable', 'integer', 'min:0', 'max:150'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'email.email' => 'The email must be a valid email address.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     * 
     * Automatically returns JSON error response for API requests.
     * Similar to Flask's ValidationError handling or Django's form.errors.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'errors' => $validator->errors()
            ], 422)
        );
    }
}

/**
 * Usage in Controller:
 * 
 * namespace App\Http\Controllers\Api;
 * 
 * use App\Http\Requests\StoreUserRequest;
 * 
 * class UserController extends Controller
 * {
 *     public function store(StoreUserRequest $request): JsonResponse
 *     {
 *         // Validation passed automatically
 *         // If validation fails, failedValidation() is called automatically
 *         $validated = $request->validated();
 *         
 *         // Create user with validated data
 *         $user = User::create($validated);
 *         
 *         return response()->json([
 *             'id' => $user->id,
 *             ...$validated
 *         ], 201);
 *     }
 * }
 */

