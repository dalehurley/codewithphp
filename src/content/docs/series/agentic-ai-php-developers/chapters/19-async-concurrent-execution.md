---
title: "19: Async & Concurrent Execution"
description: "Leverage AMPHP for parallel tool execution, batch processing, and promise-based workflows with claude-php-agent."
series: "agentic-ai-php-developers"
chapter: 19
difficulty: "Advanced"
keywords: ["PHP Async", "AMPHP", "Concurrent Execution", "Parallel Processing", "Batch Processing", "Promises", "Async Workflows"]
teaches: ["AMPHP-powered concurrent execution", "Batch processing with concurrency control", "Parallel tool execution", "Promise-based async workflows", "Agent racing and collaboration", "Async multi-agent coordination", "Performance optimization through parallelism"]
estimatedTime: "PT120M"
---

# Chapter 19: Async & Concurrent Execution

## Overview

Your agents are fast. Now make them **concurrent**. **Async & concurrent execution** — the practice of running multiple agent tasks simultaneously rather than sequentially — transforms throughput and enables parallel workflows that would be impossible with synchronous code. Without concurrency, agents process one task at a time. With AMPHP-powered parallelism, they process dozens simultaneously.

In this chapter, you'll learn to leverage [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent)'s AMPHP integration for true concurrent execution. You'll implement batch processing with concurrency limits, execute tools in parallel, build promise-based workflows, race agents for speed, orchestrate multi-agent collaboration, and design production async systems that maximize throughput while maintaining reliability.

In this chapter you'll:

- Implement **batch processing** with AMPHP for concurrent agent task execution
- Execute **tools in parallel** to eliminate sequential bottlenecks
- Build **promise-based workflows** with async/await patterns
- Create **agent racing** systems where first-to-complete wins
- Orchestrate **async multi-agent collaboration** with parallel execution
- Design **production async architectures** with proper error handling and monitoring
- Optimize **throughput** through intelligent concurrency tuning

**Estimated time:** ~120 minutes

::: info Framework Version
This chapter is based on [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent) **v0.5+**. All async features are powered by [AMPHP](https://amphp.org/).
:::

::: info Code examples
Complete, runnable examples for this chapter:

- [`01-basic-batch-processing.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/19-async-concurrent-execution/01-basic-batch-processing.php) — Concurrent batch processing fundamentals
- [`02-parallel-tool-execution.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/19-async-concurrent-execution/02-parallel-tool-execution.php) — Execute tools in parallel
- [`03-promise-workflows.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/19-async-concurrent-execution/03-promise-workflows.php) — Promise-based async patterns
- [`04-agent-racing.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/19-async-concurrent-execution/04-agent-racing.php) — Race agents for fastest response
- [`05-async-multi-agent.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/19-async-concurrent-execution/05-async-multi-agent.php) — Parallel multi-agent coordination
- [`06-concurrency-tuning.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/19-async-concurrent-execution/06-concurrency-tuning.php) — Optimize concurrency levels
- [`07-production-async-system.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/19-async-concurrent-execution/07-production-async-system.php) — Complete async architecture

All files are in [`code/19-async-concurrent-execution/`](https://github.com/dalehurley/codewithphp/tree/main/code/agentic-ai-php-developers/19-async-concurrent-execution).
:::

---

## The Cost of Sequential Execution

Without concurrency, agents process tasks one at a time:

```text
┌──────────────────────────────────────────────────────────┐
│         SEQUENTIAL VS CONCURRENT EXECUTION               │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  SEQUENTIAL (5 tasks × 4 seconds each):                  │
│  ─────────────────────────────────────────────────────   │
│  [Task 1]────[Task 2]────[Task 3]────[Task 4]────[Task 5]│
│  [========]  [========]  [========]  [========]  [======]│
│                                                          │
│  Total time: 20 seconds                                  │
│  Throughput: 0.25 tasks/second                           │
│                                                          │
│  CONCURRENT (5 tasks with concurrency: 5):               │
│  ─────────────────────────────────────────────────────   │
│  [Task 1]────                                            │
│  [Task 2]────                                            │
│  [Task 3]────                                            │
│  [Task 4]────                                            │
│  [Task 5]────                                            │
│  [========]                                              │
│                                                          │
│  Total time: 4 seconds (5x faster!)                      │
│  Throughput: 1.25 tasks/second                           │
│                                                          │
│  SPEEDUP: 80% time reduction                             │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

### The Concurrency Advantage

**Key Principle:** If tasks are independent, execute them concurrently. Don't waste time waiting.

Concurrency benefits:

1. **Throughput** — Process multiple tasks simultaneously
2. **Latency** — Faster end-to-end completion
3. **Resource Utilization** — Use API rate limits effectively
4. **Scalability** — Handle high-volume workloads
5. **User Experience** — Faster response times

---

## Understanding AMPHP in PHP

PHP doesn't have native async/await like JavaScript or Python. [`claude-php-agent`](https://github.com/claude-php/claude-php-agent) uses **AMPHP** to provide true concurrency:

### AMPHP Fundamentals

```text
┌──────────────────────────────────────────────────────────┐
│              AMPHP CONCURRENCY MODEL                     │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  1. Create async tasks with async()                      │
│     ↓                                                    │
│  2. Each task returns a Future                           │
│     ↓                                                    │
│  3. Tasks execute concurrently in event loop             │
│     ↓                                                    │
│  4. Call await() to get results                          │
│                                                          │
│  Key Concepts:                                           │
│  ───────────────                                         │
│  • async() - Create async task                           │
│  • Future - Placeholder for result                       │
│  • await() - Block until result ready                    │
│  • DeferredFuture - Manual promise control               │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

### PHP Async Adaptation

The framework adapts async patterns to PHP:

| Python/JS Pattern | PHP (AMPHP) Equivalent |
|-------------------|------------------------|
| `async/await` | `async() / await()` |
| `Promise` | `Future` / `DeferredFuture` |
| `asyncio.gather()` | `awaitAll()` |
| `Promise.race()` | `awaitFirst()` |

---

## Strategy 1: Batch Processing

### Why Batch Process?

Process multiple agent tasks concurrently instead of sequentially:

```php
use ClaudeAgents\Async\BatchProcessor;

// Create agent
$agent = Agent::create($client)
    ->withSystemPrompt('You are a helpful assistant.');

// Create batch processor
$processor = BatchProcessor::create($agent);

// Add tasks
$processor
    ->add('task1', 'What is the capital of France?')
    ->add('task2', 'What is 25 + 17?')
    ->add('task3', 'Name three colors.')
    ->add('task4', 'What is the largest planet?');

// Process concurrently (3 at a time)
$results = $processor->run(concurrency: 3);

// Check results
foreach ($results as $id => $result) {
    if ($result->isSuccess()) {
        echo "[{$id}] {$result->getAnswer()}\n";
    }
}
```

### Batch Processing Architecture

```text
┌──────────────────────────────────────────────────────────┐
│           BATCH PROCESSOR ARCHITECTURE                   │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  USER CODE                                               │
│  ┌──────────────────────────────────────┐               │
│  │ Add tasks to BatchProcessor          │               │
│  │ processor->run(concurrency: 3)       │               │
│  └──────────────────────────────────────┘               │
│           ↓                                              │
│  BATCH PROCESSOR                                         │
│  ┌──────────────────────────────────────┐               │
│  │ Split into batches of size N         │               │
│  │ For each batch:                      │               │
│  │   • Create async() futures           │               │
│  │   • Execute concurrently             │               │
│  │   • Collect results                  │               │
│  └──────────────────────────────────────┘               │
│           ↓                                              │
│  AMPHP EVENT LOOP                                        │
│  ┌──────────────────────────────────────┐               │
│  │ Task 1  Task 2  Task 3               │               │
│  │ [====]  [====]  [====]               │               │
│  │         ↓       ↓       ↓            │               │
│  │      Results collected                │               │
│  └──────────────────────────────────────┘               │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

### Batch Processing Patterns

**Pattern 1: Bulk Data Analysis**

```php
// Analyze multiple datasets in parallel
$datasets = ['users', 'orders', 'products', 'revenue'];

$processor = BatchProcessor::create($analysisAgent);

foreach ($datasets as $dataset) {
    $processor->add(
        $dataset,
        "Analyze {$dataset} dataset and provide key insights"
    );
}

$results = $processor->run(concurrency: 4);
```

**Pattern 2: Multi-Document Processing**

```php
// Process documents concurrently
$documents = glob('./documents/*.txt');

$processor = BatchProcessor::create($summaryAgent);

foreach ($documents as $doc) {
    $content = file_get_contents($doc);
    $processor->add(
        basename($doc),
        "Summarize this document:\n\n{$content}"
    );
}

$results = $processor->run(concurrency: 5);
```

**Pattern 3: Report Generation**

```php
// Generate multiple reports in parallel
$reports = [
    'executive' => 'Create executive summary for Q4',
    'financial' => 'Analyze Q4 financial performance',
    'operations' => 'Summarize Q4 operational metrics',
    'marketing' => 'Report on Q4 marketing campaigns',
];

$processor->addMany($reports);
$results = $processor->run(concurrency: 4);
```

### Batch Statistics

Track batch performance:

```php
$results = $processor->run(concurrency: 3);

$stats = $processor->getStats();

echo "Total tasks: {$stats['total_tasks']}\n";
echo "Successful: {$stats['successful']}\n";
echo "Failed: {$stats['failed']}\n";
echo "Success rate: " . ($stats['success_rate'] * 100) . "%\n";
echo "Total tokens: {$stats['total_tokens']['total']}\n";

// Get successful results only
$successful = $processor->getSuccessful();

// Get failed results only
$failed = $processor->getFailed();
```

---

## Strategy 2: Parallel Tool Execution

### Why Execute Tools in Parallel?

When agents need multiple independent tool results, execute them concurrently:

```text
┌──────────────────────────────────────────────────────────┐
│        SEQUENTIAL VS PARALLEL TOOL EXECUTION             │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  SEQUENTIAL (3 tools × 500ms each):                      │
│  [get_weather]─[get_time]─[calculate]                    │
│  [=========]   [========] [========]                     │
│  Total: 1500ms                                           │
│                                                          │
│  PARALLEL (3 tools executed concurrently):               │
│  [get_weather]─                                          │
│  [get_time]────                                          │
│  [calculate]───                                          │
│  [=========]                                             │
│  Total: 500ms (3x faster!)                               │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

### Using ParallelToolExecutor

```php
use ClaudeAgents\Async\ParallelToolExecutor;

// Define tools
$weatherTool = Tool::create('get_weather')
    ->description('Get weather for a city')
    ->parameter('city', 'string', 'City name')
    ->required('city')
    ->handler(function (array $input): ToolResult {
        // Simulate API call
        usleep(500000); // 500ms
        return ToolResult::success([
            'city' => $input['city'],
            'temperature' => rand(15, 30),
            'condition' => 'sunny',
        ]);
    });

$timeTool = Tool::create('get_time')
    ->description('Get current time')
    ->parameter('timezone', 'string', 'Timezone')
    ->required('timezone')
    ->handler(function (array $input): ToolResult {
        usleep(300000); // 300ms
        return ToolResult::success([
            'timezone' => $input['timezone'],
            'time' => date('H:i:s'),
        ]);
    });

// Execute tools in parallel
$executor = new ParallelToolExecutor([$weatherTool, $timeTool]);

$results = $executor->execute([
    ['tool' => 'get_weather', 'input' => ['city' => 'London']],
    ['tool' => 'get_weather', 'input' => ['city' => 'Paris']],
    ['tool' => 'get_time', 'input' => ['timezone' => 'UTC']],
]);

// Results returned concurrently (not sequentially)
```

### Batched Tool Execution

Control concurrency with batching:

```php
// Many tool calls
$toolCalls = [];
for ($i = 1; $i <= 20; $i++) {
    $toolCalls[] = [
        'tool' => 'calculate',
        'input' => ['expression' => "{$i} * 2"],
    ];
}

// Execute with concurrency limit of 5
$results = $executor->executeBatched($toolCalls, concurrency: 5);

// Processes in batches: [1-5], [6-10], [11-15], [16-20]
```

---

## Strategy 3: Promise-Based Workflows

### Why Use Promises?

Promises enable async patterns with callbacks and composition:

```php
use ClaudeAgents\Async\Promise;

// Execute async and get promise
$promises = $processor->runAsync();

// Add callbacks
$promises['task1']->then(function ($result) {
    echo "Task 1 complete: {$result->getAnswer()}\n";
})->catch(function ($error) {
    echo "Task 1 failed: {$error->getMessage()}\n";
});

// Wait for all
$results = Promise::all($promises);
```

### Promise Patterns

**Pattern 1: Sequential Chaining**

```php
$promise1 = Promise::resolved('Step 1 complete');

$promise1
    ->then(fn($result) => performStep2($result))
    ->then(fn($result) => performStep3($result))
    ->then(fn($result) => echo "All steps complete: {$result}\n")
    ->catch(fn($error) => echo "Chain failed: {$error}\n");
```

**Pattern 2: Parallel Execution**

```php
// Create promises
$promises = [
    'task1' => $processor1->runAsync(),
    'task2' => $processor2->runAsync(),
    'task3' => $processor3->runAsync(),
];

// Wait for all
try {
    $results = Promise::all($promises);
    echo "All tasks complete\n";
} catch (\Throwable $e) {
    echo "At least one task failed: {$e->getMessage()}\n";
}
```

**Pattern 3: First to Complete (Racing)**

```php
// Race multiple promises
$promises = [
    $agent1->runAsync('Quick question'),
    $agent2->runAsync('Quick question'),
    $agent3->runAsync('Quick question'),
];

// First to complete wins
$winner = Promise::race($promises);
echo "First response: {$winner}\n";
```

**Pattern 4: Settled (All Complete, Regardless of Success)**

```php
// Wait for all to settle (no exception on failure)
$results = Promise::allSettled($promises);

foreach ($results as $result) {
    if ($result instanceof \Throwable) {
        echo "Failed: {$result->getMessage()}\n";
    } else {
        echo "Success: {$result}\n";
    }
}
```

### Promise Composition

Build complex workflows:

```php
class AsyncWorkflow
{
    public function execute(): Promise
    {
        $promise = new Promise();
        
        async(function () use ($promise) {
            try {
                // Step 1: Fetch data
                $data = $this->fetchData()->wait();
                
                // Step 2: Process in parallel
                $processed = Promise::all([
                    $this->processA($data),
                    $this->processB($data),
                    $this->processC($data),
                ]);
                
                // Step 3: Combine results
                $final = $this->combine($processed);
                
                $promise->resolve($final);
            } catch (\Throwable $e) {
                $promise->reject($e);
            }
        });
        
        return $promise;
    }
}
```

---

## Strategy 4: Agent Racing

### Why Race Agents?

Sometimes you want the fastest response, not all responses:

```text
┌──────────────────────────────────────────────────────────┐
│                 AGENT RACING PATTERN                     │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  START                                                   │
│  ├─ Agent 1 (Haiku)   ─────────► Response (2.1s) ✓      │
│  ├─ Agent 2 (Sonnet)  ──────────────► (4.5s) ✗ [cancel]│
│  └─ Agent 3 (Haiku)   ───────────► Response (3.2s) ✗    │
│                                                          │
│  Winner: Agent 1 (fastest)                               │
│  Result: Use first response, cancel others               │
│                                                          │
│  Use cases:                                              │
│  • Speed-critical responses (chatbots, live demos)       │
│  • Redundancy (multiple providers)                       │
│  • Cost optimization (fastest model wins)                │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

### Using AsyncCollaborationManager for Racing

```php
use ClaudeAgents\MultiAgent\AsyncCollaborationManager;
use ClaudeAgents\MultiAgent\SimpleCollaborativeAgent;

$manager = new AsyncCollaborationManager($client, [
    'max_concurrent' => 3,
]);

// Register multiple agents
$manager->registerAgent('agent1', $haikuAgent1);
$manager->registerAgent('agent2', $haikuAgent2);
$manager->registerAgent('agent3', $sonnetAgent);

// Race them
$winner = $manager->race([
    'agent1' => 'Quick: What is PHP?',
    'agent2' => 'Quick: What is PHP?',
    'agent3' => 'Quick: What is PHP?',
]);

echo "Winner: {$winner['agent_id']}\n";
echo "Response: {$winner['result']->getAnswer()}\n";
```

### Racing Use Cases

**1. Redundant Providers**

```php
// Try multiple API providers, use first to respond
$winner = $manager->race([
    'anthropic_agent' => $query,
    'openai_agent' => $query,
    'cohere_agent' => $query,
]);
```

**2. Model Speed Testing**

```php
// Compare model speeds
$winner = $manager->race([
    'haiku' => $task,
    'sonnet' => $task,
]);

echo "Fastest model: {$winner['agent_id']}\n";
```

**3. Speed-Critical Responses**

```php
// For live demos or real-time chat
$winner = $manager->race([
    'fast_agent_1' => $userQuery,
    'fast_agent_2' => $userQuery,
]);
```

---

## Strategy 5: Async Multi-Agent Collaboration

### Why Async Multi-Agent?

Coordinate multiple agents in parallel for complex workflows:

```text
┌──────────────────────────────────────────────────────────┐
│         ASYNC MULTI-AGENT COLLABORATION                  │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  TASK: "Research, analyze, and write report on PHP 8.4" │
│                                                          │
│  SEQUENTIAL (8-12 seconds):                              │
│  [Researcher]──► [Analyst]──► [Writer]──►                │
│                                                          │
│  PARALLEL (3-5 seconds):                                 │
│  [Researcher] ──► ┐                                      │
│  [Analyst] ─────► ├─► [Synthesizer] ──► Final Report    │
│  [Writer] ──────► ┘                                      │
│                                                          │
│  Speedup: 60-70% faster with parallelism                 │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

### Parallel Multi-Agent Execution

```php
$manager = new AsyncCollaborationManager($client, [
    'max_concurrent' => 3,
]);

// Register specialized agents
$manager->registerAgent('researcher', $researchAgent, ['research']);
$manager->registerAgent('analyst', $analysisAgent, ['analysis']);
$manager->registerAgent('writer', $writerAgent, ['writing']);

// Execute tasks in parallel
$results = $manager->executeParallel([
    'researcher' => 'Research PHP 8.4 features',
    'analyst' => 'Analyze performance improvements',
    'writer' => 'Write summary of PHP evolution',
]);

// All three agents worked simultaneously
foreach ($results as $agentId => $result) {
    echo "{$agentId}: {$result->getAnswer()}\n";
}
```

### Parallel Collaboration Pattern

```php
// Automatic task decomposition and synthesis
$result = $manager->collaborateParallel(
    task: 'Create comprehensive analysis of PHP 8.4 features, performance, and ecosystem impact',
    parallelAgents: 3
);

// Manager automatically:
// 1. Decomposes task into 3 subtasks
// 2. Assigns to 3 agents in parallel
// 3. Synthesizes results into final answer

if ($result->isSuccess()) {
    echo $result->getAnswer();
    echo "Subtasks completed: {$result->getMetadata()['subtasks_completed']}\n";
}
```

### Batched Multi-Agent Execution

```php
// Process many tasks with batching
$tasks = [];
for ($i = 1; $i <= 20; $i++) {
    $tasks["task_{$i}"] = "Process item {$i}";
}

// Executes in batches (respects max_concurrent setting)
$results = $manager->executeBatched($tasks);

echo "Completed " . count($results) . " tasks\n";
$successRate = count(array_filter($results, fn($r) => $r->isSuccess())) / count($results);
echo "Success rate: " . ($successRate * 100) . "%\n";
```

---

## Strategy 6: Concurrency Tuning

### Choosing the Right Concurrency Level

Not all workloads benefit from maximum concurrency:

```text
┌──────────────────────────────────────────────────────────┐
│           CONCURRENCY LEVEL GUIDELINES                   │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  Concurrency   Use Case                 Trade-offs       │
│  ──────────────────────────────────────────────────────  │
│  1             Rate-limited APIs        Slowest          │
│                Sequential tasks         Safest           │
│                                                          │
│  3-5           Standard workloads       Balanced         │
│                Production default       Good speed       │
│                                         Controlled cost  │
│                                                          │
│  10+           Bulk processing          Fastest          │
│                High-volume tasks        Highest cost     │
│                Fast APIs                Risk of limits   │
│                                                          │
│  Adaptive      Variable workloads       Optimal          │
│                Complex systems          Most complex     │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

### Adaptive Concurrency

Adjust concurrency based on performance:

```php
class AdaptiveBatchProcessor
{
    private int $concurrency = 3;
    private array $latencies = [];
    
    public function run(BatchProcessor $processor): array
    {
        $startTime = microtime(true);
        $results = $processor->run(concurrency: $this->concurrency);
        $duration = microtime(true) - $startTime;
        
        // Track performance
        $this->latencies[] = $duration;
        
        // Adjust concurrency
        $this->adjustConcurrency($results, $duration);
        
        return $results;
    }
    
    private function adjustConcurrency(array $results, float $duration): void
    {
        $successRate = count(array_filter($results, fn($r) => $r->isSuccess())) / count($results);
        
        // Increase if performing well
        if ($successRate > 0.95 && $duration < 10.0) {
            $this->concurrency = min($this->concurrency + 1, 10);
            echo "Increasing concurrency to {$this->concurrency}\n";
        }
        
        // Decrease if errors or slow
        if ($successRate < 0.8 || $duration > 30.0) {
            $this->concurrency = max($this->concurrency - 1, 1);
            echo "Decreasing concurrency to {$this->concurrency}\n";
        }
    }
}
```

### Cost-Aware Concurrency

Balance speed vs cost:

```php
class CostAwareConcurrency
{
    public function determineConcurrency(
        int $taskCount,
        float $dailyBudget,
        float $currentSpend
    ): int {
        $remainingBudget = $dailyBudget - $currentSpend;
        $estimatedCostPerTask = 0.10; // $0.10 per task
        
        $affordableTasks = floor($remainingBudget / $estimatedCostPerTask);
        
        if ($affordableTasks < $taskCount) {
            // Reduce concurrency to stretch budget
            return max(1, (int) ceil($taskCount / 10));
        }
        
        // Can afford high concurrency
        return min(10, $taskCount);
    }
}
```

---

## Production Async System

### Comprehensive Async Architecture

Combine all strategies into production system:

```php
class ProductionAsyncSystem
{
    private BatchProcessor $processor;
    private AsyncCollaborationManager $manager;
    private int $maxConcurrency = 5;
    private array $metrics = [];
    
    public function __construct(
        private ClaudePhp $client,
        array $config = []
    ) {
        $this->maxConcurrency = $config['max_concurrency'] ?? 5;
        
        // Initialize async components
        $agent = Agent::create($client);
        $this->processor = BatchProcessor::create($agent);
        
        $this->manager = new AsyncCollaborationManager($client, [
            'max_concurrent' => $this->maxConcurrency,
        ]);
    }
    
    /**
     * Execute tasks with intelligent routing and monitoring
     */
    public function executeTasks(array $tasks, array $options = []): array
    {
        $startTime = microtime(true);
        
        // Determine strategy based on task count
        $strategy = $this->selectStrategy(count($tasks));
        
        try {
            switch ($strategy) {
                case 'batch':
                    $results = $this->executeBatch($tasks, $options);
                    break;
                    
                case 'race':
                    $results = $this->executeRace($tasks, $options);
                    break;
                    
                case 'parallel':
                    $results = $this->executeParallel($tasks, $options);
                    break;
                    
                default:
                    $results = $this->executeSequential($tasks);
            }
            
            // Record metrics
            $this->recordMetrics($strategy, count($tasks), microtime(true) - $startTime);
            
            return $results;
            
        } catch (\Throwable $e) {
            $this->handleError($e);
            throw $e;
        }
    }
    
    private function selectStrategy(int $taskCount): string
    {
        if ($taskCount === 1) {
            return 'sequential';
        }
        
        if ($taskCount <= 3) {
            return 'parallel';
        }
        
        if ($taskCount >= 10) {
            return 'batch';
        }
        
        return 'parallel';
    }
    
    private function executeBatch(array $tasks, array $options): array
    {
        $this->processor->addMany($tasks);
        $concurrency = $options['concurrency'] ?? $this->maxConcurrency;
        
        return $this->processor->run(concurrency: $concurrency);
    }
    
    private function executeParallel(array $tasks, array $options): array
    {
        return $this->manager->executeParallel($tasks);
    }
    
    private function executeRace(array $tasks, array $options): array
    {
        $winner = $this->manager->race($tasks);
        return [$winner['agent_id'] => $winner['result']];
    }
    
    private function executeSequential(array $tasks): array
    {
        $results = [];
        foreach ($tasks as $id => $task) {
            $agent = Agent::create($this->client);
            $results[$id] = $agent->run($task);
        }
        return $results;
    }
    
    private function recordMetrics(string $strategy, int $taskCount, float $duration): void
    {
        $this->metrics[] = [
            'strategy' => $strategy,
            'task_count' => $taskCount,
            'duration' => $duration,
            'throughput' => $taskCount / $duration,
            'timestamp' => time(),
        ];
    }
    
    public function getMetrics(): array
    {
        return $this->metrics;
    }
    
    private function handleError(\Throwable $e): void
    {
        error_log("Async system error: {$e->getMessage()}");
        // Send alert, log to monitoring system, etc.
    }
}
```

---

## Error Handling in Async Systems

### Handling Async Failures

Failures in concurrent systems need special handling:

```php
class AsyncErrorHandler
{
    public function executeWithRetry(
        BatchProcessor $processor,
        int $maxRetries = 3
    ): array {
        $attempt = 0;
        
        while ($attempt < $maxRetries) {
            $results = $processor->run(concurrency: 3);
            $failed = $processor->getFailed();
            
            if (empty($failed)) {
                return $results; // All succeeded
            }
            
            // Retry failed tasks
            $processor->reset();
            foreach ($failed as $id => $result) {
                $originalTask = $this->getOriginalTask($id);
                $processor->add($id, $originalTask);
            }
            
            $attempt++;
            usleep(1000000 * $attempt); // Exponential backoff
        }
        
        return $results;
    }
}
```

### Circuit Breaker Pattern

```php
class AsyncCircuitBreaker
{
    private int $failureCount = 0;
    private int $threshold = 5;
    private bool $open = false;
    
    public function execute(callable $operation): mixed
    {
        if ($this->open) {
            throw new \RuntimeException('Circuit breaker is OPEN');
        }
        
        try {
            $result = $operation();
            $this->failureCount = 0; // Reset on success
            return $result;
        } catch (\Throwable $e) {
            $this->failureCount++;
            
            if ($this->failureCount >= $this->threshold) {
                $this->open = true;
                echo "Circuit breaker OPENED after {$this->failureCount} failures\n";
            }
            
            throw $e;
        }
    }
}
```

---

## Async Performance Checklist

Before deploying async systems to production:

### ✅ Concurrency Configuration
- [ ] Appropriate concurrency level set (3-5 for standard workloads)
- [ ] Rate limits considered
- [ ] Cost implications understood
- [ ] Adaptive tuning implemented (optional)

### ✅ Error Handling
- [ ] Retry logic implemented for transient failures
- [ ] Circuit breakers for cascading failures
- [ ] Proper exception handling in async contexts
- [ ] Graceful degradation when services fail

### ✅ Monitoring
- [ ] Throughput metrics tracked
- [ ] Latency per task measured
- [ ] Success/failure rates monitored
- [ ] Concurrency levels logged
- [ ] Cost per execution tracked

### ✅ Resource Management
- [ ] Memory limits considered (concurrent tasks consume memory)
- [ ] Connection pools sized appropriately
- [ ] Timeouts set for long-running tasks
- [ ] Cleanup of failed tasks handled

### ✅ Testing
- [ ] Load testing with realistic concurrency
- [ ] Failure scenarios tested (timeout, errors, rate limits)
- [ ] Performance benchmarks established
- [ ] Cost projections validated

---

## Real-World Async Impact

**Case Study: Document Processing Pipeline**

```text
Before Async (Sequential):
├─ 1,000 documents
├─ 5 seconds per document
├─ Total time: 5,000 seconds (83 minutes)
└─ Throughput: 0.2 docs/second

After Async (Concurrency: 10):
├─ 1,000 documents
├─ 5 seconds per document
├─ Total time: 500 seconds (8.3 minutes)
└─ Throughput: 2.0 docs/second

Improvement:
✅ 90% time reduction (75 minutes saved)
✅ 10x throughput increase
✅ Same cost per document
✅ Faster user experience

Annual Impact:
- 100 batches/month × 75 minutes saved = 7,500 minutes
- 125 hours saved per month
- ~$18,750 in labor savings (assuming $150/hr developer time)
```

---

## Key Takeaways

1. **Batch Processing** — Use `BatchProcessor` for concurrent agent tasks with controlled concurrency
2. **Parallel Tools** — Execute independent tools simultaneously with `ParallelToolExecutor`
3. **Promises** — Build complex async workflows with promise composition
4. **Agent Racing** — Get fastest response by racing multiple agents
5. **Multi-Agent Async** — Coordinate agents in parallel for complex workflows
6. **Tuning** — Choose concurrency based on workload, not arbitrarily high values
7. **Error Handling** — Implement retries, circuit breakers, and graceful degradation

**Golden Rule:** If tasks are independent, execute them concurrently. Measure throughput, not just latency.

---

## Exercises

### Exercise 1: Batch Processing

Create a batch processor that analyzes 10 product reviews concurrently with proper error handling.

### Exercise 2: Parallel Tool Execution

Build a system that executes 5 different API calls in parallel and aggregates results.

### Exercise 3: Agent Racing

Implement a race between 3 agents (Haiku, Sonnet, and custom-tuned) to find the fastest responder.

### Exercise 4: Async Multi-Agent

Create a parallel research workflow where Researcher, Analyst, and Writer work simultaneously.

### Exercise 5: Adaptive Concurrency

Build an adaptive system that automatically adjusts concurrency based on error rates and latency.

---

## What's Next?

You've mastered async and concurrent execution. You can now build high-throughput agent systems that process tasks in parallel rather than sequentially.

In **[Chapter 20: Capstone Project](/series/agentic-ai-php-developers/chapters/20-capstone-project)**, you'll combine everything from the series into a complete production-ready agentic AI platform with tools, memory, RAG, planning, multi-agent coordination, observability, evaluation, optimization, and async execution.

**Up next:** Capstone: Build an Agentic AI Platform →
