<?php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

class ModelSpecificSampling
{
    public const MODEL_GUIDELINES = [
        'claude-haiku-4-5-20251001' => [
            'deterministic' => ['temperature' => 0.0, 'top_p' => 1.0],
            'balanced' => ['temperature' => 1.0, 'top_p' => 0.9],
            'creative' => ['temperature' => 1.3, 'top_p' => 0.95],
            'note' => 'Haiku is faster and cheaper - good for high-volume tasks',
        ],
        'claude-sonnet-4-5-20250929' => [
            'deterministic' => ['temperature' => 0.0, 'top_p' => 1.0],
            'balanced' => ['temperature' => 1.0, 'top_p' => 0.9],
            'creative' => ['temperature' => 1.5, 'top_p' => 0.95],
            'note' => 'Latest Sonnet model - best quality, use for complex tasks',
        ],
        'claude-opus-4-1-20250805' => [
            'deterministic' => ['temperature' => 0.0, 'top_p' => 1.0],
            'balanced' => ['temperature' => 1.0, 'top_p' => 0.9],
            'creative' => ['temperature' => 1.5, 'top_p' => 0.95],
            'note' => 'Most capable model - use for complex reasoning tasks',
        ],
    ];

    public static function getRecommendedConfig(string $model, string $useCase): array
    {
        $guidelines = self::MODEL_GUIDELINES[$model] ?? self::MODEL_GUIDELINES['claude-sonnet-4-5-20250929'];

        return match($useCase) {
            'extraction', 'parsing', 'classification' => $guidelines['deterministic'],
            'code', 'review', 'documentation' => ['temperature' => 0.3, 'top_p' => 0.8],
            'conversation', 'qa', 'explanation' => $guidelines['balanced'],
            'generation', 'brainstorming', 'creative' => $guidelines['creative'],
            default => $guidelines['balanced'],
        };
    }
}
