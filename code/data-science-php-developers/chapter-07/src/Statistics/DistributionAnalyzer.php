<?php

declare(strict_types=1);

namespace DataScience\Statistics;

use MathPHP\Statistics\Distribution\Continuous\Normal;
use MathPHP\Statistics\Distribution\Discrete\Binomial;
use MathPHP\Statistics\Distribution\Discrete\Poisson;

class DistributionAnalyzer
{
    /**
     * Test if data follows normal distribution (Shapiro-Wilk test approximation)
     * 
     * @param array<int|float> $data Numerical data to test
     * @param float $alpha Significance level (default 0.05)
     * @return array{is_normal: bool, mean?: float, std_dev?: float, skewness?: float, kurtosis?: float, interpretation?: string, reason?: string}
     * @throws \InvalidArgumentException If data is invalid
     */
    public function isNormallyDistributed(array $data, float $alpha = 0.05): array
    {
        // Validate alpha
        if ($alpha <= 0 || $alpha >= 1) {
            throw new \InvalidArgumentException('Alpha must be between 0 and 1');
        }
        
        // Validate all elements are numeric
        foreach ($data as $value) {
            if (!is_numeric($value)) {
                throw new \InvalidArgumentException('All data points must be numeric');
            }
        }
        
        $n = count($data);
        
        if ($n < 3) {
            return [
                'is_normal' => false,
                'reason' => 'Insufficient data (need at least 3 values)',
            ];
        }
        
        // Calculate mean and std dev
        $mean = array_sum($data) / $n;
        $variance = array_sum(array_map(fn($x) => ($x - $mean) ** 2, $data)) / ($n - 1);
        $stdDev = sqrt($variance);
        
        if ($stdDev === 0.0) {
            return [
                'is_normal' => false,
                'reason' => 'No variation in data (all values identical)',
                'mean' => $mean,
                'std_dev' => $stdDev,
            ];
        }
        
        // Calculate skewness and kurtosis
        $skewness = $this->calculateSkewness($data, $mean, $stdDev);
        $kurtosis = $this->calculateKurtosis($data, $mean, $stdDev);
        
        // Simple normality check: skewness and kurtosis should be near 0 and 3
        $isNormal = abs($skewness) < 2 && abs($kurtosis - 3) < 4;
        
        return [
            'is_normal' => $isNormal,
            'mean' => $mean,
            'std_dev' => $stdDev,
            'skewness' => $skewness,
            'kurtosis' => $kurtosis,
            'interpretation' => $this->interpretNormality($skewness, $kurtosis),
        ];
    }
    
    /**
     * Calculate probability for normal distribution
     * 
     * @param float $x Value to evaluate
     * @param float $mean Mean of distribution
     * @param float $stdDev Standard deviation of distribution
     * @return array{pdf: float, cdf: float, percentile: float}
     * @throws \InvalidArgumentException If standard deviation is zero or negative
     */
    public function normalProbability(
        float $x,
        float $mean,
        float $stdDev
    ): array {
        if ($stdDev <= 0) {
            throw new \InvalidArgumentException('Standard deviation must be positive');
        }
        
        $normal = new Normal($mean, $stdDev);
        
        return [
            'pdf' => $normal->pdf($x), // Probability density
            'cdf' => $normal->cdf($x), // Cumulative probability (P(X <= x))
            'percentile' => $normal->cdf($x) * 100,
        ];
    }
    
    /**
     * Calculate z-score (standard score)
     * 
     * @param float $value Value to evaluate
     * @param float $mean Mean of distribution
     * @param float $stdDev Standard deviation of distribution
     * @return array{z_score: float, percentile: float, interpretation: string}
     * @throws \InvalidArgumentException If standard deviation is zero
     */
    public function zScore(float $value, float $mean, float $stdDev): array
    {
        if ($stdDev === 0.0) {
            throw new \InvalidArgumentException('Standard deviation cannot be zero');
        }
        
        $z = ($value - $mean) / $stdDev;
        
        // Calculate probability using standard normal distribution
        $normal = new Normal(0, 1);
        $probability = $normal->cdf($z);
        
        return [
            'z_score' => $z,
            'percentile' => $probability * 100,
            'interpretation' => $this->interpretZScore($z),
        ];
    }
    
    /**
     * Calculate binomial probability (n trials, k successes, p probability)
     * 
     * @param int $n Number of trials
     * @param int $k Number of successes
     * @param float $p Probability of success
     * @return array{probability: float, cumulative: float, expected_value: float, variance: float}
     * @throws \InvalidArgumentException If parameters are invalid
     */
    public function binomialProbability(
        int $n,
        int $k,
        float $p
    ): array {
        if ($n < 1) {
            throw new \InvalidArgumentException('Number of trials must be at least 1');
        }
        
        if ($k < 0 || $k > $n) {
            throw new \InvalidArgumentException('Number of successes must be between 0 and n');
        }
        
        if ($p < 0 || $p > 1) {
            throw new \InvalidArgumentException('Probability must be between 0 and 1');
        }
        
        $binomial = new Binomial($n, $p);
        
        return [
            'probability' => $binomial->pmf($k), // P(X = k)
            'cumulative' => $binomial->cdf($k),  // P(X <= k)
            'expected_value' => $n * $p,
            'variance' => $n * $p * (1 - $p),
        ];
    }
    
    /**
     * Calculate Poisson probability (events in interval)
     * 
     * @param float $lambda Average rate of events
     * @param int $k Number of events to evaluate
     * @return array{probability: float, cumulative: float, expected_value: float, variance: float}
     * @throws \InvalidArgumentException If parameters are invalid
     */
    public function poissonProbability(float $lambda, int $k): array
    {
        if ($lambda <= 0) {
            throw new \InvalidArgumentException('Lambda (rate) must be positive');
        }
        
        if ($k < 0) {
            throw new \InvalidArgumentException('Number of events must be non-negative');
        }
        
        $poisson = new Poisson($lambda);
        
        return [
            'probability' => $poisson->pmf($k), // P(X = k)
            'cumulative' => $poisson->cdf($k),  // P(X <= k)
            'expected_value' => $lambda,
            'variance' => $lambda,
        ];
    }
    
    /**
     * Generate normal distribution samples
     * 
     * @param int $n Number of samples to generate
     * @param float $mean Mean of distribution
     * @param float $stdDev Standard deviation of distribution
     * @return array<float> Generated samples
     * @throws \InvalidArgumentException If parameters are invalid
     */
    public function generateNormalSamples(
        int $n,
        float $mean,
        float $stdDev
    ): array {
        if ($n < 1) {
            throw new \InvalidArgumentException('Number of samples must be at least 1');
        }
        
        if ($stdDev <= 0) {
            throw new \InvalidArgumentException('Standard deviation must be positive');
        }
        
        // Use generator for memory efficiency with large datasets
        if ($n > 100000) {
            return iterator_to_array($this->generateNormalSamplesGenerator($n, $mean, $stdDev));
        }
        
        $samples = [];
        
        for ($i = 0; $i < $n; $i++) {
            // Box-Muller transform for normal random variables
            $u1 = mt_rand() / mt_getrandmax();
            $u2 = mt_rand() / mt_getrandmax();
            
            $z = sqrt(-2 * log($u1)) * cos(2 * M_PI * $u2);
            $samples[] = $mean + ($z * $stdDev);
        }
        
        return $samples;
    }
    
    /**
     * Generator for normal samples (memory efficient for large N)
     * 
     * @param int $n Number of samples
     * @param float $mean Mean of distribution
     * @param float $stdDev Standard deviation
     * @return \Generator<float>
     */
    private function generateNormalSamplesGenerator(int $n, float $mean, float $stdDev): \Generator
    {
        for ($i = 0; $i < $n; $i++) {
            $u1 = mt_rand() / mt_getrandmax();
            $u2 = mt_rand() / mt_getrandmax();
            $z = sqrt(-2 * log($u1)) * cos(2 * M_PI * $u2);
            yield $mean + ($z * $stdDev);
        }
    }
    
    /**
     * Calculate skewness
     * 
     * @param array<int|float> $data Numerical data
     * @param float $mean Mean of data
     * @param float $stdDev Standard deviation of data
     * @return float Skewness value
     * @throws \InvalidArgumentException If insufficient data
     */
    private function calculateSkewness(array $data, float $mean, float $stdDev): float
    {
        $n = count($data);
        
        if ($n < 3) {
            throw new \InvalidArgumentException('Need at least 3 data points to calculate skewness');
        }
        
        if ($stdDev === 0.0) {
            return 0.0;
        }
        
        $sum = array_sum(array_map(
            fn($x) => (($x - $mean) / $stdDev) ** 3,
            $data
        ));
        
        return ($n / (($n - 1) * ($n - 2))) * $sum;
    }
    
    /**
     * Calculate kurtosis
     * 
     * @param array<int|float> $data Numerical data
     * @param float $mean Mean of data
     * @param float $stdDev Standard deviation of data
     * @return float Kurtosis value
     * @throws \InvalidArgumentException If insufficient data
     */
    private function calculateKurtosis(array $data, float $mean, float $stdDev): float
    {
        $n = count($data);
        
        if ($n < 4) {
            throw new \InvalidArgumentException('Need at least 4 data points to calculate kurtosis');
        }
        
        if ($stdDev === 0.0) {
            return 0.0;
        }
        
        $sum = array_sum(array_map(
            fn($x) => (($x - $mean) / $stdDev) ** 4,
            $data
        ));
        
        return (($n * ($n + 1)) / (($n - 1) * ($n - 2) * ($n - 3))) * $sum - 
               (3 * ($n - 1) ** 2) / (($n - 2) * ($n - 3));
    }
    
    /**
     * Interpret normality test results
     * 
     * @param float $skewness Skewness value
     * @param float $kurtosis Kurtosis value
     * @return string Interpretation
     */
    private function interpretNormality(float $skewness, float $kurtosis): string
    {
        if (abs($skewness) < 0.5 && abs($kurtosis - 3) < 1) {
            return 'Data appears normally distributed';
        } elseif (abs($skewness) >= 2) {
            return 'Data is highly skewed (not normal)';
        } elseif (abs($kurtosis - 3) >= 4) {
            return 'Data has extreme outliers (not normal)';
        } else {
            return 'Data is approximately normal';
        }
    }
    
    /**
     * Interpret z-score
     * 
     * @param float $z Z-score value
     * @return string Interpretation
     */
    private function interpretZScore(float $z): string
    {
        $absZ = abs($z);
        
        if ($absZ < 1) {
            return 'Within 1 standard deviation (common)';
        } elseif ($absZ < 2) {
            return 'Within 2 standard deviations (typical)';
        } elseif ($absZ < 3) {
            return 'Within 3 standard deviations (unusual)';
        } else {
            return 'Beyond 3 standard deviations (very rare)';
        }
    }
}
