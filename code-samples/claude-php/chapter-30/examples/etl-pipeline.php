<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\ETLPipeline;
use App\DocumentParser;
use App\DataValidator;
use App\AnalyticsEngine;
use ClaudePhp\ClaudePhp;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Setup logger
$logger = new Logger('etl-pipeline');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));

// Initialize Claude client
$apiKey = $_ENV['ANTHROPIC_API_KEY'] ?? throw new RuntimeException('ANTHROPIC_API_KEY not set');
$client = new ClaudePhp(apiKey: $apiKey);

// Initialize services
$parser = new DocumentParser($logger);
$validator = new DataValidator($client, $logger);
$analytics = new AnalyticsEngine($client, $logger);
$pipeline = new ETLPipeline($client, $parser, $validator, $analytics, $logger);

echo "=== ETL Pipeline Example ===\n\n";

// Example 1: Extract data from invoice text
echo "Example 1: Invoice Data Extraction\n";
echo str_repeat('-', 50) . "\n";

$invoiceText = <<<TEXT
INVOICE #INV-2024-0123

Bill To:
Acme Corporation
123 Business St
San Francisco, CA 94105

Invoice Date: 2024-01-15
Due Date: 2024-02-15

Items:
1. Web Development Services - \$5,000.00
2. Database Optimization - \$2,500.00
3. Server Setup - \$1,200.00

Subtotal: \$8,700.00
Tax (8.5%): \$739.50
Total: \$9,439.50

Payment Terms: Net 30 days
TEXT;

$invoiceSchema = [
    'invoice_number' => [
        'type' => 'string',
        'required' => true,
        'description' => 'Invoice number'
    ],
    'bill_to_company' => [
        'type' => 'string',
        'required' => true,
        'description' => 'Company being billed'
    ],
    'bill_to_address' => [
        'type' => 'string',
        'required' => true,
        'description' => 'Billing address'
    ],
    'invoice_date' => [
        'type' => 'date',
        'required' => true,
        'description' => 'Invoice date'
    ],
    'due_date' => [
        'type' => 'date',
        'required' => true,
        'description' => 'Payment due date'
    ],
    'items' => [
        'type' => 'array',
        'required' => true,
        'description' => 'Line items with description and amount'
    ],
    'subtotal' => [
        'type' => 'float',
        'required' => true,
        'description' => 'Subtotal amount'
    ],
    'tax' => [
        'type' => 'float',
        'required' => true,
        'description' => 'Tax amount'
    ],
    'total' => [
        'type' => 'float',
        'required' => true,
        'description' => 'Total amount'
    ],
    'payment_terms' => [
        'type' => 'string',
        'required' => false,
        'description' => 'Payment terms'
    ]
];

try {
    $result = $pipeline->process(
        source: $invoiceText,
        sourceType: 'text',
        schema: $invoiceSchema,
        options: [
            'generate_analytics' => true,
            'strict' => true
        ]
    );

    echo "Extraction Result:\n";
    echo "- Status: " . ($result->isSuccess() ? 'SUCCESS' : 'FAILED') . "\n";
    echo "- Validation: " . ($result->validation->isValid() ? 'PASSED' : 'FAILED') . "\n";
    echo "- Confidence: " . round($result->validation->getConfidence() * 100, 1) . "%\n";
    echo "- Processing Time: " . round($result->metadata['processing_time'], 3) . "s\n\n";

    echo "Extracted Data:\n";
    echo json_encode($result->data, JSON_PRETTY_PRINT) . "\n\n";

    if (!empty($result->validation->getErrors())) {
        echo "Validation Errors:\n";
        foreach ($result->validation->getErrors() as $error) {
            echo "  - {$error}\n";
        }
        echo "\n";
    }

    if ($result->analytics) {
        echo "Analytics:\n";
        echo "- Quality Score: " . ($result->analytics['quality']['overall_score'] * 100) . "%\n";
        echo "- Completeness: " . ($result->analytics['statistics']['completeness'] * 100) . "%\n";
        echo "\n";
    }

} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Example 2: Extract contact information
echo "\nExample 2: Contact Information Extraction\n";
echo str_repeat('-', 50) . "\n";

$contactText = <<<TEXT
John Smith
Senior Software Engineer
TechCorp Inc.

Email: john.smith@techcorp.com
Phone: +1 (555) 123-4567
Mobile: +1 (555) 987-6543

Office Address:
456 Innovation Drive, Suite 200
Austin, TX 78701

LinkedIn: linkedin.com/in/johnsmith
GitHub: github.com/johnsmith

Skills: PHP, Python, JavaScript, Docker, Kubernetes
Years of Experience: 12
TEXT;

$contactSchema = [
    'name' => ['type' => 'string', 'required' => true],
    'title' => ['type' => 'string', 'required' => true],
    'company' => ['type' => 'string', 'required' => true],
    'email' => ['type' => 'email', 'required' => true],
    'phone' => ['type' => 'phone', 'required' => false],
    'mobile' => ['type' => 'phone', 'required' => false],
    'address' => ['type' => 'string', 'required' => false],
    'linkedin' => ['type' => 'url', 'required' => false],
    'github' => ['type' => 'url', 'required' => false],
    'skills' => ['type' => 'array', 'required' => false],
    'years_experience' => ['type' => 'integer', 'required' => false]
];

try {
    $result = $pipeline->process(
        source: $contactText,
        sourceType: 'text',
        schema: $contactSchema,
        options: ['generate_analytics' => false]
    );

    echo "Contact Data Extracted:\n";
    echo json_encode($result->data, JSON_PRETTY_PRINT) . "\n\n";

    echo "Validation: ";
    echo $result->validation->isValid() ? "✓ PASSED\n" : "✗ FAILED\n";
    echo "Confidence: " . round($result->validation->getConfidence() * 100, 1) . "%\n\n";

} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

// Example 3: Batch processing
echo "\nExample 3: Batch Processing\n";
echo str_repeat('-', 50) . "\n";

$emailBatch = [
    "From: customer1@example.com\nSubject: Order #12345\nI need help with my order",
    "From: customer2@example.com\nSubject: Refund Request\nPlease process my refund for order #67890",
    "From: customer3@example.com\nSubject: Product Inquiry\nDo you have this item in stock?"
];

$emailSchema = [
    'from' => ['type' => 'email', 'required' => true],
    'subject' => ['type' => 'string', 'required' => true],
    'message' => ['type' => 'string', 'required' => true],
    'category' => ['type' => 'string', 'required' => false, 'description' => 'Email category (support, refund, inquiry, etc.)']
];

try {
    $results = $pipeline->batchProcess(
        sources: $emailBatch,
        sourceType: 'text',
        schema: $emailSchema,
        options: [
            'batch_size' => 5,
            'stop_on_error' => false
        ]
    );

    echo "Processed {count($results)} emails:\n\n";

    foreach ($results as $index => $result) {
        echo "Email " . ($index + 1) . ":\n";
        if ($result->isSuccess()) {
            echo "  Category: " . ($result->data['category'] ?? 'Unknown') . "\n";
            echo "  From: " . ($result->data['from'] ?? 'Unknown') . "\n";
            echo "  Subject: " . ($result->data['subject'] ?? 'Unknown') . "\n";
        } else {
            echo "  Status: FAILED\n";
        }
        echo "\n";
    }

} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n\n";
}

echo "=== ETL Pipeline Examples Complete ===\n";
