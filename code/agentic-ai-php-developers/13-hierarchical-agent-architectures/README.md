# Chapter 13: Hierarchical Agent Architectures

Complete code examples for building master-worker agent systems using [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent).

## Overview

These examples demonstrate how to build production-grade hierarchical agent systems where a master agent coordinates multiple specialized worker agents to solve complex, multi-domain problems.

## Examples

### 01-basic-hierarchical-system.php
**Basic master-worker coordination**

The simplest hierarchical system with two workers:
- Math expert for calculations
- Writing expert for explanations

Shows the fundamentals:
- Creating specialized workers
- Registering workers with master
- Task decomposition and delegation
- Result synthesis

**Run**: `php 01-basic-hierarchical-system.php`

**Duration**: ~15-20 seconds  
**Cost**: ~$0.02-0.04

---

### 02-worker-specialization.php
**Building focused domain experts**

Demonstrates worker specialization patterns:
- Code analysis team (security, performance, practices, testing)
- Content creation team (research, SEO, writing)
- Using different models for different workers

Shows best practices:
- Focused vs. broad specialties
- Complementary skill teams
- Cost optimization with model selection

**Run**: `php 02-worker-specialization.php`

**Duration**: ~25-30 seconds  
**Cost**: ~$0.03-0.06

---

### 03-code-review-system.php
**Multi-specialist code review**

Production-ready code review system with:
- Security expert (vulnerabilities, injection attacks)
- Performance expert (optimization, scalability)
- Best practices expert (SOLID, design patterns)
- Test coverage expert (unit tests, edge cases)

Provides comprehensive analysis of:
- SQL injection vulnerabilities
- N+1 query problems
- Missing type hints
- Test coverage gaps

**Run**: `php 03-code-review-system.php`

**Duration**: ~20-30 seconds  
**Cost**: ~$0.04-0.07

---

### 04-content-pipeline.php
**Research, write, edit workflow**

Complete content creation pipeline:
- Researcher: Fact-gathering and verification
- SEO Expert: Keyword optimization
- Content Writer: Engaging writing
- Editor: Polish and refinement

Produces publication-ready blog posts with:
- Thorough research
- SEO optimization
- Clear structure
- Professional editing

**Run**: `php 04-content-pipeline.php`

**Duration**: ~25-35 seconds  
**Cost**: ~$0.05-0.10

**Output**: Saves blog post as markdown file

---

### 05-business-analysis-team.php
**Market, financial, competitive analysis**

Strategic business analysis system:
- Market Analyst: TAM/SAM, trends, opportunities
- Financial Analyst: Projections, ROI, risk
- Competitive Analyst: Positioning, differentiation
- Strategy Consultant: Recommendations, roadmap

Evaluates business opportunities with:
- Market opportunity assessment
- Financial modeling
- Competitive intelligence
- Strategic recommendations

**Run**: `php 05-business-analysis-team.php`

**Duration**: ~30-40 seconds  
**Cost**: ~$0.06-0.12

---

### 06-production-hierarchical-system.php
**Full system with monitoring**

Production-ready implementation with:
- Rate limiting (prevent API throttling)
- Caching (reduce duplicate work)
- Retry logic (handle transient failures)
- Result validation (ensure quality)
- Cost tracking (monitor spend)
- Performance metrics (track efficiency)

Shows production patterns:
- Wrapper classes for reliability
- Configuration management
- Comprehensive monitoring
- Error handling strategies

**Run**: `php 06-production-hierarchical-system.php`

**Duration**: ~20-30 seconds  
**Cost**: ~$0.03-0.06

---

## Requirements

- PHP 8.4+
- `claude-php/claude-php-agent` package (installed in `../00-environment-setup/`)
- `ANTHROPIC_API_KEY` environment variable

## Quick Start

```bash
# Set API key
export ANTHROPIC_API_KEY='your-key-here'

# Run basic example
php 01-basic-hierarchical-system.php

# Run code review system
php 03-code-review-system.php

# Run production system
php 06-production-hierarchical-system.php
```

## Cost Estimates

Approximate costs per execution (Sonnet 4.5 pricing):

| Example | Duration | Cost | Workers |
|---------|----------|------|---------|
| 01 - Basic System | 15-20s | $0.02-0.04 | 2 |
| 02 - Specialization | 25-30s | $0.03-0.06 | 4 |
| 03 - Code Review | 20-30s | $0.04-0.07 | 4 |
| 04 - Content Pipeline | 25-35s | $0.05-0.10 | 4 |
| 05 - Business Analysis | 30-40s | $0.06-0.12 | 4 |
| 06 - Production System | 20-30s | $0.03-0.06 | 3 |

**Cost Optimization Tips**:
- Use Haiku for simple workers (70% cost reduction)
- Enable caching for repeated tasks (90%+ savings on cache hits)
- Limit `max_tokens` per worker
- Use focused system prompts to reduce output verbosity

## Key Concepts

### Master-Worker Pattern

```
Master Agent
    ├─ Decomposes task into subtasks
    ├─ Delegates to specialized workers
    └─ Synthesizes results

Worker Agents
    ├─ Security Expert
    ├─ Performance Expert
    ├─ Quality Expert
    └─ Test Expert
```

### Three Phases

1. **Decomposition**: Master analyzes task and assigns subtasks
2. **Execution**: Workers independently process their subtasks
3. **Synthesis**: Master combines outputs into coherent answer

### When to Use

✅ **Use hierarchical agents when:**
- Task requires multiple domains of expertise
- Quality demands specialist attention
- Subtasks can be worked independently
- You need auditability of who handled what

❌ **Don't use when:**
- Task is simple enough for single agent
- Budget or latency constraints are tight
- Work must be strictly sequential
- Domain doesn't benefit from specialization

## Worker Specialization

### Good Specialty Descriptions

```php
// ✅ Good: Specific and focused
'specialty' => 'SQL injection, XSS, CSRF, and authentication vulnerabilities'

// ❌ Bad: Too broad
'specialty' => 'security'
```

### Complementary Teams

Build teams where workers complement each other:

- **Code Review**: security + performance + practices + testing
- **Content**: research + SEO + writing + editing
- **Business**: market + financial + competitive + strategy

### Model Selection

- **Sonnet 4.5**: Complex analysis, strategic thinking
- **Haiku 3.5**: Simple formatting, validation (70% cheaper)

```php
// Use Sonnet for complex work
$securityWorker = new WorkerAgent($client, [
    'model' => 'claude-sonnet-4-5',
]);

// Use Haiku for simpler tasks
$formattingWorker = new WorkerAgent($client, [
    'model' => 'claude-haiku-3-5',
]);
```

## Production Patterns

### Rate Limiting

```php
$rateLimitedAgent = new RateLimitedHierarchicalAgent(
    $master,
    maxRequestsPerMinute: 50
);
```

### Caching

```php
$cachedAgent = new CachedHierarchicalAgent(
    $master,
    ttl: 3600 // 1 hour
);
```

### Retry Logic

```php
$result = runWithRetry(
    $master,
    $task,
    maxAttempts: 3
);
```

### Validation

```php
$issues = validateResult($result, [
    'min_length' => 100,
    'required_workers' => ['security_expert'],
    'max_tokens' => 10000,
]);
```

## Common Issues

### Issue: Workers not being used

**Solution**: Make specialty descriptions more specific

```php
// Bad
'specialty' => 'programming'

// Good
'specialty' => 'Python performance optimization and profiling'
```

### Issue: High token usage

**Solutions**:
- Use Haiku for simple workers
- Reduce `max_tokens` settings
- Make system prompts more concise
- Cache common task results

### Issue: Slow execution

**Solutions**:
- Reduce number of workers
- Use faster models (Haiku)
- Optimize system prompts
- Cache decomposition patterns

### Issue: Inconsistent results

**Solutions**:
- Add result validation
- Use more specific system prompts
- Increase `max_tokens` for complex analysis
- Implement retry logic

## Testing

All examples are self-contained and can be run independently. They will:

1. ✅ Connect to Claude API
2. ✅ Execute multi-worker workflows
3. ✅ Display comprehensive results
4. ✅ Show execution metadata
5. ✅ Track cost and performance

## Monitoring

Track these metrics in production:

- **Executions**: Total volume
- **Success Rate**: Reliability percentage
- **Average Cost**: Spend per execution
- **P50/P95/P99 Latency**: Performance distribution
- **Worker Utilization**: Which workers are used most
- **Cache Hit Rate**: Caching effectiveness

## Further Reading

- [Chapter 13: Hierarchical Agent Architectures](https://github.com/dalehurley/codewithphp/blob/main/docs/series/agentic-ai-php-developers/chapters/13-hierarchical-agent-architectures.md)
- [`HierarchicalAgent` Documentation](https://github.com/claude-php/claude-php-agent/blob/main/docs/HierarchicalAgent.md)
- [`HierarchicalAgent` Tutorial](https://github.com/claude-php/claude-php-agent/blob/main/docs/tutorials/HierarchicalAgent_Tutorial.md)
- [Example Code](https://github.com/claude-php/claude-php-agent/blob/main/examples/hierarchical_agent.php)

## Next Steps

1. Run the basic example to understand the pattern
2. Experiment with different worker specialties
3. Build a custom team for your domain
4. Add production features (caching, monitoring)
5. Optimize cost based on your metrics

---

**Note**: All examples require the `claude-php/claude-php-agent` package installed in the shared vendor directory at `../00-environment-setup/vendor/`.
