#!/usr/bin/env php
<?php
/**
 * Content Creation Pipeline
 * 
 * A production-ready content creation system using hierarchical agents.
 * Research, SEO optimization, writing, and editing specialists work
 * together to create high-quality blog posts.
 * 
 * This example shows:
 * - Research and fact-gathering
 * - SEO keyword optimization
 * - Engaging content writing
 * - Professional editing
 * - End-to-end content workflow
 */

require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agents\HierarchicalAgent;
use ClaudeAgents\Agents\WorkerAgent;
use ClaudePhp\ClaudePhp;

$apiKey = getenv('ANTHROPIC_API_KEY');
if (!$apiKey) {
    echo "❌ Error: ANTHROPIC_API_KEY environment variable not set\n";
    exit(1);
}

$client = new ClaudePhp(apiKey: $apiKey);

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                   Content Creation Pipeline                                ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Build Editorial Team
// ============================================================================

echo "Building editorial team...\n\n";

// Research Specialist
$researcher = new WorkerAgent($client, [
    'name' => 'researcher',
    'specialty' => 'topic research, fact-checking, data gathering, and source verification',
    'system' => 'You are a research specialist. Your responsibilities:\n' .
                '- Research the topic thoroughly\n' .
                '- Find key facts, statistics, and data points\n' .
                '- Identify expert opinions and recent developments\n' .
                '- Verify information accuracy\n' .
                '- Note relevant examples and case studies\n\n' .
                'Provide a research brief with:\n' .
                '1. Key facts and statistics\n' .
                '2. Important concepts to cover\n' .
                '3. Recent trends and developments\n' .
                '4. Potential examples or case studies',
    'max_tokens' => 2000,
]);

echo "  ✓ Research Specialist ready\n";

// SEO Specialist
$seoExpert = new WorkerAgent($client, [
    'name' => 'seo_expert',
    'specialty' => 'SEO optimization, keyword research, meta tags, search rankings, and content structure',
    'system' => 'You are an SEO expert. Your responsibilities:\n' .
                '- Identify target keywords and search intent\n' .
                '- Suggest optimized meta title and description\n' .
                '- Recommend heading structure (H1, H2, H3)\n' .
                '- Identify internal linking opportunities\n' .
                '- Suggest content length and structure for rankings\n\n' .
                'Provide SEO recommendations including:\n' .
                '1. Primary and secondary keywords\n' .
                '2. Meta title (50-60 characters)\n' .
                '3. Meta description (150-160 characters)\n' .
                '4. Recommended content structure',
    'max_tokens' => 1500,
]);

echo "  ✓ SEO Expert ready\n";

// Content Writer
$writer = new WorkerAgent($client, [
    'name' => 'content_writer',
    'specialty' => 'engaging writing, storytelling, audience connection, clear structure, and readability',
    'system' => 'You are a professional content writer. Your responsibilities:\n' .
                '- Write compelling, engaging content\n' .
                '- Use storytelling and emotional connection\n' .
                '- Create clear structure with logical flow\n' .
                '- Write for the target audience\n' .
                '- Include concrete examples and actionable advice\n' .
                '- Use conversational but professional tone\n\n' .
                'Write content that:\n' .
                '1. Hooks readers in the introduction\n' .
                '2. Delivers clear value in each section\n' .
                '3. Uses subheadings effectively\n' .
                '4. Ends with actionable takeaways',
    'max_tokens' => 3000,
]);

echo "  ✓ Content Writer ready\n";

// Editor
$editor = new WorkerAgent($client, [
    'name' => 'editor',
    'specialty' => 'editing, proofreading, grammar, style, clarity, and flow',
    'system' => 'You are a professional editor. Your responsibilities:\n' .
                '- Edit for clarity and flow\n' .
                '- Fix grammar, spelling, and punctuation\n' .
                '- Ensure consistent tone and voice\n' .
                '- Improve readability\n' .
                '- Cut unnecessary words (be concise)\n' .
                '- Verify logical structure\n\n' .
                'Provide edited version that:\n' .
                '1. Maintains the writer\'s voice\n' .
                '2. Improves clarity and readability\n' .
                '3. Fixes all errors\n' .
                '4. Enhances overall quality',
    'max_tokens' => 3000,
]);

echo "  ✓ Editor ready\n\n";

// Create master coordinator
$contentPipeline = new HierarchicalAgent($client, [
    'name' => 'content_pipeline',
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 2048,
]);

$contentPipeline->registerWorker('researcher', $researcher);
$contentPipeline->registerWorker('seo_expert', $seoExpert);
$contentPipeline->registerWorker('content_writer', $writer);
$contentPipeline->registerWorker('editor', $editor);

echo "Editorial team assembled with 4 specialists\n\n";

// ============================================================================
// Content Brief
// ============================================================================

$topic = "Building High-Performance APIs with PHP 8.4";
$audience = "intermediate PHP developers and backend engineers";
$targetLength = "1200-1500 words";
$tone = "technical but accessible, conversational";

echo "Content Brief:\n";
echo str_repeat("-", 80) . "\n";
echo "  • Topic: {$topic}\n";
echo "  • Target Audience: {$audience}\n";
echo "  • Length: {$targetLength}\n";
echo "  • Tone: {$tone}\n";
echo str_repeat("-", 80) . "\n\n";

// ============================================================================
// Execute Content Creation
// ============================================================================

echo "Starting content creation pipeline...\n";
echo "This will take ~25-35 seconds as specialists collaborate\n\n";

$task = <<<TASK
Create a comprehensive blog post about "{$topic}".

Target Audience: {$audience}
Target Length: {$targetLength}
Tone: {$tone}

Requirements:
1. Research the topic thoroughly - cover key concepts, best practices, and recent PHP 8.4 features
2. Optimize for SEO - include relevant keywords, meta tags, and structure
3. Write engaging, practical content with code examples
4. Edit for clarity, grammar, and flow

The final post should be publication-ready.
TASK;

$startTime = microtime(true);

$result = $contentPipeline->run($task);

$duration = microtime(true) - $startTime;

// ============================================================================
// Display Results
// ============================================================================

if ($result->isSuccess()) {
    echo "✅ Content Creation Complete!\n\n";
    
    $blogPost = $result->getAnswer();
    
    echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
    echo "║                           BLOG POST                                        ║\n";
    echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";
    
    echo $blogPost . "\n\n";
    
    // Save to file
    $slug = strtolower(str_replace([' ', '.'], ['-', ''], $topic));
    $filename = date('Y-m-d') . "-{$slug}.md";
    $filepath = __DIR__ . "/{$filename}";
    
    file_put_contents($filepath, $blogPost);
    
    echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
    echo "║                        PRODUCTION DETAILS                                  ║\n";
    echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";
    
    echo "📄 Content Details:\n";
    echo "  • Word count: ~" . str_word_count($blogPost) . " words\n";
    echo "  • Character count: " . strlen($blogPost) . " characters\n";
    echo "  • Saved to: {$filename}\n\n";
    
    $metadata = $result->getMetadata();
    
    echo "👥 Editorial Team:\n";
    echo "  • Specialists involved: " . implode(', ', $metadata['workers_used']) . "\n";
    echo "  • Workflow steps: {$metadata['subtasks']}\n";
    echo "  • Total iterations: {$result->getIterations()}\n\n";
    
    echo "⏱️ Performance:\n";
    echo "  • Total duration: " . round($duration, 2) . " seconds\n";
    echo "  • Average per specialist: " . round($duration / count($metadata['workers_used']), 2) . "s\n\n";
    
    echo "💰 Cost Analysis:\n";
    $usage = $result->getTokenUsage();
    echo "  • Input tokens: " . number_format($usage['input']) . "\n";
    echo "  • Output tokens: " . number_format($usage['output']) . "\n";
    echo "  • Total tokens: " . number_format($usage['total']) . "\n";
    
    $inputCost = $usage['input'] * 0.003 / 1000;
    $outputCost = $usage['output'] * 0.015 / 1000;
    $totalCost = $inputCost + $outputCost;
    
    echo "  • Total cost: $" . number_format($totalCost, 4) . "\n";
    echo "  • Cost per 1000 words: $" . number_format($totalCost / (str_word_count($blogPost) / 1000), 4) . "\n\n";
    
} else {
    echo "❌ Content creation failed: {$result->getError()}\n";
    exit(1);
}

// ============================================================================
// Pipeline Analysis
// ============================================================================

echo str_repeat("═", 80) . "\n";
echo "Content Pipeline Analysis\n";
echo str_repeat("═", 80) . "\n\n";

echo "✓ What Each Specialist Contributed:\n\n";

echo "  Researcher:\n";
echo "    • Found key facts about PHP 8.4 performance features\n";
echo "    • Identified best practices for API development\n";
echo "    • Gathered relevant examples and benchmarks\n\n";

echo "  SEO Expert:\n";
echo "    • Selected target keywords for search optimization\n";
echo "    • Structured content for better rankings\n";
echo "    • Suggested meta tags and descriptions\n\n";

echo "  Content Writer:\n";
echo "    • Crafted engaging introduction and narrative\n";
echo "    • Wrote clear explanations with code examples\n";
echo "    • Created logical structure and flow\n\n";

echo "  Editor:\n";
echo "    • Improved clarity and readability\n";
echo "    • Fixed grammar and style issues\n";
echo "    • Ensured consistent tone\n";
echo "    • Polished final version\n\n";

echo "✓ Pipeline Benefits:\n";
echo "  • Quality: Multiple specialists ensure excellence\n";
echo "  • Consistency: Every post follows same workflow\n";
echo "  • SEO: Built-in optimization for search rankings\n";
echo "  • Scalability: Can produce multiple posts in parallel\n\n";

echo "✓ Production Recommendations:\n";
echo "  1. Create content calendar with topics\n";
echo "  2. Batch similar topics for efficiency\n";
echo "  3. Cache research for related topics\n";
echo "  4. Track performance metrics (traffic, engagement)\n";
echo "  5. Refine system prompts based on results\n\n";

echo "✓ Cost Optimization:\n";
echo "  • Current cost: ~$" . number_format($totalCost, 4) . " per post\n";
echo "  • Use Haiku for simpler edits (save 50-70%)\n";
echo "  • Cache research for topic clusters\n";
echo "  • Reuse SEO analysis for similar keywords\n\n";

echo "Example completed successfully!\n";
echo "Check {$filename} for the full blog post.\n";
