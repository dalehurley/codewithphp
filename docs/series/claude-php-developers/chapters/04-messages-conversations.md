---
title: "04: Understanding Messages and Conversations"
description: "Master multi-turn conversations with Claude. Learn message formatting, conversation state management, context handling, memory techniques, and building stateful chat applications in PHP."
series: "claude-php-developers"
chapter: 4
order: 4
difficulty: "Intermediate"
prerequisites:
  - "PHP 8.2+ installed"
  - "Completion of Chapters 01-03"
  - "Understanding of arrays and objects in PHP"
---

![04: Understanding Messages and Conversations](/images/claude-php/chapter-04-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 04</span>
</div>

# Chapter 04: Understanding Messages and Conversations

## Overview

Conversations with AI are fundamentally different from traditional request-response APIs. Claude's stateless architecture requires you to maintain and manage conversation history, context, and memory. Understanding how to structure messages and manage multi-turn conversations is essential for building engaging, context-aware applications.

This chapter provides comprehensive coverage of message formatting, building stateful conversation managers, handling context windows efficiently, implementing conversation memory, and creating production-ready chat applications in PHP.

**What You'll Learn:**
- Message structure and formatting rules
- Building multi-turn conversations
- Context window management
- Conversation state persistence
- Memory and history management
- Advanced conversation patterns
- Production-ready chat systems

**Estimated Time**: 40-50 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapters 01-03**
- ✓ **PHP 8.2+** with type declarations knowledge
- ✓ **Anthropic API key** configured
- ✓ **Understanding of sessions** or state management

## Message Structure Fundamentals

### Anatomy of a Message

Every message in a Claude conversation has two required fields:

```php
<?php
$message = [
    'role' => 'user',        // Required: 'user' or 'assistant'
    'content' => 'Hello!'    // Required: string content
];
```

### Valid Message Roles

**User Messages:**
- Represent input from the user/application
- Always start conversations
- Can contain questions, commands, or data

**Assistant Messages:**
- Represent Claude's responses
- Cannot start conversations
- Used to maintain conversation history

```php
<?php
# filename: examples/01-message-roles.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

// ✓ Valid: User message starts
$response1 = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'Hello!']
    ]
]);

// ✓ Valid: Alternating roles
$response2 = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    'messages' => [
        ['role' => 'user', 'content' => 'What is PHP?'],
        ['role' => 'assistant', 'content' => 'PHP is a server-side scripting language...'],
        ['role' => 'user', 'content' => 'Tell me more about its history.']
    ]
]);

// ✗ Invalid: Cannot start with assistant
// This will throw an error
try {
    $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 1024,
        'messages' => [
            ['role' => 'assistant', 'content' => 'Hello!']  // ERROR
        ]
    ]);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

### Message Alternation Rules

Messages must strictly alternate between roles:

```php
<?php
# ✓ Valid patterns
$valid1 = [
    ['role' => 'user', 'content' => 'A'],
];

$valid2 = [
    ['role' => 'user', 'content' => 'A'],
    ['role' => 'assistant', 'content' => 'B'],
    ['role' => 'user', 'content' => 'C'],
];

# ✗ Invalid patterns
$invalid1 = [
    ['role' => 'user', 'content' => 'A'],
    ['role' => 'user', 'content' => 'B'],  // ERROR: Two consecutive user messages
];

$invalid2 = [
    ['role' => 'assistant', 'content' => 'A'],  // ERROR: Starts with assistant
];

$invalid3 = [
    ['role' => 'user', 'content' => 'A'],
    ['role' => 'assistant', 'content' => 'B'],
    ['role' => 'assistant', 'content' => 'C'],  // ERROR: Two consecutive assistant messages
];
```

### Handling Multiple User Inputs

When you need to send multiple pieces of information, combine them:

```php
<?php
# ✗ Wrong: Consecutive user messages
$wrong = [
    ['role' => 'user', 'content' => 'My name is John.'],
    ['role' => 'user', 'content' => 'I am a developer.'],  // ERROR
];

# ✓ Correct: Combine into single message
$correct = [
    ['role' => 'user', 'content' => "My name is John.\n\nI am a developer."]
];

# ✓ Also correct: Use explicit formatting
$alsoCorrect = [
    ['role' => 'user', 'content' => implode("\n\n", [
        'My name is John.',
        'I am a developer.',
        'I work with PHP.',
    ])]
];
```

## Building Multi-Turn Conversations

### Basic Conversation Manager

```php
<?php
# filename: src/Conversation/BasicConversationManager.php
declare(strict_types=1);

namespace App\Conversation;

use Anthropic\Anthropic;

class BasicConversationManager
{
    private array $messages = [];

    public function __construct(
        private readonly Anthropic $client,
        private readonly string $model = 'claude-sonnet-4-20250514',
        private readonly int $maxTokens = 2048
    ) {}

    public function sendMessage(string $userMessage): string
    {
        // Add user message
        $this->messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];

        // Get response
        $response = $this->client->messages()->create([
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'messages' => $this->messages
        ]);

        $assistantMessage = $response->content[0]->text;

        // Add assistant response to history
        $this->messages[] = [
            'role' => 'assistant',
            'content' => $assistantMessage
        ];

        return $assistantMessage;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function clearHistory(): void
    {
        $this->messages = [];
    }

    public function getMessageCount(): int
    {
        return count($this->messages);
    }
}
```

**Usage:**

```php
<?php
# filename: examples/02-basic-conversation.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Conversation\BasicConversationManager;
use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$conversation = new BasicConversationManager($client);

// Turn 1
echo "You: Hello! What's your name?\n";
$reply1 = $conversation->sendMessage("Hello! What's your name?");
echo "Claude: {$reply1}\n\n";

// Turn 2
echo "You: Can you help me with PHP?\n";
$reply2 = $conversation->sendMessage("Can you help me with PHP?");
echo "Claude: {$reply2}\n\n";

// Turn 3
echo "You: What did I just ask you about?\n";
$reply3 = $conversation->sendMessage("What did I just ask you about?");
echo "Claude: {$reply3}\n\n";

// Claude remembers the conversation context!
```

### Advanced Conversation Manager with System Prompt

```php
<?php
# filename: src/Conversation/AdvancedConversationManager.php
declare(strict_types=1);

namespace App\Conversation;

use Anthropic\Anthropic;

class AdvancedConversationManager
{
    private array $messages = [];
    private ?string $systemPrompt;
    private array $metadata = [];

    public function __construct(
        private readonly Anthropic $client,
        private readonly string $model = 'claude-sonnet-4-20250514',
        private readonly int $maxTokens = 2048,
        private readonly float $temperature = 1.0,
        ?string $systemPrompt = null
    ) {
        $this->systemPrompt = $systemPrompt;
    }

    public function sendMessage(
        string $userMessage,
        ?array $additionalParams = null
    ): array {
        // Add user message
        $this->messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];

        // Build request parameters
        $params = [
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
            'messages' => $this->messages,
        ];

        // Add system prompt if set
        if ($this->systemPrompt !== null) {
            $params['system'] = $this->systemPrompt;
        }

        // Merge additional parameters
        if ($additionalParams) {
            $params = array_merge($params, $additionalParams);
        }

        // Make request
        $response = $this->client->messages()->create($params);

        $assistantMessage = $response->content[0]->text;

        // Add assistant response to history
        $this->messages[] = [
            'role' => 'assistant',
            'content' => $assistantMessage
        ];

        // Store metadata
        $this->metadata[] = [
            'timestamp' => time(),
            'model' => $response->model,
            'input_tokens' => $response->usage->inputTokens,
            'output_tokens' => $response->usage->outputTokens,
            'stop_reason' => $response->stop_reason,
        ];

        return [
            'text' => $assistantMessage,
            'usage' => [
                'input_tokens' => $response->usage->inputTokens,
                'output_tokens' => $response->usage->outputTokens,
            ],
            'stop_reason' => $response->stop_reason,
        ];
    }

    public function setSystemPrompt(string $prompt): void
    {
        $this->systemPrompt = $prompt;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getTotalTokens(): int
    {
        return array_sum(array_column($this->metadata, 'input_tokens')) +
               array_sum(array_column($this->metadata, 'output_tokens'));
    }

    public function clearHistory(): void
    {
        $this->messages = [];
        $this->metadata = [];
    }

    public function exportConversation(): array
    {
        return [
            'system_prompt' => $this->systemPrompt,
            'messages' => $this->messages,
            'metadata' => $this->metadata,
            'total_tokens' => $this->getTotalTokens(),
        ];
    }
}
```

**Usage:**

```php
<?php
# filename: examples/03-advanced-conversation.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Conversation\AdvancedConversationManager;
use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$conversation = new AdvancedConversationManager(
    client: $client,
    model: 'claude-sonnet-4-20250514',
    maxTokens: 2048,
    temperature: 0.7,
    systemPrompt: 'You are a helpful PHP programming tutor. Provide clear explanations with code examples.'
);

// Multi-turn conversation
$turns = [
    "What are PHP interfaces?",
    "Can you show me an example?",
    "How are interfaces different from abstract classes?",
    "When should I use one over the other?"
];

foreach ($turns as $i => $question) {
    echo "Turn " . ($i + 1) . "\n";
    echo "You: {$question}\n";

    $result = $conversation->sendMessage($question);

    echo "Claude: {$result['text']}\n";
    echo "Tokens: {$result['usage']['input_tokens']} in, {$result['usage']['output_tokens']} out\n\n";
}

// Export conversation
$export = $conversation->exportConversation();
echo "Total conversation tokens: {$export['total_tokens']}\n";

file_put_contents('conversation.json', json_encode($export, JSON_PRETTY_PRINT));
echo "Conversation exported to conversation.json\n";
```

## Context Window Management

Claude has a 200,000 token context window, but managing it efficiently is crucial for cost and performance.

### Token Estimation

```php
<?php
# filename: src/Services/TokenEstimator.php
declare(strict_types=1);

namespace App\Services;

class TokenEstimator
{
    /**
     * Rough estimation: 1 token ≈ 4 characters
     * Not exact, but useful for budgeting
     */
    public static function estimate(string $text): int
    {
        $cleaned = preg_replace('/\s+/', ' ', trim($text));
        return (int) ceil(mb_strlen($cleaned) / 4);
    }

    public static function estimateMessages(array $messages): int
    {
        $total = 0;

        foreach ($messages as $message) {
            $total += self::estimate($message['content']);
            $total += 4; // Overhead for role and structure
        }

        return $total;
    }

    public static function canFitInContext(
        array $messages,
        int $maxContextTokens = 200000,
        int $maxOutputTokens = 4096
    ): bool {
        $estimatedTokens = self::estimateMessages($messages);
        $availableForContext = $maxContextTokens - $maxOutputTokens;

        return $estimatedTokens <= $availableForContext;
    }
}
```

### Conversation Trimming Strategy

```php
<?php
# filename: src/Conversation/TrimmableConversationManager.php
declare(strict_types=1);

namespace App\Conversation;

use Anthropic\Anthropic;
use App\Services\TokenEstimator;

class TrimmableConversationManager
{
    private array $messages = [];
    private int $maxContextTokens = 150000; // Leave room for response

    public function __construct(
        private readonly Anthropic $client,
        private readonly string $model = 'claude-sonnet-4-20250514'
    ) {}

    public function sendMessage(string $userMessage): string
    {
        // Add new user message
        $this->messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];

        // Trim if necessary
        $this->trimMessages();

        // Make request
        $response = $this->client->messages()->create([
            'model' => $this->model,
            'max_tokens' => 2048,
            'messages' => $this->messages
        ]);

        $assistantMessage = $response->content[0]->text;

        // Add assistant response
        $this->messages[] = [
            'role' => 'assistant',
            'content' => $assistantMessage
        ];

        return $assistantMessage;
    }

    private function trimMessages(): void
    {
        // Keep trimming oldest messages until we fit in context
        while (count($this->messages) > 2) {
            $estimatedTokens = TokenEstimator::estimateMessages($this->messages);

            if ($estimatedTokens <= $this->maxContextTokens) {
                break;
            }

            // Remove oldest pair (user + assistant)
            array_shift($this->messages); // Remove user message
            if (count($this->messages) > 0 && $this->messages[0]['role'] === 'assistant') {
                array_shift($this->messages); // Remove corresponding assistant message
            }
        }
    }

    public function setMaxContextTokens(int $tokens): void
    {
        $this->maxContextTokens = $tokens;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getEstimatedTokens(): int
    {
        return TokenEstimator::estimateMessages($this->messages);
    }
}
```

### Sliding Window Approach

Keep only the most recent N messages:

```php
<?php
# filename: src/Conversation/SlidingWindowManager.php
declare(strict_types=1);

namespace App\Conversation;

use Anthropic\Anthropic;

class SlidingWindowManager
{
    private array $messages = [];
    private int $maxMessages = 20; // Keep last 10 exchanges (20 messages)

    public function __construct(
        private readonly Anthropic $client,
        int $maxMessages = 20
    ) {
        $this->maxMessages = $maxMessages;
    }

    public function sendMessage(string $userMessage): string
    {
        // Add user message
        $this->messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];

        // Apply sliding window
        $this->applyWindow();

        // Get response
        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'messages' => $this->messages
        ]);

        $assistantMessage = $response->content[0]->text;

        // Add assistant response
        $this->messages[] = [
            'role' => 'assistant',
            'content' => $assistantMessage
        ];

        return $assistantMessage;
    }

    private function applyWindow(): void
    {
        while (count($this->messages) > $this->maxMessages) {
            array_shift($this->messages);
        }
    }

    public function setWindowSize(int $size): void
    {
        $this->maxMessages = $size;
    }
}
```

### Summary-Based Context Compression

For long conversations, create summaries:

```php
<?php
# filename: src/Conversation/SummarizingManager.php
declare(strict_types=1);

namespace App\Conversation;

use Anthropic\Anthropic;
use App\Services\TokenEstimator;

class SummarizingManager
{
    private array $messages = [];
    private ?string $conversationSummary = null;
    private int $maxContextTokens = 150000;

    public function __construct(
        private readonly Anthropic $client
    ) {}

    public function sendMessage(string $userMessage): string
    {
        // Add user message
        $this->messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];

        // Check if we need to summarize
        $estimatedTokens = TokenEstimator::estimateMessages($this->messages);

        if ($estimatedTokens > $this->maxContextTokens) {
            $this->summarizeAndCompress();
        }

        // Prepare messages for request
        $requestMessages = $this->prepareMessages();

        // Get response
        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'messages' => $requestMessages
        ]);

        $assistantMessage = $response->content[0]->text;

        // Add to history
        $this->messages[] = [
            'role' => 'assistant',
            'content' => $assistantMessage
        ];

        return $assistantMessage;
    }

    private function summarizeAndCompress(): void
    {
        // Keep only last 4 messages for context
        $recentMessages = array_slice($this->messages, -4);

        // Summarize older messages
        $oldMessages = array_slice($this->messages, 0, -4);

        if (empty($oldMessages)) {
            return;
        }

        // Build conversation text
        $conversationText = '';
        foreach ($oldMessages as $msg) {
            $conversationText .= "{$msg['role']}: {$msg['content']}\n\n";
        }

        // Ask Claude to summarize
        $response = $this->client->messages()->create([
            'model' => 'claude-haiku-4-20250514', // Use Haiku for speed/cost
            'max_tokens' => 500,
            'messages' => [[
                'role' => 'user',
                'content' => "Summarize this conversation concisely, preserving key context and facts:\n\n{$conversationText}"
            ]]
        ]);

        $this->conversationSummary = $response->content[0]->text;

        // Replace old messages with recent ones
        $this->messages = $recentMessages;
    }

    private function prepareMessages(): array
    {
        // If we have a summary, prepend it as context
        if ($this->conversationSummary !== null) {
            return array_merge([
                [
                    'role' => 'user',
                    'content' => "Previous conversation summary:\n{$this->conversationSummary}"
                ],
                [
                    'role' => 'assistant',
                    'content' => 'I understand the previous context. Please continue.'
                ]
            ], $this->messages);
        }

        return $this->messages;
    }
}
```

## Conversation Persistence

### Session-Based Storage

```php
<?php
# filename: src/Storage/SessionConversationStorage.php
declare(strict_types=1);

namespace App\Storage;

class SessionConversationStorage
{
    private string $sessionKey;

    public function __construct(string $conversationId = 'default')
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->sessionKey = "claude_conversation_{$conversationId}";
    }

    public function save(array $messages): void
    {
        $_SESSION[$this->sessionKey] = [
            'messages' => $messages,
            'updated_at' => time(),
        ];
    }

    public function load(): array
    {
        return $_SESSION[$this->sessionKey]['messages'] ?? [];
    }

    public function exists(): bool
    {
        return isset($_SESSION[$this->sessionKey]);
    }

    public function clear(): void
    {
        unset($_SESSION[$this->sessionKey]);
    }

    public function getLastUpdate(): ?int
    {
        return $_SESSION[$this->sessionKey]['updated_at'] ?? null;
    }
}
```

### File-Based Storage

```php
<?php
# filename: src/Storage/FileConversationStorage.php
declare(strict_types=1);

namespace App\Storage;

class FileConversationStorage
{
    private string $storageDir;

    public function __construct(string $storageDir = '/tmp/conversations')
    {
        $this->storageDir = $storageDir;

        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }
    }

    public function save(string $conversationId, array $messages): void
    {
        $data = [
            'id' => $conversationId,
            'messages' => $messages,
            'created_at' => $this->getCreatedAt($conversationId),
            'updated_at' => time(),
        ];

        $file = $this->getFilePath($conversationId);
        file_put_contents($file, json_encode($data), LOCK_EX);
    }

    public function load(string $conversationId): array
    {
        $file = $this->getFilePath($conversationId);

        if (!file_exists($file)) {
            return [];
        }

        $data = json_decode(file_get_contents($file), true);
        return $data['messages'] ?? [];
    }

    public function exists(string $conversationId): bool
    {
        return file_exists($this->getFilePath($conversationId));
    }

    public function delete(string $conversationId): void
    {
        $file = $this->getFilePath($conversationId);

        if (file_exists($file)) {
            unlink($file);
        }
    }

    public function listConversations(): array
    {
        $files = glob($this->storageDir . '/conversation-*.json');
        $conversations = [];

        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            $conversations[] = [
                'id' => $data['id'],
                'message_count' => count($data['messages']),
                'created_at' => $data['created_at'],
                'updated_at' => $data['updated_at'],
            ];
        }

        return $conversations;
    }

    private function getFilePath(string $conversationId): string
    {
        $safeId = preg_replace('/[^a-zA-Z0-9_-]/', '', $conversationId);
        return "{$this->storageDir}/conversation-{$safeId}.json";
    }

    private function getCreatedAt(string $conversationId): int
    {
        $file = $this->getFilePath($conversationId);

        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            return $data['created_at'] ?? time();
        }

        return time();
    }
}
```

### Database Storage (PDO)

```php
<?php
# filename: src/Storage/DatabaseConversationStorage.php
declare(strict_types=1);

namespace App\Storage;

use PDO;

class DatabaseConversationStorage
{
    public function __construct(
        private readonly PDO $pdo
    ) {
        $this->createTableIfNotExists();
    }

    private function createTableIfNotExists(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS conversations (
                id VARCHAR(255) PRIMARY KEY,
                messages TEXT NOT NULL,
                created_at INTEGER NOT NULL,
                updated_at INTEGER NOT NULL
            )
        ");
    }

    public function save(string $conversationId, array $messages): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO conversations (id, messages, created_at, updated_at)
            VALUES (:id, :messages, :created_at, :updated_at)
            ON CONFLICT(id) DO UPDATE SET
                messages = :messages,
                updated_at = :updated_at
        ");

        $now = time();

        // Get existing created_at or use now
        $createdAt = $this->getCreatedAt($conversationId) ?? $now;

        $stmt->execute([
            ':id' => $conversationId,
            ':messages' => json_encode($messages),
            ':created_at' => $createdAt,
            ':updated_at' => $now,
        ]);
    }

    public function load(string $conversationId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT messages FROM conversations WHERE id = :id
        ");

        $stmt->execute([':id' => $conversationId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return [];
        }

        return json_decode($result['messages'], true) ?? [];
    }

    public function exists(string $conversationId): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM conversations WHERE id = :id
        ");

        $stmt->execute([':id' => $conversationId]);
        return $stmt->fetchColumn() > 0;
    }

    public function delete(string $conversationId): void
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM conversations WHERE id = :id
        ");

        $stmt->execute([':id' => $conversationId]);
    }

    public function listConversations(): array
    {
        $stmt = $this->pdo->query("
            SELECT id, created_at, updated_at,
                   JSON_LENGTH(messages) as message_count
            FROM conversations
            ORDER BY updated_at DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getCreatedAt(string $conversationId): ?int
    {
        $stmt = $this->pdo->prepare("
            SELECT created_at FROM conversations WHERE id = :id
        ");

        $stmt->execute([':id' => $conversationId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? (int)$result['created_at'] : null;
    }
}
```

### Persistent Conversation Manager

```php
<?php
# filename: src/Conversation/PersistentConversationManager.php
declare(strict_types=1);

namespace App\Conversation;

use Anthropic\Anthropic;
use App\Storage\FileConversationStorage;

class PersistentConversationManager
{
    private array $messages = [];
    private string $conversationId;

    public function __construct(
        private readonly Anthropic $client,
        private readonly FileConversationStorage $storage,
        ?string $conversationId = null
    ) {
        $this->conversationId = $conversationId ?? uniqid('conv_', true);

        // Load existing conversation
        if ($this->storage->exists($this->conversationId)) {
            $this->messages = $this->storage->load($this->conversationId);
        }
    }

    public function sendMessage(string $userMessage): string
    {
        // Add user message
        $this->messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];

        // Get response
        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'messages' => $this->messages
        ]);

        $assistantMessage = $response->content[0]->text;

        // Add assistant response
        $this->messages[] = [
            'role' => 'assistant',
            'content' => $assistantMessage
        ];

        // Persist to storage
        $this->storage->save($this->conversationId, $this->messages);

        return $assistantMessage;
    }

    public function getConversationId(): string
    {
        return $this->conversationId;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function clearHistory(): void
    {
        $this->messages = [];
        $this->storage->delete($this->conversationId);
    }
}
```

**Usage:**

```php
<?php
# filename: examples/04-persistent-conversation.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Conversation\PersistentConversationManager;
use App\Storage\FileConversationStorage;
use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$storage = new FileConversationStorage(__DIR__ . '/../storage/conversations');

// Start new conversation
$conversation = new PersistentConversationManager($client, $storage);

echo "Conversation ID: {$conversation->getConversationId()}\n\n";

$reply1 = $conversation->sendMessage("My name is Sarah.");
echo "Claude: {$reply1}\n\n";

$reply2 = $conversation->sendMessage("I'm learning PHP.");
echo "Claude: {$reply2}\n\n";

// Save the conversation ID for later
$conversationId = $conversation->getConversationId();
file_put_contents('last_conversation_id.txt', $conversationId);

echo "\n--- Later, in a new session ---\n\n";

// Resume conversation
$resumedId = file_get_contents('last_conversation_id.txt');
$resumed = new PersistentConversationManager($client, $storage, $resumedId);

$reply3 = $resumed->sendMessage("What do you know about me?");
echo "Claude: {$reply3}\n";
// Claude remembers: name is Sarah, learning PHP
```

## Advanced Conversation Patterns

### Multi-Persona Conversations

```php
<?php
# filename: src/Conversation/MultiPersonaManager.php
declare(strict_types=1);

namespace App\Conversation;

use Anthropic\Anthropic;

class MultiPersonaManager
{
    private array $messages = [];

    public function __construct(
        private readonly Anthropic $client
    ) {}

    public function sendAsUser(string $message, string $systemPrompt): string
    {
        // Add user message
        $this->messages[] = [
            'role' => 'user',
            'content' => $message
        ];

        // Get response with specific persona
        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'system' => $systemPrompt,
            'messages' => $this->messages
        ]);

        $assistantMessage = $response->content[0]->text;

        // Add to history
        $this->messages[] = [
            'role' => 'assistant',
            'content' => $assistantMessage
        ];

        return $assistantMessage;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }
}
```

**Usage:**

```php
<?php
# filename: examples/05-multi-persona.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Conversation\MultiPersonaManager;
use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$conversation = new MultiPersonaManager($client);

// Ask as a beginner
echo "=== Beginner asks ===\n";
$reply1 = $conversation->sendAsUser(
    "What is dependency injection?",
    "You are a patient teacher explaining to beginners. Use simple language and analogies."
);
echo $reply1 . "\n\n";

// Ask for advanced details
echo "=== Advanced developer asks ===\n";
$reply2 = $conversation->sendAsUser(
    "Can you explain the technical implementation details?",
    "You are an expert developer. Provide technical, in-depth explanations with code examples."
);
echo $reply2 . "\n\n";
```

### Conversation Branching

```php
<?php
# filename: src/Conversation/BranchingManager.php
declare(strict_types=1);

namespace App\Conversation;

use Anthropic\Anthropic;

class BranchingManager
{
    private array $mainBranch = [];
    private array $branches = [];

    public function __construct(
        private readonly Anthropic $client
    ) {}

    public function sendMessage(string $message, ?string $branchId = null): string
    {
        if ($branchId === null) {
            return $this->sendToMain($message);
        }

        return $this->sendToBranch($branchId, $message);
    }

    private function sendToMain(string $message): string
    {
        $this->mainBranch[] = ['role' => 'user', 'content' => $message];

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'messages' => $this->mainBranch
        ]);

        $reply = $response->content[0]->text;
        $this->mainBranch[] = ['role' => 'assistant', 'content' => $reply];

        return $reply;
    }

    private function sendToBranch(string $branchId, string $message): string
    {
        // Initialize branch if doesn't exist
        if (!isset($this->branches[$branchId])) {
            $this->branches[$branchId] = $this->mainBranch;
        }

        $this->branches[$branchId][] = ['role' => 'user', 'content' => $message];

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'messages' => $this->branches[$branchId]
        ]);

        $reply = $response->content[0]->text;
        $this->branches[$branchId][] = ['role' => 'assistant', 'content' => $reply];

        return $reply;
    }

    public function createBranch(string $branchId): void
    {
        $this->branches[$branchId] = $this->mainBranch;
    }

    public function getBranch(string $branchId): array
    {
        return $this->branches[$branchId] ?? [];
    }

    public function getMainBranch(): array
    {
        return $this->mainBranch;
    }
}
```

## Exercises

### Exercise 1: Conversation Analytics

Build a system to track conversation metrics:

```php
<?php
class ConversationAnalytics
{
    public function analyze(array $messages): array
    {
        // TODO: Count total messages
        // TODO: Calculate average message length
        // TODO: Count user vs assistant messages
        // TODO: Estimate total tokens
        // TODO: Calculate conversation duration
        // TODO: Return analytics array
    }
}
```

### Exercise 2: Conversation Exporter

Create a conversation export system:

```php
<?php
class ConversationExporter
{
    public function exportToMarkdown(array $messages): string
    {
        // TODO: Format messages as markdown
        // TODO: Add metadata header
        // TODO: Return formatted string
    }

    public function exportToHtml(array $messages): string
    {
        // TODO: Create HTML document
        // TODO: Style conversation nicely
        // TODO: Return HTML string
    }
}
```

### Exercise 3: Context-Aware Summarizer

Build intelligent conversation summarization:

```php
<?php
class ContextAwareSummarizer
{
    public function summarize(array $messages, int $targetLength = 500): string
    {
        // TODO: Extract key topics
        // TODO: Identify important facts
        // TODO: Generate concise summary
        // TODO: Preserve critical context
    }
}
```

<details>
<summary>Solution Hints</summary>

**Exercise 1**: Use array functions (count, array_filter, array_map), calculate string lengths with mb_strlen, estimate tokens with char count / 4.

**Exercise 2**: Loop through messages building formatted strings, use heredoc for templates, add CSS for HTML version.

**Exercise 3**: Send older messages to Claude asking for summary, keep recent messages untouched, use Haiku model for speed/cost.

</details>

## Troubleshooting

**Conversation context lost?**
- Ensure you're passing entire message history
- Check storage is persisting correctly
- Verify messages aren't being trimmed too aggressively

**Role alternation errors?**
- Validate messages alternate user/assistant
- Check first message is always user
- Don't have consecutive same-role messages

**Out of context errors?**
- Implement conversation trimming
- Use summarization for long conversations
- Monitor estimated token usage

**Slow responses with long histories?**
- Trim older messages
- Increase timeout for large contexts
- Consider conversation summarization

## Key Takeaways

- ✓ **Messages must alternate** between user and assistant roles
- ✓ **Conversations are stateless** - you must maintain history
- ✓ **Context window is large** (200K tokens) but has limits
- ✓ **Trim strategically** to manage costs and performance
- ✓ **Persist conversations** for multi-session continuity
- ✓ **System prompts** set behavior without being in messages array
- ✓ **Summarization** helps manage very long conversations
- ✓ **Token estimation** is crucial for budget management
- ✓ **Storage strategy** depends on your application needs
- ✓ **Sliding windows** work well for ongoing chats

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="04"
  label="You can build sophisticated conversation systems!"
/>

---

Continue to [Chapter 05: Prompt Engineering Basics](/series/claude-php-developers/chapters/05-prompt-engineering-basics) to learn effective prompting techniques.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 04 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-04)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-04
composer install
cp .env.example .env
# Add your API key to .env
php examples/02-basic-conversation.php
```
