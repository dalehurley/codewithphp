<?php

declare(strict_types=1);

namespace App\Session;

use App\Security\SecureSession;

/**
 * Flash Messages
 * One-time messages stored in session for displaying after redirects
 */
class FlashMessages
{
    public function __construct(private SecureSession $session) {}

    /**
     * Set a flash message
     */
    public function set(string $type, string $message): void
    {
        $this->session->start();
        $messages = $this->session->get('flash_messages', []);
        $messages[] = ['type' => $type, 'message' => $message];
        $this->session->set('flash_messages', $messages);
    }

    /**
     * Get and remove all flash messages
     */
    public function get(): array
    {
        $this->session->start();
        $messages = $this->session->get('flash_messages', []);
        $this->session->remove('flash_messages');
        return $messages;
    }

    /**
     * Check if there are flash messages
     */
    public function has(): bool
    {
        $this->session->start();
        return $this->session->has('flash_messages');
    }

    /**
     * Render flash messages as HTML
     */
    public function render(): string
    {
        $messages = $this->get();
        if (empty($messages)) {
            return '';
        }

        $html = '<div class="flash-messages">';
        foreach ($messages as $msg) {
            $html .= sprintf(
                '<div class="flash-message flash-%s">%s</div>',
                htmlspecialchars($msg['type'], ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8')
            );
        }
        $html .= '</div>';

        return $html;
    }
}



