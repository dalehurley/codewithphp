<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use CodeWithPHP\Claude\TemperatureGuide;

// Usage examples
$useCases = [
    'Extract contact info from business card',
    'Write creative marketing tagline',
    'Explain how Laravel routing works',
    'Generate unique product descriptions',
    'Parse JSON from unstructured text',
];

echo "Temperature Recommendations by Use Case:\n\n";

foreach ($useCases as $useCase) {
    $category = match (true) {
        str_contains($useCase, 'Extract') => 'data_extraction',
        str_contains($useCase, 'creative') => 'content_generation',
        str_contains($useCase, 'Explain') => 'explanation',
        str_contains($useCase, 'Generate') => 'brainstorming',
        str_contains($useCase, 'Parse') => 'structured_output',
        default => 'general_qa'
    };

    $temp = TemperatureGuide::recommend($category);
    $desc = TemperatureGuide::describe($temp);

    echo "Use case: {$useCase}\n";
    echo "Recommended temperature: {$temp} ({$desc})\n\n";
}

echo "Temperature Scale Reference:\n";
echo "- 0.0: Highly deterministic (data extraction, structured output)\n";
echo "- 0.3: Focused (code review, technical writing)\n";
echo "- 1.0: Balanced (general conversation, explanation)\n";
echo "- 1.5: Creative (content generation, brainstorming)\n";
echo "- 2.0: Experimental (maximum creativity)\n";

