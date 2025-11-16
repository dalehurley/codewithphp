<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Security\JWT;

/**
 * JWT Authentication Middleware
 * Handles JWT token authentication for API requests
 */
class JWTAuthMiddleware
{
    public function __construct(private string $secret) {}

    /**
     * Authenticate request using JWT token
     */
    public function authenticate(string $token): ?array
    {
        $payload = JWT::decode($token, $this->secret);
        
        if (!$payload) {
            return null;
        }

        return $payload;
    }

    /**
     * Extract token from Authorization header
     */
    public function extractToken(array $headers): ?string
    {
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        
        if (!$authHeader) {
            return null;
        }

        // Format: "Bearer <token>"
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Handle authentication for API request
     */
    public function handle(array $server, callable $next): mixed
    {
        // Extract token from header
        $authHeader = $server['HTTP_AUTHORIZATION'] ?? null;
        if (!$authHeader) {
            http_response_code(401);
            echo json_encode(['error' => 'Missing authorization header']);
            exit;
        }

        $token = $this->extractToken(['Authorization' => $authHeader]);
        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid authorization format']);
            exit;
        }

        // Verify token
        $payload = $this->authenticate($token);
        if (!$payload) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or expired token']);
            exit;
        }

        // Add user data to request context
        $_SERVER['AUTH_USER'] = $payload;

        // Continue to next handler
        return $next();
    }
}



