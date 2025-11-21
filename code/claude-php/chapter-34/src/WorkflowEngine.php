<?php

declare(strict_types=1);

namespace App;

use ClaudePhp\ClaudePhp;

class WorkflowEngine
{
    private array $steps = [];
    
    public function __construct(
        private Anthropic $client
    ) {}
    
    public function addStep(string $name, callable $action): void
    {
        $this->steps[] = ['name' => $name, 'action' => $action];
    }
    
    public function execute(array $context = []): array
    {
        $results = [];
        foreach ($this->steps as $step) {
            $results[$step['name']] = call_user_func($step['action'], $context);
            $context = array_merge($context, $results[$step['name']]);
        }
        return $results;
    }
}
