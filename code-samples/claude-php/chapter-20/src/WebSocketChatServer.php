<?php

declare(strict_types=1);

namespace ClaudePhp\RealtimeChat;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * WebSocket Chat Server with Claude AI integration
 *
 * Handles real-time bidirectional communication between clients and Claude
 */
class WebSocketChatServer implements MessageComponentInterface
{
    protected \SplObjectStorage $clients;
    protected array $userConnections = [];

    public function __construct(
        private readonly ChatService $chatService,
        private readonly ?BroadcastService $broadcaster = null,
        private readonly LoggerInterface $logger = new NullLogger()
    ) {
        $this->clients = new \SplObjectStorage();
    }

    /**
     * Handle new WebSocket connection
     */
    public function onOpen(ConnectionInterface $conn): void
    {
        $this->clients->attach($conn);

        $this->logger->info('New connection', [
            'resource_id' => $conn->resourceId,
            'total_clients' => count($this->clients),
        ]);

        // Send welcome message
        $this->sendToClient($conn, [
            'type' => 'system',
            'message' => 'Connected to Claude Chat Server',
            'timestamp' => time(),
        ]);
    }

    /**
     * Handle incoming WebSocket message
     */
    public function onMessage(ConnectionInterface $from, $msg): void
    {
        try {
            $data = json_decode($msg, true);

            if (!$data || !isset($data['type'])) {
                throw new \InvalidArgumentException('Invalid message format');
            }

            $this->logger->debug('Received message', [
                'type' => $data['type'],
                'from' => $from->resourceId,
            ]);

            match($data['type']) {
                'auth' => $this->handleAuth($from, $data),
                'message' => $this->handleMessage($from, $data),
                'typing' => $this->handleTyping($from, $data),
                'clear' => $this->handleClear($from, $data),
                default => $this->sendError($from, 'Unknown message type'),
            };

        } catch (\Exception $e) {
            $this->logger->error('Message handling error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->sendError($from, $e->getMessage());
        }
    }

    /**
     * Handle client authentication
     */
    private function handleAuth(ConnectionInterface $conn, array $data): void
    {
        $userId = $data['user_id'] ?? uniqid('user_');
        $username = $data['username'] ?? 'Anonymous';

        $this->userConnections[$conn->resourceId] = [
            'user_id' => $userId,
            'username' => $username,
            'connected_at' => time(),
        ];

        $this->sendToClient($conn, [
            'type' => 'auth_success',
            'user_id' => $userId,
            'username' => $username,
        ]);

        $this->logger->info('User authenticated', [
            'user_id' => $userId,
            'username' => $username,
        ]);
    }

    /**
     * Handle chat message
     */
    private function handleMessage(ConnectionInterface $from, array $data): void
    {
        $userInfo = $this->userConnections[$from->resourceId] ?? null;

        if (!$userInfo) {
            $this->sendError($from, 'Not authenticated');
            return;
        }

        $message = $data['message'] ?? '';
        $systemPrompt = $data['system_prompt'] ?? null;

        if (empty($message)) {
            $this->sendError($from, 'Empty message');
            return;
        }

        // Echo user message
        $this->sendToClient($from, [
            'type' => 'user_message',
            'message' => $message,
            'timestamp' => time(),
        ]);

        // Send typing indicator
        $this->sendToClient($from, [
            'type' => 'assistant_typing',
            'is_typing' => true,
        ]);

        // Send message to Claude with streaming
        try {
            $this->chatService->sendMessage(
                message: $message,
                userId: $userInfo['user_id'],
                systemPrompt: $systemPrompt,
                onChunk: function(string $chunk) use ($from) {
                    $this->sendToClient($from, [
                        'type' => 'assistant_chunk',
                        'chunk' => $chunk,
                    ]);
                }
            );

            // Send completion signal
            $this->sendToClient($from, [
                'type' => 'assistant_complete',
                'timestamp' => time(),
            ]);

        } catch (\Exception $e) {
            $this->sendError($from, 'Claude API error: ' . $e->getMessage());
        }

        // Stop typing indicator
        $this->sendToClient($from, [
            'type' => 'assistant_typing',
            'is_typing' => false,
        ]);
    }

    /**
     * Handle typing indicator
     */
    private function handleTyping(ConnectionInterface $from, array $data): void
    {
        $userInfo = $this->userConnections[$from->resourceId] ?? null;

        if (!$userInfo) {
            return;
        }

        // Broadcast typing status to other clients (if implementing multi-user chat)
        if ($this->broadcaster) {
            $this->broadcaster->broadcast('typing', [
                'user_id' => $userInfo['user_id'],
                'username' => $userInfo['username'],
                'is_typing' => $data['is_typing'] ?? false,
            ]);
        }
    }

    /**
     * Handle clear history request
     */
    private function handleClear(ConnectionInterface $from, array $data): void
    {
        $userInfo = $this->userConnections[$from->resourceId] ?? null;

        if (!$userInfo) {
            $this->sendError($from, 'Not authenticated');
            return;
        }

        $this->chatService->clearHistory($userInfo['user_id']);

        $this->sendToClient($from, [
            'type' => 'history_cleared',
            'timestamp' => time(),
        ]);
    }

    /**
     * Handle connection close
     */
    public function onClose(ConnectionInterface $conn): void
    {
        $this->clients->detach($conn);

        $userInfo = $this->userConnections[$conn->resourceId] ?? null;
        unset($this->userConnections[$conn->resourceId]);

        $this->logger->info('Connection closed', [
            'resource_id' => $conn->resourceId,
            'user_id' => $userInfo['user_id'] ?? 'unknown',
            'total_clients' => count($this->clients),
        ]);
    }

    /**
     * Handle connection error
     */
    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        $this->logger->error('Connection error', [
            'resource_id' => $conn->resourceId,
            'error' => $e->getMessage(),
        ]);

        $conn->close();
    }

    /**
     * Send message to specific client
     */
    private function sendToClient(ConnectionInterface $conn, array $data): void
    {
        $conn->send(json_encode($data));
    }

    /**
     * Send error to client
     */
    private function sendError(ConnectionInterface $conn, string $error): void
    {
        $this->sendToClient($conn, [
            'type' => 'error',
            'error' => $error,
            'timestamp' => time(),
        ]);
    }

    /**
     * Broadcast message to all clients
     */
    private function broadcast(array $data, ?ConnectionInterface $exclude = null): void
    {
        $message = json_encode($data);

        foreach ($this->clients as $client) {
            if ($exclude && $client === $exclude) {
                continue;
            }

            $client->send($message);
        }
    }
}
