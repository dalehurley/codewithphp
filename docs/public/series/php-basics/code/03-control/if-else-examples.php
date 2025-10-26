<?php

declare(strict_types=1);

/**
 * If/Else Control Structures
 * 
 * Demonstrates decision-making with if, else, and elseif statements.
 * These allow your program to execute different code based on conditions.
 */

echo "=== If/Else Examples ===" . PHP_EOL . PHP_EOL;

// Example 1: Simple if statement
echo "1. Simple If Statement:" . PHP_EOL;
$temperature = 25;

if ($temperature > 20) {
    echo "It's warm outside!" . PHP_EOL;
}
echo PHP_EOL;

// Example 2: If-else statement
echo "2. If-Else Statement:" . PHP_EOL;
$age = 16;

if ($age >= 18) {
    echo "You can vote." . PHP_EOL;
} else {
    echo "You cannot vote yet." . PHP_EOL;
}
echo PHP_EOL;

// Example 3: If-elseif-else chain
echo "3. If-Elseif-Else Chain:" . PHP_EOL;
$grade = 85;

if ($grade >= 90) {
    echo "Grade: A (Excellent!)" . PHP_EOL;
} elseif ($grade >= 80) {
    echo "Grade: B (Good work!)" . PHP_EOL;
} elseif ($grade >= 70) {
    echo "Grade: C (Satisfactory)" . PHP_EOL;
} elseif ($grade >= 60) {
    echo "Grade: D (Needs improvement)" . PHP_EOL;
} else {
    echo "Grade: F (Failed)" . PHP_EOL;
}
echo PHP_EOL;

// Example 4: Nested if statements
echo "4. Nested If Statements:" . PHP_EOL;
$isLoggedIn = true;
$isAdmin = false;

if ($isLoggedIn) {
    echo "Welcome! You are logged in." . PHP_EOL;

    if ($isAdmin) {
        echo "You have admin privileges." . PHP_EOL;
    } else {
        echo "You have regular user privileges." . PHP_EOL;
    }
} else {
    echo "Please log in." . PHP_EOL;
}
echo PHP_EOL;

// Example 5: Multiple conditions with logical operators
echo "5. Multiple Conditions:" . PHP_EOL;
$hour = 14;
$dayOfWeek = 'Monday';

// AND operator (&&)
if ($hour >= 9 && $hour < 17) {
    echo "Office hours: We're open!" . PHP_EOL;
}

// OR operator (||)
if ($dayOfWeek === 'Saturday' || $dayOfWeek === 'Sunday') {
    echo "It's the weekend!" . PHP_EOL;
} else {
    echo "It's a weekday." . PHP_EOL;
}

// NOT operator (!)
$isClosed = false;
if (!$isClosed) {
    echo "The store is open." . PHP_EOL;
}
echo PHP_EOL;

// Example 6: Comparison operators
echo "6. Comparison Operators:" . PHP_EOL;
$x = 10;
$y = 20;

echo "x = $x, y = $y" . PHP_EOL;
echo "x == y: " . ($x == $y ? 'true' : 'false') . PHP_EOL;
echo "x != y: " . ($x != $y ? 'true' : 'false') . PHP_EOL;
echo "x < y: " . ($x < $y ? 'true' : 'false') . PHP_EOL;
echo "x > y: " . ($x > $y ? 'true' : 'false') . PHP_EOL;
echo "x <= y: " . ($x <= $y ? 'true' : 'false') . PHP_EOL;
echo "x >= y: " . ($x >= $y ? 'true' : 'false') . PHP_EOL;
echo PHP_EOL;

// Example 7: Strict comparison (=== vs ==)
echo "7. Loose vs Strict Comparison:" . PHP_EOL;
$a = 5;
$b = "5";

echo "a = $a (integer), b = $b (string)" . PHP_EOL;
echo "a == b (loose): " . ($a == $b ? 'true' : 'false') . " (compares values)" . PHP_EOL;
echo "a === b (strict): " . ($a === $b ? 'true' : 'false') . " (compares values AND types)" . PHP_EOL;
echo "Always use === for safer comparisons!" . PHP_EOL;
echo PHP_EOL;

// Example 8: Ternary operator (shorthand if-else)
echo "8. Ternary Operator:" . PHP_EOL;
$score = 75;
$result = ($score >= 60) ? "Pass" : "Fail";
echo "Score: $score - Result: $result" . PHP_EOL;

// Nested ternary (use sparingly - can get hard to read)
$grade = ($score >= 90) ? 'A' : (($score >= 80) ? 'B' : (($score >= 70) ? 'C' : 'F'));
echo "Grade: $grade" . PHP_EOL;
echo PHP_EOL;

// Example 9: Null coalescing operator (??)
echo "9. Null Coalescing Operator:" . PHP_EOL;
$username = null;
$displayName = $username ?? "Guest";
echo "Display name: $displayName" . PHP_EOL;

// Chain multiple null coalescing
$name = null;
$nickname = null;
$default = "Anonymous";
$finalName = $name ?? $nickname ?? $default;
echo "Final name: $finalName" . PHP_EOL;
echo PHP_EOL;

// Example 10: Practical authentication check
echo "10. Practical Example - Authentication:" . PHP_EOL;
$userEmail = "user@example.com";
$userPassword = "secret123";
$inputEmail = "user@example.com";
$inputPassword = "secret123";

if ($inputEmail === $userEmail && $inputPassword === $userPassword) {
    echo "✓ Login successful!" . PHP_EOL;
    echo "Welcome back, $userEmail" . PHP_EOL;
} else {
    echo "✗ Login failed!" . PHP_EOL;
    echo "Invalid email or password." . PHP_EOL;
}
