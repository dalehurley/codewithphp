---
title: "22: Building a Chatbot with Laravel"
description: "Build a production-ready chatbot with Laravel, Livewire, and Claude. Implement conversation persistence, user authentication, message history, streaming responses, and a beautiful reactive UI."
series: "claude-php-developers"
chapter: 22
order: 22
difficulty: "Intermediate"
prerequisites:
  - "/series/claude-php-developers/chapters/21-laravel-integration"
  - "Laravel 11+ with Livewire 3 installed"
  - "Database configured (MySQL or PostgreSQL)"
teaches:
  - 'Design and implement a scalable database schema for chat applications'
  - 'Build Eloquent models with relationships, accessors, and business logic methods'
  - 'Create Livewire components for reactive, real-time chat interfaces'
  - 'Implement streaming responses using Server-Sent Events'
  - 'Handle conversation context and message history management'
---
![22: Building a Chatbot with Laravel](/images/claude-php/chapter-22-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 22</span>
</div>

# Chapter 22: Building a Chatbot with Laravel

## Overview

In this chapter, you'll build a complete, production-ready chatbot application using Laravel, Livewire, and Claude. This isn't a simple proof-of-concept—you'll create a fully-featured chat system with user authentication, conversation persistence, real-time streaming, message history, and a beautiful reactive user interface.

By the end, you'll have a working chatbot that can handle multiple users, maintain separate conversations, stream responses in real-time, and provide a smooth, modern chat experience that rivals commercial solutions.

## Prerequisites

Before starting, ensure you have:

- ✓ **Laravel 11** with Livewire 3 installed
- ✓ **Database** configured (MySQL or PostgreSQL)
- ✓ **Authentication** set up (Laravel Breeze or Jetstream)
- ✓ **Claude service** from Chapter 21
- ✓ **Basic Livewire knowledge**
- ✓ **TailwindCSS** for styling
- ✓ **Claude-PHP-SDK v0.2** installed via Composer

**Estimated Time**: ~120-150 minutes

**Verify your setup:**

```bash
# Check Laravel version
php artisan --version

# Check Livewire version
php artisan livewire:version

# Check Claude SDK installation
composer show claude-php/claude-php-sdk
```

## Quick Start

Get a working chatbot running in 5 minutes:

```bash
# 1. Install dependencies
composer require livewire/livewire claude-php/claude-php-sdk:^0.2

# 2. Create migrations
php artisan make:migration create_conversations_table
php artisan make:migration create_messages_table

# 3. Copy migration code from this chapter, then run:
php artisan migrate

# 4. Create models
php artisan make:model Conversation
php artisan make:model Message

# 5. Create factories (for testing)
php artisan make:factory ConversationFactory
php artisan make:factory MessageFactory

# 6. Create service
php artisan make:class Services/ChatService

# 7. Create Livewire components
php artisan make:livewire Chat
php artisan make:livewire ConversationList

# 8. Add route to routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::get('/chat/{conversation?}', Chat::class)->name('chat');
});

# 9. Add to .env
ANTHROPIC_API_KEY=your_api_key_here

# 10. Visit /chat in your browser
```

**Expected Result**: A working chat interface where you can send messages and receive streaming responses from Claude.

## What You'll Build

By the end of this chapter, you will have created:

- A complete database schema for conversations and messages with proper relationships
- Eloquent models (`Conversation` and `Message`) with relationships and helper methods
- A `ChatService` class that handles message sending, streaming, and cost calculation
- Two Livewire components (`Chat` and `ConversationList`) for reactive UI
- Beautiful Blade views with TailwindCSS styling for the chat interface
- Rate limiting middleware to prevent abuse
- Conversation export functionality (Markdown and JSON formats)
- Comprehensive test suite for the chat functionality
- A production-ready chatbot application with user authentication, conversation persistence, and real-time streaming

## Objectives

By completing this chapter, you will:

- Design and implement a scalable database schema for chat applications
- Build Eloquent models with relationships, accessors, and business logic methods
- Create Livewire components for reactive, real-time chat interfaces
- Implement streaming responses using Server-Sent Events
- Handle conversation context and message history management
- Track API costs and token usage at the message level
- Implement rate limiting to control API usage and costs
- Export conversations in multiple formats for data portability
- Write comprehensive tests using Laravel's testing tools
- Build a production-ready chat application with proper error handling

## Step-by-Step Setup

### Step 1: Install Dependencies (~5 min)

**Goal**: Ensure all required packages are installed.

**Actions**:

1. **Verify Livewire 3 is installed**:

```bash
composer require livewire/livewire
```

2. **Ensure TailwindCSS is configured** (if using Laravel Breeze/Jetstream, this is already done):

```bash
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p
```

3. **Verify Claude service from Chapter 21** is set up and working.

**Expected Result**: All dependencies installed and configured.

### Step 2: Run Migrations (~2 min)

**Goal**: Create the database tables for conversations and messages.

**Actions**:

1. **Create the migration files**:

```bash
php artisan make:migration create_conversations_table
php artisan make:migration create_messages_table
```

2. **Copy the migration code** from the sections below into the respective migration files.

3. **Run the migrations**:

```bash
php artisan migrate
```

**Expected Result**: Two new tables (`conversations` and `messages`) in your database.

**Why It Works**: Migrations create the database schema that will store all conversation data. The foreign keys ensure data integrity, and indexes optimize query performance.

### Step 3: Create Models (~5 min)

**Goal**: Set up Eloquent models with relationships and business logic.

**Actions**:

1. **Create the Conversation model**:

```bash
php artisan make:model Conversation
```

2. **Create the Message model**:

```bash
php artisan make:model Message
```

3. **Copy the model code** from the sections below.

**Expected Result**: Two models ready to use with relationships and helper methods.

## Database Schema Design

The foundation of our chatbot is a well-designed database schema that separates conversations from messages. This design allows for flexible conversation management, efficient querying, and proper data relationships.

### Migrations

```php
<?php
# filename: database/migrations/2024_01_01_000001_create_conversations_table.php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('system_prompt')->nullable();
            $table->string('model')->default('claude-sonnet-4-5');
            $table->json('metadata')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'last_message_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
```

```php
<?php
# filename: database/migrations/2024_01_01_000002_create_messages_table.php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['user', 'assistant']);
            $table->text('content');
            $table->integer('input_tokens')->nullable();
            $table->integer('output_tokens')->nullable();
            $table->decimal('cost', 10, 6)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
```

**Why This Schema Works:**

- **Separate tables**: Conversations and messages are stored separately, allowing efficient querying and management
- **Foreign keys**: Proper relationships ensure data integrity with cascade deletes
- **Indexes**: The composite index on `user_id` and `last_message_at` optimizes the common query of loading a user's recent conversations
- **Soft deletes**: Conversations can be "deleted" but retained for analytics or recovery
- **Metadata fields**: JSON columns allow storing flexible, schema-less data for future features
- **Token tracking**: Storing input/output tokens and costs enables usage analytics and budgeting

## Models

Eloquent models provide an object-oriented interface to our database tables, with relationships, accessors, and business logic methods.

### Conversation Model

```php
<?php
# filename: app/Models/Conversation.php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'system_prompt',
        'model',
        'metadata',
        'last_message_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_message_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function getFormattedMessagesAttribute(): array
    {
        return $this->messages->map(function ($message) {
            return [
                'role' => $message->role,
                'content' => $message->content,
            ];
        })->toArray();
    }

    public function getFormattedMessagesForContext(int $maxTokens = 100000): array
    {
        $messages = $this->messages()->orderBy('created_at')->get();
        $totalTokens = 0;
        $contextMessages = [];

        // Start from the most recent messages and work backwards
        foreach ($messages->reverse() as $message) {
            // Estimate tokens (rough approximation: 1 token ≈ 4 characters)
            $estimatedTokens = (int) ceil(strlen($message->content) / 4);

            if ($totalTokens + $estimatedTokens > $maxTokens) {
                break;
            }

            $totalTokens += $estimatedTokens;
            array_unshift($contextMessages, [
                'role' => $message->role,
                'content' => $message->content,
            ]);
        }

        return $contextMessages;
    }

    public function generateTitle(): void
    {
        if ($this->title !== null || $this->messages()->count() < 2) {
            return;
        }

        $firstMessage = $this->messages()->where('role', 'user')->first();

        if ($firstMessage) {
            // Use first 50 chars of first message as title
            $title = substr($firstMessage->content, 0, 50);
            if (strlen($firstMessage->content) > 50) {
                $title .= '...';
            }

            $this->update(['title' => $title]);
        }
    }

    public function getTotalCostAttribute(): float
    {
        return (float) $this->messages()->sum('cost');
    }

    public function getTotalTokensAttribute(): int
    {
        return $this->messages()->sum('input_tokens') +
               $this->messages()->sum('output_tokens');
    }
}
```

### Message Model

```php
<?php
# filename: app/Models/Message.php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'input_tokens',
        'output_tokens',
        'cost',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'cost' => 'decimal:6',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function isAssistant(): bool
    {
        return $this->role === 'assistant';
    }
}
```

**Key Model Features:**

- **Relationships**: `belongsTo` and `hasMany` relationships enable easy navigation between conversations and messages
- **Accessors**: `formatted_messages` provides a clean array format for Claude API calls
- **Business Logic**: `generateTitle()` automatically creates conversation titles from the first message
- **Aggregations**: `total_cost` and `total_tokens` accessors calculate sums without additional queries
- **Type Safety**: Helper methods like `isUser()` and `isAssistant()` improve code readability

### Factory Classes

For testing, create factory classes to generate test data:

```php
<?php
# filename: database/factories/ConversationFactory.php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Conversation>
 */
class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(4),
            'system_prompt' => null,
            'model' => 'claude-sonnet-4-5-20250929',
            'metadata' => null,
            'last_message_at' => now(),
        ];
    }

    public function withSystemPrompt(string $prompt): static
    {
        return $this->state(fn (array $attributes) => [
            'system_prompt' => $prompt,
        ]);
    }

    public function withModel(string $model): static
    {
        return $this->state(fn (array $attributes) => [
            'model' => $model,
        ]);
    }
}
```

```php
<?php
# filename: database/factories/MessageFactory.php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'role' => $this->faker->randomElement(['user', 'assistant']),
            'content' => $this->faker->paragraph(),
            'input_tokens' => null,
            'output_tokens' => null,
            'cost' => null,
            'metadata' => null,
        ];
    }

    public function user(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'user',
        ]);
    }

    public function assistant(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'assistant',
            'input_tokens' => $this->faker->numberBetween(100, 500),
            'output_tokens' => $this->faker->numberBetween(50, 300),
            'cost' => $this->faker->randomFloat(6, 0.001, 0.1),
        ]);
    }
}
```

**Why Factories Matter**: Factories make testing easier by generating realistic test data. They're especially useful for creating conversations with multiple messages for testing pagination, search, and context management.

## Chat Service

The `ChatService` class encapsulates all chat-related business logic, handling message sending, streaming, cost calculation, and conversation management. This service layer keeps your controllers and Livewire components clean and focused on presentation.

```php
<?php
# filename: app/Services/ChatService.php
declare(strict_types=1);

namespace App\Services;

use ClaudePhp\ClaudePhp;
use App\Models\Conversation;
use App\Models\Message;

class ChatService
{
    private ClaudePhp $client;

    public function __construct()
    {
        $apiKey = config('services.anthropic.api_key');

        if (!$apiKey) {
            throw new \RuntimeException('ANTHROPIC_API_KEY is not configured');
        }

        $this->client = new ClaudePhp([
            'api_key' => $apiKey
        ]);
    }

    private const PRICING = [
        'claude-opus-4-1' => ['input' => 15.00, 'output' => 75.00],
        'claude-sonnet-4-5' => ['input' => 3.00, 'output' => 15.00],
        'claude-haiku-4-5-20251001' => ['input' => 0.25, 'output' => 1.25],
    ];

    public function sendMessage(
        Conversation $conversation,
        string $content
    ): Message {
        try {
            // Validate input
            if (empty(trim($content))) {
                throw new \InvalidArgumentException('Message content cannot be empty');
            }

            // Create user message
            $userMessage = $conversation->messages()->create([
                'role' => 'user',
                'content' => trim($content),
            ]);

            // Get conversation history
            $history = $conversation->getFormattedMessagesForContext(100000);

            if (empty($history)) {
                throw new \RuntimeException('Failed to generate conversation history');
            }

            // Get response from Claude
            $response = $this->client->messages()->create([
                'model' => $conversation->model,
                'max_tokens' => 4096,
                'messages' => $history,
                'system' => $conversation->system_prompt
            ]);

            // Validate response structure
            if (!isset($response->content) || !is_array($response->content) || empty($response->content)) {
                throw new \RuntimeException('Invalid response from Claude API');
            }

            $responseText = $response->content[0]->text ?? '';

            if (empty($responseText)) {
                throw new \RuntimeException('Empty response from Claude API');
            }

            $inputTokens = $response->usage->inputTokens ?? 0;
            $outputTokens = $response->usage->outputTokens ?? 0;

            // Calculate cost
            $cost = $this->calculateCost(
                $conversation->model,
                $inputTokens,
                $outputTokens
            );

            // Create assistant message
            $assistantMessage = $conversation->messages()->create([
                'role' => 'assistant',
                'content' => $responseText,
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'cost' => $cost,
            ]);

            // Update conversation
            $conversation->update([
                'last_message_at' => now(),
            ]);

            // Generate title if needed
            $conversation->generateTitle();

            return $assistantMessage;

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('ChatService sendMessage failed', [
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw with a user-friendly message
            throw new \RuntimeException('Failed to send message: ' . $e->getMessage());
        }
    }

    public function streamMessage(
        Conversation $conversation,
        string $content,
        callable $callback
    ): void {
        try {
            // Validate input
            if (empty(trim($content))) {
                throw new \InvalidArgumentException('Message content cannot be empty');
            }

            // Create user message first
            $conversation->messages()->create([
                'role' => 'user',
                'content' => trim($content),
            ]);

            // Get conversation history
            $history = $conversation->getFormattedMessagesForContext(100000);

            if (empty($history)) {
                throw new \RuntimeException('Failed to generate conversation history');
            }

            $fullResponse = '';

            // Stream response from Claude
            $stream = $this->client->messages()->create([
                'model' => $conversation->model,
                'max_tokens' => 4096,
                'messages' => $history,
                'system' => $conversation->system_prompt,
                'stream' => true
            ]);

            foreach ($stream as $chunk) {
                // Check for text delta
                if (isset($chunk['delta']['text'])) {
                    $text = $chunk['delta']['text'];
                    $fullResponse .= $text;
                    $callback($text);
                }
            }

            if (empty($fullResponse)) {
                throw new \RuntimeException('Empty response from Claude streaming API');
            }

            // Create assistant message with full response after streaming completes
            // Note: Streaming responses don't always provide usage stats in the same way
            $conversation->messages()->create([
                'role' => 'assistant',
                'content' => $fullResponse,
            ]);

            // Update conversation timestamp and generate title if needed
            $conversation->update(['last_message_at' => now()]);
            $conversation->generateTitle();

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('ChatService streamMessage failed', [
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw with a user-friendly message
            throw new \RuntimeException('Failed to stream message: ' . $e->getMessage());
        }
    }

    private function calculateCost(string $model, int $inputTokens, int $outputTokens): float
    {
        $pricing = self::PRICING[$model] ?? ['input' => 0, 'output' => 0];

        $inputCost = ($inputTokens / 1_000_000) * $pricing['input'];
        $outputCost = ($outputTokens / 1_000_000) * $pricing['output'];

        return $inputCost + $outputCost;
    }
}
```

**Service Design Benefits:**

- **Separation of Concerns**: Business logic is separated from presentation (Livewire components)
- **Cost Tracking**: Automatic cost calculation based on model pricing and token usage
- **Streaming Support**: Handles both synchronous and streaming responses
- **History Management**: Properly manages conversation context for Claude API calls
- **Reusability**: Can be used by controllers, jobs, commands, or any other part of your application

## Livewire Components

Livewire components provide reactive UI without writing complex JavaScript. They handle user interactions, manage component state, and automatically update the DOM when data changes.

### Chat Component

```php
<?php
# filename: app/Livewire/Chat.php
declare(strict_types=1);

namespace App\Livewire;

use App\Models\Conversation;
use App\Services\ChatService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\On;
use Livewire\Component;

class Chat extends Component
{
    public ?Conversation $conversation = null;
    public string $message = '';
    public bool $isStreaming = false;
    public string $streamingResponse = '';
    public bool $showSettings = false;
    public string $selectedModel = '';
    public string $systemPrompt = '';

    protected $rules = [
        'message' => 'required|string|max:10000',
        'systemPrompt' => 'nullable|string|max:5000',
    ];

    public function mount(?int $conversationId = null)
    {
        if ($conversationId) {
            $this->conversation = Conversation::where('user_id', Auth::id())
                ->findOrFail($conversationId);
            $this->selectedModel = $this->conversation->model;
            $this->systemPrompt = $this->conversation->system_prompt ?? '';
        } else {
            $this->createNewConversation();
        }
    }

    public function authorize($ability, $model = null): bool
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            abort(401, 'Authentication required');
        }

        // Additional authorization logic can be added here
        return true;
    }

    public function createNewConversation(): void
    {
        $this->conversation = Conversation::create([
            'user_id' => Auth::id(),
            'model' => $this->selectedModel ?: config('claude.default_model'),
            'system_prompt' => $this->systemPrompt ?: null,
        ]);

        $this->selectedModel = $this->conversation->model;
        $this->redirect(route('chat', ['conversation' => $this->conversation->id]));
    }

    public function updateSettings(): void
    {
        $this->validate(['systemPrompt' => 'nullable|string|max:5000']);

        if ($this->conversation) {
            $this->conversation->update([
                'model' => $this->selectedModel,
                'system_prompt' => $this->systemPrompt ?: null,
            ]);
        }

        $this->showSettings = false;
        $this->dispatch('settings-updated');
    }

    public function retryMessage(int $messageId): void
    {
        $message = $this->conversation->messages()->findOrFail($messageId);

        if ($message->role !== 'user') {
            return;
        }

        // Delete the assistant response if it exists
        $assistantMessage = $this->conversation->messages()
            ->where('id', '>', $messageId)
            ->where('role', 'assistant')
            ->first();

        if ($assistantMessage) {
            $assistantMessage->delete();
        }

        // Resend the message
        $this->message = $message->content;
        $this->messages()->create(app(ChatService::class));
    }

    public function sendMessage(ChatService $chatService): void
    {
        $this->validate();

        if ($this->isStreaming) {
            return;
        }

        // Rate limiting check
        $key = 'chat-messages:' . Auth::id();
        if (\RateLimiter::tooManyAttempts($key, 60)) { // 60 messages per hour
            $seconds = \RateLimiter::availableIn($key);
            $this->addError('message', "Too many messages. Please wait {$seconds} seconds.");
            return;
        }

        \RateLimiter::hit($key, 3600); // 1 hour window

        $this->isStreaming = true;
        $this->streamingResponse = '';

        try {
            $chatService->streamMessage(
                $this->conversation,
                $this->message,
                function ($chunk) {
                    $this->streamingResponse .= $chunk;
                    $this->dispatch('message-chunk', ['chunk' => $chunk]);
                }
            );

            $this->message = '';
            $this->conversation->refresh();
        } catch (\Exception $e) {
            $this->addError('message', 'Failed to send message: ' . $e->getMessage());
            $this->isStreaming = false;
            $this->streamingResponse = '';
        }
    }

    #[On('conversation-selected')]
    public function loadConversation(int $conversationId): void
    {
        $this->conversation = Conversation::where('user_id', Auth::id())
            ->findOrFail($conversationId);
    }

    public function deleteConversation(): void
    {
        if ($this->conversation && $this->conversation->user_id === Auth::id()) {
            $this->conversation->delete();
            $this->createNewConversation();
        }
    }

    public function render()
    {
        return view('livewire.chat', [
            'messages' => $this->conversation?->messages ?? collect(),
        ]);
    }
}
```

### Conversation List Component

```php
<?php
# filename: app/Livewire/ConversationList.php
declare(strict_types=1);

namespace App\Livewire;

use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ConversationList extends Component
{
    public \Illuminate\Contracts\Pagination\LengthAwarePaginator $conversations;
    public ?int $activeConversationId = null;
    public string $search = '';
    public int $perPage = 20;

    public function mount(?int $activeId = null)
    {
        $this->activeConversationId = $activeId;
        $this->loadConversations();
    }

    public function loadConversations(): void
    {
        $query = Conversation::where('user_id', Auth::id());

        // Add search functionality
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhereHas('messages', function ($mq) {
                      $mq->where('content', 'like', '%' . $this->search . '%');
                  });
            });
        }

        $this->conversations = $query
            ->orderBy('last_message_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function updatedSearch(): void
    {
        $this->loadConversations();
    }

    public function selectConversation(int $conversationId): void
    {
        $this->activeConversationId = $conversationId;
        $this->dispatch('conversation-selected', conversationId: $conversationId);
    }

    public function deleteConversation(int $conversationId): void
    {
        $conversation = Conversation::where('user_id', Auth::id())
            ->findOrFail($conversationId);

        $conversation->delete();
        $this->loadConversations();

        if ($this->activeConversationId === $conversationId) {
            $this->dispatch('conversation-deleted');
        }
    }

    public function render()
    {
        return view('livewire.conversation-list');
    }
}
```

**Livewire Component Patterns:**

- **Public Properties**: Automatically synced with the view and can be bound to form inputs
- **Lifecycle Hooks**: `mount()` runs when the component is initialized, perfect for loading data
- **Event Listeners**: `#[On('conversation-selected')]` listens for events from other components
- **Validation**: Built-in validation rules provide client and server-side validation
- **Error Handling**: Try-catch blocks ensure graceful error handling with user-friendly messages

## Views

Blade templates provide the HTML structure, while Livewire handles the dynamic behavior. Alpine.js (included with Livewire) adds lightweight interactivity like auto-scrolling.

### Main Chat View

```blade
{{-- filename: resources/views/livewire/chat.blade.php --}}
<div class="flex flex-col h-full">
    <!-- Chat Header -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-white">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">
                {{ $conversation?->title ?? 'New Conversation' }}
            </h2>
            <p class="text-sm text-gray-500">
                Model: {{ $conversation?->model ?? 'claude-sonnet-4' }}
            </p>
        </div>
        <div class="flex gap-2">
            <button
                wire:click="createNewConversation"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            >
                New Chat
            </button>
            @if($conversation)
                <button
                    wire:click="$set('showSettings', true)"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                >
                    Settings
                </button>
                <button
                    wire:click="deleteConversation"
                    wire:confirm="Are you sure you want to delete this conversation?"
                    class="px-4 py-2 text-sm font-medium text-red-700 bg-white border border-red-300 rounded-md hover:bg-red-50"
                >
                    Delete
                </button>
            @endif
        </div>
    </div>

    <!-- Settings Modal -->
    @if($showSettings && $conversation)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:click="$set('showSettings', false)">
            <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4" wire:click.stop>
                <h3 class="text-lg font-semibold mb-4">Conversation Settings</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Model</label>
                        <select wire:model="selectedModel" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="claude-opus-4-1">Claude Opus 4</option>
                            <option value="claude-sonnet-4-5">Claude Sonnet 4</option>
                            <option value="claude-haiku-4-5">Claude Haiku 4</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">System Prompt</label>
                        <textarea
                            wire:model="systemPrompt"
                            rows="6"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Enter a system prompt to guide Claude's behavior..."
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-500">This prompt will be used for all messages in this conversation.</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button
                        wire:click="$set('showSettings', false)"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="updateSettings"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700"
                    >
                        Save Settings
                    </button>
                </div>
            </div>
        </div>
    @endif
    </div>

    <!-- Messages Container -->
    <div
        id="messages-container"
        class="flex-1 overflow-y-auto px-6 py-4 space-y-4 bg-gray-50"
        x-data="{ scrollToBottom() { this.$el.scrollTop = this.$el.scrollHeight } }"
        x-init="scrollToBottom()"
    >
        @foreach($messages as $message)
            <div class="flex {{ $message->isUser() ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-3xl {{ $message->isUser() ? 'bg-blue-600 text-white' : 'bg-white text-gray-900' }} rounded-lg px-4 py-3 shadow">
                    <div class="flex items-start gap-3">
                        @if($message->isAssistant())
                            <svg class="w-6 h-6 text-purple-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        @endif
                        <div class="flex-1">
                            <div class="prose prose-sm max-w-none {{ $message->isUser() ? 'prose-invert' : '' }}">
                                {!! Str::markdown($message->content) !!}
                            </div>
                            <div class="mt-2 flex items-center gap-3">
                                @if($message->isAssistant() && $message->cost)
                                    <div class="text-xs text-gray-500">
                                        Cost: ${{ number_format($message->cost, 6) }} •
                                        Tokens: {{ $message->input_tokens + $message->output_tokens }}
                                    </div>
                                @endif
                                @if($message->isUser())
                                    <button
                                        wire:click="retryMessage({{ $message->id }})"
                                        wire:loading.attr="disabled"
                                        class="text-xs text-blue-600 hover:text-blue-800 disabled:opacity-50"
                                        title="Retry this message"
                                    >
                                        <span wire:loading.remove wire:target="retryMessage({{ $message->id }})">Retry</span>
                                        <span wire:loading wire:target="retryMessage({{ $message->id }})">Retrying...</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Streaming Response -->
        @if($isStreaming && $streamingResponse)
            <div class="flex justify-start">
                <div class="max-w-3xl bg-white text-gray-900 rounded-lg px-4 py-3 shadow">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-purple-600 flex-shrink-0 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                        <div class="flex-1 prose prose-sm max-w-none">
                            {!! Str::markdown($streamingResponse) !!}
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Input Form -->
    <div class="px-6 py-4 border-t border-gray-200 bg-white">
        <form wire:submit="sendMessage" class="flex gap-3">
            <textarea
                wire:model="message"
                placeholder="Type your message..."
                rows="3"
                class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                @keydown.ctrl.enter="$wire.sendMessage()"
                {{ $isStreaming ? 'disabled' : '' }}
            ></textarea>
            <button
                type="submit"
                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                {{ $isStreaming ? 'disabled' : '' }}
            >
                @if($isStreaming)
                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                @else
                    Send
                @endif
            </button>
        </form>
        @error('message')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
        <p class="mt-2 text-xs text-gray-500">
            Press Ctrl+Enter to send
        </p>
    </div>
</div>

@script
<script>
    $wire.on('message-chunk', (event) => {
        // Auto-scroll to bottom when new chunk arrives
        const container = document.getElementById('messages-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    });
</script>
@endscript
```

**View Features:**

- **Markdown Rendering**: Uses Laravel's `Str::markdown()` helper to render Claude's responses with proper formatting
- **Streaming Display**: Shows streaming responses in real-time with a pulsing icon indicator
- **Auto-scroll**: Alpine.js automatically scrolls to the bottom when new messages arrive
- **Cost Display**: Shows per-message costs and token counts for transparency
- **Responsive Design**: TailwindCSS classes ensure the UI works on all screen sizes

### Conversation List View

```blade
{{-- filename: resources/views/livewire/conversation-list.blade.php --}}
<div class="h-full flex flex-col bg-gray-100">
    <div class="p-4 border-b border-gray-200 bg-white">
        <h3 class="text-lg font-semibold text-gray-900">Conversations</h3>
    </div>

    <div class="flex-1 overflow-y-auto">
        <!-- Search Bar -->
        <div class="p-4 border-b border-gray-200 bg-white">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search conversations..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
        </div>

        @forelse($conversations as $conversation)
            <button
                wire:click="selectConversation({{ $conversation->id }})"
                class="w-full text-left px-4 py-3 border-b border-gray-200 hover:bg-gray-50 transition-colors {{ $activeConversationId === $conversation->id ? 'bg-blue-50 border-l-4 border-l-blue-600' : '' }}"
            >
                <div class="flex justify-between items-start">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">
                            {{ $conversation->title ?? 'Untitled Conversation' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $conversation->messages_count ?? 0 }} messages •
                            {{ $conversation->last_message_at?->diffForHumans() ?? 'Just created' }}
                        </p>
                        @if($conversation->total_cost > 0)
                            <p class="text-xs text-gray-400 mt-1">
                                Cost: ${{ number_format($conversation->total_cost, 4) }}
                            </p>
                        @endif
                    </div>
                    <button
                        wire:click.stop="deleteConversation({{ $conversation->id }})"
                        wire:confirm="Delete this conversation?"
                        class="ml-2 text-gray-400 hover:text-red-600"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </button>
        @empty
            <div class="p-4 text-center text-gray-500">
                <p class="text-sm">No conversations yet</p>
                <p class="text-xs mt-1">Start a new chat to begin</p>
            </div>
        @endforelse

        <!-- Pagination -->
        <div class="p-4 border-t border-gray-200 bg-white">
            {{ $conversations->links() }}
        </div>
    </div>
</div>
```

### Main Layout

```blade
{{-- filename: resources/views/chat.blade.php --}}
<x-app-layout>
    <div class="h-screen flex overflow-hidden">
        <!-- Sidebar -->
        <div class="w-80 border-r border-gray-200 flex-shrink-0">
            @livewire('conversation-list', ['activeId' => $conversation?->id])
        </div>

        <!-- Main Chat Area -->
        <div class="flex-1 flex flex-col">
            @livewire('chat', ['conversationId' => $conversation?->id])
        </div>
    </div>
</x-app-layout>
```

## Routes

```php
<?php
# filename: routes/web.php

use App\Http\Controllers\ConversationController;
use App\Livewire\Chat;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/chat/{conversation?}', Chat::class)->name('chat');

    // Optional: Add export route if using the ConversationController
    Route::get('/conversations/{conversation}/export', [ConversationController::class, 'export'])
        ->name('conversations.export');
});
```

**Why This Route Structure:**

- **Single Route**: Livewire handles all chat interactions through one route
- **Optional Parameter**: Allows both new conversations (`/chat`) and existing ones (`/chat/123`)
- **Middleware Protection**: Ensures only authenticated users can access conversations

## Advanced Features

### Context Window Management

The `getFormattedMessagesForContext()` method automatically manages conversation history to stay within Claude's context window limits. It:

- Estimates token usage (rough approximation: 1 token ≈ 4 characters)
- Keeps the most recent messages that fit within the limit
- Ensures older messages are excluded when approaching the context limit
- Maintains conversation flow by prioritizing recent context

**Usage**: This is automatically called by `ChatService` when sending messages, so no manual intervention is needed.

### Pagination

Conversation lists are paginated to improve performance with many conversations:

- Default: 20 conversations per page
- Configurable via `$perPage` property
- Uses Laravel's built-in pagination
- Works seamlessly with search functionality

### Search Functionality

Users can search conversations by:

- Conversation title
- Message content within conversations
- Real-time search with 300ms debounce
- Pagination works with search results

### Model Selection

Users can switch Claude models per conversation:

- Available models: Opus 4, Sonnet 4, Haiku 4
- Changes apply to all future messages in the conversation
- Model selection persists with the conversation
- Cost tracking adjusts automatically based on selected model

### System Prompt Management

Each conversation can have a custom system prompt:

- Editable via the Settings modal
- Applies to all messages in the conversation
- Useful for role-playing, specific behaviors, or domain-specific instructions
- Stored per conversation, not globally

### Message Retry

Users can retry failed or unsatisfactory messages:

- "Retry" button appears on user messages
- Deletes the previous assistant response
- Resends the user message automatically
- Useful for handling API errors or getting different responses

### Rate Limiting

```php
<?php
# filename: app/Http/Middleware/ChatRateLimit.php
declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;

class ChatRateLimit
{
    public function __construct(
        private readonly RateLimiter $limiter
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $key = 'chat:' . $request->user()->id;

        if ($this->limiter->tooManyAttempts($key, 60)) {
            $seconds = $this->limiter->availableIn($key);

            return response()->json([
                'error' => 'Too many messages. Please wait ' . $seconds . ' seconds.',
            ], 429);
        }

        $this->limiter->hit($key, 60); // 60 per hour

        return $next($request);
    }
}
```

### Export Conversation

```php
<?php
# filename: app/Services/ConversationExporter.php
declare(strict_types=1);

namespace App\Services;

use App\Models\Conversation;

class ConversationExporter
{
    public function exportAsMarkdown(Conversation $conversation): string
    {
        $markdown = "# {$conversation->title}\n\n";
        $markdown .= "**Created:** {$conversation->created_at->format('Y-m-d H:i:s')}\n";
        $markdown .= "**Model:** {$conversation->model}\n";
        $markdown .= "**Total Cost:** \${$conversation->total_cost}\n\n";
        $markdown .= "---\n\n";

        foreach ($conversation->messages as $message) {
            $role = $message->isUser() ? '👤 User' : '🤖 Assistant';
            $markdown .= "## {$role}\n\n";
            $markdown .= "{$message->content}\n\n";

            if ($message->isAssistant() && $message->cost) {
                $markdown .= "*Cost: \${$message->cost} | Tokens: {$message->input_tokens} + {$message->output_tokens}*\n\n";
            }

            $markdown .= "---\n\n";
        }

        return $markdown;
    }

    public function exportAsJson(Conversation $conversation): string
    {
        $data = [
            'id' => $conversation->id,
            'title' => $conversation->title,
            'model' => $conversation->model,
            'created_at' => $conversation->created_at->toIso8601String(),
            'messages' => $conversation->messages->map(function ($message) {
                return [
                    'role' => $message->role,
                    'content' => $message->content,
                    'tokens' => [
                        'input' => $message->input_tokens,
                        'output' => $message->output_tokens,
                    ],
                    'cost' => $message->cost ? (float) $message->cost : null,
                    'created_at' => $message->created_at->toIso8601String(),
                ];
            })->toArray(),
            'total_cost' => $conversation->total_cost,
            'total_tokens' => $conversation->total_tokens,
        ];

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            throw new \RuntimeException('Failed to encode conversation as JSON: ' . json_last_error_msg());
        }

        return $json;
    }
}
```

### Conversation Controller for Export

```php
<?php
# filename: app/Http/Controllers/ConversationController.php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Services\ConversationExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    public function export(
        Conversation $conversation,
        ConversationExporter $exporter,
        Request $request
    ) {
        // Authorize
        if ($conversation->user_id !== Auth::id()) {
            abort(403);
        }

        $format = $request->input('format', 'markdown');

        if ($format === 'json') {
            return response($exporter->exportAsJson($conversation))
                ->header('Content-Type', 'application/json')
                ->header('Content-Disposition', 'attachment; filename="conversation-' . $conversation->id . '.json"');
        }

        return response($exporter->exportAsMarkdown($conversation))
            ->header('Content-Type', 'text/markdown')
            ->header('Content-Disposition', 'attachment; filename="conversation-' . $conversation->id . '.md"');
    }
}
```

## Testing

```php
<?php
# filename: tests/Feature/ChatTest.php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_conversation(): void
    {
        $user = User::factory()->create();

        $conversation = Conversation::create([
            'user_id' => $user->id,
            'model' => 'claude-sonnet-4-5-20250929',
        ]);

        $this->assertDatabaseHas('conversations', [
            'id' => $conversation->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_send_message(): void
    {
        $user = User::factory()->create();
        $conversation = Conversation::factory()->create(['user_id' => $user->id]);

        // Mock ChatService
        $mockService = Mockery::mock(ChatService::class);
        $mockService->shouldReceive('streamMessage')
            ->once()
            ->andReturnUsing(function ($conv, $msg, $callback) {
                $callback('Hello from Claude!');
            });

        $this->app->instance(ChatService::class, $mockService);

        $this->actingAs($user)
            ->livewire(Chat::class, ['conversationId' => $conversation->id])
            ->set('message', 'Hello')
            ->call('sendMessage')
            ->assertHasNoErrors();
    }

    public function test_user_cannot_access_other_users_conversations(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $conversation = Conversation::factory()->create(['user_id' => $user2->id]);

        $this->actingAs($user1)
            ->get(route('chat', $conversation))
            ->assertStatus(403);
    }
}
```

## Exercises

### Exercise 1: Add Message Editing

**Goal**: Allow users to edit their previous messages and regenerate Claude's response.

**Requirements:**

- Add `edited_at` timestamp column to `messages` table
- Create a Livewire method `editMessage()` that:
  - Verifies the message belongs to the authenticated user
  - Updates the message content
  - Stores the original content in `metadata` for history
  - Marks the message as edited with a timestamp
  - Optionally regenerates Claude's response
- Add UI controls (edit button, inline editing) to the chat view
- Show "edited" indicator in the message UI

**Validation**: Test that:

- Users can only edit their own messages
- Edited messages show an "edited" indicator
- Original content is preserved in metadata
- Regenerated responses maintain conversation context

```php
<?php
# filename: app/Livewire/Chat.php
public function editMessage(int $messageId, string $newContent): void
{
    $message = Message::where('id', $messageId)
        ->whereHas('conversation', fn($q) => $q->where('user_id', Auth::id()))
        ->firstOrFail();

    if ($message->role !== 'user') {
        throw new \Exception('Only user messages can be edited');
    }

    $message->update([
        'content' => $newContent,
        'edited_at' => now(),
        'metadata' => array_merge($message->metadata ?? [], [
            'edit_history' => array_merge($message->metadata['edit_history'] ?? [], [
                [
                    'original_content' => $message->content,
                    'edited_at' => now()->toIso8601String(),
                ]
            ])
        ])
    ]);

    // Optionally regenerate Claude's response
    // $this->regenerateResponse($message);
}
```

### Exercise 2: Implement Conversation Folders

**Goal**: Add folder organization so users can organize conversations into categories.

**Requirements:**

- Create `conversation_folders` migration with:
  - `id`, `user_id`, `name`, `color` (hex color), `order`, `timestamps`
- Add `folder_id` foreign key to `conversations` table
- Create `ConversationFolder` model with relationships
- Update `ConversationList` component to:
  - Show folders in sidebar
  - Allow creating/editing/deleting folders
  - Filter conversations by folder
  - Drag-and-drop conversations between folders
- Add folder management UI (create, rename, delete, change color)

**Validation**: Test that:

- Users can only access their own folders
- Conversations can be moved between folders
- Deleting a folder moves conversations to "Uncategorized"
- Folder colors persist and display correctly

```php
<?php
# filename: app/Models/ConversationFolder.php
class ConversationFolder extends Model
{
    protected $fillable = ['user_id', 'name', 'color', 'order'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class)->orderBy('last_message_at', 'desc');
    }
}
```

### Exercise 3: Add Message Reactions

**Goal**: Let users provide feedback on assistant messages with reactions (thumbs up/down).

**Requirements:**

- Create `message_reactions` migration with:
  - `id`, `message_id`, `user_id`, `type` (enum: 'positive', 'negative'), `timestamps`
  - Unique constraint on `message_id` + `user_id` (one reaction per user per message)
- Create `MessageReaction` model with relationships
- Add reaction buttons to assistant messages in the chat view
- Track reactions in analytics for quality improvement
- Show reaction counts in the UI
- Add API endpoint to fetch reaction statistics

**Validation**: Test that:

- Users can only react to assistant messages
- Users can change their reaction (update existing)
- Reaction counts are accurate
- Analytics can aggregate reaction data

```php
<?php
# filename: app/Models/MessageReaction.php
class MessageReaction extends Model
{
    protected $fillable = ['message_id', 'user_id', 'type'];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePositive($query)
    {
        return $query->where('type', 'positive');
    }

    public function scopeNegative($query)
    {
        return $query->where('type', 'negative');
    }
}
```

**Additional Considerations:**

- **Exercise 1**: Consider adding a "Regenerate Response" button that calls Claude again with the edited message
- **Exercise 2**: Implement drag-and-drop using Alpine.js or a library like Sortable.js
- **Exercise 3**: Create a dashboard showing reaction analytics to identify which responses users find helpful

## Troubleshooting

**Messages not streaming?**

- Ensure Livewire is properly configured for streaming
- Check browser console for JavaScript errors
- Verify SSE connection is not blocked by middleware

**Conversation not loading?**

- Check database relationships are eager loaded
- Verify user authorization in route middleware
- Ensure conversation belongs to authenticated user

**High database load?**

- Add indexes on `conversation_id` and `created_at`
- Implement pagination for long conversations
- Cache recent conversations in Redis

**Memory issues with long conversations?**

- Context window management is automatically handled by `getFormattedMessagesForContext()`
- Old messages are automatically excluded when approaching token limits
- Consider implementing message summarization for very old conversations

**Search not working?**

- Ensure the search input is properly bound with `wire:model.live.debounce.300ms`
- Check that database indexes exist on `title` and `messages.content` columns
- Verify the `loadConversations()` method is being called on search updates

**Settings not saving?**

- Verify the `updateSettings()` method validates input properly
- Check that the conversation belongs to the authenticated user
- Ensure the modal is properly closed after saving

**Pagination not showing?**

- Verify `$conversations` is a paginated result, not a collection
- Check that `paginate()` is called instead of `get()` in `loadConversations()`
- Ensure Livewire pagination component is included

## Deployment Considerations

Before deploying to production, consider these important aspects:

### Queue Workers

For better performance, process long-running Claude requests via queues:

```php
# In ChatService, dispatch to queue for long conversations
if ($conversation->messages()->count() > 50) {
    ProcessChatMessage::dispatch($conversation, $content);
    return;
}
```

**Setup**:

```bash
# Start queue worker
php artisan queue:work --tries=3 --timeout=300
```

### Caching Strategy

Cache frequently accessed data:

```php
// Cache conversation list
$conversations = Cache::remember("user_conversations_{$userId}", 300, function () {
    return Conversation::where('user_id', $userId)->get();
});
```

### Database Optimization

- Add indexes on frequently queried columns
- Consider partitioning the `messages` table by date for very large datasets
- Use database read replicas for read-heavy workloads

### Rate Limiting

Configure rate limiting per user to prevent abuse:

```php
// In routes/web.php
Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    Route::get('/chat/{conversation?}', Chat::class)->name('chat');
});
```

### Monitoring

Set up monitoring for:

- API response times
- Error rates
- Cost per user/conversation
- Queue processing times
- Database query performance

### Environment Variables

Add these to your `.env` file:

```sh
# Anthropic Claude API
ANTHROPIC_API_KEY=your_anthropic_api_key_here

# Claude Configuration
CLAUDE_DEFAULT_MODEL=claude-sonnet-4-5-20250929

# Optional: For production performance
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

### Service Configuration

Create `config/services.php` if it doesn't exist and add:

```php
<?php
# filename: config/services.php

return [
    // ... existing config

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'default_model' => env('CLAUDE_DEFAULT_MODEL', 'claude-sonnet-4-5-20250929'),
    ],
];
```

### Security Checklist

- [ ] Verify authentication middleware on all routes
- [ ] Ensure user can only access their own conversations
- [ ] Validate all user inputs
- [ ] Sanitize conversation titles and messages
- [ ] Implement CSRF protection (Livewire handles this automatically)
- [ ] Rate limit API calls per user
- [ ] Log all API errors for monitoring
- [ ] Use HTTPS in production
- [ ] Set secure session cookies
- [ ] Implement proper error handling (don't expose API keys)

## Further Reading

- **[Official PHP SDK Documentation](https://github.com/anthropics/anthropic-sdk-php)** — The official Anthropic PHP SDK on GitHub
- **[Claude-PHP-SDK](https://github.com/claude-php/Claude-PHP-SDK)** — Community resources and examples for Claude with PHP
- **[Anthropic API Documentation](https://docs.anthropic.com)** — Complete API reference and guides
- **[PHP SDK Composer Package](https://packagist.org/packages/claude-php/claude-php-sdk)** — Official package on Packagist

## Wrap-up

Congratulations! You've built a complete, production-ready chatbot application with Laravel 11, Livewire 3, and Claude-PHP-SDK v0.2. Here's what you've accomplished:

- ✓ **Database Architecture**: Designed a scalable schema with conversations and messages, including proper relationships, indexes, and soft deletes
- ✓ **Eloquent Models**: Created `Conversation` and `Message` models with relationships, accessors, and business logic methods
- ✓ **Chat Service**: Built a service class that handles message sending, streaming, cost calculation, and conversation management with comprehensive error handling
- ✓ **Livewire Components**: Created reactive UI components for chat and conversation list management with proper authentication checks
- ✓ **Real-time Streaming**: Implemented streaming responses using Server-Sent Events for better user experience
- ✓ **User Authentication**: Integrated Laravel's authentication system to ensure users only access their own conversations
- ✓ **Cost Tracking**: Implemented per-message cost and token tracking for analytics and budgeting
- ✓ **Rate Limiting**: Added per-user rate limiting to prevent abuse and control API costs (60 messages/hour)
- ✓ **Export Functionality**: Built conversation export in both Markdown and JSON formats
- ✓ **Comprehensive Testing**: Created test suite with proper mocking and database testing
- ✓ **Beautiful UI**: Designed a modern, responsive chat interface with TailwindCSS
- ✓ **Production Security**: Implemented proper input validation, error handling, and authentication checks
- ✓ **SDK Compatibility**: Fully compatible with Claude-PHP-SDK v0.2 using correct API patterns and response handling

You now have a fully-functional chatbot application that can handle multiple users, maintain conversation history, stream responses in real-time, and provide a professional chat experience. The architecture is clean, maintainable, and ready for production deployment.

## Key Takeaways

- ✓ **Database Design** separates conversations and messages for flexibility
- ✓ **Livewire** enables reactive UI without complex JavaScript
- ✓ **Streaming** improves UX for long responses
- ✓ **Authentication** ensures users only access their data
- ✓ **Cost Tracking** at message level enables analytics
- ✓ **Export Functionality** provides data portability
- ✓ **Rate Limiting** prevents abuse and controls costs
- ✓ **Clean Architecture** makes the system maintainable and testable

## Further Reading

- [Laravel Livewire Documentation](https://livewire.laravel.com/docs) — Official Livewire documentation for reactive components
- [Laravel Eloquent Relationships](https://laravel.com/docs/eloquent-relationships) — Complete guide to Eloquent relationships
- [Laravel Broadcasting](https://laravel.com/docs/broadcasting) — Real-time event broadcasting for WebSockets
- [Server-Sent Events (SSE) Specification](https://developer.mozilla.org/en-US/docs/Web/API/Server-sent_events) — Understanding SSE for streaming
- [Anthropic Claude API Documentation](https://docs.claude.com) — Official Claude API reference
- [Laravel Rate Limiting](https://laravel.com/docs/rate-limiting) — Built-in rate limiting features
- [Chapter 21: Laravel Integration Patterns](/series/claude-php-developers/chapters/21-laravel-integration) — Foundation for Laravel integration
- [Chapter 20: Real-time Chat with WebSockets](/series/claude-php-developers/chapters/20-realtime-chat-websockets) — Related chapter on real-time systems
- [Chapter 23: Claude-Powered Form Validation](/series/claude-php-developers/chapters/23-ai-form-validation) — Next chapter on AI-powered validation

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="22"
  label="You've built a production-ready chatbot with Laravel!"
/>

---

Continue to [Chapter 23: Claude-Powered Form Validation](/series/claude-php-developers/chapters/23-ai-form-validation) to add intelligent validation to your forms.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository and have been tested to work correctly with:

- Laravel 11
- Livewire 3
- Claude-PHP-SDK v0.2
- PHP 8.4

**[View Chapter 22 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-22)**

Clone and run locally:

```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-22

# Install dependencies
composer install
composer require claude-php/claude-php-sdk:^0.2

# Setup environment
cp .env.example .env
# Edit .env and add your ANTHROPIC_API_KEY

# Setup database
php artisan migrate

# Install and build frontend assets
npm install && npm run build

# Start the development server
php artisan serve
```

### Verification Commands

After setup, verify everything works:

```bash
# Check Laravel version
php artisan --version

# Check Livewire version
php artisan livewire:version

# Check SDK installation
composer show claude-php/claude-php-sdk

# Run tests
php artisan test
```

The code samples include comprehensive error handling, security measures, and production-ready patterns that work seamlessly with the Claude-PHP-SDK v0.2.
