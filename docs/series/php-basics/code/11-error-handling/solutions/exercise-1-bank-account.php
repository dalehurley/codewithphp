<?php

declare(strict_types=1);

/**
 * Exercise 1: Enhanced Bank Account with Exceptions
 * 
 * Create a bank account system with custom exceptions for:
 * - Insufficient funds
 * - Invalid amount
 * - Account locked
 */

// Custom Exceptions
class InsufficientFundsException extends Exception
{
    public function __construct(float $balance, float $attempted)
    {
        parent::__construct(
            "Insufficient funds. Balance: $" . number_format($balance, 2) .
                ", Attempted: $" . number_format($attempted, 2)
        );
    }
}

class InvalidAmountException extends Exception
{
    public function __construct(float $amount)
    {
        parent::__construct("Invalid amount: $" . number_format($amount, 2));
    }
}

class AccountLockedException extends Exception
{
    public function __construct(string $reason = "")
    {
        $message = "Account is locked";
        if ($reason) {
            $message .= ": $reason";
        }
        parent::__construct($message);
    }
}

class DailyLimitExceededException extends Exception
{
    public function __construct(float $limit, float $attempted)
    {
        parent::__construct(
            "Daily withdrawal limit exceeded. Limit: $" . number_format($limit, 2) .
                ", Attempted: $" . number_format($attempted, 2)
        );
    }
}

// Bank Account Class
class BankAccount
{
    private const DAILY_WITHDRAWAL_LIMIT = 1000.00;

    private float $balance = 0.00;
    private bool $isLocked = false;
    private string $accountNumber;
    private array $transactions = [];
    private float $dailyWithdrawals = 0.00;

    public function __construct(string $accountNumber, float $initialDeposit = 0.00)
    {
        if ($initialDeposit < 0) {
            throw new InvalidAmountException($initialDeposit);
        }

        $this->accountNumber = $accountNumber;
        $this->balance = $initialDeposit;

        if ($initialDeposit > 0) {
            $this->recordTransaction('Initial Deposit', $initialDeposit);
        }
    }

    public function deposit(float $amount): void
    {
        if ($this->isLocked) {
            throw new AccountLockedException("Cannot deposit to locked account");
        }

        if ($amount <= 0) {
            throw new InvalidAmountException($amount);
        }

        $this->balance += $amount;
        $this->recordTransaction('Deposit', $amount);
    }

    public function withdraw(float $amount): void
    {
        if ($this->isLocked) {
            throw new AccountLockedException("Cannot withdraw from locked account");
        }

        if ($amount <= 0) {
            throw new InvalidAmountException($amount);
        }

        if ($amount > $this->balance) {
            throw new InsufficientFundsException($this->balance, $amount);
        }

        if ($this->dailyWithdrawals + $amount > self::DAILY_WITHDRAWAL_LIMIT) {
            throw new DailyLimitExceededException(
                self::DAILY_WITHDRAWAL_LIMIT,
                $this->dailyWithdrawals + $amount
            );
        }

        $this->balance -= $amount;
        $this->dailyWithdrawals += $amount;
        $this->recordTransaction('Withdrawal', -$amount);
    }

    public function transfer(BankAccount $toAccount, float $amount): void
    {
        try {
            $this->withdraw($amount);

            try {
                $toAccount->deposit($amount);
                $this->recordTransaction("Transfer to {$toAccount->accountNumber}", -$amount);
            } catch (Exception $e) {
                // Rollback withdrawal if deposit fails
                $this->balance += $amount;
                $this->dailyWithdrawals -= $amount;
                throw new Exception("Transfer failed: " . $e->getMessage(), 0, $e);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function lockAccount(string $reason = ""): void
    {
        $this->isLocked = true;
        $this->recordTransaction("Account Locked", 0, $reason);
    }

    public function unlockAccount(): void
    {
        $this->isLocked = false;
        $this->recordTransaction("Account Unlocked", 0);
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function isLocked(): bool
    {
        return $this->isLocked;
    }

    public function getTransactionHistory(): array
    {
        return $this->transactions;
    }

    public function printStatement(): void
    {
        echo "=== Account Statement ===" . PHP_EOL;
        echo "Account: {$this->accountNumber}" . PHP_EOL;
        echo "Status: " . ($this->isLocked ? "LOCKED" : "Active") . PHP_EOL;
        echo "Balance: $" . number_format($this->balance, 2) . PHP_EOL;
        echo PHP_EOL;

        echo "Transaction History:" . PHP_EOL;
        echo str_repeat("-", 70) . PHP_EOL;

        foreach ($this->transactions as $transaction) {
            echo $transaction['date'] . " | ";
            echo str_pad($transaction['type'], 20) . " | ";

            if ($transaction['amount'] != 0) {
                $sign = $transaction['amount'] > 0 ? '+' : '';
                echo $sign . "$" . number_format($transaction['amount'], 2);
            }

            if (!empty($transaction['note'])) {
                echo " | " . $transaction['note'];
            }

            echo PHP_EOL;
        }
    }

    private function recordTransaction(string $type, float $amount, string $note = ""): void
    {
        $this->transactions[] = [
            'date' => date('Y-m-d H:i:s'),
            'type' => $type,
            'amount' => $amount,
            'note' => $note
        ];
    }
}

// Test the bank account system
echo "=== Bank Account System Demo ===" . PHP_EOL . PHP_EOL;

try {
    // Create account
    $account1 = new BankAccount("ACC-001", 1000.00);
    echo "✓ Account created with $1000.00" . PHP_EOL;

    // Deposit
    $account1->deposit(500.00);
    echo "✓ Deposited $500.00. Balance: $" . number_format($account1->getBalance(), 2) . PHP_EOL;

    // Withdraw
    $account1->withdraw(200.00);
    echo "✓ Withdrew $200.00. Balance: $" . number_format($account1->getBalance(), 2) . PHP_EOL;

    echo PHP_EOL;

    // Test insufficient funds
    try {
        $account1->withdraw(5000.00);
    } catch (InsufficientFundsException $e) {
        echo "✗ " . $e->getMessage() . PHP_EOL;
    }

    // Test invalid amount
    try {
        $account1->deposit(-50.00);
    } catch (InvalidAmountException $e) {
        echo "✗ " . $e->getMessage() . PHP_EOL;
    }

    echo PHP_EOL;

    // Test transfer
    $account2 = new BankAccount("ACC-002", 500.00);
    echo "✓ Second account created with $500.00" . PHP_EOL;

    $account1->transfer($account2, 100.00);
    echo "✓ Transferred $100.00 from ACC-001 to ACC-002" . PHP_EOL;
    echo "  ACC-001 Balance: $" . number_format($account1->getBalance(), 2) . PHP_EOL;
    echo "  ACC-002 Balance: $" . number_format($account2->getBalance(), 2) . PHP_EOL;

    echo PHP_EOL;

    // Test account lock
    $account1->lockAccount("Suspicious activity detected");
    echo "✓ Account locked" . PHP_EOL;

    try {
        $account1->withdraw(50.00);
    } catch (AccountLockedException $e) {
        echo "✗ " . $e->getMessage() . PHP_EOL;
    }

    echo PHP_EOL;

    // Print statement
    $account1->printStatement();
} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace:" . PHP_EOL . $e->getTraceAsString() . PHP_EOL;
}
