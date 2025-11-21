<?php

declare(strict_types=1);

namespace CodeWithPHP\ClaudeShared;

use Anthropic\Contracts\MessageContract;

/**
 * Track API costs across requests
 */
class CostTracker
{
    private const PRICING = [
        'claude-haiku-4-5' => ['input' => 0.25, 'output' => 1.25],
        'claude-sonnet-4-5' => ['input' => 3.00, 'output' => 15.00],
        'claude-opus-4-1' => ['input' => 15.00, 'output' => 75.00],
    ];

    private array $requests = [];
    private float $totalCost = 0.0;
    private int $totalInputTokens = 0;
    private int $totalOutputTokens = 0;

    /**
     * Track a request's cost
     */
    public function trackRequest(MessageContract $response, ?string $model = null): float
    {
        $model = $model ?? $response->model;
        $inputTokens = $response->usage->inputTokens;
        $outputTokens = $response->usage->outputTokens;

        $cost = $this->calculateCost($inputTokens, $outputTokens, $model);

        $this->requests[] = [
            'timestamp' => time(),
            'model' => $model,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'cost' => $cost,
        ];

        $this->totalCost += $cost;
        $this->totalInputTokens += $inputTokens;
        $this->totalOutputTokens += $outputTokens;

        return $cost;
    }

    /**
     * Calculate cost for given tokens and model
     */
    public function calculateCost(int $inputTokens, int $outputTokens, string $model): float
    {
        if (!isset(self::PRICING[$model])) {
            throw new \InvalidArgumentException("Unknown model: {$model}");
        }

        $pricing = self::PRICING[$model];

        $inputCost = ($inputTokens / 1_000_000) * $pricing['input'];
        $outputCost = ($outputTokens / 1_000_000) * $pricing['output'];

        return $inputCost + $outputCost;
    }

    /**
     * Get total cost across all tracked requests
     */
    public function getTotalCost(): float
    {
        return $this->totalCost;
    }

    /**
     * Get cost of last request
     */
    public function getLastCost(): float
    {
        if (empty($this->requests)) {
            return 0.0;
        }

        return end($this->requests)['cost'];
    }

    /**
     * Get all tracked requests
     */
    public function getRequests(): array
    {
        return $this->requests;
    }

    /**
     * Get statistics
     */
    public function getStats(): array
    {
        return [
            'total_requests' => count($this->requests),
            'total_cost' => $this->totalCost,
            'total_input_tokens' => $this->totalInputTokens,
            'total_output_tokens' => $this->totalOutputTokens,
            'average_cost_per_request' => count($this->requests) > 0
                ? $this->totalCost / count($this->requests)
                : 0.0,
        ];
    }

    /**
     * Reset tracker
     */
    public function reset(): void
    {
        $this->requests = [];
        $this->totalCost = 0.0;
        $this->totalInputTokens = 0;
        $this->totalOutputTokens = 0;
    }
}
