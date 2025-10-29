<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

/**
 * OpenAI API using the official PHP library
 *
 * Much cleaner than raw cURL, with better error handling and type safety.
 *
 * Prerequisites:
 * - Run: composer install
 * - Create .env file with OPENAI_API_KEY
 *
 * Cost: ~$0.001 per run
 */

// Load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required('OPENAI_API_KEY')->notEmpty();

// Initialize the OpenAI client
$client = OpenAI::client($_ENV['OPENAI_API_KEY']);

// Make a chat completion request
echo "Generating response using OpenAI PHP library...\n\n";

try {
    $response = $client->chat()->create([
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a PHP expert who writes clear, concise explanations.'],
            ['role' => 'user', 'content' => 'What are the benefits of using Composer for dependency management?'],
        ],
        'max_tokens' => 200,
        'temperature' => 0.7,
    ]);

    // Extract the response
    $message = $response->choices[0]->message->content;
    $usage = $response->usage;

    echo "AI Response:\n";
    echo "─────────────────────────────────────\n";
    echo trim($message) . "\n";
    echo "─────────────────────────────────────\n\n";

    echo "Token Usage:\n";
    echo "  Prompt: {$usage->promptTokens} tokens\n";
    echo "  Completion: {$usage->completionTokens} tokens\n";
    echo "  Total: {$usage->totalTokens} tokens\n\n";

    // Calculate cost
    $cost = ($usage->totalTokens / 1000) * 0.002;
    echo "Estimated cost: $" . number_format($cost, 6) . "\n";
} catch (\OpenAI\Exceptions\ErrorException $e) {
    // Handle API errors (invalid key, rate limit, etc.)
    echo "OpenAI API Error: " . $e->getMessage() . "\n";
    exit(1);
} catch (\Exception $e) {
    // Handle other errors
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
