<?php

declare(strict_types=1);

namespace ClaudePhp\Auth;

use Anthropic\Anthropic;
use Anthropic\Exceptions\ErrorException;

/**
 * ApiKeyManager - Secure API key management and validation
 */
class ApiKeyManager
{
    private string $apiKey;
    private ?string $backupKey;

    public function __construct(string $apiKey, ?string $backupKey = null)
    {
        $this->apiKey = $apiKey;
        $this->backupKey = $backupKey;
    }

    /**
     * Validate API key format
     */
    public function isValidFormat(string $key): bool
    {
        return (bool) preg_match('/^sk-ant-[a-zA-Z0-9\-_]{95}$/', $key);
    }

    /**
     * Test API key by making a minimal request
     */
    public function testKey(string $key): bool
    {
        try {
            $client = Anthropic::factory()->withApiKey($key)->make();
            $client->messages()->create([
                'model' => 'claude-haiku-4-20250514',
                'max_tokens' => 10,
                'messages' => [['role' => 'user', 'content' => 'Hi']]
            ]);
            return true;
        } catch (ErrorException $e) {
            return false;
        }
    }

    /**
     * Get active API key (with fallback to backup)
     */
    public function getActiveKey(): string
    {
        if ($this->testKey($this->apiKey)) {
            return $this->apiKey;
        }

        if ($this->backupKey && $this->testKey($this->backupKey)) {
            return $this->backupKey;
        }

        throw new \RuntimeException('No valid API key available');
    }

    /**
     * Mask API key for logging
     */
    public static function maskKey(string $key): string
    {
        if (strlen($key) < 20) {
            return str_repeat('*', strlen($key));
        }
        return substr($key, 0, 8) . str_repeat('*', strlen($key) - 16) . substr($key, -8);
    }
}
