<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use CodeWithPHP\Claude\ConsistencyTester;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

$tester = new ConsistencyTester($client);

echo "Testing consistency at different temperatures:\n\n";

$temperatures = [0.0, 0.5, 1.0, 1.5];

foreach ($temperatures as $temp) {
    try {
        echo "Temperature: {$temp}\n";

        $result = $tester->measureConsistency(
            prompt: 'Name the top 3 PHP frameworks',
            temperature: $temp,
            samples: 5
        );

        echo "  Unique responses: {$result['unique_count']}/5\n";
        echo "  Consistency score: " . round($result['consistency_score'], 1) . "%\n";
        echo "  Avg length: " . round($result['average_length'], 0) . " chars\n\n";

    } catch (Exception $e) {
        echo "  Error: " . $e->getMessage() . "\n\n";
    }
}

try {
    echo "Finding optimal temperature for 80% consistency:\n";
    $optimal = $tester->findOptimalTemperature(
        prompt: 'List the main HTTP status codes',
        targetConsistency: 80.0
    );
    echo "Optimal temperature: {$optimal}\n";

} catch (Exception $e) {
    echo "Error finding optimal temperature: " . $e->getMessage() . "\n";
}
