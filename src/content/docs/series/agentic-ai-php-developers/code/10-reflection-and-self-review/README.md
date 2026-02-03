# Chapter 10: Reflection and Self-Review Loops - Code Examples

Complete, runnable examples demonstrating reflection loops for self-improving agents using the [`claude-php/agent`](https://github.com/claude-php/claude-php-agent) framework.

## Prerequisites

### Installation

These examples require the `claude-php/agent` framework. Set up a new project:

```bash
# Create a project directory
mkdir reflection-examples
cd reflection-examples

# Initialize composer
composer init --no-interaction

# Install claude-php/agent
composer require claude-php/agent

# Copy example files to this directory
cp /path/to/examples/*.php .

# Set your API key
export ANTHROPIC_API_KEY="your-key-here"
```

**Note:** The examples use `require __DIR__ . '/vendor/autoload.php'` which assumes the vendor directory is in the same location as the example files. Adjust the path if needed.

## Examples Overview

### 1. Basic Reflection (`basic-reflection.php`)

**Purpose:** Introduction to the Generate-Reflect-Refine cycle.

**What it demonstrates:**
- Creating a ReflectionLoop with quality thresholds
- Monitoring refinement progress with callbacks
- Accessing reflection metadata and scores

**Run it:**
```bash
php basic-reflection.php
```

**Expected output:**
- Initial generation of content
- 2-3 refinement cycles with quality scores
- Final high-quality output (8+/10)
- Metrics showing improvement progression

---

### 2. Custom Criteria (`custom-criteria.php`)

**Purpose:** Tailoring evaluation criteria to specific task types.

**What it demonstrates:**
- Code generation criteria (correctness, type safety, security)
- Content writing criteria (clarity, engagement, accuracy)
- Documentation criteria (completeness, beginner-friendliness)
- Structured multi-dimensional evaluation

**Run it:**
```bash
php custom-criteria.php
```

**Key takeaway:** Different tasks need different quality measures.

---

### 3. Quality Thresholds (`quality-thresholds.php`)

**Purpose:** Comparing different quality threshold settings.

**What it demonstrates:**
- Rapid draft profile (threshold: 6/10, 1 refinement)
- Standard profile (threshold: 8/10, 3 refinements)
- Critical profile (threshold: 9/10, 5 refinements)
- Cost vs. quality trade-offs

**Run it:**
```bash
php quality-thresholds.php
```

**Output includes:**
- Side-by-side comparison of all three profiles
- Token usage and latency differences
- Quality score progressions

---

### 4. Tool Validation (`tool-validation.php`)

**Purpose:** Using reflection to validate tool outputs.

**What it demonstrates:**
- Detecting incomplete search results
- Re-querying tools when information is insufficient
- Validating database query results
- Reflection-based data completeness checks

**Run it:**
```bash
php tool-validation.php
```

**Key insight:** Reflection catches when tool results are inadequate.

---

### 5. Reflection Monitoring (`reflection-monitoring.php`)

**Purpose:** Tracking and analyzing reflection metrics.

**What it demonstrates:**
- ReflectionMonitor class for detailed tracking
- Recording quality improvements over time
- Aggregate metrics across multiple tasks
- Performance and timing analysis

**Run it:**
```bash
php reflection-monitoring.php
```

**Output includes:**
- Real-time refinement progress
- Quality improvement trends
- Duration and token usage per refinement
- Aggregate statistics

---

### 6. Code Review Agent (`code-review-agent.php`)

**Purpose:** Practical code review with reflection.

**What it demonstrates:**
- Structured code review criteria
- Iterative improvement of review quality
- Security vulnerability detection
- Actionable feedback generation

**Run it:**
```bash
php code-review-agent.php
```

**Reviews include:**
- Security issues (SQL injection, password hashing)
- Best practices violations
- Code examples for fixes
- Prioritized recommendations

---

### 7. Content Refinement (`content-refinement.php`)

**Purpose:** High-quality content generation with reflection.

**What it demonstrates:**
- Blog post introduction refinement
- Technical documentation improvement
- Professional email composition
- Different criteria for different content types

**Run it:**
```bash
php content-refinement.php
```

**Shows refinement for:**
- Engagement and hook effectiveness
- Clarity and completeness
- Tone appropriateness
- Technical accuracy

---

### 8. Production Reflection System (`production-reflection-system.php`)

**Purpose:** Complete production-grade reflection orchestrator.

**What it demonstrates:**
- Quality profiles (critical, production, standard, draft)
- Token budget controls
- Comprehensive metrics collection
- Profile recommendations by task type
- Aggregate reporting

**Run it:**
```bash
php production-reflection-system.php
```

**Features:**
- `ReflectionOrchestrator` class
- Profile-based execution
- Cost monitoring and controls
- Production-ready architecture

---

## Usage Patterns

### Basic Usage

```php
use ClaudeAgents\Agent;
use ClaudeAgents\Loops\ReflectionLoop;

$loop = new ReflectionLoop(
    maxRefinements: 3,
    qualityThreshold: 8,
    criteria: 'clarity and accuracy'
);

$agent = Agent::create($client)
    ->withLoopStrategy($loop)
    ->run($task);
```

### With Custom Criteria

```php
$criteria = <<<CRITERIA
Evaluate on:
1. Correctness (40%)
2. Completeness (30%)
3. Clarity (30%)
Provide score 1-10 and improvements.
CRITERIA;

$loop = new ReflectionLoop(
    maxRefinements: 3,
    qualityThreshold: 8,
    criteria: $criteria
);
```

### With Monitoring

```php
$loop->onReflection(function ($refinement, $score, $feedback) {
    echo "Refinement {$refinement}: {$score}/10\n";
    echo "Feedback: {$feedback}\n";
});
```

## Configuration Guide

### maxRefinements

- **1**: Quick single-pass improvement
- **2-3**: Standard quality (recommended)
- **4-5**: High-stakes critical content
- **Cost impact:** Linear (more refinements = more tokens)

### qualityThreshold

- **6/10**: Acceptable drafts
- **7/10**: Good internal content
- **8/10**: Production standard (recommended)
- **9/10**: Critical/public content
- **10/10**: Rarely achievable, not recommended

### criteria

- **Generic**: "correctness, completeness, clarity"
- **Code**: "correctness, security, type safety, best practices"
- **Content**: "clarity, engagement, accuracy, tone"
- **Docs**: "completeness, accuracy, beginner-friendliness, examples"

## When to Use Reflection

### ✅ Perfect For

- Content creation (blogs, docs, emails)
- Code generation and review
- Data analysis and reports
- Decision-making and recommendations
- Any task where quality > speed

### ❌ Not Ideal For

- Simple lookups or calculations
- Real-time interactions
- Budget-constrained scenarios
- Tasks with external validation

## Performance Considerations

### Token Usage

- ReflectionLoop uses **2-3x more tokens** than ReactLoop
- Example: Standard task with 3 refinements ≈ 5,000-8,000 tokens
- Set token budgets for cost control

### Latency

- Each refinement adds **2-4 seconds**
- 3 refinements ≈ 8-12 seconds total
- Use async processing for user-facing applications

### Cost Optimization

1. **Use profile-based quality settings**
   ```php
   $profile = match($taskType) {
       'critical' => ['max' => 5, 'threshold' => 9],
       'standard' => ['max' => 3, 'threshold' => 8],
       'draft' => ['max' => 1, 'threshold' => 6],
   };
   ```

2. **Early stopping**
   - Stop if score improvement < 1 point
   - Stop if score acceptable (≥7) even if threshold not met

3. **Selective reflection**
   - Only use for quality-critical tasks
   - Use ReactLoop for simple tasks
   - Profile tasks before choosing loop strategy

## Testing the Examples

Run all examples:

```bash
# Run individually
php basic-reflection.php
php custom-criteria.php
php quality-thresholds.php
# ... etc

# Or create a test runner
for file in *.php; do
    echo "Running $file..."
    php "$file"
    echo "---"
done
```

## Troubleshooting

**Issue:** Agent never reaches threshold

```php
// Solution: Lower threshold or increase max refinements
$loop = new ReflectionLoop(
    maxRefinements: 5,  // Was 3
    qualityThreshold: 7,  // Was 9
);
```

**Issue:** Scores don't improve

```php
// Solution: Make criteria more specific
$criteria = 'accuracy and completeness with concrete examples';
// Instead of: 'quality'
```

**Issue:** Too expensive

```php
// Solution: Reduce refinements or use draft profile
$loop = new ReflectionLoop(
    maxRefinements: 1,
    qualityThreshold: 6,
);
```

## Additional Resources

- [Chapter 10: Reflection and Self-Review Loops](../../chapters/10-reflection-and-self-review-loops)
- [`claude-php/agent` ReflectionLoop Documentation](https://github.com/claude-php/claude-php-agent/blob/master/docs/loop-strategies.md#3-reflectionloop)
- [ReflectionLoop Source Code](https://github.com/claude-php/claude-php-agent/blob/master/src/Loops/ReflectionLoop.php)
- [Loop Strategies Demo](https://github.com/claude-php/claude-php-agent/blob/master/examples/loop_strategies_demo.php)

---

**Next Chapter:** [Chapter 11: Multi-Stage Workflows and Agent Graphs](../../chapters/11-multi-stage-workflows-and-agent-graphs)
