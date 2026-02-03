# Chapter 15: Adaptive Agent Selection

Complete code examples for intelligent agent selection, validation, and adaptation using the `AdaptiveAgentService`.

## Examples Overview

### 01: Basic Adaptive Selection
[`01-basic-adaptive-selection.php`](./01-basic-adaptive-selection.php)

**What it demonstrates:**
- Simple setup of AdaptiveAgentService
- Automatic agent selection based on task type
- Basic quality validation

**Key concepts:**
- Agent registration with profiles
- Automatic task→agent matching
- Quality scoring

**Run it:**
```bash
php 01-basic-adaptive-selection.php
```

---

### 02: Agent Profiles and Registration
[`02-agent-profiles-and-registration.php`](./02-agent-profiles-and-registration.php)

**What it demonstrates:**
- Comprehensive agent profile design
- Registration of diverse specialized agents
- How profiles influence selection

**Key concepts:**
- Profile fields (type, strengths, complexity, quality)
- Differentiation between agent capabilities
- Best practices for profile design

**Run it:**
```bash
php 02-agent-profiles-and-registration.php
```

---

### 03: Task Analysis and Matching
[`03-task-analysis-and-matching.php`](./03-task-analysis-and-matching.php)

**What it demonstrates:**
- How task analysis works under the hood
- The matching algorithm between tasks and agents
- Getting recommendations without execution

**Key concepts:**
- Task complexity classification
- Domain detection
- Requirement analysis
- Scoring and selection logic

**Run it:**
```bash
php 03-task-analysis-and-matching.php
```

---

### 04: Result Validation and Adaptation
[`04-result-validation-adaptation.php`](./04-result-validation-adaptation.php)

**What it demonstrates:**
- How result validation works
- Quality scoring criteria
- Adaptive retry with different agents
- Request reframing on failure

**Key concepts:**
- Validation criteria (correctness, completeness, clarity, relevance)
- Quality thresholds
- Adaptation strategies
- Reframing logic

**Run it:**
```bash
php 04-result-validation-adaptation.php
```

---

### 05: k-NN Learning System
[`05-knn-learning-system.php`](./05-knn-learning-system.php)

**What it demonstrates:**
- k-NN (k-Nearest Neighbors) machine learning
- Learning from execution history
- Confidence improvement over time
- Adaptive quality thresholds

**Key concepts:**
- Cold start vs mature learning
- Feature vector embedding (14D)
- Historical task matching
- Confidence growth

**Run it:**
```bash
php 05-knn-learning-system.php
```

**Note:** This example builds learning history incrementally, so run it multiple times to see learning improvement!

---

### 06: Performance Monitoring
[`06-performance-monitoring.php`](./06-performance-monitoring.php)

**What it demonstrates:**
- Performance tracking per agent
- Success rates and quality metrics
- Identifying best and worst performers
- Using metrics to optimize

**Key concepts:**
- Performance metrics
- Success rate calculation
- Quality vs speed tradeoffs
- Agent fleet optimization

**Run it:**
```bash
php 06-performance-monitoring.php
```

---

### 07: Production Adaptive System
[`07-production-adaptive-system.php`](./07-production-adaptive-system.php)

**What it demonstrates:**
- Complete production-ready system
- Multi-agent fleet with diverse specializations
- Comprehensive logging and monitoring
- Error handling and reporting

**Key concepts:**
- Production configuration
- Logging integration (Monolog)
- Error handling
- Metrics export
- Production deployment patterns

**Run it:**
```bash
php 07-production-adaptive-system.php
```

---

## Prerequisites

Before running these examples, ensure you have:

1. **PHP 8.4+**
2. **Composer dependencies installed:**
   ```bash
   cd /path/to/PHP-From-Scratch
   composer install
   ```
3. **Anthropic API key:**
   ```bash
   export ANTHROPIC_API_KEY="your-api-key-here"
   ```

## Storage Directory

Examples will create a `storage/` directory for:
- k-NN learning history (JSON files)
- Performance reports
- Logs

This directory is automatically created when needed.

## Learning Path

**Recommended order:**

1. Start with `01-basic-adaptive-selection.php` to understand the fundamentals
2. Progress through `02-agent-profiles-and-registration.php` to learn profile design
3. Explore `03-task-analysis-and-matching.php` to see selection algorithms
4. Dive into `04-result-validation-adaptation.php` for quality validation
5. Run `05-knn-learning-system.php` multiple times to watch learning improve
6. Use `06-performance-monitoring.php` to understand metrics
7. Study `07-production-adaptive-system.php` as a complete reference

## Key Concepts

### Agent Profiles

Each agent is registered with a profile describing:
- **Type:** Agent category (react, reflection, cot, rag, etc.)
- **Strengths:** What the agent excels at
- **Best for:** Use case descriptions
- **Complexity level:** simple, medium, complex, extreme
- **Speed:** fast, medium, slow
- **Quality:** standard, high, extreme

### Task Analysis

The service analyzes tasks to determine:
- Complexity level
- Domain (technical, creative, analytical, etc.)
- Requirements (tools, knowledge, reasoning, iteration)
- Quality needs
- Estimated steps

### Selection Methods

1. **k-NN (Primary):** Find similar historical tasks, select agent that performed best
2. **Rule-based (Fallback):** Score agents by matching profiles to task requirements

### Validation

Every result is validated on:
- **Correctness:** Does it answer correctly?
- **Completeness:** Is it complete?
- **Clarity:** Is it well-structured?
- **Relevance:** Is it relevant?

Quality score: 0-10

### Adaptation

If quality < threshold:
1. Try different agent
2. Reframe request (make it clearer)
3. Record for learning

## Performance Tips

### Optimize for Quality
```php
$service = new AdaptiveAgentService($client, [
    'quality_threshold' => 8.0,  // Higher quality
    'max_attempts' => 5,          // More retries
]);
```

### Optimize for Speed
```php
$service = new AdaptiveAgentService($client, [
    'quality_threshold' => 6.5,  // Lower threshold
    'max_attempts' => 2,          // Fewer retries
    'enable_reframing' => false,  // Skip reframing
]);
```

### Balance Both
```php
$service = new AdaptiveAgentService($client, [
    'quality_threshold' => 7.0,  // Balanced
    'max_attempts' => 3,
    'enable_reframing' => true,
    'enable_knn' => true,         // Learn over time
]);
```

## Common Patterns

### Customer Support Router
```php
// Register fast FAQ agent, conversational dialog agent, technical support agent
// Service automatically routes based on query complexity
```

### Content Pipeline
```php
// Register standard draft agent, high-quality editor, premium maker agent
// Service selects based on content criticality
```

### Code Review System
```php
// Register syntax checker, logic analyzer, security auditor
// Service picks appropriate depth based on task
```

## Troubleshooting

### Always selects same agent?
- Check that agent profiles are differentiated
- Verify `complexity_level` and `quality` vary between agents

### Quality validation too strict?
- Lower `quality_threshold` (try 6.5 or 7.0)
- Ensure agent capabilities match threshold

### k-NN not improving?
- Run more diverse tasks to build history
- Check that history file has write permissions
- Verify `enable_knn => true`

### Slow performance?
- Reduce `max_attempts`
- Disable reframing if not needed
- Use faster agents for simple tasks

## Additional Resources

- [AdaptiveAgentService Documentation](https://github.com/claude-php/claude-php-agent/blob/main/docs/adaptive-agent-service.md)
- [k-NN Learning Guide](https://github.com/claude-php/claude-php-agent/blob/main/docs/knn-learning.md)
- [Agent Selection Guide](https://github.com/claude-php/claude-php-agent/blob/main/docs/agent-selection-guide.md)

## Questions?

If you encounter issues or have questions about these examples, please:
1. Check the tutorial chapter for detailed explanations
2. Review the claude-php-agent documentation
3. Examine the logged output in `storage/adaptive.log`
