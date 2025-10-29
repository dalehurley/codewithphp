<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/TextGenerator.php';

use Dotenv\Dotenv;

/**
 * Simple Text Generation Examples
 * 
 * Demonstrates using the TextGenerator class for various content creation tasks.
 * 
 * Prerequisites:
 * - Run: composer install
 * - Create .env file with OPENAI_API_KEY
 * 
 * Cost: ~$0.003 per run (3 API calls with gpt-3.5-turbo)
 */

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required('OPENAI_API_KEY')->notEmpty();

// Initialize client and generator
$client = OpenAI::client($_ENV['OPENAI_API_KEY']);
$generator = new TextGenerator($client);

echo "=== Text Generation Examples ===\n\n";

// Example 1: Creative story
echo "1. CREATIVE STORY\n";
echo "─────────────────────────────────────\n";
try {
    $story = $generator->generateStory(
        "A PHP developer discovers their code is creating portals to parallel universes"
    );
    echo $story['text'] . "\n\n";
    echo "Tokens: {$story['tokens']} | Cost: $" . number_format($story['cost'], 6) . "\n\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Example 2: Product description
echo "2. PRODUCT DESCRIPTION\n";
echo "─────────────────────────────────────\n";
try {
    $product = $generator->generateProductDescription(
        productName: "SmartCache Pro",
        features: [
            'Redis integration',
            'automatic expiration',
            'tag-based invalidation',
            'PSR-6 compliant'
        ],
    );
    echo $product['text'] . "\n\n";
    echo "Tokens: {$product['tokens']} | Cost: $" . number_format($product['cost'], 6) . "\n\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Example 3: Blog outline
echo "3. BLOG POST OUTLINE\n";
echo "─────────────────────────────────────\n";
try {
    $outline = $generator->generateBlogOutline(
        topic: "Best Practices for PHP 8.4 Development",
        sections: 6,
    );
    echo $outline['text'] . "\n\n";
    echo "Tokens: {$outline['tokens']} | Cost: $" . number_format($outline['cost'], 6) . "\n\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Calculate total cost
if (isset($story, $product, $outline)) {
    $totalCost = $story['cost'] + $product['cost'] + $outline['cost'];
    echo "Total estimated cost for all examples: $" . number_format($totalCost, 6) . "\n";
}
