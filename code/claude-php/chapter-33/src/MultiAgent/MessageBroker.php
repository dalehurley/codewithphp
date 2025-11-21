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
