# Chapter 19: Async & Concurrent Execution

This directory contains code examples demonstrating async and concurrent execution patterns using AMPHP with the [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent) framework.

## Examples

### 01. Basic Batch Processing
**File:** `01-basic-batch-processing.php`

Demonstrates concurrent batch processing of multiple agent tasks.

**Key Concepts:**
- Creating BatchProcessor instances
- Adding tasks to batches
- Executing with concurrency control
- Collecting and analyzing results
- Performance comparison (sequential vs concurrent)

**Run:**
```bash
php 01-basic-batch-processing.php
```

### 02. Parallel Tool Execution
**File:** `02-parallel-tool-execution.php`

Shows how to execute multiple tools in parallel using ParallelToolExecutor.

**Key Concepts:**
- Creating tools for parallel execution
- Using ParallelToolExecutor
- Batched tool execution with concurrency limits
- Performance benefits of parallel tool calls
- Error handling in parallel contexts

**Run:**
```bash
php 02-parallel-tool-execution.php
```

### 03. Promise-Based Workflows
**File:** `03-promise-workflows.php`

Demonstrates promise-based async patterns with AMPHP futures.

**Key Concepts:**
- Creating promises for async operations
- Promise chaining with then/catch
- Waiting for multiple promises (all, allSettled)
- Racing promises
- Building complex async workflows

**Run:**
```bash
php 03-promise-workflows.php
```

### 04. Agent Racing
**File:** `04-agent-racing.php`

Shows how to race multiple agents to get the fastest response.

**Key Concepts:**
- Setting up AsyncCollaborationManager
- Registering multiple agents
- Racing agents for fastest response
- Use cases for agent racing
- Model speed comparison

**Run:**
```bash
php 04-agent-racing.php
```

### 05. Async Multi-Agent Collaboration
**File:** `05-async-multi-agent.php`

Demonstrates parallel execution of multiple specialized agents.

**Key Concepts:**
- Parallel multi-agent execution
- Collaborative parallel workflows
- Batched multi-agent processing
- Shared memory coordination
- Multi-stage parallel pipelines

**Run:**
```bash
php 05-async-multi-agent.php
```

### 06. Concurrency Tuning
**File:** `06-concurrency-tuning.php`

Shows how to choose and tune concurrency levels for optimal performance.

**Key Concepts:**
- Testing different concurrency levels
- Measuring throughput and latency
- Adaptive concurrency adjustment
- Cost-aware concurrency
- Performance monitoring

**Run:**
```bash
php 06-concurrency-tuning.php
```

### 07. Production Async System
**File:** `07-production-async-system.php`

Complete production-ready async system combining all strategies.

**Key Concepts:**
- Integrated async architecture
- Strategy selection (batch, race, parallel)
- Error handling in async systems
- Circuit breaker pattern
- Performance monitoring and reporting

**Run:**
```bash
php 07-production-async-system.php
```

## Prerequisites

```bash
# Install dependencies from chapter 00
cd ../00-environment-setup
composer install

# Set API key
export ANTHROPIC_API_KEY="your-key-here"
```

## Key Async Patterns

### 1. Batch Processing
Process multiple tasks concurrently with controlled concurrency:
```php
$processor = BatchProcessor::create($agent);
$processor->addMany($tasks);
$results = $processor->run(concurrency: 3);
```

### 2. Parallel Tool Execution
Execute tools simultaneously:
```php
$executor = new ParallelToolExecutor($tools);
$results = $executor->execute($toolCalls);
```

### 3. Promise Workflows
Build async workflows with promises:
```php
$promises = $processor->runAsync();
$results = Promise::all($promises);
```

### 4. Agent Racing
Get fastest response:
```php
$winner = $manager->race([
    'agent1' => $query,
    'agent2' => $query,
]);
```

### 5. Parallel Multi-Agent
Coordinate multiple agents:
```php
$results = $manager->executeParallel([
    'researcher' => $task1,
    'analyst' => $task2,
    'writer' => $task3,
]);
```

## Performance Guidelines

### Concurrency Levels

| Level | Use Case | Trade-offs |
|-------|----------|------------|
| 1 | Sequential | Safest, slowest |
| 3-5 | Standard workloads | Balanced |
| 10+ | Bulk processing | Fastest, highest cost |

### When to Use Each Pattern

**Batch Processing:**
- Multiple independent tasks
- Bulk operations
- Report generation

**Parallel Tools:**
- Independent tool calls
- I/O-bound operations
- API integrations

**Racing:**
- Speed-critical responses
- Redundancy needs
- Model comparison

**Multi-Agent:**
- Complex workflows
- Specialized agents
- Parallel subtasks

## Common Patterns

### Error Handling
```php
try {
    $results = $processor->run(concurrency: 3);
    $successful = $processor->getSuccessful();
    $failed = $processor->getFailed();
} catch (\Throwable $e) {
    // Handle errors
}
```

### Circuit Breaker
```php
$breaker = new AsyncCircuitBreaker(threshold: 5);
$result = $breaker->execute(fn() => $operation());
```

### Adaptive Tuning
```php
if ($successRate > 0.95) {
    $concurrency = min($concurrency + 1, 10);
} elseif ($successRate < 0.85) {
    $concurrency = max($concurrency - 1, 1);
}
```

## Troubleshooting

### Common Issues

1. **Too many concurrent requests**
   - Reduce concurrency level
   - Implement rate limiting
   - Use batching

2. **High error rates**
   - Decrease concurrency
   - Add retry logic
   - Check API limits

3. **Memory issues**
   - Process in smaller batches
   - Clean up after each batch
   - Monitor memory usage

4. **Cost overruns**
   - Implement cost-aware concurrency
   - Monitor token usage
   - Set budget limits

## Further Reading

- [AMPHP Documentation](https://amphp.org/)
- [claude-php-agent Async Guide](https://github.com/claude-php/claude-php-agent#async--concurrent-execution)
- Chapter 18: Performance and Cost Optimization
- Chapter 20: Capstone Project

## Testing

Run all examples in sequence:
```bash
for file in 0*.php; do
    echo "Running $file..."
    php "$file"
    echo ""
done
```

## Notes

- All examples use the Haiku model by default for speed
- Adjust concurrency based on your API rate limits
- Monitor costs when using high concurrency
- Use circuit breakers in production
- Track metrics for optimization

Happy async coding! 🚀
