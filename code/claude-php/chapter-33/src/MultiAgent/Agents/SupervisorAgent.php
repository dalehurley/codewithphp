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
            output: $response->content[0]->text ?? '',
            metadata: [
                'agent' => $this->agentId,
                'model' => $response->model ?? 'claude-sonnet-4-5-20250929'
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
