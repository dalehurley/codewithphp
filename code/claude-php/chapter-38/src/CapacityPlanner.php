<?php

declare(strict_types=1);

namespace App;

class CapacityPlanner
{
    public function calculate(
        int $requestsPerMonth,
        float $avgTokensPerRequest,
        float $costPerMillion
    ): array {
        $totalTokens = $requestsPerMonth * $avgTokensPerRequest;
        $monthlyCost = ($totalTokens / 1_000_000) * $costPerMillion;
        
        return [
            'requests_per_month' => $requestsPerMonth,
            'total_tokens' => $totalTokens,
            'monthly_cost' => $monthlyCost,
            'requests_per_second' => $requestsPerMonth / (30 * 24 * 3600),
            'recommended_rate_limit' => ceil($requestsPerMonth / (30 * 24 * 60))
        ];
    }
    
    public function forecast(array $historicalData, int $months = 3): array
    {
        $avgGrowth = $this->calculateGrowthRate($historicalData);
        $forecast = [];
        $last = end($historicalData);
        
        for ($i = 1; $i <= $months; $i++) {
            $forecast[] = $last * pow(1 + $avgGrowth, $i);
        }
        
        return $forecast;
    }
    
    private function calculateGrowthRate(array $data): float
    {
        if (count($data) < 2) return 0;
        
        $growthRates = [];
        for ($i = 1; $i < count($data); $i++) {
            if ($data[$i-1] > 0) {
                $growthRates[] = ($data[$i] - $data[$i-1]) / $data[$i-1];
            }
        }
        
        return !empty($growthRates) ? array_sum($growthRates) / count($growthRates) : 0;
    }
}
