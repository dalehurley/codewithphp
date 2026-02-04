<?php

declare(strict_types=1);

/**
 * Example 01: Response Caching
 *
 * Demonstrates how to cache agent responses to avoid redundant API calls
 * for identical or similar queries, significantly reducing costs and latency.
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Cache\FileCache;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

// Initialize client
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

// Set up file-based cache
$cacheDir = __DIR__ . '/storage/cache';
if (!file_exists($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

$cache = new FileCache($cacheDir);

// Create a simple search tool that we'll cache responses for
$searchTool = Tool::create('search_documentation')
    ->description('Search technical documentation for information')
    ->parameter('query', 'string', 'Search query')
    ->required('query')
    ->handler(function (array $input): string {
        // Simulate expensive API call or database search
        echo "🔍 [EXPENSIVE] Executing actual search for: {$input['query']}\n";
        sleep(1); // Simulate latency
        
        // Mock results
        $results = [
            'PHP array functions' => 'PHP provides array_map, array_filter, array_reduce for functional programming.',
            'error handling' => 'Use try-catch blocks and custom exception classes for robust error handling.',
            'performance tips' => 'Enable OPcache, use generators for memory efficiency, minimize I/O operations.',
        ];
        
        foreach ($results as $topic => $info) {
            if (stripos($topic, $input['query']) !== false) {
                return $info;
            }
        }
        
        return "No documentation found for: {$input['query']}";
    });

// Create agent with caching wrapper
$agent = Agent::create($client)
    ->withTool($searchTool)
    ->withSystemPrompt('You are a helpful technical assistant. When searching documentation, provide concise answers.')
    ->withModel('claude-3-5-sonnet-20241022')
    ->maxIterations(3);

/**
 * Cached agent execution function
 */
function cachedAgentRun(Agent $agent, FileCache $cache, string $query): array
{
    $start = microtime(true);
    
    // Generate cache key from query
    $cacheKey = 'agent_response:' . md5($query);
    
    // Check cache first
    $cached = $cache->get($cacheKey);
    
    if ($cached !== null) {
        $duration = microtime(true) - $start;
        echo "✅ Cache HIT! Retrieved in " . number_format($duration * 1000, 2) . "ms\n";
        return [
            'answer' => $cached['answer'],
            'cached' => true,
            'duration' => $duration,
            'tokens' => $cached['tokens'] ?? 0,
        ];
    }
    
    // Cache miss - execute agent
    echo "❌ Cache MISS - executing agent...\n";
    $result = $agent->run($query);
    
    $duration = microtime(true) - $start;
    $tokens = $result->getTokenUsage();
    
    // Store in cache
    $cacheData = [
        'answer' => $result->getAnswer(),
        'tokens' => $tokens['input'] + $tokens['output'],
        'cached_at' => date('Y-m-d H:i:s'),
    ];
    
    $cache->set($cacheKey, $cacheData, 3600); // 1 hour TTL
    
    return [
        'answer' => $result->getAnswer(),
        'cached' => false,
        'duration' => $duration,
        'tokens' => $tokens['input'] + $tokens['output'],
    ];
}

// Demo: Show cache effectiveness
echo "=== Response Caching Demo ===\n\n";

$queries = [
    'How do I handle errors in PHP?',
    'How do I handle errors in PHP?', // Duplicate - should hit cache
    'What are PHP array functions?',
    'What are PHP array functions?', // Duplicate - should hit cache
];

$totalTokens = 0;
$totalDuration = 0;
$cacheHits = 0;

foreach ($queries as $i => $query) {
    echo "\n--- Query " . ($i + 1) . " ---\n";
    echo "Q: {$query}\n\n";
    
    $response = cachedAgentRun($agent, $cache, $query);
    
    echo "A: " . substr($response['answer'], 0, 100) . "...\n";
    echo "Duration: " . number_format($response['duration'] * 1000, 2) . "ms\n";
    echo "Tokens: {$response['tokens']}\n";
    
    if ($response['cached']) {
        $cacheHits++;
    }
    
    $totalTokens += $response['tokens'];
    $totalDuration += $response['duration'];
}

// Show savings
echo "\n=== Cache Performance Summary ===\n";
echo "Total queries: " . count($queries) . "\n";
echo "Cache hits: {$cacheHits}\n";
echo "Cache hit rate: " . number_format(($cacheHits / count($queries)) * 100, 1) . "%\n";
echo "Total tokens used: {$totalTokens}\n";
echo "Total duration: " . number_format($totalDuration * 1000, 2) . "ms\n";
echo "Average per query: " . number_format(($totalDuration / count($queries)) * 1000, 2) . "ms\n";

// Token cost calculation (Claude 3.5 Sonnet pricing)
$inputCost = ($totalTokens * 0.003) / 1000; // $3 per million tokens (approximation)
echo "\nEstimated cost: $" . number_format($inputCost, 4) . "\n";

// Show what cost would be without caching
if ($cacheHits > 0 && $cacheHits < count($queries)) {
    $cacheHitRate = $cacheHits / count($queries);
    $noCacheTokens = $totalTokens / (1 - $cacheHitRate);
    $noCacheCost = ($noCacheTokens * 0.003) / 1000;
    echo "Cost without cache: $" . number_format($noCacheCost, 4) . "\n";
    $savings = $noCacheCost - $inputCost;
    if ($noCacheCost > 0) {
        echo "Savings: $" . number_format($savings, 4) . " (" . 
             number_format(($savings / $noCacheCost) * 100, 1) . "%)\n";
    }
} else {
    echo "Cost without cache: Same (no cache hits or all cache hits)\n";
}

echo "\n✅ Caching reduces both latency and costs significantly!\n";
