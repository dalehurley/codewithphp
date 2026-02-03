<?php

/**
 * Basic Session Management
 * 
 * Demonstrates creating sessions, adding turns, and managing conversation state.
 */

require_once '/Users/dalehurley/Code/claude-php-agent/vendor/autoload.php';

use ClaudeAgents\Conversation\Session;
use ClaudeAgents\Conversation\Turn;
use ClaudeAgents\Conversation\ConversationManager;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

echo "=== Basic Session Management ===\n\n";

// Example 1: Create a session
echo "1. Creating a session:\n";
$session = new Session();

echo "Session ID: {$session->getId()}\n";
echo "Created at: " . date('Y-m-d H:i:s', (int)$session->getCreatedAt()) . "\n";
echo "Turn count: {$session->getTurnCount()}\n\n";

// Example 2: Add state to session
echo "2. Adding session state:\n";
$session->updateState('user_id', 'user_123');
$session->updateState('language', 'en');
$session->updateState('topic', 'weather');

print_r($session->getState());
echo "\n";

// Example 3: Add turns
echo "3. Adding conversation turns:\n";
$turn1 = new Turn(
    userInput: "What's the weather in San Francisco?",
    agentResponse: "It's currently 72°F and sunny in San Francisco.",
    metadata: ['tokens' => 45, 'model' => 'claude-sonnet-4-5']
);

$session->addTurn($turn1);

$turn2 = new Turn(
    userInput: "What about tomorrow?",
    agentResponse: "Tomorrow in San Francisco will be 68°F with clouds.",
    metadata: ['tokens' => 38, 'model' => 'claude-sonnet-4-5']
);

$session->addTurn($turn2);

echo "Turn count: {$session->getTurnCount()}\n";
echo "Last activity: " . date('Y-m-d H:i:s', (int)$session->getLastActivity()) . "\n\n";

// Example 4: Access turns
echo "4. Accessing conversation history:\n";
foreach ($session->getTurns() as $i => $turn) {
    echo "Turn " . ($i + 1) . ":\n";
    echo "  User: {$turn->getUserInput()}\n";
    echo "  Agent: {$turn->getAgentResponse()}\n";
    echo "  Timestamp: " . date('H:i:s', (int)$turn->getTimestamp()) . "\n";
    echo "\n";
}

// Example 5: Using ConversationManager
echo "5. Using ConversationManager:\n";

// Optional: Use logger if Monolog is available
$logger = null;
if (class_exists('Monolog\Logger')) {
    $logger = new \Monolog\Logger('conversations');
    $logger->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::INFO));
}

$manager = new ConversationManager([
    'max_sessions' => 1000,
    'session_timeout' => 3600,
    'logger' => $logger,
]);

// Create sessions for different users
$session1 = $manager->createSession('user_123');
$session2 = $manager->createSession('user_456');

echo "Created sessions for two users\n";
echo "Session 1 ID: {$session1->getId()}\n";
echo "Session 2 ID: {$session2->getId()}\n\n";

// Retrieve session
$retrieved = $manager->getSession($session1->getId());
if ($retrieved) {
    echo "Successfully retrieved session: {$retrieved->getId()}\n";
}

// Get all sessions for a user
$userSessions = $manager->getSessionsByUser('user_123');
echo "Sessions for user_123: " . count($userSessions) . "\n\n";

// Delete session
$manager->deleteSession($session2->getId());
echo "Deleted session: {$session2->getId()}\n";

echo "\n✅ Basic session management complete!\n";
