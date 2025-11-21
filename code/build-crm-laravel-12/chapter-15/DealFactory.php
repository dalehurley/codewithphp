<?php

/**
 * Deal Factory
 * 
 * Generates realistic test data for deals with:
 * - Stage-specific modifiers (New, In Progress, Won, Lost)
 * - High-value deal variations
 * - Team and company scoping
 * - Automatic stage history creation
 * - Realistic deal names and lead sources
 * 
 * Usage examples:
 * - Deal::factory()->create()
 * - Deal::factory()->won()->create()
 * - Deal::factory()->inStage('In Progress')->create()
 * - Deal::factory()->highValue()->forTeam($team)->create()
 * 
 * Location: database/factories/DealFactory.php
 */

namespace Database\Factories;

use App\Models\Company;
use App\Models\Deal;
use App\Models\PipelineStage;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DealFactory extends Factory
{
    protected $model = Deal::class;

    public function definition(): array
    {
        $stage = PipelineStage::inRandomOrder()->first() 
                 ?? PipelineStage::where('stage_name', 'New')->first();
        
        return [
            'team_id' => Team::factory(),
            'company_id' => Company::factory(),
            'pipeline_stage_id' => $stage->id,
            'owner_id' => User::factory(),
            'name' => $this->faker->randomElement([
                'Enterprise License Deal',
                'Professional Services Contract',
                'Annual Subscription Renewal',
                'Implementation Project',
                'Consulting Services Agreement',
                'Software License Purchase',
                'Support Contract Extension',
            ]) . ' - ' . $this->faker->company(),
            'amount' => $this->faker->randomElement([
                5000, 10000, 25000, 50000, 75000, 100000, 250000, 500000
            ]),
            'probability' => $stage->probability,
            'closing_date' => $this->faker->dateTimeBetween('now', '+90 days'),
            'closed_at' => null,
            'lead_source' => $this->faker->randomElement([
                'Website Form',
                'Referral',
                'Cold Call',
                'Email Campaign',
                'Trade Show',
                'LinkedIn',
                'Partner',
            ]),
            'description' => $this->faker->optional(0.7)->paragraph(),
            'is_won' => false,
        ];
    }

    /**
     * Indicate the deal is in a specific stage.
     */
    public function inStage(string $stageName): static
    {
        return $this->state(function (array $attributes) use ($stageName) {
            $stage = PipelineStage::where('stage_name', $stageName)->first();
            
            return [
                'pipeline_stage_id' => $stage->id,
                'probability' => $stage->probability,
            ];
        });
    }

    /**
     * Indicate the deal is won.
     */
    public function won(): static
    {
        return $this->state(function (array $attributes) {
            $wonStage = PipelineStage::where('stage_name', 'Won')->first();
            
            return [
                'pipeline_stage_id' => $wonStage->id,
                'probability' => 1.00,
                'closed_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
                'is_won' => true,
            ];
        });
    }

    /**
     * Indicate the deal is lost.
     */
    public function lost(): static
    {
        return $this->state(function (array $attributes) {
            $lostStage = PipelineStage::where('stage_name', 'Lost')->first();
            
            return [
                'pipeline_stage_id' => $lostStage->id,
                'probability' => 0.00,
                'closed_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
                'is_won' => false,
            ];
        });
    }

    /**
     * Create a high-value deal.
     */
    public function highValue(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $this->faker->randomElement([
                250000, 500000, 750000, 1000000
            ]),
        ]);
    }

    /**
     * Create a deal for a specific team.
     */
    public function forTeam(Team $team): static
    {
        return $this->state(fn (array $attributes) => [
            'team_id' => $team->id,
            'owner_id' => $team->users()->first()->id ?? User::factory(),
        ]);
    }

    /**
     * Create a deal for a specific company.
     */
    public function forCompany(Company $company): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => $company->id,
            'team_id' => $company->team_id,
        ]);
    }

    /**
     * Configure the factory with an afterCreating hook.
     * 
     * Automatically creates initial stage history record.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Deal $deal) {
            // Create initial history record
            $deal->stageHistory()->create([
                'old_stage_id' => null,
                'new_stage_id' => $deal->pipeline_stage_id,
                'modified_by_user_id' => $deal->owner_id,
                'transition_date' => $deal->created_at,
                'comment' => 'Deal created',
            ]);
        });
    }
}







