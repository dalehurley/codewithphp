<?php

declare(strict_types=1);

require_once __DIR__ . '/UnitPrice.php';

/** @param list<int> $quantities */
function totalQuantities(UnitPrice $price, array $quantities): int
{
    $total = 0;

    foreach ($quantities as $quantity) {
        $total += $price->forQuantity($quantity);
    }

    return $total;
}
