<?php

declare(strict_types=1);

namespace App\Security;

class CsrfProtection
{
    private const TOKEN_NAME = 'csrf_token';
    private const TOKEN_LENGTH = 32;

    /**
     * Generate a CSRF token and store it in session
     */
    public static function generateToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $_SESSION[self::TOKEN_NAME] = $token;

        return $token;
    }

    /**
     * Get current CSRF token from session
     */
    public static function getToken(): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION[self::TOKEN_NAME] ?? null;
    }

    /**
     * Validate CSRF token
     */
    public static function validateToken(string $submittedToken): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $sessionToken = $_SESSION[self::TOKEN_NAME] ?? null;

        if ($sessionToken === null) {
            return false;
        }

        // Use hash_equals to prevent timing attacks
        return hash_equals($sessionToken, $submittedToken);
    }

    /**
     * Generate HTML hidden input field with CSRF token
     */
    public static function field(): string
    {
        $token = self::getToken() ?? self::generateToken();
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            htmlspecialchars(self::TOKEN_NAME, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Verify CSRF token from POST data
     */
    public static function verify(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true; // GET requests don't need CSRF protection
        }

        $submittedToken = $_POST[self::TOKEN_NAME] ?? null;

        if ($submittedToken === null) {
            return false;
        }

        return self::validateToken($submittedToken);
    }

    /**
     * Regenerate CSRF token (useful after successful form submission)
     */
    public static function regenerateToken(): string
    {
        return self::generateToken();
    }
}





