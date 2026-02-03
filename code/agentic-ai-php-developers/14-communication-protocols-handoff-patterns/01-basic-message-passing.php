<?php

/**
 * Chapter 14: Communication Protocols and Handoff Patterns
 * Example 1: Basic Message Passing Between Agents
 *
 * Demonstrates the fundamentals of inter-agent communication using the
 * Message class and basic send/receive patterns.
 */

declare(strict_types=1);

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\MultiAgent\Message;
use ClaudeAgents\MultiAgent\SimpleCollaborativeAgent;
use ClaudePhp\ClaudePhp;

// Initialize Claude client
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

echo "=== Basic Message Passing Demo ===\n\n";

// Create two collaborative agents
$alice = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'alice',
    capabilities: ['data_analysis', 'statistics'],
    options: [
        'name' => 'Alice',
        'system_prompt' => 'You are Alice, a data analyst. You analyze data and provide statistical insights. Be concise and helpful.',
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
    ]
);

$bob = new SimpleCollaborativeAgent(
    client: $client,
    agentId: 'bob',
    capabilities: ['visualization', 'reporting'],
    options: [
        'name' => 'Bob',
        'system_prompt' => 'You are Bob, a visualization expert. You create visual representations and reports. Be clear and descriptive.',
        'model' => 'claude-sonnet-4-5',
        'max_tokens' => 1024,
    ]
);

echo "Agents created:\n";
echo "  • {$alice->getName()} (ID: {$alice->getAgentId()}): " . implode(', ', $alice->getCapabilities()) . "\n";
echo "  • {$bob->getName()} (ID: {$bob->getAgentId()}): " . implode(', ', $bob->getCapabilities()) . "\n\n";

// Example 1: Simple message
echo "--- Example 1: Simple Message ---\n\n";

$message1 = new Message(
    from: 'alice',
    to: 'bob',
    content: 'I have analyzed sales data for Q4. Revenue increased 23% compared to Q3. Can you create a visualization?',
    type: 'request'
);

echo "Message sent:\n";
echo "  From: {$message1->getFrom()}\n";
echo "  To: {$message1->getTo()}\n";
echo "  Type: {$message1->getType()}\n";
echo "  Content: {$message1->getContent()}\n";
echo "  Timestamp: " . date('Y-m-d H:i:s', (int) $message1->getTimestamp()) . "\n\n";

// Bob receives the message
$bob->receiveMessage($message1);

echo "Bob's inbox count: {$bob->getUnreadCount()}\n";
echo "Message received by Bob\n\n";

// Example 2: Reply to message
echo "--- Example 2: Message Reply ---\n\n";

$message2 = new Message(
    from: 'bob',
    to: 'alice',
    content: 'Received your Q4 analysis. I will create a bar chart showing the Q3 vs Q4 comparison. Should I include year-over-year data too?',
    type: 'response',
    metadata: [
        'in_reply_to' => $message1->getId(),
        'requires_response' => true,
    ]
);

$alice->receiveMessage($message2);

echo "Reply metadata:\n";
echo "  In reply to: {$message2->getMetadata()['in_reply_to']}\n";
echo "  Requires response: " . ($message2->getMetadata()['requires_response'] ? 'Yes' : 'No') . "\n";
echo "  Content: {$message2->getContent()}\n\n";

// Example 3: Broadcast message
echo "--- Example 3: Broadcast Message ---\n\n";

$broadcastMsg = new Message(
    from: 'alice',
    to: 'broadcast',
    content: 'Team meeting at 2pm to discuss Q4 results.',
    type: 'notification'
);

echo "Broadcast message created:\n";
echo "  Is broadcast: " . ($broadcastMsg->isBroadcast() ? 'Yes' : 'No') . "\n";
echo "  Content: {$broadcastMsg->getContent()}\n\n";

// Example 4: Message with rich metadata
echo "--- Example 4: Rich Metadata ---\n\n";

$richMessage = new Message(
    from: 'alice',
    to: 'bob',
    content: 'Revenue Analysis Results',
    type: 'data_transfer',
    metadata: [
        'data_type' => 'sales_analysis',
        'period' => 'Q4_2024',
        'metrics' => [
            'revenue' => 1250000,
            'growth_rate' => 0.23,
            'customer_count' => 4850,
        ],
        'priority' => 'high',
        'requires_action_by' => '2024-12-20',
    ]
);

echo "Message with structured data:\n";
echo "  Type: {$richMessage->getType()}\n";
echo "  Metadata:\n";
foreach ($richMessage->getMetadata() as $key => $value) {
    if (is_array($value)) {
        echo "    {$key}: " . json_encode($value) . "\n";
    } else {
        echo "    {$key}: {$value}\n";
    }
}
echo "\n";

// Example 5: Message array export
echo "--- Example 5: Message Export ---\n\n";

$messageArray = $message1->toArray();
echo "Message exported to array:\n";
echo json_encode($messageArray, JSON_PRETTY_PRINT) . "\n\n";

// Example 6: Message types and routing
echo "--- Example 6: Message Type System ---\n\n";

$messageTypes = [
    'request' => 'Agent requests another agent to perform an action',
    'response' => 'Reply to a previous request',
    'notification' => 'One-way information sharing',
    'data_transfer' => 'Passing structured data between agents',
    'broadcast' => 'Message to all agents',
    'handoff' => 'Transfer of task ownership',
    'acknowledgment' => 'Confirmation of message receipt',
    'error' => 'Error notification',
];

echo "Standard message types:\n";
foreach ($messageTypes as $type => $description) {
    echo "  • {$type}: {$description}\n";
}
echo "\n";

// Example 7: Inbox/Outbox management
echo "--- Example 7: Inbox & Outbox ---\n\n";

// Send multiple messages
$alice->sendMessage($message1);
$bob->sendMessage($message2);

echo "Alice's outbox: " . count($alice->getOutbox()) . " messages\n";
echo "Bob's outbox: " . count($bob->getOutbox()) . " messages\n";
echo "Alice's inbox: " . count($alice->getInbox()) . " messages\n";
echo "Bob's inbox: " . count($bob->getInbox()) . " messages\n\n";

// Clear inboxes
$alice->clearInbox();
$bob->clearInbox();

echo "After clearing inboxes:\n";
echo "Alice's inbox: " . count($alice->getInbox()) . " messages\n";
echo "Bob's inbox: " . count($bob->getInbox()) . " messages\n\n";

echo "=== Demo Complete ===\n\n";

echo "Key Takeaways:\n";
echo "• Messages encapsulate communication between agents\n";
echo "• Each message has from, to, content, type, and metadata\n";
echo "• Broadcast messages reach all agents\n";
echo "• Metadata enables rich, structured communication\n";
echo "• Agents maintain inbox/outbox for message history\n";
echo "• Message IDs and timestamps enable tracking\n";
