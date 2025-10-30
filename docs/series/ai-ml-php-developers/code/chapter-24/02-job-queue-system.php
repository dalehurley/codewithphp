<?php

declare(strict_types=1);

/**
 * Job class representing an ML prediction request
 */
final readonly class PredictionJob
{
    public function __construct(
        public string $id,
        public string $type,
        public array $data,
        public int $priority = 0,
        public int $attempts = 0,
        public ?float $createdAt = null,
    ) {}

    public function toJson(): string
    {
        return json_encode([
            'id' => $this->id,
            'type' => $this->type,
            'data' => $this->data,
            'priority' => $this->priority,
            'attempts' => $this->attempts,
            'created_at' => $this->createdAt ?? microtime(true),
        ], JSON_THROW_ON_ERROR);
    }

    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        return new self(
            id: $data['id'],
            type: $data['type'],
            data: $data['data'],
            priority: $data['priority'] ?? 0,
            attempts: $data['attempts'] ?? 0,
            createdAt: $data['created_at'] ?? null,
        );
    }

    public function withIncrementedAttempts(): self
    {
        return new self(
            id: $this->id,
            type: $this->type,
            data: $this->data,
            priority: $this->priority,
            attempts: $this->attempts + 1,
            createdAt: $this->createdAt,
        );
    }
}

/**
 * Queue manager for handling job lifecycle
 */
final class JobQueue
{
    private const MAX_RETRIES = 3;
    private const RETRY_DELAY = 5; // seconds

    public function __construct(
        private readonly Redis $redis,
        private readonly string $queueName = 'ml:jobs',
    ) {}

    public function push(PredictionJob $job): bool
    {
        // Use priority queue if priority is set
        if ($job->priority > 0) {
            return $this->redis->zAdd(
                "{$this->queueName}:priority",
                $job->priority,
                $job->toJson()
            ) !== false;
        }

        return $this->redis->lPush($this->queueName, $job->toJson()) !== false;
    }

    public function pop(int $timeout = 5): ?PredictionJob
    {
        // Check priority queue first
        $result = $this->redis->zPopMax("{$this->queueName}:priority");

        if (!empty($result)) {
            return PredictionJob::fromJson(array_key_first($result));
        }

        // Fall back to regular queue
        $result = $this->redis->brPop([$this->queueName], $timeout);

        if ($result === false || !isset($result[1])) {
            return null;
        }

        return PredictionJob::fromJson($result[1]);
    }

    public function retry(PredictionJob $job): bool
    {
        if ($job->attempts >= self::MAX_RETRIES) {
            return $this->markFailed($job);
        }

        $retryJob = $job->withIncrementedAttempts();

        // Delay retry using sorted set with timestamp as score
        $retryAt = time() + (self::RETRY_DELAY * $job->attempts);

        return $this->redis->zAdd(
            "{$this->queueName}:retry",
            $retryAt,
            $retryJob->toJson()
        ) !== false;
    }

    public function processRetries(): int
    {
        $now = time();
        $jobs = $this->redis->zRangeByScore(
            "{$this->queueName}:retry",
            0,
            $now
        );

        $processed = 0;
        foreach ($jobs as $jobJson) {
            $job = PredictionJob::fromJson($jobJson);
            $this->push($job);
            $this->redis->zRem("{$this->queueName}:retry", $jobJson);
            $processed++;
        }

        return $processed;
    }

    public function markFailed(PredictionJob $job): bool
    {
        return $this->redis->lPush(
            "{$this->queueName}:failed",
            $job->toJson()
        ) !== false;
    }

    public function getQueueDepth(): int
    {
        $regular = $this->redis->lLen($this->queueName);
        $priority = $this->redis->zCard("{$this->queueName}:priority");

        return $regular + $priority;
    }

    public function getStats(): array
    {
        return [
            'queue_depth' => $this->getQueueDepth(),
            'retry_count' => $this->redis->zCard("{$this->queueName}:retry"),
            'failed_count' => $this->redis->lLen("{$this->queueName}:failed"),
        ];
    }
}

// Example usage
if (php_sapi_name() === 'cli') {
    $redis = new Redis();
    $redis->connect(getenv('REDIS_HOST') ?: 'localhost', 6379);

    $queue = new JobQueue($redis);

    // Create and queue a prediction job
    $job = new PredictionJob(
        id: uniqid('pred_'),
        type: 'classification',
        data: ['features' => [1.5, 2.3, 4.1]],
        priority: 1
    );

    $queue->push($job);

    echo "Job queued successfully!\n";
    echo "Queue stats: " . json_encode($queue->getStats(), JSON_PRETTY_PRINT) . "\n";
}
