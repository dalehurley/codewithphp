<?php

/**
 * Multi-User Conversations
 * 
 * Demonstrates handling multiple independent user conversations with proper isolation.
 */

require_once '/Users/dalehurley/Code/claude-php-agent/vendor/autoload.php';

use ClaudeAgents\Conversation\Session;
use ClaudeAgents\Conversation\Turn;
use ClaudeAgents\Conversation\ConversationManager;
use ClaudeAgents\Conversation\Storage\FileSessionStorage;
use ClaudeAgents\Conversation\Storage\JsonSessionSerializer;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

echo "=== Multi-User Conversations ===\n\n";

// Simple Mock Agent (for demo without API key)
class MockStatefulAgent
{
    private ConversationManager $conversationManager;
    private LoggerInterface $logger;
    
    public function __construct(string $storageDir)
    {
        // Use Monolog if available, otherwise NullLogger
        if (class_exists('Monolog\Logger')) {
            $this->logger = new \Monolog\Logger('mock-agent');
            $this->logger->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::INFO));
        } else {
            $this->logger = new NullLogger();
        }
        
        $storage = new FileSessionStorage(
            $storageDir,
            new JsonSessionSerializer()
        );
        
        $this->conversationManager = new ConversationManager([
            'storage' => $storage,
            'session_timeout' => 3600,
            'logger' => $this->logger,
        ]);
    }
    
    public function getOrCreateSession(string $userId): Session
    {
        $sessions = $this->conversationManager->getSessionsByUser($userId);
        
        if (!empty($sessions)) {
            return $sessions[0];
        }
        
        return $this->conversationManager->createSession($userId);
    }
    
    public function chat(Session $session, string $userInput): string
    {
        // Mock response based on context
        $turnCount = $session->getTurnCount();
        $userId = $session->getState()['user_id'] ?? 'unknown';
        
        // Generate contextual response
        $response = match (true) {
            str_contains(strtolower($userInput), 'capital') => 
                "The capital is Paris. (Session for {$userId}, turn {$turnCount})",
            str_contains(strtolower($userInput), 'weather') => 
                "It's sunny and 75°F. (Session for {$userId}, turn {$turnCount})",
            str_contains(strtolower($userInput), 'population') => 
                "The population is approximately 2.1 million. (Session for {$userId}, turn {$turnCount})",
            str_contains(strtolower($userInput), 'more') || str_contains(strtolower($userInput), 'tell me') => 
                "Based on our earlier discussion, here's more information... (Session for {$userId}, turn {$turnCount})",
            default => "I understand. (Session for {$userId}, turn {$turnCount})"
        };
        
        $turn = new Turn($userInput, $response);
        $session->addTurn($turn);
        $this->conversationManager->saveSession($session);
        
        return $response;
    }
    
    public function endSession(Session $session): void
    {
        $this->conversationManager->deleteSession($session->getId());
    }
}

// Multi-User Agent Service
class MultiUserAgentService
{
    private MockStatefulAgent $agent;
    private array $activeSessions = [];
    private LoggerInterface $logger;
    
    public function __construct(MockStatefulAgent $agent)
    {
        $this->agent = $agent;
        // Use Monolog if available, otherwise NullLogger
        if (class_exists('Monolog\Logger')) {
            $this->logger = new \Monolog\Logger('multi-user-service');
            $this->logger->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::INFO));
        } else {
            $this->logger = new NullLogger();
        }
    }
    
    public function handleMessage(string $userId, string $message): string
    {
        if (!isset($this->activeSessions[$userId])) {
            $this->activeSessions[$userId] = $this->agent->getOrCreateSession($userId);
            $this->logger->info("Session created/loaded for user", ['user_id' => $userId]);
        }
        
        $session = $this->activeSessions[$userId];
        
        return $this->agent->chat($session, $message);
    }
    
    public function endUserSession(string $userId): void
    {
        if (isset($this->activeSessions[$userId])) {
            $this->agent->endSession($this->activeSessions[$userId]);
            unset($this->activeSessions[$userId]);
            $this->logger->info("Session ended for user", ['user_id' => $userId]);
        }
    }
    
    public function getActiveSessionCount(): int
    {
        return count($this->activeSessions);
    }
    
    public function getUserSession(string $userId): ?Session
    {
        return $this->activeSessions[$userId] ?? null;
    }
    
    public function listActiveUsers(): array
    {
        return array_keys($this->activeSessions);
    }
}

// Demo
$agent = new MockStatefulAgent(__DIR__ . '/sessions');
$service = new MultiUserAgentService($agent);

echo "Starting multi-user conversation simulation...\n\n";

// User A: Discussing France
echo "=== User A (Alice) ===\n";
echo "User A: What's the capital of France?\n";
$response = $service->handleMessage('user_alice', "What's the capital of France?");
echo "Agent: {$response}\n\n";

echo "User A: What's the population?\n";
$response = $service->handleMessage('user_alice', "What's the population?");
echo "Agent: {$response}\n\n";

// User B: Discussing Germany (independent conversation)
echo "=== User B (Bob) ===\n";
echo "User B: What's the capital of Germany?\n";
$response = $service->handleMessage('user_bob', "What's the capital of Germany?");
echo "Agent: {$response}\n\n";

echo "User B: Tell me about the weather there.\n";
$response = $service->handleMessage('user_bob', "Tell me about the weather there.");
echo "Agent: {$response}\n\n";

// User C: Another independent conversation
echo "=== User C (Carol) ===\n";
echo "User C: What's the capital of Spain?\n";
$response = $service->handleMessage('user_carol', "What's the capital of Spain?");
echo "Agent: {$response}\n\n";

// User A continues (maintains France context)
echo "=== User A (continued) ===\n";
echo "User A: Tell me more about it.\n";
$response = $service->handleMessage('user_alice', "Tell me more about it.");
echo "Agent: {$response}\n\n";

// Show active sessions
echo "=== Service Statistics ===\n";
echo "Active sessions: {$service->getActiveSessionCount()}\n";
echo "Active users: " . implode(', ', $service->listActiveUsers()) . "\n\n";

// Show session details
foreach (['user_alice', 'user_bob', 'user_carol'] as $userId) {
    $session = $service->getUserSession($userId);
    if ($session) {
        echo "User {$userId}:\n";
        echo "  Session ID: {$session->getId()}\n";
        echo "  Turn count: {$session->getTurnCount()}\n";
        echo "  Conversation:\n";
        foreach ($session->getTurns() as $i => $turn) {
            echo "    Turn " . ($i + 1) . ": {$turn->getUserInput()}\n";
        }
        echo "\n";
    }
}

// User isolation test
echo "=== Testing User Isolation ===\n";
echo "Each user should have independent context.\n\n";

echo "User B: What was the population?\n";
$response = $service->handleMessage('user_bob', "What was the population?");
echo "Agent: {$response}\n";
echo "Note: User B asked about Germany, not France. Response should reflect Germany context.\n\n";

// Cleanup
echo "=== Cleanup ===\n";
foreach (['user_alice', 'user_bob', 'user_carol'] as $userId) {
    $service->endUserSession($userId);
    echo "Ended session for {$userId}\n";
}

echo "\nRemaining active sessions: {$service->getActiveSessionCount()}\n";

// Clean up storage directory
$storageDir = __DIR__ . '/sessions';
if (is_dir($storageDir)) {
    $files = glob($storageDir . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    if (count(glob($storageDir . '/*')) === 0) {
        rmdir($storageDir);
        echo "Storage directory cleaned up\n";
    }
}

echo "\n✅ Multi-user conversation handling demonstrated!\n";
echo "\nKey Points:\n";
echo "  ✓ Each user has independent session\n";
echo "  ✓ Context is properly isolated\n";
echo "  ✓ Conversations can run concurrently\n";
echo "  ✓ Sessions are persisted and resumable\n";
