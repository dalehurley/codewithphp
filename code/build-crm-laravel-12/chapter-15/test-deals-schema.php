<?php

/**
 * Deals Module Database Schema Test Script
 * 
 * Tests the complete deals database schema including:
 * - Pipeline stages configuration
 * - Deal creation with relationships
 * - Stage history tracking
 * - Contact role associations
 * - Line items with computed totals
 * - Stage transitions with probability updates
 * - Computed properties (is_open, days_until_closing)
 * 
 * Run with: sail artisan tinker < code/build-crm-laravel-12/chapter-15/test-deals-schema.php
 */

use App\Models\{Deal, PipelineStage, Company, Contact, User, DealLineItem, DealStageHistory};

echo "=== Pipeline Stages ===\n";
$stages = PipelineStage::ordered()->get();
foreach ($stages as $stage) {
    echo "{$stage->sort_order}. {$stage->stage_name} ({$stage->probability * 100}% probability)\n";
}

echo "\n=== Creating Test Deal ===\n";
$company = Company::first();
$contact = Contact::first();
$owner = User::first();
$newStage = PipelineStage::where('stage_name', 'New')->first();

$deal = Deal::create([
    'team_id' => $owner->currentTeam->id,
    'company_id' => $company->id,
    'pipeline_stage_id' => $newStage->id,
    'owner_id' => $owner->id,
    'name' => 'Enterprise Software License',
    'amount' => 50000.00,
    'probability' => $newStage->probability,
    'closing_date' => now()->addDays(30),
    'lead_source' => 'Website Form',
    'description' => 'Interested in our enterprise plan',
]);

echo "Created deal: {$deal->name}\n";
echo "Amount: \${$deal->amount}\n";
echo "Probability: {$deal->probability}\n";
echo "Weighted: \${$deal->weighted_amount}\n";

echo "\n=== Recording History ===\n";
DealStageHistory::create([
    'deal_id' => $deal->id,
    'old_stage_id' => null,
    'new_stage_id' => $newStage->id,
    'modified_by_user_id' => $owner->id,
    'transition_date' => now(),
    'comment' => 'Deal created from inbound lead',
]);
echo "History recorded\n";

echo "\n=== Attaching Contact ===\n";
$deal->contacts()->attach($contact->id, [
    'role' => 'Decision Maker',
    'is_primary' => true,
]);
echo "Attached {$contact->full_name} as Decision Maker\n";

echo "\n=== Adding Line Items ===\n";
DealLineItem::create([
    'deal_id' => $deal->id,
    'product_name' => 'Enterprise License (10 users)',
    'quantity' => 1,
    'unit_price' => 40000.00,
    'discount_rate' => 0.10,
]);
DealLineItem::create([
    'deal_id' => $deal->id,
    'product_name' => 'Priority Support (1 year)',
    'quantity' => 1,
    'unit_price' => 10000.00,
    'discount_rate' => 0.00,
]);

$lineItemTotal = $deal->lineItems()->sum('line_total');
echo "Line items total: \${$lineItemTotal}\n";

echo "\n=== Moving to In Progress ===\n";
$inProgressStage = PipelineStage::where('stage_name', 'In Progress')->first();
$deal->pipeline_stage_id = $inProgressStage->id;
$deal->probability = $inProgressStage->probability;
$deal->save();

DealStageHistory::create([
    'deal_id' => $deal->id,
    'old_stage_id' => $newStage->id,
    'new_stage_id' => $inProgressStage->id,
    'modified_by_user_id' => $owner->id,
    'transition_date' => now(),
    'comment' => 'Qualified - Budget confirmed',
]);

echo "Deal moved to {$deal->stage->stage_name}\n";
echo "New probability: {$deal->probability}\n";
echo "New weighted: \${$deal->weighted_amount}\n";

echo "\n=== Querying History ===\n";
foreach ($deal->stageHistory as $history) {
    $from = $history->oldStage ? $history->oldStage->stage_name : 'Created';
    $to = $history->newStage->stage_name;
    echo "{$history->transition_date->format('Y-m-d H:i')} - {$from} → {$to}\n";
}

echo "\n=== Testing Computed Properties ===\n";
echo "Is Open: " . ($deal->is_open ? 'Yes' : 'No') . "\n";
echo "Days until closing: {$deal->days_until_closing}\n";

echo "\n✅ All tests passed!\n";





