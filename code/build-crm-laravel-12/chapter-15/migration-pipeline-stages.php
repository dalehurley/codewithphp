<?php

/**
 * Pipeline Stages Table Migration
 * 
 * Creates the pipeline_stages reference table that centralizes
 * stage definitions, probabilities, and configuration.
 * 
 * Key features:
 * - Fixed probabilities for weighted forecasting
 * - Stage type classification (open, closed_won, closed_lost)
 * - WIP limits for Kanban methodology
 * - Sort order for visual pipeline display
 * - Color coding for UI differentiation
 * 
 * Location: database/migrations/YYYY_MM_DD_HHMMSS_create_pipeline_stages_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipeline_stages', function (Blueprint $table) {
            $table->id();
            $table->string('pipeline_name', 100)->default('Sales Pipeline');
            $table->string('stage_name', 100);
            $table->decimal('probability', 5, 2);  // 0.00 to 1.00
            $table->enum('stage_type', ['open', 'closed_won', 'closed_lost']);
            $table->integer('sort_order')->unique();
            $table->integer('wip_limit')->nullable();
            $table->string('color', 7)->default('#6B7280');  // Hex color for UI
            $table->timestamps();

            $table->index(['pipeline_name', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pipeline_stages');
    }
};





