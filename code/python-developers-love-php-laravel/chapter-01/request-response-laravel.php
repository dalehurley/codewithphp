<?php

declare(strict_types=1);

/**
 * Laravel Request/Response Examples
 * 
 * This demonstrates Laravel's Request and Response objects.
 * Place routes in routes/web.php
 */

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

Route::match(['get', 'post'], '/example', function (Request $request): Response {
    // Access request data
    $method = $request->method();  // 'GET', 'POST', etc.
    $userId = $request->query('id');  // Query parameter
    $data = $request->input('data');  // Form data or JSON
    $path = $request->path();  // '/example'
    $headers = $request->headers->all();  // Request headers
    
    // Create simple response
    return response('Hello, World!', 200);
    
    // Create JSON response
    return response()->json(['key' => 'value', 'user_id' => $userId]);
    
    // Response with custom headers
    return response('Hello', 200)
        ->header('X-Custom-Header', 'value');
});

