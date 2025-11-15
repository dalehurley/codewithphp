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

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapters 11-15** (Tool use fundamentals)
- ✓ **Async processing knowledge** for parallel execution
- ✓ **Design pattern familiarity** for architecture
- ✓ **Complex system experience** for orchestration

**Estimated Time**: 120-150 minutes

## Agent Framework

```php
<?php
# filename: src/MultiAgent/Agent.php
declare(strict_types=1);

namespace App\MultiAgent;

use Anthropic\Anthropic;

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
        if ($response->stopReason === 'tool_use') {
            $response = $this->handleToolCalls($response, $messages);
        }

        // Update conversation history
        $this->conversationHistory[] = [
            'role' => 'user',
            'content' => $prompt
        ];
        $this->conversationHistory[] = [
            'role' => 'assistant',
            'content' => $response->content[0]->text
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
        $message = new Message(
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
        foreach ($response->content as $block) {
            if ($block->type !== 'tool_use') {
                continue;
            }

            $result = match($block->name) {
                'delegate_task' => $this->handleDelegateTask($block->input),
                'request_information' => $this->handleRequestInformation($block->input),
                default => ['error' => 'Unknown tool']
            };

            // Continue conversation with tool result
            $messages[] = [
                'role' => 'assistant',
                'content' => $response->content
            ];

            $messages[] = [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'tool_result',
                        'tool_use_id' => $block->id,
                        'content' => json_encode($result)
                    ]
                ]
            ];
        }

        // Get final response
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
        $message = new Message(
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
            output: $response->content[0]->text,
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
            output: $response->content[0]->text,
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
            output: $response->content[0]->text,
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
            output: $response->content[0]->text,
            metadata: [
                'agent' => $this->agentId,
                'agent_type' => 'writer'
            ]
        );
    }
}
```

## Message Broker

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
        $completionMessage = new Message(
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

```php
<?php
# filename: src/MultiAgent/AgentOrchestrator.php
declare(strict_types=1);

namespace App\MultiAgent;

use Anthropic\Anthropic;

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

use Anthropic\Anthropic;
use App\MultiAgent\AgentOrchestrator;

// Initialize Claude
$claude = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

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

    "Research the latest PHP 8.3 features, write code examples demonstrating each feature, and create a blog post summarizing the findings.",

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
        public float $timestamp = 0.0
    ) {
        if ($this->timestamp === 0.0) {
            $this->timestamp = microtime(true);
        }
    }
}
```

## Key Takeaways

- ✓ Multi-agent systems solve complex problems through specialization
- ✓ Supervisor agents coordinate and delegate to worker agents
- ✓ Message brokers enable reliable inter-agent communication
- ✓ Each agent has specific expertise and capabilities
- ✓ Tool use enables agents to delegate and request information
- ✓ Task decomposition breaks complex problems into subtasks
- ✓ Result synthesis combines outputs from multiple agents
- ✓ Conversation history maintains context per agent
- ✓ Error handling and retries ensure robustness
- ✓ Agent orchestration manages the entire system lifecycle

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
