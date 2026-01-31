<?php

/**
 * Custom Loop Configuration Example
 * 
 * Demonstrates advanced loop customization including:
 * - Custom termination conditions
 * - Hybrid loop strategies
 * - Adaptive strategy switching
 * - Loop behavior modification with callbacks
 */

declare(strict_types=1);

require_once __DIR__ . '/../../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\ReactLoop;
use ClaudeAgents\Loops\PlanExecuteLoop;
use ClaudeAgents\Loops\ReflectionLoop;
use ClaudeAgents\Progress\AgentUpdate;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

// Initialize Claude client
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Define tools
$complexCalculation = Tool::create('complex_calculation')
    ->description('Perform complex mathematical calculation')
    ->parameter('expression', 'string', 'Math expression')
    ->required('expression')
    ->handler(function (array $input): string {
        // Simulate occasional failures
        if (rand(0, 100) < 20) {
            throw new RuntimeException('Calculation service temporarily unavailable');
        }
        return json_encode(['result' => rand(1, 1000)]);
    });

$validateResult = Tool::create('validate_result')
    ->description('Validate calculation result')
    ->parameter('result', 'string', 'Result to validate')
    ->required('result')
    ->handler(function (array $input): string {
        $valid = rand(0, 100) > 30; // 70% success rate
        return json_encode([
            'valid' => $valid,
            'confidence' => rand(70, 100) / 100
        ]);
    });

$tools = [$complexCalculation, $validateResult];

echo str_repeat('=', 80) . "\n";
echo "Custom Loop Configuration Examples\n";
echo str_repeat('=', 80) . "\n\n";

// Example 1: Adaptive Loop with Fallback Strategy
echo "Example 1: Adaptive Loop - Try React, Fallback to PlanExecute\n";
echo str_repeat('-', 80) . "\n\n";

$attemptCount = 0;
$currentStrategy = 'ReactLoop';

$agent = Agent::create($client)
    ->withSystemPrompt('You are a calculation assistant.')
    ->withTools($tools)
    ->withLoopStrategy(new ReactLoop())
    ->maxIterations(10)
    ->onError(function (Throwable $e, int $attempt) use ($agent, &$attemptCount, &$currentStrategy) {
        $attemptCount++;
        echo "  ⚠️  Error on attempt {$attempt}: {$e->getMessage()}\n";
        
        // Switch to PlanExecuteLoop after 2 failures
        if ($attemptCount >= 2 && $currentStrategy === 'ReactLoop') {
            echo "  🔄 Switching to PlanExecuteLoop for systematic retry...\n\n";
            $agent->withLoopStrategy(new PlanExecuteLoop(allowReplan: true));
            $currentStrategy = 'PlanExecuteLoop';
        }
    })
    ->withRetry(maxAttempts: 3, delayMs: 1000);

try {
    echo "Starting with ReactLoop...\n\n";
    $result = $agent->run('Calculate 157 * 892 and validate the result.');
    echo "✅ Success with {$currentStrategy}\n";
    echo "Answer: {$result->getAnswer()}\n";
} catch (Exception $e) {
    echo "❌ Failed after all attempts: {$e->getMessage()}\n";
}

// Example 2: Custom Termination Logic
echo "\n\n" . str_repeat('-', 80) . "\n";
echo "Example 2: Custom Termination - Stop When Confidence Threshold Met\n";
echo str_repeat('-', 80) . "\n\n";

$confidenceThreshold = 0.85;
$highestConfidence = 0.0;
$shouldTerminate = false;

$agent = Agent::create($client)
    ->withSystemPrompt('You are a calculation assistant. Continue until you have high confidence.')
    ->withTools($tools)
    ->withLoopStrategy(new ReactLoop())
    ->maxIterations(5)
    ->onUpdate(function (AgentUpdate $update) use (&$highestConfidence, &$shouldTerminate, $confidenceThreshold) {
        if ($update->getType() === 'tool_result') {
            $data = $update->getData();
            if (isset($data['confidence'])) {
                $confidence = $data['confidence'];
                $highestConfidence = max($highestConfidence, $confidence);
                
                echo "  📊 Confidence: " . ($confidence * 100) . "%\n";
                
                if ($confidence >= $confidenceThreshold) {
                    echo "  ✅ Confidence threshold met! Terminating...\n";
                    $shouldTerminate = true;
                }
            }
        }
        
        // Check for custom termination
        if ($shouldTerminate) {
            throw new RuntimeException('Confidence threshold achieved');
        }
    });

try {
    $result = $agent->run('Calculate 456 * 789 and validate until you are confident.');
    echo "\n✅ Completed with {$highestConfidence}% confidence\n";
} catch (Exception $e) {
    if (str_contains($e->getMessage(), 'Confidence threshold')) {
        echo "✅ Terminated early due to high confidence\n";
    } else {
        echo "❌ Error: {$e->getMessage()}\n";
    }
}

// Example 3: Hybrid Reflection + Streaming
echo "\n\n" . str_repeat('-', 80) . "\n";
echo "Example 3: Hybrid Loop - Reflection with Streaming Updates\n";
echo str_repeat('-', 80) . "\n\n";

$reflectionCount = 0;

$agent = Agent::create($client)
    ->withSystemPrompt('You are a meticulous calculation assistant. Double-check your work.')
    ->withTools($tools)
    ->withLoopStrategy(new ReflectionLoop(
        maxReflections: 2,
        reflectionPrompt: 'Review the calculation. Is it correct? Check for errors.'
    ))
    ->maxIterations(10)
    ->onUpdate(function (AgentUpdate $update) {
        $timestamp = date('H:i:s');
        $type = $update->getType();
        $data = $update->getData();
        $message = $data['message'] ?? $data['text'] ?? $type;
        
        $icon = match ($type) {
            'thinking' => '🤔',
            'tool_call' => '🔧',
            'tool_result' => '✅',
            'reflection' => '🔍',
            'refinement' => '📝',
            default => 'ℹ️'
        };
        
        echo "[{$timestamp}] {$icon} {$type}: {$message}\n";
    })
    ->onReflection(function (array $reflection) use (&$reflectionCount) {
        $reflectionCount++;
        echo "\n" . str_repeat('─', 60) . "\n";
        echo "  🔍 Reflection #{$reflectionCount}\n";
        echo "  Critique: {$reflection['critique']}\n";
        
        if (!empty($reflection['issues'])) {
            echo "  Issues: " . implode(', ', $reflection['issues']) . "\n";
        }
        echo str_repeat('─', 60) . "\n\n";
    });

try {
    $result = $agent->run('Calculate 234 * 567, validate it, and ensure accuracy.');
    echo "\n✅ Completed with {$reflectionCount} reflection passes\n";
    echo "Answer: {$result->getAnswer()}\n";
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
}

// Example 4: Loop with Budget Constraints
echo "\n\n" . str_repeat('-', 80) . "\n";
echo "Example 4: Budget-Constrained Loop - Stop at Token Limit\n";
echo str_repeat('-', 80) . "\n\n";

$maxTokens = 1000;
$tokensUsed = 0;

$agent = Agent::create($client)
    ->withSystemPrompt('You are a calculation assistant.')
    ->withTools($tools)
    ->withLoopStrategy(new ReactLoop())
    ->maxIterations(10)
    ->onUpdate(function (AgentUpdate $update) use (&$tokensUsed, $maxTokens) {
        // Simulate token tracking
        $tokensUsed += rand(50, 150);
        
        if ($update->getType() === 'thinking') {
            echo "  💭 Tokens used: {$tokensUsed}/{$maxTokens}\n";
        }
        
        if ($tokensUsed >= $maxTokens) {
            echo "  ⚠️  Token budget exceeded! Stopping...\n";
            throw new RuntimeException('Token budget exceeded');
        }
    });

try {
    $result = $agent->run('Perform multiple calculations: 12*34, 56*78, 90*12, validate each.');
    echo "✅ Completed within budget\n";
    echo "Tokens used: {$tokensUsed}/{$maxTokens}\n";
} catch (Exception $e) {
    if (str_contains($e->getMessage(), 'Token budget')) {
        echo "⚠️  Stopped due to token budget constraint\n";
        echo "Tokens used: {$tokensUsed}/{$maxTokens}\n";
    } else {
        echo "❌ Error: {$e->getMessage()}\n";
    }
}

// Example 5: Multi-Stage Execution
echo "\n\n" . str_repeat('-', 80) . "\n";
echo "Example 5: Multi-Stage Execution - Plan, then Execute with Reflection\n";
echo str_repeat('-', 80) . "\n\n";

echo "Stage 1: Planning phase...\n";

$planner = Agent::create($client)
    ->withSystemPrompt('You are a task planner. Create a detailed plan.')
    ->withTools($tools)
    ->withLoopStrategy(new PlanExecuteLoop(showPlanInOutput: true))
    ->maxIterations(5);

try {
    $planResult = $planner->run('Plan how to calculate 345 * 678 and validate it thoroughly.');
    $plan = $planResult->getPlan();
    
    echo "✅ Plan generated:\n";
    foreach ($plan['steps'] as $i => $step) {
        echo "  " . ($i + 1) . ". {$step['description']}\n";
    }
    
    echo "\nStage 2: Execution phase with reflection...\n";
    
    $executor = Agent::create($client)
        ->withSystemPrompt('You are a careful executor. Follow the plan precisely.')
        ->withTools($tools)
        ->withLoopStrategy(new ReflectionLoop(maxReflections: 1))
        ->maxIterations(8);
    
    $executeResult = $executor->run(
        'Execute this plan: ' . json_encode($plan) . 
        '. Reflect on the results to ensure accuracy.'
    );
    
    echo "\n✅ Execution completed\n";
    echo "Result: {$executeResult->getAnswer()}\n";
    
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
}

echo "\n\n" . str_repeat('=', 80) . "\n";
echo "✅ Custom Loop Configuration Examples Completed\n";
echo str_repeat('=', 80) . "\n\n";

echo "Key Takeaways:\n";
echo "  1. Loops can be switched dynamically based on errors or conditions\n";
echo "  2. Custom termination logic via callbacks and exceptions\n";
echo "  3. Reflection can be combined with streaming for visibility\n";
echo "  4. Budget constraints (tokens, time, cost) can be enforced\n";
echo "  5. Multi-stage execution: planning → execution → refinement\n";
