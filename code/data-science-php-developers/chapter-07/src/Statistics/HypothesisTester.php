<?php

declare(strict_types=1);

namespace DataScience\Statistics;

use MathPHP\Statistics\Distribution\Continuous\StudentT;
use MathPHP\Statistics\Distribution\Continuous\Normal;
use MathPHP\Statistics\Distribution\Continuous\ChiSquared;

class HypothesisTester
{
    /**
     * Perform one-sample t-test
     * 
     * @param array<int|float> $data Sample data
     * @param float $populationMean Hypothesized population mean
     * @param float $alpha Significance level
     * @return array Test results including statistic, p-value, and interpretation
     * @throws \InvalidArgumentException If parameters are invalid
     */
    public function oneSampleTTest(
        array $data,
        float $populationMean,
        float $alpha = 0.05
    ): array {
        if ($alpha <= 0 || $alpha >= 1) {
            throw new \InvalidArgumentException('Alpha must be between 0 and 1');
        }
        
        $n = count($data);
        
        if ($n < 2) {
            throw new \InvalidArgumentException('Need at least 2 data points for t-test');
        }
        
        // Validate all elements are numeric
        foreach ($data as $value) {
            if (!is_numeric($value)) {
                throw new \InvalidArgumentException('All data points must be numeric');
            }
        }
        
        $sampleMean = array_sum($data) / $n;
        $variance = array_sum(array_map(fn($x) => ($x - $sampleMean) ** 2, $data)) / ($n - 1);
        $stdDev = sqrt($variance);
        $standardError = $stdDev / sqrt($n);
        
        // Calculate t-statistic
        $t = ($sampleMean - $populationMean) / $standardError;
        
        // Calculate p-value (two-tailed)
        $tDist = new StudentT($n - 1);
        $pValue = 2 * (1 - $tDist->cdf(abs($t)));
        
        return [
            'test' => 'One-sample t-test',
            'sample_mean' => $sampleMean,
            'population_mean' => $populationMean,
            't_statistic' => $t,
            'degrees_of_freedom' => $n - 1,
            'p_value' => $pValue,
            'alpha' => $alpha,
            'significant' => $pValue < $alpha,
            'interpretation' => $this->interpretTTest($pValue, $alpha, $sampleMean, $populationMean),
        ];
    }
    
    /**
     * Perform two-sample t-test (independent samples)
     * 
     * @param array<int|float> $group1 First group data
     * @param array<int|float> $group2 Second group data
     * @param float $alpha Significance level
     * @return array Test results including statistic, p-value, and interpretation
     * @throws \InvalidArgumentException If parameters are invalid
     */
    public function twoSampleTTest(
        array $group1,
        array $group2,
        float $alpha = 0.05
    ): array {
        if ($alpha <= 0 || $alpha >= 1) {
            throw new \InvalidArgumentException('Alpha must be between 0 and 1');
        }
        
        $n1 = count($group1);
        $n2 = count($group2);
        
        if ($n1 < 2 || $n2 < 2) {
            throw new \InvalidArgumentException('Each group needs at least 2 data points for t-test');
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
        
        // Calculate t-statistic
        $t = ($mean1 - $mean2) / $standardError;
        
        // Degrees of freedom (Welch-Satterthwaite)
        $df = (($variance1 / $n1) + ($variance2 / $n2)) ** 2 /
              ((($variance1 / $n1) ** 2 / ($n1 - 1)) + (($variance2 / $n2) ** 2 / ($n2 - 1)));
        
        // Calculate p-value (two-tailed)
        $tDist = new StudentT((int)round($df));
        $pValue = 2 * (1 - $tDist->cdf(abs($t)));
        
        return [
            'test' => 'Two-sample t-test',
            'mean1' => $mean1,
            'mean2' => $mean2,
            'mean_difference' => $mean1 - $mean2,
            't_statistic' => $t,
            'degrees_of_freedom' => $df,
            'p_value' => $pValue,
            'alpha' => $alpha,
            'significant' => $pValue < $alpha,
            'interpretation' => $this->interpretTwoSampleTTest($pValue, $alpha, $mean1, $mean2),
        ];
    }
    
    /**
     * Calculate Cohen's d effect size
     * 
     * @param array<int|float> $group1 First group data
     * @param array<int|float> $group2 Second group data
     * @return array{cohens_d: float, effect_size: string, mean_difference: float, pooled_sd: float}
     * @throws \InvalidArgumentException If parameters are invalid
     */
    public function cohensD(array $group1, array $group2): array
    {
        $n1 = count($group1);
        $n2 = count($group2);
        
        if ($n1 < 2 || $n2 < 2) {
            throw new \InvalidArgumentException('Each group needs at least 2 data points');
        }
        
        $mean1 = array_sum($group1) / $n1;
        $mean2 = array_sum($group2) / $n2;
        
        // Calculate pooled standard deviation
        $variance1 = array_sum(array_map(fn($x) => ($x - $mean1) ** 2, $group1)) / ($n1 - 1);
        $variance2 = array_sum(array_map(fn($x) => ($x - $mean2) ** 2, $group2)) / ($n2 - 1);
        
        $pooledSD = sqrt((($n1 - 1) * $variance1 + ($n2 - 1) * $variance2) / ($n1 + $n2 - 2));
        
        if ($pooledSD === 0.0) {
            return [
                'cohens_d' => 0.0,
                'effect_size' => 'none',
                'mean_difference' => $mean1 - $mean2,
                'pooled_sd' => $pooledSD,
            ];
        }
        
        $d = ($mean1 - $mean2) / $pooledSD;
        
        return [
            'cohens_d' => $d,
            'effect_size' => $this->interpretEffectSize($d),
            'mean_difference' => $mean1 - $mean2,
            'pooled_sd' => $pooledSD,
        ];
    }
    
    /**
     * Perform z-test for proportions
     * 
     * @param int $successes Number of successes
     * @param int $total Total number of trials
     * @param float $expectedProportion Expected proportion
     * @param float $alpha Significance level
     * @return array Test results
     * @throws \InvalidArgumentException If parameters are invalid
     */
    public function zTestProportion(
        int $successes,
        int $total,
        float $expectedProportion,
        float $alpha = 0.05
    ): array {
        if ($alpha <= 0 || $alpha >= 1) {
            throw new \InvalidArgumentException('Alpha must be between 0 and 1');
        }
        
        if ($total < 1) {
            throw new \InvalidArgumentException('Total must be at least 1');
        }
        
        if ($successes < 0 || $successes > $total) {
            throw new \InvalidArgumentException('Successes must be between 0 and total');
        }
        
        if ($expectedProportion < 0 || $expectedProportion > 1) {
            throw new \InvalidArgumentException('Expected proportion must be between 0 and 1');
        }
        
        $observedProportion = $successes / $total;
        $standardError = sqrt(($expectedProportion * (1 - $expectedProportion)) / $total);
        
        // Calculate z-statistic
        $z = ($observedProportion - $expectedProportion) / $standardError;
        
        // Calculate p-value (two-tailed)
        $normal = new Normal(0, 1);
        $pValue = 2 * (1 - $normal->cdf(abs($z)));
        
        return [
            'test' => 'Z-test for proportion',
            'observed_proportion' => $observedProportion,
            'expected_proportion' => $expectedProportion,
            'z_statistic' => $z,
            'p_value' => $pValue,
            'alpha' => $alpha,
            'significant' => $pValue < $alpha,
            'interpretation' => $this->interpretZTest($pValue, $alpha, $observedProportion, $expectedProportion),
        ];
    }
    
    /**
     * Perform chi-square test for independence
     * 
     * @param array<int|float> $observed Observed frequencies
     * @param array<int|float> $expected Expected frequencies
     * @param float $alpha Significance level
     * @return array Test results
     * @throws \InvalidArgumentException If parameters are invalid
     */
    public function chiSquareTest(
        array $observed,
        array $expected,
        float $alpha = 0.05
    ): array {
        if ($alpha <= 0 || $alpha >= 1) {
            throw new \InvalidArgumentException('Alpha must be between 0 and 1');
        }
        
        if (count($observed) !== count($expected)) {
            throw new \InvalidArgumentException('Observed and expected arrays must have same length');
        }
        
        if (count($observed) < 2) {
            throw new \InvalidArgumentException('Need at least 2 categories for chi-square test');
        }
        
        // Calculate chi-square statistic
        $chiSquare = 0;
        for ($i = 0; $i < count($observed); $i++) {
            if ($expected[$i] === 0 || $expected[$i] === 0.0) {
                throw new \InvalidArgumentException('Expected frequencies cannot be zero');
            }
            $chiSquare += (($observed[$i] - $expected[$i]) ** 2) / $expected[$i];
        }
        
        $df = count($observed) - 1;
        
        // Calculate p-value
        $chiDist = new ChiSquared($df);
        $pValue = 1 - $chiDist->cdf($chiSquare);
        
        return [
            'test' => 'Chi-square test',
            'chi_square_statistic' => $chiSquare,
            'degrees_of_freedom' => $df,
            'p_value' => $pValue,
            'alpha' => $alpha,
            'significant' => $pValue < $alpha,
            'interpretation' => $this->interpretChiSquare($pValue, $alpha),
        ];
    }
    
    /**
     * Calculate statistical power
     * 
     * @param float $effectSize Effect size (Cohen's d)
     * @param int $sampleSize Sample size per group
     * @param float $alpha Significance level
     * @return array Power analysis results
     * @throws \InvalidArgumentException If parameters are invalid
     */
    public function calculatePower(
        float $effectSize,
        int $sampleSize,
        float $alpha = 0.05
    ): array {
        if ($alpha <= 0 || $alpha >= 1) {
            throw new \InvalidArgumentException('Alpha must be between 0 and 1');
        }
        
        if ($sampleSize < 2) {
            throw new \InvalidArgumentException('Sample size must be at least 2');
        }
        
        // Simplified power calculation for t-test
        $normal = new Normal(0, 1);
        $criticalValue = $normal->inverse(1 - $alpha / 2);
        
        $noncentrality = $effectSize * sqrt($sampleSize);
        $power = 1 - $normal->cdf($criticalValue - $noncentrality);
        
        return [
            'effect_size' => $effectSize,
            'sample_size' => $sampleSize,
            'alpha' => $alpha,
            'power' => $power,
            'interpretation' => $this->interpretPower($power),
        ];
    }
    
    /**
     * Interpret effect size
     * 
     * @param float $d Cohen's d value
     * @return string Effect size interpretation
     */
    private function interpretEffectSize(float $d): string
    {
        $absD = abs($d);
        
        if ($absD < 0.2) {
            return 'negligible';
        } elseif ($absD < 0.5) {
            return 'small';
        } elseif ($absD < 0.8) {
            return 'medium';
        } else {
            return 'large';
        }
    }
    
    /**
     * Interpret t-test results
     */
    private function interpretTTest(
        float $pValue,
        float $alpha,
        float $sampleMean,
        float $populationMean
    ): string {
        if ($pValue < $alpha) {
            $direction = $sampleMean > $populationMean ? 'greater' : 'less';
            return "Significant difference (p = " . round($pValue, 4) . "). " .
                   "Sample mean is significantly {$direction} than population mean.";
        } else {
            return "No significant difference (p = " . round($pValue, 4) . "). " .
                   "Cannot reject null hypothesis.";
        }
    }
    
    /**
     * Interpret two-sample t-test results
     */
    private function interpretTwoSampleTTest(
        float $pValue,
        float $alpha,
        float $mean1,
        float $mean2
    ): string {
        if ($pValue < $alpha) {
            $direction = $mean1 > $mean2 ? 'Group 1 > Group 2' : 'Group 2 > Group 1';
            return "Significant difference (p = " . round($pValue, 4) . "). {$direction}";
        } else {
            return "No significant difference (p = " . round($pValue, 4) . "). " .
                   "Groups are not significantly different.";
        }
    }
    
    /**
     * Interpret z-test results
     */
    private function interpretZTest(
        float $pValue,
        float $alpha,
        float $observed,
        float $expected
    ): string {
        if ($pValue < $alpha) {
            $direction = $observed > $expected ? 'higher' : 'lower';
            return "Significant difference (p = " . round($pValue, 4) . "). " .
                   "Observed proportion is significantly {$direction} than expected.";
        } else {
            return "No significant difference (p = " . round($pValue, 4) . "). " .
                   "Observed proportion is consistent with expected.";
        }
    }
    
    /**
     * Interpret chi-square test results
     */
    private function interpretChiSquare(float $pValue, float $alpha): string
    {
        if ($pValue < $alpha) {
            return "Significant association (p = " . round($pValue, 4) . "). " .
                   "Observed frequencies differ significantly from expected.";
        } else {
            return "No significant association (p = " . round($pValue, 4) . "). " .
                   "Observed frequencies are consistent with expected.";
        }
    }
    
    /**
     * Interpret statistical power
     */
    private function interpretPower(float $power): string
    {
        if ($power >= 0.8) {
            return "Good power (" . round($power * 100, 1) . "%). " .
                   "Study has sufficient power to detect effects.";
        } elseif ($power >= 0.5) {
            return "Moderate power (" . round($power * 100, 1) . "%). " .
                   "Consider increasing sample size.";
        } else {
            return "Low power (" . round($power * 100, 1) . "%). " .
                   "Study is underpowered—increase sample size.";
        }
    }
}
