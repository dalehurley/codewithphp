<?php

declare(strict_types=1);

/**
 * Exercise 2: Text Analyzer
 * 
 * Create a function that analyzes a text string and returns:
 * - Total character count
 * - Word count
 * - Sentence count
 * - Most common word
 * - Average word length
 */

function analyzeText(string $text): array
{
    // Character count (without spaces)
    $charCount = strlen(str_replace(' ', '', $text));

    // Word count
    $words = str_word_count(strtolower($text), 1);
    $wordCount = count($words);

    // Sentence count (approximate - count periods, exclamation marks, question marks)
    $sentenceCount = preg_match_all('/[.!?]+/', $text);

    // Most common word
    $wordFrequency = array_count_values($words);
    arsort($wordFrequency);
    $mostCommonWord = array_key_first($wordFrequency);
    $mostCommonCount = $wordFrequency[$mostCommonWord] ?? 0;

    // Average word length
    $totalLength = array_sum(array_map('strlen', $words));
    $averageLength = $wordCount > 0 ? round($totalLength / $wordCount, 2) : 0;

    return [
        'characters' => $charCount,
        'words' => $wordCount,
        'sentences' => $sentenceCount,
        'most_common_word' => $mostCommonWord,
        'most_common_count' => $mostCommonCount,
        'average_word_length' => $averageLength
    ];
}

// Test the analyzer
echo "=== Text Analyzer ===" . PHP_EOL . PHP_EOL;

$sampleText = "PHP is a popular programming language. PHP is used for web development. Many developers love PHP because PHP is powerful and flexible.";

echo "Text:" . PHP_EOL;
echo $sampleText . PHP_EOL . PHP_EOL;

$analysis = analyzeText($sampleText);

echo "Analysis Results:" . PHP_EOL;
echo "  Characters (no spaces): {$analysis['characters']}" . PHP_EOL;
echo "  Words: {$analysis['words']}" . PHP_EOL;
echo "  Sentences: {$analysis['sentences']}" . PHP_EOL;
echo "  Most common word: '{$analysis['most_common_word']}' (appears {$analysis['most_common_count']} times)" . PHP_EOL;
echo "  Average word length: {$analysis['average_word_length']} characters" . PHP_EOL;
echo PHP_EOL;

// Test with another text
$text2 = "Hello! How are you? I am fine. Thank you for asking!";
echo "Text 2:" . PHP_EOL;
echo $text2 . PHP_EOL . PHP_EOL;

$analysis2 = analyzeText($text2);
echo "Analysis Results:" . PHP_EOL;
echo "  Characters (no spaces): {$analysis2['characters']}" . PHP_EOL;
echo "  Words: {$analysis2['words']}" . PHP_EOL;
echo "  Sentences: {$analysis2['sentences']}" . PHP_EOL;
echo "  Most common word: '{$analysis2['most_common_word']}' (appears {$analysis2['most_common_count']} times)" . PHP_EOL;
echo "  Average word length: {$analysis2['average_word_length']} characters" . PHP_EOL;
