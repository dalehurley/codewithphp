<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\DocumentParser;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Setup logger
$logger = new Logger('document-parser');
$logger->pushHandler(new StreamHandler('php://stdout', Logger::INFO));

$parser = new DocumentParser($logger);

echo "=== Document Parser Examples ===\n\n";

// Example 1: Parse HTML
echo "Example 1: HTML Parsing\n";
echo str_repeat('-', 50) . "\n";

$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Product Page</title>
    <style>.hidden { display: none; }</style>
    <script>console.log('test');</script>
</head>
<body>
    <h1>Premium Widget</h1>
    <p class="price">\$299.99</p>
    <p class="description">
        High-quality widget with advanced features.
        Perfect for professional use.
    </p>
    <ul>
        <li>Durable construction</li>
        <li>5-year warranty</li>
        <li>Free shipping</li>
    </ul>
</body>
</html>
HTML;

$parsed = $parser->parse($html, 'html');
echo "Parsed HTML:\n{$parsed}\n\n";

// Example 2: Parse JSON
echo "Example 2: JSON Parsing\n";
echo str_repeat('-', 50) . "\n";

$json = json_encode([
    'product' => 'Laptop',
    'price' => 1299.99,
    'specs' => [
        'cpu' => 'Intel i7',
        'ram' => '16GB',
        'storage' => '512GB SSD'
    ],
    'in_stock' => true
]);

$parsed = $parser->parse($json, 'json');
echo "Parsed JSON:\n{$parsed}\n\n";

// Example 3: Parse CSV
echo "Example 3: CSV Parsing\n";
echo str_repeat('-', 50) . "\n";

$csv = <<<CSV
Name,Email,Department,Salary
John Doe,john@example.com,Engineering,95000
Jane Smith,jane@example.com,Marketing,85000
Bob Johnson,bob@example.com,Sales,75000
CSV;

$parsed = $parser->parse($csv, 'csv');
echo "Parsed CSV:\n{$parsed}\n\n";

// Example 4: Parse XML
echo "Example 4: XML Parsing\n";
echo str_repeat('-', 50) . "\n";

$xml = <<<XML
<?xml version="1.0"?>
<order>
    <id>12345</id>
    <customer>
        <name>Alice Brown</name>
        <email>alice@example.com</email>
    </customer>
    <items>
        <item>
            <name>Widget</name>
            <quantity>2</quantity>
            <price>29.99</price>
        </item>
        <item>
            <name>Gadget</name>
            <quantity>1</quantity>
            <price>49.99</price>
        </item>
    </items>
    <total>109.97</total>
</order>
XML;

$parsed = $parser->parse($xml, 'xml');
echo "Parsed XML:\n{$parsed}\n\n";

// Example 5: Extract snippets
echo "Example 5: Extract Snippets\n";
echo str_repeat('-', 50) . "\n";

$longText = <<<TEXT
Our company has been in business for over 20 years, providing quality services to customers worldwide.
We offer a wide range of products including software development, cloud hosting, and consulting services.
Our pricing is competitive and we guarantee customer satisfaction. Contact us today at sales@example.com
or call 1-800-555-0123 for more information. We are located at 123 Main Street, Suite 100, Anytown, USA.
TEXT;

$keywords = ['pricing', 'contact', 'services'];
$snippets = $parser->extractSnippets($longText, $keywords, 50);

echo "Extracted Snippets:\n";
foreach ($snippets as $keyword => $snippet) {
    echo "\nKeyword: {$keyword}\n";
    echo "Position: {$snippet['position']}\n";
    echo "Text: ...{$snippet['text']}...\n";
}
echo "\n";

// Example 6: Chunk long text
echo "Example 6: Text Chunking\n";
echo str_repeat('-', 50) . "\n";

$longDocument = str_repeat("This is a sample paragraph. ", 100);
$chunks = $parser->chunkText($longDocument, 200, 50);

echo "Split text into " . count($chunks) . " chunks\n";
echo "First chunk length: " . strlen($chunks[0]) . " chars\n";
echo "Last chunk length: " . strlen($chunks[count($chunks) - 1]) . " chars\n\n";

// Example 7: Parse multiple documents
echo "Example 7: Batch Document Parsing\n";
echo str_repeat('-', 50) . "\n";

$documents = [
    '{"name": "Document 1", "type": "json"}',
    '<doc><name>Document 2</name><type>xml</type></doc>',
    'Plain text document 3'
];

$types = ['json', 'xml', 'text'];
$results = [];

foreach ($documents as $index => $doc) {
    $results[] = $parser->parse($doc, $types[$index]);
}

echo "Parsed " . count($results) . " documents successfully\n";
foreach ($results as $index => $result) {
    echo "\nDocument " . ($index + 1) . ":\n";
    echo substr($result, 0, 100) . "...\n";
}

echo "\n=== Document Parser Examples Complete ===\n";
