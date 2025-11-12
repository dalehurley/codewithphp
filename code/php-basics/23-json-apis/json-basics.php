<?php

declare(strict_types=1);

/**
 * JSON Basics: Encoding and Decoding
 * 
 * Demonstrates JSON encoding, decoding, pretty printing, and error handling.
 */

// Sample data: Product catalog
$products = [
    [
        'id' => 1,
        'name' => 'Laptop Pro 15"',
        'price' => 1299.99,
        'in_stock' => true,
        'tags' => ['electronics', 'computers', 'featured']
    ],
    [
        'id' => 2,
        'name' => 'Wireless Mouse',
        'price' => 29.99,
        'in_stock' => false,
        'tags' => ['electronics', 'accessories']
    ],
    [
        'id' => 3,
        'name' => 'USB-C Cable 2m',
        'price' => 19.99,
        'in_stock' => true,
        'tags' => ['accessories', 'cables']
    ]
];

echo "=== JSON Encoding Examples ===" . PHP_EOL . PHP_EOL;

// 1. Compact JSON (minimal spacing)
$compactJson = json_encode($products);
echo "Compact JSON:" . PHP_EOL;
echo $compactJson . PHP_EOL . PHP_EOL;

// 2. Pretty-printed JSON (human-readable)
$prettyJson = json_encode($products, JSON_PRETTY_PRINT);
echo "Pretty JSON:" . PHP_EOL;
echo $prettyJson . PHP_EOL . PHP_EOL;

// 3. JSON with unescaped unicode and slashes
$user = [
    'name' => 'José García',
    'website' => 'https://example.com/path',
    'emoji' => '🚀'
];

$escapedJson = json_encode($user);
echo "Escaped JSON:" . PHP_EOL;
echo $escapedJson . PHP_EOL . PHP_EOL;

$unescapedJson = json_encode($user, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
echo "Unescaped JSON:" . PHP_EOL;
echo $unescapedJson . PHP_EOL . PHP_EOL;

// 4. Decoding JSON to associative array
echo "=== JSON Decoding Examples ===" . PHP_EOL . PHP_EOL;

$decoded = json_decode($prettyJson, true); // true = associative array
echo "Decoded (array):" . PHP_EOL;
echo "First product: " . $decoded[0]['name'] . PHP_EOL;
echo "Price: $" . $decoded[0]['price'] . PHP_EOL . PHP_EOL;

// 5. Decoding JSON to objects
$decodedObjects = json_decode($prettyJson); // false/omitted = objects
echo "Decoded (objects):" . PHP_EOL;
echo "First product: " . $decodedObjects[0]->name . PHP_EOL;
echo "In stock: " . ($decodedObjects[0]->in_stock ? 'Yes' : 'No') . PHP_EOL . PHP_EOL;

// 6. Error handling
echo "=== Error Handling ===" . PHP_EOL . PHP_EOL;

// Encoding error: Invalid UTF-8
$invalidData = [
    'name' => "Invalid \xB1\x31 encoding"
];

$result = json_encode($invalidData);

if ($result === false) {
    echo "❌ Encoding failed: " . json_last_error_msg() . PHP_EOL;
} else {
    echo "✓ Encoding successful" . PHP_EOL;
}

// Decoding error: Malformed JSON
$malformedJson = '{"name": "Bob", "age": }'; // Missing value

$result = json_decode($malformedJson, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ Decoding failed: " . json_last_error_msg() . PHP_EOL;
}

// Using exceptions (PHP 7.3+)
try {
    $data = json_decode($malformedJson, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
    echo "❌ JSON Exception: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL;

// 7. Nested data structures
$complexData = [
    'user' => [
        'id' => 42,
        'profile' => [
            'name' => 'Alice Johnson',
            'contact' => [
                'email' => 'alice@example.com',
                'phone' => '+1-555-0123'
            ]
        ]
    ],
    'orders' => [
        ['id' => 1, 'total' => 99.99],
        ['id' => 2, 'total' => 149.99]
    ]
];

echo "=== Complex Nested Structure ===" . PHP_EOL;
echo json_encode($complexData, JSON_PRETTY_PRINT) . PHP_EOL;

echo PHP_EOL . "✓ JSON basics demonstration complete!" . PHP_EOL;
