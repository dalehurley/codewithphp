#!/usr/bin/env php
<?php
/**
 * Basic Memory Storage Example
 * 
 * Demonstrates storing and retrieving memories with the MemoryStore class.
 * Shows tag-based search, importance tracking, and duplicate detection.
 * 
 * Usage: php basic-memory-storage.php
 */

declare(strict_types=1);

// Include the MemoryStore class (from chapter content)
class MemoryStore
{
    private PDO $pdo;
    
    public function __construct(string $dsn, string $username = '', string $password = '')
    {
        $this->pdo = new PDO($dsn, $username, $password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    public function store(
        string $content,
        string $userId,
        ?array $embedding = null,
        array $tags = [],
        array $metadata = [],
        string $source = 'user_input',
        float $importance = 0.5
    ): string {
        $id = $this->generateId();
        $contentHash = hash('sha256', $content);
        
        // Check for duplicate
        $existing = $this->findByContentHash($contentHash, $userId);
        if ($existing) {
            $this->updateAccessCount($existing['id']);
            return $existing['id'];
        }
        
        $stmt = $this->pdo->prepare("
            INSERT INTO memories (
                id, user_id, content, content_hash, embedding,
                source, importance, metadata, created_at, updated_at
            ) VALUES (
                :id, :user_id, :content, :content_hash, :embedding,
                :source, :importance, :metadata, :created_at, :updated_at
            )
        ");
        
        $now = time();
        
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
            'content' => $content,
            'content_hash' => $contentHash,
            'embedding' => $embedding ? serialize($embedding) : null,
            'source' => $source,
            'importance' => $importance,
            'metadata' => json_encode($metadata),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        
        if (!empty($tags)) {
            $this->addTags($id, $tags);
        }
        
        return $id;
    }
    
    public function retrieve(string $memoryId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM memories WHERE id = :id");
        $stmt->execute(['id' => $memoryId]);
        
        $memory = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$memory) {
            return null;
        }
        
        $this->updateAccessCount($memoryId);
        
        if ($memory['metadata']) {
            $memory['metadata'] = json_decode($memory['metadata'], true);
        }
        
        $memory['tags'] = $this->getTags($memoryId);
        
        return $memory;
    }
    
    public function findByTag(string $tag, ?string $userId = null, int $limit = 10): array
    {
        $sql = "
            SELECT DISTINCT m.*
            FROM memories m
            INNER JOIN memory_tags mt ON m.id = mt.memory_id
            WHERE mt.tag = :tag
        ";
        
        $params = ['tag' => $tag];
        
        if ($userId) {
            $sql .= " AND m.user_id = :user_id";
            $params['user_id'] = $userId;
        }
        
        $sql .= " ORDER BY m.importance DESC, m.created_at DESC LIMIT :limit";
        
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $this->processResults($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    
    public function count(?string $userId = null): int
    {
        if ($userId) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM memories WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);
        } else {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM memories");
        }
        
        return (int)$stmt->fetchColumn();
    }
    
    public function delete(string $memoryId): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM memories WHERE id = :id");
        $stmt->execute(['id' => $memoryId]);
    }
    
    private function addTags(string $memoryId, array $tags): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO memory_tags (memory_id, tag) VALUES (:memory_id, :tag)
        ");
        
        foreach ($tags as $tag) {
            $stmt->execute([
                'memory_id' => $memoryId,
                'tag' => strtolower(trim($tag)),
            ]);
        }
    }
    
    private function getTags(string $memoryId): array
    {
        $stmt = $this->pdo->prepare("SELECT tag FROM memory_tags WHERE memory_id = :memory_id");
        $stmt->execute(['memory_id' => $memoryId]);
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    private function findByContentHash(string $hash, string $userId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM memories WHERE content_hash = :hash AND user_id = :user_id
        ");
        $stmt->execute(['hash' => $hash, 'user_id' => $userId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    private function updateAccessCount(string $memoryId): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE memories
            SET access_count = access_count + 1,
                last_accessed_at = :now
            WHERE id = :id
        ");
        $stmt->execute(['id' => $memoryId, 'now' => time()]);
    }
    
    private function processResults(array $results): array
    {
        foreach ($results as &$result) {
            if (isset($result['metadata']) && $result['metadata']) {
                $result['metadata'] = json_decode($result['metadata'], true);
            }
            
            $result['tags'] = $this->getTags($result['id']);
        }
        
        return $results;
    }
    
    private function generateId(): string
    {
        return 'mem_' . bin2hex(random_bytes(16));
    }
}

// Main execution
echo "=== Basic Memory Storage Example ===\n\n";

// Setup database (use existing from schema design)
$dbPath = __DIR__ . '/memory.db';

if (!file_exists($dbPath)) {
    echo "❌ Database not found. Run memory-schema-design.php first.\n";
    exit(1);
}

$store = new MemoryStore('sqlite:' . $dbPath);

echo "Using database: {$dbPath}\n\n";

// Example 1: Store new memories
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 1: Storing Memories\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$userId = 'user_test_' . time();

$id1 = $store->store(
    content: 'User lives in San Francisco, California',
    userId: $userId,
    tags: ['location', 'profile'],
    metadata: ['type' => 'fact', 'source' => 'conversation'],
    importance: 0.7
);
echo "✓ Stored memory 1: {$id1}\n";

$id2 = $store->store(
    content: 'User is a senior PHP developer with 8 years experience',
    userId: $userId,
    tags: ['career', 'profile', 'programming'],
    metadata: ['type' => 'fact', 'source' => 'conversation'],
    importance: 0.8
);
echo "✓ Stored memory 2: {$id2}\n";

$id3 = $store->store(
    content: 'User prefers functional programming style when possible',
    userId: $userId,
    tags: ['preferences', 'programming'],
    metadata: ['type' => 'preference', 'source' => 'observation'],
    importance: 0.6
);
echo "✓ Stored memory 3: {$id3}\n\n";

// Example 2: Duplicate detection
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 2: Duplicate Detection\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Attempting to store duplicate content...\n";
$duplicateId = $store->store(
    content: 'User lives in San Francisco, California', // Same as id1
    userId: $userId,
    tags: ['location'],
    importance: 0.7
);

if ($duplicateId === $id1) {
    echo "✓ Duplicate detected! Returned existing memory: {$duplicateId}\n";
} else {
    echo "✗ New memory created (unexpected)\n";
}

echo "\n";

// Example 3: Retrieve by ID
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 3: Retrieving by ID\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$memory = $store->retrieve($id2);

if ($memory) {
    echo "Memory ID: {$memory['id']}\n";
    echo "Content: {$memory['content']}\n";
    echo "Importance: {$memory['importance']}\n";
    echo "Access Count: {$memory['access_count']}\n";
    echo "Tags: " . implode(', ', $memory['tags']) . "\n";
    echo "Metadata: " . json_encode($memory['metadata']) . "\n";
} else {
    echo "✗ Memory not found\n";
}

echo "\n";

// Example 4: Find by tag
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 4: Finding by Tag\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$programmingMemories = $store->findByTag('programming', $userId);

echo "Found " . count($programmingMemories) . " memories with tag 'programming':\n\n";

foreach ($programmingMemories as $mem) {
    echo "• {$mem['content']}\n";
    echo "  Tags: " . implode(', ', $mem['tags']) . "\n";
    echo "  Importance: {$mem['importance']}\n\n";
}

// Example 5: Access tracking
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 5: Access Tracking\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Accessing memory multiple times...\n";

for ($i = 0; $i < 5; $i++) {
    $store->retrieve($id1);
}

$updatedMemory = $store->retrieve($id1);

echo "✓ Memory accessed {$updatedMemory['access_count']} times total\n";
echo "Last accessed: " . date('Y-m-d H:i:s', $updatedMemory['last_accessed_at']) . "\n\n";

// Example 6: Statistics
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 6: Statistics\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$totalMemories = $store->count();
$userMemories = $store->count($userId);

echo "Total memories in database: {$totalMemories}\n";
echo "Memories for {$userId}: {$userMemories}\n\n";

// Example 7: Delete memory
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 7: Deleting Memory\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Deleting memory: {$id3}\n";
$store->delete($id3);

$deleted = $store->retrieve($id3);

if ($deleted === null) {
    echo "✓ Memory successfully deleted\n";
} else {
    echo "✗ Memory still exists (unexpected)\n";
}

$newCount = $store->count($userId);
echo "Memories remaining for user: {$newCount}\n\n";

echo "✓ Basic memory storage example complete!\n";
