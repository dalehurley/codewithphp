---
title: "06: Streaming Responses in PHP"
description: "Master real-time streaming with Claude API. Build responsive chatbots using Server-Sent Events, handle partial responses, and create fluid user experiences."
series: "claude-php-developers"
chapter: 6
order: 6
difficulty: "Expert"
prerequisites:
  - "Chapter 00-05 completed"
  - "Understanding of HTTP streaming"
  - "Basic knowledge of async PHP patterns"
---

![06: Streaming Responses in PHP](/images/claude-php/chapter-06-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 06</span>
</div>

# Chapter 06: Streaming Responses in PHP

## Overview

Waiting 5-10 seconds for Claude to finish generating a response creates a poor user experience. Streaming responses solve this by sending tokens as they're generated, creating fluid, ChatGPT-like interfaces where users see text appear word-by-word in real-time.

This chapter teaches you to implement streaming responses in PHP using Server-Sent Events (SSE), handle partial responses gracefully, build production-ready streaming chatbots, and manage the unique challenges of PHP's request-response model in streaming contexts.

By the end, you'll build a complete streaming chatbot interface with proper error handling, reconnection logic, and state management.

## Prerequisites

Before starting, ensure you understand:

- ✓ Basic Claude API usage (Chapters 00-03)
- ✓ HTTP request/response lifecycle
- ✓ JavaScript fetch API and EventSource
- ✓ PHP output buffering concepts

**Estimated Time**: 45-60 minutes

## Why Streaming Matters

### The User Experience Problem

```php
<?php
# filename: examples/01-blocking-request.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

// Non-streaming: User waits 5-10 seconds with no feedback
$startTime = microtime(true);

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 2048,
    'messages' => [[
        'role' => 'user',
        'content' => 'Explain the SOLID principles with PHP examples for each principle.'
    ]]
]);

$duration = microtime(true) - $startTime;

echo "Response received after {$duration} seconds:\n\n";
echo $response->content[0]->text;
```

**Output:**
```
Response received after 8.3 seconds:

The SOLID principles are...
```

The user waited 8+ seconds staring at a loading spinner. With streaming, they'd see text appearing immediately.

### Streaming Benefits

1. **Perceived Performance** — Users see progress immediately
2. **Better UX** — Interactive feel like ChatGPT
3. **Cancellable** — Users can stop long responses
4. **Progressive Rendering** — Display partial results while generating

## Understanding Server-Sent Events (SSE)

SSE is a standard protocol for server-to-client streaming over HTTP.

### SSE vs WebSockets

| Feature | SSE | WebSockets |
|---------|-----|------------|
| **Direction** | Server → Client only | Bidirectional |
| **Protocol** | HTTP | Separate protocol |
| **Reconnection** | Automatic | Manual |
| **Complexity** | Simple | Complex |
| **Use Case** | Real-time updates | Two-way communication |

For Claude streaming, **SSE is perfect** because we only need server-to-client data flow.

### SSE Message Format

```
data: {"type": "message_start"}\n\n
data: {"type": "content_block_delta", "delta": {"text": "Hello"}}\n\n
data: {"type": "content_block_delta", "delta": {"text": " world"}}\n\n
data: {"type": "message_stop"}\n\n
```

Each message:
- Starts with `data: `
- Contains JSON payload
- Ends with double newline `\n\n`

## Basic Streaming Implementation

### Server-Side: Streaming Endpoint

```php
<?php
# filename: examples/02-streaming-endpoint.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

// Set SSE headers
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Disable nginx buffering

// Disable PHP output buffering
if (ob_get_level()) {
    ob_end_clean();
}

// Get user message from POST
$input = json_decode(file_get_contents('php://input'), true);
$userMessage = $input['message'] ?? 'Hello, Claude!';

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

try {
    // Create streaming request
    $stream = $client->messages()->createStreamed([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 1024,
        'messages' => [[
            'role' => 'user',
            'content' => $userMessage
        ]]
    ]);

    // Process each chunk as it arrives
    foreach ($stream as $event) {
        $data = json_encode($event);

        // Send SSE message
        echo "data: {$data}\n\n";

        // Flush output immediately
        if (ob_get_level()) {
            ob_flush();
        }
        flush();
    }

    // Send completion signal
    echo "data: {\"type\": \"done\"}\n\n";
    flush();

} catch (\Exception $e) {
    $error = json_encode([
        'type' => 'error',
        'message' => $e->getMessage()
    ]);
    echo "data: {$error}\n\n";
    flush();
}
```

### Client-Side: EventSource Consumer

```html
<!-- filename: examples/02-streaming-client.html -->
<!DOCTYPE html>
<html>
<head>
    <title>Claude Streaming Chat</title>
    <style>
        body { font-family: system-ui; max-width: 800px; margin: 40px auto; padding: 20px; }
        #messages { height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 20px; margin-bottom: 20px; }
        #input { width: 80%; padding: 10px; font-size: 16px; }
        #send { padding: 10px 20px; font-size: 16px; }
        .message { margin-bottom: 20px; }
        .user { color: #0066cc; font-weight: bold; }
        .assistant { color: #16a34a; font-weight: bold; }
        .loading { color: #999; font-style: italic; }
    </style>
</head>
<body>
    <h1>Claude Streaming Chat</h1>

    <div id="messages"></div>

    <input type="text" id="input" placeholder="Ask Claude anything...">
    <button id="send">Send</button>

    <script>
        const messagesDiv = document.getElementById('messages');
        const input = document.getElementById('input');
        const sendBtn = document.getElementById('send');

        let currentAssistantMessage = null;

        function addUserMessage(text) {
            const div = document.createElement('div');
            div.className = 'message';
            div.innerHTML = `<span class="user">You:</span> ${text}`;
            messagesDiv.appendChild(div);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }

        function addAssistantMessage() {
            currentAssistantMessage = document.createElement('div');
            currentAssistantMessage.className = 'message';
            currentAssistantMessage.innerHTML = '<span class="assistant">Claude:</span> <span class="content"></span>';
            messagesDiv.appendChild(currentAssistantMessage);
            return currentAssistantMessage.querySelector('.content');
        }

        function sendMessage() {
            const message = input.value.trim();
            if (!message) return;

            addUserMessage(message);
            input.value = '';
            sendBtn.disabled = true;

            const contentSpan = addAssistantMessage();
            let fullText = '';

            // Create EventSource for streaming
            fetch('02-streaming-endpoint.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message })
            }).then(response => {
                const reader = response.body.getReader();
                const decoder = new TextDecoder();

                function read() {
                    reader.read().then(({ done, value }) => {
                        if (done) {
                            sendBtn.disabled = false;
                            return;
                        }

                        const chunk = decoder.decode(value, { stream: true });
                        const lines = chunk.split('\n');

                        lines.forEach(line => {
                            if (line.startsWith('data: ')) {
                                const data = JSON.parse(line.substring(6));

                                if (data.type === 'content_block_delta') {
                                    fullText += data.delta.text;
                                    contentSpan.textContent = fullText;
                                    messagesDiv.scrollTop = messagesDiv.scrollHeight;
                                }

                                if (data.type === 'done') {
                                    sendBtn.disabled = false;
                                }

                                if (data.type === 'error') {
                                    contentSpan.textContent = 'Error: ' + data.message;
                                    contentSpan.style.color = 'red';
                                    sendBtn.disabled = false;
                                }
                            }
                        });

                        read();
                    });
                }

                read();
            });
        }

        sendBtn.addEventListener('click', sendMessage);
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });
    </script>
</body>
</html>
```

## Production Streaming Implementation

### Stream Event Handler Class

```php
<?php
# filename: src/StreamEventHandler.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

class StreamEventHandler
{
    private string $currentText = '';
    private array $metadata = [];
    private bool $isComplete = false;

    public function __construct(
        private ?\Closure $onText = null,
        private ?\Closure $onComplete = null,
        private ?\Closure $onError = null,
    ) {}

    public function handleEvent(object $event): void
    {
        match ($event->type) {
            'message_start' => $this->handleMessageStart($event),
            'content_block_start' => $this->handleContentBlockStart($event),
            'content_block_delta' => $this->handleContentDelta($event),
            'content_block_stop' => $this->handleContentBlockStop($event),
            'message_delta' => $this->handleMessageDelta($event),
            'message_stop' => $this->handleMessageStop($event),
            'error' => $this->handleError($event),
            default => null,
        };
    }

    private function handleMessageStart(object $event): void
    {
        $this->metadata = [
            'id' => $event->message->id,
            'model' => $event->message->model,
            'role' => $event->message->role,
        ];
    }

    private function handleContentBlockStart(object $event): void
    {
        // Content block starting, prepare to receive text
    }

    private function handleContentDelta(object $event): void
    {
        $delta = $event->delta->text ?? '';
        $this->currentText .= $delta;

        if ($this->onText) {
            ($this->onText)($delta, $this->currentText);
        }
    }

    private function handleContentBlockStop(object $event): void
    {
        // Content block complete
    }

    private function handleMessageDelta(object $event): void
    {
        if (isset($event->usage)) {
            $this->metadata['usage'] = [
                'output_tokens' => $event->usage->outputTokens ?? 0,
            ];
        }

        if (isset($event->delta->stopReason)) {
            $this->metadata['stop_reason'] = $event->delta->stopReason;
        }
    }

    private function handleMessageStop(object $event): void
    {
        $this->isComplete = true;

        if ($this->onComplete) {
            ($this->onComplete)($this->currentText, $this->metadata);
        }
    }

    private function handleError(object $event): void
    {
        if ($this->onError) {
            ($this->onError)($event->error ?? 'Unknown error');
        }
    }

    public function getText(): string
    {
        return $this->currentText;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function isComplete(): bool
    {
        return $this->isComplete;
    }
}
```

### Streaming Service Class

```php
<?php
# filename: src/StreamingService.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

use Anthropic\Anthropic;
use Anthropic\Contracts\ClientContract;

class StreamingService
{
    private ClientContract $client;

    public function __construct(?string $apiKey = null)
    {
        $this->client = Anthropic::factory()
            ->withApiKey($apiKey ?? getenv('ANTHROPIC_API_KEY'))
            ->make();
    }

    public function stream(
        string $message,
        ?string $systemPrompt = null,
        array $options = []
    ): StreamEventHandler {
        $handler = new StreamEventHandler(
            onText: $options['onText'] ?? null,
            onComplete: $options['onComplete'] ?? null,
            onError: $options['onError'] ?? null,
        );

        $config = [
            'model' => $options['model'] ?? 'claude-sonnet-4-20250514',
            'max_tokens' => $options['max_tokens'] ?? 2048,
            'messages' => [[
                'role' => 'user',
                'content' => $message
            ]],
        ];

        if ($systemPrompt) {
            $config['system'] = $systemPrompt;
        }

        if (isset($options['temperature'])) {
            $config['temperature'] = $options['temperature'];
        }

        try {
            $stream = $this->client->messages()->createStreamed($config);

            foreach ($stream as $event) {
                $handler->handleEvent($event);
            }
        } catch (\Exception $e) {
            if ($handler->onError ?? null) {
                ($handler->onError)($e->getMessage());
            }
            throw $e;
        }

        return $handler;
    }

    public function streamToSSE(
        string $message,
        ?string $systemPrompt = null,
        array $options = []
    ): void {
        // Set SSE headers
        $this->sendSSEHeaders();

        $this->stream(
            message: $message,
            systemPrompt: $systemPrompt,
            options: array_merge($options, [
                'onText' => function (string $delta, string $fullText) {
                    $this->sendSSEMessage([
                        'type' => 'delta',
                        'text' => $delta,
                        'fullText' => $fullText,
                    ]);
                },
                'onComplete' => function (string $text, array $metadata) {
                    $this->sendSSEMessage([
                        'type' => 'complete',
                        'text' => $text,
                        'metadata' => $metadata,
                    ]);
                    $this->sendSSEMessage(['type' => 'done']);
                },
                'onError' => function (string $error) {
                    $this->sendSSEMessage([
                        'type' => 'error',
                        'message' => $error,
                    ]);
                },
            ])
        );
    }

    private function sendSSEHeaders(): void
    {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        if (ob_get_level()) {
            ob_end_clean();
        }
    }

    private function sendSSEMessage(array $data): void
    {
        echo "data: " . json_encode($data) . "\n\n";

        if (ob_get_level()) {
            ob_flush();
        }
        flush();
    }
}
```

### Using the Streaming Service

```php
<?php
# filename: examples/03-streaming-service-endpoint.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use CodeWithPHP\Claude\StreamingService;

// Get input
$input = json_decode(file_get_contents('php://input'), true);
$message = $input['message'] ?? 'Hello!';
$systemPrompt = $input['system'] ?? null;

// Stream to SSE
$service = new StreamingService();

$service->streamToSSE(
    message: $message,
    systemPrompt: $systemPrompt,
    options: [
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 4096,
        'temperature' => 1.0,
    ]
);
```

## Advanced Streaming Patterns

### Pattern 1: Conversational History with Streaming

```php
<?php
# filename: examples/04-conversational-streaming.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

class ConversationalStreamingService extends StreamingService
{
    private array $conversationHistory = [];

    public function streamWithHistory(
        string $message,
        ?string $systemPrompt = null,
        array $options = []
    ): StreamEventHandler {
        // Add user message to history
        $this->conversationHistory[] = [
            'role' => 'user',
            'content' => $message
        ];

        $handler = new StreamEventHandler(
            onText: $options['onText'] ?? null,
            onComplete: function (string $text, array $metadata) use ($options) {
                // Add assistant response to history
                $this->conversationHistory[] = [
                    'role' => 'assistant',
                    'content' => $text
                ];

                if ($options['onComplete'] ?? null) {
                    ($options['onComplete'])($text, $metadata);
                }
            },
            onError: $options['onError'] ?? null,
        );

        $config = [
            'model' => $options['model'] ?? 'claude-sonnet-4-20250514',
            'max_tokens' => $options['max_tokens'] ?? 2048,
            'messages' => $this->conversationHistory,
        ];

        if ($systemPrompt) {
            $config['system'] = $systemPrompt;
        }

        $stream = $this->client->messages()->createStreamed($config);

        foreach ($stream as $event) {
            $handler->handleEvent($event);
        }

        return $handler;
    }

    public function getHistory(): array
    {
        return $this->conversationHistory;
    }

    public function clearHistory(): void
    {
        $this->conversationHistory = [];
    }

    public function loadHistory(array $history): void
    {
        $this->conversationHistory = $history;
    }
}
```

### Pattern 2: Streaming with Token Budget

```php
<?php
# filename: examples/05-streaming-with-budget.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

class BudgetedStreamingService extends StreamingService
{
    private float $budgetUSD;
    private float $spentUSD = 0.0;
    private array $pricing = [
        'claude-sonnet-4-20250514' => [
            'input' => 3.00,   // per 1M tokens
            'output' => 15.00, // per 1M tokens
        ],
        'claude-haiku-4-20250514' => [
            'input' => 0.80,
            'output' => 4.00,
        ],
    ];

    public function __construct(?string $apiKey = null, float $budgetUSD = 10.0)
    {
        parent::__construct($apiKey);
        $this->budgetUSD = $budgetUSD;
    }

    public function stream(
        string $message,
        ?string $systemPrompt = null,
        array $options = []
    ): StreamEventHandler {
        if ($this->spentUSD >= $this->budgetUSD) {
            throw new \RuntimeException("Budget exceeded: \${$this->budgetUSD} limit reached");
        }

        $model = $options['model'] ?? 'claude-sonnet-4-20250514';

        $originalOnComplete = $options['onComplete'] ?? null;
        $options['onComplete'] = function (string $text, array $metadata) use ($originalOnComplete, $model) {
            // Calculate cost
            $usage = $metadata['usage'] ?? [];
            $inputTokens = $metadata['input_tokens'] ?? 0;
            $outputTokens = $usage['output_tokens'] ?? 0;

            $pricing = $this->pricing[$model] ?? $this->pricing['claude-sonnet-4-20250514'];

            $cost = (
                ($inputTokens / 1_000_000) * $pricing['input'] +
                ($outputTokens / 1_000_000) * $pricing['output']
            );

            $this->spentUSD += $cost;

            $metadata['cost'] = $cost;
            $metadata['budget_spent'] = $this->spentUSD;
            $metadata['budget_remaining'] = $this->budgetUSD - $this->spentUSD;

            if ($originalOnComplete) {
                $originalOnComplete($text, $metadata);
            }
        };

        return parent::stream($message, $systemPrompt, $options);
    }

    public function getSpent(): float
    {
        return $this->spentUSD;
    }

    public function getRemaining(): float
    {
        return max(0, $this->budgetUSD - $this->spentUSD);
    }

    public function resetBudget(float $newBudgetUSD): void
    {
        $this->budgetUSD = $newBudgetUSD;
        $this->spentUSD = 0.0;
    }
}
```

### Pattern 3: Streaming with Progress Callbacks

```php
<?php
# filename: examples/06-streaming-with-progress.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use CodeWithPHP\Claude\StreamingService;

$service = new StreamingService();

$startTime = microtime(true);
$wordCount = 0;
$charCount = 0;

$service->streamToSSE(
    message: 'Write a comprehensive guide to PHP 8.2 features',
    options: [
        'max_tokens' => 4096,
        'onText' => function (string $delta, string $fullText) use (&$wordCount, &$charCount, $startTime) {
            $charCount = strlen($fullText);
            $wordCount = str_word_count($fullText);
            $elapsed = microtime(true) - $startTime;
            $charsPerSecond = $elapsed > 0 ? $charCount / $elapsed : 0;

            // Send progress update
            echo "data: " . json_encode([
                'type' => 'progress',
                'wordCount' => $wordCount,
                'charCount' => $charCount,
                'elapsed' => round($elapsed, 2),
                'speed' => round($charsPerSecond, 0),
            ]) . "\n\n";
            flush();
        },
    ]
);
```

## Handling Streaming Edge Cases

### Reconnection Logic

```javascript
// filename: examples/07-reconnection-client.js

class RobustStreamingClient {
    constructor(endpoint) {
        this.endpoint = endpoint;
        this.maxRetries = 3;
        this.retryDelay = 1000; // Start with 1 second
        this.currentRetry = 0;
    }

    async sendMessage(message, callbacks) {
        const { onText, onComplete, onError } = callbacks;
        let fullText = '';
        let lastEventId = null;

        const attemptStream = async () => {
            try {
                const response = await fetch(this.endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        ...(lastEventId ? { 'Last-Event-ID': lastEventId } : {})
                    },
                    body: JSON.stringify({ message })
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const reader = response.body.getReader();
                const decoder = new TextDecoder();

                const read = async () => {
                    const { done, value } = await reader.read();

                    if (done) {
                        this.currentRetry = 0; // Reset on success
                        return;
                    }

                    const chunk = decoder.decode(value, { stream: true });
                    const lines = chunk.split('\n');

                    for (const line of lines) {
                        if (line.startsWith('data: ')) {
                            const data = JSON.parse(line.substring(6));

                            if (data.id) {
                                lastEventId = data.id;
                            }

                            if (data.type === 'delta') {
                                fullText += data.text;
                                onText?.(data.text, fullText);
                            }

                            if (data.type === 'complete') {
                                onComplete?.(data.text, data.metadata);
                            }

                            if (data.type === 'error') {
                                throw new Error(data.message);
                            }
                        }
                    }

                    await read();
                };

                await read();

            } catch (error) {
                if (this.currentRetry < this.maxRetries) {
                    this.currentRetry++;
                    const delay = this.retryDelay * Math.pow(2, this.currentRetry - 1);

                    console.warn(`Stream failed, retrying in ${delay}ms (attempt ${this.currentRetry}/${this.maxRetries})`);

                    await new Promise(resolve => setTimeout(resolve, delay));
                    await attemptStream();
                } else {
                    onError?.(error.message);
                    throw error;
                }
            }
        };

        await attemptStream();
    }
}

// Usage
const client = new RobustStreamingClient('stream.php');

client.sendMessage('Explain async PHP', {
    onText: (delta, fullText) => {
        document.getElementById('output').textContent = fullText;
    },
    onComplete: (text, metadata) => {
        console.log('Stream complete:', metadata);
    },
    onError: (error) => {
        console.error('Stream failed:', error);
        document.getElementById('output').textContent = 'Error: ' + error;
    }
});
```

### Timeout Handling

```php
<?php
# filename: examples/08-streaming-with-timeout.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

class TimeoutAwareStreamingService extends StreamingService
{
    public function streamWithTimeout(
        string $message,
        int $timeoutSeconds = 30,
        ?string $systemPrompt = null,
        array $options = []
    ): StreamEventHandler {
        $startTime = time();

        $originalOnText = $options['onText'] ?? null;
        $options['onText'] = function (string $delta, string $fullText) use ($originalOnText, $startTime, $timeoutSeconds) {
            // Check timeout
            if (time() - $startTime > $timeoutSeconds) {
                throw new \RuntimeException("Stream timeout after {$timeoutSeconds} seconds");
            }

            if ($originalOnText) {
                $originalOnText($delta, $fullText);
            }
        };

        return $this->stream($message, $systemPrompt, $options);
    }
}
```

### Partial Response Recovery

```php
<?php
# filename: examples/09-partial-response-recovery.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

class RecoverableStreamingService extends StreamingService
{
    private string $cacheDir;

    public function __construct(?string $apiKey = null, ?string $cacheDir = null)
    {
        parent::__construct($apiKey);
        $this->cacheDir = $cacheDir ?? sys_get_temp_dir() . '/claude-streams';

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    public function streamWithRecovery(
        string $requestId,
        string $message,
        ?string $systemPrompt = null,
        array $options = []
    ): StreamEventHandler {
        $cachePath = $this->cacheDir . '/' . $requestId . '.txt';
        $metadataPath = $this->cacheDir . '/' . $requestId . '.json';

        // Check for partial response
        $recoveredText = '';
        if (file_exists($cachePath)) {
            $recoveredText = file_get_contents($cachePath);

            // If we have a complete response, return it
            if (file_exists($metadataPath)) {
                $metadata = json_decode(file_get_contents($metadataPath), true);
                if ($metadata['complete'] ?? false) {
                    $handler = new StreamEventHandler();
                    // Simulate complete response
                    if ($options['onComplete'] ?? null) {
                        ($options['onComplete'])($recoveredText, $metadata);
                    }
                    return $handler;
                }
            }
        }

        // Wrap callbacks to save progress
        $originalOnText = $options['onText'] ?? null;
        $options['onText'] = function (string $delta, string $fullText) use ($originalOnText, $cachePath) {
            // Save progress
            file_put_contents($cachePath, $fullText);

            if ($originalOnText) {
                $originalOnText($delta, $fullText);
            }
        };

        $originalOnComplete = $options['onComplete'] ?? null;
        $options['onComplete'] = function (string $text, array $metadata) use ($originalOnComplete, $metadataPath) {
            // Mark as complete
            $metadata['complete'] = true;
            file_put_contents($metadataPath, json_encode($metadata));

            if ($originalOnComplete) {
                $originalOnComplete($text, $metadata);
            }
        };

        $originalOnError = $options['onError'] ?? null;
        $options['onError'] = function (string $error) use ($originalOnError, $cachePath, $metadataPath) {
            // Save error state
            if (file_exists($cachePath)) {
                $metadata = [
                    'complete' => false,
                    'error' => $error,
                    'timestamp' => time(),
                ];
                file_put_contents($metadataPath, json_encode($metadata));
            }

            if ($originalOnError) {
                $originalOnError($error);
            }
        };

        return $this->stream($message, $systemPrompt, $options);
    }

    public function clearCache(string $requestId): void
    {
        @unlink($this->cacheDir . '/' . $requestId . '.txt');
        @unlink($this->cacheDir . '/' . $requestId . '.json');
    }
}
```

## Complete Streaming Chatbot

### Backend: Complete Chat API

```php
<?php
# filename: examples/10-complete-chatbot-api.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use CodeWithPHP\Claude\ConversationalStreamingService;

session_start();

// Initialize or restore conversation
if (!isset($_SESSION['chat_service'])) {
    $_SESSION['chat_service'] = serialize(new ConversationalStreamingService());
}

$service = unserialize($_SESSION['chat_service']);

// Get request data
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? 'message';

switch ($action) {
    case 'message':
        $message = $input['message'] ?? '';
        $systemPrompt = $input['system'] ?? 'You are a helpful PHP programming assistant.';

        $service->streamToSSE(
            message: $message,
            systemPrompt: $systemPrompt,
            options: [
                'model' => 'claude-sonnet-4-20250514',
                'max_tokens' => 4096,
            ]
        );

        // Save updated service with conversation history
        $_SESSION['chat_service'] = serialize($service);
        break;

    case 'history':
        header('Content-Type: application/json');
        echo json_encode([
            'history' => $service->getHistory()
        ]);
        break;

    case 'clear':
        $service->clearHistory();
        $_SESSION['chat_service'] = serialize($service);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'cleared']);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}
```

### Frontend: Production Chat Interface

```html
<!-- filename: examples/10-complete-chatbot.html -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claude PHP Chat</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header {
            background: #2563eb;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 1.5rem;
        }

        .header button {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .header button:hover {
            background: rgba(255,255,255,0.3);
        }

        .chat-container {
            flex: 1;
            overflow-y: auto;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .message {
            display: flex;
            gap: 1rem;
            max-width: 80%;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.user {
            align-self: flex-end;
            flex-direction: row-reverse;
        }

        .message.assistant {
            align-self: flex-start;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            flex-shrink: 0;
        }

        .message.user .avatar {
            background: #2563eb;
        }

        .message.assistant .avatar {
            background: #16a34a;
        }

        .message-content {
            background: white;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            line-height: 1.6;
        }

        .message.user .message-content {
            background: #2563eb;
            color: white;
        }

        .typing-indicator {
            display: none;
            gap: 4px;
            padding: 1rem;
        }

        .typing-indicator.active {
            display: flex;
        }

        .typing-indicator span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #94a3b8;
            animation: bounce 1.4s infinite ease-in-out;
        }

        .typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
        .typing-indicator span:nth-child(2) { animation-delay: -0.16s; }

        @keyframes bounce {
            0%, 80%, 100% { transform: scale(0); }
            40% { transform: scale(1); }
        }

        .input-container {
            background: white;
            padding: 1rem 2rem;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 1rem;
        }

        .input-container textarea {
            flex: 1;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 0.75rem;
            font-size: 1rem;
            font-family: inherit;
            resize: none;
            max-height: 120px;
        }

        .input-container textarea:focus {
            outline: none;
            border-color: #2563eb;
        }

        .input-container button {
            background: #2563eb;
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
        }

        .input-container button:hover:not(:disabled) {
            background: #1d4ed8;
        }

        .input-container button:disabled {
            background: #94a3b8;
            cursor: not-allowed;
        }

        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 2rem;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Claude PHP Chat</h1>
        <button id="clearBtn">Clear Chat</button>
    </div>

    <div class="chat-container" id="chatContainer">
        <div class="message assistant">
            <div class="avatar">C</div>
            <div class="message-content">
                Hello! I'm Claude, your PHP programming assistant. How can I help you today?
            </div>
        </div>
    </div>

    <div class="input-container">
        <textarea
            id="messageInput"
            placeholder="Ask me anything about PHP..."
            rows="1"
        ></textarea>
        <button id="sendBtn">Send</button>
    </div>

    <script>
        const chatContainer = document.getElementById('chatContainer');
        const messageInput = document.getElementById('messageInput');
        const sendBtn = document.getElementById('sendBtn');
        const clearBtn = document.getElementById('clearBtn');

        let isStreaming = false;
        let currentAssistantMessage = null;

        // Auto-resize textarea
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });

        // Send on Enter, new line on Shift+Enter
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        sendBtn.addEventListener('click', sendMessage);
        clearBtn.addEventListener('click', clearChat);

        function addUserMessage(text) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message user';
            messageDiv.innerHTML = `
                <div class="avatar">U</div>
                <div class="message-content">${escapeHtml(text)}</div>
            `;
            chatContainer.appendChild(messageDiv);
            scrollToBottom();
        }

        function createAssistantMessage() {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message assistant';
            messageDiv.innerHTML = `
                <div class="avatar">C</div>
                <div class="message-content"></div>
            `;
            chatContainer.appendChild(messageDiv);
            currentAssistantMessage = messageDiv.querySelector('.message-content');
            scrollToBottom();
            return currentAssistantMessage;
        }

        function addTypingIndicator() {
            const indicator = document.createElement('div');
            indicator.className = 'typing-indicator active';
            indicator.id = 'typingIndicator';
            indicator.innerHTML = '<span></span><span></span><span></span>';
            chatContainer.appendChild(indicator);
            scrollToBottom();
        }

        function removeTypingIndicator() {
            const indicator = document.getElementById('typingIndicator');
            if (indicator) {
                indicator.remove();
            }
        }

        function showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.textContent = 'Error: ' + message;
            chatContainer.insertBefore(errorDiv, chatContainer.firstChild);
            setTimeout(() => errorDiv.remove(), 5000);
        }

        function scrollToBottom() {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        async function sendMessage() {
            const message = messageInput.value.trim();
            if (!message || isStreaming) return;

            isStreaming = true;
            sendBtn.disabled = true;
            messageInput.value = '';
            messageInput.style.height = 'auto';

            addUserMessage(message);
            addTypingIndicator();

            try {
                const response = await fetch('10-complete-chatbot-api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'message',
                        message: message
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                removeTypingIndicator();
                const contentDiv = createAssistantMessage();
                let fullText = '';

                const reader = response.body.getReader();
                const decoder = new TextDecoder();

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;

                    const chunk = decoder.decode(value, { stream: true });
                    const lines = chunk.split('\n');

                    for (const line of lines) {
                        if (line.startsWith('data: ')) {
                            const data = JSON.parse(line.substring(6));

                            if (data.type === 'delta') {
                                fullText += data.text;
                                contentDiv.textContent = fullText;
                                scrollToBottom();
                            }

                            if (data.type === 'complete') {
                                console.log('Stream complete:', data.metadata);
                            }

                            if (data.type === 'error') {
                                throw new Error(data.message);
                            }
                        }
                    }
                }

            } catch (error) {
                console.error('Stream error:', error);
                removeTypingIndicator();
                showError(error.message);
            } finally {
                isStreaming = false;
                sendBtn.disabled = false;
                messageInput.focus();
            }
        }

        async function clearChat() {
            if (!confirm('Clear entire conversation history?')) return;

            try {
                await fetch('10-complete-chatbot-api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'clear' })
                });

                // Clear UI
                chatContainer.innerHTML = `
                    <div class="message assistant">
                        <div class="avatar">C</div>
                        <div class="message-content">
                            Chat cleared! How can I help you?
                        </div>
                    </div>
                `;
            } catch (error) {
                showError('Failed to clear chat: ' + error.message);
            }
        }

        // Focus input on load
        messageInput.focus();
    </script>
</body>
</html>
```

## Performance Optimization

### Chunked Transfer Encoding

```php
<?php
# filename: examples/11-chunked-transfer.php
declare(strict_types=1);

// Enable chunked transfer encoding
header('Transfer-Encoding: chunked');
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

// Disable all buffering
while (ob_get_level()) {
    ob_end_clean();
}

function sendChunk(string $data): void
{
    $chunk = "data: {$data}\n\n";
    $length = strlen($chunk);

    // Send chunk size in hex, followed by chunk data
    echo dechex($length) . "\r\n";
    echo $chunk . "\r\n";
    flush();
}

// Your streaming logic here
sendChunk(json_encode(['type' => 'start']));
// ...
sendChunk(json_encode(['type' => 'end']));

// Send terminating chunk
echo "0\r\n\r\n";
```

### Connection Keep-Alive

```php
<?php
# filename: examples/12-keepalive.php
declare(strict_types=1);

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');

// Send keepalive comments every 15 seconds
$lastKeepalive = time();

foreach ($stream as $event) {
    // Send event
    echo "data: " . json_encode($event) . "\n\n";
    flush();

    // Send keepalive if needed
    if (time() - $lastKeepalive > 15) {
        echo ": keepalive\n\n";
        flush();
        $lastKeepalive = time();
    }
}
```

## Troubleshooting

### Issue: No Streaming Output

**Problem:** Response arrives all at once, not streamed.

**Solutions:**
```php
// 1. Disable all output buffering
while (ob_get_level()) {
    ob_end_clean();
}

// 2. Set correct headers
header('X-Accel-Buffering: no'); // Nginx
header('Content-Type: text/event-stream');

// 3. Flush after each write
echo "data: ...\n\n";
flush();

// 4. Check Apache config
// Add to .htaccess:
// <IfModule mod_deflate.c>
//   SetEnvIfNoCase Request_URI ^/stream\.php$ no-gzip dont-vary
// </IfModule>
```

### Issue: Premature Connection Close

**Problem:** Stream stops unexpectedly.

**Solutions:**
```php
// 1. Increase PHP timeout
set_time_limit(300); // 5 minutes

// 2. Ignore client disconnect
ignore_user_abort(true);

// 3. Keep connection alive
register_shutdown_function(function() {
    // Cleanup on shutdown
});

// 4. Send periodic keepalive
```

### Issue: High Memory Usage

**Problem:** Memory increases during long streams.

**Solutions:**
```php
// 1. Process events immediately, don't accumulate
foreach ($stream as $event) {
    handleEvent($event);
    unset($event); // Free memory
}

// 2. Don't store full text in memory
// Use file-backed storage for long responses

// 3. Set memory limit
ini_set('memory_limit', '128M');
```

## Exercises

### Exercise 1: Multi-User Chat Room

Build a streaming chat room where multiple users can see messages in real-time:

**Requirements:**
- Multiple users can connect simultaneously
- Messages stream to all connected users
- Show typing indicators
- Display user join/leave notifications

**Hints:**
- Use file-based or Redis pub/sub for message distribution
- Each user gets their own SSE endpoint
- Implement long-polling or WebSocket fallback

### Exercise 2: Streaming Code Generator

Create a tool that streams generated code with syntax highlighting:

**Requirements:**
- Generate multi-file code projects
- Stream each file as it's generated
- Apply syntax highlighting on-the-fly
- Show progress (current file, completion %)

**Hints:**
- Parse code blocks from Claude's response
- Use a PHP syntax highlighter library
- Send metadata about current file being generated

### Exercise 3: Streaming Document Processor

Build a document analysis tool that streams results:

**Requirements:**
- Upload PDF/text documents
- Stream analysis results (summary, key points, entities)
- Show progress bar based on document length
- Allow user to stop processing mid-stream

**Hints:**
- Split document into chunks
- Process each chunk with Claude
- Aggregate results as they arrive
- Implement cancellation token pattern

<details>
<summary>Solution Hints</summary>

For Exercise 1, use Redis pub/sub or file-based message queue. For Exercise 2, detect code fence markers in streaming text and apply highlighting incrementally. For Exercise 3, chunk documents and track progress, implementing a session-based cancellation flag.

</details>

## Key Takeaways

- ✓ Streaming improves perceived performance dramatically
- ✓ Server-Sent Events (SSE) is ideal for Claude API streaming
- ✓ Proper buffering configuration is critical for PHP streaming
- ✓ Always implement reconnection logic on the client side
- ✓ Track partial responses for recovery from failures
- ✓ Use chunked transfer encoding for better performance
- ✓ Test streaming behavior across different web servers (Apache, Nginx)

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="06"
  label="You've mastered streaming responses in PHP!"
/>

---

Continue to [Chapter 07: System Prompts and Role Definition](/series/claude-php-developers/chapters/07-system-prompts-roles) to learn about shaping Claude's personality and behavior.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 06 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-06)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-06
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php -S localhost:8000
# Open http://localhost:8000/10-complete-chatbot.html
```
