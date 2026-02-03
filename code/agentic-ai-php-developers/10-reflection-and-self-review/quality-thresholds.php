<?php

/**
 * Quality Thresholds and Scoring Example
 * 
 * Demonstrates how different quality thresholds affect reflection behavior.
 * Compares low, medium, and high threshold settings for the same task.
 * 
 * From: Agentic AI for PHP Developers
 * Chapter 10: Reflection and Self-Review Loops
 * 
 * @link https://github.com/claude-php/claude-php-agent
 */

declare(strict_types=1);

// Adjust path to your vendor/autoload.php location
require __DIR__ . '/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\ReflectionLoop;
use ClaudePhp\ClaudePhp;

// Initialize Claude client
$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    die("Error: ANTHROPIC_API_KEY environment variable not set\n");
}

$client = new ClaudePhp(apiKey: $apiKey);

echo "Quality Thresholds Comparison\n";
echo str_repeat("=", 80) . "\n\n";

$task = 'Explain what property hooks are in PHP 8.4 and provide a simple example.';

/**
 * Profile 1: Rapid Draft (Low Threshold)
 * Good for: Internal docs, quick prototypes, drafts
 */
echo "Profile 1: Rapid Draft (Threshold: 6/10, Max Refinements: 1)\n";
echo str_repeat("-", 80) . "\n";

$rapidLoop = new ReflectionLoop(
    maxRefinements: 1,
    qualityThreshold: 6,
    criteria: 'basic correctness and clarity'
);

$rapidLoop->onReflection(function (int $ref, int $score, string $feedback): void {
    echo "  Refinement {$ref}: {$score}/10\n";
});

$rapidAgent = Agent::create($client)
    ->withLoopStrategy($rapidLoop)
    ->withSystemPrompt('You are a helpful assistant.')
    ->maxIterations(10);

$startTime = microtime(true);
$rapidResult = $rapidAgent->run($task);
$rapidDuration = microtime(true) - $startTime;

echo "\nRapid Draft Output:\n";
echo substr($rapidResult->getAnswer(), 0, 300) . "...\n\n";
echo "Stats:\n";
echo "  - Final Score: {$rapidResult->getMetadata()['final_score']}/10\n";
echo "  - Refinements: " . count($rapidResult->getMetadata()['reflections']) . "\n";
echo "  - Duration: " . round($rapidDuration, 2) . "s\n";
echo "  - Tokens: " . ($rapidResult->getTokenUsage()['total'] ?? 0) . "\n\n";

/**
 * Profile 2: Standard Quality (Medium Threshold)
 * Good for: Production content, user-facing output, standard tasks
 */
echo "\nProfile 2: Standard Quality (Threshold: 8/10, Max Refinements: 3)\n";
echo str_repeat("-", 80) . "\n";

$standardLoop = new ReflectionLoop(
    maxRefinements: 3,
    qualityThreshold: 8,
    criteria: 'correctness, completeness, clarity, and code example quality'
);

$standardLoop->onReflection(function (int $ref, int $score, string $feedback): void {
    echo "  Refinement {$ref}: {$score}/10\n";
});

$standardAgent = Agent::create($client)
    ->withLoopStrategy($standardLoop)
    ->withSystemPrompt('You are a skilled technical writer creating production-quality content.')
    ->maxIterations(15);

$startTime = microtime(true);
$standardResult = $standardAgent->run($task);
$standardDuration = microtime(true) - $startTime;

echo "\nStandard Quality Output:\n";
echo substr($standardResult->getAnswer(), 0, 300) . "...\n\n";
echo "Stats:\n";
echo "  - Final Score: {$standardResult->getMetadata()['final_score']}/10\n";
echo "  - Refinements: " . count($standardResult->getMetadata()['reflections']) . "\n";
echo "  - Duration: " . round($standardDuration, 2) . "s\n";
echo "  - Tokens: " . ($standardResult->getTokenUsage()['total'] ?? 0) . "\n\n";

/**
 * Profile 3: Critical/Premium (High Threshold)
 * Good for: Public docs, critical content, high-stakes tasks
 */
echo "\nProfile 3: Critical Quality (Threshold: 9/10, Max Refinements: 5)\n";
echo str_repeat("-", 80) . "\n";

$criticalLoop = new ReflectionLoop(
    maxRefinements: 5,
    qualityThreshold: 9,
    criteria: 'exceptional correctness, completeness, clarity, engaging examples, and perfect accuracy'
);

$criticalLoop->onReflection(function (int $ref, int $score, string $feedback): void {
    echo "  Refinement {$ref}: {$score}/10\n";
});

$criticalAgent = Agent::create($client)
    ->withLoopStrategy($criticalLoop)
    ->withSystemPrompt('You are an expert technical writer. Create exceptional, publication-ready content.')
    ->maxIterations(20);

$startTime = microtime(true);
$criticalResult = $criticalAgent->run($task);
$criticalDuration = microtime(true) - $startTime;

echo "\nCritical Quality Output:\n";
echo substr($criticalResult->getAnswer(), 0, 300) . "...\n\n";
echo "Stats:\n";
echo "  - Final Score: {$criticalResult->getMetadata()['final_score']}/10\n";
echo "  - Refinements: " . count($criticalResult->getMetadata()['reflections']) . "\n";
echo "  - Duration: " . round($criticalDuration, 2) . "s\n";
echo "  - Tokens: " . ($criticalResult->getTokenUsage()['total'] ?? 0) . "\n\n";

/**
 * Comparison Summary
 */
echo "\n" . str_repeat("=", 80) . "\n";
echo "COMPARISON SUMMARY\n";
echo str_repeat("=", 80) . "\n\n";

$profiles = [
    'Rapid Draft' => [
        'threshold' => 6,
        'max_refinements' => 1,
        'result' => $rapidResult,
        'duration' => $rapidDuration,
    ],
    'Standard' => [
        'threshold' => 8,
        'max_refinements' => 3,
        'result' => $standardResult,
        'duration' => $standardDuration,
    ],
    'Critical' => [
        'threshold' => 9,
        'max_refinements' => 5,
        'result' => $criticalResult,
        'duration' => $criticalDuration,
    ],
];

printf("%-15s | %10s | %12s | %10s | %10s | %10s\n",
    'Profile', 'Threshold', 'Max Refine', 'Final Score', 'Duration', 'Tokens'
);
echo str_repeat("-", 80) . "\n";

foreach ($profiles as $name => $data) {
    $finalScore = $data['result']->getMetadata()['final_score'];
    $tokens = $data['result']->getTokenUsage()['total'] ?? 0;
    
    printf("%-15s | %8s/10 | %12d | %8s/10 | %8ss | %10d\n",
        $name,
        $data['threshold'],
        $data['max_refinements'],
        $finalScore,
        round($data['duration'], 1),
        $tokens
    );
}

echo "\n📊 Key Observations:\n";
echo "   1. Higher thresholds = More refinements = Better quality\n";
echo "   2. Higher thresholds = More tokens = Higher cost\n";
echo "   3. Higher thresholds = More latency = Slower response\n";
echo "\n💡 Recommendation: Match threshold to task importance\n";
echo "   - Internal docs: 6-7/10\n";
echo "   - User-facing: 8/10\n";
echo "   - Critical/Public: 9/10\n";
echo "   - Perfection (10/10): Rarely achievable, not recommended\n";
