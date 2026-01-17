<?php

declare(strict_types=1);

namespace DataScience\Statistics;

use DataScience\Statistics\HypothesisTester;
use DataScience\Statistics\ConfidenceIntervalCalculator;

class ABTestAnalyzer
{
    public function __construct(
        private HypothesisTester $tester = new HypothesisTester(),
        private ConfidenceIntervalCalculator $ciCalculator = new ConfidenceIntervalCalculator()
    ) {}
    
    /**
     * Analyze A/B test results for conversion rates
     * 
     * @param int $controlConversions Number of conversions in control group
     * @param int $controlTotal Total users in control group
     * @param int $variantConversions Number of conversions in variant group
     * @param int $variantTotal Total users in variant group
     * @param float $alpha Significance level
     * @return array Complete A/B test analysis
     * @throws \InvalidArgumentException If parameters are invalid
     */
    public function analyzeConversionTest(
        int $controlConversions,
        int $controlTotal,
        int $variantConversions,
        int $variantTotal,
        float $alpha = 0.05
    ): array {
        // Validate inputs
        if ($alpha <= 0 || $alpha >= 1) {
            throw new \InvalidArgumentException('Alpha must be between 0 and 1');
        }
        
        if ($controlTotal < 1 || $variantTotal < 1) {
            throw new \InvalidArgumentException('Sample sizes must be at least 1');
        }
        
        if ($controlConversions < 0 || $controlConversions > $controlTotal) {
            throw new \InvalidArgumentException('Control conversions must be between 0 and total');
        }
        
        if ($variantConversions < 0 || $variantConversions > $variantTotal) {
            throw new \InvalidArgumentException('Variant conversions must be between 0 and total');
        }
        
        // Check if sample size is sufficient for normal approximation
        $minSampleSize = 5;
        if ($controlTotal < $minSampleSize || $variantTotal < $minSampleSize) {
            throw new \InvalidArgumentException('Sample sizes too small for normal approximation (need at least 5)');
        }
        
        $controlRate = $controlConversions / $controlTotal;
        $variantRate = $variantConversions / $variantTotal;
        
        // Prevent division by zero
        $lift = $controlRate > 0 
            ? (($variantRate - $controlRate) / $controlRate) * 100 
            : 0.0;
        
        // Calculate confidence intervals
        $controlCI = $this->ciCalculator->forProportion($controlConversions, $controlTotal, 0.95);
        $variantCI = $this->ciCalculator->forProportion($variantConversions, $variantTotal, 0.95);
        
        // Perform z-test for proportions
        $pooledRate = ($controlConversions + $variantConversions) / ($controlTotal + $variantTotal);
        $standardError = sqrt($pooledRate * (1 - $pooledRate) * ((1 / $controlTotal) + (1 / $variantTotal)));
        
        // Prevent division by zero
        if ($standardError === 0.0) {
            $zStatistic = 0.0;
            $pValue = 1.0;
        } else {
            $zStatistic = ($variantRate - $controlRate) / $standardError;
            
            // Calculate p-value (two-tailed)
            $normal = new \MathPHP\Statistics\Distribution\Continuous\Normal(0, 1);
            $pValue = 2 * (1 - $normal->cdf(abs($zStatistic)));
        }
        
        $isSignificant = $pValue < $alpha;
        
        return [
            'control' => [
                'conversions' => $controlConversions,
                'total' => $controlTotal,
                'rate' => $controlRate,
                'percentage' => $controlRate * 100,
                'ci_lower' => $controlCI['lower_bound'] * 100,
                'ci_upper' => $controlCI['upper_bound'] * 100,
            ],
            'variant' => [
                'conversions' => $variantConversions,
                'total' => $variantTotal,
                'rate' => $variantRate,
                'percentage' => $variantRate * 100,
                'ci_lower' => $variantCI['lower_bound'] * 100,
                'ci_upper' => $variantCI['upper_bound'] * 100,
            ],
            'analysis' => [
                'lift' => $lift,
                'absolute_difference' => ($variantRate - $controlRate) * 100,
                'z_statistic' => $zStatistic,
                'p_value' => $pValue,
                'is_significant' => $isSignificant,
                'confidence_level' => (1 - $alpha) * 100,
            ],
            'recommendation' => $this->makeRecommendation($lift, $isSignificant, $pValue),
        ];
    }
    
    /**
     * Calculate required sample size for A/B test
     * 
     * @param float $baselineRate Current conversion rate
     * @param float $minimumDetectableEffect Minimum effect to detect (as proportion, e.g., 0.10 for 10%)
     * @param float $alpha Significance level
     * @param float $power Statistical power
     * @return array Sample size requirements
     * @throws \InvalidArgumentException If parameters are invalid
     */
    public function calculateSampleSize(
        float $baselineRate,
        float $minimumDetectableEffect,
        float $alpha = 0.05,
        float $power = 0.80
    ): array {
        if ($baselineRate <= 0 || $baselineRate >= 1) {
            throw new \InvalidArgumentException('Baseline rate must be between 0 and 1');
        }
        
        if ($minimumDetectableEffect <= 0) {
            throw new \InvalidArgumentException('Minimum detectable effect must be positive');
        }
        
        if ($alpha <= 0 || $alpha >= 1) {
            throw new \InvalidArgumentException('Alpha must be between 0 and 1');
        }
        
        if ($power <= 0 || $power >= 1) {
            throw new \InvalidArgumentException('Power must be between 0 and 1');
        }
        
        // Calculate required sample size per group
        $normal = new \MathPHP\Statistics\Distribution\Continuous\Normal(0, 1);
        
        $zAlpha = $normal->inverse(1 - $alpha / 2);
        $zBeta = $normal->inverse($power);
        
        $p1 = $baselineRate;
        $p2 = $baselineRate * (1 + $minimumDetectableEffect);
        
        // Ensure p2 is valid probability
        if ($p2 > 1) {
            $p2 = 1.0;
        }
        
        $pooledP = ($p1 + $p2) / 2;
        
        $n = (($zAlpha * sqrt(2 * $pooledP * (1 - $pooledP))) + 
              ($zBeta * sqrt($p1 * (1 - $p1) + $p2 * (1 - $p2)))) ** 2 /
             ($p1 - $p2) ** 2;
        
        $samplePerGroup = (int)ceil($n);
        
        return [
            'sample_per_group' => $samplePerGroup,
            'total_sample' => $samplePerGroup * 2,
            'baseline_rate' => $baselineRate * 100,
            'expected_variant_rate' => $p2 * 100,
            'minimum_detectable_effect' => $minimumDetectableEffect * 100,
            'alpha' => $alpha,
            'power' => $power,
            'interpretation' => "You need {$samplePerGroup} users in each group " .
                                "({$samplePerGroup} control + {$samplePerGroup} variant) " .
                                "to detect a " . round($minimumDetectableEffect * 100, 1) . "% " .
                                "change with " . round($power * 100) . "% power.",
        ];
    }
    
    /**
     * Analyze A/B test with continuous metrics (e.g., revenue, time)
     * 
     * @param array<int|float> $controlData Control group measurements
     * @param array<int|float> $variantData Variant group measurements
     * @param float $alpha Significance level
     * @return array Complete analysis results
     * @throws \InvalidArgumentException If parameters are invalid
     */
    public function analyzeContinuousTest(
        array $controlData,
        array $variantData,
        float $alpha = 0.05
    ): array {
        if ($alpha <= 0 || $alpha >= 1) {
            throw new \InvalidArgumentException('Alpha must be between 0 and 1');
        }
        
        if (count($controlData) < 2 || count($variantData) < 2) {
            throw new \InvalidArgumentException('Each group needs at least 2 data points');
        }
        
        // Perform t-test
        $tTestResult = $this->tester->twoSampleTTest($controlData, $variantData, $alpha);
        
        // Calculate confidence interval for difference
        $ciResult = $this->ciCalculator->forMeanDifference($controlData, $variantData, 0.95);
        
        $controlMean = array_sum($controlData) / count($controlData);
        $variantMean = array_sum($variantData) / count($variantData);
        
        // Prevent division by zero
        $lift = $controlMean > 0 
            ? (($variantMean - $controlMean) / $controlMean) * 100 
            : 0.0;
        
        return [
            'control' => [
                'n' => count($controlData),
                'mean' => $controlMean,
                'std_dev' => sqrt(array_sum(array_map(fn($x) => ($x - $controlMean) ** 2, $controlData)) / count($controlData)),
            ],
            'variant' => [
                'n' => count($variantData),
                'mean' => $variantMean,
                'std_dev' => sqrt(array_sum(array_map(fn($x) => ($x - $variantMean) ** 2, $variantData)) / count($variantData)),
            ],
            'analysis' => [
                'difference' => $variantMean - $controlMean,
                'lift' => $lift,
                'ci_lower' => $ciResult['lower_bound'],
                'ci_upper' => $ciResult['upper_bound'],
                't_statistic' => $tTestResult['t_statistic'],
                'p_value' => $tTestResult['p_value'],
                'is_significant' => $tTestResult['significant'],
            ],
            'recommendation' => $this->makeRecommendation($lift, $tTestResult['significant'], $tTestResult['p_value']),
        ];
    }
    
    /**
     * Make recommendation based on results
     * 
     * @param float $lift Percentage lift
     * @param bool $isSignificant Whether result is statistically significant
     * @param float $pValue P-value from test
     * @return string Recommendation
     */
    private function makeRecommendation(
        float $lift,
        bool $isSignificant,
        float $pValue
    ): string {
        if (!$isSignificant) {
            return "No significant difference detected. Consider running test longer or " .
                   "increasing sample size. Current p-value: " . round($pValue, 4);
        }
        
        if ($lift > 0) {
            return "✅ WINNER: Variant performs significantly better with " .
                   round(abs($lift), 1) . "% improvement. Recommend rolling out to all users.";
        } else {
            return "⚠️ WARNING: Variant performs significantly worse with " .
                   round(abs($lift), 1) . "% decrease. Recommend keeping control version.";
        }
    }
}
