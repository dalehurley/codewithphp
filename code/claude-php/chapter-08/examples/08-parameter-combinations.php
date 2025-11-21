<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use CodeWithPHP\Claude\SamplingPresets;

// Usage - demonstrating strategy selection without API call
$strategy = SamplingPresets::get('creative');

echo "Using strategy: {$strategy->description}\n";
echo "Temperature: {$strategy->temperature}, Top-p: {$strategy->topP}\n\n";

echo "This strategy would be used for: " . implode(', ', $strategy->bestFor) . "\n\n";

// Show all available strategies
echo "Available strategies:\n";
foreach (SamplingPresets::STRATEGIES as $name => $config) {
    echo "- {$name}: {$config['description']}\n";
}
