<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    require __DIR__ . '/inventory/public/inventory.php';

    return;
}

require __DIR__ . '/inventory/report.php';

$options = array_slice($argv, 1);

if ($options !== [] && $options !== ['--in-stock']) {
    fwrite(STDERR, 'Usage: php inventory.php [--in-stock]' . PHP_EOL);
    exit(1);
}

$report = inventoryReport(inventoryRows(), $options === ['--in-stock']);
echo json_encode($report, JSON_THROW_ON_ERROR), PHP_EOL;
