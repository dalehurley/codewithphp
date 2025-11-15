<?php

declare(strict_types=1);

namespace App;

class ROITracker
{
    private array $metrics = [];
    
    public function track(string $feature, float $cost, float $revenue): void
    {
        $this->metrics[$feature] = [
            'cost' => $cost,
            'revenue' => $revenue,
            'roi' => ($revenue - $cost) / max($cost, 1),
            'timestamp' => time()
        ];
    }
    
    public function calculate(string $feature): ?array
    {
        return $this->metrics[$feature] ?? null;
    }
    
    public function getTotalROI(): float
    {
        $totalCost = array_sum(array_column($this->metrics, 'cost'));
        $totalRevenue = array_sum(array_column($this->metrics, 'revenue'));
        
        return $totalCost > 0 ? ($totalRevenue - $totalCost) / $totalCost : 0;
    }
    
    public function getReport(): array
    {
        return [
            'features' => $this->metrics,
            'total_cost' => array_sum(array_column($this->metrics, 'cost')),
            'total_revenue' => array_sum(array_column($this->metrics, 'revenue')),
            'overall_roi' => $this->getTotalROI()
        ];
    }
}
