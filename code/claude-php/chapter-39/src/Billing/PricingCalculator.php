<?php

declare(strict_types=1);

namespace App\Billing;

class PricingCalculator
{
    // Prices per million tokens (as of 2025)
    private const PRICING = [
        'claude-opus-4-1' => [
            'input' => 15.00,
            'output' => 75.00,
        ],
        'claude-sonnet-4-5' => [
            'input' => 3.00,
            'output' => 15.00,
        ],
        'claude-haiku-4-5-20251001' => [
            'input' => 0.25,
            'output' => 1.25,
        ],
    ];

    /**
     * Calculate cost for a request
     */
    public function calculateCost(
        string $model,
        int $inputTokens,
        int $outputTokens
    ): array {
        $pricing = self::PRICING[$model] ?? ['input' => 0, 'output' => 0];

        $inputCost = ($inputTokens / 1_000_000) * $pricing['input'];
        $outputCost = ($outputTokens / 1_000_000) * $pricing['output'];
        $totalCost = $inputCost + $outputCost;

        return [
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'total_tokens' => $inputTokens + $outputTokens,
            'input_cost' => $inputCost,
            'output_cost' => $outputCost,
            'total_cost' => $totalCost,
            'cost_per_token' => $totalCost / ($inputTokens + $outputTokens),
            'model' => $model,
        ];
    }

    /**
     * Estimate cost before making request
     */
    public function estimateCost(
        string $model,
        string $prompt,
        int $expectedOutputTokens = 500
    ): array {
        // Rough estimation: 1 token ≈ 4 characters
        $estimatedInputTokens = (int) ceil(strlen($prompt) / 4);

        return $this->calculateCost($model, $estimatedInputTokens, $expectedOutputTokens);
    }

    /**
     * Compare costs across models
     */
    public function compareCosts(
        int $inputTokens,
        int $outputTokens
    ): array {
        $comparison = [];

        foreach (self::PRICING as $model => $pricing) {
            $cost = $this->calculateCost($model, $inputTokens, $outputTokens);
            $comparison[$this->simplifyModelName($model)] = $cost['total_cost'];
        }

        // Add relative costs
        $haikusCost = $comparison['haiku'];
        foreach ($comparison as $model => $cost) {
            $comparison[$model . '_relative'] = $cost / $haikusCost;
        }

        return $comparison;
    }

    /**
     * Calculate monthly projection
     */
    public function projectMonthlyCost(
        string $model,
        int $avgInputTokens,
        int $avgOutputTokens,
        int $requestsPerDay
    ): array {
        $costPerRequest = $this->calculateCost(
            $model,
            $avgInputTokens,
            $avgOutputTokens
        )['total_cost'];

        $dailyCost = $costPerRequest * $requestsPerDay;
        $monthlyCost = $dailyCost * 30;
        $yearlyCost = $dailyCost * 365;

        return [
            'cost_per_request' => $costPerRequest,
            'daily_cost' => $dailyCost,
            'monthly_cost' => $monthlyCost,
            'yearly_cost' => $yearlyCost,
            'assumptions' => [
                'model' => $model,
                'avg_input_tokens' => $avgInputTokens,
                'avg_output_tokens' => $avgOutputTokens,
                'requests_per_day' => $requestsPerDay,
            ],
        ];
    }

    private function simplifyModelName(string $model): string
    {
        return match(true) {
            str_contains($model, 'opus') => 'opus',
            str_contains($model, 'sonnet') => 'sonnet',
            str_contains($model, 'haiku') => 'haiku',
            default => 'unknown'
        };
    }
}
