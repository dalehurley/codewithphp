<?php

/**
 * Practical String Search Applications
 *
 * @package CodeWithPHP\Algorithms\Chapter14
 */

declare(strict_types=1);

require_once '01-naive-kmp-search.php';

class SimpleGrep
{
    public function search(array $lines, string $pattern): array
    {
        $results = [];
        $lineNumber = 0;

        foreach ($lines as $line) {
            $lineNumber++;

            if (strpos($line, $pattern) !== false) {
                $results[] = [
                    'line' => $lineNumber,
                    'content' => trim($line)
                ];
            }
        }

        return $results;
    }
}

function highlightPattern(string $text, string $pattern): string
{
    $matches = naiveSearch($text, $pattern);

    if (empty($matches)) {
        return $text;
    }

    $highlighted = '';
    $lastPos = 0;
    $patternLen = strlen($pattern);

    foreach ($matches as $pos) {
        $highlighted .= substr($text, $lastPos, $pos - $lastPos);
        $highlighted .= '<mark>' . substr($text, $pos, $patternLen) . '</mark>';
        $lastPos = $pos + $patternLen;
    }

    $highlighted .= substr($text, $lastPos);

    return $highlighted;
}

// DEMONSTRATIONS
echo "=== Practical Applications ===\n\n";

echo "Example 1: Grep-like search\n";
echo str_repeat('-', 50) . "\n";
$logLines = [
    '[2025-01-15 10:23:45] INFO: Application started',
    '[2025-01-15 10:23:47] ERROR: Database connection failed',
    '[2025-01-15 10:23:50] ERROR: Query execution failed',
];

$grep = new SimpleGrep();
$errors = $grep->search($logLines, 'ERROR');

foreach ($errors as $error) {
    echo "Line {$error['line']}: {$error['content']}\n";
}
echo "\n";

echo "Example 2: Text highlighting\n";
echo str_repeat('-', 50) . "\n";
$text = "The quick brown fox jumps over the lazy dog";
$highlighted = highlightPattern($text, "the");
echo "Original: $text\n";
echo "Highlighted: $highlighted\n";
