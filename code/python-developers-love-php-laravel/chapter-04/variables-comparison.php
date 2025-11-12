<?php

declare(strict_types=1);

/**
 * Variables and Variable Prefixes - PHP vs Python
 * 
 * This file demonstrates PHP's variable syntax differences from Python.
 * Key differences:
 * - PHP requires $ prefix for all variables
 * - Constants don't use $ prefix
 * - Variable assignment syntax is otherwise similar
 */

// ============================================================================
// 1. Variable Prefix Requirement
// ============================================================================

// Python: name = "Alice"
// PHP:   $name = "Alice";
$name = "Alice";
$age = 30;
$is_active = true;

echo "Name: $name\n";
echo "Age: $age\n";
echo "Active: " . ($is_active ? 'true' : 'false') . "\n";

// ============================================================================
// 2. Variable Assignment and Reassignment
// ============================================================================

// Python: count = 10
// PHP:    $count = 10;
$count = 10;

// Reassignment
$count = $count + 5;  // Same as Python: count = count + 5
echo "Count after addition: $count\n";

// Shorthand operators (same as Python)
$count += 5;
echo "Count after += 5: $count\n";

$count *= 2;
echo "Count after *= 2: $count\n";

// ============================================================================
// 3. Constants
// ============================================================================

// Python: MAX_USERS = 100  (convention: uppercase)
// PHP:    define('MAX_USERS', 100);  or  const MAX_USERS = 100;

// Function-based constant (global scope)
define('MAX_USERS', 100);

// Class/file-level constant
const API_VERSION = "v1";

// Constants don't use $ prefix
echo "Max users: " . MAX_USERS . "\n";
echo "API version: " . API_VERSION . "\n";

// ============================================================================
// 4. Variable Scope
// ============================================================================

// PHP variables are function-scoped (similar to Python)
function testScope(): void
{
    $local_var = "I'm local";
    echo "Inside function: $local_var\n";
    
    // Global variables (use global keyword or $GLOBALS)
    global $name;
    echo "Global name: $name\n";
}

testScope();

// ============================================================================
// 5. Variable Variables (PHP-specific feature)
// ============================================================================

// PHP allows variable variables (Python doesn't have this)
$var_name = "message";
$$var_name = "Hello from variable variable!";
echo $message . "\n";  // Output: Hello from variable variable!

// ============================================================================
// 6. Null Coalescing Operator (PHP 7.0+)
// ============================================================================

// Python: value = data.get('key', 'default')
// PHP:    $value = $data['key'] ?? 'default';

$data = [];
$value = $data['key'] ?? 'default';
echo "Value: $value\n";  // Output: default

// Null coalescing assignment (PHP 7.4+)
$user = null;
$user ??= "Guest";
echo "User: $user\n";  // Output: Guest

