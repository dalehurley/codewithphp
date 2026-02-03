# Chapter 09: Planning - From Tasks to Steps

Code examples for the Planning chapter of the Agentic AI for PHP Developers series.

## Overview

This directory contains examples demonstrating plan-execute patterns using the [`claude-php/agent`](https://github.com/claude-php/claude-php-agent) framework.

## Prerequisites

```bash
composer require claude-php/agent
export ANTHROPIC_API_KEY="your-key-here"
```

## Files

### Core Examples

- **`basic-plan-execute.php`** — Simple plan-execute workflow
  - Creates multi-step plans
  - Executes steps systematically
  - Synthesizes final answer
  - Basic callbacks and tracking

- **`task-decomposition.php`** — Task decomposition strategies
  - Sequential decomposition (steps depend on each other)
  - Parallel decomposition (independent steps)
  - Hierarchical decomposition (phases → substeps)
  - Iterative decomposition (repeating cycles)

- **`step-execution-tracking.php`** — Progress tracking during execution
  - Visual progress bars
  - Step timing information
  - Execution summaries
  - Performance metrics

### Advanced Examples

- **`dynamic-replanning.php`** — Replanning when steps fail
  - Failure detection
  - Automatic replanning triggers
  - Plan revision based on progress
  - Alternative approach generation

- **`plan-with-tools.php`** — Planning with tool execution
  - Multi-tool integration
  - Tool execution during steps
  - Complete automated workflows
  - Result aggregation

- **`ml-optimized-planning.php`** — ML-enhanced plan optimization
  - Learns optimal plan granularity
  - Learns ideal step counts
  - Reduces overhead by 15-25%
  - Quality vs cost optimization

### Production Example

- **`production-planning-system.php`** — Complete production system
  - Comprehensive logging
  - Error handling
  - Batch execution support
  - Monitoring integration

## Usage

### Run Basic Example

```bash
php basic-plan-execute.php
```

**Expected output:**

```
=== Task: Research and Compare PHP Frameworks ===

=== Plan Created ===

Total steps: 5

  1. Research Laravel framework features...
  2. Research Symfony framework features...
  3. Research Slim framework features...
  4. Compare the three frameworks...
  5. Provide a recommendation...

✅ Step 1 complete
...
```

### Run with Progress Tracking

```bash
php step-execution-tracking.php
```

### Test Replanning

```bash
php dynamic-replanning.php
```

## Key Concepts

### Plan-Execute Pattern

1. **Plan:** Analyze task and create step-by-step plan
2. **Execute:** Run each step systematically
3. **Monitor:** Check for failures or issues
4. **Replan:** Revise plan if needed
5. **Synthesize:** Combine results into final answer

### When to Use Plan-Execute

Use Plan-Execute when:
- ✅ Tasks have clear sequential steps
- ✅ You need progress visibility
- ✅ Steps have dependencies
- ✅ Upfront structure helps

Use React when:
- ✅ Tasks are exploratory
- ✅ High flexibility required
- ✅ Steps emerge dynamically

### Replanning Triggers

PlanExecuteLoop automatically replans when step results contain:
- `error`
- `failed`
- `unable`
- `cannot`
- `impossible`

### ML Optimization

Enable ML learning to optimize:
- Plan detail level (high/medium/low)
- Step count per task type
- Replanning effectiveness
- Token usage vs quality

## Integration with Other Chapters

- **Chapter 02:** ReactLoop vs PlanExecuteLoop comparison
- **Chapter 03:** Tool integration during step execution
- **Chapter 06:** Maintaining state across plan steps
- **Chapter 10:** Combining planning with reflection
- **Chapter 11:** Multi-stage workflows with plan-execute

## Common Patterns

### Sequential Execution

```php
$loop = new PlanExecuteLoop(allowReplan: true);
$agent = Agent::create($client)->withLoopStrategy($loop);
$result = $agent->run("Multi-step task");
```

### With Tools

```php
$agent = new PlanExecuteAgent($client, [
    'tools' => [$tool1, $tool2],
    'allow_replan' => true,
]);
```

### ML Optimized

```php
$agent = new PlanExecuteAgent($client, [
    'enable_ml_optimization' => true,
    'ml_history_path' => 'plan_history.json',
]);
```

### Progress Tracking

```php
$loop->onPlanCreated(fn($steps) => /* handle plan */);
$loop->onStepComplete(fn($num, $desc, $result) => /* track */);
```

## Testing

Run all examples to verify functionality:

```bash
for file in *.php; do
    echo "Testing $file..."
    php "$file" || echo "Failed: $file"
done
```

## Performance Tips

1. **Set appropriate max_iterations** — Plan + execution + synthesis needs enough iterations
2. **Use allowReplan selectively** — Replanning adds cost but improves resilience
3. **Enable ML optimization** — Reduces overhead by 15-25% over time
4. **Track metrics** — Monitor plan quality, step count, and replan frequency

## Troubleshooting

### "Max iterations reached"

Increase `maxIterations` — plan-execute requires more iterations than simple React:

```php
$agent->maxIterations(20); // Instead of default 10
```

### "Plan parsing failed"

The framework parses numbered steps automatically. Ensure your prompts request:

```
1. First step
2. Second step
...
```

### "Excessive replanning"

Adjust failure detection thresholds or disable replanning for simple tasks:

```php
$loop = new PlanExecuteLoop(allowReplan: false);
```

## Further Reading

- [PlanExecuteLoop Source](https://github.com/claude-php/claude-php-agent/blob/master/src/Loops/PlanExecuteLoop.php)
- [PlanExecuteAgent Source](https://github.com/claude-php/claude-php-agent/blob/master/src/Agents/PlanExecuteAgent.php)
- [Loop Strategies Documentation](https://github.com/claude-php/claude-php-agent/blob/master/docs/loop-strategies.md)
- Chapter 09 Tutorial: [Planning: From Tasks to Steps](../../chapters/09-planning-from-tasks-to-steps.md)

## License

MIT License - see [LICENSE](../../../../../LICENSE) for details.
