<?php

declare(strict_types=1);

require_once __DIR__ . '/tokenizer.php';

use AiMlPhp\Chapter13\Tokenizer;

$tokenizer = new Tokenizer();

$text = "The quick brown fox jumps over the lazy dog! Don't forget: it's 2024.";

echo "Original Text:\n";
echo "  $text\n\n";

// Simple tokenization
$tokens = $tokenizer->tokenize($text);
echo "Simple Tokens:\n";
echo "  " . implode(', ', $tokens) . "\n";
echo "  Count: " . count($tokens) . " tokens\n\n";

// Advanced tokenization
$advTokens = $tokenizer->tokenizeAdvanced($text);
echo "Advanced Tokens (preserves contractions):\n";
echo "  " . implode(', ', $advTokens) . "\n";
echo "  Count: " . count($advTokens) . " tokens\n\n";

// Bigrams
$bigrams = $tokenizer->tokenizeNgrams($text, 2);
echo "Bigrams (2-word sequences):\n";
foreach (array_slice($bigrams, 0, 5) as $bigram) {
    echo "  - \"$bigram\"\n";
}
echo "  Total: " . count($bigrams) . " bigrams\n\n";

// Sentences
$sentences = $tokenizer->tokenizeSentences($text);
echo "Sentences:\n";
foreach ($sentences as $idx => $sent) {
    echo "  " . ($idx + 1) . ". $sent\n";
}
