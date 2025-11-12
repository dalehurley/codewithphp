<?php

declare(strict_types=1);

/**
 * PHP 8.4 Property Hooks - Basic Examples
 * 
 * Property hooks provide a clean alternative to traditional getters and setters.
 * They allow you to add behavior when reading or writing properties
 * without verbose method definitions.
 * 
 * Requires PHP 8.4+
 */

echo "=== PHP 8.4 Property Hooks ===" . PHP_EOL . PHP_EOL;

// Example 1: Simple transformation on set
class User
{
    // Property hook automatically normalizes email to lowercase
    public string $email {
        set => strtolower($value);
    }

    // Property hook capitalizes first name on set
    public string $firstName {
        set => ucfirst($value);
    }

    public function __construct(string $email, string $firstName)
    {
        $this->email = $email;         // Triggers set hook
        $this->firstName = $firstName; // Triggers set hook
    }
}

$user = new User('JOHN.DOE@EXAMPLE.COM', 'john');
echo "Example 1: Set Hooks (Data Normalization)" . PHP_EOL;
echo "Email: " . $user->email . PHP_EOL;          // john.doe@example.com
echo "First Name: " . $user->firstName . PHP_EOL; // John
echo PHP_EOL;

// Example 2: Get hook for computed properties
class Product
{
    public string $name;
    public float $price;
    public float $taxRate = 0.08;

    // Computed property - calculated on each access
    public float $priceWithTax {
        get => $this->price * (1 + $this->taxRate);
    }

    public function __construct(string $name, float $price)
    {
        $this->name = $name;
        $this->price = $price;
    }
}

$product = new Product('Laptop', 999.99);
echo "Example 2: Get Hooks (Computed Properties)" . PHP_EOL;
echo "Base Price: $" . $product->price . PHP_EOL;
echo "Price with Tax: $" . number_format($product->priceWithTax, 2) . PHP_EOL;
echo PHP_EOL;

// Example 3: Combined get and set hooks
class Temperature
{
    // Store internally as Celsius
    private float $celsius = 0;

    // But expose as Fahrenheit
    public float $fahrenheit {
        get => ($this->celsius * 9 / 5) + 32;
        set => $this->celsius = ($value - 32) * 5 / 9;
    }

    public function getCelsius(): float
    {
        return $this->celsius;
    }
}

$temp = new Temperature();
$temp->fahrenheit = 68; // Set in Fahrenheit
echo "Example 3: Get & Set Hooks (Unit Conversion)" . PHP_EOL;
echo "Set to 68°F" . PHP_EOL;
echo "Stored as: " . $temp->getCelsius() . "°C" . PHP_EOL;
echo "Read as: " . $temp->fahrenheit . "°F" . PHP_EOL;
echo PHP_EOL;

// Example 4: Validation in set hook
class Account
{
    public string $username;

    // Balance can't be negative
    public float $balance {
        set {
            if ($value < 0) {
                throw new InvalidArgumentException("Balance cannot be negative");
            }
            $this->balance = $value;
        }
    }

    public function __construct(string $username, float $initialBalance)
    {
        $this->username = $username;
        $this->balance = $initialBalance;
    }
}

$account = new Account('alice', 1000.00);
echo "Example 4: Validation in Set Hooks" . PHP_EOL;
echo "Initial balance: $" . $account->balance . PHP_EOL;

$account->balance = 500.00; // OK
echo "After withdrawal: $" . $account->balance . PHP_EOL;

try {
    $account->balance = -100.00; // Will throw exception
} catch (InvalidArgumentException $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
echo PHP_EOL;

// Example 5: Caching expensive computations
class DataSet
{
    private array $data;
    private ?float $cachedAverage = null;

    public float $average {
        get {
            // Only calculate if not cached
            if ($this->cachedAverage === null) {
                echo "  [Computing average...]" . PHP_EOL;
                $this->cachedAverage = array_sum($this->data) / count($this->data);
            }
            return $this->cachedAverage;
        }
    }

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function addValue(float $value): void
    {
        $this->data[] = $value;
        $this->cachedAverage = null; // Invalidate cache
    }
}

$dataset = new DataSet([10, 20, 30, 40, 50]);
echo "Example 5: Lazy Computation with Caching" . PHP_EOL;
echo "Average: " . $dataset->average . PHP_EOL; // Computes
echo "Average: " . $dataset->average . PHP_EOL; // Uses cache
$dataset->addValue(60);
echo "After adding 60:" . PHP_EOL;
echo "Average: " . $dataset->average . PHP_EOL; // Recomputes
echo PHP_EOL;

echo "=== Property Hooks Benefits ===" . PHP_EOL;
echo "✓ Cleaner syntax than traditional getters/setters" . PHP_EOL;
echo "✓ Automatic validation and transformation" . PHP_EOL;
echo "✓ Computed properties without manual methods" . PHP_EOL;
echo "✓ Type-safe with full IDE support" . PHP_EOL;
echo "✓ Less boilerplate code" . PHP_EOL;
