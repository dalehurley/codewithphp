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
            output: $response->content[0]->text ?? '',
            metadata: [
                'agent' => $this->agentId,
                'agent_type' => 'research'
            ]
        );
    }
}
