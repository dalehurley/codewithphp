<?php

declare(strict_types=1);

namespace Tests\Feature;

use Database\Seeders\BenchmarkSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class BenchmarkTest extends TestCase
{
    use RefreshDatabase;

    public function test_both_routes_return_the_same_orders_with_different_query_counts(): void
    {
        $this->seed(BenchmarkSeeder::class);
        DB::enableQueryLog();

        try {
            $perRow = $this->getJson('/_benchmark/orders/per-row')->assertOk()->assertJsonCount(40);
            self::assertCount(41, DB::getQueryLog());

            DB::flushQueryLog();
            $join = $this->getJson('/_benchmark/orders/join')->assertOk();
            self::assertCount(1, DB::getQueryLog());
            self::assertSame($perRow->getContent(), $join->getContent());
        } finally {
            DB::disableQueryLog();
            DB::flushQueryLog();
        }
    }
}
