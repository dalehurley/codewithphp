<?php

declare(strict_types=1);

/**
 * Exercise 4: CSV Export
 * 
 * Write a function that converts user data to CSV format:
 * 
 * Requirements:
 * - exportUsersToCSV() function with users array and filename
 * - Include headers: id, name, email, active
 * - Use proper CSV escaping for commas and quotes
 * - Test with sample user data
 */

/**
 * Export users to CSV file
 */
function exportUsersToCSV(array $users, string $filename): bool
{
    $handle = fopen($filename, 'w');
    if ($handle === false) {
        echo "Error: Failed to create CSV file" . PHP_EOL;
        return false;
    }

    // Write headers
    $headers = ['id', 'name', 'email', 'active'];
    fputcsv($handle, $headers);

    // Write user data
    $rowCount = 0;
    foreach ($users as $user) {
        $row = [
            $user['id'] ?? '',
            $user['name'] ?? '',
            $user['email'] ?? '',
            $user['active'] ?? false ? 'Yes' : 'No'
        ];
        fputcsv($handle, $row);
        $rowCount++;
    }

    fclose($handle);
    echo "Exported {$rowCount} users to: {$filename}" . PHP_EOL;
    return true;
}

/**
 * Read and display CSV file contents
 */
function displayCSV(string $filename): void
{
    if (!file_exists($filename)) {
        echo "CSV file not found" . PHP_EOL;
        return;
    }

    $handle = fopen($filename, 'r');
    if ($handle === false) {
        echo "Error: Failed to read CSV file" . PHP_EOL;
        return;
    }

    echo "CSV Contents:" . PHP_EOL;
    echo str_repeat('-', 70) . PHP_EOL;

    $rowNum = 0;
    while (($data = fgetcsv($handle)) !== false) {
        if ($rowNum === 0) {
            // Header row
            echo implode(' | ', array_map(fn($h) => str_pad($h, 10), $data)) . PHP_EOL;
            echo str_repeat('-', 70) . PHP_EOL;
        } else {
            echo implode(' | ', array_map(fn($d) => str_pad($d, 10), $data)) . PHP_EOL;
        }
        $rowNum++;
    }

    fclose($handle);
    echo str_repeat('-', 70) . PHP_EOL;
}

/**
 * Import users from CSV file
 */
function importUsersFromCSV(string $filename): array
{
    if (!file_exists($filename)) {
        throw new RuntimeException("CSV file not found: {$filename}");
    }

    $handle = fopen($filename, 'r');
    if ($handle === false) {
        throw new RuntimeException("Failed to open CSV file");
    }

    $users = [];
    $headers = fgetcsv($handle); // Read headers

    while (($data = fgetcsv($handle)) !== false) {
        if (count($data) !== count($headers)) {
            continue; // Skip malformed rows
        }

        $user = array_combine($headers, $data);
        // Convert 'active' back to boolean
        if (isset($user['active'])) {
            $user['active'] = strtolower($user['active']) === 'yes';
        }
        $users[] = $user;
    }

    fclose($handle);
    echo "Imported " . count($users) . " users from: {$filename}" . PHP_EOL;
    return $users;
}

// Test the CSV export functionality
echo "=== CSV Export Demo ===" . PHP_EOL . PHP_EOL;

// Sample user data with special characters
$users = [
    [
        'id' => 1,
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'active' => true
    ],
    [
        'id' => 2,
        'name' => 'Jane Smith, Jr.',  // Name with comma
        'email' => 'jane@example.com',
        'active' => true
    ],
    [
        'id' => 3,
        'name' => 'Bob "Bobby" Wilson',  // Name with quotes
        'email' => 'bob@example.com',
        'active' => false
    ],
    [
        'id' => 4,
        'name' => 'Alice O\'Brien',  // Name with apostrophe
        'email' => 'alice@example.com',
        'active' => true
    ],
    [
        'id' => 5,
        'name' => 'Charlie Brown',
        'email' => 'charlie@example.com',
        'active' => false
    ]
];

echo "--- Original user data ---" . PHP_EOL;
foreach ($users as $user) {
    echo "ID {$user['id']}: {$user['name']} ({$user['email']}) - " .
        ($user['active'] ? 'Active' : 'Inactive') . PHP_EOL;
}

echo PHP_EOL . "--- Exporting to CSV ---" . PHP_EOL;
$csvFile = __DIR__ . '/data/users.csv';
$dataDir = dirname($csvFile);
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

exportUsersToCSV($users, $csvFile);

echo PHP_EOL . "--- Reading CSV file ---" . PHP_EOL;
displayCSV($csvFile);

echo PHP_EOL . "--- Importing from CSV ---" . PHP_EOL;
$importedUsers = importUsersFromCSV($csvFile);
echo "Imported users:" . PHP_EOL;
foreach ($importedUsers as $user) {
    echo "ID {$user['id']}: {$user['name']} ({$user['email']}) - " .
        ($user['active'] ? 'Active' : 'Inactive') . PHP_EOL;
}

echo PHP_EOL . "--- CSV file size ---" . PHP_EOL;
$fileSize = filesize($csvFile);
echo "File size: {$fileSize} bytes" . PHP_EOL;
