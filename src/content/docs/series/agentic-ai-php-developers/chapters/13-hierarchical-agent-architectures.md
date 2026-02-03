---
title: "13: Hierarchical Agent Architectures"
description: "Build master-worker patterns using claude-php-agent's HierarchicalAgent. Define responsibilities and handoff rules."
series: "agentic-ai-php-developers"
chapter: 13
difficulty: "Advanced"
---

# Chapter 13: Hierarchical Agent Architectures

## Overview

Some problems are too complex for a single agent. A customer support request might require database queries, API calls, email composition, and policy validation — each demanding different expertise. **Hierarchical agent architectures** solve this by introducing specialization: a **master agent** coordinates multiple **worker agents**, each focused on a specific domain.

This chapter shows you how to build production-grade hierarchical systems using [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent)'s `HierarchicalAgent` and `WorkerAgent` classes. You'll learn task decomposition, worker coordination, result synthesis, and patterns for real-world multi-agent orchestration.

In this chapter you'll:

- Understand the **master-worker pattern** and when to use hierarchical architectures
- Build **specialized worker agents** with focused expertise and clear responsibilities
- Implement **task decomposition** where masters intelligently delegate subtasks
- Create **result synthesis** that combines worker outputs into cohesive answers
- Design **multi-domain systems** like code review pipelines and content workflows
- Optimize **performance, cost, and reliability** in hierarchical agent systems

**Estimated time:** ~90 minutes

::: info Code examples
Complete, runnable examples for this chapter:

- [`01-basic-hierarchical-system.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/13-hierarchical-agent-architectures/01-basic-hierarchical-system.php) — Simple master-worker coordination
- [`02-worker-specialization.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/13-hierarchical-agent-architectures/02-worker-specialization.php) — Building focused domain experts
- [`03-code-review-system.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/13-hierarchical-agent-architectures/03-code-review-system.php) — Multi-specialist code review
- [`04-content-pipeline.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/13-hierarchical-agent-architectures/04-content-pipeline.php) — Research, write, edit workflow
- [`05-business-analysis-team.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/13-hierarchical-agent-architectures/05-business-analysis-team.php) — Market, financial, competitive analysis
- [`06-production-hierarchical-system.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/13-hierarchical-agent-architectures/06-production-hierarchical-system.php) — Full system with monitoring

All files are in [`code/13-hierarchical-agent-architectures/`](https://github.com/dalehurley/codewithphp/tree/main/code/agentic-ai-php-developers/13-hierarchical-agent-architectures).
:::

---

## Understanding Hierarchical Architectures

### The Master-Worker Pattern

A **hierarchical agent system** consists of:

1. **Master Agent**: Coordinates the system, decomposes tasks, and synthesizes results
2. **Worker Agents**: Specialized experts that handle specific subtask domains

```text
┌─────────────────────────────────────┐
│       Master Agent                  │
│  (HierarchicalAgent)                │
│                                     │
│  1. Decomposes task                 │
│  2. Delegates to workers            │
│  3. Synthesizes results             │
└──────────┬──────────────────────────┘
           │
           │ Coordinates
           ▼
┌──────────────────────────────────────┐
│         Worker Agents                │
├──────────────────────────────────────┤
│  • Math Agent                        │
│  • Writing Agent                     │
│  • Research Agent                    │
│  • Code Agent                        │
│  • ... (custom workers)              │
└──────────────────────────────────────┘
```

### The Three Phases

Every hierarchical execution follows this pattern:

```text
┌─────────────────────────────────────────────────────┐
│ Phase 1: Decomposition                              │
│                                                     │
│ Master analyzes task → identifies subtasks         │
│ → assigns each to appropriate worker               │
└─────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────┐
│ Phase 2: Execution                                  │
│                                                     │
│ Each worker processes its subtask independently    │
│ → returns specialized result                       │
└─────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────┐
│ Phase 3: Synthesis                                  │
│                                                     │
│ Master combines all worker outputs → coherent      │
│ final answer                                       │
└─────────────────────────────────────────────────────┘
```

### When to Use Hierarchical Architectures

✅ **Use hierarchical agents when:**

- Task requires **multiple domains of expertise** (math + writing, code + docs)
- **Quality matters** and specialists outperform generalists
- Subtasks can be **worked on independently** (parallelizable)
- You need **auditability** of which expert handled each part

❌ **Don't use when:**

- Task is **simple** enough for a single agent
- Work must be **strictly sequential** (use Chain of Thought)
- **Low latency** is critical (multiple agents take time)
- **Budget is tight** (N workers = N API calls)

### Comparison with Other Patterns

| Pattern | Best For | Complexity | Cost | Latency |
|---------|----------|------------|------|---------|
| **Hierarchical** | Multi-domain problems | High | High | High |
| **ReAct** | Tool-driven tasks | Medium | Low | Medium |
| **PlanExecute** | Sequential workflows | Medium | Medium | Medium |
| **Reflection** | Quality refinement | Low | Medium | Medium |

---

## Building Your First Hierarchical System

Let's build a simple system with two workers: a math specialist and a writing specialist.

### Step 1: Create Worker Agents

Workers are specialized agents with clear domains:

```php
use ClaudeAgents\Agents\WorkerAgent;
use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Math specialist
$mathWorker = new WorkerAgent($client, [
    'name' => 'math_expert',
    'specialty' => 'mathematical calculations, statistics, and numerical analysis',
    'system' => 'You are a mathematics expert. Provide precise calculations with clear explanations.',
]);

// Writing specialist
$writerWorker = new WorkerAgent($client, [
    'name' => 'writer_expert',
    'specialty' => 'clear and engaging writing',
    'system' => 'You are a professional writer. Create clear, engaging content that explains complex topics simply.',
]);
```

**Key configuration options:**

- `name`: Unique identifier for the worker
- `specialty`: Description used by master for delegation decisions
- `system`: System prompt that defines the worker's behavior
- `model`: Optional, defaults to `claude-sonnet-4-5`
- `max_tokens`: Optional, defaults to 2048

### Step 2: Create the Master Agent

The master coordinates workers:

```php
use ClaudeAgents\Agents\HierarchicalAgent;

$master = new HierarchicalAgent($client, [
    'name' => 'master_coordinator',
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 2048,
]);
```

### Step 3: Register Workers

Tell the master about available specialists:

```php
$master->registerWorker('math_expert', $mathWorker);
$master->registerWorker('writer_expert', $writerWorker);

// Verify registration
echo "Registered workers:\n";
foreach ($master->getWorkerNames() as $name) {
    $worker = $master->getWorker($name);
    echo "  • {$name}: {$worker->getSpecialty()}\n";
}
```

### Step 4: Execute a Task

Give the master a task that requires both specialists:

```php
$task = "Calculate the average of 45, 67, 89, and 123, then explain in simple terms what an average represents and why it's useful.";

$result = $master->run($task);

if ($result->isSuccess()) {
    echo "Final Answer:\n";
    echo $result->getAnswer() . "\n\n";
    
    // Inspect metadata
    $metadata = $result->getMetadata();
    echo "Execution Details:\n";
    echo "  • Iterations: {$result->getIterations()}\n";
    echo "  • Subtasks: {$metadata['subtasks']}\n";
    echo "  • Workers used: " . implode(', ', $metadata['workers_used']) . "\n";
    echo "  • Duration: {$metadata['duration_seconds']} seconds\n";
    echo "  • Total tokens: {$metadata['token_usage']['total']}\n";
}
```

### What Happened?

Behind the scenes, the system executed:

1. **Decomposition**: Master identified two subtasks:
   - `math_expert`: Calculate 45 + 67 + 89 + 123 and divide by 4
   - `writer_expert`: Explain what averages are and why they're useful

2. **Execution**: Each worker completed their subtask independently:
   - Math expert: "The average is 81"
   - Writer expert: "An average represents the central tendency..."

3. **Synthesis**: Master combined results:
   - "The average of 45, 67, 89, and 123 is 81. An average represents..."

---

## Worker Specialization Patterns

### Creating Focused Specialists

Each worker should have a **narrow, well-defined domain**:

```php
// ✅ Good: Specific expertise
$securityWorker = new WorkerAgent($client, [
    'specialty' => 'security vulnerabilities, SQL injection, XSS, and authentication flaws',
    'system' => 'You are a security expert. Review code for vulnerabilities like SQL injection, XSS, CSRF, and authentication issues. Provide specific fixes.',
]);

// ❌ Bad: Too broad
$generalWorker = new WorkerAgent($client, [
    'specialty' => 'programming',
    'system' => 'You are a programmer.',
]);
```

### Multi-Domain Worker Teams

Build teams with **complementary specialties**:

```php
// Code review team
$securityWorker = new WorkerAgent($client, [
    'specialty' => 'security vulnerabilities and secure coding',
    'system' => 'Review code for security issues. Identify vulnerabilities and suggest fixes.',
]);

$performanceWorker = new WorkerAgent($client, [
    'specialty' => 'performance optimization, algorithms, and scalability',
    'system' => 'Identify performance bottlenecks, inefficient algorithms, and scalability issues.',
]);

$practicesWorker = new WorkerAgent($client, [
    'specialty' => 'coding standards, design patterns, and maintainability',
    'system' => 'Review for clean code, SOLID principles, design patterns, and maintainability.',
]);

$testWorker = new WorkerAgent($client, [
    'specialty' => 'unit testing, integration testing, and test coverage',
    'system' => 'Suggest test cases, identify untested paths, and recommend testing strategies.',
]);

$codeReviewer = new HierarchicalAgent($client);
$codeReviewer->registerWorker('security_expert', $securityWorker);
$codeReviewer->registerWorker('performance_expert', $performanceWorker);
$codeReviewer->registerWorker('practices_expert', $practicesWorker);
$codeReviewer->registerWorker('test_expert', $testWorker);

// Now this team can provide comprehensive code reviews
$result = $codeReviewer->run("Review this PHP function for all issues: " . $code);
```

### Using Different Models

Optimize cost by using different models for different workers:

```php
// Master uses Sonnet for smart decomposition
$master = new HierarchicalAgent($client, [
    'model' => 'claude-sonnet-4-5',
]);

// Simple workers use Haiku (faster, cheaper)
$simpleWorker = new WorkerAgent($client, [
    'model' => 'claude-haiku-3-5',
    'max_tokens' => 1024,
    'specialty' => 'simple data formatting',
]);

// Complex workers use Sonnet
$complexWorker = new WorkerAgent($client, [
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 3000,
    'specialty' => 'complex algorithm design',
]);
```

---

## Real-World Hierarchical Systems

### Example 1: Code Review Pipeline

Build a comprehensive code review system with multiple specialists:

```php
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Security specialist
$securityWorker = new WorkerAgent($client, [
    'name' => 'security_expert',
    'specialty' => 'security vulnerabilities, injection attacks, and secure coding',
    'system' => 'You are a security expert. Review code for vulnerabilities like SQL injection, XSS, CSRF, authentication issues, and data exposure. Provide specific fixes with code examples.',
]);

// Performance specialist
$performanceWorker = new WorkerAgent($client, [
    'name' => 'performance_expert',
    'specialty' => 'performance optimization, algorithms, and scalability',
    'system' => 'You are a performance expert. Identify bottlenecks, inefficient algorithms, N+1 queries, memory issues, and scalability problems. Suggest concrete optimizations.',
]);

// Best practices specialist
$practicesWorker = new WorkerAgent($client, [
    'name' => 'practices_expert',
    'specialty' => 'coding standards, design patterns, and maintainability',
    'system' => 'You are a code quality expert. Review for SOLID principles, design patterns, PSR standards, naming conventions, and long-term maintainability.',
]);

// Test coverage specialist
$testWorker = new WorkerAgent($client, [
    'name' => 'test_expert',
    'specialty' => 'unit testing, integration testing, and test coverage',
    'system' => 'You are a testing expert. Suggest test cases, identify untested edge cases, recommend testing strategies, and evaluate test quality.',
]);

// Create master
$codeReviewer = new HierarchicalAgent($client, [
    'name' => 'code_review_master',
]);

$codeReviewer->registerWorker('security_expert', $securityWorker);
$codeReviewer->registerWorker('performance_expert', $performanceWorker);
$codeReviewer->registerWorker('practices_expert', $practicesWorker);
$codeReviewer->registerWorker('test_expert', $testWorker);

// Review code
$code = file_get_contents('UserRepository.php');
$result = $codeReviewer->run(
    "Provide a comprehensive code review covering security, performance, best practices, and testing:\n\n{$code}"
);

if ($result->isSuccess()) {
    echo "CODE REVIEW REPORT\n";
    echo str_repeat("=", 80) . "\n\n";
    echo $result->getAnswer() . "\n\n";
    
    // Track which specialists provided feedback
    $metadata = $result->getMetadata();
    echo "Review by: " . implode(', ', $metadata['workers_used']) . "\n";
    echo "Total cost: $" . estimateCost($metadata['token_usage']['total']) . "\n";
}
```

### Example 2: Content Creation Pipeline

Build an editorial workflow with research, SEO, writing, and editing:

```php
// Research specialist
$researcher = new WorkerAgent($client, [
    'name' => 'researcher',
    'specialty' => 'topic research, fact-checking, and source verification',
    'system' => 'You are a research analyst. Research topics thoroughly, find key facts and statistics, identify expert opinions, and note recent developments. Focus on accuracy.',
]);

// SEO specialist
$seoExpert = new WorkerAgent($client, [
    'name' => 'seo_expert',
    'specialty' => 'SEO optimization, keywords, and search rankings',
    'system' => 'You are an SEO expert. Identify target keywords, suggest meta descriptions, recommend content structure, and optimize for search engine rankings.',
]);

// Content writer
$writer = new WorkerAgent($client, [
    'name' => 'content_writer',
    'specialty' => 'engaging writing, storytelling, and audience connection',
    'system' => 'You are a professional content writer. Write compelling, engaging content with clear structure, storytelling elements, and emotional connection. Write for your target audience.',
]);

// Editor
$editor = new WorkerAgent($client, [
    'name' => 'editor',
    'specialty' => 'editing, proofreading, grammar, and style',
    'system' => 'You are a professional editor. Edit for clarity, grammar, flow, and style. Ensure consistent tone, fix errors, and improve readability. Be concise.',
]);

$contentMaster = new HierarchicalAgent($client, [
    'name' => 'content_pipeline',
]);

$contentMaster->registerWorker('researcher', $researcher);
$contentMaster->registerWorker('seo_expert', $seoExpert);
$contentMaster->registerWorker('content_writer', $writer);
$contentMaster->registerWorker('editor', $editor);

// Create blog post
$topic = "Best Practices for Remote Team Management";
$audience = "startup founders and engineering managers";

$result = $contentMaster->run(
    "Create a comprehensive 1000-word blog post about '{$topic}' " .
    "targeted at {$audience}. Include thorough research, SEO optimization, " .
    "engaging writing, and professional editing."
);

if ($result->isSuccess()) {
    $post = $result->getAnswer();
    
    // Save to file
    file_put_contents("blog_posts/{$slug}.md", $post);
    
    // Log execution details
    $metadata = $result->getMetadata();
    echo "Blog post created!\n";
    echo "  • Workers: " . implode(', ', $metadata['workers_used']) . "\n";
    echo "  • Duration: {$metadata['duration_seconds']}s\n";
    echo "  • Tokens: {$metadata['token_usage']['total']}\n";
}
```

### Example 3: Business Analysis Team

Build a strategic analysis system with market, financial, and competitive experts:

```php
// Market analyst
$marketAnalyst = new WorkerAgent($client, [
    'name' => 'market_analyst',
    'specialty' => 'market analysis, trends, and consumer behavior',
    'system' => 'You are a market analyst. Analyze market trends, consumer behavior, industry dynamics, and growth opportunities. Provide data-driven insights.',
]);

// Financial analyst
$financialAnalyst = new WorkerAgent($client, [
    'name' => 'financial_analyst',
    'specialty' => 'financial analysis, projections, and risk assessment',
    'system' => 'You are a financial analyst. Analyze financial data, create projections, assess risks, calculate ROI and NPV. Show all calculations clearly.',
]);

// Competitive analyst
$competitiveAnalyst = new WorkerAgent($client, [
    'name' => 'competitive_analyst',
    'specialty' => 'competitive intelligence and positioning',
    'system' => 'You are a competitive analyst. Analyze competitors, identify differentiators, assess market positioning, and recommend competitive strategies.',
]);

// Strategy consultant
$strategist = new WorkerAgent($client, [
    'name' => 'strategist',
    'specialty' => 'business strategy and recommendations',
    'system' => 'You are a strategy consultant. Synthesize analysis into actionable strategy. Provide clear, prioritized recommendations with implementation roadmaps.',
]);

$businessMaster = new HierarchicalAgent($client, [
    'name' => 'business_strategist',
]);

$businessMaster->registerWorker('market_analyst', $marketAnalyst);
$businessMaster->registerWorker('financial_analyst', $financialAnalyst);
$businessMaster->registerWorker('competitive_analyst', $competitiveAnalyst);
$businessMaster->registerWorker('strategist', $strategist);

// Analyze business opportunity
$scenario = [
    'question' => 'Should we expand our SaaS product into the healthcare market?',
    'current_arr' => '$2M',
    'current_customers' => 150,
    'current_sector' => 'finance',
    'target_sector' => 'healthcare',
    'competitors' => ['Epic ($3B)', 'Cerner ($1.5B)', 'Athenahealth ($1B)'],
    'expansion_cost' => '$500K',
    'timeline' => '12 months',
];

$result = $businessMaster->run(
    "{$scenario['question']}\n\n" .
    "Context:\n" .
    "- Current ARR: {$scenario['current_arr']}\n" .
    "- Current customers: {$scenario['current_customers']} in {$scenario['current_sector']}\n" .
    "- Target competitors: " . implode(', ', $scenario['competitors']) . "\n" .
    "- Estimated expansion cost: {$scenario['expansion_cost']}\n" .
    "- Timeline: {$scenario['timeline']}\n\n" .
    "Provide comprehensive analysis covering market opportunity, financial projections, " .
    "competitive positioning, and strategic recommendation with clear rationale."
);
```

---

## Task Decomposition and Delegation

### How Decomposition Works

The master agent uses a specialized prompt to decompose tasks:

```php
// Inside HierarchicalAgent::decompose()
$workersList = '';
foreach ($this->workers as $name => $worker) {
    $workersList .= "- {$name}: {$worker->getSpecialty()}\n";
}

$prompt = "Complex task: {$task}\n\n" .
    "Available specialized agents:\n{$workersList}\n" .
    "Decompose this task into subtasks. For each subtask:\n" .
    "1. Specify which agent should handle it\n" .
    "2. Describe the subtask clearly\n\n" .
    "Format each subtask as:\n" .
    "Agent: [agent_name]\n" .
    'Subtask: [description]';
```

The LLM returns structured subtask assignments:

```text
Agent: security_expert
Subtask: Review the code for SQL injection vulnerabilities in the UserRepository class

Agent: performance_expert
Subtask: Analyze the database query patterns for N+1 issues and inefficient joins

Agent: practices_expert
Subtask: Evaluate adherence to SOLID principles and PSR coding standards

Agent: test_expert
Subtask: Assess test coverage and suggest missing test cases for edge conditions
```

### Influencing Delegation

You can guide decomposition through:

1. **Specialty descriptions** — More specific = better delegation:

```php
// Less effective
$worker = new WorkerAgent($client, [
    'specialty' => 'data analysis',
]);

// More effective
$worker = new WorkerAgent($client, [
    'specialty' => 'time series analysis, statistical forecasting, and anomaly detection',
]);
```

2. **Task phrasing** — Explicit mentions help:

```php
// Vague
$result = $master->run("Analyze this data and write something about it");

// Specific
$result = $master->run(
    "Perform statistical analysis on this dataset (focusing on trends and outliers), " .
    "then write an executive summary highlighting key business insights"
);
```

### Fallback Behavior

If a requested worker doesn't exist:

```php
// Master tries to use 'specialized_worker' but it's not registered
// System falls back to first available worker
$worker = reset($this->workers) ?: null;

// If NO workers registered, subtask notes unavailability
if ($worker === null) {
    $workerResults[$workerName] = "No worker available for: {$subtaskText}";
}
```

---

## Result Synthesis

### How Synthesis Works

After workers complete subtasks, the master synthesizes results:

```php
// Inside HierarchicalAgent::synthesize()
$resultsText = '';
foreach ($results as $agent => $output) {
    $resultsText .= "=== {$agent} Output ===\n{$output}\n\n";
}

$prompt = "Original task: {$task}\n\n" .
    "Worker outputs:\n{$resultsText}\n" .
    'Synthesize these into a comprehensive, coherent final answer.';
```

### Synthesis Quality

The master combines outputs intelligently:

```php
// Worker outputs:
$outputs = [
    'math_expert' => 'The average is 81 (calculated as (45+67+89+123)/4)',
    'writer_expert' => 'An average is a measure of central tendency that represents the typical value in a dataset. It\'s useful for summarizing data and making comparisons...',
];

// Synthesized result:
"The average of 45, 67, 89, and 123 is 81. 

An average is a measure of central tendency that represents the typical value in a dataset, calculated by summing all values and dividing by the count. In this case, we sum 45 + 67 + 89 + 123 = 324, then divide by 4 to get 81.

Averages are useful for summarizing data and making comparisons. They help us understand the typical value when dealing with multiple numbers..."
```

---

## Performance and Cost Optimization

### Token Usage Tracking

The system aggregates tokens across all API calls:

```php
$result = $master->run($task);

$usage = $result->getTokenUsage();
echo "Input tokens: {$usage['input']}\n";
echo "Output tokens: {$usage['output']}\n";
echo "Total tokens: {$usage['total']}\n";

// Estimate cost (Sonnet pricing)
$inputCost = $usage['input'] * 0.003 / 1000;
$outputCost = $usage['output'] * 0.015 / 1000;
$totalCost = $inputCost + $outputCost;

echo "Estimated cost: $" . number_format($totalCost, 4) . "\n";
```

### Execution Time

Typical timing breakdown:

```text
Decomposition:  2-3 seconds (1 API call)
Worker 1:       3-5 seconds (1 API call)
Worker 2:       3-5 seconds (1 API call)
Worker 3:       3-5 seconds (1 API call)
Synthesis:      2-4 seconds (1 API call)
─────────────────────────────────────
Total (3 workers): ~15-22 seconds
```

### Optimization Strategies

1. **Use Haiku for simple workers**:

```php
$simpleWorker = new WorkerAgent($client, [
    'model' => 'claude-haiku-3-5',  // 20x cheaper than Sonnet
    'max_tokens' => 1024,
    'specialty' => 'data formatting and validation',
]);
```

2. **Limit worker tokens**:

```php
$worker = new WorkerAgent($client, [
    'max_tokens' => 1024,  // Shorter responses
    'system' => 'Provide concise, focused answers. No unnecessary elaboration.',
]);
```

3. **Cache common decompositions**:

```php
class CachedHierarchicalAgent
{
    private array $decompositionCache = [];
    
    public function run(string $task): AgentResult
    {
        $cacheKey = md5($task);
        
        if (isset($this->decompositionCache[$cacheKey])) {
            // Reuse decomposition, skip API call
            $subtasks = $this->decompositionCache[$cacheKey];
        } else {
            $subtasks = $this->decompose($task);
            $this->decompositionCache[$cacheKey] = $subtasks;
        }
        
        // Continue with execution...
    }
}
```

4. **Limit worker count**:

```php
// More workers = more API calls
// For budget-conscious systems, use 2-3 focused workers
$master->registerWorker('primary_expert', $primaryWorker);
$master->registerWorker('secondary_expert', $secondaryWorker);
// Skip registering 5+ workers unless quality demands it
```

---

## Error Handling and Reliability

### Handling Failures

The system provides comprehensive error information:

```php
$result = $master->run($task);

if (!$result->isSuccess()) {
    $error = $result->getError();
    
    // Categorize error
    if (str_contains($error, 'decompose')) {
        echo "Failed to break down task. Try simplifying or rephrasing.\n";
    } elseif (str_contains($error, 'API')) {
        echo "API error. Check connection and retry.\n";
    } else {
        echo "Unexpected error: {$error}\n";
    }
    
    // Log for debugging
    error_log("Hierarchical agent failure: {$error}");
}
```

### Retry Logic

Implement retries for transient failures:

```php
function runWithRetry(
    HierarchicalAgent $master,
    string $task,
    int $maxAttempts = 3
): AgentResult {
    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
        try {
            $result = $master->run($task);
            
            if ($result->isSuccess()) {
                return $result;
            }
            
            if ($attempt < $maxAttempts) {
                $backoff = 2 ** $attempt; // Exponential backoff
                echo "Attempt {$attempt} failed, retrying in {$backoff}s...\n";
                sleep($backoff);
            }
        } catch (\Throwable $e) {
            if ($attempt === $maxAttempts) {
                throw $e;
            }
            
            $backoff = 2 ** $attempt;
            echo "Exception on attempt {$attempt}, retrying in {$backoff}s...\n";
            sleep($backoff);
        }
    }
    
    throw new \RuntimeException("Failed after {$maxAttempts} attempts");
}

// Usage
$result = runWithRetry($master, $complexTask);
```

### Validation

Validate results before using:

```php
$result = $master->run($task);

if ($result->isSuccess()) {
    $answer = $result->getAnswer();
    $metadata = $result->getMetadata();
    
    // Check answer quality
    if (strlen($answer) < 100) {
        error_log("Warning: Suspiciously short answer");
    }
    
    // Verify expected workers were used
    $expectedWorkers = ['security_expert', 'performance_expert'];
    $usedWorkers = $metadata['workers_used'] ?? [];
    
    $missing = array_diff($expectedWorkers, $usedWorkers);
    if (!empty($missing)) {
        error_log("Warning: Expected workers not used: " . implode(', ', $missing));
    }
    
    // Check token usage for anomalies
    $totalTokens = $metadata['token_usage']['total'] ?? 0;
    if ($totalTokens > 10000) {
        error_log("Warning: High token usage ({$totalTokens})");
    }
}
```

---

## Production Patterns

### Logging and Monitoring

Add comprehensive logging:

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;

$logger = new Logger('hierarchical');
$logger->pushHandler(new RotatingFileHandler(
    'logs/hierarchical.log',
    30, // Keep 30 days
    Logger::INFO
));
$logger->pushHandler(new StreamHandler('php://stderr', Logger::ERROR));

$master = new HierarchicalAgent($client, [
    'logger' => $logger,
]);

// Logs include:
// - Task decomposition decisions
// - Worker assignments
// - Execution progress
// - Token usage per worker
// - Synthesis results
// - Errors and warnings
```

### Configuration Management

Use a factory pattern for consistent setup:

```php
class HierarchicalAgentFactory
{
    public function __construct(
        private ClaudePhp $client,
        private array $config
    ) {}
    
    public function createCodeReviewer(): HierarchicalAgent
    {
        $master = new HierarchicalAgent($this->client, [
            'name' => $this->config['name'] ?? 'code_reviewer',
            'model' => $this->config['model'] ?? 'claude-sonnet-4-5',
            'max_tokens' => $this->config['max_tokens'] ?? 2048,
        ]);
        
        foreach ($this->config['workers'] as $workerConfig) {
            $worker = new WorkerAgent($this->client, $workerConfig);
            $master->registerWorker($workerConfig['name'], $worker);
        }
        
        return $master;
    }
}

// Usage with config file
$config = require 'config/agents.php';
$factory = new HierarchicalAgentFactory($client, $config);
$codeReviewer = $factory->createCodeReviewer();
```

### Rate Limiting

Protect against API rate limits:

```php
class RateLimitedHierarchicalAgent
{
    private array $requestTimes = [];
    
    public function __construct(
        private HierarchicalAgent $agent,
        private int $maxRequestsPerMinute = 50
    ) {}
    
    public function run(string $task): AgentResult
    {
        $this->waitForRateLimit();
        $this->requestTimes[] = time();
        
        return $this->agent->run($task);
    }
    
    private function waitForRateLimit(): void
    {
        // Remove requests older than 1 minute
        $cutoff = time() - 60;
        $this->requestTimes = array_filter(
            $this->requestTimes,
            fn($time) => $time > $cutoff
        );
        
        // Wait if at limit
        if (count($this->requestTimes) >= $this->maxRequestsPerMinute) {
            $oldestRequest = min($this->requestTimes);
            $waitTime = 60 - (time() - $oldestRequest);
            if ($waitTime > 0) {
                sleep($waitTime);
            }
        }
    }
}

// Usage
$rateLimitedAgent = new RateLimitedHierarchicalAgent($master, maxRequestsPerMinute: 50);
$result = $rateLimitedAgent->run($task);
```

### Caching Results

Cache common task results:

```php
class CachedHierarchicalAgent
{
    private array $cache = [];
    
    public function __construct(
        private HierarchicalAgent $agent,
        private int $ttl = 3600 // 1 hour TTL
    ) {}
    
    public function run(string $task): AgentResult
    {
        $cacheKey = $this->getCacheKey($task);
        
        // Check cache
        if (isset($this->cache[$cacheKey])) {
            $cached = $this->cache[$cacheKey];
            if (time() - $cached['timestamp'] < $this->ttl) {
                return $cached['result'];
            }
            unset($this->cache[$cacheKey]);
        }
        
        // Execute and cache
        $result = $this->agent->run($task);
        
        if ($result->isSuccess()) {
            $this->cache[$cacheKey] = [
                'result' => $result,
                'timestamp' => time(),
            ];
        }
        
        return $result;
    }
    
    private function getCacheKey(string $task): string
    {
        return md5(strtolower(trim($task)));
    }
}
```

---

## Testing Hierarchical Systems

### Unit Tests

Test individual workers:

```php
use PHPUnit\Framework\TestCase;

class WorkerAgentTest extends TestCase
{
    private ClaudePhp $client;
    
    protected function setUp(): void
    {
        $this->client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));
    }
    
    public function testMathWorker(): void
    {
        $worker = new WorkerAgent($this->client, [
            'specialty' => 'mathematics',
            'system' => 'Provide precise calculations.',
        ]);
        
        $result = $worker->run('Calculate 25 * 17');
        
        $this->assertTrue($result->isSuccess());
        $this->assertStringContainsString('425', $result->getAnswer());
    }
    
    public function testWorkerSpecialty(): void
    {
        $worker = new WorkerAgent($this->client, [
            'specialty' => 'Python programming',
        ]);
        
        $this->assertEquals('Python programming', $worker->getSpecialty());
    }
}
```

### Integration Tests

Test full hierarchical systems:

```php
class HierarchicalSystemTest extends TestCase
{
    private HierarchicalAgent $agent;
    
    protected function setUp(): void
    {
        $client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));
        
        $mathWorker = new WorkerAgent($client, [
            'specialty' => 'mathematics',
        ]);
        
        $writerWorker = new WorkerAgent($client, [
            'specialty' => 'writing',
        ]);
        
        $this->agent = new HierarchicalAgent($client);
        $this->agent->registerWorker('math', $mathWorker);
        $this->agent->registerWorker('writer', $writerWorker);
    }
    
    public function testMultiWorkerTask(): void
    {
        $result = $this->agent->run(
            'Calculate 10 + 15 and explain the result'
        );
        
        $this->assertTrue($result->isSuccess());
        
        $metadata = $result->getMetadata();
        $this->assertGreaterThan(0, $metadata['subtasks']);
        $this->assertNotEmpty($metadata['workers_used']);
    }
    
    public function testTokenUsage(): void
    {
        $result = $this->agent->run('Simple task');
        
        $usage = $result->getTokenUsage();
        $this->assertGreaterThan(0, $usage['input']);
        $this->assertGreaterThan(0, $usage['output']);
        $this->assertEquals(
            $usage['input'] + $usage['output'],
            $usage['total']
        );
    }
}
```

---

## Best Practices

### 1. Design Clear Specialties

✅ **Do**:
- Create narrow, focused specialties
- Use specific, descriptive specialty descriptions
- Avoid overlapping domains between workers

❌ **Don't**:
- Create overly broad "general purpose" workers
- Use vague specialty descriptions
- Register workers with conflicting domains

### 2. Right-Size Your Team

✅ **Do**:
- Use 2-4 workers for most tasks
- Add workers only when quality demands it
- Balance cost vs. quality trade-offs

❌ **Don't**:
- Register 10+ workers for simple tasks
- Add workers "just in case"
- Forget to consider budget constraints

### 3. Monitor and Optimize

✅ **Do**:
- Track token usage per worker
- Log decomposition decisions
- Measure execution time per phase
- Cache common task patterns

❌ **Don't**:
- Run blind without metrics
- Ignore cost optimization opportunities
- Skip result validation

### 4. Handle Errors Gracefully

✅ **Do**:
- Implement retry logic with exponential backoff
- Validate worker outputs before synthesis
- Provide fallback strategies
- Log detailed error context

❌ **Don't**:
- Assume all tasks will succeed
- Ignore partial failures
- Skip validation of synthesized results

### 5. Test Thoroughly

✅ **Do**:
- Test workers individually
- Test full system integration
- Verify worker selection logic
- Test error scenarios

❌ **Don't**:
- Skip testing in production
- Test only happy paths
- Ignore edge cases

---

## Summary

Hierarchical agent architectures excel at **multi-domain problems** that benefit from specialized expertise. The [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent) framework's `HierarchicalAgent` and `WorkerAgent` classes make it straightforward to build production-grade master-worker systems.

### Key Takeaways

1. **Specialization Matters**: Focused workers outperform generalists on complex tasks
2. **Three Phases**: Decomposition → Execution → Synthesis
3. **Cost Awareness**: More workers = more API calls; optimize strategically
4. **Production Readiness**: Add logging, monitoring, retries, and validation
5. **Right Tool for the Job**: Use hierarchical patterns for truly multi-domain problems

### When to Use

- ✅ Task requires multiple domains of expertise
- ✅ Quality demands specialist attention
- ✅ Subtasks can be worked independently
- ✅ You need auditability of which expert handled what

### When to Skip

- ❌ Task is simple enough for a single agent
- ❌ Budget or latency constraints are tight
- ❌ Work must be strictly sequential
- ❌ Domain doesn't benefit from specialization

---

## Next Steps

In **Chapter 14: Communication Protocols and Handoff Patterns**, you'll learn how to standardize inter-agent messaging, design structured outputs, and build contract-driven collaboration for even more sophisticated multi-agent systems.

For now, practice building hierarchical systems:

1. Start with a 2-worker system (math + writing)
2. Expand to a 4-worker code review pipeline
3. Build a custom team for your domain
4. Add production features: logging, caching, monitoring
5. Optimize cost and performance based on metrics

### Additional Resources

- [`HierarchicalAgent` API Documentation](https://github.com/claude-php/claude-php-agent/blob/main/docs/HierarchicalAgent.md)
- [`HierarchicalAgent` Tutorial](https://github.com/claude-php/claude-php-agent/blob/main/docs/tutorials/HierarchicalAgent_Tutorial.md)
- [Example Code](https://github.com/claude-php/claude-php-agent/blob/main/examples/hierarchical_agent.php)

---

## Exercises

### Exercise 1: Build a Documentation Team

Create a hierarchical system with:
- Technical writer (explains concepts clearly)
- Code example generator (creates working code samples)
- Proofreader (fixes grammar and style)
- API documenter (documents API endpoints)

**Task**: Generate complete API documentation for a REST endpoint.

### Exercise 2: Financial Analysis Pipeline

Build a system with:
- Data analyst (processes financial data)
- Risk analyst (assesses risks)
- Compliance checker (verifies regulations)
- Report writer (summarizes findings)

**Task**: Analyze a quarterly financial report and produce an executive summary.

### Exercise 3: Customer Support System

Create workers for:
- Technical support (diagnoses technical issues)
- Policy specialist (knows company policies)
- Sentiment analyzer (detects customer emotion)
- Response writer (crafts empathetic replies)

**Task**: Process a customer complaint and generate an appropriate response.

### Exercise 4: Optimize for Cost

Take any hierarchical system and:
1. Measure baseline token usage
2. Replace simple workers with Haiku
3. Reduce max_tokens where possible
4. Cache common decompositions
5. Measure new token usage

**Goal**: Reduce cost by 30-50% without sacrificing quality.

---

**You now have the knowledge to build production-grade hierarchical agent systems. The master-worker pattern unlocks sophisticated multi-domain capabilities that single agents can't match.**
