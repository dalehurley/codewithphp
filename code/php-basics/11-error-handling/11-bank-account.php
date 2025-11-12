<?php

declare(strict_types=1);

/**
 * Custom Exception Example
 * Demonstrates creating and using custom exception classes
 */

// A custom exception class for insufficient funds
class InsufficientFundsException extends Exception {}

class BankAccount
{
    // PHP 8.0+ constructor property promotion
    public function __construct(private float $balance) {}

    public function withdraw(float $amount): void
    {
        if ($amount <= 0) {
            throw new Exception("Withdrawal amount must be positive.");
        }

        if ($amount > $this->balance) {
            // Throw our specific exception type
            throw new InsufficientFundsException(
                "Cannot withdraw $$amount. Insufficient funds."
            );
        }

        $this->balance -= $amount;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }
}

// Demonstration
$account = new BankAccount(100);

try {
    $account->withdraw(50);
    echo "Withdrawal successful. New balance: $" . $account->getBalance() . PHP_EOL;

    $account->withdraw(75); // This will throw InsufficientFundsException
    echo "This line won't execute." . PHP_EOL;
} catch (InsufficientFundsException $e) {
    // We can specifically catch *our* custom exception
    echo "Transaction failed: " . $e->getMessage() . PHP_EOL;
    echo "Current balance remains: $" . $account->getBalance() . PHP_EOL;
} catch (Exception $e) {
    // A general catch block for any other type of exception
    echo "A general error occurred: " . $e->getMessage() . PHP_EOL;
}

echo "Application continues running normally." . PHP_EOL;
