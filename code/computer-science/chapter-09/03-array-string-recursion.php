<?php

declare(strict_types=1);

echo "=== Array and String Recursion ===\n\n";

// Sum array elements
function sumArray(array $arr): int
{
    if (empty($arr)) return 0;
    return $arr[0] + sumArray(array_slice($arr, 1));
}

// Reverse string
function reverseString(string $str): string
{
    if (strlen($str) <= 1) return $str;
    return $str[strlen($str) - 1] . reverseString(substr($str, 0, -1));
}

// Check palindrome
function isPalindrome(string $str): bool
{
    if (strlen($str) <= 1) return true;
    if ($str[0] !== $str[strlen($str) - 1]) return false;
    return isPalindrome(substr($str, 1, -1));
}

// Find maximum in array
function findMax(array $arr): int
{
    if (count($arr) === 1) return $arr[0];
    $restMax = findMax(array_slice($arr, 1));
    return max($arr[0], $restMax);
}

// Tests
echo "Sum array [1,2,3,4,5]: " . sumArray([1,2,3,4,5]) . "\n";
echo "Reverse 'hello': " . reverseString("hello") . "\n";
echo "Is 'racecar' palindrome? " . (isPalindrome("racecar") ? "Yes" : "No") . "\n";
echo "Max of [3,7,2,9,1]: " . findMax([3,7,2,9,1]) . "\n";

echo "\n✅ Complete!\n";
