#!/usr/bin/env php
<?php
/**
 * Code Review System
 * 
 * A production-ready code review system using hierarchical agents.
 * Multiple specialists review code from different angles: security,
 * performance, best practices, and testing.
 * 
 * This example shows:
 * - Building a multi-specialist review team
 * - Comprehensive code analysis
 * - Structured review output
 * - Cost and performance tracking
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
echo "║                  Production Code Review System                            ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Build Code Review Team
// ============================================================================

echo "Building code review team...\n\n";

// Security Expert
$securityWorker = new WorkerAgent($client, [
    'name' => 'security_expert',
    'specialty' => 'security vulnerabilities, SQL injection, XSS, CSRF, authentication flaws, and data exposure',
    'system' => 'You are a senior security engineer. Review code for:\n' .
                '- SQL injection vulnerabilities\n' .
                '- Cross-site scripting (XSS)\n' .
                '- CSRF attacks\n' .
                '- Authentication and authorization issues\n' .
                '- Sensitive data exposure\n' .
                '- Input validation gaps\n\n' .
                'For each issue, provide:\n' .
                '1. Severity (Critical/High/Medium/Low)\n' .
                '2. Explanation of the vulnerability\n' .
                '3. Specific code fix with examples',
]);

echo "  ✓ Security Expert configured\n";

// Performance Expert
$performanceWorker = new WorkerAgent($client, [
    'name' => 'performance_expert',
    'specialty' => 'performance optimization, algorithm efficiency, database queries, N+1 problems, and scalability',
    'system' => 'You are a performance optimization expert. Review code for:\n' .
                '- N+1 query problems\n' .
                '- Inefficient algorithms (O(n²) where O(n) possible)\n' .
                '- Memory leaks and excessive memory usage\n' .
                '- Missing indexes or query optimization\n' .
                '- Unnecessary computations in loops\n' .
                '- Caching opportunities\n\n' .
                'For each issue, provide:\n' .
                '1. Performance impact estimate\n' .
                '2. Specific optimization recommendation\n' .
                '3. Code example of improved version',
]);

echo "  ✓ Performance Expert configured\n";

// Best Practices Expert
$practicesWorker = new WorkerAgent($client, [
    'name' => 'practices_expert',
    'specialty' => 'coding standards, SOLID principles, design patterns, PSR standards, and code maintainability',
    'system' => 'You are a code quality and architecture expert. Review code for:\n' .
                '- SOLID principle violations\n' .
                '- Design pattern opportunities\n' .
                '- PSR-12 coding standard compliance\n' .
                '- Naming conventions (clear, descriptive names)\n' .
                '- Code duplication (DRY principle)\n' .
                '- Single Responsibility violations\n' .
                '- Type safety (type hints, return types)\n\n' .
                'For each issue, provide:\n' .
                '1. Impact on maintainability\n' .
                '2. Recommended pattern or refactoring\n' .
                '3. Example of improved structure',
]);

echo "  ✓ Best Practices Expert configured\n";

// Test Coverage Expert
$testWorker = new WorkerAgent($client, [
    'name' => 'test_expert',
    'specialty' => 'unit testing, integration testing, test coverage, edge cases, and PHPUnit',
    'system' => 'You are a testing specialist. Review code for:\n' .
                '- Testability of the code\n' .
                '- Missing test cases\n' .
                '- Edge cases that should be tested\n' .
                '- Error condition testing\n' .
                '- Mock/stub opportunities\n' .
                '- Integration test needs\n\n' .
                'For each recommendation, provide:\n' .
                '1. Test case description\n' .
                '2. Example PHPUnit test code\n' .
                '3. Expected outcome',
]);

echo "  ✓ Test Expert configured\n\n";

// Create master coordinator
$codeReviewer = new HierarchicalAgent($client, [
    'name' => 'code_review_master',
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 2048,
]);

$codeReviewer->registerWorker('security_expert', $securityWorker);
$codeReviewer->registerWorker('performance_expert', $performanceWorker);
$codeReviewer->registerWorker('practices_expert', $practicesWorker);
$codeReviewer->registerWorker('test_expert', $testWorker);

echo "Code review team ready with 4 specialists\n\n";

// ============================================================================
// Sample Code to Review
// ============================================================================

$codeToReview = <<<'PHP'
<?php

class UserController
{
    private $db;
    
    public function __construct($database)
    {
        $this->db = $database;
    }
    
    public function getUserProfile($userId)
    {
        // Get user data
        $query = "SELECT * FROM users WHERE id = " . $userId;
        $result = mysqli_query($this->db, $query);
        $user = mysqli_fetch_assoc($result);
        
        if (!$user) {
            return null;
        }
        
        // Get user's posts
        $postsQuery = "SELECT * FROM posts WHERE user_id = " . $userId;
        $postsResult = mysqli_query($this->db, $postsQuery);
        
        $posts = [];
        while ($post = mysqli_fetch_assoc($postsResult)) {
            // Get comments for each post
            $commentsQuery = "SELECT * FROM comments WHERE post_id = " . $post['id'];
            $commentsResult = mysqli_query($this->db, $commentsQuery);
            
            $comments = [];
            while ($comment = mysqli_fetch_assoc($commentsResult)) {
                $comments[] = $comment;
            }
            
            $post['comments'] = $comments;
            $posts[] = $post;
        }
        
        $user['posts'] = $posts;
        
        return $user;
    }
    
    public function updateUser($userId, $data)
    {
        $name = $data['name'];
        $email = $data['email'];
        
        $query = "UPDATE users SET name = '$name', email = '$email' WHERE id = $userId";
        $result = mysqli_query($this->db, $query);
        
        if ($result) {
            return ['success' => true];
        }
        
        return ['success' => false];
    }
    
    public function deleteUser($userId)
    {
        $query = "DELETE FROM users WHERE id = " . $userId;
        mysqli_query($this->db, $query);
        
        return ['deleted' => true];
    }
}
PHP;

echo "Code to Review:\n";
echo str_repeat("-", 80) . "\n";
echo $codeToReview . "\n";
echo str_repeat("-", 80) . "\n\n";

// ============================================================================
// Execute Comprehensive Review
// ============================================================================

echo "Starting comprehensive code review...\n";
echo "This will take ~20-30 seconds as 4 specialists analyze the code\n\n";

$startTime = microtime(true);

$result = $codeReviewer->run(
    "Perform a comprehensive code review of this PHP UserController class. " .
    "Analyze for security vulnerabilities, performance issues, best practice violations, " .
    "and testing recommendations. Be specific and provide code examples for fixes.\n\n" .
    "Code:\n{$codeToReview}"
);

$duration = microtime(true) - $startTime;

// ============================================================================
// Display Review Results
// ============================================================================

if ($result->isSuccess()) {
    echo "✅ Code Review Complete!\n\n";
    
    echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
    echo "║                        CODE REVIEW REPORT                                  ║\n";
    echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";
    
    echo $result->getAnswer() . "\n\n";
    
    echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
    echo "║                        REVIEW METADATA                                     ║\n";
    echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";
    
    $metadata = $result->getMetadata();
    
    echo "📊 Execution Details:\n";
    echo "  • Duration: " . round($duration, 2) . " seconds\n";
    echo "  • Specialists consulted: " . implode(', ', $metadata['workers_used']) . "\n";
    echo "  • Individual reviews: {$metadata['subtasks']}\n";
    echo "  • Total iterations: {$result->getIterations()}\n\n";
    
    echo "💰 Cost Analysis:\n";
    $usage = $result->getTokenUsage();
    echo "  • Input tokens: " . number_format($usage['input']) . "\n";
    echo "  • Output tokens: " . number_format($usage['output']) . "\n";
    echo "  • Total tokens: " . number_format($usage['total']) . "\n";
    
    $inputCost = $usage['input'] * 0.003 / 1000;
    $outputCost = $usage['output'] * 0.015 / 1000;
    $totalCost = $inputCost + $outputCost;
    
    echo "  • Input cost: $" . number_format($inputCost, 4) . "\n";
    echo "  • Output cost: $" . number_format($outputCost, 4) . "\n";
    echo "  • Total cost: $" . number_format($totalCost, 4) . "\n\n";
    
    echo "📈 Cost per Specialist:\n";
    $costPerWorker = $totalCost / count($metadata['workers_used']);
    echo "  • Average: $" . number_format($costPerWorker, 4) . " per specialist\n\n";
    
} else {
    echo "❌ Code review failed: {$result->getError()}\n";
    exit(1);
}

// ============================================================================
// Summary and Recommendations
// ============================================================================

echo str_repeat("═", 80) . "\n";
echo "Code Review System Summary\n";
echo str_repeat("═", 80) . "\n\n";

echo "✓ Benefits of Multi-Specialist Review:\n";
echo "  • Security expert catches vulnerabilities others miss\n";
echo "  • Performance expert identifies optimization opportunities\n";
echo "  • Best practices expert ensures long-term maintainability\n";
echo "  • Test expert provides comprehensive test coverage\n\n";

echo "✓ Issues Found in Sample Code:\n";
echo "  • SQL Injection: Direct interpolation of user input\n";
echo "  • N+1 Queries: Comments loaded in loop (performance issue)\n";
echo "  • Missing Type Hints: Parameters lack type declarations\n";
echo "  • No Error Handling: Database errors not caught\n";
echo "  • No Tests: Code lacks unit tests\n\n";

echo "✓ Production Recommendations:\n";
echo "  1. Run this review on every pull request\n";
echo "  2. Track review metrics over time\n";
echo "  3. Train team based on common issues found\n";
echo "  4. Integrate with CI/CD pipeline\n";
echo "  5. Cache reviews for unchanged code\n\n";

echo "Example completed successfully!\n";
