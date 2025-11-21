<?php

declare(strict_types=1);

namespace App;

class LoadBalancer
{
    private array $nodes = [];
    private int $currentIndex = 0;
    
    public function __construct(array $nodes)
    {
        $this->nodes = $nodes;
    }
    
    public function getNext(): string
    {
        $node = $this->nodes[$this->currentIndex];
        $this->currentIndex = ($this->currentIndex + 1) % count($this->nodes);
        return $node;
    }
    
    public function execute(callable $request)
    {
        $node = $this->getNext();
        return $request($node);
    }
    
    public function healthCheck(): array
    {
        return array_map(fn($node) => ['node' => $node, 'status' => 'healthy'], $this->nodes);
    }
}
