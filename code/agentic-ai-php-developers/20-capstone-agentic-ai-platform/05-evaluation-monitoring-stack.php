<?php

declare(strict_types=1);

/**
 * 05 - Evaluation & Monitoring Stack
 * 
 * Comprehensive quality evaluation, safety validation, cost tracking,
 * and performance monitoring for production agentic systems.
 */

// Load autoloader - use the claude-php-agent repo
require_once '/Users/dalehurley/Code/claude-php-agent/vendor/autoload.php';


/**
 * Evaluation and Monitoring Stack
 */
class EvaluationMonitoringStack
{
    private array $metrics = [];
    private array $evaluations = [];
    private array $alerts = [];
    private array $thresholds = [
        'quality_min' => 0.7,
        'safety_min' => 0.9,
        'cost_max' => 0.10, // $0.10 per task
        'latency_max' => 10.0, // 10 seconds
    ];

    /**
     * Evaluate execution result across multiple dimensions
     */
    public function evaluate(array $execution): array
    {
        $scores = [
            'quality' => $this->evaluateQuality($execution),
            'safety' => $this->evaluateSafety($execution),
            'cost' => $this->evaluateCost($execution),
            'performance' => $this->evaluatePerformance($execution),
        ];
        
        $overallScore = array_sum($scores) / count($scores);
        
        $evaluation = [
            'execution_id' => $execution['execution_id'],
            'timestamp' => time(),
            'scores' => $scores,
            'overall' => $overallScore,
            'passed' => $this->isPassing($scores),
            'flags' => $this->identifyFlags($scores, $execution),
        ];
        
        $this->evaluations[] = $evaluation;
        
        // Check for alerts
        $this->checkAlerts($evaluation);
        
        return $evaluation;
    }

    /**
     * Evaluate quality: completeness, accuracy, relevance
     */
    private function evaluateQuality(array $execution): float
    {
        if (!$execution['success']) {
            return 0.0;
        }
        
        $score = 1.0;
        $result = $execution['result'] ?? '';
        
        // Penalize very short responses (likely incomplete)
        if (strlen($result) < 50) {
            $score -= 0.4;
        }
        
        // Penalize if result contains error indicators
        if (preg_match('/\b(error|failed|unable|cannot)\b/i', $result)) {
            $score -= 0.2;
        }
        
        // Bonus for structured responses
        if (preg_match('/\n[-•*]|\d+\./', $result)) {
            $score += 0.1;
        }
        
        return max(0, min(1, $score));
    }

    /**
     * Evaluate safety: no PII, harmful content, or policy violations
     */
    private function evaluateSafety(array $execution): float
    {
        $result = $execution['result'] ?? '';
        
        // Critical patterns (immediate failure)
        $criticalPatterns = [
            '/\b\d{3}-\d{2}-\d{4}\b/', // SSN
            '/\b\d{4}[- ]?\d{4}[- ]?\d{4}[- ]?\d{4}\b/', // Credit card
            '/password\s*[:=]\s*\S+/i',
            '/api[_-]?key\s*[:=]\s*\S+/i',
        ];
        
        foreach ($criticalPatterns as $pattern) {
            if (preg_match($pattern, $result)) {
                return 0.0; // Critical safety failure
            }
        }
        
        // Warning patterns (reduce score)
        $warningPatterns = [
            '/\b(hack|exploit|bypass|crack)\b/i',
            '/\b(kill|harm|hurt|destroy)\b/i',
        ];
        
        $score = 1.0;
        foreach ($warningPatterns as $pattern) {
            if (preg_match($pattern, $result)) {
                $score -= 0.2;
            }
        }
        
        return max(0, $score);
    }

    /**
     * Evaluate cost efficiency
     */
    private function evaluateCost(array $execution): float
    {
        $cost = $execution['metadata']['cost'] ?? 0;
        
        // Score based on cost tiers
        if ($cost < 0.01) {
            return 1.0;
        } elseif ($cost < 0.03) {
            return 0.9;
        } elseif ($cost < 0.05) {
            return 0.7;
        } elseif ($cost < 0.10) {
            return 0.5;
        } else {
            return 0.3;
        }
    }

    /**
     * Evaluate performance: latency, throughput
     */
    private function evaluatePerformance(array $execution): float
    {
        $duration = $execution['metadata']['duration'] ?? 0;
        
        // Score based on latency
        if ($duration < 2.0) {
            return 1.0;
        } elseif ($duration < 5.0) {
            return 0.8;
        } elseif ($duration < 10.0) {
            return 0.6;
        } elseif ($duration < 20.0) {
            return 0.4;
        } else {
            return 0.2;
        }
    }

    /**
     * Check if evaluation passes all thresholds
     */
    private function isPassing(array $scores): bool
    {
        return $scores['quality'] >= $this->thresholds['quality_min']
            && $scores['safety'] >= $this->thresholds['safety_min'];
    }

    /**
     * Identify specific issues
     */
    private function identifyFlags(array $scores, array $execution): array
    {
        $flags = [];
        
        if ($scores['quality'] < $this->thresholds['quality_min']) {
            $flags[] = 'low_quality';
        }
        
        if ($scores['safety'] < $this->thresholds['safety_min']) {
            $flags[] = 'safety_concern';
        }
        
        if (($execution['metadata']['cost'] ?? 0) > $this->thresholds['cost_max']) {
            $flags[] = 'high_cost';
        }
        
        if (($execution['metadata']['duration'] ?? 0) > $this->thresholds['latency_max']) {
            $flags[] = 'high_latency';
        }
        
        return $flags;
    }

    /**
     * Check for alert conditions
     */
    private function checkAlerts(array $evaluation): void
    {
        // Alert on low overall score
        if ($evaluation['overall'] < 0.5) {
            $this->alerts[] = [
                'level' => 'warning',
                'message' => 'Low evaluation score',
                'execution_id' => $evaluation['execution_id'],
                'score' => $evaluation['overall'],
                'timestamp' => time(),
            ];
        }
        
        // Alert on safety failure
        if ($evaluation['scores']['safety'] < $this->thresholds['safety_min']) {
            $this->alerts[] = [
                'level' => 'critical',
                'message' => 'Safety evaluation failed',
                'execution_id' => $evaluation['execution_id'],
                'safety_score' => $evaluation['scores']['safety'],
                'timestamp' => time(),
            ];
        }
        
        // Alert on quality failure
        if ($evaluation['scores']['quality'] < $this->thresholds['quality_min']) {
            $this->alerts[] = [
                'level' => 'warning',
                'message' => 'Quality below threshold',
                'execution_id' => $evaluation['execution_id'],
                'quality_score' => $evaluation['scores']['quality'],
                'timestamp' => time(),
            ];
        }
    }

    /**
     * Record metric
     */
    public function recordMetric(
        string $name,
        float $value,
        array $tags = []
    ): void {
        $this->metrics[] = [
            'name' => $name,
            'value' => $value,
            'tags' => $tags,
            'timestamp' => microtime(true),
        ];
    }

    /**
     * Get aggregated statistics
     */
    public function getStats(int $lastNMinutes = 60): array
    {
        $cutoff = time() - ($lastNMinutes * 60);
        
        $recentEvals = array_filter(
            $this->evaluations,
            fn($e) => $e['timestamp'] >= $cutoff
        );
        
        if (empty($recentEvals)) {
            return [
                'total_evaluations' => 0,
                'avg_scores' => [],
                'pass_rate' => 0,
                'alert_count' => 0,
            ];
        }
        
        $avgScores = [
            'quality' => 0,
            'safety' => 0,
            'cost' => 0,
            'performance' => 0,
        ];
        
        $passCount = 0;
        $flagCounts = [];
        
        foreach ($recentEvals as $eval) {
            foreach ($avgScores as $key => $value) {
                $avgScores[$key] += $eval['scores'][$key];
            }
            
            if ($eval['passed']) {
                $passCount++;
            }
            
            foreach ($eval['flags'] as $flag) {
                $flagCounts[$flag] = ($flagCounts[$flag] ?? 0) + 1;
            }
        }
        
        $count = count($recentEvals);
        foreach ($avgScores as &$score) {
            $score /= $count;
        }
        
        return [
            'total_evaluations' => $count,
            'avg_scores' => $avgScores,
            'pass_rate' => $passCount / $count,
            'alert_count' => count($this->alerts),
            'flag_counts' => $flagCounts,
        ];
    }

    public function getAlerts(?string $level = null): array
    {
        if (!$level) {
            return $this->alerts;
        }
        
        return array_filter(
            $this->alerts,
            fn($a) => $a['level'] === $level
        );
    }

    public function getMetrics(string $name): array
    {
        return array_filter(
            $this->metrics,
            fn($m) => $m['name'] === $name
        );
    }

    public function getEvaluations(?string $executionId = null): array
    {
        if (!$executionId) {
            return $this->evaluations;
        }
        
        return array_filter(
            $this->evaluations,
            fn($e) => $e['execution_id'] === $executionId
        );
    }

    public function exportReport(): string
    {
        $stats = $this->getStats();
        
        $report = "=== EVALUATION REPORT ===\n\n";
        $report .= "Total Evaluations: {$stats['total_evaluations']}\n";
        $report .= "Pass Rate: " . number_format($stats['pass_rate'] * 100, 1) . "%\n\n";
        
        $report .= "Average Scores:\n";
        foreach ($stats['avg_scores'] as $dimension => $score) {
            $report .= "  " . ucfirst($dimension) . ": " . number_format($score, 2) . "\n";
        }
        
        $report .= "\nAlerts:\n";
        $report .= "  Total: {$stats['alert_count']}\n";
        $report .= "  Critical: " . count($this->getAlerts('critical')) . "\n";
        $report .= "  Warning: " . count($this->getAlerts('warning')) . "\n";
        
        if (!empty($stats['flag_counts'])) {
            $report .= "\nCommon Issues:\n";
            foreach ($stats['flag_counts'] as $flag => $count) {
                $report .= "  {$flag}: {$count}\n";
            }
        }
        
        return $report;
    }
}

// Example usage
echo "=== Evaluation & Monitoring Stack ===\n\n";

$monitoring = new EvaluationMonitoringStack();

echo "📊 Running evaluation tests...\n\n";

// Simulate various execution results
$testExecutions = [
    // Good quality result
    [
        'execution_id' => 'exec_001',
        'success' => true,
        'result' => 'Here is a comprehensive analysis of the problem:\n1. First issue\n2. Second issue\n3. Recommendations',
        'metadata' => [
            'duration' => 1.5,
            'tokens' => 500,
            'cost' => 0.008,
        ],
    ],
    // Short result (quality issue)
    [
        'execution_id' => 'exec_002',
        'success' => true,
        'result' => 'OK',
        'metadata' => [
            'duration' => 0.8,
            'tokens' => 50,
            'cost' => 0.001,
        ],
    ],
    // Safety issue (potential PII)
    [
        'execution_id' => 'exec_003',
        'success' => true,
        'result' => 'The user password is: secret123',
        'metadata' => [
            'duration' => 1.2,
            'tokens' => 100,
            'cost' => 0.002,
        ],
    ],
    // High cost
    [
        'execution_id' => 'exec_004',
        'success' => true,
        'result' => 'This is a detailed analysis with many tokens that costs a lot to generate because it is very comprehensive and thorough.',
        'metadata' => [
            'duration' => 15.0,
            'tokens' => 10000,
            'cost' => 0.150,
        ],
    ],
    // Failed execution
    [
        'execution_id' => 'exec_005',
        'success' => false,
        'error' => 'Agent timeout',
        'result' => '',
        'metadata' => [
            'duration' => 30.0,
            'tokens' => 0,
            'cost' => 0.0,
        ],
    ],
];

foreach ($testExecutions as $execution) {
    echo "Evaluating {$execution['execution_id']}...\n";
    
    $evaluation = $monitoring->evaluate($execution);
    
    echo "  Overall Score: " . number_format($evaluation['overall'], 2) . "\n";
    echo "  Quality: " . number_format($evaluation['scores']['quality'], 2) . "\n";
    echo "  Safety: " . number_format($evaluation['scores']['safety'], 2) . "\n";
    echo "  Cost: " . number_format($evaluation['scores']['cost'], 2) . "\n";
    echo "  Performance: " . number_format($evaluation['scores']['performance'], 2) . "\n";
    echo "  Status: " . ($evaluation['passed'] ? '✅ PASS' : '❌ FAIL') . "\n";
    
    if (!empty($evaluation['flags'])) {
        echo "  Flags: " . implode(', ', $evaluation['flags']) . "\n";
    }
    
    echo "\n";
    
    // Record metrics
    $monitoring->recordMetric('task_duration', $execution['metadata']['duration']);
    $monitoring->recordMetric('task_cost', $execution['metadata']['cost'] ?? 0);
}

// Display alerts
echo "🚨 Alerts:\n";
$criticalAlerts = $monitoring->getAlerts('critical');
$warningAlerts = $monitoring->getAlerts('warning');

echo "  Critical: " . count($criticalAlerts) . "\n";
foreach ($criticalAlerts as $alert) {
    echo "    • {$alert['message']} ({$alert['execution_id']})\n";
}

echo "  Warnings: " . count($warningAlerts) . "\n";
foreach ($warningAlerts as $alert) {
    echo "    • {$alert['message']} ({$alert['execution_id']})\n";
}

echo "\n";

// Display statistics
echo "📈 Statistics (last 60 minutes):\n";
$stats = $monitoring->getStats();
echo "  Total evaluations: {$stats['total_evaluations']}\n";
echo "  Pass rate: " . number_format($stats['pass_rate'] * 100, 1) . "%\n";
echo "  Average scores:\n";
foreach ($stats['avg_scores'] as $dimension => $score) {
    echo "    " . ucfirst($dimension) . ": " . number_format($score, 2) . "\n";
}

if (!empty($stats['flag_counts'])) {
    echo "  Common issues:\n";
    foreach ($stats['flag_counts'] as $flag => $count) {
        echo "    {$flag}: {$count}\n";
    }
}

echo "\n";

// Export full report
echo "📄 Full Report:\n";
echo str_repeat('-', 50) . "\n";
echo $monitoring->exportReport();
echo str_repeat('-', 50) . "\n";

echo "\n✅ Evaluation & Monitoring Stack demonstration complete!\n";
echo "\n💡 Key features:\n";
echo "   • Multi-dimensional evaluation (quality, safety, cost, performance)\n";
echo "   • Automated alert generation\n";
echo "   • Comprehensive metrics tracking\n";
echo "   • Statistical analysis\n";
echo "   • Report generation\n";
echo "\n💡 Production enhancements:\n";
echo "   • Integrate with monitoring systems (Datadog, New Relic)\n";
echo "   • Add LLM-as-judge for quality evaluation\n";
echo "   • Implement regression test suites\n";
echo "   • Add A/B testing framework\n";
echo "   • Build real-time dashboards\n";
