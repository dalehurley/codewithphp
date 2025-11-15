<?php

declare(strict_types=1);

interface CacheInterface
{
    public function get(string $key): mixed;
    public function set(string $key, mixed $value, int $ttl = 3600): void;
    public function delete(string $key): void;
    public function clear(): void;
}

trait CacheTrait
{
    protected array $cache = [];
    protected array $expiry = [];

    public function get(string $key): mixed
    {
        if ($this->isExpired($key)) {
            unset($this->cache[$key], $this->expiry[$key]);
            return null;
        }

        return $this->cache[$key] ?? null;
    }

    public function set(string $key, mixed $value, int $ttl = 3600): void
    {
        $this->cache[$key] = $value;
        $this->expiry[$key] = time() + $ttl;
    }

    public function delete(string $key): void
    {
        unset($this->cache[$key], $this->expiry[$key]);
    }

    public function clear(): void
    {
        $this->cache = [];
        $this->expiry = [];
    }

    protected function isExpired(string $key): bool
    {
        if (!isset($this->expiry[$key])) {
            return false;
        }

        return time() > $this->expiry[$key];
    }
}

class ArrayCache implements CacheInterface
{
    use CacheTrait;
}

class FileCache implements CacheInterface
{
    use CacheTrait {
        set as protected setInMemory;
    }

    public function __construct(
        private string $cacheDir
    ) {
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
    }

    public function set(string $key, mixed $value, int $ttl = 3600): void
    {
        $this->setInMemory($key, $value, $ttl);

        $filePath = $this->cacheDir . '/' . md5($key);
        file_put_contents($filePath, serialize([
            'value' => $value,
            'expiry' => time() + $ttl
        ]));
    }
}

function cacheUser(CacheInterface $cache, int $userId, array $userData): void
{
    $cache->set("user:$userId", $userData, 3600);
    echo "Cached user {$userId}\n";
}

// Usage
echo "=== Array Cache ===\n";
$arrayCache = new ArrayCache();
cacheUser($arrayCache, 1, ['name' => 'Alice', 'email' => 'alice@example.com']);

$user = $arrayCache->get('user:1');
echo "Retrieved: {$user['name']}\n";

echo "\n=== File Cache ===\n";
$fileCache = new FileCache('/tmp/php-cache');
cacheUser($fileCache, 2, ['name' => 'Bob', 'email' => 'bob@example.com']);

$user = $fileCache->get('user:2');
echo "Retrieved: {$user['name']}\n";
