<?php

declare(strict_types=1);

namespace App\Services;

use Anthropic\Anthropic;

/**
 * PII (Personally Identifiable Information) Redaction Service
 */
class PiiRedactionService
{
    private $client;

    public function __construct(string $apiKey)
    {
        $this->client = Anthropic::factory()
            ->withApiKey($apiKey)
            ->make();
    }

    /**
     * Detect and redact PII from content
     */
    public function redact(string $content): array
    {
        $detected = $this->detectPii($content);
        $redacted = $this->applyRedaction($content, $detected);

        return [
            'original' => $content,
            'redacted' => $redacted,
            'pii_detected' => $detected,
        ];
    }

    /**
     * Detect PII in content
     */
    public function detectPii(string $content): array
    {
        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'messages' => [[
                'role' => 'user',
                'content' => <<<PROMPT
Identify all personally identifiable information (PII) in this text:

"{$content}"

Detect:
- Email addresses
- Phone numbers
- Social Security Numbers
- Credit card numbers
- Home addresses
- Names (first and last)
- Dates of birth
- IP addresses
- Account numbers

Return as JSON with type and location for each PII found.
PROMPT,
            ]],
        ]);

        return $this->parsePiiDetection($response->content[0]->text);
    }

    /**
     * Apply redaction to content
     */
    private function applyRedaction(string $content, array $piiItems): string
    {
        $redacted = $content;

        foreach ($piiItems as $item) {
            $replacement = match($item['type']) {
                'email' => '[EMAIL REDACTED]',
                'phone' => '[PHONE REDACTED]',
                'ssn' => '[SSN REDACTED]',
                'credit_card' => '[CARD REDACTED]',
                'address' => '[ADDRESS REDACTED]',
                'name' => '[NAME REDACTED]',
                default => '[REDACTED]',
            };

            $redacted = str_replace($item['value'], $replacement, $redacted);
        }

        return $redacted;
    }

    /**
     * Anonymize content with AI
     */
    public function anonymize(string $content): string
    {
        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 4096,
            'messages' => [[
                'role' => 'user',
                'content' => <<<PROMPT
Anonymize this content by removing or replacing all PII while preserving the meaning:

"{$content}"

Replace:
- Names with generic identifiers (Person A, Person B)
- Specific locations with general regions
- Dates with relative timeframes
- Other identifying details

Return the anonymized version.
PROMPT,
            ]],
        ]);

        return $response->content[0]->text;
    }

    /**
     * Validate if content contains PII
     */
    public function containsPii(string $content): bool
    {
        $detected = $this->detectPii($content);
        return !empty($detected);
    }

    private function parsePiiDetection(string $text): array
    {
        // Would parse JSON properly
        return [
            ['type' => 'email', 'value' => 'user@example.com', 'confidence' => 0.99],
        ];
    }
}
