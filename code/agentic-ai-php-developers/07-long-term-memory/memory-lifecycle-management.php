#!/usr/bin/env php
<?php
/**
 * Memory Lifecycle Management Example
 * 
 * Demonstrates memory maintenance: pruning old memories, consolidating duplicates,
 * promoting important memories, and managing memory lifecycle.
 * 
 * Usage: php memory-lifecycle-management.php
 */

declare(strict_types=1);

echo "=== Memory Lifecycle Management Example ===\n\n";
echo "This example shows memory maintenance operations.\n";
echo "Run memory-schema-design.php and basic-memory-storage.php first.\n\n";

$dbPath = __DIR__ . '/memory.db';

if (!file_exists($dbPath)) {
    echo "❌ Database not found. Run memory-schema-design.php first.\n";
    exit(1);
}

$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Display initial statistics
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Initial Statistics\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$stmt = $pdo->query("SELECT COUNT(*) as total, AVG(importance) as avg_importance, AVG(access_count) as avg_access FROM memories");
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Total memories: {$stats['total']}\n";
echo "Average importance: " . round($stats['avg_importance'], 2) . "\n";
echo "Average access count: " . round($stats['avg_access'], 1) . "\n\n";

// Operation 1: Delete expired memories
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Operation 1: Delete Expired Memories\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// First, create some expired memories for demo
$now = time();
$yesterday = $now - 86400;

$stmt = $pdo->prepare("
    INSERT INTO memories (id, user_id, content, content_hash, importance, created_at, updated_at, expires_at)
    VALUES (:id, :user_id, :content, :content_hash, :importance, :created_at, :updated_at, :expires_at)
");

$stmt->execute([
    'id' => 'mem_expired_1',
    'user_id' => 'user_demo',
    'content' => 'This memory has expired',
    'content_hash' => hash('sha256', 'This memory has expired'),
    'importance' => 0.3,
    'created_at' => $yesterday,
    'updated_at' => $yesterday,
    'expires_at' => $yesterday, // Expired yesterday
]);

echo "Created test expired memory\n";

// Delete expired
$stmt = $pdo->prepare("DELETE FROM memories WHERE expires_at IS NOT NULL AND expires_at < :now");
$stmt->execute(['now' => $now]);
$deleted = $stmt->rowCount();

echo "✓ Deleted {$deleted} expired memories\n\n";

// Operation 2: Consolidate duplicates
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Operation 2: Consolidate Duplicate Memories\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Find duplicates
$stmt = $pdo->query("
    SELECT content_hash, GROUP_CONCAT(id) as ids, COUNT(*) as count
    FROM memories
    GROUP BY content_hash, user_id
    HAVING count > 1
");

$duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
$consolidated = 0;

foreach ($duplicates as $group) {
    $ids = explode(',', $group['ids']);
    $keepId = $ids[0];
    $deleteIds = array_slice($ids, 1);
    
    // Update access count on kept memory
    $updateStmt = $pdo->prepare("UPDATE memories SET access_count = access_count + ? WHERE id = ?");
    $updateStmt->execute([count($deleteIds), $keepId]);
    
    // Delete duplicates
    foreach ($deleteIds as $deleteId) {
        $delStmt = $pdo->prepare("DELETE FROM memories WHERE id = ?");
        $delStmt->execute([$deleteId]);
        $consolidated++;
    }
    
    echo "✓ Consolidated duplicate (kept {$keepId}, removed " . count($deleteIds) . " duplicates)\n";
}

if ($consolidated === 0) {
    echo "No duplicates found\n";
}

echo "\n";

// Operation 3: Promote frequently accessed memories
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Operation 3: Promote Important Memories\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Boost importance for frequently accessed memories
$threshold = 3;

$stmt = $pdo->prepare("
    UPDATE memories
    SET importance = MIN(1.0, importance + 0.1)
    WHERE access_count >= :threshold AND importance < 0.9
");

$stmt->execute(['threshold' => $threshold]);
$promoted = $stmt->rowCount();

echo "✓ Promoted {$promoted} memories (accessed ≥ {$threshold} times)\n\n";

// Operation 4: Prune low-value memories
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Operation 4: Prune Low-Value Memories\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$maxMemories = 100; // Per user
$minImportance = 0.2;

// Get user with most memories
$stmt = $pdo->query("SELECT user_id, COUNT(*) as count FROM memories GROUP BY user_id ORDER BY count DESC LIMIT 1");
$topUser = $stmt->fetch(PDO::FETCH_ASSOC);

if ($topUser) {
    $userId = $topUser['user_id'];
    $count = $topUser['count'];
    
    echo "User '{$userId}' has {$count} memories\n";
    
    if ($count > $maxMemories) {
        $toPrune = $count - $maxMemories;
        echo "Need to prune {$toPrune} memories\n";
        
        // Get candidates (low importance, old, rarely accessed)
        $stmt = $pdo->prepare("
            SELECT id FROM memories
            WHERE user_id = :user_id
            ORDER BY importance ASC, access_count ASC, created_at ASC
            LIMIT :limit
        ");
        
        $stmt->bindValue('user_id', $userId);
        $stmt->bindValue('limit', $toPrune, PDO::PARAM_INT);
        $stmt->execute();
        
        $candidates = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $pruned = 0;
        
        foreach ($candidates as $memoryId) {
            $delStmt = $pdo->prepare("DELETE FROM memories WHERE id = ?");
            $delStmt->execute([$memoryId]);
            $pruned++;
        }
        
        echo "✓ Pruned {$pruned} low-value memories\n";
    } else {
        echo "✓ Memory count under limit, no pruning needed\n";
    }
} else {
    echo "No memories to prune\n";
}

echo "\n";

// Final statistics
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Final Statistics\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$stmt = $pdo->query("SELECT COUNT(*) as total, AVG(importance) as avg_importance, AVG(access_count) as avg_access FROM memories");
$finalStats = $stmt->fetch(PDO::FETCH_ASSOC);

echo "Total memories: {$finalStats['total']}\n";
echo "Average importance: " . round($finalStats['avg_importance'], 2) . "\n";
echo "Average access count: " . round($finalStats['avg_access'], 1) . "\n\n";

// Show change
$change = $stats['total'] - $finalStats['total'];
if ($change > 0) {
    echo "Removed {$change} memories total\n";
} elseif ($change < 0) {
    echo "Added " . abs($change) . " memories total\n";
} else {
    echo "Memory count unchanged\n";
}

echo "\n✓ Memory lifecycle management example complete!\n\n";

echo "Maintenance Best Practices:\n";
echo "• Run expired deletion daily\n";
echo "• Consolidate duplicates weekly\n";
echo "• Promote important memories weekly\n";
echo "• Prune low-value memories monthly\n";
echo "• Monitor average importance and access patterns\n";
