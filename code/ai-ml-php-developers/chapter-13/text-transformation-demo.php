<?php

declare(strict_types=1);

$text = "The cats are running quickly through the garden!";

echo "Original Text:\n";
echo "  \"$text\"\n\n";

// Step 1: Character array (how computers see it)
$chars = mb_str_split($text);
echo "As Characters (first 20):\n";
echo "  " . json_encode(array_slice($chars, 0, 20)) . "\n\n";

// Step 2: Words (tokenization)
$words = str_word_count(strtolower($text), 1);
echo "As Words (tokens):\n";
echo "  " . json_encode($words) . "\n\n";

// Step 3: Without stop words
$stopWords = ['the', 'are', 'through'];
$filtered = array_values(array_filter($words, fn($w) => !in_array($w, $stopWords)));
echo "Without Stop Words:\n";
echo "  " . json_encode($filtered) . "\n\n";

// Step 4: Stemmed (simplified)
$stemmed = array_map(fn($w) => rtrim($w, 'ing'), $filtered);
$stemmed = array_map(fn($w) => rtrim($w, 's'), $stemmed);
echo "Stemmed:\n";
echo "  " . json_encode($stemmed) . "\n\n";

// Step 5: Numeric representation (bag of words)
$vocab = array_unique($stemmed);
$vector = array_map(fn($word) => count(array_filter($stemmed, fn($w) => $w === $word)), $vocab);
echo "As Numbers (word frequencies):\n";
foreach ($vocab as $idx => $word) {
    echo "  '$word' => " . $vector[$idx] . "\n";
}
