<?php

declare(strict_types=1);

namespace DataScience\Analysis;

class DataProfiler
{
    private StatisticalAnalyzer $statsAnalyzer;
    private CorrelationAnalyzer $corrAnalyzer;
    
    public function __construct()
    {
        $this->statsAnalyzer = new StatisticalAnalyzer();
        $this->corrAnalyzer = new CorrelationAnalyzer();
    }
    
    /**
     * Generate comprehensive dataset profile
     */
    public function profileDataset(array $data): array
    {
        if (empty($data)) {
            return ['error' => 'Empty dataset'];
        }
        
        return [
            'overview' => $this->getOverview($data),
            'columns' => $this->profileColumns($data),
            'statistics' => $this->statsAnalyzer->analyzeDataset($data),
            'correlations' => $this->corrAnalyzer->strongestCorrelations($data, 0.5),
            'quality' => $this->assessQuality($data),
            'insights' => $this->extractInsights($data),
        ];
    }
    
    /**
     * Get dataset overview
     */
    private function getOverview(array $data): array
    {
        $columns = array_keys($data[0]);
        
        return [
            'rows' => count($data),
            'columns' => count($columns),
            'column_names' => $columns,
            'memory_usage' => $this->estimateMemoryUsage($data),
        ];
    }
    
    /**
     * Profile each column
     */
    private function profileColumns(array $data): array
    {
        $columns = array_keys($data[0]);
        $profiles = [];
        
        foreach ($columns as $column) {
            $values = array_column($data, $column);
            $profiles[$column] = $this->profileColumn($values, $column, $data);
        }
        
        return $profiles;
    }
    
    /**
     * Profile individual column
     */
    private function profileColumn(array $values, string $name, array $data): array
    {
        $nonNull = array_filter($values, fn($v) => $v !== null && $v !== '');
        $unique = array_unique($nonNull);
        
        $profile = [
            'type' => $this->inferType($nonNull),
            'count' => count($values),
            'non_null' => count($nonNull),
            'null_count' => count($values) - count($nonNull),
            'null_percentage' => round((1 - count($nonNull) / count($values)) * 100, 2),
            'unique' => count($unique),
            'unique_percentage' => round((count($unique) / count($nonNull)) * 100, 2),
        ];
        
        // Add type-specific stats
        if ($profile['type'] === 'numeric') {
            $numericValues = array_filter($nonNull, fn($v) => is_numeric($v));
            if (!empty($numericValues)) {
                $stats = $this->statsAnalyzer->analyzeColumn($data, $name);
                $profile['min'] = $stats['min'];
                $profile['max'] = $stats['max'];
                $profile['mean'] = $stats['mean'];
                $profile['median'] = $stats['median'];
            }
        } elseif ($profile['type'] === 'categorical') {
            $freq = $this->statsAnalyzer->categoricalFrequency($data, $name);
            $profile['top_values'] = array_slice($freq, 0, 5);
        }
        
        return $profile;
    }
    
    /**
     * Infer column data type
     */
    private function inferType(array $values): string
    {
        if (empty($values)) {
            return 'unknown';
        }
        
        $numericCount = count(array_filter($values, fn($v) => is_numeric($v)));
        
        if ($numericCount / count($values) > 0.9) {
            return 'numeric';
        }
        
        $uniqueRatio = count(array_unique($values)) / count($values);
        
        if ($uniqueRatio < 0.5) {
            return 'categorical';
        }
        
        return 'text';
    }
    
    /**
     * Assess data quality
     */
    private function assessQuality(array $data): array
    {
        $columns = array_keys($data[0]);
        $totalCells = count($data) * count($columns);
        $missingCells = 0;
        
        foreach ($data as $row) {
            foreach ($columns as $column) {
                if (!isset($row[$column]) || $row[$column] === null || $row[$column] === '') {
                    $missingCells++;
                }
            }
        }
        
        $completeness = 1 - ($missingCells / $totalCells);
        
        return [
            'completeness' => round($completeness * 100, 2),
            'missing_cells' => $missingCells,
            'total_cells' => $totalCells,
            'quality_score' => $this->calculateQualityScore($completeness),
        ];
    }
    
    /**
     * Calculate overall quality score
     */
    private function calculateQualityScore(float $completeness): string
    {
        if ($completeness >= 0.95) {
            return 'Excellent';
        } elseif ($completeness >= 0.85) {
            return 'Good';
        } elseif ($completeness >= 0.70) {
            return 'Fair';
        } else {
            return 'Poor';
        }
    }
    
    /**
     * Extract key insights
     */
    private function extractInsights(array $data): array
    {
        $insights = [];
        
        // Check for strong correlations
        $correlations = $this->corrAnalyzer->strongestCorrelations($data, 0.7);
        if (!empty($correlations)) {
            $insights[] = [
                'type' => 'correlation',
                'message' => count($correlations) . ' strong correlation(s) found',
                'details' => $correlations,
            ];
        }
        
        // Check for high missing data
        $columns = array_keys($data[0]);
        foreach ($columns as $column) {
            $values = array_column($data, $column);
            $missing = count(array_filter($values, fn($v) => $v === null || $v === ''));
            $missingPct = ($missing / count($values)) * 100;
            
            if ($missingPct > 20) {
                $insights[] = [
                    'type' => 'data_quality',
                    'message' => "Column '{$column}' has {$missingPct}% missing values",
                    'severity' => $missingPct > 50 ? 'high' : 'medium',
                ];
            }
        }
        
        return $insights;
    }
    
    /**
     * Estimate memory usage
     */
    private function estimateMemoryUsage(array $data): string
    {
        $bytes = strlen(serialize($data));
        
        if ($bytes < 1024) {
            return $bytes . ' B';
        } elseif ($bytes < 1048576) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return round($bytes / 1048576, 2) . ' MB';
        }
    }
    
    /**
     * Print profile report
     */
    public function printProfile(array $profile): void
    {
        echo "=== Dataset Profile ===\n\n";
        
        // Overview
        echo "Overview:\n";
        echo "  Rows: " . number_format($profile['overview']['rows']) . "\n";
        echo "  Columns: " . $profile['overview']['columns'] . "\n";
        echo "  Memory: " . $profile['overview']['memory_usage'] . "\n\n";
        
        // Quality
        echo "Data Quality:\n";
        echo "  Completeness: " . $profile['quality']['completeness'] . "%\n";
        echo "  Quality Score: " . $profile['quality']['quality_score'] . "\n";
        echo "  Missing Cells: " . number_format($profile['quality']['missing_cells']) . 
             " / " . number_format($profile['quality']['total_cells']) . "\n\n";
        
        // Columns
        echo "Columns:\n";
        foreach ($profile['columns'] as $name => $col) {
            echo "  {$name} ({$col['type']}):\n";
            echo "    Non-null: {$col['non_null']} ({$col['null_percentage']}% missing)\n";
            echo "    Unique: {$col['unique']} ({$col['unique_percentage']}%)\n";
            
            if (isset($col['mean'])) {
                echo "    Range: [" . round($col['min'], 2) . " - " . round($col['max'], 2) . "]\n";
                echo "    Mean: " . round($col['mean'], 2) . "\n";
            }
            
            echo "\n";
        }
        
        // Insights
        if (!empty($profile['insights'])) {
            echo "Key Insights:\n";
            foreach ($profile['insights'] as $insight) {
                echo "  • " . $insight['message'] . "\n";
            }
            echo "\n";
        }
    }
}


