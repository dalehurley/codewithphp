<?php

/**
 * Code Review Agent with Reflection
 * 
 * Practical example of using reflection loops for code review.
 * The agent reviews PHP code, reflects on review quality, and iteratively
 * improves feedback to be more thorough, specific, and actionable.
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

echo "Code Review Agent with Reflection\n";
echo str_repeat("=", 80) . "\n\n";

/**
 * Code Review Evaluation Criteria
 * 
 * Structured criteria ensure thorough, high-quality code reviews.
 */
$codeReviewCriteria = <<<'CRITERIA'
Evaluate this code review on the following dimensions:

1. Issue Detection (30%):
   - Are all bugs identified?
   - Are security vulnerabilities noted (SQL injection, XSS, etc.)?
   - Are performance problems flagged?
   - Are edge cases considered?

2. Suggestion Quality (30%):
   - Are suggestions specific and actionable?
   - Are code examples provided for fixes?
   - Are alternative approaches discussed?
   - Are best practices referenced?

3. Completeness (25%):
   - Is every part of the code addressed?
   - Are all imports/dependencies reviewed?
   - Is error handling evaluated?
   - Are tests/documentation mentioned?

4. Communication (15%):
   - Is feedback constructive and professional?
   - Are priorities clearly indicated (critical vs. nice-to-have)?
   - Is tone appropriate for peer review?
   - Are explanations clear?

Provide a quality score (1-10) and specific improvements for the review itself.
CRITERIA;

/**
 * Code Sample 1: Payment Processing (Multiple Issues)
 */
$paymentCode = <<<'PHP'
<?php

function processPayment($amount, $userId) {
    $pdo = new PDO('mysql:host=localhost;dbname=app', 'root', '');
    
    $stmt = $pdo->query("SELECT * FROM users WHERE id = $userId");
    $user = $stmt->fetch();
    
    if ($user['balance'] >= $amount) {
        $newBalance = $user['balance'] - $amount;
        $pdo->query("UPDATE users SET balance = $newBalance WHERE id = $userId");
        return true;
    }
    
    return false;
}
PHP;

echo "Code Sample 1: Payment Processing Function\n";
echo str_repeat("-", 80) . "\n";
echo $paymentCode;
echo str_repeat("-", 80) . "\n\n";

// Configure reflection loop for code review
$reviewLoop = new ReflectionLoop(
    maxRefinements: 3,
    qualityThreshold: 8,
    criteria: $codeReviewCriteria
);

$reviewLoop->onReflection(function (int $ref, int $score, string $feedback): void {
    echo "📋 Review Refinement {$ref}: Quality {$score}/10\n";
    if ($score < 8) {
        echo "   Improvements needed for the review itself:\n";
        $lines = explode("\n", $feedback);
        foreach (array_slice($lines, 0, 5) as $line) {
            echo "   " . trim($line) . "\n";
        }
        echo "\n";
    }
});

$reviewAgent = Agent::create($client)
    ->withLoopStrategy($reviewLoop)
    ->withSystemPrompt(
        'You are an expert PHP code reviewer with security and best practices expertise. ' .
        'Provide thorough, constructive feedback with specific examples and actionable recommendations. ' .
        'Prioritize security issues, then correctness, then best practices.'
    )
    ->maxIterations(15);

$reviewTask = "Review this payment processing code for:\n" .
              "1. Security vulnerabilities\n" .
              "2. Correctness and error handling\n" .
              "3. Best practices (type hints, PSR compliance)\n" .
              "4. Performance considerations\n\n" .
              "Provide specific, actionable feedback with code examples.\n\n" .
              $paymentCode;

echo "Performing code review with reflection...\n";
echo str_repeat("-", 80) . "\n\n";

$startTime = microtime(true);
$reviewResult = $reviewAgent->run($reviewTask);
$duration = microtime(true) - $startTime;

echo "\n" . str_repeat("=", 80) . "\n";
echo "FINAL CODE REVIEW\n";
echo str_repeat("=", 80) . "\n";
echo $reviewResult->getAnswer() . "\n";
echo str_repeat("=", 80) . "\n";

$metadata = $reviewResult->getMetadata();
echo "\nReview Quality Metrics:\n";
echo "  - Final Score: {$metadata['final_score']}/10\n";
echo "  - Refinements: " . count($metadata['reflections']) . "\n";
echo "  - Duration: " . round($duration, 2) . "s\n";
echo "  - Iterations: {$reviewResult->getIterations()}\n";

/**
 * Code Sample 2: API Controller (Better Code, Different Issues)
 */
echo "\n\n" . str_repeat("=", 80) . "\n";
echo "Code Sample 2: API Controller\n";
echo str_repeat("=", 80) . "\n\n";

$apiCode = <<<'PHP'
<?php

class UserController {
    public function getUser($id) {
        $user = Database::query("SELECT * FROM users WHERE id = ?", [$id]);
        
        if (!$user) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            return;
        }
        
        echo json_encode($user);
    }
    
    public function createUser() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $id = Database::insert('users', [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => md5($data['password'])
        ]);
        
        echo json_encode(['id' => $id, 'message' => 'User created']);
    }
}
PHP;

echo $apiCode;
echo "\n" . str_repeat("-", 80) . "\n\n";

$apiReviewLoop = new ReflectionLoop(
    maxRefinements: 2,
    qualityThreshold: 8,
    criteria: $codeReviewCriteria
);

$apiReviewLoop->onReflection(function (int $ref, int $score, string $feedback): void {
    echo "📋 API Review Refinement {$ref}: {$score}/10\n";
});

$apiReviewAgent = Agent::create($client)
    ->withLoopStrategy($apiReviewLoop)
    ->withSystemPrompt(
        'You are an expert API and web security reviewer. ' .
        'Focus on security, proper HTTP responses, validation, and modern PHP practices.'
    )
    ->maxIterations(15);

$apiReviewTask = "Review this API controller code for:\n" .
                 "1. Security issues (password handling, input validation)\n" .
                 "2. HTTP response handling\n" .
                 "3. Error handling and validation\n" .
                 "4. Type safety and modern PHP 8.4 features\n\n" .
                 $apiCode;

echo "Processing API code review...\n\n";

$apiResult = $apiReviewAgent->run($apiReviewTask);

echo "\n" . str_repeat("=", 80) . "\n";
echo "API CONTROLLER REVIEW\n";
echo str_repeat("=", 80) . "\n";
echo $apiResult->getAnswer() . "\n";
echo str_repeat("=", 80) . "\n";

$apiMetadata = $apiResult->getMetadata();
echo "\nReview Quality: {$apiMetadata['final_score']}/10\n";
echo "Refinements: " . count($apiMetadata['reflections']) . "\n";

/**
 * Summary and Insights
 */
echo "\n\n" . str_repeat("=", 80) . "\n";
echo "CODE REVIEW REFLECTION INSIGHTS\n";
echo str_repeat("=", 80) . "\n\n";

echo "What Reflection Improves:\n\n";

echo "1. Issue Detection:\n";
echo "   - Initial review might catch obvious SQL injection\n";
echo "   - Reflection prompts checking for ALL security issues\n";
echo "   - Final review includes password hashing, validation, race conditions\n\n";

echo "2. Suggestion Quality:\n";
echo "   - Initial: 'Use prepared statements'\n";
echo "   - After reflection: Provides complete code example with PDO\n";
echo "   - Shows before/after comparison\n\n";

echo "3. Completeness:\n";
echo "   - Initial review might focus on main function\n";
echo "   - Reflection identifies missing coverage (error handling, tests)\n";
echo "   - Final review addresses every aspect of the code\n\n";

echo "4. Communication:\n";
echo "   - Initial feedback might be terse\n";
echo "   - Reflection improves tone and structure\n";
echo "   - Final review is constructive, prioritized, actionable\n\n";

echo "Expected Quality Improvements:\n";
echo "  Refinement 1: ~6/10 - Basic issues identified\n";
echo "  Refinement 2: ~7/10 - More thorough, better examples\n";
echo "  Refinement 3: ~8/10 - Comprehensive, well-structured, actionable\n\n";

echo "✅ Code review examples complete!\n";
echo "\n💡 Use Case: Reflection is ideal for code review because:\n";
echo "   - Quality of feedback directly impacts development\n";
echo "   - Initial reviews often miss subtle issues\n";
echo "   - Self-critique catches gaps in coverage\n";
echo "   - Iterative refinement produces thorough, actionable reviews\n";
