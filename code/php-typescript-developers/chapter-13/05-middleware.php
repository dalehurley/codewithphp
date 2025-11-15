<?php

/**
 * Laravel Middleware Examples
 *
 * Middleware intercepts HTTP requests before they reach controllers.
 * Similar to Express.js middleware.
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Simple Logging Middleware
 */
class LogRequests
{
    public function handle(Request $request, Closure $next)
    {
        // Before controller
        \Log::info('Request:', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
        ]);

        $response = $next($request);

        // After controller
        \Log::info('Response:', [
            'status' => $response->status(),
        ]);

        return $response;
    }
}

/**
 * Authentication Middleware
 */
class CheckAuth
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Validate token (simplified)
        if ($token !== 'Bearer valid-token') {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        // Add user to request
        $request->merge(['user' => ['id' => 1, 'name' => 'Alice']]);

        return $next($request);
    }
}

/**
 * CORS Middleware
 */
class Cors
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        return $response
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }
}

/**
 * Rate Limiting Middleware
 */
class RateLimit
{
    public function handle(Request $request, Closure $next, int $maxAttempts = 60)
    {
        $key = 'rate_limit:' . $request->ip();

        $attempts = cache()->get($key, 0);

        if ($attempts >= $maxAttempts) {
            return response()->json([
                'error' => 'Too many requests'
            ], 429);
        }

        cache()->put($key, $attempts + 1, now()->addMinute());

        $response = $next($request);

        $response->header('X-RateLimit-Limit', $maxAttempts);
        $response->header('X-RateLimit-Remaining', $maxAttempts - $attempts - 1);

        return $response;
    }
}

/**
 * Validate JSON Middleware
 */
class ValidateJson
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod('post') || $request->isMethod('put')) {
            if (!$request->isJson()) {
                return response()->json([
                    'error' => 'Content-Type must be application/json'
                ], 400);
            }
        }

        return $next($request);
    }
}

// ===== Register Middleware =====

/**
 * In app/Http/Kernel.php:
 */

// Global middleware (runs on every request)
protected $middleware = [
    \App\Http\Middleware\Cors::class,
];

// Route middleware (apply to specific routes)
protected $middlewareAliases = [
    'auth' => \App\Http\Middleware\CheckAuth::class,
    'log' => \App\Http\Middleware\LogRequests::class,
    'rate' => \App\Http\Middleware\RateLimit::class,
    'json' => \App\Http\Middleware\ValidateJson::class,
];

// ===== Using Middleware in Routes =====

/**
 * In routes/api.php:
 */

// Single middleware
Route::get('/users', [UserController::class, 'index'])
    ->middleware('auth');

// Multiple middleware
Route::get('/admin', [AdminController::class, 'index'])
    ->middleware(['auth', 'log']);

// Middleware with parameters
Route::get('/api/data', [DataController::class, 'index'])
    ->middleware('rate:10'); // 10 requests per minute

// Middleware group
Route::middleware(['auth', 'log'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
});

// Apply to all routes in a group
Route::prefix('api')
    ->middleware(['json', 'rate:100'])
    ->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('posts', PostController::class);
    });

// ===== Comparison with Express.js =====

/**
 * Express.js:
 * ```javascript
 * // Logging middleware
 * app.use((req, res, next) => {
 *   console.log(`${req.method} ${req.url}`);
 *   next();
 * });
 *
 * // Auth middleware
 * const checkAuth = (req, res, next) => {
 *   const token = req.headers.authorization;
 *   if (!token) {
 *     return res.status(401).json({ error: 'Unauthorized' });
 *   }
 *   next();
 * };
 *
 * // Apply middleware
 * app.get('/protected', checkAuth, (req, res) => {
 *   res.json({ message: 'Protected data' });
 * });
 *
 * // Middleware with params
 * const rateLimit = require('express-rate-limit');
 * app.use(rateLimit({ windowMs: 60000, max: 100 }));
 * ```
 *
 * Laravel differences:
 * - Class-based middleware (more structured)
 * - Built-in middleware for auth, CORS, rate limiting
 * - Middleware registered in Kernel.php
 * - Can run code before AND after request
 * - Type-hinted Request object
 */

/**
 * Comparison with Nest.js:
 * ```typescript
 * @Injectable()
 * export class LoggerMiddleware implements NestMiddleware {
 *   use(req: Request, res: Response, next: NextFunction) {
 *     console.log(`${req.method} ${req.url}`);
 *     next();
 *   }
 * }
 *
 * // Apply in module
 * export class AppModule implements NestModule {
 *   configure(consumer: MiddlewareConsumer) {
 *     consumer
 *       .apply(LoggerMiddleware)
 *       .forRoutes('*');
 *   }
 * }
 * ```
 *
 * Laravel similarities with Nest.js:
 * - Both use class-based middleware
 * - Both register middleware in a central place
 * - Both support middleware parameters
 * - Laravel's syntax is cleaner
 */
