# Chapter 18: Performance and Cost Optimization

This directory contains code examples demonstrating performance optimization and cost reduction strategies for production AI agents.

## Examples

### 01. Response Caching
**File:** `01-response-caching.php`

Demonstrates response caching to avoid redundant API calls. Shows significant cost and latency reductions for repeated queries.

**Key Concepts:**
- File-based caching with TTL
- Cache key generation
- Hit rate tracking
- Cost savings calculation

**Run:**
```bash
php 01-response-caching.php
```

### 02. Batch Processing
**File:** `02-batch-processing.php`

Shows concurrent batch processing using AMPHP to reduce total processing time for multiple tasks.

**Key Concepts:**
- AMPHP-based concurrency
- BatchProcessor usage
- Sequential vs parallel comparison
- Performance metrics

**Run:**
```bash
php 02-batch-processing.php
```

### 03. Model Routing
**File:** `03-model-routing.php`

Demonstrates intelligent model selection based on task complexity to optimize cost and performance.

**Key Concepts:**
- Complexity analysis
- Automatic model selection
- Haiku for simple tasks, Sonnet for complex reasoning
- Cost comparison tracking

**Run:**
```bash
php 03-model-routing.php
```

### 04. Prompt Optimization
**File:** `04-prompt-optimization.php`

Shows how optimizing prompts reduces token usage while maintaining output quality.

**Key Concepts:**
- Remove redundant phrases
- Concise instructions
- Structured output formats
- Token estimation and savings

**Run:**
```bash
php 04-prompt-optimization.php
```

### 05. Token Budgeting
**File:** `05-token-budgeting.php`

Implements comprehensive token budget tracking with alerts and enforcement.

**Key Concepts:**
- Budget setting per scope
- Usage tracking and alerts
- Budget enforcement
- Cost per request monitoring

**Run:**
```bash
php 05-token-budgeting.php
```

### 06. Context Window Management
**File:** `06-context-window-management.php`

Demonstrates efficient context window management through pruning and summarization.

**Key Concepts:**
- Message history management
- Automatic pruning
- Conversation summarization
- Token usage tracking

**Run:**
```bash
php 06-context-window-management.php
```

### 07. Production Optimization System
**File:** `07-production-optimization-system.php`

Complete production-ready optimization system combining all strategies.

**Key Concepts:**
- Integrated caching, routing, and budgeting
- Comprehensive metrics
- Production configuration
- Real-time monitoring

**Run:**
```bash
php 07-production-optimization-system.php
```

## Setup

All examples use the shared environment from chapter 00:

```bash
cd ../00-environment-setup
composer install
```

Set your API key:
```bash
export ANTHROPIC_API_KEY="your-key-here"
```

## Key Takeaways

### Performance Optimization
- **Caching**: 50%+ cost reduction for repeated queries
- **Batch Processing**: 2-3x speedup with concurrency
- **Model Routing**: 30-60% cost savings using appropriate models

### Cost Optimization
- **Prompt Optimization**: 10-30% token reduction per request
- **Token Budgeting**: Prevents cost overruns
- **Context Management**: Prevents token bloat in long conversations

### Production Patterns
- Combine multiple strategies for maximum impact
- Monitor and track all metrics
- Set and enforce budgets
- Cache aggressively for read-heavy workloads
- Use smaller models for simple tasks

## Pricing Reference (as of 2024)

**Claude 3.5 Sonnet:**
- Input: $3.00 per million tokens
- Output: $15.00 per million tokens

**Claude 3.5 Haiku:**
- Input: $0.80 per million tokens  
- Output: $4.00 per million tokens

**Haiku is 3.75x cheaper than Sonnet** — use it whenever possible!

## Testing

Run all examples in sequence to see the full optimization story:

```bash
for file in 0*.php; do
    echo "Running $file..."
    php "$file"
    echo ""
done
```

## Storage

Some examples create cache files in `storage/cache/`. This directory is created automatically.

## Related Chapters

- **Chapter 04**: Agent Configuration and Best Practices
- **Chapter 16**: Observability: Logs, Traces, and Metrics
- **Chapter 17**: Evaluation Harnesses and QA
- **Chapter 19**: Async & Concurrent Execution
