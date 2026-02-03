<?php

/**
 * Content Refinement with Reflection
 * 
 * Demonstrates using reflection loops for high-quality content generation.
 * The agent creates content, evaluates it against quality criteria, and
 * iteratively refines it for clarity, engagement, and accuracy.
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

echo "Content Refinement with Reflection\n";
echo str_repeat("=", 80) . "\n\n";

/**
 * Example 1: Blog Post Introduction
 */
echo "Example 1: Blog Post Introduction\n";
echo str_repeat("-", 80) . "\n\n";

$blogCriteria = <<<'CRITERIA'
Evaluate this blog post introduction on:

1. Hook Effectiveness (25%):
   - Does it grab attention immediately?
   - Is the opening sentence compelling?
   - Does it make the reader want to continue?

2. Clarity (25%):
   - Is the main point clear?
   - Is the writing concise?
   - Are concepts explained simply?

3. Engagement (20%):
   - Is it interesting to read?
   - Does it use concrete examples?
   - Is the tone conversational yet professional?

4. Value Proposition (15%):
   - Does it explain why this matters?
   - Is the benefit to the reader clear?
   - Does it promise actionable insights?

5. Technical Accuracy (15%):
   - Are technical details correct?
   - Are code concepts accurate?
   - Is terminology used properly?

Score 1-10 and provide specific improvements.
CRITERIA;

$blogLoop = new ReflectionLoop(
    maxRefinements: 3,
    qualityThreshold: 8,
    criteria: $blogCriteria
);

$blogLoop->onReflection(function (int $ref, int $score, string $feedback): void {
    echo "✍️  Refinement {$ref}: Quality {$score}/10\n";
    if ($score < 8) {
        echo "   Needs improvement in:\n";
        // Extract first few points from feedback
        $lines = array_filter(explode("\n", $feedback));
        foreach (array_slice($lines, 0, 3) as $line) {
            if (trim($line)) {
                echo "   - " . trim($line) . "\n";
            }
        }
        echo "\n";
    }
});

$blogAgent = Agent::create($client)
    ->withLoopStrategy($blogLoop)
    ->withSystemPrompt(
        'You are an expert technical content writer. Create engaging, accurate content ' .
        'that hooks readers while teaching complex concepts clearly. Write for intermediate ' .
        'PHP developers who want practical, actionable insights.'
    )
    ->maxIterations(15);

$blogTask = 'Write a compelling 200-word introduction for a blog post titled ' .
            '"Why PHP 8.4 Property Hooks Will Change How You Write Classes". ' .
            'Target audience: intermediate PHP developers. Hook them with the problem, ' .
            'tease the solution, and make them excited to learn more.';

echo "Task: Create blog post introduction\n\n";
echo "Generating and refining content...\n";
echo str_repeat("-", 80) . "\n\n";

$blogResult = $blogAgent->run($blogTask);

echo "\n" . str_repeat("=", 80) . "\n";
echo "REFINED BLOG INTRODUCTION\n";
echo str_repeat("=", 80) . "\n";
echo $blogResult->getAnswer() . "\n";
echo str_repeat("=", 80) . "\n";

$blogMetadata = $blogResult->getMetadata();
echo "\nContent Quality:\n";
echo "  - Final Score: {$blogMetadata['final_score']}/10\n";
echo "  - Refinements: " . count($blogMetadata['reflections']) . "\n";
echo "  - Word Count: ~" . str_word_count($blogResult->getAnswer()) . " words\n";

/**
 * Example 2: Technical Documentation
 */
echo "\n\n" . str_repeat("=", 80) . "\n";
echo "Example 2: Technical Documentation\n";
echo str_repeat("=", 80) . "\n\n";

$docsCriteria = <<<'CRITERIA'
Evaluate this documentation on:

1. Completeness (30%):
   - Are all key concepts covered?
   - Are edge cases mentioned?
   - Is nothing left ambiguous?

2. Clarity for Beginners (25%):
   - Can a junior developer understand it?
   - Is jargon explained?
   - Are steps logical and easy to follow?

3. Code Examples (25%):
   - Are examples practical and realistic?
   - Do they demonstrate key concepts?
   - Are they complete and runnable?

4. Accuracy (20%):
   - Is information factually correct?
   - Are code examples valid PHP 8.4?
   - Are best practices followed?

Provide score (1-10) and specific improvements needed.
CRITERIA;

$docsLoop = new ReflectionLoop(
    maxRefinements: 3,
    qualityThreshold: 8,
    criteria: $docsCriteria
);

$docsLoop->onReflection(function (int $ref, int $score, string $feedback): void {
    echo "📚 Documentation refinement {$ref}: {$score}/10\n";
});

$docsAgent = Agent::create($client)
    ->withLoopStrategy($docsLoop)
    ->withSystemPrompt(
        'You are a documentation specialist. Write clear, complete docs with practical ' .
        'examples. Explain concepts simply while being technically accurate. Always include ' .
        'runnable code examples that demonstrate real-world usage.'
    )
    ->maxIterations(15);

$docsTask = 'Write documentation explaining how to use try-catch-finally in PHP 8.4. ' .
            'Include: when to use it, how the flow works, code examples showing different ' .
            'scenarios (with/without exceptions, with return statements), and best practices. ' .
            'Target: junior developers learning error handling.';

echo "Task: Create technical documentation\n\n";
echo "Processing...\n\n";

$docsResult = $docsAgent->run($docsTask);

echo "\n" . str_repeat("=", 80) . "\n";
echo "REFINED DOCUMENTATION\n";
echo str_repeat("=", 80) . "\n";
echo $docsResult->getAnswer() . "\n";
echo str_repeat("=", 80) . "\n";

$docsMetadata = $docsResult->getMetadata();
echo "\nDocumentation Quality:\n";
echo "  - Final Score: {$docsMetadata['final_score']}/10\n";
echo "  - Refinements: " . count($docsMetadata['reflections']) . "\n";

/**
 * Example 3: Email Communication
 */
echo "\n\n" . str_repeat("=", 80) . "\n";
echo "Example 3: Professional Email\n";
echo str_repeat("=", 80) . "\n\n";

$emailCriteria = <<<'CRITERIA'
Evaluate this professional email on:

1. Tone (30%):
   - Is it professional yet friendly?
   - Is it appropriate for the situation?
   - Does it show respect and courtesy?

2. Clarity (30%):
   - Is the purpose immediately clear?
   - Are requests specific and actionable?
   - Is the structure logical?

3. Completeness (20%):
   - Is all necessary context provided?
   - Are next steps clear?
   - Are timelines mentioned if relevant?

4. Brevity (20%):
   - Is it concise without being terse?
   - Does every sentence add value?
   - Could anything be said more briefly?

Score 1-10 and suggest improvements.
CRITERIA;

$emailLoop = new ReflectionLoop(
    maxRefinements: 2,
    qualityThreshold: 8,
    criteria: $emailCriteria
);

$emailLoop->onReflection(function (int $ref, int $score, string $feedback): void {
    echo "📧 Email refinement {$ref}: {$score}/10\n";
});

$emailAgent = Agent::create($client)
    ->withLoopStrategy($emailLoop)
    ->withSystemPrompt(
        'You are a professional communication specialist. Write clear, concise emails ' .
        'that are friendly yet professional. Get to the point quickly while maintaining warmth.'
    )
    ->maxIterations(15);

$emailTask = 'Write a professional email to a client explaining that a feature they requested ' .
             'will take longer than initially estimated (2 weeks instead of 1 week) due to ' .
             'unexpected technical complexity. Maintain the relationship, explain the reason ' .
             'clearly, and offer a solution (phased delivery). Keep it under 150 words.';

echo "Task: Create professional email\n\n";
echo "Processing...\n\n";

$emailResult = $emailAgent->run($emailTask);

echo "\n" . str_repeat("=", 80) . "\n";
echo "REFINED EMAIL\n";
echo str_repeat("=", 80) . "\n";
echo $emailResult->getAnswer() . "\n";
echo str_repeat("=", 80) . "\n";

$emailMetadata = $emailResult->getMetadata();
echo "\nEmail Quality:\n";
echo "  - Final Score: {$emailMetadata['final_score']}/10\n";
echo "  - Refinements: " . count($emailMetadata['reflections']) . "\n";
echo "  - Word Count: " . str_word_count($emailResult->getAnswer()) . " words\n";

/**
 * Summary
 */
echo "\n\n" . str_repeat("=", 80) . "\n";
echo "CONTENT REFINEMENT INSIGHTS\n";
echo str_repeat("=", 80) . "\n\n";

echo "How Reflection Improves Content:\n\n";

echo "1. Blog Posts:\n";
echo "   - Initial draft might be informative but flat\n";
echo "   - Reflection identifies weak hook or unclear value\n";
echo "   - Final version has compelling opening and clear benefits\n\n";

echo "2. Documentation:\n";
echo "   - First version might have correct but incomplete examples\n";
echo "   - Reflection catches missing edge cases or unclear explanations\n";
echo "   - Final docs are comprehensive and beginner-friendly\n\n";

echo "3. Professional Communication:\n";
echo "   - Initial email might be too formal or too casual\n";
echo "   - Reflection adjusts tone and clarity\n";
echo "   - Final email is professional, warm, and actionable\n\n";

echo "Quality Progression:\n";
echo "  Round 1: ~6/10 - Good start but needs work\n";
echo "  Round 2: ~7/10 - Improved clarity and structure\n";
echo "  Round 3: ~8/10 - Polished, publication-ready\n\n";

echo "✅ Content refinement examples complete!\n";
echo "\n💡 Key Takeaway: Reflection elevates content from 'good enough'\n";
echo "   to 'publication-ready' by iteratively addressing weaknesses\n";
echo "   in engagement, clarity, completeness, and tone.\n";
