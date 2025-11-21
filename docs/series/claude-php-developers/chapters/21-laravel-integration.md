---
title: "21: Laravel Integration Patterns"
description: "Master Claude integration in Laravel with service providers, facades, contracts, and configuration management. Build production-ready packages and implement comprehensive testing strategies."
series: "claude-php-developers"
chapter: 21
order: 21
difficulty: "Intermediate"
prerequisites:
  - "Laravel 12+ installed"
  - "Understanding of service containers"
  - "Completion of Chapters 00-20"
---

![21: Laravel Integration Patterns](/images/claude-php/chapter-21-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 21</span>
</div>

# Chapter 21: Laravel Integration Patterns

## Overview

Integrating Claude into Laravel applications requires more than just making API calls. This chapter teaches you how to build production-ready Claude integrations using Laravel's powerful features: service providers, facades, contracts, and configuration management.

You'll learn to architect a clean, testable, and maintainable Claude integration that follows Laravel best practices. By the end, you'll have built a complete Laravel package for Claude that can be reused across projects, tested comprehensively, and configured through environment variables.

**Estimated Time**: 90-120 minutes

## What You'll Build

By the end of this chapter, you will have created:

- A complete Laravel service provider for Claude integration
- A contract (interface) for flexible, testable Claude services
- A production-ready ClaudeService implementation with caching and logging
- A facade for convenient static access to Claude functionality
- Comprehensive test suite with mocked Anthropic client
- Configuration management system using Laravel's config system
- Usage tracking system for monitoring costs and patterns
- A reusable Laravel package structure for Claude integration

## Prerequisites

Before starting, ensure you have:

- ✓ **Laravel 12+** installed and configured
- ✓ **Anthropic API key** set up
- ✓ **PHP 8.4+** with Composer
- ✓ **Understanding of Laravel architecture** (service container, facades, providers)
- ✓ **Completion of Chapters 00-20** (fundamental Claude concepts)

### Environment Setup

Create or update your `.env` file with these Claude-specific variables:

```bash
# .env
ANTHROPIC_API_KEY=sk-ant-xxxxxxxxxxxxxxxxxxxxx
CLAUDE_MODEL=claude-sonnet-4-5-20250929
CLAUDE_MAX_TOKENS=2048
CLAUDE_TEMPERATURE=1.0
CLAUDE_TIMEOUT=60
CLAUDE_CACHE_ENABLED=true
CLAUDE_CACHE_TTL=3600
CLAUDE_CACHE_STORE=redis
```

**Environment Variable Reference:**

- `ANTHROPIC_API_KEY` — Your API key from [console.anthropic.com](https://console.anthropic.com)
- `CLAUDE_MODEL` — Default model (latest: `claude-sonnet-4-5-20250929`)
- `CLAUDE_MAX_TOKENS` — Maximum tokens per response (1-4096)
- `CLAUDE_TEMPERATURE` — Randomness (0.0 deterministic → 1.0 creative)
- `CLAUDE_TIMEOUT` — Request timeout in seconds
- `CLAUDE_CACHE_ENABLED` — Enable response caching
- `CLAUDE_CACHE_TTL` — Cache duration in seconds
- `CLAUDE_CACHE_STORE` — Cache driver (redis, file, database)

## Objectives

By completing this chapter, you will:

- Understand Laravel's service container and dependency injection patterns
- Create custom service providers to bootstrap Claude integration
- Implement contracts and interfaces for flexible, testable code
- Build facades for convenient static access to services
- Configure Laravel applications using environment variables and config files
- Write comprehensive tests using mocking and dependency injection
- Package Claude integration as a reusable Laravel package
- Implement caching strategies to reduce API costs
- Track usage and monitor Claude API consumption

## Laravel Architecture Overview

Laravel's architecture is built on several key patterns that make Claude integration elegant and maintainable.

### The Service Container

Laravel's service container is a powerful dependency injection system:

```php
<?php
# The container resolves dependencies automatically

// Binding in a service provider
$this->app->singleton(ClaudeService::class, function ($app) {
    return new ClaudeService(
        apiKey: config('claude.api_key'),
        'model' => config('claude.default_model')
    );
});

// Automatic resolution anywhere
public function __construct(ClaudeService $claude)
{
    $this->claude = $claude;
}
```

### Service Providers

Service providers bootstrap your application's components:

```php
<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ClaudeService;
use ClaudePhp\ClaudePhp;

class ClaudeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register bindings in the container
        $this->app->singleton(ClaudePhp::class, function ($app) {
            return new ClaudePhp(
                apiKey: config('claude.api_key')
            );
        });

        $this->app->singleton(ClaudeService::class, function ($app) {
            return new ClaudeService($app->make(\ClaudePhp\ClaudePhp::class));
        });
    }

    public function boot(): void
    {
        // Bootstrap application services
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/claude.php' => config_path('claude.php'),
            ], 'claude-config');
        }
    }
}
```

## Building the Claude Service Provider

Let's create a comprehensive service provider for Claude integration.

### Step 1: Create the Configuration File (~5 min)

### Goal

Set up a centralized configuration file that manages all Claude-related settings using Laravel's configuration system and environment variables.

### Actions

1. **Create the configuration file** at `config/claude.php`:

```php
<?php
# filename: config/claude.php
declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Anthropic API Key
    |--------------------------------------------------------------------------
    |
    | Your Anthropic API key from console.anthropic.com
    |
    */
    'api_key' => env('ANTHROPIC_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Default Model
    |--------------------------------------------------------------------------
    |
    | The default Claude model to use for API calls.
    | Options: claude-opus-4-1-20250805, claude-sonnet-4-5-20250929, claude-haiku-4-5-20251001
    |
    */
    'default_model' => env('CLAUDE_MODEL', 'claude-sonnet-4-5-20250929'),

    /*
    |--------------------------------------------------------------------------
    | Default Max Tokens
    |--------------------------------------------------------------------------
    |
    | The default maximum number of tokens to generate in responses.
    |
    */
    'max_tokens' => env('CLAUDE_MAX_TOKENS', 2048),

    /*
    |--------------------------------------------------------------------------
    | Default Temperature
    |--------------------------------------------------------------------------
    |
    | Controls randomness. 0.0 = focused, 1.0 = creative
    |
    */
    'temperature' => env('CLAUDE_TEMPERATURE', 1.0),

    /*
    |--------------------------------------------------------------------------
    | Timeout Settings
    |--------------------------------------------------------------------------
    |
    | Request timeout in seconds
    |
    */
    'timeout' => env('CLAUDE_TIMEOUT', 60),

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Enable caching for repeated prompts
    |
    */
    'cache' => [
        'enabled' => env('CLAUDE_CACHE_ENABLED', true),
        'ttl' => env('CLAUDE_CACHE_TTL', 3600), // 1 hour
        'store' => env('CLAUDE_CACHE_STORE', 'redis'),
    ],

    /*
    |--------------------------------------------------------------------------
    | System Prompts
    |--------------------------------------------------------------------------
    |
    | Predefined system prompts for different use cases
    |
    */
    'system_prompts' => [
        'code_review' => 'You are a senior PHP developer specializing in Laravel. Review code for bugs, performance, security, and best practices.',
        'documentation' => 'You are a technical writer. Create clear, concise documentation with examples.',
        'support' => 'You are a helpful customer support agent. Be friendly, professional, and solution-oriented.',
    ],
];
```

### Expected Result

You'll have a configuration file that:

- Loads sensitive values (API key) from environment variables
- Provides sensible defaults for all settings
- Organizes cache, timeout, and system prompt settings
- Can be published to your application's config directory

### Why It Works

Laravel's configuration system (`config()`) merges environment variables with default values, allowing you to override settings per environment without modifying code. The `env()` helper reads from your `.env` file, keeping sensitive credentials out of version control. This pattern follows Laravel's 12-factor app principles.

### Step 2: Create the Service Provider (~10 min)

### Goal

Build a service provider that registers Claude services in Laravel's container, enabling dependency injection throughout your application.

### Actions

1. **Create the service provider** at `app/Providers/ClaudeServiceProvider.php`:

```php
<?php
# filename: app/Providers/ClaudeServiceProvider.php
declare(strict_types=1);

namespace App\Providers;

use ClaudePhp\ClaudePhp;
use App\Contracts\ClaudeInterface;
use App\Services\ClaudeService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class ClaudeServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__.'/../../config/claude.php',
            'claude'
        );

        // Register the Claude Client
        $this->app->singleton(\ClaudePhp\ClaudePhp::class, function (Application $app) {
            $apiKey = config('claude.api_key');

            if (empty($apiKey)) {
                throw new \RuntimeException(
                    'Anthropic API key not configured. Set ANTHROPIC_API_KEY in your .env file.'
                );
            }

            return new \ClaudePhp\ClaudePhp(
                apiKey: $apiKey
            );
        });

        // Register the Claude service
        $this->app->singleton(ClaudeInterface::class, ClaudeService::class);
        $this->app->singleton(ClaudeService::class, function (Application $app) {
            return new ClaudeService(
                client: $app->make(\ClaudePhp\ClaudePhp::class),
                config: config('claude')
            );
        });

        // Alias for convenience
        $this->app->alias(ClaudeService::class, 'claude');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish configuration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/claude.php' => config_path('claude.php'),
            ], 'claude-config');
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            \ClaudePhp\ClaudePhp::class,
            ClaudeInterface::class,
            ClaudeService::class,
            'claude',
        ];
    }
}
```

### Expected Result

After creating the service provider, you'll be able to:

- Resolve `ClaudeService` and `ClaudeInterface` from the container
- Access Claude functionality via dependency injection
- Use the `'claude'` alias for convenient access
- Publish configuration files using `php artisan vendor:publish`

### Why It Works

The service provider's `register()` method runs early in Laravel's bootstrap process, allowing you to bind services before they're needed. Using `singleton()` ensures only one instance exists per request, improving performance. The `provides()` method helps Laravel optimize service resolution, and the `boot()` method runs after all providers are registered, perfect for publishing assets.

### Step 3: Register the Provider (~2 min)

### Goal

Register your service provider so Laravel loads it during application bootstrap.

### Actions

1. **Add the provider** to `bootstrap/providers.php`:

```php
<?php
# filename: bootstrap/providers.php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\ClaudeServiceProvider::class,
];
```

### Expected Result

Your service provider will be loaded automatically when Laravel boots, and all Claude services will be available throughout your application.

### Why It Works

Laravel reads the `providers.php` file during bootstrap and instantiates each provider, calling their `register()` and `boot()` methods in order. This ensures your Claude services are ready before any routes, controllers, or other services try to use them.

## Creating the Contract (Interface) (~5 min)

### Goal

Define a contract (interface) that specifies Claude service methods, enabling flexible implementations and easy testing through dependency injection.

### Actions

1. **Create the interface** at `app/Contracts/ClaudeInterface.php`:

```php
<?php
# filename: app/Contracts/ClaudeInterface.php
declare(strict_types=1);

namespace App\Contracts;

interface ClaudeInterface
{
    /**
     * Generate a response from Claude
     */
    public function generate(
        string $prompt,
        ?string $systemPrompt = null,
        array $options = []
    ): string;

    /**
     * Generate with conversation history
     */
    public function chat(
        string $message,
        array $history = [],
        ?string $systemPrompt = null
    ): array;

    /**
     * Analyze code
     */
    public function analyzeCode(string $code, string $language = 'php'): string;

    /**
     * Extract structured data
     */
    public function extractData(string $text, array $schema): array;

    /**
     * Stream a response
     */
    public function stream(
        string $prompt,
        callable $callback,
        array $options = []
    ): void;

    /**
     * Get the configured model
     */
    public function getModel(): string;

    /**
     * Set the model for next request
     */
    public function withModel(string $model): self;
}
```

### Expected Result

You'll have a contract that:

- Defines all Claude service methods with proper type hints
- Enables dependency injection of the interface (not concrete class)
- Allows easy mocking in tests
- Provides clear documentation of available methods

### Why It Works

Contracts (interfaces) follow the Dependency Inversion Principle from SOLID—depend on abstractions, not concretions. By injecting `ClaudeInterface` instead of `ClaudeService`, you can swap implementations without changing consuming code. This is especially valuable for testing, where you can inject mock implementations.

## Implementing the Claude Service (~15 min)

### Goal

Create a production-ready service implementation that handles API calls, caching, logging, and error handling.

### Actions

1. **Create the service class** at `app/Services/ClaudeService.php`:

````php
<?php
# filename: app/Services/ClaudeService.php
declare(strict_types=1);

namespace App\Services;

use ClaudePhp\ClaudePhp;
use App\Contracts\ClaudeInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ClaudeService implements ClaudeInterface
{
    private string $currentModel;
    private array $defaultOptions;

    public function __construct(
        private readonly \ClaudePhp\ClaudePhp $client,
        private readonly array $config
    ) {
        $this->currentModel = $config['default_model'];
        $this->defaultOptions = [
            'temperature' => $config['temperature'],
            'maxTokens' => $config['max_tokens'],
        ];
    }

    /**
     * Generate a response from Claude
     */
    public function generate(
        string $prompt,
        ?string $systemPrompt = null,
        array $options = []
    ): string {
        // Check cache if enabled
        if ($this->isCacheEnabled()) {
            $cacheKey = $this->getCacheKey($prompt, $systemPrompt, $options);
            $cached = Cache::store($this->config['cache']['store'])
                ->get($cacheKey);

            if ($cached !== null) {
                Log::debug('Claude response served from cache', ['key' => $cacheKey]);
                return $cached;
            }
        }

        // Merge options with defaults
        // Mapping max_tokens to maxTokens if needed, handled in constructor/merge
        $requestOptions = array_merge($this->defaultOptions, $options, [
            'model' => $this->currentModel,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
        ]);

        if ($systemPrompt !== null) {
            $requestOptions['system'] = $systemPrompt;
        }

        // Make the API call
        try {
            $response = $this->client->messages()->create($requestOptions);
            $text = $response->content[0]->text ?? '';

            // Cache the response if enabled
            if ($this->isCacheEnabled()) {
                Cache::store($this->config['cache']['store'])
                    ->put($cacheKey, $text, $this->config['cache']['ttl']);
            }

            // Log usage for monitoring
            $inputTokens = $response->usage->inputTokens ?? 0;
            $outputTokens = $response->usage->outputTokens ?? 0;

            Log::info('Claude API call completed', [
                'model' => $this->currentModel,
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
            ]);

            return $text;
        } catch (\Exception $e) {
            Log::error('Claude API call failed', [
                'error' => $e->getMessage(),
                'model' => $this->currentModel,
            ]);
            throw $e;
        }
    }

    /**
     * Generate with conversation history
     */
    public function chat(
        string $message,
        array $history = [],
        ?string $systemPrompt = null
    ): array {
        // Build messages array
        $messages = $history;
        $messages[] = ['role' => 'user', 'content' => $message];

        $requestOptions = [
            'model' => $this->currentModel,
            'maxTokens' => $this->defaultOptions['maxTokens'],
            'messages' => $messages,
        ];

        if ($systemPrompt !== null) {
            $requestOptions['system'] = $systemPrompt;
        }

        $response = $this->client->messages()->create($requestOptions);
        $reply = $response->content[0]->text ?? '';

        // Return updated history
        return [
            'history' => array_merge($messages, [
                ['role' => 'assistant', 'content' => $reply]
            ]),
            'response' => $reply,
            'usage' => [
                'input_tokens' => $response->usage->inputTokens ?? 0,
                'output_tokens' => $response->usage->outputTokens ?? 0,
            ],
        ];
    }

    /**
     * Analyze code
     */
    public function analyzeCode(string $code, string $language = 'php'): string
    {
        $systemPrompt = $this->config['system_prompts']['code_review'] ?? null;

        $prompt = "Analyze this {$language} code for:\n" .
                  "- Bugs and errors\n" .
                  "- Security vulnerabilities\n" .
                  "- Performance issues\n" .
                  "- Best practice violations\n\n" .
                  "```{$language}\n{$code}\n```";

        return $this->generate($prompt, $systemPrompt);
    }

    /**
     * Extract structured data
     */
    public function extractData(string $text, array $schema): array
    {
        $schemaDescription = json_encode($schema, JSON_PRETTY_PRINT);

        $prompt = "Extract data from this text according to the schema below. " .
                  "Return only valid JSON, no explanation.\n\n" .
                  "Schema:\n{$schemaDescription}\n\n" .
                  "Text:\n{$text}";

        $response = $this->generate($prompt, null, ['temperature' => 0.0]);

        // Parse JSON from response
        if (preg_match('/```json\s*(\{.*?\})\s*```/s', $response, $matches)) {
            $jsonText = $matches[1];
        } elseif (preg_match('/(\{.*?\})/s', $response, $matches)) {
            $jsonText = $matches[1];
        } else {
            $jsonText = $response;
        }

        return json_decode($jsonText, true) ?? [];
    }

    /**
     * Stream a response
     */
    public function stream(
        string $prompt,
        callable $callback,
        array $options = []
    ): void {
        $requestOptions = array_merge($this->defaultOptions, $options, [
            'model' => $this->currentModel,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'stream' => true,
        ]);

        $stream = $this->client->messages()->create($requestOptions);

        foreach ($stream as $event) {
            // Handle streaming response based on Claude SDK structure
            if (isset($event->delta->text)) {
                $callback($event->delta->text);
            }
        }
    }

    /**
     * Get the configured model
     */
    public function getModel(): string
    {
        return $this->currentModel;
    }

    /**
     * Set the model for next request
     */
    public function withModel(string $model): self
    {
        $clone = clone $this;
        $clone->currentModel = $model;
        return $clone;
    }

    /**
     * Check if caching is enabled
     */
    private function isCacheEnabled(): bool
    {
        return $this->config['cache']['enabled'] ?? false;
    }

    /**
     * Generate cache key
     */
    private function getCacheKey(string $prompt, ?string $systemPrompt, array $options): string
    {
        return 'claude:' . md5(json_encode([
            'model' => $this->currentModel,
            'prompt' => $prompt,
            'system' => $systemPrompt,
            'options' => $options,
        ]));
    }
}
````

### Expected Result

Your service will:

- Cache responses when enabled, reducing API costs
- Log all API calls for monitoring and debugging
- Handle errors gracefully with proper exception handling
- Support streaming, code analysis, and data extraction
- Allow model switching via fluent interface

### Why It Works

The service wraps the Anthropic SDK client, adding Laravel-specific features like caching and logging. By checking cache before making API calls, you avoid redundant requests. The `withModel()` method uses cloning to create a new instance with different settings, following the immutable builder pattern. Error handling ensures failures are logged but exceptions propagate for proper error handling upstream.

## Creating a Facade (~5 min)

### Goal

Provide a convenient static interface to Claude services, following Laravel's facade pattern for cleaner, more readable code.

### Actions

1. **Create the facade** at `app/Facades/Claude.php`:

Facades provide a static interface to services:

```php
<?php
# filename: app/Facades/Claude.php
declare(strict_types=1);

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string generate(string $prompt, ?string $systemPrompt = null, array $options = [])
 * @method static array chat(string $message, array $history = [], ?string $systemPrompt = null)
 * @method static string analyzeCode(string $code, string $language = 'php')
 * @method static array extractData(string $text, array $schema)
 * @method static void stream(string $prompt, callable $callback, array $options = [])
 * @method static string getModel()
 * @method static \App\Contracts\ClaudeInterface withModel(string $model)
 *
 * @see \App\Services\ClaudeService
 */
class Claude extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'claude';
    }
}
```

### Expected Result

You'll be able to use Claude functionality statically:

```php
Claude::generate('Hello');
Claude::analyzeCode($code);
```

### Why It Works

Facades provide static access to services resolved from the container. When you call `Claude::generate()`, Laravel resolves the `'claude'` binding (which points to `ClaudeService`) and calls the method. The `@method` annotations provide IDE autocomplete support, making facades feel like static classes while maintaining testability.

### Using the Facade

Here's how to use the facade in controllers:

```php
<?php
# filename: app/Http/Controllers/AiController.php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Facades\Claude;
use Illuminate\Http\Request;

class AiController extends Controller
{
    public function analyze(Request $request)
    {
        $code = $request->input('code');

        $analysis = Claude::analyzeCode($code);

        return response()->json([
            'analysis' => $analysis,
        ]);
    }

    public function chat(Request $request)
    {
        $message = $request->input('message');
        $history = $request->input('history', []);

        $result = Claude::chat($message, $history);

        return response()->json($result);
    }

    public function extract(Request $request)
    {
        $text = $request->input('text');

        $schema = [
            'name' => 'string',
            'email' => 'string',
            'phone' => 'string',
        ];

        $data = Claude::extractData($text, $schema);

        return response()->json($data);
    }
}
```

## Testing the Integration (~20 min)

### Goal

Write comprehensive tests that mock the Anthropic client, ensuring your service works correctly without making real API calls.

### Actions

1. **Configure test environment** in `tests/TestCase.php`:

```php
<?php
# filename: tests/TestCase.php
declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Set test configuration
        config([
            'claude.api_key' => 'sk-ant-test-key',
            'claude.cache.enabled' => false, // Disable cache for tests
        ]);
    }
}
```

### Expected Result

Your test base class will:

- Set test API keys automatically
- Disable caching for predictable test results
- Provide a clean environment for each test

### Why It Works

The `setUp()` method runs before each test, ensuring consistent configuration. Disabling cache prevents tests from interfering with each other, and using test API keys ensures you never accidentally make real API calls during testing.

### Mock the Anthropic ClaudePhp

2. **Create unit tests** for the service in `tests/Unit/Services/ClaudeServiceTest.php`:

```php
<?php
# filename: tests/Unit/Services/ClaudeServiceTest.php
declare(strict_types=1);

namespace Tests\Unit\Services;

use ClaudePhp\ClaudePhp;
use App\Services\ClaudeService;
use Mockery;
use Tests\TestCase;

class ClaudeServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_generate_returns_response_text(): void
    {
        // Mock the Claude Client
        $mockClient = Mockery::mock(\ClaudePhp\ClaudePhp::class);
        $mockMessages = Mockery::mock();

        $mockClient->shouldReceive('messages')
            ->andReturn($mockMessages);

        // Mock the response
        $mockResponse = Mockery::mock();
        // SDK uses array access for content or property access?
        // Following pattern: $response->content[0]->text
        $mockResponse->content = [['text' => 'Test response from Claude']];
        $mockResponse->usage = (object) [
            'inputTokens' => 10,
            'outputTokens' => 5
        ];

        $mockMessages->shouldReceive('create')
            ->once()
            ->andReturn($mockResponse);

        // Create service with mock
        $service = new ClaudeService($mockClient, config('claude'));

        // Test
        $result = $service->generate('Test prompt');

        $this->assertEquals('Test response from Claude', $result);
    }

    public function test_chat_maintains_history(): void
    {
        $mockClient = Mockery::mock(\ClaudePhp\ClaudePhp::class);
        $mockMessages = Mockery::mock();

        $mockClient->shouldReceive('messages')
            ->andReturn($mockMessages);

        $mockResponse = Mockery::mock();
        $mockResponse->content = [['text' => 'Assistant response']];
        $mockResponse->usage = (object) [
            'inputTokens' => 15,
            'outputTokens' => 8
        ];

        $mockMessages->shouldReceive('create')
            ->once()
            ->andReturn($mockResponse);

        $service = new ClaudeService($mockClient, config('claude'));

        $initialHistory = [
            ['role' => 'user', 'content' => 'First message'],
            ['role' => 'assistant', 'content' => 'First response'],
        ];

        $result = $service->chat('Second message', $initialHistory);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('history', $result);
        $this->assertArrayHasKey('response', $result);
        $this->assertCount(4, $result['history']); // 2 + 2 new
        $this->assertEquals('Assistant response', $result['response']);
    }

    public function test_with_model_creates_clone_with_different_model(): void
    {
        $mockClient = Mockery::mock(\ClaudePhp\ClaudePhp::class);
        $service = new ClaudeService($mockClient, config('claude'));

        $newService = $service->withModel('claude-opus-4-1');

        $this->assertNotSame($service, $newService);
        $this->assertEquals('claude-opus-4-1', $newService->getModel());
        $this->assertEquals(config('claude.default_model'), $service->getModel());
    }
}
```

### Expected Result

Your tests will:

- Verify service methods return expected results
- Test conversation history management
- Validate model switching functionality
- Run without making real API calls

### Why It Works

Mockery creates mock objects that simulate the Anthropic client's behavior. By setting expectations (`shouldReceive()`), you control what methods are called and what they return. The `tearDown()` method cleans up mocks to prevent test interference. This approach tests your service logic without external dependencies.

### Feature Test with Facade

3. **Test facade integration** in `tests/Feature/ClaudeFacadeTest.php`:

```php
<?php
# filename: tests/Feature/ClaudeFacadeTest.php
declare(strict_types=1);

namespace Tests\Feature;

use App\Facades\Claude;
use ClaudePhp\ClaudePhp;
use Mockery;
use Tests\TestCase;

class ClaudeFacadeTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_facade_calls_service(): void
    {
        // Mock the client
        $mockClient = Mockery::mock(\ClaudePhp\ClaudePhp::class);
        $mockMessages = Mockery::mock();
        $mockClient->shouldReceive('messages')->andReturn($mockMessages);

        $mockResponse = Mockery::mock();
        $mockResponse->content = [['text' => 'Facade test response']];
        $mockResponse->usage = (object) ['inputTokens' => 5, 'outputTokens' => 3];

        $mockMessages->shouldReceive('create')->once()->andReturn($mockResponse);

        // Bind mock to container
        $this->app->instance(\ClaudePhp\ClaudePhp::class, $mockClient);

        // Use facade
        $result = Claude::generate('Test via facade');

        $this->assertEquals('Facade test response', $result);
    }
}
```

### Expected Result

Your feature tests will verify that:

- Facades correctly resolve services from the container
- Static calls work as expected
- Integration between facade and service is seamless

### Why It Works

Feature tests run in a full Laravel application context, including service providers. By binding a mock to the container (`$this->app->instance()`), the facade resolves your mock instead of the real service. This tests the entire integration chain from facade to service to client.

## Advanced Patterns (~15 min)

### Goal

Implement production-ready patterns for model selection, usage tracking, and cost monitoring.

### Model Selection Helper

```php
<?php
# filename: app/Services/Helpers/ModelSelector.php
declare(strict_types=1);

namespace App\Services\Helpers;

class ModelSelector
{
    public static function forTask(string $task): string
    {
        return match($task) {
            'classification',
            'extraction',
            'simple' => 'claude-haiku-4-5-20251001',

            'analysis',
            'generation',
            'moderate' => 'claude-sonnet-4-5-20250929',

            'complex',
            'creative',
            'architecture' => 'claude-opus-4-1-20250805',

            default => config('claude.default_model'),
        };
    }

    public static function forCost(string $priority): string
    {
        return match($priority) {
            'low' => 'claude-haiku-4-5-20251001',
            'medium' => 'claude-sonnet-4-5-20250929',
            'high' => 'claude-opus-4-1-20250805',
            default => config('claude.default_model'),
        };
    }
}
```

### Usage Tracking

```php
<?php
# filename: app/Models/ClaudeUsage.php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClaudeUsage extends Model
{
    protected $fillable = [
        'user_id',
        'model',
        'prompt',
        'response',
        'input_tokens',
        'output_tokens',
        'cost',
        'duration_ms',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'cost' => 'decimal:6',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### Usage Tracker Service

```php
<?php
# filename: app/Services/UsageTracker.php
declare(strict_types=1);

namespace App\Services;

use App\Models\ClaudeUsage;

class UsageTracker
{
    private const PRICING = [
        'claude-opus-4-1-20250805' => ['input' => 15.00, 'output' => 75.00],
        'claude-sonnet-4-5-20250929' => ['input' => 3.00, 'output' => 15.00],
        'claude-haiku-4-5-20251001' => ['input' => 0.25, 'output' => 1.25],
    ];

    public function track(
        ?int $userId,
        string $model,
        string $prompt,
        string $response,
        int $inputTokens,
        int $outputTokens,
        float $durationMs,
        array $metadata = []
    ): ClaudeUsage {
        $cost = $this->calculateCost($model, $inputTokens, $outputTokens);

        return ClaudeUsage::create([
            'user_id' => $userId,
            'model' => $model,
            'prompt' => $prompt,
            'response' => $response,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'cost' => $cost,
            'duration_ms' => $durationMs,
            'metadata' => $metadata,
        ]);
    }

    private function calculateCost(string $model, int $inputTokens, int $outputTokens): float
    {
        $pricing = self::PRICING[$model] ?? ['input' => 0, 'output' => 0];

        $inputCost = ($inputTokens / 1_000_000) * $pricing['input'];
        $outputCost = ($outputTokens / 1_000_000) * $pricing['output'];

        return $inputCost + $outputCost;
    }

    public function getUserUsage(int $userId, int $days = 30): array
    {
        $usage = ClaudeUsage::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays($days))
            ->get();

        return [
            'total_requests' => $usage->count(),
            'total_cost' => $usage->sum('cost'),
            'total_input_tokens' => $usage->sum('input_tokens'),
            'total_output_tokens' => $usage->sum('output_tokens'),
            'average_duration_ms' => $usage->avg('duration_ms'),
            'by_model' => $usage->groupBy('model')->map->count(),
        ];
    }
}
```

## Creating Exception Classes

Custom exceptions enable better error handling and debugging:

```php
<?php
# filename: app/Exceptions/Claude/ClaudeException.php
declare(strict_types=1);

namespace App\Exceptions\Claude;

use Exception;
use Throwable;

class ClaudeException extends Exception
{
    /**
     * Create a new Claude exception.
     */
    public function __construct(
        string $message = 'Claude API error',
        int $code = 0,
        ?Throwable $previous = null,
        public readonly ?string $apiErrorCode = null,
        public readonly ?array $apiResponse = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the API error code if available
     */
    public function getApiErrorCode(): ?string
    {
        return $this->apiErrorCode;
    }

    /**
     * Get the full API response for debugging
     */
    public function getApiResponse(): ?array
    {
        return $this->apiResponse;
    }

    /**
     * Check if this is a rate limit error
     */
    public function isRateLimit(): bool
    {
        return str_contains($this->message, 'rate_limit') ||
               str_contains($this->message, '429');
    }

    /**
     * Check if this is an authentication error
     */
    public function isAuthenticationError(): bool
    {
        return str_contains($this->message, 'authentication') ||
               str_contains($this->message, '401');
    }
}
```

Create a rate limit exception:

```php
<?php
# filename: app/Exceptions/Claude/RateLimitException.php
declare(strict_types=1);

namespace App\Exceptions\Claude;

class RateLimitException extends ClaudeException
{
    public function __construct(
        string $message = 'Claude API rate limit exceeded',
        public readonly ?int $retryAfterSeconds = null,
    ) {
        parent::__construct($message, 429, apiErrorCode: 'rate_limit_error');
    }

    /**
     * Get seconds to wait before retrying
     */
    public function getRetryAfter(): ?int
    {
        return $this->retryAfterSeconds;
    }
}
```

Update the `ClaudeService` to throw these exceptions:

```php
<?php
# Inside app/Services/ClaudeService.php - modify the generate() method

        try {
            $response = $this->client->messages()->create($requestOptions);
            $text = $response->content[0]->text;
            // ... rest of code
        } catch (\Exception $e) {
            Log::error('Claude API call failed', [
                'error' => $e->getMessage(),
                'model' => $this->currentModel,
            ]);

            // Convert Anthropic exceptions to custom exceptions
            if (str_contains($e->getMessage(), 'rate_limit')) {
                throw new \App\Exceptions\Claude\RateLimitException(
                    $e->getMessage(),
                    retryAfterSeconds: 60
                );
            }

            if (str_contains($e->getMessage(), '401') || str_contains($e->getMessage(), 'Unauthorized')) {
                throw new \App\Exceptions\Claude\ClaudeException(
                    'Authentication failed: Invalid API key',
                    401,
                    $e,
                    apiErrorCode: 'authentication_error'
                );
            }

            throw new \App\Exceptions\Claude\ClaudeException(
                $e->getMessage(),
                $e->getCode(),
                $e,
                apiErrorCode: 'api_error',
                apiResponse: []
            );
        }
```

## Database Migration for Usage Tracking

Create a migration for the `claude_usage` table:

```bash
php artisan make:migration create_claude_usage_table
```

```php
<?php
# filename: database/migrations/[timestamp]_create_claude_usage_table.php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('claude_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('model', 100);
            $table->longText('prompt');
            $table->longText('response');
            $table->integer('input_tokens')->default(0);
            $table->integer('output_tokens')->default(0);
            $table->decimal('cost', 10, 6)->default(0);
            $table->integer('duration_ms')->default(0);
            $table->json('metadata')->nullable();
            $table->string('status', 50)->default('completed'); // completed, failed, rate_limited
            $table->text('error_message')->nullable();
            $table->timestamps();

            // Indexes for common queries
            $table->index(['user_id', 'created_at']);
            $table->index(['model', 'created_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claude_usage');
    }
};
```

Run the migration:

```bash
php artisan migrate
```

## Console Command for Testing Integration

Create a console command to test the Claude service:

```bash
php artisan make:command ClaudeTestCommand
```

```php
<?php
# filename: app/Console/Commands/ClaudeTestCommand.php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\ClaudeInterface;
use App\Exceptions\Claude\ClaudeException;
use Illuminate\Console\Command;

class ClaudeTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'claude:test
        {--model= : Override the default model}
        {--timeout=30 : Request timeout in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Claude API integration';

    /**
     * Execute the console command.
     */
    public function handle(ClaudeInterface $claude): int
    {
        $this->info('🤖 Testing Claude API Integration\n');

        // Check environment
        $this->info('📋 Environment Check:');
        $this->checkEnvironment();

        // Test basic generation
        $this->info('\n✅ Testing Basic Generation:');
        if (!$this->testBasicGeneration($claude)) {
            return Command::FAILURE;
        }

        // Test with custom model
        if ($this->option('model')) {
            $this->info('\n✅ Testing with Custom Model:');
            if (!$this->testCustomModel($claude)) {
                return Command::FAILURE;
            }
        }

        // Test conversation
        $this->info('\n✅ Testing Conversation:');
        if (!$this->testConversation($claude)) {
            return Command::FAILURE;
        }

        $this->info('\n✨ All tests passed! Claude integration is working correctly.\n');
        return Command::SUCCESS;
    }

    /**
     * Check environment variables
     */
    private function checkEnvironment(): void
    {
        $required = [
            'ANTHROPIC_API_KEY',
            'CLAUDE_MODEL',
        ];

        foreach ($required as $var) {
            if (env($var)) {
                $value = $var === 'ANTHROPIC_API_KEY' ? '***' . substr(env($var), -4) : env($var);
                $this->line("  ✓ {$var} = {$value}");
            } else {
                $this->error("  ✗ {$var} is not set");
            }
        }
    }

    /**
     * Test basic generation
     */
    private function testBasicGeneration(ClaudeInterface $claude): bool
    {
        try {
            $this->line('  Testing: Generate a simple response...');

            $response = $claude->generate(
                prompt: 'Say "Claude is working!" in exactly those words.'
            );

            if (str_contains($response, 'Claude is working')) {
                $this->line("  ✓ Response: {$response}");
                return true;
            }

            $this->error('  ✗ Unexpected response format');
            return false;
        } catch (ClaudeException $e) {
            $this->error("  ✗ Error: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Test with custom model
     */
    private function testCustomModel(ClaudeInterface $claude): bool
    {
        try {
            $model = $this->option('model');
            $this->line("  Testing: Generate with model {$model}...");

            $service = $claude->withModel($model);
            $response = $service->generate(
                prompt: 'Respond with the model name you are.'
            );

            $this->line("  ✓ Response with {$model}: {$response}");
            return true;
        } catch (ClaudeException $e) {
            if ($e->isRateLimit()) {
                $this->warn("  ⚠ Rate limited: {$e->getMessage()}");
                return true; // Not a failure, just API limit
            }

            $this->error("  ✗ Error: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Test conversation
     */
    private function testConversation(ClaudeInterface $claude): bool
    {
        try {
            $this->line('  Testing: Multi-turn conversation...');

            // First turn
            $result1 = $claude->chat(
                message: 'What is 2 + 2?'
            );

            $this->line("  ✓ User: What is 2 + 2?");
            $this->line("  ✓ Claude: {$result1['response']}");

            // Second turn with history
            $result2 = $claude->chat(
                message: 'What about 2 + 3?',
                history: $result1['history']
            );

            $this->line("  ✓ User: What about 2 + 3?");
            $this->line("  ✓ Claude: {$result2['response']}");
            $this->line("  ✓ Conversation history maintained across turns");

            return true;
        } catch (ClaudeException $e) {
            $this->error("  ✗ Error: {$e->getMessage()}");
            return false;
        }
    }
}
```

Run the test command:

```bash
php artisan claude:test

# Test with custom model
php artisan claude:test --model=claude-haiku-4-5

# Test with custom timeout
php artisan claude:test --timeout=60
```

**Expected Output:**

```
🤖 Testing Claude API Integration

📋 Environment Check:
  ✓ ANTHROPIC_API_KEY = ***xxxx
  ✓ CLAUDE_MODEL = claude-sonnet-4-5-20250929

✅ Testing Basic Generation:
  Testing: Generate a simple response...
  ✓ Response: Claude is working!

✅ Testing Conversation:
  Testing: Multi-turn conversation...
  ✓ User: What is 2 + 2?
  ✓ Claude: 2 + 2 equals 4.
  ✓ User: What about 2 + 3?
  ✓ Claude: 2 + 3 equals 5.
  ✓ Conversation history maintained across turns

✨ All tests passed! Claude integration is working correctly.
```

## Creating a Laravel Package

### Package Structure

```
packages/
└── claude-laravel/
    ├── config/
    │   └── claude.php
    ├── src/
    │   ├── ClaudeServiceProvider.php
    │   ├── Contracts/
    │   │   └── ClaudeInterface.php
    │   ├── Services/
    │   │   └── ClaudeService.php
    │   └── Facades/
    │       └── Claude.php
    ├── tests/
    │   └── ClaudeServiceTest.php
    └── composer.json
```

### Package composer.json

```json
{
  "name": "yourname/claude-laravel",
  "description": "Laravel integration for Anthropic Claude AI",
  "type": "library",
  "require": {
    "php": "^8.2",
    "illuminate/support": "^12.0",
    "claude-php/claude-php-sdk": "^0.2"
  },
  "require-dev": {
    "orchestra/testbench": "^10.0",
    "phpunit/phpunit": "^10.0"
  },
  "autoload": {
    "psr-4": {
      "YourName\\ClaudeLaravel\\": "src/"
    }
  },
  "autoload-dev": {
    "psr-4": {
      "YourName\\ClaudeLaravel\\Tests\\": "tests/"
    }
  },
  "extra": {
    "laravel": {
      "providers": ["YourName\\ClaudeLaravel\\ClaudeServiceProvider"],
      "aliases": {
        "Claude": "YourName\\ClaudeLaravel\\Facades\\Claude"
      }
    }
  }
}
```

## Exercises

### Exercise 1: Build a Rate Limiter

**Goal**: Create a rate limiting system to control Claude API usage per user.

**Requirements**:

- Implement `attempt()` method that checks if a user can make a request
- Implement `remaining()` method that returns requests left for the hour
- Use Laravel Cache with keys like `claude:ratelimit:{userId}:{hour}`
- Set cache expiry to 1 hour (3600 seconds)
- Return `true` if under limit, `false` if exceeded

**Validation**: Test with multiple users and verify limits are enforced:

```php
$limiter = new ClaudeRateLimiter();

// First 100 requests should succeed
for ($i = 0; $i < 100; $i++) {
    $this->assertTrue($limiter->attempt(1));
}

// 101st should fail
$this->assertFalse($limiter->attempt(1));

// Check remaining
$this->assertEquals(0, $limiter->remaining(1));
```

Create a rate limiting system for Claude API calls:

```php
<?php
# filename: app/Services/ClaudeRateLimiter.php
declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ClaudeRateLimiter
{
    public function attempt(int $userId, int $maxPerHour = 100): bool
    {
        // TODO: Implement rate limiting using Laravel Cache
        // Return true if user can make request, false if exceeded
    }

    public function remaining(int $userId): int
    {
        // TODO: Return remaining requests for user
    }
}
```

### Exercise 2: Create a Prompt Template System

**Goal**: Build a reusable prompt template system that loads templates from storage and replaces variables.

**Requirements**:

- Store templates in `storage/app/prompts/` directory
- Support variable replacement using `{{variable}}` syntax
- Load template files by name (e.g., `code_review.txt`)
- Replace all variables with provided values
- Return rendered prompt string

**Validation**: Create a template file and test variable replacement:

```php
// storage/app/prompts/code_review.txt
// Review this {{language}} code: {{code}}

$prompt = PromptTemplate::render('code_review', [
    'language' => 'PHP',
    'code' => '<?php echo "Hello"; ?>'
]);

// Should return: "Review this PHP code: <?php echo "Hello"; ?>"
```

Build a template system for reusable prompts:

```php
<?php
# filename: app/Services/PromptTemplate.php
declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class PromptTemplate
{
    public static function render(string $template, array $variables): string
    {
        // TODO: Load template from storage/app/prompts/
        // TODO: Replace {{variable}} placeholders
        // TODO: Return rendered prompt
    }
}

// Usage:
// $prompt = PromptTemplate::render('code_review', ['code' => $code]);
```

### Exercise 3: Build a Response Cache Manager

**Goal**: Create an intelligent caching system that stores Claude responses with configurable TTL and supports pattern-based invalidation.

**Requirements**:

- Generate cache keys using prompt and model hash
- Use Laravel's `Cache::remember()` for automatic retrieval/storage
- Support custom TTL (default from config)
- Implement `invalidate()` that removes keys matching a pattern
- Return count of invalidated keys

**Validation**: Test caching and invalidation:

```php
$manager = new ResponseCacheManager();

// First call executes callback
$result1 = $manager->remember('test prompt', fn() => 'response');

// Second call returns cached value
$result2 = $manager->remember('test prompt', fn() => 'different');

$this->assertEquals('response', $result2);

// Invalidate all Claude caches
$count = $manager->invalidate('claude:*');
$this->assertGreaterThan(0, $count);
```

Create a smart caching system with TTL and invalidation:

```php
<?php
# filename: app/Services/ResponseCacheManager.php
declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class ResponseCacheManager
{
    public function remember(string $prompt, callable $callback, ?int $ttl = null): string
    {
        // TODO: Generate cache key from prompt and model
        // TODO: Use Cache::remember() with callback
        // TODO: Return cached or fresh response
    }

    public function invalidate(string $pattern): int
    {
        // TODO: Find cache keys matching pattern
        // TODO: Delete matching keys
        // TODO: Return count of deleted keys
    }
}
```

<details>
<summary>Solution Hints</summary>

**Exercise 1**: Use `Cache::increment()` with a key like `claude:ratelimit:{$userId}:{$hour}`. Set expiry to 1 hour using `Cache::put()` or `Cache::remember()`. Check count before allowing request. For `remaining()`, calculate `maxPerHour - currentCount`.

**Exercise 2**: Use `Storage::get("prompts/{$template}.txt")` to load templates. Use `preg_replace('/\{\{(\w+)\}\}/', $variables[$1], $template)` for variable replacement. Handle missing variables gracefully.

**Exercise 3**: Generate cache key using `md5(json_encode(['prompt' => $prompt, 'model' => $model]))`. Use `Cache::remember($key, $ttl, $callback)`. For invalidation, use Redis `KEYS` command or iterate through known patterns if using file cache.

</details>

## Integration Testing in Your Application

Test the integration with a controller or job:

```php
<?php
# filename: app/Http/Controllers/ClaudeTestController.php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\ClaudeInterface;
use App\Models\ClaudeUsage;
use App\Services\UsageTracker;
use Illuminate\Http\JsonResponse;

class ClaudeTestController extends Controller
{
    public function test(
        ClaudeInterface $claude,
        UsageTracker $tracker
    ): JsonResponse {
        $startTime = microtime(true);

        try {
            $response = $claude->generate(
                prompt: 'Explain Laravel service providers in one sentence.'
            );

            $duration = (microtime(true) - $startTime) * 1000;

            // Track usage (if user is authenticated)
            if (auth()->check()) {
                $tracker->track(
                    userId: auth()->id(),
                    'model' => $claude->getModel(),
                    prompt: 'Explain Laravel service providers...',
                    response: $response,
                    inputTokens: 15,
                    outputTokens: 45,
                    durationMs: (int)$duration
                );
            }

            return response()->json([
                'success' => true,
                'response' => $response,
                'duration_ms' => round($duration, 2),
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

Add the route:

```php
<?php
# In routes/web.php

Route::get('/test/claude', [\App\Http\Controllers\ClaudeTestController::class, 'test'])
    ->middleware('auth');
```

## Troubleshooting

**Service provider not loading?**

- Ensure provider is registered in `bootstrap/providers.php`
- Run `php artisan config:clear` to clear cached config
- Check for syntax errors in provider
- Run `php artisan provider:list` to see registered providers

**Facade not working?**

- Verify facade accessor returns correct binding name
- Check provider registers the service with that name
- Run `php artisan optimize:clear` to clear all caches
- Verify facade is imported: `use App\Facades\Claude;`

**Tests failing with API errors?**

- Ensure you're mocking the Anthropic client, not making real calls
- Use `Mockery::close()` in `tearDown()` to clean up mocks
- Check test configuration in `TestCase.php`
- Use `$this->app->instance()` to bind mocks to container

**Cache not working?**

- Verify Redis/cache driver is configured correctly
- Check `CLAUDE_CACHE_ENABLED` is true in `.env`
- Ensure cache store specified in config exists
- Run `php artisan cache:clear` to clear old entries

**API key not recognized?**

- Verify `ANTHROPIC_API_KEY` is set in `.env`
- Ensure key starts with `sk-ant-`
- Check for trailing whitespace in `.env`
- Run `php artisan config:cache` if using config caching
- Verify key has proper API permissions in Anthropic console

**Rate limiting errors?**

- Catch `RateLimitException` specifically: `catch (RateLimitException $e)`
- Implement exponential backoff: `sleep($e->getRetryAfter() ?? 60)`
- Check usage in Anthropic console dashboard
- Consider implementing queue-based processing (Chapter 19)

**Exception handling issues?**

- Import custom exceptions: `use App\Exceptions\Claude\ClaudeException;`
- Check exception is caught before generic `\Exception` handler
- Use `->isRateLimit()` and `->isAuthenticationError()` helpers
- Log exception details: `Log::error($e->getMessage(), $e->getTrace())`

## Further Reading

- **[Claude-PHP-SDK Documentation](https://github.com/claude-php/Claude-PHP-SDK)** — The official repository for the Claude-PHP SDK
- **[Anthropic API Documentation](https://docs.anthropic.com)** — Complete API reference and guides
- **[Laravel Service Providers Documentation](https://laravel.com/docs/providers)** — Official guide to creating and registering service providers
- **[Claude-PHP-SDK on Packagist](https://packagist.org/packages/claude-php/claude-php-sdk)** — Composer package details

## Wrap-up

Congratulations! You've mastered Laravel integration patterns for Claude. Here's what you've accomplished:

- ✓ **Built a service provider** — Properly registered Claude services in Laravel's container
- ✓ **Created contracts** — Implemented interfaces for flexible, testable code
- ✓ **Implemented ClaudeService** — Production-ready service with caching, logging, and error handling
- ✓ **Built a facade** — Convenient static access to Claude functionality
- ✓ **Configured Laravel** — Environment-based configuration management
- ✓ **Wrote comprehensive tests** — Mocked Anthropic client for reliable testing
- ✓ **Designed a package structure** — Reusable Laravel package for Claude integration
- ✓ **Implemented usage tracking** — Monitor costs and API consumption patterns
- ✓ **Applied Laravel best practices** — Followed framework conventions and patterns

Your Claude integration now follows Laravel's architecture patterns, making it maintainable, testable, and production-ready. The service provider pattern ensures clean dependency injection, while contracts enable easy mocking and testing.

In the next chapter, you'll build a complete chatbot application using these integration patterns, bringing together all the concepts you've learned throughout this series.

## Further Reading

- [Laravel Service Providers Documentation](https://laravel.com/docs/providers) — Official guide to creating and registering service providers
- [Laravel Service Container](https://laravel.com/docs/container) — Understanding dependency injection and service resolution
- [Laravel Facades](https://laravel.com/docs/facades) — How facades work and when to use them
- [Laravel Package Development](https://laravel.com/docs/packages) — Creating reusable Laravel packages
- [PSR-11: Container Interface](https://www.php-fig.org/psr/psr-11/) — Standard container interface for dependency injection
- [Laravel Testing Documentation](https://laravel.com/docs/testing) — Comprehensive testing guide with mocking examples

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="21"
  label="You've mastered Laravel integration patterns for Claude!"
/>

---

Continue to [Chapter 22: Building a Chatbot with Laravel](/series/claude-php-developers/chapters/22-chatbot-laravel) to build a complete chatbot application.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 21 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-21)**

Clone and run locally:

```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-21
composer install
cp .env.example .env
# Add your ANTHROPIC_API_KEY to .env
php artisan migrate
php artisan serve
```
