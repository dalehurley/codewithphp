<?php

/**
 * Laravel Form Request Validation
 *
 * Form Requests provide clean, reusable validation logic.
 * Similar to DTOs in Nest.js or Joi validation in Express.
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Basic Form Request
 */
class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // or implement authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'age' => ['nullable', 'integer', 'min:18', 'max:120'],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.min' => 'Name must be at least 3 characters.',
            'email.unique' => 'This email is already taken.',
            'password.min' => 'Password must be at least 8 characters.',
            'age.min' => 'You must be at least 18 years old.',
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'email' => 'email address',
        ];
    }
}

/**
 * Update Request (different rules)
 */
class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only owner can update
        return $this->user()->id === $this->route('user')->id;
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name' => ['sometimes', 'string', 'min:3', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $userId],
            'password' => ['sometimes', 'string', 'min:8'],
        ];
    }
}

/**
 * Complex Validation with Custom Rules
 */
class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'unique:posts', 'regex:/^[a-z0-9-]+$/'],
            'content' => ['required', 'string', 'min:100'],
            'category_id' => ['required', 'exists:categories,id'],
            'tags' => ['array', 'max:5'],
            'tags.*' => ['string', 'max:50'],
            'published_at' => ['nullable', 'date', 'after:now'],
            'featured_image' => ['nullable', 'image', 'max:2048'], // 2MB max
        ];
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-generate slug from title if not provided
        if (!$this->slug && $this->title) {
            $this->merge([
                'slug' => \Str::slug($this->title),
            ]);
        }
    }

    /**
     * Custom validation after rules pass.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->somethingElseIsInvalid()) {
                $validator->errors()->add('field', 'Something is wrong with this field!');
            }
        });
    }

    private function somethingElseIsInvalid(): bool
    {
        // Custom validation logic
        return false;
    }
}

/**
 * Use in Controller
 */
namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Store a new user.
     *
     * The request is automatically validated BEFORE this method runs!
     * If validation fails, a 422 response is returned automatically.
     */
    public function store(StoreUserRequest $request)
    {
        // Data is already validated!
        $validated = $request->validated();

        $user = User::create($validated);

        return response()->json($user, 201);
    }

    /**
     * Update user.
     *
     * Authorization check runs automatically.
     * Validation runs automatically.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());

        return $user;
    }
}

/**
 * Validation Rules Cheat Sheet
 */
$commonRules = [
    // Required
    'field' => 'required',
    'field' => 'required_if:other_field,value',
    'field' => 'required_unless:other_field,value',
    'field' => 'required_with:other_field',

    // Types
    'field' => 'string',
    'field' => 'integer',
    'field' => 'numeric',
    'field' => 'boolean',
    'field' => 'array',
    'field' => 'email',
    'field' => 'url',
    'field' => 'ip',
    'field' => 'json',
    'field' => 'date',
    'field' => 'file',
    'field' => 'image',

    // Size
    'field' => 'min:3',
    'field' => 'max:255',
    'field' => 'between:3,10',
    'field' => 'size:10',

    // Relationships
    'field' => 'exists:table,column',
    'field' => 'unique:table,column',
    'field' => 'unique:table,column,except_id',

    // Patterns
    'field' => 'regex:/pattern/',
    'field' => 'alpha',           // Only letters
    'field' => 'alpha_dash',      // Letters, numbers, dashes, underscores
    'field' => 'alpha_num',       // Letters and numbers

    // Dates
    'field' => 'after:tomorrow',
    'field' => 'before:2024-12-31',
    'field' => 'after_or_equal:start_date',

    // Arrays
    'items' => 'array',
    'items.*' => 'string',
    'items.*.id' => 'integer',

    // Files
    'file' => 'image',            // jpeg, png, bmp, gif, svg, webp
    'file' => 'mimes:pdf,doc',
    'file' => 'max:2048',         // KB

    // Custom
    'field' => 'confirmed',       // Requires field_confirmation
    'field' => 'in:value1,value2',
    'field' => 'not_in:value1,value2',
];

// ===== Comparison with Nest.js =====

/**
 * Nest.js with class-validator:
 * ```typescript
 * import { IsString, IsEmail, MinLength, IsOptional, Min, Max } from 'class-validator';
 *
 * export class CreateUserDto {
 *   @IsString()
 *   @MinLength(3)
 *   name: string;
 *
 *   @IsEmail()
 *   email: string;
 *
 *   @IsString()
 *   @MinLength(8)
 *   password: string;
 *
 *   @IsOptional()
 *   @Min(18)
 *   @Max(120)
 *   age?: number;
 * }
 *
 * @Post()
 * create(@Body() createUserDto: CreateUserDto) {
 *   // Validated automatically
 *   return this.userService.create(createUserDto);
 * }
 * ```
 *
 * Laravel similarities:
 * - Both validate automatically before controller method
 * - Both return 422 on validation error
 * - Both support custom messages
 * - Laravel's syntax is more concise
 * - Laravel has more built-in rules
 */

// ===== Comparison with Express.js + Joi =====

/**
 * Express.js:
 * ```javascript
 * const Joi = require('joi');
 *
 * const userSchema = Joi.object({
 *   name: Joi.string().min(3).max(255).required(),
 *   email: Joi.string().email().required(),
 *   password: Joi.string().min(8).required(),
 *   age: Joi.number().integer().min(18).max(120).optional(),
 * });
 *
 * app.post('/users', (req, res) => {
 *   const { error, value } = userSchema.validate(req.body);
 *
 *   if (error) {
 *     return res.status(400).json({ errors: error.details });
 *   }
 *
 *   // Create user with validated data
 * });
 * ```
 *
 * Laravel advantages:
 * - No manual validation call needed
 * - Automatic error responses
 * - Type-hinted in controller
 * - Reusable validation classes
 * - Built-in database rules (exists, unique)
 */
