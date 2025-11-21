<?php

declare(strict_types=1);

namespace App;

class ROICalculator
{
    public function calculate(
        float $fineTuneCost,
        float $inferenceImprovement,
        int $monthlyRequests
    ): array {
        $monthlySavings = $inferenceImprovement * $monthlyRequests;
        $breakEvenMonths = $fineTuneCost / max($monthlySavings, 1);
        
        return [
            'monthly_savings' => $monthlySavings,
            'break_even_months' => $breakEvenMonths,
            'annual_roi' => (($monthlySavings * 12) - $fineTuneCost) / $fineTuneCost
        ];
    }
}
