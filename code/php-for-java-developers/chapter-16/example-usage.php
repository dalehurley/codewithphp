<?php

declare(strict_types=1);

/**
 * Example usage of authentication and session classes
 * This demonstrates how to use the authentication system
 */

require_once __DIR__ . '/SecureSession.php';
require_once __DIR__ . '/PasswordHasher.php';
require_once __DIR__ . '/Authenticator.php';
require_once __DIR__ . '/JWT.php';
require_once __DIR__ . '/CSRFProtection.php';

use App\Security\{SecureSession, PasswordHasher, JWT, CSRFProtection};
use App\Auth\Authenticator;
use PDO;

// Example 1: Secure Session Usage
echo "=== Example 1: Secure Session ===\n";
$session = new SecureSession();
$session->start();
$session->set('user_id', 123);
$session->set('username', 'john_doe');
echo "Session ID: " . $session->getId() . "\n";
echo "User ID: " . $session->get('user_id') . "\n";
echo "Username: " . $session->get('username') . "\n";
echo "\n";

// Example 2: Password Hashing
echo "=== Example 2: Password Hashing ===\n";
$password = 'MySecure123!';
$errors = PasswordHasher::validateStrength($password);
if (empty($errors)) {
    $hash = PasswordHasher::hash($password);
    echo "Password hash: " . substr($hash, 0, 50) . "...\n";
    echo "Verification: " . (PasswordHasher::verify($password, $hash) ? "Success" : "Failed") . "\n";
} else {
    foreach ($errors as $error) {
        echo "Error: $error\n";
    }
}
echo "\n";

// Example 3: JWT Token
echo "=== Example 3: JWT Token ===\n";
$secret = 'your-secret-key-change-this-in-production';
$token = JWT::encode(['user_id' => 123, 'email' => 'user@example.com'], $secret, 3600);
echo "JWT Token: " . substr($token, 0, 50) . "...\n";
$payload = JWT::decode($token, $secret);
if ($payload) {
    echo "Decoded User ID: " . $payload['user_id'] . "\n";
    echo "Decoded Email: " . $payload['email'] . "\n";
} else {
    echo "Token validation failed\n";
}
echo "\n";

// Example 4: CSRF Protection
echo "=== Example 4: CSRF Protection ===\n";
$csrf = new CSRFProtection($session);
$token = $csrf->generateToken();
echo "CSRF Token: " . substr($token, 0, 20) . "...\n";
echo "CSRF Validation: " . ($csrf->validateToken($token) ? "Valid" : "Invalid") . "\n";
echo "CSRF Field HTML: " . $csrf->field() . "\n";
echo "\n";

// Example 5: Authentication (requires database)
echo "=== Example 5: Authentication ===\n";
echo "Note: This example requires a database. Uncomment to test:\n";
/*
try {
    $pdo = new PDO('sqlite:database.db');
    $pdo->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        email TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL
    )');
    
    $auth = new Authenticator($pdo, $session);
    
    // Register
    try {
        $auth->register('user@example.com', 'SecurePass123!');
        echo "User registered successfully\n";
    } catch (\InvalidArgumentException $e) {
        echo "Registration error: " . $e->getMessage() . "\n";
    }
    
    // Login
    if ($auth->login('user@example.com', 'SecurePass123!')) {
        echo "Login successful\n";
        echo "User ID: " . $auth->getUserId() . "\n";
        echo "Is authenticated: " . ($auth->isAuthenticated() ? "Yes" : "No") . "\n";
    } else {
        echo "Login failed\n";
    }
    
    // Logout
    $auth->logout();
    echo "Logged out\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
*/



