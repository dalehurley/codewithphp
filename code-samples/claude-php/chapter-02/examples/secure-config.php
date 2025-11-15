<?php

declare(strict_types=1);

/**
 * Secure Configuration Example
 *
 * Build a production-ready configuration manager.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\Auth\ApiKeyManager;
use Dotenv\Dotenv;

echo "=== Secure Configuration Manager ===\n\n";

/**
 * Secure configuration class
 */
class SecureConfig
{
    private array $config = [];
    private array $sensitiveKeys = ['ANTHROPIC_API_KEY', 'ANTHROPIC_API_KEY_BACKUP'];

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();

        $this->loadConfig();
    }

    private function loadConfig(): void
    {
        foreach ($_ENV as $key => $value) {
            $this->config[$key] = $value;
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    public function isSensitive(string $key): bool
    {
        return in_array($key, $this->sensitiveKeys);
    }

    public function display(string $key): string
    {
        $value = $this->get($key);
        if ($this->isSensitive($key) && $value) {
            return ApiKeyManager::maskKey($value);
        }
        return (string) $value;
    }

    public function validate(): array
    {
        $errors = [];

        if (!$this->get('ANTHROPIC_API_KEY')) {
            $errors[] = 'ANTHROPIC_API_KEY is required';
        }

        return $errors;
    }
}

try {
    $config = new SecureConfig();

    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Configuration Display (Safe)\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $keys = ['ANTHROPIC_API_KEY', 'APP_ENV'];
    foreach ($keys as $key) {
        echo "{$key}: " . $config->display($key) . "\n";
    }
    echo "\n";

    $errors = $config->validate();
    if (empty($errors)) {
        echo "✓ Configuration valid!\n";
    } else {
        foreach ($errors as $error) {
            echo "✗ {$error}\n";
        }
    }

} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n";
    exit(1);
}
