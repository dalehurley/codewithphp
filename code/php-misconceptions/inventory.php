<?php

declare(strict_types=1);

$items = [
    ['sku' => 'PHP-101', 'stock' => 12],
    ['sku' => 'PHP-102', 'stock' => 0],
];

if (PHP_SAPI !== 'cli') {
    header('Content-Type: application/json; charset=utf-8');
}

echo json_encode(['items' => $items], JSON_THROW_ON_ERROR), PHP_EOL;
