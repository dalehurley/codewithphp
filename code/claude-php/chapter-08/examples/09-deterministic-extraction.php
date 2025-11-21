<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

class DataExtractor
{
    public function __construct(
        private ClaudePhp $client
    ) {}

    public function extractJSON(string $text, array $schema): array
    {
        $schemaDescription = json_encode($schema, JSON_PRETTY_PRINT);

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-5-20250929',
            'max_tokens' => 2048,
            'temperature' => 0.0,  // Maximum determinism
            'top_p' => 1.0,
            'system' => 'Extract structured data as valid JSON. Return ONLY the JSON object, no other text.',
            'messages' => [[
                'role' => 'user',
                'content' => "Extract data matching this schema:\n\n{$schemaDescription}\n\nFrom this text:\n\n{$text}"
            ]]
        ]);

        $json = $response->content[0]['text'] ?? '';

        // Clean potential markdown wrapping
        if (preg_match('/```json\s*(.*?)\s*```/s', $json, $matches)) {
            $json = $matches[1];
        }

        return json_decode($json, true) ?? [];
    }
}

// Usage
$client = new ClaudePhp(
    apiKey: getenv('ANTHROPIC_API_KEY')
);

$extractor = new DataExtractor($client);

$businessCard = <<<TEXT
John Smith
Senior Software Engineer
Acme Corporation
Email: john.smith@acme.com
Phone: +1-555-123-4567
Website: https://acme.com
TEXT;

$schema = [
    'name' => 'string',
    'title' => 'string',
    'company' => 'string',
    'email' => 'string',
    'phone' => 'string',
    'website' => 'string',
];

try {
    $data = $extractor->extractJSON($businessCard, $schema);
    print_r($data);

    // Run multiple times - should get IDENTICAL results
    // This is critical for reliable data processing
    echo "\nRunning extraction 3 times to verify consistency:\n\n";

    for ($i = 1; $i <= 3; $i++) {
        $result = $extractor->extractJSON($businessCard, $schema);
        echo "Run {$i}: " . json_encode($result) . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
