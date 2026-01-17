<?php

declare(strict_types=1);

namespace DataScience\Analysis;

use MathPHP\Statistics\Correlation;

class CorrelationAnalyzer
{
    /**
     * Calculate Pearson correlation coefficient between two variables
     */
    public function pearsonCorrelation(
        array $data,
        string $column1,
        string $column2
    ): float {
        $x = $this->extractNumericValues($data, $column1);
        $y = $this->extractNumericValues($data, $column2);
        
        if (count($x) !== count($y) || count($x) < 2) {
            throw new \InvalidArgumentException('Invalid data for correlation');
        }
        
        return Correlation::r($x, $y);
    }
    
    /**
     * Calculate correlation matrix for all numeric columns
     */
    public function correlationMatrix(array $data): array
    {
        $numericColumns = $this->getNumericColumns($data);
        $matrix = [];
        
        foreach ($numericColumns as $col1) {
            $matrix[$col1] = [];
            
            foreach ($numericColumns as $col2) {
                if ($col1 === $col2) {
                    $matrix[$col1][$col2] = 1.0;
                } else {
                    try {
                        $matrix[$col1][$col2] = $this->pearsonCorrelation(
                            $data,
                            $col1,
                            $col2
                        );
                    } catch (\Exception $e) {
                        $matrix[$col1][$col2] = null;
                    }
                }
            }
        }
        
        return $matrix;
    }
    
    /**
     * Find strongest correlations
     */
    public function strongestCorrelations(
        array $data,
        float $threshold = 0.7
    ): array {
        $matrix = $this->correlationMatrix($data);
        $strong = [];
        
        $columns = array_keys($matrix);
        
        for ($i = 0; $i < count($columns); $i++) {
            for ($j = $i + 1; $j < count($columns); $j++) {
                $col1 = $columns[$i];
                $col2 = $columns[$j];
                $correlation = $matrix[$col1][$col2] ?? null;
                
                if ($correlation !== null && abs($correlation) >= $threshold) {
                    $strong[] = [
                        'variable1' => $col1,
                        'variable2' => $col2,
                        'correlation' => $correlation,
                        'strength' => $this->interpretStrength($correlation),
                        'direction' => $correlation > 0 ? 'positive' : 'negative',
                    ];
                }
            }
        }
        
        // Sort by absolute correlation
        usort($strong, fn($a, $b) => 
            abs($b['correlation']) <=> abs($a['correlation'])
        );
        
        return $strong;
    }
    
    /**
     * Calculate covariance between two variables
     */
    public function covariance(
        array $data,
        string $column1,
        string $column2
    ): float {
        $x = $this->extractNumericValues($data, $column1);
        $y = $this->extractNumericValues($data, $column2);
        
        if (count($x) !== count($y) || count($x) < 2) {
            throw new \InvalidArgumentException('Invalid data for covariance');
        }
        
        $meanX = array_sum($x) / count($x);
        $meanY = array_sum($y) / count($y);
        
        $sum = 0;
        for ($i = 0; $i < count($x); $i++) {
            $sum += ($x[$i] - $meanX) * ($y[$i] - $meanY);
        }
        
        return $sum / (count($x) - 1);
    }
    
    /**
     * Interpret correlation strength
     */
    private function interpretStrength(float $correlation): string
    {
        $abs = abs($correlation);
        
        if ($abs >= 0.9) {
            return 'very strong';
        } elseif ($abs >= 0.7) {
            return 'strong';
        } elseif ($abs >= 0.5) {
            return 'moderate';
        } elseif ($abs >= 0.3) {
            return 'weak';
        } else {
            return 'very weak';
        }
    }
    
    /**
     * Extract numeric values from column
     */
    private function extractNumericValues(array $data, string $column): array
    {
        return array_values(array_filter(
            array_column($data, $column),
            fn($v) => is_numeric($v)
        ));
    }
    
    /**
     * Get all numeric columns
     */
    private function getNumericColumns(array $data): array
    {
        if (empty($data)) {
            return [];
        }
        
        $columns = array_keys($data[0]);
        $numeric = [];
        
        foreach ($columns as $column) {
            $values = array_column($data, $column);
            $numericValues = array_filter($values, fn($v) => is_numeric($v));
            
            if (count($numericValues) > 0) {
                $numeric[] = $column;
            }
        }
        
        return $numeric;
    }
}


