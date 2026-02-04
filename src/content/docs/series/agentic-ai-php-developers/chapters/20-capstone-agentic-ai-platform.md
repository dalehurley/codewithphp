---
title: "20: Capstone: Build an Agentic AI Platform"
description: "Combine everything into a full system: tool registry, memory, RAG, planning, and multi-agent orchestration. Build a real product-ready agentic application."
series: "agentic-ai-php-developers"
chapter: 20
difficulty: "Advanced"
keywords: ["Agentic AI Platform", "Production AI System", "Multi-Agent Platform", "Agent Orchestration", "Production Architecture"]
teaches: ["Complete agentic AI platform architecture", "Tool registry with permissioning and validation", "Integrated memory and RAG systems", "Multi-agent orchestration at scale", "Production monitoring and evaluation", "Agent lifecycle management", "Real-world deployment patterns"]
estimatedTime: "PT240M"
---

# Chapter 20: Capstone: Build an Agentic AI Platform

## Overview

You've learned the pieces. Now assemble the platform. This **capstone chapter** brings together every concept from the series — tool systems, memory, RAG, planning, reflection, multi-agent orchestration, observability, evaluation, and optimization — into a single, production-ready **Agentic AI Platform** built with [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent).

The platform you'll build isn't a toy demo. It's a **real production system** designed to power commercial AI products. It includes centralized tool management, persistent memory across sessions, knowledge retrieval, intelligent planning, multi-agent coordination, comprehensive monitoring, continuous evaluation, and operational controls. This is the culmination of everything you've learned — a complete, deployable agent infrastructure.

In this chapter you'll build:

- **Tool Registry** — Centralized tool catalog with permissions, validation, and usage tracking
- **Memory System** — Short-term conversation state + long-term knowledge storage with RAG
- **Agent Hub** — Registry of specialized agents with capability profiles and selection logic
- **Orchestrator** — Master coordinator that routes tasks, manages handoffs, and tracks execution
- **Evaluation Harness** — Continuous quality, safety, and cost monitoring with automated regression tests
- **Monitoring Stack** — Structured logging, distributed tracing, and metrics dashboards
- **Admin Interface** — Management APIs for configuration, debugging, and operations
- **Production Deployment** — Docker configuration, scaling strategies, and operational runbooks

**Estimated time:** ~4 hours

::: info Framework Version
This chapter is based on [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent) **v0.5+**. This is the complete integration of all framework capabilities.
:::

::: info Code examples
Complete, runnable examples for this chapter:

- [`01-tool-registry-system.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/20-capstone-agentic-ai-platform/01-tool-registry-system.php) — Centralized tool catalog with permissions
- [`02-memory-rag-integration.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/20-capstone-agentic-ai-platform/02-memory-rag-integration.php) — Combined memory and RAG system
- [`03-agent-hub-registry.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/20-capstone-agentic-ai-platform/03-agent-hub-registry.php) — Agent registration and capability management
- [`04-platform-orchestrator.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/20-capstone-agentic-ai-platform/04-platform-orchestrator.php) — Master coordination and task routing
- [`05-evaluation-monitoring-stack.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/20-capstone-agentic-ai-platform/05-evaluation-monitoring-stack.php) — Comprehensive quality and performance tracking
- [`06-admin-management-api.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/20-capstone-agentic-ai-platform/06-admin-management-api.php) — Platform administration interface
- [`07-complete-platform.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/20-capstone-agentic-ai-platform/07-complete-platform.php) — Full integrated platform

All files are in [`code/20-capstone-agentic-ai-platform/`](https://github.com/dalehurley/codewithphp/tree/main/code/agentic-ai-php-developers/20-capstone-agentic-ai-platform).
:::

---

## Platform Architecture

Before diving into implementation, let's understand the complete system architecture:

```text
┌─────────────────────────────────────────────────────────────────┐
│                    AGENTIC AI PLATFORM                           │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────────────────┐     ┌──────────────────────────────────┐  │
│  │  API Gateway    │────▶│  Platform Orchestrator            │  │
│  │  (Entry Point)  │     │  • Task routing                   │  │
│  └─────────────────┘     │  • Agent selection                │  │
│                          │  • Execution coordination         │  │
│                          │  • Result aggregation             │  │
│                          └───────────┬──────────────────────┘  │
│                                      │                          │
│  ┌────────────────────────────────┬──┴──┬────────────────────┐ │
│  │                                │     │                     │ │
│  ▼                                ▼     ▼                     ▼ │
│ ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────┴──┐
│ │ Agent Hub    │  │ Tool Registry│  │ Memory/RAG   │  │ Planning│
│ │              │  │              │  │  System      │  │ System  │
│ │ • Research   │  │ • Calculator │  │ • Conv State │  │ • Tasks │
│ │ • Code Gen   │  │ • Search     │  │ • LT Memory  │  │ • Steps │
│ │ • QA         │  │ • Database   │  │ • RAG Index  │  │ • Status│
│ │ • Content    │  │ • Email      │  │ • Retrieval  │  │         │
│ └──────────────┘  └──────────────┘  └──────────────┘  └─────────┘
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  Observability & Evaluation Layer                        │  │
│  │  • Structured Logging  • Distributed Tracing             │  │
│  │  • Metrics & Alerts    • Quality Evaluation              │  │
│  │  • Cost Tracking       • Safety Checks                   │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                  │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  Storage Layer                                            │  │
│  │  • PostgreSQL (memory, audit logs, analytics)            │  │
│  │  • Redis (cache, sessions, queues)                       │  │
│  │  • Vector DB (embeddings for RAG)                        │  │
│  └──────────────────────────────────────────────────────────┘  │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

### Key Components

**1. Platform Orchestrator**
The brain of the system. Routes incoming tasks to appropriate agents, coordinates multi-agent workflows, manages execution state, and aggregates results.

**2. Agent Hub**
Registry of all available agents with capability profiles, performance stats, and availability status. Supports dynamic agent registration and selection.

**3. Tool Registry**
Centralized catalog of all tools with JSON schemas, permission controls, rate limits, and execution tracking.

**4. Memory & RAG System**
Combined short-term conversation memory and long-term knowledge storage with semantic retrieval.

**5. Planning System**
Task decomposition, step generation, progress tracking, and adaptive replanning.

**6. Observability Stack**
Logging, tracing, metrics, and dashboards for production monitoring.

**7. Evaluation Harness**
Continuous quality testing, safety validation, and cost optimization.

---

## Component 1: Tool Registry System

The **Tool Registry** is your centralized tool catalog with permission controls, usage tracking, and validation.

### Implementation

```php
<?php

declare(strict_types=1);

namespace AgenticPlatform\Tools;

use ClaudeAgents\Tools\Tool;
use ClaudeAgents\Exceptions\ToolException;

/**
 * Tool Registry - Centralized tool management with permissions and tracking
 */
class ToolRegistry
{
    private array $tools = [];
    private array $permissions = [];
    private array $usageStats = [];
    private array $rateLimits = [];

    public function register(
        Tool $tool,
        array $allowedAgents = [],
        ?int $rateLimit = null
    ): void {
        $name = $tool->getName();
        
        $this->tools[$name] = [
            'tool' => $tool,
            'registered_at' => time(),
            'version' => '1.0',
        ];
        
        $this->permissions[$name] = [
            'allowed_agents' => $allowedAgents, // Empty = all agents
            'requires_approval' => false,
        ];
        
        if ($rateLimit) {
            $this->rateLimits[$name] = [
                'max_calls' => $rateLimit,
                'window' => 3600, // 1 hour
                'calls' => [],
            ];
        }
        
        $this->usageStats[$name] = [
            'total_calls' => 0,
            'successful_calls' => 0,
            'failed_calls' => 0,
            'total_duration' => 0,
        ];
    }

    public function getTool(string $name, string $agentId): Tool
    {
        if (!isset($this->tools[$name])) {
            throw new ToolException("Tool not found: {$name}");
        }

        // Check permissions
        $permissions = $this->permissions[$name];
        if (!empty($permissions['allowed_agents']) 
            && !in_array($agentId, $permissions['allowed_agents'])
        ) {
            throw new ToolException(
                "Agent {$agentId} not authorized to use tool: {$name}"
            );
        }

        // Check rate limits
        if (isset($this->rateLimits[$name])) {
            $this->enforceRateLimit($name);
        }

        return $this->tools[$name]['tool'];
    }

    public function getToolsForAgent(string $agentId): array
    {
        $availableTools = [];
        
        foreach ($this->tools as $name => $data) {
            $permissions = $this->permissions[$name];
            
            // If no restrictions or agent is in allowed list
            if (empty($permissions['allowed_agents']) 
                || in_array($agentId, $permissions['allowed_agents'])
            ) {
                $availableTools[] = $data['tool'];
            }
        }
        
        return $availableTools;
    }

    public function trackUsage(
        string $toolName,
        bool $success,
        float $duration
    ): void {
        if (!isset($this->usageStats[$toolName])) {
            return;
        }

        $stats = &$this->usageStats[$toolName];
        $stats['total_calls']++;
        
        if ($success) {
            $stats['successful_calls']++;
        } else {
            $stats['failed_calls']++;
        }
        
        $stats['total_duration'] += $duration;
    }

    public function getUsageStats(?string $toolName = null): array
    {
        if ($toolName) {
            return $this->usageStats[$toolName] ?? [];
        }
        
        return $this->usageStats;
    }

    private function enforceRateLimit(string $toolName): void
    {
        $limit = &$this->rateLimits[$toolName];
        $now = time();
        
        // Remove old calls outside window
        $limit['calls'] = array_filter(
            $limit['calls'],
            fn($timestamp) => ($now - $timestamp) < $limit['window']
        );
        
        if (count($limit['calls']) >= $limit['max_calls']) {
            throw new ToolException(
                "Rate limit exceeded for tool: {$toolName}"
            );
        }
        
        $limit['calls'][] = $now;
    }

    public function listTools(): array
    {
        return array_map(
            fn($data) => [
                'name' => $data['tool']->getName(),
                'description' => $data['tool']->getDescription(),
                'registered_at' => $data['registered_at'],
            ],
            $this->tools
        );
    }
}
```

This registry provides:

- **Centralized Management** — Single source of truth for all tools
- **Permission Control** — Agent-level access restrictions
- **Rate Limiting** — Prevent tool abuse
- **Usage Tracking** — Monitor tool performance and adoption
- **Validation** — Schema enforcement and error handling

See [`01-tool-registry-system.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/20-capstone-agentic-ai-platform/01-tool-registry-system.php) for the complete implementation with example tools.

---

## Component 2: Memory + RAG Integration

The **Memory & RAG System** combines short-term conversation state with long-term knowledge retrieval.

### Architecture

```text
┌────────────────────────────────────────────────────────────┐
│                  MEMORY + RAG SYSTEM                        │
├────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌──────────────────────┐      ┌──────────────────────┐   │
│  │  Short-Term Memory   │      │  Long-Term Memory    │   │
│  │  (Conversation)      │      │  (Knowledge Base)    │   │
│  │                      │      │                      │   │
│  │  • Session state     │      │  • Facts             │   │
│  │  • Recent turns      │      │  • Documents         │   │
│  │  • Context window    │      │  • Embeddings        │   │
│  │  • Auto-prune        │      │  • Semantic search   │   │
│  └──────────────────────┘      └──────────────────────┘   │
│           │                              │                 │
│           └──────────┬───────────────────┘                 │
│                      ▼                                     │
│          ┌────────────────────────┐                        │
│          │  Memory Coordinator    │                        │
│          │  • Query routing       │                        │
│          │  • Relevance scoring   │                        │
│          │  • Context assembly    │                        │
│          └────────────────────────┘                        │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### Implementation

```php
<?php

declare(strict_types=1);

namespace AgenticPlatform\Memory;

use ClaudeAgents\Memory\ConversationMemory;
use ClaudeAgents\Memory\VectorMemory;
use ClaudeAgents\RAG\EmbeddingService;
use ClaudeAgents\RAG\VectorStore;

/**
 * Integrated Memory + RAG System
 */
class MemoryRAGSystem
{
    private ConversationMemory $conversationMemory;
    private VectorMemory $longTermMemory;
    private EmbeddingService $embeddings;
    private VectorStore $vectorStore;

    public function __construct(
        ConversationMemory $conversationMemory,
        VectorMemory $longTermMemory,
        EmbeddingService $embeddings,
        VectorStore $vectorStore
    ) {
        $this->conversationMemory = $conversationMemory;
        $this->longTermMemory = $longTermMemory;
        $this->embeddings = $embeddings;
        $this->vectorStore = $vectorStore;
    }

    /**
     * Store conversation turn in short-term memory
     */
    public function storeConversation(
        string $sessionId,
        string $role,
        string $content
    ): void {
        $this->conversationMemory->add($sessionId, [
            'role' => $role,
            'content' => $content,
            'timestamp' => time(),
        ]);
    }

    /**
     * Store knowledge in long-term memory with embeddings
     */
    public function storeKnowledge(
        string $content,
        array $metadata = []
    ): void {
        $embedding = $this->embeddings->embed($content);
        
        $this->vectorStore->store([
            'content' => $content,
            'embedding' => $embedding,
            'metadata' => array_merge($metadata, [
                'stored_at' => time(),
            ]),
        ]);
        
        $this->longTermMemory->store($content, $metadata);
    }

    /**
     * Retrieve relevant context for a query
     */
    public function retrieveContext(
        string $query,
        string $sessionId,
        int $maxResults = 5
    ): array {
        $context = [];
        
        // Get recent conversation history
        $conversationHistory = $this->conversationMemory->get(
            $sessionId,
            limit: 10
        );
        
        if (!empty($conversationHistory)) {
            $context['conversation'] = [
                'source' => 'short_term_memory',
                'items' => $conversationHistory,
            ];
        }
        
        // Retrieve relevant long-term knowledge
        $queryEmbedding = $this->embeddings->embed($query);
        $relevant = $this->vectorStore->search(
            $queryEmbedding,
            limit: $maxResults
        );
        
        if (!empty($relevant)) {
            $context['knowledge'] = [
                'source' => 'long_term_memory',
                'items' => $relevant,
            ];
        }
        
        return $context;
    }

    /**
     * Consolidate session into long-term knowledge
     */
    public function consolidateSession(string $sessionId): void
    {
        $history = $this->conversationMemory->get($sessionId);
        
        if (empty($history)) {
            return;
        }
        
        // Extract key facts and decisions
        $summary = $this->extractKeyPoints($history);
        
        // Store in long-term memory
        foreach ($summary as $fact) {
            $this->storeKnowledge($fact, [
                'session_id' => $sessionId,
                'type' => 'consolidated_fact',
            ]);
        }
    }

    private function extractKeyPoints(array $history): array
    {
        // In production, use an LLM to extract key facts
        // For now, simple extraction
        $facts = [];
        
        foreach ($history as $turn) {
            if ($turn['role'] === 'assistant' && strlen($turn['content']) > 100) {
                $facts[] = $turn['content'];
            }
        }
        
        return $facts;
    }

    public function clearSession(string $sessionId): void
    {
        $this->conversationMemory->clear($sessionId);
    }

    public function getStats(): array
    {
        return [
            'conversation_sessions' => $this->conversationMemory->countSessions(),
            'long_term_items' => $this->longTermMemory->count(),
            'vector_store_size' => $this->vectorStore->count(),
        ];
    }
}
```

See [`02-memory-rag-integration.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/20-capstone-agentic-ai-platform/02-memory-rag-integration.php) for the complete implementation.

---

## Component 3: Agent Hub & Registry

The **Agent Hub** maintains a registry of all specialized agents with their capabilities and performance profiles.

### Agent Profiles

Each agent in the hub has:

- **Capabilities** — What the agent can do (e.g., code_generation, research, qa)
- **Tools** — Which tools the agent has access to
- **Performance Stats** — Success rate, avg latency, cost per task
- **Availability** — Online status, current load
- **Specializations** — Domains of expertise

### Implementation

```php
<?php

declare(strict_types=1);

namespace AgenticPlatform\Agents;

use ClaudeAgents\Agent;
use ClaudePhp\ClaudePhp;

/**
 * Agent Hub - Registry of specialized agents
 */
class AgentHub
{
    private array $agents = [];
    private array $profiles = [];
    private array $performanceStats = [];

    public function registerAgent(
        string $id,
        Agent $agent,
        array $capabilities,
        array $specializations = []
    ): void {
        $this->agents[$id] = $agent;
        
        $this->profiles[$id] = [
            'id' => $id,
            'capabilities' => $capabilities,
            'specializations' => $specializations,
            'registered_at' => time(),
            'status' => 'available',
        ];
        
        $this->performanceStats[$id] = [
            'tasks_completed' => 0,
            'tasks_failed' => 0,
            'avg_latency' => 0,
            'total_cost' => 0,
        ];
    }

    public function selectAgent(
        string $taskType,
        array $requirements = []
    ): ?Agent {
        $scores = [];
        
        foreach ($this->profiles as $id => $profile) {
            if ($profile['status'] !== 'available') {
                continue;
            }
            
            $score = $this->scoreAgent($id, $taskType, $requirements);
            
            if ($score > 0) {
                $scores[$id] = $score;
            }
        }
        
        if (empty($scores)) {
            return null;
        }
        
        // Select agent with highest score
        arsort($scores);
        $selectedId = array_key_first($scores);
        
        return $this->agents[$selectedId];
    }

    private function scoreAgent(
        string $agentId,
        string $taskType,
        array $requirements
    ): float {
        $profile = $this->profiles[$agentId];
        $stats = $this->performanceStats[$agentId];
        
        $score = 0;
        
        // Capability match
        if (in_array($taskType, $profile['capabilities'])) {
            $score += 50;
        }
        
        // Specialization bonus
        foreach ($requirements as $requirement) {
            if (in_array($requirement, $profile['specializations'])) {
                $score += 20;
            }
        }
        
        // Performance bonus
        if ($stats['tasks_completed'] > 0) {
            $successRate = $stats['tasks_completed'] 
                / ($stats['tasks_completed'] + $stats['tasks_failed']);
            $score += $successRate * 30;
        }
        
        return $score;
    }

    public function trackPerformance(
        string $agentId,
        bool $success,
        float $latency,
        float $cost
    ): void {
        if (!isset($this->performanceStats[$agentId])) {
            return;
        }
        
        $stats = &$this->performanceStats[$agentId];
        
        if ($success) {
            $stats['tasks_completed']++;
        } else {
            $stats['tasks_failed']++;
        }
        
        // Update moving average for latency
        $total = $stats['tasks_completed'] + $stats['tasks_failed'];
        $stats['avg_latency'] = (
            ($stats['avg_latency'] * ($total - 1)) + $latency
        ) / $total;
        
        $stats['total_cost'] += $cost;
    }

    public function getAgent(string $id): ?Agent
    {
        return $this->agents[$id] ?? null;
    }

    public function listAgents(?string $capability = null): array
    {
        if (!$capability) {
            return $this->profiles;
        }
        
        return array_filter(
            $this->profiles,
            fn($p) => in_array($capability, $p['capabilities'])
        );
    }

    public function getPerformanceStats(string $agentId): array
    {
        return $this->performanceStats[$agentId] ?? [];
    }
}
```

See [`03-agent-hub-registry.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/20-capstone-agentic-ai-platform/03-agent-hub-registry.php) for the complete implementation with example agents.

---

## Component 4: Platform Orchestrator

The **Platform Orchestrator** is the master coordinator that routes tasks, selects agents, manages execution, and aggregates results.

### Key Responsibilities

1. **Task Analysis** — Understand what needs to be done
2. **Agent Selection** — Choose the right agent for the job
3. **Execution Management** — Run tasks with monitoring
4. **Result Aggregation** — Combine outputs from multiple agents
5. **Error Handling** — Retry logic, fallbacks, and graceful degradation

### Implementation

```php
<?php

declare(strict_types=1);

namespace AgenticPlatform\Core;

use AgenticPlatform\Agents\AgentHub;
use AgenticPlatform\Tools\ToolRegistry;
use AgenticPlatform\Memory\MemoryRAGSystem;
use ClaudeAgents\Agent;

/**
 * Platform Orchestrator - Master coordinator for the platform
 */
class PlatformOrchestrator
{
    private AgentHub $agentHub;
    private ToolRegistry $toolRegistry;
    private MemoryRAGSystem $memory;
    private array $executionLog = [];

    public function __construct(
        AgentHub $agentHub,
        ToolRegistry $toolRegistry,
        MemoryRAGSystem $memory
    ) {
        $this->agentHub = $agentHub;
        $this->toolRegistry = $toolRegistry;
        $this->memory = $memory;
    }

    /**
     * Execute a task using the platform
     */
    public function executeTask(
        string $taskDescription,
        string $sessionId,
        array $options = []
    ): array {
        $executionId = uniqid('exec_', true);
        $startTime = microtime(true);
        
        $this->log($executionId, 'started', [
            'task' => $taskDescription,
            'session' => $sessionId,
        ]);
        
        try {
            // Analyze task
            $taskAnalysis = $this->analyzeTask($taskDescription);
            
            // Retrieve relevant context
            $context = $this->memory->retrieveContext(
                $taskDescription,
                $sessionId
            );
            
            // Select appropriate agent
            $agent = $this->agentHub->selectAgent(
                $taskAnalysis['type'],
                $taskAnalysis['requirements']
            );
            
            if (!$agent) {
                throw new \RuntimeException('No suitable agent available');
            }
            
            // Get tools for this agent
            $tools = $this->toolRegistry->getToolsForAgent(
                $taskAnalysis['agent_id'] ?? 'default'
            );
            
            // Configure agent with tools
            foreach ($tools as $tool) {
                $agent->withTool($tool);
            }
            
            // Build enriched prompt with context
            $enrichedPrompt = $this->buildPromptWithContext(
                $taskDescription,
                $context
            );
            
            // Execute
            $result = $agent->run($enrichedPrompt);
            
            // Store in memory
            $this->memory->storeConversation(
                $sessionId,
                'user',
                $taskDescription
            );
            $this->memory->storeConversation(
                $sessionId,
                'assistant',
                $result->getAnswer()
            );
            
            // Track performance
            $duration = microtime(true) - $startTime;
            $this->agentHub->trackPerformance(
                $taskAnalysis['agent_id'] ?? 'default',
                true,
                $duration,
                $result->getTotalTokens() * 0.000015 // Estimate cost
            );
            
            $this->log($executionId, 'completed', [
                'duration' => $duration,
                'tokens' => $result->getTotalTokens(),
            ]);
            
            return [
                'success' => true,
                'execution_id' => $executionId,
                'result' => $result->getAnswer(),
                'metadata' => [
                    'agent_used' => $taskAnalysis['agent_id'] ?? 'default',
                    'duration' => $duration,
                    'tokens' => $result->getTotalTokens(),
                    'tool_calls' => count($result->getToolCalls()),
                ],
            ];
            
        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;
            
            $this->log($executionId, 'failed', [
                'error' => $e->getMessage(),
                'duration' => $duration,
            ]);
            
            return [
                'success' => false,
                'execution_id' => $executionId,
                'error' => $e->getMessage(),
                'metadata' => [
                    'duration' => $duration,
                ],
            ];
        }
    }

    /**
     * Execute multi-agent workflow
     */
    public function executeWorkflow(
        array $tasks,
        string $sessionId
    ): array {
        $results = [];
        
        foreach ($tasks as $task) {
            $result = $this->executeTask(
                $task['description'],
                $sessionId,
                $task['options'] ?? []
            );
            
            $results[] = $result;
            
            // Stop on first failure if not configured to continue
            if (!$result['success'] 
                && !($task['continue_on_failure'] ?? false)
            ) {
                break;
            }
        }
        
        return [
            'workflow_completed' => true,
            'total_tasks' => count($tasks),
            'successful_tasks' => count(array_filter(
                $results,
                fn($r) => $r['success']
            )),
            'results' => $results,
        ];
    }

    private function analyzeTask(string $task): array
    {
        // Simple keyword-based analysis
        // In production, use an LLM for better analysis
        
        $task = strtolower($task);
        
        if (str_contains($task, 'code') || str_contains($task, 'implement')) {
            return [
                'type' => 'code_generation',
                'requirements' => ['programming'],
                'agent_id' => 'code_generator',
            ];
        }
        
        if (str_contains($task, 'research') || str_contains($task, 'find')) {
            return [
                'type' => 'research',
                'requirements' => ['web_search', 'analysis'],
                'agent_id' => 'researcher',
            ];
        }
        
        if (str_contains($task, 'review') || str_contains($task, 'check')) {
            return [
                'type' => 'quality_assurance',
                'requirements' => ['validation'],
                'agent_id' => 'qa_agent',
            ];
        }
        
        return [
            'type' => 'general',
            'requirements' => [],
            'agent_id' => 'general_agent',
        ];
    }

    private function buildPromptWithContext(
        string $task,
        array $context
    ): string {
        $prompt = $task;
        
        if (!empty($context['conversation'])) {
            $prompt .= "\n\nRecent conversation context:\n";
            foreach ($context['conversation']['items'] as $turn) {
                $prompt .= "- {$turn['role']}: {$turn['content']}\n";
            }
        }
        
        if (!empty($context['knowledge'])) {
            $prompt .= "\n\nRelevant knowledge:\n";
            foreach ($context['knowledge']['items'] as $item) {
                $prompt .= "- {$item['content']}\n";
            }
        }
        
        return $prompt;
    }

    private function log(
        string $executionId,
        string $event,
        array $data
    ): void {
        $this->executionLog[] = [
            'execution_id' => $executionId,
            'event' => $event,
            'data' => $data,
            'timestamp' => microtime(true),
        ];
    }

    public function getExecutionLog(?string $executionId = null): array
    {
        if ($executionId) {
            return array_filter(
                $this->executionLog,
                fn($log) => $log['execution_id'] === $executionId
            );
        }
        
        return $this->executionLog;
    }
}
```

See [`04-platform-orchestrator.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/20-capstone-agentic-ai-platform/04-platform-orchestrator.php) for the complete implementation.

---

## Component 5: Evaluation & Monitoring Stack

Production systems need continuous evaluation and comprehensive monitoring.

### Evaluation Dimensions

1. **Quality** — Accuracy, completeness, relevance
2. **Safety** — No harmful content, PII leaks, or policy violations
3. **Cost** — Token usage, API costs, efficiency
4. **Performance** — Latency, throughput, reliability

### Implementation

```php
<?php

declare(strict_types=1);

namespace AgenticPlatform\Monitoring;

/**
 * Evaluation and Monitoring Stack
 */
class EvaluationMonitoringStack
{
    private array $metrics = [];
    private array $evaluations = [];
    private array $alerts = [];

    /**
     * Evaluate execution result
     */
    public function evaluate(array $execution): array
    {
        $scores = [
            'quality' => $this->evaluateQuality($execution),
            'safety' => $this->evaluateSafety($execution),
            'cost' => $this->evaluateCost($execution),
            'performance' => $this->evaluatePerformance($execution),
        ];
        
        $overallScore = array_sum($scores) / count($scores);
        
        $evaluation = [
            'execution_id' => $execution['execution_id'],
            'timestamp' => time(),
            'scores' => $scores,
            'overall' => $overallScore,
            'passed' => $overallScore >= 0.7, // 70% threshold
        ];
        
        $this->evaluations[] = $evaluation;
        
        // Check for alerts
        $this->checkAlerts($evaluation);
        
        return $evaluation;
    }

    private function evaluateQuality(array $execution): float
    {
        // In production, use LLM-based evaluation
        // For now, simple heuristics
        
        $score = 1.0;
        
        // Penalize if result is too short
        if (isset($execution['result']) 
            && strlen($execution['result']) < 50
        ) {
            $score -= 0.3;
        }
        
        // Penalize if failed
        if (!$execution['success']) {
            $score -= 0.5;
        }
        
        return max(0, $score);
    }

    private function evaluateSafety(array $execution): float
    {
        // Check for safety issues
        $result = $execution['result'] ?? '';
        
        $bannedPatterns = [
            '/password/i',
            '/secret/i',
            '/api[_-]?key/i',
            '/credit[_-]?card/i',
        ];
        
        foreach ($bannedPatterns as $pattern) {
            if (preg_match($pattern, $result)) {
                return 0.0; // Critical safety failure
            }
        }
        
        return 1.0;
    }

    private function evaluateCost(array $execution): float
    {
        $tokens = $execution['metadata']['tokens'] ?? 0;
        
        // Score based on efficiency
        if ($tokens < 500) {
            return 1.0;
        } elseif ($tokens < 1000) {
            return 0.8;
        } elseif ($tokens < 2000) {
            return 0.6;
        } else {
            return 0.4;
        }
    }

    private function evaluatePerformance(array $execution): float
    {
        $duration = $execution['metadata']['duration'] ?? 0;
        
        // Score based on latency
        if ($duration < 2.0) {
            return 1.0;
        } elseif ($duration < 5.0) {
            return 0.8;
        } elseif ($duration < 10.0) {
            return 0.6;
        } else {
            return 0.4;
        }
    }

    private function checkAlerts(array $evaluation): void
    {
        // Alert on low overall score
        if ($evaluation['overall'] < 0.5) {
            $this->alerts[] = [
                'level' => 'warning',
                'message' => 'Low evaluation score',
                'execution_id' => $evaluation['execution_id'],
                'score' => $evaluation['overall'],
                'timestamp' => time(),
            ];
        }
        
        // Alert on safety failure
        if ($evaluation['scores']['safety'] < 0.5) {
            $this->alerts[] = [
                'level' => 'critical',
                'message' => 'Safety evaluation failed',
                'execution_id' => $evaluation['execution_id'],
                'timestamp' => time(),
            ];
        }
    }

    /**
     * Record metric
     */
    public function recordMetric(
        string $name,
        float $value,
        array $tags = []
    ): void {
        $this->metrics[] = [
            'name' => $name,
            'value' => $value,
            'tags' => $tags,
            'timestamp' => microtime(true),
        ];
    }

    /**
     * Get aggregated stats
     */
    public function getStats(int $lastNMinutes = 60): array
    {
        $cutoff = time() - ($lastNMinutes * 60);
        
        $recentEvals = array_filter(
            $this->evaluations,
            fn($e) => $e['timestamp'] >= $cutoff
        );
        
        if (empty($recentEvals)) {
            return [
                'total_evaluations' => 0,
                'avg_scores' => [],
                'pass_rate' => 0,
            ];
        }
        
        $avgScores = [
            'quality' => 0,
            'safety' => 0,
            'cost' => 0,
            'performance' => 0,
        ];
        
        $passCount = 0;
        
        foreach ($recentEvals as $eval) {
            foreach ($avgScores as $key => $value) {
                $avgScores[$key] += $eval['scores'][$key];
            }
            
            if ($eval['passed']) {
                $passCount++;
            }
        }
        
        $count = count($recentEvals);
        foreach ($avgScores as &$score) {
            $score /= $count;
        }
        
        return [
            'total_evaluations' => $count,
            'avg_scores' => $avgScores,
            'pass_rate' => $passCount / $count,
            'alerts' => count($this->alerts),
        ];
    }

    public function getAlerts(?string $level = null): array
    {
        if (!$level) {
            return $this->alerts;
        }
        
        return array_filter(
            $this->alerts,
            fn($a) => $a['level'] === $level
        );
    }

    public function getMetrics(string $name): array
    {
        return array_filter(
            $this->metrics,
            fn($m) => $m['name'] === $name
        );
    }
}
```

See [`05-evaluation-monitoring-stack.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/20-capstone-agentic-ai-platform/05-evaluation-monitoring-stack.php) for the complete implementation with detailed metrics.

---

## Component 6: Admin Management API

Every production platform needs administration and operational controls.

### Management Capabilities

- **Agent Management** — Register, update, disable agents
- **Tool Management** — Add tools, set permissions, view usage
- **Configuration** — Update system parameters
- **Monitoring** — View metrics, logs, and execution traces
- **Debugging** — Inspect executions, replay tasks

See [`06-admin-management-api.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/20-capstone-agentic-ai-platform/06-admin-management-api.php) for the complete admin API implementation.

---

## Complete Platform Integration

Now let's bring everything together into a unified platform:

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use AgenticPlatform\Core\PlatformOrchestrator;
use AgenticPlatform\Agents\AgentHub;
use AgenticPlatform\Tools\ToolRegistry;
use AgenticPlatform\Memory\MemoryRAGSystem;
use AgenticPlatform\Monitoring\EvaluationMonitoringStack;

/**
 * Complete Agentic AI Platform
 */
class AgenticAIPlatform
{
    private AgentHub $agentHub;
    private ToolRegistry $toolRegistry;
    private MemoryRAGSystem $memory;
    private PlatformOrchestrator $orchestrator;
    private EvaluationMonitoringStack $monitoring;

    public function __construct()
    {
        // Initialize components
        $this->agentHub = new AgentHub();
        $this->toolRegistry = new ToolRegistry();
        $this->memory = $this->initializeMemory();
        $this->monitoring = new EvaluationMonitoringStack();
        
        $this->orchestrator = new PlatformOrchestrator(
            $this->agentHub,
            $this->toolRegistry,
            $this->memory
        );
        
        // Bootstrap platform
        $this->bootstrap();
    }

    private function bootstrap(): void
    {
        // Register tools
        $this->registerTools();
        
        // Register agents
        $this->registerAgents();
        
        echo "✅ Agentic AI Platform initialized\n";
        echo "   - Agents: " . count($this->agentHub->listAgents()) . "\n";
        echo "   - Tools: " . count($this->toolRegistry->listTools()) . "\n";
        echo "   - Status: Ready\n\n";
    }

    /**
     * Main entry point - execute a task
     */
    public function execute(
        string $task,
        string $sessionId = 'default'
    ): array {
        echo "🚀 Executing task: {$task}\n\n";
        
        $result = $this->orchestrator->executeTask($task, $sessionId);
        
        // Evaluate result
        $evaluation = $this->monitoring->evaluate($result);
        
        // Record metrics
        $this->monitoring->recordMetric(
            'task_execution',
            $result['metadata']['duration'] ?? 0,
            ['success' => $result['success']]
        );
        
        return [
            'result' => $result,
            'evaluation' => $evaluation,
        ];
    }

    /**
     * Get platform health and statistics
     */
    public function getHealth(): array
    {
        return [
            'status' => 'healthy',
            'agents' => $this->agentHub->listAgents(),
            'tools' => $this->toolRegistry->listTools(),
            'memory' => $this->memory->getStats(),
            'monitoring' => $this->monitoring->getStats(),
        ];
    }

    private function registerTools(): void
    {
        // Implementation in code example
    }

    private function registerAgents(): void
    {
        // Implementation in code example
    }

    private function initializeMemory(): MemoryRAGSystem
    {
        // Implementation in code example
    }
}

// Example usage
if (require_once __DIR__ . '/../../bootstrap.php') {
    $platform = new AgenticAIPlatform();
    
    // Execute a task
    $result = $platform->execute(
        'Write a PHP function to calculate the Fibonacci sequence'
    );
    
    echo "\n📊 Result:\n";
    echo $result['result']['result'] . "\n\n";
    
    echo "📈 Evaluation:\n";
    echo "  Quality: " . $result['evaluation']['scores']['quality'] . "\n";
    echo "  Safety: " . $result['evaluation']['scores']['safety'] . "\n";
    echo "  Cost: " . $result['evaluation']['scores']['cost'] . "\n";
    echo "  Performance: " . $result['evaluation']['scores']['performance'] . "\n";
    echo "  Overall: " . $result['evaluation']['overall'] . "\n";
    
    // Check platform health
    $health = $platform->getHealth();
    echo "\n🏥 Platform Health:\n";
    print_r($health);
}
```

See [`07-complete-platform.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/20-capstone-agentic-ai-platform/07-complete-platform.php) for the complete, runnable platform implementation.

---

## Production Deployment

### Docker Configuration

```dockerfile
FROM php:8.4-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    redis-tools \
    && docker-php-ext-install pdo_pgsql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application
WORKDIR /app
COPY . /app

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port
EXPOSE 9000

CMD ["php-fpm"]
```

### Docker Compose

```yaml
version: '3.8'

services:
  app:
    build: .
    environment:
      - ANTHROPIC_API_KEY=${ANTHROPIC_API_KEY}
      - POSTGRES_HOST=postgres
      - REDIS_HOST=redis
    depends_on:
      - postgres
      - redis
    volumes:
      - ./storage:/app/storage

  postgres:
    image: postgres:16
    environment:
      POSTGRES_DB: agentic_platform
      POSTGRES_USER: agent
      POSTGRES_PASSWORD: secret
    volumes:
      - postgres_data:/var/lib/postgresql/data

  redis:
    image: redis:7-alpine
    volumes:
      - redis_data:/data

  nginx:
    image: nginx:alpine
    ports:
      - "8080:80"
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf
    depends_on:
      - app

volumes:
  postgres_data:
  redis_data:
```

---

## Operational Runbook

### Monitoring Checklist

✅ **Daily**
- Review execution success rates
- Check cost trends
- Verify no critical alerts
- Scan evaluation scores

✅ **Weekly**
- Analyze agent performance trends
- Review tool usage patterns
- Check memory growth
- Update agent configurations

✅ **Monthly**
- Run regression test suite
- Audit safety evaluations
- Review and optimize costs
- Update documentation

### Common Issues & Solutions

**Issue: Agent selection returns null**
- **Cause:** No agents match task requirements
- **Solution:** Register more agents or relax selection criteria

**Issue: High cost/token usage**
- **Cause:** Inefficient prompts or wrong model selection
- **Solution:** Enable caching, optimize prompts, use model routing

**Issue: Memory growth**
- **Cause:** Sessions not being consolidated
- **Solution:** Schedule regular memory consolidation jobs

**Issue: Tool execution failures**
- **Cause:** Rate limits or permission issues
- **Solution:** Check rate limit configs and agent permissions

---

## What You Built

Congratulations! You've built a **complete production-ready Agentic AI Platform**:

✅ **Tool Registry** — Centralized, permissioned, tracked  
✅ **Memory + RAG** — Short-term + long-term with semantic search  
✅ **Agent Hub** — Specialized agents with intelligent selection  
✅ **Orchestrator** — Task routing and multi-agent coordination  
✅ **Evaluation** — Quality, safety, cost, and performance monitoring  
✅ **Admin API** — Management and operational controls  
✅ **Production Ready** — Docker deployment, monitoring, runbooks

This platform is ready to power real AI products.

---

## Next Steps

### Immediate Enhancements

1. **Add More Agents** — Expand capabilities with domain-specific agents
2. **Improve Task Analysis** — Use LLM-based task classification
3. **Add More Tools** — Build integrations with your services
4. **Enhance Evaluation** — Implement LLM-as-judge for quality scoring

### Advanced Features

1. **Streaming Responses** — Real-time output streaming to users
2. **Async Execution** — Queue-based background processing
3. **Multi-Tenancy** — Isolate data by customer/organization
4. **Fine-Tuning** — Train custom models on your data

### Production Hardening

1. **Load Testing** — Verify performance under high load
2. **Security Audit** — Pen test and vulnerability scanning
3. **Disaster Recovery** — Backup and restore procedures
4. **SLA Monitoring** — Track uptime and reliability

---

## Series Completion

**You've completed the Agentic AI for PHP Developers series!**

You've learned:

- ✅ Agent fundamentals and loop strategies
- ✅ Tool systems and execution pipelines
- ✅ Memory management and RAG
- ✅ Planning and reflection patterns
- ✅ Multi-agent orchestration
- ✅ Production observability and evaluation
- ✅ Performance optimization and async execution
- ✅ Complete platform architecture

You're now equipped to build production-grade agentic AI applications that power real products and services.

---

## Additional Resources

- **Framework Documentation**: [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent)
- **Example Applications**: Explore the examples directory
- **Community**: Join discussions and share your builds
- **Support**: Get help with production deployments

---

## Feedback & Community

Built something with this platform? Share it! Found an issue or have suggestions? Open an issue on GitHub.

**Happy building! 🚀**
