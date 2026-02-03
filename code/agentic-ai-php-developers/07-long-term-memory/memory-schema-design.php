#!/usr/bin/env php
<?php
/**
 * Memory Schema Design Example
 * 
 * Creates database tables for long-term memory storage:
 * - memories: Core memory storage with embeddings
 * - memory_tags: Tagging system for organization
 * - entities: Entity tracking (people, places, etc.)
 * - entity_relationships: Knowledge graph relationships
 * - memory_access_log: Usage analytics
 * 
 * Usage: php memory-schema-design.php
 */

declare(strict_types=1);

// Database connection (SQLite for demo)
$dbPath = __DIR__ . '/memory.db';
$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== Long-Term Memory Schema Design ===\n\n";
echo "Creating database: {$dbPath}\n\n";

// Drop existing tables
echo "Dropping existing tables...\n";

$tables = ['memory_access_log', 'entity_relationships', 'memory_tags', 'entities', 'memories'];
foreach ($tables as $table) {
    $pdo->exec("DROP TABLE IF EXISTS {$table}");
}

echo "✓ Existing tables dropped\n\n";

// Create memories table
echo "Creating 'memories' table...\n";

$pdo->exec("
    CREATE TABLE memories (
        id TEXT PRIMARY KEY,
        user_id TEXT NOT NULL,
        content TEXT NOT NULL,
        content_hash TEXT NOT NULL,
        embedding BLOB,
        source TEXT DEFAULT 'user_input',
        confidence REAL DEFAULT 1.0,
        importance REAL DEFAULT 0.5,
        access_count INTEGER DEFAULT 0,
        last_accessed_at INTEGER,
        created_at INTEGER NOT NULL,
        updated_at INTEGER NOT NULL,
        expires_at INTEGER,
        metadata TEXT
    )
");

$pdo->exec("CREATE INDEX idx_memories_user_id ON memories(user_id)");
$pdo->exec("CREATE INDEX idx_memories_source ON memories(source)");
$pdo->exec("CREATE INDEX idx_memories_importance ON memories(importance)");
$pdo->exec("CREATE INDEX idx_memories_created_at ON memories(created_at)");
$pdo->exec("CREATE INDEX idx_memories_content_hash ON memories(content_hash)");

echo "✓ 'memories' table created\n\n";

// Create memory_tags table
echo "Creating 'memory_tags' table...\n";

$pdo->exec("
    CREATE TABLE memory_tags (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        memory_id TEXT NOT NULL,
        tag TEXT NOT NULL,
        FOREIGN KEY (memory_id) REFERENCES memories(id) ON DELETE CASCADE
    )
");

$pdo->exec("CREATE INDEX idx_memory_tags_memory_id ON memory_tags(memory_id)");
$pdo->exec("CREATE INDEX idx_memory_tags_tag ON memory_tags(tag)");

echo "✓ 'memory_tags' table created\n\n";

// Create entities table
echo "Creating 'entities' table...\n";

$pdo->exec("
    CREATE TABLE entities (
        id TEXT PRIMARY KEY,
        name TEXT NOT NULL,
        type TEXT NOT NULL,
        mentions INTEGER DEFAULT 1,
        first_seen_at INTEGER NOT NULL,
        last_seen_at INTEGER NOT NULL,
        attributes TEXT
    )
");

$pdo->exec("CREATE INDEX idx_entities_name ON entities(name)");
$pdo->exec("CREATE INDEX idx_entities_type ON entities(type)");
$pdo->exec("CREATE INDEX idx_entities_mentions ON entities(mentions)");

echo "✓ 'entities' table created\n\n";

// Create entity_relationships table
echo "Creating 'entity_relationships' table...\n";

$pdo->exec("
    CREATE TABLE entity_relationships (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        subject_id TEXT NOT NULL,
        predicate TEXT NOT NULL,
        object_id TEXT NOT NULL,
        confidence REAL DEFAULT 1.0,
        source TEXT,
        created_at INTEGER NOT NULL,
        FOREIGN KEY (subject_id) REFERENCES entities(id) ON DELETE CASCADE,
        FOREIGN KEY (object_id) REFERENCES entities(id) ON DELETE CASCADE
    )
");

$pdo->exec("CREATE INDEX idx_entity_relationships_subject ON entity_relationships(subject_id)");
$pdo->exec("CREATE INDEX idx_entity_relationships_object ON entity_relationships(object_id)");
$pdo->exec("CREATE INDEX idx_entity_relationships_predicate ON entity_relationships(predicate)");

echo "✓ 'entity_relationships' table created\n\n";

// Create memory_access_log table
echo "Creating 'memory_access_log' table...\n";

$pdo->exec("
    CREATE TABLE memory_access_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        memory_id TEXT NOT NULL,
        query_text TEXT,
        relevance_score REAL,
        accessed_at INTEGER NOT NULL,
        FOREIGN KEY (memory_id) REFERENCES memories(id) ON DELETE CASCADE
    )
");

$pdo->exec("CREATE INDEX idx_memory_access_log_memory_id ON memory_access_log(memory_id)");
$pdo->exec("CREATE INDEX idx_memory_access_log_accessed_at ON memory_access_log(accessed_at)");

echo "✓ 'memory_access_log' table created\n\n";

// Insert sample data
echo "Inserting sample data...\n\n";

$now = time();

// Sample memories
$sampleMemories = [
    [
        'id' => 'mem_' . bin2hex(random_bytes(8)),
        'user_id' => 'user_demo',
        'content' => 'User prefers dark mode for all applications',
        'importance' => 0.8,
        'tags' => ['preferences', 'ui'],
    ],
    [
        'id' => 'mem_' . bin2hex(random_bytes(8)),
        'user_id' => 'user_demo',
        'content' => 'User\'s primary programming language is PHP',
        'importance' => 0.7,
        'tags' => ['preferences', 'programming'],
    ],
    [
        'id' => 'mem_' . bin2hex(random_bytes(8)),
        'user_id' => 'user_demo',
        'content' => 'User asked about Laravel authentication implementation',
        'importance' => 0.5,
        'tags' => ['history', 'technical', 'laravel'],
    ],
];

$stmt = $pdo->prepare("
    INSERT INTO memories (
        id, user_id, content, content_hash, importance, 
        created_at, updated_at, metadata
    ) VALUES (
        :id, :user_id, :content, :content_hash, :importance,
        :created_at, :updated_at, :metadata
    )
");

$tagStmt = $pdo->prepare("
    INSERT INTO memory_tags (memory_id, tag) VALUES (:memory_id, :tag)
");

foreach ($sampleMemories as $memory) {
    $stmt->execute([
        'id' => $memory['id'],
        'user_id' => $memory['user_id'],
        'content' => $memory['content'],
        'content_hash' => hash('sha256', $memory['content']),
        'importance' => $memory['importance'],
        'created_at' => $now,
        'updated_at' => $now,
        'metadata' => json_encode(['category' => 'sample']),
    ]);
    
    foreach ($memory['tags'] as $tag) {
        $tagStmt->execute([
            'memory_id' => $memory['id'],
            'tag' => $tag,
        ]);
    }
    
    echo "✓ Inserted: {$memory['content']}\n";
}

echo "\n";

// Sample entities
$sampleEntities = [
    [
        'id' => 'ent_' . bin2hex(random_bytes(8)),
        'name' => 'Laravel',
        'type' => 'framework',
        'mentions' => 3,
    ],
    [
        'id' => 'ent_' . bin2hex(random_bytes(8)),
        'name' => 'PHP',
        'type' => 'language',
        'mentions' => 5,
    ],
];

$entityStmt = $pdo->prepare("
    INSERT INTO entities (
        id, name, type, mentions, first_seen_at, last_seen_at, attributes
    ) VALUES (
        :id, :name, :type, :mentions, :first_seen_at, :last_seen_at, :attributes
    )
");

foreach ($sampleEntities as $entity) {
    $entityStmt->execute([
        'id' => $entity['id'],
        'name' => $entity['name'],
        'type' => $entity['type'],
        'mentions' => $entity['mentions'],
        'first_seen_at' => $now,
        'last_seen_at' => $now,
        'attributes' => json_encode([]),
    ]);
    
    echo "✓ Inserted entity: {$entity['name']} ({$entity['type']})\n";
}

// Display schema summary
echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Schema Summary\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$tables = [
    'memories' => 'Core memory storage with embeddings',
    'memory_tags' => 'Tagging system for organization',
    'entities' => 'Entity tracking (people, places, etc.)',
    'entity_relationships' => 'Knowledge graph relationships',
    'memory_access_log' => 'Usage analytics',
];

foreach ($tables as $table => $description) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM {$table}");
    $count = $stmt->fetchColumn();
    
    echo sprintf("%-25s %3d rows   %s\n", $table, $count, $description);
}

echo "\n";
echo "Database file: {$dbPath}\n";
echo "Size: " . number_format(filesize($dbPath) / 1024, 2) . " KB\n\n";

echo "✓ Schema design complete!\n";
echo "\nNext: Run other examples to see the memory system in action.\n";
