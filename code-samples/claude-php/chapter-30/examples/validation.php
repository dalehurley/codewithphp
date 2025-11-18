<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\DataValidator;
use ClaudePhp\ClaudePhp;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Setup logger
$logger = new Logger('validation');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));

// Initialize Claude client
$apiKey = $_ENV['ANTHROPIC_API_KEY'] ?? throw new RuntimeException('ANTHROPIC_API_KEY not set');
$client = new ClaudePhp(apiKey: $apiKey);

$validator = new DataValidator($client, $logger, minConfidence: 0.85);

echo "=== Data Validation Examples ===\n\n";

// Example 1: Valid data
echo "Example 1: Valid Data\n";
echo str_repeat('-', 50) . "\n";

$validData = [
    'name' => 'John Doe',
    'email' => 'john.doe@example.com',
    'age' => 30,
    'phone' => '+1-555-123-4567',
    'registered_date' => '2024-01-15'
];

$userSchema = [
    'name' => ['type' => 'string', 'required' => true],
    'email' => ['type' => 'email', 'required' => true],
    'age' => ['type' => 'integer', 'required' => true],
    'phone' => ['type' => 'phone', 'required' => false],
    'registered_date' => ['type' => 'date', 'required' => true]
];

$result = $validator->validate($validData, $userSchema);

echo "Validation Result:\n";
echo "- Valid: " . ($result->isValid() ? 'YES' : 'NO') . "\n";
echo "- Confidence: " . round($result->getConfidence() * 100, 1) . "%\n";
echo "- Errors: " . (empty($result->getErrors()) ? 'None' : count($result->getErrors())) . "\n";
echo "- Warnings: " . (empty($result->getWarnings()) ? 'None' : count($result->getWarnings())) . "\n\n";

// Example 2: Invalid data (missing required fields)
echo "Example 2: Missing Required Fields\n";
echo str_repeat('-', 50) . "\n";

$invalidData = [
    'name' => 'Jane Smith',
    'age' => 25
    // Missing required email and registered_date
];

$result = $validator->validate($invalidData, $userSchema);

echo "Validation Result:\n";
echo "- Valid: " . ($result->isValid() ? 'YES' : 'NO') . "\n";
echo "- Confidence: " . round($result->getConfidence() * 100, 1) . "%\n";

if (!empty($result->getErrors())) {
    echo "\nErrors:\n";
    foreach ($result->getErrors() as $error) {
        echo "  - {$error}\n";
    }
}
echo "\n";

// Example 3: Type validation
echo "Example 3: Type Validation\n";
echo str_repeat('-', 50) . "\n";

$wrongTypes = [
    'name' => 'Bob Johnson',
    'email' => 'not-an-email',  // Invalid email
    'age' => '30',              // String instead of int
    'phone' => '123',           // Invalid phone
    'registered_date' => '2024-15-45'  // Invalid date
];

$result = $validator->validate($wrongTypes, $userSchema);

echo "Validation Result:\n";
echo "- Valid: " . ($result->isValid() ? 'YES' : 'NO') . "\n";

if (!empty($result->getErrors())) {
    echo "\nType Errors Found:\n";
    foreach ($result->getErrors() as $error) {
        echo "  - {$error}\n";
    }
}
echo "\n";

// Example 4: Custom validation rules
echo "Example 4: Custom Validation\n";
echo str_repeat('-', 50) . "\n";

$productSchema = [
    'name' => ['type' => 'string', 'required' => true],
    'price' => [
        'type' => 'float',
        'required' => true,
        'validate' => function($value) {
            if ($value <= 0) {
                return ['valid' => false, 'message' => 'Price must be positive'];
            }
            if ($value > 10000) {
                return ['valid' => false, 'message' => 'Price exceeds maximum'];
            }
            return ['valid' => true, 'message' => ''];
        }
    ],
    'quantity' => [
        'type' => 'integer',
        'required' => true,
        'validate' => function($value) {
            if ($value < 0) {
                return ['valid' => false, 'message' => 'Quantity cannot be negative'];
            }
            return ['valid' => true, 'message' => ''];
        }
    ]
];

$productData = [
    'name' => 'Widget',
    'price' => -50.00,  // Invalid: negative price
    'quantity' => 10
];

$result = $validator->validate($productData, $productSchema);

echo "Product Validation:\n";
echo "- Valid: " . ($result->isValid() ? 'YES' : 'NO') . "\n";

if (!empty($result->getErrors())) {
    echo "\nCustom Validation Errors:\n";
    foreach ($result->getErrors() as $error) {
        echo "  - {$error}\n";
    }
}
echo "\n";

// Example 5: Completeness check
echo "Example 5: Data Completeness\n";
echo str_repeat('-', 50) . "\n";

$partialData = [
    'name' => 'Complete Corp',
    'email' => 'contact@complete.com',
    'phone' => '+1-555-0000',
    'address' => '123 Main St'
    // Missing optional fields: website, fax
];

$companySchema = [
    'name' => ['type' => 'string', 'required' => true],
    'email' => ['type' => 'email', 'required' => true],
    'phone' => ['type' => 'phone', 'required' => true],
    'address' => ['type' => 'string', 'required' => false],
    'website' => ['type' => 'url', 'required' => false],
    'fax' => ['type' => 'phone', 'required' => false]
];

$completeness = $validator->validateCompleteness($partialData, $companySchema);

echo "Completeness Score: " . round($completeness * 100, 1) . "%\n";
echo "Fields Present: " . count($partialData) . "/" . count($companySchema) . "\n\n";

// Example 6: Batch validation
echo "Example 6: Batch Validation\n";
echo str_repeat('-', 50) . "\n";

$records = [
    ['name' => 'Record 1', 'email' => 'user1@example.com', 'age' => 25, 'registered_date' => '2024-01-01'],
    ['name' => 'Record 2', 'email' => 'invalid-email', 'age' => 30, 'registered_date' => '2024-01-02'],
    ['name' => 'Record 3', 'age' => 35, 'registered_date' => '2024-01-03'],  // Missing email
];

$results = $validator->validateBatch($records, $userSchema);

echo "Batch Validation Results:\n";
foreach ($results as $index => $result) {
    echo "\nRecord " . ($index + 1) . ":\n";
    echo "  Valid: " . ($result->isValid() ? '✓' : '✗') . "\n";
    echo "  Confidence: " . round($result->getConfidence() * 100, 1) . "%\n";

    if (!empty($result->getErrors())) {
        echo "  Errors: " . implode(', ', $result->getErrors()) . "\n";
    }
}

echo "\n=== Data Validation Examples Complete ===\n";
