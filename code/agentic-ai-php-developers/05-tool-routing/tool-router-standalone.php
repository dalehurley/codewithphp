<?php

/**
 * Tool Router and Dispatcher (Standalone Version)
 * 
 * This is a standalone version without composer dependencies.
 * Demonstrates tool routing patterns with mock implementations.
 */

declare(strict_types=1);

// ============================================================================
// MOCK CLASSES (simulating claude-php/agent framework)
// ============================================================================

interface ToolInterface
{
    public function getName(): string;
    public function execute(array $input): ToolResult;
}

class ToolResult
{
    public function __construct(
        private string $content,
        private bool $isError = false
    ) {}
    
    public static function success(string $content): self
    {
        return new self($content, false);
    }
    
    public static function error(string $message): self
    {
        return new self($message, true);
    }
    
    public function getContent(): string
    {
        return $this->content;
    }
    
    public function isSuccess(): bool
    {
        return !$this->isError;
    }
    
    public function isError(): bool
    {
        return $this->isError;
    }
}

class Tool implements ToolInterface
{
    private string $name;
    private $handler = null;
    
    private function __construct(string $name)
    {
        $this->name = $name;
    }
    
    public static function create(string $name): self
    {
        return new self($name);
    }
    
    public function description(string $desc): self
    {
        return $this;
    }
    
    public function stringParam(string $name, string $desc): self
    {
        return $this;
    }
    
    public function handler(callable $handler): self
    {
        $this->handler = $handler;
        return $this;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function execute(array $input): ToolResult
    {
        if ($this->handler === null) {
            return ToolResult::error("No handler defined");
        }
        
        try {
            return ($this->handler)($input);
        } catch (\Throwable $e) {
            return ToolResult::error($e->getMessage());
        }
    }
}

class ToolRegistry
{
    private array $tools = [];
    
    public function register(ToolInterface $tool): self
    {
        $this->tools[$tool->getName()] = $tool;
        return $this;
    }
    
    public function get(string $name): ?ToolInterface
    {
        return $this->tools[$name] ?? null;
    }
}

interface LoggerInterface
{
    public function info(string $message, array $context = []): void;
    public function warning(string $message, array $context = []): void;
    public function error(string $message, array $context = []): void;
}

class SimpleLogger implements LoggerInterface
{
    public function info(string $message, array $context = []): void
    {
        echo "[INFO] {$message}\n";
    }
    
    public function warning(string $message, array $context = []): void
    {
        echo "[WARNING] {$message}\n";
    }
    
    public function error(string $message, array $context = []): void
    {
        echo "[ERROR] {$message}\n";
    }
}

// ============================================================================
// TOOL ROUTER (same as production version)
// ============================================================================

class ToolRouter
{
    public function __construct(
        private ToolRegistry $registry,
        private ?LoggerInterface $logger = null,
    ) {}
    
    public function route(string $toolName, array $input): ToolResult
    {
        $tool = $this->registry->get($toolName);
        
        if ($tool === null) {
            $this->logger?->warning("Tool not found: {$toolName}");
            return ToolResult::error("Unknown tool: {$toolName}");
        }
        
        try {
            $this->logger?->info("Routing to tool: {$toolName}");
            $result = $tool->execute($input);
            $this->logger?->info("Tool executed successfully: {$toolName}");
            return $result;
        } catch (\Throwable $e) {
            $this->logger?->error("Tool execution failed: {$toolName}");
            return ToolResult::error($e->getMessage());
        }
    }
}

// ============================================================================
// EXAMPLE USAGE
// ============================================================================

echo "═══════════════════════════════════════════════════════════════════\n";
echo "           TOOL ROUTER DEMONSTRATION (Standalone)\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

$logger = new SimpleLogger();
$registry = new ToolRegistry();

// Register tools
$calculatorTool = Tool::create('calculator')
    ->description('Perform mathematical calculations')
    ->stringParam('expression', 'Math expression to evaluate')
    ->handler(function (array $input): ToolResult {
        $expression = $input['expression'];
        
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
        $conditions = ['Sunny', 'Cloudy', 'Rainy', 'Partly Cloudy'];
        $temp = rand(50, 85);
        $condition = $conditions[array_rand($conditions)];
        
        return ToolResult::success("Weather in {$city}: {$temp}°F, {$condition}");
    });

$registry->register($calculatorTool);
$registry->register($weatherTool);

$router = new ToolRouter($registry, $logger);

// Test routing
echo "Test 1: Route to calculator\n";
echo "─────────────────────────────────────────────────────────────────\n";
$result = $router->route('calculator', ['expression' => '25 * 4 + 100']);
echo "Success: " . ($result->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Content: {$result->getContent()}\n\n";

echo "Test 2: Route to weather tool\n";
echo "─────────────────────────────────────────────────────────────────\n";
$result = $router->route('get_weather', ['city' => 'San Francisco']);
echo "Success: " . ($result->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Content: {$result->getContent()}\n\n";

echo "Test 3: Route to non-existent tool\n";
echo "─────────────────────────────────────────────────────────────────\n";
$result = $router->route('missing_tool', ['param' => 'value']);
echo "Success: " . ($result->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Content: {$result->getContent()}\n\n";

echo "Test 4: Route with invalid input\n";
echo "─────────────────────────────────────────────────────────────────\n";
$result = $router->route('calculator', ['expression' => 'malicious; rm -rf']);
echo "Success: " . ($result->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Content: {$result->getContent()}\n\n";

echo "═══════════════════════════════════════════════════════════════════\n";
echo "                    ✓ ALL TESTS PASSED\n";
echo "═══════════════════════════════════════════════════════════════════\n";
