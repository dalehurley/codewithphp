<?php

/**
 * Naive and KMP String Search
 *
 * @package CodeWithPHP\Algorithms\Chapter14
 */

declare(strict_types=1);

function naiveSearch(string $text, string $pattern): array
{
    $matches = [];
    $textLen = strlen($text);
    $patternLen = strlen($pattern);

    for ($i = 0; $i <= $textLen - $patternLen; $i++) {
        $j = 0;

        while ($j < $patternLen && $text[$i + $j] === $pattern[$j]) {
            $j++;
        }

        if ($j === $patternLen) {
            $matches[] = $i;
        }
    }

    return $matches;
}

function computePrefixTable(string $pattern): array
{
    $m = strlen($pattern);
    $lps = array_fill(0, $m, 0);
    $len = 0;
    $i = 1;

    while ($i < $m) {
        if ($pattern[$i] === $pattern[$len]) {
            $len++;
            $lps[$i] = $len;
            $i++;
        } else {
            if ($len !== 0) {
                $len = $lps[$len - 1];
            } else {
                $lps[$i] = 0;
                $i++;
            }
        }
    }

    return $lps;
}

function kmpSearch(string $text, string $pattern): array
{
    $matches = [];
    $n = strlen($text);
    $m = strlen($pattern);

    if ($m === 0) return $matches;

    $lps = computePrefixTable($pattern);

    $i = 0; // index for text
    $j = 0; // index for pattern

    while ($i < $n) {
        if ($pattern[$j] === $text[$i]) {
            $i++;
            $j++;
        }

        if ($j === $m) {
            $matches[] = $i - $j;
            $j = $lps[$j - 1];
        } elseif ($i < $n && $pattern[$j] !== $text[$i]) {
            if ($j !== 0) {
                $j = $lps[$j - 1];
            } else {
                $i++;
            }
        }
    }

    return $matches;
}

// DEMONSTRATIONS
echo "=== String Search Algorithms ===\n\n";

$text = "AABAACAADAABAABA";
$pattern = "AABA";

echo "Text: $text\n";
echo "Pattern: $pattern\n\n";

echo "Naive Search: ";
print_r(naiveSearch($text, $pattern));

echo "KMP Search: ";
print_r(kmpSearch($text, $pattern));
