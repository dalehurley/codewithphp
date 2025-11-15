---
title: "Appendix E: Migration Guides"
description: "Guides for upgrading between API versions, migrating from other providers, and handling breaking changes"
series: "openai-php"
appendix: "E"
---

# Appendix E: Migration Guides

Comprehensive guides for upgrading APIs, migrating between providers, and handling breaking changes.

## Table of Contents

1. [OpenAI API Version Migrations](#openai-api-version-migrations)
2. [Chat Completions vs Assistants API](#chat-completions-vs-assistants-api)
3. [Migrating from Other AI Providers](#migrating-from-other-ai-providers)
4. [PHP Version Upgrades](#php-version-upgrades)
5. [Breaking Changes Reference](#breaking-changes-reference)

---

## OpenAI API Version Migrations

### Legacy Completions API to Chat Completions API

The old Completions API (`/v1/completions`) has been deprecated. Migrate to Chat Completions API (`/v1/chat/completions`).

**Before (Legacy Completions):**

```php
$response = $client->completions()->create([
    'model' => 'text-davinci-003',
    'prompt' => 'Explain quantum computing',
    'max_tokens' => 100,
    'temperature' => 0.7,
]);

$text = $response->choices[0]->text;
```

**After (Chat Completions):**

```php
$response = $client->chat()->create([
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        ['role' => 'user', 'content' => 'Explain quantum computing']
    ],
    'max_tokens' => 100,
    'temperature' => 0.7,
]);

$text = $response->choices[0]->message->content;
```

**Key Changes:**
- Use `messages` array instead of `prompt` string
- Response is in `message->content` not `text`
- Different model names (`gpt-3.5-turbo` instead of `text-davinci-003`)
- Better cost-to-performance ratio with new models

**Migration Helper Function:**

```php
function migrateToChat(array $legacyParams): array
{
    $chatParams = [
        'model' => match($legacyParams['model'] ?? '') {
            'text-davinci-003' => 'gpt-3.5-turbo',
            'text-davinci-002' => 'gpt-3.5-turbo',
            'text-curie-001' => 'gpt-3.5-turbo',
            default => 'gpt-3.5-turbo',
        },
        'messages' => [
            ['role' => 'user', 'content' => $legacyParams['prompt']]
        ],
    ];

    // Copy compatible parameters
    foreach (['temperature', 'max_tokens', 'top_p', 'frequency_penalty', 'presence_penalty'] as $key) {
        if (isset($legacyParams[$key])) {
            $chatParams[$key] = $legacyParams[$key];
        }
    }

    return $chatParams;
}

// Usage
$chatParams = migrateToChat($legacyCompletionParams);
$response = $client->chat()->create($chatParams);
```

---

### Function Calling: v1 to v2 (Tools)

OpenAI introduced a new "tools" format for function calling.

**Before (Functions):**

```php
$response = $client->chat()->create([
    'model' => 'gpt-3.5-turbo',
    'messages' => $messages,
    'functions' => [
        [
            'name' => 'get_weather',
            'description' => 'Get current weather',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'location' => ['type' => 'string'],
                ],
                'required' => ['location'],
            ],
        ],
    ],
    'function_call' => 'auto',
]);

// Check for function call
if ($response->choices[0]->message->function_call) {
    $functionCall = $response->choices[0]->message->function_call;
    $result = callFunction($functionCall->name, json_decode($functionCall->arguments, true));
}
```

**After (Tools):**

```php
$response = $client->chat()->create([
    'model' => 'gpt-3.5-turbo',
    'messages' => $messages,
    'tools' => [
        [
            'type' => 'function',
            'function' => [
                'name' => 'get_weather',
                'description' => 'Get current weather',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'location' => ['type' => 'string'],
                    ],
                    'required' => ['location'],
                ],
            ],
        ],
    ],
    'tool_choice' => 'auto',
]);

// Check for tool calls (can be multiple!)
if ($response->choices[0]->message->tool_calls) {
    foreach ($response->choices[0]->message->tool_calls as $toolCall) {
        if ($toolCall->type === 'function') {
            $result = callFunction(
                $toolCall->function->name,
                json_decode($toolCall->function->arguments, true)
            );
        }
    }
}
```

**Key Differences:**
- `functions` → `tools` with `type: 'function'`
- `function_call` → `tool_choice`
- `message->function_call` → `message->tool_calls` (array, supports multiple!)
- Each tool call has an `id` for tracking
- Supports parallel function calling

**Backward Compatible Wrapper:**

```php
class FunctionCallAdapter
{
    public function convertFunctionsToTools(array $functions): array
    {
        return array_map(fn($f) => [
            'type' => 'function',
            'function' => $f
        ], $functions);
    }

    public function extractFunctionCalls($message): array
    {
        // Handle both old and new format
        if (isset($message->function_call)) {
            return [[
                'id' => uniqid(),
                'type' => 'function',
                'function' => $message->function_call
            ]];
        }

        return $message->tool_calls ?? [];
    }
}
```

---

## Chat Completions vs Assistants API

Deciding when to migrate from Chat Completions to Assistants API.

### When to Use Chat Completions

✅ Simple conversations
✅ Stateless interactions
✅ Full control over context
✅ Lower latency requirements
✅ Simple function calling

### When to Use Assistants API

✅ Multi-turn conversations with state
✅ Need Code Interpreter or File Search
✅ Complex document analysis
✅ Thread-based conversations
✅ Built-in memory management

### Migration Example

**Before (Chat Completions with Manual State):**

```php
class ChatbotWithManualState
{
    private array $conversationHistory = [];

    public function chat(string $userMessage): string
    {
        // Add user message
        $this->conversationHistory[] = [
            'role' => 'user',
            'content' => $userMessage
        ];

        // Call API
        $response = $client->chat()->create([
            'model' => 'gpt-4',
            'messages' => $this->conversationHistory,
        ]);

        // Save assistant response
        $assistantMessage = $response->choices[0]->message->content;
        $this->conversationHistory[] = [
            'role' => 'assistant',
            'content' => $assistantMessage
        ];

        // Manage context window manually
        if (count($this->conversationHistory) > 20) {
            array_shift($this->conversationHistory); // Remove oldest
        }

        return $assistantMessage;
    }
}
```

**After (Assistants API with Built-in State):**

```php
class ChatbotWithAssistants
{
    private string $assistantId;
    private string $threadId;

    public function __construct(OpenAIClient $client)
    {
        // Create assistant once
        $this->assistantId = $client->assistants()->create([
            'model' => 'gpt-4',
            'name' => 'Customer Support Bot',
            'instructions' => 'You are a helpful customer support agent.',
        ])->id;

        // Create thread for this conversation
        $this->threadId = $client->threads()->create()->id;
    }

    public function chat(string $userMessage): string
    {
        // Add message to thread
        $client->threads()->messages()->create($this->threadId, [
            'role' => 'user',
            'content' => $userMessage,
        ]);

        // Run assistant
        $run = $client->threads()->runs()->create($this->threadId, [
            'assistant_id' => $this->assistantId,
        ]);

        // Wait for completion
        while ($run->status === 'in_progress' || $run->status === 'queued') {
            sleep(1);
            $run = $client->threads()->runs()->retrieve($this->threadId, $run->id);
        }

        // Get latest message
        $messages = $client->threads()->messages()->list($this->threadId, ['limit' => 1]);
        return $messages->data[0]->content[0]->text->value;
    }
}
```

**Benefits of Assistants API:**
- Automatic state management
- No manual context window handling
- Easy to add tools (Code Interpreter, File Search)
- Better for long-running conversations

**Tradeoffs:**
- Higher latency (polling required)
- Less control over exact prompt structure
- Additional costs for tools

---

## Migrating from Other AI Providers

### From Azure OpenAI to OpenAI

**Before (Azure):**

```php
$client = OpenAI::factory()
    ->withBaseUri('https://YOUR-RESOURCE.openai.azure.com/openai/deployments/YOUR-DEPLOYMENT')
    ->withHttpHeader('api-key', $azureApiKey)
    ->withQueryParam('api-version', '2023-05-15')
    ->make();
```

**After (OpenAI):**

```php
$client = OpenAI::client($openaiApiKey);
```

**Key Changes:**
- Different authentication (Bearer token vs api-key header)
- No deployment names (use model names directly)
- No API version in query params
- Different endpoint URLs

### From Anthropic Claude to OpenAI

**Before (Anthropic):**

```php
$response = $anthropic->completions()->create([
    'model' => 'claude-3-opus-20240229',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'Hello!']
    ],
]);
```

**After (OpenAI):**

```php
$response = $client->chat()->create([
    'model' => 'gpt-4',
    'messages' => [
        ['role' => 'user', 'content' => 'Hello!']
    ],
    'max_tokens' => 1024,
]);
```

**Main Differences:**
- Temperature defaults (Claude: 1.0, OpenAI: 1.0) - same
- Max tokens (Claude: required, OpenAI: optional)
- System messages (Claude: separate parameter, OpenAI: in messages array)
- Streaming format slightly different

**Adapter Pattern:**

```php
interface LLMProvider
{
    public function chat(array $messages, array $options = []): string;
}

class OpenAIProvider implements LLMProvider
{
    public function chat(array $messages, array $options = []): string
    {
        $response = $this->client->chat()->create([
            'model' => $options['model'] ?? 'gpt-3.5-turbo',
            'messages' => $messages,
            ...$options
        ]);
        return $response->choices[0]->message->content;
    }
}

class AnthropicProvider implements LLMProvider
{
    public function chat(array $messages, array $options = []): string
    {
        // Convert to Anthropic format
        $response = $this->client->completions()->create([
            'model' => $options['model'] ?? 'claude-3-opus-20240229',
            'messages' => $messages,
            'max_tokens' => $options['max_tokens'] ?? 1024,
        ]);
        return $response->content[0]->text;
    }
}

// Easy to switch providers
$provider = new OpenAIProvider($openai); // or new AnthropicProvider($anthropic)
$response = $provider->chat($messages);
```

---

## PHP Version Upgrades

### Migrating from PHP 7.4 to PHP 8.x

**New Features to Adopt:**

1. **Named Arguments**
```php
// PHP 7.4
$response = createChat($model, $messages, 0.7, 100, null, null);

// PHP 8.0+
$response = createChat(
    model: $model,
    messages: $messages,
    temperature: 0.7,
    maxTokens: 100
);
```

2. **Union Types**
```php
// PHP 7.4
/**
 * @param ChatCompletion|Error $response
 */
function handle($response): string { }

// PHP 8.0+
function handle(ChatCompletion|Error $response): string { }
```

3. **Nullsafe Operator**
```php
// PHP 7.4
$content = isset($response['choices'][0]['message']['content'])
    ? $response['choices'][0]['message']['content']
    : null;

// PHP 8.0+
$content = $response?->choices[0]?->message?->content;
```

4. **Match Expression**
```php
// PHP 7.4
switch ($error->type) {
    case 'rate_limit':
        return retry();
    case 'auth_error':
        return reauth();
    default:
        return throw new Exception();
}

// PHP 8.0+
return match ($error->type) {
    'rate_limit' => retry(),
    'auth_error' => reauth(),
    default => throw new Exception(),
};
```

---

## Breaking Changes Reference

### OpenAI API Breaking Changes

**2024-11:**
- Assistants API v2 introduced (replaces v1)
- `gpt-3.5-turbo` now points to newer snapshot
- Legacy Completions API deprecated

**2024-06:**
- New structured outputs feature
- JSON mode improvements
- Function calling updated to tools format

**2023-11:**
- GPT-4 Turbo released
- New token limits (128K context window)
- Vision capabilities added

### Handling Breaking Changes

```php
class ApiVersionManager
{
    private const API_VERSION = '2024-11-15';

    public function createRequest(array $params): array
    {
        // Add version-specific modifications
        if ($this->shouldUseToolsFormat()) {
            $params = $this->convertFunctionsToTools($params);
        }

        // Add version header if needed
        $params['headers']['OpenAI-Version'] = self::API_VERSION;

        return $params;
    }

    private function shouldUseToolsFormat(): bool
    {
        return version_compare(self::API_VERSION, '2023-11-06', '>=');
    }
}
```

---

## Testing Your Migration

### Parallel Testing Strategy

Run old and new implementations side-by-side:

```php
class MigrationTester
{
    public function compareImplementations(string $input): array
    {
        $oldResult = $this->oldImplementation($input);
        $newResult = $this->newImplementation($input);

        return [
            'match' => $this->resultsMatch($oldResult, $newResult),
            'old' => $oldResult,
            'new' => $newResult,
            'diff' => $this->getDifferences($oldResult, $newResult),
        ];
    }

    private function resultsMatch($old, $new): bool
    {
        // Define what "match" means for your use case
        return similar_text($old, $new) > 0.9;
    }
}
```

### Gradual Rollout

```php
class FeatureFlag
{
    public function shouldUseNewAPI(string $userId): bool
    {
        // Gradually roll out to users
        $rolloutPercentage = 10; // Start with 10%

        $hash = crc32($userId);
        return ($hash % 100) < $rolloutPercentage;
    }
}

// Usage
if ($featureFlag->shouldUseNewAPI($userId)) {
    $response = $newImplementation->chat($message);
} else {
    $response = $oldImplementation->chat($message);
}
```

---

## Checklist for Safe Migration

- [ ] Review changelog for breaking changes
- [ ] Update all model names to current versions
- [ ] Test with sample data before production
- [ ] Implement fallback to old version if needed
- [ ] Monitor error rates during migration
- [ ] Update environment variables and configuration
- [ ] Check pricing changes and update budgets
- [ ] Update documentation and code comments
- [ ] Run comprehensive test suite
- [ ] Plan rollback strategy if issues arise

---

**Last Updated**: 2025-11-15

For latest migration guides, check:
- [OpenAI Migration Guide](https://platform.openai.com/docs/guides/migration)
- [OpenAI Changelog](https://platform.openai.com/docs/changelog)
