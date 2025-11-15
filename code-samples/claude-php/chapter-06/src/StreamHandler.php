<?php

declare(strict_types=1);

namespace ClaudePhp\Streaming;

/**
 * StreamHandler - Handle streaming responses from Claude
 */
class StreamHandler
{
    private string $buffer = '';

    /**
     * Process a chunk of streaming data
     */
    public function processChunk(string $chunk): ?string
    {
        $this->buffer .= $chunk;

        // Extract complete events from buffer
        if (str_contains($this->buffer, "\n\n")) {
            $parts = explode("\n\n", $this->buffer);
            $this->buffer = array_pop($parts) ?? '';

            foreach ($parts as $part) {
                if (str_starts_with($part, 'data: ')) {
                    $data = substr($part, 6);
                    return $data;
                }
            }
        }

        return null;
    }

    /**
     * Reset the buffer
     */
    public function reset(): void
    {
        $this->buffer = '';
    }
}
