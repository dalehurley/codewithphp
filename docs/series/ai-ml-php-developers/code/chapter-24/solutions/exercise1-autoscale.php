<?php

declare(strict_types=1);

/**
 * Exercise 1 Solution: Auto-scaling based on queue depth
 * Monitors queue and scales workers automatically
 */

require_once __DIR__ . '/../02-job-queue-system.php';

final class AutoScaler
{
    private const MIN_WORKERS = 2;
    private const MAX_WORKERS = 10;
    private const SCALE_UP_THRESHOLD = 50;
    private const SCALE_DOWN_THRESHOLD = 10;
    private const SCALE_DOWN_WAIT = 300; // 5 minutes
    private const CHECK_INTERVAL = 30; // seconds

    private int $currentWorkers = 2;
    private int $lastScaleTime = 0;
    private int $lowQueueStartTime = 0;

    public function __construct(
        private readonly JobQueue $queue,
        private readonly string $composeFile = 'docker-compose.yml',
    ) {
        $this->currentWorkers = $this->getCurrentWorkerCount();
        $this->lastScaleTime = time();
    }

    public function start(): void
    {
        echo "Auto-scaler started\n";
        echo "Current workers: {$this->currentWorkers}\n";
        echo "Scale up threshold: " . self::SCALE_UP_THRESHOLD . "\n";
        echo "Scale down threshold: " . self::SCALE_DOWN_THRESHOLD . "\n\n";

        while (true) {
            $queueDepth = $this->queue->getQueueDepth();
            $stats = $this->queue->getStats();

            echo "[" . date('H:i:s') . "] Queue depth: {$queueDepth}, Workers: {$this->currentWorkers}\n";

            // Check if we should scale up
            if ($queueDepth > self::SCALE_UP_THRESHOLD && $this->currentWorkers < self::MAX_WORKERS) {
                $this->scaleUp();
            }

            // Check if we should scale down
            if ($queueDepth < self::SCALE_DOWN_THRESHOLD && $this->currentWorkers > self::MIN_WORKERS) {
                $this->considerScaleDown();
            } else {
                $this->lowQueueStartTime = 0; // Reset timer
            }

            sleep(self::CHECK_INTERVAL);
        }
    }

    private function scaleUp(): void
    {
        // Prevent thrashing - wait at least 60 seconds between scale operations
        if (time() - $this->lastScaleTime < 60) {
            echo "  Skipping scale up (too soon since last scale)\n";
            return;
        }

        $newCount = min($this->currentWorkers + 2, self::MAX_WORKERS);

        echo "  ⬆️  Scaling UP from {$this->currentWorkers} to {$newCount} workers\n";

        $this->scaleWorkers($newCount);
        $this->currentWorkers = $newCount;
        $this->lastScaleTime = time();
    }

    private function considerScaleDown(): void
    {
        // Start timing low queue if not already started
        if ($this->lowQueueStartTime === 0) {
            $this->lowQueueStartTime = time();
            return;
        }

        // Only scale down after queue has been low for SCALE_DOWN_WAIT seconds
        $lowDuration = time() - $this->lowQueueStartTime;

        if ($lowDuration >= self::SCALE_DOWN_WAIT) {
            $this->scaleDown();
            $this->lowQueueStartTime = 0;
        } else {
            echo "  Queue low for {$lowDuration}s (need " . self::SCALE_DOWN_WAIT . "s before scaling down)\n";
        }
    }

    private function scaleDown(): void
    {
        $newCount = max($this->currentWorkers - 2, self::MIN_WORKERS);

        echo "  ⬇️  Scaling DOWN from {$this->currentWorkers} to {$newCount} workers\n";

        $this->scaleWorkers($newCount);
        $this->currentWorkers = $newCount;
        $this->lastScaleTime = time();
    }

    private function scaleWorkers(int $count): void
    {
        $command = "docker compose -f {$this->composeFile} up -d --scale worker={$count}";

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            echo "  ❌ Failed to scale workers\n";
            echo "  Output: " . implode("\n", $output) . "\n";
        } else {
            echo "  ✅ Scaled to {$count} workers successfully\n";
        }
    }

    private function getCurrentWorkerCount(): int
    {
        $command = "docker compose -f {$this->composeFile} ps worker --format json";
        $output = shell_exec($command);

        if (!$output) {
            return self::MIN_WORKERS;
        }

        $lines = array_filter(explode("\n", trim($output)));
        return count($lines);
    }
}

// Run autoscaler
if (php_sapi_name() === 'cli') {
    $redis = new Redis();
    $redis->connect(getenv('REDIS_HOST') ?: 'localhost', 6379);

    $queue = new JobQueue($redis);
    $scaler = new AutoScaler($queue);

    // Handle shutdown gracefully
    pcntl_async_signals(true);
    pcntl_signal(SIGTERM, function () {
        echo "\nShutting down autoscaler...\n";
        exit(0);
    });
    pcntl_signal(SIGINT, function () {
        echo "\nShutting down autoscaler...\n";
        exit(0);
    });

    $scaler->start();
}
