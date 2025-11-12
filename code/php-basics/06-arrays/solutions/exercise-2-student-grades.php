<?php

declare(strict_types=1);

/**
 * Exercise 2: Student Grade Management
 * 
 * Create a system to:
 * - Store student names and their grades
 * - Calculate average grade per student
 * - Find highest and lowest grades
 * - Sort students by average grade
 */

$students = [
    'Alice' => [85, 92, 78, 95, 88],
    'Bob' => [72, 85, 79, 82, 90],
    'Charlie' => [95, 98, 92, 96, 94],
    'Diana' => [88, 84, 91, 87, 85],
    'Eve' => [76, 82, 79, 85, 80]
];

/**
 * Calculate average grade for a student
 */
function calculateAverage(array $grades): float
{
    return array_sum($grades) / count($grades);
}

/**
 * Get letter grade from numeric grade
 */
function getLetterGrade(float $average): string
{
    return match (true) {
        $average >= 90 => 'A',
        $average >= 80 => 'B',
        $average >= 70 => 'C',
        $average >= 60 => 'D',
        default => 'F'
    };
}

// Calculate averages for all students
$averages = [];
foreach ($students as $name => $grades) {
    $averages[$name] = calculateAverage($grades);
}

// Display student grades
echo "=== Student Grade Report ===" . PHP_EOL . PHP_EOL;

foreach ($students as $name => $grades) {
    $average = $averages[$name];
    $letter = getLetterGrade($average);

    echo "$name:" . PHP_EOL;
    echo "  Grades: " . implode(", ", $grades) . PHP_EOL;
    echo "  Average: " . number_format($average, 2) . PHP_EOL;
    echo "  Letter Grade: $letter" . PHP_EOL;
    echo "  Highest: " . max($grades) . PHP_EOL;
    echo "  Lowest: " . min($grades) . PHP_EOL;
    echo PHP_EOL;
}

// Class statistics
echo "=== Class Statistics ===" . PHP_EOL;
$classAverage = array_sum($averages) / count($averages);
echo "Class Average: " . number_format($classAverage, 2) . PHP_EOL;
echo "Highest Student Average: " . number_format(max($averages), 2) . PHP_EOL;
echo "Lowest Student Average: " . number_format(min($averages), 2) . PHP_EOL;
echo PHP_EOL;

// Sort students by average (descending)
arsort($averages);

echo "=== Student Rankings ===" . PHP_EOL;
$rank = 1;
foreach ($averages as $name => $average) {
    $letter = getLetterGrade($average);
    echo "$rank. $name - " . number_format($average, 2) . " ($letter)" . PHP_EOL;
    $rank++;
}
echo PHP_EOL;

// Find students above class average
echo "=== Students Above Class Average ===" . PHP_EOL;
foreach ($averages as $name => $average) {
    if ($average > $classAverage) {
        echo "- $name (" . number_format($average, 2) . ")" . PHP_EOL;
    }
}
