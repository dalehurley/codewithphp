<?php

declare(strict_types=1);

require_once __DIR__ . '/total-quantities.php';

/** @return list<int>|null */
function parseQuantityList(mixed $rawQuantities): ?array
{
    if (!is_array($rawQuantities) || $rawQuantities === []) {
        return null;
    }

    $quantities = [];
    foreach ($rawQuantities as $rawQuantity) {
        if (!is_string($rawQuantity)) {
            return null;
        }

        $quantity = filter_var($rawQuantity, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0, 'max_range' => 1000],
        ]);
        if ($quantity === false) {
            return null;
        }

        $quantities[] = $quantity;
    }

    return $quantities;
}

// Simulate the field a web handler would read as $_POST['quantities'] ?? null.
$rawQuantities = ['3', '0'];
$quantities = parseQuantityList($rawQuantities);

if ($quantities === null) {
    fwrite(STDERR, 'Submit at least one whole-number quantity from 0 to 1000.' . PHP_EOL);
    exit(1);
}

echo totalQuantities(new UnitPrice(1250), $quantities), PHP_EOL;
