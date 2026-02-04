<?php

/**
 * Chapter 17: Evaluation Harnesses and QA
 * Example 03: Regression Testing
 * 
 * Demonstrates regression testing to catch breaking changes.
 * Compares current agent outputs against baseline results to detect regressions.
 * 
 * Run: php 03-regression-testing.php
 * Requires: ANTHROPIC_API_KEY environment variable
 */

declare(strict_types=1);

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudePhp\ClaudePhp;

echo "╔════════════════════════════════════════════════════════════════════╗\n";
echo "║  Chapter 17: Regression Testing                                    ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n\n";

if (!getenv('ANTHROPIC_API_KEY')) {
    die("❌ Error: ANTHROPIC_API_KEY environment variable not set\n");
}

/**
 * Baseline result for comparison
 */
class BaselineResult
{
    public function __construct(
        public readonly string $testId,
        public readonly string $input,
        public readonly string $output,
        public readonly bool $passed,
        public readonly string $version,
        public readonly int $timestamp,
    ) {}
}

/**
 * Regression comparison result
 */
class RegressionComparison
{
    public function __construct(
        public readonly string $testId,
        public readonly string $status, // 'passed', 'changed', 'failed', 'missing'
        public readonly ?string $baselineOutput,
        public readonly ?string $currentOutput,
        public readonly ?string $message = null,
    ) {}
    
    public function isRegression(): bool
    {
        return in_array($this->status, ['failed', 'changed'], true);
    }
}

/**
 * Regression test report
 */
class RegressionReport
{
    /**
     * @param RegressionComparison[] $comparisons
     */
    public function __construct(
        public readonly array $comparisons,
        public readonly int $passed,
        public readonly int $changed,
        public readonly int $failed,
        public readonly int $missing,
        public readonly string $baselineVersion,
        public readonly string $currentVersion,
    ) {}
    
    public function hasRegressions(): bool
    {
        return $this->changed > 0 || $this->failed > 0;
    }
    
    public function display(): void
    {
        echo "╔════════════════════════════════════════════════════════════════╗\n";
        echo "║                    REGRESSION TEST REPORT                      ║\n";
        echo "╚════════════════════════════════════════════════════════════════╝\n\n";
        
        echo "Version Comparison:\n";
        echo "  Baseline: {$this->baselineVersion}\n";
        echo "  Current: {$this->currentVersion}\n\n";
        
        echo "Summary:\n";
        echo "  ✅ Passed (no change): {$this->passed}\n";
        echo "  ⚠️  Changed (verify): {$this->changed}\n";
        echo "  ❌ Failed (regression): {$this->failed}\n";
        echo "  ⚪ Missing: {$this->missing}\n\n";
        
        if ($this->hasRegressions()) {
            echo "⚠️  REGRESSIONS DETECTED\n\n";
            
            echo "Changed Tests:\n";
            foreach ($this->comparisons as $comparison) {
                if ($comparison->status === 'changed') {
                    echo "  ⚠️  {$comparison->testId}\n";
                    echo "      Baseline: " . $this->truncate($comparison->baselineOutput ?? '') . "\n";
                    echo "      Current:  " . $this->truncate($comparison->currentOutput ?? '') . "\n";
                }
            }
            
            echo "\nFailed Tests:\n";
            foreach ($this->comparisons as $comparison) {
                if ($comparison->status === 'failed') {
                    echo "  ❌ {$comparison->testId}\n";
                    echo "      Message: {$comparison->message}\n";
                }
            }
        } else {
            echo "✅ No regressions detected\n";
        }
    }
    
    private function truncate(string $text, int $length = 60): string
    {
        return strlen($text) > $length 
            ? substr($text, 0, $length) . '...' 
            : $text;
    }
}

/**
 * Regression tester compares current results to baseline
 */
class RegressionTester
{
    public function __construct(
        private readonly string $baselinePath,
    ) {}
    
    /**
     * Create baseline from current test run
     */
    public function createBaseline(
        Agent $agent,
        array $testCases,
        string $version
    ): void {
        echo "Creating baseline (version {$version})...\n\n";
        
        $baseline = [
            'version' => $version,
            'created_at' => date('Y-m-d H:i:s'),
            'timestamp' => time(),
            'results' => [],
        ];
        
        foreach ($testCases as $testCase) {
            echo "  Running: {$testCase['id']}... ";
            
            try {
                $result = $agent->run($testCase['input']);
                $output = $result->getAnswer();
                
                $baseline['results'][] = [
                    'test_id' => $testCase['id'],
                    'input' => $testCase['input'],
                    'output' => $output,
                    'passed' => true,
                ];
                
                echo "✅\n";
            } catch (\Throwable $e) {
                $baseline['results'][] = [
                    'test_id' => $testCase['id'],
                    'input' => $testCase['input'],
                    'output' => null,
                    'passed' => false,
                    'error' => $e->getMessage(),
                ];
                
                echo "❌\n";
            }
        }
        
        // Ensure directory exists
        $dir = dirname($this->baselinePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents(
            $this->baselinePath,
            json_encode($baseline, JSON_PRETTY_PRINT)
        );
        
        echo "\n✅ Baseline saved to: {$this->baselinePath}\n\n";
    }
    
    /**
     * Run regression test comparing to baseline
     */
    public function runRegressionTest(
        Agent $agent,
        array $testCases,
        string $currentVersion
    ): RegressionReport {
        echo "Loading baseline from: {$this->baselinePath}\n";
        
        $baseline = $this->loadBaseline();
        
        echo "Running regression tests (current version: {$currentVersion})...\n\n";
        
        $comparisons = [];
        $passed = 0;
        $changed = 0;
        $failed = 0;
        $missing = 0;
        
        foreach ($testCases as $testCase) {
            echo "  Testing: {$testCase['id']}... ";
            
            $baselineResult = $this->findBaselineResult($baseline, $testCase['id']);
            
            if (!$baselineResult) {
                echo "⚪ (not in baseline)\n";
                $comparisons[] = new RegressionComparison(
                    testId: $testCase['id'],
                    status: 'missing',
                    baselineOutput: null,
                    currentOutput: null,
                    message: 'Test not found in baseline',
                );
                $missing++;
                continue;
            }
            
            try {
                $result = $agent->run($testCase['input']);
                $currentOutput = $result->getAnswer();
                
                // Compare outputs
                if ($currentOutput === $baselineResult['output']) {
                    echo "✅ (passed)\n";
                    $comparisons[] = new RegressionComparison(
                        testId: $testCase['id'],
                        status: 'passed',
                        baselineOutput: $baselineResult['output'],
                        currentOutput: $currentOutput,
                    );
                    $passed++;
                } else {
                    echo "⚠️  (changed)\n";
                    $comparisons[] = new RegressionComparison(
                        testId: $testCase['id'],
                        status: 'changed',
                        baselineOutput: $baselineResult['output'],
                        currentOutput: $currentOutput,
                        message: 'Output differs from baseline',
                    );
                    $changed++;
                }
            } catch (\Throwable $e) {
                echo "❌ (failed)\n";
                $comparisons[] = new RegressionComparison(
                    testId: $testCase['id'],
                    status: 'failed',
                    baselineOutput: $baselineResult['output'],
                    currentOutput: null,
                    message: $e->getMessage(),
                );
                $failed++;
            }
        }
        
        return new RegressionReport(
            comparisons: $comparisons,
            passed: $passed,
            changed: $changed,
            failed: $failed,
            missing: $missing,
            baselineVersion: $baseline['version'],
            currentVersion: $currentVersion,
        );
    }
    
    private function loadBaseline(): array
    {
        if (!file_exists($this->baselinePath)) {
            throw new \RuntimeException("Baseline not found: {$this->baselinePath}");
        }
        
        $baseline = json_decode(file_get_contents($this->baselinePath), true);
        
        if (!$baseline) {
            throw new \RuntimeException("Invalid baseline JSON");
        }
        
        return $baseline;
    }
    
    private function findBaselineResult(array $baseline, string $testId): ?array
    {
        foreach ($baseline['results'] as $result) {
            if ($result['test_id'] === $testId) {
                return $result;
            }
        }
        
        return null;
    }
}

// ============================================================================
// EXAMPLE USAGE
// ============================================================================

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Define test cases
$testCases = [
    [
        'id' => 'capitals_01',
        'input' => 'What is the capital of France?',
    ],
    [
        'id' => 'capitals_02',
        'input' => 'What is the capital of Japan?',
    ],
    [
        'id' => 'math_01',
        'input' => 'What is 25 * 4?',
    ],
    [
        'id' => 'logic_01',
        'input' => 'If all roses are flowers, and some flowers fade, can we conclude that some roses fade?',
    ],
];

// Create regression tester
$baselinePath = __DIR__ . '/baseline-v1.0.json';
$tester = new RegressionTester($baselinePath);

// Step 1: Create baseline (first run)
echo "═══ STEP 1: CREATE BASELINE ═══\n\n";

$agentV1 = Agent::create($client)
    ->withSystemPrompt('You are a helpful assistant. Answer questions concisely and accurately.');

$tester->createBaseline($agentV1, $testCases, 'v1.0');

// Step 2: Test with same agent (should have no regressions)
echo "\n═══ STEP 2: TEST AGAINST BASELINE (NO CHANGES) ═══\n\n";

$report = $tester->runRegressionTest($agentV1, $testCases, 'v1.0-test');

echo "\n";
$report->display();

// Step 3: Simulate a change that causes regression
echo "\n\n═══ STEP 3: TEST WITH MODIFIED AGENT ═══\n\n";

$agentV2 = Agent::create($client)
    ->withSystemPrompt('You are a helpful assistant. Give detailed, comprehensive answers with context and explanations.');

$report2 = $tester->runRegressionTest($agentV2, $testCases, 'v1.1-modified');

echo "\n";
$report2->display();

// Check for regressions
echo "\n";
if ($report2->hasRegressions()) {
    echo "⚠️  Regressions detected! Review changes before deploying.\n";
    exit(1);
} else {
    echo "✅ No regressions! Safe to deploy.\n";
    exit(0);
}
