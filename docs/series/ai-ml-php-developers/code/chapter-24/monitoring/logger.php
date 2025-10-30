<?php

declare(strict_types=1);

/**
 * Centralized logging utility
 * Provides structured logging with different levels
 */

enum LogLevel: string
{
    case DEBUG = 'DEBUG';
    case INFO = 'INFO';
    case WARNING = 'WARNING';
    case ERROR = 'ERROR';
    case CRITICAL = 'CRITICAL';
}

final class Logger
{
    public function __construct(
        private readonly string $logFile = __DIR__ . '/../logs/app.log',
        private readonly LogLevel $minLevel = LogLevel::INFO,
    ) {
        // Ensure log directory exists
        $dir = dirname($this->logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    public function log(LogLevel $level, string $message, array $context = []): void
    {
        // Skip if below minimum level
        if ($this->shouldSkip($level)) {
            return;
        }

        // Sanitize context (remove sensitive data)
        $context = $this->sanitizeContext($context);

        // Build log entry
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level->value,
            'message' => $message,
            'context' => $context,
            'process_id' => getmypid(),
        ];

        // Write to file
        $json = json_encode($entry, JSON_THROW_ON_ERROR) . "\n";
        file_put_contents($this->logFile, $json, FILE_APPEND | LOCK_EX);

        // Also write to stderr for critical errors
        if ($level === LogLevel::CRITICAL || $level === LogLevel::ERROR) {
            fwrite(STDERR, "[{$level->value}] {$message}\n");
        }
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    private function shouldSkip(LogLevel $level): bool
    {
        $levels = [
            LogLevel::DEBUG->value => 0,
            LogLevel::INFO->value => 1,
            LogLevel::WARNING->value => 2,
            LogLevel::ERROR->value => 3,
            LogLevel::CRITICAL->value => 4,
        ];

        return $levels[$level->value] < $levels[$this->minLevel->value];
    }

    private function sanitizeContext(array $context): array
    {
        $sensitive = ['password', 'token', 'secret', 'key', 'auth', 'api_key'];

        foreach ($context as $key => $value) {
            if (in_array(strtolower($key), $sensitive, true)) {
                $context[$key] = '***REDACTED***';
            }
        }

        return $context;
    }
}

// Example usage
if (php_sapi_name() === 'cli') {
    $logger = new Logger();

    $logger->info('Application started', ['version' => '1.0.0']);
    $logger->debug('Debug message', ['data' => [1, 2, 3]]);
    $logger->warning('Queue depth high', ['depth' => 150]);
    $logger->error('Failed to process job', ['job_id' => 'test_123']);

    // Sensitive data is automatically sanitized
    $logger->info('User authenticated', [
        'user_id' => 42,
        'password' => 'should_not_appear_in_logs'
    ]);

    echo "Logs written to application log file\n";
}
