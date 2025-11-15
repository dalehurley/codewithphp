<?php
declare(strict_types=1);

/**
 * WebSocket Server with Ratchet
 *
 * Real-time WebSocket server for bidirectional communication.
 * Similar to ws library in Node.js.
 */

require 'vendor/autoload.php';

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ChatServer implements MessageComponentInterface {
    protected $clients;
    protected int $clientId = 0;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        echo "💬 Chat Server initialized\n";
    }

    public function onOpen(ConnectionInterface $conn): void {
        $this->clientId++;
        $conn->clientId = $this->clientId;
        $this->clients->attach($conn);

        echo "[{$conn->resourceId}] Client #{$conn->clientId} connected\n";
        echo "   Total clients: " . count($this->clients) . "\n";

        // Send welcome message
        $conn->send(json_encode([
            'type' => 'welcome',
            'message' => "Welcome! You are client #{$conn->clientId}",
            'totalClients' => count($this->clients)
        ]));

        // Notify others
        $this->broadcast(json_encode([
            'type' => 'user_joined',
            'message' => "Client #{$conn->clientId} joined",
            'totalClients' => count($this->clients)
        ]), $conn);
    }

    public function onMessage(ConnectionInterface $from, $msg): void {
        echo "[{$from->resourceId}] Client #{$from->clientId}: {$msg}\n";

        try {
            $data = json_decode($msg, true);

            // Handle different message types
            if (isset($data['type'])) {
                switch ($data['type']) {
                    case 'chat':
                        $this->handleChatMessage($from, $data['message'] ?? '');
                        break;
                    case 'ping':
                        $from->send(json_encode(['type' => 'pong', 'timestamp' => time()]));
                        break;
                    default:
                        $from->send(json_encode(['type' => 'error', 'message' => 'Unknown message type']));
                }
            } else {
                // Plain text message
                $this->handleChatMessage($from, $msg);
            }
        } catch (\Exception $e) {
            echo "   Error processing message: {$e->getMessage()}\n";
            $from->send(json_encode(['type' => 'error', 'message' => 'Invalid message format']));
        }
    }

    public function onClose(ConnectionInterface $conn): void {
        $this->clients->detach($conn);

        echo "[{$conn->resourceId}] Client #{$conn->clientId} disconnected\n";
        echo "   Total clients: " . count($this->clients) . "\n";

        // Notify others
        $this->broadcast(json_encode([
            'type' => 'user_left',
            'message' => "Client #{$conn->clientId} left",
            'totalClients' => count($this->clients)
        ]));
    }

    public function onError(ConnectionInterface $conn, \Exception $e): void {
        echo "[{$conn->resourceId}] Error: {$e->getMessage()}\n";
        $conn->close();
    }

    private function handleChatMessage(ConnectionInterface $from, string $message): void {
        if (empty(trim($message))) {
            return;
        }

        $response = json_encode([
            'type' => 'chat',
            'clientId' => $from->clientId,
            'message' => $message,
            'timestamp' => date('H:i:s')
        ]);

        // Broadcast to all clients
        $this->broadcast($response);
    }

    private function broadcast(string $message, ?ConnectionInterface $except = null): void {
        foreach ($this->clients as $client) {
            if ($except === null || $client !== $except) {
                $client->send($message);
            }
        }
    }
}

// Start the WebSocket server
echo "=== WebSocket Chat Server ===\n\n";
echo "Starting server on ws://localhost:8080\n\n";
echo "Test with JavaScript:\n";
echo "  const ws = new WebSocket('ws://localhost:8080');\n";
echo "  ws.onmessage = (e) => console.log(JSON.parse(e.data));\n";
echo "  ws.send(JSON.stringify({type: 'chat', message: 'Hello!'}));\n\n";
echo "Press Ctrl+C to stop\n\n";

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new ChatServer()
        )
    ),
    8080
);

$server->run();
