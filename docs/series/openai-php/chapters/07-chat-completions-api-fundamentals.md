---
title: "07: Chat Completions API Fundamentals"
description: "Master the Chat Completions API with message roles, system prompts, conversation management, and response formatting"
series: "openai-php"
chapter: 7
order: 7
difficulty: "Intermediate"
prerequisites:
  - "/series/openai-php/chapters/06-working-with-tokens"
  - "Understanding of conversational AI concepts"
---

![Chat Completions API Fundamentals](/images/openai-php/chapter-07-chat-completions-hero-full.webp)

[Home](/series/openai-php) > [Chapter 06](/series/openai-php/chapters/06-working-with-tokens) > Chat Completions API Fundamentals

# Chapter 07: Chat Completions API Fundamentals

<span class="difficulty-badge difficulty-intermediate">Intermediate</span>
<span class="time-badge">60-75 minutes</span>

## Overview

The Chat Completions API is the heart of OpenAI's conversational capabilities and the foundation for most modern AI applications. Unlike simple text completion, the Chat API understands context, maintains conversation flow, and responds based on different message roles—system, user, and assistant.

Mastering this API is crucial because it powers chatbots, virtual assistants, customer support systems, and any application that needs natural language understanding. The difference between a frustrating chatbot and a delightful one often comes down to how well you leverage the Chat Completions API's features.

In this comprehensive chapter, you'll learn to craft effective system prompts that shape AI personality, manage multi-turn conversations that maintain context, and format responses precisely to your needs. You'll build practical systems for conversation state management, context preservation, and dynamic prompt generation.

This is one of the most important chapters in the series—take your time, experiment with the examples, and build a solid foundation for everything that follows.

## What You'll Learn

- 🎭 **Message Roles**: Master system, user, and assistant roles effectively
- 📝 **System Prompts**: Craft prompts that shape AI behavior and personality
- 💬 **Multi-Turn Conversations**: Build and manage conversation flows
- 🧠 **Context Management**: Preserve context across multiple exchanges
- 🎯 **Response Formatting**: Control output format and structure
- 🔄 **Conversation Patterns**: Implement common dialogue patterns
- 🛠️ **Practical Applications**: Build real-world conversation systems

## Prerequisites

- ✅ Completed Chapters 01-06
- ✅ Understanding of conversation design
- ✅ Knowledge of token management
- ✅ Familiarity with JSON structures

---

## Understanding Message Roles

### The Three Roles

The Chat Completions API uses three distinct message roles:

```php
<?php

/**
 * Message role definitions
 */

class MessageRole
{
    // Sets the AI's behavior, personality, and guidelines
    public const SYSTEM = 'system';

    // Represents the human user's input
    public const USER = 'user';

    // Represents the AI's responses
    public const ASSISTANT = 'assistant';
}

// Example conversation
$messages = [
    [
        'role' => MessageRole::SYSTEM,
        'content' => 'You are a helpful PHP programming assistant.'
    ],
    [
        'role' => MessageRole::USER,
        'content' => 'How do I connect to MySQL in PHP?'
    ],
    [
        'role' => MessageRole::ASSISTANT,
        'content' => 'You can connect to MySQL using PDO or MySQLi...'
    ],
    [
        'role' => MessageRole::USER,
        'content' => 'Show me a PDO example.'
    ]
];
```

### Role Purposes

**System Message:**
- Sets the assistant's behavior and personality
- Defines constraints and guidelines
- Usually the first message
- Can be updated mid-conversation
- Not visible to end users

**User Message:**
- Input from the human
- Questions, commands, or statements
- Drives the conversation forward
- Can include context and examples

**Assistant Message:**
- AI's previous responses
- Used to maintain conversation history
- Helps AI understand context
- Can be pre-filled for few-shot learning

---

## Crafting Effective System Prompts

### System Prompt Best Practices

```php
<?php

class SystemPromptBuilder
{
    private string $role = '';
    private array $traits = [];
    private array $constraints = [];
    private array $format = [];

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function addTrait(string $trait): self
    {
        $this->traits[] = $trait;
        return $this;
    }

    public function addConstraint(string $constraint): self
    {
        $this->constraints[] = $constraint;
        return $this;
    }

    public function setOutputFormat(string $format): self
    {
        $this->format[] = $format;
        return $this;
    }

    public function build(): string
    {
        $parts = [];

        // Role definition
        if ($this->role) {
            $parts[] = "You are {$this->role}.";
        }

        // Personality traits
        if ($this->traits) {
            $parts[] = "You are " . implode(', ', $this->traits) . ".";
        }

        // Constraints
        if ($this->constraints) {
            $parts[] = "\nConstraints:";
            foreach ($this->constraints as $constraint) {
                $parts[] = "- {$constraint}";
            }
        }

        // Output format
        if ($this->format) {
            $parts[] = "\nOutput format:";
            foreach ($this->format as $format) {
                $parts[] = "- {$format}";
            }
        }

        return implode("\n", $parts);
    }
}

// Example: Customer support assistant
$systemPrompt = (new SystemPromptBuilder())
    ->setRole('a friendly and professional customer support assistant')
    ->addTrait('helpful')
    ->addTrait('patient')
    ->addTrait('empathetic')
    ->addConstraint('Always remain polite and professional')
    ->addConstraint('Never make promises about refunds without checking policies')
    ->addConstraint('Escalate complex issues to human agents')
    ->setOutputFormat('Keep responses under 200 words')
    ->setOutputFormat('Use bullet points for lists')
    ->build();

echo $systemPrompt;
/*
You are a friendly and professional customer support assistant.
You are helpful, patient, empathetic.

Constraints:
- Always remain polite and professional
- Never make promises about refunds without checking policies
- Escalate complex issues to human agents

Output format:
- Keep responses under 200 words
- Use bullet points for lists
*/
```

### Common System Prompt Patterns

```php
<?php

class SystemPromptTemplates
{
    public static function expert(string $domain): string
    {
        return "You are an expert in {$domain}. Provide accurate, detailed, " .
               "and well-explained answers. Use technical terminology appropriately " .
               "but explain complex concepts clearly.";
    }

    public static function tutor(string $subject, string $level): string
    {
        return "You are a patient and encouraging {$subject} tutor for {$level} students. " .
               "Break down concepts into simple steps. Use examples and analogies. " .
               "Encourage learning through questions rather than just giving answers.";
    }

    public static function coder(string $language): string
    {
        return "You are an experienced {$language} developer. Provide clean, " .
               "well-commented code following best practices. Explain your " .
               "implementation choices and potential tradeoffs.";
    }

    public static function creative(string $style): string
    {
        return "You are a creative writer specializing in {$style}. " .
               "Write engaging, original content with vivid descriptions. " .
               "Maintain consistent tone and style throughout.";
    }

    public static function analyst(string $domain): string
    {
        return "You are a {$domain} analyst. Provide data-driven insights, " .
               "identify patterns and trends, and support conclusions with evidence. " .
               "Be objective and thorough in your analysis.";
    }
}

// Usage
$messages = [
    [
        'role' => 'system',
        'content' => SystemPromptTemplates::coder('PHP')
    ],
    [
        'role' => 'user',
        'content' => 'Write a function to validate email addresses'
    ]
];
```

---

## Building Multi-Turn Conversations

### Conversation Manager

```php
<?php

/**
 * Comprehensive conversation management system
 */

class ConversationManager
{
    private array $messages = [];
    private string $systemPrompt;
    private TokenCounter $tokenCounter;
    private int $maxContextTokens;

    public function __construct(
        string $systemPrompt,
        string $model = 'gpt-3.5-turbo',
        int $maxContextTokens = 4000
    ) {
        $this->systemPrompt = $systemPrompt;
        $this->tokenCounter = new TokenCounter($model);
        $this->maxContextTokens = $maxContextTokens;

        // Initialize with system message
        $this->messages[] = [
            'role' => 'system',
            'content' => $systemPrompt
        ];
    }

    public function addUserMessage(string $content): void
    {
        $this->messages[] = [
            'role' => 'user',
            'content' => $content
        ];

        $this->trimContext();
    }

    public function addAssistantMessage(string $content): void
    {
        $this->messages[] = [
            'role' => 'assistant',
            'content' => $content
        ];

        $this->trimContext();
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getLastUserMessage(): ?string
    {
        $userMessages = array_filter(
            $this->messages,
            fn($m) => $m['role'] === 'user'
        );

        if (empty($userMessages)) {
            return null;
        }

        return end($userMessages)['content'];
    }

    public function getLastAssistantMessage(): ?string
    {
        $assistantMessages = array_filter(
            $this->messages,
            fn($m) => $m['role'] === 'assistant'
        );

        if (empty($assistantMessages)) {
            return null;
        }

        return end($assistantMessages)['content'];
    }

    public function getConversationLength(): int
    {
        return count($this->messages) - 1; // Exclude system message
    }

    public function getTokenCount(): int
    {
        return $this->tokenCounter->countMessages($this->messages);
    }

    private function trimContext(): void
    {
        $currentTokens = $this->getTokenCount();

        // Keep system message, trim oldest user/assistant pairs
        while ($currentTokens > $this->maxContextTokens && count($this->messages) > 3) {
            // Remove second and third messages (first user/assistant pair)
            array_splice($this->messages, 1, 2);
            $currentTokens = $this->getTokenCount();
        }
    }

    public function reset(): void
    {
        $this->messages = [
            [
                'role' => 'system',
                'content' => $this->systemPrompt
            ]
        ];
    }

    public function summarize(): array
    {
        return [
            'total_messages' => count($this->messages),
            'user_messages' => count(array_filter(
                $this->messages,
                fn($m) => $m['role'] === 'user'
            )),
            'assistant_messages' => count(array_filter(
                $this->messages,
                fn($m) => $m['role'] === 'assistant'
            )),
            'token_count' => $this->getTokenCount(),
            'token_limit' => $this->maxContextTokens,
            'usage_percentage' => round(
                ($this->getTokenCount() / $this->maxContextTokens) * 100,
                1
            ),
        ];
    }
}

// Usage
$conversation = new ConversationManager(
    systemPrompt: 'You are a helpful PHP programming assistant.',
    model: 'gpt-3.5-turbo',
    maxContextTokens: 4000
);

$conversation->addUserMessage('What is dependency injection?');
// Make API call, get response
$conversation->addAssistantMessage('Dependency injection is a design pattern...');

$conversation->addUserMessage('Show me an example.');
// Get messages for API call
$messages = $conversation->getMessages();

print_r($conversation->summarize());
```

### Complete Chat Flow Example

```php
<?php

/**
 * Complete conversational chat implementation
 */

class ChatBot
{
    private ConversationManager $conversation;
    private \OpenAI\Client $client;
    private string $model;

    public function __construct(
        string $systemPrompt,
        string $apiKey,
        string $model = 'gpt-3.5-turbo'
    ) {
        $this->conversation = new ConversationManager($systemPrompt, $model);
        $this->client = \OpenAI::client($apiKey);
        $this->model = $model;
    }

    public function chat(string $userMessage, array $options = []): string
    {
        // Add user message to conversation
        $this->conversation->addUserMessage($userMessage);

        // Prepare API request
        $params = array_merge([
            'model' => $this->model,
            'messages' => $this->conversation->getMessages(),
            'temperature' => 0.7,
            'max_tokens' => 500,
        ], $options);

        try {
            // Make API call
            $response = $this->client->chat()->create($params);

            // Extract assistant's message
            $assistantMessage = $response->choices[0]->message->content;

            // Add to conversation history
            $this->conversation->addAssistantMessage($assistantMessage);

            return $assistantMessage;

        } catch (\Exception $e) {
            error_log("Chat error: " . $e->getMessage());
            throw $e;
        }
    }

    public function streamChat(string $userMessage, callable $callback): void
    {
        $this->conversation->addUserMessage($userMessage);

        $stream = $this->client->chat()->createStreamed([
            'model' => $this->model,
            'messages' => $this->conversation->getMessages(),
        ]);

        $fullResponse = '';

        foreach ($stream as $chunk) {
            $content = $chunk->choices[0]->delta->content ?? '';
            $fullResponse .= $content;
            $callback($content);
        }

        $this->conversation->addAssistantMessage($fullResponse);
    }

    public function getConversation(): ConversationManager
    {
        return $this->conversation;
    }

    public function reset(): void
    {
        $this->conversation->reset();
    }
}

// Usage
$bot = new ChatBot(
    systemPrompt: 'You are a helpful programming assistant specializing in PHP.',
    apiKey: $_ENV['OPENAI_API_KEY']
);

// Simple chat
$response = $bot->chat('What is a closure in PHP?');
echo "Bot: $response\n\n";

// Follow-up question
$response = $bot->chat('Can you show me an example?');
echo "Bot: $response\n\n";

// Check conversation stats
print_r($bot->getConversation()->summarize());

// Streaming example
echo "Streaming response:\n";
$bot->streamChat('Explain traits in PHP', function($chunk) {
    echo $chunk;
    flush();
});
echo "\n";
```

---

## Response Formatting

### Controlling Output Structure

```php
<?php

/**
 * Response formatting utilities
 */

class ResponseFormatter
{
    public static function asList(string $topic, int $items = 5): string
    {
        return "Provide exactly {$items} key points about {$topic}. " .
               "Format as a numbered list with brief explanations for each point.";
    }

    public static function asSteps(string $task): string
    {
        return "Explain how to {$task} as a step-by-step guide. " .
               "Number each step and keep instructions clear and concise.";
    }

    public static function asComparison(array $items): string
    {
        $itemList = implode(' and ', $items);
        return "Compare and contrast {$itemList}. " .
               "Present the comparison in a structured format with " .
               "pros and cons for each.";
    }

    public static function asExample(string $concept, string $language = 'PHP'): string
    {
        return "Explain {$concept} with a practical {$language} code example. " .
               "Include comments explaining each part of the code.";
    }

    public static function asSummary(int $sentences = 3): string
    {
        return "Summarize the above in exactly {$sentences} sentences. " .
               "Focus on the most important points.";
    }

    public static function withConstraints(array $constraints): string
    {
        $formatted = [];
        foreach ($constraints as $constraint) {
            $formatted[] = "- {$constraint}";
        }

        return "Follow these requirements:\n" . implode("\n", $formatted);
    }
}

// Usage examples
$messages = [
    [
        'role' => 'system',
        'content' => 'You are a PHP programming instructor.'
    ],
    [
        'role' => 'user',
        'content' => ResponseFormatter::asSteps('set up a Laravel project')
    ]
];

// With constraints
$prompt = "Explain REST APIs. " . ResponseFormatter::withConstraints([
    'Keep it under 150 words',
    'Use simple language',
    'Include one real-world example',
    'Avoid jargon'
]);
```

### JSON Response Format

```php
<?php

/**
 * Requesting structured JSON responses
 */

class JsonResponseHandler
{
    public static function requestJson(array $schema): string
    {
        return "Respond with valid JSON matching this structure: " .
               json_encode($schema, JSON_PRETTY_PRINT) .
               "\nOnly return the JSON, no other text.";
    }

    public static function parseJsonResponse(string $response): ?array
    {
        // Extract JSON from response (handles markdown code blocks)
        $json = $response;

        // Remove markdown code fences if present
        $json = preg_replace('/```json\s*/', '', $json);
        $json = preg_replace('/```\s*$/', '', $json);
        $json = trim($json);

        try {
            return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            error_log("JSON parse error: " . $e->getMessage());
            return null;
        }
    }
}

// Example: Request structured data
$schema = [
    'name' => 'string',
    'description' => 'string',
    'difficulty' => 'beginner|intermediate|advanced',
    'topics' => ['array', 'of', 'strings'],
    'estimated_hours' => 'number'
];

$messages = [
    [
        'role' => 'system',
        'content' => 'You are a curriculum designer. Respond only with valid JSON.'
    ],
    [
        'role' => 'user',
        'content' => "Create a PHP course outline. " .
                     JsonResponseHandler::requestJson($schema)
    ]
];

// After getting response
$response = "..."; // From API
$data = JsonResponseHandler::parseJsonResponse($response);

if ($data) {
    echo "Course: {$data['name']}\n";
    echo "Topics: " . implode(', ', $data['topics']) . "\n";
}
```

---

## Common Conversation Patterns

### Question-Answer Pattern

```php
<?php

class QAAssistant
{
    private ChatBot $bot;

    public function __construct(string $domain, string $apiKey)
    {
        $systemPrompt = "You are an expert in {$domain}. " .
                        "Provide accurate, concise answers. " .
                        "If you don't know something, say so.";

        $this->bot = new ChatBot($systemPrompt, $apiKey);
    }

    public function ask(string $question): array
    {
        $response = $this->bot->chat($question);

        return [
            'question' => $question,
            'answer' => $response,
            'confidence' => $this->estimateConfidence($response),
        ];
    }

    private function estimateConfidence(string $response): string
    {
        $uncertainPhrases = [
            'i think', 'probably', 'might be', 'possibly',
            'not sure', 'i don\'t know', 'unclear'
        ];

        $responseLower = strtolower($response);

        foreach ($uncertainPhrases as $phrase) {
            if (str_contains($responseLower, $phrase)) {
                return 'low';
            }
        }

        return 'high';
    }
}
```

### Multi-Step Task Pattern

```php
<?php

class TaskAssistant
{
    private ChatBot $bot;
    private array $steps = [];

    public function __construct(string $apiKey)
    {
        $systemPrompt = "You are a helpful task assistant. " .
                        "Break down complex tasks into clear, actionable steps.";

        $this->bot = new ChatBot($systemPrompt, $apiKey);
    }

    public function planTask(string $task): array
    {
        $prompt = "Break down this task into clear steps: {$task}\n\n" .
                  "Format as:\n" .
                  "1. [Step description]\n" .
                  "2. [Step description]\n" .
                  "etc.";

        $response = $this->bot->chat($prompt);

        // Parse steps
        preg_match_all('/(\d+)\.\s*(.+?)(?=\n\d+\.|$)/s', $response, $matches);

        $this->steps = array_map(
            fn($step) => trim($step),
            $matches[2]
        );

        return $this->steps;
    }

    public function executeStep(int $stepNumber, ?string $question = null): string
    {
        if (!isset($this->steps[$stepNumber - 1])) {
            throw new \InvalidArgumentException("Invalid step number");
        }

        $step = $this->steps[$stepNumber - 1];

        $prompt = $question
            ? "For step {$stepNumber} ({$step}), {$question}"
            : "Provide detailed instructions for step {$stepNumber}: {$step}";

        return $this->bot->chat($prompt);
    }
}

// Usage
$assistant = new TaskAssistant($_ENV['OPENAI_API_KEY']);

$steps = $assistant->planTask('Set up a new Laravel project with authentication');

echo "Steps:\n";
foreach ($steps as $i => $step) {
    echo ($i + 1) . ". $step\n";
}

// Get details for a specific step
$details = $assistant->executeStep(1, "What prerequisites do I need?");
echo "\nStep 1 details:\n$details\n";
```

---

## Exercises

### Exercise 1: Personality Customization

Create a system that:
1. Allows users to choose assistant personality
2. Dynamically builds system prompts
3. Maintains consistency across conversation
4. Switches personality mid-conversation

### Exercise 2: Context Summarization

Implement:
1. Automatic conversation summarization
2. Replace old messages with summaries
3. Maintain key information
4. Reduce token usage

### Exercise 3: Multi-User Chat

Build:
1. Separate conversations per user
2. User identification system
3. Conversation persistence
4. User history tracking

### Exercise 4: Advanced Response Parser

Create a parser that:
1. Extracts structured data from responses
2. Handles multiple formats (JSON, markdown, plain text)
3. Validates extracted data
4. Provides fallbacks for parsing failures

---

## Key Takeaways

- ✅ System messages define AI behavior and should be carefully crafted
- ✅ User and assistant messages build conversation context
- ✅ Conversation history must be managed to stay within token limits
- ✅ Response formatting can be controlled through clear instructions
- ✅ Multi-turn conversations require careful state management
- ✅ Different use cases benefit from different conversation patterns
- ✅ Token counting is crucial for conversation management
- ✅ System prompts can dramatically affect output quality

---

## Next Steps

With Chat Completions mastery, you're ready to level up with prompt engineering!

👉 **[Chapter 08: Prompt Engineering Essentials](/series/openai-php/chapters/08-prompt-engineering-essentials)**

In the next chapter, you'll learn:
- Advanced prompting techniques
- Few-shot learning strategies
- Chain-of-thought prompting
- Prompt optimization methods

---

[← Previous: Chapter 06](/series/openai-php/chapters/06-working-with-tokens) | [Next: Chapter 08 →](/series/openai-php/chapters/08-prompt-engineering-essentials)
