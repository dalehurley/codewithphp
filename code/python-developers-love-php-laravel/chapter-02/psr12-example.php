<?php

declare(strict_types=1);

// PSR-12 compliant code example
// Demonstrates modern PHP code structure following PSR standards

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use InvalidArgumentException;

class UserService
{
    public function __construct(
        private UserRepository $repository
    ) {}

    public function getUserById(int $id): ?User
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('User ID must be positive');
        }

        return $this->repository->find($id);
    }

    public function createUser(
        string $name,
        string $email,
        int $age = 0
    ): User {
        if (empty($name)) {
            throw new InvalidArgumentException('Name cannot be empty');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address');
        }

        $user = new User($name, $email, $age);
        $this->repository->save($user);

        return $user;
    }

    public function updateUserEmail(int $id, string $email): void
    {
        $user = $this->getUserById($id);

        if ($user === null) {
            throw new InvalidArgumentException('User not found');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address');
        }

        $user->setEmail($email);
        $this->repository->save($user);
    }
}

// Mock classes for demonstration
namespace App\Models;

class User
{
    public function __construct(
        public string $name,
        public string $email,
        public int $age = 0
    ) {}

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    private array $users = [];

    public function find(int $id): ?User
    {
        return $this->users[$id] ?? null;
    }

    public function save(User $user): void
    {
        $this->users[] = $user;
    }
}

