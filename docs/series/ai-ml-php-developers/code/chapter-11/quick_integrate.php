<?php

declare(strict_types=1);

/**
 * Quick Start: PHP-Python Integration
 * 
 * This standalone file demonstrates basic PHP-Python communication.
 * Run it to see PHP and Python working together in under 5 minutes!
 * 
 * Usage:
 *   php quick_integrate.php
 * 
 * What it does:
 *   1. PHP prepares data as JSON
 *   2. PHP calls Python script with the data
 *   3. Python analyzes sentiment (simple word matching)
 *   4. Python returns result as JSON
 *   5. PHP displays the result
 */

// Prepare data to send to Python
$data = ['text' => 'This product is amazing! Highly recommend.'];
$json = json_encode($data);

// Call Python script with data (properly escaped for security)
$escaped = escapeshellarg($json);
$pythonScript = __DIR__ . '/quick_sentiment.py';

// Check if Python script exists
if (!file_exists($pythonScript)) {
    echo "❌ Error: quick_sentiment.py not found\n";
    echo "   Make sure both files are in the same directory.\n";
    exit(1);
}

$output = shell_exec("python3 {$pythonScript} {$escaped}");

if ($output === null) {
    echo "❌ Error: Failed to execute Python script\n";
    echo "   Make sure Python 3 is installed: python3 --version\n";
    exit(1);
}

// Parse Python's response
$result = json_decode($output ?: '{}', true);

if (!$result || isset($result['error'])) {
    echo "❌ Error: " . ($result['error'] ?? 'Invalid response from Python') . "\n";
    exit(1);
}

// Display results
echo "🎉 PHP-Python Integration Working!\n\n";
echo "Review: {$data['text']}\n";
echo "Sentiment: {$result['sentiment']}\n";
echo "Confidence: " . round($result['confidence'] * 100, 1) . "%\n\n";

echo "✅ Success! You've integrated PHP with Python.\n\n";

echo "What just happened:\n";
echo "  1. PHP prepared your review text as JSON\n";
echo "  2. PHP called the Python script (quick_sentiment.py)\n";
echo "  3. Python analyzed the sentiment using word matching\n";
echo "  4. Python returned the result as JSON\n";
echo "  5. PHP parsed and displayed the result\n\n";

echo "Next steps:\n";
echo "  • Try changing the review text above\n";
echo "  • Look at quick_sentiment.py to see the Python code\n";
echo "  • Explore the full examples in Chapter 11\n";
echo "  • Build a real sentiment analyzer with machine learning\n";


