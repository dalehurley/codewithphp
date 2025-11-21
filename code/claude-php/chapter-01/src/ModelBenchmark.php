<?php

declare(strict_types=1);

namespace ClaudePhp\ApiIntro;

use ClaudePhp\ClaudePhp;


/**
 * ModelBenchmark - Compare Claude models across different tasks
 *
 * This class helps benchmark and compare different Claude models
 * for speed, quality, and cost.
 */
class ModelBenchmark
{
    private ClaudePhp $client;
    private array $results = [];

    public function __construct(string $apiKey)
    {
                $this->client = new ClaudePhp(
            apiKey: $apiKey
        );
    }

    /**
     * Run a benchmark test on a specific model
     *
     * @param string $model The model to test
     * @param string $prompt The test prompt
     * @param int $maxTokens Maximum tokens to generate
     * @return array<string, mixed> Benchmark results
     */
    public function benchmark(string $model, string $prompt, int $maxTokens = 1024): array
    {
        $startTime = microtime(true);

        $response = $this->client->messages()->create([
            'model' => $model,
            'max_tokens' => $maxTokens,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        $result = [
            'model' => $model,
            'duration' => $duration,
            'input_tokens' => $response->usage->inputTokens,
            'output_tokens' => $response->usage->outputTokens,
            'total_tokens' => $response->usage->inputTokens + $response->usage->outputTokens,
            'response' => $response->content[0]->text,
            'stop_reason' => $response->stopReason
        ];

        $this->results[] = $result;
        return $result;
    }

    /**
     * Compare multiple models on the same task
     *
     * @param array<int, string> $models Array of model names
     * @param string $prompt The test prompt
     * @param int $maxTokens Maximum tokens to generate
     * @return array<int, array<string, mixed>> Comparison results
     */
    public function compare(array $models, string $prompt, int $maxTokens = 1024): array
    {
        $results = [];

        foreach ($models as $model) {
            $results[] = $this->benchmark($model, $prompt, $maxTokens);
        }

        return $results;
    }

    /**
     * Get all benchmark results
     *
     * @return array<int, array<string, mixed>>
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * Reset all results
     */
    public function reset(): void
    {
        $this->results = [];
    }

    /**
     * Format results as a comparison table
     *
     * @param array<int, array<string, mixed>> $results Results to format
     * @return string Formatted table
     */
    public static function formatComparison(array $results): string
    {
        $output = str_pad("Model", 35) . str_pad("Time", 10) . str_pad("Tokens", 10) . "Cost\n";
        $output .= str_repeat("─", 65) . "\n";

        foreach ($results as $result) {
            $pricing = match(true) {
                str_contains($result['model'], 'haiku') => ['input' => 0.25, 'output' => 1.25],
                str_contains($result['model'], 'opus') => ['input' => 15.00, 'output' => 75.00],
                default => ['input' => 3.00, 'output' => 15.00]
            };

            $cost = ($result['input_tokens'] / 1_000_000) * $pricing['input'] +
                    ($result['output_tokens'] / 1_000_000) * $pricing['output'];

            $output .= str_pad($result['model'], 35);
            $output .= str_pad(number_format($result['duration'], 3) . 's', 10);
            $output .= str_pad((string)$result['total_tokens'], 10);
            $output .= '$' . number_format($cost, 6) . "\n";
        }

        return $output;
    }
}
