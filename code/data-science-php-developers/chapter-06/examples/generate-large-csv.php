<?php

# filename: examples/generate-large-csv.php

declare(strict_types=1);

// Create data directory if it doesn't exist
$dataDir = __DIR__ . '/../data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

$filename = $dataDir . '/large_dataset.csv';
$rows = 100000; // 100k rows

echo "Generating large CSV file with {$rows} rows...\n";

$handle = fopen($filename, 'w');

if ($handle === false) {
    die("Failed to create file: {$filename}\n");
}

// Write header
fputcsv($handle, ['id', 'name', 'email', 'age', 'salary', 'department']);

// Write rows
for ($i = 1; $i <= $rows; $i++) {
    fputcsv($handle, [
        $i,
        'Employee ' . $i,
        "employee{$i}@company.com",
        rand(22, 65),
        rand(30000, 150000),
        ['Engineering', 'Sales', 'Marketing', 'HR', 'Finance'][rand(0, 4)],
    ]);
    
    if ($i % 10000 === 0) {
        echo "  Written {$i} rows...\n";
    }
}

fclose($handle);

$size = filesize($filename);
echo "✓ Generated " . round($size / 1024 / 1024, 2) . " MB file\n";
echo "✓ File: {$filename}\n";
