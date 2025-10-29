<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Summarizer.php';

use Dotenv\Dotenv;

/**
 * Article Summarization Examples
 *
 * Demonstrates using the Summarizer class to condense long articles
 * into concise summaries with different styles and lengths.
 *
 * Prerequisites:
 * - Run: composer install
 * - Create .env file with OPENAI_API_KEY
 *
 * Cost: ~$0.002 per run (4 API calls with gpt-3.5-turbo)
 */

// Load environment
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required('OPENAI_API_KEY')->notEmpty();

// Initialize summarizer
$client = OpenAI::client($_ENV['OPENAI_API_KEY']);
$summarizer = new Summarizer($client);

// Sample article about PHP 8.4
$article = <<<'ARTICLE'
PHP 8.4 represents a significant milestone in the evolution of the PHP programming
language, introducing several groundbreaking features that enhance developer productivity
and code quality. Among the most anticipated additions are property hooks, which allow
developers to define custom logic for property access without writing explicit getter
and setter methods. This feature enables cleaner, more maintainable code by encapsulating
property behavior directly within class definitions.

Another major addition is asymmetric visibility, which gives developers fine-grained control
over property access patterns. With this feature, you can now declare properties that are
publicly readable but privately writable, enforcing immutability from the outside while
maintaining internal flexibility. This addresses a long-standing pain point in PHP's object-
oriented programming model and brings the language closer to modern programming paradigms.

The release also includes improvements to the type system, enhanced performance through
JIT compilation optimizations, and better integration with modern development tools.
The deprecation of several legacy features signals PHP's commitment to moving forward
while maintaining backward compatibility where possible. These changes reflect the PHP
core team's focus on making the language more expressive, safer, and easier to work with
for developers building modern web applications.

For existing PHP projects, the migration path to 8.4 is relatively smooth, with most
code requiring minimal changes. The official migration guide provides detailed information
about deprecated features and recommended alternatives. Early adopters report significant
improvements in code readability and maintainability, particularly in large codebases
where property hooks reduce boilerplate significantly.
ARTICLE;

echo "=== Article Summarization Examples ===\n\n";

// Example 1: Brief summary
echo "1. BRIEF SUMMARY\n";
echo "─────────────────────────────────────\n";
try {
    $brief = $summarizer->summarize($article, 'brief', maxWords: 50);
    echo $brief['summary'] . "\n\n";
    echo "Original: {$brief['originalLength']} words → Summary: {$brief['summaryLength']} words\n";
    echo "Compression: " . round($brief['compressionRatio'] * 100) . "%\n";
    echo "Cost: $" . number_format($brief['cost'], 6) . "\n\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Example 2: Detailed summary
echo "2. DETAILED SUMMARY\n";
echo "─────────────────────────────────────\n";
try {
    $detailed = $summarizer->summarize($article, 'detailed', maxWords: 100);
    echo $detailed['summary'] . "\n\n";
    echo "Compression: " . round($detailed['compressionRatio'] * 100) . "%\n";
    echo "Cost: $" . number_format($detailed['cost'], 6) . "\n\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Example 3: Bullet points
echo "3. BULLET POINT SUMMARY\n";
echo "─────────────────────────────────────\n";
try {
    $bullets = $summarizer->summarize($article, 'bulletPoints');
    echo $bullets['summary'] . "\n\n";
    echo "Cost: $" . number_format($bullets['cost'], 6) . "\n\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Example 4: Key quotes
echo "4. KEY QUOTES\n";
echo "─────────────────────────────────────\n";
try {
    $quotes = $summarizer->extractKeyQuotes($article, numQuotes: 3);
    foreach ($quotes['quotes'] as $i => $quote) {
        echo ($i + 1) . ". {$quote}\n";
    }
    echo "\nCost: $" . number_format($quotes['cost'], 6) . "\n\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Calculate total cost
if (isset($brief, $detailed, $bullets, $quotes)) {
    $totalCost = $brief['cost'] + $detailed['cost'] + $bullets['cost'] + $quotes['cost'];
    echo "Total cost: $" . number_format($totalCost, 6) . "\n";
}

// Bonus: Summarize from file
echo "\n5. SUMMARIZE FROM FILE\n";
echo "─────────────────────────────────────\n";
$articlePath = __DIR__ . '/data/sample-articles/tech-article.txt';
if (file_exists($articlePath)) {
    try {
        $fileResult = $summarizer->summarizeFile($articlePath, 'brief');
        echo "File: tech-article.txt\n";
        echo $fileResult['summary'] . "\n\n";
        echo "Original: {$fileResult['originalLength']} words\n";
        echo "Summary: {$fileResult['summaryLength']} words\n";
        echo "Cost: $" . number_format($fileResult['cost'], 6) . "\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "Sample article file not found. Skipping file summarization demo.\n";
}
