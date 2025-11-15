---
title: Quick Reference - Claude for PHP Developers
description: Essential syntax, patterns, and examples for rapid development with Claude AI and PHP
---

# Claude for PHP Quick Reference

> A concise, printable reference for working with Claude AI in PHP applications.

## 📋 Table of Contents

1. [Basic Setup](#basic-setup)
2. [Common API Calls](#common-api-calls)
3. [Model Selection](#model-selection)
4. [Prompt Patterns](#prompt-patterns)
5. [Tool Use](#tool-use)
6. [Error Handling](#error-handling)
7. [Cost Optimization](#cost-optimization)
8. [Common Patterns](#common-patterns)

---

## Basic Setup

### Installation
```bash
composer require anthropic-ai/sdk
```

### Initialize Client
```php
use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();
```

### Environment Variables
```bash
ANTHROPIC_API_KEY=sk-ant-your-key-here
ANTHROPIC_MODEL=claude-sonnet-4-20250514
ANTHROPIC_MAX_TOKENS=4096
```

---

## Common API Calls

### Basic Request
```php
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'Your prompt here']
    ]
]);

$text = $response->content[0]->text;
```

### With System Prompt
```php
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'system' => 'You are a helpful PHP expert.',
    'messages' => [
        ['role' => 'user', 'content' => 'Explain dependency injection']
    ]
]);
```

### Multi-turn Conversation
```php
$messages = [
    ['role' => 'user', 'content' => 'What is Laravel?'],
    ['role' => 'assistant', 'content' => 'Laravel is a PHP framework...'],
    ['role' => 'user', 'content' => 'How do I install it?']
];

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => $messages
]);
```

### Streaming Response
```php
$stream = $client->messages()->createStreamed([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'Tell me a story']
    ]
]);

foreach ($stream as $event) {
    if ($event->type === 'content_block_delta') {
        echo $event->delta->text;
    }
}
```

---

## Model Selection

| Model | Speed | Cost (Input/Output per 1M tokens) | Best For |
|-------|-------|-----------------------------------|----------|
| **claude-haiku-4-20250514** | Fastest | $0.25 / $1.25 | Simple tasks, high volume |
| **claude-sonnet-4-20250514** | Balanced | $3.00 / $15.00 | Most use cases (recommended) |
| **claude-opus-4-20250514** | Slowest | $15.00 / $75.00 | Complex reasoning, critical tasks |

### When to Use Each

**Haiku** (Fastest, Cheapest):
- Simple data extraction
- Classification tasks
- High-volume processing
- Speed is critical

**Sonnet** (Balanced):
- General-purpose tasks
- Code generation
- Content creation
- Most production use cases

**Opus** (Most Capable):
- Complex analysis
- Critical decisions
- Legal/medical content
- Research and reasoning

---

## Prompt Patterns

### Code Generation
```php
$prompt = "Write a PHP function that:
1. [Specific requirement]
2. [Another requirement]
3. [Edge case handling]

Requirements:
- Use PHP 8.2+ features
- Include type hints
- Add PHPDoc comments
- Handle errors gracefully";
```

### Code Review
```php
$prompt = "Review this PHP code for:
1. Security vulnerabilities
2. Performance issues
3. Best practices violations
4. Potential bugs

```php
{$code}
```

Provide specific, actionable feedback.";
```

### Data Extraction (JSON)
```php
$prompt = "Extract structured data from this text and return as JSON:

{$text}

Return JSON with these fields:
- name (string)
- email (string or null)
- phone (string or null)
- company (string or null)

Return ONLY valid JSON, no explanation.";
```

### Classification
```php
$prompt = "Classify this text into ONE category:

Categories: [Bug, Feature Request, Question, Spam]

Text: {$text}

Return ONLY the category name, nothing else.";
```

### Content Moderation
```php
$prompt = "Analyze this content for policy violations:

Content: {$content}

Check for:
- Toxic language
- Personal information (PII)
- Spam
- Offensive content

Return JSON:
{
  \"is_safe\": boolean,
  \"violations\": [array of violation types],
  \"confidence\": number (0-1)
}";
```

---

## Tool Use

### Define Tool
```php
$tools = [[
    'name' => 'get_weather',
    'description' => 'Get current weather for a city',
    'input_schema' => [
        'type' => 'object',
        'properties' => [
            'city' => [
                'type' => 'string',
                'description' => 'City name'
            ]
        ],
        'required' => ['city']
    ]
]];
```

### Use Tool
```php
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'tools' => $tools,
    'messages' => [
        ['role' => 'user', 'content' => 'What is the weather in Paris?']
    ]
]);

// Check if Claude wants to use a tool
if ($response->stopReason === 'tool_use') {
    $toolUse = $response->content[1]; // content[0] is text, [1] is tool_use

    if ($toolUse->name === 'get_weather') {
        $city = $toolUse->input['city'];
        $weather = getWeather($city); // Your function

        // Send result back to Claude
        $finalResponse = $client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 1024,
            'tools' => $tools,
            'messages' => [
                ['role' => 'user', 'content' => 'What is the weather in Paris?'],
                ['role' => 'assistant', 'content' => $response->content],
                [
                    'role' => 'user',
                    'content' => [[
                        'type' => 'tool_result',
                        'tool_use_id' => $toolUse->id,
                        'content' => json_encode($weather)
                    ]]
                ]
            ]
        ]);
    }
}
```

---

## Error Handling

### Basic Try-Catch
```php
use Anthropic\Exceptions\ErrorException;
use Anthropic\Exceptions\RateLimitException;

try {
    $response = $client->messages()->create([...]);
} catch (RateLimitException $e) {
    // Rate limit hit - wait and retry
    sleep(60);
    $response = $client->messages()->create([...]);
} catch (ErrorException $e) {
    // API error
    logger()->error('Claude API error', [
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
} catch (\Exception $e) {
    // Unexpected error
    logger()->error('Unexpected error', [
        'message' => $e->getMessage()
    ]);
}
```

### Retry with Exponential Backoff
```php
function callClaudeWithRetry(callable $fn, int $maxRetries = 3): mixed
{
    $attempt = 0;

    while ($attempt < $maxRetries) {
        try {
            return $fn();
        } catch (RateLimitException $e) {
            $attempt++;
            if ($attempt >= $maxRetries) {
                throw $e;
            }
            $delay = (2 ** $attempt) + rand(0, 1000) / 1000;
            sleep($delay);
        }
    }
}

$response = callClaudeWithRetry(fn() =>
    $client->messages()->create([...])
);
```

---

## Cost Optimization

### Calculate Cost
```php
function calculateCost(object $response, string $model): float
{
    $pricing = [
        'claude-haiku-4-20250514' => ['input' => 0.25, 'output' => 1.25],
        'claude-sonnet-4-20250514' => ['input' => 3.00, 'output' => 15.00],
        'claude-opus-4-20250514' => ['input' => 15.00, 'output' => 75.00],
    ];

    $inputCost = ($response->usage->inputTokens / 1_000_000)
                 * $pricing[$model]['input'];
    $outputCost = ($response->usage->outputTokens / 1_000_000)
                  * $pricing[$model]['output'];

    return $inputCost + $outputCost;
}

$cost = calculateCost($response, 'claude-sonnet-4-20250514');
echo "Cost: $" . number_format($cost, 6);
```

### Cost-Saving Tips

1. **Use Haiku for simple tasks**
   ```php
   // For classification, simple extraction
   'model' => 'claude-haiku-4-20250514'
   ```

2. **Compress prompts**
   ```php
   // Instead of verbose prompts, use abbreviations
   $prompt = "Classify: [categories]\nText: {$text}\nReturn: category only";
   ```

3. **Cache responses**
   ```php
   $cacheKey = 'claude:' . md5($prompt);
   if ($cached = cache()->get($cacheKey)) {
       return $cached;
   }

   $response = $client->messages()->create([...]);
   cache()->put($cacheKey, $response, 3600);
   ```

4. **Limit max_tokens**
   ```php
   'max_tokens' => 500  // Instead of 4096 when you only need short responses
   ```

5. **Batch requests**
   ```php
   // Process 100 items in one request instead of 100 separate requests
   $prompt = "Process these items:\n" . implode("\n", $items);
   ```

---

## Common Patterns

### Laravel Service
```php
namespace App\Services;

use Anthropic\Anthropic;

class ClaudeService
{
    public function __construct(
        private readonly Anthropic $client
    ) {}

    public function generate(string $prompt, array $options = []): string
    {
        $response = $this->client->messages()->create([
            'model' => $options['model'] ?? config('claude.model'),
            'max_tokens' => $options['max_tokens'] ?? 1024,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        return $response->content[0]->text;
    }
}
```

### Conversation Manager
```php
class ConversationManager
{
    private array $messages = [];

    public function addUserMessage(string $content): self
    {
        $this->messages[] = ['role' => 'user', 'content' => $content];
        return $this;
    }

    public function addAssistantMessage(string $content): self
    {
        $this->messages[] = ['role' => 'assistant', 'content' => $content];
        return $this;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function trimToTokenLimit(int $limit = 100000): self
    {
        // Keep last N messages that fit within token limit
        // Simplified - you'd need actual token counting
        while (count($this->messages) > 10) {
            array_shift($this->messages);
        }
        return $this;
    }
}
```

### Structured Output Parser
```php
function extractJSON(string $response): ?array
{
    // Try to extract JSON from response (may be wrapped in markdown)
    if (preg_match('/```json\s*(\{.*?\})\s*```/s', $response, $matches)) {
        return json_decode($matches[1], true);
    }

    if (preg_match('/(\{.*?\})/s', $response, $matches)) {
        return json_decode($matches[1], true);
    }

    return null;
}
```

---

## Environment Setup

### .env Example
```bash
ANTHROPIC_API_KEY=sk-ant-api03-xxx
ANTHROPIC_MODEL=claude-sonnet-4-20250514
ANTHROPIC_MAX_TOKENS=4096
ANTHROPIC_TEMPERATURE=1.0

# Optional
CLAUDE_CACHE_TTL=3600
CLAUDE_RETRY_ATTEMPTS=3
CLAUDE_TIMEOUT=120
```

### composer.json
```json
{
    "require": {
        "php": "^8.2",
        "anthropic-ai/sdk": "^1.0",
        "vlucas/phpdotenv": "^5.5",
        "predis/predis": "^2.0"
    }
}
```

---

## Debugging

### Enable Logging
```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$log = new Logger('claude');
$log->pushHandler(new StreamHandler('claude.log', Logger::DEBUG));

// Log request
$log->debug('Claude request', ['prompt' => $prompt]);

// Log response
$log->debug('Claude response', [
    'text' => $response->content[0]->text,
    'tokens' => $response->usage
]);
```

### Inspect Response
```php
var_dump([
    'id' => $response->id,
    'model' => $response->model,
    'role' => $response->role,
    'content' => $response->content,
    'stop_reason' => $response->stopReason,
    'usage' => [
        'input_tokens' => $response->usage->inputTokens,
        'output_tokens' => $response->usage->outputTokens
    ]
]);
```

---

## Links

- **Full Series**: [Claude for PHP Developers](index.md)
- **API Docs**: [Anthropic Documentation](https://docs.anthropic.com)
- **SDK**: [Anthropic PHP SDK](https://github.com/anthropics/anthropic-sdk-php)
- **Console**: [console.anthropic.com](https://console.anthropic.com)

---

**Print this reference and keep it handy while developing!** 📄
