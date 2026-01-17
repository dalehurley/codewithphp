<?php

declare(strict_types=1);

namespace DataScience\Statistics;

use MathPHP\Statistics\Distribution\Continuous\F;

class ANOVAAnalyzer
{
    /**
     * Perform one-way ANOVA
     * 
     * @param array<array<float>> $groups Array of groups, each group is array of values
     * @param float $alpha Significance level
     * @return array{F_statistic: float, df_between: int, df_within: int, p_value: float, significant: bool, interpretation: string, ss_between: float, ss_within: float, ms_between: float, ms_within: float}
     * @throws \InvalidArgumentException If parameters are invalid
     */
    public function oneWayANOVA(array $groups, float $alpha = 0.05): array
    {
        if ($alpha <= 0 || $alpha >= 1) {
            throw new \InvalidArgumentException('Alpha must be between 0 and 1');
        }
        
        if (count($groups) < 2) {
            throw new \InvalidArgumentException('Need at least 2 groups for ANOVA');
        }
        
        // Validate each group has sufficient data
        foreach ($groups as $i => $group) {
            if (count($group) < 2) {
                throw new \InvalidArgumentException("Group {$i} needs at least 2 data points");
            }
            foreach ($group as $value) {
                if (!is_numeric($value)) {
                    throw new \InvalidArgumentException("All data points must be numeric");
                }
            }
        }
        
        $k = count($groups);  // Number of groups
        $N = array_sum(array_map('count', $groups));  // Total sample size
        
        // Calculate overall mean
        $allData = array_merge(...$groups);
        $overallMean = array_sum($allData) / $N;
        
        // Between-group sum of squares
        $SSB = 0;
        foreach ($groups as $group) {
            $groupMean = array_sum($group) / count($group);
            $SSB += count($group) * ($groupMean - $overallMean) ** 2;
        }
        
        // Within-group sum of squares
        $SSW = 0;
        foreach ($groups as $group) {
            $groupMean = array_sum($group) / count($group);
            foreach ($group as $value) {
                $SSW += ($value - $groupMean) ** 2;
            }
        }
        
        // Degrees of freedom
        $dfBetween = $k - 1;
        $dfWithin = $N - $k;
        
        if ($dfBetween < 1 || $dfWithin < 1) {
            throw new \InvalidArgumentException('Insufficient degrees of freedom for ANOVA');
        }
        
        // Mean squares
        $MSB = $SSB / $dfBetween;
        $MSW = $SSW / $dfWithin;
        
        // F-statistic (prevent division by zero)
        if ($MSW === 0.0) {
            $F = 0.0;
            $pValue = 1.0;
        } else {
            $F = $MSB / $MSW;
            
            // Calculate p-value using F-distribution
            $pValue = $this->calculateFPValue($F, $dfBetween, $dfWithin);
        }
        
        return [
            'F_statistic' => $F,
            'df_between' => $dfBetween,
            'df_within' => $dfWithin,
            'ss_between' => $SSB,
            'ss_within' => $SSW,
            'ms_between' => $MSB,
            'ms_within' => $MSW,
            'p_value' => $pValue,
            'significant' => $pValue < $alpha,
            'interpretation' => $this->interpretANOVA($pValue, $alpha),
        ];
    }
    
    /**
     * Calculate p-value from F-distribution
     * 
     * @param float $F F-statistic
     * @param int $df1 Degrees of freedom (numerator)
     * @param int $df2 Degrees of freedom (denominator)
     * @return float P-value
     */
    private function calculateFPValue(float $F, int $df1, int $df2): float
    {
        try {
            $fDist = new F($df1, $df2);
            return 1 - $fDist->cdf($F);
        } catch (\Exception $e) {
            // If F-distribution calculation fails, return conservative p-value
            return 1.0;
        }
    }
    
    /**
     * Interpret ANOVA results
     * 
     * @param float $pValue P-value from test
     * @param float $alpha Significance level
     * @return string Interpretation
     */
    private function interpretANOVA(float $pValue, float $alpha): string
    {
        if ($pValue < $alpha) {
            return "Significant difference between groups (p = " . round($pValue, 4) . "). " .
                   "At least one group mean differs significantly from the others.";
        }
        return "No significant difference between groups (p = " . round($pValue, 4) . "). " .
               "Group means are not significantly different.";
    }
    
    /**
     * Calculate eta-squared effect size for ANOVA
     * 
     * @param float $ssb Sum of squares between groups
     * @param float $ssw Sum of squares within groups
     * @return array{eta_squared: float, effect_size: string, interpretation: string}
     */
    public function calculateEtaSquared(float $ssb, float $ssw): array
    {
        $sst = $ssb + $ssw;
        
        if ($sst === 0.0) {
            return [
                'eta_squared' => 0.0,
                'effect_size' => 'none',
                'interpretation' => 'No variance explained by group differences',
            ];
        }
        
        $etaSquared = $ssb / $sst;
        
        return [
            'eta_squared' => $etaSquared,
            'effect_size' => $this->interpretEtaSquared($etaSquared),
            'interpretation' => sprintf(
                "Groups explain %.1f%% of the total variance",
                $etaSquared * 100
            ),
        ];
    }
    
    /**
     * Interpret eta-squared effect size
     * 
     * @param float $etaSquared Eta-squared value
     * @return string Effect size category
     */
    private function interpretEtaSquared(float $etaSquared): string
    {
        if ($etaSquared < 0.01) {
            return 'negligible';
        } elseif ($etaSquared < 0.06) {
            return 'small';
        } elseif ($etaSquared < 0.14) {
            return 'medium';
        } else {
            return 'large';
        }
    }
}
