<?php

declare(strict_types=1);

namespace DataScience\Analysis;

use MathPHP\Statistics\Descriptive;
use MathPHP\Statistics\Average;

class StatisticalAnalyzer
{
    /**
     * Calculate comprehensive statistics for a numeric column
     */
    public function analyzeColumn(array $data, string $column): array
    {
        $values = $this->extractNumericValues($data, $column);
        
        if (empty($values)) {
            return ['error' => 'No numeric values found'];
        }
        
        sort($values);
        
        return [
            'count' => count($values),
            'sum' => array_sum($values),
            'mean' => Average::mean($values),
            'median' => Average::median($values),
            'mode' => $this->calculateMode($values),
            'min' => min($values),
            'max' => max($values),
            'range' => max($values) - min($values),
            'variance' => Descriptive::variance($values),
            'std_dev' => Descriptive::standardDeviation($values),
            'quartiles' => $this->calculateQuartiles($values),
            'iqr' => $this->calculateIQR($values),
            'skewness' => $this->calculateSkewness($values),
            'kurtosis' => $this->calculateKurtosis($values),
        ];
    }
    
    /**
     * Calculate statistics for all numeric columns
     */
    public function analyzeDataset(array $data): array
    {
        if (empty($data)) {
            return [];
        }
        
        $stats = [];
        $columns = array_keys($data[0]);
        
        foreach ($columns as $column) {
            $values = $this->extractNumericValues($data, $column);
            
            if (!empty($values)) {
                $stats[$column] = $this->analyzeColumn($data, $column);
            }
        }
        
        return $stats;
    }
    
    /**
     * Generate five-number summary (min, Q1, median, Q3, max)
     */
    public function fiveNumberSummary(array $data, string $column): array
    {
        $values = $this->extractNumericValues($data, $column);
        
        if (empty($values)) {
            return [];
        }
        
        sort($values);
        $quartiles = $this->calculateQuartiles($values);
        
        return [
            'min' => min($values),
            'q1' => $quartiles['q1'],
            'median' => Average::median($values),
            'q3' => $quartiles['q3'],
            'max' => max($values),
        ];
    }
    
    /**
     * Calculate frequency distribution
     */
    public function frequencyDistribution(
        array $data,
        string $column,
        int $bins = 10
    ): array {
        $values = $this->extractNumericValues($data, $column);
        
        if (empty($values)) {
            return [];
        }
        
        $min = min($values);
        $max = max($values);
        $binWidth = ($max - $min) / $bins;
        
        $distribution = array_fill(0, $bins, 0);
        $binRanges = [];
        
        for ($i = 0; $i < $bins; $i++) {
            $lower = $min + ($i * $binWidth);
            $upper = $min + (($i + 1) * $binWidth);
            $binRanges[$i] = [
                'lower' => round($lower, 2),
                'upper' => round($upper, 2),
                'count' => 0,
            ];
        }
        
        foreach ($values as $value) {
            $binIndex = min((int)(($value - $min) / $binWidth), $bins - 1);
            $binRanges[$binIndex]['count']++;
        }
        
        return $binRanges;
    }
    
    /**
     * Calculate categorical frequency
     */
    public function categoricalFrequency(array $data, string $column): array
    {
        $values = array_column($data, $column);
        $counts = array_count_values($values);
        arsort($counts);
        
        $total = count($values);
        $frequency = [];
        
        foreach ($counts as $value => $count) {
            $frequency[] = [
                'value' => $value,
                'count' => $count,
                'percentage' => round(($count / $total) * 100, 2),
            ];
        }
        
        return $frequency;
    }
    
    /**
     * Extract numeric values from column
     */
    private function extractNumericValues(array $data, string $column): array
    {
        return array_filter(
            array_column($data, $column),
            fn($v) => is_numeric($v)
        );
    }
    
    /**
     * Calculate mode (most frequent value)
     */
    private function calculateMode(array $values): mixed
    {
        $counts = array_count_values($values);
        arsort($counts);
        return array_key_first($counts);
    }
    
    /**
     * Calculate quartiles
     */
    private function calculateQuartiles(array $sortedValues): array
    {
        return [
            'q1' => $this->percentile($sortedValues, 25),
            'q2' => $this->percentile($sortedValues, 50),
            'q3' => $this->percentile($sortedValues, 75),
        ];
    }
    
    /**
     * Calculate IQR (Interquartile Range)
     */
    private function calculateIQR(array $sortedValues): float
    {
        $quartiles = $this->calculateQuartiles($sortedValues);
        return $quartiles['q3'] - $quartiles['q1'];
    }
    
    /**
     * Calculate percentile
     */
    private function percentile(array $sortedValues, float $percentile): float
    {
        $index = ($percentile / 100) * (count($sortedValues) - 1);
        $lower = floor($index);
        $upper = ceil($index);
        
        if ($lower === $upper) {
            return $sortedValues[(int)$index];
        }
        
        $fraction = $index - $lower;
        return $sortedValues[(int)$lower] * (1 - $fraction) + 
               $sortedValues[(int)$upper] * $fraction;
    }
    
    /**
     * Calculate skewness (measure of asymmetry)
     */
    private function calculateSkewness(array $values): float
    {
        $n = count($values);
        $mean = Average::mean($values);
        $stdDev = Descriptive::standardDeviation($values);
        
        if ($stdDev == 0) {
            return 0;
        }
        
        $sum = array_sum(array_map(
            fn($v) => (($v - $mean) / $stdDev) ** 3,
            $values
        ));
        
        return ($n / (($n - 1) * ($n - 2))) * $sum;
    }
    
    /**
     * Calculate kurtosis (measure of "tailedness")
     */
    private function calculateKurtosis(array $values): float
    {
        $n = count($values);
        $mean = Average::mean($values);
        $stdDev = Descriptive::standardDeviation($values);
        
        if ($stdDev == 0) {
            return 0;
        }
        
        $sum = array_sum(array_map(
            fn($v) => (($v - $mean) / $stdDev) ** 4,
            $values
        ));
        
        return (($n * ($n + 1)) / (($n - 1) * ($n - 2) * ($n - 3))) * $sum - 
               (3 * ($n - 1) ** 2) / (($n - 2) * ($n - 3));
    }
}


