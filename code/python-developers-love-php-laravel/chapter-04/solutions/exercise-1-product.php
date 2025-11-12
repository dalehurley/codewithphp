<?php

declare(strict_types=1);

/**
 * Exercise 1 Solution: Convert Python Class to PHP
 * 
 * This is the solution for Exercise 1 from Chapter 04.
 * Converts a Python Product class to PHP 8.4 with proper type declarations.
 */

class Product
{
    public function __construct(
        private string $name,
        private float $price,
        private bool $inStock = true
    ) {}
    
    public function applyDiscount(float $percent): void
    {
        if ($percent < 0 || $percent > 100) {
            throw new ValueError("Discount must be between 0 and 100");
        }
        $this->price *= (1 - $percent / 100);
    }
    
    public function getInfo(): string
    {
        $status = $this->inStock ? "Available" : "Out of stock";
        // Format price to 2 decimal places (similar to Python's :.2f)
        $formattedPrice = number_format($this->price, 2, '.', '');
        return "{$this->name}: \${$formattedPrice} - {$status}";
    }
    
    // Getters for testing purposes
    public function getName(): string
    {
        return $this->name;
    }
    
    public function getPrice(): float
    {
        return $this->price;
    }
    
    public function isInStock(): bool
    {
        return $this->inStock;
    }
}

// Test the solution
$product = new Product("Laptop", 999.99, true);
$product->applyDiscount(10);
echo $product->getInfo() . "\n";
// Expected output: Laptop: $899.99 - Available

