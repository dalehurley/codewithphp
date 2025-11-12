<?php

namespace BadExamples; // ❌ No blank line after opening tag

use DateTime;
use InvalidArgumentException; // ❌ Multiple imports on one line

// ❌ Inconsistent spacing and formatting
class   OrderProcessor
{ // ❌ Extra spaces, brace not on new line

    private $total = 0.0; // ❌ No spaces around =
    private array $items = []; // ❌ Array instead of array

    // ❌ Opening brace on wrong line
    public function __construct(string $orderId, DateTime $createdAt) // ❌ No space after comma
    {
        $this->orderId = $orderId; // ❌ No spaces around =
        $this->createdAt = $createdAt;
    }

    // ❌ Poor parameter formatting
    public function addItem($productId, $quantity, $price, $options = [])
    { // ❌ No type hints

        // ❌ No spaces in conditionals
        if ($quantity <= 0) { // ❌ Brace on same line with no space
            throw new InvalidArgumentException('Quantity must be positive');
        }

        // ❌ Inconsistent indentation (2 spaces instead of 4)
        if ($price < 0) {
            throw new InvalidArgumentException('Price cannot be negative');
        }

        // ❌ Inconsistent array formatting
        $this->items[] = ['product_id' => $productId, 'quantity' => $quantity, 'price' => $price];

        $this->total += $price * $quantity; // ❌ No spaces around operators
    }

    public function getTotal(): Float // ❌ Float instead of float
    {
        return $this->total; // ❌ Uppercase Return
    }

    public function process(): Bool // ❌ Bool instead of bool
    {
        // ❌ else if instead of elseif
        if (empty($this->items)) {
            throw new RuntimeException('No items');
        } else if ($this->total <= 0) { // ❌ Wrong keyword, no spacing
            throw new RuntimeException('Invalid total');
        }

        // ❌ Poor loop formatting
        foreach ($this->items as $item) { // ❌ No spaces
            $this->validateItem($item);
        }

        // ❌ Poor for loop formatting
        for ($i = 0; $i < count($this->items); $i++) {
            // Process
        }

        // ❌ Inconsistent while formatting
        $retry = 3;
        while ($retry > 0) {  // ❌ Brace on new line
            if ($this->attemptPayment()) return true; // ❌ No braces, same line
            $retry--;
        }

        return FALSE; // ❌ Uppercase FALSE
    }

    private function validateItem(array $item) // ❌ Array instead of array, no return type
    {
        // ❌ Poor switch formatting
        switch ($item['product_id']) {
            case 'PROD001': // ❌ Not indented
                $this->applyDiscount(0.10); // ❌ Not indented
                break;
            case 'PROD002':
                $this->applyDiscount(0.15);
                break; // ❌ All on one line
            default:
                break; // ❌ No spacing
        }
    }

    private function attemptPayment()
    {  // ❌ Extra spaces before brace
        try { // ❌ Uppercase Try
            return $this->processPayment();
        } catch (RuntimeException $e) { // ❌ Uppercase Catch, no space
            $this->logError($e->getMessage());
            return false;
        } finally { // ❌ Uppercase Finally, no space
            $this->cleanup();
        }
    }

    // ❌ Poor array formatting
    public function getConfig()
    {
        return ['timeout' => 30, 'retry' => 3, 'endpoints' => ['api' => 'https://api.example.com']]; // ❌ All on one line
    }

    // ❌ Poor closure formatting
    public function filterItems($callback)
    {
        return array_filter($this->items, function ($item) use ($callback) {
            return $callback($item);
        }); // ❌ All on one line, no spaces
    }

    // Placeholder methods
    private function applyDiscount($p) {}
    private function processPayment()
    {
        return true;
    }
    private function logError($m) {}
    private function cleanup() {}
}
