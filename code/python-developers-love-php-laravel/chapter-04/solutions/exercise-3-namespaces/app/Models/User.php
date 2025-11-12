<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\EmailService;

class User
{
    public function __construct(
        public string $name,
        public string $email
    ) {}
    
    public function sendWelcomeEmail(): void
    {
        $emailService = new EmailService();
        $emailService->sendWelcome($this->email, $this->name);
    }
}

