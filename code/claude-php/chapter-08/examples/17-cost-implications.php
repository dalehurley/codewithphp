<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use CodeWithPHP\Claude\SamplingCostAnalyzer;

$client = new ClaudePhp(apiKey: getenv('ANTHROPIC_API_KEY'));

$analyzer = new SamplingCostAnalyzer($client);

try {
    $results = $analyzer->analyzeCostImpact(
        prompt: 'Write a detailed product description',
        temperatures: [0.0, 1.0, 1.5, 2.0],
        samples: 3  // Reduced for demo
    );

    echo "Cost Analysis by Temperature:\n\n";

    foreach ($results as $result) {
        echo "Temperature {$result['temperature']}: ";
        echo "Avg {$result['avg_tokens']} tokens, ";
        echo "Est. cost per 1K requests: \${$result['cost_per_1k_requests']}\n";
    }

    echo "\nNote: Higher temperature often produces longer, more varied outputs,\n";
    echo "which increases token usage and costs. For high-volume applications,\n";
    echo "test whether lower temperature achieves similar results at lower cost.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Make sure your ANTHROPIC_API_KEY is set correctly.\n";
}
