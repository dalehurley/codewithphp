<?php

declare(strict_types=1);

enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
}

final class Order
{
    public string $label {
        get => "Order #{$this->id} is {$this->status->value}.";
    }

    public function __construct(
        public readonly int $id,
        public OrderStatus $status,
    ) {
    }
}
