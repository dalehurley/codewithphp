---
title: "Appendix A: API Reference Quick Guide"
description: "Complete Claude API reference with all endpoints, parameters, request/response examples, and error codes for PHP developers."
series: "claude-php-developers"
appendix: "A"
order: 100
difficulty: "Reference"
---

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Appendix A: API Reference</span>
</div>

# Appendix A: API Reference Quick Guide

Complete reference for the Claude API with PHP examples. Bookmark this page for quick access to endpoints, parameters, and response formats.

---

## Table of Contents

- [Base Configuration](#base-configuration)
- [Messages API](#messages-api)
- [Request Parameters](#request-parameters)
- [Response Format](#response-format)
- [Streaming Responses](#streaming-responses)
- [Tool Use (Function Calling)](#tool-use-function-calling)
- [Vision API](#vision-api)
- [Error Responses](#error-responses)
- [Rate Limits](#rate-limits)
- [Model Versions](#model-versions)

---

## Base Configuration

### API Endpoint

```
https://api.anthropic.com/v1/messages
```

### Authentication Header

```php
'x-api-key' => 'your_api_key_here'
```

### Required Headers

```php
$headers = [
    'x-api-key' => getenv('ANTHROPIC_API_KEY'),
    'anthropic-version' => '2023-06-01',
    'content-type' => 'application/json',
];
```

### Optional Headers

```php
// Beta features
'anthropic-beta' => 'prompt-caching-2024-07-31'

// Custom identifier for your integration
'anthropic-client-id' => 'your-client-id'
```

### PHP SDK Initialization

```php
use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->withHttpHeader('anthropic-version', '2023-06-01')
    ->make();
```

---

## Messages API

### Create Message (Basic)

**Endpoint:** `POST /v1/messages`

**Minimal Request:**

```php
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'Hello, Claude!']
    ]
]);
```

**Complete Request with All Options:**

```php
$response = $client->messages()->create([
    // Required parameters
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 4096,
    'messages' => [
        [
            'role' => 'user',
            'content' => 'Analyze this code and suggest improvements.'
        ]
    ],

    // Optional parameters
    'system' => 'You are an expert PHP developer and code reviewer.',
    'temperature' => 0.7,
    'top_p' => 0.9,
    'top_k' => 40,
    'stop_sequences' => ['</code>', 'END'],
    'metadata' => [
        'user_id' => 'user_12345'
    ],

    // Streaming
    'stream' => false,
]);
```

**Multi-turn Conversation:**

```php
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [
        [
            'role' => 'user',
            'content' => 'What is dependency injection?'
        ],
        [
            'role' => 'assistant',
            'content' => 'Dependency injection is a design pattern...'
        ],
        [
            'role' => 'user',
            'content' => 'Show me a PHP example.'
        ]
    ]
]);
```

---

## Request Parameters

### Required Parameters

| Parameter | Type | Description | Example |
|-----------|------|-------------|---------|
| `model` | string | Model identifier | `"claude-sonnet-4-20250514"` |
| `max_tokens` | integer | Maximum tokens to generate (1-4096) | `1024` |
| `messages` | array | Array of message objects | See examples |

### Optional Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `system` | string | null | System prompt that defines behavior |
| `temperature` | float | 1.0 | Randomness (0.0-1.0) |
| `top_p` | float | -1 | Nucleus sampling threshold |
| `top_k` | integer | -1 | Top-k sampling parameter |
| `stop_sequences` | array | [] | Sequences that stop generation |
| `stream` | boolean | false | Enable streaming responses |
| `metadata` | object | {} | Custom metadata for request |
| `tools` | array | [] | Available tools for function calling |
| `tool_choice` | object | auto | How to use tools |

### Message Object Structure

```php
[
    'role' => 'user',      // Required: 'user' or 'assistant'
    'content' => 'text'    // Required: string or array of content blocks
]
```

**Content Block Types:**

```php
// Text content
['type' => 'text', 'text' => 'Your message here']

// Image content (base64)
[
    'type' => 'image',
    'source' => [
        'type' => 'base64',
        'media_type' => 'image/jpeg',
        'data' => base64_encode($imageData)
    ]
]

// Image content (URL)
[
    'type' => 'image',
    'source' => [
        'type' => 'url',
        'url' => 'https://example.com/image.jpg'
    ]
]

// Tool use (assistant message)
[
    'type' => 'tool_use',
    'id' => 'toolu_12345',
    'name' => 'get_weather',
    'input' => ['city' => 'San Francisco']
]

// Tool result (user message)
[
    'type' => 'tool_result',
    'tool_use_id' => 'toolu_12345',
    'content' => 'Temperature: 72°F, Sunny'
]
```

---

## Response Format

### Standard Response

```php
// Response object structure
$response = [
    'id' => 'msg_01AbCdEfGhIjKlMnOpQr',
    'type' => 'message',
    'role' => 'assistant',
    'content' => [
        [
            'type' => 'text',
            'text' => 'Hello! How can I help you today?'
        ]
    ],
    'model' => 'claude-sonnet-4-20250514',
    'stop_reason' => 'end_turn',
    'stop_sequence' => null,
    'usage' => [
        'input_tokens' => 12,
        'output_tokens' => 25
    ]
];

// Accessing response text
$text = $response->content[0]->text;

// Accessing usage
$inputTokens = $response->usage->input_tokens;
$outputTokens = $response->usage->output_tokens;
```

### Stop Reasons

| Stop Reason | Description |
|-------------|-------------|
| `end_turn` | Natural conversation end |
| `max_tokens` | Hit max_tokens limit |
| `stop_sequence` | Hit a stop sequence |
| `tool_use` | Claude wants to use a tool |

### Response with Tool Use

```php
$response = [
    'id' => 'msg_01AbCdEfGhIjKlMnOpQr',
    'type' => 'message',
    'role' => 'assistant',
    'content' => [
        [
            'type' => 'text',
            'text' => "I'll check the weather for you."
        ],
        [
            'type' => 'tool_use',
            'id' => 'toolu_01ABC',
            'name' => 'get_weather',
            'input' => [
                'city' => 'San Francisco',
                'units' => 'fahrenheit'
            ]
        ]
    ],
    'stop_reason' => 'tool_use',
    'usage' => [
        'input_tokens' => 250,
        'output_tokens' => 45
    ]
];
```

---

## Streaming Responses

### Enable Streaming

```php
$stream = $client->messages()->createStreamed([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'Write a haiku about PHP']
    ]
]);

foreach ($stream as $event) {
    if ($event->type === 'content_block_delta') {
        echo $event->delta->text;
    }
}
```

### Stream Event Types

| Event Type | Description | Data |
|------------|-------------|------|
| `message_start` | Stream started | Message metadata |
| `content_block_start` | Content block started | Block index and type |
| `content_block_delta` | Partial content | Delta with text |
| `content_block_stop` | Content block finished | Block index |
| `message_delta` | Message metadata update | Usage, stop_reason |
| `message_stop` | Stream ended | None |
| `ping` | Keepalive | None |
| `error` | Error occurred | Error details |

### Stream Event Examples

```php
// message_start
[
    'type' => 'message_start',
    'message' => [
        'id' => 'msg_01ABC',
        'type' => 'message',
        'role' => 'assistant',
        'content' => [],
        'model' => 'claude-sonnet-4-20250514',
        'usage' => ['input_tokens' => 12, 'output_tokens' => 0]
    ]
]

// content_block_delta
[
    'type' => 'content_block_delta',
    'index' => 0,
    'delta' => [
        'type' => 'text_delta',
        'text' => 'Hello'
    ]
]

// message_delta
[
    'type' => 'message_delta',
    'delta' => [
        'stop_reason' => 'end_turn',
        'stop_sequence' => null
    ],
    'usage' => ['output_tokens' => 25]
]
```

---

## Tool Use (Function Calling)

### Define Tools

```php
$tools = [
    [
        'name' => 'get_weather',
        'description' => 'Get current weather for a city',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'city' => [
                    'type' => 'string',
                    'description' => 'City name'
                ],
                'units' => [
                    'type' => 'string',
                    'enum' => ['celsius', 'fahrenheit'],
                    'description' => 'Temperature units'
                ]
            ],
            'required' => ['city']
        ]
    ],
    [
        'name' => 'search_database',
        'description' => 'Search database for records',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'query' => [
                    'type' => 'string',
                    'description' => 'Search query'
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Maximum results',
                    'default' => 10
                ]
            ],
            'required' => ['query']
        ]
    ]
];

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'tools' => $tools,
    'messages' => [
        ['role' => 'user', 'content' => 'What is the weather in Paris?']
    ]
]);
```

### Tool Choice Options

```php
// Auto (default) - Let Claude decide
'tool_choice' => ['type' => 'auto']

// Force a specific tool
'tool_choice' => [
    'type' => 'tool',
    'name' => 'get_weather'
]

// Require any tool
'tool_choice' => ['type' => 'any']
```

### Handle Tool Use Response

```php
// Check if Claude wants to use a tool
foreach ($response->content as $block) {
    if ($block->type === 'tool_use') {
        $toolName = $block->name;
        $toolInput = $block->input;
        $toolUseId = $block->id;

        // Execute the tool
        $result = executeTool($toolName, $toolInput);

        // Send result back to Claude
        $followUp = $client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 1024,
            'tools' => $tools,
            'messages' => [
                ['role' => 'user', 'content' => 'What is the weather in Paris?'],
                ['role' => 'assistant', 'content' => $response->content],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'tool_result',
                            'tool_use_id' => $toolUseId,
                            'content' => json_encode($result)
                        ]
                    ]
                ]
            ]
        ]);
    }
}
```

---

## Vision API

### Send Image (Base64)

```php
$imageData = file_get_contents('image.jpg');
$base64Image = base64_encode($imageData);

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [
        [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'image',
                    'source' => [
                        'type' => 'base64',
                        'media_type' => 'image/jpeg',
                        'data' => $base64Image
                    ]
                ],
                [
                    'type' => 'text',
                    'text' => 'What is in this image?'
                ]
            ]
        ]
    ]
]);
```

### Send Image (URL)

```php
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [
        [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'image',
                    'source' => [
                        'type' => 'url',
                        'url' => 'https://example.com/image.jpg'
                    ]
                ],
                [
                    'type' => 'text',
                    'text' => 'Describe this image in detail.'
                ]
            ]
        ]
    ]
]);
```

### Supported Image Formats

| Format | MIME Type | Max Size |
|--------|-----------|----------|
| JPEG | `image/jpeg` | 5 MB |
| PNG | `image/png` | 5 MB |
| GIF | `image/gif` | 5 MB |
| WebP | `image/webp` | 5 MB |

---

## Error Responses

### Error Response Structure

```php
[
    'type' => 'error',
    'error' => [
        'type' => 'invalid_request_error',
        'message' => 'max_tokens: Field required'
    ]
]
```

### Common Error Types

| Error Type | HTTP Status | Description | Solution |
|------------|-------------|-------------|----------|
| `invalid_request_error` | 400 | Malformed request | Check request parameters |
| `authentication_error` | 401 | Invalid API key | Verify API key |
| `permission_error` | 403 | No access to resource | Check account permissions |
| `not_found_error` | 404 | Resource not found | Verify endpoint URL |
| `rate_limit_error` | 429 | Too many requests | Implement rate limiting |
| `api_error` | 500 | Server error | Retry with backoff |
| `overloaded_error` | 529 | Service overloaded | Retry with backoff |

### Error Handling Example

```php
use Anthropic\Exceptions\AnthropicException;
use Anthropic\Exceptions\ErrorException;

try {
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 1024,
        'messages' => [
            ['role' => 'user', 'content' => 'Hello!']
        ]
    ]);
} catch (ErrorException $e) {
    $errorType = $e->getErrorType();
    $errorMessage = $e->getMessage();

    match($errorType) {
        'rate_limit_error' => handleRateLimit($e),
        'invalid_request_error' => logInvalidRequest($e),
        'authentication_error' => refreshApiKey($e),
        default => logError($e)
    };
}
```

---

## Rate Limits

### Rate Limit Headers

```php
// Check remaining requests
$remaining = $response->headers['anthropic-ratelimit-requests-remaining'];
$limit = $response->headers['anthropic-ratelimit-requests-limit'];
$reset = $response->headers['anthropic-ratelimit-requests-reset'];

// Check remaining tokens
$tokensRemaining = $response->headers['anthropic-ratelimit-tokens-remaining'];
$tokensLimit = $response->headers['anthropic-ratelimit-tokens-limit'];
$tokensReset = $response->headers['anthropic-ratelimit-tokens-reset'];
```

### Default Rate Limits (Tier 1)

| Limit Type | Claude Opus | Claude Sonnet | Claude Haiku |
|------------|-------------|---------------|--------------|
| Requests/min | 50 | 50 | 50 |
| Tokens/min | 40,000 | 40,000 | 40,000 |
| Tokens/day | 1,000,000 | 1,000,000 | 1,000,000 |

*Note: Limits increase with higher tiers. Check console.anthropic.com for your tier.*

### Handle Rate Limiting

```php
function makeRequestWithRetry($client, $params, $maxRetries = 3) {
    $attempt = 0;

    while ($attempt < $maxRetries) {
        try {
            return $client->messages()->create($params);
        } catch (ErrorException $e) {
            if ($e->getErrorType() === 'rate_limit_error') {
                $attempt++;
                $waitTime = min(pow(2, $attempt) * 1000000, 32000000);
                usleep($waitTime);
                continue;
            }
            throw $e;
        }
    }

    throw new Exception('Max retries exceeded');
}
```

---

## Model Versions

### Current Models (2024)

| Model | Identifier | Context | Use Case |
|-------|-----------|---------|----------|
| **Claude Opus 4** | `claude-opus-4-20250514` | 200K tokens | Most capable, complex tasks |
| **Claude Sonnet 4** | `claude-sonnet-4-20250514` | 200K tokens | Balanced performance & cost |
| **Claude Haiku 3.5** | `claude-3-5-haiku-20241022` | 200K tokens | Fast, cost-effective |

### Model Selection Guide

```php
// Complex reasoning, highest quality
$model = 'claude-opus-4-20250514';

// Balanced - most use cases
$model = 'claude-sonnet-4-20250514';

// High volume, simple tasks
$model = 'claude-3-5-haiku-20241022';
```

### Model Pricing (Approximate)

| Model | Input (per 1M tokens) | Output (per 1M tokens) |
|-------|----------------------|------------------------|
| Opus 4 | $15.00 | $75.00 |
| Sonnet 4 | $3.00 | $15.00 |
| Haiku 3.5 | $0.80 | $4.00 |

*Check official pricing at anthropic.com/pricing for current rates.*

---

## Quick Reference: Complete Example

```php
<?php
require 'vendor/autoload.php';

use Anthropic\Anthropic;
use Anthropic\Exceptions\ErrorException;

// Initialize client
$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

// Define tools
$tools = [
    [
        'name' => 'calculate',
        'description' => 'Perform mathematical calculation',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'expression' => [
                    'type' => 'string',
                    'description' => 'Mathematical expression to evaluate'
                ]
            ],
            'required' => ['expression']
        ]
    ]
];

try {
    // Make request
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 1024,
        'system' => 'You are a helpful assistant.',
        'temperature' => 0.7,
        'tools' => $tools,
        'messages' => [
            [
                'role' => 'user',
                'content' => 'What is 25 * 47?'
            ]
        ]
    ]);

    // Process response
    foreach ($response->content as $block) {
        if ($block->type === 'text') {
            echo $block->text . "\n";
        } elseif ($block->type === 'tool_use') {
            echo "Tool: {$block->name}\n";
            echo "Input: " . json_encode($block->input) . "\n";
        }
    }

    // Log usage
    echo "\nTokens used: {$response->usage->input_tokens} in, ";
    echo "{$response->usage->output_tokens} out\n";

} catch (ErrorException $e) {
    echo "Error: {$e->getMessage()}\n";
    echo "Type: {$e->getErrorType()}\n";
}
```

---

## Additional Resources

- **[Official API Documentation](https://docs.anthropic.com)** - Complete API reference
- **[API Status Page](https://status.anthropic.com)** - Service status
- **[Anthropic Console](https://console.anthropic.com)** - Manage API keys
- **[PHP SDK Repository](https://github.com/anthropics/anthropic-sdk-php)** - SDK source code
- **[Pricing Calculator](https://anthropic.com/pricing)** - Calculate costs

---

::: tip Quick Navigation
- **[← Back to Series](/series/claude-php-developers)** - Return to main series
- **[Appendix B: Prompting Patterns →](/series/claude-php-developers/appendices/b-prompting-patterns)** - Proven prompt templates
- **[Appendix C: Error Codes →](/series/claude-php-developers/appendices/c-error-codes)** - Troubleshooting guide
:::

*Last updated: November 2024 • API Version: 2023-06-01*
