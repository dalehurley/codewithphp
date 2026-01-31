# Chapter 02: Understanding Loop Strategies - Code Examples

Complete, runnable code examples demonstrating the four loop strategies in the `claude-php/agent` framework.

## Prerequisites

- PHP 8.4+
- Composer installed
- Anthropic API key (set in environment)
- `claude-php/agent` package installed

## Setup

If you haven't already set up the environment from Chapter 00:

```bash
cd ../00-environment-setup
composer install
```

Set your API key:

```bash
export ANTHROPIC_API_KEY="your-api-key-here"
```

## Examples

### 1. ReactLoop (Reason-Act-Observe)

**File:** `react-loop.php`

Demonstrates the default ReAct pattern where the agent reasons about what to do next, acts by calling tools, and observes results.

**Use case:** General-purpose tasks with flexible tool orchestration

```bash
php react-loop.php
```

**What it demonstrates:**
- Adaptive reasoning based on tool results
- Dynamic tool selection without upfront planning
- Low latency for tasks with 1-5 tool calls
- Travel planning scenario with weather, distance, and hotel tools

---

### 2. PlanExecuteLoop (Upfront Planning)

**File:** `plan-execute-loop.php`

Demonstrates systematic execution where the agent creates a complete plan first, then executes steps in sequence.

**Use case:** Complex multi-step workflows with clear dependencies

```bash
php plan-execute-loop.php
```

**What it demonstrates:**
- Upfront task planning before execution
- Systematic step-by-step execution with progress tracking
- Replanning support when steps fail
- Business intelligence workflow: query → aggregate → report → email

---

### 3. ReflectionLoop (Self-Critique and Refinement)

**File:** `reflection-loop.php`

Demonstrates quality-focused execution where the agent generates output, critiques it, and refines based on self-identified issues.

**Use case:** Code generation, technical writing, quality-critical tasks

```bash
php reflection-loop.php
```

**What it demonstrates:**
- Self-critique and iterative refinement
- Quality validation with validation tools
- Code generation with PSR-12 compliance checking
- Multiple reflection passes until quality criteria are met

---

### 4. StreamingLoop (Real-Time Updates)

**File:** `streaming-loop.php`

Demonstrates streaming execution with real-time progress updates as the agent works through the task.

**Use case:** Long-running tasks, user-facing applications, progress feedback

```bash
php streaming-loop.php
```

**What it demonstrates:**
- Real-time progress updates during execution
- Visual progress indicators and timestamps
- Tool call monitoring as they happen
- Data analytics workflow with slow operations

---

### 5. Loop Comparison (Side-by-Side)

**File:** `loop-comparison.php`

Runs the same task with all four loop strategies to compare performance characteristics, token usage, and output quality.

```bash
php loop-comparison.php
```

**What it demonstrates:**
- Performance comparison across all loop strategies
- Token usage patterns
- Relative latency measurements
- Recommendations for when to use each strategy

---

### 6. Custom Loop Configuration

**File:** `custom-loop-config.php`

Demonstrates advanced loop customization including adaptive strategy switching, custom termination conditions, and hybrid approaches.

```bash
php custom-loop-config.php
```

**What it demonstrates:**
- Adaptive loop switching (React → PlanExecute on failure)
- Custom termination logic (confidence thresholds, budget constraints)
- Hybrid strategies (Reflection + Streaming)
- Multi-stage execution (planning → execution → refinement)

---

## Loop Strategy Quick Reference

| Strategy | Best For | Latency | Token Usage | File |
| --- | --- | --- | --- | --- |
| **ReactLoop** | General tasks, flexible tool use | Low | Medium | `react-loop.php` |
| **PlanExecuteLoop** | Multi-step workflows, dependencies | Medium | High | `plan-execute-loop.php` |
| **ReflectionLoop** | Quality-critical output | High | Very High | `reflection-loop.php` |
| **StreamingLoop** | Long-running tasks, UX feedback | Low (perceived) | Medium | `streaming-loop.php` |

## Running All Examples

To run all examples in sequence:

```bash
for file in react-loop.php plan-execute-loop.php reflection-loop.php streaming-loop.php loop-comparison.php custom-loop-config.php; do
    echo "Running $file..."
    php "$file"
    echo ""
done
```

## Expected Output

Each example includes:
- Visual progress indicators (emojis, timestamps, progress bars)
- Execution statistics (duration, tool calls, iterations)
- Final agent responses
- Key takeaways and recommendations

## Common Issues

### API Key Not Set

If you see "API key not found" errors:

```bash
export ANTHROPIC_API_KEY="your-api-key-here"
```

### Package Not Installed

If you see "Class not found" errors:

```bash
cd ../00-environment-setup
composer install
```

### Rate Limiting

If you encounter rate limits when running multiple examples:

- Add delays between runs
- Use a lower request rate
- Check your API tier limits

## Customization

Feel free to modify these examples:

1. **Change the tasks** - Try different prompts to see how loops adapt
2. **Add your own tools** - Implement domain-specific tools
3. **Adjust loop parameters** - Modify `maxIterations`, `maxReflections`, etc.
4. **Combine strategies** - Experiment with hybrid approaches

## Learn More

- Chapter 02: [Understanding Loop Strategies](../../../docs/series/agentic-ai-php-developers/chapters/02-understanding-loop-strategies.md)
- Claude PHP Agent: [Loop Strategies Documentation](https://github.com/claude-php/claude-php-agent/blob/master/docs/loop-strategies.md)
- ReAct Pattern: [Original Paper](https://arxiv.org/abs/2210.03629)

## Next Steps

After mastering loop strategies, proceed to:

- **Chapter 03**: Tool System Deep Dive - Schema validation, error handling, and production-grade tools
- **Chapter 04**: Agent Configuration and Best Practices - Retry logic, logging, monitoring

---

**Need help?** Open an issue or check the [series overview](../../../docs/series/agentic-ai-php-developers/index.md).
