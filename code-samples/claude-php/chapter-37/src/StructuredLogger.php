<?php

declare(strict_types=1);

namespace App;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class StructuredLogger implements LoggerInterface
{
    private string $logPath;
    
    public function __construct(string $logPath = 'php://stdout')
    {
        $this->logPath = $logPath;
    }
    
    public function log($level, $message, array $context = []): void
    {
        $entry = [
            'timestamp' => microtime(true),
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'memory' => memory_get_usage(true),
            'pid' => getmypid()
        ];
        
        file_put_contents($this->logPath, json_encode($entry) . PHP_EOL, FILE_APPEND);
    }
    
    public function emergency($message, array $context = []): void { $this->log(LogLevel::EMERGENCY, $message, $context); }
    public function alert($message, array $context = []): void { $this->log(LogLevel::ALERT, $message, $context); }
    public function critical($message, array $context = []): void { $this->log(LogLevel::CRITICAL, $message, $context); }
    public function error($message, array $context = []): void { $this->log(LogLevel::ERROR, $message, $context); }
    public function warning($message, array $context = []): void { $this->log(LogLevel::WARNING, $message, $context); }
    public function notice($message, array $context = []): void { $this->log(LogLevel::NOTICE, $message, $context); }
    public function info($message, array $context = []): void { $this->log(LogLevel::INFO, $message, $context); }
    public function debug($message, array $context = []): void { $this->log(LogLevel::DEBUG, $message, $context); }
}
