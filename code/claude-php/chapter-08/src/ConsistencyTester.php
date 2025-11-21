<?php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

use ClaudePhp\ClaudePhp;

class ConsistencyTester
{
    public function __construct(
        private ClaudePhp $client
    ) {}

    public function measureConsistency(
        string $prompt,
        float $temperature,
        int $samples = 10
    ): array {
        $responses = [];

        for ($i = 0; $i < $samples; $i++) {
            $response = $this->client->messages()->create([
                'model' => 'claude-sonnet-4-5-20250929',
                'max_tokens' => 500,
                'temperature' => $temperature,
                'messages' => [[
                    'role' => 'user',
                    'content' => $prompt
                ]]
            ]);

            $responses[] = $response->content[0]['text'] ?? '';
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
