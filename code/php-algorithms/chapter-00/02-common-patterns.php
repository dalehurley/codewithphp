<?php
/**
 * Common Algorithm Patterns
 *
 * Demonstrates fundamental patterns used throughout algorithm design:
 * - Two Pointers
 * - Sliding Window
 * - Fast & Slow Pointers
 * - Hash Map Lookups
 *
 * @package PHPAlgorithms
 * @chapter 00
 */

declare(strict_types=1);

// ============================================================================
// TWO POINTERS PATTERN
// ============================================================================

/**
 * Check if string is a palindrome using two pointers
 *
 * @param string $str String to check
 * @return bool True if palindrome
 */
function isPalindrome(string $str): bool
{
    $str = strtolower(preg_replace('/[^a-z0-9]/', '', $str));
    $left = 0;
    $right = strlen($str) - 1;

    while ($left < $right) {
        if ($str[$left] !== $str[$right]) {
            return false;
        }
        $left++;
        $right--;
    }

    return true;
}

/**
 * Find pair in sorted array that sums to target
 *
 * @param array<int> $nums Sorted array
 * @param int $target Target sum
 * @return array{int, int}|null Array of two values, or null
 */
function twoSumSorted(array $nums, int $target): ?array
{
    $left = 0;
    $right = count($nums) - 1;

    while ($left < $right) {
        $sum = $nums[$left] + $nums[$right];

        if ($sum === $target) {
            return [$nums[$left], $nums[$right]];
        } elseif ($sum < $target) {
            $left++; // Need larger sum
        } else {
            $right--; // Need smaller sum
        }
    }

    return null;
}

/**
 * Remove duplicates from sorted array in-place
 *
 * @param array<int> $nums Sorted array (passed by reference)
 * @return int New length
 */
function removeDuplicates(array &$nums): int
{
    if (empty($nums)) {
        return 0;
    }

    $writeIndex = 1; // Where to write next unique element

    for ($readIndex = 1; $readIndex < count($nums); $readIndex++) {
        if ($nums[$readIndex] !== $nums[$readIndex - 1]) {
            $nums[$writeIndex] = $nums[$readIndex];
            $writeIndex++;
        }
    }

    // Trim array to new length
    $nums = array_slice($nums, 0, $writeIndex);

    return $writeIndex;
}

/**
 * Reverse array in-place using two pointers
 *
 * @param array<mixed> $arr Array to reverse
 * @return array<mixed> Reversed array
 */
function reverseArray(array $arr): array
{
    $left = 0;
    $right = count($arr) - 1;

    while ($left < $right) {
        [$arr[$left], $arr[$right]] = [$arr[$right], $arr[$left]];
        $left++;
        $right--;
    }

    return $arr;
}

// ============================================================================
// SLIDING WINDOW PATTERN
// ============================================================================

/**
 * Find maximum sum of k consecutive elements
 *
 * @param array<int> $nums Array of integers
 * @param int $k Window size
 * @return int Maximum sum
 */
function maxSumKElements(array $nums, int $k): int
{
    $n = count($nums);

    if ($n < $k) {
        throw new \InvalidArgumentException("Array size must be >= k");
    }

    // Calculate first window
    $windowSum = array_sum(array_slice($nums, 0, $k));
    $maxSum = $windowSum;

    // Slide the window
    for ($i = $k; $i < $n; $i++) {
        $windowSum = $windowSum - $nums[$i - $k] + $nums[$i];
        $maxSum = max($maxSum, $windowSum);
    }

    return $maxSum;
}

/**
 * Find length of longest substring without repeating characters
 *
 * @param string $str Input string
 * @return int Length of longest substring
 */
function lengthOfLongestSubstring(string $str): int
{
    $charMap = []; // char => last seen index
    $maxLength = 0;
    $start = 0; // Window start

    for ($end = 0; $end < strlen($str); $end++) {
        $char = $str[$end];

        // If character seen before and within current window
        if (isset($charMap[$char]) && $charMap[$char] >= $start) {
            // Shrink window from left
            $start = $charMap[$char] + 1;
        }

        $charMap[$char] = $end;
        $maxLength = max($maxLength, $end - $start + 1);
    }

    return $maxLength;
}

/**
 * Find minimum window substring containing all characters
 *
 * @param string $str String to search
 * @param string $pattern Characters to find
 * @return string Minimum window, or empty string if not found
 */
function minWindowSubstring(string $str, string $pattern): string
{
    if (strlen($pattern) > strlen($str)) {
        return '';
    }

    // Count required characters
    $required = [];
    for ($i = 0; $i < strlen($pattern); $i++) {
        $char = $pattern[$i];
        $required[$char] = ($required[$char] ?? 0) + 1;
    }

    $left = 0;
    $right = 0;
    $formed = 0; // How many unique characters match required count
    $requiredCount = count($required);
    $windowCounts = [];
    $minLen = PHP_INT_MAX;
    $minLeft = 0;

    while ($right < strlen($str)) {
        // Expand window
        $char = $str[$right];
        $windowCounts[$char] = ($windowCounts[$char] ?? 0) + 1;

        if (isset($required[$char]) && $windowCounts[$char] === $required[$char]) {
            $formed++;
        }

        // Contract window while valid
        while ($left <= $right && $formed === $requiredCount) {
            // Update result
            if ($right - $left + 1 < $minLen) {
                $minLen = $right - $left + 1;
                $minLeft = $left;
            }

            // Remove leftmost character
            $char = $str[$left];
            $windowCounts[$char]--;

            if (isset($required[$char]) && $windowCounts[$char] < $required[$char]) {
                $formed--;
            }

            $left++;
        }

        $right++;
    }

    return $minLen === PHP_INT_MAX ? '' : substr($str, $minLeft, $minLen);
}

// ============================================================================
// FAST & SLOW POINTERS PATTERN
// ============================================================================

/**
 * Linked list node
 */
class ListNode
{
    public function __construct(
        public int $value,
        public ?ListNode $next = null
    ) {}
}

/**
 * Detect cycle in linked list using Floyd's algorithm
 *
 * @param ListNode|null $head Head of linked list
 * @return bool True if cycle exists
 */
function hasCycle(?ListNode $head): bool
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
            return true;
        }
    }

    return false;
}

/**
 * Find middle of linked list
 *
 * @param ListNode|null $head Head of linked list
 * @return ListNode|null Middle node
 */
function findMiddle(?ListNode $head): ?ListNode
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
 * Find nth node from end of linked list
 *
 * @param ListNode|null $head Head of linked list
 * @param int $n Position from end (1-indexed)
 * @return ListNode|null Nth node from end
 */
function nthFromEnd(?ListNode $head, int $n): ?ListNode
{
    $fast = $head;
    $slow = $head;

    // Move fast n steps ahead
    for ($i = 0; $i < $n; $i++) {
        if ($fast === null) {
            return null; // List shorter than n
        }
        $fast = $fast->next;
    }

    // Move both until fast reaches end
    while ($fast !== null) {
        $slow = $slow->next;
        $fast = $fast->next;
    }

    return $slow;
}

// ============================================================================
// HASH MAP PATTERN
// ============================================================================

/**
 * Find first non-repeating character
 *
 * @param string $str Input string
 * @return string|null First unique character, or null
 */
function firstUniqueChar(string $str): ?string
{
    // Count frequencies
    $freq = [];
    for ($i = 0; $i < strlen($str); $i++) {
        $char = $str[$i];
        $freq[$char] = ($freq[$char] ?? 0) + 1;
    }

    // Find first with frequency 1
    for ($i = 0; $i < strlen($str); $i++) {
        if ($freq[$str[$i]] === 1) {
            return $str[$i];
        }
    }

    return null;
}

/**
 * Group anagrams together
 *
 * @param array<string> $words Array of words
 * @return array<array<string>> Grouped anagrams
 */
function groupAnagrams(array $words): array
{
    $groups = [];

    foreach ($words as $word) {
        // Sort characters to get canonical form
        $chars = str_split($word);
        sort($chars);
        $key = implode('', $chars);

        if (!isset($groups[$key])) {
            $groups[$key] = [];
        }
        $groups[$key][] = $word;
    }

    return array_values($groups);
}

/**
 * Find all pairs with given difference
 *
 * @param array<int> $nums Array of integers
 * @param int $k Target difference
 * @return array<array{int, int}> Pairs with difference k
 */
function findPairsWithDiff(array $nums, int $k): array
{
    $numSet = array_flip($nums);
    $pairs = [];
    $seen = [];

    foreach ($nums as $num) {
        // Check if num + k exists
        if (isset($numSet[$num + $k]) && !isset($seen[$num])) {
            $pairs[] = [$num, $num + $k];
            $seen[$num] = true;
        }
    }

    return $pairs;
}

/**
 * Check if two strings are isomorphic
 *
 * @param string $str1 First string
 * @param string $str2 Second string
 * @return bool True if isomorphic
 */
function isIsomorphic(string $str1, string $str2): bool
{
    if (strlen($str1) !== strlen($str2)) {
        return false;
    }

    $map1to2 = [];
    $map2to1 = [];

    for ($i = 0; $i < strlen($str1); $i++) {
        $c1 = $str1[$i];
        $c2 = $str2[$i];

        // Check if mapping exists and matches
        if (isset($map1to2[$c1])) {
            if ($map1to2[$c1] !== $c2) {
                return false;
            }
        } else {
            $map1to2[$c1] = $c2;
        }

        if (isset($map2to1[$c2])) {
            if ($map2to1[$c2] !== $c1) {
                return false;
            }
        } else {
            $map2to1[$c2] = $c1;
        }
    }

    return true;
}

// ============================================================================
// DEMONSTRATION
// ============================================================================

if (php_sapi_name() === 'cli') {
    echo "=== Common Algorithm Patterns ===\n\n";

    // Two Pointers
    echo "1. TWO POINTERS PATTERN\n";
    echo "   Palindrome Check:\n";
    $tests = ['racecar', 'hello', 'A man, a plan, a canal: Panama'];
    foreach ($tests as $test) {
        $result = isPalindrome($test) ? 'Yes' : 'No';
        echo "   '{$test}' -> {$result}\n";
    }

    echo "\n   Two Sum (Sorted Array):\n";
    $sorted = [1, 2, 3, 4, 5, 6, 7];
    $target = 9;
    $pair = twoSumSorted($sorted, $target);
    if ($pair) {
        echo "   Numbers that sum to {$target}: {$pair[0]} + {$pair[1]}\n";
    }

    // Sliding Window
    echo "\n2. SLIDING WINDOW PATTERN\n";
    echo "   Max Sum of K Elements:\n";
    $nums = [2, 1, 5, 1, 3, 2];
    $k = 3;
    $maxSum = maxSumKElements($nums, $k);
    echo "   Array: [" . implode(', ', $nums) . "], k={$k}\n";
    echo "   Max sum: {$maxSum}\n";

    echo "\n   Longest Substring Without Repeating:\n";
    $strings = ['abcabcbb', 'bbbbb', 'pwwkew'];
    foreach ($strings as $str) {
        $len = lengthOfLongestSubstring($str);
        echo "   '{$str}' -> {$len}\n";
    }

    // Fast & Slow Pointers
    echo "\n3. FAST & SLOW POINTERS\n";
    echo "   Linked List Middle:\n";
    $list = new ListNode(1, new ListNode(2, new ListNode(3, new ListNode(4, new ListNode(5)))));
    $middle = findMiddle($list);
    echo "   List: 1->2->3->4->5\n";
    echo "   Middle value: {$middle->value}\n";

    // Hash Map Pattern
    echo "\n4. HASH MAP PATTERN\n";
    echo "   First Unique Character:\n";
    $strings = ['leetcode', 'loveleetcode', 'aabb'];
    foreach ($strings as $str) {
        $unique = firstUniqueChar($str) ?? 'none';
        echo "   '{$str}' -> '{$unique}'\n";
    }

    echo "\n   Group Anagrams:\n";
    $words = ['eat', 'tea', 'tan', 'ate', 'nat', 'bat'];
    $groups = groupAnagrams($words);
    echo "   Words: [" . implode(', ', $words) . "]\n";
    echo "   Groups: " . count($groups) . "\n";
    foreach ($groups as $i => $group) {
        echo "     Group " . ($i + 1) . ": [" . implode(', ', $group) . "]\n";
    }

    echo "\n   Isomorphic Strings:\n";
    $pairs = [['egg', 'add'], ['foo', 'bar'], ['paper', 'title']];
    foreach ($pairs as [$s1, $s2]) {
        $result = isIsomorphic($s1, $s2) ? 'Yes' : 'No';
        echo "   '{$s1}' and '{$s2}' -> {$result}\n";
    }
}
