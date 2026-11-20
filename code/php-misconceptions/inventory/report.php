<?php

declare(strict_types=1);

/** @return list<array{sku: string, stock: int}> */
function inventoryRows(): array
{
    return [
        ['sku' => 'PHP-101', 'stock' => 12],
        ['sku' => 'PHP-102', 'stock' => 0],
    ];
}

/**
 * @param list<array{sku: string, stock: int}> $items
 * @return array{items: list<array{sku: string, stock: int}>}
 */
function inventoryReport(array $items, bool $inStockOnly): array
{
    if ($inStockOnly) {
        $items = array_values(array_filter(
            $items,
            static fn (array $item): bool => $item['stock'] > 0,
        ));
    }

    return ['items' => $items];
}
