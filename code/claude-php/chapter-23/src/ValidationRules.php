<?php

declare(strict_types=1);

namespace App\Rules;

use ClaudePhp\ClaudePhp;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Cache;

/**
 * Claude-powered validation rules
 */
class ClaudeValidation implements Rule
{
    public function __construct(
        private readonly string $validationType,
        private readonly ?string $customPrompt = null
    ) {
    }

    public function passes($attribute, $value): bool
    {
        $client = new ClaudePhp(
                apiKey: config('services.anthropic.api_key')
            );

        $prompt = $this->buildPrompt($attribute, $value);

        $cacheKey = 'validation:' . md5($prompt . $value);

        return Cache::remember($cacheKey, config('validation.cache_ttl', 3600), function () use ($client, $prompt) {
            $response = $client->messages()->create([
                'model' => config('services.anthropic.model'),
                'max_tokens' => 100,
                'messages' => [[
                    'role' => 'user',
                    'content' => $prompt,
                ]],
            ]);

            $result = strtolower(trim($response->content[0]->text));
            return str_contains($result, 'valid') || str_contains($result, 'yes');
        });
    }

    public function message(): string
    {
        return "The :attribute does not pass {$this->validationType} validation.";
    }

    private function buildPrompt(string $attribute, $value): string
    {
        if ($this->customPrompt) {
            return str_replace(['{attribute}', '{value}'], [$attribute, $value], $this->customPrompt);
        }

        return match($this->validationType) {
            'professional_email' => "Is this a professional business email address? '{$value}'. Respond with only 'valid' or 'invalid'.",
            'appropriate_content' => "Is this content appropriate and professional? '{$value}'. Respond with only 'valid' or 'invalid'.",
            'realistic_name' => "Does this appear to be a realistic person's name? '{$value}'. Respond with only 'valid' or 'invalid'.",
            'valid_address' => "Does this appear to be a valid physical address? '{$value}'. Respond with only 'valid' or 'invalid'.",
            default => "Validate this {$attribute}: '{$value}'. Respond with only 'valid' or 'invalid'.",
        };
    }
}

/**
 * Content Quality Validation
 */
class ContentQuality implements Rule
{
    public function __construct(
        private readonly int $minQualityScore = 7
    ) {
    }

    public function passes($attribute, $value): bool
    {
        $client = new ClaudePhp(
                apiKey: config('services.anthropic.api_key')
            );

        $response = $client->messages()->create([
            'model' => config('services.anthropic.model'),
            'max_tokens' => 200,
            'messages' => [[
                'role' => 'user',
                'content' => "Rate this content's quality on a scale of 1-10 based on:\n"
                    . "- Grammar and spelling\n"
                    . "- Clarity and coherence\n"
                    . "- Professionalism\n"
                    . "- Completeness\n\n"
                    . "Content: \"{$value}\"\n\n"
                    . "Respond with ONLY a number from 1-10.",
            ]],
        ]);

        $score = (int) trim($response->content[0]->text);
        return $score >= $this->minQualityScore;
    }

    public function message(): string
    {
        return "The :attribute does not meet minimum quality standards.";
    }
}

/**
 * Spam Detection Rule
 */
class SpamDetection implements Rule
{
    public function __construct(
        private readonly float $threshold = 0.7
    ) {
    }

    public function passes($attribute, $value): bool
    {
        $client = new ClaudePhp(
                apiKey: config('services.anthropic.api_key')
            );

        $response = $client->messages()->create([
            'model' => config('services.anthropic.model'),
            'max_tokens' => 500,
            'messages' => [[
                'role' => 'user',
                'content' => "Analyze this content for spam indicators:\n"
                    . "- Excessive links or promotional content\n"
                    . "- Repetitive text\n"
                    . "- Suspicious patterns\n"
                    . "- Irrelevant content\n\n"
                    . "Content: \"{$value}\"\n\n"
                    . "Provide a spam probability (0.0-1.0) and brief reason.\n"
                    . "Format: SCORE|REASON",
            ]],
        ]);

        $result = explode('|', $response->content[0]->text);
        $score = (float) trim($result[0]);

        return $score < $this->threshold;
    }

    public function message(): string
    {
        return "The :attribute appears to contain spam or inappropriate content.";
    }
}

/**
 * Sentiment Validation
 */
class PositiveSentiment implements Rule
{
    public function passes($attribute, $value): bool
    {
        $client = new ClaudePhp(
                apiKey: config('services.anthropic.api_key')
            );

        $response = $client->messages()->create([
            'model' => config('services.anthropic.model'),
            'max_tokens' => 50,
            'messages' => [[
                'role' => 'user',
                'content' => "What is the sentiment of this text: \"{$value}\"? Respond with only: positive, negative, or neutral.",
            ]],
        ]);

        $sentiment = strtolower(trim($response->content[0]->text));
        return $sentiment !== 'negative';
    }

    public function message(): string
    {
        return "The :attribute contains negative sentiment.";
    }
}
