<?php

// filename: json-encoding-performance.php

declare(strict_types=1);

$rows = [];
for ($index = 1; $index <= 2_000; $index++) {
    $rows[] = ['id' => $index, 'state' => 'ready', 'items' => [1, 2, 3]];
}

$json = json_encode($rows, JSON_THROW_ON_ERROR);
echo 'Decoded records: ', count(json_decode($json, true, flags: JSON_THROW_ON_ERROR)), PHP_EOL;
echo 'Compact bytes: ', strlen($json), PHP_EOL;
echo 'Pretty bytes: ', strlen(json_encode($rows, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)), PHP_EOL;

if (in_array('--time', $argv, true)) {
    $start = hrtime(true);
    for ($run = 0; $run < 100; $run++) {
        json_encode($rows, JSON_THROW_ON_ERROR);
    }
    printf("100 encodes: %.1f ms\n", (hrtime(true) - $start) / 1_000_000);
}
