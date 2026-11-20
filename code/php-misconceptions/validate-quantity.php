<?php

declare(strict_types=1);

require __DIR__ . '/UnitPrice.php';

$rawQuantity = $argv[1] ?? '';
$quantity = filter_var($rawQuantity, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 0, 'max_range' => 1000],
]);

if ($quantity === false) {
    fwrite(STDERR, 'Enter a whole-number quantity from 0 to 1000.' . PHP_EOL);
    exit(1);
}

echo (new UnitPrice(1250))->forQuantity($quantity), PHP_EOL;
