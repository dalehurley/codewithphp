---
title: "02: Authentication and API Keys"
description: "Master secure authentication with Claude API. Learn to set up your Anthropic account, generate and manage API keys, implement secure authentication in PHP, use environment variables, and handle key rotation."
series: "claude-php-developers"
chapter: 2
order: 2
difficulty: "Beginner"
prerequisites:
  - "PHP 8.2+ installed"
  - "Composer installed"
  - "Completion of Chapter 01"
---

![02: Authentication and API Keys](/images/claude-php/chapter-02-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 02</span>
</div>

# Chapter 02: Authentication and API Keys

## Overview

Security is paramount when working with AI APIs. A leaked API key can lead to unauthorized usage, unexpected costs, and potential data breaches. This chapter provides a comprehensive guide to secure authentication with the Claude API.

You'll learn how to properly set up your Anthropic account, generate and manage API keys, implement secure authentication patterns in PHP, use environment variables correctly, handle key rotation, and follow security best practices. By the end, you'll have production-ready authentication strategies that protect your API keys and your budget.

**What You'll Learn:**
- Setting up your Anthropic account correctly
- Generating and managing API keys
- Secure authentication patterns in PHP
- Environment variable management
- Key rotation strategies
- Security best practices and common pitfalls

**Estimated Time**: 25-35 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapter 01** (Introduction to Claude API)
- ✓ **Email address** for account creation
- ✓ **Payment method** (required for API access)
- ✓ **PHP 8.2+** with Composer installed

## Setting Up Your Anthropic Account

### Creating Your Account

1. **Navigate to Anthropic Console**
   - Visit [console.anthropic.com](https://console.anthropic.com)
   - Click "Sign Up" or "Get Started"

2. **Registration**
   - Enter your email address
   - Create a strong password
   - Verify your email address

3. **Account Information**
   - Complete your profile
   - Add organization details (for team usage)
   - Read and accept terms of service

::: warning
Use a corporate email for business projects. Personal emails should only be used for personal development.
:::

### Adding Payment Information

API access requires a payment method on file:

1. **Navigate to Billing**
   - Go to Settings → Billing
   - Click "Add Payment Method"

2. **Add Card**
   - Enter credit/debit card information
   - Verify billing address
   - Save payment method

3. **Set Budget Limits** (Recommended)
   - Click "Usage Limits"
   - Set monthly budget limit (e.g., $100)
   - Enable email notifications
   - Set alert thresholds (e.g., 50%, 80%, 100%)

::: info
Setting budget limits prevents unexpected charges. You'll receive alerts before reaching limits.
:::

### Understanding Billing

**How Billing Works:**
- **Pay-as-you-go**: Charged only for actual usage
- **No minimum fees**: Zero cost if you don't use the API
- **Token-based**: Billed per input/output token
- **Monthly invoicing**: Charges appear at month end

**Usage Tiers:**
Anthropic offers different tiers with varying rate limits:

| Tier | Requirements | Rate Limit |
|------|-------------|------------|
| **Tier 1** | New accounts | 50 requests/min |
| **Tier 2** | $25 spent | 1,000 requests/min |
| **Tier 3** | $250 spent | 2,000 requests/min |
| **Tier 4** | $1,000+ spent | 4,000+ requests/min |

::: tip
Start with Tier 1, upgrade automatically as you use more. Contact Anthropic for custom enterprise limits.
:::

## Generating API Keys

### Creating Your First API Key

1. **Navigate to API Keys**
   - Go to Settings → API Keys
   - Click "Create Key"

2. **Configure Key**
   - Name: `development-local` (descriptive names help)
   - Permissions: Full access (default)
   - Click "Create Key"

3. **Save the Key**
   - Copy the key immediately (starts with `sk-ant-`)
   - Store securely (you won't see it again)
   - Never share or commit to version control

**Example Key Format:**
```
sk-ant-api03-1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890
```

::: danger
The key is shown only once! If lost, you must generate a new key and update all applications using it.
:::

### API Key Naming Convention

Use descriptive names that indicate purpose and environment:

```
# Good examples
production-web-app
staging-api-server
development-john-laptop
testing-ci-pipeline
backup-key-2025

# Bad examples
key1
my-key
test
```

### Managing Multiple Keys

For different environments and team members:

```php
<?php
# .env.production
ANTHROPIC_API_KEY=sk-ant-api03-production-key-here

# .env.staging
ANTHROPIC_API_KEY=sk-ant-api03-staging-key-here

# .env.development
ANTHROPIC_API_KEY=sk-ant-api03-development-key-here
```

**Benefits:**
- **Isolation**: Separate keys per environment
- **Tracking**: Monitor usage by environment
- **Security**: Revoke compromised keys without affecting others
- **Team Management**: Individual keys for team members

### Viewing and Revoking Keys

**View Active Keys:**
1. Go to Settings → API Keys
2. See list of all active keys with:
   - Key name
   - Creation date
   - Last used date
   - Usage statistics

**Revoke a Key:**
1. Find the key in the list
2. Click "Revoke" or trash icon
3. Confirm revocation
4. Key becomes invalid immediately

::: warning
Revoking a key immediately breaks any application using it. Update applications before revoking production keys.
:::

## Secure Authentication in PHP

### Basic Authentication

The simplest way to authenticate:

```php
<?php
# filename: examples/basic-auth.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

// Direct API key (NOT RECOMMENDED for production)
$client = Anthropic::factory()
    ->withApiKey('sk-ant-api03-your-key-here')
    ->make();

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'Hello, Claude!']
    ]
]);

echo $response->content[0]->text;
```

::: danger
Never hardcode API keys in source code! This is for demonstration only.
:::

### Environment Variable Authentication

**Recommended approach** for all environments:

```php
<?php
# filename: examples/env-auth.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

// Load API key from environment variable
$apiKey = getenv('ANTHROPIC_API_KEY');

if (!$apiKey) {
    die("Error: ANTHROPIC_API_KEY environment variable not set\n");
}

$client = Anthropic::factory()
    ->withApiKey($apiKey)
    ->make();
```

Set environment variable:

```bash
# Linux/Mac
export ANTHROPIC_API_KEY="sk-ant-your-key-here"

# Windows PowerShell
$env:ANTHROPIC_API_KEY="sk-ant-your-key-here"

# Windows CMD
set ANTHROPIC_API_KEY=sk-ant-your-key-here
```

### Using .env Files

For local development, use `.env` files with `vlucas/phpdotenv`:

```bash
composer require vlucas/phpdotenv
```

Create `.env` file:

```bash
# .env
ANTHROPIC_API_KEY=sk-ant-api03-your-development-key-here
ANTHROPIC_MODEL=claude-sonnet-4-20250514
ANTHROPIC_MAX_TOKENS=2048
APP_ENV=development
```

**Load in PHP:**

```php
<?php
# filename: examples/dotenv-auth.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;
use Dotenv\Dotenv;

// Load .env file
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Require specific variables
$dotenv->required(['ANTHROPIC_API_KEY'])->notEmpty();

// Create client with environment config
$client = Anthropic::factory()
    ->withApiKey($_ENV['ANTHROPIC_API_KEY'])
    ->make();

// Use environment-based defaults
$response = $client->messages()->create([
    'model' => $_ENV['ANTHROPIC_MODEL'] ?? 'claude-sonnet-4-20250514',
    'max_tokens' => (int)($_ENV['ANTHROPIC_MAX_TOKENS'] ?? 1024),
    'messages' => [
        ['role' => 'user', 'content' => 'Hello!']
    ]
]);
```

**Important: Add .env to .gitignore**

```bash
# .gitignore
.env
.env.*
!.env.example
```

### Configuration Class Pattern

Centralize configuration management:

```php
<?php
# filename: src/Config/ClaudeConfig.php
declare(strict_types=1);

namespace App\Config;

class ClaudeConfig
{
    private string $apiKey;
    private string $model;
    private int $maxTokens;
    private float $temperature;
    private bool $debug;

    public function __construct()
    {
        $this->apiKey = $this->getEnv('ANTHROPIC_API_KEY');
        $this->model = $this->getEnv('ANTHROPIC_MODEL', 'claude-sonnet-4-20250514');
        $this->maxTokens = (int)$this->getEnv('ANTHROPIC_MAX_TOKENS', '2048');
        $this->temperature = (float)$this->getEnv('ANTHROPIC_TEMPERATURE', '1.0');
        $this->debug = $this->getEnv('APP_DEBUG', 'false') === 'true';

        $this->validate();
    }

    private function getEnv(string $key, ?string $default = null): string
    {
        $value = getenv($key) ?: $_ENV[$key] ?? $default;

        if ($value === null) {
            throw new \RuntimeException("Required environment variable {$key} is not set");
        }

        return $value;
    }

    private function validate(): void
    {
        // Validate API key format
        if (!str_starts_with($this->apiKey, 'sk-ant-')) {
            throw new \InvalidArgumentException('Invalid API key format');
        }

        // Validate model
        $validModels = [
            'claude-opus-4-20250514',
            'claude-sonnet-4-20250514',
            'claude-haiku-4-20250514',
        ];

        if (!in_array($this->model, $validModels)) {
            throw new \InvalidArgumentException("Invalid model: {$this->model}");
        }

        // Validate max tokens
        if ($this->maxTokens < 1 || $this->maxTokens > 16384) {
            throw new \InvalidArgumentException('max_tokens must be between 1 and 16384');
        }

        // Validate temperature
        if ($this->temperature < 0 || $this->temperature > 1) {
            throw new \InvalidArgumentException('temperature must be between 0.0 and 1.0');
        }
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getMaxTokens(): int
    {
        return $this->maxTokens;
    }

    public function getTemperature(): float
    {
        return $this->temperature;
    }

    public function isDebug(): bool
    {
        return $this->debug;
    }

    public function toArray(): array
    {
        return [
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
        ];
    }
}
```

**Usage:**

```php
<?php
# filename: examples/config-pattern.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Config\ClaudeConfig;
use Anthropic\Anthropic;

// Load configuration
$config = new ClaudeConfig();

// Create client
$client = Anthropic::factory()
    ->withApiKey($config->getApiKey())
    ->make();

// Use configuration defaults
$response = $client->messages()->create([
    ...$config->toArray(),
    'messages' => [
        ['role' => 'user', 'content' => 'Hello!']
    ]
]);
```

### Dependency Injection Pattern

For larger applications using DI containers:

```php
<?php
# filename: src/Services/ClaudeService.php
declare(strict_types=1);

namespace App\Services;

use Anthropic\Anthropic;
use Anthropic\Resources\Messages;
use App\Config\ClaudeConfig;

class ClaudeService
{
    private Messages $messages;

    public function __construct(ClaudeConfig $config)
    {
        $client = Anthropic::factory()
            ->withApiKey($config->getApiKey())
            ->make();

        $this->messages = $client->messages();
    }

    public function chat(string $prompt, ?string $systemPrompt = null): string
    {
        $params = [
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ];

        if ($systemPrompt) {
            $params['system'] = $systemPrompt;
        }

        $response = $this->messages->create($params);

        return $response->content[0]->text;
    }
}
```

**Laravel Service Provider:**

```php
<?php
# filename: app/Providers/ClaudeServiceProvider.php
declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ClaudeService;
use App\Config\ClaudeConfig;

class ClaudeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ClaudeConfig::class, function () {
            return new ClaudeConfig();
        });

        $this->app->singleton(ClaudeService::class, function ($app) {
            return new ClaudeService($app->make(ClaudeConfig::class));
        });
    }
}
```

**Usage in Controller:**

```php
<?php
# filename: app/Http/Controllers/ChatController.php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ClaudeService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(
        private readonly ClaudeService $claude
    ) {}

    public function chat(Request $request)
    {
        $message = $request->input('message');
        $response = $this->claude->chat($message);

        return response()->json(['response' => $response]);
    }
}
```

## Environment Variables Best Practices

### Development Environment

Create `.env` file for local development:

```bash
# .env
APP_NAME=MyApp
APP_ENV=development
APP_DEBUG=true

ANTHROPIC_API_KEY=sk-ant-api03-dev-key-here
ANTHROPIC_MODEL=claude-sonnet-4-20250514
ANTHROPIC_MAX_TOKENS=2048
ANTHROPIC_TIMEOUT=30

LOG_LEVEL=debug
```

### Production Environment

**Never use .env files in production.** Instead, set environment variables at the system level.

**Apache (.htaccess):**

```apache
SetEnv ANTHROPIC_API_KEY "sk-ant-api03-prod-key-here"
SetEnv ANTHROPIC_MODEL "claude-sonnet-4-20250514"
```

**Nginx (with PHP-FPM):**

```nginx
# /etc/php/8.2/fpm/pool.d/www.conf
env[ANTHROPIC_API_KEY] = sk-ant-api03-prod-key-here
env[ANTHROPIC_MODEL] = claude-sonnet-4-20250514
```

**Docker:**

```dockerfile
# docker-compose.yml
version: '3.8'
services:
  app:
    build: .
    environment:
      - ANTHROPIC_API_KEY=${ANTHROPIC_API_KEY}
      - ANTHROPIC_MODEL=claude-sonnet-4-20250514
```

```bash
# Set in .env file (not committed)
ANTHROPIC_API_KEY=sk-ant-api03-prod-key-here
```

**Kubernetes (Secrets):**

```yaml
# secret.yaml
apiVersion: v1
kind: Secret
metadata:
  name: claude-api-secret
type: Opaque
stringData:
  api-key: sk-ant-api03-prod-key-here
```

```yaml
# deployment.yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: my-app
spec:
  template:
    spec:
      containers:
      - name: app
        env:
        - name: ANTHROPIC_API_KEY
          valueFrom:
            secretKeyRef:
              name: claude-api-secret
              key: api-key
```

**AWS Lambda:**

```bash
aws lambda update-function-configuration \
  --function-name my-function \
  --environment Variables={ANTHROPIC_API_KEY=sk-ant-api03-prod-key-here}
```

### Environment Variable Validation

Always validate environment variables on startup:

```php
<?php
# filename: src/Bootstrap/EnvironmentValidator.php
declare(strict_types=1);

namespace App\Bootstrap;

class EnvironmentValidator
{
    private const REQUIRED_VARS = [
        'ANTHROPIC_API_KEY' => 'sk-ant-',
        'APP_ENV' => null,
    ];

    private const OPTIONAL_VARS = [
        'ANTHROPIC_MODEL' => 'claude-sonnet-4-20250514',
        'ANTHROPIC_MAX_TOKENS' => '2048',
        'ANTHROPIC_TIMEOUT' => '30',
    ];

    public static function validate(): void
    {
        // Check required variables
        foreach (self::REQUIRED_VARS as $var => $prefix) {
            $value = getenv($var) ?: $_ENV[$var] ?? null;

            if (!$value) {
                throw new \RuntimeException("Missing required environment variable: {$var}");
            }

            if ($prefix && !str_starts_with($value, $prefix)) {
                throw new \RuntimeException(
                    "Invalid format for {$var}. Expected to start with: {$prefix}"
                );
            }
        }

        // Set defaults for optional variables
        foreach (self::OPTIONAL_VARS as $var => $default) {
            if (!getenv($var) && !isset($_ENV[$var])) {
                putenv("{$var}={$default}");
                $_ENV[$var] = $default;
            }
        }
    }

    public static function checkHealth(): array
    {
        $health = [
            'status' => 'healthy',
            'checks' => []
        ];

        // Check API key exists
        $apiKey = getenv('ANTHROPIC_API_KEY');
        $health['checks']['api_key'] = [
            'status' => $apiKey ? 'ok' : 'error',
            'format' => str_starts_with($apiKey, 'sk-ant-') ? 'valid' : 'invalid'
        ];

        // Check environment
        $env = getenv('APP_ENV');
        $health['checks']['environment'] = [
            'status' => 'ok',
            'value' => $env
        ];

        // Determine overall status
        foreach ($health['checks'] as $check) {
            if ($check['status'] === 'error') {
                $health['status'] = 'unhealthy';
                break;
            }
        }

        return $health;
    }
}
```

**Bootstrap your application:**

```php
<?php
# filename: bootstrap.php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\Bootstrap\EnvironmentValidator;
use Dotenv\Dotenv;

// Load .env in non-production environments
if (getenv('APP_ENV') !== 'production') {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Validate environment
try {
    EnvironmentValidator::validate();
} catch (\RuntimeException $e) {
    error_log("Environment validation failed: " . $e->getMessage());
    http_response_code(500);
    die("Application configuration error\n");
}

// Application continues...
```

## Key Rotation Strategies

Regular key rotation improves security and limits exposure if a key is compromised.

### When to Rotate Keys

**Scheduled Rotation:**
- Every 90 days (recommended)
- Every 180 days (minimum)

**Immediate Rotation:**
- Key suspected of being compromised
- Team member with key access leaves
- Security audit recommendation
- After security incident

### Zero-Downtime Rotation

Rotate keys without service interruption:

**Step 1: Generate New Key**
```bash
# Create new key in Anthropic Console
# Name it with version: production-v2
```

**Step 2: Dual-Key Period**
```php
<?php
# filename: src/Services/DualKeyClaudeService.php
declare(strict_types=1);

namespace App\Services;

use Anthropic\Anthropic;

class DualKeyClaudeService
{
    private array $clients = [];

    public function __construct()
    {
        // Primary key (new)
        $primaryKey = getenv('ANTHROPIC_API_KEY_PRIMARY');
        if ($primaryKey) {
            $this->clients['primary'] = $this->createClient($primaryKey);
        }

        // Secondary key (old - fallback)
        $secondaryKey = getenv('ANTHROPIC_API_KEY_SECONDARY');
        if ($secondaryKey) {
            $this->clients['secondary'] = $this->createClient($secondaryKey);
        }
    }

    private function createClient(string $apiKey): Anthropic
    {
        return Anthropic::factory()
            ->withApiKey($apiKey)
            ->make();
    }

    public function chat(string $prompt): string
    {
        // Try primary key first
        if (isset($this->clients['primary'])) {
            try {
                return $this->attemptChat($this->clients['primary'], $prompt);
            } catch (\Exception $e) {
                error_log("Primary key failed: " . $e->getMessage());
            }
        }

        // Fallback to secondary key
        if (isset($this->clients['secondary'])) {
            try {
                return $this->attemptChat($this->clients['secondary'], $prompt);
            } catch (\Exception $e) {
                error_log("Secondary key failed: " . $e->getMessage());
                throw $e;
            }
        }

        throw new \RuntimeException('No valid API keys available');
    }

    private function attemptChat($client, string $prompt): string
    {
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        return $response->content[0]->text;
    }
}
```

**Step 3: Update Environment Variables**
```bash
# Deploy with both keys
ANTHROPIC_API_KEY_PRIMARY=sk-ant-api03-new-key-here
ANTHROPIC_API_KEY_SECONDARY=sk-ant-api03-old-key-here
```

**Step 4: Monitor and Verify**
```bash
# Monitor logs for 24-48 hours
# Ensure all services using new key
# Check error rates
```

**Step 5: Remove Old Key**
```bash
# After verification period
# Update to single key
ANTHROPIC_API_KEY=sk-ant-api03-new-key-here

# Revoke old key in Anthropic Console
```

### Automated Rotation Script

```php
<?php
# filename: scripts/rotate-api-key.php
declare(strict_types=1);

/**
 * API Key Rotation Helper
 *
 * Usage:
 * 1. Generate new key manually in Anthropic Console
 * 2. Run: php rotate-api-key.php <new-key>
 * 3. Script updates .env.production
 * 4. Deploy updated configuration
 * 5. Wait 48 hours
 * 6. Revoke old key
 */

if ($argc < 2) {
    echo "Usage: php rotate-api-key.php <new-key>\n";
    exit(1);
}

$newKey = $argv[1];

// Validate key format
if (!str_starts_with($newKey, 'sk-ant-')) {
    echo "Error: Invalid API key format\n";
    exit(1);
}

$envFile = __DIR__ . '/../.env.production';
$backupFile = $envFile . '.backup.' . date('Y-m-d-His');

// Backup current .env
if (!copy($envFile, $backupFile)) {
    echo "Error: Could not create backup\n";
    exit(1);
}

echo "Created backup: {$backupFile}\n";

// Read current .env
$lines = file($envFile, FILE_IGNORE_NEW_LINES);
$updated = false;

foreach ($lines as &$line) {
    if (str_starts_with($line, 'ANTHROPIC_API_KEY=')) {
        $oldKey = substr($line, 18);
        $line = "ANTHROPIC_API_KEY_PRIMARY={$newKey}";

        // Add secondary (old) key on next line
        $line .= "\nANTHROPIC_API_KEY_SECONDARY={$oldKey}";
        $updated = true;

        echo "Updated configuration:\n";
        echo "  Primary (new): {$newKey}\n";
        echo "  Secondary (old): {$oldKey}\n";
    }
}

if (!$updated) {
    echo "Error: ANTHROPIC_API_KEY not found in .env\n";
    exit(1);
}

// Write updated .env
file_put_contents($envFile, implode("\n", $lines) . "\n");

echo "\n✓ Configuration updated successfully\n";
echo "\nNext steps:\n";
echo "1. Review changes: diff {$backupFile} {$envFile}\n";
echo "2. Deploy updated configuration\n";
echo "3. Monitor logs for 24-48 hours\n";
echo "4. Run: php finalize-rotation.php (after verification)\n";
```

## Security Best Practices

### 1. Never Commit API Keys

**Bad:**
```php
<?php
// NEVER DO THIS
$apiKey = 'sk-ant-api03-1234567890abcdef';
```

**Good:**
```php
<?php
// Always use environment variables
$apiKey = getenv('ANTHROPIC_API_KEY');
```

**Ensure .gitignore is configured:**
```bash
# .gitignore
.env
.env.*
!.env.example
*.key
secrets/
config/secrets.php
```

### 2. Restrict Key Permissions

Create separate keys for different purposes:

```bash
# Read-only key for analytics
read-only-analytics-key

# Limited key for specific features
feature-x-key

# Admin key (restricted access)
admin-key
```

### 3. Monitor Key Usage

```php
<?php
# filename: src/Monitoring/ApiKeyMonitor.php
declare(strict_types=1);

namespace App\Monitoring;

class ApiKeyMonitor
{
    private string $logFile;

    public function __construct(string $logFile = '/var/log/claude-api.log')
    {
        $this->logFile = $logFile;
    }

    public function logRequest(
        string $keyPrefix,
        string $model,
        int $inputTokens,
        int $outputTokens,
        float $cost
    ): void {
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'key_prefix' => $keyPrefix,
            'model' => $model,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'cost' => $cost,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ];

        $this->writeLog($entry);
        $this->checkAnomalies($entry);
    }

    private function writeLog(array $entry): void
    {
        $line = json_encode($entry) . "\n";
        file_put_contents($this->logFile, $line, FILE_APPEND | LOCK_EX);
    }

    private function checkAnomalies(array $entry): void
    {
        // Alert on unusually high token usage
        if ($entry['output_tokens'] > 10000) {
            $this->sendAlert("High token usage: {$entry['output_tokens']} tokens");
        }

        // Alert on expensive requests
        if ($entry['cost'] > 1.00) {
            $this->sendAlert("Expensive request: $" . $entry['cost']);
        }
    }

    private function sendAlert(string $message): void
    {
        error_log("ALERT: {$message}");
        // Send email, Slack notification, etc.
    }

    public function getKeyPrefix(string $apiKey): string
    {
        // Only log first/last 4 chars for security
        return substr($apiKey, 0, 10) . '...' . substr($apiKey, -4);
    }
}
```

### 4. Implement Rate Limiting

```php
<?php
# filename: src/Security/RateLimiter.php
declare(strict_types=1);

namespace App\Security;

class RateLimiter
{
    private string $cacheFile;
    private int $maxRequests;
    private int $timeWindow;

    public function __construct(
        string $cacheFile = '/tmp/rate-limit.json',
        int $maxRequests = 100,
        int $timeWindow = 60
    ) {
        $this->cacheFile = $cacheFile;
        $this->maxRequests = $maxRequests;
        $this->timeWindow = $timeWindow;
    }

    public function checkLimit(string $identifier): bool
    {
        $data = $this->loadData();
        $now = time();

        // Clean old entries
        $data = array_filter($data, fn($entry) => $entry['expires'] > $now);

        // Count recent requests for this identifier
        $key = md5($identifier);
        $recentRequests = array_filter(
            $data,
            fn($entry) => $entry['identifier'] === $key
        );

        if (count($recentRequests) >= $this->maxRequests) {
            return false; // Rate limit exceeded
        }

        // Add new request
        $data[] = [
            'identifier' => $key,
            'timestamp' => $now,
            'expires' => $now + $this->timeWindow
        ];

        $this->saveData($data);

        return true;
    }

    private function loadData(): array
    {
        if (!file_exists($this->cacheFile)) {
            return [];
        }

        $content = file_get_contents($this->cacheFile);
        return json_decode($content, true) ?? [];
    }

    private function saveData(array $data): void
    {
        file_put_contents(
            $this->cacheFile,
            json_encode($data),
            LOCK_EX
        );
    }
}
```

### 5. Encrypt Stored Keys

If you must store keys in a database:

```php
<?php
# filename: src/Security/KeyEncryption.php
declare(strict_types=1);

namespace App\Security;

class KeyEncryption
{
    private string $encryptionKey;

    public function __construct()
    {
        $this->encryptionKey = getenv('APP_ENCRYPTION_KEY');

        if (!$this->encryptionKey) {
            throw new \RuntimeException('APP_ENCRYPTION_KEY not set');
        }
    }

    public function encrypt(string $apiKey): string
    {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt(
            $apiKey,
            'AES-256-CBC',
            $this->encryptionKey,
            0,
            $iv
        );

        return base64_encode($iv . $encrypted);
    }

    public function decrypt(string $encryptedKey): string
    {
        $data = base64_decode($encryptedKey);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);

        return openssl_decrypt(
            $encrypted,
            'AES-256-CBC',
            $this->encryptionKey,
            0,
            $iv
        );
    }
}

// Usage
$encryption = new KeyEncryption();

// Encrypt before storing
$encryptedKey = $encryption->encrypt('sk-ant-api03-your-key');
// Store $encryptedKey in database

// Decrypt when needed
$apiKey = $encryption->decrypt($encryptedKey);
```

### 6. Audit Trail

```php
<?php
# filename: src/Audit/ApiKeyAudit.php
declare(strict_types=1);

namespace App\Audit;

class ApiKeyAudit
{
    public function logKeyAccess(string $keyId, string $action, ?string $user = null): void
    {
        $entry = [
            'timestamp' => date('c'),
            'key_id' => $keyId,
            'action' => $action,
            'user' => $user ?? $this->getCurrentUser(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ];

        $this->writeAuditLog($entry);
    }

    private function writeAuditLog(array $entry): void
    {
        $logFile = '/var/log/api-key-audit.log';
        $line = json_encode($entry) . "\n";
        file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }

    private function getCurrentUser(): string
    {
        return $_SERVER['USER'] ?? 'system';
    }

    public static function created(string $keyId, string $user): void
    {
        (new self())->logKeyAccess($keyId, 'created', $user);
    }

    public static function accessed(string $keyId): void
    {
        (new self())->logKeyAccess($keyId, 'accessed');
    }

    public static function revoked(string $keyId, string $user): void
    {
        (new self())->logKeyAccess($keyId, 'revoked', $user);
    }
}
```

## Common Security Pitfalls

### Pitfall 1: Committing .env Files

**Problem:**
```bash
git add .env
git commit -m "Add configuration"
```

**Solution:**
```bash
# Add to .gitignore FIRST
echo ".env" >> .gitignore
git add .gitignore
git commit -m "Ignore .env files"

# Create .env.example instead
cp .env .env.example
# Remove sensitive values from .env.example
git add .env.example
```

### Pitfall 2: Logging API Keys

**Problem:**
```php
<?php
error_log("Using API key: {$apiKey}");  // BAD!
```

**Solution:**
```php
<?php
$keyPreview = substr($apiKey, 0, 10) . '...';
error_log("Using API key: {$keyPreview}");  // GOOD
```

### Pitfall 3: Client-Side Exposure

**Problem:**
```javascript
// NEVER do this in JavaScript
const apiKey = 'sk-ant-api03-your-key';
```

**Solution:**
```php
<?php
// Always make API calls from server-side PHP
// Never expose API keys to client-side code
```

### Pitfall 4: Insufficient Access Controls

**Problem:**
```php
<?php
// Anyone can access
$apiKey = file_get_contents('/config/api-key.txt');
```

**Solution:**
```bash
# Restrict file permissions
chmod 600 /config/api-key.txt
chown www-data:www-data /config/api-key.txt
```

## Exercises

### Exercise 1: Secure Configuration Manager

Build a configuration manager with validation and encryption:

```php
<?php
class SecureConfigManager
{
    public function __construct(private string $configPath) {}

    public function loadConfig(): array
    {
        // TODO: Load and validate configuration
        // TODO: Decrypt sensitive values
        // TODO: Validate API key format
        // TODO: Return configuration array
    }

    public function saveConfig(array $config): void
    {
        // TODO: Validate config structure
        // TODO: Encrypt sensitive values
        // TODO: Save to secure location
    }
}
```

### Exercise 2: Key Rotation Automation

Create an automated key rotation system:

```php
<?php
class KeyRotationManager
{
    public function initiateRotation(string $newKey): void
    {
        // TODO: Validate new key
        // TODO: Update configuration with dual keys
        // TODO: Log rotation event
        // TODO: Notify administrators
    }

    public function finalizeRotation(): void
    {
        // TODO: Remove old key
        // TODO: Update to single key
        // TODO: Log completion
    }

    public function shouldRotate(): bool
    {
        // TODO: Check last rotation date
        // TODO: Return true if > 90 days
    }
}
```

### Exercise 3: Usage Monitor

Build a system to monitor and alert on unusual API usage:

```php
<?php
class UsageMonitor
{
    public function trackRequest($response): void
    {
        // TODO: Extract token usage
        // TODO: Calculate cost
        // TODO: Store in database/log
        // TODO: Check for anomalies
    }

    public function getDailyUsage(): array
    {
        // TODO: Aggregate today's usage
        // TODO: Return summary with costs
    }

    public function checkBudget(): bool
    {
        // TODO: Compare usage to budget
        // TODO: Return true if under budget
    }
}
```

<details>
<summary>Solution Hints</summary>

**Exercise 1**: Use openssl_encrypt for encryption, validate API key with regex, implement singleton pattern for config instance.

**Exercise 2**: Store rotation metadata in JSON file, use file locking for concurrent access, implement notification system (email/Slack).

**Exercise 3**: Store usage in SQLite database, aggregate with SQL queries, implement alerting thresholds (50%, 80%, 100% of budget).

</details>

## Troubleshooting

**API key not recognized?**
- Verify key starts with `sk-ant-`
- Check for extra spaces or newlines
- Ensure payment method is added to account
- Try generating a new key

**Environment variable not found?**
- Check variable name spelling
- Verify .env file is in correct directory
- Ensure Dotenv is loaded before accessing variables
- Check if using $_ENV vs getenv()

**Key works locally but not in production?**
- Verify environment variables are set in production
- Check file permissions on .env file
- Ensure web server has access to environment variables
- Review server logs for errors

**Rate limit errors immediately?**
- Check if using wrong key (test key vs production)
- Verify account tier and limits
- Implement exponential backoff
- Contact Anthropic support for limit increase

## Key Takeaways

- ✓ **Never hardcode API keys** in source code
- ✓ **Use environment variables** for all configurations
- ✓ **Add .env to .gitignore** before first commit
- ✓ **Rotate keys regularly** (every 90 days minimum)
- ✓ **Monitor usage** to detect anomalies
- ✓ **Set budget limits** to prevent unexpected costs
- ✓ **Use separate keys** for different environments
- ✓ **Implement rate limiting** to protect against abuse
- ✓ **Encrypt stored keys** if database storage is required
- ✓ **Maintain audit logs** for key access and usage

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="02"
  label="You've mastered secure API key management!"
/>

---

Continue to [Chapter 03: Your First Claude Request in PHP](/series/claude-php-developers/chapters/03-first-claude-request) to start making API calls.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 02 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-02)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-02
composer install
cp .env.example .env
# Add your API key to .env
php examples/dotenv-auth.php
```
