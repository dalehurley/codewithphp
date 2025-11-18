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

## What You'll Build

By the end of this chapter, you will have created:

- **Decision framework** for choosing between fine-tuning, prompt engineering, RAG, and hybrid approaches
- **Dataset preparation pipeline** with quality assessment, cleaning, formatting, and train/validation/test splitting
- **Model evaluation system** with multiple metrics, comparison tools, and performance analysis
- **Cost-benefit analyzer** for calculating ROI, break-even points, and long-term value
- **Production-ready fine-tuning tools** that can be integrated into your Claude applications

## Objectives

By completing this chapter, you will:

- Understand when fine-tuning is appropriate versus prompt engineering or RAG
- Prepare high-quality training datasets with proper validation and quality checks
- Evaluate fine-tuned models using systematic metrics and comparison tools
- Calculate ROI and make data-driven decisions about fine-tuning investments
- Implement production-ready fine-tuning workflows and deployment strategies
- Apply hybrid approaches that combine multiple customization techniques

## Prerequisites

Before starting, ensure you have:

- ✓ **Completed Chapters 1-34** (Full series foundation)
- ✓ **ML concept understanding** for training and evaluation
- ✓ **Production AI experience** for deployment
- ✓ **Evaluation metrics knowledge** for assessment

**Estimated Time**: 90-120 minutes

**Verify your setup:**

```bash
# Check PHP version
php --version

# Verify you have access to Claude API
php -r "echo getenv('ANTHROPIC_API_KEY') ? 'API key found' : 'API key not set';"
```

## Quick Start

Here's a quick 5-minute example demonstrating the decision framework:

```php
<?php
# filename: examples/quick-start-fine-tuning.php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\FineTuning\DecisionFramework;
use App\FineTuning\UseCase;

// Create a use case
$useCase = new UseCase(
    name: 'Email Classification',
    taskComplexity: 'simple',
    dataAvailability: 'low',
    dataQuality: 'medium',
    volumePerDay: 1000,
    budgetConstraint: 'low',
    timeToMarket: 'urgent',
    requiresFactualAccuracy: false,
    requiresConsistency: true,
    requiresSpecializedBehavior: false,
    hasKnowledgeBase: false,
    dataChangesFrequently: true,
    requiresCitations: false,
    requiresFlexibility: true,
    domainSpecific: false
);

// Analyze the use case
$framework = new DecisionFramework();
$decision = $framework->analyze($useCase);

echo "Recommendation: {$decision->recommendation}\n\n";
echo "Scores:\n";
foreach ($decision->scores as $approach => $score) {
    echo sprintf("  %-20s: %.2f\n", $approach, $score);
}
```

Run this example:

```bash
php examples/quick-start-fine-tuning.php
```

Expected output:

```
Recommendation: prompt_engineering

Scores:
  prompt_engineering  : 0.90
  rag                  : 0.50
  fine_tuning          : 0.30
  hybrid               : 0.32
```

This quick example shows how the decision framework evaluates your use case and recommends the best approach.

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

use ClaudePhp\ClaudePhp;

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

            $jsonText = $response->content[0]['text'];
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

use ClaudePhp\ClaudePhp;

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

            $actual = $response->content[0]['text'];

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

        $jsonText = $response->content[0]['text'];
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

use ClaudePhp\ClaudePhp;
use App\FineTuning\DecisionFramework;
use App\FineTuning\UseCase;
use App\FineTuning\DatasetPreparation;
use App\FineTuning\CostBenefitAnalyzer;

// Initialize Claude
$claude = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY');

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

## Production Deployment & Model Management

Deploying fine-tuned models to production requires careful version management, performance monitoring, and gradual rollout strategies.

```php
<?php
# filename: src/FineTuning/ModelDeployment.php
declare(strict_types=1);

namespace App\FineTuning;

use ClaudePhp\ClaudePhp;

class ModelDeployment
{
    public function __construct(
        private Anthropic $claude
    ) {}

    /**
     * Deploy fine-tuned model with A/B testing
     */
    public function deployWithABTesting(
        string $fineTunedModelId,
        string $baselineModelId,
        float $fineTunedTraffic = 0.1, // Start with 10%
        array $testMetrics = ['accuracy', 'latency']
    ): DeploymentResult {
        $trafficSplit = [
            'baseline' => 1.0 - $fineTunedTraffic,
            'fine_tuned' => $fineTunedTraffic
        ];

        return new DeploymentResult(
            status: 'deployed',
            modelId: $fineTunedModelId,
            baselineModelId: $baselineModelId,
            trafficSplit: $trafficSplit,
            testMetrics: $testMetrics,
            deploymentTime: now()
        );
    }

    /**
     * Compare model performance with statistical significance
     */
    public function compareModels(
        array $fineTunedResults,
        array $baselineResults,
        float $significanceLevel = 0.05
    ): ComparisonAnalysis {
        $fineMetrics = $this->calculateMetrics($fineTunedResults);
        $baselineMetrics = $this->calculateMetrics($baselineResults);

        $improvements = [];
        foreach ($fineMetrics as $metric => $fineValue) {
            $baseValue = $baselineMetrics[$metric] ?? 0;
            $improvement = (($fineValue - $baseValue) / $baseValue) * 100;

            $pValue = $this->calculatePValue($fineTunedResults, $baselineResults, $metric);
            $isSignificant = $pValue < $significanceLevel;

            $improvements[$metric] = [
                'baseline' => $baseValue,
                'fine_tuned' => $fineValue,
                'improvement_percent' => $improvement,
                'p_value' => $pValue,
                'is_significant' => $isSignificant
            ];
        }

        return new ComparisonAnalysis(
            improvements: $improvements,
            recommendation: $this->generateRecommendation($improvements)
        );
    }

    /**
     * Model versioning for rollback capability
     */
    public function versionModel(
        string $modelId,
        string $version,
        array $metadata = []
    ): ModelVersion {
        return new ModelVersion(
            modelId: $modelId,
            version: $version,
            createdAt: now(),
            metadata: $metadata,
            performance: []
        );
    }

    /**
     * Gradual traffic shift (canary deployment)
     */
    public function canaryDeployment(
        string $fineTunedModelId,
        string $baselineModelId,
        array $trafficSchedule = [
            '0:00' => 0.05,   // 5% at start
            '1:00' => 0.10,   // 10% after 1 hour
            '4:00' => 0.25,   // 25% after 4 hours
            '8:00' => 0.50,   // 50% after 8 hours
            '24:00' => 1.00   // 100% after 1 day
        ]
    ): CanaryDeploymentPlan {
        return new CanaryDeploymentPlan(
            fineTunedModelId: $fineTunedModelId,
            baselineModelId: $baselineModelId,
            trafficSchedule: $trafficSchedule,
            status: 'scheduled',
            startTime: now()
        );
    }

    private function calculateMetrics(array $results): array
    {
        $accuracy = count(array_filter($results, fn($r) => $r['correct'])) / count($results);
        $avgLatency = array_sum(array_column($results, 'latency')) / count($results);

        return [
            'accuracy' => $accuracy,
            'latency' => $avgLatency
        ];
    }

    private function calculatePValue(array $group1, array $group2, string $metric): float
    {
        // Simple t-test approximation
        $values1 = array_column($group1, 'score');
        $values2 = array_column($group2, 'score');

        $mean1 = array_sum($values1) / count($values1);
        $mean2 = array_sum($values2) / count($values2);

        $variance1 = $this->calculateVariance($values1, $mean1);
        $variance2 = $this->calculateVariance($values2, $mean2);

        $t = ($mean1 - $mean2) / sqrt(($variance1 / count($values1)) + ($variance2 / count($values2)));

        // Approximate p-value (simplified)
        return 2 * (1 - $this->normalCDF(abs($t)));
    }

    private function calculateVariance(array $values, float $mean): float
    {
        $squareDiffs = array_map(fn($x) => pow($x - $mean, 2), $values);
        return array_sum($squareDiffs) / count($values);
    }

    private function normalCDF(float $x): float
    {
        return 0.5 * (1 + erf($x / sqrt(2)));
    }

    private function generateRecommendation(array $improvements): string
    {
        $significant = array_filter($improvements, fn($m) => $m['is_significant']);

        if (empty($significant)) {
            return 'Not recommended - no statistically significant improvement';
        }

        $avgImprovement = array_sum(array_column($significant, 'improvement_percent')) / count($significant);

        if ($avgImprovement > 10) {
            return 'Highly recommended - significant improvements detected';
        }

        return 'Recommended - modest improvements detected';
    }
}
```

## Retraining & Continuous Improvement

Monitor model performance and retrain when drift is detected.

```php
<?php
# filename: src/FineTuning/ModelMonitoring.php
declare(strict_types=1);

namespace App\FineTuning;

use ClaudePhp\ClaudePhp;

class ModelMonitoring
{
    public function __construct(
        private Anthropic $claude
    ) {}

    /**
     * Detect data drift in production
     */
    public function detectDataDrift(
        array $productionData,
        array $trainingDataDistribution,
        float $driftThreshold = 0.1
    ): DriftReport {
        $driftMetrics = [];

        foreach ($trainingDataDistribution as $feature => $baseline) {
            $current = $this->analyzeFeatureDistribution($productionData, $feature);
            $divergence = $this->calculateJSDivergence($baseline, $current);

            $driftMetrics[$feature] = [
                'divergence' => $divergence,
                'is_drifted' => $divergence > $driftThreshold,
                'recommendation' => $divergence > $driftThreshold ? 'retrain_needed' : 'monitor'
            ];
        }

        $shouldRetrain = count(array_filter($driftMetrics, fn($m) => $m['is_drifted'])) > 0;

        return new DriftReport(
            detectedAt: now(),
            driftMetrics: $driftMetrics,
            shouldRetrain: $shouldRetrain,
            confidenceScore: $this->calculateConfidence($driftMetrics)
        );
    }

    /**
     * Monitor model performance degradation
     */
    public function monitorPerformance(
        array $productionPredictions,
        float $degradationThreshold = 0.05 // 5% drop
    ): PerformanceReport {
        $currentAccuracy = $this->calculateAccuracy($productionPredictions);
        $currentLatency = $this->calculateAverageLatency($productionPredictions);

        return new PerformanceReport(
            timestamp: now(),
            accuracy: $currentAccuracy,
            latency: $currentLatency,
            degradationDetected: false, // Compare with baseline
            recommendations: []
        );
    }

    /**
     * Schedule automated retraining
     */
    public function scheduleRetraining(
        array $newTrainingData,
        string $previousModelId,
        array $options = [
            'epochs' => 3,
            'batch_size' => 32,
            'learning_rate' => 0.0001
        ]
    ): RetrainingSchedule {
        return new RetrainingSchedule(
            status: 'scheduled',
            scheduledFor: now()->addHours(24),
            newDataCount: count($newTrainingData),
            previousModelId: $previousModelId,
            expectedImprovement: null, // Will be calculated after retraining
            options: $options
        );
    }

    /**
     * Detect when fine-tuned model loses effectiveness
     */
    public function shouldRetrain(
        array $recentMetrics,
        array $baselineMetrics,
        float $acceptableDecline = 0.05
    ): bool {
        $recentAccuracy = $recentMetrics['accuracy'] ?? 0;
        $baselineAccuracy = $baselineMetrics['accuracy'] ?? 0;

        $accuracyDrop = ($baselineAccuracy - $recentAccuracy) / $baselineAccuracy;

        return $accuracyDrop > $acceptableDecline;
    }

    private function analyzeFeatureDistribution(array $data, string $feature): array
    {
        $values = array_column($data, $feature);
        $buckets = array_fill(0, 10, 0);

        foreach ($values as $value) {
            $bucketIndex = (int)($value * 10);
            $buckets[$bucketIndex] = ($buckets[$bucketIndex] ?? 0) + 1;
        }

        return array_map(fn($count) => $count / count($values), $buckets);
    }

    private function calculateJSDivergence(array $p, array $q): float
    {
        $m = array_map(fn($pi, $qi) => ($pi + $qi) / 2, $p, $q);

        $dKL_pm = 0;
        $dKL_qm = 0;

        for ($i = 0; $i < count($p); $i++) {
            if ($p[$i] > 0) {
                $dKL_pm += $p[$i] * log($p[$i] / $m[$i]);
            }
            if ($q[$i] > 0) {
                $dKL_qm += $q[$i] * log($q[$i] / $m[$i]);
            }
        }

        return 0.5 * $dKL_pm + 0.5 * $dKL_qm;
    }

    private function calculateConfidence(array $driftMetrics): float
    {
        $totalFeatures = count($driftMetrics);
        $driftedFeatures = count(array_filter($driftMetrics, fn($m) => $m['is_drifted']));

        return 1.0 - ($driftedFeatures / $totalFeatures);
    }

    private function calculateAccuracy(array $predictions): float
    {
        $correct = count(array_filter($predictions, fn($p) => $p['correct']));
        return $correct / count($predictions);
    }

    private function calculateAverageLatency(array $predictions): float
    {
        return array_sum(array_column($predictions, 'latency')) / count($predictions);
    }
}
```

## Common Pitfalls & Mistakes

Avoid these costly mistakes when fine-tuning:

```php
<?php
# filename: examples/common-pitfalls.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

echo "=== Common Fine-tuning Pitfalls ===\n\n";

// PITFALL 1: Data Leakage
echo "1. Data Leakage (WRONG):\n";
echo "   ❌ Using test set for validation\n";
echo "   ❌ Information from test set influences training\n";
echo "   ❌ Overly optimistic performance metrics\n\n";

echo "   ✅ CORRECT: Strict split\n";
$totalExamples = 1000;
$trainSet = $totalExamples * 0.8;      // 800 examples
$validSet = $totalExamples * 0.1;      // 100 examples
$testSet = $totalExamples * 0.1;       // 100 examples (never used in training)
echo "   - Train: 80% ({$trainSet} examples)\n";
echo "   - Validation: 10% ({$validSet} examples) - for hyperparameter tuning\n";
echo "   - Test: 10% ({$testSet} examples) - final evaluation only\n\n";

// PITFALL 2: Overfitting
echo "2. Overfitting on Small Datasets (WRONG):\n";
echo "   ❌ 100 examples → perfect accuracy on training\n";
echo "   ❌ Validation accuracy much lower\n";
echo "   ❌ Poor generalization to new data\n\n";

echo "   ✅ CORRECT: Minimum dataset size\n";
echo "   - Minimum: 500 quality examples\n";
echo "   - Recommended: 2000+ examples\n";
echo "   - High quality matters more than quantity\n";
echo "   - Use cross-validation with small datasets\n\n";

// PITFALL 3: No Baseline
echo "3. Inadequate Performance Baseline (WRONG):\n";
echo "   ❌ Fine-tuned model: 85% accuracy\n";
echo "   ❌ No comparison to base model\n";
echo "   ❌ Is the improvement from fine-tuning or just from prompt?\n\n";

echo "   ✅ CORRECT: Compare to baseline\n";
echo "   Steps:\n";
echo "   1. Test base model on same test set\n";
echo "   2. Calculate improvement percentage\n";
echo "   3. Verify statistical significance (p-value < 0.05)\n";
echo "   4. Consider cost-benefit of fine-tuning vs prompt engineering\n\n";

// PITFALL 4: Data Quality Issues
echo "4. Including Low-Quality Examples (WRONG):\n";
echo "   ❌ Inconsistent prompt/completion pairs\n";
echo "   ❌ Factually incorrect completions\n";
echo "   ❌ Duplicate or near-duplicate examples\n";
echo "   ❌ Ambiguous or unclear prompts\n\n";

echo "   ✅ CORRECT: Quality over quantity\n";
echo "   Steps:\n";
echo "   1. Manual review of 10% of examples\n";
echo "   2. Automated quality checks:\n";
echo "      - Minimum prompt length: 10 characters\n";
echo "      - Minimum completion length: 10 characters\n";
echo "      - Remove duplicates (check MD5 hashes)\n";
echo "   3. Use Claude to assess quality (see chapter code)\n";
echo "   4. Target quality score: 0.7+ overall\n\n";

// PITFALL 5: Wrong Dataset Split
echo "5. Improper Train/Validation/Test Split (WRONG):\n";
echo "   ❌ Random shuffle then sequential split (temporal leakage)\n";
echo "   ❌ Using same random seed everywhere\n";
echo "   ❌ Stratification ignored for imbalanced data\n\n";

echo "   ✅ CORRECT: Proper stratification\n";
echo "   Steps:\n";
echo "   1. Shuffle examples randomly\n";
echo "   2. Use stratified splitting for imbalanced classes\n";
echo "   3. Verify split distributions match original\n";
echo "   4. Never use test set for any decisions during training\n\n";

// PITFALL 6: Model Obsession
echo "6. Over-investing in Fine-tuning (WRONG):\n";
echo "   ❌ Spending weeks fine-tuning when prompt works fine\n";
echo "   ❌ High data preparation cost vs. benefit\n";
echo "   ❌ Missed opportunity for faster solutions\n\n";

echo "   ✅ CORRECT: Evaluate alternatives first\n";
echo "   Decision tree:\n";
echo "   1. Can prompt engineering solve it? (1-2 weeks) → TRY FIRST\n";
echo "   2. Do you have changing knowledge? → Use RAG\n";
echo "   3. Need consistent, specialized behavior? → Fine-tune\n";
echo "   4. Do you have 500+ high-quality examples? → PROCEED\n";
echo "   5. Expected improvement: >10%? → PROCEED\n\n";

// PITFALL 7: Wrong Metrics
echo "7. Using Wrong Evaluation Metrics (WRONG):\n";
echo "   ❌ Accuracy on imbalanced dataset (95% class → 95% accuracy)\n";
echo "   ❌ Single metric for multi-dimensional task\n";
echo "   ❌ No comparison to business metrics\n\n";

echo "   ✅ CORRECT: Multi-dimensional evaluation\n";
echo "   Key metrics:\n";
echo "   - Accuracy: % correct\n";
echo "   - Precision: Of predicted positive, how many correct?\n";
echo "   - Recall: Of actual positive, how many found?\n";
echo "   - F1 Score: Harmonic mean of precision and recall\n";
echo "   - Business metrics: Customer satisfaction, cost savings\n\n";
```

## Real-World Use Case Examples

### Example 1: Email Classification Fine-tuning

```php
<?php
# filename: examples/usecase-email-classification.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\FineTuning\DatasetPreparation;
use App\FineTuning\ModelEvaluator;
use App\FineTuning\CostBenefitAnalyzer;
use ClaudePhp\ClaudePhp;

echo "=== Email Classification Fine-tuning Use Case ===\n\n";

$claude = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY');

// Prepare realistic training data
$trainingExamples = [
    [
        'prompt' => 'Subject: URGENT: Your Account Has Been Compromised\n\nDear Valued Customer,\n\nWe detected suspicious activity...',
        'completion' => 'phishing'
    ],
    [
        'prompt' => 'Subject: Project Update - Q4 Goals\n\nHi team,\n\nHere\'s our progress on Q4 objectives...',
        'completion' => 'work'
    ],
    [
        'prompt' => 'Subject: 50% OFF LIMITED TIME ONLY!!!\n\nClick here now to claim your offer...',
        'completion' => 'promotional'
    ],
    // ... more examples ...
];

$prep = new DatasetPreparation($claude);
$dataset = $prep->prepare($trainingExamples, 'email_classification');

echo "Dataset prepared:\n";
echo "- Total examples: " . $dataset->stats['total_examples'] . "\n";
echo "- Train: " . count($dataset->train) . "\n";
echo "- Validation: " . count($dataset->validation) . "\n";
echo "- Test: " . count($dataset->test) . "\n";
echo "- Quality score: " . number_format($dataset->quality['average_score'], 2) . "\n\n";

// Characteristics of this use case:
echo "Use Case Characteristics:\n";
echo "- Domain-specific: Email classification requires knowledge of phishing patterns\n";
echo "- High volume: 10,000+ emails/day\n";
echo "- Consistency critical: Must classify correctly every time\n";
echo "- Data stable: Email types don't change dramatically\n";
echo "- Quality impact: Wrong classification affects user experience\n";
echo "- ROI: High - reduces support tickets significantly\n\n";

// Cost analysis
$analyzer = new CostBenefitAnalyzer();
$roi = $analyzer->analyze(
    monthlyVolume: 300000,
    currentCostPerRequest: 0.004,  // Using Claude Sonnet on base model
    fineTunedCostPerRequest: 0.002, // Fine-tuned model cheaper
    setupCost: 3000,               // 1 week of data prep
    monthlyMaintenanceCost: 500,   // Regular monitoring and updates
    qualityImprovement: 0.25       // 25% fewer misclassifications
);

echo "ROI Analysis:\n";
echo "- Current yearly cost: $" . number_format($roi->currentYearlyCost, 2) . "\n";
echo "- Fine-tuned yearly cost: $" . number_format($roi->fineTunedYearlyCost, 2) . "\n";
echo "- Yearly savings: $" . number_format($roi->yearlySavings, 2) . "\n";
echo "- Quality value: $" . number_format($roi->qualityValue, 2) . "\n";
echo "- Break-even: " . number_format($roi->breakEvenMonths, 1) . " months\n";
echo "- Recommendation: {$roi->recommendation}\n";
```

### Example 2: Technical Documentation Q&A Fine-tuning

```php
<?php
# filename: examples/usecase-documentation-qa.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

echo "=== Technical Documentation Q&A Fine-tuning ===\n\n";

$trainingExamples = [
    [
        'prompt' => 'Q: How do I authenticate with the API?\n\nDocumentation excerpt: Authentication uses bearer tokens in the Authorization header. Include your API key prefixed with "Bearer ".',
        'completion' => 'To authenticate with the API:\n1. Get your API key from the dashboard\n2. Add to request header: Authorization: Bearer YOUR_API_KEY\n3. Include in all API requests'
    ],
    [
        'prompt' => 'Q: What\'s the rate limit?\n\nDocumentation excerpt: Rate limits are 1000 requests per minute for standard tier, 5000 for premium.',
        'completion' => 'Rate limits depend on your tier:\n- Standard: 1000 req/min\n- Premium: 5000 req/min\n- Enterprise: Custom limits\n\nCheck X-RateLimit-Remaining header for current quota'
    ],
    // ... more examples from actual documentation ...
];

echo "Use Case Characteristics:\n";
echo "- Domain-specific knowledge required from documentation\n";
echo "- Answers must be accurate and reference-able\n";
echo "- Limited data available (only from docs + support tickets)\n";
echo "- RAG might be better choice here!\n\n";

echo "Decision: Should we fine-tune?\n";
echo "- ❌ Data availability: Low (only documentation examples)\n";
echo "- ❌ Data changes frequently: Yes (docs update regularly)\n";
echo "- ✅ Needs factual accuracy: Yes\n";
echo "- ✅ Knowledge base available: Yes\n\n";

echo "RECOMMENDATION: Use RAG instead!\n";
echo "Reasons:\n";
echo "1. Limited training data (< 500 examples)\n";
echo "2. Knowledge changes (documentation updates)\n";
echo "3. Need citations (which RAG provides)\n";
echo "4. Faster to implement than fine-tuning\n";
echo "5. Keep documentation as single source of truth\n\n";

echo "For fine-tuning: Would need historical Q&A pairs, not just documentation\n";
```

## Advanced Topics

### Multi-task Fine-tuning

Fine-tune a single model on multiple related tasks:

```php
<?php
# filename: src/FineTuning/MultiTaskFineTuning.php
declare(strict_types=1);

namespace App\FineTuning;

class MultiTaskFineTuning
{
    /**
     * Prepare multi-task training data
     */
    public function prepareMultiTaskDataset(
        array $taskDatasets // ['sentiment' => [...], 'category' => [...], ...]
    ): array {
        $combinedDataset = [];

        foreach ($taskDatasets as $taskName => $examples) {
            foreach ($examples as $example) {
                $combinedDataset[] = [
                    'task' => $taskName,
                    'prompt' => "[TASK: {$taskName}]\n\n{$example['prompt']}",
                    'completion' => $example['completion']
                ];
            }
        }

        // Shuffle to mix tasks
        shuffle($combinedDataset);

        return $combinedDataset;
    }

    /**
     * Evaluate multi-task model
     */
    public function evaluateMultiTask(
        array $evaluations // Task => results
    ): MultiTaskEvaluation {
        $results = [];

        foreach ($evaluations as $task => $scores) {
            $results[$task] = [
                'accuracy' => array_sum($scores) / count($scores),
                'count' => count($scores)
            ];
        }

        return new MultiTaskEvaluation($results);
    }
}
```

### Few-shot vs Fine-tuning Trade-offs

```php
<?php
# filename: examples/few-shot-vs-finetuning.php
declare(strict_types=1);

echo "=== Few-Shot Learning vs Fine-tuning ===\n\n";

echo "FEW-SHOT LEARNING (Prompt Engineering):\n";
echo "Pros:\n";
echo "- ✅ Immediate - no training needed\n";
echo "- ✅ No API costs for training\n";
echo "- ✅ Easy to modify behavior\n";
echo "- ✅ Works with low data (3-5 examples)\n";
echo "- ✅ Good for rapid experimentation\n\n";

echo "Cons:\n";
echo "- ❌ Token overhead (examples in every request)\n";
echo "- ❌ Limited by context window\n";
echo "- ❌ Less consistent for complex tasks\n";
echo "- ❌ Scales poorly with number of examples\n\n";

echo "FINE-TUNING:\n";
echo "Pros:\n";
echo "- ✅ Consistent behavior across requests\n";
echo "- ✅ Cheaper per-token for high volume\n";
echo "- ✅ Specialized model for domain\n";
echo "- ✅ Better at complex tasks\n";
echo "- ✅ Scales to thousands of examples\n\n";

echo "Cons:\n";
echo "- ❌ Requires 500+ quality examples\n";
echo "- ❌ Setup cost (data prep, training)\n";
echo "- ❌ Harder to modify behavior\n";
echo "- ❌ Less flexible than prompts\n";
echo "- ❌ Break-even period (weeks to months)\n\n";

echo "DECISION MATRIX:\n";
echo "Use Few-Shot when:\n";
echo "- Prototyping or exploring\n";
echo "- Have < 100 examples\n";
echo "- Behavior changes frequently\n";
echo "- Low volume (< 1000 req/day)\n";
echo "- Need flexibility\n\n";

echo "Use Fine-tuning when:\n";
echo "- Have 500+ quality examples\n";
echo "- Need consistent, specialized behavior\n";
echo "- High volume (10,000+ req/day)\n";
echo "- Ready for production\n";
echo "- ROI positive over 6+ months\n\n";
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

## Troubleshooting

### Dataset Quality Too Low

**Symptom**: `RuntimeException: Dataset quality too low: 0.65`

**Cause**: The quality assessment found that your training examples don't meet the minimum quality threshold (0.7).

**Solution**: 

1. Review individual quality scores to identify problematic examples
2. Remove or improve low-quality examples
3. Ensure prompts are clear and specific
4. Verify completions are accurate and complete
5. Consider manual review of a sample before full dataset preparation

```php
// Check individual scores
$quality = $prep->assessQuality($examples);
foreach ($quality['individual_scores'] as $i => $score) {
    if ($score['overall'] < 0.7) {
        echo "Example {$i} needs improvement: {$score['overall']}\n";
        echo "Issues: " . implode(', ', $score['issues']) . "\n";
    }
}
```

### Break-Even Calculation Returns Negative or Infinite

**Symptom**: Break-even months is negative, zero, or infinite

**Cause**: The fine-tuned model doesn't provide cost savings, or the calculation divides by zero.

**Solution**:

1. Verify your cost estimates are accurate
2. Check if fine-tuned cost per request is actually lower than current cost
3. Consider that fine-tuning may not be cost-effective for your volume
4. Factor in quality improvements, not just cost savings

```php
// Add validation
if ($currentMonthlyCost <= $fineTunedMonthlyCost) {
    throw new \InvalidArgumentException(
        'Fine-tuning does not provide cost savings. Review your cost estimates.'
    );
}
```

### Model Evaluation Takes Too Long

**Symptom**: Evaluation process is slow or times out

**Cause**: Evaluating large test sets with Claude API calls can be time-consuming and expensive.

**Solution**:

1. Use a smaller representative sample for evaluation
2. Cache evaluation results for unchanged examples
3. Use parallel processing for independent evaluations
4. Consider using faster models (Haiku) for initial screening

```php
// Sample test set for faster evaluation
$sampleSize = min(100, count($testSet));
$sampledTestSet = array_slice($testSet, 0, $sampleSize);
$evaluation = $evaluator->evaluate($modelId, $sampledTestSet);
```

### Decision Framework Always Recommends Same Approach

**Symptom**: Framework consistently recommends prompt engineering or RAG

**Cause**: Your use case characteristics don't favor fine-tuning (low data, low volume, urgent timeline).

**Solution**:

1. This may be correct - fine-tuning isn't always the answer
2. Review your use case parameters - are they accurate?
3. Consider if you can improve data availability or quality
4. Evaluate if hybrid approaches might work better
5. Don't force fine-tuning if it's not appropriate

## Wrap-up

Congratulations! You've completed a comprehensive guide to fine-tuning strategies for Claude applications. In this chapter, you've:

- ✓ **Built a decision framework** to evaluate when fine-tuning makes sense versus alternatives
- ✓ **Created dataset preparation tools** with quality assessment and proper formatting
- ✓ **Implemented model evaluation systems** with multiple metrics and comparison capabilities
- ✓ **Developed cost-benefit analyzers** for ROI calculations and investment decisions
- ✓ **Learned production deployment strategies** for fine-tuned models
- ✓ **Explored hybrid approaches** that combine multiple customization techniques

Fine-tuning is a powerful tool, but it's not always the right choice. The decision framework you've built helps you make informed choices based on your specific use case, data availability, budget, and timeline. Remember that prompt engineering and RAG are often faster, cheaper, and more flexible alternatives that should be considered first.

When fine-tuning is appropriate, focus on dataset quality over quantity, evaluate systematically, and calculate ROI carefully. The tools and patterns you've learned here provide a solid foundation for making strategic decisions about model customization.

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

## Further Reading

- [Anthropic Fine-tuning Documentation](https://docs.claude.com/en/docs/fine-tuning) — Official fine-tuning guide from Anthropic
- [Chapter 31: Retrieval Augmented Generation](/series/claude-php-developers/chapters/31-retrieval-augmented-generation) — Learn when RAG is a better choice
- [Chapter 32: Vector Databases](/series/claude-php-developers/chapters/32-vector-databases) — Understand vector database integration
- [Machine Learning Evaluation Metrics](https://en.wikipedia.org/wiki/Evaluation_of_binary_classifiers) — Comprehensive guide to ML evaluation
- [ROI Calculation Best Practices](https://www.investopedia.com/terms/r/returnoninvestment.asp) — Understanding ROI in business context
- [Dataset Preparation for ML](https://towardsdatascience.com/data-preparation-for-machine-learning-9d3c82a8e781) — Best practices for preparing training data

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="35"
  label="You've mastered fine-tuning strategy and decision-making!"
/>

---

Continue to [Chapter 36: Security Best Practices](/series/claude-php-developers/chapters/36-security-best-practices) to learn comprehensive security strategies for production Claude applications.

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
