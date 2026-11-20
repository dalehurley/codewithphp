<?php

declare(strict_types=1);

return [
    'categories' => 20,
    'products_per_category' => 250,
    'catalogue_benchmark' => [
        'category' => 21,
        'first_id' => 5001,
        'size' => 25_000,
        'description_bytes' => 4096,
    ],
    'catalogue_cache_store' => env('SHOP_CATALOGUE_CACHE_STORE', 'redis'),
    'shipping_url' => env('SHOP_SHIPPING_URL', 'http://shipping:8081'),
];
