<?php

/**
 * Chapter 17: Evaluation Harnesses and QA
 * Example 01: Basic Evaluation Harness
 * 
 * Demonstrates building a simple evaluation framework to test agents
 * against known inputs and outputs. Measures pass/fail rates and basic accuracy.
 * 
 * Run: php 01-basic-evaluation-harness.php
 * Requires: ANTHROPIC_API_KEY environment variable
 */

declare(strict_types=1);

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudePhp\ClaudePhp;

echo "╔════════════════════════════════════════════════════════════════════╗\n";
echo "║  Chapter 17: Basic Evaluation Harness                             ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n\n";

if (!getenv('ANTHROPIC_API_KEY')) {
    die("❌ Error: ANTHROPIC_API_KEY environment variable not set\n");
}

/**
 * Simple test case structure
 */
class TestCase
{
    public function __construct(
        public readonly string $id,
        public readonly string $input,
        public readonly mixed $expectedOutput,
        /** @var \Closure $validator */
        public readonly \Closure $validator,
        public readonly array $metadata = [],
    ) {}
}

/**
 * Evaluation result for a single test
 */
class TestResult
{
    public function __construct(
        public readonly string $testId,
        public readonly string $input,
        public readonly mixed $expected,
        public readonly mixed $actual,
        public readonly bool $passed,
        public readonly float $executionTimeMs,
        public readonly ?string $error = null,
    ) {}
}

/**
 * Evaluation report summarizing all test results
 */
class EvaluationReport
{
    /**
     * @param TestResult[] $results
     */
    public function __construct(
        public readonly array $results,
        public readonly int $total,
        public readonly int $passed,
        public readonly int $failed,
        public readonly float $accuracy,
        public readonly float $totalExecutionTimeMs,
    ) {}
    
    public function display(): void
    {
        echo "╔════════════════════════════════════════════════════════════════╗\n";
        echo "║                    EVALUATION REPORT                           ║\n";
        echo "╚════════════════════════════════════════════════════════════════╝\n\n";
        
        echo "Summary:\n";
        echo "  Total tests: {$this->total}\n";
        echo "  Passed: {$this->passed} ✅\n";
        echo "  Failed: {$this->failed} ❌\n";
        echo "  Accuracy: " . number_format($this->accuracy * 100, 1) . "%\n";
        echo "  Total execution time: " . number_format($this->totalExecutionTimeMs, 2) . "ms\n\n";
        
        echo "Detailed Results:\n";
        echo str_repeat('─', 66) . "\n";
        
        foreach ($this->results as $result) {
            $status = $result->passed ? '✅ PASS' : '❌ FAIL';
            echo "\n{$status} - {$result->testId}\n";
            echo "  Input: {$result->input}\n";
            echo "  Expected: " . $this->formatOutput($result->expected) . "\n";
            echo "  Actual: " . $this->formatOutput($result->actual) . "\n";
            echo "  Time: " . number_format($result->executionTimeMs, 2) . "ms\n";
            
            if ($result->error) {
                echo "  Error: {$result->error}\n";
            }
        }
        
        echo "\n" . str_repeat('═', 66) . "\n";
    }
    
    private function formatOutput(mixed $output): string
    {
        if (is_string($output)) {
            return strlen($output) > 60 
                ? substr($output, 0, 60) . '...' 
                : $output;
        }
        
        return json_encode($output);
    }
}

/**
 * Basic evaluation harness for testing agents
 */
class BasicEvaluationHarness
{
    /** @var TestCase[] */
    private array $testCases = [];
    
    public function addTestCase(TestCase $testCase): self
    {
        $this->testCases[] = $testCase;
        return $this;
    }
    
    public function addMultiple(array $testCases): self
    {
        foreach ($testCases as $testCase) {
            $this->addTestCase($testCase);
        }
        return $this;
    }
    
    public function evaluate(Agent $agent): EvaluationReport
    {
        echo "Running evaluation on {$this->getTestCount()} test cases...\n\n";
        
        $results = [];
        $totalTime = 0;
        
        foreach ($this->testCases as $index => $testCase) {
            echo "Test " . ($index + 1) . "/{$this->getTestCount()}: {$testCase->id}... ";
            
            $startTime = microtime(true);
            $result = $this->runTest($agent, $testCase);
            $executionTime = (microtime(true) - $startTime) * 1000;
            
            $totalTime += $executionTime;
            $results[] = $result;
            
            echo ($result->passed ? '✅' : '❌') . "\n";
        }
        
        $passed = count(array_filter($results, fn($r) => $r->passed));
        $failed = count($results) - $passed;
        $accuracy = count($results) > 0 ? $passed / count($results) : 0;
        
        return new EvaluationReport(
            results: $results,
            total: count($results),
            passed: $passed,
            failed: $failed,
            accuracy: $accuracy,
            totalExecutionTimeMs: $totalTime,
        );
    }
    
    private function runTest(Agent $agent, TestCase $testCase): TestResult
    {
        try {
            $startTime = microtime(true);
            $result = $agent->run($testCase->input);
            $executionTime = (microtime(true) - $startTime) * 1000;
            
            $output = $result->getAnswer();
            $passed = ($testCase->validator)($output, $testCase->expectedOutput);
            
            return new TestResult(
                testId: $testCase->id,
                input: $testCase->input,
                expected: $testCase->expectedOutput,
                actual: $output,
                passed: $passed,
                executionTimeMs: $executionTime,
            );
        } catch (\Throwable $e) {
            return new TestResult(
                testId: $testCase->id,
                input: $testCase->input,
                expected: $testCase->expectedOutput,
                actual: null,
                passed: false,
                executionTimeMs: 0,
                error: $e->getMessage(),
            );
        }
    }
    
    public function getTestCount(): int
    {
        return count($this->testCases);
    }
}

// ============================================================================
// EXAMPLE USAGE
// ============================================================================

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Create an agent to evaluate
echo "Creating math assistant agent...\n\n";

$agent = Agent::create($client)
    ->withSystemPrompt('You are a helpful math assistant. Answer math questions concisely with just the numerical answer when possible.');

// Create evaluation harness
$harness = new BasicEvaluationHarness();

// Define test cases
$testCases = [
    new TestCase(
        id: 'addition_simple',
        input: 'What is 2 + 2?',
        expectedOutput: '4',
        validator: fn($output, $expected) => str_contains(strtolower($output), (string)$expected),
    ),
    new TestCase(
        id: 'multiplication_basic',
        input: 'What is 10 * 5?',
        expectedOutput: '50',
        validator: fn($output, $expected) => str_contains($output, (string)$expected),
    ),
    new TestCase(
        id: 'subtraction_negative',
        input: 'What is 15 - 20?',
        expectedOutput: '-5',
        validator: fn($output, $expected) => str_contains($output, (string)$expected),
    ),
    new TestCase(
        id: 'division_decimal',
        input: 'What is 10 / 4?',
        expectedOutput: '2.5',
        validator: fn($output, $expected) => str_contains($output, (string)$expected),
    ),
    new TestCase(
        id: 'order_of_operations',
        input: 'What is 2 + 3 * 4?',
        expectedOutput: '14',
        validator: fn($output, $expected) => str_contains($output, (string)$expected),
    ),
];

// Add test cases to harness
$harness->addMultiple($testCases);

// Run evaluation
$report = $harness->evaluate($agent);

// Display results
echo "\n";
$report->display();

// Example: Check if accuracy meets threshold
echo "\n";
$threshold = 0.8; // 80% accuracy required
if ($report->accuracy >= $threshold) {
    echo "✅ Agent meets accuracy threshold ({$threshold})\n";
    exit(0);
} else {
    echo "❌ Agent below accuracy threshold ({$threshold})\n";
    exit(1);
}
