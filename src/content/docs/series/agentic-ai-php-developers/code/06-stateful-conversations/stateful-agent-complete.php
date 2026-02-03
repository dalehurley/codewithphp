<?php

/**
 * Complete Stateful Agent
 * 
 * A production-ready stateful agent with:
 * - Session management
 * - Context window strategies
 * - Summarization
 * - Persistent storage
 * 
 * Note: Requires ANTHROPIC_API_KEY environment variable for full functionality.
 */

require_once '/Users/dalehurley/Code/claude-php-agent/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Conversation\ConversationManager;
use ClaudeAgents\Conversation\Session;
use ClaudeAgents\Conversation\Turn;
use ClaudeAgents\Conversation\Summarization\AIConversationSummarizer;
use ClaudeAgents\Conversation\Storage\FileSessionStorage;
use ClaudeAgents\Conversation\Storage\JsonSessionSerializer;
use ClaudePhp\ClaudePhp;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

echo "=== Complete Stateful Agent ===\n\n";

// Check for API key
$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    echo "⚠️  ANTHROPIC_API_KEY not set. Running in demo mode.\n\n";
    $apiKey = null; // Explicitly set to null for demo mode
}

// Hybrid Context Strategy
class HybridContextStrategy
{
    public function __construct(
        private int $recentTurns = 5,
        private int $maxTotalTokens = 8000,
    ) {}
    
    public function getContext(Session $session, ?string $summary = null): array
    {
        $allTurns = $session->getTurns();
        
        if (count($allTurns) <= $this->recentTurns) {
            return $this->formatTurns($allTurns);
        }
        
        $recentTurns = array_slice($allTurns, -$this->recentTurns);
        $messages = [];
        
        if ($summary) {
            $messages[] = [
                'role' => 'user',
                'content' => "Previous conversation summary: {$summary}",
            ];
            $messages[] = [
                'role' => 'assistant',
                'content' => "I understand. I'll use this context for our conversation.",
            ];
        }
        
        foreach ($recentTurns as $turn) {
            $messages[] = [
                'role' => 'user',
                'content' => $turn->getUserInput(),
            ];
            $messages[] = [
                'role' => 'assistant',
                'content' => $turn->getAgentResponse(),
            ];
        }
        
        return $messages;
    }
    
    private function formatTurns(array $turns): array
    {
        $messages = [];
        
        foreach ($turns as $turn) {
            $messages[] = ['role' => 'user', 'content' => $turn->getUserInput()];
            $messages[] = ['role' => 'assistant', 'content' => $turn->getAgentResponse()];
        }
        
        return $messages;
    }
}

// Stateful Agent
class StatefulAgent
{
    private ?Agent $agent = null;
    private ConversationManager $conversationManager;
    private ?AIConversationSummarizer $summarizer = null;
    private HybridContextStrategy $contextStrategy;
    private LoggerInterface $logger;
    private bool $hasApiKey;
    
    public function __construct(
        ?string $apiKey = null,
        string $storageDir = '/tmp/sessions'
    ) {
        $this->hasApiKey = $apiKey !== null;
        
        // Setup logger
        if (class_exists('Monolog\Logger')) {
            $this->logger = new \Monolog\Logger('stateful-agent');
            $this->logger->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::INFO));
        } else {
            $this->logger = new NullLogger();
        }
        
        // Setup conversation manager with persistent storage
        $storage = new FileSessionStorage(
            $storageDir,
            new JsonSessionSerializer()
        );
        
        $this->conversationManager = new ConversationManager([
            'storage' => $storage,
            'session_timeout' => 3600,
            'logger' => $this->logger,
        ]);
        
        // Setup Claude client and agent if API key is available
        if ($apiKey) {
            $client = new ClaudePhp(apiKey: $apiKey);
            
            $this->summarizer = new AIConversationSummarizer($client);
            
            $this->agent = Agent::create($client)
                ->withSystemPrompt('You are a helpful assistant with memory of our conversation.');
        }
        
        // Setup context strategy
        $this->contextStrategy = new HybridContextStrategy(
            recentTurns: 5,
            maxTotalTokens: 8000
        );
    }
    
    public function getOrCreateSession(string $userId): Session
    {
        $sessions = $this->conversationManager->getSessionsByUser($userId);
        
        if (!empty($sessions)) {
            $session = $sessions[0];
            $this->logger->info("Resumed session", ['session_id' => $session->getId()]);
            return $session;
        }
        
        $session = $this->conversationManager->createSession($userId);
        $this->logger->info("Created new session", ['session_id' => $session->getId()]);
        
        return $session;
    }
    
    public function chat(Session $session, string $userInput): string
    {
        $this->logger->info("User input", ['message' => $userInput]);
        
        // If no API key, simulate response
        if (!$this->hasApiKey) {
            $agentResponse = "Demo response to: {$userInput}";
            
            $turn = new Turn(
                userInput: $userInput,
                agentResponse: $agentResponse,
                metadata: ['demo_mode' => true]
            );
            
            $session->addTurn($turn);
            $this->conversationManager->saveSession($session);
            
            return $agentResponse;
        }
        
        // Get context with summary if conversation is long
        $summary = null;
        if ($session->getTurnCount() >= 10 && $this->summarizer) {
            try {
                $summary = $this->summarizer->summarize($session, [
                    'style' => 'concise',
                    'max_tokens' => 200,
                ]);
            } catch (\Exception $e) {
                $this->logger->warning("Summarization failed", ['error' => $e->getMessage()]);
            }
        }
        
        $contextMessages = $this->contextStrategy->getContext($session, $summary);
        
        // Get agent response
        try {
            if ($this->agent === null) {
                throw new \Exception("Agent not initialized (no API key provided)");
            }
            
            $response = $this->agent->run($userInput, [
                'context_messages' => $contextMessages,
            ]);
            
            $agentResponse = $response->getAnswer();
            
            $this->logger->info("Agent response", ['message' => $agentResponse]);
            
            // Save turn
            $turn = new Turn(
                userInput: $userInput,
                agentResponse: $agentResponse,
                metadata: [
                    'tokens' => $response->getMetadata()['usage']['input_tokens'] ?? 0,
                    'model' => 'claude-sonnet-4-5',
                ]
            );
            
            $session->addTurn($turn);
            $this->conversationManager->saveSession($session);
            
            return $agentResponse;
            
        } catch (\Exception $e) {
            $this->logger->error("Agent failed", ['error' => $e->getMessage()]);
            
            $errorResponse = "I encountered an error. Please try again.";
            
            $turn = new Turn(
                userInput: $userInput,
                agentResponse: $errorResponse,
                metadata: ['error' => $e->getMessage()]
            );
            
            $session->addTurn($turn);
            $this->conversationManager->saveSession($session);
            
            return $errorResponse;
        }
    }
    
    public function getSummary(Session $session): string
    {
        if (!$this->summarizer) {
            return "Summary not available (no API key)";
        }
        
        try {
            return $this->summarizer->summarize($session, ['style' => 'detailed']);
        } catch (\Exception $e) {
            return "Summary failed: {$e->getMessage()}";
        }
    }
    
    public function endSession(Session $session): void
    {
        $this->conversationManager->deleteSession($session->getId());
        $this->logger->info("Session ended", ['session_id' => $session->getId()]);
    }
}

// Demo Usage
$agent = new StatefulAgent(
    apiKey: $apiKey,
    storageDir: __DIR__ . '/sessions'
);

echo "1. Starting conversation with user_123:\n";
$session = $agent->getOrCreateSession('user_123');
echo "   Session ID: {$session->getId()}\n\n";

// Simulate conversation
$messages = [
    "What's the weather in Paris?",
    "What about tomorrow?",
    "Thanks for the information!",
];

foreach ($messages as $message) {
    echo "User: {$message}\n";
    $response = $agent->chat($session, $message);
    echo "Agent: {$response}\n\n";
}

// Show conversation stats
echo "2. Conversation Statistics:\n";
echo "   Total turns: {$session->getTurnCount()}\n";
echo "   Session state: " . json_encode($session->getState()) . "\n\n";

// Resume conversation (simulate restart)
echo "3. Resuming conversation:\n";
$resumedSession = $agent->getOrCreateSession('user_123');
echo "   Resumed session: {$resumedSession->getId()}\n";
echo "   Previous turns: {$resumedSession->getTurnCount()}\n\n";

echo "User: Can you remind me what we discussed?\n";
$response = $agent->chat($resumedSession, "Can you remind me what we discussed?");
echo "Agent: {$response}\n\n";

// Get summary
if ($apiKey) {
    echo "4. Conversation Summary:\n";
    $summary = $agent->getSummary($resumedSession);
    echo "   {$summary}\n\n";
}

// Cleanup
echo "5. Cleanup:\n";
$agent->endSession($resumedSession);
echo "   Session ended and removed\n";

echo "\n✅ Complete stateful agent demonstrated!\n";
