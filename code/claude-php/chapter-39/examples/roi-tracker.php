<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Billing\RoiCalculator;

echo "=== ROI Calculator Example ===\n";

$roiCalculator = new RoiCalculator();

// Example: Content generation feature
$contentRoi = $roiCalculator->calculateRoi([
    'claude_api_cost' => 150.00,         // Monthly API costs
    'infrastructure_cost' => 50.00,       // Server costs
    'development_cost' => 2000.00 / 12,   // Amortized over 12 months
    'hours_saved' => 40,                  // Writer hours saved
    'hourly_rate' => 75.00,               // Writer hourly rate
    'revenue_generated' => 5000.00,       // Revenue from increased content
]);

echo "Content Generation ROI:\n";
echo "ROI: {$contentRoi['roi_percentage']}%\n";
echo "Net benefit: $" . number_format($contentRoi['net_benefit'], 2) . "\n";
echo "Payback period: {$contentRoi['payback_period_months']} months\n";

// Example: Customer support chatbot
$supportRoi = $roiCalculator->calculateSupportRoi([
    'total_requests' => 10000,
    'avg_cost_per_request' => 0.005,
    'tickets_deflected' => 3000,
    'avg_human_ticket_cost' => 15.00,
    'improved_satisfaction_score' => 2,  // 2 points improvement
]);

echo "\nCustomer Support ROI:\n";
echo "ROI: {$supportRoi['roi_percentage']}%\n";
echo "Cost savings: $" . number_format($supportRoi['benefits']['cost_avoided'], 2) . "\n";

echo "\n✓ ROI calculator working correctly\n";
