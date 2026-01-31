<?php

declare(strict_types=1);

/**
 * Validation Patterns Example
 * 
 * Demonstrates input validation patterns:
 * - Schema validation (automatic)
 * - Business logic validation
 * - Early return pattern
 * - Error accumulation
 * - Custom validation functions
 */

// Load from the claude-php-agent project in the workspace
require_once __DIR__ . '/../../../../claude-php-agent/vendor/autoload.php';

use ClaudeAgents\Tools\Tool;
use ClaudeAgents\Tools\ToolResult;

echo "=== Validation Patterns Example ===\n\n";

// Pattern 1: Early Return for Validation
$createUser = Tool::create('create_user')
    ->description('Create a new user account with validation')
    ->stringParam('username', 'Username (3-20 alphanumeric characters)')
    ->stringParam('email', 'Email address')
    ->stringParam('password', 'Password (min 8 characters)')
    ->numberParam('age', 'User age', required: true, minimum: 13, maximum: 120)
    ->handler(function (array $input): ToolResult {
        // Schema already validated: username, email, password present; age in range
        
        $username = $input['username'];
        $email = $input['email'];
        $password = $input['password'];
        $age = (int)$input['age'];
        
        // Pattern: Early return for each validation
        
        // 1. Username format
        if (strlen($username) < 3 || strlen($username) > 20) {
            return ToolResult::error('Username must be between 3 and 20 characters');
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return ToolResult::error('Username can only contain letters, numbers, and underscores');
        }
        
        // 2. Email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ToolResult::error("Invalid email format: {$email}");
        }
        
        // 3. Password strength
        if (strlen($password) < 8) {
            return ToolResult::error('Password must be at least 8 characters');
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            return ToolResult::error('Password must contain at least one uppercase letter');
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            return ToolResult::error('Password must contain at least one number');
        }
        
        // 4. Username uniqueness (simulated)
        $existingUsernames = ['admin', 'root', 'test', 'demo'];
        if (in_array(strtolower($username), $existingUsernames)) {
            return ToolResult::error("Username '{$username}' is already taken");
        }
        
        // All validation passed - create user
        $userId = 'user_' . uniqid();
        
        return ToolResult::success([
            'user_id' => $userId,
            'username' => $username,
            'email' => $email,
            'age' => $age,
            'created_at' => date('c'),
        ]);
    });

// Pattern 2: Error Accumulation
$updateProfile = Tool::create('update_profile')
    ->description('Update user profile (collects all validation errors)')
    ->stringParam('user_id', 'User ID')
    ->stringParam('bio', 'User biography', required: false)
    ->stringParam('website', 'Personal website URL', required: false)
    ->stringParam('location', 'Location', required: false)
    ->handler(function (array $input): ToolResult {
        $userId = $input['user_id'];
        $bio = $input['bio'] ?? '';
        $website = $input['website'] ?? '';
        $location = $input['location'] ?? '';
        
        // Pattern: Collect all errors before failing
        $errors = [];
        
        // Validate bio length
        if (!empty($bio) && strlen($bio) > 500) {
            $errors[] = "Bio too long: " . strlen($bio) . " characters (max: 500)";
        }
        
        // Validate website URL
        if (!empty($website) && !filter_var($website, FILTER_VALIDATE_URL)) {
            $errors[] = "Invalid website URL: {$website}";
        }
        
        // Validate location
        if (!empty($location) && strlen($location) < 2) {
            $errors[] = "Location too short: must be at least 2 characters";
        }
        
        // Check profanity in bio (simplified)
        $profanity = ['badword1', 'badword2'];
        foreach ($profanity as $word) {
            if (!empty($bio) && stripos($bio, $word) !== false) {
                $errors[] = "Bio contains inappropriate content";
                break;
            }
        }
        
        // Return all errors at once (better UX)
        if (!empty($errors)) {
            return ToolResult::error(implode('; ', $errors));
        }
        
        // Update profile
        return ToolResult::success([
            'user_id' => $userId,
            'bio' => $bio,
            'website' => $website,
            'location' => $location,
            'updated_at' => date('c'),
        ]);
    });

// Pattern 3: Custom Validation Function
function validatePhoneNumber(string $phone): array
{
    $errors = [];
    
    // Remove common formatting
    $cleaned = preg_replace('/[\s\-\(\)]/', '', $phone);
    
    // Check if it's all digits (optionally with leading +)
    if (!preg_match('/^\+?\d+$/', $cleaned)) {
        $errors[] = 'Phone number can only contain digits, spaces, dashes, parentheses, and optional + prefix';
    }
    
    // Check length (10-15 digits)
    $digitCount = strlen(preg_replace('/[^\d]/', '', $cleaned));
    if ($digitCount < 10 || $digitCount > 15) {
        $errors[] = "Phone number must be 10-15 digits (found: {$digitCount})";
    }
    
    return $errors;
}

$addContact = Tool::create('add_contact')
    ->description('Add a contact with phone validation')
    ->stringParam('name', 'Contact name')
    ->stringParam('phone', 'Phone number')
    ->handler(function (array $input): ToolResult {
        $name = $input['name'];
        $phone = $input['phone'];
        
        // Use custom validation function
        $phoneErrors = validatePhoneNumber($phone);
        
        if (!empty($phoneErrors)) {
            return ToolResult::error(implode('; ', $phoneErrors));
        }
        
        // Normalize phone number
        $normalized = preg_replace('/[\s\-\(\)]/', '', $phone);
        
        return ToolResult::success([
            'contact_id' => 'contact_' . uniqid(),
            'name' => $name,
            'phone' => $phone,
            'phone_normalized' => $normalized,
            'created_at' => date('c'),
        ]);
    });

// Tests
echo "=== Test: Valid User Creation ===\n";
$result1 = $createUser->execute([
    'username' => 'john_doe',
    'email' => 'john@example.com',
    'password' => 'SecurePass123',
    'age' => 25,
]);
echo "Success: " . ($result1->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Result: " . $result1->getContent() . "\n\n";

echo "=== Test: Invalid Username (too short) ===\n";
$result2 = $createUser->execute([
    'username' => 'ab',
    'email' => 'test@example.com',
    'password' => 'SecurePass123',
    'age' => 25,
]);
echo "Success: " . ($result2->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Error: " . $result2->getContent() . "\n\n";

echo "=== Test: Invalid Email ===\n";
$result3 = $createUser->execute([
    'username' => 'john_doe',
    'email' => 'not-an-email',
    'password' => 'SecurePass123',
    'age' => 25,
]);
echo "Success: " . ($result3->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Error: " . $result3->getContent() . "\n\n";

echo "=== Test: Weak Password ===\n";
$result4 = $createUser->execute([
    'username' => 'john_doe',
    'email' => 'john@example.com',
    'password' => 'weakpass',
    'age' => 25,
]);
echo "Success: " . ($result4->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Error: " . $result4->getContent() . "\n\n";

echo "=== Test: Username Taken ===\n";
$result5 = $createUser->execute([
    'username' => 'admin',
    'email' => 'admin@example.com',
    'password' => 'SecurePass123',
    'age' => 30,
]);
echo "Success: " . ($result5->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Error: " . $result5->getContent() . "\n\n";

echo "=== Test: Update Profile (Multiple Errors) ===\n";
$result6 = $updateProfile->execute([
    'user_id' => 'user_123',
    'bio' => str_repeat('x', 501), // Too long
    'website' => 'not-a-url', // Invalid URL
    'location' => 'a', // Too short
]);
echo "Success: " . ($result6->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Errors: " . $result6->getContent() . "\n";
echo "(Notice all errors returned at once)\n\n";

echo "=== Test: Valid Profile Update ===\n";
$result7 = $updateProfile->execute([
    'user_id' => 'user_123',
    'bio' => 'Software developer passionate about PHP and AI',
    'website' => 'https://example.com',
    'location' => 'San Francisco, CA',
]);
echo "Success: " . ($result7->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Result: " . $result7->getContent() . "\n\n";

echo "=== Test: Valid Phone Number ===\n";
$result8 = $addContact->execute([
    'name' => 'Alice',
    'phone' => '+1 (555) 123-4567',
]);
echo "Success: " . ($result8->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Result: " . $result8->getContent() . "\n\n";

echo "=== Test: Invalid Phone Number ===\n";
$result9 = $addContact->execute([
    'name' => 'Bob',
    'phone' => '123', // Too short
]);
echo "Success: " . ($result9->isSuccess() ? 'Yes' : 'No') . "\n";
echo "Error: " . $result9->getContent() . "\n\n";

echo "✅ Validation patterns example complete!\n";
echo "\nValidation Patterns Demonstrated:\n";
echo "1. Early Return - Fail fast on first error\n";
echo "2. Error Accumulation - Collect all errors before failing\n";
echo "3. Custom Functions - Reusable validation logic\n";
echo "4. Descriptive Messages - Clear, actionable error messages\n";
