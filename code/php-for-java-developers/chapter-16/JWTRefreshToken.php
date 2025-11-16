<?php

declare(strict_types=1);

namespace App\Security;

/**
 * JWT Refresh Token Handler
 * Manages refresh tokens for obtaining new access tokens
 */
class JWTRefreshToken
{
    /**
     * Generate a refresh token (longer-lived, stored server-side)
     */
    public static function generate(string $userId, string $secret): string
    {
        // Refresh tokens are longer-lived (7 days) and stored in database
        return JWT::encode(
            ['user_id' => $userId, 'type' => 'refresh'],
            $secret,
            604800 // 7 days
        );
    }

    /**
     * Refresh an access token using a refresh token
     */
    public static function refresh(string $refreshToken, string $secret): ?array
    {
        $payload = JWT::decode($refreshToken, $secret);
        
        if (!$payload || ($payload['type'] ?? null) !== 'refresh') {
            return null;
        }

        // Generate new access token
        $newAccessToken = JWT::encode(
            ['user_id' => $payload['user_id']],
            $secret,
            3600 // 1 hour
        );

        return [
            'access_token' => $newAccessToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600
        ];
    }
}



