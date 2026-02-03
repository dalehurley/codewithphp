<?php

/**
 * AI-Powered Conversation Summarization
 * 
 * Demonstrates using AIConversationSummarizer to compress long conversation histories.
 * 
 * Note: Requires ANTHROPIC_API_KEY environment variable.
 */

require_once '/Users/dalehurley/Code/claude-php-agent/vendor/autoload.php';

use ClaudeAgents\Conversation\Session;
use ClaudeAgents\Conversation\Turn;
use ClaudeAgents\Conversation\Summarization\AIConversationSummarizer;
use ClaudePhp\ClaudePhp;

// Check for API key
$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    echo "⚠️  ANTHROPIC_API_KEY not set. This example requires the Claude API.\n";
    echo "Set it with: export ANTHROPIC_API_KEY='your-key-here'\n";
    exit(1);
}

echo "=== AI-Powered Conversation Summarization ===\n\n";

// Create a session with a realistic conversation
$session = new Session();

$conversation = [
    ["What's the weather in San Francisco?", "It's currently 72°F and sunny in San Francisco with light winds from the west."],
    ["What about tomorrow?", "Tomorrow in San Francisco will be slightly cooler at 68°F with some clouds moving in during the afternoon."],
    ["Should I bring an umbrella?", "For tomorrow, you won't need an umbrella. While there will be clouds, no rain is expected. However, you might want a light jacket as it will be a bit cooler."],
    ["What's the best time to visit Golden Gate Park?", "Given tomorrow's forecast, the morning would be ideal for visiting Golden Gate Park. It will be clear and pleasant before the clouds arrive in the afternoon."],
    ["Are there any good hiking trails nearby?", "Yes! The Lands End Trail offers stunning coastal views and is about 3.5 miles. The weather will be perfect for hiking in the morning."],
];

foreach ($conversation as [$user, $agent]) {
    $turn = new Turn(
        userInput: $user,
        agentResponse: $agent
    );
    $session->addTurn($turn);
}

echo "Created session with {$session->getTurnCount()} turns\n\n";

// Setup summarizer
$client = new ClaudePhp(apiKey: $apiKey);
$summarizer = new AIConversationSummarizer($client);

// Example 1: Concise summary
echo "1. Concise Summary:\n";
try {
    $conciseSummary = $summarizer->summarize($session, [
        'max_tokens' => 200,
        'style' => 'concise',
    ]);
    echo "   {$conciseSummary}\n\n";
} catch (\Exception $e) {
    echo "   Error: {$e->getMessage()}\n\n";
}

// Example 2: Detailed summary
echo "2. Detailed Summary:\n";
try {
    $detailedSummary = $summarizer->summarize($session, [
        'max_tokens' => 400,
        'style' => 'detailed',
    ]);
    echo "   {$detailedSummary}\n\n";
} catch (\Exception $e) {
    echo "   Error: {$e->getMessage()}\n\n";
}

// Example 3: Bullet points summary
echo "3. Bullet Points Summary:\n";
try {
    $bulletSummary = $summarizer->summarize($session, [
        'max_tokens' => 300,
        'style' => 'bullet_points',
    ]);
    echo "   {$bulletSummary}\n\n";
} catch (\Exception $e) {
    echo "   Error: {$e->getMessage()}\n\n";
}

// Example 4: Extract topics
echo "4. Main Topics:\n";
try {
    $topics = $summarizer->extractTopics($session, maxTopics: 5);
    foreach ($topics as $topic) {
        echo "   - {$topic}\n";
    }
    echo "\n";
} catch (\Exception $e) {
    echo "   Error: {$e->getMessage()}\n\n";
}

// Example 5: Auto-summarizing session
echo "5. Auto-Summarizing Session:\n";

class AutoSummarizingSession
{
    private Session $session;
    private AIConversationSummarizer $summarizer;
    private ?string $currentSummary = null;
    private int $summarizeInterval = 10;
    
    public function __construct(
        Session $session,
        AIConversationSummarizer $summarizer,
        int $summarizeInterval = 10
    ) {
        $this->session = $session;
        $this->summarizer = $summarizer;
        $this->summarizeInterval = $summarizeInterval;
    }
    
    public function addTurn(Turn $turn): void
    {
        $this->session->addTurn($turn);
        
        if ($this->session->getTurnCount() % $this->summarizeInterval === 0) {
            $this->updateSummary();
        }
    }
    
    private function updateSummary(): void
    {
        echo "   Generating summary after {$this->session->getTurnCount()} turns...\n";
        
        try {
            $this->currentSummary = $this->summarizer->summarize(
                $this->session,
                ['style' => 'concise', 'max_tokens' => 200]
            );
            echo "   Summary updated.\n";
        } catch (\Exception $e) {
            echo "   Summary failed: {$e->getMessage()}\n";
        }
    }
    
    public function getSummary(): ?string
    {
        return $this->currentSummary;
    }
    
    public function getSession(): Session
    {
        return $this->session;
    }
}

$autoSession = new AutoSummarizingSession(
    new Session(),
    $summarizer,
    summarizeInterval: 5
);

// Add turns - summary will auto-generate at turn 5
for ($i = 1; $i <= 7; $i++) {
    $turn = new Turn(
        userInput: "Question {$i}",
        agentResponse: "This is response {$i} with some content."
    );
    $autoSession->addTurn($turn);
}

if ($summary = $autoSession->getSummary()) {
    echo "\n   Auto-generated summary:\n   {$summary}\n";
}

echo "\n✅ AI-powered summarization complete!\n";
