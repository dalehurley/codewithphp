<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Predis\Client as RedisClient;

echo "Queue Processing - Progress Tracking\n\n";

class ProgressTracker
{
    private RedisClient $redis;
    private string $jobId;

    public function __construct(RedisClient $redis, string $jobId)
    {
        $this->redis = $redis;
        $this->jobId = $jobId;
    }

    public function start(int $totalSteps): void
    {
        $this->redis->hmset("job:{$this->jobId}", [
            'status' => 'running',
            'progress' => 0,
            'total' => $totalSteps,
            'started_at' => time(),
        ]);
    }

    public function updateProgress(int $currentStep, string $message = ''): void
    {
        $total = (int) $this->redis->hget("job:{$this->jobId}", 'total');
        $percentage = $total > 0 ? round(($currentStep / $total) * 100, 2) : 0;

        $this->redis->hmset("job:{$this->jobId}", [
            'progress' => $currentStep,
            'percentage' => $percentage,
            'message' => $message,
            'updated_at' => time(),
        ]);

        echo "Progress: {$currentStep}/{$total} ({$percentage}%) - {$message}\n";
    }

    public function complete(): void
    {
        $started = (int) $this->redis->hget("job:{$this->jobId}", 'started_at');
        $duration = time() - $started;

        $this->redis->hmset("job:{$this->jobId}", [
            'status' => 'completed',
            'progress' => $this->redis->hget("job:{$this->jobId}", 'total'),
            'percentage' => 100,
            'completed_at' => time(),
            'duration' => $duration,
        ]);

        echo "\n✓ Job completed in {$duration} seconds\n";
    }

    public function getProgress(): array
    {
        return $this->redis->hgetall("job:{$this->jobId}");
    }
}

// Setup Redis (or use in-memory simulation)
try {
    $redis = new RedisClient([
        'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
        'port' => (int) ($_ENV['REDIS_PORT'] ?? 6379),
    ]);
    $redis->ping();
} catch (\Exception $e) {
    echo "Note: Using simulated progress tracking (Redis not available)\n\n";

    // Simulated tracker for demo
    class SimulatedTracker
    {
        private array $data = [];

        public function start(int $total)
        {
            $this->data = ['status' => 'running', 'progress' => 0, 'total' => $total];
        }

        public function updateProgress(int $current, string $msg = '')
        {
            $percentage = round(($current / $this->data['total']) * 100, 2);
            echo "Progress: {$current}/{$this->data['total']} ({$percentage}%) - {$msg}\n";
        }

        public function complete()
        {
            echo "\n✓ Job completed\n";
        }
    }

    $tracker = new SimulatedTracker();
    $tracker->start(5);

    for ($i = 1; $i <= 5; $i++) {
        sleep(1);
        $tracker->updateProgress($i, "Processing item {$i}");
    }

    $tracker->complete();
    exit;
}

$jobId = 'job-' . uniqid();
$tracker = new ProgressTracker($redis, $jobId);

echo "Starting job: {$jobId}\n\n";

$tasks = [
    'Analyzing document 1',
    'Analyzing document 2',
    'Analyzing document 3',
    'Generating summary',
    'Finalizing results',
];

$tracker->start(count($tasks));

foreach ($tasks as $i => $task) {
    sleep(1); // Simulate work
    $tracker->updateProgress($i + 1, $task);
}

$tracker->complete();

echo "\nFinal progress:\n";
print_r($tracker->getProgress());
