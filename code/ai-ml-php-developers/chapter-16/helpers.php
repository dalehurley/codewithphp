<?php

declare(strict_types=1);

/**
 * Helper functions for chapter examples
 */

/**
 * Pretty print an array with proper formatting
 */
function printArray(array $data, string $title = ''): void
{
    if ($title) {
        echo "\n=== {$title} ===\n";
    }

    foreach ($data as $key => $value) {
        if (is_array($value)) {
            echo sprintf("  %-20s: [array with %d elements]\n", $key, count($value));
        } elseif (is_float($value)) {
            echo sprintf("  %-20s: %.4f\n", $key, $value);
        } else {
            echo sprintf("  %-20s: %s\n", $key, $value);
        }
    }
    echo "\n";
}

/**
 * Format file size in human-readable format
 */
function formatFileSize(int $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
    return number_format($bytes / (1024 ** $power), 2) . ' ' . $units[$power];
}

/**
 * Print RGB color information
 */
function printColor(array $color, string $label = 'Color'): void
{
    echo "{$label}: RGB({$color['r']}, {$color['g']}, {$color['b']})\n";
}

/**
 * Display a simple histogram visualization in text
 */
function displayHistogram(array $data, int $width = 50): void
{
    $max = max($data);

    foreach ($data as $index => $value) {
        $bar = str_repeat('█', (int)(($value / $max) * $width));
        printf("%2d: %-{$width}s %d\n", $index, $bar, $value);
    }
}

/**
 * Check if GD extension is loaded
 */
function checkGdExtension(): bool
{
    if (!extension_loaded('gd')) {
        echo "❌ GD extension is not loaded!\n";
        echo "Install it with: sudo apt-get install php-gd (Linux) or enable it in php.ini\n";
        return false;
    }

    echo "✓ GD extension is loaded\n";
    return true;
}

/**
 * Get GD information
 */
function getGdInfo(): array
{
    if (!extension_loaded('gd')) {
        return [];
    }

    $info = gd_info();
    return [
        'version' => $info['GD Version'] ?? 'Unknown',
        'jpeg_support' => $info['JPEG Support'] ?? false,
        'png_support' => $info['PNG Support'] ?? false,
        'gif_support' => $info['GIF Create Support'] ?? false,
        'webp_support' => $info['WebP Support'] ?? false,
    ];
}

/**
 * Print section header
 */
function section(string $title): void
{
    $width = 60;
    echo "\n";
    echo str_repeat('=', $width) . "\n";
    echo " " . $title . "\n";
    echo str_repeat('=', $width) . "\n\n";
}

/**
 * Print success message
 */
function success(string $message): void
{
    echo "✓ {$message}\n";
}

/**
 * Print error message
 */
function error(string $message): void
{
    echo "❌ {$message}\n";
}

/**
 * Measure execution time of a function
 */
function measureTime(callable $fn, string $label = 'Operation'): mixed
{
    $start = microtime(true);
    $result = $fn();
    $duration = microtime(true) - $start;

    echo "{$label} completed in " . number_format($duration * 1000, 2) . " ms\n";

    return $result;
}
