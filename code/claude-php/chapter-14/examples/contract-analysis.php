<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\DocumentProcessing\DocumentProcessor;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Document Processing - Contract Analysis\n\n";

$processor = new DocumentProcessor($_ENV['ANTHROPIC_API_KEY']);

$contractText = <<<TEXT
SOFTWARE LICENSE AGREEMENT

This Agreement is entered into on January 1, 2024 between ABC Software Inc. ("Licensor") and XYZ Company ("Licensee").

1. LICENSE GRANT
Licensor grants Licensee a non-exclusive, non-transferable license to use the Software.

2. TERM
This Agreement is effective for 12 months from the date above and will automatically renew for successive 12-month periods unless terminated by either party with 30 days written notice.

3. FEES
Licensee shall pay an annual license fee of $10,000, due within 30 days of invoice.

4. TERMINATION
Either party may terminate this Agreement with 30 days written notice.
TEXT;

$prompt = 'Analyze this contract and extract: parties, term length, renewal terms, fees, termination clause. Identify any potential risks or concerns.';

echo "Analyzing contract...\n\n";
$result = $processor->processText($contractText, $prompt);

echo "Contract analysis:\n";
echo $result['content'][0]['text'] . "\n";
