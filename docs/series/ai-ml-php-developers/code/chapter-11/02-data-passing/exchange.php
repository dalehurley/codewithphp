<?php

declare(strict_types=1);

/**
 * Demonstrates passing complex, nested data structures between PHP and Python.
 * 
 * Use cases:
 * - Feature extraction from user data
 * - Batch processing multiple records
 * - Preprocessing before ML prediction
 */

function callPythonProcessor(array $data): array
{
    $json = json_encode($data);
    $escaped = escapeshellarg($json);
    $output = shell_exec("python3 " . __DIR__ . "/process.py {$escaped}");

    if ($output === null) {
        throw new RuntimeException('Python script execution failed');
    }

    $result = json_decode($output, true);

    if (isset($result['error'])) {
        throw new RuntimeException("Python error: {$result['error']}");
    }

    return $result;
}

// Example 1: Process a single user
echo "=== Example 1: Single User Processing ===\n\n";

$user = [
    'id' => 12345,
    'name' => 'Alice Johnson',
    'age' => 34,
    'purchases' => [
        ['product' => 'Laptop', 'amount' => 1200.00, 'date' => '2024-01-15'],
        ['product' => 'Mouse', 'amount' => 25.00, 'date' => '2024-01-15'],
        ['product' => 'Keyboard', 'amount' => 80.00, 'date' => '2024-02-03'],
        ['product' => 'Monitor', 'amount' => 350.00, 'date' => '2024-03-10'],
    ]
];

try {
    $result = callPythonProcessor($user);

    echo "User: {$result['name']}\n";
    echo "Segment: {$result['segment']}\n";
    echo "Total Purchases: {$result['metrics']['total_purchases']}\n";
    echo "Total Spent: \${$result['metrics']['total_spent']}\n";
    echo "Average Purchase: \${$result['metrics']['avg_purchase_value']}\n";
    echo "Recommendations:\n";
    foreach ($result['recommendations'] as $rec) {
        echo "  - {$rec}\n";
    }
} catch (RuntimeException $e) {
    echo "Error: {$e->getMessage()}\n";
    exit(1);
}

echo "\n=== Example 2: Batch Processing ===\n\n";

$users = [
    [
        'id' => 1,
        'name' => 'Bob Smith',
        'purchases' => [
            ['amount' => 50],
            ['amount' => 75],
        ]
    ],
    [
        'id' => 2,
        'name' => 'Carol White',
        'purchases' => [
            ['amount' => 600],
            ['amount' => 450],
            ['amount' => 300],
        ]
    ],
];

try {
    $results = callPythonProcessor($users);

    foreach ($results as $result) {
        echo "{$result['name']}: {$result['segment']} segment ";
        echo "(\${$result['metrics']['total_spent']} total)\n";
    }

    echo "\n✅ Complex data exchange working!\n";
} catch (RuntimeException $e) {
    echo "Error: {$e->getMessage()}\n";
    exit(1);
}


