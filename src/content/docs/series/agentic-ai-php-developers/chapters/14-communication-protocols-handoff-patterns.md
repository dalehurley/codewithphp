---
title: "14: Communication Protocols and Handoff Patterns"
description: "Standardize inter-agent messaging, structured outputs, and contract-driven collaboration for multi-agent systems."
series: "agentic-ai-php-developers"
chapter: 14
difficulty: "Advanced"
---

# Chapter 14: Communication Protocols and Handoff Patterns

## Overview

Multi-agent systems become complex quickly. When three or more agents collaborate, you need **standardized communication protocols** and **clear handoff patterns** to prevent chaos. An agent might need to request data from another, broadcast status updates to the team, or hand off a partially-completed task. Without structure, messages get lost, agents duplicate work, or critical handoffs fail.

This chapter shows you how to build **structured communication** using [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent)'s multi-agent collaboration features. You'll learn message-based communication, protocol enforcement, handoff patterns, shared memory coordination, and contract-driven interfaces that make multi-agent systems reliable and maintainable.

In this chapter you'll:

- Master **message-based communication** with the Message class and agent inbox/outbox
- Implement **communication protocols** (Request-Response, Broadcast, Contract-Net, Auction)
- Design **handoff patterns** for transferring task ownership between agents
- Use **SharedMemory** as a blackboard for indirect agent coordination
- Orchestrate agents with **CollaborationManager** and automatic message routing
- Build **contract-driven interfaces** with structured validation
- Create **production communication systems** with monitoring and error handling

**Estimated time:** ~120 minutes

::: info Code examples
Complete, runnable examples for this chapter:

- [`01-basic-message-passing.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/14-communication-protocols-handoff-patterns/01-basic-message-passing.php) — Message fundamentals and agent communication
- [`02-communication-protocols.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/14-communication-protocols-handoff-patterns/02-communication-protocols.php) — Protocol patterns and validation
- [`03-handoff-patterns.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/14-communication-protocols-handoff-patterns/03-handoff-patterns.php) — Task transfer patterns
- [`04-shared-memory-coordination.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/14-communication-protocols-handoff-patterns/04-shared-memory-coordination.php) — Blackboard coordination
- [`05-collaboration-manager.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/14-communication-protocols-handoff-patterns/05-collaboration-manager.php) — Multi-agent orchestration
- [`06-contract-driven-collaboration.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/14-communication-protocols-handoff-patterns/06-contract-driven-collaboration.php) — Structured interfaces
- [`07-production-communication-system.php`](https://github.com/dalehurley/codewithphp/blob/main/code/agentic-ai-php-developers/14-communication-protocols-handoff-patterns/07-production-communication-system.php) — Complete production system

All files are in [`code/14-communication-protocols-handoff-patterns/`](https://github.com/dalehurley/codewithphp/tree/main/code/agentic-ai-php-developers/14-communication-protocols-handoff-patterns).
:::

---

## Message-Based Communication

### The Message Class

The `Message` class is the fundamental unit of agent communication:

```php
use ClaudeAgents\MultiAgent\Message;

$message = new Message(
    from: 'agent_a',
    to: 'agent_b',
    content: 'Please analyze this dataset',
    type: 'request',
    metadata: ['priority' => 'high']
);
```

**Message structure:**

- `from`: Sender agent ID
- `to`: Recipient agent ID (or `'broadcast'` for all)
- `content`: Message payload (string or structured data)
- `type`: Message category (`request`, `response`, `notification`, `handoff`, etc.)
- `metadata`: Additional structured data

Every message gets a unique ID and timestamp automatically.

### Agent Inbox and Outbox

Agents implementing `CollaborativeInterface` maintain inbox and outbox:

```php
use ClaudeAgents\MultiAgent\SimpleCollaborativeAgent;

$agent = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'analyst',
    capabilities: ['data_analysis'],
    options: ['name' => 'Data Analyst']
);

// Send message
$agent->sendMessage($message);

// Receive message
$agent->receiveMessage($message);

// Check inbox
$unreadCount = $agent->getUnreadCount();
$inbox = $agent->getInbox();
$outbox = $agent->getOutbox();

// Clear history
$agent->clearInbox();
$agent->clearOutbox();
```

### Message Types

Standard message types:

| Type | Purpose | Example |
|------|---------|---------|
| `request` | Ask agent to perform action | "Analyze this data" |
| `response` | Reply to request | "Analysis complete: 23% growth" |
| `notification` | One-way information | "Task queue has 5 items" |
| `data_transfer` | Pass structured data | JSON payload with metrics |
| `handoff` | Transfer task ownership | "Taking over your research task" |
| `broadcast` | Announce to all | "System maintenance in 10min" |
| `acknowledgment` | Confirm receipt | "Received your request" |
| `error` | Report failure | "Processing failed: timeout" |

### Rich Metadata

Use metadata for structured information:

```php
$message = new Message(
    from: 'researcher',
    to: 'analyst',
    content: 'Market research complete',
    type: 'data_transfer',
    metadata: [
        'task_id' => 'task_123',
        'findings' => [
            'market_size' => '$2.5B',
            'growth_rate' => 0.15,
            'competitors' => 5,
        ],
        'confidence_score' => 0.92,
        'sources' => ['Gartner', 'McKinsey'],
        'requires_review' => true,
    ]
);
```

---

## Communication Protocols

### Protocol Types

The [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent) framework provides built-in protocols:

```php
use ClaudeAgents\MultiAgent\Protocol;

// Request-Response: Direct 1-to-1 communication
$requestResponse = Protocol::requestResponse();

// Broadcast: One-to-many announcements
$broadcast = Protocol::broadcast();

// Contract-Net: Task bidding (CFP → Proposal → Award)
$contractNet = Protocol::contractNet();

// Auction: Competitive resource allocation
$auction = Protocol::auction();
```

### Protocol Validation

Protocols validate message types and flows:

```php
$protocol = Protocol::requestResponse();

$validRequest = new Message(
    from: 'agent_a',
    to: 'agent_b',
    content: 'Task',
    type: 'request'
);

if ($protocol->validateMessage($validRequest)) {
    echo "✓ Message allowed by protocol\n";
}

$invalidMessage = new Message(
    from: 'agent_a',
    to: 'agent_b',
    content: 'Data',
    type: 'random_type'
);

if (!$protocol->validateMessage($invalidMessage)) {
    echo "✗ Message rejected by protocol\n";
}
```

### Request-Response Protocol

**Pattern:** Agent A sends request → Agent B sends response

**Use cases:**
- Direct task delegation
- Question-answer interactions
- Simple 1-to-1 workflows

**Valid message types:** `request`, `response`

```php
// Requester
$request = new Message(
    from: 'writer',
    to: 'researcher',
    content: 'What are the top 3 benefits of microservices?',
    type: 'request'
);

$researcher->receiveMessage($request);
$result = $researcher->run($request->getContent());

// Responder
$response = new Message(
    from: 'researcher',
    to: 'writer',
    content: $result->getAnswer(),
    type: 'response',
    metadata: ['in_reply_to' => $request->getId()]
);

$writer->receiveMessage($response);
```

### Broadcast Protocol

**Pattern:** One agent announces to all

**Use cases:**
- System announcements
- Shared state updates
- Event notifications

**Valid message types:** `broadcast`

```php
$announcement = new Message(
    from: 'coordinator',
    to: 'broadcast',
    content: 'New priority: Focus on Q1 customer retention',
    type: 'broadcast'
);

// All agents receive the broadcast
if ($announcement->isBroadcast()) {
    foreach ($agents as $agent) {
        if ($agent->getAgentId() !== 'coordinator') {
            $agent->receiveMessage($announcement);
        }
    }
}
```

### Contract-Net Protocol

**Pattern:** Call for Proposals → Agents bid → Manager awards task

**Use cases:**
- Dynamic task allocation
- Capability-based routing
- Resource optimization

**Valid message types:** `cfp` (call for proposals), `proposal`, `award`, `reject`

```php
// Step 1: Call for Proposals
$cfp = new Message(
    from: 'coordinator',
    to: 'broadcast',
    content: 'CFP: Analyze customer churn dataset (50K rows)',
    type: 'cfp',
    metadata: [
        'task_id' => 'task_001',
        'deadline' => '2024-12-31',
    ]
);

// Step 2: Agents submit proposals
$proposal1 = new Message(
    from: 'analyst_a',
    to: 'coordinator',
    content: 'Can complete in 2 hours using logistic regression',
    type: 'proposal',
    metadata: [
        'task_id' => 'task_001',
        'estimated_time' => '2 hours',
        'confidence' => 0.9,
    ]
);

$proposal2 = new Message(
    from: 'analyst_b',
    to: 'coordinator',
    content: 'Can complete in 3 hours using decision trees',
    type: 'proposal',
    metadata: [
        'task_id' => 'task_001',
        'estimated_time' => '3 hours',
        'confidence' => 0.95,
    ]
);

// Step 3: Award task
$award = new Message(
    from: 'coordinator',
    to: 'analyst_a',
    content: 'Task awarded. Please proceed.',
    type: 'award',
    metadata: ['task_id' => 'task_001']
);

$reject = new Message(
    from: 'coordinator',
    to: 'analyst_b',
    content: 'Thank you for your proposal.',
    type: 'reject',
    metadata: ['task_id' => 'task_001']
);
```

### Auction Protocol

**Pattern:** Agents bid competitively for resources

**Use cases:**
- Resource allocation
- Priority-based task assignment
- Competitive scenarios

**Valid message types:** `bid`, `accept`, `reject`

```php
// Agents submit bids
$bid1 = new Message(
    from: 'agent_x',
    to: 'auctioneer',
    content: 'Bid: $100 for compute slot',
    type: 'bid',
    metadata: ['amount' => 100]
);

$bid2 = new Message(
    from: 'agent_y',
    to: 'auctioneer',
    content: 'Bid: $150 for compute slot',
    type: 'bid',
    metadata: ['amount' => 150]
);

// Auctioneer awards
$accept = new Message(
    from: 'auctioneer',
    to: 'agent_y',
    content: 'Your bid of $150 accepted',
    type: 'accept',
    metadata: ['winning_bid' => 150]
);
```

---

## Handoff Patterns

### Pattern 1: Direct Handoff

**Flow:** Agent A completes work → hands off directly to Agent B

**Use case:** Sequential work where B depends on A's output

```php
// Triage agent classifies task
$triageResult = $triageAgent->run($task);

// Direct handoff to specialist
$handoff = new Message(
    from: 'triage',
    to: 'business_analyst',
    content: "Handing off: {$task}",
    type: 'handoff',
    metadata: [
        'original_task' => $task,
        'classification' => 'business',
        'context' => $triageResult->getAnswer(),
    ]
);

$businessAgent->receiveMessage($handoff);
```

### Pattern 2: Sequential Chain

**Flow:** Task flows through A → B → C

**Use case:** Multi-stage processing where each agent adds value

```php
// Stage 1: Triage
$stage1 = $triageAgent->run($task);

// Stage 2: Business Requirements
$handoff1 = new Message(
    from: 'triage',
    to: 'business',
    content: "Define requirements: {$task}",
    type: 'handoff',
    metadata: [
        'chain_position' => 1,
        'next_agent' => 'technical',
    ]
);

$stage2 = $businessAgent->run($handoff1->getContent());

// Stage 3: Technical Implementation
$handoff2 = new Message(
    from: 'business',
    to: 'technical',
    content: "Implement: {$task}",
    type: 'handoff',
    metadata: [
        'chain_position' => 2,
        'context' => $stage2->getAnswer(),
    ]
);

$stage3 = $technicalAgent->run($handoff2->getContent());
```

### Pattern 3: Conditional Handoff

**Flow:** Route based on conditions or agent capabilities

**Use case:** Dynamic routing based on task properties

```php
// Analyze task
$analysis = $triageAgent->run("Classify urgency: {$task}");

// Determine route
$isUrgent = str_contains(
    strtoupper($analysis->getAnswer()),
    'URGENT'
);

$targetAgent = $isUrgent ? 'senior_tech' : 'junior_tech';

$handoff = new Message(
    from: 'triage',
    to: $targetAgent,
    content: $task,
    type: 'handoff',
    metadata: [
        'routing_type' => 'conditional',
        'condition' => 'urgency',
        'urgency_level' => $isUrgent ? 'high' : 'normal',
    ]
);
```

### Pattern 4: Parallel Handoff (Fan-Out)

**Flow:** One agent hands off to multiple agents simultaneously

**Use case:** Independent subtasks that can run concurrently

```php
$task = "Launch new feature: push notifications with analytics";

// Parallel handoffs
$handoffTechnical = new Message(
    from: 'triage',
    to: 'technical',
    content: "Implement push notification infrastructure",
    type: 'handoff',
    metadata: [
        'handoff_pattern' => 'parallel',
        'parallel_group' => 'feature_launch',
        'focus_area' => 'implementation',
    ]
);

$handoffBusiness = new Message(
    from: 'triage',
    to: 'business',
    content: "Define KPIs and success criteria",
    type: 'handoff',
    metadata: [
        'handoff_pattern' => 'parallel',
        'parallel_group' => 'feature_launch',
        'focus_area' => 'metrics',
    ]
);

// Both agents work simultaneously
$technicalAgent->receiveMessage($handoffTechnical);
$businessAgent->receiveMessage($handoffBusiness);
```

### Pattern 5: Context-Preserving Handoff

**Flow:** Handoff includes full history from previous agents

**Use case:** Complex chains where context is critical

```php
// Build context chain
$contexts = [];

// Triage adds context
$triageResult = $triageAgent->run($task);
$contexts['triage'] = [
    'agent' => 'triage',
    'timestamp' => microtime(true),
    'output' => $triageResult->getAnswer(),
];

// Business adds context
$businessResult = $businessAgent->run($task);
$contexts['business'] = [
    'agent' => 'business',
    'timestamp' => microtime(true),
    'output' => $businessResult->getAnswer(),
];

// Handoff with full context
$handoff = new Message(
    from: 'business',
    to: 'technical',
    content: $task,
    type: 'handoff',
    metadata: [
        'context_chain' => $contexts,
        'context_count' => count($contexts),
        'preserve_history' => true,
    ]
);
```

### Pattern 6: Escalation Handoff

**Flow:** Agent escalates when it cannot complete the task

**Use case:** Capability limits, complexity exceeds agent expertise

```php
$escalation = new Message(
    from: 'junior_tech',
    to: 'senior_tech',
    content: "ESCALATION: Critical production outage",
    type: 'handoff',
    metadata: [
        'handoff_type' => 'escalation',
        'severity' => 'critical',
        'reason' => 'exceeds_capability',
        'attempted_actions' => [
            'checked_server_logs',
            'restarted_application',
        ],
        'escalation_level' => 'senior',
    ]
);
```

### Pattern 7: Handoff with Callback

**Flow:** Agent hands off but requests completion notification

**Use case:** Async work where originator needs final result

```php
$handoff = new Message(
    from: 'business',
    to: 'technical',
    content: "Generate monthly financial report",
    type: 'handoff',
    metadata: [
        'callback_required' => true,
        'callback_agent' => 'business',
        'callback_message' => 'Report generation complete',
        'timeout' => 3600,
    ]
);

// Technical agent completes work, then callbacks
$callback = new Message(
    from: 'technical',
    to: 'business',
    content: 'Financial report ready: report_2024_12.pdf',
    type: 'response',
    metadata: [
        'in_reply_to' => $handoff->getId(),
        'callback' => true,
    ]
);
```

---

## Shared Memory Coordination

### SharedMemory as Blackboard

The `SharedMemory` class enables indirect coordination through a shared workspace:

```php
use ClaudeAgents\MultiAgent\SharedMemory;

$sharedMemory = new SharedMemory([
    'track_access' => true,
]);

// Agent A writes
$sharedMemory->write(
    key: 'sales_data',
    value: ['q1' => 250000, 'q2' => 280000],
    agentId: 'data_collector'
);

// Agent B reads
$data = $sharedMemory->read('sales_data', 'analyst');

// Check if key exists
if ($sharedMemory->has('sales_data')) {
    echo "Data available\n";
}

// Get metadata
$meta = $sharedMemory->getMetadata('sales_data');
echo "Written by: {$meta['written_by']}\n";
echo "Version: {$meta['version']}\n";
```

### Multi-Agent Workflow

Agents coordinate through shared memory:

```php
// Agent 1: Data Collector
$sharedMemory->write('customer_count', 4850, 'data_collector');
$sharedMemory->write('churn_rate', 0.08, 'data_collector');

// Agent 2: Analyst (reads and computes)
$customers = $sharedMemory->read('customer_count', 'analyst');
$churn = $sharedMemory->read('churn_rate', 'analyst');

$expectedChurn = (int)($customers * $churn);
$retention = 1 - $churn;

$sharedMemory->write('expected_churn', $expectedChurn, 'analyst');
$sharedMemory->write('retention_rate', $retention, 'analyst');

// Agent 3: Reporter (reads final metrics)
$report = [
    'customers' => $sharedMemory->read('customer_count', 'reporter'),
    'expected_churn' => $sharedMemory->read('expected_churn', 'reporter'),
    'retention_rate' => $sharedMemory->read('retention_rate', 'reporter'),
];

$sharedMemory->write('final_report', $report, 'reporter');
```

### Atomic Operations

Prevent race conditions with atomic operations:

```php
// Append to array
$sharedMemory->write('task_queue', [], 'coordinator');
$sharedMemory->append('task_queue', 'Task 1', 'agent_a');
$sharedMemory->append('task_queue', 'Task 2', 'agent_b');

// Increment counter
$sharedMemory->write('api_calls', 0, 'coordinator');
$count = $sharedMemory->increment('api_calls', 'agent_a');

// Compare-and-swap (CAS)
$sharedMemory->write('task_status', 'pending', 'coordinator');

$claimed = $sharedMemory->compareAndSwap(
    key: 'task_status',
    expected: 'pending',
    new: 'in_progress',
    agentId: 'agent_a'
);

if ($claimed) {
    echo "Agent A claimed the task\n";
}

// Agent B tries to claim (fails - already in_progress)
$claimed2 = $sharedMemory->compareAndSwap(
    key: 'task_status',
    expected: 'pending',
    new: 'in_progress',
    agentId: 'agent_b'
);
```

### Coordination Patterns

**1. Work Queue:**

```php
$sharedMemory->write('work_queue', [
    ['id' => 1, 'task' => 'Process order #1001', 'status' => 'pending'],
    ['id' => 2, 'task' => 'Process order #1002', 'status' => 'pending'],
], 'coordinator');

// Agents claim and process tasks atomically
```

**2. Producer-Consumer:**

```php
$sharedMemory->write('event_stream', [], 'producer');

// Producer adds events
$sharedMemory->append('event_stream', ['type' => 'signup'], 'producer');

// Consumer reads events
$events = $sharedMemory->read('event_stream', 'consumer');
```

**3. Flag-based Coordination:**

```php
$sharedMemory->write('all_ready', false, 'coordinator');
$sharedMemory->write('ready_count', 0, 'coordinator');

// Agents signal readiness
$sharedMemory->increment('ready_count', 'agent_a');
$sharedMemory->increment('ready_count', 'agent_b');
$sharedMemory->increment('ready_count', 'agent_c');

$count = $sharedMemory->read('ready_count', 'coordinator');
if ($count >= 3) {
    $sharedMemory->write('all_ready', true, 'coordinator');
}
```

### Access Tracking

Monitor shared memory usage:

```php
$stats = $sharedMemory->getStatistics();

echo "Total keys: {$stats['total_keys']}\n";
echo "Operations: {$stats['total_operations']}\n";
echo "Reads: {$stats['reads']}\n";
echo "Writes: {$stats['writes']}\n";
echo "Unique agents: {$stats['unique_agents']}\n";

// Get access log
$log = $sharedMemory->getAccessLog();
foreach ($log as $entry) {
    echo "{$entry['operation']} '{$entry['key']}' by {$entry['agent_id']}\n";
}
```

---

## CollaborationManager

### Orchestrating Multi-Agent Systems

The `CollaborationManager` orchestrates multiple agents with automatic routing:

```php
use ClaudeAgents\MultiAgent\{
    CollaborationManager,
    Protocol,
    SharedMemory,
    SimpleCollaborativeAgent
};

$sharedMemory = new SharedMemory();
$manager = new CollaborationManager($client, [
    'max_rounds' => 10,
    'protocol' => Protocol::requestResponse(),
    'shared_memory' => $sharedMemory,
    'enable_message_passing' => true,
]);
```

### Agent Registration

Register agents with capabilities:

```php
$researcher = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'researcher',
    capabilities: ['research', 'fact-checking'],
    options: [
        'name' => 'Researcher',
        'system_prompt' => 'You gather accurate information.',
    ]
);

$analyst = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'analyst',
    capabilities: ['analysis', 'statistics'],
    options: [
        'name' => 'Analyst',
        'system_prompt' => 'You analyze data and extract insights.',
    ]
);

$manager->registerAgent('researcher', $researcher, ['research']);
$manager->registerAgent('analyst', $analyst, ['analysis']);
```

### Running Collaboration

The manager automatically selects agents and routes work:

```php
$task = "Analyze the impact of remote work on productivity. " .
        "Research findings and provide data-backed insights.";

$result = $manager->collaborate($task);

if ($result->isSuccess()) {
    echo $result->getAnswer() . "\n";
    
    $metadata = $result->getMetadata();
    echo "Rounds: {$metadata['rounds']}\n";
    echo "Agents: " . implode(', ', $metadata['agents_involved']) . "\n";
}
```

### Conversation History

Track multi-agent interactions:

```php
$history = $manager->getConversationHistory();

foreach ($history as $entry) {
    echo "Round {$entry['round']} - {$entry['agent']}:\n";
    echo "  Task: {$entry['task']}\n";
    echo "  Result: {$entry['result']}\n";
}
```

### Manager Metrics

Monitor collaboration:

```php
$metrics = $manager->getMetrics();

echo "Agents: {$metrics['agents_registered']}\n";
echo "Messages routed: {$metrics['messages_routed']}\n";
echo "Conversation length: {$metrics['conversation_length']}\n";

if (isset($metrics['performance'])) {
    echo "Success rate: " . round($metrics['performance']['success_rate'] * 100) . "%\n";
}
```

---

## Contract-Driven Collaboration

### Defining Agent Contracts

Contracts specify structured input/output schemas:

```php
class ResearchContract
{
    public static function validateRequest(array $data): bool
    {
        return isset($data['topic']) && 
               isset($data['depth']) &&
               in_array($data['depth'], ['basic', 'detailed', 'comprehensive']);
    }
    
    public static function validateResponse(array $data): bool
    {
        return isset($data['findings']) && 
               is_array($data['findings']) &&
               isset($data['sources']) &&
               isset($data['confidence_score']);
    }
    
    public static function formatRequest(string $topic, string $depth): array
    {
        return [
            'type' => 'research_request',
            'topic' => $topic,
            'depth' => $depth,
            'timestamp' => time(),
        ];
    }
    
    public static function formatResponse(
        array $findings,
        array $sources,
        float $confidence
    ): array {
        return [
            'type' => 'research_result',
            'findings' => $findings,
            'sources' => $sources,
            'confidence_score' => $confidence,
            'timestamp' => time(),
        ];
    }
}
```

### Using Contracts

Validate requests and responses:

```php
// Create structured request
$request = ResearchContract::formatRequest(
    topic: 'Microservices benefits',
    depth: 'detailed'
);

// Validate request
if (ResearchContract::validateRequest($request)) {
    echo "✓ Valid request\n";
    
    // Process request...
    $response = ResearchContract::formatResponse(
        findings: [
            'Independent scaling',
            'Technology flexibility',
            'Team autonomy',
        ],
        sources: ['Martin Fowler', 'AWS'],
        confidence: 0.92
    );
    
    // Validate response
    if (ResearchContract::validateResponse($response)) {
        echo "✓ Valid response\n";
    }
}
```

### Contract Chains

Chain contracts across multiple agents:

```php
// Step 1: Research contract
$researchData = ResearchContract::formatResponse(
    findings: ['Finding 1', 'Finding 2'],
    sources: ['Source A'],
    confidence: 0.88
);

// Step 2: Analysis contract
$analysisRequest = AnalysisContract::formatRequest(
    data: $researchData,
    method: 'trend_analysis'
);

if (AnalysisContract::validateRequest($analysisRequest)) {
    $analysisResponse = AnalysisContract::formatResponse(
        insights: ['Insight 1', 'Insight 2'],
        metrics: ['growth_rate' => 3.0]
    );
}

// Step 3: Report contract
$reportRequest = ReportContract::formatRequest(
    content: [
        'research' => $researchData,
        'analysis' => $analysisResponse,
    ],
    format: 'executive'
);
```

### Contract Versioning

Support multiple contract versions:

```php
$contractV1 = [
    'version' => '1.0',
    'fields' => ['data', 'method'],
];

$contractV2 = [
    'version' => '2.0',
    'fields' => ['data', 'method', 'options', 'callback'],
    'backward_compatible' => true,
];

// V1 requests work with V2 agents if backward compatible
$v1Request = [
    'contract_version' => '1.0',
    'data' => ['values' => [1, 2, 3]],
    'method' => 'average',
];
```

### Contract Registry

Centralized contract management:

```php
class ContractRegistry
{
    private array $contracts = [];
    
    public function register(string $name, string $version, array $schema): void
    {
        $this->contracts["{$name}:{$version}"] = $schema;
    }
    
    public function validate(string $name, string $version, array $data): array
    {
        $key = "{$name}:{$version}";
        if (!isset($this->contracts[$key])) {
            return ['valid' => false, 'error' => 'Contract not found'];
        }
        
        $schema = $this->contracts[$key];
        $errors = [];
        
        foreach ($schema['required'] ?? [] as $field) {
            if (!isset($data[$field])) {
                $errors[] = "Missing: {$field}";
            }
        }
        
        return ['valid' => empty($errors), 'errors' => $errors];
    }
}

$registry = new ContractRegistry();
$registry->register('Research', '1.0', [
    'required' => ['topic', 'depth'],
    'optional' => ['deadline'],
]);

$validation = $registry->validate('Research', '1.0', $data);
```

---

## Production Communication System

### Message Routing

Production systems need robust routing:

```php
class MessageRouter
{
    private array $routes = [];
    private array $stats = [
        'total_messages' => 0,
        'routed' => 0,
        'rejected' => 0,
        'errors' => 0,
    ];
    
    public function registerRoute(string $agentId, callable $handler): void
    {
        $this->routes[$agentId] = $handler;
    }
    
    public function routeMessage(Message $message): array
    {
        $this->stats['total_messages']++;
        
        // Validate against protocol
        if ($this->protocol && !$this->protocol->validateMessage($message)) {
            $this->stats['rejected']++;
            return ['success' => false, 'error' => 'Protocol violation'];
        }
        
        // Route to handler
        if ($message->isBroadcast()) {
            return $this->handleBroadcast($message);
        }
        
        $to = $message->getTo();
        if (!isset($this->routes[$to])) {
            $this->stats['errors']++;
            return ['success' => false, 'error' => 'Agent not found'];
        }
        
        try {
            $handler = $this->routes[$to];
            $result = $handler($message);
            $this->stats['routed']++;
            
            return ['success' => true, 'result' => $result];
        } catch (\Throwable $e) {
            $this->stats['errors']++;
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    public function getStats(): array
    {
        return $this->stats;
    }
}
```

### Communication Monitoring

Track message flow and latency:

```php
class CommunicationMonitor
{
    private array $metrics = [];
    
    public function recordMessage(Message $message): void
    {
        $type = $message->getType();
        
        if (!isset($this->metrics[$type])) {
            $this->metrics[$type] = [
                'count' => 0,
                'first_seen' => microtime(true),
            ];
        }
        
        $this->metrics[$type]['count']++;
    }
    
    public function recordLatency(string $agentId, float $latency): void
    {
        if (!isset($this->metrics['latency'][$agentId])) {
            $this->metrics['latency'][$agentId] = [];
        }
        
        $this->metrics['latency'][$agentId][] = $latency;
    }
    
    public function getSummary(): array
    {
        $summary = [
            'message_types' => [],
            'total_messages' => 0,
            'avg_latency' => [],
        ];
        
        foreach ($this->metrics as $type => $data) {
            if ($type === 'latency') {
                foreach ($data as $agent => $latencies) {
                    $summary['avg_latency'][$agent] = 
                        array_sum($latencies) / count($latencies);
                }
            } else {
                $summary['message_types'][$type] = $data['count'];
                $summary['total_messages'] += $data['count'];
            }
        }
        
        return $summary;
    }
}
```

### Production Workflow

Complete production system:

```php
// Initialize components
$protocol = Protocol::requestResponse();
$router = new MessageRouter($protocol);
$monitor = new CommunicationMonitor();

// Register agents with routes
foreach ($agents as $id => $agent) {
    $router->registerRoute($id, function(Message $msg) use ($agent, $monitor) {
        $start = microtime(true);
        $monitor->recordMessage($msg);
        
        $agent->receiveMessage($msg);
        
        $latency = microtime(true) - $start;
        $monitor->recordLatency($agent->getAgentId(), $latency);
        
        return ['received' => true, 'latency' => $latency];
    });
}

// Route message
$message = new Message(
    from: 'client',
    to: 'api_gateway',
    content: 'Process order #12345',
    type: 'request'
);

$result = $router->routeMessage($message);

// Check metrics
$routerStats = $router->getStats();
echo "Success rate: " . 
     round(($routerStats['routed'] / $routerStats['total_messages']) * 100) . "%\n";

$monitorSummary = $monitor->getSummary();
foreach ($monitorSummary['avg_latency'] as $agent => $latency) {
    echo "{$agent}: " . round($latency * 1000, 2) . "ms\n";
}
```

---

## Best Practices

### 1. Choose the Right Protocol

✅ **Do:**
- Use Request-Response for direct tasks
- Use Broadcast for announcements
- Use Contract-Net for dynamic allocation
- Use Auction for competitive scenarios

❌ **Don't:**
- Mix protocols without clear boundaries
- Use Broadcast for point-to-point communication
- Ignore protocol validation

### 2. Design Clear Handoffs

✅ **Do:**
- Include full context for complex chains
- Use metadata for routing decisions
- Document handoff reasons
- Track handoff history

❌ **Don't:**
- Hand off without context
- Create circular handoffs
- Ignore handoff failures

### 3. Use Contracts for Safety

✅ **Do:**
- Define clear input/output schemas
- Validate all messages
- Version contracts properly
- Document contract requirements

❌ **Don't:**
- Skip validation
- Make breaking changes without versioning
- Use unstructured data

### 4. Monitor Everything

✅ **Do:**
- Track message latency
- Log all communications
- Monitor success rates
- Alert on failures

❌ **Don't:**
- Run blind without metrics
- Ignore latency spikes
- Skip audit trails

### 5. Handle Errors Gracefully

✅ **Do:**
- Validate before processing
- Provide clear error messages
- Include retry logic
- Log errors with context

❌ **Don't:**
- Assume all messages succeed
- Silently drop messages
- Skip error logging

---

## Common Pitfalls

### Pitfall 1: Missing Protocol Validation

```php
// ❌ Bad: No validation
$router->routeMessage($message);

// ✓ Good: Protocol validation
if ($protocol->validateMessage($message)) {
    $router->routeMessage($message);
} else {
    logger->warning('Message rejected by protocol');
}
```

### Pitfall 2: Lost Context in Handoffs

```php
// ❌ Bad: No context
$handoff = new Message(
    from: 'agent_a',
    to: 'agent_b',
    content: 'Continue task',
    type: 'handoff'
);

// ✓ Good: Full context
$handoff = new Message(
    from: 'agent_a',
    to: 'agent_b',
    content: 'Continue task',
    type: 'handoff',
    metadata: [
        'original_task' => $task,
        'context_chain' => $contexts,
        'progress' => '50% complete',
    ]
);
```

### Pitfall 3: Race Conditions in Shared Memory

```php
// ❌ Bad: Non-atomic read-modify-write
$count = $sharedMemory->read('counter', 'agent_a');
$sharedMemory->write('counter', $count + 1, 'agent_a');

// ✓ Good: Atomic increment
$sharedMemory->increment('counter', 'agent_a');

// ✓ Good: Compare-and-swap
$sharedMemory->compareAndSwap(
    key: 'task_status',
    expected: 'pending',
    new: 'claimed',
    agentId: 'agent_a'
);
```

### Pitfall 4: No Message Audit Trail

```php
// ❌ Bad: No logging
$router->routeMessage($message);

// ✓ Good: Complete audit trail
$messageLog[] = [
    'id' => $message->getId(),
    'from' => $message->getFrom(),
    'to' => $message->getTo(),
    'type' => $message->getType(),
    'timestamp' => $message->getTimestamp(),
];

$router->routeMessage($message);
```

---

## Summary

Communication protocols and handoff patterns are essential for **scalable multi-agent systems**. The [`claude-php/claude-php-agent`](https://github.com/claude-php/claude-php-agent) framework provides powerful primitives for structured communication.

### Key Takeaways

1. **Message-Based Communication**: Messages encapsulate agent interactions with structure
2. **Protocol Enforcement**: Protocols validate message flows and prevent invalid communication
3. **Handoff Patterns**: Seven patterns for transferring task ownership between agents
4. **Shared Memory**: Blackboard pattern enables indirect coordination
5. **CollaborationManager**: Orchestrates multi-agent systems with automatic routing
6. **Contract-Driven**: Structured interfaces with validation ensure type safety
7. **Production-Ready**: Monitoring, error handling, and audit trails are essential

### When to Use Each Pattern

| Pattern | Best For | Avoid When |
|---------|----------|------------|
| **Request-Response** | Direct tasks | Broadcasting to many |
| **Broadcast** | Announcements | Point-to-point |
| **Contract-Net** | Dynamic allocation | Simple tasks |
| **Direct Handoff** | Sequential work | Parallel work |
| **Parallel Handoff** | Independent subtasks | Sequential dependencies |
| **SharedMemory** | Indirect coordination | Direct messaging is clearer |
| **Contracts** | Type safety needed | Simple prototyping |

### Architecture Benefits

- **Decoupling**: Agents communicate through messages, not direct dependencies
- **Flexibility**: Protocols allow different communication patterns
- **Reliability**: Validation prevents invalid message flows
- **Auditability**: Complete message logs for debugging
- **Scalability**: Patterns support growing agent counts

---

## Next Steps

In **Chapter 15: Adaptive Agent Selection**, you'll learn how to use `AdaptiveAgentService` for intelligent agent selection, validation, and auto-adaptation based on task analysis.

For now, practice communication patterns:

1. Implement Request-Response for a simple task delegation
2. Build a Contract-Net system for dynamic task allocation
3. Create a handoff chain with context preservation
4. Use SharedMemory for producer-consumer coordination
5. Add monitoring to track message latency and success rates

### Additional Resources

- [`CollaborationManager` Source](https://github.com/claude-php/claude-php-agent/blob/main/src/MultiAgent/CollaborationManager.php)
- [`Protocol` Documentation](https://github.com/claude-php/claude-php-agent/blob/main/src/MultiAgent/Protocol.php)
- [`Message` Class](https://github.com/claude-php/claude-php-agent/blob/main/src/MultiAgent/Message.php)
- [`SharedMemory` System](https://github.com/claude-php/claude-php-agent/blob/main/src/MultiAgent/SharedMemory.php)

---

## Exercises

### Exercise 1: Build a Task Router

Create a router that uses Contract-Net protocol to allocate tasks to the best agent based on capability matching and estimated completion time.

**Requirements:**
- 3+ agents with different capabilities
- CFP → Proposal → Award flow
- Select agent with best time/confidence ratio

### Exercise 2: Implement Context-Preserving Chain

Build a 4-agent sequential chain where each agent adds context that all subsequent agents can access.

**Requirements:**
- Triage → Research → Analysis → Report
- Each stage adds to context chain
- Final agent sees full history

### Exercise 3: Shared Memory Producer-Consumer

Create a system where multiple producers add tasks to shared memory and multiple consumers process them atomically.

**Requirements:**
- 2 producers, 3 consumers
- Atomic task claiming (CAS)
- Track completion metrics

### Exercise 4: Production Monitoring System

Build a complete communication system with routing, protocol enforcement, and monitoring.

**Requirements:**
- Message router with protocol validation
- Latency tracking per agent
- Success rate monitoring
- Complete audit log

---

**You now have the tools to build structured, reliable multi-agent communication systems. Protocols and handoffs turn chaos into coordinated collaboration.**
