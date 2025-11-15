---
title: "35: Fine-tuning Strategies"
description: "Master the decision framework for when to fine-tune vs. prompt engineer vs. RAG. Learn dataset preparation, evaluation metrics, cost-benefit analysis, and production deployment strategies."
series: "claude-php-developers"
chapter: 35
order: 35
difficulty: "Expert"
prerequisites:
  - "Completed Chapters 1-34"
  - "Understanding of ML concepts"
  - "Experience with production AI systems"
  - "Knowledge of evaluation metrics"
---

![35: Fine-tuning Strategies](/images/claude-php/chapter-35-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 35</span>
</div>

# Chapter 35: Fine-tuning Strategies

## Overview

Fine-tuning adapts a pre-trained model to your specific use case by training it on your data. However, it's not always the right choice. This chapter teaches you to make informed decisions about when fine-tuning makes sense versus prompt engineering, RAG, or other approaches.

You'll learn to prepare high-quality training datasets, evaluate fine-tuned models, calculate ROI, and deploy fine-tuned models in production. We'll cover the complete lifecycle from initial assessment through deployment and monitoring.

**What You'll Build**: A decision framework for model customization, dataset preparation tools, evaluation pipelines, and deployment strategies for fine-tuned models.

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapters 1-34** (Full series foundation)
- ✓ **ML concept understanding** for training and evaluation
- ✓ **Production AI experience** for deployment
- ✓ **Evaluation metrics knowledge** for assessment

**Estimated Time**: 90-120 minutes

## Decision Framework

```php
<?php
# filename: src/FineTuning/DecisionFramework.php
declare(strict_types=1);

namespace App\FineTuning;

class DecisionFramework
{
    /**
     * Analyze whether fine-tuning is appropriate
     */
    public function analyze(UseCase $useCase): DecisionReport
    {
        $scores = [
            'prompt_engineering' => $this->scorePromptEngineering($useCase),
            'rag' => $this->scoreRAG($useCase),
            'fine_tuning' => $this->scoreFineTuning($useCase),
            'hybrid' => $this->scoreHybrid($useCase)
        ];

        // Determine recommendation
        arsort($scores);
        $recommendation = array_key_first($scores);

        return new DecisionReport(
            useCase: $useCase,
            scores: $scores,
            recommendation: $recommendation,
            reasoning: $this->generateReasoning($useCase, $scores),
            considerations: $this->getConsiderations($useCase)
        );
    }

    private function scorePromptEngineering(UseCase $useCase): float
    {
        $score = 0.5; // Base score

        // Factors that favor prompt engineering
        if ($useCase->taskComplexity === 'simple') {
            $score += 0.3;
        }

        if ($useCase->dataAvailability === 'low') {
            $score += 0.2;
        }

        if ($useCase->budgetConstraint === 'low') {
            $score += 0.2;
        }

        if ($useCase->timeToMarket === 'urgent') {
            $score += 0.3;
        }

        if ($useCase->requiresFlexibility) {
            $score += 0.2;
        }

        return min(1.0, $score);
    }

    private function scoreRAG(UseCase $useCase): float
    {
        $score = 0.5;

        // Factors that favor RAG
        if ($useCase->requiresFactualAccuracy) {
            $score += 0.3;
        }

        if ($useCase->hasKnowledgeBase) {
            $score += 0.3;
        }

        if ($useCase->dataChangesFrequently) {
            $score += 0.2;
        }

        if ($useCase->requiresCitations) {
            $score += 0.2;
        }

        if ($useCase->domainSpecific) {
            $score += 0.1;
        }

        return min(1.0, $score);
    }

    private function scoreFineTuning(UseCase $useCase): float
    {
        $score = 0.3; // Lower base score (higher barrier)

        // Factors that favor fine-tuning
        if ($useCase->dataAvailability === 'high' && $useCase->dataQuality === 'high') {
            $score += 0.4;
        }

        if ($useCase->taskComplexity === 'complex') {
            $score += 0.2;
        }

        if ($useCase->requiresConsistency) {
            $score += 0.2;
        }

        if ($useCase->volumePerDay > 10000) {
            $score += 0.2; // High volume justifies fine-tuning cost
        }

        if ($useCase->requiresSpecializedBehavior) {
            $score += 0.3;
        }

        if ($useCase->budgetConstraint === 'high') {
            $score += 0.1;
        }

        // Negative factors
        if ($useCase->dataAvailability === 'low') {
            $score -= 0.4;
        }

        if ($useCase->timeToMarket === 'urgent') {
            $score -= 0.2;
        }

        return max(0.0, min(1.0, $score));
    }

    private function scoreHybrid(UseCase $useCase): float
    {
        // Hybrid approaches combine multiple techniques
        $ragScore = $this->scoreRAG($useCase);
        $ftScore = $this->scoreFineTuning($useCase);

        // Hybrid is good when both RAG and fine-tuning have merit
        if ($ragScore > 0.6 && $ftScore > 0.6) {
            return 0.85;
        }

        return ($ragScore + $ftScore) / 2.5;
    }

    private function generateReasoning(UseCase $useCase, array $scores): string
    {
        $lines = [];

        $lines[] = "Analysis for: {$useCase->name}";
        $lines[] = "";
        $lines[] = "Factors considered:";

        if ($useCase->dataAvailability === 'high') {
            $lines[] = "✓ High data availability supports fine-tuning";
        } else {
            $lines[] = "✗ Limited data makes fine-tuning challenging";
        }

        if ($useCase->requiresFactualAccuracy) {
            $lines[] = "✓ Factual accuracy needs suggest RAG";
        }

        if ($useCase->taskComplexity === 'simple') {
            $lines[] = "✓ Simple tasks well-suited for prompt engineering";
        }

        if ($useCase->budgetConstraint === 'low') {
            $lines[] = "! Budget constraints limit fine-tuning viability";
        }

        if ($useCase->volumePerDay > 10000) {
            $lines[] = "✓ High volume justifies fine-tuning investment";
        }

        return implode("\n", $lines);
    }

    private function getConsiderations(UseCase $useCase): array
    {
        return [
            'data_requirements' => $this->getDataRequirements($useCase),
            'cost_estimate' => $this->estimateCost($useCase),
            'timeline' => $this->estimateTimeline($useCase),
            'maintenance' => $this->estimateMaintenance($useCase)
        ];
    }

    private function getDataRequirements(UseCase $useCase): array
    {
        return [
            'minimum_examples' => 500,
            'recommended_examples' => 2000,
            'quality_threshold' => 'High - manually reviewed and validated',
            'format' => 'JSONL with prompt/completion pairs'
        ];
    }

    private function estimateCost(UseCase $useCase): array
    {
        // Rough estimates for different approaches
        $volume = $useCase->volumePerDay;

        return [
            'prompt_engineering' => [
                'setup' => 0,
                'monthly_api' => $volume * 30 * 0.003 // Rough estimate
            ],
            'rag' => [
                'setup' => 500, // Vector DB setup
                'monthly_api' => $volume * 30 * 0.004,
                'monthly_infrastructure' => 200
            ],
            'fine_tuning' => [
                'setup' => 2000, // Dataset prep + training
                'training' => 500,
                'monthly_api' => $volume * 30 * 0.0025, // Lower per-token cost
                'monthly_maintenance' => 500
            ]
        ];
    }

    private function estimateTimeline(UseCase $useCase): array
    {
        return [
            'prompt_engineering' => '1-2 weeks',
            'rag' => '2-4 weeks',
            'fine_tuning' => '4-8 weeks',
            'hybrid' => '6-12 weeks'
        ];
    }

    private function estimateMaintenance(UseCase $useCase): array
    {
        return [
            'prompt_engineering' => 'Low - update prompts as needed',
            'rag' => 'Medium - update knowledge base regularly',
            'fine_tuning' => 'Medium-High - periodic retraining needed',
            'hybrid' => 'High - maintain multiple components'
        ];
    }
}
```

## Dataset Preparation

```php
<?php
# filename: src/FineTuning/DatasetPreparation.php
declare(strict_types=1);

namespace App\FineTuning;

use Anthropic\Anthropic;

class DatasetPreparation
{
    public function __construct(
        private Anthropic $claude
    ) {}

    /**
     * Prepare dataset for fine-tuning
     */
    public function prepare(
        array $rawExamples,
        string $taskType,
        array $options = []
    ): PreparedDataset {
        // Step 1: Validate and clean examples
        $cleaned = $this->cleanExamples($rawExamples);

        // Step 2: Ensure quality
        $quality = $this->assessQuality($cleaned);

        if ($quality['average_score'] < 0.7) {
            throw new \RuntimeException(
                "Dataset quality too low: {$quality['average_score']}"
            );
        }

        // Step 3: Format for fine-tuning
        $formatted = $this->formatExamples($cleaned, $taskType);

        // Step 4: Split into train/validation/test
        $splits = $this->splitDataset($formatted, [
            'train' => $options['train_ratio'] ?? 0.8,
            'validation' => $options['validation_ratio'] ?? 0.1,
            'test' => $options['test_ratio'] ?? 0.1
        ]);

        // Step 5: Generate statistics
        $stats = $this->generateStats($formatted);

        return new PreparedDataset(
            train: $splits['train'],
            validation: $splits['validation'],
            test: $splits['test'],
            stats: $stats,
            quality: $quality
        );
    }

    /**
     * Clean and normalize examples
     */
    private function cleanExamples(array $examples): array
    {
        $cleaned = [];

        foreach ($examples as $example) {
            // Remove duplicates
            $hash = md5(json_encode($example));
            if (isset($cleaned[$hash])) {
                continue;
            }

            // Validate structure
            if (!isset($example['prompt']) || !isset($example['completion'])) {
                continue;
            }

            // Trim whitespace
            $example['prompt'] = trim($example['prompt']);
            $example['completion'] = trim($example['completion']);

            // Skip empty examples
            if (empty($example['prompt']) || empty($example['completion'])) {
                continue;
            }

            // Skip very short examples
            if (strlen($example['prompt']) < 10 || strlen($example['completion']) < 10) {
                continue;
            }

            $cleaned[$hash] = $example;
        }

        return array_values($cleaned);
    }

    /**
     * Assess dataset quality using Claude
     */
    private function assessQuality(array $examples): array
    {
        $sampleSize = min(50, count($examples));
        $sample = array_slice($examples, 0, $sampleSize);

        $scores = [];

        foreach ($sample as $example) {
            $prompt = <<<PROMPT
Assess the quality of this training example:

Prompt: {$example['prompt']}
Completion: {$example['completion']}

Rate on a scale of 0-1:
1. Clarity of prompt (0-1)
2. Quality of completion (0-1)
3. Relevance (0-1)
4. Completeness (0-1)

Return JSON:
{
    "clarity": 0.0,
    "quality": 0.0,
    "relevance": 0.0,
    "completeness": 0.0,
    "overall": 0.0,
    "issues": ["list any issues"]
}
PROMPT;

            $response = $this->claude->messages()->create([
                'model' => 'claude-haiku-4-20250514', // Use fast model for quality checks
                'max_tokens' => 512,
                'temperature' => 0.2,
                'messages' => [[
                    'role' => 'user',
                    'content' => $prompt
                ]]
            ]);

            $jsonText = $response->content[0]->text;
            if (preg_match('/\{.*\}/s', $jsonText, $matches)) {
                $score = json_decode($matches[0], true);
                if ($score) {
                    $scores[] = $score;
                }
            }
        }

        $avgScore = !empty($scores)
            ? array_sum(array_column($scores, 'overall')) / count($scores)
            : 0.0;

        return [
            'sample_size' => count($scores),
            'average_score' => $avgScore,
            'individual_scores' => $scores,
            'recommendation' => $avgScore > 0.8 ? 'Excellent' : ($avgScore > 0.6 ? 'Good' : 'Needs improvement')
        ];
    }

    /**
     * Format examples for fine-tuning
     */
    private function formatExamples(array $examples, string $taskType): array
    {
        $formatted = [];

        foreach ($examples as $example) {
            $formatted[] = [
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $example['prompt']
                    ],
                    [
                        'role' => 'assistant',
                        'content' => $example['completion']
                    ]
                ]
            ];
        }

        return $formatted;
    }

    /**
     * Split dataset into train/validation/test sets
     */
    private function splitDataset(array $examples, array $ratios): array
    {
        shuffle($examples);

        $total = count($examples);
        $trainSize = (int)($total * $ratios['train']);
        $valSize = (int)($total * $ratios['validation']);

        return [
            'train' => array_slice($examples, 0, $trainSize),
            'validation' => array_slice($examples, $trainSize, $valSize),
            'test' => array_slice($examples, $trainSize + $valSize)
        ];
    }

    /**
     * Generate dataset statistics
     */
    private function generateStats(array $examples): array
    {
        $promptLengths = [];
        $completionLengths = [];

        foreach ($examples as $example) {
            $promptLengths[] = strlen($example['messages'][0]['content']);
            $completionLengths[] = strlen($example['messages'][1]['content']);
        }

        return [
            'total_examples' => count($examples),
            'prompt_length' => [
                'min' => min($promptLengths),
                'max' => max($promptLengths),
                'avg' => array_sum($promptLengths) / count($promptLengths),
                'median' => $this->median($promptLengths)
            ],
            'completion_length' => [
                'min' => min($completionLengths),
                'max' => max($completionLengths),
                'avg' => array_sum($completionLengths) / count($completionLengths),
                'median' => $this->median($completionLengths)
            ]
        ];
    }

    private function median(array $values): float
    {
        sort($values);
        $count = count($values);
        $middle = (int)floor($count / 2);

        if ($count % 2 === 0) {
            return ($values[$middle - 1] + $values[$middle]) / 2;
        }

        return $values[$middle];
    }

    /**
     * Export dataset to JSONL format
     */
    public function exportToJSONL(PreparedDataset $dataset, string $outputDir): array
    {
        $files = [];

        foreach (['train', 'validation', 'test'] as $split) {
            $filepath = "{$outputDir}/{$split}.jsonl";
            $handle = fopen($filepath, 'w');

            foreach ($dataset->$split as $example) {
                fwrite($handle, json_encode($example) . "\n");
            }

            fclose($handle);
            $files[$split] = $filepath;
        }

        return $files;
    }
}
```

## Model Evaluation

```php
<?php
# filename: src/FineTuning/ModelEvaluator.php
declare(strict_types=1);

namespace App\FineTuning;

use Anthropic\Anthropic;

class ModelEvaluator
{
    public function __construct(
        private Anthropic $claude
    ) {}

    /**
     * Evaluate model performance on test set
     */
    public function evaluate(
        string $modelId,
        array $testSet,
        array $metrics = ['accuracy', 'relevance', 'quality']
    ): EvaluationReport {
        $results = [];
        $scores = [];

        foreach ($testSet as $i => $example) {
            $prompt = $example['messages'][0]['content'];
            $expected = $example['messages'][1]['content'];

            // Generate completion with fine-tuned model
            $response = $this->claude->messages()->create([
                'model' => $modelId,
                'max_tokens' => 2048,
                'messages' => [[
                    'role' => 'user',
                    'content' => $prompt
                ]]
            ]);

            $actual = $response->content[0]->text;

            // Evaluate this example
            $exampleScores = $this->evaluateExample($prompt, $expected, $actual, $metrics);

            $results[] = [
                'prompt' => $prompt,
                'expected' => $expected,
                'actual' => $actual,
                'scores' => $exampleScores
            ];

            // Aggregate scores
            foreach ($exampleScores as $metric => $score) {
                if (!isset($scores[$metric])) {
                    $scores[$metric] = [];
                }
                $scores[$metric][] = $score;
            }
        }

        // Calculate average scores
        $averageScores = [];
        foreach ($scores as $metric => $values) {
            $averageScores[$metric] = array_sum($values) / count($values);
        }

        return new EvaluationReport(
            modelId: $modelId,
            testSetSize: count($testSet),
            results: $results,
            averageScores: $averageScores,
            overallScore: array_sum($averageScores) / count($averageScores)
        );
    }

    /**
     * Evaluate a single example
     */
    private function evaluateExample(
        string $prompt,
        string $expected,
        string $actual,
        array $metrics
    ): array {
        $scores = [];

        // Use Claude to evaluate
        $evalPrompt = <<<PROMPT
Evaluate this model output:

Prompt: {$prompt}

Expected Output: {$expected}

Actual Output: {$actual}

Rate the following (0-1 scale):
PROMPT;

        foreach ($metrics as $metric) {
            $evalPrompt .= "\n- {$metric}";
        }

        $evalPrompt .= "\n\nReturn JSON with scores for each metric.";

        $response = $this->claude->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 512,
            'temperature' => 0.2,
            'messages' => [[
                'role' => 'user',
                'content' => $evalPrompt
            ]]
        ]);

        $jsonText = $response->content[0]->text;
        if (preg_match('/\{.*\}/s', $jsonText, $matches)) {
            $scores = json_decode($matches[0], true) ?? [];
        }

        return $scores;
    }

    /**
     * Compare multiple models
     */
    public function compare(array $modelIds, array $testSet): ComparisonReport
    {
        $evaluations = [];

        foreach ($modelIds as $modelId) {
            $evaluations[$modelId] = $this->evaluate($modelId, $testSet);
        }

        // Determine winner for each metric
        $winners = [];
        $metrics = array_keys($evaluations[array_key_first($evaluations)]->averageScores);

        foreach ($metrics as $metric) {
            $bestScore = 0;
            $bestModel = null;

            foreach ($evaluations as $modelId => $eval) {
                if ($eval->averageScores[$metric] > $bestScore) {
                    $bestScore = $eval->averageScores[$metric];
                    $bestModel = $modelId;
                }
            }

            $winners[$metric] = [
                'model' => $bestModel,
                'score' => $bestScore
            ];
        }

        return new ComparisonReport(
            evaluations: $evaluations,
            winners: $winners,
            overallWinner: $this->determineOverallWinner($evaluations)
        );
    }

    private function determineOverallWinner(array $evaluations): string
    {
        $bestScore = 0;
        $bestModel = null;

        foreach ($evaluations as $modelId => $eval) {
            if ($eval->overallScore > $bestScore) {
                $bestScore = $eval->overallScore;
                $bestModel = $modelId;
            }
        }

        return $bestModel;
    }
}
```

## Cost-Benefit Analyzer

```php
<?php
# filename: src/FineTuning/CostBenefitAnalyzer.php
declare(strict_types=1);

namespace App\FineTuning;

class CostBenefitAnalyzer
{
    /**
     * Calculate ROI of fine-tuning vs alternatives
     */
    public function analyze(
        int $monthlyVolume,
        float $currentCostPerRequest,
        float $fineTunedCostPerRequest,
        int $setupCost,
        int $monthlyMaintenanceCost,
        float $qualityImprovement
    ): ROIAnalysis {
        $months = 12; // Analyze over 1 year

        // Current approach costs
        $currentMonthlyCost = $monthlyVolume * $currentCostPerRequest;
        $currentYearlyCost = $currentMonthlyCost * $months;

        // Fine-tuned approach costs
        $fineTunedMonthlyCost = ($monthlyVolume * $fineTunedCostPerRequest) + $monthlyMaintenanceCost;
        $fineTunedYearlyCost = $setupCost + ($fineTunedMonthlyCost * $months);

        // Calculate savings
        $yearlySavings = $currentYearlyCost - $fineTunedYearlyCost;
        $breakEvenMonths = $setupCost / ($currentMonthlyCost - $fineTunedMonthlyCost);

        // Factor in quality improvement value
        $qualityValue = $this->estimateQualityValue($monthlyVolume, $qualityImprovement);
        $totalValue = $yearlySavings + $qualityValue;

        return new ROIAnalysis(
            currentYearlyCost: $currentYearlyCost,
            fineTunedYearlyCost: $fineTunedYearlyCost,
            yearlySavings: $yearlySavings,
            qualityValue: $qualityValue,
            totalValue: $totalValue,
            breakEvenMonths: $breakEvenMonths,
            roi: ($totalValue / $setupCost) * 100,
            recommendation: $this->generateRecommendation($totalValue, $breakEvenMonths)
        );
    }

    private function estimateQualityValue(int $volume, float $improvement): float
    {
        // Rough estimate: quality improvement translates to business value
        // This is domain-specific and should be customized
        $valuePerImprovedRequest = 0.50; // Example: $0.50 value per better response
        return $volume * 12 * $improvement * $valuePerImprovedRequest;
    }

    private function generateRecommendation(float $totalValue, float $breakEvenMonths): string
    {
        if ($totalValue > 0 && $breakEvenMonths <= 6) {
            return 'Strongly Recommended - Quick payback with positive ROI';
        }

        if ($totalValue > 0 && $breakEvenMonths <= 12) {
            return 'Recommended - Positive ROI within first year';
        }

        if ($totalValue > 0) {
            return 'Consider - Positive ROI but longer payback period';
        }

        return 'Not Recommended - Negative ROI expected';
    }
}
```

## Complete Example

```php
<?php
# filename: examples/fine-tuning-demo.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;
use App\FineTuning\DecisionFramework;
use App\FineTuning\UseCase;
use App\FineTuning\DatasetPreparation;
use App\FineTuning\CostBenefitAnalyzer;

// Initialize Claude
$claude = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

echo "=== Fine-tuning Strategy Analysis ===\n\n";

// Example 1: Decision Framework
echo "Example 1: Should I Fine-tune?\n";
echo str_repeat('-', 80) . "\n\n";

$useCase = new UseCase(
    name: 'Customer Support Automation',
    taskComplexity: 'complex',
    dataAvailability: 'high',
    dataQuality: 'high',
    volumePerDay: 5000,
    budgetConstraint: 'medium',
    timeToMarket: 'normal',
    requiresFactualAccuracy: true,
    requiresConsistency: true,
    requiresSpecializedBehavior: true,
    hasKnowledgeBase: true,
    dataChangesFrequently: false,
    requiresCitations: false,
    requiresFlexibility: false,
    domainSpecific: true
);

$framework = new DecisionFramework();
$decision = $framework->analyze($useCase);

echo "Recommendation: {$decision->recommendation}\n\n";
echo "Scores:\n";
foreach ($decision->scores as $approach => $score) {
    echo sprintf("  %-20s: %.2f\n", $approach, $score);
}
echo "\nReasoning:\n{$decision->reasoning}\n\n";

// Example 2: Dataset Preparation
echo "\nExample 2: Dataset Preparation\n";
echo str_repeat('-', 80) . "\n\n";

$rawExamples = [
    [
        'prompt' => 'How do I reset my password?',
        'completion' => 'To reset your password: 1) Go to the login page, 2) Click "Forgot Password", 3) Enter your email, 4) Check your email for reset link, 5) Follow the link and create a new password.'
    ],
    [
        'prompt' => 'What are your business hours?',
        'completion' => 'Our customer support team is available Monday-Friday, 9 AM - 6 PM EST. For urgent issues outside these hours, please use our emergency contact line at 1-800-SUPPORT.'
    ],
    // ... more examples ...
];

$prep = new DatasetPreparation($claude);

try {
    $dataset = $prep->prepare($rawExamples, 'question_answering');

    echo "Dataset prepared successfully!\n";
    echo "Total examples: {$dataset->stats['total_examples']}\n";
    echo "Train: " . count($dataset->train) . "\n";
    echo "Validation: " . count($dataset->validation) . "\n";
    echo "Test: " . count($dataset->test) . "\n";
    echo "Quality score: " . number_format($dataset->quality['average_score'], 2) . "\n";

    // Export to JSONL
    $files = $prep->exportToJSONL($dataset, __DIR__ . '/../storage/datasets');
    echo "\nExported to:\n";
    foreach ($files as $split => $filepath) {
        echo "  {$split}: {$filepath}\n";
    }

} catch (\Exception $e) {
    echo "Error: {$e->getMessage()}\n";
}

// Example 3: Cost-Benefit Analysis
echo "\n\nExample 3: Cost-Benefit Analysis\n";
echo str_repeat('-', 80) . "\n\n";

$analyzer = new CostBenefitAnalyzer();

$roi = $analyzer->analyze(
    monthlyVolume: 150000,
    currentCostPerRequest: 0.005,
    fineTunedCostPerRequest: 0.003,
    setupCost: 5000,
    monthlyMaintenanceCost: 500,
    qualityImprovement: 0.15 // 15% improvement
);

echo "ROI Analysis (12 months):\n\n";
echo "Current yearly cost: $" . number_format($roi->currentYearlyCost, 2) . "\n";
echo "Fine-tuned yearly cost: $" . number_format($roi->fineTunedYearlyCost, 2) . "\n";
echo "Yearly savings: $" . number_format($roi->yearlySavings, 2) . "\n";
echo "Quality improvement value: $" . number_format($roi->qualityValue, 2) . "\n";
echo "Total value: $" . number_format($roi->totalValue, 2) . "\n";
echo "Break-even: " . number_format($roi->breakEvenMonths, 1) . " months\n";
echo "ROI: " . number_format($roi->roi, 1) . "%\n\n";
echo "Recommendation: {$roi->recommendation}\n";
```

## Data Structures

```php
<?php
# filename: src/FineTuning/DataStructures.php
declare(strict_types=1);

namespace App\FineTuning;

readonly class UseCase
{
    public function __construct(
        public string $name,
        public string $taskComplexity,
        public string $dataAvailability,
        public string $dataQuality,
        public int $volumePerDay,
        public string $budgetConstraint,
        public string $timeToMarket,
        public bool $requiresFactualAccuracy,
        public bool $requiresConsistency,
        public bool $requiresSpecializedBehavior,
        public bool $hasKnowledgeBase,
        public bool $dataChangesFrequently,
        public bool $requiresCitations,
        public bool $requiresFlexibility,
        public bool $domainSpecific
    ) {}
}

readonly class DecisionReport
{
    public function __construct(
        public UseCase $useCase,
        public array $scores,
        public string $recommendation,
        public string $reasoning,
        public array $considerations
    ) {}
}

readonly class PreparedDataset
{
    public function __construct(
        public array $train,
        public array $validation,
        public array $test,
        public array $stats,
        public array $quality
    ) {}
}

readonly class EvaluationReport
{
    public function __construct(
        public string $modelId,
        public int $testSetSize,
        public array $results,
        public array $averageScores,
        public float $overallScore
    ) {}
}

readonly class ComparisonReport
{
    public function __construct(
        public array $evaluations,
        public array $winners,
        public string $overallWinner
    ) {}
}

readonly class ROIAnalysis
{
    public function __construct(
        public float $currentYearlyCost,
        public float $fineTunedYearlyCost,
        public float $yearlySavings,
        public float $qualityValue,
        public float $totalValue,
        public float $breakEvenMonths,
        public float $roi,
        public string $recommendation
    ) {}
}
```

## Key Takeaways

- ✓ Fine-tuning isn't always the right choice - evaluate alternatives first
- ✓ Prompt engineering is fastest and cheapest for simple tasks
- ✓ RAG is best for factual accuracy and changing knowledge
- ✓ Fine-tuning excels at consistent, specialized behavior
- ✓ High-quality datasets (500+ examples) are critical for success
- ✓ Dataset quality matters more than quantity
- ✓ Evaluate models systematically with multiple metrics
- ✓ Calculate ROI including setup, training, and maintenance costs
- ✓ Consider break-even time and long-term value
- ✓ Hybrid approaches combine strengths of multiple techniques

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="35"
  label="You've mastered fine-tuning strategy and decision-making!"
/>

---

Congratulations! You've completed the Advanced Techniques section of the Claude for PHP Developers series. You now have the knowledge to build sophisticated AI systems with RAG, vector databases, multi-agent coordination, workflow orchestration, and strategic fine-tuning.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 35 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-35)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-35
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php examples/fine-tuning-demo.php
```
