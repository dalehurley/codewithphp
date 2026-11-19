<?php

declare(strict_types=1);

namespace App\Quotes;

final class SeatPrice
{
    private const int CENTS_PER_SEAT = 1250;

    public function totalCents(int $quantity): int
    {
        return self::CENTS_PER_SEAT * $quantity;
    }
}
