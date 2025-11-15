<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\ErrorHandling\ErrorMonitor;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Claude API - Error Monitoring and Alerting\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Initialize error monitor
$monitor = new ErrorMonitor(
    logPath: $_ENV['ERROR_MONITOR_LOG_PATH'] ?? __DIR__ . '/../var/logs/errors.log',
    alertThreshold: (int) ($_ENV['ERROR_MONITOR_ALERT_THRESHOLD'] ?? 5),
    webhookUrl: $_ENV['ERROR_MONITOR_WEBHOOK_URL'] ?? null
);

// Example 1: Recording various error types
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 1: Recording Different Error Types\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

// Simulate rate limit error
try {
    throw new Exception('Rate limit exceeded. Please try again later.', 429);
} catch (Exception $e) {
    $monitor->recordError($e, [
        'endpoint' => '/v1/messages',
        'user_id' => 'user-123',
        'api_key' => 'sk-ant-***',
    ]);
}

// Simulate timeout error
try {
    throw new Exception('Request timeout after 30 seconds', 408);
} catch (Exception $e) {
    $monitor->recordError($e, [
        'endpoint' => '/v1/messages',
        'timeout' => 30,
    ]);
}

// Simulate authentication error
try {
    throw new Exception('Invalid authentication credentials', 401);
} catch (Exception $e) {
    $monitor->recordError($e, [
        'endpoint' => '/v1/messages',
        'api_key' => 'invalid-key',
    ]);
}

// Show current stats
echo "Current Statistics:\n";
print_r($monitor->getStats());

// Example 2: Pattern detection
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 2: Error Pattern Detection\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

// Simulate multiple rate limit errors
for ($i = 1; $i <= 8; $i++) {
    try {
        throw new Exception('Rate limit exceeded (429)', 429);
    } catch (Exception $e) {
        $monitor->recordError($e, ['attempt' => $i]);
    }
    usleep(100000); // 100ms
}

echo "\nDetected Patterns:\n";
$stats = $monitor->getStats();
if (!empty($stats['detected_patterns'])) {
    foreach ($stats['detected_patterns'] as $pattern => $count) {
        echo "  • {$pattern}: {$count} occurrences\n";
    }
}

// Example 3: Alert threshold
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 3: Alert Threshold Monitoring\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

echo "Alert threshold: {$stats['alert_threshold']} errors\n";
echo "Current health: " . ($monitor->isHealthy() ? "✓ Healthy" : "✗ Unhealthy") . "\n\n";

// Generate server errors to trigger alert
echo "Simulating server errors...\n";
for ($i = 1; $i <= 6; $i++) {
    try {
        throw new Exception('Internal server error', 500);
    } catch (Exception $e) {
        $monitor->recordError($e, ['server' => 'api-1']);
    }
}

echo "\nHealth after errors: " . ($monitor->isHealthy() ? "✓ Healthy" : "✗ Unhealthy") . "\n";

// Example 4: Top errors report
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 4: Top Errors Report\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$topErrors = $monitor->getTopErrors(5);

echo "Top 5 Errors:\n";
$rank = 1;
foreach ($topErrors as $errorType => $count) {
    $shortType = substr($errorType, strrpos($errorType, '\\') + 1);
    echo "  {$rank}. {$shortType}: {$count} occurrences\n";
    $rank++;
}

// Example 5: Export comprehensive report
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 5: Comprehensive Error Report\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$report = $monitor->exportReport();

echo json_encode($report, JSON_PRETTY_PRINT);

// Example 6: Recommendations
echo "\n\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 6: Automated Recommendations\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

if (!empty($report['recommendations'])) {
    echo "Based on error patterns, we recommend:\n\n";
    foreach ($report['recommendations'] as $i => $recommendation) {
        echo ($i + 1) . ". {$recommendation}\n";
    }
} else {
    echo "✓ No issues detected. System is operating normally.\n";
}

// Example 7: Real-time monitoring simulation
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Example 7: Real-Time Monitoring Simulation\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$monitor->reset();
echo "Monitoring reset. Simulating live traffic...\n\n";

$errors = [
    ['msg' => 'Success', 'code' => 200, 'throws' => false],
    ['msg' => 'Success', 'code' => 200, 'throws' => false],
    ['msg' => 'Rate limit exceeded', 'code' => 429, 'throws' => true],
    ['msg' => 'Success', 'code' => 200, 'throws' => false],
    ['msg' => 'Timeout', 'code' => 408, 'throws' => true],
    ['msg' => 'Success', 'code' => 200, 'throws' => false],
    ['msg' => 'Server error', 'code' => 500, 'throws' => true],
    ['msg' => 'Success', 'code' => 200, 'throws' => false],
    ['msg' => 'Rate limit exceeded', 'code' => 429, 'throws' => true],
    ['msg' => 'Success', 'code' => 200, 'throws' => false],
];

foreach ($errors as $i => $error) {
    $requestNum = $i + 1;

    if ($error['throws']) {
        try {
            throw new Exception($error['msg'], $error['code']);
        } catch (Exception $e) {
            $monitor->recordError($e, ['request' => $requestNum]);
        }
    } else {
        echo "Request {$requestNum}: ✓ {$error['msg']}\n";
    }

    usleep(200000); // 200ms
}

// Final summary
echo "\n" . str_repeat("─", 60) . "\n";
echo "MONITORING SUMMARY\n";
echo str_repeat("─", 60) . "\n";

$finalStats = $monitor->getStats();
echo "Total Errors: {$finalStats['total_errors']}\n";
echo "Health Status: " . ($monitor->isHealthy() ? "✓ Healthy" : "✗ Unhealthy") . "\n";
echo "\nError Breakdown:\n";
foreach ($finalStats['errors_by_type'] as $type => $count) {
    echo "  • " . substr($type, strrpos($type, '\\') + 1) . ": {$count}\n";
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Error Monitoring Best Practices:\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "1. Log all errors with context (user, endpoint, timestamp)\n";
echo "2. Set up alerts for error thresholds\n";
echo "3. Monitor error patterns for system issues\n";
echo "4. Use webhooks for real-time notifications\n";
echo "5. Generate regular error reports\n";
echo "6. Track error rates over time\n";
echo "7. Implement automated remediation for common patterns\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
