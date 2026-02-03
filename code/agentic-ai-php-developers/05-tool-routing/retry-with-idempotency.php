<?php

/**
 * Retry Logic with Idempotency
 * 
 * Demonstrates:
 * - Idempotency key generation
 * - Caching execution results
 * - Safe retries without side effects
 * - Exponential backoff
 * - Retryable error detection
 */

declare(strict_types=1);

// ============================================================================
// IDEMPOTENCY KEY GENERATOR
// ============================================================================

class IdempotencyKeyGenerator
{
    /**
     * Generate an idempotency key from tool name and input.
     */
    public static function generate(string $toolName, array $input): string
    {
        // Normalize input for consistent hashing
        $normalized = self::normalizeInput($input);
        
        // Create hash
        $hash = hash('sha256', $toolName . json_encode($normalized));
        
        return substr($hash, 0, 32);
    }
    
    /**
     * Normalize input for consistent hashing.
     */
    private static function normalizeInput(array $input): array
    {
        // Sort keys
        ksort($input);
        
        // Recursively normalize nested arrays
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $input[$key] = self::normalizeInput($value);
            }
        }
        
        return $input;
    }
}

// ============================================================================
// IDEMPOTENCY CACHE
// ============================================================================

class IdempotencyCache
{
    private array $cache = [];
    private int $ttl = 3600; // 1 hour
    
    public function __construct(int $ttl = 3600)
    {
        $this->ttl = $ttl;
    }
    
    /**
     * Check if we've seen this execution before and return cached result.
     */
    public function get(string $key): ?array
    {
        if (!isset($this->cache[$key])) {
            return null;
        }
        
        $entry = $this->cache[$key];
        
        // Check if expired
        if (time() > $entry['expires_at']) {
            unset($this->cache[$key]);
            return null;
        }
        
        return $entry;
    }
    
    /**
     * Store execution result for idempotency.
     */
    public function set(string $key, bool $success, string $content): void
    {
        $this->cache[$key] = [
            'success' => $success,
            'content' => $content,
            'stored_at' => time(),
            'expires_at' => time() + $this->ttl,
        ];
    }
    
    /**
     * Clear expired entries.
     */
    public function cleanup(): void
    {
        $now = time();
        
        foreach ($this->cache as $key => $entry) {
            if ($now > $entry['expires_at']) {
                unset($this->cache[$key]);
            }
        }
    }
    
    /**
     * Get cache statistics.
     */
    public function getStats(): array
    {
        return [
            'total_entries' => count($this->cache),
            'size_bytes' => strlen(serialize($this->cache)),
        ];
    }
}

// ============================================================================
// TOOL ERROR CODE (from previous example)
// ============================================================================

class ToolErrorCode
{
    public const NETWORK_ERROR = 'NETWORK_ERROR';
    public const SERVICE_UNAVAILABLE = 'SERVICE_UNAVAILABLE';
    public const RATE_LIMIT_EXCEEDED = 'RATE_LIMIT_EXCEEDED';
    public const EXECUTION_TIMEOUT = 'EXECUTION_TIMEOUT';
    public const INTERNAL_ERROR = 'INTERNAL_ERROR';
    public const VALIDATION_FAILED = 'VALIDATION_FAILED';
    
    public static function isRetryable(string $code): bool
    {
        return in_array($code, [
            self::NETWORK_ERROR,
            self::SERVICE_UNAVAILABLE,
            self::RATE_LIMIT_EXCEEDED,
            self::EXECUTION_TIMEOUT,
            self::INTERNAL_ERROR,
        ]);
    }
}

// ============================================================================
// RETRYABLE TOOL EXECUTOR
// ============================================================================

class RetryableToolExecutor
{
    public IdempotencyCache $cache;
    private array $executionLog = [];
    
    public function __construct(
        private int $maxRetries = 3,
        private int $baseDelayMs = 1000,
        ?IdempotencyCache $cache = null,
    ) {
        $this->cache = $cache ?? new IdempotencyCache();
    }
    
    /**
     * Execute a tool with retry logic and idempotency.
     */
    public function execute(string $toolName, array $input, callable $executor): array
    {
        // Generate idempotency key
        $idempotencyKey = IdempotencyKeyGenerator::generate($toolName, $input);
        
        $this->log("Generated idempotency key: {$idempotencyKey}");
        
        // Check cache for previous execution
        $cached = $this->cache->get($idempotencyKey);
        if ($cached !== null) {
            $this->log("✓ Returning cached result (idempotent)");
            return [
                'success' => $cached['success'],
                'content' => $cached['content'],
                'cached' => true,
                'idempotency_key' => $idempotencyKey,
            ];
        }
        
        // Execute with retries
        $attempt = 0;
        $delay = $this->baseDelayMs;
        
        while ($attempt < $this->maxRetries) {
            $attempt++;
            
            $this->log("Attempt {$attempt}/{$this->maxRetries}");
            
            try {
                // Execute the tool
                $result = $executor($input);
                
                $success = $result['success'] ?? true;
                $content = $result['content'] ?? '';
                $errorCode = $result['error_code'] ?? null;
                
                // Success - cache and return
                if ($success) {
                    $this->log("✓ Execution successful");
                    $this->cache->set($idempotencyKey, true, $content);
                    
                    return [
                        'success' => true,
                        'content' => $content,
                        'cached' => false,
                        'attempts' => $attempt,
                        'idempotency_key' => $idempotencyKey,
                    ];
                }
                
                // Check if retryable
                $isRetryable = $errorCode && ToolErrorCode::isRetryable($errorCode);
                
                if (!$isRetryable) {
                    $this->log("✗ Error not retryable, returning immediately");
                    return [
                        'success' => false,
                        'content' => $content,
                        'cached' => false,
                        'attempts' => $attempt,
                        'idempotency_key' => $idempotencyKey,
                    ];
                }
                
                // Last attempt - return error
                if ($attempt >= $this->maxRetries) {
                    $this->log("✗ Max retries exceeded");
                    return [
                        'success' => false,
                        'content' => $content,
                        'cached' => false,
                        'attempts' => $attempt,
                        'idempotency_key' => $idempotencyKey,
                    ];
                }
                
                // Retry with exponential backoff
                $this->log("↻ Retrying in {$delay}ms (retryable error)");
                usleep($delay * 1000);
                $delay *= 2; // Exponential backoff
                
            } catch (\Throwable $e) {
                $this->log("✗ Exception: {$e->getMessage()}");
                
                if ($attempt >= $this->maxRetries) {
                    return [
                        'success' => false,
                        'content' => "Error: {$e->getMessage()}",
                        'cached' => false,
                        'attempts' => $attempt,
                        'idempotency_key' => $idempotencyKey,
                    ];
                }
                
                $this->log("↻ Retrying in {$delay}ms");
                usleep($delay * 1000);
                $delay *= 2;
            }
        }
        
        return [
            'success' => false,
            'content' => 'Unexpected retry loop exit',
            'cached' => false,
            'attempts' => $attempt,
            'idempotency_key' => $idempotencyKey,
        ];
    }
    
    private function log(string $message): void
    {
        $this->executionLog[] = sprintf("[%s] %s", date('H:i:s'), $message);
    }
    
    public function getExecutionLog(): array
    {
        return $this->executionLog;
    }
    
    public function clearLog(): void
    {
        $this->executionLog = [];
    }
}

// ============================================================================
// EXAMPLE TOOLS WITH DIFFERENT BEHAVIORS
// ============================================================================

class ExampleTools
{
    private static int $callCount = 0;
    
    /**
     * Tool that succeeds immediately.
     */
    public static function successfulTool(array $input): array
    {
        self::$callCount++;
        return [
            'success' => true,
            'content' => "Success on call " . self::$callCount,
        ];
    }
    
    /**
     * Tool that fails with retryable error, then succeeds.
     */
    public static function retryableTool(array $input): array
    {
        self::$callCount++;
        
        if (self::$callCount < 3) {
            return [
                'success' => false,
                'content' => 'Network error occurred',
                'error_code' => ToolErrorCode::NETWORK_ERROR,
            ];
        }
        
        return [
            'success' => true,
            'content' => 'Success after retries',
        ];
    }
    
    /**
     * Tool that fails with non-retryable error.
     */
    public static function nonRetryableTool(array $input): array
    {
        return [
            'success' => false,
            'content' => 'Validation failed: invalid input',
            'error_code' => ToolErrorCode::VALIDATION_FAILED,
        ];
    }
    
    public static function resetCallCount(): void
    {
        self::$callCount = 0;
    }
}

// ============================================================================
// DEMONSTRATIONS
// ============================================================================

echo "═══════════════════════════════════════════════════════════════════\n";
echo "          RETRY LOGIC WITH IDEMPOTENCY DEMONSTRATION\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

$executor = new RetryableToolExecutor(maxRetries: 3, baseDelayMs: 100);

// ============================================================================
// Test 1: Successful execution (cached on retry)
// ============================================================================

echo "Test 1: Successful Execution with Caching\n";
echo "─────────────────────────────────────────────────────────────────\n";

ExampleTools::resetCallCount();

// First execution
$result1 = $executor->execute('test_tool', ['param' => 'value'], [ExampleTools::class, 'successfulTool']);
echo "First execution:\n";
echo "  Success: " . ($result1['success'] ? 'Yes' : 'No') . "\n";
echo "  Cached: " . ($result1['cached'] ? 'Yes' : 'No') . "\n";
echo "  Content: {$result1['content']}\n";
echo "  Idempotency Key: {$result1['idempotency_key']}\n\n";

// Second execution (same input - should be cached)
$result2 = $executor->execute('test_tool', ['param' => 'value'], [ExampleTools::class, 'successfulTool']);
echo "Second execution (same input):\n";
echo "  Success: " . ($result2['success'] ? 'Yes' : 'No') . "\n";
echo "  Cached: " . ($result2['cached'] ? 'Yes' : 'No') . "\n";
echo "  Content: {$result2['content']}\n";
echo "  Note: Content is same as first execution (cached)\n\n";

echo "Execution Log:\n";
foreach ($executor->getExecutionLog() as $log) {
    echo "  {$log}\n";
}
echo "\n";

// ============================================================================
// Test 2: Retryable error (succeeds after retries)
// ============================================================================

echo "Test 2: Retryable Error (Succeeds After Retries)\n";
echo "─────────────────────────────────────────────────────────────────\n";

ExampleTools::resetCallCount();
$executor->clearLog();

$result = $executor->execute('network_tool', ['url' => 'https://api.example.com'], [ExampleTools::class, 'retryableTool']);
echo "Final result:\n";
echo "  Success: " . ($result['success'] ? 'Yes' : 'No') . "\n";
echo "  Attempts: {$result['attempts']}\n";
echo "  Content: {$result['content']}\n\n";

echo "Execution Log:\n";
foreach ($executor->getExecutionLog() as $log) {
    echo "  {$log}\n";
}
echo "\n";

// ============================================================================
// Test 3: Non-retryable error (fails immediately)
// ============================================================================

echo "Test 3: Non-Retryable Error (Fails Immediately)\n";
echo "─────────────────────────────────────────────────────────────────\n";

ExampleTools::resetCallCount();
$executor->clearLog();

$result = $executor->execute('validation_tool', ['data' => 'invalid'], [ExampleTools::class, 'nonRetryableTool']);
echo "Final result:\n";
echo "  Success: " . ($result['success'] ? 'Yes' : 'No') . "\n";
echo "  Attempts: {$result['attempts']}\n";
echo "  Content: {$result['content']}\n\n";

echo "Execution Log:\n";
foreach ($executor->getExecutionLog() as $log) {
    echo "  {$log}\n";
}
echo "\n";

// ============================================================================
// Cache Statistics
// ============================================================================

echo "═══════════════════════════════════════════════════════════════════\n";
echo "                       CACHE STATISTICS\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

$stats = $executor->cache->getStats();
echo "Total cached entries: {$stats['total_entries']}\n";
echo "Cache size: {$stats['size_bytes']} bytes\n\n";

echo "═══════════════════════════════════════════════════════════════════\n";
echo "                           COMPLETE\n";
echo "═══════════════════════════════════════════════════════════════════\n";
