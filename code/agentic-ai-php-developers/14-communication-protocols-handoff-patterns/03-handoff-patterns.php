<?php

/**
 * Chapter 14: Communication Protocols and Handoff Patterns
 * Example 3: Task Handoff Patterns
 *
 * Demonstrates different patterns for transferring task ownership between agents.
 */

declare(strict_types=1);

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\MultiAgent\{Message, SimpleCollaborativeAgent};
use ClaudePhp\ClaudePhp;

// Initialize
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

echo "=== Task Handoff Patterns Demo ===\n\n";

// Create specialized agents
$triageAgent = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'triage',
    capabilities: ['task_classification', 'routing'],
    options: [
        'name' => 'Triage Agent',
        'system_prompt' => 'You classify incoming tasks and determine which specialist should handle them. Be concise.',
    ]
);

$technicalAgent = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'technical',
    capabilities: ['programming', 'debugging', 'architecture'],
    options: [
        'name' => 'Technical Specialist',
        'system_prompt' => 'You are a senior software engineer. Handle technical questions with expertise.',
    ]
);

$businessAgent = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'business',
    capabilities: ['business_analysis', 'strategy', 'requirements'],
    options: [
        'name' => 'Business Analyst',
        'system_prompt' => 'You are a business analyst. Focus on business value, ROI, and requirements.',
    ]
);

// ============================================================================
// Pattern 1: Direct Handoff
// ============================================================================

echo "--- Pattern 1: Direct Handoff ---\n";
echo "Description: Agent A completes its work and directly hands off to Agent B\n\n";

$initialTask = "Analyze the requirements for a new customer dashboard feature.";

// Triage agent classifies the task
echo "Step 1: Triage Agent receives task\n";
echo "  Task: {$initialTask}\n\n";

$triageResult = $triageAgent->run(
    "Classify this task and determine if it's technical or business focused: {$initialTask}. " .
    "Respond with just 'TECHNICAL' or 'BUSINESS' and a brief reason."
);

echo "Step 2: Triage completes classification\n";
echo "  Classification: " . substr($triageResult->getAnswer(), 0, 100) . "...\n\n";

// Direct handoff to business agent
$handoffMessage = new Message(
    from: 'triage',
    to: 'business',
    content: "Handing off task: {$initialTask}. This requires business analysis for requirements gathering.",
    type: 'handoff',
    metadata: [
        'original_task' => $initialTask,
        'classification' => 'business',
        'handoff_reason' => 'requires_business_requirements_analysis',
        'context' => $triageResult->getAnswer(),
    ]
);

echo "Step 3: Direct handoff to Business Agent\n";
echo "  From: {$handoffMessage->getFrom()}\n";
echo "  To: {$handoffMessage->getTo()}\n";
echo "  Type: {$handoffMessage->getType()}\n";
echo "  Reason: {$handoffMessage->getMetadata()['handoff_reason']}\n\n";

$businessAgent->receiveMessage($handoffMessage);
$businessResult = $businessAgent->run($handoffMessage->getContent());

echo "Step 4: Business Agent completes task\n";
echo "  Result: " . substr($businessResult->getAnswer(), 0, 150) . "...\n\n";

// ============================================================================
// Pattern 2: Sequential Handoff Chain
// ============================================================================

echo "--- Pattern 2: Sequential Handoff Chain ---\n";
echo "Description: Task flows through multiple agents in sequence\n\n";

$chainTask = "Build a user authentication system with social login.";

echo "Task: {$chainTask}\n";
echo "Chain: Triage → Business → Technical\n\n";

// Step 1: Triage
echo "Step 1: Triage Agent (classify)\n";
$step1Result = $triageAgent->run("Classify: {$chainTask}");
echo "  Output: Multi-domain task requiring business and technical\n\n";

// Step 2: Handoff to Business
$handoff1 = new Message(
    from: 'triage',
    to: 'business',
    content: "Define business requirements for: {$chainTask}",
    type: 'handoff',
    metadata: [
        'chain_position' => 1,
        'next_agent' => 'technical',
        'original_task' => $chainTask,
    ]
);

echo "Step 2: Handoff to Business Agent (requirements)\n";
$businessAgent->receiveMessage($handoff1);
$step2Result = $businessAgent->run($handoff1->getContent());
echo "  Output: " . substr($step2Result->getAnswer(), 0, 120) . "...\n\n";

// Step 3: Handoff to Technical
$handoff2 = new Message(
    from: 'business',
    to: 'technical',
    content: "Implement technical design for: {$chainTask}. Business requirements: {$step2Result->getAnswer()}",
    type: 'handoff',
    metadata: [
        'chain_position' => 2,
        'previous_agent' => 'business',
        'context' => $step2Result->getAnswer(),
    ]
);

echo "Step 3: Handoff to Technical Agent (implementation)\n";
$technicalAgent->receiveMessage($handoff2);
$step3Result = $technicalAgent->run($handoff2->getContent());
echo "  Output: " . substr($step3Result->getAnswer(), 0, 120) . "...\n\n";

echo "Sequential chain complete: Triage → Business → Technical\n\n";

// ============================================================================
// Pattern 3: Conditional Handoff
// ============================================================================

echo "--- Pattern 3: Conditional Handoff ---\n";
echo "Description: Handoff decision based on conditions or agent capabilities\n\n";

$conditionalTask = "Debug why API response time increased from 100ms to 2000ms.";

echo "Task: {$conditionalTask}\n\n";

$triageAnalysis = $triageAgent->run(
    "Analyze this task and determine urgency and complexity: {$conditionalTask}. " .
    "Is this URGENT or NORMAL priority? Is complexity HIGH or LOW?"
);

echo "Triage analysis:\n";
echo "  " . substr($triageAnalysis->getAnswer(), 0, 100) . "...\n\n";

// Determine handoff based on urgency
$isUrgent = str_contains(strtoupper($triageAnalysis->getAnswer()), 'URGENT');
$targetAgent = $isUrgent ? 'technical' : 'business';

echo "Conditional routing:\n";
echo "  Urgent: " . ($isUrgent ? 'Yes' : 'No') . "\n";
echo "  Route to: {$targetAgent}\n";
echo "  Reason: " . ($isUrgent ? 'Technical issue requires immediate debugging' : 'Normal priority, route through business') . "\n\n";

$conditionalHandoff = new Message(
    from: 'triage',
    to: $targetAgent,
    content: $conditionalTask,
    type: 'handoff',
    metadata: [
        'routing_type' => 'conditional',
        'condition' => 'urgency',
        'urgency_level' => $isUrgent ? 'high' : 'normal',
    ]
);

echo "Handoff executed to: {$conditionalHandoff->getTo()}\n\n";

// ============================================================================
// Pattern 4: Parallel Handoff (Fan-Out)
// ============================================================================

echo "--- Pattern 4: Parallel Handoff (Fan-Out) ---\n";
echo "Description: One agent hands off to multiple agents simultaneously\n\n";

$parallelTask = "Launch new mobile app feature: push notifications with analytics";

echo "Task: {$parallelTask}\n";
echo "Strategy: Fan out to both Technical and Business in parallel\n\n";

$handoffTechnical = new Message(
    from: 'triage',
    to: 'technical',
    content: "Technical implementation: {$parallelTask}. Focus on push notification infrastructure.",
    type: 'handoff',
    metadata: [
        'handoff_pattern' => 'parallel',
        'parallel_group' => 'mobile_feature_launch',
        'focus_area' => 'technical_implementation',
    ]
);

$handoffBusiness = new Message(
    from: 'triage',
    to: 'business',
    content: "Business metrics: {$parallelTask}. Define KPIs and success criteria.",
    type: 'handoff',
    metadata: [
        'handoff_pattern' => 'parallel',
        'parallel_group' => 'mobile_feature_launch',
        'focus_area' => 'business_metrics',
    ]
);

echo "Parallel handoffs:\n";
echo "  1. Technical Agent: Implementation focus\n";
echo "  2. Business Agent: Metrics focus\n";
echo "  Both work simultaneously on different aspects\n\n";

$technicalAgent->receiveMessage($handoffTechnical);
$businessAgent->receiveMessage($handoffBusiness);

echo "Both agents received tasks and can work concurrently\n\n";

// ============================================================================
// Pattern 5: Handoff with Context Preservation
// ============================================================================

echo "--- Pattern 5: Context-Preserving Handoff ---\n";
echo "Description: Handoff includes full context from previous agents\n\n";

$contextTask = "Optimize database query performance";

// Build context through chain
$contexts = [];

echo "Building context chain:\n\n";

// Triage adds initial context
$triageCtx = $triageAgent->run("Classify and provide initial analysis: {$contextTask}");
$contexts['triage'] = [
    'agent' => 'triage',
    'timestamp' => microtime(true),
    'output' => substr($triageCtx->getAnswer(), 0, 100),
];
echo "1. Triage context added\n";

// Business adds context
$businessCtx = $businessAgent->run("What business impact does this have: {$contextTask}");
$contexts['business'] = [
    'agent' => 'business',
    'timestamp' => microtime(true),
    'output' => substr($businessCtx->getAnswer(), 0, 100),
];
echo "2. Business context added\n\n";

// Handoff to technical with full context
$contextHandoff = new Message(
    from: 'business',
    to: 'technical',
    content: $contextTask,
    type: 'handoff',
    metadata: [
        'context_chain' => $contexts,
        'context_count' => count($contexts),
        'preserve_history' => true,
    ]
);

echo "Handoff to Technical with preserved context:\n";
echo "  Contexts included: " . count($contextHandoff->getMetadata()['context_chain']) . "\n";
echo "  Agents in chain: " . implode(' → ', array_keys($contexts)) . "\n";
echo "  Technical agent can see full history\n\n";

// ============================================================================
// Pattern 6: Escalation Handoff
// ============================================================================

echo "--- Pattern 6: Escalation Handoff ---\n";
echo "Description: Task escalated when agent cannot complete it\n\n";

$escalationTask = "Resolve critical production outage affecting 10k users";

echo "Task: {$escalationTask}\n\n";

// Junior agent attempts but needs to escalate
$escalation = new Message(
    from: 'junior_tech',
    to: 'technical',
    content: "ESCALATION: {$escalationTask}. I attempted initial diagnostics but this requires senior expertise.",
    type: 'handoff',
    metadata: [
        'handoff_type' => 'escalation',
        'severity' => 'critical',
        'reason' => 'exceeds_capability',
        'attempted_actions' => [
            'checked_server_logs',
            'verified_database_connection',
            'restarted_application',
        ],
        'escalation_level' => 'senior',
    ]
);

echo "Escalation details:\n";
echo "  From: {$escalation->getFrom()}\n";
echo "  To: {$escalation->getTo()} (senior specialist)\n";
echo "  Severity: {$escalation->getMetadata()['severity']}\n";
echo "  Reason: {$escalation->getMetadata()['reason']}\n";
echo "  Actions attempted: " . implode(', ', $escalation->getMetadata()['attempted_actions']) . "\n\n";

// ============================================================================
// Pattern 7: Handoff with Callback
// ============================================================================

echo "--- Pattern 7: Handoff with Callback ---\n";
echo "Description: Agent hands off but requests notification on completion\n\n";

$callbackTask = "Generate monthly financial report";

$callbackHandoff = new Message(
    from: 'business',
    to: 'technical',
    content: "Generate report: {$callbackTask}",
    type: 'handoff',
    metadata: [
        'callback_required' => true,
        'callback_agent' => 'business',
        'callback_message' => 'Report generation complete',
        'timeout' => 3600, // 1 hour
    ]
);

echo "Handoff with callback:\n";
echo "  Task: {$callbackTask}\n";
echo "  Callback to: {$callbackHandoff->getMetadata()['callback_agent']}\n";
echo "  Message: {$callbackHandoff->getMetadata()['callback_message']}\n";
echo "  Timeout: {$callbackHandoff->getMetadata()['timeout']} seconds\n\n";

echo "Technical agent will notify Business agent upon completion\n\n";

echo "=== Demo Complete ===\n\n";

echo "Handoff Pattern Summary:\n";
echo "• Direct Handoff: Simple A → B transfer\n";
echo "• Sequential Chain: A → B → C flow\n";
echo "• Conditional: Route based on conditions\n";
echo "• Parallel (Fan-Out): One → Many simultaneously\n";
echo "• Context-Preserving: Include full history\n";
echo "• Escalation: Hand up when capability exceeded\n";
echo "• Callback: Request completion notification\n";
