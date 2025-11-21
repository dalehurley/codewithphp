<?php

declare(strict_types=1);

namespace App;

use ClaudePhp\ClaudePhp;
use Anthropic\Resources\Messages;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Analytics Engine
 *
 * Generates insights and analytics from extracted data using Claude.
 */
class AnalyticsEngine
{
    private Messages $messages;
    private string $model;

    public function __construct(
        private Anthropic $client,
        private LoggerInterface $logger = new NullLogger()
    ) {
        $this->messages = $this->client->messages();
        $this->model = 'claude-3-5-sonnet-20241022';
    }

    /**
     * Analyze data and generate insights
     */
    public function analyze(array $data, array $options = []): array
    {
        $this->logger->info('Generating analytics', [
            'data_points' => count($data)
        ]);

        $insights = [];

        // Basic statistical analysis
        $insights['statistics'] = $this->generateStatistics($data);

        // Pattern detection
        if ($options['detect_patterns'] ?? true) {
            $insights['patterns'] = $this->detectPatterns($data);
        }

        // AI-powered insights
        if ($options['ai_insights'] ?? true) {
            $insights['ai_insights'] = $this->generateAIInsights($data);
        }

        // Data quality metrics
        $insights['quality'] = $this->calculateQualityMetrics($data);

        // Anomaly detection
        if ($options['detect_anomalies'] ?? true) {
            $insights['anomalies'] = $this->detectAnomalies($data);
        }

        return $insights;
    }

    /**
     * Generate basic statistics
     */
    private function generateStatistics(array $data): array
    {
        $stats = [
            'total_fields' => count($data),
            'non_empty_fields' => 0,
            'empty_fields' => 0,
            'field_types' => []
        ];

        foreach ($data as $field => $value) {
            if ($value !== null && $value !== '') {
                $stats['non_empty_fields']++;
            } else {
                $stats['empty_fields']++;
            }

            $type = gettype($value);
            $stats['field_types'][$type] = ($stats['field_types'][$type] ?? 0) + 1;
        }

        $stats['completeness'] = $stats['total_fields'] > 0
            ? round($stats['non_empty_fields'] / $stats['total_fields'], 2)
            : 0;

        return $stats;
    }

    /**
     * Detect patterns in data
     */
    private function detectPatterns(array $data): array
    {
        $patterns = [];

        // Detect date patterns
        $dates = [];
        foreach ($data as $field => $value) {
            if (is_string($value) && $this->looksLikeDate($value)) {
                $dates[] = $field;
            }
        }
        if (!empty($dates)) {
            $patterns['date_fields'] = $dates;
        }

        // Detect numeric patterns
        $numbers = [];
        foreach ($data as $field => $value) {
            if (is_numeric($value)) {
                $numbers[$field] = $value;
            }
        }
        if (!empty($numbers)) {
            $patterns['numeric_fields'] = array_keys($numbers);
            $patterns['numeric_stats'] = [
                'count' => count($numbers),
                'sum' => array_sum($numbers),
                'average' => count($numbers) > 0 ? array_sum($numbers) / count($numbers) : 0,
                'min' => !empty($numbers) ? min($numbers) : null,
                'max' => !empty($numbers) ? max($numbers) : null
            ];
        }

        // Detect list/array patterns
        $arrays = [];
        foreach ($data as $field => $value) {
            if (is_array($value)) {
                $arrays[$field] = count($value);
            }
        }
        if (!empty($arrays)) {
            $patterns['array_fields'] = $arrays;
        }

        return $patterns;
    }

    /**
     * Generate AI-powered insights
     */
    private function generateAIInsights(array $data): array
    {
        $dataJson = json_encode($data, JSON_PRETTY_PRINT);

        $prompt = <<<PROMPT
Analyze this extracted data and provide insights:

{$dataJson}

Generate insights about:
1. Data completeness and quality
2. Notable patterns or trends
3. Potential issues or concerns
4. Recommendations for data improvement
5. Summary of key findings

Return insights as a JSON object with these sections:
{
  "summary": "Brief overview",
  "key_findings": ["list of important discoveries"],
  "recommendations": ["suggested improvements"],
  "concerns": ["potential issues"],
  "confidence": 0.0-1.0
}
PROMPT;

        try {
            $response = $this->messages->create([
                'model' => $this->model,
                'max_tokens' => 2048,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'system' => 'You are a data analytics expert. Provide clear, actionable insights.'
            ]);

            $text = $response->content[0]->text;

            if (preg_match('/```json\s*(.*?)\s*```/s', $text, $matches)) {
                $text = $matches[1];
            }

            $insights = json_decode($text, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $insights;
            }
        } catch (\Throwable $e) {
            $this->logger->error('AI insights generation failed', [
                'error' => $e->getMessage()
            ]);
        }

        return [
            'summary' => 'AI insights unavailable',
            'key_findings' => [],
            'recommendations' => [],
            'concerns' => ['AI analysis failed'],
            'confidence' => 0.0
        ];
    }

    /**
     * Calculate data quality metrics
     */
    private function calculateQualityMetrics(array $data): array
    {
        $metrics = [
            'completeness' => 0,
            'consistency' => 1.0,
            'accuracy_confidence' => 0.8,
            'overall_score' => 0
        ];

        // Completeness
        $totalFields = count($data);
        $filledFields = 0;

        foreach ($data as $value) {
            if ($value !== null && $value !== '') {
                $filledFields++;
            }
        }

        $metrics['completeness'] = $totalFields > 0
            ? round($filledFields / $totalFields, 2)
            : 0;

        // Check for consistency issues
        $inconsistencies = 0;
        foreach ($data as $field => $value) {
            // Check for mixed types in what should be consistent fields
            if (is_string($value) && strlen($value) > 0) {
                if (preg_match('/\s{2,}/', $value)) {
                    $inconsistencies++; // Multiple spaces
                }
                if (preg_match('/[^\x20-\x7E]/', $value)) {
                    // Non-printable characters
                }
            }
        }

        $metrics['consistency'] = max(0, 1.0 - ($inconsistencies * 0.1));

        // Overall quality score
        $metrics['overall_score'] = round(
            ($metrics['completeness'] * 0.4) +
            ($metrics['consistency'] * 0.3) +
            ($metrics['accuracy_confidence'] * 0.3),
            2
        );

        return $metrics;
    }

    /**
     * Detect anomalies in data
     */
    private function detectAnomalies(array $data): array
    {
        $anomalies = [];

        foreach ($data as $field => $value) {
            // Check for unusually long strings
            if (is_string($value) && strlen($value) > 1000) {
                $anomalies[] = [
                    'field' => $field,
                    'type' => 'unusual_length',
                    'description' => 'String length exceeds expected range',
                    'value_length' => strlen($value)
                ];
            }

            // Check for empty required-looking fields
            if (($value === null || $value === '') &&
                (str_contains(strtolower($field), 'id') ||
                 str_contains(strtolower($field), 'name'))) {
                $anomalies[] = [
                    'field' => $field,
                    'type' => 'missing_critical',
                    'description' => 'Critical field appears to be empty'
                ];
            }

            // Check for potential data type mismatches
            if (str_contains(strtolower($field), 'date') && !$this->looksLikeDate($value)) {
                $anomalies[] = [
                    'field' => $field,
                    'type' => 'type_mismatch',
                    'description' => 'Field name suggests date but value doesn\'t match',
                    'value' => $value
                ];
            }
        }

        return $anomalies;
    }

    /**
     * Check if value looks like a date
     */
    private function looksLikeDate(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        // Try common date formats
        $formats = ['Y-m-d', 'Y-m-d H:i:s', 'm/d/Y', 'd/m/Y', 'Y/m/d'];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $value);
            if ($date !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate a comprehensive report
     */
    public function generateReport(array $data, array $analysisResults): string
    {
        $report = "# Data Analysis Report\n\n";
        $report .= "Generated: " . date('Y-m-d H:i:s') . "\n\n";

        // Summary
        $report .= "## Summary\n\n";
        $stats = $analysisResults['statistics'] ?? [];
        $report .= "- Total Fields: " . ($stats['total_fields'] ?? 0) . "\n";
        $report .= "- Completeness: " . (($stats['completeness'] ?? 0) * 100) . "%\n";
        $report .= "- Quality Score: " . (($analysisResults['quality']['overall_score'] ?? 0) * 100) . "%\n\n";

        // Key Findings
        if (isset($analysisResults['ai_insights']['key_findings'])) {
            $report .= "## Key Findings\n\n";
            foreach ($analysisResults['ai_insights']['key_findings'] as $finding) {
                $report .= "- {$finding}\n";
            }
            $report .= "\n";
        }

        // Patterns
        if (isset($analysisResults['patterns'])) {
            $report .= "## Detected Patterns\n\n";
            $report .= json_encode($analysisResults['patterns'], JSON_PRETTY_PRINT);
            $report .= "\n\n";
        }

        // Anomalies
        if (!empty($analysisResults['anomalies'] ?? [])) {
            $report .= "## Anomalies Detected\n\n";
            foreach ($analysisResults['anomalies'] as $anomaly) {
                $report .= "- **{$anomaly['field']}**: {$anomaly['description']}\n";
            }
            $report .= "\n";
        }

        // Recommendations
        if (isset($analysisResults['ai_insights']['recommendations'])) {
            $report .= "## Recommendations\n\n";
            foreach ($analysisResults['ai_insights']['recommendations'] as $rec) {
                $report .= "- {$rec}\n";
            }
            $report .= "\n";
        }

        return $report;
    }

    /**
     * Compare multiple datasets
     */
    public function compareDatasets(array $datasets): array
    {
        $comparison = [
            'count' => count($datasets),
            'common_fields' => [],
            'unique_fields' => [],
            'field_overlap' => 0
        ];

        if (count($datasets) < 2) {
            return $comparison;
        }

        // Find common fields
        $allFields = [];
        foreach ($datasets as $index => $dataset) {
            $allFields[$index] = array_keys($dataset);
        }

        $comparison['common_fields'] = array_values(
            array_intersect(...$allFields)
        );

        $comparison['field_overlap'] = count($comparison['common_fields']) /
            max(1, count(array_unique(array_merge(...$allFields))));

        return $comparison;
    }
}
