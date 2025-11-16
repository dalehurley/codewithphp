<?php

declare(strict_types=1);

namespace App\Security;

/**
 * Secure session management class
 * Provides secure session handling with proper configuration
 */
class SecureSession
{
    private bool $started = false;

    public function __construct()
    {
        $this->configure();
    }

    /**
     * Configure secure session settings
     */
    private function configure(): void
    {
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', $this->isHttps() ? '1' : '0');
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.cookie_lifetime', '0');
        ini_set('session.gc_maxlifetime', '1800');
    }

    /**
     * Start secure session
     */
    public function start(): void
    {
        if (!$this->started && session_status() === PHP_SESSION_NONE) {
            session_start();
            $this->started = true;
        }
    }

    /**
     * Regenerate session ID (call after login)
     */
    public function regenerateId(): void
    {
        if ($this->started) {
            session_regenerate_id(true);
        }
    }

    /**
     * Get session value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->ensureStarted();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set session value
     */
    public function set(string $key, mixed $value): void
    {
        $this->ensureStarted();
        $_SESSION[$key] = $value;
    }

    /**
     * Check if session key exists
     */
    public function has(string $key): bool
    {
        $this->ensureStarted();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session value
     */
    public function remove(string $key): void
    {
        $this->ensureStarted();
        unset($_SESSION[$key]);
    }

    /**
     * Destroy session
     */
    public function destroy(): void
    {
        if ($this->started) {
            $_SESSION = [];
            if (isset($_COOKIE[session_name()])) {
                setcookie(
                    session_name(),
                    '',
                    time() - 3600,
                    '/',
                    '',
                    true,
                    true
                );
            }
            session_destroy();
            $this->started = false;
        }
    }

    /**
     * Get session ID
     */
    public function getId(): string
    {
        $this->ensureStarted();
        return session_id();
    }

    private function ensureStarted(): void
    {
        if (!$this->started) {
            $this->start();
        }
    }

    private function isHttps(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['SERVER_PORT'] ?? 80) == 443;
    }
}



