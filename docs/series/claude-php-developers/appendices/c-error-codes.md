---
title: "Appendix C: Error Codes and Troubleshooting"
description: "Complete reference for Claude API error codes, debugging strategies, and solutions to common issues for PHP developers."
series: "claude-php-developers"
appendix: "C"
order: 102
difficulty: "Reference"
---

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Appendix C: Error Codes</span>
</div>

# Appendix C: Error Codes and Troubleshooting

Complete guide to Claude API errors, debugging strategies, and solutions. Use this as your first stop when encountering issues.

---

## Table of Contents

- [Error Response Format](#error-response-format)
- [Client Errors (4xx)](#client-errors-4xx)
- [Server Errors (5xx)](#server-errors-5xx)
- [SDK-Specific Errors](#sdk-specific-errors)
- [Common Issues](#common-issues)
- [Debugging Strategies](#debugging-strategies)
- [Best Practices](#best-practices)

---

## Error Response Format

### Standard Error Structure

```json
{
    "type": "error",
    "error": {
        "type": "invalid_request_error",
        "message": "max_tokens: Field required"
    }
}
```

### PHP SDK Error Object

```php
# filename: sdk-error-handling.php
try {
    $response = $client->messages()->create([...]);
} catch (\ClaudePhp\Exceptions\APIError $e) {
    echo $e->getMessage();      // Error message
    echo $e->getErrorType();    // Error type code
    echo $e->getStatusCode();   // HTTP status code
}
```

---

## Client Errors (4xx)

Errors caused by invalid requests from your application.

### 400 - invalid_request_error

**Cause:** Request is malformed or missing required parameters.

**Common Examples:**

```php
// Missing required field
[
    "type" => "error",
    "error" => [
        "type" => "invalid_request_error",
        "message" => "max_tokens: Field required"
    ]
]
```

**Solutions:**

```php
# filename: fix-missing-max-tokens.php
// ❌ Wrong - missing max_tokens
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'messages' => [
        ['role' => 'user', 'content' => 'Hello']
    ]
]);

// ✅ Correct
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,  // Required!
    'messages' => [
        ['role' => 'user', 'content' => 'Hello']
    ]
]);
```

**Common Validation Errors:**

| Message | Cause | Fix |
|---------|-------|-----|
| `max_tokens: Field required` | Missing max_tokens | Add max_tokens parameter |
| `messages: Field required` | Missing messages array | Add messages parameter |
| `messages: Array must not be empty` | Empty messages array | Add at least one message |
| `messages.0.role: Must be 'user' or 'assistant'` | Invalid role | Use 'user' or 'assistant' |
| `messages.0: First message must have role 'user'` | Wrong first role | Start with user message |
| `messages: Roles must alternate` | Non-alternating roles | Alternate user/assistant |
| `model: Invalid model identifier` | Wrong model name | Use valid model ID |
| `temperature: Must be between 0 and 1` | Invalid temperature | Use 0.0-1.0 range |
| `max_tokens: Must be between 1 and 4096` | Invalid max_tokens | Use 1-4096 range |

**Validation Helper:**

```php
# filename: ClaudeRequestValidator.php
class ClaudeRequestValidator
{
    public static function validate(array $params): array
    {
        $errors = [];

        // Required fields
        if (!isset($params['model'])) {
            $errors[] = 'model is required';
        }

        if (!isset($params['max_tokens'])) {
            $errors[] = 'max_tokens is required';
        } elseif ($params['max_tokens'] < 1 || $params['max_tokens'] > 4096) {
            $errors[] = 'max_tokens must be between 1 and 4096';
        }

        if (!isset($params['messages'])) {
            $errors[] = 'messages is required';
        } elseif (empty($params['messages'])) {
            $errors[] = 'messages cannot be empty';
        } elseif ($params['messages'][0]['role'] !== 'user') {
            $errors[] = 'First message must have role "user"';
        }

        // Validate temperature
        if (isset($params['temperature'])) {
            if ($params['temperature'] < 0 || $params['temperature'] > 1) {
                $errors[] = 'temperature must be between 0 and 1';
            }
        }

        return $errors;
    }
}

// Usage
$errors = ClaudeRequestValidator::validate($params);
if (!empty($errors)) {
    throw new InvalidArgumentException(implode(', ', $errors));
}
```

---

### 401 - authentication_error

**Cause:** Invalid or missing API key.

**Error Response:**

```json
{
    "type": "error",
    "error": {
        "type": "authentication_error",
        "message": "invalid x-api-key"
    }
}
```

**Common Causes:**

1. **Missing API key**
2. **Invalid API key format**
3. **Expired API key**
4. **Wrong environment variable**

**Solutions:**

```php
# filename: fix-authentication-error.php
// Check if API key is set
$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    throw new RuntimeException('ANTHROPIC_API_KEY not set');
}

// Verify API key format (should start with 'sk-ant-')
if (!str_starts_with($apiKey, 'sk-ant-')) {
    throw new RuntimeException('Invalid API key format');
}

// Initialize client
$client = new ClaudePhp(apiKey: $apiKey);
```

**Debug Checklist:**

- [ ] API key is set in environment variables
- [ ] Using correct .env file (local vs production)
- [ ] API key starts with `sk-ant-`
- [ ] No extra whitespace in API key
- [ ] API key is still active (check console.anthropic.com)
- [ ] Using correct header (`x-api-key`, not `Authorization`)

---

### 403 - permission_error

**Cause:** API key doesn't have permission to access the resource or feature.

**Error Response:**

```json
{
    "type": "error",
    "error": {
        "type": "permission_error",
        "message": "Your API key does not have permission to use the specified model"
    }
}
```

**Common Causes:**

1. **Model not available in your tier**
2. **Beta features without beta header**
3. **Account suspended**

**Solutions:**

```php
# filename: fix-permission-error.php
// Check model availability
$availableModels = [
    'claude-opus-4-20250514',
    'claude-sonnet-4-20250514',
    'claude-haiku-4-20250514'
];

if (!in_array($model, $availableModels)) {
    throw new RuntimeException("Model {$model} not available");
}

// For beta features, add beta header
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY');
```

---

### 404 - not_found_error

**Cause:** Requested resource doesn't exist.

**Error Response:**

```json
{
    "type": "error",
    "error": {
        "type": "not_found_error",
        "message": "Not found"
    }
}
```

**Common Causes:**

1. **Wrong API endpoint URL**
2. **Typo in endpoint path**

**Solution:**

```php
# filename: fix-not-found-error.php
// ❌ Wrong
$url = 'https://api.anthropic.com/v1/message'; // Missing 's'

// ✅ Correct
$url = 'https://api.anthropic.com/v1/messages';

// When using SDK, this is handled automatically
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY');
```

---

### 429 - rate_limit_error

**Cause:** Too many requests in a time window.

**Error Response:**

```json
{
    "type": "error",
    "error": {
        "type": "rate_limit_error",
        "message": "Rate limit exceeded"
    }
}
```

**Rate Limit Types:**

1. **Requests per minute** (RPM)
2. **Tokens per minute** (TPM)
3. **Tokens per day** (TPD)

**Solutions:**

```php
# filename: RateLimiter.php
class RateLimiter
{
    private int $maxRetries = 5;
    private int $baseDelay = 1000000; // 1 second in microseconds

    public function makeRequest(callable $request, int $attempt = 0)
    {
        try {
            return $request();
        } catch (ErrorException $e) {
            if ($e->getErrorType() === 'rate_limit_error' && $attempt < $this->maxRetries) {
                // Exponential backoff: 1s, 2s, 4s, 8s, 16s
                $delay = $this->baseDelay * pow(2, $attempt);

                // Add jitter to prevent thundering herd
                $jitter = random_int(0, 100000); // 0-100ms
                usleep($delay + $jitter);

                return $this->makeRequest($request, $attempt + 1);
            }

            throw $e;
        }
    }
}

// Usage
$rateLimiter = new RateLimiter();

$response = $rateLimiter->makeRequest(function() use ($client, $params) {
    return $client->messages()->create($params);
});
```

**Advanced Rate Limiting with Redis:**

```php
# filename: RedisRateLimiter.php
class RedisRateLimiter
{
    private Redis $redis;
    private int $requestsPerMinute;

    public function __construct(Redis $redis, int $requestsPerMinute = 50)
    {
        $this->redis = $redis;
        $this->requestsPerMinute = $requestsPerMinute;
    }

    public function allowRequest(string $identifier): bool
    {
        $key = "rate_limit:{$identifier}:" . floor(time() / 60);
        $current = $this->redis->incr($key);

        if ($current === 1) {
            $this->redis->expire($key, 60);
        }

        return $current <= $this->requestsPerMinute;
    }

    public function waitForSlot(string $identifier): void
    {
        while (!$this->allowRequest($identifier)) {
            usleep(100000); // Wait 100ms
        }
    }
}

// Usage
$limiter = new RedisRateLimiter($redis, 50);
$limiter->waitForSlot('user_123');

$response = $client->messages()->create($params);
```

**Check Rate Limit Headers:**

```php
# filename: check-rate-limit-headers.php
// After making a request, check remaining quota
if (method_exists($response, 'headers')) {
    $remaining = $response->headers['anthropic-ratelimit-requests-remaining'] ?? null;
    $reset = $response->headers['anthropic-ratelimit-requests-reset'] ?? null;

    if ($remaining !== null && $remaining < 10) {
        Log::warning("Rate limit running low: {$remaining} requests remaining");
    }
}
```

---

## Server Errors (5xx)

Errors on Anthropic's side. Usually transient.

### 500 - api_error

**Cause:** Internal server error on Anthropic's side.

**Error Response:**

```json
{
    "type": "error",
    "error": {
        "type": "api_error",
        "message": "Internal server error"
    }
}
```

**Solution:** Retry with exponential backoff.

```php
# filename: ApiErrorHandler.php
class ApiErrorHandler
{
    public function makeRequestWithRetry(callable $request, int $maxAttempts = 3): mixed
    {
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            try {
                return $request();
            } catch (ErrorException $e) {
                if ($e->getErrorType() === 'api_error' && $attempt < $maxAttempts - 1) {
                    $attempt++;
                    $waitTime = min(pow(2, $attempt) * 1000000, 32000000); // Max 32s
                    usleep($waitTime);
                    continue;
                }
                throw $e;
            }
        }
    }
}
```

---

### 529 - overloaded_error

**Cause:** Anthropic's API is temporarily overloaded.

**Error Response:**

```json
{
    "type": "error",
    "error": {
        "type": "overloaded_error",
        "message": "Overloaded"
    }
}
```

**Solution:** Retry with longer delays.

```php
# filename: OverloadHandler.php
class OverloadHandler
{
    public function makeRequest(callable $request, int $maxRetries = 5): mixed
    {
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                return $request();
            } catch (ErrorException $e) {
                if ($e->getErrorType() === 'overloaded_error') {
                    $attempt++;

                    if ($attempt >= $maxRetries) {
                        throw new RuntimeException('API overloaded after max retries', 0, $e);
                    }

                    // Longer delays for overload: 5s, 10s, 20s, 40s, 80s
                    $delay = min(5 * pow(2, $attempt) * 1000000, 80000000);
                    usleep($delay);
                    continue;
                }
                throw $e;
            }
        }
    }
}
```

---

## SDK-Specific Errors

### Connection Timeout

**Cause:** Request took too long to complete.

```php
# filename: connection-timeout.php
// Set custom timeout
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY');
```

### SSL Certificate Errors

**Cause:** SSL verification issues.

```php
# filename: ssl-certificate-errors.php
// Development only - DO NOT use in production
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY');

// Production - specify CA bundle
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY');
```

---

## Common Issues

### Issue: Empty or Null Response

**Symptoms:**
- Response object is null
- No content in response
- Empty message

**Causes & Solutions:**

```php
# filename: fix-empty-response.php
// 1. Check stop_reason
if ($response->stop_reason === 'max_tokens') {
    // Response was truncated - increase max_tokens
    $params['max_tokens'] = 2048;
}

// 2. Verify response structure
if (empty($response->content)) {
    Log::error('Empty response content', ['response' => $response]);
}

// 3. Check for tool_use stop
if ($response->stop_reason === 'tool_use') {
    // Claude wants to use a tool - handle it
    foreach ($response->content as $block) {
        if ($block->type === 'tool_use') {
            // Execute tool and continue conversation
        }
    }
}
```

---

### Issue: Unexpected Response Format

**Symptoms:**
- JSON parsing errors
- Missing expected fields
- Wrong data types

**Solution:**

```php
# filename: ResponseValidator.php
class ResponseValidator
{
    public static function validateMessageResponse($response): void
    {
        if (!isset($response->id)) {
            throw new RuntimeException('Response missing id field');
        }

        if (!isset($response->content) || !is_array($response->content)) {
            throw new RuntimeException('Response missing or invalid content');
        }

        if (empty($response->content)) {
            throw new RuntimeException('Response content is empty');
        }

        if (!isset($response->usage)) {
            throw new RuntimeException('Response missing usage field');
        }
    }

    public static function extractText($response): string
    {
        self::validateMessageResponse($response);

        $textBlocks = array_filter(
            $response->content,
            fn($block) => $block->type === 'text'
        );

        if (empty($textBlocks)) {
            throw new RuntimeException('No text blocks in response');
        }

        return implode("\n", array_map(
            fn($block) => $block->text,
            $textBlocks
        ));
    }
}

// Usage
try {
    $response = $client->messages()->create($params);
    ResponseValidator::validateMessageResponse($response);
    $text = ResponseValidator::extractText($response);
} catch (RuntimeException $e) {
    Log::error('Invalid response', ['error' => $e->getMessage()]);
}
```

---

### Issue: Conversation Context Lost

**Symptoms:**
- Claude doesn't remember previous messages
- Responses lack context
- Conversation feels disjointed

**Solution:**

```php
# filename: ConversationManager.php
class ConversationManager
{
    private array $messages = [];
    private int $maxTokens = 100000; // Keep under 200K limit

    public function addMessage(string $role, string $content): void
    {
        $this->messages[] = [
            'role' => $role,
            'content' => $content
        ];

        $this->pruneIfNeeded();
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    private function pruneIfNeeded(): void
    {
        // Rough token estimation: 1 token ≈ 4 characters
        $totalChars = array_sum(array_map(
            fn($msg) => strlen($msg['content']),
            $this->messages
        ));

        $estimatedTokens = $totalChars / 4;

        if ($estimatedTokens > $this->maxTokens) {
            // Keep system message and recent messages
            $system = array_shift($this->messages); // Remove first (system)
            $this->messages = array_slice($this->messages, -20); // Keep last 20
            array_unshift($this->messages, $system); // Add system back
        }
    }
}

// Usage
$conversation = new ConversationManager();
$conversation->addMessage('user', 'Hello');

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => $conversation->getMessages()
]);

$conversation->addMessage('assistant', $response->content[0]['text']);
```

---

### Issue: Tool Use Not Working

**Symptoms:**
- Claude doesn't use defined tools
- Tool calls malformed
- Tool results not processed

**Debug Steps:**

```php
# filename: debug-tool-use.php
// 1. Verify tool definition format
$tools = [
    [
        'name' => 'get_weather',
        'description' => 'Get weather for a city', // Clear description
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'city' => [
                    'type' => 'string',
                    'description' => 'City name' // Describe each param
                ]
            ],
            'required' => ['city'] // Specify required fields
        ]
    ]
];

// 2. Log tool use attempts
foreach ($response->content as $block) {
    if ($block->type === 'tool_use') {
        Log::info('Tool use detected', [
            'tool' => $block->name,
            'input' => $block->input,
            'id' => $block->id
        ]);
    }
}

// 3. Ensure tool results are properly formatted
$toolResult = [
    'type' => 'tool_result',
    'tool_use_id' => $block->id, // Must match tool_use id
    'content' => json_encode($result) // String content
];
```

---

## Debugging Strategies

### Enable Debug Logging

```php
# filename: ClaudeDebugger.php
class ClaudeDebugger
{
    private $client;
    private bool $debugMode;

    public function __construct($client, bool $debugMode = false)
    {
        $this->client = $client;
        $this->debugMode = $debugMode;
    }

    public function createMessage(array $params)
    {
        if ($this->debugMode) {
            Log::debug('Claude Request', [
                'model' => $params['model'],
                'max_tokens' => $params['max_tokens'],
                'message_count' => count($params['messages']),
                'has_tools' => isset($params['tools']),
                'temperature' => $params['temperature'] ?? 1.0
            ]);
        }

        $startTime = microtime(true);

        try {
            $response = $this->client->messages()->create($params);

            if ($this->debugMode) {
                $duration = microtime(true) - $startTime;
                Log::debug('Claude Response', [
                    'duration' => round($duration, 2) . 's',
                    'stop_reason' => $response->stop_reason,
                    'input_tokens' => $response->usage->input_tokens,
                    'output_tokens' => $response->usage->output_tokens,
                    'cost' => $this->estimateCost($response, $params['model'])
                ]);
            }

            return $response;
        } catch (\Exception $e) {
            if ($this->debugMode) {
                Log::error('Claude Error', [
                    'error' => $e->getMessage(),
                    'type' => method_exists($e, 'getErrorType') ? $e->getErrorType() : 'unknown',
                    'params' => $params
                ]);
            }
            throw $e;
        }
    }

    private function estimateCost($response, string $model): float
    {
        $pricing = [
            'claude-opus-4-20250514' => ['input' => 15, 'output' => 75],
            'claude-sonnet-4-20250514' => ['input' => 3, 'output' => 15],
            'claude-haiku-4-20250514' => ['input' => 0.8, 'output' => 4],
        ];

        $rates = $pricing[$model] ?? ['input' => 3, 'output' => 15];

        $inputCost = ($response->usage->input_tokens / 1_000_000) * $rates['input'];
        $outputCost = ($response->usage->output_tokens / 1_000_000) * $rates['output'];

        return round($inputCost + $outputCost, 6);
    }
}

// Usage
$debugger = new ClaudeDebugger($client, debugMode: true);
$response = $debugger->createMessage($params);
```

### Request/Response Inspector

```php
# filename: RequestInspector.php
class RequestInspector
{
    public static function inspect(array $params): void
    {
        echo "=== REQUEST INSPECTION ===\n";
        echo "Model: {$params['model']}\n";
        echo "Max Tokens: {$params['max_tokens']}\n";
        echo "Messages: " . count($params['messages']) . "\n";

        foreach ($params['messages'] as $i => $msg) {
            $length = is_string($msg['content'])
                ? strlen($msg['content'])
                : count($msg['content']);
            echo "  [{$i}] {$msg['role']}: {$length} chars/blocks\n";
        }

        if (isset($params['tools'])) {
            echo "Tools: " . count($params['tools']) . "\n";
            foreach ($params['tools'] as $tool) {
                echo "  - {$tool['name']}\n";
            }
        }

        if (isset($params['temperature'])) {
            echo "Temperature: {$params['temperature']}\n";
        }

        echo "========================\n\n";
    }

    public static function inspectResponse($response): void
    {
        echo "=== RESPONSE INSPECTION ===\n";
        echo "ID: {$response->id}\n";
        echo "Model: {$response->model}\n";
        echo "Stop Reason: {$response->stop_reason}\n";
        echo "Input Tokens: {$response->usage->input_tokens}\n";
        echo "Output Tokens: {$response->usage->output_tokens}\n";
        echo "Content Blocks: " . count($response->content) . "\n";

        foreach ($response->content as $i => $block) {
            echo "  [{$i}] Type: {$block->type}\n";
            if ($block->type === 'text') {
                echo "      Length: " . strlen($block->text) . " chars\n";
            } elseif ($block->type === 'tool_use') {
                echo "      Tool: {$block->name}\n";
            }
        }

        echo "===========================\n\n";
    }
}

// Usage
RequestInspector::inspect($params);
$response = $client->messages()->create($params);
RequestInspector::inspectResponse($response);
```

---

## Best Practices

### Comprehensive Error Handling

```php
# filename: ClaudeClient.php
class ClaudeClient
{
    private $client;
    private LoggerInterface $logger;

    public function sendMessage(array $params): ?object
    {
        try {
            return $this->client->messages()->create($params);

        } catch (ErrorException $e) {
            return $this->handleApiError($e, $params);

        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            $this->logger->error('Connection failed', [
                'error' => $e->getMessage()
            ]);
            throw new RuntimeException('Failed to connect to Claude API', 0, $e);

        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $this->logger->error('Request failed', [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            throw new RuntimeException('Claude API request failed', 0, $e);

        } catch (\Exception $e) {
            $this->logger->error('Unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function handleApiError(ErrorException $e, array $params): ?object
    {
        $errorType = $e->getErrorType();

        $this->logger->warning('Claude API error', [
            'type' => $errorType,
            'message' => $e->getMessage(),
            'status' => $e->getStatusCode()
        ]);

        return match($errorType) {
            'rate_limit_error' => $this->retryWithBackoff($params),
            'overloaded_error' => $this->retryWithBackoff($params, maxAttempts: 5),
            'api_error' => $this->retryWithBackoff($params, maxAttempts: 3),
            'invalid_request_error' => throw new InvalidArgumentException(
                'Invalid request: ' . $e->getMessage(),
                0,
                $e
            ),
            'authentication_error' => throw new RuntimeException(
                'Authentication failed - check API key',
                0,
                $e
            ),
            default => throw $e
        };
    }

    private function retryWithBackoff(array $params, int $maxAttempts = 3): ?object
    {
        // Implementation from earlier examples
        // ...
    }
}
```

### Circuit Breaker Pattern

```php
# filename: CircuitBreaker.php
class CircuitBreaker
{
    private const STATE_CLOSED = 'closed';
    private const STATE_OPEN = 'open';
    private const STATE_HALF_OPEN = 'half_open';

    private string $state = self::STATE_CLOSED;
    private int $failures = 0;
    private int $threshold = 5;
    private int $timeout = 60; // seconds
    private ?int $openedAt = null;

    public function execute(callable $callback)
    {
        if ($this->state === self::STATE_OPEN) {
            if (time() - $this->openedAt >= $this->timeout) {
                $this->state = self::STATE_HALF_OPEN;
            } else {
                throw new RuntimeException('Circuit breaker is OPEN');
            }
        }

        try {
            $result = $callback();
            $this->onSuccess();
            return $result;
        } catch (\Exception $e) {
            $this->onFailure();
            throw $e;
        }
    }

    private function onSuccess(): void
    {
        $this->failures = 0;
        $this->state = self::STATE_CLOSED;
    }

    private function onFailure(): void
    {
        $this->failures++;

        if ($this->failures >= $this->threshold) {
            $this->state = self::STATE_OPEN;
            $this->openedAt = time();
        }
    }
}

// Usage
$breaker = new CircuitBreaker();

try {
    $response = $breaker->execute(fn() => $client->messages()->create($params));
} catch (RuntimeException $e) {
    if ($e->getMessage() === 'Circuit breaker is OPEN') {
        // Use fallback or cached response
        $response = $this->getFallbackResponse();
    }
}
```

---

## Getting Help

If you're still stuck after trying these solutions:

1. **Check API Status**: [status.anthropic.com](https://status.anthropic.com)
2. **Official Docs**: [docs.claude.com](https://docs.claude.com)
3. **Discord Community**: [discord.gg/anthropic](https://discord.gg/anthropic)
4. **GitHub Issues**: [github.com/anthropics/claude-php/Claude-PHP-SDK](https://github.com/anthropics/claude-php/Claude-PHP-SDK)

---

::: tip Quick Navigation
- **[← Appendix A: API Reference](/series/claude-php-developers/appendices/a-api-reference)** - Complete API reference
- **[← Appendix B: Prompting Patterns](/series/claude-php-developers/appendices/b-prompting-patterns)** - Prompt templates
- **[← Appendix C: Error Codes](/series/claude-php-developers/appendices/c-error-codes)** - Troubleshooting guide
- **[Appendix D: Resources →](/series/claude-php-developers/appendices/d-resources)** - Tools and resources
- **[Back to Series](/series/claude-php-developers)** - Return to main series
:::

_Last updated: November 2024_
