<?php

/**
 * Practical Hash Table Applications
 *
 * @package CodeWithPHP\Algorithms\Chapter13
 */

declare(strict_types=1);

require_once '01-hash-table-chaining.php';

function countFrequencies(array $items): array
{
    $freq = new HashTableChaining();

    foreach ($items as $item) {
        $count = $freq->get($item) ?? 0;
        $freq->set($item, $count + 1);
    }

    $result = [];
    foreach ($freq->keys() as $key) {
        $result[$key] = $freq->get($key);
    }

    return $result;
}

function twoSum(array $nums, int $target): ?array
{
    $map = new HashTableChaining();

    foreach ($nums as $i => $num) {
        $complement = $target - $num;

        if ($map->has((string)$complement)) {
            return [(int)$map->get((string)$complement), $i];
        }

        $map->set((string)$num, $i);
    }

    return null;
}

// DEMONSTRATIONS
echo "=== Practical Applications ===\n\n";

echo "Example 1: Count frequencies\n";
echo str_repeat('-', 50) . "\n";
$words = ['apple', 'banana', 'apple', 'cherry', 'banana', 'apple'];
$freq = countFrequencies($words);
foreach ($freq as $word => $count) {
    echo "$word: $count\n";
}
echo "\n";

echo "Example 2: Two Sum problem\n";
echo str_repeat('-', 50) . "\n";
$nums = [2, 7, 11, 15];
$result = twoSum($nums, 9);
echo "Indices: [" . implode(', ', $result) . "]\n";
