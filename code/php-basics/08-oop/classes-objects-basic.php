<?php

declare(strict_types=1);

/**
 * Classes and Objects - The Basics
 * 
 * Introduction to object-oriented programming in PHP:
 * - Defining classes
 * - Creating objects
 * - Properties and methods
 * - $this keyword
 */

echo "=== Classes and Objects Basics ===" . PHP_EOL . PHP_EOL;

// Example 1: Simple class definition
echo "1. Simple Class Definition:" . PHP_EOL;

class Car
{
    public function __construct(
        public string $brand,
        public string $model,
        public int $year
    ) {}

    public function getInfo(): string
    {
        return "{$this->year} {$this->brand} {$this->model}";
    }
}

$myCar = new Car("Toyota", "Camry", 2024);

echo $myCar->getInfo() . PHP_EOL;
echo PHP_EOL;

// Example 2: Constructor method
echo "2. Constructor Method:" . PHP_EOL;

class Book
{
    public function __construct(
        public string $title,
        public string $author,
        public int $pages
    ) {
        echo "Book '{$this->title}' created!" . PHP_EOL;
    }

    public function getDescription(): string
    {
        return "\"{$this->title}\" by {$this->author} ({$this->pages} pages)";
    }
}

$book1 = new Book("1984", "George Orwell", 328);
echo $book1->getDescription() . PHP_EOL;
echo PHP_EOL;

// Example 3: Multiple objects from same class
echo "3. Multiple Objects:" . PHP_EOL;

$book2 = new Book("To Kill a Mockingbird", "Harper Lee", 324);
$book3 = new Book("The Great Gatsby", "F. Scott Fitzgerald", 180);

echo $book2->getDescription() . PHP_EOL;
echo $book3->getDescription() . PHP_EOL;
echo PHP_EOL;

// Example 4: Methods that modify object state
echo "4. Methods Modifying State:" . PHP_EOL;

class BankAccount
{
    private float $balance = 0;

    public function __construct(
        public string $accountNumber,
        public string $owner
    ) {}

    public function deposit(float $amount): void
    {
        if ($amount > 0) {
            $this->balance += $amount;
            echo "Deposited \${$amount}. New balance: \${$this->balance}" . PHP_EOL;
        }
    }

    public function withdraw(float $amount): bool
    {
        if ($amount > 0 && $amount <= $this->balance) {
            $this->balance -= $amount;
            echo "Withdrew \${$amount}. New balance: \${$this->balance}" . PHP_EOL;
            return true;
        }

        echo "Insufficient funds!" . PHP_EOL;
        return false;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }
}

$account = new BankAccount("123456", "Alice");
$account->deposit(1000);
$account->withdraw(250);
$account->withdraw(2000);  // Should fail
echo "Final balance: \$" . $account->getBalance() . PHP_EOL;
echo PHP_EOL;

// Example 5: Practical example - User class
echo "5. Practical Example - User Class:" . PHP_EOL;

class User
{
    private string $passwordHash;

    public function __construct(
        public string $username,
        public string $email,
        string $password
    ) {
        $this->passwordHash = password_hash($password, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    public function getProfile(): array
    {
        return [
            'username' => $this->username,
            'email' => $this->email
        ];
    }
}

$user = new User("john_doe", "john@example.com", "secret123");
echo "User created: {$user->username}" . PHP_EOL;
echo "Password correct: " . ($user->verifyPassword("secret123") ? "Yes" : "No") . PHP_EOL;
echo "Password wrong: " . ($user->verifyPassword("wrong") ? "Yes" : "No") . PHP_EOL;
print_r($user->getProfile());
