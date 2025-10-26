<?php

declare(strict_types=1);

/**
 * Exercise 2: Payment System with Abstract Classes
 * 
 * Design an abstract payment system:
 * 
 * Requirements:
 * - Abstract PaymentMethod class with abstract processPayment() method
 * - Concrete formatAmount() method that formats to 2 decimal places
 * - CreditCard class implementing processPayment()
 * - PayPal class implementing processPayment()
 * - checkout() function that accepts PaymentMethod and amount
 */

abstract class PaymentMethod
{
    /**
     * Process a payment - must be implemented by child classes
     */
    abstract public function processPayment(float $amount): string;

    /**
     * Format amount to 2 decimal places
     */
    public function formatAmount(float $amount): string
    {
        return '$' . number_format($amount, 2);
    }
}

class CreditCard extends PaymentMethod
{
    public function __construct(
        private string $cardNumber,
        private string $cardHolder
    ) {}

    public function processPayment(float $amount): string
    {
        $formattedAmount = $this->formatAmount($amount);
        return "Processed {$formattedAmount} via credit card (****" .
            substr($this->cardNumber, -4) . ")";
    }
}

class PayPal extends PaymentMethod
{
    public function __construct(
        private string $email
    ) {}

    public function processPayment(float $amount): string
    {
        $formattedAmount = $this->formatAmount($amount);
        return "Processed {$formattedAmount} via PayPal ({$this->email})";
    }
}

class BankTransfer extends PaymentMethod
{
    public function __construct(
        private string $accountNumber
    ) {}

    public function processPayment(float $amount): string
    {
        $formattedAmount = $this->formatAmount($amount);
        return "Processed {$formattedAmount} via bank transfer to account " .
            "****" . substr($this->accountNumber, -4);
    }
}

/**
 * Generic checkout function that works with any PaymentMethod
 */
function checkout(PaymentMethod $method, float $amount): void
{
    echo "Processing payment..." . PHP_EOL;
    echo $method->processPayment($amount) . PHP_EOL;
    echo "Payment complete!" . PHP_EOL . PHP_EOL;
}

// Test the payment system
echo "=== Payment Processing System ===" . PHP_EOL . PHP_EOL;

$creditCard = new CreditCard('4532123456789012', 'John Doe');
checkout($creditCard, 149.99);

$paypal = new PayPal('john.doe@example.com');
checkout($paypal, 79.50);

$bankTransfer = new BankTransfer('1234567890');
checkout($bankTransfer, 250.00);

echo "--- Testing polymorphism ---" . PHP_EOL;
$paymentMethods = [
    $creditCard,
    $paypal,
    $bankTransfer
];

echo "Processing multiple payments:" . PHP_EOL;
foreach ($paymentMethods as $index => $method) {
    $amount = ($index + 1) * 25.00;
    echo ($index + 1) . ". " . $method->processPayment($amount) . PHP_EOL;
}
