<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class QuoteTest extends TestCase
{
    public function test_a_valid_quantity_gets_a_quote_in_cents(): void
    {
        $this->getJson('/quotes?quantity=3')
            ->assertOk()
            ->assertExactJson(['quantity' => 3, 'total_cents' => 3750]);
    }

    public function test_invalid_quantities_return_a_validation_error(): void
    {
        foreach (['/quotes', '/quotes?quantity=0', '/quotes?quantity=101', '/quotes?quantity=3.5'] as $url) {
            $this->getJson($url)
                ->assertUnprocessable()
                ->assertJsonValidationErrors('quantity');
        }
    }
}
