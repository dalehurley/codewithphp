<?php

declare(strict_types=1);

namespace ComputerScience\Chapter04;

/**
 * Common Linked List Algorithms
 *
 * Collection of frequently-used linked list manipulation algorithms
 * that appear in technical interviews and real-world applications.
 */
class LinkedListAlgorithms
{
    /**
     * Detect cycle using Floyd's Algorithm - O(n) time, O(1) space
     *
     * Uses slow/fast pointers (tortoise and hare).
     *
     * @param Node|null $head Head of the list
     * @return bool True if cycle exists
     */
    public static function hasCycle(?Node $head): bool
    {
        if ($head === null) {
            return false;
        }

        $slow = $head;
        $fast = $head;

        while ($fast !== null && $fast->next !== null) {
            $slow = $slow->next;
            $fast = $fast->next->next;

            if ($slow === $fast) {
                return true; // Cycle detected!
            }
        }

        return false;
    }

    /**
     * Find middle element - O(n) time, O(1) space
     *
     * When list has even number of nodes, returns second middle.
     *
     * @param Node|null $head Head of the list
     * @return Node|null The middle node
     */
    public static function findMiddle(?Node $head): ?Node
    {
        if ($head === null) {
            return null;
        }

        $slow = $head;
        $fast = $head;

        while ($fast !== null && $fast->next !== null) {
            $slow = $slow->next;
            $fast = $fast->next->next;
        }

        return $slow;
    }

    /**
     * Reverse linked list - O(n) time, O(1) space
     *
     * @param Node|null $head Head of the list
     * @return Node|null New head after reversal
     */
    public static function reverse(?Node $head): ?Node
    {
        $prev = null;
        $current = $head;

        while ($current !== null) {
            $next = $current->next;
            $current->next = $prev;
            $prev = $current;
            $current = $next;
        }

        return $prev;
    }

    /**
     * Merge two sorted lists - O(m + n) time, O(1) space
     *
     * @param Node|null $l1 First sorted list
     * @param Node|null $l2 Second sorted list
     * @return Node|null Merged sorted list
     */
    public static function mergeSorted(?Node $l1, ?Node $l2): ?Node
    {
        $dummy = new Node(0);
        $current = $dummy;

        while ($l1 !== null && $l2 !== null) {
            if ($l1->data <= $l2->data) {
                $current->next = $l1;
                $l1 = $l1->next;
            } else {
                $current->next = $l2;
                $l2 = $l2->next;
            }
            $current = $current->next;
        }

        // Attach remaining nodes
        $current->next = $l1 ?? $l2;

        return $dummy->next;
    }

    /**
     * Remove Nth node from end - O(n) time, O(1) space
     *
     * Uses two pointers with n gap between them.
     *
     * @param Node|null $head Head of the list
     * @param int $n Position from end (1-based)
     * @return Node|null New head after removal
     */
    public static function removeNthFromEnd(?Node $head, int $n): ?Node
    {
        $dummy = new Node(0);
        $dummy->next = $head;

        $first = $dummy;
        $second = $dummy;

        // Move first n+1 steps ahead
        for ($i = 0; $i <= $n; $i++) {
            $first = $first->next;
        }

        // Move both until first reaches end
        while ($first !== null) {
            $first = $first->next;
            $second = $second->next;
        }

        // Remove the node
        $second->next = $second->next->next;

        return $dummy->next;
    }

    /**
     * Check if list is palindrome - O(n) time, O(1) space
     *
     * @param Node|null $head Head of the list
     * @return bool True if palindrome
     */
    public static function isPalindrome(?Node $head): bool
    {
        if ($head === null || $head->next === null) {
            return true;
        }

        // Find middle
        $slow = $head;
        $fast = $head;

        while ($fast !== null && $fast->next !== null) {
            $slow = $slow->next;
            $fast = $fast->next->next;
        }

        // Reverse second half
        $secondHalf = self::reverse($slow);

        // Compare both halves
        $firstHalf = $head;
        while ($secondHalf !== null) {
            if ($firstHalf->data !== $secondHalf->data) {
                return false;
            }
            $firstHalf = $firstHalf->next;
            $secondHalf = $secondHalf->next;
        }

        return true;
    }

    /**
     * Find intersection point of two lists - O(m + n) time, O(1) space
     *
     * @param Node|null $headA Head of first list
     * @param Node|null $headB Head of second list
     * @return Node|null Intersection node or null
     */
    public static function getIntersection(?Node $headA, ?Node $headB): ?Node
    {
        if ($headA === null || $headB === null) {
            return null;
        }

        $pointerA = $headA;
        $pointerB = $headB;

        // Clever trick: when reaching end, jump to other list's head
        // They will meet at intersection or both reach null
        while ($pointerA !== $pointerB) {
            $pointerA = $pointerA === null ? $headB : $pointerA->next;
            $pointerB = $pointerB === null ? $headA : $pointerB->next;
        }

        return $pointerA;
    }

    /**
     * Remove duplicates from sorted list - O(n) time, O(1) space
     *
     * @param Node|null $head Head of sorted list
     * @return Node|null Head after removing duplicates
     */
    public static function removeDuplicates(?Node $head): ?Node
    {
        if ($head === null) {
            return null;
        }

        $current = $head;

        while ($current !== null && $current->next !== null) {
            if ($current->data === $current->next->data) {
                $current->next = $current->next->next;
            } else {
                $current = $current->next;
            }
        }

        return $head;
    }

    /**
     * Rotate list to the right by k positions - O(n) time, O(1) space
     *
     * @param Node|null $head Head of the list
     * @param int $k Number of positions to rotate
     * @return Node|null New head after rotation
     */
    public static function rotateRight(?Node $head, int $k): ?Node
    {
        if ($head === null || $head->next === null || $k === 0) {
            return $head;
        }

        // Find length and tail
        $length = 1;
        $tail = $head;
        while ($tail->next !== null) {
            $tail = $tail->next;
            $length++;
        }

        // Normalize k
        $k = $k % $length;
        if ($k === 0) {
            return $head;
        }

        // Find new tail (at length - k - 1)
        $newTail = $head;
        for ($i = 0; $i < $length - $k - 1; $i++) {
            $newTail = $newTail->next;
        }

        // Rearrange pointers
        $newHead = $newTail->next;
        $newTail->next = null;
        $tail->next = $head;

        return $newHead;
    }

    /**
     * Partition list around value x - O(n) time, O(1) space
     *
     * All nodes < x come before all nodes >= x.
     *
     * @param Node|null $head Head of the list
     * @param mixed $x Partition value
     * @return Node|null Partitioned list
     */
    public static function partition(?Node $head, mixed $x): ?Node
    {
        $beforeHead = new Node(0);
        $before = $beforeHead;

        $afterHead = new Node(0);
        $after = $afterHead;

        while ($head !== null) {
            if ($head->data < $x) {
                $before->next = $head;
                $before = $before->next;
            } else {
                $after->next = $head;
                $after = $after->next;
            }
            $head = $head->next;
        }

        $after->next = null;
        $before->next = $afterHead->next;

        return $beforeHead->next;
    }

    /**
     * Add two numbers represented as linked lists - O(max(m,n)) time, O(max(m,n)) space
     *
     * Each node contains a single digit. Digits stored in reverse order.
     * Example: 342 + 465 = 807 is represented as 2->4->3 + 5->6->4 = 7->0->8
     *
     * @param Node|null $l1 First number
     * @param Node|null $l2 Second number
     * @return Node|null Sum as linked list
     */
    public static function addTwoNumbers(?Node $l1, ?Node $l2): ?Node
    {
        $dummy = new Node(0);
        $current = $dummy;
        $carry = 0;

        while ($l1 !== null || $l2 !== null || $carry !== 0) {
            $sum = $carry;

            if ($l1 !== null) {
                $sum += $l1->data;
                $l1 = $l1->next;
            }

            if ($l2 !== null) {
                $sum += $l2->data;
                $l2 = $l2->next;
            }

            $carry = (int)($sum / 10);
            $current->next = new Node($sum % 10);
            $current = $current->next;
        }

        return $dummy->next;
    }

    /**
     * Copy list with random pointer - O(n) time, O(n) space
     *
     * Creates a deep copy of a linked list where each node has
     * a random pointer to any node in the list.
     *
     * @param Node|null $head Head of the list
     * @return Node|null Head of copied list
     */
    public static function copyRandomList(?Node $head): ?Node
    {
        if ($head === null) {
            return null;
        }

        $map = [];
        $current = $head;

        // First pass: create all nodes
        while ($current !== null) {
            $map[spl_object_id($current)] = new Node($current->data);
            $current = $current->next;
        }

        // Second pass: connect pointers
        $current = $head;
        while ($current !== null) {
            $newNode = $map[spl_object_id($current)];

            if ($current->next !== null) {
                $newNode->next = $map[spl_object_id($current->next)];
            }

            $current = $current->next;
        }

        return $map[spl_object_id($head)];
    }
}
