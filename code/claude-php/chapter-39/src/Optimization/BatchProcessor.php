<?php

declare(strict_types=1);

namespace App\Optimization;

use ClaudePhp\ClaudePhp;

class BatchProcessor
{
    public function __construct(
        private readonly ?ClaudePhp $client = null
    ) {}

    /**
     * Process multiple items in a single request
     * Much more cost-effective than individual requests
     */
    public function processBatch(array $items, string $task, string $model = 'claude-haiku-4-5-20251001'): array
    {
        // Build batch prompt
        $batchPrompt = $this->buildBatchPrompt($items, $task);

        // Single Claude request for all items
        $response = $this->client->messages()->create([
            'model' => $model,
            'max_tokens' => count($items) * 100,  // Allocate tokens per item
            'messages' => [[
                'role' => 'user',
                'content' => $batchPrompt
            ]]
        ]);

        // Parse batch response
        $results = $this->parseBatchResponse($response->content[0]->text, count($items));

        // Calculate savings
        $singleRequestCost = $this->calculateBatchCost($response);
        $individualRequestsCost = $this->estimateIndividualCosts(count($items), $model);

        return [
            'results' => $results,
            'cost_analysis' => [
                'batch_cost' => $singleRequestCost,
                'individual_cost' => $individualRequestsCost,
                'savings' => $individualRequestsCost - $singleRequestCost,
                'savings_pct' => round((1 - $singleRequestCost / $individualRequestsCost) * 100, 1),
            ],
        ];
    }

    private function buildBatchPrompt(array $items, string $task): string
    {
        $itemsList = '';
        foreach ($items as $index => $item) {
            $itemsList .= ($index + 1) . ". $item\n";
        }

        return <<<PROMPT
$task

Process each item and return results in this exact format:
1. [result for item 1]
2. [result for item 2]
...

Items:
$itemsList

Return ONLY the numbered results, no explanation.
PROMPT;
    }

    private function parseBatchResponse(string $response, int $expectedCount): array
    {
        $results = [];
        $lines = explode("\n", trim($response));

        foreach ($lines as $line) {
            if (preg_match('/^(\d+)\.\s*(.+)$/', $line, $matches)) {
                $index = (int) $matches[1];
                $result = trim($matches[2]);
                $results[$index - 1] = $result;
            }
        }

        return $results;
    }

    private function calculateBatchCost($response): float
    {
        $calculator = new \App\Billing\PricingCalculator();
        $cost = $calculator->calculateCost(
            $response->model,
            $response->usage->inputTokens,
            $response->usage->outputTokens
        );

        return $cost['total_cost'];
    }

    private function estimateIndividualCosts(int $itemCount, string $model): float
    {
        // Assume 50 input tokens and 50 output tokens per item
        $calculator = new \App\Billing\PricingCalculator();
        $costPerItem = $calculator->calculateCost($model, 50, 50)['total_cost'];

        return $costPerItem * $itemCount;
    }
}
