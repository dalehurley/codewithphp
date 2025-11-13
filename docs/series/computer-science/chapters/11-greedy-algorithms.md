---
title: "11: Greedy Algorithms"
description: "Make locally optimal choices that lead to globally optimal solutions. Learn the greedy approach, solve coin change problems, activity selection, and understand when greedy algorithms work."
series: "computer-science"
chapter: 11
order: 11
difficulty: "Intermediate"
prerequisites: ["Algorithm analysis", "Sorting"]
---

# Chapter 11: Greedy Algorithms

## Introduction

Greedy algorithms make the locally optimal choice at each step, hoping to find a global optimum. They're simple, fast, and work perfectly for certain problems—but can fail spectacularly for others.

In this chapter, you'll learn:

- The greedy approach
- Classic greedy problems
- When greedy algorithms work
- When they fail

## The Greedy Strategy

**Greedy approach**:
1. Make the best choice available at each step
2. Never reconsider previous choices
3. Hope that local optimums lead to global optimum

### Example: Coin Change

```php
<?php

function greedyCoinChange(int $amount, array $coins): array {
    rsort($coins); // Sort descending
    $result = [];

    foreach ($coins as $coin) {
        while ($amount >= $coin) {
            $result[] = $coin;
            $amount -= $coin;
        }
    }

    return $amount === 0 ? $result : [];
}

$coins = [25, 10, 5, 1]; // US coins
print_r(greedyCoinChange(41, $coins));
// [25, 10, 5, 1] - 4 coins
```

**Works for US coins, but not always optimal!**

```php
$coins = [25, 10, 1]; // Different coin system
greedyCoinChange(30, $coins); // [25, 1, 1, 1, 1, 1] - 6 coins
// Optimal: [10, 10, 10] - 3 coins
// Greedy fails here!
```

## Classic Greedy Problems

### 1. Activity Selection

Select maximum number of non-overlapping activities.

```php
<?php

function activitySelection(array $activities): array {
    // Sort by end time
    usort($activities, fn($a, $b) => $a['end'] <=> $b['end']);

    $selected = [$activities[0]];
    $lastEnd = $activities[0]['end'];

    for ($i = 1; $i < count($activities); $i++) {
        if ($activities[$i]['start'] >= $lastEnd) {
            $selected[] = $activities[$i];
            $lastEnd = $activities[$i]['end'];
        }
    }

    return $selected;
}

$activities = [
    ['start' => 1, 'end' => 4],
    ['start' => 3, 'end' => 5],
    ['start' => 0, 'end' => 6],
    ['start' => 5, 'end' => 7],
    ['start' => 8, 'end' => 9],
    ['start' => 5, 'end' => 9]
];

$result = activitySelection($activities);
// Selects: [1,4], [5,7], [8,9]
```

**Time**: O(n log n) due to sorting
**Greedy choice**: Always pick activity that ends earliest

### 2. Fractional Knapsack

Fill knapsack to maximize value (can take fractions).

```php
<?php

function fractionalKnapsack(array $items, float $capacity): float {
    // Calculate value per weight
    foreach ($items as &$item) {
        $item['ratio'] = $item['value'] / $item['weight'];
    }

    // Sort by ratio descending
    usort($items, fn($a, $b) => $b['ratio'] <=> $a['ratio']);

    $totalValue = 0;

    foreach ($items as $item) {
        if ($capacity >= $item['weight']) {
            // Take whole item
            $totalValue += $item['value'];
            $capacity -= $item['weight'];
        } else {
            // Take fraction
            $totalValue += $item['ratio'] * $capacity;
            break;
        }
    }

    return $totalValue;
}

$items = [
    ['weight' => 10, 'value' => 60],
    ['weight' => 20, 'value' => 100],
    ['weight' => 30, 'value' => 120]
];

echo fractionalKnapsack($items, 50); // 240
```

**Note**: For 0/1 knapsack (can't take fractions), greedy doesn't work—use dynamic programming.

### 3. Huffman Coding

Build optimal prefix-free binary code for compression.

```php
<?php

class HuffmanNode {
    public function __construct(
        public ?string $char,
        public int $frequency,
        public ?HuffmanNode $left = null,
        public ?HuffmanNode $right = null
    ) {}
}

function huffmanCoding(array $frequencies): array {
    $heap = [];

    // Create leaf nodes
    foreach ($frequencies as $char => $freq) {
        $heap[] = new HuffmanNode($char, $freq);
    }

    // Build tree
    while (count($heap) > 1) {
        // Sort by frequency
        usort($heap, fn($a, $b) => $a->frequency <=> $b->frequency);

        // Take two minimum
        $left = array_shift($heap);
        $right = array_shift($heap);

        // Create parent
        $parent = new HuffmanNode(
            null,
            $left->frequency + $right->frequency,
            $left,
            $right
        );

        $heap[] = $parent;
    }

    // Generate codes
    $codes = [];
    generateCodes($heap[0], '', $codes);

    return $codes;
}

function generateCodes(?HuffmanNode $node, string $code, array &$codes): void {
    if ($node === null) return;

    if ($node->char !== null) {
        $codes[$node->char] = $code;
        return;
    }

    generateCodes($node->left, $code . '0', $codes);
    generateCodes($node->right, $code . '1', $codes);
}

$frequencies = ['a' => 5, 'b' => 9, 'c' => 12, 'd' => 13, 'e' => 16, 'f' => 45];
$codes = huffmanCoding($frequencies);
print_r($codes);
// More frequent chars get shorter codes
```

## When Greedy Works

Greedy algorithms work when a problem has:

1. **Greedy choice property**: Locally optimal choice leads to globally optimal solution
2. **Optimal substructure**: Optimal solution contains optimal solutions to subproblems

**Examples where greedy works**:
- Activity selection
- Fractional knapsack
- Huffman coding
- Minimum spanning tree (Prim's, Kruskal's)
- Dijkstra's shortest path

**Examples where greedy fails**:
- 0/1 knapsack
- Longest path problem
- Some coin change problems

## Greedy vs. Dynamic Programming

| Problem | Greedy | Dynamic Programming |
|---------|--------|---------------------|
| Activity Selection | ✓ Works | Overkill |
| Fractional Knapsack | ✓ Works | Unnecessary |
| 0/1 Knapsack | ✗ Fails | ✓ Works |
| Coin Change | Sometimes | Always works |

## Key Takeaways

- **Greedy**: Make locally optimal choice at each step
- Works when problem has **greedy choice property**
- Much **simpler and faster** than dynamic programming
- **Doesn't always work**—verify correctness first
- Common in optimization problems

## Exercises

1. **Jump Game**: Determine if you can reach the last index given max jump lengths.

2. **Gas Station**: Find starting station to complete circular route.

3. **Minimum Platforms**: Find minimum platforms needed at a train station.

4. **Job Sequencing**: Schedule jobs to maximize profit within deadlines.

## What's Next?

When greedy fails and we need to explore multiple possibilities, we use **Backtracking**. Chapter 12 covers this exhaustive search technique.

---

**Further Reading**:
- [Greedy Algorithms (Wikipedia)](https://en.wikipedia.org/wiki/Greedy_algorithm)
- [Greedy vs Dynamic Programming](https://www.geeksforgeeks.org/greedy-algorithms/)
