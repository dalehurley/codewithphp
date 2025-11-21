<?php

declare(strict_types=1);

namespace App;

use ClaudePhp\ClaudePhp;

class BudgetTracker
{
    public function __construct(
        private readonly \Redis $redis,
        private readonly array $budgetLimits = [
            'hourly' => 10.00,
            'daily' => 200.00,
            'monthly' => 5000.00,
        ]
    ) {}

    /**
     * Track spending and check limits
     */
    public function trackSpending(float $cost, string $userId = 'system'): void
    {
        $now = new \DateTimeImmutable();

        // Track at different time granularities
        $this->incrementSpending('hourly', $cost, $now->format('Y-m-d-H'));
        $this->incrementSpending('daily', $cost, $now->format('Y-m-d'));
        $this->incrementSpending('monthly', $cost, $now->format('Y-m'));

        // Track per user
        $this->incrementUserSpending($userId, $cost, $now->format('Y-m-d'));

        // Check if limits exceeded
        $this->checkLimits($cost);
    }

    /**
     * Check if request would exceed budget
     */
    public function wouldExceedBudget(float $estimatedCost): bool
    {
        foreach (['hourly', 'daily', 'monthly'] as $period) {
            $current = $this->getCurrentSpending($period);
            $limit = $this->budgetLimits[$period];

            if ($current + $estimatedCost > $limit) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get current spending
     */
    public function getCurrentSpending(string $period): float
    {
        $key = $this->getSpendingKey($period);
        return (float) ($this->redis->get($key) ?? 0);
    }

    /**
     * Get budget status
     */
    public function getBudgetStatus(): array
    {
        $status = [];

        foreach ($this->budgetLimits as $period => $limit) {
            $spent = $this->getCurrentSpending($period);
            $remaining = $limit - $spent;
            $usedPct = round($spent / $limit * 100, 1);

            $status[$period] = [
                'limit' => $limit,
                'spent' => $spent,
                'remaining' => $remaining,
                'used_pct' => $usedPct,
                'status' => $this->getStatus($usedPct),
            ];
        }

        return $status;
    }

    /**
     * Get user spending
     */
    public function getUserSpending(string $userId, string $date): float
    {
        $key = "budget:user:$userId:$date";
        return (float) ($this->redis->get($key) ?? 0);
    }

    /**
     * Get top spending users
     */
    public function getTopSpenders(int $limit = 10): array
    {
        $today = (new \DateTimeImmutable())->format('Y-m-d');

        // Get all user spending keys for today
        $keys = $this->redis->keys("budget:user:*:$today");

        $spenders = [];
        foreach ($keys as $key) {
            preg_match('/budget:user:(.+):/', $key, $matches);
            $userId = $matches[1] ?? 'unknown';
            $spending = (float) $this->redis->get($key);

            $spenders[$userId] = $spending;
        }

        arsort($spenders);
        return array_slice($spenders, 0, $limit, true);
    }

    private function incrementSpending(string $period, float $cost, string $key): void
    {
        $redisKey = "budget:$period:$key";
        $this->redis->incrByFloat($redisKey, $cost);

        // Set expiration
        $ttl = match($period) {
            'hourly' => 7200,      // 2 hours
            'daily' => 172800,     // 2 days
            'monthly' => 2592000,  // 30 days
            default => 86400
        };

        $this->redis->expire($redisKey, $ttl);
    }

    private function incrementUserSpending(string $userId, float $cost, string $date): void
    {
        $key = "budget:user:$userId:$date";
        $this->redis->incrByFloat($key, $cost);
        $this->redis->expire($key, 172800);  // 2 days
    }

    private function checkLimits(float $recentCost): void
    {
        foreach ($this->budgetLimits as $period => $limit) {
            $current = $this->getCurrentSpending($period);
            $usedPct = ($current / $limit) * 100;

            // Alert at different thresholds
            if ($usedPct >= 100) {
                $this->sendAlert('critical', $period, $current, $limit);
            } elseif ($usedPct >= 90) {
                $this->sendAlert('warning', $period, $current, $limit);
            } elseif ($usedPct >= 75) {
                $this->sendAlert('info', $period, $current, $limit);
            }
        }
    }

    private function getSpendingKey(string $period): string
    {
        $now = new \DateTimeImmutable();

        $key = match($period) {
            'hourly' => $now->format('Y-m-d-H'),
            'daily' => $now->format('Y-m-d'),
            'monthly' => $now->format('Y-m'),
            default => $now->format('Y-m-d')
        };

        return "budget:$period:$key";
    }

    private function getStatus(float $usedPct): string
    {
        return match(true) {
            $usedPct >= 100 => 'exceeded',
            $usedPct >= 90 => 'critical',
            $usedPct >= 75 => 'warning',
            default => 'ok'
        };
    }

    private function sendAlert(string $severity, string $period, float $current, float $limit): void
    {
        $message = "Budget $severity: $period spending at $" . number_format($current, 2) .
                   " of $" . number_format($limit, 2) . " limit";

        error_log("[BUDGET ALERT] $message");

        // Send to monitoring system, email, Slack, etc.
    }
}
