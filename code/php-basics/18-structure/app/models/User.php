<?php

declare(strict_types=1);

namespace Models;

/**
 * User Model
 * 
 * Represents a user account.
 */

class User extends Model
{
    protected string $table = 'users';

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array
    {
        $result = $this->query(
            "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1",
            [$email]
        );

        return $result[0] ?? null;
    }

    /**
     * Verify user credentials
     */
    public function verify(string $email, string $password): bool
    {
        $user = $this->findByEmail($email);

        if ($user === null) {
            return false;
        }

        return password_verify($password, $user['password']);
    }

    /**
     * Create user with hashed password
     */
    public function register(array $data): int
    {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return $this->create($data);
    }
}
