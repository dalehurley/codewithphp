<?php
/**
 * Chapter 02: Modern PHP - Property Hooks (PHP 8.4)
 * 
 * Property hooks eliminate the need for verbose getters and setters.
 * This is a PHP 8.4 feature that rivals Ruby's elegance.
 */

declare(strict_types=1);

// ============ BASIC PROPERTY HOOKS ============

class User
{
    // Property with custom setter (automatic backing field)
    public string $email {
        set(string $value) {
            // Normalize email to lowercase
            $this->email = strtolower(trim($value));
        }
    }

    public string $name {
        set(string $value) {
            // Capitalize first letter
            $this->name = ucfirst(strtolower(trim($value)));
        }
    }

    public function __construct(string $email, string $name)
    {
        $this->email = $email;
        $this->name = $name;
    }
}

echo "=== Basic Property Hooks ===\n";
$user = new User('  JOHN@EXAMPLE.COM  ', '  john doe  ');
echo "Email: {$user->email}\n";  // "john@example.com"
echo "Name: {$user->name}\n";    // "John doe"

// ============ COMPUTED PROPERTIES ============

class Product
{
    public function __construct(
        public float $price,
        public float $taxRate = 0.1,
    ) {}

    // Computed property - no backing field needed
    public float $totalPrice {
        get => $this->price * (1 + $this->taxRate);
    }

    public string $priceDisplay {
        get => '$' . number_format($this->totalPrice, 2);
    }
}

echo "\n=== Computed Properties ===\n";
$product = new Product(price: 99.99, taxRate: 0.1);
echo "Price: {$product->price}\n";           // 99.99
echo "Total with tax: {$product->totalPrice}\n";  // 109.989
echo "Display: {$product->priceDisplay}\n"; // "$109.99"

// ============ LAZY LOADING ============

class Post
{
    private ?array $commentsCache = null;

    public function __construct(
        public int $id,
        public string $title,
    ) {}

    // Property hook with lazy loading
    public array $comments {
        get {
            // Load comments only when accessed
            if ($this->commentsCache === null) {
                $this->commentsCache = $this->loadComments();
            }
            return $this->commentsCache;
        }
    }

    private function loadComments(): array
    {
        // Simulate database query
        echo "[Loading comments from database...]\n";
        return [
            ['id' => 1, 'body' => 'Great post!'],
            ['id' => 2, 'body' => 'Very helpful'],
        ];
    }
}

echo "\n=== Lazy Loading ===\n";
$post = new Post(id: 1, title: 'Hello World');
echo "Post: {$post->title}\n";
echo "Comments (first access): ";
$comments = $post->comments;
echo count($comments) . " comments\n";
echo "Comments (second access - cached): ";
$comments = $post->comments;  // No database query this time!
echo count($comments) . " comments\n";

// ============ ASYMMETRIC VISIBILITY (PHP 8.4) ============

class BankAccount
{
    public function __construct(
        public string $name,
        // Public getter, private setter
        public private(set) int $balance = 0,
    ) {}

    public function deposit(int $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be positive');
        }
        $this->balance += $amount;
    }

    public function withdraw(int $amount): void
    {
        if ($amount > $this->balance) {
            throw new \InvalidArgumentException('Insufficient funds');
        }
        $this->balance -= $amount;
    }
}

echo "\n=== Asymmetric Visibility ===\n";
$account = new BankAccount('John', 1000);
echo "Account: {$account->name}\n";
echo "Balance: {$account->balance}\n";  // Can read

$account->deposit(500);
echo "After deposit: {$account->balance}\n";

// This would throw an error:
// $account->balance = 99999;  // Error: Cannot write property
// echo "Trying to set balance directly\n";

echo "\n✓ All property hook examples completed successfully!\n";

