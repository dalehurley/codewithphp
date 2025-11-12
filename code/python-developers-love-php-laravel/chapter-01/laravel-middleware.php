<?php

declare(strict_types=1);

/**
 * Laravel Middleware Example
 * 
 * This demonstrates Laravel middleware for request/response processing.
 * Place this in app/Http/Middleware/LoggingMiddleware.php
 * 
 * Register in bootstrap/app.php or app/Http/Kernel.php
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class LoggingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Before request
        Log::info("Request: {$request->method()} {$request->path()}");
        
        $response = $next($request);
        
        // After request
        Log::info("Response: {$response->getStatusCode()}");
        
        return $response;
    }
}

// Register in bootstrap/app.php (Laravel 11+):
// ->middleware(LoggingMiddleware::class)

// Or in app/Http/Kernel.php (Laravel 10 and earlier):
// protected $middleware = [
//     \App\Http\Middleware\LoggingMiddleware::class,
// ];

// Or apply to specific routes:
// Route::get('/example', ...)->middleware('logging');

