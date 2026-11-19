<?php

declare(strict_types=1);

final readonly class UnitPrice
{
    public function __construct(public int $cents)
    {
        if ($cents < 0) {
            throw new InvalidArgumentException('Unit price must be non-negative.');
        }
    }

    public function forQuantity(int $quantity): int
    {
        return $this->cents * $quantity;
    }
}
