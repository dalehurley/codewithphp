<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\DocumentProcessing\DocumentProcessor;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Document Processing - PDF Parser\n\n";

$processor = new DocumentProcessor($_ENV['ANTHROPIC_API_KEY']);

// For demo purposes, we'll use text content instead of actual PDF
$documentText = <<<TEXT
QUARTERLY REPORT - Q4 2024

Executive Summary:
Revenue increased by 23% year-over-year to $45.2M
Customer base grew from 1,200 to 1,850
Operating expenses were $12.3M

Key Metrics:
- Monthly Recurring Revenue: $3.8M
- Customer Churn: 2.1%
- Net Promoter Score: 72
TEXT;

echo "Analyzing quarterly report...\n\n";

$result = $processor->processText(
    $documentText,
    'Extract key metrics and summarize the performance. Present as JSON with fields: revenue, growth, customers, and summary.'
);

echo "Claude's analysis:\n";
echo $result['content'][0]['text'] . "\n\n";
echo "Tokens: {$result['usage']['input_tokens']} in, {$result['usage']['output_tokens']} out\n";
