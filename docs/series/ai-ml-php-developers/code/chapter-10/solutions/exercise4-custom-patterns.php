<?php

declare(strict_types=1);

echo "Exercise 4: Custom Pattern Recognition\n";
echo str_repeat("=", 60) . "\n\n";

echo "NOTE: This exercise requires Rubix ML installation.\n";
echo "Please install Rubix ML first:\n";
echo "  cd ../chapter-02\n";
echo "  composer install\n\n";

echo "Then, implement your own 3x3 patterns for letters O, X, H\n";
echo "or create custom symbols of your choice.\n\n";

echo "Example patterns:\n\n";

$patterns = [
    'O' => [
        [1, 1, 1],
        [1, 0, 1],
        [1, 1, 1],
    ],
    'X' => [
        [1, 0, 1],
        [0, 1, 0],
        [1, 0, 1],
    ],
    'H' => [
        [1, 0, 1],
        [1, 1, 1],
        [1, 0, 1],
    ],
];

foreach ($patterns as $letter => $pattern) {
    echo "Pattern '{$letter}':\n";
    foreach ($pattern as $row) {
        echo "  " . implode(' ', array_map(fn($p) => $p ? '█' : '·', $row)) . "\n";
    }
    echo "\n";
}

echo "See 12-pattern-recognizer.php for implementation reference.\n";
