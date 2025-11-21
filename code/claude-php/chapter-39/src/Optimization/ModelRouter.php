<?php

declare(strict_types=1);

namespace App\Optimization;

use ClaudePhp\ClaudePhp;

class ModelRouter
{
    public function __construct(
        private readonly ?ClaudePhp $client = null
    ) {}

    /**
     * Select optimal model based on task requirements
     */
    public function selectModel(array $taskRequirements): string
    {
        $complexity = $taskRequirements['complexity'] ?? 'medium';
        $qualityRequired = $taskRequirements['quality_required'] ?? 'medium';
        $budgetPriority = $taskRequirements['budget_priority'] ?? false;
        $responseTimeRequired = $taskRequirements['response_time'] ?? 'normal';

        // High budget priority → use cheapest suitable model
        if ($budgetPriority) {
            return match($complexity) {
                'simple' => 'claude-haiku-4-5-20251001',
                'medium' => 'claude-haiku-4-5-20251001',
                'complex' => 'claude-sonnet-4-5',
                default => 'claude-haiku-4-5-20251001'
            };
        }

        // Speed critical → use fastest model
        if ($responseTimeRequired === 'fast') {
            return 'claude-haiku-4-5-20251001';
        }

        // Quality critical → use best model
        if ($qualityRequired === 'critical') {
            return 'claude-opus-4-1';
        }

        // Default routing based on complexity
        return match($complexity) {
            'simple' => 'claude-haiku-4-5-20251001',
            'medium' => 'claude-sonnet-4-5',
            'complex' => 'claude-opus-4-1',
            default => 'claude-sonnet-4-5'
        };
    }

    /**
     * Classify task complexity automatically
     */
    public function classifyComplexity(string $prompt, array $context = []): string
    {
        $promptLength = strlen($prompt);
        $hasCodeGeneration = str_contains(strtolower($prompt), 'generate code') ||
                            str_contains(strtolower($prompt), 'write a function');
        $hasComplexReasoning = str_contains(strtolower($prompt), 'analyze') ||
                               str_contains(strtolower($prompt), 'design') ||
                               str_contains(strtolower($prompt), 'architecture');

        // Simple tasks
        if ($promptLength < 200 && !$hasCodeGeneration && !$hasComplexReasoning) {
            return 'simple';
        }

        // Complex tasks
        if ($hasComplexReasoning || $promptLength > 2000) {
            return 'complex';
        }

        // Default to medium
        return 'medium';
    }

    /**
     * Get model recommendation with cost comparison
     */
    public function recommendModel(string $prompt, array $requirements = []): array
    {
        $complexity = $this->classifyComplexity($prompt, $requirements);
        $requirements['complexity'] = $complexity;

        $recommendedModel = $this->selectModel($requirements);

        // Estimate costs for all models
        $estimatedTokens = (int) ceil(strlen($prompt) / 4);
        $expectedOutput = $requirements['expected_output_tokens'] ?? 500;

        $calculator = new \App\Billing\PricingCalculator();
        $costs = $calculator->compareCosts($estimatedTokens, $expectedOutput);

        return [
            'recommended_model' => $recommendedModel,
            'complexity' => $complexity,
            'estimated_costs' => $costs,
            'reasoning' => $this->explainRecommendation($complexity, $requirements),
        ];
    }

    private function explainRecommendation(string $complexity, array $requirements): string
    {
        if ($requirements['budget_priority'] ?? false) {
            return "Budget-optimized: Using most cost-effective model for $complexity complexity";
        }

        if ($requirements['quality_required'] === 'critical') {
            return "Quality-optimized: Using highest quality model";
        }

        return "Balanced: Using appropriate model for $complexity complexity";
    }
}
