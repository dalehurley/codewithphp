<?php

declare(strict_types=1);

/**
 * Raw HTTP request to OpenAI API using cURL
 *
 * This demonstrates the low-level API interaction without libraries.
 * Understanding this helps debug issues and work with any HTTP client.
 *
 * Prerequisites:
 * - Set OPENAI_API_KEY environment variable
 * - PHP 8.4+ with cURL extension
 *
 * Cost: ~$0.001 per run (approximately 100 tokens with gpt-3.5-turbo)
 */

// Load API key from environment
$apiKey = getenv('OPENAI_API_KEY');
if (!$apiKey) {
    die("Error: Set OPENAI_API_KEY environment variable\n" .
        "Example: export OPENAI_API_KEY='sk-your-key'\n");
}

// Prepare the request payload
$requestData = [
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        [
            'role' => 'system',
            'content' => 'You are a helpful assistant that explains technical concepts clearly.'
        ],
        [
            'role' => 'user',
            'content' => 'Explain what an API is in one paragraph.'
        ]
    ],
    'max_tokens' => 150,
    'temperature' => 0.7,
];

// Initialize cURL
$ch = curl_init('https://api.openai.com/v1/chat/completions');

// Configure cURL options
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,  // Return response as string
    CURLOPT_POST => true,             // Use POST method
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ],
    CURLOPT_POSTFIELDS => json_encode($requestData),
    CURLOPT_TIMEOUT => 30,            // Timeout after 30 seconds
]);

// Execute the request
echo "Sending request to OpenAI API...\n\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Handle cURL errors
if ($curlError) {
    die("cURL Error: {$curlError}\n");
}

// Handle HTTP errors
if ($httpCode !== 200) {
    echo "HTTP Error {$httpCode}\n";
    echo "Response: {$response}\n";
    die();
}

// Parse the JSON response
$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("JSON Parse Error: " . json_last_error_msg() . "\n");
}

// Extract and display the AI's response
$aiMessage = $data['choices'][0]['message']['content'] ?? 'No response';
$tokensUsed = $data['usage']['total_tokens'] ?? 0;
$promptTokens = $data['usage']['prompt_tokens'] ?? 0;
$completionTokens = $data['usage']['completion_tokens'] ?? 0;

echo "AI Response:\n";
echo "─────────────────────────────────────\n";
echo trim($aiMessage) . "\n";
echo "─────────────────────────────────────\n\n";

echo "Token Usage:\n";
echo "  Prompt: {$promptTokens} tokens\n";
echo "  Completion: {$completionTokens} tokens\n";
echo "  Total: {$tokensUsed} tokens\n\n";

// Calculate approximate cost (gpt-3.5-turbo pricing)
$costPerToken = 0.002 / 1000;  // $0.002 per 1K tokens
$estimatedCost = $tokensUsed * $costPerToken;
echo "Estimated cost: $" . number_format($estimatedCost, 6) . "\n";
