<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'team_id',
        'name',
        'email',
        'phone',
        'website',
        'industry',
        'size',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Inverse relationship: Company belongs to a Team
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    // Forward relationships: Company has many Contacts
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }
}

