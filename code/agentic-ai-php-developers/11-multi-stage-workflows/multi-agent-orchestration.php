#!/usr/bin/env php
<?php
/**
 * Multi-Agent Orchestration Example
 *
 * Demonstrates coordinating specialized agents with defined roles,
 * shared memory, and handoff patterns.
 *
 * Workflow: Researcher → Analyzer → Writer
 *
 * Part of: Agentic AI for PHP Developers
 * Chapter 11: Multi-Stage Workflows and Agent Graphs
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use ClaudeAgents\Agent;
use ClaudeAgents\MultiAgent\SharedMemory;
use ClaudeAgents\Tools\Tool;
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
echo "║              Multi-Agent Orchestration Example                             ║\n";
echo "║              Researcher → Analyzer → Writer                                ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

/**
 * Agent Workflow:
 * 
 * 1. Researcher Agent:
 *    - Gathers information on the topic
 *    - Stores findings in shared memory
 *
 * 2. Analyzer Agent:
 *    - Retrieves research from memory
 *    - Analyzes and identifies key insights
 *    - Stores analysis in shared memory
 *
 * 3. Writer Agent:
 *    - Retrieves analysis from memory
 *    - Creates comprehensive report
 *    - Returns final output
 */

// ============================================================================
// Initialize Shared Memory
// ============================================================================

echo "Initializing shared memory for agent collaboration...\n";

$sharedMemory = new SharedMemory();

echo "✓ Shared memory initialized\n\n";

// ============================================================================
// Define Specialized Agents
// ============================================================================

echo "Creating specialized agents...\n\n";

// Agent 1: Researcher
echo "1. Researcher Agent\n";
echo "   Role: Gather and organize information\n";

$researcher = Agent::create($client)
    ->withSystemPrompt(
        'You are a research specialist with expertise in technology and programming. ' .
        'Your role is to gather comprehensive information on topics and organize your findings clearly. ' .
        'Use the store_findings tool to save your research for other agents to use.'
    )
    ->withTool(Tool::create('store_findings')
        ->description('Store research findings in shared memory for other agents')
        ->parameter('key', 'string', 'Storage key for the findings')
        ->parameter('findings', 'string', 'Research findings and data')
        ->required('key', 'findings')
        ->handler(function (array $input) use ($sharedMemory) {
            $key = $input['key'];
            $findings = $input['findings'];
            
            $sharedMemory->set($key, $findings);
            
            return "✓ Research findings stored under key: {$key}\nLength: " . strlen($findings) . " characters";
        }))
    ->maxIterations(10);

echo "   ✓ Researcher agent created with store_findings tool\n\n";

// Agent 2: Analyzer
echo "2. Analyzer Agent\n";
echo "   Role: Analyze research and identify insights\n";

$analyzer = Agent::create($client)
    ->withSystemPrompt(
        'You are an analysis specialist with deep expertise in interpreting research data. ' .
        'Your role is to retrieve research findings, analyze them, and identify key insights, ' .
        'patterns, and conclusions. Use the tools to retrieve and store your analysis.'
    )
    ->withTool(Tool::create('get_findings')
        ->description('Retrieve research findings from shared memory')
        ->parameter('key', 'string', 'Storage key for the findings')
        ->required('key')
        ->handler(function (array $input) use ($sharedMemory) {
            $key = $input['key'];
            $data = $sharedMemory->get($key);
            
            if ($data === null) {
                return "✗ No findings found for key: {$key}";
            }
            
            return "✓ Retrieved findings for: {$key}\n\n{$data}";
        }))
    ->withTool(Tool::create('store_analysis')
        ->description('Store analysis results in shared memory')
        ->parameter('key', 'string', 'Storage key for the analysis')
        ->parameter('analysis', 'string', 'Analysis results and insights')
        ->required('key', 'analysis')
        ->handler(function (array $input) use ($sharedMemory) {
            $key = $input['key'];
            $analysis = $input['analysis'];
            
            $sharedMemory->set($key, $analysis);
            
            return "✓ Analysis stored under key: {$key}\nLength: " . strlen($analysis) . " characters";
        }))
    ->maxIterations(10);

echo "   ✓ Analyzer agent created with get_findings and store_analysis tools\n\n";

// Agent 3: Writer
echo "3. Writer Agent\n";
echo "   Role: Create comprehensive reports\n";

$writer = Agent::create($client)
    ->withSystemPrompt(
        'You are a technical writer with expertise in creating clear, comprehensive reports. ' .
        'Your role is to retrieve analysis from memory and create well-structured, professional ' .
        'reports that are easy to understand. Focus on clarity, accuracy, and good organization.'
    )
    ->withTool(Tool::create('get_data')
        ->description('Retrieve data from shared memory')
        ->parameter('key', 'string', 'Storage key for the data')
        ->required('key')
        ->handler(function (array $input) use ($sharedMemory) {
            $key = $input['key'];
            $data = $sharedMemory->get($key);
            
            if ($data === null) {
                return "✗ No data found for key: {$key}";
            }
            
            return "✓ Retrieved data for: {$key}\n\n{$data}";
        }))
    ->maxIterations(10);

echo "   ✓ Writer agent created with get_data tool\n\n";

// ============================================================================
// Execute Multi-Agent Workflow
// ============================================================================

$topic = 'PHP 8.4 Property Hooks';

echo str_repeat("=", 80) . "\n";
echo "MULTI-AGENT WORKFLOW EXECUTION\n";
echo "Topic: {$topic}\n";
echo str_repeat("=", 80) . "\n\n";

$startTime = microtime(true);

try {
    // Stage 1: Research
    echo str_repeat("-", 80) . "\n";
    echo "STAGE 1: RESEARCH\n";
    echo str_repeat("-", 80) . "\n";
    
    $researchStart = microtime(true);
    
    echo "[Researcher] Starting research on: {$topic}\n";
    
    $researchResult = $researcher->run(
        "Research the benefits and usage of {$topic}. " .
        "Provide comprehensive information about what they are, how they work, and their advantages. " .
        "Store your findings under the key 'research_findings'."
    );
    
    $researchDuration = microtime(true) - $researchStart;
    
    echo "\n[Researcher] Research complete!\n";
    echo "Response preview: " . substr($researchResult->getAnswer(), 0, 200) . "...\n";
    echo "Iterations: " . $researchResult->getIterations() . "\n";
    echo "Duration: " . number_format($researchDuration, 2) . "s\n\n";
    
    // Stage 2: Analysis
    echo str_repeat("-", 80) . "\n";
    echo "STAGE 2: ANALYSIS\n";
    echo str_repeat("-", 80) . "\n";
    
    $analysisStart = microtime(true);
    
    echo "[Analyzer] Starting analysis of research findings\n";
    
    $analysisResult = $analyzer->run(
        "Retrieve the research findings from 'research_findings', analyze them thoroughly, " .
        "and identify the key insights, benefits, and use cases. " .
        "Store your analysis under the key 'analysis_results'."
    );
    
    $analysisDuration = microtime(true) - $analysisStart;
    
    echo "\n[Analyzer] Analysis complete!\n";
    echo "Response preview: " . substr($analysisResult->getAnswer(), 0, 200) . "...\n";
    echo "Iterations: " . $analysisResult->getIterations() . "\n";
    echo "Duration: " . number_format($analysisDuration, 2) . "s\n\n";
    
    // Stage 3: Report Writing
    echo str_repeat("-", 80) . "\n";
    echo "STAGE 3: REPORT WRITING\n";
    echo str_repeat("-", 80) . "\n";
    
    $writingStart = microtime(true);
    
    echo "[Writer] Starting report generation\n";
    
    $reportResult = $writer->run(
        "Retrieve the analysis from 'analysis_results' and create a comprehensive, " .
        "well-structured report about {$topic}. The report should include: " .
        "1) Introduction, 2) Key Features, 3) Benefits, 4) Use Cases, and 5) Conclusion."
    );
    
    $writingDuration = microtime(true) - $writingStart;
    
    echo "\n[Writer] Report complete!\n";
    echo "Iterations: " . $reportResult->getIterations() . "\n";
    echo "Duration: " . number_format($writingDuration, 2) . "s\n\n";
    
    // Display final report
    echo str_repeat("=", 80) . "\n";
    echo "FINAL REPORT\n";
    echo str_repeat("=", 80) . "\n";
    echo $reportResult->getAnswer() . "\n";
    echo str_repeat("=", 80) . "\n\n";
    
    // Calculate total metrics
    $totalDuration = microtime(true) - $startTime;
    $totalTokens = $researchResult->getTokenUsage()['total_tokens'] +
                   $analysisResult->getTokenUsage()['total_tokens'] +
                   $reportResult->getTokenUsage()['total_tokens'];
    $totalIterations = $researchResult->getIterations() +
                      $analysisResult->getIterations() +
                      $reportResult->getIterations();
    
    // Display collaboration metrics
    echo str_repeat("=", 80) . "\n";
    echo "COLLABORATION METRICS\n";
    echo str_repeat("=", 80) . "\n";
    echo "Total Duration: " . number_format($totalDuration, 2) . "s\n";
    echo "  - Research: " . number_format($researchDuration, 2) . "s\n";
    echo "  - Analysis: " . number_format($analysisDuration, 2) . "s\n";
    echo "  - Writing: " . number_format($writingDuration, 2) . "s\n";
    echo "\nTotal Iterations: {$totalIterations}\n";
    echo "  - Researcher: " . $researchResult->getIterations() . "\n";
    echo "  - Analyzer: " . $analysisResult->getIterations() . "\n";
    echo "  - Writer: " . $reportResult->getIterations() . "\n";
    echo "\nTotal Tokens: {$totalTokens}\n";
    echo "  - Researcher: " . $researchResult->getTokenUsage()['total_tokens'] . "\n";
    echo "  - Analyzer: " . $analysisResult->getTokenUsage()['total_tokens'] . "\n";
    echo "  - Writer: " . $reportResult->getTokenUsage()['total_tokens'] . "\n";
    
    // Display shared memory contents
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "SHARED MEMORY CONTENTS\n";
    echo str_repeat("=", 80) . "\n";
    
    $memoryKeys = ['research_findings', 'analysis_results'];
    foreach ($memoryKeys as $key) {
        $data = $sharedMemory->get($key);
        if ($data !== null) {
            echo "\nKey: {$key}\n";
            echo "Length: " . strlen($data) . " characters\n";
            echo "Preview: " . substr($data, 0, 150) . "...\n";
        }
    }
    
    echo "\n";
    
} catch (\Exception $e) {
    echo "\n[ERROR] Multi-agent workflow failed: {$e->getMessage()}\n";
    echo "Stack trace:\n{$e->getTraceAsString()}\n";
}

// ============================================================================
// Key Takeaways
// ============================================================================

echo "\n" . str_repeat("=", 80) . "\n";
echo "KEY CONCEPTS DEMONSTRATED\n";
echo str_repeat("=", 80) . "\n";
echo "1. Agent Specialization: Each agent has a specific role and expertise\n";
echo "2. Shared Memory: Agents communicate through shared memory store\n";
echo "3. Tool-Based Collaboration: Agents use tools to read/write shared data\n";
echo "4. Sequential Handoff: Output of one agent becomes input for the next\n";
echo "5. Workflow Orchestration: Manual coordination of specialized agents\n";
echo "6. Metrics Tracking: Monitor iterations, tokens, and duration per agent\n";
echo "\n";

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                         Example Complete                                   ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n";
