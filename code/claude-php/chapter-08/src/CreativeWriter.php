<?php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

use ClaudePhp\ClaudePhp;

class CreativeWriter
{
    public function __construct(
        private ClaudePhp $client
    ) {}

    public function generateVariations(
        string $prompt,
        int $count = 5,
        float $temperature = 1.5
    ): array {
        $variations = [];

        for ($i = 0; $i < $count; $i++) {
            $response = $this->client->messages()->create([
                'model' => 'claude-sonnet-4-5-20250929',
                'max_tokens' => 500,
                'temperature' => $temperature,  // High for creativity
                'top_p' => 0.95,               // Broad token consideration
                'messages' => [[
                    'role' => 'user',
                    'content' => $prompt
                ]]
            ]);

            $variations[] = $response->content[0]['text'] ?? '';
        }

        return $variations;
    }

    public function generateUnique(string $prompt, array $previous = []): string
    {
        $attempt = 0;
        $maxAttempts = 10;

        while ($attempt < $maxAttempts) {
            $response = $this->client->messages()->create([
                'model' => 'claude-sonnet-4-5-20250929',
                'max_tokens' => 500,
                'temperature' => 1.6,  // Higher for uniqueness
                'top_p' => 0.98,
                'messages' => [[
                    'role' => 'user',
                    'content' => $prompt . "\n\nGenerate something completely unique and different from these:\n" . implode("\n", $previous)
                ]]
            ]);

            $result = $response->content[0]['text'] ?? '';

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
