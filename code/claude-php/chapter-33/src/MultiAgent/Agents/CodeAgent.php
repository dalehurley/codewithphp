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
            output: $response->content[0]->text ?? '',
            metadata: [
                'agent' => $this->agentId,
                'agent_type' => 'code'
            ]
        );
    }
}
