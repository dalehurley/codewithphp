<?php

declare(strict_types=1);

namespace CodeWithPHP\Claude;

class SamplingPresets
{
    public const STRATEGIES = [
        'deterministic' => [
            'temperature' => 0.0,
            'top_p' => 1.0,
            'description' => 'Maximum consistency, minimal variation',
            'best_for' => ['data extraction', 'JSON parsing', 'classification']
        ],

        'focused' => [
            'temperature' => 0.3,
            'top_p' => 0.8,
            'description' => 'Consistent but not robotic',
            'best_for' => ['code review', 'documentation', 'translation']
        ],

        'balanced' => [
            'temperature' => 1.0,
            'top_p' => 0.9,
            'description' => 'Natural variation, good for most tasks',
            'best_for' => ['conversation', 'explanation', 'Q&A']
        ],

        'creative' => [
            'temperature' => 1.3,
            'top_p' => 0.95,
            'description' => 'Increased creativity while staying coherent',
            'best_for' => ['brainstorming', 'content generation', 'writing']
        ],

        'experimental' => [
            'temperature' => 1.8,
            'top_p' => 1.0,
            'description' => 'Maximum creativity and exploration',
            'best_for' => ['ideation', 'creative writing', 'novel solutions']
        ],
    ];

    public static function get(string $name): SamplingStrategy
    {
        $config = self::STRATEGIES[$name] ?? self::STRATEGIES['balanced'];

        return new SamplingStrategy(
            temperature: $config['temperature'],
            topP: $config['top_p'],
            description: $config['description'],
            bestFor: $config['best_for']
        );
    }

    public static function forTask(string $task): SamplingStrategy
    {
        $task = strtolower($task);

        foreach (self::STRATEGIES as $name => $config) {
            foreach ($config['best_for'] as $useCase) {
                if (str_contains($task, $useCase)) {
                    return self::get($name);
                }
            }
        }

        return self::get('balanced');
    }
}

