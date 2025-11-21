<?php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

class TemperatureGuide
{
    public const DETERMINISTIC = 0.0;    // Absolute consistency
    public const FOCUSED = 0.3;          // Minimal variation
    public const BALANCED = 1.0;         // Default, natural
    public const CREATIVE = 1.5;         // More variety
    public const EXPERIMENTAL = 2.0;     // Maximum creativity

    public static function recommend(string $useCase): float
    {
        return match($useCase) {
            // Deterministic tasks
            'data_extraction',
            'json_parsing',
            'structured_output',
            'classification',
            'fact_checking' => self::DETERMINISTIC,

            // Focused but slightly flexible
            'code_review',
            'technical_documentation',
            'translation',
            'summarization' => self::FOCUSED,

            // Balanced natural language
            'general_qa',
            'explanation',
            'tutoring',
            'conversational' => self::BALANCED,

            // Creative tasks
            'brainstorming',
            'content_generation',
            'story_writing',
            'marketing_copy' => self::CREATIVE,

            // Highly experimental
            'ideation',
            'creative_writing',
            'unusual_perspectives' => self::EXPERIMENTAL,

            default => self::BALANCED,
        };
    }

    public static function describe(float $temperature): string
    {
        return match(true) {
            $temperature < 0.3 => 'Highly deterministic - minimal variation',
            $temperature < 0.7 => 'Mostly deterministic - slight variation',
            $temperature < 1.2 => 'Balanced - natural variation',
            $temperature < 1.7 => 'Creative - increased variation',
            default => 'Highly creative - maximum variation',
        };
    }
}

