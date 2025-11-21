<?php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

use ClaudePhp\ClaudePhp;

class SamplingABTester
{
    public function __construct(
        private ClaudePhp $client
    ) {}

    public function testConfigurations(
        string $prompt,
        array $configurations,
        int $samplesPerConfig = 5
    ): array {
        $results = [];

        foreach ($configurations as $name => $config) {
            $responses = [];
            $totalTokens = 0;
            $totalTime = 0;

            for ($i = 0; $i < $samplesPerConfig; $i++) {
                $start = microtime(true);

                $response = $this->client->messages()->create([
                    'model' => 'claude-sonnet-4-5-20250929',
                    'max_tokens' => 500,
                    'temperature' => $config['temperature'] ?? 1.0,
                    'top_p' => $config['top_p'] ?? 0.9,
                    'messages' => [[
                        'role' => 'user',
                        'content' => $prompt
                    ]]
                ]);

                $totalTime += microtime(true) - $start;
                $totalTokens += $response->usage->outputTokens ?? 0;
                $responses[] = $response->content[0]['text'] ?? '';
            }

            $results[$name] = [
                'config' => $config,
                'responses' => $responses,
                'avg_length' => array_sum(array_map('strlen', $responses)) / count($responses),
                'avg_tokens' => $totalTokens / $samplesPerConfig,
                'avg_time' => $totalTime / $samplesPerConfig,
                'consistency' => $this->calculateConsistency($responses),
                'uniqueness' => count(array_unique($responses)) / count($responses),
            ];
        }

        return $results;
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

    public function recommendBestConfig(array $results, string $priority = 'balanced'): string
    {
        $scored = [];

        foreach ($results as $name => $result) {
            $score = match($priority) {
                'consistency' => $result['consistency'],
                'creativity' => 1 - $result['consistency'],
                'cost' => 1 / ($result['avg_tokens'] + 1),
                'balanced' => ($result['consistency'] + (1 - $result['consistency']) + (1 / ($result['avg_tokens'] + 1))) / 3,
                default => $result['consistency'],
            };

            $scored[$name] = $score;
        }

        arsort($scored);
        return array_key_first($scored);
    }
}
