<?php

declare(strict_types=1);

namespace App\Billing;

class RoiCalculator
{
    /**
     * Calculate ROI for AI feature
     */
    public function calculateRoi(array $metrics): array
    {
        $costs = [
            'claude_api' => $metrics['claude_api_cost'],
            'infrastructure' => $metrics['infrastructure_cost'] ?? 0,
            'development' => $metrics['development_cost'] ?? 0,
        ];

        $benefits = [
            'time_saved' => $metrics['hours_saved'] * ($metrics['hourly_rate'] ?? 50),
            'revenue_generated' => $metrics['revenue_generated'] ?? 0,
            'cost_avoided' => $metrics['cost_avoided'] ?? 0,
        ];

        $totalCost = array_sum($costs);
        $totalBenefit = array_sum($benefits);
        $netBenefit = $totalBenefit - $totalCost;
        $roi = $totalCost > 0 ? ($netBenefit / $totalCost) * 100 : 0;

        return [
            'costs' => $costs,
            'total_cost' => $totalCost,
            'benefits' => $benefits,
            'total_benefit' => $totalBenefit,
            'net_benefit' => $netBenefit,
            'roi_percentage' => round($roi, 2),
            'payback_period_months' => $this->calculatePaybackPeriod($totalCost, $totalBenefit),
        ];
    }

    /**
     * Calculate customer support ROI
     */
    public function calculateSupportRoi(array $metrics): array
    {
        // Costs
        $claudeCost = $metrics['total_requests'] * $metrics['avg_cost_per_request'];

        // Benefits
        $ticketsDeflected = $metrics['tickets_deflected'];
        $avgTicketCost = $metrics['avg_human_ticket_cost'] ?? 15.00;
        $costSavings = $ticketsDeflected * $avgTicketCost;

        $customerSatisfactionValue = $metrics['improved_satisfaction_score'] * 1000;  // Value per point

        return $this->calculateRoi([
            'claude_api_cost' => $claudeCost,
            'cost_avoided' => $costSavings,
            'revenue_generated' => $customerSatisfactionValue,
        ]);
    }

    private function calculatePaybackPeriod(float $totalCost, float $monthlyBenefit): float
    {
        if ($monthlyBenefit <= 0) {
            return INF;
        }

        return round($totalCost / $monthlyBenefit, 1);
    }
}
