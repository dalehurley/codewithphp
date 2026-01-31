<?php

declare(strict_types=1);

$task = "Explain agentic AI and include today's date.";

function plainLlmCall(string $prompt): string
{
    return "Agentic AI uses tools, memory, and loops to solve tasks."
        . " (This plain call can't look up today's date.)";
}

echo "Task: {$task}" . PHP_EOL;
echo plainLlmCall($task) . PHP_EOL;
