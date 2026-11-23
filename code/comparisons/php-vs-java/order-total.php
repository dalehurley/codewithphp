<?php
declare(strict_types=1);

final class Order
{
    public function __construct(
        public readonly int $id,
        public readonly int $quantity,
        public readonly int $unitCents,
    ) {
        if ($id < 1 || $id > 9_999_999 || $quantity < 1 || $quantity > 1000 ||
            $unitCents < 0 || $unitCents > 1_000_000) {
            throw new InvalidArgumentException('Order fields are out of range.');
        }
    }

    public function totalCents(): int
    {
        return $this->quantity * $this->unitCents;
    }
}

function wholeNumber(string $value): int
{
    if (preg_match('/\A(?:0|[1-9][0-9]{0,6})\z/', $value) !== 1) {
        throw new InvalidArgumentException('Expected digits for a whole number.');
    }
    return (int) $value;
}

function fail(int $status, string $message): never
{
    fwrite(STDERR, $message . PHP_EOL);
    exit($status);
}

try {
    $id = $argc === 2 ? wholeNumber($argv[1]) : 0;
    if ($id === 0) {
        throw new InvalidArgumentException('Expected a positive order ID.');
    }
} catch (InvalidArgumentException) {
    fail(2, 'Enter one order ID from 1 to 9999999.');
}

$fixture = 'orders.csv';
$source = is_file($fixture) ? @file_get_contents($fixture) : false;
if ($source === false) {
    fail(4, 'Cannot read orders.csv. Run from the directory with the fixture.');
}

$orders = [];
$lines = explode("\n", str_replace("\r\n", "\n", $source));
foreach ($lines as $index => $line) {
    if ($line === '') {
        continue;
    }
    try {
        $fields = explode(',', $line);
        if (count($fields) !== 3) {
            throw new InvalidArgumentException('Expected three fields.');
        }
        $order = new Order(...array_map(wholeNumber(...), $fields));
        if (isset($orders[$order->id])) {
            throw new InvalidArgumentException('Duplicate order ID.');
        }
        $orders[$order->id] = $order;
    } catch (InvalidArgumentException $error) {
        $lineNumber = $index + 1;
        fail(4, "Invalid orders.csv on line {$lineNumber}. " . $error->getMessage());
    }
}

$order = $orders[$id] ?? null;
if ($order === null) {
    fail(3, "Order {$id} was not found.");
}

echo "Order {$id}: {$order->totalCents()} cents", PHP_EOL;
