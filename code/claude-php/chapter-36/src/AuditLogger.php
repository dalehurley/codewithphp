<?php

declare(strict_types=1);

namespace App;

class AuditLogger
{
    public function __construct(
        private string $logPath
    ) {}
    
    public function log(string $event, array $data): void
    {
        $entry = [
            'timestamp' => date('c'),
            'event' => $event,
            'data' => $data,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'cli',
            'user' => $_SERVER['USER'] ?? 'system'
        ];
        
        file_put_contents(
            $this->logPath,
            json_encode($entry) . PHP_EOL,
            FILE_APPEND
        );
    }
    
    public function query(string $event, ?int $limit = null): array
    {
        if (!file_exists($this->logPath)) {
            return [];
        }
        
        $lines = file($this->logPath);
        $results = [];
        
        foreach ($lines as $line) {
            $entry = json_decode($line, true);
            if ($entry && $entry['event'] === $event) {
                $results[] = $entry;
            }
        }
        
        return $limit ? array_slice($results, -$limit) : $results;
    }
}
