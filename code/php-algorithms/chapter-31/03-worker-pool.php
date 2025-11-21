<?php
/**
 * Worker Pool Pattern
 *
 * Implements a worker pool for parallel task processing. Workers execute tasks
 * concurrently, distributing load across multiple processes or threads.
 *
 * This is a simulation that works without external extensions but demonstrates
 * the pattern. For production, use ext-parallel or Swoole.
 *
 * @category  Algorithms
 * @package   ConcurrentAlgorithms
 * @author    PHP Algorithms Series
 * @license   MIT
 */

declare(strict_types=1);

namespace CodeWithPhp\Algorithms\Chapter31;

/**
 * Task interface for worker pool
 */
interface Task
{
    /**
     * Execute the task
     *
     * @return mixed Task result
     */
    public function execute(): mixed;

    /**
     * Get task ID
     *
     * @return string Task identifier
     */
    public function getId(): string;
}

/**
 * Simple task implementation
 */
class SimpleTask implements Task
{
    private string $id;
    private mixed $data;
    private \Closure $handler;

    public function __construct(string $id, mixed $data, \Closure $handler)
    {
        $this->id = $id;
        $this->data = $data;
        $this->handler = $handler;
    }

    public function execute(): mixed
    {
        return ($this->handler)($this->data);
    }

    public function getId(): string
    {
        return $this->id;
    }
}

/**
 * Worker pool for concurrent task execution
 *
 * Note: This is a simulated implementation for demonstration.
 * For real parallel execution, use ext-parallel or Swoole.
 */
class WorkerPool
{
    private array $tasks = [];
    private array $results = [];
    private int $workerCount;
    private bool $acceptingWork = true;

    public function __construct(int $workerCount = 4)
    {
        $this->workerCount = $workerCount;
    }

    /**
     * Submit a task to the pool
     *
     * @param Task $task Task to execute
     * @throws \RuntimeException If pool is not accepting work
     */
    public function submit(Task $task): void
    {
        if (!$this->acceptingWork) {
            throw new \RuntimeException("Worker pool is not accepting new tasks");
        }

        $this->tasks[] = $task;
    }

    /**
     * Execute all submitted tasks
     *
     * @return array<string, array> Results indexed by task ID
     */
    public function execute(): array
    {
        $this->results = [];
        $taskChunks = array_chunk($this->tasks, (int)ceil(count($this->tasks) / $this->workerCount));

        echo "Processing " . count($this->tasks) . " tasks across {$this->workerCount} workers...\n";

        // Simulate parallel execution (in reality, these would run in separate processes)
        foreach ($taskChunks as $workerIndex => $workerTasks) {
            echo "  Worker {$workerIndex}: " . count($workerTasks) . " tasks\n";

            foreach ($workerTasks as $task) {
                try {
                    $start = microtime(true);
                    $result = $task->execute();
                    $duration = microtime(true) - $start;

                    $this->results[$task->getId()] = [
                        'success' => true,
                        'result' => $result,
                        'worker' => $workerIndex,
                        'duration' => $duration
                    ];
                } catch (\Throwable $e) {
                    $this->results[$task->getId()] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'worker' => $workerIndex
                    ];
                }
            }
        }

        return $this->results;
    }

    /**
     * Stop accepting new work
     */
    public function stopAcceptingWork(): void
    {
        $this->acceptingWork = false;
    }

    /**
     * Check if pool has active work
     *
     * @return bool True if tasks are pending
     */
    public function hasActiveWork(): bool
    {
        return count($this->tasks) > count($this->results);
    }

    /**
     * Clean up resources
     */
    public function cleanup(): void
    {
        $this->tasks = [];
        $this->results = [];
        $this->acceptingWork = false;
    }
}

/**
 * Advanced worker pool with priority queues
 */
class PriorityWorkerPool extends WorkerPool
{
    private \SplPriorityQueue $taskQueue;

    public function __construct(int $workerCount = 4)
    {
        parent::__construct($workerCount);
        $this->taskQueue = new \SplPriorityQueue();
    }

    /**
     * Submit task with priority
     *
     * @param Task $task Task to execute
     * @param int $priority Task priority (higher = more important)
     */
    public function submitWithPriority(Task $task, int $priority): void
    {
        $this->taskQueue->insert($task, $priority);
    }

    /**
     * Execute tasks in priority order
     *
     * @return array Results
     */
    public function execute(): array
    {
        // Convert priority queue to array for processing
        $tasks = [];
        $queueClone = clone $this->taskQueue;

        while (!$queueClone->isEmpty()) {
            $tasks[] = $queueClone->extract();
        }

        // Set tasks for parent execution
        $this->tasks = $tasks;

        return parent::execute();
    }
}

/**
 * Parallel data processor using worker pool
 */
class ParallelDataProcessor
{
    private WorkerPool $pool;

    public function __construct(int $workers = 4)
    {
        $this->pool = new WorkerPool($workers);
    }

    /**
     * Process array of items in parallel
     *
     * @param array $items Items to process
     * @param callable $processor Processing function
     * @return array Processed results
     */
    public function map(array $items, callable $processor): array
    {
        foreach ($items as $index => $item) {
            $task = new SimpleTask(
                "item_{$index}",
                $item,
                $processor
            );
            $this->pool->submit($task);
        }

        $results = $this->pool->execute();

        // Extract just the values
        return array_map(fn($r) => $r['success'] ? $r['result'] : null, $results);
    }

    /**
     * Filter array in parallel
     *
     * @param array $items Items to filter
     * @param callable $predicate Filter function
     * @return array Filtered items
     */
    public function filter(array $items, callable $predicate): array
    {
        foreach ($items as $index => $item) {
            $task = new SimpleTask(
                "filter_{$index}",
                $item,
                fn($data) => ['item' => $data, 'keep' => $predicate($data)]
            );
            $this->pool->submit($task);
        }

        $results = $this->pool->execute();

        // Filter and extract items
        $filtered = [];
        foreach ($results as $result) {
            if ($result['success'] && $result['result']['keep']) {
                $filtered[] = $result['result']['item'];
            }
        }

        return $filtered;
    }

    /**
     * Reduce array in parallel (parallel reduce-merge)
     *
     * @param array $items Items to reduce
     * @param callable $reducer Reduction function
     * @param mixed $initial Initial value
     * @return mixed Final reduced value
     */
    public function reduce(array $items, callable $reducer, mixed $initial = null): mixed
    {
        $workerCount = 4;
        $chunks = array_chunk($items, (int)ceil(count($items) / $workerCount));

        // Parallel reduce on chunks
        foreach ($chunks as $index => $chunk) {
            $task = new SimpleTask(
                "reduce_{$index}",
                $chunk,
                fn($data) => array_reduce($data, $reducer, $initial)
            );
            $this->pool->submit($task);
        }

        $results = $this->pool->execute();

        // Sequential reduce on partial results
        $partialResults = array_map(fn($r) => $r['result'], $results);
        return array_reduce($partialResults, $reducer, $initial);
    }
}

/**
 * Example usage demonstrating worker pool patterns
 */
function demonstrateWorkerPool(): void
{
    echo "=== Worker Pool Pattern Demo ===\n\n";

    // Test 1: Basic worker pool
    echo "1. Basic Worker Pool\n";
    $pool = new WorkerPool(4);

    // Submit tasks
    for ($i = 1; $i <= 10; $i++) {
        $task = new SimpleTask(
            "task_{$i}",
            $i,
            function ($n) {
                usleep(mt_rand(10000, 50000)); // Simulate work
                return $n * $n;
            }
        );
        $pool->submit($task);
    }

    $start = microtime(true);
    $results = $pool->execute();
    $duration = microtime(true) - $start;

    echo "   Completed in " . number_format($duration, 3) . " seconds\n";
    echo "   Results: ";
    foreach ($results as $taskId => $result) {
        if ($result['success']) {
            echo "{$result['result']} ";
        }
    }
    echo "\n";

    // Test 2: Priority worker pool
    echo "\n2. Priority Worker Pool\n";
    $priorityPool = new PriorityWorkerPool(3);

    // Submit tasks with different priorities
    for ($i = 1; $i <= 10; $i++) {
        $priority = $i <= 3 ? 10 : ($i <= 6 ? 5 : 1); // High, medium, low priority
        $task = new SimpleTask(
            "priority_task_{$i}",
            $i,
            fn($n) => "Task {$n} (priority {$priority})"
        );
        $priorityPool->submitWithPriority($task, $priority);
    }

    $results = $priorityPool->execute();
    echo "   Execution order (by priority):\n";
    foreach ($results as $taskId => $result) {
        if ($result['success']) {
            echo "   - {$result['result']} (worker {$result['worker']})\n";
        }
    }

    // Test 3: Parallel data processor
    echo "\n3. Parallel Data Processor\n";
    $processor = new ParallelDataProcessor(4);

    // Map operation
    $numbers = range(1, 20);
    $squared = $processor->map($numbers, fn($n) => $n * $n);
    echo "   Squared (first 10): " . implode(', ', array_slice($squared, 0, 10)) . "\n";

    // Filter operation
    $evenNumbers = $processor->filter($numbers, fn($n) => $n % 2 === 0);
    echo "   Even numbers: " . implode(', ', $evenNumbers) . "\n";

    // Reduce operation
    $sum = $processor->reduce($numbers, fn($carry, $n) => $carry + $n, 0);
    echo "   Sum of 1-20: {$sum}\n";

    // Test 4: Image processing simulation
    echo "\n4. Batch Image Processing (Simulated)\n";
    $imagePool = new WorkerPool(4);

    $images = [
        'photo1.jpg', 'photo2.jpg', 'photo3.jpg', 'photo4.jpg',
        'photo5.jpg', 'photo6.jpg', 'photo7.jpg', 'photo8.jpg'
    ];

    foreach ($images as $image) {
        $task = new SimpleTask(
            $image,
            $image,
            function ($filename) {
                // Simulate image processing
                usleep(mt_rand(50000, 150000));
                return [
                    'input' => $filename,
                    'output' => str_replace('.jpg', '_processed.jpg', $filename),
                    'operations' => ['resize', 'compress', 'watermark']
                ];
            }
        );
        $imagePool->submit($task);
    }

    $start = microtime(true);
    $results = $imagePool->execute();
    $duration = microtime(true) - $start;

    echo "   Processed " . count($images) . " images in " . number_format($duration, 3) . " seconds\n";
    $avgTime = $duration / count($images);
    echo "   Average time per image: " . number_format($avgTime, 3) . "s\n";

    $successCount = array_reduce($results, fn($c, $r) => $c + ($r['success'] ? 1 : 0), 0);
    echo "   Success rate: {$successCount}/" . count($images) . "\n";
}

// Run if executed directly
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    try {
        demonstrateWorkerPool();
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";
        exit(1);
    }
}
