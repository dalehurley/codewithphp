<?php

declare(strict_types=1);

/**
 * Test Script for Chapter 11 Examples
 * 
 * This script tests all code examples to ensure they work correctly.
 * Similar to /testing/test-all-samples.php but specific to Chapter 11.
 * 
 * Usage:
 *   php test-examples.php
 */

class Chapter11Tester
{
    private int $passed = 0;
    private int $failed = 0;
    private int $skipped = 0;
    private array $results = [];

    public function runAllTests(): void
    {
        echo "\n";
        echo str_repeat('=', 70) . "\n";
        echo "  Chapter 11: PHP-Python Integration - Test Suite\n";
        echo str_repeat('=', 70) . "\n\n";

        // Test 1: Quick Start
        $this->testQuickStart();

        // Test 2: Simple Shell
        $this->testSimpleShell();

        // Test 3: Data Passing
        $this->testDataPassing();

        // Test 4: Sentiment Analysis (if model exists)
        $this->testSentimentAnalysis();

        // Test 5: REST API (if Flask is running)
        $this->testRestApi();

        // Test 6: Secure Executor
        $this->testSecureExecutor();

        // Display summary
        $this->displaySummary();
    }

    private function testQuickStart(): void
    {
        $this->runTest('Quick Start Integration', function () {
            $output = shell_exec('php ' . __DIR__ . '/quick_integrate.php 2>&1');

            if (
                strpos($output, 'PHP-Python Integration Working!') !== false &&
                strpos($output, 'Sentiment:') !== false
            ) {
                return ['passed' => true, 'message' => 'Quick start working'];
            }

            return ['passed' => false, 'message' => 'Quick start failed: ' . substr($output, 0, 100)];
        });
    }

    private function testSimpleShell(): void
    {
        $this->runTest('01-simple-shell/hello.php', function () {
            $output = shell_exec('php ' . __DIR__ . '/01-simple-shell/hello.php 2>&1');

            if (strpos($output, 'Integration working successfully') !== false) {
                return ['passed' => true, 'message' => 'Shell integration working'];
            }

            return ['passed' => false, 'message' => 'Shell integration failed'];
        });
    }

    private function testDataPassing(): void
    {
        $this->runTest('02-data-passing/exchange.php', function () {
            $output = shell_exec('php ' . __DIR__ . '/02-data-passing/exchange.php 2>&1');

            if (strpos($output, 'Complex data exchange working') !== false) {
                return ['passed' => true, 'message' => 'Data exchange working'];
            }

            return ['passed' => false, 'message' => 'Data exchange failed'];
        });
    }

    private function testSentimentAnalysis(): void
    {
        $modelPath = __DIR__ . '/03-sentiment-analysis/models/sentiment_model.pkl';

        if (!file_exists($modelPath)) {
            $this->results[] = [
                'test' => '03-sentiment-analysis',
                'status' => 'skipped',
                'message' => 'Model not trained (run: php 03-sentiment-analysis/analyze.php)'
            ];
            $this->skipped++;
            return;
        }

        $this->runTest('03-sentiment-analysis/predict.py', function () {
            $testData = json_encode(['text' => 'This is a test']);
            $escaped = escapeshellarg($testData);
            $output = shell_exec("python3 " . __DIR__ . "/03-sentiment-analysis/predict.py {$escaped} 2>&1");

            $result = json_decode($output, true);

            if ($result && isset($result['sentiment']) && isset($result['confidence'])) {
                return ['passed' => true, 'message' => 'Sentiment prediction working'];
            }

            return ['passed' => false, 'message' => 'Sentiment prediction failed'];
        });
    }

    private function testRestApi(): void
    {
        // Check if Flask is running
        $ch = curl_init('http://127.0.0.1:5000/health');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $this->results[] = [
                'test' => '04-rest-api-example',
                'status' => 'skipped',
                'message' => 'Flask server not running (start: python3 04-rest-api-example/flask_server.py)'
            ];
            $this->skipped++;
            return;
        }

        $this->runTest('04-rest-api-example/php_client.php', function () {
            $output = shell_exec('php ' . __DIR__ . '/04-rest-api-example/php_client.php 2>&1');

            if (strpos($output, 'API integration working') !== false) {
                return ['passed' => true, 'message' => 'REST API integration working'];
            }

            return ['passed' => false, 'message' => 'REST API integration failed'];
        });
    }

    private function testSecureExecutor(): void
    {
        $this->runTest('05-production-patterns/secure_executor.php', function () {
            $output = shell_exec('php ' . __DIR__ . '/05-production-patterns/secure_executor.php 2>&1');

            if (strpos($output, 'Security demonstration complete') !== false) {
                return ['passed' => true, 'message' => 'Secure executor working'];
            }

            return ['passed' => false, 'message' => 'Secure executor failed'];
        });
    }

    private function runTest(string $testName, callable $testFunction): void
    {
        try {
            $result = $testFunction();

            $this->results[] = [
                'test' => $testName,
                'status' => $result['passed'] ? 'passed' : 'failed',
                'message' => $result['message']
            ];

            if ($result['passed']) {
                $this->passed++;
            } else {
                $this->failed++;
            }
        } catch (Exception $e) {
            $this->results[] = [
                'test' => $testName,
                'status' => 'error',
                'message' => $e->getMessage()
            ];
            $this->failed++;
        }
    }

    private function displaySummary(): void
    {
        echo "\n" . str_repeat('=', 70) . "\n";
        echo "  Test Results\n";
        echo str_repeat('=', 70) . "\n\n";

        foreach ($this->results as $result) {
            $icon = match ($result['status']) {
                'passed' => '✅',
                'failed' => '❌',
                'error' => '💥',
                'skipped' => '⏭️',
                default => '❓'
            };

            $test = str_pad($result['test'], 40);
            echo "{$icon} {$test} {$result['message']}\n";
        }

        $total = $this->passed + $this->failed + $this->skipped;
        $passRate = $total > 0 ? round(($this->passed / ($this->passed + $this->failed)) * 100, 1) : 0;

        echo "\n" . str_repeat('=', 70) . "\n";
        echo "  Summary\n";
        echo str_repeat('=', 70) . "\n\n";
        echo "Total Tests: {$total}\n";
        echo "Passed: {$this->passed}\n";
        echo "Failed: {$this->failed}\n";
        echo "Skipped: {$this->skipped}\n";
        echo "Pass Rate: {$passRate}%\n\n";

        if ($this->passed === ($total - $this->skipped)) {
            echo "🎉 All tests passed!\n\n";
        } elseif ($this->passed > 0) {
            echo "⚠️  Some tests failed. Review the output above for details.\n\n";
        } else {
            echo "❌ All tests failed. Check your Python installation and dependencies.\n\n";
        }

        // Helpful hints
        if ($this->skipped > 0) {
            echo "Note: Skipped tests require additional setup:\n";
            foreach ($this->results as $result) {
                if ($result['status'] === 'skipped') {
                    echo "  • {$result['test']}: {$result['message']}\n";
                }
            }
            echo "\n";
        }

        if ($this->failed > 0) {
            echo "Troubleshooting:\n";
            echo "  • Verify Python 3.10+ installed: python3 --version\n";
            echo "  • Install Python packages: pip install pandas scikit-learn joblib flask\n";
            echo "  • Train sentiment model: cd 03-sentiment-analysis && php analyze.php\n";
            echo "  • Start Flask API: python3 04-rest-api-example/flask_server.py\n";
            echo "  • Check README.md for detailed setup instructions\n";
        }
    }
}

// Run tests
$tester = new Chapter11Tester();
$tester->runAllTests();


