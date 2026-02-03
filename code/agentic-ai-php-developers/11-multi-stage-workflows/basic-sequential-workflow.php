#!/usr/bin/env php
<?php
/**
 * Basic Sequential Workflow Example
 *
 * Demonstrates a simple multi-stage workflow that executes
 * agents in sequence: Extract → Analyze → Format
 *
 * Part of: Agentic AI for PHP Developers
 * Chapter 11: Multi-Stage Workflows and Agent Graphs
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use ClaudeAgents\Chains\ChainInput;
use ClaudeAgents\Chains\LLMChain;
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
echo "║            Basic Sequential Workflow Example                               ║\n";
echo "║            Extract → Analyze → Format                                      ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Define Workflow Stages
// ============================================================================

// Stage 1: Extract named entities from text
echo "Building Stage 1: Entity Extraction\n";
$extractionChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Extract all named entities (people, places, organizations) from this text. ' .
        'Format as a comma-separated list:\n\n{text}'
    ))
    ->withModel('claude-sonnet-4-5')
    ->withMaxTokens(500);

// Stage 2: Analyze sentiment of extracted entities
echo "Building Stage 2: Sentiment Analysis\n";
$analysisChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Analyze the sentiment and tone based on these entities: {entities}. ' .
        'Provide a brief analysis (2-3 sentences) of the overall sentiment.'
    ))
    ->withModel('claude-sonnet-4-5')
    ->withMaxTokens(500);

// Stage 3: Format as structured output
echo "Building Stage 3: Output Formatting\n";
$formatChain = TransformChain::create(function (array $input): array {
    return [
        'entities' => $input['entities'] ?? 'None found',
        'sentiment_analysis' => $input['analysis'] ?? 'No analysis available',
        'processed_at' => date('Y-m-d H:i:s'),
        'status' => 'complete',
        'word_count' => str_word_count($input['entities'] ?? ''),
    ];
});

// ============================================================================
// Compose Sequential Pipeline
// ============================================================================

echo "\nComposing Sequential Pipeline...\n";

$pipeline = SequentialChain::create()
    ->addChain('extract', $extractionChain)
    ->addChain('analyze', $analysisChain)
    ->addChain('format', $formatChain)
    // Map outputs between stages
    ->mapOutput('extract', 'result', 'analyze', 'entities')
    ->mapOutput('analyze', 'result', 'format', 'analysis')
    ->mapOutput('extract', 'result', 'format', 'entities')
    // Add callbacks for monitoring
    ->onBefore(function (ChainInput $input) {
        echo "\n[PIPELINE] Starting workflow execution...\n";
    })
    ->onAfter(function (ChainInput $input, $output) {
        echo "[PIPELINE] Workflow completed successfully!\n";
    })
    ->onError(function (ChainInput $input, \Throwable $error) {
        echo "[PIPELINE] Error: {$error->getMessage()}\n";
    });

// ============================================================================
// Execute Workflow
// ============================================================================

$sampleText = <<<TEXT
Apple Inc. announced a major partnership with Microsoft Corporation today.
CEO Tim Cook praised the collaboration, stating it will revolutionize cloud computing.
The deal was finalized in Cupertino, California, with executives from both companies present.
Industry analysts believe this partnership will significantly impact the technology sector.
TEXT;

echo "\n" . str_repeat("=", 80) . "\n";
echo "SAMPLE INPUT\n";
echo str_repeat("=", 80) . "\n";
echo $sampleText . "\n";
echo str_repeat("=", 80) . "\n";

echo "\n[WORKFLOW] Executing 3-stage pipeline...\n\n";

$startTime = microtime(true);

try {
    $result = $pipeline->invoke(['text' => $sampleText]);
    
    $duration = microtime(true) - $startTime;
    
    // Display results from each stage
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "STAGE 1: ENTITY EXTRACTION\n";
    echo str_repeat("=", 80) . "\n";
    echo $result['extract']['result'] ?? 'N/A';
    echo "\n\n";
    
    echo str_repeat("=", 80) . "\n";
    echo "STAGE 2: SENTIMENT ANALYSIS\n";
    echo str_repeat("=", 80) . "\n";
    echo $result['analyze']['result'] ?? 'N/A';
    echo "\n\n";
    
    echo str_repeat("=", 80) . "\n";
    echo "STAGE 3: FORMATTED OUTPUT\n";
    echo str_repeat("=", 80) . "\n";
    echo json_encode($result['format'], JSON_PRETTY_PRINT);
    echo "\n\n";
    
    // Display execution metrics
    echo str_repeat("=", 80) . "\n";
    echo "EXECUTION METRICS\n";
    echo str_repeat("=", 80) . "\n";
    echo "Total Duration: " . number_format($duration, 3) . "s\n";
    echo "Stages Executed: 3\n";
    echo "Status: " . ($result['format']['status'] ?? 'unknown') . "\n";
    
    // Check for step metadata
    $metadata = $result->getMetadata() ?? [];
    if (isset($metadata['steps'])) {
        echo "\nStep Details:\n";
        foreach ($metadata['steps'] as $stepName => $stepMeta) {
            echo "  - {$stepName}: executed\n";
        }
    }
    
    echo "\n";
    
} catch (\Exception $e) {
    echo "\n[ERROR] Workflow failed: {$e->getMessage()}\n";
    echo "Stack trace:\n{$e->getTraceAsString()}\n";
}

// ============================================================================
// Key Takeaways
// ============================================================================

echo "\n" . str_repeat("=", 80) . "\n";
echo "KEY CONCEPTS DEMONSTRATED\n";
echo str_repeat("=", 80) . "\n";
echo "1. Sequential Execution: Stages execute in order (Extract → Analyze → Format)\n";
echo "2. Output Mapping: Data flows between stages via mapOutput()\n";
echo "3. Chain Composition: LLMChain + TransformChain combined seamlessly\n";
echo "4. Callbacks: onBefore/onAfter for monitoring workflow progress\n";
echo "5. Error Handling: Try/catch wrapper for graceful failure handling\n";
echo "\n";

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                         Example Complete                                   ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n";
