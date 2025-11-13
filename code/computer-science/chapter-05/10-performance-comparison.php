<?php

declare(strict_types=1);

/**
 * BST vs Array vs Hash Table Performance Comparison
 *
 * Demonstrates:
 * - Time comparison for insert, search, delete
 * - Memory usage
 * - When to use each data structure
 */

require_once '03-bst-implementation.php';

echo "=== Performance Comparison: BST vs Array vs Hash Table ===\n\n";

// Test data size
$testSizes = [100, 1000, 10000];
$searchCount = 1000;

echo "Test Configuration:\n";
echo "  Perform $searchCount searches for each structure\n";
echo "  Random inserts and searches\n\n";

foreach ($testSizes as $size) {
    echo "═══════════════════════════════════════════════════\n";
    echo "Dataset Size: $size elements\n";
    echo "═══════════════════════════════════════════════════\n\n";

    // Generate random data
    $data = range(1, $size);
    shuffle($data);

    // 1. BST Performance
    echo "1. Binary Search Tree\n";
    $bst = new BinarySearchTree();

    $start = microtime(true);
    foreach ($data as $value) {
        $bst->insert($value);
    }
    $insertTime = (microtime(true) - $start) * 1000;

    $start = microtime(true);
    for ($i = 0; $i < $searchCount; $i++) {
        $bst->search(random_int(1, $size));
    }
    $searchTime = (microtime(true) - $start) * 1000;

    $start = microtime(true);
    $bst->delete($data[0]);
    $deleteTime = (microtime(true) - $start) * 1000;

    echo "   Insert all: " . number_format($insertTime, 2) . " ms\n";
    echo "   $searchCount searches: " . number_format($searchTime, 2) . " ms (" . number_format($searchTime/$searchCount, 4) . " ms each)\n";
    echo "   Single delete: " . number_format($deleteTime, 4) . " ms\n";
    echo "   Tree height: " . $bst->height() . " (optimal: " . floor(log($size, 2)) . ")\n\n";

    // 2. Sorted Array Performance
    echo "2. Sorted Array (with binary search)\n";
    $sortedArray = $data;

    $start = microtime(true);
    sort($sortedArray);
    $insertTime = (microtime(true) - $start) * 1000;

    $start = microtime(true);
    for ($i = 0; $i < $searchCount; $i++) {
        $target = random_int(1, $size);
        // Binary search
        $left = 0;
        $right = count($sortedArray) - 1;
        while ($left <= $right) {
            $mid = intval(($left + $right) / 2);
            if ($sortedArray[$mid] === $target) {
                break;
            }
            if ($sortedArray[$mid] < $target) {
                $left = $mid + 1;
            } else {
                $right = $mid - 1;
            }
        }
    }
    $searchTime = (microtime(true) - $start) * 1000;

    $start = microtime(true);
    $key = array_search($data[0], $sortedArray);
    if ($key !== false) {
        array_splice($sortedArray, $key, 1);
    }
    $deleteTime = (microtime(true) - $start) * 1000;

    echo "   Sort array: " . number_format($insertTime, 2) . " ms\n";
    echo "   $searchCount searches: " . number_format($searchTime, 2) . " ms (" . number_format($searchTime/$searchCount, 4) . " ms each)\n";
    echo "   Single delete: " . number_format($deleteTime, 4) . " ms (includes shift)\n\n";

    // 3. Hash Table Performance
    echo "3. Hash Table (PHP associative array)\n";
    $hashTable = [];

    $start = microtime(true);
    foreach ($data as $value) {
        $hashTable[$value] = true;
    }
    $insertTime = (microtime(true) - $start) * 1000;

    $start = microtime(true);
    for ($i = 0; $i < $searchCount; $i++) {
        isset($hashTable[random_int(1, $size)]);
    }
    $searchTime = (microtime(true) - $start) * 1000;

    $start = microtime(true);
    unset($hashTable[$data[0]]);
    $deleteTime = (microtime(true) - $start) * 1000;

    echo "   Insert all: " . number_format($insertTime, 2) . " ms\n";
    echo "   $searchCount searches: " . number_format($searchTime, 2) . " ms (" . number_format($searchTime/$searchCount, 4) . " ms each)\n";
    echo "   Single delete: " . number_format($deleteTime, 4) . " ms\n\n";

    // Memory comparison
    echo "4. Memory Usage Estimate\n";
    echo "   BST: ~" . ($size * 40) . " bytes (node objects)\n";
    echo "   Array: ~" . ($size * 16) . " bytes (packed integers)\n";
    echo "   Hash Table: ~" . ($size * 56) . " bytes (array overhead)\n\n";
}

echo "═══════════════════════════════════════════════════\n";
echo "Summary: When to Use Each Structure\n";
echo "═══════════════════════════════════════════════════\n\n";

echo "✓ Use BST when:\n";
echo "  - Need sorted traversal (inorder)\n";
echo "  - Range queries are common\n";
echo "  - Need fast insert AND sorted order\n";
echo "  - Memory is limited\n\n";

echo "✓ Use Sorted Array when:\n";
echo "  - Data is mostly static (few inserts/deletes)\n";
echo "  - Need very fast binary search\n";
echo "  - Memory efficiency is critical\n";
echo "  - Simple range queries\n\n";

echo "✓ Use Hash Table when:\n";
echo "  - Only need exact key lookups\n";
echo "  - No need for sorting\n";
echo "  - Insert/delete are frequent\n";
echo "  - Fastest possible search is required\n\n";

echo "Key Insights:\n";
echo "  • Hash table: Fastest for lookups (O(1) average)\n";
echo "  • BST: Best balance of sorted order + fast operations\n";
echo "  • Sorted Array: Best for read-heavy, static data\n";
echo "  • BST height matters! Unbalanced = O(n) worst case\n";

echo "\n✅ Complete! Performance comparison demonstrated.\n";
