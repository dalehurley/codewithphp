<?php

/**
 * Chapter 14: Communication Protocols and Handoff Patterns
 * Example 7: Production Communication System
 *
 * A complete production-ready multi-agent communication system with
 * protocols, contracts, monitoring, and error handling.
 */

declare(strict_types=1);

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\MultiAgent\{
    CollaborationManager,
    Message,
    Protocol,
    SharedMemory,
    SimpleCollaborativeAgent
};
use ClaudePhp\ClaudePhp;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Initialize
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Setup logging
$logger = new Logger('communication_system');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));

echo "=== Production Communication System ===\n\n";

// ============================================================================
// Message Router with Protocol Enforcement
// ============================================================================

class MessageRouter
{
    private array $routes = [];
    private array $messageLog = [];
    private ?Protocol $protocol;
    private Logger $logger;
    private array $stats = [
        'total_messages' => 0,
        'routed' => 0,
        'rejected' => 0,
        'errors' => 0,
    ];
    
    public function __construct(?Protocol $protocol = null, ?Logger $logger = null)
    {
        $this->protocol = $protocol;
        $this->logger = $logger ?? new Logger('router');
        $this->logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));
    }
    
    public function registerRoute(string $agentId, callable $handler): void
    {
        $this->routes[$agentId] = $handler;
        $this->logger->info("Route registered for agent: {$agentId}");
    }
    
    public function routeMessage(Message $message): array
    {
        $this->stats['total_messages']++;
        
        // Validate against protocol
        if ($this->protocol && !$this->protocol->validateMessage($message)) {
            $this->stats['rejected']++;
            $this->logger->warning("Message rejected by protocol", [
                'from' => $message->getFrom(),
                'to' => $message->getTo(),
                'type' => $message->getType(),
            ]);
            
            return [
                'success' => false,
                'error' => 'Protocol validation failed',
            ];
        }
        
        // Log message
        $this->messageLog[] = [
            'id' => $message->getId(),
            'from' => $message->getFrom(),
            'to' => $message->getTo(),
            'type' => $message->getType(),
            'timestamp' => $message->getTimestamp(),
        ];
        
        // Route to handler
        if ($message->isBroadcast()) {
            return $this->handleBroadcast($message);
        }
        
        return $this->handleDirect($message);
    }
    
    private function handleDirect(Message $message): array
    {
        $to = $message->getTo();
        
        if (!isset($this->routes[$to])) {
            $this->stats['errors']++;
            $this->logger->error("No route found for agent: {$to}");
            
            return [
                'success' => false,
                'error' => "Agent not found: {$to}",
            ];
        }
        
        try {
            $handler = $this->routes[$to];
            $result = $handler($message);
            
            $this->stats['routed']++;
            $this->logger->info("Message routed successfully", [
                'from' => $message->getFrom(),
                'to' => $to,
            ]);
            
            return [
                'success' => true,
                'result' => $result,
            ];
        } catch (\Throwable $e) {
            $this->stats['errors']++;
            $this->logger->error("Routing error: {$e->getMessage()}");
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    private function handleBroadcast(Message $message): array
    {
        $results = [];
        $errors = [];
        
        foreach ($this->routes as $agentId => $handler) {
            if ($agentId === $message->getFrom()) {
                continue; // Skip sender
            }
            
            try {
                $results[$agentId] = $handler($message);
                $this->stats['routed']++;
            } catch (\Throwable $e) {
                $errors[$agentId] = $e->getMessage();
                $this->stats['errors']++;
            }
        }
        
        $this->logger->info("Broadcast delivered", [
            'from' => $message->getFrom(),
            'recipients' => count($results),
            'errors' => count($errors),
        ]);
        
        return [
            'success' => true,
            'results' => $results,
            'errors' => $errors,
        ];
    }
    
    public function getStats(): array
    {
        return $this->stats;
    }
    
    public function getMessageLog(): array
    {
        return $this->messageLog;
    }
}

// ============================================================================
// Communication Monitoring
// ============================================================================

class CommunicationMonitor
{
    private array $metrics = [];
    private float $startTime;
    
    public function __construct()
    {
        $this->startTime = microtime(true);
    }
    
    public function recordMessage(Message $message): void
    {
        $type = $message->getType();
        
        if (!isset($this->metrics[$type])) {
            $this->metrics[$type] = [
                'count' => 0,
                'first_seen' => microtime(true),
                'last_seen' => 0,
            ];
        }
        
        $this->metrics[$type]['count']++;
        $this->metrics[$type]['last_seen'] = microtime(true);
    }
    
    public function recordLatency(string $agentId, float $latency): void
    {
        if (!isset($this->metrics['latency'])) {
            $this->metrics['latency'] = [];
        }
        
        if (!isset($this->metrics['latency'][$agentId])) {
            $this->metrics['latency'][$agentId] = [];
        }
        
        $this->metrics['latency'][$agentId][] = $latency;
    }
    
    public function getSummary(): array
    {
        $uptime = microtime(true) - $this->startTime;
        
        $summary = [
            'uptime_seconds' => round($uptime, 2),
            'message_types' => [],
            'total_messages' => 0,
            'avg_latency' => [],
        ];
        
        foreach ($this->metrics as $type => $data) {
            if ($type === 'latency') {
                foreach ($data as $agent => $latencies) {
                    $summary['avg_latency'][$agent] = round(
                        array_sum($latencies) / count($latencies),
                        3
                    );
                }
            } else {
                $summary['message_types'][$type] = $data['count'];
                $summary['total_messages'] += $data['count'];
            }
        }
        
        return $summary;
    }
}

// ============================================================================
// Production Setup
// ============================================================================

echo "--- Setting Up Production System ---\n\n";

// Initialize components
$sharedMemory = new SharedMemory(['logger' => $logger]);
$protocol = Protocol::requestResponse();
$router = new MessageRouter($protocol, $logger);
$monitor = new CommunicationMonitor();

$manager = new CollaborationManager($client, [
    'protocol' => $protocol,
    'shared_memory' => $sharedMemory,
    'logger' => $logger,
    'max_rounds' => 10,
]);

echo "✓ Protocol: request-response\n";
echo "✓ Shared memory: enabled\n";
echo "✓ Message router: configured\n";
echo "✓ Monitoring: active\n\n";

// Create production agents
$agents = [
    'api_gateway' => new SimpleCollaborativeAgent(
        $client,
        'api_gateway',
        ['request_handling', 'validation'],
        [
            'name' => 'API Gateway',
            'system_prompt' => 'You are an API gateway. Validate requests and route to appropriate services.',
        ]
    ),
    'business_logic' => new SimpleCollaborativeAgent(
        $client,
        'business_logic',
        ['processing', 'business_rules'],
        [
            'name' => 'Business Logic',
            'system_prompt' => 'You handle business logic and apply business rules.',
        ]
    ),
    'data_service' => new SimpleCollaborativeAgent(
        $client,
        'data_service',
        ['data_access', 'persistence'],
        [
            'name' => 'Data Service',
            'system_prompt' => 'You manage data access and persistence operations.',
        ]
    ),
];

// Register agents with manager
foreach ($agents as $id => $agent) {
    $manager->registerAgent($id, $agent, $agent->getCapabilities());
    
    // Register routes
    $router->registerRoute($id, function(Message $msg) use ($agent, $monitor) {
        $start = microtime(true);
        $monitor->recordMessage($msg);
        
        $agent->receiveMessage($msg);
        
        $latency = microtime(true) - $start;
        $monitor->recordLatency($agent->getAgentId(), $latency);
        
        return ['received' => true, 'latency' => $latency];
    });
}

echo "Agents registered:\n";
foreach ($agents as $id => $agent) {
    echo "  • {$agent->getName()} ({$id})\n";
}
echo "\n";

// ============================================================================
// Production Workflow
// ============================================================================

echo "--- Production Workflow Execution ---\n\n";

// Simulate user request
$userRequest = "Process order #12345: Product SKU-789, Quantity: 3, Customer: cust_456";

echo "Incoming request: {$userRequest}\n\n";

// Step 1: API Gateway receives request
echo "Step 1: API Gateway validates request\n";
$gatewayMessage = new Message(
    from: 'client',
    to: 'api_gateway',
    content: $userRequest,
    type: 'request',
    metadata: ['request_id' => 'req_' . time()]
);

$routeResult = $router->routeMessage($gatewayMessage);
echo "  Status: " . ($routeResult['success'] ? '✓ Routed' : '✗ Failed') . "\n";
echo "  Latency: " . round($routeResult['result']['latency'] * 1000, 2) . "ms\n\n";

// Step 2: Gateway hands off to Business Logic
echo "Step 2: Gateway → Business Logic\n";
$businessMessage = new Message(
    from: 'api_gateway',
    to: 'business_logic',
    content: "Validated order request: {$userRequest}",
    type: 'request',
    metadata: [
        'request_id' => $gatewayMessage->getMetadata()['request_id'],
        'validation_passed' => true,
    ]
);

$routeResult = $router->routeMessage($businessMessage);
echo "  Status: " . ($routeResult['success'] ? '✓ Routed' : '✗ Failed') . "\n";
echo "  Latency: " . round($routeResult['result']['latency'] * 1000, 2) . "ms\n\n";

// Step 3: Business Logic → Data Service
echo "Step 3: Business Logic → Data Service\n";
$dataMessage = new Message(
    from: 'business_logic',
    to: 'data_service',
    content: "Store order: SKU-789, Qty: 3, Customer: cust_456",
    type: 'request',
    metadata: [
        'request_id' => $gatewayMessage->getMetadata()['request_id'],
        'operation' => 'insert',
    ]
);

$routeResult = $router->routeMessage($dataMessage);
echo "  Status: " . ($routeResult['success'] ? '✓ Routed' : '✗ Failed') . "\n";
echo "  Latency: " . round($routeResult['result']['latency'] * 1000, 2) . "ms\n\n";

// Step 4: Response chain
echo "Step 4: Response chain (Data → Business → Gateway → Client)\n";

$dataResponse = new Message(
    from: 'data_service',
    to: 'business_logic',
    content: "Order stored successfully: order_id=ord_789",
    type: 'response',
    metadata: ['request_id' => $gatewayMessage->getMetadata()['request_id']]
);

$router->routeMessage($dataResponse);
echo "  Data → Business: ✓\n";

$businessResponse = new Message(
    from: 'business_logic',
    to: 'api_gateway',
    content: "Order processed: order_id=ord_789",
    type: 'response',
    metadata: ['request_id' => $gatewayMessage->getMetadata()['request_id']]
);

$router->routeMessage($businessResponse);
echo "  Business → Gateway: ✓\n\n";

// ============================================================================
// Monitoring and Metrics
// ============================================================================

echo "--- System Metrics ---\n\n";

// Router statistics
$routerStats = $router->getStats();
echo "Message Router:\n";
echo "  Total messages: {$routerStats['total_messages']}\n";
echo "  Successfully routed: {$routerStats['routed']}\n";
echo "  Rejected: {$routerStats['rejected']}\n";
echo "  Errors: {$routerStats['errors']}\n";
echo "  Success rate: " . round(($routerStats['routed'] / $routerStats['total_messages']) * 100, 1) . "%\n\n";

// Monitor summary
$monitorSummary = $monitor->getSummary();
echo "Communication Monitor:\n";
echo "  Uptime: {$monitorSummary['uptime_seconds']}s\n";
echo "  Total messages: {$monitorSummary['total_messages']}\n";
echo "  Message types: " . count($monitorSummary['message_types']) . "\n";
foreach ($monitorSummary['message_types'] as $type => $count) {
    echo "    • {$type}: {$count}\n";
}
echo "\n  Average latency per agent:\n";
foreach ($monitorSummary['avg_latency'] as $agent => $latency) {
    echo "    • {$agent}: {$latency}s\n";
}
echo "\n";

// Shared memory stats
$memoryStats = $sharedMemory->getStatistics();
echo "Shared Memory:\n";
echo "  Keys: {$memoryStats['total_keys']}\n";
echo "  Operations: {$memoryStats['total_operations']}\n";
echo "  Unique agents: {$memoryStats['unique_agents']}\n\n";

// Manager metrics
$managerMetrics = $manager->getMetrics();
echo "Collaboration Manager:\n";
echo "  Registered agents: {$managerMetrics['agents_registered']}\n";
echo "  Messages routed: {$managerMetrics['messages_routed']}\n\n";

// ============================================================================
// Message Log
// ============================================================================

echo "--- Message Audit Log ---\n\n";

$messageLog = $router->getMessageLog();
echo "Total messages logged: " . count($messageLog) . "\n\n";

foreach ($messageLog as $i => $log) {
    echo "Message " . ($i + 1) . ":\n";
    echo "  ID: {$log['id']}\n";
    echo "  From: {$log['from']} → To: {$log['to']}\n";
    echo "  Type: {$log['type']}\n";
    echo "  Time: " . date('H:i:s', (int)$log['timestamp']) . "\n\n";
}

echo "=== Production System Operational ===\n\n";

echo "Production Features:\n";
echo "• ✓ Protocol enforcement on all messages\n";
echo "• ✓ Message routing with error handling\n";
echo "• ✓ Latency monitoring per agent\n";
echo "• ✓ Complete audit trail\n";
echo "• ✓ Shared memory coordination\n";
echo "• ✓ Structured logging\n";
echo "• ✓ Success rate tracking\n";
echo "• ✓ Multi-agent workflow orchestration\n";
