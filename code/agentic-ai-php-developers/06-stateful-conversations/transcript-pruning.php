<?php

/**
 * Transcript Pruning
 * 
 * Demonstrates intelligent pruning strategies to keep only relevant conversation turns:
 * - Importance-based pruning
 * - Time-based pruning
 * - Combined strategies
 */

require_once '/Users/dalehurley/Code/claude-php-agent/vendor/autoload.php';

use ClaudeAgents\Conversation\Session;
use ClaudeAgents\Conversation\Turn;

echo "=== Transcript Pruning ===\n\n";

// Create a session with varied content
$session = new Session();

$conversations = [
    ["Hi, my name is John", "Hello John! Nice to meet you.", ['importance' => 'high']],
    ["What's the weather?", "It's sunny and 72°F.", []],
    ["Tell me a joke", "Why did the chicken cross the road? To get to the other side!", []],
    ["My email is john@example.com", "I've noted your email address.", ['importance' => 'high']],
    ["What's 2+2?", "2+2 equals 4.", []],
    ["Can you search for Python tutorials?", "I found several Python tutorials for you.", ['tools_used' => ['search']]],
    ["Thanks", "You're welcome!", []],
    ["What was my email again?", "Your email is john@example.com", ['importance' => 'high']],
    ["What's the capital of France?", "The capital of France is Paris.", []],
    ["Remember: I prefer morning meetings", "I'll remember that you prefer morning meetings.", ['importance' => 'high']],
];

foreach ($conversations as [$user, $agent, $meta]) {
    // Add slight delay to get different timestamps
    usleep(100000); // 0.1 second
    
    $turn = new Turn(
        userInput: $user,
        agentResponse: $agent,
        metadata: $meta
    );
    $session->addTurn($turn);
}

echo "Created session with {$session->getTurnCount()} turns\n\n";

// Strategy 1: Importance-Based Pruning
class TranscriptPruner
{
    public function pruneByImportance(
        Session $session,
        int $keepCount,
        array $importanceScores = []
    ): array {
        $turns = $session->getTurns();
        $turnCount = count($turns);
        
        if ($turnCount <= $keepCount) {
            return $turns;
        }
        
        // Always keep most recent turns
        $recentKeep = min(3, $keepCount);
        $recentTurns = array_slice($turns, -$recentKeep);
        $remainingTurns = array_slice($turns, 0, -$recentKeep);
        
        // Score remaining turns
        $scoredTurns = [];
        foreach ($remainingTurns as $i => $turn) {
            $score = $importanceScores[$i] ?? $this->calculateImportance($turn);
            $scoredTurns[] = ['turn' => $turn, 'score' => $score, 'index' => $i];
        }
        
        // Sort by importance (descending)
        usort($scoredTurns, fn($a, $b) => $b['score'] <=> $a['score']);
        
        // Keep top N important turns
        $keepFromOld = $keepCount - $recentKeep;
        $importantTurns = array_slice($scoredTurns, 0, $keepFromOld);
        
        // Sort by original order
        usort($importantTurns, fn($a, $b) => $a['index'] <=> $b['index']);
        
        // Combine important + recent
        $prunedTurns = array_merge(
            array_map(fn($st) => $st['turn'], $importantTurns),
            $recentTurns
        );
        
        return $prunedTurns;
    }
    
    private function calculateImportance(Turn $turn): float
    {
        $score = 0.0;
        
        $input = strtolower($turn->getUserInput());
        $response = strtolower($turn->getAgentResponse());
        
        // Important keywords
        $importantKeywords = ['name', 'email', 'phone', 'address', 'important', 'remember', 'prefer'];
        foreach ($importantKeywords as $keyword) {
            if (str_contains($input, $keyword) || str_contains($response, $keyword)) {
                $score += 10.0;
            }
        }
        
        // Questions get higher importance
        if (str_contains($input, '?')) {
            $score += 5.0;
        }
        
        // Longer responses may be more informative
        $score += min(strlen($response) / 100, 5.0);
        
        // Check metadata for tool use
        $metadata = $turn->getMetadata();
        if (isset($metadata['tools_used']) && !empty($metadata['tools_used'])) {
            $score += 15.0;
        }
        
        if (isset($metadata['importance']) && $metadata['importance'] === 'high') {
            $score += 20.0;
        }
        
        return $score;
    }
}

echo "1. Importance-Based Pruning (keep 5 most important):\n";
$pruner = new TranscriptPruner();
$prunedTurns = $pruner->pruneByImportance($session, keepCount: 5);

echo "   Original turns: {$session->getTurnCount()}\n";
echo "   Pruned turns: " . count($prunedTurns) . "\n";
echo "   Kept turns:\n";
foreach ($prunedTurns as $i => $turn) {
    echo "     {$turn->getUserInput()}\n";
}
echo "\n";

// Strategy 2: Time-Based Pruning
class TimeBasedPruner
{
    public function pruneByTime(Session $session, int $maxAgeSeconds = 3600): array
    {
        $now = microtime(true);
        $cutoff = $now - $maxAgeSeconds;
        
        return array_filter(
            $session->getTurns(),
            fn($turn) => $turn->getTimestamp() > $cutoff
        );
    }
}

echo "2. Time-Based Pruning (keep last 0.5 seconds):\n";
$timePruner = new TimeBasedPruner();
// Use small time window for demo (in production, use 3600 for 1 hour)
$recentTurns = $timePruner->pruneByTime($session, maxAgeSeconds: 0.5);

echo "   Original turns: {$session->getTurnCount()}\n";
echo "   Recent turns: " . count($recentTurns) . "\n\n";

// Strategy 3: Combined Pruning
class CombinedPruner
{
    private TranscriptPruner $importancePruner;
    private TimeBasedPruner $timePruner;
    
    public function __construct()
    {
        $this->importancePruner = new TranscriptPruner();
        $this->timePruner = new TimeBasedPruner();
    }
    
    public function prune(
        Session $session,
        int $keepCount,
        int $maxAgeSeconds = 3600
    ): array {
        // First, filter by time
        $timePruned = $this->timePruner->pruneByTime($session, $maxAgeSeconds);
        
        // Create temporary session with time-filtered turns
        $tempSession = new Session();
        foreach ($timePruned as $turn) {
            $tempSession->addTurn($turn);
        }
        
        // Then apply importance pruning
        if (count($timePruned) > $keepCount) {
            return $this->importancePruner->pruneByImportance($tempSession, $keepCount);
        }
        
        return $timePruned;
    }
}

echo "3. Combined Pruning (time + importance):\n";
$combinedPruner = new CombinedPruner();
$finalTurns = $combinedPruner->prune($session, keepCount: 5, maxAgeSeconds: 10);

echo "   Original turns: {$session->getTurnCount()}\n";
echo "   Final turns: " . count($finalTurns) . "\n";
echo "   Strategy: Time filter → Importance ranking\n\n";

// Example 4: Pruning Statistics
echo "4. Pruning Statistics:\n";
$allTurns = $session->getTurns();
$scores = [];

foreach ($allTurns as $i => $turn) {
    $pruner = new TranscriptPruner();
    $score = 0.0; // Would calculate via reflection, simplified here
    $input = strtolower($turn->getUserInput());
    
    // Simplified scoring for demo
    if (str_contains($input, 'name') || str_contains($input, 'email') || str_contains($input, 'remember')) {
        $score = 30.0;
    } elseif (str_contains($input, '?')) {
        $score = 10.0;
    } else {
        $score = 5.0;
    }
    
    $scores[] = [
        'turn' => ($i + 1),
        'input' => substr($turn->getUserInput(), 0, 40),
        'score' => $score,
    ];
}

usort($scores, fn($a, $b) => $b['score'] <=> $a['score']);

echo "   Top 5 most important turns:\n";
foreach (array_slice($scores, 0, 5) as $item) {
    echo "     Turn {$item['turn']}: {$item['input']}... (score: {$item['score']})\n";
}

echo "\n✅ Transcript pruning strategies demonstrated!\n";
