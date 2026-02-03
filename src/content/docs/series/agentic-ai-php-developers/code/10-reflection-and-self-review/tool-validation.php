<?php

/**
 * Tool Validation with Reflection Example
 * 
 * Demonstrates using reflection loops to validate tool outputs for correctness
 * and completeness. The agent reflects on whether tool results are sufficient
 * and iteratively improves by calling tools again or synthesizing better answers.
 * 
 * From: Agentic AI for PHP Developers
 * Chapter 10: Reflection and Self-Review Loops
 * 
 * @link https://github.com/claude-php/claude-php-agent
 */

declare(strict_types=1);

// Adjust path to your vendor/autoload.php location
require __DIR__ . '/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\Loops\ReflectionLoop;
use ClaudeAgents\Tools\Tool;
use ClaudePhp\ClaudePhp;

// Initialize Claude client
$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    die("Error: ANTHROPIC_API_KEY environment variable not set\n");
}

$client = new ClaudePhp(apiKey: $apiKey);

echo "Tool Validation with Reflection\n";
echo str_repeat("=", 80) . "\n\n";

/**
 * Simulated Search Tool
 * 
 * This tool returns intentionally incomplete results to demonstrate how
 * reflection can identify gaps and prompt the agent to gather more information.
 */
$searchCallCount = 0;
$searchTool = Tool::create('search_documentation')
    ->description('Search PHP documentation for information about features and functions')
    ->parameter('query', 'string', 'Search query')
    ->required('query')
    ->handler(function (array $input) use (&$searchCallCount): string {
        $searchCallCount++;
        $query = strtolower($input['query']);
        
        // First call returns incomplete info
        if ($searchCallCount === 1) {
            return json_encode([
                'results' => [
                    'PHP 8.4 was released in November 2024.',
                    'PHP 8.4 includes property hooks as a major feature.',
                    'Property hooks allow get and set operations.',
                ],
                'total' => 3,
            ]);
        }
        
        // Subsequent calls return more complete information
        return json_encode([
            'results' => [
                'PHP 8.4 property hooks allow defining get/set behavior directly in properties.',
                'Property hooks eliminate the need for explicit getter/setter methods.',
                'Example: public string $name { get => $this->name; set => strtoupper($value); }',
                'Property hooks work with backed properties and virtual properties.',
                'Hooks can be asymmetric (only get or only set).',
                'PHP 8.4 also includes asymmetric visibility, array functions, and new DOM API.',
            ],
            'total' => 6,
        ]);
    });

/**
 * Database Query Tool (simulated)
 * 
 * Simulates querying a database with potential validation issues.
 */
$dbTool = Tool::create('query_database')
    ->description('Query the database for user statistics')
    ->parameter('table', 'string', 'Table name')
    ->parameter('condition', 'string', 'WHERE condition', required: false)
    ->required('table')
    ->handler(function (array $input): string {
        $table = $input['table'];
        $condition = $input['condition'] ?? '';
        
        // Simulate results
        $results = [
            'users' => [
                ['id' => 1, 'name' => 'Alice', 'status' => 'active'],
                ['id' => 2, 'name' => 'Bob', 'status' => 'active'],
                ['id' => 3, 'name' => 'Charlie', 'status' => 'inactive'],
            ],
            'orders' => [
                ['id' => 101, 'user_id' => 1, 'total' => 49.99],
                ['id' => 102, 'user_id' => 1, 'total' => 29.99],
                ['id' => 103, 'user_id' => 2, 'total' => 99.99],
            ],
        ];
        
        $data = $results[$table] ?? [];
        
        // Apply basic condition filtering
        if ($condition && str_contains($condition, 'active')) {
            $data = array_filter($data, fn($item) => ($item['status'] ?? null) === 'active');
        }
        
        return json_encode([
            'rows' => array_values($data),
            'count' => count($data),
        ]);
    });

/**
 * Example 1: Validating Search Results Completeness
 */
echo "Example 1: Validating Search Results\n";
echo str_repeat("-", 80) . "\n\n";

$searchValidationCriteria = <<<'CRITERIA'
Evaluate the answer based on:

1. Completeness (40%):
   - Are all major features covered?
   - Is the information comprehensive?
   - Are there obvious gaps?

2. Accuracy (30%):
   - Are facts correct?
   - Are code examples valid?
   - Is version information accurate?

3. Clarity (30%):
   - Is the explanation clear?
   - Are examples helpful?
   - Is it easy to understand?

If information is incomplete or unclear, the agent should search again for more details.
Provide a score (1-10) and specify what's missing.
CRITERIA;

$searchLoop = new ReflectionLoop(
    maxRefinements: 3,
    qualityThreshold: 8,
    criteria: $searchValidationCriteria
);

$searchLoop->onReflection(function (int $ref, int $score, string $feedback): void {
    echo "📊 Validation Pass {$ref}: Quality {$score}/10\n";
    if ($score < 8) {
        echo "   Issues: " . substr($feedback, 0, 150) . "...\n\n";
    }
});

$searchAgent = Agent::create($client)
    ->withLoopStrategy($searchLoop)
    ->withTool($searchTool)
    ->withSystemPrompt('You are a helpful assistant. Use the search tool to find complete, accurate information.')
    ->maxIterations(20);

$searchTask = 'What are the major new features in PHP 8.4? Provide comprehensive details.';

echo "Task: {$searchTask}\n\n";
echo "Processing (watch for tool validation through reflection)...\n";
echo str_repeat("-", 80) . "\n\n";

$searchResult = $searchAgent->run($searchTask);

echo "\nFinal Answer (validated through reflection):\n";
echo str_repeat("=", 80) . "\n";
echo $searchResult->getAnswer() . "\n";
echo str_repeat("=", 80) . "\n";

$searchMetadata = $searchResult->getMetadata();
echo "\nValidation Metrics:\n";
echo "  - Final Quality Score: {$searchMetadata['final_score']}/10\n";
echo "  - Refinements Performed: " . count($searchMetadata['reflections']) . "\n";
echo "  - Search Tool Calls: {$searchCallCount}\n";
echo "  - Total Iterations: {$searchResult->getIterations()}\n";

/**
 * Example 2: Validating Data Queries
 */
echo "\n\nExample 2: Validating Database Query Results\n";
echo str_repeat("-", 80) . "\n\n";

$dbValidationCriteria = <<<'CRITERIA'
Evaluate the database query and analysis:

1. Query Correctness (35%):
   - Was the right table queried?
   - Were appropriate conditions used?
   - Is the data relevant to the question?

2. Data Completeness (35%):
   - Is all necessary data retrieved?
   - Are relationships considered?
   - Is context missing?

3. Analysis Quality (30%):
   - Are insights meaningful?
   - Are calculations correct?
   - Is the answer actionable?

If data is insufficient or analysis is incomplete, query again with better parameters.
CRITERIA;

$dbLoop = new ReflectionLoop(
    maxRefinements: 2,
    qualityThreshold: 8,
    criteria: $dbValidationCriteria
);

$dbLoop->onReflection(function (int $ref, int $score, string $feedback): void {
    echo "📊 Query Validation {$ref}: {$score}/10\n";
    if ($score < 8) {
        echo "   Improvements needed: " . substr($feedback, 0, 150) . "...\n\n";
    }
});

$dbAgent = Agent::create($client)
    ->withLoopStrategy($dbLoop)
    ->withTool($dbTool)
    ->withSystemPrompt('You are a data analyst. Query the database and provide comprehensive analysis.')
    ->maxIterations(15);

$dbTask = 'Analyze user activity: How many active users are there, and what is their total order value?';

echo "Task: {$dbTask}\n\n";
echo "Processing...\n";
echo str_repeat("-", 80) . "\n\n";

$dbResult = $dbAgent->run($dbTask);

echo "\nFinal Analysis:\n";
echo str_repeat("=", 80) . "\n";
echo $dbResult->getAnswer() . "\n";
echo str_repeat("=", 80) . "\n";

$dbMetadata = $dbResult->getMetadata();
echo "\nValidation Metrics:\n";
echo "  - Final Quality Score: {$dbMetadata['final_score']}/10\n";
echo "  - Refinements: " . count($dbMetadata['reflections']) . "\n";
echo "  - Total Iterations: {$dbResult->getIterations()}\n";

echo "\n✅ Tool validation examples complete!\n";
echo "\n💡 Key Takeaway: Reflection helps catch incomplete tool results.\n";
echo "   The agent can re-query tools or synthesize better answers\n";
echo "   when reflection identifies gaps in information quality.\n";
