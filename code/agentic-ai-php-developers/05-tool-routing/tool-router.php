<?php

/**
 * Tool Router and Dispatcher
 * 
 * Demonstrates:
 * - Basic tool routing
 * - Tool lookup and dispatch
 * - Error handling for missing tools
 * - Logging tool executions
 */

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use ClaudeAgents\Tools\ToolRegistry;
use ClaudeAgents\Tools\Tool;
use ClaudeAgents\Tools\ToolResult;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// ============================================================================
// BASIC TOOL ROUTER
// ============================================================================

class ToolRouter
{
    public function __construct(
        private ToolRegistry $registry,
        private ?\Psr\Log\LoggerInterface $logger = null,
    ) {}
    
    /**
     * Route a tool call to the appropriate handler.
     */
    public function route(string $toolName, array $input): ToolResult
    {
        // 1. Find tool
        $tool = $this->registry->get($toolName);
        
        if ($tool === null) {
            $this->logger?->warning("Tool not found: {$toolName}");
            return ToolResult::error("Unknown tool: {$toolName}");
        }
        
        // 2. Execute
        try {
            $this->logger?->info("Routing to tool: {$toolName}", [
                'input_keys' => array_keys($input),
            ]);
            
            $result = $tool->execute($input);
            
            $this->logger?->info("Tool executed successfully: {$toolName}");
            
            return $result;
            
        } catch (\Throwable $e) {
            $this->logger?->error("Tool execution failed: {$toolName}", [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
            
            return ToolResult::error($e->getMessage());
        }
    }
}

// ============================================================================
// EXAMPLE USAGE
// ============================================================================

// Setup logger
$logger = new Logger('router');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

// Create registry
$registry = new ToolRegistry();

// Register some tools
$calculatorTool = Tool::create('calculator')
    ->description('Perform mathematical calculations')
    ->stringParam('expression', 'Math expression to evaluate')
    ->handler(function (array $input): ToolResult {
        $expression = $input['expression'];
        
        // Basic validation
        if (!preg_match('/^[\d\s\+\-\*\/\(\)\.]+$/', $expression)) {
            return ToolResult::error('Invalid expression format');
        }
        
        try {
            $result = eval("return {$expression};");
            return ToolResult::success("Result: {$result}");
        } catch (\Throwable $e) {
            return ToolResult::error("Calculation error: {$e->getMessage()}");
        }
    });

$weatherTool = Tool::create('get_weather')
    ->description('Get current weather for a city')
    ->stringParam('city', 'City name')
    ->handler(function (array $input): ToolResult {
        $city = $input['city'];
        
        // Simulate API call
        $conditions = ['Sunny', 'Cloudy', 'Rainy', 'Partly Cloudy'];
        $temp = rand(50, 85);
        $condition = $conditions[array_rand($conditions)];
        
        return ToolResult::success(
            "Weather in {$city}: {$temp}°F, {$condition}"
        );
    });

$registry->register($calculatorTool);
$registry->register($weatherTool);

// Create router
$router = new ToolRouter($registry, $logger);

// ============================================================================
// TEST ROUTING
// ============================================================================

echo "═══════════════════════════════════════════════════════════════════\n";
echo "                    TOOL ROUTER DEMONSTRATION\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

// Test 1: Route to calculator
echo "Test 1: Route to calculator\n";
echo "─────────────────────────────────────────────────────────────────\n";
$result = $router->route('calculator', ['expression' => '25 * 4 + 100']);
echo "Success: " . ($result->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Content: {$result->getContent()}\n\n";

// Test 2: Route to weather tool
echo "Test 2: Route to weather tool\n";
echo "─────────────────────────────────────────────────────────────────\n";
$result = $router->route('get_weather', ['city' => 'San Francisco']);
echo "Success: " . ($result->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Content: {$result->getContent()}\n\n";

// Test 3: Route to non-existent tool
echo "Test 3: Route to non-existent tool\n";
echo "─────────────────────────────────────────────────────────────────\n";
$result = $router->route('missing_tool', ['param' => 'value']);
echo "Success: " . ($result->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Content: {$result->getContent()}\n\n";

// Test 4: Route with invalid input
echo "Test 4: Route with invalid calculator input\n";
echo "─────────────────────────────────────────────────────────────────\n";
$result = $router->route('calculator', ['expression' => 'malicious; rm -rf']);
echo "Success: " . ($result->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Content: {$result->getContent()}\n\n";

echo "═══════════════════════════════════════════════════════════════════\n";
echo "                           COMPLETE\n";
echo "═══════════════════════════════════════════════════════════════════\n";
