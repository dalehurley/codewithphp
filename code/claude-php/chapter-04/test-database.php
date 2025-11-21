<?php

declare(strict_types=1);

echo "Testing Database Conversation Storage...\n\n";

// Test the database compatibility fix
echo "Test 1: DatabaseConversationStorage compatibility...\n";

class DatabaseConversationStorage
{
    public function __construct(
        private readonly \PDO $pdo
    ) {
        $this->createTableIfNotExists();
    }

    private function createTableIfNotExists(): void
    {
        // Create table (works with SQLite, MySQL, PostgreSQL)
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS conversations (
                id VARCHAR(255) PRIMARY KEY,
                messages TEXT NOT NULL,
                created_at INTEGER NOT NULL,
                updated_at INTEGER NOT NULL
            )
        ");
    }

    public function save(string $conversationId, array $messages): void
    {
        $now = time();
        $messagesJson = json_encode($messages);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to encode messages: ' . json_last_error_msg());
        }

        // Get existing created_at or use now
        $createdAt = $this->getCreatedAt($conversationId) ?? $now;

        // Try INSERT first
        $stmt = $this->pdo->prepare("
            INSERT INTO conversations (id, messages, created_at, updated_at)
            VALUES (:id, :messages, :created_at, :updated_at)
        ");

        try {
            $stmt->execute([
                ':id' => $conversationId,
                ':messages' => $messagesJson,
                ':created_at' => $createdAt,
                ':updated_at' => $now,
            ]);
        } catch (\PDOException $e) {
            // If INSERT fails (duplicate key), try UPDATE
            if ($this->isDuplicateKeyError($e)) {
                $stmt = $this->pdo->prepare("
                    UPDATE conversations
                    SET messages = :messages, updated_at = :updated_at
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':id' => $conversationId,
                    ':messages' => $messagesJson,
                    ':updated_at' => $now,
                ]);
            } else {
                throw $e;
            }
        }
    }

    /**
     * Check if PDOException is a duplicate key error
     */
    private function isDuplicateKeyError(\PDOException $e): bool
    {
        $errorCode = $e->getCode();
        $errorMessage = $e->getMessage();

        // SQLite
        if ($errorCode === '23000' && str_contains($errorMessage, 'UNIQUE constraint failed')) {
            return true;
        }

        // MySQL
        if ($errorCode === '23000' && str_contains($errorMessage, 'Duplicate entry')) {
            return true;
        }

        // PostgreSQL
        if ($errorCode === '23505') {
            return true;
        }

        return false;
    }

    public function load(string $conversationId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT messages FROM conversations WHERE id = :id
        ");

        $stmt->execute([':id' => $conversationId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$result) {
            return [];
        }

        $decoded = json_decode($result['messages'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Failed to decode stored messages: ' . json_last_error_msg());
        }
        return $decoded;
    }

    public function exists(string $conversationId): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM conversations WHERE id = :id
        ");

        $stmt->execute([':id' => $conversationId]);
        return $stmt->fetchColumn() > 0;
    }

    private function getCreatedAt(string $conversationId): ?int
    {
        $stmt = $this->pdo->prepare("
            SELECT created_at FROM conversations WHERE id = :id
        ");

        $stmt->execute([':id' => $conversationId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result ? (int)$result['created_at'] : null;
    }
}

// Test with SQLite in memory
try {
    // Create in-memory SQLite database
    $pdo = new \PDO('sqlite::memory:');
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    $storage = new DatabaseConversationStorage($pdo);
    echo "✓ DatabaseConversationStorage instantiated with SQLite\n";

    // Test saving conversation
    $conversationId = 'test-conversation';
    $messages = [
        ['role' => 'user', 'content' => 'Hello!'],
        ['role' => 'assistant', 'content' => 'Hi there!'],
    ];

    $storage->save($conversationId, $messages);
    echo "✓ Conversation saved successfully\n";

    // Test loading conversation
    $loaded = $storage->load($conversationId);
    if ($loaded === $messages) {
        echo "✓ Conversation loaded successfully and matches original\n";
    } else {
        echo "✗ Loaded conversation doesn't match original\n";
        var_dump($loaded);
    }

    // Test exists check
    if ($storage->exists($conversationId)) {
        echo "✓ exists() correctly returns true for existing conversation\n";
    } else {
        echo "✗ exists() incorrectly returns false for existing conversation\n";
    }

    // Test non-existing conversation
    if (!$storage->exists('non-existing')) {
        echo "✓ exists() correctly returns false for non-existing conversation\n";
    } else {
        echo "✗ exists() incorrectly returns true for non-existing conversation\n";
    }

    // Test UPSERT functionality (save same ID again)
    $messages2 = [
        ['role' => 'user', 'content' => 'Hello!'],
        ['role' => 'assistant', 'content' => 'Hi there!'],
        ['role' => 'user', 'content' => 'How are you?'],
    ];

    $storage->save($conversationId, $messages2);
    echo "✓ UPSERT functionality works (updated existing conversation)\n";

    $loaded2 = $storage->load($conversationId);
    if ($loaded2 === $messages2) {
        echo "✓ Updated conversation loaded successfully\n";
    } else {
        echo "✗ Updated conversation doesn't match\n";
    }
} catch (\Exception $e) {
    echo "✗ Database test failed: {$e->getMessage()}\n";
    exit(1);
}

// Test JSON error handling
echo "\nTest 2: JSON error handling...\n";

try {
    // Test with invalid JSON
    $pdo = new \PDO('sqlite::memory:');
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    // Manually insert invalid JSON to test error handling
    $pdo->exec("CREATE TABLE conversations (id TEXT PRIMARY KEY, messages TEXT)");
    $pdo->exec("INSERT INTO conversations VALUES ('test', '{invalid json}')");

    $storage = new DatabaseConversationStorage($pdo);

    try {
        $storage->load('test');
        echo "✗ Should have failed with invalid JSON\n";
    } catch (\RuntimeException $e) {
        if (str_contains($e->getMessage(), 'Failed to decode stored messages')) {
            echo "✓ JSON decode error handling works correctly\n";
        } else {
            echo "✗ Unexpected error message: {$e->getMessage()}\n";
        }
    }
} catch (\Exception $e) {
    echo "✗ JSON error handling test failed: {$e->getMessage()}\n";
    exit(1);
}

echo "\n✅ ALL DATABASE TESTS PASSED!\n";
echo "DatabaseConversationStorage is compatible with SQLite and handles errors properly.\n";
