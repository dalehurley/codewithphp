<?php

// filename: stricter-value-errors.php

declare(strict_types=1);

if (PHP_VERSION_ID < 80600) {
    throw new RuntimeException('Run this example with PHP 8.6');
}

$examples = [
    'Invalid filter mode' => static fn () => array_filter([1], static fn (int $value): bool => $value > 0, -1),
    'NUL in file name' => static fn () => file_exists("report.csv\0ignored"),
    'Out-of-range delays' => static fn () => usleep(5_000_000_000),
];

foreach ($examples as $label => $run) {
    try {
        $run();
        echo "$label accepted\n";
    } catch (ValueError) {
        echo "$label rejected\n";
    }
}
