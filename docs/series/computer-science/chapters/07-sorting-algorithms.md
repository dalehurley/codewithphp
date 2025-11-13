---
title: "07: Sorting Algorithms"
description: "Implement bubble sort, selection sort, insertion sort, merge sort, quick sort, and heap sort. Compare their time complexity and learn when to use each algorithm."
series: "computer-science"
chapter: 7
order: 7
difficulty: "Intermediate"
prerequisites: ["Arrays", "Algorithm analysis", "Recursion"]
---

# Chapter 07: Sorting Algorithms

## Introduction

Sorting is one of the most fundamental operations in computer science. Understanding different sorting algorithms—their strengths, weaknesses, and appropriate use cases—is essential for every developer.

In this chapter, you'll learn:

- Common sorting algorithms and their implementations
- Time and space complexity analysis
- When to use each algorithm
- Stability and in-place sorting

## Why Sorting Matters

Sorted data enables:
- Binary search (O(log n) instead of O(n))
- Efficient duplicate detection
- Database query optimization
- Better data visualization

## Comparison-Based Sorting

### 1. Bubble Sort — O(n²)

Repeatedly swap adjacent elements if they're in the wrong order.

```mermaid
graph TB
    subgraph "Bubble Sort Example: [5, 2, 8, 1]"
        direction LR
        P1["Pass 1:<br/>[5,2,8,1]<br/>↓<br/>[2,5,8,1]<br/>↓<br/>[2,5,1,8]"]
        P2["Pass 2:<br/>[2,5,1,8]<br/>↓<br/>[2,1,5,8]"]
        P3["Pass 3:<br/>[2,1,5,8]<br/>↓<br/>[1,2,5,8]"]
        P4["✓ Sorted:<br/>[1,2,5,8]"]

        P1 --> P2
        P2 --> P3
        P3 --> P4
    end

    style P1 fill:#FF9800
    style P2 fill:#FFA726
    style P3 fill:#FFB74D
    style P4 fill:#4CAF50
```

**How it works**: Largest elements "bubble up" to the end in each pass.

```php
<?php

function bubbleSort(array $arr): array {
    $n = count($arr);

    for ($i = 0; $i < $n - 1; $i++) {
        $swapped = false;

        for ($j = 0; $j < $n - $i - 1; $j++) {
            if ($arr[$j] > $arr[$j + 1]) {
                // Swap
                [$arr[$j], $arr[$j + 1]] = [$arr[$j + 1], $arr[$j]];
                $swapped = true;
            }
        }

        // Optimization: If no swaps, already sorted
        if (!$swapped) break;
    }

    return $arr;
}

$numbers = [64, 34, 25, 12, 22, 11, 90];
print_r(bubbleSort($numbers));
// [11, 12, 22, 25, 34, 64, 90]
```

**Complexity**: O(n²) time, O(1) space
**Stable**: Yes
**Use**: Educational purposes only

### 2. Selection Sort — O(n²)

Find the minimum element and place it at the beginning.

```mermaid
graph TB
    subgraph "Selection Sort Example: [64, 25, 12, 22, 11]"
        S0["Initial:<br/>[64, 25, 12, 22, 11]"]
        S1["Find min (11):<br/>[11, 25, 12, 22, 64]"]
        S2["Find min (12):<br/>[11, 12, 25, 22, 64]"]
        S3["Find min (22):<br/>[11, 12, 22, 25, 64]"]
        S4["✓ Sorted:<br/>[11, 12, 22, 25, 64]"]

        S0 --> S1
        S1 --> S2
        S2 --> S3
        S3 --> S4
    end

    style S0 fill:#FF6B6B,color:#fff
    style S1 fill:#FFA500
    style S2 fill:#FFD700
    style S3 fill:#90EE90
    style S4 fill:#4CAF50
```

**How it works**: Select minimum from unsorted portion, swap with first unsorted element.

```php
<?php

function selectionSort(array $arr): array {
    $n = count($arr);

    for ($i = 0; $i < $n - 1; $i++) {
        $minIndex = $i;

        // Find minimum in remaining array
        for ($j = $i + 1; $j < $n; $j++) {
            if ($arr[$j] < $arr[$minIndex]) {
                $minIndex = $j;
            }
        }

        // Swap with current position
        if ($minIndex !== $i) {
            [$arr[$i], $arr[$minIndex]] = [$arr[$minIndex], $arr[$i]];
        }
    }

    return $arr;
}
```

**Complexity**: O(n²) time, O(1) space
**Stable**: No (can be made stable)
**Use**: Small datasets with expensive swaps

### 3. Insertion Sort — O(n²)

Build sorted array one element at a time.

```mermaid
graph TB
    subgraph "Insertion Sort Example: [5, 2, 4, 6, 1, 3]"
        I0["Initial:<br/>[5 | 2, 4, 6, 1, 3]<br/>sorted | unsorted"]
        I1["Insert 2:<br/>[2, 5 | 4, 6, 1, 3]"]
        I2["Insert 4:<br/>[2, 4, 5 | 6, 1, 3]"]
        I3["Insert 6:<br/>[2, 4, 5, 6 | 1, 3]"]
        I4["Insert 1:<br/>[1, 2, 4, 5, 6 | 3]"]
        I5["Insert 3:<br/>[1, 2, 3, 4, 5, 6]"]

        I0 --> I1
        I1 --> I2
        I2 --> I3
        I3 --> I4
        I4 --> I5
    end

    style I0 fill:#FF6B6B,color:#fff
    style I1 fill:#FF8C00
    style I2 fill:#FFA500
    style I3 fill:#FFD700
    style I4 fill:#90EE90
    style I5 fill:#4CAF50
```

**How it works**: Pick each element and insert it into its correct position in the sorted portion.

```php
<?php

function insertionSort(array $arr): array {
    $n = count($arr);

    for ($i = 1; $i < $n; $i++) {
        $key = $arr[$i];
        $j = $i - 1;

        // Move elements greater than key one position ahead
        while ($j >= 0 && $arr[$j] > $key) {
            $arr[$j + 1] = $arr[$j];
            $j--;
        }

        $arr[$j + 1] = $key;
    }

    return $arr;
}
```

**Complexity**: O(n²) time, O(1) space, O(n) best case
**Stable**: Yes
**Use**: Small datasets, nearly sorted data, online sorting

### 4. Merge Sort — O(n log n)

Divide array in half, sort each half, merge them.

```mermaid
graph TB
    subgraph "Merge Sort: Divide and Conquer"
        M0["[38, 27, 43, 3]"]
        M1["[38, 27]"]
        M2["[43, 3]"]
        M3["[38]"]
        M4["[27]"]
        M5["[43]"]
        M6["[3]"]
        M7["[27, 38]"]
        M8["[3, 43]"]
        M9["[3, 27, 38, 43]"]

        M0 -->|"Divide"| M1
        M0 -->|"Divide"| M2
        M1 -->|"Divide"| M3
        M1 -->|"Divide"| M4
        M2 -->|"Divide"| M5
        M2 -->|"Divide"| M6
        M3 -->|"Merge"| M7
        M4 -->|"Merge"| M7
        M5 -->|"Merge"| M8
        M6 -->|"Merge"| M8
        M7 -->|"Merge"| M9
        M8 -->|"Merge"| M9
    end

    style M0 fill:#FF6B6B,color:#fff
    style M3 fill:#90EE90
    style M4 fill:#90EE90
    style M5 fill:#90EE90
    style M6 fill:#90EE90
    style M7 fill:#FFD700
    style M8 fill:#FFD700
    style M9 fill:#4CAF50
```

**How it works**: Recursively divide, then merge sorted halves. O(n log n) guaranteed!

```php
<?php

function mergeSort(array $arr): array {
    if (count($arr) <= 1) {
        return $arr;
    }

    $mid = (int)(count($arr) / 2);
    $left = array_slice($arr, 0, $mid);
    $right = array_slice($arr, $mid);

    return merge(mergeSort($left), mergeSort($right));
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

    // Append remaining elements
    while ($i < count($left)) {
        $result[] = $left[$i++];
    }
    while ($j < count($right)) {
        $result[] = $right[$j++];
    }

    return $result;
}

$numbers = [64, 34, 25, 12, 22, 11, 90];
print_r(mergeSort($numbers));
```

**Complexity**: O(n log n) time, O(n) space
**Stable**: Yes
**Use**: Large datasets, linked lists, external sorting

### 5. Quick Sort — O(n log n) average

Choose a pivot, partition array around it, recursively sort partitions.

```mermaid
graph TB
    subgraph "Quick Sort: Partition Around Pivot"
        Q0["[7, 2, 1, 6, 8, 5, 3]<br/>Pivot: 3"]
        Q1["[2, 1, 3] | [6, 8, 5, 7]<br/>Partition: < 3 | > 3"]
        Q2["[1, 2] | [3] | [5, 6, 7, 8]<br/>Recursively sort"]
        Q3["[1, 2, 3, 5, 6, 7, 8]<br/>✓ Sorted"]

        Q0 -->|"Partition"| Q1
        Q1 -->|"Recurse"| Q2
        Q2 -->|"Combine"| Q3
    end

    style Q0 fill:#FF6B6B,color:#fff
    style Q1 fill:#FFA500
    style Q2 fill:#FFD700
    style Q3 fill:#4CAF50
```

**How it works**: Pick pivot, partition into smaller/larger, recursively sort both sides.

```php
<?php

function quickSort(array $arr): array {
    if (count($arr) <= 1) {
        return $arr;
    }

    $pivot = $arr[0];
    $left = [];
    $right = [];

    for ($i = 1; $i < count($arr); $i++) {
        if ($arr[$i] < $pivot) {
            $left[] = $arr[$i];
        } else {
            $right[] = $arr[$i];
        }
    }

    return array_merge(
        quickSort($left),
        [$pivot],
        quickSort($right)
    );
}

// In-place version (more efficient)
function quickSortInPlace(array &$arr, int $low, int $high): void {
    if ($low < $high) {
        $pi = partition($arr, $low, $high);

        quickSortInPlace($arr, $low, $pi - 1);
        quickSortInPlace($arr, $pi + 1, $high);
    }
}

function partition(array &$arr, int $low, int $high): int {
    $pivot = $arr[$high];
    $i = $low - 1;

    for ($j = $low; $j < $high; $j++) {
        if ($arr[$j] < $pivot) {
            $i++;
            [$arr[$i], $arr[$j]] = [$arr[$j], $arr[$i]];
        }
    }

    [$arr[$i + 1], $arr[$high]] = [$arr[$high], $arr[$i + 1]];
    return $i + 1;
}

$numbers = [64, 34, 25, 12, 22, 11, 90];
print_r(quickSort($numbers));
```

**Complexity**: O(n log n) average, O(n²) worst, O(log n) space
**Stable**: No (can be made stable)
**Use**: General purpose, when average case matters

### 6. Heap Sort — O(n log n)

Build a max heap, repeatedly extract maximum.

```mermaid
graph TB
    subgraph "Heap Sort: Max Heap Structure"
        H0["Build Max Heap:<br/>[4,10,3,5,1]"]
        H1["Max Heap:<br/>[10,5,3,4,1]"]
        H2["Extract 10:<br/>[5,4,3,1] + [10]"]
        H3["Extract 5:<br/>[4,1,3] + [5,10]"]
        H4["Extract 4:<br/>[3,1] + [4,5,10]"]
        H5["Extract 3:<br/>[1] + [3,4,5,10]"]
        H6["✓ Sorted:<br/>[1,3,4,5,10]"]

        H0 --> H1
        H1 --> H2
        H2 --> H3
        H3 --> H4
        H4 --> H5
        H5 --> H6
    end

    style H0 fill:#FF6B6B,color:#fff
    style H1 fill:#FFA500
    style H2 fill:#FFD700
    style H3 fill:#FFE082
    style H4 fill:#C5E1A5
    style H5 fill:#90EE90
    style H6 fill:#4CAF50
```

**How it works**: Build max heap, repeatedly extract max (root) and heapify. O(1) space!

```php
<?php

function heapSort(array $arr): array {
    $n = count($arr);

    // Build max heap
    for ($i = (int)($n / 2) - 1; $i >= 0; $i--) {
        heapify($arr, $n, $i);
    }

    // Extract elements from heap one by one
    for ($i = $n - 1; $i > 0; $i--) {
        // Move current root to end
        [$arr[0], $arr[$i]] = [$arr[$i], $arr[0]];

        // Heapify reduced heap
        heapify($arr, $i, 0);
    }

    return $arr;
}

function heapify(array &$arr, int $n, int $i): void {
    $largest = $i;
    $left = 2 * $i + 1;
    $right = 2 * $i + 2;

    if ($left < $n && $arr[$left] > $arr[$largest]) {
        $largest = $left;
    }

    if ($right < $n && $arr[$right] > $arr[$largest]) {
        $largest = $right;
    }

    if ($largest !== $i) {
        [$arr[$i], $arr[$largest]] = [$arr[$largest], $arr[$i]];
        heapify($arr, $n, $largest);
    }
}
```

**Complexity**: O(n log n) time, O(1) space
**Stable**: No
**Use**: Memory-constrained environments

## Non-Comparison Sorts

### Counting Sort — O(n + k)

Count occurrences of each value.

```mermaid
graph TB
    subgraph "Counting Sort Example: [4, 2, 2, 8, 3, 3, 1]"
        C0["Input:<br/>[4, 2, 2, 8, 3, 3, 1]"]
        C1["Count array:<br/>1→1, 2→2, 3→2, 4→1, 8→1"]
        C2["Build output:<br/>[1] + [2,2] + [3,3] + [4] + [8]"]
        C3["✓ Sorted:<br/>[1, 2, 2, 3, 3, 4, 8]"]

        C0 -->|"Count"| C1
        C1 -->|"Reconstruct"| C2
        C2 --> C3
    end

    style C0 fill:#FF6B6B,color:#fff
    style C1 fill:#FFA500
    style C2 fill:#FFD700
    style C3 fill:#4CAF50
```

**How it works**: Count frequency of each value, reconstruct sorted array. O(n+k) where k is range!

```php
<?php

function countingSort(array $arr): array {
    if (empty($arr)) return $arr;

    $max = max($arr);
    $min = min($arr);
    $range = $max - $min + 1;

    $count = array_fill(0, $range, 0);
    $output = [];

    // Count occurrences
    foreach ($arr as $num) {
        $count[$num - $min]++;
    }

    // Build output array
    for ($i = 0; $i < $range; $i++) {
        for ($j = 0; $j < $count[$i]; $j++) {
            $output[] = $i + $min;
        }
    }

    return $output;
}

$numbers = [4, 2, 2, 8, 3, 3, 1];
print_r(countingSort($numbers));
```

**Complexity**: O(n + k) time, O(k) space (k = range)
**Use**: Small range of integers

## Sorting Algorithm Comparison

| Algorithm | Best | Average | Worst | Space | Stable |
|-----------|------|---------|-------|-------|--------|
| Bubble | O(n) | O(n²) | O(n²) | O(1) | Yes |
| Selection | O(n²) | O(n²) | O(n²) | O(1) | No |
| Insertion | O(n) | O(n²) | O(n²) | O(1) | Yes |
| Merge | O(n log n) | O(n log n) | O(n log n) | O(n) | Yes |
| Quick | O(n log n) | O(n log n) | O(n²) | O(log n) | No |
| Heap | O(n log n) | O(n log n) | O(n log n) | O(1) | No |
| Counting | O(n+k) | O(n+k) | O(n+k) | O(k) | Yes |

## PHP's Built-in Sorting

```php
<?php

$arr = [3, 1, 4, 1, 5, 9, 2, 6];

// Sort values (maintains keys)
sort($arr);        // [1, 1, 2, 3, 4, 5, 6, 9]

// Sort associative array by value
asort($arr);

// Sort associative array by key
ksort($arr);

// Custom comparison
usort($arr, function($a, $b) {
    return $a <=> $b; // Spaceship operator
});
```

PHP uses **Timsort** (hybrid of merge sort and insertion sort) for its sorting functions.

## When to Use Each Algorithm

```mermaid
graph TB
    START["Which sorting<br/>algorithm?"]
    Q1{"Dataset size?"}
    Q2{"Stability<br/>required?"}
    Q3{"Memory<br/>constrained?"}
    Q4{"Integer data<br/>with small range?"}
    Q5{"Linked list?"}

    START --> Q1
    Q1 -->|"< 50 elements"| INS["Insertion Sort<br/>O(n²) - Simple"]
    Q1 -->|"> 50 elements"| Q4
    Q4 -->|"Yes"| CNT["Counting Sort<br/>O(n+k) - Fast!"]
    Q4 -->|"No"| Q2
    Q2 -->|"Yes"| Q5
    Q5 -->|"Yes"| MRG["Merge Sort<br/>O(n log n) - Stable"]
    Q5 -->|"No"| MRG2["Merge Sort<br/>or Timsort"]
    Q2 -->|"No"| Q3
    Q3 -->|"Yes"| HEP["Heap Sort<br/>O(n log n) - O(1) space"]
    Q3 -->|"No"| QCK["Quick Sort<br/>O(n log n) avg - Fast"]

    style START fill:#2196F3,color:#fff
    style INS fill:#4CAF50
    style CNT fill:#9C27B0,color:#fff
    style MRG fill:#FF9800
    style MRG2 fill:#FF9800
    style HEP fill:#F44336,color:#fff
    style QCK fill:#FFD700
```

**Quick Selection Guide**:
- **Bubble/Selection/Insertion**: Small datasets (< 50 elements), educational
- **Merge Sort**: Stable sort needed, linked lists, external sorting
- **Quick Sort**: General purpose, average case important
- **Heap Sort**: Memory constrained, predictable performance
- **Counting/Radix**: Integer data with limited range

## Key Takeaways

- **O(n²)** algorithms are simple but slow for large datasets
- **O(n log n)** algorithms are efficient for general use
- **Merge sort** is stable and consistent
- **Quick sort** is fast on average but has worst-case O(n²)
- **Heap sort** uses O(1) space
- **Counting sort** beats O(n log n) for specific data types

## Exercises

1. **Implement selection sort** that finds both min and max in each pass.

2. **Kth largest element**: Find the kth largest element using quickselect.

3. **Sort colors**: Sort an array of 0s, 1s, and 2s in one pass (Dutch National Flag problem).

4. **Merge k sorted arrays**: Merge multiple sorted arrays efficiently.

5. **Custom sort**: Sort strings by length, then alphabetically.

## What's Next?

Now that data is sorted, searching becomes much faster. In Chapter 08, we'll explore **Searching Algorithms**, including binary search and its variations.

---

**Further Reading**:
- [Sorting Algorithm Animations](https://www.toptal.com/developers/sorting-algorithms)
- [Timsort Explained](https://en.wikipedia.org/wiki/Timsort)
- [Comparison of Sorting Algorithms](https://en.wikipedia.org/wiki/Sorting_algorithm)
