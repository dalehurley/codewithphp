<?php

/**
 * Deals Table Migration
 * 
 * Creates the core deals (opportunities) table with:
 * - Stage, amount, probability, closing date
 * - Relationships to companies, users, teams
 * - Computed weighted_amount column
 * - Strategic indexes for Kanban queries
 * - Soft deletes for recovery
 * 
 * Critical design decisions:
 * - Probability denormalized from pipeline_stages for query performance
 * - Weighted amount computed automatically (amount × probability)
 * - Composite indexes for team/owner + stage queries
 * 
 * Location: database/migrations/YYYY_MM_DD_HHMMSS_create_deals_table.php
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pipeline_stage_id')->constrained()->restrictOnDelete();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            
            $table->string('name');
            $table->decimal('amount', 18, 2);
            $table->decimal('probability', 5, 2);  // Denormalized from stage
            $table->decimal('weighted_amount', 18, 2)->storedAs('amount * probability');
            $table->date('closing_date');
            $table->date('closed_at')->nullable();
            
            $table->string('lead_source')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_won')->default(false);
            
            $table->softDeletes();
            $table->timestamps();

            // Optimization indexes
            $table->index(['team_id', 'pipeline_stage_id']);
            $table->index(['owner_id', 'pipeline_stage_id']);
            $table->index('closing_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};







