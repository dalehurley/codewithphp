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
        protected ClaudePhp $claude,
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

        $requestData = [
            'model' => $options['model'] ?? 'claude-sonnet-4-5-20250929',
            'max_tokens' => $options['max_tokens'] ?? 4096,
            'temperature' => $options['temperature'] ?? 0.7,
            'system' => $this->getSystemPrompt(),
            'messages' => $messages
        ];

        // Add tools if available
        $tools = $this->getTools();
        if (!empty($tools)) {
            $requestData['tools'] = $tools;
        }

        $response = $this->claude->messages()->create($requestData);

        // Handle tool calls if present
        if (isset($response->stop_reason) && $response->stop_reason === 'tool_use') {
            $response = $this->handleToolCalls($response, $messages);
        }

        // Update conversation history
        $this->conversationHistory[] = [
            'role' => 'user',
            'content' => $prompt
        ];
        $this->conversationHistory[] = [
            'role' => 'assistant',
            'content' => $response->content[0]->text ?? ''
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

        $this->messageBroker->send($targetAgentId, $message);

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
                'tool_call_id' => $block->id,
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
            'model' => 'claude-sonnet-4-5-20250929',
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

        $this->messageBroker->send($input->agent_id, $message);

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
