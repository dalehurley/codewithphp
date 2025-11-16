<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = [
        'team_id',
        'deal_id',
        'contact_id',
        'title',
        'description',
        'status',
        'due_date',
        'completed_at',
    ];

    protected $casts = [
        'status' => TaskStatus::class,
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class)->nullable();
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class)->nullable();
    }

    // Query scope: Get only completed tasks
    public function scopeCompleted($query)
    {
        return $query->where('status', TaskStatus::Completed);
    }

    // Query scope: Get only open/in-progress tasks
    public function scopeOpen($query)
    {
        return $query->whereIn('status', [TaskStatus::Open, TaskStatus::InProgress]);
    }
}





