<?php

declare(strict_types=1);

/**
 * Exercise 4: Grade Statistics Calculator
 * 
 * Given an array of student grades:
 * - Calculate the average grade
 * - Count how many students passed (>= 60)
 * - Count how many students failed (< 60)
 * - Find the highest and lowest grades
 * - Display each grade with its letter equivalent
 */

// Solution:

$grades = [85, 92, 78, 54, 88, 95, 67, 72, 45, 91];

echo "=== Grade Statistics ===" . PHP_EOL . PHP_EOL;

// Display all grades
echo "Student Grades:" . PHP_EOL;
$sum = 0;
$passed = 0;
$failed = 0;
$highest = $grades[0];
$lowest = $grades[0];

foreach ($grades as $index => $grade) {
    // Calculate sum
    $sum += $grade;

    // Count pass/fail
    if ($grade >= 60) {
        $passed++;
    } else {
        $failed++;
    }

    // Track highest and lowest
    if ($grade > $highest) {
        $highest = $grade;
    }
    if ($grade < $lowest) {
        $lowest = $grade;
    }

    // Determine letter grade
    $letter = match (true) {
        $grade >= 90 => 'A',
        $grade >= 80 => 'B',
        $grade >= 70 => 'C',
        $grade >= 60 => 'D',
        default => 'F'
    };

    $studentNum = $index + 1;
    $status = $grade >= 60 ? '✓ Pass' : '✗ Fail';
    echo "  Student $studentNum: $grade ($letter) - $status" . PHP_EOL;
}

// Calculate average
$average = $sum / count($grades);

// Display statistics
echo PHP_EOL;
echo "=== Statistics ===" . PHP_EOL;
echo "Total Students: " . count($grades) . PHP_EOL;
echo "Average Grade: " . round($average, 2) . PHP_EOL;
echo "Highest Grade: $highest" . PHP_EOL;
echo "Lowest Grade: $lowest" . PHP_EOL;
echo "Passed: $passed (" . round(($passed / count($grades)) * 100, 1) . "%)" . PHP_EOL;
echo "Failed: $failed (" . round(($failed / count($grades)) * 100, 1) . "%)" . PHP_EOL;

// Grade distribution
echo PHP_EOL;
echo "=== Grade Distribution ===" . PHP_EOL;
$gradeCount = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0];

foreach ($grades as $grade) {
    $letter = match (true) {
        $grade >= 90 => 'A',
        $grade >= 80 => 'B',
        $grade >= 70 => 'C',
        $grade >= 60 => 'D',
        default => 'F'
    };
    $gradeCount[$letter]++;
}

foreach ($gradeCount as $letter => $count) {
    $bar = str_repeat('█', $count);
    echo "$letter: $bar ($count)" . PHP_EOL;
}
