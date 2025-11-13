<?php

declare(strict_types=1);

/**
 * Digit Dynamic Programming Examples
 *
 * Digit DP is used to count numbers with specific digit properties
 * within a range [1, N]. Common applications include counting numbers
 * with certain digit sums, without consecutive digits, etc.
 *
 * Time Complexity: O(d × s × 2^k) where d = digits, s = sum, k = flags
 * Space Complexity: O(d × s × 2^k) for memoization
 */
class DigitDP
{
    private array $memo = [];

    /**
     * Count numbers from 1 to n with digit sum equal to target
     *
     * @param int $n Upper bound (inclusive)
     * @param int $targetSum Target digit sum
     * @return int Count of valid numbers
     */
    public function countWithDigitSum(int $n, int $targetSum): int
    {
        $this->memo = [];
        $digits = str_split((string)$n);

        return $this->solveDigitSum($digits, 0, 0, $targetSum, true, false);
    }

    private function solveDigitSum(
        array $digits,
        int $pos,
        int $currentSum,
        int $targetSum,
        bool $tight,
        bool $started
    ): int {
        // Base case: processed all digits
        if ($pos === count($digits)) {
            return ($started && $currentSum === $targetSum) ? 1 : 0;
        }

        // Memoization key
        $key = "$pos:$currentSum:" . ($tight ? '1' : '0') . ':' . ($started ? '1' : '0');
        if (isset($this->memo[$key])) {
            return $this->memo[$key];
        }

        $limit = $tight ? (int)$digits[$pos] : 9;
        $result = 0;

        for ($digit = 0; $digit <= $limit; $digit++) {
            if (!$started && $digit === 0) {
                // Leading zeros - don't start counting yet
                $result += $this->solveDigitSum(
                    $digits,
                    $pos + 1,
                    0,
                    $targetSum,
                    false,
                    false
                );
            } else {
                // Number has started
                $result += $this->solveDigitSum(
                    $digits,
                    $pos + 1,
                    $currentSum + $digit,
                    $targetSum,
                    $tight && ($digit === $limit),
                    true
                );
            }
        }

        $this->memo[$key] = $result;
        return $result;
    }

    /**
     * Count numbers without consecutive repeating digits
     *
     * @param int $n Upper bound
     * @return int Count of valid numbers
     */
    public function countWithoutConsecutiveDigits(int $n): int
    {
        $this->memo = [];
        $digits = str_split((string)$n);

        return $this->solveConsecutive($digits, 0, -1, true, false);
    }

    private function solveConsecutive(
        array $digits,
        int $pos,
        int $lastDigit,
        bool $tight,
        bool $started
    ): int {
        if ($pos === count($digits)) {
            return $started ? 1 : 0;
        }

        $key = "$pos:$lastDigit:" . ($tight ? '1' : '0') . ':' . ($started ? '1' : '0');
        if (isset($this->memo[$key])) {
            return $this->memo[$key];
        }

        $limit = $tight ? (int)$digits[$pos] : 9;
        $result = 0;

        for ($digit = 0; $digit <= $limit; $digit++) {
            if (!$started && $digit === 0) {
                $result += $this->solveConsecutive(
                    $digits,
                    $pos + 1,
                    -1,
                    false,
                    false
                );
            } elseif ($digit !== $lastDigit) {
                $result += $this->solveConsecutive(
                    $digits,
                    $pos + 1,
                    $digit,
                    $tight && ($digit === $limit),
                    true
                );
            }
        }

        $this->memo[$key] = $result;
        return $result;
    }

    /**
     * Count numbers with at most K different digits
     *
     * @param int $n Upper bound
     * @param int $k Maximum different digits
     * @return int Count of valid numbers
     */
    public function countWithAtMostKDifferentDigits(int $n, int $k): int
    {
        $this->memo = [];
        $digits = str_split((string)$n);

        return $this->solveKDigits($digits, 0, 0, $k, true, false);
    }

    private function solveKDigits(
        array $digits,
        int $pos,
        int $usedMask,  // Bitmask of used digits
        int $k,
        bool $tight,
        bool $started
    ): int {
        if ($pos === count($digits)) {
            return $started ? 1 : 0;
        }

        $key = "$pos:$usedMask:" . ($tight ? '1' : '0') . ':' . ($started ? '1' : '0');
        if (isset($this->memo[$key])) {
            return $this->memo[$key];
        }

        $limit = $tight ? (int)$digits[$pos] : 9;
        $result = 0;

        for ($digit = 0; $digit <= $limit; $digit++) {
            if (!$started && $digit === 0) {
                $result += $this->solveKDigits(
                    $digits,
                    $pos + 1,
                    0,
                    $k,
                    false,
                    false
                );
            } else {
                $newMask = $usedMask | (1 << $digit);
                $differentCount = $this->popcount($newMask);

                if ($differentCount <= $k) {
                    $result += $this->solveKDigits(
                        $digits,
                        $pos + 1,
                        $newMask,
                        $k,
                        $tight && ($digit === $limit),
                        true
                    );
                }
            }
        }

        $this->memo[$key] = $result;
        return $result;
    }

    /**
     * Count numbers in range [L, R] with given properties
     *
     * @param int $left Left bound
     * @param int $right Right bound
     * @param int $targetSum Target digit sum
     * @return int Count in range
     */
    public function countInRange(int $left, int $right, int $targetSum): int
    {
        $countRight = $this->countWithDigitSum($right, $targetSum);
        $countLeft = $left > 0 ? $this->countWithDigitSum($left - 1, $targetSum) : 0;

        return $countRight - $countLeft;
    }

    private function popcount(int $mask): int
    {
        $count = 0;
        while ($mask > 0) {
            $count += $mask & 1;
            $mask >>= 1;
        }
        return $count;
    }
}

// =============================================================================
// EXAMPLES AND USAGE
// =============================================================================

echo "Digit Dynamic Programming Examples\n";
echo str_repeat("=", 70) . "\n\n";

$digitDP = new DigitDP();

// Example 1: Numbers with specific digit sum
echo "Example 1: Count Numbers with Digit Sum\n";
echo "-" . str_repeat("-", 69) . "\n";

$testCases = [
    [100, 10],
    [1000, 15],
    [50, 5],
    [999, 27]  // Max digit sum for 3-digit numbers
];

foreach ($testCases as [$n, $targetSum]) {
    $count = $digitDP->countWithDigitSum($n, $targetSum);
    echo "Numbers 1-$n with digit sum = $targetSum: $count\n";

    // Show some examples
    $examples = [];
    for ($i = 1; $i <= $n && count($examples) < 5; $i++) {
        if (array_sum(str_split((string)$i)) === $targetSum) {
            $examples[] = $i;
        }
    }
    echo "  Examples: " . implode(', ', $examples) . "...\n";
}
echo "\n";

// Example 2: Numbers without consecutive repeating digits
echo "Example 2: Numbers Without Consecutive Repeating Digits\n";
echo "-" . str_repeat("-", 69) . "\n";

$ranges = [100, 500, 1000, 10000];

foreach ($ranges as $n) {
    $count = $digitDP->countWithoutConsecutiveDigits($n);
    $percentage = ($count / $n) * 100;

    echo "1-$n: $count numbers (" . number_format($percentage, 1) . "%)\n";
}
echo "\n";

// Example 3: Numbers with at most K different digits
echo "Example 3: Numbers with At Most K Different Digits\n";
echo "-" . str_repeat("-", 69) . "\n";

$n = 1000;
for ($k = 1; $k <= 5; $k++) {
    $count = $digitDP->countWithAtMostKDifferentDigits($n, $k);
    echo "1-$n with at most $k different digits: $count\n";

    if ($k === 1) {
        echo "  (e.g., 1, 2, 3, ..., 9, 11, 22, 33, ..., 111, 222, ...)\n";
    } elseif ($k === 2) {
        echo "  (e.g., 10, 12, 21, 23, 101, 112, 121, ...)\n";
    }
}
echo "\n";

// Example 4: Range queries
echo "Example 4: Count in Specific Range\n";
echo "-" . str_repeat("-", 69) . "\n";

$ranges = [
    [1, 100, 10],
    [50, 150, 8],
    [100, 500, 15],
    [1000, 2000, 10]
];

foreach ($ranges as [$left, $right, $targetSum]) {
    $count = $digitDP->countInRange($left, $right, $targetSum);
    echo "Range [$left, $right] with digit sum = $targetSum: $count numbers\n";
}
echo "\n";

// Example 5: Real-world application - Lottery numbers
echo "Example 5: Lottery Numbers (Sum = Lucky Number)\n";
echo "-" . str_repeat("-", 69) . "\n";

$lotteryMax = 999999;  // 6-digit lottery
$luckySum = 42;

$luckyCount = $digitDP->countWithDigitSum($lotteryMax, $luckySum);
$totalCount = $lotteryMax;
$probability = ($luckyCount / $totalCount) * 100;

echo "6-digit lottery numbers (000000-999999)\n";
echo "Numbers with digit sum = $luckySum: " . number_format($luckyCount) . "\n";
echo "Probability: " . number_format($probability, 4) . "%\n";
echo "Odds: 1 in " . number_format($totalCount / $luckyCount, 0) . "\n\n";

// Performance benchmark
echo str_repeat("=", 70) . "\n";
echo "Performance Benchmark\n";
echo str_repeat("=", 70) . "\n";

$benchmarks = [
    ['N' => 10000, 'Sum' => 20],
    ['N' => 100000, 'Sum' => 25],
    ['N' => 1000000, 'Sum' => 30],
    ['N' => 10000000, 'Sum' => 35]
];

foreach ($benchmarks as $test) {
    $start = microtime(true);
    $count = $digitDP->countWithDigitSum($test['N'], $test['Sum']);
    $time = (microtime(true) - $start) * 1000;

    echo sprintf(
        "N = %s, Sum = %2d: %s numbers, Time = %6.2f ms\n",
        str_pad(number_format($test['N']), 10, ' ', STR_PAD_LEFT),
        $test['Sum'],
        str_pad(number_format($count), 8, ' ', STR_PAD_LEFT),
        $time
    );
}

echo "\n✓ All examples completed successfully!\n";
echo "\nDigit DP is powerful for counting problems with digit constraints.\n";
echo "Key insight: Process digits left-to-right with memoization on state.\n";
