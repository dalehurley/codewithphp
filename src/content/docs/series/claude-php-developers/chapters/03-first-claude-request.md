---
title: "03: Your First Claude Request in PHP"
description: "Make your first Claude API call in PHP with the community SDK or Guzzle, read the response, and diagnose common request errors."
sidebar:
  label: "03: Your First Claude Request in PHP"
  order: 3
  badge:
    text: Beginner
    variant: success
---

![03: Your First Claude Request in PHP](/images/claude-php/chapter-03-hero-full.webp)

## Overview

Send a text request to Claude with the community [Claude-PHP-SDK](https://github.com/claude-php/Claude-PHP-SDK), then make the same kind of request directly over HTTP with Guzzle. You’ll read text blocks and token counts, parse JSON supplied by the model, and diagnose failed requests.

## Prerequisites

- PHP 8.6 and Composer
- An Anthropic API key from [Chapter 02](/series/claude-php-developers/chapters/02-authentication-api-keys)
- A local copy of the [Chapter 03 examples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-03)

## Step 1: Installation and Project Setup

From the repository root, open the chapter directory and install its dependencies.

```bash
cd code/claude-php/chapter-03
composer install
cp .env.example .env
```

Set `ANTHROPIC_API_KEY` in `.env` or export it from your shell. Keep `.env` out of version control. `ANTHROPIC_MODEL` defaults to `claude-sonnet-5-5`. Use a model ID available to your account if you need to change it. [Claude model lifecycle](https://platform.claude.com/docs/en/about-claude/model-deprecations)

## Step 2: Your First Claude API Request

```bash
php examples/sdk-request.php
```

The script loads the key and model from the chapter’s `bootstrap.php`. Here is the request at its core.

```php
<?php

declare(strict_types=1);

use ClaudePhp\ClaudePhp;

$config = require __DIR__ . '/../bootstrap.php';
$client = new ClaudePhp(apiKey: $config['apiKey']);

$response = $client->messages()->create([
    'model' => $config['model'],
    'max_tokens' => 256,
    'system' => 'Answer in one concise sentence.',
    'messages' => [
        ['role' => 'user', 'content' => 'What is Composer used for in PHP?'],
    ],
]);

foreach ($response->content as $block) {
    if ($block['type'] === 'text') {
        echo $block['text'] . PHP_EOL;
    }
}

printf("Tokens: %d in, %d out\n", $response->usage->input_tokens, $response->usage->output_tokens);
```

Expect a sentence about dependency management followed by input and output token counts. The wording and token counts vary by request. The community SDK accepts a single associative array with snake_case API fields. Its message response has object properties, with an array of content blocks.

Anthropic also publishes a PHP SDK under `anthropic-ai/sdk`. Its examples use `Anthropic\Client` and a different method signature. [Anthropic PHP SDK reference](https://platform.claude.com/docs/en/cli-sdks-libraries/sdks/php)

## Step 3: Understanding Request Parameters

| Field | Purpose |
| --- | --- |
| `model` | Selects the Claude model for the request. |
| `max_tokens` | Caps the generated output. Check `stop_reason` for `max_tokens` if output is cut short. |
| `messages` | Supplies an ordered conversation using `user` and `assistant` turns. |
| `system` | Gives instructions for the entire request. |
| `stop_sequences` | Supplies optional strings that end generation when encountered, where supported by the selected model. |

The Messages API is stateless. For a follow-up, send the needed history along with the next user turn. Separate the system instructions from that history.

```php
<?php

$conversation = [
    ['role' => 'user', 'content' => 'What does Composer do?'],
    ['role' => 'assistant', 'content' => 'It manages PHP dependencies and autoloading.'],
    ['role' => 'user', 'content' => 'What does composer.lock record?'],
];
```

The examples use prompt instructions to control style and format. Check model support before adding sampling parameters such as `temperature` or `top_p`. [Messages API reference](https://platform.claude.com/docs/en/api/messages/create)

## Building Requests with the Builder Pattern

Start with an associative array for a single call. As requests become more dynamic, centralise required fields and defaults, then change only what each caller needs. This example uses a function as the smallest reusable request builder.

```php
<?php

declare(strict_types=1);

function buildMessageRequest(string $model, string $prompt, int $maxTokens = 256): array
{
    if (trim($prompt) === '' || $maxTokens < 1) {
        throw new InvalidArgumentException('Supply a prompt and a positive token limit.');
    }

    return [
        'model' => $model,
        'max_tokens' => $maxTokens,
        'messages' => [['role' => 'user', 'content' => $prompt]],
    ];
}
```

Pass the array to `$client->messages()->create()`. A builder class becomes useful when several components contribute the model, system prompt, or conversation history.

## Make the Request with Guzzle

```bash
php examples/guzzle-request.php
```

The script sends the same JSON field names to `POST /v1/messages`. Guzzle’s `json` option encodes the payload and sets `Content-Type: application/json`. The client adds `x-api-key` and `anthropic-version` to the request.

The sample sets a five-second connection timeout and a 30-second request timeout. On success, `json_decode()` returns an associative array, so access the content with `$message['content']`. Longer work may need a larger timeout or streaming, which [Chapter 16](/series/claude-php-developers/chapters/16-claude-php-sdk) covers.

## Parsing Responses

An API response can contain several content blocks. Collect blocks whose `type` is `text` and inspect `stop_reason` to learn why generation ended.

### Extracting JSON from Responses

```bash
php examples/response-parsing.php
```

The script asks for one JSON object, collects the text blocks, and strips a surrounding Markdown fence if present. It checks that the decoded object contains two string fields. Successful output looks like this.

```text
Composer: Manages dependencies in a PHP project.
```

The model can still produce malformed JSON or a different shape. Catch `JsonException`, validate fields before use, and check for truncation when a request reaches its output limit. Use Claude’s [structured outputs](https://platform.claude.com/docs/en/build-with-claude/structured-outputs) when your application needs a schema guarantee.

### Working with a Response in Your Application

Keep API-specific fields at the boundary. A service can return its own response value with just the fields callers need, such as the text, model, stop reason, and token counts. Use the returned model and input and output token counts with their separate per-million-token prices to estimate cost. Features such as prompt caching may add separate usage and pricing fields. [Claude pricing](https://platform.claude.com/docs/en/about-claude/pricing)



The community SDK exposes the status and request ID on `ClaudePhp\Exceptions\APIStatusError`. Guzzle raises a `RequestException` for HTTP errors. Record the status and request ID for diagnosis. Keep API keys and request content out of shared logs.

| Result | What to check |
| --- | --- |
| `400 Bad Request` | Verify parameter names, message shape, and support for model-specific options. |
| `401 Unauthorized` | Check the key in `.env` or the shell environment. |
| `403 Forbidden` or `404 Not Found` | Check account access, the model ID, and the request path. |
| `429 Too Many Requests` | Respect `retry-after` when supplied and reduce request concurrency. |
| Timeout, `500`, or `529` | Check the network or service status. For transient failures, retry with capped backoff and jitter. |

A request that times out may have reached Claude. Before retrying automatically, account for possible duplicate work or charges. For rate limits, start with the API’s [rate-limit guide](https://platform.claude.com/docs/en/api/rate-limits).

### Request Debugging and HTTP Configuration

For diagnostics, log the HTTP status, the response’s `request-id` header when present, and elapsed time. Avoid logging API keys, complete prompts, or user data. Use a shared client for requests in the same process so its transport can reuse connections.

You can pass a configured Guzzle client as the community SDK’s `httpClient`. This lets you choose timeouts and supply a mock client in tests.

```php
<?php

declare(strict_types=1);

use ClaudePhp\ClaudePhp;
use GuzzleHttp\Client;

$config = require __DIR__ . '/../bootstrap.php';
$httpClient = new Client(['connect_timeout' => 5.0, 'timeout' => 30.0]);
$claude = new ClaudePhp(apiKey: $config['apiKey'], httpClient: $httpClient);
```

For short requests, set a timeout that fits your application’s response budget. For longer generation, use streaming and account for partial output if the connection closes.

## Exercises

1. Add a system instruction to change the answer style, then compare output token counts.
2. Send a two-turn conversation using the same SDK call and return only the new answer.
3. Try a response with an unexpected JSON field type and verify that your validation rejects it.

## Next Steps

Continue with [Chapter 04: Understanding Messages and Conversations](/series/claude-php-developers/chapters/04-messages-conversations) to build multi-turn requests.
