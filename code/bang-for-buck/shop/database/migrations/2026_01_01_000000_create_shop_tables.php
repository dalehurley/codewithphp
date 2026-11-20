<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name');
        });

        Schema::create('products', function (Blueprint $table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->foreignId('category_id')->constrained();
            $table->string('title');
            $table->unsignedInteger('price_cents');
            $table->index(['category_id', 'id']);
        });

        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('run_id', 64);
            $table->string('reference', 64);
            $table->foreignId('product_id')->constrained();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('unit_price_cents');
            $table->unsignedInteger('shipping_cents');
            $table->unsignedInteger('total_cents');
            $table->timestampTz('created_at', 6);
            $table->unique(['run_id', 'reference']);
        });

        Schema::create('exports', function (Blueprint $table): void {
            $table->id();
            $table->string('run_id', 64);
            $table->string('reference', 64);
            $table->foreignId('category_id')->constrained();
            $table->string('state');
            $table->timestampTz('accepted_at', 6);
            $table->timestampTz('started_at', 6)->nullable();
            $table->timestampTz('finished_at', 6)->nullable();
            $table->unsignedInteger('rows_written')->nullable();
            $table->unique(['run_id', 'reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exports');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
