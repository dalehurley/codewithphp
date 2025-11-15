<?php

/**
 * DealStageHistory Model
 * 
 * Captures immutable audit trail of stage transitions.
 * 
 * Key features:
 * - Records every stage change with who, when, why
 * - Enables velocity metrics (time in stage, cycle time)
 * - Protected from updates and deletes
 * - Indexed for temporal queries
 * 
 * Design notes:
 * - old_stage_id nullable (first creation has no previous stage)
 * - UPDATED_AT disabled (immutable records)
 * - booted() method prevents modifications
 * - Used for sales analytics and compliance auditing
 * 
 * Location: app/Models/DealStageHistory.php
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealStageHistory extends Model
{
    use HasFactory;

    const UPDATED_AT = null;  // Immutable records don't update

    protected $fillable = [
        'deal_id',
        'old_stage_id',
        'new_stage_id',
        'modified_by_user_id',
        'transition_date',
        'comment',
    ];

    protected $casts = [
        'transition_date' => 'datetime',
    ];

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function oldStage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'old_stage_id');
    }

    public function newStage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'new_stage_id');
    }

    public function modifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modified_by_user_id');
    }

    // Prevent updates and deletes
    protected static function booted()
    {
        static::updating(fn() => false);
        static::deleting(fn() => false);
    }
}

