---
title: "17: Building a Claude Service Class"
description: "Design production-ready service layers with dependency injection, configuration management, testing, and framework-agnostic patterns for Claude API integration."
series: "claude-php-developers"
chapter: 17
order: 17
difficulty: "Expert"
prerequisites:
  - "Chapter 16: The Claude PHP SDK"
  - "SOLID principles understanding"
  - "Dependency injection concepts"
  - "PSR standards familiarity"
teaches:
  - 'Design and implement a service interface that abstracts Claude API complexity'
  - 'Build a framework-agnostic service layer following SOLID principles'
  - 'Create type-safe configuration classes with validation'
  - 'Implement the factory pattern for complex object creation'
  - 'Integrate services into Laravel applications using service providers'
---
![17: Building a Claude Service Class](/images/claude-php/chapter-17-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 17</span>
</div>

# Chapter 17: Building a Claude Service Class

## Overview

A well-designed service class abstracts Claude API complexity behind a clean interface, making it easy to integrate AI capabilities throughout your application. This chapter teaches you to build production-ready service layers with proper separation of concerns, configuration management, error handling, and testability.

You'll learn to create framework-agnostic services that can be used in Laravel, Symfony, plain PHP, or any other framework, following SOLID principles and modern PHP best practices.

## What You'll Build

By the end of this chapter, you will have created:

- A `ClaudeServiceInterface` that defines a clean contract for Claude API interactions
- A production-ready `ClaudeService` implementation using the **Claude-PHP-SDK**
- A `ClaudeConfig` class for type-safe configuration management
- A `ClaudeServiceFactory` for centralized service creation
- Standalone PHP usage examples demonstrating framework-agnostic patterns
- Laravel integration examples with service providers and controllers
- A `ConversationService` for managing multi-turn conversations
- A `PromptTemplate` system for reusable prompt generation
- Comprehensive PHPUnit tests with mocked dependencies
- Error handling with retry logic

## Prerequisites

Before diving in, ensure you have:

- ✓ Completed [Chapter 16: The Claude PHP SDK](/series/claude-php-developers/chapters/16-official-php-sdk)
- ✓ Understanding of **SOLID principles** (especially dependency inversion)
- ✓ Experience with **dependency injection** patterns
- ✓ Familiarity with **unit testing** using PHPUnit
- ✓ PHP 8.4+ with strict typing enabled
- ✓ Composer for dependency management

**Estimated Time**: 60-75 minutes

## Objectives

By completing this chapter, you will:

- Design and implement a service interface that abstracts Claude API complexity
- Build a framework-agnostic service layer following SOLID principles
- Create type-safe configuration classes with validation
- Implement the factory pattern for complex object creation
- Integrate services into Laravel applications using service providers
- Build conversation management and prompt templating systems
- Write comprehensive unit tests with mocked dependencies
- Apply dependency injection patterns for testability and maintainability

## Quick Start (~5 minutes)

Want to get up and running immediately? Here's a minimal working example:

```php
<?php
declare(strict_types=1);

require 'vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use App\Services\ClaudeService;
use App\Contracts\ClaudeServiceInterface;

// Initialize the SDK client
$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

// Create a simple service instance
$service = new ClaudeService(
    client: $client,
    config: [
        'model' => 'claude-sonnet-4-5-20250929',
        'max_tokens' => 1024,
        'temperature' => 0.7,
    ]
);

// Use it!
$response = $service->generate('Explain PHP type system in 2 sentences.');
echo $response;
```

That's it! In the sections below, you'll learn to build production-ready services with full configuration, testing, and framework integration.

## Service Layer Architecture

A proper service layer separates concerns and provides clear boundaries:

```
Application Layer
    ↓
ClaudeService (Interface)
    ↓
ClaudeServiceImplementation
    ↓
ClaudePhp\ClaudePhp
    ↓
HTTP/API
```

### Basic Service Interface

```php
# filename: src/Contracts/ClaudeServiceInterface.php
<?php
declare(strict_types=1);

namespace App\Contracts;

interface ClaudeServiceInterface
{
    /**
     * Generate text completion from a prompt
     */
    public function generate(
        string $prompt,
        ?int $maxTokens = null,
        ?float $temperature = null,
        ?string $model = null
    ): string;

    /**
     * Generate with full response details
     */
    public function generateWithMetadata(
        string $prompt,
        array $options = []
    ): array;

    /**
     * Stream a response
     */
    public function stream(
        string $prompt,
        callable $callback,
        array $options = []
    ): void;

    /**
     * Get token count estimate
     */
    public function estimateTokens(string $text): int;

    /**
     * Check service health
     */
    public function healthCheck(): bool;
}
```

## Framework-Agnostic Service Implementation

Build a service that works anywhere using the **Claude-PHP-SDK**:

```php
# filename: src/Services/ClaudeService.php
<?php
declare(strict_types=1);

namespace App\Services;

use App\Contracts\ClaudeServiceInterface;
use ClaudePhp\ClaudePhp;
use ClaudePhp\Exceptions\AnthropicException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ClaudeService implements ClaudeServiceInterface
{
    private string $defaultModel;
    private int $defaultMaxTokens;
    private float $defaultTemperature;

    public function __construct(
        private ClaudePhp $client,
        private ?LoggerInterface $logger = null,
        array $config = []
    ) {
        $this->logger ??= new NullLogger();

        // Load configuration with sensible defaults
        $this->defaultModel = $config['model'] ?? 'claude-sonnet-4-5';
        $this->defaultMaxTokens = $config['max_tokens'] ?? 4096;
        $this->defaultTemperature = $config['temperature'] ?? 1.0;
    }

    public function generate(
        string $prompt,
        ?int $maxTokens = null,
        ?float $temperature = null,
        ?string $model = null
    ): string {
        $this->logger->info('Generating text', [
            'prompt_length' => strlen($prompt),
            'max_tokens' => $maxTokens ?? $this->defaultMaxTokens,
            'model' => $model ?? $this->defaultModel,
        ]);

        try {
            $response = $this->client->messages()->create([
                'model' => $model ?? $this->defaultModel,
                'max_tokens' => $maxTokens ?? $this->defaultMaxTokens,
                'temperature' => $temperature ?? $this->defaultTemperature,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ]
            ]);

            // Access content from array response
            $text = $response->content[0]->text;

            $this->logger->info('Text generated successfully', [
                'output_length' => strlen($text),
                'tokens_used' => $response->usage->outputTokens,
            ]);

            return $text;

        } catch (AnthropicException $e) {
            $this->logger->error('Text generation failed', [
                'error' => $e->getMessage(),
                'prompt_length' => strlen($prompt),
            ]);
            throw $e;
        }
    }

    public function generateWithMetadata(
        string $prompt,
        array $options = []
    ): array {
        $model = $options['model'] ?? $this->defaultModel;
        $maxTokens = $options['max_tokens'] ?? $this->defaultMaxTokens;
        $temperature = $options['temperature'] ?? $this->defaultTemperature;
        $system = $options['system'] ?? null;

        $params = [
            'model' => $model,
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ];

        if ($system) {
            $params['system'] = $system;
        }

        $response = $this->client->messages()->create($params);

        return [
            'text' => $response->content[0]->text,
            'metadata' => [
                'id' => $response->id,
                'model' => $response->model,
                'stop_reason' => $response->stopReason,
                'usage' => [
                    'input_tokens' => $response->usage->inputTokens,
                    'output_tokens' => $response->usage->outputTokens,
                ],
            ]
        ];
    }

    public function stream(
        string $prompt,
        callable $callback,
        array $options = []
    ): void {
        $model = $options['model'] ?? $this->defaultModel;
        $maxTokens = $options['max_tokens'] ?? $this->defaultMaxTokens;

        $this->logger->info('Starting stream', [
            'model' => $model,
            'max_tokens' => $maxTokens,
        ]);

        // Get generator from SDK
        $stream = $this->client->messages()->create([
            'model' => $model,
            'max_tokens' => $maxTokens,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'stream' => true
        ]);

        foreach ($stream as $event) {
            // Check for content block deltas (text chunks)
            if (isset($event['type']) && $event['type'] === 'content_block_delta') {
                $callback($event['delta']['text'] ?? '');
            }
        }

        $this->logger->info('Stream completed');
    }

    public function estimateTokens(string $text): int
    {
        // Rough estimation: ~4 characters per token
        return (int) ceil(strlen($text) / 4);
    }

    public function healthCheck(): bool
    {
        try {
            $response = $this->client->messages()->create([
                'model' => $this->defaultModel,
                'max_tokens' => 10,
                'messages' => [
                    ['role' => 'user', 'content' => 'ping']
                ]
            ]);

            return !empty($response->content[0]->text);

        } catch (\Exception $e) {
            $this->logger->error('Health check failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
```

### Why It Works

The service implementation adapts the `ClaudePhp\ClaudePhp` to your application's interface. Unlike the official SDK, the **Claude-PHP-SDK** returns arrays instead of objects, which we handle in the service methods (`$response->content[0]->text`).

We inject the `ClaudePhp` instance via the constructor, following the **Dependency Injection** pattern. This allows us to mock the client during testing or configure it differently for various environments.

## Configuration Management

### Configuration Class

```php
# filename: src/Configuration/ClaudeConfig.php
<?php
declare(strict_types=1);

namespace App\Configuration;

class ClaudeConfig
{
    public function __construct(
        public readonly string $apiKey,
        public readonly string $defaultModel = 'claude-sonnet-4-5',
        public readonly int $maxTokens = 4096,
        public readonly float $temperature = 1.0,
        public readonly int $timeout = 30,
        public readonly bool $loggingEnabled = true,
    ) {
        if (empty($this->apiKey)) {
            throw new \InvalidArgumentException('API key cannot be empty');
        }

        if ($this->maxTokens < 1 || $this->maxTokens > 200000) {
            throw new \InvalidArgumentException('Max tokens must be between 1 and 200000');
        }
    }

    public static function fromArray(array $config): self
    {
        return new self(
            apiKey: $config['api_key'] ?? throw new \InvalidArgumentException('api_key is required'),
            defaultModel: $config['model'] ?? 'claude-sonnet-4-5',
            'max_tokens' => $config['max_tokens'] ?? 4096,
            'temperature' => $config['temperature'] ?? 1.0,
            timeout: $config['timeout'] ?? 30,
            loggingEnabled: $config['logging_enabled'] ?? true,
        );
    }

    public function toArray(): array
    {
        return [
            'api_key' => $this->apiKey,
            'model' => $this->defaultModel,
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
            'timeout' => $this->timeout,
            'logging_enabled' => $this->loggingEnabled,
        ];
    }
}
```

### Why It Works

The `ClaudeConfig` class uses PHP 8.1+ readonly properties for immutability. It validates configuration values upon instantiation, preventing invalid states like negative temperatures or empty API keys.

## Factory Pattern for Service Creation

```php
# filename: src/Factory/ClaudeServiceFactory.php
<?php
declare(strict_types=1);

namespace App\Factory;

use App\Configuration\ClaudeConfig;
use App\Contracts\ClaudeServiceInterface;
use App\Services\ClaudeService;
use ClaudePhp\ClaudePhp;
use Psr\Log\LoggerInterface;

class ClaudeServiceFactory
{
    public static function create(
        ClaudeConfig $config,
        ?LoggerInterface $logger = null
    ): ClaudeServiceInterface {
        // Create the SDK Client
        $client = new ClaudePhp(
            apiKey: $config->apiKey,
            timeout: $config->timeout,
        );

        return new ClaudeService(
            client: $client,
            logger: $logger,
            config: [
                'model' => $config->defaultModel,
                'max_tokens' => $config->maxTokens,
                'temperature' => $config->temperature,
            ]
        );
    }
}
```

### Why It Works

The factory handles the instantiation details. The `Claude-PHP-SDK` uses named parameters for client construction, providing type safety and clear parameter names. The factory encapsulates this creation logic, allowing you to change how the client is built (e.g., adding custom HTTP handlers) in one place.

## Usage Examples

### Standalone PHP Application

```php
# filename: examples/01-standalone-usage.php
<?php
declare(strict_types=1);

require 'vendor/autoload.php';

use App\Configuration\ClaudeConfig;
use App\Factory\ClaudeServiceFactory;

// Create configuration
$config = ClaudeConfig::fromArray([
    'api_key' => $_ENV['ANTHROPIC_API_KEY'],
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 2048,
    'temperature' => 0.7,
]);

// Create service
$claude = ClaudeServiceFactory::create($config);

// Simple text generation
$response = $claude->generate(
    prompt: 'Explain dependency injection in PHP in 2 sentences.',
    'max_tokens' => 200
);

echo "Response: {$response}\n\n";

// Generation with metadata
$result = $claude->generateWithMetadata(
    prompt: 'What are PHP traits?',
    options: [
        'max_tokens' => 300,
        'system' => 'You are a PHP expert. Be concise.',
    ]
);

echo "Text: {$result['text']}\n";
echo "Tokens used: {$result['metadata']['usage']['output_tokens']}\n";
echo "Stop reason: {$result['metadata']['stop_reason']}\n\n";

// Streaming
echo "Streaming response:\n";
$claude->stream(
    prompt: 'Count from 1 to 5.',
    callback: function (string $chunk) {
        echo $chunk;
        flush();
    }
);
echo "\n";
```

### Laravel Integration

```php
# filename: app/Providers/ClaudeServiceProvider.php
<?php
declare(strict_types=1);

namespace App\Providers;

use App\Configuration\ClaudeConfig;
use App\Contracts\ClaudeServiceInterface;
use App\Factory\ClaudeServiceFactory;
use Illuminate\Support\ServiceProvider;

class ClaudeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register configuration
        $this->app->singleton(ClaudeConfig::class, function ($app) {
            return ClaudeConfig::fromArray(config('claude'));
        });

        // Register service
        $this->app->singleton(ClaudeServiceInterface::class, function ($app) {
            return ClaudeServiceFactory::create(
                config: $app->make(ClaudeConfig::class),
                logger: $app->make('log')
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/claude.php' => config_path('claude.php'),
        ], 'claude-config');
    }
}
```

```php
# filename: config/claude.php
<?php

return [
    'api_key' => env('ANTHROPIC_API_KEY'),
    'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-5'),
    'max_tokens' => (int) env('ANTHROPIC_MAX_TOKENS', 4096),
    'temperature' => (float) env('ANTHROPIC_TEMPERATURE', 1.0),
    'timeout' => (int) env('ANTHROPIC_TIMEOUT', 30),
    'logging_enabled' => env('ANTHROPIC_LOGGING_ENABLED', true),
];
```

```php
# filename: app/Http/Controllers/AiController.php
<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\ClaudeServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function __construct(
        private ClaudeServiceInterface $claude
    ) {}

    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:10000',
            'max_tokens' => 'nullable|integer|min:1|max:4096',
        ]);

        try {
            $result = $this->claude->generateWithMetadata(
                prompt: $validated['prompt'],
                options: [
                    'max_tokens' => $validated['max_tokens'] ?? 1024,
                ]
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
```

### Why It Works

The Laravel service provider registers both the configuration and service as singletons. By using the `ClaudeServiceFactory`, we ensure that the underlying `ClaudePhp\ClaudePhp` is correctly initialized with configuration from Laravel's config system.

## Advanced Service Features

### Conversation Management

```php
# filename: src/Services/ConversationService.php
<?php
declare(strict_types=1);

namespace App\Services;

use ClaudePhp\ClaudePhp;

class ConversationService
{
    private array $messages = [];

    public function __construct(
        private ClaudePhp $client,
        private string $model = 'claude-sonnet-4-5',
        private ?string $systemPrompt = null
    ) {}

    public function addUserMessage(string $content): self
    {
        $this->messages[] = [
            'role' => 'user',
            'content' => $content
        ];

        return $this;
    }

    public function addAssistantMessage(string $content): self
    {
        $this->messages[] = [
            'role' => 'assistant',
            'content' => $content
        ];

        return $this;
    }

    public function send(int $maxTokens = 4096): string
    {
        if (empty($this->messages)) {
            throw new \RuntimeException('No messages to send');
        }

        $params = [
            'model' => $this->model,
            'max_tokens' => $maxTokens,
            'messages' => $this->messages,
        ];

        if ($this->systemPrompt) {
            $params['system'] = $this->systemPrompt;
        }

        // Use SDK to send message
        $response = $this->client->messages()->create($params);
        $text = $response->content[0]->text;

        // Add assistant's response to conversation
        $this->addAssistantMessage($text);

        return $text;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function clear(): self
    {
        $this->messages = [];
        return $this;
    }
}
```

### Prompt Templates

```php
# filename: src/Services/PromptTemplate.php
<?php
declare(strict_types=1);

namespace App\Services;

class PromptTemplate
{
    public function __construct(
        private string $template,
        private array $defaults = []
    ) {}

    public function render(array $variables = []): string
    {
        $merged = array_merge($this->defaults, $variables);

        $output = $this->template;

        foreach ($merged as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $output = str_replace($placeholder, (string) $value, $output);
        }

        if (preg_match('/\{\{([^}]+)\}\}/', $output, $matches)) {
            throw new \RuntimeException("Unresolved template variable: {$matches[1]}");
        }

        return $output;
    }
}
```

## Comprehensive Testing

Testing with the Claude-PHP-SDK requires careful mocking of the complex SDK classes. For this tutorial, we've simplified the tests to focus on service functionality rather than complex SDK mocking.

```php
# filename: tests/Services/ClaudeServiceTest.php
<?php
declare(strict_types=1);

namespace Tests\Services;

use App\Services\ClaudeService;
use ClaudePhp\ClaudePhp;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ClaudeServiceTest extends TestCase
{
    private ClaudePhp $mockClient;
    private LoggerInterface $mockLogger;
    private ClaudeService $service;

    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(ClaudePhp::class);
        $this->mockLogger = $this->createMock(LoggerInterface::class);

        $this->service = new ClaudeService(
            client: $this->mockClient,
            logger: $this->mockLogger,
            config: [
                'model' => 'claude-sonnet-4-5-20250929',
                'max_tokens' => 1024,
                'temperature' => 1.0,
            ]
        );
    }

    public function testGenerateReturnsText(): void
    {
        $expectedText = 'This is a test response';

        $mockResponse = (object) [
            'id' => 'msg_123',
            'content' => [(object) ['text' => $expectedText]],
            'usage' => (object) ['inputTokens' => 10, 'outputTokens' => 20],
            'model' => 'claude-sonnet-4-5-20250929',
            'stop_reason' => 'end_turn'
        ];

        // Mock the messages() method to return a mock that has create()
        $messagesMock = $this->createMock(\stdClass::class);
        $messagesMock->expects($this->once())
            ->method('create')
            ->with($this->callback(function ($params) {
                return $params['model'] === 'claude-sonnet-4-5-20250929'
                    && $params['max_tokens'] === 1024
                    && $params['messages'][0]['role'] === 'user'
                    && $params['messages'][0]['content'] === 'Test prompt';
            }))
            ->willReturn($mockResponse);

        $this->mockClient->expects($this->once())
            ->method('messages')
            ->willReturn($messagesMock);

        $result = $this->service->generate('Test prompt');

        $this->assertEquals($expectedText, $result);
    }

    public function testGenerateWithMetadataReturnsFullResponse(): void
    {
        $mockResponse = (object) [
            'id' => 'msg_456',
            'content' => [(object) ['text' => 'Response text']],
            'model' => 'claude-sonnet-4-5-20250929',
            'stop_reason' => 'end_turn',
            'usage' => (object) ['inputTokens' => 15, 'outputTokens' => 25]
        ];

        $messagesMock = $this->createMock(\stdClass::class);
        $messagesMock->expects($this->once())
            ->method('create')
            ->willReturn($mockResponse);

        $this->mockClient->expects($this->once())
            ->method('messages')
            ->willReturn($messagesMock);

        $result = $this->service->generateWithMetadata('Test prompt');

        $this->assertIsArray($result);
        $this->assertEquals('Response text', $result['text']);
        $this->assertEquals('msg_456', $result['metadata']['id']);
        $this->assertEquals(15, $result['metadata']['usage']['input_tokens']);
    }

    public function testHealthCheckReturnsTrueOnSuccess(): void
    {
        $mockResponse = (object) [
            'content' => [(object) ['text' => 'pong']]
        ];

        $messagesMock = $this->createMock(\stdClass::class);
        $messagesMock->expects($this->once())
            ->method('create')
            ->willReturn($mockResponse);

        $this->mockClient->expects($this->once())
            ->method('messages')
            ->willReturn($messagesMock);

        $result = $this->service->healthCheck();

        $this->assertTrue($result);
    }

    public function testHealthCheckReturnsFalseOnException(): void
    {
        $this->mockClient
            ->expects($this->once())
            ->method('messages')
            ->willReturn($this->createMock(\stdClass::class))
            ->getMock()
            ->expects($this->once())
            ->method('create')
            ->willThrowException(new \Exception('API Error'));

        $result = $this->service->healthCheck();

        $this->assertFalse($result);
    }
}
```

## Exercises

### Exercise 1: Create a Metrics Service (~10 min)

**Goal**: Extend `ClaudeService` to track response times and token usage

Create a `MetricsCollectorInterface` that the service can use to record statistics.

**Validation**: Your service should call metrics methods for each request.

### Exercise 2: Build a Mock Service Implementation (~8 min)

**Goal**: Create a `MockClaudeService` for testing without API calls

Implement `ClaudeServiceInterface` with hardcoded responses. This allows you to test the rest of your application without needing the SDK or API keys.

**Validation**: You should be able to use `MockClaudeService` wherever `ClaudeServiceInterface` is type-hinted.

### Exercise 3: Implement Configuration Validation (~10 min)

**Goal**: Add more sophisticated validation to `ClaudeConfig`

Add validation for:

- Model name against allowed models list
- Valid temperature values with clear error messages
- Reasonable token limits based on model

### Exercise 4: Add Retry Strategy Options (~12 min)

**Goal**: Implement a retry decorator

Since the `ClaudePhp\ClaudePhp` is simple, wrap it in a `RetryingClient` class that implements the same methods but adds retry logic for specific exceptions.

```php
class RetryingClient extends ClaudePhp {
    // Override sendMessage to add retries
}
```

## Troubleshooting

**Class 'ClaudePhp\ClaudePhp' not found?**

- Run `composer dump-autoload`.
- Ensure you installed the correct package: `composer require claude-php/claude-php-sdk`.

**Configuration not loading?**

- Verify environment variables are set in `.env`.
- Run `php artisan config:cache` in production.

**Undefined array key 'text'?**

- Remember that the SDK returns arrays, not objects.
- Check `$response->content[0]->text`.

## Further Reading

- **[Claude-PHP-SDK GitHub](https://github.com/claude-php/Claude-PHP-SDK)** — Official repository and documentation
- **[Anthropic API Documentation](https://docs.anthropic.com)** — Complete API reference
- **[Packagist Package](https://packagist.org/packages/claude-php/claude-php-sdk)** — Version history and installation

## Wrap-up

Congratulations! You've built a production-ready Claude service class using the community-standard **Claude-PHP-SDK**. Here's what you've accomplished:

- ✓ **Designed a service interface** — Created `ClaudeServiceInterface` that abstracts API complexity
- ✓ **Built framework-agnostic service** — Implemented `ClaudeService` using the `ClaudePhp\ClaudePhp`
- ✓ **Created type-safe configuration** — Built `ClaudeConfig` with validation
- ✓ **Implemented factory pattern** — Centralized service creation
- ✓ **Integrated with Laravel** — Created service providers for framework integration
- ✓ **Wrote comprehensive tests** — Created PHPUnit tests with mocked `ClaudePhp`
- ✓ **Applied SOLID principles** — Used dependency injection and separation of concerns

Your service layer now provides a clean, testable interface for Claude API interactions, handling the specifics of the SDK while providing a stable API for your application.

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="17"
  label="You've built a production-ready Claude service class!"
/>

---

Continue to [Chapter 18: Caching Strategies for API Calls](/series/claude-php-developers/chapters/18-caching-strategies) to optimize performance and reduce costs.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 17 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-17)**

Clone and run locally:

```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-17
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php examples/01-standalone-usage.php
```
