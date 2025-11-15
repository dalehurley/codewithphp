---
title: "09: Streaming Responses"
description: "Implement real-time streaming responses using Server-Sent Events for responsive AI applications"
series: "openai-php"
chapter: 9
order: 9
difficulty: "Intermediate"
prerequisites:
  - "/series/openai-php/chapters/08-prompt-engineering-essentials"
  - "Understanding of async programming"
---

![Streaming Responses](/images/openai-php/chapter-09-streaming-hero-full.webp)

[Home](/series/openai-php) > [Chapter 08](/series/openai-php/chapters/08-prompt-engineering-essentials) > Streaming Responses

# Chapter 09: Streaming Responses

<span class="difficulty-badge difficulty-intermediate">Intermediate</span>
<span class="time-badge">55-65 minutes</span>

## Overview

Imagine waiting 30 seconds for an AI to finish generating a long response while staring at a blank screen. Now imagine seeing the response appear word-by-word as it's generated, like a human typing. That's the power of streaming responses—they transform user experience from frustrating waits to engaging interactions.

Streaming is essential for modern AI applications. It provides immediate feedback, reduces perceived latency, and enables real-time interactions. In this chapter, you'll master Server-Sent Events (SSE), implement streaming in PHP, handle partial responses, and build responsive UIs.

## What You'll Learn

- 🌊 **Server-Sent Events**: Understand SSE protocol and implementation
- ⚡ **Real-Time Processing**: Handle streaming data as it arrives
- 🎨 **UI Integration**: Build responsive frontends for streaming
- 🛡️ **Error Handling**: Manage stream interruptions and failures
- 📊 **Partial Responses**: Process incomplete data chunks
- 🔧 **Performance**: Optimize streaming for production

## Prerequisites

- ✅ Completed Chapters 01-08
- ✅ Understanding of HTTP streaming
- ✅ Basic JavaScript knowledge
- ✅ Familiarity with event-driven programming

---

## Understanding Server-Sent Events

### SSE Basics

Server-Sent Events (SSE) allow servers to push data to clients over HTTP:

```php
<?php

/**
 * Basic SSE implementation
 */

class SSEStreamer
{
    public function startStream(): void
    {
        // Set headers for SSE
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Disable nginx buffering

        // Prevent output buffering
        if (ob_get_level()) {
            ob_end_clean();
        }
    }

    public function sendEvent(string $event, array $data): void
    {
        echo "event: {$event}\n";
        echo "data: " . json_encode($data) . "\n\n";
        flush();
    }

    public function sendMessage(string $message): void
    {
        echo "data: {$message}\n\n";
        flush();
    }

    public function sendError(string $error): void
    {
        $this->sendEvent('error', ['message' => $error]);
    }

    public function close(): void
    {
        $this->sendEvent('close', ['status' => 'complete']);
    }
}
```

---

## Implementing Streaming with OpenAI

### Basic Streaming Request

```php
<?php

/**
 * Stream OpenAI chat completions
 */

class StreamingChat
{
    private \OpenAI\Client $client;

    public function __construct(string $apiKey)
    {
        $this->client = \OpenAI::client($apiKey);
    }

    public function streamResponse(array $messages): void
    {
        $streamer = new SSEStreamer();
        $streamer->startStream();

        try {
            $stream = $this->client->chat()->createStreamed([
                'model' => 'gpt-3.5-turbo',
                'messages' => $messages,
                'stream' => true,
            ]);

            $fullResponse = '';

            foreach ($stream as $chunk) {
                $content = $chunk->choices[0]->delta->content ?? '';

                if ($content) {
                    $fullResponse .= $content;

                    $streamer->sendEvent('chunk', [
                        'content' => $content,
                        'accumulated' => $fullResponse,
                    ]);
                }

                // Check for finish
                if (($chunk->choices[0]->finishReason ?? null) !== null) {
                    $streamer->sendEvent('complete', [
                        'finish_reason' => $chunk->choices[0]->finishReason,
                        'full_response' => $fullResponse,
                    ]);
                }
            }

            $streamer->close();

        } catch (\Exception $e) {
            $streamer->sendError($e->getMessage());
        }
    }
}

// Usage in endpoint (stream.php)
require 'vendor/autoload.php';

$messages = json_decode($_POST['messages'] ?? '[]', true);

$chat = new StreamingChat($_ENV['OPENAI_API_KEY']);
$chat->streamResponse($messages);
```

### Advanced Streaming with Progress

```php
<?php

class AdvancedStreamingChat
{
    private \OpenAI\Client $client;
    private SSEStreamer $streamer;
    private int $tokenCount = 0;

    public function __construct(string $apiKey)
    {
        $this->client = \OpenAI::client($apiKey);
        $this->streamer = new SSEStreamer();
    }

    public function stream(array $messages, array $options = []): void
    {
        $this->streamer->startStream();

        // Send start event
        $this->streamer->sendEvent('start', [
            'timestamp' => time(),
            'model' => $options['model'] ?? 'gpt-3.5-turbo',
        ]);

        $fullResponse = '';
        $startTime = microtime(true);

        try {
            $stream = $this->client->chat()->createStreamed(array_merge([
                'model' => 'gpt-3.5-turbo',
                'messages' => $messages,
                'stream' => true,
            ], $options));

            foreach ($stream as $chunk) {
                $delta = $chunk->choices[0]->delta ?? null;

                if ($delta && isset($delta->content)) {
                    $content = $delta->content;
                    $fullResponse .= $content;
                    $this->tokenCount += $this->estimateTokens($content);

                    // Send chunk with metadata
                    $this->streamer->sendEvent('chunk', [
                        'content' => $content,
                        'tokens' => $this->tokenCount,
                        'elapsed_ms' => round((microtime(true) - $startTime) * 1000),
                    ]);
                }

                // Handle finish
                $finishReason = $chunk->choices[0]->finishReason ?? null;
                if ($finishReason) {
                    $duration = microtime(true) - $startTime;

                    $this->streamer->sendEvent('complete', [
                        'finish_reason' => $finishReason,
                        'total_tokens' => $this->tokenCount,
                        'duration_seconds' => round($duration, 2),
                        'full_response' => $fullResponse,
                    ]);
                }
            }

        } catch (\Exception $e) {
            $this->streamer->sendError($e->getMessage());
        } finally {
            $this->streamer->close();
        }
    }

    private function estimateTokens(string $text): int
    {
        // Rough estimate: ~4 chars per token
        return (int) ceil(strlen($text) / 4);
    }
}
```

---

## Frontend Integration

### JavaScript EventSource Client

```html
<!DOCTYPE html>
<html>
<head>
    <title>Streaming Chat</title>
    <style>
        #response {
            white-space: pre-wrap;
            font-family: monospace;
            padding: 20px;
            background: #f5f5f5;
            min-height: 200px;
        }
        .metadata {
            color: #666;
            font-size: 0.9em;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>Streaming Chat Demo</h1>

    <div>
        <textarea id="prompt" rows="4" cols="50" placeholder="Enter your question..."></textarea>
        <br>
        <button onclick="sendMessage()">Send</button>
    </div>

    <div id="response"></div>
    <div class="metadata" id="metadata"></div>

    <script>
        let eventSource = null;

        function sendMessage() {
            const prompt = document.getElementById('prompt').value;
            const responseDiv = document.getElementById('response');
            const metadataDiv = document.getElementById('metadata');

            // Clear previous response
            responseDiv.textContent = '';
            metadataDiv.textContent = '';

            // Close existing connection
            if (eventSource) {
                eventSource.close();
            }

            // Create form data
            const formData = new FormData();
            formData.append('messages', JSON.stringify([
                { role: 'user', content: prompt }
            ]));

            // Start streaming
            eventSource = new EventSource('/stream.php');

            eventSource.addEventListener('start', (e) => {
                const data = JSON.parse(e.data);
                console.log('Stream started:', data);
            });

            eventSource.addEventListener('chunk', (e) => {
                const data = JSON.parse(e.data);
                responseDiv.textContent += data.content;

                // Update metadata
                metadataDiv.textContent = `Tokens: ${data.tokens} | Time: ${data.elapsed_ms}ms`;
            });

            eventSource.addEventListener('complete', (e) => {
                const data = JSON.parse(e.data);
                console.log('Stream complete:', data);

                metadataDiv.textContent += ` | Total: ${data.total_tokens} tokens in ${data.duration_seconds}s`;
            });

            eventSource.addEventListener('error', (e) => {
                const data = JSON.parse(e.data);
                console.error('Stream error:', data.message);
                responseDiv.textContent += '\n\n[Error: ' + data.message + ']';
                eventSource.close();
            });

            eventSource.addEventListener('close', (e) => {
                eventSource.close();
            });

            eventSource.onerror = (error) => {
                console.error('EventSource failed:', error);
                eventSource.close();
            };
        }
    </script>
</body>
</html>
```

### React Hook for Streaming

```javascript
import { useState, useEffect, useRef } from 'react';

function useStreamingChat(apiUrl) {
    const [response, setResponse] = useState('');
    const [isStreaming, setIsStreaming] = useState(false);
    const [error, setError] = useState(null);
    const [metadata, setMetadata] = useState(null);
    const eventSourceRef = useRef(null);

    const sendMessage = (message) => {
        setResponse('');
        setError(null);
        setIsStreaming(true);

        // Close existing connection
        if (eventSourceRef.current) {
            eventSourceRef.current.close();
        }

        // Encode message in URL
        const url = `${apiUrl}?message=${encodeURIComponent(message)}`;
        eventSourceRef.current = new EventSource(url);

        eventSourceRef.current.addEventListener('chunk', (e) => {
            const data = JSON.parse(e.data);
            setResponse(prev => prev + data.content);
            setMetadata(data);
        });

        eventSourceRef.current.addEventListener('complete', (e) => {
            const data = JSON.parse(e.data);
            setMetadata(data);
            setIsStreaming(false);
            eventSourceRef.current.close();
        });

        eventSourceRef.current.addEventListener('error', (e) => {
            const data = JSON.parse(e.data);
            setError(data.message);
            setIsStreaming(false);
            eventSourceRef.current.close();
        });

        eventSourceRef.current.onerror = () => {
            setError('Connection failed');
            setIsStreaming(false);
            eventSourceRef.current.close();
        };
    };

    useEffect(() => {
        return () => {
            if (eventSourceRef.current) {
                eventSourceRef.current.close();
            }
        };
    }, []);

    return { response, isStreaming, error, metadata, sendMessage };
}

// Usage in component
function ChatComponent() {
    const { response, isStreaming, error, sendMessage } = useStreamingChat('/stream.php');
    const [input, setInput] = useState('');

    return (
        <div>
            <textarea
                value={input}
                onChange={(e) => setInput(e.target.value)}
                placeholder="Ask a question..."
            />
            <button
                onClick={() => sendMessage(input)}
                disabled={isStreaming}
            >
                {isStreaming ? 'Streaming...' : 'Send'}
            </button>

            <div className="response">
                {response}
                {isStreaming && <span className="cursor">▊</span>}
            </div>

            {error && <div className="error">{error}</div>}
        </div>
    );
}
```

---

## Error Handling

```php
<?php

/**
 * Robust streaming with error recovery
 */

class RobustStreamingChat
{
    private \OpenAI\Client $client;
    private SSEStreamer $streamer;
    private int $maxRetries = 3;

    public function __construct(string $apiKey)
    {
        $this->client = \OpenAI::client($apiKey);
        $this->streamer = new SSEStreamer();
    }

    public function stream(array $messages): void
    {
        $this->streamer->startStream();
        $attempt = 0;

        while ($attempt < $this->maxRetries) {
            try {
                $this->attemptStream($messages);
                return;

            } catch (\Exception $e) {
                $attempt++;

                if ($attempt < $this->maxRetries) {
                    $this->streamer->sendEvent('retry', [
                        'attempt' => $attempt,
                        'error' => $e->getMessage(),
                        'retrying_in' => 2,
                    ]);

                    sleep(2);
                } else {
                    $this->streamer->sendError(
                        "Failed after {$attempt} attempts: " . $e->getMessage()
                    );
                    break;
                }
            }
        }
    }

    private function attemptStream(array $messages): void
    {
        $stream = $this->client->chat()->createStreamed([
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            'stream' => true,
        ]);

        $fullResponse = '';
        $lastChunkTime = time();
        $timeout = 30; // seconds

        foreach ($stream as $chunk) {
            // Check for timeout
            if (time() - $lastChunkTime > $timeout) {
                throw new \RuntimeException('Stream timeout');
            }

            $content = $chunk->choices[0]->delta->content ?? '';

            if ($content) {
                $fullResponse .= $content;
                $this->streamer->sendEvent('chunk', ['content' => $content]);
                $lastChunkTime = time();
            }

            if (($chunk->choices[0]->finishReason ?? null) !== null) {
                $this->streamer->sendEvent('complete', [
                    'finish_reason' => $chunk->choices[0]->finishReason,
                    'full_response' => $fullResponse,
                ]);
                return;
            }
        }
    }
}
```

---

## Performance Optimization

```php
<?php

/**
 * Optimized streaming with batching
 */

class OptimizedStreamingChat
{
    private \OpenAI\Client $client;
    private SSEStreamer $streamer;
    private int $batchSize = 10; // Characters to batch before sending

    public function __construct(string $apiKey)
    {
        $this->client = \OpenAI::client($apiKey);
        $this->streamer = new SSEStreamer();
    }

    public function stream(array $messages): void
    {
        $this->streamer->startStream();

        $stream = $this->client->chat()->createStreamed([
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            'stream' => true,
        ]);

        $buffer = '';
        $fullResponse = '';

        foreach ($stream as $chunk) {
            $content = $chunk->choices[0]->delta->content ?? '';

            if ($content) {
                $buffer .= $content;
                $fullResponse .= $content;

                // Send when buffer reaches batch size
                if (strlen($buffer) >= $this->batchSize) {
                    $this->streamer->sendEvent('chunk', ['content' => $buffer]);
                    $buffer = '';
                }
            }

            // Handle completion
            if (($chunk->choices[0]->finishReason ?? null) !== null) {
                // Send any remaining buffer
                if ($buffer) {
                    $this->streamer->sendEvent('chunk', ['content' => $buffer]);
                }

                $this->streamer->sendEvent('complete', [
                    'finish_reason' => $chunk->choices[0]->finishReason,
                    'full_response' => $fullResponse,
                ]);
            }
        }

        $this->streamer->close();
    }
}
```

---

## Exercises

### Exercise 1: Typing Animation

Create a frontend that:
1. Displays text with typing effect
2. Shows cursor animation
3. Plays typing sounds
4. Handles backspace/corrections

### Exercise 2: Multi-User Streaming

Build a system that:
1. Streams to multiple users simultaneously
2. Manages separate sessions
3. Tracks active streams
4. Handles cleanup

### Exercise 3: Progress Indicators

Implement:
1. Progress bar based on estimated length
2. Token counter
3. Time remaining estimate
4. Visual feedback for stream status

### Exercise 4: Stream Recording

Create functionality to:
1. Record all streamed responses
2. Replay streams
3. Export as text/JSON
4. Search through recorded streams

---

## Key Takeaways

- ✅ Streaming dramatically improves perceived performance
- ✅ Server-Sent Events provide simple, effective streaming
- ✅ Proper error handling is critical for stream reliability
- ✅ Batching chunks can reduce overhead
- ✅ Frontend integration requires EventSource API
- ✅ Timeouts prevent hung connections
- ✅ Stream state management needs careful attention

---

## Next Steps

👉 **[Chapter 10: Temperature, Top-P & Sampling Parameters](/series/openai-php/chapters/10-temperature-top-p-sampling-parameters)**

---

[← Previous: Chapter 08](/series/openai-php/chapters/08-prompt-engineering-essentials) | [Next: Chapter 10 →](/series/openai-php/chapters/10-temperature-top-p-sampling-parameters)
