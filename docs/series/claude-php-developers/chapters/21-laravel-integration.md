---
title: "21: Laravel Integration Patterns"
description: "Master Claude integration in Laravel with service providers, facades, contracts, and configuration management. Build production-ready packages and implement comprehensive testing strategies."
series: "claude-php-developers"
chapter: 21
order: 21
difficulty: "Intermediate"
prerequisites:
  - "Laravel 11+ installed"
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

**What You'll Learn:**
- Creating custom service providers for Claude integration
- Building facades for convenient API access
- Implementing contracts and interfaces for flexibility
- Managing configuration with environment variables
- Writing comprehensive tests with mocking
- Packaging Claude integration for reuse
- Performance optimization with Laravel caching
- Queue integration for async processing

**Estimated Time**: 90-120 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Laravel 11+** installed and configured
- ✓ **Anthropic API key** set up
- ✓ **PHP 8.2+** with Composer
- ✓ **Understanding of Laravel architecture** (service container, facades, providers)
- ✓ **Completion of Chapters 00-20** (fundamental Claude concepts)

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
        model: config('claude.default_model')
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
use Anthropic\Anthropic;

class ClaudeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register bindings in the container
        $this->app->singleton(Anthropic::class, function ($app) {
            return Anthropic::factory()
                ->withApiKey(config('claude.api_key'))
                ->make();
        });

        $this->app->singleton(ClaudeService::class, function ($app) {
            return new ClaudeService($app->make(Anthropic::class));
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

### Step 1: Create the Configuration File

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
    | Options: claude-opus-4-20250514, claude-sonnet-4-20250514, claude-haiku-4-20250514
    |
    */
    'default_model' => env('CLAUDE_MODEL', 'claude-sonnet-4-20250514'),

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

### Step 2: Create the Service Provider

```php
<?php
# filename: app/Providers/ClaudeServiceProvider.php
declare(strict_types=1);

namespace App\Providers;

use Anthropic\Anthropic;
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

        // Register the Anthropic client
        $this->app->singleton(Anthropic::class, function (Application $app) {
            $apiKey = config('claude.api_key');

            if (empty($apiKey)) {
                throw new \RuntimeException(
                    'Anthropic API key not configured. Set ANTHROPIC_API_KEY in your .env file.'
                );
            }

            return Anthropic::factory()
                ->withApiKey($apiKey)
                ->withTimeout(config('claude.timeout', 60))
                ->make();
        });

        // Register the Claude service
        $this->app->singleton(ClaudeInterface::class, ClaudeService::class);
        $this->app->singleton(ClaudeService::class, function (Application $app) {
            return new ClaudeService(
                client: $app->make(Anthropic::class),
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
            Anthropic::class,
            ClaudeInterface::class,
            ClaudeService::class,
            'claude',
        ];
    }
}
```

### Step 3: Register the Provider

```php
<?php
# filename: bootstrap/providers.php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\ClaudeServiceProvider::class,
];
```

## Creating the Contract (Interface)

Contracts provide flexibility and testability:

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

## Implementing the Claude Service

```php
<?php
# filename: app/Services/ClaudeService.php
declare(strict_types=1);

namespace App\Services;

use Anthropic\Anthropic;
use App\Contracts\ClaudeInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ClaudeService implements ClaudeInterface
{
    private string $currentModel;
    private array $defaultOptions;

    public function __construct(
        private readonly Anthropic $client,
        private readonly array $config
    ) {
        $this->currentModel = $config['default_model'];
        $this->defaultOptions = [
            'temperature' => $config['temperature'],
            'max_tokens' => $config['max_tokens'],
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
            $text = $response->content[0]->text;

            // Cache the response if enabled
            if ($this->isCacheEnabled()) {
                Cache::store($this->config['cache']['store'])
                    ->put($cacheKey, $text, $this->config['cache']['ttl']);
            }

            // Log usage for monitoring
            Log::info('Claude API call completed', [
                'model' => $this->currentModel,
                'input_tokens' => $response->usage->inputTokens,
                'output_tokens' => $response->usage->outputTokens,
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
            'max_tokens' => $this->defaultOptions['max_tokens'],
            'messages' => $messages,
        ];

        if ($systemPrompt !== null) {
            $requestOptions['system'] = $systemPrompt;
        }

        $response = $this->client->messages()->create($requestOptions);
        $reply = $response->content[0]->text;

        // Return updated history
        return [
            'history' => array_merge($messages, [
                ['role' => 'assistant', 'content' => $reply]
            ]),
            'response' => $reply,
            'usage' => [
                'input_tokens' => $response->usage->inputTokens,
                'output_tokens' => $response->usage->outputTokens,
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
```

## Creating a Facade

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

### Using the Facade

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

## Testing the Integration

### Setup Test Configuration

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

### Mock the Anthropic Client

```php
<?php
# filename: tests/Unit/Services/ClaudeServiceTest.php
declare(strict_types=1);

namespace Tests\Unit\Services;

use Anthropic\Anthropic;
use Anthropic\Responses\Messages\CreateResponse;
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
        // Mock the Anthropic client
        $mockClient = Mockery::mock(Anthropic::class);
        $mockMessages = Mockery::mock();

        $mockClient->shouldReceive('messages')
            ->andReturn($mockMessages);

        // Mock the response
        $mockResponse = Mockery::mock(CreateResponse::class);
        $mockContent = (object) ['text' => 'Test response from Claude'];
        $mockResponse->content = [$mockContent];
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
        $mockClient = Mockery::mock(Anthropic::class);
        $mockMessages = Mockery::mock();

        $mockClient->shouldReceive('messages')
            ->andReturn($mockMessages);

        $mockResponse = Mockery::mock(CreateResponse::class);
        $mockContent = (object) ['text' => 'Assistant response'];
        $mockResponse->content = [$mockContent];
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
        $mockClient = Mockery::mock(Anthropic::class);
        $service = new ClaudeService($mockClient, config('claude'));

        $newService = $service->withModel('claude-opus-4-20250514');

        $this->assertNotSame($service, $newService);
        $this->assertEquals('claude-opus-4-20250514', $newService->getModel());
        $this->assertEquals(config('claude.default_model'), $service->getModel());
    }
}
```

### Feature Test with Facade

```php
<?php
# filename: tests/Feature/ClaudeFacadeTest.php
declare(strict_types=1);

namespace Tests\Feature;

use App\Facades\Claude;
use Anthropic\Anthropic;
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
        $mockClient = Mockery::mock(Anthropic::class);
        $mockMessages = Mockery::mock();
        $mockClient->shouldReceive('messages')->andReturn($mockMessages);

        $mockResponse = Mockery::mock();
        $mockContent = (object) ['text' => 'Facade test response'];
        $mockResponse->content = [$mockContent];
        $mockResponse->usage = (object) ['inputTokens' => 5, 'outputTokens' => 3];

        $mockMessages->shouldReceive('create')->once()->andReturn($mockResponse);

        // Bind mock to container
        $this->app->instance(Anthropic::class, $mockClient);

        // Use facade
        $result = Claude::generate('Test via facade');

        $this->assertEquals('Facade test response', $result);
    }
}
```

## Advanced Patterns

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
            'simple' => 'claude-haiku-4-20250514',

            'analysis',
            'generation',
            'moderate' => 'claude-sonnet-4-20250514',

            'complex',
            'creative',
            'architecture' => 'claude-opus-4-20250514',

            default => config('claude.default_model'),
        };
    }

    public static function forCost(string $priority): string
    {
        return match($priority) {
            'low' => 'claude-haiku-4-20250514',
            'medium' => 'claude-sonnet-4-20250514',
            'high' => 'claude-opus-4-20250514',
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
        'claude-opus-4-20250514' => ['input' => 15.00, 'output' => 75.00],
        'claude-sonnet-4-20250514' => ['input' => 3.00, 'output' => 15.00],
        'claude-haiku-4-20250514' => ['input' => 0.25, 'output' => 1.25],
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
        "illuminate/support": "^11.0",
        "anthropic-ai/sdk": "^0.6"
    },
    "require-dev": {
        "orchestra/testbench": "^9.0",
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
            "providers": [
                "YourName\\ClaudeLaravel\\ClaudeServiceProvider"
            ],
            "aliases": {
                "Claude": "YourName\\ClaudeLaravel\\Facades\\Claude"
            }
        }
    }
}
```

## Exercises

### Exercise 1: Build a Rate Limiter

Create a rate limiting system for Claude API calls:

```php
<?php
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

Build a template system for reusable prompts:

```php
<?php
class PromptTemplate
{
    public static function render(string $template, array $variables): string
    {
        // TODO: Load template from storage
        // TODO: Replace variables
        // TODO: Return rendered prompt
    }
}

// Usage:
// $prompt = PromptTemplate::render('code_review', ['code' => $code]);
```

### Exercise 3: Build a Response Cache Manager

Create a smart caching system with TTL and invalidation:

```php
<?php
class ResponseCacheManager
{
    public function remember(string $prompt, callable $callback, ?int $ttl = null): string
    {
        // TODO: Check cache
        // TODO: Call callback if not cached
        // TODO: Store with semantic key
    }

    public function invalidate(string $pattern): int
    {
        // TODO: Invalidate matching cache keys
    }
}
```

<details>
<summary>Solution Hints</summary>

**Exercise 1**: Use `Cache::increment()` with a key like `claude:ratelimit:{$userId}:{$hour}`. Set expiry to 1 hour. Check count before allowing request.

**Exercise 2**: Store templates in `storage/app/prompts/`. Use `str_replace()` or `preg_replace()` for variable substitution. Support nested templates.

**Exercise 3**: Generate cache key using `md5(json_encode(['prompt' => $prompt, 'model' => $model]))`. Use `Cache::remember()` with custom TTL. For invalidation, iterate cache keys matching pattern.

</details>

## Troubleshooting

**Service provider not loading?**
- Ensure provider is registered in `bootstrap/providers.php`
- Run `php artisan config:clear` to clear cached config
- Check for syntax errors in provider

**Facade not working?**
- Verify facade accessor returns correct binding name
- Check provider registers the service with that name
- Run `php artisan optimize:clear` to clear all caches

**Tests failing with API errors?**
- Ensure you're mocking the Anthropic client, not making real calls
- Use `Mockery::close()` in `tearDown()` to clean up mocks
- Check test configuration in `TestCase.php`

**Cache not working?**
- Verify Redis/cache driver is configured correctly
- Check `CLAUDE_CACHE_ENABLED` is true in `.env`
- Ensure cache store specified in config exists

## Key Takeaways

- ✓ **Service Providers** bootstrap Claude integration in Laravel
- ✓ **Contracts** provide flexibility and enable easy testing
- ✓ **Facades** offer convenient static access to services
- ✓ **Configuration** should use environment variables
- ✓ **Caching** dramatically reduces costs for repeated queries
- ✓ **Testing** requires mocking the Anthropic client
- ✓ **Packages** enable reuse across multiple projects
- ✓ **Usage Tracking** provides visibility into costs and patterns

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
