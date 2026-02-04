<?php

declare(strict_types=1);

/**
 * Example 06: Context Window Management
 *
 * Demonstrates efficient context window management through summarization,
 * pruning, and smart history management to reduce token usage in long conversations.
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudePhp\ClaudePhp;

/**
 * Context window manager for efficient conversation history
 */
class ContextWindowManager
{
    private array $history = [];
    private int $maxTokens;
    private int $currentTokens = 0;
    
    public function __construct(int $maxTokens = 10000)
    {
        $this->maxTokens = $maxTokens;
    }
    
    /**
     * Add a message to history
     */
    public function addMessage(string $role, string $content): void
    {
        $tokens = $this->estimateTokens($content);
        
        $this->history[] = [
            'role' => $role,
            'content' => $content,
            'tokens' => $tokens,
            'timestamp' => time(),
        ];
        
        $this->currentTokens += $tokens;
        
        // Prune if over limit
        if ($this->currentTokens > $this->maxTokens) {
            $this->prune();
        }
    }
    
    /**
     * Get formatted history for agent
     */
    public function getHistory(): array
    {
        return array_map(
            fn($msg) => ['role' => $msg['role'], 'content' => $msg['content']],
            $this->history
        );
    }
    
    /**
     * Prune old messages to stay within budget
     */
    private function prune(): void
    {
        echo "⚡ Pruning context window (current: {$this->currentTokens} tokens)\n";
        
        // Always keep system message and last 2 exchanges
        $keepRecent = 5; // system + 2 user + 2 assistant
        $recentMessages = array_slice($this->history, -$keepRecent);
        
        // Remove oldest messages
        $prunedMessages = array_slice($this->history, 0, -$keepRecent);
        
        // Recalculate tokens
        $this->history = $recentMessages;
        $this->currentTokens = array_sum(array_column($recentMessages, 'tokens'));
        
        $prunedCount = count($prunedMessages);
        $prunedTokens = array_sum(array_column($prunedMessages, 'tokens'));
        
        echo "   Removed: {$prunedCount} messages ({$prunedTokens} tokens)\n";
        echo "   Remaining: {$this->currentTokens} tokens\n";
    }
    
    /**
     * Create a summary of conversation so far
     */
    public function summarize(ClaudePhp $client): string
    {
        echo "📝 Generating conversation summary...\n";
        
        $conversation = '';
        foreach ($this->history as $msg) {
            if ($msg['role'] !== 'system') {
                $conversation .= "{$msg['role']}: {$msg['content']}\n\n";
            }
        }
        
        // Use a simple agent to create summary
        $summaryAgent = Agent::create($client)
            ->withSystemPrompt('Summarize the conversation in 2-3 sentences, highlighting key topics and decisions.')
            ->withModel('claude-3-5-haiku-20241022')
            ->maxIterations(1);
        
        $result = $summaryAgent->run($conversation);
        $summary = $result->getAnswer();
        
        echo "   Summary: " . substr($summary, 0, 100) . "...\n";
        
        return $summary;
    }
    
    /**
     * Replace old messages with a summary
     */
    public function compactWithSummary(ClaudePhp $client): void
    {
        if (count($this->history) < 6) {
            return; // Not enough messages to compact
        }
        
        echo "\n🗜️  Compacting context window with summary...\n";
        
        $beforeTokens = $this->currentTokens;
        $beforeCount = count($this->history);
        
        // Generate summary of old messages
        $summary = $this->summarize($client);
        
        // Keep system message and last 2 exchanges
        $recentMessages = array_slice($this->history, -4);
        $systemMessage = $this->history[0];
        
        // Create new history with summary
        $summaryTokens = $this->estimateTokens($summary);
        $this->history = [
            $systemMessage,
            [
                'role' => 'assistant',
                'content' => "Previous conversation summary: {$summary}",
                'tokens' => $summaryTokens,
                'timestamp' => time(),
            ],
            ...$recentMessages,
        ];
        
        $this->currentTokens = array_sum(array_column($this->history, 'tokens'));
        
        $saved = $beforeTokens - $this->currentTokens;
        $savedPercent = ($saved / $beforeTokens) * 100;
        
        echo "   Before: {$beforeCount} messages ({$beforeTokens} tokens)\n";
        echo "   After: " . count($this->history) . " messages ({$this->currentTokens} tokens)\n";
        echo "   Saved: {$saved} tokens (" . number_format($savedPercent, 1) . "%)\n";
    }
    
    /**
     * Estimate tokens (rough approximation)
     */
    private function estimateTokens(string $text): int
    {
        return (int) ceil(strlen($text) / 4);
    }
    
    /**
     * Get statistics
     */
    public function getStats(): array
    {
        return [
            'message_count' => count($this->history),
            'current_tokens' => $this->currentTokens,
            'max_tokens' => $this->maxTokens,
            'usage_percent' => ($this->currentTokens / $this->maxTokens) * 100,
            'avg_tokens_per_message' => count($this->history) > 0 ? 
                $this->currentTokens / count($this->history) : 0,
        ];
    }
}

// Demo
echo "=== Context Window Management Demo ===\n\n";

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Create context manager with small limit for demo
$contextManager = new ContextWindowManager(maxTokens: 5000);

// Add system message
$systemPrompt = 'You are a helpful PHP programming assistant. Answer questions concisely.';
$contextManager->addMessage('system', $systemPrompt);

// Create agent
$agent = Agent::create($client)
    ->withSystemPrompt($systemPrompt)
    ->withModel('claude-3-5-haiku-20241022')
    ->maxIterations(2);

// Simulate a long conversation
$exchanges = [
    'What is the difference between require and include in PHP?',
    'How do I connect to a MySQL database?',
    'Explain PHP namespaces and autoloading.',
    'What are traits in PHP?',
    'How does PHP handle sessions?',
    'What is the difference between == and === in PHP?',
    'Explain PHP error handling with try-catch.',
    'How do I validate user input in PHP?',
];

foreach ($exchanges as $i => $question) {
    echo "\n--- Exchange " . ($i + 1) . " ---\n";
    echo "User: {$question}\n";
    
    $contextManager->addMessage('user', $question);
    
    // Get response
    $result = $agent->run($question);
    $answer = $result->getAnswer();
    
    echo "Assistant: " . substr($answer, 0, 100) . "...\n";
    
    $contextManager->addMessage('assistant', $answer);
    
    // Show stats
    $stats = $contextManager->getStats();
    echo "\n📊 Context Stats:\n";
    echo "   Messages: {$stats['message_count']}\n";
    echo "   Tokens: {$stats['current_tokens']} / {$stats['max_tokens']} ";
    echo "(" . number_format($stats['usage_percent'], 1) . "%)\n";
    
    // Compact if getting too large
    if ($stats['usage_percent'] > 80 && $i < count($exchanges) - 1) {
        $contextManager->compactWithSummary($client);
    }
}

// Final stats
echo "\n\n=== Final Context Window Stats ===\n";
$finalStats = $contextManager->getStats();
echo "Total messages: {$finalStats['message_count']}\n";
echo "Total tokens: {$finalStats['current_tokens']}\n";
echo "Usage: " . number_format($finalStats['usage_percent'], 1) . "%\n";
echo "Avg tokens/message: " . number_format($finalStats['avg_tokens_per_message'], 0) . "\n";

echo "\n✅ Context window management prevents token bloat in long conversations!\n";
echo "💡 Strategies: pruning old messages, summarization, smart history retention\n";
