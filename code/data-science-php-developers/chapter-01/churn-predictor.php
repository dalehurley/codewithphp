<?php

declare(strict_types=1);

/**
 * Use Case 5: Churn Predictor
 * 
 * This class predicts which customers are at risk of churning
 * using rule-based feature engineering and risk scoring.
 * 
 * Requirements:
 * - PHP 8.4+
 * - Database with 'customers' and 'tickets' tables
 * - Laravel/Eloquent or PDO
 */

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

final class ChurnPredictor
{
    /**
     * Calculate churn risk for a specific customer.
     * 
     * This uses a simple rule-based system. In production, you would
     * typically use a trained ML model instead.
     * 
     * @param int $customerId Customer to analyze
     * @return array Risk assessment with score and factors
     */
    public function calculateChurnRisk(int $customerId): array
    {
        $customer = DB::table('customers')->find($customerId);

        if (!$customer) {
            throw new \InvalidArgumentException("Customer {$customerId} not found");
        }

        // Feature engineering: Extract relevant features
        $daysSinceLastLogin = Carbon::now()->diffInDays($customer->last_login_at);
        $daysSinceSignup = Carbon::now()->diffInDays($customer->created_at);
        $supportTickets = DB::table('tickets')
            ->where('customer_id', $customerId)
            ->where('created_at', '>=', Carbon::now()->subMonths(3))
            ->count();

        // Simple rule-based risk scoring (0-100)
        // In production, replace with trained ML model predictions
        $risk = 0;

        // Inactivity is a strong churn signal
        if ($daysSinceLastLogin > 30) $risk += 40;
        if ($daysSinceLastLogin > 60) $risk += 30;

        // High support volume may indicate dissatisfaction
        if ($supportTickets > 5) $risk += 20;
        if ($supportTickets > 10) $risk += 10;

        // Payment issues are critical
        if ($customer->payment_failed) $risk += 10;

        // New customers are more likely to churn
        if ($daysSinceSignup < 30) $risk += 15;

        // Cap at 100
        $risk = min($risk, 100);

        // Determine risk level
        $riskLevel = match (true) {
            $risk >= 70 => 'high',
            $risk >= 40 => 'medium',
            default => 'low',
        };

        // Identify contributing factors
        $factors = $this->identifyRiskFactors(
            $daysSinceLastLogin,
            $supportTickets,
            $customer->payment_failed,
            $daysSinceSignup
        );

        return [
            'customer_id' => $customerId,
            'risk_score' => $risk,
            'risk_level' => $riskLevel,
            'factors' => $factors,
            'recommendations' => $this->getRecommendations($riskLevel, $factors),
            'analyzed_at' => Carbon::now()->toIso8601String(),
        ];
    }

    /**
     * Identify which factors contribute to churn risk.
     * 
     * @return array List of risk factors
     */
    private function identifyRiskFactors(
        int $daysSinceLastLogin,
        int $supportTickets,
        bool $paymentFailed,
        int $daysSinceSignup
    ): array {
        $factors = [];

        if ($daysSinceLastLogin > 60) {
            $factors[] = [
                'type' => 'critical',
                'description' => "No login in {$daysSinceLastLogin} days",
            ];
        } elseif ($daysSinceLastLogin > 30) {
            $factors[] = [
                'type' => 'warning',
                'description' => "Inactive for {$daysSinceLastLogin} days",
            ];
        }

        if ($supportTickets > 10) {
            $factors[] = [
                'type' => 'critical',
                'description' => "Very high support volume ({$supportTickets} tickets)",
            ];
        } elseif ($supportTickets > 5) {
            $factors[] = [
                'type' => 'warning',
                'description' => "High support volume ({$supportTickets} tickets)",
            ];
        }

        if ($paymentFailed) {
            $factors[] = [
                'type' => 'critical',
                'description' => 'Payment failed',
            ];
        }

        if ($daysSinceSignup < 30) {
            $factors[] = [
                'type' => 'info',
                'description' => 'New customer (higher churn risk)',
            ];
        }

        return $factors;
    }

    /**
     * Get actionable recommendations based on risk assessment.
     * 
     * @param string $riskLevel Risk level (low, medium, high)
     * @param array $factors Contributing risk factors
     * @return array Recommended actions
     */
    private function getRecommendations(string $riskLevel, array $factors): array
    {
        $recommendations = [];

        if ($riskLevel === 'high') {
            $recommendations[] = 'Immediate intervention required';
            $recommendations[] = 'Contact customer within 24 hours';
            $recommendations[] = 'Offer personalized incentive or discount';
        }

        foreach ($factors as $factor) {
            if (str_contains($factor['description'], 'Inactive')) {
                $recommendations[] = 'Send re-engagement email campaign';
            }
            if (str_contains($factor['description'], 'support')) {
                $recommendations[] = 'Schedule customer success call';
            }
            if (str_contains($factor['description'], 'Payment failed')) {
                $recommendations[] = 'Update payment method immediately';
            }
        }

        return array_unique($recommendations);
    }

    /**
     * Batch analyze churn risk for multiple customers.
     * 
     * @param array $customerIds Array of customer IDs
     * @return array Array of risk assessments
     */
    public function batchAnalyze(array $customerIds): array
    {
        return array_map(
            fn($id) => $this->calculateChurnRisk($id),
            $customerIds
        );
    }
}

// Example usage:
// $predictor = new ChurnPredictor();
// $risk = $predictor->calculateChurnRisk(customerId: 12345);
// 
// echo "Customer {$risk['customer_id']} Risk Assessment:\n";
// echo "Risk Level: {$risk['risk_level']} ({$risk['risk_score']}/100)\n";
// echo "\nRisk Factors:\n";
// foreach ($risk['factors'] as $factor) {
//     echo "- [{$factor['type']}] {$factor['description']}\n";
// }
// echo "\nRecommended Actions:\n";
// foreach ($risk['recommendations'] as $action) {
//     echo "- {$action}\n";
// }
