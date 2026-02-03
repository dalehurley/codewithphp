# Chapter 06: Stateful Conversations and Short-Term Memory

Code examples for implementing stateful agents with conversation memory.

## Examples

### 1. Basic Session Management
**File:** [`basic-session-management.php`](basic-session-management.php)

Creates sessions, adds turns, and manages conversation state using the framework's `Session`, `Turn`, and `ConversationManager` classes.

```bash
php basic-session-management.php
```

**What it demonstrates:**
- Creating and configuring sessions
- Adding conversation turns
- Managing session state
- Using `ConversationManager` for multi-session handling

---

### 2. Context Window Management
**File:** [`context-window-management.php`](context-window-management.php)

Implements different strategies for managing context windows within token limits.

```bash
php context-window-management.php
```

**Strategies demonstrated:**
- **Sliding Window:** Keep last N turns
- **Token-Based:** Keep turns within token budget
- **Hybrid:** Recent turns + summary of older context

---

### 3. AI-Powered Summarization
**File:** [`ai-summarization.php`](ai-summarization.php)

Uses `AIConversationSummarizer` to compress long conversation histories.

```bash
# Requires ANTHROPIC_API_KEY
export ANTHROPIC_API_KEY='your-key-here'
php ai-summarization.php
```

**Features:**
- Concise summaries
- Detailed summaries
- Bullet-point summaries
- Topic extraction
- Auto-summarizing at intervals

---

### 4. Transcript Pruning
**File:** [`transcript-pruning.php`](transcript-pruning.php)

Intelligent pruning to keep only relevant conversation turns.

```bash
php transcript-pruning.php
```

**Pruning strategies:**
- **Importance-based:** Score turns by relevance (keywords, tool use, questions)
- **Time-based:** Keep only recent turns
- **Combined:** Time filter + importance ranking

---

### 5. Persistent Storage
**File:** [`persistent-storage.php`](persistent-storage.php)

Demonstrates saving and loading sessions with `FileSessionStorage`.

```bash
php persistent-storage.php
```

**What it demonstrates:**
- File-based session persistence
- Loading sessions from storage
- Finding sessions by user
- Session lifecycle management

---

### 6. Complete Stateful Agent
**File:** [`stateful-agent-complete.php`](stateful-agent-complete.php)

Production-ready stateful agent with all features integrated.

```bash
# With API key (full functionality)
export ANTHROPIC_API_KEY='your-key-here'
php stateful-agent-complete.php

# Without API key (demo mode)
php stateful-agent-complete.php
```

**Features:**
- Session management with persistence
- Context window strategies
- Auto-summarization for long conversations
- Resumable conversations
- Error handling and logging

---

### 7. Multi-User Conversations
**File:** [`multi-user-conversations.php`](multi-user-conversations.php)

Handles multiple independent user conversations with proper isolation.

```bash
php multi-user-conversations.php
```

**What it demonstrates:**
- Multiple concurrent user sessions
- Context isolation between users
- Session statistics and monitoring
- Independent conversation tracking

---

## Running the Examples

### Prerequisites

1. Install dependencies:
```bash
composer install
```

2. For examples using the Claude API, set your API key:
```bash
export ANTHROPIC_API_KEY='your-anthropic-api-key'
```

### Run All Examples

```bash
# Examples that don't require API key
php basic-session-management.php
php context-window-management.php
php transcript-pruning.php
php persistent-storage.php
php multi-user-conversations.php
php stateful-agent-complete.php  # Works in demo mode

# Examples requiring API key
export ANTHROPIC_API_KEY='your-key-here'
php ai-summarization.php
php stateful-agent-complete.php  # Full functionality
```

---

## Key Concepts

### Session
A `Session` represents a conversation with:
- Unique ID
- Creation timestamp
- Last activity
- State (key-value data)
- Turns (conversation history)

### Turn
A `Turn` represents one exchange:
- User input
- Agent response
- Metadata (tokens, tools used, etc.)
- Timestamp

### Context Window
The portion of conversation history sent to the LLM. Strategies:
1. **Sliding Window:** Last N turns
2. **Token-Based:** Turns within token budget
3. **Hybrid:** Recent turns + summary

### Summarization
AI-powered compression of long conversations:
- Concise: Brief overview
- Detailed: Comprehensive summary
- Bullet points: Key points list

### Pruning
Removing less relevant turns:
- Importance scoring (keywords, tool use, questions)
- Time-based filtering
- Combined strategies

---

## Storage Options

### File Storage
Provided by the framework:
```php
$storage = new FileSessionStorage($storageDir, new JsonSessionSerializer());
```

### Database Storage
Implement `SessionStorageInterface` for your database:
- MySQL/PostgreSQL: Full-featured, production-ready
- SQLite: Lightweight, good for development
- Redis: Fast, with TTL support

---

## Production Considerations

### Session Timeout
Configure appropriate timeouts:
```php
$manager = new ConversationManager([
    'session_timeout' => 3600, // 1 hour
]);
```

### Storage Cleanup
Regularly clean up expired sessions:
- Implement cron job or background task
- Use database TTL features (Redis)
- Monitor storage growth

### Context Strategy
Choose based on your use case:
- **Customer support:** Hybrid (5 recent + summary)
- **Chatbots:** Sliding window (3-5 turns)
- **Long research:** Token-based (max budget)

### Summarization Intervals
Balance cost vs. quality:
- Every 10 turns: Moderate cost
- Every 20 turns: Lower cost, less frequent updates
- On-demand: Lowest cost, summarize when needed

---

## Testing

Each example includes demonstration code. For production, add:

### Unit Tests
```php
class SessionTest extends TestCase
{
    public function testCreateSession(): void
    {
        $session = new Session();
        $this->assertNotEmpty($session->getId());
    }
    
    public function testAddTurn(): void
    {
        $session = new Session();
        $turn = new Turn('Hello', 'Hi there!');
        $session->addTurn($turn);
        $this->assertEquals(1, $session->getTurnCount());
    }
}
```

### Integration Tests
Test full agent behavior with storage, context strategies, and summarization.

---

## Further Reading

- [Chapter 06 Tutorial](../../chapters/06-stateful-conversations-and-short-term-memory.md)
- [`claude-php/agent` Conversation Components](https://github.com/claude-php/claude-php-agent/tree/master/src/Conversation)
- [Claude API Context Tips](https://docs.anthropic.com/en/docs/build-with-claude/prompt-engineering/long-context-tips)

---

## Questions?

These examples are part of the **Agentic AI for PHP Developers** series. For questions or issues, please refer to the main tutorial or open an issue on GitHub.
