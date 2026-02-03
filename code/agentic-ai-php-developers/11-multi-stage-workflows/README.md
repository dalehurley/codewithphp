# Chapter 11: Multi-Stage Workflows and Agent Graphs

Complete, runnable code examples for building DAG-style workflows with the `claude-php/agent` framework.

## Overview

This directory contains 8 production-ready examples demonstrating:
- **Sequential workflows** - Multi-stage pipelines with data flow
- **Parallel execution** - Concurrent agent processing
- **Conditional routing** - Dynamic workflow paths with RouterChain
- **State management** - Workflow state tracking and recovery
- **Complex DAGs** - Branching, merging, and multi-path workflows
- **Multi-agent orchestration** - Coordinating specialized agents
- **Workflow monitoring** - Real-time tracking and metrics
- **Production systems** - Complete orchestration framework

## Prerequisites

```bash
cd /path/to/your/project
composer require claude-php/agent
```

**Required:**
- PHP 8.4+
- `claude-php/agent` package
- `ANTHROPIC_API_KEY` environment variable

## Examples

### 1. Basic Sequential Workflow

Simple 3-stage pipeline: Extract → Analyze → Format

```bash
php basic-sequential-workflow.php
```

**What it demonstrates:**
- Sequential chain composition
- Output mapping between stages
- Workflow callbacks
- LLMChain + TransformChain integration

**Output:** Named entity extraction with sentiment analysis

---

### 2. Parallel Execution

Concurrent analysis: Sentiment + Topics + Keywords + Summary

```bash
php parallel-execution.php
```

**What it demonstrates:**
- ParallelChain for concurrent execution
- Aggregation strategies (merge, first, all)
- Time savings vs sequential execution
- Error handling for individual chains

**Output:** Multi-faceted content analysis with metrics

---

### 3. Conditional Routing

Dynamic routing based on content classification

```bash
php conditional-routing.php
```

**What it demonstrates:**
- RouterChain for conditional logic
- Content-based routing (code, docs, questions)
- Multi-criteria conditions
- Route metadata tracking

**Output:** Specialized processing based on input type

---

### 4. State Management

Workflow state tracking with checkpointing and recovery

```bash
php state-management.php
```

**What it demonstrates:**
- StateManager for workflow persistence
- Goal tracking across stages
- Checkpoint creation before expensive operations
- Recovery from failures
- Resume interrupted workflows

**Output:** Stateful workflow with progress tracking

**Note:** This example supports resuming from checkpoints. Run it multiple times to see state persistence in action.

---

### 5. Complex DAG Workflow

Sophisticated multi-path workflow with branching and merging

```bash
php complex-dag-workflow.php
```

**What it demonstrates:**
- Classification → Router → Parallel QA → Report
- DAG structure with multiple paths
- Conditional routing integrated with sequential execution
- Parallel quality assurance (review + validation)
- Structured result aggregation

**Output:** Complete content processing pipeline with QA

---

### 6. Multi-Agent Orchestration

Coordinating specialized agents with shared memory

```bash
php multi-agent-orchestration.php
```

**What it demonstrates:**
- Agent specialization (Researcher, Analyzer, Writer)
- SharedMemory for agent collaboration
- Tool-based handoff patterns
- Sequential agent execution
- Collaboration metrics

**Output:** Research → Analysis → Report with agent handoffs

**Duration:** ~60-90 seconds (3 agent executions)

---

### 7. Workflow Monitoring

Real-time execution tracking and performance metrics

```bash
php workflow-monitoring.php
```

**What it demonstrates:**
- WorkflowMonitor class for observability
- Real-time step tracking
- Execution timeline
- Error logging
- Performance metrics
- Comprehensive execution reports

**Output:** Workflow with detailed monitoring report

---

### 8. Production Workflow System

Complete production-grade orchestration framework

```bash
php production-workflow-system.php
```

**What it demonstrates:**
- WorkflowOrchestrator class
- Workflow registration and execution
- State management integration
- Error handling and recovery
- Metrics collection
- WorkflowResult typing
- Modular workflow composition

**Output:** Two production workflows with full orchestration

---

## Key Concepts

### Sequential Workflows

Execute chains in order with data flow between stages:

```php
$pipeline = SequentialChain::create()
    ->addChain('step1', $chain1)
    ->addChain('step2', $chain2)
    ->mapOutput('step1', 'result', 'step2', 'input');
```

### Parallel Execution

Run multiple chains concurrently:

```php
$parallel = ParallelChain::create()
    ->addChain('task1', $chain1)
    ->addChain('task2', $chain2)
    ->withAggregation('all');
```

### Conditional Routing

Route based on input conditions:

```php
$router = RouterChain::create()
    ->addRoute(fn($input) => condition($input), $chain1)
    ->addRoute(fn($input) => otherCondition($input), $chain2)
    ->setDefault($defaultChain);
```

### State Management

Track workflow progress:

```php
$stateManager = new StateManager('workflow_state.json');
$state = $stateManager->load() ?? AgentState::create('workflow-id');

$state->addGoal(new Goal('task-1', 'Description', GoalStatus::PENDING));
$state->updateGoal('task-1', GoalStatus::COMPLETED);
$stateManager->save($state);
```

## Workflow Patterns

### Fan-Out, Fan-In

Process multiple items in parallel, then aggregate:

```php
SequentialChain::create()
    ->addChain('split', $splitChain)
    ->addChain('parallel_process', ParallelChain::create()->...)
    ->addChain('aggregate', $aggregateChain);
```

### Try-Fallback

Try primary, fallback on failure:

```php
ParallelChain::create()
    ->addChain('primary', $primaryChain)
    ->addChain('fallback', $fallbackChain)
    ->withAggregation('first');
```

### Validation Pipeline

Validate at each stage:

```php
SequentialChain::create()
    ->addChain('validate_input', $inputValidator)
    ->addChain('process', $processor)
    ->addChain('validate_output', $outputValidator)
    ->setCondition('process', fn($r) => $r['validate_input']['valid']);
```

## Testing

All examples are self-contained and can run independently. To test:

```bash
# Test a single example
php basic-sequential-workflow.php

# Test all examples (sequential, takes ~10-15 minutes)
for file in *.php; do
    echo "Testing $file..."
    php "$file"
    echo "---"
done
```

## Performance Notes

- **Sequential chains**: Execution time = sum of all stages
- **Parallel chains**: Execution time ≈ slowest chain (simulated parallel)
- **State persistence**: Adds ~10-50ms per save operation
- **Monitoring**: Minimal overhead (<1% increase)

## Troubleshooting

### "ANTHROPIC_API_KEY not set"

Set your API key:

```bash
export ANTHROPIC_API_KEY=your-key-here
php example.php
```

Or create a `.env` file in the project root:

```
ANTHROPIC_API_KEY=your-key-here
```

### "Class not found" errors

Install dependencies:

```bash
composer install
```

### State file permissions

If state management examples fail:

```bash
chmod 755 /path/to/code/11-multi-stage-workflows
```

### Memory issues with large workflows

Increase PHP memory limit:

```bash
php -d memory_limit=512M production-workflow-system.php
```

## Production Considerations

1. **Error Handling**: All workflows should have try-catch wrappers
2. **Timeouts**: Set appropriate timeouts for long-running workflows
3. **State Backups**: Enable backup retention for critical workflows
4. **Monitoring**: Implement comprehensive logging and metrics
5. **Cost Control**: Monitor token usage across parallel executions
6. **Idempotency**: Design workflows to be safely re-runnable

## Further Reading

- [Chain Composition Documentation](https://github.com/claude-php/claude-php-agent/blob/master/docs/chains.md)
- [StateManager Documentation](https://github.com/claude-php/claude-php-agent/blob/master/docs/state-management.md)
- [Multi-Agent Systems](https://github.com/claude-php/claude-php-agent/blob/master/docs/multi-agent.md)
- [Chapter 11: Multi-Stage Workflows](/series/agentic-ai-php-developers/chapters/11-multi-stage-workflows-and-agent-graphs)

## License

MIT License - see LICENSE file for details
