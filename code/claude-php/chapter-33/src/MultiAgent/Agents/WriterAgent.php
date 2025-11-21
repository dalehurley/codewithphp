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
            output: $response->content[0]->text ?? '',
            metadata: [
                'agent' => $this->agentId,
                'agent_type' => 'writer'
            ]
        );
    }
}
