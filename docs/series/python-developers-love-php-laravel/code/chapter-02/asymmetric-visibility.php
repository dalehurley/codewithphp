<?php

declare(strict_types=1);

// PHP 8.4 asymmetric visibility
// Allows public read access with private write access
// Similar to Python's @property without a setter

class User
{
    // Public read, private write
    public string $name {
        get { return $this->name ?? ''; }
        set { $this->name = $value; }
    }

    public function __construct(string $name)
    {
        $this->name = $name;  // Can set in constructor
    }

    public function updateName(string $newName): void
    {
        $this->name = $newName;  // Can set in class methods
    }
}

$user = new User('John Doe');
echo "Name: {$user->name}\n";  // ✅ Can read: Output: Name: John Doe

// $user->name = 'Jane';  // ❌ Error: Cannot access private property

$user->updateName('Jane Smith');
echo "Updated Name: {$user->name}\n";  // ✅ Can read: Output: Updated Name: Jane Smith

// Asymmetric visibility with validation
class Product
{
    public float $price {
        get { return $this->price ?? 0.0; }
        set { $this->price = $value; }
    }

    public function __construct(float $price)
    {
        $this->setPrice($price);
    }

    private function setPrice(float $price): void
    {
        if ($price < 0) {
            throw new InvalidArgumentException('Price cannot be negative');
        }
        $this->price = $price;
    }

    public function updatePrice(float $newPrice): void
    {
        $this->setPrice($newPrice);
    }
}

$product = new Product(99.99);
echo "Price: \${$product->price}\n";  // ✅ Can read: Output: Price: $99.99

$product->updatePrice(149.99);
echo "Updated Price: \${$product->price}\n";  // ✅ Can read: Output: Updated Price: $149.99

// $product->price = 199.99;  // ❌ Error: Cannot access private property

