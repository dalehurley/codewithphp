<?php

declare(strict_types=1);

class BankAccount
{
    private static int $totalAccounts = 0;
    private static int $totalTransactions = 0;

    private float $balance;
    private string $accountNumber;

    public function __construct(string $accountNumber, float $initialBalance = 0)
    {
        if ($initialBalance < 0) {
            throw new InvalidArgumentException("Initial balance cannot be negative");
        }

        $this->accountNumber = $accountNumber;
        $this->balance = $initialBalance;
        self::$totalAccounts++;
    }

    public function deposit(float $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException("Deposit amount must be positive");
        }

        $this->balance += $amount;
        self::$totalTransactions++;
        echo "Deposited \${$amount} to account {$this->accountNumber}\n";
    }

    public function withdraw(float $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException("Withdrawal amount must be positive");
        }

        if ($amount > $this->balance) {
            throw new RuntimeException("Insufficient funds");
        }

        $this->balance -= $amount;
        self::$totalTransactions++;
        echo "Withdrawn \${$amount} from account {$this->accountNumber}\n";
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public static function getTotalAccounts(): int
    {
        return self::$totalAccounts;
    }

    public static function getTotalTransactions(): int
    {
        return self::$totalTransactions;
    }
}

// Test the BankAccount class
echo "=== Creating Bank Accounts ===\n";
$account1 = new BankAccount("ACC001", 1000);
echo "Created account: {$account1->getAccountNumber()}\n";
echo "Initial balance: \${$account1->getBalance()}\n\n";

echo "=== Transactions ===\n";
$account1->deposit(500);
$account1->withdraw(200);
echo "Current balance: \${$account1->getBalance()}\n\n";

$account2 = new BankAccount("ACC002", 2000);
$account2->deposit(1000);
echo "\n";

echo "=== Statistics ===\n";
echo "Total accounts: " . BankAccount::getTotalAccounts() . "\n";
echo "Total transactions: " . BankAccount::getTotalTransactions() . "\n";

// Test error handling
echo "\n=== Error Handling ===\n";
try {
    $account1->withdraw(5000);
} catch (RuntimeException $e) {
    echo "Error: {$e->getMessage()}\n";
}

try {
    $invalidAccount = new BankAccount("ACC003", -100);
} catch (InvalidArgumentException $e) {
    echo "Error: {$e->getMessage()}\n";
}
