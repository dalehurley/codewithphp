<?php

declare(strict_types=1);

namespace App\Services;

class UserService
{
    public function findAll(): array
    {
        // In real app, fetch from database
        return [
            ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
            ['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com'],
        ];
    }

    public function findById(int $id): ?array
    {
        $users = $this->findAll();
        foreach ($users as $user) {
            if ($user['id'] === $id) {
                return $user;
            }
        }
        return null;
    }

    public function create(array $data): array
    {
        // In real app, save to database
        return [
            'id' => 3,
            'name' => $data['name'] ?? 'Unknown',
            'email' => $data['email'] ?? 'unknown@example.com'
        ];
    }
}





