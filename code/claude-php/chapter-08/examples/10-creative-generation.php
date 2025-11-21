<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use CodeWithPHP\Claude\CreativeWriter;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

$writer = new CreativeWriter($client);

try {
    echo "Generating 5 creative taglines:\n\n";

    $taglines = $writer->generateVariations(
        prompt: 'Create a catchy tagline for a modern PHP framework focused on developer experience',
        count: 5,
        temperature: 1.5
    );

    foreach ($taglines as $i => $tagline) {
        echo ($i + 1) . ". {$tagline}\n\n";
    }

    echo "Generating a unique tagline different from the above:\n\n";

    $uniqueTagline = $writer->generateUnique(
        prompt: 'Create a catchy tagline for a modern PHP framework',
        previous: $taglines
    );

    echo "Unique: {$uniqueTagline}\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Make sure your ANTHROPIC_API_KEY is set correctly.\n";
}
