#!/usr/bin/env php
<?php
/**
 * Entity Memory Tracking Example
 * 
 * Demonstrates entity extraction and tracking using the framework's EntityMemory.
 * Extracts people, places, organizations, and other entities from conversations.
 * 
 * Usage: php entity-memory-tracking.php
 * Requires: ANTHROPIC_API_KEY environment variable
 */

declare(strict_types=1);

// Check if running from vendor context
$autoloadPaths = [
    __DIR__ . '/../../../../../../vendor/autoload.php', // From tutorial code
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

use ClaudeAgents\Memory\EntityMemory;
use ClaudeAgents\Memory\Entities\EntityExtractor;
use ClaudeAgents\Memory\Entities\EntityStore;
use ClaudePhp\ClaudePhp;

// Check for API key
$apiKey = getenv('ANTHROPIC_API_KEY');
if (empty($apiKey)) {
    echo "❌ ANTHROPIC_API_KEY environment variable not set\n";
    echo "Set it with: export ANTHROPIC_API_KEY=your_key_here\n";
    exit(1);
}

echo "=== Entity Memory Tracking Example ===\n\n";

// Setup
$client = new ClaudePhp(apiKey: $apiKey);
$extractor = new EntityExtractor($client);
$entityStore = new EntityStore();

// Create entity memory with extraction interval of 2 messages
$entityMemory = new EntityMemory(
    extractor: $extractor,
    store: $entityStore,
    extractionInterval: 2
);

echo "Entity extraction configured (interval: 2 messages)\n\n";

// Simulate a conversation with entity-rich content
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Step 1: Building Conversation with Entities\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$conversation = [
    [
        'role' => 'user',
        'content' => 'I work at Acme Corporation in San Francisco.',
    ],
    [
        'role' => 'assistant',
        'content' => 'That\'s great! San Francisco is a wonderful city for tech companies like Acme Corporation.',
    ],
    [
        'role' => 'user',
        'content' => 'Yes, I report to Sarah Johnson, our Chief Technology Officer.',
    ],
    [
        'role' => 'assistant',
        'content' => 'It sounds like you have a great reporting structure with Sarah Johnson as CTO.',
    ],
    [
        'role' => 'user',
        'content' => 'We use Laravel and PHP for most of our projects, deployed on AWS.',
    ],
    [
        'role' => 'assistant',
        'content' => 'Laravel and PHP are excellent choices, and AWS provides great infrastructure.',
    ],
];

foreach ($conversation as $message) {
    $entityMemory->add($message);
    echo "[{$message['role']}] {$message['content']}\n";
}

echo "\n✓ Added " . $entityMemory->count() . " messages to memory\n\n";

// Extract entities
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Step 2: Extracting Entities\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

echo "Calling Claude to extract entities...\n";
$entityMemory->extractEntities();

echo "✓ Entity extraction complete\n\n";

// Display extracted entities
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Step 3: Extracted Entities\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$entities = $entityMemory->getEntityStore()->all();

if (empty($entities)) {
    echo "No entities extracted (this might happen if the API call failed)\n\n";
} else {
    echo "Found " . count($entities) . " unique entities:\n\n";
    
    foreach ($entities as $name => $entity) {
        echo "Entity: {$name}\n";
        echo "  Type: {$entity['type']}\n";
        echo "  Mentions: {$entity['mentions']}\n";
        
        if (!empty($entity['attributes'])) {
            echo "  Attributes:\n";
            foreach ($entity['attributes'] as $key => $value) {
                echo "    • {$key}: {$value}\n";
            }
        }
        
        echo "\n";
    }
}

// Search entities by type
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Step 4: Searching by Entity Type\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$entityTypes = ['person', 'organization', 'location', 'technology'];

foreach ($entityTypes as $type) {
    $entitiesOfType = $entityMemory->getEntitiesByType($type);
    
    if (!empty($entitiesOfType)) {
        echo ucfirst($type) . "s:\n";
        foreach ($entitiesOfType as $name => $entity) {
            echo "  • {$name} (mentioned {$entity['mentions']}x)\n";
        }
        echo "\n";
    }
}

// Search entities by query
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Step 5: Semantic Entity Search\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$searchQueries = ['San Francisco', 'technology', 'person'];

foreach ($searchQueries as $query) {
    echo "Search: \"{$query}\"\n";
    $results = $entityMemory->searchEntities($query);
    
    if (empty($results)) {
        echo "  No matches\n";
    } else {
        foreach ($results as $name => $entity) {
            echo "  • {$name} ({$entity['type']})\n";
        }
    }
    
    echo "\n";
}

// Get context for LLM
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Step 6: Context for LLM\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$context = $entityMemory->getContext();

echo "Generated " . count($context) . " context messages:\n\n";

// Show entity summary (first message)
if (!empty($context) && isset($context[0]['content'])) {
    echo "Entity Summary Message:\n";
    echo str_repeat('-', 60) . "\n";
    echo $context[0]['content'] . "\n";
    echo str_repeat('-', 60) . "\n\n";
}

// Statistics
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Statistics\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

$stats = $entityMemory->getStats();

echo "Message count: {$stats['message_count']}\n";
echo "Entity count: {$stats['entity_stats']['total_entities']}\n";

if (isset($stats['entity_stats']['by_type'])) {
    echo "\nEntities by type:\n";
    foreach ($stats['entity_stats']['by_type'] as $type => $count) {
        echo "  • {$type}: {$count}\n";
    }
}

echo "\n✓ Entity memory tracking example complete!\n\n";

echo "Key Takeaways:\n";
echo "• EntityMemory automatically extracts entities from conversations\n";
echo "• Entities are categorized by type (person, place, organization, etc.)\n";
echo "• Entity information is included in LLM context for richer responses\n";
echo "• Search and filter entities by type or query\n";
