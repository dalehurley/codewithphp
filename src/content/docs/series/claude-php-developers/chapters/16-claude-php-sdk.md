---
title: "16: The Claude PHP SDK"
description: "Stream Claude responses with the community PHP SDK and use Guzzle to test request and response middleware without an API key."
sidebar:
  label: "16: The Claude PHP SDK"
  order: 16
  badge:
    text: Intermediate
    variant: caution
---

![16: The Claude PHP SDK](/images/claude-php/chapter-16-hero-full.webp)

## Overview

Use the community Claude-PHP-SDK to stream text from Claude. Then use a Guzzle transport to add application middleware, record token usage, and test the Messages request with a fake response.

The Composer package `claude-php/claude-php-sdk` provides `ClaudePhp\ClaudePhp`, the client used throughout these examples. Anthropic’s official PHP SDK is the separate package `anthropic-ai/sdk`, which provides `Anthropic\Client`. Each package documents its own request and response types. [Community SDK](https://github.com/claude-php/Claude-PHP-SDK) · [Anthropic PHP SDK](https://platform.claude.com/docs/en/cli-sdks-libraries/sdks/php)

## Prerequisites

Use PHP 8.6, Composer, and a local copy of the [Chapter 16 examples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-16). Review [Chapter 03](/series/claude-php-developers/chapters/03-first-claude-request) for basic request and response handling.

## Installing the SDK

From the repository root, install the chapter’s Composer dependencies. The mock examples work immediately.

```bash
cd code/claude-php/chapter-16
composer install
php examples/custom-transport.php
php examples/testing.php
```

The first command that runs PHP prints `Mocked response`. The second checks the method, path, headers, request payload, middleware order, and response content.

For live examples, copy `.env.example` to `.env` and provide `ANTHROPIC_API_KEY`. The examples use `ANTHROPIC_MODEL` if you set it.

## Streaming Responses

```bash
php examples/stream.php
```

`messages()->stream()` takes the same array of request parameters as `messages()->create()`. It returns an iterable of decoded server-sent events. Set Guzzle’s `stream` option to let the SDK read the HTTP response incrementally. Below is the text handling from the sample.

```php
<?php

declare(strict_types=1);

use ClaudePhp\ClaudePhp;
use GuzzleHttp\Client;

$config = require __DIR__ . '/../bootstrap.php';
$transport = new Client(['stream' => true, 'timeout' => 60.0]);
$client = new ClaudePhp(apiKey: $config['apiKey'], httpClient: $transport);

$stream = $client->messages()->stream([
    'model' => $config['model'],
    'max_tokens' => 256,
    'messages' => [['role' => 'user', 'content' => 'Explain dependency injection in one sentence.']],
]);

try {
    foreach ($stream as $event) {
        if ($event['type'] === 'error') {
            throw new RuntimeException('Claude sent a stream error.');
        }

        if ($event['type'] === 'content_block_delta' && $event['delta']['type'] === 'text_delta') {
            echo $event['delta']['text'];
            flush();
        }
    }
} finally {
    $stream->close();
}
```

The downloadable script also checks for the `message_stop` event so it can report an incomplete response. Keep that check when saving the output as a finished message. Web servers and proxies can buffer output, so verify streaming end to end if you use it in a browser. [Claude streaming events](https://platform.claude.com/docs/en/build-with-claude/streaming)

## Middleware and Custom Transport

The chapter’s `SDKWrapper` uses Guzzle to send JSON directly to the Messages API. Each registered middleware function sees the request payload before sending and the decoded response after it returns. Return the data from each callback so the next callback receives it.

```php
<?php

declare(strict_types=1);

use ClaudePHP\SDK\SDKWrapper;

$config = require __DIR__ . '/../bootstrap.php';
$client = new SDKWrapper($config['apiKey']);

$client->addMiddleware(static function (string $phase, array $data): array {
    if ($phase === 'response') {
        printf("Output tokens: %d\n", $data['usage']['output_tokens']);
    }

    return $data;
});

$client->sendMessage([
    'model' => $config['model'],
    'max_tokens' => 256,
    'messages' => [['role' => 'user', 'content' => 'What is middleware?']],
]);
```

Run `php examples/middleware.php` to aggregate token usage over two requests. Run `php examples/sdk-advanced.php` to log the model and response ID. Logging identifiers and counts is usually sufficient to correlate failures without recording API keys or prompts.

## Testing with a Mock Response

`examples/testing.php` injects a `GuzzleHttp\Client` backed by `MockHandler`. The queued response contains the same text and usage fields as the live endpoint, and Guzzle’s history middleware saves the outgoing request for inspection. This runs the wrapper and its middleware locally.

Try changing the expected token count in the test and rerun it. The script reports a failure and exits with a nonzero status. Restore the expected value to see the pass result.

## Troubleshooting

- If Composer cannot load `ClaudePhp\ClaudePhp`, run `composer install` from the Chapter 16 directory and confirm `composer show claude-php/claude-php-sdk`.
- For authentication errors, verify the key in the chapter’s `.env` and check whether your shell already exports a different key.
- If the stream ends early, handle the text as partial output and investigate the network or response error before retrying.

Continue to [Chapter 17: Building a Claude Service Class](/series/claude-php-developers/chapters/17-claude-service-class) to apply these patterns in a reusable service.
