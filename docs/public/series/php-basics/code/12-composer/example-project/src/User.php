<?php

declare(strict_types=1);

namespace App;

class User
{
    public function __construct(
        private string $name,
        private string $email
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getInitials(): string
    {
        $parts = explode(' ', $this->name);
        $initials = '';

        foreach ($parts as $part) {
            if (!empty($part)) {
                $initials .= strtoupper($part[0]);
            }
        }

        return $initials;
    }
}
