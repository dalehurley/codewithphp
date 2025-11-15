<?php

declare(strict_types=1);

namespace ClaudePHP\ErrorHandling;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use Exception;

/**
 * Error monitoring and alerting system
 */
class ErrorMonitor
{
    private Logger $logger;
    private int $alertThreshold;
    private ?string $webhookUrl;
    private array $errorCounts = [];
    private array $errorPatterns = [];

    /**
     * @param string $logPath Path to log file
     * @param int $alertThreshold Number of errors before alerting
     * @param string|null $webhookUrl Webhook URL for alerts
     */
    public function __construct(
        string $logPath = '/var/log/claude-errors.log',
        int $alertThreshold = 10,
        ?string $webhookUrl = null
    ) {
        $this->alertThreshold = $alertThreshold;
        $this->webhookUrl = $webhookUrl;

        // Initialize logger
        $this->logger = new Logger('claude-error-monitor');

        // Add rotating file handler
        $handler = new RotatingFileHandler($logPath, 30, Logger::ERROR);
        $formatter = new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context%\n",
            "Y-m-d H:i:s"
        );
        $handler->setFormatter($formatter);
        $this->logger->pushHandler($handler);
    }

    /**
     * Record an error
     */
    public function recordError(
        \Throwable $error,
        array $context = []
    ): void {
        $errorType = get_class($error);
        $errorMessage = $error->getMessage();
        $errorCode = $error->getCode();

        // Increment error count
        if (!isset($this->errorCounts[$errorType])) {
            $this->errorCounts[$errorType] = 0;
        }
        $this->errorCounts[$errorType]++;

        // Log the error
        $this->logger->error($errorMessage, [
            'type' => $errorType,
            'code' => $errorCode,
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'trace' => $error->getTraceAsString(),
            'context' => $context,
        ]);

        // Check for patterns
        $this->detectPattern($errorType, $errorMessage);

        // Check if we should alert
        if ($this->errorCounts[$errorType] >= $this->alertThreshold) {
            $this->sendAlert($errorType, $this->errorCounts[$errorType]);
        }

        // Display error summary
        echo "\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "⚠ ERROR RECORDED\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Type: {$errorType}\n";
        echo "Message: {$errorMessage}\n";
        echo "Code: {$errorCode}\n";
        echo "Count: {$this->errorCounts[$errorType]}\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    }

    /**
     * Get error statistics
     */
    public function getStats(): array
    {
        $totalErrors = array_sum($this->errorCounts);

        return [
            'total_errors' => $totalErrors,
            'errors_by_type' => $this->errorCounts,
            'detected_patterns' => $this->errorPatterns,
            'alert_threshold' => $this->alertThreshold,
        ];
    }

    /**
     * Get top errors
     */
    public function getTopErrors(int $limit = 10): array
    {
        arsort($this->errorCounts);
        return array_slice($this->errorCounts, 0, $limit, true);
    }

    /**
     * Reset error counts
     */
    public function reset(): void
    {
        $this->errorCounts = [];
        $this->errorPatterns = [];
    }

    /**
     * Check if error rate is healthy
     */
    public function isHealthy(): bool
    {
        foreach ($this->errorCounts as $count) {
            if ($count >= $this->alertThreshold) {
                return false;
            }
        }
        return true;
    }

    /**
     * Export error report
     */
    public function exportReport(): array
    {
        $stats = $this->getStats();

        return [
            'timestamp' => date('Y-m-d H:i:s'),
            'health_status' => $this->isHealthy() ? 'healthy' : 'unhealthy',
            'statistics' => $stats,
            'top_errors' => $this->getTopErrors(5),
            'recommendations' => $this->getRecommendations(),
        ];
    }

    /**
     * Detect error patterns
     */
    private function detectPattern(string $errorType, string $message): void
    {
        // Check for rate limiting
        if (stripos($message, 'rate limit') !== false ||
            stripos($message, '429') !== false) {
            $this->errorPatterns['rate_limiting'] =
                ($this->errorPatterns['rate_limiting'] ?? 0) + 1;
        }

        // Check for timeout
        if (stripos($message, 'timeout') !== false) {
            $this->errorPatterns['timeout'] =
                ($this->errorPatterns['timeout'] ?? 0) + 1;
        }

        // Check for authentication
        if (stripos($message, 'auth') !== false ||
            stripos($message, '401') !== false ||
            stripos($message, '403') !== false) {
            $this->errorPatterns['authentication'] =
                ($this->errorPatterns['authentication'] ?? 0) + 1;
        }

        // Check for server errors
        if (stripos($message, '500') !== false ||
            stripos($message, '502') !== false ||
            stripos($message, '503') !== false) {
            $this->errorPatterns['server_error'] =
                ($this->errorPatterns['server_error'] ?? 0) + 1;
        }
    }

    /**
     * Send alert notification
     */
    private function sendAlert(string $errorType, int $count): void
    {
        $message = "Alert: {$errorType} has occurred {$count} times (threshold: {$this->alertThreshold})";

        echo "\n🚨 ALERT: {$message}\n\n";

        if ($this->webhookUrl) {
            $this->sendWebhook($message);
        }
    }

    /**
     * Send webhook notification
     */
    private function sendWebhook(string $message): void
    {
        try {
            $data = [
                'text' => $message,
                'timestamp' => date('c'),
                'stats' => $this->getStats(),
            ];

            $ch = curl_init($this->webhookUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);

            curl_exec($ch);
            curl_close($ch);
        } catch (Exception $e) {
            // Log webhook failure but don't throw
            $this->logger->warning('Failed to send webhook notification', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get recommendations based on error patterns
     */
    private function getRecommendations(): array
    {
        $recommendations = [];

        if (isset($this->errorPatterns['rate_limiting']) &&
            $this->errorPatterns['rate_limiting'] > 5) {
            $recommendations[] = 'Consider implementing rate limiting or increasing delay between requests';
        }

        if (isset($this->errorPatterns['timeout']) &&
            $this->errorPatterns['timeout'] > 5) {
            $recommendations[] = 'Consider increasing timeout values or optimizing request payload';
        }

        if (isset($this->errorPatterns['authentication']) &&
            $this->errorPatterns['authentication'] > 2) {
            $recommendations[] = 'Check API key validity and permissions';
        }

        if (isset($this->errorPatterns['server_error']) &&
            $this->errorPatterns['server_error'] > 3) {
            $recommendations[] = 'Implement circuit breaker pattern to handle server instability';
        }

        return $recommendations;
    }
}
