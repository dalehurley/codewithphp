<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class BenchmarkSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('benchmark_customers')) {
            Schema::create('benchmark_customers', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
            });
        }

        if (!Schema::hasTable('benchmark_orders')) {
            Schema::create('benchmark_orders', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('customer_id')->constrained('benchmark_customers');
            });
        }

        DB::transaction(static function (): void {
            $customers = [];
            $orders = [];

            for ($id = 1; $id <= 40; $id++) {
                $customers[] = ['id' => $id, 'name' => "Customer {$id}"];
                $orders[] = ['id' => $id, 'customer_id' => $id];
            }

            DB::table('benchmark_customers')->upsert($customers, ['id'], ['name']);
            DB::table('benchmark_orders')->upsert($orders, ['id'], ['customer_id']);
        });
    }
}
