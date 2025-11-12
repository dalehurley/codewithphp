<?php

declare(strict_types=1);

namespace App\Services;

class EmailService
{
    public function sendWelcome(string $email, string $name): void
    {
        // In a real application, this would send an actual email
        echo "Sending welcome email to {$email} for {$name}\n";
    }
}

