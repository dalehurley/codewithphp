<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Conversation Model
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $system_prompt
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'system_prompt',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the user that owns the conversation
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all messages in the conversation
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get the last message in the conversation
     */
    public function lastMessage(): ?Message
    {
        return $this->messages()->latest()->first();
    }

    /**
     * Get conversation token count
     */
    public function getTotalTokensAttribute(): int
    {
        return $this->messages()
            ->whereNotNull('metadata->tokens')
            ->get()
            ->sum(fn($msg) => ($msg->metadata['tokens']['input_tokens'] ?? 0) + ($msg->metadata['tokens']['output_tokens'] ?? 0));
    }
}
