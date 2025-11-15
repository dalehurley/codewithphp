<?php

declare(strict_types=1);

namespace App;

class PromptCompressor
{
    public function compress(string $prompt): string
    {
        // Remove excessive whitespace
        $compressed = preg_replace('/\s+/', ' ', $prompt);
        
        // Remove redundant phrases
        $compressed = str_replace([
            'Please note that ',
            'It is important to ',
            'You should know that '
        ], '', $compressed);
        
        return trim($compressed);
    }
    
    public function summarize(string $longText, int $maxLength = 500): string
    {
        if (strlen($longText) <= $maxLength) {
            return $longText;
        }
        
        // Simple summarization - take first part
        return substr($longText, 0, $maxLength) . '...';
    }
    
    public function calculateSavings(string $original, string $compressed): array
    {
        $originalTokens = $this->estimateTokens($original);
        $compressedTokens = $this->estimateTokens($compressed);
        
        return [
            'original_tokens' => $originalTokens,
            'compressed_tokens' => $compressedTokens,
            'tokens_saved' => $originalTokens - $compressedTokens,
            'reduction_percent' => round((1 - $compressedTokens / $originalTokens) * 100, 2)
        ];
    }
    
    private function estimateTokens(string $text): int
    {
        // Rough estimate: 1 token ≈ 4 characters
        return (int)ceil(strlen($text) / 4);
    }
}
