<?php

declare(strict_types=1);

require_once __DIR__ . '/stemmer.php';

use AiMlPhp\Chapter13\Stemmer;

$stemmer = new Stemmer();

$words = [
    'running',
    'runs',
    'ran',
    'runner',
    'quickly',
    'quicker',
    'quickest',
    'beautiful',
    'beautifully',
    'cats',
    'cat',
    'catlike',
    'development',
    'developing',
    'developed',
    'creation',
    'creates',
    'created',
    'creating'
];

echo "Stemming Examples:\n\n";

foreach ($words as $word) {
    $stem = $stemmer->stem($word);
    echo sprintf("  %-15s → %s\n", $word, $stem);
}

// Show how stemming groups variants
echo "\n\nGrouping Variants by Stem:\n\n";
$mapping = $stemmer->stemWithMapping($words);

foreach ($mapping as $stem => $variants) {
    echo "  '$stem':\n";
    foreach ($variants as $variant) {
        echo "    - $variant\n";
    }
}
