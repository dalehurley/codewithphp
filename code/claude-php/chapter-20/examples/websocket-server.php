#!/usr/bin/env php
<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\RealtimeChat\ChatService;
use ClaudePhp\RealtimeChat\WebSocketChatServer;
use ClaudePhp\RealtimeChat\BroadcastService;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Dotenv\Dotenv;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Predis\ClaudePhp as RedisClient;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Setup logger
$logger = new Logger('websocket');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));

// Configuration
$host = $_ENV['WEBSOCKET_HOST'] ?? '0.0.0.0';
$port = (int)($_ENV['WEBSOCKET_PORT'] ?? 8080);
$apiKey = $_ENV['ANTHROPIC_API_KEY'] ?? throw new RuntimeException('ANTHROPIC_API_KEY not set');

$logger->info('Starting Claude Chat WebSocket Server', [
    'host' => $host,
    'port' => $port,
]);

try {
    // Initialize Redis for broadcasting (optional)
    $broadcaster = null;
    if (!empty($_ENV['REDIS_HOST'])) {
        $redis = new RedisClient([
            'host' => $_ENV['REDIS_HOST'],
            'port' => (int)($_ENV['REDIS_PORT'] ?? 6379),
            'password' => $_ENV['REDIS_PASSWORD'] ?? null,
            'database' => (int)($_ENV['REDIS_DATABASE'] ?? 0),
        ]);
        $broadcaster = new BroadcastService($redis, $logger);
        $logger->info('Redis broadcasting enabled');
    }

    // Initialize Claude chat service
    $chatService = new ChatService(
        apiKey: $apiKey,
        model: $_ENV['ANTHROPIC_MODEL'] ?? 'claude-sonnet-4-5',
        maxTokens: (int)($_ENV['ANTHROPIC_MAX_TOKENS'] ?? 4096),
        logger: $logger,
        maxHistoryLength: (int)($_ENV['MAX_CONVERSATION_HISTORY'] ?? 50)
    );

    // Create WebSocket server
    $chatServer = new WebSocketChatServer($chatService, $broadcaster, $logger);

    $server = IoServer::factory(
        new HttpServer(
            new WsServer($chatServer)
        ),
        $port,
        $host
    );

    $logger->info('WebSocket server started successfully', [
        'url' => "ws://{$host}:{$port}",
        'press' => 'Ctrl+C to stop',
    ]);

    $server->run();

} catch (\Exception $e) {
    $logger->error('Server error', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    exit(1);
}
