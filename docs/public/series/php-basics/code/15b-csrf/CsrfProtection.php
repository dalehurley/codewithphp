<?php

declare(strict_types=1);

/**
 * CSRF Protection Class
 * 
 * Provides comprehensive CSRF protection with token generation,
 * validation, and form field embedding.
 * 
 * Usage:
 *   session_start();
 *   CsrfProtection::init();
 *   
 *   // In form
 *   echo CsrfProtection::getTokenField();
 *   
 *   // Validate on submission
 *   if (!CsrfProtection::validate()) {
 *       die('CSRF validation failed');
 *   }
 */
class CsrfProtection
{
    /**
     * Session key for storing token
     */
    private const TOKEN_NAME = 'csrf_token';

    /**
     * Token length in bytes (will be 64 chars in hex)
     */
    private const TOKEN_LENGTH = 32;

    /**
     * Initialize CSRF protection
     * 
     * Must be called after session_start()
     * 
     * @throws RuntimeException If session not started
     */
    public static function init(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException('Session must be started before initializing CSRF protection');
        }

        if (empty($_SESSION[self::TOKEN_NAME])) {
            $_SESSION[self::TOKEN_NAME] = self::generateToken();
        }
    }

    /**
     * Generate a new CSRF token
     * 
     * @return string Cryptographically secure random token
     */
    public static function generateToken(): string
    {
        return bin2hex(random_bytes(self::TOKEN_LENGTH));
    }

    /**
     * Get the current CSRF token
     * 
     * @return string Current session token
     */
    public static function getToken(): string
    {
        if (empty($_SESSION[self::TOKEN_NAME])) {
            self::init();
        }

        return $_SESSION[self::TOKEN_NAME];
    }

    /**
     * Validate a CSRF token from request
     * 
     * @param string|null $token Token to validate (null to auto-detect from POST)
     * @return bool True if valid, false otherwise
     */
    public static function validate(?string $token = null): bool
    {
        // Auto-detect token from POST if not provided
        if ($token === null) {
            $token = $_POST[self::TOKEN_NAME] ?? '';
        }

        // Check both session token and submitted token exist
        if (empty($_SESSION[self::TOKEN_NAME]) || empty($token)) {
            return false;
        }

        // Use timing-attack-safe comparison
        return hash_equals($_SESSION[self::TOKEN_NAME], $token);
    }

    /**
     * Get HTML for hidden CSRF token field
     * 
     * @return string HTML input element
     */
    public static function getTokenField(): string
    {
        $token = htmlspecialchars(self::getToken(), ENT_QUOTES, 'UTF-8');
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            self::TOKEN_NAME,
            $token
        );
    }

    /**
     * Validate token and throw exception on failure
     * 
     * Useful for failing fast in controller methods
     * 
     * @param string|null $token Token to validate
     * @throws RuntimeException If token is invalid
     */
    public static function validateOrFail(?string $token = null): void
    {
        if (!self::validate($token)) {
            http_response_code(403);
            throw new RuntimeException('CSRF token validation failed');
        }
    }

    /**
     * Regenerate token
     * 
     * Call this after sensitive actions (login, password change, etc.)
     * to prevent token reuse
     */
    public static function regenerateToken(): void
    {
        $_SESSION[self::TOKEN_NAME] = self::generateToken();
    }

    /**
     * Get token name (useful for JavaScript)
     * 
     * @return string Token field name
     */
    public static function getTokenName(): string
    {
        return self::TOKEN_NAME;
    }
}
