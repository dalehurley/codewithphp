---
title: "02: Authentication and API Keys"
description: "Master secure authentication with Claude API. Learn to set up your Anthropic account, generate and manage API keys, implement secure authentication in PHP, use environment variables, and handle key rotation."
sidebar:
  label: "02: Authentication and API Keys"
  order: 2
  badge:
    text: Beginner
    variant: success
---
![02: Authentication and API Keys](/images/claude-php/chapter-02-hero-full.webp)


# Chapter 02: Authentication and API Keys

## Overview

Security is paramount when working with AI APIs. A leaked API key can lead to unauthorized usage, unexpected costs, and potential data breaches. This chapter provides a comprehensive guide to secure authentication with the Claude API using the [Claude-PHP-SDK](https://github.com/claude-php/Claude-PHP-SDK).

You'll learn how to properly set up your Anthropic account, generate and manage API keys, implement secure authentication patterns in PHP, use environment variables correctly, handle key rotation, and follow security best practices. By the end, you'll have production-ready authentication strategies that protect your API keys and your budget.

**About the Claude-PHP-SDK:**

This chapter uses the [Claude-PHP-SDK](https://github.com/claude-php/Claude-PHP-SDK) v0.2 — a community-maintained PHP library that provides a streamlined interface for interacting with the Claude API. It's available on Packagist and can be installed via Composer with `composer require claude-php/claude-php-sdk:"^0.2":"^0.2"`. The SDK handles HTTP communication, error handling, and response parsing, allowing you to focus on building great applications.

**What You'll Learn:**

- Setting up your Anthropic account correctly
- Generating and managing API keys
- Secure authentication patterns in PHP using Claude-PHP-SDK
- Environment variable management
- CI/CD integration for automated pipelines
- Secrets management services for enterprise applications
- Key validation and testing strategies
- Multi-tenant API key management
- Key rotation strategies
- Testing strategies with API keys
- Security best practices and common pitfalls

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed [Chapter 01](/series/claude-php-developers/chapters/01-introduction-to-claude-api)** (Introduction to Claude API)
- ✓ **Email address** for account creation
- ✓ **Payment method** (required for API access)
- ✓ **PHP 8.4+** with Composer installed
- ✓ **Claude-PHP-SDK v0.2+**: Install via `composer require claude-php/claude-php-sdk:"^0.2":"^0.2"`

**Estimated Time**: ~25-35 minutes

**Quick Setup:**

```bash
# Create a new project
mkdir my-claude-app && cd my-claude-app
composer init
composer require claude-php/claude-php-sdk:"^0.2":"^0.2" vlucas/phpdotenv
```

## What You'll Build

By the end of this chapter, you will have created:

- A secure authentication system using environment variables
- A `ClaudeConfig` class for centralized configuration management
- A `ClaudeService` class with dependency injection support
- An `EnvironmentValidator` class for startup validation
- A `DualKeyClaudeService` class for zero-downtime key rotation
- An `ApiKeyMonitor` class for usage tracking and anomaly detection
- A `RateLimiter` class for protecting against abuse
- A `KeyEncryption` class for secure key storage
- An `ApiKeyAudit` class for comprehensive audit logging
- CI/CD integration examples for GitHub Actions, GitLab CI, and CircleCI
- AWS Secrets Manager integration for enterprise applications
- An `ApiKeyValidator` class for key validation and testing
- A `MultiTenantClaudeService` class for SaaS applications
- Testing strategies with mocks and real API calls
- Understanding of production-ready security best practices

## Objectives

- Set up your Anthropic account with proper billing and budget limits
- Generate and manage API keys with descriptive naming conventions
- Implement secure authentication patterns using environment variables
- Integrate API keys into CI/CD pipelines (GitHub Actions, GitLab CI, CircleCI)
- Use secrets management services for enterprise applications
- Validate API keys before use in production
- Manage API keys in multi-tenant SaaS applications
- Create a centralized configuration management system
- Build a zero-downtime key rotation system
- Implement monitoring and rate limiting for API key protection
- Write tests that safely handle API keys with mocks and real API calls
- Understand and apply security best practices for API key management
- Avoid common security pitfalls that lead to key exposure

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

| Tier       | Requirements  | Rate Limit          |
| ---------- | ------------- | ------------------- |
| **Tier 1** | New accounts  | 50 requests/min     |
| **Tier 2** | $25 spent     | 1,000 requests/min  |
| **Tier 3** | $250 spent    | 2,000 requests/min  |
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

::: info
**API Key Permissions**: Currently, Anthropic API keys provide full access to all API endpoints. There's no granular permission system (e.g., read-only vs read-write). All keys have the same permissions. Use separate keys per environment/team for better tracking and security isolation.
:::

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

### Multi-Tenant API Key Management

For SaaS applications serving multiple clients, manage API keys per tenant:

```php
<?php
# filename: src/Services/MultiTenantClaudeService.php
declare(strict_types=1);

namespace App\Services;

use ClaudePhp\ClaudePhp;
use App\Models\Tenant;

class MultiTenantClaudeService
{
    private array $clients = [];

    public function __construct(
        private readonly string $defaultApiKey
    ) {}

    /**
     * Get Claude client for specific tenant
     */
    public function getClientForTenant(Tenant $tenant): ClaudePhp
    {
        // Check if tenant has custom API key
        $apiKey = $tenant->anthropic_api_key ?? $this->defaultApiKey;

        // Cache clients per API key
        if (!isset($this->clients[$apiKey])) {
            $this->clients[$apiKey] = new ClaudePhp(
                apiKey: $apiKey
            );
        }

        return $this->clients[$apiKey];
    }

    /**
     * Make request on behalf of tenant
     */
    public function chatForTenant(Tenant $tenant, string $prompt): string
    {
        $client = $this->getClientForTenant($tenant);

        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 2048,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        // Track usage per tenant
        $this->trackUsage($tenant, $response->usage ?? null);

        return $response->content[0]->text;
    }

    private function trackUsage(Tenant $tenant, array $usage): void
    {
        // Log usage for billing/analytics
        // Implementation depends on your tracking system
    }
}
```

**Database Schema:**

```sql
-- tenants table
CREATE TABLE tenants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    anthropic_api_key VARCHAR(255) NULL, -- NULL = use default key
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_api_key (anthropic_api_key)
);

-- Usage tracking
CREATE TABLE tenant_api_usage (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT NOT NULL,
    input_tokens INT NOT NULL,
    output_tokens INT NOT NULL,
    cost DECIMAL(10, 6) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id),
    INDEX idx_tenant_date (tenant_id, created_at)
);
```

**Usage:**

```php
<?php
use App\Models\Tenant;
use App\Services\MultiTenantClaudeService;

$service = new MultiTenantClaudeService(getenv('ANTHROPIC_API_KEY_DEFAULT'));

$tenant = Tenant::find($tenantId);
$response = $service->chatForTenant($tenant, "Generate a product description");
```

**Benefits:**

- **Cost Allocation**: Track usage per tenant for accurate billing
- **Custom Keys**: Allow enterprise tenants to use their own API keys
- **Isolation**: Separate rate limits and usage per tenant
- **Security**: Revoke tenant-specific keys without affecting others

::: tip
For high-volume multi-tenant applications, consider using Anthropic's organization-level API keys or implementing a key pool system for better rate limit management.
:::

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

### Error Handling with Claude-PHP-SDK

The Claude-PHP-SDK provides specific exception types for different error conditions. Always use these for proper error handling:

```php
<?php
# filename: examples/sdk-error-handling.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use ClaudePhp\Exceptions\{
    APIConnectionError,
    RateLimitError,
    AuthenticationError,
    APIStatusError
};

$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-5-20250929',
        'max_tokens' => 1024,
        'messages' => [
            ['role' => 'user', 'content' => 'Hello, Claude!']
        ]
    ]);

    echo $response->content[0]->text;

} catch (AuthenticationError $e) {
    // Invalid API key or authentication failure
    error_log("Authentication failed: " . $e->getMessage());
    http_response_code(401);
    echo "Authentication failed. Please check your API key.\n";

} catch (RateLimitError $e) {
    // Rate limit exceeded
    $retryAfter = $e->response->getHeaderLine('retry-after');
    error_log("Rate limit exceeded. Retry after: {$retryAfter} seconds");
    http_response_code(429);
    echo "Rate limit exceeded. Please try again later.\n";

} catch (APIConnectionError $e) {
    // Network or connection issues
    error_log("Connection failed: " . $e->getMessage());
    http_response_code(503);
    echo "Service temporarily unavailable. Please try again.\n";

} catch (APIStatusError $e) {
    // Other API errors (4xx/5xx)
    error_log("API Error {$e->status_code}: " . $e->message);
    http_response_code(500);
    echo "An error occurred while processing your request.\n";

} catch (\Exception $e) {
    // Catch any other unexpected errors
    error_log("Unexpected error: " . $e->getMessage());
    http_response_code(500);
    echo "An unexpected error occurred.\n";
}
```

**Exception Types:**

- **`AuthenticationError`**: Invalid API key, expired key, or authentication failure
- **`RateLimitError`**: API rate limit exceeded (includes retry-after header)
- **`APIConnectionError`**: Network issues, timeouts, or connection failures
- **`APIStatusError`**: Other HTTP errors (4xx/5xx responses)

::: tip
Always catch `AuthenticationError` specifically to handle invalid or expired API keys gracefully.
:::

### Basic Authentication

The simplest way to authenticate:

```php
<?php
# filename: examples/basic-auth.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

// Direct API key (NOT RECOMMENDED for production)
$client = new ClaudePhp(
    apiKey: 'sk-ant-api03-your-key-here'
);

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5-20250929',
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

use ClaudePhp\ClaudePhp;

// Load API key from environment variable
$apiKey = $_ENV['ANTHROPIC_API_KEY'];

if (!$apiKey) {
    die("Error: ANTHROPIC_API_KEY environment variable not set\n");
}

$client = new ClaudePhp(
    apiKey: $apiKey
);
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
ANTHROPIC_MODEL=claude-sonnet-4-5
ANTHROPIC_MAX_TOKENS=2048
APP_ENV=development
```

**Load in PHP:**

```php
<?php
# filename: examples/dotenv-auth.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use Dotenv\Dotenv;

// Load .env file
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Require specific variables
$dotenv->required(['ANTHROPIC_API_KEY'])->notEmpty();

// Create client with environment config
$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

// Use environment-based defaults
$response = $client->messages()->create([
    'model' => $_ENV['ANTHROPIC_MODEL'] ?? 'claude-sonnet-4-5-20250929',
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

use ClaudePhp\ClaudePhp;

class ClaudeConfig
{
    private string $apiKey;
    private string $model;
    private int $maxTokens;
    private float $temperature;
    private bool $debug;
    private ClaudePhp $client;

    public function __construct()
    {
        $this->apiKey = $this->getEnv('ANTHROPIC_API_KEY');
        $this->model = $this->getEnv('ANTHROPIC_MODEL', 'claude-sonnet-4-5-20250929');
        $this->maxTokens = (int)$this->getEnv('ANTHROPIC_MAX_TOKENS', '2048');
        $this->temperature = (float)$this->getEnv('ANTHROPIC_TEMPERATURE', '1.0');
        $this->debug = $this->getEnv('APP_DEBUG', 'false') === 'true';

        $this->validate();
        $this->client = new ClaudePhp(
            apiKey: $this->apiKey
        );
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
            'claude-opus-4-1',
            'claude-opus-4-1-20250805',
            'claude-sonnet-4-5',
            'claude-sonnet-4-5-20250929',
            'claude-haiku-4-5-20251001',
            'claude-haiku-4-5-20251001',
        ];

        if (!in_array($this->model, $validModels)) {
            throw new \InvalidArgumentException("Invalid 'model' => {$this->model}");
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

    public function getClient(): ClaudePhp
    {
        return $this->client;
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

// Load configuration
$config = new ClaudeConfig();

// Get client from config
$client = $config->getClient();

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

use ClaudePhp\ClaudePhp;
use App\Config\ClaudeConfig;

class ClaudeService
{
    private ClaudePhp $client;

    public function __construct(ClaudeConfig $config)
    {
        $this->client = $config->getClient();
    }

    public function chat(string $prompt, ?string $systemPrompt = null): string
    {
        $params = [
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 2048,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ];

        if ($systemPrompt) {
            $params['system'] = $systemPrompt;
        }

        $response = $this->client->messages()->create($params);

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
ANTHROPIC_MODEL=claude-sonnet-4-5
ANTHROPIC_MAX_TOKENS=2048
ANTHROPIC_TIMEOUT=30

LOG_LEVEL=debug
```

### Production Environment

**Never use .env files in production.** Instead, set environment variables at the system level.

**Apache (.htaccess):**

```apache
SetEnv ANTHROPIC_API_KEY "sk-ant-api03-prod-key-here"
SetEnv ANTHROPIC_MODEL "claude-sonnet-4-5"
```

**Nginx (with PHP-FPM):**

```nginx
# /etc/php/8.2/fpm/pool.d/www.conf
env[ANTHROPIC_API_KEY] = sk-ant-api03-prod-key-here
env[ANTHROPIC_MODEL] = claude-sonnet-4-5
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
      - ANTHROPIC_MODEL=claude-sonnet-4-5
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

### CI/CD Integration

For automated testing and deployment pipelines, securely inject API keys as secrets:

**GitHub Actions:**

```yaml
# .github/workflows/test.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: "8.2"

      - name: Install dependencies
        run: composer install

      - name: Run tests
        env:
          ANTHROPIC_API_KEY: ${{ secrets.ANTHROPIC_API_KEY }}
        run: php vendor/bin/phpunit
```

**Setting up GitHub Secrets:**

1. Go to repository Settings → Secrets and variables → Actions
2. Click "New repository secret"
3. Name: `ANTHROPIC_API_KEY`
4. Value: Your API key (starts with `sk-ant-`)
5. Click "Add secret"

**GitLab CI:**

```yaml
# .gitlab-ci.yml
test:
  script:
    - composer install
    - php vendor/bin/phpunit
  variables:
    ANTHROPIC_API_KEY: $ANTHROPIC_API_KEY
```

**Setting up GitLab CI Variables:**

1. Go to Settings → CI/CD → Variables
2. Click "Add variable"
3. Key: `ANTHROPIC_API_KEY`
4. Value: Your API key
5. Check "Mask variable" and "Protect variable"
6. Click "Add variable"

**CircleCI:**

```yaml
# .circleci/config.yml
version: 2.1

jobs:
  test:
    docker:
      - image: cimg/php:8.2
    steps:
      - checkout
      - run: composer install
      - run:
          name: Run tests
          environment:
            ANTHROPIC_API_KEY: ${ANTHROPIC_API_KEY}
          command: php vendor/bin/phpunit
```

**Setting up CircleCI Environment Variables:**

1. Go to Project Settings → Environment Variables
2. Click "Add Environment Variable"
3. Name: `ANTHROPIC_API_KEY`
4. Value: Your API key
5. Click "Add Environment Variable"

::: tip
Use different API keys for CI/CD pipelines. Create a dedicated key named `ci-pipeline` or `github-actions` for better tracking and easier revocation if needed.
:::

### Secrets Management Services

For enterprise applications, consider using dedicated secrets management services instead of environment variables:

**When to Use Secrets Management:**

- **High-security requirements**: Compliance with SOC 2, HIPAA, or PCI-DSS
- **Multi-environment deployments**: Centralized secret management across environments
- **Automatic rotation**: Built-in key rotation capabilities
- **Audit requirements**: Comprehensive access logging and audit trails
- **Team collaboration**: Fine-grained access control for team members

**Available Services:**

| Service                          | Provider  | Best For                  |
| -------------------------------- | --------- | ------------------------- |
| **AWS Secrets Manager**          | Amazon    | AWS-hosted applications   |
| **Azure Key Vault**              | Microsoft | Azure-hosted applications |
| **Google Secret Manager**        | Google    | GCP-hosted applications   |
| **HashiCorp Vault**              | HashiCorp | Multi-cloud, on-premises  |
| **1Password Secrets Automation** | 1Password | Team collaboration        |

**Example: AWS Secrets Manager Integration**

```php
<?php
# filename: src/Security/AwsSecretsProvider.php
declare(strict_types=1);

namespace App\Security;

use Aws\SecretsManager\SecretsManagerClient;
use Aws\Exception\AwsException;

class AwsSecretsProvider
{
    private ?string $cachedKey = null;
    private ?int $cacheExpiry = null;
    private const CACHE_TTL = 3600; // 1 hour

    public function __construct(
        private readonly SecretsManagerClient $client,
        private readonly string $secretName
    ) {}

    public function getApiKey(): string
    {
        // Return cached key if still valid
        if ($this->cachedKey && $this->cacheExpiry > time()) {
            return $this->cachedKey;
        }

        try {
            $result = $this->client->getSecretValue([
                'SecretId' => $this->secretName,
            ]);

            if (isset($result['SecretString'])) {
                $secret = json_decode($result['SecretString'], true);
                $this->cachedKey = $secret['ANTHROPIC_API_KEY'] ?? throw new \RuntimeException('ANTHROPIC_API_KEY not found in secret');
                $this->cacheExpiry = time() + self::CACHE_TTL;

                return $this->cachedKey;
            }

            throw new \RuntimeException('Secret not found in expected format');

        } catch (AwsException $e) {
            error_log("[SECURITY] Failed to retrieve API key from Secrets Manager: {$e->getMessage()}");
            throw new \RuntimeException('Failed to retrieve API credentials', 0, $e);
        }
    }
}
```

**Installation:**

```bash
composer require aws/aws-sdk-php
```

**Usage:**

```php
<?php
use App\Security\AwsSecretsProvider;
use Aws\SecretsManager\SecretsManagerClient;
use ClaudePhp\ClaudePhp;

$secretsProvider = new AwsSecretsProvider(
    new SecretsManagerClient([
        'region' => 'us-east-1',
        'version' => 'latest'
    ]),
    'prod/anthropic/api-key'
);

$client = new ClaudePhp(
    apiKey: $secretsProvider->getApiKey()
);
```

::: info
For detailed secrets management integration patterns, see [Chapter 36: Security Best Practices](/series/claude-php-developers/chapters/36-security-best-practices) which covers advanced security patterns including comprehensive secrets management.
:::

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
        'ANTHROPIC_MODEL' => 'claude-sonnet-4-5-20250929',
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
        $apiKey = $_ENV['ANTHROPIC_API_KEY'];
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
use ClaudePhp\ClaudePhp;

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

### Key Validation and Testing

### Token Counting and Usage Monitoring

The Claude-PHP-SDK provides built-in token counting to help monitor API usage and costs:

```php
<?php
# filename: examples/token-counting.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

// Count tokens without making API call
$message = [
    'model' => 'claude-sonnet-4-5-20250929',
    'messages' => [
        ['role' => 'user', 'content' => 'Hello, how are you today?']
    ]
];

$tokenCount = $client->messages()->countTokens($message);
echo "Estimated input tokens: " . $tokenCount->input_tokens . "\n";

// After API call, get actual usage
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'Hello, how are you today?']
    ]
]);

$inputTokens = $response->usage->inputTokens;
$outputTokens = $response->usage->outputTokens;
$totalTokens = $inputTokens + $outputTokens;

echo "Actual usage - Input: {$inputTokens}, Output: {$outputTokens}, Total: {$totalTokens}\n";

// Cost calculation (approximate rates as of 2025)
$inputCostPerToken = 0.000003;  // $3 per million input tokens
$outputCostPerToken = 0.000015; // $15 per million output tokens

$totalCost = ($inputTokens * $inputCostPerToken) + ($outputTokens * $outputCostPerToken);
echo "Estimated cost: $" . number_format($totalCost, 6) . "\n";
```

**Token Counting Benefits:**

- **Cost Monitoring**: Track API usage costs in real-time
- **Rate Limiting**: Implement custom rate limits based on token usage
- **Budget Alerts**: Set up alerts when approaching spending limits
- **Usage Analytics**: Monitor which features consume the most tokens

### API Key Validation and Testing

Before using an API key in production, validate that it's active and has proper permissions:

```php
<?php
# filename: src/Security/ApiKeyValidator.php
declare(strict_types=1);

namespace App\Security;

use ClaudePhp\ClaudePhp;
use Exception;

class ApiKeyValidator
{
    public function __construct(
        private readonly ClaudePhp $client
    ) {}

    /**
     * Validate API key by making a lightweight test request
     */
    public function validateKey(string $apiKey): array
    {
        $testClient = new ClaudePhp(
            apiKey: $apiKey
        );

        try {
            // Make minimal test request (1 token, cheapest model)
            $response = $testClient->messages()->create([
                'model' => 'claude-haiku-4-5-20251001', // Cheapest model for validation
                'max_tokens' => 1, // Minimal token usage
                'messages' => [
                    ['role' => 'user', 'content' => 'Hi']
                ]
            ]);

            return [
                'valid' => true,
                'model' => $response->model ?? 'unknown',
                'tokens_used' => ($response->usage->inputTokens ?? 0) + ($response->usage->outputTokens ?? 0),
            ];

        } catch (Exception $e) {
            return [
                'valid' => false,
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
            ];
        }
    }

    /**
     * Quick format validation (doesn't make API call)
     */
    public function validateFormat(string $apiKey): bool
    {
        return str_starts_with($apiKey, 'sk-ant-') && strlen($apiKey) > 20;
    }

    /**
     * Check if key has sufficient permissions
     */
    public function checkPermissions(string $apiKey): array
    {
        $validation = $this->validateKey($apiKey);

        if (!$validation['valid']) {
            return [
                'has_permissions' => false,
                'error' => $validation['error'] ?? 'Key validation failed',
            ];
        }

        // If validation succeeded, key has basic permissions
        return [
            'has_permissions' => true,
            'can_read' => true,
            'can_write' => true, // Claude API keys are all-or-nothing
        ];
    }
}
```

**Usage:**

```php
<?php
# filename: examples/validate-key.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Security\ApiKeyValidator;
use ClaudePhp\ClaudePhp;

$apiKey = $_ENV['ANTHROPIC_API_KEY'];

if (!$apiKey) {
    die("Error: ANTHROPIC_API_KEY not set\n");
}

$client = new ClaudePhp(
    apiKey: $apiKey
);

$validator = new ApiKeyValidator($client);

// Quick format check (no API call)
if (!$validator->validateFormat($apiKey)) {
    die("Error: Invalid API key format\n");
}

echo "✓ API key format is valid\n";

// Full validation (makes lightweight API call)
$result = $validator->validateKey($apiKey);

if ($result['valid']) {
    echo "✓ API key is valid and active\n";
    echo "  Model tested: {$result['model']}\n";
    echo "  Tokens used: {$result['tokens_used']}\n";
} else {
    echo "✗ API key validation failed: {$result['error']}\n";
    exit(1);
}
```

**Bootstrap Validation:**

```php
<?php
# filename: bootstrap.php
declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\Security\ApiKeyValidator;
use ClaudePhp\ClaudePhp;

$apiKey = $_ENV['ANTHROPIC_API_KEY'];

if (!$apiKey) {
    throw new RuntimeException('ANTHROPIC_API_KEY not set');
}

// Quick format validation (no API call)
if (!str_starts_with($apiKey, 'sk-ant-')) {
    throw new InvalidArgumentException('Invalid API key format');
}

// Optional: Full validation (makes API call - use sparingly)
if (getenv('VALIDATE_API_KEY_ON_STARTUP') === 'true') {
    $client = new ClaudePhp(
        apiKey: $apiKey
    );
    $validator = new ApiKeyValidator($client);
    $result = $validator->validateKey($apiKey);

    if (!$result['valid']) {
        $errorType = $result['error_type'] ?? 'unknown';
        $errorMsg = $result['error'] ?? 'Validation failed';

        if ($errorType === 'authentication_error') {
            throw new \RuntimeException("Invalid API key: {$errorMsg}");
        } elseif ($errorType === 'rate_limit_error') {
            error_log("Rate limit hit during startup validation: {$errorMsg}");
            // Continue anyway - rate limits are temporary
        } else {
            throw new \RuntimeException("API key validation failed: {$errorMsg}");
        }
    }
}

// Application continues...
```

::: warning
Key validation makes an API call, which costs tokens. Use format validation for startup checks, and full validation only when necessary (e.g., after key rotation or in health checks).
:::

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

use ClaudePhp\ClaudePhp;
use ClaudePhp\Exceptions\AuthenticationError;

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

    private function createClient(string $apiKey): ClaudePhp
    {
        return new ClaudePhp(
            apiKey: $apiKey
        );
    }

    public function chat(string $prompt): string
    {
        // Try primary key first
        if (isset($this->clients['primary'])) {
            try {
                return $this->attemptChat($this->clients['primary'], $prompt);
            } catch (AuthenticationError $e) {
                error_log("Primary key authentication failed: " . $e->getMessage());
                // Primary key is invalid, don't retry
            } catch (\Exception $e) {
                error_log("Primary key failed: " . $e->getMessage());
                // Try secondary key for other errors
            }
        }

        // Fallback to secondary key
        if (isset($this->clients['secondary'])) {
            try {
                return $this->attemptChat($this->clients['secondary'], $prompt);
            } catch (AuthenticationError $e) {
                error_log("Secondary key authentication failed: " . $e->getMessage());
                throw new \RuntimeException('Both API keys are invalid', 0, $e);
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
            'model' => 'claude-sonnet-4-5-20250929',
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
$apiKey = $_ENV['ANTHROPIC_API_KEY'];
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

## Testing Strategies

When writing tests for code that uses Claude API, you need strategies for handling API keys safely.

### Using Test API Keys

Create dedicated API keys for testing:

```bash
# .env.testing
ANTHROPIC_API_KEY=sk-ant-api03-test-key-here
ANTHROPIC_MODEL=claude-haiku-4-5  # Cheapest for testing
```

**PHPUnit Configuration:**

```php
<?php
# filename: phpunit.xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="ANTHROPIC_API_KEY" value="sk-ant-api03-test-key-here"/>
    </php>
</phpunit>
```

### Mocking API Keys for Unit Tests

For unit tests that don't need real API calls:

```php
<?php
# filename: tests/Unit/ClaudeServiceTest.php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\ClaudeService;
use App\Config\ClaudeConfig;
use ClaudePhp\ClaudePhp;
use ClaudePhp\Exceptions\AuthenticationError;
use Mockery;

class ClaudeServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testServiceInitializesWithConfig(): void
    {
        // Mock configuration
        $config = Mockery::mock(ClaudeConfig::class);
        $mockClient = Mockery::mock(ClaudePhp::class);
        $config->shouldReceive('getClient')
            ->andReturn($mockClient);

        // Create service with mocked config
        $service = new ClaudeService($config);

        $this->assertInstanceOf(ClaudeService::class, $service);
    }

    public function testServiceHandlesAuthenticationErrors(): void
    {
        $config = Mockery::mock(ClaudeConfig::class);
        $mockClient = Mockery::mock(ClaudePhp::class);

        // Mock the messages API chain
        $messagesApi = Mockery::mock();
        $mockClient->shouldReceive('messages')
            ->andReturn($messagesApi);

        // Mock API call to throw AuthenticationError
        $messagesApi->shouldReceive('create')
            ->andThrow(new AuthenticationError('Invalid API key'));

        $config->shouldReceive('getClient')
            ->andReturn($mockClient);

        $service = new ClaudeService($config);

        $this->expectException(AuthenticationError::class);
        $this->expectExceptionMessage('Invalid API key');

        $service->chat('Test prompt');
    }
}
```

### Integration Testing with Real API

For integration tests that verify actual API behavior:

```php
<?php
# filename: tests/Integration/ClaudeApiTest.php
declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use ClaudePhp\ClaudePhp;
use ClaudePhp\Exceptions\AuthenticationError;

class ClaudeApiTest extends TestCase
{
    private ClaudePhp $client;

    protected function setUp(): void
    {
        $apiKey = getenv('ANTHROPIC_API_KEY_TEST');

        if (!$apiKey) {
            $this->markTestSkipped('ANTHROPIC_API_KEY_TEST not set');
        }

        $this->client = new ClaudePhp(
            apiKey: $apiKey
        );
    }

    public function testCanMakeApiRequest(): void
    {
        $response = $this->client->messages()->create([
            'model' => 'claude-haiku-4-5-20251001',
            'max_tokens' => 10,
            'messages' => [
                ['role' => 'user', 'content' => 'Say "test"']
            ]
        ]);

        $this->assertNotEmpty($response->content[0]->text);
        $this->assertEquals('claude-haiku-4-5-20251001', $response->model);
        $this->assertIsObject($response->usage);
        $this->assertIsInt($response->usage->inputTokens);
        $this->assertIsInt($response->usage->outputTokens);
    }

    public function testTokenCountingWorks(): void
    {
        $message = [
            'model' => 'claude-haiku-4-5-20251001',
            'messages' => [
                ['role' => 'user', 'content' => 'Hello world']
            ]
        ];

        $tokenCount = $this->client->messages()->countTokens($message);

        $this->assertIsInt($tokenCount->input_tokens);
        $this->assertGreaterThan(0, $tokenCount->input_tokens);
    }

    public function testHandlesAuthenticationError(): void
    {
        // Create client with invalid key
        $invalidClient = new ClaudePhp(
            apiKey: 'sk-ant-invalid-key-for-testing'
        );

        $this->expectException(AuthenticationError::class);

        $invalidClient->messages()->create([
            'model' => 'claude-haiku-4-5-20251001',
            'max_tokens' => 10,
            'messages' => [
                ['role' => 'user', 'content' => 'Test']
            ]
        ]);
    }
}
```

**Running Tests:**

```bash
# Unit tests (no API calls)
php vendor/bin/phpunit tests/Unit

# Integration tests (requires API key)
ANTHROPIC_API_KEY_TEST=sk-ant-api03-test-key-here php vendor/bin/phpunit tests/Integration
```

### Mocking API Responses

For tests that need predictable responses:

```php
<?php
# filename: tests/Unit/ClaudeServiceMockTest.php
declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use ClaudePhp\ClaudePhp;
use Mockery;

class ClaudeServiceMockTest extends TestCase
{
    public function testHandlesApiResponse(): void
    {
        // Mock the Claude client
        $client = Mockery::mock(ClaudePhp::class);
        $messagesApi = Mockery::mock();

        $client->shouldReceive('messages')
            ->andReturn($messagesApi);

        // Mock API response
        $mockResponse = [
            'content' => [
                ['text' => 'Mocked response']
            ],
            'model' => 'claude-sonnet-4-5-20250929',
            'usage' => [
                'input_tokens' => 10,
                'output_tokens' => 5,
            ],
        ];

        $messagesApi->shouldReceive('create')
            ->once()
            ->andReturn($mockResponse);

        // Test your service with mocked client
        // ... your test code ...
    }
}
```

### Test Environment Setup

**Separate Test Configuration:**

```php
<?php
# filename: tests/bootstrap.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

// Load test environment variables
if (file_exists(__DIR__ . '/../.env.testing')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..', '.env.testing');
    $dotenv->load();
}

// Validate test API key exists
if (!getenv('ANTHROPIC_API_KEY_TEST')) {
    throw new RuntimeException('ANTHROPIC_API_KEY_TEST must be set for integration tests');
}
```

**Best Practices:**

- **Use separate test keys**: Never use production keys in tests
- **Use cheapest model**: Use `claude-haiku-4-5` for testing to minimize costs
- **Limit token usage**: Set `max_tokens` to minimum needed (e.g., 10-50)
- **Mock when possible**: Use mocks for unit tests, real API only for integration tests
- **Skip expensive tests**: Use `markTestSkipped()` if test key not available
- **Track test costs**: Monitor usage of test API keys separately

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

### Pitfall 3: ClaudePhp-Side Exposure

**Problem:**

```javascript
// NEVER do this in JavaScript
const apiKey = "sk-ant-api03-your-key";
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
declare(strict_types=1);

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
declare(strict_types=1);

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
declare(strict_types=1);

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
- Check if using $\_ENV vs getenv()

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

## Further Reading

- **[Claude-PHP-SDK Repository](https://github.com/claude-php/Claude-PHP-SDK)** — The community-maintained PHP SDK for Claude API
- **[Claude-PHP-SDK on Packagist](https://packagist.org/packages/claude-php/claude-php-sdk)** — Install via Composer
- **[Anthropic API Documentation](https://docs.claude.com)** — Complete API reference and guides
- **[Official Anthropic PHP SDK](https://github.com/anthropics/anthropic-sdk-php)** — Alternative official SDK from Anthropic
- **[Environment Variables Best Practices](https://12factor.net/config)** — The Twelve-Factor App methodology for configuration management
- **[PHP Dotenv Documentation](https://github.com/vlucas/phpdotenv)** — Complete guide to managing .env files

## Wrap-up

Congratulations! You've completed a comprehensive guide to secure authentication with the Claude API. Here's what you've accomplished:

- ✓ **Set up your Anthropic account** with proper billing and budget limits
- ✓ **Generated and managed API keys** with descriptive naming conventions
- ✓ **Implemented secure authentication patterns** using environment variables
- ✓ **Created a centralized configuration management system** with validation
- ✓ **Built a zero-downtime key rotation system** for production environments
- ✓ **Implemented monitoring and rate limiting** for API key protection
- ✓ **Applied security best practices** to protect your API keys and budget
- ✓ **Avoided common security pitfalls** that lead to key exposure

### Key Concepts Learned

- **Environment Variables**: Never hardcode API keys; always use environment variables or secure configuration systems
- **Key Management**: Use descriptive names, separate keys per environment, and implement regular rotation (90 days minimum)
- **Configuration Patterns**: Centralize configuration with validation classes, use dependency injection for services
- **Key Rotation**: Implement zero-downtime rotation with dual-key periods and monitoring
- **Security Monitoring**: Track usage, detect anomalies, implement rate limiting, and maintain audit logs
- **Production Practices**: Encrypt stored keys, restrict file permissions, never log full keys, and use system-level environment variables

### Real-World Application

The authentication patterns and security practices you've learned in this chapter are essential for any production Claude application. The `ClaudeConfig` class provides centralized, validated configuration, `EnvironmentValidator` ensures proper setup, and the monitoring/audit classes help protect against abuse and track usage. These patterns form the security foundation that all subsequent chapters will build upon.

### Next Steps

You now have a secure authentication system in place. In the next chapter, you'll use these secure credentials to make your first Claude API requests and learn how to structure requests, parse responses, and handle errors effectively.

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

## Further Reading

- **[Claude-PHP-SDK Repository](https://github.com/claude-php/Claude-PHP-SDK)** — The community-maintained PHP SDK for Claude API with examples and documentation
- **[Anthropic API Keys Documentation](https://docs.claude.com/en/docs/get-started/quickstart)** — Official guide to managing API keys and account settings
- **[Environment Variables Best Practices](https://12factor.net/config)** — The Twelve-Factor App methodology for configuration management
- **[PHP Dotenv Documentation](https://github.com/vlucas/phpdotenv)** — Complete guide to using vlucas/phpdotenv for .env file management
- **[OWASP API Security Top 10](https://owasp.org/www-project-api-security/)** — Security best practices for API development
- **[Chapter 01: Introduction to Claude API](/series/claude-php-developers/chapters/01-introduction-to-claude-api)** — Review Claude's capabilities and architecture
- **[Chapter 03: Your First Claude Request in PHP](/series/claude-php-developers/chapters/03-first-claude-request)** — Start making API calls with your secure credentials


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
ANTHROPIC_API_KEY=sk-ant-your-key-here php examples/dotenv-auth.php
```

**Installation:**

The Claude-PHP-SDK is available on Packagist and can be installed via Composer:

```bash
composer require claude-php/claude-php-sdk:"^0.2"
```
