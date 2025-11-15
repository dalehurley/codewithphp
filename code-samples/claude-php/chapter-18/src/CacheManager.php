<?php

declare(strict_types=1);

namespace ClaudePHP\Caching;

use Predis\Client as RedisClient;

/**
 * Cache manager for Claude API responses
 */
class CacheManager
{
    private RedisClient $redis;
    private int $ttl;
    private string $prefix;

    public function __construct(
        RedisClient $redis,
        int $ttl = 3600,
        string $prefix = 'claude:'
    ) {
        $this->redis = $redis;
        $this->ttl = $ttl;
        $this->prefix = $prefix;
    }

    public function get(string $key): ?array
    {
        $cached = $this->redis->get($this->prefix . $key);

        if ($cached === null) {
            return null;
        }

        return json_decode($cached, true);
    }

    public function set(string $key, array $value, ?int $ttl = null): void
    {
        $ttl = $ttl ?? $this->ttl;

        $this->redis->setex(
            $this->prefix . $key,
            $ttl,
            json_encode($value)
        );
    }

    public function has(string $key): bool
    {
        return $this->redis->exists($this->prefix . $key) > 0;
    }

    public function delete(string $key): void
    {
        $this->redis->del([$this->prefix . $key]);
    }

    public function clear(): void
    {
        $keys = $this->redis->keys($this->prefix . '*');
        if (!empty($keys)) {
            $this->redis->del($keys);
        }
    }

    public function generateKey(string $prompt, array $options = []): string
    {
        $data = $prompt . json_encode($options);
        return hash('sha256', $data);
    }

    public function getStats(): array
    {
        $keys = $this->redis->keys($this->prefix . '*');

        return [
            'total_entries' => count($keys),
            'memory_used' => $this->redis->info('memory')['used_memory_human'] ?? 'N/A',
        ];
    }
}
