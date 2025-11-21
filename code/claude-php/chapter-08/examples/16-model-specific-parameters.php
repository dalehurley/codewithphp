<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use CodeWithPHP\Claude\ModelSpecificSampling;

// Usage
$models = ['claude-haiku-4-5-20251001', 'claude-sonnet-4-5-20250929', 'claude-opus-4-1-20250805'];
$useCases = ['extraction', 'conversation', 'creative'];

echo "Model-Specific Sampling Recommendations:\n\n";

foreach ($models as $model) {
    echo "Model: {$model}\n";
    echo "Note: " . (ModelSpecificSampling::MODEL_GUIDELINES[$model]['note'] ?? 'General purpose model') . "\n\n";

    foreach ($useCases as $useCase) {
        $config = ModelSpecificSampling::getRecommendedConfig($model, $useCase);
        echo "  {$useCase}: temperature {$config['temperature']}, top_p {$config['top_p']}\n";
    }
    echo "\n";
}

echo "Example usage:\n";
$config = ModelSpecificSampling::getRecommendedConfig(
    'claude-sonnet-4-5',
    'extraction'
);

echo "Recommended config for Sonnet + extraction: ";
print_r($config);
