<?php

declare(strict_types=1);

require_once '02-job-queue-system.php';

/**
 * ML Worker daemon that processes prediction jobs
 */
final class MLWorker
{
    private bool $shouldStop = false;
    private int $processedJobs = 0;
    private int $failedJobs = 0;

    public function __construct(
        private readonly JobQueue $queue,
        private readonly Redis $redis,
        private readonly string $workerName = 'worker-1',
    ) {
        // Register signal handlers for graceful shutdown
        pcntl_async_signals(true);
        pcntl_signal(SIGTERM, [$this, 'handleShutdown']);
        pcntl_signal(SIGINT, [$this, 'handleShutdown']);
    }

    public function start(): void
    {
        echo "[{$this->workerName}] Worker started\n";
        echo "[{$this->workerName}] Waiting for jobs...\n";

        // Process retry queue periodically
        $lastRetryCheck = time();

        while (!$this->shouldStop) {
            // Check for jobs to retry every 10 seconds
            if (time() - $lastRetryCheck > 10) {
                $retried = $this->queue->processRetries();
                if ($retried > 0) {
                    echo "[{$this->workerName}] Requeued {$retried} retry jobs\n";
                }
                $lastRetryCheck = time();
            }

            // Pop job with timeout
            $job = $this->queue->pop(5);

            if ($job === null) {
                continue;
            }

            echo "[{$this->workerName}] Processing job {$job->id} (attempt {$job->attempts})\n";

            try {
                $result = $this->processJob($job);
                $this->storeResult($job->id, $result);
                $this->processedJobs++;

                echo "[{$this->workerName}] ✓ Job {$job->id} completed\n";
            } catch (Exception $e) {
                echo "[{$this->workerName}] ✗ Job {$job->id} failed: {$e->getMessage()}\n";

                $this->failedJobs++;
                $this->queue->retry($job);
            }

            // Publish metrics
            $this->publishMetrics();
        }

        echo "[{$this->workerName}] Graceful shutdown complete\n";
    }

    private function processJob(PredictionJob $job): array
    {
        // Simulate ML inference based on job type
        return match ($job->type) {
            'classification' => $this->runClassification($job->data),
            'regression' => $this->runRegression($job->data),
            'clustering' => $this->runClustering($job->data),
            default => throw new RuntimeException("Unknown job type: {$job->type}"),
        };
    }

    private function runClassification(array $data): array
    {
        if (!isset($data['features']) || !is_array($data['features'])) {
            throw new InvalidArgumentException('Features required for classification');
        }

        // Simulate classification (replace with actual model)
        $startTime = microtime(true);

        // Simulated inference delay
        usleep(random_int(100000, 500000)); // 100-500ms

        $prediction = random_int(0, 1);
        $confidence = random_int(70, 99) / 100;

        $inferenceTime = microtime(true) - $startTime;

        return [
            'prediction' => $prediction,
            'confidence' => $confidence,
            'inference_time' => $inferenceTime,
            'model' => 'classifier-v1',
        ];
    }

    private function runRegression(array $data): array
    {
        if (!isset($data['features']) || !is_array($data['features'])) {
            throw new InvalidArgumentException('Features required for regression');
        }

        $startTime = microtime(true);

        // Simulated inference
        usleep(random_int(100000, 500000));

        $prediction = array_sum($data['features']) / count($data['features']);

        $inferenceTime = microtime(true) - $startTime;

        return [
            'prediction' => $prediction,
            'inference_time' => $inferenceTime,
            'model' => 'regressor-v1',
        ];
    }

    private function runClustering(array $data): array
    {
        if (!isset($data['samples']) || !is_array($data['samples'])) {
            throw new InvalidArgumentException('Samples required for clustering');
        }

        $startTime = microtime(true);

        usleep(random_int(200000, 800000));

        $clusters = random_int(2, 5);

        $inferenceTime = microtime(true) - $startTime;

        return [
            'num_clusters' => $clusters,
            'inference_time' => $inferenceTime,
            'model' => 'kmeans-v1',
        ];
    }

    private function storeResult(string $jobId, array $result): void
    {
        $key = "result:{$jobId}";
        $ttl = 3600; // Results expire after 1 hour

        $this->redis->setEx(
            $key,
            $ttl,
            json_encode($result, JSON_THROW_ON_ERROR)
        );
    }

    private function publishMetrics(): void
    {
        $metrics = [
            'worker_name' => $this->workerName,
            'processed' => $this->processedJobs,
            'failed' => $this->failedJobs,
            'timestamp' => time(),
        ];

        $this->redis->set(
            "metrics:worker:{$this->workerName}",
            json_encode($metrics),
            60 // Refresh every minute
        );
    }

    public function handleShutdown(): void
    {
        echo "\n[{$this->workerName}] Shutdown signal received, finishing current job...\n";
        $this->shouldStop = true;
    }

    public function getStats(): array
    {
        return [
            'worker_name' => $this->workerName,
            'processed_jobs' => $this->processedJobs,
            'failed_jobs' => $this->failedJobs,
        ];
    }
}

// Start worker if run directly
if (php_sapi_name() === 'cli') {
    $redis = new Redis();
    $redis->connect(getenv('REDIS_HOST') ?: 'localhost', 6379);

    $queue = new JobQueue($redis);
    $worker = new MLWorker(
        queue: $queue,
        redis: $redis,
        workerName: getenv('WORKER_NAME') ?: 'worker-' . getmypid()
    );

    $worker->start();
}
