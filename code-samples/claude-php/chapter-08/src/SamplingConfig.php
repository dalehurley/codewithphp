<?php

declare(strict_types=1);

namespace ClaudePhp\Temperature;

/**
 * SamplingConfig - Configure sampling parameters
 */
class SamplingConfig
{
    /**
     * Get temperature for deterministic output
     */
    public static function deterministic(): array
    {
        return ['temperature' => 0.0];
    }

    /**
     * Get temperature for factual responses
     */
    public static function factual(): array
    {
        return ['temperature' => 0.3];
    }

    /**
     * Get temperature for balanced responses
     */
    public static function balanced(): array
    {
        return ['temperature' => 0.7];
    }

    /**
     * Get temperature for creative output
     */
    public static function creative(): array
    {
        return ['temperature' => 1.0];
    }

    /**
     * Select temperature based on task type
     */
    public static function forTask(string $taskType): array
    {
        return match ($taskType) {
            'code' => self::deterministic(),
            'analysis' => self::factual(),
            'general' => self::balanced(),
            'creative' => self::creative(),
            default => self::balanced()
        };
    }
}
