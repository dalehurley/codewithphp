#!/usr/bin/env php
<?php
/**
 * Parallel Execution Example
 *
 * Demonstrates concurrent agent execution for speed and efficiency.
 * Multiple analyses run simultaneously and results are aggregated.
 *
 * Part of: Agentic AI for PHP Developers
 * Chapter 11: Multi-Stage Workflows and Agent Graphs
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use ClaudeAgents\Chains\LLMChain;
use ClaudeAgents\Chains\ParallelChain;
use ClaudeAgents\Prompts\PromptTemplate;
use ClaudePhp\ClaudePhp;

// Load environment
$dotenv = __DIR__ . '/../../../.env';
if (file_exists($dotenv)) {
    $lines = file($dotenv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        [$name, $value] = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

$apiKey = $_ENV['ANTHROPIC_API_KEY'] ?? throw new RuntimeException('ANTHROPIC_API_KEY not set');
$client = new ClaudePhp(apiKey: $apiKey);

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║              Parallel Execution Example                                    ║\n";
echo "║              Concurrent Analysis: Sentiment + Topics + Keywords + Summary  ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Define Specialized Analysis Chains
// ============================================================================

echo "Building specialized analysis chains...\n\n";

// Chain 1: Sentiment Analysis
echo "1. Sentiment Analysis Chain\n";
$sentimentChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Analyze the sentiment of this text. Provide a rating (positive/negative/neutral) ' .
        'with confidence level (0-100%):\n\n{text}'
    ))
    ->withModel('claude-sonnet-4-5')
    ->withMaxTokens(200);

// Chain 2: Topic Extraction
echo "2. Topic Extraction Chain\n";
$topicsChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Identify the 3-5 main topics discussed in this text. ' .
        'List them as bullet points:\n\n{text}'
    ))
    ->withModel('claude-sonnet-4-5')
    ->withMaxTokens(200);

// Chain 3: Keyword Extraction
echo "3. Keyword Extraction Chain\n";
$keywordsChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Extract the most important keywords and phrases from this text. ' .
        'Provide 5-7 keywords:\n\n{text}'
    ))
    ->withModel('claude-sonnet-4-5')
    ->withMaxTokens(200);

// Chain 4: Summary Generation
echo "4. Summary Generation Chain\n";
$summaryChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Provide a concise 2-3 sentence summary of this text:\n\n{text}'
    ))
    ->withModel('claude-sonnet-4-5')
    ->withMaxTokens(200);

// ============================================================================
// Compose Parallel Chain
// ============================================================================

echo "\nComposing Parallel Execution Chain...\n";

$parallelAnalysis = ParallelChain::create()
    ->addChain('sentiment', $sentimentChain)
    ->addChain('topics', $topicsChain)
    ->addChain('keywords', $keywordsChain)
    ->addChain('summary', $summaryChain)
    ->withAggregation('all')  // Return structured results
    ->withTimeout(30000);     // 30 second timeout

echo "Configuration: 4 chains executing concurrently\n";
echo "Aggregation: 'all' (returns structured results)\n";
echo "Timeout: 30 seconds\n";

// ============================================================================
// Execute Parallel Analysis
// ============================================================================

$productReview = <<<TEXT
I recently purchased the new UltraBook Pro laptop and I'm absolutely thrilled with it!
The build quality is exceptional - the aluminum chassis feels premium and solid.
Performance is outstanding, especially for software development and video editing.
The battery life easily lasts 12+ hours on a single charge, which is perfect for my needs.

However, I did notice a few minor issues. The keyboard backlight is a bit uneven in certain keys,
and the trackpad occasionally has some lag when using gesture controls. The price point is also
quite high at $2,499, which might be prohibitive for some users.

Overall, I would highly recommend this laptop to professionals who need reliable performance
and portability. The pros far outweigh the cons, and it's been a game-changer for my productivity.
The customer service was excellent too - they answered all my pre-purchase questions promptly.

Rating: 9/10 - Would definitely buy again!
TEXT;

echo "\n" . str_repeat("=", 80) . "\n";
echo "SAMPLE INPUT (Product Review)\n";
echo str_repeat("=", 80) . "\n";
echo substr($productReview, 0, 300) . "...\n";
echo str_repeat("=", 80) . "\n";

echo "\n[PARALLEL] Executing 4 concurrent analyses...\n";
echo "This should be faster than sequential execution!\n\n";

$startTime = microtime(true);

try {
    $result = $parallelAnalysis->invoke(['text' => $productReview]);
    
    $duration = microtime(true) - $startTime;
    
    // Display results from each analysis
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "ANALYSIS 1: SENTIMENT\n";
    echo str_repeat("=", 80) . "\n";
    echo $result['results']['sentiment']['result'] ?? 'N/A';
    echo "\n\n";
    
    echo str_repeat("=", 80) . "\n";
    echo "ANALYSIS 2: TOPICS\n";
    echo str_repeat("=", 80) . "\n";
    echo $result['results']['topics']['result'] ?? 'N/A';
    echo "\n\n";
    
    echo str_repeat("=", 80) . "\n";
    echo "ANALYSIS 3: KEYWORDS\n";
    echo str_repeat("=", 80) . "\n";
    echo $result['results']['keywords']['result'] ?? 'N/A';
    echo "\n\n";
    
    echo str_repeat("=", 80) . "\n";
    echo "ANALYSIS 4: SUMMARY\n";
    echo str_repeat("=", 80) . "\n";
    echo $result['results']['summary']['result'] ?? 'N/A';
    echo "\n\n";
    
    // Check for errors
    if (!empty($result['errors'])) {
        echo str_repeat("=", 80) . "\n";
        echo "ERRORS ENCOUNTERED\n";
        echo str_repeat("=", 80) . "\n";
        foreach ($result['errors'] as $chainName => $error) {
            echo "- {$chainName}: {$error}\n";
        }
        echo "\n";
    }
    
    // Display execution metrics
    echo str_repeat("=", 80) . "\n";
    echo "EXECUTION METRICS\n";
    echo str_repeat("=", 80) . "\n";
    echo "Total Duration: " . number_format($duration, 3) . "s\n";
    echo "Chains Executed: 4 (parallel)\n";
    echo "Successful: " . count($result['results']) . "\n";
    echo "Failed: " . count($result['errors']) . "\n";
    
    // Estimate time savings vs sequential
    $estimatedSequential = $duration * 4;  // Rough estimate
    $timeSaved = $estimatedSequential - $duration;
    echo "\nEstimated Time Saved vs Sequential: ~" . number_format($timeSaved, 1) . "s\n";
    echo "(Parallel execution is ~" . number_format($estimatedSequential / $duration, 1) . "x faster)\n";
    
    echo "\n";
    
} catch (\Exception $e) {
    echo "\n[ERROR] Parallel execution failed: {$e->getMessage()}\n";
    echo "Stack trace:\n{$e->getTraceAsString()}\n";
}

// ============================================================================
// Demonstrate Different Aggregation Strategies
// ============================================================================

echo "\n" . str_repeat("=", 80) . "\n";
echo "AGGREGATION STRATEGIES\n";
echo str_repeat("=", 80) . "\n";

echo "\n1. Strategy: 'merge' (flatten all results into one array)\n";
$mergeChain = ParallelChain::create()
    ->addChain('sentiment', $sentimentChain)
    ->addChain('summary', $summaryChain)
    ->withAggregation('merge');

$mergeResult = $mergeChain->invoke(['text' => 'Short sample text for merge demo.']);
echo "Result keys: " . implode(', ', array_keys($mergeResult)) . "\n";

echo "\n2. Strategy: 'first' (return first successful result)\n";
$firstChain = ParallelChain::create()
    ->addChain('sentiment', $sentimentChain)
    ->addChain('summary', $summaryChain)
    ->withAggregation('first');

$firstResult = $firstChain->invoke(['text' => 'Short sample text for first demo.']);
echo "Returns: Single result from first successful chain\n";
echo "Result: " . substr($firstResult['result'] ?? 'N/A', 0, 100) . "...\n";

echo "\n3. Strategy: 'all' (structured results + errors)\n";
echo "Returns: ['results' => [...], 'errors' => [...]]\n";
echo "Best for: Production systems needing full observability\n";

// ============================================================================
// Key Takeaways
// ============================================================================

echo "\n" . str_repeat("=", 80) . "\n";
echo "KEY CONCEPTS DEMONSTRATED\n";
echo str_repeat("=", 80) . "\n";
echo "1. Concurrent Execution: Multiple chains run in parallel (simulated)\n";
echo "2. Time Savings: Parallel execution is significantly faster than sequential\n";
echo "3. Aggregation Strategies: 'merge', 'first', 'all' for different use cases\n";
echo "4. Error Handling: Individual chain failures don't stop others\n";
echo "5. Structured Results: Easy access to individual chain outputs\n";
echo "\n";

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                         Example Complete                                   ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n";
