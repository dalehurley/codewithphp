<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->string('service'); // 'sentiment', 'recommendation', etc.
            $table->text('input_hash'); // MD5 of input for cache key tracking
            $table->json('input_data')->nullable(); // Original input (optional)
            $table->json('output_data'); // Prediction result
            $table->integer('latency_ms'); // Time taken to compute
            $table->boolean('cache_hit')->default(false);
            $table->string('status')->default('success'); // 'success' or 'error'
            $table->text('error_message')->nullable();
            $table->timestamps();

            // Indexes for monitoring queries
            $table->index('service');
            $table->index('created_at');
            $table->index(['service', 'cache_hit']);
            $table->index(['service', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predictions');
    }
};
