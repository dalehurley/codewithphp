<?php

declare(strict_types=1);

require __DIR__ . '/book-title.php';

$cases = [
    ['  My Book  ', 'My Book'],
    ["\tNext Book\n", 'Next Book'],
    ['  ', ''],
    ['Notes & Queries', 'Notes & Queries'],
];

foreach ($cases as [$input, $expected]) {
    $actual = normalizeBookTitle($input);
    if ($actual !== $expected) {
        fwrite(STDERR, sprintf(
            "Expected %s, got %s\n",
            var_export($expected, true),
            var_export($actual, true)
        ));
        exit(1);
    }
}

echo "4 checks passed.\n";
