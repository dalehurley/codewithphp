<?php

declare(strict_types=1);

/**
 * PHP 8.4 Asymmetric Visibility
 * 
 * Asymmetric visibility allows different access levels for reading and writing properties.
 * This is perfect for implementing immutability and controlled property access.
 * 
 * Syntax: public private(set) $property
 * - Reading: public
 * - Writing: private (only within the class)
 * 
 * Requires PHP 8.4+
 */

echo "=== PHP 8.4 Asymmetric Visibility ===" . PHP_EOL . PHP_EOL;

// Example 1: Immutable ID
class Order
{
    // Public for reading, private for writing
    public private(set) string $id;
    public private(set) DateTime $createdAt;

    public string $customerName;
    public float $total;

    public function __construct(string $customerName, float $total)
    {
        // Can set within constructor
        $this->id = uniqid('ORD_');
        $this->createdAt = new DateTime();

        // Regular properties can be set normally
        $this->customerName = $customerName;
        $this->total = $total;
    }
}

$order = new Order('Alice Johnson', 99.99);

echo "Example 1: Immutable ID and Timestamp" . PHP_EOL;
echo "Order ID: " . $order->id . PHP_EOL;                        // ✓ Can read
echo "Created: " . $order->createdAt->format('Y-m-d H:i:s') . PHP_EOL; // ✓ Can read
echo "Customer: " . $order->customerName . PHP_EOL;

// $order->id = 'ORD_123'; // ✗ Error! Cannot write from outside
// $order->createdAt = new DateTime(); // ✗ Error! Cannot write from outside

$order->customerName = 'Bob Smith';  // ✓ Can write (public property)
echo "Updated customer: " . $order->customerName . PHP_EOL;
echo PHP_EOL;

// Example 2: Controlled Computed Property
class ShoppingCart
{
    private array $items = [];

    // Anyone can read the total, but only the class can update it
    public private(set) float $total = 0.0;

    public function addItem(string $name, float $price): void
    {
        $this->items[] = ['name' => $name, 'price' => $price];
        // Only the class can update the total
        $this->total += $price;
    }

    public function removeLastItem(): void
    {
        if (!empty($this->items)) {
            $item = array_pop($this->items);
            $this->total -= $item['price'];
        }
    }

    public function getItemCount(): int
    {
        return count($this->items);
    }
}

$cart = new ShoppingCart();
echo "Example 2: Shopping Cart with Protected Total" . PHP_EOL;
echo "Initial total: $" . $cart->total . PHP_EOL;

$cart->addItem('Book', 29.99);
$cart->addItem('Pen', 5.99);
echo "After adding items: $" . $cart->total . PHP_EOL;

// $cart->total = 1000000; // ✗ Error! Cannot manipulate total directly

$cart->removeLastItem();
echo "After removing one: $" . $cart->total . PHP_EOL;
echo PHP_EOL;

// Example 3: User Authentication State
class UserSession
{
    public private(set) ?int $userId = null;
    public private(set) ?string $username = null;
    public private(set) bool $isAuthenticated = false;
    public private(set) ?DateTime $loginTime = null;

    public function login(int $userId, string $username): void
    {
        $this->userId = $userId;
        $this->username = $username;
        $this->isAuthenticated = true;
        $this->loginTime = new DateTime();
    }

    public function logout(): void
    {
        $this->userId = null;
        $this->username = null;
        $this->isAuthenticated = false;
        $this->loginTime = null;
    }
}

$session = new UserSession();
echo "Example 3: Secure Session Management" . PHP_EOL;
echo "Authenticated: " . ($session->isAuthenticated ? 'Yes' : 'No') . PHP_EOL;

$session->login(42, 'alice');
echo "After login:" . PHP_EOL;
echo "  User ID: " . $session->userId . PHP_EOL;
echo "  Username: " . $session->username . PHP_EOL;
echo "  Authenticated: " . ($session->isAuthenticated ? 'Yes' : 'No') . PHP_EOL;
echo "  Login time: " . $session->loginTime->format('H:i:s') . PHP_EOL;

// $session->isAuthenticated = false; // ✗ Error! Can't fake authentication
// $session->userId = 999;             // ✗ Error! Can't impersonate another user

$session->logout();
echo "After logout: " . ($session->isAuthenticated ? 'Yes' : 'No') . PHP_EOL;
echo PHP_EOL;

// Example 4: Configuration with Timestamp Tracking
class Config
{
    public private(set) array $settings = [];
    public private(set) DateTime $lastModified;
    public private(set) int $version = 1;

    public function __construct()
    {
        $this->lastModified = new DateTime();
    }

    public function set(string $key, mixed $value): void
    {
        $this->settings[$key] = $value;
        $this->lastModified = new DateTime();
        $this->version++;
    }

    public function get(string $key): mixed
    {
        return $this->settings[$key] ?? null;
    }
}

$config = new Config();
echo "Example 4: Configuration with Automatic Tracking" . PHP_EOL;
echo "Initial version: " . $config->version . PHP_EOL;

$config->set('app_name', 'My App');
$config->set('debug_mode', true);

echo "After updates:" . PHP_EOL;
echo "  Version: " . $config->version . PHP_EOL;
echo "  Last modified: " . $config->lastModified->format('H:i:s') . PHP_EOL;
echo "  App name: " . $config->get('app_name') . PHP_EOL;

// $config->version = 999; // ✗ Error! Can't manipulate version
echo PHP_EOL;

echo "=== Asymmetric Visibility Benefits ===" . PHP_EOL;
echo "✓ Enforces immutability without private + getter boilerplate" . PHP_EOL;
echo "✓ Clear intent: 'readable everywhere, writable only here'" . PHP_EOL;
echo "✓ Prevents accidental property modification" . PHP_EOL;
echo "✓ Reduces need for getter methods" . PHP_EOL;
echo "✓ Perfect for IDs, timestamps, and computed values" . PHP_EOL;
echo PHP_EOL;

echo "=== Common Use Cases ===" . PHP_EOL;
echo "• IDs and unique identifiers" . PHP_EOL;
echo "• Timestamps (createdAt, updatedAt)" . PHP_EOL;
echo "• Computed values (totals, counts)" . PHP_EOL;
echo "• State flags (isPublished, isActive)" . PHP_EOL;
echo "• Version numbers" . PHP_EOL;
