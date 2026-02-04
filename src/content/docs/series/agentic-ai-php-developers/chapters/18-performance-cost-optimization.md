---
title: "18: Performance and Cost Optimization"
description: "Implement caching, batching, and model routing. Use smaller models for sub-tasks and optimize token spend."
series: "agentic-ai-php-developers"
chapter: 18
difficulty: "Advanced"
keywords: ["PHP Performance", "Cost Optimization", "Token Optimization", "Model Routing", "Caching Strategies", "Batch Processing"]
teaches: ["Response caching strategies", "Concurrent batch processing", "Intelligent model routing", "Prompt optimization techniques", "Token budgeting and monitoring", "Context window management", "Production cost controls"]
estimatedTime: "PT120M"
---

# Chapter 18: Performance and Cost Optimization

## Overview

You've built powerful AI agents. Now it's time to make them production-efficient. **Performance and cost optimization** — the practice of reducing latency, token usage, and API costs while maintaining quality — separates expensive experiments from sustainable production systems. Without optimization, costs spiral out of control and users experience slow response times.

In this chapter, you'll learn to optimize agents using [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent)'s caching, batching, and routing capabilities. You'll implement response caching to eliminate redundant API calls, use batch processing for concurrent execution, route tasks to appropriately-sized models, optimize prompts to reduce tokens, set token budgets with enforcement, manage context windows efficiently, and build a complete production optimization system.

In this chapter you'll:

- Implement **response caching** to avoid redundant API calls and reduce costs by 50%+
- Use **batch processing** with AMPHP for concurrent task execution
- Build **intelligent model routing** to use cheaper models for simple tasks
- Apply **prompt optimization** techniques to reduce token usage by 10-30%
- Set up **token budgeting** with monitoring, alerts, and enforcement
- Manage **context windows** efficiently through pruning and summarization
- Design **production optimization systems** combining all strategies

**Estimated time:** ~120 minutes

::: info Framework Version
This chapter is based on [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent) **v0.5+**. All optimization features are built into the framework.
:::

::: info Code examples
Complete, runnable examples for this chapter:

- [`01-response-caching.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/18-performance-cost-optimization/01-response-caching.php) — Cache responses to avoid redundant API calls
- [`02-batch-processing.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/18-performance-cost-optimization/02-batch-processing.php) — Concurrent processing with BatchProcessor
- [`03-model-routing.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/18-performance-cost-optimization/03-model-routing.php) — Route tasks to appropriate models
- [`04-prompt-optimization.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/18-performance-cost-optimization/04-prompt-optimization.php) — Reduce tokens through prompt engineering
- [`05-token-budgeting.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/18-performance-cost-optimization/05-token-budgeting.php) — Track and enforce token budgets
- [`06-context-window-management.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/18-performance-cost-optimization/06-context-window-management.php) — Manage conversation history efficiently
- [`07-production-optimization-system.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/18-performance-cost-optimization/07-production-optimization-system.php) — Complete optimization stack

All files are in [`code/18-performance-cost-optimization/`](https://github.com/dalehurley/codewithphp/tree/main/code/agentic-ai-php-developers/18-performance-cost-optimization).
:::

---

## The Cost of Unoptimized Agents

Without optimization, production costs escalate rapidly:

```text
┌──────────────────────────────────────────────────────────┐
│         UNOPTIMIZED VS OPTIMIZED AGENTS                  │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  UNOPTIMIZED                  OPTIMIZED                  │
│  ───────────────────────────────────────────────────     │
│  🐌 Slow responses           ⚡ Fast with caching        │
│  💸 Every request costs      💰 50%+ cost reduction      │
│  🔄 Redundant API calls      ✅ Cached responses         │
│  🦥 Sequential processing    🚀 Concurrent batching      │
│  🐘 Expensive model always   🎯 Right-sized models       │
│  📈 Token bloat              📉 Optimized prompts        │
│  🤷 No budget control        ⚖️  Enforced limits         │
│  💵 $1,000/month            💵 $300/month                │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

### The Optimization Mindset

**Key Principle:** Every token costs money. Optimize aggressively without sacrificing quality.

Optimization strategies fall into categories:

1. **Avoid Work** — Cache responses, deduplicate requests
2. **Parallelize Work** — Batch process, concurrent execution
3. **Use Cheaper Resources** — Route to smaller models when appropriate
4. **Reduce Tokens** — Optimize prompts, manage context
5. **Set Limits** — Budget enforcement, rate limiting
6. **Monitor Everything** — Track costs, performance, usage patterns

---

## Strategy 1: Response Caching

### Why Cache Agent Responses?

Identical queries return identical answers. Cache them to avoid redundant API calls:

```text
┌──────────────────────────────────────────────────────────┐
│              CACHING IMPACT                              │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  WITHOUT CACHE:                                          │
│  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐                │
│  │ API  │  │ API  │  │ API  │  │ API  │  4 calls       │
│  │ $$$  │  │ $$$  │  │ $$$  │  │ $$$  │  ~2000ms       │
│  └──────┘  └──────┘  └──────┘  └──────┘  $0.04         │
│                                                          │
│  WITH CACHE:                                             │
│  ┌──────┐  ┌─────┐  ┌─────┐  ┌─────┐                   │
│  │ API  │  │Cache│  │Cache│  │Cache│    1 API call     │
│  │ $$$  │  │ ✓   │  │ ✓   │  │ ✓   │    ~520ms         │
│  └──────┘  └─────┘  └─────┘  └─────┘    $0.01          │
│                                                          │
│  SAVINGS: 75% cost, 74% latency reduction                │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

### Implementing Response Caching

Use the framework's built-in `CacheService`:

```php
use ClaudeAgents\Services\Cache\CacheService;
use ClaudeAgents\Services\Settings\SettingsService;

// Configure cache
$settings = new SettingsService();
$settings->set('cache.driver', 'file');
$settings->set('cache.path', './storage/cache');
$settings->set('cache.ttl', 3600); // 1 hour

$cache = new CacheService($settings);
$cache->initialize();

// Cached execution pattern
function cachedAgentRun(Agent $agent, CacheService $cache, string $query): array
{
    $cacheKey = 'agent_response:' . md5($query);
    
    // Check cache first
    if ($cached = $cache->get($cacheKey)) {
        return ['answer' => $cached['answer'], 'cached' => true];
    }
    
    // Cache miss - execute agent
    $result = $agent->run($query);
    
    // Store in cache
    $cache->set($cacheKey, [
        'answer' => $result->getAnswer(),
        'tokens' => $result->getTokenUsage(),
    ], 3600);
    
    return ['answer' => $result->getAnswer(), 'cached' => false];
}
```

### Cache Key Strategies

Different caching strategies for different use cases:

```php
// Exact match caching
$cacheKey = 'query:' . md5($query);

// Semantic similarity caching (with embeddings)
$embedding = generateEmbedding($query);
$similarKey = findSimilarCachedKey($embedding, threshold: 0.95);

// User-scoped caching
$cacheKey = "user:{$userId}:query:" . md5($query);

// Time-based invalidation
$cacheKey = "query:" . md5($query) . ":date:" . date('Y-m-d');
```

### When to Cache

✅ **Good candidates for caching:**
- FAQ responses
- Documentation lookups
- Static data queries
- Repeated user queries
- Read-heavy workloads

❌ **Bad candidates for caching:**
- Real-time data (weather, stock prices)
- User-specific sensitive data
- Time-dependent responses
- Queries with side effects

### Cache Performance Metrics

Track cache effectiveness:

```php
$metrics = [
    'total_requests' => 100,
    'cache_hits' => 75,
    'cache_misses' => 25,
    'hit_rate' => 0.75,
    'tokens_saved' => 150_000,
    'cost_saved' => 0.45,
    'latency_reduction' => 0.74,
];
```

**Real-world impact:** 50-80% cost reduction for read-heavy applications.

---

## Strategy 2: Batch Processing

### Why Batch Process?

Process multiple tasks concurrently instead of sequentially:

```text
┌──────────────────────────────────────────────────────────┐
│           SEQUENTIAL VS CONCURRENT                       │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  SEQUENTIAL (5 tasks):                                   │
│  ──Task 1──  ──Task 2──  ──Task 3──  ──Task 4──  ──5──  │
│  [========]  [========]  [========]  [========]  [====]  │
│  Time: 25 seconds                                        │
│                                                          │
│  CONCURRENT (3x concurrency):                            │
│  ──Task 1──                                              │
│  ──Task 2──  ──Task 4──                                  │
│  ──Task 3──  ──Task 5──                                  │
│  [========]  [========]                                  │
│  Time: 10 seconds (2.5x faster!)                         │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

### Using BatchProcessor

The framework includes AMPHP-powered batch processing:

```php
use ClaudeAgents\Async\BatchProcessor;

// Create batch processor
$processor = BatchProcessor::create($agent)
    ->add('task_1', 'Analyze Q1 sales data')
    ->add('task_2', 'Analyze Q2 sales data')
    ->add('task_3', 'Analyze Q3 sales data')
    ->add('task_4', 'Analyze Q4 sales data');

// Process with concurrency limit
$results = $processor->run(concurrency: 3);

// Check results
$stats = $processor->getStats();
echo "Processed {$stats['total_tasks']} tasks\n";
echo "Success rate: " . ($stats['success_rate'] * 100) . "%\n";
echo "Total tokens: {$stats['total_tokens']['total']}\n";
```

### Batch Processing Patterns

**Pattern 1: Bulk Analysis**

Process multiple datasets in parallel:

```php
$datasets = ['users', 'orders', 'products', 'revenue'];
$tasks = array_map(
    fn($ds) => "Analyze {$ds} dataset and summarize key metrics",
    $datasets
);

$processor->addMany($tasks);
$results = $processor->run(concurrency: 4);
```

**Pattern 2: Report Generation**

Generate multiple reports concurrently:

```php
$reports = [
    'executive_summary' => 'Create executive summary',
    'financial_analysis' => 'Analyze financial performance',
    'market_trends' => 'Summarize market trends',
];

$results = $processor->addMany($reports)->run(concurrency: 3);
```

**Pattern 3: Multi-Document Processing**

Process documents in parallel:

```php
$documents = glob('./documents/*.txt');
foreach ($documents as $doc) {
    $processor->add(
        basename($doc),
        "Summarize the document: " . file_get_contents($doc)
    );
}

$results = $processor->run(concurrency: 5);
```

### Concurrency Tuning

Choose concurrency based on workload:

```text
Concurrency Level    Use Case                    Cost Impact
─────────────────────────────────────────────────────────────
1 (sequential)       Rate-limited APIs           Low
3-5 (moderate)       Typical workloads           Medium
10+ (aggressive)     Bulk processing             High (watch costs!)
```

**Guidelines:**
- Start with concurrency: 3
- Monitor rate limits (Claude API allows high concurrency)
- Watch token usage and costs
- Balance speed vs cost

---

## Strategy 3: Model Routing

### Why Route to Different Models?

Claude offers models with different capabilities and costs:

```text
┌──────────────────────────────────────────────────────────┐
│              MODEL COMPARISON                            │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  Model              Speed   Cost    Best For            │
│  ───────────────────────────────────────────────────     │
│  Haiku             ⚡⚡⚡   $      Simple queries        │
│  (3.5)                           FAQs, classification   │
│                                  Data extraction        │
│                                                          │
│  Sonnet            ⚡⚡     $$$    Complex reasoning     │
│  (3.5)                           Analysis, planning     │
│                                  Multi-step tasks       │
│                                                          │
│  COST DIFFERENCE: Haiku is 3.75x cheaper than Sonnet!   │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

### Complexity Analysis

Analyze task complexity to select the right model:

```php
class TaskComplexityAnalyzer
{
    public function analyze(string $task): array
    {
        $score = 0;
        $reasons = [];
        
        // Check for complexity indicators
        if (preg_match('/\b(analyze|complex|detailed|reasoning)\b/i', $task)) {
            $score += 2;
            $reasons[] = 'Requires analysis or reasoning';
        }
        
        if (preg_match('/\b(multiple|compare|contrast)\b/i', $task)) {
            $score += 1;
            $reasons[] = 'Multiple elements to consider';
        }
        
        if (str_word_count($task) > 20) {
            $score += 1;
            $reasons[] = 'Long task description';
        }
        
        $complexity = match (true) {
            $score >= 3 => 'complex',
            $score >= 1 => 'moderate',
            default => 'simple',
        };
        
        return ['complexity' => $complexity, 'score' => $score, 'reasons' => $reasons];
    }
}
```

### Adaptive Model Router

Route tasks to appropriate models:

```php
class AdaptiveAgentRouter
{
    private array $agents = [];
    
    public function __construct(ClaudePhp $client)
    {
        // Create agents for different models
        $this->agents['haiku'] = Agent::create($client)
            ->withModel('claude-3-5-haiku-20241022');
            
        $this->agents['sonnet'] = Agent::create($client)
            ->withModel('claude-3-5-sonnet-20241022');
    }
    
    public function route(string $task): array
    {
        // Analyze complexity
        $analysis = (new TaskComplexityAnalyzer())->analyze($task);
        
        // Select model
        $model = match ($analysis['complexity']) {
            'complex' => 'sonnet',
            default => 'haiku',
        };
        
        // Execute with selected agent
        $result = $this->agents[$model]->run($task);
        
        return [
            'answer' => $result->getAnswer(),
            'model' => $model,
            'complexity' => $analysis['complexity'],
        ];
    }
}
```

### Model Routing Savings

**Example cost comparison:**

```text
Task Type         Model      Tokens   Cost      With Routing
────────────────────────────────────────────────────────────
"What is PHP?"    Haiku      1,200    $0.0029   $0.0029 ✅
"Explain OOP"     Haiku      2,500    $0.0060   $0.0060 ✅
"Analyze arch"    Sonnet     3,800    $0.0342   $0.0342 ✅
"List frameworks" Haiku      1,500    $0.0036   $0.0036 ✅

WITHOUT ROUTING: All Sonnet = $0.0809
WITH ROUTING: Mixed = $0.0467
SAVINGS: $0.0342 (42%)
```

---

## Strategy 4: Prompt Optimization

### Why Optimize Prompts?

Verbose prompts waste tokens:

```text
┌──────────────────────────────────────────────────────────┐
│           PROMPT OPTIMIZATION IMPACT                     │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  VERBOSE PROMPT (200 tokens):                            │
│  "Hello! I would like you to please help me understand   │
│   what the difference is between abstract classes and    │
│   interfaces in PHP. Could you please provide a detailed │
│   explanation with some examples? I would really         │
│   appreciate your assistance! Thank you!"                │
│                                                          │
│  OPTIMIZED PROMPT (50 tokens):                           │
│  "Explain the difference between abstract classes and    │
│   interfaces in PHP with examples."                      │
│                                                          │
│  SAVINGS: 150 tokens (75% reduction)                     │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

### Optimization Techniques

**1. Remove politeness fluff:**

```php
// ❌ Verbose
"Please could you help me understand..."

// ✅ Concise  
"Explain..."
```

**2. Use action verbs:**

```php
// ❌ Verbose
"I need you to provide information about..."

// ✅ Concise
"Describe..."
```

**3. Structured output:**

```php
// ❌ Verbose system prompt
"You are a helpful assistant. Please provide comprehensive 
answers with full sentences and detailed explanations."

// ✅ Structured system prompt
"Format responses as: ANSWER: [brief], DETAILS: [bullets], 
EXAMPLE: [code if relevant]."
```

**4. Specify constraints:**

```php
// ❌ Open-ended
"Explain machine learning"

// ✅ Constrained
"Explain machine learning in 3 sentences"
```

### Prompt Optimization Rules

✅ **Do:**
- Use imperative commands ("Explain", "List", "Analyze")
- Specify output format upfront
- Set length constraints
- Use bullet points for multi-part questions
- Keep system prompts concise

❌ **Don't:**
- Use politeness padding ("please", "could you", "I would like")
- Repeat context in every query
- Write conversational filler
- Use redundant phrasing

### Measuring Optimization Impact

```php
class PromptOptimizer
{
    public function optimize(string $prompt): array
    {
        $original = $prompt;
        $optimized = $prompt;
        
        // Remove politeness
        $optimized = preg_replace('/\bplease\b/i', '', $optimized);
        $optimized = preg_replace('/\bcould you\b/i', '', $optimized);
        
        // Simplify phrasing
        $optimized = str_replace('provide information about', 'describe', $optimized);
        $optimized = str_replace('I would like', '', $optimized);
        
        return [
            'original' => $original,
            'optimized' => trim($optimized),
            'token_reduction' => $this->estimateTokens($original) - 
                                $this->estimateTokens($optimized),
        ];
    }
    
    private function estimateTokens(string $text): int
    {
        return (int) ceil(strlen($text) / 4); // Rough estimate
    }
}
```

---

## Strategy 5: Token Budgeting

### Why Set Token Budgets?

Prevent cost overruns with enforced budgets:

```text
┌──────────────────────────────────────────────────────────┐
│              TOKEN BUDGET FLOW                           │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  1. SET BUDGET                                           │
│     Daily: 1M tokens, $5.00                              │
│                                                          │
│  2. TRACK USAGE                                          │
│     Request 1: 2.5K tokens → 0.25% used                  │
│     Request 2: 3.8K tokens → 0.63% used                  │
│     ...                                                  │
│                                                          │
│  3. ALERT AT THRESHOLDS                                  │
│     75% → ⚡ Notice                                       │
│     90% → ⚠️  Warning                                     │
│     100% → 🚨 Block requests                             │
│                                                          │
│  4. ENFORCE LIMITS                                       │
│     Over budget → Reject request                         │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

### Token Budget Manager

```php
class TokenBudgetManager
{
    private array $budgets = [];
    
    // Pricing per million tokens
    private array $pricing = [
        'claude-3-5-sonnet-20241022' => ['input' => 3.00, 'output' => 15.00],
        'claude-3-5-haiku-20241022' => ['input' => 0.80, 'output' => 4.00],
    ];
    
    public function setBudget(string $scope, int $tokenLimit, float $costLimit): void
    {
        $this->budgets[$scope] = [
            'token_limit' => $tokenLimit,
            'cost_limit' => $costLimit,
            'tokens_used' => 0,
            'cost_incurred' => 0,
        ];
    }
    
    public function recordUsage(
        string $scope,
        string $model,
        int $inputTokens,
        int $outputTokens
    ): array {
        $cost = $this->calculateCost($model, $inputTokens, $outputTokens);
        $totalTokens = $inputTokens + $outputTokens;
        
        $this->budgets[$scope]['tokens_used'] += $totalTokens;
        $this->budgets[$scope]['cost_incurred'] += $cost;
        
        return $this->checkThresholds($scope);
    }
    
    private function checkThresholds(string $scope): array
    {
        $budget = $this->budgets[$scope];
        $tokenPercent = ($budget['tokens_used'] / $budget['token_limit']) * 100;
        $costPercent = ($budget['cost_incurred'] / $budget['cost_limit']) * 100;
        
        $alerts = [];
        
        if ($tokenPercent >= 100) {
            $alerts[] = "🚨 TOKEN LIMIT EXCEEDED for {$scope}!";
        } elseif ($tokenPercent >= 90) {
            $alerts[] = "⚠️  TOKEN WARNING: {$scope} at {$tokenPercent}%";
        }
        
        return ['within_budget' => $tokenPercent < 100, 'alerts' => $alerts];
    }
}
```

### Budget Scopes

Set budgets at different levels:

```php
// Per-user daily budget
$budgetManager->setBudget("user:{$userId}:daily", 50_000, 0.15);

// Per-feature budget  
$budgetManager->setBudget("feature:chat", 1_000_000, 3.00);

// Organization-wide budget
$budgetManager->setBudget("org:acme:monthly", 10_000_000, 30.00);
```

---

## Strategy 6: Context Window Management

### Why Manage Context Windows?

Long conversations consume excessive tokens:

```text
┌──────────────────────────────────────────────────────────┐
│         CONTEXT WINDOW GROWTH                            │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  Turn 1:  System + User + Assistant = 500 tokens        │
│  Turn 2:  +500 tokens = 1,000 tokens                     │
│  Turn 3:  +500 tokens = 1,500 tokens                     │
│  Turn 10: +4,500 tokens = 5,000 tokens                   │
│  Turn 20: +9,500 tokens = 10,000 tokens                  │
│                                                          │
│  WITHOUT MANAGEMENT: Token usage grows linearly!         │
│                                                          │
│  WITH MANAGEMENT:                                        │
│  - Prune old messages                                    │
│  - Summarize conversation                                │
│  - Keep only recent context                              │
│  Turn 20: ~2,000 tokens (80% savings!)                   │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

### Context Window Manager

```php
class ContextWindowManager
{
    private array $history = [];
    private int $maxTokens;
    
    public function addMessage(string $role, string $content): void
    {
        $tokens = $this->estimateTokens($content);
        
        $this->history[] = [
            'role' => $role,
            'content' => $content,
            'tokens' => $tokens,
        ];
        
        // Prune if over limit
        if ($this->getTotalTokens() > $this->maxTokens) {
            $this->prune();
        }
    }
    
    private function prune(): void
    {
        // Keep system message and last N exchanges
        $keepRecent = 5;
        $recentMessages = array_slice($this->history, -$keepRecent);
        
        $this->history = $recentMessages;
    }
    
    public function compactWithSummary(ClaudePhp $client): void
    {
        // Generate summary of old messages
        $summary = $this->generateSummary($client);
        
        // Replace old messages with summary
        $this->history = [
            $this->history[0], // System message
            ['role' => 'assistant', 'content' => "Previous: {$summary}"],
            ...array_slice($this->history, -4), // Recent messages
        ];
    }
}
```

### Context Management Strategies

**1. Pruning** — Remove old messages

```php
// Keep only last N exchanges
$recentHistory = array_slice($history, -10);
```

**2. Summarization** — Condense old messages

```php
// Summarize conversation so far
$summary = $summaryAgent->run($conversationHistory);
$history = [systemMessage, summaryMessage, ...recentMessages];
```

**3. Sliding Window** — Fixed-size context

```php
// Always maintain exactly N messages
if (count($history) > $maxMessages) {
    array_shift($history);
}
```

---

## Production Optimization System

### Comprehensive Optimizer

Combine all strategies:

```php
class ProductionOptimizer
{
    private CacheService $cache;
    private array $metrics = [];
    
    public function __construct(
        private ClaudePhp $client,
        array $config = []
    ) {
        $this->config = array_merge([
            'cache_enabled' => true,
            'model_routing_enabled' => true,
            'max_tokens_per_request' => 4000,
            'daily_token_budget' => 1_000_000,
        ], $config);
        
        // Initialize cache
        $this->cache = new CacheService(new SettingsService());
        $this->cache->initialize();
    }
    
    public function execute(string $query, array $options = []): array
    {
        // 1. Check cache
        if ($this->config['cache_enabled']) {
            $cached = $this->checkCache($query);
            if ($cached !== null) {
                return $this->formatResponse($cached, cached: true);
            }
        }
        
        // 2. Select model
        $model = $this->selectModel($query, $options);
        
        // 3. Check budget
        if (!$this->checkBudget()) {
            return ['success' => false, 'error' => 'Budget exceeded'];
        }
        
        // 4. Execute request
        $result = $this->executeRequest($query, $model);
        
        // 5. Cache result
        if ($this->config['cache_enabled'] && $result['success']) {
            $this->cacheResult($query, $result);
        }
        
        // 6. Record metrics
        $this->recordMetric($model, $result['tokens'] ?? 0);
        
        return $result;
    }
}
```

---

## Cost Optimization Checklist

Before deploying to production:

### ✅ Caching
- [ ] Response caching implemented
- [ ] Cache keys properly scoped
- [ ] TTL set appropriately
- [ ] Cache hit rate monitored

### ✅ Batching
- [ ] Batch processing for bulk operations
- [ ] Concurrency level tuned
- [ ] Error handling for failed tasks
- [ ] Stats tracked per batch

### ✅ Model Routing
- [ ] Complexity analysis implemented
- [ ] Haiku used for simple tasks
- [ ] Sonnet reserved for complex reasoning
- [ ] Routing decisions logged

### ✅ Prompt Optimization
- [ ] Prompts reviewed and shortened
- [ ] Politeness fluff removed
- [ ] Structured output formats used
- [ ] Token usage tracked

### ✅ Token Budgeting
- [ ] Budgets set per scope
- [ ] Usage tracked in real-time
- [ ] Alerts configured (75%, 90%)
- [ ] Enforcement enabled at 100%

### ✅ Context Management
- [ ] Context window limits set
- [ ] Pruning or summarization enabled
- [ ] Token growth monitored
- [ ] History retention tuned

### ✅ Monitoring
- [ ] Cost per request tracked
- [ ] Daily/monthly spend dashboards
- [ ] Alert thresholds configured
- [ ] Optimization metrics reviewed weekly

---

## Real-World Optimization Impact

**Case Study: Customer Support Bot**

```text
Before Optimization:
├─ 10,000 requests/day
├─ 50M tokens/day
├─ $450/day
└─ Avg response time: 2.5s

After Optimization:
├─ 10,000 requests/day (same volume)
├─ 15M tokens/day (70% reduction)
├─ $135/day (70% cost reduction)
└─ Avg response time: 0.8s (68% faster)

Strategies Applied:
✅ Response caching (60% hit rate)
✅ Model routing (80% Haiku, 20% Sonnet)
✅ Prompt optimization (25% token reduction)
✅ Context pruning (prevents bloat)

Annual Savings: $115,000
```

---

## Key Takeaways

1. **Cache Aggressively** — 50%+ cost reduction for read-heavy workloads
2. **Batch Processing** — 2-3x speedup with concurrency
3. **Route Intelligently** — Use Haiku for simple tasks, Sonnet for complex reasoning
4. **Optimize Prompts** — Remove fluff, reduce tokens by 10-30%
5. **Set Budgets** — Prevent cost overruns with enforced limits
6. **Manage Context** — Prevent token bloat in long conversations
7. **Monitor Everything** — Track costs, tokens, hit rates, latency

**Golden Rule:** Every optimization compounds. Combine strategies for maximum impact.

---

## Exercises

### Exercise 1: Implement Caching

Add response caching to an existing agent with TTL and hit rate tracking.

### Exercise 2: Batch Process Reports

Create a batch processor that generates 10 reports concurrently with proper error handling.

### Exercise 3: Build Model Router

Implement a complexity analyzer and router that selects Haiku or Sonnet based on task analysis.

### Exercise 4: Optimize Prompts

Take 5 verbose prompts and optimize them. Measure token reduction.

### Exercise 5: Set Token Budgets

Create a budget manager with daily limits, alerts at 75%/90%, and enforcement at 100%.

---

## What's Next?

In **[Chapter 19: Async & Concurrent Execution](/series/agentic-ai-php-developers/chapters/19-async-concurrent-execution)**, you'll dive deeper into AMPHP-powered asynchronous patterns, parallel tool execution, promise-based workflows, and advanced concurrency strategies.

**Up next:** Async & concurrent execution with AMPHP →
