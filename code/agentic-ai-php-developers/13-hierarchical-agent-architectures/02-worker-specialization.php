#!/usr/bin/env php
<?php
/**
 * Worker Specialization
 * 
 * Demonstrates how to create highly specialized worker agents with
 * focused domains of expertise.
 * 
 * This example shows:
 * - Creating workers with specific specialties
 * - Comparing broad vs. focused specialties
 * - Building complementary teams
 * - Using different models for different workers
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
echo "║               Worker Specialization Patterns                               ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Example 1: Code Analysis Team - Highly Specialized Workers
// ============================================================================

echo "Example 1: Building a Code Analysis Team\n";
echo str_repeat("-", 80) . "\n\n";

// Security specialist - focused on vulnerabilities
$securityWorker = new WorkerAgent($client, [
    'name' => 'security_expert',
    'specialty' => 'security vulnerabilities, SQL injection, XSS, CSRF, and authentication flaws',
    'system' => 'You are a security expert. Review code for vulnerabilities like SQL injection, XSS, CSRF, authentication issues, and data exposure. Provide specific, actionable fixes.',
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 2000,
]);

echo "✓ Security Expert Created\n";
echo "  Domain: {$securityWorker->getSpecialty()}\n\n";

// Performance specialist - focused on optimization
$performanceWorker = new WorkerAgent($client, [
    'name' => 'performance_expert',
    'specialty' => 'performance optimization, algorithm efficiency, N+1 queries, and scalability',
    'system' => 'You are a performance expert. Identify bottlenecks, inefficient algorithms, N+1 query problems, memory leaks, and scalability issues. Suggest concrete optimizations.',
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 2000,
]);

echo "✓ Performance Expert Created\n";
echo "  Domain: {$performanceWorker->getSpecialty()}\n\n";

// Best practices specialist - focused on code quality
$practicesWorker = new WorkerAgent($client, [
    'name' => 'practices_expert',
    'specialty' => 'coding standards, SOLID principles, design patterns, and maintainability',
    'system' => 'You are a code quality expert. Review for clean code principles, SOLID principles, design patterns, PSR standards, naming conventions, and long-term maintainability.',
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 2000,
]);

echo "✓ Best Practices Expert Created\n";
echo "  Domain: {$practicesWorker->getSpecialty()}\n\n";

// Test coverage specialist - focused on testing
$testWorker = new WorkerAgent($client, [
    'name' => 'test_expert',
    'specialty' => 'unit testing, integration testing, PHPUnit, test coverage, and edge cases',
    'system' => 'You are a testing expert. Suggest test cases, identify untested code paths, recommend testing strategies, and evaluate test quality.',
    'model' => 'claude-haiku-3-5', // Use Haiku for cost savings on simpler tasks
    'max_tokens' => 1500,
]);

echo "✓ Test Expert Created\n";
echo "  Domain: {$testWorker->getSpecialty()}\n";
echo "  Model: claude-haiku-3-5 (cost optimization)\n\n";

// ============================================================================
// Create master and register team
// ============================================================================

$codeAnalyzer = new HierarchicalAgent($client, [
    'name' => 'code_analyzer',
]);

$codeAnalyzer->registerWorker('security_expert', $securityWorker);
$codeAnalyzer->registerWorker('performance_expert', $performanceWorker);
$codeAnalyzer->registerWorker('practices_expert', $practicesWorker);
$codeAnalyzer->registerWorker('test_expert', $testWorker);

echo "Team assembled with " . count($codeAnalyzer->getWorkerNames()) . " specialists\n\n";

// ============================================================================
// Test with sample code
// ============================================================================

$sampleCode = <<<'PHP'
<?php
class UserRepository
{
    private $conn;
    
    public function getUserById($id)
    {
        $query = "SELECT * FROM users WHERE id = " . $id;
        $result = mysqli_query($this->conn, $query);
        return mysqli_fetch_assoc($result);
    }
    
    public function getAllUsers()
    {
        $users = [];
        $query = "SELECT * FROM users";
        $result = mysqli_query($this->conn, $query);
        
        while ($row = mysqli_fetch_assoc($result)) {
            // Get each user's posts
            $postsQuery = "SELECT * FROM posts WHERE user_id = " . $row['id'];
            $postsResult = mysqli_query($this->conn, $postsQuery);
            $row['posts'] = [];
            
            while ($post = mysqli_fetch_assoc($postsResult)) {
                $row['posts'][] = $post;
            }
            
            $users[] = $row;
        }
        
        return $users;
    }
}
PHP;

echo "Analyzing sample code with multiple issues...\n";
echo str_repeat("-", 80) . "\n";
echo substr($sampleCode, 0, 300) . "...\n";
echo str_repeat("-", 80) . "\n\n";

echo "Running comprehensive analysis...\n\n";

$result = $codeAnalyzer->run(
    "Provide a comprehensive code review of this PHP class:\n\n{$sampleCode}\n\n" .
    "Cover security vulnerabilities, performance issues, best practice violations, and testing recommendations."
);

if ($result->isSuccess()) {
    echo "✅ Analysis Complete\n\n";
    
    echo "CODE REVIEW REPORT\n";
    echo str_repeat("=", 80) . "\n\n";
    echo $result->getAnswer() . "\n\n";
    echo str_repeat("=", 80) . "\n\n";
    
    // Show which experts contributed
    $metadata = $result->getMetadata();
    echo "📋 Review Details:\n";
    echo "  • Specialists consulted: " . implode(', ', $metadata['workers_used']) . "\n";
    echo "  • Analysis depth: {$metadata['subtasks']} specialized reviews\n";
    echo "  • Duration: {$metadata['duration_seconds']} seconds\n";
    
    $usage = $result->getTokenUsage();
    echo "  • Total tokens: {$usage['total']}\n";
    
    $inputCost = $usage['input'] * 0.003 / 1000;
    $outputCost = $usage['output'] * 0.015 / 1000;
    $totalCost = $inputCost + $outputCost;
    echo "  • Estimated cost: $" . number_format($totalCost, 4) . "\n";
}

// ============================================================================
// Example 2: Content Creation Team
// ============================================================================

echo "\n\n" . str_repeat("═", 80) . "\n";
echo "Example 2: Content Creation Team\n";
echo str_repeat("═", 80) . "\n\n";

// Research specialist
$researcher = new WorkerAgent($client, [
    'name' => 'researcher',
    'specialty' => 'topic research, fact-checking, data gathering, and source verification',
    'system' => 'You are a research specialist. Find key facts, statistics, expert opinions, and recent developments. Prioritize accuracy and cite your reasoning.',
]);

// SEO specialist
$seoExpert = new WorkerAgent($client, [
    'name' => 'seo_expert',
    'specialty' => 'SEO optimization, keyword research, meta tags, and search rankings',
    'system' => 'You are an SEO expert. Identify target keywords, suggest meta descriptions, recommend content structure, and optimize for search engine rankings.',
]);

// Content writer
$writer = new WorkerAgent($client, [
    'name' => 'content_writer',
    'specialty' => 'engaging writing, storytelling, audience connection, and readability',
    'system' => 'You are a professional content writer. Create compelling, engaging content with clear structure, storytelling elements, and emotional connection.',
]);

$contentTeam = new HierarchicalAgent($client, [
    'name' => 'content_pipeline',
]);

$contentTeam->registerWorker('researcher', $researcher);
$contentTeam->registerWorker('seo_expert', $seoExpert);
$contentTeam->registerWorker('content_writer', $writer);

echo "Content team assembled:\n";
foreach ($contentTeam->getWorkerNames() as $name) {
    $worker = $contentTeam->getWorker($name);
    echo "  • {$name}: {$worker->getSpecialty()}\n";
}

echo "\n\n" . str_repeat("═", 80) . "\n";
echo "Key Lessons on Worker Specialization\n";
echo str_repeat("═", 80) . "\n\n";

echo "✓ Focused Specialties:\n";
echo "  Good: 'security vulnerabilities, SQL injection, XSS'\n";
echo "  Bad:  'programming'\n\n";

echo "✓ Complementary Skills:\n";
echo "  Build teams where workers don't overlap but work together\n";
echo "  Example: security + performance + practices = comprehensive review\n\n";

echo "✓ Model Selection:\n";
echo "  Use Sonnet for complex analysis (security, performance)\n";
echo "  Use Haiku for simpler tasks (formatting, basic validation)\n\n";

echo "✓ System Prompts:\n";
echo "  Be specific about what each worker should do\n";
echo "  Include examples of expected output\n";
echo "  Define quality standards\n\n";

echo "Example completed!\n";
