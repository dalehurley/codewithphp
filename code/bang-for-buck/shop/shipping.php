<?php

declare(strict_types=1);

$product = filter_input(INPUT_GET, 'product', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

header('Content-Type: application/json');
if (!$product) {
    http_response_code(422);
    echo '{"error":"A positive product ID is required"}';
    return;
}

$extraMs = filter_var(getenv('SHOP_SHIPPING_EXTRA_MS'), FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 0, 'max_range' => 500],
]);
usleep((12 + ($product % 5) * 6 + (int) $extraMs) * 1000);
echo json_encode(['shipping_cents' => 399 + ($product % 5) * 50], JSON_THROW_ON_ERROR);
