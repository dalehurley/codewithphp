<?php
declare(strict_types=1);

echo "=== Backtracking with Recursion ===\n\n";

// Generate all permutations
function permutations(array $arr): array {
    if (count($arr) <= 1) return [$arr];
    
    $result = [];
    for ($i = 0; $i < count($arr); $i++) {
        $current = $arr[$i];
        $remaining = array_merge(
            array_slice($arr, 0, $i),
            array_slice($arr, $i + 1)
        );
        
        foreach (permutations($remaining) as $perm) {
            $result[] = array_merge([$current], $perm);
        }
    }
    return $result;
}

// Generate all subsets
function subsets(array $arr): array {
    if (empty($arr)) return [[]];
    
    $first = $arr[0];
    $rest = array_slice($arr, 1);
    $subsetsWithoutFirst = subsets($rest);
    $subsetsWithFirst = array_map(
        fn($subset) => array_merge([$first], $subset),
        $subsetsWithoutFirst
    );
    
    return array_merge($subsetsWithoutFirst, $subsetsWithFirst);
}

$perms = permutations([1, 2, 3]);
echo "Permutations of [1,2,3]: " . count($perms) . " total\n";
foreach (array_slice($perms, 0, 6) as $perm) {
    echo "  " . implode(',', $perm) . "\n";
}

$subs = subsets([1, 2, 3]);
echo "\nSubsets of [1,2,3]: " . count($subs) . " total\n";
foreach ($subs as $subset) {
    echo "  [" . implode(',', $subset) . "]\n";
}

echo "\n✅ Complete!\n";
