<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\DocumentProcessing\DocumentProcessor;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Document Processing - Invoice Extraction\n\n";

$processor = new DocumentProcessor($_ENV['ANTHROPIC_API_KEY']);

$invoiceText = <<<TEXT
INVOICE #INV-2024-001

Bill To:
Acme Corporation
123 Main Street
San Francisco, CA 94102

Date: January 15, 2024
Due Date: February 15, 2024

Items:
1. Web Development Services (40 hours @ $150/hr) - $6,000.00
2. UI/UX Design (20 hours @ $125/hr) - $2,500.00
3. Hosting & Maintenance - $500.00

Subtotal: $9,000.00
Tax (8.5%): $765.00
Total: $9,765.00
TEXT;

$prompt = 'Extract invoice data as JSON: invoice_number, date, due_date, customer, items (array), subtotal, tax, total';

echo "Extracting invoice data...\n\n";
$result = $processor->processText($invoiceText, $prompt);

echo "Extracted data:\n";
echo $result['content'][0]['text'] . "\n";
