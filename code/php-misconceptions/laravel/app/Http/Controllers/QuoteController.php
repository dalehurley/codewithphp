<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\QuoteRequest;
use App\Quotes\SeatPrice;
use Illuminate\Http\JsonResponse;

final class QuoteController
{
    public function __invoke(QuoteRequest $request, SeatPrice $price): JsonResponse
    {
        $quantity = $request->quantity();

        return response()->json([
            'quantity' => $quantity,
            'total_cents' => $price->totalCents($quantity),
        ]);
    }
}
