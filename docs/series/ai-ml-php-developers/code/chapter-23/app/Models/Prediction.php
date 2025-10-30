<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prediction extends Model
{
    protected $fillable = [
        'service',
        'input_hash',
        'input_data',
        'output_data',
        'latency_ms',
        'cache_hit',
        'status',
        'error_message',
    ];

    protected $casts = [
        'input_data' => 'array',
        'output_data' => 'array',
        'cache_hit' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get cache hit rate for a service.
     */
    public static function cacheHitRate(string $service, int $hours = 24): float
    {
        $since = now()->subHours($hours);

        $total = static::where('service', $service)
            ->where('created_at', '>=', $since)
            ->count();

        if ($total === 0) {
            return 0.0;
        }

        $hits = static::where('service', $service)
            ->where('created_at', '>=', $since)
            ->where('cache_hit', true)
            ->count();

        return round(($hits / $total) * 100, 2);
    }

    /**
     * Get average latency for a service.
     */
    public static function averageLatency(string $service, int $hours = 24): float
    {
        $since = now()->subHours($hours);

        return (float) static::where('service', $service)
            ->where('created_at', '>=', $since)
            ->where('cache_hit', false) // Only non-cached predictions
            ->avg('latency_ms') ?? 0.0;
    }

    /**
     * Get error rate for a service.
     */
    public static function errorRate(string $service, int $hours = 24): float
    {
        $since = now()->subHours($hours);

        $total = static::where('service', $service)
            ->where('created_at', '>=', $since)
            ->count();

        if ($total === 0) {
            return 0.0;
        }

        $errors = static::where('service', $service)
            ->where('created_at', '>=', $since)
            ->where('status', 'error')
            ->count();

        return round(($errors / $total) * 100, 2);
    }
}

