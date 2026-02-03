#!/usr/bin/env php
<?php
require_once __DIR__ . '/../00-environment-setup/vendor/autoload.php';

use ClaudeAgents\Agents\HierarchicalAgent;
use ClaudeAgents\Agents\WorkerAgent;
use ClaudePhp\ClaudePhp;

$apiKey = getenv('ANTHROPIC_API_KEY');
$client = new ClaudePhp(apiKey: $apiKey);

echo "Creating workers...\n";

$mathWorker = new WorkerAgent($client, [
    'name' => 'math_expert',
    'specialty' => 'mathematical calculations',
]);

$writerWorker = new WorkerAgent($client, [
    'name' => 'writer_expert',
    'specialty' => 'clear writing',
]);

echo "Creating master...\n";

// Create a custom extended class to debug
class DebugHierarchicalAgent extends HierarchicalAgent
{
    protected function parseSubtasks(string $text): array
    {
        echo "\n=== PARSING SUBTASKS ===\n";
        echo "Text length: " . strlen($text) . "\n";
        echo "First 500 chars:\n" . substr($text, 0, 500) . "\n";
        echo "========================\n\n";
        
        $lines = explode("\n", $text);
        $subtasks = [];
        $current = null;

        foreach ($lines as $line) {
            $line = trim($line);

            if (preg_match('/^Agent:\s*(.+)$/i', $line, $matches)) {
                if ($current !== null && isset($current['task'])) {
                    $subtasks[] = $current;
                }
                $current = ['agent' => trim($matches[1]), 'task' => ''];
                echo "Found agent: " . trim($matches[1]) . "\n";
            } elseif (preg_match('/^Subtask:\s*(.+)$/i', $line, $matches)) {
                if ($current !== null) {
                    $current['task'] = trim($matches[1]);
                    echo "Found subtask: " . trim($matches[1]) . "\n";
                }
            }
        }

        if ($current !== null && isset($current['task']) && $current['task'] !== '') {
            $subtasks[] = $current;
        }

        echo "\nParsed " . count($subtasks) . " subtasks\n";
        
        return $subtasks;
    }
}

$master = new DebugHierarchicalAgent($client, [
    'name' => 'master',
]);

$master->registerWorker('math_expert', $mathWorker);
$master->registerWorker('writer_expert', $writerWorker);

echo "Running task...\n\n";

$result = $master->run('Calculate average of 45, 67, 89, 123 and explain what average means');

if ($result->isSuccess()) {
    echo "\n✅ SUCCESS\n";
    echo $result->getAnswer() . "\n";
} else {
    echo "\n❌ FAILED: " . $result->getError() . "\n";
}
