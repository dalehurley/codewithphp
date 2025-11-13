<?php
declare(strict_types=1);

/**
 * Match Expressions (PHP 8.0+)
 * Better than switch: returns value, strict comparison, no fall-through
 */

echo "=== Match Expression Basics ===" . PHP_EOL . PHP_EOL;

// Simple match
$status = 200;
$message = match($status) {
    200 => "OK",
    404 => "Not Found",
    500 => "Server Error",
    default => "Unknown Status"
};
echo "Status {$status}: {$message}" . PHP_EOL;
// Output: Status 200: OK

// Multiple conditions
echo PHP_EOL . "=== Multiple Conditions ===" . PHP_EOL . PHP_EOL;

$httpCode = 201;
$result = match($httpCode) {
    200, 201, 202 => "Success",
    400, 401, 403, 404 => "Client Error",
    500, 502, 503 => "Server Error",
    default => "Unknown"
};
echo "HTTP {$httpCode}: {$result}" . PHP_EOL;
// Output: HTTP 201: Success

// Match with expressions
echo PHP_EOL . "=== Match with Expressions ===" . PHP_EOL . PHP_EOL;

$age = 25;
$category = match(true) {
    $age < 13 => "Child",
    $age < 20 => "Teenager",
    $age < 60 => "Adult",
    default => "Senior"
};
echo "Age {$age}: {$category}" . PHP_EOL;
// Output: Age 25: Adult

// Comparison: Switch vs Match
echo PHP_EOL . "=== Switch vs Match ===" . PHP_EOL . PHP_EOL;

// Old switch (statement, loose comparison)
$value = "1";
switch ($value) {
    case 1:  // ⚠️ Matches because '1' == 1
        $switchResult = "One";
        break;
    default:
        $switchResult = "Other";
}
echo "Switch result: {$switchResult}" . PHP_EOL;
// Output: Switch result: One (loose comparison!)

// New match (expression, strict comparison)
$matchResult = match($value) {
    1 => "One",  // ❌ Does NOT match because '1' !== 1
    default => "Other"
};
echo "Match result: {$matchResult}" . PHP_EOL;
// Output: Match result: Other (strict comparison!)

// Practical example: Route handling
echo PHP_EOL . "=== Practical: Route Handling ===" . PHP_EOL . PHP_EOL;

function handleRoute(string $method, string $path): string {
    return match([$method, $path]) {
        ['GET', '/'] => "Home page",
        ['GET', '/about'] => "About page",
        ['POST', '/api/users'] => "Create user",
        ['GET', '/api/users'] => "List users",
        default => "404 Not Found"
    };
}

echo handleRoute('GET', '/') . PHP_EOL;
echo handleRoute('POST', '/api/users') . PHP_EOL;
echo handleRoute('GET', '/unknown') . PHP_EOL;

// Practical example: Price calculation
echo PHP_EOL . "=== Practical: Price Calculation ===" . PHP_EOL . PHP_EOL;

enum MembershipTier: string {
    case Bronze = 'bronze';
    case Silver = 'silver';
    case Gold = 'gold';
}

function calculateDiscount(MembershipTier $tier, float $price): float {
    $discountPercent = match($tier) {
        MembershipTier::Bronze => 5,
        MembershipTier::Silver => 10,
        MembershipTier::Gold => 20,
    };

    return $price * (1 - $discountPercent / 100);
}

$originalPrice = 100.0;
echo "Bronze: $" . calculateDiscount(MembershipTier::Bronze, $originalPrice) . PHP_EOL;
echo "Silver: $" . calculateDiscount(MembershipTier::Silver, $originalPrice) . PHP_EOL;
echo "Gold: $" . calculateDiscount(MembershipTier::Gold, $originalPrice) . PHP_EOL;

// Complex match with calculations
echo PHP_EOL . "=== Complex Match ===" . PHP_EOL . PHP_EOL;

function calculateShipping(string $country, float $weight): float {
    return match($country) {
        'US' => match(true) {
            $weight < 1 => 5.0,
            $weight < 5 => 10.0,
            default => 15.0 + ($weight - 5) * 2
        },
        'CA' => match(true) {
            $weight < 1 => 7.0,
            $weight < 5 => 12.0,
            default => 17.0 + ($weight - 5) * 2.5
        },
        default => 20.0 + $weight * 3
    };
}

echo "US (0.5kg): $" . calculateShipping('US', 0.5) . PHP_EOL;
echo "CA (3kg): $" . calculateShipping('CA', 3) . PHP_EOL;
echo "UK (2kg): $" . calculateShipping('UK', 2) . PHP_EOL;
