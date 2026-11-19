<?php

declare(strict_types=1);

require __DIR__ . '/UnitPrice.php';

$price = new UnitPrice(1250);
echo $price->forQuantity(3), PHP_EOL;

try {
    // The string simulates a value received from a form or environment variable.
    $input = '3';
    $price->forQuantity($input);
} catch (TypeError) {
    echo 'The strict caller rejected the numeric string.', PHP_EOL;
}
