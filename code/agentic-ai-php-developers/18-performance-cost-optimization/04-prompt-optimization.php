<?php

declare(strict_types=1);

/**
 * Example 04: Prompt Optimization
 *
 * Demonstrates how optimizing prompts can reduce token usage and costs
 * while maintaining or improving output quality.
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudePhp\ClaudePhp;

// Initialize client
$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

/**
 * Prompt optimizer that reduces token usage
 */
class PromptOptimizer
{
    /**
     * Optimize a verbose prompt
     */
    public function optimize(string $prompt): array
    {
        $original = $prompt;
        $optimized = $prompt;
        
        // Remove redundant phrases
        $redundancies = [
            '/\bplease\b/i' => '',
            '/\bI would like you to\b/i' => '',
            '/\bCould you\b/i' => '',
            '/\bI need you to\b/i' => '',
            '/\s+/' => ' ', // Multiple spaces
        ];
        
        foreach ($redundancies as $pattern => $replacement) {
            $optimized = preg_replace($pattern, $replacement, $optimized);
        }
        
        // Make instructions more concise
        $optimized = str_replace(
            'provide a detailed explanation',
            'explain',
            $optimized
        );
        
        $optimized = str_replace(
            'give me information about',
            'describe',
            $optimized
        );
        
        $optimized = trim($optimized);
        
        return [
            'original' => $original,
            'optimized' => $optimized,
            'original_tokens' => $this->estimateTokens($original),
            'optimized_tokens' => $this->estimateTokens($optimized),
            'reduction' => $this->estimateTokens($original) - $this->estimateTokens($optimized),
        ];
    }
    
    /**
     * Rough token estimation (1 token ≈ 4 characters for English)
     */
    private function estimateTokens(string $text): int
    {
        return (int) ceil(strlen($text) / 4);
    }
}

/**
 * Compare agent performance with different prompt styles
 */
function comparePromptEfficiency(ClaudePhp $client, array $prompts, string $userQuery): void
{
    echo "\n=== Testing Query: {$userQuery} ===\n";
    
    foreach ($prompts as $label => $systemPrompt) {
        echo "\n--- {$label} ---\n";
        echo "System prompt length: " . strlen($systemPrompt) . " chars\n";
        
        $agent = Agent::create($client)
            ->withSystemPrompt($systemPrompt)
            ->withModel('claude-3-5-haiku-20241022')
            ->maxIterations(2);
        
        $start = microtime(true);
        $result = $agent->run($userQuery);
        $duration = microtime(true) - $start;
        
        $usage = $result->getTokenUsage();
        $totalTokens = $usage['input'] + $usage['output'];
        
        echo "Answer: " . substr($result->getAnswer(), 0, 100) . "...\n";
        echo "Input tokens: {$usage['input']}\n";
        echo "Output tokens: {$usage['output']}\n";
        echo "Total tokens: {$totalTokens}\n";
        echo "Duration: " . number_format($duration * 1000, 0) . "ms\n";
        
        // Cost calculation (Haiku pricing: $0.80/$4.00 per million)
        $cost = (($usage['input'] * 0.80) + ($usage['output'] * 4.00)) / 1_000_000;
        echo "Cost: $" . number_format($cost, 6) . "\n";
    }
}

echo "=== Prompt Optimization Demo ===\n\n";

// Example 1: System prompt optimization
echo "Example 1: System Prompt Optimization\n";
echo "=" . str_repeat("=", 50) . "\n";

$verbosePrompt = <<<PROMPT
Hello! I would like you to act as a helpful technical documentation assistant.
Please provide detailed explanations when users ask questions about programming
concepts. I need you to be clear, concise, and accurate in your responses.
Could you please make sure to include relevant examples when appropriate and
avoid using overly technical jargon unless it's necessary for the explanation.
Thank you for your assistance!
PROMPT;

$concisePrompt = <<<PROMPT
You are a technical documentation assistant. Provide clear, accurate explanations
with examples when needed. Use simple language unless technical terms are required.
PROMPT;

$prompts = [
    'Verbose (Original)' => $verbosePrompt,
    'Concise (Optimized)' => $concisePrompt,
];

comparePromptEfficiency(
    $client,
    $prompts,
    'What is dependency injection in PHP?'
);

// Example 2: User query optimization
echo "\n\n";
echo "Example 2: User Query Optimization\n";
echo "=" . str_repeat("=", 50) . "\n";

$optimizer = new PromptOptimizer();

$verboseQueries = [
    "Please could you help me understand what the difference is between abstract classes and interfaces in PHP? I would really appreciate if you could provide a detailed explanation with some examples.",
    "I need you to explain to me how I can implement caching in my PHP application. Could you please give me information about the best practices?",
    "I would like you to help me understand what are the benefits of using composer for dependency management in PHP projects.",
];

$totalOriginal = 0;
$totalOptimized = 0;

foreach ($verboseQueries as $i => $query) {
    echo "\n--- Query " . ($i + 1) . " ---\n";
    $result = $optimizer->optimize($query);
    
    echo "Original: {$result['original']}\n";
    echo "Optimized: {$result['optimized']}\n";
    echo "Token reduction: {$result['reduction']} tokens (" . 
         number_format(($result['reduction'] / $result['original_tokens']) * 100, 1) . "%)\n";
    
    $totalOriginal += $result['original_tokens'];
    $totalOptimized += $result['optimized_tokens'];
}

echo "\n=== Total Savings ===\n";
echo "Original: {$totalOriginal} estimated tokens\n";
echo "Optimized: {$totalOptimized} estimated tokens\n";
$saved = $totalOriginal - $totalOptimized;
echo "Saved: {$saved} tokens (" . 
     number_format(($saved / $totalOriginal) * 100, 1) . "%)\n";

// Example 3: Structured output reduces tokens
echo "\n\n";
echo "Example 3: Structured Output Format\n";
echo "=" . str_repeat("=", 50) . "\n";

$structuredPrompts = [
    'Verbose Output' => 'You are a helpful assistant. Provide comprehensive answers with full sentences and detailed explanations.',
    'Structured Output' => 'You are a technical assistant. Format responses as: ANSWER: [brief answer], DETAILS: [key points as bullets], EXAMPLE: [code if relevant].',
];

comparePromptEfficiency(
    $client,
    $structuredPrompts,
    'How do I connect to MySQL in PHP?'
);

echo "\n\n";
echo "=== Prompt Optimization Best Practices ===\n\n";
echo "✅ Remove politeness fluff ('please', 'could you', 'I would like')\n";
echo "✅ Use concise instructions\n";
echo "✅ Request structured output formats\n";
echo "✅ Specify output length constraints when appropriate\n";
echo "✅ Avoid redundant context in repeated queries\n";
echo "✅ Use system prompts for general instructions, keep user queries focused\n";
echo "\n💡 Optimization Impact:\n";
echo "   - 10-30% token reduction per request\n";
echo "   - Faster response times\n";
echo "   - Lower costs at scale\n";
echo "   - Often clearer, more actionable responses\n";
