<?php

declare(strict_types=1);

namespace ClaudePhp\RealtimeChat;

use Predis\ClaudePhp as RedisClient;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Broadcasting Service for real-time events
 *
 * Uses Redis pub/sub for distributing messages across multiple server instances
 */
class BroadcastService
{
    private const CHANNEL_PREFIX = 'claude_chat:';

    public function __construct(
        private readonly RedisClient $redis,
        private readonly LoggerInterface $logger = new NullLogger()
    ) {}

    /**
     * Broadcast an event to all subscribers
     */
    public function broadcast(string $event, array $data, ?string $channel = 'global'): void
    {
        $channelName = self::CHANNEL_PREFIX . $channel;

        $payload = json_encode([
            'event' => $event,
            'data' => $data,
            'timestamp' => microtime(true),
        ]);

        try {
            $this->redis->publish($channelName, $payload);

            $this->logger->debug('Broadcasted event', [
                'event' => $event,
                'channel' => $channelName,
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Broadcast failed', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Subscribe to a channel
     */
    public function subscribe(string $channel, callable $callback): void
    {
        $channelName = self::CHANNEL_PREFIX . $channel;

        $pubsub = $this->redis->pubSubLoop();
        $pubsub->subscribe($channelName);

        $this->logger->info('Subscribed to channel', ['channel' => $channelName]);

        foreach ($pubsub as $message) {
            if ($message->kind === 'message') {
                try {
                    $payload = json_decode($message->payload, true);

                    if ($payload) {
                        $callback($payload['event'], $payload['data'], $payload['timestamp']);
                    }
                } catch (\Exception $e) {
                    $this->logger->error('Message processing failed', [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Publish a message to a specific user
     */
    public function publishToUser(string $userId, string $event, array $data): void
    {
        $this->broadcast($event, $data, "user:{$userId}");
    }

    /**
     * Publish a message to a specific room
     */
    public function publishToRoom(string $roomId, string $event, array $data): void
    {
        $this->broadcast($event, $data, "room:{$roomId}");
    }
}
