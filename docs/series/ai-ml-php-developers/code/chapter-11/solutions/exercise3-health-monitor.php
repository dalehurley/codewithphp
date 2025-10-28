<?php

declare(strict_types=1);

/**
 * Exercise 3 Solution: Health Monitoring System
 * 
 * This solution demonstrates:
 * - Comprehensive health checks for PHP-Python integration
 * - Python version validation
 * - Package dependency verification
 * - Model file existence checks
 * - Performance testing with latency measurement
 * - Overall system health reporting
 */

class HealthMonitor
{
    private array $checks = [];
    private array $results = [];

    public function __construct(
        private string $pythonPath = 'python3',
        private string $scriptsDir = __DIR__ . '/../03-sentiment-analysis',
        private float $maxLatencyMs = 200.0
    ) {}

    /**
     * Run all health checks.
     */
    public function runAllChecks(): array
    {
        $this->results = [];

        $this->checkPythonInstalled();
        $this->checkPythonVersion();
        $this->checkRequiredPackages();
        $this->checkModelFiles();
        $this->checkPredictionLatency();

        return $this->getHealthReport();
    }

    /**
     * Check if Python is installed and accessible.
     */
    private function checkPythonInstalled(): void
    {
        $output = shell_exec("{$this->pythonPath} --version 2>&1");

        if ($output === null || empty(trim($output))) {
            $this->addResult('Python Installation', false, 'Python not found in PATH');
            return;
        }

        $this->addResult('Python Installation', true, trim($output));
    }

    /**
     * Check Python version >= 3.10.
     */
    private function checkPythonVersion(): void
    {
        $output = shell_exec("{$this->pythonPath} --version 2>&1");

        if (preg_match('/Python (\d+)\.(\d+)\.(\d+)/', $output, $matches)) {
            $major = (int)$matches[1];
            $minor = (int)$matches[2];
            $version = "{$major}.{$minor}";

            if ($major >= 3 && $minor >= 10) {
                $this->addResult('Python Version', true, "Version {$version} (✓ >= 3.10)");
            } else {
                $this->addResult('Python Version', false, "Version {$version} (requires >= 3.10)");
            }
        } else {
            $this->addResult('Python Version', false, 'Could not parse version');
        }
    }

    /**
     * Check required Python packages.
     */
    private function checkRequiredPackages(): void
    {
        $requiredPackages = ['sklearn', 'pandas', 'joblib'];

        foreach ($requiredPackages as $package) {
            $testScript = "import {$package}; print('{$package} OK')";
            $output = shell_exec("{$this->pythonPath} -c \"{$testScript}\" 2>&1");

            if ($output && strpos($output, 'OK') !== false) {
                $this->addResult("Package: {$package}", true, 'Installed');
            } else {
                $error = trim($output ?: 'Not found');
                $this->addResult("Package: {$package}", false, $error);
            }
        }
    }

    /**
     * Check if model files exist.
     */
    private function checkModelFiles(): void
    {
        $modelPath = $this->scriptsDir . '/models/sentiment_model.pkl';
        $vectorizerPath = $this->scriptsDir . '/models/vectorizer.pkl';

        if (file_exists($modelPath)) {
            $size = $this->formatBytes(filesize($modelPath));
            $this->addResult('Model File', true, "Found ({$size})");
        } else {
            $this->addResult('Model File', false, 'Not found - run training first');
        }

        if (file_exists($vectorizerPath)) {
            $size = $this->formatBytes(filesize($vectorizerPath));
            $this->addResult('Vectorizer File', true, "Found ({$size})");
        } else {
            $this->addResult('Vectorizer File', false, 'Not found - run training first');
        }
    }

    /**
     * Check prediction latency with a test prediction.
     */
    private function checkPredictionLatency(): void
    {
        $testText = 'This is a test prediction for health monitoring';

        try {
            $start = microtime(true);

            $data = json_encode(['text' => $testText]);
            $escaped = escapeshellarg($data);
            $command = "{$this->pythonPath} {$this->scriptsDir}/predict.py {$escaped} 2>&1";

            $output = shell_exec($command);
            $latency = (microtime(true) - $start) * 1000; // Convert to ms

            if ($output === null) {
                $this->addResult('Prediction Test', false, 'Script execution failed');
                return;
            }

            $result = json_decode($output, true);

            if (isset($result['error'])) {
                $this->addResult('Prediction Test', false, $result['error']);
                return;
            }

            $passed = $latency <= $this->maxLatencyMs;
            $status = $passed ? '✓' : '⚠';
            $message = "{$status} {$latency}ms (threshold: {$this->maxLatencyMs}ms)";

            $this->addResult('Prediction Latency', $passed, $message, $latency);

            // Verify result structure
            if (isset($result['sentiment']) && isset($result['confidence'])) {
                $this->addResult(
                    'Prediction Output',
                    true,
                    "Sentiment: {$result['sentiment']} ({$result['confidence']})"
                );
            } else {
                $this->addResult('Prediction Output', false, 'Invalid response format');
            }
        } catch (Exception $e) {
            $this->addResult('Prediction Test', false, $e->getMessage());
        }
    }

    /**
     * Add a check result.
     */
    private function addResult(string $check, bool $passed, string $message, ?float $value = null): void
    {
        $this->results[] = [
            'check' => $check,
            'passed' => $passed,
            'message' => $message,
            'value' => $value
        ];
    }

    /**
     * Get overall health report.
     */
    private function getHealthReport(): array
    {
        $passed = count(array_filter($this->results, fn($r) => $r['passed']));
        $failed = count($this->results) - $passed;
        $total = count($this->results);

        $healthPercentage = $total > 0 ? ($passed / $total) * 100 : 0;

        // Determine overall status
        if ($healthPercentage === 100) {
            $status = 'HEALTHY';
            $emoji = '✅';
        } elseif ($healthPercentage >= 80) {
            $status = 'DEGRADED';
            $emoji = '⚠️';
        } else {
            $status = 'UNHEALTHY';
            $emoji = '❌';
        }

        return [
            'status' => $status,
            'emoji' => $emoji,
            'health_percentage' => round($healthPercentage, 1),
            'checks_passed' => $passed,
            'checks_failed' => $failed,
            'total_checks' => $total,
            'results' => $this->results
        ];
    }

    /**
     * Display health report in a nice format.
     */
    public function displayReport(array $report): void
    {
        echo "\n";
        echo str_repeat('=', 70) . "\n";
        echo "  SYSTEM HEALTH CHECK\n";
        echo str_repeat('=', 70) . "\n\n";

        foreach ($report['results'] as $result) {
            $icon = $result['passed'] ? '✅' : '❌';
            $check = str_pad($result['check'], 25);
            echo "{$icon} {$check} {$result['message']}\n";
        }

        echo "\n" . str_repeat('=', 70) . "\n";
        echo "  OVERALL STATUS: {$report['emoji']} {$report['status']}\n";
        echo str_repeat('=', 70) . "\n\n";

        echo "Summary:\n";
        echo "  Checks Passed: {$report['checks_passed']}/{$report['total_checks']}\n";
        echo "  Health Score: {$report['health_percentage']}%\n\n";

        // Recommendations
        if ($report['status'] !== 'HEALTHY') {
            echo "Recommendations:\n";
            foreach ($report['results'] as $result) {
                if (!$result['passed']) {
                    echo "  ⚠ {$result['check']}: {$this->getRecommendation($result)}\n";
                }
            }
            echo "\n";
        }
    }

    /**
     * Get recommendation for failed check.
     */
    private function getRecommendation(array $result): string
    {
        $check = $result['check'];

        if (strpos($check, 'Python Installation') !== false) {
            return 'Install Python 3.10+: https://www.python.org/downloads/';
        }

        if (strpos($check, 'Python Version') !== false) {
            return 'Upgrade to Python 3.10 or higher';
        }

        if (strpos($check, 'Package') !== false) {
            $package = str_replace('Package: ', '', $check);
            return "Install package: pip install {$package}";
        }

        if (strpos($check, 'Model File') !== false || strpos($check, 'Vectorizer') !== false) {
            return 'Run training: cd 03-sentiment-analysis && php analyze.php';
        }

        if (strpos($check, 'Latency') !== false) {
            return 'Consider caching or switching to REST API for better performance';
        }

        return 'Check documentation for resolution steps';
    }

    /**
     * Format bytes to human-readable size.
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

// Run health monitor
try {
    echo "=== Exercise 3: Health Monitor Solution ===\n";

    $monitor = new HealthMonitor(
        pythonPath: 'python3',
        scriptsDir: __DIR__ . '/../03-sentiment-analysis',
        maxLatencyMs: 200.0
    );

    $report = $monitor->runAllChecks();
    $monitor->displayReport($report);

    echo "✅ Exercise 3 Complete!\n\n";

    echo "What we learned:\n";
    echo "  ✓ Comprehensive health checking prevents production issues\n";
    echo "  ✓ Version validation ensures compatibility\n";
    echo "  ✓ Dependency checks catch missing packages early\n";
    echo "  ✓ Latency monitoring identifies performance problems\n";
    echo "  ✓ Health scores provide quick system status overview\n\n";

    echo "For production:\n";
    echo "  → Run health checks on deployment\n";
    echo "  → Set up monitoring alerts for degraded health\n";
    echo "  → Log health check results for trend analysis\n";
    echo "  → Include health endpoint in load balancer checks\n";

    // Exit with appropriate code
    exit($report['status'] === 'HEALTHY' ? 0 : 1);
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    exit(1);
}


