<?php

declare(strict_types=1);

require __DIR__ . '/UnitPrice.php';

$price = new UnitPrice(1250);
echo $price->forQuantity(3), PHP_EOL;

try {
    // The string simulates a value received from a form or environment variable.
    $input = '3';
    echo $price->forQuantity($input), PHP_EOL;
} catch (TypeError) {
    echo 'The quantity call raised a TypeError.', PHP_EOL;
}
