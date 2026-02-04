<?php

/**
 * Chapter 17: Evaluation Harnesses and QA
 * Example 02: Golden Test Sets
 * 
 * Demonstrates creating and using golden test sets with known correct answers.
 * Shows how to load tests from JSON, organize by category, and validate results.
 * 
 * Run: php 02-golden-test-sets.php
 * Requires: ANTHROPIC_API_KEY environment variable
 */

declare(strict_types=1);

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudePhp\ClaudePhp;

echo "╔════════════════════════════════════════════════════════════════════╗\n";
echo "║  Chapter 17: Golden Test Sets                                      ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n\n";

if (!getenv('ANTHROPIC_API_KEY')) {
    die("❌ Error: ANTHROPIC_API_KEY environment variable not set\n");
}

/**
 * Golden test with ground truth answer
 */
class GoldenTest
{
    public function __construct(
        public readonly string $id,
        public readonly string $input,
        public readonly mixed $expected,
        public readonly array $categories,
        public readonly string $difficulty,
        public readonly array $metadata = [],
    ) {}
    
    public function hasCategory(string $category): bool
    {
        return in_array($category, $this->categories, true);
    }
}

/**
 * Golden test set with versioning and persistence
 */
class GoldenTestSet
{
    private string $version = '1.0';
    /** @var GoldenTest[] */
    private array $tests = [];
    
    public function setVersion(string $version): self
    {
        $this->version = $version;
        return $this;
    }
    
    public function addTest(GoldenTest $test): self
    {
        $this->tests[] = $test;
        return $this;
    }
    
    public function getTests(): array
    {
        return $this->tests;
    }
    
    public function getTestsByCategory(string $category): array
    {
        return array_filter(
            $this->tests,
            fn($test) => $test->hasCategory($category)
        );
    }
    
    public function getTestsByDifficulty(string $difficulty): array
    {
        return array_filter(
            $this->tests,
            fn($test) => $test->difficulty === $difficulty
        );
    }
    
    public function getCount(): int
    {
        return count($this->tests);
    }
    
    public function getCategories(): array
    {
        $categories = [];
        foreach ($this->tests as $test) {
            $categories = array_merge($categories, $test->categories);
        }
        return array_unique($categories);
    }
    
    /**
     * Load test set from JSON file
     */
    public static function loadFromJson(string $path): self
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("Test set not found: {$path}");
        }
        
        $data = json_decode(file_get_contents($path), true);
        
        if (!$data) {
            throw new \RuntimeException("Invalid JSON in test set: {$path}");
        }
        
        $testSet = new self();
        $testSet->setVersion($data['version'] ?? '1.0');
        
        foreach ($data['tests'] ?? [] as $testData) {
            $testSet->addTest(new GoldenTest(
                id: $testData['id'],
                input: $testData['input'],
                expected: $testData['expected'],
                categories: $testData['categories'] ?? [],
                difficulty: $testData['difficulty'] ?? 'medium',
                metadata: $testData['metadata'] ?? [],
            ));
        }
        
        return $testSet;
    }
    
    /**
     * Save test set to JSON file
     */
    public function saveToJson(string $path): void
    {
        $data = [
            'version' => $this->version,
            'created_at' => date('Y-m-d H:i:s'),
            'test_count' => count($this->tests),
            'categories' => $this->getCategories(),
            'tests' => array_map(
                fn($test) => [
                    'id' => $test->id,
                    'input' => $test->input,
                    'expected' => $test->expected,
                    'categories' => $test->categories,
                    'difficulty' => $test->difficulty,
                    'metadata' => $test->metadata,
                ],
                $this->tests
            ),
        ];
        
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    }
}

/**
 * Validator for golden test results
 */
class GoldenTestValidator
{
    /**
     * Exact string match
     */
    public function exactMatch(string $actual, string $expected): bool
    {
        return $actual === $expected;
    }
    
    /**
     * Contains expected value
     */
    public function contains(string $actual, string $expected): bool
    {
        return str_contains(strtolower($actual), strtolower($expected));
    }
    
    /**
     * Numeric comparison with tolerance
     */
    public function numericMatch(string $actual, float $expected, float $tolerance = 0.01): bool
    {
        // Extract number from response
        preg_match('/-?\d+\.?\d*/', $actual, $matches);
        
        if (empty($matches)) {
            return false;
        }
        
        $actualNumber = (float) $matches[0];
        return abs($actualNumber - $expected) <= $tolerance;
    }
    
    /**
     * JSON structure match
     */
    public function jsonMatch(string $actual, array $expected): bool
    {
        $actualData = json_decode($actual, true);
        
        if (!$actualData) {
            return false;
        }
        
        return $this->arrayMatch($actualData, $expected);
    }
    
    /**
     * Recursive array comparison
     */
    private function arrayMatch(array $actual, array $expected): bool
    {
        foreach ($expected as $key => $value) {
            if (!isset($actual[$key])) {
                return false;
            }
            
            if (is_array($value)) {
                if (!is_array($actual[$key])) {
                    return false;
                }
                if (!$this->arrayMatch($actual[$key], $value)) {
                    return false;
                }
            } else {
                if ($actual[$key] !== $value) {
                    return false;
                }
            }
        }
        
        return true;
    }
}

/**
 * Evaluator for golden test sets
 */
class GoldenTestEvaluator
{
    public function __construct(
        private readonly GoldenTestValidator $validator = new GoldenTestValidator(),
    ) {}
    
    public function evaluate(Agent $agent, GoldenTestSet $testSet): array
    {
        echo "Evaluating {$testSet->getCount()} golden tests...\n\n";
        
        $results = [];
        $passed = 0;
        
        foreach ($testSet->getTests() as $index => $test) {
            echo "Test " . ($index + 1) . "/{$testSet->getCount()}: {$test->id} ";
            
            try {
                $result = $agent->run($test->input);
                $output = $result->getAnswer();
                
                // Determine validation method based on expected type
                $isValid = match (gettype($test->expected)) {
                    'string' => $this->validator->contains($output, $test->expected),
                    'integer', 'double' => $this->validator->numericMatch($output, (float) $test->expected),
                    'array' => $this->validator->jsonMatch($output, $test->expected),
                    default => false,
                };
                
                if ($isValid) {
                    echo "✅\n";
                    $passed++;
                }  else {
                    echo "❌\n";
                }
                
                $results[] = [
                    'test_id' => $test->id,
                    'input' => $test->input,
                    'expected' => $test->expected,
                    'actual' => $output,
                    'passed' => $isValid,
                    'categories' => $test->categories,
                    'difficulty' => $test->difficulty,
                ];
            } catch (\Throwable $e) {
                echo "❌ (Error)\n";
                $results[] = [
                    'test_id' => $test->id,
                    'input' => $test->input,
                    'expected' => $test->expected,
                    'actual' => null,
                    'passed' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }
        
        $accuracy = count($results) > 0 ? $passed / count($results) : 0;
        
        echo "\n";
        echo "Results: {$passed}/" . count($results) . " passed (" . 
             number_format($accuracy * 100, 1) . "% accuracy)\n";
        
        return [
            'total' => count($results),
            'passed' => $passed,
            'failed' => count($results) - $passed,
            'accuracy' => $accuracy,
            'results' => $results,
        ];
    }
}

// ============================================================================
// EXAMPLE USAGE
// ============================================================================

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Create agent
echo "Creating math and reasoning agent...\n\n";

$agent = Agent::create($client)
    ->withSystemPrompt('You are a helpful assistant that answers questions accurately and concisely.');

// Create golden test set
$testSet = new GoldenTestSet();
$testSet->setVersion('1.0');

// Add math tests
$testSet->addTest(new GoldenTest(
    id: 'math_01',
    input: 'What is 15 + 27?',
    expected: '42',
    categories: ['math', 'addition'],
    difficulty: 'easy',
));

$testSet->addTest(new GoldenTest(
    id: 'math_02',
    input: 'What is 144 / 12?',
    expected: '12',
    categories: ['math', 'division'],
    difficulty: 'easy',
));

$testSet->addTest(new GoldenTest(
    id: 'math_03',
    input: 'What is the square root of 64?',
    expected: '8',
    categories: ['math', 'roots'],
    difficulty: 'medium',
));

// Add reasoning tests
$testSet->addTest(new GoldenTest(
    id: 'reasoning_01',
    input: 'If a train travels 60 miles in 1 hour, how far does it travel in 3 hours?',
    expected: '180',
    categories: ['reasoning', 'math'],
    difficulty: 'medium',
));

// Add logic tests
$testSet->addTest(new GoldenTest(
    id: 'logic_01',
    input: 'Is the statement "All cats are animals" true or false?',
    expected: 'true',
    categories: ['logic'],
    difficulty: 'easy',
));

echo "Created test set with {$testSet->getCount()} tests\n";
echo "Categories: " . implode(', ', $testSet->getCategories()) . "\n\n";

// Save test set to JSON
$testSetPath = __DIR__ . '/golden-tests.json';
$testSet->saveToJson($testSetPath);
echo "Saved test set to: {$testSetPath}\n\n";

// Load test set from JSON
$loadedTestSet = GoldenTestSet::loadFromJson($testSetPath);
echo "Loaded test set: version {$loadedTestSet->getCount()} tests\n\n";

// Run evaluation
$evaluator = new GoldenTestEvaluator();
$results = $evaluator->evaluate($agent, $loadedTestSet);

// Display detailed results
echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║                    GOLDEN TEST RESULTS                         ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "Overall:\n";
echo "  Accuracy: " . number_format($results['accuracy'] * 100, 1) . "%\n";
echo "  Passed: {$results['passed']}/{$results['total']}\n\n";

// Group by category
$byCategory = [];
foreach ($results['results'] as $result) {
    foreach ($result['categories'] as $category) {
        if (!isset($byCategory[$category])) {
            $byCategory[$category] = ['passed' => 0, 'total' => 0];
        }
        $byCategory[$category]['total']++;
        if ($result['passed']) {
            $byCategory[$category]['passed']++;
        }
    }
}

echo "By Category:\n";
foreach ($byCategory as $category => $stats) {
    $categoryAccuracy = $stats['total'] > 0 
        ? ($stats['passed'] / $stats['total']) * 100 
        : 0;
    echo "  {$category}: {$stats['passed']}/{$stats['total']} ";
    echo "(" . number_format($categoryAccuracy, 1) . "%)\n";
}

echo "\n✅ Golden test evaluation complete!\n";
