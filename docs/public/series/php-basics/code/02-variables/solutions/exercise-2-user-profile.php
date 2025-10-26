<?php

declare(strict_types=1);

/**
 * Exercise 2: User Profile
 * 
 * Create a user profile with various data types:
 * - String for name and email
 * - Integer for age
 * - Float for account balance
 * - Boolean for premium status
 */

// Solution:

$userName = "Sarah Johnson";
$userEmail = "sarah.j@example.com";
$userAge = 32;
$accountBalance = 1250.75;
$isPremiumMember = true;

echo "=== User Profile ===" . PHP_EOL;
echo "Name: $userName" . PHP_EOL;
echo "Email: $userEmail" . PHP_EOL;
echo "Age: $userAge years old" . PHP_EOL;
echo "Account Balance: $" . number_format($accountBalance, 2) . PHP_EOL;
echo "Premium Member: " . ($isPremiumMember ? "Yes" : "No") . PHP_EOL;
echo PHP_EOL;

echo "Data Types:" . PHP_EOL;
echo "  userName type: " . gettype($userName) . PHP_EOL;
echo "  userAge type: " . gettype($userAge) . PHP_EOL;
echo "  accountBalance type: " . gettype($accountBalance) . PHP_EOL;
echo "  isPremiumMember type: " . gettype($isPremiumMember) . PHP_EOL;
