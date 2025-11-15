<?php

declare(strict_types=1);

/**
 * Hash Set Implementation
 *
 * Demonstrates:
 * - Set data structure using hash table
 * - Set operations: add, remove, contains
 * - Set theory: union, intersection, difference
 * - Subset and superset checks
 */

class HashSet
{
    private array $elements = [];
    private int $count = 0;

    /**
     * Add element to set - O(1)
     */
    public function add(mixed $element): void
    {
        $key = $this->getKey($element);

        if (!isset($this->elements[$key])) {
            $this->elements[$key] = $element;
            $this->count++;
        }
    }

    /**
     * Remove element from set - O(1)
     */
    public function remove(mixed $element): bool
    {
        $key = $this->getKey($element);

        if (isset($this->elements[$key])) {
            unset($this->elements[$key]);
            $this->count--;
            return true;
        }

        return false;
    }

    /**
     * Check if element exists - O(1)
     */
    public function contains(mixed $element): bool
    {
        $key = $this->getKey($element);
        return isset($this->elements[$key]);
    }

    /**
     * Get set size
     */
    public function size(): int
    {
        return $this->count;
    }

    /**
     * Check if set is empty
     */
    public function isEmpty(): bool
    {
        return $this->count === 0;
    }

    /**
     * Clear all elements
     */
    public function clear(): void
    {
        $this->elements = [];
        $this->count = 0;
    }

    /**
     * Get all elements
     */
    public function toArray(): array
    {
        return array_values($this->elements);
    }

    /**
     * Union: elements in either set
     */
    public function union(HashSet $other): HashSet
    {
        $result = new HashSet();

        foreach ($this->elements as $element) {
            $result->add($element);
        }

        foreach ($other->elements as $element) {
            $result->add($element);
        }

        return $result;
    }

    /**
     * Intersection: elements in both sets
     */
    public function intersection(HashSet $other): HashSet
    {
        $result = new HashSet();

        foreach ($this->elements as $element) {
            if ($other->contains($element)) {
                $result->add($element);
            }
        }

        return $result;
    }

    /**
     * Difference: elements in this set but not in other
     */
    public function difference(HashSet $other): HashSet
    {
        $result = new HashSet();

        foreach ($this->elements as $element) {
            if (!$other->contains($element)) {
                $result->add($element);
            }
        }

        return $result;
    }

    /**
     * Check if this set is subset of other
     */
    public function isSubsetOf(HashSet $other): bool
    {
        foreach ($this->elements as $element) {
            if (!$other->contains($element)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if this set is superset of other
     */
    public function isSupersetOf(HashSet $other): bool
    {
        return $other->isSubsetOf($this);
    }

    /**
     * Check if sets are disjoint (no common elements)
     */
    public function isDisjoint(HashSet $other): bool
    {
        foreach ($this->elements as $element) {
            if ($other->contains($element)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get unique key for element
     */
    private function getKey(mixed $element): string
    {
        if (is_scalar($element)) {
            return (string)$element;
        }

        return serialize($element);
    }

    /**
     * String representation
     */
    public function __toString(): string
    {
        return '{' . implode(', ', $this->toArray()) . '}';
    }
}

// Demo
echo "=== Hash Set Implementation ===\n\n";

// Test 1: Basic Operations
echo "1. Basic Set Operations\n";
echo "========================\n";

$set = new HashSet();
echo "Created empty set: $set\n";
echo "Is empty: " . ($set->isEmpty() ? "Yes" : "No") . "\n\n";

echo "Adding elements: 1, 2, 3, 4, 5\n";
foreach ([1, 2, 3, 4, 5] as $num) {
    $set->add($num);
}
echo "Set: $set\n";
echo "Size: " . $set->size() . "\n\n";

echo "Adding duplicate element: 3\n";
$set->add(3);
echo "Set: $set (duplicates ignored)\n";
echo "Size: " . $set->size() . " (unchanged)\n\n";

echo "Checking membership:\n";
foreach ([3, 10] as $num) {
    $contains = $set->contains($num);
    echo "  Contains $num: " . ($contains ? "Yes" : "No") . "\n";
}
echo "\n";

echo "Removing element: 3\n";
$set->remove(3);
echo "Set: $set\n";
echo "Size: " . $set->size() . "\n\n";

// Test 2: Set Theory Operations
echo "2. Set Theory Operations\n";
echo "=========================\n";

$setA = new HashSet();
$setB = new HashSet();

foreach ([1, 2, 3, 4, 5] as $num) {
    $setA->add($num);
}

foreach ([4, 5, 6, 7, 8] as $num) {
    $setB->add($num);
}

echo "Set A: $setA\n";
echo "Set B: $setB\n\n";

$union = $setA->union($setB);
echo "Union (A ∪ B): $union\n";

$intersection = $setA->intersection($setB);
echo "Intersection (A ∩ B): $intersection\n";

$differenceAB = $setA->difference($setB);
echo "Difference (A - B): $differenceAB\n";

$differenceBA = $setB->difference($setA);
echo "Difference (B - A): $differenceBA\n\n";

// Test 3: Subset and Superset
echo "3. Subset and Superset Checks\n";
echo "==============================\n";

$setC = new HashSet();
$setD = new HashSet();

foreach ([1, 2, 3] as $num) {
    $setC->add($num);
}

foreach ([1, 2, 3, 4, 5] as $num) {
    $setD->add($num);
}

echo "Set C: $setC\n";
echo "Set D: $setD\n\n";

echo "C is subset of D: " . ($setC->isSubsetOf($setD) ? "Yes" : "No") . "\n";
echo "D is superset of C: " . ($setD->isSupersetOf($setC) ? "Yes" : "No") . "\n";
echo "C is superset of D: " . ($setC->isSupersetOf($setD) ? "Yes" : "No") . "\n\n";

// Test 4: Disjoint Sets
echo "4. Disjoint Sets\n";
echo "=================\n";

$setE = new HashSet();
$setF = new HashSet();

foreach ([1, 2, 3] as $num) {
    $setE->add($num);
}

foreach ([4, 5, 6] as $num) {
    $setF->add($num);
}

echo "Set E: $setE\n";
echo "Set F: $setF\n";
echo "E and F are disjoint: " . ($setE->isDisjoint($setF) ? "Yes" : "No") . "\n\n";

// Test 5: Practical Example - Finding Unique Elements
echo "5. Practical Example: Remove Duplicates\n";
echo "========================================\n";

$numbers = [1, 2, 3, 2, 4, 1, 5, 3, 6, 4];
echo "Array with duplicates: [" . implode(', ', $numbers) . "]\n";

$uniqueSet = new HashSet();
foreach ($numbers as $num) {
    $uniqueSet->add($num);
}

echo "Unique elements: $uniqueSet\n";
echo "Original count: " . count($numbers) . "\n";
echo "Unique count: " . $uniqueSet->size() . "\n\n";

// Test 6: String Set
echo "6. String Set Example\n";
echo "======================\n";

$fruits = new HashSet();
foreach (['apple', 'banana', 'cherry', 'apple', 'date'] as $fruit) {
    $fruits->add($fruit);
}

echo "Fruits: $fruits\n";
echo "Count: " . $fruits->size() . " (duplicates removed)\n\n";

echo "Performance Characteristics:\n";
echo "  • Add:      O(1)\n";
echo "  • Remove:   O(1)\n";
echo "  • Contains: O(1)\n";
echo "  • Union:    O(n + m)\n";
echo "  • Intersection: O(min(n, m))\n";
echo "  • Difference: O(n)\n\n";

echo "Use Cases:\n";
echo "  ✓ Remove duplicates from collection\n";
echo "  ✓ Check membership (O(1) vs O(n) for array)\n";
echo "  ✓ Set theory operations (union, intersection)\n";
echo "  ✓ Track visited/seen elements in algorithms\n";

echo "\n✅ Complete! Hash set implementation demonstrated.\n";
