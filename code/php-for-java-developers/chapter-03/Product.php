<?php

declare(strict_types=1);

// Modern PHP 8+ with constructor promotion
class Product
{
    public function __construct(
        public string $name,
        public float $price,
        public int $quantity = 0
    ) {}

    public function getTotalValue(): float
    {
        return $this->price * $this->quantity;
    }

    public function display(): void
    {
        echo "{$this->name}: \${$this->price} x {$this->quantity}\n";
        echo "Total value: \${$this->getTotalValue()}\n";
    }

    public function __toString(): string
    {
        return "{$this->name} (\${$this->price})";
    }
}

// Usage
$product = new Product("Laptop", 999.99, 5);
$product->display();

echo "\nAccessing properties:\n";
echo "Name: {$product->name}\n";
echo "Price: {$product->price}\n";
echo "Quantity: {$product->quantity}\n";

echo "\nUsing __toString:\n";
echo $product . "\n";
