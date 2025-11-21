<?php

declare(strict_types=1);

namespace ClaudePhp\TokenManagement;

/**
 * BudgetManager - Track and enforce token budgets
 */
class BudgetManager
{
    private int $budget;
    private int $used = 0;

    public function __construct(int $budget)
    {
        $this->budget = $budget;
    }

    /**
     * Add token usage
     */
    public function addUsage(int $tokens): void
    {
        $this->used += $tokens;
    }

    /**
     * Check if budget allows for more tokens
     */
    public function canAfford(int $tokens): bool
    {
        return ($this->used + $tokens) <= $this->budget;
    }

    /**
     * Get remaining budget
     */
    public function getRemaining(): int
    {
        return max(0, $this->budget - $this->used);
    }

    /**
     * Get usage percentage
     */
    public function getUsagePercentage(): float
    {
        return ($this->used / $this->budget) * 100;
    }

    /**
     * Check if budget is exhausted
     */
    public function isExhausted(): bool
    {
        return $this->used >= $this->budget;
    }

    /**
     * Get summary
     */
    public function getSummary(): string
    {
        return sprintf(
            "Budget: %s | Used: %s (%.1f%%) | Remaining: %s",
            number_format($this->budget),
            number_format($this->used),
            $this->getUsagePercentage(),
            number_format($this->getRemaining())
        );
    }
}
