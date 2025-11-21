---
title: "16: The Claude PHP SDK"
description: "Master the community-driven Claude PHP SDK, understanding why it's preferred over the official package, and learn advanced integration patterns."
series: "claude-php-developers"
chapter: 16
order: 16
difficulty: "Intermediate"
prerequisites:
  - "PHP 8.4+ with type declarations"
  - "Composer dependency management"
  - "Understanding of PSR standards"
  - "Basic Claude API knowledge"
---

![16: The Claude PHP SDK](/images/claude-php/chapter-16-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 16</span>
</div>

# Chapter 16: The Claude PHP SDK

## Overview

When building production applications with Claude in PHP, choosing the right SDK is critical. While Anthropic released an official SDK (`claude-php/claude-php-sdk`), it is currently in beta and has seen limited maintenance. In contrast, the community-driven **Claude-PHP-SDK** (`claude-php/claude-php-sdk`) has emerged as the standard for PHP developers.

This chapter explores why the community SDK is the preferred choice, its architecture (which mirrors the official Python SDK), and how to build robust applications using it.

## Why Not the Official SDK?

The official `claude-php/claude-php-sdk` package, while initially promising, has several drawbacks for production use:

1.  **Maintenance Status**: The official SDK is in beta and often lags behind API updates.
2.  **Feature Parity**: It lacks some features found in the official Python and TypeScript SDKs.
3.  **Community Support**: The PHP ecosystem has rallied around `claude-php/claude-php-sdk` due to its active development and faster release cycle.

The **Claude-PHP-SDK** aims for 1-to-1 feature parity with the official Python SDK, ensuring that PHP developers have access to the same capabilities, including latest model support, streaming, and tool use.

## What You'll Build

By the end of this chapter, you will have created:

- A fully configured Claude client using the community SDK
- A robust service layer that handles logging and error management
- A streaming implementation for real-time responses
- Comprehensive tests using mocked clients
- A configuration strategy for production environments

## Prerequisites

Before diving in, ensure you have:

- ✓ **PHP 8.4+**
- ✓ **Composer** installed
- ✓ **Anthropic API key**
- ✓ Completed [Chapter 03: Your First Claude Request](/series/claude-php-developers/chapters/03-first-claude-request)

**Estimated Time**: 45-60 minutes

## Installing the SDK

Remove the official SDK if you have it installed, and require the community package:

```bash
# Remove official SDK (if present)
composer remove claude-php/claude-php-sdk

# Install the community Claude-PHP-SDK
composer require claude-php/claude-php-sdk
```

**Verify installation:**

```bash
composer show claude-php/claude-php-sdk
```

## SDK Architecture

The `Claude-PHP-SDK` is designed to be simple and intuitive. Unlike the complex factory pattern of the official SDK, it uses a straightforward client instantiation approach.

### Basic Usage

```php
# filename: examples/01-basic-usage.php
<?php
declare(strict_types=1);

require 'vendor/autoload.php';

use ClaudePhp\ClaudePhp;

// Simple instantiation
$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

// Direct method calls
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 100,
    'messages' => [
        ['role' => 'user', 'content' => 'Say hello from the SDK!']
    ]
]);

// Response is object-accessible
echo $response->content[0]->text;
```

### Why It Works

The `ClaudePhp` class is the main entry point. It handles authentication, HTTP communication, and response parsing. The `messages()->create()` method maps directly to the `/v1/messages` endpoint.

Unlike the official SDK which returns typed objects, the community SDK returns array-accessible responses, giving you raw access to the data structure while still providing helper methods where necessary.

## Working with Responses

The SDK returns responses that are easy to work with standard PHP array functions.

```php
# filename: examples/02-responses.php
<?php
declare(strict_types=1);

require 'vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: $_ENV['ANTHROPIC_API_KEY']);

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 1024,
    'messages' => [['role' => 'user', 'content' => 'Analyze PHP 8.4']]
]);

// 1. Accessing Content
$text = $response->content[0]->text;

// 2. Checking Usage (Cost Tracking)
$inputTokens = $response->usage->inputTokens;
$outputTokens = $response->usage->outputTokens;

// 3. Metadata
$id = $response->id;
$stopReason = $response->stopReason; // e.g., 'end_turn' or 'max_tokens'

echo "Generated {$outputTokens} tokens. Stop reason: {$stopReason}\n";
```

## Streaming Responses

One of the most powerful features of the SDK is support for streaming. This allows you to process generation events in real-time, essential for chat interfaces.

```php
# filename: examples/03-streaming.php
<?php
declare(strict_types=1);

require 'vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: $_ENV['ANTHROPIC_API_KEY']);

// Use messages()->stream() method for streaming responses
$stream = $client->messages()->stream([
    'model' => 'claude-sonnet-4-5-20250929',
    'max_tokens' => 1024,
    'messages' => [['role' => 'user', 'content' => 'Write a haiku about code.']]
]);

echo "Streaming response:\n";

foreach ($stream as $event) {
    // Handle content updates
    if (($event['type'] ?? null) === 'content_block_delta') {
        echo $event['delta']['text'] ?? '';
        flush(); // Force output to terminal
    }
}
echo "\n";
```

### Why It Works

The `messages()->stream()` method returns a generator that yields Server-Sent Events (SSE) as they arrive from Claude's streaming API. You can iterate over this generator to handle events like `message_start`, `content_block_delta` (text chunks), and `message_stop` in real-time.

## Service Layer Pattern

Since the SDK doesn't use a complex middleware system out of the box, the best practice is to wrap the client in a service class. This allows you to add logging, caching, and application-specific logic.

```php
# filename: examples/04-service-pattern.php
<?php
declare(strict_types=1);

namespace App\Services;

use ClaudePhp\ClaudePhp;
use Psr\Log\LoggerInterface;

class ClaudeService
{
    public function __construct(
        private ClaudePhp $client,
        private LoggerInterface $logger
    ) {}

    public function generate(string $prompt, array $options = []): string
    {
        $model = $options['model'] ?? 'claude-sonnet-4-5-20250929';

        $this->logger->info('Claude Request', [
            'model' => $model,
            'prompt_length' => strlen($prompt)
        ]);

        try {
            $response = $this->client->messages()->create([
                'model' => $model,
                'max_tokens' => $options['max_tokens'] ?? 1024,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ]
            ]);

            $this->logger->info('Claude Response', [
                'id' => $response->id,
                'tokens' => $response->usage
            ]);

            return $response->content[0]->text;

        } catch (\Exception $e) {
            $this->logger->error('Claude API Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
```

### Why It Works

Wrapping the external SDK in your own service class (the **Adapter Pattern**) decouples your application from the specific SDK implementation. If you ever need to switch SDKs again or add global behavior like rate limiting, you only need to update this service class.

## Error Handling

The SDK throws specific exceptions that allow you to handle different failure scenarios gracefully.

```php
# filename: examples/05-error-handling.php
<?php
declare(strict_types=1);

use ClaudePhp\ClaudePhp;
use ClaudePhp\Exceptions\{
    APIConnectionError,
    RateLimitError,
    AuthenticationError,
    APIStatusError
};

$client = new ClaudePhp(apiKey: $_ENV['ANTHROPIC_API_KEY']);

try {
    $client->messages()->create([/* ... */]);
} catch (RateLimitError $e) {
    // Handle 429 Too Many Requests
    // Implement backoff/retry logic here
    error_log("Rate limit hit: " . $e->getMessage());
} catch (AuthenticationError $e) {
    // Handle invalid API key
    error_log("Authentication failed: " . $e->getMessage());
} catch (APIConnectionError $e) {
    // Handle network/timeout issues
    error_log("Connection error: " . $e->getMessage());
} catch (APIStatusError $e) {
    // Handle other 4xx/5xx errors
    error_log("API Error {$e->status_code}: {$e->message}");
} catch (\Exception $e) {
    // Handle unexpected errors
    error_log("Unexpected error: " . $e->getMessage());
}
```

## Testing with the SDK

Since the client is a simple class, you can mock it using PHPUnit's mocking capabilities or wrap it in an interface.

```php
# filename: tests/ClaudeServiceTest.php
<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ClaudePhp\ClaudePhp;
use App\Services\ClaudeService;
use Psr\Log\LoggerInterface;

class ClaudeServiceTest extends TestCase
{
    public function testGenerateText(): void
    {
        // Mock the Client
        $mockClient = $this->createMock(ClaudePhp::class);

        // Define expected response structure (as object)
        $mockResponse = (object) [
            'id' => 'msg_123',
            'content' => [(object) ['text' => 'Mocked response']],
            'usage' => (object) ['inputTokens' => 10, 'outputTokens' => 5]
        ];

        // Mock the messages() method to return a mock that has create()
        $mockMessages = $this->createMock(\stdClass::class);
        $mockMessages->expects($this->once())
            ->method('create')
            ->with($this->callback(function ($params) {
                return isset($params['model']) && str_contains($params['model'], 'claude-sonnet');
            }))
            ->willReturn($mockResponse);

        $mockClient->expects($this->once())
            ->method('messages')
            ->willReturn($mockMessages);

        // Mock Logger
        $mockLogger = $this->createMock(LoggerInterface::class);

        // Instantiate service
        $service = new ClaudeService($mockClient, $mockLogger);

        $result = $service->generate('Test prompt');

        $this->assertEquals('Mocked response', $result);
    }
}
```

## Best Practices Summary

1.  **Prefer the Community SDK**: It is actively maintained and follows the Python SDK's feature set.
2.  **Use Service Wrappers**: Wrap the `ClaudePhp` in your own classes to add application-specific logic.
3.  **Handle Object Responses**: Remember that responses are objects, not arrays. Use `$response->content[0]->text`.
4.  **Use Environment Variables**: Never hardcode API keys; use `$_ENV['ANTHROPIC_API_KEY']` for security.
5.  **Leverage Streaming**: Use `$client->messages()->stream([...])` method for real-time responses.

## Troubleshooting

### "Class ClaudePHP\ClaudePhp not found"

- Run `composer dump-autoload`.
- Ensure you installed `claude-php/claude-php-sdk` and not `claude-php/claude-php-sdk`.

### "Undefined array key 'text'"

- Responses can contain multiple content blocks. Always check `$response->content[0]->text` or iterate through content blocks.
- Ensure the API call succeeded before accessing keys.

### "SSL Certificate Errors"

- If you encounter SSL issues locally, ensure your PHP installation has a valid `curl.cainfo` configuration.

## Further Reading

- **[Claude-PHP-SDK GitHub](https://github.com/claude-php/Claude-PHP-SDK)** — Official repository and documentation
- **[Anthropic API Reference](https://docs.anthropic.com/en/api/getting-started)** — API endpoint details
- **[Packagist Package](https://packagist.org/packages/claude-php/claude-php-sdk)** — Version history and installation

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="16"
  label="You've mastered the Claude PHP SDK integration!"
/>

---

Continue to [Chapter 17: Building a Claude Service Class](/series/claude-php-developers/chapters/17-claude-service-class) to apply these patterns in a robust architecture.
