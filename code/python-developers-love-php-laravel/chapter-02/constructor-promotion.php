<?php

declare(strict_types=1);

// PHP 8.0+ constructor property promotion
// Similar to Python dataclasses, reduces boilerplate

// PHP 8.0+ constructor property promotion
class User
{
    public function __construct(
        public string $name,
        public string $email,
        public int $age = 0
    ) {}
}

$user = new User('John Doe', 'john@example.com', 30);
echo "Name: {$user->name}\n";  // Output: Name: John Doe
echo "Email: {$user->email}\n";  // Output: Email: john@example.com
echo "Age: {$user->age}\n";  // Output: Age: 30

// With mixed visibility
class Product
{
    public function __construct(
        public string $name,
        public float $price,
        private int $stock = 0
    ) {}

    public function getStock(): int
    {
        return $this->stock;
    }

    public function reduceStock(int $amount): void
    {
        $this->stock -= $amount;
    }
}

$product = new Product('Laptop', 999.99, 10);
echo "Product: {$product->name}\n";  // Output: Product: Laptop
echo "Price: \${$product->price}\n";  // Output: Price: $999.99
echo "Stock: {$product->getStock()}\n";  // Output: Stock: 10

$product->reduceStock(3);
echo "Stock after reduction: {$product->getStock()}\n";  // Output: Stock after reduction: 7

// With readonly properties (PHP 8.2+)
class Order
{
    public function __construct(
        public readonly string $id,
        public readonly float $total,
        public readonly string $status = 'pending'
    ) {}
}

$order = new Order('ORD-123', 149.99, 'processing');
echo "Order ID: {$order->id}\n";  // Output: Order ID: ORD-123
echo "Total: \${$order->total}\n";  // Output: Total: $149.99
// $order->id = 'NEW-ID';  // ❌ Error: Cannot modify readonly property

