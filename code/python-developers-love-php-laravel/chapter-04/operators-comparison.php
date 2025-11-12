<?php

declare(strict_types=1);

/**
 * Operators: Ternary and Null Coalescing - PHP vs Python
 * 
 * This file demonstrates PHP's ternary operator and null coalescing operator
 * compared to Python's conditional expressions and 'or' operator.
 */

// ============================================================================
// 1. Ternary Operator
// ============================================================================

// Python: result = "positive" if value > 0 else "non-positive"
// PHP:    $result = $value > 0 ? "positive" : "non-positive";

$value = 5;
$result = $value > 0 ? "positive" : "non-positive";
echo "Value {$value} is: {$result}\n";  // Output: Value 5 is: positive

$value = -3;
$result = $value > 0 ? "positive" : "non-positive";
echo "Value {$value} is: {$result}\n";  // Output: Value -3 is: non-positive

// Nested ternary (less readable, but possible)
$status = "active";
$message = $status === "active" ? "User is active" : ($status === "pending" ? "User is pending" : "User is inactive");
echo "Message: {$message}\n";

// ============================================================================
// 2. Null Coalescing Operator (??)
// ============================================================================

// Python: username = request.GET.get('username') or 'guest'
// PHP:    $username = $_GET['username'] ?? 'guest';

// Simulating $_GET array
$_GET = ['username' => 'alice'];
$username = $_GET['username'] ?? 'guest';
echo "Username: {$username}\n";  // Output: Username: alice

// When key doesn't exist
unset($_GET['username']);
$username = $_GET['username'] ?? 'guest';
echo "Username: {$username}\n";  // Output: Username: guest

// Important: ?? only checks for null/undefined, not other falsy values
$value = 0;
$result = $value ?? 'default';
echo "Result: {$result}\n";  // Output: Result: 0 (0 is not null, so it's used)

$value = null;
$result = $value ?? 'default';
echo "Result: {$result}\n";  // Output: Result: default

// ============================================================================
// 3. Null Coalescing Assignment (??=)
// ============================================================================

// PHP 7.4+ null coalescing assignment
$config = [];
$config['timeout'] ??= 30;  // Sets to 30 because it doesn't exist
echo "Timeout: {$config['timeout']}\n";  // Output: Timeout: 30

$config['timeout'] ??= 60;  // Doesn't change because it already exists
echo "Timeout: {$config['timeout']}\n";  // Output: Timeout: 30

// ============================================================================
// 4. Chaining Null Coalescing
// ============================================================================

// Python: value = (data.get('user') or {}).get('name') or 'Unknown'
// PHP:    $value = $data['user']['name'] ?? 'Unknown';

$data = [];
$value = $data['user']['name'] ?? 'Unknown';
echo "Value: {$value}\n";  // Output: Value: Unknown

// Multiple fallbacks
$data = ['default_name' => 'Bob'];
$value = $data['user']['name'] ?? $data['default_name'] ?? 'Unknown';
echo "Value: {$value}\n";  // Output: Value: Bob

// ============================================================================
// 5. Comparison: PHP ?? vs Python 'or'
// ============================================================================

// PHP ?? only checks null/undefined
$value = 0;
$result = $value ?? 'default';
echo "PHP ?? with 0: {$result}\n";  // Output: PHP ?? with 0: 0

// Python 'or' checks falsy (None, 0, "", [], False)
// In PHP, to check falsy, you'd use: $value ?: 'default' (Elvis operator)
$value = 0;
$result = $value ?: 'default';  // Elvis operator (checks falsy)
echo "PHP ?: with 0: {$result}\n";  // Output: PHP ?: with 0: default

$value = '';
$result = $value ?: 'default';
echo "PHP ?: with empty string: {$result}\n";  // Output: PHP ?: with empty string: default

