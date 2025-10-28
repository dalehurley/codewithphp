<?php

declare(strict_types=1);

require_once __DIR__ . '/bag-of-words.php';

use AiMlPhp\Chapter13\BagOfWords;

// Sample documents (already tokenized and preprocessed)
$documents = [
    ['cat', 'dog', 'pet'],
    ['dog', 'bark', 'loud'],
    ['cat', 'meow', 'soft'],
    ['pet', 'love', 'care'],
];

$bow = new BagOfWords();

echo "Training Documents:\n";
foreach ($documents as $idx => $doc) {
    echo "  Doc " . ($idx + 1) . ": " . implode(', ', $doc) . "\n";
}
echo "\n";

// Fit and transform
$vectors = $bow->fitTransform($documents);

// Show vocabulary
$vocab = $bow->getVocabulary();
echo "Vocabulary (" . count($vocab) . " terms):\n";
echo "  " . implode(', ', $vocab) . "\n\n";

// Show vectors
echo "Feature Vectors:\n\n";
foreach ($vectors as $idx => $vector) {
    echo "Document " . ($idx + 1) . ":\n";
    echo "  Raw vector: [" . implode(', ', $vector) . "]\n";
    echo "  With labels: " . json_encode($bow->displayVector($vector)) . "\n\n";
}

// Test on new document
$newDoc = ['cat', 'dog', 'play'];
$newVector = $bow->transform([$newDoc])[0];
echo "New Document: " . implode(', ', $newDoc) . "\n";
echo "  Vector: " . json_encode($bow->displayVector($newVector)) . "\n";
