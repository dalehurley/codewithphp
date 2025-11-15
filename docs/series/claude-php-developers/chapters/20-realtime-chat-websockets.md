---
title: "20: Real-time Chat with WebSockets"
description: "Build interactive real-time chat applications with Claude using Laravel Reverb, WebSockets, streaming responses to multiple clients, typing indicators, and presence channels."
series: "claude-php-developers"
chapter: 20
order: 20
difficulty: "Expert"
prerequisites:
  - "Laravel 11+ with Reverb"
  - "WebSockets understanding"
  - "Laravel Broadcasting knowledge"
  - "Chapter 06: Streaming Responses"
---

![20: Real-time Chat with WebSockets](/images/claude-php/chapter-20-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 20</span>
</div>

# Chapter 20: Real-time Chat with WebSockets

## Overview

Building a real-time chat application with Claude requires streaming responses over WebSockets, managing conversation state, handling typing indicators, and broadcasting updates to multiple clients. Laravel Reverb provides a first-party WebSocket server that makes this seamless.

This chapter teaches you to build production-ready chat applications with streaming Claude responses, real-time typing indicators, presence tracking, and multi-user chat rooms—all powered by WebSockets.

## Prerequisites

Before diving in, ensure you have:

- ✓ **Laravel 11+** with Reverb installed
- ✓ **Laravel Broadcasting** configured
- ✓ **Chapter 06** completed (Streaming knowledge)
- ✓ **WebSockets** basic understanding

**Estimated Time**: 75-90 minutes

## Real-Time Chat Architecture

```
User Browser
  ↓ (WebSocket)
Laravel Reverb Server
  ↓
Laravel Application
  ↓
Claude API (Streaming)
  ↓ (Stream chunks)
Broadcast to WebSocket
  ↓
All Connected Clients
```

## Setup Laravel Reverb

```bash
# Install Reverb
composer require laravel/reverb

# Publish configuration
php artisan reverb:install

# Update .env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

```bash
# Start Reverb server
php artisan reverb:start

# Start queue worker (for processing)
php artisan queue:work
```

## Database Setup

```php
<?php
# filename: database/migrations/2025_01_01_000001_create_chat_tables.php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('model')->default('claude-sonnet-4-20250514');
            $table->text('system_prompt')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('chat_conversations')->cascadeOnDelete();
            $table->enum('role', ['user', 'assistant', 'system']);
            $table->longText('content');
            $table->json('metadata')->nullable();
            $table->boolean('is_streaming')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversations');
    }
};
```

## Models

```php
<?php
# filename: app/Models/ChatConversation.php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatConversation extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'model',
        'system_prompt',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    public function getFormattedMessages(): array
    {
        return $this->messages()
            ->where('role', '!=', 'system')
            ->orderBy('created_at')
            ->get()
            ->map(fn($msg) => [
                'role' => $msg->role,
                'content' => $msg->content,
            ])
            ->toArray();
    }
}
```

```php
<?php
# filename: app/Models/ChatMessage.php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'metadata',
        'is_streaming',
        'completed_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_streaming' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }
}
```

## Streaming Events

```php
<?php
# filename: app/Events/MessageChunkReceived.php
declare(strict_types=1);

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageChunkReceived implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $conversationId,
        public int $messageId,
        public string $chunk
    ) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('conversation.' . $this->conversationId);
    }

    public function broadcastAs(): string
    {
        return 'message.chunk';
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->messageId,
            'chunk' => $this->chunk,
        ];
    }
}
```

```php
<?php
# filename: app/Events/MessageCompleted.php
declare(strict_types=1);

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ChatMessage $message
    ) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('conversation.' . $this->message->conversation_id);
    }

    public function broadcastAs(): string
    {
        return 'message.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'role' => $this->message->role,
                'content' => $this->message->content,
                'completed_at' => $this->message->completed_at,
            ],
        ];
    }
}
```

```php
<?php
# filename: app/Events/UserTyping.php
declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $conversationId,
        public int $userId,
        public string $userName,
        public bool $isTyping
    ) {}

    public function broadcastOn(): Channel
    {
        return new PresenceChannel('conversation.' . $this->conversationId);
    }

    public function broadcastAs(): string
    {
        return 'user.typing';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'is_typing' => $this->isTyping,
        ];
    }
}
```

## Chat Service with Streaming

```php
<?php
# filename: app/Services/ChatService.php
declare(strict_types=1);

namespace App\Services;

use App\Events\MessageChunkReceived;
use App\Events\MessageCompleted;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Anthropic\Contracts\ClientContract;
use Illuminate\Support\Facades\Log;

class ChatService
{
    public function __construct(
        private ClientContract $client
    ) {}

    public function sendMessage(
        ChatConversation $conversation,
        string $userMessage
    ): ChatMessage {
        // Store user message
        $userMsg = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $userMessage,
            'completed_at' => now(),
        ]);

        // Create assistant message placeholder
        $assistantMsg = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => '',
            'is_streaming' => true,
        ]);

        // Get conversation history
        $messages = $conversation->getFormattedMessages();

        // Stream Claude response
        $this->streamResponse($conversation, $assistantMsg, $messages);

        return $assistantMsg;
    }

    private function streamResponse(
        ChatConversation $conversation,
        ChatMessage $assistantMsg,
        array $messages
    ): void {
        try {
            $fullContent = '';

            $params = [
                'model' => $conversation->model,
                'max_tokens' => 4096,
                'messages' => $messages,
            ];

            if ($conversation->system_prompt) {
                $params['system'] = $conversation->system_prompt;
            }

            $stream = $this->client->messages()->createStreamed($params);

            foreach ($stream as $event) {
                if ($event->type === 'content_block_delta') {
                    $chunk = $event->delta->text ?? '';

                    if ($chunk) {
                        $fullContent .= $chunk;

                        // Broadcast chunk to WebSocket clients
                        broadcast(new MessageChunkReceived(
                            conversationId: $conversation->id,
                            messageId: $assistantMsg->id,
                            chunk: $chunk
                        ))->toOthers();
                    }
                }
            }

            // Update message with full content
            $assistantMsg->update([
                'content' => $fullContent,
                'is_streaming' => false,
                'completed_at' => now(),
            ]);

            // Broadcast completion
            broadcast(new MessageCompleted($assistantMsg));

        } catch (\Exception $e) {
            Log::error('Chat streaming error', [
                'conversation_id' => $conversation->id,
                'message_id' => $assistantMsg->id,
                'error' => $e->getMessage(),
            ]);

            $assistantMsg->update([
                'content' => 'Error: ' . $e->getMessage(),
                'is_streaming' => false,
                'completed_at' => now(),
            ]);

            throw $e;
        }
    }
}
```

## Controller Implementation

```php
<?php
# filename: app/Http/Controllers/ChatController.php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\UserTyping;
use App\Models\ChatConversation;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(
        private ChatService $chatService
    ) {}

    /**
     * Create a new conversation
     */
    public function createConversation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'model' => 'nullable|string|in:claude-opus-4-20250514,claude-sonnet-4-20250514,claude-haiku-4-20250514',
            'system_prompt' => 'nullable|string|max:10000',
        ]);

        $conversation = ChatConversation::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'] ?? 'New Conversation',
            'model' => $validated['model'] ?? 'claude-sonnet-4-20250514',
            'system_prompt' => $validated['system_prompt'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'conversation' => $conversation,
        ], 201);
    }

    /**
     * Send a message (triggers streaming)
     */
    public function sendMessage(Request $request, ChatConversation $conversation): JsonResponse
    {
        $this->authorize('update', $conversation);

        $validated = $request->validate([
            'message' => 'required|string|max:50000',
        ]);

        try {
            $message = $this->chatService->sendMessage(
                conversation: $conversation,
                userMessage: $validated['message']
            );

            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'role' => $message->role,
                    'is_streaming' => $message->is_streaming,
                ],
            ], 202);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get conversation with messages
     */
    public function show(ChatConversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        $conversation->load('messages');

        return response()->json([
            'conversation' => $conversation,
            'messages' => $conversation->messages->map(fn($msg) => [
                'id' => $msg->id,
                'role' => $msg->role,
                'content' => $msg->content,
                'is_streaming' => $msg->is_streaming,
                'created_at' => $msg->created_at,
                'completed_at' => $msg->completed_at,
            ]),
        ]);
    }

    /**
     * List user conversations
     */
    public function index(Request $request): JsonResponse
    {
        $conversations = ChatConversation::where('user_id', $request->user()->id)
            ->withCount('messages')
            ->orderByDesc('updated_at')
            ->paginate(20);

        return response()->json($conversations);
    }

    /**
     * Broadcast typing indicator
     */
    public function typing(Request $request, ChatConversation $conversation): JsonResponse
    {
        $this->authorize('update', $conversation);

        $validated = $request->validate([
            'is_typing' => 'required|boolean',
        ]);

        broadcast(new UserTyping(
            conversationId: $conversation->id,
            userId: $request->user()->id,
            userName: $request->user()->name,
            isTyping: $validated['is_typing']
        ));

        return response()->json(['success' => true]);
    }
}
```

## Frontend Implementation (Vue 3)

```vue
<!-- filename: resources/js/components/ChatWindow.vue -->
<template>
  <div class="chat-window">
    <div class="chat-header">
      <h2>{{ conversation.title }}</h2>
      <span v-if="typingUsers.length" class="typing-indicator">
        {{ typingUsers.join(', ') }} {{ typingUsers.length === 1 ? 'is' : 'are' }} typing...
      </span>
    </div>

    <div class="chat-messages" ref="messagesContainer">
      <div
        v-for="message in messages"
        :key="message.id"
        :class="['message', message.role]"
      >
        <div class="message-content">
          {{ message.content }}
          <span v-if="message.is_streaming" class="streaming-cursor">▊</span>
        </div>
        <div class="message-time">
          {{ formatTime(message.created_at) }}
        </div>
      </div>
    </div>

    <div class="chat-input">
      <textarea
        v-model="newMessage"
        @keydown.enter.prevent="sendMessage"
        @input="handleTyping"
        placeholder="Type your message..."
        :disabled="isSending"
      ></textarea>
      <button @click="sendMessage" :disabled="!newMessage.trim() || isSending">
        Send
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue';
import Echo from 'laravel-echo';
import axios from 'axios';

const props = defineProps({
  conversationId: {
    type: Number,
    required: true
  }
});

const conversation = ref({});
const messages = ref([]);
const newMessage = ref('');
const isSending = ref(false);
const typingUsers = ref([]);
const messagesContainer = ref(null);
let typingTimeout = null;
let channel = null;

onMounted(async () => {
  await loadConversation();
  setupWebSocket();
});

onUnmounted(() => {
  if (channel) {
    window.Echo.leave(`conversation.${props.conversationId}`);
  }
});

watch(messages, () => {
  nextTick(() => scrollToBottom());
}, { deep: true });

async function loadConversation() {
  const response = await axios.get(`/api/conversations/${props.conversationId}`);
  conversation.value = response.data.conversation;
  messages.value = response.data.messages;
}

function setupWebSocket() {
  channel = window.Echo.private(`conversation.${props.conversationId}`);

  // Listen for message chunks
  channel.listen('.message.chunk', (event) => {
    const message = messages.value.find(m => m.id === event.message_id);
    if (message) {
      message.content += event.chunk;
    }
  });

  // Listen for message completion
  channel.listen('.message.completed', (event) => {
    const message = messages.value.find(m => m.id === event.message.id);
    if (message) {
      message.is_streaming = false;
      message.content = event.message.content;
      message.completed_at = event.message.completed_at;
    }
  });

  // Listen for typing indicators
  channel.listen('.user.typing', (event) => {
    if (event.is_typing) {
      if (!typingUsers.value.includes(event.user_name)) {
        typingUsers.value.push(event.user_name);
      }
    } else {
      typingUsers.value = typingUsers.value.filter(u => u !== event.user_name);
    }
  });
}

async function sendMessage() {
  if (!newMessage.value.trim() || isSending.value) return;

  const messageText = newMessage.value;
  newMessage.value = '';
  isSending.value = true;

  // Stop typing indicator
  broadcastTyping(false);

  try {
    // Add user message immediately
    const userMsg = {
      id: Date.now(),
      role: 'user',
      content: messageText,
      created_at: new Date().toISOString(),
      is_streaming: false
    };
    messages.value.push(userMsg);

    // Send to backend
    const response = await axios.post(
      `/api/conversations/${props.conversationId}/messages`,
      { message: messageText }
    );

    // Add streaming assistant message placeholder
    if (response.data.success) {
      messages.value.push({
        id: response.data.message.id,
        role: 'assistant',
        content: '',
        created_at: new Date().toISOString(),
        is_streaming: true
      });
    }
  } catch (error) {
    console.error('Failed to send message:', error);
    alert('Failed to send message. Please try again.');
  } finally {
    isSending.value = false;
  }
}

function handleTyping() {
  broadcastTyping(true);

  clearTimeout(typingTimeout);
  typingTimeout = setTimeout(() => {
    broadcastTyping(false);
  }, 1000);
}

async function broadcastTyping(isTyping) {
  try {
    await axios.post(`/api/conversations/${props.conversationId}/typing`, {
      is_typing: isTyping
    });
  } catch (error) {
    console.error('Failed to broadcast typing:', error);
  }
}

function scrollToBottom() {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
}

function formatTime(dateString) {
  const date = new Date(dateString);
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}
</script>

<style scoped>
.chat-window {
  display: flex;
  flex-direction: column;
  height: 100vh;
  max-width: 800px;
  margin: 0 auto;
}

.chat-header {
  padding: 1rem;
  background: #f5f5f5;
  border-bottom: 1px solid #ddd;
}

.typing-indicator {
  font-size: 0.875rem;
  color: #666;
  font-style: italic;
}

.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  background: #fff;
}

.message {
  margin-bottom: 1rem;
  max-width: 70%;
}

.message.user {
  margin-left: auto;
  text-align: right;
}

.message.user .message-content {
  background: #007bff;
  color: white;
  border-radius: 1rem 1rem 0 1rem;
}

.message.assistant .message-content {
  background: #f0f0f0;
  color: #333;
  border-radius: 1rem 1rem 1rem 0;
}

.message-content {
  padding: 0.75rem 1rem;
  display: inline-block;
  word-wrap: break-word;
}

.streaming-cursor {
  animation: blink 1s infinite;
  margin-left: 0.25rem;
}

@keyframes blink {
  0%, 49% { opacity: 1; }
  50%, 100% { opacity: 0; }
}

.message-time {
  font-size: 0.75rem;
  color: #999;
  margin-top: 0.25rem;
}

.chat-input {
  padding: 1rem;
  background: #f5f5f5;
  border-top: 1px solid #ddd;
  display: flex;
  gap: 0.5rem;
}

.chat-input textarea {
  flex: 1;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 0.5rem;
  resize: none;
  min-height: 60px;
}

.chat-input button {
  padding: 0.75rem 1.5rem;
  background: #007bff;
  color: white;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
}

.chat-input button:disabled {
  background: #ccc;
  cursor: not-allowed;
}
</style>
```

## Broadcasting Channels Configuration

```php
<?php
# filename: routes/channels.php
declare(strict_types=1);

use App\Models\ChatConversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = ChatConversation::find($conversationId);

    return $conversation && $user->id === $conversation->user_id;
});
```

## API Routes

```php
<?php
# filename: routes/api.php
declare(strict_types=1);

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Conversations
    Route::get('/conversations', [ChatController::class, 'index']);
    Route::post('/conversations', [ChatController::class, 'createConversation']);
    Route::get('/conversations/{conversation}', [ChatController::class, 'show']);

    // Messages
    Route::post('/conversations/{conversation}/messages', [ChatController::class, 'sendMessage']);
    Route::post('/conversations/{conversation}/typing', [ChatController::class, 'typing']);
});
```

## Troubleshooting

**WebSocket connection fails?**
- Ensure Reverb is running: `php artisan reverb:start`
- Check `.env` configuration for `REVERB_*` variables
- Verify frontend Echo configuration matches backend
- Check browser console for connection errors

**Messages not streaming?**
- Verify broadcasting is configured: `BROADCAST_CONNECTION=reverb`
- Check channel authorization in `routes/channels.php`
- Ensure user is authenticated
- Review Laravel logs for errors

**Typing indicators not working?**
- Use PresenceChannel instead of PrivateChannel for presence features
- Verify Echo is properly initialized on frontend
- Check network tab for failed broadcast requests

**Performance issues with many concurrent users?**
- Scale Reverb horizontally with multiple instances
- Use Redis for horizontal scaling
- Implement message pagination
- Consider rate limiting typing indicator broadcasts

## Key Takeaways

- ✓ Laravel Reverb provides first-party WebSocket support
- ✓ Streaming Claude responses create engaging chat experiences
- ✓ Broadcasting enables real-time updates to all clients
- ✓ Typing indicators improve user experience
- ✓ Presence channels track who's online
- ✓ Proper error handling ensures reliability
- ✓ Authentication and authorization protect conversations

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="20"
  label="You've built a real-time chat application with Claude!"
/>

---

Congratulations on completing Chapter 20 and the PHP Integration Patterns section!

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 20 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-20)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-20
composer install
npm install
php artisan migrate
php artisan reverb:start
# In another terminal:
npm run dev
```
