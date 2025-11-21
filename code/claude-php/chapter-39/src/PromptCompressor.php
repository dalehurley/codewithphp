<?php

declare(strict_types=1);

namespace App\Optimization;

class PromptOptimizer
{
    /**
     * Optimize prompt to reduce token usage
     */
    public function optimize(string $prompt, array $options = []): array
    {
        $original = $prompt;
        $optimized = $prompt;

        // Apply optimization techniques
        $optimized = $this->removeRedundancy($optimized);
        $optimized = $this->useAbbreviations($optimized);
        $optimized = $this->removeFluff($optimized);
        $optimized = $this->consolidateInstructions($optimized);

        if ($options['aggressive'] ?? false) {
            $optimized = $this->aggressiveOptimization($optimized);
        }

        return [
            'original' => $original,
            'optimized' => $optimized,
            'original_length' => strlen($original),
            'optimized_length' => strlen($optimized),
            'reduction_pct' => round((1 - strlen($optimized) / strlen($original)) * 100, 1),
            'estimated_token_savings' => (int) ceil((strlen($original) - strlen($optimized)) / 4),
        ];
    }

    /**
     * Remove redundant words and phrases
     */
    private function removeRedundancy(string $prompt): string
    {
        $redundancies = [
            'I would like you to ' => '',
            'Please ' => '',
            'Could you please ' => '',
            'I need you to ' => '',
            'Can you ' => '',
            ' the following ' => ' ',
            ' that is ' => ' ',
            ' which is ' => ' ',
        ];

        foreach ($redundancies as $redundant => $replacement) {
            $prompt = str_ireplace($redundant, $replacement, $prompt);
        }

        return $prompt;
    }

    /**
     * Use common abbreviations
     */
    private function useAbbreviations(string $prompt): string
    {
        $abbreviations = [
            'For example' => 'E.g.',
            'That is' => 'I.e.',
            'et cetera' => 'etc.',
            'versus' => 'vs.',
        ];

        foreach ($abbreviations as $full => $abbr) {
            $prompt = str_ireplace($full, $abbr, $prompt);
        }

        return $prompt;
    }

    /**
     * Remove unnecessary fluff
     */
    private function removeFluff(string $prompt): string
    {
        $fluff = [
            'basically',
            'actually',
            'literally',
            'honestly',
            'really',
            'very much',
            'kind of',
            'sort of',
        ];

        foreach ($fluff as $word) {
            $prompt = preg_replace('/\b' . preg_quote($word, '/') . '\b/i', '', $prompt);
        }

        // Clean up extra spaces
        $prompt = preg_replace('/\s+/', ' ', $prompt);

        return trim($prompt);
    }

    /**
     * Consolidate multiple instructions into concise format
     */
    private function consolidateInstructions(string $prompt): string
    {
        // If prompt has numbered instructions, keep them
        // Otherwise, it's already consolidated
        return $prompt;
    }

    /**
     * Aggressive optimization (may reduce clarity)
     */
    private function aggressiveOptimization(string $prompt): string
    {
        // Remove articles (a, an, the) where not critical
        $prompt = preg_replace('/\b(a|an|the)\b/i', '', $prompt);

        // Remove extra punctuation
        $prompt = preg_replace('/[,;]+/', ',', $prompt);

        // Clean up spaces
        $prompt = preg_replace('/\s+/', ' ', $prompt);

        return trim($prompt);
    }
}
