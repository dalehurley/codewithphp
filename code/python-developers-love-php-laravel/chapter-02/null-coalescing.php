<?php

// PHP 7.0+ null coalescing operator (??)
// Similar to Python's 'or' operator, but only checks for null/undefined

// Simulating $_GET array (in real web context)
$_GET = ['username' => 'john_doe'];

// Using null coalescing operator
$username = $_GET['username'] ?? 'guest';
echo "Username: {$username}\n";  // Output: Username: john_doe

// If key doesn't exist, use default
$email = $_GET['email'] ?? 'no-email@example.com';
echo "Email: {$email}\n";  // Output: Email: no-email@example.com

// Chaining null coalescing operators
$value = $_GET['value'] ?? $_GET['default'] ?? 'fallback';
echo "Value: {$value}\n";  // Output: Value: fallback

// With object properties (simulating a user object)
class User
{
    public ?string $email = null;
}

$user = new User();
$userEmail = $user->email ?? 'no-email@example.com';
echo "User Email: {$userEmail}\n";  // Output: User Email: no-email@example.com

