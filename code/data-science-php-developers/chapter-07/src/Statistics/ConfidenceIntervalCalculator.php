<?php

declare(strict_types=1);

namespace DataScience\Statistics;

use MathPHP\Statistics\Distribution\Continuous\StudentT;
use MathPHP\Statistics\Distribution\Continuous\Normal;

class ConfidenceIntervalCalculator
{
    /**
     * Calculate confidence interval for mean
     * 
     * @param array<int|float> $data Numerical data
     * @param float $confidenceLevel Confidence level (default 0.95 for 95% CI)
     * @return array{mean: float, std_dev: float, std_error: float, confidence_level: float, margin_of_error: float, lower_bound: float, upper_bound: float, sample_size: int, distribution: string}
     * @throws \InvalidArgumentException If data is insufficient or confidence level is invalid
     */
    public function forMean(
        array $data,
        float $confidenceLevel = 0.95
    ): array {
        if ($confidenceLevel <= 0 || $confidenceLevel >= 1) {
            throw new \InvalidArgumentException('Confidence level must be between 0 and 1');
        }
        
        $n = count($data);
        
        if ($n < 2) {
            throw new \InvalidArgumentException('Need at least 2 data points');
        }
        
        // Validate all elements are numeric
        foreach ($data as $value) {
            if (!is_numeric($value)) {
                throw new \InvalidArgumentException('All data points must be numeric');
            }
        }
        
        $mean = array_sum($data) / $n;
        $variance = array_sum(array_map(fn($x) => ($x - $mean) ** 2, $data)) / ($n - 1);
        $stdDev = sqrt($variance);
        $standardError = $stdDev / sqrt($n);
        
        // Use t-distribution for small samples, normal for large
        if ($n < 30) {
            $t = new StudentT($n - 1);
            $alpha = 1 - $confidenceLevel;
            $criticalValue = $t->inverse(1 - $alpha / 2);
        } else {
            $normal = new Normal(0, 1);
            $alpha = 1 - $confidenceLevel;
            $criticalValue = $normal->inverse(1 - $alpha / 2);
        }
        
        $marginOfError = $criticalValue * $standardError;
        
        return [
            'mean' => $mean,
            'std_dev' => $stdDev,
            'std_error' => $standardError,
            'confidence_level' => $confidenceLevel,
            'margin_of_error' => $marginOfError,
            'lower_bound' => $mean - $marginOfError,
            'upper_bound' => $mean + $marginOfError,
            'sample_size' => $n,
            'distribution' => $n < 30 ? 't' : 'normal',
        ];
    }
    
    /**
     * Calculate confidence interval for proportion
     * 
     * @param int $successes Number of successes
     * @param int $total Total number of trials
     * @param float $confidenceLevel Confidence level (default 0.95)
     * @return array{proportion: float, percentage: float, std_error: float, confidence_level: float, margin_of_error: float, lower_bound: float, upper_bound: float, sample_size: int, successes: int}
     * @throws \InvalidArgumentException If parameters are invalid
     */
    public function forProportion(
        int $successes,
        int $total,
        float $confidenceLevel = 0.95
    ): array {
        if ($confidenceLevel <= 0 || $confidenceLevel >= 1) {
            throw new \InvalidArgumentException('Confidence level must be between 0 and 1');
        }
        
        if ($total < 1) {
            throw new \InvalidArgumentException('Total must be at least 1');
        }
        
        if ($successes < 0 || $successes > $total) {
            throw new \InvalidArgumentException('Successes must be between 0 and total');
        }
        
        // Check if sample size is sufficient for normal approximation
        $minSampleSize = 5;
        if ($total < $minSampleSize) {
            throw new \InvalidArgumentException('Sample size too small for normal approximation (need at least 5)');
        }
        
        $proportion = $successes / $total;
        $standardError = sqrt(($proportion * (1 - $proportion)) / $total);
        
        // Use normal approximation (valid when np >= 5 and n(1-p) >= 5)
        $normal = new Normal(0, 1);
        $alpha = 1 - $confidenceLevel;
        $criticalValue = $normal->inverse(1 - $alpha / 2);
        
        $marginOfError = $criticalValue * $standardError;
        
        return [
            'proportion' => $proportion,
            'percentage' => $proportion * 100,
            'std_error' => $standardError,
            'confidence_level' => $confidenceLevel,
            'margin_of_error' => $marginOfError,
            'lower_bound' => max(0, $proportion - $marginOfError),
            'upper_bound' => min(1, $proportion + $marginOfError),
            'sample_size' => $total,
            'successes' => $successes,
        ];
    }
    
    /**
     * Calculate confidence interval for difference between means
     * 
     * @param array<int|float> $group1 First group data
     * @param array<int|float> $group2 Second group data
     * @param float $confidenceLevel Confidence level (default 0.95)
     * @return array{mean1: float, mean2: float, mean_difference: float, std_error: float, confidence_level: float, margin_of_error: float, lower_bound: float, upper_bound: float, degrees_of_freedom: float}
     * @throws \InvalidArgumentException If data is insufficient or invalid
     */
    public function forMeanDifference(
        array $group1,
        array $group2,
        float $confidenceLevel = 0.95
    ): array {
        if ($confidenceLevel <= 0 || $confidenceLevel >= 1) {
            throw new \InvalidArgumentException('Confidence level must be between 0 and 1');
        }
        
        $n1 = count($group1);
        $n2 = count($group2);
        
        if ($n1 < 2 || $n2 < 2) {
            throw new \InvalidArgumentException('Each group needs at least 2 data points');
        }
        
        // Validate all elements are numeric
        foreach ($group1 as $value) {
            if (!is_numeric($value)) {
                throw new \InvalidArgumentException('All data points in group 1 must be numeric');
            }
        }
        foreach ($group2 as $value) {
            if (!is_numeric($value)) {
                throw new \InvalidArgumentException('All data points in group 2 must be numeric');
            }
        }
        
        $mean1 = array_sum($group1) / $n1;
        $mean2 = array_sum($group2) / $n2;
        
        $variance1 = array_sum(array_map(fn($x) => ($x - $mean1) ** 2, $group1)) / ($n1 - 1);
        $variance2 = array_sum(array_map(fn($x) => ($x - $mean2) ** 2, $group2)) / ($n2 - 1);
        
        // Pooled standard error
        $standardError = sqrt(($variance1 / $n1) + ($variance2 / $n2));
        
        // Degrees of freedom (Welch-Satterthwaite equation)
        $df = (($variance1 / $n1) + ($variance2 / $n2)) ** 2 /
              ((($variance1 / $n1) ** 2 / ($n1 - 1)) + (($variance2 / $n2) ** 2 / ($n2 - 1)));
        
        $t = new StudentT((int)round($df));
        $alpha = 1 - $confidenceLevel;
        $criticalValue = $t->inverse(1 - $alpha / 2);
        
        $meanDifference = $mean1 - $mean2;
        $marginOfError = $criticalValue * $standardError;
        
        return [
            'mean1' => $mean1,
            'mean2' => $mean2,
            'mean_difference' => $meanDifference,
            'std_error' => $standardError,
            'confidence_level' => $confidenceLevel,
            'margin_of_error' => $marginOfError,
            'lower_bound' => $meanDifference - $marginOfError,
            'upper_bound' => $meanDifference + $marginOfError,
            'degrees_of_freedom' => $df,
        ];
    }
    
    /**
     * Format confidence interval for display
     * 
     * @param array $ci Confidence interval array
     * @param int $decimals Number of decimal places
     * @return string Formatted string
     */
    public function format(array $ci, int $decimals = 2): string
    {
        if (isset($ci['mean'])) {
            return sprintf(
                "%s ± %s (95%% CI: %s to %s)",
                number_format($ci['mean'], $decimals),
                number_format($ci['margin_of_error'], $decimals),
                number_format($ci['lower_bound'], $decimals),
                number_format($ci['upper_bound'], $decimals)
            );
        } elseif (isset($ci['proportion'])) {
            return sprintf(
                "%s%% ± %s%% (95%% CI: %s%% to %s%%)",
                number_format($ci['percentage'], $decimals),
                number_format($ci['margin_of_error'] * 100, $decimals),
                number_format($ci['lower_bound'] * 100, $decimals),
                number_format($ci['upper_bound'] * 100, $decimals)
            );
        } else {
            return sprintf(
                "%s ± %s (95%% CI: %s to %s)",
                number_format($ci['mean_difference'], $decimals),
                number_format($ci['margin_of_error'], $decimals),
                number_format($ci['lower_bound'], $decimals),
                number_format($ci['upper_bound'], $decimals)
            );
        }
    }
}
