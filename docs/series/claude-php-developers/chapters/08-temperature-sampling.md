---
title: "08: Temperature and Sampling Parameters"
description: "Control Claude's creativity and determinism through temperature, top_p, and top_k parameters. Learn when to use deterministic vs creative outputs."
series: "claude-php-developers"
chapter: 8
order: 8
difficulty: "Expert"
prerequisites:
  - "Chapter 00-07 completed"
  - "Understanding of probability and randomness"
  - "Familiarity with API request configuration"
---

![08: Temperature and Sampling Parameters](/images/claude-php/chapter-08-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 08</span>
</div>

# Chapter 08: Temperature and Sampling Parameters

## Overview

Temperature, top_p, and top_k are the control knobs that determine how predictable or creative Claude's outputs will be. Understanding these parameters transforms your AI from a one-size-fits-all assistant into a precision tool that produces exactly the right balance of consistency and creativity for each use case.

This chapter teaches you how sampling works under the hood, when to use high vs low temperature, how to combine temperature with top_p and top_k, and how to choose the right parameters for different applications.

By the end, you'll confidently configure Claude for deterministic tasks like data extraction, creative tasks like content generation, and everything in between.

## Prerequisites

Before starting, ensure you understand:

- ✓ Basic Claude API usage (Chapters 00-03)
- ✓ Prompt engineering fundamentals (Chapter 05)
- ✓ System prompts and role definition (Chapter 07)
- ✓ Basic probability concepts

**Estimated Time**: 45-60 minutes

## How Language Model Sampling Works

### Token Prediction Fundamentals

```php
<?php
# filename: examples/01-understanding-sampling.php
declare(strict_types=1);

/**
 * Conceptual demonstration of how Claude predicts the next token
 *
 * When Claude generates text, it:
 * 1. Looks at all tokens generated so far
 * 2. Calculates probability for EVERY possible next token
 * 3. Uses sampling parameters to choose which token to output
 * 4. Adds that token to the sequence
 * 5. Repeats until complete
 */

// Simplified example: Claude is completing "The best PHP framework is"
// Here are the top predicted tokens and their probabilities:

$tokenProbabilities = [
    'Laravel' => 0.45,      // 45% probability
    'Symfony' => 0.25,      // 25%
    'CodeIgniter' => 0.10,  // 10%
    'Yii' => 0.08,          // 8%
    'Laminas' => 0.05,      // 5%
    'Slim' => 0.04,         // 4%
    'CakePHP' => 0.03,      // 3%
    // ... thousands of other tokens with tiny probabilities
];

// Different sampling strategies will choose different tokens
// Let's simulate this:

function greedySampling(array $probs): string
{
    // Always pick the highest probability token
    arsort($probs);
    return array_key_first($probs);
}

function temperatureSampling(array $probs, float $temperature): string
{
    // Adjust probabilities based on temperature
    $adjusted = [];
    foreach ($probs as $token => $prob) {
        // Higher temperature = flatter distribution (more random)
        // Lower temperature = sharper distribution (more deterministic)
        $adjusted[$token] = pow($prob, 1 / $temperature);
    }

    // Normalize
    $sum = array_sum($adjusted);
    $normalized = array_map(fn($p) => $p / $sum, $adjusted);

    // Sample randomly based on adjusted probabilities
    return weightedRandomChoice($normalized);
}

function weightedRandomChoice(array $probabilities): string
{
    $rand = mt_rand() / mt_getrandmax();
    $cumulative = 0;

    foreach ($probabilities as $token => $probability) {
        $cumulative += $probability;
        if ($rand <= $cumulative) {
            return $token;
        }
    }

    return array_key_first($probabilities);
}

// Demonstrate different sampling strategies
echo "Original probabilities:\n";
print_r($tokenProbabilities);
echo "\n";

echo "Greedy sampling (always picks highest): ";
echo greedySampling($tokenProbabilities) . "\n\n";

echo "Temperature = 0.3 (deterministic): ";
echo temperatureSampling($tokenProbabilities, 0.3) . "\n";

echo "Temperature = 1.0 (balanced): ";
echo temperatureSampling($tokenProbabilities, 1.0) . "\n";

echo "Temperature = 2.0 (creative): ";
echo temperatureSampling($tokenProbabilities, 2.0) . "\n";
```

**Key Insight:** Temperature doesn't change *what* Claude knows, it changes *which* tokens get selected from the probability distribution.

## Temperature Parameter

### What Temperature Controls

```php
<?php
# filename: examples/02-temperature-comparison.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$prompt = 'Complete this sentence: The future of PHP is';

// Test different temperatures
$temperatures = [0.0, 0.5, 1.0, 1.5, 2.0];

foreach ($temperatures as $temp) {
    echo "Temperature {$temp}:\n";

    // Generate 3 completions to see variation
    for ($i = 1; $i <= 3; $i++) {
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 50,
            'temperature' => $temp,
            'messages' => [[
                'role' => 'user',
                'content' => $prompt
            ]]
        ]);

        echo "  {$i}. {$response->content[0]->text}\n";
    }
    echo "\n";
}
```

**Expected pattern:**
```
Temperature 0.0:
  1. bright, with continued evolution...
  2. bright, with continued evolution...
  3. bright, with continued evolution...
  (Nearly identical - very deterministic)

Temperature 1.0:
  1. bright, with continued evolution...
  2. promising as the language evolves...
  3. strong, particularly with modern features...
  (Some variation but still coherent)

Temperature 2.0:
  1. absolutely revolutionary with quantum...
  2. in serverless edge computing...
  3. tied to blockchain and metaverse...
  (Much more creative/unpredictable)
```

### Temperature Scale Guide

```php
<?php
# filename: examples/03-temperature-scale.php
declare(strict_types=1);

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

// Usage examples
$useCases = [
    'Extract contact info from business card',
    'Write creative marketing tagline',
    'Explain how Laravel routing works',
    'Generate unique product descriptions',
    'Parse JSON from unstructured text',
];

foreach ($useCases as $useCase) {
    $category = match(true) {
        str_contains($useCase, 'Extract') => 'data_extraction',
        str_contains($useCase, 'creative') => 'content_generation',
        str_contains($useCase, 'Explain') => 'explanation',
        str_contains($useCase, 'Generate') => 'brainstorming',
        str_contains($useCase, 'Parse') => 'structured_output',
        default => 'general_qa'
    };

    $temp = TemperatureGuide::recommend($category);
    $desc = TemperatureGuide::describe($temp);

    echo "Use case: {$useCase}\n";
    echo "Recommended temperature: {$temp} ({$desc})\n\n";
}
```

## Top-P (Nucleus Sampling)

### Understanding Top-P

Top-p (also called nucleus sampling) considers only the most probable tokens whose cumulative probability reaches the threshold p.

```php
<?php
# filename: examples/04-top-p-visualization.php
declare(strict_types=1);

/**
 * Demonstrates how top_p filters token choices
 */

// Token probabilities from Claude's prediction
$tokenProbabilities = [
    'Laravel' => 0.35,
    'Symfony' => 0.25,
    'CodeIgniter' => 0.15,
    'Yii' => 0.10,
    'Laminas' => 0.05,
    'Slim' => 0.04,
    'CakePHP' => 0.03,
    'Phalcon' => 0.02,
    'FuelPHP' => 0.01,
];

function applyTopP(array $probs, float $topP): array
{
    // Sort by probability descending
    arsort($probs);

    $cumulative = 0;
    $filtered = [];

    foreach ($probs as $token => $prob) {
        $cumulative += $prob;
        $filtered[$token] = $prob;

        if ($cumulative >= $topP) {
            break;
        }
    }

    return $filtered;
}

// Compare different top_p values
$topPValues = [0.5, 0.8, 0.9, 1.0];

foreach ($topPValues as $topP) {
    echo "top_p = {$topP}:\n";

    $filtered = applyTopP($tokenProbabilities, $topP);

    echo "  Tokens considered: " . count($filtered) . "\n";
    echo "  Options: " . implode(', ', array_keys($filtered)) . "\n";
    echo "  Cumulative probability: " . round(array_sum($filtered) * 100, 1) . "%\n\n";
}
```

**Output:**
```
top_p = 0.5:
  Tokens considered: 2
  Options: Laravel, Symfony
  Cumulative probability: 60%

top_p = 0.8:
  Tokens considered: 4
  Options: Laravel, Symfony, CodeIgniter, Yii
  Cumulative probability: 85%

top_p = 0.9:
  Tokens considered: 5
  Options: Laravel, Symfony, CodeIgniter, Yii, Laminas
  Cumulative probability: 90%

top_p = 1.0:
  Tokens considered: 9
  Options: (all)
  Cumulative probability: 100%
```

### Top-P in Practice

```php
<?php
# filename: examples/05-top-p-comparison.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

function testTopP(ClientContract $client, float $topP): array
{
    $responses = [];

    for ($i = 0; $i < 3; $i++) {
        $response = $client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 100,
            'temperature' => 1.0,  // Keep temperature constant
            'top_p' => $topP,
            'messages' => [[
                'role' => 'user',
                'content' => 'Name 3 unique PHP frameworks:'
            ]]
        ]);

        $responses[] = $response->content[0]->text;
    }

    return $responses;
}

// Compare different top_p values
echo "top_p = 0.5 (conservative):\n";
$responses = testTopP($client, 0.5);
foreach ($responses as $i => $resp) {
    echo "  " . ($i + 1) . ". " . substr($resp, 0, 100) . "...\n";
}
echo "\n";

echo "top_p = 0.9 (standard):\n";
$responses = testTopP($client, 0.9);
foreach ($responses as $i => $resp) {
    echo "  " . ($i + 1) . ". " . substr($resp, 0, 100) . "...\n";
}
echo "\n";

echo "top_p = 1.0 (all options):\n";
$responses = testTopP($client, 1.0);
foreach ($responses as $i => $resp) {
    echo "  " . ($i + 1) . ". " . substr($resp, 0, 100) . "...\n";
}
```

## Top-K Sampling

### Understanding Top-K

Top-k limits consideration to only the k most probable tokens.

```php
<?php
# filename: examples/06-top-k-demonstration.php
declare(strict_types=1);

/**
 * Top-k is simpler than top-p: just take the top k tokens
 */

function applyTopK(array $probs, int $topK): array
{
    // Sort by probability descending
    arsort($probs);

    // Take only top k tokens
    return array_slice($probs, 0, $topK, true);
}

$tokenProbabilities = [
    'Laravel' => 0.35,
    'Symfony' => 0.25,
    'CodeIgniter' => 0.15,
    'Yii' => 0.10,
    'Laminas' => 0.05,
    'Slim' => 0.04,
    'CakePHP' => 0.03,
    'Phalcon' => 0.02,
    'FuelPHP' => 0.01,
];

$topKValues = [1, 3, 5, 10];

foreach ($topKValues as $k) {
    echo "top_k = {$k}:\n";

    $filtered = applyTopK($tokenProbabilities, $k);

    echo "  Options: " . implode(', ', array_keys($filtered)) . "\n";
    echo "  Probability coverage: " . round(array_sum($filtered) * 100, 1) . "%\n\n";
}
```

**Output:**
```
top_k = 1:
  Options: Laravel
  Probability coverage: 35%
  (Most deterministic - only best option)

top_k = 3:
  Options: Laravel, Symfony, CodeIgniter
  Probability coverage: 75%

top_k = 5:
  Options: Laravel, Symfony, CodeIgniter, Yii, Laminas
  Probability coverage: 90%

top_k = 10:
  Options: (all 9 tokens)
  Probability coverage: 100%
```

### Top-K Usage

```php
<?php
# filename: examples/07-top-k-usage.php
declare(strict_types=1);

// Note: Claude API doesn't expose top_k as directly as some models
// But understanding it helps grasp sampling mechanics

// Conceptual usage (if supported):
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 200,
    'temperature' => 1.0,
    'top_k' => 40,  // Consider only top 40 tokens at each step
    'messages' => [[
        'role' => 'user',
        'content' => 'Generate a creative function name for user authentication'
    ]]
]);

// Lower top_k = more focused/conventional
// Higher top_k = more creative/unusual
```

## Combining Sampling Parameters

### Temperature + Top-P Interaction

```php
<?php
# filename: examples/08-parameter-combinations.php
declare(strict_types=1);

class SamplingStrategy
{
    public function __construct(
        public readonly float $temperature,
        public readonly float $topP,
        public readonly string $description,
        public readonly array $bestFor
    ) {}
}

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

// Usage
$strategy = SamplingPresets::get('creative');

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 500,
    'temperature' => $strategy->temperature,
    'top_p' => $strategy->topP,
    'messages' => [[
        'role' => 'user',
        'content' => 'Generate unique variable names for a payment processing system'
    ]]
]);

echo "Using strategy: {$strategy->description}\n";
echo "Temperature: {$strategy->temperature}, Top-p: {$strategy->topP}\n\n";
echo $response->content[0]->text;
```

### Configuration Manager

```php
<?php
# filename: src/SamplingConfigManager.php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

class SamplingConfigManager
{
    private array $configs = [];
    private string $currentConfig = 'default';

    public function __construct()
    {
        // Load default configurations
        $this->registerConfig('default', [
            'temperature' => 1.0,
            'top_p' => 0.9,
        ]);

        $this->registerConfig('deterministic', [
            'temperature' => 0.0,
            'top_p' => 1.0,
        ]);

        $this->registerConfig('creative', [
            'temperature' => 1.5,
            'top_p' => 0.95,
        ]);
    }

    public function registerConfig(string $name, array $config): void
    {
        $this->validateConfig($config);
        $this->configs[$name] = $config;
    }

    public function useConfig(string $name): void
    {
        if (!isset($this->configs[$name])) {
            throw new \InvalidArgumentException("Config '{$name}' not found");
        }
        $this->currentConfig = $name;
    }

    public function getConfig(?string $name = null): array
    {
        $name = $name ?? $this->currentConfig;
        return $this->configs[$name] ?? $this->configs['default'];
    }

    public function mergeConfig(array $overrides): array
    {
        return array_merge($this->getConfig(), $overrides);
    }

    private function validateConfig(array $config): void
    {
        if (isset($config['temperature'])) {
            $temp = $config['temperature'];
            if ($temp < 0 || $temp > 2) {
                throw new \InvalidArgumentException('Temperature must be between 0 and 2');
            }
        }

        if (isset($config['top_p'])) {
            $topP = $config['top_p'];
            if ($topP < 0 || $topP > 1) {
                throw new \InvalidArgumentException('top_p must be between 0 and 1');
            }
        }
    }

    public function listConfigs(): array
    {
        return array_keys($this->configs);
    }
}

// Usage
$manager = new SamplingConfigManager();

// Register custom config
$manager->registerConfig('code_generation', [
    'temperature' => 0.4,
    'top_p' => 0.85,
]);

// Use a config
$manager->useConfig('code_generation');
$config = $manager->getConfig();

$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
    'max_tokens' => 1024,
    ...$config,  // Spread operator to merge config
    'messages' => [[
        'role' => 'user',
        'content' => 'Generate a PHP class for user authentication'
    ]]
]);
```

## Use Case Specific Configurations

### Data Extraction (Deterministic)

```php
<?php
# filename: examples/09-deterministic-extraction.php
declare(strict_types=1);

class DataExtractor
{
    public function __construct(
        private ClientContract $client
    ) {}

    public function extractJSON(string $text, array $schema): array
    {
        $schemaDescription = json_encode($schema, JSON_PRETTY_PRINT);

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'temperature' => 0.0,  // Maximum determinism
            'top_p' => 1.0,
            'system' => 'Extract structured data as valid JSON. Return ONLY the JSON object, no other text.',
            'messages' => [[
                'role' => 'user',
                'content' => "Extract data matching this schema:\n\n{$schemaDescription}\n\nFrom this text:\n\n{$text}"
            ]]
        ]);

        $json = $response->content[0]->text;

        // Clean potential markdown wrapping
        if (preg_match('/```json\s*(.*?)\s*```/s', $json, $matches)) {
            $json = $matches[1];
        }

        return json_decode($json, true) ?? [];
    }
}

// Usage
$extractor = new DataExtractor($client);

$businessCard = <<<TEXT
John Smith
Senior Software Engineer
Acme Corporation
Email: john.smith@acme.com
Phone: +1-555-123-4567
Website: https://acme.com
TEXT;

$schema = [
    'name' => 'string',
    'title' => 'string',
    'company' => 'string',
    'email' => 'string',
    'phone' => 'string',
    'website' => 'string',
];

$data = $extractor->extractJSON($businessCard, $schema);
print_r($data);

// Run multiple times - should get IDENTICAL results
// This is critical for reliable data processing
```

### Content Generation (Creative)

```php
<?php
# filename: examples/10-creative-generation.php
declare(strict_types=1);

class CreativeWriter
{
    public function __construct(
        private ClientContract $client
    ) {}

    public function generateVariations(
        string $prompt,
        int $count = 5,
        float $temperature = 1.5
    ): array {
        $variations = [];

        for ($i = 0; $i < $count; $i++) {
            $response = $this->client->messages()->create([
                'model' => 'claude-sonnet-4-20250514',
                'max_tokens' => 500,
                'temperature' => $temperature,  // High for creativity
                'top_p' => 0.95,               // Broad token consideration
                'messages' => [[
                    'role' => 'user',
                    'content' => $prompt
                ]]
            ]);

            $variations[] = $response->content[0]->text;
        }

        return $variations;
    }

    public function generateUnique(string $prompt, array $previous = []): string
    {
        $attempt = 0;
        $maxAttempts = 10;

        while ($attempt < $maxAttempts) {
            $response = $this->client->messages()->create([
                'model' => 'claude-sonnet-4-20250514',
                'max_tokens' => 500,
                'temperature' => 1.6,  // Higher for uniqueness
                'top_p' => 0.98,
                'messages' => [[
                    'role' => 'user',
                    'content' => $prompt . "\n\nGenerate something completely unique and different from these:\n" . implode("\n", $previous)
                ]]
            ]);

            $result = $response->content[0]->text;

            // Check similarity against previous
            $isSimilar = false;
            foreach ($previous as $prev) {
                similar_text($prev, $result, $similarity);
                if ($similarity > 70) {
                    $isSimilar = true;
                    break;
                }
            }

            if (!$isSimilar) {
                return $result;
            }

            $attempt++;
        }

        throw new \RuntimeException('Could not generate unique content');
    }
}

// Usage
$writer = new CreativeWriter($client);

echo "Generating 5 creative taglines:\n\n";

$taglines = $writer->generateVariations(
    prompt: 'Create a catchy tagline for a modern PHP framework focused on developer experience',
    count: 5,
    temperature: 1.5
);

foreach ($taglines as $i => $tagline) {
    echo ($i + 1) . ". {$tagline}\n\n";
}
```

### Code Generation (Focused)

```php
<?php
# filename: examples/11-focused-code-generation.php
declare(strict_types=1);

class CodeGenerator
{
    public function __construct(
        private ClientContract $client
    ) {}

    public function generateFunction(
        string $description,
        array $parameters = [],
        ?string $returnType = null
    ): string {
        $paramsList = '';
        foreach ($parameters as $name => $type) {
            $paramsList .= "{$type} \${$name}, ";
        }
        $paramsList = rtrim($paramsList, ', ');

        $returnHint = $returnType ? ": {$returnType}" : '';

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 1024,
            'temperature' => 0.3,  // Low for consistent, reliable code
            'top_p' => 0.85,       // Focused on conventional patterns
            'system' => 'Generate clean PHP 8.2+ code following PSR-12 standards. Include type hints, return types, and PHPDoc comments.',
            'messages' => [[
                'role' => 'user',
                'content' => "Generate a PHP function:\n\nDescription: {$description}\nParameters: {$paramsList}\nReturn type: {$returnHint}\n\nUse declare(strict_types=1) and modern PHP features."
            ]]
        ]);

        return $response->content[0]->text;
    }

    public function refactorCode(string $code, array $improvements = []): string
    {
        $improvementsList = implode("\n", array_map(fn($i) => "- {$i}", $improvements));

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'temperature' => 0.2,  // Very focused - we want reliable refactoring
            'top_p' => 0.8,
            'system' => 'Refactor PHP code following best practices. Maintain functionality while improving code quality.',
            'messages' => [[
                'role' => 'user',
                'content' => "Refactor this code:\n\n```php\n{$code}\n```\n\nFocus on:\n{$improvementsList}"
            ]]
        ]);

        return $response->content[0]->text;
    }
}

// Usage
$generator = new CodeGenerator($client);

$function = $generator->generateFunction(
    description: 'Validate and sanitize email addresses',
    parameters: ['email' => 'string'],
    returnType: 'string|null'
);

echo $function;
```

## Adaptive Temperature Based on Context

### Dynamic Temperature Adjustment

```php
<?php
# filename: examples/12-adaptive-temperature.php
declare(strict_types=1);

class AdaptiveAssistant
{
    public function __construct(
        private ClientContract $client
    ) {}

    public function respond(string $message): string
    {
        $temperature = $this->determineTemperature($message);
        $topP = $this->determineTopP($message);

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'temperature' => $temperature,
            'top_p' => $topP,
            'messages' => [[
                'role' => 'user',
                'content' => $message
            ]]
        ]);

        return $response->content[0]->text;
    }

    private function determineTemperature(string $message): float
    {
        $message = strtolower($message);

        // Deterministic tasks
        if ($this->contains($message, ['extract', 'parse', 'classify', 'categorize', 'validate'])) {
            return 0.0;
        }

        // Focused tasks
        if ($this->contains($message, ['review', 'analyze', 'explain', 'document', 'translate'])) {
            return 0.3;
        }

        // Creative tasks
        if ($this->contains($message, ['generate', 'create', 'write', 'brainstorm', 'suggest'])) {
            return 1.5;
        }

        // Code-related (focused)
        if ($this->contains($message, ['code', 'function', 'class', 'refactor', 'implement'])) {
            return 0.4;
        }

        // Default: balanced
        return 1.0;
    }

    private function determineTopP(string $message): float
    {
        $message = strtolower($message);

        // Deterministic: consider all options
        if ($this->contains($message, ['extract', 'parse'])) {
            return 1.0;
        }

        // Creative: broad consideration
        if ($this->contains($message, ['generate', 'brainstorm', 'unique'])) {
            return 0.95;
        }

        // Standard
        return 0.9;
    }

    private function contains(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }
        return false;
    }
}

// Usage
$assistant = new AdaptiveAssistant($client);

// This will use low temperature (deterministic)
echo $assistant->respond('Extract email addresses from this text: ...') . "\n\n";

// This will use high temperature (creative)
echo $assistant->respond('Generate creative marketing taglines for...') . "\n\n";

// This will use medium-low temperature (focused)
echo $assistant->respond('Review this PHP code for issues...') . "\n\n";
```

## Measuring Output Consistency

### Consistency Tester

```php
<?php
# filename: examples/13-consistency-tester.php
declare(strict_types=1);

class ConsistencyTester
{
    public function __construct(
        private ClientContract $client
    ) {}

    public function measureConsistency(
        string $prompt,
        float $temperature,
        int $samples = 10
    ): array {
        $responses = [];

        for ($i = 0; $i < $samples; $i++) {
            $response = $this->client->messages()->create([
                'model' => 'claude-sonnet-4-20250514',
                'max_tokens' => 500,
                'temperature' => $temperature,
                'messages' => [[
                    'role' => 'user',
                    'content' => $prompt
                ]]
            ]);

            $responses[] = $response->content[0]->text;
        }

        return [
            'responses' => $responses,
            'unique_count' => count(array_unique($responses)),
            'consistency_score' => $this->calculateConsistency($responses),
            'average_length' => array_sum(array_map('strlen', $responses)) / count($responses),
        ];
    }

    private function calculateConsistency(array $responses): float
    {
        if (count($responses) < 2) {
            return 1.0;
        }

        $similarities = [];
        $count = count($responses);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                similar_text($responses[$i], $responses[$j], $percent);
                $similarities[] = $percent;
            }
        }

        return array_sum($similarities) / count($similarities);
    }

    public function findOptimalTemperature(
        string $prompt,
        float $targetConsistency = 80.0,
        float $step = 0.1
    ): float {
        $temperature = 0.0;

        while ($temperature <= 2.0) {
            $result = $this->measureConsistency($prompt, $temperature, 5);

            if ($result['consistency_score'] >= $targetConsistency) {
                return $temperature;
            }

            $temperature += $step;
        }

        return 0.0; // Fallback to deterministic
    }
}

// Usage
$tester = new ConsistencyTester($client);

echo "Testing consistency at different temperatures:\n\n";

foreach ([0.0, 0.5, 1.0, 1.5] as $temp) {
    echo "Temperature: {$temp}\n";

    $result = $tester->measureConsistency(
        prompt: 'Name the top 3 PHP frameworks',
        temperature: $temp,
        samples: 5
    );

    echo "  Unique responses: {$result['unique_count']}/5\n";
    echo "  Consistency score: " . round($result['consistency_score'], 1) . "%\n";
    echo "  Avg length: " . round($result['average_length'], 0) . " chars\n\n";
}
```

## Exercises

### Exercise 1: Temperature Optimizer

Build a system that automatically finds the optimal temperature for a given task based on requirements.

**Requirements:**
- Accept task description and consistency requirements
- Test multiple temperature values
- Measure output quality and consistency
- Return recommended temperature setting

### Exercise 2: Multi-Strategy Generator

Create a content generator that uses different sampling strategies for different sections.

**Requirements:**
- Use low temperature for factual sections
- Use high temperature for creative sections
- Combine outputs coherently
- Track which strategy was used where

### Exercise 3: Determinism Validator

Build a tool that validates whether responses are deterministic enough for production use.

**Requirements:**
- Run same prompt multiple times
- Compare outputs for consistency
- Flag non-deterministic behavior
- Recommend temperature adjustments

<details>
<summary>Solution Hints</summary>

For Exercise 1, create a test suite that runs prompts at different temperatures and scores results. For Exercise 2, parse content into sections and apply different strategies based on section type. For Exercise 3, use similarity scoring and statistical analysis to measure variance.

</details>

## Key Takeaways

- ✓ Temperature controls creativity vs determinism (0.0 = deterministic, 2.0 = creative)
- ✓ Top-p (nucleus sampling) filters tokens by cumulative probability
- ✓ Top-k limits consideration to k most probable tokens
- ✓ Combine temperature + top_p for fine-grained control
- ✓ Use temperature 0.0 for data extraction and structured output
- ✓ Use temperature 1.5+ for creative content generation
- ✓ Match sampling parameters to your use case requirements
- ✓ Test consistency for production deterministic tasks

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="08"
  label="You've mastered temperature and sampling parameters!"
/>

---

Continue to [Chapter 09: Token Management and Counting](/series/claude-php-developers/chapters/09-token-management) to learn about optimizing context windows and managing costs.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 08 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-08)**

Clone and run locally:
```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-08
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php examples/02-temperature-comparison.php
```
