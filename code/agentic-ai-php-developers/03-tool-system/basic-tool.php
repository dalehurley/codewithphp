<?php

declare(strict_types=1);

/**
 * Basic Tool Example
 * 
 * Demonstrates the fundamental structure of a tool:
 * - Tool creation with fluent API
 * - Parameter definition
 * - Handler implementation
 * - Direct execution (without agent)
 */

// Load from the claude-php-agent project in the workspace
require_once __DIR__ . '/../../../../claude-php-agent/vendor/autoload.php';

use ClaudeAgents\Tools\Tool;
use ClaudeAgents\Tools\ToolResult;

echo "=== Basic Tool Example ===\n\n";

// Create a simple calculator tool
$calculator = Tool::create('calculate')
    ->description('Perform mathematical calculations')
    ->stringParam('expression', 'Mathematical expression to evaluate (e.g., "25 * 17 + 42")')
    ->handler(function (array $input): ToolResult {
        $expression = trim($input['expression']);
        
        // Validation
        if (empty($expression)) {
            return ToolResult::error('Expression cannot be empty');
        }
        
        // Basic arithmetic only (security: no functions, variables, etc.)
        if (!preg_match('/^[0-9+\-*\/().\s]+$/', $expression)) {
            return ToolResult::error('Invalid expression: only numbers and operators allowed');
        }
        
        try {
            // Evaluate safely
            $result = eval("return {$expression};");
            
            if ($result === false) {
                return ToolResult::error('Failed to evaluate expression');
            }
            
            return ToolResult::success([
                'result' => $result,
                'expression' => $expression,
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error("Calculation error: {$e->getMessage()}");
        }
    });

// Display tool metadata
echo "Tool Name: " . $calculator->getName() . "\n";
echo "Description: " . $calculator->getDescription() . "\n";
echo "Schema:\n";
print_r($calculator->getInputSchema());
echo "\n";

// Test 1: Valid expression
echo "Test 1: Valid expression\n";
$result1 = $calculator->execute(['expression' => '25 * 17 + 42']);
echo "Success: " . ($result1->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Content: " . $result1->getContent() . "\n";
echo "\n";

// Test 2: Another valid expression
echo "Test 2: Another valid expression\n";
$result2 = $calculator->execute(['expression' => '(100 + 50) / 3']);
echo "Success: " . ($result2->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Content: " . $result2->getContent() . "\n";
echo "\n";

// Test 3: Empty expression (error case)
echo "Test 3: Empty expression (should error)\n";
$result3 = $calculator->execute(['expression' => '']);
echo "Success: " . ($result3->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Error: " . ($result3->isError() ? 'Yes' : 'No') . "\n";
echo "Content: " . $result3->getContent() . "\n";
echo "\n";

// Test 4: Invalid characters (security check)
echo "Test 4: Invalid expression with forbidden characters (should error)\n";
$result4 = $calculator->execute(['expression' => '10 + eval("2 + 2")']);
echo "Success: " . ($result4->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Error: " . ($result4->isError() ? 'Yes' : 'No') . "\n";
echo "Content: " . $result4->getContent() . "\n";
echo "\n";

// Example: Converting to API definition format
echo "=== API Definition Format ===\n";
$definition = $calculator->toDefinition();
echo json_encode($definition, JSON_PRETTY_PRINT);
echo "\n";

echo "\n✅ Basic tool example complete!\n";
