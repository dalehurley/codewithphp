<?php

declare(strict_types=1);

namespace GoodExamples;

/**
 * PSR-1 Compliant Example
 * 
 * This file demonstrates correct PSR-1 basic coding standards.
 */

// ✓ Uses PascalCase for class names
class UserAccount
{
    // ✓ Uses camelCase for method names
    public function getUserName(): string
    {
        return $this->userName;
    }

    // ✓ Uses camelCase for property names
    private string $userName = 'default';

    // ✓ Uses UPPER_CASE for constants
    public const MAX_LOGIN_ATTEMPTS = 5;
    public const SESSION_TIMEOUT = 3600;

    // ✓ Method names are descriptive and start with verb
    public function setUserName(string $name): void
    {
        $this->userName = $name;
    }

    public function isActive(): bool
    {
        return true;
    }

    public function hasPermission(string $permission): bool
    {
        return false;
    }
}

// ✓ Side effects only: No output, just class definition
// ✓ One class per file
// ✓ Proper namespace usage
// ✓ PHP tags: Always <?php, never short tags
