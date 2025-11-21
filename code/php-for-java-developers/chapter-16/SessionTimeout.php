<?php

declare(strict_types=1);

namespace App\Session;

use App\Security\SecureSession;

/**
 * Session Timeout Handler
 * Manages idle session timeout and automatic logout
 */
class SessionTimeout
{
    public function __construct(
        private SecureSession $session,
        private int $timeoutSeconds = 1800 // 30 minutes
    ) {}

    /**
     * Check if session has timed out
     */
    public function isExpired(): bool
    {
        $this->session->start();
        $lastActivity = $this->session->get('last_activity');

        if ($lastActivity === null) {
            return false; // New session
        }

        return (time() - $lastActivity) > $this->timeoutSeconds;
    }

    /**
     * Update last activity timestamp
     */
    public function updateActivity(): void
    {
        $this->session->start();
        $this->session->set('last_activity', time());
    }

    /**
     * Check and handle timeout
     */
    public function check(): bool
    {
        if ($this->isExpired()) {
            $this->session->destroy();
            return false; // Session expired
        }

        $this->updateActivity();
        return true; // Session valid
    }
}





