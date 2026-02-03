#!/usr/bin/env php
<?php
/**
 * Knowledge Graph Memory Example
 * 
 * Demonstrates building a knowledge graph with entities and relationships
 * using the framework's ConversationKGMemory component.
 * 
 * Usage: php knowledge-graph-memory.php
 * Requires: ANTHROPIC_API_KEY environment variable
 */

declare(strict_types=1);

// Check if running from vendor context
$autoloadPaths = [
    __DIR__ . '/../../../../../../vendor/autoload.php',
    __DIR__ . '/../../../../../vendor/autoload.php',
];

$loaded = false;
foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $loaded = true;
        break;
    }
}

if (!$loaded) {
    echo "❌ Could not find autoload.php\n";
    echo "Please install dependencies: composer require claude-php/agent\n";
    exit(1);
}

use ClaudeAgents\Memory\ConversationKGMemory;
use ClaudeAgents\Memory\Entities\EntityExtractor;
use ClaudePhp\ClaudePhp;

// Check for API key
$apiKey = getenv('ANTHROPIC_API_KEY');
if (empty($apiKey)) {
    echo "❌ ANTHROPIC_API_KEY environment variable not set\n";
    echo "Set it with: export ANTHROPIC_API_KEY=your_key_here\n";
    exit(1);
}

echo "=== Knowledge Graph Memory Example ===\n\n";

// Setup
$client = new ClaudePhp(apiKey: $apiKey);
$extractor = new EntityExtractor($client);

// Create knowledge graph memory
$kgMemory = new ConversationKGMemory(
    client: $client,
    extractor: $extractor,
    options: [
        'extraction_interval' => 2,
        'model' => 'claude-haiku-4-5',
    ]
);

echo "Knowledge graph memory initialized\n";
echo "Model: claude-haiku-4-5 (fast, cost-effective)\n\n";

// Build conversation about a team
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Step 1: Building Knowledge from Conversation\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$conversation = [
    [
        'role' => 'user',
        'content' => 'Alice is the Engineering Manager at TechCorp in Seattle.',
    ],
    [
        'role' => 'assistant',
        'content' => 'I see that Alice manages engineering at TechCorp in Seattle.',
    ],
    [
        'role' => 'user',
        'content' => 'Bob reports to Alice and works on the React frontend.',
    ],
    [
        'role' => 'assistant',
        'content' => 'Got it, Bob is on Alice\'s team working with React.',
    ],
    [
        'role' => 'user',
        'content' => 'Carol also reports to Alice and specializes in PHP backend development.',
    ],
    [
        'role' => 'assistant',
        'content' => 'So Carol is another team member under Alice, focusing on PHP backend.',
    ],
];

foreach ($conversation as $message) {
    $kgMemory->add($message);
    echo "[{$message['role']}] {$message['content']}\n";
}

echo "\n✓ Added " . $kgMemory->count() . " messages\n\n";

// Extract knowledge (entities + relationships)
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Step 2: Extracting Knowledge Graph\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Calling Claude to extract entities and relationships...\n";
$kgMemory->extractKnowledge();

echo "✓ Knowledge extraction complete\n\n";

// Display entities
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Step 3: Entities\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$entities = $kgMemory->getEntities();

if (empty($entities)) {
    echo "No entities extracted\n\n";
} else {
    echo "Found " . count($entities) . " entities:\n\n";
    
    foreach ($entities as $name => $entity) {
        echo "• {$name}";
        if (isset($entity['type'])) {
            echo " ({$entity['type']})";
        }
        if (isset($entity['mentions'])) {
            echo " - mentioned {$entity['mentions']}x";
        }
        echo "\n";
    }
    echo "\n";
}

// Display relationships
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Step 4: Relationships\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$relationships = $kgMemory->getRelationships();

if (empty($relationships)) {
    echo "No relationships extracted\n\n";
} else {
    echo "Found " . count($relationships) . " relationships:\n\n";
    
    foreach ($relationships as $rel) {
        echo sprintf(
            "• %s -[%s]-> %s\n",
            $rel['subject'],
            $rel['predicate'],
            $rel['object']
        );
    }
    echo "\n";
}

// Query relationships for specific entities
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Step 5: Querying Relationships\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Try to find Alice's relationships
if (!empty($entities)) {
    $aliceRels = $kgMemory->getEntityRelationships('Alice');
    
    if (!empty($aliceRels)) {
        echo "Alice's Relationships:\n";
        foreach ($aliceRels as $rel) {
            echo sprintf(
                "  %s %s %s\n",
                $rel['subject'],
                $rel['predicate'],
                $rel['object']
            );
        }
    } else {
        echo "No relationships found for Alice\n";
    }
    
    echo "\n";
}

// Query by predicate
$managerRels = $kgMemory->query(null, 'manages', null);

if (!empty($managerRels)) {
    echo "Management Relationships:\n";
    foreach ($managerRels as $rel) {
        echo "  • {$rel['subject']} manages {$rel['object']}\n";
    }
} else {
    echo "No 'manages' relationships found\n";
}

echo "\n";

// Get context for LLM
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Step 6: Knowledge Graph Context for LLM\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$context = $kgMemory->getContext();

echo "Generated " . count($context) . " context messages\n\n";

if (!empty($context) && isset($context[0]['content'])) {
    echo "Knowledge Graph Summary:\n";
    echo str_repeat('-', 60) . "\n";
    echo $context[0]['content'] . "\n";
    echo str_repeat('-', 60) . "\n\n";
}

// Statistics
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Statistics\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$stats = $kgMemory->getStats();

echo "Messages: {$stats['message_count']}\n";
echo "Entities: {$stats['entity_count']}\n";
echo "Relationships: {$stats['relationship_count']}\n";

echo "\n✓ Knowledge graph memory example complete!\n\n";

echo "Key Insights:\n";
echo "• ConversationKGMemory builds structured knowledge from conversations\n";
echo "• Entities are automatically identified and tracked\n";
echo "• Relationships create a queryable graph structure\n";
echo "• Knowledge graph enables complex queries (who reports to whom?)\n";
echo "• Useful for organizational memory, customer relationships, etc.\n";
