#!/usr/bin/env php
<?php
/**
 * Complex DAG Workflow Example
 *
 * Demonstrates a sophisticated multi-path workflow with:
 * - Classification routing
 * - Specialized processing
 * - Parallel quality assurance
 * - Result aggregation
 *
 * Part of: Agentic AI for PHP Developers
 * Chapter 11: Multi-Stage Workflows and Agent Graphs
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use ClaudeAgents\Chains\LLMChain;
use ClaudeAgents\Chains\ParallelChain;
use ClaudeAgents\Chains\RouterChain;
use ClaudeAgents\Chains\SequentialChain;
use ClaudeAgents\Chains\TransformChain;
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
echo "║              Complex DAG Workflow Example                                  ║\n";
echo "║              Classify → Route → Process → QA → Report                      ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

/**
 * Workflow Structure:
 * 
 *                     ┌─────────────┐
 *                     │  Classify   │
 *                     └──────┬──────┘
 *                            ↓
 *                     ┌──────────────┐
 *                     │RouterChain   │
 *                     │ (3 routes)   │
 *                     └─┬─────┬────┬─┘
 *                       ↓     ↓    ↓
 *                   ┌────┐ ┌───┐ ┌───┐
 *                   │Code│ │Doc│ │Q&A│
 *                   └─┬──┘ └─┬─┘ └─┬─┘
 *                     └──────┼─────┘
 *                            ↓
 *                     ┌──────────────┐
 *                     │ParallelChain │
 *                     │(Review+Valid)│
 *                     └──────┬───────┘
 *                            ↓
 *                     ┌──────────────┐
 *                     │Final Report  │
 *                     └──────────────┘
 */

// ============================================================================
// Stage 1: Content Classification
// ============================================================================

echo "Building Stage 1: Content Classification\n";

$classifyChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Classify this content as exactly one of: CODE, DOCUMENTATION, QUESTION, or OTHER\n\n' .
        '{content}\n\n' .
        'Return ONLY the classification word, nothing else.'
    ))
    ->withModel('claude-sonnet-4-5')
    ->withMaxTokens(50);

// ============================================================================
// Stage 2a: Specialized Processing Chains
// ============================================================================

echo "Building Stage 2: Specialized Processing Chains\n";

// Code processing
$codeChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Analyze this PHP code:\n\n{content}\n\n' .
        'Provide:\n' .
        '1. Purpose and functionality\n' .
        '2. Potential issues or bugs\n' .
        '3. Security concerns\n' .
        '4. Improvement suggestions'
    ))
    ->withModel('claude-sonnet-4-5')
    ->withMaxTokens(1000);

// Documentation processing
$docsChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Review this documentation:\n\n{content}\n\n' .
        'Provide:\n' .
        '1. Clarity assessment (1-10)\n' .
        '2. Completeness check\n' .
        '3. Missing information\n' .
        '4. Improvement suggestions'
    ))
    ->withModel('claude-sonnet-4-5')
    ->withMaxTokens(1000);

// Question answering
$qaChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Answer this question thoroughly:\n\n{content}\n\n' .
        'Provide a comprehensive answer with:\n' .
        '1. Direct answer\n' .
        '2. Explanation\n' .
        '3. Examples if appropriate\n' .
        '4. Additional context'
    ))
    ->withModel('claude-sonnet-4-5')
    ->withMaxTokens(800);

// ============================================================================
// Stage 2b: Router for Specialized Processing
// ============================================================================

echo "Building Stage 2b: Processing Router\n";

$processingRouter = RouterChain::create()
    ->addRoute(
        fn($input) => str_contains(strtoupper($input['classification'] ?? ''), 'CODE'),
        $codeChain
    )
    ->addRoute(
        fn($input) => str_contains(strtoupper($input['classification'] ?? ''), 'DOCUMENTATION'),
        $docsChain
    )
    ->addRoute(
        fn($input) => str_contains(strtoupper($input['classification'] ?? ''), 'QUESTION'),
        $qaChain
    )
    ->setDefault($codeChain);

// ============================================================================
// Stage 3: Parallel Quality Assurance
// ============================================================================

echo "Building Stage 3: Parallel QA (Review + Validation)\n";

// Review chain
$reviewChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Review this analysis for accuracy and completeness:\n\n' .
        'Classification: {classification}\n' .
        'Analysis: {processing_result}\n\n' .
        'Provide a quality score (1-10) and identify any issues.'
    ))
    ->withModel('claude-sonnet-4-5')
    ->withMaxTokens(500);

// Validation chain (programmatic checks)
$validateChain = TransformChain::create(function (array $input): array {
    $processingResult = $input['processing_result'] ?? '';
    $classification = $input['classification'] ?? 'UNKNOWN';
    
    $wordCount = str_word_count($processingResult);
    $hasNumbers = preg_match('/\d/', $processingResult);
    $hasCode = str_contains($processingResult, '<?php') || str_contains($processingResult, 'function');
    
    // Validation rules based on classification
    $minWordCount = match(strtoupper($classification)) {
        'CODE' => 50,
        'DOCUMENTATION' => 30,
        'QUESTION' => 20,
        default => 10,
    };
    
    $isValid = $wordCount >= $minWordCount;
    $qualityScore = min(10, $wordCount / 10);
    
    $checks = [
        'word_count' => [
            'value' => $wordCount,
            'passed' => $wordCount >= $minWordCount,
            'message' => "Word count: {$wordCount} (minimum: {$minWordCount})",
        ],
        'content_length' => [
            'value' => strlen($processingResult),
            'passed' => strlen($processingResult) > 100,
            'message' => 'Content length adequate',
        ],
        'structured_content' => [
            'value' => substr_count($processingResult, "\n") > 3,
            'passed' => substr_count($processingResult, "\n") > 3,
            'message' => 'Contains multiple paragraphs/sections',
        ],
    ];
    
    return [
        'is_valid' => $isValid,
        'quality_score' => round($qualityScore, 1),
        'checks' => $checks,
        'metrics' => [
            'word_count' => $wordCount,
            'character_count' => strlen($processingResult),
            'line_count' => substr_count($processingResult, "\n") + 1,
            'has_numbers' => $hasNumbers,
            'has_code_examples' => $hasCode,
        ],
    ];
});

// Compose parallel QA
$parallelQA = ParallelChain::create()
    ->addChain('review', $reviewChain)
    ->addChain('validate', $validateChain)
    ->withAggregation('all');

// ============================================================================
// Stage 4: Final Report Generation
// ============================================================================

echo "Building Stage 4: Final Report Generation\n";

$reportChain = TransformChain::create(function (array $input): array {
    $validation = $input['validation'] ?? [];
    $review = $input['review'] ?? '';
    
    $status = ($validation['is_valid'] ?? false) ? 'approved' : 'needs_review';
    
    return [
        'workflow_id' => uniqid('wf-'),
        'classification' => $input['classification'] ?? 'unknown',
        'processing_result' => substr($input['processing_result'] ?? '', 0, 500) . '...',
        'qa_results' => [
            'review' => substr($review, 0, 200) . '...',
            'validation' => $validation,
        ],
        'status' => $status,
        'timestamp' => date('Y-m-d H:i:s'),
        'metrics' => $validation['metrics'] ?? [],
    ];
});

// ============================================================================
// Compose the Complete DAG Workflow
// ============================================================================

echo "\nComposing Complete DAG Workflow...\n";

$complexWorkflow = SequentialChain::create()
    ->addChain('classify', $classifyChain)
    ->addChain('process', $processingRouter)
    ->addChain('qa', $parallelQA)
    ->addChain('report', $reportChain)
    // Map outputs between stages
    ->mapOutput('classify', 'result', 'process', 'classification')
    ->mapOutput('process', 'result', 'qa', 'processing_result')
    ->mapOutput('classify', 'result', 'qa', 'classification')
    ->mapOutput('classify', 'result', 'report', 'classification')
    ->mapOutput('process', 'result', 'report', 'processing_result')
    ->mapOutput('qa', 'results', 'report', 'qa_results');

echo "✓ Workflow composed with 4 main stages\n";
echo "✓ Includes branching (RouterChain) and merging (ParallelChain)\n";

// ============================================================================
// Test the Workflow
// ============================================================================

$testCases = [
    [
        'name' => 'PHP Code',
        'content' => <<<'CODE'
<?php
class UserRepository {
    private PDO $db;
    
    public function findById(int $id): ?User {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        
        return $data ? User::fromArray($data) : null;
    }
    
    public function save(User $user): void {
        // Implementation
    }
}
CODE
    ],
    [
        'name' => 'Documentation',
        'content' => <<<'DOC'
# Database Configuration

This guide explains how to configure the database connection.

## Environment Variables

Set the following environment variables:
- DB_HOST: Database server hostname
- DB_NAME: Database name
- DB_USER: Database username
- DB_PASS: Database password

## Connection Pool

The application uses a connection pool with a maximum of 10 connections.
DOC
    ],
];

foreach ($testCases as $index => $testCase) {
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "TEST CASE " . ($index + 1) . ": {$testCase['name']}\n";
    echo str_repeat("=", 80) . "\n";
    
    $content = $testCase['content'];
    echo "Input: " . substr(str_replace("\n", " ", $content), 0, 80) . "...\n\n";
    
    echo "[WORKFLOW] Executing DAG pipeline...\n";
    
    $startTime = microtime(true);
    
    try {
        $result = $complexWorkflow->invoke(['content' => $content]);
        
        $duration = microtime(true) - $startTime;
        
        echo "\n--- Stage Results ---\n\n";
        
        echo "1. CLASSIFICATION: " . ($result['classify']['result'] ?? 'N/A') . "\n";
        
        $processMeta = $result['process']->getMetadata() ?? [];
        echo "2. ROUTING: " . ($processMeta['route'] ?? 'unknown') . " (type: " . ($processMeta['type'] ?? 'unknown') . ")\n";
        
        echo "3. PROCESSING:\n";
        echo "   " . substr($result['process']['result'] ?? 'N/A', 0, 150) . "...\n";
        
        echo "4. QA VALIDATION:\n";
        $validation = $result['qa']['results']['validate'] ?? [];
        echo "   Valid: " . ($validation['is_valid'] ? 'YES' : 'NO') . "\n";
        echo "   Quality Score: " . ($validation['quality_score'] ?? 'N/A') . "/10\n";
        
        echo "\n--- Final Report ---\n";
        echo json_encode($result['report'], JSON_PRETTY_PRINT) . "\n";
        
        echo "\n--- Execution Metrics ---\n";
        echo "Total Duration: " . number_format($duration, 3) . "s\n";
        echo "Workflow Status: " . ($result['report']['status'] ?? 'unknown') . "\n";
        
        echo "\n";
        
    } catch (\Exception $e) {
        echo "\n[ERROR] Workflow failed: {$e->getMessage()}\n";
    }
    
    // Delay between tests
    if ($index < count($testCases) - 1) {
        sleep(2);
    }
}

// ============================================================================
// Key Takeaways
// ============================================================================

echo "\n" . str_repeat("=", 80) . "\n";
echo "KEY CONCEPTS DEMONSTRATED\n";
echo str_repeat("=", 80) . "\n";
echo "1. DAG Structure: Multi-stage workflow with branching and merging\n";
echo "2. Conditional Routing: RouterChain routes based on classification\n";
echo "3. Parallel Processing: QA review and validation run concurrently\n";
echo "4. Data Flow: Output mapping connects stages seamlessly\n";
echo "5. Validation: Programmatic + AI-based quality checks\n";
echo "6. Structured Outputs: Final report aggregates all stage results\n";
echo "\n";

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                         Example Complete                                   ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n";
