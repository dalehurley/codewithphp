<?php

// filename: array-map-performance.php

declare(strict_types=1);

function formatPrice(float $price, float $factor = 1.2): string
{
    return sprintf('%.2f', $price * $factor);
}

$prices = ['tea' => 5.0, 'coffee' => 7.5];
$withCallable = array_map(formatPrice(...), $prices);
$withPartial = array_map(formatPrice(?, 1.2), $prices);

echo json_encode($withCallable, JSON_THROW_ON_ERROR), PHP_EOL;
echo 'Same prices: ', $withCallable === $withPartial ? 'yes' : 'no', PHP_EOL;
