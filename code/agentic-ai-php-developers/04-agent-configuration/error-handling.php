<?php

/**
 * Error Handling Patterns
 * 
 * Demonstrates robust error handling:
 * - Try-catch patterns at agent level
 * - Exception type handling
 * - Graceful degradation
 * - Tool-level error handling
 * - Error recovery strategies
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Tools\Tool;
use ClaudeAgents\Tools\ToolResult;
use ClaudePhp\ClaudePhp;

// Example exception classes (framework provides these)
class AgentException extends \Exception {}
class ToolExecutionException extends \Exception {
    public function __construct(
        string $message,
        private string $toolName
    ) {
        parent::__construct($message);
    }
    
    public function getToolName(): string
    {
        return $this->toolName;
    }
}

// Example 1: Basic Error Handling
echo "=== Example 1: Basic Error Handling ===\n\n";

function safeAgentRun(Agent $agent, string $input): string
{
    try {
        $result = $agent->run($input);
        return $result->getAnswer();
        
    } catch (ToolExecutionException $e) {
        echo "Tool error: {$e->getToolName()} - {$e->getMessage()}\n";
        return "I encountered an error with the {$e->getToolName()} tool.";
        
    } catch (AgentException $e) {
        echo "Agent error: {$e->getMessage()}\n";
        return "I couldn't complete that task due to a technical issue.";
        
    } catch (\Exception $e) {
        echo "Unexpected error: {$e->getMessage()}\n";
        return "An unexpected error occurred. Please try again.";
    }
}

echo "Example of safe agent wrapper (no actual execution)\n\n";

// Example 2: Tool-Level Error Handling
echo "=== Example 2: Tool-Level Error Handling ===\n\n";

// Tool that handles errors gracefully
$databaseTool = Tool::create('query_database')
    ->description('Query the user database')
    ->parameter('query', 'string', 'SQL query to execute')
    ->required('query')
    ->handler(function (array $input) {
        echo "Executing query: {$input['query']}\n";
        
        try {
            // Simulate database query
            if (str_contains(strtolower($input['query']), 'drop')) {
                throw new \RuntimeException('DROP statements are not allowed');
            }
            
            if (str_contains(strtolower($input['query']), 'timeout')) {
                throw new \RuntimeException('Database connection timeout');
            }
            
            return ToolResult::success([
                'rows' => [
                    ['id' => 1, 'name' => 'Alice'],
                    ['id' => 2, 'name' => 'Bob'],
                ],
                'count' => 2,
            ]);
            
        } catch (\RuntimeException $e) {
            echo "  Error caught in tool handler: {$e->getMessage()}\n";
            
            return ToolResult::error(
                "Database query failed: {$e->getMessage()}"
            );
        }
    });

// Simulate tool execution results
echo "Simulating database tool execution:\n";

// Success case
echo "\nTest 1: Valid SELECT query\n";
echo "Executing query: SELECT * FROM users\n";
echo "Success: {\"rows\":[{\"id\":1,\"name\":\"Alice\"},{\"id\":2,\"name\":\"Bob\"}],\"count\":2}\n\n";

// Error case
echo "Test 2: Invalid DROP query\n";
echo "Executing query: DROP TABLE users\n";
echo "  Error caught in tool handler: DROP statements are not allowed\n";
echo "Error: Database query failed: DROP statements are not allowed\n\n";

// Example 3: Graceful Degradation
echo "=== Example 3: Graceful Degradation ===\n\n";

class FallbackHandler
{
    private Agent $primaryAgent;
    private ?Agent $fallbackAgent;
    
    public function __construct(Agent $primaryAgent, ?Agent $fallbackAgent = null)
    {
        $this->primaryAgent = $primaryAgent;
        $this->fallbackAgent = $fallbackAgent;
    }
    
    public function run(string $input): string
    {
        // Try primary agent
        try {
            echo "Attempting primary agent...\n";
            $result = $this->primaryAgent->run($input);
            echo "✓ Primary agent succeeded\n";
            return $result->getAnswer();
            
        } catch (\Exception $e) {
            echo "✗ Primary agent failed: {$e->getMessage()}\n";
            
            // Try fallback agent if available
            if ($this->fallbackAgent !== null) {
                try {
                    echo "Attempting fallback agent...\n";
                    $result = $this->fallbackAgent->run($input);
                    echo "✓ Fallback agent succeeded\n";
                    return $result->getAnswer();
                    
                } catch (\Exception $fallbackError) {
                    echo "✗ Fallback agent also failed: {$fallbackError->getMessage()}\n";
                }
            }
            
            // Return safe default
            return $this->getDefaultResponse($input);
        }
    }
    
    private function getDefaultResponse(string $input): string
    {
        return "I'm currently unable to process your request. Please try again later.";
    }
}

echo "Example of graceful degradation (no actual execution)\n\n";

// Example 4: Retry with Exponential Backoff
echo "=== Example 4: Retry with Exponential Backoff ===\n\n";

class RetryHandler
{
    public function __construct(
        private int $maxAttempts = 3,
        private int $baseDelay = 1000
    ) {}
    
    public function execute(callable $fn): mixed
    {
        $attempt = 1;
        
        while ($attempt <= $this->maxAttempts) {
            try {
                echo "Attempt $attempt...\n";
                $result = $fn();
                echo "✓ Success on attempt $attempt\n";
                return $result;
                
            } catch (\Exception $e) {
                echo "✗ Attempt $attempt failed: {$e->getMessage()}\n";
                
                if ($attempt >= $this->maxAttempts) {
                    echo "Max attempts reached, giving up\n";
                    throw $e;
                }
                
                $delay = $this->baseDelay * pow(2, $attempt - 1);
                echo "  Waiting {$delay}ms before retry...\n";
                usleep($delay * 1000);
                
                $attempt++;
            }
        }
    }
}

$retryHandler = new RetryHandler(maxAttempts: 3, baseDelay: 500);

// Simulate a function that fails twice then succeeds
$attemptCount = 0;
try {
    $retryHandler->execute(function () use (&$attemptCount) {
        $attemptCount++;
        if ($attemptCount < 3) {
            throw new \RuntimeException("Temporary failure");
        }
        return "Success!";
    });
} catch (\Exception $e) {
    echo "Final error: {$e->getMessage()}\n";
}

echo "\n";

// Example 5: Error Categorization
echo "=== Example 5: Error Categorization ===\n\n";

class ErrorHandler
{
    public function categorize(\Exception $e): string
    {
        return match (true) {
            $e instanceof \InvalidArgumentException => 'validation',
            $e instanceof \RuntimeException && str_contains($e->getMessage(), 'timeout') => 'timeout',
            $e instanceof \RuntimeException && str_contains($e->getMessage(), 'rate limit') => 'rate_limit',
            $e instanceof \RuntimeException => 'transient',
            default => 'unknown',
        };
    }
    
    public function shouldRetry(string $category): bool
    {
        return in_array($category, ['timeout', 'rate_limit', 'transient']);
    }
    
    public function getUserMessage(string $category): string
    {
        return match ($category) {
            'validation' => 'Invalid input provided. Please check your request.',
            'timeout' => 'Request timed out. Please try again.',
            'rate_limit' => 'Too many requests. Please wait a moment.',
            'transient' => 'Temporary error. Please try again.',
            default => 'An error occurred. Please contact support.',
        };
    }
}

$errorHandler = new ErrorHandler();

$errors = [
    new \InvalidArgumentException('Missing required field'),
    new \RuntimeException('Database connection timeout'),
    new \RuntimeException('Rate limit exceeded'),
    new \RuntimeException('Temporary failure'),
    new \LogicException('Unexpected state'),
];

foreach ($errors as $error) {
    $category = $errorHandler->categorize($error);
    $shouldRetry = $errorHandler->shouldRetry($category);
    $message = $errorHandler->getUserMessage($category);
    
    echo sprintf(
        "Error: %s\n  Category: %s\n  Retry: %s\n  Message: %s\n\n",
        $error->getMessage(),
        $category,
        $shouldRetry ? 'Yes' : 'No',
        $message
    );
}

// Example 6: Error Logging and Reporting
echo "=== Example 6: Error Logging and Reporting ===\n\n";

class ErrorReporter
{
    private array $errors = [];
    
    public function report(\Exception $e, array $context = []): void
    {
        $this->errors[] = [
            'timestamp' => time(),
            'type' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'context' => $context,
        ];
    }
    
    public function getReport(): array
    {
        return [
            'total_errors' => count($this->errors),
            'errors' => $this->errors,
            'most_common' => $this->getMostCommonError(),
        ];
    }
    
    private function getMostCommonError(): ?string
    {
        if (empty($this->errors)) {
            return null;
        }
        
        $counts = [];
        foreach ($this->errors as $error) {
            $type = $error['type'];
            $counts[$type] = ($counts[$type] ?? 0) + 1;
        }
        
        arsort($counts);
        return array_key_first($counts);
    }
}

$reporter = new ErrorReporter();

// Simulate various errors
$reporter->report(new \RuntimeException('Database timeout'), ['query' => 'SELECT * FROM users']);
$reporter->report(new \RuntimeException('API rate limit'), ['endpoint' => '/api/data']);
$reporter->report(new \InvalidArgumentException('Invalid input'), ['field' => 'email']);
$reporter->report(new \RuntimeException('Network error'), ['host' => 'api.example.com']);

$report = $reporter->getReport();
echo "Error Report:\n";
echo "  Total errors: {$report['total_errors']}\n";
echo "  Most common: {$report['most_common']}\n";

echo "\n✅ Error handling examples completed!\n";
