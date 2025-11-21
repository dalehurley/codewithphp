<?php
declare(strict_types=1);

namespace CodeWithPHP\Claude;

use ClaudePhp\ClaudePhp;

class AdaptiveAssistant
{
    public function __construct(
        private ClaudePhp $client
    ) {}

    public function respond(string $message): string
    {
        $temperature = $this->determineTemperature($message);
        $topP = $this->determineTopP($message);

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 2048,
            'temperature' => $temperature,
            'top_p' => $topP,
            'messages' => [[
                'role' => 'user',
                'content' => $message
            ]]
        ]);

        return $response->content[0]['text'] ?? '';
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
