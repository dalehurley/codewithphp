# Chapter 21: Laravel Integration Patterns

Complete Laravel package for integrating Claude AI with service providers, facades, configuration, and testing.

## Features

- **Service Provider** with automatic registration
- **Facade** for convenient access
- **Configuration** file for easy customization
- **Caching** support for API responses
- **Conversation builder** for multi-turn interactions
- **Prompt templates** management
- **Full test coverage** with Orchestra Testbench

## Installation

### As a Package

Add to your Laravel project's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../chapter-21"
        }
    ],
    "require": {
        "claude-php/chapter-21-laravel-integration": "*"
    }
}
```

Then run:

```bash
composer install
```

### Publish Configuration

```bash
php artisan vendor:publish --tag=claude-config
```

This creates `config/claude.php`.

### Environment Setup

Add to `.env`:

```bash
CLAUDE_API_KEY=sk-ant-your-key-here
CLAUDE_MODEL=claude-sonnet-4-20250514
CLAUDE_MAX_TOKENS=4096
CLAUDE_CACHE_ENABLED=true
```

## Usage

### Basic Message

```php
use ClaudePhp\LaravelIntegration\Facades\Claude;

$response = Claude::message('Explain Laravel service containers');

echo $response->content();
echo "Tokens: " . $response->totalTokens();
```

### With System Prompt

```php
$response = Claude::message(
    messages: 'How do I optimize database queries?',
    system: 'You are a Laravel performance expert'
);
```

### Streaming Response

```php
Claude::stream(
    messages: 'Write a tutorial on Laravel events',
    onChunk: function(string $chunk) {
        echo $chunk;
        flush();
    }
);
```

### Conversation Builder

```php
$conversation = Claude::conversation('You are a helpful Laravel assistant');

// Build conversation
$conversation
    ->user('What are service providers?')
    ->assistant('Service providers are...')
    ->user('Can you give me an example?');

// Get response
$response = $conversation->send();

// Continue conversation
$next = $conversation->continue('How about middleware?');
```

### In Controllers

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use ClaudePhp\LaravelIntegration\Facades\Claude;

class AiController extends Controller
{
    public function chat(Request $request)
    {
        $response = Claude::message($request->input('message'));

        return response()->json([
            'response' => $response->content(),
            'tokens' => $response->totalTokens(),
        ]);
    }
}
```

### Streaming in Controllers

```php
public function stream(Request $request)
{
    return response()->stream(function () use ($request) {
        Claude::stream(
            messages: $request->input('message'),
            onChunk: function(string $chunk) {
                echo "data: " . json_encode(['chunk' => $chunk]) . "\n\n";
                ob_flush();
                flush();
            }
        );
    }, 200, [
        'Content-Type' => 'text/event-stream',
        'Cache-Control' => 'no-cache',
    ]);
}
```

### Dependency Injection

```php
use ClaudePhp\LaravelIntegration\ClaudeService;

class MyService
{
    public function __construct(
        private ClaudeService $claude
    ) {}

    public function analyze(string $text): string
    {
        $response = $this->claude->message($text);
        return $response->content();
    }
}
```

## Prompt Templates

### Create Template Directory

```bash
mkdir -p resources/prompts
```

### Create Template

`resources/prompts/code-review.txt`:

```
You are an expert code reviewer.

Analyze the following {language} code for:
- Bugs and errors
- Security vulnerabilities
- Performance issues
- Best practices

Code:
{code}

Provide specific, actionable feedback.
```

### Use Template

```php
use ClaudePhp\LaravelIntegration\PromptManager;

$prompts = app(PromptManager::class);

$prompt = $prompts->fill('code-review', [
    'language' => 'PHP',
    'code' => $codeToReview,
]);

$response = Claude::message($prompt);
```

## Configuration

### config/claude.php

```php
return [
    'api_key' => env('CLAUDE_API_KEY'),
    'model' => env('CLAUDE_MODEL', 'claude-sonnet-4-20250514'),
    'max_tokens' => env('CLAUDE_MAX_TOKENS', 4096),
    'temperature' => env('CLAUDE_TEMPERATURE', null),

    'cache' => [
        'enabled' => env('CLAUDE_CACHE_ENABLED', true),
        'driver' => env('CLAUDE_CACHE_DRIVER', 'redis'),
        'ttl' => env('CLAUDE_CACHE_TTL', 3600),
    ],

    'prompts' => [
        'path' => resource_path('prompts'),
        'cache' => true,
    ],
];
```

## Caching

Responses are automatically cached when enabled. Cache key is generated from request parameters.

```php
// First call - hits API
$response1 = Claude::message('What is Laravel?');

// Second call - from cache
$response2 = Claude::message('What is Laravel?');
```

### Disable Caching Per Request

```php
config(['claude.cache.enabled' => false]);
$response = Claude::message('Real-time data needed');
config(['claude.cache.enabled' => true]);
```

## Testing

### Run Tests

```bash
composer test
```

### Test Example

```php
use ClaudePhp\LaravelIntegration\Tests\TestCase;

class MyTest extends TestCase
{
    public function test_claude_integration()
    {
        $response = \Claude::message('Test message');

        $this->assertNotNull($response->content());
    }
}
```

### Mocking Claude

```php
use ClaudePhp\LaravelIntegration\Facades\Claude;
use ClaudePhp\LaravelIntegration\ClaudeResponse;

Claude::shouldReceive('message')
    ->once()
    ->with('Test')
    ->andReturn(new ClaudeResponse([
        'id' => 'test',
        'content' => 'Mocked response',
        'model' => 'claude-sonnet-4-20250514',
        'role' => 'assistant',
        'stop_reason' => 'end_turn',
        'usage' => ['input_tokens' => 5, 'output_tokens' => 10],
    ]));

$response = Claude::message('Test');
$this->assertEquals('Mocked response', $response->content());
```

## Advanced Usage

### Custom Model Per Request

```php
$response = Claude::message(
    messages: 'Complex analysis task',
    model: 'claude-opus-4-20250514'
);
```

### Temperature Control

```php
// More creative
$response = Claude::message(
    messages: 'Write a creative story',
    temperature: 0.9
);

// More deterministic
$response = Claude::message(
    messages: 'Extract data from text',
    temperature: 0.1
);
```

### Multiple Messages

```php
$response = Claude::message([
    ['role' => 'user', 'content' => 'Hello'],
    ['role' => 'assistant', 'content' => 'Hi there!'],
    ['role' => 'user', 'content' => 'How are you?'],
]);
```

## API Reference

### Claude Facade

- `message(string|array $messages, ?string $system, ?string $model, ?int $maxTokens, ?float $temperature): ClaudeResponse`
- `stream(string|array $messages, callable $onChunk, ?string $system, ?string $model, ?int $maxTokens): void`
- `conversation(?string $system): Conversation`
- `client(): Anthropic`

### ClaudeResponse

- `content(): string` - Get response text
- `id(): string` - Get response ID
- `model(): string` - Get model used
- `inputTokens(): int` - Get input token count
- `outputTokens(): int` - Get output token count
- `totalTokens(): int` - Get total token count
- `toArray(): array` - Convert to array
- `toJson(): string` - Convert to JSON

### Conversation

- `user(string $content): self` - Add user message
- `assistant(string $content): self` - Add assistant message
- `send(?string $model, ?int $maxTokens): ClaudeResponse` - Send conversation
- `continue(string $message, ?string $model): ClaudeResponse` - Continue with new message
- `messages(): array` - Get all messages
- `clear(): self` - Clear conversation

## Production Best Practices

1. **Always cache** in production for cost savings
2. **Use Redis** for cache driver
3. **Monitor token usage** to track costs
4. **Implement rate limiting** to prevent abuse
5. **Log errors** for debugging
6. **Use queues** for long-running requests

## Troubleshooting

### API Key Not Set

Make sure `CLAUDE_API_KEY` is in your `.env` file.

### Cache Not Working

Check that Redis is running and configured correctly.

### Memory Issues

Reduce `max_tokens` or implement pagination for long responses.

## Next Steps

- Implement queue processing (Chapter 19)
- Add chatbot interface (Chapter 22)
- Create admin panel (Chapter 25)
- Implement monitoring (Chapter 37)

## Resources

- [Laravel Service Providers](https://laravel.com/docs/providers)
- [Laravel Facades](https://laravel.com/docs/facades)
- [Claude API Documentation](https://docs.anthropic.com/claude/reference/)
