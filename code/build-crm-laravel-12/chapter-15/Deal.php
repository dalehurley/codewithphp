<?php

/**
 * Deal (Opportunity) Model
 * 
 * Represents a sales opportunity moving through the pipeline.
 * 
 * Key features:
 * - Team-scoped (HasTeamScope trait)
 * - Soft deletes for recovery
 * - Relationships to company, contacts, owner, stage, line items
 * - Computed properties (is_open, is_closed, days_until_closing)
 * - Query scopes (open, closed, won)
 * - Stage history tracking
 * 
 * Data integrity:
 * - Probability must sync with pipeline_stage probability
 * - Weighted amount computed automatically by database
 * - Amount should equal sum of line items (enforced in controller)
 * 
 * Location: app/Models/Deal.php
 */

namespace App\Models;

use App\Models\Traits\HasTeamScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deal extends Model
{
    use HasFactory, HasTeamScope, SoftDeletes;

    protected $fillable = [
        'team_id',
        'company_id',
        'pipeline_stage_id',
        'owner_id',
        'name',
        'amount',
        'probability',
        'closing_date',
        'closed_at',
        'lead_source',
        'description',
        'is_won',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'probability' => 'decimal:2',
        'weighted_amount' => 'decimal:2',
        'closing_date' => 'date',
        'closed_at' => 'date',
        'is_won' => 'boolean',
    ];

    // Relationships
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'deal_contact_role')
            ->withPivot('role', 'is_primary')
            ->withTimestamps();
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(DealLineItem::class);
    }

    public function stageHistory(): HasMany
    {
        return $this->hasMany(DealStageHistory::class);
    }

    // Computed properties
    public function getIsOpenAttribute(): bool
    {
        return $this->stage->stage_type === 'open';
    }

    public function getIsClosedAttribute(): bool
    {
        return in_array($this->stage->stage_type, ['closed_won', 'closed_lost']);
    }

    public function getDaysUntilClosingAttribute(): int
    {
        return now()->diffInDays($this->closing_date, false);
    }

    // Query scopes
    public function scopeOpen($query)
    {
        return $query->whereHas('stage', fn($q) => $q->where('stage_type', 'open'));
    }

    public function scopeClosed($query)
    {
        return $query->whereHas('stage', fn($q) => $q->whereIn('stage_type', ['closed_won', 'closed_lost']));
    }

    public function scopeWon($query)
    {
        return $query->where('is_won', true);
    }
}







