<?php

declare(strict_types=1);

/**
 * Exercise 3: Create a Product Class (Challenge)
 * 
 * Goal: Combine multiple OOP concepts in a practical scenario.
 * 
 * Requirements:
 * - Private properties: name, price, quantity
 * - Constructor property promotion with type declarations
 * - applyDiscount() reduces price by percentage
 * - restock() increases quantity
 * - sell() decreases quantity with stock validation
 * - getTotalValue() returns price * quantity
 * - Proper input validation on all methods
 */

class Product
{
    public function __construct(
        private string $name,
        private float $price,
        private int $quantity
    ) {
        if ($price < 0) {
            throw new InvalidArgumentException("Price cannot be negative");
        }
        if ($quantity < 0) {
            throw new InvalidArgumentException("Quantity cannot be negative");
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function applyDiscount(int $percentage): void
    {
        if ($percentage < 0 || $percentage > 100) {
            echo "Error: Discount percentage must be between 0 and 100." . PHP_EOL;
            return;
        }

        $discountAmount = $this->price * ($percentage / 100);
        $this->price -= $discountAmount;
        echo "Applied {$percentage}% discount. New price: \$" . number_format($this->price, 2) . PHP_EOL;
    }

    public function restock(int $amount): void
    {
        if ($amount <= 0) {
            echo "Error: Restock amount must be positive." . PHP_EOL;
            return;
        }

        $this->quantity += $amount;
        echo "Restocked {$amount} units. New quantity: {$this->quantity}" . PHP_EOL;
    }

    public function sell(int $amount): bool
    {
        if ($amount <= 0) {
            echo "Error: Sell amount must be positive." . PHP_EOL;
            return false;
        }

        if ($amount > $this->quantity) {
            echo "Error: Insufficient stock. Available: {$this->quantity}" . PHP_EOL;
            return false;
        }

        $this->quantity -= $amount;
        echo "Sold {$amount} units. Remaining quantity: {$this->quantity}" . PHP_EOL;
        return true;
    }

    public function getTotalValue(): float
    {
        return $this->price * $this->quantity;
    }

    public function displayInfo(): void
    {
        echo "Product: {$this->name}" . PHP_EOL;
        echo "Price: \$" . number_format($this->price, 2) . PHP_EOL;
        echo "Quantity: {$this->quantity}" . PHP_EOL;
        echo "Total Value: \$" . number_format($this->getTotalValue(), 2) . PHP_EOL;
    }
}

// Test the Product class
echo "=== Product Management System ===" . PHP_EOL . PHP_EOL;

$laptop = new Product('Gaming Laptop', 1299.99, 15);
$laptop->displayInfo();
echo PHP_EOL;

echo "--- Applying discount ---" . PHP_EOL;
$laptop->applyDiscount(10);
echo PHP_EOL;

echo "--- Selling products ---" . PHP_EOL;
$laptop->sell(3);
echo PHP_EOL;

echo "--- Restocking ---" . PHP_EOL;
$laptop->restock(10);
echo PHP_EOL;

echo "--- Final state ---" . PHP_EOL;
$laptop->displayInfo();
echo PHP_EOL;

echo "--- Testing validation ---" . PHP_EOL;
$laptop->sell(50); // Should fail - insufficient stock
$laptop->applyDiscount(150); // Should fail - invalid percentage
