<?php

declare(strict_types=1);

/**
 * 04: Result Validation and Adaptation
 *
 * This example demonstrates:
 * - How result validation works
 * - Quality scoring and criteria
 * - Adaptive retry with different agents
 * - Request reframing on failure
 *
 * See how the service ensures quality through validation and adaptation.
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agents\AdaptiveAgentService;
use ClaudeAgents\Agents\ReactAgent;
use ClaudeAgents\Agents\ReflectionAgent;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    echo "Error: ANTHROPIC_API_KEY environment variable not set\n";
    exit(1);
}

echo "=================================================================\n";
echo "Result Validation and Adaptation\n";
echo "=================================================================\n\n";

$client = new ClaudePhp(apiKey: $apiKey);

// =================================================================
// Setup Agents
// =================================================================

$calculatorTool = Tool::create('calculate')
    ->description('Perform mathematical calculations')
    ->stringParam('expression', 'Mathematical expression')
    ->handler(function (array $input): string {
        try {
            $expr = preg_replace('/[^0-9+\-*\/().\s]/', '', $input['expression']);
            return (string) eval("return {$expr};");
        } catch (\Throwable $e) {
            return "Error: {$e->getMessage()}";
        }
    });

$reactAgent = new ReactAgent($client, [
    'tools' => [$calculatorTool],
    'max_iterations' => 5,
]);

$reflectionAgent = new ReflectionAgent($client, [
    'max_refinements' => 2,
    'quality_threshold' => 7,
]);

// =================================================================
// Example 1: High Quality Threshold
// =================================================================

echo "─────────────────────────────────────────────────────────────────\n";
echo "Example 1: High Quality Threshold\n";
echo "─────────────────────────────────────────────────────────────────\n\n";

echo "Setting quality threshold to 8.0 (high)...\n\n";

$service1 = new AdaptiveAgentService($client, [
    'max_attempts' => 3,
    'quality_threshold' => 8.0,  // High threshold
    'enable_reframing' => true,
    'enable_knn' => false,  // Disable for this demo
]);

$service1->registerAgent('react', $reactAgent, [
    'type' => 'react',
    'complexity_level' => 'medium',
    'quality' => 'standard',
]);

$service1->registerAgent('reflection', $reflectionAgent, [
    'type' => 'reflection',
    'complexity_level' => 'medium',
    'quality' => 'high',
]);

$task1 = "Write a brief professional introduction for a software engineer";

echo "Task: {$task1}\n\n";
echo "Expected: Service may try multiple agents to meet 8.0 quality\n\n";

$result1 = $service1->run($task1);

if ($result1->isSuccess()) {
    echo "✓ SUCCESS\n\n";
    echo "Answer (first 200 chars):\n{$result1->getAnswer()}\n...\n\n";

    $metadata = $result1->getMetadata();
    echo "Final Agent: {$metadata['final_agent']}\n";
    echo "Final Quality: {$metadata['final_quality']}/10\n";
    echo "Total Attempts: {$result1->getIterations()}\n\n";

    // Show all attempts
    if (!empty($metadata['attempts'])) {
        echo "Attempt History:\n";
        foreach ($metadata['attempts'] as $attempt) {
            echo "  Attempt {$attempt['attempt']}: {$attempt['agent_type']} agent\n";
            echo "    Quality: {$attempt['validation']['quality_score']}/10\n";
            echo "    Success: " . ($attempt['success'] ? 'yes' : 'no') . "\n";

            if (!empty($attempt['validation']['issues'])) {
                echo "    Issues: " . implode(', ', $attempt['validation']['issues']) . "\n";
            }

            if (!empty($attempt['validation']['strengths'])) {
                echo "    Strengths: " . implode(', ', $attempt['validation']['strengths']) . "\n";
            }

            echo "\n";
        }
    }
} else {
    echo "✗ FAILED\n";
    echo "Error: {$result1->getError()}\n\n";

    $metadata = $result1->getMetadata();
    if (isset($metadata['best_attempt'])) {
        echo "Best attempt achieved {$metadata['best_attempt']['validation']['quality_score']}/10\n\n";
    }
}

// =================================================================
// Example 2: Request Reframing
// =================================================================

echo "─────────────────────────────────────────────────────────────────\n";
echo "Example 2: Request Reframing on Low Quality\n";
echo "─────────────────────────────────────────────────────────────────\n\n";

$service2 = new AdaptiveAgentService($client, [
    'max_attempts' => 3,
    'quality_threshold' => 7.0,
    'enable_reframing' => true,  // Enable reframing
    'enable_knn' => false,
]);

$service2->registerAgent('react', $reactAgent, [
    'type' => 'react',
    'complexity_level' => 'medium',
    'quality' => 'standard',
]);

$service2->registerAgent('reflection', $reflectionAgent, [
    'type' => 'reflection',
    'complexity_level' => 'medium',
    'quality' => 'high',
]);

// Intentionally vague task
$task2 = "Tell me about PHP";

echo "Task (intentionally vague): {$task2}\n\n";
echo "Expected: Service may reframe to be more specific\n\n";

$result2 = $service2->run($task2);

if ($result2->isSuccess()) {
    echo "✓ SUCCESS\n\n";
    echo "Answer (first 200 chars):\n" . substr($result2->getAnswer(), 0, 200) . "...\n\n";

    $metadata = $result2->getMetadata();
    echo "Final Quality: {$metadata['final_quality']}/10\n";
    echo "Attempts: {$result2->getIterations()}\n\n";

    // Check if reframing occurred
    if ($result2->getIterations() > 1) {
        echo "Note: Multiple attempts suggest reframing or agent switching occurred\n\n";
    }
} else {
    echo "✗ Task did not meet quality threshold\n\n";
}

// =================================================================
// Example 3: Validation Details
// =================================================================

echo "─────────────────────────────────────────────────────────────────\n";
echo "Example 3: Detailed Validation Inspection\n";
echo "─────────────────────────────────────────────────────────────────\n\n";

$service3 = new AdaptiveAgentService($client, [
    'max_attempts' => 2,
    'quality_threshold' => 7.0,
    'enable_reframing' => false,
    'enable_knn' => false,
]);

$service3->registerAgent('react', $reactAgent, [
    'type' => 'react',
    'complexity_level' => 'medium',
    'quality' => 'standard',
]);

$task3 = "Calculate 15% of 240";

echo "Task: {$task3}\n\n";

$result3 = $service3->run($task3);

if ($result3->isSuccess()) {
    echo "✓ SUCCESS\n\n";
    echo "Answer: {$result3->getAnswer()}\n\n";

    $metadata = $result3->getMetadata();

    if (!empty($metadata['attempts'])) {
        $firstAttempt = $metadata['attempts'][0];
        $validation = $firstAttempt['validation'];

        echo "Validation Details:\n";
        echo "  Quality Score: {$validation['quality_score']}/10\n";
        echo "  Is Correct: " . ($validation['is_correct'] ? 'yes' : 'no') . "\n";
        echo "  Is Complete: " . ($validation['is_complete'] ? 'yes' : 'no') . "\n";

        if (!empty($validation['issues'])) {
            echo "  Issues:\n";
            foreach ($validation['issues'] as $issue) {
                echo "    - {$issue}\n";
            }
        }

        if (!empty($validation['strengths'])) {
            echo "  Strengths:\n";
            foreach ($validation['strengths'] as $strength) {
                echo "    - {$strength}\n";
            }
        }

        echo "\n";
    }
}

// =================================================================
// Example 4: Adaptation Strategies
// =================================================================

echo "─────────────────────────────────────────────────────────────────\n";
echo "Example 4: Multiple Agents for Difficult Task\n";
echo "─────────────────────────────────────────────────────────────────\n\n";

$service4 = new AdaptiveAgentService($client, [
    'max_attempts' => 3,
    'quality_threshold' => 7.5,
    'enable_reframing' => true,
    'enable_knn' => false,
]);

$service4->registerAgent('react', $reactAgent, [
    'type' => 'react',
    'complexity_level' => 'medium',
    'quality' => 'standard',
]);

$service4->registerAgent('reflection', $reflectionAgent, [
    'type' => 'reflection',
    'complexity_level' => 'medium',
    'quality' => 'high',
]);

$task4 = "Explain dependency injection in PHP with a clear code example";

echo "Task: {$task4}\n\n";
echo "Quality threshold: 7.5 (will try to achieve high quality)\n\n";

$result4 = $service4->run($task4);

if ($result4->isSuccess()) {
    echo "✓ SUCCESS\n\n";

    $metadata = $result4->getMetadata();
    echo "Final Agent: {$metadata['final_agent']}\n";
    echo "Final Quality: {$metadata['final_quality']}/10\n";
    echo "Total Attempts: {$result4->getIterations()}\n\n";

    if ($result4->getIterations() > 1) {
        echo "Adaptation Strategy:\n";
        foreach ($metadata['attempts'] as $attempt) {
            echo "  Attempt {$attempt['attempt']}: {$attempt['agent_id']} (quality: {$attempt['validation']['quality_score']}/10)\n";
        }
        echo "\n  → Service tried different agents to meet quality threshold\n\n";
    }

    echo "Answer (first 300 chars):\n" . substr($result4->getAnswer(), 0, 300) . "...\n\n";
} else {
    echo "✗ FAILED\n";
    echo "Error: {$result4->getError()}\n\n";
}

// =================================================================
// Key Takeaways
// =================================================================

echo "=================================================================\n";
echo "Key Lessons:\n";
echo "=================================================================\n\n";

echo "1. Validation Criteria\n";
echo "   - Correctness: Does it answer correctly?\n";
echo "   - Completeness: Is the answer complete?\n";
echo "   - Clarity: Is it well-structured?\n";
echo "   - Relevance: Is it relevant to the task?\n\n";

echo "2. Adaptation Strategies\n";
echo "   - Try different agent (different strengths)\n";
echo "   - Reframe request (make it clearer)\n";
echo "   - Up to max_attempts tries\n\n";

echo "3. Quality Threshold\n";
echo "   - Higher threshold = more attempts\n";
echo "   - Match threshold to agent capabilities\n";
echo "   - 7.0 = good balance for most cases\n\n";

echo "4. Reframing\n";
echo "   - Occurs when quality significantly below threshold\n";
echo "   - Makes vague requests more specific\n";
echo "   - Can improve results on retry\n\n";
