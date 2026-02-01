<?php

declare(strict_types=1);

namespace App;

use ClaudePhp\ClaudePhp;

class AgentFramework
{
    private array $agents = [];
    
    public function __construct(
        private ClaudePhp $client
    ) {}
    
    public function registerAgent(string $name, Agent $agent): void
    {
        $this->agents[$name] = $agent;
    }
    
    public function executeTask(string $task, ?string $agentName = null): array
    {
        if ($agentName && isset($this->agents[$agentName])) {
            return $this->agents[$agentName]->execute($task);
        }
        
        // Route to best agent
        $agent = $this->selectBestAgent($task);
        return $agent->execute($task);
    }
    
    private function selectBestAgent(string $task): Agent
    {
        // Simplified agent selection
        return reset($this->agents);
    }
}

abstract class Agent
{
    public function __construct(
        protected ClaudePhp $client,
        protected string $role
    ) {}
    
    abstract public function execute(string $task): array;
}
