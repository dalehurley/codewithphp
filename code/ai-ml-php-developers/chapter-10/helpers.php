<?php

declare(strict_types=1);

/**
 * Helper functions for Chapter 10 neural network examples.
 */

/**
 * Format a number for display with fixed decimal places.
 */
function formatNumber(float $number, int $decimals = 4): string
{
    return number_format($number, $decimals);
}

/**
 * Print a section header.
 */
function printHeader(string $title, int $width = 60): void
{
    echo str_repeat("=", $width) . "\n";
    echo $title . "\n";
    echo str_repeat("=", $width) . "\n\n";
}

/**
 * Print a subsection divider.
 */
function printDivider(int $width = 60): void
{
    echo str_repeat("-", $width) . "\n";
}

/**
 * Visualize a 3x3 binary pattern.
 */
function visualizePattern(array $pattern): void
{
    foreach ($pattern as $row) {
        echo "  " . implode(' ', array_map(fn($p) => $p ? '█' : '·', $row)) . "\n";
    }
}

/**
 * Flatten a 2D array (pattern) to 1D array (feature vector).
 */
function flattenPattern(array $pattern): array
{
    $flattened = [];
    foreach ($pattern as $row) {
        $flattened = array_merge($flattened, $row);
    }
    return $flattened;
}

/**
 * Calculate accuracy from predictions and true labels.
 */
function calculateAccuracy(array $predictions, array $labels): float
{
    $correct = 0;
    foreach ($predictions as $i => $prediction) {
        if ($prediction === $labels[$i]) {
            $correct++;
        }
    }
    return count($labels) > 0 ? $correct / count($labels) : 0.0;
}
