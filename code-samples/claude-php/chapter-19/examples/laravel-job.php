<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\Queue\ClaudeJob;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Queue Processing - Laravel Job\n\n";

// Simulated job queue
class JobQueue
{
    private array $jobs = [];

    public function dispatch(ClaudeJob $job): void
    {
        $this->jobs[] = $job;
        echo "✓ Job queued\n";
    }

    public function process(): void
    {
        echo "\nProcessing " . count($this->jobs) . " queued jobs...\n\n";

        foreach ($this->jobs as $i => $job) {
            echo "Job " . ($i + 1) . ":\n";
            try {
                $job->handle();
            } catch (\Exception $e) {
                echo "  Error: {$e->getMessage()}\n";
            }
            echo "\n";
        }
    }
}

$queue = new JobQueue();

// Queue multiple jobs
echo "Queueing jobs...\n";

$queue->dispatch(new ClaudeJob('Explain what PHP is'));
$queue->dispatch(new ClaudeJob('What is Laravel?'));
$queue->dispatch(new ClaudeJob('Describe RESTful APIs'));

// Process queue
$queue->process();

echo "Benefits of queue processing:\n";
echo "- Non-blocking API calls\n";
echo "- Better resource management\n";
echo "- Automatic retry on failure\n";
echo "- Scale workers independently\n";
