<?php

declare(strict_types=1);

namespace App\Security;

/**
 * CSRF (Cross-Site Request Forgery) protection
 * Generates and validates CSRF tokens for form submissions
 */
class CSRFProtection
{
    public function __construct(private SecureSession $session) {}

    /**
     * Generate CSRF token
     */
    public function generateToken(): string
    {
        $this->session->start();
        $token = bin2hex(random_bytes(32));
        $this->session->set('csrf_token', $token);
        return $token;
    }

    /**
     * Get current CSRF token
     */
    public function getToken(): ?string
    {
        $this->session->start();
        return $this->session->get('csrf_token');
    }

    /**
     * Validate CSRF token
     */
    public function validateToken(string $submittedToken): bool
    {
        $this->session->start();
        $storedToken = $this->session->get('csrf_token');

        if (!$storedToken) {
            return false;
        }

        // Use hash_equals for constant-time comparison
        return hash_equals($storedToken, $submittedToken);
    }

    /**
     * Generate HTML hidden input field
     */
    public function field(): string
    {
        $token = $this->getToken() ?? $this->generateToken();
        return '<input type="hidden" name="csrf_token" value="' 
            . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') 
            . '">';
    }
}





