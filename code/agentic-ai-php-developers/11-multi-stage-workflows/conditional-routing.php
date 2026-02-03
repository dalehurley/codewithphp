#!/usr/bin/env php
<?php
/**
 * Conditional Routing Example
 *
 * Demonstrates RouterChain for dynamic workflow routing based on
 * input conditions. Routes requests to specialized chains.
 *
 * Part of: Agentic AI for PHP Developers
 * Chapter 11: Multi-Stage Workflows and Agent Graphs
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use ClaudeAgents\Chains\LLMChain;
use ClaudeAgents\Chains\RouterChain;
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
echo "║              Conditional Routing Example                                   ║\n";
echo "║              RouterChain: Dynamic Workflow Routing                         ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Define Specialized Processing Chains
// ============================================================================

echo "Building specialized processing chains...\n\n";

// Chain 1: Code Review
echo "1. Code Review Chain (for PHP code)\n";
$codeReviewChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Review this PHP code for bugs, security issues, and best practices:\n\n{content}\n\n' .
        'Provide: 1) Issues found, 2) Security concerns, 3) Recommendations'
    ))
    ->withModel('claude-sonnet-4-5')
    ->withMaxTokens(1000);

// Chain 2: Documentation Review
echo "2. Documentation Review Chain (for docs)\n";
$documentationChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Review this documentation:\n\n{content}\n\n' .
        'Provide: 1) Clarity assessment, 2) Completeness check, 3) Improvement suggestions'
    ))
    ->withModel('claude-sonnet-4-5')
    ->withMaxTokens(1000);

// Chain 3: Question Answering
echo "3. Question Answering Chain (for questions)\n";
$questionAnswerChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Answer this question thoroughly and accurately:\n\n{content}\n\n' .
        'Provide a clear, detailed answer with examples if appropriate.'
    ))
    ->withModel('claude-sonnet-4-5')
    ->withMaxTokens(800);

// Chain 4: General Processing
echo "4. General Processing Chain (default)\n";
$generalChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Process this request:\n\n{content}\n\n' .
        'Provide appropriate assistance based on the content.'
    ))
    ->withModel('claude-sonnet-4-5')
    ->withMaxTokens(800);

// ============================================================================
// Create Router with Conditions
// ============================================================================

echo "\nBuilding RouterChain with conditional logic...\n";

$router = RouterChain::create()
    // Route 1: Code content (contains PHP tags or function/class keywords)
    ->addRoute(
        function (array $input): bool {
            $content = strtolower($input['content'] ?? '');
            return str_contains($content, '<?php') ||
                   str_contains($content, 'function ') ||
                   str_contains($content, 'class ') ||
                   str_contains($content, 'namespace ');
        },
        $codeReviewChain
    )
    // Route 2: Documentation (contains doc keywords or markdown)
    ->addRoute(
        function (array $input): bool {
            $content = strtolower($input['content'] ?? '');
            return str_contains($content, 'documentation') ||
                   str_contains($content, '/**') ||
                   str_contains($content, '# ') ||
                   str_contains($content, 'readme');
        },
        $documentationChain
    )
    // Route 3: Questions (ends with question mark)
    ->addRoute(
        function (array $input): bool {
            $content = trim($input['content'] ?? '');
            return str_ends_with($content, '?');
        },
        $questionAnswerChain
    )
    // Default: General processing
    ->setDefault($generalChain);

echo "Router configured with 3 conditions + default fallback\n";

// ============================================================================
// Test Router with Different Inputs
// ============================================================================

$testCases = [
    [
        'name' => 'PHP Code',
        'content' => <<<'CODE'
<?php
function calculateDiscount($price, $percentage) {
    return $price - ($price * $percentage / 100);
}

$finalPrice = calculateDiscount(100, 20);
echo $finalPrice;
CODE
    ],
    [
        'name' => 'Documentation',
        'content' => <<<'DOC'
# API Documentation

This is the documentation for our User API endpoint.

## GET /api/users/{id}

Returns user information for the specified user ID.

### Parameters
- id (integer): The user ID

### Response
Returns a JSON object containing user details.
DOC
    ],
    [
        'name' => 'Question',
        'content' => 'What is the difference between abstract classes and interfaces in PHP 8.4?',
    ],
    [
        'name' => 'General Request',
        'content' => 'Please help me understand dependency injection patterns.',
    ],
];

echo "\n" . str_repeat("=", 80) . "\n";
echo "TESTING ROUTER WITH DIFFERENT INPUT TYPES\n";
echo str_repeat("=", 80) . "\n";

foreach ($testCases as $index => $testCase) {
    echo "\n" . str_repeat("-", 80) . "\n";
    echo "TEST CASE " . ($index + 1) . ": {$testCase['name']}\n";
    echo str_repeat("-", 80) . "\n";
    
    $content = $testCase['content'];
    echo "Input: " . substr($content, 0, 100);
    if (strlen($content) > 100) {
        echo "...";
    }
    echo "\n\n";
    
    echo "[ROUTER] Evaluating conditions...\n";
    
    $startTime = microtime(true);
    
    try {
        $result = $router->invoke(['content' => $content]);
        
        $duration = microtime(true) - $startTime;
        
        $metadata = $result->getMetadata() ?? [];
        $routeTaken = $metadata['route'] ?? 'unknown';
        $routeType = $metadata['type'] ?? 'unknown';
        
        echo "[ROUTER] Route taken: {$routeTaken} (type: {$routeType})\n";
        echo "[ROUTER] Execution time: " . number_format($duration, 3) . "s\n\n";
        
        echo "Response:\n";
        echo str_repeat("-", 80) . "\n";
        $response = $result['result'] ?? 'No result';
        // Limit output length for readability
        if (strlen($response) > 400) {
            echo substr($response, 0, 400) . "...\n";
        } else {
            echo $response . "\n";
        }
        echo str_repeat("-", 80) . "\n";
        
    } catch (\Exception $e) {
        echo "[ERROR] Routing failed: {$e->getMessage()}\n";
    }
    
    // Small delay between tests
    if ($index < count($testCases) - 1) {
        sleep(1);
    }
}

// ============================================================================
// Advanced Routing: Multi-Criteria
// ============================================================================

echo "\n\n" . str_repeat("=", 80) . "\n";
echo "ADVANCED ROUTING: MULTI-CRITERIA CONDITIONS\n";
echo str_repeat("=", 80) . "\n";

// Create a more sophisticated router
$advancedRouter = RouterChain::create()
    // Complex condition: High-priority code review
    ->addRoute(
        function (array $input): bool {
            $hasCode = str_contains($input['content'] ?? '', '<?php');
            $isLong = strlen($input['content'] ?? '') > 500;
            $priority = ($input['priority'] ?? 'normal') === 'high';
            
            return $hasCode && ($isLong || $priority);
        },
        $codeReviewChain
    )
    // Role-based routing
    ->addRoute(
        function (array $input): bool {
            $role = $input['user_role'] ?? 'guest';
            $type = $input['type'] ?? 'unknown';
            
            return $role === 'admin' && $type === 'documentation';
        },
        $documentationChain
    )
    ->setDefault($generalChain);

echo "\nAdvanced Router Features:\n";
echo "- Multi-criteria conditions (content + length + priority)\n";
echo "- Role-based routing (user_role + type)\n";
echo "- Complex boolean logic\n";

$advancedTestCase = [
    'content' => '<?php class UserService { /* ... */ }',
    'priority' => 'high',
    'user_role' => 'developer',
];

echo "\nTest Input:\n";
echo json_encode($advancedTestCase, JSON_PRETTY_PRINT) . "\n\n";

$advancedResult = $advancedRouter->invoke($advancedTestCase);
$advancedMeta = $advancedResult->getMetadata() ?? [];

echo "Routed to: " . ($advancedMeta['route'] ?? 'unknown') . "\n";
echo "Reason: Multi-criteria match (code + high priority)\n";

// ============================================================================
// Key Takeaways
// ============================================================================

echo "\n" . str_repeat("=", 80) . "\n";
echo "KEY CONCEPTS DEMONSTRATED\n";
echo str_repeat("=", 80) . "\n";
echo "1. Conditional Routing: Dynamic chain selection based on input\n";
echo "2. Multiple Conditions: Evaluated in order, first match wins\n";
echo "3. Default Fallback: Handles inputs that don't match any route\n";
echo "4. Route Metadata: Track which route was taken for debugging\n";
echo "5. Complex Logic: Multi-criteria conditions for sophisticated routing\n";
echo "\n";

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                         Example Complete                                   ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n";
