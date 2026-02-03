<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

/**
 * Task Decomposition Strategies
 * 
 * Demonstrates different approaches to breaking down complex tasks:
 * - Sequential: Steps depend on each other
 * - Parallel: Independent steps
 * - Hierarchical: Phases with sub-steps
 * - Iterative: Repeating cycles
 */

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

/**
 * Helper function to generate a task decomposition plan.
 */
function decomposeTask(ClaudePhp $client, string $task, string $strategy): array
{
    $strategyPrompts = [
        'sequential' => 'Break this into sequential steps where each step depends on the previous one.',
        'parallel' => 'Break this into independent steps that can be executed in parallel.',
        'hierarchical' => 'Break this into major phases, then break each phase into sub-steps.',
        'iterative' => 'Break this into an iterative cycle of steps that repeat until complete.',
    ];
    
    $prompt = "Task: {$task}\n\n" .
              "{$strategyPrompts[$strategy]}\n\n" .
              "Format each step as:\n1. [Step description]\n2. [Step description]\n...";
    
    $response = $client->messages()->create([
        'model' => 'claude-sonnet-4-20250514',
        'max_tokens' => 1024,
        'system' => 'You are a systematic task planner.',
        'messages' => [['role' => 'user', 'content' => $prompt]],
    ]);
    
    $plan = '';
    foreach ($response->content as $block) {
        if (isset($block['type']) && $block['type'] === 'text') {
            $plan .= $block['text'];
        }
    }
    
    // Parse steps
    $steps = [];
    foreach (explode("\n", $plan) as $line) {
        if (preg_match('/^\d+\.\s+(.+)$/', trim($line), $matches)) {
            $steps[] = trim($matches[1]);
        }
    }
    
    return $steps;
}

echo "=== Task Decomposition Strategies ===\n\n";

$task = "Build a user authentication system with email verification";

// Strategy 1: Sequential Decomposition
echo "1. Sequential Decomposition (steps depend on each other):\n";
echo str_repeat('-', 60) . "\n";

$steps = decomposeTask($client, $task, 'sequential');
foreach ($steps as $i => $step) {
    echo "   " . ($i + 1) . ". {$step}\n";
}
echo "\n";

// Strategy 2: Parallel Decomposition
echo "2. Parallel Decomposition (independent steps):\n";
echo str_repeat('-', 60) . "\n";

$steps = decomposeTask($client, $task, 'parallel');
foreach ($steps as $i => $step) {
    echo "   " . ($i + 1) . ". {$step}\n";
}
echo "\n";

// Strategy 3: Hierarchical Decomposition
echo "3. Hierarchical Decomposition (phases → substeps):\n";
echo str_repeat('-', 60) . "\n";

$steps = decomposeTask($client, $task, 'hierarchical');
foreach ($steps as $i => $step) {
    echo "   " . ($i + 1) . ". {$step}\n";
}
echo "\n";

// Strategy 4: Iterative Decomposition
echo "4. Iterative Decomposition (repeating cycles):\n";
echo str_repeat('-', 60) . "\n";

$steps = decomposeTask($client, $task, 'iterative');
foreach ($steps as $i => $step) {
    echo "   " . ($i + 1) . ". {$step}\n";
}
echo "\n";

// Best practices
echo "=== Choosing the Right Strategy ===\n\n";
echo "• Sequential: When steps have strict dependencies (A → B → C)\n";
echo "• Parallel: When steps are independent (A, B, C can happen simultaneously)\n";
echo "• Hierarchical: When task has distinct phases with sub-tasks\n";
echo "• Iterative: When task requires repeated cycles (build → test → refine)\n";
