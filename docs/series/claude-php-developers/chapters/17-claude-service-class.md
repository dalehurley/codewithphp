---
title: "17: Building a Claude Service Class"
description: "Design production-ready service layers with dependency injection, configuration management, testing, and framework-agnostic patterns for Claude API integration."
series: "claude-php-developers"
chapter: 17
order: 17
difficulty: "Expert"
prerequisites:
  - "Chapter 16: The Official PHP SDK"
  - "SOLID principles understanding"
  - "Dependency injection concepts"
  - "PSR standards familiarity"
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

## Prerequisites

Before diving in, ensure you have:

- ✓ **Chapter 16** completed (SDK knowledge)
- ✓ **SOLID principles** understanding
- ✓ **Dependency injection** experience
- ✓ **Unit testing** familiarity (PHPUnit)

**Estimated Time**: 60-75 minutes

## Service Layer Architecture

A proper service layer separates concerns and provides clear boundaries:

```
Application Layer
    ↓
ClaudeService (Interface)
    ↓
ClaudeServiceImplementation
    ↓
Anthropic SDK Client
    ↓
HTTP/API
```

### Basic Service Interface

```php
<?php
# filename: src/Contracts/ClaudeServiceInterface.php
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

Build a service that works anywhere:

```php
<?php
# filename: src/Services/ClaudeService.php
declare(strict_types=1);

namespace App\Services;

use App\Contracts\ClaudeServiceInterface;
use Anthropic\Contracts\ClientContract;
use Anthropic\Responses\Messages\CreateResponse;
use Anthropic\Exceptions\ErrorException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ClaudeService implements ClaudeServiceInterface
{
    private string $defaultModel;
    private int $defaultMaxTokens;
    private float $defaultTemperature;

    public function __construct(
        private ClientContract $client,
        private ?LoggerInterface $logger = null,
        array $config = []
    ) {
        $this->logger ??= new NullLogger();

        // Load configuration with sensible defaults
        $this->defaultModel = $config['model'] ?? 'claude-sonnet-4-20250514';
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
            $response = $this->createMessage([
                'model' => $model ?? $this->defaultModel,
                'max_tokens' => $maxTokens ?? $this->defaultMaxTokens,
                'temperature' => $temperature ?? $this->defaultTemperature,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ]
            ]);

            $text = $response->content[0]->text;

            $this->logger->info('Text generated successfully', [
                'output_length' => strlen($text),
                'tokens_used' => $response->usage->outputTokens,
            ]);

            return $text;

        } catch (ErrorException $e) {
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

        $response = $this->createMessage($params);

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

        $stream = $this->client->messages()->createStreamed([
            'model' => $model,
            'max_tokens' => $maxTokens,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        foreach ($stream as $event) {
            if ($event->type === 'content_block_delta') {
                $callback($event->delta->text ?? '');
            }
        }

        $this->logger->info('Stream completed');
    }

    public function estimateTokens(string $text): int
    {
        // Rough estimation: ~4 characters per token
        // For production, use the tokenization API or library
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

            return $response->content[0]->text !== null;

        } catch (\Exception $e) {
            $this->logger->error('Health check failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Internal method for creating messages with retry logic
     */
    private function createMessage(array $params): CreateResponse
    {
        $maxRetries = 3;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                return $this->client->messages()->create($params);

            } catch (ErrorException $e) {
                $attempt++;

                if ($attempt >= $maxRetries || $e->getResponse()?->getStatusCode() < 500) {
                    throw $e;
                }

                $waitTime = pow(2, $attempt);
                $this->logger->warning("Request failed, retrying in {$waitTime}s", [
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);

                sleep($waitTime);
            }
        }

        throw new \RuntimeException('Max retries exceeded');
    }
}
```

## Configuration Management

### Configuration Class

```php
<?php
# filename: src/Configuration/ClaudeConfig.php
declare(strict_types=1);

namespace App\Configuration;

class ClaudeConfig
{
    public function __construct(
        public readonly string $apiKey,
        public readonly string $defaultModel = 'claude-sonnet-4-20250514',
        public readonly int $maxTokens = 4096,
        public readonly float $temperature = 1.0,
        public readonly int $timeout = 120,
        public readonly int $retryAttempts = 3,
        public readonly bool $loggingEnabled = true,
        public readonly ?string $logChannel = null,
    ) {
        if (empty($this->apiKey)) {
            throw new \InvalidArgumentException('API key cannot be empty');
        }

        if ($this->maxTokens < 1 || $this->maxTokens > 200000) {
            throw new \InvalidArgumentException('Max tokens must be between 1 and 200000');
        }

        if ($this->temperature < 0 || $this->temperature > 1) {
            throw new \InvalidArgumentException('Temperature must be between 0 and 1');
        }
    }

    public static function fromArray(array $config): self
    {
        return new self(
            apiKey: $config['api_key'] ?? throw new \InvalidArgumentException('api_key is required'),
            defaultModel: $config['model'] ?? 'claude-sonnet-4-20250514',
            maxTokens: $config['max_tokens'] ?? 4096,
            temperature: $config['temperature'] ?? 1.0,
            timeout: $config['timeout'] ?? 120,
            retryAttempts: $config['retry_attempts'] ?? 3,
            loggingEnabled: $config['logging_enabled'] ?? true,
            logChannel: $config['log_channel'] ?? null,
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
            'retry_attempts' => $this->retryAttempts,
            'logging_enabled' => $this->loggingEnabled,
            'log_channel' => $this->logChannel,
        ];
    }
}
```

## Factory Pattern for Service Creation

```php
<?php
# filename: src/Factory/ClaudeServiceFactory.php
declare(strict_types=1);

namespace App\Factory;

use App\Configuration\ClaudeConfig;
use App\Contracts\ClaudeServiceInterface;
use App\Services\ClaudeService;
use Anthropic\Anthropic;
use Anthropic\Contracts\ClientContract;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ClaudeServiceFactory
{
    public static function create(
        ClaudeConfig $config,
        ?LoggerInterface $logger = null
    ): ClaudeServiceInterface {
        $client = self::createClient($config, $logger);

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

    private static function createClient(
        ClaudeConfig $config,
        ?LoggerInterface $logger
    ): ClientContract {
        // Build HTTP client with middleware
        $handlerStack = HandlerStack::create();

        // Add retry middleware
        $handlerStack->push(
            Middleware::retry(
                function ($retries, $request, $response, $exception) use ($config) {
                    return $retries < $config->retryAttempts
                        && ($exception || ($response && $response->getStatusCode() >= 500));
                },
                function ($retries) {
                    return 1000 * pow(2, $retries);
                }
            )
        );

        // Add logging middleware if enabled
        if ($config->loggingEnabled && $logger) {
            $handlerStack->push(
                Middleware::log($logger, new \GuzzleHttp\MessageFormatter())
            );
        }

        $guzzleClient = new GuzzleClient([
            'handler' => $handlerStack,
            'timeout' => $config->timeout,
        ]);

        return Anthropic::factory()
            ->withApiKey($config->apiKey)
            ->withHttpClient($guzzleClient)
            ->make();
    }
}
```

## Usage Examples

### Standalone PHP Application

```php
<?php
# filename: examples/01-standalone-usage.php
declare(strict_types=1);

require 'vendor/autoload.php';

use App\Configuration\ClaudeConfig;
use App\Factory\ClaudeServiceFactory;

// Create configuration
$config = ClaudeConfig::fromArray([
    'api_key' => getenv('ANTHROPIC_API_KEY'),
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 2048,
    'temperature' => 0.7,
]);

// Create service
$claude = ClaudeServiceFactory::create($config);

// Simple text generation
$response = $claude->generate(
    prompt: 'Explain dependency injection in PHP in 2 sentences.',
    maxTokens: 200
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
<?php
# filename: app/Providers/ClaudeServiceProvider.php
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
<?php
# filename: config/claude.php

return [
    'api_key' => env('ANTHROPIC_API_KEY'),
    'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-20250514'),
    'max_tokens' => (int) env('ANTHROPIC_MAX_TOKENS', 4096),
    'temperature' => (float) env('ANTHROPIC_TEMPERATURE', 1.0),
    'timeout' => (int) env('ANTHROPIC_TIMEOUT', 120),
    'retry_attempts' => (int) env('ANTHROPIC_RETRY_ATTEMPTS', 3),
    'logging_enabled' => env('ANTHROPIC_LOGGING_ENABLED', true),
    'log_channel' => env('ANTHROPIC_LOG_CHANNEL', 'stack'),
];
```

```php
<?php
# filename: app/Http/Controllers/AiController.php
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

## Advanced Service Features

### Conversation Management

```php
<?php
# filename: src/Services/ConversationService.php
declare(strict_types=1);

namespace App\Services;

use Anthropic\Contracts\ClientContract;
use Psr\Log\LoggerInterface;

class ConversationService
{
    private array $messages = [];

    public function __construct(
        private ClientContract $client,
        private ?LoggerInterface $logger = null,
        private string $model = 'claude-sonnet-4-20250514',
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

    public function getMessageCount(): int
    {
        return count($this->messages);
    }
}
```

### Prompt Templates

```php
<?php
# filename: src/Services/PromptTemplate.php
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

        // Check for unresolved placeholders
        if (preg_match('/\{\{([^}]+)\}\}/', $output, $matches)) {
            throw new \RuntimeException("Unresolved template variable: {$matches[1]}");
        }

        return $output;
    }

    public static function fromFile(string $path, array $defaults = []): self
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("Template file not found: {$path}");
        }

        $template = file_get_contents($path);
        return new self($template, $defaults);
    }
}

// Usage example
class ProductDescriptionGenerator
{
    private PromptTemplate $template;

    public function __construct(
        private ClaudeServiceInterface $claude
    ) {
        $this->template = new PromptTemplate(
            template: <<<'TEMPLATE'
            Generate a compelling product description for:

            Product Name: {{product_name}}
            Category: {{category}}
            Price: ${{price}}

            Key Features:
            {{features}}

            Target Audience: {{audience}}
            Tone: {{tone}}

            Write a {{length}} description that highlights benefits and creates desire.
            TEMPLATE,
            defaults: [
                'tone' => 'professional',
                'length' => 'medium-length',
                'audience' => 'general consumers'
            ]
        );
    }

    public function generate(array $productData): string
    {
        $prompt = $this->template->render([
            'product_name' => $productData['name'],
            'category' => $productData['category'],
            'price' => $productData['price'],
            'features' => implode("\n", array_map(fn($f) => "- $f", $productData['features'])),
            'audience' => $productData['audience'] ?? null,
        ]);

        return $this->claude->generate($prompt);
    }
}
```

## Comprehensive Testing

```php
<?php
# filename: tests/Services/ClaudeServiceTest.php
declare(strict_types=1);

namespace Tests\Services;

use App\Contracts\ClaudeServiceInterface;
use App\Services\ClaudeService;
use Anthropic\Contracts\ClientContract;
use Anthropic\Resources\Messages;
use Anthropic\Responses\Messages\CreateResponse;
use Anthropic\Responses\Messages\Content\TextContent;
use Anthropic\Responses\Messages\Usage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ClaudeServiceTest extends TestCase
{
    private ClientContract $mockClient;
    private Messages $mockMessages;
    private LoggerInterface $mockLogger;
    private ClaudeService $service;

    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(ClientContract::class);
        $this->mockMessages = $this->createMock(Messages::class);
        $this->mockLogger = $this->createMock(LoggerInterface::class);

        $this->mockClient
            ->method('messages')
            ->willReturn($this->mockMessages);

        $this->service = new ClaudeService(
            client: $this->mockClient,
            logger: $this->mockLogger,
            config: [
                'model' => 'claude-sonnet-4-20250514',
                'max_tokens' => 1024,
                'temperature' => 1.0,
            ]
        );
    }

    public function testGenerateReturnsText(): void
    {
        $expectedText = 'This is a test response';

        $mockResponse = new CreateResponse(
            id: 'msg_123',
            type: 'message',
            role: 'assistant',
            content: [new TextContent(type: 'text', text: $expectedText)],
            model: 'claude-sonnet-4-20250514',
            stopReason: 'end_turn',
            stopSequence: null,
            usage: new Usage(inputTokens: 10, outputTokens: 20)
        );

        $this->mockMessages
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function ($params) {
                return $params['model'] === 'claude-sonnet-4-20250514'
                    && $params['max_tokens'] === 1024
                    && $params['messages'][0]['role'] === 'user'
                    && $params['messages'][0]['content'] === 'Test prompt';
            }))
            ->willReturn($mockResponse);

        $result = $this->service->generate('Test prompt');

        $this->assertEquals($expectedText, $result);
    }

    public function testGenerateWithMetadataReturnsFullResponse(): void
    {
        $mockResponse = new CreateResponse(
            id: 'msg_456',
            type: 'message',
            role: 'assistant',
            content: [new TextContent(type: 'text', text: 'Response text')],
            model: 'claude-sonnet-4-20250514',
            stopReason: 'end_turn',
            stopSequence: null,
            usage: new Usage(inputTokens: 15, outputTokens: 25)
        );

        $this->mockMessages
            ->expects($this->once())
            ->method('create')
            ->willReturn($mockResponse);

        $result = $this->service->generateWithMetadata('Test prompt');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('text', $result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertEquals('Response text', $result['text']);
        $this->assertEquals('msg_456', $result['metadata']['id']);
        $this->assertEquals(15, $result['metadata']['usage']['input_tokens']);
        $this->assertEquals(25, $result['metadata']['usage']['output_tokens']);
    }

    public function testEstimateTokensReturnsApproximation(): void
    {
        $text = str_repeat('word ', 100); // ~500 characters
        $estimate = $this->service->estimateTokens($text);

        // Should be roughly 125 tokens (500 chars / 4)
        $this->assertGreaterThan(100, $estimate);
        $this->assertLessThan(150, $estimate);
    }

    public function testHealthCheckReturnsTrueOnSuccess(): void
    {
        $mockResponse = new CreateResponse(
            id: 'msg_health',
            type: 'message',
            role: 'assistant',
            content: [new TextContent(type: 'text', text: 'pong')],
            model: 'claude-sonnet-4-20250514',
            stopReason: 'end_turn',
            stopSequence: null,
            usage: new Usage(inputTokens: 5, outputTokens: 5)
        );

        $this->mockMessages
            ->expects($this->once())
            ->method('create')
            ->willReturn($mockResponse);

        $result = $this->service->healthCheck();

        $this->assertTrue($result);
    }

    public function testHealthCheckReturnsFalseOnException(): void
    {
        $this->mockMessages
            ->expects($this->once())
            ->method('create')
            ->willThrowException(new \Exception('API Error'));

        $result = $this->service->healthCheck();

        $this->assertFalse($result);
    }
}
```

## Troubleshooting

**Service not found in Laravel?**
- Ensure the service provider is registered in `config/app.php`
- Run `php artisan config:clear` to clear cached config
- Check the binding in the service provider uses the correct interface

**Configuration not loading?**
- Verify environment variables are set in `.env`
- Run `php artisan config:cache` in production
- Check the config file is published to `config/claude.php`

**Dependency injection not working?**
- Ensure your framework's container is configured correctly
- Check that interfaces are bound to implementations
- Verify constructor parameters use type hints

**Tests failing with "Class not found"?**
- Run `composer dump-autoload` to regenerate autoloader
- Check PSR-4 namespaces in `composer.json`
- Verify test file namespaces match directory structure

## Key Takeaways

- ✓ Service layer separates business logic from API implementation details
- ✓ Interfaces enable testing and allow swapping implementations
- ✓ Configuration classes provide type safety and validation
- ✓ Factory pattern centralizes complex object creation
- ✓ Framework-agnostic design allows code reuse across projects
- ✓ Proper logging and error handling are essential for production
- ✓ Comprehensive tests ensure reliability and enable refactoring

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
