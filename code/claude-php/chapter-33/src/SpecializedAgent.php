<?php

declare(strict_types=1);

namespace App;

class ResearchAgent extends Agent
{
    public function execute(string $task): array
    {
        return ['role' => 'research', 'result' => "Research completed for: $task"];
    }
}

class WriterAgent extends Agent
{
    public function execute(string $task): array
    {
        return ['role' => 'writer', 'result' => "Content created for: $task"];
    }
}

class ReviewerAgent extends Agent
{
    public function execute(string $task): array
    {
        return ['role' => 'reviewer', 'result' => "Review completed for: $task"];
    }
}
