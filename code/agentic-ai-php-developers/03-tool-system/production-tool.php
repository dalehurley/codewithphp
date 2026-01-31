<?php

declare(strict_types=1);

/**
 * Production Tool Example
 * 
 * Complete production-ready tool demonstrating:
 * - Idempotency
 * - Database transactions
 * - Audit logging
 * - Comprehensive validation
 * - Structured logging
 * - Error handling
 * - Performance tracking
 */

// Load from the claude-php-agent project in the workspace
require_once __DIR__ . '/../../../../claude-php-agent/vendor/autoload.php';

use ClaudeAgents\Tools\Tool;
use ClaudeAgents\Tools\ToolResult;

echo "=== Production Tool Example ===\n\n";

// Simple in-memory logger
class SimpleLogger
{
    public function info(string $message, array $context = []): void
    {
        echo "[INFO] {$message} " . json_encode($context) . "\n";
    }
    
    public function warning(string $message, array $context = []): void
    {
        echo "[WARN] {$message} " . json_encode($context) . "\n";
    }
    
    public function error(string $message, array $context = []): void
    {
        echo "[ERROR] {$message} " . json_encode($context) . "\n";
    }
}

// Initialize in-memory SQLite database
$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create schema
$db->exec('
    CREATE TABLE products (
        id TEXT PRIMARY KEY,
        name TEXT NOT NULL,
        quantity INTEGER NOT NULL DEFAULT 0,
        updated_at DATETIME
    );
    
    CREATE TABLE inventory_audit (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id TEXT NOT NULL,
        old_quantity INTEGER NOT NULL,
        new_quantity INTEGER NOT NULL,
        mode TEXT NOT NULL,
        reason TEXT,
        created_at DATETIME NOT NULL
    );
    
    CREATE TABLE idempotency_cache (
        key TEXT PRIMARY KEY,
        result TEXT NOT NULL,
        created_at DATETIME NOT NULL
    );
');

// Insert sample products
$db->exec("
    INSERT INTO products (id, name, quantity, updated_at) VALUES
    ('SKU001', 'Widget A', 100, datetime('now')),
    ('SKU002', 'Widget B', 50, datetime('now')),
    ('SKU003', 'Widget C', 25, datetime('now'))
");

$logger = new SimpleLogger();

// Helper functions
function checkIdempotencyKey(PDO $db, string $key): ?array
{
    $stmt = $db->prepare('SELECT result FROM idempotency_cache WHERE key = ?');
    $stmt->execute([$key]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $row ? json_decode($row['result'], true) : null;
}

function storeIdempotencyResult(PDO $db, string $key, array $result): void
{
    $stmt = $db->prepare(
        'INSERT INTO idempotency_cache (key, result, created_at) VALUES (?, ?, datetime(\'now\'))'
    );
    $stmt->execute([$key, json_encode($result)]);
}

// Create production-grade inventory tool
$inventoryTool = Tool::create('update_inventory')
    ->description(
        'Update product inventory quantities. ' .
        'Supports both absolute values and relative adjustments. ' .
        'Includes stock validation and audit logging.'
    )
    ->stringParam('product_id', 'Product SKU or ID')
    ->numberParam('quantity', 'New quantity or adjustment', true, -10000, 10000)
    ->stringParam(
        'mode',
        'Update mode: "set" (absolute) or "adjust" (relative)',
        true,
        ['set', 'adjust']
    )
    ->stringParam('reason', 'Reason for inventory change', required: false)
    ->stringParam('idempotency_key', 'Unique key to prevent duplicate updates', required: false)
    ->handler(function (array $input) use ($db, $logger): ToolResult {
        $startTime = microtime(true);
        $productId = $input['product_id'];
        $quantity = (int)$input['quantity'];
        $mode = $input['mode'];
        $reason = $input['reason'] ?? 'no reason provided';
        $idempotencyKey = $input['idempotency_key'] ?? null;
        
        $logger->info('Inventory update requested', [
            'product_id' => $productId,
            'quantity' => $quantity,
            'mode' => $mode,
        ]);
        
        // 1. Check idempotency (prevent duplicate operations)
        if ($idempotencyKey !== null) {
            $existing = checkIdempotencyKey($db, $idempotencyKey);
            if ($existing !== null) {
                $logger->info('Duplicate request detected, returning cached result', [
                    'idempotency_key' => $idempotencyKey,
                ]);
                return ToolResult::success($existing);
            }
        }
        
        try {
            $db->beginTransaction();
            
            // 2. Fetch current product (with row lock)
            $stmt = $db->prepare('SELECT * FROM products WHERE id = ?');
            $stmt->execute([$productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product === false) {
                $db->rollBack();
                $logger->warning('Product not found', ['product_id' => $productId]);
                return ToolResult::error("Product not found: {$productId}");
            }
            
            // 3. Calculate new quantity
            $oldQuantity = (int)$product['quantity'];
            
            if ($mode === 'set') {
                $newQuantity = $quantity;
            } else { // adjust
                $newQuantity = $oldQuantity + $quantity;
            }
            
            // 4. Validate new quantity
            if ($newQuantity < 0) {
                $db->rollBack();
                $logger->warning('Negative inventory not allowed', [
                    'product_id' => $productId,
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $newQuantity,
                ]);
                return ToolResult::error(
                    "Inventory cannot go negative: current={$oldQuantity}, adjustment={$quantity}"
                );
            }
            
            if ($newQuantity > 10000) {
                $db->rollBack();
                $logger->warning('Max quantity exceeded', [
                    'product_id' => $productId,
                    'new_quantity' => $newQuantity,
                ]);
                return ToolResult::error(
                    "Quantity {$newQuantity} exceeds maximum allowed: 10000"
                );
            }
            
            // 5. Update inventory
            $updateStmt = $db->prepare('UPDATE products SET quantity = ?, updated_at = datetime(\'now\') WHERE id = ?');
            $updateStmt->execute([$newQuantity, $productId]);
            
            // 6. Create audit log
            $auditStmt = $db->prepare(
                'INSERT INTO inventory_audit (product_id, old_quantity, new_quantity, mode, reason, created_at) ' .
                'VALUES (?, ?, ?, ?, ?, datetime(\'now\'))'
            );
            $auditStmt->execute([$productId, $oldQuantity, $newQuantity, $mode, $reason]);
            $auditId = $db->lastInsertId();
            
            // 7. Store idempotency result
            $resultData = [
                'product_id' => $productId,
                'product_name' => $product['name'],
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'change' => $newQuantity - $oldQuantity,
                'mode' => $mode,
                'audit_id' => $auditId,
                'timestamp' => date('c'),
            ];
            
            if ($idempotencyKey !== null) {
                storeIdempotencyResult($db, $idempotencyKey, $resultData);
            }
            
            $db->commit();
            
            $duration = microtime(true) - $startTime;
            
            $logger->info('Inventory updated successfully', [
                'product_id' => $productId,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'audit_id' => $auditId,
                'duration_seconds' => round($duration, 3),
            ]);
            
            $resultData['duration_seconds'] = round($duration, 3);
            
            return ToolResult::success($resultData);
            
        } catch (PDOException $e) {
            $db->rollBack();
            $logger->error('Database error during inventory update', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);
            return ToolResult::error("Database error: {$e->getMessage()}");
        } catch (\Throwable $e) {
            $db->rollBack();
            $logger->error('Unexpected error during inventory update', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);
            return ToolResult::fromException($e);
        }
    });

// Tests
echo "=== Initial Inventory ===\n";
$products = $db->query('SELECT * FROM products')->fetchAll(PDO::FETCH_ASSOC);
foreach ($products as $product) {
    echo "{$product['id']}: {$product['name']} - Quantity: {$product['quantity']}\n";
}
echo "\n";

echo "=== Test 1: Set Inventory (Absolute) ===\n";
$result1 = $inventoryTool->execute([
    'product_id' => 'SKU001',
    'quantity' => 150,
    'mode' => 'set',
    'reason' => 'Restocking from supplier',
]);
echo "Success: " . ($result1->isSuccess() ? 'Yes' : 'No') . "\n";
$data1 = json_decode($result1->getContent(), true);
echo "Old Quantity: {$data1['old_quantity']}\n";
echo "New Quantity: {$data1['new_quantity']}\n";
echo "Change: " . ($data1['change'] > 0 ? '+' : '') . "{$data1['change']}\n";
echo "Duration: {$data1['duration_seconds']}s\n\n";

echo "=== Test 2: Adjust Inventory (Relative) ===\n";
$result2 = $inventoryTool->execute([
    'product_id' => 'SKU002',
    'quantity' => -10,
    'mode' => 'adjust',
    'reason' => 'Sold units',
]);
echo "Success: " . ($result2->isSuccess() ? 'Yes' : 'No') . "\n";
$data2 = json_decode($result2->getContent(), true);
echo "Old Quantity: {$data2['old_quantity']}\n";
echo "New Quantity: {$data2['new_quantity']}\n";
echo "Change: {$data2['change']}\n\n";

echo "=== Test 3: Idempotency (First Request) ===\n";
$idempotencyKey = 'unique-key-' . uniqid();
$result3a = $inventoryTool->execute([
    'product_id' => 'SKU003',
    'quantity' => 75,
    'mode' => 'set',
    'reason' => 'Inventory adjustment',
    'idempotency_key' => $idempotencyKey,
]);
echo "Success: " . ($result3a->isSuccess() ? 'Yes' : 'No') . "\n";
$data3a = json_decode($result3a->getContent(), true);
echo "New Quantity: {$data3a['new_quantity']}\n\n";

echo "=== Test 4: Idempotency (Duplicate Request) ===\n";
$result3b = $inventoryTool->execute([
    'product_id' => 'SKU003',
    'quantity' => 75,
    'mode' => 'set',
    'reason' => 'Inventory adjustment',
    'idempotency_key' => $idempotencyKey,
]);
echo "Success: " . ($result3b->isSuccess() ? 'Yes' : 'No') . "\n";
$data3b = json_decode($result3b->getContent(), true);
echo "New Quantity: {$data3b['new_quantity']} (should be same as before)\n";
echo "Cached result returned? " . ($data3a === $data3b ? 'Yes' : 'No') . "\n\n";

echo "=== Test 5: Negative Inventory (Should Fail) ===\n";
$result4 = $inventoryTool->execute([
    'product_id' => 'SKU002',
    'quantity' => -1000,
    'mode' => 'adjust',
    'reason' => 'Testing validation',
]);
echo "Success: " . ($result4->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Error: " . $result4->getContent() . "\n\n";

echo "=== Test 6: Product Not Found ===\n";
$result5 = $inventoryTool->execute([
    'product_id' => 'SKU999',
    'quantity' => 10,
    'mode' => 'set',
]);
echo "Success: " . ($result5->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Error: " . $result5->getContent() . "\n\n";

echo "=== Final Inventory ===\n";
$products = $db->query('SELECT * FROM products')->fetchAll(PDO::FETCH_ASSOC);
foreach ($products as $product) {
    echo "{$product['id']}: {$product['name']} - Quantity: {$product['quantity']}\n";
}
echo "\n";

echo "=== Audit Log ===\n";
$audits = $db->query('SELECT * FROM inventory_audit ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
foreach ($audits as $audit) {
    echo "#{$audit['id']}: {$audit['product_id']} - ";
    echo "{$audit['old_quantity']} → {$audit['new_quantity']} ";
    echo "({$audit['mode']}) - {$audit['reason']}\n";
}
echo "\n";

echo "✅ Production tool example complete!\n";
echo "\nProduction Patterns Demonstrated:\n";
echo "- ✅ Idempotency (duplicate requests return cached results)\n";
echo "- ✅ Transactions (atomic database operations)\n";
echo "- ✅ Validation (business rules enforced)\n";
echo "- ✅ Audit logging (all changes tracked with reason)\n";
echo "- ✅ Structured logging (detailed context at every step)\n";
echo "- ✅ Performance tracking (duration included in response)\n";
echo "- ✅ Error context (errors include relevant debugging data)\n";
