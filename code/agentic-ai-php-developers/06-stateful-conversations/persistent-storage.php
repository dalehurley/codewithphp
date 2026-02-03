<?php

/**
 * Persistent Storage
 * 
 * Demonstrates saving and loading sessions with FileSessionStorage.
 */

require_once '/Users/dalehurley/Code/claude-php-agent/vendor/autoload.php';

use ClaudeAgents\Conversation\Session;
use ClaudeAgents\Conversation\Turn;
use ClaudeAgents\Conversation\ConversationManager;
use ClaudeAgents\Conversation\Storage\FileSessionStorage;
use ClaudeAgents\Conversation\Storage\JsonSessionSerializer;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

echo "=== Persistent Storage ===\n\n";

// Setup
$storageDir = __DIR__ . '/sessions';
$serializer = new JsonSessionSerializer();
$storage = new FileSessionStorage($storageDir, $serializer);

// Optional: Use logger if Monolog is available
$logger = null;
if (class_exists('Monolog\Logger')) {
    $logger = new \Monolog\Logger('storage');
    $logger->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::INFO));
}

$manager = new ConversationManager([
    'storage' => $storage,
    'session_timeout' => 3600,
    'logger' => $logger,
]);

echo "Storage directory: {$storageDir}\n\n";

// Example 1: Create and save session
echo "1. Creating and saving a session:\n";
$session = $manager->createSession('user_123');
$session->updateState('language', 'en');
$session->updateState('timezone', 'America/Los_Angeles');

// Add some conversation
$turn1 = new Turn(
    userInput: "Hello!",
    agentResponse: "Hi! How can I help you today?",
    metadata: ['tokens' => 20]
);
$session->addTurn($turn1);

$turn2 = new Turn(
    userInput: "What's the weather?",
    agentResponse: "It's currently sunny and 75°F.",
    metadata: ['tokens' => 25]
);
$session->addTurn($turn2);

// Save to storage
$saved = $manager->saveSession($session);
echo "   Session saved: " . ($saved ? 'Yes' : 'No') . "\n";
echo "   Session ID: {$session->getId()}\n";
echo "   Turn count: {$session->getTurnCount()}\n\n";

// Example 2: Load session
echo "2. Loading session from storage:\n";
$sessionId = $session->getId();
$loaded = $manager->getSession($sessionId);

if ($loaded) {
    echo "   Session loaded successfully!\n";
    echo "   Session ID: {$loaded->getId()}\n";
    echo "   Turn count: {$loaded->getTurnCount()}\n";
    echo "   State: " . json_encode($loaded->getState()) . "\n\n";
}

// Example 3: Multiple sessions
echo "3. Creating multiple sessions:\n";
$session2 = $manager->createSession('user_456');
$session2->updateState('language', 'es');
$session2->addTurn(new Turn("Hola", "¡Hola! ¿Cómo puedo ayudarte?"));
$manager->saveSession($session2);

$session3 = $manager->createSession('user_789');
$session3->updateState('language', 'fr');
$session3->addTurn(new Turn("Bonjour", "Bonjour! Comment puis-je vous aider?"));
$manager->saveSession($session3);

$allSessions = $storage->listSessions();
echo "   Total sessions: " . count($allSessions) . "\n\n";

// Example 4: Find sessions by user
echo "4. Finding sessions by user:\n";
$userSessions = $manager->getSessionsByUser('user_123');
echo "   Sessions for user_123: " . count($userSessions) . "\n";
foreach ($userSessions as $s) {
    echo "     - {$s->getId()} ({$s->getTurnCount()} turns)\n";
}
echo "\n";

// Example 5: Session exists check
echo "5. Checking if session exists:\n";
$exists = $storage->exists($sessionId);
echo "   Session {$sessionId} exists: " . ($exists ? 'Yes' : 'No') . "\n\n";

// Example 6: Resume and continue conversation
echo "6. Resuming conversation:\n";
$resumed = $manager->getSession($sessionId);
if ($resumed) {
    echo "   Resumed session: {$resumed->getId()}\n";
    echo "   Previous turns: {$resumed->getTurnCount()}\n";
    
    // Add new turn
    $newTurn = new Turn(
        userInput: "Tell me more",
        agentResponse: "I'd be happy to provide more information!"
    );
    $resumed->addTurn($newTurn);
    
    // Save updated session
    $manager->saveSession($resumed);
    
    echo "   Added new turn. Total turns: {$resumed->getTurnCount()}\n\n";
}

// Example 7: Delete session
echo "7. Deleting a session:\n";
$deleted = $manager->deleteSession($session3->getId());
echo "   Session deleted: " . ($deleted ? 'Yes' : 'No') . "\n";

$remainingSessions = $storage->listSessions();
echo "   Remaining sessions: " . count($remainingSessions) . "\n\n";

// Example 8: Load all from storage
echo "8. Loading all sessions from storage:\n";
$manager2 = new ConversationManager([
    'storage' => $storage,
    'session_timeout' => 3600,
]);

$loadedCount = $manager2->loadAllFromStorage();
echo "   Loaded {$loadedCount} sessions from storage\n\n";

// Cleanup
echo "9. Cleanup:\n";
foreach ($storage->listSessions() as $sid) {
    $storage->delete($sid);
}
echo "   All sessions deleted\n";

if (is_dir($storageDir) && count(glob($storageDir . '/*')) === 0) {
    rmdir($storageDir);
    echo "   Storage directory removed\n";
}

echo "\n✅ Persistent storage complete!\n";
