---
title: "10: Reflection and Self-Review Loops"
description: "Use ReflectionLoop for self-improvement. Check answers, validate tool outputs, and reduce mistakes with critique stages."
series: "agentic-ai-php-developers"
chapter: 10
difficulty: "Advanced"
keywords: ["Reflection Loop PHP", "Self-Review Agents", "Quality Improvement", "Agent Refinement", "Self-Critique AI", "Iterative Improvement", "Quality Assurance Agents"]
teaches: ["Generate-Reflect-Refine pattern", "Quality scoring and thresholds", "Custom evaluation criteria", "Tool validation with reflection", "Production quality control", "Reflection callbacks and monitoring", "Cost-quality trade-offs"]
estimatedTime: "PT120M"
---

# Chapter 10: Reflection and Self-Review Loops

## Overview

You've built agents that **react** (Chapter 02) and agents that **plan** (Chapter 09). But what about agents that **improve their own work**? What if your agent could critique its outputs, identify weaknesses, and refine them iteratively—just like a human would review and revise a draft?

This is the power of **reflection loops**. Instead of generating output once and hoping for the best, reflection agents operate in a **Generate-Reflect-Refine cycle**: they create an initial answer, evaluate its quality, and then improve it based on that evaluation. This pattern is essential for tasks where quality matters more than speed—writing, code generation, analysis, and decision-making.

The [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent) framework provides `ReflectionLoop` for exactly this purpose: a self-improving agent that iteratively refines outputs until they meet quality thresholds.

In this chapter you'll:

- Master the **Generate-Reflect-Refine pattern**
- Implement **quality scoring and thresholds**
- Define **custom evaluation criteria**
- Validate **tool outputs through reflection**
- Build **production-grade quality control systems**
- Monitor **reflection metrics and costs**
- Optimize **cost vs. quality trade-offs**

**Estimated time:** ~120 minutes

::: info Framework Version
This chapter is based on [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent) **v0.5+**. We'll use ReflectionLoop extensively throughout.
:::

::: info Code examples
Complete, runnable examples for this chapter:

- [`basic-reflection.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/10-reflection-and-self-review/basic-reflection.php) — Simple reflection loop
- [`custom-criteria.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/10-reflection-and-self-review/custom-criteria.php) — Custom evaluation criteria
- [`quality-thresholds.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/10-reflection-and-self-review/quality-thresholds.php) — Quality scoring and stopping conditions
- [`tool-validation.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/10-reflection-and-self-review/tool-validation.php) — Validating tool outputs
- [`reflection-monitoring.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/10-reflection-and-self-review/reflection-monitoring.php) — Tracking reflection metrics
- [`code-review-agent.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/10-reflection-and-self-review/code-review-agent.php) — Practical code review example
- [`content-refinement.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/10-reflection-and-self-review/content-refinement.php) — Content writing with reflection
- [`production-reflection-system.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/10-reflection-and-self-review/production-reflection-system.php) — Complete production system

All files are in [`code/10-reflection-and-self-review/`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/10-reflection-and-self-review/README.md).
:::

---

## Understanding Reflection Loops

Before implementing, let's understand what makes reflection different from other loop strategies.

### The Generate-Reflect-Refine Cycle

A reflection loop operates in three distinct phases:

```text
┌─────────────────────────────────┐
│  PHASE 1: GENERATE              │
│  Create initial output          │
│  (may use tools)                │
└───────────┬─────────────────────┘
            ↓
┌─────────────────────────────────┐
│  PHASE 2: REFLECT               │
│  Evaluate quality               │
│  Identify issues                │
│  Suggest improvements           │
│  Assign quality score (1-10)    │
└───────────┬─────────────────────┘
            ↓
        Score ≥ Threshold?
            ↓ No
┌─────────────────────────────────┐
│  PHASE 3: REFINE                │
│  Apply improvements             │
│  Address issues                 │
│  (may use tools)                │
└───────────┬─────────────────────┘
            ↓
    Repeat REFLECT → REFINE
    (until threshold or max refinements)
```

### Comparison: React vs Plan vs Reflection

| Aspect | ReactLoop | PlanExecuteLoop | ReflectionLoop |
| --- | --- | --- | --- |
| **Pattern** | Reason → Act → Observe | Plan → Execute → Synthesize | Generate → Reflect → Refine |
| **Goal** | Complete task | Execute plan systematically | Maximize quality |
| **Iterations** | Unpredictable (1-10+) | Predictable (plan-driven) | Fixed (1 + N refinements) |
| **Quality Focus** | Task completion | Plan adherence | Output excellence |
| **Best For** | General tasks | Multi-step workflows | Quality-critical outputs |
| **Token Usage** | Medium | High | **Very High** |
| **Latency** | Low-Medium | Medium-High | **High** |

### When to Use Reflection Loops

✅ **Perfect for:**
- **Content creation**: Blog posts, documentation, emails
- **Code generation**: Functions, classes, APIs
- **Analysis**: Research reports, data summaries
- **Decision-making**: Evaluations, recommendations
- **Any task where quality > speed**

❌ **Not ideal for:**
- Simple lookups or calculations
- Real-time interactions
- Budget-constrained applications
- Tasks with external quality validation

---

## Basic Reflection Loop

Let's start with a simple example to see reflection in action.

### Minimal Example

```php
<?php

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\ReflectionLoop;
use ClaudePhp\ClaudePhp;

require 'vendor/autoload.php';

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Create reflection loop
$loop = new ReflectionLoop(
    maxRefinements: 3,        // Up to 3 refinement iterations
    qualityThreshold: 8,      // Stop when score ≥ 8/10
    criteria: 'clarity, accuracy, and completeness'
);

// Add callback to monitor reflection progress
$loop->onReflection(function (int $refinement, int $score, string $feedback) {
    echo "Refinement #{$refinement}: Score {$score}/10\n";
    echo "Feedback: " . substr($feedback, 0, 200) . "...\n\n";
});

// Create agent with reflection loop
$agent = Agent::create($client)
    ->withLoopStrategy($loop)
    ->withSystemPrompt('You are a helpful assistant that creates high-quality explanations.')
    ->maxIterations(15);

// Run a quality-critical task
$result = $agent->run(
    'Explain the concept of dependency injection in PHP to a junior developer.'
);

echo "Final Output:\n";
echo str_repeat("=", 80) . "\n";
echo $result->getAnswer() . "\n";
echo str_repeat("=", 80) . "\n";

// Access reflection metadata
$metadata = $result->getMetadata();
echo "\nQuality Metrics:\n";
echo "- Final Score: {$metadata['final_score']}/10\n";
echo "- Total Refinements: " . count($metadata['reflections']) . "\n";
echo "- Iterations: {$result->getIterations()}\n";
echo "- Tokens: " . json_encode($result->getTokenUsage()) . "\n";
```

### What Happens Here?

1. **Generate**: Agent creates initial explanation
2. **Reflect**: Agent evaluates clarity, accuracy, completeness
3. **Score**: Agent assigns quality score (e.g., 6/10)
4. **Refine**: Agent improves explanation based on feedback
5. **Repeat**: Continue until score ≥ 8 or 3 refinements done

---

## Configuring Reflection Loops

`ReflectionLoop` provides several configuration options to control behavior.

### Constructor Parameters

```php
$loop = new ReflectionLoop(
    logger: $logger,              // PSR-3 logger (optional)
    maxRefinements: 3,            // Max refinement iterations
    qualityThreshold: 8,          // Quality score (1-10) to stop
    criteria: 'accuracy and clarity'  // Evaluation criteria
);
```

### Configuration Trade-offs

| Parameter | Low Value | High Value |
| --- | --- | --- |
| `maxRefinements` | Faster, cheaper | Better quality |
| `qualityThreshold` | More refinements | Stops earlier |
| `criteria` | General evaluation | Task-specific quality |

### Example: Strict Quality Requirements

```php
// For mission-critical outputs
$strictLoop = new ReflectionLoop(
    maxRefinements: 5,          // Allow more iterations
    qualityThreshold: 9,        // Demand excellence
    criteria: 'correctness, security, performance, and maintainability'
);
```

### Example: Fast Iteration

```php
// For budget-constrained scenarios
$fastLoop = new ReflectionLoop(
    maxRefinements: 1,          // Single refinement pass
    qualityThreshold: 7,        // Lower bar
    criteria: 'basic correctness'
);
```

---

## Custom Evaluation Criteria

The power of reflection comes from **custom evaluation criteria** tailored to your specific task.

### Default Criteria

Without custom criteria, ReflectionLoop uses:
> "correctness, completeness, clarity, and quality"

### Domain-Specific Criteria

#### Code Generation

```php
$codeLoop = new ReflectionLoop(
    maxRefinements: 3,
    qualityThreshold: 8,
    criteria: 'correctness, error handling, type safety, PSR-12 compliance, and documentation'
);
```

#### Content Writing

```php
$contentLoop = new ReflectionLoop(
    maxRefinements: 3,
    qualityThreshold: 8,
    criteria: 'clarity, engagement, accuracy, tone appropriateness, and grammar'
);
```

#### Data Analysis

```php
$analysisLoop = new ReflectionLoop(
    maxRefinements: 3,
    qualityThreshold: 9,
    criteria: 'statistical accuracy, insight depth, visualization clarity, and actionable recommendations'
);
```

#### Technical Documentation

```php
$docsLoop = new ReflectionLoop(
    maxRefinements: 3,
    qualityThreshold: 8,
    criteria: 'accuracy, completeness, clarity for target audience, code examples, and navigation'
);
```

### Structured Criteria Approach

For complex evaluations, structure your criteria:

```php
$criteria = <<<CRITERIA
Evaluate on these dimensions:

1. Correctness (30%):
   - Factual accuracy
   - No misleading statements
   - Valid code examples

2. Completeness (25%):
   - All requirements addressed
   - Edge cases considered
   - No missing information

3. Clarity (25%):
   - Easy to understand
   - Logical structure
   - Clear examples

4. Professionalism (20%):
   - Appropriate tone
   - Proper grammar
   - Polished presentation

Provide specific feedback for each dimension.
CRITERIA;

$loop = new ReflectionLoop(
    maxRefinements: 3,
    qualityThreshold: 8,
    criteria: $criteria
);
```

---

## Quality Scoring and Thresholds

Understanding how scoring works helps optimize your reflection loops.

### How Scores Are Extracted

ReflectionLoop uses pattern matching to extract scores from reflection text:

```php
// Recognizes these patterns:
"Score: 7/10"
"Quality: 8"
"Rating: 6 out of 10"
"Overall score of 7"
"7/10"
```

**Default if no score found:** 5/10

### Score Interpretation Guidelines

| Score | Meaning | Action |
| --- | --- | --- |
| 1-3 | Poor quality, major issues | Continue refining (likely) |
| 4-5 | Below average, multiple problems | Continue refining |
| 6-7 | Acceptable but improvable | Depends on threshold |
| 8-9 | High quality, minor issues only | Often meets threshold |
| 10 | Perfect (rare) | Exceeds threshold |

### Setting Appropriate Thresholds

#### High-Stakes Tasks (Threshold: 9)

```php
// Medical advice, legal content, financial analysis
$loop = new ReflectionLoop(
    maxRefinements: 5,
    qualityThreshold: 9,
    criteria: 'accuracy, completeness, and legal compliance'
);
```

#### Production Code (Threshold: 8)

```php
// Code that ships to users
$loop = new ReflectionLoop(
    maxRefinements: 3,
    qualityThreshold: 8,
    criteria: 'correctness, security, and maintainability'
);
```

#### Internal Documentation (Threshold: 7)

```php
// Internal docs, draft content
$loop = new ReflectionLoop(
    maxRefinements: 2,
    qualityThreshold: 7,
    criteria: 'clarity and completeness'
);
```

#### Rapid Prototyping (Threshold: 6)

```php
// Quick drafts, proof-of-concepts
$loop = new ReflectionLoop(
    maxRefinements: 1,
    qualityThreshold: 6,
    criteria: 'basic functionality'
);
```

---

## Reflection Callbacks and Monitoring

Monitor reflection progress with callbacks to track quality improvements.

### Available Callbacks

```php
$loop = new ReflectionLoop(maxRefinements: 3);

// 1. Iteration callback (fired for every LLM call)
$loop->onIteration(function (int $iteration, $response, $context) {
    echo "Iteration {$iteration}: " . 
         ($response->stop_reason ?? 'unknown') . "\n";
});

// 2. Tool execution callback
$loop->onToolExecution(function (string $tool, array $input, $result) {
    echo "Tool '{$tool}' executed\n";
});

// 3. Reflection callback (fired after each reflection)
$loop->onReflection(function (int $refinement, int $score, string $feedback) {
    echo "Refinement {$refinement}: {$score}/10\n";
    echo "Issues: " . substr($feedback, 0, 150) . "\n\n";
});
```

### Building a Reflection Monitor

```php
class ReflectionMonitor
{
    private array $refinements = [];
    private float $startTime;

    public function start(): void
    {
        $this->startTime = microtime(true);
        $this->refinements = [];
    }

    public function recordRefinement(int $num, int $score, string $feedback): void
    {
        $this->refinements[] = [
            'number' => $num,
            'score' => $score,
            'feedback' => $feedback,
            'timestamp' => microtime(true) - $this->startTime,
        ];
    }

    public function getReport(): array
    {
        $scores = array_column($this->refinements, 'score');
        
        return [
            'total_refinements' => count($this->refinements),
            'initial_score' => $scores[0] ?? 0,
            'final_score' => end($scores) ?: 0,
            'improvement' => (end($scores) ?: 0) - ($scores[0] ?? 0),
            'duration' => microtime(true) - $this->startTime,
            'refinements' => $this->refinements,
        ];
    }
}

// Usage
$monitor = new ReflectionMonitor();
$monitor->start();

$loop = new ReflectionLoop(maxRefinements: 3);
$loop->onReflection([$monitor, 'recordRefinement']);

$agent = Agent::create($client)->withLoopStrategy($loop);
$result = $agent->run($task);

$report = $monitor->getReport();
echo "Improved by {$report['improvement']} points over {$report['duration']}s\n";
```

---

## Tool Validation with Reflection

One powerful use case: validating tool outputs for correctness.

### Problem: Tools Can Return Bad Data

```php
// Search tool might return irrelevant results
// API tool might return stale data
// Calculator tool might have edge case bugs
```

### Solution: Reflect on Tool Outputs

```php
<?php

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\ReflectionLoop;
use ClaudeAgents\Tools\Tool;

// Create a search tool (simulated)
$searchTool = Tool::create('search')
    ->description('Search for information')
    ->parameter('query', 'string', 'Search query')
    ->required('query')
    ->handler(function (array $input): string {
        // Simulate potentially incomplete/incorrect search results
        return json_encode([
            'results' => [
                'PHP 8.4 was released in November 2024',
                'PHP 8.4 includes property hooks',
                // Intentionally incomplete
            ]
        ]);
    });

// Reflection loop with validation criteria
$loop = new ReflectionLoop(
    maxRefinements: 3,
    qualityThreshold: 8,
    criteria: 'completeness of information, factual accuracy, and coverage of all key features'
);

$loop->onReflection(function (int $ref, int $score, string $feedback) {
    echo "Validation pass {$ref}: {$score}/10\n";
    if ($score < 8) {
        echo "Issues found: " . substr($feedback, 0, 200) . "\n\n";
    }
});

$agent = Agent::create($client)
    ->withLoopStrategy($loop)
    ->withTool($searchTool)
    ->withSystemPrompt('You are a helpful assistant. Use tools when needed.')
    ->maxIterations(15);

$result = $agent->run('What are the major features in PHP 8.4?');

echo "Final answer (validated through reflection):\n";
echo $result->getAnswer() . "\n";
```

**What happens:**
1. Agent calls search tool
2. Gets incomplete results
3. Generates initial answer
4. **Reflection identifies missing information**
5. Agent searches again or synthesizes better answer
6. Process repeats until validation passes

---

## Production Reflection System

Let's build a complete production-grade reflection system with monitoring, logging, and cost controls.

### Architecture

```text
┌─────────────────────────────────────┐
│   ReflectionOrchestrator            │
│  - Quality settings per task type   │
│  - Cost tracking                    │
│  - Performance monitoring           │
└─────────────┬───────────────────────┘
              ↓
┌─────────────────────────────────────┐
│   ReflectionLoop                    │
│  - Generate → Reflect → Refine      │
└─────────────┬───────────────────────┘
              ↓
┌─────────────────────────────────────┐
│   Reflection Metrics Store          │
│  - Score history                    │
│  - Token usage                      │
│  - Performance data                 │
└─────────────────────────────────────┘
```

### Implementation

See the complete implementation in:
- [`production-reflection-system.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/10-reflection-and-self-review/production-reflection-system.php)

Key components:

```php
class ReflectionOrchestrator
{
    private array $qualityProfiles = [
        'critical' => [
            'maxRefinements' => 5,
            'qualityThreshold' => 9,
            'maxTokens' => 20000,
        ],
        'standard' => [
            'maxRefinements' => 3,
            'qualityThreshold' => 8,
            'maxTokens' => 10000,
        ],
        'draft' => [
            'maxRefinements' => 1,
            'qualityThreshold' => 6,
            'maxTokens' => 5000,
        ],
    ];

    public function executeWithProfile(
        string $task,
        string $profile = 'standard',
        ?string $criteria = null
    ): array {
        $config = $this->qualityProfiles[$profile];
        
        $loop = new ReflectionLoop(
            maxRefinements: $config['maxRefinements'],
            qualityThreshold: $config['qualityThreshold'],
            criteria: $criteria
        );

        // Add monitoring
        $metrics = [];
        $loop->onReflection(function ($ref, $score, $feedback) use (&$metrics) {
            $metrics[] = compact('ref', 'score', 'feedback');
        });

        $agent = Agent::create($this->client)
            ->withLoopStrategy($loop)
            ->maxIterations(20);

        $result = $agent->run($task);

        return [
            'result' => $result,
            'metrics' => $metrics,
            'profile' => $profile,
        ];
    }
}
```

---

## Practical Example: Code Review Agent

Let's build a code review agent that uses reflection to provide high-quality feedback.

```php
<?php

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\ReflectionLoop;
use ClaudePhp\ClaudePhp;

require 'vendor/autoload.php';

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Code review criteria
$codeReviewCriteria = <<<CRITERIA
Evaluate this code review on:

1. Issue Detection (30%):
   - Are all bugs/issues identified?
   - Are security concerns noted?
   - Are performance problems flagged?

2. Suggestion Quality (30%):
   - Are suggestions specific and actionable?
   - Are code examples provided?
   - Are alternatives considered?

3. Completeness (25%):
   - Is every part of the code addressed?
   - Are edge cases considered?
   - Is testing feedback included?

4. Communication (15%):
   - Is feedback constructive?
   - Is tone professional?
   - Are priorities clear?

Provide a score (1-10) and specific improvements needed.
CRITERIA;

$loop = new ReflectionLoop(
    maxRefinements: 3,
    qualityThreshold: 8,
    criteria: $codeReviewCriteria
);

$loop->onReflection(function (int $ref, int $score, string $feedback) {
    echo "Review refinement {$ref}: Quality score {$score}/10\n";
    if ($score < 8) {
        echo "Improvements needed:\n";
        echo substr($feedback, 0, 300) . "\n\n";
    }
});

$agent = Agent::create($client)
    ->withLoopStrategy($loop)
    ->withSystemPrompt('You are an expert code reviewer. Provide thorough, constructive feedback.')
    ->maxIterations(15);

// Sample code to review
$codeToReview = <<<'PHP'
<?php

function processPayment($amount, $userId) {
    $pdo = new PDO('mysql:host=localhost;dbname=app', 'root', '');
    
    $stmt = $pdo->query("SELECT * FROM users WHERE id = $userId");
    $user = $stmt->fetch();
    
    if ($user['balance'] >= $amount) {
        $newBalance = $user['balance'] - $amount;
        $pdo->query("UPDATE users SET balance = $newBalance WHERE id = $userId");
        return true;
    }
    
    return false;
}
PHP;

$result = $agent->run(
    "Review this payment processing code for security, correctness, and best practices:\n\n" .
    $codeToReview
);

echo "\n" . str_repeat("=", 80) . "\n";
echo "FINAL CODE REVIEW:\n";
echo str_repeat("=", 80) . "\n";
echo $result->getAnswer() . "\n";

$metadata = $result->getMetadata();
echo "\nReview Quality: {$metadata['final_score']}/10\n";
echo "Refinements: " . count($metadata['reflections']) . "\n";
```

**Expected improvements through reflection:**
- Initial review might miss SQL injection
- Reflection identifies missing security analysis
- Refined review includes prepared statements
- Further refinement adds error handling notes
- Final review is comprehensive and actionable

---

## Cost vs. Quality Trade-offs

Reflection loops can be expensive. Let's optimize costs while maintaining quality.

### Token Usage Pattern

```text
Standard Task (Threshold: 8, Max Refinements: 3):

Generation:    ~1,000 tokens
Reflection 1:  ~800 tokens  (Score: 6/10)
Refinement 1:  ~1,200 tokens
Reflection 2:  ~800 tokens  (Score: 7/10)
Refinement 2:  ~1,200 tokens
Reflection 3:  ~800 tokens  (Score: 8/10 ✓)

Total: ~5,800 tokens
```

### Cost Comparison

| Loop Strategy | Tokens | Cost (Claude 3.5 Sonnet) | Latency |
| --- | --- | --- | --- |
| ReactLoop | ~2,000 | ~$0.006 | 2-3s |
| PlanExecuteLoop | ~3,500 | ~$0.011 | 4-6s |
| **ReflectionLoop** | ~5,800 | ~$0.017 | 8-12s |

### Optimization Strategies

#### 1. Profile-Based Quality

```php
class AdaptiveReflection
{
    public function getConfigForTask(string $taskType): array
    {
        return match($taskType) {
            'user_facing_content' => [
                'maxRefinements' => 3,
                'qualityThreshold' => 8,
            ],
            'internal_doc' => [
                'maxRefinements' => 2,
                'qualityThreshold' => 7,
            ],
            'draft' => [
                'maxRefinements' => 1,
                'qualityThreshold' => 6,
            ],
            default => [
                'maxRefinements' => 2,
                'qualityThreshold' => 7,
            ],
        };
    }
}
```

#### 2. Early Stopping

```php
// Stop early if improvement is minimal
$previousScore = 0;
$loop->onReflection(function ($ref, $score, $feedback) use (&$previousScore) {
    if ($score - $previousScore < 1 && $score >= 7) {
        // Improvement plateaued, consider stopping
        echo "Minimal improvement detected, score acceptable\n";
    }
    $previousScore = $score;
});
```

#### 3. Selective Reflection

```php
function shouldUseReflection(string $task): bool
{
    // Only use reflection for quality-critical tasks
    $qualityCritical = [
        'code_generation',
        'user_communication',
        'data_analysis',
        'decision_making',
    ];

    foreach ($qualityCritical as $pattern) {
        if (str_contains(strtolower($task), $pattern)) {
            return true;
        }
    }

    return false;
}

$loop = shouldUseReflection($task)
    ? new ReflectionLoop(maxRefinements: 3)
    : new ReactLoop();
```

#### 4. Caching Reflections

```php
class ReflectionCache
{
    public function getCachedScore(string $outputHash): ?int
    {
        // If we've seen similar output before, reuse quality score
        return $this->redis->get("reflection:score:{$outputHash}");
    }

    public function cacheScore(string $outputHash, int $score): void
    {
        $this->redis->setex("reflection:score:{$outputHash}", 3600, $score);
    }
}
```

---

## Best Practices

### ✅ DO

1. **Use for quality-critical tasks**
   - Content creation, code generation, analysis
   - When output quality directly impacts users

2. **Set realistic thresholds**
   - Don't demand 10/10 (rarely achievable)
   - 8/10 is excellent for most tasks
   - 7/10 is good for internal use

3. **Customize criteria**
   - Domain-specific evaluation dimensions
   - Measurable, specific criteria
   - Prioritize what matters most

4. **Monitor and adjust**
   - Track quality improvements
   - Measure cost vs. benefit
   - Tune parameters based on data

5. **Use callbacks for visibility**
   - Log reflection scores
   - Track token usage
   - Alert on quality issues

### ❌ DON'T

1. **Use for simple tasks**
   - Lookups, calculations, formatting
   - Tasks with external validation
   - Real-time interactions

2. **Set unrealistic thresholds**
   - Threshold 10: Almost never reached
   - Too low threshold: Wastes refinements
   - Match threshold to task importance

3. **Use generic criteria**
   - "Quality" alone is too vague
   - Specify what quality means
   - Make criteria actionable

4. **Ignore costs**
   - Reflection is 2-3x more expensive
   - Monitor token usage
   - Use selectively

5. **Forget about latency**
   - Reflection adds 5-10s per refinement
   - Not suitable for real-time apps
   - Consider async processing

---

## Common Patterns

### Pattern 1: Two-Stage Reflection

Generate multiple options, then reflect to choose the best:

```php
// Stage 1: Generate 3 options (no reflection)
$options = [];
for ($i = 0; $i < 3; $i++) {
    $result = $agentNoReflection->run($task);
    $options[] = $result->getAnswer();
}

// Stage 2: Use reflection to pick best
$loop = new ReflectionLoop(maxRefinements: 2, qualityThreshold: 8);
$evaluator = Agent::create($client)->withLoopStrategy($loop);

$result = $evaluator->run(
    "Choose the best option and refine it:\n\n" .
    implode("\n\n---\n\n", $options)
);
```

### Pattern 2: Conditional Reflection

Only reflect if initial quality is below threshold:

```php
// Quick quality check
$result = $quickAgent->run($task);
$qualityScore = $this->quickQualityCheck($result->getAnswer());

if ($qualityScore < 7) {
    // Quality insufficient, use reflection
    $loop = new ReflectionLoop(maxRefinements: 2);
    $result = Agent::create($client)
        ->withLoopStrategy($loop)
        ->run($task);
}
```

### Pattern 3: Reflection with External Validation

Combine AI reflection with programmatic checks:

```php
$loop = new ReflectionLoop(maxRefinements: 3);
$loop->onReflection(function ($ref, $score, $feedback) use ($task) {
    // Also run external validation
    if ($task->type === 'code') {
        $syntaxValid = $this->validatePHPSyntax($task->output);
        if (!$syntaxValid) {
            $feedback .= "\n\nSYNTAX ERROR: Code contains syntax errors.";
            $score = min($score, 4); // Cap score if syntax invalid
        }
    }
});
```

---

## Debugging Reflection Loops

### Common Issues

**Issue: Agent never reaches threshold**

```php
// Check if criteria are too strict
$loop = new ReflectionLoop(
    maxRefinements: 5,
    qualityThreshold: 9,  // Try lowering to 8
    criteria: 'perfection in every way'  // Too vague/strict
);
```

**Issue: Scores don't improve**

```php
// Add detailed monitoring
$loop->onReflection(function ($ref, $score, $feedback) {
    echo "Refinement {$ref}:\n";
    echo "Score: {$score}/10\n";
    echo "Full feedback:\n{$feedback}\n\n";
    // Look for whether agent understands criteria
});
```

**Issue: Too expensive**

```php
// Reduce refinements and increase threshold
$loop = new ReflectionLoop(
    maxRefinements: 1,  // Only one refinement pass
    qualityThreshold: 7,  // Lower bar
);
```

---

## Key Takeaways

1. **Reflection = Generate + Reflect + Refine**
   - Three-phase cycle for quality improvement
   - Iterative refinement until threshold met

2. **Use for quality-critical tasks**
   - Content, code, analysis, decisions
   - When quality > speed matters

3. **Configure appropriately**
   - Max refinements: 1-5 depending on budget
   - Quality threshold: 7-9 depending on stakes
   - Custom criteria: Domain-specific evaluation

4. **Monitor costs and quality**
   - Reflection is 2-3x more expensive
   - Track score improvements
   - Optimize based on data

5. **Combine with other strategies**
   - Use selectively (not for all tasks)
   - Combine with external validation
   - Consider two-stage approaches

---

## What's Next?

In **Chapter 11: Multi-Stage Workflows and Agent Graphs**, we'll move beyond single-agent loops to **orchestrate multiple agents** in complex workflows. You'll learn to:

- Build DAG-style agent graphs
- Coordinate sequential and parallel execution
- Manage state transitions between stages
- Combine planning, reflection, and specialized agents

After mastering individual agent patterns (React, Plan, Reflect), you'll be ready to compose them into powerful multi-agent systems.

---

## Additional Resources

- [`claude-php/claude-php-agent` ReflectionLoop Documentation](https://github.com/claude-php/claude-php-agent/blob/master/docs/loop-strategies.md#3-reflectionloop)
- [Reflection Loop Source Code](https://github.com/claude-php/claude-php-agent/blob/master/src/Loops/ReflectionLoop.php)
- [Loop Strategies Demo](https://github.com/claude-php/claude-php-agent/blob/master/examples/loop_strategies_demo.php)

---

**Next:** [Chapter 11: Multi-Stage Workflows and Agent Graphs →](/series/agentic-ai-php-developers/chapters/11-multi-stage-workflows-and-agent-graphs)
