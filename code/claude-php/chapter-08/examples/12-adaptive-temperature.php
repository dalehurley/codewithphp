<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use CodeWithPHP\Claude\AdaptiveAssistant;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

$assistant = new AdaptiveAssistant($client);

$testMessages = [
    'Extract email addresses from this text: ...',
    'Generate creative marketing taglines for...',
    'Review this PHP code for issues...',
    'Explain how Laravel routing works',
    'Create a function to validate user input',
];

foreach ($testMessages as $message) {
    try {
        echo "Message: {$message}\n";
        echo "Response: " . substr($assistant->respond($message), 0, 200) . "...\n\n";
    } catch (Exception $e) {
        echo "Error for message '{$message}': " . $e->getMessage() . "\n\n";
    }
}
