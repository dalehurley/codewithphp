<?php

/**
 * Chapter 14: Communication Protocols and Handoff Patterns
 * Example 2: Communication Protocols (Request-Response, Broadcast, Contract-Net)
 *
 * Demonstrates different communication protocols for coordinating multi-agent systems.
 */

declare(strict_types=1);

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\MultiAgent\{Message, Protocol, SimpleCollaborativeAgent, CollaborationManager};
use ClaudePhp\ClaudePhp;

// Initialize
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

echo "=== Communication Protocols Demo ===\n\n";

// ============================================================================
// Example 1: Request-Response Protocol
// ============================================================================

echo "--- Example 1: Request-Response Protocol ---\n\n";

$requestResponseProtocol = Protocol::requestResponse();

echo "Protocol: {$requestResponseProtocol->getName()}\n";
echo "Description: Agent A sends request → Agent B sends response\n";
echo "Use case: Direct task delegation, question answering\n\n";

// Create agents
$researcher = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'researcher',
    capabilities: ['research', 'fact-checking'],
    options: [
        'name' => 'Researcher',
        'system_prompt' => 'You are a research specialist. Provide accurate, well-researched information.',
    ]
);

$writer = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'writer',
    capabilities: ['writing', 'content-creation'],
    options: [
        'name' => 'Writer',
        'system_prompt' => 'You are a professional writer. Create clear, engaging content.',
    ]
);

// Writer sends request
$request = new Message(
    from: 'writer',
    to: 'researcher',
    content: 'What are the top 3 benefits of microservices architecture?',
    type: 'request'
);

echo "Request sent:\n";
echo "  {$request->getFrom()} → {$request->getTo()}: {$request->getContent()}\n";
echo "  Valid per protocol: " . ($requestResponseProtocol->validateMessage($request) ? 'Yes' : 'No') . "\n\n";

// Researcher processes and responds
$researcher->receiveMessage($request);
$researchResult = $researcher->run($request->getContent());

$response = new Message(
    from: 'researcher',
    to: 'writer',
    content: $researchResult->getAnswer(),
    type: 'response',
    metadata: ['in_reply_to' => $request->getId()]
);

echo "Response received:\n";
echo "  From: {$response->getFrom()}\n";
echo "  Valid per protocol: " . ($requestResponseProtocol->validateMessage($response) ? 'Yes' : 'No') . "\n";
echo "  Content preview: " . substr($response->getContent(), 0, 150) . "...\n\n";

// ============================================================================
// Example 2: Broadcast Protocol
// ============================================================================

echo "--- Example 2: Broadcast Protocol ---\n\n";

$broadcastProtocol = Protocol::broadcast();

echo "Protocol: {$broadcastProtocol->getName()}\n";
echo "Description: One agent broadcasts to all agents\n";
echo "Use case: Announcements, shared state updates\n\n";

$coordinator = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'coordinator',
    capabilities: ['coordination', 'management'],
    options: ['name' => 'Coordinator']
);

$broadcast = new Message(
    from: 'coordinator',
    to: 'broadcast',
    content: 'New priority: Focus on customer retention metrics for Q1 2025',
    type: 'broadcast'
);

echo "Broadcast message:\n";
echo "  From: {$broadcast->getFrom()}\n";
echo "  To: {$broadcast->getTo()}\n";
echo "  Is broadcast: " . ($broadcast->isBroadcast() ? 'Yes' : 'No') . "\n";
echo "  Valid per protocol: " . ($broadcastProtocol->validateMessage($broadcast) ? 'Yes' : 'No') . "\n";
echo "  Content: {$broadcast->getContent()}\n\n";

// ============================================================================
// Example 3: Contract-Net Protocol
// ============================================================================

echo "--- Example 3: Contract-Net Protocol (Task Bidding) ---\n\n";

$contractNetProtocol = Protocol::contractNet();

echo "Protocol: {$contractNetProtocol->getName()}\n";
echo "Description: Call for proposals → Agents bid → Manager awards\n";
echo "Use case: Dynamic task allocation, resource optimization\n\n";

// Step 1: Call for proposals (CFP)
$cfp = new Message(
    from: 'coordinator',
    to: 'broadcast',
    content: 'CFP: Need data analysis on customer churn dataset (50K rows). Provide estimated time and approach.',
    type: 'cfp',
    metadata: [
        'task_id' => 'task_001',
        'deadline' => '2024-12-31',
        'priority' => 'high',
    ]
);

echo "Step 1: Call for Proposals (CFP)\n";
echo "  Task: {$cfp->getContent()}\n";
echo "  Valid per protocol: " . ($contractNetProtocol->validateMessage($cfp) ? 'Yes' : 'No') . "\n\n";

// Step 2: Agents submit proposals
$proposal1 = new Message(
    from: 'analyst_a',
    to: 'coordinator',
    content: 'Proposal: I can complete the churn analysis in 2 hours using logistic regression and feature importance analysis.',
    type: 'proposal',
    metadata: [
        'task_id' => 'task_001',
        'estimated_time' => '2 hours',
        'confidence' => 0.9,
        'approach' => 'logistic_regression',
    ]
);

$proposal2 = new Message(
    from: 'analyst_b',
    to: 'coordinator',
    content: 'Proposal: I can analyze churn in 3 hours using decision trees and SHAP values for better interpretability.',
    type: 'proposal',
    metadata: [
        'task_id' => 'task_001',
        'estimated_time' => '3 hours',
        'confidence' => 0.95,
        'approach' => 'decision_trees_with_shap',
    ]
);

echo "Step 2: Proposals Received\n";
echo "  Proposal 1 from analyst_a:\n";
echo "    Time: {$proposal1->getMetadata()['estimated_time']}\n";
echo "    Confidence: {$proposal1->getMetadata()['confidence']}\n";
echo "    Valid: " . ($contractNetProtocol->validateMessage($proposal1) ? 'Yes' : 'No') . "\n\n";

echo "  Proposal 2 from analyst_b:\n";
echo "    Time: {$proposal2->getMetadata()['estimated_time']}\n";
echo "    Confidence: {$proposal2->getMetadata()['confidence']}\n";
echo "    Valid: " . ($contractNetProtocol->validateMessage($proposal2) ? 'Yes' : 'No') . "\n\n";

// Step 3: Award task
$award = new Message(
    from: 'coordinator',
    to: 'analyst_a',
    content: 'Task awarded to analyst_a. Faster completion time and sufficient confidence. Please proceed.',
    type: 'award',
    metadata: [
        'task_id' => 'task_001',
        'awarded_to' => 'analyst_a',
        'reason' => 'best_time_confidence_ratio',
    ]
);

$reject = new Message(
    from: 'coordinator',
    to: 'analyst_b',
    content: 'Thank you for your proposal. Task awarded to another agent this time.',
    type: 'reject',
    metadata: ['task_id' => 'task_001']
);

echo "Step 3: Task Award\n";
echo "  Award to: {$award->getTo()}\n";
echo "  Valid: " . ($contractNetProtocol->validateMessage($award) ? 'Yes' : 'No') . "\n";
echo "  Reject to: {$reject->getTo()}\n";
echo "  Valid: " . ($contractNetProtocol->validateMessage($reject) ? 'Yes' : 'No') . "\n\n";

// ============================================================================
// Example 4: Auction Protocol
// ============================================================================

echo "--- Example 4: Auction Protocol ---\n\n";

$auctionProtocol = Protocol::auction();

echo "Protocol: {$auctionProtocol->getName()}\n";
echo "Description: Agents bid competitively for resources or tasks\n";
echo "Use case: Resource allocation, priority-based task assignment\n\n";

$bid1 = new Message(
    from: 'agent_x',
    to: 'auctioneer',
    content: 'Bid: $100 for compute resource slot',
    type: 'bid',
    metadata: ['amount' => 100, 'resource' => 'compute_slot_1']
);

$bid2 = new Message(
    from: 'agent_y',
    to: 'auctioneer',
    content: 'Bid: $150 for compute resource slot',
    type: 'bid',
    metadata: ['amount' => 150, 'resource' => 'compute_slot_1']
);

echo "Bids received:\n";
echo "  Agent X: \${$bid1->getMetadata()['amount']} - Valid: " . ($auctionProtocol->validateMessage($bid1) ? 'Yes' : 'No') . "\n";
echo "  Agent Y: \${$bid2->getMetadata()['amount']} - Valid: " . ($auctionProtocol->validateMessage($bid2) ? 'Yes' : 'No') . "\n\n";

$accept = new Message(
    from: 'auctioneer',
    to: 'agent_y',
    content: 'Congratulations! Your bid of $150 has been accepted.',
    type: 'accept',
    metadata: ['winning_bid' => 150]
);

echo "Auction result:\n";
echo "  Winner: {$accept->getTo()}\n";
echo "  Valid: " . ($auctionProtocol->validateMessage($accept) ? 'Yes' : 'No') . "\n\n";

// ============================================================================
// Example 5: Protocol Enforcement
// ============================================================================

echo "--- Example 5: Protocol Validation ---\n\n";

// Invalid message for request-response (wrong type)
$invalidMsg = new Message(
    from: 'agent_a',
    to: 'agent_b',
    content: 'Hello',
    type: 'random_type'
);

echo "Protocol Enforcement:\n";
echo "  Message type: {$invalidMsg->getType()}\n";
echo "  Valid for request-response: " . ($requestResponseProtocol->validateMessage($invalidMsg) ? 'Yes' : 'No') . "\n";
echo "  Valid for contract-net: " . ($contractNetProtocol->validateMessage($invalidMsg) ? 'Yes' : 'No') . "\n\n";

// ============================================================================
// Example 6: Using Protocols with CollaborationManager
// ============================================================================

echo "--- Example 6: Protocol-Driven Collaboration ---\n\n";

$manager = new CollaborationManager($client, [
    'protocol' => $contractNetProtocol,
    'max_rounds' => 5,
]);

$agent1 = new SimpleCollaborativeAgent($client, 'agent1', ['analysis']);
$agent2 = new SimpleCollaborativeAgent($client, 'agent2', ['writing']);

$manager->registerAgent('agent1', $agent1, ['analysis']);
$manager->registerAgent('agent2', $agent2, ['writing']);

echo "CollaborationManager configured with: {$contractNetProtocol->getName()}\n";
echo "Registered agents: agent1, agent2\n";
echo "Protocol will validate all messages according to contract-net rules\n\n";

echo "=== Demo Complete ===\n\n";

echo "Protocol Summary:\n";
echo "• Request-Response: Direct 1-to-1 communication\n";
echo "• Broadcast: One-to-many announcements\n";
echo "• Contract-Net: Competitive bidding for tasks\n";
echo "• Auction: Price-based resource allocation\n";
echo "• Protocols validate message types and flow\n";
echo "• Choose protocol based on coordination needs\n";
