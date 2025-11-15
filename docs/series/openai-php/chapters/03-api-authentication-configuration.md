---
title: "03: API Authentication & Configuration"
description: "Master secure API key management, organization settings, and rate limit handling for production OpenAI applications"
series: "openai-php"
chapter: 3
order: 3
difficulty: "Intermediate"
prerequisites:
  - "/series/openai-php/chapters/02-understanding-openai-models"
  - "Understanding of environment variables"
  - "Basic security concepts"
---

![API Authentication & Configuration](/images/openai-php/chapter-03-authentication-hero-full.webp)

[Home](/series/openai-php) > [Chapter 02](/series/openai-php/chapters/02-understanding-openai-models) > API Authentication & Configuration

# Chapter 03: API Authentication & Configuration

<span class="difficulty-badge difficulty-intermediate">Intermediate</span>
<span class="time-badge">40-50 minutes</span>

## Overview

Secure API authentication isn't just about keeping your API keys safe—it's about building a robust, production-ready system that protects your budget, prevents unauthorized access, and gracefully handles rate limits. Poor authentication practices can lead to exposed keys, unexpected bills, or service disruptions.

In this chapter, you'll learn professional API key management strategies used in production environments. We'll cover everything from basic key storage to advanced multi-key rotation strategies, organization management, and handling OpenAI's rate limiting system.

You'll build practical systems for managing multiple API keys, implementing automatic failover, tracking usage across projects, and securing keys at every layer of your application. By the end, you'll have the tools and knowledge to confidently deploy OpenAI integrations in any environment.

## What You'll Learn

- 🔑 **API Key Management**: Store and rotate keys securely across environments
- 🏢 **Organizations & Projects**: Structure your OpenAI account for teams
- 🚦 **Rate Limits**: Understand and handle API quotas effectively
- 🔄 **Key Rotation**: Implement zero-downtime key updates
- 🔒 **Security Best Practices**: Prevent key leakage and unauthorized access
- 📊 **Usage Tracking**: Monitor API consumption per key and project
- ⚙️ **Configuration Management**: Build flexible, environment-aware setups

## Prerequisites

- ✅ Completed Chapters 01-02
- ✅ Understanding of environment variables
- ✅ Basic PHP security knowledge
- ✅ Familiarity with .env files

---

## API Key Fundamentals

### Anatomy of an API Key

```
sk-proj-AbCdEfGhIjKlMnOpQrStUvWxYz1234567890AbCdEfGhIjKlMnOpQrStUvWxYz
│  │    └────────────────────────────────────────────────────────────────┘
│  │                            Key payload
│  └─── Project-scoped key indicator
└────── Service key prefix
```

**Key Types:**
- `sk-...`: Standard API key
- `sk-proj-...`: Project-scoped key (newer format)

### Key Permissions & Scopes

OpenAI API keys can have different permission levels:

```php
<?php

/**
 * API Key Configuration
 */

class ApiKeyConfig
{
    public function __construct(
        public readonly string $key,
        public readonly string $organization = '',
        public readonly string $project = '',
        public readonly string $environment = 'production',
        public readonly array $permissions = [],
    ) {}

    public function getHeaders(): array
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->key,
            'Content-Type' => 'application/json',
        ];

        if ($this->organization) {
            $headers['OpenAI-Organization'] = $this->organization;
        }

        if ($this->project) {
            $headers['OpenAI-Project'] = $this->project;
        }

        return $headers;
    }
}
```

---

## Secure Key Storage

### Environment-Based Configuration

```php
<?php

/**
 * Environment-aware key management
 */

class KeyManager
{
    private static ?self $instance = null;
    private array $keys = [];

    private function __construct()
    {
        $this->loadKeys();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadKeys(): void
    {
        // Load environment-specific keys
        $env = $_ENV['APP_ENV'] ?? 'production';

        $this->keys = [
            'default' => [
                'key' => $_ENV['OPENAI_API_KEY'] ?? '',
                'organization' => $_ENV['OPENAI_ORG_ID'] ?? '',
                'project' => $_ENV['OPENAI_PROJECT_ID'] ?? '',
            ],
            'fallback' => [
                'key' => $_ENV['OPENAI_API_KEY_FALLBACK'] ?? '',
                'organization' => $_ENV['OPENAI_ORG_ID'] ?? '',
                'project' => $_ENV['OPENAI_PROJECT_ID'] ?? '',
            ],
        ];

        // Development-specific key
        if ($env === 'development') {
            $this->keys['dev'] = [
                'key' => $_ENV['OPENAI_API_KEY_DEV'] ?? $this->keys['default']['key'],
                'organization' => $_ENV['OPENAI_ORG_ID'] ?? '',
            ];
        }
    }

    public function getKey(string $name = 'default'): ApiKeyConfig
    {
        $config = $this->keys[$name] ?? $this->keys['default'];

        if (empty($config['key'])) {
            throw new \RuntimeException("API key '$name' not configured");
        }

        return new ApiKeyConfig(
            key: $config['key'],
            organization: $config['organization'],
            project: $config['project'] ?? '',
            environment: $_ENV['APP_ENV'] ?? 'production',
        );
    }

    public function getAllKeys(): array
    {
        return array_keys($this->keys);
    }
}
```

### .env Configuration

```bash
# .env file structure for multiple environments

# Primary API Key
OPENAI_API_KEY=sk-proj-primary-key-here
OPENAI_ORG_ID=org-xxxxxxxxxxxxx
OPENAI_PROJECT_ID=proj_xxxxxxxxxxxxx

# Fallback Key (for rate limit handling)
OPENAI_API_KEY_FALLBACK=sk-proj-fallback-key-here

# Development Key (separate billing)
OPENAI_API_KEY_DEV=sk-proj-dev-key-here

# Environment
APP_ENV=production

# Rate Limiting
RATE_LIMIT_PER_MINUTE=3500
RATE_LIMIT_PER_DAY=200000
```

---

## Organization & Project Management

### Understanding OpenAI's Hierarchy

```
Organization (org-xxx)
├── Project 1 (proj_xxx)
│   ├── API Key 1
│   └── API Key 2
├── Project 2 (proj_yyy)
│   ├── API Key 3
│   └── API Key 4
└── Default Project
    └── API Key 5
```

### Project-Based Configuration

```php
<?php

/**
 * Multi-Project OpenAI Client
 */

class MultiProjectClient
{
    private array $clients = [];

    public function __construct(
        private readonly KeyManager $keyManager
    ) {}

    public function getClient(string $project = 'default'): \OpenAI\Client
    {
        if (!isset($this->clients[$project])) {
            $keyConfig = $this->keyManager->getKey($project);

            $this->clients[$project] = \OpenAI::factory()
                ->withApiKey($keyConfig->key)
                ->withOrganization($keyConfig->organization)
                ->withHttpHeader('OpenAI-Project', $keyConfig->project)
                ->make();
        }

        return $this->clients[$project];
    }

    public function forProject(string $projectId): \OpenAI\Client
    {
        // Create client with specific project header
        $keyConfig = $this->keyManager->getKey('default');

        return \OpenAI::factory()
            ->withApiKey($keyConfig->key)
            ->withOrganization($keyConfig->organization)
            ->withHttpHeader('OpenAI-Project', $projectId)
            ->make();
    }
}

// Usage
$multiClient = new MultiProjectClient(KeyManager::getInstance());

// Use default project
$response = $multiClient->getClient('default')->chat()->create([
    'model' => 'gpt-3.5-turbo',
    'messages' => [['role' => 'user', 'content' => 'Hello']],
]);

// Use specific project
$devResponse = $multiClient->getClient('dev')->chat()->create([
    'model' => 'gpt-3.5-turbo',
    'messages' => [['role' => 'user', 'content' => 'Test']],
]);
```

---

## Rate Limits & Quotas

### Understanding Rate Limits

OpenAI enforces several types of limits:

**Rate Limits:**
- **Requests Per Minute (RPM)**: Maximum API calls per minute
- **Tokens Per Minute (TPM)**: Maximum tokens processed per minute
- **Tokens Per Day (TPD)**: Daily token quota

**Typical Limits (vary by tier):**
```
Free Tier:
- RPM: 3 requests/min
- TPM: 40,000 tokens/min

Pay-as-you-go:
- RPM: 3,500 requests/min (GPT-3.5)
- RPM: 500 requests/min (GPT-4)
- TPM: 90,000 tokens/min (GPT-3.5)
- TPM: 10,000 tokens/min (GPT-4)
```

### Rate Limit Detector

```php
<?php

/**
 * Rate Limit Detection and Handling
 */

class RateLimitHandler
{
    private array $limits = [];

    public function handleResponse(\Psr\Http\Message\ResponseInterface $response): void
    {
        $headers = $response->getHeaders();

        // Extract rate limit info from headers
        $this->limits = [
            'limit_requests' => (int) ($headers['x-ratelimit-limit-requests'][0] ?? 0),
            'limit_tokens' => (int) ($headers['x-ratelimit-limit-tokens'][0] ?? 0),
            'remaining_requests' => (int) ($headers['x-ratelimit-remaining-requests'][0] ?? 0),
            'remaining_tokens' => (int) ($headers['x-ratelimit-remaining-tokens'][0] ?? 0),
            'reset_requests' => $headers['x-ratelimit-reset-requests'][0] ?? null,
            'reset_tokens' => $headers['x-ratelimit-reset-tokens'][0] ?? null,
        ];
    }

    public function isNearLimit(float $threshold = 0.9): bool
    {
        $requestUtilization = 1 - ($this->limits['remaining_requests'] /
                                   max($this->limits['limit_requests'], 1));

        return $requestUtilization >= $threshold;
    }

    public function getWaitTime(): int
    {
        // Parse reset time (format: "1m30s" or "2s")
        $resetTime = $this->limits['reset_requests'] ?? '0s';

        if (preg_match('/(\d+)m(\d+)s/', $resetTime, $matches)) {
            return (int)$matches[1] * 60 + (int)$matches[2];
        }

        if (preg_match('/(\d+)s/', $resetTime, $matches)) {
            return (int)$matches[1];
        }

        return 60; // Default 60 seconds
    }

    public function getRemainingPercentage(): float
    {
        return ($this->limits['remaining_requests'] /
                max($this->limits['limit_requests'], 1)) * 100;
    }

    public function getLimits(): array
    {
        return $this->limits;
    }
}
```

### Automatic Rate Limit Handling

```php
<?php

/**
 * Client with automatic rate limit handling
 */

class RateLimitAwareClient
{
    private RateLimitHandler $rateLimitHandler;
    private int $maxRetries = 3;

    public function __construct(
        private readonly \OpenAI\Client $client
    ) {
        $this->rateLimitHandler = new RateLimitHandler();
    }

    public function chatCompletion(array $params): mixed
    {
        $attempt = 0;

        while ($attempt < $this->maxRetries) {
            try {
                $response = $this->client->chat()->create($params);
                return $response;

            } catch (\OpenAI\Exceptions\ErrorException $e) {
                $attempt++;

                // Check if it's a rate limit error
                if ($e->getCode() === 429) {
                    $waitTime = $this->calculateBackoff($attempt);

                    error_log(sprintf(
                        "Rate limit hit. Waiting %d seconds (attempt %d/%d)",
                        $waitTime,
                        $attempt,
                        $this->maxRetries
                    ));

                    sleep($waitTime);
                    continue;
                }

                // Re-throw if not rate limit error
                throw $e;
            }
        }

        throw new \RuntimeException("Max retries exceeded");
    }

    private function calculateBackoff(int $attempt): int
    {
        // Exponential backoff: 2^attempt seconds
        return min(pow(2, $attempt), 60);
    }
}
```

---

## API Key Rotation

### Zero-Downtime Key Rotation

```php
<?php

/**
 * Key rotation with graceful fallback
 */

class RotatingKeyManager
{
    private array $activeKeys = [];
    private int $currentKeyIndex = 0;

    public function __construct()
    {
        $this->loadActiveKeys();
    }

    private function loadActiveKeys(): void
    {
        // Load all active keys
        $keys = [
            $_ENV['OPENAI_API_KEY'],
            $_ENV['OPENAI_API_KEY_FALLBACK'] ?? null,
            $_ENV['OPENAI_API_KEY_BACKUP'] ?? null,
        ];

        $this->activeKeys = array_filter($keys, fn($key) => !empty($key));

        if (empty($this->activeKeys)) {
            throw new \RuntimeException("No active API keys configured");
        }
    }

    public function getCurrentKey(): string
    {
        return $this->activeKeys[$this->currentKeyIndex];
    }

    public function rotateToNextKey(): bool
    {
        $this->currentKeyIndex++;

        if ($this->currentKeyIndex >= count($this->activeKeys)) {
            $this->currentKeyIndex = 0;
            return false; // All keys exhausted
        }

        error_log("Rotated to key index: {$this->currentKeyIndex}");
        return true;
    }

    public function executeWithRotation(callable $operation): mixed
    {
        $lastException = null;

        foreach ($this->activeKeys as $index => $key) {
            $this->currentKeyIndex = $index;

            try {
                return $operation($key);
            } catch (\OpenAI\Exceptions\ErrorException $e) {
                $lastException = $e;

                if ($e->getCode() === 429 || $e->getCode() === 401) {
                    // Rate limit or auth error - try next key
                    error_log("Key {$index} failed, trying next key");
                    continue;
                }

                // Other errors - don't rotate
                throw $e;
            }
        }

        throw $lastException ?? new \RuntimeException("All keys failed");
    }
}

// Usage
$keyManager = new RotatingKeyManager();

$result = $keyManager->executeWithRotation(function($apiKey) {
    $client = \OpenAI::client($apiKey);
    return $client->chat()->create([
        'model' => 'gpt-3.5-turbo',
        'messages' => [['role' => 'user', 'content' => 'Hello']],
    ]);
});
```

---

## Usage Tracking

```php
<?php

/**
 * API Usage Tracker
 */

class UsageTracker
{
    private string $logFile;

    public function __construct(string $logFile = 'openai_usage.log')
    {
        $this->logFile = $logFile;
    }

    public function track(
        string $model,
        int $promptTokens,
        int $completionTokens,
        float $cost,
        string $project = 'default',
        array $metadata = []
    ): void {
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'model' => $model,
            'project' => $project,
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $promptTokens + $completionTokens,
            'cost' => $cost,
            'metadata' => $metadata,
        ];

        file_put_contents(
            $this->logFile,
            json_encode($entry) . PHP_EOL,
            FILE_APPEND
        );
    }

    public function getUsageStats(string $period = 'today'): array
    {
        if (!file_exists($this->logFile)) {
            return $this->emptyStats();
        }

        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $stats = $this->emptyStats();

        $startDate = match($period) {
            'today' => date('Y-m-d'),
            'week' => date('Y-m-d', strtotime('-7 days')),
            'month' => date('Y-m-d', strtotime('-30 days')),
            default => '1970-01-01',
        };

        foreach ($lines as $line) {
            $entry = json_decode($line, true);

            if ($entry && $entry['timestamp'] >= $startDate) {
                $stats['total_requests']++;
                $stats['total_tokens'] += $entry['total_tokens'];
                $stats['total_cost'] += $entry['cost'];
                $stats['by_model'][$entry['model']] =
                    ($stats['by_model'][$entry['model']] ?? 0) + 1;
            }
        }

        return $stats;
    }

    private function emptyStats(): array
    {
        return [
            'total_requests' => 0,
            'total_tokens' => 0,
            'total_cost' => 0.0,
            'by_model' => [],
        ];
    }
}

// Usage with OpenAI client
$tracker = new UsageTracker();

$response = $client->chat()->create([
    'model' => 'gpt-3.5-turbo',
    'messages' => [['role' => 'user', 'content' => 'Hello']],
]);

// Track usage
$tracker->track(
    model: $response->model,
    promptTokens: $response->usage->promptTokens,
    completionTokens: $response->usage->completionTokens,
    cost: calculateCost($response),
    project: 'my-project',
    metadata: ['user_id' => 123]
);
```

---

## Security Best Practices

### Secure Configuration Class

```php
<?php

/**
 * Secure configuration management
 */

class SecureConfig
{
    private static array $config = [];
    private static bool $loaded = false;

    public static function load(string $envFile = '.env'): void
    {
        if (self::$loaded) {
            return;
        }

        // Load environment variables
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname($envFile));
        $dotenv->load();

        // Validate required variables
        $dotenv->required([
            'OPENAI_API_KEY',
            'APP_ENV',
        ])->notEmpty();

        // Validate API key format
        if (!self::isValidApiKey($_ENV['OPENAI_API_KEY'])) {
            throw new \RuntimeException("Invalid OPENAI_API_KEY format");
        }

        self::$loaded = true;
    }

    private static function isValidApiKey(string $key): bool
    {
        // Validate format: sk-... or sk-proj-...
        return preg_match('/^sk-(proj-)?[a-zA-Z0-9]{32,}$/', $key) === 1;
    }

    public static function getApiKey(): string
    {
        if (!self::$loaded) {
            throw new \RuntimeException("Configuration not loaded");
        }

        return $_ENV['OPENAI_API_KEY'];
    }

    public static function maskApiKey(string $key): string
    {
        // Show only first 7 and last 4 characters
        if (strlen($key) < 15) {
            return '***';
        }

        return substr($key, 0, 7) . '...' . substr($key, -4);
    }

    public static function isProduction(): bool
    {
        return ($_ENV['APP_ENV'] ?? 'production') === 'production';
    }
}
```

### Preventing Key Leakage

```php
<?php

/**
 * Sanitize logs to prevent key leakage
 */

class SecureLogger
{
    private static array $sensitivePatterns = [
        '/sk-[a-zA-Z0-9]{32,}/',  // API keys
        '/"api_key"\s*:\s*"[^"]+"/i',  // JSON api_key fields
        '/Bearer\s+sk-[a-zA-Z0-9]+/i',  // Authorization headers
    ];

    public static function sanitize(string $message): string
    {
        $sanitized = $message;

        foreach (self::$sensitivePatterns as $pattern) {
            $sanitized = preg_replace($pattern, '[REDACTED]', $sanitized);
        }

        return $sanitized;
    }

    public static function log(string $level, string $message, array $context = []): void
    {
        $sanitized = self::sanitize($message);

        // Use your logging framework
        error_log("[$level] $sanitized");
    }
}

// Usage
SecureLogger::log('info', "Making API call with key: {$apiKey}");
// Logs: [info] Making API call with key: [REDACTED]
```

---

## Exercises

### Exercise 1: Multi-Environment Setup

Create a configuration that:
1. Uses different keys for dev/staging/production
2. Validates keys on startup
3. Logs which key is active
4. Prevents production keys in development

### Exercise 2: Rate Limit Dashboard

Build a simple dashboard that:
1. Shows current rate limit status
2. Displays remaining requests/tokens
3. Estimates time until reset
4. Warns when approaching limits

### Exercise 3: Key Rotation System

Implement:
1. Scheduled key rotation (e.g., every 90 days)
2. Notification before expiration
3. Automatic failover to backup key
4. Rollback capability

### Exercise 4: Usage Cost Alert

Create a system that:
1. Tracks daily API costs
2. Sends alert when threshold reached
3. Can automatically switch to cheaper model
4. Provides cost breakdown by project

---

## Key Takeaways

- ✅ Never commit API keys to version control—use environment variables
- ✅ Implement multiple keys for fallback and rotation
- ✅ Monitor rate limits proactively to avoid service disruptions
- ✅ Use organization and project IDs for better usage tracking
- ✅ Implement automatic retry with exponential backoff for rate limits
- ✅ Track usage per project/key for cost allocation
- ✅ Rotate API keys regularly (every 90 days recommended)
- ✅ Sanitize logs to prevent key leakage

---

## Next Steps

You now have a robust authentication and configuration system! Next, we'll dive into HTTP clients and API integration patterns.

👉 **[Chapter 04: HTTP Clients & API Integration](/series/openai-php/chapters/04-http-clients-api-integration)**

In the next chapter, you'll learn:
- Comparing HTTP clients (Guzzle, cURL, Symfony)
- Building custom API wrappers
- Request/response middleware
- Connection pooling and optimization

---

[← Previous: Chapter 02](/series/openai-php/chapters/02-understanding-openai-models) | [Next: Chapter 04 →](/series/openai-php/chapters/04-http-clients-api-integration)
