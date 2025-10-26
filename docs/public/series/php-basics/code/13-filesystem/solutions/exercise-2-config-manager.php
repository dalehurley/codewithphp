<?php

declare(strict_types=1);

/**
 * Exercise 2: Configuration Manager
 * 
 * Build a simple configuration system using JSON:
 * 
 * Requirements:
 * - Create JSON config file with nested settings
 * - Config class with load(), get(), set(), save() methods
 * - Support dot notation for nested keys (e.g., "database.host")
 * - Test script to load, read, modify, and save config
 */

class Config
{
    private array $data = [];
    private string $filePath = '';

    /**
     * Load configuration from a JSON file
     */
    public function load(string $path): void
    {
        if (!file_exists($path)) {
            throw new RuntimeException("Config file not found: {$path}");
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException("Failed to read config file: {$path}");
        }

        $this->data = json_decode($contents, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Invalid JSON in config file: " . json_last_error_msg());
        }

        $this->filePath = $path;
        echo "Configuration loaded from: {$path}" . PHP_EOL;
    }

    /**
     * Get a configuration value using dot notation
     * Example: get('database.host') returns the 'host' key from 'database' array
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);
        $value = $this->data;

        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Set a configuration value using dot notation
     */
    public function set(string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $current = &$this->data;

        foreach ($keys as $i => $k) {
            if ($i === count($keys) - 1) {
                $current[$k] = $value;
            } else {
                if (!isset($current[$k]) || !is_array($current[$k])) {
                    $current[$k] = [];
                }
                $current = &$current[$k];
            }
        }

        echo "Set '{$key}' = " . json_encode($value) . PHP_EOL;
    }

    /**
     * Save configuration back to the file
     */
    public function save(): void
    {
        if (empty($this->filePath)) {
            throw new RuntimeException("No file path set. Load a config first.");
        }

        $json = json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new RuntimeException("Failed to encode config: " . json_last_error_msg());
        }

        if (file_put_contents($this->filePath, $json) === false) {
            throw new RuntimeException("Failed to save config to: {$this->filePath}");
        }

        echo "Configuration saved to: {$this->filePath}" . PHP_EOL;
    }

    /**
     * Get all configuration data
     */
    public function all(): array
    {
        return $this->data;
    }
}

// Test the Config class
echo "=== Configuration Manager Demo ===" . PHP_EOL . PHP_EOL;

// First, create a sample config file
$configDir = __DIR__ . '/config';
if (!is_dir($configDir)) {
    mkdir($configDir, 0755, true);
}

$configFile = $configDir . '/app.json';
$sampleConfig = [
    'app_name' => 'My Awesome Blog',
    'version' => '1.0.0',
    'database' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'blog_db'
    ],
    'features' => [
        'comments_enabled' => true,
        'registration_enabled' => false
    ]
];

file_put_contents($configFile, json_encode($sampleConfig, JSON_PRETTY_PRINT));
echo "Created sample config file." . PHP_EOL . PHP_EOL;

// Load and test the config
$config = new Config();
$config->load($configFile);

echo PHP_EOL . "--- Reading configuration ---" . PHP_EOL;
echo "App name: " . $config->get('app_name') . PHP_EOL;
echo "Database host: " . $config->get('database.host') . PHP_EOL;
echo "Database port: " . $config->get('database.port') . PHP_EOL;
echo "Comments enabled: " . ($config->get('features.comments_enabled') ? 'Yes' : 'No') . PHP_EOL;
echo "Non-existent key: " . ($config->get('non.existent.key', 'default_value')) . PHP_EOL;

echo PHP_EOL . "--- Modifying configuration ---" . PHP_EOL;
$config->set('features.comments_enabled', false);
$config->set('features.max_upload_size', '10MB');
$config->set('cache.enabled', true);

echo PHP_EOL . "--- Saving configuration ---" . PHP_EOL;
$config->save();

echo PHP_EOL . "--- Verifying changes ---" . PHP_EOL;
$newConfig = new Config();
$newConfig->load($configFile);
echo "Comments enabled: " . ($newConfig->get('features.comments_enabled') ? 'Yes' : 'No') . PHP_EOL;
echo "Max upload size: " . $newConfig->get('features.max_upload_size') . PHP_EOL;
echo "Cache enabled: " . ($newConfig->get('cache.enabled') ? 'Yes' : 'No') . PHP_EOL;
