<?php

declare(strict_types=1);

/**
 * Example 07: Production Optimization System
 *
 * Comprehensive production-ready optimization system combining caching,
 * batching, model routing, token budgeting, and monitoring.
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Cache\FileCache;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

/**
 * Production optimization coordinator
 */
class ProductionOptimizer
{
    private FileCache $cache;
    private array $metrics = [];
    private array $config;
    
    public function __construct(
        private ClaudePhp $client,
        array $config = []
    ) {
        $this->config = array_merge([
            'cache_enabled' => true,
            'cache_ttl' => 3600,
            'model_routing_enabled' => true,
            'default_model' => 'claude-3-5-haiku-20241022',
            'complex_model' => 'claude-3-5-sonnet-20241022',
            'max_tokens_per_request' => 4000,
            'daily_token_budget' => 1_000_000,
            'daily_cost_budget' => 5.00,
        ], $config);
        
        // Initialize cache
        $cacheDir = __DIR__ . '/storage/cache';
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        $this->cache = new FileCache($cacheDir);
        
        $this->resetMetrics();
    }
    
    /**
     * Execute an optimized agent request
     */
    public function execute(string $query, array $options = []): array
    {
        $start = microtime(true);
        $requestId = uniqid('req_');
        
        echo "\n🚀 Request {$requestId}\n";
        echo "Query: " . substr($query, 0, 80) . (strlen($query) > 80 ? '...' : '') . "\n";
        
        // Step 1: Check cache
        if ($this->config['cache_enabled'] && !($options['skip_cache'] ?? false)) {
            $cached = $this->checkCache($query);
            if ($cached !== null) {
                $duration = microtime(true) - $start;
                $this->recordMetric('cache_hit', $duration, 0, 'cache');
                return $this->formatResponse($cached, $duration, true, 'cache');
            }
        }
        
        // Step 2: Select model based on complexity
        $model = $this->selectModel($query, $options);
        echo "Selected model: {$model}\n";
        
        // Step 3: Check token budget
        if (!$this->checkBudget()) {
            return [
                'success' => false,
                'error' => 'Token budget exceeded',
                'metrics' => $this->getMetrics(),
            ];
        }
        
        // Step 4: Execute request
        $result = $this->executeRequest($query, $model, $options);
        
        // Step 5: Cache result
        if ($this->config['cache_enabled'] && $result['success']) {
            $this->cacheResult($query, $result);
        }
        
        // Step 6: Record metrics
        $duration = microtime(true) - $start;
        $this->recordMetric('api_call', $duration, $result['tokens'] ?? 0, $model);
        
        return $result;
    }
    
    /**
     * Check cache for query
     */
    private function checkCache(string $query): ?array
    {
        $cacheKey = 'opt_query:' . md5($query);
        $cached = $this->cache->get($cacheKey);
        
        if ($cached !== null) {
            echo "✅ Cache HIT\n";
            return $cached;
        }
        
        echo "❌ Cache MISS\n";
        return null;
    }
    
    /**
     * Cache a result
     */
    private function cacheResult(string $query, array $result): void
    {
        $cacheKey = 'opt_query:' . md5($query);
        $this->cache->set($cacheKey, [
            'answer' => $result['answer'],
            'model' => $result['model'],
            'cached_at' => time(),
        ], $this->config['cache_ttl']);
    }
    
    /**
     * Select appropriate model based on query complexity
     */
    private function selectModel(string $query, array $options): string
    {
        if (isset($options['model'])) {
            return $options['model'];
        }
        
        if (!$this->config['model_routing_enabled']) {
            return $this->config['default_model'];
        }
        
        // Simple complexity heuristics
        $complexity = $this->analyzeComplexity($query);
        
        echo "Complexity: {$complexity}\n";
        
        return match ($complexity) {
            'complex' => $this->config['complex_model'],
            default => $this->config['default_model'],
        };
    }
    
    /**
     * Analyze query complexity
     */
    private function analyzeComplexity(string $query): string
    {
        $complexIndicators = [
            'analyze', 'compare', 'evaluate', 'explain why', 'reasoning',
            'detailed', 'comprehensive', 'architectural', 'design patterns',
        ];
        
        $queryLower = strtolower($query);
        foreach ($complexIndicators as $indicator) {
            if (str_contains($queryLower, $indicator)) {
                return 'complex';
            }
        }
        
        if (str_word_count($query) > 20) {
            return 'moderate';
        }
        
        return 'simple';
    }
    
    /**
     * Check if within token budget
     */
    private function checkBudget(): bool
    {
        $tokensUsed = $this->metrics['total_tokens'];
        $costIncurred = $this->metrics['total_cost'];
        
        if ($tokensUsed >= $this->config['daily_token_budget']) {
            echo "⚠️  Token budget exceeded!\n";
            return false;
        }
        
        if ($costIncurred >= $this->config['daily_cost_budget']) {
            echo "⚠️  Cost budget exceeded!\n";
            return false;
        }
        
        return true;
    }
    
    /**
     * Execute the actual request
     */
    private function executeRequest(string $query, string $model, array $options): array
    {
        try {
            $tool = Tool::create('search')
                ->description('Search for information')
                ->parameter('query', 'string', 'Search query')
                ->required('query')
                ->handler(fn($input) => "Results for: {$input['query']}");
            
            $agent = Agent::create($this->client)
                ->withTool($tool)
                ->withModel($model)
                ->maxIterations(3);
            
            $result = $agent->run($query);
            $usage = $result->getTokenUsage();
            $totalTokens = $usage['input'] + $usage['output'];
            
            return [
                'success' => true,
                'answer' => $result->getAnswer(),
                'model' => $model,
                'tokens' => $totalTokens,
                'token_breakdown' => $usage,
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'model' => $model,
                'tokens' => 0,
            ];
        }
    }
    
    /**
     * Format response
     */
    private function formatResponse(
        array $data,
        float $duration,
        bool $cached,
        string $source
    ): array {
        return [
            'success' => true,
            'answer' => $data['answer'],
            'model' => $data['model'] ?? 'unknown',
            'cached' => $cached,
            'source' => $source,
            'duration' => $duration,
            'metrics' => $this->getMetrics(),
        ];
    }
    
    /**
     * Record a metric
     */
    private function recordMetric(string $type, float $duration, int $tokens, string $model): void
    {
        $this->metrics['requests']++;
        $this->metrics['total_duration'] += $duration;
        $this->metrics['total_tokens'] += $tokens;
        
        if ($type === 'cache_hit') {
            $this->metrics['cache_hits']++;
        } else {
            $this->metrics['api_calls']++;
        }
        
        if (!isset($this->metrics['by_model'][$model])) {
            $this->metrics['by_model'][$model] = [
                'requests' => 0,
                'tokens' => 0,
                'duration' => 0,
            ];
        }
        
        $this->metrics['by_model'][$model]['requests']++;
        $this->metrics['by_model'][$model]['tokens'] += $tokens;
        $this->metrics['by_model'][$model]['duration'] += $duration;
        
        // Estimate cost
        $pricing = [
            'claude-3-5-sonnet-20241022' => 9.0,  // avg per million
            'claude-3-5-haiku-20241022' => 2.4,   // avg per million
            'cache' => 0,
        ];
        
        $cost = ($tokens * ($pricing[$model] ?? 2.4)) / 1_000_000;
        $this->metrics['total_cost'] += $cost;
    }
    
    /**
     * Reset metrics
     */
    private function resetMetrics(): void
    {
        $this->metrics = [
            'requests' => 0,
            'cache_hits' => 0,
            'api_calls' => 0,
            'total_tokens' => 0,
            'total_duration' => 0,
            'total_cost' => 0,
            'by_model' => [],
        ];
    }
    
    /**
     * Get metrics
     */
    public function getMetrics(): array
    {
        return $this->metrics;
    }
    
    /**
     * Print detailed metrics
     */
    public function printMetrics(): void
    {
        $m = $this->metrics;
        
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "PRODUCTION OPTIMIZATION METRICS\n";
        echo str_repeat("=", 60) . "\n\n";
        
        echo "Overall Performance:\n";
        echo "  Total Requests: {$m['requests']}\n";
        echo "  Cache Hits: {$m['cache_hits']} (" . 
             number_format(($m['cache_hits'] / max(1, $m['requests'])) * 100, 1) . "%)\n";
        echo "  API Calls: {$m['api_calls']}\n";
        echo "  Total Duration: " . number_format($m['total_duration'], 2) . "s\n";
        echo "  Avg Duration: " . number_format($m['total_duration'] / max(1, $m['requests']), 3) . "s\n\n";
        
        echo "Token Usage:\n";
        echo "  Total Tokens: " . number_format($m['total_tokens']) . "\n";
        echo "  Budget: " . number_format($this->config['daily_token_budget']) . "\n";
        echo "  Usage: " . number_format(($m['total_tokens'] / $this->config['daily_token_budget']) * 100, 1) . "%\n\n";
        
        echo "Cost:\n";
        echo "  Total Cost: $" . number_format($m['total_cost'], 4) . "\n";
        echo "  Budget: $" . number_format($this->config['daily_cost_budget'], 2) . "\n";
        echo "  Usage: " . number_format(($m['total_cost'] / $this->config['daily_cost_budget']) * 100, 1) . "%\n";
        echo "  Avg Cost/Request: $" . number_format($m['total_cost'] / max(1, $m['requests']), 6) . "\n\n";
        
        if (!empty($m['by_model'])) {
            echo "By Model:\n";
            foreach ($m['by_model'] as $model => $stats) {
                echo "  {$model}:\n";
                echo "    Requests: {$stats['requests']}\n";
                echo "    Tokens: " . number_format($stats['tokens']) . "\n";
                echo "    Duration: " . number_format($stats['duration'], 2) . "s\n";
            }
        }
        
        echo "\n" . str_repeat("=", 60) . "\n";
    }
}

// Demo
echo "=== Production Optimization System Demo ===\n";

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

$optimizer = new ProductionOptimizer($client, [
    'cache_enabled' => true,
    'cache_ttl' => 3600,
    'model_routing_enabled' => true,
    'daily_token_budget' => 100_000,
    'daily_cost_budget' => 1.00,
]);

$queries = [
    'What is PHP?',
    'What is PHP?', // Duplicate - should hit cache
    'List 3 popular PHP frameworks',
    'Analyze the architectural differences between monolithic and microservices architectures in detail',
    'What is Docker?',
    'List 3 popular PHP frameworks', // Duplicate
];

foreach ($queries as $i => $query) {
    $response = $optimizer->execute($query);
    
    if ($response['success']) {
        echo "Answer: " . substr($response['answer'], 0, 80) . "...\n";
        if (isset($response['cached']) && $response['cached']) {
            echo "💚 Served from cache\n";
        }
    } else {
        echo "Error: {$response['error']}\n";
    }
}

$optimizer->printMetrics();

echo "\n✅ Production optimization system operational!\n";
echo "💡 Combines: caching + model routing + token budgeting + monitoring\n";
