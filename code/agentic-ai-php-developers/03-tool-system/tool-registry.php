<?php

declare(strict_types=1);

/**
 * Tool Registry Example
 * 
 * Demonstrates the ToolRegistry for managing multiple tools:
 * - Registration
 * - Discovery
 * - Execution
 * - Dynamic tool management
 */

// Load from the claude-php-agent project in the workspace
require_once __DIR__ . '/../../../../claude-php-agent/vendor/autoload.php';

use ClaudeAgents\Tools\Tool;
use ClaudeAgents\Tools\ToolResult;
use ClaudeAgents\Tools\ToolRegistry;

echo "=== Tool Registry Example ===\n\n";

// Create a registry
$registry = new ToolRegistry();

echo "Initial tool count: " . $registry->count() . "\n\n";

// Create some tools
$calculator = Tool::create('calculate')
    ->description('Perform mathematical calculations')
    ->stringParam('expression', 'Math expression')
    ->handler(fn($input) => ToolResult::success(['result' => eval("return {$input['expression']};")]));

$uppercase = Tool::create('uppercase')
    ->description('Convert text to uppercase')
    ->stringParam('text', 'Text to convert')
    ->handler(fn($input) => ToolResult::success(['result' => strtoupper($input['text'])]));

$reverse = Tool::create('reverse')
    ->description('Reverse a string')
    ->stringParam('text', 'Text to reverse')
    ->handler(fn($input) => ToolResult::success(['result' => strrev($input['text'])]));

// Register tools individually
echo "=== Registering Tools ===\n";
$registry->register($calculator);
echo "Registered: calculator\n";

$registry->register($uppercase);
echo "Registered: uppercase\n";

$registry->register($reverse);
echo "Registered: reverse\n";

echo "\nTotal tools: " . $registry->count() . "\n\n";

// Get tool names
echo "=== Tool Names ===\n";
$names = $registry->names();
foreach ($names as $name) {
    echo "- {$name}\n";
}
echo "\n";

// Check if tool exists
echo "=== Tool Existence Check ===\n";
echo "Has 'calculator'? " . ($registry->has('calculator') ? 'Yes' : 'No') . "\n";
echo "Has 'weather'? " . ($registry->has('weather') ? 'Yes' : 'No') . "\n\n";

// Get a specific tool
echo "=== Get Specific Tool ===\n";
$tool = $registry->get('calculator');
if ($tool !== null) {
    echo "Tool name: " . $tool->getName() . "\n";
    echo "Description: " . $tool->getDescription() . "\n";
}
echo "\n";

// Execute via registry
echo "=== Execute via Registry ===\n";
$result1 = $registry->execute('calculate', ['expression' => '25 * 17']);
echo "Calculate: Success=" . ($result1->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Result: " . $result1->getContent() . "\n\n";

$result2 = $registry->execute('uppercase', ['text' => 'hello world']);
echo "Uppercase: Success=" . ($result2->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Result: " . $result2->getContent() . "\n\n";

$result3 = $registry->execute('reverse', ['text' => 'Hello World']);
echo "Reverse: Success=" . ($result3->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Result: " . $result3->getContent() . "\n\n";

// Try to execute non-existent tool
echo "=== Execute Non-Existent Tool ===\n";
$result4 = $registry->execute('nonexistent', ['param' => 'value']);
echo "Success: " . ($result4->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Error: " . $result4->getContent() . "\n\n";

// Get all tools
echo "=== Get All Tools ===\n";
$allTools = $registry->all();
echo "Total tools retrieved: " . count($allTools) . "\n";
foreach ($allTools as $tool) {
    echo "- {$tool->getName()}: {$tool->getDescription()}\n";
}
echo "\n";

// Get tool definitions for API
echo "=== Tool Definitions (for API calls) ===\n";
$definitions = $registry->toDefinitions();
echo json_encode($definitions, JSON_PRETTY_PRINT);
echo "\n\n";

// Register multiple tools at once
echo "=== Register Multiple Tools ===\n";
$wordcount = Tool::create('wordcount')
    ->description('Count words in text')
    ->stringParam('text', 'Text to count words in')
    ->handler(fn($input) => ToolResult::success(['count' => str_word_count($input['text'])]));

$length = Tool::create('length')
    ->description('Get string length')
    ->stringParam('text', 'Text to measure')
    ->handler(fn($input) => ToolResult::success(['length' => strlen($input['text'])]));

$registry->registerMany([$wordcount, $length]);
echo "Registered: wordcount, length\n";
echo "Total tools: " . $registry->count() . "\n\n";

// Remove a tool
echo "=== Remove Tool ===\n";
$registry->remove('reverse');
echo "Removed: reverse\n";
echo "Total tools: " . $registry->count() . "\n";
echo "Has 'reverse'? " . ($registry->has('reverse') ? 'Yes' : 'No') . "\n\n";

// Dynamic tool registry pattern
echo "=== Dynamic Tool Registry Pattern ===\n";

class DynamicToolRegistry extends ToolRegistry
{
    private array $config;
    
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->registerDefaultTools();
    }
    
    private function registerDefaultTools(): void
    {
        // Always available
        $this->register(Tool::create('echo')
            ->description('Echo text back')
            ->stringParam('text', 'Text to echo')
            ->handler(fn($input) => ToolResult::success(['echo' => $input['text']])));
        
        // Conditional registration
        if ($this->config['enable_math'] ?? false) {
            $this->register(Tool::create('calculate')
                ->description('Math calculator')
                ->stringParam('expression', 'Expression')
                ->handler(fn($input) => ToolResult::success(['result' => eval("return {$input['expression']};")])));
        }
        
        if ($this->config['enable_string'] ?? false) {
            $this->register(Tool::create('uppercase')
                ->description('Convert to uppercase')
                ->stringParam('text', 'Text')
                ->handler(fn($input) => ToolResult::success(['result' => strtoupper($input['text'])])));
        }
    }
}

$dynamicRegistry = new DynamicToolRegistry([
    'enable_math' => true,
    'enable_string' => true,
]);

echo "Dynamic registry tools: " . $dynamicRegistry->count() . "\n";
echo "Tools: " . implode(', ', $dynamicRegistry->names()) . "\n\n";

// Clear all tools
echo "=== Clear All Tools ===\n";
echo "Before clear: " . $registry->count() . " tools\n";
$registry->clear();
echo "After clear: " . $registry->count() . " tools\n\n";

echo "✅ Tool registry example complete!\n";
echo "\nRegistry Operations:\n";
echo "- register() - Add single tool\n";
echo "- registerMany() - Add multiple tools\n";
echo "- get() - Retrieve specific tool\n";
echo "- has() - Check tool existence\n";
echo "- all() - Get all tools\n";
echo "- names() - Get tool names\n";
echo "- execute() - Execute tool by name\n";
echo "- toDefinitions() - Get API definitions\n";
echo "- remove() - Remove tool\n";
echo "- clear() - Remove all tools\n";
echo "- count() - Get tool count\n";
