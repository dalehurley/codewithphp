<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use CodeWithPHP\Claude\SamplingABTester;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

$tester = new SamplingABTester($client);

$configurations = [
    'deterministic' => ['temperature' => 0.0, 'top_p' => 1.0],
    'focused' => ['temperature' => 0.3, 'top_p' => 0.8],
    'balanced' => ['temperature' => 1.0, 'top_p' => 0.9],
    'creative' => ['temperature' => 1.5, 'top_p' => 0.95],
];

try {
    $results = $tester->testConfigurations(
        prompt: 'Generate a product tagline for a PHP framework',
        configurations: $configurations,
        samplesPerConfig: 3  // Reduced for demo
    );

    // Find best config for your priority
    $best = $tester->recommendBestConfig($results, priority: 'balanced');

    echo "Recommended configuration: {$best}\n\n";

    foreach ($results as $name => $result) {
        echo "Config: {$name}\n";
        echo "  Temperature: {$result['config']['temperature']}, Top-p: {$result['config']['top_p']}\n";
        echo "  Avg tokens: " . round($result['avg_tokens'], 1) . "\n";
        echo "  Consistency: " . round($result['consistency'], 1) . "%\n";
        echo "  Uniqueness: " . round($result['uniqueness'] * 100, 1) . "%\n";
        echo "  Sample response: " . substr($result['responses'][0], 0, 100) . "...\n\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Make sure your ANTHROPIC_API_KEY is set correctly.\n";
}
