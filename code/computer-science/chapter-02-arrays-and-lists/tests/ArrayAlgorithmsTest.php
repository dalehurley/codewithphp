<?php

declare(strict_types=1);

namespace ComputerScience\Chapter02\Tests;

use ComputerScience\Chapter02\ArrayAlgorithms;
use PHPUnit\Framework\TestCase;

class ArrayAlgorithmsTest extends TestCase
{
    public function testLinearSearch(): void
    {
        $arr = [10, 20, 30, 40, 50];

        $this->assertEquals(2, ArrayAlgorithms::linearSearch($arr, 30));
        $this->assertNull(ArrayAlgorithms::linearSearch($arr, 999));
    }

    public function testBinarySearch(): void
    {
        $arr = [10, 20, 30, 40, 50];

        $this->assertEquals(2, ArrayAlgorithms::binarySearch($arr, 30));
        $this->assertEquals(0, ArrayAlgorithms::binarySearch($arr, 10));
        $this->assertEquals(4, ArrayAlgorithms::binarySearch($arr, 50));
        $this->assertNull(ArrayAlgorithms::binarySearch($arr, 999));
    }

    public function testReverse(): void
    {
        $arr = [1, 2, 3, 4, 5];
        $result = ArrayAlgorithms::reverse($arr);

        $this->assertEquals([5, 4, 3, 2, 1], $result);
    }

    public function testRotateRight(): void
    {
        $arr = [1, 2, 3, 4, 5];
        $result = ArrayAlgorithms::rotateRight($arr, 2);

        $this->assertEquals([4, 5, 1, 2, 3], $result);
    }

    public function testRotateRightWithLargeK(): void
    {
        $arr = [1, 2, 3];
        $result = ArrayAlgorithms::rotateRight($arr, 5); // k > n

        $this->assertEquals([2, 3, 1], $result); // Same as k=2
    }

    public function testFindMissingNumber(): void
    {
        $arr = [1, 2, 4, 5, 6]; // Missing 3
        $missing = ArrayAlgorithms::findMissingNumber($arr);

        $this->assertEquals(3, $missing);
    }

    public function testRemoveDuplicates(): void
    {
        $arr = [1, 1, 2, 2, 2, 3, 4, 4, 5];
        $length = ArrayAlgorithms::removeDuplicates($arr);

        $this->assertEquals(5, $length);
        $this->assertEquals([1, 2, 3, 4, 5], $arr);
    }

    public function testTwoSum(): void
    {
        $arr = [2, 7, 11, 15];
        $result = ArrayAlgorithms::twoSum($arr, 9);

        $this->assertEquals([0, 1], $result);
    }

    public function testTwoSumNotFound(): void
    {
        $arr = [2, 7, 11, 15];
        $result = ArrayAlgorithms::twoSum($arr, 100);

        $this->assertNull($result);
    }

    public function testMaxSubarraySum(): void
    {
        $arr = [-2, 1, -3, 4, -1, 2, 1, -5, 4];
        $maxSum = ArrayAlgorithms::maxSubarraySum($arr);

        $this->assertEquals(6, $maxSum); // [4, -1, 2, 1]
    }

    public function testMergeSorted(): void
    {
        $arr1 = [1, 3, 5, 7];
        $arr2 = [2, 4, 6, 8];
        $result = ArrayAlgorithms::mergeSorted($arr1, $arr2);

        $this->assertEquals([1, 2, 3, 4, 5, 6, 7, 8], $result);
    }

    public function testFindPairs(): void
    {
        $arr = [1, 2, 3, 4, 5];
        $pairs = ArrayAlgorithms::findPairs($arr, 6);

        $this->assertCount(2, $pairs);
        $this->assertContains([1, 5], $pairs);
        $this->assertContains([2, 4], $pairs);
    }

    public function testMoveZerosToEnd(): void
    {
        $arr = [0, 1, 0, 3, 12];
        $result = ArrayAlgorithms::moveZerosToEnd($arr);

        $this->assertEquals([1, 3, 12, 0, 0], $result);
    }

    public function testIntersection(): void
    {
        $arr1 = [1, 2, 3, 4, 5];
        $arr2 = [3, 4, 5, 6, 7];
        $result = ArrayAlgorithms::intersection($arr1, $arr2);

        $this->assertEquals([3, 4, 5], $result);
    }

    public function testIsSorted(): void
    {
        $this->assertTrue(ArrayAlgorithms::isSorted([1, 2, 3, 4, 5]));
        $this->assertFalse(ArrayAlgorithms::isSorted([1, 3, 2, 4, 5]));
        $this->assertTrue(ArrayAlgorithms::isSorted([5, 4, 3, 2, 1], false));
    }

    public function testFindPeak(): void
    {
        $arr = [1, 3, 20, 4, 1, 0];
        $peakIndex = ArrayAlgorithms::findPeak($arr);

        $this->assertEquals(2, $peakIndex); // Element 20 at index 2
    }

    public function testFindPeakAtEnd(): void
    {
        $arr = [1, 2, 3, 4, 5];
        $peakIndex = ArrayAlgorithms::findPeak($arr);

        $this->assertEquals(4, $peakIndex); // Last element is peak
    }
}
