<?php

/**
 * Chapter 17: Evaluation Harnesses and QA
 * Example 04: Accuracy Measurement
 * 
 * Demonstrates calculating accuracy metrics including precision, recall,
 * F1 score, and semantic similarity for agent evaluation.
 * 
 * Run: php 04-accuracy-measurement.php
 * Requires: ANTHROPIC_API_KEY environment variable
 */

declare(strict_types=1);

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudePhp\ClaudePhp;

echo "╔════════════════════════════════════════════════════════════════════╗\n";
echo "║  Chapter 17: Accuracy Measurement                                  ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n\n";

if (!getenv('ANTHROPIC_API_KEY')) {
    die("❌ Error: ANTHROPIC_API_KEY environment variable not set\n");
}

/**
 * Accuracy metrics calculator
 */
class AccuracyMetrics
{
    /**
     * Calculate precision: TP / (TP + FP)
     * Of predicted positives, how many were correct?
     */
    public function calculatePrecision(
        int $truePositives,
        int $falsePositives
    ): float {
        $total = $truePositives + $falsePositives;
        return $total > 0 ? $truePositives / $total : 0.0;
    }
    
    /**
     * Calculate recall: TP / (TP + FN)
     * Of actual positives, how many did we find?
     */
    public function calculateRecall(
        int $truePositives,
        int $falseNegatives
    ): float {
        $total = $truePositives + $falseNegatives;
        return $total > 0 ? $truePositives / $total : 0.0;
    }
    
    /**
     * Calculate F1 score: harmonic mean of precision and recall
     */
    public function calculateF1(
        float $precision,
        float $recall
    ): float {
        $sum = $precision + $recall;
        return $sum > 0 ? 2 * ($precision * $recall) / $sum : 0.0;
    }
    
    /**
     * Calculate exact match accuracy
     */
    public function exactMatchAccuracy(array $results): float
    {
        $matches = 0;
        
        foreach ($results as $result) {
            if ($result['actual'] === $result['expected']) {
                $matches++;
            }
        }
        
        return count($results) > 0 ? $matches / count($results) : 0.0;
    }
    
    /**
     * Calculate substring match accuracy (contains)
     */
    public function substringMatchAccuracy(array $results): float
    {
        $matches = 0;
        
        foreach ($results as $result) {
            $actual = strtolower((string) $result['actual']);
            $expected = strtolower((string) $result['expected']);
            
            if (str_contains($actual, $expected)) {
                $matches++;
            }
        }
        
        return count($results) > 0 ? $matches / count($results) : 0.0;
    }
    
    /**
     * Calculate semantic similarity using simple word overlap
     * (In production, use embeddings from claude-php-agent)
     */
    public function semanticSimilarity(string $text1, string $text2): float
    {
        $words1 = $this->tokenize($text1);
        $words2 = $this->tokenize($text2);
        
        if (empty($words1) || empty($words2)) {
            return 0.0;
        }
        
        $intersection = count(array_intersect($words1, $words2));
        $union = count(array_unique(array_merge($words1, $words2)));
        
        return $union > 0 ? $intersection / $union : 0.0;
    }
    
    /**
     * Simple tokenization
     */
    private function tokenize(string $text): array
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        return $words ?: [];
    }
    
    /**
     * Calculate task success rate
     */
    public function taskSuccessRate(array $results): float
    {
        $successful = count(array_filter($results, fn($r) => $r['success'] ?? false));
        return count($results) > 0 ? $successful / count($results) : 0.0;
    }
}

/**
 * Classification evaluator for binary classification tasks
 */
class ClassificationEvaluator
{
    public function __construct(
        private readonly AccuracyMetrics $metrics = new AccuracyMetrics(),
    ) {}
    
    /**
     * Evaluate binary classification results
     */
    public function evaluate(array $results): array
    {
        $truePositives = 0;
        $trueNegatives = 0;
        $falsePositives = 0;
        $falseNegatives = 0;
        
        foreach ($results as $result) {
            $predicted = $result['predicted'] ?? false;
            $actual = $result['actual'] ?? false;
            
            if ($predicted && $actual) {
                $truePositives++;
            } elseif (!$predicted && !$actual) {
                $trueNegatives++;
            } elseif ($predicted && !$actual) {
                $falsePositives++;
            } else { // !$predicted && $actual
                $falseNegatives++;
            }
        }
        
        $precision = $this->metrics->calculatePrecision($truePositives, $falsePositives);
        $recall = $this->metrics->calculateRecall($truePositives, $falseNegatives);
        $f1 = $this->metrics->calculateF1($precision, $recall);
        $accuracy = ($truePositives + $trueNegatives) / count($results);
        
        return [
            'true_positives' => $truePositives,
            'true_negatives' => $trueNegatives,
            'false_positives' => $falsePositives,
            'false_negatives' => $falseNegatives,
            'precision' => $precision,
            'recall' => $recall,
            'f1' => $f1,
            'accuracy' => $accuracy,
        ];
    }
    
    /**
     * Display confusion matrix
     */
    public function displayConfusionMatrix(array $evaluation): void
    {
        echo "Confusion Matrix:\n";
        echo "                  Predicted\n";
        echo "                  Pos    Neg\n";
        echo "         Pos   " . 
             str_pad((string) $evaluation['true_positives'], 6) . " " . 
             str_pad((string) $evaluation['false_negatives'], 6) . "\n";
        echo "Actual   Neg   " . 
             str_pad((string) $evaluation['false_positives'], 6) . " " . 
             str_pad((string) $evaluation['true_negatives'], 6) . "\n\n";
    }
}

/**
 * Comprehensive accuracy report
 */
class AccuracyReport
{
    public function __construct(
        private readonly array $results,
        private readonly AccuracyMetrics $metrics = new AccuracyMetrics(),
    ) {}
    
    public function display(): void
    {
        echo "╔════════════════════════════════════════════════════════════════╗\n";
        echo "║                    ACCURACY REPORT                             ║\n";
        echo "╚════════════════════════════════════════════════════════════════╝\n\n";
        
        // Exact match accuracy
        $exactMatch = $this->metrics->exactMatchAccuracy($this->results);
        echo "Exact Match Accuracy: " . number_format($exactMatch * 100, 1) . "%\n";
        
        // Substring match accuracy
        $substringMatch = $this->metrics->substringMatchAccuracy($this->results);
        echo "Substring Match Accuracy: " . number_format($substringMatch * 100, 1) . "%\n";
        
        // Average semantic similarity
        $totalSimilarity = 0;
        $count = 0;
        
        foreach ($this->results as $result) {
            if (isset($result['actual']) && isset($result['expected'])) {
                $similarity = $this->metrics->semanticSimilarity(
                    (string) $result['actual'],
                    (string) $result['expected']
                );
                $totalSimilarity += $similarity;
                $count++;
            }
        }
        
        $avgSimilarity = $count > 0 ? $totalSimilarity / $count : 0;
        echo "Avg Semantic Similarity: " . number_format($avgSimilarity * 100, 1) . "%\n\n";
        
        // Per-test details
        echo "Per-Test Results:\n";
        echo str_repeat('─', 66) . "\n";
        
        foreach ($this->results as $index => $result) {
            $similarity = $this->metrics->semanticSimilarity(
                (string) ($result['actual'] ?? ''),
                (string) ($result['expected'] ?? '')
            );
            
            $status = $result['actual'] === $result['expected'] ? '✅' : '❌';
            echo "\n{$status} Test " . ($index + 1) . "\n";
            echo "  Expected: " . $this->truncate((string) $result['expected']) . "\n";
            echo "  Actual:   " . $this->truncate((string) $result['actual']) . "\n";
            echo "  Similarity: " . number_format($similarity * 100, 1) . "%\n";
        }
    }
    
    private function truncate(string $text, int $length = 50): string
    {
        return strlen($text) > $length 
            ? substr($text, 0, $length) . '...' 
            : $text;
    }
}

// ============================================================================
// EXAMPLE USAGE: QUESTION ANSWERING
// ============================================================================

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

echo "═══ EXAMPLE 1: QUESTION ANSWERING ACCURACY ═══\n\n";

$agent = Agent::create($client)
    ->withSystemPrompt('You are a helpful assistant. Answer questions concisely.');

// Test cases
$qaTests = [
    [
        'input' => 'What is the capital of France?',
        'expected' => 'Paris',
    ],
    [
        'input' => 'What is 2 + 2?',
        'expected' => '4',
    ],
    [
        'input' => 'What color is the sky?',
        'expected' => 'blue',
    ],
    [
        'input' => 'How many days are in a week?',
        'expected' => '7',
    ],
];

echo "Running Q&A tests...\n\n";

$qaResults = [];
foreach ($qaTests as $index => $test) {
    echo "Test " . ($index + 1) . "... ";
    $result = $agent->run($test['input']);
    $output = $result->getAnswer();
    
    $qaResults[] = [
        'input' => $test['input'],
        'expected' => $test['expected'],
        'actual' => $output,
    ];
    
    echo "✅\n";
}

$qaReport = new AccuracyReport($qaResults);
echo "\n";
$qaReport->display();

// ============================================================================
// EXAMPLE 2: BINARY CLASSIFICATION
// ============================================================================

echo "\n\n═══ EXAMPLE 2: BINARY CLASSIFICATION (SENTIMENT) ═══\n\n";

$sentimentAgent = Agent::create($client)
    ->withSystemPrompt('You are a sentiment classifier. Respond with only "positive" or "negative".');

$sentimentTests = [
    ['text' => 'I love this product!', 'expected' => true], // positive
    ['text' => 'This is terrible and broken.', 'expected' => false], // negative
    ['text' => 'Amazing experience, highly recommend!', 'expected' => true], // positive
    ['text' => 'Worst purchase ever.', 'expected' => false], // negative
    ['text' => 'Pretty good overall.', 'expected' => true], // positive
];

echo "Running sentiment classification...\n\n";

$sentimentResults = [];
foreach ($sentimentTests as $index => $test) {
    echo "Test " . ($index + 1) . "... ";
    $result = $sentimentAgent->run("Classify sentiment: {$test['text']}");
    $output = strtolower($result->getAnswer());
    
    $predicted = str_contains($output, 'positive');
    $actual = $test['expected'];
    
    $sentimentResults[] = [
        'text' => $test['text'],
        'predicted' => $predicted,
        'actual' => $actual,
    ];
    
    echo ($predicted === $actual ? '✅' : '❌') . "\n";
}

$evaluator = new ClassificationEvaluator();
$evaluation = $evaluator->evaluate($sentimentResults);

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║              CLASSIFICATION METRICS                            ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$evaluator->displayConfusionMatrix($evaluation);

echo "Metrics:\n";
echo "  Precision: " . number_format($evaluation['precision'] * 100, 1) . "%\n";
echo "  Recall:    " . number_format($evaluation['recall'] * 100, 1) . "%\n";
echo "  F1 Score:  " . number_format($evaluation['f1'] * 100, 1) . "%\n";
echo "  Accuracy:  " . number_format($evaluation['accuracy'] * 100, 1) . "%\n";

echo "\n✅ Accuracy measurement complete!\n";
