# Chapter 14: Communication Protocols and Handoff Patterns

This directory contains complete, runnable examples for Chapter 14 on standardizing inter-agent messaging, structured outputs, and contract-driven collaboration in multi-agent systems.

## Examples

### 1. Basic Message Passing (`01-basic-message-passing.php`)

**Demonstrates:**
- Creating and sending messages between agents
- Message structure (from, to, content, type, metadata)
- Inbox/Outbox management
- Broadcast messaging
- Message types and routing patterns

**Key Concepts:**
- Messages encapsulate agent communication
- Each message has ID, timestamp, and metadata
- Agents maintain inbox/outbox for history
- Broadcast reaches all agents

**Run:**
```bash
php 01-basic-message-passing.php
```

### 2. Communication Protocols (`02-communication-protocols.php`)

**Demonstrates:**
- Request-Response protocol (direct communication)
- Broadcast protocol (one-to-many)
- Contract-Net protocol (task bidding)
- Auction protocol (competitive allocation)
- Protocol validation and enforcement

**Key Concepts:**
- Protocols define valid message flows
- Request-Response for direct tasks
- Contract-Net for dynamic allocation
- Auction for resource competition
- Protocol validation prevents invalid flows

**Run:**
```bash
php 02-communication-protocols.php
```

### 3. Handoff Patterns (`03-handoff-patterns.php`)

**Demonstrates:**
- Direct handoff (A → B)
- Sequential chain (A → B → C)
- Conditional routing (based on conditions)
- Parallel fan-out (one → many)
- Context-preserving handoff
- Escalation patterns
- Callback mechanisms

**Key Concepts:**
- Handoffs transfer task ownership
- Sequential chains build on prior work
- Conditional routing based on task properties
- Parallel execution for independent subtasks
- Context preservation maintains history
- Escalation when capability exceeded

**Run:**
```bash
php 03-handoff-patterns.php
```

### 4. Shared Memory Coordination (`04-shared-memory-coordination.php`)

**Demonstrates:**
- Read/Write operations
- Multi-agent workflow coordination
- Append and increment operations
- Compare-and-swap (atomic operations)
- Access tracking and statistics
- Coordination patterns (work queue, producer-consumer, flags)
- State export/import

**Key Concepts:**
- SharedMemory enables indirect communication
- Agents coordinate through shared blackboard
- Atomic operations prevent race conditions
- Multiple coordination patterns supported
- Access log tracks all operations

**Run:**
```bash
php 04-shared-memory-coordination.php
```

### 5. Collaboration Manager (`05-collaboration-manager.php`)

**Demonstrates:**
- Multi-agent orchestration
- Automatic agent selection
- Protocol enforcement
- Conversation history tracking
- Shared memory integration
- Manager metrics
- Dynamic agent registration

**Key Concepts:**
- CollaborationManager orchestrates multi-agent systems
- Agents selected based on capabilities
- Protocols validate all messages
- Conversation history tracked
- Metrics provide visibility

**Run:**
```bash
php 05-collaboration-manager.php
```

### 6. Contract-Driven Collaboration (`06-contract-driven-collaboration.php`)

**Demonstrates:**
- Agent contract definitions
- Structured request/response formats
- Contract validation
- Multi-agent contract chains
- Message-level schemas
- Error handling with contracts
- Contract versioning
- Production contract registry

**Key Concepts:**
- Contracts define agent interfaces
- Type-safe communication
- Validation catches errors early
- Contracts enable versioning
- Registry manages contracts centrally

**Run:**
```bash
php 06-contract-driven-collaboration.php
```

### 7. Production Communication System (`07-production-communication-system.php`)

**Demonstrates:**
- Message routing with protocol enforcement
- Communication monitoring
- Latency tracking per agent
- Complete audit trail
- Production workflow execution
- Error handling and recovery
- Comprehensive metrics

**Key Concepts:**
- Production systems need routing + monitoring
- Latency tracking per agent
- Audit logs for compliance
- Error handling at all levels
- Metrics for operational visibility

**Run:**
```bash
php 07-production-communication-system.php
```

## Prerequisites

- PHP 8.4+
- Composer dependencies installed
- Anthropic API key set in environment
- `claude-php/claude-php-agent` installed

## Installation

```bash
# Install dependencies (from project root)
composer install

# Set API key
export ANTHROPIC_API_KEY=your_key_here
```

## Running Examples

All examples are standalone and can be run individually:

```bash
cd /path/to/code/agentic-ai-php-developers/14-communication-protocols-handoff-patterns
php 01-basic-message-passing.php
```

## Key Learnings

### Communication Fundamentals
- Messages are the unit of agent communication
- Each message has structure: from, to, content, type, metadata
- Agents maintain inbox/outbox for history
- Broadcast enables one-to-many communication

### Protocols
- **Request-Response**: Direct 1-to-1 communication
- **Broadcast**: One-to-many announcements
- **Contract-Net**: Competitive bidding for tasks (CFP → Proposal → Award)
- **Auction**: Price-based resource allocation
- Protocols validate message types and flows

### Handoff Patterns
- **Direct**: Simple A → B transfer
- **Sequential**: A → B → C chain
- **Conditional**: Route based on conditions
- **Parallel**: One → Many simultaneously
- **Context-Preserving**: Include full history
- **Escalation**: Hand up when capability exceeded
- **Callback**: Request completion notification

### Coordination Mechanisms
- **Direct Messaging**: Agents send messages directly
- **Shared Memory**: Indirect coordination through blackboard
- **Protocol Enforcement**: Validate all communications
- **Contract-Driven**: Type-safe, validated interfaces

### Production Considerations
- Message routing with error handling
- Latency monitoring per agent
- Complete audit trails for compliance
- Protocol validation prevents invalid flows
- Contract validation ensures type safety
- Metrics for operational visibility
- Graceful error handling and recovery

## Architecture Patterns

### 1. Message-Based Communication
```
Agent A → Message → Agent B
```

### 2. Protocol-Enforced Flow
```
Message → Protocol Validator → Route/Reject
```

### 3. Shared Memory Coordination
```
Agent A → Write → SharedMemory
Agent B ← Read ← SharedMemory
```

### 4. Contract-Driven Interface
```
Request → Contract Validation → Processing
Result → Contract Validation → Response
```

## Common Patterns

### Request-Response
```php
$request = new Message(
    from: 'agent_a',
    to: 'agent_b',
    content: 'Task description',
    type: 'request'
);

$response = new Message(
    from: 'agent_b',
    to: 'agent_a',
    content: 'Result',
    type: 'response',
    metadata: ['in_reply_to' => $request->getId()]
);
```

### Handoff with Context
```php
$handoff = new Message(
    from: 'agent_a',
    to: 'agent_b',
    content: 'Task',
    type: 'handoff',
    metadata: [
        'context_chain' => $previousContexts,
        'original_task' => $task,
    ]
);
```

### Shared Memory Coordination
```php
// Agent A writes
$sharedMemory->write('task_result', $data, 'agent_a');

// Agent B reads
$result = $sharedMemory->read('task_result', 'agent_b');
```

### Contract Validation
```php
$request = ContractClass::formatRequest($params);
if (ContractClass::validateRequest($request)) {
    // Process request
}
```

## Best Practices

1. **Choose the Right Protocol**
   - Request-Response for direct tasks
   - Broadcast for announcements
   - Contract-Net for dynamic allocation
   - Auction for competitive scenarios

2. **Design Clear Handoffs**
   - Include full context for complex chains
   - Use metadata for routing decisions
   - Document handoff reasons
   - Track handoff history

3. **Use Contracts for Safety**
   - Define clear input/output schemas
   - Validate all messages
   - Version contracts for evolution
   - Document contract requirements

4. **Monitor Everything**
   - Track message latency
   - Log all communications
   - Monitor success rates
   - Alert on failures

5. **Handle Errors Gracefully**
   - Validate before processing
   - Provide clear error messages
   - Include retry logic
   - Log errors for debugging

## Testing

Test communication patterns:

```bash
# Run all examples
for file in *.php; do
    echo "Running $file..."
    php "$file"
    echo ""
done
```

## Troubleshooting

### Message Not Delivered
- Check agent ID matches registered agent
- Verify protocol allows message type
- Check route exists in router

### Protocol Validation Fails
- Verify message type is allowed by protocol
- Check message structure matches protocol rules
- Review protocol documentation

### Handoff Not Executed
- Verify target agent exists
- Check handoff message metadata
- Review agent capabilities

### Shared Memory Issues
- Ensure agents use correct keys
- Check atomic operations for race conditions
- Verify agent IDs in operations

## Additional Resources

- [CollaborationManager API](https://github.com/claude-php/claude-php-agent/blob/main/src/MultiAgent/CollaborationManager.php)
- [Protocol Documentation](https://github.com/claude-php/claude-php-agent/blob/main/src/MultiAgent/Protocol.php)
- [Message Class](https://github.com/claude-php/claude-php-agent/blob/main/src/MultiAgent/Message.php)
- [SharedMemory System](https://github.com/claude-php/claude-php-agent/blob/main/src/MultiAgent/SharedMemory.php)

## Next Steps

- **Chapter 15**: Adaptive Agent Selection - Use AdaptiveAgentService for intelligent agent selection
- Experiment with custom protocols
- Build contract registries for your domain
- Implement monitoring dashboards
- Create custom coordination patterns
