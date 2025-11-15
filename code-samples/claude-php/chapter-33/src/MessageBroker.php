<?php

declare(strict_types=1);

namespace App;

class MessageBroker
{
    private array $queue = [];
    
    public function publish(string $channel, array $message): void
    {
        $this->queue[$channel][] = array_merge($message, ['timestamp' => time()]);
    }
    
    public function subscribe(string $channel): array
    {
        return $this->queue[$channel] ?? [];
    }
    
    public function clear(string $channel): void
    {
        unset($this->queue[$channel]);
    }
}
