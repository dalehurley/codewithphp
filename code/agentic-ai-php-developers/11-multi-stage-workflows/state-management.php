#!/usr/bin/env php
<?php
/**
 * State Management and Transitions Example
 *
 * Demonstrates StateManager for tracking workflow state across
 * stages with goals, checkpointing, and recovery.
 *
 * Part of: Agentic AI for PHP Developers
 * Chapter 11: Multi-Stage Workflows and Agent Graphs
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use ClaudeAgents\Chains\LLMChain;
use ClaudeAgents\Prompts\PromptTemplate;
use ClaudeAgents\State\AgentState;
use ClaudeAgents\State\Goal;
use ClaudeAgents\State\GoalStatus;
use ClaudeAgents\State\StateManager;
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
echo "║              State Management and Transitions Example                      ║\n";
echo "║              Track workflow goals, checkpoint, and recover                 ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

// ============================================================================
// Initialize State Manager
// ============================================================================

$stateFile = __DIR__ . '/workflow_state.json';

echo "Initializing StateManager...\n";
echo "State file: {$stateFile}\n\n";

$stateManager = new StateManager(
    stateFile: $stateFile,
    options: [
        'atomic_writes' => true,
        'backup_retention' => 5,
    ]
);

// ============================================================================
// Create or Load Workflow State
// ============================================================================

echo "Loading or creating workflow state...\n";

$state = $stateManager->load();

if ($state === null) {
    echo "No existing state found. Creating new workflow state.\n";
    $state = AgentState::create(
        stateId: 'workflow-' . uniqid(),
        agentType: 'multi-stage-workflow'
    );
} else {
    echo "Loaded existing state: {$state->getStateId()}\n";
    
    // Ask if user wants to continue or restart
    echo "\nExisting workflow state found. Options:\n";
    echo "1. Continue from last checkpoint\n";
    echo "2. Start fresh\n";
    echo "Choice (1 or 2): ";
    
    $choice = trim((string) fgets(STDIN));
    
    if ($choice === '2') {
        echo "Starting fresh...\n";
        $state = AgentState::create(
            stateId: 'workflow-' . uniqid(),
            agentType: 'multi-stage-workflow'
        );
    } else {
        echo "Continuing from checkpoint...\n";
    }
}

// ============================================================================
// Define Workflow Goals
// ============================================================================

echo "\nDefining workflow goals...\n";

$goals = [
    new Goal(
        id: 'extract-entities',
        description: 'Extract named entities from input text',
        status: GoalStatus::PENDING,
        priority: 1
    ),
    new Goal(
        id: 'analyze-sentiment',
        description: 'Analyze sentiment of extracted entities',
        status: GoalStatus::PENDING,
        priority: 2
    ),
    new Goal(
        id: 'generate-summary',
        description: 'Generate comprehensive summary',
        status: GoalStatus::PENDING,
        priority: 3
    ),
    new Goal(
        id: 'generate-report',
        description: 'Generate final analysis report',
        status: GoalStatus::PENDING,
        priority: 4
    ),
];

foreach ($goals as $goal) {
    $existingGoal = null;
    foreach ($state->getGoals() as $g) {
        if ($g->id === $goal->id) {
            $existingGoal = $g;
            break;
        }
    }
    
    if ($existingGoal === null) {
        $state->addGoal($goal);
        echo "  ✓ Added goal: {$goal->description}\n";
    } else {
        echo "  → Existing goal: {$goal->description} (status: {$existingGoal->status->value})\n";
    }
}

// Save initial state
$stateManager->save($state);

// ============================================================================
// Prepare Input Text
// ============================================================================

$inputText = <<<TEXT
Microsoft announced a groundbreaking partnership with OpenAI today, valued at $10 billion.
CEO Satya Nadella expressed excitement about integrating AI capabilities into Azure services.
The deal, finalized in Redmond, Washington, represents Microsoft's largest AI investment to date.
Industry experts predict this will accelerate enterprise AI adoption significantly.
Competitors Google and Amazon are expected to announce similar initiatives soon.
TEXT;

echo "\n" . str_repeat("=", 80) . "\n";
echo "INPUT TEXT\n";
echo str_repeat("=", 80) . "\n";
echo $inputText . "\n";
echo str_repeat("=", 80) . "\n";

// ============================================================================
// Build Processing Chains
// ============================================================================

$extractionChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Extract all named entities (people, organizations, places) from this text:\n\n{text}'
    ))
    ->withMaxTokens(400);

$sentimentChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Analyze the sentiment based on these entities:\n\n{entities}\n\n' .
        'Provide sentiment analysis (positive/negative/neutral) with reasoning.'
    ))
    ->withMaxTokens(400);

$summaryChain = LLMChain::create($client)
    ->withPromptTemplate(PromptTemplate::create(
        'Generate a comprehensive 3-sentence summary based on:\n' .
        'Entities: {entities}\n' .
        'Sentiment: {sentiment}'
    ))
    ->withMaxTokens(400);

// ============================================================================
// Execute Workflow with State Tracking
// ============================================================================

echo "\n" . str_repeat("=", 80) . "\n";
echo "EXECUTING WORKFLOW WITH STATE TRACKING\n";
echo str_repeat("=", 80) . "\n\n";

try {
    // Stage 1: Extract Entities
    $goal1 = 'extract-entities';
    $goal1Status = null;
    foreach ($state->getGoals() as $g) {
        if ($g->id === $goal1) {
            $goal1Status = $g->status;
            break;
        }
    }
    
    if ($goal1Status !== GoalStatus::COMPLETED) {
        echo "[STAGE 1] Extracting entities...\n";
        $state->updateGoal($goal1, GoalStatus::IN_PROGRESS);
        $stateManager->save($state);
        
        $extractionResult = $extractionChain->invoke(['text' => $inputText]);
        $entities = $extractionResult['result'] ?? '';
        
        $state->updateGoal($goal1, GoalStatus::COMPLETED);
        $state->storeData('entities', $entities);
        $state->storeData('extraction_timestamp', date('Y-m-d H:i:s'));
        $stateManager->save($state);
        
        echo "✓ Entities extracted and stored\n";
        echo "  Preview: " . substr($entities, 0, 100) . "...\n\n";
    } else {
        echo "[STAGE 1] Already completed (loading from state)\n";
        $entities = $state->getData('entities') ?? '';
        echo "  Loaded: " . substr($entities, 0, 100) . "...\n\n";
    }
    
    // Create checkpoint before expensive operation
    echo "[CHECKPOINT] Creating backup before sentiment analysis...\n";
    $stateManager->createBackup();
    echo "✓ Checkpoint created\n\n";
    
    // Stage 2: Analyze Sentiment
    $goal2 = 'analyze-sentiment';
    $goal2Status = null;
    foreach ($state->getGoals() as $g) {
        if ($g->id === $goal2) {
            $goal2Status = $g->status;
            break;
        }
    }
    
    if ($goal2Status !== GoalStatus::COMPLETED) {
        echo "[STAGE 2] Analyzing sentiment...\n";
        $state->updateGoal($goal2, GoalStatus::IN_PROGRESS);
        $stateManager->save($state);
        
        $sentimentResult = $sentimentChain->invoke(['entities' => $entities]);
        $sentiment = $sentimentResult['result'] ?? '';
        
        $state->updateGoal($goal2, GoalStatus::COMPLETED);
        $state->storeData('sentiment', $sentiment);
        $state->storeData('sentiment_timestamp', date('Y-m-d H:i:s'));
        $stateManager->save($state);
        
        echo "✓ Sentiment analyzed and stored\n";
        echo "  Preview: " . substr($sentiment, 0, 100) . "...\n\n";
    } else {
        echo "[STAGE 2] Already completed (loading from state)\n";
        $sentiment = $state->getData('sentiment') ?? '';
        echo "  Loaded: " . substr($sentiment, 0, 100) . "...\n\n";
    }
    
    // Stage 3: Generate Summary
    $goal3 = 'generate-summary';
    $goal3Status = null;
    foreach ($state->getGoals() as $g) {
        if ($g->id === $goal3) {
            $goal3Status = $g->status;
            break;
        }
    }
    
    if ($goal3Status !== GoalStatus::COMPLETED) {
        echo "[STAGE 3] Generating summary...\n";
        $state->updateGoal($goal3, GoalStatus::IN_PROGRESS);
        $stateManager->save($state);
        
        $summaryResult = $summaryChain->invoke([
            'entities' => $entities,
            'sentiment' => $sentiment,
        ]);
        $summary = $summaryResult['result'] ?? '';
        
        $state->updateGoal($goal3, GoalStatus::COMPLETED);
        $state->storeData('summary', $summary);
        $state->storeData('summary_timestamp', date('Y-m-d H:i:s'));
        $stateManager->save($state);
        
        echo "✓ Summary generated and stored\n";
        echo "  Preview: " . substr($summary, 0, 100) . "...\n\n";
    } else {
        echo "[STAGE 3] Already completed (loading from state)\n";
        $summary = $state->getData('summary') ?? '';
        echo "  Loaded: " . substr($summary, 0, 100) . "...\n\n";
    }
    
    // Stage 4: Generate Final Report
    $goal4 = 'generate-report';
    echo "[STAGE 4] Generating final report...\n";
    $state->updateGoal($goal4, GoalStatus::IN_PROGRESS);
    $stateManager->save($state);
    
    $report = [
        'entities' => $entities,
        'sentiment' => $sentiment,
        'summary' => $summary,
        'workflow_id' => $state->getStateId(),
        'completed_at' => date('Y-m-d H:i:s'),
        'stages_completed' => 4,
    ];
    
    $state->updateGoal($goal4, GoalStatus::COMPLETED);
    $state->storeData('final_report', $report);
    $stateManager->save($state);
    
    echo "✓ Final report generated\n\n";
    
    // Display final results
    echo str_repeat("=", 80) . "\n";
    echo "FINAL REPORT\n";
    echo str_repeat("=", 80) . "\n";
    echo json_encode($report, JSON_PRETTY_PRINT) . "\n";
    echo str_repeat("=", 80) . "\n\n";
    
} catch (\Exception $e) {
    echo "\n[ERROR] Workflow failed: {$e->getMessage()}\n";
    echo "Attempting recovery from last checkpoint...\n";
    
    $restoredState = $stateManager->restoreLatest();
    if ($restoredState !== null) {
        echo "✓ State restored from backup\n";
        echo "You can re-run this script to continue from the checkpoint\n";
    } else {
        echo "✗ No backup available for recovery\n";
    }
}

// ============================================================================
// Display State Information
// ============================================================================

echo "\n" . str_repeat("=", 80) . "\n";
echo "WORKFLOW STATE SUMMARY\n";
echo str_repeat("=", 80) . "\n";

$allGoals = $state->getGoals();
$completed = array_filter($allGoals, fn($g) => $g->status === GoalStatus::COMPLETED);
$inProgress = array_filter($allGoals, fn($g) => $g->status === GoalStatus::IN_PROGRESS);
$pending = array_filter($allGoals, fn($g) => $g->status === GoalStatus::PENDING);

echo "Workflow ID: {$state->getStateId()}\n";
echo "Total Goals: " . count($allGoals) . "\n";
echo "  - Completed: " . count($completed) . "\n";
echo "  - In Progress: " . count($inProgress) . "\n";
echo "  - Pending: " . count($pending) . "\n\n";

echo "Goal Details:\n";
foreach ($allGoals as $goal) {
    $statusIcon = match($goal->status) {
        GoalStatus::COMPLETED => '✓',
        GoalStatus::IN_PROGRESS => '→',
        GoalStatus::PENDING => '○',
        GoalStatus::FAILED => '✗',
    };
    echo "  {$statusIcon} {$goal->description} ({$goal->status->value})\n";
}

// List available backups
echo "\n" . str_repeat("=", 80) . "\n";
echo "AVAILABLE BACKUPS\n";
echo str_repeat("=", 80) . "\n";

$backups = $stateManager->listBackups();
if (empty($backups)) {
    echo "No backups available\n";
} else {
    echo "Found " . count($backups) . " backup(s):\n";
    foreach ($backups as $index => $backup) {
        $filename = basename($backup);
        $modified = date('Y-m-d H:i:s', filemtime($backup));
        echo "  " . ($index + 1) . ". {$filename} (modified: {$modified})\n";
    }
}

echo "\n";

// ============================================================================
// Key Takeaways
// ============================================================================

echo str_repeat("=", 80) . "\n";
echo "KEY CONCEPTS DEMONSTRATED\n";
echo str_repeat("=", 80) . "\n";
echo "1. State Persistence: Workflow state saved to JSON file\n";
echo "2. Goal Tracking: Monitor progress through defined goals\n";
echo "3. Checkpointing: Create backups before expensive operations\n";
echo "4. Recovery: Restore from backups on failure\n";
echo "5. Resume Capability: Continue interrupted workflows\n";
echo "\n";

echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                         Example Complete                                   ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n";
