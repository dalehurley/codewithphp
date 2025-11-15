<?php

declare(strict_types=1);

namespace App;

class BudgetManager
{
    private float $spent = 0;
    
    public function __construct(
        private float $monthlyBudget,
        private float $alertThreshold = 0.8
    ) {}
    
    public function trackRequest(string $model, int $tokens): void
    {
        $cost = $this->calculateCost($model, $tokens);
        $this->spent += $cost;
        
        if ($this->spent >= $this->monthlyBudget * $this->alertThreshold) {
            $this->sendAlert();
        }
    }
    
    private function calculateCost(string $model, int $tokens): float
    {
        $rates = [
            'haiku' => 0.25,
            'sonnet' => 3.0,
            'opus' => 15.0
        ];
        
        return ($tokens / 1_000_000) * ($rates[$model] ?? 3.0);
    }
    
    private function sendAlert(): void
    {
        // Send budget alert
        error_log("Budget alert: {$this->spent}/{$this->monthlyBudget} spent");
    }
    
    public function getStats(): array
    {
        return [
            'budget' => $this->monthlyBudget,
            'spent' => $this->spent,
            'remaining' => $this->monthlyBudget - $this->spent,
            'utilization' => round(($this->spent / $this->monthlyBudget) * 100, 2)
        ];
    }
    
    public function canAfford(string $model, int $tokens): bool
    {
        $cost = $this->calculateCost($model, $tokens);
        return ($this->spent + $cost) <= $this->monthlyBudget;
    }
}
