---
title: "15: Adaptive Agent Selection"
description: "Use AdaptiveAgentService for intelligent agent selection, validation, and auto-adaptation based on task analysis."
series: "agentic-ai-php-developers"
chapter: 15
difficulty: "Advanced"
---

# Chapter 15: Adaptive Agent Selection

## Overview

When building multi-agent systems, a critical question emerges: **which agent should handle this task?** Choosing the wrong agent wastes tokens, delivers poor results, or outright fails. The [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent) framework solves this with **AdaptiveAgentService** — a meta-agent that analyzes tasks, selects the best agent, validates results, and adapts if quality is insufficient.

This chapter teaches you to build intelligent agent selection systems that automatically match tasks to the right specialist, validate output quality, retry with different agents when needed, and learn from experience using k-NN machine learning. You'll go from manual agent selection to fully automated, self-improving orchestration.

In this chapter you'll:

- Master **intelligent agent selection** using task analysis and capability matching
- Implement **automatic result validation** with quality scoring and correctness checks
- Build **adaptive retry logic** that tries different agents or reframes requests
- Use **k-NN machine learning** for continuous learning from historical performance
- Design **agent profiles** that describe strengths, complexity levels, and use cases
- Create **production-ready adaptive systems** with monitoring and performance tracking
- Optimize **quality vs cost tradeoffs** in multi-agent orchestration

**Estimated time:** ~120 minutes

::: info Code examples
Complete, runnable examples for this chapter:

- [`01-basic-adaptive-selection.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/15-adaptive-agent-selection/01-basic-adaptive-selection.php) — Simple adaptive agent setup
- [`02-agent-profiles-and-registration.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/15-adaptive-agent-selection/02-agent-profiles-and-registration.php) — Profile design and registration
- [`03-task-analysis-and-matching.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/15-adaptive-agent-selection/03-task-analysis-and-matching.php) — How task analysis works
- [`04-result-validation-adaptation.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/15-adaptive-agent-selection/04-result-validation-adaptation.php) — Quality validation and retry
- [`05-knn-learning-system.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/15-adaptive-agent-selection/05-knn-learning-system.php) — Machine learning with k-NN
- [`06-performance-monitoring.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/15-adaptive-agent-selection/06-performance-monitoring.php) — Tracking metrics and history
- [`07-production-adaptive-system.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/15-adaptive-agent-selection/07-production-adaptive-system.php) — Complete production system

All files are in [`code/15-adaptive-agent-selection/`](https://github.com/dalehurley/codewithphp/tree/main/code/agentic-ai-php-developers/15-adaptive-agent-selection).
:::

---

## The Problem: Agent Selection at Scale

### Manual Agent Selection is Brittle

When you have multiple specialized agents, you face several challenges:

```php
// ❌ Problem: Manual selection is error-prone
if (str_contains($task, 'calculate')) {
    $result = $reactAgent->run($task);
} elseif (str_contains($task, 'write')) {
    $result = $reflectionAgent->run($task);
} elseif (str_contains($task, 'research')) {
    $result = $ragAgent->run($task);
} else {
    // What agent handles this?
    $result = $defaultAgent->run($task);
}

// No quality validation
// No retry logic
// No learning from failures
```

**Issues:**

- Simple keyword matching fails for complex tasks
- No fallback if selected agent performs poorly
- No quality assurance
- No learning or improvement over time

### The Adaptive Solution

```php
// ✓ Solution: Adaptive Agent Service
$service = new AdaptiveAgentService($client, [
    'max_attempts' => 3,
    'quality_threshold' => 7.0,
    'enable_reframing' => true,
    'enable_knn' => true,
]);

// Register specialized agents
$service->registerAgent('react', $reactAgent, [...]);
$service->registerAgent('reflection', $reflectionAgent, [...]);
$service->registerAgent('rag', $ragAgent, [...]);

// Automatic intelligent selection, validation, and adaptation
$result = $service->run($task);
```

**Benefits:**

- Analyzes task characteristics automatically
- Selects best agent based on profiles and history
- Validates results against quality standards
- Retries with different agents if needed
- Learns from every execution (k-NN)

---

## Understanding AdaptiveAgentService

### Architecture Overview

```text
┌───────────────────────────────────────────────────────┐
│                User Task                              │
└────────────────────┬──────────────────────────────────┘
                     ↓
┌────────────────────────────────────────────────────────┐
│  1. Task Analysis                                      │
│  ┌─────────────────────────────────────────────────┐  │
│  │  • Complexity: simple, medium, complex, extreme │  │
│  │  • Domain: technical, creative, analytical      │  │
│  │  • Requirements: tools, knowledge, reasoning    │  │
│  │  • Quality needs: standard, high, extreme       │  │
│  └─────────────────────────────────────────────────┘  │
└────────────────────┬───────────────────────────────────┘
                     ↓
┌────────────────────────────────────────────────────────┐
│  2. Agent Selection                                    │
│  ┌─────────────────────────────────────────────────┐  │
│  │  k-NN: Find similar historical tasks           │  │
│  │  Rules: Score agents by capability match        │  │
│  │  History: Consider past performance             │  │
│  └─────────────────────────────────────────────────┘  │
└────────────────────┬───────────────────────────────────┘
                     ↓
┌────────────────────────────────────────────────────────┐
│  3. Execution                                          │
│  Run selected agent with task                          │
└────────────────────┬───────────────────────────────────┘
                     ↓
┌────────────────────────────────────────────────────────┐
│  4. Validation                                         │
│  ┌─────────────────────────────────────────────────┐  │
│  │  • Correctness check                            │  │
│  │  • Completeness check                           │  │
│  │  • Clarity assessment                           │  │
│  │  • Quality score (0-10)                         │  │
│  └─────────────────────────────────────────────────┘  │
└────────────────────┬───────────────────────────────────┘
                     ↓
                ┌────────┐
                │ Good?  │
                └───┬────┘
                    │
         ┌──────────┴──────────┐
         │                     │
        Yes                   No
         │                     │
         ↓                     ↓
   ┌─────────┐         ┌────────────────┐
   │ Return  │         │ 5. Adapt       │
   │ Result  │         │ • Try another  │
   └─────────┘         │   agent        │
                       │ • Reframe task │
                       └────┬───────────┘
                            │
                            └──► Back to Step 2
```

### The Five Phases

**1. Task Analysis**

The service uses Claude to analyze the task and determine:

- **Complexity**: How difficult is this task?
- **Domain**: What field does it belong to?
- **Requirements**: What capabilities are needed?
- **Quality needs**: How critical is output quality?

**2. Agent Selection**

Two methods:

- **k-NN (Primary)**: Find k=10 similar historical tasks and select agent that performed best on them
- **Rule-based (Fallback)**: Score agents by matching task requirements to agent profiles

**3. Execution**

Run the selected agent with the task.

**4. Validation**

Claude evaluates the result on:

- Correctness
- Completeness
- Clarity
- Relevance

Produces quality score (0-10).

**5. Adaptation (if needed)**

If quality < threshold:

- Try a different agent (different strengths)
- Reframe the task (make it clearer)
- Record failure for learning

---

## Basic Setup and Usage

### Installing the Service

```php
use ClaudeAgents\Agents\AdaptiveAgentService;
use ClaudePhp\ClaudePhp;

$client = ClaudePhp::make($apiKey);

$service = new AdaptiveAgentService($client, [
    'max_attempts' => 3,              // Try up to 3 times
    'quality_threshold' => 7.0,       // Require 7/10 quality
    'enable_reframing' => true,       // Reframe on failure
    'enable_knn' => true,             // Enable learning
    'history_store_path' => 'storage/agent_history.json',
]);
```

### Configuration Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `max_attempts` | int | 3 | Maximum retry attempts |
| `quality_threshold` | float | 7.0 | Minimum acceptable quality (0-10) |
| `enable_reframing` | bool | true | Reframe requests on failure |
| `enable_knn` | bool | true | Enable k-NN learning system |
| `history_store_path` | string | `'storage/agent_history.json'` | Path to learning history |
| `logger` | LoggerInterface | `NullLogger` | PSR-3 logger |

### Simple Example

```php
// Create agents
$reactAgent = new ReactAgent($client, ['tools' => [$calculator]]);
$reflectionAgent = new ReflectionAgent($client);

// Create service
$service = new AdaptiveAgentService($client);

// Register agents
$service->registerAgent('react', $reactAgent, [
    'type' => 'react',
    'complexity_level' => 'medium',
    'quality' => 'standard',
]);

$service->registerAgent('reflection', $reflectionAgent, [
    'type' => 'reflection',
    'complexity_level' => 'medium',
    'quality' => 'high',
]);

// Run task - automatic selection and validation
$result = $service->run('Calculate 15% of 240');

if ($result->isSuccess()) {
    echo $result->getAnswer() . "\n";
    echo "Agent: " . $result->getMetadata()['final_agent'] . "\n";
    echo "Quality: " . $result->getMetadata()['final_quality'] . "/10\n";
}
```

---

## Agent Profiles

### Profile Structure

Each agent needs a profile describing its capabilities:

```php
$service->registerAgent('agent_id', $agentInstance, [
    'type' => 'react',                    // Agent type
    'strengths' => [                       // What it's good at
        'tool usage',
        'iterative problem solving',
    ],
    'best_for' => [                        // Use cases
        'calculations',
        'API calls',
        'multi-step tasks',
    ],
    'complexity_level' => 'medium',        // simple|medium|complex|extreme
    'speed' => 'medium',                   // fast|medium|slow
    'quality' => 'standard',               // standard|high|extreme
]);
```

### Complexity Levels

**Simple**

- Single-step tasks
- Straightforward logic
- No special requirements

**Example agents:** ReflexAgent, Simple RAG

```php
'complexity_level' => 'simple',
'best_for' => ['FAQ questions', 'basic lookups', 'simple calculations']
```

**Medium**

- Multi-step workflows
- Moderate reasoning
- Tool usage

**Example agents:** ReactAgent, ChainOfThoughtAgent, ReflectionAgent

```php
'complexity_level' => 'medium',
'best_for' => ['data processing', 'code generation', 'research tasks']
```

**Complex**

- Multiple domains
- Advanced reasoning
- Orchestration

**Example agents:** PlanExecuteAgent, RAG with complex retrieval

```php
'complexity_level' => 'complex',
'best_for' => ['multi-domain tasks', 'planning workflows', 'complex analysis']
```

**Extreme**

- Million-step scale
- Near-perfect accuracy
- Voting mechanisms

**Example agents:** MakerAgent, TreeOfThoughtsAgent

```php
'complexity_level' => 'extreme',
'best_for' => ['critical systems', 'theorem proving', 'ultra-high-quality content']
```

### Quality Levels

**Standard**

- Good quality
- Single-pass generation
- Typical use cases

```php
'quality' => 'standard',
'speed' => 'fast',
```

**High**

- High quality
- Validation or refinement
- Quality-critical tasks

```php
'quality' => 'high',
'speed' => 'medium',
```

**Extreme**

- Near-perfect quality
- Multiple validation passes
- Mission-critical outputs

```php
'quality' => 'extreme',
'speed' => 'slow',
```

### Profile Examples

**React Agent (General Purpose)**

```php
$service->registerAgent('react', $reactAgent, [
    'type' => 'react',
    'strengths' => ['tool orchestration', 'iterative solving', 'debugging'],
    'best_for' => ['calculations', 'API calls', 'data processing', 'multi-step tasks'],
    'complexity_level' => 'medium',
    'speed' => 'medium',
    'quality' => 'standard',
]);
```

**Reflection Agent (Quality-Critical)**

```php
$service->registerAgent('reflection', $reflectionAgent, [
    'type' => 'reflection',
    'strengths' => ['quality refinement', 'self-improvement', 'critique'],
    'best_for' => ['writing', 'code generation', 'critical outputs', 'content refinement'],
    'complexity_level' => 'medium',
    'speed' => 'slow',
    'quality' => 'high',
]);
```

**RAG Agent (Knowledge-Based)**

```php
$service->registerAgent('rag', $ragAgent, [
    'type' => 'rag',
    'strengths' => ['knowledge grounding', 'source attribution', 'fact accuracy'],
    'best_for' => ['Q&A', 'documentation', 'fact-based tasks', 'research'],
    'complexity_level' => 'simple',
    'speed' => 'fast',
    'quality' => 'high',
]);
```

**Maker Agent (Extreme Quality)**

```php
$service->registerAgent('maker', $makerAgent, [
    'type' => 'maker',
    'strengths' => ['extreme accuracy', 'voting mechanisms', 'million-step reasoning'],
    'best_for' => ['critical systems', 'theorem proving', 'ultra-complex tasks'],
    'complexity_level' => 'extreme',
    'speed' => 'slow',
    'quality' => 'extreme',
]);
```

**Chain of Thought (Reasoning)**

```php
$service->registerAgent('cot', $cotAgent, [
    'type' => 'cot',
    'strengths' => ['step-by-step reasoning', 'transparency', 'logical explanations'],
    'best_for' => ['math problems', 'logic puzzles', 'explanations', 'teaching'],
    'complexity_level' => 'medium',
    'speed' => 'fast',
    'quality' => 'standard',
]);
```

**Dialog Agent (Conversational)**

```php
$service->registerAgent('dialog', $dialogAgent, [
    'type' => 'dialog',
    'strengths' => ['natural conversation', 'context management', 'persona consistency'],
    'best_for' => ['customer support', 'chatbots', 'interviews', 'tutoring'],
    'complexity_level' => 'simple',
    'speed' => 'fast',
    'quality' => 'standard',
]);
```

---

## Task Analysis

### How Task Analysis Works

The service sends this prompt to Claude:

```
Analyze this task and provide a structured assessment:

Task: "{$task}"

Provide analysis in JSON format with these fields:
{
  "complexity": "simple|medium|complex|extreme",
  "domain": "general|technical|creative|analytical|conversational|monitoring",
  "requires_tools": true|false,
  "requires_quality": "standard|high|extreme",
  "requires_knowledge": true|false,
  "requires_reasoning": true|false,
  "requires_iteration": true|false,
  "estimated_steps": <number>,
  "key_requirements": ["requirement1", "requirement2"]
}
```

### Task Analysis Examples

**Example 1: Simple Calculation**

```php
$task = "Calculate 15% of 240";

// Analysis:
[
    'complexity' => 'simple',
    'domain' => 'general',
    'requires_tools' => true,
    'requires_quality' => 'standard',
    'requires_knowledge' => false,
    'requires_reasoning' => false,
    'requires_iteration' => false,
    'estimated_steps' => 2,
    'key_requirements' => ['arithmetic calculation'],
]
```

**Selected agent:** React (has calculator tool)

**Example 2: Writing Task**

```php
$task = "Write a professional email apologizing for a project delay";

// Analysis:
[
    'complexity' => 'medium',
    'domain' => 'creative',
    'requires_tools' => false,
    'requires_quality' => 'high',
    'requires_knowledge' => false,
    'requires_reasoning' => false,
    'requires_iteration' => true,
    'estimated_steps' => 5,
    'key_requirements' => ['tone', 'professionalism', 'empathy'],
]
```

**Selected agent:** Reflection (high quality output)

**Example 3: Knowledge Query**

```php
$task = "What is dependency injection in PHP?";

// Analysis:
[
    'complexity' => 'simple',
    'domain' => 'technical',
    'requires_tools' => false,
    'requires_quality' => 'standard',
    'requires_knowledge' => true,
    'requires_reasoning' => false,
    'requires_iteration' => false,
    'estimated_steps' => 3,
    'key_requirements' => ['PHP knowledge', 'clear explanation'],
]
```

**Selected agent:** RAG (knowledge-based)

**Example 4: Complex Reasoning**

```php
$task = "If all A are B, and all B are C, prove that all A are C";

// Analysis:
[
    'complexity' => 'medium',
    'domain' => 'analytical',
    'requires_tools' => false,
    'requires_quality' => 'standard',
    'requires_reasoning' => true,
    'requires_knowledge' => false,
    'requires_iteration' => false,
    'estimated_steps' => 5,
    'key_requirements' => ['logical reasoning', 'step-by-step proof'],
]
```

**Selected agent:** Chain of Thought (reasoning specialist)

---

## Agent Selection Algorithms

### Method 1: k-NN Based Selection (Primary)

When historical data exists, the service uses **k-Nearest Neighbors**:

```text
Step 1: Convert task to 14D feature vector
[complexity, domain×6, requirements×4, quality, steps, req_count]

Step 2: Find k=10 most similar historical tasks
Using cosine similarity between vectors

Step 3: Weight by temporal decay
Recent tasks weighted higher than old ones

Step 4: Group by agent_id and score
For each agent: success_rate × avg_quality × recency

Step 5: Select agent with highest score
```

**Example:**

```php
// First calculation task
$service->run('Calculate 15% of 240');
// Method: rule-based (no history)
// Confidence: 50%

// Second similar task (after history exists)
$service->run('Calculate 20% of 500');
// Method: k-NN
// Found 1 similar task where react_agent scored 8.5/10
// Confidence: 87%
// Selected: react_agent
```

### Method 2: Rule-Based Selection (Fallback)

When no historical data exists, the service uses scoring:

**1. Complexity Match (0-10 points)**

Match task complexity to agent capability:

```php
$complexityScores = [
    'simple' => ['simple' => 10, 'medium' => 5, 'complex' => 2, 'extreme' => 1],
    'medium' => ['simple' => 5, 'medium' => 10, 'complex' => 7, 'extreme' => 3],
    'complex' => ['simple' => 2, 'medium' => 5, 'complex' => 10, 'extreme' => 8],
    'extreme' => ['simple' => 1, 'medium' => 2, 'complex' => 5, 'extreme' => 10],
];

$score += $complexityScores[$taskComplexity][$agentComplexity];
```

**2. Quality Match (0-10 points)**

Match quality requirements to agent quality:

```php
$qualityScores = [
    'standard' => ['standard' => 10, 'high' => 5, 'extreme' => 3],
    'high' => ['standard' => 3, 'high' => 10, 'extreme' => 7],
    'extreme' => ['standard' => 1, 'high' => 5, 'extreme' => 10],
];

$score += $qualityScores[$taskQuality][$agentQuality];
```

**3. Performance History (0-8 points)**

```php
if ($agent['attempts'] > 0) {
    $successRate = $agent['successes'] / $agent['attempts'];
    $score += $successRate * 5;  // 0-5 points
    $score += ($agent['average_quality'] / 10) * 3;  // 0-3 points
}
```

**4. Capability Bonuses (0-7 points each)**

```php
// Task requires tools → React agent
if ($taskAnalysis['requires_tools'] && $agentType === 'react') {
    $score += 5;
}

// High quality needed → Reflection/Maker agent
if ($taskAnalysis['requires_quality'] === 'extreme' && $agentType === 'maker') {
    $score += 7;
}

// Knowledge required → RAG agent
if ($taskAnalysis['requires_knowledge'] && $agentType === 'rag') {
    $score += 5;
}

// Reasoning needed → CoT/ToT agent
if ($taskAnalysis['requires_reasoning'] && in_array($agentType, ['cot', 'tot'])) {
    $score += 5;
}

// Conversational → Dialog agent
if ($taskAnalysis['domain'] === 'conversational' && $agentType === 'dialog') {
    $score += 5;
}
```

**5. Retry Penalty (-10 points)**

If agent was already tried and failed:

```php
if (in_array($agentId, $previousAttempts)) {
    $score -= 10;
}
```

**Final Selection:**

```php
arsort($scores);  // Sort by score descending
$selectedAgent = array_key_first($scores);
```

---

## Result Validation

### Validation Process

Every result is validated using Claude as a judge:

```
Evaluate this agent's response for quality and correctness.

Original Task: "{$task}"

Agent's Answer: "{$answer}"

Evaluate on these criteria:
1. Correctness: Does it answer the task correctly?
2. Completeness: Is the answer complete?
3. Clarity: Is it clear and well-structured?
4. Relevance: Is it relevant to the task?

Provide evaluation in JSON format:
{
  "quality_score": <0-10>,
  "is_correct": true|false,
  "is_complete": true|false,
  "issues": ["issue1", "issue2"],
  "strengths": ["strength1", "strength2"]
}
```

### Validation Output

```php
[
    'quality_score' => 8.5,
    'is_correct' => true,
    'is_complete' => true,
    'issues' => ['Could provide more examples'],
    'strengths' => ['Clear', 'Well-structured', 'Accurate'],
]
```

### Quality Threshold

```php
// Set threshold in constructor
$service = new AdaptiveAgentService($client, [
    'quality_threshold' => 7.0,  // Require 7/10 minimum
]);

// If result quality < 7.0:
// → Try different agent or reframe task
```

### Adaptive Quality Thresholds (k-NN)

With k-NN enabled, the service adjusts thresholds based on historical difficulty:

```php
// Hard tasks historically achieve 6.5 average
// → Threshold adjusted to 6.0 (achievable)

// Easy tasks historically achieve 9.0 average
// → Threshold adjusted to 8.5 (expect high quality)

// Formula:
$adaptiveThreshold = $historicalMean - (0.5 * $standardDeviation);
$adaptiveThreshold = max(5.0, min(9.5, $adaptiveThreshold));
```

---

## Adaptation Strategies

### Strategy 1: Try Different Agent

If quality is insufficient, select a different agent:

```php
// Attempt 1: React agent (quality: 5.5/10)
// → Below threshold

// Attempt 2: Reflection agent (quality: 8.0/10)
// → Success!
```

The selection algorithm avoids previously-tried agents unless all have been exhausted.

### Strategy 2: Reframe the Task

If quality is significantly below threshold (> 2 points), reframe the task:

```
The following task was attempted but had quality issues. Reframe it to be clearer and more specific.

Original Task: "{$originalTask}"

Issues identified:
- Issue 1
- Issue 2

Provide a reframed version that addresses these issues.
```

**Example:**

```php
// Original (vague)
"Tell me about PHP"

// Issues: Too vague, lacks focus

// Reframed (specific)
"Explain the key features of PHP as a programming language, including variable syntax, function definitions, and class structure. Provide code examples."
```

### Strategy 3: Record and Learn

Every execution (success or failure) is recorded:

```php
[
    'id' => 'task_abc123',
    'task' => 'Calculate compound interest...',
    'task_vector' => [0.5, 0, 1, 0, ...],  // 14D vector
    'task_analysis' => [...],
    'agent_id' => 'react_agent',
    'agent_type' => 'react',
    'success' => true,
    'quality_score' => 8.5,
    'duration' => 3.2,
    'timestamp' => 1702456789,
]
```

Future similar tasks benefit from this learning.

---

## k-NN Learning System

### Overview

The k-NN system learns from every execution to improve agent selection over time.

### Learning Process

```text
1. Task arrives
   ↓
2. Convert to 14D feature vector
   [complexity, domain×6, requirements×4, quality, steps, req_count]
   ↓
3. Search history for k=10 most similar tasks
   Using cosine similarity
   ↓
4. Find which agents performed best on similar tasks
   Weight by: success_rate × avg_quality × recency
   ↓
5. Select top-performing agent
   (or fall back to rules if no history)
   ↓
6. Execute and record outcome
   ↓
7. Future similar tasks benefit from this learning
```

### Feature Vector (14 Dimensions)

```php
[
    0.5,      // complexity (0-1 scale)
    0,1,0,0,0,0,  // domain (one-hot: general, technical, creative, analytical, conversational, monitoring)
    1,0,1,0,  // requirements (tools, knowledge, reasoning, iteration)
    0.66,     // quality level (0-1)
    0.2,      // estimated steps (normalized)
    0.3,      // requirement count (normalized)
]
```

### Performance Growth

| Stage | History Size | Method | Confidence | Quality |
|-------|--------------|--------|------------|---------|
| Cold Start | 0-5 | Rule-based | 50% | Good |
| Learning | 5-20 | Mixed | 60-70% | Better |
| Mature | 20-50 | k-NN | 75-85% | Great |
| Expert | 50+ | k-NN | 85-95% | Optimal |

### Example: Learning in Action

```php
$service = new AdaptiveAgentService($client, [
    'enable_knn' => true,  // Enable learning
]);

// Task 1 (First time - no history)
$result = $service->run('Calculate 15% of 240');
// Method: rule-based
// Confidence: 50%
// Selected: react_agent

// Task 2 (Similar task - k-NN kicks in!)
$result = $service->run('Calculate 20% of 500');
// Method: k-NN
// Confidence: 87%
// Found 1 similar task: react_agent scored 8.5/10
// Selected: react_agent (based on history)

// After 10 similar tasks
$result = $service->run('Calculate compound interest over 5 years');
// Method: k-NN
// Confidence: 92%
// Found 10 similar tasks: react_agent 90% success, 8.7 avg quality
// Selected: react_agent (proven track record)
```

### Monitoring Learning

```php
// Get learning statistics
$stats = $service->getHistoryStats();
print_r($stats);
/*
[
    'knn_enabled' => true,
    'total_records' => 42,
    'unique_agents' => 3,
    'success_rate' => 0.95,
    'avg_quality' => 8.2,
    'oldest_record' => 1702345678,
    'newest_record' => 1702456789,
]
*/

// Get recommendation without execution
$recommendation = $service->recommendAgent($task);
echo "Best agent: {$recommendation['agent_id']}\n";
echo "Confidence: {$recommendation['confidence']}\n";
echo "Method: {$recommendation['method']}\n";  // "k-NN" or "rule-based"
echo "Reasoning: {$recommendation['reasoning']}\n";
```

### Storage

History is stored in JSON format (`storage/agent_history.json`):

```json
{
  "id": "task_abc123",
  "task": "Calculate compound interest...",
  "task_vector": [0.5, 0, 1, 0, 0, 0, 0, 1, 0, 1, 0, 0.66, 0.2, 0.3],
  "task_analysis": {...},
  "agent_id": "react_agent",
  "success": true,
  "quality_score": 8.5,
  "duration": 3.2,
  "timestamp": 1702456789
}
```

---

## Performance Monitoring

### Tracked Metrics

```php
$performance = $service->getPerformance();

// Per agent:
[
    'react' => [
        'attempts' => 15,
        'successes' => 12,
        'failures' => 3,
        'average_quality' => 7.8,
        'total_duration' => 45.6,
    ],
    'reflection' => [
        'attempts' => 8,
        'successes' => 8,
        'failures' => 0,
        'average_quality' => 9.1,
        'total_duration' => 68.4,
    ],
]
```

### Using Performance Data

```php
// Find best performing agent
$bestAgent = null;
$bestScore = 0;

foreach ($performance as $agentId => $stats) {
    if ($stats['attempts'] > 0) {
        $score = $stats['average_quality'];
        if ($score > $bestScore) {
            $bestScore = $score;
            $bestAgent = $agentId;
        }
    }
}

echo "Best agent: {$bestAgent} ({$bestScore}/10)\n";
```

### Logging

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('adaptive');
$logger->pushHandler(new StreamHandler('logs/adaptive.log', Logger::DEBUG));

$service = new AdaptiveAgentService($client, [
    'logger' => $logger,
]);

// All operations logged:
// - Task analysis
// - Agent selection (with scores)
// - Validation results
// - Adaptation decisions
```

---

## Production Patterns

### Pattern 1: Customer Support Router

```php
$service = new AdaptiveAgentService($client);

// FAQ agent (fast, simple queries)
$service->registerAgent('faq', $reflexAgent, [
    'complexity_level' => 'simple',
    'speed' => 'fast',
    'best_for' => ['common questions', 'quick answers'],
]);

// Dialog agent (conversations)
$service->registerAgent('dialog', $dialogAgent, [
    'complexity_level' => 'medium',
    'speed' => 'medium',
    'best_for' => ['support conversations', 'multi-turn'],
]);

// Technical agent (complex issues)
$service->registerAgent('technical', $reactAgent, [
    'complexity_level' => 'complex',
    'quality' => 'high',
    'best_for' => ['technical troubleshooting', 'debugging'],
]);

// Automatic routing
$response = $service->run($customerQuestion);
```

### Pattern 2: Content Generation Pipeline

```php
$service = new AdaptiveAgentService($client, [
    'quality_threshold' => 8.0,  // High quality for content
]);

// Draft agent (fast first draft)
$service->registerAgent('draft', $reactAgent, [
    'speed' => 'fast',
    'quality' => 'standard',
    'best_for' => ['initial drafts', 'brainstorming'],
]);

// Editor agent (quality refinement)
$service->registerAgent('editor', $reflectionAgent, [
    'speed' => 'medium',
    'quality' => 'high',
    'best_for' => ['editing', 'polishing', 'refinement'],
]);

// Premium agent (critical content)
$service->registerAgent('premium', $makerAgent, [
    'speed' => 'slow',
    'quality' => 'extreme',
    'best_for' => ['critical content', 'flagship pieces'],
]);

// Automatic quality-based selection
$article = $service->run("Write an article about {$topic}");
```

### Pattern 3: Code Review System

```php
$service = new AdaptiveAgentService($client);

// Syntax checker (fast)
$service->registerAgent('syntax', $reflexAgent, [...]);

// Logic analyzer (medium)
$service->registerAgent('logic', $reactAgent, [...]);

// Security auditor (thorough)
$service->registerAgent('security', $reflectionAgent, [...]);

$review = $service->run("Review this code:\n{$code}");
```

### Pattern 4: Research Assistant

```php
$service = new AdaptiveAgentService($client);

// Quick search (RAG)
$service->registerAgent('search', $ragAgent, [...]);

// Deep research (Plan+Execute)
$service->registerAgent('research', $planExecuteAgent, [...]);

// Synthesis (Reflection)
$service->registerAgent('synthesize', $reflectionAgent, [...]);

$research = $service->run("Research and summarize: {$topic}");
```

---

## Best Practices

### 1. Register Diverse Agents

Include agents with complementary strengths:

```php
// Speed tier
$service->registerAgent('fast', $reflexAgent, [...]);

// Quality tier
$service->registerAgent('quality', $reflectionAgent, [...]);

// Scale tier
$service->registerAgent('scale', $makerAgent, [...]);

// Specialty tier
$service->registerAgent('rag', $ragAgent, [...]);
```

### 2. Set Appropriate Thresholds

```php
// Production (high quality required)
$service = new AdaptiveAgentService($client, [
    'quality_threshold' => 8.0,
    'max_attempts' => 5,
]);

// Experimentation (more lenient)
$service = new AdaptiveAgentService($client, [
    'quality_threshold' => 6.0,
    'max_attempts' => 2,
]);
```

### 3. Profile Agents Accurately

Be honest about capabilities:

```php
// ❌ Don't over-promise
$service->registerAgent('simple_agent', $agent, [
    'complexity_level' => 'extreme',  // Too high
    'quality' => 'extreme',           // Unrealistic
]);

// ✓ Be realistic
$service->registerAgent('simple_agent', $agent, [
    'complexity_level' => 'simple',
    'quality' => 'standard',
]);
```

### 4. Monitor and Adjust

```php
// Regularly check performance
$performance = $service->getPerformance();

// Identify underperformers
foreach ($performance as $agentId => $stats) {
    if ($stats['attempts'] > 5 && $stats['average_quality'] < 6.0) {
        echo "Warning: {$agentId} underperforming\n";
        // Adjust profile or replace agent
    }
}
```

### 5. Build Initial History

```php
// Run 10-20 diverse tasks to build baseline
$tasks = [
    'Simple calculation',
    'Write an email',
    'Research a topic',
    'Logical reasoning',
    // ... mix of different task types
];

foreach ($tasks as $task) {
    $service->run($task);
}

// k-NN system now has learning data
```

---

## Common Pitfalls

### Pitfall 1: Too Few Agents

```php
// ❌ Only one agent registered
$service->registerAgent('react', $reactAgent, [...]);

$result = $service->run($anyTask);
// No choice, always uses react agent
// No adaptation possible
```

**Solution:** Register multiple specialized agents.

### Pitfall 2: Identical Profiles

```php
// ❌ All agents have same profile
$service->registerAgent('agent1', $a1, ['complexity_level' => 'medium']);
$service->registerAgent('agent2', $a2, ['complexity_level' => 'medium']);
$service->registerAgent('agent3', $a3, ['complexity_level' => 'medium']);

// Selection becomes random
```

**Solution:** Differentiate profiles based on actual capabilities.

### Pitfall 3: Unrealistic Thresholds

```php
// ❌ Threshold too high for agent capabilities
$service = new AdaptiveAgentService($client, [
    'quality_threshold' => 9.5,  // Near-perfect
]);

// Standard agents rarely meet this
// Constant retries, high costs
```

**Solution:** Match threshold to agent quality levels.

### Pitfall 4: Ignoring k-NN Data

```php
// ❌ Disabling learning unnecessarily
$service = new AdaptiveAgentService($client, [
    'enable_knn' => false,  // Why?
]);

// Miss out on improving over time
```

**Solution:** Keep k-NN enabled for production systems.

### Pitfall 5: No Monitoring

```php
// ❌ Fire and forget
$result = $service->run($task);
// No tracking, no improvement
```

**Solution:** Monitor performance and history stats.

---

## Summary

The **AdaptiveAgentService** provides intelligent orchestration for multi-agent systems. It eliminates manual agent selection, ensures quality through automatic validation, and continuously improves through k-NN machine learning.

### Key Takeaways

1. **Intelligent Selection**: Task analysis + capability matching + historical learning
2. **Quality Validation**: Automatic quality scoring on every result
3. **Adaptive Retry**: Try different agents or reframe requests on failure
4. **Continuous Learning**: k-NN system improves with every execution
5. **Production-Ready**: Performance tracking, logging, and monitoring built-in

### When to Use Adaptive Agent Selection

✅ **Use when:**

- You have multiple specialized agents
- Quality is critical
- Tasks vary significantly
- You want automatic optimization
- Long-term learning is valuable

❌ **Don't use when:**

- Only one agent type
- Simplicity is priority
- Task types are identical
- Manual control is needed

### Performance vs Cost

| Approach | Token Cost | Quality | Latency | Learning |
|----------|------------|---------|---------|----------|
| Manual Selection | Low | Variable | Low | None |
| Rule-Based | Medium | Good | Medium | None |
| Adaptive (k-NN) | Medium-High | High | Medium | Yes |

**Recommendation:** Use Adaptive for production systems where quality and reliability matter.

---

## Next Steps

In **Part 5: Production Engineering**, you'll learn how to:

- Instrument agents with comprehensive observability
- Build evaluation harnesses for quality assurance
- Optimize performance and token costs
- Implement async and concurrent execution patterns

For now, practice adaptive agent selection:

1. Register 3-5 diverse agents with accurate profiles
2. Run 20+ varied tasks to build initial k-NN history
3. Monitor performance metrics and success rates
4. Adjust quality thresholds based on results
5. Compare k-NN vs rule-based selection confidence

### Additional Resources

- [AdaptiveAgentService Source](https://github.com/claude-php/claude-php-agent/blob/main/src/Agents/AdaptiveAgentService.php)
- [k-NN Learning Guide](https://github.com/claude-php/claude-php-agent/blob/main/docs/knn-learning.md)
- [Adaptive Agent Documentation](https://github.com/claude-php/claude-php-agent/blob/main/docs/adaptive-agent-service.md)
- [Example Implementation](https://github.com/claude-php/claude-php-agent/blob/main/examples/adaptive_agent_service_example.php)

---

## Exercises

### Exercise 1: Build a Multi-Agent Support System

Create an adaptive service with 4 agents:

- FAQ agent (simple, fast)
- Dialog agent (conversational)
- Technical agent (complex issues)
- Escalation agent (critical problems)

Test with 10 diverse support queries and monitor which agents are selected.

### Exercise 2: Quality-Driven Content Pipeline

Build a system that automatically selects agents based on content criticality:

- Blog posts → Standard agent
- Marketing copy → High-quality agent
- Legal documents → Extreme-quality agent

Set appropriate thresholds and measure quality scores.

### Exercise 3: Learning Curve Analysis

Track how k-NN performance improves over time:

- Run 50 tasks (10 each of 5 types)
- Record confidence scores over time
- Plot learning curve
- Analyze when confidence stabilizes

### Exercise 4: Profile Optimization

Start with generic profiles, then refine based on performance:

- Register 3 agents with initial profiles
- Run 20 tasks
- Analyze performance metrics
- Adjust profiles based on actual strengths
- Measure improvement

---

**You now have intelligent, self-improving agent orchestration. The AdaptiveAgentService makes multi-agent systems reliable, high-quality, and continuously optimizing.**
