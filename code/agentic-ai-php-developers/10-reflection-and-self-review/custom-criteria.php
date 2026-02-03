<?php

/**
 * Custom Evaluation Criteria Example
 * 
 * Demonstrates how to define task-specific evaluation criteria for reflection loops.
 * Shows different criteria for code generation, content writing, and data analysis.
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
use ClaudePhp\ClaudePhp;

// Initialize Claude client
$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    die("Error: ANTHROPIC_API_KEY environment variable not set\n");
}

$client = new ClaudePhp(apiKey: $apiKey);

echo "Custom Evaluation Criteria Example\n";
echo str_repeat("=", 80) . "\n\n";

/**
 * Example 1: Code Generation with Structured Criteria
 */
echo "Example 1: Code Generation with Quality Criteria\n";
echo str_repeat("-", 80) . "\n";

$codeGenerationCriteria = <<<'CRITERIA'
Evaluate this code on the following dimensions:

1. Correctness (30%):
   - Does the code work as specified?
   - Are there any bugs or logic errors?
   - Does it handle edge cases?

2. Type Safety (20%):
   - Proper type hints for parameters and return types?
   - No mixed types or loose comparisons?
   - Follows PHP 8.4 type system best practices?

3. Error Handling (20%):
   - Appropriate exception handling?
   - Validates input parameters?
   - Provides meaningful error messages?

4. Code Quality (20%):
   - Follows PSR-12 coding standards?
   - Clear variable and function names?
   - Appropriate comments for complex logic?

5. Security (10%):
   - No SQL injection vulnerabilities?
   - Proper input sanitization?
   - Secure handling of sensitive data?

Provide specific feedback for each dimension and an overall score (1-10).
CRITERIA;

$codeLoop = new ReflectionLoop(
    maxRefinements: 3,
    qualityThreshold: 8,
    criteria: $codeGenerationCriteria
);

$codeLoop->onReflection(function (int $ref, int $score, string $feedback): void {
    echo "Code refinement {$ref}: {$score}/10\n";
});

$codeAgent = Agent::create($client)
    ->withLoopStrategy($codeLoop)
    ->withSystemPrompt('You are an expert PHP developer. Write clean, secure, well-documented code following PHP 8.4 best practices.')
    ->maxIterations(15);

$codeTask = 'Create a PHP function that validates and sanitizes user email addresses. ' .
            'The function should check for valid format, remove dangerous characters, ' .
            'and return either the sanitized email or null if invalid.';

echo "\nTask: {$codeTask}\n\n";

$result1 = $codeAgent->run($codeTask);

echo "\nGenerated Code:\n";
echo str_repeat("-", 80) . "\n";
echo $result1->getAnswer() . "\n";
echo str_repeat("-", 80) . "\n";
echo "Final Quality Score: {$result1->getMetadata()['final_score']}/10\n\n";

/**
 * Example 2: Content Writing Criteria
 */
echo "\nExample 2: Content Writing with Engagement Criteria\n";
echo str_repeat("-", 80) . "\n";

$contentWritingCriteria = <<<'CRITERIA'
Evaluate this content on:

1. Clarity (25%):
   - Is the message clear and easy to understand?
   - Are sentences concise?
   - Is jargon explained?

2. Engagement (25%):
   - Is the content interesting to read?
   - Does it hook the reader?
   - Are examples compelling?

3. Accuracy (20%):
   - Are facts correct?
   - Are technical details accurate?
   - Are sources credible?

4. Tone (15%):
   - Is the tone appropriate for the audience?
   - Is it professional yet approachable?
   - Does it match brand voice?

5. Structure (15%):
   - Logical flow of ideas?
   - Good use of headings and paragraphs?
   - Strong opening and closing?

Provide an overall score (1-10) and specific improvement suggestions.
CRITERIA;

$contentLoop = new ReflectionLoop(
    maxRefinements: 3,
    qualityThreshold: 8,
    criteria: $contentWritingCriteria
);

$contentLoop->onReflection(function (int $ref, int $score, string $feedback): void {
    echo "Content refinement {$ref}: {$score}/10\n";
});

$contentAgent = Agent::create($client)
    ->withLoopStrategy($contentLoop)
    ->withSystemPrompt('You are a skilled technical content writer. Create engaging, accurate content for developer audiences.')
    ->maxIterations(15);

$contentTask = 'Write a 150-word introduction for a blog post about the benefits of using ' .
               'type declarations in PHP 8.4. Target audience: intermediate PHP developers.';

echo "\nTask: {$contentTask}\n\n";

$result2 = $contentAgent->run($contentTask);

echo "\nGenerated Content:\n";
echo str_repeat("-", 80) . "\n";
echo $result2->getAnswer() . "\n";
echo str_repeat("-", 80) . "\n";
echo "Final Quality Score: {$result2->getMetadata()['final_score']}/10\n\n";

/**
 * Example 3: Simple Task-Specific Criteria
 */
echo "\nExample 3: Documentation with Completeness Focus\n";
echo str_repeat("-", 80) . "\n";

$docsLoop = new ReflectionLoop(
    maxRefinements: 2,
    qualityThreshold: 8,
    criteria: 'completeness, accuracy, clarity for beginners, and inclusion of practical code examples'
);

$docsLoop->onReflection(function (int $ref, int $score, string $feedback): void {
    echo "Documentation refinement {$ref}: {$score}/10\n";
});

$docsAgent = Agent::create($client)
    ->withLoopStrategy($docsLoop)
    ->withSystemPrompt('You are a documentation writer. Create clear, complete docs with examples.')
    ->maxIterations(15);

$docsTask = 'Document how to use PHP\'s array_map function with examples showing ' .
            'different use cases: transforming strings, working with objects, and using arrow functions.';

echo "\nTask: {$docsTask}\n\n";

$result3 = $docsAgent->run($docsTask);

echo "\nGenerated Documentation:\n";
echo str_repeat("-", 80) . "\n";
echo $result3->getAnswer() . "\n";
echo str_repeat("-", 80) . "\n";
echo "Final Quality Score: {$result3->getMetadata()['final_score']}/10\n";

echo "\n✅ All examples complete!\n";
echo "\n💡 Key Takeaway: Tailor evaluation criteria to your specific task requirements.\n";
echo "   Different tasks (code, content, docs) need different quality measures.\n";
