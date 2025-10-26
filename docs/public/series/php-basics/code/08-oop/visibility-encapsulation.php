<?php

declare(strict_types=1);

/**
 * Property and Method Visibility
 * 
 * Demonstrates:
 * - public, private, protected access modifiers
 * - Encapsulation principles
 * - Getters and setters
 */

echo "=== Visibility and Encapsulation ===" . PHP_EOL . PHP_EOL;

// Example 1: Public visibility
echo "1. Public Visibility:" . PHP_EOL;

class PublicExample
{
    public string $name = "Public Property";

    public function showName(): void
    {
        echo "Name: {$this->name}" . PHP_EOL;
    }
}

$obj = new PublicExample();
echo $obj->name . PHP_EOL;  // Can access directly
$obj->name = "Modified";     // Can modify directly
$obj->showName();
echo PHP_EOL;

// Example 2: Private visibility
echo "2. Private Visibility:" . PHP_EOL;

class PrivateExample
{
    private string $secret = "Private Property";

    public function revealSecret(): string
    {
        return $this->secret;  // Can access within class
    }

    public function setSecret(string $newSecret): void
    {
        $this->secret = $newSecret;
    }
}

$obj2 = new PrivateExample();
// echo $obj2->secret;  // ERROR: Cannot access private property
echo $obj2->revealSecret() . PHP_EOL;  // Must use public method
$obj2->setSecret("New Secret");
echo $obj2->revealSecret() . PHP_EOL;
echo PHP_EOL;

// Example 3: Protected visibility (for inheritance)
echo "3. Protected Visibility:" . PHP_EOL;

class ParentClass
{
    protected string $protectedProp = "Protected in parent";

    protected function protectedMethod(): string
    {
        return "Protected method called";
    }
}

class ChildClass extends ParentClass
{
    public function accessProtected(): void
    {
        // Can access protected members from parent
        echo $this->protectedProp . PHP_EOL;
        echo $this->protectedMethod() . PHP_EOL;
    }
}

$child = new ChildClass();
$child->accessProtected();
// echo $child->protectedProp;  // ERROR: Cannot access protected property
echo PHP_EOL;

// Example 4: Proper encapsulation with getters/setters
echo "4. Encapsulation with Getters/Setters:" . PHP_EOL;

class Product
{
    private float $price;
    private int $quantity;

    public function __construct(
        private string $name,
        float $price,
        int $quantity
    ) {
        $this->setPrice($price);
        $this->setQuantity($quantity);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        if ($price < 0) {
            throw new InvalidArgumentException("Price cannot be negative");
        }
        $this->price = $price;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        if ($quantity < 0) {
            throw new InvalidArgumentException("Quantity cannot be negative");
        }
        $this->quantity = $quantity;
    }

    public function getTotalValue(): float
    {
        return $this->price * $this->quantity;
    }
}

$product = new Product("Laptop", 999.99, 5);
echo "Product: {$product->getName()}" . PHP_EOL;
echo "Price: \${$product->getPrice()}" . PHP_EOL;
echo "Quantity: {$product->getQuantity()}" . PHP_EOL;
echo "Total Value: \$" . number_format($product->getTotalValue(), 2) . PHP_EOL;

// Validation in action
try {
    $product->setPrice(-100);
} catch (InvalidArgumentException $e) {
    echo "Error: {$e->getMessage()}" . PHP_EOL;
}
echo PHP_EOL;

// Example 5: Real-world example - Email validation
echo "5. Real-World Example - Email Class:" . PHP_EOL;

class Email
{
    private string $address;

    public function __construct(string $address)
    {
        $this->setAddress($address);
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        if (!filter_var($address, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email address: $address");
        }
        $this->address = strtolower($address);
    }

    public function getDomain(): string
    {
        return substr(strstr($this->address, '@'), 1);
    }

    public function getUsername(): string
    {
        return strstr($this->address, '@', true);
    }
}

$email = new Email("John.Doe@Example.COM");
echo "Email: {$email->getAddress()}" . PHP_EOL;
echo "Domain: {$email->getDomain()}" . PHP_EOL;
echo "Username: {$email->getUsername()}" . PHP_EOL;

try {
    $invalidEmail = new Email("not-an-email");
} catch (InvalidArgumentException $e) {
    echo "Validation error: {$e->getMessage()}" . PHP_EOL;
}
