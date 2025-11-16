<?php

namespace App\Models;

use App\Enums\DealStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deal extends Model
{
    protected $fillable = [
        'team_id',
        'company_id',
        'contact_id',
        'title',
        'amount',
        'currency',
        'status',
        'expected_close_date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => DealStatus::class,
        'expected_close_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class)->nullable();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}





