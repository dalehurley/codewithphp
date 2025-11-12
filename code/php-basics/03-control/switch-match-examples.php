<?php

declare(strict_types=1);

/**
 * Switch and Match Statements
 * 
 * Switch statements provide an elegant way to check a value against
 * multiple possible matches. Match expressions (PHP 8.0+) are a modern,
 * safer alternative to switch.
 */

echo "=== Switch and Match Examples ===" . PHP_EOL . PHP_EOL;

// Example 1: Basic switch statement
echo "1. Basic Switch Statement:" . PHP_EOL;
$day = 'Wednesday';

switch ($day) {
    case 'Monday':
        echo "Start of the work week" . PHP_EOL;
        break;
    case 'Wednesday':
        echo "Midweek - Hump day!" . PHP_EOL;
        break;
    case 'Friday':
        echo "TGIF - Almost weekend!" . PHP_EOL;
        break;
    case 'Saturday':
    case 'Sunday':
        echo "Weekend - Time to relax!" . PHP_EOL;
        break;
    default:
        echo "Just another day" . PHP_EOL;
        break;
}
echo PHP_EOL;

// Example 2: Switch with fall-through (multiple cases)
echo "2. Switch with Multiple Cases:" . PHP_EOL;
$month = 'February';

switch ($month) {
    case 'December':
    case 'January':
    case 'February':
        echo "$month is in Winter" . PHP_EOL;
        break;
    case 'March':
    case 'April':
    case 'May':
        echo "$month is in Spring" . PHP_EOL;
        break;
    case 'June':
    case 'July':
    case 'August':
        echo "$month is in Summer" . PHP_EOL;
        break;
    case 'September':
    case 'October':
    case 'November':
        echo "$month is in Fall" . PHP_EOL;
        break;
    default:
        echo "Unknown month" . PHP_EOL;
}
echo PHP_EOL;

// Example 3: Switch for HTTP status codes
echo "3. Switch for Status Codes:" . PHP_EOL;
$statusCode = 404;

switch ($statusCode) {
    case 200:
        echo "200: OK - Request successful" . PHP_EOL;
        break;
    case 201:
        echo "201: Created - Resource created" . PHP_EOL;
        break;
    case 400:
        echo "400: Bad Request - Invalid input" . PHP_EOL;
        break;
    case 401:
        echo "401: Unauthorized - Login required" . PHP_EOL;
        break;
    case 404:
        echo "404: Not Found - Resource doesn't exist" . PHP_EOL;
        break;
    case 500:
        echo "500: Server Error - Something went wrong" . PHP_EOL;
        break;
    default:
        echo "$statusCode: Unknown status code" . PHP_EOL;
}
echo PHP_EOL;

// Example 4: Match expression (PHP 8.0+) - RECOMMENDED
echo "4. Match Expression (Modern PHP):" . PHP_EOL;
$day = 'Wednesday';

$message = match ($day) {
    'Monday' => 'Start of the work week',
    'Wednesday' => 'Midweek - Hump day!',
    'Friday' => 'TGIF - Almost weekend!',
    'Saturday', 'Sunday' => 'Weekend - Time to relax!',
    default => 'Just another day'
};

echo $message . PHP_EOL;
echo PHP_EOL;

// Example 5: Match with conditions
echo "5. Match with Complex Conditions:" . PHP_EOL;
$age = 25;

$category = match (true) {
    $age < 13 => 'Child',
    $age >= 13 && $age < 18 => 'Teenager',
    $age >= 18 && $age < 65 => 'Adult',
    $age >= 65 => 'Senior',
    default => 'Unknown'
};

echo "Age $age: Category is $category" . PHP_EOL;
echo PHP_EOL;

// Example 6: Match returning different types
echo "6. Match with Different Return Types:" . PHP_EOL;
$operation = 'multiply';
$a = 10;
$b = 5;

$result = match ($operation) {
    'add' => $a + $b,
    'subtract' => $a - $b,
    'multiply' => $a * $b,
    'divide' => $b !== 0 ? $a / $b : 'Cannot divide by zero',
    default => 'Unknown operation'
};

echo "$operation: $a and $b = $result" . PHP_EOL;
echo PHP_EOL;

// Example 7: Switch vs Match comparison
echo "7. Switch vs Match Comparison:" . PHP_EOL;

$value = '2';

// Switch uses loose comparison (==)
echo "Switch (loose comparison):" . PHP_EOL;
switch ($value) {
    case 2:
        echo "  Matched integer 2" . PHP_EOL;
        break;
    case '2':
        echo "  Matched string '2'" . PHP_EOL;
        break;
}

// Match uses strict comparison (===)
echo "Match (strict comparison):" . PHP_EOL;
$matchResult = match ($value) {
    2 => '  Matched integer 2',
    '2' => '  Matched string \'2\'',
};
echo $matchResult . PHP_EOL;
echo PHP_EOL;

// Example 8: Match with multiple expressions
echo "8. Match for HTTP Status Handling:" . PHP_EOL;
$status = 404;

$response = match ($status) {
    200, 201, 202 => 'Success',
    400, 401, 403 => 'Client Error',
    404 => 'Not Found',
    500, 502, 503 => 'Server Error',
    default => 'Unknown Status'
};

echo "Status $status: $response" . PHP_EOL;
echo PHP_EOL;

// Example 9: Practical example - Grade calculation
echo "9. Practical Example - Grade System:" . PHP_EOL;
$score = 87;

$grade = match (true) {
    $score >= 90 => 'A',
    $score >= 80 => 'B',
    $score >= 70 => 'C',
    $score >= 60 => 'D',
    default => 'F'
};

$feedback = match ($grade) {
    'A' => 'Excellent work!',
    'B' => 'Good job!',
    'C' => 'Satisfactory',
    'D' => 'Needs improvement',
    'F' => 'Failed - please study harder',
};

echo "Score: $score" . PHP_EOL;
echo "Grade: $grade - $feedback" . PHP_EOL;
echo PHP_EOL;

// Example 10: When to use Switch vs Match
echo "10. When to Use Each:" . PHP_EOL;
echo "✓ Use MATCH when:" . PHP_EOL;
echo "  - You need to return a value" . PHP_EOL;
echo "  - You want strict comparison (===)" . PHP_EOL;
echo "  - You want exhaustive checking (no default = error)" . PHP_EOL;
echo "  - You're using PHP 8.0+" . PHP_EOL;
echo PHP_EOL;
echo "✓ Use SWITCH when:" . PHP_EOL;
echo "  - You need to execute multiple statements" . PHP_EOL;
echo "  - You need fall-through behavior" . PHP_EOL;
echo "  - You're on older PHP versions" . PHP_EOL;
echo "  - You prefer loose comparison" . PHP_EOL;
