<?php

declare(strict_types=1);

// PHP 8.1+ enums (similar to Python's Enum class)
// Enums provide type-safe constants with methods

// Backed enum (with string values)
enum Status: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    // Enums can have methods
    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending Review',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
        };
    }
}

// Pure enum (without values)
enum Permission
{
    case READ;
    case WRITE;
    case DELETE;

    public function canModify(): bool
    {
        return match ($this) {
            self::READ => false,
            self::WRITE => true,
            self::DELETE => true,
        };
    }
}

// Using enums
function processStatus(Status $status): string
{
    return match ($status) {
        Status::PENDING => 'Processing...',
        Status::APPROVED => 'Completed!',
        Status::REJECTED => 'Failed.',
    };
}

// Example usage
$status = Status::PENDING;
echo "Status: {$status->value}\n";  // Output: Status: pending
echo "Label: {$status->getLabel()}\n";  // Output: Label: Pending Review
echo processStatus($status) . "\n";  // Output: Processing...

// Enums provide type safety
function updateStatus(Status $newStatus): void
{
    echo "Updating to: {$newStatus->value}\n";
}

updateStatus(Status::APPROVED);  // ✅ Works
// updateStatus('approved');  // ❌ Type error: Expected Status, got string

// Enums can be used in type hints
class Order
{
    public function __construct(
        public readonly string $id,
        public Status $status = Status::PENDING
    ) {}

    public function approve(): void
    {
        $this->status = Status::APPROVED;
    }
}

$order = new Order('ORD-123');
echo "Order Status: {$order->status->value}\n";  // Output: Order Status: pending

$order->approve();
echo "Order Status: {$order->status->value}\n";  // Output: Order Status: approved

// Enums are commonly used in Laravel for:
// - Status fields in models
// - Permissions and roles
// - Configuration values
// - Type-safe constants

