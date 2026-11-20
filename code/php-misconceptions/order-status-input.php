<?php

declare(strict_types=1);

require __DIR__ . '/Order.php';

$status = OrderStatus::tryFrom($argv[1] ?? '');

if ($status === null) {
    fwrite(STDERR, 'Unknown order status.' . PHP_EOL);
    exit(1);
}

echo (new Order(12, $status))->label, PHP_EOL;
