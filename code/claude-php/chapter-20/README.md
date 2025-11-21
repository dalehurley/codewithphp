# Chapter 20: Real-time Chat with WebSockets

Complete implementation of a real-time chat application using WebSockets, Claude AI, and Vue.js.

## Features

- **Real-time bidirectional communication** via WebSockets
- **Streaming responses** from Claude AI
- **Vue.js chat interface** with typing indicators
- **Redis broadcasting** for multi-server deployments
- **Conversation history** management
- **Reconnection handling** and error recovery

## Installation

```bash
composer install
cp .env.example .env
# Edit .env and add your ANTHROPIC_API_KEY
```

## Running the Server

Start the WebSocket server:

```bash
php examples/websocket-server.php
```

Or use the composer script:

```bash
composer websocket
```

The server will start on `ws://localhost:8080` by default.

## Using the Chat ClaudePhp

Open `examples/chat-client.html` in your browser. The Vue.js application will:

1. Connect to the WebSocket server
2. Authenticate with a generated user ID
3. Allow real-time messaging with Claude
4. Display streaming responses as they arrive
5. Show typing indicators during generation

## Architecture

### ChatService

Manages conversation with Claude AI:
- Maintains conversation history per user
- Supports streaming responses
- Handles token management
- Exports conversation history

### WebSocketChatServer

Handles WebSocket connections:
- ClaudePhp connection management
- Message routing and authentication
- Integration with ChatService
- Error handling and logging

### BroadcastService

Redis-based broadcasting:
- Pub/sub messaging
- Multi-server coordination
- User and room-specific broadcasts

## Message Types

### ClaudePhp to Server

```javascript
// Authentication
{
    type: 'auth',
    user_id: 'user_123',
    username: 'John Doe'
}

// Send message
{
    type: 'message',
    message: 'Hello Claude!',
    system_prompt: 'You are a helpful assistant'
}

// Typing indicator
{
    type: 'typing',
    is_typing: true
}

// Clear history
{
    type: 'clear'
}
```

### Server to ClaudePhp

```javascript
// System message
{
    type: 'system',
    message: 'Connected to server',
    timestamp: 1234567890
}

// Authentication success
{
    type: 'auth_success',
    user_id: 'user_123',
    username: 'John Doe'
}

// Assistant typing
{
    type: 'assistant_typing',
    is_typing: true
}

// Streaming chunk
{
    type: 'assistant_chunk',
    chunk: 'Hello, '
}

// Message complete
{
    type: 'assistant_complete',
    timestamp: 1234567890
}

// Error
{
    type: 'error',
    error: 'Error message',
    timestamp: 1234567890
}
```

## Configuration

### Environment Variables

```bash
# Anthropic API
ANTHROPIC_API_KEY=sk-ant-your-key-here
ANTHROPIC_MODEL=claude-sonnet-4-20250514
ANTHROPIC_MAX_TOKENS=4096

# WebSocket Server
WEBSOCKET_HOST=0.0.0.0
WEBSOCKET_PORT=8080

# Redis (optional, for broadcasting)
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Application
MAX_CONVERSATION_HISTORY=50
```

## Usage Examples

### Basic Chat

```php
use ClaudePhp\RealtimeChat\ChatService;

$chat = new ChatService(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

// Synchronous message
$response = $chat->sendMessageSync(
    message: 'Tell me a joke',
    userId: 'user_123'
);

echo $response['message'];
```

### Streaming Chat

```php
$chat->sendMessage(
    message: 'Explain quantum computing',
    userId: 'user_123',
    onChunk: function(string $chunk) {
        echo $chunk;
        flush();
    }
);
```

### With Broadcasting

```php
use ClaudePhp\RealtimeChat\BroadcastService;
use Predis\ClaudePhp;

$redis = new ClaudePhp(['host' => '127.0.0.1']);
$broadcaster = new BroadcastService($redis);

// Broadcast to all users
$broadcaster->broadcast('notification', [
    'message' => 'System maintenance in 5 minutes'
]);

// Broadcast to specific user
$broadcaster->publishToUser('user_123', 'alert', [
    'message' => 'You have a new message'
]);
```

## Testing

Run the test suite:

```bash
composer test
```

## Production Deployment

### Nginx Configuration

```nginx
upstream websocket {
    server 127.0.0.1:8080;
}

server {
    listen 80;
    server_name chat.example.com;

    location /ws {
        proxy_pass http://websocket;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

### Supervisor Configuration

```ini
[program:claude-websocket]
command=php /path/to/examples/websocket-server.php
directory=/path/to/project
autostart=true
autorestart=true
user=www-data
stdout_logfile=/var/log/claude-websocket.log
stderr_logfile=/var/log/claude-websocket-error.log
```

### Multi-Server Deployment

Enable Redis broadcasting in `.env`:

```bash
REDIS_HOST=redis.example.com
REDIS_PORT=6379
```

All servers will share messages through Redis pub/sub.

## Security Considerations

1. **Authentication**: Implement proper user authentication
2. **Rate Limiting**: Limit messages per user/IP
3. **Input Validation**: Sanitize all user inputs
4. **SSL/TLS**: Use WSS in production
5. **Token Management**: Secure API key storage

## Troubleshooting

### Connection Refused

Check that the WebSocket server is running:
```bash
netstat -an | grep 8080
```

### Redis Connection Failed

Verify Redis is running:
```bash
redis-cli ping
```

### High Memory Usage

Reduce `MAX_CONVERSATION_HISTORY` in `.env`

## Next Steps

- Add user authentication (Chapter 21)
- Implement message persistence
- Add file upload support (Chapter 13)
- Create multi-user chat rooms
- Add message reactions and threading

## Resources

- [Ratchet Documentation](http://socketo.me/)
- [Claude API Streaming](https://docs.anthropic.com/claude/reference/streaming)
- [Vue.js Guide](https://vuejs.org/guide/)
- [Redis Pub/Sub](https://redis.io/topics/pubsub)
