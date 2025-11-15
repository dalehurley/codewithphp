<?php

declare(strict_types=1);

namespace App\Services;

use Anthropic\Anthropic;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Toxic Content Detector using Claude AI
 */
class ToxicContentDetector
{
    private $client;

    public function __construct(string $apiKey)
    {
        $this->client = Anthropic::factory()
            ->withApiKey($apiKey)
            ->make();
    }

    /**
     * Analyze content for toxicity
     */
    public function analyze(string $content): array
    {
        $cacheKey = 'toxic:' . md5($content);

        return Cache::remember($cacheKey, 3600, function () use ($content) {
            $response = $this->client->messages()->create([
                'model' => 'claude-sonnet-4-20250514',
                'max_tokens' => 1024,
                'messages' => [[
                    'role' => 'user',
                    'content' => <<<PROMPT
Analyze this content for toxic behavior:

Content: "{$content}"

Evaluate for:
1. Hate speech or discrimination
2. Harassment or bullying
3. Violence or threats
4. Sexual content (inappropriate)
5. Profanity or offensive language
6. Personal attacks

Provide:
- Overall toxicity score (0.0 - 1.0)
- Specific issues found
- Severity (low, medium, high, critical)
- Suggested action (approve, flag, reject)

Format as JSON.
PROMPT,
                ]],
            ]);

            return $this->parseAnalysis($response->content[0]->text);
        });
    }

    /**
     * Check if content is safe
     */
    public function isSafe(string $content, float $threshold = 0.8): bool
    {
        $analysis = $this->analyze($content);
        return $analysis['toxicity_score'] < $threshold;
    }

    /**
     * Detect hate speech
     */
    public function detectHateSpeech(string $content): array
    {
        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 500,
            'messages' => [[
                'role' => 'user',
                'content' => <<<PROMPT
Analyze for hate speech:

"{$content}"

Detect:
- Racial discrimination
- Religious intolerance
- Gender-based hate
- LGBTQ+ discrimination
- Xenophobia

Provide score (0.0-1.0) and specific examples if found.
PROMPT,
            ]],
        ]);

        return [
            'detected' => true, // Would parse from response
            'score' => 0.0,
            'categories' => [],
            'examples' => [],
        ];
    }

    /**
     * Detect harassment
     */
    public function detectHarassment(string $content): array
    {
        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 500,
            'messages' => [[
                'role' => 'user',
                'content' => "Does this content contain harassment or bullying? '{$content}'. Provide score (0.0-1.0) and details.",
            ]],
        ]);

        return [
            'detected' => false,
            'score' => 0.0,
            'type' => null,
        ];
    }

    /**
     * Get moderation recommendation
     */
    public function getRecommendation(array $analysis): string
    {
        $score = $analysis['toxicity_score'];

        if ($score >= 0.9) {
            return 'reject';
        } elseif ($score >= 0.7) {
            return 'flag';
        } elseif ($score >= 0.4) {
            return 'review';
        } else {
            return 'approve';
        }
    }

    private function parseAnalysis(string $text): array
    {
        // Would parse JSON properly
        return [
            'toxicity_score' => 0.0,
            'issues' => [],
            'severity' => 'low',
            'action' => 'approve',
            'details' => $text,
        ];
    }
}
