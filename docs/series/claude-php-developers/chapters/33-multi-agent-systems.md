---
title: "33: Multi-Agent Systems"
description: "Build sophisticated multi-agent systems where specialized AI agents collaborate, delegate tasks, and coordinate complex workflows. Master agent orchestration, message passing, and collaborative problem-solving."
series: "claude-php-developers"
chapter: 33
order: 33
difficulty: "Expert"
prerequisites:
  - "Completed Chapters 11-15 (Tool Use)"
  - "Understanding of async/parallel processing"
  - "Knowledge of design patterns"
  - "Experience with complex system architecture"
---

![33: Multi-Agent Systems](/images/claude-php/chapter-33-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 33</span>
</div>

# Chapter 33: Multi-Agent Systems

## Overview

Multi-agent systems coordinate multiple specialized AI agents to solve complex problems that exceed the capabilities of a single agent. Each agent has specific expertise, and they collaborate through structured communication, task delegation, and consensus building.

This chapter teaches you to build production-ready multi-agent systems with intelligent orchestration, robust message passing, error recovery, and collaborative workflows. You'll learn to design agent hierarchies, implement communication protocols, and optimize multi-agent coordination.

**What You'll Build**: A complete multi-agent framework with supervisor-worker patterns, peer-to-peer collaboration, task routing, message queuing, and workflow orchestration.

## What You'll Build

By the end of this chapter, you will have created:

- A complete multi-agent framework with base `Agent` class
- Supervisor agent for task coordination and delegation
- Specialized worker agents (Research, Code, Writer)
- Message broker for inter-agent communication
- Agent orchestrator for system management
- Complete working example demonstrating multi-agent collaboration

## Objectives

- Understand multi-agent system architecture and design patterns
- Build a flexible agent framework with specialization and delegation
- Implement reliable message passing between agents
- Create supervisor-worker coordination patterns
- Master task decomposition and result synthesis
- Design robust error handling and retry mechanisms
- Optimize multi-agent workflows for production use

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapters 11-15** (Tool use fundamentals)
- ✓ **Async processing knowledge** for parallel execution
- ✓ **Design pattern familiarity** for architecture
- ✓ **Complex system experience** for orchestration

**Estimated Time**: 120-150 minutes

## Agent Framework

The foundation of our multi-agent system is the abstract `Agent` class. This base class provides common functionality for all agents, including Claude API integration, message passing, task delegation, and conversation history management.

Each agent maintains its own conversation history, allowing it to build context over multiple interactions. Agents can communicate with each other through the message broker, delegate tasks to specialized agents, and use Claude's tool use capabilities for dynamic task routing.

**Key Features:**
- Abstract base class enforcing specialization
- Built-in Claude API integration with tool support
- Message passing and broadcasting capabilities
- Task delegation with result waiting
- Conversation history per agent
- Capability registration system

```php
<?php
# filename: src/MultiAgent/Agent.php
declare(strict_types=1);

namespace App\MultiAgent;

use ClaudePhp\ClaudePhp;

abstract class Agent
{
    protected array $conversationHistory = [];
    protected array $capabilities = [];

    public function __construct(
        protected Anthropic $claude,
        protected string $agentId,
        protected string $name,
        protected string $role,
        protected MessageBroker $messageBroker
    ) {
        $this->registerCapabilities();
        $this->messageBroker->registerAgent($this);
    }

    /**
     * Process a task assigned to this agent
     */
    abstract public function processTask(Task $task): TaskResult;

    /**
     * Define agent capabilities
     */
    abstract protected function registerCapabilities(): void;

    /**
     * Get agent's system prompt
     */
    abstract protected function getSystemPrompt(): string;

    /**
     * Execute with Claude
     */
    protected function execute(string $prompt, array $options = []): object
    {
        $messages = $this->buildMessages($prompt);

        $response = $this->claude->messages()->create([
            'model' => $options['model'] ?? 'claude-sonnet-4-20250514',
            'max_tokens' => $options['max_tokens'] ?? 4096,
            'temperature' => $options['temperature'] ?? 0.7,
            'system' => $this->getSystemPrompt(),
            'messages' => $messages,
            'tools' => $this->getTools()
        ]);

        // Handle tool calls if present
        if ($response->stop_reason === 'tool_use') {
            $response = $this->handleToolCalls($response, $messages);
        }

        // Update conversation history
        $this->conversationHistory[] = [
            'role' => 'user',
            'content' => $prompt
        ];
        $this->conversationHistory[] = [
            'role' => 'assistant',
            'content' => $response->content[0]['text']
        ];

        return $response;
    }

    /**
     * Send message to another agent
     */
    protected function sendMessage(string $targetAgentId, Message $message): void
    {
        $this->messageBroker->send($targetAgentId, $message);
    }

    /**
     * Broadcast message to all agents
     */
    protected function broadcast(Message $message): void
    {
        $this->messageBroker->broadcast($this->agentId, $message);
    }

    /**
     * Request help from another agent
     */
    protected function delegateTask(string $targetAgentId, Task $task): TaskResult
    {
        $message = Message::create(
            from: $this->agentId,
            to: $targetAgentId,
            type: 'task_delegation',
            content: $task->toArray()
        );

        $this->sendMessage($targetAgentId, $message);

        // Wait for response (or use async patterns)
        return $this->waitForTaskResult($task->id);
    }

    /**
     * Get available tools for this agent
     */
    protected function getTools(): array
    {
        return [
            [
                'name' => 'delegate_task',
                'description' => 'Delegate a task to another specialized agent',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'agent_id' => [
                            'type' => 'string',
                            'description' => 'ID of the agent to delegate to'
                        ],
                        'task_description' => [
                            'type' => 'string',
                            'description' => 'Description of the task to delegate'
                        ]
                    ],
                    'required' => ['agent_id', 'task_description']
                ]
            ],
            [
                'name' => 'request_information',
                'description' => 'Request information from another agent',
                'input_schema' => [
                    'type' => 'object',
                    'properties' => [
                        'agent_id' => [
                            'type' => 'string',
                            'description' => 'ID of the agent to query'
                        ],
                        'query' => [
                            'type' => 'string',
                            'description' => 'Information to request'
                        ]
                    ],
                    'required' => ['agent_id', 'query']
                ]
            ]
        ];
    }

    protected function handleToolCalls(object $response, array $messages): object
    {
        $toolResults = [];

        foreach ($response->content as $block) {
            if ($block->type !== 'tool_use') {
                continue;
            }

            $result = match($block->name) {
                'delegate_task' => $this->handleDelegateTask($block->input),
                'request_information' => $this->handleRequestInformation($block->input),
                default => ['error' => 'Unknown tool']
            };

            // Collect tool results for the response
            $toolResults[] = [
                'type' => 'tool_result',
                'tool_use_id' => $block->id,
                'content' => json_encode($result)
            ];
        }

        // Add assistant's tool use request to messages
        $messages[] = [
            'role' => 'assistant',
            'content' => $response->content
        ];

        // Add tool results as user message
        if (!empty($toolResults)) {
            $messages[] = [
                'role' => 'user',
                'content' => $toolResults
            ];
        }

        // Get final response after tool execution
        return $this->claude->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4096,
            'system' => $this->getSystemPrompt(),
            'messages' => $messages
        ]);
    }

    protected function handleDelegateTask(object $input): array
    {
        $task = new Task(
            id: uniqid('task_'),
            type: 'delegated',
            description: $input->task_description,
            assignedTo: $input->agent_id,
            createdBy: $this->agentId
        );

        $result = $this->delegateTask($input->agent_id, $task);

        return [
            'task_id' => $task->id,
            'status' => $result->status,
            'output' => $result->output
        ];
    }

    protected function handleRequestInformation(object $input): array
    {
        $message = Message::create(
            from: $this->agentId,
            to: $input->agent_id,
            type: 'information_request',
            content: ['query' => $input->query]
        );

        $this->sendMessage($input->agent_id, $message);

        // Wait for response
        $response = $this->waitForMessage($input->agent_id, 'information_response');

        return $response->content;
    }

    protected function buildMessages(string $prompt): array
    {
        $messages = $this->conversationHistory;
        $messages[] = [
            'role' => 'user',
            'content' => $prompt
        ];

        return $messages;
    }

    protected function waitForTaskResult(string $taskId): TaskResult
    {
        // Simplified synchronous wait
        // In production, use async/await or message queue patterns
        return $this->messageBroker->waitForTaskResult($taskId, timeout: 30);
    }

    protected function waitForMessage(string $fromAgentId, string $messageType): Message
    {
        return $this->messageBroker->waitForMessage($this->agentId, $fromAgentId, $messageType, timeout: 10);
    }

    public function getAgentId(): string
    {
        return $this->agentId;
    }

    public function getCapabilities(): array
    {
        return $this->capabilities;
    }
}
```

## Supervisor Agent

The `SupervisorAgent` acts as the coordinator for the multi-agent system. It receives complex tasks, analyzes them, breaks them into subtasks, and delegates work to appropriate specialized agents. The supervisor uses Claude's reasoning capabilities to determine the best task decomposition strategy and agent assignment.

**Responsibilities:**
- Task analysis and decomposition
- Agent selection and task assignment
- Progress monitoring
- Result synthesis from multiple agents
- Error handling and retry coordination

The supervisor maintains a registry of available worker agents and their capabilities, allowing it to make informed delegation decisions. It uses Claude's tool use feature to dynamically delegate tasks based on the task requirements.

```php
<?php
# filename: src/MultiAgent/Agents/SupervisorAgent.php
declare(strict_types=1);

namespace App\MultiAgent\Agents;

use App\MultiAgent\Agent;
use App\MultiAgent\Task;
use App\MultiAgent\TaskResult;

class SupervisorAgent extends Agent
{
    private array $workerAgents = [];

    protected function registerCapabilities(): void
    {
        $this->capabilities = [
            'task_planning',
            'task_decomposition',
            'agent_coordination',
            'result_synthesis'
        ];
    }

    protected function getSystemPrompt(): string
    {
        $workerInfo = $this->getWorkerAgentsInfo();

        return <<<SYSTEM
You are a Supervisor Agent responsible for coordinating a team of specialized AI agents to solve complex tasks.

Your responsibilities:
1. Analyze incoming tasks and break them into subtasks
2. Assign subtasks to appropriate specialized agents
3. Monitor progress and handle failures
4. Synthesize results from multiple agents
5. Ensure task completion and quality

Available Worker Agents:
{$workerInfo}

Guidelines:
- Decompose complex tasks into logical subtasks
- Assign each subtask to the most qualified agent
- Coordinate dependencies between subtasks
- Synthesize results into coherent final output
- Handle errors and retry failed subtasks

Use the delegate_task tool to assign work to agents.
SYSTEM;
    }

    public function processTask(Task $task): TaskResult
    {
        // Let Claude decide how to decompose and delegate
        $prompt = <<<PROMPT
New Task Received:

ID: {$task->id}
Type: {$task->type}
Description: {$task->description}
Priority: {$task->priority}

Please:
1. Analyze this task
2. Break it into subtasks if needed
3. Delegate to appropriate agents
4. Wait for results
5. Synthesize the final answer

Provide the final result.
PROMPT;

        $response = $this->execute($prompt);

        return new TaskResult(
            taskId: $task->id,
            status: 'completed',
            output: $response->content[0]['text'],
            metadata: [
                'agent' => $this->agentId,
                'model' => $response->model
            ]
        );
    }

    public function registerWorkerAgent(Agent $agent): void
    {
        $this->workerAgents[$agent->getAgentId()] = [
            'id' => $agent->getAgentId(),
            'name' => $agent->name,
            'role' => $agent->role,
            'capabilities' => $agent->getCapabilities()
        ];
    }

    private function getWorkerAgentsInfo(): string
    {
        $info = [];

        foreach ($this->workerAgents as $worker) {
            $capabilities = implode(', ', $worker['capabilities']);
            $info[] = "- {$worker['id']}: {$worker['name']} ({$worker['role']}) - Capabilities: {$capabilities}";
        }

        return implode("\n", $info);
    }
}
```

## Specialized Worker Agents

Worker agents are specialized AI agents focused on specific domains. Each worker agent has distinct capabilities and uses optimized system prompts and temperature settings for their particular task type.

**Research Agent**: Optimized for factual information gathering with lower temperature (0.3) for accuracy
**Code Agent**: Focused on code generation with very low temperature (0.2) for precision
**Writer Agent**: Creative content generation with higher temperature (0.8) for variety

Each agent extends the base `Agent` class and implements its specific `processTask` method, defining how it handles assigned work. The specialized system prompts guide Claude's behavior to match each agent's role.

```php
<?php
# filename: src/MultiAgent/Agents/ResearchAgent.php
declare(strict_types=1);

namespace App\MultiAgent\Agents;

use App\MultiAgent\Agent;
use App\MultiAgent\Task;
use App\MultiAgent\TaskResult;

class ResearchAgent extends Agent
{
    protected function registerCapabilities(): void
    {
        $this->capabilities = [
            'information_gathering',
            'web_search',
            'data_analysis',
            'fact_verification'
        ];
    }

    protected function getSystemPrompt(): string
    {
        return <<<SYSTEM
You are a Research Agent specialized in gathering and analyzing information.

Your expertise:
- Conducting thorough research on topics
- Finding and verifying facts
- Analyzing data and identifying patterns
- Synthesizing information from multiple sources
- Providing well-sourced answers

Guidelines:
- Be thorough and accurate
- Cite sources when available
- Distinguish facts from opinions
- Identify knowledge gaps
- Provide context and background
SYSTEM;
    }

    public function processTask(Task $task): TaskResult
    {
        $prompt = <<<PROMPT
Research Task:

{$task->description}

Please conduct thorough research and provide:
1. Key findings
2. Supporting evidence
3. Sources (if applicable)
4. Confidence level in findings

Research:
PROMPT;

        $response = $this->execute($prompt, [
            'temperature' => 0.3 // Lower temperature for factual research
        ]);

        return new TaskResult(
            taskId: $task->id,
            status: 'completed',
            output: $response->content[0]['text'],
            metadata: [
                'agent' => $this->agentId,
                'agent_type' => 'research'
            ]
        );
    }
}
```

```php
<?php
# filename: src/MultiAgent/Agents/CodeAgent.php
declare(strict_types=1);

namespace App\MultiAgent\Agents;

use App\MultiAgent\Agent;
use App\MultiAgent\Task;
use App\MultiAgent\TaskResult;

class CodeAgent extends Agent
{
    protected function registerCapabilities(): void
    {
        $this->capabilities = [
            'code_generation',
            'code_review',
            'debugging',
            'refactoring',
            'testing'
        ];
    }

    protected function getSystemPrompt(): string
    {
        return <<<SYSTEM
You are a Code Agent specialized in software development tasks.

Your expertise:
- Writing clean, efficient code
- Following best practices and design patterns
- Code review and optimization
- Debugging and error fixing
- Test generation
- Documentation

Guidelines:
- Write production-quality code
- Follow PSR standards for PHP
- Include error handling
- Add inline comments for complex logic
- Consider security and performance
- Provide complete, runnable code
SYSTEM;
    }

    public function processTask(Task $task): TaskResult
    {
        $prompt = <<<PROMPT
Coding Task:

{$task->description}

Please provide:
1. Complete, working code
2. Explanation of approach
3. Any assumptions made
4. Usage examples

Code:
PROMPT;

        $response = $this->execute($prompt, [
            'temperature' => 0.2 // Lower temperature for precise code
        ]);

        return new TaskResult(
            taskId: $task->id,
            status: 'completed',
            output: $response->content[0]['text'],
            metadata: [
                'agent' => $this->agentId,
                'agent_type' => 'code'
            ]
        );
    }
}
```

```php
<?php
# filename: src/MultiAgent/Agents/WriterAgent.php
declare(strict_types=1);

namespace App\MultiAgent\Agents;

use App\MultiAgent\Agent;
use App\MultiAgent\Task;
use App\MultiAgent\TaskResult;

class WriterAgent extends Agent
{
    protected function registerCapabilities(): void
    {
        $this->capabilities = [
            'content_writing',
            'editing',
            'summarization',
            'translation',
            'tone_adjustment'
        ];
    }

    protected function getSystemPrompt(): string
    {
        return <<<SYSTEM
You are a Writer Agent specialized in content creation and editing.

Your expertise:
- Writing clear, engaging content
- Editing and proofreading
- Adapting tone and style
- Summarizing complex information
- Creating documentation

Guidelines:
- Write clearly and concisely
- Match requested tone and style
- Ensure grammatical correctness
- Structure content logically
- Use appropriate formatting
SYSTEM;
    }

    public function processTask(Task $task): TaskResult
    {
        $prompt = <<<PROMPT
Writing Task:

{$task->description}

Please create content that:
1. Meets the specified requirements
2. Uses appropriate tone and style
3. Is well-structured and formatted
4. Is grammatically correct

Content:
PROMPT;

        $response = $this->execute($prompt, [
            'temperature' => 0.8 // Higher temperature for creative writing
        ]);

        return new TaskResult(
            taskId: $task->id,
            status: 'completed',
            output: $response->content[0]['text'],
            metadata: [
                'agent' => $this->agentId,
                'agent_type' => 'writer'
            ]
        );
    }
}
```

## Message Broker

The `MessageBroker` is the communication hub for all agents. It manages message queues, handles task delegation, stores task results, and provides blocking wait operations for synchronous coordination.

**Features:**
- Per-agent message queues
- Point-to-point messaging
- Broadcast messaging to all agents
- Task result storage and retrieval
- Timeout-based waiting mechanisms
- Automatic task processing on delegation

The broker processes task delegations automatically: when a task delegation message arrives, it extracts the task, assigns it to the target agent, processes it, stores the result, and sends a completion message back to the delegating agent.

```php
<?php
# filename: src/MultiAgent/MessageBroker.php
declare(strict_types=1);

namespace App\MultiAgent;

class MessageBroker
{
    private array $agents = [];
    private array $messageQueues = [];
    private array $taskResults = [];

    /**
     * Register an agent with the broker
     */
    public function registerAgent(Agent $agent): void
    {
        $agentId = $agent->getAgentId();
        $this->agents[$agentId] = $agent;
        $this->messageQueues[$agentId] = [];
    }

    /**
     * Send message to specific agent
     */
    public function send(string $targetAgentId, Message $message): void
    {
        if (!isset($this->messageQueues[$targetAgentId])) {
            throw new \RuntimeException("Agent {$targetAgentId} not registered");
        }

        $this->messageQueues[$targetAgentId][] = $message;

        // If message is a task, process it
        if ($message->type === 'task_delegation') {
            $this->processTaskDelegation($targetAgentId, $message);
        }
    }

    /**
     * Broadcast message to all agents except sender
     */
    public function broadcast(string $fromAgentId, Message $message): void
    {
        foreach ($this->messageQueues as $agentId => $queue) {
            if ($agentId !== $fromAgentId) {
                $this->messageQueues[$agentId][] = $message;
            }
        }
    }

    /**
     * Get messages for an agent
     */
    public function getMessages(string $agentId, ?string $messageType = null): array
    {
        if (!isset($this->messageQueues[$agentId])) {
            return [];
        }

        $messages = $this->messageQueues[$agentId];

        if ($messageType !== null) {
            $messages = array_filter($messages, fn($m) => $m->type === $messageType);
        }

        // Clear retrieved messages
        $this->messageQueues[$agentId] = array_diff($this->messageQueues[$agentId], $messages);

        return array_values($messages);
    }

    /**
     * Wait for a specific message (blocking)
     */
    public function waitForMessage(
        string $agentId,
        string $fromAgentId,
        string $messageType,
        int $timeout = 10
    ): Message {
        $endTime = time() + $timeout;

        while (time() < $endTime) {
            $messages = $this->getMessages($agentId, $messageType);

            foreach ($messages as $message) {
                if ($message->from === $fromAgentId) {
                    return $message;
                }
            }

            usleep(100000); // 100ms
        }

        throw new \RuntimeException("Timeout waiting for message from {$fromAgentId}");
    }

    /**
     * Store task result
     */
    public function storeTaskResult(TaskResult $result): void
    {
        $this->taskResults[$result->taskId] = $result;
    }

    /**
     * Wait for task result (blocking)
     */
    public function waitForTaskResult(string $taskId, int $timeout = 30): TaskResult
    {
        $endTime = time() + $timeout;

        while (time() < $endTime) {
            if (isset($this->taskResults[$taskId])) {
                $result = $this->taskResults[$taskId];
                unset($this->taskResults[$taskId]);
                return $result;
            }

            usleep(100000); // 100ms
        }

        throw new \RuntimeException("Timeout waiting for task result: {$taskId}");
    }

    /**
     * Process task delegation
     */
    private function processTaskDelegation(string $targetAgentId, Message $message): void
    {
        if (!isset($this->agents[$targetAgentId])) {
            return;
        }

        $agent = $this->agents[$targetAgentId];
        $taskData = $message->content;

        $task = new Task(
            id: $taskData['id'] ?? uniqid('task_'),
            type: $taskData['type'] ?? 'general',
            description: $taskData['description'] ?? '',
            assignedTo: $targetAgentId,
            createdBy: $message->from
        );

        // Process task asynchronously (in production, use queues)
        $result = $agent->processTask($task);

        // Store result
        $this->storeTaskResult($result);

        // Send completion message back
        $completionMessage = Message::create(
            from: $targetAgentId,
            to: $message->from,
            type: 'task_completed',
            content: [
                'task_id' => $task->id,
                'status' => $result->status,
                'output' => $result->output
            ]
        );

        $this->send($message->from, $completionMessage);
    }
}
```

## Agent Orchestrator

The `AgentOrchestrator` is the high-level interface for creating and managing multi-agent systems. It provides factory methods for creating supervisors and worker teams, and a simple API for executing tasks through the system.

**Responsibilities:**
- Agent creation and initialization
- System setup and configuration
- Task execution coordination
- Agent registry management

The orchestrator simplifies the process of setting up a complete multi-agent system, handling the initialization of the message broker and agent registration automatically.

```php
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
        private Anthropic $claude
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
```

## Complete Example

```php
<?php
# filename: examples/multi-agent-demo.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use App\MultiAgent\AgentOrchestrator;

// Initialize Claude
$claude = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY');

// Create orchestrator
$orchestrator = new AgentOrchestrator($claude);

// Create supervisor
$supervisor = $orchestrator->createSupervisor('Project Manager');

// Create worker team
$workers = $orchestrator->createWorkerTeam();

// Register workers with supervisor
foreach ($workers as $worker) {
    $supervisor->registerWorkerAgent($worker);
}

echo "Multi-Agent System initialized\n";
echo "Supervisor: {$supervisor->name}\n";
echo "Workers:\n";
foreach ($workers as $id => $worker) {
    echo "  - {$id}: {$worker->name} ({$worker->role})\n";
}
echo "\n";

// Example tasks
$tasks = [
    "Create a comprehensive guide for building a Laravel API with authentication, including code examples and best practices documentation.",

    "Research the latest PHP 8.4 features, write code examples demonstrating each feature, and create a blog post summarizing the findings.",

    "Build a user registration system with validation, create database migrations, and write documentation explaining the implementation."
];

foreach ($tasks as $i => $taskDescription) {
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "Task " . ($i + 1) . ": {$taskDescription}\n";
    echo str_repeat('=', 80) . "\n\n";

    $startTime = microtime(true);

    $result = $orchestrator->executeTask($taskDescription, priority: 'high');

    $duration = microtime(true) - $startTime;

    echo "Status: {$result->status}\n";
    echo "Duration: " . number_format($duration, 2) . "s\n\n";
    echo "Result:\n";
    echo $result->output . "\n";
}

echo "\n--- Multi-Agent System Statistics ---\n";
echo "Total agents: " . count($orchestrator->getAgents()) . "\n";
```

### Why It Works

This example demonstrates the complete multi-agent workflow:

1. **Initialization**: The orchestrator creates a supervisor and three specialized worker agents (Research, Code, Writer), automatically registering them with the message broker.

2. **Worker Registration**: Workers are registered with the supervisor, providing it with capability information for intelligent task delegation.

3. **Task Execution**: When a complex task is submitted, the supervisor:
   - Analyzes the task using Claude's reasoning
   - Decomposes it into subtasks (research, coding, writing)
   - Delegates each subtask to the appropriate specialist
   - Waits for results from each agent
   - Synthesizes the final output

4. **Message Flow**: The message broker handles all inter-agent communication:
   - Task delegation messages from supervisor to workers
   - Task completion messages from workers back to supervisor
   - Result storage for retrieval

5. **Parallel Processing**: Independent subtasks can be processed in parallel, improving overall system performance.

The system demonstrates how multiple specialized AI agents can collaborate to solve complex problems that would be difficult for a single agent to handle effectively.

## Data Structures

```php
<?php
# filename: src/MultiAgent/DataStructures.php
declare(strict_types=1);

namespace App\MultiAgent;

readonly class Task
{
    public function __construct(
        public string $id,
        public string $type,
        public string $description,
        public string $assignedTo,
        public string $createdBy,
        public string $priority = 'medium',
        public array $metadata = []
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'description' => $this->description,
            'assigned_to' => $this->assignedTo,
            'created_by' => $this->createdBy,
            'priority' => $this->priority,
            'metadata' => $this->metadata
        ];
    }
}

readonly class TaskResult
{
    public function __construct(
        public string $taskId,
        public string $status,
        public mixed $output,
        public array $metadata = []
    ) {}
}

readonly class Message
{
    public function __construct(
        public string $from,
        public string $to,
        public string $type,
        public mixed $content,
        public float $timestamp
    ) {
        // Timestamp defaults to current time if not provided
        // Note: readonly properties must be initialized in constructor parameters
    }

    public static function create(
        string $from,
        string $to,
        string $type,
        mixed $content,
        ?float $timestamp = null
    ): self {
        return new self(
            from: $from,
            to: $to,
            type: $type,
            content: $content,
            timestamp: $timestamp ?? microtime(true)
        );
    }
}
```

## Wrap-up

You've successfully built a sophisticated multi-agent system! Here's what you accomplished:

- ✓ **Created a flexible agent framework** with base `Agent` class supporting specialization
- ✓ **Implemented supervisor-worker patterns** for task coordination and delegation
- ✓ **Built specialized agents** (Research, Code, Writer) with distinct capabilities
- ✓ **Designed message broker** for reliable inter-agent communication
- ✓ **Created agent orchestrator** for system lifecycle management
- ✓ **Implemented task decomposition** breaking complex problems into subtasks
- ✓ **Built result synthesis** combining outputs from multiple agents
- ✓ **Added error handling** with timeouts and retry mechanisms
- ✓ **Enabled tool use** for agents to delegate tasks and request information
- ✓ **Maintained conversation history** per agent for context preservation

Multi-agent systems enable you to solve complex problems that exceed single-agent capabilities. By coordinating specialized agents through structured communication, you can build sophisticated AI applications that leverage the strengths of each agent while maintaining system reliability and performance.

## Key Takeaways

- Multi-agent systems solve complex problems through specialization
- Supervisor agents coordinate and delegate to worker agents
- Message brokers enable reliable inter-agent communication
- Each agent has specific expertise and capabilities
- Tool use enables agents to delegate and request information
- Task decomposition breaks complex problems into subtasks
- Result synthesis combines outputs from multiple agents
- Conversation history maintains context per agent
- Error handling and retries ensure robustness
- Agent orchestration manages the entire system lifecycle

## Production Deployment

Moving multi-agent systems from prototypes to production requires careful consideration of asynchronous processing, monitoring, security, and scalability. This section covers production-ready patterns and integrations with Laravel queues, observability tools, and security measures.

### Async Agent Processing with Laravel Queues

For production systems, replace synchronous waiting with Laravel's queue system. This prevents blocking requests and enables true concurrent agent processing:

```php
<?php
# filename: src/MultiAgent/Jobs/ProcessAgentTask.php
declare(strict_types=1);

namespace App\MultiAgent\Jobs;

use App\MultiAgent\Task;
use App\MultiAgent\TaskResult;
use App\MultiAgent\AgentOrchestrator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAgentTask implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        private Task $task,
        private string $agentId,
        private string $resultChannel
    ) {}

    public function handle(AgentOrchestrator $orchestrator): void
    {
        try {
            $agent = $orchestrator->getAgent($this->agentId);
            
            if (!$agent) {
                throw new \RuntimeException("Agent not found: {$this->agentId}");
            }

            // Process task
            $result = $agent->processTask($this->task);

            // Broadcast result via WebSocket
            broadcast(new TaskCompleted(
                taskId: $this->task->id,
                result: $result,
                channel: $this->resultChannel
            ))->toOthers();

        } catch (\Exception $e) {
            // Log to observability system
            \Log::error('Agent task failed', [
                'task_id' => $this->task->id,
                'agent_id' => $this->agentId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);

            if ($this->attempts() >= $this->tries) {
                throw $e;
            }
        }
    }

    public function failed(\Exception $e): void
    {
        broadcast(new TaskFailed(
            taskId: $this->task->id,
            error: $e->getMessage(),
            channel: $this->resultChannel
        ))->toOthers();
    }
}
```

**See Also:** Chapter 19 (Queue-Based Processing) for comprehensive queue patterns

### Monitoring Agent Performance

Integrate with observability systems to track agent metrics, execution times, error rates, and model usage:

```php
<?php
# filename: src/MultiAgent/Monitoring/AgentMetrics.php
declare(strict_types=1);

namespace App\MultiAgent\Monitoring;

use Illuminate\Support\Facades\Log;
use OpenTelemetry\API\Metrics\MeterProvider;
use OpenTelemetry\API\Trace\TracerProvider;

class AgentMetrics
{
    private array $metrics = [];

    public function __construct(
        private MeterProvider $meterProvider,
        private TracerProvider $tracerProvider
    ) {}

    public function recordTaskExecution(
        string $agentId,
        string $taskType,
        float $duration,
        bool $success,
        array $metadata = []
    ): void {
        // Record metrics
        $meter = $this->meterProvider->getMeter('multi-agent-system');
        
        $counter = $meter->createCounter('agent_tasks_total', [
            'status' => $success ? 'success' : 'failure',
            'agent_id' => $agentId,
            'task_type' => $taskType
        ]);
        $counter->add(1);

        $histogram = $meter->createHistogram('agent_task_duration_seconds', [
            'agent_id' => $agentId,
            'task_type' => $taskType
        ]);
        $histogram->record($duration);

        // Log structured data
        Log::info('Agent task executed', [
            'agent_id' => $agentId,
            'task_type' => $taskType,
            'duration' => $duration,
            'success' => $success,
            'metadata' => $metadata,
            'timestamp' => date('c')
        ]);

        // Track token usage
        if (isset($metadata['tokens_used'])) {
            $gauge = $meter->createUpDownCounter('agent_tokens_used_total');
            $gauge->add($metadata['tokens_used']);
        }
    }

    public function recordAgentCommunication(
        string $fromAgent,
        string $toAgent,
        string $messageType,
        int $messageSize,
        float $latency
    ): void {
        $meter = $this->meterProvider->getMeter('multi-agent-system');
        
        $histogram = $meter->createHistogram('agent_message_latency_ms', [
            'from_agent' => $fromAgent,
            'to_agent' => $toAgent,
            'message_type' => $messageType
        ]);
        $histogram->record($latency);

        Log::debug('Agent communication', [
            'from_agent' => $fromAgent,
            'to_agent' => $toAgent,
            'message_type' => $messageType,
            'message_size' => $messageSize,
            'latency_ms' => $latency
        ]);
    }
}
```

**See Also:** Chapter 37 (Monitoring & Observability) for comprehensive instrumentation strategies

### Security for Agent Communication

Secure inter-agent communication with validation, encryption, and audit logging:

```php
<?php
# filename: src/MultiAgent/Security/SecureMessageBroker.php
declare(strict_types=1);

namespace App\MultiAgent\Security;

use App\MultiAgent\Message;
use App\MultiAgent\MessageBroker;
use Illuminate\Support\Facades\Log;

class SecureMessageBroker extends MessageBroker
{
    public function __construct(
        private string $encryptionKey,
        private array $allowedMessageTypes = [],
        private array $agentPermissions = []
    ) {
        parent::__construct();
    }

    public function send(string $targetAgentId, Message $message): void
    {
        // Validate message
        $this->validateMessage($message);

        // Check agent permissions
        $this->checkPermissions($message->from, $targetAgentId, $message->type);

        // Encrypt sensitive data
        if ($this->isSensitive($message->type)) {
            $message = $this->encryptMessage($message);
        }

        // Audit log
        Log::info('Agent message sent', [
            'from_agent' => $message->from,
            'to_agent' => $targetAgentId,
            'message_type' => $message->type,
            'timestamp' => date('c'),
            'encrypted' => $this->isSensitive($message->type)
        ]);

        parent::send($targetAgentId, $message);
    }

    private function validateMessage(Message $message): void
    {
        if (!empty($this->allowedMessageTypes) && 
            !in_array($message->type, $this->allowedMessageTypes)) {
            throw new \SecurityException("Message type not allowed: {$message->type}");
        }

        // Validate content structure
        if ($message->type === 'task_delegation') {
            $this->validateTaskContent($message->content);
        }
    }

    private function checkPermissions(string $fromAgent, string $toAgent, string $messageType): void
    {
        if (!isset($this->agentPermissions[$fromAgent][$toAgent])) {
            throw new \SecurityException(
                "Agent {$fromAgent} not permitted to send to {$toAgent}"
            );
        }

        $allowedTypes = $this->agentPermissions[$fromAgent][$toAgent];
        if (!in_array($messageType, $allowedTypes)) {
            throw new \SecurityException(
                "Agent {$fromAgent} not permitted to send {$messageType} to {$toAgent}"
            );
        }
    }

    private function encryptMessage(Message $message): Message
    {
        $encrypted = encrypt(json_encode($message->content), false);
        
        return Message::create(
            from: $message->from,
            to: $message->to,
            type: $message->type,
            content: ['encrypted' => $encrypted, 'algorithm' => 'AES-256-GCM']
        );
    }

    private function isSensitive(string $messageType): bool
    {
        return in_array($messageType, [
            'task_delegation',
            'sensitive_data',
            'credentials'
        ]);
    }

    private function validateTaskContent(array $content): void
    {
        if (empty($content['description'])) {
            throw new \InvalidArgumentException('Task description required');
        }

        if (!isset($content['assigned_to'])) {
            throw new \InvalidArgumentException('Task must specify assigned agent');
        }
    }
}
```

**See Also:** Chapter 36 (Security Best Practices) for comprehensive security patterns

### Scaling to Multiple Servers

For production multi-agent systems across multiple servers, use Redis or message brokers instead of in-memory message queues:

```php
<?php
# filename: src/MultiAgent/DistributedMessageBroker.php
declare(strict_types=1);

namespace App\MultiAgent;

use Illuminate\Support\Facades\Redis;

class DistributedMessageBroker extends MessageBroker
{
    public function __construct(
        private \Predis\Client $redis,
        private string $namespace = 'agents:'
    ) {
        parent::__construct();
    }

    public function send(string $targetAgentId, Message $message): void
    {
        $queueKey = "{$this->namespace}queue:{$targetAgentId}";
        
        // Store message in Redis (persists across servers)
        $this->redis->rpush(
            $queueKey,
            serialize($message)
        );

        // Set expiration to prevent memory leaks
        $this->redis->expire($queueKey, 86400); // 24 hours
    }

    public function getMessages(string $agentId, ?string $messageType = null): array
    {
        $queueKey = "{$this->namespace}queue:{$agentId}";
        $messages = [];

        // Get all messages from Redis
        while ($serialized = $this->redis->lpop($queueKey)) {
            $message = unserialize($serialized);

            if ($messageType === null || $message->type === $messageType) {
                $messages[] = $message;
            }
        }

        return $messages;
    }

    public function storeTaskResult(TaskResult $result): void
    {
        $resultKey = "{$this->namespace}results:{$result->taskId}";
        
        $this->redis->setex(
            $resultKey,
            3600, // 1 hour TTL
            serialize($result)
        );
    }

    public function waitForTaskResult(string $taskId, int $timeout = 30): TaskResult
    {
        $resultKey = "{$this->namespace}results:{$taskId}";
        $endTime = time() + $timeout;

        while (time() < $endTime) {
            $serialized = $this->redis->get($resultKey);

            if ($serialized) {
                return unserialize($serialized);
            }

            usleep(100000); // 100ms
        }

        throw new \RuntimeException("Timeout waiting for task result: {$taskId}");
    }
}
```

**See Also:** Chapter 38 (Scaling) for distributed system patterns

## Advanced Patterns

### Peer-to-Peer Agent Communication

For systems where agents need to communicate directly (not just through supervisors), implement peer networks:

```php
<?php
# filename: src/MultiAgent/PeerNetwork.php
declare(strict_types=1);

namespace App\MultiAgent;

class PeerNetwork
{
    private array $agents = [];
    private array $routes = [];
    private array $discoveryCache = [];

    public function registerAgent(Agent $agent): void
    {
        $agentId = $agent->getAgentId();
        $this->agents[$agentId] = $agent;

        // Announce agent to network
        $this->broadcastDiscovery($agentId, $agent->getCapabilities());
    }

    /**
     * Direct peer-to-peer communication without going through message broker
     */
    public function peerToPeer(string $fromAgent, string $toAgent, Message $message): void
    {
        if (!isset($this->agents[$toAgent])) {
            throw new \RuntimeException("Agent not available: {$toAgent}");
        }

        // Log peer communication
        \Log::info('Peer-to-peer message', [
            'from' => $fromAgent,
            'to' => $toAgent,
            'type' => $message->type
        ]);

        // Direct invocation (can be async with queues in production)
        $this->agents[$toAgent]->receiveMessage($message);
    }

    /**
     * Service discovery for dynamic agent lookup
     */
    public function discoverAgent(string $capability): ?Agent
    {
        // Check cache first
        if (isset($this->discoveryCache[$capability])) {
            return $this->agents[$this->discoveryCache[$capability]] ?? null;
        }

        // Find agent with capability
        foreach ($this->agents as $agentId => $agent) {
            if (in_array($capability, $agent->getCapabilities())) {
                $this->discoveryCache[$capability] = $agentId;
                return $agent;
            }
        }

        return null;
    }

    /**
     * Gossip protocol for state synchronization
     */
    public function gossip(string $fromAgent, array $state): void
    {
        foreach ($this->agents as $agentId => $agent) {
            if ($agentId !== $fromAgent) {
                $message = Message::create(
                    from: $fromAgent,
                    to: $agentId,
                    type: 'gossip_sync',
                    content: $state
                );

                try {
                    $this->peerToPeer($fromAgent, $agentId, $message);
                } catch (\Exception $e) {
                    // Ignore failures in gossip - eventual consistency
                    \Log::debug("Gossip delivery failed to {$agentId}");
                }
            }
        }
    }
}
```

### Consensus and Voting

For critical decisions, use consensus mechanisms where multiple agents vote:

```php
<?php
# filename: src/MultiAgent/Consensus/VotingMechanism.php
declare(strict_types=1);

namespace App\MultiAgent\Consensus;

class VotingMechanism
{
    private array $votes = [];

    public function initiateVote(string $voteId, string $proposal, array $agentIds): void
    {
        $this->votes[$voteId] = [
            'proposal' => $proposal,
            'participants' => array_fill_keys($agentIds, null),
            'started_at' => time()
        ];
    }

    public function vote(string $voteId, string $agentId, bool $decision): void
    {
        if (!isset($this->votes[$voteId])) {
            throw new \RuntimeException("Vote not found: {$voteId}");
        }

        if (!isset($this->votes[$voteId]['participants'][$agentId])) {
            throw new \RuntimeException("Agent not eligible: {$agentId}");
        }

        $this->votes[$voteId]['participants'][$agentId] = $decision;
    }

    public function getResult(string $voteId): array
    {
        if (!isset($this->votes[$voteId])) {
            throw new \RuntimeException("Vote not found: {$voteId}");
        }

        $vote = $this->votes[$voteId];
        $votes = array_filter($vote['participants'], fn($v) => $v !== null);
        
        if (count($votes) === 0) {
            return ['status' => 'pending', 'result' => null];
        }

        $yesCount = array_sum($votes);
        $noCount = count($votes) - $yesCount;
        $totalVotes = count($vote['participants']);

        $result = $yesCount > $noCount ? 'approved' : 'rejected';
        $threshold = ceil($totalVotes / 2);
        $hasQuorum = count($votes) >= $threshold;

        return [
            'status' => 'completed',
            'result' => $result,
            'votes_for' => $yesCount,
            'votes_against' => $noCount,
            'total_participants' => $totalVotes,
            'has_quorum' => $hasQuorum,
            'proposal' => $vote['proposal']
        ];
    }
}
```

## Best Practices

### Agent Design

- **Single Responsibility**: Each agent should have a focused, well-defined purpose
- **Clear Capabilities**: Explicitly define what each agent can and cannot do
- **Optimized Prompts**: Tailor system prompts and temperature settings to each agent's role
- **Error Handling**: Implement robust error handling with retry logic and fallback strategies

### Communication Patterns

- **Message Types**: Use consistent message types for different communication patterns
- **Timeout Management**: Set appropriate timeouts based on expected task duration
- **Result Storage**: Store task results for debugging and auditing
- **Broadcast Sparingly**: Use broadcasts only when necessary to avoid message flooding

### Performance Optimization

- **Parallel Execution**: Delegate independent tasks in parallel when possible
- **Context Management**: Keep conversation history focused to reduce token usage
- **Caching**: Cache agent capabilities and frequently accessed information
- **Async Patterns**: Use async/await or message queues for production systems

### Production Considerations

- **Monitoring**: Track agent performance, task completion rates, and error rates
- **Scaling**: Design for horizontal scaling with distributed message brokers
- **Security**: Validate all inter-agent communications and sanitize inputs
- **Cost Management**: Monitor API usage and optimize for token efficiency

## Troubleshooting

### Error: "Agent not registered"

**Symptom**: `RuntimeException: Agent {agentId} not registered`

**Cause**: Attempting to send a message to an agent that hasn't been registered with the message broker

**Solution**: Ensure all agents are created through the orchestrator or manually registered with the message broker before use:

```php
// Correct: Use orchestrator
$orchestrator = new AgentOrchestrator($claude);
$supervisor = $orchestrator->createSupervisor();
$workers = $orchestrator->createWorkerTeam();

// Or manually register
$messageBroker->registerAgent($agent);
```

### Error: "Timeout waiting for task result"

**Symptom**: `RuntimeException: Timeout waiting for task result: {taskId}`

**Cause**: Task processing exceeded the timeout period (default 30 seconds)

**Solution**: Increase timeout for long-running tasks or optimize task processing:

```php
// Increase timeout
$result = $messageBroker->waitForTaskResult($taskId, timeout: 60);

// Or use async patterns for production
```

### Issue: Agents Not Collaborating

**Symptom**: Supervisor doesn't delegate tasks to workers

**Cause**: Workers not registered with supervisor or supervisor system prompt missing worker information

**Solution**: Ensure workers are registered before processing tasks:

```php
$supervisor = $orchestrator->createSupervisor();
$workers = $orchestrator->createWorkerTeam();

// Critical: Register workers with supervisor
foreach ($workers as $worker) {
    $supervisor->registerWorkerAgent($worker);
}
```

### Issue: Tool Calls Not Working

**Symptom**: Agents don't use delegate_task or request_information tools

**Cause**: Tool definitions missing or Claude not recognizing tool use patterns

**Solution**: Verify tool definitions are correct and system prompts encourage tool use:

```php
// Ensure tools are properly defined
protected function getTools(): array
{
    return [
        [
            'name' => 'delegate_task',
            'description' => 'Clear description...',
            'input_schema' => [
                'type' => 'object',
                'properties' => [...],
                'required' => [...]
            ]
        ]
    ];
}
```

### Issue: High Token Usage

**Symptom**: Excessive API costs due to large conversation histories

**Cause**: Conversation history growing unbounded with each interaction

**Solution**: Implement conversation history management:

```php
// Limit conversation history
protected function buildMessages(string $prompt): array
{
    // Keep only last N messages
    $recentHistory = array_slice($this->conversationHistory, -10);
    $recentHistory[] = [
        'role' => 'user',
        'content' => $prompt
    ];
    return $recentHistory;
}
```

## Related Chapters

These chapters complement multi-agent systems with critical capabilities for production deployment:

- **[Chapter 19: Queue-Based Processing with Laravel](/series/claude-php-developers/chapters/19-queue-processing-laravel)** — Essential for async agent task processing and background job handling
- **[Chapter 34: Prompt Chaining and Workflows](/series/claude-php-developers/chapters/34-prompt-chaining-workflows)** — Orchestrating sequential AI operations; complements agent delegation
- **[Chapter 36: Security Best Practices](/series/claude-php-developers/chapters/36-security-best-practices)** — Securing inter-agent communication and validating agent actions
- **[Chapter 37: Monitoring & Observability](/series/claude-php-developers/chapters/37-monitoring-observability)** — Instrumenting multi-agent systems for production visibility
- **[Chapter 38: Scaling Claude Applications](/series/claude-php-developers/chapters/38-scaling-applications)** — Distributed agent systems across multiple servers

## Further Reading

- [Claude API Documentation — Tool Use](https://docs.claude.com/en/tool-use) — Official guide to function calling and tool integration
- [Multi-Agent Systems Research](https://arxiv.org/list/cs.MA/recent) — Academic papers on multi-agent coordination and distributed consensus
- [Design Patterns: Observer Pattern](https://refactoring.guru/design-patterns/observer) — Communication patterns for agent coordination
- [Distributed Systems Principles](https://martinfowler.com/articles/patterns-of-distributed-systems/) — Foundational patterns for building reliable distributed systems
- [Laravel Queue Documentation](https://laravel.com/docs/11.x/queues) — Production-ready queue processing for async operations

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="33"
  label="You've built a sophisticated multi-agent system!"
/>

---

Continue to [Chapter 34: Prompt Chaining and Workflows](/series/claude-php-developers/chapters/34-prompt-chaining-workflows) to learn advanced workflow orchestration.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 33 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-33)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-33
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php examples/multi-agent-demo.php
```
