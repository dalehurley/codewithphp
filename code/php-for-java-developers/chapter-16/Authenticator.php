<?php

declare(strict_types=1);

namespace App\Auth;

use App\Security\{PasswordHasher, SecureSession};
use PDO;

/**
 * Complete authentication system
 * Handles user registration, login, and session management
 */
class Authenticator
{
    public function __construct(
        private PDO $pdo,
        private SecureSession $session
    ) {}

    /**
     * Authenticate user with email and password
     */
    public function login(string $email, string $password): bool
    {
        // Find user by email
        $stmt = $this->pdo->prepare(
            'SELECT id, email, password_hash FROM users WHERE email = ?'
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        // Verify password
        if (!PasswordHasher::verify($password, $user['password_hash'])) {
            return false;
        }

        // Check if hash needs rehashing (algorithm/cost updated)
        if (PasswordHasher::needsRehash($user['password_hash'])) {
            $newHash = PasswordHasher::hash($password);
            $updateStmt = $this->pdo->prepare(
                'UPDATE users SET password_hash = ? WHERE id = ?'
            );
            $updateStmt->execute([$newHash, $user['id']]);
        }

        // Start session and store user data
        $this->session->start();
        $this->session->regenerateId(); // Prevent session fixation
        $this->session->set('user_id', $user['id']);
        $this->session->set('email', $user['email']);
        $this->session->set('login_time', time());

        return true;
    }

    /**
     * Register a new user
     */
    public function register(string $email, string $password): bool
    {
        // Validate password strength
        $errors = PasswordHasher::validateStrength($password);
        if (!empty($errors)) {
            throw new \InvalidArgumentException(
                'Password does not meet requirements: ' . implode(', ', $errors)
            );
        }

        // Check if user already exists
        $stmt = $this->pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            throw new \InvalidArgumentException('User already exists');
        }

        // Hash password and insert user
        $hash = PasswordHasher::hash($password);
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (email, password_hash) VALUES (?, ?)'
        );

        return $stmt->execute([$email, $hash]);
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        $this->session->start();
        return $this->session->has('user_id');
    }

    /**
     * Get current user ID
     */
    public function getUserId(): ?int
    {
        $this->session->start();
        return $this->session->get('user_id');
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        $this->session->destroy();
    }
}



