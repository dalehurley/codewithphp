<?php

declare(strict_types=1);

/**
 * Match Expressions - PHP vs Python
 * 
 * This file demonstrates PHP's match expressions (PHP 8.0+) compared to
 * Python's match statements (Python 3.10+).
 */

// ============================================================================
// 1. Basic Match Expression
// ============================================================================

// Python 3.10+:
// def get_status_message(status: str) -> str:
//     match status:
//         case 'pending':
//             return 'Your request is pending review.'
//         case 'approved':
//             return 'Your request has been approved!'
//         case 'rejected':
//             return 'Your request has been rejected.'
//         case _:
//             return 'Unknown status.'

// PHP 8.0+:
function getStatusMessage(string $status): string
{
    return match ($status) {
        'pending' => 'Your request is pending review.',
        'approved' => 'Your request has been approved!',
        'rejected' => 'Your request has been rejected.',
        default => 'Unknown status.',
    };
}

echo getStatusMessage('approved') . "\n";  // Output: Your request has been approved!
echo getStatusMessage('unknown') . "\n";   // Output: Unknown status.

// ============================================================================
// 2. Match with Multiple Conditions
// ============================================================================

// Python:
// match value:
//     case 1 | 2 | 3:
//         return "Small number"
//     case 4 | 5 | 6:
//         return "Medium number"
//     case _:
//         return "Large number"

// PHP:
$value = 2;
$result = match ($value) {
    1, 2, 3 => "Small number",
    4, 5, 6 => "Medium number",
    default => "Large number",
};
echo "Value {$value}: {$result}\n";  // Output: Value 2: Small number

$value = 5;
$result = match ($value) {
    1, 2, 3 => "Small number",
    4, 5, 6 => "Medium number",
    default => "Large number",
};
echo "Value {$value}: {$result}\n";  // Output: Value 5: Medium number

// ============================================================================
// 3. Match with Conditions (Guards)
// ============================================================================

// Python:
// match value:
//     case x if x > 100:
//         return "Very large"
//     case x if x > 50:
//         return "Large"
//     case _:
//         return "Small"

// PHP:
$value = 75;
$result = match (true) {
    $value > 100 => "Very large",
    $value > 50 => "Large",
    default => "Small",
};
echo "Value {$value}: {$result}\n";  // Output: Value 75: Large

$value = 150;
$result = match (true) {
    $value > 100 => "Very large",
    $value > 50 => "Large",
    default => "Small",
};
echo "Value {$value}: {$result}\n";  // Output: Value 150: Very large

// ============================================================================
// 4. Match Returns Value (Expression)
// ============================================================================

// PHP match is an expression - it returns a value directly
$status = 'pending';
$message = match ($status) {
    'pending' => 'Please wait...',
    'approved' => 'Success!',
    default => 'Unknown',
};
echo "Message: {$message}\n";

// Can be used in assignments, returns, etc.
function getDiscount(int $quantity): float
{
    return match (true) {
        $quantity >= 100 => 0.20,  // 20% discount
        $quantity >= 50 => 0.15,   // 15% discount
        $quantity >= 10 => 0.10,   // 10% discount
        default => 0.05,            // 5% discount
    };
}

echo "Discount for 75 items: " . (getDiscount(75) * 100) . "%\n";  // Output: Discount for 75 items: 15%

// ============================================================================
// 5. Match Type Checking
// ============================================================================

// PHP match does strict comparison (===)
$value = "1";
$result = match ($value) {
    1 => "Integer one",
    "1" => "String one",
    default => "Other",
};
echo "Match result: {$result}\n";  // Output: Match result: String one

// ============================================================================
// 6. Match Exhaustiveness
// ============================================================================

// PHP match requires all cases to be handled or a default case
enum Status: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}

function getStatusMessageEnum(Status $status): string
{
    return match ($status) {
        Status::PENDING => 'Your request is pending review.',
        Status::APPROVED => 'Your request has been approved!',
        Status::REJECTED => 'Your request has been rejected.',
        // No default needed - all enum cases are handled
    };
}

echo getStatusMessageEnum(Status::APPROVED) . "\n";

