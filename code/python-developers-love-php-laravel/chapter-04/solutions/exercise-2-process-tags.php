<?php

declare(strict_types=1);

/**
 * Exercise 2 Solution: String Processing Function
 * 
 * This is the solution for Exercise 2 from Chapter 04.
 * Converts a Python function that processes tags to PHP.
 */

/**
 * Count tag occurrences and return sorted by count.
 * 
 * @param array<string> $tags Array of tag strings
 * @return array<string, int> Associative array with tag => count, sorted by count descending
 */
function processTags(array $tags): array
{
    $counts = [];
    
    foreach ($tags as $tag) {
        // Convert to lowercase and trim whitespace
        $tagLower = strtolower(trim($tag));
        
        // Skip empty strings
        if ($tagLower === '') {
            continue;
        }
        
        // Count occurrences (similar to Python's dict.get(key, default))
        $counts[$tagLower] = ($counts[$tagLower] ?? 0) + 1;
    }
    
    // Sort by count (descending), then by key (ascending) for stability
    uasort($counts, function($a, $b) {
        if ($a === $b) {
            return 0;
        }
        return ($a > $b) ? -1 : 1;  // Descending order
    });
    
    return $counts;
}

// Test the solution
$tags = ["PHP", "python", "PHP", "  JavaScript  ", "python", ""];
$result = processTags($tags);
print_r($result);

// Expected output:
// Array
// (
//     [php] => 2
//     [python] => 2
//     [javascript] => 1
// )

