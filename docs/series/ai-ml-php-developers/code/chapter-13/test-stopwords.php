<?php

declare(strict_types=1);

require_once __DIR__ . '/tokenizer.php';
require_once __DIR__ . '/stop-words.php';

use AiMlPhp\Chapter13\Tokenizer;
use AiMlPhp\Chapter13\StopWordRemover;

$tokenizer = new Tokenizer();
$stopWordRemover = new StopWordRemover();

$text = "The quick brown fox jumps over the lazy dog in the garden.";

echo "Original Text:\n";
echo "  $text\n\n";

// Tokenize
$tokens = $tokenizer->tokenize($text);
echo "Tokens:\n";
echo "  " . implode(', ', $tokens) . "\n";
echo "  Count: " . count($tokens) . " tokens\n\n";

// Remove stop words
$filtered = $stopWordRemover->remove($tokens);
echo "After Stop Word Removal:\n";
echo "  " . implode(', ', $filtered) . "\n";
echo "  Count: " . count($filtered) . " tokens\n";
echo "  Removed: " . (count($tokens) - count($filtered)) . " stop words\n\n";

// Show which were removed
$removed = array_diff($tokens, $filtered);
echo "Stop Words Removed:\n";
echo "  " . implode(', ', $removed) . "\n";
