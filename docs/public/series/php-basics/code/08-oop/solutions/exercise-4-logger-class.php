<?php

declare(strict_types=1);

/**
 * Exercise 4: Create a Logger Class with Static Methods
 * 
 * Goal: Practice using static properties, methods, and class constants.
 * 
 * Requirements:
 * - Class constants for log levels (DEBUG, INFO, WARNING, ERROR)
 * - Private static property $logs (array) to store messages
 * - Private static property $logCount (int) to track total logs
 * - Static method log() adds messages with timestamp
 * - Static method getLogCount() returns total count
 * - Static method getLogs() returns all logs
 * - Static method clearLogs() empties the logs array
 */

class Logger
{
    // Log level constants
    public const DEBUG = 'debug';
    public const INFO = 'info';
    public const WARNING = 'warning';
    public const ERROR = 'error';

    // Static properties to store logs
    private static array $logs = [];
    private static int $logCount = 0;

    /**
     * Log a message with the specified level
     */
    public static function log(string $level, string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $formattedLog = "[{$timestamp}] [{$level}] {$message}";

        self::$logs[] = $formattedLog;
        self::$logCount++;

        echo $formattedLog . PHP_EOL;
    }

    /**
     * Get the total number of logs
     */
    public static function getLogCount(): int
    {
        return self::$logCount;
    }

    /**
     * Get all logs
     */
    public static function getLogs(): array
    {
        return self::$logs;
    }

    /**
     * Clear all logs
     */
    public static function clearLogs(): void
    {
        self::$logs = [];
        self::$logCount = 0;
        echo "All logs cleared." . PHP_EOL;
    }
}

// Test the Logger class
echo "=== Logger System Demo ===" . PHP_EOL . PHP_EOL;

Logger::log(Logger::INFO, 'Application started');
Logger::log(Logger::DEBUG, 'Loading configuration files');
Logger::log(Logger::INFO, 'Database connection established');
Logger::log(Logger::WARNING, 'High memory usage detected');
Logger::log(Logger::ERROR, 'Failed to connect to external API');

echo PHP_EOL . "--- Log Statistics ---" . PHP_EOL;
echo "Total logs: " . Logger::getLogCount() . PHP_EOL;

echo PHP_EOL . "--- All Logs ---" . PHP_EOL;
$allLogs = Logger::getLogs();
foreach ($allLogs as $index => $log) {
    echo ($index + 1) . ". " . $log . PHP_EOL;
}

echo PHP_EOL . "--- Clearing Logs ---" . PHP_EOL;
Logger::clearLogs();
echo "Log count after clearing: " . Logger::getLogCount() . PHP_EOL;
