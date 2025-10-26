<?php

/**
 * Xdebug Debugging Test Script
 * 
 * This script demonstrates debugging with Xdebug. Set a breakpoint on line 20
 * and run this script to test your Xdebug configuration.
 * 
 * Prerequisites:
 *   - Xdebug extension installed and configured
 *   - VS Code with PHP Debug extension
 *   - launch.json configured for "Listen for Xdebug"
 * 
 * Usage:
 *   1. Set a breakpoint on line 20 (the $message = ... line)
 *   2. Start debugging in VS Code (F5)
 *   3. Run: php debug-test.php
 *   4. VS Code should pause at the breakpoint
 */

$name = "PHP Developer";
$message = "Hello, $name!";  // Set breakpoint here

echo $message . PHP_EOL;

// Try stepping through with F10 to see variables update
$numbers = [1, 2, 3, 4, 5];
$sum = array_sum($numbers);

echo "Sum of numbers: $sum" . PHP_EOL;
