<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

/**
 * Exercise 1 Solution: Language Translator
 *
 * A multi-language translator using GPT's language capabilities.
 * Demonstrates low-temperature prompting for accurate translations.
 *
 * Prerequisites:
 * - Run: composer install (from parent directory)
 * - Create .env file with OPENAI_API_KEY (in parent directory)
 *
 * Cost: ~$0.001-0.002 per translation depending on length
 */

final class Translator
{
    public function __construct(
        private readonly \OpenAI\Client $client,
        private readonly string $model = 'gpt-3.5-turbo',
    ) {}

    /**
     * Translate text to target language
     *
     * @param string $text Text to translate
     * @param string $targetLanguage Target language (e.g., "Spanish", "French", "Japanese")
     * @param string|null $sourceLanguage Source language (null for auto-detect)
     * @param string $formality Formality level: "formal", "informal", "neutral"
     * @return array{translation: string, tokens: int, cost: float, detected_language: string|null}
     */
    public function translate(
        string $text,
        string $targetLanguage,
        ?string $sourceLanguage = null,
        string $formality = 'neutral',
    ): array {
        // Build system prompt
        $systemPrompt = "You are an expert translator who provides accurate, natural translations. " .
            "Preserve the meaning and tone of the original text. " .
            "Adapt idioms and cultural references appropriately.";

        // Build user prompt
        $sourceLangText = $sourceLanguage ? "from {$sourceLanguage} " : "";
        $formalityText = match ($formality) {
            'formal' => " Use formal language and respectful tone.",
            'informal' => " Use casual, friendly language.",
            default => "",
        };

        $userPrompt = "Translate the following text {$sourceLangText}to {$targetLanguage}.{$formalityText}\n\n" .
            "Text to translate:\n{$text}\n\n" .
            "Provide only the translation, without explanations or alternatives.";

        // Make API request
        $response = $this->client->chat()->create([
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'max_tokens' => (int)(strlen($text) * 1.5),  // Allow for expansion
            'temperature' => 0.3,  // Low for accuracy
        ]);

        $translation = trim($response->choices[0]->message->content);
        $tokens = $response->usage->totalTokens;
        $cost = ($tokens / 1000) * 0.002;  // gpt-3.5-turbo pricing

        // If source language wasn't specified, we could try to detect it
        // (this is a simplified approach - production would use language detection)
        $detectedLanguage = $sourceLanguage ?? 'auto-detected';

        return [
            'translation' => $translation,
            'tokens' => $tokens,
            'cost' => $cost,
            'detected_language' => $detectedLanguage,
        ];
    }

    /**
     * Translate multiple phrases at once (more efficient)
     *
     * @param array<string> $phrases Phrases to translate
     * @param string $targetLanguage Target language
     * @return array{translations: array<string>, tokens: int, cost: float}
     */
    public function translateBatch(array $phrases, string $targetLanguage): array
    {
        $systemPrompt = "You are an expert translator. Translate each numbered phrase " .
            "to {$targetLanguage}. Respond with only the translations, " .
            "numbered to match the input.";

        $numberedPhrases = "";
        foreach ($phrases as $i => $phrase) {
            $numberedPhrases .= ($i + 1) . ". {$phrase}\n";
        }

        $response = $this->client->chat()->create([
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $numberedPhrases],
            ],
            'max_tokens' => count($phrases) * 50,
            'temperature' => 0.3,
        ]);

        $translationsText = trim($response->choices[0]->message->content);
        $translations = [];

        // Parse numbered translations
        foreach (explode("\n", $translationsText) as $line) {
            if (preg_match('/^\d+\.\s*(.+)$/', trim($line), $matches)) {
                $translations[] = $matches[1];
            }
        }

        $tokens = $response->usage->totalTokens;
        $cost = ($tokens / 1000) * 0.002;

        return [
            'translations' => $translations,
            'tokens' => $tokens,
            'cost' => $cost,
        ];
    }

    /**
     * Get supported languages (a subset for demonstration)
     *
     * @return array<string>
     */
    public static function getSupportedLanguages(): array
    {
        return [
            'Spanish',
            'French',
            'German',
            'Italian',
            'Portuguese',
            'Russian',
            'Japanese',
            'Chinese',
            'Korean',
            'Arabic',
            'Hindi',
            'Turkish',
            'Dutch',
            'Polish',
            'Swedish',
            'Norwegian',
            'Danish',
            'Finnish',
            'Greek',
            'Hebrew',
        ];
    }
}

// Example usage and validation
if (php_sapi_name() === 'cli') {
    // Load environment
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
    $dotenv->required('OPENAI_API_KEY')->notEmpty();

    // Initialize translator
    $client = OpenAI::client($_ENV['OPENAI_API_KEY']);
    $translator = new Translator($client);

    echo "=== Language Translator Examples ===\n\n";

    // Example 1: Simple translation
    echo "1. ENGLISH TO SPANISH\n";
    echo "─────────────────────────────────────\n";
    $result = $translator->translate(
        text: "Hello, how are you today?",
        targetLanguage: "Spanish"
    );
    echo "Original: Hello, how are you today?\n";
    echo "Translation: {$result['translation']}\n";
    echo "Tokens: {$result['tokens']} | Cost: $" . number_format($result['cost'], 6) . "\n\n";

    // Example 2: Formal translation
    echo "2. FORMAL JAPANESE\n";
    echo "─────────────────────────────────────\n";
    $result2 = $translator->translate(
        text: "Could you please help me with this task?",
        targetLanguage: "Japanese",
        formality: "formal"
    );
    echo "Original: Could you please help me with this task?\n";
    echo "Translation: {$result2['translation']}\n";
    echo "Tokens: {$result2['tokens']} | Cost: $" . number_format($result2['cost'], 6) . "\n\n";

    // Example 3: Longer text
    echo "3. PARAGRAPH TRANSLATION\n";
    echo "─────────────────────────────────────\n";
    $paragraph = "PHP 8.4 introduces several exciting features including property hooks " .
        "and asymmetric visibility. These improvements make the language more " .
        "expressive and maintainable for modern web development.";
    $result3 = $translator->translate(
        text: $paragraph,
        targetLanguage: "French"
    );
    echo "Translation to French:\n{$result3['translation']}\n";
    echo "Tokens: {$result3['tokens']} | Cost: $" . number_format($result3['cost'], 6) . "\n\n";

    // Example 4: Batch translation
    echo "4. BATCH TRANSLATION\n";
    echo "─────────────────────────────────────\n";
    $phrases = [
        "Good morning",
        "Thank you",
        "You're welcome",
        "See you later",
    ];
    $batchResult = $translator->translateBatch($phrases, "German");
    echo "Translating to German:\n";
    foreach ($phrases as $i => $phrase) {
        $translation = $batchResult['translations'][$i] ?? 'N/A';
        echo "  {$phrase} → {$translation}\n";
    }
    echo "\nTokens: {$batchResult['tokens']} | Cost: $" . number_format($batchResult['cost'], 6) . "\n\n";

    // Total cost
    $totalCost = $result['cost'] + $result2['cost'] + $result3['cost'] + $batchResult['cost'];
    echo "Total cost for all examples: $" . number_format($totalCost, 6) . "\n";
}
