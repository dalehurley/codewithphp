<?php

declare(strict_types=1);

/**
 * Exercise 3: Simple File-Based Database
 * 
 * Create a basic "database" using JSON files:
 * 
 * Requirements:
 * - data/users/ directory to store user files
 * - Each user stored as {id}.json
 * - createUser() creates new user file
 * - getUser() reads and returns user data
 * - updateUser() updates user data
 * - deleteUser() deletes user file
 * - getAllUsers() returns all users as array
 */

class UserDatabase
{
    private string $dataDir;

    public function __construct(string $dataDir = __DIR__ . '/data/users')
    {
        $this->dataDir = $dataDir;

        // Create directory if it doesn't exist
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0755, true);
            echo "Created data directory: {$this->dataDir}" . PHP_EOL;
        }
    }

    /**
     * Generate a new unique user ID
     */
    private function generateId(): int
    {
        $files = glob($this->dataDir . '/*.json');
        if (empty($files)) {
            return 1;
        }

        $maxId = 0;
        foreach ($files as $file) {
            $id = (int) basename($file, '.json');
            if ($id > $maxId) {
                $maxId = $id;
            }
        }

        return $maxId + 1;
    }

    /**
     * Create a new user
     */
    public function createUser(array $userData): int
    {
        $id = $this->generateId();
        $userData['id'] = $id;
        $userData['created_at'] = date('Y-m-d H:i:s');

        $filePath = $this->dataDir . "/{$id}.json";
        $json = json_encode($userData, JSON_PRETTY_PRINT);

        if (file_put_contents($filePath, $json) === false) {
            throw new RuntimeException("Failed to create user file");
        }

        echo "Created user with ID: {$id}" . PHP_EOL;
        return $id;
    }

    /**
     * Get a user by ID
     */
    public function getUser(int $id): ?array
    {
        $filePath = $this->dataDir . "/{$id}.json";

        if (!file_exists($filePath)) {
            return null;
        }

        $contents = file_get_contents($filePath);
        if ($contents === false) {
            throw new RuntimeException("Failed to read user file");
        }

        $data = json_decode($contents, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Invalid JSON in user file");
        }

        return $data;
    }

    /**
     * Update a user
     */
    public function updateUser(int $id, array $updates): bool
    {
        $user = $this->getUser($id);
        if ($user === null) {
            echo "User {$id} not found" . PHP_EOL;
            return false;
        }

        // Merge updates with existing data
        $user = array_merge($user, $updates);
        $user['updated_at'] = date('Y-m-d H:i:s');

        $filePath = $this->dataDir . "/{$id}.json";
        $json = json_encode($user, JSON_PRETTY_PRINT);

        if (file_put_contents($filePath, $json) === false) {
            throw new RuntimeException("Failed to update user file");
        }

        echo "Updated user {$id}" . PHP_EOL;
        return true;
    }

    /**
     * Delete a user
     */
    public function deleteUser(int $id): bool
    {
        $filePath = $this->dataDir . "/{$id}.json";

        if (!file_exists($filePath)) {
            echo "User {$id} not found" . PHP_EOL;
            return false;
        }

        if (unlink($filePath) === false) {
            throw new RuntimeException("Failed to delete user file");
        }

        echo "Deleted user {$id}" . PHP_EOL;
        return true;
    }

    /**
     * Get all users
     */
    public function getAllUsers(): array
    {
        $files = glob($this->dataDir . '/*.json');
        $users = [];

        foreach ($files as $file) {
            $contents = file_get_contents($file);
            if ($contents !== false) {
                $user = json_decode($contents, true);
                if ($user !== null) {
                    $users[] = $user;
                }
            }
        }

        // Sort by ID
        usort($users, fn($a, $b) => $a['id'] <=> $b['id']);

        return $users;
    }

    /**
     * Get user count
     */
    public function count(): int
    {
        $files = glob($this->dataDir . '/*.json');
        return count($files);
    }
}

// Test the UserDatabase class
echo "=== File-Based User Database Demo ===" . PHP_EOL . PHP_EOL;

$db = new UserDatabase();

echo "--- Creating users ---" . PHP_EOL;
$userId1 = $db->createUser([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'active' => true
]);

$userId2 = $db->createUser([
    'name' => 'Jane Smith',
    'email' => 'jane@example.com',
    'active' => true
]);

$userId3 = $db->createUser([
    'name' => 'Bob Wilson',
    'email' => 'bob@example.com',
    'active' => false
]);

echo PHP_EOL . "--- Reading user ---" . PHP_EOL;
$user = $db->getUser($userId1);
if ($user) {
    echo "User: {$user['name']} ({$user['email']})" . PHP_EOL;
    echo "Active: " . ($user['active'] ? 'Yes' : 'No') . PHP_EOL;
}

echo PHP_EOL . "--- Updating user ---" . PHP_EOL;
$db->updateUser($userId2, [
    'email' => 'jane.smith@example.com',
    'active' => false
]);

echo PHP_EOL . "--- Getting all users ---" . PHP_EOL;
$allUsers = $db->getAllUsers();
echo "Total users: " . count($allUsers) . PHP_EOL;
foreach ($allUsers as $user) {
    echo "  ID {$user['id']}: {$user['name']} ({$user['email']}) - " .
        ($user['active'] ? 'Active' : 'Inactive') . PHP_EOL;
}

echo PHP_EOL . "--- Deleting user ---" . PHP_EOL;
$db->deleteUser($userId3);

echo PHP_EOL . "--- Final user count ---" . PHP_EOL;
echo "Total users: " . $db->count() . PHP_EOL;
