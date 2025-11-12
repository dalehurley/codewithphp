<?php

declare(strict_types=1);

/**
 * Laravel Query Parameter Filtering & Sorting Example
 * 
 * This example demonstrates how to handle query parameters for filtering and sorting in Laravel APIs.
 * Compare this to Flask's request.args and Django REST's request.query_params.
 * 
 * Location: app/Http/Controllers/Api/UserController.php
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Basic query parameter filtering and sorting.
     * GET /api/users?status=active&sort=created_at&order=desc
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();
        
        // Filtering: ?status=active
        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }
        
        // Filtering: ?author_id=1
        if ($request->has('author_id')) {
            $query->where('author_id', $request->query('author_id'));
        }
        
        // Multiple status filter: ?status[]=active&status[]=pending
        if ($request->has('status')) {
            $statuses = $request->query('status');
            if (is_array($statuses)) {
                $query->whereIn('status', $statuses);
            } else {
                $query->where('status', $statuses);
            }
        }
        
        // Date range filtering: ?created_from=2024-01-01&created_to=2024-12-31
        if ($request->has('created_from')) {
            $query->whereDate('created_at', '>=', $request->query('created_from'));
        }
        if ($request->has('created_to')) {
            $query->whereDate('created_at', '<=', $request->query('created_to'));
        }
        
        // Sorting: ?sort=created_at&order=desc
        $sort = $request->query('sort', 'created_at');
        $order = $request->query('order', 'desc');
        
        // Validate sort column to prevent SQL injection
        $allowedSorts = ['created_at', 'updated_at', 'name', 'email'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }
        
        $query->orderBy($sort, $order);
        
        // Pagination with filters
        $perPage = $request->query('per_page', 15);
        $users = $query->paginate((int)$perPage);
        
        return response()->json($users);
    }
    
    /**
     * Advanced filtering with Form Request validation.
     * GET /api/users?status=active&author_id=1&sort=name&order=asc
     */
    public function indexValidated(IndexUserRequest $request): JsonResponse
    {
        $query = User::query();
        
        // All parameters are validated by Form Request
        $validated = $request->validated();
        
        // Filtering with validated data
        if ($request->filled('status')) {
            $query->where('status', $validated['status']);
        }
        
        if ($request->filled('author_id')) {
            $query->where('author_id', $validated['author_id']);
        }
        
        // Sorting with validated data
        $sort = $validated['sort'] ?? 'created_at';
        $order = $validated['order'] ?? 'desc';
        
        $query->orderBy($sort, $order);
        
        $users = $query->paginate($validated['per_page'] ?? 15);
        
        return response()->json($users);
    }
}

/**
 * Form Request for query parameter validation.
 * Location: app/Http/Requests/IndexUserRequest.php
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', 'in:active,inactive,pending'],
            'author_id' => ['sometimes', 'integer', 'exists:users,id'],
            'sort' => ['sometimes', 'string', 'in:created_at,updated_at,name,email'],
            'order' => ['sometimes', 'string', 'in:asc,desc'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'created_from' => ['sometimes', 'date'],
            'created_to' => ['sometimes', 'date', 'after_or_equal:created_from'],
        ];
    }
}

