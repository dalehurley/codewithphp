<?php
declare(strict_types=1);

echo "=== Divide and Conquer ===\n\n";

// Merge sort - classic divide and conquer
function mergeSort(array $arr): array {
    if (count($arr) <= 1) return $arr;
    
    $mid = (int)(count($arr) / 2);
    $left = mergeSort(array_slice($arr, 0, $mid));
    $right = mergeSort(array_slice($arr, $mid));
    
    return merge($left, $right);
}

function merge(array $left, array $right): array {
    $result = [];
    $i = $j = 0;
    
    while ($i < count($left) && $j < count($right)) {
        if ($left[$i] <= $right[$j]) {
            $result[] = $left[$i++];
        } else {
            $result[] = $right[$j++];
        }
    }
    
    return array_merge($result, array_slice($left, $i), array_slice($right, $j));
}

// Binary search - recursive
function binarySearch(array $arr, int $target, int $left, int $right): ?int {
    if ($left > $right) return null;
    
    $mid = $left + (int)(($right - $left) / 2);
    
    if ($arr[$mid] === $target) return $mid;
    
    if ($arr[$mid] < $target) {
        return binarySearch($arr, $target, $mid + 1, $right);
    }
    return binarySearch($arr, $target, $left, $mid - 1);
}

$unsorted = [64, 34, 25, 12, 22, 11, 90];
$sorted = mergeSort($unsorted);
echo "Merge sort: [" . implode(', ', $sorted) . "]\n";

$index = binarySearch($sorted, 25, 0, count($sorted) - 1);
echo "Binary search for 25: Found at index $index\n";

echo "\n✅ Complete!\n";
