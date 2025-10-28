<?php

declare(strict_types=1);

/**
 * Message Queue Pattern for Async ML Processing
 * 
 * Use this pattern when:
 * - ML task takes >5 seconds (model training, large predictions)
 * - User doesn't need immediate result
 * - High throughput is required
 * - Results can be delivered later (callback, polling, email)
 * 
 * This example uses Redis as the message queue.
 * Production alternatives: RabbitMQ, AWS SQS, Google Pub/Sub
 */

class AsyncMLQueue
{
    private Redis $redis;

    public function __construct(
        string $host = '127.0.0.1',
        int $port = 6379
    ) {
        if (!extension_loaded('redis')) {
            throw new RuntimeException(
                'Redis extension required. Install: pecl install redis'
            );
        }

        $this->redis = new Redis();
        if (!$this->redis->connect($host, $port)) {
            throw new RuntimeException("Failed to connect to Redis at {$host}:{$port}");
        }
    }

    /**
     * Submit ML task to queue for async processing.
     */
    public function submitTask(string $taskType, array $data, ?string $callbackUrl = null): string
    {
        $taskId = $this->generateTaskId();

        $task = [
            'id' => $taskId,
            'type' => $taskType,
            'data' => $data,
            'callback_url' => $callbackUrl,
            'submitted_at' => time(),
            'status' => 'pending'
        ];

        // Add task to queue
        $this->redis->lPush('ml_tasks', json_encode($task));

        // Store task metadata for status checking
        $this->redis->setex(
            "task:{$taskId}",
            3600,  // 1 hour TTL
            json_encode($task)
        );

        return $taskId;
    }

    /**
     * Check status of submitted task.
     */
    public function getTaskStatus(string $taskId): ?array
    {
        $data = $this->redis->get("task:{$taskId}");
        return $data ? json_decode($data, true) : null;
    }

    /**
     * Get result of completed task.
     */
    public function getTaskResult(string $taskId): ?array
    {
        $data = $this->redis->get("result:{$taskId}");
        return $data ? json_decode($data, true) : null;
    }

    /**
     * Worker: Process tasks from queue (this would run in Python).
     * 
     * This is PHP pseudocode showing the worker pattern.
     * Real implementation would be in Python worker process.
     */
    public function processTasksWorker(): void
    {
        echo "Worker started. Listening for tasks...\n";

        while (true) {
            // Blocking pop with 1 second timeout
            $taskData = $this->redis->brPop(['ml_tasks'], 1);

            if (!$taskData) {
                continue;  // No task, keep waiting
            }

            $task = json_decode($taskData[1], true);
            echo "Processing task {$task['id']} ({$task['type']})...\n";

            try {
                // Update status to processing
                $task['status'] = 'processing';
                $task['started_at'] = time();
                $this->redis->setex("task:{$task['id']}", 3600, json_encode($task));

                // Process task (call Python script, do ML work)
                $result = $this->executeMLTask($task);

                // Store result
                $this->redis->setex(
                    "result:{$task['id']}",
                    3600,
                    json_encode($result)
                );

                // Update task status
                $task['status'] = 'completed';
                $task['completed_at'] = time();
                $this->redis->setex("task:{$task['id']}", 3600, json_encode($task));

                // Callback if URL provided
                if ($task['callback_url']) {
                    $this->sendCallback($task['callback_url'], $result);
                }

                echo "✅ Task {$task['id']} completed\n";
            } catch (Exception $e) {
                echo "❌ Task {$task['id']} failed: {$e->getMessage()}\n";

                $task['status'] = 'failed';
                $task['error'] = $e->getMessage();
                $this->redis->setex("task:{$task['id']}", 3600, json_encode($task));
            }
        }
    }

    private function executeMLTask(array $task): array
    {
        // In reality, this would call Python script or API
        // For demo, simulate processing
        sleep(2);  // Simulate long ML task

        return [
            'task_id' => $task['id'],
            'result' => 'Task completed',
            'processed_at' => time()
        ];
    }

    private function sendCallback(string $url, array $result): void
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($result));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }

    private function generateTaskId(): string
    {
        return bin2hex(random_bytes(16));
    }
}

// Example usage
try {
    echo "=== Async ML Queue Pattern ===\n\n";

    $queue = new AsyncMLQueue();

    // Submit tasks
    echo "Submitting tasks...\n";

    $taskId1 = $queue->submitTask('sentiment_analysis', [
        'text' => 'Analyze this review for sentiment'
    ]);
    echo "Task 1 submitted: {$taskId1}\n";

    $taskId2 = $queue->submitTask('image_classification', [
        'image_url' => 'https://example.com/image.jpg'
    ]);
    echo "Task 2 submitted: {$taskId2}\n\n";

    // Check status
    echo "Checking task status...\n";
    $status1 = $queue->getTaskStatus($taskId1);
    echo "Task {$taskId1}: {$status1['status']}\n";
    echo "Type: {$status1['type']}\n";
    echo "Submitted: " . date('Y-m-d H:i:s', $status1['submitted_at']) . "\n\n";

    echo "✅ Tasks queued for async processing\n\n";

    echo "In production:\n";
    echo "  1. Python workers continuously poll queue\n";
    echo "  2. Workers process tasks and store results\n";
    echo "  3. PHP checks results by task ID or receives callback\n";
    echo "  4. Scale by adding more workers\n";
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";

    if (!extension_loaded('redis')) {
        echo "\nRedis extension not installed. This is normal for demo.\n";
        echo "For production use, install Redis:\n";
        echo "  brew install redis  # macOS\n";
        echo "  apt install redis-server  # Ubuntu\n";
        echo "  pecl install redis  # PHP extension\n";
    }
}


