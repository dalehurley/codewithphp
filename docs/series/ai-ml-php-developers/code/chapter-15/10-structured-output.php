<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

/**
 * Structured Output / JSON Mode Demo
 *
 * Demonstrates how to get reliably structured JSON responses from GPT
 * for data extraction, form filling, and API responses.
 *
 * Prerequisites:
 * - Run: composer install
 * - Create .env file with OPENAI_API_KEY
 *
 * Cost: ~$0.001-0.002 per request
 */

// Load environment
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required('OPENAI_API_KEY')->notEmpty();

// Initialize client
$client = OpenAI::client($_ENV['OPENAI_API_KEY']);

echo "=== Structured Output Demo ===\n\n";

// Example 1: Extract structured data from unstructured text
echo "1. EXTRACT CONTACT INFORMATION\n";
echo "─────────────────────────────────────\n";

$unstructuredText = "Hi, I'm Sarah Johnson and you can reach me at sarah.j@example.com " .
    "or call me at +1-555-0123. I work at Tech Corp in the engineering department.";

$extractionPrompt = "Extract contact information from this text and return it as JSON with " .
    "fields: name, email, phone, company, department.\n\nText: {$unstructuredText}";

try {
    $response = $client->chat()->create([
        'model' => 'gpt-3.5-turbo-1106', // Model that supports JSON mode
        'messages' => [
            ['role' => 'system', 'content' => 'You extract structured data and respond in JSON format.'],
            ['role' => 'user', 'content' => $extractionPrompt],
        ],
        'response_format' => ['type' => 'json_object'], // Force JSON output
        'temperature' => 0.3,
    ]);

    $jsonResponse = $response->choices[0]->message->content;
    $contactData = json_decode($jsonResponse, true);

    echo "Extracted Data:\n";
    echo json_encode($contactData, JSON_PRETTY_PRINT) . "\n\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Example 2: Parse product reviews into structured ratings
echo "2. PARSE PRODUCT REVIEW\n";
echo "─────────────────────────────────────\n";

$review = "This laptop is amazing! The build quality is excellent and the battery lasts forever. " .
    "However, it's a bit heavy and the price is quite high. Overall, I'd recommend it " .
    "for professional use.";

$reviewPrompt = "Analyze this product review and return JSON with: sentiment (positive/negative/neutral), " .
    "pros (array), cons (array), rating (1-5), recommended (boolean).\n\nReview: {$review}";

try {
    $response = $client->chat()->create([
        'model' => 'gpt-3.5-turbo-1106',
        'messages' => [
            ['role' => 'system', 'content' => 'You analyze product reviews and return structured JSON.'],
            ['role' => 'user', 'content' => $reviewPrompt],
        ],
        'response_format' => ['type' => 'json_object'],
        'temperature' => 0.3,
    ]);

    $analysis = json_decode($response->choices[0]->message->content, true);

    echo "Review Analysis:\n";
    echo json_encode($analysis, JSON_PRETTY_PRINT) . "\n\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Example 3: Generate structured data (API mock response)
echo "3. GENERATE MOCK API DATA\n";
echo "─────────────────────────────────────\n";

$dataPrompt = "Generate 3 sample user records in JSON format with fields: id (number), " .
    "name (string), email (string), role (admin/user/guest), active (boolean), " .
    "created_at (ISO date). Return as an array called 'users'.";

try {
    $response = $client->chat()->create([
        'model' => 'gpt-3.5-turbo-1106',
        'messages' => [
            ['role' => 'system', 'content' => 'You generate realistic mock data in JSON format.'],
            ['role' => 'user', 'content' => $dataPrompt],
        ],
        'response_format' => ['type' => 'json_object'],
        'temperature' => 0.7,
    ]);

    $mockData = json_decode($response->choices[0]->message->content, true);

    echo "Generated Mock Data:\n";
    echo json_encode($mockData, JSON_PRETTY_PRINT) . "\n\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Example 4: Practical use case - Form validation with suggestions
echo "4. SMART FORM VALIDATION\n";
echo "─────────────────────────────────────\n";

final class FormValidator
{
    public function __construct(
        private readonly \OpenAI\Client $client
    ) {}

    public function validateAndSuggest(array $formData, array $rules): array
    {
        $prompt = "Validate this form data against the rules and provide suggestions. " .
            "Return JSON with: is_valid (boolean), errors (array of field => message), " .
            "suggestions (array of field => suggestion).\n\n" .
            "Data: " . json_encode($formData) . "\n" .
            "Rules: " . json_encode($rules);

        $response = $this->client->chat()->create([
            'model' => 'gpt-3.5-turbo-1106',
            'messages' => [
                ['role' => 'system', 'content' => 'You validate form data and provide helpful suggestions.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.3,
        ]);

        return json_decode($response->choices[0]->message->content, true);
    }
}

$validator = new FormValidator($client);

$formData = [
    'username' => 'jd',
    'email' => 'john@example',
    'password' => '12345',
    'bio' => 'I like coding',
];

$rules = [
    'username' => 'minimum 3 characters, alphanumeric',
    'email' => 'valid email format',
    'password' => 'minimum 8 characters, must contain numbers and letters',
    'bio' => 'minimum 20 characters, should be descriptive',
];

try {
    $validationResult = $validator->validateAndSuggest($formData, $rules);

    echo "Validation Result:\n";
    echo json_encode($validationResult, JSON_PRETTY_PRINT) . "\n\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "=== Key Benefits ===\n";
echo "• Guaranteed valid JSON output (no parsing errors)\n";
echo "• Consistent structure for database insertion\n";
echo "• Perfect for API responses and data extraction\n";
echo "• Reduces error handling code significantly\n";
echo "\n";
echo "Note: Use models ending in -1106 or later for JSON mode support\n";
