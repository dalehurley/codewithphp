<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

/**
 * Simple Interactive Chatbot with Conversation History
 *
 * Demonstrates maintaining context across multiple turns.
 * Type 'quit' or 'exit' to end the conversation.
 *
 * Prerequisites:
 * - Run: composer install
 * - Create .env file with OPENAI_API_KEY
 *
 * Cost: ~$0.001-0.003 per exchange depending on conversation length
 */

// Load environment
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required('OPENAI_API_KEY')->notEmpty();

// Initialize client
$client = OpenAI::client($_ENV['OPENAI_API_KEY']);

// Conversation history
$messages = [
    [
        'role' => 'system',
        'content' => 'You are a helpful, friendly PHP programming assistant. ' .
            'You provide clear, practical advice and code examples when needed. ' .
            'Keep responses concise but informative.'
    ],
];

echo "=== PHP Assistant Chatbot ===\n";
echo "Ask me anything about PHP! Type 'quit' to exit.\n\n";

$totalTokens = 0;
$totalCost = 0.0;

while (true) {
    // Get user input
    echo "You: ";
    $userInput = trim(fgets(STDIN));

    // Check for quit command
    if (in_array(strtolower($userInput), ['quit', 'exit', 'bye'])) {
        echo "\nGoodbye! Session stats:\n";
        echo "  Total tokens used: {$totalTokens}\n";
        echo "  Total cost: $" . number_format($totalCost, 6) . "\n";
        break;
    }

    // Skip empty input
    if (empty($userInput)) {
        continue;
    }

    // Add user message to history
    $messages[] = ['role' => 'user', 'content' => $userInput];

    try {
        // Get AI response
        $response = $client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            'max_tokens' => 300,
            'temperature' => 0.7,
        ]);

        $assistantMessage = $response->choices[0]->message->content;
        $tokensUsed = $response->usage->totalTokens;
        $cost = ($tokensUsed / 1000) * 0.002;

        $totalTokens += $tokensUsed;
        $totalCost += $cost;

        // Add assistant response to history
        $messages[] = ['role' => 'assistant', 'content' => $assistantMessage];

        // Display response
        echo "\nAssistant: {$assistantMessage}\n\n";
        echo "[Tokens: {$tokensUsed}, Cost: $" . number_format($cost, 6) . "]\n\n";
    } catch (\OpenAI\Exceptions\ErrorException $e) {
        echo "\nError: " . $e->getMessage() . "\n\n";
        // Remove the failed user message from history
        array_pop($messages);
    }

    // Warning if conversation getting long
    if ($totalTokens > 2000) {
        echo "[⚠ Warning: Conversation is getting long ({$totalTokens} tokens total). " .
            "Consider starting fresh to reduce costs.]\n\n";
    }
}
