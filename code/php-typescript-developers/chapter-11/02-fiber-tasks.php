<?php
declare(strict_types=1);

/**
 * Fiber Task Manager
 *
 * Practical example of using Fibers to manage async-like tasks.
 */

class AsyncTask {
    private Fiber $fiber;
    private string $name;

    public function __construct(string $name, callable $task) {
        $this->name = $name;
        $this->fiber = new Fiber($task);
    }

    public function run(): mixed {
        if (!$this->fiber->isStarted()) {
            echo "[{$this->name}] Starting...\n";
            return $this->fiber->start();
        }

        if (!$this->fiber->isTerminated()) {
            echo "[{$this->name}] Resuming...\n";
            return $this->fiber->resume();
        }

        return null;
    }

    public function isFinished(): bool {
        return $this->fiber->isTerminated();
    }

    public function getName(): string {
        return $this->name;
    }
}

class TaskScheduler {
    /** @var AsyncTask[] */
    private array $tasks = [];

    public function addTask(AsyncTask $task): void {
        $this->tasks[] = $task;
    }

    public function runAll(): void {
        while (!empty($this->tasks)) {
            foreach ($this->tasks as $index => $task) {
                $task->run();

                if ($task->isFinished()) {
                    echo "[{$task->getName()}] Completed!\n";
                    unset($this->tasks[$index]);
                }
            }
            $this->tasks = array_values($this->tasks); // Re-index
        }
    }
}

// Example usage
echo "=== Task Scheduler with Fibers ===\n\n";

$scheduler = new TaskScheduler();

// Task 1: Database query simulation
$scheduler->addTask(new AsyncTask('DB Query', function() {
    echo "  - Executing SELECT query\n";
    Fiber::suspend();
    echo "  - Processing results\n";
    Fiber::suspend();
    echo "  - Returning data\n";
    return ['id' => 1, 'name' => 'Alice'];
}));

// Task 2: API call simulation
$scheduler->addTask(new AsyncTask('API Call', function() {
    echo "  - Sending HTTP request\n";
    Fiber::suspend();
    echo "  - Receiving response\n";
    Fiber::suspend();
    echo "  - Parsing JSON\n";
    return ['status' => 'success'];
}));

// Task 3: File operation simulation
$scheduler->addTask(new AsyncTask('File I/O', function() {
    echo "  - Opening file\n";
    Fiber::suspend();
    echo "  - Reading content\n";
    Fiber::suspend();
    echo "  - Closing file\n";
    return 'file content';
}));

$scheduler->runAll();

echo "\n✅ All tasks completed!\n";
