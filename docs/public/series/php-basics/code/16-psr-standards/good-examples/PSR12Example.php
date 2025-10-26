<?php

declare(strict_types=1);

namespace GoodExamples;

use DateTime;
use InvalidArgumentException;
use RuntimeException;

/**
 * PSR-12 Extended Coding Style Example
 * 
 * Demonstrates all PSR-12 formatting rules.
 */
class OrderProcessor
{
    // ✓ Visibility declared on all properties
    private float $total = 0.0;
    private array $items = [];

    // ✓ Type declarations with spaces
    public function __construct(
        private string $orderId,
        private DateTime $createdAt
    ) {}

    /**
     * ✓ Method declaration on multiple lines when needed
     * ✓ Each parameter on its own line
     * ✓ Closing parenthesis and opening brace on same line
     */
    public function addItem(
        string $productId,
        int $quantity,
        float $price,
        array $options = []
    ): void {
        // ✓ 4 spaces indentation (not tabs)
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Quantity must be positive');
        }

        // ✓ Space after control structure keyword
        // ✓ Opening brace on same line
        if ($price < 0) {
            throw new InvalidArgumentException('Price cannot be negative');
        }

        $this->items[] = [
            'product_id' => $productId,
            'quantity' => $quantity,
            'price' => $price,
            'options' => $options,
        ];

        $this->total += $price * $quantity;
    }

    /**
     * ✓ Return type declaration with colon and space
     */
    public function getTotal(): float
    {
        return $this->total;
    }

    /**
     * ✓ Control structures properly formatted
     */
    public function process(): bool
    {
        // ✓ Elseif (not else if)
        if (empty($this->items)) {
            throw new RuntimeException('No items in order');
        } elseif ($this->total <= 0) {
            throw new RuntimeException('Invalid order total');
        }

        // ✓ Foreach with proper spacing
        foreach ($this->items as $item) {
            $this->validateItem($item);
        }

        // ✓ For loop with proper spacing
        for ($i = 0; $i < count($this->items); $i++) {
            // Process item
        }

        // ✓ While with proper spacing
        $retry = 3;
        while ($retry > 0) {
            if ($this->attemptPayment()) {
                return true;
            }
            $retry--;
        }

        return false;
    }

    /**
     * ✓ Private methods follow same rules
     */
    private function validateItem(array $item): void
    {
        // ✓ Switch properly formatted
        switch ($item['product_id']) {
            case 'PROD001':
                // ✓ Case indented
                $this->applyDiscount(0.10);
                break; // ✓ Break on new line

            case 'PROD002':
                $this->applyDiscount(0.15);
                break;

            default:
                // No discount
                break;
        }
    }

    /**
     * ✓ Try-catch-finally properly formatted
     */
    private function attemptPayment(): bool
    {
        try {
            // ✓ Single space after keyword
            return $this->processPayment();
        } catch (RuntimeException $e) {
            // ✓ Catch on same line as closing brace
            $this->logError($e->getMessage());
            return false;
        } finally {
            // ✓ Finally on same line
            $this->cleanup();
        }
    }

    /**
     * ✓ Method chaining properly formatted
     */
    public function buildQuery(): string
    {
        return (new QueryBuilder())
            ->select(['id', 'name', 'email'])
            ->from('users')
            ->where('status', '=', 'active')
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->build();
    }

    /**
     * ✓ Array declaration properly formatted
     */
    public function getConfig(): array
    {
        return [
            'timeout' => 30,
            'retry_attempts' => 3,
            'endpoints' => [
                'api' => 'https://api.example.com',
                'cdn' => 'https://cdn.example.com',
            ],
            'features' => [
                'cache' => true,
                'logging' => true,
                'monitoring' => false,
            ],
        ];
    }

    /**
     * ✓ Closure properly formatted
     */
    public function filterItems(callable $callback): array
    {
        return array_filter($this->items, function (array $item) use ($callback): bool {
            return $callback($item);
        });
    }

    /**
     * ✓ Anonymous class properly formatted
     */
    public function getLogger(): object
    {
        return new class {
            public function log(string $message): void
            {
                echo $message . PHP_EOL;
            }
        };
    }

    // Placeholder methods
    private function applyDiscount(float $percent): void {}
    private function processPayment(): bool
    {
        return true;
    }
    private function logError(string $message): void {}
    private function cleanup(): void {}
}

// ✓ Mock QueryBuilder for example
class QueryBuilder
{
    public function select(array $columns): self
    {
        return $this;
    }
    public function from(string $table): self
    {
        return $this;
    }
    public function where(string $col, string $op, string $val): self
    {
        return $this;
    }
    public function orderBy(string $col, string $dir): self
    {
        return $this;
    }
    public function limit(int $limit): self
    {
        return $this;
    }
    public function build(): string
    {
        return 'SELECT ...';
    }
}
