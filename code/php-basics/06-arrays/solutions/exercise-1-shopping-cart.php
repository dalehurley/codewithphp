<?php

declare(strict_types=1);

/**
 * Exercise 1: Shopping Cart System
 * 
 * Create a shopping cart using arrays that can:
 * - Add items with name, price, and quantity
 * - Calculate total cost
 * - Apply a discount
 * - Display cart contents
 */

// Shopping cart array
$cart = [];

/**
 * Add item to cart
 */
function addToCart(array &$cart, string $name, float $price, int $quantity = 1): void
{
    $cart[] = [
        'name' => $name,
        'price' => $price,
        'quantity' => $quantity,
        'subtotal' => $price * $quantity
    ];
}

/**
 * Calculate cart total
 */
function getCartTotal(array $cart): float
{
    return array_sum(array_column($cart, 'subtotal'));
}

/**
 * Apply discount percentage
 */
function applyDiscount(float $total, float $discountPercent): float
{
    return $total * (1 - $discountPercent / 100);
}

/**
 * Display cart contents
 */
function displayCart(array $cart): void
{
    echo "=== Shopping Cart ===" . PHP_EOL;
    echo str_repeat("-", 60) . PHP_EOL;
    echo str_pad("Item", 20) . str_pad("Price", 12) . str_pad("Qty", 8) . "Subtotal" . PHP_EOL;
    echo str_repeat("-", 60) . PHP_EOL;

    foreach ($cart as $item) {
        echo str_pad($item['name'], 20);
        echo str_pad("$" . number_format($item['price'], 2), 12);
        echo str_pad($item['quantity'], 8);
        echo "$" . number_format($item['subtotal'], 2) . PHP_EOL;
    }

    echo str_repeat("-", 60) . PHP_EOL;
}

// Test the cart
addToCart($cart, "Laptop", 999.99, 1);
addToCart($cart, "Mouse", 29.99, 2);
addToCart($cart, "Keyboard", 79.99, 1);
addToCart($cart, "USB Cable", 9.99, 3);

displayCart($cart);

$total = getCartTotal($cart);
echo "Total: $" . number_format($total, 2) . PHP_EOL;

// Apply 10% discount
$discountedTotal = applyDiscount($total, 10);
echo "After 10% discount: $" . number_format($discountedTotal, 2) . PHP_EOL;

// Count total items
$itemCount = array_sum(array_column($cart, 'quantity'));
echo "Total items: $itemCount" . PHP_EOL;
