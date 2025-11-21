<?php
# filename: src/MultiAgent/AgentOrchestrator.php
declare(strict_types=1);

namespace App\MultiAgent;

use ClaudePhp\ClaudePhp;

class AgentOrchestrator
{
    private MessageBroker $messageBroker;
    private array $agents = [];

    public function __construct(
        private ClaudePhp $claude
    ) {
        $this->messageBroker = new MessageBroker();
    }

    /**
     * Create and register a supervisor agent
     */
    public function createSupervisor(string $name = 'Supervisor'): Agents\SupervisorAgent
    {
        $supervisor = new Agents\SupervisorAgent(
            claude: $this->claude,
            agentId: 'supervisor',
            name: $name,
            role: 'Task Coordinator',
            messageBroker: $this->messageBroker
        );

        $this->agents['supervisor'] = $supervisor;

        return $supervisor;
    }

    /**
     * Create specialized worker agents
     */
    public function createWorkerTeam(): array
    {
        $workers = [];

        // Research Agent
        $workers['researcher'] = new Agents\ResearchAgent(
            claude: $this->claude,
            agentId: 'researcher',
            name: 'Research Specialist',
            role: 'Information Gathering',
            messageBroker: $this->messageBroker
        );

        // Code Agent
        $workers['coder'] = new Agents\CodeAgent(
            claude: $this->claude,
            agentId: 'coder',
            name: 'Code Specialist',
            role: 'Software Development',
            messageBroker: $this->messageBroker
        );

        // Writer Agent
        $workers['writer'] = new Agents\WriterAgent(
            claude: $this->claude,
            agentId: 'writer',
            name: 'Content Specialist',
            role: 'Content Creation',
            messageBroker: $this->messageBroker
        );

        $this->agents = array_merge($this->agents, $workers);

        return $workers;
    }

    /**
     * Execute a task with the multi-agent system
     */
    public function executeTask(string $taskDescription, string $priority = 'medium'): TaskResult
    {
        $task = new Task(
            id: uniqid('task_'),
            type: 'complex',
            description: $taskDescription,
            assignedTo: 'supervisor',
            createdBy: 'user',
            priority: $priority
        );

        $supervisor = $this->agents['supervisor'] ?? throw new \RuntimeException('No supervisor agent');

        return $supervisor->processTask($task);
    }

    /**
     * Get agent by ID
     */
    public function getAgent(string $agentId): ?Agent
    {
        return $this->agents[$agentId] ?? null;
    }

    /**
     * Get all agents
     */
    public function getAgents(): array
    {
        return $this->agents;
    }

    /**
     * Get message broker
     */
    public function getMessageBroker(): MessageBroker
    {
        return $this->messageBroker;
    }
}
