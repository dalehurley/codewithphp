<?php

declare(strict_types=1);

// PHP 8.1+ Enum
enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    // Methods in enums
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending Payment',
            self::PROCESSING => 'Processing Order',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function canCancel(): bool
    {
        return match($this) {
            self::PENDING, self::PROCESSING => true,
            default => false,
        };
    }

    public function isComplete(): bool
    {
        return match($this) {
            self::DELIVERED, self::CANCELLED => true,
            default => false,
        };
    }
}

// Usage
echo "=== Order Status Demo ===\n\n";

$status = OrderStatus::PENDING;
echo "Status: {$status->value}\n";
echo "Label: {$status->label()}\n";
echo "Can cancel: " . ($status->canCancel() ? 'Yes' : 'No') . "\n";
echo "Is complete: " . ($status->isComplete() ? 'Yes' : 'No') . "\n\n";

// Process order through statuses
$statuses = [
    OrderStatus::PENDING,
    OrderStatus::PROCESSING,
    OrderStatus::SHIPPED,
    OrderStatus::DELIVERED
];

foreach ($statuses as $status) {
    echo "Order is now: {$status->label()}\n";
    echo "  Can cancel: " . ($status->canCancel() ? 'Yes' : 'No') . "\n";
    echo "  Is complete: " . ($status->isComplete() ? 'Yes' : 'No') . "\n";
}

echo "\n=== All Statuses ===\n";
foreach (OrderStatus::cases() as $status) {
    echo "{$status->name}: {$status->value} - {$status->label()}\n";
}
