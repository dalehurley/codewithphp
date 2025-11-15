<?php

declare(strict_types=1);

namespace App;

class Supervisor
{
    private array $workers = [];
    
    public function addWorker(string $name, Agent $worker): void
    {
        $this->workers[$name] = $worker;
    }
    
    public function coordinate(string $task): array
    {
        $results = [];
        foreach ($this->workers as $name => $worker) {
            $results[$name] = $worker->execute($task);
        }
        return $this->aggregateResults($results);
    }
    
    private function aggregateResults(array $results): array
    {
        return ['combined' => $results, 'status' => 'completed'];
    }
}
