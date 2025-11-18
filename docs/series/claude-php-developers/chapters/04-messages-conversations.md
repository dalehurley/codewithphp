---
title: "04: Understanding Messages and Conversations"
description: "Master multi-turn conversations with Claude. Learn message formatting, conversation state management, context handling, memory techniques, and building stateful chat applications in PHP."
series: "claude-php-developers"
chapter: 4
order: 4
difficulty: "Intermediate"
prerequisites:
  - "PHP 8.4+ installed"
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
- ✓ **PHP 8.4+** with type declarations knowledge
- ✓ **Anthropic API key** configured
- ✓ **Understanding of sessions** or state management

**Verify your setup:**

```bash
php --version  # Should show PHP 8.4+
composer --version  # Should be installed
```

## What You'll Build

By the end of this chapter, you will have created:

- A `MessageValidator` class for validating and sanitizing individual messages
- A `ConversationValidator` class for validating entire conversation arrays
- A `BasicConversationManager` class for managing multi-turn conversations
- An `AdvancedConversationManager` with system prompts and metadata tracking
- A `TokenEstimator` service for context window budgeting
- Multiple conversation trimming strategies (sliding window, token-based, summarization)
- Three storage implementations: Session, File, and Database-based conversation persistence
- A `PersistentConversationManager` that maintains conversations across sessions
- Advanced conversation patterns including multi-persona and branching conversations
- Production-ready conversation management systems with context window handling

You'll understand how to structure messages, validate conversation integrity, manage conversation state, handle context windows efficiently, and build stateful chat applications that maintain context across multiple interactions.

## Objectives

By completing this chapter, you will:

- **Understand** message structure, content types, and role alternation rules
- **Validate** messages and conversations before sending to catch errors early
- **Sanitize** message content for consistency and safety
- **Build** conversation managers for multi-turn interactions
- **Implement** context window management strategies
- **Create** persistent conversation storage systems
- **Master** conversation trimming and summarization techniques
- **Apply** advanced patterns like branching and multi-persona conversations
- **Design** production-ready conversation systems with proper state management

## Message Structure Fundamentals (~10 min)

### Anatomy of a Message

Every message in a Claude conversation has two required fields:

```php
<?php
$message = [
    'role' => 'user',        // Required: 'user' or 'assistant'
    'content' => 'Hello!'    // Required: string or array of content blocks
];
```

**Content Structure:**

The `content` field can be either:

1. **Simple string** (most common):
   ```php
   'content' => 'Hello, Claude!'
   ```

2. **Array of content blocks** (for multimodal content):
   ```php
   'content' => [
       ['type' => 'text', 'text' => 'What is in this image?'],
       ['type' => 'image', 'source' => ['type' => 'base64', 'data' => '...']]
   ]
   ```

::: info Content Blocks
For this chapter, we focus on simple string content. Multimodal content with images and other content blocks is covered in [Chapter 13: Vision - Working with Images](/series/claude-php-developers/chapters/13-vision-images).
:::

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

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY');

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

### Message Validation

Before sending messages to Claude, it's good practice to validate their structure. This catches errors early and provides clear feedback:

```php
<?php
# filename: src/Validation/MessageValidator.php
declare(strict_types=1);

namespace App\Validation;

class MessageValidator
{
    /**
     * Validate a single message structure
     * 
     * @throws \InvalidArgumentException if message is invalid
     */
    public static function validateMessage(array $message): void
    {
        // Check required fields
        if (!isset($message['role'])) {
            throw new \InvalidArgumentException('Message must have a "role" field');
        }

        if (!isset($message['content'])) {
            throw new \InvalidArgumentException('Message must have a "content" field');
        }

        // Validate role
        if (!in_array($message['role'], ['user', 'assistant'], true)) {
            throw new \InvalidArgumentException(
                'Message role must be "user" or "assistant", got: ' . $message['role']
            );
        }

        // Validate content type
        if (!is_string($message['content']) && !is_array($message['content'])) {
            throw new \InvalidArgumentException(
                'Message content must be string or array, got: ' . gettype($message['content'])
            );
        }

        // Validate string content length
        if (is_string($message['content'])) {
            if (mb_strlen($message['content']) === 0) {
                throw new \InvalidArgumentException('Message content cannot be empty');
            }

            // Warn about very long content (not an error, but worth noting)
            if (mb_strlen($message['content']) > 1000000) {
                trigger_error(
                    'Message content is very long (' . mb_strlen($message['content']) . ' chars). ' .
                    'Consider splitting or using content blocks.',
                    E_USER_WARNING
                );
            }
        }

        // Validate content blocks array structure (if array)
        if (is_array($message['content'])) {
            self::validateContentBlocks($message['content']);
        }
    }

    /**
     * Validate content blocks array structure
     */
    private static function validateContentBlocks(array $blocks): void
    {
        if (empty($blocks)) {
            throw new \InvalidArgumentException('Content blocks array cannot be empty');
        }

        foreach ($blocks as $index => $block) {
            if (!is_array($block)) {
                throw new \InvalidArgumentException(
                    "Content block at index {$index} must be an array"
                );
            }

            if (!isset($block['type'])) {
                throw new \InvalidArgumentException(
                    "Content block at index {$index} must have a 'type' field"
                );
            }

            // Validate text block
            if ($block['type'] === 'text') {
                if (!isset($block['text']) || !is_string($block['text'])) {
                    throw new \InvalidArgumentException(
                        "Text content block at index {$index} must have a 'text' field (string)"
                    );
                }
            }

            // Note: Image block validation would go here, but covered in Chapter 13
        }
    }

    /**
     * Sanitize message content
     * 
     * Ensures content is safe and properly formatted
     */
    public static function sanitizeContent(string $content): string
    {
        // Trim whitespace
        $content = trim($content);

        // Normalize line endings
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        // Remove excessive blank lines (more than 2 consecutive)
        $content = preg_replace('/\n{3,}/', "\n\n", $content);

        // Ensure content is not empty after sanitization
        if (mb_strlen($content) === 0) {
            throw new \InvalidArgumentException('Content is empty after sanitization');
        }

        return $content;
    }

    /**
     * Validate and sanitize a message
     * 
     * Returns sanitized message array
     */
    public static function validateAndSanitize(array $message): array
    {
        self::validateMessage($message);

        // Sanitize string content
        if (is_string($message['content'])) {
            $message['content'] = self::sanitizeContent($message['content']);
        }

        return $message;
    }
}
```

**Usage:**

```php
<?php
# filename: examples/01-message-validation.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Validation\MessageValidator;

// ✓ Valid message
$validMessage = [
    'role' => 'user',
    'content' => 'Hello, Claude!'
];

MessageValidator::validateMessage($validMessage);
echo "Message is valid!\n";

// ✗ Invalid: Missing role
try {
    MessageValidator::validateMessage(['content' => 'Hello']);
} catch (\InvalidArgumentException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// ✗ Invalid: Empty content
try {
    MessageValidator::validateMessage(['role' => 'user', 'content' => '']);
} catch (\InvalidArgumentException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Sanitize content
$dirtyContent = "  Hello\n\n\n\nWorld  ";
$cleanContent = MessageValidator::sanitizeContent($dirtyContent);
echo "Sanitized: '{$cleanContent}'\n";

// Validate and sanitize
$message = [
    'role' => 'user',
    'content' => "  Multiple\n\n\n\nLines  "
];

$cleanMessage = MessageValidator::validateAndSanitize($message);
echo "Cleaned message: " . json_encode($cleanMessage) . "\n";
```

### Conversation Validation

Validate entire conversation arrays to ensure proper structure:

```php
<?php
# filename: src/Validation/ConversationValidator.php
declare(strict_types=1);

namespace App\Validation;

class ConversationValidator
{
    /**
     * Validate an entire conversation array
     * 
     * @throws \InvalidArgumentException if conversation is invalid
     */
    public static function validateConversation(array $messages): void
    {
        if (empty($messages)) {
            throw new \InvalidArgumentException('Conversation cannot be empty');
        }

        // Validate first message
        $firstMessage = $messages[0];
        if ($firstMessage['role'] !== 'user') {
            throw new \InvalidArgumentException(
                'Conversation must start with a user message, got: ' . $firstMessage['role']
            );
        }

        // Validate each message
        foreach ($messages as $index => $message) {
            try {
                MessageValidator::validateMessage($message);
            } catch (\InvalidArgumentException $e) {
                throw new \InvalidArgumentException(
                    "Invalid message at index {$index}: " . $e->getMessage(),
                    0,
                    $e
                );
            }
        }

        // Validate alternation
        self::validateAlternation($messages);
    }

    /**
     * Validate that messages alternate between user and assistant
     */
    private static function validateAlternation(array $messages): void
    {
        $previousRole = null;

        foreach ($messages as $index => $message) {
            $currentRole = $message['role'];

            // Check for consecutive same-role messages
            if ($previousRole !== null && $previousRole === $currentRole) {
                throw new \InvalidArgumentException(
                    "Messages must alternate roles. Found consecutive '{$currentRole}' " .
                    "messages at indices " . ($index - 1) . " and {$index}"
                );
            }

            $previousRole = $currentRole;
        }
    }

    /**
     * Validate and sanitize entire conversation
     * 
     * Returns sanitized conversation array
     */
    public static function validateAndSanitize(array $messages): array
    {
        self::validateConversation($messages);

        $sanitized = [];
        foreach ($messages as $message) {
            $sanitized[] = MessageValidator::validateAndSanitize($message);
        }

        return $sanitized;
    }

    /**
     * Check if conversation is valid without throwing exceptions
     * 
     * Returns [isValid: bool, errors: string[]]
     */
    public static function checkConversation(array $messages): array
    {
        $errors = [];

        try {
            self::validateConversation($messages);
            return ['isValid' => true, 'errors' => []];
        } catch (\InvalidArgumentException $e) {
            return ['isValid' => false, 'errors' => [$e->getMessage()]];
        }
    }
}
```

**Usage:**

```php
<?php
# filename: examples/02-conversation-validation.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Validation\ConversationValidator;

// ✓ Valid conversation
$validConversation = [
    ['role' => 'user', 'content' => 'Hello!'],
    ['role' => 'assistant', 'content' => 'Hi there!'],
    ['role' => 'user', 'content' => 'How are you?'],
];

ConversationValidator::validateConversation($validConversation);
echo "Conversation is valid!\n";

// ✗ Invalid: Starts with assistant
try {
    $invalid = [
        ['role' => 'assistant', 'content' => 'Hello!'],
    ];
    ConversationValidator::validateConversation($invalid);
} catch (\InvalidArgumentException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// ✗ Invalid: Consecutive user messages
try {
    $invalid = [
        ['role' => 'user', 'content' => 'First'],
        ['role' => 'user', 'content' => 'Second'],
    ];
    ConversationValidator::validateConversation($invalid);
} catch (\InvalidArgumentException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Check without exceptions
$result = ConversationValidator::checkConversation($validConversation);
if ($result['isValid']) {
    echo "Conversation is valid!\n";
} else {
    echo "Errors: " . implode(', ', $result['errors']) . "\n";
}
```

## Building Multi-Turn Conversations (~15 min)

### Basic Conversation Manager

```php
<?php
# filename: src/Conversation/BasicConversationManager.php
declare(strict_types=1);

namespace App\Conversation;

use ClaudePhp\ClaudePhp;

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
        // Validate and sanitize user message
        $message = \App\Validation\MessageValidator::validateAndSanitize([
            'role' => 'user',
            'content' => $userMessage
        ]);

        // Add user message
        $this->messages[] = $message;

        // Validate conversation before sending
        \App\Validation\ConversationValidator::validateConversation($this->messages);

        // Get response
        $response = $this->client->messages()->create([
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
            'messages' => $this->messages
        ]);

        $assistantMessage = $response->content[0]['text'];

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
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY');

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

use ClaudePhp\ClaudePhp;

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

        $assistantMessage = $response->content[0]['text'];

        // Add assistant response to history
        $this->messages[] = [
            'role' => 'assistant',
            'content' => $assistantMessage
        ];

        // Store metadata
        $this->metadata[] = [
            'timestamp' => time(),
            'model' => $response->model,
            'input_tokens' => $response->usage->input_tokens,
            'output_tokens' => $response->usage->output_tokens,
            'stop_reason' => $response->stop_reason,
        ];

        return [
            'text' => $assistantMessage,
            'usage' => [
                'input_tokens' => $response->usage->input_tokens,
                'output_tokens' => $response->usage->output_tokens,
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
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY');

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

## Context Window Management (~10 min)

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

use ClaudePhp\ClaudePhp;
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

        $assistantMessage = $response->content[0]['text'];

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

use ClaudePhp\ClaudePhp;

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

        $assistantMessage = $response->content[0]['text'];

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

use ClaudePhp\ClaudePhp;
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

        $assistantMessage = $response->content[0]['text'];

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

        $this->conversationSummary = $response->content[0]['text'];

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

## Conversation Persistence (~10 min)

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

use ClaudePhp\ClaudePhp;
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

        $assistantMessage = $response->content[0]['text'];

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
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY');

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

## Advanced Conversation Patterns (~5 min)

### Multi-Persona Conversations

```php
<?php
# filename: src/Conversation/MultiPersonaManager.php
declare(strict_types=1);

namespace App\Conversation;

use ClaudePhp\ClaudePhp;

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

        $assistantMessage = $response->content[0]['text'];

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
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY');

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

use ClaudePhp\ClaudePhp;

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

        $reply = $response->content[0]['text'];
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

        $reply = $response->content[0]['text'];
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

### Exercise 1: Conversation Analytics (~15 min)

**Goal**: Build a system to track conversation metrics and provide insights.

Create a `ConversationAnalytics` class that analyzes conversation data:

```php
<?php
# filename: exercises/ConversationAnalytics.php
declare(strict_types=1);

class ConversationAnalytics
{
    public function analyze(array $messages): array
    {
        // TODO: Count total messages
        // TODO: Calculate average message length
        // TODO: Count user vs assistant messages
        // TODO: Estimate total tokens
        // TODO: Calculate conversation duration (if timestamps available)
        // TODO: Return analytics array
    }
}
```

**Requirements:**
- Count total messages in the conversation
- Calculate average message length (characters)
- Separate counts for user vs assistant messages
- Estimate total tokens using the 4-character approximation
- Return a structured array with all metrics

**Validation**: Test with a sample conversation:

```php
$messages = [
    ['role' => 'user', 'content' => 'Hello'],
    ['role' => 'assistant', 'content' => 'Hi there!'],
    ['role' => 'user', 'content' => 'How are you?'],
];

$analytics = new ConversationAnalytics();
$result = $analytics->analyze($messages);

// Expected structure:
// [
//     'total_messages' => 3,
//     'user_messages' => 2,
//     'assistant_messages' => 1,
//     'average_length' => 8.67,
//     'estimated_tokens' => 7,
// ]
```

### Exercise 2: Conversation Exporter (~20 min)

**Goal**: Create a conversation export system for multiple formats.

Build a `ConversationExporter` class that formats conversations:

```php
<?php
# filename: exercises/ConversationExporter.php
declare(strict_types=1);

class ConversationExporter
{
    public function exportToMarkdown(array $messages): string
    {
        // TODO: Format messages as markdown
        // TODO: Add metadata header with timestamp
        // TODO: Use proper markdown formatting
        // TODO: Return formatted string
    }

    public function exportToHtml(array $messages): string
    {
        // TODO: Create HTML document with DOCTYPE
        // TODO: Style conversation with CSS
        // TODO: Format user/assistant messages differently
        // TODO: Return HTML string
    }
}
```

**Requirements:**
- Markdown export should include a header with date/time
- Format user messages with "**User:**" prefix
- Format assistant messages with "**Assistant:**" prefix
- HTML export should include inline CSS for styling
- Both exports should preserve message order

**Validation**: Export a sample conversation and verify formatting:

```php
$messages = [
    ['role' => 'user', 'content' => 'What is PHP?'],
    ['role' => 'assistant', 'content' => 'PHP is a server-side language.'],
];

$exporter = new ConversationExporter();
$markdown = $exporter->exportToMarkdown($messages);
$html = $exporter->exportToHtml($messages);

// Verify markdown contains proper formatting
// Verify HTML is valid and styled
```

### Exercise 3: Context-Aware Summarizer (~25 min)

**Goal**: Build intelligent conversation summarization that preserves key context.

Create a `ContextAwareSummarizer` that uses Claude to summarize conversations:

```php
<?php
# filename: exercises/ContextAwareSummarizer.php
declare(strict_types=1);

use ClaudePhp\ClaudePhp;

class ContextAwareSummarizer
{
    public function __construct(
        private readonly Anthropic $client
    ) {}

    public function summarize(array $messages, int $targetLength = 500): string
    {
        // TODO: Extract key topics from messages
        // TODO: Identify important facts and context
        // TODO: Use Claude to generate concise summary
        // TODO: Preserve critical context (names, decisions, etc.)
        // TODO: Return summary string
    }
}
```

**Requirements:**
- Use Claude Haiku model for cost efficiency
- Preserve important facts (names, dates, decisions)
- Keep summary under target length
- Maintain conversation flow context
- Handle empty or short conversations gracefully

**Validation**: Test with a longer conversation:

```php
$messages = [
    ['role' => 'user', 'content' => 'My name is Sarah.'],
    ['role' => 'assistant', 'content' => 'Nice to meet you, Sarah!'],
    ['role' => 'user', 'content' => 'I am learning PHP.'],
    // ... more messages
];

$summarizer = new ContextAwareSummarizer($client);
$summary = $summarizer->summarize($messages, 300);

// Verify summary includes "Sarah" and "learning PHP"
// Verify summary is under 300 characters
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

## Wrap-up

Congratulations! You've completed a comprehensive exploration of messages and conversations with Claude. Here's what you've accomplished:

- ✓ **Mastered message structure** - Understanding role alternation and message formatting
- ✓ **Built conversation managers** - Created basic and advanced managers with system prompts
- ✓ **Implemented context management** - Learned token estimation and trimming strategies
- ✓ **Created persistence systems** - Built session, file, and database storage solutions
- ✓ **Applied advanced patterns** - Explored multi-persona and branching conversations
- ✓ **Designed production systems** - Combined all concepts into production-ready solutions

You now have the knowledge to build sophisticated, stateful chat applications that maintain context, manage conversation history efficiently, and scale to handle long-running conversations. These skills are essential for creating engaging AI-powered applications that feel natural and contextually aware.

In the next chapter, you'll learn prompt engineering techniques to get the most out of Claude's capabilities and create more effective interactions.

## Key Takeaways

- ✓ **Messages must alternate** between user and assistant roles
- ✓ **Conversations are stateless** - you must maintain history
- ✓ **Content can be string or array** - simple strings for text, arrays for multimodal content
- ✓ **Validate messages before sending** - catch errors early with validation helpers
- ✓ **Sanitize content** - normalize whitespace and formatting for consistency
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

## Further Reading

- [Anthropic Messages API Documentation](https://docs.claude.com/en/api/messages) — Official API reference for message structure and parameters
- [Anthropic Context Window Guide](https://docs.claude.com/en/docs/build-with-claude/context-windows) — Understanding context limits and best practices
- [PHP Sessions Documentation](https://www.php.net/manual/en/book.session.php) — PHP session management for conversation persistence
- [PDO Database Access](https://www.php.net/manual/en/book.pdo.php) — PHP Data Objects for database-backed conversation storage
- [Chapter 03: Your First Claude Request](/series/claude-php-developers/chapters/03-first-claude-request) — Review API request fundamentals
- [Chapter 05: Prompt Engineering Basics](/series/claude-php-developers/chapters/05-prompt-engineering-basics) — Learn effective prompting techniques

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
