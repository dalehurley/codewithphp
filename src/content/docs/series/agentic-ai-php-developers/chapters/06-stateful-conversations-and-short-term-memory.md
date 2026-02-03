---
title: "06: Stateful Conversations and Short-Term Memory"
description: "Implement session storage, context windows, summarization, and transcript pruning. Keep agents coherent over long interactions."
series: "agentic-ai-php-developers"
chapter: 6
difficulty: "Intermediate"
keywords: ["Conversation State PHP", "Session Management", "Context Window", "Summarization", "Transcript Pruning", "Stateful Agents"]
teaches: ["Session management for stateful agents", "Context window strategies", "AI-powered conversation summarization", "Transcript pruning and compression", "Multi-turn conversation handling", "State persistence patterns"]
estimatedTime: "PT120M"
---

# Chapter 06: Stateful Conversations and Short-Term Memory

## Overview

In Chapter 05, you built production-ready tool execution pipelines. But tools alone don't make an agent feel intelligent—**stateful conversations** do. When an agent remembers what you discussed two messages ago, references earlier context, and maintains coherence over dozens of turns, that's when it becomes truly useful.

This is where **short-term memory** comes in: the ability to maintain conversation state, manage context windows, summarize long histories, and prune transcripts intelligently. Without these capabilities, your agent starts every interaction from scratch—frustrating users and wasting resources.

The [`claude-php/agent`](https://github.com/claude-php/claude-php-agent) framework provides comprehensive conversation management with the `Conversation` namespace: `Session`, `Turn`, `ConversationManager`, storage adapters, and AI-powered summarizers. In this chapter, you'll learn to use these tools to build agents that maintain coherent, context-aware conversations.

In this chapter you'll:

- Understand **conversation state** and why it matters for agentic AI
- Implement **session management** with the framework's `Session` and `ConversationManager`
- Design **context window strategies** to fit within model token limits
- Add **AI-powered summarization** to compress long conversation histories
- Build **transcript pruning** to keep only relevant context
- Apply **production patterns** for stateful agent deployments
- Handle **multi-user conversations** with proper isolation

**Estimated time:** ~120 minutes

::: info Framework Version
This chapter is based on [`claude-php/agent`](https://github.com/claude-php/claude-php-agent) **v0.5+**. We'll use the framework's `Conversation` components extensively.
:::

::: info Code examples
Complete, runnable examples for this chapter:

- [`basic-session-management.php`](../../code/agentic-ai-php-developers/06-stateful-conversations/basic-session-management.php) — Creating and managing sessions
- [`context-window-management.php`](../../code/agentic-ai-php-developers/06-stateful-conversations/context-window-management.php) — Context window strategies
- [`ai-summarization.php`](../../code/agentic-ai-php-developers/06-stateful-conversations/ai-summarization.php) — AI-powered conversation summarization
- [`transcript-pruning.php`](../../code/agentic-ai-php-developers/06-stateful-conversations/transcript-pruning.php) — Intelligent transcript pruning
- [`persistent-storage.php`](../../code/agentic-ai-php-developers/06-stateful-conversations/persistent-storage.php) — File-based session persistence
- [`stateful-agent-complete.php`](../../code/agentic-ai-php-developers/06-stateful-conversations/stateful-agent-complete.php) — Complete stateful agent implementation
- [`multi-user-conversations.php`](../../code/agentic-ai-php-developers/06-stateful-conversations/multi-user-conversations.php) — Multi-user conversation handling

All files are in [`code/agentic-ai-php-developers/06-stateful-conversations/`](../../code/agentic-ai-php-developers/06-stateful-conversations/README.md).
:::

---

## Understanding Conversation State

Before diving into code, let's understand what conversation state means for agents.

### The Problem: Stateless vs Stateful

**Stateless Agent:**

```text
User: What's the weather in San Francisco?
Agent: It's 72°F and sunny.

User: What about tomorrow?
Agent: ❌ I don't know which city you're asking about.
```

**Stateful Agent:**

```text
User: What's the weather in San Francisco?
Agent: It's 72°F and sunny.

User: What about tomorrow?
Agent: ✅ Tomorrow in San Francisco will be 68°F with clouds.
```

The stateful agent **remembers the context** (San Francisco) from the previous turn.

### What is Short-Term Memory?

Short-term memory (also called **working memory** or **conversation memory**) is the agent's ability to:

1. **Remember recent turns** — What was said in the last 5-10 messages
2. **Maintain context** — Track entities, topics, and user preferences within the session
3. **Reference earlier statements** — "As I mentioned before..."
4. **Stay coherent** — Avoid contradicting itself or repeating information

### The Context Window Challenge

LLMs have finite context windows:

- **Claude 3.5 Sonnet:** 200,000 tokens (~150,000 words)
- **Claude 3 Opus:** 200,000 tokens
- **Claude 3 Haiku:** 200,000 tokens

But even with large windows, you face trade-offs:

- **Cost:** More tokens = higher API costs
- **Latency:** Longer prompts = slower responses
- **Relevance:** Old context may confuse the agent

**Strategy:** Keep only the most relevant recent context, and summarize or prune older turns.

---

## Session Management Basics

The framework provides `Session` and `Turn` classes for conversation tracking.

### Core Components

```text
┌─────────────────────────────────────────────────────────────────┐
│                    CONVERSATION COMPONENTS                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Session                                                         │
│    ├─ Unique ID                                                 │
│    ├─ Creation timestamp                                        │
│    ├─ Last activity                                             │
│    ├─ State (key-value data)                                    │
│    └─ Turns (conversation history)                              │
│                                                                  │
│  Turn                                                            │
│    ├─ Unique ID                                                 │
│    ├─ User input                                                │
│    ├─ Agent response                                            │
│    ├─ Metadata (tokens, tools used, etc.)                       │
│    └─ Timestamp                                                 │
│                                                                  │
│  ConversationManager                                             │
│    ├─ Creates sessions                                          │
│    ├─ Loads/saves sessions                                      │
│    ├─ Manages multiple users                                    │
│    └─ Cleanup expired sessions                                  │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Creating a Session

```php
<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use ClaudeAgents\Conversation\Session;
use ClaudeAgents\Conversation\Turn;

// Create a new session
$session = new Session();

echo "Session ID: {$session->getId()}\n";
echo "Created at: " . date('Y-m-d H:i:s', (int)$session->getCreatedAt()) . "\n";

// Add some state
$session->updateState('user_id', 'user_123');
$session->updateState('language', 'en');
$session->updateState('topic', 'weather');

// Add a turn
$turn1 = new Turn(
    userInput: "What's the weather in San Francisco?",
    agentResponse: "It's currently 72°F and sunny in San Francisco.",
    metadata: ['tokens' => 45, 'model' => 'claude-sonnet-4-5']
);

$session->addTurn($turn1);

// Add another turn
$turn2 = new Turn(
    userInput: "What about tomorrow?",
    agentResponse: "Tomorrow in San Francisco will be 68°F with clouds.",
    metadata: ['tokens' => 38, 'model' => 'claude-sonnet-4-5']
);

$session->addTurn($turn2);

// Inspect session
echo "\nTurn count: {$session->getTurnCount()}\n";
echo "Session state:\n";
print_r($session->getState());

// Access turns
foreach ($session->getTurns() as $i => $turn) {
    echo "\nTurn " . ($i + 1) . ":\n";
    echo "  User: {$turn->getUserInput()}\n";
    echo "  Agent: {$turn->getAgentResponse()}\n";
}
```

**Output:**

```text
Session ID: session_65a1b2c3d4e5f
Created at: 2026-02-03 14:30:00

Turn count: 2
Session state:
Array
(
    [user_id] => user_123
    [language] => en
    [topic] => weather
)

Turn 1:
  User: What's the weather in San Francisco?
  Agent: It's currently 72°F and sunny in San Francisco.

Turn 2:
  User: What about tomorrow?
  Agent: Tomorrow in San Francisco will be 68°F with clouds.
```

### Using ConversationManager

For production, use `ConversationManager` to handle multiple sessions:

```php
<?php

use ClaudeAgents\Conversation\ConversationManager;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('conversations');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));

$manager = new ConversationManager([
    'max_sessions' => 1000,
    'session_timeout' => 3600, // 1 hour
    'logger' => $logger,
]);

// Create sessions for different users
$session1 = $manager->createSession('user_123');
$session2 = $manager->createSession('user_456');

echo "Created session for user_123: {$session1->getId()}\n";
echo "Created session for user_456: {$session2->getId()}\n";

// Retrieve session
$retrieved = $manager->getSession($session1->getId());

if ($retrieved) {
    echo "Retrieved session: {$retrieved->getId()}\n";
}

// Get all sessions for a user
$userSessions = $manager->getSessionsByUser('user_123');
echo "User sessions: " . count($userSessions) . "\n";

// Delete session
$manager->deleteSession($session2->getId());
echo "Deleted session: {$session2->getId()}\n";
```

---

## Context Window Management

As conversations grow, you need strategies to manage context within token limits.

### Strategy 1: Sliding Window (Keep Last N Turns)

The simplest approach: keep only the most recent N turns.

```php
<?php

class SlidingWindowContext
{
    public function __construct(
        private int $maxTurns = 10,
    ) {}
    
    /**
     * Get the last N turns for context.
     */
    public function getContext(Session $session): array
    {
        $turns = $session->getTurns();
        
        // Keep only last N turns
        $recentTurns = array_slice($turns, -$this->maxTurns);
        
        return $this->formatForAPI($recentTurns);
    }
    
    /**
     * Format turns for Claude API messages.
     */
    private function formatForAPI(array $turns): array
    {
        $messages = [];
        
        foreach ($turns as $turn) {
            $messages[] = [
                'role' => 'user',
                'content' => $turn->getUserInput(),
            ];
            
            $messages[] = [
                'role' => 'assistant',
                'content' => $turn->getAgentResponse(),
            ];
        }
        
        return $messages;
    }
    
    /**
     * Calculate total tokens in context.
     */
    public function estimateTokens(array $messages): int
    {
        // Rough estimation: 1 token ≈ 4 characters
        $totalChars = 0;
        
        foreach ($messages as $message) {
            $totalChars += strlen($message['content']);
        }
        
        return (int)ceil($totalChars / 4);
    }
}

// Usage
$contextManager = new SlidingWindowContext(maxTurns: 5);

$messages = $contextManager->getContext($session);
$tokenEstimate = $contextManager->estimateTokens($messages);

echo "Context messages: " . count($messages) . "\n";
echo "Estimated tokens: {$tokenEstimate}\n";
```

### Strategy 2: Token-Based Window

Keep turns until you hit a token limit:

```php
<?php

class TokenBasedContext
{
    public function __construct(
        private int $maxTokens = 4000,
    ) {}
    
    /**
     * Get context within token budget.
     */
    public function getContext(Session $session): array
    {
        $turns = array_reverse($session->getTurns()); // Start from most recent
        $messages = [];
        $totalTokens = 0;
        
        foreach ($turns as $turn) {
            // Estimate tokens for this turn
            $userTokens = $this->estimateTokens($turn->getUserInput());
            $assistantTokens = $this->estimateTokens($turn->getAgentResponse());
            $turnTokens = $userTokens + $assistantTokens;
            
            // Check if adding this turn would exceed budget
            if ($totalTokens + $turnTokens > $this->maxTokens) {
                break;
            }
            
            // Add turn (prepend since we're going in reverse)
            array_unshift($messages, [
                'role' => 'assistant',
                'content' => $turn->getAgentResponse(),
            ]);
            
            array_unshift($messages, [
                'role' => 'user',
                'content' => $turn->getUserInput(),
            ]);
            
            $totalTokens += $turnTokens;
        }
        
        return $messages;
    }
    
    private function estimateTokens(string $text): int
    {
        // Rough estimation: 1 token ≈ 4 characters
        return (int)ceil(strlen($text) / 4);
    }
}
```

### Strategy 3: Hybrid (Recent + Summary)

Keep recent turns + a summary of older context:

```php
<?php

class HybridContextStrategy
{
    public function __construct(
        private int $recentTurns = 5,
        private int $maxTotalTokens = 8000,
    ) {}
    
    /**
     * Build context with recent turns + summary of older turns.
     */
    public function getContext(Session $session, ?string $summary = null): array
    {
        $allTurns = $session->getTurns();
        
        if (count($allTurns) <= $this->recentTurns) {
            // No need for summary yet
            return $this->formatTurns($allTurns);
        }
        
        // Split into old + recent
        $recentTurns = array_slice($allTurns, -$this->recentTurns);
        
        $messages = [];
        
        // Add summary of older context if provided
        if ($summary) {
            $messages[] = [
                'role' => 'user',
                'content' => "Previous conversation summary: {$summary}",
            ];
            $messages[] = [
                'role' => 'assistant',
                'content' => "I understand. I'll use this context for our conversation.",
            ];
        }
        
        // Add recent turns
        foreach ($recentTurns as $turn) {
            $messages[] = [
                'role' => 'user',
                'content' => $turn->getUserInput(),
            ];
            $messages[] = [
                'role' => 'assistant',
                'content' => $turn->getAgentResponse(),
            ];
        }
        
        return $messages;
    }
    
    private function formatTurns(array $turns): array
    {
        $messages = [];
        
        foreach ($turns as $turn) {
            $messages[] = ['role' => 'user', 'content' => $turn->getUserInput()];
            $messages[] = ['role' => 'assistant', 'content' => $turn->getAgentResponse()];
        }
        
        return $messages;
    }
}
```

---

## AI-Powered Summarization

The framework provides `AIConversationSummarizer` for intelligent summarization.

### Basic Summarization

```php
<?php

use ClaudeAgents\Conversation\Summarization\AIConversationSummarizer;
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));
$summarizer = new AIConversationSummarizer($client);

// Summarize the session
$summary = $summarizer->summarize($session, [
    'max_tokens' => 300,
    'style' => 'concise', // 'concise', 'detailed', 'bullet_points'
]);

echo "Summary:\n{$summary}\n";

// Extract main topics
$topics = $summarizer->extractTopics($session, maxTopics: 5);
echo "\nMain Topics:\n";
foreach ($topics as $topic) {
    echo "  - {$topic}\n";
}
```

**Example output:**

```text
Summary:
The user inquired about the weather in San Francisco, learning it's currently 72°F and sunny. They then asked about tomorrow's forecast, which will be cooler at 68°F with clouds.

Main Topics:
  - Weather conditions
  - San Francisco
  - Temperature forecasts
  - Tomorrow's weather
  - Cloud coverage
```

### Summarizing at Intervals

Summarize every N turns to keep context manageable:

```php
<?php

class AutoSummarizingSession
{
    private Session $session;
    private AIConversationSummarizer $summarizer;
    private ?string $currentSummary = null;
    private int $summarizeInterval = 10;
    
    public function __construct(
        Session $session,
        AIConversationSummarizer $summarizer,
        int $summarizeInterval = 10
    ) {
        $this->session = $session;
        $this->summarizer = $summarizer;
        $this->summarizeInterval = $summarizeInterval;
    }
    
    /**
     * Add a turn and auto-summarize if needed.
     */
    public function addTurn(Turn $turn): void
    {
        $this->session->addTurn($turn);
        
        // Check if we should summarize
        if ($this->session->getTurnCount() % $this->summarizeInterval === 0) {
            $this->updateSummary();
        }
    }
    
    /**
     * Update the conversation summary.
     */
    private function updateSummary(): void
    {
        echo "Generating summary after {$this->session->getTurnCount()} turns...\n";
        
        $this->currentSummary = $this->summarizer->summarize(
            $this->session,
            ['style' => 'concise', 'max_tokens' => 200]
        );
        
        echo "Summary updated.\n";
    }
    
    /**
     * Get context with summary.
     */
    public function getContextWithSummary(int $recentTurns = 5): array
    {
        $strategy = new HybridContextStrategy($recentTurns);
        return $strategy->getContext($this->session, $this->currentSummary);
    }
    
    public function getSummary(): ?string
    {
        return $this->currentSummary;
    }
}
```

---

## Transcript Pruning

Intelligent pruning removes less relevant turns while keeping important context.

### Importance-Based Pruning

```php
<?php

class TranscriptPruner
{
    /**
     * Prune turns based on importance scores.
     */
    public function pruneByImportance(
        Session $session,
        int $keepCount,
        array $importanceScores = []
    ): array {
        $turns = $session->getTurns();
        $turnCount = count($turns);
        
        if ($turnCount <= $keepCount) {
            return $turns; // No pruning needed
        }
        
        // Always keep most recent turns
        $recentKeep = min(3, $keepCount);
        $recentTurns = array_slice($turns, -$recentKeep);
        $remainingTurns = array_slice($turns, 0, -$recentKeep);
        
        // Score remaining turns
        $scoredTurns = [];
        foreach ($remainingTurns as $i => $turn) {
            $score = $importanceScores[$i] ?? $this->calculateImportance($turn);
            $scoredTurns[] = ['turn' => $turn, 'score' => $score, 'index' => $i];
        }
        
        // Sort by importance (descending)
        usort($scoredTurns, fn($a, $b) => $b['score'] <=> $a['score']);
        
        // Keep top N important turns
        $keepFromOld = $keepCount - $recentKeep;
        $importantTurns = array_slice($scoredTurns, 0, $keepFromOld);
        
        // Sort by original order
        usort($importantTurns, fn($a, $b) => $a['index'] <=> $b['index']);
        
        // Combine important + recent
        $prunedTurns = array_merge(
            array_map(fn($st) => $st['turn'], $importantTurns),
            $recentTurns
        );
        
        return $prunedTurns;
    }
    
    /**
     * Calculate importance score for a turn.
     */
    private function calculateImportance(Turn $turn): float
    {
        $score = 0.0;
        
        $input = strtolower($turn->getUserInput());
        $response = strtolower($turn->getAgentResponse());
        
        // Important keywords
        $importantKeywords = ['name', 'email', 'phone', 'address', 'important', 'remember'];
        foreach ($importantKeywords as $keyword) {
            if (str_contains($input, $keyword) || str_contains($response, $keyword)) {
                $score += 10.0;
            }
        }
        
        // Questions get higher importance
        if (str_contains($input, '?')) {
            $score += 5.0;
        }
        
        // Longer responses may be more informative
        $score += min(strlen($response) / 100, 5.0);
        
        // Check metadata for tool use (important interactions)
        $metadata = $turn->getMetadata();
        if (isset($metadata['tools_used']) && !empty($metadata['tools_used'])) {
            $score += 15.0;
        }
        
        return $score;
    }
}

// Usage
$pruner = new TranscriptPruner();
$prunedTurns = $pruner->pruneByImportance($session, keepCount: 5);

echo "Original turns: {$session->getTurnCount()}\n";
echo "Pruned turns: " . count($prunedTurns) . "\n";
```

### Time-Based Pruning

Keep turns from the last N minutes/hours:

```php
<?php

class TimeBasedPruner
{
    /**
     * Keep only turns within the time window.
     */
    public function pruneByTime(Session $session, int $maxAgeSeconds = 3600): array
    {
        $now = microtime(true);
        $cutoff = $now - $maxAgeSeconds;
        
        return array_filter(
            $session->getTurns(),
            fn($turn) => $turn->getTimestamp() > $cutoff
        );
    }
}
```

---

## Persistent Storage

Use `FileSessionStorage` to persist sessions to disk.

### File-Based Persistence

```php
<?php

use ClaudeAgents\Conversation\Storage\FileSessionStorage;
use ClaudeAgents\Conversation\Storage\JsonSessionSerializer;
use ClaudeAgents\Conversation\ConversationManager;

// Setup storage
$storageDir = __DIR__ . '/sessions';
$serializer = new JsonSessionSerializer();
$storage = new FileSessionStorage($storageDir, $serializer);

// Create manager with storage
$manager = new ConversationManager([
    'storage' => $storage,
    'session_timeout' => 3600,
]);

// Create and save session
$session = $manager->createSession('user_123');
$session->updateState('language', 'en');

// Add turns
$turn = new Turn(
    userInput: "Hello!",
    agentResponse: "Hi! How can I help you?"
);
$session->addTurn($turn);

// Save to storage
$manager->saveSession($session);
echo "Session saved: {$session->getId()}\n";

// Later: load session
$loaded = $manager->getSession($session->getId());
if ($loaded) {
    echo "Session loaded: {$loaded->getId()}\n";
    echo "Turn count: {$loaded->getTurnCount()}\n";
}

// List all sessions
$allSessions = $storage->listSessions();
echo "Total sessions: " . count($allSessions) . "\n";
```

### Database Storage (Custom Implementation)

For production, implement `SessionStorageInterface` with your database:

```php
<?php

use ClaudeAgents\Contracts\SessionStorageInterface;

class DatabaseSessionStorage implements SessionStorageInterface
{
    private PDO $pdo;
    
    public function __construct(string $dsn, string $username = '', string $password = '')
    {
        $this->pdo = new PDO($dsn, $username, $password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->createTablesIfNeeded();
    }
    
    public function save(Session $session): bool
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO sessions (id, user_id, state, turns, created_at, last_activity)
            VALUES (:id, :user_id, :state, :turns, :created_at, :last_activity)
            ON DUPLICATE KEY UPDATE
                state = :state,
                turns = :turns,
                last_activity = :last_activity
        ");
        
        return $stmt->execute([
            'id' => $session->getId(),
            'user_id' => $session->getState()['user_id'] ?? null,
            'state' => json_encode($session->getState()),
            'turns' => json_encode(array_map(fn($t) => $t->toArray(), $session->getTurns())),
            'created_at' => $session->getCreatedAt(),
            'last_activity' => $session->getLastActivity() ?? $session->getCreatedAt(),
        ]);
    }
    
    public function load(string $sessionId): ?Session
    {
        $stmt = $this->pdo->prepare("SELECT * FROM sessions WHERE id = :id");
        $stmt->execute(['id' => $sessionId]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        
        // Reconstruct session
        $session = new Session($row['id']);
        $session->setState(json_decode($row['state'], true) ?? []);
        
        $turnsData = json_decode($row['turns'], true) ?? [];
        foreach ($turnsData as $turnData) {
            $turn = new Turn(
                $turnData['user_input'],
                $turnData['agent_response'],
                $turnData['metadata'] ?? []
            );
            $session->addTurn($turn);
        }
        
        return $session;
    }
    
    public function delete(string $sessionId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE id = :id");
        return $stmt->execute(['id' => $sessionId]);
    }
    
    public function exists(string $sessionId): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM sessions WHERE id = :id");
        $stmt->execute(['id' => $sessionId]);
        return $stmt->fetchColumn() > 0;
    }
    
    public function listSessions(): array
    {
        $stmt = $this->pdo->query("SELECT id FROM sessions");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function findByUser(string $userId): array
    {
        $stmt = $this->pdo->prepare("SELECT id FROM sessions WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        
        $sessions = [];
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $sessionId) {
            $session = $this->load($sessionId);
            if ($session) {
                $sessions[] = $session;
            }
        }
        
        return $sessions;
    }
    
    private function createTablesIfNeeded(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS sessions (
                id VARCHAR(255) PRIMARY KEY,
                user_id VARCHAR(255),
                state TEXT,
                turns LONGTEXT,
                created_at DOUBLE,
                last_activity DOUBLE,
                INDEX idx_user_id (user_id),
                INDEX idx_last_activity (last_activity)
            )
        ");
    }
}
```

---

## Complete Stateful Agent

Putting it all together—a production-ready stateful agent:

```php
<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Conversation\ConversationManager;
use ClaudeAgents\Conversation\Session;
use ClaudeAgents\Conversation\Turn;
use ClaudeAgents\Conversation\Summarization\AIConversationSummarizer;
use ClaudeAgents\Conversation\Storage\FileSessionStorage;
use ClaudeAgents\Conversation\Storage\JsonSessionSerializer;
use ClaudePhp\ClaudePhp;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class StatefulAgent
{
    private Agent $agent;
    private ConversationManager $conversationManager;
    private AIConversationSummarizer $summarizer;
    private HybridContextStrategy $contextStrategy;
    private Logger $logger;
    
    public function __construct(
        string $apiKey,
        string $storageDir = '/tmp/sessions'
    ) {
        // Setup logger
        $this->logger = new Logger('stateful-agent');
        $this->logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));
        
        // Setup Claude client
        $client = new ClaudePhp(apiKey: $apiKey);
        
        // Setup conversation manager with persistent storage
        $storage = new FileSessionStorage(
            $storageDir,
            new JsonSessionSerializer()
        );
        
        $this->conversationManager = new ConversationManager([
            'storage' => $storage,
            'session_timeout' => 3600,
            'logger' => $this->logger,
        ]);
        
        // Setup summarizer
        $this->summarizer = new AIConversationSummarizer($client);
        
        // Setup context strategy
        $this->contextStrategy = new HybridContextStrategy(
            recentTurns: 5,
            maxTotalTokens: 8000
        );
        
        // Create agent
        $this->agent = Agent::create($client)
            ->withSystemPrompt('You are a helpful assistant with memory of our conversation.');
    }
    
    /**
     * Start or resume a conversation.
     */
    public function getOrCreateSession(string $userId): Session
    {
        // Try to find existing session for user
        $sessions = $this->conversationManager->getSessionsByUser($userId);
        
        if (!empty($sessions)) {
            $session = $sessions[0];
            $this->logger->info("Resumed session", ['session_id' => $session->getId()]);
            return $session;
        }
        
        // Create new session
        $session = $this->conversationManager->createSession($userId);
        $this->logger->info("Created new session", ['session_id' => $session->getId()]);
        
        return $session;
    }
    
    /**
     * Chat with the agent (stateful).
     */
    public function chat(Session $session, string $userInput): string
    {
        $this->logger->info("User input", ['message' => $userInput]);
        
        // Get context with summary if conversation is long
        $summary = null;
        if ($session->getTurnCount() >= 10) {
            $summary = $this->summarizer->summarize($session, [
                'style' => 'concise',
                'max_tokens' => 200,
            ]);
        }
        
        $contextMessages = $this->contextStrategy->getContext($session, $summary);
        
        // Add current user input
        $contextMessages[] = [
            'role' => 'user',
            'content' => $userInput,
        ];
        
        // Get agent response
        $response = $this->agent->run($userInput, [
            'context_messages' => $contextMessages,
        ]);
        
        $agentResponse = $response->getAnswer();
        
        $this->logger->info("Agent response", ['message' => $agentResponse]);
        
        // Save turn
        $turn = new Turn(
            userInput: $userInput,
            agentResponse: $agentResponse,
            metadata: [
                'tokens' => $response->getMetadata()['usage']['input_tokens'] ?? 0,
                'model' => 'claude-sonnet-4-5',
            ]
        );
        
        $session->addTurn($turn);
        
        // Persist session
        $this->conversationManager->saveSession($session);
        
        return $agentResponse;
    }
    
    /**
     * Get conversation summary.
     */
    public function getSummary(Session $session): string
    {
        return $this->summarizer->summarize($session, ['style' => 'detailed']);
    }
    
    /**
     * Delete a session.
     */
    public function endSession(Session $session): void
    {
        $this->conversationManager->deleteSession($session->getId());
        $this->logger->info("Session ended", ['session_id' => $session->getId()]);
    }
}

// Usage example
$agent = new StatefulAgent(
    apiKey: getenv('ANTHROPIC_API_KEY'),
    storageDir: __DIR__ . '/sessions'
);

// User 1 conversation
$session1 = $agent->getOrCreateSession('user_123');

echo "User: What's the weather in Paris?\n";
$response1 = $agent->chat($session1, "What's the weather in Paris?");
echo "Agent: {$response1}\n\n";

echo "User: What about tomorrow?\n";
$response2 = $agent->chat($session1, "What about tomorrow?");
echo "Agent: {$response2}\n\n";

// Later: Resume conversation
echo "\n--- Resuming conversation ---\n\n";
$resumedSession = $agent->getOrCreateSession('user_123');

echo "User: Thanks for the info earlier!\n";
$response3 = $agent->chat($resumedSession, "Thanks for the info earlier!");
echo "Agent: {$response3}\n\n";

// Get summary
echo "\n--- Conversation Summary ---\n";
$summary = $agent->getSummary($resumedSession);
echo $summary . "\n";
```

---

## Multi-User Conversations

Handle multiple users with proper isolation:

```php
<?php

class MultiUserAgentService
{
    private StatefulAgent $agent;
    private array $activeSessions = [];
    
    public function __construct(StatefulAgent $agent)
    {
        $this->agent = $agent;
    }
    
    /**
     * Handle a message from a user.
     */
    public function handleMessage(string $userId, string $message): string
    {
        // Get or create session for this user
        if (!isset($this->activeSessions[$userId])) {
            $this->activeSessions[$userId] = $this->agent->getOrCreateSession($userId);
        }
        
        $session = $this->activeSessions[$userId];
        
        // Get response
        return $this->agent->chat($session, $message);
    }
    
    /**
     * End a user's session.
     */
    public function endUserSession(string $userId): void
    {
        if (isset($this->activeSessions[$userId])) {
            $this->agent->endSession($this->activeSessions[$userId]);
            unset($this->activeSessions[$userId]);
        }
    }
    
    /**
     * Get active session count.
     */
    public function getActiveSessionCount(): int
    {
        return count($this->activeSessions);
    }
}

// Simulate multi-user conversations
$service = new MultiUserAgentService($agent);

// User A conversation
echo "=== User A ===\n";
echo $service->handleMessage('user_a', "What's the capital of France?") . "\n\n";
echo $service->handleMessage('user_a', "What's the population?") . "\n\n";

// User B conversation (independent)
echo "=== User B ===\n";
echo $service->handleMessage('user_b', "What's the capital of Germany?") . "\n\n";
echo $service->handleMessage('user_b', "What's the population?") . "\n\n";

// User A continues (maintains context)
echo "=== User A (continued) ===\n";
echo $service->handleMessage('user_a', "Tell me more about it.") . "\n\n";

echo "Active sessions: {$service->getActiveSessionCount()}\n";
```

---

## Testing Stateful Agents

### Unit Tests for Session Management

```php
<?php

use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    public function testCreateSession(): void
    {
        $session = new Session();
        
        $this->assertNotEmpty($session->getId());
        $this->assertGreaterThan(0, $session->getCreatedAt());
        $this->assertEquals(0, $session->getTurnCount());
    }
    
    public function testAddTurn(): void
    {
        $session = new Session();
        
        $turn = new Turn('Hello', 'Hi there!');
        $session->addTurn($turn);
        
        $this->assertEquals(1, $session->getTurnCount());
        $this->assertNotNull($session->getLastActivity());
    }
    
    public function testSessionState(): void
    {
        $session = new Session();
        
        $session->updateState('user_id', 'test_user');
        $session->updateState('language', 'en');
        
        $state = $session->getState();
        $this->assertEquals('test_user', $state['user_id']);
        $this->assertEquals('en', $state['language']);
    }
}
```

### Integration Tests for Context Management

```php
<?php

class ContextStrategyTest extends TestCase
{
    public function testSlidingWindow(): void
    {
        $session = new Session();
        
        // Add 10 turns
        for ($i = 1; $i <= 10; $i++) {
            $session->addTurn(new Turn("Message {$i}", "Response {$i}"));
        }
        
        $strategy = new SlidingWindowContext(maxTurns: 5);
        $context = $strategy->getContext($session);
        
        // Should have 10 messages (5 turns × 2 messages per turn)
        $this->assertCount(10, $context);
        
        // Should start from turn 6
        $this->assertStringContainsString('Message 6', $context[0]['content']);
    }
    
    public function testTokenBasedWindow(): void
    {
        $session = new Session();
        
        // Add turns with known sizes
        $session->addTurn(new Turn(str_repeat('a', 100), str_repeat('b', 100))); // ~50 tokens
        $session->addTurn(new Turn(str_repeat('c', 100), str_repeat('d', 100))); // ~50 tokens
        $session->addTurn(new Turn(str_repeat('e', 100), str_repeat('f', 100))); // ~50 tokens
        
        $strategy = new TokenBasedContext(maxTokens: 100);
        $context = $strategy->getContext($session);
        
        // Should include only last 2 turns (4 messages)
        $this->assertCount(4, $context);
    }
}
```

---

## Summary

In this chapter, you learned how to build stateful agents with short-term memory:

✅ **Session management** — Track conversations with `Session`, `Turn`, and `ConversationManager`  
✅ **Context window strategies** — Sliding window, token-based, and hybrid approaches  
✅ **AI-powered summarization** — Use Claude to compress long conversation histories  
✅ **Transcript pruning** — Remove less relevant turns intelligently  
✅ **Persistent storage** — Save sessions to files or databases  
✅ **Multi-user support** — Handle multiple independent conversations  
✅ **Production patterns** — Complete stateful agent with all best practices

With these patterns, your agents can maintain coherent, context-aware conversations across many turns.

---

## Practice Exercises

### Exercise 1: Implement Importance Scoring

Create a custom `calculateImportance()` function that:

- Scores turns based on entities mentioned (people, places, dates)
- Gives higher scores to turns with tool usage
- Considers user sentiment
- Weights recent turns slightly higher

### Exercise 2: Build a Summary Cache

Implement a caching layer that:

- Stores summaries for sessions
- Invalidates cache after N new turns
- Reduces API calls for frequently accessed sessions

### Exercise 3: Add Redis Session Storage

Implement `SessionStorageInterface` using Redis:

- Store sessions with TTL
- Support session listing by user
- Add session metadata (last access, turn count)

### Exercise 4: Create Context Visualization

Build a utility that:

- Shows which turns are included in context
- Displays token usage per turn
- Highlights summarized vs. full context

---

## Next Steps

Now that you have stateful short-term memory, it's time to add **long-term memory**. In **Chapter 07: Long-Term Memory with Datastores**, you'll design memory tables, implement embeddings for semantic recall, and build relevance scoring—enabling agents to remember facts and context across sessions and days.

Continue to [Chapter 07](/series/agentic-ai-php-developers/chapters/07-long-term-memory-with-datastores) →

---

## Further Reading

- [`claude-php/agent` Conversation Components](https://github.com/claude-php/claude-php-agent/tree/master/src/Conversation)
- [Claude API Context Window Management](https://docs.anthropic.com/en/docs/build-with-claude/prompt-engineering/long-context-tips)
- [Conversation Memory in AI Systems](https://arxiv.org/abs/2304.03442)
- [Session Management Best Practices](https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html)
