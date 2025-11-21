<?php

declare(strict_types=1);

/**
 * Key Rotation Example
 *
 * Demonstrates API key rotation strategies for enhanced security.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\Auth\ApiKeyManager;
use Dotenv\Dotenv;

echo "=== API Key Rotation Strategy ===\n\n";

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

try {
    $primaryKey = getenv('ANTHROPIC_API_KEY');
    $backupKey = getenv('ANTHROPIC_API_KEY_BACKUP') ?: null;

    $manager = new ApiKeyManager($primaryKey, $backupKey);

    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "1. Key Rotation Status\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    echo "Primary Key: " . ApiKeyManager::maskKey($primaryKey) . "\n";
    echo "Primary Valid: " . ($manager->testKey($primaryKey) ? "✓" : "✗") . "\n";

    if ($backupKey) {
        echo "Backup Key: " . ApiKeyManager::maskKey($backupKey) . "\n";
        echo "Backup Valid: " . ($manager->testKey($backupKey) ? "✓" : "✗") . "\n";
    } else {
        echo "Backup Key: Not configured\n";
    }
    echo "\n";

    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "2. Automatic Failover\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    echo "Getting active key with failover...\n";
    $activeKey = $manager->getActiveKey();
    echo "✓ Active key: " . ApiKeyManager::maskKey($activeKey) . "\n\n";

    echo "✓ Key rotation system ready!\n";

} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
