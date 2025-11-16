<?php

/**
 * PipelineStage Model
 * 
 * Manages sales pipeline stage configuration.
 * 
 * Key features:
 * - Centralizes stage definitions and probabilities
 * - Supports multiple pipelines (enterprise, SMB, etc.)
 * - Query scopes for filtering (open, ordered)
 * - WIP limits for Kanban methodology
 * - Color coding for UI
 * 
 * Design notes:
 * - Probability is fixed per stage (not user-editable)
 * - Sort order determines Kanban column sequence
 * - Stage type differentiates open vs terminal states
 * 
 * Location: app/Models/PipelineStage.php
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipelineStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'pipeline_name',
        'stage_name',
        'probability',
        'stage_type',
        'sort_order',
        'wip_limit',
        'color',
    ];

    protected $casts = [
        'probability' => 'decimal:2',
        'sort_order' => 'integer',
        'wip_limit' => 'integer',
    ];

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'pipeline_stage_id');
    }

    public function scopeOpen($query)
    {
        return $query->where('stage_type', 'open');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}





