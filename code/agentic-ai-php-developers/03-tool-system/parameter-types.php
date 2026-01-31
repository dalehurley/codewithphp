<?php

declare(strict_types=1);

/**
 * Parameter Types Example
 * 
 * Demonstrates all parameter types supported by the tool system:
 * - String parameters (with enums)
 * - Number parameters (with min/max)
 * - Boolean parameters
 * - Array parameters (with item schemas)
 * - Object parameters (nested structures)
 */

// Load from the claude-php-agent project in the workspace
require_once __DIR__ . '/../../../../claude-php-agent/vendor/autoload.php';

use ClaudeAgents\Tools\Tool;
use ClaudeAgents\Tools\ToolResult;

echo "=== Parameter Types Example ===\n\n";

// Tool demonstrating all parameter types
$createTask = Tool::create('create_task')
    ->description('Create a task with various parameter types')
    
    // String parameter with enum
    ->stringParam(
        'title',
        'Task title',
        required: true
    )
    
    // String parameter with enum constraint
    ->stringParam(
        'priority',
        'Task priority level',
        required: true,
        enum: ['low', 'medium', 'high', 'urgent']
    )
    
    // Number parameter with range
    ->numberParam(
        'estimated_hours',
        'Estimated hours to complete',
        required: true,
        minimum: 0.5,
        maximum: 40.0
    )
    
    // Boolean parameter
    ->booleanParam(
        'is_recurring',
        'Is this a recurring task?',
        required: false
    )
    
    // Array parameter (simple array of strings)
    ->arrayParam(
        'tags',
        'List of tags for categorization',
        required: false
    )
    
    // Array parameter with object items
    ->arrayParam(
        'subtasks',
        'List of subtasks',
        required: false,
        items: [
            'type' => 'object',
            'properties' => [
                'title' => ['type' => 'string'],
                'completed' => ['type' => 'boolean'],
            ],
            'required' => ['title'],
        ]
    )
    
    // Object parameter (complex nested structure)
    ->parameter('assignee', 'object', 'Person assigned to this task', false, [
        'properties' => [
            'user_id' => ['type' => 'string'],
            'name' => ['type' => 'string'],
            'email' => ['type' => 'string'],
        ],
        'required' => ['user_id'],
    ])
    
    ->handler(function (array $input): ToolResult {
        // All parameters are validated by the framework before reaching here
        
        $task = [
            'id' => uniqid('task_'),
            'title' => $input['title'],
            'priority' => $input['priority'],
            'estimated_hours' => $input['estimated_hours'],
            'is_recurring' => $input['is_recurring'] ?? false,
            'tags' => $input['tags'] ?? [],
            'subtasks' => $input['subtasks'] ?? [],
            'assignee' => $input['assignee'] ?? null,
            'created_at' => date('c'),
            'status' => 'pending',
        ];
        
        // Calculate completeness
        $totalSubtasks = count($task['subtasks']);
        $completedSubtasks = array_reduce(
            $task['subtasks'],
            fn($count, $st) => $count + ($st['completed'] ?? false ? 1 : 0),
            0
        );
        
        $task['progress'] = $totalSubtasks > 0
            ? round(($completedSubtasks / $totalSubtasks) * 100, 1)
            : 0;
        
        return ToolResult::success($task);
    });

// Display schema
echo "=== Tool Schema ===\n";
print_r($createTask->getInputSchema());
echo "\n";

// Test 1: Minimum required parameters
echo "Test 1: Minimum required parameters\n";
$result1 = $createTask->execute([
    'title' => 'Review pull requests',
    'priority' => 'high',
    'estimated_hours' => 2.5,
]);
echo "Success: " . ($result1->isSuccess() ? 'Yes' : 'No') . "\n";
$data1 = json_decode($result1->getContent(), true);
echo "Task ID: " . $data1['id'] . "\n";
echo "Title: " . $data1['title'] . "\n";
echo "Priority: " . $data1['priority'] . "\n";
echo "Estimated Hours: " . $data1['estimated_hours'] . "\n";
echo "\n";

// Test 2: All parameters
echo "Test 2: All parameters including optional\n";
$result2 = $createTask->execute([
    'title' => 'Build new feature',
    'priority' => 'medium',
    'estimated_hours' => 16.0,
    'is_recurring' => false,
    'tags' => ['backend', 'api', 'php'],
    'subtasks' => [
        ['title' => 'Design API endpoints', 'completed' => true],
        ['title' => 'Implement handlers', 'completed' => false],
        ['title' => 'Write tests', 'completed' => false],
    ],
    'assignee' => [
        'user_id' => 'user_123',
        'name' => 'Alice Developer',
        'email' => 'alice@example.com',
    ],
]);
echo "Success: " . ($result2->isSuccess() ? 'Yes' : 'No') . "\n";
$data2 = json_decode($result2->getContent(), true);
echo "Task ID: " . $data2['id'] . "\n";
echo "Title: " . $data2['title'] . "\n";
echo "Tags: " . implode(', ', $data2['tags']) . "\n";
echo "Subtasks: " . count($data2['subtasks']) . "\n";
echo "Progress: " . $data2['progress'] . "%\n";
echo "Assignee: " . $data2['assignee']['name'] . " (" . $data2['assignee']['email'] . ")\n";
echo "\n";

// Test 3: Boolean parameter
echo "Test 3: Recurring task (boolean = true)\n";
$result3 = $createTask->execute([
    'title' => 'Weekly team meeting',
    'priority' => 'medium',
    'estimated_hours' => 1.0,
    'is_recurring' => true,
    'tags' => ['meeting', 'recurring'],
]);
echo "Success: " . ($result3->isSuccess() ? 'Yes' : 'No') . "\n";
$data3 = json_decode($result3->getContent(), true);
echo "Is Recurring: " . ($data3['is_recurring'] ? 'Yes' : 'No') . "\n";
echo "\n";

// Test 4: Array with complex objects
echo "Test 4: Task with multiple subtasks\n";
$result4 = $createTask->execute([
    'title' => 'Release v2.0',
    'priority' => 'urgent',
    'estimated_hours' => 40.0,
    'subtasks' => [
        ['title' => 'Code freeze', 'completed' => true],
        ['title' => 'QA testing', 'completed' => true],
        ['title' => 'Security audit', 'completed' => true],
        ['title' => 'Deploy to production', 'completed' => false],
        ['title' => 'Monitor metrics', 'completed' => false],
    ],
]);
echo "Success: " . ($result4->isSuccess() ? 'Yes' : 'No') . "\n";
$data4 = json_decode($result4->getContent(), true);
echo "Subtasks:\n";
foreach ($data4['subtasks'] as $subtask) {
    $status = $subtask['completed'] ? '✓' : '○';
    echo "  {$status} {$subtask['title']}\n";
}
echo "Progress: " . $data4['progress'] . "%\n";
echo "\n";

echo "✅ Parameter types example complete!\n";
echo "\nKey Takeaways:\n";
echo "- String params can have enum constraints\n";
echo "- Number params support min/max ranges\n";
echo "- Boolean params are optional by default\n";
echo "- Array params can contain complex objects\n";
echo "- Object params support nested structures\n";
echo "- Framework validates all types automatically\n";
