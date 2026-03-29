---
title: "10: Temperature, Top-P & Sampling Parameters"
description: "Master sampling parameters to control creativity, determinism, and output diversity in AI responses"
series: "openai-php"
chapter: 10
order: 10
difficulty: "Intermediate"
prerequisites:
  - "/series/openai-php/chapters/09-streaming-responses"
  - "Understanding of probability concepts"
---

![Temperature, Top-P & Sampling Parameters](/images/openai-php/chapter-10-parameters-hero-full.webp)

[Home](/series/openai-php) > [Chapter 09](/series/openai-php/chapters/09-streaming-responses) > Temperature, Top-P & Sampling Parameters

# Chapter 10: Temperature, Top-P & Sampling Parameters

<span class="difficulty-badge difficulty-intermediate">Intermediate</span>
<span class="time-badge">50-60 minutes</span>

## Overview

The same prompt can produce wildly different outputs depending on sampling parameters. Understanding temperature, top-p, frequency penalty, and presence penalty is crucial for controlling AI behavior—from perfectly deterministic outputs to creative variations.

## What You'll Learn

- 🌡️ **Temperature**: Control randomness and creativity
- 🎯 **Top-P (Nucleus Sampling)**: Alternative randomness control
- 🔁 **Frequency Penalty**: Reduce repetition
- ✨ **Presence Penalty**: Encourage topic diversity
- ⚖️ **Parameter Combinations**: Find optimal settings
- 🎨 **Use Case Matching**: Choose parameters for different scenarios

## Prerequisites

- ✅ Completed Chapters 01-09
- ✅ Basic understanding of probability
- ✅ Experience with API parameters

---

## Temperature Parameter

### Understanding Temperature

Temperature controls randomness in token selection (0.0 to 2.0):

```php
<?php

class TemperatureExplorer
{
    private \OpenAI\Client $client;

    public function __construct(string $apiKey)
    {
        $this->client = \OpenAI::client($apiKey);
    }

    public function compareTemperatures(string $prompt, array $temperatures): array
    {
        $results = [];

        foreach ($temperatures as $temp) {
            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => $temp,
                'max_tokens' => 100,
            ]);

            $results[$temp] = $response->choices[0]->message->content;
        }

        return $results;
    }

    public function getDeterministicResponse(string $prompt): string
    {
        $response = $this->client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.0, // Deterministic
            'seed' => 12345, // For even more consistency
        ]);

        return $response->choices[0]->message->content;
    }

    public function getCreativeResponse(string $prompt): string
    {
        $response = $this->client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 1.2, // Creative
        ]);

        return $response->choices[0]->message->content;
    }
}

// Usage
$explorer = new TemperatureExplorer($_ENV['OPENAI_API_KEY']);

// Compare different temperatures
$results = $explorer->compareTemperatures(
    "Write a tagline for a PHP framework",
    [0.0, 0.3, 0.7, 1.0, 1.5]
);

foreach ($results as $temp => $response) {
    echo "Temperature {$temp}:\n{$response}\n\n";
}
```

### Temperature Guidelines

```php
<?php

class TemperatureGuide
{
    public const DETERMINISTIC = 0.0;    // Exact, factual answers
    public const FACTUAL = 0.3;          // Slight variation, still accurate
    public const BALANCED = 0.7;         // Default, good balance
    public const CREATIVE = 1.0;         // More diverse outputs
    public const VERY_CREATIVE = 1.5;    // High variation
    public const EXPERIMENTAL = 2.0;     // Maximum randomness

    public static function forUseCase(string $useCase): float
    {
        return match($useCase) {
            'code_generation' => self::DETERMINISTIC,
            'data_extraction' => self::DETERMINISTIC,
            'classification' => self::FACTUAL,
            'summarization' => self::FACTUAL,
            'general_chat' => self::BALANCED,
            'content_writing' => self::CREATIVE,
            'creative_writing' => self::VERY_CREATIVE,
            'brainstorming' => self::VERY_CREATIVE,
            default => self::BALANCED,
        };
    }

    public static function getDescription(float $temperature): string
    {
        return match(true) {
            $temperature === 0.0 => "Deterministic - Same output every time",
            $temperature < 0.5 => "Low - Focused, predictable, factual",
            $temperature < 0.9 => "Medium - Balanced creativity and coherence",
            $temperature < 1.5 => "High - Creative, diverse, less predictable",
            default => "Very High - Maximum creativity, may be inconsistent",
        };
    }
}
```

---

## Top-P (Nucleus Sampling)

### Understanding Top-P

Top-P selects from the smallest set of tokens whose cumulative probability exceeds P:

```php
<?php

class TopPController
{
    private \OpenAI\Client $client;

    public function __construct(string $apiKey)
    {
        $this->client = \OpenAI::client($apiKey);
    }

    public function compareTopP(string $prompt, array $topPValues): array
    {
        $results = [];

        foreach ($topPValues as $topP) {
            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'top_p' => $topP,
                'temperature' => 1.0, // Let top_p control randomness
            ]);

            $results[$topP] = $response->choices[0]->message->content;
        }

        return $results;
    }

    public function focused(string $prompt): string
    {
        // Top-p = 0.1: Only most likely tokens
        return $this->generate($prompt, topP: 0.1, temperature: 1.0);
    }

    public function balanced(string $prompt): string
    {
        // Top-p = 0.5: Moderate token selection
        return $this->generate($prompt, topP: 0.5, temperature: 1.0);
    }

    public function diverse(string $prompt): string
    {
        // Top-p = 0.9: Wide token selection
        return $this->generate($prompt, topP: 0.9, temperature: 1.0);
    }

    private function generate(string $prompt, float $topP, float $temperature): string
    {
        $response = $this->client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'top_p' => $topP,
            'temperature' => $temperature,
        ]);

        return $response->choices[0]->message->content;
    }
}
```

### Temperature vs Top-P

```php
<?php

class SamplingStrategy
{
    /**
     * When to use Temperature
     */
    public static function useTemperature(): string
    {
        return "Use temperature when:\n" .
               "- You want simple randomness control\n" .
               "- You need consistent behavior across models\n" .
               "- You're familiar with temperature settings\n" .
               "- You want gradual control (0.0 to 2.0)";
    }

    /**
     * When to use Top-P
     */
    public static function useTopP(): string
    {
        return "Use top-p when:\n" .
               "- You want more sophisticated sampling\n" .
               "- You need better quality at high randomness\n" .
               "- You want to avoid very unlikely tokens\n" .
               "- You're fine-tuning creative outputs";
    }

    /**
     * Recommended: Don't use both at once
     */
    public static function getBestPractice(): string
    {
        return "Best practice: Use temperature OR top-p, not both.\n" .
               "Set one and leave the other at default (1.0).";
    }
}
```

---

## Frequency and Presence Penalties

### Frequency Penalty

Reduces likelihood of repeating the same tokens based on how often they appear:

```php
<?php

class PenaltyController
{
    private \OpenAI\Client $client;

    public function __construct(string $apiKey)
    {
        $this->client = \OpenAI::client($apiKey);
    }

    public function withFrequencyPenalty(
        string $prompt,
        float $penalty = 0.5
    ): string {
        $response = $this->client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'frequency_penalty' => $penalty, // -2.0 to 2.0
        ]);

        return $response->choices[0]->message->content;
    }

    public function withPresencePenalty(
        string $prompt,
        float $penalty = 0.5
    ): string {
        $response = $this->client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'presence_penalty' => $penalty, // -2.0 to 2.0
        ]);

        return $response->choices[0]->message->content;
    }

    public function reduceRepetition(string $prompt): string
    {
        // Combine penalties to reduce repetition
        $response = $this->client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'frequency_penalty' => 0.8,  // Strongly discourage repetition
            'presence_penalty' => 0.6,   // Encourage new topics
        ]);

        return $response->choices[0]->message->content;
    }
}

// Example: List generation without repetition
$controller = new PenaltyController($_ENV['OPENAI_API_KEY']);

$response = $controller->reduceRepetition(
    "List 20 unique PHP frameworks. Don't repeat any."
);
```

### Penalty Guidelines

```php
<?php

class PenaltyGuide
{
    public static function frequencyPenalty(string $goal): float
    {
        return match($goal) {
            'allow_repetition' => -0.5,        // Negative: encourage repetition
            'neutral' => 0.0,                  // Default: no penalty
            'slight_reduction' => 0.3,         // Slight discouragement
            'moderate_reduction' => 0.7,       // Moderate discouragement
            'strong_reduction' => 1.2,         // Strong discouragement
            'maximum_reduction' => 2.0,        // Maximum penalty
            default => 0.0,
        };
    }

    public static function presencePenalty(string $goal): float
    {
        return match($goal) {
            'stay_on_topic' => -0.5,           // Negative: stay focused
            'neutral' => 0.0,                  // Default: no penalty
            'some_variety' => 0.3,             // Slight encouragement
            'topic_diversity' => 0.7,          // Moderate encouragement
            'maximum_diversity' => 1.5,        // Strong encouragement
            default => 0.0,
        };
    }
}
```

---

## Parameter Optimization

```php
<?php

/**
 * Find optimal parameters for specific use cases
 */

class ParameterOptimizer
{
    private \OpenAI\Client $client;

    public function __construct(string $apiKey)
    {
        $this->client = \OpenAI::client($apiKey);
    }

    public function findOptimal(
        string $prompt,
        array $parameterRanges,
        callable $evaluator,
        int $samplesPerConfig = 3
    ): array {
        $bestConfig = null;
        $bestScore = -INF;
        $results = [];

        foreach ($this->generateConfigs($parameterRanges) as $config) {
            $scores = [];

            for ($i = 0; $i < $samplesPerConfig; $i++) {
                $response = $this->client->chat()->create(array_merge([
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                ], $config));

                $output = $response->choices[0]->message->content;
                $scores[] = $evaluator($output);
            }

            $avgScore = array_sum($scores) / count($scores);
            $results[] = [
                'config' => $config,
                'score' => $avgScore,
            ];

            if ($avgScore > $bestScore) {
                $bestScore = $avgScore;
                $bestConfig = $config;
            }
        }

        return [
            'best_config' => $bestConfig,
            'best_score' => $bestScore,
            'all_results' => $results,
        ];
    }

    private function generateConfigs(array $ranges): \Generator
    {
        // Simple grid search
        foreach ($ranges['temperature'] ?? [0.7] as $temp) {
            foreach ($ranges['top_p'] ?? [1.0] as $topP) {
                foreach ($ranges['frequency_penalty'] ?? [0.0] as $freqPen) {
                    foreach ($ranges['presence_penalty'] ?? [0.0] as $presPen) {
                        yield [
                            'temperature' => $temp,
                            'top_p' => $topP,
                            'frequency_penalty' => $freqPen,
                            'presence_penalty' => $presPen,
                        ];
                    }
                }
            }
        }
    }
}

// Usage: Find best parameters for creative writing
$optimizer = new ParameterOptimizer($_ENV['OPENAI_API_KEY']);

$result = $optimizer->findOptimal(
    prompt: "Write a unique opening sentence for a sci-fi novel",
    parameterRanges: [
        'temperature' => [0.8, 1.0, 1.2],
        'frequency_penalty' => [0.0, 0.5, 1.0],
    ],
    evaluator: function($output) {
        // Score based on length, uniqueness, etc.
        $score = 0;
        $score += strlen($output) > 50 ? 1 : 0;  // Long enough
        $score += str_word_count($output) > 8 ? 1 : 0;  // Complex
        $score += preg_match('/[.!?]$/', $output) ? 1 : 0;  // Proper ending
        return $score;
    },
    samplesPerConfig: 5
);

print_r($result['best_config']);
```

---

## Use Case Configurations

```php
<?php

class UseCaseConfigs
{
    public static function codeGeneration(): array
    {
        return [
            'temperature' => 0.2,
            'top_p' => 1.0,
            'frequency_penalty' => 0.0,
            'presence_penalty' => 0.0,
        ];
    }

    public static function dataExtraction(): array
    {
        return [
            'temperature' => 0.0,  // Deterministic
            'top_p' => 1.0,
            'frequency_penalty' => 0.0,
            'presence_penalty' => 0.0,
        ];
    }

    public static function creativeWriting(): array
    {
        return [
            'temperature' => 1.0,
            'top_p' => 1.0,
            'frequency_penalty' => 0.7,  // Avoid repetition
            'presence_penalty' => 0.5,   // Encourage variety
        ];
    }

    public static function summarization(): array
    {
        return [
            'temperature' => 0.3,
            'top_p' => 1.0,
            'frequency_penalty' => 0.0,
            'presence_penalty' => 0.0,
        ];
    }

    public static function brainstorming(): array
    {
        return [
            'temperature' => 1.2,
            'top_p' => 1.0,
            'frequency_penalty' => 1.0,  // Prevent repeating ideas
            'presence_penalty' => 1.0,   // Encourage diverse topics
        ];
    }

    public static function chatbot(): array
    {
        return [
            'temperature' => 0.8,
            'top_p' => 1.0,
            'frequency_penalty' => 0.3,  // Slight variety
            'presence_penalty' => 0.2,   // Some topic variation
        ];
    }
}
```

---

## Exercises

### Exercise 1: Parameter Playground

Build a web interface that:
1. Allows adjusting all parameters
2. Shows side-by-side comparisons
3. Saves favorite configurations
4. Tracks which params affect output most

### Exercise 2: Auto-Tuner

Create a system that:
1. Automatically tests parameter combinations
2. Scores outputs on multiple criteria
3. Finds optimal settings for use case
4. Provides confidence metrics

### Exercise 3: Consistency Tester

Build a tool that:
1. Tests output consistency at different temperatures
2. Measures variance
3. Finds minimum temperature for desired variety
4. Generates reports

### Exercise 4: Penalty Analyzer

Create:
1. Tool to detect repetition in outputs
2. Automatic penalty adjustment
3. Before/after comparisons
4. Repetition scoring

---

## Key Takeaways

- ✅ Temperature controls randomness: 0.0 = deterministic, 2.0 = maximum creativity
- ✅ Top-P provides more sophisticated randomness control
- ✅ Use temperature OR top-p, not both simultaneously
- ✅ Frequency penalty reduces token repetition
- ✅ Presence penalty encourages topic diversity
- ✅ Different use cases need different parameter configurations
- ✅ Test and optimize parameters for your specific needs

---

## Next Steps

👉 **[Chapter 11: JSON Mode & Structured Outputs](/series/openai-php/chapters/11-json-mode-structured-outputs)**

---

[← Previous: Chapter 09](/series/openai-php/chapters/09-streaming-responses) | [Next: Chapter 11 →](/series/openai-php/chapters/11-json-mode-structured-outputs)
