<?php

declare(strict_types=1);

/**
 * Laravel CORS Configuration Example
 * 
 * This example demonstrates how to configure CORS for Laravel APIs.
 * Compare this to Flask-CORS and Django CORS headers.
 * 
 * Location: config/cors.php
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',
        'http://localhost:5173', // Vite dev server
        'https://example.com',
    ],

    'allowed_origins_patterns' => [
        // Pattern example: 'http://*.example.com'
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];

/**
 * Environment-based configuration example:
 * 
 * In .env file:
 * CORS_ALLOWED_ORIGINS=http://localhost:3000,https://example.com
 * 
 * In config/cors.php:
 */

// 'allowed_origins' => env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000')
//     ? explode(',', env('CORS_ALLOWED_ORIGINS'))
//     : [],

/**
 * Development configuration (allow all origins):
 * 
 * WARNING: Never use this in production!
 */

// 'allowed_origins' => ['*'],

/**
 * Middleware application (Laravel 11):
 * 
 * CORS is handled automatically for routes matching 'api/*' pattern.
 * No manual middleware registration needed.
 * 
 * For Laravel 10 and earlier, ensure HandleCors middleware is in api middleware group:
 * 
 * // app/Http/Kernel.php
 * protected $middlewareGroups = [
 *     'api' => [
 *         \Illuminate\Http\Middleware\HandleCors::class,
 *         // ...
 *     ],
 * ];
 */

