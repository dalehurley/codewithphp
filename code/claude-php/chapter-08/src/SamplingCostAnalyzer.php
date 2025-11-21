<?php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

use ClaudePhp\ClaudePhp;

class SamplingCostAnalyzer
{
    public function __construct(
        private ClaudePhp $client
    ) {}

    public function analyzeCostImpact(
        string $prompt,
        array $temperatures = [0.0, 0.5, 1.0, 1.5, 2.0],
        int $samples = 5
    ): array {
        $results = [];

        foreach ($temperatures as $temp) {
            $tokens = [];
            $lengths = [];

            for ($i = 0; $i < $samples; $i++) {
                $response = $this->client->messages()->create([
                    'model' => 'claude-sonnet-4-5-20250929',
                    'max_tokens' => 1000,
                    'temperature' => $temp,
                    'messages' => [[
                        'role' => 'user',
                        'content' => $prompt
                    ]]
                ]);

                $tokens[] = $response->usage->outputTokens ?? 0;
                $lengths[] = strlen($response->content[0]['text'] ?? '');
            }

            $results[] = [
                'temperature' => $temp,
                'avg_tokens' => array_sum($tokens) / count($tokens),
                'avg_length' => array_sum($lengths) / count($lengths),
                'cost_per_1k_requests' => (array_sum($tokens) / count($tokens)) * 1000 * 0.003, // Example pricing
            ];
        }

        return $results;
    }
}
