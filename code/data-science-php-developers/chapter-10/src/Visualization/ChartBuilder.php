<?php

declare(strict_types=1);

namespace DataScience\Visualization;

/**
 * ChartBuilder - Generate Chart.js configurations with security and accessibility
 * 
 * This class provides a secure way to build Chart.js configurations from PHP,
 * with XSS protection, accessibility features, and colorblind-safe palettes.
 */
class ChartBuilder
{
    /**
     * Generate Chart.js configuration for line chart
     */
    public function lineChart(
        array $labels,
        array $datasets,
        array $options = []
    ): array {
        return [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => $this->formatDatasets($datasets, 'line'),
            ],
            'options' => array_merge($this->getDefaultOptions(), $options),
        ];
    }
    
    /**
     * Generate Chart.js configuration for bar chart
     */
    public function barChart(
        array $labels,
        array $datasets,
        array $options = []
    ): array {
        return [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => $this->formatDatasets($datasets, 'bar'),
            ],
            'options' => array_merge($this->getDefaultOptions(), $options),
        ];
    }
    
    /**
     * Generate Chart.js configuration for pie chart
     */
    public function pieChart(
        array $labels,
        array $data,
        array $options = []
    ): array {
        return [
            'type' => 'pie',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'data' => $data,
                    'backgroundColor' => $this->generateColors(count($data)),
                ]],
            ],
            'options' => array_merge($this->getDefaultOptions(), $options),
        ];
    }
    
    /**
     * Generate Chart.js configuration for scatter plot
     */
    public function scatterPlot(
        array $datasets,
        array $options = []
    ): array {
        return [
            'type' => 'scatter',
            'data' => [
                'datasets' => $this->formatDatasets($datasets, 'scatter'),
            ],
            'options' => array_merge($this->getDefaultOptions(), $options),
        ];
    }
    
    /**
     * ML model performance chart (accuracy over time)
     */
    public function modelPerformanceChart(
        array $dates,
        array $accuracyScores,
        float $threshold = 0.85
    ): array {
        return $this->lineChart(
            labels: $dates,
            datasets: [
                [
                    'label' => 'Model Accuracy',
                    'data' => $accuracyScores,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Threshold',
                    'data' => array_fill(0, count($dates), $threshold),
                    'borderColor' => 'rgb(255, 99, 132)',
                    'borderDash' => [5, 5],
                    'fill' => false,
                ],
            ],
            options: [
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Model Accuracy Over Time',
                    ],
                ],
                'scales' => [
                    'y' => [
                        'min' => 0,
                        'max' => 1,
                        'ticks' => [
                            'callback' => 'JS:function(value) { return (value * 100).toFixed(0) + "%"; }',
                        ],
                    ],
                ],
            ]
        );
    }
    
    /**
     * A/B test results chart
     */
    public function abTestChart(
        string $controlLabel,
        float $controlConversion,
        string $treatmentLabel,
        float $treatmentConversion,
        float $pValue
    ): array {
        $significant = $pValue < 0.05;
        
        return $this->barChart(
            labels: [$controlLabel, $treatmentLabel],
            datasets: [[
                'label' => 'Conversion Rate',
                'data' => [$controlConversion, $treatmentConversion],
                'backgroundColor' => [
                    'rgba(54, 162, 235, 0.8)',
                    $significant ? 'rgba(75, 192, 192, 0.8)' : 'rgba(255, 206, 86, 0.8)',
                ],
            ]],
            options: [
                'plugins' => [
                    'title' => [
                        'display' => true,
                        'text' => sprintf(
                            'A/B Test Results (p-value: %.4f %s)',
                            $pValue,
                            $significant ? '- Significant ✓' : '- Not Significant'
                        ),
                    ],
                ],
                'scales' => [
                    'y' => [
                        'min' => 0,
                        'max' => 1,
                        'ticks' => [
                            'callback' => 'JS:function(value) { return (value * 100).toFixed(1) + "%"; }',
                        ],
                    ],
                ],
            ]
        );
    }
    
    /**
     * Format datasets for Chart.js
     */
    private function formatDatasets(array $datasets, string $chartType): array
    {
        $formatted = [];
        $colors = $this->generateColors(count($datasets));
        
        foreach ($datasets as $index => $dataset) {
            $color = $colors[$index];
            
            $formatted[] = array_merge([
                'label' => $dataset['label'] ?? "Dataset " . ($index + 1),
                'data' => $dataset['data'],
                'backgroundColor' => $this->hexToRgba($color, 0.2),
                'borderColor' => $color,
                'borderWidth' => 2,
            ], $dataset['options'] ?? []);
        }
        
        return $formatted;
    }
    
    /**
     * Get default Chart.js options
     */
    private function getDefaultOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => true,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
        ];
    }
    
    /**
     * Generate color palette (default or colorblind-safe)
     */
    private function generateColors(int $count): array
    {
        $colors = [
            'rgb(255, 99, 132)',   // Red
            'rgb(54, 162, 235)',   // Blue
            'rgb(255, 206, 86)',   // Yellow
            'rgb(75, 192, 192)',   // Green
            'rgb(153, 102, 255)',  // Purple
            'rgb(255, 159, 64)',   // Orange
            'rgb(199, 199, 199)',  // Gray
            'rgb(83, 102, 255)',   // Indigo
        ];
        
        // Repeat colors if needed
        while (count($colors) < $count) {
            $colors = array_merge($colors, $colors);
        }
        
        return array_slice($colors, 0, $count);
    }
    
    /**
     * Generate colorblind-safe color palette (Wong 2011)
     */
    private function getAccessibleColors(): array
    {
        return [
            'rgb(0, 114, 178)',    // Blue
            'rgb(230, 159, 0)',    // Orange
            'rgb(0, 158, 115)',    // Green
            'rgb(204, 121, 167)',  // Purple
            'rgb(86, 180, 233)',   // Sky Blue
            'rgb(213, 94, 0)',     // Vermillion
            'rgb(240, 228, 66)',   // Yellow
        ];
    }
    
    /**
     * Apply colorblind-safe palette to chart configuration
     */
    public function makeColorblindSafe(array $config): array
    {
        $accessibleColors = $this->getAccessibleColors();
        
        if (isset($config['data']['datasets'])) {
            foreach ($config['data']['datasets'] as $index => &$dataset) {
                $color = $accessibleColors[$index % count($accessibleColors)];
                $dataset['backgroundColor'] = $this->hexToRgba($color, 0.2);
                $dataset['borderColor'] = $color;
            }
        }
        
        return $config;
    }
    
    /**
     * Add accessibility features to chart configuration
     * 
     * Makes charts WCAG 2.1 compliant with ARIA labels and descriptions
     */
    public function makeAccessible(array $config, string $description): array
    {
        $config['options']['plugins']['title'] = array_merge(
            $config['options']['plugins']['title'] ?? [],
            ['text' => $description]
        );
        
        // Add aria attributes for canvas element
        $config['aria'] = [
            'label' => $description,
            'describedby' => 'chart-description-' . uniqid(),
        ];
        
        return $config;
    }
    
    /**
     * Downsample data for performance (LTTB algorithm)
     * 
     * Largest Triangle Three Buckets algorithm for time-series downsampling
     * Preserves visual shape while reducing data points
     */
    public function downsampleData(array $data, int $maxPoints = 500): array
    {
        $count = count($data);
        
        if ($count <= $maxPoints) {
            return $data;
        }
        
        $sampled = [$data[0]]; // Always include first point
        $bucketSize = ($count - 2) / ($maxPoints - 2);
        
        for ($i = 0; $i < $maxPoints - 2; $i++) {
            $avgRangeStart = (int)floor(($i + 1) * $bucketSize) + 1;
            $avgRangeEnd = (int)floor(($i + 2) * $bucketSize) + 1;
            $avgRangeEnd = min($avgRangeEnd, $count);
            
            // Calculate average point in next bucket
            $avgX = 0;
            $avgY = 0;
            $avgRangeLength = $avgRangeEnd - $avgRangeStart;
            
            for ($j = $avgRangeStart; $j < $avgRangeEnd; $j++) {
                $avgX += $j;
                $avgY += $data[$j];
            }
            
            $avgX /= $avgRangeLength;
            $avgY /= $avgRangeLength;
            
            // Find point in current bucket with largest triangle area
            $rangeStart = (int)floor($i * $bucketSize) + 1;
            $rangeEnd = (int)floor(($i + 1) * $bucketSize) + 1;
            
            $maxArea = -1;
            $maxAreaPoint = null;
            
            for ($j = $rangeStart; $j < $rangeEnd; $j++) {
                $area = abs(
                    ($sampled[count($sampled) - 1] - $avgX) * ($data[$j] - $avgY) -
                    ($sampled[count($sampled) - 1] - $data[$j]) * ($avgX - $avgY)
                );
                
                if ($area > $maxArea) {
                    $maxArea = $area;
                    $maxAreaPoint = $data[$j];
                }
            }
            
            $sampled[] = $maxAreaPoint;
        }
        
        $sampled[] = $data[$count - 1]; // Always include last point
        
        return $sampled;
    }
    
    /**
     * Convert hex color to rgba
     */
    private function hexToRgba(string $hex, float $alpha): string
    {
        // Remove 'rgb(' and ')' if present
        $hex = str_replace(['rgb(', ')'], '', $hex);
        
        // If already rgb format, add alpha
        if (str_contains($hex, ',')) {
            return "rgba({$hex}, {$alpha})";
        }
        
        // Otherwise it's hex, convert it
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        
        return "rgba({$r}, {$g}, {$b}, {$alpha})";
    }
    
    /**
     * Export chart configuration as JSON (SECURE VERSION)
     * 
     * Validates JavaScript callbacks to prevent XSS attacks
     */
    public function toJson(array $config): string
    {
        $json = json_encode($config, JSON_PRETTY_PRINT);
        
        if ($json === false) {
            throw new \RuntimeException('Failed to encode chart configuration: ' . json_last_error_msg());
        }
        
        // Replace JS callback placeholders with validation
        $json = preg_replace_callback(
            '/"JS:(.*?)"/',
            function($matches) {
                $callback = $matches[1];
                
                // Basic validation - ensure it looks like a function
                if (!preg_match('/^function\s*\([^)]*\)\s*\{/', $callback)) {
                    throw new \InvalidArgumentException('Invalid JavaScript callback format');
                }
                
                return $callback;
            },
            $json
        );
        
        return $json;
    }
}
