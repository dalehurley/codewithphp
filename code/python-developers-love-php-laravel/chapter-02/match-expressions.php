<?php

declare(strict_types=1);

// PHP 8.0+ match expressions
// Similar to Python 3.10+'s match statement

function getStatusMessage(string $status): string
{
    return match ($status) {
        'pending' => 'Your request is pending review.',
        'approved' => 'Your request has been approved!',
        'rejected' => 'Your request has been rejected.',
        default => 'Unknown status.',
    };
}

// Example usage
echo getStatusMessage('pending') . "\n";  // Output: Your request is pending review.
echo getStatusMessage('approved') . "\n";  // Output: Your request has been approved!
echo getStatusMessage('rejected') . "\n";  // Output: Your request has been rejected.
echo getStatusMessage('unknown') . "\n";  // Output: Unknown status.

// Match expressions return values (unlike switch)
function getStatusCode(string $method): int
{
    return match ($method) {
        'GET' => 200,
        'POST' => 201,
        'PUT' => 200,
        'DELETE' => 204,
        default => 405,
    };
}

echo "GET status: " . getStatusCode('GET') . "\n";  // Output: GET status: 200
echo "POST status: " . getStatusCode('POST') . "\n";  // Output: POST status: 201

// Match with multiple conditions
function getCategory(int $age): string
{
    return match (true) {
        $age < 13 => 'child',
        $age < 18 => 'teen',
        $age < 65 => 'adult',
        default => 'senior',
    };
}

echo "Age 10: " . getCategory(10) . "\n";  // Output: Age 10: child
echo "Age 25: " . getCategory(25) . "\n";  // Output: Age 25: adult
echo "Age 70: " . getCategory(70) . "\n";  // Output: Age 70: senior

