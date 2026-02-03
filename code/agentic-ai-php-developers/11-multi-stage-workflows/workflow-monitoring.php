#!/usr/bin/env php
<?php
/**
 * Workflow Monitoring Example
 *
 * Demonstrates comprehensive workflow observability with:
 * - Real-time step tracking
 * - Performance metrics
 * - Error logging
 * - Execution reports
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
echo "║              Workflow Monitoring Example                                   ║\n";
echo "║              Track execution, performance, and errors                      ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Workflow Monitor Class
// ============================================================================

class WorkflowMonitor
{
    private array $steps = [];
    private float $startTime;
    private array $errors = [];
    private array $metrics = [];
    
    public function start(): void
    {
        $this->startTime = microtime(true);
        $this->steps = [];
        $this->errors = [];
        $this->metrics = [];
        
        $this->log('info', 'Workflow started', ['timestamp' => date('Y-m-d H:i:s')]);
    }
    
    public function recordStep(string $name, string $status, ?array $metadata = null): void
    {
        $timestamp = microtime(true) - $this->startTime;
        
        $step = [
            'name' => $name,
            'status' => $status,
            'timestamp' => $timestamp,
            'metadata' => $metadata ?? [],
        ];
        
        $this->steps[] = $step;
        
        $statusIcon = match ($status) {
            'started' => '▶',
            'complete' => '✓',
            'failed' => '✗',
            'skipped' => '⊘',
            default => '○',
        };
        
        $duration = number_format($timestamp, 3);
        $this->log('info', "{$statusIcon} {$name}: {$status}", [
            'duration' => $duration . 's',
            'metadata' => $metadata,
        ]);
    }
    
    public function recordError(string $step, string $error, ?array $context = null): void
    {
        $this->errors[] = [
            'step' => $step,
            'error' => $error,
            'context' => $context ?? [],
            'timestamp' => microtime(true) - $this->startTime,
        ];
        
        $this->log('error', "Error in {$step}: {$error}", $context);
    }
    
    public function recordMetric(string $name, $value, ?string $unit = null): void
    {
        $this->metrics[$name] = [
            'value' => $value,
            'unit' => $unit,
            'timestamp' => microtime(true) - $this->startTime,
        ];
    }
    
    public function getReport(): array
    {
        $duration = microtime(true) - $this->startTime;
        
        $statusCounts = [
            'started' => 0,
            'complete' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];
        
        foreach ($this->steps as $step) {
            $status = $step['status'];
            if (isset($statusCounts[$status])) {
                $statusCounts[$status]++;
            }
        }
        
        return [
            'summary' => [
                'total_duration' => $duration,
                'total_steps' => count($this->steps),
                'successful' => $statusCounts['complete'],
                'failed' => $statusCounts['failed'],
                'skipped' => $statusCounts['skipped'],
                'errors' => count($this->errors),
            ],
            'steps' => $this->steps,
            'errors' => $this->errors,
            'metrics' => $this->metrics,
        ];
    }
    
    public function printReport(): void
    {
        $report = $this->getReport();
        
        echo "\n" . str_repeat("=", 80) . "\n";
        echo "WORKFLOW EXECUTION REPORT\n";
        echo str_repeat("=", 80) . "\n";
        
        $summary = $report['summary'];
        echo "Duration: " . number_format($summary['total_duration'], 3) . "s\n";
        echo "Steps: {$summary['total_steps']}\n";
        echo "  ✓ Successful: {$summary['successful']}\n";
        echo "  ✗ Failed: {$summary['failed']}\n";
        echo "  ⊘ Skipped: {$summary['skipped']}\n";
        
        if ($summary['errors'] > 0) {
            echo "\nErrors: {$summary['errors']}\n";
        }
        
        if (!empty($report['metrics'])) {
            echo "\n" . str_repeat("-", 80) . "\n";
            echo "METRICS\n";
            echo str_repeat("-", 80) . "\n";
            
            foreach ($report['metrics'] as $name => $metric) {
                $unit = $metric['unit'] ?? '';
                echo "  {$name}: {$metric['value']}{$unit}\n";
            }
        }
        
        if (!empty($report['steps'])) {
            echo "\n" . str_repeat("-", 80) . "\n";
            echo "STEP TIMELINE\n";
            echo str_repeat("-", 80) . "\n";
            
            foreach ($report['steps'] as $step) {
                $time = number_format($step['timestamp'], 3);
                $statusIcon = match ($step['status']) {
                    'started' => '▶',
                    'complete' => '✓',
                    'failed' => '✗',
                    'skipped' => '⊘',
                    default => '○',
                };
                
                echo "  [{$time}s] {$statusIcon} {$step['name']}: {$step['status']}\n";
            }
        }
        
        if (!empty($report['errors'])) {
            echo "\n" . str_repeat("-", 80) . "\n";
            echo "ERROR LOG\n";
            echo str_repeat("-", 80) . "\n";
            
            foreach ($report['errors'] as $index => $error) {
                $time = number_format($error['timestamp'], 3);
                echo "\n" . ($index + 1) . ". [{$time}s] {$error['step']}\n";
                echo "   Error: {$error['error']}\n";
                
                if (!empty($error['context'])) {
                    echo "   Context: " . json_encode($error['context'], JSON_PRETTY_PRINT) . "\n";
                }
            }
        }
        
        echo "\n" . str_repeat("=", 80) . "\n";
    }
    
    private function log(string $level, string $message, ?array $context = null): void
    {
        $timestamp = date('Y-m-d H:i:s.u');
        $levelUpper = strtoupper($level);
        
        echo "[{$timestamp}] [{$levelUpper}] {$message}\n";
        
        if ($context !== null && !empty($context)) {
            $contextStr = json_encode($context, JSON_UNESCAPED_SLASHES);
            if (strlen($contextStr) < 200) {
                echo "   Context: {$contextStr}\n";
            }
        }
    }
}

// ============================================================================
// Build Monitored Workflow
// ============================================================================

echo "Building monitored workflow...\n\n";

$monitor = new WorkflowMonitor();

// Stage 1: Input preprocessing
$preprocessChain = TransformChain::create(function (array $input): array {
    return [
        'cleaned_text' => trim($input['text'] ?? ''),
        'word_count' => str_word_count($input['text'] ?? ''),
        'char_count' => strlen($input['text'] ?? ''),
    ];
});

// Stage 2: Analysis
$analysisChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Analyze this text and identify the main topic in 2-3 words:\n\n{cleaned_text}'
    ))
    ->withMaxTokens(100);

// Stage 3: Summary
$summaryChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Provide a 1-sentence summary of text about: {topic}\n\nOriginal: {cleaned_text}'
    ))
    ->withMaxTokens(200);

// Stage 4: Formatting
$formatChain = TransformChain::create(function (array $input): array {
    return [
        'topic' => $input['topic'] ?? 'Unknown',
        'summary' => $input['summary'] ?? 'No summary',
        'word_count' => $input['word_count'] ?? 0,
        'char_count' => $input['char_count'] ?? 0,
        'processed_at' => date('Y-m-d H:i:s'),
    ];
});

// Build pipeline with monitoring callbacks
$monitoredWorkflow = SequentialChain::create()
    ->addChain('preprocess', $preprocessChain)
    ->addChain('analyze', $analysisChain)
    ->addChain('summarize', $summaryChain)
    ->addChain('format', $formatChain)
    // Map outputs
    ->mapOutput('preprocess', 'cleaned_text', 'analyze', 'cleaned_text')
    ->mapOutput('analyze', 'result', 'summarize', 'topic')
    ->mapOutput('preprocess', 'cleaned_text', 'summarize', 'cleaned_text')
    ->mapOutput('analyze', 'result', 'format', 'topic')
    ->mapOutput('summarize', 'result', 'format', 'summary')
    ->mapOutput('preprocess', 'word_count', 'format', 'word_count')
    ->mapOutput('preprocess', 'char_count', 'format', 'char_count')
    // Global callbacks
    ->onBefore(function (ChainInput $input) use ($monitor) {
        $monitor->start();
        $monitor->recordStep('workflow', 'started', [
            'input_keys' => array_keys($input->all()),
        ]);
    })
    ->onAfter(function (ChainInput $input, $output) use ($monitor) {
        $monitor->recordStep('workflow', 'complete', [
            'output_keys' => array_keys($output->all()),
        ]);
    })
    ->onError(function (ChainInput $input, \Throwable $error) use ($monitor) {
        $monitor->recordError('workflow', $error->getMessage(), [
            'type' => get_class($error),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
        ]);
    });

echo "✓ Workflow configured with monitoring callbacks\n";

// ============================================================================
// Execute Monitored Workflow
// ============================================================================

$sampleText = <<<TEXT
Artificial Intelligence is transforming software development through advanced code generation,
automated testing, and intelligent debugging tools. Modern AI systems can understand context,
generate complex code structures, and help developers be more productive than ever before.
TEXT;

echo "\n" . str_repeat("=", 80) . "\n";
echo "SAMPLE INPUT\n";
echo str_repeat("=", 80) . "\n";
echo $sampleText . "\n";
echo str_repeat("=", 80) . "\n\n";

echo "Executing monitored workflow...\n\n";

try {
    // Monitor each stage
    $stageMonitors = [
        'preprocess' => function ($name, $timing) use ($monitor) {
            $monitor->recordStep($name, $timing === 'before' ? 'started' : 'complete');
        },
        'analyze' => function ($name, $timing) use ($monitor) {
            $monitor->recordStep($name, $timing === 'before' ? 'started' : 'complete');
        },
        'summarize' => function ($name, $timing) use ($monitor) {
            $monitor->recordStep($name, $timing === 'before' ? 'started' : 'complete');
        },
        'format' => function ($name, $timing) use ($monitor) {
            $monitor->recordStep($name, $timing === 'before' ? 'started' : 'complete');
        },
    ];
    
    // Execute workflow
    $result = $monitoredWorkflow->invoke(['text' => $sampleText]);
    
    // Record metrics
    $report = $monitor->getReport();
    $monitor->recordMetric('total_steps', count($report['steps']));
    $monitor->recordMetric('success_rate', 
        round(($report['summary']['successful'] / $report['summary']['total_steps']) * 100, 1),
        '%'
    );
    
    // Display results
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "WORKFLOW RESULTS\n";
    echo str_repeat("=", 80) . "\n";
    echo json_encode($result['format'], JSON_PRETTY_PRINT) . "\n";
    echo str_repeat("=", 80) . "\n";
    
    // Print monitoring report
    $monitor->printReport();
    
} catch (\Exception $e) {
    echo "\n[ERROR] Workflow execution failed: {$e->getMessage()}\n";
    $monitor->recordError('execution', $e->getMessage());
    $monitor->printReport();
}

// ============================================================================
// Key Takeaways
// ============================================================================

echo "\n" . str_repeat("=", 80) . "\n";
echo "KEY CONCEPTS DEMONSTRATED\n";
echo str_repeat("=", 80) . "\n";
echo "1. Real-time Monitoring: Track step execution as it happens\n";
echo "2. Performance Metrics: Measure duration and success rates\n";
echo "3. Error Logging: Capture and report workflow failures\n";
echo "4. Execution Timeline: Visualize workflow progress over time\n";
echo "5. Comprehensive Reports: Generate detailed execution summaries\n";
echo "6. Callback Integration: Use callbacks for non-intrusive monitoring\n";
echo "\n";

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                         Example Complete                                   ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n";
