<?php

declare(strict_types=1);

// Use the exercise's grade data without maintaining a second set of numbers.
$gradesByStudent = require __DIR__ . '/../data/student-grades.php';

$averages = array_map(
    static fn (array $grades): float => array_sum($grades) / count($grades),
    $gradesByStudent,
);
arsort($averages);

foreach ($averages as $name => $average) {
    $grades = $gradesByStudent[$name];
    printf("%s: %.2f (highest %d, lowest %d)\n", $name, $average, max($grades), min($grades));
}
