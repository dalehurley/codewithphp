---
title: "22: Building a Chatbot with Laravel"
description: "Build a production-ready chatbot with Laravel, Livewire, and Claude. Implement conversation persistence, user authentication, message history, streaming responses, and a beautiful reactive UI."
series: "claude-php-developers"
chapter: 22
order: 22
difficulty: "Intermediate"
prerequisites:
  - "Laravel 11+ with Livewire 3"
  - "Database configured (MySQL/PostgreSQL)"
  - "Completion of Chapter 21"
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

**What You'll Learn:**
- Database schema design for conversations and messages
- User authentication and authorization
- Livewire components for reactive chat UI
- Real-time streaming with Server-Sent Events
- Conversation management and context handling
- Message persistence and history
- Rate limiting per user
- Export and sharing functionality
- Typing indicators and UX polish
- Deployment considerations

**Estimated Time**: 120-150 minutes

## Prerequisites

Before starting, ensure you have:

- ✓ **Laravel 11+** with Livewire 3 installed
- ✓ **Database** configured (MySQL or PostgreSQL)
- ✓ **Authentication** set up (Laravel Breeze or Jetstream)
- ✓ **Claude service** from Chapter 21
- ✓ **Basic Livewire knowledge**
- ✓ **TailwindCSS** for styling

## Database Schema Design

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
            $table->string('model')->default('claude-sonnet-4-20250514');
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

## Models

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

## Chat Service

```php
<?php
# filename: app/Services/ChatService.php
declare(strict_types=1);

namespace App\Services;

use App\Facades\Claude;
use App\Models\Conversation;
use App\Models\Message;

class ChatService
{
    private const PRICING = [
        'claude-opus-4-20250514' => ['input' => 15.00, 'output' => 75.00],
        'claude-sonnet-4-20250514' => ['input' => 3.00, 'output' => 15.00],
        'claude-haiku-4-20250514' => ['input' => 0.25, 'output' => 1.25],
    ];

    public function sendMessage(
        Conversation $conversation,
        string $content
    ): Message {
        // Create user message
        $userMessage = $conversation->messages()->create([
            'role' => 'user',
            'content' => $content,
        ]);

        // Get conversation history
        $history = $conversation->formatted_messages;

        // Get response from Claude
        $result = Claude::withModel($conversation->model)
            ->chat($content, array_slice($history, 0, -1), $conversation->system_prompt);

        // Calculate cost
        $cost = $this->calculateCost(
            $conversation->model,
            $result['usage']['input_tokens'],
            $result['usage']['output_tokens']
        );

        // Create assistant message
        $assistantMessage = $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $result['response'],
            'input_tokens' => $result['usage']['input_tokens'],
            'output_tokens' => $result['usage']['output_tokens'],
            'cost' => $cost,
        ]);

        // Update conversation
        $conversation->update([
            'last_message_at' => now(),
        ]);

        // Generate title if needed
        $conversation->generateTitle();

        return $assistantMessage;
    }

    public function streamMessage(
        Conversation $conversation,
        string $content,
        callable $callback
    ): void {
        // Create user message
        $userMessage = $conversation->messages()->create([
            'role' => 'user',
            'content' => $content,
        ]);

        $fullResponse = '';
        $history = $conversation->formatted_messages;

        // Build prompt with history
        $messages = array_slice($history, 0, -1);

        // Stream response
        Claude::withModel($conversation->model)
            ->stream(
                $content,
                function ($chunk) use (&$fullResponse, $callback) {
                    $fullResponse .= $chunk;
                    $callback($chunk);
                },
                [
                    'system' => $conversation->system_prompt,
                ]
            );

        // Create assistant message with full response
        $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $fullResponse,
        ]);

        $conversation->update(['last_message_at' => now()]);
        $conversation->generateTitle();
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

## Livewire Components

### Chat Component

```php
<?php
# filename: app/Livewire/Chat.php
declare(strict_types=1);

namespace App\Livewire;

use App\Models\Conversation;
use App\Services\ChatService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class Chat extends Component
{
    public ?Conversation $conversation = null;
    public string $message = '';
    public bool $isStreaming = false;
    public string $streamingResponse = '';

    protected $rules = [
        'message' => 'required|string|max:10000',
    ];

    public function mount(?int $conversationId = null)
    {
        if ($conversationId) {
            $this->conversation = Conversation::where('user_id', Auth::id())
                ->findOrFail($conversationId);
        } else {
            $this->createNewConversation();
        }
    }

    public function createNewConversation(): void
    {
        $this->conversation = Conversation::create([
            'user_id' => Auth::id(),
            'model' => config('claude.default_model'),
        ]);

        $this->redirect(route('chat', ['conversation' => $this->conversation->id]));
    }

    public function sendMessage(ChatService $chatService): void
    {
        $this->validate();

        if ($this->isStreaming) {
            return;
        }

        $this->isStreaming = true;
        $this->streamingResponse = '';

        try {
            $chatService->streamMessage(
                $this->conversation,
                $this->message,
                function ($chunk) {
                    $this->streamingResponse .= $chunk;
                    $this->dispatch('message-chunk', chunk: $chunk);
                }
            );

            $this->message = '';
            $this->conversation->refresh();
        } catch (\Exception $e) {
            $this->addError('message', 'Failed to send message: ' . $e->getMessage());
        } finally {
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
    public $conversations;
    public ?int $activeConversationId = null;

    public function mount(?int $activeId = null)
    {
        $this->activeConversationId = $activeId;
        $this->loadConversations();
    }

    public function loadConversations(): void
    {
        $this->conversations = Conversation::where('user_id', Auth::id())
            ->orderBy('last_message_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
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

## Views

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
                    wire:click="deleteConversation"
                    wire:confirm="Are you sure you want to delete this conversation?"
                    class="px-4 py-2 text-sm font-medium text-red-700 bg-white border border-red-300 rounded-md hover:bg-red-50"
                >
                    Delete
                </button>
            @endif
        </div>
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
                            @if($message->isAssistant() && $message->cost)
                                <div class="mt-2 text-xs text-gray-500">
                                    Cost: ${{ number_format($message->cost, 6) }} •
                                    Tokens: {{ $message->input_tokens + $message->output_tokens }}
                                </div>
                            @endif
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

### Conversation List View

```blade
{{-- filename: resources/views/livewire/conversation-list.blade.php --}}
<div class="h-full flex flex-col bg-gray-100">
    <div class="p-4 border-b border-gray-200 bg-white">
        <h3 class="text-lg font-semibold text-gray-900">Conversations</h3>
    </div>

    <div class="flex-1 overflow-y-auto">
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

use App\Livewire\Chat;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/chat/{conversation?}', Chat::class)->name('chat');
});
```

## Advanced Features

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
        return json_encode([
            'id' => $conversation->id,
            'title' => $conversation->title,
            'model' => $conversation->model,
            'created_at' => $conversation->created_at,
            'messages' => $conversation->messages->map(function ($message) {
                return [
                    'role' => $message->role,
                    'content' => $message->content,
                    'tokens' => [
                        'input' => $message->input_tokens,
                        'output' => $message->output_tokens,
                    ],
                    'cost' => $message->cost,
                    'created_at' => $message->created_at,
                ];
            }),
            'total_cost' => $conversation->total_cost,
            'total_tokens' => $conversation->total_tokens,
        ], JSON_PRETTY_PRINT);
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
            'model' => 'claude-sonnet-4-20250514',
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
        $mockService->shouldReceive('sendMessage')
            ->once()
            ->andReturn(new \App\Models\Message([
                'role' => 'assistant',
                'content' => 'Test response',
            ]));

        $this->app->instance(ChatService::class, $mockService);

        $this->actingAs($user)
            ->post(route('chat.send', $conversation), [
                'message' => 'Hello',
            ])
            ->assertStatus(200);
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

Allow users to edit their previous messages:

```php
<?php
public function editMessage(int $messageId, string $newContent): void
{
    // TODO: Find message, verify ownership
    // TODO: Update message content
    // TODO: Mark as edited with timestamp
}
```

### Exercise 2: Implement Conversation Folders

Add folder organization for conversations:

```php
<?php
class ConversationFolder extends Model
{
    // TODO: Create migration
    // TODO: Add relationships
    // TODO: Update UI to show folders
}
```

### Exercise 3: Add Message Reactions

Let users react to assistant messages:

```php
<?php
class MessageReaction extends Model
{
    // TODO: Migration for reactions (thumbs up/down)
    // TODO: Track feedback for quality improvement
    // TODO: UI component for reactions
}
```

<details>
<summary>Solution Hints</summary>

**Exercise 1**: Add `edited_at` column to messages table. Create Livewire method to toggle edit mode. Store original content in `metadata` for history.

**Exercise 2**: Create `conversation_folders` table with `user_id`, `name`, `color`. Add `folder_id` to conversations. Update sidebar to group by folder.

**Exercise 3**: Create `message_reactions` table with `message_id`, `user_id`, `type` (positive/negative). Add buttons to message UI. Track in analytics.

</details>

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
- Implement context window management
- Truncate old messages before sending to Claude
- Store summarized history for very old messages

## Key Takeaways

- ✓ **Database Design** separates conversations and messages for flexibility
- ✓ **Livewire** enables reactive UI without complex JavaScript
- ✓ **Streaming** improves UX for long responses
- ✓ **Authentication** ensures users only access their data
- ✓ **Cost Tracking** at message level enables analytics
- ✓ **Export Functionality** provides data portability
- ✓ **Rate Limiting** prevents abuse and controls costs
- ✓ **Clean Architecture** makes the system maintainable and testable

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="22"
  label="You've built a production-ready chatbot with Laravel!"
/>

---

Continue to [Chapter 23: Claude-Powered Form Validation](/series/claude-php-developers/chapters/23-ai-form-validation) to add intelligent validation to your forms.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 22 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-22)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-22
composer install
npm install && npm run dev
cp .env.example .env
# Add your ANTHROPIC_API_KEY to .env
php artisan migrate
php artisan serve
```
