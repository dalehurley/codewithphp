<?php

declare(strict_types=1);

namespace Examples;

/**
 * User Management Service
 * 
 * Handles user-related operations including CRUD operations,
 * authentication, and authorization.
 * 
 * @package Examples
 * @author  Your Name <your.email@example.com>
 * @license MIT
 * @link    https://example.com/docs/user-service
 */
class UserService
{
    /**
     * Maximum login attempts before lockout
     * 
     * @var int
     */
    private const MAX_LOGIN_ATTEMPTS = 5;

    /**
     * User repository instance
     * 
     * @var UserRepository
     */
    private UserRepository $repository;

    /**
     * Currently authenticated user
     * 
     * @var User|null
     */
    private ?User $currentUser = null;

    /**
     * UserService constructor
     * 
     * @param UserRepository $repository User data access layer
     */
    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Find a user by their unique identifier
     * 
     * Retrieves a user from the database using their ID.
     * Returns null if the user is not found.
     * 
     * @param int $id The user's unique identifier
     * 
     * @return User|null The user object or null if not found
     * 
     * @throws InvalidArgumentException If the ID is invalid
     * @throws DatabaseException If database query fails
     */
    public function findById(int $id): ?User
    {
        if ($id <= 0) {
            throw new \InvalidArgumentException('User ID must be positive');
        }

        return $this->repository->find($id);
    }

    /**
     * Get all users matching the given criteria
     * 
     * @param array<string, mixed> $criteria Search criteria
     * @param array<string>        $orderBy  Sort order ['field' => 'direction']
     * @param int                  $limit    Maximum results to return
     * @param int                  $offset   Number of results to skip
     * 
     * @return array<int, User> Array of User objects
     * 
     * @throws DatabaseException If query fails
     */
    public function findBy(
        array $criteria = [],
        array $orderBy = [],
        int $limit = 10,
        int $offset = 0
    ): array {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Create a new user account
     * 
     * @param string $email    User's email address
     * @param string $password Plain text password (will be hashed)
     * @param array  $data     Additional user data
     * 
     * @return User The newly created user
     * 
     * @throws ValidationException If validation fails
     * @throws DuplicateEmailException If email already exists
     */
    public function createUser(string $email, string $password, array $data = []): User
    {
        $this->validateEmail($email);
        $this->validatePassword($password);

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        return $this->repository->create([
            'email' => $email,
            'password' => $hashedPassword,
            ...$data
        ]);
    }

    /**
     * Authenticate a user
     * 
     * Verifies the user's credentials and returns the user object
     * if authentication is successful.
     * 
     * @param string $email    User's email
     * @param string $password User's password
     * 
     * @return User Authenticated user object
     * 
     * @throws AuthenticationException If credentials are invalid
     * @throws AccountLockedException If account is locked
     */
    public function authenticate(string $email, string $password): User
    {
        $user = $this->repository->findByEmail($email);

        if (!$user || !password_verify($password, $user->getPassword())) {
            throw new AuthenticationException('Invalid credentials');
        }

        if ($user->isLocked()) {
            throw new AccountLockedException('Account is locked');
        }

        $this->currentUser = $user;

        return $user;
    }

    /**
     * Check if user has specific permission
     * 
     * @param User   $user       The user to check
     * @param string $permission Permission name
     * 
     * @return bool True if user has permission
     * 
     * @deprecated 2.0.0 Use $user->hasPermission() instead
     * @see User::hasPermission()
     */
    public function hasPermission(User $user, string $permission): bool
    {
        return $user->hasPermission($permission);
    }

    /**
     * Get current authenticated user
     * 
     * @return User|null Current user or null if not authenticated
     */
    public function getCurrentUser(): ?User
    {
        return $this->currentUser;
    }

    /**
     * Validate email format
     * 
     * @param string $email Email to validate
     * 
     * @return void
     * 
     * @throws ValidationException If email is invalid
     */
    private function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException('Invalid email format');
        }
    }

    /**
     * Validate password strength
     * 
     * @param string $password Password to validate
     * 
     * @return void
     * 
     * @throws ValidationException If password is too weak
     */
    private function validatePassword(string $password): void
    {
        if (strlen($password) < 8) {
            throw new ValidationException('Password must be at least 8 characters');
        }
    }
}

/**
 * User Entity
 * 
 * Represents a user account in the system
 * 
 * @property-read int $id User's unique identifier
 * @property string $email User's email address
 */
class User
{
    /**
     * @var int User ID
     */
    private int $id;

    /**
     * @var string Email address
     */
    private string $email;

    /**
     * @var string Hashed password
     */
    private string $password;

    /**
     * @var bool Account locked status
     */
    private bool $isLocked = false;

    /**
     * Check if user has permission
     * 
     * @param string $permission Permission to check
     * 
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        return false; // Simplified for example
    }

    /**
     * Get user's password hash
     * 
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Check if account is locked
     * 
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->isLocked;
    }
}

// Mock classes for example
class UserRepository
{
    public function find(int $id): ?User
    {
        return null;
    }
    public function findByEmail(string $email): ?User
    {
        return null;
    }
    public function findBy(array $criteria, array $orderBy, int $limit, int $offset): array
    {
        return [];
    }
    public function create(array $data): User
    {
        return new User();
    }
}

class ValidationException extends \Exception {}
class DuplicateEmailException extends \Exception {}
class AuthenticationException extends \Exception {}
class AccountLockedException extends \Exception {}
class DatabaseException extends \Exception {}
