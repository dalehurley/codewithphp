<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

/**
 * Streaming Responses Demo
 *
 * Displays AI responses word-by-word as they're generated,
 * improving perceived performance and user experience.
 *
 * Prerequisites:
 * - Run: composer install
 * - Create .env file with OPENAI_API_KEY
 *
 * Cost: ~$0.001 per request
 */

// Load environment
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required('OPENAI_API_KEY')->notEmpty();

// Initialize client
$client = OpenAI::client($_ENV['OPENAI_API_KEY']);

echo "=== Streaming Response Demo ===\n\n";
echo "Ask a question and watch the response stream in real-time!\n\n";

echo "Question: Explain dependency injection in PHP in simple terms.\n\n";
echo "Response: ";

try {
    // Create streaming request
    $stream = $client->chat()->createStreamed([
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a helpful PHP programming assistant.'],
            ['role' => 'user', 'content' => 'Explain dependency injection in PHP in simple terms.'],
        ],
        'max_tokens' => 200,
        'temperature' => 0.7,
    ]);

    $fullResponse = '';

    // Process each chunk as it arrives
    foreach ($stream as $response) {
        // Extract the content delta (new text chunk)
        $newContent = $response->choices[0]->delta->content ?? '';

        if ($newContent !== '') {
            echo $newContent;
            flush(); // Send to output immediately
            $fullResponse .= $newContent;

            // Optional: Add small delay to make streaming visible
            // usleep(20000); // 20ms delay
        }
    }

    echo "\n\n";
    echo "─────────────────────────────────────\n";
    echo "Streaming complete!\n";
    echo "Total characters: " . strlen($fullResponse) . "\n";
} catch (\OpenAI\Exceptions\ErrorException $e) {
    echo "\n\nError: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== Interactive Streaming Chat ===\n\n";

$messages = [
    ['role' => 'system', 'content' => 'You are a helpful PHP expert. Keep responses concise.'],
];

while (true) {
    echo "You (or 'quit'): ";
    $input = trim(fgets(STDIN));

    if (in_array(strtolower($input), ['quit', 'exit'])) {
        echo "Goodbye!\n";
        break;
    }

    if (empty($input)) {
        continue;
    }

    $messages[] = ['role' => 'user', 'content' => $input];

    echo "\nAssistant: ";

    try {
        $stream = $client->chat()->createStreamed([
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            'max_tokens' => 300,
            'temperature' => 0.7,
        ]);

        $fullResponse = '';

        foreach ($stream as $response) {
            $newContent = $response->choices[0]->delta->content ?? '';
            if ($newContent !== '') {
                echo $newContent;
                flush();
                $fullResponse .= $newContent;
            }
        }

        echo "\n\n";

        // Add assistant response to history
        $messages[] = ['role' => 'assistant', 'content' => $fullResponse];
    } catch (\Exception $e) {
        echo "\nError: " . $e->getMessage() . "\n\n";
        array_pop($messages); // Remove failed user message
    }
}
