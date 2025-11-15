# Chapter 22: Building a Chatbot with Laravel

Complete chatbot implementation using Laravel, Livewire, and Claude AI with conversation management, streaming responses, and persistent storage.

## Features

- **Livewire real-time interface** for seamless user experience
- **Conversation management** with persistent storage
- **Streaming responses** for real-time feedback
- **RESTful API** endpoints
- **User authentication** and authorization
- **Conversation history** and export
- **Token tracking** and usage analytics
- **Sentiment analysis** and intent detection

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
```

### Configure Database

Edit `.env`:

```bash
DB_DATABASE=claude_chatbot
ANTHROPIC_API_KEY=sk-ant-your-key-here
```

### Run Migrations

```bash
php artisan migrate
```

## Usage

### Livewire Component

Include in your Blade template:

```blade
<livewire:chatbot />
```

Or with a specific conversation:

```blade
<livewire:chatbot :conversation-id="$conversationId" />
```

### REST API Endpoints

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/conversations', [ChatbotController::class, 'createConversation']);
    Route::get('/conversations', [ChatbotController::class, 'listConversations']);
    Route::get('/conversations/{conversation}', [ChatbotController::class, 'getConversation']);
    Route::post('/conversations/{conversation}/messages', [ChatbotController::class, 'sendMessage']);
    Route::post('/conversations/{conversation}/stream', [ChatbotController::class, 'streamMessage']);
    Route::delete('/conversations/{conversation}', [ChatbotController::class, 'deleteConversation']);
    Route::post('/conversations/{conversation}/clear', [ChatbotController::class, 'clearConversation']);
    Route::get('/conversations/{conversation}/export', [ChatbotController::class, 'exportConversation']);
});
```

### Using the Chatbot Service

```php
use App\Services\ChatbotService;
use App\Models\Conversation;

$chatbot = app(ChatbotService::class);

// Send message
$response = $chatbot->sendMessage($conversation, 'Hello!');
echo $response['content'];

// Stream message
$chatbot->streamMessage(
    conversation: $conversation,
    message: 'Tell me a story',
    onChunk: function(string $chunk) {
        echo $chunk;
        flush();
    }
);

// Generate title
$title = $chatbot->generateTitle('What is Laravel?');

// Analyze sentiment
$sentiment = $chatbot->analyzeSentiment('I love this!'); // "positive"

// Detect intent
$intent = $chatbot->detectIntent('How do I install Laravel?'); // "question"
```

## Database Schema

### conversations

- `id` - Primary key
- `user_id` - Foreign key to users
- `title` - Conversation title
- `system_prompt` - Custom system prompt
- `metadata` - JSON metadata
- `created_at`, `updated_at`, `deleted_at`

### messages

- `id` - Primary key
- `conversation_id` - Foreign key to conversations
- `role` - enum('user', 'assistant', 'system')
- `content` - Message text
- `metadata` - JSON metadata (includes token usage)
- `created_at`, `updated_at`

## API Examples

### Create Conversation

```bash
curl -X POST http://localhost/api/conversations \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"
```

### Send Message

```bash
curl -X POST http://localhost/api/conversations/1/messages \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"message": "Hello, Claude!"}'
```

### Stream Message

```bash
curl -X POST http://localhost/api/conversations/1/stream \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"message": "Tell me a story"}' \
  --no-buffer
```

### Export Conversation

```bash
curl -X GET http://localhost/api/conversations/1/export \
  -H "Authorization: Bearer {token}" \
  -o conversation.txt
```

## Livewire Component Usage

The Livewire chatbot component provides:

- Real-time message sending
- Streaming response display
- Conversation management
- Auto-scrolling
- Typing indicators

### Events

```javascript
// Listen for streaming events
Livewire.on('start-streaming', (data) => {
    console.log('Starting stream for conversation', data.conversationId);
});

// Emit chunks
Livewire.dispatch('chunk-received', { chunk: 'Hello' });

// Complete streaming
Livewire.dispatch('streaming-complete', { content: fullMessage });
```

## Customization

### System Prompts

Customize the chatbot's behavior:

```php
$conversation = Conversation::create([
    'user_id' => Auth::id(),
    'title' => 'Code Review Assistant',
    'system_prompt' => 'You are an expert code reviewer. Provide constructive feedback on code quality, security, and best practices.',
]);
```

### Custom Metadata

Track additional information:

```php
$message = $conversation->messages()->create([
    'role' => 'assistant',
    'content' => $response['content'],
    'metadata' => [
        'model' => $response['model'],
        'tokens' => $response['usage'],
        'sentiment' => $chatbot->analyzeSentiment($response['content']),
        'custom_field' => 'value',
    ],
]);
```

## Testing

Run tests:

```bash
php artisan test
```

Example test:

```php
public function test_can_send_message()
{
    $conversation = Conversation::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson("/api/conversations/{$conversation->id}/messages", [
            'message' => 'Hello!',
        ]);

    $response->assertSuccessful();
    $this->assertCount(2, $conversation->fresh()->messages);
}
```

## Production Considerations

1. **Rate Limiting**: Implement per-user rate limits
2. **Queue Processing**: Use queues for non-streaming requests
3. **Caching**: Cache common responses
4. **Monitoring**: Track token usage and costs
5. **Security**: Validate and sanitize all inputs
6. **Scaling**: Use Redis for session storage

## Advanced Features

### Multi-User Chat Rooms

Extend the conversation model to support multiple users:

```php
Schema::create('conversation_user', function (Blueprint $table) {
    $table->foreignId('conversation_id');
    $table->foreignId('user_id');
    $table->timestamps();
});
```

### Context-Aware Responses

Include user profile data:

```php
$systemPrompt = "You are assisting {$user->name}. "
    . "Their preferences: " . json_encode($user->preferences);
```

### File Attachments

Add file upload support (Chapter 13 - Vision):

```php
Schema::table('messages', function (Blueprint $table) {
    $table->json('attachments')->nullable();
});
```

## Next Steps

- Add file upload support (Chapter 13)
- Implement RAG for knowledge base (Chapter 31)
- Add admin panel (Chapter 25)
- Implement analytics dashboard
- Add multi-language support

## Resources

- [Livewire Documentation](https://livewire.laravel.com/)
- [Laravel Eloquent](https://laravel.com/docs/eloquent)
- [Claude Streaming API](https://docs.anthropic.com/claude/reference/streaming)
