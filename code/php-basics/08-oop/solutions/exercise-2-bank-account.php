<?php

declare(strict_types=1);

/**
 * Exercise 2: Create a BankAccount Class
 * 
 * Goal: Implement business logic protection using encapsulation.
 * 
 * Requirements:
 * - BankAccount class with private balance and accountNumber
 * - Use constructor property promotion
 * - getBalance() returns formatted currency
 * - deposit() validates positive amount
 * - withdraw() checks sufficient funds
 * - Display error message if withdrawal fails
 */

class BankAccount
{
    public function __construct(
        private string $accountNumber,
        private float $balance
    ) {}

    public function getBalance(): string
    {
        return '$' . number_format($this->balance, 2);
    }

    public function deposit(float $amount): void
    {
        if ($amount <= 0) {
            echo "Error: Deposit amount must be positive." . PHP_EOL;
            return;
        }

        $this->balance += $amount;
    }

    public function withdraw(float $amount): bool
    {
        if ($amount <= 0) {
            echo "Error: Withdrawal amount must be positive." . PHP_EOL;
            return false;
        }

        if ($amount > $this->balance) {
            echo "Error: Insufficient funds. Available: " . $this->getBalance() . PHP_EOL;
            return false;
        }

        $this->balance -= $amount;
        return true;
    }
}

// Test the BankAccount class
echo "=== Bank Account Demo ===" . PHP_EOL . PHP_EOL;

$account = new BankAccount('ACC-12345', 1000.00);
echo "Initial balance: " . $account->getBalance() . PHP_EOL;

$account->deposit(500.00);
echo "After deposit: " . $account->getBalance() . PHP_EOL;

$account->withdraw(200.00);
echo "After withdrawal: " . $account->getBalance() . PHP_EOL;

$account->withdraw(2000.00); // Should fail
echo "Final balance: " . $account->getBalance() . PHP_EOL;
