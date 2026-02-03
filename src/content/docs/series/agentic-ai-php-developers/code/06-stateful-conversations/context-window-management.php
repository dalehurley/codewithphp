<?php

/**
 * Context Window Management
 * 
 * Demonstrates different strategies for managing context windows:
 * - Sliding window (last N turns)
 * - Token-based window
 * - Hybrid (recent + summary)
 */

require_once '/Users/dalehurley/Code/claude-php-agent/vendor/autoload.php';

use ClaudeAgents\Conversation\Session;
use ClaudeAgents\Conversation\Turn;

echo "=== Context Window Management ===\n\n";

// Create a session with multiple turns
$session = new Session();
$session->updateState('user_id', 'user_123');

for ($i = 1; $i <= 15; $i++) {
    $turn = new Turn(
        userInput: "User message {$i}",
        agentResponse: "Agent response {$i} with some additional content to simulate realistic message lengths."
    );
    $session->addTurn($turn);
}

echo "Created session with {$session->getTurnCount()} turns\n\n";

// Strategy 1: Sliding Window
class SlidingWindowContext
{
    public function __construct(
        private int $maxTurns = 10,
    ) {}
    
    public function getContext(Session $session): array
    {
        $turns = $session->getTurns();
        $recentTurns = array_slice($turns, -$this->maxTurns);
        
        return $this->formatForAPI($recentTurns);
    }
    
    private function formatForAPI(array $turns): array
    {
        $messages = [];
        
        foreach ($turns as $turn) {
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
    
    public function estimateTokens(array $messages): int
    {
        $totalChars = 0;
        
        foreach ($messages as $message) {
            $totalChars += strlen($message['content']);
        }
        
        return (int)ceil($totalChars / 4);
    }
}

echo "1. Sliding Window Strategy (keep last 5 turns):\n";
$slidingWindow = new SlidingWindowContext(maxTurns: 5);
$context = $slidingWindow->getContext($session);
$tokens = $slidingWindow->estimateTokens($context);

echo "   Context messages: " . count($context) . "\n";
echo "   Estimated tokens: {$tokens}\n";
echo "   First message: {$context[0]['content']}\n\n";

// Strategy 2: Token-Based Window
class TokenBasedContext
{
    public function __construct(
        private int $maxTokens = 4000,
    ) {}
    
    public function getContext(Session $session): array
    {
        $turns = array_reverse($session->getTurns());
        $messages = [];
        $totalTokens = 0;
        
        foreach ($turns as $turn) {
            $userTokens = $this->estimateTokens($turn->getUserInput());
            $assistantTokens = $this->estimateTokens($turn->getAgentResponse());
            $turnTokens = $userTokens + $assistantTokens;
            
            if ($totalTokens + $turnTokens > $this->maxTokens) {
                break;
            }
            
            array_unshift($messages, [
                'role' => 'assistant',
                'content' => $turn->getAgentResponse(),
            ]);
            
            array_unshift($messages, [
                'role' => 'user',
                'content' => $turn->getUserInput(),
            ]);
            
            $totalTokens += $turnTokens;
        }
        
        return $messages;
    }
    
    private function estimateTokens(string $text): int
    {
        return (int)ceil(strlen($text) / 4);
    }
}

echo "2. Token-Based Window Strategy (max 500 tokens):\n";
$tokenBased = new TokenBasedContext(maxTokens: 500);
$context = $tokenBased->getContext($session);

echo "   Context messages: " . count($context) . "\n";
echo "   Turns included: " . (count($context) / 2) . "\n\n";

// Strategy 3: Hybrid (Recent + Summary)
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

echo "3. Hybrid Strategy (5 recent turns + summary):\n";
$hybrid = new HybridContextStrategy(recentTurns: 5);
$summary = "Earlier discussion covered messages 1-10 about various topics.";
$context = $hybrid->getContext($session, $summary);

echo "   Context messages: " . count($context) . "\n";
echo "   Includes summary: Yes\n";
echo "   Recent turns: 5\n";
echo "   First message (summary): " . substr($context[0]['content'], 0, 50) . "...\n\n";

// Comparison
echo "4. Strategy Comparison:\n";
echo "   Total turns in session: {$session->getTurnCount()}\n";
echo "   Sliding window (5 turns): " . count($slidingWindow->getContext($session)) . " messages\n";
echo "   Token-based (500 tokens): " . count($tokenBased->getContext($session)) . " messages\n";
echo "   Hybrid (5 + summary): " . count($hybrid->getContext($session, $summary)) . " messages\n";

echo "\n✅ Context window management strategies demonstrated!\n";
