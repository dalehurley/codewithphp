---
title: "Appendix B: PHP Performance & Optimization"
description: "PHP 8.x features, memory optimization, and performance tuning for AI applications"
series: "openai-php"
appendix: "B"
---

# Appendix B: PHP Performance & Optimization

Optimize your PHP applications for AI workloads with modern PHP features and best practices.

## PHP 8.x Features for AI Applications

### 1. Named Arguments

Improve readability when calling OpenAI API with many parameters:

```php
// Without named arguments (hard to read)
$response = $client->chat()->create([
    'model' => 'gpt-3.5-turbo',
    'messages' => $messages,
    'temperature' => 0.7,
    'max_tokens' => 150,
]);

// With named arguments (clearer)
$response = createCompletion(
    model: 'gpt-3.5-turbo',
    messages: $messages,
    temperature: 0.7,
    maxTokens: 150
);
```

### 2. Union Types

Better type safety for API responses:

```php
function handleResponse(ChatCompletion|Error $response): string
{
    return match (true) {
        $response instanceof ChatCompletion => $response->choices[0]->message->content,
        $response instanceof Error => throw new ApiException($response->message),
    };
}
```

### 3. Match Expressions

Cleaner error handling:

```php
$action = match ($error->type) {
    'rate_limit_exceeded' => retryWithBackoff(),
    'invalid_api_key' => throw new AuthException(),
    'server_error' => logAndRetry(),
    default => throw new UnknownException(),
};
```

### 4. Nullsafe Operator

Safe navigation through nested API responses:

```php
// Before
$content = isset($response['choices'][0]['message']['content'])
    ? $response['choices'][0]['message']['content']
    : null;

// PHP 8.0+
$content = $response?->choices[0]?->message?->content;
```

### 5. Attributes

Clean metadata for AI features:

```php
#[AIFunction(
    name: 'get_weather',
    description: 'Get current weather for a location'
)]
function getWeather(string $location): array
{
    return WeatherAPI::fetch($location);
}
```

---

## Memory Optimization

### 1. Stream Large Responses

Don't load entire response into memory:

```php
$stream = $client->chat()->createStreamed([
    'model' => 'gpt-4',
    'messages' => $messages,
]);

foreach ($stream as $chunk) {
    echo $chunk->choices[0]->delta->content ?? '';
    flush(); // Send to browser immediately
}
```

### 2. Generator Functions for Large Datasets

Process embeddings without loading all data:

```php
function processDocuments(array $filePaths): Generator
{
    foreach ($filePaths as $path) {
        $content = file_get_contents($path);
        yield $client->embeddings()->create([
            'model' => 'text-embedding-3-small',
            'input' => $content,
        ]);

        unset($content); // Free memory immediately
    }
}

foreach (processDocuments($files) as $embedding) {
    saveToDatabase($embedding);
}
```

### 3. Memory Limit Configuration

```php
// Increase for batch processing
ini_set('memory_limit', '512M');

// Monitor usage
$before = memory_get_usage();
processLargeBatch();
$after = memory_get_usage();
echo "Memory used: " . ($after - $before) / 1024 / 1024 . " MB\n";
```

---

## Async Processing

### 1. Parallel Requests with cURL Multi

Process multiple API calls concurrently:

```php
use GuzzleHttp\Promise;

$promises = [
    'summary' => $client->chat()->createAsync([...]),
    'sentiment' => $client->chat()->createAsync([...]),
    'keywords' => $client->chat()->createAsync([...]),
];

$results = Promise\Utils::unwrap($promises);
```

### 2. Queue Integration (Laravel)

Offload AI processing to background jobs:

```php
// Dispatch job
GenerateContentJob::dispatch($article);

// Job class
class GenerateContentJob implements ShouldQueue
{
    public function handle(OpenAIClient $client): void
    {
        $result = $client->chat()->create([...]);
        // Process result
    }
}
```

### 3. ReactPHP for Event-Driven Processing

```php
use React\EventLoop\Factory;
use React\Promise\Deferred;

$loop = Factory::create();

$loop->addTimer(0, function() use ($client) {
    $client->chat()->createStreamed([...]);
});

$loop->run();
```

---

## Database Optimization

### 1. Efficient Vector Storage

**PostgreSQL with pgvector:**

```php
// Create table
CREATE TABLE embeddings (
    id SERIAL PRIMARY KEY,
    content TEXT,
    embedding vector(1536)
);

// Create index for fast similarity search
CREATE INDEX ON embeddings USING ivfflat (embedding vector_cosine_ops);

// Query similar vectors
SELECT * FROM embeddings
ORDER BY embedding <=> $query_embedding
LIMIT 10;
```

### 2. Caching Embeddings

```php
use Illuminate\Support\Facades\Cache;

function getOrCreateEmbedding(string $text): array
{
    $key = 'embedding:' . md5($text);

    return Cache::remember($key, 3600, function() use ($text) {
        return $client->embeddings()->create([
            'model' => 'text-embedding-3-small',
            'input' => $text,
        ])->embeddings[0]->embedding;
    });
}
```

---

## Caching Strategies

### 1. Response Caching

```php
use Symfony\Contracts\Cache\CacheInterface;

class CachedOpenAIClient
{
    public function __construct(
        private OpenAIClient $client,
        private CacheInterface $cache
    ) {}

    public function complete(array $params): ChatCompletion
    {
        // Deterministic requests (temperature=0) can be cached
        if (($params['temperature'] ?? 1.0) === 0.0) {
            $key = md5(json_encode($params));
            return $this->cache->get($key, function() use ($params) {
                return $this->client->chat()->create($params);
            });
        }

        return $this->client->chat()->create($params);
    }
}
```

### 2. Redis for Distributed Caching

```php
use Predis\Client as RedisClient;

$redis = new RedisClient();
$cacheKey = "ai:response:" . md5($prompt);

if ($cached = $redis->get($cacheKey)) {
    return json_decode($cached, true);
}

$response = $client->chat()->create([...]);
$redis->setex($cacheKey, 3600, json_encode($response));

return $response;
```

---

## Connection Pooling

Reuse HTTP connections for better performance:

```php
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;

$stack = HandlerStack::create();
$stack->push(Middleware::retry(function($retries, $request, $response) {
    return $retries < 3 && $response && $response->getStatusCode() >= 500;
}));

$client = new Client([
    'handler' => $stack,
    'timeout' => 30,
    'connect_timeout' => 10,
    // Connection pooling enabled by default
]);
```

---

## Profiling & Monitoring

### 1. Execution Time Tracking

```php
class PerformanceMonitor
{
    private array $timings = [];

    public function start(string $operation): void
    {
        $this->timings[$operation] = ['start' => microtime(true)];
    }

    public function end(string $operation): float
    {
        $elapsed = microtime(true) - $this->timings[$operation]['start'];
        $this->timings[$operation]['elapsed'] = $elapsed;
        return $elapsed;
    }

    public function report(): array
    {
        return array_map(fn($t) => $t['elapsed'] * 1000 . 'ms', $this->timings);
    }
}

// Usage
$monitor = new PerformanceMonitor();

$monitor->start('api_call');
$response = $client->chat()->create([...]);
$duration = $monitor->end('api_call');

Log::info("API call took {$duration}ms");
```

### 2. Token Usage Tracking

```php
class TokenTracker
{
    private int $totalTokens = 0;
    private int $totalCost = 0;

    public function track(ChatCompletion $response): void
    {
        $tokens = $response->usage->totalTokens;
        $this->totalTokens += $tokens;

        // Calculate cost (GPT-3.5 Turbo pricing)
        $inputCost = ($response->usage->promptTokens / 1000000) * 0.50;
        $outputCost = ($response->usage->completionTokens / 1000000) * 1.50;
        $this->totalCost += ($inputCost + $outputCost);
    }

    public function getReport(): array
    {
        return [
            'total_tokens' => $this->totalTokens,
            'total_cost' => number_format($this->totalCost, 4),
        ];
    }
}
```

---

## Best Practices Checklist

### Performance
- [ ] Use streaming for long responses
- [ ] Implement connection pooling
- [ ] Cache deterministic responses
- [ ] Use generators for large datasets
- [ ] Process in parallel when possible
- [ ] Set appropriate timeouts
- [ ] Monitor memory usage

### Code Quality
- [ ] Use PHP 8.x type declarations
- [ ] Implement proper error handling
- [ ] Use dependency injection
- [ ] Write unit tests
- [ ] Profile before optimizing
- [ ] Document performance requirements

### Production
- [ ] Enable OPcache
- [ ] Use production-grade cache (Redis)
- [ ] Implement health checks
- [ ] Set up monitoring/alerting
- [ ] Configure proper logging
- [ ] Use queue workers for heavy tasks

---

## PHP Configuration for AI Workloads

### php.ini Settings

```ini
; Memory
memory_limit = 512M

; Execution time for long-running jobs
max_execution_time = 300

; OPcache (essential for production)
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0  ; Disable in production

; File uploads (for document processing)
upload_max_filesize = 20M
post_max_size = 20M
```

---

**Last Updated**: 2025-11-15

For more optimization tips, see:
- [PHP Manual: Performance](https://www.php.net/manual/en/features.performance.php)
- [OPcache Documentation](https://www.php.net/manual/en/book.opcache.php)
