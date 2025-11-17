---
title: "16: The Official PHP SDK"
description: "Use the official anthropic-ai/sdk package correctly: installation, typed requests, streaming, retries, pagination, and how to layer middleware or custom transports around it."
series: "claude-php-developers"
chapter: 16
order: 16
difficulty: "Expert"
prerequisites:
  - "PHP 8.1+ with type declarations"
  - "Composer dependency management"
  - "Basic Claude API knowledge"
---

![16: The Official PHP SDK](/images/claude-php/chapter-16-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 16</span>
</div>

# Chapter 16: The Official PHP SDK

## Overview

The official Anthropic PHP SDK (`anthropic-ai/sdk`) gives you typed request/response models, streaming support, automatic retries, and auto-pagination. It does **not** ship with a factory pattern or middleware system—those come from community SDKs or your own HTTP client abstractions. This chapter sticks to what the official SDK provides out of the box and shows how to wrap it safely when you need custom transports or logging.

We will also highlight how the official package differs from popular community SDKs so you can choose the right API surface and avoid mixing them up.

## What You'll Build

- A correctly installed and configured official SDK client using `Anthropic\Client`.
- Typed message requests with `MessageParam` and streaming responses via `messages->createStream()`.
- Resilient calls that use the SDK's built-in retries and structured errors.
- Auto-pagination for batch/list endpoints.
- A thin wrapper you can extend with logging or metrics (middleware-style) without claiming it is an SDK feature.
- Testing patterns that inject a mock transport so you can run unit tests without hitting the API.

## Prerequisites

- ✓ **PHP 8.1+** (the official SDK's minimum requirement)
- ✓ **Composer** for dependency management
- ✓ **Anthropic API key** and basic familiarity with Claude requests
- ✓ Optional: PSR-7/PSR-18 knowledge if you plan to add your own HTTP client wrappers

**Estimated Time**: 45-60 minutes

## Installing the SDK (official package)

```bash
# Install the official Anthropic PHP SDK
composer require "anthropic-ai/sdk:^0.3" --prefer-dist

# (Optional) dotenv for loading environment variables in examples
composer require vlucas/phpdotenv
```

Verification:

```bash
composer show anthropic-ai/sdk
```

You should see package details and version information. The official SDK requires PHP 8.1+; there is no built-in middleware system or PSR-18 adapter—those are patterns you can add on top.

::: tip
If you previously installed a community SDK such as `ahmadrosid/anthropic-php` or `jordandalton/anthropic-sdk-php`, remove it before following this chapter to avoid API-surface conflicts.
:::

## Pick the right SDK

| Package | Maintainer | Typical usage | Notes |
| --- | --- | --- | --- |
| `anthropic-ai/sdk` | Anthropic (official) | `new Client(apiKey: getenv('ANTHROPIC_API_KEY')); $client->messages->create([...]);` | Beta status, typed models, streaming, retries, pagination. |
| `ahmadrosid/anthropic-php` | Community | `Anthropic::factory()->withHttpClient(...)->messages()` | Factory + middleware hooks are part of this library, not the official one. |
| `jordandalton/anthropic-sdk-php` | Community | Saloon-based API | Also not the official SDK. |

This chapter uses `anthropic-ai/sdk` exclusively.

## Quickstart with the official client

```php
<?php
# examples/00-quickstart.php
declare(strict_types=1);

require 'vendor/autoload.php';

use Anthropic\Client;
use Anthropic\Messages\MessageParam;

$client = new Client(apiKey: getenv('ANTHROPIC_API_KEY'));

$response = $client->messages->create(
    model: 'claude-3-5-sonnet-20240620',
    maxTokens: 512,
    messages: [
        MessageParam::fromUser('Give me two bullet points about ocean conservation.'),
    ],
);

echo $response->content[0]->text, "\n";
```

Key points:
- Use `Anthropic\Client` directly; there is no `Anthropic::factory()` in the official SDK.
- `MessageParam` provides typed helpers for user/assistant messages.
- The `messages` resource is accessed as `$client->messages->create(...)`, not `messages()`.

## Built-in capabilities you should rely on

### Typed requests and responses

Use the provided value objects for safer requests:

```php
use Anthropic\Messages\ToolParam;

$prompt = [
    MessageParam::fromUser('Suggest a project name for a task manager SaaS.'),
];

$response = $client->messages->create(
    model: 'claude-3-5-sonnet-20240620',
    maxTokens: 128,
    messages: $prompt,
    tools: [
        ToolParam::builder(name: 'logSuggestion', description: 'Store the suggestion'),
    ],
);
```

### Streaming responses

```php
$stream = $client->messages->createStream(
    model: 'claude-3-5-sonnet-20240620',
    maxTokens: 256,
    messages: [MessageParam::fromUser('Stream one fun fact about coral reefs.')],
);

foreach ($stream as $event) {
    if ($event->type === 'content_block_delta') {
        echo $event->delta->text;
    }
}
```

### Automatic retries and structured errors

The client retries idempotent requests by default. Configure limits when you construct the client:

```php
$client = new Client(
    apiKey: getenv('ANTHROPIC_API_KEY'),
    maxRetries: 2, // default is 2; set to 0 to disable
);
```

Handle errors with the SDK's exception hierarchy:

```php
use Anthropic\Exceptions\ApiError;

try {
    $client->messages->create(
        model: 'claude-3-5-sonnet-20240620',
        maxTokens: 1,
        messages: [MessageParam::fromUser('Hi')],
    );
} catch (ApiError $e) {
    error_log('Anthropic error: ' . $e->getMessage());
}
```

### Auto-pagination

List endpoints expose iterators so you can stream through pages without manual cursors:

```php
foreach ($client->beta->messages->batches->list() as $batch) {
    echo $batch->id, "\n";
}
```

## Adding middleware-style hooks (your code, not the SDK)

The official SDK does not provide a middleware system. You can build one around it with a thin wrapper that calls hooks before and after `messages->create()` or while streaming events. The code samples in `code-samples/claude-php/chapter-16` include `SDKWrapper.php`, which accepts user-defined callbacks for logging, metrics, and rate limiting without modifying the SDK itself.

Example hook usage:

```php
use ClaudePHP\SDK\SDKWrapper;

$sdk = new SDKWrapper(apiKey: getenv('ANTHROPIC_API_KEY'));

$sdk->addMiddleware(function (string $phase, mixed $payload) {
    if ($phase === 'request') {
        echo "→ Sending request\n";
    }
    if ($phase === 'response') {
        echo "← Received response\n";
    }
    return $payload;
});

$result = $sdk->sendMessage([
    'model' => 'claude-3-5-sonnet-20240620',
    'maxTokens' => 128,
    'messages' => [['role' => 'user', 'content' => 'Summarize PSR-7 in one line.']],
]);
```

## Testing without live API calls

Pass a mock transport (a callable) into the wrapper so unit tests avoid the network:

```php
$fakeTransport = function (array $payload) {
    return [
        'content' => [['type' => 'text', 'text' => 'Mocked reply']],
        'usage' => ['input_tokens' => 5, 'output_tokens' => 2],
    ];
};

$sdk = new SDKWrapper(apiKey: 'test-key', transport: $fakeTransport);
$response = $sdk->sendMessage([
    'model' => 'claude-3-5-sonnet-20240620',
    'maxTokens' => 32,
    'messages' => [['role' => 'user', 'content' => 'Hello']],
]);
```

Because the transport is user-supplied, you can inject fixtures in PHPUnit without touching the real API or the SDK internals.

## Checklist

- [ ] Installed `anthropic-ai/sdk` and verified PHP 8.1+.
- [ ] Switched any community SDK usage to `Anthropic\Client` syntax (no factories or `messages()` methods).
- [ ] Used typed helpers like `MessageParam` for safer requests.
- [ ] Enabled streaming and retries where appropriate.
- [ ] Added your own wrapper for logging/metrics instead of assuming middleware is built in.
- [ ] Wrote tests with a mock transport to keep suites fast and deterministic.

With these patterns you get the official SDK's strengths—type safety, streaming, retries, and pagination—while keeping middleware and transport concerns in your own layer where you have full control.
