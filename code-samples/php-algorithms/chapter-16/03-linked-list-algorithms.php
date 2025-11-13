<?php

declare(strict_types=1);

/**
 * Linked List Algorithms
 *
 * Common algorithms for linked list operations including:
 * - Reverse a linked list
 * - Find middle element (fast/slow pointer)
 * - Detect cycle (Floyd's algorithm)
 * - Remove nth node from end
 * - Merge two sorted lists
 * - Check for palindrome
 *
 * @author PHP Algorithms Series
 * @version 1.0.0
 */

require_once __DIR__ . '/01-singly-linked-list.php';

/**
 * Collection of linked list algorithms
 */
class LinkedListAlgorithms
{
    /**
     * Reverse a linked list iteratively - O(n) time, O(1) space
     *
     * @param LinkedList $list The list to reverse
     * @return void
     */
    public static function reverseIterative(LinkedList $list): void
    {
        $reflection = new ReflectionClass($list);
        $headProperty = $reflection->getProperty('head');
        $headProperty->setAccessible(true);
        $tailProperty = $reflection->getProperty('tail');
        $tailProperty->setAccessible(true);

        $head = $headProperty->getValue($list);
        $prev = null;
        $current = $head;
        $oldTail = $current; // Will become new head

        while ($current !== null) {
            $next = $current->next;
            $current->next = $prev;
            $prev = $current;
            $current = $next;
        }

        // Update head and tail
        $headProperty->setValue($list, $prev);
        $tailProperty->setValue($list, $oldTail);
    }

    /**
     * Find the middle element of a linked list using fast/slow pointers - O(n)
     * When list has even number of elements, returns the first middle
     *
     * @param LinkedList $list The list to find middle of
     * @return mixed|null The middle element data or null if empty
     */
    public static function findMiddle(LinkedList $list): mixed
    {
        $reflection = new ReflectionClass($list);
        $headProperty = $reflection->getProperty('head');
        $headProperty->setAccessible(true);
        $head = $headProperty->getValue($list);

        if ($head === null) {
            return null;
        }

        $slow = $fast = $head;

        // Fast pointer moves 2x speed of slow pointer
        // When fast reaches end, slow is at middle
        while ($fast !== null && $fast->next !== null) {
            $slow = $slow->next;
            $fast = $fast->next->next;
        }

        return $slow->data;
    }

    /**
     * Detect if list has a cycle using Floyd's algorithm - O(n) time, O(1) space
     *
     * @param Node|null $head The head node
     * @return bool True if cycle exists, false otherwise
     */
    public static function hasCycle(?Node $head): bool
    {
        if ($head === null) {
            return false;
        }

        $slow = $fast = $head;

        while ($fast !== null && $fast->next !== null) {
            $slow = $slow->next;
            $fast = $fast->next->next;

            // If pointers meet, there's a cycle
            if ($slow === $fast) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove the nth node from the end of the list - O(n)
     *
     * @param LinkedList $list The list to modify
     * @param int $n Position from end (1-indexed)
     * @return bool True if removed, false if not possible
     */
    public static function removeNthFromEnd(LinkedList $list, int $n): bool
    {
        if ($n <= 0 || $list->isEmpty()) {
            return false;
        }

        $reflection = new ReflectionClass($list);
        $headProperty = $reflection->getProperty('head');
        $headProperty->setAccessible(true);
        $sizeProperty = $reflection->getProperty('size');
        $sizeProperty->setAccessible(true);

        $head = $headProperty->getValue($list);
        $size = $sizeProperty->getValue($list);

        if ($n > $size) {
            return false;
        }

        // Create dummy node for easier edge case handling
        $dummy = new Node(0);
        $dummy->next = $head;

        $slow = $fast = $dummy;

        // Move fast pointer n+1 steps ahead
        for ($i = 0; $i <= $n; $i++) {
            if ($fast === null) {
                return false;
            }
            $fast = $fast->next;
        }

        // Move both pointers until fast reaches end
        while ($fast !== null) {
            $slow = $slow->next;
            $fast = $fast->next;
        }

        // Delete node (slow->next is the node to delete)
        $slow->next = $slow->next?->next;

        // Update head if needed
        $headProperty->setValue($list, $dummy->next);
        $sizeProperty->setValue($list, $size - 1);

        return true;
    }

    /**
     * Merge two sorted linked lists into one sorted list - O(n + m)
     *
     * @param LinkedList $list1 First sorted list
     * @param LinkedList $list2 Second sorted list
     * @return LinkedList Merged sorted list
     */
    public static function mergeSortedLists(LinkedList $list1, LinkedList $list2): LinkedList
    {
        $reflection = new ReflectionClass($list1);
        $headProperty = $reflection->getProperty('head');
        $headProperty->setAccessible(true);

        $head1 = $headProperty->getValue($list1);
        $head2 = $headProperty->getValue($list2);

        $result = new LinkedList();
        $dummy = new Node(0);
        $current = $dummy;

        $current1 = $head1;
        $current2 = $head2;

        // Merge while both lists have elements
        while ($current1 !== null && $current2 !== null) {
            if ($current1->data <= $current2->data) {
                $current->next = new Node($current1->data);
                $current1 = $current1->next;
            } else {
                $current->next = new Node($current2->data);
                $current2 = $current2->next;
            }
            $current = $current->next;
        }

        // Append remaining elements
        while ($current1 !== null) {
            $current->next = new Node($current1->data);
            $current1 = $current1->next;
            $current = $current->next;
        }

        while ($current2 !== null) {
            $current->next = new Node($current2->data);
            $current2 = $current2->next;
            $current = $current->next;
        }

        // Set the result list's head
        $headProperty->setValue($result, $dummy->next);

        // Update size
        $sizeProperty = $reflection->getProperty('size');
        $sizeProperty->setAccessible(true);
        $newSize = $sizeProperty->getValue($list1) + $sizeProperty->getValue($list2);
        $sizeProperty->setValue($result, $newSize);

        return $result;
    }

    /**
     * Check if linked list is a palindrome - O(n) time, O(1) space
     *
     * @param LinkedList $list The list to check
     * @return bool True if palindrome, false otherwise
     */
    public static function isPalindrome(LinkedList $list): bool
    {
        if ($list->isEmpty() || $list->getSize() === 1) {
            return true;
        }

        // Convert to array for easier comparison
        $arr = $list->toArray();
        $left = 0;
        $right = count($arr) - 1;

        while ($left < $right) {
            if ($arr[$left] !== $arr[$right]) {
                return false;
            }
            $left++;
            $right--;
        }

        return true;
    }

    /**
     * Remove duplicates from unsorted linked list - O(n) time, O(n) space
     *
     * @param LinkedList $list The list to remove duplicates from
     * @return void
     */
    public static function removeDuplicates(LinkedList $list): void
    {
        if ($list->isEmpty()) {
            return;
        }

        $seen = [];
        $reflection = new ReflectionClass($list);
        $headProperty = $reflection->getProperty('head');
        $headProperty->setAccessible(true);
        $head = $headProperty->getValue($list);

        $current = $head;
        $prev = null;

        while ($current !== null) {
            $key = serialize($current->data);

            if (isset($seen[$key])) {
                // Duplicate found, remove it
                $prev->next = $current->next;
            } else {
                $seen[$key] = true;
                $prev = $current;
            }

            $current = $current->next;
        }
    }

    /**
     * Find the intersection point of two linked lists - O(n + m)
     *
     * @param Node|null $head1 First list head
     * @param Node|null $head2 Second list head
     * @return Node|null The intersection node or null if no intersection
     */
    public static function findIntersection(?Node $head1, ?Node $head2): ?Node
    {
        if ($head1 === null || $head2 === null) {
            return null;
        }

        // Get lengths
        $len1 = self::getLength($head1);
        $len2 = self::getLength($head2);

        // Align starting points
        $diff = abs($len1 - $len2);
        $longer = $len1 > $len2 ? $head1 : $head2;
        $shorter = $len1 > $len2 ? $head2 : $head1;

        // Move longer list pointer ahead by diff
        for ($i = 0; $i < $diff; $i++) {
            $longer = $longer->next;
        }

        // Traverse both lists together
        while ($longer !== null && $shorter !== null) {
            if ($longer === $shorter) {
                return $longer;
            }
            $longer = $longer->next;
            $shorter = $shorter->next;
        }

        return null;
    }

    /**
     * Helper: Get length of linked list
     *
     * @param Node|null $head The head node
     * @return int The length
     */
    private static function getLength(?Node $head): int
    {
        $count = 0;
        $current = $head;

        while ($current !== null) {
            $count++;
            $current = $current->next;
        }

        return $count;
    }
}

// Example usage and demonstration
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? '')) {
    echo "=== Linked List Algorithms Demo ===\n\n";

    // Test 1: Reverse
    echo "=== Test 1: Reverse List ===\n";
    $list = new LinkedList();
    foreach ([1, 2, 3, 4, 5] as $val) {
        $list->append($val);
    }
    echo "Original: ";
    $list->display();
    LinkedListAlgorithms::reverseIterative($list);
    echo "Reversed: ";
    $list->display();

    // Test 2: Find Middle
    echo "\n=== Test 2: Find Middle ===\n";
    $list = new LinkedList();
    foreach ([1, 2, 3, 4, 5] as $val) {
        $list->append($val);
    }
    echo "List: ";
    $list->display();
    $middle = LinkedListAlgorithms::findMiddle($list);
    echo "Middle element: {$middle}\n";

    $list->append(6);
    echo "After adding 6: ";
    $list->display();
    $middle = LinkedListAlgorithms::findMiddle($list);
    echo "Middle element: {$middle}\n";

    // Test 3: Detect Cycle
    echo "\n=== Test 3: Detect Cycle ===\n";
    $node1 = new Node(1);
    $node2 = new Node(2);
    $node3 = new Node(3);
    $node4 = new Node(4);
    $node1->next = $node2;
    $node2->next = $node3;
    $node3->next = $node4;
    echo "List without cycle: " . (LinkedListAlgorithms::hasCycle($node1) ? "Has cycle" : "No cycle") . "\n";

    // Create cycle
    $node4->next = $node2;
    echo "List with cycle (4→2): " . (LinkedListAlgorithms::hasCycle($node1) ? "Has cycle" : "No cycle") . "\n";

    // Test 4: Remove Nth from End
    echo "\n=== Test 4: Remove Nth from End ===\n";
    $list = new LinkedList();
    foreach ([1, 2, 3, 4, 5] as $val) {
        $list->append($val);
    }
    echo "Original: ";
    $list->display();
    LinkedListAlgorithms::removeNthFromEnd($list, 2);
    echo "After removing 2nd from end: ";
    $list->display();

    // Test 5: Merge Sorted Lists
    echo "\n=== Test 5: Merge Sorted Lists ===\n";
    $list1 = new LinkedList();
    foreach ([1, 3, 5, 7] as $val) {
        $list1->append($val);
    }
    $list2 = new LinkedList();
    foreach ([2, 4, 6, 8] as $val) {
        $list2->append($val);
    }
    echo "List 1: ";
    $list1->display();
    echo "List 2: ";
    $list2->display();
    $merged = LinkedListAlgorithms::mergeSortedLists($list1, $list2);
    echo "Merged: ";
    $merged->display();

    // Test 6: Palindrome
    echo "\n=== Test 6: Check Palindrome ===\n";
    $list = new LinkedList();
    foreach ([1, 2, 3, 2, 1] as $val) {
        $list->append($val);
    }
    echo "List: ";
    $list->display();
    echo "Is palindrome: " . (LinkedListAlgorithms::isPalindrome($list) ? "Yes" : "No") . "\n";

    $list->append(9);
    echo "After adding 9: ";
    $list->display();
    echo "Is palindrome: " . (LinkedListAlgorithms::isPalindrome($list) ? "Yes" : "No") . "\n";

    // Test 7: Remove Duplicates
    echo "\n=== Test 7: Remove Duplicates ===\n";
    $list = new LinkedList();
    foreach ([1, 2, 3, 2, 4, 1, 5, 3] as $val) {
        $list->append($val);
    }
    echo "Original: ";
    $list->display();
    LinkedListAlgorithms::removeDuplicates($list);
    echo "After removing duplicates: ";
    $list->display();
}
