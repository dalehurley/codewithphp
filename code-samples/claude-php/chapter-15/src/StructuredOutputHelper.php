<?php

declare(strict_types=1);

namespace ClaudePHP\StructuredOutputs;

use GuzzleHttp\Client;
use JsonSchema\Validator;

class StructuredOutputHelper
{
    private Client $client;
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->client = new Client(['base_uri' => 'https://api.anthropic.com']);
    }

    public function extractStructured(string $prompt, array $schema): array
    {
        $schemaJson = json_encode($schema, JSON_PRETTY_PRINT);

        $systemPrompt = <<<PROMPT
You must respond with valid JSON that matches this exact schema:

{$schemaJson}

Return ONLY the JSON, no other text.
PROMPT;

        $response = $this->client->post('/v1/messages', [
            'headers' => [
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ],
            'json' => [
                'model' => 'claude-sonnet-4-20250514',
                'max_tokens' => 4096,
                'system' => $systemPrompt,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ],
        ]);

        $result = json_decode($response->getBody()->getContents(), true);
        $jsonText = $result['content'][0]['text'];

        // Extract JSON from response
        if (preg_match('/\{.*\}/s', $jsonText, $matches)) {
            $jsonText = $matches[0];
        }

        $data = json_decode($jsonText, true);

        // Validate against schema
        $this->validateSchema($data, $schema);

        return $data;
    }

    private function validateSchema($data, array $schema): void
    {
        $validator = new Validator();
        $validator->validate($data, (object) $schema);

        if (!$validator->isValid()) {
            $errors = [];
            foreach ($validator->getErrors() as $error) {
                $errors[] = sprintf("[%s] %s", $error['property'], $error['message']);
            }
            throw new \Exception("Schema validation failed:\n" . implode("\n", $errors));
        }
    }
}
