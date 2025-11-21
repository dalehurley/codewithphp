<?php

/**
 * Pipeline Stages Seeder
 * 
 * Seeds the four foundational pipeline stages:
 * - New (10% probability) - Initial lead capture
 * - In Progress (50%) - Qualified, active negotiation
 * - Won (100%) - Closed successfully
 * - Lost (0%) - Closed unsuccessfully
 * 
 * Features:
 * - Color-coded for visual differentiation
 * - WIP limit on In Progress (10 deals max)
 * - Probabilities aligned with B2B sales conversion rates
 * 
 * Location: database/seeders/PipelineStageSeeder.php
 * Register in: database/seeders/DatabaseSeeder.php
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PipelineStageSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            [
                'pipeline_name' => 'Sales Pipeline',
                'stage_name' => 'New',
                'probability' => 0.10,
                'stage_type' => 'open',
                'sort_order' => 1,
                'wip_limit' => null,
                'color' => '#3B82F6',  // Blue
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pipeline_name' => 'Sales Pipeline',
                'stage_name' => 'In Progress',
                'probability' => 0.50,
                'stage_type' => 'open',
                'sort_order' => 2,
                'wip_limit' => 10,  // Limit active negotiations
                'color' => '#F59E0B',  // Amber
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pipeline_name' => 'Sales Pipeline',
                'stage_name' => 'Won',
                'probability' => 1.00,
                'stage_type' => 'closed_won',
                'sort_order' => 3,
                'wip_limit' => null,
                'color' => '#10B981',  // Green
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pipeline_name' => 'Sales Pipeline',
                'stage_name' => 'Lost',
                'probability' => 0.00,
                'stage_type' => 'closed_lost',
                'sort_order' => 4,
                'wip_limit' => null,
                'color' => '#EF4444',  // Red
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('pipeline_stages')->insert($stages);
    }
}







