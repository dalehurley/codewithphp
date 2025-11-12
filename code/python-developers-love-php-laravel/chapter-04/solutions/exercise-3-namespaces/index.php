<?php

declare(strict_types=1);

/**
 * Exercise 3 Solution: Namespace and Autoloading
 * 
 * This demonstrates PSR-4 autoloading with namespaces.
 * 
 * To use this example:
 * 1. Run: composer install (or composer dump-autoload if composer.json already exists)
 * 2. Run: php index.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Services\EmailService;

$user = new User("Alice", "alice@example.com");
$user->sendWelcomeEmail();

// You can also use EmailService directly
$emailService = new EmailService();
$emailService->sendWelcome("bob@example.com", "Bob");

