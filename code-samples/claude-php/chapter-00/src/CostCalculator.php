<?php

declare(strict_types=1);

namespace ClaudePhp\QuickStart;

/**
 * CostCalculator - Calculate API costs from token usage
 *
 * This class helps you track and calculate the cost of using Claude API
 * based on current pricing (as of 2025).
 */
class CostCalculator
{
    /**
     * Pricing per million tokens (as of 2025)
     */
    private const PRICING = [
        'claude-sonnet-4-20250514' => [
            'input' => 3.00,
            'output' => 15.00
        ],
        'claude-opus-4-20250514' => [
            'input' => 15.00,
            'output' => 75.00
        ],
        'claude-haiku-4-20250514' => [
            'input' => 0.25,
            'output' => 1.25
        ],
        // Legacy models
        'claude-3-5-sonnet-20241022' => [
            'input' => 3.00,
            'output' => 15.00
        ],
        'claude-3-5-haiku-20241022' => [
            'input' => 0.80,
            'output' => 4.00
        ]
    ];

    private string $model;
    private int $totalInputTokens = 0;
    private int $totalOutputTokens = 0;

    /**
     * Create a new cost calculator
     *
     * @param string $model The Claude model being used
     */
    public function __construct(string $model = 'claude-sonnet-4-20250514')
    {
        $this->model = $model;
    }

    /**
     * Add token usage from an API response
     *
     * @param int $inputTokens Number of input tokens used
     * @param int $outputTokens Number of output tokens generated
     * @return void
     */
    public function addUsage(int $inputTokens, int $outputTokens): void
    {
        $this->totalInputTokens += $inputTokens;
        $this->totalOutputTokens += $outputTokens;
    }

    /**
     * Add token usage from an API response object
     *
     * @param object $response The API response containing usage information
     * @return void
     */
    public function addUsageFromResponse(object $response): void
    {
        $this->addUsage(
            $response->usage->input_tokens,
            $response->usage->output_tokens
        );
    }

    /**
     * Calculate cost for specific token usage
     *
     * @param int $inputTokens Number of input tokens
     * @param int $outputTokens Number of output tokens
     * @param string|null $model Model to calculate for (uses instance model if null)
     * @return float Cost in USD
     */
    public function calculateCost(
        int $inputTokens,
        int $outputTokens,
        ?string $model = null
    ): float {
        $model = $model ?? $this->model;

        if (!isset(self::PRICING[$model])) {
            throw new \InvalidArgumentException("Unknown model: {$model}");
        }

        $pricing = self::PRICING[$model];

        $inputCost = ($inputTokens / 1_000_000) * $pricing['input'];
        $outputCost = ($outputTokens / 1_000_000) * $pricing['output'];

        return $inputCost + $outputCost;
    }

    /**
     * Get total cost for all tracked usage
     *
     * @return float Total cost in USD
     */
    public function getTotalCost(): float
    {
        return $this->calculateCost($this->totalInputTokens, $this->totalOutputTokens);
    }

    /**
     * Get total input tokens tracked
     *
     * @return int Total input tokens
     */
    public function getTotalInputTokens(): int
    {
        return $this->totalInputTokens;
    }

    /**
     * Get total output tokens tracked
     *
     * @return int Total output tokens
     */
    public function getTotalOutputTokens(): int
    {
        return $this->totalOutputTokens;
    }

    /**
     * Get total tokens tracked (input + output)
     *
     * @return int Total tokens
     */
    public function getTotalTokens(): int
    {
        return $this->totalInputTokens + $this->totalOutputTokens;
    }

    /**
     * Reset all tracked usage
     *
     * @return void
     */
    public function reset(): void
    {
        $this->totalInputTokens = 0;
        $this->totalOutputTokens = 0;
    }

    /**
     * Get a formatted summary of usage and costs
     *
     * @return string Formatted summary
     */
    public function getSummary(): string
    {
        $cost = $this->getTotalCost();

        return sprintf(
            "Model: %s\n" .
            "Input Tokens: %s\n" .
            "Output Tokens: %s\n" .
            "Total Tokens: %s\n" .
            "Total Cost: $%.6f USD",
            $this->model,
            number_format($this->totalInputTokens),
            number_format($this->totalOutputTokens),
            number_format($this->getTotalTokens()),
            $cost
        );
    }

    /**
     * Get pricing information for a specific model
     *
     * @param string $model The model to get pricing for
     * @return array<string, float> Array with 'input' and 'output' prices per million tokens
     */
    public static function getPricing(string $model): array
    {
        if (!isset(self::PRICING[$model])) {
            throw new \InvalidArgumentException("Unknown model: {$model}");
        }

        return self::PRICING[$model];
    }

    /**
     * Get all available models with pricing
     *
     * @return array<string, array<string, float>> All models and their pricing
     */
    public static function getAllPricing(): array
    {
        return self::PRICING;
    }
}
